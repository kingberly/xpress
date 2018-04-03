<?php
include("../../header.php");
include("../../menu.php");
#Authentication Section
$sql="select * from qlync.menu where Name = 'Lesson Info'";
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

$upload_dir=$home_path."/html/scid";
if(!is_dir($upload_dir."/{$_SESSION["SCID"]}"))
{
	mkdir($upload_dir."/{$_SESSION["SCID"]}", 0777, true);
}
$rid='00';
foreach($_SESSION["RID"] as $key=>$value)
{

	if(!is_dir($upload_dir."/{$_SESSION["SCID"]}/{$key}") and $value==1)
	{

	        mkdir($upload_dir."/{$_SESSION["SCID"]}/{$key}", 0777, true);
		chmod ($upload_dir."/{$_SESSION["SCID"]}/{$key}",0777);
	}
        if(!is_dir($upload_dir."/{$_SESSION["SCID"]}/{$key}/{$_SESSION["Contact"]}") and $value==1)
        {
                mkdir($upload_dir."/{$_SESSION["SCID"]}/{$key}/{$_SESSION["Contact"]}", 0777, true);
                chmod ($upload_dir."/{$_SESSION["SCID"]}/{$key}/{$_SESSION["Contact"]}",0777);
        }
	if($value==1 )
		$rid=$key;

}

if($_REQUEST["step"]=="Delete" and $_REQUEST["id"] <> "")
{
	$sql="delete from qlync.application_info where ID='{$_REQUEST["id"]}'";
	sql($sql,$result_delete,$num_delete,0);
}

if($_REQUEST["step"]=="Update" and $_REQUEST["id"] <> "")
{
        $sql="update qlync.application_info set Name='{$_REQUEST["lesson_name"]}',Info='{$_REQUEST["lesson_info"]}' where ID='{$_REQUEST["id"]}'";
        sql($sql,$result_update,$num_update,0);
}


if($_REQUEST["step"]=="add" )
{
	$ld_tmp[]=str_pad($_REQUEST["lesson_date_yy"],3,"000",STR_PAD_LEFT);
        $ld_tmp[]=str_pad($_REQUEST["lesson_date_mm"],2,"00",STR_PAD_LEFT);
        $ld_tmp[]=str_pad($_REQUEST["lesson_date_dd"],2,"00",STR_PAD_LEFT);
        $ld_tmp[]=str_pad($_REQUEST["lesson_date_hh"],2,"00",STR_PAD_LEFT);
        $ld_tmp[]=str_pad($_REQUEST["lesson_date_MM"],2,"00",STR_PAD_LEFT);
	$ld=implode("-",$ld_tmp);


	$sql="insert into qlync.application_info (Name,Info,Role_type,Owner, Time_start, Time_duration,SCID) values ";
	$sql.="( '{$_REQUEST["lesson_name"]}' ,'{$_REQUEST["lesson_info"]}','{$rid}','{$_SESSION["Contact"]}','{$ld}','{$_REQUEST["lesson_duration"]}','{$_SESSION["SCID"]}');";
	sql($sql,$result,$num,0);


}

if($_REQUEST["step"]=="modify_camera")
{
        $web_address = "http://{$api_id}:{$api_pwd}@{$api_path}";
        $path='/manage_device.php';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);


        # 1. Add a user account
        if(trim($_REQUEST["mac"]) <> "" and trim($_REQUEST["camera_name"])<> "" )
        {
        $mobile_tmp=explode("-",$_REQUEST["mobile"]);

        $params = array(
                'command'       =>'rename_device',
                'new_name'      =>"{$_REQUEST["camera_name"]}",
                'mac_addr'      =>"{$_REQUEST["mac"]}" );


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


}
/*
##########curl get license hash info start
if($get_license_info=="1")
{

}
*/
##############################
$sql="select * from isat.query_info where user_name='{$_SESSION["Contact"]}' group by mac_addr order by mac_addr";
//sql($sql,$result_email,$num_email,0);
$db_emails= myPDOQuery($sql);$num_email=sizeof($db_emails);
for($i=0;$i<$num_email;$i++)
{
	//fetch($db_email,$result_email,$i,0);
  $db_email= $db_emails[$i];
	$cam[$i]["mac"]		= $db_email["mac_addr"];
	$cam[$i]["name"]	= $db_email["name"];
}

echo "<table class=table_main>\n";
foreach($cam as $key=>$value)
{
	echo "<tr class=tr_2>\n";
		echo "<td>\n";
			echo "Camera Mac : ".$value["mac"];
		echo "</td>\n";
                echo "<td>\n";
          echo "<form action=".$_SERVER['PHP_SELF']." method=post>\n";//jinho fix multiple camera update issue
                        echo "Camera Name : ";
      /*if ($value["mac"] != $value["name"]) //fix utf8 issue
			    echo "<input type=text name=camera_name value='{$value["name"]}' size=32 readonly>";
      else */
      echo "<input type=text name=camera_name value='{$value["name"]}' size=32>";
                echo "</td>\n";
		echo "<td>\n";
      //if ($value["mac"] == $value["name"]) //fix utf8 issue
			   echo "<input type=submit name=step value=modify_camera>\n";
			echo "<input type=hidden name=mac value='{$value["mac"]}'>\n";
      echo "</form>\n";
		echo "</td>\n";

	echo "</tr>\n";
	
}
echo "</table>\n";



echo "<div align=center><font size=6> "._("Upload  Lesson Info")."</div>";
echo "<div class=bg_mid>";
	echo "<div class=content>";
echo "<form enctype=\"multipart/form-data\" method=post action=".$_SERVER['PHP_SELF'].">";
	echo "<table class=table_main>";
		echo "<tr class=topic_main>";
			echo "<td>"._("Lesson Name")."</td>";
			echo "<td>"._("Lesson Introduction")."</td>";
			echo "<td>"._("Lesson Time")."</td>\n";
			echo "<td>"._("Lesson Duration")."</td>\n";
			echo "<td colspan=2>"._("Function")."</td>";
		echo "</tr>";
		echo "<tr class=tr_2>";
			echo "<td>";
				echo "<input type=index name=lesson_name value=''>\n";

			echo "</td>";
			echo "<td>\n";
				echo "<textarea name=lesson_info cols=20 rows=5>\n";
				echo "</textarea>\n";
			echo "</td>\n";
                        echo "<td>";
                                echo "年<input type=index name=lesson_date_yy value='' placeholder='yyy'>\n";
                                echo "月<input type=index name=lesson_date_mm value='' placeholder='mm'>\n";
                                echo "日<input type=index name=lesson_date_dd value='' placeholder='dd'>\n";
                                echo "時<input type=index name=lesson_date_hh value='' placeholder='00~23'>\n";
                                echo "分<input type=index name=lesson_date_MM value='' placeholder='00~59>'\n";


                        echo "</td>";
                        echo "<td>";
				echo "<select name=lesson_duration>\n";
					echo "<option value=15> 15</option>\n";
					echo "<option value=30> 30</option>\n";
					echo "<option value=60> 60</option>\n";
					echo "<option value=90> 90</option>\n";
					echo "<option value=120> 120</option>\n";
				echo "</select>\n";
				echo "Min.";

                        echo "</td>";
			echo "<td>";
			        echo "<input type=submit value='"._("Submit")."' class=btn_3>";
			        echo "<input type=hidden name=step value=add>\n";

			echo "</td>";

		echo "</tr>";
	echo "</table>";	
		
echo "</form>";
echo "<HR>";

	$sql="select * from qlync.application_info where Role_type='{$rid}' and Owner='{$_SESSION["Contact"]}' and Time_start > '".(date("Y")-1911)."-".date("m")."-".date("d")."' order by Time_start asc";
	sql($sql,$result_list,$num_list,0);

        echo "<table class=table_main>";
                echo "<tr class=topic_main>";
                        echo "<td>"._("Lesson Name")."</td>";
                        echo "<td>"._("Lesson Introduction")."</td>";
                        echo "<td>"._("Lesson Time")."</td>\n";
                        echo "<td>"._("Lesson Duration")."</td>\n";
			echo "<td>"._("Function")."</td>\n";
		echo "</tr>\n";
		for($i=0;$i<$num_list;$i++)
		{
			fetch($db_list,$result_list,$i,0);
			echo "<form method=post action=".$_SERVER['PHP_SELF'].">\n";
			echo "<tr>\n";
				echo "<td>\n";
					echo "<input type=txt name=lesson_name value='{$db_list["Name"]}'>\n";
				echo "</td>\n";
                        echo "<td>\n";
                                echo "<textarea name=lesson_info cols=20 rows=5 >\n";
					echo $db_list["Info"];
                                echo "</textarea>\n";
                        echo "</td>\n";
			echo "<td>\n";
				echo $db_list["Time_start"];
			echo "</td>\n";
		
                        echo "<td>\n";
                                echo $db_list["Time_duration"];
                        echo "</td>\n";
			echo "<td>\n";
				echo "<input type=submit name=step value='Delete' class=btn_4>\n";
				echo "<input type=submit name=step value='Update' class=btn_3>\n";
				echo "<input type=hidden name=id value='{$db_list["ID"]}'>\n";
				echo "<HR>\n";

		
			echo "</tr>\n";
			echo "</form>\n";
		}
	echo "</table>\n";


echo "<font color=#FF0000 size=5>";

echo $msg_err;


?>
