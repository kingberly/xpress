<?php
/****************
 *Validated on Nov-25,2014,  
 * Management Evo Stream license
 *Writer: JinHo, Chang   
*****************/
require_once '_auth_.inc';

$upload_dir="/var/tmp";
if($_REQUEST["step"]=="new_with_mac" )
{

  $total_uploads = 1;
  $limitedext = array(".lic",".1",".txt");
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
          $lic=file_get_contents($file, true);
          //read data to array
        		$lines = explode("\n", $lic);
        		$decoded = array();
        		foreach($lines as $l) {
        			// string
        			$m = preg_match('/([A-Za-z0-9_]*)="([^"]*)",?/', $l, $matches);
        			if ($m && $matches[1] && $matches[2]) {
        				$decoded[':'.$matches[1]] = $matches[2];
        				continue;
        			}
        			// integer
        			$m = preg_match('/([A-Za-z0-9_]*)=([0-9]*),?/', $l, $matches);
        			if ($m && $matches[1] && $matches[2]) {
        				$decoded[':'.$matches[1]] = intval($matches[2]);
        				continue;
        			}
        			// date
        			$m = preg_match(
  					'/([A-Za-z0-9_]*)={year=([0-9]*)[\s,]*month=([0-9]*)[\s,]*day=([0-9]*)[\s,]*hour=([0-9]*)[\s,]*min=([0-9]*)[\s,]*sec=([0-9]*)[\s,]*},?/',
        				$l, $matches);
        			if ($m && $matches[1] && $matches[2] && $matches[3] && $matches[4] &&
        					$matches[5] && $matches[6] && $matches[7]) {

        				$decoded[':'.$matches[1]] = sprintf('%04d-%02d-%02d %02d:%02d:%02d',
        						intval($matches[2]), intval($matches[3]), intval($matches[4]),
        						intval($matches[5]), intval($matches[6]), intval($matches[7]));
        				continue;
        			}
        		} //foreach lines
        		if (!$decoded[':VERSION'] ||
        				!$decoded[':LICENSE_ID'] ||
        				!$decoded[':KEY1'] ||
        				!$decoded[':KEY2'] ||
        				!$decoded[':KEY3'] ||
        				!$decoded[':SIGNATURE']  ) {
        		$msg_err = 'Invalid license<br>';
        		}
      //if move_upload else
       }else $msg_err=$msg_err."<BR>{$file_name} fail to upload<br/>";
     }//format check
   }//if upload
  }//for totalupload

     if ($msg_err ==""){
         $link = lic_getLink();
         //insert
         $sql="select * from licservice.stream_server_license where LICENSE_ID='{$decoded[':LICENSE_ID']}'";
         $result=mysql_query($sql,$link);
         $result_num=mysql_num_rows($result);
         if ($result_num>0){
            //duplicate
            $msg_err .= "{$decoded[':LICENSE_ID']} duplicated!<br>";
         }else{
             $sql = "INSERT INTO licservice.stream_server_license (VERSION, LICENSE_ID,KEY1, KEY2, KEY3, ISSUE_DATE, PURPOSE, COMPANY, CONTACT_PERSON_NAME, CONTACT_PERSON_EMAIL, SIGNATURE)".
     			"VALUES ('{$decoded[':VERSION']}', '{$decoded[':LICENSE_ID']}', '{$decoded[':KEY1']}', '{$decoded[':KEY2']}', '{$decoded[':KEY3']}',".
     			"'{$decoded[':ISSUE_DATE']}', '{$decoded[':PURPOSE']}', '{$decoded[':COMPANY']}', '{$decoded[':CONTACT_PERSON_NAME']}', '{$decoded[':CONTACT_PERSON_EMAIL']}', '{$decoded[':SIGNATURE']}')";
//echo $sql;
              $result=mysql_query($sql,$link);
      	    if(!$result)  $msg_err .="add fail!<br>";
         }


     }//if no error insert

}else if($_REQUEST["step"]=="remove_license"){

    $link = lic_getLink();
    $sql="delete from licservice.stream_server_license where id='".$_POST['lic_id']."'";

    $result=mysql_query($sql,$link);
    if ($result)
       $msg_err = "<div id=\"info\" class=\"success\">Delete Evo Stream License ".$_POST['lic_id']." successfully.</div>";
    else
        $msg_err = "<div id=\"info\" class=\"fail\">Delete Evo Stream License ".$_POST['lic_id']." Fail.</div>";

}

function createServiceTable($services)
{
    $link = lic_getLink();
    $sql = "select * from licservice.stream_server_license";
    	$result=mysql_query($sql,$link);
	if ($result){
       	$services = array();
    	   $index = 0;
    	   for($i=0;$i<mysql_num_rows($result);$i++){
    		$arr['id'] = mysql_result($result,$i,'id');
          $arr['VERSION'] = mysql_result($result,$i,'VERSION');
	     $arr['LICENSE_ID'] = mysql_result($result,$i,'LICENSE_ID');
		$arr['ISSUE_DATE'] = mysql_result($result,$i,'ISSUE_DATE');
		$arr['PURPOSE'] = mysql_result($result,$i,'PURPOSE');
		$arr['COMPANY'] = mysql_result($result,$i,'COMPANY');
		$arr['CONTACT_PERSON_NAME'] = mysql_result($result,$i,'CONTACT_PERSON_NAME');
    		$services[$index] = $arr;
		$index++;
    	   }//for
	}

  $html = '';
  foreach($services as $service)
  {

      $html.= "\n<tr class=tr_2>\n";
      $html.= "<td>{$service['id']}</td>\n";
      $html.= "<td>{$service['VERSION']}</td>\n";
      $html.= "<td>{$service['LICENSE_ID']}</td>\n";
      $html.= "<td>{$service['ISSUE_DATE']}</td>\n";
      $html.= "<td>{$service['PURPOSE']}</td>\n";
      $html.= "<td>{$service['COMPANY']}</td>\n";
      $html.= "<td>{$service['CONTACT_PERSON_NAME']}</td>\n";
	    $html.= "<td>\n";
	    $html.= "<form action=\"".$_SERVER['PHP_SELF']."\" method=post>\n";
		$html.= "<input type=hidden name=step value=remove_license>\n";
		$html.= "<input type=submit value=\"Remove\">\n";
		$html.= "<input type=hidden name=lic_id value={$service['id']}>\n";
		$html.= "</form>\n";
		$html.= "</td>\n";
	    $html.= "</tr>\n";
	}

	echo $html;
}

##############################
echo "<div align=center><b><font size=5>Upload Evo Stream Server License</font></b></div>";
echo "<div class=bg_mid>";
	echo "<div class=content>";
echo "<form enctype=\"multipart/form-data\" method=post action=\"".$_SERVER['PHP_SELF']."\">";
	echo "<table class=table_main>";
		echo "<tr class=topic_main>";
			echo "<td>License Qty's</td>";
			echo "<td>Please select file</td>";
			echo "<td colspan=2> Function </td>";
		echo "</tr>";
		echo "<tr class=tr_2>";
			echo "<td>";
			        echo "<input type=text name=num value=1>";
			echo "</td>";
			echo "<td>";
				echo "<input name=file0 type=file >";
			echo "</td>";
			echo "<td>";
			        echo "<input type=submit value=Submit class=btn_1>";
			        echo "<input type=hidden name=step value=new_with_mac>\n";

			echo "</td>";

		echo "</tr>";
	echo "</table>";

echo "</form>";
echo "<HR>";
echo "<font color=#FF0000 size=5>";
echo $msg_err;


echo "<table class=main_table>\n";
	echo "<tr class=topic_main>\n";
		echo "<td>ID</td>\n";
		echo "<td>Version</td>\n";
		echo "<td>LICENSE_ID</td>\n";
		echo "<td>ISSUE_DATE</td>\n";
		echo "<td>PURPOSE</td>\n";
		echo "<td>COMPANY</td>\n";
		echo "<td>CONTACT</td>\n";
		echo "<td>Action</td></tr>\n";

          createServiceTable($services);


echo "</table>\n";


?>