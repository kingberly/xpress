<?php
/****************
 *Validated on Dec-6,2017,
 * mgmt user API on admin
 * add /delete / set_expire / reset_expire
 * add get_date_list and get_date
 * add get_user_list   
 * return Success or Fail or string
 * Date format is in local server Y-M-d
 * Add get_camera_list / get_token if validated
 * @ /var/www/qlync_admin/html/api           
 *Writer: JinHo, Chang
*****************/

include("/var/www/qlync_admin/doc/config.php");
include("/var/www/qlync_admin/doc/mysql_connect.php"); 
include("/var/www/qlync_admin/doc/sql.php");
include("util.php");

//global $oem, $api_id, $api_pwd, $api_path;

function insertUser ($email,$name,$password) //can return true/false
{
    global $oem, $api_id, $api_pwd, $api_path;
      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) return false;
      if (!preg_match("/^[0-9a-zA-Z-]{4,32}/",$name)) return false;
      else if (!preg_match("/^[0-9a-zA-Z]{4,32}/",$password)) return false;
      $email = strtolower($email);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $params = array(
                'command'=>'add',
                'name'=>"{$name}",
                'pwd'=>"{$password}",
                'reg_email'=>"{$email}",
                'oem_id'=>"{$oem}" );
       
        $url = "http://{$api_id}:{$api_pwd}@{$api_path}/manage_user.php?" . http_build_query($params);
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);
        curl_close($ch);
        $content=json_decode($result,true);
        if($content["status"]=="success")
        {
            $sql="insert into qlync.end_user_list ( Account,Email, Type,Time_update,Oem_id) values ('".mysql_real_escape_string($name)."','".mysql_real_escape_string($email)."','s','".date("Y-m-d H:i")."','{$oem}')";
            sql($sql,$result_tmp,$num_tmp,0);
            return true;
        }
      //print $content['error_msg'];
      return false;
}
function deleteUser($email)
{
  global $oem, $api_id, $api_pwd, $api_path;
    $import_target_url ="http://{$api_id}:{$api_pwd}@{$api_path}/manage_user.php?command=delete&reg_email={$email}&oem_id={$oem}";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$import_target_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result=curl_exec($ch);
    curl_close($ch);
    $content=array();
    $content=json_decode($result,true);
    if($content["status"]=="success")
    {
      $sql="delete from qlync.end_user_list where Email='".mysql_real_escape_string($email)."' and Oem_id='{$oem}'";
      sql($sql,$result_tmp,$num_tmp,0);
      return true;
    }
    return false;
}


function deleteUserOEM($email,$oemid)
{
  global $api_id, $api_pwd, $api_path;
    $import_target_url ="http://{$api_id}:{$api_pwd}@{$api_path}/manage_user.php?command=delete&reg_email={$email}&oem_id={$oemid}";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$import_target_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result=curl_exec($ch);
    curl_close($ch);
    $content=array();
    $content=json_decode($result,true);
    if($content["status"]=="success")
    {
      $sql="delete from qlync.end_user_list where Email='".mysql_real_escape_string($email)."' and Oem_id='{$oemid}'";
      sql($sql,$result_tmp,$num_tmp,0);
      return true;
    }
    return false;
}

function setAccountExpireDate($name,$type)
{
    $sql="select reg_date,expire_date from isat.user where name='{$name}'";
    sql($sql,$result,$num,0);
    if ($num ==1 ){
      fetch($arr,$result,0,0);
      if ($type=="set_expire")
        $newdate=intval($arr['reg_date']) + 1;
      else if ($type=="reset_expire")
        $newdate=intval($arr['reg_date']) + 315532800;//315532800=10years
      
      $sql="update isat.user set expire_date={$newdate} where name='{$name}'";
      sql($sql,$result_update,$num_update,0);
      if ($result_update) return true;
      else return false;
    }
    return false;
}

function getAccountDate($name)
{//DATE_FORMAT(FROM_UNIXTIME(reg_date),'%Y-%m-%d')
    if ($name == "")
      //$sql="select name,reg_date,expire_date,login_date from isat.user";
      $sql="select name,DATE_FORMAT(FROM_UNIXTIME(reg_date),'%Y-%m-%d') as reg_date,DATE_FORMAT(FROM_UNIXTIME(expire_date),'%Y-%m-%d') as expire_date,DATE_FORMAT(FROM_UNIXTIME(login_date),'%Y-%m-%d') as login_date from isat.user";
    else
      //$sql="select name,reg_date,expire_date,login_date from isat.user where name='{$name}'";
      $sql="select name,DATE_FORMAT(FROM_UNIXTIME(reg_date),'%Y-%m-%d') as reg_date,DATE_FORMAT(FROM_UNIXTIME(expire_date),'%Y-%m-%d') as expire_date,DATE_FORMAT(FROM_UNIXTIME(login_date),'%Y-%m-%d') as login_date from isat.user where name='{$name}'";
    sql($sql,$result,$num,0);
    if ($num == 0) return "Fail";
    $textList="";
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      $textList.= $arr['name'].",".$arr['reg_date'].",".$arr['expire_date'].",".$arr['login_date']."\n";
    }
    $textList = rtrim($textList, ",\n");
    return $textList;
}

function getAccountList()
{//DATE_FORMAT(FROM_UNIXTIME(reg_date),'%Y-%m-%d')
    $sql="select name,reg_email,DATE_FORMAT(FROM_UNIXTIME(reg_date),'%Y-%m-%d') as reg_date from isat.user order by id";
      //$sql="select name,DATE_FORMAT(FROM_UNIXTIME(reg_date),'%Y-%m-%d') as reg_date,DATE_FORMAT(FROM_UNIXTIME(expire_date),'%Y-%m-%d') as expire_date,DATE_FORMAT(FROM_UNIXTIME(login_date),'%Y-%m-%d') as login_date from isat.user";
    sql($sql,$result,$num,0);
    $textList="";
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      $textList.= $arr['name'].",".$arr['reg_email'].",".$arr['reg_date']."\n";
    }
    $textList = rtrim($textList, ",\n");
    return $textList;
}


function VerifyUserWithPwd($name,$pwd)
{
  global $api_id, $api_pwd, $api_ip, $oem;
    $import_target_url ="http://{$api_id}:{$api_pwd}@{$api_ip}/backstage_login.php?user_name={$name}&user_pwd={$pwd}&oem_id={$oem}";
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

function getStreamToken($name,$pwd)
{
  global $api_id, $api_pwd, $api_ip;
    $import_target_url ="http://{$api_id}:{$api_pwd}@{$api_ip}/backstage_token_auth.php?command=authenticate&user={$name}&pwd={$pwd}";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$import_target_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result=curl_exec($ch);
    curl_close($ch);
    $content=array();
    $content=json_decode($result,true);
    if($content["status"]=="success")
      return $content["key"];
    return "";
}

function getCamera($user_name)
{
  $sql = "select mac_addr from isat.query_info where user_name='{$user_name}' group by mac_addr";
  sql($sql,$result,$num,0);
  $textList="";
  if ($num > 0){
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
        $textList.= $arr['mac_addr']."\n"; 
      }
      $textList = rtrim($textList, "\n");
  }
  return $textList;
}

//var_dump($_REQUEST);  
if($_REQUEST["id"]==$internal_api_id and $_REQUEST["pwd"]==$internal_api_pwd)
{
  if ( isset($_REQUEST["command"]) )
  {

    $command = $_REQUEST["command"];
    if ($command == "add" and isset($_REQUEST["password"]) and isset($_REQUEST["name"]) )
    {
      if (insertUser($_REQUEST["email"],$_REQUEST["name"],$_REQUEST["password"]) ){
        InsertLog($command." ".$_REQUEST["name"],"SUCCESS");
        echo 'Success';
      }else{
          InsertLog($command." ".$_REQUEST["name"],"FAIL");
          echo 'Fail';
       }
    }else if ( $command == "delete"){
        if (deleteUser($_REQUEST["email"]) ){
        InsertLog($command." ".$_REQUEST["email"],"SUCCESS");
        echo 'Success';
      }else{
          InsertLog($command." ".$_REQUEST["email"],"FAIL");
          echo 'Fail';
       }
//--------------------validate user and get owner camear MAC-------------
    }else if ( $command == "get_camera_list"){
      if (VerifyUserWithPwd($_REQUEST["name"],$_REQUEST["password"]))
        echo getCamera($_REQUEST["name"]);
      else  echo 'Fail';
    }else if ( $command == "get_token"){
      if (VerifyUserWithPwd($_REQUEST["name"],$_REQUEST["password"]))
        echo getStreamToken($_REQUEST["name"],$_REQUEST["password"]);
      else  echo 'Fail';
//--------------------godwatch-------------
    }else if ( $command == "deletegw"){
        if (deleteUserOEM($_REQUEST["email"],"G09") ){
        InsertLog($command." G09:".$_REQUEST["email"],"SUCCESS");
        echo 'Success';
      }else{
          InsertLog($command." G09:".$_REQUEST["email"],"FAIL");
          echo 'Fail';
       }
    }else if ( $command == "deletesp"){
        if (deleteUserOEM($_REQUEST["email"],$_REQUEST["oemid"]) ){
        InsertLog($command." ".$_REQUEST["oemid"]." :".$_REQUEST["email"],"SUCCESS");
        echo 'Success';
      }else{
          InsertLog($command." ".$_REQUEST["oemid"]." :".$_REQUEST["email"],"FAIL");
          echo 'Fail';
       }
//--------------------VNPT-------------
    }else if ( $command == "get_date" )
    {//reg_date,expire_date,login_date
      if (isset($_REQUEST["name"]) ){
        InsertLog($command." ".$_REQUEST["name"],"SUCCESS");
        echo getAccountDate($_REQUEST["name"]);
      }else{
          InsertLog($command,"FAIL");
          echo 'Fail';
       }
    }else if ( $command == "get_date_list" )
    {//reg_date,expire_date,login_date
        InsertLog($command,"SUCCESS");
        echo getAccountDate("");
    }else if ( $command == "get_user_list" )
    {
        InsertLog($command,"SUCCESS");
        echo getAccountList();
    }else if ( (( $command == "set_expire")
          or ( $command == "reset_expire") )
          and (isset($_REQUEST["name"]))
    ){
        if (setAccountExpireDate($_REQUEST["name"],$command) ){
        InsertLog($command." ".$_REQUEST["name"],"SUCCESS");
        echo 'Success';
      }else{
          InsertLog($command,"FAIL");
          echo 'Fail';
       }
    }else{ //error command
        InsertLog($command,"FAIL");
        echo 'Fail';
    } 
  }else{//isset command
    //print ' Please Enter Corrent Parameters !';
    InsertLog("Incorrect Parameter","FAIL");
    echo 'Fail';
  }
}else{
  InsertLog("ID/Pwd","FAIL");
  echo 'Fail';
	//echo 'Please Enter Correct ID/ PWD !';
}

?>
