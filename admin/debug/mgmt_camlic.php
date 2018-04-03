<?php
/****************
 *Validated on Dec-22,2017,
 * delete and update camera assignment on servers
 * Add /move camera assignment on servers
 * add debugadmin feature to hide remove button
 * add debugadv feature to hide advance button 
 * check rtmp exist or not
 * integrate delete_server_cam.php
 * add recording_list check (playback required)
 * add drop down filter display
 * fix camera model limitation (origialn support Z01/M04 only)
 * fix clean NULL device_uid for recording_list 
 * fix updateHAserver, updateHAserverLimit on port_slot             
 *Writer: JinHo, Chang
*****************/
include("../../header.php");
include("../../menu.php");
require_once '_auth_.inc';
if (file_exists("_debug_.inc")) 
  require_once '_debug_.inc';
define("STREAM_SRV","1");
define("TUNNEL_SRV","2");
define("RTMP_SRV","3");
define("RECORDING_LIST","4");
define("TUNNEL_DEFAULT_PORT",50000);
define("TUNNEL_MAX_PORT",65530);
$CamCID=[//CID, PREFIX
  "M04CC"=>"184E",
  "Z01CC"=>"001B",
  "B03CC"=>"0050",
  "A02CC"=>"3C49",
  "F01CC"=>"2C62",
  $oem."MC"=>"M".$oem
];
$CamPROFILE=array("RVLO", "RVME", "RVHI");
//var_dump($_REQUEST);
$bNewServer = TRUE; // for new server /rtmp
$sql = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'isat' AND TABLE_NAME = 'tunnel_server' AND COLUMN_NAME = 'purpose'";
sql($sql,$result,$num,0);
if ($num==0) $bNewServer = FALSE;

if (isset($_REQUEST['uid_filter']) )
{
  if ($_REQUEST['step']=="delXstream" ) $_REQUEST['server_filter'] = "(STREAM)";
  else if ($_REQUEST['step']=="delXtunnel" ) $_REQUEST['server_filter'] = "(TUNNEL-RTMP)";
  else if ($_REQUEST['step']=="delXrtmp" ) $_REQUEST['server_filter'] = "(TUNNEL-RTMP)"; 
  else if ($_REQUEST['step']=="update_recording_list" ) $_REQUEST['server_filter'] = "(RECORDING)";
}

if($_REQUEST["btnAction2"]=="DebugMove"){
     $msg_err = "";
     if (!isset($MACArray)) $msg_err = "<font color=red>MacArray not exist!</font><br>\n";
     foreach($MACArray as $mac)
     {
        $result = moveLicToServer(STREAM_SRV, $mac, $_REQUEST["moveUID"]);
        if ($result){
            $msg_err .= "<font color=blue>".$_REQUEST["Movemac"]." move to stream server ".$_REQUEST["moveUID"]. " SUCCESS!</font><br>\n";
        }else $msg_err .= "<font color=red>".$_REQUEST["Movemac"]." move to stream server ".$_REQUEST["moveUID"]. " FAIL!</font><br>\n";   
     }
          
}else if($_REQUEST["step"]=="delXstream")
{
//echo $_POST['btnAction2']; 
    if (($_POST['btnAction']=='Remove') and ($_REQUEST["id"] != "") )
    {
        $result = deleteCamByID(STREAM_SRV,$_REQUEST["id"]);
        if ($result){
            $msg_err = "<font color=blue>delete Stream server ".$_REQUEST["id"]. " SUCCESS!</font><br>\n";
        }else $msg_err = "<font color=red>delete Stream server ".$_REQUEST["id"]. " FAIL!</font><br>\n";
    }else if (($_POST['btnAction2']=='Delete Stream device ID between') and ($_REQUEST["idstart"] != "") and ($_REQUEST["idend"] != ""))
    {
       $result = deleteCamByBetweenID(STREAM_SRV,$_REQUEST["idstart"],$_REQUEST["idend"]);
        if ($result){
            $msg_err = "<font color=blue>delete Stream server > ".$_REQUEST["idstart"]. " SUCCESS!</font><br>\n";
        }else $msg_err = "<font color=red>delete Stream server > ".$_REQUEST["idstart"]. " FAIL!</font><br>\n";
    }else if ( ($_POST['btnAction2']=='Delete Stream device ID like')  ){
    //and  validteMAC($_REQUEST["mac"],"TEST")
        $result = deleteCamByMAC(STREAM_SRV,$_REQUEST["mac"]);
        if ($result){
            $msg_err = "<font color=blue>delete Stream server MAC like ".$_REQUEST["mac"]. " SUCCESS!</font><br>\n";
        }else $msg_err = "<font color=red>delete Stream server MAC like ".$_REQUEST["mac"]. " FAIL!</font><br>\n";
    }else if ($_POST['btnAction2']=='MACSwitchTo')
    { 
        if ( ($_REQUEST["MfromUID"] != $_REQUEST["MtoUID"]) and  validateMAC($_REQUEST["maclimit"],"CAMERA") ){
            $result = updateHAserverByMAC(STREAM_SRV,$_REQUEST["MfromUID"], $_REQUEST["MtoUID"],$_REQUEST["maclimit"]);            
          if ($result){
              $msg_err = "<font color=blue>update {$_REQUEST['maclimit']} to Stream ".$_REQUEST["MtoUID"]. " SUCCESS!</font><br>\n";
          }else $msg_err = "<font color=red>update {$_REQUEST['maclimit']} to Stream ".$_REQUEST["MtoUID"]. " FAIL!</font><br>\n";
        }else $msg_err = "<font color=red>MACSwitchTo FAIL due to invalid parameter input!</font><br>\n";
    }else if ($_POST['btnAction2']=='SwitchTo')
    {
        if ($_REQUEST["fromUID"] != $_REQUEST["toUID"]){
          if ($_REQUEST["limit"]!="")
            $result = updateHAserverLimit(STREAM_SRV,$_REQUEST["fromUID"], $_REQUEST["toUID"],$_REQUEST["limit"]);
          else $result = updateHAserver(STREAM_SRV,$_REQUEST["fromUID"], $_REQUEST["toUID"]);             
          if ($result){
              $msg_err = "<font color=blue>update Stream HA to ".$_REQUEST["toUID"]. " SUCCESS!</font><br>\n";
          }else $msg_err = "<font color=red>update Stream HA to ".$_REQUEST["toUID"]. " FAIL!</font><br>\n";
        }
    }else if ($_POST['btnAction2']=='MoveTestTo')
    {
        if (strpos($_REQUEST["MoveTestmac"],"CC-")!==FALSE){
          $result = moveTestLicToServer(STREAM_SRV, $_REQUEST["MoveTestmac"], $_REQUEST["moveTestUID"]);             
          if ($result){
              $msg_err = "<font color=blue>".$_REQUEST["MoveTestmac"]." move to stream server ".$_REQUEST["moveTestUID"]. " SUCCESS!</font><br>\n";
          }else $msg_err = "<font color=red>".$_REQUEST["MoveTestmac"]." move to stream server ".$_REQUEST["moveTestUID"]. " FAIL!</font><br>\n";
        }else $msg_err = "<font color=red>Required Camera MAC UID like XNNCC- or {$oem}CC-</font><br>\n";         

    }else if ($_POST['btnAction2']=='MoveTo')
    {
        if (validateMAC($_REQUEST["Movemac"],"UID")){
          $result = moveLicToServer(STREAM_SRV, $_REQUEST["Movemac"], $_REQUEST["moveUID"]);             
          if ($result){
              $msg_err = "<font color=blue>".$_REQUEST["Movemac"]." move to stream server ".$_REQUEST["moveUID"]. " SUCCESS!</font><br>\n";
          }else $msg_err = "<font color=red>".$_REQUEST["Movemac"]." move to stream server ".$_REQUEST["moveUID"]. " FAIL!</font><br>\n";
        }else $msg_err = "<font color=red>Required valid Camera MAC UID</font><br>\n";         
        
    }else if ($_POST['btnAction2']=='AddTo')
    {
        if (validateMAC($_REQUEST["Addmac"],"UID")){
          $result = addLicToServer(STREAM_SRV, $_REQUEST["Addmac"], $_REQUEST["addUID"]);             
          if ($result){
              $msg_err = "<font color=blue>".$_REQUEST["Addmac"]." add to stream server ".$_REQUEST["addUID"]. " SUCCESS!</font><br>\n";
          }else $msg_err = "<font color=red>".$_REQUEST["Addmac"]." add to stream server ".$_REQUEST["addUID"]. " FAIL!</font><br>\n";
        }else $msg_err = "<font color=red>Required valid Camera MAC UID</font><br>\n";         
        
    }else if ($_POST['btnAction2']=='dataplanAR')
    {
          $result = setDatabaseStr("stream_server_assignment","dataplan","AR");
          if ($result){
              $msg_err = "<font color=blue>update DB dataplan SUCCESS!</font><br>\n";
          }else $msg_err = "<font color=red>update DB dataplan FAIL!</font><br>\n";      
    }else if ($_POST['btnAction2']=='VI-AR7')
    {
          $result = setDatabaseStrUID("stream_server_assignment","dataplan","AR","CC-VI");
          $result2 = setDatabaseStrUID("stream_server_assignment","recycle","7","CC-VI");
          if ($result and $result2){
              $msg_err = "<font color=blue>update DB AR+7 for VirtualMAC SUCCESS!</font><br>\n";
          }else $msg_err = "<font color=red>update DB AR+7 for VirtualMAC FAIL!</font><br>\n";

    }else if ($_POST['btnAction2']=='recycle')
    {
          if (intval($_REQUEST["recycleDay"]) > 0){
          $result = setDatabaseStr("stream_server_assignment","recycle",intval($_REQUEST["recycleDay"]));
          if ($result){
              $msg_err = "<font color=blue>update DB recycle SUCCESS!</font><br>\n";
          }else $msg_err = "<font color=red>update DB recycle FAIL!</font><br>\n";
          }else $msg_err = "<font color=red>No recycleDay!</font><br>\n";
    }else if ($_POST['btnAction2']=='recycle180')
    {
          $result = setDatabaseStr("stream_server_assignment","recycle",180);
          if ($result){
              $msg_err = "<font color=blue>update DB recycle SUCCESS!</font><br>\n";
          }else $msg_err = "<font color=red>update DB recycle FAIL!</font><br>\n"; 
    }else if ($_POST['btnAction2']=='recycle60')
    {
          $result = setDatabaseStr("stream_server_assignment","recycle",60);
          if ($result){
              $msg_err = "<font color=blue>update DB recycle SUCCESS!</font><br>\n";
          }else $msg_err = "<font color=red>update DB recycle FAIL!</font><br>\n";      
    }else if ($_POST['btnAction2']=='purpose')
    {
          $result = setDatabaseStrUID("stream_server_assignment","purpose",$_POST['purposeValue'],$_POST['purposeUID']);
          if ($result){
              $msg_err = "<font color=blue>update DB {$_POST['purposeUID']} purpose={$_POST['purposeValue']} SUCCESS!</font><br>\n";
          }else $msg_err = "<font color=red>update DB {$_POST['purposeUID']} purpose={$_POST['purposeValue']} FAIL!</font><br>\n";      
/*    }else if ($_POST['btnAction2']=='RVME Z01')
    {
          $result = setDatabaseStrUID("stream_server_assignment","purpose","RVME","Z01CC");
          if ($result){
              $msg_err = "<font color=blue>update DB purpose SUCCESS!</font><br>\n";
          }else $msg_err = "<font color=red>update DB purpose FAIL!</font><br>\n";      
    }else if ($_POST['btnAction2']=='RVHI Z01')
    {
          $result = setDatabaseStrUID("stream_server_assignment","purpose","RVHI","Z01CC");
          if ($result){
              $msg_err = "<font color=blue>update DB purpose SUCCESS!</font><br>\n";
          }else $msg_err = "<font color=red>update DB purpose FAIL!</font><br>\n";      
    }else if ($_POST['btnAction2']=='RVHI M04')
    {
          $result = setDatabaseStrUID("stream_server_assignment","purpose","RVHI","M04CC");
          if ($result){
              $msg_err = "<font color=blue>update DB purpose SUCCESS!</font><br>\n";
          }else $msg_err = "<font color=red>update DB purpose FAIL!</font><br>\n";
*/      
    } 
//----------------------------------------------------------------------------
}else if($_REQUEST["step"]=="delXtunnel")
{
//echo $_POST['btnAction1'];
    if (($_POST['btnAction']=='Remove') and ($_REQUEST["id"] != ""))
    {
        $result = deleteCamByID(TUNNEL_SRV,$_REQUEST["id"]);
        if ($result){
            $msg_err = "<font color=blue>delete Tunnel server ".$_REQUEST["id"]. " SUCCESS!</font><br>\n";
        }else $msg_err = "<font color=red>delete Tunnel server ".$_REQUEST["id"]. " FAIL!</font><br>\n";
    }else if (($_POST['btnAction2']=='Delete Tunnel device ID between') and ($_REQUEST["idstart"] != "") and ($_REQUEST["idend"] != ""))
    {
          $result = deleteCamByBetweenID(TUNNEL_SRV,$_REQUEST["idstart"],$_REQUEST["idend"]);
          if ($result){
              $msg_err = "<font color=blue>delete Tunnel server > ".$_REQUEST["idstart"]. " SUCCESS!</font><br>\n";
          }else $msg_err = "<font color=red>delete Tunnel server > ".$_REQUEST["idstart"]. " FAIL!</font><br>\n";
    }else if (($_POST['btnAction2']=='Delete Tunnel device ID like') and  validateMAC($_REQUEST["mac"],"TEST") )
    {
          $result = deleteCamByMAC(TUNNEL_SRV,$_REQUEST["mac"]);
          if ($result){
              $msg_err = "<font color=blue>delete Tunnel server MAC like ".$_REQUEST["mac"]. " SUCCESS!</font><br>\n";
          }else $msg_err = "<font color=red>delete Tunnel server MAC like ".$_REQUEST["mac"]. " FAIL!</font><br>\n";
    }else if ($_POST['btnAction2']=='MoveTo')
    {
        if (validateMAC($_REQUEST["Movemac"],"UID")){
          $result = moveLicToServer(TUNNEL_SRV, $_REQUEST["Movemac"], $_REQUEST["moveUID"]);             
          if ($result){
              $msg_err = "<font color=blue>".$_REQUEST["Movemac"]." move to server ".$_REQUEST["moveUID"]. " SUCCESS!</font><br>\n";
          }else $msg_err = "<font color=red>".$_REQUEST["Movemac"]." move to server ".$_REQUEST["moveUID"]. " FAIL!</font><br>\n";
        }else $msg_err = "<font color=red>Required valid Camera MAC UID</font><br>\n";         
    }else if ($_POST['btnAction2']=='AddTo')
    {
        if (validateMAC($_REQUEST["Addmac"],"UID")){
          $result = addLicToServer(TUNNEL_SRV, $_REQUEST["Addmac"], $_REQUEST["addUID"]);             
          if ($result){
              $msg_err = "<font color=blue>".$_REQUEST["Addmac"]." add to server ".$_REQUEST["addUID"]. " SUCCESS!</font><br>\n";
          }else $msg_err = "<font color=red>".$_REQUEST["Addmac"]." add to server ".$_REQUEST["addUID"]. " FAIL!</font><br>\n";
        }else $msg_err = "<font color=red>Required valid Camera MAC UID</font><br>\n";         

    }else if ($_POST['btnAction2']=='SwitchTo')
    {
          if ($_REQUEST["fromUID"] != $_REQUEST["toUID"]){
          if ($_REQUEST["limit"]!="")
            $result = updateHAserverLimit(TUNNEL_SRV,$_REQUEST["fromUID"], $_REQUEST["toUID"],$_REQUEST["limit"]);
          else $result = updateHAserver(TUNNEL_SRV,$_REQUEST["fromUID"], $_REQUEST["toUID"]); 
            if ($result){
                $msg_err = "<font color=blue>update Tunnel HA to ".$_REQUEST["toUID"]. " SUCCESS!</font><br>\n";
            }else $msg_err = "<font color=red>update Tunnel HA to ".$_REQUEST["toUID"]. " FAIL!</font><br>\n";
          }
    }
//----------------------------------------------------------------------------     
}else if($_REQUEST["step"]=="delXrtmp")
{
    if (($_POST['btnAction']=='Remove') and ($_REQUEST["id"] != ""))
    {
        $result = deleteCamByID(RTMP_SRV,$_REQUEST["id"]);
        if ($result){
            $msg_err = "<font color=blue>delete Rtmp server ".$_REQUEST["id"]. " SUCCESS!</font><br>\n";
        }else $msg_err = "<font color=red>delete Rtmp server ".$_REQUEST["id"]. " FAIL!</font><br>\n";
    }else if (($_POST['btnAction3']=='Delete RTMP device ID between') and ($_REQUEST["idstart"] != "") and ($_REQUEST["idend"] != ""))
    {
          $result = deleteCamByBetweenID(RTMP_SRV,$_REQUEST["idstart"],$_REQUEST["idend"]);
          if ($result){
              $msg_err = "<font color=blue>delete Rtmp server > ".$_REQUEST["idstart"]. " SUCCESS!</font><br>\n";
          }else $msg_err = "<font color=red>delete Rtmp server > ".$_REQUEST["idstart"]. " FAIL!</font><br>\n";
    }else if (($_POST['btnAction3']=='Delete RTMP device ID like') and  ($_REQUEST["mac"] != "MC-M"))
    {
          $result = deleteCamByMAC(RTMP_SRV,$_REQUEST["mac"]);
          if ($result){
              $msg_err = "<font color=blue>delete Rtmp server MAC like ".$_REQUEST["mac"]. " SUCCESS!</font><br>\n";
          }else $msg_err = "<font color=red>delete Rtmp server MAC like ".$_REQUEST["mac"]. " FAIL!</font><br>\n";
    }else if ($_POST['btnAction3']=='MoveTo')
    {
        if (validateMAC($_REQUEST["Movemac"],"UID")){
          $result = moveLicToServer(RTMP_SRV, $_REQUEST["Movemac"], $_REQUEST["moveUID"]);             
          if ($result){
              $msg_err = "<font color=blue>".$_REQUEST["Movemac"]." move to server ".$_REQUEST["moveUID"]. " SUCCESS!</font><br>\n";
          }else $msg_err = "<font color=red>".$_REQUEST["Movemac"]." move to server ".$_REQUEST["moveUID"]. " FAIL!</font><br>\n";
        }else $msg_err = "<font color=red>Required valid Camera MAC UID</font><br>\n";         
    }else if ($_POST['btnAction3']=='AddTo')
    {
        if (validateMAC($_REQUEST["Addmac"],"UID")){
          $result = addLicToServer(RTMP_SRV, $_REQUEST["Addmac"], $_REQUEST["addUID"]);             
          if ($result){
              $msg_err = "<font color=blue>".$_REQUEST["Addmac"]." add to server ".$_REQUEST["addUID"]. " SUCCESS!</font><br>\n";
          }else $msg_err = "<font color=red>".$_REQUEST["Addmac"]." add to server ".$_REQUEST["addUID"]. " FAIL!</font><br>\n";
        }else $msg_err = "<font color=red>Required valid Camera MAC UID</font><br>\n";         

    }else if ($_POST['btnAction3']=='SwitchTo')
    {
          if ($_REQUEST["fromUID"] != $_REQUEST["toUID"]){
          if ($_REQUEST["limit"]!="")
            $result = updateHAserverLimit(RTMP_SRV,$_REQUEST["fromUID"], $_REQUEST["toUID"],$_REQUEST["limit"]);
          else $result = updateHAserver(RTMP_SRV,$_REQUEST["fromUID"], $_REQUEST["toUID"]); 
            if ($result){
                $msg_err = "<font color=blue>update Rtmp HA to ".$_REQUEST["toUID"]. " SUCCESS!</font><br>\n";
            }else $msg_err = "<font color=red>update Rtmp HA to ".$_REQUEST["toUID"]. " FAIL!</font><br>\n";
          }
    } 
//----------------------------------------------------------------------------
}else if($_REQUEST["step"]=="update_recording_list")
{
    if ($_POST['btnAction4']=='SwitchTo')
    {
      if ($_REQUEST["fromUID"] != $_REQUEST["toUID"]){
          $result = updateHAserver(RECORDING_LIST,$_REQUEST["fromUID"], $_REQUEST["toUID"]);             
          if ($result){
              $msg_err = "<font color=blue>update recording_list to ".$_REQUEST["toUID"]. " SUCCESS!</font><br>\n";
          }else $msg_err = "<font color=red>update recording_list to ".$_REQUEST["toUID"]. " FAIL!</font><br>\n";
        }
    }elseif (($_POST['btnAction']=='Remove') )
    {
      if (($_REQUEST["device_uid"] != "")){
        $result = cleanupRecording($_REQUEST["device_uid"]);
        if ($result){
            $msg_err = "<font color=blue>cleanup recording on ".$_REQUEST["device_uid"]. " SUCCESS!</font><br>\n";
        }else $msg_err = "<font color=red>cleanup recording on ".$_REQUEST["device_uid"]. " FAIL!</font><br>\n";
      }else{
        cleanupRecording($_REQUEST["device_uid"]);
        $msg_err = "<font color=blue>cleanup empty/NULL UID recording on SUCCESS!</font><br>\n";
      }
    }

}

//----------------------------------------------------------------------------
function validateMAC($mac, $type){
  if ($mac == "") return false;
  if ($type == "UID"){
    if (strlen($mac)!=18) return false;
    if (strpos($mac,"CC-") ===FALSE) //not found
      if (strpos($mac,"MC-M") ===FALSE) return false; //not found
    if (!ereg("[A-Za-z0-9]{12}",substr($mac,6,12))) return false;
  }else if ($type == "MAC"){
    if (strlen($mac)!=12) return false;
    if (!ereg("[A-Za-z0-9]{12}",$mac)) return false;
  }else if ($type == "CAMERA"){ 
    if (strpos($mac,"CC-") ===FALSE) //not found
      if (strpos($mac,"MC-M") ===FALSE) return false; //not found
  }else if ($type == "TEST"){
    if (strpos($mac,"Z01CC-00") ===FALSE) return false;
  }else return false;
  return true;
}
function cleanupRecording($device_uid)
{
  if ($device_uid == "")
    $sql="delete from isat.recording_list where device_uid='' or device_uid is NULL";
  else
    $sql="delete from isat.recording_list where device_uid='$device_uid'";
    sql($sql,$result,$num,0);
    if ($result) return true;
    return false;    
}

function setDatabaseStr ($table, $param, $value)
{
   $sql="update isat.{$table} set {$param} = '{$value}'";
   sql($sql,$result,$num,0);
   if ($result) return true;
    return false;
}
function setDatabaseStrUID ($table, $param, $value, $uidlike)
{
   $sql="update isat.{$table} set {$param} = '{$value}' where device_uid like '%{$uidlike}%'";
   sql($sql,$result,$num,0);
   if ($result) return true;
    return false;
} 
function setDatabaseInt ($table, $param, $value)
{
   $sql="update isat.{$table} set {$param} = {$value}";
   sql($sql,$result,$num,0);
   if ($result) return true;
    return false;
} 

function addLicToServer ($type, $addMAC, $addUID)
{
    if ($type == STREAM_SRV){
      $sql = "INSERT INTO isat.stream_server_assignment (device_uid, stream_server_uid, url_path, purpose,dataplan,recycle) " .
			"VALUES ('{$addMAC}', '{$addUID}', '/{$addMAC}', 'RVME','AR',7)";
    }else if ($type == TUNNEL_SRV){ 
      $lastPort = getTunnelPortSlot($addUID);
      if ($lastPort == -1) return false;
      $sql1 = "INSERT INTO isat.tunnel_server_assignment (device_uid, tunnel_server_uid, url_prefix, bind_port) VALUES" .
			" ('{$addMAC}', '{$addUID}', 'rtsp://', '".$lastPort."'),".
      " ('{$addMAC}', '{$addUID}', 'http://', '".($lastPort++)."'),".
      " ('{$addMAC}', '{$addUID}', 'cmd://', '".($lastPort++)."')";
      sql($sql1,$result,$num,0);

    }else if ($type == RTMP_SRV){
      $sql = "INSERT INTO isat.rtmp_server_assignment (device_uid, tunnel_server_uid) VALUES ('{$addMAC}', '{$addUID}')";
    }
    sql($sql,$result,$num,0);
    //echo $sql;
    if ($result) return true;
    return false;
}

function moveTestLicToServer ($type, $addMAC, $moveUID)
{
    if ($type == STREAM_SRV)
      $sql = "UPDATE isat.stream_server_assignment set stream_server_uid='{$moveUID}' where device_uid like '%{$addMAC}%'";
    else if ($type == TUNNEL_SRV){
      $sql = "UPDATE isat.tunnel_server_assignment set tunnel_server_uid='{$moveUID}' where device_uid like '%{$addMAC}%'";
    }else if ($type == RTMP_SRV){
      $sql = "UPDATE isat.rtmp_server_assignment set tunnel_server_uid='{$moveUID}' where device_uid like '%{$addMAC}%'";
    }
    sql($sql,$result,$num,0);
    //echo $sql;
    if ($result) return true;
    return false;
}

function getAvailTunnelPort($moveUID)
{
  if ($moveUID=="") return -1;
    $sql = "select bind_port from isat.tunnel_server_assignment where tunnel_server_uid='{$moveUID}' order by bind_port desc Limit 1;";
    sql($sql,$result,$num,0);
    if ($num==1) {
        fetch($arr,$result,0,0); //$arr['bind_port']
        $lastPort = intval($arr['bind_port'])+1;
        if ($lastPort <0) return -1;
        if ($lastPort > TUNNEL_MAX_PORT) return -1; //port range limit
        if ($lastPort == TUNNEL_DEFAULT_PORT) return (TUNNEL_DEFAULT_PORT +1);  
        return $lastPort;
    }else if ($num==0)  return (TUNNEL_DEFAULT_PORT +1);
    return -1;
}
/*
function checkTunnelPortOccupied($port, $moveUID)
{
    $sql = "select bind_port from isat.tunnel_server_assignment where tunnel_server_uid='{$moveUID}' and bind_port={$port};";
    sql($sql,$result,$num,0);
    if ($num>0) return true;
    return false;
}*/

function moveLicToServer ($type, $addMAC, $moveUID)
{
    if ($type == STREAM_SRV){
      //check if moveUID is original UID
      $sql = "select stream_server_uid from isat.stream_server_assignment where device_uid='{$addMAC}'";
      sql($sql,$result,$num,0);
      fetch($arr,$result,0,0);
      if ($arr['stream_server_uid']== $moveUID) return true;
      $sql = "UPDATE isat.stream_server_assignment set stream_server_uid='{$moveUID}' where device_uid='{$addMAC}'";
    }else if ($type == TUNNEL_SRV){
      $portCheck = getTunnelPortSlot($moveUID);
      if ($portCheck==-1) return false; 
        $sql = "UPDATE isat.tunnel_server_assignment set tunnel_server_uid='{$moveUID}', bind_port='{$portCheck}' where device_uid='{$addMAC}' and url_prefix like 'rtsp%'";
        sql($sql,$result,$num,0);
        $portCheck++;
        $sql = "UPDATE isat.tunnel_server_assignment set tunnel_server_uid='{$moveUID}', bind_port='{$portCheck}' where device_uid='{$addMAC}' and url_prefix like 'http%'";
        sql($sql,$result,$num,0);
        $portCheck++;
        $sql = "UPDATE isat.tunnel_server_assignment set tunnel_server_uid='{$moveUID}', bind_port='{$portCheck}' where device_uid='{$addMAC}' and url_prefix like 'cmd%'";
        sql($sql,$result,$num,0);
        return true;
    }else if ($type == RTMP_SRV){
        $sql = "select tunnel_server_uid from isat.rtmp_server_assignment where device_uid='{$addMAC}'";
        sql($sql,$result,$num,0);
        fetch($arr,$result,0,0);
        if ($arr['tunnel_server_uid']== $moveUID) return true;
        $sql = "UPDATE isat.rtmp_server_assignment set tunnel_server_uid='{$moveUID}' where device_uid='{$addMAC}'";
    }
    sql($sql,$result,$num,0);
    //echo $sql;
    if ($result) return true;
    return false;
}

function updateHAserver ($type, $fromUID, $toUID)
{
    if ($type == STREAM_SRV){
        $sql="update isat.stream_server_assignment set stream_server_uid='{$toUID}' where stream_server_uid='{$fromUID}' and ( device_uid like '%CC-%' or device_uid like '%MC-M%')" ;
    }else if ($type == TUNNEL_SRV){
        /*$AvailPort= getTunnelPortSlot($toUID);
        if ($Availport==-1) return false;
        $sql="SELECT @i:={$AvailPort};UPDATE isat.tunnel_server_assignment set bind_port= @i:=@i , tunnel_server_uid='{$toUID}' where tunnel_server_uid='{$fromUID}' and device_uid like '%CC-%' order by bind_port";
        //echo $sql;
        */
        $sql = "select count(*) as total from isat.tunnel_server_assignment where tunnel_server_uid='{$fromUID}' and device_uid like '%CC-%'";
        sql($sql,$result,$num,0);
        if ($num==1){
        fetch($arr, $result,0,0);
        updateHAserverLimit($type, $fromUID, $toUID,$arr['total']);
        return true;
        }else return false;
    }else if ($type == RTMP_SRV){
        $sql="update isat.rtmp_server_assignment set tunnel_server_uid='{$toUID}' where tunnel_server_uid='{$fromUID}' and device_uid like '%MC-M%'";
    }else if ($type == RECORDING_LIST){
        $sql="update isat.recording_list set stream_server_uid='{$toUID}' where stream_server_uid='{$fromUID}'";
    }
    sql($sql,$result,$num,0);
    //echo $sql;
    if ($result) return true;
    return false;
}

function getTunnelPortSlot($toUID)
{//get 3 port slot every time
  $port = getAvailTunnelPort($toUID); //last available port
  if ($port == -1){
    $sql="SELECT a.bind_port+1 AS start, MIN(b.bind_port) - 1 AS end FROM isat.tunnel_server_assignment AS a, isat.tunnel_server_assignment AS b WHERE a.tunnel_server_uid='{$toUID}' and a.bind_port < b.bind_port GROUP BY a.bind_port HAVING start < MIN(b.bind_port) order by end desc limit 1";
    sql($sql,$result,$num,0);
    if ($num==1){
      fetch($arr, $result,0,0);
      if (intval($arr['end']) <= TUNNEL_DEFAULT_PORT ){ //fix error assign <50000
        return  (TUNNEL_DEFAULT_PORT+1);
      }else{ //get between slot and make sure has 3 slot
        $sql="SELECT a.bind_port+1 AS start, MIN(b.bind_port) - 1 AS end FROM isat.tunnel_server_assignment AS a, isat.tunnel_server_assignment AS b WHERE a.tunnel_server_uid='{$toUID}' and a.bind_port < b.bind_port GROUP BY a.bind_port HAVING start < MIN(b.bind_port)";
        sql($sql,$result,$num,0);
        $slot_start=0;
        $slot_end=0;

        for ($i=0;$i<$num;$i++){
          fetch($arr, $result,$i,0);
          if ($slot_start ==0){
            $slot_start=intval($arr['end']);
            continue;
          }
          if ($slot_end ==0){
            $slot_end=intval($arr['start']);
            continue;
          }
          if (($slot_end !=0) and ($slot_start !=0))  break;
        }
        //rand($slot_start,$slot_end);
        if ($slot_end >= TUNNEL_MAX_PORT) return -1; //not available port
        if ($slot_end-$slot_start >=2) //slot size=3
          return $slot_start;
      }
    }
  }else  return $port;
}

function updateHAserverByMAC ($type, $fromUID, $toUID, $macstr)
{
    if ($type == STREAM_SRV)
        $sql="update isat.stream_server_assignment set stream_server_uid='{$toUID}' where stream_server_uid='{$fromUID}' and device_uid like '{$macstr}%'";
    else if ($type == TUNNEL_SRV){
        $sql="update isat.tunnel_server_assignment set bind_port={$Availport} ,tunnel_server_uid='{$toUID}' where tunnel_server_uid='{$fromUID}' and device_uid like '{$macstr}%'";
    }else if ($type == RTMP_SRV){//mobile camera <oem>MC-M<serial>
        $sql="update isat.rtmp_server_assignment set tunnel_server_uid='{$toUID}' where tunnel_server_uid='{$fromUID}' and device_uid like '{$macstr}%'";
    }
    sql($sql,$result,$num,0);
    //echo $sql;
    if ($result) return true;
    return false;
}

function updateHAserverLimit ($type, $fromUID, $toUID, $limit="")
{
    if ($type == STREAM_SRV){
      if ($limit == "")
        $sql="update isat.stream_server_assignment set stream_server_uid='{$toUID}' where stream_server_uid='{$fromUID}' and (device_uid like '%CC-%' or device_uid like '%MC-M%')";
      else
        $sql="update isat.stream_server_assignment set stream_server_uid='{$toUID}' where stream_server_uid='{$fromUID}' and (device_uid like '%CC-%' or device_uid like '%MC-M%') LIMIT {$limit}";
      //echo $sql;        
    }else if ($type == TUNNEL_SRV){
      if ($limit == ""){
        $sql="update isat.tunnel_server_assignment set bind_port={$Availport} ,tunnel_server_uid='{$toUID}' where tunnel_server_uid='{$fromUID}' and device_uid like '%CC-%'";
      }else{
      $res = intval($limit) % 3; //make sure come in 3 as one block
      $limit = intval($limit) - $res;
      for ($i=0;$i<$limit;$i++){
        $Availport = getTunnelPortSlot($toUID);
        if ($Availport==-1) return false;
        $sql="update isat.tunnel_server_assignment set bind_port={$Availport} ,tunnel_server_uid='{$toUID}' where tunnel_server_uid='{$fromUID}' and device_uid like '%CC-%' LIMIT 1";
        sql($sql,$result,$num,0);    
      }
      return true;
      }//limit ?
    }else if ($type == RTMP_SRV){//mobile camera <oem>MC-M<serial>
        $sql="update isat.rtmp_server_assignment set tunnel_server_uid='{$toUID}' where tunnel_server_uid='{$fromUID}' and device_uid like '%MC-M%' LIMIT {$limit}";
    }
    sql($sql,$result,$num,0);
    //echo $sql;
    if ($result) return true;
    return false;
}


function deleteCamByBetweenID ($type, $idstart, $idend)
{
    if ($type == STREAM_SRV)
        $sql="delete from isat.stream_server_assignment where id>'{$idstart}' and id <'{$idend}'";
    else if ($type == TUNNEL_SRV)
        $sql="delete from isat.tunnel_server_assignment where id>'{$idstart}' and id <'{$idend}'";
    else if ($type == RTMP_SRV)
        $sql="delete from isat.rtmp_server_assignment where id>'{$idstart}' and id <'{$idend}'";
    sql($sql,$result,$num,0);
    echo $sql;
    if ($result) return true;
    return false;
}

function deleteCamByMAC ($type, $mac)
{
    if ($type == STREAM_SRV)
        $sql="delete from isat.stream_server_assignment where device_uid like '{$mac}%'";
    else if ($type == TUNNEL_SRV)
        $sql="delete from isat.tunnel_server_assignment where device_uid like '{$mac}%'";
    else if ($type == RTMP_SRV)
        $sql="delete from isat.rtmp_server_assignment where device_uid like '{$mac}%'";

    sql($sql,$result,$num,0);
    echo $sql;
    if ($result) return true;
    return false;
}


function deleteCamByID ($type, $id)
{
    if ($type == STREAM_SRV)
        $sql="delete from isat.stream_server_assignment where id='{$id}'";
    else if ($type == TUNNEL_SRV)
        $sql="delete from isat.tunnel_server_assignment where id='{$id}'";
    else if ($type == RTMP_SRV)
        $sql="delete from isat.rtmp_server_assignment where id='{$id}'";
    sql($sql,$result,$num,0);
    if ($result) return true;
    return false;
}

function SelectServerList($type,$id)
{
    if ($type == STREAM_SRV)
        $sql = "select stream_server_uid as uid from isat.stream_server_assignment group by stream_server_uid";
    else if ($type == TUNNEL_SRV)
        $sql = "select tunnel_server_uid as uid from isat.tunnel_server_assignment group by tunnel_server_uid";
    else if ($type == RTMP_SRV)
        $sql = "select tunnel_server_uid as uid from isat.rtmp_server_assignment group by tunnel_server_uid";
    else if ($type == RECORDING_LIST)
        $sql = "select stream_server_uid as uid from isat.recording_list group by stream_server_uid";
    else return;
   sql($sql,$result,$num,0);
   $html="";
    //if ($id=="")  $html = "\n<option value='' selected >--NA--</option>";
    //else $html = "\n<option value=''>--NA--</option>";
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      if ($id==$arr['uid'])
        $html.= "\n<option value='{$arr['uid']}' selected>{$arr['uid']}</option>";
      else
        $html.= "\n<option value='{$arr['uid']}'>{$arr['uid']}</option>";
    }//for
	echo $html;
}

function createServiceTable($type,$whereParam) //$limit
{
    if ($type == STREAM_SRV)
        $sql = "select * from isat.stream_server_assignment {$whereParam} ";
    else if ($type == TUNNEL_SRV)
        $sql = "select * from isat.tunnel_server_assignment {$whereParam} ";
    else if ($type == RTMP_SRV)
        $sql = "select * from isat.rtmp_server_assignment {$whereParam} ";
    else if ($type == RECORDING_LIST)
        $sql = "select device_uid, count(*) as count from isat.recording_list  {$whereParam}  group by device_uid";
    /*if ($limit!="")
      $sql .=  " order by id desc limit {$limit}";
  */
    sql($sql,$result,$num,0);
    $services = array();
    $index = 0;
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      if ($type == STREAM_SRV){
          $arr['server_uid'] = $arr['stream_server_uid'];
          $arr['note'] = $arr['purpose'] . " / ". $arr['dataplan'] . " / ". $arr['recycle'];
      }else if ($type == TUNNEL_SRV){
          $arr['server_uid'] = $arr['tunnel_server_uid'];
          $arr['note'] = $arr['url_prefix'] . " / " . $arr['bind_port'];
      }else if ($type == RTMP_SRV){
          $arr['server_uid'] = $arr['tunnel_server_uid'];
      }else if ($type == RECORDING_LIST){
          $arr['note'] = $arr['count'];
      }
      $services[$index] = $arr;
    	$index++;
    }//for
  $html = "Total: {$num}";
  $html .= "<table id='tbl1' class=table_main><tr class=topic_main><td> ID</td><td>Camera UID</td>";
  if ($type == STREAM_SRV)
    $html .= '<td>STREAM UID</td><td>note</td><td></td></tr>';
  else if ($type == TUNNEL_SRV)
    $html .= '<td>TUNNEL UID</td><td>note</td><td></td></tr>';
  else if ($type == RTMP_SRV)
    $html .= '<td>RTMP UID</td><td>note</td><td></td></tr>';    
  else if ($type == RECORDING_LIST)
    $html .= '<td></td><td>count</td><td></td></tr>';    
  foreach($services as $service)
  {
		$html.= "\n<tr class=tr_2>\n";
    if ($type != RECORDING_LIST)
        $html.= "<td>{$service['id']}</td>\n";
    else $html.= "<td></td>\n";            
    $html.= "<td>{$service['device_uid']}</td>\n";
    if ($type != RECORDING_LIST)
      $html.= "<td>{$service['server_uid']}</td>\n";
    else $html.= "<td></td>\n";
    $html.= "<td>{$service['note']}</td>\n";
    if (isset($_REQUEST["debugadmin"])){
    $html.= "<td><form action=\"".htmlentities($_SERVER['PHP_SELF'])."\" method=POST>\n";
    $html.= "<input type=hidden name=debugadmin value='1'>";
    $html.= "<input type=submit name='btnAction' value=\"Remove\">\n";
    if ($type == STREAM_SRV){
        $html.= "<input type=hidden name='step' value=\"delXstream\" >\n";
        $html.= "<input type=hidden name='id' value=\"{$service['id']}\" >\n";
    }else if ($type == TUNNEL_SRV){
        $html.= "<input type=hidden name='step' value=\"delXtunnel\" >\n";
        $html.= "<input type=hidden name='id' value=\"{$service['id']}\" >\n";
    }else if ($type == RTMP_SRV){
        $html.= "<input type=hidden name='step' value=\"delXrtmp\" >\n";
        $html.= "<input type=hidden name='id' value=\"{$service['id']}\" >\n";
    }else if ($type == RECORDING_LIST){
        $html.= "<input type=hidden name='step' value=\"update_recording_list\" >\n";
        $html.= "<input type=hidden name='device_uid' value=\"{$service['device_uid']}\" >\n";
    }
    $html.= "</form></td>\n";
    }else $html.= "<td></td>\n";
    $html.= "</tr>\n";
	}
  $html.= "</table>\n";
	echo $html;
}

function createCountTable($type)
{
global $bNewServer;
    if ($type == STREAM_SRV)
        //$sql = "select stream_server_uid, count(*) as count from isat.stream_server_assignment group by stream_server_uid";
        $sql = "select c2.hostname, c1.stream_server_uid, count(*) as count from isat.stream_server_assignment as c1 left join isat.stream_server as c2 on c1.stream_server_uid=c2.uid group by stream_server_uid";
    else if ($type == TUNNEL_SRV)
        if ($bNewServer)
        $sql = "select c2.hostname, c1.tunnel_server_uid, count(*) as count from isat.tunnel_server_assignment as c1 left join isat.tunnel_server as c2 on c1.tunnel_server_uid=c2.uid and c2.purpose='TUNNEL' group by tunnel_server_uid";
        else
        $sql = "select c2.hostname, c1.tunnel_server_uid, count(*) as count from isat.tunnel_server_assignment as c1 left join isat.tunnel_server as c2 on c1.tunnel_server_uid=c2.uid group by tunnel_server_uid";

    else if ($type == RTMP_SRV)
        $sql = "select c2.hostname, c1.tunnel_server_uid, count(*) as count from isat.rtmp_server_assignment as c1 left join isat.tunnel_server as c2 on c1.tunnel_server_uid=c2.uid and c2.purpose='RTMPD' group by tunnel_server_uid";
    elseif ($type == RECORDING_LIST)
        $sql = "select c2.hostname, c1.stream_server_uid, count(*) as count from isat.recording_list as c1 left join isat.stream_server as c2 on c1.stream_server_uid=c2.uid group by stream_server_uid";

    sql($sql,$result,$num,0);
    $services = array();
    $index = 0;
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      if (($type == STREAM_SRV) or ($type == RECORDING_LIST)) {
          $arr['server_uid'] = $arr['stream_server_uid'];
      }else if (($type == TUNNEL_SRV) or ($type == RTMP_SRV)){
          $arr['server_uid'] = $arr['tunnel_server_uid'];
      }
      $services[$index] = $arr;
    	$index++;
    }//for
    $html = "Total: {$num}";
    if ($type == STREAM_SRV)
        $html = "\n<table id='tbls' class=table_main><tr class=topic_main><td>Hostname</td><td>Stream UID</td><td>#</td></tr>"; //add table header
    else if ($type == TUNNEL_SRV)
        $html = "\n<table id='tblt' class=table_main><tr class=topic_main><td>Hostname</td><td>Tunnel UID</td><td>#</td></tr>"; //add table header
    else if ($type == RTMP_SRV)
        $html = "\n<table id='tblr' class=table_main><tr class=topic_main><td>Hostname</td><td>RTMP UID</td><td>#</td></tr>"; //add table header
    else if ($type == RECORDING_LIST)
        $html = "\n<table id='tbls' class=table_main><tr class=topic_main><td>Hostname</td><td>recording_list Stream UID</td><td>#</td></tr>"; //add table header
  foreach($services as $service)
  {
		$html.= "\n<tr class=tr_2>\n";
    $html.= "<td>{$service['hostname']}</td>\n";
    $html.= "<td>{$service['server_uid']}</td>\n";
    $html.= "<td>{$service['count']}</td>\n";
    $html.= "</tr>\n";
	}
  $html .= "</table>\n";   //add table end
	echo $html;
}
function selectUidAssign($type, $tagName)
{
    if ($type == STREAM_SRV)
         $sql = "select c2.hostname, c1.stream_server_uid from isat.stream_server_assignment as c1 left join isat.stream_server as c2 on c1.stream_server_uid=c2.uid group by stream_server_uid order by c2.id";
    else if ($type == TUNNEL_SRV)
        $sql = "select c2.hostname, c1.tunnel_server_uid from isat.tunnel_server_assignment as c1 left join isat.tunnel_server as c2 on c1.tunnel_server_uid=c2.uid group by tunnel_server_uid order by c2.id";
    else if ($type == RTMP_SRV)
        $sql = "select c2.hostname, c1.tunnel_server_uid from isat.rtmp_server_assignment as c1 left join isat.tunnel_server as c2 on c1.tunnel_server_uid=c2.uid group by tunnel_server_uid order by c2.id";
   else if ($type == RECORDING_LIST)
         $sql = "select c2.hostname, c1.stream_server_uid from isat.recording_list as c1 left join isat.stream_server as c2 on c1.stream_server_uid=c2.uid group by stream_server_uid order by c2.id";         

    sql($sql,$result,$num,0);
    $html = "<select name='{$tagName}'>";
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      if (($type == STREAM_SRV) or ($type == RECORDING_LIST))
          $html.= "\n<option value='{$arr['stream_server_uid']}'>{$arr['hostname']} {$arr['stream_server_uid']}</option>";
      else if (($type == TUNNEL_SRV) or ($type == RTMP_SRV))      
          $html.= "\n<option value='{$arr['tunnel_server_uid']}'>{$arr['hostname']} {$arr['tunnel_server_uid']}</option>";
    }//for
  $html .= "</select>\n";   //add table end
	echo $html;
}

function selectUid($type, $tagName)
{
  global $bNewServer;
  if ((!$bNewServer) and ($type == RTMP_SRV))
    return;
    if ($type == STREAM_SRV)
        $sql = "select hostname, uid from isat.stream_server";
    else if ($type == TUNNEL_SRV)
      if ($bNewServer)        
        $sql = "select hostname, uid from isat.tunnel_server where purpose='TUNNEL'";
      else
        $sql = "select hostname, uid from isat.tunnel_server";
    else if ($type == RTMP_SRV)
        $sql = "select hostname, uid from isat.tunnel_server where purpose='RTMPD'";
    sql($sql,$result,$num,0);
    $html = "<select name='{$tagName}'>";
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      $html.= "\n<option value='{$arr['uid']}'>{$arr['hostname']} {$arr['uid']}</option>";
    }//for
  $html .= "</select>\n";   //add table end
	echo $html;
}
?>
<!--html>
<head>
</head>
<body-->
<script>
function optionValue(thisformobj, selectobj)
{
	var chosenoption=selectobj.options[selectobj.selectedIndex];
  thisformobj.value = chosenoption.value;
}
</script>
<div align=center><b><font size=5>Maintain Camera assignment DB</font></b></div>
<div id="container">
<form method=post action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>">
<select name="server_filter" id="server_filter" onchange="optionValue(this.form.server_filter, this);this.form.submit();">
<option value="" ></option>
<option value="(STREAM)" <?php if($_REQUEST['server_filter'] =="(STREAM)" ) echo "selected";?>>(STREAM)</option>
<option value="(TUNNEL-RTMP)" <?php if($_REQUEST['server_filter'] =="(TUNNEL-RTMP)" ) echo "selected";?>>(TUNNEL-RTMP)</option>
<option value="(RECORDING)" <?php if($_REQUEST['server_filter'] =="(RECORDING)" ) echo "selected";?>>(RECORDING)</option>
<option value="(ALL)" <?php if($_REQUEST['server_filter'] =="(ALL)" ) echo "selected";?>>(ALL)</option>
</select>
<?php if (isset($_REQUEST["debugadmin"])){ ?>
<input type=hidden name=debugadmin value='1'>
<?php } 
if (isset($_REQUEST["debugadv"])){ ?>
<input type=hidden name=debugadv value='1'>
<?php } ?>
</form>
<?php
if (isset($MACArray) and isset($_REQUEST["debugadv"]) )
{
?>
<form method=post action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>">
<textarea><?php
      foreach($MACArray as $mac)
      { echo $mac."\n";}
?></textarea>
<input type=submit name=btnAction2 value="DebugMove">
<?php selectUid(STREAM_SRV,"moveUID");?>
</form>
<!-------------------------------stream---------------------------------------->
<?php
} //isset($MACArray)
echo $msg_err."<hr>";
if(($_REQUEST['server_filter'] =="(STREAM)") or ($_REQUEST['server_filter'] =="(ALL)")  ){
createCountTable(STREAM_SRV);
?>
<form method=post action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>">
<input type=hidden name=step value='delXstream'>
<?php if (isset($_REQUEST["debugadmin"])){ ?>
<input type=hidden name=debugadmin value='1'>
<?php } ?>
<select name=purposeValue  <?php if (!isset($_REQUEST["debugadmin"])) echo "disabled='disabled'";?>>
<option value=''></option>
<?php
  foreach ($CamPROFILE as $key){
    echo "<option value='{$key}'>{$key}</option>";
  }
?>
</select>
<select name=purposeUID <?php if (!isset($_REQUEST["debugadmin"])) echo "disabled='disabled'";?>>
<option value=''></option>
<?php
  foreach ($CamCID as $key=>$data){
    echo "<option value='{$key}'>{$key}</option>";
  }
?>
</select>
<input type=submit name=btnAction2 value="purpose" <?php if (!isset($_REQUEST["debugadmin"])) echo "disabled";?>>
<br>
<!--input type=submit name=btnAction2 value="RVME Z01" <?php if (!isset($_REQUEST["debugadmin"])) echo "disabled";?> -->
<input type=submit name=btnAction2 value="dataplanAR" >
<input type=submit name=btnAction2 value="VI-AR7" >
<?php
if (isset($_REQUEST["debugadv"])){
?>
<input type=hidden name=debugadv value='1'>
<input type=submit name=btnAction2 value="recycle180" <?php if (!isset($_REQUEST["debugadmin"])) echo "disabled";?>>
<input type=submit name=btnAction2 value="recycle60" <?php if (!isset($_REQUEST["debugadmin"])) echo "disabled";?>>
<?php
}//debugadv
?>
<input type=submit name=btnAction2 value="recycle" <?php if (!isset($_REQUEST["debugadmin"])) echo "disabled";?>>
<input type=text name=recycleDay value="7" size="1" <?php if (!isset($_REQUEST["debugadmin"])) echo "disabled";?>>Days
<br>
<input type=text  size=20 name=Movemac value='<?php if ($_REQUEST["Movemac"]!="") echo $_REQUEST["Movemac"]; else echo "Z01CC-001BFE054Dxx";?>'>
<input type=submit name=btnAction2 value="MoveTo"  <?php if (!isset($_REQUEST["debugadmin"])) echo "disabled";?>>
<?php selectUid(STREAM_SRV,"moveUID");?>
<br>
<?php
if (isset($_REQUEST["debugadmin"])){
?>
<input type=hidden name=debugadmin value='1'>
<?php selectUidAssign(STREAM_SRV,"fromUID");?>
<input type=submit name=btnAction2 value="SwitchTo" <?php if (!isset($_REQUEST["debugadmin"])) echo "disabled";?>>
<input type=text name=limit size=3 value="1" <?php if (!isset($_REQUEST["debugadmin"])) echo "disabled";?>>
<?php selectUid(STREAM_SRV,"toUID");?>
<br>
<font color=red><small>move partial matched MAC:</small></font><br>
<?php selectUidAssign(STREAM_SRV,"MfromUID");?>
<input type=text name=maclimit size=15 value="<?php echo "{$oem}MC-M{$oem}??";?>" <?php if (!isset($_REQUEST["debugadmin"])) echo "disabled";?>>
<input type=submit name=btnAction2 value="MACSwitchTo" <?php if (!isset($_REQUEST["debugadmin"])) echo "disabled";?>>
<?php selectUid(STREAM_SRV,"MtoUID");?>
<br>
<?php
}//debugadmin
if (isset($_REQUEST["debugadv"])){
?>
<input type=hidden name=debugadv value='1'>
<input type=text  size=20 name=Addmac value='<?php if ($_REQUEST["Addmac"]!="") echo $_REQUEST["Addmac"]; else echo "Z01CC-001BFE054Dxx";?>'>
<input type=submit name=btnAction2 value="AddTo"  <?php if (!isset($_REQUEST["debugadmin"])) echo "disabled";?>>
<?php selectUid(STREAM_SRV,"addUID");?>
<br>
<?php
}//debugadv
?>
<select name="uid_filter" id="uid_filter" onchange="optionValue(this.form.uid_filter, this);this.form.submit();">
<option value="(FOLD)">(FOLD)</option>
<option value="(ALL)" <?php if($_REQUEST['uid_filter'] =="(ALL)" ) echo "selected";?>>(ALL)</option>
<?php
     SelectServerList(STREAM_SRV,$_REQUEST['uid_filter']);
?>
</select><br> 
<?php

if ($_REQUEST['step']=="delXstream" )
  if(!isset($_REQUEST['uid_filter'] ))
    echo "<br>";
  else if($_REQUEST['uid_filter'] =="(FOLD)" )
    echo "<br>";
  else if($_REQUEST['uid_filter'] =="(ALL)" )
    createServiceTable(STREAM_SRV,"");
  else createServiceTable(STREAM_SRV, " where stream_server_uid='".$_REQUEST['uid_filter']."'");

if (isset($_REQUEST["debugadv"])){
?>
<input type=hidden name=debugadv value='1'>
<input type=text  size=20 name=MoveTestmac value='<?php if ($_REQUEST["MoveTestmac"]!="") echo $_REQUEST["MoveTestmac"]; else echo "Z01CC-000000";?>'>
<input type=submit name=btnAction2 value="MoveTestTo"  <?php if (!isset($_REQUEST["debugadmin"])) echo "disabled";?>>
<?php selectUid(STREAM_SRV,"moveTestUID");?>
<br>
<input type=text  size=5 name=idstart value='<?php if ($_REQUEST["idstart"] != "") echo $_REQUEST["idstart"];?>' <?php if (!isset($_REQUEST["debugadmin"])) echo "disabled";?>>
<input type=submit name=btnAction2 value="Delete Stream device ID between" <?php if (!isset($_REQUEST["debugadmin"])) echo "disabled";?>>
<input type=text  size=5 name=idend value='<?php if($_REQUEST["idend"] != "") echo $_REQUEST["idend"];?>' <?php if (!isset($_REQUEST["debugadmin"])) echo "disabled";?>>
<br>
<input type=text  size=20 name=mac value='Z01CC-00' <?php if (!isset($_REQUEST["debugadmin"])) echo "disabled";?>>
<input type=submit name=btnAction2 value="Delete Stream device ID like" <?php if (!isset($_REQUEST["debugadmin"])) echo "disabled";?>><br>
<?php
}//debugadv
echo "</form>";
}//server_filter
?>

<!-------------------------------tunnel--------------------------------------->
<?php
if(($_REQUEST['server_filter'] =="(TUNNEL-RTMP)") or ($_REQUEST['server_filter'] =="(ALL)") ){
echo "<hr>";
createCountTable(TUNNEL_SRV);
?>
<form method=post action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>">
<input type=hidden name=step value='delXtunnel'>
<?php if (isset($_REQUEST["debugadmin"])){ ?>
<input type=hidden name=debugadmin value='1'>
<?php }?>
<input type=text  size=20 name=Movemac value='<?php if ($_REQUEST["Movemac"]!="") echo $_REQUEST["Movemac"]; else echo "Z01CC-001BFE054Dxx";?>' <?php if (!isset($_REQUEST["debugadmin"])) echo "disabled";?>>
<input type=submit name=btnAction2 value="MoveTo"  <?php if (!isset($_REQUEST["debugadmin"])) echo "disabled";?>>
<?php selectUid(TUNNEL_SRV,"moveUID");?>
<br>
<?php 
if (isset($_REQUEST["debugadmin"])){
selectUidAssign(TUNNEL_SRV,"fromUID");
?>
<input type=hidden name=debugadmin value='1'>
<input type=submit name=btnAction2 value="SwitchTo"  <?php if (!isset($_REQUEST["debugadmin"])) echo "disabled";?>>
<input type=text name=limit size=3 value="30">
<?php selectUid(TUNNEL_SRV,"toUID");?>
<br>
<input type=hidden name=debugadmin value='1'>
<?php
}//debugadmin
if (isset($_REQUEST["debugadv"])){
?>
<input type=text  size=20 name=Addmac value='<?php if ($_REQUEST["Addmac"]!="") echo $_REQUEST["Addmac"]; else echo "Z01CC-001BFE054Dxx";?>' <?php if (!isset($_REQUEST["debugadmin"])) echo "disabled";?>>
<input type=submit name=btnAction2 value="AddTo"  <?php if (!isset($_REQUEST["debugadmin"])) echo "disabled";?>>
<?php selectUid(TUNNEL_SRV,"addUID");?>
<br>
<input type=hidden name=debugadv value='1'>
<?php
}//debugadv
?>
<select name="uid_filter" id="uid_filter" onchange="optionValue(this.form.uid_filter, this);this.form.submit();">
<option value="(FOLD)">(FOLD)</option>
<option value="(ALL)" <?php if($_REQUEST['uid_filter'] =="(ALL)" ) echo "selected";?>>(ALL)</option>
<?php
     SelectServerList(TUNNEL_SRV,$_REQUEST['uid_filter']);
?>
</select><br> 
<?php

if ($_REQUEST['step']=="delXtunnel" )
  if(!isset($_REQUEST['uid_filter'] ))
    echo "<br>";
  else if($_REQUEST['uid_filter'] =="(FOLD)" )
    echo "<br>";
  else if($_REQUEST['uid_filter'] =="(ALL)" )
    createServiceTable(TUNNEL_SRV,"");
  else createServiceTable(TUNNEL_SRV, " where tunnel_server_uid='".$_REQUEST['uid_filter']."'");
if (isset($_REQUEST["debugadv"])){
?>
<input type=text  size=5 name=idstart value='<?php if($_REQUEST["idstart"] != "") echo $_REQUEST["idstart"];?>' <?php if (!isset($_REQUEST["debugadmin"])) echo "disabled";?>>
<input type=submit name=btnAction2 value="Delete Tunnel device ID between"  <?php if (!isset($_REQUEST["debugadmin"])) echo "disabled";?>>
<input type=text  size=5 name=idend value='<?php if($_REQUEST["idend"] != "") echo $_REQUEST["idend"];?>' <?php if (!isset($_REQUEST["debugadmin"])) echo "disabled";?>><br>
<input type=text  size=20 name=mac value='Z01CC-00'>
<input type=submit name=btnAction2 value="Delete Tunnel device ID like"  <?php if (!isset($_REQUEST["debugadmin"])) echo "disabled";?>><br>
<?php
}//debugadv

?>

</form>
<!-------------------------------rtmp--------------------------------------------->
<?php
if ($bNewServer) {
echo "<hr>";
  createCountTable(RTMP_SRV);
?>
<form method=post action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>">
<input type=hidden name=step value='delXrtmp'>
<?php if (isset($_REQUEST["debugadmin"])){ ?>
<input type=hidden name=debugadmin value='1'>
<?php
selectUidAssign(RTMP_SRV,"fromUID");
?>
<input type=submit name=btnAction3 value="SwitchTo" >
<input type=text name=limit size=3 value="3">
<?php selectUid(RTMP_SRV,"toUID");
}//debugadmin
?>
<br>
<input type=text  size=20 name=Movemac value='<?php echo "{$oem}MC-M{$oem}1234567x";?>' <?php if (!isset($_REQUEST["debugadmin"])) echo "disabled";?>>
<input type=submit name=btnAction3 value="MoveTo"  <?php if (!isset($_REQUEST["debugadmin"])) echo "disabled";?>>
<?php selectUid(RTMP_SRV,"moveUID");?>
<br>
<select name="uid_filter" id="uid_filter" onchange="optionValue(this.form.uid_filter, this);this.form.submit();">
<option value="(FOLD)">(FOLD)</option>
<option value="(ALL)" <?php if($_REQUEST['uid_filter'] =="(ALL)" ) echo "selected";?>>(ALL)</option>
<?php
     SelectServerList(RTMP_SRV,$_REQUEST['uid_filter']);
?>
</select><br> 
<?php

if ($_REQUEST['step']=="delXrtmp" )
  if(!isset($_REQUEST['uid_filter'] ))
    echo "<br>";
  else if($_REQUEST['uid_filter'] =="(FOLD)" )
    echo "<br>";
  else if($_REQUEST['uid_filter'] =="(ALL)" )
    createServiceTable(RTMP_SRV,"");
  else createServiceTable(RTMP_SRV, " where tunnel_server_uid='".$_REQUEST['uid_filter']."'"); 

if (isset($_REQUEST["debugadv"])){
?>
<input type=text  size=20 name=mac value='<?php echo $oem;?>MC-M<?php echo $oem;?>12345678' <?php if (!isset($_REQUEST["debugadmin"])) echo "disabled";?>>
<input type=submit name=btnAction3 value="Delete RTMP device ID like"  <?php if (!isset($_REQUEST["debugadmin"])) echo "disabled";?>><br>
<?php
}//debugadv
}//$bNewServer
echo "</form>";
}//server_filter
?>
<!-------------------------------recording_list--------------------------------------------->
<?php
if (($_REQUEST['server_filter'] =="(RECORDING)") or ($_REQUEST['server_filter'] =="(ALL)") ){
echo "<hr>";
createCountTable(RECORDING_LIST);
?>
<form method=post name=recordinglist action="<?php echo htmlentities($_SERVER['PHP_SELF']);?>">
<?php if (isset($_REQUEST["debugadmin"])){ ?>
<input type=hidden name=debugadmin value='1'>
<?php } ?>
<input type=hidden name=step value='update_recording_list'>
<?php selectUidAssign(RECORDING_LIST,"fromUID");?>
<input type=submit name=btnAction4 value="SwitchTo" <?php if (!isset($_REQUEST["debugadmin"])) echo "disabled";?>>
<?php selectUid(STREAM_SRV,"toUID");?>
<br>
<select name="uid_filter" id="uid_filter" onchange="optionValue(this.form.uid_filter, this);this.form.submit();">
<option value="(FOLD)">(FOLD)</option>
<option value="(ALL)" <?php if($_REQUEST['uid_filter'] =="(ALL)" ) echo "selected";?>>(ALL)</option>
<?php
     SelectServerList(RECORDING_LIST,$_REQUEST['uid_filter']);
?>
</select><br> 
<?php
if($_REQUEST['server_filter'] =="(RECORDING)" )
if ($_REQUEST['step']=="update_recording_list" )
  if(!isset($_REQUEST['uid_filter'] ))
    echo "<br>";
  else if($_REQUEST['uid_filter'] =="(FOLD)" )
    echo "<br>";
  else if($_REQUEST['uid_filter'] =="(ALL)" )
    createServiceTable(RECORDING_LIST,"");
  else createServiceTable(RECORDING_LIST, " where stream_server_uid='".$_REQUEST['uid_filter']."'");

echo "</form>";
}//server_filter
?>
	</div>
</body>
</html>