<?php
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

$upload_dir=$api_temp;
if($_REQUEST["step"]=="new_with_mac" )
{
        $total_uploads = 1;
        $limitedext = array(".txt",".xls",".png");
for ($i = 0; $i < $total_uploads; $i++) {
         $new_file = $_FILES['file'.$i];
$order=array("(",")"," ");
$replace="_";
    $file_name = $new_file['name'];
        $file_name= str_replace($order,$replace,$file_name);
//    $file_name = str_replace(' ', '_', $file_name);
    $file_tmp = $new_file['tmp_name'];
    if (!is_uploaded_file($file_tmp)) {
                         $msg_err="There is no file Updated!<br />";


    }else{
      $ext = strrchr($file_name,'.');
      if (!in_array(strtolower($ext),$limitedext)) {
        echo "the formate of the file is not correct<br />";
      }else{

                $file_name_tmp=$file_name;
          if (move_uploaded_file($file_tmp,  $upload_dir."/".$file_name))
                {
                $s1_pass=1;
                $msg_err="";
#######################
# 處理EXCEL資料
#######################
                $file=$upload_dir."/".$file_name;
                $row = 1;
if (($handle = fopen($file, "r")) !== FALSE) {
    while (($d = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $num = count($d);
        $row++;
        if($num==5)
                $list[]=$d;
    }
    fclose($handle);
}
        $enter_num=$_REQUEST["num"];
                if($enter_num == "")
                {
                        $msg_err        = "Please enter the License Qty's";
                        $s1_pass        = 0;
                }
                if($enter_num <> sizeof($list) and $enter_num <> "")
                {
                        $msg_err        = "Please enter the correct Qty's with your Upload File";
                        $s1_pass        = 0;

                }
                if($s1_pass==1)
                {
                        {
                        foreach($list as $key=>$value)
                        {

                                $apply_mac[]=$value[0];
                                $apply_lic[]=$value[1];
                                $apply_cid[]=$value[2];
                                $apply_pid[]=$value[3];
                                $apply_hash[]=$value[4];

                        }
                        foreach($list as $key=>$value)
                        {
                                //檢查是否為12碼
                                if(strlen(trim(str_replace("-","",str_replace(":","",$value[0])))) <> 12)
                                {
                                        $mac_err_list[len][]=$value[0];
                                        $s1_pass=0;
                                }


                        }
                        }
                }

                //處理 檔案檢查通過
                if($s1_pass==1)
                {
                        //搬檔案到正式名稱
                        chmod($upload_dir."/".$file_name,0777);
//                      exec(" mv {$upload_dir}{$file_name_tmp} {$upload_dir}{$file_name}");
                        //將ORDER NUMBER 回存
                        $get_license_info="1";

                }

          }else{
                        $msg_err="{$msg_err}.<BR>{$file_name}無法上傳。<br />";
                        $s1_pass=0;
          }
       }
     }
  }

}
##########curl get license hash info start
if($get_license_info=="1")
{
        $import_target_url = "http://{$api_id}:{$api_pwd}@{$api_path}/order.php";
//    $import_data_array = array();
        foreach($apply_mac as $key=>$value)
        {
            	$import_data_array[] = array('mac' => "{$value}", 'ac' => "{$apply_lic[$key]}",'user' => "{$apply_hash[$key]}");
		$temp_pid=$apply_pid[$key];
		$temp_cid=$apply_cid[$key];
		
	}

        
    // $data_array[] = array('mac' => 'xxxxxx', 'ac' => 'yyyyyy');
    // ...
		 $data = array('action'=>'bind_device_order','data'=>json_encode($import_data_array),'cid' => "{$temp_cid}",'pid' => "{$temp_pid}");
// CURL Post
	$ch = curl_init();
	    curl_setopt($ch, CURLOPT_URL,$import_target_url);
	    curl_setopt($ch, CURLOPT_POST, true);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	    curl_setopt($ch,CURLOPT_HTTPHEADER,array('Expect:')); 


	    $result=curl_exec($ch);
                                              
//	        echo curl_error($ch);


    // JSON Decoded Array to $content
	    $content=array();
	    $content=json_decode($result,true);
//    var_dump($content);
	    curl_close($ch);

	
    if($content[n_success_data] <> $enter_num)
        {
                $msg_err=$content["fail_data"]["error"]."<BR>";

                if($content[n_fail_data]>0)
                {
                        $msg_err.="Duplitcated<BR>\n";
                }

                foreach($content[fail_data] as $key=>$value)
                {
                        $msg_err.="<font color=$0000FF>{$value[mac]}=>{$value[ac]}<BR>";
                }
        }
        else
        {
                $msg_err="<font color=#0000FF size=4>".$content["status"]."<BR>";

        }
}



if($_REQUEST["step"]=="delete")
{
	$sql="select oem_id from isat.user where reg_email='{$_REQUEST["email"]}' limit 0,1";
	sql($sql,$result_del,$num_del,0);
	fetch($db_del,$result_del,0,0);
	$url="http://{$api_id}:{$api_pwd}@{$api_path}/manage_user.php?command=deletei\&reg_email={$_REQUEST["email"]}\&oem_id={$db_del["oem_id"]}";
 	$import_target_url ="http://{$api_id}:{$api_pwd}@{$api_path}/manage_user.php?command=delete&reg_email={$_REQUEST["email"]}&oem_id={$db_del["oem_id"]}";
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
		$sql="delete from qlync.end_user_list where Email='".mysql_real_escape_string($_REQUEST["email"])."' and Oem_id='{$db_del["oem_id"]}'";
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
	$fid_tmp=0;
	foreach($_REQUEST["fid"] as $key=>$value)
	{
		$fid_tmp+=array_sum($value);
	}	

	# 1. Add a user account
	if(trim($_REQUEST["name"]) <> "" and trim($_REQUEST["pwd"])<> "" and trim($_REQUEST["email"])<> "")
	{
	$params = array(
	        'command'	=>'add',
	        'name'		=>"{$_REQUEST["name"]}",
	        'pwd'		=>"{$_REQUEST["pwd"]}",
	        'reg_email'	=>"{$_REQUEST["email"]}",
		'group_id'	=>"{$_REQUEST["scid"]}{$_REQUEST["aid"]}{$_REQUEST["rid"]}".str_pad($fid_tmp,3,"000",STR_PAD_LEFT)."1",
	        'oem_id'	=>"{$_REQUEST["sub_oem"]}" );
	
	$url = $web_address . $path . '?' . http_build_query($params);
	curl_setopt($ch, CURLOPT_URL, $url);
	$result = curl_exec($ch);
	$content=json_decode($result,true);
	}
	else
	{
		echo "Account info can't leave as blank";
	}
	curl_close();
//jinho added back
	if($content["status"]=="success")
	{
		$sql="insert into qlync.end_user_list ( Account,Email, Type,Time_update,Oem_id) values ('".mysql_real_escape_string($_REQUEST["name"])."','".mysql_real_escape_string($_REQUEST["email"])."','s','".date("Y-m-d H:i")."','{$oem}')";
		sql($sql,$result_tmp,$num_tmp,0);
                echo "<h2>Add Success</h2>\n";

	}
	else
	{
                echo "<h2> Add failed, Please check if the email is correct or the email has already exist in this system!</h2>\n";
	}
//	exec("php5 {$home_path}/html/common/daily_update_device.php");

 }//jinho fix end if

}

echo "<form action='".htmlentities($_SERVER['PHP_SELF'])."' method=post>\n";//jinho fix
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
echo "<form action='".htmlentities($_SERVER['PHP_SELF'])."' method=post>\n";
echo "<H1> "._("Add End User")."</H1>\n";
echo "<table class=table_main>\n";
        echo "<tr class=topic_main>\n";
                echo "<td> "._("Name")."<BR>[0-9,a-z,A-Z,_,-]<BR>[No Space]</td>\n";
		echo "<td colspan=2> "._("Email Address")."</td>\n";
		echo "<td> "._("Password")."</td>\n";
                echo "<td> "._("Function")." </td>\n";
        echo "</tr>\n";
        echo "<tr class=tr_2>\n";
		echo "<td> <input type=text name=name size=20 value=\"\">\n";
                echo "<td colspan=2> <input type=text name=email size=80 value=\"\" >\n";
                echo "<td> <input type=text name=pwd size=20 value=\"\">\n";
		echo "<input type=hidden name=step value=add>\n";
                echo "<td rowspan=4> ";
		if(sizeof($sub_oem) ==0)
		{
			echo "<input type=hidden name=sub_oem value='{$oem}'>\n";
		}
		else
		{
			echo "<select name=sub_oem>\n";
				echo "<option value='{$ome}' selected>{$oem}</option>\n";
			foreach($sub_oem as $key_oem=>$value_oem)
			{

				echo "<option value='{$value_oem}'>{$value_oem}</option>\n";
			}
			echo "</select>\n";
		}
		echo "<input type=submit class=btn_4 value='"._("Add")."' >\n";
		echo "</td>\n";
        echo "</tr>\n";
	echo "<tr class=topic_main>\n";

	//jinho hide group param
  echo "<input type=hidden name=scid value='000'>";
	echo "<input type=hidden name=aid value='0'>";
	echo "<input type=hidden name=rid value='00'>";  
	/*
		echo "<td colspan=4>Group ID</td>\n";
	echo "</tr>\n";
	echo "<tr class=topic_main>\n";
		echo "<td>Sub-CID</td>\n";
		echo "<td>Applicaton ID</td>\n";
		echo "<td>Role ID</td>\n";
		echo "<td>Functional ID</td>\n";
	echo "</tr>\n";
	echo "<tr class=tr_2>\n";
		echo "<td>\n";
			echo "<select name=scid  style='display:none;'>\n";//jinho hide
			$sql="select * from qlync.scid where Name <> ''";
			sql($sql,$result_scid,$num_scid,0);
			for($i=0;$i<$num_scid;$i++)
			{
				fetch($db_scid,$result_scid,$i,0);
				echo "<option value='{$db_scid["SCID"]}'>{$db_scid["Name"]}</option>\n";
			}
			echo "</select>\n";
		echo "</td>\n";
		echo "<td>\n";
			echo "<select name=aid  style='display:none;'>\n";//jinho
			foreach($gid as $key=>$value)
			{
				echo "<option value='{$key}'>{$value[0][name]}</option>\n";
			
			}
			echo "</select>\n";
		echo "</td>\n";
		echo "<td>\n";
			echo "<select name=rid  style='display:none;'>\n";//jinho hide
				echo "<option value='00'>Please select..</option>\n";
				foreach($gid	as $key_aid =>$value_aid)
				{
					//echo "<optgroup label=\"{$value_aid[0][name]}\">\n";//jinho hide
				foreach($value_aid as $key=>$value)
				{
					if($key>"0")
					{
						echo "<option value='".str_pad($key,2,"00",STR_PAD_LEFT)."'>{$value[0]} (".str_pad($key,2,"00",STR_PAD_LEFT).")</option>\n";
					}
				}
				}
			echo "</select>\n";
		echo "</td>\n";
		echo "<td>\n";
		*/
		//jinho hide

			echo "<table>\n";
                        echo "<tr>\n";
 
			foreach($fid as $key=>$value)
			{
 
				echo "<td nowrap>\n";
					echo "<H3>\n";
					//echo $gid[$key][0][name];//jinho hide
					echo "<HR>\n";
					foreach($value as $key_fid=>$value_fid)
					{
						echo "<div id='div1' style='display:none'><input type=checkbox name=fid[$key][] value='{$key_fid}'>{$value_fid[name]}<br></div>\n";//jinho hide
					}
				echo "</td>\n";
 
			}
                        echo "</tr>\n";
 
			echo "</table>\n";

		echo "</td>\n";
	echo "</tr>\n";
echo "</table>\n";
echo "</form>\n";


echo "<HR>\n";

echo "<H1> "._("Binding Device")."</H1>\n";
echo "<form enctype=\"multipart/form-data\" method=post action='".htmlentities($_SERVER['PHP_SELF'])."'>";
        echo "<table class=table_main>";
                echo "<tr class=topic_main>";
                        echo "<td>"._("Num")."</td>";
                        echo "<td colspan=2> "._("Function")." </td>";
                echo "</tr>";
                echo "<tr class=tr_2>";
                        echo "<td>";
                                echo "<input type=text name=num>";
                        echo "</td>";
                        echo "<td>";
                                echo "<input name=file0 type=file >";
                        echo "</td>";
                        echo "<td>";
                                echo "<input type=submit value='"._("Submit")."' class=btn_4>";
                                echo "<input type=hidden name=step value=new_with_mac>\n";

                        echo "</td>";

                echo "</tr>";
        echo "</table>";

echo "</form>";
echo $msg_err;


?>
