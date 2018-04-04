<?
/****************
 *Validated on Apr-21,2016,
 * delete un-used servers from database 
 * listed server status
 * integrated to licservice
 * add new sites     
 *Writer: JinHo, Chang
*****************/
require_once '_auth_.inc';
 
//jinho config
$siteArray = [
'X01'=>'http://ivedaManageUser:ivedaManagePassword@xpress2.megasys.com.tw',
'X02'=>'http://ivedaManageUser:ivedaManagePassword@xpress.megasys.com.tw',
'P04'=>'http://pldtManageUser:pldtManagePassword@videomonitoring.pldtcloud.com',
'V04'=>'http://ivedaManageUser:ivedaManagePassword@camera.vinaphone.vn',
'V03'=>'http://ivedaManageUser:ivedaManagePassword@sentirvietnam.vn',
'Z02'=>'http://ivedaManageUser:7iUut4fmysRe0Qw3J9Vr@zee.ivedaxpress.com',
'T03'=>'http://ivedaManageUser:7iUut4fmysRe0Qw3J9Vr@test.ivedaxpress.com',
'J01'=>'http://ivedaManageUser:ivedaManagePassword@japan.ivedaxpress.com',
'T04'=>'http://ivedaManageUser:ivedaManagePassword@rpic.taipei',
'T05'=>'http://ivedaManageUser:ivedaManagePassword@rpic.tycg.gov.tw',
'K01'=>'http://megasysManageUser:amICAnCeDiNgEntAtiDE@kreac.kcg.gov.tw'
];

function isDomainAvailible($domain)
{
       //initialize curl
       $curlInit = curl_init($domain);
       curl_setopt($curlInit,CURLOPT_CONNECTTIMEOUT,10);
       curl_setopt($curlInit,CURLOPT_HEADER,true);
       curl_setopt($curlInit,CURLOPT_NOBODY,true);
       curl_setopt($curlInit,CURLOPT_RETURNTRANSFER,true);

       //get answer
       $response = curl_exec($curlInit);

       curl_close($curlInit);

       if ($response) return true;

       return false;
}
if (isset($_REQUEST["baseurl"]))
  $BASEURL = $_REQUEST["baseurl"];
else //current site
  $BASEURL = $siteArray[$oem];
//end of jinho config
$upload_dir=$api_temp;
if($_REQUEST["step"]=="new_with_mac" )
{
        $total_uploads = 1;
        $limitedext = array(".txt",".xls",".png",".csv");
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
    while (($data = fgetcsv($handle, 1000,",")) !== FALSE) {
			$list[]=array_filter($data);// remove the null array
    }
    fclose($handle);
}
	$enter_num=$_REQUEST["num"];
		if($enter_num == "")
		{
			$msg_err	= "Please enter the License Qty's";
			$s1_pass	= 0;
		}
		$list=array_filter($list); // remove the null array 
		if($enter_num <> sizeof($list) and $enter_num <> "")
		{
                        $msg_err        = "Please enter the correct Qty's with your Upload File";
                        $s1_pass        = 0;

		}
                if($s1_pass==1)
                {
                        {
//			$i=0;
			foreach($list as $key=>$value)
                        {
				
                                $apply_id[$key]=$value[0];
                                $apply_cid[$key]=$value[1];
				$apply_oemid[$key]=$value[2];
				$apply_begin[$key]=$value[3];
				$apply_expire[$key]=$value[4];
				$apply_num[$key]=$value[5];
				$apply_key[$key]=$value[6];
				$apply_hash[$key]=$value[7];
//				$i++;

                        }
                        }
                }

		//處理 檔案檢查通過
		if($s1_pass==1)
		{
			//搬檔案到正式名稱
	                chmod($upload_dir."/".$file_name,0777);
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
##########curl get license hash info starta
if($get_license_info=="1")
{
//jinho check site available
if (! isDomainAvailible($BASEURL)){
    foreach ($siteArray as $mkey => $mvalue){
      if ($BASEURL == $mvalue){
        echo $mkey;
        break;
      }
    }
    echo " site is not available!!";
    //exit(1);
    $BASEURL = $siteArray[$oem];
}
//jinho check site available end
//$web_address = "http://{$api_id}:{$api_pwd}@{$api_ip}:{$api_port}";
$web_address = $BASEURL;
$path = '/manage/manage_tunnelserver.php';
$ch = curl_init();
curl_setopt($ch, CURLOPT_NOSIGNAL, 1); //jinho add timeout
curl_setopt($ch, CURLOPT_TIMEOUT_MS, 200);//jinho add timeout
curl_setopt($ch, CURLOPT_TIMEOUT,30);  //jinho add timeout
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);//jinho add timeout
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

foreach($apply_id as $key=>$value){

	$licenses[$key]=array(
		'version' 	=> $apply_id[$key],
		'cid'		=> $apply_cid[$key],
		'oem_id'	=> $apply_oemid[$key],
		'begin'		=> $apply_begin[$key],
		'expire'	=> $apply_expire[$key],
		'channels'	=> $apply_num[$key],
		'license_key'	=> $apply_key[$key],
		'signature'	=> $apply_hash[$key]
		);
}
$post_data = array();
$post_data['command'] = 'import_licenses';
$post_data['data'] = json_encode($licenses);
curl_setopt($ch, CURLOPT_URL, $web_address . $path);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch,CURLOPT_HTTPHEADER,array('Expect:'));
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
$result = curl_exec($ch);
$content=json_decode($result,true);
curl_setopt($ch, CURLOPT_POST, false);
if ($content['status'] != 'success') {
//	$msg_err.=$content['error_msg'];
	foreach($content['error_msg'] as $v)
	{
		$msg_err.=$v."<BR>";
	}
}
else {
//    	echo "success\n";
}


}

##############################
echo "<div align=center><font size=6> "._("Tunnel Server License Upload")."</div>";
echo "<div class=bg_mid>";
	echo "<div class=content>";
echo "<form enctype=\"multipart/form-data\" method=post action=".$_SERVER['PHP_SELF'].">";
echo "<input type=hidden name=baseurl value='".$BASEURL."'>";//jinho added url
	echo "<table class=table_main>";
		echo "<tr class=topic_main>";
			echo "<td>"._("Please Input Qty's")."</td>";
			echo "<td>"._("Please Select File")."</td>";
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
			        echo "<input type=submit value='"._("Submit")."' class=btn_1>";
			        echo "<input type=hidden name=step value=new_with_mac>\n";

			echo "</td>";

		echo "</tr>";
	echo "</table>";	
		
echo "</form>";
echo "<HR>";



echo "<font color=#FF0000 size=5>";

echo $msg_err;

//jinho check site available
if (! isDomainAvailible($BASEURL)){
    foreach ($siteArray as $mkey => $mvalue){
      if ($BASEURL == $mvalue){
        echo $mkey;
        break;
      }
    }
    echo " site is not available!!";
    //exit(1);
    $BASEURL = $siteArray[$oem];
}
//jinho check site available end

//$web_address = "http://{$api_id}:{$api_pwd}@{$api_ip}:{$api_port}";
$web_address = $BASEURL;
$path = '/manage/manage_tunnelserver.php';
########################Remove license

if($_REQUEST["step"]=="remove_license")
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$params = '?command=remove_license&license_id=' . $_REQUEST["lic_id"];
	curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
	$result = curl_exec($ch);
	$content_remove_license=json_decode($result,true);
	curl_close($ch);
}




#########################List of existing license
// List the tunnel server license
#API A-2
$ch = curl_init();
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

# 1. Get stream server list
$params = '?command=get_licenses';
curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
$result = curl_exec($ch);
$content=json_decode($result,true);
$license=$content["licenses"];
curl_close($ch);

#######################Get tunnel servers
$ch = curl_init();
curl_setopt($ch, CURLOPT_HEADER, false);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

# 1. Get stream server list
$params = '?command=get_tunnelservers';
curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
$result = curl_exec($ch);
$content=json_decode($result,true);
$tunnelservers = $content['tunnelservers'];
curl_close($ch);
foreach($tunnelservers as $key_tunnel =>$value_tunnel)
{
	$tunnel[$value_tunnel[id]][hostname]=$value_tunnel[hostname];
}
#############################

flush($lic);
flush($unlic);
foreach($license as $key=>$value){
                if($value[tunnel_server_id]<> "")
                {
                $lic[$value[tunnel_server_id]][id]=$value[id];
                $lic[$value[tunnel_server_id]][version]=$value[version];
                $lic[$value[tunnel_server_id]][begin]=gmdate("Y-m-d H:i:s",$value[begin]);
                $lic[$value[tunnel_server_id]][expire]=gmdate("Y-m-d H:i:s",$value[expire]);
                $lic[$value[tunnel_server_id]][license_key]=$value[license_key];
                }
		else
		{
                $unlic[$key][id]=$value[id];
                $unlic[$key][version]=$value[version];
                $unlic[$key][begin]=gmdate("Y-m-d H:i:s",$value[begin]);
                $unlic[$key][expire]=gmdate("Y-m-d H:i:s",$value[expire]);
                $unlic[$key][license_key]=$value[license_key];

		}

}
?>
<form name=plugin>
<select name=baseurl action="<?php echo $_SERVER['PHP_SELF'];?>" method=post>
<?php
foreach ($siteArray as $mkey => $mvalue){
  if ($BASEURL == $mvalue)
    echo "<option value='".$mvalue."' selected>{$mkey}</option>\n";
  else echo "<option value='".$mvalue."'>{$mkey}</option>\n";
}
?>
</select>
<input type=submit>
</form>
<?php
echo "<table class=main_table>\n";
	echo "<tr class=topic_main>\n";
		echo "<td>"._("ID")."</td>\n";
		echo "<td>"._("Version")."</td>\n";
		echo "<td>"._("Start")." </td>\n";
		echo "<td>"._("Expired")."</td>\n";
		echo "<td>"._("License Key")."</td>\n";
		echo "<td>"._("Valid")."</td>\n";
		echo "<td>"._("Status")." </td>\n";

	foreach($unlic as $key => $value)
	{
		echo "<tr class=tr_2>\n";
		foreach($value as $key_1 =>$value_1)
		{
			echo "<td>{$value_1}</td>\n";
		}
			if(date("Ymd") > str_replace("-","",substr($value[expire])))
	                        echo "<td> <font color=#00990F>"._("Un-used")."</font> </td>\n";
			else
				echo "<td> <font color=#AA990F>"._("Expired")."</font> </td>\n";
			echo "<td>\n";
				if($_SESSION["CID"]=="N99")
				{
				echo "<form action=".$_SERVER['PHP_SELF']." method=post>\n";
        echo "<input type=hidden name=baseurl value='".$BASEURL."'>";//jinho added url
					echo "<input type=hidden name=step value=remove_license>\n";
					echo "<input type=submit value=\""._("Remove")."\">\n";
					echo "<input type=hidden name=lic_id value={$value[id]}>\n";
				echo "</form>\n";
				}
			echo "</td>\n";
		echo "</tr>\n";
	}
        foreach($lic as $key => $value)
        {
                echo "<tr class=tr_2>\n";
                foreach($value as $key_1 =>$value_1)
                {
                        echo "<td>{$value_1}</td>\n";
                }
			echo "<td> <font color=#0000FF>"._("Used")."</font> </td>\n";
			
			echo "<td> {$tunnel[$key][hostname]}</td>\n";
                echo "</tr>\n";
        }

echo "</table>\n";
?>
</body>
</html>