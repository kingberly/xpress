<?php
include_once( "./include/global.php" );
$google_language_map = array(
	"en_US" => "en",
	"zh_CN" => "zh-CN",
	"zh_TW" => "zh-TW",
  "cs_CZ" => "cs",
  "de_DE" => "de",
  "fr_FR" => "fr",
  "es_ES" => "es",
  "es_US" => "es-419",
  "it_IT" => "it",
	"ja_JP" => "ja",
  "ko_KR" => "ko",
  "nl_NL" => "nl",
  "pl_PL" => "pl",
  "ru_RU" => "ru",
  "tr_TR" => "tr",
  "vi_VN" => "vi"
);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Forget Password</title>
<?php require( "./include/common_css_include.php" ); ?>
<?php require( "./include/common_js_include.php" ); ?>
<style type="text/css">
#password_recovery_notice {text-align: center; margin-top: 20px;}
</style>
<script src='//www.google.com/recaptcha/api.js?hl=<?php echo $google_language_map[$_SESSION["user_language"]];?>'></script>
<script type="text/javascript">
var vCAPTCHA2 = function(response) 
{  //alert(response);
  if (response.length > 0){
    $('#apply-btn').show();
    $('#reset-btn').show();
  }
};
var vCAPTCHA2reset = function() 
{
    $('#apply-btn').hide();
    $('#reset-btn').hide();
};  
	function ApplyPasswordRecovery()
	{
  //jinho add check
    if ($('input#name-edit').val().length < 4)
      return Alert("<?php echo _("Please enter a valid name.");?>");

		// prepare parameter lsit
		var parameter_array = {
			command: "password_recovery",				
			name: $.trim($('input#name-edit').val()),
			txtCaptcha : $('input#txtCaptcha').val()
			/*recaptcha_challenge: Recaptcha.get_challenge(),
			recaptcha_response: Recaptcha.get_response()*/
		};

		// black screen
		applyBegin();

		$.getJSON(
			"backstage_user_register.php",
			parameter_array,
			function( data )
			{
				if( data != null )
				{//jinho fix security, always return success, do not provide error
//					if( data.status != "success" ) Alert( data.error_msg );
//					else
					{
						$('div#password_recovery_config').hide();
						$('div#password_recovery_notice').show();
					}
				}
				
				// reset recaptcha
				//Recaptcha.reload();
				setTimeout('refreshimg()', 300);

				// remove black screen
				applyEnd();
			}
		);	
	}

	$(function(){
		//SetupRecaptcha( "recaptcha-div" );
    $('#apply-btn').hide();
    $('#reset-btn').hide();
	});


</script>
</head>
<body>
<div id="password_recovery_list" class="left_list">
	<div id="password_recovery_config">
		<table>
			<tr>
      <td><!---jinho add recaptcha v2---->
      <div class="g-recaptcha" data-sitekey="6LdcFzIUAAAAAAjnovxVbbaHleJ54RBhfObpWMrR" data-callback="vCAPTCHA2" data-expired-callback="vCAPTCHA2reset"></div>
      </td>
				<td><div id=apply-btn class="act_1" onClick='ApplyPasswordRecovery();' /><?php echo _("Apply");?></div></td>
				<td><div id=reset-btn class="act_1" onClick='$("table#user_config_table input").val("");' /><?php echo _("Reset");?></div></td>
			</tr>
		</table>
		<div class="description">
			<?php echo _("If you have lost your account password, simply enter your username below.") . "<br>" . 
					_("The instructions will be sent to you register email.") . "<br>";?>
		</div>
		<table class="layouttable_2" border="1" cellspacing="1" cellpadding="1" id="user_config_table">
			<tr>
				<th width="100" scope="col"><?php echo _("Property");?></th>
				<th width="100" scope="col"><?php echo _("Content");?></th>
			</tr>
			<tr class="even">
				<td><?php echo _("Email or Username");?></td>
				<td>
					<input type="text" class="input_text" id="name-edit" size="20" maxlength="128" />
				</td>
			</tr>
		</table>
	</div>
	<div id="password_recovery_notice" style="display:none;">
		<p><b><?php echo _("You will receive an e-mail from us with instructions for resetting your password.");?></b></p>
		<p><?php echo _("If you don't receive this e-mail, please check your junk mail folder.");?></p>
	</div>
</div>
<?php include_once("./include/tail.php"); ?>
</body>
</html>
