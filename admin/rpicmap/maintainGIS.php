<?php
include("/var/www/qlync_admin/doc/config.php");
include("/var/www/qlync_admin/doc/mysql_connect.php"); 
include("/var/www/qlync_admin/doc/sql.php");
require_once ("share.inc");
require_once ("rpic.inc");
#################translation section
$lan="zh_TW.UTF-8";
setlocale(LC_MESSAGES,  $lan);
setlocale(LC_NUMERIC, $lan);
setlocale(LC_TIME, $lan);
setlocale(LC_COLLATE, $lan);
setlocale(LC_MONETARY, $lan);
bindtextdomain("messages", "{$home_path}/locale");
bind_textdomain_codeset("messages", "UTF-8");
textdomain("messages");
$lan_tmp=explode(".",$lan);
putenv("LANG={$lan_tmp[0]}");
setlocale("LC_ALL","{$lan_tmp[0]}");
#################translation section end
header("Content-Type:text/html; charset=utf-8");

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
//ENCODE will be remove after v5.7, current is v5.5
//SELECT @@version;
//SELECT VERSION();
  //$sql = "INSERT INTO $table SET Email=?, Password=ENCODE(?,?), Contact=?, Mobile=?, Company_english=?, Company_chinese=?, Address=?"; //1~8
  $sql = "UPDATE $table SET Password=ENCODE(:password,:key), Contact=:contact, Mobile=:mobile, Company_english=:company_english, Company_chinese=:company_chinese, Address=:address";
  $stmt = $pdo->prepare($sql);
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
#Apply Partner
if($_REQUEST["step"] == "apply")
{
  //jinho for SQL injection
  $sql="select * from qlync.account where Email =:email";
  $stmt = $pdo->prepare($sql);
  $stmt->execute(array('email' => $_REQUEST["email"]));
  if($stmt->rowCount()==1)
	{	 
		if (!updateAdminPDO($_REQUEST)) $msgErr = "Fail to Update ".$_REQUEST["email"];
  }
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Maintain</title>
<?php
if (isMobile())
{
printMobileMeta();
}//detect user agent
?>
<meta http-equiv="Content-Language" content="utf-8">
<meta http-equiv="Content-Type" content="text/javascript; charset=utf-8">
<link href="/css/form.css" rel="stylesheet" type="text/css"  charset="utf-8" />
<link href="/css/nav.css" rel="stylesheet" type="text/css"  charset="utf-8" />
</head>
<body> 
<div class="container">
	<div class="header">
		<div class="logo"><img src="/images/logo_qlync.png" /></div>
    <div class="div_navi">
       	<span class="navi"></span>
            <span class="navi"></span>
            <span class="navi"></span> 
		</div>
        <div align="right">
<?php
if(!isset($_SESSION["ID_qlync"]))
	echo "<a href=\"installGIS.php\">".gettext("Log in")."</a>\n";
else{
	//echo "<a href=\"/html/member/logout.php?step=logout\">".gettext("Log out")."</a>\n";
	echo "<a href=\"javascript:var wobj=window.open('/html/member/logout.php?step=logout','print_popup','width=300,height=300');setTimeout(function(){location='installGIS.php';},500);setTimeout(function() { wobj.close(); }, 500);\">登出</a>\n";
}
?>
        </div> 
	</div>
</div>
<hr>
<div class="container">
<table><tr><td>
<?php
############start right part for join
if($_SESSION["ID_qlync"] != "")
{
  $sql="select *,DECODE(Password,left(Email,5)) as dec_pass from qlync.account where ID='{$_SESSION["ID_qlync"]}'";
  sql($sql,$result,$num,0);
  fetch($db,$result,0,0);
  
echo "<form action='".$_SERVER['PHP_SELF']."' method=post>\n";
echo "<div class=login_right>";
echo "<div class=login_right_con>";
echo "<div style='width:100%;height:47px; background-size: 100% 100%;background-image:url(/images/bg_title_r.png);background-repeat:no-repeat; font: bold 20px arial; color:#FFF; text-align:center;padding-top:8px'>"._("Maintain Your Partner Info.")."</div>";
echo "<div class=login_topic>"._("Please modified below infomation with us now!")."</div>";
echo "<div class=nonpartner_box>";
echo "<table class=table_login>";
echo "<tr>";
echo "<td>".gettext("Company English Name")."</td>";
echo "<td><input class=input_1 type=text name=company_english value='{$db["Company_english"]}'  maxlength='32'></td>";
echo "</tr>";
echo "<tr>";
echo "<td>".gettext("Company Chinese Name")."</td>";
echo "<td><input class=input_1 type=text name=company_chinese value='{$db["Company_chinese"]}' maxlength='32'></td>";
echo "</tr>";
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
echo "<td>"._("Account Name")."</td>";
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
echo "</div>";//login_right
}
?>
</div>
<div class=clear></div>
<div class=bg_btm></div>
</div>
</td></tr></table>
</body>
</html>