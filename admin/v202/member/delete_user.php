<?
include("../../header.php");
include("../../menu.php");
#Authentication Section
$sql="select * from qlync.menu where Name = 'ADD / Delete End User'";
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
if($_REQUEST["step"]=="delete")
{
	$url="http://{$api_id}:{$api_pwd}@{$api_path}/manage_user.php?command=deletei\&reg_email={$_REQUEST["email"]}\&oem_id={$oem}";
 	$import_target_url ="http://{$api_id}:{$api_pwd}@{$api_path}/manage_user.php?command=delete&reg_email={$_REQUEST["email"]}&oem_id={$oem}";
    	$ch = curl_init();
    	curl_setopt($ch, CURLOPT_URL,$import_target_url);
    	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    	$result=curl_exec($ch);
    	curl_close($ch);

	// JSON Decoded Array to $content
    	$content=array();
    	$content=json_decode($result,true);
	echo "<br><HR>\n";
	if($content["status"]=="success")
	{
		$sql="delete from qlync.end_user_list where Email='".mysql_real_escape_string($_REQUEST["email"])."' and Oem_id='{$oem}'";
		sql($sql,$result_tmp,$num_tmp,0);
		echo "<h2>Delete Success</h2>\n";
	}
	else
	{
		echo "<h2> Delete failed, Please check if the email is correct or the email is valid in this system!</h2>\n";
	}

	
}

if($_REQUEST["step"]=="add")
{
  //jinho fix of QXP-294 
  if (!filter_var($_REQUEST["email"], FILTER_VALIDATE_EMAIL)) {
    echo "<h2><font color=red>Email format invalid</font></h2><HR>\n";
  }else if (!preg_match("/^[0-9a-zA-Z]{4,32}/",$_REQUEST["name"])) {
    echo "<h2><font color=red>User Name is 4 - 32 alphabet or numeric characters Only</font></h2><HR>\n";
  }else if (!preg_match("/^[0-9a-zA-Z]{4,32}/",$_REQUEST["pwd"])) {
    echo "<h2><font color=red>Password is 4 - 32 alphabet or numeric characters Only</font></h2><HR>\n";    
  }else{ //jinho fix else
	$web_address = "http://{$api_id}:{$api_pwd}@{$api_path}";
	$path='/manage_user.php';
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	

	# 1. Add a user account
	if(trim($_REQUEST["name"]) <> "" and trim($_REQUEST["pwd"])<> "" and trim($_REQUEST["email"])<> "")
	{
	$params = array(
	        'command'=>'add',
	        'name'=>"{$_REQUEST["name"]}",
	        'pwd'=>"{$_REQUEST["pwd"]}",
	        'reg_email'=>"{$_REQUEST["email"]}",
	        'oem_id'=>"{$oem}" );
	
	$url = $web_address . $path . '?' . http_build_query($params);
	curl_setopt($ch, CURLOPT_URL, $url);
	$result = curl_exec($ch);
	$content=json_decode($result,true);
	}
	else
	{
		echo "Account info cna't leave as blank";
	}
	curl_close();
	if($content["status"]=="success")
	{
		$sql="insert into qlync.end_user_list ( Account,Email, Type,Time_update,Oem_id) values ('".mysql_real_escape_string($_REQUEST["name"])."','".mysql_real_escape_string($_REQUEST["email"])."','s','".date("Y-m-d H:i")."','{$oem}')";
		sql($sql,$result_tmp,$num_tmp,0);
                echo "<h2>Add Success</h2>\n";

	}
	else
	{
                echo "<h2> Add failed, Please check if the email is correct or the email has already exsit in this system!</h2>\n";
	}
//	exec("php5 {$home_path}/html/common/daily_update_device.php");
 }//jinho fix end if

}


echo "<form action=delete_user.php method=post>\n";
echo "<H1> "._("Delete End User")."</H1>\n";
echo "<table class=table_main>\n";
	echo "<tr class=topic_main>\n";
		echo "<td> "._("Email Address")."</td>\n";
		echo "<td> "._("Function")." </td>\n";
	echo "</tr>\n";
	echo "<tr class=tr_2>\n";
		echo "<td> <input type=text name=email size=100 value=\"{$_REQUEST["email"]}\" >\n";
		echo "<input type=hidden name=step value=delete>\n";
		echo "<td> <input type=submit class=btn_2 value='"._("Delete")."' >\n";
	echo "</tr>\n";
echo "</table>\n";
echo "</form>\n";	
echo "<HR>\n";
echo "<form action=delete_user.php method=post>\n";
echo "<H1> "._("Add End User")."</H1>\n";
echo "<table class=table_main>\n";
        echo "<tr class=topic_main>\n";
                echo "<td> "._("Name")."</td>\n";
		echo "<td> "._("Email Address")."</td>\n";
		echo "<td> "._("Password")."</td>\n";
                echo "<td> "._("Function")." </td>\n";
        echo "</tr>\n";
        echo "<tr class=tr_2>\n";
		echo "<td> <input type=text name=name size=20 value=\"\">\n";
                echo "<td> <input type=text name=email size=80 value=\"\" >\n";
                echo "<td> <input type=text name=pwd size=20 value=\"\">\n";
		echo "<input type=hidden name=step value=add>\n";
                echo "<td> <input type=submit value='"._("Add")."' >\n";
        echo "</tr>\n";
echo "</table>\n";
echo "</form>\n";

?>
