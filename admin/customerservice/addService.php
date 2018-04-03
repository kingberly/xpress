<?php
// * update jquery library to local js
require_once '_auth_.inc';
require_once 'dataSourcePDO.php';

$service = array();
$service['cloudid'] = '';
if(isset($_POST['cloudid'])){
	$service['cloudid'] = trim(htmlspecialchars($_POST['cloudid'], ENT_QUOTES));
	$service['account'] = trim(htmlspecialchars($_POST['account'], ENT_QUOTES));
	$service['email'] = trim(htmlspecialchars($_POST['email'], ENT_QUOTES));
	createCloudService($service);
}else if(isset($_POST['camera_cloudid'])){
	$service['cloudid'] = trim(htmlspecialchars($_POST['camera_cloudid'], ENT_QUOTES));
	$service['mac'] = trim(htmlspecialchars($_POST['mac'], ENT_QUOTES));
	$service['serviceid'] = trim(htmlspecialchars($_POST['serviceid'], ENT_QUOTES));
	$service['model'] = trim(htmlspecialchars($_POST['model'], ENT_QUOTES));
	$service['firmware'] = trim(htmlspecialchars($_POST['firmware'], ENT_QUOTES));
	createCameraService($service);
}

?>
<html>
<head>
<script src="../user_log/js/jquery-1.11.1.min.js"></script>
<link rel=stylesheet type="text/css" href="../user_log/js/style.css">
</head>
<body>
	<div id="container">
		<div style="display: none" id="customMessage"></div>
		<form id="createCloudForm" method="post"
			action="<?php echo $_SERVER['PHP_SELF']; ?>">
			<table>
				<tr>
					<th class="custom" colspan="2">Add Cloud Service</th>
				</tr>
				<tr>
					<td class="custom">Service ID - Cloud</td>
					<td class="custom"><input type="text" name="cloudid" id="cloudid"
						size="50" />
					</td>
				</tr>
				<tr>
					<td class="custom">Account Name</td>
					<td class="custom"><input type="text" name="account" id="account"
						size="50" />
					</td>
				</tr>
				<tr>
					<td class="custom">Email</td>
					<td class="custom"><input type="text" name="email" id="email"
						size="50" />
					</td>
				</tr>
				<tr>
					<td class="custom"></td>
					<td class="custom"><input type="button" value="Submit"
						onclick="submitCloudForm();" /><input type="button" value="Cancel"
						onclick="cancel();"></td>
				</tr>
			</table>
		</form>
		<form id="createCameraForm" method="post"
			action="<?php echo $_SERVER['PHP_SELF']; ?>">
			<table>
				<tr>
					<th class="custom" colspan="2">Add Camera Service</th>
				</tr>
				<tr>
					<td class="custom">Camera MAC</td>
					<td class="custom"><input type="text" name="mac" id="mac" size="50" />
					</td>
				</tr>
				<tr>
					<td class="custom">Service ID - Cloud</td>
					<td class="custom"><input type="text" name="camera_cloudid"
						id="camera_cloudid" size="50"
						value="<?php echo $service['cloudid'];?>" />
					</td>
				</tr>
				<tr>
					<td class="custom">Service ID - Camera</td>
					<td class="custom"><input type="text" name="serviceid"
						id="serviceid" size="50" /></td>
				</tr>
				<tr>
					<td class="custom">Model</td>
					<td class="custom"><input type="text" name="model" id="model"
						size="50" /></td>
				</tr>
				<tr>
					<td class="custom">Firmware</td>
					<td class="custom"><input type="text" name="firmware" id="firmware"
						size="50" /></td>
				</tr>
				<tr>
					<td class="custom"></td>
					<td class="custom"><input type="button" value="Submit"
						onclick="submitCameraForm();" /><input type="button"
						value="Cancel" onclick="cancel();"></td>
				</tr>
			</table>
		</form>

	</div>
	<script>
	function cancel(){
		window.location.href="showService.php";
	}
	function submitCloudForm(){
		clearStyle();
		if(isEmpty($('#cloudid').val())){
			setInputWarning($('#cloudid'));
			return false;
		}
		if(isEmpty($('#account').val())){
			setInputWarning($('#account'));
			return false;
		}
		if(isEmpty($('#email').val())){
			setInputWarning($('#email'));
			return false;
		}
		if(!validateEmail($('#email').val())){
			setInputWarning($('#email'));
			return false;
		}
		
		document.forms["createCloudForm"].submit();
	}

	function submitCameraForm(){
		clearStyle();
		if(isEmpty($('#camera_cloudid').val())){
			setInputWarning($('#camera_cloudid'));
			return false;
		}
		if(isEmpty($('#mac').val())){
			setInputWarning($('#mac'));
			return false;
		}
		if(isEmpty($('#serviceid').val())){
			setInputWarning($('#serviceid'));
			return false;
		}
		if(isEmpty($('#model').val())){
			setInputWarning($('#model'));
			return false;
		}
		if(isEmpty($('#firmware').val())){
			setInputWarning($('#firmware'));
			return false;
		}
		
		document.forms["createCameraForm"].submit();
		
	}
	
	function isEmpty(inputStr) { 
		if ( null == inputStr || "" == inputStr ) {
			return true; 
		} return false; 
	}
	
	function setInputWarning(input){
		input.css('border-style','solid');
		input.css('border-width','3px');
		input.css('border-color','red');
		input.focus();
		input.select();
	}

	function showCustomMessage(className,msg){
		alert('bbb');
		var obj = $('#customMessage');
		obj.attr("class", className);
		obj.html(msg);
		obj.show();
	}

	function clearStyle(){
		$('#cloudid').removeAttr('style');
		$('#camera_cloudid').removeAttr('style');
		$('#account').removeAttr('style');
		$('#email').removeAttr('style');
		$('#mac').removeAttr('style');
		$('#serviceid').removeAttr('style');
		$('#model').removeAttr('style');
		$('#firmware').removeAttr('style');
		
		$('#customMessage').hide();
	}

	function validateEmail(email) {
		  regularExpression = /^[^\s]+@[^\s]+\.[^\s]{2,3}$/;
		  if (regularExpression.test(email)) {
		      return true;
		  }else{
		      return false;
		  }
	}
	</script>
</body>
</html>
