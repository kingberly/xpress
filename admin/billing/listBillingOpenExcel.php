<?php
/****************
 *Validated on Feb-11,2015, Validated pass on open file under folder log/
 *Writer: JinHo, Chang   
*****************/
ini_set('memory_limit','64M');
require_once 'Classes/PHPExcel.php';
require_once 'Classes/PHPExcel/IOFactory.php';
include  'Classes/PHPExcel/Writer/Excel2007.php';
require_once '_auth_.inc';
//require_once ("/var/www/qlync_admin/doc/config.php");
//require_once 'dbutil.php';

if ($_GET["file"]==""){
   echo "No Parameter!!";
  exit();
}

$filepath = DL_PATH.$_GET["file"];
if (!file_exists($filepath)){
  echo "{$filepath} File Not Exist!!";
  exit();
}

//global value
$QUERY_TOTAL =0;
$CAM_ADDED_TOTAL =0;
$BILL_DATE = "";
$BILL_SERVER = "";
    
$services = readExcel($filepath);

function readExcel($filepath)
{
error_reporting(E_ALL);
    global $QUERY_TOTAL,$CAM_ADDED_TOTAL,$BILL_DATE,$BILL_SERVER;
     $inputFileType = PHPExcel_IOFactory::identify($filepath);
     $objReader = PHPExcel_IOFactory::createReader($inputFileType);
     $objReader->setReadDataOnly(true);
     $objPHPExcel 	= $objReader->load($filepath);
	$last_num	= $objPHPExcel->setActiveSheetIndex(0)->getHighestRow();

     $BILL_SERVER = $objPHPExcel->getActiveSheet()->getCell("B2")->getValue();
     $BILL_DATE = $objPHPExcel->getActiveSheet()->getCell("B3")->getValue();
     $QUERY_TOTAL = $objPHPExcel->getActiveSheet()->getCell("B4")->getValue();
     $CAM_ADDED_TOTAL = $objPHPExcel->getActiveSheet()->getCell("B5")->getValue();
    	$services = array();
    	$index = 0;
     for($i=9;$i<=$QUERY_TOTAL+9;$i++)
     {

          $arr['Account'] = $objPHPExcel->getActiveSheet()->getCell("A{$i}")->getValue();
          $arr['CameraMac'] = $objPHPExcel->getActiveSheet()->getCell("B{$i}")->getValue();
          $arr['ActivationDate'] = $objPHPExcel->getActiveSheet()->getCell("C{$i}")->getValue();
          $arr['DaysStorage'] = $objPHPExcel->getActiveSheet()->getCell("D{$i}")->getValue();
          $arr['Resolution'] = $objPHPExcel->getActiveSheet()->getCell("E{$i}")->getValue();
          $arr['ServicePackage'] = $objPHPExcel->getActiveSheet()->getCell("F{$i}")->getValue();
          $arr['AccountAdded'] = $objPHPExcel->getActiveSheet()->getCell("G{$i}")->getValue();
          $arr['Server'] = $objPHPExcel->getActiveSheet()->getCell("H{$i}")->getValue();
          $index++;
          $services[$index] = $arr;
      }
      return $services;
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
<font color=#000000>
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