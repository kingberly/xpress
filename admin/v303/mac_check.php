<?php
include("../../header.php");
include("../../menu.php");
#Authentication Section
if (!isset($_SESSION["Email"]) )  die("Require Login!!\n");
############  Authentication Section End
define("SCRIPT_ROOT_FOLDER","/home/ivedasuper/admin/check/");
//jinho add
$resolutionNAME=[
"RVHI"=>"Profile1 HD 30 fps",
"RVME"=>"Profile2 VGA 5 fps",
"RVLO"=>"Profile3 QVGA 5 fps"
];
$dataplanNAME=[
  "AR"=>"Always Recording+Event",
  "LV"=>"Live View",
  "D"=>"Disabled",
  "EV"=>"Event Recording",
  "SR"=>"Schedule Recording"
];
//jinho add end

#Get Data File
#    upload section
$msg_mac="";
$msg_license="";
flush($display);
if($_REQUEST["step"]=="apply_mac")
{
  $_REQUEST["mac"] = trim($_REQUEST["mac"]);//jinho fix space
//  if($_SESSION["CID"]=="T99" or $_SESSION["CID"]=="N99")
  if($_SESSION["ID_admin_qlync"]==1 or $_SESSION["ID_admin_oem_qlync"]==1 or $_SESSION["ID_fae_qlync"]==1)
  {
    $sql="select * from qlync.license where Mac='".mysql_real_escape_string($_REQUEST["mac"])."'";
  }
  else
  {
    $sql="select * from qlync.license where Mac='".mysql_real_escape_string($_REQUEST["mac"])."' and CID='".mysql_real_escape_string($_SESSION["CID"])."'";
  }
  sql($sql,$result,$num,0);
  fetch($db_mac,$result,0,0);
  $display["mac"]=mysql_real_escape_string($_REQUEST["mac"]);
  $display["license"]=$db_mac["Code"];
//  $license=$db_mac["Code"];
  if($num==1)
  {
    /*$sql="select * from qlync.account_device where Mac='".mysql_real_escape_string($_REQUEST["mac"])."'";
    sql($sql,$result,$num,0);
    if($num==1)
    {
      fetch($db_tmp,$result,0,0);
      $display["email"] =$db_tmp["Email"];
      $display["account"] =$db_tmp["Name"];
      $display["info"]  =$db_tmp["Model"]." \ ".$db_tmp["Fw"];
    }*/
    $sql="select user_name,reg_email,model,device_models_version from isat.query_info where mac_addr='{$_REQUEST["mac"]}' group by mac_addr";
    sql($sql,$result,$num,0);
    if($num==1)
    {
      fetch($db_tmp,$result,0,0);
      $display["email"] =$db_tmp["reg_email"];
      $display["account"] =$db_tmp["user_name"];
      $display["info"]  =$db_tmp["model"]." \ ".$db_tmp["device_models_version"];
    }
    //jinho add feature
    if($_SESSION["ID_admin_qlync"]){//god admin only
      $dataplan_msg="";
      if (strpos($_REQUEST["mac"],"M{$oem}")!==false)
      $sql1 ="select c1.device_uid as MAC, c1.purpose as purpose, c1.dataplan as dataplan, c1.recycle as recycle, c2.hostname as sHostname, c4.hostname as tHostname, c2.internal_address as s_internal_address, c4.internal_address as t_internal_address from isat.stream_server_assignment as c1 LEFT JOIN isat.stream_server as c2 on c1.stream_server_uid=c2.uid LEFT JOIN isat.rtmp_server_assignment as c3 on c1.device_uid=c3.device_uid LEFT JOIN isat.tunnel_server as c4 on c3.tunnel_server_uid=c4.uid where c1.device_uid like '%".$_REQUEST["mac"]."%' group by c1.device_uid";
      else 
      $sql1 ="select c1.device_uid as MAC, c1.purpose as purpose, c1.dataplan as dataplan, c1.recycle as recycle, c2.hostname as sHostname, c4.hostname as tHostname, c2.internal_address as s_internal_address, c4.internal_address as t_internal_address from isat.stream_server_assignment as c1 LEFT JOIN isat.stream_server as c2 on c1.stream_server_uid=c2.uid LEFT JOIN isat.tunnel_server_assignment as c3 on c1.device_uid=c3.device_uid LEFT JOIN isat.tunnel_server as c4 on c3.tunnel_server_uid=c4.uid where c1.device_uid like '%".$_REQUEST["mac"]."%' group by c1.device_uid"; 
      //$sql1 ="select * from isat.stream_server_assignment where device_uid like '%".$_REQUEST["mac"]."%'";
       sql($sql1,$result1,$num1,0);
        if($num1==1){
            $dataplan_msg = "<table><tr class=topic_main><td>Resolution</td><td>Package</td><td>Recycle Days</td><td>Assigned Tunnel</td><td>Assigned Stream</td><tr class=tr_1>";
            fetch($data_tmp,$result1,0,0);
            if ($data_tmp["purpose"]=="RVME") $dataplan_msg .="<td>".$resolutionNAME["RVME"]."</td>";
            else if ($data_tmp["purpose"]=="RVLO") $dataplan_msg .="<td>".$resolutionNAME["RVLO"]."</td>";
            else  $dataplan_msg .="<td>".$resolutionNAME["RVHI"]."</td>";
            if ($data_tmp["dataplan"]=="AR") $dataplan_msg .= "<td>Always Recording+Event</td>";
            else if ($data_tmp["dataplan"]=="LV") $dataplan_msg .= "<td>{$dataplanNAME['LV']}</td>";
            else if ($data_tmp["dataplan"]=="D") $dataplan_msg .= "<td>{$dataplanNAME['D']}</td>";
            else if ($data_tmp["dataplan"]=="SR") $dataplan_msg .= "<td>{$dataplanNAME['SR']}</td>";
            else if ($data_tmp["dataplan"]=="EV") $dataplan_msg .= "<td>{$dataplanNAME['EV']}</td>";
            $dataplan_msg .= "<td>".$data_tmp['recycle']."</td>";
            $dataplan_msg .= "<td>".$data_tmp['tHostname']."</td>";
            $s_status = exec ("python ".SCRIPT_ROOT_FOLDER."getStreamingStatus.py ".$data_tmp['s_internal_address']." ".strtoupper($_REQUEST["mac"]));
            $dataplan_msg .= "<td>".$data_tmp['sHostname']." ({$s_status})</td>";
            $dataplan_msg .= "</tr></table>";
        }
      }
      //jinho add feature end
  }
  else
  {
      $display["info"]  =" Searching item is not available in system";
  }
}

if($_REQUEST["step"]=="apply_license")
{
  $_REQUEST["license"] = trim($_REQUEST["license"]);//jinho fix space
  if($_SESSION["ID_admin_qlync"]==1 or $_SESSION["ID_admin_oem_qlync"]==1 or $_SESSION["ID_fae_qlync"]==1)
  {
    $sql="select * from qlync.license where binary Code='".mysql_real_escape_string($_REQUEST["license"])."' ";
  }
  else
  {
          $sql="select * from qlync.license where binary Code='".mysql_real_escape_string($_REQUEST["license"])."' and CID='".mysql_real_escape_string($_SESSION["CID"])."'";
  }
        sql($sql,$result,$num,0);
        fetch($db_license,$result,0,0);
        $display["mac"]=$db_license["Mac"];
        $display["license"]=mysql_real_escape_string($_REQUEST["license"]);

        if($num==1)
        {
                $sql="select * from qlync.account_device where Mac='".mysql_real_escape_string($db_license["Mac"])."'";
                sql($sql,$result,$num,0);
                if($num==1)
                {
                        fetch($db_tmp,$result,0,0);
                        $display["email"]       =$db_tmp["Email"];
                        $display["account"]     =$db_tmp["Name"];
                        $display["info"]        =$db_tmp["Model"]." \ ".$db_tmp["Fw"];

                }
        //jinho add feature
        if($_SESSION["ID_admin_qlync"]){//god admin only
          $dataplan_msg="";
          if (strpos($db_license["Mac"],"M{$oem}")!==false)
          $sql1 ="select c1.device_uid as MAC, c1.purpose as purpose, c1.dataplan as dataplan, c1.recycle as recycle, c2.hostname as sHostname, c4.hostname as tHostname,c2.internal_address as s_internal_address, c4.internal_address as t_internal_address from isat.stream_server_assignment as c1 LEFT JOIN isat.stream_server as c2 on c1.stream_server_uid=c2.uid LEFT JOIN isat.rtmp_server_assignment as c3 on c1.device_uid=c3.device_uid LEFT JOIN isat.tunnel_server as c4 on c3.tunnel_server_uid=c4.uid where c1.device_uid like '%".$db_license["Mac"]."%' group by c1.device_uid";
          else 
          $sql1 ="select c1.device_uid as MAC, c1.purpose as purpose, c1.dataplan as dataplan, c1.recycle as recycle, c2.hostname as sHostname, c4.hostname as tHostname,c2.internal_address as s_internal_address, c4.internal_address as t_internal_address from isat.stream_server_assignment as c1 LEFT JOIN isat.stream_server as c2 on c1.stream_server_uid=c2.uid LEFT JOIN isat.tunnel_server_assignment as c3 on c1.device_uid=c3.device_uid LEFT JOIN isat.tunnel_server as c4 on c3.tunnel_server_uid=c4.uid where c1.device_uid like '%".$db_license["Mac"]."%' group by c1.device_uid"; 
          //$sql1 ="select * from isat.stream_server_assignment where device_uid like '%".$db_license["Mac"]."%'";
           sql($sql1,$result1,$num1,0);
            if($num1==1){
                $dataplan_msg = "<table><tr class=topic_main><td>Resolution</td><td>Package</td><td>Recycle Days</td><td>Assigned Tunnel</td><td>Assigned Stream</td><tr class=tr_1>";
                //<td>Latest Status</td>
                fetch($data_tmp,$result1,0,0);
                if ($data_tmp["purpose"]=="RVME") $dataplan_msg .="<td>".$resolutionNAME["RVME"]."</td>";
                else if ($data_tmp["purpose"]=="RVLO") $dataplan_msg .="<td>".$resolutionNAME["RVLO"]."</td>";
                else  $dataplan_msg .="<td>".$resolutionNAME["RVHI"]."</td>";
                if ($data_tmp["dataplan"]=="AR") $dataplan_msg .= "<td>Always Recording+Event</td>";
                else if ($data_tmp["dataplan"]=="LV") $dataplan_msg .= "<td>{$dataplanNAME['LV']}</td>";
                else if ($data_tmp["dataplan"]=="D") $dataplan_msg .= "<td>{$dataplanNAME['D']}</td>";
                else if ($data_tmp["dataplan"]=="SR") $dataplan_msg .= "<td>{$dataplanNAME['SR']}</td>";
                else if ($data_tmp["dataplan"]=="EV") $dataplan_msg .= "<td>{$dataplanNAME['EV']}</td>";
                $dataplan_msg .= "<td>".$data_tmp['recycle']."</td>";
                $dataplan_msg .= "<td>".$data_tmp['tHostname']."</td>";
                $s_status = exec ("python ".SCRIPT_ROOT_FOLDER."getStreamingStatus.py ".$data_tmp['s_internal_address']." ".$display["mac"]);
                $dataplan_msg .= "<td>".$data_tmp['sHostname']." ({$s_status})</td>";
                $dataplan_msg .= "</tr></table>";
            }
        }
        //end of jinho feature 
        }
        else
        {
                        $display["info"]        =" Searching item is not available in system";
        }
}
##############################
echo "<table class=table_main>";
echo "<form enctype=\"multipart/form-data\" method=post action='{$_SERVER['PHP_SELF']}'>\n";
//  echo "<div class=login_left>\n";
//    echo "<div class=login_left_con>\n";
echo "<tr>\n";
echo "<td>\n";  
echo "<div class=partner_topic>"._("MAC Check")."</div>\n";
echo "<div class=login_topic>"._("Input the MAC to check")."</div>\n";
echo "</td>\n";
echo "<td>\n";
echo "<input type=text name=mac class=input_1 value='{$display["mac"]}'>\n";
echo "</td>\n";
echo "<td>\n";
echo "<input type=hidden name=step value=apply_mac>\n";
echo "<input type=submit value='"._("Submit")."' class=btn_2>\n";
echo "</td>\n";
echo "</form>\n";
echo "</tr>";
echo "<form enctype=\"multipart/form-data\" method=post action='{$_SERVER['PHP_SELF']}'>\n";
echo "<tr>\n";
echo "<td>\n";
echo "<div class=partner_topic>"._("License Check")."</div>\n";
echo "<div class=login_topic>"._("Input the License to check")."</div>\n";
echo "</td>\n";
echo "<td>\n";
echo "<input type=text name=license class=input_1 value='{$display["license"]}'>\n";
echo "</td>\n";
echo "<td>\n";
echo "<input type=hidden name=step value=apply_license>\n";
echo "<input type=submit value='"._("Submit")."' class=btn_2>\n";
echo "</td>\n";
echo "</form>\n";
#the right side for the Activate Code check
echo "</tr>";
echo "<tr class=topic_main>";
echo "<td>MAC</td>\n";
echo "<td>"._("License")."</td>\n";
echo "<td>"._("Account")."</td>\n";
echo "<td>"._("Email")."</td>\n";
echo "<td>"._("Info")."</td>\n";
echo "</tr>";
echo "<tr class=tr_1>\n";

//if($_REQUEST["step"] == "apply_mac")
{
  echo "<TD>{$display["mac"]}</td>\n";
  echo "<TD>{$display["license"]}</td>\n";
  echo "<TD>{$display["account"]}</td>\n";
  echo "<TD nowrap>{$display["email"]}</td>\n";
  echo "<TD nowrap>{$display["info"]}</td>\n";

  if($license <> "")
    $link= array_search($_REQUEST["mac"],$total);
}
echo "</tr>\n";
echo "</table>";
if ($dataplan_msg!="")  echo $dataplan_msg; //jinho added feature 
echo "</body>";
echo "</html>";