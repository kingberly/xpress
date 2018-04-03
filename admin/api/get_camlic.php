<?php
/****************
 *Validated on Jun-22,2017,
 * activation_date | bind_account | resolution | 
 *  service_package | days_storage | data_date 
 *Writer: JinHo, Chang
*****************/

include("/var/www/qlync_admin/doc/config.php");
include("/var/www/qlync_admin/doc/mysql_connect.php"); 
include("/var/www/qlync_admin/doc/sql.php");

function queryLicDB($mac,$type)
{
  $sql = "select {$type} from licservice.qlicense where mac ='{$mac}'";
  sql($sql,$result,$num,0);

  if ($num > 0){
      fetch($arr,$result,0,0);
      return $arr[$type];  
  }
  return "-1";
}

function queryLicDBArray(&$macArr,$mac)
{
  $sql = "select mac, code, CID,Hw, Filename from licservice.qlicense where mac like '%{$mac}%'";
  sql($sql,$result,$num,0);

  if ($num > 0){
	  for($i=0;$i<$num;$i++)
	  {
      fetch($arr,$result,$i,0);
      array_push($macArr, $arr);
		}
		return $macArr;  
  }
  return null;
}

$sql="SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = 'licservice'";
//$sql="SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'licservice' and TABLE_NAME='qlicense'";
sql($sql,$result,$num,0);
if ($num == 0){ 
	echo "licservice Not Exist";
	exit;
}
  
if($_REQUEST["id"]==$internal_api_id and $_REQUEST["pwd"]==$internal_api_pwd)
{
  if ( isset($_REQUEST["mac"]) )
    if (ereg("[A-Za-z0-9]{12}",$_REQUEST["mac"]))
    {//correct 12 digit MAC
        echo queryLicDB($_REQUEST["mac"],"code");
		}else{
				$resArray= array();
				if (!is_null(queryLicDBArray($resArray,$_REQUEST["mac"]))){
					echo "MAC;Code;CID;Model;Note;<br>";
					for($i=0;$i<sizeof($resArray);$i++)
	  			{
	  				  echo $resArray[$i][mac].";".$resArray[$i][code].";".$resArray[$i][CID].";".$resArray[$i][Hw].";".$resArray[$i][Filename].";<br>";
					}
				}else{
					echo "Fail";
				}
		}
}else{ 	//echo 'Please Enter Correct ID/ PWD !';
  echo 'Fail';
}

?>
