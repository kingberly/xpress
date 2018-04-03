<?php
/****************
 *Validated on Aug-17,2017
 *maintain db for camera RMA/replace
 *Add Source IP original check for non-admin user
 *Add video list via MAC admin feature
 *Add new account api
 *Add switch account api
 *fix MobileCam set as RVHI issue       
 *Writer: JinHo, Chang
*****************/
include("/var/www/qlync_admin/doc/config.php");
include("/var/www/qlync_admin/doc/mysql_connect.php"); 
include("/var/www/qlync_admin/doc/sql.php");
header("Content-Type:text/html; charset=utf-8");
require_once ("_iveda.inc");
if (file_exists("/var/www/qlync_admin/plugin/rpic/_iveda.inc")){
  if (!isset($CamCID))
      require_once ("/var/www/qlync_admin/plugin/rpic/_iveda.inc");
  else die("missing camera variable include file.");
}
define("MSG_SUCCEED"," 成功!");
define("MSG_FAIL"," 失敗!");
define("PAGE_LIMIT",10);
define("DEBUG_FLAG","OFF"); //OFF ON
//0name,1pwd,2Lic#,3apiURL,4URL,5LBpwd,6apiPwd,7camset+1,8playlist
$siteAdminArrayInfo = [
'T04'=>array('台北',"Ea9M7gOu586UQaOtXJ3e6f51",80,'https://rpic.taipei:8080/html/api/',"rpic.taipei","1qazxdr56yhN","ivedaManageUser:ivedaManagePassword","/camera_setting.php?adminpwd=","/plugin/taipei/playback_list.php?adminuser=tperpic&adminpwd=Ea9M7gOu586UQaOtXJ3e6f51"),
'T05'=>array('桃園',"tN8V8bMTtuKycj7BNW2Esp8p",49,'https://rpic.tycg.gov.tw:8080/html/api/',"rpic.tycg.gov.tw","1qazxdr56yhN","ivedaManageUser:ivedaManagePassword","/camera_setting.php?adminpwd=","/plugin/ty/playback_list.php?adminuser=tyrpic&adminpwd=U2QUU4EHHRTQp6rynmz4"),
'K01'=>array('高雄',"ydEP6Ug6uBzWTXU28gfSV3hu",271,'https://kreac.kcg.gov.tw:8080/html/api/',"kreac.kcg.gov.tw","1qazxdr56yhN","megasysManageUser:amICAnCeDiNgEntAtiDE","/camera_setting.php?adminpwd=","/plugin/rpic/playback_list.php?adminuser=kcgrpic&adminpwd=x6h8YzgKpBunCMDWYL9u"),
'C13'=>array('奇美',"Cqjf6NE2R5xY5unCMXVDqqmP",100,'https://engeye.chimei.com.tw:8080/html/api/',"engeye.chimei.com.tw","1qazxdr56yhN","chimeiManageUser:cHsCtmeQYNFJwsCTffux","/camera_setting.php?adminpwd=","/plugin/rpic/playback_list.php?adminuser=rpic&adminpwd=Cqjf6NE2R5xY5unCMXVDqqmP"),
'SYS'=>array('',"ydEP6Ug6uBzWTXU28gfSV3hu",200,'https://59.124.70.90:8080/html/api/'),
//'T06'=>array('工務',"vzEG2Ea6fWzEGb5Ea235uZvm",49,'https://workeye.megasys.com.tw:8080/html/api/'),
'X01'=>array('',"ydEP6Ug6uBzWTXU28gfSV3hu",200,'https://xpress2.megasys.com.tw:8080/html/api/'),
'X02'=>array('Xpress',"ydEP6Ug6uBzWTXU28gfSV3hu",100,'https://xpress.megasys.com.tw:8080/html/api/',"xpress.megasys.com.tw","1qazxdr56yhN","ivedaManageUser:ivedaManagePassword","/camera_setting.php?adminpwd=ydEP6Ug6uBzWTXU28gfSV3hu","/plugin/rpic/playback_list.php?adminuser=megasys&adminpwd="),
'RPIC'=>array('',"ydEP6Ug6uBzWTXU28gfSV3hu",0)
];

$siteAdminName = array();
$jsSITE = array();
foreach ($siteAdminArrayInfo as $key=> $data){
  if ($data[0]=="") continue;
  $siteAdminName[$key] = $data[0];
  //$siteLicNumber[$key] = $data[2];
  //$RPICAPP_USER_PWD[$key] =$data[1];
  //$siteAdminArray[$key] =$data[3];
  array_push($jsSITE,array($key,$data[0],$data[4],$data[5],$data[6],$data[7].$data[1],$data[8]));
}

$infoArray['type'] = [
"NEW"=>"新增",
"RMA"=>"退還",
"DEL"=>"已刪除",
"CHG"=>"修改"
];
$infoArray['note'] = [
"ADD"=>"上傳授權",
"PKG-P1"=>"錄影畫質最高設定",
"PKG-P3"=>"Mobile錄影設定",
"DAYS-"=>"錄影天數設定",
"BIND"=>"綁定成功",
"RMA"=>"取得攝影機帳號資訊",
"with"=>"替換攝影機MAC",
"CHG"=>"攝影機切換帳號",
"BIND to"=>"攝影機綁定至帳號",
];

function printArray($arr, $type="")
{
if (DEBUG_FLAG=="ON") var_dump($arr);
	$html = "";
	foreach ($arr as $key=>$value)
	{
		if ($type=="tooltip"){
			$html .= "{$key}{$value};";
		}else{
			$html .= "{$key}:{$value};";
		}
	}
	return $html;
}
$submiter="";
if ( (strpos($_SESSION["Email"],"maintain")!==false) or
 (strpos($_SESSION["Email"],"admin@localhost.com")!==false))
{ //login
  $submiter=$_SESSION["Email"];
}else if (chkSourceFromMegasys($submiter)){//company ip
  //echo "{$submiter}您好:";
}else{
  //echo "沒有存取權限!! 請登入";
  //exit();
}

$cmdArray = [
'bind'=>'bind_camera.php?id=root&pwd=1qazxdr5',
'unbind'=>'unbind_camera.php?id=root&pwd=1qazxdr5',
'delete'=>'delete_camera.php?id=root&pwd=1qazxdr5',
'add'=>'add_camera.php?id=root&pwd=1qazxdr5',
'recycle180'=>'camera_recycle_setting.php?id=root&pwd=1qazxdr5&days=180',
'recycle7'=>'camera_recycle_setting.php?id=root&pwd=1qazxdr5&days=7',
'package0'=>'camera_package_setting.php?id=root&pwd=1qazxdr5&&resolution=0&package=AR',
'package1'=>'camera_package_setting.php?id=root&pwd=1qazxdr5&&resolution=1&package=AR',
'package2'=>'camera_package_setting.php?id=root&pwd=1qazxdr5&&resolution=2&package=AR',//mobile
'get_date'=>'mgmt_enduser.php?id=root&pwd=1qazxdr5&command=get_date',
'getresol'=>'getinfo_camera.php?id=root&pwd=1qazxdr5&mode=SQL&type=resolution',
'getaccount'=>'getinfo_camera.php?id=root&pwd=1qazxdr5&mode=SQL&type=bind_account'
];
if (DEBUG_FLAG == "ON") var_dump($_REQUEST);
/*
if (($_REQUEST["mac"]!="") and strlen($_REQUEST["mac"]) <> 12){
  $msg_err.= "<div id=\"info\" class=\"error\">".$_REQUEST["mac"]. "非正確MAC</div>";
}else if (($_REQUEST["mac"]!="") and !ereg("[A-Za-z0-9]{12}",$_REQUEST["mac"])){
  $msg_err.= "<div id=\"info\" class=\"error\">".$_REQUEST["mac"]. "非正確MAC</div>"; 
}else */ 
if (isset($_REQUEST["rma"]) ){
  if ($_REQUEST["mac"]!="") $_REQUEST["mac"]=strtoupper($_REQUEST["mac"]);
  $account=getCamStatus($_REQUEST["oemid"],$_REQUEST["mac"],"getaccount");
  $title="退還RMA MAC: ";
  if ($account=="-1") 
    $msg_err .= "<div id=\"info\" class=\"error\">".$title.$_REQUEST["mac"]. MSG_FAIL. "</div>";
  else
    if (insertDB($_REQUEST["oemid"],$_REQUEST["mac"],$account,"RMA") )
      $msg_err .= "<div id=\"info\" class=\"success\">".$title.$_REQUEST["mac"]. MSG_SUCCEED. "</div>";
    else $msg_err .= "<div id=\"info\" class=\"error\">".$title.$_REQUEST["mac"]. MSG_FAIL. "</div>";
//-------------------add / switch-------------------------------------------
}else if (isset($_REQUEST['btn_newmac']) ){
//-------------------add
  if ($_REQUEST['newaccount']!=""){//new
    $oldaccount = checkAccountExist($_REQUEST["oemid"],$_REQUEST['newaccount']) ;
    $_REQUEST["mac"] = $_REQUEST["apimac"];
    $title="新增攝影機到帳號 {$oldaccount}: "; 
    if ($oldaccount=="-1")
        $msg_err .= "<div id=\"info\" class=\"error\">".$title. MSG_FAIL. " 帳號不存在</div>";
      else{//old account ok
          if ($_REQUEST["mac"]!="") $_REQUEST["mac"]=strtoupper($_REQUEST["mac"]); 
          if (setCameraLicense($_REQUEST["oemid"],$_REQUEST["mac"],"add","")){
              insertDB($_REQUEST["oemid"],$_REQUEST["mac"],$_REQUEST['newaccount'],"NEW"); //insert after set OK
              $newid=getDBidByNEWMAC($_REQUEST["oemid"],$_REQUEST["mac"]);
              updateDBvalue($newid,"note", "ADD");
              if ( strpos($_REQUEST["mac"],"M") !== false){ //mobile mac
              if (setCameraLicense($_REQUEST["oemid"],$_REQUEST["mac"],"package2","")) updateDBvalue($newid,"note", "PKG-P3");
              }else{
               //if ( ($_REQUEST["oemid"]=="T04" ) or ($_REQUEST["oemid"]=="T05" ) or ($_REQUEST["oemid"]=="K01" ) )
                if ( strpos($_REQUEST["mac"],"M") !== false)   //mobile
                  if (setCameraLicense($_REQUEST["oemid"],$_REQUEST["mac"],"package2","")) updateDBvalue($newid,"note", "PKG-P3");
              else
                  if (setCameraLicense($_REQUEST["oemid"],$_REQUEST["mac"],"package0","")) updateDBvalue($newid,"note", "PKG-P1");
              //else
              //    if (setCameraLicense($_REQUEST["oemid"],$_REQUEST["mac"],"package1","")) updateDBvalue($newid,"note", "PKG-P2");
              }
                
              //if ( ($_REQUEST["oemid"]=="T04" ) or ($_REQUEST["oemid"]=="T05" ) or ($_REQUEST["oemid"]=="K01" ) )
                  if (setCameraLicense($_REQUEST["oemid"],$_REQUEST["mac"],"recycle180","")) updateDBvalue($newid,"note", "DAYS-180");
              //else
              //    if (setCameraLicense($_REQUEST["oemid"],$_REQUEST["mac"],"recycle7","")) updateDBvalue($newid,"note", "DAYS-7");
                
              if (setCameraLicense($_REQUEST["oemid"],$_REQUEST["mac"],"bind",$oldaccount)){
                updateDBvalue($newid,"note", "BIND");
                $msg_err .= "<div id=\"info\" class=\"success\">".$title.$_REQUEST["mac"]. MSG_SUCCEED. "</div>";
              }else $msg_err .= "<div id=\"info\" class=\"error\">綁定帳號".MSG_FAIL."</div>";
          }else{
            $msg_err .= "<div id=\"info\" class=\"error\">上傳授權碼".MSG_FAIL."</div>";
            if (getLicDBValue($_REQUEST["mac"],"Code")==""){
              $msg_err .= "<div id=\"info\" class=\"error\">授權碼不存在</div>";
            }else{
            //error handling after lic is uploaded
            $newid=getDBidByNEWMAC($_REQUEST["oemid"],$_REQUEST["mac"]);
            if ($newid == ""){ //has uploaded via other method
              insertDB($_REQUEST["oemid"],$_REQUEST["mac"],$oldaccount,"NEW");
              $newid=getDBidByNEWMAC($_REQUEST["oemid"],$_REQUEST["mac"]);
            }
            if ( strpos(getDBValue($newid,'note'),"BIND")===false)//not found
            {
              if ( strpos($_REQUEST["mac"],"M") !== false)   //mobile
                if (setCameraLicense($_REQUEST["oemid"],$_REQUEST["mac"],"package2","")) updateDBvalue($newid,"note", "PKG-P3");
              else
                if (setCameraLicense($_REQUEST["oemid"],$_REQUEST["mac"],"package0",""))  updateDBvalue($newid,"note", "PKG-P1");
              if (setCameraLicense($_REQUEST["oemid"],$_REQUEST["mac"],"recycle180",""))  updateDBvalue($newid,"note", "DAYS-180");
              if (setCameraLicense($_REQUEST["oemid"],$_REQUEST["mac"],"bind",$oldaccount)){
                updateDBvalue($newid,"note", "BIND");
                $msg_err .= "<div id=\"info\" class=\"success\">".$title.$_REQUEST["mac"]. MSG_SUCCEED. "</div>";
             }else $msg_err .= "<div id=\"info\" class=\"error\">".$title.$_REQUEST["mac"]. MSG_FAIL. "</div>";
            }else //bind
              $msg_err .= "<div id=\"info\" class=\"error\">[{$_REQUEST["oemid"]}]{$_REQUEST["mac"]}已綁定過</div>";
          }//getLicDBValue check
          }
      }//account not exist check
//-------------------switch
  }else	if ($_REQUEST['switchaccount']!=""){
			$taccount = checkAccountExist($_REQUEST["oemid"],$_REQUEST['switchaccount']);
			$_REQUEST["mac"] = $_REQUEST["apimac"];
			$oldaccount=getCamStatus($_REQUEST["oemid"],$_REQUEST["mac"],"getaccount");
			if ($_REQUEST["mac"]!="") $_REQUEST["mac"]=strtoupper($_REQUEST["mac"]);
			
    	if ( $taccount=="-1") //no target account
    		$msg_err .= "<div id=\"info\" class=\"error\">".MSG_FAIL. " 帳號{$_REQUEST['switchaccount']}不存在</div>";
      //no act code
      else if (getCamStatus($_REQUEST["oemid"],$_REQUEST["mac"], "getresol")=="-1")  $msg_err .= "<div id=\"info\" class=\"error\">".MSG_FAIL. "攝影機授權不存在</div>";   
      else{//taccount ok
				$title="移動到帳號 {$taccount} ";
				if ($oldaccount == $taccount)//do nothing
      			$msg_err .= "<div id=\"info\" class=\"success\">{$_REQUEST["mac"]}已經綁定在帳號{$taccount}下</div>";
      	else{
					if ($oldaccount!="-1"){
	 				//unbind
						if (setCameraLicense($_REQUEST["oemid"],$_REQUEST["mac"],"unbind","")){
								if (!insertDB($_REQUEST["oemid"],$_REQUEST["mac"],$oldaccount,"CHG")){
									$newid=getDBidByMAC($_REQUEST["oemid"],$_REQUEST["mac"],"CHG");
									updateDBvalue($newid,"note", "UNBIND");
								} 
		            $msg_err .= "<div id=\"info\" class=\"success\">{$_REQUEST["mac"]}解除綁定". MSG_SUCCEED. "</div>";
		        }else $msg_err .= "<div id=\"info\" class=\"error\">{$_REQUEST["mac"]}解除綁定".MSG_FAIL."</div>";
	        }else{//never bind but license exist
						$msg_err .= "<div id=\"info\" class=\"error\">攝影機未綁定帳號</div>";
						if (!insertDB($_REQUEST["oemid"],$_REQUEST["mac"],$oldaccount,"CHG")){
								$newid=getDBidByMAC($_REQUEST["oemid"],$_REQUEST["mac"],"CHG");
								updateDBvalue($newid,"note", "CHG");
						}
					}
					$newid=getDBidByMAC($_REQUEST["oemid"],$_REQUEST["mac"],"CHG");
	        //bind
					if (setCameraLicense($_REQUEST["oemid"],$_REQUEST["mac"],"bind",$_REQUEST['switchaccount'])){
	          updateDBvalue($newid,"note", "BIND to {$taccount}");
	          $msg_err .= "<div id=\"info\" class=\"success\">".$_REQUEST["mac"].$title. MSG_SUCCEED. "</div>";
	        }else $msg_err .= "<div id=\"info\" class=\"error\">綁定帳號".MSG_FAIL."</div>"; 
				}//account is the same?
			}//perform action
		}//switchaccount not empty

}else if (isset($_REQUEST["newmac"]) and isset($_REQUEST['btn_replacemac']) ){
  
  if ( ($_REQUEST["rmaid"]=="") ) {
      $msg_err .= "<div id=\"info\" class=\"error\">不存在攝影機, 置換".MSG_FAIL."</div>";
  }else{

    $oldaccount=getDBvalue($_REQUEST["rmaid"],"account");
    $title="置換RMA帳號 {$oldaccount}: ";
    if ($oldaccount=="-1")
      $msg_err .= "<div id=\"info\" class=\"error\">".$title. MSG_FAIL. " 帳號不存在</div>";
    else{//old account ok
        if ($_REQUEST["mac"]!="") $_REQUEST["mac"]=strtoupper($_REQUEST["mac"]); 

        if (setCameraLicense($_REQUEST["oemid"],$_REQUEST["mac"],"add","")){
            insertDB($_REQUEST["oemid"],$_REQUEST["mac"],$oldaccount,"NEW");
            $newid=getDBidByNEWMAC($_REQUEST["oemid"],$_REQUEST["mac"]);
            updateDBvalue($newid,"note", "ADD");
            if ( strpos($_REQUEST["mac"],"M") !== false)   //mobile
                if (setCameraLicense($_REQUEST["oemid"],$_REQUEST["mac"],"package2","")) updateDBvalue($newid,"note", "PKG-P3");
            else
              if (setCameraLicense($_REQUEST["oemid"],$_REQUEST["mac"],"package0","")) updateDBvalue($newid,"note", "PKG-P1");  
            if (setCameraLicense($_REQUEST["oemid"],$_REQUEST["mac"],"recycle180","")) updateDBvalue($newid,"note", "DAYS-180");
            if (setCameraLicense($_REQUEST["oemid"],$_REQUEST["mac"],"bind",$oldaccount)){
              updateDBvalue($newid,"note", "BIND");
              updateDBvalue($_REQUEST["rmaid"],"note"," with ".$_REQUEST["mac"]);
              $msg_err .= "<div id=\"info\" class=\"success\">".$title.$_REQUEST["mac"]. MSG_SUCCEED. "</div>";
            }else $msg_err .= "<div id=\"info\" class=\"error\">綁定帳號".MSG_FAIL."</div>";
        }else  $msg_err .= "<div id=\"info\" class=\"error\">上傳授權碼".MSG_FAIL."</div>";
      
    }//account not exist check

  
  }//rmaid empty
}else if (isset($_REQUEST["deletelic"]) ){
    $title="刪除授權碼: ";
    $mac=getDBvalue($_REQUEST["rmaid"],"mac");
    $oemid= getDBvalue($_REQUEST["rmaid"],"oem_id");
    if (setCameraLicense($oemid,$mac,"delete","")){
        updateDBvalue($_REQUEST["rmaid"],"camera_type", "DEL");
        $msg_err .= "<div id=\"info\" class=\"success\">".$title.$mac. MSG_SUCCEED. "</div>";
    }else $msg_err .= "<div id=\"info\" class=\"error\">".$title.$mac. MSG_FAIL. "</div>"; 
}else if (isset($_REQUEST["deleterma"]) ){
  $title="刪除ID: ";
  if (deleteDB($_REQUEST["rmaid"]))
    $msg_err .= "<div id=\"info\" class=\"success\">".$title.$_REQUEST["rmaid"]. MSG_SUCCEED. "</div>";
  else $msg_err .= "<div id=\"info\" class=\"error\">".$title.$_REQUEST["rmaid"]. MSG_FAIL. "</div>";
}

function getAdminPlayer($oemid,$type)
{
  global $siteAdminArrayInfo;
  $url="";
  foreach ($siteAdminArrayInfo as $key => $aurl)
  {
      if ($key==$oemid){
        $arrurl=explode("/html",$aurl[3]);
        $url=$arrurl[0];
        break;
      }
  }
  if ($type=="LIVEVIEW")
    return $url."/plugin/debug/online_player.php?";
  else return $url."/plugin/debug/playback_list.php?";
}

function getUID($mac)
{
  global $CamCID;
  foreach ($CamCID as $cid => $prefix)
  {
     if ( preg_match("/^".$prefix."/",strtoupper($mac)) ){
      $uid = $cid. "-".strtoupper($mac);
      break;
     }
  }
  return $uid;
}

function insertDB($oem,$mac,$account,$type)
{
  global $submiter;
  $sql ="select * from customerservice.maintain where mac='{$mac}' and oem_id='{$oem}' and camera_type='{$type}'";
  sql($sql,$result,$num,0);
  if ($num>0)    //cannot duplicate in the same camera_type
    return false;

  $mac=strtoupper($mac);
  $sql ="insert into customerservice.maintain (oem_id,mac,account,camera_type, note,submiter) values ('{$oem}','{$mac}','{$account}','{$type}','{$type}','{$submiter}' )";
    sql($sql,$result,$num,0);
    if ($result) return true;
    return false;
}

function updateDBvalue($id, $field,$value)
{
  if ($field == "note"){
    $note=getDBvalue($id,$field).";{$value}";
    $sql ="update customerservice.maintain set {$field}='{$note}' where id={$id}";
  }else{
    $sql ="update customerservice.maintain set {$field}='{$value}' where id={$id}";
  }
    sql($sql,$result,$num,0);
    if ($result) return true;
    return false;
}

function getDBidByNEWMAC($oem,$mac)
{
	 return getDBidByMAC($oem,$mac,"NEW");
}
function getDBidByMAC($oem,$mac,$camera_type)
{
    $sql ="select id from customerservice.maintain where mac='{$mac}' and camera_type='{$camera_type}' and oem_id='{$oem}'";
if (DEBUG_FLAG=="ON") echo $sql;
    sql($sql,$result,$num,0);
    if ($num>0){
      fetch($arr,$result,0,0);
      return $arr['id'];
    }else  return "";
}

function getDBvalue($id,$field)
{
    $sql ="select {$field} from customerservice.maintain where id={$id}";
    sql($sql,$result,$num,0);
    if ($result){
      fetch($arr,$result,0,0);
      return $arr[$field];
    }
    return "";
}
function deleteDB($id)
{
    $sql ="delete from customerservice.maintain where id={$id}";
    sql($sql,$result,$num,0);
    if ($result) return true;
    return false;
}

function getLicDBValue($mac,$field)
{
  $sql = "select {$field} from licservice.qlicense where mac='{$mac}'";
  sql($sql,$result,$num,0);
  if ($result){
    fetch($arr,$result,0,0);
    return $arr[$field];
  }
  return "";
}

function checkAccountExist($oem,$name)
{//return name or -1
  global $siteAdminArrayInfo,$cmdArray;
  if ($name!=""){
    $url = $siteAdminArrayInfo[$oem][3].$cmdArray['get_date']."&name={$name}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);//ignore invalid SSL
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    $result = curl_exec($ch);
    $result = str_replace("\n", '', $result); // remove new lines
    curl_close($ch);
if (DEBUG_FLAG=="ON") {
  echo $url;
  var_dump($result);
}
    if (strpos($result,"Fail")!==false) return "-1";
    if ($result=="") return "-1"; //old api
    return $name;
  }
  return "-1";
}

function getCamStatus($oem,$mac,$cmd)
{//return value or -1
  global $siteAdminArrayInfo,$cmdArray;
  if ($mac!=""){
    $url = $siteAdminArrayInfo[$oem][3].$cmdArray[$cmd]."&mac={$mac}";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);//ignore invalid SSL
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    $result = curl_exec($ch);
    $result = str_replace("\n", '', $result); // remove new lines
    curl_close($ch);
if (DEBUG_FLAG=="ON") {
  echo $url;
  var_dump($result);
}
    if (strpos($result,"Fail")!==false) return "-1";
    return $result;
  }
  return "-1";
}
function setCameraLicense($oem,$mac,$cmd,$param)
{
  global $siteAdminArrayInfo, $cmdArray;
//check command parameter
  if ($cmd=="delete") {
    $ac=getLicDBValue($mac,"Code");
    if ($ac!="")
      $url = $siteAdminArrayInfo[$oem][3].$cmdArray[$cmd]."&mac={$mac}&ac={$ac}";
  }else if ($cmd=="add"){
    $ac=getLicDBValue($mac,"Code");
    $cid=getLicDBValue($mac,"CID");
    if ($ac!="")
      $url = $siteAdminArrayInfo[$oem][3].$cmdArray[$cmd]."&mac={$mac}&ac={$ac}&cid={$cid}";
    else return false;
  }else if ($cmd=="unbind"){
      $ac=getLicDBValue($mac,"Code");
      //old account from replaced mac
      $url = $siteAdminArrayInfo[$oem][3].$cmdArray[$cmd]."&mac={$mac}&ac={$ac}";
  }else if ($cmd=="bind"){
      $ac=getLicDBValue($mac,"Code");
      //old account from replaced mac
      $url = $siteAdminArrayInfo[$oem][3].$cmdArray[$cmd]."&mac={$mac}&ac={$ac}&user={$param}";
  }else if ( (strpos($cmd,"recycle")!== false) or (strpos($cmd,"package")!== false) ){
    $url = $siteAdminArrayInfo[$oem][3].$cmdArray[$cmd]."&mac={$mac}";
  }else{
    return false;
  }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);//ignore invalid SSL
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    $result = curl_exec($ch);
    $result = str_replace("\n", '', $result); // remove new lines
    curl_close($ch);
if (DEBUG_FLAG == "ON"){ 
  echo $url;
  var_dump($result);
}
    //if ($result =="Success") return true;
    if (strpos($result, 'Success') !== false) return true;     

  return false;  
}

function getRMAList($andparam)
{
  $sql = "select * from customerservice.maintain where camera_type='RMA' and note = 'RMA' {$andparam}";
  sql($sql,$result,$num,0);
  $html="<option value=''>(選擇可置換的MAC)</option>";
  for($i=0;$i<$num;$i++){
    fetch($arr,$result,$i,0);
    if (strpos($andparam,$arr['oem_id']) !== false)
      $html.="<option value='".$arr['id']."' selected>".$arr['oem_id']." :".$arr['mac']."</option>\n";
    else
    $html.="<option value='".$arr['id']."'>".$arr['oem_id']." :".$arr['mac']."</option>\n";
  }
  echo $html;
}
function getSiteList($oemid)
{
  global $siteAdminName;
  foreach($siteAdminName as $key=>$name){
    if (!isset($_REQUEST["debugadmin"]) and (($key=="SYS") ) ) continue;
    if ($oemid==$key)
      $html.="<option value='".$key."' selected>[{$key}] {$name}</option>\n";
    else
    $html.="<option value='".$key."'>[{$key}] {$name}</option>\n";
  }
  echo $html;
}

function getWebCamURLList($type)
{
  global $siteAdminArrayInfo,$siteAdminName;
  foreach ($siteAdminArrayInfo as $key => $aurl)
  {
    if ($siteAdminName[$key]==NULL) continue;
    $wurl = "https://".$aurl[4];//explode(":8080/html",$aurl); //https://kreac.kcg.gov.tw
    $purl = explode("/html/api",$aurl[3]);  //https://kreac.kcg.gov.tw:8080/
    $pwd= $aurl[1];//$RPICAPP_USER_PWD[$key];
    //if ($pwd==NULL) $pwd=$RPICAPP_USER_PWD['RPIC'];
    if ($type=="admin_camera_video")
      $theurl= $purl[0].$aurl[8];
      /*if ($key=="T04")
        $theurl= $purl[0]."/plugin/taipei/playback_list.php?adminuser=tperpic&adminpwd=Ea9M7gOu586UQaOtXJ3e6f51";
      else if ($key=="T05")
        $theurl= $purl[0]."/plugin/ty/playback_list.php?adminuser=tyrpic&adminpwd=U2QUU4EHHRTQp6rynmz4";
      else if ($key=="K01")
        $theurl= $purl[0]."/plugin/rpic/playback_list.php?adminuser=kcgrpic&adminpwd=x6h8YzgKpBunCMDWYL9u";
      //else if ($key=="X02")
      //  $theurl= $purl[0]."/plugin/rpic/playback_list.php?";
      else $theurl= $purl[0];
      */
    else if ($type=="camera_setting")
        $theurl= $wurl[0].$aurl[7].$pwd;//"/camera_setting.php?adminpwd={$pwd}";
    else if ($type=="web_url")
        $theurl= $wurl[0]."/";
    else if ($type=="admin_url")
        $theurl= $purl[0]."/";
    else if ($type=="admin_api")
        $theurl= $aurl;
    if ($oemid==$key)
      $html.="<option value='".$theurl."' selected>[{$key}] {$siteAdminName[$key]}</option>\n";
    else
    $html.="<option value='".$theurl."'>[{$key}] {$siteAdminName[$key]}</option>\n";
  }
  echo $html;
}

function createMaintainTable($limit, $whereParam)
{
   $sql = "select * from customerservice.maintain {$whereParam} order by id desc";
    sql($sql,$result,$num,0);
    $services = array();
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      $services[$i] = $arr;
    }//for
  if ($limit != 0){
  $sqlc = "select count(*) as total from customerservice.maintain";
  sql($sqlc,$resultc,$numc,0);
  fetch($arrc,$resultc,0,0);
  //$html .="Total ".$arrc['total']."<br>";
  $html .= "\n<table id='tbl5' class=table_main><tr class=topic_main><td>ID ({$num}/{$arrc['total']})</td>";
  }else
  $html .= "\n<table id='tbl5' class=table_main><tr class=topic_main><td>ID ({$num})</td>";
  //$html .= "<td>OEM</td><td>MAC</td><td>Owner</td><td>Type</td><td>Note</td><td>Date</td><td>Submitter</td><td></td></tr>"; //add table header
    //<div class='tooltip'>???<span class='tooltiptext'>ttt</span></div>
  global $siteAdminName,$infoArray; 
  $html .= "<td><div class='tooltip'>OEM<span class='tooltiptext'>".printArray($siteAdminName,'')."</span></div></td><td>MAC</td><td><div class='tooltip'>Owner<span class='tooltiptext'>所屬帳號</span></div></td><td><div class='tooltip'>Type<span class='tooltiptext'>".printArray($infoArray['type'],'tooltip')."</span></div></td><td><div class='tooltip'>Note<span class='tooltiptext'>".printArray($infoArray['note'],'tooltip')."</span></div></td>"; //add table header
  if (!isMobile())
    $html .= "<td>Date</td><td>Submitter</td><td></td>"; //add table header
  $html .="</tr>";
  $i = 0;//for limit check
  foreach($services as $service)
  {
    if (!isset($_REQUEST["debugadmin"]) and (($service['oem_id']=="SYS") ) ) continue;
    $html.= "\n<tr class=tr_2>\n";
    $html.= "<td>{$service['id']}</td>\n";
    $html.= "<td>{$service['oem_id']}</td>\n";
    //if (( ($service['oem_id']=="T04") or ($service['oem_id']=="T05") or ($service['oem_id']=="K01")) and ($service['camera_type']=="NEW") )
    
    if ( ($service['camera_type']=="NEW") or  ($service['camera_type']=="RMA") )
      //$html.= "<td><a href=\"javascript: window.open('".getAdminPlayer($service['oem_id'],"LIVEVIEW")."user={$service['account']}&uid=".getUID($service['mac'])."&debugadmin','',config='height=450,width=500');\">{$service['mac']}</a></td>";
      $html.= "<td><a href=\"javascript: window.open('".getAdminPlayer($service['oem_id'],"PLAYBACK")."user={$service['account']}&mac=".$service['mac']."&debugadmin','',config='height=450,width=500');\">{$service['mac']}</a></td>";
    else if ( ($service['camera_type']=="CHG") ){ //different user
      $tmpaccount = trim(end(explode( "BIND to ",$service['note']))," ");
      $html.= "<td><a href=\"javascript: window.open('".getAdminPlayer($service['oem_id'],"PLAYBACK")."user={$tmpaccount}&mac=".$service['mac']."&debugadmin','',config='height=450,width=500');\">{$service['mac']}</a></td>";
    }else $html.= "<td>{$service['mac']}</td>\n";
    
    $html.= "<td>{$service['account']}</td>\n";
    $html.= "<td>{$service['camera_type']}</td>\n";
    $html.= "<td>{$service['note']}</td>\n";
  if (!isMobile()){
    $html.= "<td>{$service['update_date']}</td>\n";
    $html.= "<td><small>{$service['submiter']}</small></td>\n";
    $html.= "<td>\n";
    if ( ($service['camera_type']=="RMA") ){
      if ( ($service['note'] !="RMA") ){   
      $html.= "<form action=\"".$_SERVER['PHP_SELF']."\" method=POST>\n";
      $html.= "<input type=submit name='btn_delete_lic' value=\"移除授權碼\" class=\"btn_2\">\n";
      $html.= "<input type=hidden name='deletelic'>\n";
      $html.= "<input type=hidden name='rmaid' value=\"{$service['id']}\" >\n";
      $html.= "</form>\n";
      }
    }
    if (isset($_REQUEST["debugadmin"])){
      if ( ($service['camera_type'] !="DEL") ){   
      $html.= "<form action=\"".$_SERVER['PHP_SELF']."\" method=POST>\n";
      $html.= "<input type=submit name='btn_delete_lic' value=\"D移除授權碼\" class=\"btn_2\">\n";
      $html.= "<input type=hidden name='deletelic'>\n";
      $html.= "<input type=hidden name='rmaid' value=\"{$service['id']}\" >\n";
      $html.= "<input type=hidden name=debugadmin value='1'>";
      $html.= "</form>\n";
      }
 
      $html.= "<form action=\"".$_SERVER['PHP_SELF']."\" method=POST>\n";
      $html.= "<input type=submit name='btn_delete_rma' value=\"D移除\" class=\"btn_2\">\n";
      $html.= "<input type=hidden name='deleterma'>\n";
      $html.= "<input type=hidden name='rmaid' value=\"{$service['id']}\" >\n";
      $html.= "<input type=hidden name=debugadmin value='1'>";
      $html.= "</form>\n";
    }
    $html.= "</td>\n";
  }//isMobile
    $html.= "</tr>\n";
    $i++;
    if (($limit!=0) and ($i > $limit)) break;
  }
  $html .= "</table>\n";   //add table end
  echo $html;
}

function getSiteName($oem)
{
  global $siteAdminName;
  foreach($siteAdminName as $key=>$name){
    if ($key == $oem) return $name;
  }
  return "";
}
function getLicNumber($oem)
{
/*    global $siteLicNumber;
    foreach($siteLicNumber as $key=>$count){
        if ($key == $oem) return $count;
    }
*/
    global $siteAdminArrayInfo; 
    foreach($siteAdminArrayInfo as $key=>$count){
        if ($key == $oem) return $count[2];
    }
    return -1;
}
function listCount()
{ //total - oem_id(NEW + CHG)???? + DEL
  global $siteAdminName;
  $total = array();
  foreach ($siteAdminName as $mkey => $mvalue){
    $total[$mkey]= intval(getLicNumber($mkey));
    /*$sql = "select camera_type, count(*) as count from customerservice.maintain where oem_id='{$mkey}' and camera_type ='NEW'  group by mac;";
    sql($sql,$result,$numNew,0);
    $sql = "select camera_type, count(*) as count from customerservice.maintain where oem_id='{$mkey}' and camera_type ='CHG'  group by mac;";
    sql($sql,$result,$numCHG,0);
    $total[$mkey]-=$numNew;
    if (($total[$mkey] - $numCHG) > 0) $total[$mkey]-=$numCHG; 
    $sql = "select camera_type, count(*) as count from customerservice.maintain where oem_id='{$mkey}' and camera_type ='DEL'  group by mac;";
    sql($sql,$result,$num,0);
    $total[$mkey]+=$num;*/
  }
  //$totalX01= intval(getLicNumber("X01"));  $totalX02= intval(getLicNumber("X02"));  $totalT04= intval(getLicNumber("T04"));  $totalT05= intval(getLicNumber("T05"));

    $sql = "select oem_id,camera_type,count(*) as count from customerservice.maintain group by camera_type,oem_id";

    sql($sql,$result,$num,0);
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      //foreach ($siteAdminName as $mkey => $mvalue){
      foreach ($total as $mkey => $mvalue){
        if ($arr['oem_id'] == $mkey){
            if ($arr['camera_type'] == "NEW")
                $total[$mkey]-=intval($arr['count']);
            else if ($arr['camera_type'] == "DEL")
                $total[$mkey]+=intval($arr['count']);
            break; //once match continue to next sql
        }
      }//foreach
    }

  $html="<b><div style='color:red;'>";
  $i=0;
  //foreach ($siteAdminName as $mkey => $mvalue){
  foreach ($total as $mkey => $mvalue){
    if (($i%2) ==0) $html.="<br>";
    $html .= "[{$mkey}]".getSiteName($mkey)."站授權碼剩餘:{$total[$mkey]}/".getLicNumber($mkey).";&nbsp;&nbsp;";
    $i++;
  }
  $html .= "<br></div></b>";
  echo $html;
}

function printSearchOption($target, $type="")
{
  global $siteAdminName,$infoArray;
  $html="";
  if ($type=="TYPE"){
    foreach ($infoArray['type'] as $key=>$data){
      if ($target == "Type{$key}" )  $html.="<option value=\"Type{$key}\" selected>({$data})</option>\n";
      else  $html.="<option value=\"Type{$key}\">({$data})</option>\n";
    }  
  }else{  //($type=="OEM")
    foreach ($siteAdminName as $key=>$data){
      if ($target == $key )  $html.="<option value=\"{$key}\" selected>({$key})</option>\n";
      else  $html.="<option value=\"{$key}\">({$key})</option>\n";
    }
  }
  echo $html;
}
?>
<html>
<head>
<title>道管維修</title>
<!-- To support ios sizes -->
<link rel="apple-touch-icon" href="image/mt.png">
<!--link rel="apple-touch-icon" sizes="57x57" href="image/mt57.png">
<link rel="apple-touch-icon" sizes="72x72" href="image/mt72.png">
<link rel="apple-touch-icon" sizes="114x114" href="image/mt114.png">
<link rel="apple-touch-icon" sizes="144x144" href="image/mt144.png">
<link rel="apple-touch-icon" sizes="60×60" href="image/mt60.png">
<link rel="apple-touch-icon" sizes="76×76" href="image/mt76.png">
<link rel="apple-touch-icon" sizes="120×120" href="image/mt120.png">
<link rel="apple-touch-icon" sizes="152×152" href="image/mt152.png">
<link rel="apple-touch-icon" sizes="180×180" href="image/mt180.png"-->
<!-- To support android sizes -->
<link rel="icon" sizes="192×192" href="image/mt192.png">
<link rel="icon" sizes="128×128" href="image/mt128.png">
<?php
if (isMobile()){ //detect user agent
  printMobileMeta();
echo "<style type=\"text/css\">\n";
echo "html *\n{font-size: 1em !important;\n}\n";
echo "h1{\nfont-size: 2em !important;\n}\n";
echo "h2{\nfont-size: 1.5em !important;\n}\n";
echo ".tooltip .tooltiptext{\nfont-size: 0.8em !important;\n}\n";
echo "#container {\n width: 100% !important;}\n"; //overwrite
echo ".table_main{\n width: 100% !important;}\n"; //overwrite
echo "</style>\n";
}
?>
<style>
#loginform
{
 width:300px;
 height:200px;
 margin-top:30px;
 background-color:#585858;
 border-radius:3px;
 box-shadow:0px 0px 10px 0px #424242;
 padding:10px;
 box-sizing:border-box;
 font-family:helvetica;
 visibility:hidden;
 display:none;
 font-size:20px;
}
#loginform p
{
 margin-top:15px;
 font-size:22px;
 color:#E6E6E6;
}
#loginform #dologin
{
 margin-left:5px;
 margin-top:10px;
 width:80px;
 height:40px;
 border:none;
 border-radius:3px;
 color:#E6E6E6;
 background-color:grey;
 font-size:20px;
}
/* Tooltip container */
.tooltip {
    position: relative;
    display: inline-block;
    border-bottom: 1px dotted black; /* If you want dots under the hoverable text */
}
/* Tooltip text */
.tooltip .tooltiptext {
    visibility: hidden;
    width: 150px;
    background-color: black;
    color: #fff;
    text-align: center;
    padding: 5px 0;
    border-radius: 6px;
    position: absolute;
    z-index: 1;
    /* Position the tooltip text - see examples below! */
    top: -25px;
    left: 10%; 
}

/* Show the tooltip text when you mouse over the tooltip container */
.tooltip:hover .tooltiptext {
    visibility: visible;
}
/*form.css*/
.table_main{width:780px; border:0px; border-collapse:collapse; cellspacing:0; cellpadding:0}
.topic_main{height:25px; background-color:#0069C9; font:bold 14px arial; color:#FFF; text-align:center; border:1px solid #fff}
.topic_main td{border:1px solid #fff}
.tr_2{background-color:#EBF0FF; height:25px}
/*rewrite, remove image*/
.btn_1{background-color:#0069C9; background-repeat:no-repeat; width:200px; height:26px; text-align:center; 
     font: 14px arial; color:#FFF; text-shadow:0px -1px 0px #0069C9 ;margin-top:30px; padding-top:5px; display:inline-block; cursor:pointer }
.btn_2{background-color:#0069C9; background-repeat:no-repeat; width:100px; height:26px; text-align:center; 
     font: 14px arial; color:#FFF; text-shadow:0px -1px 0px #0069C9 ;margin-top:20px; padding-top:5px; display:inline-block; cursor:pointer}

</style>
<script type="text/javascript" src="/plugin/user_log/js/jquery-1.11.1.min.js"></script>
<script type="text/javascript">
var x41site = [
<?php
$tmp = "";
for ($i=0;$i<sizeof($jsSITE);$i++){
  $tmp.= "[";
  for ($j=0;$j<sizeof($jsSITE[$i]);$j++)
      $tmp.= "\"".$jsSITE[$i][$j]."\",";
  $tmp= rtrim( $tmp,",");
  $tmp.= "],\n";
}
echo rtrim( $tmp,","); 
?>
];

$(document).ready(function()
{
<?php
  if ($submiter=="") 
    echo "showpopup();\n";
?>
 $("#show_login").click(function(){
  showpopup();
 });
 $("#close_login").click(function(){
  hidepopup();
 });
 $('#myMessage').show();
});

function showpopup()
{
 $("#loginform").fadeIn();
 $("#loginform").css({"visibility":"visible","display":"block"});
}

function hidepopup()
{
 $("#loginform").fadeOut();
 $("#loginform").css({"visibility":"hidden","display":"none"});
}
function optionValue(thisformobj, selectobj)
{
  var chosenoption=selectobj.options[selectobj.selectedIndex];
  thisformobj.value = chosenoption.value;
}
function genMACQRPage(mac)
{
    var cmd = "/plugin/licservice/listLicense_camQR.php?data="+mac.toUpperCase()+"&filename="+mac;
    window.open(cmd, 'Camera MAC QR', config='height=300,width=250');
}
function printSiteValue()
{
  var preOEMID="";
  <?php
    if (isset($_REQUEST['oemid'])) echo "preOEMID='{$_REQUEST['oemid']}';";
  ?>
  for (var i = 0; i < x41site.length; i++) {
    if (preOEMID == x41site[i][0])
      document.write("<option value='"+x41site[i][0]+"' selected>["+x41site[i][0]+"] "+x41site[i][1]+"</option>");
    else
     document.write("<option value='"+x41site[i][0]+"'>["+x41site[i][0]+"] "+x41site[i][1]+"</option>");
  }
}
function getPluginURL(type, oemid, urlprefix)
{
    for (var i = 0; i < x41site.length; i++) {
      if (oemid == x41site[i][0]){
        if (type == "camera_setting")
          if (x41site[i][5]) 
            return urlprefix+x41site[i][2]+x41site[i][5];
          else return "";
        else if (type == "playback_list")
          if (x41site[i][6])
            return urlprefix+x41site[i][2]+":8080"+x41site[i][6];
          else return "";
          
        else if (type == "manage_share")
          if (x41site[i][4])
            return urlprefix+x41site[i][4]+"@"+x41site[i][2]+"/manage/manage_share.php?command=";
          else return "";
        else if (type == "selectoption")
          return urlprefix+x41site[i][2]+"/";
        else if (type == "selectoptionpwd"){
          if (x41site[i][4]){
            return urlprefix+x41site[i][4]+"@"+x41site[i][2]+"/";
          }else return "";          
        }else if (type == "adminURL")
          return urlprefix+x41site[i][2]+":8080";
        else if (type == "device_status"){
          var baseurl = urlprefix+x41site[i][2]+":8080"+"/plugin/";
          var keyArr = x41site[i][5].split("=");
          var key = keyArr[1];
          if (oemid=="T04")
            return baseurl +"taipei/device_status.php?key="+key;
          else if (oemid=="T05")
            return baseurl +"ty/device_status.php?key="+key;
          else return baseurl +"rpic/device_status.php?key="+key;
        }
      }else continue;       
    } 
}
function printWebAPInew(type, urlprefix,urlpostfix)
{
  for (var i = 0; i < x41site.length; i++) {
    if (type == "camera_setting"){
      if (x41site[i][5]){
          document.write("<option value='"+urlprefix+x41site[i][2]+x41site[i][5]+"'>["+x41site[i][0]+"] "+x41site[i][1]+"</option>");
      }
    }else if (type == "playback_list"){
      if (x41site[i][6])
          document.write("<option value='"+urlprefix+x41site[i][2]+urlpostfix+x41site[i][6]+"'>["+x41site[i][0]+"] "+x41site[i][1]+"</option>");
    }else if (type == "manage_share"){
      if (x41site[i][4])
        document.write("<option value='"+urlprefix+x41site[i][4]+"@"+x41site[i][2]+urlpostfix+"'>"+x41site[i][0]+" "+x41site[i][1]+"</option>");
    }else if (type=="selectoption"){
        document.write("<option value='"+urlprefix+x41site[i][2]+urlpostfix+"'>"+x41site[i][0]+" "+x41site[i][1]+"</option>");
    }else if (type=="selectoptionpwd")
      if (x41site[i][4])
        document.write("<option value='"+urlprefix+x41site[i][4]+"@"+x41site[i][2]+"'>["+x41site[i][0]+"] "+x41site[i][1]+"</option>");
  }
  //alert(type);
}
function hideMessage(){
  $('#myMessage').hide();
  $('#customMessage').hide();
}
function showCustomMessage(className,msg){
  var obj = $('#customMessage');
  obj.attr("class", className);
  obj.html(msg);
  obj.show();
}

function isValid(tag,type){
  hideMessage();
  $("#"+tag ).css({'background-color' : 'white'});
  var myvalue = $('#'+tag).val();
  
  if (isEmpty(myvalue)) {
    setNotice(tag);
    showCustomMessage('error','欄位不可空白.');
    return false;
  }
  if (type=="MAC"){
    if (myvalue.length!=12){
      setNotice(tag);
      showCustomMessage('error','長度需為12碼.');
      return false;
    }
  }else if (type=="NAME"){
  	if ( (myvalue.length<4) || (myvalue.length>32)){
      setNotice(tag);
      showCustomMessage('error','長度需為4-32碼.');
      return false;
		}
  }
  return true;
}
function setNotice(tag){
    $("#"+tag).focus();
    $("#"+tag).css({'background-color' : 'red'});
}
function isEmpty(inputStr) { 
  if ( null == inputStr || "" == inputStr ) {
    return true; 
  } return false; 
}
</script>
<link rel=stylesheet type="text/css" href="../user_log/js/style.css">
</head>
<body>
<div class="container">
  <div style='height:90px;'>
    <div style='margin-top:20px; float:left;'><a href="<?php echo $home_url;?>"><img src="/images/logo_qlync.png"/></a></div>
    <div style='margin:55px 0 0 30px; float:left;'>
        <span style='color:#999999; font:14px arial; margin-left:0px;'></span>
            <span style='color:#999999; font:14px arial; margin-left:0px;'></span>
            <span style='color:#999999; font:14px arial; margin-left:0px;'></span> 
    </div>
        <div align="right">
<?php
if($_SESSION["ID_qlync"] !=""){
  echo "<a href=\"javascript:var wobj=window.open('/html/member/logout.php?step=logout','print_popup','width=300,height=300');setTimeout(function(){location=location;},500);setTimeout(function() { wobj.close(); }, 500);\">登出</a>\n";
}
?>
        </div> 
  </div>
</div>
<hr>
<div class="container">
<div align=center><small><?php echo $submiter;?></small><h2 style='display:inline'>道管攝影機維護</h2>
<div align=right><font size=1>
</font></div>
</div>
<center>  
 <div id = "loginform">
  <form method = "post" action = "/html/member/login.php" target="print_popup" onsubmit="var wobj=window.open('about:blank','print_popup','width=300,height=300');setTimeout(function(){window.location.reload();},500);setTimeout(function() { wobj.close(); }, 500);">
  <p><?php if ($_SESSION["login_err"] > 0) echo "<font color=red>登入驗證失敗 {$_SESSION["login_err"]}</font>"; else echo "維護請登入";?></p>
   <p><input type = "text" id = "email" name = "email"  placeholder = "帳號"></p>
   <input type = "password" id = "password" name = "password" placeholder = "密碼" AUTOCOMPLETE="OFF"><br>
   <input type = "hidden" name="step" value = "login">
   <input type = "submit" id = "dologin" value = "登入">
  </form>
 </div>
</center>
<div style="display: none" id="customMessage"></div>
<div style="display: none" id="myMessage">
<?php
if (isset($msg_err))
echo $msg_err;
?>
</div>
<?php
if ($submiter=="") exit();
//else echo "{$submiter}您好:\n";
?>
<table><tr><td width='30%'>
<form name=camera_setting action="<?php echo $_SERVER['PHP_SELF'];?>" method=post>
<select name=oemid>
<script>
printSiteValue();
</script>
</select>
<input name=apimac id=apimac placeholder="攝影機MAC 12碼" value="<?php if (isset($_REQUEST['mac'])) echo $_REQUEST['mac'];?>" size='10'>
&nbsp;
</td>
<td>
<input type=button name="btn_camera_setting" value="設定攝影機NTP(Java)"  onclick="javascript: if (isValid('apimac','MAC')) window.open(getPluginURL('camera_setting',this.form.oemid.value,'https://')+'&mac='+this.form.apimac.value.toUpperCase());"><br>
<input type=button name="btn_admin_camera_video" value="後台管理確認錄影檔"  onclick="javascript: if (isValid('apimac','MAC')) window.open(getPluginURL('playback_list',this.form.oemid.value,'https://')+'&mac='+this.form.apimac.value.toUpperCase(),'ivideo','height=500,width=500,location=yes,toolbar=yes,scrollbars=yes');"><br>
<input type=button name="btn_admin_camera_qr" value="MAC QR"  onclick="javascript: if (isValid('apimac','MAC')) genMACQRPage(this.form.apimac.value);">
<br>
<input type=button onclick="javascript: if (isValid('apimac','MAC')) window.open(getPluginURL('adminURL',this.form.oemid.value,'https://')+'/html/api/getinfo_camera.php?id=root&pwd=1qazxdr5&mode=SQL&type=camera_status&mac='+this.form.apimac.value,'imain','height=300,width=300,location=yes,toolbar=yes,scrollbars=yes');" value="攝影機錄影設定">
<br>
<input type=button onclick="javascript: if (isValid('apimac','MAC')) window.open(getPluginURL('adminURL',this.form.oemid.value,'https://')+'/html/api/getinfo_camera.php?id=root&pwd=1qazxdr5&mode=SQL&type=online_status&mac='+this.form.apimac.value,'imain','height=300,width=300,location=yes,toolbar=yes,scrollbars=yes');" value="攝影機線上狀態">
<br>
<!--
<input type=button name="btn_camera_share" value="分享攝影機To"  onclick="javascript: if (isValid('apimac','MAC')) window.open(getPluginURL('manage_share',this.form.oemid.value,'http://')+'share_camera&mac='+this.form.apimac.value.toUpperCase()+'&user_name='+this.form.user.value,'ivideo','height=300,width=300,location=yes,toolbar=yes,scrollbars=yes');">&nbsp;
<input name=user size=12 placeholder="分享帳號" value="IVD12345">&nbsp;
<input type=button name="btn_camera_share" value="From刪除分享攝影機"  onclick="javascript: if (isValid('apimac','MAC')) window.open(getPluginURL('manage_share',this.form.oemid.value,'http://')+'unshare_camera&mac='+this.form.apimac.value.toUpperCase()+'&user_name='+this.form.user.value,'ivideo','height=300,width=300,location=yes,toolbar=yes,scrollbars=yes');">&nbsp;
-->
<hr>
<small><font color=black>
Name/Email:<input type=text id=addname name=addname placeholder='name(4-32)' size=5>@
<input type=text name=addemailtail placeholder='email host' value="safecity.com.tw" style="background-color:grey;"  size=5>
<br>
password:<input type=text name=addpwd placeholder='passwd' value="1qaz2wsx" style="background-color:grey;"  size=5>
<input type=button onclick="javascript: if (isValid('addname','NAME')) window.open(getPluginURL('adminURL',this.form.oemid.value,'https://')+'/html/api/mgmt_enduser.php?id=root&pwd=1qazxdr5&command=add&email='+this.form.addname.value+'@'+this.form.addemailtail.value+'&name='+this.form.addname.value+'&password='+this.form.addpwd.value,'imain','height=300,width=300,location=yes,toolbar=yes,scrollbars=yes');" value="新增帳號">
</small></font>

<hr>
<input type=button name='btn_newmac' value='上傳新攝影機並綁定至'  class="btn_1" onclick="javascript: if (isValid('apimac','MAC') && isValid('newaccount','NAME')) {this.form.switchaccount.value='';this.form.submit();}">
<input name=newaccount id=newaccount size=8  value='<?php echo $_REQUEST['newaccount'];?>' placeholder='帳號'><br>
<input type=button onclick="javascript: window.open(getPluginURL('adminURL',this.form.oemid.value,'https://')+'/html/api/unregister_camera.php?id=root&pwd=1qazxdr5&command=list','imain','height=300,width=450,location=yes,toolbar=yes,scrollbars=yes');" value="未註冊攝影機清單">&nbsp;
<input type=button onclick="javascript: if (isValid('apimac','MAC')) window.open(getPluginURL('adminURL',this.form.oemid.value,'https://')+'/html/api/unregister_camera.php?id=root&pwd=1qazxdr5&command=clean&'+'&mac='+this.form.apimac.value,'imain','height=300,width=300,location=yes,toolbar=yes,scrollbars=yes');" value="未註冊攝影機MAC清除">
<hr>

<input type=button name='btn_switchmac' value='切換攝影機至'  class="btn_1" onclick="javascript: if (isValid('apimac','MAC') && isValid('switchaccount','NAME')) {this.form.newaccount.value='';this.form.submit();}">
<input name=switchaccount id=switchaccount size=8  value='<?php echo $_REQUEST['switchaccount'];?>' placeholder='帳號'>
<input type=hidden name=btn_newmac id=btn_newmac>
</form>
</td></tr></table>
<br>
<ul>
<form name="device_status">
<select name=oemid>
<script>
printSiteValue();
</script>
</select>
<input type=button onclick="javascript: window.open(getPluginURL('device_status',this.form.oemid.value,'https://'),'imain','height=300,width=600,location=yes,toolbar=yes,scrollbars=yes');" value="攝影機使用狀態報告">
</ul>
</form>
<ol><li type="1">
<form name=rma action="<?php echo $_SERVER['PHP_SELF'];?>" method=post>
<select name=oemid>
<?php
getSiteList($_REQUEST['oemid']);
?>
</select>
<input name=mac size=12  placeholder="要退還MAC 12碼"  value="<?php if (isset($_REQUEST['mac'])) echo $_REQUEST['mac'];?>">
<input type=hidden name="rma">
<input type=submit name="btn_rma" value="退還" class="btn_2">
</li>
<?php
if (isset($_REQUEST["debugadmin"]))
  echo "<input type=hidden name=debugadmin value='1'>";
?>
</form>
<li type="1">
<form name=newmac action="<?php echo $_SERVER['PHP_SELF'];?>" method=post>
<select name=oemid  onchange="optionValue(this.form.oemid, this);this.form.submit();">
<?php
  getSiteList($_REQUEST['oemid']);
?>
</select>
<input name=mac size=12 placeholder="新攝影機MAC 12碼" value="<?php if (isset($_REQUEST['mac'])) echo $_REQUEST['mac'];?>">
<input type=hidden name="newmac">
<select name=rmaid>
<?php
  if (isset($_REQUEST['oemid']) )
    getRMAList("and oem_id='".$_REQUEST['oemid']."'");
  else getRMAList("and oem_id='X01'");
?>
</select>
<input type=submit name="btn_replacemac" value="置換"  class="btn_2">
</li>
</form>
</ol>
<?php
  listCount();
if (($_REQUEST['uid_filter'] =="(MORE)" ) or ($_REQUEST['uid_filter'] =="(MORE)" ))
  $_REQUEST['found_mac'] = "";
?>
<form name=filter action="<?php echo $_SERVER['PHP_SELF'];?>" method=post>
<select name="uid_filter" id="uid_filter" onchange="optionValue(this.form.uid_filter, this);this.form.submit();">
<option value="(SEARCH/FOLD)"<?php if($_REQUEST['found_mac'] !="" ) echo "selected";?>>(SEARCH/FOLD)</option>
<?php
printSearchOption($_REQUEST['uid_filter'],"TYPE");
printSearchOption($_REQUEST['uid_filter'],"OEM");
?>
<option value="(MORE)" <?php if($_REQUEST['uid_filter'] =="(MORE)" ) echo "selected";?>>(MORE)</option>
<option value="(ALL)" <?php if($_REQUEST['uid_filter'] =="(ALL)" ) echo "selected";?>>(ALL)</option>
</select>
<input type=text name=found_mac size=8 value='<?php if( isset($_REQUEST['found_mac'])) echo $_REQUEST['found_mac'];?>' placeholder='尋找MAC'>
<?php
if (isset($_REQUEST["debugadmin"]))
  echo "<input type=hidden name=debugadmin value='1'>";
?>
</form>
<?php
if ( $_REQUEST['found_mac'] != "" ){
  createMaintainTable(0," where mac like '%".$_REQUEST['found_mac']."%'");
}else if (preg_match("/^Type[A-Z]{3}$/",$_REQUEST['uid_filter'])){
  createMaintainTable(20," where camera_type='".substr($_REQUEST['uid_filter'],-3)."'");
}else if (preg_match("/^[A-Z]{1}[0-9]{2}$/",$_REQUEST['uid_filter'])){
  createMaintainTable(0," where oem_id='{$_REQUEST['uid_filter']}'");
}else if($_REQUEST['uid_filter'] =="(ALL)" ){
  createMaintainTable(0,"");
}else if($_REQUEST['uid_filter'] =="(MORE)" ){
  createMaintainTable(20,"");
}else
  createMaintainTable(10,"");
?>
</div>
</body>
</html>