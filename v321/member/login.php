<?php
include_once("../../header.php");
include_once("../../menu.php");
/****************
 *Jul-6,2017, v3.2.1          
 * Add SQL Injection protection by PDO object
 * fix $var_req parsing variable issue
 * Add Insert sql by PDO object
 * Add warning message             
 *Writer: JinHo, Chang
*****************/
//var_dump($_REQUEST);
  $ref=exec("grep utf8 /var/www/qlync_admin/doc/mysql_connect.php");//correct
  if ($ref=="")//pre v3.2.1 vesion
    $pdo = new PDO('mysql:host='.$mysql_ip, $mysql_id, $mysql_pwd);
  else//correct utf8 
  $pdo = new PDO('mysql:host='.$mysql_ip, $mysql_id, $mysql_pwd,
      array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); //must add
//SET sql_mode='NO_BACKSLASH_ESCAPES';
//Config File: /etc/mysql/my.cnf
//[mysqld] 
//sql_mode = NO_BACKSLASH_ESCAPES
#Apply Partner

function sqlInjectionFilter($var)
{
    $var = preg_replace("/[\'\"]+/" , '' ,$var);    
    $r=array("AND ","OR ","--","=");
    //$result = str_replace($r,"",(mysql_real_escape_string($var)));
    $var =mysql_real_escape_string($var);
    $result = str_replace($r,"",$var);
    $rep = array("'" => "&#39;", "\"" => "&quot;");
    $result = strtr($result, $rep);
    return $result;
}
function validStr($type,$var){
  $flag="pass"; //default pass
  //preg_match("/\p{Han}+/u", $utf8_str);
  if ($type=="email") 
    if (!preg_match("/^[0-9a-zA-Z@.]{4,64}$/",$var)) $flag="fail";
  else if ($type=="password")
    if (!preg_match("/^[0-9a-zA-Z]{4,32}$/",$var)) $flag="fail";
  else if ($type=="mobile")
    if (!preg_match("/^[0-9.\(\)'-]{0,16}$/",$var)) $flag="fail";
  else if ($type=="contact")
    if (!preg_match("/^[0-9a-zA-Z'-]{4,32}$/",$var)) $flag="fail";
  else if ($type=="hidden_num")
    if (!preg_match("/^[0-9]{4}$/",$var)) $flag="fail";
  else{
    if (strpos($var,"AND ")!== FALSE)  $flag="fail"; //found
    if (strpos($var,"OR ")!== FALSE)  $flag="fail";
    if (strpos($var,"--")!== FALSE)  $flag="fail";
    if (strpos($var,"=")!== FALSE)  $flag="fail";
  }

  if ($flag=="fail"){
    echo "{$type} Format Error, Please Try again!<BR>\n";
    //exit(1);
  }
}
function addAdminPDO($service)
{
  global $pdo;
  $table = 'qlync.account';
//ENCODE will be remove after v5.7, current is v5.5
//SELECT @@version;
//SELECT VERSION();
  //$sql = "INSERT INTO $table SET Email=?, Password=ENCODE(?,?), Contact=?, Mobile=?, Company_english=?, Company_chinese=?, Address=?"; //1~8
  $sql = "INSERT INTO $table SET Email=:email, Password=ENCODE(:password,:key), Contact=:contact, Mobile=:mobile, Company_english=:company_english, Company_chinese=:company_chinese, Address=:address";
  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(':email',$service['email'],PDO::PARAM_STR);
  $stmt->bindParam(':password',$service["password"],PDO::PARAM_STR);
  $stmt->bindParam(':key',substr($service["email"],0,5),PDO::PARAM_STR);
  $stmt->bindParam(':contact',$service['contact'],PDO::PARAM_STR);
  $stmt->bindParam(':mobile',$service['mobile'],PDO::PARAM_STR);
  $stmt->bindParam(':company_english',$service['company_english'],PDO::PARAM_STR);
  $stmt->bindParam(':company_chinese',$service['company_chinese'],PDO::PARAM_STR);
  $stmt->bindParam(':address',$service['address'],PDO::PARAM_STR);
  $b = false;
  try {
    $stmt->execute();
    $b = true;
  }catch(Exception $e){
  }
  return $b;
}

if($_REQUEST["step"] == "apply")
{
  validStr("hidden_num",$_REQUEST["hidden_num"]);
  //validStr("",$_REQUEST["hidden_num"])
  if($_REQUEST["hidden_num"] == $_REQUEST["check_num"])
  {
    //jinho for SQL injection
    validStr("email",$_REQUEST["Aemail"]);
    validStr("password",$_REQUEST["Apassword"]);
    //validStr("contact",$_REQUEST["contact"]);
    //validStr("mobile",$_REQUEST["mobile"]);
    validStr("",$_REQUEST["contact"]);
    validStr("",$_REQUEST["mobile"]);
    validStr("",$_REQUEST["company_english"]);
    validStr("",$_REQUEST["company_chinese"]);
    validStr("",$_REQUEST["address"]);

    $_REQUEST["email"] = sqlInjectionFilter($_REQUEST["Aemail"]);
    $_REQUEST["password"] = sqlInjectionFilter($_REQUEST["Apassword"]);
    $_REQUEST["company_chinese"] = sqlInjectionFilter($_REQUEST["company_chinese"]);
    $_REQUEST["company_english"] = sqlInjectionFilter($_REQUEST["company_english"]);
    $_REQUEST["address"] = sqlInjectionFilter($_REQUEST["address"]);
    $_REQUEST["mobile"] = sqlInjectionFilter($_REQUEST["mobile"]);
  
    $sql="select * from qlync.account where Email =:email";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':email', $_REQUEST["email"],PDO::PARAM_STR); 
    $stmt->execute();
    //$db=$stmt->fetch(PDO::FETCH_ASSOC); //get one row
     $db = $stmt->fetchAll();
    if($stmt->rowCount()==0)
    {
      if (addAdminPDO($_REQUEST))    
        echo "Apply Email ".$_REQUEST["email"].". Please Notify Administrator for Approval<BR>\n";
      else echo "Fail to Apply ".$_REQUEST["email"];
    }else{
        echo "Apply Email ".$_REQUEST["email"].". Fail! Duplicate Email Found.<BR>\n";
    }
  }//hidden num
  else
  {
    echo "Apply Check Number Error, Please Try again!<BR>\n";
    //exit();
  }
}else if($_REQUEST["step"]=="login")
{//jinho for SQL injection
  validStr("email",$_REQUEST["email"]);
  validStr("password",$_REQUEST["password"]);
  $_REQUEST["email"] = sqlInjectionFilter($_REQUEST["email"]);
  $_REQUEST["password"] = sqlInjectionFilter($_REQUEST["password"]);
  //$sql="select * from qlync.account where Email =? and DECODE(Password,'".substr($_REQUEST["email"],0,5)."')=?";
  $sql="select * from qlync.account where Email =:email and DECODE(Password,:key)=:password";
  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(':email', $_REQUEST["email"],PDO::PARAM_STR); 
  $stmt->bindParam(':key', substr($_REQUEST["email"],0,5),PDO::PARAM_STR);
  $stmt->bindParam(':password', $_REQUEST["password"],PDO::PARAM_STR);  
  $stmt->execute();
  $db=$stmt->fetch(PDO::FETCH_ASSOC); //get one row

  if($stmt->rowCount()==1) 
  {
    //fetch($db,$result,0);
    $_SESSION["ID_qlync"]     = $db["ID"];
    $_SESSION["Status_qlync"] = $db["Status"];
    $_SESSION["ID_webmaster_qlync"] = $db["ID_webmaster"];
    $_SESSION["ID_admin_qlync"]     = $db["ID_admin"];
    $_SESSION["ID_admin_oem_qlync"] = $db["ID_admin_oem"];
    $_SESSION["ID_pm_oem_qlync"]    = $db["ID_pm_oem"]; // purchase man
    $_SESSION["ID_qlync_pm_qlync"]  = $db["ID_qlync_pm"];
    $_SESSION["ID_qlync_rd_qlync"]  = $db["ID_qlync_rd"];
    $_SESSION["ID_qlync_fae_qlync"] = $db["ID_qlync_fae"];
    $_SESSION["ID_qlync_qa_qlync"]  = $db["ID_qlync_qa"];
    $_SESSION["ID_qlync_admin_qlync"] =$db["ID_qlync_admin"]; // qlync administrator people
    $_SESSION["ID_fae_qlync"] = $db["ID_fae"];
    $_SESSION["CID"]                = $db["CID"];
    $_SESSION["Contact"]    = $db["Contact"];
    $_SESSION["SCID"]   = $db["SCID"];

    $sql="select Name from qlync.scid where SCID='{$db["SCID"]}'";
    sql($sql,$result_scid,$num_scid,0);
    fetch($db_scid,$result_scid,0,0);

    $_SESSION["SCID_name"]    = $db_scid["Name"];
    $_SESSION["RID"]['01']    = $db["ID_01"];
    $_SESSION["RID"]['02']          = $db["ID_02"];
    $_SESSION["RID"]['03']          = $db["ID_03"];
    $_SESSION["RID"]['04']          = $db["ID_04"];
    $_SESSION["RID"]['05']          = $db["ID_05"];
    $_SESSION["RID"]['06']          = $db["ID_06"];
    $_SESSION["RID"]['09']    = $db["ID_09"];

    $_SESSION["ID_01_qlync"]          = $db["ID_01"];
    $_SESSION["ID_02_qlync"]          = $db["ID_02"];
    $_SESSION["ID_03_qlync"]          = $db["ID_03"];
    $_SESSION["ID_04_qlync"]          = $db["ID_04"];
    $_SESSION["ID_05_qlync"]          = $db["ID_05"];
    $_SESSION["ID_06_qlync"]          = $db["ID_06"];
    $_SESSION["ID_09_qlync"]          = $db["ID_09"];
    $_SESSION["AID"]    = $db["AID"]+0;


    $_SESSION["Email"]              = $db["Email"];
    $_SESSION["Mobile"]   = $db["Mobile"];
    $_SESSION["login_err"]    = 0;

    $sql="insert into qlync.login_log (Account, Date) values( '".$_REQUEST["email"]."','".date("Y-m-d H:i:s")."')";
    sql($sql,$result,$num,0);

    $sql="select Content from qlync.oem_info where Cat2='Application_ID'";
    sql($sql,$result_aid,$num_aid,0);
    fetch($db_aid,$result_aid,0,0);
    if(substr($db["GID"],0,3)=="000"){
      $_SESSION["AID"]=$db_aid["Content"];
    }else{
      $_SESSION["AID"]=substr($db["GID"],0,3);
    }
    if (!$_SERVER['HTTPS']) //jinho
      header( 'Location: http://' . $_SERVER['HTTP_HOST'] );
    else //jinho
      header("Location:".$home_url);
      //header("Location:".str_replace("/:",":",$home_url));

  }else{
    $_SESSION["login_err"]++;
    $_SESSION['timeout'] = time(); //jinho added for cleanup
  }
}else if($_REQUEST["step"]=="logout")
{
    session_destroy();
    $_SESSION["CID"]   = "";
    $_SESSION["Email"]="";
    $_SESSION["ID_qlync"]="";
    $_SESSION["Status_qlync"]="";
    $_SESSION["ID_webmaster_qlync"]="";
    $_SESSION["ID_admin_qlync"]="";
    $_SESSION["ID_admin_oem_qlync"]="";
    $_SESSION["ID_qlync_fae_qlync"]="";
    $_SESSION["ID_qlync_rd_qlync"]="";
    $_SESSION["ID_qlync_qa_qlync"]="";
    $_SESSION["ID_qlync_admin_qlync"]="";
if (!$_SERVER['HTTPS']) //jinho
  header( 'Location: http://' . $_SERVER['HTTP_HOST'] );
else{//jinho    
    header("Location:".str_replace("/:",":",$home_url));
    exit();
    }

}

#######################
#Status Section
#######################
echo "<div class=shadow_0>";
echo "<div class=shadow_1></div>";
echo "</div>";
echo "<div class=container>";
echo "<div class=bg_top></div>";
echo "<div class=bg_mid>";
echo "<div class=login_left>";
echo "<div class=login_left_con >";
echo "<div class=partner_topic>".gettext("Please Log-in here")."</div>";
echo "<div class=line></div>";
#######################
#Login Section
#######################
//jinho added timeout cleanup
if (!isset($_SESSION['timeout'])) $_SESSION['timeout'] = 0;
if ($_SESSION['timeout'] + 15 * 60 < time()) $_SESSION["login_err"] = 0;//over 15 mins
if($_SESSION["ID_qlync"] == "")
{
    if($_SESSION["login_err"] >0)
    {
      echo "<font color=#FF0000>You have failed to login {$_SESSION["login_err"]} times.<BR>Only ".(5-$_SESSION["login_err"])." times left!<BR></font>";
    }
    if($_SESSION["login_err"]>=5){
      echo "Please try again in 15 min later!!\n";
    }else{
      echo "<form method=post action=\"".$_SERVER['PHP_SELF']."\">";
      echo "<div class=partner_ques>".gettext("Email")."</div>";
      echo "<input class=input_1 type=text name=email  AUTOCOMPLETE=\"OFF\">";
      echo "<div class=partner_ques>".gettext("Password")."</div>";
      echo "<input  class=input_1 type=password name=password  AUTOCOMPLETE=\"OFF\">";
      echo "<input class=btn_1 type=submit value='".gettext("Send")."'>";
      echo "<input type=hidden name=step value=login>";
      echo "<div class=forget>";
      echo "</div>";
      
      echo "</form>";
    }
}else{                
#######################
#Logout Section
#######################
//if($_SESSION["ID_qlync"] <> "")
//{
  echo "<form method=post action=\"logout.php\">\n";
  echo "<input type=hidden name=step value=logout>\n";
  echo "<input class=btn_1  type=submit value='".gettext("Log out")."'>\n";
  echo "</form>";

}
#######################
#Status Notification Section
#######################
/*
if($_SESSION["Status_qlync"] <> "9")
{
          echo "<div class=forget>";
      if($_SESSION["Status_qlync"] == "9")
        echo "<a herf=#>Account Apply</a>";
      if($_SESSION["Status_qlync"] =="0")
        echo "<a herf=#>Waiting Approved</a>";
      if($_SESSION["Status_qlync"] >"1")
        echo "<a herf=#>Under Evaluation</a>";
      if($_SESSION["Status_qlync"] =="1")
        echo "<a herf=#>Approved</a>";
        echo "</div>";

}
*/
echo "</div>";
echo "</div>";
###########end left part
############start right part for join

//if (($oem == "T04") OR ($oem == "T05") or ($oem == "K01")) //RPIC check
if ($oem != "X02")
  if ($_SESSION["ID_qlync"] <> ""){ //jinho added to apply account after login
    printApplyForm();
  }else echo "</div></div></div></div>";
else printApplyForm();
function printApplyForm(){
echo "<form action=\"".$_SERVER['PHP_SELF']."\" method=post>\n";
echo "<div class=login_right>";
echo "<div class=login_right_con>";
echo "<div class=nonpartner_topic>".gettext("Sign up to be partner?")."</div>";
echo "<div class=nonpartner_box>";
echo "<table class=table_login>";
echo "<tr>";
echo "<td>".gettext("Company English name")."</td>";
echo "<td><input class=input_1 type=text name=company_english AUTOCOMPLETE=\"OFF\">";
echo "</tr>";
echo "<tr>";
//echo "<td>".gettext("Company Chinese name")."</td>";
echo "<td>公司名稱</td>"; //jinho fix chinese
echo "<td><input class=input_1 type=text name=company_chinese AUTOCOMPLETE=\"OFF\">";
echo "</tr>";
//jinho remove duplicate
//echo "<input type=hidden name=company_chinese >";
echo "<tr>";
echo "<td>".gettext("Email")."</td>";
echo "<td><input class=input_1 name=Aemail AUTOCOMPLETE=\"OFF\">";
echo "</tr>";
echo "<tr>";
echo "<td>".gettext("Password")."</td>";
echo "<td><input type=password class=input_1 name=Apassword AUTOCOMPLETE=\"OFF\">";
echo "</tr>";
echo "<tr>";
echo "<td>".gettext("Account Name")."</td>";
echo "<td><input class=input_1 name=contact AUTOCOMPLETE=\"OFF\">";
echo "</tr>";
echo "<tr>";
echo "<td>".gettext("Address of the company")."</td>";
echo "<td><input class=input_1 name=address AUTOCOMPLETE=\"OFF\">";
echo "</tr>";
echo "<tr>";
echo "<td>".gettext("Contact phone number")." </td>";
echo " <td><input class=input_1 name=mobile  AUTOCOMPLETE=\"OFF\">";
echo "</tr>";
echo "<tr>\n";
$chk_num=date("is");    
echo "<input type=hidden name=hidden_num value={$chk_num}>\n";
echo "<td>".gettext("Check number")." [{$chk_num}]</td>\n";
echo "<td><input class=input_1 name=check_num placeholder='".gettext("Please enter 4 digits number")."'></td>\n";
echo "</tr>\n";
echo "</table>";
echo "</div>";
echo "<input class=btn_2 type=submit value='".gettext("Join")."'></div>";
echo "<input type=hidden name=step value=apply>\n";
echo "</form>";
echo " </div>";
echo " </div>";
echo "  <div class=clear></div>";
echo "</div>";
echo "<div class=bg_btm></div>";
echo "</div>"; //jinho fix menu.php missing div
echo "</div>"; //container
}


echo "</body>";
echo "</html>";
  $pdo=null; //jinho free pdo resource
?>