<?
	if($_REQUEST["step"]=="aa")
	{
	        header("Refresh: 1;");
	}
	else
	{
                header("Refresh: 15;");

	}

include("../../header.php");
include("../../menu.php");
#Authentication Section
$sql="select * from qlync.menu where Name = 'Admin Server'";
sql($sql,$result,$num,0);
fetch($db,$result,0,0);
$sql="select * from qlync.right_tree where Cfun='{$db["ID"]}' and `Right` = 1";
sql($sql,$result,$num,0);
$right=0;
$oem_id="";
for($i=0;$i<$num;$i++)
{
        fetch($db,$result,$i,0);
        if($_SESSION["{$db["Fright"]}_qlync"] ==1)
        {
                $right+=1;
                if($db["Oem"] == "0")
                {
                        $oem_id="N99";
                }
                if($db["Oem"] == "1" and $oem_id == "")
                {
                        $oem_id=$_SESSION["CID"];
                }
        }
}
if($right  == "0")
        exit();
############  Authentication Section End
################################################
#API A-1
    $free = shell_exec('free');
    $free = (string)trim($free);
    $free_arr = explode("\n", $free);
    $mem = explode(" ", $free_arr[1]);
    $mem = array_filter($mem);
    $mem = array_merge($mem);
    $memory_usage = $mem[2]/$mem[1]*100;



        $load = sys_getloadavg();
//        echo $load[0]."\n";


$df=disk_free_space("/");
$dt=disk_total_space("/");



###########################################################AA section Stop
##########  update the server port
echo "<BR>";
echo "<HR>";
echo "<HR>\n";

#################adding menu item section
echo "<table class=table_main>\n";
	echo "<tr class=topic_main>\n";
    echo "<td> UID </td>\n";//jinho fix added UID
		echo "<td> "._("LAN")." IP </td>\n";
		echo "<td> CPU</td>\n";
		echo "<td> Mem </td>\n";
		echo "<td> Disk Space</td>\n";
	echo "</tr>\n";
	echo "<tr class=tr_2 >\n";
    //jinho fix admin info
    $output=shell_exec("blkid");
    if ($output!=""){
      $output=shell_exec("blkid | awk '/ext4/{print $2}'");
      if (strpos($output,'UUID') === FALSE) $output=shell_exec("blkid | awk '/ext4/{print $3}'");
      $output=ltrim($output,"UUID=\"");
      $output=rtrim($output);
      $output=rtrim($output,"\"");
    }else{ //$output = "NULL";
     $output = shell_exec("sed -n '/ext4/p' /etc/fstab |awk '{print $1}'");
     if (strpos($output,"UUID=")===FALSE)
        $output = shell_exec("sed -n '/UUID=/p' /etc/fstab |awk '{print $1}'");
    }
    include("../webmaster/version_content.php");
    //krsort($ver_log); //get first key without sort
    foreach($ver_log as $key => $value)
    {
      $ver_admin = $key;
      break;
    }
    $ver_apache=shell_exec("apache2 -v | awk -F/ '/version/{print $2}'");
    echo "<td>{$output}<br><font color=#0000CC>Version : {$ver_admin}<br>PHP : ".phpversion()."<br>Apache : {$ver_apache}</font></td>\n";
    //end of jinho fix
		echo "<td>{$_SERVER['SERVER_ADDR']}</td>\n";
		echo "<td >".($load[0]*100)."% </td>\n";
		echo "<td>".round($memory_usage,2)." % (".round($mem[1]/1000/1024,2)."GB )</td>\n";
		echo "<td>".round(($dt-$df)/$dt*100,2)."%  ( ".round($dt/1024/1024/1024,1)." GB) </td>\n";
		
	echo "</tr>\n";
echo "</table>\n";
######################################################Show section

include("{$api_temp}/partner_min.log");
include("{$api_temp}/partner_hourly.log");



$time=array("Please select..."=>"","Last Hour"=>"min","Last Day"=>"hour");

echo "<table class=table_main>\n";
        echo "<form name=partner_monitor method=post action=partner_server.php>\n";
        echo "<tr class=tr_2>\n";
                echo "<td>\n";
                        echo "Please Select Period:<select name=time onchange=\"this.form.submit()\">\n";
                                foreach($time as $key=>$value)
                                {
                                        $chk="";
                                        if($value == $_REQUEST["time"])
                                                $chk="selected";
                                        echo "<option value=\"{$value}\" {$chk}>{$key}</option>\n";
                                }
                        echo "</select>\n";
                echo "</td>\n";
        echo "</tr>\n";
        echo "</form>\n";
echo "</table>\n";
switch($_REQUEST["time"])
{
        case "hour":
                $log=$partner_log_hour;
                break;
        default:
                $log=$partner_log;
                break;
}
foreach($log as $key=>$value)
{
$seq=0;
        $max[$key]=max($value["cpu"]);
        $max_mem[$key]=max($value["mem"]);
//      $chd[$key]=implode(",",$value["cpu"]);
        ksort($value["cpu"]);
        ksort($value["mem"]);
        foreach($value["cpu"] as $key_time=>$value_num)
        {
                //check if lack of data
//      if($substr($key_time,4,4) <> date("md"))
                while(substr($key_time,-2) <> $seq )
                {
                        $t[$key][$seq]=str_pad($seq,2,"00",STR_PAD_LEFT);
                        $c[$key][$seq]=0;
                        $seq++;

                }
                        $t[$key][$seq]=substr($key_time,-2);

                if($max[$key] <10)

                {
                        $c[$key][$seq]=$value_num*10;
                        $y_note="&#8240;";
                        $scale[$key]=floor($max[$key]*10);

                }
                else
                {
                        $c[$key][$seq]=$value_num;
                        $y_note="%";
                        $scale[$key]=floor($max[$key]);
                }

        $seq++;
        }
        $seq=0;
        foreach($value["mem"] as $key_time=>$value_num)
        {
                //check if lack of data

                while(substr($key_time,-2) <> $seq )
                {
                        $t_mem[$key][$seq]=str_pad($seq,2,"00",STR_PAD_LEFT);
                        $c_mem[$key][$seq]=0;
                        $seq++;

                }
                        $t_mem[$key][$seq]=substr($key_time,-2);
                        $c_mem[$key][$seq]=$value_num;
                        $y_note_mem="%";
                        $scale_mem[$key]=floor($max_mem[$key]);
        $seq++;
        }
        $chl[$key]=implode("|",$t[$key]);
        $chd[$key]=implode(",",$c[$key]);
        $chl_mem[$key]=implode("|",$t_mem[$key]);
        $chd_mem[$key]=implode(",",$c_mem[$key]);

}
$chs="800x200";
$chm="N*f0*,000000,0,-1,11";
foreach($log as $key=>$value)
{
echo "<table class=table_main>\n";
        echo "<tr class=topic_main>\n";
                echo "<td>{$key}</td>\n";
        echo "</tr>\n";
        echo "<tr>\n";
                echo "<td>\n";
                        echo "<img src=\"https://chart.googleapis.com/chart?cht=bvg&chd=t:{$chd[$key]}&chs={$chs}&chl={$chl[$key]}&chm={$chm}&chtt=CPU&chxr=0,0,{$scale[$key]}&chxt=y,y&chxp=1,100&chxl=1:|{$y_note}&chbh=a&chds=0,{$scale[$key]}\">\n";
                echo "</td>\n";
        echo "</tr>\n";
        echo "<tr>\n";
                echo "<td>\n";
                        echo "<img src=\"https://chart.googleapis.com/chart?cht=bvg&chd=t:{$chd_mem[$key]}&chs={$chs}&chl={$chl_mem[$key]}&chm={$chm}&chtt=Memory&chxr=0,0,{$scale_mem[$key]}&chxt=y,y&chxp=1,100&chxl=1:|{$y_note_mem}&chbh=a&chds=0,{$scale_mem[$key]}\">\n";
                echo "</td>\n";
        echo "</tr>\n";
echo "</table>\n";
}

?>


