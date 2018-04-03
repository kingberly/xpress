<?php
/****************
 *Validated on Oct-18,2017, Send Email via system
 * forgot pwd email
 * register resend
 * any content
 * test mis email   
 *Writer: JinHo, Chang   
*****************/
include("/var/www/qlync_admin/header.php");
include("/var/www/qlync_admin/menu.php");
if (!isset($_SESSION["ID_admin_qlync"])) die("No Permission");
if($_REQUEST["step"]=="test_email")
{
$params = array(
   	 'command' => 'test_current',
   	 'receiver' => $receiver,
   	 'oem_id' => $oem);
$url = $web_address . $path . '?' . http_build_query($params);
curl_setopt($ch, CURLOPT_URL, $url);
$result = curl_exec($ch);
$content_test_email=json_decode($result,true);

}else{
$web_address = "http://{$api_id}:{$api_pwd}@{$api_ip}:{$api_port}";
$path = '/manage/manage_oem.php';
$ch = curl_init();
define("COMPANY","");
}
function SendMail( $receiver_email, $title, $content, $attachments=array() )
{
	$mail = new Mail();
	$mail->Send($receiver_email, $title, $content, $attachments);
	return TRUE;
}
function GetProtocol() {
	//return $_SERVER['HTTPS']=='on'?'https://':'http://';
  return "https://";
}
function oem_id_to_host($oem_id) {

		return "";
}
function SendPasswordMail( $name, $recovery_code, $reg_email )
{
	global $oem;
	// server link , ex : sat.nuuolink.com
	$http_link = oem_id_to_host($oem);	
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
 
?>
<div class=container>
<table class=table_main><tr>
<td>
</td>
<td>

</td>
</tr></table>
</div>
</body>
</html>