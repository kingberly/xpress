<?
include("../../header.php");
include("../../menu.php");
#Authentication Section
$sql="select * from qlync.menu where Name = 'Push Info'";
sql($sql,$result,$num,0);
fetch($db,$result,0,0);
$sql="select * from qlync.right_tree where Cfun='{$db["ID"]}' and `Right` = 1";
sql($sql,$result,$num,0);
$right=0;
$oem_id="";
for($i=0;$i<$num;$i++)
{
        fetch($db,$result,$i,0);
        if($_SESSION["{$db["Fright"]}_qlync"] ==1 )
        {
                $right+=1;
                if($db["Oem"] == "0")
                {
                        $oem_id="N99";
                }
                if($db["Oem"] == "1" and $oem_id == "")
                {
                        $oem_id=$_SESSION["CID"];
                }
        }
}
//if($right  == "0")
  //      exit();
############  Authentication Section End
//jinho added for db utf8 fix
function myPDOQuery($sql){
  global $mysql_ip, $mysql_id, $mysql_pwd;
  $ref=exec("grep utf8 /var/www/qlync_admin/doc/mysql_connect.php");//correct
  if ($ref=="")//pre v3.2.1 vesion
    $pdo = new PDO('mysql:host='.$mysql_ip, $mysql_id, $mysql_pwd);
  else//correct utf8 
  $pdo = new PDO('mysql:host='.$mysql_ip, $mysql_id, $mysql_pwd,
			array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
  $qResult =$pdo->query($sql);
  $arr= $qResult->fetchAll(PDO::FETCH_ASSOC);
  return $arr;
}
//end of jinho 
if($_REQUEST["step"]=="to_camera" )
{
        //for log
        $sql="insert into qlync.push_log (Oem_id, Cmd, Msg, Time_s1,Email) values ('S{$_SESSION["SCID"]}','{$_REQUEST["step"]}','".urlencode($_REQUEST["msg"])."','".date("Ymd H:i:s")."','{$_SESSION["Email"]}')";
        sql($sql,$result_tmp,$num_tmp,0);

        if($_REQUEST["agree"]=="on")  // check if agree
        {

                //check the oem is and the account has the camera or not.
                if($_SESSION["SCID"]<>"")
                {

                        switch($_REQUEST["step"])
                        {
                        case("to_camera"):
                                if(strlen(str_replace("-","",str_replace(":","",$_REQUEST["mac"])))==12)
                                {
//                                        $sql="select count(ID) as c,Name from qlync.account_device where Mac='".strtoupper(str_replace("-","",str_replace(":","",$_REQUEST["mac"])))."' limit 0,1";
					$sql="select device_share.visitor_id,user.name from isat.device_share,isat.user where device_share.visitor_id=user.id and right(device_share.uid,12) = '{$_REQUEST["mac"]}' ";
                                }
                                break;
			}
		}
        	sql($sql,$result_name,$num,0);
        	$t=explode("/",$api_path);
		for($i=0;$i<$num;$i++)
		{
			fetch($db,$result_name,$i,0);
	               	$import_target_url ="https://{$api_id}:{$api_pwd}@{$t[0]}/push_notify/push.php?cmd=SMSG&user={$db["name"]}&msg=".urlencode($_REQUEST["msg"])."";

// CURL Post
//echo $import_target_url;
//	        if(sizeof($err_msg)==0)
	        {
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL,$import_target_url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,0);

                curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,0);

                $result=curl_exec($ch);
                curl_close($ch);


            // JSON Decoded Array to $content
                $content=array();
                $content=json_decode($result,true);
	//              print_r($content);
        	        if($content["status"]=="success")
	                {
	                        $err_msg[]=" <H2>Successfully Send! [{$db["name"]}]</H2>";
	                }
        	}
		}
        }
	else
	{
	        $err_msg[]="<H2><font color=red>Please click the confirmed check box for send message to user</font></H2>\n";
        //not agree clicked
	}

}

	
	

###############################################
$sql="select * from isat.query_info where user_name='{$_SESSION["Contact"]}' group by mac_addr order by mac_addr";

//sql($sql,$result_email,$num_email,0);
$db_emails=myPDOQuery($sql);$num_email=sizeof($db_emails);//jinho
for($i=0;$i<$num_email;$i++)
{
//        fetch($db_email,$result_email,$i,0);
        $db_email = $db_emails[$i];
        $cam[$i]["mac"]         = $db_email["mac_addr"];
        $cam[$i]["name"]        = $db_email["name"];
}
echo "<form action=push_info.php method=post>\n";
echo "<HR>\n";
echo "<H2>To a Specific Camera Owner</H2>\n";
echo "<table class=table_main>\n";
// for specific user
        echo "<tr class=topic_main>\n";
                echo "<td>Item</td>\n";
                echo "<td>Content</td>\n";
                echo "<td>Notice</td>\n";
        echo "</tr>\n";
        echo "<tr class=tr_2>\n";
                echo "<td bgcolor=black><font color=white>Camera Name</font></td>\n";
		echo "<td>\n";
			echo "<select name=mac>\n";
				foreach($cam as $key=>$value)
				{
					$chk="";
					if($value["mac"] == $_REQUEST["mac"])
						$chk="selected";

					echo "<option value='{$value["mac"]}' {$chk}>{$value["name"]}</option>\n";
				}
			echo "</select>\n";
		echo "</td>\n";
                echo "<td><font color=red size=2>\n";
                echo "1. The Camera Mac must be exist and has been bind by users<BR>\n";
                echo "2. The message onlly push to the one who has account binding with this account in mobile phone\n";
                echo "</font></td>\n";
        echo "</tr>\n";
        echo "<tr class=tr_2>\n";
                echo "<td bgcolor=black><font color=white> Message</font></td>\n";
                echo "<td>\n";
                        echo "<textarea rows=2 cols=33 name=msg style=\"resize:none\">\n";
                        echo $_REQUEST["msg"];
                        echo "</textarea>\n";
                echo "</td>\n";
                echo "<td><font color=red size=2>\n";
                echo "1. The messages is limited to 66 English character by mobile phone system<BR>\n";
                echo "2. The special charater can not be shown difference from mobiles<BR>\n";
                echo "3. The message is directly show to end user, and there is no multi-language support\n";
                echo "</font></td>\n";
        echo "</tr>\n";
        echo "<tr class=tr_2>\n";
                echo "<td bgcolor=black><font color=white>Submit</font></td>\n";
                echo "<td><input type=checkbox name=agree>Confirmed<BR><font color=blue size=1>(Confirmed means agree all the notice listed term)</font></td>\n";
                echo "<td><font color=red size=2>\n";
                echo "1. The message will submit right away to the end user, please use it carefully<BR>\n";
                echo "2. The Confirmed check has to click for agree that Qlync would not take any responsibility for the diliver message content to your end user customers<BR>\n";
                echo "<input type=hidden name=step value='to_camera'>\n";
                echo "<input class=btn_3 type=submit value=Send>\n";
                echo "</font></td>\n";
        echo "</tr>\n";
echo "</table>\n";
echo "</form>\n";

if(sizeof($err_msg) <> 0)
{
        foreach($err_msg as $key=>$value)
        {
                echo $value."<BR>";
        }
}
?>
