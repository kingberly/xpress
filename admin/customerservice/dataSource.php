<?php
require_once 'dbutil.php';

function query($cloudid){
	$link = getLink();
	$sql = "select * from service_users as u left join service_cameras as c on u.cloudid=c.cloudid";
	if($cloudid!='')
		$sql.=" where u.cloudid like '%$cloudid%'";

	$result=mysql_query($sql,$link);
	$services = array();
	$tmp = '';
	$index = 0;
	for($i=0;$i<mysql_num_rows($result);$i++){
		$index = $i+1;
		$cloudid = mysql_result($result,$i,'cloudid');
		if($cloudid!=$tmp){
			$tmp = $cloudid;
			$arr['cloudid'] = $cloudid;
			$arr['account'] = mysql_result($result,$i,'account');
			$arr['email'] = mysql_result($result,$i,'email');
			$arr['mac'] = mysql_result($result,$i,'mac');
			$arr['serviceid'] = mysql_result($result,$i,'serviceid');
			$arr['model'] = mysql_result($result,$i,'model');
			$arr['firmware'] = mysql_result($result,$i,'firmware');
			$index++;
		}else{
			$arr['mac'].= ",".mysql_result($result,$i,'mac');
			$arr['serviceid'].= ",".mysql_result($result,$i,'serviceid');
			$arr['model'].= ",".mysql_result($result,$i,'model');
			$arr['firmware'].= ",".mysql_result($result,$i,'firmware');
		}
		$services[$index] = $arr;


	}
	return $services;
}

function queryByCloudID($cloudid){
	$link = getLink();
	$sql = "select * from service_users as u left join service_cameras as c on u.cloudid=c.cloudid";
	$sql.=" where u.cloudid = '$cloudid'";
	$result=mysql_query($sql,$link);
	$tmp = '';
	$arr = array();
	for($i=0;$i<mysql_num_rows($result);$i++){
		$cloudid = mysql_result($result,$i,'cloudid');
		if($cloudid!=$tmp){
			$tmp = $cloudid;
			$arr['cloudid'] = $cloudid;
			$arr['account'] = mysql_result($result,$i,'account');
			$arr['email'] = mysql_result($result,$i,'email');
			$arr['mac'] = mysql_result($result,$i,'mac');
			$arr['serviceid'] = mysql_result($result,$i,'serviceid');
			$arr['model'] = mysql_result($result,$i,'model');
			$arr['firmware'] = mysql_result($result,$i,'firmware');
			$index++;
		}else{
			$arr['mac'].= ",".mysql_result($result,$i,'mac');
			$arr['serviceid'].= ",".mysql_result($result,$i,'serviceid');
			$arr['model'].= ",".mysql_result($result,$i,'model');
			$arr['firmware'].= ",".mysql_result($result,$i,'firmware');
		}

	}
	return $arr;
}

function queryByCameraID($mac){
	$link = getLink();
	$sql = sprintf("SELECT * FROM service_cameras WHERE mac=%s",GetSQLValue($mac, 'text'));
	$result=mysql_query($sql,$link);
	$arr = array();
	$arr['mac'] = $mac;
	for($i=0;$i<mysql_num_rows($result);$i++){
		$arr['cloudid'] = mysql_result($result,$i,'cloudid');
		$arr['serviceid'] = mysql_result($result,$i,'serviceid');
		$arr['model'] = mysql_result($result,$i,'model');
		$arr['firmware'] = mysql_result($result,$i,'firmware');
	}
	return $arr;
}

function createCloudService($service){
	$link = getLink();
	$sql = sprintf("INSERT INTO service_users (cloudid,account,email) VALUES(%s,%s,%s)",
			GetSQLValue($service['cloudid'], 'text'), GetSQLValue($service['account'], 'text'),
			GetSQLValue($service['email'], 'text'));
	$b = mysql_query($sql,$link);
	if($b){
		echo "<div class=\"success\">Create Service - Cloud successfully.</div>";
	}else{
		$error = mysql_error($link);
		echo "<div class=\"error\">Create Service - Cloud failed. ($error)</div>";
	}

}

function updateCloudService($service){
	$link = getLink();
	$sql = sprintf("UPDATE service_users SET account=%s, email=%s WHERE cloudid=%s",GetSQLValue($service['account'], 'text'),
			GetSQLValue($service['email'], 'text'),GetSQLValue($service['cloudid'], 'text'));
	$b = mysql_query($sql,$link);
	if($b){
		echo "<div class=\"success\">Update Service - Cloud successfully.</div>";
	}else{
		$error = mysql_error($link);
		echo "<div class=\"error\">Update Service - Cloud failed. ($error)</div>";
	}
}

function createCameraService($service){
	$link = getLink();
	$query = sprintf("SELECT * FROM service_users Where cloudid = %s", GetSQLValue($service['cloudid'], 'text'));
	$result = mysql_query($query,$link);
	if (mysql_fetch_row($result)) {
		$sql = sprintf("INSERT INTO service_cameras (cloudid,mac,serviceid,model,firmware) VALUES(%s,%s,%s,%s,%s)",
				GetSQLValue($service['cloudid'], 'text'), GetSQLValue($service['mac'], 'text'),
				GetSQLValue($service['serviceid'], 'text'),GetSQLValue($service['model'], 'text'),
				GetSQLValue($service['firmware'], 'text'));
		$b = mysql_query($sql,$link);
		if($b){
			echo "<div class=\"success\">Create Service - Camera successfully.</div>";
		}else{
			$error = mysql_error($link);
			echo "<div class=\"error\">Create Service - Camera failed.($error)</div>";
		}
	}else{
		echo "<div class=\"error\">No Such Cloud Service ID!</div>";
	}
}

function updateCameraService($camera){
	$link = getLink();
	$sql = sprintf("UPDATE service_cameras SET cloudid=%s, serviceid=%s, model=%s, firmware=%s WHERE mac=%s",GetSQLValue($camera['cloudid'], 'text'),
			GetSQLValue($camera['serviceid'], 'text'),GetSQLValue($camera['model'], 'text'),GetSQLValue($camera['firmware'], 'text'),GetSQLValue($camera['mac'], 'text'));
	$b = mysql_query($sql,$link);
	if($b){
		echo "<div class=\"success\">Update Service - Camera successfully.</div>";
	}else{
		$error = mysql_error($link);
		echo "<div class=\"error\">Update Service - Camera failed. ($error)</div>";
	}
}

function removeByCloudID($cloudid){
	$link = getLink();
	$sql1 = sprintf("DELETE FROM service_users  WHERE cloudid= %s",GetSQLValue($cloudid, "text"));
	$sql2 = sprintf("DELETE FROM service_cameras  WHERE cloudid= %s",GetSQLValue($cloudid, "text"));

	mysql_query("START TRANSACTION");
	$b1 = mysql_query($sql1,$link);
	$b2 = mysql_query($sql2,$link);
	if($b1&$b2){
		mysql_query("COMMIT");
		echo "<div class=\"success\">Delete Service successfully.</div>";
	}else{
		mysql_query("ROLLBACK");
		echo "<div class=\"success\">Delete Service failed.</div>";
	}
}

function removeByCameraID($mac){
	$link = getLink();
	$sql = sprintf("DELETE FROM service_cameras  WHERE mac= %s",GetSQLValue($mac, "text"));

	$b = mysql_query($sql,$link);
	if($b){
		echo "<div class=\"success\">Delete Camera successfully.</div>";
	}else{
		$error = mysql_error($link);
		echo "<div class=\"error\">Delete Camera failed. ($error)</div>";
	}
}


