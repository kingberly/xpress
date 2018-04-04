<?php
include_once( "./include/global.php" );
include_once( "./include/utility.php" );
include_once( "./include/index_title.php" );
if (!IsUserLoggedIn()) {header('Location: login.php'); exit;}
//$aURL=explode (":",$_SERVER['HTTP_HOST']);$myURL=$aURL[0];

//$myURL = "https://".$_SERVER['HTTP_HOST'].":8080/doc/";
$myURL = "https://xpress.megasys.com.tw:8080/doc/";

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Support</title>
<?php require( "./include/common_css_include.php" ); ?>
	<!--Added New CSS-->
<link href="css/bootstrap.min.css" rel="stylesheet">
<link href="css/stick-footer.css" rel="stylesheet">
<link rel="stylesheet" href="css/normalize.css">
<link rel="stylesheet" href="css/main.css">
<?php require( "./include/common_js_include.php" ); ?>
<link href="css/device_list.css" rel="stylesheet" type="text/css" charset="utf-8">
<link href="<?php echo $oem_style_list['css']['pagelist']; ?>" rel="stylesheet" type="text/css" charset="utf-8">
<link href="shadowbox-3.0.3/shadowbox.css" rel="stylesheet" type="text/css" charset="utf-8">
<script type="text/javascript" src="js/panel.js"></script>
<script type="text/javascript" src="js/googleRelay.js?20120522"></script>
<script type="text/javascript" src="js/device_common.js?20140331"></script>
<script type="text/javascript" src="js/device_list.js?20120523"></script>
<script type="text/javascript" src="shadowbox-3.0.3/shadowbox.js"></script>
<script type="text/javascript" src="js/pagelist.js"></script>

<!--Added New JS -->
<script src="js/vendor/modernizr-2.6.2.min.js"></script>

</head>
<body>
    <div id="support_container" >
 
        <div id='header' >
            <h2 class="page-header">Support</h1>
        </div>
        <div id='faq'>
            <h3 class="page-header">FAQ - Frequently Asked Questions</h3>
        </div>
        <div id='updates'>
             <h3 class="page-header">Service Updates</h3>
        </div>
        <div id='kBase'>
            <h3 class="page-header">Knowledge Base</h3>
            <p>Find the latest documentation regarding IvedaXpress or your camera here!</p>

            <p>Z-3020<br />
            <a href="<?php echo $myURL;?>Z3020_install_user_guide.pdf" target="_blank">Installation Guide and User Manual</a></p>

            <p>NCR770<br />
           <a href="<?php echo $myURL;?>NCR770.pdf" target="_blank">User Manual</a></p>

           <p>Z-3505<br />
           <a href="<?php echo $myURL;?>Z3505.pdf" target="_blank">Installation Guide and User Manual</a></p>

           <p>Ivedamobile (Mobile Camera)<br />
           <a href="<?php echo $myURL;?>ivedamobile_cht.pdf" target="_blank">Installation Guide and User Manual</a></p>
        </div>
</div>
<?php include_once("./include/tail.php"); ?>

<script>
$('document').ready(function(){
   bMarks('h3','header');
});
</script>
	
        <script src="js/plugins.js"></script>
        <script src="js/main.js"></script>
        <script src="js/vendor/bootstrap.min.js"></script>
        <!-- Google Analytics: change UA-XXXXX-X to be your site's ID. -->
        <script>
            (function(b,o,i,l,e,r){b.GoogleAnalyticsObject=l;b[l]||(b[l]=
            function(){(b[l].q=b[l].q||[]).push(arguments)});b[l].l=+new Date;
            e=o.createElement(i);r=o.getElementsByTagName(i)[0];
            e.src='//www.google-analytics.com/analytics.js';
            r.parentNode.insertBefore(e,r)}(window,document,'script','ga'));
            ga('create','UA-XXXXX-X');ga('send','pageview');
        </script>

</body>
</html>
