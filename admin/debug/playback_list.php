<?php
/****************
 *Validated on Sep-7,2017
 *List camera video file
 *Parameter:
 *	mac: 12 digit MAC address
 *	user: user account name
 *	optional: datefolder, url  
 *if stream auth key required, need user pwd as well
 *(TODO) integrate with rpic/playback_list.php  	    
 *Writer: JinHo, Chang
*****************/
require_once ("/var/www/qlync_admin/doc/config.php");
require_once("/var/www/qlync_admin/doc/mysql_connect.php"); 
require_once("/var/www/qlync_admin/doc/sql.php");
header("Content-Type:text/html; charset=utf-8");
require_once("_iveda.inc"); //under /plugin/debug/
#Authentication Section
if (!isset($_SESSION["Email"]) )  die("Require Login!!\n");
if (!isset ($_REQUEST["debugadmin"])) die("No Permission!!\n");
//rpic
if (($oem=="T04") or ($oem=="T05") or ($oem=="K01") or ($oem=="T06") )
  $_REQUEST['key'] = "?"; //$_REQUEST['nokey'] = "";
else if (isset($_SESSION['SCID'])) //T06 series @X02
  if (($_SESSION['SCID']=="003") and ($_SESSION['RID']['03']=="1"))  $_REQUEST['key'] = "?";
else{ //check Contact if exist
  if (isset($_SESSION["Contact"]))
     if (isset($_SESSION["ContactPwd"]))
      $_REQUEST['key'] = getToken($_SESSION["Contact"],$_SESSION["ContactPwd"]);
}
############  Authentication Section End
//for $RPICAPP_USER_PWD
if (!defined('RPIC_FOLDER'))
  if ($oem=="T04")
  define("RPIC_FOLDER","/var/www/qlync_admin/plugin/taipei/");
  else if ($oem=="T05")
  define("RPIC_FOLDER","/var/www/qlync_admin/plugin/ty/");
  else
  define("RPIC_FOLDER","/var/www/qlync_admin/plugin/rpic/");

if (file_exists(RPIC_FOLDER."_iveda.inc"))
  require_once (RPIC_FOLDER."_iveda.inc");
else die("missing camera variable include file.");
//var_dump($_REQUEST);
######################################
if ( isset($_REQUEST["mac"]) or isset($_REQUEST["url"]) ){
  $CameraNAME = "";
  if ( isset($_REQUEST["mac"])){
    if (is_null( $CameraNAME=getCamName($_REQUEST["mac"])) ) exit(1);
  }else{
    if (isset($_REQUEST["url"])){
      $uid = explode("/",$_REQUEST["url"]);
      $_REQUEST["mac"] = substr($uid[2],6,12);
      if (is_null( $CameraNAME=getCamName($_REQUEST["mac"])) ) exit(1);
    }
  }
  //access other prople's camera
  //if ($CameraNAME == "") header ('Location: /html/member/login.php');

  if ( isset($_REQUEST['key']) ){ 
    $key = $_REQUEST['key'];
  }else if ( isset($_REQUEST['nokey']) )
    $key = "?"; 
  else if ($_REQUEST['user'] == $_SESSION["stream_token_key_user"])
  {
    if ($_REQUEST['stream_token_key_pwd']==""){//reset
        $_SESSION["stream_token_key_user"]="";
        $newhtml= printAuthForm($_REQUEST);
    }else{
      if ( time() < $_SESSION["stream_token_key_expire"])
        $key = $_SESSION["stream_token_key"];
      else  $key =getToken($_REQUEST['user'],$_SESSION["stream_token_key_pwd"]);
    } 
  }else if (isset($_REQUEST['pwd']) and isset($_REQUEST['user']) ){
    if ($_REQUEST['pwd']=="")
        $newhtml= printAuthForm($_REQUEST);
    else{
    $key =getToken($_REQUEST['user'],$_REQUEST['pwd']);
    $_SESSION["stream_token_key_user"] = $_REQUEST['user'];
    $_SESSION["stream_token_key_pwd"] = $_REQUEST['pwd'];
    }
  }
    //clean url if datefolder checked
  if (isset($_REQUEST['datefolder'])){
    $urltmp = explode("/",$_REQUEST["url"]);
    if ($_REQUEST['datefolder'] !=$urltmp[3])  $_REQUEST["url"]="";
  }
//var_dump($_REQUEST);
  if ($_REQUEST["url"]!=""){
      
    if (isLAN() ){
      $mac_streamserver=getStreamServerInfo($_REQUEST["mac"],"LAN");
      if ($mac_streamserver!="")
            $videosrc = "http://".$mac_streamserver.$_REQUEST["url"];
    }else{
        $mac_streamserver=getStreamServerInfo($_REQUEST["mac"],"INTERNET");

        if ($key !=""){
          if ($mac_streamserver!="")
              $videosrc = "http://".$mac_streamserver.$_REQUEST["url"]."?key={$key}";
        }else{
          if ($_SESSION["Contact"] == $_REQUEST["user"]){//for godwatch
          if (strpos($_REQUEST["user"],"-")!==FALSE) //found
            $tmp=substr($_REQUEST["user"],strpos($_REQUEST["user"],"-")-4, 4);
          else  $tmp=substr($_REQUEST["user"], -4);; //last 4 digit or preset pwd
          $_REQUEST["pwd"]=$tmp;
          $newhtml= printAuthForm($_REQUEST);
          }else{ 
            $newhtml= printAuthForm($_REQUEST);
          }
        }
      }//isLAN
  }//URL !=""
}else{
  header ('Location: /html/member/login.php');
}

function printAuthForm($req){
  $newhtml= "<form method=post action={$_SERVER['PHP_SELF']}><input type=text name=user value='{$req['user']}' size=5 placeholder='Camera Username'>";
  if ($req['pwd']!="")
    $newhtml.="<input  type=text name=pwd value='{$req['pwd']}' size=5 placeholder='Password'>";
  else
    $newhtml.="<input  type=text name=pwd size=5 placeholder='Password'>";
  $newhtml.="<input type=hidden name=url value='{$req['url']}'><input type=hidden name=mac value='{$req['mac']}'><input type=hidden name=datefolder value='{$req['datefolder']}'><input type=submit value='".BTN_AUTH."'><input type=hidden name=debugadmin value='1'></form>";
  //<input type=submit name=nokey value='nokey'>
  return $newhtml;
}
?>

<html>                             
<head>
<title>Video Playback</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<?php
if (isMobile())
{
	 printMobileMeta();
}//detect user agent
?>
</head>
<body>
<script>
function optionValue(thisformobj, selectobj)
{
	var chosenoption=selectobj.options[selectobj.selectedIndex];
  thisformobj.value = chosenoption.value;
}
</script>
<div><b><font size=5><?php echo $_REQUEST["mac"].(($CameraNAME!="") ? "(".$CameraNAME.")":"").TXT_PLAYBACK;?></font></b></div>
<?php
if ($newhtml!="") echo $newhtml;
?>
<div id="container">
<form id="group1" method="post"	action="<?php echo $_SERVER['PHP_SELF'];?>">
<?php
if (isset ($_REQUEST["mac"]))  echo "<input type=hidden name=mac value=".$_REQUEST["mac"].">";
if (isset ($_REQUEST["user"]))  echo "<input type=hidden name=user value=".$_REQUEST["user"].">";
if ($key!="")  echo "<input type=hidden name=key value='{$key}'>";
if (isset ($_REQUEST["debugadmin"]))   echo "<input type=hidden name=debugadmin value='1'>"; //jinho debugadmin

////add date picker
if ( $_REQUEST["url"]!="" )  //month select
  getMonthPicker("datefolder",$_REQUEST["url"],$_REQUEST['datefolder']);
else
  getMonthPicker("datefolder",$_REQUEST["mac"],$_REQUEST['datefolder']);

 //url select
$tmp="";
if ( isset($_REQUEST["datefolder"]) )
  $tmp = "%{$_REQUEST["mac"]}/{$_REQUEST["datefolder"]}/%";
else if ( $_REQUEST["url"]!="" )
  $tmp = "%{$_REQUEST["mac"]}%";
else exit(1);
if ($tmp!="")
  createMyList($_REQUEST["url"]," where path like '{$tmp}' order by end desc");

if  ( $_REQUEST["url"]!="" ){ //download button
    $tmp = (isset($RPICAPP_USER_PWD[$oem]))? $RPICAPP_USER_PWD[$oem][0]:$RPICAPP_USER_PWD['RPIC'][0]; 
    //$downloadurl=getWebURL("https://")."/backstage_download.php?key={$tmp}&path=".$_REQUEST["url"];
    $downloadurl=getWebURL("http://")."/backstage_download.php?key={$tmp}&path=".$_REQUEST["url"];
  echo "<input type=button onclick=\"javascript:location.assign('{$downloadurl}');\" value='".BTN_DOWNLOAD."'>";
}
/******* // this can be replaced in the html5 download button
if($videosrc!="" ){
  $downloadurl = "http://".getStreamServerInfo($_REQUEST["mac"],"LAN").$_REQUEST["url"]; //directly get from stream
  echo "<input type=button onclick=\"javascript:location.href='getVideo.php?URL=".urlencode($downloadurl)."'\" value=download>";
} */
/*else{
  for($i=0;$i<sizeof($cam);$i++)
    createMyList($_REQUEST["url"]," where path like '%{$cam[$i]["mac"]}%' order by end desc limit 10");
}*/
?>
</form>
<?php if($videosrc!="" ){
?>
<video width="480" height="320" controls>
  <source src="<?php echo $videosrc;?>" type="video/mp4">
Your browser does not support the video tag.
</video>
<?php  
}//if videosrc
?>

</div>
</body>
</html>           