<?php
/****************
 *Validated on Feb-14,2017,  Database and Global parameters
 *remove redundancy code and add shared funcitons here
 *fix account shows E19 number issue
 *fix UTF8 database sql error   
 *Writer: JinHo, Chang   
*****************/
define("PAGE_LIMIT",50);
define("HOME_PATH","/var/www/qlync_admin/plugin/billing");
define("DL_PATH","log/");
define("LOG_FILE","billing.log");
define("EXCEL_EXT",".xlsx");
//default admin account, admin@localhost.com / 1qaz2wsx
//global value $ServerSite
//$ServerSite = getSiteName($oem);
$SiteArray = [
  "j01" => "Japan",
  "t03" => "Test for TelMex",
  "p04" => "PLDT",
  "z02" => "Zee",
  "x01" => "Xpress2",
  "x02" => "Xpress",
  "v03" => "VNPT",
  "v04" => "SentirVN"
];
if (!defined('RPIC_FOLDER'))
  if ($oem=="T04")
  define("RPIC_FOLDER","/var/www/qlync_admin/plugin/taipei/");
  else if ($oem=="T05")
  define("RPIC_FOLDER","/var/www/qlync_admin/plugin/ty/");
  else
  define("RPIC_FOLDER","/var/www/qlync_admin/plugin/rpic/");

if (file_exists(RPIC_FOLDER."_iveda.inc")){
  require_once (RPIC_FOLDER."_iveda.inc");
  if (isset($RPICAPP_USER_PWD)){
    foreach ($RPICAPP_USER_PWD as $key=>$data){
      if (($key == "RPIC")or ($key == "N99")) continue;
      if (!isset($SiteArray[strtolower($key)]))
        $SiteArray[strtolower($key)] = $data[5]; 
    }
  }
}
//else die("missing camera variable include file.");

function getSiteName($oemTag)
{
  global $SiteArray;
  foreach ($SiteArray as $oemkey => $name){
    if (strtolower($oemTag) == strtolower($oemkey) )
    return $name;
  }
  return "NA"; 
} 

function query(){
  global $oem, $QUERY_TOTAL,$CAM_ADDED_TOTAL;
  $ref=exec("grep utf8 /var/www/qlync_admin/doc/mysql_connect.php");//correct
  if ($ref=="") //v2.x
    $sql = "select Account, c1.device_uid as CameraMac, ActivationDate,c1.recycle as DaysStorage,c1.purpose as Resolution,c1.dataplan as ServicePackage  from isat.stream_server_assignment as c1 left outer join (select c3.id as c3id, c2.id as c2id, c2.uid,  DATE_FORMAT(FROM_UNIXTIME(c3.reg_date),'%b/%d/%Y') as ActivationDate, c2.Name as Account, c3.name from qlync.account_device as c2 inner join isat.device as c3 on c3.uid=c2.Uid group by c3.uid) as c4 on c1.device_uid=c4.Uid order by c4.Account Desc";
  else  //after v3.x add COLLATE utf8_unicode_ci due to database utf8 change
    $sql = "select Account, c1.device_uid as CameraMac, ActivationDate,c1.recycle as DaysStorage,c1.purpose as Resolution,c1.dataplan as ServicePackage  from isat.stream_server_assignment as c1 left outer join (select c3.id as c3id, c2.id as c2id, c2.uid,  DATE_FORMAT(FROM_UNIXTIME(c3.reg_date),'%b/%d/%Y') as ActivationDate, c2.Name as Account, c3.name from qlync.account_device as c2 inner join isat.device as c3 on c3.uid=c2.Uid COLLATE utf8_unicode_ci group by c3.uid) as c4 on c1.device_uid=c4.Uid COLLATE utf8_unicode_ci order by c4.Account Desc";
  sql($sql,$result,$num,0);
	if ($result){
      $QUERY_TOTAL = $num;
    	$services = array();
    	$index = 0;
     $CAM_ADDED_TOTAL = 0;
    	for($i=0;$i<$num;$i++){
          fetch($arr,$result,$i,0); 
          $res = $arr['Resolution'];
          if ($res=="RVME")
            $arr['Resolution'] = "VGA";
          else if ($res=="RVHI")
            $arr['Resolution'] = "HD";
          else
            $arr['Resolution'] = "QVGA";

          $srvpkg=$arr['ServicePackage'];
          if ($srvpkg=="AR") 
            $arr['ServicePackage'] = "Always + Event";
          else if ($srvpkg=="LV")
            $arr['ServicePackage'] = "Live view";           
          else if ($srvpkg=="D")
            $arr['ServicePackage'] = "Disabled";           
          else if ($srvpkg=="SR")
            $arr['ServicePackage'] = "Schedule + Event";           
          else if ($srvpkg=="EV")
            $arr['ServicePackage'] = "Event";           

          if (is_null($arr['Account'])){
            $arr['AccountAdded'] = "No";
          }else{
            $arr['AccountAdded'] = "Yes";
            $CAM_ADDED_TOTAL++;
          }
          $arr['Server'] = getSiteName($oem);          
    			$index++;
    		$services[$index] = $arr;
    	}//for
  }
	return $services;
}

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

function createExcel($services)
{
error_reporting(E_ALL);
    global $oem, $QUERY_TOTAL,$CAM_ADDED_TOTAL,$CurrentDate;
    //$filepath = DL_PATH. $oem . "_" . $CurrentDate->format("YmdHi").EXCEL_EXT;
    $filepath = HOME_PATH. "/". DL_PATH. $oem . "_" . $CurrentDate->format("YmdHi").EXCEL_EXT;
//echo $filepath;
    $objPHPExcel = new PHPExcel();
//echo date('H:i:s') . " Set properties\n";
    $objPHPExcel->getProperties()->setCreator(getSiteName($oem));
    $objPHPExcel->getProperties()->setTitle("Xpress4.1 Billing");
    $objPHPExcel->getProperties()->setSubject("Xpress4.1 Billing");
    $objPHPExcel->getProperties()->setDescription("Xpress4.1 Billing, generated using PHP classes.");
//echo date('H:i:s') . " Add some data\n";
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->setTitle('Required for Billing');
    $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(15);
    $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(10);
    $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
    $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(10);

    //$objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $rowArea = sizeof($services)+9;
    $objPHPExcel->getActiveSheet()->getStyle("D1:D{$rowArea}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle("E1:E{$rowArea}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle("F1:F{$rowArea}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->getStyle("G1:G{$rowArea}")->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getActiveSheet()->SetCellValue('A2', 'Servers');
    $objPHPExcel->getActiveSheet()->SetCellValue('B2', $oem." / ".getSiteName($oem));
    $objPHPExcel->getActiveSheet()->SetCellValue('A3', 'Date');
    $objPHPExcel->getActiveSheet()->SetCellValue('B3', $CurrentDate->format('Y-m-d H:i:s') . "(" . date_default_timezone_get() . ")");
    $objPHPExcel->getActiveSheet()->SetCellValue('A4', 'Total Cameras');
    $objPHPExcel->getActiveSheet()->SetCellValue('B4', $QUERY_TOTAL);
    $objPHPExcel->getActiveSheet()->SetCellValue('A5', 'Cameras added to Account');
    $objPHPExcel->getActiveSheet()->SetCellValue('B5', $CAM_ADDED_TOTAL);    
    $objPHPExcel->getActiveSheet()->setTitle(getSiteName($oem));
    $objPHPExcel->getActiveSheet()->SetCellValue('A8', 'Account');
    $objPHPExcel->getActiveSheet()->getStyle('A8')->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'c0c0c0'))));
    $objPHPExcel->getActiveSheet()->SetCellValue('B8', 'Camera MAC');
    $objPHPExcel->getActiveSheet()->getStyle('B8')->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'c0c0c0'))));
    $objPHPExcel->getActiveSheet()->SetCellValue('C8', 'Activation Date');
    $objPHPExcel->getActiveSheet()->getStyle('C8')->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'fac090'))));
    $objPHPExcel->getActiveSheet()->SetCellValue('D8', 'Days of Storage');
    $objPHPExcel->getActiveSheet()->getStyle('D8')->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'c0c0c0'))));
    $objPHPExcel->getActiveSheet()->SetCellValue('E8', 'Resolution');
    $objPHPExcel->getActiveSheet()->getStyle('E8')->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'c0c0c0'))));
    $objPHPExcel->getActiveSheet()->SetCellValue('F8', 'Service Package');
    $objPHPExcel->getActiveSheet()->getStyle('F8')->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'c0c0c0'))));
    $objPHPExcel->getActiveSheet()->SetCellValue('G8', 'Added to Account?');
    $objPHPExcel->getActiveSheet()->getStyle('G8')->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'c0c0c0'))));
    $objPHPExcel->getActiveSheet()->SetCellValue('H8', 'Server');
    $objPHPExcel->getActiveSheet()->getStyle('H8')->applyFromArray(array('fill' => array('type' => PHPExcel_Style_Fill::FILL_SOLID,'color' => array('rgb' => 'c0c0c0'))));
//echo date('H:i:s') . " Add some data2\n";
    $i=9;
    foreach($services as $service){
        //need to set as text, or account in number will be in number mode
        //$objPHPExcel->getActiveSheet()->getStyle("A{$i}")->getNumberFormat()->setFormatCode( PHPExcel_Style_NumberFormat::FORMAT_TEXT );
        //$objPHPExcel->getActiveSheet()->SetCellValue("A{$i}", $service['Account']);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit("A{$i}", $service['Account'],PHPExcel_Cell_DataType::TYPE_STRING);
        //$objPHPExcel->getActiveSheet()->SetCellValue("B{$i}", $service['CameraMac']);
        $objPHPExcel->getActiveSheet()->setCellValueExplicit("B{$i}", strval($service['CameraMac']),PHPExcel_Cell_DataType::TYPE_STRING);
        $objPHPExcel->getActiveSheet()->SetCellValue("C{$i}", $service['ActivationDate']);
        $objPHPExcel->getActiveSheet()->SetCellValue("D{$i}", $service['DaysStorage']);
        $objPHPExcel->getActiveSheet()->SetCellValue("E{$i}", $service['Resolution']);
        $objPHPExcel->getActiveSheet()->SetCellValue("F{$i}", $service['ServicePackage']);
        $objPHPExcel->getActiveSheet()->SetCellValue("G{$i}", $service['AccountAdded']);
        $objPHPExcel->getActiveSheet()->SetCellValue("H{$i}", $service['Server']);
        $i++;
    }
//echo date('H:i:s') . " Write to Excel2007 format\n";
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save($filepath);
//echo date('H:i:s') . " Done writing file.\r\n";
    return $filepath;              
}

?>