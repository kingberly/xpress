<?php
/****************
 *Validated on Jul-27,2016,  
 * List/Export/Search camera license
 * add debugadmin for adv feature
 * add print out format for non-debugadmin
 * add QR code feature  
 * debug cid function
 * fix global value page/cloudmac/cloudtype in the delete/export form
 * fix update feature as selectedServices is for delete-camera only    
 *Writer: JinHo, Chang   
*****************/
require_once '_auth_.inc';   //PAGE_LIMIT
//define("EDITABLE",1); //replace with debugadmin
//define("READONLY",0);
//var_dump($_POST);

if(isset($_POST['cloudmac'])){
	$cloudmac = rtrim($_POST['cloudmac']," ");
}else $cloudmac = '';

if(isset($_POST['cloudtype'])){
	$cloudtype = $_POST['cloudtype'];
}else $cloudtype =''; 

if(isset($_POST['page'])){
	$PAGE = $_POST['page'];
}else $PAGE = 1;

$QUERY_TOTAL =0;
if ($_POST['btnAction']=='ExportAll'){
$filepath = exportLicenseAll($cloudmac,$cloudtype);
//echo $filepath;
}else if ($_REQUEST["btnAction"]=="Back"){
  $currURL = "http";
   if ($_SERVER["HTTPS"] == "on") {$currURL .= "s";}
   $currURL .= "://";
   if ($_SERVER["SERVER_PORT"] != "80") {
    $currURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
   } else {
    $currURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
   }
  $cloudmac = '';$cloudtype ='';
  header("Location: ".$currURL);
}else{  

if(isset($_POST['form-type'])){
  $type = $_POST['form-type'];//htmlspecialchars($_POST['form-type'], ENT_QUOTES);
  $selectedServices = '';

    if($type=='delete-camera'){
      if(!isset($_POST['selectedServices']))
      	 echo "<div id=\"info\" class=\"error\">No Selected MAC!</div>";
      else
        $selectedServices = $_POST['selectedServices'];

          if ($_POST['btnAction']=='Delete')
          {
          	foreach($selectedServices as $itemid){
          		$b = removeByCameraMAC($itemid);
        		//$b = removeByCameraID($deleteCameraID);
          		if($b){
          			echo "<div id=\"info\" class=\"success\">Delete Camera ".$itemid." successfully.</div>";
          		}else{
          			echo "<div id=\"info\" class=\"error\">Delete Camera ".$itemid." failed</div>";
          		}
          	}//foreach
          }else if ($_POST['btnAction']=='Export'){
            $filepath = exportLicense($selectedServices);
            //echo $filepath;
          }
  	}else if($type=='update-camera'){
      $camera['mac'] = $_POST['update-mac'];//trim(htmlspecialchars($_POST['update-mac'], ENT_QUOTES));
      if (isset($_REQUEST["debugadmin"])){ 
      $camera['act'] = $_POST['update-act'];//trim(htmlspecialchars($_POST['update-mac'], ENT_QUOTES));
      $camera['cid'] = $_POST['update-cid'];
      }//debugadmin
  		$camera['serial_num'] = $_POST['update-serial_num'];//trim(htmlspecialchars($_POST['update-mac'], ENT_QUOTES));
  		$camera['model'] = $_POST['update-model'];//trim(htmlspecialchars($_POST['update-model'], ENT_QUOTES));
  		$camera['note'] = $_POST['update-note'];//trim(htmlspecialchars($_POST['update-firmware'], ENT_QUOTES));
  		$b = updateCameraService($camera);
  		if($b){
  			echo "<div id=\"info\" class=\"success\">Update Camera ".$camera['mac']." successfully.</div>";
  		}else{
  			echo "<div id=\"info\" class=\"error\">Update Camera ".$camera['mac']." failed.</div>";
  		}
    }//update-camera
}//formtype

}//else

$services = query($cloudmac,$cloudtype);

function query($value,$byItem){
//byItem 1=MAC
//byItem 2=code
//byItem 3=Serial (Order_num)
//byItem 4=Model (Hw)
//byItem 5=Note (filename)
//byItem 6=CID
  global $PAGE, $QUERY_TOTAL;

	$sql = "select * from licservice.qlicense";
  if (($byItem=="") or (strcmp($byItem,"1")==0)){
  	if($value!='')
  		$sql.=" where mac like '%$value%'";
  }else if (strcmp($byItem,"2")==0){
      $sql.=" where code like '%$value%'";
  }else if (strcmp($byItem,"3")==0){
      $sql.=" where Order_num like '%$value%'";
  }else if (strcmp($byItem,"4")==0){
      $sql.=" where Hw like '%$value%'";
  }else if (strcmp($byItem,"5")==0){
      $sql.=" where Filename like '%$value%'";
  }else if (strcmp($byItem,"6")==0){
      $value= strtoupper($value);
      $sql.=" where CID like '$value%'";
  }
	sql($sql,$result,$num,0);
	if ($result){
      $QUERY_TOTAL = $num;
  }
  //fix page jump bug while page result is exceeded next Query_TOTAL 
  if ((PAGE_LIMIT * ($PAGE-1)) > $QUERY_TOTAL)
    $PAGE = 1;

  $sql .=" order by mac DESC limit ".PAGE_LIMIT." offset ".(($PAGE-1)*PAGE_LIMIT);
//echo $sql;

	//$result=mysql_query($sql,$link);
  sql($sql,$result,$num,0);
	if ($result){
    	$services = array();
    	$index = 0;
    	for($i=0;$i<mysql_num_rows($result);$i++){
        fetch($arr,$result,$i,0);
    		 //$arr['Mac'] = mysql_result($result,$i,'Mac');
          //$arr['ID'] = mysql_result($result,$i,'ID');
    			//$arr['Code'] = mysql_result($result,$i,'Code');//act_code
    			//$arr['Filename'] = mysql_result($result,$i,'Filename');//note(64)
    			//$arr['CID'] = mysql_result($result,$i,'CID');
    			//$arr['PID'] = mysql_result($result,$i,'PID');
    			//$arr['Order_num'] = mysql_result($result,$i,'Order_num');//serial number(16)
    			//$arr['Hw'] = mysql_result($result,$i,'Hw');//model name (16)
    			$index++;
    		$services[$index] = $arr;
    	}//for
  }
	//close_db($result,$link);
	return $services;
}

function removeByCameraMAC($cloudmac){
	
	$sql1 = "DELETE FROM licservice.qlicense WHERE Mac='".$cloudmac."'";
	
  sql($sql1,$b1,$num,0);
  return $b1;
}

function removeByCameraID($id){
  
	$sql = "DELETE FROM licservice.qlicense  WHERE id='".$id."'";
	
  sql($sql,$b1,$num,0);
  return $b1;
}

function updateCameraService($camera){
  
  if (isset($camera['cid']))
  $sql = "UPDATE licservice.qlicense SET CID='".$camera['cid']."', Order_num='".$camera['serial_num']."', Hw='".$camera['model']."', Filename='".$camera['note']."' WHERE mac='".$camera['mac']."'";
  else
	$sql = "UPDATE licservice.qlicense SET Order_num='".$camera['serial_num']."', Hw='".$camera['model']."', Filename='".$camera['note']."' WHERE mac='".$camera['mac']."'";
  sql($sql,$b1,$num,0);
	return $b1;
}

function createServiceTable($services){
	$html = '';
	foreach($services as $service){
		$html.="\n<tr>";
		$id = $service['ID'];
		$cid = $service['CID'];
		$mac = $service['Mac'];
     $pid = $service['PID'];
     $order_num = $service['Order_num'];
     $hw = $service['Hw'];
     $filename = $service['Filename'];
     $code = $service['Code'];
     $mac_js = '\''.$mac.'\'';
		$html.="\n<td style=\"text-align: center\"><input type=\"checkbox\" class=\"chk\" name=\"selectedServices[]\" value=\"$mac\"></td>";
		$html.="\n<td>$id</td>";
		$html.="\n<td>$mac</td>";
    if (isset($_REQUEST["debugadmin"])){ 
        $html.="\n<td><input type=\"text\" id=\"$mac-act\" value=\"$code\" size=\"10px\"></td>";
    		$html.="\n<td><input type=\"text\" id=\"$mac-cid\" value=\"$cid\" size=\"1px\"> / $pid<input type=hidden name=debugadmin value=1></td>";
    		$html.="\n<td><input type=\"text\" id=\"$mac-serial_num\" value=\"$order_num\" size=\"10px\"></td>";
        $html.="\n<td><input type=\"text\" id=\"$mac-model\" value=\"$hw\" size=\"10px\"></td>";
       $html.="\n<td><input type=\"text\" id=\"$mac-note\" value=\"$filename\" size=\"15px\"></td>";
    		$html.="\n<td><input type=\"button\" value=\"Update\" onclick=\"updateCamera($mac_js);\">&nbsp;";
    		
				$html.="<input type=button onclick=\"genQRPage('{$cid}','{$mac}','{$code}');\" value='QR-add'>";
				$html.="<input type=button onclick=\"genMACQRPage('{$mac}');\" value='QR MAC'></td>";

    }else{//debugadmin
    		$html.="\n<td>$code</td>";
    		$html.="\n<td>$cid / $pid</td>";
        $html.="\n<td>$order_num</td>";
        $html.="\n<td>$hw</td>";
        $html.="\n<td>$filename</td>";
        if (($pid !="MC") and (substr($mac,0,2)!="VI") )
            $html.="\n<td><input type=button onclick=\"genQRPage('{$cid}','{$mac}','{$code}');\" value='QR-add'>";
        else    $html.="\n<td>";
        if ((substr($mac,0,2)!="VI") )
				$html.="<input type=button onclick=\"genMACQRPage('{$mac}');\" value='QR MAC'></td>";
				else $html.="</td>";   
    }
    

		$html.="</tr>";

	}
	echo $html;
}


function exportLicenseAll($value,$byItem)
{
//byItem 1=MAC
//byItem 2=code
//byItem 3=Serial (Order_num)
//byItem 4=Model (Hw)
//byItem 5=Note (filename)
//byItem 6=CID
  //$link = lic_getLink();
  $sql = "select * from licservice.qlicense";
  if (($byItem=="") or (strcmp($byItem,"1")==0)){
  	if($value!='')
  		$sql.=" where mac like '%$value%'";
  }else if (strcmp($byItem,"2")==0){
      $sql.=" where code like '%$value%'";
  }else if (strcmp($byItem,"3")==0){
      $sql.=" where Order_num like '%$value%'";
  }else if (strcmp($byItem,"4")==0){
      $sql.=" where Hw like '%$value%'";
  }else if (strcmp($byItem,"5")==0){
      $sql.=" where Filename like '%$value%'";
  }else if (strcmp($byItem,"6")==0){
      $value= strtoupper($value);
      $sql.=" where CID like '%$value%'";
  }

  $d=date("YmdHi");
  //open file
  $filepath = DL_PATH.$d.".txt";
  $h=fopen(HOME_PATH.$filepath,"w+");
  //echo HOME_PATH.$filepath;
    //$b1 = mysql_query($sql,$link);
    sql($sql,$b1,$num,0);
    if ($b1) {
       if ($num>0){
          for($i=0;$i<$num;$i++){
               $mac = mysql_result($b1,$i,'Mac');
               $code = mysql_result($b1,$i,'Code');
               $pid = mysql_result($b1,$i,'PID');
               $cid = mysql_result($b1,$i,'CID');
               $hash = hash4($cid,$pid,$mac,$code);
               fwrite($h,$mac.",".$code.",".$cid.",".$pid.",".$hash."\n");
          }
       }
    }
  //close file
  fclose($h);
  //close_db($b1,$link);
  return $filepath;
}

function exportLicense($list)
{
  //$link = lic_getLink();
  $d=date("YmdHi");
  //open file
  $filepath = DL_PATH.$d.".txt";
  $h=fopen(HOME_PATH.$filepath,"w+");
  //echo HOME_PATH.$filepath;
  foreach($list as $itemid){
    $sql = "Select * FROM licservice.qlicense WHERE Mac='".$itemid."'";
    //$b1 = mysql_query($sql,$link);
    sql($sql,$b1,$num,0);
    if ($b1) {
       if ($num>0){
          $mac = mysql_result($b1,0,'Mac');
          $code = mysql_result($b1,0,'Code');
          $pid = mysql_result($b1,0,'PID');
          $cid = mysql_result($b1,0,'CID');
          $hash = hash4($cid,$pid,$mac,$code);
          fwrite($h,$mac.",".$code.",".$cid.",".$pid.",".$hash."\n");
       }
    }
  }
  //close file
  fclose($h);
  //close_db($b1,$link);
  return $filepath;
}
?>
<!--html>
<head>
</head>
<body-->
<script src="../user_log/js/jquery-1.11.1.min.js"></script>
<link rel=stylesheet type="text/css" href="../user_log/js/style.css">
		<?php
		if ($filepath!=""){
		?>
          <!--a href='#' onClick="window.open('<?php echo dirname($_SERVER['REQUEST_URI']).$filepath;?>','text',config='height=500,width=500');"><?php echo $filepath;?></a-->
          <a href='<?php echo dirname($_SERVER['REQUEST_URI']).$filepath;?>' download style="float: left">Download <?php echo $filepath;?></a><br>
    <?php
    }
    ?>

<?php if (isset($_REQUEST["debugadmin"])){ ?>
<small><a href='#' onClick="window.open('listLicensePie.php','text',config='height=600,width=900');">dist. chart</a></small><br> 
<?php } ?>
<font color=#000000 size=3>
	<div id="container">
		<form id="searchForm" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
		<select id="cloudtype" name="cloudtype">
		<option value="1" <?php if ($cloudtype=="" or $cloudtype=="1") echo "selected";?>>MAC</option>
		<option value="2" <?php if ($cloudtype=="2") echo "selected";?>>Code</option>
    <option value="6" <?php if ($cloudtype=="6") echo "selected";?>>CID</option>
		<option value="3" <?php if ($cloudtype=="3") echo "selected";?>>Serial</option>
		<option value="4" <?php if ($cloudtype=="4") echo "selected";?>>Model</option>
		<option value="5" <?php if ($cloudtype=="5") echo "selected";?>>Note</option>
		</select>
		&nbsp;&nbsp;<input type="text" size="10" name="cloudmac" id="cloudmac" value="<?php echo $cloudmac?>">&nbsp;&nbsp;

      <?php
      echo "(".count($services)." / ".$QUERY_TOTAL.") ";
      echo "PAGE&nbsp;&nbsp;<Select id='page' name='page' onChange=\"this.form.submit();\">";
      for ($i=1;$i<ceil($QUERY_TOTAL/PAGE_LIMIT)+1;$i++)
      {
          if ($PAGE==$i)
            echo "<Option value='" . $i ."' selected>".$i."</Option>";
          else
            echo "<Option value='" . $i ."'>".$i."</Option>";
      }
      echo "</Select>";
      if (isset($_REQUEST["debugadmin"]))
        echo "<input type=hidden name=debugadmin value=1>";
      ?>
			<input type="submit" value="Search">
      <?php
        if ($_SESSION['ID_admin_qlync']){ //only admin can see button
      ?>
          <input type="button" name=btnAction value="ExportMultiple" onclick="window.location='exportLicense_cam.php';" style="float: right">
          <input type="submit" name=btnAction value="ExportAll" id="exportallBtn" style="float: right">
		</form>
		<form name="removeForm" id="removeForm" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
          <input type=hidden name=page value=<?php echo $PAGE;?>>
          <input type=hidden name=cloudmac value=<?php echo $cloudmac;?>>
          <input type=hidden name=cloudtype value=<?php echo $cloudtype;?>>
          <input type="button" name=btnAction value="Import" onclick="window.location='addLicense_cam.php';" style="float: right">
          <input type="submit" name=btnAction value="Export" id="exportBtn" disabled style="float: right">
          <input type="submit" name=btnAction value="Delete" id="removeBtn" disabled style="float: right">
		<input type="hidden" name="form-type" value="delete-camera">
<?php
    }//admin check
?>
			<table style="margin-top: 5px" id="tbl">
				<thead>
					<tr>
						<th></th>
						<th>ID</th>
						<th>MAC</th>
						<th>Act. Code</th>
						<th>CID / PID</th>
						<th>Serial Num.</th>
						<th>Model</th>
						<th>Note</th>
						<th>Action</th>
					</tr>
				</thead>
				<tbody>
					<?php
            createServiceTable($services);
          ?>
				</tbody>
			</table>
		</form>
	</div>
		<form id="update-camera" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
		<input type="hidden" name="form-type" value="update-camera">
		<input type="hidden" name="update-mac" id="update-mac">
<?php if (isset($_REQUEST["debugadmin"])){ ?>
    <input type="hidden" name="debugadmin" value=1>
    <input type="hidden" name="update-act" id="update-act">
    <input type="hidden" name="update-cid" id="update-cid">
<?php }?>
          <input type="hidden" name="update-serial_num" id="update-serial_num">
          <input type="hidden" name="update-model" id="update-model">
          <input type="hidden" name="update-note" id="update-note">
          <input type="hidden" name="cloudmac" id="cloudmac" value="<?php echo $cloudmac?>">
          <input type="hidden" name="cloudtype" id="cloudtype" value="<?php echo $cloudtype?>">
          <input type="hidden" name="page" id="page" value="<?php echo $PAGE?>">
	</form>

	<script>
$(document).ready(function() {
	 $('#cloudmac').focus();
	 $('#cloudmac').select();
	});

var checkboxes = $("input[type='checkbox']"),
submitButt = $("#removeBtn");
submitButt1 = $("#exportBtn");
checkboxes.click(function() {
<?php if (isset($_REQUEST["debugadmin"])){ ?>
submitButt.attr("disabled", !checkboxes.is(":checked"));
<?php }?>
submitButt1.attr("disabled", !checkboxes.is(":checked"));
});


function updateCamera(mac){
	hideMessage();
	var serial_num = $('#'+mac+'-serial_num').val();
	var model = $('#'+mac+'-model').val();
	var note = $('#'+mac+'-note').val();
<?php if (isset($_REQUEST["debugadmin"])){ ?>
  var act = $('#'+mac+'-act').val(); //new code
  var cid = $('#'+mac+'-cid').val(); //new code
  $('#update-act').val(act); //new code
  $('#update-cid').val(cid); //new code
<?php }?>
	$('#update-mac').val(mac);
	$('#update-serial_num').val(serial_num);
	$('#update-model').val(model);
	$('#update-note').val(note);
	$('#update-camera').submit();
}

function showCustomMessage(className,msg){
	var obj = $('#customMessage');
	obj.attr("class", className);
	obj.html(msg);
	obj.show();
}

function hideMessage(){
	$('#info').hide();
	$('#customMessage').hide();
}

function confirmDelete(form){
     var r = confirm("Delete the selected MAC?");
        //alert(document.getElementById('removeBtn').value);
        //document.getElementById('btnAction').value=document.getElementById('removeBtn').value;
     if (r == true){
        //btnAction value fail to submit
        form.submit();
         return true;
     }
     return false;
}
function genQRPage(cid,mac,code)
{//"cid=M04\nmac=184E9404036C\nac=SSdAcccAcjtc";
    var data = "cid="+cid+"\nmac="+mac+"\nac="+code;
    data = encodeURIComponent(data);
    var cmd = "listLicense_camQR.php?data="+data+"&filename="+mac;
    window.open(cmd, 'AddCam QR', config='height=300,width=250');
    //window.open(cmd, '_blank', config='height=300,width=250');
}
function genMACQRPage(mac)
{
    var cmd = "listLicense_camQR.php?data="+mac+"&filename="+mac;
    window.open(cmd, 'Camera MAC QR', config='height=300,width=250');
    //window.open(cmd, '_blank', config='height=300,width=250');
}
</script>
</font>
</body>

</html>