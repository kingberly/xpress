<?php
/****************
 *Validated on Jan,2016,
 * analysis mailer-xxxx.log     
 *Writer: JinHo, Chang
*****************/
ini_set('memory_limit', '64M');
define("LOG_PATH","/var/tmp/");
define("LOG_FILE","mailer.log");




if($_REQUEST["step"]=="set_pattern")
{
  if ($_REQUEST["btnAction"]=="Read")
  {
      if (isset($_REQUEST["logfile"])){
        $SEL_LOG_FILE = $_REQUEST["logfile"];
          if (!file_exists(LOG_PATH.$SEL_LOG_FILE))
            $msgErr = "<font color=red>".$SEL_LOG_FILE." Read FAIL! </font><br>\n";
          else
            $msgErr = readLog(LOG_PATH.$SEL_LOG_FILE);
      } 
  }else if ($_REQUEST["btnAction"]=="Count")
  {
    $patternlen = strlen($_REQUEST['pattern']);
    if ($patternlen < 14 ){ //2016-01-08 16:
       echo "wrong search pattern ({$patternlen})";
       exit(0);
    }

    $myPatternLogFile = substr($_REQUEST['pattern'],0,10); //2016-01-08
    $cur_date=date("YmdHi");
    $myPatternLogFileExt = ".".$myPatternLogFile.".".$cur_date;
    
    exec(" echo '' > ".LOG_PATH.LOG_FILE.$myPatternLogFileExt);
    chmod(LOG_PATH.LOG_FILE.$myPatternLogFileExt,0777);
    $fplog=fopen(LOG_PATH.LOG_FILE.$myPatternLogFileExt,"a+");
    $fwrite = fwrite($fplog,"From File /var/log/alarm/mailer_".$myPatternLogFile.".log\n");
    $fwrite = fwrite($fplog,"================================================\n");
    for ($i=0;$i<60;$i++){
      if ($i<10)  $myPattern = $_REQUEST['pattern']."0".$i;
      else $myPattern = $_REQUEST['pattern'].$i; 
      $count = exec("grep '".$myPattern."' /var/log/alarm/mailer_".$myPatternLogFile.".log  | wc -l");
      $fwrite = fwrite($fplog,$myPattern." = ".$count."\n");  
    }
    $count = exec("grep '".$_REQUEST['pattern']."' /var/log/alarm/mailer_".$myPatternLogFile.".log  | wc -l");
    $fwrite = fwrite($fplog,"================================================\n");
    $fwrite = fwrite($fplog,"Total  = ".$count."\n");
    $msgErr = "count pattern ".$_REQUEST['pattern'] ."from mailer_".$myPatternLogFile.".log @ ".$myPatternLogFileExt;
    fclose($fplog);
  }  
}

function selectLogFile($tagName)
{
    global $SEL_LOG_FILE;
    $html = "<select name='{$tagName}'>";
    $files = scandir(LOG_PATH, SCANDIR_SORT_ASCENDING);
    for($i=0;$i<sizeof($files);$i++){
      if (strpos($files[$i],LOG_FILE)!== FALSE){
            if ($files[$i] == $SEL_LOG_FILE) 
                $html.= "\n<option value='{$files[$i]}' selected>{$files[$i]}</option>";
            else
                $html.= "\n<option value='{$files[$i]}'>{$files[$i]}</option>";
      }
    }//for
  $html .= "</select>\n";   //add table end
	echo $html;
}

function readLog($filepath)
{
    $html = file_get_contents($filepath);
    //$html = str_replace("<?"," ",$html);
    $html = "<pre>".$html."</pre>";
    return $html;
}
?>
<html>
<head>
</head>
<body>
<div align=center><b><font size=5>Mailer log</font></b></div>
<div id="container">
<form method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
  <input type=text name=pattern value='2016-01-08 16:'>
  <input type=hidden name=step value='set_pattern'>
  <input type=submit name=btnAction value="Count">
  <?php selectLogFile("logfile");?>
  <input type=submit name=btnAction value="Read"> 
  </form>
  <HR>
  <?php
  if (isset($msgErr) and($msgErr !="") )
    echo $msgErr;   
  ?>
</div>
</body>
</html>