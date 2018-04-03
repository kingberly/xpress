<?php
/****************
 *Validated on Dec-11,2017,
 * apk auto download page, filename set as apk name to work
 * keep download counter to /var/tmp/filename.dat
 * recycle dat file monthly
 * Add parameter:
 *   current counter=> cc
 *   2017 Jan counter => cc=201701
 * fix flock error      
 *Writer : JinHo Chang   
*****************/
//wget --timeout=10 --tries=1 -O /var/www/SAT-CLOUDNVR/rpic/ivedamobile.apk ftp://iveda:2wsxCFT6@ftp.tdi-megasys.com/TW/APP/RPIC/ivedamobile.x02.apk
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://"; 
$filename=substr($_SERVER['PHP_SELF'],0,strrpos($_SERVER['PHP_SELF'],"."));
////rpic.php access counter
define("LOG_PATH","/var/tmp/");
define("LOG_FILE",basename($_SERVER["SCRIPT_FILENAME"], '.php').".dat");

function fixme() //fix old date error once
{
  $files = scandir(LOG_PATH, SCANDIR_SORT_ASCENDING);
  for($i=0;$i<sizeof($files);$i++){//check filemdate
    if ( (preg_match("/.dat.[0-9]{4}(0[1-9]|1[0-2])$/",$files[$i])) ){
      $filecc = end(explode(".",$files[$i]));
      $realfiledate=date("Ym",filemtime(LOG_PATH.$files[$i]));
      if ($filecc == $realfiledate) break; //alrady fixed
      $fileprefix = rtrim($files[$i],$filecc); //trim date=> file.dat.
      exec("mv ".LOG_PATH.$files[$i]." ".LOG_PATH.$fileprefix.$realfiledate); 
     }   
  }
}
//fixme();
if (file_exists(LOG_PATH.LOG_FILE)){
  //echo "file exist";
  $lastCount=file_get_contents(LOG_PATH.LOG_FILE);
  $filedate=filemtime(LOG_PATH.LOG_FILE);
  $file_mdate=date("Ym",$filedate); //date("Ym");
  $lastCountDate=date("Y-m-d H:i:s",$filedate);
  if( date("n")!=date("n",$filedate) ){ //check if file date MONTH and current date 1-12  different
    exec("mv ".LOG_PATH.LOG_FILE." ".LOG_PATH.LOG_FILE.".".$file_mdate);
    exec(" echo '0' > ".LOG_PATH.LOG_FILE);
    chmod(LOG_PATH.LOG_FILE,0777);
  } 
}else{
  //echo "file NOT exist!".LOG_FILE;
    exec(" echo '0' > ".LOG_PATH.LOG_FILE);
    chmod(LOG_PATH.LOG_FILE,0777);  
}

if (isset($_REQUEST['cc']))
{
  if ($_REQUEST['cc']==""){
    if (file_exists(LOG_PATH.LOG_FILE))
      die(file_get_contents(LOG_PATH.LOG_FILE));
  }else{
    if (file_exists(LOG_PATH.LOG_FILE.".".$_REQUEST['cc']))
      die(file_get_contents(LOG_PATH.LOG_FILE.".".$_REQUEST['cc']));
  }    
  die("0");
}

function incrementFile (){
  $f = fopen(LOG_PATH.LOG_FILE, "r+"); 
  if (is_null($f)) die("Unable to open file!");
  // We get the exclusive lock
  if (flock($f, LOCK_EX)) { 
    $counter = (int) fgets ($f); // Gets the value of the counter
    rewind($f); // Moves pointer to the beginning
    fwrite($f, ++$counter); // Increments the variable and overwrites it
    flock($f, LOCK_UN); // Unlocks the file for other uses
  }
  fclose($f); // Closes the file
}
incrementFile();
  
$dlurl=$protocol.$_SERVER['HTTP_HOST'].$filename.".apk";
//echo $dlurl;
?>
<html>
<title>APK install</title>
<head>
<meta http-equiv="refresh" content="0; url=<?php echo $dlurl;?>" />
</head>
<body>
<a href='<?php echo $filename;?>.apk'><?php echo $filename;?></a>
<br></p>
<?php
if (isset($lastCount))
  echo "[{$lastCountDate}] Previous Access count was {$lastCount} times.";
?>
</body>
</html>