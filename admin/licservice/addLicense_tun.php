<?php
/****************
 *Validated on Sep-7,2016,  
 * Management Tunnel license
 * DB : licservice.tunnel_server_license 
 *Writer: JinHo, Chang   
*****************/
require_once '_auth_.inc';
if (!$_SESSION['ID_admin_qlync']) exit();
$upload_dir="/var/tmp";
if($_REQUEST["step"]=="new_with_mac" )
{
  $total_uploads = 1;
  $limitedext = array(".csv");
  for ($i = 0; $i < $total_uploads; $i++) {
      $new_file = $_FILES['file'.$i];
      $file_name = $new_file['name'];
      $file_name = str_replace(")","_",str_replace("(","_",str_replace(' ', '_', $file_name)));
      $file_tmp = $new_file['tmp_name'];

      if (!is_uploaded_file($file_tmp)) {
          $msg_err="There is no file Updated!<br />";
      }else{
      $ext = strrchr($file_name,'.');
      if (!in_array(strtolower($ext),$limitedext)) {
        $msg_err = "the formate of the file is not correct<br />";
      }else{
    		$file_name_tmp=$file_name;
        if (move_uploaded_file($file_tmp,  $upload_dir."/".$file_name))
    		{
      		$msg_err="";
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
      		}
      		$list=array_filter($list); // remove the null array
      		if($enter_num <> sizeof($list) and $enter_num <> "")
      		{
                     $msg_err = "Please enter the correct Qty's with your Upload File";
      		}
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
          }
      //if move_upload else
       }else $msg_err=$msg_err."<BR>{$file_name} fail to upload<br/>";
     }//format check
   }//if upload
  }//for totalupload
     if ($msg_err ==""){
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
         //$link = lic_getLink();
          foreach($licenses as $apply_arr){
              //insert
              $sql="select * from licservice.tunnel_server_license where license_key='{$apply_arr['license_key']}'";
              //$result=mysql_query($sql,$link);
              //$result_num=mysql_num_rows($result);
              sql($sql,$result,$result_num,0);
              if ($result_num>0){
                 //duplicate
                 $msg_err .= "{$apply_arr['license_key']} duplicated!<br>";
                 break;
              }
              $sql = "INSERT INTO licservice.tunnel_server_license (version, cid, oem_id, begin, expire, channels, license_key, signature, note)".
          			"VALUES ('{$apply_arr['version']}', '{$apply_arr['cid']}', '{$apply_arr['oem_id']}', FROM_UNIXTIME( '{$apply_arr['begin']}' ), ".
          			"FROM_UNIXTIME( '{$apply_arr['expire']}' ), '{$apply_arr['channels']}', '{$apply_arr['license_key']}', '{$apply_arr['signature']}', '')";
              //$result=mysql_query($sql,$link);
              sql($sql,$result,$result_num,0);
//echo $sql;
           	if(!$result)  $msg_err .="{$apply_arr['license_key']} add fail!<br>";
         }


     }//if no error insert

}else if($_REQUEST["step"]=="remove_license"){

   if ($_POST['btnAction']=='Remove'){
    $link = lic_getLink();
    $sql="delete from licservice.tunnel_server_license where id='".$_POST['lic_id']."'";

    //$result=mysql_query($sql,$link);
    sql($sql,$result,$result_num,0);
    if ($result)
       $msg_err = "<div id=\"info\" class=\"success\">Delete Tunnel License ".$_POST['lic_id']." successfully.</div>";
    else
        $msg_err = "<div id=\"info\" class=\"fail\">Delete Tunnel License ".$_POST['lic_id']." Fail.</div>";
    }else if ($_POST['btnAction']=='Export'){
          $filepath = exportLicense($_POST['lic_id']);
    }

}


function createServiceTable($services)
{
    //$link = lic_getLink();
    $sql = "select * from licservice.tunnel_server_license";
    sql($sql,$result,$num,0);
    	//$result=mysql_query($sql,$link);
	if ($result){
       	$services = array();
    	   $index = 0;
    	   for($i=0;$i<mysql_num_rows($result);$i++){
             fetch($arr,$result,$i,0);
        		//$arr['id'] = mysql_result($result,$i,'id');
            //$arr['begin'] = mysql_result($result,$i,'begin');
        	  //$arr['expire'] = mysql_result($result,$i,'expire');
        		//$arr['license_key'] = mysql_result($result,$i,'license_key');
        		//$arr['cid'] = mysql_result($result,$i,'cid');
        		//$arr['channels'] = mysql_result($result,$i,'channels');
        		//$arr['version'] = mysql_result($result,$i,'version');
        		//$arr['oem_id'] = mysql_result($result,$i,'oem_id');
        		$services[$index] = $arr;
    		    $index++;
    	   }//for
	}

  $html = '';
  $now = time();
  foreach($services as $service)
  {
        $datediff = strtotime(substr($service['expire'],0,10)) - $now;
        $days_remain = floor($datediff/(60*60*24));
        
        $html.= "\n<tr class=tr_2>\n";
        $html.= "<td>{$service['id']}</td>\n";
        $html.= "<td>{$service['version']}</td>\n";
        $html.= "<td>{$service['begin']}</td>\n";
          if ($days_remain < 14)
               $html.= "<td><font color=#FF0000><b>{$service['expire']}</b></font></td>\n";
          else
              $html.= "<td>{$service['expire']}</td>\n";
		      $html.= "<td>{$service['license_key']}</td>\n";
          $html.= "<td>{$service['channels']}</td>\n";
          if ($days_remain < 14)
               $html.= "<td><font color=#FF0000><b>{$service['oem_id']}</b></font> / {$service['cid']}</td>\n";
          else
            $html.= "<td>{$service['oem_id']} / {$service['cid']}</td>\n";

          $html.= "<td>\n";
          $html.= "<form action=\"".$_SERVER['PHP_SELF']."\" method=post>\n";
          $html.= "<input type=hidden name=step value=remove_license>\n";
          if ($days_remain < 0)
      		   $html.= "<input type=submit name='btnAction' value=\"Remove\"><font color=#FF0000><b>Expired</b></font></td>\n";
          else if ($days_remain < 14)
            $html.= "<input type=submit name='btnAction' value=\"Remove\"><font color=#FF0000><b>Expire Soon</b></font></td>\n";
          else
              $html.= "<input type=submit name='btnAction' disabled value=\"Remove\"></td>\n";
    		$html.= "<td><input type=submit name='btnAction' value=\"Export\">\n";
    		$html.= "<input type=hidden name=lic_id value={$service['id']}>\n";
    		$html.= "</form>\n";
    		$html.= "</td>\n";
    	  $html.= "</tr>\n";
	}

	echo $html;
}
function exportLicense($id)
{
  //$link = lic_getLink();
  $d=date("YmdHi");
  //open file
  $filename = $d.".csv";
  $filepath = "/log/".$filename;
  $h=fopen(HOME_PATH.$filepath,"w+");
  //echo HOME_PATH.$filepath;
   $sql = "Select version, cid, oem_id, license_key, signature, channels, UNIX_TIMESTAMP(begin) as begin,UNIX_TIMESTAMP(expire) as expire FROM licservice.tunnel_server_license WHERE id='".$id."'";
   //$b1 = mysql_query($sql,$link);
   sql($sql,$b1,$result_num,0);
    if ($b1) {
       if (mysql_num_rows($b1)>0){
          $version = mysql_result($b1,0,'version');
          $cid = mysql_result($b1,0,'cid');
          $oem_id = mysql_result($b1,0,'oem_id');
          $license_key = mysql_result($b1,0,'license_key');
          $signature = mysql_result($b1,0,'signature');
          $channels = mysql_result($b1,0,'channels');
          $begin = mysql_result($b1,0,'begin');
          $expire = mysql_result($b1,0,'expire');

          fwrite($h, $version.",".$cid.",".$oem_id.",".$begin.",".$expire.",".$channels.",".$license_key.",\"".
          $signature."\"\n");
       }
    }
  //close file
  fclose($h);
  close_db($b1,$link);
  return $filename;
}
?>
<div align=center><b><font size=5><a href='tunLicense_upload.php' target=blank>Upload Tunnel</a> Server License</font></b></div>
<div class=bg_mid>
<div class=content>
<form enctype="multipart/form-data" method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
<table class=table_main>
<tr class=topic_main>
<td>License Qty</td>
<td>Please select file</td>
<td colspan=2> Function </td></tr>
<tr class=tr_2>
<td><input type=text name=num value=3></td>
<td><input name=file0 type=file ></td>
<td><input type=submit value=Submit class=btn_1>
<input type=hidden name=step value=new_with_mac>
</td></tr>
</table>
</form>
<HR>
<font color=#FF0000 size=5>
<?php
echo $msg_err;
if ($filepath!="")
   echo "<a href='download.php?file={$filepath}'>Download {$filepath}</a>";
?>
<table class=main_table>
<col /> <col /><col /><col /> <col /><col /><col width="140px" />
<tr class=topic_main>
<td>ID</td>
<td>Version</td>
<td>Start </td>
<td>Expired</td>
<td>License Key</td>
<td>Ch</td>
<td>OEM_ID / CID</td>
<td colspan=2>Status</td></tr>
<?php
 createServiceTable($services);
?>


</table>

