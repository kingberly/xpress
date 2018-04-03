<?php
// * update jquery library to local js
require_once '_auth_.inc';
require_once 'dataSourcePDO.php';

$camera = array();
$mac = '';
$mac = htmlspecialchars($_GET['mac'], ENT_QUOTES);
$camera = queryByCameraID($mac);

if(isset($_POST['mac'])){
	$camera['cloudid'] = trim(htmlspecialchars($_POST['cloudid'], ENT_QUOTES));
	$camera['mac'] = trim(htmlspecialchars($_POST['mac'], ENT_QUOTES));
	$camera['serviceid'] = trim(htmlspecialchars($_POST['serviceid'], ENT_QUOTES));
	$camera['model'] = trim(htmlspecialchars($_POST['model'], ENT_QUOTES));
	$camera['firmware'] = trim(htmlspecialchars($_POST['firmware'], ENT_QUOTES));
	updateCameraService($camera);
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
		<form id="createCameraForm" method="post"
			action="<?php echo $_SERVER['PHP_SELF']; ?>">
			<table>
				<tr>
					<th class="custom" colspan="2">Edit Camera Service</th>
				</tr>
				<tr>
					<td class="custom">Camera MAC</td>
					<td class="custom"><input type="text" name="mac" id="mac" size="50"
						value="<?php echo $camera['mac'];?>" readonly />
					</td>
				</tr>
				<tr>
					<td class="custom">Service ID - Cloud</td>
					<td class="custom"><input type="text" name="cloudid" id="cloudid"
						size="50" value="<?php echo $camera['cloudid'];?>" />
					</td>
				</tr>
				<tr>
					<td class="custom">Service ID - Camera</td>
					<td class="custom"><input type="text" name="serviceid"
						id="serviceid" size="50" value="<?php echo $camera['serviceid']?>" />
					</td>
				</tr>
				<tr>
					<td class="custom">Model</td>
					<td class="custom"><input type="text" name="model" id="model"
						size="50" value="<?php echo $camera['model'];?>" /></td>
				</tr>
				<tr>
					<td class="custom">Firmware</td>
					<td class="custom"><input type="text" name="firmware" id="firmware"
						size="50" value="<?php echo $camera['firmware'];?>" /></td>
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

	function submitCameraForm(){
		clearStyle();
		if(isEmpty($('#cloudid').val())){
			setInputWarning($('#cloudid'));
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


	function clearStyle(){
		$('#camera_cloudid').removeAttr('style');
		$('#mac').removeAttr('style');
		$('#serviceid').removeAttr('style');
		$('#model').removeAttr('style');
		$('#firmware').removeAttr('style');
		
		$('#customMessage').hide();
	}

	</script>
</body>
</html>
