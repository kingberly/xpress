<?php
/****************
 *Validated on Jun-22,2016    
 * management camera license for logistic
 * Update CID if different
 * Add excel export feature   
 * Add checkbox for export type 
 * Add excel report feature
 * update memory limit from 64M to 256M. 64M only supports 270 excel running  
 *Writer: JinHo, Chang   
*****************/
ini_set('memory_limit','256M');
require_once '../billing/Classes/PHPExcel.php';
require_once '../billing/Classes/PHPExcel/IOFactory.php';
require_once '_auth_.inc';
header("Content-Type:text/html; charset=utf-8");
if (!$_SESSION['ID_admin_qlync']) exit();
$upload_dir="/var/tmp";
$msg_err ="";
$msg_dl ="";
$d=date("YmdHi"); $filepath="";$filepathE="";$filepathER="";
if($_POST["step"]=="new_with_mac" )
{

  $total_uploads = 1;
  $limitedext = array(".xls",".xlsx",".txt");

  $sheetIndex=intval($_POST["sheet_index"]);
  $maccol=$_POST["maccol"];
  //$codecol=$_POST["codecol"];
  //$serialcol=$_POST["serialcol"];
  $type = $_POST["type"];
//  $enter_num  = $_POST["num"];

  for ($i = 0; $i < $total_uploads; $i++) {
      $new_file = $_FILES['file'.$i];
      $file_name = $new_file['name'];
      $file_name = str_replace(")","_",str_replace("(","_",str_replace(' ', '_', $file_name)));
      $file_tmp = $new_file['tmp_name'];
//debugadmin, manual search
      if (!is_uploaded_file($file_tmp)) {
          $msg_err="There is no file Updated!<br />";
          if (isset($_POST["macsubstr"]) ){ //new feature
              if ($_POST["txt"]=="1")  
              {
                $filepath = DL_PATH.$d.".txt";
                $msg_dl .= "<a href='". dirname($_SERVER['REQUEST_URI']).$filepath."' download>Download {$filepath}</a><br>";
              }
              if ( ($_POST["xls"]=="1") and copy("template.xls", HOME_PATH.DL_PATH.$d.".xls") ) {
                  $filepathE = DL_PATH.$d.".xls";
                  $msg_dl   .= "<a href='". dirname($_SERVER['REQUEST_URI']).$filepathE."' download>Download Excel {$filepathE}</a><br>";
              }
              if ( ($_POST["xlsreport"]=="1") and copy("template.xls", HOME_PATH.DL_PATH.$d."report.xls") ) {
                  $filepathER = DL_PATH.$d."report.xls";
                  $msg_dl   .= "<a href='". dirname($_SERVER['REQUEST_URI']).$filepathER."' download>Download Excel Report{$filepathER}</a><br>";
              }
              if ($_POST["macsubstr"]!="")
                $msg_err=getCodeList("Mac",$_POST["macsubstr"],$type);
              else if ($_POST["notesubstr"]!="")
                $msg_err=getCodeList("Filename",$_POST["notesubstr"],$type);                              
          }
      }else{
        $ext = strrchr($file_name,'.');
        if (move_uploaded_file($file_tmp,  $upload_dir."/".$file_name))
        {
            $msg_err="";
            $file_name_tmp=$file_name;
            $file=$upload_dir."/".$file_name;

          if (!in_array(strtolower($ext),$limitedext)) {
            $msg_err .= "the formate of the file is not correct<br />";
          }else if (strcmp($ext,".txt")==0){
             //# handle text file
              $row = 1;
              if (($handle = fopen($file, "r")) !== FALSE) {
                  while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                      $num = count($data);
                      $row++;
                      //if($num==5) //original licesne field is 5,  
                          $list[]=$data;
                  }
                  fclose($handle);
              }//1000 handle
              
              $last_num = sizeof($list);

              if ( ($_POST["txt"]=="1") or ($num<>5)) 
              {//original licesne field is 5, if upload mac list
                $filepath = DL_PATH.$d.".txt";
                $msg_dl .= "<a href='". dirname($_SERVER['REQUEST_URI']).$filepath."' download>Download {$filepath}</a><br>";
                $_POST["txt"] = "1";
              }
              if ( ($_POST["xls"]=="1") and copy("template.xls", HOME_PATH.DL_PATH.$d.".xls") ) {
                  $filepathE = DL_PATH.$d.".xls";
                  $msg_dl   .= "<a href='". dirname($_SERVER['REQUEST_URI']).$filepathE."' download>Download Excel {$filepathE}</a><br>";
              }
              if ( ($_POST["xlsreport"]=="1") and copy("template.xls", HOME_PATH.DL_PATH.$d."report.xls") ) {
                  $filepathER = DL_PATH.$d."report.xls";
                  $msg_dl   .= "<a href='". dirname($_SERVER['REQUEST_URI']).$filepathER."' download>Download Excel Report {$filepathER}</a><br>";
              }
                $msg_err    .= "Processed Total: {$last_num}<br>";
                $excelindex=0;
                $myArray=array("Mac"=>"MAC","Code"=>"Code",'Order_num'=>"Serial No.",'Hw'=>"Model",'filename'=>"Note");
                //var_dump($myArray);
                //Excel Report header row
                if (($filepathER!="") and file_exists(HOME_PATH.$filepathER))
                    write2Excel($filepathER,-1, $myArray);
                foreach($list as $key=>$value)
                {//$apply_mac[]=$value[0];
                  if (!preg_match('/^[a-zA-Z0-9]{12}$/', $value[0])){
                        $msg_err.= "<font color=red>MAC format error ".$value[0]."</font>";
                        //continue;
                  }else  $msg_err.=$value[0]."\t".getCode($value[0]);
                  if ( ($type!="") and ($type!="Code")){
                    //echo "write special field {$type}"; 
                    $msg_err.="\t".getValue($value[0],$type)."<br>";
                  }else $msg_err.="<br>";
                  if ($filepath!=""){
                    if (preg_match('/^[a-zA-Z0-9]{12}$/', $value[0]))
                      writeLicense2Txt($filepath,getLicenseString($value[0]) );
                  }
                  if (($filepathE!="") and file_exists(HOME_PATH.$filepathE)){
                    if (preg_match('/^[a-zA-Z0-9]{12}$/', $value[0]))
                      writeLicense2Excel($filepathE,$excelindex, $value[0]);
                  }
                  if (($filepathER!="") and file_exists(HOME_PATH.$filepathER)){
                    if (preg_match('/^[a-zA-Z0-9]{12}$/', $value[0])) {
                      getLicenseArray($value[0],$myArray);
                      write2Excel($filepathER,$excelindex, $myArray);
                    }
                  }
                  $excelindex ++;
                }
                
          }else{
                $inputFileType = PHPExcel_IOFactory::identify($file);
              $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                $objReader->setReadDataOnly(true);
                $objPHPExcel    = $objReader->load($file);
              //check column

                $objWorksheet   = $objPHPExcel->setActiveSheetIndex($sheetIndex);
                $last_num   = $objPHPExcel->setActiveSheetIndex($sheetIndex)->getHighestRow();
              $header_row =intval($_POST["header_row"]);
                $last_num   = $last_num-$header_row;

                /*if($enter_num == "")
                    $msg_err    .= "Please enter the License Qty's";
                $last_num=$enter_num;
              */
              if (isset($_POST["txt"]) ){
                $filepath = DL_PATH.$d.".txt";
                $msg_dl .= "<a href='". dirname($_SERVER['REQUEST_URI']).$filepath."' download>Download {$filepath}</a><br>";
                $_POST["txt"] = "1";
              }
              if ( ($_POST["xls"]=="1") and copy("template.xls", HOME_PATH.DL_PATH.$d.".xls") ) {
                  $filepathE = DL_PATH.$d.".xls";
                  $msg_dl   .= "<a href='". dirname($_SERVER['REQUEST_URI']).$filepathE."' download>Download Excel {$filepathE}</a><br>";
              }
              if ( ($_POST["xlsreport"]=="1") and copy("template.xls", HOME_PATH.DL_PATH.$d."report.xls") ) {
                  $filepathER = DL_PATH.$d."report.xls";
                  $msg_dl   .= "<a href='". dirname($_SERVER['REQUEST_URI']).$filepathER."' download>Download Excel Report {$filepathER}</a><br>";
              }
              $msg_err  .= "Processed Total: {$last_num}<br>";
              $excelindex = 0;
                    for($i=$header_row+1;$i<$last_num+$header_row+1;$i++)
                    {
                        $mac    = $objPHPExcel->getActiveSheet()->getCell("{$maccol}{$i}")->getValue();
                   //$apply_mac[] = $mac;
                     if (!preg_match('/^[a-zA-Z0-9]{12}$/', $mac)){
                            $msg_err.= "MAC format error ".$mac."<br>";
                            //continue;
                   }else  $msg_err.=$mac."\t".getCode($mac);
                  if ( ($type!="")or ($type!="Code"))
                      $msg_err.=$mac."\t".getValue($mac,$type)."<br>";
                  else $msg_err.="<br>";
                  if (($filepath!="") and $_POST["txt"] == "1")
                      writeLicense2Txt($filepath,getLicenseString($mac) );
                  if (($filepathE!="") and file_exists(HOME_PATH.$filepathE)){
                    writeLicense2Excel($filepathE,$excelindex, $mac);
                  }
                  if (($filepathER!="") and file_exists(HOME_PATH.$filepathER)){
                    write2Excel($filepathER,$excelindex, getLicenseArray($mac,$myArray));
                  }
                  $excelindex++;
              }//for
          }//if fileext
        }else $msg_err.="<BR>{$file_name} fail to upload<br/>";
       }//if upload
     }//for totalupload
     

}//if newmac submit

function getCodeList($field,$param,$type) //Mac, Filename
{//Mac and Code is necessary field
global  $filepath,$filepathE,$filepathER;
  $msg ="";
  if (($type=="") or ($type=="Code"))
      $sql = "select Mac, Code from licservice.qlicense where {$field} like '%{$param}%'";
  else
      $sql = "select Mac, Code, {$type} from licservice.qlicense where {$field} like '%{$param}%'";    
  sql($sql,$result,$num,0);
  if ($result and ($num>0)) {
    $msg.="Total: {$num}<br>";
    //Excel Report header row
    if ($filepathER!=""){
        $excelindex=0;
        $myArray=array("Mac"=>"MAC","Code"=>"Code",'Order_num'=>"Serial No.",'Hw'=>"Model",'filename'=>"Note");
        write2Excel($filepathER,-1, $myArray);
    }
    for ($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      if ( ($type=="Code") or ($type==""))
        $msg.= $arr['Mac']."\t".$arr['Code']."<br>";
      else  $msg.= $arr['Mac']."\t".$arr['Code']."\t".$arr[$type]."<br>";
      if ($filepath!="")
        writeLicense2Txt($filepath,getLicenseString($arr['Mac']) );
      if ($filepathE!="")
        writeLicense2Excel($filepathE,$i, $arr['Mac']);
      if ($filepathER!=""){
        getLicenseArray($arr['Mac'],$myArray);
        write2Excel($filepathER,$excelindex, $myArray);
        $excelindex++;
      }
      
    }
    return $msg;
  }else return "No Exist!!";
}

function getCode($mac)
{
   $sql = "select Code from licservice.qlicense where Mac='{$mac}'";
   sql($sql,$result,$num,0);
   if ($result and ($num>0)) {
      fetch($arr,$result,0,0);
      return $arr['Code'];
   }else return "No Exist!!";
}

function getValue($mac, $type)
{
   $sql = "select {$type} from licservice.qlicense where Mac='{$mac}'";
   sql($sql,$result,$num,0);
   if ($result and ($num>0)) {
      fetch($arr,$result,0,0);
      return $arr[$type];
   }else return "No Exist!!";
}

function getLicenseArray($mac,&$dataArray)
{
   $sql = "select Mac,Code,Hw,Order_num,filename from licservice.qlicense where Mac = '{$mac}'";
   sql($sql,$result,$num,0);
   if ($result and ($num>0)) {
      fetch($dataArray,$result,0,0);
   }
}

function getLicenseString($mac)
{
   $sql = "select Code,CID,PID from licservice.qlicense where Mac='{$mac}'";
   sql($sql,$result,$num,0);
   if ($result and ($num>0)) {
      fetch($arr,$result,0,0);
      $hash = hash4($arr[CID],$arr[PID],$mac,$arr[Code]);
      return $mac.",".$arr[Code].",".$arr[CID].",".$arr[PID].",".$hash;
   }else return "No Exist!!";
}

function getValueByArray($macArr, $type, &$typeArr)
{
  $param=" where (";
  foreach($macArr as $mac) {
	if (!preg_match('/^[a-zA-Z0-9]{12}$/', $mac)){
	      continue;
	}
    $param.= " Mac='$mac' OR";
  }
  $param=rtrim($param," OR");
  $param.=" )";
   $sql = "select {$type} from licservice.qlicense {$param}'";
   sql($sql,$result,$num,0);
   if ($result and ($num>0)) {
      fetch($typeArr,$result,0,0);
   }else $typeArr = NULL;
}

function write2Excel($filepath,$index,$Array)
{
//var_dump($Array);
    $index = $index + 3; //initial index is 0
    $inputFileType = PHPExcel_IOFactory::identify(HOME_PATH.$filepath);
    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    //$objReader->setReadDataOnly(true);
    $objPHPExcel    = $objReader->load(HOME_PATH.$filepath);
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->SetCellValue("B{$index}", $Array[Mac]);
    $objPHPExcel->getActiveSheet()->SetCellValue("C{$index}", $Array[Code]); 
    $objPHPExcel->getActiveSheet()->SetCellValue("D{$index}", $Array[Order_num]);
    $objPHPExcel->getActiveSheet()->SetCellValue("E{$index}", $Array[Hw]);
    $objPHPExcel->getActiveSheet()->SetCellValue("F{$index}", $Array[filename]);
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $inputFileType);
    $objWriter->save(HOME_PATH.$filepath);
    return true;      
}

function writeLicense2Excel($filepath,$index,$mac)
{//$filepath = DL_PATH.$d.".xls";
    $index = $index + 3; //initial index is 0
    $inputFileType = PHPExcel_IOFactory::identify(HOME_PATH.$filepath);
    $objReader = PHPExcel_IOFactory::createReader($inputFileType);
    //$objReader->setReadDataOnly(true);
    $objPHPExcel    = $objReader->load(HOME_PATH.$filepath);
    $objPHPExcel->setActiveSheetIndex(0);
    $objPHPExcel->getActiveSheet()->SetCellValue("B{$index}", $mac);
    $objPHPExcel->getActiveSheet()->SetCellValue("C{$index}", getCode($mac)); 
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, $inputFileType);
    $objWriter->save(HOME_PATH.$filepath);
    return true;      
}

function writeLicense2Txt($filepath,$string)
{
//byItem 1=MAC
//byItem 2=code
//byItem 3=Serial (Order_num)
//byItem 4=Model (Hw)
//byItem 5=Note (filename)
//byItem 6=CID

  //$d=date("YmdHi");
  //open file
  //$filepath = DL_PATH.$d.".txt";
  $h=fopen(HOME_PATH.$filepath,"a+");
  $res=fwrite($h,$string."\n");
//echo $res;
  fclose($h);
}

if (empty($maccol)) $maccol="B";
if (empty($header_row)) $header_row="2";
?>
 
<div align=center><b><font size=5>Export Camera License</font></b></div>
<div align=center><font size=3>(<a href='exampleq.txt'>license txt</a> and <a href='example.xls'>excel</a> format supported)</font></div>
<div class=bg_mid>
<div class=content>
<?php
echo "<form enctype=\"multipart/form-data\" method=post action=\"".$_SERVER['PHP_SELF']."\">";
?>
<table class=table_main>
<tr class=topic_main>
<td>Excel Info</td>
<td>Please select file</td></tr>
<tr><td>
Sheet Index <input type=text size=3 name=sheet_index value=0> (0,1,2..)<br>
Header lines:<input type=text  size=1 name=header_row value='<?php echo $header_row;?>'><br>
MAC <input type=text  size=1 name=maccol value='<?php echo $maccol;?>'><br>
Query <select name=type value=""><option>Code</option>
<option value=Filename <?php if ($type=="filename") echo "selected";?>>Note</option>
<option value=Hw <?php if ($type=="Hw") echo "selected";?>>Model</option></select>
<br>File: <input type='hidden' name='txt' value='0' />
<input type='checkbox' name='txt' value='1' <?php if ($_POST["txt"]=="1") echo "checked";?>/>TXT
&nbsp;&nbsp;<input type='hidden' name='xls' value='0' />
<input type='checkbox' name='xls' value='1' <?php if ($_POST["xls"]=="1") echo "checked";?>/>EXCEL
<?php
if (isset($_REQUEST[debugadmin]) ){
echo "&nbsp;&nbsp;<input type='hidden' name='xlsreport' value='0' />";
if ($_POST["xlsreport"]=="1")
  echo "<input type='checkbox' name='xlsreport' value='1' checked />REPORT";
else
  echo "<input type='checkbox' name='xlsreport' value='1' />REPORT";
echo "<br>MAC SubString <input type=text  size=10 name=macsubstr value='{$_REQUEST['macsubstr']}'><br>";
echo "<br>Note SubString <input type=text  size=10 name=notesubstr value='{$_REQUEST['notesubstr']}'><br>";
echo "<input type=hidden name=debugadmin value=1>";
}
?>
</td>
<td>
<input name=file0 type=file >
<br><input type=submit value=Submit class=btn_1>
<input type=hidden name=step value=new_with_mac>
</td></tr></table>
</form>
<HR>
<table><tr><td>
<?php
if ($msg_err!=""){
  echo "<font color=blue size=1>".$msg_err;
  echo "</font>";
}else{
  echo "<font color=black size=3>";
  echo "Excel ex.<img src='excel1.png'>";
}
?>
</td><td valign=top>
<?php
if ($msg_dl!=""){
  echo "<font color=black size=2>".$msg_dl;
  echo "</font>";
}
?>
</td></tr>
</table>
</body>
</html>