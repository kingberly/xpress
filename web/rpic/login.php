<?php
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
  else if ((explode(".",$_SERVER['REMOTE_ADDR'])[0]=="192") and (explode(".",$_SERVER['REMOTE_ADDR'])[1]=="168") ) //jinho echo "bypass if ip is local";
    return false;
 return isCAPTCHA;
}
//end of jinho add
session_start();
include_once( "./include/global.php" );
include_once( "./include/index_title.php" );
include_once( './include/login.php' );

if (FORCE_HTTPS && !$_SERVER['HTTPS']) {
  header( 'Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ) ;
  exit(0);
}

if (isset($_SESSION['user_id'])) {
  loginProcedure($_SESSION['user_id']);
  if (isset($_SESSION["request_device_type"]) && $_SESSION["request_device_type"] == "" &&
    isset($_SESSION["request_service_type"]) && $_SESSION["request_service_type"] == "") {

    exit(header("Location: device_list.php?mode=personal"));
    }

    if (!empty($_GET['oem-url'])) {
    exit(header("Location: " . str_replace('.', '', $_GET['oem-url']) . "device_matrix.php?mode=personal&checkdevice=true"));
    }

    exit(header("Location: device_matrix.php?mode=personal&checkdevice=true"));
}
//jinho add for recapcha
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
<!--[if lt IE 7 ]> <html class="ie6"> <![endif]-->
<!--[if IE 7 ]>    <html class="ie7"> <![endif]-->
<!--[if IE 8 ]>    <html class="ie8"> <![endif]-->
<!--[if IE 9 ]>    <html class="ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html class=""> <!--<![endif]-->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title><?php echo $oem_style_list['title']; ?> - Login</title>
<?php require( "./include/common_css_include.php" ); ?>
<?php require( "./include/common_js_include.php" ); ?>
<link href="<?php echo $oem_style_list['css']['login']; ?>" rel="stylesheet" type="text/css" charset="utf-8">
<script type="text/javascript" src="js/jquery.client.js"></script>
<script defer type="text/javascript" src="js/login.js?20120827"></script>
<script type="text/javascript" src="js/deployJava.js?20120509"></script>
<script type="text/javascript" src="js/jQuery.XDomainRequest.js"></script>
<!-----jinho add for captcha---->
<script src='//www.google.com/recaptcha/api.js?hl=<?php echo $google_language_map[$_SESSION["user_language"]];?>'></script>
<script type="text/javascript">
var isCAPTCHA = false;
var vCAPTCHA2 = function(response) 
{  //alert(response);
  if (response.length > 0){
     isCAPTCHA = true;
  }
};
var vCAPTCHA2reset = function() 
{
  isCAPTCHA = false;
};
function mylogin()
{
  var err = "<?php echo _('Security image verify failed.');?>"; 
  if (!isCAPTCHA)
    return $('#msg').text( err);  
  login();
}
//add end
  var p2p_jars = "<?php echo $oem_style_list['p2p_jars']; ?>";
  var oem_id = "<?php echo $oem_style_list['oem_id']; ?>";

  $(function(){
    $('#name-edit').focus();
    $('#name-edit').keyup(checkUsername);
    $('#pwd-edit').keyup(checkPwd);
    $('#pwd-edit').keypress(function(event) {
      if (event.keyCode == '13') {
        $('#login-btn').click();
      }
    });
<?php 
if (enableCAPTCHA()){
?>    
    $('#login-btn').click(mylogin); //change to verify reCAPTCHA
<?php }else echo "    $('#login-btn').click(login);";?>
    $('#reset-btn').click(function(){
      $('#name-edit').val('');
      $('#pwd-edit').val('');
    });
    unlock();

    $('.login_btn').hover(function(){
      if ($(this).attr('disabled')=="disabled") return false;
      $(this).addClass('login_btn_mover');
    },function(){
      $(this).removeClass('login_btn_mover');
    });

    if ($.client.browser=="Safari"){ //Safari Detect
      //$('#name-edit').attr('disabled',true);
      //$('#pwd-edit').attr('disabled',true);
    }
    if ("<?php echo $_GET['mode']; ?>" == "google")
      $('#google-openid-btn').click();

  });
</script>
</head>
<body>
<div class="H179 bg_w">
<div class='_login_frame'>
  <div class="_login_attr_title"><?php echo _("Username"); ?></div>
  <input type="text" id="name-edit" class="_login_attr" maxlength="32" />
  <div class="_login_attr_title"><?php echo _("Password"); ?></div>
  <input type="password" id="pwd-edit" class="_login_attr" autocomplete="off" maxlength="32" />
  <div class="_login_attr_tps"><a href="password_recovery.php"><?php echo _("Forgot your password?"); ?></a></div>

<!---jinho add recaptcha v2---->
<?php 
if (enableCAPTCHA()){
?>
<div style='display:block;margin: 20px auto;width: 150px;'>
<div class="g-recaptcha" data-sitekey="6LdcFzIUAAAAAAjnovxVbbaHleJ54RBhfObpWMrR" data-callback="vCAPTCHA2" data-expired-callback="vCAPTCHA2reset" data-theme="light" style="transform:scale(0.8);transform-origin:0 0"></div>
</div>
<?php } ?>
<!----jinho add end---->

  <div class="login_btn" id="login-btn"><?php echo _("Login"); ?></div>
  <?php
    if ($oem_style_list['user_register']) {
      echo '<a href="user_register.php"><div class="login_btn">' . _("Create Account") . "</div></a>\n";
    }
  ?>

  <div class="login_btn" id="google-openid-btn" onclick="googlelogin();"><?php echo _("Google Login"); ?></div>
<!---jinho add------>
<?php   if ($oem_style_list['oem_id'] == "X02"){ ?>
<div style='display:block;margin: 20px auto;width: 170px;'>
  <div style='float:left;' class="act_1" id="workeyemap-btn" onclick="window.open('http://workeyemap.megasys.com.tw/map/rpic_map.php?key=KZo3i6UJbKd0bb6B5Suv','WorkeyeMap','width=800,height=600,resizable=1,scrollbars=yes');"><?php echo _("Map"); ?></div>
  <div style='float:right;' class="act_1" id="download-btn" onclick="window.open('/download');"><?php echo _("下載"); ?></div>
</div>
<?php } ?>
  <div class="login_error_msg" id="msg"></div>
</div>
<div class='ad_area'>
  <div class="bg_ad_top"></div>
    <div class="bg_ad_mid">
      <div class="ad_content" id="ad_content">
      </div>
    </div>
  <div class="bg_ad_btm"></div>
</div>
</div>
<?php include_once("./include/tail.php"); ?>
</body>
</html>