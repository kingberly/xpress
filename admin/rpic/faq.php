<?PHP
include_once("/var/www/qlync_admin/header.php");
include_once("/var/www/qlync_admin/menu.php");
#Authentication Section
if ( $_SESSION["Email"]=="" )   exit();
############  Authentication Section End
?>
<!--
<html><head></head>
<title>疑難排解</title>
<body>
-->
<?php
define("FAQ_PREFIX","faq");
define("FAQ_POSTFIX",".jpg");
if (is_dir(getcwd()."/picture/"))
define("FAQ_IMG_PATH","picture/");
else
define("FAQ_IMG_PATH",".");

 $files = scandir(FAQ_IMG_PATH, SCANDIR_SORT_ASCENDING);
//var_dump($files);
 for($i=0;$i<sizeof($files);$i++){
    if (strpos($files[$i],FAQ_POSTFIX)!== FALSE)
       if (strpos($files[$i],FAQ_PREFIX)!== FALSE)
          echo "<img src='".FAQ_IMG_PATH."{$files[$i]}'>";
 }
?>
</body>
</html>
