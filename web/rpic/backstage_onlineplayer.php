<?php
/****************
 *Validated on May-25,2016
 * reference from backstage_liveview.php 
 * /var/www/SAT-CLOUDNVR/  
 *Writer: JinHo, Chang
 *backstage_onlineplayer.php?adminpwd=&mac=
 *backstage_onlineplayer.php?token&mac= 
 *pass user_pwd via SESSION 
 *sed -i -e 's|XNN|X02|' /var/www/SAT-CLOUDNVR/backstage_liveview.php  
*****************/
include_once( "./include/global.php" );
include_once( "./include/db_function.php" );
include_once( "./include/log_db_function.php" );
include_once( "./include/user_function.php" );
include_once( "./include/utility.php" );
//include_once( "./include/oem_id.php" );
include_once( "./include/index_title.php" ); //oem_id
header('Access-Control-Allow-Methods: POST, GET');
header('Cache-Control: no-cache, must-revalidate');
header("Content-Type:text/html; charset=utf-8");

include_once( "rpic.inc" );

if ($_REQUEST['adminpwd'] == APP_USER_PWD){ 
//correct site format with hyphen
}else if(isset($_SESSION["user_name"])){
//has logined
}else{
     echo 'Please go to web portal for normal login';
     exit();
}
if ($_REQUEST['mac']=="") {header('Location: /'); exit();}
//if uid
$_REQUEST['mac']=strtoupper($_REQUEST['mac']);
if (strlen($_REQUEST['mac'])==18){
	if (!preg_match('/^[A-Z0-9]{12}$/', substr($_REQUEST['mac'],6,12)))  {
	echo "Invalid MAC format";
	exit();
	}
}else if (strlen($_REQUEST['mac'])==12) { 
	if (!preg_match('/^[A-Z0-9]{12}$/', $_REQUEST['mac']))  {
		echo "Invalid MAC format";
		exit();
	}
}else{header('Location: /'); exit;}
if (!isset($_REQUEST["IE"])) $IE=1;
else $IE=$_REQUEST["IE"];

if (isset($_REQUEST['debugadmin'])) echo "index:{$lindex}<br>"; 
$CameraInfo=array();

function setCamInfo($mac,&$CameraInfo)
{

  if ((OEM_ID=="T04") or (OEM_ID=="T05") or (OEM_ID=="K01") or (OEM_ID=="T06") ) $myKey = "?"; //T04, T05 only
  else
		if (isset($_REQUEST['token'])) $myKey = $_REQUEST['token'];
		else if (isset($_SESSION['user_pwd'])) $myKey =getToken($username,$_SESSION['user_pwd']);  
		else return false;
  
  $CameraInfo[0][uid] =getUID($mac);
  $CameraInfo[0][name] = getCamName($CameraInfo[0][uid]);
  $CameraInfo[0][videosrc] = getURL($CameraInfo[0][uid],$myKey);
  echo $html;
	return true;    
}

function vlcPage($vArr)
{//uid, name, url
  if ($vArr[videosrc]=="") return "";
  $html= "<div><b><font size=3>";
  if ($vArr[name]!="")    $html.= $vArr[uid]."(".$vArr[name].")"; 
  else $html.= substr($vArr[uid],strpos($vArr[uid],"-")+1);
  $html.= "直播畫面</font></b></div>";
  //objvlc-{$vArr[uid]}
  $html.= "<Br><object width='352' height='240' id='video1'>\n<param name='src' value='{$vArr[videosrc]}'/>\n";
  $html.= "<embed type='application/x-vlc-plugin'  pluginspage='http://www.videolan.org' name='vlc-{$vArr[uid]}'\n
     autoplay='yes' loop='no' controls='yes' width='352' height='240'\ntarget='{$vArr[videosrc]}' />\n";
  $html.= "</object>\n";
  return $html;
}
 
function vlcObjPage($vArr)
{//uid, name, url
  if ($vArr[videosrc]=="") return "";
  $html= "<div><b><font size=3>";
  if ($vArr[name]!="")    $html.= $vArr[uid]."(".$vArr[name].")"; 
  else $html.= substr($vArr[uid],strpos($vArr[uid],"-")+1);
  $html.= "直播畫面</font></b></div>";
  $html.= "<Br><object  type='application/x-vlc-plugin' width='352' height='240' id='video1'>\n<param name='src' value='{$vArr[videosrc]}'/>\n";
  $html.= "</object>\n";
  return $html;
}
function vlcEmbedPage($vArr)
{//uid, name, url
  if ($vArr[videosrc]=="") return "";
  $html= "<div><b><font size=3>";
  if ($vArr[name]!="")    $html.= $vArr[name]; 
  else $html.= substr($vArr[uid],6,12);//substr($vArr[uid],strpos($vArr[uid],"-")+1);
  $html.= "</font></b></div>";
  //vlc-{$vArr[uid]}
  $html.= "<embed type='application/x-vlc-plugin'  pluginspage='http://www.videolan.org' name='video1'\n
     mute='true' volumn='0' autoplay='yes' loop='no' controls='yes' width='352' height='240'\ntarget='{$vArr[videosrc]}' />\n";
  return $html;
}

function printLayout($mvArr)
{
  $html.="<table><tr>";
    $html.="<td>";
    if ($_REQUEST['IE']=="1")
      $html.= vlcObjPage($mvArr[0]);
    else $html.= vlcEmbedPage($mvArr[0]);//old chrome, firefox    
    $html.="</td>";
  $html.="</tr></table>";
  echo $html;
}  
?>
<!DOCTYPE html>
<html>                             
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>
<script>
function muteVLC(objname)
{
      var vlc = document.getElementById(objname);
      vlc.audio.toggleMute();
} 
function ccopy(s) {
	alert("如果瀏覽器自動複製網址不成功,\n請手動將網址複製後至VLC播放器!");
  var clip_area = document.createElement('textarea');
  clip_area.textContent = s;

  document.body.appendChild(clip_area);
  clip_area.select();
    
  document.execCommand('copy');
  clip_area.remove();
}
function isChromeNPAPI()
{
  var raw;
  var minimum_chrome_version= 45; //42 enable NPAPI
    raw = navigator.userAgent.match(/Chrom(e|ium)\/([0-9]+)\./);
    if (parseInt(raw[2], 10) > 45)
      return true;//cannot load vlc
    
    return false; 
}
function optionValue(thisformobj, selectobj)
{
	var chosenoption=selectobj.options[selectobj.selectedIndex];
  thisformobj.value = chosenoption.value;
}

</script>
<?php
echo "<form name=switchBrowser method=post action={$_SERVER['PHP_SELF']}>";
if (isset($_REQUEST["adminpwd"]) ) echo "<input type=hidden name=adminpwd value='{$_REQUEST["adminpwd"]}'>";
if (isset($_REQUEST["token"]) ) echo "<input type=hidden name=token value='{$_REQUEST["token"]}'>";
if (isset($_REQUEST["debugadmin"]) ) echo "<input type=hidden name=debugadmin>";
echo "<input type=hidden name=mac value='{$_REQUEST["mac"]}'><input type=hidden name=IE value='{$IE}'><input type=submit id='switchBtn' value='Switch to IE'  style='display: none;'></form>";
?>
<div id="container">
<?php
  if (setCamInfo($_REQUEST['mac'],$CameraInfo))
  	printLayout($CameraInfo);
  else echo "直播畫面需要使用者帳密";
  if (isset($_REQUEST['debugadmin'])){
    var_dump($CameraInfo);
  } 
?>
<br><a href="http://www.videolan.org/"  style="text-decoration: none;color:black" target=_blank>無法顯示直播畫面請下載安裝VLC</a>
<br>
<script>
var videosrc = "<?php echo $CameraInfo[0][videosrc];?>";
var txtNoVLC = "你的瀏覽器不支援 VLC元件, 請使用Internet Explorer/Firefox.";
var txtNoIEComp = "<br>如無法顯示元件,請開啟相容性檢視<br>";
var txtCopyVLC="可手動將網址複製至VLC播放器播放!";
var isIE = /*@cc_on!@*/false || !!document.documentMode;
var isEdge = !isIE && !!window.StyleMedia;
var isFirefox = typeof InstallTrigger !== 'undefined';
var isChrome = /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor);

if (isIE){  //10 OK,
  <?php 
      if ((!isset($_REQUEST["IE"])) or ($_REQUEST['IE']=="0") ){
          echo "document.forms['switchBrowser'].IE.value='1';";
          echo "document.forms['switchBrowser'].submit();";
      }
  ?>
  document.getElementById('switchBtn').disabled=true;
  //document.write("IE:"+navigator.userAgent+"<br>");
  var ua = navigator.userAgent;
  var msie = ua.indexOf("MSIE ");
  if (isNaN(parseInt(ua.substring(msie + 5, ua.indexOf(".", msie))))) {
     if (navigator.appName == 'Netscape') {
        var re = new RegExp("Trident/.*rv:([0-9]{1,}[\.0-9]{0,})");
        if (re.exec(ua) != null) {
            rv = parseFloat(RegExp.$1);
            //document.write("Version:"+rv);
            document.write(txtNoIEComp);
        }
     }else{
        document.write("Not IE");
     }
  }else{  //<IE11
    var msieV=parseInt(ua.substring(msie + 5, ua.indexOf(".", msie)));
    //document.write("Version:"+msieV);
    if (msieV!=7)  document.write(txtNoIEComp);
  } 
}else if (isEdge){//not working
  //document.write("Edge:"+navigator.userAgent+"<br>");
  document.write(txtNoVLC);
  //setInterval("window.location = '/';",3000);
}else if (isFirefox){//firefox 46, 36 ok
  //document.write("Firefox:"+navigator.userAgent+"<br>");
  muteVLC("video1");
}else{
  //document.write("Misc:"+navigator.userAgent+"<br>");
  if  (isChrome!= null){
    if (isChromeNPAPI()){
      document.write(txtNoVLC);
      //setInterval("window.location = '/';",3000);
    }else //support old chrome version
      muteVLC("video1");
  }else{
      document.write(txtNoVLC);
      //setInterval("window.location = '/';",3000);
  //var isSafari = Object.prototype.toString.call(window.HTMLElement).indexOf('Constructor') > 0 || (function (p) { return p.toString() === "[object SafariRemoteNotification]"; })(!window['safari'] || safari.pushNotification);// Safari 3.0+ "[object HTMLElementConstructor]"
  //var isOpera = (!!window.opr && !!opr.addons) || !!window.opera || navigator.userAgent.indexOf(' OPR/') >= 0;// Opera 8.0+
  }
}
if (videosrc!="")
document.write("<br>"+txtCopyVLC+"<br><a href='"+videosrc+"' onclick='javascript:ccopy(\""+videosrc+"\");return false;'>"+videosrc+"</a><br>");
</script>
</div>
</body>
</html>