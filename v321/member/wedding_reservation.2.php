<?
include("../../header.php");
include("../../menu.php");
#Authentication Section
$sql="select * from qlync.menu where Name = 'Wedding Reservation'";
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
//jinho added
header("Content-Type:text/html; charset=utf-8");
?>
<script>
function confirmDelete(res, room, date)  
{//jinho add confirmDelete
    return confirm('確認刪除'+res+'房號'+room+'時段'+date+'?');
}

function optionValue(thisformobj, selectobj)
{//jinho on change select
	var chosenoption=selectobj.options[selectobj.selectedIndex];
  thisformobj.value = chosenoption.value;
}

</script>
<?php
$res_name="";
$res_room="";
//end of jinho add


if($_REQUEST["step"]=="D")
{
	$sql="select * from isat.user where ID='{$_REQUEST["room"]}' limit 0,1";
	sql($sql,$result,$num,0);
	fetch($db_room,$result,0,0);
	$tmp=explode("_",strtolower($db_room["name"]));
	//print_r($tmp);
	$sym[lunch]='a';
	$sym[dinner]='b';
	$email=$_REQUEST["date_start"].$_REQUEST["restaurant"].$sym[$_REQUEST["number"]].$tmp[1]."@weddingcam.com";
//        $url="http://{$api_id}:{$api_pwd}@{$api_path}/manage_user.php?command=deletei\&reg_email={$_REQUEST["email"]}\&oem_id={$_SESSION["CID"]}";
        $import_target_url ="http://{$api_id}:{$api_pwd}@{$api_path}/manage_user.php?command=delete&reg_email={$email}&oem_id={$_SESSION["CID"]}";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$import_target_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $result=curl_exec($ch);
        $content=json_decode($result,true); //jinho add
        curl_close($ch);
	//print_r($result);
    if ($content['status']=="success") //jinho msg 
      echo "<font color=red><b>".$_REQUEST["date_start"].$_REQUEST["restaurant"].$sym[$_REQUEST["number"]].$tmp[1]."刪除預約成功.</b></font><br>";
    else echo "<font color=red><b>".$_REQUEST["date_start"].$_REQUEST["restaurant"].$sym[$_REQUEST["number"]].$tmp[1]."刪除預約失敗!".$content['error_msg']."</b></font><br>";

	$sql="delete from qlync.reservation where ID='{$_REQUEST["id"]}'";
	sql($sql,$result_del,$num_del,0);	
}
if($_REQUEST["step"]=="Reservation")
{
	$sql="select * from qlync.account where id='{$_REQUEST["restaurant"]}' limit 0,1";
	sql($sql,$result_restaurant,$num_restaurant,0);
	fetch($db_r,$result_restaurant,0,0);
        $sql="select * from isat.user where id='{$_REQUEST["room"]}' limit 0,1";
        sql($sql,$result_room,$num_room,0);
        fetch($db_room,$result_room,0,0);
	// check if exitxist or not. if not, then insert, if 
	$sql="select * from qlync.reservation where Room_id='{$_REQUEST["room"]}' and FID='{$_REQUEST["restaurant"]}' and Number='{$_REQUEST["period"]}' and Date_start='{$_REQUEST["date_start"]}' limit 0,1";
	sql($sql,$result_chk,$num_chk,0);
	if($num_chk==0)
	{
		$sql="insert into qlync.reservation (Room_id,FID,Date_start,Time_start,Time_zone,Number) values('{$_REQUEST["room"]}','{$_REQUEST["restaurant"]}','{$_REQUEST["date_start"]}','{$_REQUEST["start_time"]}','".substr($db_r["Contact"],5,2)."','{$_REQUEST["period"]}') ";
		sql($sql,$result_insert,$num,0);
		//echo "Reserved!!";
	}
	$sym['lunch']='a';
	$sym['dinner']='b';
//	if($_REQUEST["step"]=="add")
	{
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
//	        if(trim($_REQUEST["name"]) <> "" and trim($_REQUEST["pwd"])<> "" and trim($_REQUEST["email"])<> "")
	        {
		$tmp=explode("_",strtolower($db_room["name"]));
//		$d_tmp=getdate(strtotime($_REQUEST["date_start"]));
		$d_print=date("Y-m-d",$d_tmp);

	        $params = array(
	                'command'       =>'add',
//			'name'		=>$_REQUEST["restaurant"]."{$tmp["1"]}".$_REQUEST["start_time"].str_replace("-","",substr($_REQUEST["date_start"],-5)),
			'name'		=>str_replace("-","",substr($_REQUEST["date_start"],-5)).$_REQUEST["restaurant"].$sym[$_REQUEST["period"]].$tmp["1"],
	                'pwd'           =>$_REQUEST["pwd"],
//	                'reg_email'     =>$_REQUEST["restaurant"]."{$tmp["1"]}".$_REQUEST["start_time"].str_replace("-","",substr($_REQUEST["date_start"],-5))."@weddingcam.com",
			'reg_email'	=>str_replace("-","",substr($_REQUEST["date_start"],-5)).$_REQUEST["restaurant"].$sym[$_REQUEST["period"]].$tmp["1"]."@weddingcam.com",
	                'group_id'      =>"{$_SESSION["SCID"]}{$_SESSION["AID"]}11".str_pad($fid_tmp+0,3,"000",STR_PAD_LEFT)."1",
	                'oem_id'        =>"{$_SESSION["CID"]}" );
	
	        $url = $web_address . $path . '?' . http_build_query($params);
	        curl_setopt($ch, CURLOPT_URL, $url);
	        $result = curl_exec($ch);
	        $content=json_decode($result,true);
    //jinho add msg
    if ($content['status']=="success") 
      echo "<font color=red><b>".$_REQUEST["date_start"]."預約成功. 本次預約新人帳號".$params["name"].", 密碼: ".$params["pwd"]."</b></font><br>";
    else echo "<font color=red><b>".$_REQUEST["date_start"]."預約失敗!".$content['error_msg']."</b></font><br>";
    //end of jinho mgs
		// start to add virtual camera under
		print_r($params);
		print_r($content);


	        }
	        curl_close();
	
	}
	// need to also share camera to this new account

}

if($_REQUEST["step"]=="add")
{
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

	if(trim($_REQUEST["name"]) <> "" and trim($_REQUEST["pwd"])<> "" and trim($_REQUEST["email"])<> "")
	{
	$params = array(
	        'command'	=>'add',
	        'name'		=>"{$_REQUEST["name"]}",
	        'pwd'		=>"{$_REQUEST["pwd"]}",
	        'reg_email'	=>"{$_REQUEST["email"]}",
		'group_id'	=>"{$_REQUEST["scid"]}{$_REQUEST["aid"]}{$_REQUEST["rid"]}".str_pad($fid_tmp+0,3,"000",STR_PAD_LEFT)."1",
	        'oem_id'	=>"{$_REQUEST["sub_oem"]}" );
	
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
//select the restaurant for the dealer
echo "<form action=".$_SERVER['PHP_SELF']." method=post>\n"; //jinho fix
$sql="select * from qlync.account where AID='{$_SESSION["AID"]}' and SCID='{$_SESSION["SCID"]}' and ID_01='1'";
sql($sql,$result_01,$num_01,0);
	echo "<select name=restaurant onchange='optionValue(this.form.restaurant, this);this.form.submit();'>\n";
	echo "<option value=''> Please Select...</option>\n";
for($i=0;$i<$num_01;$i++)
{
	fetch($db_01,$result_01,$i,0);
	$chk='';
	if($_REQUEST["restaurant"]==$db_01["ID"])
	{
		$chk="selected";
	}
	echo "<option value='{$db_01["ID"]}' {$chk}>{$db_01["Company_english"]}</option>\n";
 if ($chk=="selected") $res_name = $db_01["Company_english"];//jinho	
//	echo $db_01["Email"];
//	echo $db_01["Contact"];
//	echo $db_01["ID"];
}
	echo "</select>\n";
	echo "<input type=submit class=btn_4 value=select>\n";
echo "</form>\n";

if($_REQUEST["restaurant"]==""){
echo "</body></html>"; //jinho add
exit();
}

//select the room for the restaurant
echo "<form action=".$_SERVER['PHP_SELF']." method=post>\n"; //jinho fix
$sql="select id,name,reg_email,oem_id,group_id from isat.user where  right(group_id,7)='{$_SESSION["AID"]}020001' and name like 'R{$_REQUEST["restaurant"]}_%'   order by name asc ";
sql($sql,$result_02,$num_02,0);
        echo "<select name=room onchange='optionValue(this.form.room, this);this.form.submit();>\n";
        echo "<option value=''> Please Select...</option>\n";
for($i=0;$i<$num_02;$i++)
{
        fetch($db_02,$result_02,$i,0);
        $chk='';
        if($_REQUEST["room"]==$db_02["id"])
        {
                $chk="selected";
        }
        echo "<option value='{$db_02["id"]}' {$chk}>{$db_02["name"]}</option>\n";
      if ($chk=="selected") $res_room = $db_02["name"];//jinho
//      echo $db_01["Email"];
//      echo $db_01["Contact"];
//      echo $db_01["ID"];
}
        echo "</select>\n";
	echo "<input type=hidden name=restaurant value='{$_REQUEST["restaurant"]}'>\n";
        echo "<input type=submit class=btn_4 value=select>\n";
echo "</form>\n";

if($_REQUEST["room"]==""){
echo "</body></html>"; //jinho add
exit();
}


/**
  * 日曆
  *
  */
 if (function_exists('date_default_timezone_set')) {
     date_default_timezone_set('Asia/Chongqing');
 }
 $date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
 $date = getdate(strtotime($date));
 $end = getdate(mktime(0, 0, 0, $date['mon'] + 1, 1, $date['year']) - 1);
 $start = getdate(mktime(0, 0, 0, $date['mon'], 1, $date['year']));
 $pre = date('Y-m-d', $start[0] - 1);
 $next = date('Y-m-d', $end[0] + 86400);

$sql="select * from qlync.reservation where Room_id='{$_REQUEST["room"]}' and FID='{$_REQUEST["restaurant"]}' and Date_start > '{$date['year']}-{$date[mon]}-00'";
sql($sql,$result_res,$num_res,0);
$res=array();
for($k=0;$k<$num_res;$k++)
{
	fetch($db_res,$result_res,$k,0);
  if ($date['mon'] < 10) $keyname=substr($db_res["Date_start"],5,1); 
  else $keyname=substr($db_res["Date_start"],5,2); //jinho fix res key error
	$res[$keyname][substr($db_res["Date_start"],-2)][$db_res[Number]][status]="reserved";
	$res[$keyname][substr($db_res["Date_start"],-2)][$db_res[Number]][time_start]=$db_res["Time_start"];
  $res[$keyname][substr($db_res["Date_start"],-2)][$db_res[Number]][id]=$db_res["ID"];


	
}

$parm="";
foreach($_REQUEST as $key=>$value)
{
	if($key<>"date")
	{
		$parm.="&{$key}={$value}";
	}
}

 echo  '<table class=table_main>';
 echo '<tr class=topic_main>';
 echo '<td><a href="' . $PHP_SELF . '?date=' . $pre . $parm. '"><font color=#FFFFFF>Pre</a></td>';
 echo '<td colspan="5">' . $date['year'] . '  ' . $date['month'] . '</td>';
 echo  '<td><a href="' . $PHP_SELF . '?date=' . $next . $parm.'"><font color=#FFFFFF>Next</a></td>';
 echo  '</tr>';
 $arr_tpl = array(0 => '', 1 => '', 2 => '', 3 => '', 4 => '', 5 => '', 6 => '');
 $date_arr = array();
 $j = 0;
 for ($i = 0; $i < $end['mday']; $i++) {
     if (!isset($date_arr[$j])) {
         $date_arr[$j] = $arr_tpl;
     }
     $date_arr[$j][($i+$start['wday'])%7] = $i+1;
     if ($date_arr[$j][6]) {
         $j++;
     }
 }
 echo "<tr class=topic_main>\n";
	echo "<td>SUN.</td>\n";
        echo "<td>MON.</td>\n";
        echo "<td>TUE.</td>\n";
        echo "<td>WED.</td>\n";
        echo "<td>THR.</td>\n";
        echo "<td>FRI.</td>\n";
        echo "<td>SAT.</td>\n";

 echo "</tr>\n";
 foreach ($date_arr as $value) {
     echo '<tr class=topic_main>';
     foreach ($value as $v) {
         if ($v) {
             if ($v == $date['mday']) {
                 echo '<td><b>' . $v . '</b></td>';
             } else {
                 echo '<td>' . $v . '</td>';
             }
         } else {
             echo '<td>&nbsp;</td>';
         }
     }
     echo '</tr>';

	echo "<tr class=tr_2>\n";
	     foreach ($value as $v) {
        	 if ($v) {
	             if ($v == $date['mday']) {
        	         echo  '<td bgcolor=#AAFFAA>';
                                echo "<form action=".$_SERVER['PHP_SELF']." method=post>\n"; //jinho fix
                                        echo "<input type=hidden name=date_start value='{$date['year']}-{$date['mon']}-".str_pad($v,2,"00",STR_PAD_LEFT)."'>\n";
                                        echo "<input type=radio name=period value=lunch>中午場\n";
                                        echo "<input type=radio name=period value=dinner>晚上場\n";
                                        echo "<br>\n";
echo "Start Time:";
                                        echo "<select name=start_time>\n";



                                                echo "<option value='00'>00</option>\n";
                                                echo "<option value='01'>01</option>\n";

						echo "<option value='08'>08</option>\n";
                                                echo "<option value='09'>09</option>\n";
                                                echo "<option value='10' selected>10</option>\n";
                                                echo "<option value='11'>11</option>\n";
                                                echo "<option value='12'>12</option>\n";
                                                echo "<option value='13'>13</option>\n";
                                                echo "<option value='14'>14</option>\n";
                                                echo "<option value='15'>15</option>\n";
                                                echo "<option value='16'>16</option>\n";
                                                echo "<option value='17'>17</option>\n";
                                                echo "<option value='18'>18</option>\n";
                                                echo "<option value='19'>19</option>\n";
                                                echo "<option value='20'>20</option>\n";


                                        echo "</select>\n";
					echo "<HR>\n";
					echo gettext("Password"); //"Password";
          if ($date['mon'] < 10) $tmppwd="0{$date['mon']}".str_pad($v,2,"00",STR_PAD_LEFT);//jinho fix password length error
          else $tmppwd="{$date['mon']}".str_pad($v,2,"00",STR_PAD_LEFT);
					echo "<input type=txt size=4 name=pwd value='{$tmppwd}'>\n";

                                        echo "<HR>\n";
                                        echo "<input type=submit  name=step value=Reservation>\n";
//                                        echo "<input type=submit  name=step value=E>\n";
				
                                        echo "<input type=hidden name=restaurant value={$_REQUEST["restaurant"]}>\n";
                                        echo "<input type=hidden name=room value={$_REQUEST["room"]}>\n";
					echo "<input type=hidden name=date value={$_REQUEST["date"]}>\n";
					
                                echo "</form>\n";

//                                        echo "<input type=submit  name=step value=D>\n";
                                        foreach($res[$date['mon']][str_pad($v,2,"00",STR_PAD_LEFT)] as $key=>$value)
                                        {
                                                if($value[status]=="reserved")
                                                {
                                                        echo "<BR>\n";
                                                        echo "<font color=#FF6666>\n";
     //                                                   echo $key.' Reserved';
      //                                                  echo "({$value[time_start]}~".($value[time_start]+5).")";
                                                        echo "<form action=".$_SERVER['PHP_SELF']." method=post onsubmit=\"return confirmDelete('{$res_name}','{$res_room}','".$date['mon'].str_pad($v,2,"00",STR_PAD_LEFT)."({$value[time_start]}~)');\">\n"; //jinho add confirmDelete
                                                                echo "<BR>\n";
                                                                echo "<font color=#FF6666>\n";
                                                                echo $key.' Reserved';
                                                                echo "({$value[time_start]}~".($value[time_start]+5).")";
                                                                echo "<input type=hidden name=id value='{$value[id]}'>\n";
                                                                echo "<input type=hidden name=restaurant value={$_REQUEST["restaurant"]}>\n";
                                                                echo "<input type=hidden name=room value={$_REQUEST["room"]}>\n";
                                                                echo "<input type=hidden name=time_start value='{$value['time_start']}'>\n";
                                                                echo "<input type=hidden name=date_start value='{$date['mon']}".str_pad($v,2,"00",STR_PAD_LEFT)."'>\n";
                                                                echo "<input type=hidden name=number value='{$key}'>\n";
								echo "<input type=hidden name=date value={$_REQUEST["date"]}>\n";



                                                                echo "<input type=submit name=step value='D'>\n";
                                                                echo "</font>\n";
                                                        echo "</form>\n";


                                                        echo "</font>\n";
                                                }

                                        }

			echo '</td>';// today
	             } else {
	                echo  '<td nowrap>';
				echo "<form action=".$_SERVER['PHP_SELF']." method=post>\n"; //jinho fix
					echo "<input type=hidden name=date_start value='{$date['year']}-{$date['mon']}-".str_pad($v,2,"00",STR_PAD_LEFT)."'>\n";
					echo "<input type=radio name=period value=lunch>中午場\n";
					echo "<input type=radio name=period value=dinner>晚上場\n";
					echo "<br>\n";
					echo "Start Time:";
					echo "<select name=start_time>\n";
						echo "<option value='08'>08</option>\n";
                                                echo "<option value='09'>09</option>\n";
                                                echo "<option value='10' selected>10</option>\n";
                                                echo "<option value='11'>11</option>\n";
                                                echo "<option value='12'>12</option>\n";
                                                echo "<option value='13'>13</option>\n";
                                                echo "<option value='14'>14</option>\n";
                                                echo "<option value='15'>15</option>\n";
                                                echo "<option value='16'>16</option>\n";
                                                echo "<option value='17'>17</option>\n";
                                                echo "<option value='18'>18</option>\n";
                                                echo "<option value='19'>19</option>\n";
                                                echo "<option value='20'>20</option>\n";


					echo "</select>\n";
                                        echo "<HR>\n";
                                        echo gettext("Password"); //"Password";
          if ($date['mon'] < 10) $tmppwd="0{$date['mon']}".str_pad($v,2,"00",STR_PAD_LEFT);//jinho fix password length error
          else $tmppwd="{$date['mon']}".str_pad($v,2,"00",STR_PAD_LEFT);
                                        echo "<input type=txt size=4 name=pwd value='{$tmppwd}'>\n";


                                        echo "<HR>\n";
                                        echo "<input type=submit  name=step value=Reservation>\n";
//                                        echo "<input type=submit  name=step value=E>\n";
//                                        echo "<input type=submit  name=step value=D>\n";
                                        echo "<input type=hidden name=restaurant value={$_REQUEST["restaurant"]}>\n";
                                        echo "<input type=hidden name=room value={$_REQUEST["room"]}>\n";
					echo "<input type=hidden name=date value={$_REQUEST["date"]}>\n";



                                echo "</form>\n";

					foreach($res[$date['mon']][str_pad($v,2,"00",STR_PAD_LEFT)] as $key=>$value)
					{
						if($value[status]=="reserved")
						{
							echo "<form action=".$_SERVER['PHP_SELF']." method=post onsubmit=\"return confirmDelete('{$res_name}','{$res_room}','".$date['mon'].str_pad($v,2,"00",STR_PAD_LEFT)."({$value[time_start]}~)');\">\n"; //jinho add confirmDelete
								echo "<BR>\n";
								echo "<font color=#FF6666>\n";
								echo $key.' Reserved';
								echo "({$value[time_start]}~".($value[time_start]+5).")";
								echo "<input type=hidden name=id value='{$value[id]}'>\n";
								echo "<input type=hidden name=restaurant value={$_REQUEST["restaurant"]}>\n";
			                                        echo "<input type=hidden name=room value={$_REQUEST["room"]}>\n";
								echo "<input type=hidden name=time_start value='{$value['time_start']}'>\n";
								echo "<input type=hidden name=date_start value='{$date['mon']}".str_pad($v,2,"00",STR_PAD_LEFT)."'>\n";
								echo "<input type=hidden name=number value='{$key}'>\n";
								echo "<input type=hidden name=date value={$_REQUEST["date"]}>\n";
							

								echo "<input type=submit name=step value='D'>\n";
								echo "</font>\n";
							echo "</form>\n";
						}

					}
					echo "<input type=hidden name=restaurant value={$_REQUEST["restaurant"]}>\n";
					echo "<input type=hidden name=room value={$_REQUEST["room"]}>\n";


					
				echo "</form>\n";
			echo  '</td>';// others day
	             }
	         } else {
	             echo '<td>&nbsp;</td>';
	         }
	     }

	echo "</tr>\n";
 }
echo  '</table>';

/*
echo "<form action=dealer_account_mgr.php method=post>\n";
echo "<H1> "._("Add Room Admin")."</H1>\n";
echo "<table class=table_main>\n";
        echo "<tr class=topic_main>\n";
                echo "<td> "._("Name")."(Prefix+\"-\"+Floor+Romm number)</td>\n";
		echo "<td> "._("Email Address")."</td>\n";
		echo "<td> "._("Password")."</td>\n";
		echo "<td> "._("OEM ID")."</td>\n";
                echo "<td> "._("Function")." </td>\n";
        echo "</tr>\n";
        echo "<tr class=tr_2>\n";
		echo "<td> <input type=text name=name size=20 value=\"R{$db_01["ID"]}-\">\n";
                echo "<td > <input type=text name=email size=40 value=\"R{$db_01["ID"]}-xxxx@weddingcam.com\">\n";
                echo "<td> <input type=text name=pwd size=20 value=\"".substr($db_01["Contact"],7)."\">\n";
		echo "<input type=hidden name=step value=add>\n";
		echo "</td>\n";
		echo "<td>\n";
		echo "<select name=sub_oem>\n";
			echo "<option>Please select..</option>\n";
			echo "<option value='S08'>Wedding Cam</option>\n";
			echo "<option value='S09'>Truku</option>\n";
		echo "</select>\n";
		echo "</td>\n";
		echo "<td>\n";
		echo "<input type=submit class=btn_4 value='"._("Add")."' >\n";
		echo "</td>\n";
        echo "</tr>\n";
		echo "<input type=hidden name=scid value='{$_SESSION["SCID"]}'>\n";
		echo "<input type=hidden name=aid value='{$_SESSION["AID"]}'>\n";
		echo "<input type=hidden name=rid value='02'>\n"; // fixed for room admin
echo "</table>\n";
echo "</form>\n";


echo "<HR>\n";

echo "<H1> "._("Binding Device")."</H1>\n";
echo "<form enctype=\"multipart/form-data\" method=post action=dealer_account_mgr.php>";
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
*/

?>
</body>
</html>