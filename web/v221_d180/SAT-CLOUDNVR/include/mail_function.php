<?php
ini_set('default_charset', 'UTF-8');

function GetProtocol() {
	// protocol
	return $_SERVER['HTTPS']=='on'?'https://':'http://';
}

function http_post($url,$postData){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 0);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	$result = curl_exec($ch);
	if ($result === false) {
		throw new Exception(curl_error($ch));
	}
	return $result;
}

function SendEmailForIveda($to, $subject, $body, $name, $notification_email = false) {
	$postData['func'] = 'send';
	$postData['body'] = $body;
	$postData['subject'] = $subject;
	$postData['email'] = $to;
	$postData['name'] = $name;
	if (!$notification_email) {
		@include('config/notification_email.php');
	}

	if ($notification_email) {
		$url = "http://$notification_email/Mailer/mailer.php";
		$result = http_post($url, $postData);
	}
	else {
		$result = 'notiication e-mail server serveris not set';
	}

	return $result;
}

function SendMail( $receiver_email, $title, $content, $attachments=array() )
{
	$mail = new Mail();
	$mail->Send($receiver_email, $title, $content, $attachments);
	return TRUE;
}

function CompanyTitle($http_link){
	$http_link_pieces = explode(".",$http_link);
	if (isset($http_link_pieces[0]) && $http_link_pieces[0] == "sat-custom")
		return "SMAX";
	if (isset($http_link_pieces[1]) && $http_link_pieces[1] == "gsiit")
		return "GSI";
	if (isset($http_link_pieces[1]) && $http_link_pieces[1] == "nuuolink")
		return "NUUOLink";	
	return "";
}

function SendConfirmationMail( $auth_code, $name, $pwd, $reg_email )
{
	global $oem_style_list;
	// get directory
	$directory_path = dirname($_SERVER["PHP_SELF"]);
	
	// server link , ex : sat.nuuolink.com
	$http_link = oem_id_to_host($oem_style_list['oem_id']);
	// setup the link , ex : http://sat.nuuolink.com
	$auth_link = GetProtocol() . $http_link;
	//if( $directory_path != "/" ) $auth_link .= $directory_path;
	$auth_link .= "/user_register_authentication.php?auth_code=" . $auth_code . "&name=" . $name;

	// prepare email content
	$email_content = "<table style=\"width:500px\"><tr><td>" .
		sprintf(_("Your <a href='%s'>%s</a>/ account '%s' has been created."), $auth_link, $http_link, $name) . ' ' .
		_("You need to visit the confirmation address below within 48 hours to complete the account creation process:") .
		"<br><br>" .
		"<a href=\"" . $auth_link. "\">" . _("Please click to verify your registration.") . "</a><br>" .
		"<br>" .
		_("If you did not sign up for this account, this will be the only communication you will receive. All non-confirmed accounts are automatically deleted after 48 hours, and no addresses are kept on file. We apologize for any inconvenience this correspondence may have caused, and we assure you that it was only sent at the request of someone visiting our site requesting an account.") .
		"<br><br>" .
		_("Sincerely"). ",<br>" .
		_("The %BRAND% Team") . "<br>" . //"The SAT/SyncMe Team<br>"
		"<a href=\"$auth_link\">$auth_link</a><br>" .
		"%COMPANY%</td></tr></table>";

	// send email with authentication code
	$result = SendMail( $reg_email, CompanyTitle($http_link) . " " . _("%BRAND% User Registration"), $email_content );
	global $oem_style_list;
	return ($oem_style_list['confirm_user_reg'] == false || $result == true);
}

function SendPasswordMail( $name, $recovery_code, $reg_email )
{
	global $oem_style_list;
	// server link , ex : sat.nuuolink.com
	$http_link = oem_id_to_host($oem_style_list['oem_id']);	
	// setup the link , ex : http://sat.nuuolink.com
	$auth_link = GetProtocol() . $http_link;
	$auth_link .= "/password_recovery_authentication.php?recovery_code=" . $recovery_code . "&name=" . $name;
	// prepare email content
	$email_content =  "<table style=\"width:500px\"><tr><td>" .
		sprintf(_("Your <a href='%s'>%s</a>/ account is '%s'.<br>"), $http_link, $http_link, $name) .
		_("You need to visit the recovery address below within 24 hours to complete the password recovery process:") .
		"<br><br>" .
		"<a href=\"" . $auth_link. "\">" . _("Please click to begin the password recovery process.") . "</a><br>" .
		"<br>" .
		_("If you did not request for password recovery, this will be the only communication you will receive. We apologize for any inconvenience this correspondence may have caused, and we assure you that it was only sent at the request of someone visiting our site requesting password recovery.") . " <br><br>" .
		_("Sincerely") . ",<br>" .
		_("The %BRAND% Team") . "<br>" . //"The SAT/SyncMe Team<br>" .
		"<a href=\"$auth_link\">$auth_link</a><br>" .
		"%COMPANY%</td></tr></table>";

	// send email
	return SendMail( $reg_email, CompanyTitle($http_link) . " %BRAND% Password Recovery", $email_content );
}

function GetSupportEmail($oem_id) {
	$support_email = array();
	@include('config/support_email.php');
	return $support_email;
}
?>
