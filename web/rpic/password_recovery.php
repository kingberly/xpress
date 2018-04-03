<?php
include_once( "./include/global.php" );
//jinho added
include_once( "./include/index_title.php" );
define("OEM_ID",$oem_style_list['oem_id']);
if (OEM_ID=="X02")  define("IPv4_ADDR_PREFIX","192.168.");  
else define("IPv4_ADDR_PREFIX","192.168.1.");  
if (isset($_REQUEST['noCAPTCHA']))  define("isCAPTCHA",false);
else
  define("isCAPTCHA",true);
function enableCAPTCHA()
{
  if ( (strpos($_SERVER['REMOTE_ADDR'], IPv4_ADDR_PREFIX) !== FALSE) and ((strrpos($_SERVER['REMOTE_ADDR'], ".101") !== FALSE) or (strrpos($_SERVER['REMOTE_ADDR'], ".102") !== FALSE) or (strrpos($_SERVER['REMOTE_ADDR'], ".100") !== FALSE) ) ) //Load Balancer found
    return isCAPTCHA;
  else if ((explode(".",$_SERVER['REMOTE_ADDR'])[0]=="192") and (explode(".",$_SERVER['REMOTE_ADDR'])[1]=="168") )//jinho echo "bypass if ip is local";
    return false;
  return isCAPTCHA;
}
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
function isMobile()
{
	$useragent=$_SERVER['HTTP_USER_AGENT'];
if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)))
	return true;
else if (strstr($useragent,'iPhone') || strstr($useragent,'iPad') )
	return true;
else return false;
}

function printMobileMeta()
{
  $meta = "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0, user-scalable=yes, minimum-scale=1.0, maximum-scale=2.0\" />";
  echo $meta;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<?php
if (isMobile()){ 
  printMobileMeta();
  echo "<style type=\"text/css\">\n";
  echo "html *\n{font-size: 1.1em !important;\n}\n";
  echo ".act_1 \n{width:160px !important;height: 45px !important;background-size: cover !important;\n}\n"; 
  echo "</style>\n";
}
?>
<title>Forget Password</title>
<?php require( "./include/common_css_include.php" ); ?>
<?php require( "./include/common_js_include.php" ); ?>
<style type="text/css">
#password_recovery_notice {text-align: center; margin-top: 20px;}
</style>
<script src='//www.google.com/recaptcha/api.js?hl=<?php echo $google_language_map[$_SESSION["user_language"]];?>'></script>
<script type="text/javascript">
<?php 
if (enableCAPTCHA()){
?>
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
<?php } ?>  
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
//          if( data.status != "success" ) Alert( data.error_msg );
//          else
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

<?php 
if (enableCAPTCHA()){
?>
  $(function(){
    //SetupRecaptcha( "recaptcha-div" );
    $('#apply-btn').hide();
    $('#reset-btn').hide();
  });
<?php } ?>

</script>
</head>
<body>
<div id="password_recovery_list" class="left_list">
  <div id="password_recovery_config">
    <table>
      <tr>
<!---jinho add recaptcha v2---->
<?php 
if (enableCAPTCHA()){
?>
      <td colspan=2>
      <div class="g-recaptcha" data-sitekey="6LdcFzIUAAAAAAjnovxVbbaHleJ54RBhfObpWMrR" data-callback="vCAPTCHA2" data-expired-callback="vCAPTCHA2reset"></div>
      </td>
      </tr><tr>
<?php } ?>
<!----jinho add end---->
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