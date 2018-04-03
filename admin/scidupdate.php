<?php
/********************
 *Validated on Aug-22,2017, SCID info update for godwatch project
 *(must) update SCID list from db-> scid.php
 *optional parameter: 
 * default:   update SCID file, if admin is master do BACKUP, slave do INSTALL
 * INSTALL:  force write file from db
 * BACKUP:   force upload file to db 
 *select id, SCID, AID, account, crc,LENGTH(image) from customerservice.scidinfo;
 *      
 ********************/
require_once("/var/www/qlync_admin/doc/config.php");
//require_once("/var/www/qlync_admin/doc/mysql_connect.php");
require_once("/var/www/qlync_admin/doc/sql.php");

define("DEBUG_FLAG",false); //true/false :false will only print out
define("SQLTYPE","MYSQL"); //MYSQL //PDO
define("SCID_FILEPATH","{$home_path}/html/common/scid.php");
define("SCID_FOLDER","{$home_path}/html/scid/");
define("MYUUID",getRootFilesystemUUID());

$argvinstall=0;
$argvbackup=0;
if (isset($argv[1])){
	if ($argv[1] == "INSTALL")
		$argvinstall=1;
	else if ($argv[1] == "BACKUP"){
		$argvbackup=1;
	}
}
define("FIRST_INSTALL",$argvinstall);
define("BACKUP_IMG",$argvbackup);


function sqlInit($type="")
{
	global $mysql_ip, $mysql_id, $mysql_pwd;
	if ($type == "PDO")
	{
		$pdo = new PDO('mysql:host='.$mysql_ip, $mysql_id, $mysql_pwd, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
		$pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
		$pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
	}else{
		mysql_connect($mysql_ip,$mysql_id,$mysql_pwd)or die("Mysql Connecting Failed..!!");
		mysql_query("SET NAMES utf8");
		mysql_select_db("qlync");
	}
} 

function updateSCIDTable()
{
  include(SCID_FILEPATH);
 
  if ( ($scid['001']["name"] == "佛在線") )
	{
  $fp_menu=fopen(SCID_FILEPATH,"w");
  if (!DEBUG_FLAG) fwrite($fp_menu,'<?');
  $sql="select * from qlync.scid";
  sql($sql,$result,$num,0);
  for($i=0;$i<$num;$i++)
  {
      fetch($db,$result,$i,0);
      if (DEBUG_FLAG) echo "DEBUG: {$db['SCID']}={$db['Name']}\n"; 
      else fwrite($fp_menu, '$scid[\''.$db["SCID"].'\']["name"]="'.$db["Name"].'";'."\n"); 
  }
  if (!DEBUG_FLAG) fwrite($fp_menu,'?>');
  fclose($fp_menu);
  echo "Write SCID file!!\n";
  }
}
function isImgDBExist()
{
  $sql ="SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'customerservice' AND TABLE_NAME = 'scidinfo'";
  sql($sql,$result,$num,0);
  if ($num>0) return true;
  echo "customerservice.scidinfo table not exist!!!\n";
  return false;
}
function insertImage($service)
{
  if (!isImgDBExist()) return false;
  if (checkDBExist($service) ){
  	if (!isFileEqual($service)) //file changed
    		return updateImage($service);
    else{
    	echo "DB and file size/crc identical!!!\n";
			return false;
    }
  }else{
		if (DEBUG_FLAG) echo "insertImage\n";
//if (SQLTYPE == "PDO") return insertImagePDO($service);
		//$cksum = shell_exec("cksum {$path}");
		//$crc = crc32($service['image']); //different than loading of file
    $sql = "INSERT INTO customerservice.scidinfo SET
       SCID ='{$service['SCID']}',
       AID ='{$service['AID']}',
       account ='{$service['account']}',
       filename ='{$service['filename']}',
       uuid = '".MYUUID."',
       crc = '{$service['crc']}',
       create_date = '{$service['create_date']}',
       image = '".mysql_real_escape_string($service['image'])."'";

		if (DEBUG_FLAG) echo "{$service['SCID']}/{$service['AID']}/{$service['account']}/{$service['filename']},{$crc} @".MYUUID."\n";  
    else{
				sql($sql,$result,$num,0);
    	  return $result;
    }
  }//insert
  return true;
}
function updateImage($service)
{//path is the same,
	if (DEBUG_FLAG) echo "updateImage\n";
//if (SQLTYPE == "PDO") return updateImagePDO($service);
//	if (DEBUG_FLAG) 
//		return setDBvalue(getDBvalue($service,"id"),"crc",$service['crc']);

  $sql = "UPDATE customerservice.scidinfo SET
     crc = '{$service['crc']}',
     create_date = '{$service['create_date']}',
     uuid = '".MYUUID."',
     image = '".mysql_real_escape_string($service['image'])."'
		 WHERE SCID ='{$service['SCID']}' and AID ='{$service['AID']}'
     and account ='{$service['account']}' and filename ='{$service['filename']}'";
	if (DEBUG_FLAG) echo "{$service['SCID']}/{$service['AID']}/{$service['account']}/{$service['filename']} update {$crc} @".MYUUID."\n";  
  else sql($sql,$result,$num,0);

  return $result;
}

function insertImagePDO($service)
{
  global $pdo;
  $table = 'customerservice.scidinfo';
  $sql = "INSERT INTO $table SET SCID=:pSCID, AID=:pAID, account=:paccount, filename=:pfilename, image=:pimage";
  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(':pSCID',$service['SCID'],PDO::PARAM_STR);
  $stmt->bindParam(':pAID',$service["AID"],PDO::PARAM_STR);
  $stmt->bindParam(':paccount',$service["account"],PDO::PARAM_STR);
  $stmt->bindParam(':pfilename',$service['filename'],PDO::PARAM_STR);
  $stmt->bindParam(':pimage',$service['image'],PDO::PARAM_LOB);
  $b = false;
  try {
    $stmt->execute();
    $b = true;
  }catch(Exception $e){ echo $e->getMessage();}
  return $b;
}

function updateImagePDO($service)
{
  global $pdo;
  $table = 'customerservice.scidinfo';
  $sql = "UPDATE $table SET SCID=:pSCID, AID=:pAID, account=:paccount, filename=:pfilename, image=:pimage";
  $stmt = $pdo->prepare($sql);
  $stmt->bindParam(':pSCID',$service['SCID'],PDO::PARAM_STR);
  $stmt->bindParam(':pAID',$service["AID"],PDO::PARAM_STR);
  $stmt->bindParam(':paccount',$service["account"],PDO::PARAM_STR);
  $stmt->bindParam(':pfilename',$service['filename'],PDO::PARAM_STR);
  $stmt->bindParam(':pimage',$service['image'],PDO::PARAM_LOB);
  $b = false;
  try {
    $stmt->execute();
    $b = true;
  }catch(Exception $e){ echo $e->getMessage();}
  return $b;
}

function checkDBExist($service)
{//return true/false
  $sql = "SELECT id FROM customerservice.scidinfo WHERE
     SCID ='{$service['SCID']}' and AID ='{$service['AID']}'
     and account ='{$service['account']}'
     and filename ='{$service['filename']}'";
  sql($sql,$result,$num,0);
  if ($num > 0)
			return true;
  return false; //not exist
}

function getDBvalueByID($id,$type)
{
	$sql = "SELECT *,LENGTH(image) as imagesize FROM customerservice.scidinfo WHERE id ={$id}";
	sql($sql,$result,$num,0);
  if ($num == 1){
  	fetch($arr,$result,0,0);
  	return $arr[$type];
	}
	return null;
}
function getDBvalue($service,$type)
{//imagesize
  $sql = "SELECT *,LENGTH(image) as imagesize FROM customerservice.scidinfo WHERE SCID ='{$service['SCID']}'";
  if (isset($service['AID']))
  	$sql .=" and AID ='{$service['AID']}'";
  if (isset($service['account']))
    $sql .=" and account ='{$service['account']}'";
  if (isset($service['filename']))
    $sql .=" and filename ='{$service['filename']}'";
  sql($sql,$result,$num,0);
  if ($num == 1)
  {
  	fetch($arr,$result,0,0);
		return $arr[$type];
	}
	return null;
}
function setDBvalue($id, $field, $value)
{//except image
	$sql = "UPDATE customerservice.scidinfo SET {$field} ='{$value}' WHERE id = {$id}";
  sql($sql,$result,$num,0);
  return $result;
}
function isFileEqual($service)
{
	$filepath = createImgPath($service);
	$crcFileInt = crc32(file_get_contents($filepath));
	$sizeFileInt = (int) filesize($filepath); 
	$crcDB = getDBvalue($service,"crc");
	$crcDBInt = (is_null($crcDB)) ? 0 : (int)$crcDB;
	$sizeDB = getDBvalue($service,"imagesize");
	$sizeDBInt = (is_null($sizeDB)) ? 0 : (int)$sizeDB;
	if ($sizeFileInt == $sizeDBInt ) //file same
		if ($crcFileInt == $crcDBInt ) //crc
		 	return true;
		else echo "file size is the same, but crc changed(F={$crcFileInt}:DB={$crcDBInt})!!\n";
	else echo "file size changed(F={$sizeFileInt}:DB={$sizeDBInt})!!\n";
	return false;
}

function isFileNew($service)
{
		$dbdate = getDBvalue($service,"create_date");
		if (is_null($dbdate)) return false;
		$dbfileTime = strtotime($dbdate);

		$filepath = createImgPath($service);
		$filedate = filemtime($filepath);
		$fileLastModifyTime = strtotime($filedate);

		//$diff = abs($fileLastModifyTime - $dbfileTime);
if (DEBUG_FLAG) echo "Local File date=".date("Y-m-d H:i:s",$filedate).", DB date={$dbdate}.\n";

		if ($fileLastModifyTime > $dbfileTime) //if ($diff->i > 0)
		{    //file is newer
			echo "Local File(".date("Y-m-d H:i:s",$filedate).") is Updated, newer than DB {$dbdate}.\n";
			return true;
		}
	return false;
}
function writeImage($path, $data)
{
  if (!DEBUG_FLAG) file_put_contents($path, $data);
	echo "DEBUG: call write Image to {$path}.\n";
}
function updateSCIDfile() { //write to local
    if (!isImgDBExist()) return false;
    $sql = "SELECT * FROM customerservice.scidinfo";
    sql($sql,$result,$num,0);
    if ($num > 0)
    {
      for ($i=0; $i<$num; $i++){
        fetch($arr,$result, $i,0);
        $imgPath = createImgPath($arr);

    		if (file_exists($imgPath)){ //file exist
        	echo "file {$imgPath} exist!\n";
					if (! isFileEqual($arr)) //file is different 
							writeImage($imgPath,$arr['image']);
					else echo "DB and file size/crc identical!!!\n";
        }else writeImage($imgPath,$arr['image']);
      }
    }
}
function createImgPath($arr)
{
	if (is_null($arr)) return "";
	$imgPath = SCID_FOLDER . $arr['SCID'];
  if (!is_dir($imgPath))   mkdir($imgPath, 777);
  if ($arr['AID']!="")  {
			$imgPath .= "/".$arr['AID'];
			if (!is_dir($imgPath))   mkdir($imgPath, 777);
	}
  if ($arr['account']!="") {
		$imgPath .= "/".$arr['account'];
		if (!is_dir($imgPath))   mkdir($imgPath, 777);
	}  
  $imgPath .= "/".$arr['filename'];
  return $imgPath;
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
          $img = array("SCID"=>"","AID"=>"","account"=>"","filename"=>"","image"=>"","crc"=>0); 
          $fdir = dirname($curPATH);
          $img['filename'] = basename($curPATH);
          $img['image'] = file_get_contents($curPATH);
          $img['create_date'] = date("Y-m-d H:i:s",filemtime($curPATH));
          $img['crc'] = crc32($img['image']);

          $fdir = ltrim($fdir,SCID_FOLDER);
          $fpath = explode("/",$fdir);
          for ($i=0;$i<sizeof($fpath);$i++){
            if ($i==0) $img['SCID'] = $fpath[$i];
            else if ($i==1) $img['AID'] = $fpath[$i];
            else if ($i==2) $img['account'] = $fpath[$i];

          }

					if (insertImage($img)) echo "set {$fdir}/{$img['filename']} to DB\n";        
				}else if(is_dir($curPATH)) 
            scanFolderFiles($curPATH);
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
		if (DEBUG_FLAG) echo "single admin, act as Master\n";
		return true;  //single Admin, always master
	}
	return false;
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
/***************** MAIN ****************************/
sqlInit(SQLTYPE);
updateSCIDTable();


//backup image to database if master
if ( (isMaster()) or (BACKUP_IMG) ){
	if (!FIRST_INSTALL) //force install does not backup
		scanFolderFiles(SCID_FOLDER);
}

//write file to local for new installation,
if ( (!isMaster()) or (FIRST_INSTALL))
	if (!BACKUP_IMG) // if force BACKUP, do not write file
		updateSCIDfile();
?>