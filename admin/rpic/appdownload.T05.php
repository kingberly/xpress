<?php
include_once("/var/www/qlync_admin/header.php");
include_once("/var/www/qlync_admin/menu.php");
define("FAQ_IMG_PATH","/html/faq/picture/");
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>
<?php
echo "<img src='".FAQ_IMG_PATH."appdownload1.png' alt='xpress' usemap='#viewerMAP'>"; 
echo "<img src='".FAQ_IMG_PATH."appdownload2.png' alt='ivedamobile' usemap='#ivedaMobileMAP'>";
echo "<img src='".FAQ_IMG_PATH."appdownload3.png' alt='grca' usemap='#grcaMAP'>";
?>
<map name="viewerMAP" id="viewerMAP">
    <!--area alt="android" title="android" href="http://rpic.tycg.gov.tw/ty/rpic.htm" target=_blank shape="rect" coords="259,410,372,524" /-->
    <area alt="android" title="android" href="https://play.google.com/store/apps/details?id=com.tycg.android.mva" target=_blank shape="rect" coords="205,410,400,600" />
    <area alt="ios" title="ios" href="https://itunes.apple.com/tw/app/%E9%81%93%E8%B7%AF%E6%96%BD%E5%B7%A5%E5%BD%B1%E5%83%8F%E7%B3%BB%E7%B5%B1/id1276120544?mt=8" target=_blank shape="rect" coords="540,410,750,600" />
    <!--area alt="ios" title="ios" href="itms-services://?action=download-manifest&url=https://rpic.tycg.gov.tw/ty/rpic.plist" target=_blank shape="rect" coords="589,410,706,526" /-->
</map>
<map name="ivedaMobileMAP" id="ivedaMobileMAP">
    <area alt="android" title="android" href="http://rpic.tycg.gov.tw/ty/ivedamobile.ty.php" shape="rect" target=_blank coords="225,419,380,585" />
    <area alt="ios" title="ios" href="https://itunes.apple.com/tw/app/tyivedamobile/id1107940902?mt=8" target=_blank shape="rect" coords="555,416,730,590" />
</map>
<map name="grcaMAP" id="grcaMAP">
    <area alt="grca" title="grca" href="http://grca.nat.gov.tw/repository/Certs/GRCA.cer" target=_blank shape="rect" coords="257,418,359,519" />
    <area alt="grca2" title="grca2" href="http://grca.nat.gov.tw/repository/Certs/GRCA2.cer" target=_blank shape="rect" coords="575,417,676,518" />
</map>
<a href='http://grca.nat.gov.tw/repository/Certs/GRCA1_to_GRCA1_5.cer'>grca1.5</a>&nbsp;&nbsp;
<a href='http://grca.nat.gov.tw/repository/Certs/GRCA1_5_to_GRCA2.cer'>grca1.5/2</a>
</body>
</html>