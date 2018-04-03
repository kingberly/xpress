<?php
// * update jquery library to local js
require_once '_auth_.inc';
require_once 'dataSourcePDO.php';

$service = array();

$serviceID = htmlspecialchars($_GET['cloudid'], ENT_QUOTES);

$service = queryByCloudID($serviceID);

if(isset($_POST['cloudid'])){
	$service['cloudid'] = trim(htmlspecialchars($_POST['cloudid'], ENT_QUOTES));
	$service['account'] =  trim(htmlspecialchars($_POST['account'], ENT_QUOTES));
	$service['email'] =  trim(htmlspecialchars($_POST['email'], ENT_QUOTES));
	updateCloudService($service);
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
					<th class="custom" colspan="2">Edit Cloud Service</th>
				</tr>
				<tr>
					<td class="custom">Service ID - Cloud</td>
					<td class="custom"><input type="text" name="cloudid" id="cloudid"
						size="50" readonly value="<?php echo $service['cloudid']?>" />
					</td>
				</tr>
				<tr>
					<td class="custom">Account Name</td>
					<td class="custom"><input type="text" name="account" id="account"
						size="50" value="<?php echo $service['account']?>" />
					</td>
				</tr>
				<tr>
					<td class="custom">Email</td>
					<td class="custom"><input type="text" name="email" id="email"
						size="50" value="<?php echo $service['email']?>" />
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
		$('#cloudid').removeAttr('style');
		$('#account').removeAttr('style');
		$('#email').removeAttr('style');
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
