<?php
define("FAQ_IMG_PATH","/html/faq/picture/");
define("ANDROID_APP_IMG","android_store.png");
//define("ANDROID_APP_IMG","android_apk.jpg");
define("IOS_APP_IMG","ios_store.png");
//define("IOS_APP_IMG","ios_ipa.jpg");


//type : name,logoPATH, 2appurl_a, qr_a, appurl_i, qr_i, pdf, 7/font color
$APPINFO=[
"VIEW_APP"=>array("MegasysXpress",FAQ_IMG_PATH."megasysxpress.png",
"https://play.google.com/store/apps/details?id=com.xpress1.cloudnvr&hl=zh_TW",FAQ_IMG_PATH."megasysxpress_and.png",
"https://itunes.apple.com/kg/app/megasysxpress/id931909110?mt=8",FAQ_IMG_PATH."megasysxpress_ios.png",
"/doc/MegasysXpressAPP.pdf", "#CD2626"),
"MAP_APP"=>array("全民監工網",FAQ_IMG_PATH."workeye.png",
"https://play.google.com/store/apps/details?id=com.iveda.cva&hl=zh_TW",FAQ_IMG_PATH."workeye_and.png",
"https://itunes.apple.com/kg/app/%E5%85%A8%E6%B0%91%E7%9B%A3%E5%B7%A5%E7%B6%B2/id1240636236?mt=8",FAQ_IMG_PATH."workeye_ios.png",
"/doc/WorkeyeViewerAPP.pdf","#FF4500"),
"REC_APP"=>array("監工眼",FAQ_IMG_PATH."ivedamobile.png",
"http://engeye.chimei.com.tw/rpic/mobilecam.php",FAQ_IMG_PATH."ivedamobile_and.png",
"itms-services://?action=download-manifest&url=https://engeye.chimei.com.tw/rpic/mobilecam.plist.php",FAQ_IMG_PATH."ivedamobile_ios.png",
"/doc/WorkeyeMobilecamAPP.pdf","#3A5FCD")
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
if (!isset($_SESSION["ID_qlync"]))
	echo "<a href=\"{$home_url}\">登入</a>\n";
else{
	echo "<a href=\"javascript:var wobj=window.open('/html/member/logout.php?step=logout','print_popup','width=300,height=300');setTimeout(function(){location='{$home_url}';},500);setTimeout(function() { wobj.close(); }, 500);\">登出</a>\n";
}
?>
        </div> 
	</div>
</div>
<hr>
<?php
}else{
	include("/var/www/qlync_admin/header.php");
	include("/var/www/qlync_admin/menu.php");
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