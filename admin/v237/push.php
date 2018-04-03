<?
include("../../../header.php");
include("../../../menu.php");
//include("../common/ip.php");
//include("../common/country.php");
#Authentication Section
$sql="select * from qlync.menu where Name = 'Push Notification'";
sql($sql,$result,$num,0);
fetch($db,$result,0,0);
$sql="select * from qlync.right_tree where Cfun='{$db["ID"]}' and `Right` = 1";
sql($sql,$result,$num,0);
$right=0;
$oem_id="";
for($i=0;$i<$num;$i++)
{
fetch($db,$result,$i,0);
if($_SESSION["{$db["Fright"]}_qlync"] ==1)
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
if($right  == "0")
exit();
############  Authentication Section Enda
if($_REQUEST["push_type"]<> "")
{
$_SESSION["push_type"]=$_REQUEST["push_type"];
}


$oem_id=$_SESSION["CID"];
if($_SESSION["CID"]=="G01")
{
        $oem_id="N99";
}

$err_msg=array();
if($_REQUEST["step"]=="to_user" or $_REQUEST["step"]=="to_camera" or $_REQUEST["step"]=="to_all" or $_REQUEST["step"]=="to_model" or $_REQUEST["step"]=="to_version" or $_REQUEST["step"]=="to_group")
{
        //for log
        $sql="insert into qlync.push_log (Oem_id, Cmd, Msg, Time_s1,Email) values ('{$_SESSION["CID"]}','{$_REQUEST["step"]}','".urlencode($_REQUEST["msg"])."','".date("Ymd H:i:s")."','{$_SESSION["Email"]}')";
        sql($sql,$result_tmp,$num_tmp,0);

        if($_REQUEST["agree"]=="on")  // check if agree
        {

                //check the oem is and the account has the camera or not.
                if($_SESSION["CID"]=="N99")
                {

                        switch($_REQUEST["step"])
                        {
                        case("to_user"):

                                $sql="select count(ID) as c from qlync.account_device where Name='{$_REQUEST["user_name"]}'";
                                break;
			case("to_group"):
                                if(strlen(str_replace("-","",str_replace(":","",$_REQUEST["mac"])))==12)
                                {
                                        $sql="select count(ID) as c,Name from qlync.account_device where Mac='".strtoupper(str_replace("-","",str_replace(":","",$_REQUEST["mac"])))."' limit 0,1";
                                }
                                else
                                {
                                        $err_msg[]="<H2><font color=red>The Mac is not with 12 character</font></H2>\n";
                                }

				break;
                        case("to_camera"):
                                if(strlen(str_replace("-","",str_replace(":","",$_REQUEST["mac"])))==12)
                                {
                                        $sql="select count(ID) as c,Name from qlync.account_device where Mac='".strtoupper(str_replace("-","",str_replace(":","",$_REQUEST["mac"])))."' limit 0,1";
                                }
                                else
                                {
                                        $err_msg[]="<H2><font color=red>The Mac is not with 12 character</font></H2>\n";
                                }
                                break;
                        case("to_all"):
                                        $sql="select count(ID) as c from qlync.account_device where Oem_id='{$_SESSION["CID"]}' limit 0,1";

                                break;
                        case("to_model"):
                                        if($_REQUEST["oem_id"] <> "")
                                        {
                                                $sql="select * from qlync.account_device where Oem_id='{$_REQUEST["oem_id"]}' and Model='{$_REQUEST["model"]}' group by Name";
                                                sql($sql,$result,$num,0);
                                                for($i=0;$i<$num;$i++)
                                                {
                                                        fetch($db_tmp,$result,$i,0);
                                                        $tmp[]="('{$db_tmp["Name"]}','{$_REQUEST["oem_id"]}','".urlencode($_REQUEST["msg"])."','".date("Ymd H:i:s")."')";

                                                }
                                                $tmp_queue=implode(",",$tmp);
                                                $sql="insert into qlync.push_queue  (Name,Oem_id,Msg,Time_s1) values {$tmp_queue}";
                                                sql($sql,$result,$num,0);
                                                $err_msg[]="<H2><font color=Green>The select users has waiting in the queue to send message successfully!</font></H2>\n";
                                                $sql="select count(ID) as c from qlync.account_device where Oem_id='{$_REQUEST["oem_id"]}' and Model='{$_REQUEST["model"]}' group by Name limit 0,1";
                                        }
                                        else
                                        {
                                                $err_msg[]="<H2><font color=red>Please select the OEM ID to Send!</font></H2>\n";
                                        }
                                break;
                        case("to_version"):
                                        if($_REQUEST["oem_id"] <> "")
                                        {
                                                $sql="select * from qlync.account_device where Oem_id='{$_REQUEST["oem_id"]}' and Model='{$_REQUEST["model"]}' and Fw='{$_REQUEST["version"]}' group by Name";
                                                sql($sql,$result,$num,0);
                                                for($i=0;$i<$num;$i++)
                                                {
                                                        fetch($db_tmp,$result,$i,0);
                                                        $tmp[]="('{$db_tmp["Name"]}','{$_REQUEST["oem_id"]}','".urlencode($_REQUEST["msg"])."','".date("Ymd H:i:s")."')";

                                                }
                                                $tmp_queue=implode(",",$tmp);
                                                $sql="insert into qlync.push_queue  (Name,Oem_id,Msg,Time_s1) values {$tmp_queue}";
                                                sql($sql,$result,$num,0);
                                                $err_msg[]="<H2><font color=Green>The select users has waiting in the queue to send message successfully!</font></H2>\n";
                                                $sql="select count(ID) as c from qlync.account_device where Oem_id='{$_REQUEST["oem_id"]}' and Model='{$_REQUEST["model"]}' group by Name limit 0,1";
                                        }
                                        else
                                        {
                                                $err_msg[]="<H2><font color=red>Please select the OEM ID to Send!</font></H2>\n";
                                        }
                                break;


                }

        }
        else
        {
                switch($_REQUEST["step"])
                {
                        case("to_user"):
                                $sql="select count(ID) as c from qlync.account_device where Name='{$_REQUEST["user_name"]}'";
                                break;

                        case("to_camera"):
                                if(strlen(str_replace("-","",str_replace(":"."",$_REQUEST["mac"])))==12)
                                {
                                        $sql="select count(ID) as c,Name from qlync.account_device where Oem_id='{$_SESSION["CID"]}' and Mac='".strtoupper(str_replace("-","",str_replace(":","",$_REQUEST["mac"])))."' limit 0,1";
                                }
                                else
                                {
                                        $err_msg[]="<H2><font color=red>The Mac is not with 12 character</font></H2>\n";
                                }

                                break;
			case("to_group"):
                                if(strlen(str_replace("-","",str_replace(":","",$_REQUEST["mac"])))==12)
                                {
                                        $sql="select count(ID) as c,Name from qlync.account_device where Mac='".strtoupper(str_replace("-","",str_replace(":","",$_REQUEST["mac"])))."' limit 0,1";
                                }
                                else
                                {
                                        $err_msg[]="<H2><font color=red>The Mac is not with 12 character</font></H2>\n";
                                }

				break;
                        case("to_model"):
                                                $sql="select * from qlync.account_device where Oem_id='{$_SESSION["CID"]}' and Model='{$_REQUEST["model"]}' group by Name";
                                                sql($sql,$result,$num,0);
                                                for($i=0;$i<$num;$i++)
                                                {
                                                        fetch($db_tmp,$result,$i,0);
                                                        $tmp[]="('{$db_tmp["Name"]}','{$_SESSION["CID"]}','".urlencode($_REQUEST["msg"])."','".date("Ymd H:i:s")."')";

                                                }
                                                $tmp_queue=implode(",",$tmp);
                                                $sql="insert into qlync.push_queue  (Name,Oem_id,Msg,Time_s1) values {$tmp_queue}";
                                                sql($sql,$result,$num,0);
                                                $err_msg[]="<H2><font color=Green>The select users has waiting in the queue to send message successfully!</font></H2>\n";

                                                $sql="select count(ID) as c from qlync.account_device where Oem_id='{$_SESSION["CID"]}' and Model='{$_REQUEST["model"]}' group by Name limit 0,1";
                                break;
                        case("to_version"):
                                                $sql="select * from qlync.account_device where Oem_id='{$_SESSION["CID"]}' and Model='{$_REQUEST["model"]}' and Fw='{$_REQUEST["version"]}' group by Name";
                                                sql($sql,$result,$num,0);
                                                for($i=0;$i<$num;$i++)
                                                {
                                                        fetch($db_tmp,$result,$i,0);
                                                        $tmp[]="('{$db_tmp["Name"]}','{$_SESSION["CID"]}','".urlencode($_REQUEST["msg"])."','".date("Ymd H:i:s")."')";

                                                }
                                                $tmp_queue=implode(",",$tmp);
                                                $sql="insert into qlync.push_queue  (Name,Oem_id,Msg,Time_s1) values {$tmp_queue}";
                                                sql($sql,$result,$num,0);
                                                $err_msg[]="<H2><font color=Green>The select users has waiting in the queue to send message successfully!</font></H2>\n";

                                                $sql="select count(ID) as c from qlync.account_device where Oem_id='{$_SESSION["CID"]}' and Model='{$_REQUEST["model"]}' group by Name limit 0,1";
                                break;


                        case("to_all"):
                                        $sql="select count(ID) as c from qlync.account_device where Oem_id='{$_SESSION["CID"]}' limit 0,1";
                                break;
	                case("to_group"):
	// notification for shar user
	                        $sql_tmp="select device_share.uid, device_share.visitor_id,user.id,user.name from isat.device_share,isat.user where device_share.uid like '%".str_replace("-","",str_replace(":","",$_REQUEST["mac"]))."' and device_share.visitor_id=user.id";
	                        sql($sql_tmp,$result_tmp,$num_tmp,0);
	                        for($k=0;$k<$num_tmp;$k++)
	                        {
	                                fetch($db_tmp,$result_tmp,$k,0);
	                                $import_target_url ="https://{$api_id}:{$api_pwd}@{$t[0]}/push_notify/push.php?cmd=SMSG&user={$db_tmp["name"]}&msg=".urlencode($_REQUEST["msg"])."";
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
	                        //      echo $import_target_url;
	                              //print_r($content);
	                                if($content["status"]=="success")
	                                {
	                                        $err_msg_group[]=" <H2>Successfully Send to {$db_tmp["name"]}!</H2>";
	                                }
	
	
	
	                        }
	
	// notification for owner
	                         $import_target_url ="https://{$api_id}:{$api_pwd}@{$t[0]}/push_notify/push.php?cmd=SMSG&user={$db["Name"]}&msg=".urlencode($_REQUEST["msg"])."";
	                        break;

                }
        }
        sql($sql,$result,$num,0);
        fetch($db,$result,0,0);
	$t=explode("/",$api_path);

        switch($_REQUEST["step"])
        {
                case("to_user"):
                        $import_target_url ="https://{$api_id}:{$api_pwd}@{$t[0]}/push_notify/push.php?cmd=SMSG&user={$_REQUEST["user_name"]}&msg=".urlencode($_REQUEST["msg"])."";
                        break;
                case("to_camera"):
                         $import_target_url ="https://{$api_id}:{$api_pwd}@{$t[0]}/push_notify/push.php?cmd=SMSG&user={$db["Name"]}&msg=".urlencode($_REQUEST["msg"])."";

                        break;
                case("to_all"):

                        $import_target_url ="https://{$api_id}:{$api_pwd}@{$t[0]}/push_notify/push.php?cmd=OEMB&oemid={$_SESSION["CID"]}&service=camera&msg=".urlencode($_REQUEST["msg"])."";
                        break;
		case("to_group"):
// notification for shar user
			$sql_tmp="select device_share.uid, device_share.visitor_id,user.id,user.name from isat.device_share,isat.user where device_share.uid like '%".str_replace("-","",str_replace(":","",$_REQUEST["mac"]))."' and device_share.visitor_id=user.id";
			sql($sql_tmp,$result_tmp,$num_tmp,0);
			for($k=0;$k<$num_tmp;$k++)
			{
				fetch($db_tmp,$result_tmp,$k,0);
	                        $import_target_url ="https://{$api_id}:{$api_pwd}@{$t[0]}/push_notify/push.php?cmd=SMSG&user={$db_tmp["name"]}&msg=".urlencode($_REQUEST["msg"])."";
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
		        //      echo $import_target_url;
		              //print_r($content);
		                if($content["status"]=="success")
		                {
		                        $err_msg_group[]=" <H2>Successfully Send to {$db_tmp["name"]}!</H2>";
		                }



			}
			
// notification for owner
                         $import_target_url ="https://{$api_id}:{$api_pwd}@{$t[0]}/push_notify/push.php?cmd=SMSG&user={$db["Name"]}&msg=".urlencode($_REQUEST["msg"])."";
			break;

        }

// CURL Post
//echo $import_target_url;
        if($db["c"] >0 and sizeof($err_msg)==0)
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
	//	echo $import_target_url;
              //print_r($content);
                if($content["status"]=="success")
                {
                        $err_msg[]=" <H2>Successfully Send!</H2>";
			$err_msg=array_merge($err_msg,$err_msg_group);
                }
        }
        else
        {
                if($_REQUEST["step"] <> "to_model" or $_REQUEST["step"] <> "to_version")
                {
                        $err_msg[]="<H2><font color=red>Message Send Error!</font></H2>";
                        $err_msg[]="The possible problems are:";
                        $err_msg[]="1. This account is not available.";
                        $err_msg[]="2. This account has no camera binding.";
                        $err_msg[]="3. You have no right to access this user.";
                }
        }

}
else
{
        $err_msg[]="<H2><font color=red>Please click the confirmed check box for send message to user</font></H2>\n";
        //not agree clicked
}

}


echo "<BR><HR>\n";

echo "<input type=button class=btn_4 value='To a user' onclick='javascript:location.href=\"push.php?push_type=to_user\";'>\n";

echo "<input type=button class=btn_3 value='To a camera owner' onclick='javascript:location.href=\"push.php?push_type=to_camera\";'>\n";

//echo "<input type=button class=btn_3 value='To a specify model owner' onclick='javascript:location.href=\"push.php?push_type=to_model\";'>\n";

//echo "<input type=button class=btn_3 value='To a specify version owner' onclick='javascript:location.href=\"push.php?push_type=to_version\";'>\n";

echo "<input type=button class=btn_3 value='To all your customer' onclick='javascript:location.href=\"push.php?push_type=to_all\";'>\n";

echo "<input type=button class=btn_3 value='To specified camera group' onclick='javascript:location.href=\"push.php?push_type=to_group\";'>\n";

echo "<HR>\n";
foreach($err_msg as $value)
{
echo $value."<BR>";
}

switch($_SESSION["push_type"])
{
case("to_user"):
        include("push_user.php");
        break;
case("to_camera");
        include("push_camera.php");
        break;
case("to_model");
        include("push_model.php");
        break;
case("to_version");
        include("push_version.php");
        break;
case("to_all");
        include("push_all.php");
        break;
case("to_group");
	include("push_group.php");
	break;
default:
        echo "Please select a type.";
        break;
}


?>
 
