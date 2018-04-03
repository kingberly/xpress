<?php
require_once("Order.php");
require_once("../include/streamserver.php");
require_once("../include/fileserver.php");
require_once("tunnelserver.php");
require_once("rtmpd.php");

class ImportEncapsoluteCodeOrder extends Order {

	public function __construct($header, $data) {
		parent::__construct($header, $data);
		// $data:
		// [{'mac': <mac>, 'ac': <activation code>, 'cid': <cid>, 'pid': <pid>, 'hash': <checksum>}, ...]
	}

	public function process() {
		$this->importActivationCodes();
		return $this->orderResults;
	}

	private function importActivationCodes() {
		// Prepare SQL.
		$pdo = $this->license_db->db;
		$StreamServer = new StreamServer($this->license_db);
		$TunnelServer = new TunnelServer($this->license_db);
		$RtmpServer = new Rtmpd($this->license_db);
		$FileServer = new FileServer($this->license_db);
		
		$pdo->beginTransaction();
		$device_count = count($this->data);
		$region = $this->region;
		$StreamServer->batchAssignBegin($device_uid, $device_count, $region);
		$TunnelServer->batchAssignBegin($device_uid, false, $device_count, $region);
		$RtmpServer->batchAssignBegin($device_uid, false, $device_count, $region);
		if (FileServer::ENABLED) {
			$FileServer->batchAssignBegin($device_uid, $device_count, $region);
		}

		$sql = "INSERT INTO series_number 
					(license_id, mac, activated_code, bind_mac) 
				VALUES 
					(:license_id, :mac, :ac, 1)";
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(':license_id', $licenseId);
		$stmt->bindParam(':mac', $mac);
		$stmt->bindParam(':ac', $ac);

		$failData = array();
		$messages = array();
		$validLicenses = array();
		foreach ($this->data as $datum) {
			// Check checksum.
			$hash = Order::hash($datum);
			if ($hash != $datum['hash']) {
				$failData[] = $datum;
				$messages[] = 'hash does not match';
				continue;
			}

			// Check license.
			$key = "{$datum['cid']}{$datum['pid']}";
			if (!isset($validLicenses[$key])) {
				try {
					$license = $this->getLicense($datum['cid'], $datum['pid']);
				}
				catch (Exception $e) {
					// Create license if the license is inexist.
					try {
						$this->license_db->AddCompany($datum['cid'], 'AUTO');
					}
					catch (Exception $e) {
						// Do nothing.
					}
					$this->license_db->AddLicense($datum['cid'], $datum['pid']);

					$license = $this->getLicense($datum['cid'], $datum['pid']);
				}
				$validLicenses[$key] = $license;
			}

			$license = $validLicenses[$key];

			// Execute SQL.
			$licenseId = $license['id'];
			$mac = $datum['mac'];
			$ac = $datum['ac'];
			$device_uid = $datum['cid'] . $datum['pid'] . '-' . $datum['mac'];

			$ret = $stmt->execute();
			if ($ret === false) {
				$failData[] = $datum;
				$err = $stmt->errorInfo();
				$messages[] = $err[2];
			}
			else {
				try {
					if ($datum['pid'] == 'CC') {
						$TunnelServer->batchAssignOne();
						$StreamServer->batchAssignOne();

						if (FileServer::ENABLED) {
							$FileServer->batchAssignOne();
						}
					}
					else if ($datum['pid'] == 'MC') {
						$RtmpServer->batchAssignOne();
						$StreamServer->batchAssignOne('RVLO');
					}
				}
				catch (Exception $e) {
					$failData[] = $datum;
					$messages[] = $e->getMessage();
				}
			}
		}

		$nFailData = count($failData);
		$this->orderResults['n_success_data'] = count($this->data) - $nFailData;
		$this->orderResults['n_fail_data'] = $nFailData;
		if ($nFailData == 0) {
			$pdo->commit();
		}
		else {
			$this->orderResults['status'] = 'fail'; // Overwrite status.
			$this->orderResults['fail_data'] = $failData;
			$this->orderResults['messages'] = $messages;
			$pdo->rollBack();
		}
	}
}

?>
