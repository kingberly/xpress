<?php
/****************
 *Validated on Dec-7,2017
 * Google API bar charts
 *  Use Multiple Array as INPUT
 *  Work with Const statistics
 *  integrate T04
 *  Fix display limitation issue by partition to MAXLIST(250)               
 *Writer: JinHo, Chang
*****************/
if (isset($_SESSION) )
  if (!isset($_SESSION["Email"]) ) exit(); 
if($_REQUEST["btnAction"]=="SetTun")
{
  if (file_exists("/var/www/qlync_admin/plugin/taipei/tunnel_connlog_tpe.php"))
    exit(header("Location: tunnel_connlog_t04.php?setOnly"));
  else  exit(header("Location: tunnel_connlog_Const.php?setOnly"));
}else if($_REQUEST["btnAction"]=="SetRtmp")
{
  if (file_exists("/var/www/qlync_admin/plugin/taipei/rtmpd_connlog_tpe.php"))
    exit(header("Location: rtmpd_connlog_tpe.php?setOnly"));
  else  exit(header("Location: rtmpd_connlog_Const.php?setOnly"));
}else if($_REQUEST["btnAction"]=="SetStr")
{
    exit(header("Location: stream_connlog_Const.php?setOnly"));
}
include("/var/www/qlync_admin/header.php");
include("/var/www/qlync_admin/menu.php");
//var_dump($_REQUEST);
if ($_REQUEST["Bar"] == "Yes" )
  define("MAXLIST",3000);
else
  define("MAXLIST",250);

define("STREAM_SRV","1");
define("TUNNEL_SRV","2");
define("RTMPD_SRV","3");
define("LOG_PATH","/var/tmp/");
if ($oem == "T04"){
define("LOG_FILE_T","tunnel_connection_t04.log");
define("LOG_FILE_R","rtmpd_connection_t04.log");
define("LOG_FILE_POSTFIX","_connection_t04.log");
}else{
define("LOG_FILE_T","tunnel_connection_const.log");
define("LOG_FILE_R","rtmpd_connection_const.log");
define("LOG_FILE_POSTFIX","_connection_const.log");
}
//*/3 * * * * root /usr/bin/php5  "/var/www/qlync_admin/plugin/debug/tunnel_connlog.php"
//$TUNNEL_UID='5284b02873234e1d86e101e8fc3f5b22';
$UID="";
if($_REQUEST["step"]=="set_log_file")
{
    $SEL_LOG_FILE = $_REQUEST["logfile"];
    if (file_exists(LOG_PATH.$SEL_LOG_FILE))
      include_once(LOG_PATH.$SEL_LOG_FILE);

    if (strpos($SEL_LOG_FILE,"stream")!==FALSE){
      $TYPE = STREAM_SRV;
      $log = $stream_log;
    }else if (strpos($SEL_LOG_FILE,"tunnel")!==FALSE){
       $TYPE = TUNNEL_SRV;
       if (count($tunnelArray) ==1)
          $UID = $tunnelArray[0];
       else if (count($tunnelArray) ==2){
          $UID = $tunnelArray[0];
          $UID1 = $tunnelArray[1];
       }else if (count($tunnelArray) >2){
          $UID = $tunnelArray[0];
          $UID1 = $tunnelArray[1];
          $UID2 = $tunnelArray[2];
      }
      $log = $tunnel_log;
    }else if (strpos($SEL_LOG_FILE,"rtmpd")!==FALSE){
       $TYPE = RTMPD_SRV;
       if (count($rtmpArray) ==1)
          $UID = $rtmpArray[0];
       else if (count($rtmpArray) ==2){
          $UID = $rtmpArray[0];
          $UID1 = $rtmpArray[1];
      }
      $log = $rtmp_log;
    }
    
    if ($_REQUEST["btnAction"]=="Delete") 
    {
        if ((filesize(LOG_PATH.$SEL_LOG_FILE) < 1000) and ($SEL_LOG_FILE != LOG_FILE_T) and ($SEL_LOG_FILE != LOG_FILE_R)){
          exec("rm -rf ".LOG_PATH.$SEL_LOG_FILE);
          $msgErr = "<font color=red>File {$SEL_LOG_FILE} is Deleted!</font><br>"; 
        }else 
          $msgErr = "<font color=red>File Log {$SEL_LOG_FILE} is locked.</font><br>";
    }else if ($_REQUEST["btnAction"]=="Read")
    {
        if (!file_exists(LOG_PATH.$SEL_LOG_FILE))
          $msgErr = "<font color=red>".$SEL_LOG_FILE." Read FAIL! </font><br>\n";
        else
          $msgErr = readLog(LOG_PATH.$SEL_LOG_FILE);
    }else if ($_REQUEST["btnAction"]=="DeviceNumber")
    {
        $typeNumber="device";
    }else if ($_REQUEST["btnAction"]=="ViewerNumber")
    {
        $typeNumber="viewer";
        if ($TYPE == RTMPD_SRV)
          $msgErr = "<font color=red>No Viewer Number from Mobile Camera.</font><br>\n";
    }
}


function selectLogFile($tagName)
{
    global $SEL_LOG_FILE;
    $html = "<select name='{$tagName}'>";
    $files = scandir(LOG_PATH, SCANDIR_SORT_ASCENDING);
    for($i=0;$i<sizeof($files);$i++){
      if (strpos($files[$i],LOG_FILE_POSTFIX)!== FALSE){
            if ( ($files[$i] == $SEL_LOG_FILE) or
                  ( ($SEL_LOG_FILE=="") and ($files[$i] == LOG_FILE_T) ) )
                $html.= "\n<option value='{$files[$i]}' selected>{$files[$i]}</option>";
            else
              if (strlen($files[$i]) < 39) 
                $html.= "\n<option value='{$files[$i]}'>{$files[$i]}</option>";
      }
    }//for
  $html .= "</select>\n";   //add table end
  echo $html;
}
function readLog($filepath)
{
    $html = file_get_contents($filepath);
    $html = str_replace("<?"," ",$html);
    $html = "<pre>".$html."</pre>";
    return $html;
}

//only suppport 3 tunnels
function createChartData2($type,$log,$uid,$uid1="",$uid2=""){
  global $typeNumber;
  $i = 0; $num1=0; $num2=0;
  if (isset($_REQUEST['block_filter'])){
    if (!isset($log[$uid][$_REQUEST['block_filter']])){
         unset($_REQUEST['block_filter']);
         $startFLAG=true;
    }else $startFLAG=false;
  }else  $startFLAG=true;
  if ($uid == "") {echo "['null',0,0,0]";return;}
  if ($uid2 != "")  $html = "['Connection | Date', '{$uid}','{$uid1}','{$uid2}'],";
  else if ($uid1 != "") $html = "['Connection | Date', '{$uid}','{$uid1}'],";
  else  $html = "['Connection | Date', '{$uid}'],";
  //$tunnel_log["5284b02873234e1d86e101e8fc3f5b22"]["201503070100"]["device]='31';
  foreach($log[$uid] as $key=>$values){
     if (($type==TUNNEL_SRV) or ($type==RTMPD_SRV)){
        if (isset($typeNumber)){
        if (!$startFLAG)
          if ($_REQUEST['block_filter'] == $key) $startFLAG=true;
          else continue;

         $num = ($values[$typeNumber]=="") ? 0:$values[$typeNumber];
         if ($uid1 != "") $num1 = ($log[$uid1][$key][$typeNumber]=="") ? 0:$log[$uid1][$key][$typeNumber];
         if ($uid2 != "") $num2 = ($log[$uid2][$key][$typeNumber]=="") ? 0:$log[$uid2][$key][$typeNumber];
        }else{
         $num = ($values["device"]=="") ? 0:$values["device"];
         if ($uid1 != "") $num1 = ($log[$uid2][$key]["device"]=="") ? 0:$log[$uid1][$key]["device"];
         if ($uid2 != "") $num2 = ($log[$uid2][$key]["device"]=="") ? 0:$log[$uid2][$key]["device"];
        }
     }else if ($type==STREAM_SRV){
         $num = ($values["status"]=="") ? 0:$values["status"];
         if ($uid1 != "") $num1 = ($log[$uid2][$key]["status"]=="") ? 0:$log[$uid1][$key]["status"];
         if ($uid2 != "") $num2 = ($log[$uid2][$key]["status"]=="") ? 0:$log[$uid2][$key]["status"];
     }
     
     if ($uid2 != "")  $html.="['$key',$num,$num1,$num2],\n";
     else if ($uid1 != "")  $html.="['$key',$num,$num1],\n";
     else $html.="['$key',$num],\n";
     $i++; 
     if (MAXLIST < $i) break;//add for skip
  }
  $html = rtrim($html, ",\n"); //remove last,
  echo $html;
}

function createChartDataArray($log,$uidArray){
  global $typeNumber;
  $j = 0;
  if (isset($_REQUEST['block_filter'])){
    if (!isset($log[$uidArray[0]][$_REQUEST['block_filter']])){
         unset($_REQUEST['block_filter']);
         $startFLAG=true;
    }else $startFLAG=false;
  }else  $startFLAG=true;  
  if (count($uidArray) ==0) {echo "['null',0]";return;}
  $html = "['Connection | Date',";
  for ($i=0;$i<count($uidArray);$i++){
    $html .= " '". $uidArray[$i]. "' ";
    if ($i <> (count($uidArray)-1)) $html .= ",";
  }
  $html .= "],";
  foreach($log[$uidArray[0]] as $key=>$values){
    if (!$startFLAG)
      if ($_REQUEST['block_filter'] == $key) $startFLAG=true;
      else continue;
    $num = ($values[$typeNumber]=="") ? 0:$values[$typeNumber];

    $html.="['$key',$num,";
    for ($i=1;$i<count($uidArray);$i++){
         $num1 = ($log[$uidArray[$i]][$key][$typeNumber]=="") ? 0:$log[$uidArray[$i]][$key][$typeNumber];

         $html.="$num1,";
    }
    $html = rtrim($html, ","); //remove last,
    $html.="],\n";
    $j++;
    if (MAXLIST < $j) break;//add for skip
  }
  $html = rtrim($html, ",\n"); //remove last,
  echo $html;
}
function createFilterOption($sel,$log){
  global $typeNumber,$TYPE,$tunnelArray, $rtmpArray;
  if ($TYPE == TUNNEL_SRV) $uidArray = $tunnelArray;
  else if ($TYPE==RTMPD_SRV) $uidArray = $rtmpArray;
  if (count($uidArray) ==0) return;
  $html="";//"<option value=\"\"></option>\n";
  $i=0; $timestamp = "";
  foreach($log[$uidArray[0]] as $key=>$values){
    //$html.="<option value=\"{$key}\" selected>($timestamp){$key}</option>\n";
    if ($timestamp != $key){ 
      $timestamp = $key;
      if (MAXLIST >= $i){//if (($i%MAXLIST) ==0){
        if ($i==0){
        if ($sel == $key )  $html.="<option value=\"{$key}\" selected>{$key}</option>\n";
        else  $html.="<option value=\"{$key}\">{$key}</option>\n";
        }
        $i++;
      }else $i=0;
    }
  }
  echo $html;
}   
?>
<!--html>
<head>
</head>
<body-->
  <script type="text/javascript" src="//www.google.com/jsapi"></script>
  <script type="text/javascript">
  //load package
  <?php if ($_REQUEST["Bar"] == "Yes" ) { ?>
  google.load('visualization', '1.1', {packages: ['bar']});   //isStacked not working
  <?php }else{ ?>
  google.load('visualization', '1.1', {packages: ['corechart']});
  <?php } ?>
  google.setOnLoadCallback(drawChart);
  function drawChart() {
      // Create and populate the data table.
      var data = google.visualization.arrayToDataTable([
          <?php
              if ($UID2!=""){
                //createChartData2($TYPE,$log,$UID,$UID1,$UID2);
                if ($TYPE==TUNNEL_SRV)                
                  createChartDataArray($log,$tunnelArray);
                else if ($TYPE==RTMPD_SRV)
                  createChartDataArray($log,$rtmpArray);
              }else if ($UID1!=""){
                createChartData2($TYPE,$log,$UID,$UID1);                
              }else{
                createChartData2($TYPE,$log,$UID);
              }
          ?>
      ]);
       var options = {
          title: '<?php echo "{$SEL_LOG_FILE} ({$typeNumber}) ";?>',
          legend: { position: 'top', maxLines: 4},
          isStacked: true,
        };
      // Create and draw the visualization.
      <?php if ($_REQUEST["Bar"] == "Yes" ) { ?>
      var chart = new google.charts.Bar(document.getElementById('columnchart_material')); //isStacked not working
     <?php }else{ ?>
      var chart = new google.visualization.ColumnChart(document.getElementById('columnchart_material'));  //horizontal use BarChart
    <?php } ?>
      chart.draw(data, options);
   }
function optionValue(thisformobj, selectobj)
{
  var chosenoption=selectobj.options[selectobj.selectedIndex];
  thisformobj.value = chosenoption.value;
}
</script> 
<form method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
<?php selectLogFile("logfile");?>
<input type=hidden name=step value='set_log_file'>
<?php
if ((isset($_REQUEST["step"])) and (($_REQUEST["btnAction"]=="DeviceNumber") or ($_REQUEST["btnAction"]=="ViewerNumber")) ){
?>
<select name="block_filter" id="block_filter">
<!-- onchange="optionValue(this.form.block_filter, this);this.form.submit();"-->
<?php
  createFilterOption($_REQUEST['block_filter'],$log);
?>
</select>
<?php } ?>
Bar <input type="checkbox" name="Bar" value="Yes" <?php if ($_REQUEST["Bar"] == "Yes" ) echo "checked";?>>
<input type=submit name=btnAction value="Read">
<input type=submit name=btnAction value="DeviceNumber" class="btn_2">
<input type=submit name=btnAction value="ViewerNumber" class="btn_2">
</form>

<?php
if (isset($msgErr) and($msgErr !="") )
  echo $msgErr;
else {  
?> 
  <div id="columnchart_material" style="width: 900px; height: 500px;"></div>
<?php
}
?>
</body>
</html>