<?php
include("../../header.php");
include("../../menu.php");
define("FAQ_IMG_PATH","/html/faq/picture/");
?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>
<?php
//echo "<div>安卓行動裝置應用<br><img src='".FAQ_IMG_PATH."apk.jpg' alt='apk' height='250' width='250'></div>"; 
//echo "<div>蘋果行動裝置應用<br><img src='".FAQ_IMG_PATH."ios.jpg' alt='ios' height='250' width='250'></div>";
//echo "<ul>如果您出現無法連接rpic, 請先安裝政府簽署憑證<img src='".FAQ_IMG_PATH."grca.jpg' alt='grca' height='150' width='150'><img src='".FAQ_IMG_PATH."grca2.jpg' alt='grca2' height='150' width='150'>";
//echo "<div>安卓IvedaMobile行動裝置應用<br><img src='".FAQ_IMG_PATH."ivedamobile.jpg' alt='ivedamobile' height='250' width='250'></div>";
echo "<img src='".FAQ_IMG_PATH."appdownload1.png' alt='xpress' usemap='#viewerMAP'>"; 
echo "<img src='".FAQ_IMG_PATH."appdownload2.png' alt='ivedamobile' usemap='#ivedaMobileMAP'>";
echo "<img src='".FAQ_IMG_PATH."appdownload3.png' alt='grca' usemap='#grcaMAP'>";
?>
<map name="viewerMAP" id="viewerMAP">
    <area alt="android" title="android" href="http://rpic.taipei/taipei/rpic.php" shape="rect" coords="259,410,372,524" />
    <area alt="ios" title="ios" href="itms-services://?action=download-manifest&url=https://rpic.taipei/taipei/rpic.plist.php" shape="rect" coords="589,410,706,526" />
</map>
<map name="ivedaMobileMAP" id="ivedaMobileMAP">
    <area alt="android" title="android" href="http://rpic.taipei/taipei/ivedamobile.php" shape="rect" coords="253,419,368,536" />
    <area alt="ios" title="ios" href="https://itunes.apple.com/tw/app/ivedamobile/id1003592955?mt=8" shape="rect" coords="587,416,710,537" />
</map>
<map name="grcaMAP" id="grcaMAP">
    <area alt="grca" title="grca" href="http://grca.nat.gov.tw/repository/Certs/GRCA.cer" shape="rect" coords="257,418,359,519" />
    <area alt="grca2" title="grca2" href="http://grca.nat.gov.tw/repository/Certs/GRCA2.cer" shape="rect" coords="575,417,676,518" />
</map>
</body>
</html>