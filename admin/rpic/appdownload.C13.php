<?php
define("FAQ_IMG_PATH","/html/faq/picture/");

//type : name,logoPATH, appurl_a, qr_a, appurl_i, qr_i, pdf, 7/font color
$APPINFO=[
"VIEW_APP"=>array("奇美Xpress",FAQ_IMG_PATH."xpress.logo.png",
"http://engeye.chimei.com.tw/rpic/xpress.php",FAQ_IMG_PATH."xpress.png",
"itms-services://?action=download-manifest&url=https://engeye.chimei.com.tw/rpic/xpress.plist.php",FAQ_IMG_PATH."xpress.plist.php.png",
"/doc/奇美XpressAPP使用說明.pdf","#00266C"),
"REC_APP"=>array("奇美行動眼",FAQ_IMG_PATH."mobilecam.logo.png",
"http://engeye.chimei.com.tw/rpic/mobilecam.php",FAQ_IMG_PATH."mobilecam.png",
"itms-services://?action=download-manifest&url=https://engeye.chimei.com.tw/rpic/mobilecam.plist.php",FAQ_IMG_PATH."mobilecam.plist.php.png",
"/doc/奇美行動眼APP使用說明.pdf","#00266C")
//"MAP_APP"=>array()
];
include("/var/www/qlync_admin/doc/config.php");
include("rpic.inc");


if (isMobile())
{

?>
<html>
<head>
<title>App Download</title>
<?php
printMobileMeta();
echo "\n<style type=\"text/css\">\n";
echo "html *\n{font-size: 1em !important;\n}\n";
echo "h1{\nfont-size: 2em !important;\n}\n";
echo "\n</style>\n"
?>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<link href="/css/form.css" rel="stylesheet" type="text/css"  charset="utf-8" />
<link href="/css/nav.css" rel="stylesheet" type="text/css"  charset="utf-8" />
</head>
<body>
<div class="container">
	<div class="header">
		<div class="logo"><a href="<?php echo $home_url;?>"><img src="/images/logo_qlync.png"/></a></div>
    <div class="div_navi">
       	<span class="navi"></span>
            <span class="navi"></span>
            <span class="navi"></span> 
		</div>
        <div align="right">
<?php
if (!isset($_SESSION["ID_qlync"])){ //if (!isset($_SESSION["Email"]) ){
	echo "<a href=\"{$home_url}\">登入</a>\n";
  exit(1);
}else{
  if ($_SESSION["ID_admin_qlync"]!='1') //not godadmin
    if ($_SESSION["ID_admin_oem_qlync"]!='1')  exit(); //not super admin
	echo "<a href=\"javascript:var wobj=window.open('/html/member/logout.php?step=logout','print_popup','width=300,height=300');setTimeout(function(){location='{$home_url}';},500);setTimeout(function() { wobj.close(); }, 500);\">登出</a>\n";
}
?>
        </div> 
	</div>
</div>
<hr>
<?php
}else{
	include_once("/var/www/qlync_admin/header.php");
	include_once("/var/www/qlync_admin/menu.php");
//var_dump($_SESSION);
  if ($_SESSION["ID_admin_qlync"]!='1') //not godadmin
    if ($_SESSION["ID_admin_oem_qlync"]!='1')  exit(); //not super admin
}
?>
<style type="text/css">
a img {
border:none;
outline:none;
}
img {
max-width: 100%;
}
</style>
<div align="center">
<table>
<?php
foreach ($APPINFO as $key=> $data){
?>
<tr><td align=center colspan=3> 
<img src='<?php echo $data[1];?>' alt='xpress'>
<br><h2><font color="<?php echo $data[7];?>"><?php echo $data[0];?></font></h2>
</td></tr><tr><td align=center>
<a title="android" href="<?php echo $data[2];?>" target=_blank><img src='<?php echo $data[3];?>'></a>
</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td align=center>
<a title="ios" href="<?php echo $data[4];?>" target=_blank><img src='<?php echo $data[5];?>'></a>
</td></tr><tr><td align=center>
<!--store android_store.png or apk android_apk.jpg---->
<a title="android" href="<?php echo $data[2];?>" target=_blank><img src='<?php echo FAQ_IMG_PATH.ANDROID_APP_IMG;?>'></a>
</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td><td align=center>
<!--store ios_store.png or apk ios_ipa.jpg---->
<a title="ios" href="<?php echo $data[4];?>" target=_blank><img src='<?php echo FAQ_IMG_PATH.IOS_APP_IMG;?>'></a>
</td></tr>
<tr><td align=center colspan=3>
<a href="<?php echo $data[6];?>" valign="top" style="vertical-align: top;font-weight: bold;color: <?php echo $data[7];?>; text-decoration: none;" target='_blank'>查看APP使用說明 &gt;&gt;</a>
</td></tr>
<tr><td align=center colspan=3 height="100"></tr>
<?php
}
?>
</table>
</div>
</body>
</html>
