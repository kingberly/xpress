<?php
/********************
 *Validated on Aug-24,2017, admin HA file sync
 * sync the latest change file between 1~24hour
 * for slave only 
 * sudo apt-get install --force-yes -y sshpass   
 *	int(10) = unsigned int = max 4G file 
 *parameter: 
 *    root folder /var/www/qlync_admin, /var/tmp     
 ********************/
ini_set('memory_limit', '64M');
require_once("/var/www/qlync_admin/doc/config.php");
//require_once("/var/www/qlync_admin/doc/mysql_connect.php"); //sqlInit();
require_once("/var/www/qlync_admin/doc/sql.php");

define("DEBUG_FLAG",false); //true/false :false will only print out


define("MYUUID",getRootFilesystemUUID());
define("MYLANIP",getLanIP());
define("SCP_PWD","1qazxdr56yhN");
define("SCP_USER","ivedasuper");
define("SSH_PY","/home/ivedasuper/admin/check/sshgetFile.py");
//define("DETECT_HR",24); //24 hour older file
//define("DETECT_HR_END",24*7); //within one week
define("DETECT_DAY",0); //24 hour older file
define("DETECT_DAY_END",35); //24 hour older file

// /var/tmp/monthly_bill_update.log

$argv1=0;$argv2=0;
if (isset($argv[1])){
	if ( is_dir($argv[1]) or is_file($argv[1]) ){
		define("ROOT_FOLDER",$argv[1]);
		if (isset($argv[2])){
			 if ($argv[2] == "BACKUP") $argv2=1;
		}
	}else if ($argv[1] == "INSTALL")
     $argv1=1;
	else die("Root Folder/File Not Exist!!!\n");
}else die("No Parameter Input!!! (/folder BACKUP or INSTALL)\n");

define("FIRST_INSTALL",$argv1);
define("BACKUP_FILE",$argv2);

function sqlInit($type="")
{
	global $mysql_ip, $mysql_id, $mysql_pwd;
		mysql_connect($mysql_ip,$mysql_id,$mysql_pwd)or die("Mysql Connecting Failed..!!\n");
		mysql_query("SET NAMES utf8");
		mysql_select_db("qlync");
} 


function isDBExist()
{
  $sql ="SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'customerservice' AND TABLE_NAME = 'syncinfo'";
  @sql($sql,$result,$num,0);
  if ($num>0) return true;
  echo "customerservice.syncinfo table not exist!!!\n";
  return false;
}
function insertFile($service)
{
  if (!isDBExist()) return false;
  if (checkDBExist($service) ){
  	if (!isFileEqual($service)) //file changed
    		return updateFile($service);
    else{
    	echo "DB and file size/crc identical!!!\n";
			return false;
    }
  }else{
		if (DEBUG_FLAG) echo "insertFile\n";
    $sql = "INSERT INTO customerservice.syncinfo SET
       filepath ='{$service['filepath']}',
       filesize ={$service['filesize']},
       sourceIP = '".MYLANIP."',       
       uuid = '".MYUUID."',
       file_date ='".date("Y-m-d H:i:s",filemtime($service['filepath']))."',
       crc = '{$service['crc']}'";

		if (DEBUG_FLAG) echo "{$service['filepath']}/{$service['filesize']},{$service['crc']} @".MYUUID."\n";  
    else{
				@sql($sql,$result,$num,0);
    	  return $result;
    }
  }//insert
  return true;
}
function updateFile($service)
{//path is the same,
	if (DEBUG_FLAG) echo "updateFile\n";
  $sql = "UPDATE customerservice.syncinfo SET
		filesize = {$service['filesize']},
		crc = '{$service['crc']}',
		sourceIP = '".MYLANIP."'
		file_date ='".date("Y-m-d H:i:s",filemtime($service['filepath']))."',
		WHERE filepath='{$service['filepath']}' and uuid = '".MYUUID."'";
	if (DEBUG_FLAG) echo "{$service['filepath']} update {$service['filesize']}/{$crc} @".MYUUID."\n";  
  else @sql($sql,$result,$num,0);

  return $result;
}
function deleteDBbyID($id)
{
	  $sql = "DELETE from customerservice.syncinfo WHERE id={$id}";
	  if (!DEBUG_FLAG)
	  @sql($sql,$result,$num,0);
}

function checkDBExist($service)
{//return true/false
  $sql = "SELECT id FROM customerservice.syncinfo WHERE
     uuid='".MYUUID."' and filepath ='{$service['filepath']}'";
  @sql($sql,$result,$num,0);
  if ($num > 0)
			return true;
  return false; //not exist
}

function getDBvalueByID($id,$type)
{
	$sql = "SELECT * FROM customerservice.syncinfo WHERE id ={$id}";
	sql($sql,$result,$num,0);
  if ($num == 1){
  	fetch($arr,$result,0,0);
  	return $arr[$type];
	}
	return null;
}
function getDBvalue($service,$type)
{//imagesize
  $sql = "SELECT * FROM customerservice.syncinfo WHERE filepath ='{$service['filepath']}'";
  sql($sql,$result,$num,0);
  if ($num == 1)
  {
  	fetch($arr,$result,0,0);
		return $arr[$type];
	}
	return null;
}
function setDBvalue($id, $field, $value)
{
	if ($field == "filesize")
		$sql = "UPDATE customerservice.syncinfo SET {$field} ={$value} WHERE id = {$id}";
	else
	$sql = "UPDATE customerservice.syncinfo SET {$field} ='{$value}' WHERE id = {$id}";
  @sql($sql,$result,$num,0);
  return $result;
}
function isSameSourceIP($sIP)
{
	if ($sIP == MYLANIP) return true;
	return false;
}
function isFileEqual($service)
{
	$crcFileInt = crc32(file_get_contents($service['filepath']));
	$sizeFileInt = (int) filesize($service['filepath']); 
	$crcDB = getDBvalue($service,"crc");
	$crcDBInt = (is_null($crcDB)) ? 0 : (int)$crcDB;
	$sizeDB = getDBvalue($service,"filesize");
	$sizeDBInt = (is_null($sizeDB)) ? 0 : (int)$sizeDB;
	if ($sizeFileInt == $sizeDBInt ) //file same
		if ($crcFileInt == $crcDBInt ) //crc
		 	return true;
		else echo "file size is the same, but crc changed(F={$crcFileInt}:DB={$crcDBInt})!!\n";
	else echo "file size changed(F={$sizeFileInt}:DB={$sizeDBInt})!!\n";
	return false;
}

function getRemoteFile($path,$tip,$target="")
{
	if ($target == "") $target = $path;
  //backup before overwrite
  if (file_exists($target))	shell_exec("cp {$target} {$target}.bak");
	if (!createFolder(dirname($target))) die("local folder is not writable!\n");
/*
	pkgInstall();
//sshpass -p 'password' scp user1@xxx.xxx.x.5:sys_config /var/www/dev/
	$cmd = "sshpass -p '".SCP_PWD."' scp ".SCP_USER."@{$tip}:{$path} {$target}";
*/
	//sshgetFile.py required igsssh.py
	$cmd = "python ".SSH_PY." ".SCP_USER.":".SCP_PWD."@{$tip}{$path} $target";
	if (DEBUG_FLAG) echo "DEBUG:{$cmd}\n";
  $res = shell_exec($cmd);	
  if (strpos($res,"No such file")!==FALSE) return false;
  else if (strpos($res,"Errno")!==FALSE) return false; //python only
  echo "Get File ({$path}@{$tip}) to {$target}.\n";
	return true;
}

function createFolder($path)
{
	if (is_dir($path)) return true;
	$prev_path = substr($path, 0, strrpos($path, '/', -2) + 1 );
	if (DEBUG_FLAG) echo "check folder {$prev_path}";
	$return = createFolder($prev_path);
	return ($return && is_writable($prev_path)) ? @mkdir($path) : false;
}

function deleteFile($path)
{
	if (!DEBUG_FLAG){
		$res = shell_exec("rm -rf {$path}");
		$res= rtrim($res, "\n");
		if ($res !="") return false;
	}
	return true;
}
function isDBDateNew($dbdatestr, $filepath)
{
	$dbdate = strtotime($dbdatestr);
	$filedate = filemtime($filepath); //int
	if (DEBUG_FLAG) echo "dbdate={$dbdate}, file date={$filedate}.\n";
  
	if ($dbdate > $filedate) return true;
	return false;
}
function isFirstFileNew($preFilepath, $postFilepath)
{
	$filedate1 = filemtime($preFilepath);
	$fileLastModifyTime1 = strtotime($filedate1);
	$filedate2 = filemtime($postFilepath);
	$fileLastModifyTime2 = strtotime($filedate2);
	if (DEBUG_FLAG) echo "1st Filedate={$filedate1}, 2nd  date={$filedate2}.\n";

	if ($fileLastModifyTime1 > $fileLastModifyTime2)  return true;
	return false;
}
function mergeLog($local,$oldlog)
{
	if (DEBUG_FLAG) echo $local."\n";
	if (!file_exists($local)) return false;
	if (DEBUG_FLAG) echo $oldlog."\n";
	if (!file_exists($oldlog)) return false;
	$preLog = file_get_contents($oldlog); 
	$postLog = file_get_contents($local);
	if (DEBUG_FLAG) echo "DEBUG: file Merged to {$local}\n";
	if (file_put_contents($local, $preLog.PHP_EOL.$postLog))
		return true;
	return false;
}
function syncFile() { //write to local
    if (!isDBExist()) return false;
    $sql = "SELECT * FROM customerservice.syncinfo";
    sql($sql,$result,$num,0);
    if ($num > 0)
    {
      for ($i=0; $i<$num; $i++){
        fetch($arr,$result, $i,0);
        if (MYLANIP == $arr['sourceIP']) continue;
        if (MYUUID == $arr['uuid']) continue;
    		if (file_exists($arr['filepath'])){ //file exist
    			if (isDBDateNew($arr['file_date'],$arr['filepath']))
        			echo "(EXISTED)Remote file {$arr['filepath']} is Latest!!\n";
        	else echo "(EXISTED)Local file {$arr['filepath']} is Latest!!\n";
        	
					if (! isFileEqual($arr)){ //file is different
						//log file merge?
						if (isMergeFile($arr['filepath'])){
							echo "Merging File Begin:\n";
							if (isDBDateNew($arr['file_date'],$arr['filepath'])){
								echo "Remote File is Latest, Stop Merging\n";
								continue;
							}
							$tmp = "/var/tmp/mtmp"; 
							if (getRemoteFile($arr['filepath'],$arr['sourceIP'],$tmp)){
								if (mergeLog($arr['filepath'],$tmp)){
									deleteFile($tmp);
									deleteDBbyID($arr['id']); //clean after download
								}else echo "Merge File Fail\n";
							}else "download File FAIL\n";
						}else{//not merging, overwrite older
							if (isDBDateNew($arr['file_date'],$arr['filepath'])) 
								if (getRemoteFile($arr['filepath'],$arr['sourceIP'])){
									echo "Overwrite older local file {$arr['filepath']}!!\n";
									deleteDBbyID($arr['id']);
								}else echo "download file FAIL!\n";
							else echo "Local File {$arr['filepath']} is latest, skip download\n";
						}
					}else{ //equal
						echo "DB and file size/crc identical!!!\n";
						deleteDBbyID($arr['id']);
					}
        }else if (getRemoteFile($arr['filepath'],$arr['sourceIP'])) deleteDBbyID($arr['id']);

      }
    }
}
function isMergeFile($filepath)
{
	$folder = "/var/tmp/";
	$mergeList = ["rtmpd_connection_t04.log","tunnel_connection_t04.log",
	"rtmpd_connection_const.log","tunnel_connection_const.log",
	"tunnel_daily.log","web_daily.log", "partner_daily.log", "evo_daily.log"];
	foreach ($mergeList as $value){
		if (DEBUG_FLAG) echo "check {$filepath} vs {$value}\n";
    $pattern = "/^".str_replace("/","","{$folder}{$value}")."$/";
		if ( preg_match($pattern,str_replace("/","",$filepath)) ) return true;
		//if (strpos($filepath,$value)!==FALSE) return true;
	}
	return false; 
} 
function scanFolderFiles($dir){//update to db
    $ffs = scandir($dir);
    unset($ffs[array_search('.', $ffs, true)]);
    unset($ffs[array_search('..', $ffs, true)]);
    // prevent empty ordered elements
    if (count($ffs) < 1)
        return;
    
    foreach($ffs as $ff){
      $curPATH = $dir.'/'.$ff;
        if (is_file($curPATH)){
          $img = array("filepath"=>"","filesize"=>0,"crc"=>0); 
          $fdir = dirname($curPATH);
          $img['filepath'] = $curPATH;
          $img['filesize'] = filesize($curPATH);
          $img['crc'] = crc32(file_get_contents($curPATH));

          $fdir = ltrim($fdir,SCID_FOLDER);
					if (insertFile($img)) echo "set {$img['filepath']} to DB\n";        
				}else if(is_dir($curPATH)) 
            scanFolderFiles($curPATH);
    }
}

function parseFolderFiles($dir)
{
	//$intMinOld = DETECT_HR * 60; //24hr old file
	//$intMinOldEnd = DETECT_HR_END * 60;
	//$param ="-mmin +{$intMinOld} -mmin -{$intMinOldEnd}" ;
  if (DETECT_DAY == 0)  $param ="-mtime -".DETECT_DAY_END ;
  else	$param ="-mtime +".DETECT_DAY. " -mtime -".DETECT_DAY_END ;
	if (strpos($dir,"/var/tmp")!==FALSE) $dir .= " -name '*.log*'"; 
	else if (strpos($dir,"/var/www/qlync_admin")!==FALSE) $dir .= " -name '*.php' -o -name '*.log' -o -name '*.inc' -o -name '*.png'";
	$cmd = "find {$dir} -type f {$param} -ls | awk {print'$11'}";
	echo "check list: {$cmd}\n";
	$res = shell_exec ($cmd);
	$res= rtrim($res, "\n");
	$fileArr = explode("\n",$res);
	if (empty($fileArr)) return;
	if (DEBUG_FLAG) var_dump($fileArr);
	foreach ($fileArr as $path)
	{
		if ($path =="") break;
		if (strpos($path,"_min.log")!==FALSE) {echo "skip {$path}\n";continue;}
		if (strpos($path,"_hourly.log")!==FALSE) {echo "skip {$path}\n";continue;}
    if (strpos($path,"_daily.log")!==FALSE) {echo "skip {$path}\n";continue;}
		if (strpos($path,"/log/")!==FALSE) {echo "skip {$path}\n";continue;}		
    $img = array("filepath"=>"{$path}","filesize"=>0,"crc"=>0); 
    $img['filesize'] = filesize($path);
    $img['crc'] = crc32(file_get_contents($path));
		if (insertFile($img)) echo "set {$path} to DB\n";
	}
}

function isMaster()
{
	$hastat = exec ("ps -ax | grep [h]eartbeat");
	if ($hastat != ""){
    //stdout = os.popen("ifconfig | grep eth0:0 | wc -l")
    //isMaster = stdout.read().replace("\n", "")
    $stat = exec ("ifconfig | grep eth0:0 | wc -l");
    $stat = trim ( $stat , " \t\n\r\0\x0B");
    //if (DEBUG_FLAG) echo "master={$stat}\n";
    if (intval($stat) == 1)
    	return true;
	}else{
		if (DEBUG_FLAG) echo "single admin, No need to sync\n";
		return false;  //single Admin, no need to sync
	}
	return false;
}
function getLanIP()
{
  if (exec ("ifconfig | grep eth0") == "")  die("Not VM Network Adpator(eth0) Avail!!\n");
	$res = shell_exec("/sbin/ifconfig eth0 | grep 'inet addr:' | cut -d: -f2 | awk '{ print $1}'");
	$res= rtrim($res, "\n");
	return $res; 
}
function getRootFilesystemUUID()
{//$output=shell_exec("blkid | awk '/{$devID}/{print $2}'");
	$dirname = "/dev/disk/by-uuid/";
	$devID=shell_exec("df /root | grep dev | awk {print'$1'}"); // /dev/sda1
	$devID = ltrim($devID,"/dev/");
	$devID= rtrim($devID, "\n");
	$cmd = "ls -al {$dirname} | awk '/{$devID}/{print $9}'";
		//= "ls -al {$dirname} | grep '{$devID}' | awk {print'$9'}"; 
	$res= shell_exec($cmd);
	$res= rtrim($res, "\n");
	return $res;
}

function pkgInstall()
{
	$res = shell_exec("dpkg -l  | grep 'sshpass'");
	//if (DEBUG_FLAG) echo "dpkg sshpass={$res}";
	if (rtrim($res, "\n") == "") 
		$res = shell_exec("sudo apt-get install --force-yes -y sshpass");
	//if (DEBUG_FLAG) echo "install sshpass={$res}";
}
/***************** MAIN ****************************/
sqlInit();

//backup to database if master
if (isMaster() or (BACKUP_FILE)) {
	if (!FIRST_INSTALL) 
		if (is_file(ROOT_FOLDER)){
		    $img = array("filepath"=>ROOT_FOLDER,"filesize"=>0,"crc"=>0); 
		    $img['filesize'] = filesize(ROOT_FOLDER);
		    $img['crc'] = crc32(file_get_contents(ROOT_FOLDER));
		    if (DEBUG_FLAG) var_dump($img);
				if (insertFile($img)) echo "set ".ROOT_FOLDER." to DB\n";        
		}else	parseFolderFiles(ROOT_FOLDER);
}

//update file to local for new installation,
if (!isMaster() or (FIRST_INSTALL==1)){
	if (!file_exists(SSH_PY)) die("sshgetFile.py Not Exist for download file!!!\n");
	if (!BACKUP_FILE) syncFile();
}
?>