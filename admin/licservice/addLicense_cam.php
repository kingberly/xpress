<?php
/****************
 *Validated on Feb-14,2017   
 * management camera license for logistic
 * Update CID if different
 * Add two row txt file added with cidinput
 * add PID MC    
 *Writer: JinHo, Chang   
*****************/
ini_set('memory_limit','64M');
require_once '../billing/Classes/PHPExcel.php';
require_once '../billing/Classes/PHPExcel/IOFactory.php';
require_once '_auth_.inc';
if (!$_SESSION['ID_admin_qlync']) exit();
$countPASS=0;
$countFAIL=0;
$upload_dir="/var/tmp";

if($_POST["step"]=="new_with_mac" )
{
  $total_uploads = 1;
  $limitedext = array(".xls",".xlsx",".txt");

  $sheetIndex=intval($_POST["sheet_index"]);
  $maccol=$_POST["maccol"];
  $codecol=$_POST["codecol"];
  $serialcol=$_POST["serialcol"];
  $modelinput=$_POST["modelinput"];
  $noteinput=$_POST["noteinput"];
  $enter_num	= $_POST["num"];
  $cidinput=$_POST["sheet_cid"];
  $pidinput=$_POST["sheet_pid"];
  if ( ($maccol =="") or ($codecol =="") or ($serialcol =="") 
       or ($modelinput =="") or ($noteinput =="") or ($enter_num =="")
       or ($cidinput =="") or ($pidinput =="") )  
      $msg_err="Necessary Field is blank! <br />";
  for ($i = 0; $i < $total_uploads; $i++) {
      if ($msg_err !="") break;
      $new_file = $_FILES['file'.$i];
      $file_name = $new_file['name'];
      $file_name = str_replace(")","_",str_replace("(","_",str_replace(' ', '_', $file_name)));
      $file_tmp = $new_file['tmp_name'];

      if (!is_uploaded_file($file_tmp)) {
          $msg_err="There is no file Updated!<br />";
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
              $row = 1;$txtMode=5;
              if (($handle = fopen($file, "r")) !== FALSE) {
                  while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                      $num = count($data);
                      $row++;
                      if ($num==5) $txtMode=5;
                      else if ($num==2) $txtMode=2;
                      if (($num==5) or ($num==2))  $list[]=$data;
                  }
                  
                  fclose($handle);
              }//1000 handle
              $last_num = sizeof($list);
            	$enter_num=$_POST["num"];
          		if($enter_num == "")
          			$msg_err	.= "Please enter the License Qty's";
          		//if($enter_num <>  $last_num and $enter_num <> "")
              //    $msg_err        = "Please enter the correct Qty's with your Upload File";
                $last_num=$enter_num;

   			foreach($list as $key=>$value)
              {
                $apply_mac[]=$value[0];
                $apply_lic[]=$value[1];
                if ($txtMode==2){
                $apply_cid[]=$cidinput;
                $apply_pid[]=$pidinput;
                $apply_hash[]=hash4($cidinput,$pidinput,$value[0],$value[1]);

                }else{
                $apply_cid[]=$value[2];
                $apply_pid[]=$value[3];
                $apply_hash[]=$value[4];
                }
                $apply_status[]="";
                  //check mac and code
		     if (!preg_match('/^[a-zA-Z0-9]{12}$/', $value[0]))
                  $msg_err.= "MAC format error ".$value[0]."<br>";
		     if (!preg_match('/^[a-zA-Z0-9]{12}$/', $value[1]))
                  $msg_err.= "Activation code format error ".$value[1]."<br>";
              }
          }else{
              # Excel
             if (!preg_match('/^[A-Z0-9]{3}$/', $cidinput))
                      $msg_err.= "CID format error ".$cidinput."<br>";
			 if (!ereg("[M,C]{2}",$pidinput))
                      $msg_err.= "PID format error ".$pidinput."<br>";

          		$inputFileType = PHPExcel_IOFactory::identify($file);
              $objReader = PHPExcel_IOFactory::createReader($inputFileType);
          		$objReader->setReadDataOnly(true);
          		$objPHPExcel 	= $objReader->load($file);
              //check column

          		$objWorksheet 	= $objPHPExcel->setActiveSheetIndex($sheetIndex);
          		$last_num	= $objPHPExcel->setActiveSheetIndex($sheetIndex)->getHighestRow();
                    $header_row =intval($_POST["header_row"]);
          		$last_num	= $last_num-$header_row;

          		if($enter_num == "")
          			$msg_err	.= "Please enter the License Qty's";

          		//if($enter_num <> $last_num and $enter_num <> "")
                  //$msg_err        = "Please enter the correct Qty's with your Upload File ".$last_num;
                  $last_num=$enter_num;

        			for($i=$header_row+1;$i<$last_num+$header_row+1;$i++)
        			{
                        if ($msg_err!="") break;
        				$mac 	= $objPHPExcel->getActiveSheet()->getCell("{$maccol}{$i}")->getValue();
                        $apply_mac[] = $mac;
                         $code = $objPHPExcel->getActiveSheet()->getCell("{$codecol}{$i}")->getValue();
        				$apply_lic[] = $code;
                          $apply_cid[]=$cidinput; //changed for input value
                          $apply_pid[]=$pidinput;
                          $apply_serial[]=$objPHPExcel->getActiveSheet()->getCell("{$serialcol}{$i}")->getValue();
                          $apply_status[]="";
                            // echo "{$mac},{$objPHPExcel->getActiveSheet()->getCell("C{$i}")->getValue()}, {$objPHPExcel->getActiveSheet()->getCell("F{$i}")->getValue()},";
          		     if (!preg_match('/^[a-zA-Z0-9]{12}$/', $mac))
                            $msg_err.= "MAC format error ".$mac."<br>";
          		     if (!preg_match('/^[a-zA-Z0-9]{12}$/', $code))
                            $msg_err.= "Activation code format error ".$code."<br>";

              }//for
          }//if fileext
        }else $msg_err.="<BR>{$file_name} fail to upload<br/>";
       }//if upload
     }//for totalupload
     //If success perform database update
     if ($msg_err ==""){
          $link = lic_getLink();
          foreach($apply_mac as $key=>$value){
                 if (strcmp($ext,".txt")==0){
                   if (strcmp($apply_hash[$key],hash4($apply_cid[$key],$apply_pid[$key],$value,$apply_lic[$key]))!=0)
                       $apply_status[$key] ="Warning! hash error.";
                 }
                 $sql="select * from licservice.qlicense where Mac='{$value}' or Binary Code='{$apply_lic[$key]}'";
                 $result_qmac=mysql_query($sql,$link);
                 $result_num=mysql_num_rows($result_qmac);
                 if ($result_num>0){
                     $apply_status[$key] .="duplicated.";
                     //update serial number
                     if ((strcmp($ext,".txt")!=0) and ($apply_serial[$key]!="") and mysql_result($result_qmac,0,'Order_num')==""){
                        if (ereg("[0-9]{10}",$apply_serial[$key])){
                            $sql="update licservice.qlicense set Order_num='{$apply_serial[$key]}' where Mac='{$value}'";
                            $result=mysql_query($sql,$link);
                            if(!$result)  $apply_status[$key].= "upadte serial fail.";
                            else $apply_status[$key].= "<font color=#0000FF size=4>updated serial.</font>";
                        }else $apply_status[$key].= "serial format error {$apply_serial[$key]}.";
                     }//excel update only

                 }else{ //insert
                     if (strcmp($ext,".txt")==0)
                       $sql="insert into licservice.qlicense(Mac,Code,CID,PID) select * from (select '{$value}','{$apply_lic[$key]}','{$apply_cid[$key]}','{$apply_pid[$key]}') as tmp where not exists ( select Mac,Code from qlicense where Mac='{$value}' or Binary Code='{$apply_lic[$key]}') limit 1";
                     else
                       $sql="insert into licservice.qlicense(Mac,Code,CID,PID,Order_num) select * from (select '{$value}','{$apply_lic[$key]}','{$apply_cid[$key]}','{$apply_pid[$key]}','{$apply_serial[$key]}') as tmp where not exists ( select Mac,Code from qlicense where Mac='{$value}' or  Binary Code='{$apply_lic[$key]}') limit 1";
                     $result=mysql_query($sql,$link);
                    	if(!$result) { 
                        $apply_status[$key] .="add fail.";
                        $countFAIL++;
                      }else{
                       $apply_status[$key].="<font color=#0000FF size=4>success.</font>";
                        $countPASS++;
                      }
                     //echo "{$value},{$apply_lic[$key]},{$apply_serial[$key]} ";

                 }
                 //update note and model if had value and db blank
                 if (($modelinput!="") and (mysql_result($result_qmac,0,'Hw')=="")){
                       $sql="update licservice.qlicense set Hw='{$modelinput}' where Mac='{$value}'";
                       $result=mysql_query($sql,$link);
                       if(!$result)  $apply_status[$key].= "update model fail.";
                       else $apply_status[$key].= "<font color=#0000FF size=4>updated model.</font>";

                  }
                  if (($noteinput!="")and (mysql_result($result_qmac,0,'filename')=="")){
                       $sql="update licservice.qlicense set filename='{$noteinput}' where Mac='{$value}'";
                       $result=mysql_query($sql,$link);
                       if(!$result)  $apply_status[$key].="update note fail.";
                       else $apply_status[$key].= "<font color=#0000FF size=4>updated note.</font>";

                  }
                 //update CID
                 if (($apply_cid[$key]!="")){
                      $sql="update licservice.qlicense set CID='".$apply_cid[$key]."' where Mac='{$value}'";
                      $result=mysql_query($sql,$link);
                      if(!$result)  $apply_status[$key].="update CID fail.";
                      else $apply_status[$key].= "<font color=#0000FF size=4>updated CID {$apply_cid[$key]}.</font>";
                 }
             $msg_err .= "<font color=black size=4>MAC " .$value . "</font> " .$apply_status[$key]. "<BR>";
          }//foreach
          $msg_err = "Total Qty".count($apply_mac).". Add Qty {$countPASS}, Add FAIL Qty {$countFAIL}<BR>" .$msg_err;
     }
}//if newmac submit

if (empty($maccol)) $maccol="B";
if (empty($codecol)) $codecol="C";
if (empty($serialcol)) $serialcol="F";
if (empty($header_row)) $header_row="2";

##############################
echo "<div align=center><b><font size=5>Upload/<a href='inputLicense_cam.php'>Input</a> <a href='listLicensePage.php'>Camera License</a></font></b></div>";
echo "<div align=center><font size=3>(<a href='example.txt'>license txt</a> and <a href='example.xls'>excel</a> format supported)</font></div>";
echo "<div class=bg_mid>";
echo "<div class=content>";
echo "<form enctype=\"multipart/form-data\" method=post action=\"".$_SERVER['PHP_SELF']."\">";
echo "<table class=table_main>";
echo "<tr class=topic_main>";
echo "<td>Excel Sheet/Qty Info</td>";
echo "<td>Excel CID / PID</td>";
echo "<td>Excel Column# Info</td>";
echo "<td>Please select file</td>";
echo "</tr>";
echo "<tr class=tr_2>";
echo "<td>";
echo "Sheet Index <input type=text size=3 name=sheet_index value=0> (0,1,2..)<br>";
echo "Qty<input type=text size=3 name=num value=10>";
echo "</td>";
echo "<td>";
echo "CID / PID <input type=text size=3 name=sheet_cid value=Z01>";
//echo "<input type=text size=1 name=sheet_pid value=CC readonly style='background-color:grey;'><br>";
echo "<select name=sheet_pid><option value='CC'>CC</option><option value='MC'>MC</option></select><br>"; 
echo "<small>Z01:Zavio, M04:Messoa,<br>B03:Baycom, R01:Raylios, Xnn:IvedaMobile per site;CC:Camera,MC:IvedaMobile</small>";
echo "</td>";
echo "<td>";
echo "Header lines:<input type=text  size=1 name=header_row value='{$header_row}'><br>";
echo "MAC <input type=text  size=1 name=maccol value='{$maccol}'> ";
echo "Code <input type=text  size=1 name=codecol value='{$codecol}'> <br>";
echo "Serial# <input type=text  size=1 name=serialcol value='{$serialcol}'> ";
echo "</td>";
echo "<td>";
echo "<input name=file0 type=file >";
echo "</td>";
echo "</tr><tr class=tr_2>";
echo "<td>";
echo "MODEL: <input name=modelinput type=text size=10><br>ex:Z3201PT";
echo "</td>";
echo "<td colspan=2>";
echo "NOTE: <input name=noteinput type=text size=30><br>ex: PLDT shipment";
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
echo "</font>";
if ($msg_err==""){
        echo "<font color=black size=3>";
        echo "Excel ex.<img src='excel1.png'>";
        echo "<img src='excel2.png'></font>";
}
?>