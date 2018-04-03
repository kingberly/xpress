<?
include("../../header.php");
include("../../menu.php");
#Authentication Section
$sql="select * from qlync.menu where Name = 'Account Photo'";
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
//        exit();
############  Authentication Section End
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


if($_REQUEST["step"]=="new_with_mac" )
{
        $total_uploads = 1;
        $limitedext = array(".jpg",".xls",".png",".1",".lic");
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
//      if (!in_array(strtolower($ext),$limitedext)) {
	if(0){
        echo "the formate of the file is not correct<br />";
      }else{
		    $file_name_tmp=$file_name;
          if (move_uploaded_file($file_tmp,  $upload_dir."/{$_SESSION["SCID"]}/{$rid}/{$_SESSION["Contact"]}/cover.jpg")) 
		{
		chmod ($upload_dir."/{$_SESSION["SCID"]}/{$rid}/{$_SESSION["Contact"]}/cover.jpg",0777);
		$s1_pass=1;
		$msg_err="";

		//處理 檔案檢查通過
		if($s1_pass==1)
		{
			//搬檔案到正式名稱
	    chmod($upload_dir."/{$_SESSION["SCID"]}/{$rid}/{$_SESSION["Contact"]}/.cover.jpg",0777);
			if($_SESSION["RID"]['09']=="1")//if($_SESSION["RID"]=="09") //jackey patch
			{
				exec("cp {$upload_dir}/{$_SESSION["SCID"]}/{$rid}/{$_SESSION["Contact"]}/cover.jpg {$upload_dir}/{$_SESSION["SCID"]}/cover.jpg");
				// move the templ cover jpg to the root folder	
			}
//        		exec(" mv {$upload_dir}{$file_name_tmp} {$upload_dir}{$file_name}");
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
/*
##########curl get license hash info start
if($get_license_info=="1")
{

	$web_address = "http://{$api_id}:{$api_pwd}@{$api_ip}:{$api_port}";
	$path = '/manage/manage_streamserver.php';
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$data=array();
	$data['command'] = 'import_license';
	$data['license'] = $lic;


	curl_setopt($ch, CURLOPT_URL, $web_address . $path);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	curl_setopt($ch,CURLOPT_HTTPHEADER,array('Expect:'));
	$result = curl_exec($ch);
	$content=json_decode($result,true);
	curl_setopt($ch, CURLOPT_POST, false);
	if ($content['status'] != 'success') {
	    	print_r($content['error_msg']);
	}
	else {
	    	echo "success\n";
	}





    if($content[n_success_data] <> $enter_num)
	{
//		$msg_err=" Upload failed<BR>\n";
		$msg_err=$content["messages"][0]."<BR>";
		
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
*/
##############################
echo "<div align=center><font size=6> "._("Upload Account Photo")."</div>";
echo "<div class=bg_mid>";
	echo "<div class=content>";
echo "<form enctype=\"multipart/form-data\" method=post action=account_photo.php>";
	echo "<table class=table_main>";
		echo "<tr class=topic_main>";
			echo "<td>"._("Images")."</td>";
			echo "<td>"._("Please Select File")."</td>";
			echo "<td colspan=2>"._("Function")."</td>";
		echo "</tr>";
		echo "<tr class=tr_2>";
			echo "<td>";
			foreach($_SESSION["RID"] as $key=>$value)
			{

				if($value==1)
				{
				        echo "<img src='../scid/{$_SESSION["SCID"]}/{$rid}/{$_SESSION["Contact"]}/cover.jpg' alt='圖片不存在'>";
				}
			}
			echo "</td>";
			echo "<td>";
				echo "<input name=file0 type=file >";
			echo "</td>";
			echo "<td>";
			        echo "<input type=submit value='"._("Submit")."' class=btn_1>";
			        echo "<input type=hidden name=step value=new_with_mac>\n";

			echo "</td>";

		echo "</tr>";
	echo "</table>";	
		
echo "</form>";
echo "<HR>";



echo "<font color=#FF0000 size=5>";

echo $msg_err;


?>
