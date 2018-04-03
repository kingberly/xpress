<?php
session_start();
include_once( "./include/global.php" );
include_once( "./include/index_title.php" );

if (isset($_SESSION['user_id'])) {
	if (isset($_SESSION["request_device_type"]) && $_SESSION["request_device_type"] == "" && 
		isset($_SESSION["request_service_type"]) && $_SESSION["request_service_type"] == "")
		header("Location: device_list.php?mode=personal");
	else
		header("Location: device_matrix.php?mode=personal&checkdevice=true");
}

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
<style>
.font_error{
	font-size:10px;
	color:#F00;
}
.diagnosis-message {font-family: Arial, Helvetica,sans-serif; font-size: 12px; display: none;}
#diagnosis-success {color: #5E8A38;}
#diagnosis-limited {color: #D69E05;}
#diagnosis-fail {color: #D95C47;}
#diagnosis-java {color: #D69E05;}
</style>
<!--Added New CSS-->
<link rel="stylesheet" href="css/normalize.css">
<link href="css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="css/main.css">
<link href='//fonts.googleapis.com/css?family=Source+Sans+Pro' rel='stylesheet' type='text/css'>	
	
<script type="text/javascript" src="js/login.js?20120827"></script>
<script type="text/javascript" src="js/diagnosis.js?20120700"></script>
<script type="text/javascript" src="js/deployJava.js?20120509"></script>
<script type="text/javascript" src="js/jquery.client.js"></script>
<script type="text/javascript" src="js/jQuery.XDomainRequest.js"></script>
<script type="text/javascript">	
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
		$('#login-btn').click(login);
		$('#reset-btn').click(function(){
			$('#name-edit').val('');
			$('#pwd-edit').val('');
		});
		$('#diagnosis-btn').click(function(){
			if ($(this).attr('disabled') == 'disabled')
				return;
			$('#msg').hide();
			loadDiagnosis(this, 'diagnosis-applet','.diagnosis-message', '#diagnosis-success', '#diagnosis-limited', '#diagnosis-fail', '#diagnosis-java', '#diagnosis-throbber',"",'<?php echo $oem_style_list['java_p2p_test_title']; ?>');
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
	
	<!--h2 class="form-signin-heading">PLEASE SIGN IN</h2-->
  <h2 class="form-signin-heading"><img src="images/login.png"><br><?php echo _("PLEASE SIGN IN");?></h2>
	
	<div class="form-group">
		<div class="input-group">
			<div class="input-group-addon"><span class="glyphicon glyphicon-user"></span></div>
			<input type="text" id="name-edit" maxlength="32" class="form-control" placeholder="<?php echo _("Username"); ?>" required="" autofocus="">
		</div>
	</div>
	<div class="form-group">
		<div class="input-group">
			<div class="input-group-addon"><span class="glyphicon glyphicon-lock"></span></div>
			<input type="password" id="pwd-edit"  maxlength="32" class="form-control" placeholder="<?php echo _("Password"); ?>" required="">
		</div>
	</div>
	
	
	
	<!--<div class="_login_attr_title " ><?php echo _("Username"); ?></div>-->
	
	<!--<div class="_login_attr_title"><?php echo _("Password"); ?></div>-->
	
	
	
	<button class="btn btn-lg btn-primary btn-block" type="submit" id="login-btn"><?php echo _("Login"); ?></button>
	<a class="btn" href="user_register.php"><?php echo _("Create Account"); ?></a>
	<a class="btn"  href="password_recovery.php"><?php echo _("Forgot your password?"); ?></a>

	
	
	
	<!--
	<div class="_login_attr_tps"><a href="password_recovery.php"><?php echo _("Forgot your password?"); ?></a></div>
	<div class="login_btn" id="login-btn"><?php echo _("Login"); ?></div>
	<a href="user_register.php"><div class="login_btn"><?php echo _("Create Account"); ?></div></a>-->
	
	<div class="login_btn" id="diagnosis-btn"><?php echo _("Network Test"); ?></div>
	<div class="login_btn" id="google-openid-btn" onclick="googlelogin();"><?php echo _("Google Login"); ?></div>
	<div class="login_error_msg" id="msg"></div>
	<span class="diagnosis-message" id="diagnosis-success"><?php echo _("Success! Your network will fully support " . $oem_style_list['diagnosis_title'] . " services."); ?></span>
	<span class="diagnosis-message" id="diagnosis-limited"><?php echo _("Limited connection! For better performance, please check your firewall settings."); ?></span>
	<span class="diagnosis-message" id="diagnosis-fail"><?php echo _("Not ready. please check your network connection."); ?></span>
	<span class="diagnosis-message" id="diagnosis-java"><?php echo _("Java is required to run network test."); ?></span>
	<span class="diagnosis-message" id="diagnosis-throbber"><img src="images/testing.gif" /></span>
	<div id="diagnosis-applet"></div>
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
        <script src="js/plugins.js"></script>
        <script src="js/main.js"></script>
        <script src="js/vendor/bootstrap.min.js"></script>
</body>
</html>
