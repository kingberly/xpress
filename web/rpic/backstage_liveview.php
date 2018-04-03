<?php
/****************
 *Validated on May-22,2016
 * reference from backstage_login.php 
 * /var/www/SAT-CLOUDNVR/
 * include shared library rpic.inc   
 *Writer: JinHo, Chang
 *backstage_liveview.php?user_name=<pmtNo>&user_pwd=APP_USER_PWD
 *sed -i -e 's|XNN|X02|' /var/www/SAT-CLOUDNVR/rpic.inc  
*****************/
include_once( "./include/global.php" );
include_once( "./include/db_function.php" );
include_once( "./include/log_db_function.php" );
include_once( "./include/user_function.php" );
include_once( "./include/utility.php" );
//include_once( "./include/oem_id.php" );
include_once( "./include/index_title.php" ); //oem_id
include_once( "rpic.inc" );
	
header('Access-Control-Allow-Methods: POST, GET');
header('Cache-Control: no-cache, must-revalidate');
header("Content-Type:text/html; charset=utf-8");

$iCountDown=30;

// the data array to return
$ret = array();
$ret["status"] = "success";
if (!isset($_REQUEST["IE"])) $IE=1;
else $IE=$_REQUEST["IE"];
if (isset($_REQUEST['layout']) )
  $layout=$_REQUEST['layout'];
else  $layout="3x2";
$lsize=explode("x",$layout);
$layoutSize=intval($lsize[0])*intval($lsize[1]);
$lindex=0;//default start in page1
if (isset($_REQUEST['vlcpage']) ) $lindex = intval($_REQUEST['vlcpage']);
if (isset($_REQUEST['debugadmin'])) echo "index:{$lindex}<br>"; 
$CameraInfo=array();


function setCamArray($username)
{
  global $layoutSize,$CameraInfo,$lindex;
  if ((OEM_ID=="T04") or (OEM_ID=="T05") or (OEM_ID=="K01")) $myKey = "?"; //T04, T05 only
  else  $myKey =getToken($username,APP_USER_PWD);
  
  $sql="select c1.owner_id,c1.uid from isat.device_share as c1 left join isat.user as c2 on c1.visitor_id = c2.id where c2.name='{$username}'";
  sql($sql,$result,$num,0);
 
  for($i=$lindex;$i<$num;$i++) //for($i=0;$i<$num;$i++) 
  {
      if (($i-$lindex ) >= $layoutSize) break;
      fetch($arr,$result,$i,0);
      $CameraInfo[$i][uid] = $arr['uid'];
      $CameraInfo[$i][name] = getCamName($CameraInfo[$i][uid]);
      $CameraInfo[$i][videosrc] = getURL($CameraInfo[$i][uid],$myKey);
  }//for

  //for page printout
  if ($num > $layoutSize ) // more than 6 cameras, usinge page
  {
    $html.="<form  method=post action={$_SERVER['PHP_SELF']}><select name='vlcpage' id='vlcpage' onchange=\"optionValue(this.form.vlcpage, this);this.form.submit();\">";
    for ($i=1;$i<=ceil($num/$layoutSize);$i++){
      $j=($i-1)*$layoutSize;
      if ($j<0) $j=0;
      if ($j==$lindex)
        $html.="<option value='{$j}' selected>頁{$i}</option>";
      else  $html.="<option value='{$j}'>頁{$i}</option>";
    }
    $html.="</select><input type=hidden name=IE value='{$_REQUEST['IE']}'><input type=hidden name=user_name value='{$_REQUEST[user_name]}'><input type=hidden name=user_pwd value='{$_REQUEST[user_pwd]}'>";
    if (isset($_REQUEST['debugadmin'])) $html.="<input type=hidden name=debugadmin value='1'>";
    $html.="</form>";
  }
  echo $html;    
}
 
function vlcObjPage($vArr)
{//uid, name, url
  if ($vArr[videosrc]=="") return "";
  $html= "<div><b><font size=3>";
  if ($vArr[name]!="")    $html.= $vArr[uid]."(".$vArr[name].")"; 
  else $html.= substr($vArr[uid],strpos($vArr[uid],"-")+1);
  $html.= "直播畫面</font></b></div>";
  //classid='clsid:9BE31822-FDAD-461B-AD51-BE1D1C159921'
  //codebase='http://download.videolan.org/pub/vlc/0.8.6c/win32/axvlc.cab'
  //codebase='http://download.videolan.org/pub/videolan/vlc/0.9.2/win32/axvlc.cab'
  //data='{$vArr[videosrc]}'  
  $html.= "<Br><object  type='application/x-vlc-plugin' width='352' height='240' id='objvlc-{$vArr[uid]}'>\n<param name='src' value='{$vArr[videosrc]}'/>\n";
  $html.= "</object>\n";
  return $html;
}

function vlcPage($vArr)
{//uid, name, url
  if ($vArr[videosrc]=="") return "";
  $html= "<div><b><font size=3>";
  if ($vArr[name]!="")    $html.= $vArr[uid]."(".$vArr[name].")"; 
  else $html.= substr($vArr[uid],strpos($vArr[uid],"-")+1);
  $html.= "直播畫面</font></b></div>";
  //classid='clsid:9BE31822-FDAD-461B-AD51-BE1D1C159921'
  //codebase='http://download.videolan.org/pub/vlc/0.8.6c/win32/axvlc.cab'
  //codebase='http://download.videolan.org/pub/videolan/vlc/0.9.2/win32/axvlc.cab'
  //data='{$vArr[videosrc]}'  
  $html.= "<Br><object width='352' height='240' id='objvlc-{$vArr[uid]}'>\n<param name='src' value='{$vArr[videosrc]}'/>\n";
  $html.= "<embed type='application/x-vlc-plugin'  pluginspage='http://www.videolan.org' name='vlc-{$vArr[uid]}'\n
     autoplay='yes' loop='no' controls='yes' width='352' height='240'\ntarget='{$vArr[videosrc]}' />\n";
  $html.= "</object>\n";
  return $html;
}

function vlcEmbedPage($vArr)
{//uid, name, url
  if ($vArr[videosrc]=="") return "";
  $html= "<div><b><font size=3>";
  if ($vArr[name]!="")    $html.= $vArr[uid]."(".$vArr[name].")"; 
  else $html.= substr($vArr[uid],strpos($vArr[uid],"-")+1);
  $html.= "直播畫面</font></b></div>";
  $html.= "<embed type='application/x-vlc-plugin'  pluginspage='http://www.videolan.org' name='vlc-{$vArr[uid]}'\n
     mute='true' volumn='0' autoplay='yes' loop='no' controls='yes' width='352' height='240'\ntarget='{$vArr[videosrc]}' />\n";
  return $html;
}

function printLayout($layout,$mvArr)
{
  //$lsize=explode("x",$layout); //$lsize[0], $lsize[1]
  //$layoutSize=intval($lsize[0])*intval($lsize[1]);
  global $lsize,$layoutSize,$lindex;

  $html.="<table><tr>";

  for ($i=0;$i<sizeof($mvArr);$i++){
    if ( ($i % $lsize[0] == 0) and ($i!=0))
        $html.="</tr><tr>";
    $html.="<td>";
    if ($_REQUEST['IE']=="1")
      $html.= vlcObjPage($mvArr[$i+$lindex]);
    else $html.= vlcEmbedPage($mvArr[$i+$lindex]);//old chrome, firefox    
    
    $html.="</td>";
  }
  $html.="</tr></table>";
  echo $html;
}

try {
//for taipei project site account login only
    if (checkSiteAccountRule(OEM_ID,$_REQUEST['user_name']) and ($_REQUEST['user_pwd'] == APP_USER_PWD) )
    { //correct site format with hyphen
    }else{
         throw new Exception('Please go to web portal for normal login');
    }
//end of  taipei project
	// open db
	$data_db = new DataDBFunction();
	// select user info
	$verify_result = VerifyUserWithPwd($data_db, $_REQUEST['user_name'], $_REQUEST['user_pwd'], $user_info_row, OEM_ID);
	if ($verify_result != VERIFY_USER_HASH_IN_USER_TABLE_SUCCESS) {
		ClearSessionExceptLanguage();
	}
	switch ($verify_result) {
		case VERIFY_USER_HASH_IN_USER_TABLE_SUCCESS:
			break;
		case VERIFY_USER_HASH_PRODUCT_UNMATCH:
			throw new Exception('Product unmatched.');
			break;
		case VERIFY_USER_HASH_IN_USER_REG_TABLE_SUCCESS:
			throw new Exception('Please check your confirm letter first.');
			break;
		default:
			$_SESSION['login_failure'][] = time();
			throw new Exception( 'Invalid Username / Password.' );
			break;
	}
}catch( Exception $e ) {
	SetErrorState( $ret, $e->getMessage() );
}

try {
	if ($ret['status'] == 'success') {
		// Parse HTTP_USER_AGENT
		$user_agent = parse_user_agent();

		// Save user log.
		$log_db = new LogDBFunction();
		$log_db->InsertUserLog($user_info_row, 'LOGIN', 'SUCCESS', 'sat', $user_agent);
	}
}catch (Exception $e) {
}
if (isset($_REQUEST['debugadmin']))
  echo json_encode( $ret );
  
?>
<!DOCTYPE html>
<html>                             
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<?php if (!isset($_REQUEST['debugadmin'])){?>
<meta http-equiv="refresh" content="<?php echo $iCountDown;?>; URL=<?php echo "https://".$_SERVER['SERVER_NAME'];?>">
<?php } ?>
</head>
<body>
<script>
var CountDownSecond = <?php echo ($iCountDown+1);?>;
function CountDown() {
  if (CountDownSecond !=0) {
    CountDownSecond -= 1;
    document.getElementById("myMessage").innerHTML = "視窗關閉倒數" + CountDownSecond + " 秒";
  }
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
 
setInterval("CountDown();",1000);

</script>
<?php
echo "<form name=switchBrowser method=post action={$_SERVER['PHP_SELF']}><input type=hidden name=user_name value='{$_REQUEST["user_name"]}'><input type=hidden name=user_pwd value='{$_REQUEST["user_pwd"]}'><input type=hidden name=IE value='{$IE}'><input type=submit id='switchBtn' value='Switch to IE'  style='display: none;'></form>";
?>
<div id="myMessage">視窗關閉倒數<?php echo $iCountDown;?>秒....</div>
<div id="container">
<?php
if ($ret['status'] == 'success'){
  setCamArray($_REQUEST['user_name']);
  if (isset($_REQUEST['debugadmin'])){
    var_dump($CameraInfo);
  } 

  printLayout($layout,$CameraInfo);
}
?>
<br><a href="http://www.videolan.org/"  style="text-decoration: none;color:black" target=_blank>無法顯示直播畫面請下載安裝VLC</a>
<br>
<script>
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
            document.write("<br>如無法顯示元件,請開啟相容性檢視<br>");
        }
     }else{
        document.write("Not IE");
     }
  }else{  //<IE11
    var msieV=parseInt(ua.substring(msie + 5, ua.indexOf(".", msie)));
    //document.write("Version:"+msieV);
    if (msieV!=7)  document.write("<br>如無法顯示元件,請開啟相容性檢視<br>"); 
    //document.write("<br>請關閉相容性檢視<br>");
  } 
}else if (isEdge){//not working
  //document.write("Edge:"+navigator.userAgent+"<br>");
  document.write("你的瀏覽器Edge不支援 VLC元件, 請使用Internet Explorer/Firefox.");
  setInterval("window.location = '/';",3000);
}else if (isFirefox){//firefox 46, 36 ok
  //document.write("Firefox:"+navigator.userAgent+"<br>");
}else{
  //document.write("Misc:"+navigator.userAgent+"<br>");
  if  (isChrome!= null){
    if (isChromeNPAPI()){
      document.write("你的瀏覽器版本不支援NPAPI, 請使用Internet Explorer/Firefox.");
      setInterval("window.location = '/';",3000);
    }//support old chrome version
  }else{
      document.write("你的瀏覽器不支援 VLC元件, 請使用Internet Explorer/Firefox.");
      setInterval("window.location = '/';",3000);
  //var isSafari = Object.prototype.toString.call(window.HTMLElement).indexOf('Constructor') > 0 || (function (p) { return p.toString() === "[object SafariRemoteNotification]"; })(!window['safari'] || safari.pushNotification);// Safari 3.0+ "[object HTMLElementConstructor]"
  //var isOpera = (!!window.opr && !!opr.addons) || !!window.opera || navigator.userAgent.indexOf(' OPR/') >= 0;// Opera 8.0+
  }
}
</script>
</div>
</body>
</html>