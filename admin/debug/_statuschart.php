<?php
/****************
 *Validated on Jan-7,2016,
 * Google API bar charts compatible
 * Delete file that is less than 1K
 * add read log files    
 *Writer: JinHo, Chang
*****************/
if($_REQUEST["btnAction"]=="Set")
{
    exit(header("Location: stream_statuslog.php?setOnly=1"));
}
if ($_REQUEST["Bar"] == "Yes" )
  define("MAXLIST",3000);
else
  define("MAXLIST",250);
include("/var/www/qlync_admin/header.php");
include("/var/www/qlync_admin/menu.php");
define("STREAM_SRV","1");
define("TUNNEL_SRV","2");
define("LOG_PATH","/var/tmp/");
define("LOG_FILE_T","tunnel_status.log");
define("LOG_FILE_S","stream_status.log");
define("LOG_FILE_POSTFIX","_status.log");
//*/3 * * * * root /usr/bin/php5  "/var/www/qlync_admin/plugin/debug/tunnel_connlog.php"
//$TUNNEL_UID='5284b02873234e1d86e101e8fc3f5b22';
$UID="";
$flag_LoadAvg=0;
$flag_CPU=0;
$flag_MEM=0;
if($_REQUEST["step"]=="set_log_file")
{
    $SEL_LOG_FILE = $_REQUEST["logfile"];
    if (file_exists(LOG_PATH.$SEL_LOG_FILE))
      include_once(LOG_PATH.$SEL_LOG_FILE);

    if (strpos($SEL_LOG_FILE,"stream")!==FALSE){
      $TYPE = STREAM_SRV;
      $UID = $STREAM_UID;
      $UID1 = $STREAM_UID1;
      //$UID2 = $STREAM_UID2;
      //$UID3 = $STREAM_UID3;
      $log = $stream_log;
    }else if (strpos($SEL_LOG_FILE,"tunnel")!==FALSE){
       $TYPE = TUNNEL_SRV;
      $UID = $TUNNEL_UID;
      $UID1 = $TUNNEL_UID1;
      $UID2 = $TUNNEL_UID2;
      //$UID3 = $TUNNEL_UID3;
      $log = $tunnel_log;
    }
    if ($_REQUEST["btnAction"]=="LoadAvg")
      $flag_LoadAvg = 1;
    else if ($_REQUEST["btnAction"]=="CPU")
      $flag_CPU = 1;
    else if ($_REQUEST["btnAction"]=="MEM")
      $flag_MEM = 1;
    else if ($_REQUEST["btnAction"]=="Delete") 
    {
        if ((filesize(LOG_PATH.$SEL_LOG_FILE) < 1000) and  ($SEL_LOG_FILE != LOG_FILE_S)){
          exec("rm -rf ".LOG_PATH.$SEL_LOG_FILE);
          $msgErr = "<font color=red>File {$SEL_LOG_FILE} is Deleted!</font><br>\n"; 
        }else 
          $msgErr = "<font color=red>File Log {$SEL_LOG_FILE} is locked.</font><br>\n";
    }else if($_REQUEST["btnAction"]=="Read")
    {
        if (!file_exists(LOG_PATH.$SEL_LOG_FILE))
          $msgErr = "<font color=red>".$SEL_LOG_FILE." Read FAIL! </font><br>\n";
        else
          $msgErr = readLog(LOG_PATH.$SEL_LOG_FILE);
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
                  ( ($SEL_LOG_FILE=="") and ($files[$i] == LOG_FILE_S) ) )
                $html.= "\n<option value='{$files[$i]}' selected>{$files[$i]}</option>";
            else
                $html.= "\n<option value='{$files[$i]}'>{$files[$i]}</option>";
      }
    }//for
  $html .= "</select>\n";   //add table end
	echo $html;
}


function createChartData($type,$log,$uid){
  global $flag_LoadAvg, $flag_CPU,$flag_MEM;
  $skip = sizeof($log[$uid])-MAXLIST;$i = 0;  
  if ($uid == ""){   echo "['null',0]";return;  }
  if ($flag_CPU==1)
    $html = "['CPU | Date', '%'],"; 
  else if ($flag_MEM==1)
    $html = "['MEM | Date', '%'],";
  else if ($flag_LoadAvg==1)
    $html = "['LoadAvg | Date', '#'],";
  else
  	$html = "['Streaming | Date', '#'],";   // ['201501111111',25]
  //$tunnel_log["5284b02873234e1d86e101e8fc3f5b22"]["201503070100"]["device]='31';
	foreach($log[$uid] as $key=>$values){
     if ($type==TUNNEL_SRV)
         $num = $values["device"];
     else if ($type==STREAM_SRV)
         if ($flag_LoadAvg==1)
            $num = $values["loadavg"];
         else if ($flag_CPU==1)
            $num = trim($values["cpu"],"%");
         else if ($flag_MEM==1)
            $num = trim($values["mem"],"%");
         else
            $num = $values["status"];
     if (! is_numeric($num)) $num=0;
     if ($skip < ++$i) //add for skip
     $html.="['$key',$num],\n";
  }
  $html = rtrim($html, ",\n"); //remove last,
	echo $html;
}

function createChartData1($type,$log,$uid,$uid1){
  global $flag_LoadAvg, $flag_CPU,$flag_MEM;
   $skip = sizeof($log[$uid])-MAXLIST;$i = 0;  
  if ($uid == "") {echo "['null',0,0]";return;}
  if ($flag_CPU==1)
    $html = "['CPU | Date', '%','%2'],"; 
  else if ($flag_MEM==1)
    $html = "['MEM | Date', '%','%2'],";
  else if ($flag_LoadAvg==1)
    $html = "['LoadAvg | Date', '#','#2'],";
  else
  	$html = "['Streaming | Date', '#','#2'],";   // ['201501111111',25]
  //$tunnel_log["5284b02873234e1d86e101e8fc3f5b22"]["201503070100"]["device]='31';
	foreach($log[$uid] as $key=>$values){
     if ($type==TUNNEL_SRV){
         $num = $values["device"];
         $num1 = $log[$uid1][$key]["device"];
         if ($num1=="") $num1=0;
     }else if ($type==STREAM_SRV){
         if ($flag_LoadAvg==1){
            $num = $values["loadavg"];
            $num1 = $log[$uid1][$key]["loadavg"];
         }else if ($flag_CPU==1){
            $num = trim($values["cpu"],"%");
            $num1 = trim($log[$uid1][$key]["cpu"],"%");
         }else if ($flag_MEM==1){
            $num = trim($values["mem"],"%");
            $num1 =  trim($log[$uid1][$key]["mem"],"%");
         }else{
         $num = $values["status"];
         $num1 = $log[$uid1][$key]["status"];
         }
         if (! is_numeric($num)) $num=0;
         if (! is_numeric($num1)) $num1=0;
     }
     if ($skip < ++$i) //add for skip
     $html.="['$key',$num,$num1],\n";
  }
  $html = rtrim($html, ",\n"); //remove last,
	echo $html;
}
//only suppport 3 tunnels
function createChartData2($type,$log,$uid,$uid1,$uid2){
  global $flag_LoadAvg, $flag_CPU,$flag_MEM;
   $skip = sizeof($log[$uid])-MAXLIST;$i = 0;  
  if ($uid == "") { echo "['null',0,0,0]";return;}
 	$html = "['Connection | Date', '#','#2','#3'],";   // ['201501111111',25]
  //$tunnel_log["5284b02873234e1d86e101e8fc3f5b22"]["201503070100"]["device]='31';
	foreach($log[$uid] as $key=>$values){
     if ($type==TUNNEL_SRV){
         $num = $values["device"];
         $num1 = $log[$uid1][$key]["device"];
         $num2 = $log[$uid2][$key]["device"];
       if (! is_numeric($num)) $num=0;
       if (! is_numeric($num1)) $num1=0;
       if (! is_numeric($num2)) $num2=0;
     }
     if ($skip < ++$i) //add for skip
     $html.="['$key',$num,$num1,$num2],\n";
  }
  $html = rtrim($html, ",\n"); //remove last,
	echo $html;
} 

function readLog($filepath)
{
    $html = file_get_contents($filepath);
    $html = str_replace("<?"," ",$html);
    $html = "<pre>".$html."</pre>";
    return $html;
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
  google.load('visualization', '1.1', {packages: ['bar']});  //isStacked not working
 <?php }else{ ?>
  google.load('visualization', '1.1', {packages: ['corechart']});
  <?php } ?> 
  google.setOnLoadCallback(drawChart);
  function drawChart() {
      // Create and populate the data table.
      var data = google.visualization.arrayToDataTable([
          <?php
              if ($UID2!=""){
                createChartData2($TYPE,$log,$UID,$UID1,$UID2);                
              }else if ($UID1!=""){
                createChartData1($TYPE,$log,$UID,$UID1);                
              }else{
                createChartData($TYPE,$log,$UID);
              }
          ?>
      ]);
       var options = {
            title: '<?php echo $SEL_LOG_FILE." ".$UID." "; if ($UID1!="") echo $UID1;?>',
          legend: { position: 'top' },
        <?php if (!( ($flag_CPU==1) or  ($flag_MEM==1)or ($flag_LoadAvg==1)))
              echo "isStacked: true,"; ?>
        };
      // Create and draw the visualization.
       <?php if ($_REQUEST["Bar"] == "Yes" ) { ?> 
      var chart = new google.charts.Bar(document.getElementById('columnchart_material'));   //isStacked not working
     <?php }else{ ?>
      var chart = new google.visualization.ColumnChart(document.getElementById('columnchart_material'));  //horizontal use BarChart
    <?php } ?>
      chart.draw(data, options);
   }
</script> 
<form method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
<?php selectLogFile("logfile");?>
<input type=hidden name=step value='set_log_file'>
<?php if (isset($_REQUEST["debugadmin"])){?>
<input type=submit name=btnAction value="Delete">
<?php } ?>
<input type=submit name=btnAction value="Read">
<input type=submit name=btnAction value="Set">
Bar <input type="checkbox" name="Bar" value="Yes" <?php if ($_REQUEST["Bar"] == "Yes" ) echo "checked";?>>
<input type=submit name=btnAction value="ViewNumber" class="btn_2">
<input type=submit name=btnAction value="LoadAvg" class="btn_2">
<input type=submit name=btnAction value="CPU" class="btn_2">
<input type=submit name=btnAction value="MEM" class="btn_2">
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