<?php
/****************
 *Validated on Oct-12,2017,
 *  parameter: mac, date 
 *Writer: JinHo, Chang
*****************/

include("/var/www/qlync_admin/doc/config.php");
include("/var/www/qlync_admin/doc/mysql_connect.php"); 
include("/var/www/qlync_admin/doc/sql.php");

function queryVideoList($mac,$date="")
{
  //$sql = "select stream_server_uid,path from isat.recording_list where device_uid like '%{$mac}' and start like '{$date}%'";
  $sql = "select c2.external_address, c2.external_port ,path from isat.recording_list as c1 left join isat.stream_server as c2 on c1.stream_server_uid=c2.uid where device_uid like '%{$mac}' and start like '{$date}%'";
  sql($sql,$result,$num,0);
  $textList="";
  if ($num > 0){
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
        $textList.= "http://".$arr['external_address'].":".$arr['external_port'].$arr['path']."\n"; 
      }
      $textList = rtrim($textList, "\n");
  }
  return ($textList=="")? "-1" :$textList ;
}


  
if($_REQUEST["id"]==$internal_api_id and $_REQUEST["pwd"]==$internal_api_pwd)
{
  if (isset($_REQUEST["mac"]) and (ereg("[A-Za-z0-9]{12}",$_REQUEST["mac"])) )
  {
       if (isset($_REQUEST["date"])){
          if (ereg("[0-9]{8}",$_REQUEST["date"]))
            echo queryVideoList($_REQUEST["mac"],$_REQUEST["date"]);
          else if ($_REQUEST["date"]=="ALL")
            echo queryVideoList($_REQUEST["mac"]);
          else  echo "Fail";
       }else  echo queryVideoList($_REQUEST["mac"],date("Ymd")); //todays 
  }else  echo "Fail";
}else{  //echo 'Please Enter Correct ID/ PWD !';
  echo 'Fail';
}
?>