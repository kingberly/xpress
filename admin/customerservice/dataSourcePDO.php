<?php
require_once 'dbutil.php';

function query($cloudid){
	$pdo = getPDO();
	$table_u = 'customerservice.service_users';
	$table_c = 'customerservice.service_cameras';
	$sql = "select u.cloudid,account,email,mac,serviceid,model,firmware from $table_u as u left join $table_c as c on u.cloudid=c.cloudid";
	if($cloudid!='')
		$sql.=" where u.cloudid like ?";
	$stmt = $pdo->prepare($sql);
	if($cloudid!='')
		$stmt->bindValue(1,"%$cloudid%",PDO::PARAM_STR);

	$stmt->execute();
	$services = array();
	$tmp = '';
	$index = 1;
	while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
		$cloudid = $row['cloudid'];
		//if($cloudid!=$tmp){
    if(strcmp($cloudid,$tmp)!=0){
			$arr = array();
			$tmp = $cloudid;
			$arr['cloudid'] = $cloudid;
			$arr['account'] = $row['account'];
			$arr['email'] = $row['email'];
			$arr['mac'] =$row['mac'];
			$arr['serviceid'] = $row['serviceid'];
			$arr['model'] =$row['model'];
			$arr['firmware'] =$row['firmware'];
			$index++;
		}else{
			$arr['mac'].= ",".$row['mac'];
			$arr['serviceid'].= ",".$row['serviceid'];
			$arr['model'].= ",".$row['model'];
			$arr['firmware'].= ",".$row['firmware'];
		}
		$services[$index] = $arr;
	}
	$pdo = null;
	return $services;
}

function queryByCloudID($cloudid){
	$pdo = getPDO();
	$table_u = 'customerservice.service_users';
	$table_c = 'customerservice.service_cameras';
	$sql = "select u.cloudid,account,email,mac,serviceid,model,firmware from $table_u as u left join $table_c as c on u.cloudid=c.cloudid";
	$sql.=" where u.cloudid =?";
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(1, $cloudid,PDO::PARAM_STR);
	$stmt->execute();
	$arr = array();
	$tmp = '';
	while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
		$cloudid = $row['cloudid'];
		if($cloudid!=$tmp){
			$tmp = $cloudid;
			$arr['cloudid'] = $cloudid;
			$arr['account'] = $row['account'];
			$arr['email'] = $row['email'];
			$arr['mac'] =$row['mac'];
			$arr['serviceid'] = $row['serviceid'];
			$arr['model'] =$row['model'];
			$arr['firmware'] =$row['firmware'];
		}else{
			$arr['mac'].= ",".$row['mac'];
			$arr['serviceid'].= ",".$row['serviceid'];
			$arr['model'].= ",".$row['model'];
			$arr['firmware'].= ",".$row['firmware'];
		}
	}
	$pdo = null;
	return $arr;
}

function queryByCameraID($mac){
	$pdo = getPDO();
	$sql = "SELECT * FROM service_cameras WHERE mac=?";
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(1, $mac,PDO::PARAM_STR);
	$stmt->execute();
	$arr = array();
	$arr['mac'] = $mac;
	while($row=$stmt->fetch(PDO::FETCH_ASSOC)){
		$arr['cloudid'] = $row['cloudid'];
		$arr['serviceid'] = $row['serviceid'];
		$arr['model'] =$row['model'];
		$arr['firmware'] = $row['firmware'];
	}
	$pdo = null;
	return $arr;

}

function createCloudService($service){
	$pdo = getPDO();
	$table = 'service_users';
	$sql = "INSERT INTO $table SET cloudid=?, account=?, email=?";
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(1,$service['cloudid'],PDO::PARAM_STR);
	$stmt->bindParam(2,$service['account'],PDO::PARAM_STR);
	$stmt->bindParam(3,$service['email'],PDO::PARAM_STR);
	$b = false;
	try {
		$stmt->execute();
		$b = true;
	}catch(Exception $e){
	}

	$pdo = null;
	return $b;
}

function updateCloudService($service){
	$pdo = getPDO();
	$sql = "UPDATE service_users SET account=?, email=? WHERE cloudid=?";
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(1,$service['account'],PDO::PARAM_STR);
	$stmt->bindParam(2,$service['email'],PDO::PARAM_STR);
	$stmt->bindParam(3,$service['cloudid'],PDO::PARAM_STR);
	$b = $stmt->execute();
	$pdo = null;
	return $b;
}

function createCameraService($service){
	$pdo = getPDO();
	$table = 'service_users';
	$sql = "SELECT * FROM $table Where cloudid=?";
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(1,$service['cloudid'],PDO::PARAM_STR);
	$stmt->execute();
	$b = false;
	if($stmt->rowCount()==0){
	}else{
		$table = 'service_cameras';
		$sql = "INSERT INTO $table SET cloudid=?, mac=?, serviceid=?, model=?, firmware=?";
		$stmt = $pdo->prepare($sql);
		$stmt->bindParam(1,$service['cloudid'],PDO::PARAM_STR);
		$stmt->bindParam(2,$service['mac'],PDO::PARAM_STR);
		$stmt->bindParam(3,$service['serviceid'],PDO::PARAM_STR);
		$stmt->bindParam(4,$service['model'],PDO::PARAM_STR);
		$stmt->bindParam(5,$service['firmware'],PDO::PARAM_STR);
		try {
			$stmt->execute();
			$b = true;
		}catch(Exception $e){
		}
	}

	$pdo = null;
	return $b;

}

function updateCameraService($camera){
	$pdo = getPDO();
	$sql = "UPDATE service_cameras SET serviceid=?, model=?, firmware=? WHERE mac=?";
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(1,$camera['serviceid'],PDO::PARAM_STR);
	$stmt->bindParam(2,$camera['model'],PDO::PARAM_STR);
	$stmt->bindParam(3,$camera['firmware'],PDO::PARAM_STR);
	$stmt->bindParam(4,$camera['mac'],PDO::PARAM_STR);
	$b = $stmt->execute();
	$pdo = null;
	return $b;
}

function removeByCloudID($cloudid){
	$pdo = getPDO();
	$table_u = 'customerservice.service_users';
	$table_c = 'customerservice.service_cameras';
	$sql1 = "DELETE FROM $table_u  WHERE cloudid=?";
	$sql2 = "DELETE FROM $table_c  WHERE cloudid=?";

	$stmt1 = $pdo->prepare($sql1);
	$stmt1->bindParam(1,$cloudid,PDO::PARAM_STR);
	$b1 = $stmt1->execute();

	$stmt2 = $pdo->prepare($sql2);
	$stmt2->bindParam(1,$cloudid,PDO::PARAM_STR);
	$b2 = $stmt2->execute();
	$pdo = null;
	if($b1&$b2){
		return true;
	}else{
		return false;
	}


}

function removeByCameraID($mac){
	$pdo = getPDO();
	$table = 'customerservice.service_cameras';
	$sql = "DELETE FROM $table  WHERE mac=?";
	$stmt = $pdo->prepare($sql);
	$stmt->bindParam(1,$mac,PDO::PARAM_STR);
	$b = $stmt->execute();
	$pdo = null;
	return $b;
}