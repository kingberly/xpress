<?php
/****************
 *Validated on Sep-12,2017,
 * Add SQL Injection protection by PDO object
 * fix $var_req parsing variable issue
 * Add Insert sql by PDO object
 * Add warning message, Fix bug             
 *Writer: JinHo, Chang
*****************/
include_once("../../header.php");
include_once("../../menu.php");
/////APO SQL update
  $ref=exec("grep utf8 /var/www/qlync_admin/doc/mysql_connect.php");//correct
  if ($ref=="")//pre v3.2.1 vesion
    $pdo = new PDO('mysql:host='.$mysql_ip, $mysql_id, $mysql_pwd);
  else//correct utf8 
  $pdo = new PDO('mysql:host='.$mysql_ip, $mysql_id, $mysql_pwd,
      array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
  $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
  $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false); //must add 
function updateAdminPDO($service)
{
  global $pdo;
  $table = 'qlync.account';
  $sql = "UPDATE $table SET Password=ENCODE(:password,:key), Contact=:contact, Mobile=:mobile, Company_english=:company_english, Address=:address WHERE Email=:email"; //, Company_chinese=:company_chinese
  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(':password',$service["password"],PDO::PARAM_STR);
  $stmt->bindParam(':key',substr($service["email"],0,5),PDO::PARAM_STR);
  $stmt->bindParam(':contact',$service['contact'],PDO::PARAM_STR);
  $stmt->bindParam(':mobile',$service['mobile'],PDO::PARAM_STR);
  $stmt->bindParam(':company_english',$service['company_english'],PDO::PARAM_STR);
  //$stmt->bindParam(':company_chinese',$service['company_chinese'],PDO::PARAM_STR);
  $stmt->bindParam(':address',$service['address'],PDO::PARAM_STR);
  $stmt->bindParam(':email',$service['email'],PDO::PARAM_STR);
  $b = false;
  try {
    $stmt->execute();
    $b = true;
  }catch(Exception $e){
  }
  return $b;
}
#Apply Partner
if($_REQUEST["step"] == "apply")
{/*
  $sql="select * from qlync.account where Email = '{$_REQUEST["email"]}' ";
  sql($sql,$result,$num,0);
  if($num ==1)
  {
    fetch($db,$result,0,0);
    $sql="update qlync.account set Company_english='".mysql_real_escape_string($_REQUEST["company_english"])."',Company_chinese='".mysql_real_escape_string($_REQUEST["company_chinese"])."',Email='".mysql_real_escape_string($_REQUEST["email"])."',";

    $sql=$sql." Password=ENCODE('".mysql_real_escape_string($_REQUEST["password"])."','".substr(mysql_real_escape_string($_REQUEST["email"]),0,5)."') , Contact='".mysql_real_escape_string($_REQUEST["contact"])."', Mobile='".mysql_real_escape_string($_REQUEST["mobile"])."', Address='".mysql_real_escape_string($_REQUEST["address"])."' where ID='{$db["ID"]}'";

    sql($sql,$result,$num,0); 
  }*/
  //jinho for SQL injection
  $sql="select * from qlync.account where Email =:email";
  $stmt = $pdo->prepare($sql);
  $stmt->execute(array('email' => $_REQUEST["email"]));
  if($stmt->rowCount()==1)
  {  
    if (!updateAdminPDO($_REQUEST)) echo "Fail to Update ".$_REQUEST["email"];
  }
}

#  Login Partner  //pass to login.php
/*
#  Login Partner
if($_REQUEST["step"]=="login")
{
  $sql="select * from qlync.account where Email = '{$_REQUEST["email"]}' and Password='{$_REQUEST["password"]}' ";
  sql($sql,$result,$num,0);
  if($num==1)
  {
    fetch($db,$result,0);

    $_SESSION["ID_qlync"]     = $db["ID"];
    $_SESSION["Status_qlync"] = $db["Status"];
    $_SESSION["ID_webmaster_qlync"] = $db["ID_webmaster"];
    $_SESSION["ID_admin_qlync"]     = $db["ID_admin"];
    $_SESSION["ID_admin_oem_qlync"] = $db["ID_admin_oem"];
    $sql="insert into qlync.login_log (Account, Date) values( '{$_REQUEST["email"]}','".date("Y-m-d H:i:s")."')";
    sql($sql,$result,$num,0);
    header('Location:http://partner.qlync.com/html/member/login.php');
  }
}



if($_REQUEST["step"]=="logout")
{


    $_SESSION["ID_qlync"]="";
    $_SESSION["Status_qlync"]="";
    $_SESSION["ID_webmaster_qlync"]="";
    $_SESSION["ID_admin_qlync"]="";
    $_SESSION["ID_admin_oem_qlync"]="";
    
    header('Location:http://partner.qlync.com/html/member/login.php');
    exit();

}
*/
#######################
#Status Section
#######################
echo "<div class=shadow_0>";
echo "<div class=shadow_1></div>";
echo "</div>";
echo "<div class=container>";
//    echo "<div class=partnertitle>Partner login";
//    echo "</div>";
echo "<div class=bg_top></div>";
echo "<div class=bg_mid>";
echo "<div class=login_left>";
echo "<div class=login_left_con >";
echo "<div class=partner_topic>"._("Partner Already")." </div>";
//          echo "<div class=login_topic>Please log in here.</div>";
echo "<div class=line></div>";
#######################
#Login Section
#######################
if($_SESSION["ID_qlync"] == "")
{////update from login.php
if($_SESSION["login_err"] >0)
{
  echo "<font color=#FF0000>You have failed to login {$_SESSION["login_err"]} times.<BR>Only ".(5-$_SESSION["login_err"])." times left!<BR></font>";
}
if($_SESSION["login_err"]>=5)
{
  echo "Please try again in 15 min later!!\n";
}
else
{
  echo "<form method=post action=login.php>";
  echo "<div class=partner_ques>Email</div>";
  echo "<input class=input_1 type=txt name=email>";
  echo "<div class=partner_ques>Password</div>";
  echo "<input  class=input_1 type=password name=password>";
  echo "<input class=btn_1 type=submit value=send>";
  echo "<input type=hidden name=step value=login>";
  echo "<div class=forget>";
  echo "<a herf=#>Forgot Password</a>";
  echo "</div>";
  echo "</from>";
}
} //ID_qlync==""               
#######################
#Logout Section
#######################
//if($_SESSION["ID_qlync"] <> "")
else
{
  echo "<form method=post action=logout.php>\n";
    echo "<input type=hidden name=step value=logout>\n";
    echo "<input class=btn_1  type=submit value='"._("Log out")."'>\n";
  echo "</form>";

}

echo "</div>";
echo "</div>";
###########end left part

############start right part for join
if($_SESSION["ID_qlync"] <> "")
{
  $sql="select *,DECODE(Password,left(Email,5)) as dec_pass from qlync.account where ID='{$_SESSION["ID_qlync"]}'";
  sql($sql,$result,$num,0);
  fetch($db,$result,0,0);
  
echo "<form action=maintain.php method=post>\n";
echo "<div class=login_right>";
echo "<div class=login_right_con>";
echo "<div class=nonpartner_topic>"._("Maintain Your Partner Info.")."</div>";
echo "<div class=login_topic>"._("Please modified below infomation with us now!")."</div>";
echo "<div class=nonpartner_box>";
echo "<table class=table_login>";
echo "<tr>";
echo "<td>".gettext("Company Name")."</td>";
echo "<td><input class=input_1 type=text name=company_english value='{$db["Company_english"]}'  maxlength='32'></td>";
echo "</tr>";
//echo "<tr>";
//echo "<td>".gettext("Company Chinese Name")."</td>";
//echo "<td><input class=input_1 type=text name=company_chinese value='{$db["Company_chinese"]}' maxlength='32'></td>";
                  echo "<input type=hidden name=company_chinese value={$db["Company_chinese"]} >";
//                        echo "</tr>";
                        echo "<tr>";
                          echo "<td>"._("Email")."</td>";
                            echo "<td>{$db["Email"]}";
                            echo "<input type=hidden name=email value={$db["Email"]} ></td>";
                        echo "</tr>";
                        echo "<tr>";
                          echo "<td>"._("Password")."</td>";
                            echo "<td><input type=password class=input_1 name=password value={$db["dec_pass"]}></td>";
                        echo "</tr>";
                        echo "<tr>";
                          echo "<td>"._("Contact person")."</td>";
                            echo "<td><input class=input_1 name=contact value={$db["Contact"]}></td>";
                        echo "</tr>";
                        echo "<tr>";
                          echo "<td>"._("Address")."</td>";
                            echo "<td><input class=input_1 name=address value='{$db["Address"]}'  maxlength='64'></td>";
                        echo "</tr>";
                        echo "<tr>";
                          echo "<td>"._("Mobile no.")."</td>";
                           echo " <td><input class=input_1 name=mobile value='{$db["Mobile"]}'  maxlength='16'></td>";
                        echo "</tr>";
                    echo "</table>";
                echo "</div>";
                echo "<input class=btn_2 type=submit value='"._("Update")."' ></div>";
                echo "<input type=hidden name=step value=apply>\n";
                echo "</form>";
           echo " </div>";
       echo " </div>";
 }
echo "  <div class=clear></div>";
echo "</div>";
echo "<div class=bg_btm></div>";
echo "</div>";
echo "</body>";
echo "</html>";
?>
