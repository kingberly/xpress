<?php
/****************
 * Validated on May-23,2017, 
 *  $argv[0] is script name
 *  $argv[1] update_app param: 0 or 1, default 0
 *Writer: JinHo, Chang
*****************/ 
require_once ("/var/www/qlync_admin/doc/config.php");
include("/var/www/qlync_admin/doc/mysql_connect.php");
include("/var/www/qlync_admin/doc/sql.php");
header("Content-Type:text/html; charset=utf-8"); 
//oem,url is load from config.php
//$mysql_ip="192.168.2.138";
//$mysql_id="isatRoot";
//$mysql_pwd="isatPassword";
//$home_path="/var/www/qlync_admin";
//$home_url="http://153.150.122.96:8080";
//$oem="J01";
if (isset($argv[1]))
	define ("UPDATE_APP", $argv[1]);
else define ("UPDATE_APP", 0); 
$update_app=UPDATE_APP;
//(6) 0:title, 1:copyright,2:notification_email,3:support_email,
// 4:$tel_android ,5:$tel_ios, 6: language
$SiteInfo = [
  "J01" => array("IvedaXpress Video Hosting","Copyright &#169; 2014 Iveda Solutions Inc.","127.0.0.1:80","rtsay@iveda.com","+886.2.29997699","+886.2.29997699","en_US.UTF-8" ),
  "T03" => array("IvedaXpress Video Hosting","Copyright &#169; 2014 Iveda Solutions Inc.","127.0.0.1:80","servicedesk@iveda.com"," "," " ),
  "P04" => array("IvedaXpress Video Hosting","Copyright &#169; 2014 PLDT","127.0.0.1:80","cloud.helpdesk@epldt.com","177","177","en_US.UTF-8" ),
  "Z02" => array("IvedaXpress Video Hosting","Copyright &#169; 2014 Iveda Solutions Inc.","127.0.0.1:80","servicedesk@iveda.com"," "," ","en_US.UTF-8" ),
  "V03" => array("VNPT Video Hosting","Copyright &#169; 2015 VNPT","127.0.0.1:80","vnptcamera@vdconline.vn","vnpt hotline","vnpt hotline","en_US.UTF-8" ),
  "V04" => array("SentirVietnam Video Hosting","Copyright &#169; 2015 Iveda Inc.","127.0.0.1:80","servicedesk@iveda.com"," "," ","en_US.UTF-8" ),
  "X02" => array("Megasys Xpress Video Hosting","Copyright &#169; 2015 Megasys Sole-Vision Tech.","127.0.0.1:80","victor@tdi-megasys.com","+886.2.29997699","+886.2.29997699","zh_TW.UTF-8" ),
  "X01" => array("IvedaXpress Video Hosting","Copyright &#169; 2015 Megasys Sole-Vision Tech.","127.0.0.1:80","alarm@tdi-megasys.com","29997699","29997699","zh_TW.UTF-8" ),
  "T04" => array("道管施工影像系統","Copyright &#169; 2015 New Construction Office, PWD, TCG","127.0.0.1:80","tingkai.lu@tdi-megasys.com","+886.2.29997699","+886.2.29997699","zh_TW.UTF-8" ),
  "T05" => array("道管施工影像系統","Copyright © Taoyuan City Government. All rights reserved.","127.0.0.1:80","tingkai.lu@tdi-megasys.com","+886.2.29997699","+886.2.29997699","zh_TW.UTF-8" ),
  "K01" => array("道管施工影像系統","版權所有 © 高雄市道路挖掘管理中心.","127.0.0.1:80","tingkai.lu@tdi-megasys.com","+886.2.29997699","+886.2.29997699","zh_TW.UTF-8" ),
  "T06" => array("即時工務影像管理服務","版權所有 © 艾維達系統有限公司.","127.0.0.1:80","cherlin@tdi-megasys.com","+886.2.29997699","+886.2.29997699","zh_TW.UTF-8" ),
  "C13" => array("即時工務影像管理服務","版權所有 © 奇美實業有限公司.","127.0.0.1:80","tingkai.lu@tdi-megasys.com","+886.2.29997699","+886.2.29997699","zh_TW.UTF-8" ),
  "default" => array("IvedaXpress Video Hosting","Copyright &#169; 2014 Iveda Solutions Inc.","127.0.0.1:80","alarm@tdi-megasys.com","+886.2.29997699","+886.2.29997699","en_US.UTF-8" )
];
$mis_email=$SiteInfo['default'][3]; //Mis_oem_email alarm
// (11) oem, 0:auth_type, 1:realm, username, pwd, 4:enc,  
// 5:host, port, 7:from_email, from_name, brand, 10:company
//12 db mail=> oem_id, auth_type, <realm>,username... 
$SiteMail = [
  "J01" => array("","","","","",  "10.0.0.9","25","alarm@tdi-megasys.com","IvedaXpress JAPAN","IvedaXpress JAPAN","Iveda Inc."),
  //"p04" => array("","","","","",  "10.31.20.80","25","noreply@videomonitoring.pldtcloud.com","PLDT Video Monitoring","PLDT Video Monitoring","PLDT"),
  "P04" => array("","","","","",  "192.168.0.14","587","noreply@videomonitoring.pldtcloud.com","PLDT Video Monitoring","PLDT Video Monitoring","PLDT"),
  "Z02" => array("","","","","",  "127.0.0.1","587","servicedesk@iveda.com","IvedaXpress","IvedaXpress","Iveda Inc."),
  "V03" => array("LOGIN","","vnptcamera@vdconline.vn","vdc123456","ssl",  "smtp.gmail.com","465","vnptcamera@vdconline.vn","vnptcamera","vnptcamera","VNPT"),
  "V04" => array("","","","","",  "192.168.2.200","25","servicedesk@iveda.com","SentirVietnam","SentirVietnam","Iveda Inc."),
  "T04" => array("","","","","",  "192.168.1.200","587","alarm@tdi-megasys.com","IvedaXpress","IvedaXpress","Iveda Inc."),
  "T05" => array("","","","","",  "192.168.1.200","587","alarm@tdi-megasys.com","IvedaXpress","IvedaXpress","Iveda Inc."),
  "K01" => array("","","","","",  "192.168.2.200","587","alarm@tdi-megasys.com","IvedaXpress","IvedaXpress","Sole-Vision Tech, Inc."),
  "T06" => array("","","","","",  "127.0.0.1","587","alarm@tdi-megasys.com","WorkEYE","WorkEYE","艾維達系統有限公司"),
  "X02" => array("","","","","",  "192.168.1.200","587","alarm@tdi-megasys.com","IvedaXpress","IvedaXpress","Iveda Inc."),
  "X01" => array("","","","","",  "127.0.0.1","587","alarm@tdi-megasys.com","IvedaXpress","IvedaXpress","Iveda Inc."),
  "T06" => array("","","","","",  "127.0.0.1","587","alarm@tdi-megasys.com","EngEYE","EngEYE","奇美實業有限公司"),
  "default" => array("","","","","",  "192.168.1.200","587","alarm@tdi-megasys.com","IvedaXpress","IvedaXpress","Iveda Inc.")
];


function getSql($oemTag,$type)
{
  global $SiteInfo,$SiteMail;
  global $home_url;
  $web_url = rtrim($home_url,":8080");
  $web_url= preg_replace('#^https?://#', '', $web_url);

  $sql="";
  //_mail part for different Array access
  if (strpos($type, "_mail") !== false) {
      foreach ($SiteMail as $oemkey => $oemArr){
          if (strtolower($oemTag) == strtolower($oemkey) ){
            if ($type == "insert_mail"){
              $sql="INSERT INTO isat.mail VALUES ('{$oemTag}','{$oemArr[0]}',
              '{$oemArr[1]}','{$oemArr[2]}','{$oemArr[3]}',
              '{$oemArr[4]}','{$oemArr[5]}',{$oemArr[6]},
              '{$oemArr[7]}','{$oemArr[8]}','{$oemArr[9]}','{$oemArr[10]}')";
            }else if ($type == "update_mail"){
              $sql="UPDATE isat.mail set auth_type='{$oemArr[0]}',
              realm='{$oemArr[1]}',username='{$oemArr[2]}',
              password='{$oemArr[3]}',enc='{$oemArr[4]}',
              host='{$oemArr[5]}',port={$oemArr[6]},
              from_email='{$oemArr[7]}',from_name='{$oemArr[8]}',
              brand='{$oemArr[9]}',company='{$oemArr[10]}'";
            }
          }
      }
      if ($sql==""){ //default
        if ($type == "insert_mail"){
  $sql="INSERT INTO isat.mail VALUES ('{$oemTag}','".$SiteMail['default'][0]."',
      '".$SiteMail['default'][1]."','".$SiteMail['default'][2]."',
      '".$SiteMail['default'][3]."','".$SiteMail['default'][4]."',
      '".$SiteMail['default'][5]."',".$SiteMail['default'][6].",
      '".$SiteMail['default'][7]."','".$SiteMail['default'][8]."',
      '".$SiteMail['default'][9]."','".$SiteMail['default'][10]."')";
        }else if ($type == "update_mail"){
          $sql="UPDATE isat.mail set auth_type='".$SiteMail['default'][0]."',
    realm='".$SiteMail['default'][1]."',username='".$SiteMail['default'][2]."',
    password='".$SiteMail['default'][3]."',enc='".$SiteMail['default'][4]."',
    host='".$SiteMail['default'][5]."',port=".$SiteMail['default'][6].",
    from_email='".$SiteMail['default'][7]."',from_name='".$SiteMail['default'][8]."',
    brand='".$SiteMail['default'][9]."',company='".$SiteMail['default'][10]."'";
        }
      }

  }else{ //other type
      foreach ($SiteInfo as $oemkey => $oemArr){
        if (strtolower($oemTag) == strtolower($oemkey) ){
          if ($type == "update_web"){
              //$sql="update isat.oem_info set title='{$oemArr[0]}' where oem_id='{$oemkey}'";
              $sql="update isat.oem_info set title='{$oemArr[0]}',notification_email='{$oemArr[2]}',copyright='{$oemArr[1]}',support_email='{$oemArr[3]}' where oem_id='{$oemTag}'";
          }else if ($type == "insert_web"){
              $sql="insert into isat.oem_info (oem_id,notification_email,title,copyright,url,support_email) values ('{$oemTag}','{$oemArr[2]}', '{$oemArr[0]}','{$oemArr[1]}','{$web_url}','{$oemArr[3]}')";
          }else if($type == "update_android_email"){
              $sql="update qlync.oem_info set Content=\"{$oemArr[3]}\"  where Cat2=\"Android_oem_contact_email\";";
          }else if($type == "update_android_phone"){
              $sql="update qlync.oem_info set Content=\"{$oemArr[4]}\"  where Cat2=\"Android_oem_contact_phone\";";
          }else if($type == "update_android_contact"){              
              $content="<h1>Customer Support</h1>\n<dl>\n";
              $content.="<dt>E-mail</dt>\n<dd><a href='mailto:{$oemArr[3]}'>{$oemArr[3]}</a></dd>\n";
              $content.="<dt>Tel</dt>\n<dd><a href='tel:{$oemArr[4]}'>{$oemArr[4]}</a></dd>\n</dl>\n";
              $sql="update isat.oem_info set contact_android=\"{$content}\";";
          }else if($type == "update_ios_email"){
              $sql="update qlync.oem_info set Content=\"{$oemArr[3]}\"  where Cat2=\"Ios_oem_contact_email\";";
          }else if($type == "update_ios_phone"){
              $sql="update qlync.oem_info set Content=\"{$oemArr[5]}\"  where Cat2=\"Ios_oem_contact_phone\";";
          }else if($type == "update_ios_contact"){              
              $content="<h1>Customer Support</h1>\n<dl>\n";
              $content.="<dt>E-mail</dt>\n<dd><a href='mailto:{$oemArr[3]}'>{$oemArr[3]}</a></dd>\n";
              $content.="<dt>Tel</dt>\n<dd><a href='tel:{$oemArr[5]}'>{$oemArr[5]}</a></dd>\n</dl>\n";
              $sql="update isat.oem_info set contact_ios=\"{$content}\";";
          }else if ($type == "update_mis"){
              $sql="update qlync.oem_info set Content=\"{$mis_email}\"  where Cat2=\"Mis_oem_email\";";
          }else if ($type =="update_lang"){
              $sql="update qlync.oem_info set Content=\"{$oemArr[6]}\" where Cat2=\"Admin_web_language\"";
          }
        }//oem matching
      }//foreach
      if ($sql==""){ //default
          if ($type == "update_web"){
              $sql="update isat.oem_info set title='".$SiteInfo['default'][0]."',notification_email='".$SiteInfo['default'][2]."',copyright='".$SiteInfo['default'][1]."',support_email='".$SiteInfo['default'][3]."' where oem_id='{$oemTag}'";
          }else if ($type == "insert_web"){
            $web_url = rtrim($home_url,":8080");
            $web_url= preg_replace('#^https?://#', '', $web_url);
              $sql="insert into isat.oem_info (oem_id,notification_email,title,copyright,url,support_email) values ('{$oemTag}','".$SiteInfo['default'][2]."', '".$SiteInfo['default'][0]."','".$SiteInfo['default'][1]."','{$web_url}','".$SiteInfo['default'][3]."')";
          }else if($type == "update_android_email"){
              $sql="update qlync.oem_info set Content=\"".$SiteInfo['default'][3]."\"  where Cat2=\"Android_oem_contact_email\";";
          }else if($type == "update_android_phone"){
              $sql="update qlync.oem_info set Content=\"".$SiteInfo['default'][4]."\"  where Cat2=\"Android_oem_contact_phone\";";
          }else if($type == "update_android_contact"){              
              $content="<h1>Customer Support</h1>\n<dl>\n";
              $content.="<dt>E-mail</dt>\n<dd><a href='mailto:".$SiteInfo['default'][3]."'>".$SiteInfo['default'][3]."</a></dd>\n";
              $content.="<dt>Tel</dt>\n<dd><a href='tel:".$SiteInfo['default'][4]."'>".$SiteInfo['default'][4]."</a></dd>\n</dl>\n";
              $sql="update isat.oem_info set contact_android=\"{$content}\";";
          }else if($type == "update_ios_email"){
              $sql="update qlync.oem_info set Content=\"".$SiteInfo['default'][3]."\"  where Cat2=\"Ios_oem_contact_email\";";
          }else if($type == "update_ios_phone"){
              $sql="update qlync.oem_info set Content=\"".$SiteInfo['default'][5]."\"  where Cat2=\"Ios_oem_contact_phone\";";
          }else if($type == "update_ios_contact"){              
              $content="<h1>Customer Support</h1>\n<dl>\n";
              $content.="<dt>E-mail</dt>\n<dd><a href='mailto:".$SiteInfo['default'][3]."'>".$SiteInfo['default'][3]."</a></dd>\n";
              $content.="<dt>Tel</dt>\n<dd><a href='tel:".$SiteInfo['default'][5]."'>".$SiteInfo['default'][5]."</a></dd>\n</dl>\n";
              $sql="update isat.oem_info set contact_ios=\"{$content}\";";
          }else if ($type == "update_mis"){
              $sql="update qlync.oem_info set Content=\"".$SiteInfo['default'][3]."\"  where Cat2=\"Mis_oem_email\";";
          }else if ($type =="update_lang"){
              $sql="update qlync.oem_info set Content=\"".$SiteInfo['default'][6]."\" where Cat2=\"Admin_web_language\"";
          }//default 

      }
  }//compare type
  return $sql;
 
} 

function dbEntryExist($sql)//return true/false
{
  if ($sql!=""){
    sql($sql,$result,$num,0);
    if ($result)
      if ($num > 0) return true;
  }
  return false; 
}
function sqlResultPrint($sql,$tasktitle)
{  //sql($sql,$result,$num,0);
  //fetch($arr,$result,0,0);
  if ($sql!=""){ 
    sql($sql,$result,$num,0);
    if ($result) $response = "Succeed\n";
    else $response = "Fail\n"; 
    printf ("command:".$sql."\n".$tasktitle."=>". $response."\n");
  }else printf($tasktitle." Exist!!\n");
  
}

if (dbEntryExist("select oem_id from isat.oem_info")){
  if ($update_app){
      $sql = getSql($oem,"update_web");
  }else
    $sql = "";
}else{
  $sql = getSql($oem,"insert_web");
}
sqlResultPrint($sql,"Web server oem_info");

//insert  or update email server
if (dbEntryExist("select oem_id from isat.mail")){
  if ($update_app){
    $sql = getSql($oem,"update_mail");
  }else
    $sql = "";
}else{
  $sql = getSql($oem,"insert_mail");
}
sqlResultPrint($sql,"Mail server info");

// Web server android, ios contact us info
//check blank to auto enable
$sql = getSql($oem,"update_lang");
sqlResultPrint($sql,"Admin Default Language");

if (dbEntryExist("select * from qlync.oem_info where Cat2=\"Ios_oem_contact_email\" and Content=\"support@qlync.com\"")){
    $update_app = true;
}
if ($update_app){
    $sql= getSql($oem,"update_mis");
    sqlResultPrint($sql,"Mis Email Update");

    $sql= getSql($oem,"update_ios_email");
    sqlResultPrint($sql,"iOS App email");
    $sql= getSql($oem,"update_ios_phone");
    sqlResultPrint($sql,"iOS App Phone");
    $sql= getSql($oem,"update_ios_contact");
    sqlResultPrint($sql,"iOS App Contact");

    $sql= getSql($oem,"update_android_email");
    sqlResultPrint($sql,"Android App email");
    $sql= getSql($oem,"update_android_phone");
    sqlResultPrint($sql,"Android App phone");
    $sql= getSql($oem,"update_android_contact");
    sqlResultPrint($sql,"Android App Contact");

}
//constants
foreach ($SiteInfo as $oemkey => $oemArr){
  if (strtolower($oem) == strtolower($oemkey) )
     $support_email= $oemArr[3];
}
if ($support_email == "")
     $support_email= $SiteInfo['default'][3];

//force update web server (updateOemConfig)
	$params['command']='set';
	$params['oem_id']=$oem;
  $params['support_email']=$support_email;
  $ch=curl_init();
	$url = "http://{$api_id}:{$api_pwd}@{$api_ip}:{$api_port}/manage/manage_oem.php";
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
	curl_setopt($ch, CURLOPT_HEADER, false);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
	$result = curl_exec($ch);
	$content=json_decode($result,true);
//echo curl_error($ch);
	curl_close($ch);
  echo "set: ".$result."\n"; 

$result = exec("wget -q -O - '".$url."?oem_id={$oem}&command=download'");
echo "download: ".$result."\n";	
?>