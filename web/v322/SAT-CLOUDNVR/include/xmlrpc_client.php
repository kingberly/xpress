<?php
class xmlrpc_client {
	private $url;
	function __construct($url, $autoload=true) {
		$this->url = $url;
		$this->methods = array();
		if ($autoload) {
			$resp = $this->call('system.listMethods', null);
			$this->methods = $resp;
		}
	}
	public function call($method, $params = null, $options = null) {
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL, $this->url);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch,CURLOPT_POST, true);
		$post = xmlrpc_encode_request($method, $params);
		curl_setopt($ch,CURLOPT_POSTFIELDS, $post);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER,array('Content-Type: text/xml')); 
		if ($options) {
			foreach($options as $k => $v) {
				curl_setopt($ch, $k, $v);
			}
		}

		$result_raw = curl_exec ($ch);
		if ($result_raw === false) {
			$error = curl_error($ch);
			curl_close ($ch);
			throw new Exception($error);
		}
		else {
			curl_close ($ch);
			$result = xmlrpc_decode($result_raw);
		}
		return $result;
	}
}
?>
