<?php
/****************
 *Validated on Mar-6,2018,
 * reference from turtorial.php, turtorial_content.php
 * copy as /var/www/qlync_admin/html/faq/turtorial.php 
 * get pdf list from /var/www/qlync_admin/html/faq/pdf/ or /var/www/qlync_admin/html/faq/
 ** fix current folder link issue 
 ****************/
include_once("/var/www/qlync_admin/header.php");
include_once("/var/www/qlync_admin/menu.php");
header("Content-Type:text/html; charset=utf-8");
define("FILE_EXT",".pdf");
if (is_dir(getcwd()."/pdf/"))
define("FILE_FOLDER","pdf/");
else
define("FILE_FOLDER",".");

if ( $_SESSION["Email"]=="" )   exit();
$turtorial[支援文件][0][q] ="支援文件:"; //if only 1q
$files = scandir(FILE_FOLDER, SCANDIR_SORT_ASCENDING);
//var_dump($files);
for($i=0;$i<sizeof($files);$i++){
  if (strpos($files[$i],FILE_EXT)!== FALSE){
      $files_noext = preg_replace('/\\.[^.\\s]{3,4}$/', '', $files[$i]);
      if (FILE_FOLDER==".") $turtorial[支援文件][0][a][]="<a href='{$files[$i]}' target=blank>{$files_noext}</a>";
      else  $turtorial[支援文件][0][a][]="<a href='".FILE_FOLDER."{$files[$i]}' target=blank>{$files_noext}</a>";
  }
}//for
//var_dump($turtorial);

echo "<div class=bg_mid>";
echo "<div class=content>";
foreach($turtorial as $key=>$value_list){
	//echo "<font color=#0000FF size=5><B><I>".ucfirst($key)."</I></b></font>";
	echo "<font size=5><B>".ucfirst($key)."</b></font>";
	echo "<ol>";
	foreach($value_list as $key1=>$value){
		if (sizeof($value_list)>1)  //jinho add if only 1q, do not show
				echo "<li><b><font size=4>".(1+$key1).". {$value[q]}</font></b></li>";
		echo "<hr>";
		echo "<ul>";
		foreach($value[a] as $a_value)
				echo "<li> {$a_value}</li>";

		echo "</ul>";
		echo "<br><BR>";
	}
	echo "</ol>";
			
}
         
?>

