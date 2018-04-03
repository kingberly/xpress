<?php
/****************
 *Validated on May-25,2016
 * reference from backstage_liveview.php 
 * input parameter: user_name,user_pwd
 * special input parameter: page, debugadmin, share
 * include owner device list   
 * include shared library /var/www/SAT-CLOUDNVR/rpic.inc
 * css code from http://unraveled.com/     
 *Writer: JinHo, Chang  
*****************/
include_once( "./include/global.php" );
include_once( "./include/db_function.php" );
include_once( "./include/log_db_function.php" );
include_once( "./include/user_function.php" );
include_once( "./include/utility.php" );
include_once( "./include/index_title.php" ); //oem_id
include_once( "rpic.inc" );
	
header('Access-Control-Allow-Methods: POST, GET');
header('Cache-Control: no-cache, must-revalidate');
header("Content-Type:text/html; charset=utf-8");

//new translation
if ($_SESSION["user_language"] == "en_US")
	define("OFFLINE","Offline");
else define("OFFLINE","離線");

// the data array to return
$ret = array();
$ret["status"] = "success";
$layout="1x10";
$lsize=explode("x",$layout);
$layoutSize=intval($lsize[0])*intval($lsize[1]);
$lindex=0;//default start in page1
if (isset($_REQUEST['page']) ) $lindex = intval($_REQUEST['page']);
if (isset($_REQUEST['debugadmin'])) echo "index:{$lindex}<br>"; 
$CameraInfo=array();
define ("TYPE_SHARE","Share");


function setCamArray($username,$user_pwd,&$CameraInfo,$type="")
{
  global $layoutSize,$lindex;
  if ((OEM_ID=="T04") or (OEM_ID=="T05") or (OEM_ID=="K01") or (OEM_ID=="T06")) $myKey = "?"; //rpic only
  else  $myKey =getToken($username,$user_pwd);

	if ($type ==TYPE_SHARE)
	$sql="select uid,mac_addr,c1.name,is_signal_online,ip_addr from isat.query_share as c1 left join isat.user as c2 on c1.visitor_id=c2.id where c2.name='{$username}' group by uid";
  //$sql="select c1.owner_id,c1.uid,c3.ip_addr from isat.device_share as c1 left join isat.user as c2 on c1.visitor_id = c2.id left join isat.signal_server_online_client_list as c3 on c1.uid = c3.uid where c2.name='{$username}'";
  else $sql="select uid, mac_addr,name,is_signal_online,ip_addr from isat.query_info where user_name='{$username}' group by uid"; 
	//$sql="select c1.owner_id,c1.uid,c3.ip_addr from isat.query_info as c1 left join isat.signal_server_online_client_list as c3 on c1.uid = c3.uid where c1.user_name='{$username}' group by c1.uid";
  
  sql($sql,$result,$num,0);

  for($i=$lindex;$i<$num;$i++) //for($i=0;$i<$num;$i++) 
  {
      if (($i-$lindex ) >= $layoutSize) break;
      fetch($arr,$result,$i,0);
      $CameraInfo[$i][mac] = $arr['mac_addr'];
      $CameraInfo[$i][name] = $arr['name'];
      $CameraInfo[$i][videosrc] = getURL($arr['uid'],$myKey);
      $CameraInfo[$i][thumbnail] = getURL($arr['uid'],$myKey,THUMBNAIL_TAG);
      if ($arr['is_signal_online']=="true")
      	$CameraInfo[$i][online] = $arr['ip_addr'];
      else $CameraInfo[$i][online] = "";
  }//for

  //for page printout
  if ($num > $layoutSize ) // more than 6 cameras, usinge page
  {
    $html.="<form  method=post action={$_SERVER['PHP_SELF']}><select name='page' id='page' onchange=\"optionValue(this.form.page, this);this.form.submit();\">";
    for ($i=1;$i<=ceil($num/$layoutSize);$i++){
      $j=($i-1)*$layoutSize;
      if ($j<0) $j=0;
      if ($j==$lindex)
        $html.="<option value='{$j}' selected>頁{$i}</option>";
      else  $html.="<option value='{$j}'>頁{$i}</option>";
    }
    $html.="</select><input type=hidden name=user_name value='{$_REQUEST[user_name]}'><input type=hidden name=user_pwd value='{$_REQUEST[user_pwd]}'>";
    if (isset($_REQUEST['debugadmin'])) $html.="<input type=hidden name=debugadmin value='1'>";
    if (isset($_REQUEST['share'])) $html.="<input type=hidden name=share>";
    $html.="</form>";
  }
  echo $html;    
}

/*
intent:
   HOST/URI-path // Optional host
   #Intent;
      package=[string]; //org.videolan.vlc
      action=[string];
      category=[string];
      component=[string];
      scheme=[string];
   end;
*/
function printLayoutTable($mvArr,$type)
{
  global $layoutSize,$lindex;
/*	$html="<table><th><td colspan=2>"._("Device List")."</td></th>";
	if ($type==TYPE_SHARE){
			$html.="<tr><td colspan=2>"._("Share")."</td></tr>";
	}
	*/
	$html="<table>";
  for ($i=0;$i<sizeof($mvArr);$i++){
  	$index = $lindex + $i;
    $html.="<tr><td>";
    if ($mvArr[$index][online]!="")
    	$html.="<img src='{$mvArr[$index][thumbnail]}' style='max-height:50px ;'>"; //ThumbNail
    else $html.="<img src='/images/rpic_offline.png' style='max-height:50px ;'>"; //ThumbNail
    $html.="</td><td>";
    $mytoken = explode ("?key=",$mvArr[$index][videosrc]);
		if ($mvArr[$index][name] == "")
	  	$displayname = $mvArr[$index][mac];
		else $displayname = $mvArr[$index][name];  
    if ($mvArr[$index][online]!=""){
		    if (isMobile()){
		    	//$html.="<a href='{$mvArr[$index][videosrc]}' onclick='javascript:alert(\"請將網址複製到VLC播放器!\");return false;'>".$displayname."</a>&nbsp;";
		    	$html.="<a href='{$mvArr[$index][videosrc]}' onclick='javascript:ccopy(\"{$mvArr[$index][videosrc]}\");return false;'>".$displayname."</a>&nbsp;";
		    	//$html.="<a href='intent://".ltrim($mvArr[$i][videosrc],"http://")."#Intent;scheme=http;package=org.videolan.vlc;category=android.intent.category.LAUNCHER;end'  target=_blank>{$mvArr[$i][mac]}</a>&nbsp;"; //not working
		    }else  $html.="<a href='backstage_onlineplayer.php?mac={$mvArr[$index][mac]}&token={$mytoken[1]}'>".$displayname."</a>";
		}else $html.= $displayname ."<br>". OFFLINE;    
    $html.="</td></tr>";
  }
  $html.="</table>";
  echo $html;
}
/****************************main*************************************/
if ($_SESSION["user_name"] != $_REQUEST['user_name']) { //session is the same
//if (!isset($_SESSION["user_name"]))  { //never login user must verify
try {
/*  //rpic project only check
    if (checkSiteAccountRule(OEM_ID,$_REQUEST['user_name']) and ($_REQUEST['user_pwd'] == APP_USER_PWD) )
    { //correct site format with hyphen
    }else{
         throw new Exception('Please go to web portal for normal login');
    }
*/
	// open db
	$data_db = new DataDBFunction();
	// select user info
	$verify_result = VerifyUserWithPwd($data_db, $_REQUEST['user_name'], $_REQUEST['user_pwd'], $user_info_row, OEM_ID);
	if ($verify_result != VERIFY_USER_HASH_IN_USER_TABLE_SUCCESS) {
		ClearSessionExceptLanguage();
	}
	switch ($verify_result) {
		case VERIFY_USER_HASH_IN_USER_TABLE_SUCCESS:
			$_SESSION["user_name"] = $_REQUEST['user_name'];
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
}  
?>
<!DOCTYPE html>
<html>                             
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<?php
if (isMobile())
	printMobileMeta();
//detect user agent
?>
<style type="text/css">
body {
background-color: #636363;
margin: 50px;
}

/* begin css tabs */

ul#tabnav { /* general settings */
text-align: left; /* set to left, right or center */
margin: 1em 0 1em 0; /* set margins as desired */
/*font: bold 11px verdana, arial, sans-serif; /* set font as desired */
border-bottom: 1px solid #3b3b3b; /* 6c6 set border COLOR as desired */
list-style-type: none;
padding: 3px 10px 3px 10px; /* THIRD number must change with respect to padding-top (X) below */
background-color: #fff;
}

ul#tabnav li { /* do not change */
display: inline;
}

body#tab1 li.tab1, body#tab2 li.tab2, body#tab3 li.tab3, body#tab4 li.tab4 { /* settings for selected tab */
border-bottom: 1px solid #fff; /* set border color to page background color */
background-color: #fff; /* set background color to match above border color */
}

body#tab1 li.tab1 a, body#tab2 li.tab2 a, body#tab3 li.tab3 a, body#tab4 li.tab4 a { /* settings for selected tab link */
background-color: #fff; /* set selected tab background color as desired */
color: #000; /* set selected tab link color as desired */
position: relative;
top: 1px;
padding-top: 4px; /* must change with respect to padding (X) above and below */
}

ul#tabnav li a { /* settings for all tab links */
padding: 3px 4px; /* set padding (tab size) as desired; FIRST number must change with respect to padding-top (X) above */
border: 1px solid #3b3b3b; /* 6c6 set border COLOR as desired; usually matches border color specified in #tabnav */
background-color: #595959; /* cfc set unselected tab background color as desired */
color: #d0d0d0; /* 666 set unselected tab link color as desired */
margin-right: 0px; /* set additional spacing between tabs as desired */
text-decoration: none;
border-bottom: none;
}

ul#tabnav a:hover { /* settings for hover effect */
color: #000;
background: #fff; /* set desired hover color */
}

/* end css tabs */

li.owner {  /*same as selected tab*/
color: #000;
background: #fff;
position: relative;
top: 1px;
padding-top: 4px
}
li.justdisable { /*same as a link*/
padding: 3px 4px;
border: 1px solid #3b3b3b;
background-color: #595959; 
color: #d0d0d0;
margin-right: 0px; /* set additional spacing between tabs as desired */
border-bottom: none; 
}	
</style>
</head>
<body>
<script>
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
<div>
<?php
if ($ret['status'] == 'success'){
	if ($_REQUEST['user_pwd'] == APP_USER_PWD){
		$_REQUEST['share'] = 1;
	}
	if ($_REQUEST['user_pwd'] != APP_USER_PWD) $_SESSION['user_pwd']=$_REQUEST['user_pwd'];//for onlineplayer
 
?>
<ul id="tabnav">
<?php
if (!isset($_REQUEST['share'])){ //owner list
	echo "<li class='owner'>"._("Device List")."</li>\n<li class=\"tab2\"><a href=\"javascript:document.forms['mylist'].submit();\">"._("Share")."</a></li>";
	setCamArray($_REQUEST['user_name'],$_REQUEST['user_pwd'],$CameraInfo,"");
	printLayoutTable($CameraInfo,"");
}else{
	if ($_REQUEST['user_pwd'] == APP_USER_PWD) //public account does not have
	echo "<li class='justdisable'>"._("Device List")."</li>\n<li class=\"tab2\"  class='owner'>"._("Share")."</li>";
	else	echo "<li class=\"tab1\"><a href=\"javascript:document.forms['mylist'].submit();\">"._("Device List")."</a></li>\n<li class='owner'>"._("Share")."</li>";
	setCamArray($_REQUEST['user_name'],$_REQUEST['user_pwd'],$CameraInfo,TYPE_SHARE);
	printLayoutTable($CameraInfo,TYPE_SHARE);
}
  if (isset($_REQUEST['debugadmin']))
    var_dump($CameraInfo);
}//ret status
?>
</ul>
<form  method=post name=mylist action="<?php echo $_SERVER['PHP_SELF'];?>"">
<input type=hidden name=user_name value="<?php echo $_REQUEST[user_name];?>">
<input type=hidden name=user_pwd value="<?php echo $_REQUEST[user_pwd];?>">
<?php if (isset($_REQUEST['debugadmin'])) echo "<input type=hidden name=debugadmin value='1'>"; ?>
<?php //if currently show share list, provide owner list form only. 
if (!isset($_REQUEST['share'])) echo "<input type=hidden name=share>";
?>
</form>
<script>
var txtNoVLC = "你的瀏覽器不支援 VLC元件, 請使用Internet Explorer/Firefox.";
var txtNoIEComp = "<br>如無法顯示元件,請開啟相容性檢視<br>";
var txtCopyVLC="請手動將網址複製後至VLC播放器!";
var isIE = /*@cc_on!@*/false || !!document.documentMode;
var isEdge = !isIE && !!window.StyleMedia;
var isFirefox = typeof InstallTrigger !== 'undefined';
var isChrome = /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor);
var isAndroid = /Android/.test(navigator.userAgent);
var isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent); 
//var isSafari = Object.prototype.toString.call(window.HTMLElement).indexOf('Constructor') > 0 || (function (p) { return p.toString() === "[object SafariRemoteNotification]"; })(!window['safari'] || safari.pushNotification);// Safari 3.0+ "[object HTMLElementConstructor]"
//var isOpera = (!!window.opr && !!opr.addons) || !!window.opera || navigator.userAgent.indexOf(' OPR/') >= 0;// Opera 8.0+
  
function ccopy(s) {
	alert("如果瀏覽器自動複製網址不成功,\n請手動按右鍵將網址複製後至VLC播放器!");
  var clip_area = document.createElement('textarea');
  clip_area.textContent = s;

  document.body.appendChild(clip_area);
  clip_area.select();
    
  document.execCommand('copy');
  clip_area.remove();
}

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
}else if (isAndroid){
	document.write("請將網址複製到<a href='intent://127.0.0.1/#Intent;scheme=http;package=org.videolan.vlc;end'>Android VLC播放器</a>-開啟網路串流");
}else if (isIOS){
	document.write("請將網址複製到<a href='itms://itunes.apple.com/tw/app/vlc-for-mobile/id650377962?mt=8'>iOS VLC播放器</a>-開啟網路串流");	
}else{
  //document.write(navigator.userAgent+"<br>");
  if  (isChrome!= null){
    if (isChromeNPAPI()){
      document.write("你的瀏覽器版本不支援NPAPI, 請使用Internet Explorer/Firefox52ESR或安裝<a href='https://chrome.google.com/webstore/detail/ie-tab/hehijbfgiekmjfkfjpbkbammjbdenadd?hl=zh-TW' target='_blank'>附加元件IETab</a>.");
      //setInterval("window.location = '/';",30000);
    }//support old chrome version
  }else{
      document.write(txtNoVLC);
      //setInterval("window.location = '/';",3000);
  }
}
</script>
</div>
</body>
</html>