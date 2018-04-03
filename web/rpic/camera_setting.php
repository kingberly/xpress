<?php
/****************
 *validated on Mar-14,2016
 * reference from device_matrix.php 
 * /var/www/SAT-CLOUDNVR/  
 *Writer: JinHo, Chang
 * camera_setting.php?adminpwd=APP_USER_PWD&mac=<MAC>   
*****************/ 
// note that this file is only used for mms play now
include_once( "./include/global.php" );
include_once( "./include/utility.php" );
include_once( "./include/index_title.php" );
include_once( "./include/db_function.php" ); //jinho added
include_once( "./include/user_function.php" ); //jinho added for login
//include_once( "./include/device_function.php" ); //jinho added for deviceid
//if (!IsUserLoggedIn()) {header('Location: login.php'); exit;}
//includeOemGlobal( (isset($_SESSION['oem']) ? $_SESSION['oem'] : null) );

require_once("rpic.inc"); //$RPICAPP_USER_PWD

if ($RPICAPP_USER_PWD[OEM_ID]==NULL)  
	define("ADMIN_PWD",$RPICAPP_USER_PWD['RPIC']);
else define("ADMIN_PWD",$RPICAPP_USER_PWD[OEM_ID]);

if ($_REQUEST['adminpwd']!=ADMIN_PWD){$_SESSION = NULL;header('Location: /'); exit;}
if ($_REQUEST['mac']=="") {header('Location: login.php'); exit;}
else if (!preg_match('/^[a-zA-Z0-9]{12}$/', $_REQUEST['mac']))  {
	echo "Invalid MAC format";
	exit();
}
$_REQUEST['mac']=strtoupper($_REQUEST['mac']);
//if (!chkSourceFromMegasys($accessip)) {header('Location: login.php'); exit;}
/*if (chkSourceFromMegasys($accessip)) echo "From Office {$accessip}";
else echo "Other IP {$accessip}";*/

$_SESSION["request_service_type"] = "camera";
$_SESSION["user_id"]=getUserIDByMAC($_REQUEST['mac']);//camera owner id;
if ($_SESSION["user_id"]=="") {echo "Unreconized Camera UID.";exit();} 
$_SESSION["user_name"]=getUserName($_SESSION["user_id"]);
$_SESSION['login_failure'] = array();
if (!isset($_GET["mode"]) )  $_GET["mode"]="personal";
getUserInfo($_SESSION["user_name"],$user_info_row);
StoreUserInfoInSession( $user_info_row );
//force add position
if (!deviceEnable($_SESSION["user_id"],$_REQUEST['mac'])){
	echo $_REQUEST['mac']." is not registered!!";
	exit();
}


function getDeviceIDByMAC($data_db,$mac)
{ //RVLO only
	$condition .= "mac_addr=:mac AND service_type='camera' AND purpose='RVLO'";
	$param[':mac'] = $mac;
	$device_entry =$data_db->QueryRecordDataOne('device', $condition, 'id', $param);
	return $device_entry['id'];
}
function deviceEnable($userid,$mac) //device exist?
{
	$data_db = new DataDBFunction();
	$dev_id =getDeviceIDByMAC($data_db,$mac);
	//echo $dev_id;
	//cleanup before Add??     device_list.php
	//$data_db->AddRemoveSeq(false,false,$userid,$dev_id); //isfavor, isadd
	//backstage_device.php?command=addremoveseq&device_id=7390&isadd=true&isfavorite=false&request_device_type=&request_service_type=camera
	//AddRemoveSeq($_GET["isadd"],$_GET['isfavorite'],$_SESSION["user_id"],$_GET['device_id']);
	if ($dev_id!=NULL){ 
		$data_db->AddRemoveSeq(true,false,$userid,$dev_id); //isfavor, isadd
		return true;
	}
	return false;
}

function chkSourceFromMegasys(&$cip)//true / false
{
//bypass if protocol is https
	if(isset($_SERVER['HTTPS'])){
		$cip="https";
		return true;
	} 
  $MegasysIP = [
  "118.163.90.31",
  "59.124.70.86",
  "59.124.70.90",
  "125.227.139.173",
  "125.227.139.174",
  "123.193.125.132",
  "61.216.61.162"
  ];
    if($_SERVER['HTTP_CLIENT_IP'] !="" )
        $client_ip = $_SERVER['HTTP_CLIENT_IP'];
    if($_SERVER['HTTP_X_FORWARDED_FOR']!=""  and $client_ip=="" ) 
        $client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    if($_SERVER['REMOTE_ADDR']!="" and $client_ip=="" )
        $client_ip = $_SERVER['REMOTE_ADDR'];
    for ($i=0;$i<sizeof($MegasysIP);$i++){
      if (strpos($client_ip, $MegasysIP[$i]) !== FALSE){
        $cip=$MegasysIP[$i];
        return true;
      }
    }
    $cip = $client_ip;
    return false;
}
function getUserInfo($name,&$user_info_row)
{
	$data_db = new DataDBFunction();
	$conditions = 'name=:name';
	$params = array(':name' => $name);
	$user_info_row = $data_db->QueryRecordDataOne('user', $conditions, '*', $params);
}
function getUserIDByMAC($mac) {
  // Get user
  $uid = getUID($mac);
  if ($uid=="") return "";
  $data_db = new DataDBFunction();
  $table = 'device';
  $condition = "uid=:uid";
  $params = array(':uid'=>$uid);
  $user_id = $data_db->QueryRecordDataOne($table, $condition, 'owner_id', $params);
  return $user_id["owner_id"];
}
/* //duplicate with rpic.inc
function getUID($mac)
{
	global $CamCID;
  $uid="";
  foreach ($CamCID as $cid => $prefix)
  {
     if ( preg_match("/^".$prefix."/",strtoupper($mac)) )
      $uid = $cid. "-".strtoupper($mac);
  }
  if ($uid=="") $uid=$mac;
  return $uid;
}
*/
function getUIDType($mac)
{
	global $CamCID;
  foreach ($CamCID as $cid => $prefix)
  {
     if ( preg_match("/^".$prefix."/",strtoupper($mac)) )
      return $cid;
  }
  return "";
}
function getUserName($id) {
  // Get user
  $data_db = new DataDBFunction();
  $table = 'user';
  $condition = "id=:id";
  $params = array(':id'=>$id);
  $user_id = $data_db->QueryRecordDataOne($table, $condition, 'name', $params);
  return $user_id["name"];
} 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!--[if lt IE 7 ]> <html class="ie6" xmlns="http://www.w3.org/1999/xhtml"> <![endif]-->
<!--[if IE 7 ]>    <html class="ie7" xmlns="http://www.w3.org/1999/xhtml"> <![endif]-->
<!--[if IE 8 ]>    <html class="ie8" xmlns="http://www.w3.org/1999/xhtml"> <![endif]-->
<!--[if IE 9 ]>    <html class="ie9" xmlns="http://www.w3.org/1999/xhtml"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html class="" xmlns="http://www.w3.org/1999/xhtml"> <!--<![endif]-->
<head>
<title>Camera Setting NTP</title>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><![endif]-->

<?php require( "./include/common_css_include.php" ); ?>
<?php require( "./include/common_js_include.php" ); ?>
<link href="<?php echo $oem_style_list['css']['device_matrix']; ?>" rel="stylesheet" type="text/css" charset="utf-8">
<link href="<?php echo $oem_style_list['css']['pagelist']; ?>" rel="stylesheet" type="text/css" charset="utf-8">
<link href="shadowbox-3.0.3/shadowbox.css?20120708" rel="stylesheet" type="text/css" charset="utf-8">
<script type="text/javascript" src="js/jquery.client.js"></script>
<script type="text/javascript" src="js/device_common.js?20140331"></script>
<script type="text/javascript" src="js/deployJava.js"></script>
<script type="text/javascript" src="js/page_control.js?20120526"></script>
<script type="text/javascript" src="js/jquery.media.js"></script>
<script type="text/javascript" src="js/jquery-ui.min.js"></script>
<script type="text/javascript" src="js/customSort.js"></script>
<script type="text/javascript" src="js/java_vlc_control.js?20120567"></script>
<!--script type="text/javascript" src="js/device_matrix.js?20130906"></script-->
<script type="text/javascript" src="js/device_matrix_ts.js?20130906"></script>
<script type="text/javascript" src="shadowbox-3.0.3/shadowbox.js"></script>
<script type="text/javascript" src="js/googleRelay.js?20120605"></script>
<script type="text/javascript">
	var p2p_jars = "<?php echo $oem_style_list['p2p_jars']; ?>";
	var p2p_vlc_name = "<?php echo $oem_style_list['java_p2p_vlc_title']; ?>";
	var refresh_token = "<?php if (isset($_SESSION["refresh_token"])) echo $_SESSION["refresh_token"]; else echo "";?>"; //"p2p";

	var request_device_type = "<?php if (isset($_SESSION["request_device_type"])) echo $_SESSION["request_device_type"]; else echo "";?>"; //"p2p";
	var request_service_type = "<?php if (isset($_SESSION["request_service_type"])) echo $_SESSION["request_service_type"]; else echo "camera";?>";

	var checkdevice = "<?php if (isset($_GET["checkdevice"])) echo $_GET["checkdevice"]; else echo "";?>";
	var autobinding_cid = "<?php echo $oem_style_list['autobinding']?>";

	//is favorite mode
	var favorite_mode = '<?php if (isset($_GET["favorite_mode"])) echo $_GET["favorite_mode"]; ?>';
	if (favorite_mode=='true')
		favorite_mode = true;
	else
		favorite_mode = false;

	my_favorite = Array();
	// mode
	var mode = '<?php if(isset($_GET["mode"])) echo $_GET["mode"]; else echo "public"; ?>';
	//if (mode== "" ) mode = "public";
	// get user name
	var user_name = '<?php echo $_SESSION["user_name"];?>';
	var user_id = '<?php echo $_SESSION["user_id"];?>';
	var direct_connection = '<?php echo $oem_style_list['direct_connection']?'true':'false';?>';
	var public_stream_auth = <?php echo PUBLIC_STREAM_AUTH?'true':'false';?>;

	// get a random number for matrix ordery
	var order_by = Math.round((Math.random()+1)*2147483647)%15 + 1;

	var max_nail = 0;

	//timer for snapshot
	var snapshot_timer;

	// if already login, user device id for order
	if( mode != "public" )	order_by = "sequential";
	//if(favorite_mode) order_by = "1";//order_by = "myfavorite_seq";

	//store sequential list , for debug
	var sequential_list = Array();
  
	function PageControlCallback( current_page_no )
	{
		RefreshDisplay( mode, $("select#display_type-select").val(), order_by );
	}

	// new page control
	var page_control_object = null;

	$(function(){
		<?php
			//if user login , trigger Java P2P keep alive program
			if(isset($_GET["mode"]) && $_GET["mode"]=="personal" && isset($_SESSION["user_id"]) && $_SESSION["user_id"]!=""){
				echo "try{";
				echo "parent.initJavaBackStage();";
				echo "} catch(err) {}";
			}
		?>
		// get user pref matrix display mode
		var matrix_display_mode = '<?php echo $_SESSION["user_matrix_display_mode"]; ?>';
		if( matrix_display_mode == "" || isNaN(parseInt(matrix_display_mode)) ) matrix_display_mode = 3; // default to 3x3
		else matrix_display_mode = parseInt(matrix_display_mode);
		$("select#display_type-select").val(matrix_display_mode);
		<?php
			//jinho jump to last page, cannot get correct g_page_control_total_page_no before fully loaded 
			if(isset($_REQUEST['page'])){
				if ( (intval($_REQUEST['page'])>1) ) 
					echo "g_page_control_current_page_no = ".$_REQUEST['page']." -1;";
				}
		?>
		//document.write(g_page_control_current_page_no);
		// setup the page control button layer
		setupBtnLayer();

		// generate hte page control object
		InitializePageControl(
			"select#page_control-select",
			"div#page_first-btn",
			"div#page_prev-btn",
			"div#page_next-btn",
			"div#page_last-btn",
			PageControlCallback
		);
		// display data Moved because of info system
		// RefreshDisplay( mode, $("select#display_type-select").val(), order_by );
		RefreshInfoSystem();

		// display type on change
		$("select#display_type-select").change(function(){
			RefreshDisplay( mode, $("select#display_type-select").val(), order_by );
		});

		ActiveShadowBoxInit();
		setTimeout("QuerySignal();",2000);
		singal_timer = self.setInterval("QuerySignal();", 10000);
		self.setInterval("changeSignalLight();", 1000);

		if (!autobinding_cid)
			setTimeout("checkDeviceCount();",700);
	});
</script>
</head>
<body>
<script>
var zavioAccountPwd = "admin:admin%40Iveda1688@"; //admin@Iveda1688
var messoaAccountPwd = "admin:1234@";
var baycomAccountPwd = "admin:admin%40Iveda1688@";
var cameraPwd=<?php $mycid=getUIDType($_REQUEST['mac']); if ($mycid=="M04CC") echo "messoaAccountPwd"; else if ($mycid=="Z01CC") echo "zavioAccountPwd"; else if ($mycid=="B03CC") echo "baycomAccountPwd";?>;
function checkip(lanip)
{
	if (lanip=="127.0.0.1:") {
		alert("Please fill in Tunnel port\n by Clicking Setting of Target Camera");
		return false;
	} 
	return true;
}
function linkMessoaAdminPage(lanip,link1)
{//hex code of @ is %40
    var cmd = "http://"+messoaAccountPwd+lanip+"/operator/get_param.cgi?"+link1;
    if (checkip(lanip))
    var wnd=window.open(cmd, 'Messoa', config='height=200,width=250');
    return wnd;
}
function linkMessoaAdminPageSet(lanip,link1,param)
{//hex code of @ is %40
    var cmd = "http://"+messoaAccountPwd+lanip+"/operator/set_param.cgi?"+link1+param;
    if (checkip(lanip))
    window.open(cmd, 'Messoa', config='height=200,width=250');
    return cmd;
}
function linkZavioAdminPage(lanip,link1)
{//hex code of @ is %40
    var cmd = "http://"+zavioAccountPwd+lanip+"/cgi-bin/admin/param?"+link1;
    if (checkip(lanip))
    var wnd=window.open(cmd, 'Zavio', config='height=200,width=250');
    return wnd;
}
function linkZavioAdminPageParam(lanip,link1,param)
{//hex code of @ is %40
    var cmd = "http://"+zavioAccountPwd+lanip+"/cgi-bin/admin/param?"+link1+param;
    if (checkip(lanip))
    window.open(cmd, 'Zavio', config='height=200,width=250');
    return cmd;
}
var wobj=null;
function linkPage(lanip)
{
	if (!checkip(lanip)) return;
	var msg="確定攝影機的連線IP是"+lanip+"?\n此為遠端連線,請勿使用此連線燒錄韌體!!存取同網域攝影機請按Localweb鍵!";
	if (confirm(msg)==true){
  		window.open("http://"+cameraPwd+lanip, 'Tunnel Camera', config='height=600,width=650');
  }
}
function linkLocalPage(lanip)
{
	if (wobj!=null){
		if (wobj.closed) {
			if (openIPWnd(lanip))
				setTimeout(function() { linkLocalPage(lanip); }, 1000);
			return;
		}
		var localip = prompt("確定攝影機與電腦同一網域.\n區域網路連線IP:", "192.168.0.");
/*if (cameraPwd == messoaAccountPwd)
		localip = wTXT.substring(wTXT.indexOf("cur_ip=")+7,wTXT.lengh); 
	else if (cameraPwd == zavioAccountPwd)
		localip = wTXT.substring(wTXT.indexOf("IPAddress=")+9,wTXT.lengh);
*/
		if (localip==null) return;
		if (!(/^(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\.(25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)$/.test(localip)))
		{
			alert("Please fill in Correct IP");
			return false;
		}
		window.open("http://"+cameraPwd+localip, 'Local Camera', config='height=600,width=650');		
    wobj.close();
	}else{
		if (openIPWnd(lanip))
			setTimeout(function() { linkLocalPage(lanip); }, 1000);
		return;
	}
}
function openIPWnd(lanip)
{
	if (!checkip(lanip)) return false;
		if (cameraPwd == messoaAccountPwd) 
			wobj = linkMessoaAdminPage(lanip,"network.lan.cur_ip");
		else if (cameraPwd == zavioAccountPwd)
			wobj = linkZavioAdminPage(lanip,"action=list&group=General.Network.eth0.IPAddress");
		return true;
}
/*
//XMLHttpRequest limited by same domain policy
//var resIP = getText(cmd, mycallback); //limited by domain
getText= function(url, callback) // How can I use this callback?
{
    var request = new XMLHttpRequest();
    request.onreadystatechange = function()
    {
        if (request.readyState == 4 && request.status == 200)
        {
            callback(request.responseText); // Another callback here
        }
    }; 
    request.open('GET', url);
    request.send();
}

function mycallback(data) {
   alert(data);
}
*/
function openVLCPage(uid) //for device_matrix_ts.js
{
	var path="https://<?php echo $_SERVER['HTTP_HOST'];?>/backstage_onlineplayer.php?mac=" + uid;
	//alert(path);
	window.open(path,'',config='height=450,width=450');
}

</script>
<div>
<br>
<form name=form1 id=form1>
Camera Setting Tunnel IP <input type=text name="camip" value='127.0.0.1:'  style="width: 10em;">
<input type=button onclick="linkPage(this.form.camip.value);" value="web"  style="width: 3em;">
<input type=button onclick="linkLocalPage(this.form.camip.value);" value="Localweb"  style="width: 5em;">
<?php if (isset($_REQUEST['debugadmin']) ){ ?>
<input type=button onclick="linkMessoaAdminPage(this.form.camip.value,'network.lan.cur_ip&network.lan.mac');" value="M04IP"  style="width: 3em;">
<input type=button onclick="linkZavioAdminPage(this.form.camip.value,'action=list&group=General.Network.eth0.IPAddress&group=General.Network.eth0.MACAddress');" value="Z01IP"  style="width: 3em;">
<?php }
if (OEM_ID=="T04"){ ?>
<input type=button onclick="linkMessoaAdminPage(this.form.camip.value,'system.datetime.ntpinterval&system.datetime.method&system.datetime.ntpserver','tw.pool.ntp.org');" value="Messoa NTP Status(4/2/tw)"  style="background-color:grey;width: 11em;">
<input type=button onclick="linkMessoaAdminPageSet(this.form.camip.value,'system.datetime.ntpinterval=4&system.datetime.method=2&system.datetime.ntpserver=','tw.pool.ntp.org');" value="Messoa setNtpServer hourly" style="background-color:Aquamarine;width: 15em;">
<?php } ?>
<input type=button onclick="linkZavioAdminPage(this.form.camip.value,'action=list&group=General.Time.NTP.Server&group=General.Time.SyncSource');" value="Zavio NTP Status" style="background-color:grey;width: 8em;">
<input type=button onclick="linkZavioAdminPageParam(this.form.camip.value,'action=update&General.Time.SyncSource=NTP&General.Time.NTP.ManualServer=tw.pool.ntp.org&General.Time.NTP.Server=','tw.pool.ntp.org');" value="Zavio setNtpServer@TW" style="background-color:pink;width: 13em;">
</form>
<a href="/device_list.php?mode=rpic" target='_blank'><?php echo $_SESSION["user_name"];?> Device List</a>
</div>
<div class="off" id="frame_loading_cover"></div>
<!--display mode select-->
<select id="display_type-select" style="float:left ; display:none">
	<option value="2">2X2</option>
	<option value="3">3X3</option>
	<option value="4">4X4</option>
</select>

<!--page control select-->
<select id="page_control-select" style="float:left; display:none"></select>

<div id="toolbar_div">
	<div class="toolbarL">
	<div id="btn_plus" class="displaynone" onclick="OpenActiveBox();"></div>

        <?php if (defined('OEM_URL')): ?>
        <div id="btn_enlarge" title="<?php echo _('Enlarge') ?>" onclick="OpenLargeMatrix();"></div>
        <?php endif; ?>
    </div>
    <div class="toolbarR">


    </div>
</div>
<div id="device_matrix" class="left_list"></div>
<div id="downer_div">
	<div id="whole_button_cover">
	
	<div class="_page_button_cover">
        <div id="page_first-btn" class="btn_sharef floatleft" title="<?php echo _("First Page");?>"><span class="btn_page_firstpage"></span></div>
        <div id="page_prev-btn" class="btn_sharef floatleft"  title="<?php echo _("Previous Page");?>"><span class="btn_page_pre"></span><?php echo _('Prev');?></div>
        <div id="page_next-btn" class="btn_sharef floatleft"  title="<?php echo _("Next Page");?>"><?php echo _('Next');?><span class="btn_page_next"></span></div>
        <div id="page_last-btn" class="btn_sharef floatleft"  title="<?php echo _("Last Page"); ?>"><span class="btn_page_lastpage"></span></div>
	</div>
	<div id="_page_button">
		<div class="center_cover">
			<input id="_page_now" type="text" value="" id="" /> / <span id="_page_total">0</span>
			<span id='auto_cycle'>
				<!--div id='cb_auto_cycle' class='_checkbox-disable' style='display: inline-block'></div><?php echo _("Auto Cycle");?>
				-->
			</div>
		</div>
	</div>
</div>
<?php 
//include_once("./include/tail.php"); //jinho remark 
?>
<script>
oForm = document.getElementById("form1");//document.forms[0];
//oForm.elements["camip"].value will be used for device_matrix_ts.js openweb
</script>
</body>
</html>
