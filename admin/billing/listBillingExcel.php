<?php
/****************
 *Validated on Feb-14,2017,  List and Excel output OK with Charts link
 *fix error handling 
 *Writer: JinHo, Chang   
*****************/
ini_set('memory_limit','64M');
require_once 'Classes/PHPExcel.php';
require_once 'Classes/PHPExcel/IOFactory.php';
include  'Classes/PHPExcel/Writer/Excel2007.php';
require_once '_auth_.inc';
//global value  $ServerArray from dbutil.php from _auth_.inc
$CurrentDate = new DateTime();

$QUERY_TOTAL =0;
$CAM_ADDED_TOTAL =0;
$BILL_DATE = $CurrentDate->format('Y-m-d H:i:s'). "(" . date_default_timezone_get() . ")";
$BILL_SERVER = $oem." / ".getSiteName($oem);
    
//scandir(DL_PATH, SCANDIR_SORT_DESCENDING), SCANDIR_SORT_DESCENDING=1
$files = scandir(DL_PATH, 1);
$filepath = DL_PATH.$files[0];
if (($files[1]!="..") && ($files[1]!="."))
  $filepath2= DL_PATH.$files[1];


if (isTimeUp($files[0])) {
     $services = query();
     $filepath = createExcel($services);
}else
     $services = readExcel($filepath);

function isTimeUp($newest_file)
{
     global $CurrentDate;
     $currentTime=(int)$CurrentDate->format("YmdHi");
     $latestTime=(int)substr($newest_file,4,12);
     if ($currentTime - $latestTime >9999 ) return true;
     return false;
}


function createServiceTable($services){
	$html = '';
	foreach($services as $service){
		$html.="\n<tr>";
		$html.="\n<td>".$service['Account']."</td>";
		$html.="\n<td>".$service['CameraMac']."</td>";
		$html.="\n<td>".$service['ActivationDate']."</td>";
		$html.="\n<td align=center>".$service['DaysStorage']."</td>";
		$html.="\n<td align=center>".$service['Resolution']."</td>";
		$html.="\n<td align=center>".$service['ServicePackage']."</td>";
		$html.="\n<td align=center>".$service['AccountAdded']."</td>";
    $html.="\n<td>".$service['Server']."</td>";
		$html.="</tr>";

	}
	echo $html;
}
?>

<html>
<head>
</head>
<body>
<?php
  if (isset($_REQUEST["debugadmin"]))
    echo "<a href='#' onClick=\"window.open('listBillingExcelOutput.php','text',config='height=300,width=600');\">Force Excel Output</a><br>"; 
?>
<small><a href='#' onClick="window.open('listBillingCharts.php','text',config='height=600,width=900');">Monthly Bar chart</a></small><br>
<font color=#000000>
		<?php

 		if (file_exists($filepath)){
		?>
          <a href='<?php echo dirname($_SERVER['REQUEST_URI'])."/".$filepath;?>' download>Download <?php echo $filepath;?></a>
		<?php
    }
 		if (file_exists($filepath2)){
		?>          
          &nbsp<a href='<?php echo dirname($_SERVER['REQUEST_URI'])."/".$filepath2;?>' download>Download <?php echo $filepath2;?></a>

    <?
    }
    ?>
			<table>
      <tr><td>Servers</td>
      <td><?php echo $BILL_SERVER;?></td>
      </tr>
      <tr><td>Date</td>
      <td><?php echo $BILL_DATE;?></td>
      </tr>
      <tr><td>Total Cameras</td>
      <td><?php echo $QUERY_TOTAL;?></td>
      </tr>
      <tr><td>Cameras added to Account</td>
      <td><?php echo $CAM_ADDED_TOTAL;?></td>
      </tr>
      </table>
			<table>
				<thead>
					<tr bgcolor="gray">
						<th>Account</th>
						<th>Camera MAC</th>
						<th bgcolor="#fac090">Activation Date</th>
						<th>Days of Storage</th>
						<th>Resolution</th>
						<th>Service Package</th>
						<th>Added to Account?</th>
						<th>Server</th>
					</tr>
				</thead>
				<tbody>
					<?php createServiceTable($services);?>
				</tbody>
			</table>
	</div>
</font>
</body>
</html>
