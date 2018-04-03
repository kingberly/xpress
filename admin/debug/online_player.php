<?php
/****************
 *Validated on May-17,2016
 *Admin to show liveview camera
 *Parameter:
 *	uid: 12 digit MAC address or 18 digit UID both acceptable
 *	user: user account name
 *if stream auth key required, need user pwd as well    
 *Writer: JinHo, Chang
*****************/
require_once ("/var/www/qlync_admin/doc/config.php");
include("/var/www/qlync_admin/doc/mysql_connect.php"); 
include("/var/www/qlync_admin/doc/sql.php");
include("_iveda.inc"); //under /plugin/debug/
if (!isset($_SESSION["Email"]) ) exit();
header("Content-Type:text/html; charset=utf-8");
#Authentication Section
if ( !isset($_REQUEST["debugadmin"]) and !isset($_SESSION["Contact"]) ) exit();
if (($oem=="T04") or ($oem=="T05") or ($oem=="K01")) $_REQUEST['nokey'] = "?";
if (!isset($_REQUEST["IE"])) $IE=1;
else $IE=$_REQUEST["IE"];
############  Authentication Section End

//var_dump($_REQUEST);
######################################
if ( isset($_REQUEST["uid"]) ){
  $CameraNAME = "";
  //$sql="select uid,name from isat.query_info where user_name='{$_SESSION["Contact"]}' group by mac_addr order by mac_addr";
  $sql="select * from isat.query_info where uid like '%{$_REQUEST["uid"]}'";
  sql($sql,$result_email,$num_email,0);
  for($i=0;$i<$num_email;$i++)
  {
          fetch($db_email,$result_email,$i,0);
          if ($db_email["uid"] == $_REQUEST["uid"] ){
              $CameraNAME = $db_email["name"];
              if ($CameraNAME == $db_email["mac_addr"]) $CameraNAME=""; 
              break;
          }
          //$cam[$i]["mac"]         = $db_email["mac_addr"];
          //$cam[$i]["name"]        = $db_email["name"];
  }
  //limit access of own camera
  //if ($CameraNAME == "") header ('Location: /html/member/login.php');
  if (isLAN() ){
      $mac_streamserver=getStreamServerInfo($_REQUEST["uid"],"LAN");
      if ($mac_streamserver!="")
            $videosrc = "http://".$mac_streamserver."/".$_REQUEST["uid"];
  }else{
      if ( isset($_REQUEST['nokey']))
        $key = "?";
      else if ($_REQUEST['user'] == $_SESSION["stream_token_key_user"])
      {
          if ( time() < $_SESSION["stream_token_key_expire"])
            $key = $_SESSION["stream_token_key"];
          else  $key =getToken($_REQUEST['user'],$_SESSION["stream_token_key_pwd"]); 
      }else if ( isset($_REQUEST['pwd']) and isset($_REQUEST['user']) ){
        $key =getToken($_REQUEST['user'],$_REQUEST['pwd']);
        $_SESSION["stream_token_key_user"] = $_REQUEST['user'];
        $_SESSION["stream_token_key_pwd"] = $_REQUEST['pwd'];
      }
      if ($key!=""){
        $mac_streamserver=getStreamServerInfo($_REQUEST["uid"],"INTERNET");
        if ($mac_streamserver!="")
              $videosrc = "http://".$mac_streamserver."/".$_REQUEST["uid"]."?key={$key}";
      }else{
          if ($_SESSION["Contact"] == $_REQUEST["user"]){
          $tmp=substr($_REQUEST["user"], -4);; //last 4 digit or preset pwd
          $msg_err= "<form method=post action={$_SERVER['PHP_SELF']}><input type=text name=user value='{$_REQUEST["user"]}' size=5 placeholder='Camera Username'><input  type=text name=pwd value='{$tmp}' size=5 placeholder='Password'><input type=hidden name=uid value='{$_REQUEST["uid"]}'><input type=hidden name=IE value='{$IE}'><input type=submit value='Authenticate'><input type=submit name=nokey value='nokey'></form>";
          }else{
          $msg_err= "<form method=post action={$_SERVER['PHP_SELF']}><input type=text name=user value='{$_REQUEST["user"]}' size=5 placeholder='Camera Username'><input  type=text name=pwd size=5 placeholder='Password'><input type=hidden name=uid value='{$_REQUEST["uid"]}'><input type=hidden name=IE value='{$IE}'><input type=submit value='Authenticate'><input type=submit name=nokey value='nokey'></form>";
          }
      }
  }
          
        
}else{
  header ('Location: /html/member/login.php');
}


?>
<!DOCTYPE html>
<html>                             
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>
<script>
var isChrome = /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor);
var isIE = /*@cc_on!@*/false || !!document.documentMode;
var isEdge = !isIE && !!window.StyleMedia;
var isFirefox = typeof InstallTrigger !== 'undefined';
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
<div align=center><b><font size=3><?php if ($CameraNAME!="") echo $_REQUEST["uid"]."(".$CameraNAME.")"; else echo $_REQUEST["uid"];?> Liveview</font></b></div>
<?php
if ($msg_err!="") echo $msg_err;

echo "<form name=switchBrowser method=post action={$_SERVER['PHP_SELF']}><input type=hidden name=uid value='{$_REQUEST["uid"]}'><input type=hidden name=user value='{$_REQUEST["user"]}'><input type=hidden name=IE value='{$IE}'><input type=submit id='switchBtn' value='Switch to IE' style='display: none;'></form>";
?>
<div id="container">
<script>
var videosrc = "<?php echo $videosrc;?>";
function muteVLC(objname)
{
      var vlc = document.getElementById(objname);
      vlc.audio.toggleMute();
}
function ccopy(s) {
	alert("If Auto Copy URL Fail,\nPlease use Mouse Right Click to manually copy URL to VLC player!");
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
            //document.write("TestIE11 Version:"+rv);
        }
        document.write("<br>Please Turn On Compatibility View if fail to see LiveView.<br>");
     }else{
        document.write("Not IE");
     }
  }else{  //<IE11
    var msieV=parseInt(ua.substring(msie + 5, ua.indexOf(".", msie)));
    //document.write("Version:"+msieV);
    if (msieV!=7) document.write("<br>Please Turn On Compatibility View if fail to see LiveView.<br>");
    //document.write("<br>Please Turn Off Compatibility View.<br>");
  } 
}else if (isEdge){//not working
  //document.write("Edge:"+navigator.userAgent+"<br>");
  document.write("Your Browser Does not Support VLC, use Internet Explorer/FireFox instead.");
  document.write("<br>Paste below URL to VLC player:<br><a href='"+videosrc+"' onclick='javascript:ccopy(\""+videosrc+"\");return false;'>"+videosrc+"</a><br>");
  //setInterval("window.location = '/';",10000);
}else if (isFirefox){//firefox 46, 36 ok
  //document.write("Firefox:"+navigator.userAgent+"<br>");
}else{
  if  (isChrome!= null){
    if (isChromeNPAPI()){
       document.write("Your Chrome does not support NPAPI Plugin.");
       document.write("<br>Paste below URL to VLC player:<br><a href='"+videosrc+"' onclick='javascript:ccopy(\""+videosrc+"\");return false;'>"+videosrc+"</a><br>");
       //setInterval("window.location = '/';",10000);
    }
  }
}
</script>
<?php if($videosrc!="" ){ //<param name='movie' value='{$videosrc}'/>\n
  if ($_REQUEST['IE']=="1"){
  //data='{$videosrc}' 
  //codebase='http://download.videolan.org/pub/videolan/vlc/0.9.2/win32/axvlc.cab'
    echo "<Br><object type='application/x-vlc-plugin' width='480' height='320' id='video1'>\n<param name='src' value='{$videosrc}'/>\n";
    echo "</object>\n";
  }else{
    //volume='50' mute='true'  not working
    echo "<embed type='application/x-vlc-plugin' id='video1'\n
       autoplay='yes' loop='no' controls='yes' width='480' height='320'\ntarget='{$videosrc}' />\n";
  }//IE 
}else{
  if ($key!="")  echo "Camera is Offline!!\n";
  else  echo "Please Provide Authenciation to access!\n";
}
?>
<script type="text/javascript">
//IE fail to load vlc plugin
if (isFirefox){
  muteVLC("video1");
}else if  (isChrome!= null){
    if (!isChromeNPAPI()){
        muteVLC("video1");
    }
}
</script>
</div>
</body>
</html>           