<?php
/*************
 *Validated on Mar-24,2017,  
 * for RPIC plugin include header
 * remove authentication part
 * required config.php   
 *Writer : JinHo Chang
 **************/
#Authentication Section DISBLED
//if (!isset($_SESSION["Email"]) ) exit();

if (!function_exists('getWebURL')){
//isLAN, //getWEBURL
include_once ("../debug/_iveda.inc"); 
}

function isDBFieldAvail($field, $table, $db){
  $sql = "SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = '{$db}' AND TABLE_NAME = '{$table}' AND COLUMN_NAME = '{$field}'";
  sql($sql,$result,$num,0);
  if ($num==0) return false;
  else return true;
}

function getContact($adminuser){
  $sql = "select Contact from qlync.account where Email='{$adminuser}'";
  sql($sql,$result,$num,0);
  fetch($arr,$result,0,0);
  if ($num ==1) return $arr['Contact'];
  return "";
}

function addShareDeviceAPI ($mac, $username)
{
   //$req_var = explode("-", $uid);
   //$mac = $req_var[1];
   global $oem, $api_id, $api_pwd, $api_path;
    $import_target_url ="http://{$api_id}:{$api_pwd}@{$api_path}/manage_share.php?command=share_camera&mac={$mac}&user_name={$username}";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$import_target_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result=curl_exec($ch);
        curl_close($ch);
        $content=array();
        $content=json_decode($result,true);
    if($content["status"]=="success")
      return true;
    return false;
}

function deleteShareDeviceAPI ($mac, $username)
{
   //$req_var = explode("-", $uid);
   //$mac = $req_var[1];
   global $oem, $api_id, $api_pwd, $api_path;
    $import_target_url ="http://{$api_id}:{$api_pwd}@{$api_path}/manage_share.php?command=unshare_camera&mac={$mac}&user_name={$username}";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$import_target_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result=curl_exec($ch);
        curl_close($ch);
        $content=array();
        $content=json_decode($result,true);
if (isset($_REQUEST["debugadmin"])) var_dump($content);
    if($content["status"]=="success")
      return true;
    return false;
}

function httpError($code, $message) {
    header($_SERVER["SERVER_PROTOCOL"] . ' ' . $code);
    echo $message . "\n";
    exit(0);
}

function pdoLogin($user, $pwd)
{
    $sql="select * from qlync.account where Email =? and DECODE(Password,'".substr($user,0,5)."')=?";
    $pdo = getPDO();
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(1, $user,PDO::PARAM_STR); 
    $stmt->bindParam(2, $pwd,PDO::PARAM_STR);  
    $stmt->execute();
    $db=$stmt->fetch(PDO::FETCH_ASSOC); //get one row 
    if($stmt->rowCount()==1){
      $sql="insert into qlync.login_log (Account, Date) values( '{$user}','".date("Y-m-d H:i:s")."')";
      $execResult =$pdo->exec($sql);
      return true;
    }
    return false;
}
function getPDO(){
  global $mysql_ip, $mysql_id, $mysql_pwd;
  $ref=exec("grep utf8 /var/www/qlync_admin/doc/mysql_connect.php");//correct
  if ($ref=="")//pre v3.2.1 vesion
    $pdo = new PDO('mysql:host='.$mysql_ip, $mysql_id, $mysql_pwd);
  else//correct utf8 
  $pdo = new PDO('mysql:host='.$mysql_ip, $mysql_id, $mysql_pwd,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
  return $pdo;
}

############  Authentication Section End
$RPICAPP_USER_PWD = [
//oem=> pwd, page, note, siteName
"T04"=>array("Ea9M7gOu586UQaOtXJ3e6f51","backstage_login_tpe.php","六~十五碼不限大寫字母數字,<br>123456789, AA1234567<br>六~十五碼數字加之號,<br>123456789-01,NNN-NNNN, NN-NNNN","台北市道路管線中心"),
"T05"=>array("tN8V8bMTtuKycj7BNW2Esp8p","backstage_login_ty.php","五~十五碼不限大寫字母數字之號,<br>123456789, AA1234567","桃園市道路挖掘服務中心"),
"K01"=>array("ydEP6Ug6uBzWTXU28gfSV3hu","backstage_login_rpic.php","五~三十二碼不限大寫字母數字加之號,<br>123456789, AA1234567,123456789-01, 123456789-1","高雄市道路挖掘管理中心"),
"T06"=>array("vzEG2Ea6fWzEGb5Ea235uZvm","backstage_login_rpic.php","五~三十二碼不限大寫字母數字加之號,<br>123456789, AA1234567,123456789-01, 123456789-1","即時工務影像管理服務"),
"X02"=>array("ydEP6Ug6uBzWTXU28gfSV3hu","backstage_login_rpic.php","五~三十二碼不限大寫字母數字加之號,<br>123456789, Aa1234567,123456789-01, 123456789-1","MegasysXpress"),
"C13"=>array("Cqjf6NE2R5xY5unCMXVDqqmP","backstage_login_rpic.php","五~三十二碼不限數字加之號,<br>000-0000, 123456789-01, 123456789-1","奇美工務影像管理服務"), 
//XXX"=>array("skv9SjcwfFGgFzZduacc","backstage_login_rpic.php",""),
"RPIC"=>array("ydEP6Ug6uBzWTXU28gfSV3hu","backstage_login_rpic.php","五~三十二碼不限大寫字母數字加之號,<br>123456789, Aa1234567,123456789-01, 123456789-1","工務影像管理服務")
];
$CamCID=[//CID, PREFIX
  "M04CC"=>"184E",
  "Z01CC"=>"001B",
  "B03CC"=>"0050",
  "A02CC"=>"3C49",
  "F01CC"=>"2C62",
  $oem."MC"=>"M".$oem
];

function checkUID($mac)//get CID
{
  global $CamCID;
  foreach ($CamCID as $cid => $prefix)
  {
     if (strpos($mac,$prefix)!== FALSE)//found
      return true;
  }
  return false;
}

//manage_share error code getErrorCode($errmsg)
  $errorMSG = [
    "FAIL:101" => "Invalid user account",  //user
    "FAIL:102" => "Dupliated name or register email",  //user
    "FAIL:103" => "Unknown command.",  //user
    "FAIL:104" => "User not found.",  //user
    "FAIL:105" => "Account Format Error.",  //user
    "FAIL:106" => "Account Exist.",  //user
    "FAIL:107" => "Unauthorize to delete common user account",  //user
    "FAIL:108" => "PipeGIS Account Not Exist.",  //K01 user
    "FAIL:109" => "Invalid Username / Password.",
    "FAIL:110" => "Please check your confirm letter first.",
    "FAIL:111" => "Product unmatched.",
    "FAIL:201" => "Device not found.",  //mac
    "FAIL:202" => "Device is shared to requested account.",  //mac
    "SUCCESS:202" => "Device is shared to requested account.",  //special add
    "FAIL:203" => "Device is NOT shared to requested account.",  //mac
    "FAIL:204" => "Invalid MAC format",  //mac
    "FAIL:205" => "Device does not belong to any user",  //mac    
    "FAIL:301" => "Invalid command",   //command
    "FAIL:302" => "Invalid parameters.",
    "FAIL:303" => "Can't connect to DB",
    "FAIL:404" => "ErrorCode Matching Fail"
  ];
define("ADMIN_PREFIX","ADMIN_");
define("USER_EMAIL_PREFIX","user_");
define("USER_EMAIL_POSTFIX","@safecity.com.tw");

//align with manage_share.php
function checkSiteAccountRule($siteAccount)
{
  global $oem;
  if ($oem=="K01"){ 
    if (preg_match("/^[0-9A-Z'-]{5,15}$/",$siteAccount)) 
    { //correct site format
        return true;
    }
  }else if ($oem=="T05"){
    if (preg_match("/^[0-9A-Z'-]{5,15}$/",$siteAccount))
    { //correct site format
        return true;
    }
  }else if ($oem=="T04"){
    if (preg_match("/^[0-9A-Z'-]{6,15}$/",$siteAccount)) //2017Aug1
    { //correct site format
        return true;
    } 
  }else if ($oem=="C13"){
    if (preg_match("/^[0-9]{3,}-[0-9]{2,}$/",$siteAccount)) //2017Aug1
    { //correct site format
        return true;
    }
  }else{
    if (preg_match("/^[0-9A-Z'-]{5,32}$/",$siteAccount))
    { //correct site format
        return true;
    }
  }
  return false;
}
############### share_log database
/* //cannot use unless whole page is using customerservice db only
function addShareLog ($mac, $owner_id,$owner_name,$visitor_id,$visitor_name,$action)
{//DELETE S_ACCT
    $sql ="insert into customerservice.share_log(mac,owner_id,owner_name,visitor_id,visitor_name,action,result,ip_addr) values
    ('$mac','{$owner_id}','{$owner_name}','{$visitor_id}','{$visitor_name}','{$action}','SUCCESS','{$_SERVER['REMOTE_ADDR']}')";
    sql($sql,$result,$num,0);
}
*/
?>