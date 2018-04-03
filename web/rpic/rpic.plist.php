<?php
/****************
 *Validated on Dec-1,2017,
 * ipa auto download page, set IPA_LIST to work
 * auto detect OEM ID and plist filename 
 *Writer : JinHo Chang   
*****************/
include_once( "../include/index_title.php" );
if ($oem_style_list['oem_id']!="N99")
	define("OEM_ID",$oem_style_list['oem_id']);
else die("No OEM_ID!!");
//oemid => if more than one=> 0boundle id, 1title, 2ver, 3url,4plist file 
$IPA_LIST=[
"X02"=>array(array("rpic.taipei","道路施工影像系統","1.1.23","https://{$_SERVER['HTTP_HOST']}/taipei/rpic.ipa","rpic.plist")),
"T04"=>array(array("rpic.taipei","道路施工影像系統","1.1.24","https://{$_SERVER['HTTP_HOST']}/taipei/rpic.ipa","rpic.plist")),
"T05"=>array(array("tw.gov.tycg.tymobileviewer","道路施工影像系統","1.2.22","https://{$_SERVER['HTTP_HOST']}/taipei/rpic.ipa","rpic.plist")),
"C13"=>array(
array("tw.com.chimei.engeye","奇美Xpress","1.2.1.113","https://{$_SERVER['HTTP_HOST']}/rpic/xpress.ipa","xpress.plist"),
array("tw.com.chimei.mobilecam","奇美行動眼","1.2.7.100","https://{$_SERVER['HTTP_HOST']}/rpic/mobilecam.ipa","mobilecam.plist")
)
];
//get this filename
$tmp = explode("/",$_SERVER['PHP_SELF']); //use tmp avoid "only variables should be passed by reference"
$plistname=end($tmp); //xxx.plist.php
$plistname=rtrim($plistname,".php");
if (is_null($IPA_LIST[OEM_ID])) die("No such plist info");
if (sizeof($IPA_LIST[OEM_ID])==1){
  $identifier= $IPA_LIST[OEM_ID][0][0];
  $title= $IPA_LIST[OEM_ID][0][1];  
  $ver = $IPA_LIST[OEM_ID][0][2]; 
  $dlurl= $IPA_LIST[OEM_ID][0][3];
}else if (sizeof($IPA_LIST[OEM_ID]) >1){
  foreach ($IPA_LIST[OEM_ID] as $data){
    if ($data[4] == $plistname){
      $identifier= $data[0];
      $title= $data[1];  
      $ver = $data[2]; 
      $dlurl= $data[3];
    }
  }
}
//check file exist
$tmp = explode("/",$dlurl); //use tmp avoid "only variables should be passed by reference"
if (!file_exists(end($tmp)))  die("ipa file not exist!!");

////same as rpic.php access counter
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
else //new added for ipa
  header('Content-type: application/x-plist');


function incrementFile (){
  $f = fopen(LOG_PATH.LOG_FILE, "r+") or die("Unable to open file!");
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
?>  
<?xml version="1.0" encoding="UTF-8"?>
<!DOCTYPE plist PUBLIC "-//Apple//DTD PLIST 1.0//EN" "http://www.apple.com/DTDs/PropertyList-1.0.dtd">
<plist version="1.0">
<dict>
	<key>items</key>
	<array>
		<dict>
			<key>assets</key>
			<array>
				<dict>
					<key>kind</key>
					<string>software-package</string>
					<key>url</key>
					<string><?php echo $dlurl;?></string>
				</dict>
			</array>
			<key>metadata</key>
			<dict>
				<key>bundle-identifier</key>
					<string><?php echo $identifier;?></string>
				<key>bundle-version</key>
					<string><?php echo $ver;?></string>
				<key>kind</key>
				<string>software</string>
				<key>title</key>
					<string><?php echo $title;?></string>
			</dict>
		</dict>
	</array>
</dict>
</plist>
