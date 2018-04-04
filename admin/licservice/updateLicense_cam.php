<?php
/****************
 *Validated on Feb-14,2017 
 * Update camera license Info from text or excel
 * Update CID for debugadmin feature
 * fixed necessary field if statement
 * add PID MC   
 *Writer: JinHo, Chang   
*****************/
ini_set('memory_limit','64M');
require_once '../billing/Classes/PHPExcel.php';
require_once '../billing/Classes/PHPExcel/IOFactory.php';
require_once '_auth_.inc';
if (!$_SESSION['ID_admin_qlync']) exit();

$upload_dir="/var/tmp";

if($_POST["step"]=="new_with_mac" )
{
  $total_uploads = 1;
  $limitedext = array(".xls",".xlsx",".txt");

  $sheetIndex=intval($_POST["sheet_index"]);
  $maccol=$_POST["maccol"];

  $serialcol=$_POST["serialcol"];
  $modelinput=$_POST["modelinput"];
  $noteinput=$_POST["noteinput"];
  $enter_num=$_POST["num"];
  $cidinput=$_POST["sheet_cid"];
  $pidinput=$_POST["sheet_pid"];

  if ( ($maccol =="")  or ($cidinput =="") or ($pidinput =="") 
    or ( ($modelinput !="") and ($noteinput !="") and  ($seialcol!="") ) )  
      $msg_err="Necessary Field is blank! <br />";

  for ($i = 0; $i < $total_uploads; $i++) {
      if ($msg_err !="") break;
      $new_file = $_FILES['file'.$i];
      $file_name = $new_file['name'];
      $file_name = str_replace(")","_",str_replace("(","_",str_replace(' ', '_', $file_name)));
      $file_tmp = $new_file['tmp_name'];
//echo "upload file";
      if (!is_uploaded_file($file_tmp)) {
          $msg_err="There is no file Updated!<br />";
      }else{
        $ext = strrchr($file_name,'.');
        if (move_uploaded_file($file_tmp,  $upload_dir."/".$file_name))
        {
            $msg_err="";
            $file_name_tmp=$file_name;
            $file=$upload_dir."/".$file_name;

         if (!preg_match('/^[A-Z0-9]{3}$/', $cidinput))
                  $msg_err.= "CID format error ".$cidinput."<br>";
         if (!ereg("[M,C]{2}",$pidinput))
                  $msg_err.= "PID format error ".$pidinput."<br>";
          if (!in_array(strtolower($ext),$limitedext)) {
            $msg_err .= "the formate of the file is not correct<br />";
          }else if (strcmp($ext,".txt")==0){
             //# handle text file
              $row = 1;
              if (($handle = fopen($file, "r")) !== FALSE) {
                  while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                      $num = count($data);
                      $row++;
                      if($num==5)  $list[]=$data;
                  }
                  fclose($handle);
              }//1000 handle
              $last_num = sizeof($list);

                if($enter_num == "") //use last_num instead
                    $msg_err    .= "Please enter the License Qty's";
              else{
                  if($enter_num <>  $last_num )
                    $msg_err .= "Current text file holds {$last_num} license instead of {$enter_num}";
              }

            foreach($list as $key=>$value)
              {
                $apply_mac[]=$value[0];
                $apply_lic[]=$value[1];
                $apply_cid[]=$value[2];
                $apply_pid[]=$value[3];
                $apply_hash[]=$value[4];
                $apply_status[]="";
                  //check mac 12 digits
                if(strlen(trim(str_replace("-","",str_replace(":","",$value[0])))) <> 12)
                  $msg_err.= "MAC error ".$value[0]."<br>";
              }
          }else{
              # Excel
                $inputFileType = PHPExcel_IOFactory::identify($file);
              $objReader = PHPExcel_IOFactory::createReader($inputFileType);
                $objReader->setReadDataOnly(true);
                $objPHPExcel    = $objReader->load($file);
              //check column

                $objWorksheet   = $objPHPExcel->setActiveSheetIndex($sheetIndex);
                $last_num   = $objPHPExcel->setActiveSheetIndex($sheetIndex)->getHighestRow();
              $header_row =intval($_POST["header_row"]);
              $last_num = $last_num-$header_row;
                if($enter_num == "")
                    $msg_err    .= "Please enter the License Qty's";
                //if($enter_num <> $last_num and $enter_num <> "")
                  //$msg_err        = "Please enter the correct Qty's with your Upload File ".$last_num;
              else  $last_num=$enter_num;
                
              for($i=$header_row+1;$i<$last_num+$header_row+1;$i++)
              {
                  if ($msg_err!="") break;
                  $mac    = $objPHPExcel->getActiveSheet()->getCell("{$maccol}{$i}")->getValue();
                  $apply_mac[] = $mac;

                    $apply_cid[]=$cidinput; //changed for input value
                    $apply_pid[]=$pidinput;
                    if ($serialcol !="") 
                        $apply_serial[]=$objPHPExcel->getActiveSheet()->getCell("{$serialcol}{$i}")->getValue();
                    $apply_status[]="";
                    if(strlen(trim(str_replace("-","",str_replace(":","",$mac)))) <> 12)
                       $msg_err.= "row{$i} MAC error ".$mac."<br>";
                   //$regs = array();
               if (!preg_match('/^[a-zA-Z0-9]{12}$/', $mac))
                      $msg_err.= "MAC format error {$mac}(".strlen($mac).")<br>";
               
              }//for
          }//if fileext
        }else $msg_err.="<BR>{$file_name} fail to upload<br/>";
       }//if upload
     }//for totalupload
     //If success perform database update
     if ($msg_err ==""){
          $link = lic_getLink();
          foreach($apply_mac as $key=>$value){
             $sql="select * from licservice.qlicense where Mac='{$value}'";
             $result_qmac=mysql_query($sql,$link);
             $result_num=mysql_num_rows($result_qmac);
             if ($result_num>0){
                 //$apply_status[$key] .="duplicate error.";
                 //update serial number
                 if ((strcmp($ext,".txt")!=0) and ($serialcol!="") and ($apply_serial[$key]!="")){
                    if (ereg("[0-9]{10}",$apply_serial[$key])){
                        $sql="update licservice.qlicense set Order_num='{$apply_serial[$key]}' where Mac='{$value}'";
                        $result=mysql_query($sql,$link);
                        if(!$result)  $apply_status[$key].= "update serial fail.";
                        else $apply_status[$key].= "<font color=#0000FF size=4>updated serial.</font>";
                    }else $apply_status[$key].= "serial format error {$apply_serial[$key]}.";
                 }//excel update only
                   if (($pidinput!="")){
                        $sql="update licservice.qlicense set PID='".$pidinput."' where Mac='{$value}'";
                        $result=mysql_query($sql,$link);
                        if(!$result)  $apply_status[$key].="update PID fail.";
                        else $apply_status[$key].= "<font color=#0000FF size=4>updated PID.</font>";
                   }
                   //update CID for debugadmin only
                   if (isset($_REQUEST[debugadmin]) ){
                   if (($cidinput!="")){
                        $sql="update licservice.qlicense set CID='".$cidinput."' where Mac='{$value}'";
                        $result=mysql_query($sql,$link);
                        if(!$result)  $apply_status[$key].="update CID fail.";
                        else $apply_status[$key].= "<font color=#0000FF size=4>updated CID.</font>";
                   }
                   }//debugadmin
                  //update note and model
                  if (($modelinput!="")){
                        $sql="update licservice.qlicense set Hw='{$modelinput}' where Mac='{$value}'";
                        $result=mysql_query($sql,$link);
                        if(!$result)  $apply_status[$key].= "update model fail.";
                        else $apply_status[$key].= "<font color=#0000FF size=4>updated model.</font>";

                   }
                   if (($noteinput!="")){
                        $sql="update licservice.qlicense set filename='{$noteinput}' where Mac='{$value}'";
                        $result=mysql_query($sql,$link);
                        if(!$result)  $apply_status[$key].="update note fail.";
                        else $apply_status[$key].= "<font color=#0000FF size=4>updated note.</font>";

                   }

             }else{ //NO insert

                 $apply_status[$key] .="MAC is not exited for updating.";
                 
             }

         $msg_err .= "<font color=black size=4>MAC " .$value . "</font> " .$apply_status[$key]. "<BR>";
          }//foreach
          $msg_err = "Total Qty".count($apply_mac)."<BR>" .$msg_err;
     }
}//if newmac submit

if (empty($maccol)) $maccol="B";
//if (empty($codecol)) $codecol="C";
if (empty($serialcol)) $serialcol="F";
if (empty($header_row)) $header_row="2";
if (empty($modelinput)) $modelinput="";
if (empty($noteinput)) $noteinput="";

##############################
echo "<div align=center><b><font size=5>Update Camera License <a href='listLicensePage.php?debugadmin'>CID</a>/PID/Serial/MODEL/NOTE</font></b></div>";
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
echo "Sheet Index: <input type=text size=3 name=sheet_index value=0> (0,1,2..)<br>";
echo "Qty<input type=text size=3 name=num value=10  style='background-color:hotpink;'>";
echo "</td>";
echo "<td>";
if (isset($_REQUEST[debugadmin]) ){
  echo "CID / PID <input type=text size=3 name=sheet_cid value=Z01 style='background-color:pink;'>";
  echo "<input type=hidden name=debugadmin value=1>";
}else{
  echo "CID / PID <input type=text size=3 name=sheet_cid value=Z01 readonly style='color:grey;background-color:grey;'>";
}
//echo "<input type=text size=1 name=sheet_pid value=CC readonly  style='background-color:grey;'><br>";
echo "<select name=sheet_pid style='background-color:pink;'><option value='CC'>CC</option><option value='MC'>MC</option></select><br>"; 
echo "<small>Z01:Zavio, M04:Messoa,<br>B03:Baycom, R01:Raylios, Xnn:IvedaMobile per site;CC:Camera,MC:IvedaMobile</small>";
echo "</td>";
echo "<td>";
echo "Header lines:<input type=text  size=1 name=header_row value='{$header_row}'><br>";
echo "MAC <input type=text  size=1 name=maccol value='{$maccol}'> <br>";
//echo "Code <input type=text  size=1 name=codecol value='{$codecol}'> <br>";
echo "<font color=red>Serial# <input type=text  size=1 name=serialcol value='{$serialcol}' style='background-color:hotpink;'></font>";
echo "</td>";
echo "<td>";
echo "<input name=file0 type=file >";
echo "</td>";
echo "</tr><tr class=tr_2>";
echo "<td bgcolor=pink>";
echo "MODEL: <input name=modelinput type=text size=10 value='{$modelinput}'><br>ex:Z3201PT";
echo "</td>";
echo "<td bgcolor=pink colspan=2>";
echo "NOTE: <input name=noteinput type=text size=30 value='{$noteinput}'><br>ex: PLDT shipment";
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
?>