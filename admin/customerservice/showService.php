<?php
/****************
 *Validated on Apr-29,2016,
 * update jquery library to local js
 * add new database table vendor_info     
 *Writer: JinHo, Chang
*****************/

require_once 'dataSourcePDO.php';
require_once '_auth_.inc';

$vendorid =checkVendor($_SESSION["Email"]); //return empty for pldt
$cloudid = $vendorid;
//$cloudid = '';
$services = null;
if(isset($_POST['form-type'])){
	$type = htmlspecialchars($_POST['form-type'], ENT_QUOTES);
	$cloudid = htmlspecialchars($_POST['search-cloudid'], ENT_QUOTES);
	if($type=='add-service'){
		$service['cloudid'] = trim(htmlspecialchars($_POST['add-cloudid'], ENT_QUOTES));
		$service['account'] = trim(htmlspecialchars($_POST['add-account'], ENT_QUOTES));
		$service['email'] = trim(htmlspecialchars($_POST['add-email'], ENT_QUOTES));
		$cloudid = $service['cloudid'];
  if (strpos($cloudid, $vendorid) !== false)  //added for $vendorid
		$b = createCloudService($service);
  else $b=false;
		if($b){
			echo "<div id=\"info\" class=\"success\">Create Service - Cloud successfully.</div>";
		}else{
			echo "<div id=\"info\" class=\"error\">Create Service - Cloud failed.</div>";
		}
	}else if($type=='delete-service'){
		$deleteCloudID = htmlspecialchars($_POST['delete-cloudid'], ENT_QUOTES);
		$b = removeByCloudID($deleteCloudID);
		if($b){
			echo "<div id=\"info\" class=\"success\">Delete Service successfully.</div>";
		}else{
			echo "<div id=\"info\" class=\"success\">Delete Service failed.</div>";
		}
	}else if($type=='update-service'){
		$service['cloudid'] = htmlspecialchars($_POST['update-cloudid'], ENT_QUOTES);
		$service['account'] =  trim(htmlspecialchars($_POST['update-account'], ENT_QUOTES));
		$service['email'] =  trim(htmlspecialchars($_POST['update-email'], ENT_QUOTES));
		$b = updateCloudService($service);
		if($b){
			echo "<div id=\"info\" class=\"success\">Update Service - Cloud successfully.</div>";
		}else{
			echo "<div id=\"info\" class=\"error\">Update Service - Cloud failed.</div>";
		}
	}else if($type=='add-camera'){
		$service['cloudid'] = trim(htmlspecialchars($_POST['add-camera-cloudid'], ENT_QUOTES));
		$service['mac'] = trim(htmlspecialchars($_POST['add-mac'], ENT_QUOTES));
		$service['serviceid'] = trim(htmlspecialchars($_POST['add-camera-service'], ENT_QUOTES));
		$service['model'] = trim(htmlspecialchars($_POST['add-model'], ENT_QUOTES));
		$service['firmware'] = trim(htmlspecialchars($_POST['add-firmware'], ENT_QUOTES));
		$b = createCameraService($service);
		if($b){
			echo "<div id=\"info\" class=\"success\">Create Service - Camera successfully.</div>";
		}else{
			echo "<div id=\"info\" class=\"error\">Create Service - Camera failed.</div>";
		}
	}else if($type=='delete-camera'){
		$deleteCameraID = htmlspecialchars($_POST['delete-mac'], ENT_QUOTES);
		$b = removeByCameraID($deleteCameraID);
		if($b){
			echo "<div id=\"info\" class=\"success\">Delete Camera successfully.</div>";
		}else{
			echo "<div id=\"info\" class=\"error\">Delete Camera failed</div>";
		}
	}else if($type=='update-camera'){
		$camera['mac'] = trim(htmlspecialchars($_POST['update-mac'], ENT_QUOTES));
		$camera['serviceid'] = trim(htmlspecialchars($_POST['update-camera-service'], ENT_QUOTES));
		$camera['model'] = trim(htmlspecialchars($_POST['update-model'], ENT_QUOTES));
		$camera['firmware'] = trim(htmlspecialchars($_POST['update-firmware'], ENT_QUOTES));
		$b = updateCameraService($camera);
		if($b){
			echo "<div id=\"info\" class=\"success\">Update Service - Camera successfully.</div>";
		}else{
			echo "<div id=\"info\" class=\"error\">Update Service - Camera failed.</div>";
		}
	}
}


if(isset($_POST['cloudid'])){
	$cloudid = htmlspecialchars($_POST['cloudid'], ENT_QUOTES);
	if($cloudid!='')
    if (isset($_REQUEST["debugadmin"])) 
      $services = queryAll();
    else
		$services = query($cloudid);
}else if (isset($_REQUEST["debugadmin"])){
  	$services = queryAll();
}else{
	if($cloudid!='')
		$services = query($cloudid);
}
//added by Jinho
function checkVendor($email)
{
    $sql = "select * from customerservice.vendor_info where Email='{$email}'";
    $link = getLink();
    $result=mysql_query($sql,$link);
    if (mysql_num_rows($result) >0){
       myfetch($arr,$result,0,0);
       return $arr[cloudprefix];
    }else return "";
}
//special command <all>
function queryAll(){
	$link = getLink();
	$sql = "select * from service_users as u inner join customerservice.service_cameras as c on u.cloudid=c.cloudid";
	$result=mysql_query($sql,$link);
	$services = array();
	$tmp = '';
	$index = 0;
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
		$services[$index] = $arr;
	}
	return $services;
}

function createServiceTable($services){
	if($services==null)
		return;
	$html = '';
	foreach($services as $service){
		$html.="\n<tr>";
		$account = $service['account'];
		$email = $service['email'];
		$cloudid = $service['cloudid'];

		// mac
		$arr = explode(',', $service['mac']);
		$mac = '';
		foreach($arr as $item){
			$mac_js = '\''.$item.'\'';
			$delHtml = "<a href=\"#\" onclick=\"removeCamera($mac_js);\">Delete</a>";
			$editHtml = "<a href=\"editCamera.php?mac=$item\">$item</a>";
			$mac.= $editHtml.' '.$delHtml."<br/>";
		}

		// serviceid
		$serviceid = '';
		$arr = explode(',', $service['serviceid']);
		foreach($arr as $item){
			$serviceid.=$item."<br/>";
		}

		//model
		$model = '';
		$arr = explode(',', $service['model']);
		foreach($arr as $item){
			$model.=$item."<br/>";
		}

		// fw
		$firmware = '';
		$arr = explode(',', $service['firmware']);
		foreach($arr as $item){
			$firmware.=$item."<br/>";
		}
		$cloudid_js = '\''.$cloudid.'\'';

		//$html.="\n<td style=\"text-align: center\"><input type=\"checkbox\" class=\"chk\" name=\"selectedServices[]\" value=\"$cloudid\"></td>";
		$html.="\n<td>$account</td>";
		$html.="\n<td>$email</td>";
		$html.="\n<td><a href=\"editService.php?cloudid=$cloudid\">$cloudid</a> <a href=\"#\" onclick=\"removeService($cloudid_js);\">Delete</a></td>";
		$html.="\n<td>$mac </td>";
		$html.="\n<td>$serviceid</td>";
		$html.="\n<td>$model</td>";
		$html.="\n<td>$firmware</td>";
		$html.="</tr>";

	}
	echo $html;
}

function createServiceTable2($services){
	$html='';
	$page = $_SERVER['PHP_SELF'];
	if(count($services)!=0){
		foreach($services as $service){
			$html.="\n<tr>";
			$account = $service['account'];
			$email = $service['email'];
			$cloudid = $service['cloudid'];

			$macArr = explode(',', $service['mac']);
			$cameraCount = count($macArr);
			if($macArr[0]=='')
				$cameraCount = 0;
			$cameraRows = $cameraCount + 2;

			// render html
			$cloudid_js = '\''.$cloudid.'\'';
			$html.="\n<td rowspan=\"$cameraRows\"  class=\"td-custom\"><input type=\"text\" id=\"$cloudid-account\" value=\"$account\" size=\"10px\"></td>";
			$html.="\n<td rowspan=\"$cameraRows\"><input type=\"text\" id=\"$cloudid-email\" value=\"$email\" size=\"10px\"></td>";
			$html.="\n<td rowspan=\"$cameraRows\">$cloudid</td>";
			$html.="\n<td rowspan=\"$cameraRows\" class=\"custom\"><input type=\"button\" value=\"Update\" onclick=\"updateService($cloudid_js)\"><input
			type=\"button\" value=\"Delete\" onclick=\"removeService($cloudid_js);\"></td>";
			$html.="</tr>";


			$modelArr = explode(',', $service['model']);
			$firmArr = explode(',', $service['firmware']);
			$serviceArr =  explode(',', $service['serviceid']);
			for($i=0;$i<$cameraCount;$i++){
				$mac =$macArr[$i];
				$html.= "\n<tr>";
				$html.= "\n<td>$mac</td>";
				$mac_js = '\''.$mac.'\'';
				$html.= "\n<td><input type=\"text\" id=\"$mac-camera-service\" value=\"$serviceArr[$i]\" size=\"10px\"></td>";
				$html.= "\n<td><input type=\"text\" id=\"$mac-model\" value=\"$modelArr[$i]\" size=\"5px\"></td>";
				$html.= "\n<td><input type=\"text\" id=\"$mac-firmware\" value=\"$firmArr[$i]\" size=\"15px\"></td>";
				$html.= "\n<td class=\"custom\"><input type=\"button\" value=\"Update\" onclick=\"updateCamera($mac_js);\"><input
				type=\"button\" value=\"Delete\" onclick=\"removeCamera($mac_js);\"></td>";
				$html.= "\n</tr>";
			}

			// Add camera
			$html.= "\n<tr><td><input type=\"text\" id=\"$cloudid-mac\" value=\"\" size=\"10px\"></td>";
			$html.= "\n<td><input type=\"text\" id=\"$cloudid-camera-service\" value=\"\" size=\"10px\"></td>";
			//$html.= "\n<td><input type=\"text\" id=\"$cloudid-model\" value=\"\" size=\"10px\"></td>";
			//$html.= "\n<td><input type=\"text\" id=\"$cloudid-firmware\" value=\"\" size=\"10px\"></td>";
      $html.="\n<td><Select id=\"$cloudid-model\"><Option value=\"\"></Option><Option value=\"Simple\">Simple</Option><Option value=\"Night\">Night</Option><Option value=\"Outdoor\">Outdoor</Option><Option value=\"Pan/Tilt\">Pan/Tilt</Option></Select></td>";
      $html.="\n<td><Select id=\"$cloudid-firmware\"><Option value=\"\"></Option><Option value=\"M2.1.6.05Z008_P04\">M2.1.6.05Z008_P04</Option></Select></td>";
			$html.= "\n<td><input type=\"button\" value=\"Add\" onclick=\"addCamera($cloudid_js);\" size=\"10px\"></td></tr>";
		}
	}

	// Add Service row
global $vendorid;
	$html.= "\n<tr><td><input type=\"text\" name=\"account\" id=\"account\" value=\"\" size=\"10px\"></td>";
	$html.= "\n<td><input type=\"text\" name=\"email\" id=\"email\" value=\"\" size=\"10px\"></td>";
	$html.= "\n<td><input type=\"text\" name=\"add-serviceid\" id=\"add-serviceid\" value=\"$vendorid\" size=\"10px\"></td>";
	$html.= "\n<td><input type=\"button\" value=\"Add\" onclick=\"addService();\"></td></tr></form>";

	echo $html;
}
?>
<html>
<head>
<script src="../user_log/js/jquery-1.11.1.min.js"></script>
<link rel=stylesheet type="text/css" href="../user_log/js/style.css">
</head>
<body>
	<div style="display: none" id="customMessage"></div>
	<div id="container">
<?php if($_SESSION["ID_admin_oem"] or $_SESSION["ID_admin_qlync"]) {?>
<a href='addVendor.php'>Mgmt Vendor</a><br>
<?php }?>
		<form id="searchForm" method="post"
			action="<?php echo $_SERVER['PHP_SELF']; ?>">
			Service ID - Cloud&nbsp;&nbsp;<input type="text" size="30"
				name="cloudid" id="cloudid" value="<?php echo $cloudid?>">&nbsp;&nbsp;
			<input type="submit" value="Search">
		</form>
		<form id="removeForm" method="post"
			action="<?php echo $_SERVER['PHP_SELF']; ?>">
			<input type="hidden" name="action" value="remove"> <input
				type="hidden" id="removeCloudID" name="removeCloudID" value=""> <input
				type="hidden" id="removeCameraID" name="removeCameraID" value=""> <input
				type="hidden" name="cloudid" value="<?php echo $cloudid;?>">
			<table style="margin-top: 15px" id="tbl">
				<thead>
					<tr>
						<th>Account Name</th>
						<th>Email Address</th>
						<th>Service ID - Cloud</th>
						<th>Action</th>
						<th>Camera MAC</th>
						<th>Service ID - Camera</th>
						<th>Model</th>
						<th>Firmware</th>
						<th>Action</th>

					</tr>
				</thead>
				<tbody>
					<?php createServiceTable2($services)?>
				</tbody>
			</table>
		</form>
	</div>

	<form id="add-service" method="post"
		action="<?php echo $_SERVER['PHP_SELF']; ?>">
		<input type="hidden" name="form-type" value="add-service"> <input
			type="hidden" name="add-email" id="add-email"> <input type="hidden"
			name="add-account" id="add-account"> <input type="hidden"
			name="add-cloudid" id="add-cloudid"> <input type="hidden"
			name="search-cloudid" value="<?php echo $cloudid;?>">
	</form>

	<form id="delete-service" method="post"
		action="<?php echo $_SERVER['PHP_SELF']; ?>">
		<input type="hidden" name="form-type" value="delete-service"> <input
			type="hidden" name="delete-cloudid" id="delete-cloudid"> <input
			type="hidden" name="search-cloudid" value="<?php echo $cloudid;?>">
	</form>
	<form id="update-service" method="post"
		action="<?php echo $_SERVER['PHP_SELF']; ?>">
		<input type="hidden" name="form-type" value="update-service"> <input
			type="hidden" name="update-cloudid" id="update-cloudid"><input
			type="hidden" name="update-account" id="update-account"> <input
			type="hidden" name="update-email" id="update-email"> <input
			type="hidden" name="search-cloudid" value="<?php echo $cloudid;?>">
	</form>
	<form id="add-camera" method="post"
		action="<?php echo $_SERVER['PHP_SELF']; ?>">
		<input type="hidden" name="form-type" value="add-camera"><input
			type="hidden" name="add-camera-cloudid" id="add-camera-cloudid"> 
      <input type="hidden" name="add-mac" id="add-mac"> 
      <input type="hidden" name="add-camera-service" id="add-camera-service"> 
      <input type="hidden" name="add-model" id="add-model">
      <input type="hidden" name="add-firmware" id="add-firmware">

      <input type="hidden" name="search-cloudid" value="<?php echo $cloudid;?>">
	</form>
	<form id="delete-camera" method="post"
		action="<?php echo $_SERVER['PHP_SELF']; ?>">
		<input type="hidden" name="form-type" value="delete-camera"> <input
			type="hidden" name="delete-mac" id="delete-mac"> <input type="hidden"
			name="search-cloudid" value="<?php echo $cloudid;?>">
	</form>

	<form id="update-camera" method="post"
		action="<?php echo $_SERVER['PHP_SELF']; ?>">
		<input type="hidden" name="form-type" value="update-camera"> <input
			type="hidden" name="update-mac" id="update-mac"><input type="hidden"
			name="update-camera-service" id="update-camera-service"> <input
			type="hidden" name="update-model" id="update-model"><input
			type="hidden" name="update-firmware" id="update-firmware"> <input
			type="hidden" name="search-cloudid" value="<?php echo $cloudid;?>">
	</form>

	<script>
$(document).ready(function() {
	 $('#cloudid').focus();
	 $('#cloudid').select();
	});

var checkboxes = $("input[type='checkbox']"),
submitButt = $("#removeBtn");
checkboxes.click(function() {
submitButt.attr("disabled", !checkboxes.is(":checked"));
});

function add(){
	window.location.href="addService.php";
}

function updateService(cloudid){
	hideMessage();
	var account = $('#'+cloudid+"-account").val();
	var email = $('#'+cloudid+"-email").val();
	$('#update-cloudid').val(cloudid);
	$('#update-account').val(account);
	$('#update-email').val(email);
	$('#update-service').submit();
}

function removeService(cloudid){
	if (window.confirm('Are you sure to delete service \''+cloudid+'\' ?') == true) {
		hideMessage();
		$('#delete-cloudid').val(cloudid);
		$('#delete-service').submit();
	}
}

function removeCamera(mac){
	if (window.confirm('Are you sure to delete camera \''+mac+'\' ?') == true) {
		hideMessage();
		$('#delete-mac').val(mac);
		$('#delete-camera').submit();
	}
}

function addService(){
	hideMessage();
	var cloudid = $('#add-serviceid').val();
	var email = $('#email').val();
	var account = $('#account').val();
	if(isEmpty(cloudid)){
		showCustomMessage('error','Service ID - Cloud can not be empty.')
		return;
	}
	$('#add-cloudid').val(cloudid);
	$('#add-email').val(email);
	$('#add-account').val(account);
	
	if(!validateEmail(email)){
		showCustomMessage('error','Email format is incorrect.')
		return;
	}
	$('#add-service').submit();
	
}

function addCamera(cloudid){
	hideMessage();
	var macValue = $('#'+cloudid+"-mac").val();
	if(isEmpty(macValue)){
		showCustomMessage('error','Camera Mac can not be empty.')
		return;
	}
	
	var mac = $('#'+cloudid+"-mac").val();
	var service = $('#'+cloudid+"-camera-service").val();
	var model = $('#'+cloudid+"-model").val();
	var firmware = $('#'+cloudid+"-firmware").val();
	$('#add-camera-cloudid').val(cloudid);
	$('#add-mac').val(mac);
	$('#add-camera-service').val(service);
	$('#add-model').val(model);
	$('#add-firmware').val(firmware);
	$('#add-camera').submit();
}

function updateCamera(mac){
	hideMessage();
	var service = $('#'+mac+'-camera-service').val();
	var model = $('#'+mac+'-model').val();
	var firmware = $('#'+mac+'-firmware').val();
	$('#update-mac').val(mac);
	$('#update-camera-service').val(service);
	$('#update-model').val(model);
	$('#update-firmware').val(firmware);
	$('#update-camera').submit();
}

function isEmpty(inputStr) { 
	if ( null == inputStr || "" == inputStr ) {
		return true; 
	} return false; 
}

function showCustomMessage(className,msg){
	var obj = $('#customMessage');
	obj.attr("class", className);
	obj.html(msg);
	obj.show();
}

function validateEmail(email) {
	  regularExpression = /^[^\s]+@[^\s]+\.[^\s]{2,3}$/;
	  if (regularExpression.test(email)) {
	      return true;
	  }else{
	      return false;
	  }
}

function hideMessage(){
	$('#info').hide();
	$('#customMessage').hide();
}




</script>

</body>

</html>
