<?php
/****************
 *Validated on Nov-25,2014,  
 * Revised from Qlync maintain php
 *Writer: JinHo, Chang   
*****************/
include_once("header.php");
include_once("menu.php");

#Apply Partner
if($_REQUEST["step"] == "apply")
{
	$sql="select * from account where Email = '{$_REQUEST["email"]}' ";
	sql($sql,$result,$num,0);
	if($num ==1)
	{
		myfetch($db,$result,0,0);
		$sql="update qlync.account set Company_english='".mysql_real_escape_string($_REQUEST["company_english"])."',Company_chinese='".mysql_real_escape_string($_REQUEST["company_chinese"])."',Email='".mysql_real_escape_string($_REQUEST["email"])."',";

		$sql=$sql." Password=ENCODE('".mysql_real_escape_string($_REQUEST["password"])."','".substr(mysql_real_escape_string($_REQUEST["email"]),0,5)."') , Contact='".mysql_real_escape_string($_REQUEST["contact"])."', Mobile='".mysql_real_escape_string($_REQUEST["mobile"])."', Address='".mysql_real_escape_string($_REQUEST["address"])."' where ID='{$db["ID"]}'";
		//sql($sql,$result,$num,0);
          $result=mysql_query($sql,$link);
          $num = mysql_num_rows($result);

	}
}

#  Login Partner
if($_REQUEST["step"]=="login")
{
	$sql="select * from account where Email = '{$_REQUEST["email"]}' and Password='{$_REQUEST["password"]}' ";
	//sql($sql,$result,$num,0);
     $result=mysql_query($sql,$link);
     $num = mysql_num_rows($result);

	if($num==1)
	{
		myfetch($db,$result,0);

		$_SESSION["ID_qlync"]     = $db["ID"];
		$_SESSION["Status_qlync"] = $db["Status"];
		$_SESSION["ID_webmaster_qlync"] = $db["ID_webmaster"];
		$_SESSION["ID_admin_qlync"]			= $db["ID_admin"];
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
		
		header('Location:login.php');
		exit();

}

#######################
#Status Section
#######################
echo "<div class=shadow_0>";
	echo "<div class=shadow_1></div>";
echo "</div>";
	echo "<div class=container>";
//		echo "<div class=partnertitle>Partner login";
//    echo "</div>";
    echo "<div class=bg_top></div>";
    echo "<div class=bg_mid>";
    	echo "<div class=login_left>";
        	echo "<div class=login_left_con >";
        	echo "<div class=partner_topic>Partner Already </div>";
//        	echo "<div class=login_topic>Please log in here.</div>";
                echo "<div class=line></div>";
#######################
#Login Section
#######################
					if($_SESSION["ID_qlync"] == "")
					{
  					echo "<form method=post action=login.php>";
 									echo "<div class=partner_ques>Email</div>";
 										echo "<input class=input_1 type=txt name=email>";
									echo "<div class=partner_ques>Password</div>";
									 	echo "<input  class=input_1 type=password name=password>";
									echo "<input class=btn_1 type=submit value=send>";
									echo "<input type=hidden name=step value=login>";
        					echo "</div>";

 						echo "</from>";
					}                
#######################
#Logout Section
#######################
					if($_SESSION["ID_qlync"] <> "")
					{
						echo "<form method=post action=logout.php>\n";
							echo "<input type=hidden name=step value=logout>\n";
							echo "<input class=btn_1  type=submit value=Logout>\n";
						echo "</form>";
	
					}

            echo "</div>";
        echo "</div>";
###########end left part

############start right part for join
if($_SESSION["ID_qlync"] <> "")
{
	$sql="select *,DECODE(Password,left(Email,5)) as dec_pass from qlync.account where ID='{$_SESSION["ID_qlync"]}'";
//	sql($sql,$result,$num,0);
//	fetch($db,$result,0,0);
     $result=mysql_query($sql,$link);
     $num = mysql_num_rows($result);
     myfetch($db,$result,0);
	
echo "<form action=maintain.php method=post>\n";
       echo "<div class=login_right>";
        	echo "<div class=login_right_con>";
            	echo "<div class=nonpartner_topic>Maintain Your partner Info.</div>";
                echo "<div class=login_topic>Please Modified below infomation with us now.</div>";
                echo "<div class=nonpartner_box>";
                	echo "<table class=table_login>";
                    	echo "<tr>";
                        	echo "<td>Company English name</td>";
                            echo "<td><input class=input_1 type=text name=company_english value={$db["Company_english"]} >";
                        echo "</tr>";
                        echo "<tr>";
                        	echo "<td>Company Chinese name</td>";
                            echo "<td><input class=input_1 name=company_chinese value={$db["Company_chinese"]} >";
                        echo "</tr>";
                        echo "<tr>";
                        	echo "<td>Email</td>";
                            echo "<td>{$db["Email"]}";
                            echo "<input type=hidden name=email value={$db["Email"]} >";
                        echo "</tr>";
                        echo "<tr>";
                        	echo "<td>Password</td>";
                            echo "<td><input class=input_1 name=password value={$db["dec_pass"]}>";
                        echo "</tr>";
                        echo "<tr>";
                        	echo "<td>Contact person</td>";
                            echo "<td><input class=input_1 name=contact value={$db["Contact"]}>";
                        echo "</tr>";
                        echo "<tr>";
                        	echo "<td>Address</td>";
                            echo "<td><input class=input_1 name=address value={$db["Address"]}>";
                        echo "</tr>";
                        echo "<tr>";
                        	echo "<td>Mobile no.</td>";
                           echo " <td><input class=input_1 name=Mobile value={$db["Mobile"]}>";
                        echo "</tr>";
                    echo "</table>";
                echo "</div>";
                echo "<input class=btn_2 type=submit value=Update ></div>";
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