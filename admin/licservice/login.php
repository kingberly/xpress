<?php
/****************
 *Validated on Nov-25,2014,  
 * Admin login  revised from Qlync.
 * Apply only running as stand alone plugin  
 *Writer: JinHo, Chang   
*****************/
include_once("header.php");
include_once("menu.php");

$var_req["email"] = $_POST["email"];
$var_req["password"] = $_POST["password"];
if($_REQUEST["step"]=="login")
{
$sql="select * from licservice.account where Email = '".$var_req["email"]."' and DECODE(Password,'admin')='".$var_req["password"]."'";
//echo $sql;
  //$link=lic_getLink();
  //$result=mysql_query($sql,$link);
  sql($sql,$result,$num,0);
  //if ($result){
  //  $num = mysql_num_rows($result);

	if($num==1)
	{
		
		//myfetch($db,$result,0);
    fetch($db,$result,0,0);
		$_SESSION["ID_qlync"]     = $db["ID"];
		$_SESSION["Status_qlync"] = $db["Status"];
		$_SESSION["ID_webmaster_qlync"] = $db["ID_webmaster"];
		$_SESSION["ID_admin_qlync"]			= $db["ID_admin"];
		$_SESSION["ID_admin_oem_qlync"] = $db["ID_admin_oem"];
		$_SESSION["ID_pm_oem_qlync"]    = $db["ID_pm_oem"]; // purchase man
		$_SESSION["ID_qlync_pm_qlync"]	= $db["ID_qlync_pm"];
		$_SESSION["ID_qlync_rd_qlync"] 	= $db["ID_qlync_rd"];
		$_SESSION["ID_qlync_fae_qlync"] = $db["ID_qlync_fae"];
		$_SESSION["ID_qlync_qa_qlync"]	= $db["ID_qlync_qa"];
		$_SESSION["ID_qlync_admin_qlync"]	=$db["ID_qlync_admin"]; // qlync administrator people
		$_SESSION["ID_fae_qlync"]	= $db["ID_fae"];
		$_SESSION["CID"]                = $db["CID"];
		$_SESSION["Contact"]		= $db["Contact"];
		$_SESSION["Email"]							= $db["Email"];
		$_SESSION["login_err"]		= 0;

		header("Location:/plugin/licservice/login.php");

	}
	else
	{
		$_SESSION["login_err"]++;
	}
  }else echo "Login error/sql error!";
}



if($_REQUEST["step"]=="logout")
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
		
		header("Location:/plugin/licservice/login.php");
		exit();

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
        	echo "<div class=partner_topic>Please Log-in here</div>";
                echo "<div class=line></div>";
#######################
#Login Section
#######################
					if($_SESSION["ID_qlync"] == "")
					{

					if($_SESSION["login_err"] >0)
					{
						echo "<font color=#FF0000>You have failed to login {$_SESSION["login_err"]} times.<BR></font>";
					}
  					echo "<form method=post action=\"".$_SERVER['PHP_SELF']."\">";
 									echo "<div class=partner_ques>Email</div>";
 										echo "<input class=input_1 type=txt name=email  AUTOCOMPLETE=\"OFF\">";
									echo "<div class=partner_ques>Password</div>";
									 	echo "<input  class=input_1 type=password name=password  AUTOCOMPLETE=\"OFF\">";
									echo "<input class=btn_1 type=submit value=send>";
									echo "<input type=hidden name=step value=login>";
                	echo "<div class=forget>";
        					echo "</div>";

 						echo "</form>";
					}                
#######################
#Logout Section
#######################
					if($_SESSION["ID_qlync"] <> "")
					{
						echo "<form method=post action=\"logout.php\">\n";
							echo "<input type=hidden name=step value=logout>\n";
							echo "<input class=btn_1  type=submit value=Logout>\n";
						echo "</form>";
	
					}

            echo "</div>";
        echo "</div>";
###########end left part
      echo "  <div class=clear></div>";
    echo "</div>";
    echo "<div class=bg_btm></div>";
echo "</div>";

echo "</body>";
echo "</html>";
?>