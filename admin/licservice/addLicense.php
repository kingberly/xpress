<?php
/****************
 *Validated on Oct-30,2015,  
 * management All license expiration date
 * fixed input date as current date 
 *Writer: JinHo, Chang   
*****************/
require_once '_auth_.inc';
if (!$_SESSION['ID_admin_qlync']) exit();
if($_POST["step"]=="new_with_mac" )
{

    $oem_id=$_POST["oem_id"];
    $start_date=$_POST["start_date"];
    $expire_date=$_POST["expire_date"];        
    $lic_note=$_POST["lic_note"];
     //If success perform database update
     if ($msg_err ==""){
          $link = lic_getLink();
           //insert
           $sql="insert into licservice.other_license(begin, expire, oem_id, note)VALUES ('{$start_date}','{$expire_date}','{$oem_id}','{$lic_note}')"; 
           $result=mysql_query($sql,$link);
           if(!$result)  $msg_err="add fail!";
          else $msg_err.="<font color=#0000FF size=4>success.</font>";
        }
}else if($_REQUEST["step"]=="remove_license"){
    if ($_POST['btnAction']=='Remove'){
        $link = lic_getLink();
        $sql="delete from licservice.other_license where id='".$_POST['lic_id']."'";
      
        $result=mysql_query($sql,$link);
        if ($result)
           $msg_err = "<div id=\"info\" class=\"success\">Delete License ".$_POST['lic_id']." successfully.</div>";
        else
            $msg_err = "<div id=\"info\" class=\"fail\">Delete License ".$_POST['lic_id']." Fail.</div>";    
    }
}//if newmac submit

function createServiceTable()
{
    $link = lic_getLink();
    $sql = "select * from licservice.other_license";
    $result=mysql_query($sql,$link);
	if ($result){
       	$services = array();
    	   $index = 0;
    	   for($i=0;$i<mysql_num_rows($result);$i++){
            $arr['id'] = mysql_result($result,$i,'id');
            $arr['begin'] = mysql_result($result,$i,'begin');
            $arr['expire'] = mysql_result($result,$i,'expire');
            $arr['note'] = mysql_result($result,$i,'note');
            $arr['oem_id'] = mysql_result($result,$i,'oem_id');
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
          $html.= "<td>{$service['begin']}</td>\n";
          if ($days_remain < 30)
               $html.= "<td><font color=#FF0000><b>{$service['expire']}</b></font></td>\n";
          else
              $html.= "<td>{$service['expire']}</td>\n";
          if ($days_remain < 30)
               $html.= "<td><font color=#FF0000><b>{$service['oem_id']}</b></font></td>\n";
          else
		    $html.= "<td>{$service['oem_id']}</td>\n";
        $html.= "<td>{$service['note']}</td>\n";
  	    $html.= "<td>\n";
  	    $html.= "<form action=\"".$_SERVER['PHP_SELF']."\" method=post>\n";
  	   	$html.= "<input type=hidden name=step value=remove_license>\n";
          if (($days_remain < 0) or (isset($_REQUEST["debugadmin"])) )
		        $html.= "<input type=submit name='btnAction' value=\"Remove\">\n";
          else
              $html.= "<input type=submit name='btnAction' disabled value=\"Remove\">\n";
    		$html.= "<input type=hidden name=lic_id value={$service['id']}>\n";
    		$html.= "</form>\n";
		if($days_remain < 0)
		    $html.= "<font color=#FF0000><b>Expired</b></font>\n";
    else if ($days_remain < 30)
        $html.= "<font color=#FF0000><b>Expire Soon</b></font>\n";
		  $html.= "</td>\n";
	    $html.= "</tr>\n";
	}
	echo $html;
}
?>
<div align=center><b><font size=5>License Tracking Management</font></b></div>
<div class=bg_mid>
<div class=content>
<form method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
<table class=table_main>
<tr class=topic_main>
<td>Date</td>
<td>Info</td>
<td colspan=2> Function </td></tr>
<tr class=tr_2>
<td>
Start Date <input type=text  size=15 name=start_date value='<?php echo date("Y-m-d");?> 00:00:00'><br>
Expire Date <input type=text  size=15 name=expire_date value='<?php echo date('Y-m-d', strtotime('+1 years'));?> 00:00:00'><br>
ex format: YYYY-MM-DD hh:mm:ss
</td><td>
OEM ID <input type=text  size=5 name=oem_id value='X02'><br>
License Note <input type=text  size=30 name=lic_note value='SSL'> 
</td><td><input type=submit value=Submit class=btn_1>
<input type=hidden name=step value=new_with_mac>
</td></tr>
</table>
</form>
<HR>
<font color=#FF0000 size=5>
<?php
echo $msg_err;
?>
</font>
<table class=main_table>
<col /> <col /><col /><col /> <col /><col /><col width="140px" />
<tr class=topic_main>
<td>ID</td>
<td>Start</td>
<td>Expired</td>
<td>OEM_ID</td>
<td width=300>License</td>
<td>Status</td></tr>
<?php
 createServiceTable();
?>
