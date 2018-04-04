<?php
/****************
 *Validated on Apr-26,2016,  
 * Input camera license Manually
 * Fix code case-sensitive insert issue again (BINARY Code)
 *Writer: JinHo, Chang   
*****************/
require_once '_auth_.inc';
$upload_dir="/var/tmp";

if($_POST["step"]=="new_with_mac" )
{

        $apply_mac=$_POST["mac"];
        $apply_lic=$_POST["code"];
        $apply_cid=$_POST["cid"];
        $apply_pid=$_POST["pid"];
        $apply_serial=$_POST["serial"];        
        $apply_model=$_POST["model"];
        $apply_note=$_POST["note"];
        $apply_status="";
		     if (!preg_match('/^[a-zA-Z0-9]{12}$/', $apply_mac))
                  $msg_err.= "MAC format error ".$apply_mac."<br>";
		     if (!preg_match('/^[a-zA-Z0-9]{12}$/', $apply_lic))
                  $msg_err.= "Activation code format error ".$apply_lic."<br>";

     //If success perform database update
     if ($msg_err ==""){
          //$link = lic_getLink();
          //binary for code case-sensitivit search
          $sql="select * from licservice.qlicense where Mac='{$apply_mac}' or BINARY Code='{$apply_lic}'";
          sql($sql,$result_qmac,$result_num,0);
         //$result_qmac=mysql_query($sql,$link);
         //$result_num=mysql_num_rows($result_qmac);
         if ($result_num>0){
             $apply_status .="duplicated.";
             //update serial number
             if (($apply_serial!="") and mysql_result($result_qmac,0,'Order_num')==""){
                  if (ereg("[0-9]{10}",$apply_serial)){
                      $sql="update licservice.qlicense set Order_num='{$apply_serial}' where Mac='{$value}'";
                      $result=mysql_query($sql,$link);
                      if(!$result)  $apply_status.= "upadte serial fail.";
                      else $apply_status.= "<font color=#0000FF size=4>updated serial.</font>";
                   }else $apply_status.= "serial format error {$apply_serial}.";
               }
        }else{ //insert
            $sql="insert into licservice.qlicense(Mac,Code,CID,PID,Order_num,Hw, Filename)VALUES ('{$apply_mac}','{$apply_lic}','{$apply_cid}','{$apply_pid}','{$apply_serial}','{$apply_model}','{$apply_note}')"; 
           //$result=mysql_query($sql,$link);
            sql($sql,$result,$num,0);
           if(!$result)  $apply_status.="add fail.";
          else $apply_status.="<font color=#0000FF size=4>success.</font>";
        }
       //update note and model if had value and db blank
       if (($apply_model!="") and (mysql_result($result_qmac,0,'Hw')=="")){
             $sql="update licservice.qlicense set Hw='{$apply_model}' where Mac='{$apply_mac}'";
             //$result=mysql_query($sql,$link);
              sql($sql,$result,$num,0);
             if(!$result)  $apply_status.= "update model fail.";
             else $apply_status.= "<font color=#0000FF size=4>updated model.</font>";

       }
        if (($apply_note!="")and (mysql_result($result_qmac,0,'filename')=="")){
             $sql="update licservice.qlicense set filename='{$apply_note}' where Mac='{$apply_mac}'";
             //$result=mysql_query($sql,$link);
              sql($sql,$result,$num,0);
             if(!$result)  $apply_status.="update note fail.";
             else $apply_status.= "<font color=#0000FF size=4>updated note.</font>";

        }

       $msg_err .= "<font color=black size=4>MAC " .$apply_mac . "</font> " .$apply_status. "<BR>";

     }
}//if newmac submit
if (empty($apply_cid)) $apply_cid ="Z01"; 
if (empty($apply_pid)) $apply_pid ="CC";
if (empty($apply_model)) $apply_model ="Z";
##############################
echo "<div align=center><b><font size=5>Input <a href='listLicensePage.php?debugadmin'>Camera License</a></font></b></div>";
echo "<div class=bg_mid>";
echo "<div class=content>";
echo "<form method=post action=\"".$_SERVER['PHP_SELF']."\">";
echo "<table class=table_main>";
echo "<tr class=topic_main>";
echo "<td>Info</td>";
echo "<td>Function</td>";
echo "</tr>";
echo "<tr class=tr_2>";
echo "<td>";
echo "MAC <input type=text  size=10 name=mac value='{$apply_mac}'> ";
echo "Code <input type=text  size=10 name=code value='{$apply_lic}'> <br>";
echo "CID <input type=text  size=1 name=cid value='{$apply_cid}'> ";
echo "PID <input type=text  size=1 name=pid value='$apply_pid'> <br>";
echo "Serial# <input type=text  size=10 name=serial value='{$apply_serial}'>";
echo "Model <input type=text  size=5 name=model value='{$apply_model}'><br>";
echo "Note <input type=text  size=15 name=note value='{$apply_note}'><br>";
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