<?php
/****************
 *Validated on Jul-5,2017,
 * reference from showPipeUnit.php
 * Required qlync mysql connection config.php
 * option param: DEMO for db insert
 * option param: debugadmin for delete         
 *Writer: JinHo, Chang
*****************/

include("/var/www/qlync_admin/header.php");
include("/var/www/qlync_admin/menu.php"); //modify menu to menuscid002??
require_once ("share.inc");
require_once ("rpic.inc");
header("Content-Type:text/html; charset=utf-8");
define("DEFAULT_APPMODE","租賃影像");
define("DEFAULT_SHARE_PWD","1qaz2wsx");
define("LIMIT_SHARE_NUMBER",20);
define("DEBUG_FLAG","OFF"); //OFF ON
define("DEFAULT_IS_INSTALLER","0"); //always vendor info 0

if (DEBUG_FLAG == "ON") var_dump($_REQUEST);

$installuser = "";
if (chkSourceFromMegasys($installuser)){//company ip
	if (DEBUG_FLAG == "ON") echo "{$installuser}";
	$installuser = "";
}else if ( $_SESSION["Email"]=="" )   exit();
//filter user
if (($_SESSION["ID_admin_qlync"]==1) or ($_SESSION["ID_admin_oem_qlync"]==1))
	$installuser = "";
else $installuser = $_SESSION['Email'];
define("INSTALL_USER","{$installuser}"); 

$PAGE_LIMIT = 20;
$DEFAULT_PWD = ["DEMO"=>"1qaz2wsx"];
$siteOEMTag = array();
if (isset($RPICAPP_USER_PWD)){
  foreach ($RPICAPP_USER_PWD as $key=>$data){
    if (($key == "RPIC")or ($key == "N99")) continue;
    $DEFAULT_PWD[$key] = $data[1];
    $siteOEMTag[$key] = array($data[5],"image/{$key}.png",$data[0]);
  }
}else  die("OEM/Pwd Info Not Found!!");
//var_dump($DEFAULT_PWD);
//var_dump($siteOEMTag);

$bInputData = false; //keep input data flag
//default pwd is align with SITE
if (!is_null($DEFAULT_PWD[$oem])){
  define("APP_USER_PWD",$DEFAULT_PWD[$oem]);
}else exit;
define("EMAIL_POSTFIX","@safecity.com.tw");
if ($_REQUEST['uid_filter'] =="(PAGE)" ){
	$total = getSizeByTable("customerservice.workeyegis");
	$total_page=ceil($total/$PAGE_LIMIT);
	if (isset($_REQUEST['page'])){
		$page = intval($_REQUEST["page"]); //確認頁數只能夠是數值資料
 		$page = ($page > 0) ? $page : 1; //確認頁數大於零
	}else{
		$page = 1;
	}
	$pageSTART = ($page-1)*$PAGE_LIMIT;
	$pageEND =$pageSTART + $PAGE_LIMIT;
	$pageBANNER = "頁 ";
	for ($i=1;$i<=$total_page;$i++){
		if ($i!=$page)
			$pageBANNER .="<a href='?uid_filter=(PAGE)&page={$i}'>{$i}</a> | ";
		else $pageBANNER .= "{$i} | "; 
	}
	$pageBANNER = rtrim($pageBANNER,"| ");
}
/***********below is special DEMO ***************************************/
if (isset($_REQUEST['DEMO'])){
	$msgerr = "DEMO Mode :".$_REQUEST['form-type'];
	$service = null;
  if($_REQUEST['form-type']=='search-service'){
     $searchCloudID = htmlspecialchars($_REQUEST['cloudid'], ENT_QUOTES);
	}else if($_REQUEST['form-type']=='add-service'){
    $service['OEM_ID'] = trim(htmlspecialchars($_REQUEST['add-OEM_ID'], ENT_QUOTES));
    $service['ACNO'] = trim(htmlspecialchars($_REQUEST['add-ACNO'], ENT_QUOTES));
    $service['PURP'] = trim(htmlspecialchars($_REQUEST['add-PURP'], ENT_QUOTES));
    $service['APNAME'] = trim(htmlspecialchars($_REQUEST['add-APNAME'], ENT_QUOTES));
    $service['DIGADD'] = trim(htmlspecialchars($_REQUEST['add-DIGADD'], ENT_QUOTES));
    $service['TCNAME'] = trim(htmlspecialchars($_REQUEST['add-TCNAME'], ENT_QUOTES));
    $service['TC_TEL'] = trim(htmlspecialchars($_REQUEST['add-TC_TEL'], ENT_QUOTES));
    $service['LAT'] = trim(htmlspecialchars($_REQUEST['add-LAT'], ENT_QUOTES));
    $service['LNG'] = trim(htmlspecialchars($_REQUEST['add-LNG'], ENT_QUOTES));
    $service['is_public'] = trim(htmlspecialchars($_REQUEST['add-is_public'], ENT_QUOTES));
    //$service['is_installer'] = trim(htmlspecialchars($_REQUEST['add-is_installer'], ENT_QUOTES));
    $service['bind_account'] = trim(htmlspecialchars($_REQUEST['add-bind_account'], ENT_QUOTES));
    $service['start_date'] = trim(htmlspecialchars($_REQUEST['add-start_date'], ENT_QUOTES));
    $service['end_date'] = trim(htmlspecialchars($_REQUEST['add-end_date'], ENT_QUOTES));
    $service['note'] = trim(htmlspecialchars($_REQUEST['add-note'], ENT_QUOTES));
    $service['user_name'] = trim(htmlspecialchars($_REQUEST['add-user_name'], ENT_QUOTES));
    $service['user_pwd'] = trim(htmlspecialchars($_REQUEST['add-user_pwd'], ENT_QUOTES));
    $service['APPMODE'] = trim(htmlspecialchars($_REQUEST['add-APPMODE'], ENT_QUOTES)); //DEMO
    $service['URL'] = $siteOEMTag[$service['OEM_ID']][2];
    if (insertDBfield($service))
     	$msgerr .="<div id=\"info\" class=\"success\">DEMO新增 成功.</div>";
  	else {
			$msgerr .="<div id=\"info\" class=\"error\">DEMO新增 失敗.</div>";
			$bInputData = true;
		}
  }else if($_REQUEST['form-type']=='update-service'){
    $id = trim(htmlspecialchars($_REQUEST['update-cloudid'], ENT_QUOTES));
		$service['ACNO'] = trim(htmlspecialchars($_REQUEST['update-ACNO'], ENT_QUOTES));
    $service['OEM_ID'] = trim(htmlspecialchars($_REQUEST['update-OEM_ID'], ENT_QUOTES));
    $service['PURP'] = trim(htmlspecialchars($_REQUEST['update-PURP'], ENT_QUOTES));
    $service['APNAME'] = trim(htmlspecialchars($_REQUEST['update-APNAME'], ENT_QUOTES));
    $service['DIGADD'] = trim(htmlspecialchars($_REQUEST['update-DIGADD'], ENT_QUOTES));
    $service['TCNAME'] = trim(htmlspecialchars($_REQUEST['update-TCNAME'], ENT_QUOTES));
    $service['TC_TEL'] = trim(htmlspecialchars($_REQUEST['update-TC_TEL'], ENT_QUOTES));
    $service['LAT'] = trim(htmlspecialchars($_REQUEST['update-LAT'], ENT_QUOTES));
    $service['LNG'] = trim(htmlspecialchars($_REQUEST['update-LNG'], ENT_QUOTES));
    $service['is_public'] = trim(htmlspecialchars($_REQUEST['update-is_public'], ENT_QUOTES));
    $service['bind_account'] = trim(htmlspecialchars($_REQUEST['update-bind_account'], ENT_QUOTES));
    $service['is_installer'] = trim(htmlspecialchars($_REQUEST['update-is_installer'], ENT_QUOTES));
    $service['start_date'] = trim(htmlspecialchars($_REQUEST['update-start_date'], ENT_QUOTES));
    $service['end_date'] = trim(htmlspecialchars($_REQUEST['update-end_date'], ENT_QUOTES));
    $service['note'] = trim(htmlspecialchars($_REQUEST['update-note'], ENT_QUOTES));
    $service['user_name'] = trim(htmlspecialchars($_REQUEST['update-user_name'], ENT_QUOTES));
    $service['user_pwd'] = trim(htmlspecialchars($_REQUEST['update-user_pwd'], ENT_QUOTES));
    $service['APPMODE'] = trim(htmlspecialchars($_REQUEST['update-APPMODE'], ENT_QUOTES)); //DEMO
    $service['URL'] = $siteOEMTag[$service['OEM_ID']][2];
    if (updateDBfield($id,$service))
    	$msgerr .="<div id=\"info\" class=\"success\">DEMO更新 {$id} 成功.</div>";
  	else $msgerr .="<div id=\"info\" class=\"error\">DEMO更新 {$id} 失敗.</div>";
  	$searchCloudID = $id;
  }
/***********below is common user ***************************************/
}else if(isset($_REQUEST['form-type'])){
  $type = htmlspecialchars($_REQUEST['form-type'], ENT_QUOTES);
  $service = null;
  if($type=='search-service'){
     $searchCloudID = htmlspecialchars($_REQUEST['cloudid'], ENT_QUOTES);
  }else if($type=='propr-service'){
  		$searchCloudID = trim(htmlspecialchars($_REQUEST['propr-cloudid'], ENT_QUOTES));
			$command = trim(htmlspecialchars($_REQUEST['propr-command'], ENT_QUOTES));
			$ACNO = getValueByID($searchCloudID,"ACNO");
  		$newusername = trim(htmlspecialchars($_REQUEST['propr-username'], ENT_QUOTES));
  		if ($command == "add"){
				if (insertEndUserAPI($newusername,DEFAULT_SHARE_PWD)){
	      	$msgerr .="<div id=\"info\" class=\"success\">新增分享帳號{$newusername}至 {$ACNO} - 成功.</div>";
	      	updateShareAccountList($searchCloudID,$command,$newusername);
	      	shareDevice2User(getValueByID($searchCloudID,"bind_account"),$newusername);
	      }else{
						$msgerr .="<div id=\"info\" class=\"error\">新增分享帳號{$newusername}至 {$ACNO} - 失敗.</div>";
				}
			}else if ($command == "delete"){
				if (deleteEndUserAPI($newusername)){
	      	$msgerr .="<div id=\"info\" class=\"success\">刪除分享帳號{$newusername}至 {$ACNO} - 成功.</div>";
	      	updateShareAccountList($searchCloudID,$command,$newusername);
	      }else{
						$msgerr .="<div id=\"info\" class=\"error\">刪除分享帳號{$newusername}至 {$ACNO} - 失敗.</div>";
				}			
			}
  }else if($type=='default-account'){
  		$searchCloudID = trim(htmlspecialchars($_REQUEST['default-cloudid'], ENT_QUOTES));
  		$bind_account = trim(htmlspecialchars($_REQUEST['default-bind_account'], ENT_QUOTES));
	    $service['DIGADD'] = trim(htmlspecialchars($_REQUEST['default-DIGADD'], ENT_QUOTES));
	    $service['TCNAME'] = trim(htmlspecialchars($_REQUEST['default-TCNAME'], ENT_QUOTES));
	    $service['TC_TEL'] = trim(htmlspecialchars($_REQUEST['default-TC_TEL'], ENT_QUOTES));
  		if (getAdminUserID($bind_account) < 0)
  			if (createAdminUser($bind_account)){
	  			$msgerr .= "<div id=\"info\" class=\"success\">建立Admin帳號{$bind_account} - 成功.</div>";
	  			updateAdminUserInfo($bind_account,$service);
	  		}else  $msgerr .="<div id=\"info\" class=\"error\">建立Admin帳號{$bind_account} - 失敗.</div>";
  		else
  			if (setDefaultAdminUser($bind_account)){
	  			$msgerr .= "<div id=\"info\" class=\"success\">恢復Admin帳號{$bind_account}預設值 - 成功.</div>";
	  			updateAdminUserInfo($bind_account,$service);
	  		}else  $msgerr .="<div id=\"info\" class=\"error\">恢復Admin帳號{$bind_account}預設值 - 失敗.</div>";
  }else if($type=='validate-account'){
      if ($_REQUEST['validate-type']=='validate-bind_account'){
        $searchCloudID = trim(htmlspecialchars($_REQUEST['valid-cloudid'], ENT_QUOTES));
        $service['bind_account'] = trim(htmlspecialchars($_REQUEST['valid-bind_account'], ENT_QUOTES));
        $ACNO = trim(htmlspecialchars($_REQUEST['valid-ACNO'], ENT_QUOTES));
        $service['action'] = "VALIDATE ({$searchCloudID})bind_account={$service['bind_account']}";
        if (getUserID($service['bind_account'])>0){
            if (getUserID($ACNO)>0){
              $service['user_name']=$ACNO;  //update bind_account/user_name
              if (shareDevice2User($service['bind_account'],$ACNO)){
                $msgerr .= "<div id=\"info\" class=\"success\">分享攝影機至 {$ACNO} - 成功.</div>";
              }else{
								$msgerr .="<div id=\"info\" class=\"error\">分享{$service['bind_account']}攝影機至 {$ACNO} - 失敗.</div>";
								$service['result'] = "FAIL";
							}
              if (createAdminUser($service['bind_account']))
                  $msgerr .="<div id=\"info\" class=\"success\">租賃後台帳號 {$service['bind_account']} 建立成功.預設密碼同帳號</div>";
              else $msgerr .="<div id=\"info\" class=\"error\">租賃後台帳號 {$service['bind_account']} 已存在</div>";
            }
        }else{
					$msgerr .="<div id=\"info\" class=\"error\">分享攝影機失敗. 帳號{$service['bind_account']}不存在</div>";
					$service['result'] = "FAIL";
				}
        updateDBfield($searchCloudID,$service); //update bind_account
 
      }else if ($_REQUEST['validate-type']=='validate-ACNO'){
        $searchCloudID = trim(htmlspecialchars($_REQUEST['valid-cloudid'], ENT_QUOTES));
        $service['ACNO'] = trim(htmlspecialchars($_REQUEST['valid-ACNO'], ENT_QUOTES));
        $bind_account = trim(htmlspecialchars($_REQUEST['valid-bind_account'], ENT_QUOTES));
        $service['action'] = "VALIDATE ({$searchCloudID})ACNO={$service['ACNO']}";
        if (getUserID($service['ACNO'])<0){   //create ANCO account
          if (insertEndUserAPI($service['ACNO'])){ 
              $service['user_name']=$service['ACNO'];
              $msgerr .="<div id=\"info\" class=\"success\">分享帳號 {$service['ACNO']} 建立成功.</div>";
              if (getUserID($bind_account)>0){
                  if (shareDevice2User($bind_account,$service['ACNO']))
                    $msgerr .="<div id=\"info\" class=\"success\">分享攝影機至 {$service['ACNO']} - 成功.</div>";
                  else{
										$msgerr .="<div id=\"info\" class=\"error\">分享{$bind_account}攝影機至 {$service['ACNO']} - 失敗.</div>";
										$service['result'] = "FAIL";
									}
                  if (createAdminUser($service['bind_account']))
                      $msgerr .="<div id=\"info\" class=\"success\">租賃後台帳號 {$bind_account} 建立成功.預設密碼同帳號</div>";
                  else $msgerr .="<div id=\"info\" class=\"error\">租賃後台帳號 {$bind_account} 已存在</div>";
              }else{
								$msgerr .="<div id=\"info\" class=\"error\">分享攝影機失敗. 帳號{$bind_account}不存在</div>";
								$service['result'] = "FAIL";
							} 
          }else{
             $service['user_name'] ="";
             $msgerr .="<div id=\"info\" class=\"error\">分享帳號 {$service['ACNO']} 建立失敗.</div>";
             $service['result'] = "FAIL";
          }
        }else{
        	$service['user_name']=$service['ACNO'];
          $msgerr .="<div id=\"info\" class=\"error\">分享帳號 {$service['ACNO']} 已存在.</div>";
        }
      updateDBfield($searchCloudID,$service);
         
    }

		if ($service['result'] == "") $service['result'] = "SUCCESS";
    insertGISLOG($service);
  }else if($type=='add-service'){
    $service = null;
    $service['OEM_ID'] = trim(htmlspecialchars($_REQUEST['add-OEM_ID'], ENT_QUOTES));
    $service['ACNO'] = trim(htmlspecialchars($_REQUEST['add-ACNO'], ENT_QUOTES));
    $service['PURP'] = trim(htmlspecialchars($_REQUEST['add-PURP'], ENT_QUOTES));
    $service['APNAME'] = trim(htmlspecialchars($_REQUEST['add-APNAME'], ENT_QUOTES));
    $service['DIGADD'] = trim(htmlspecialchars($_REQUEST['add-DIGADD'], ENT_QUOTES));
    $service['TCNAME'] = trim(htmlspecialchars($_REQUEST['add-TCNAME'], ENT_QUOTES));
    $service['TC_TEL'] = trim(htmlspecialchars($_REQUEST['add-TC_TEL'], ENT_QUOTES));
    $service['LAT'] = trim(htmlspecialchars($_REQUEST['add-LAT'], ENT_QUOTES));
    $service['LNG'] = trim(htmlspecialchars($_REQUEST['add-LNG'], ENT_QUOTES));
    $service['is_public'] = trim(htmlspecialchars($_REQUEST['add-is_public'], ENT_QUOTES));
    ////$service['is_installer'] = trim(htmlspecialchars($_REQUEST['add-is_installer'], ENT_QUOTES));
    $service['bind_account'] = trim(htmlspecialchars($_REQUEST['add-bind_account'], ENT_QUOTES));
    $service['start_date'] = trim(htmlspecialchars($_REQUEST['add-start_date'], ENT_QUOTES));
    $service['end_date'] = trim(htmlspecialchars($_REQUEST['add-end_date'], ENT_QUOTES));
    $service['note'] = trim(htmlspecialchars($_REQUEST['add-note'], ENT_QUOTES));
    $service['APPMODE'] = DEFAULT_APPMODE;
    if (getUserID($service['ACNO']) < 0){
	    //create ANCO account
	    if (insertEndUserAPI($service['ACNO'])){ 
	        $service['user_name']=$service['ACNO'];
	        $msgerr .="<div id=\"info\" class=\"success\">分享帳號 {$service['ACNO']} 建立成功.</div>";
	    }else{
	       $service['user_name'] ="";
	       $msgerr .="<div id=\"info\" class=\"error\">分享帳號 {$service['ACNO']} 建立失敗.</div>";
	       $service['result'] = "FAIL";
	    }
	    $b = createCloudService($service);
	
	    if($b){
	      $msgerr .="<div id=\"info\" class=\"success\">新增圖資成功.</div>";
	      //if bind_account exist, share all mac to ANCO
	      $userid =getUserID($service['bind_account']);
	      if ($userid>0){
	          shareDevice2User($service['bind_account'],$service['ACNO']);
	          $msgerr .="<div id=\"info\" class=\"success\">分享攝影機至 {$service['ACNO']} - 成功.</div>";
	          if (createAdminUser($service['bind_account']))
	              $msgerr .="<div id=\"info\" class=\"success\">租賃後台帳號 {$service['bind_account']} 建立成功.預設密碼同帳號</div>";
	          else{
							$msgerr .="<div id=\"info\" class=\"error\">租賃後台帳號 {$service['bind_account']} 建立失敗</div>";
							$service['result'] = "FAIL";
						}
	      }else{
					$msgerr .="<div id=\"info\" class=\"error\">分享攝影機失敗. 帳號{$service['bind_account']}不存在</div>";
					$service['result'] = "FAIL";
				}
			} 
    }else{
    	$msgerr .="<div id=\"info\" class=\"error\">分享帳號 {$service['ACNO']} 已存在. 新增圖資失敗.</div>";
    	$service['result'] = "FAIL";
    	$bInputData = true; 
    }
    //$searchCloudID = $service['ACNO'];
    $service['action'] = "ADD {$service['ACNO']}";
		if ($service['result'] == "") $service['result'] = "SUCCESS";
    insertGISLOG($service);
  }else if($type=='delete-service'){
    $deleteCloudID = htmlspecialchars($_REQUEST['delete-cloudid'], ENT_QUOTES);
    $service['ACNO'] = getValueByID($deleteCloudID);
    $b = removeByCloudID($deleteCloudID);
    if($b){
      $msgerr .="<div id=\"info\" class=\"success\">刪除 id:{$deleteCloudID} 成功.</div>";
      deleteEndUserAPI($service['ACNO']);
    }else{
      $msgerr .="<div id=\"info\" class=\"error\">刪除 id:{$deleteCloudID} 失敗.</div>";
      $service['result'] ="FAIL";
    }
    $service['action'] = "DELETE {$service['ACNO']}";
		if ($service['result'] == "") $service['result'] = "SUCCESS";
    insertGISLOG($service);
  }else if($type=='update-service'){
    $service = null;
    $service['id'] = trim(htmlspecialchars($_REQUEST['update-cloudid'], ENT_QUOTES));
    $service['OEM_ID'] = trim(htmlspecialchars($_REQUEST['update-OEM_ID'], ENT_QUOTES));
    $service['PURP'] = trim(htmlspecialchars($_REQUEST['update-PURP'], ENT_QUOTES));
    $service['APNAME'] = trim(htmlspecialchars($_REQUEST['update-APNAME'], ENT_QUOTES));
    $service['DIGADD'] = trim(htmlspecialchars($_REQUEST['update-DIGADD'], ENT_QUOTES));
    $service['TCNAME'] = trim(htmlspecialchars($_REQUEST['update-TCNAME'], ENT_QUOTES));
    $service['TC_TEL'] = trim(htmlspecialchars($_REQUEST['update-TC_TEL'], ENT_QUOTES));
    $service['LAT'] = trim(htmlspecialchars($_REQUEST['update-LAT'], ENT_QUOTES));
    $service['LNG'] = trim(htmlspecialchars($_REQUEST['update-LNG'], ENT_QUOTES));
    $service['is_public'] = trim(htmlspecialchars($_REQUEST['update-is_public'], ENT_QUOTES));
    $service['is_installer'] = trim(htmlspecialchars($_REQUEST['update-is_installer'], ENT_QUOTES));
    $service['bind_account'] = trim(htmlspecialchars($_REQUEST['update-bind_account'], ENT_QUOTES));
    $service['start_date'] = trim(htmlspecialchars($_REQUEST['update-start_date'], ENT_QUOTES));
    $service['end_date'] = trim(htmlspecialchars($_REQUEST['update-end_date'], ENT_QUOTES));
    $service['note'] = trim(htmlspecialchars($_REQUEST['update-note'], ENT_QUOTES));

    $service['ACNO'] = trim(htmlspecialchars($_REQUEST['update-ACNO'], ENT_QUOTES));
    $searchCloudID = $service['id'];

    $b = updateCloudService($service);
    if($b){
      $msgerr .="<div id=\"info\" class=\"success\">更新 {$service['id']}:{$service['ACNO']} 成功.</div>";
    }else{
      $msgerr .="<div id=\"info\" class=\"error\">更新 {$service['id']}:{$service['ACNO']} 失敗.</div>";
      $service['result'] = "FAIL";
    }
    $service['action'] = "UPDATE {$service['ACNO']}";
		if ($service['result'] == "") $service['result'] = "SUCCESS";
    insertGISLOG($service);
  }
}
function shareDevice2User($owner, $target)
{
    $sql = "select mac_addr from isat.query_info where user_name = '{$owner}' group by mac_addr";
    sql($sql,$result,$num,0);
    for ($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      if (!addShareDeviceAPI($arr['mac_addr'],$target)) return false;
    }
    return true;
    
}

function getUniqueACNO($oemid="")
{
  if ($oemid=="X02") $prefix = "MEGA";
  else $prefix = "IVD";
  //$shareAccount= $prefix.date("YmdHis");
  $shareAccount= $prefix.date("Ymd")."-";

  $max = 1000;
  $base = 10;
  $log = log($max, $base);
  $errCount = 1;
//get random number
  $sn= sprintf("%{$log}d", rand(1, $max));
  while (getUserID($shareAccount.$sn)>0){
    $sn= sprintf("%{$log}d", rand(1, $max));
    if (getUserID($shareAccount.$sn)<0) break;
    $errCount++;
    if ($errCount > $base){//if error more than 10 times, get one more digit
      $max = $max*$base;
      $log = log($max, $base);
    }
  }
  return $shareAccount.$sn;
/*
  for ($i=1;$i<1000;$i++){ //3 digits
    $sn = sprintf("%03d", $i);
    if (getUserID($shareAccount.$sn)<0)
      return $shareAccount.$sn;
  }
  return $shareAccount; //1-999 all fail
*/
}


function updateAdminUserInfo($email,$service)
{
  if (getAdminUserID($email) < 0) return false;
  $sql="UPDATE qlync.account SET 
	Company_chinese='{$service['TCNAME']}',
	Mobile='{$service['TC_TEL']}',
  Address='{$service['DIGADD']}' WHERE Email='{$email}'";
	if (DEBUG_FLAG == "ON") echo $sql;
  sql($sql,$result,$num,0);
  //if ($result) checkAdminUser($email);
  return $result;
}


function createCloudService($service){
//is_installer={$service['is_installer']},
  $sql = "INSERT INTO customerservice.workeyegis SET 
  OEM_ID='{$service['OEM_ID']}',
  ACNO='{$service['ACNO']}',
  PURP='{$service['PURP']}',
  APNAME='{$service['APNAME']}',
  DIGADD='{$service['DIGADD']}',
  TCNAME='{$service['TCNAME']}',
  TC_TEL='{$service['TC_TEL']}',
  LAT='{$service['LAT']}',
  LNG='{$service['LNG']}',
  APPMODE='{$service['APPMODE']}',
  bind_account='{$service['bind_account']}',
  is_public={$service['is_public']},
  is_installer=".DEFAULT_IS_INSTALLER.",
  user_name='{$service['user_name']}',
  user_pwd='".APP_USER_PWD."',
  start_date='{$service['start_date']}',
  end_date='{$service['end_date']}',
  note='{$service['note']}'";
  sql($sql,$result,$num,0);
  return $result;
}

function insertDBfield($service){
  $sql = "INSERT INTO customerservice.workeyegis SET ";
  foreach ($service as $key=>$value){
  	if ($key == "is_public") $sql .="{$key}={$value}, ";
    else $sql .="{$key}='{$value}', ";
  }
  $sql=rtrim($sql,", "); //remove last,
	if (DEBUG_FLAG == "ON")  echo $sql;
  sql($sql,$result,$num,0);
  return $result; 
}

function updateDBfield($id,$service){
  $sql = "UPDATE customerservice.workeyegis SET ";
  foreach ($service as $key=>$value){
  	if ($key == "is_public") $sql .="{$key}={$value}, ";
  	else if ($key == "is_installer") $sql .="{$key}={$value}, ";
    else $sql .="{$key}='{$value}', ";
  }
  $sql=rtrim($sql,", "); //remove last,
  $sql .= " WHERE id={$id}";
	if (DEBUG_FLAG == "ON")  echo $sql;
  sql($sql,$result,$num,0);
  return $result; 
}
function updateCloudService($service){
  if ($service['id'] == "") return false;
  $sql = "UPDATE customerservice.workeyegis SET 
  OEM_ID='{$service['OEM_ID']}',
  PURP='{$service['PURP']}',
  APNAME='{$service['APNAME']}',
  DIGADD='{$service['DIGADD']}',
  TCNAME='{$service['TCNAME']}',
  TC_TEL='{$service['TC_TEL']}',
  LAT='{$service['LAT']}',
  LNG='{$service['LNG']}',
  bind_account='{$service['bind_account']}',
  is_public={$service['is_public']},
  is_installer={$service['is_installer']},
  start_date='{$service['start_date']}',
  end_date='{$service['end_date']}',
  note='{$service['note']}'
   WHERE id={$service['id']}";
//  ACNO='{$service['ACNO']}', //should not change
//  APPMODE='',
//  user_name='{$service['user_name']}',
//  user_pwd='".APP_USER_PWD."', //no need to update
  sql($sql,$result,$num,0);
  return $result; 
}
function removeByCloudID($cloudid){
  $sql = "DELETE FROM customerservice.workeyegis WHERE id={$cloudid}";
  sql($sql,$result,$num,0);
  return $result;    
}

function getMAP_OEM_ID($oemid)
{
	if (($oemid == "X02") or ($oemid == "T06") )
		return $oemid;
	else return "DEMO";
}

function createServiceTable($sqlParam="")
{
	 global $siteOEMTag,$PAGE_LIMIT;
	if ($_REQUEST['uid_filter'] =="(PAGE)"){
		$limitParam = $sqlParam;
		$sqlParam = "";
	}else $limitParam = " LIMIT {$PAGE_LIMIT}";		
	if (!isset($_REQUEST['DEMO']))
		if (($sqlParam == "")) $sqlParam .= " WHERE OEM_ID IN ('T06','X02') ";
		else  $sqlParam .= " AND OEM_ID IN ('T06','X02') ";
  if (($sqlParam == "")) $sqlParam .= " WHERE is_installer=".DEFAULT_IS_INSTALLER;
  else $sqlParam .= " AND is_installer=".DEFAULT_IS_INSTALLER; 
	$sql = "select * from customerservice.workeyegis {$sqlParam} order by id desc {$limitParam}";
  sql($sql,$result,$num,0);
  for($i=0;$i<$num;$i++){
    fetch($arr,$result,$i,0);
    $services[$i] = $arr;
  }
  $html="";
  //$html ="<tr><td>(Total:{$num})</td><td></td></tr>";
  foreach($services as $service){ //two row, one for brief data, one for edit table
    $cloudid = $service['id'];
    $cloudid_js = '\''.$cloudid.'\'';

    $html .="<tr>";
    $html .="<td class=list>({$cloudid})&nbsp;&nbsp;\t";
    $now=strtotime(date('Y-m-d'));
    $exp = strtotime($service['end_date']);
    if (time() > $exp )
    	    $html .="<div class=\"tooltip\"><bold><font color=gray>{$service['ACNO']}</font></bold><span class=\"tooltiptext\">租期{$service['end_date']}已過期</span></div></td>";
    else    $html .="{$service['ACNO']}</td>";
    if (strlen($service['PURP']) > 20)
        $html .="<td class=list>{$service['PURP']} /<br> {$service['TCNAME']}</td>";
    else    $html .="<td class=list>{$service['PURP']} / {$service['TCNAME']}</td>";
    $html .="<td class=list>{$service['bind_account']}</td>";
    $html .="<td class=list>{$service['OEM_ID']} ".$siteOEMTag[$service['OEM_ID']][0]."</td>";
    if ( $service['is_public']=="1")
      $html .="<td class=list><a href='http://workeyemap.megasys.com.tw/map/rpic_map.php?key=KZo3i6UJbKd0bb6B5Suv&mylat={$service['LAT']}&mylng={$service['LNG']}&conntype=direct&oemid=".getMAP_OEM_ID($service['OEM_ID'])."' class='maplink' target=_blank>圖資公開</a></td>";
    else $html .="<td class=list>非公開</td>";
    $html.="\n<td>";
    if (isset($_REQUEST['debugadmin'])){
      $html.="<input type=\"button\" value=\"刪除\" onclick=\"removeService({$cloudid_js})\"  class='buttonEnable'>\n";
      if ($service['is_installer'] == "1") //special
				$html.="&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"javascript: switchPopup('tr-gis{$cloudid}');switchLink('edit-gis-link{$cloudid}');\" id=\"edit-gis-link{$cloudid}\" >更新</a>\n";
    }
    if ($service['is_installer'] == "1")
    		$html.="&nbsp;&nbsp;&nbsp;&nbsp;<input type=button  value='開工模式' onclick=\"window.open('installGIS.php?installuser={$service['bind_account']}','installerAPP','width=400,height=600,resizable=1,scrollbars=yes');\" class='buttonDisable'>";
    else  $html.="&nbsp;&nbsp;&nbsp;&nbsp;<a href=\"javascript: switchPopup('tr-gis{$cloudid}');switchLink('edit-gis-link{$cloudid}');\" id=\"edit-gis-link{$cloudid}\" >更新</a>\n";

    $html.="</td></tr>";

      $html.="<tr id=\"tr-gis{$cloudid}\" style=\"width=100%;display:none\"><td colspan=6>";


     $html.="<table class='mytable'>";
     //ACNO row
     $html .="\n<tr><td class=table_header>專案編號</td><td colspan=2>";
			if (isset($_REQUEST['DEMO']))
				$html .="<input type='text' id='{$cloudid}-ACNO' value='{$service['ACNO']}' size='20px'>\n";
      else if (getUserID($service['ACNO']) > 0) //updateACNO                                                                                                                       
          $html .="<input type='text' id='{$cloudid}-ACNO' value='{$service['ACNO']}' size='20px' readonly class='inputReadOnly'>";
      else $html .="<input type='text' id='{$cloudid}-ACNO' value='{$service['ACNO']}' size='20px'><font color=red>不存在!</font><input type=button value='修改' onClick=\"updateAccount({$cloudid_js},'validate-ACNO')\">";
      if ($service['user_name'] =="")
      	$html .="\n<input type=button value='設定user_name' onClick=\"updateAccount({$cloudid_js},'validate-ACNO')\">";
      $html .="</td>";
      $html .="<td class=table_header>專案名稱</td><td colspan=2><input type='text' id='{$cloudid}-PURP' value='{$service['PURP']}' size='20px'></td></tr>";
      //TCNAME row
      $html .="\n<tr><td class=table_header>施工廠商</td><td colspan=2><input type='text' id='{$cloudid}-TCNAME' value='{$service['TCNAME']}' size='20px'></td><td class=table_header>廠商電話</td><td colspan=2><input type='text' id='{$cloudid}-TC_TEL' value='{$service['TC_TEL']}' size='20px'></td></tr>";
      //APNAME row
      $html .="\n<tr><td class=table_header>施工單位</td><td colspan=2><input type='text' id='{$cloudid}-APNAME' value='{$service['APNAME']}' size='20px'></td>";
      $html .="\n<td class=table_header>施工地址</td><td colspan=2><input type='text' id='{$cloudid}-DIGADD' value='{$service['DIGADD']}' size='35px'><br>\n<input type='text' id='{$cloudid}-LAT' value='{$service['LAT']}' size='10px' readonly class='inputReadOnly'><input type='text' id='{$cloudid}-LNG' value='{$service['LNG']}' size='10px' readonly class='inputReadOnly'><input type=button value='找坐標' onclick=\"window.open('getGIS.php?tag={$cloudid}&addr='+getAddr('{$cloudid}-DIGADD'),'popuppage','width=800,height=500,toolbar=1,resizable=1,scrollbars=yes,top=100,left=100');\"></td></tr>";
      //is_public row
      $html .="\n<tr><td class=table_header>是否公開</td><td colspan=2 style=\"vertical-align: middle;\">";
      if ( $service['is_public']=="1")
        $html .="<input type=checkbox  id='{$cloudid}-is_public' checked>";
      else $html .="<input type=checkbox  id='{$cloudid}-is_public'>";
      if ( $service['is_installer']=="1")
        $html .="&nbsp;&nbsp;&nbsp;&nbsp;開工模式<input type=checkbox  id='{$cloudid}-is_installer' checked readonly>";
      //else $html .="&nbsp;&nbsp;&nbsp;&nbsp;<input type=checkbox  id='{$cloudid}-is_installer' readonly>";
      $html .="</td><td class=table_headerImage>地圖標示</td>";
      $html .="<td colspan=2>".selectOEMID("{$cloudid}-OEM_ID",$service['OEM_ID'])."</td></tr>";
      //bind_account row
      $html .="\n<tr><td class=table_header>租賃帳號</td>";
      if (isset($_REQUEST['DEMO']))
				$html .="<td colspan=2><input type='text' id='{$cloudid}-bind_account' value='{$service['bind_account']}' size='20px'></td>\n"; 
      else if (getUserID($service['bind_account']) > 0){
      	$html .="<td colspan=2><input type='text' id='{$cloudid}-bind_account' value='{$service['bind_account']}' size='20px' readonly class='inputReadOnly'>";
				if (INSTALL_USER =="")      
          $html .="<input type=button value='設定分享攝影機' onclick=\"window.open('editShare.php?owner={$service['bind_account']}&visitor={$service['user_name']}','popuppage','width=800,toolbar=1,resizable=1,scrollbars=yes,height=300,top=100,left=100');\">&nbsp;&nbsp;&nbsp;<input type=button value='Admin預設帳密相同' onClick=\"defaultAccount({$cloudid_js});\"></td>";
        else $html .="</td>"; 
      }else $html .="<td colspan=2><input type='text' id='{$cloudid}-bind_account' value='{$service['bind_account']}' size='20px'><font color=red>不存在!</font><input type=button value='修改' onClick=\"updateAccount({$cloudid_js},'validate-bind_account');\"></td>"; 
      $html .="\n<td class=table_header>租賃期間</td><td colspan=5><input type='text' id='{$cloudid}-start_date' value='{$service['start_date']}' size='10px'>";
      $html .="&#126; <input type='text' id='{$cloudid}-end_date' value='{$service['end_date']}' size='10px'></td>\n";
      $html .="</tr>";
  //DEMO row
	if (isset($_REQUEST['DEMO'])){
		$html .="\n<tr><td class=table_header>DEMO專用</td><td colspan=5 align='center'><input type='text' id='{$cloudid}-user_name' placeholder='user_name' value='{$service['user_name']}' size='20px'><input type='text' id='{$cloudid}-user_pwd' placeholder='user_pwd' value='{$service['user_pwd']}' size='20px'><input type='text' id='{$cloudid}-APPMODE' placeholder='APPMODE' value='{$service['APPMODE']}' size='20px'></td></tr>\n"; 
	}
	//propr row
	$html .="\n<tr><td class=table_header>分享帳號</td><td colspan=5><font color=black>";
	$saNum = 0;
	if ($service['share_account']!="")
 		$saNum = count(explode(';' , $service['share_account']));
 	if ($saNum > 0){
		$saArr = explode(';',$service['share_account']);
if(DEBUG_FLAG == "ON") { echo "share account#={$saNum}\n";var_dump($saArr);}
		for ($i=0;$i<$saNum;$i++){
			if ($i % 4 ==0) $html.="<br>\n";
			if (isset($_REQUEST['debugadmin']))
				$html .="<input type=button value='刪除{$saArr[$i]}' onclick=\"proprService('delete','{$saArr[$i]}',{$cloudid_js})\"> , ";
			else $html .= "{$saArr[$i]} , ";
		}
		$html .= "<br>";
	}
	if ($saNum < LIMIT_SHARE_NUMBER){
		$nUser = getUniqueShareAccount($service['ACNO']."propr");
		if ($nUser != -1){
			$html .="<input type='text' id='{$cloudid}-share_account' placeholder='share_account' value='{$nUser}'>";
		}else{
			$html .="<input type='text' id='{$cloudid}-share_account' placeholder='share_account' value='".getUniqueShareAccount($service['ACNO']."spvr")."'>";
		}
		$html .="<input type=button value='新增' onclick=\"proprService('add',$('#{$cloudid}'+'-share_account').val(),{$cloudid_js})\"  style='background-color: #0066FF;color:white;'>";
	}
	$html .="</font><br><small>預設密碼".DEFAULT_SHARE_PWD.". 監造帳號建議spvrNN, 業主帳號建議proprNN</small>";
	$html .="</td></tr>";
	//note row
	$html .="\n<tr><td class=table_header>註解</td><td colspan=4 align='center'><input type='text' id='{$cloudid}-note' value='{$service['note']}' size='60px'></td>";
		 
   	$html .= "<td><input type=button value='更新' onclick=\"updateService({$cloudid_js})\"  class='buttonAdd'>";
   	if (intval($service['is_installer'])==DEFAULT_IS_INSTALLER)
    		$html.="&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type=button  value='開工模式' onclick=\"window.open('installGIS.php?installuser={$service['bind_account']}','installerAPP','width=400,height=600,resizable=1,scrollbars=yes');\"  class='buttonDisable'>";
    $html.="</td></tr></table>";
    if (INSTALL_USER!="")
    	$html.="<script>limitCustomer({$cloudid});</script>";
    $html.="</td></tr>";
  }
  echo $html;
}
function getUniqueShareAccount($prefix)
{
  for ($i=1;$i<100;$i++){ //2 digits
    $sn = sprintf("%02d", $i);
    if (getUserID($prefix.$sn)<0)
      return $prefix.$sn;
  }
  return "-1"; //1-99 all fail
}
function selectOEMID($tag,$oemid="")
{
	global $siteOEMTag;
  $html="";
  if ($oemid!="") $html.="<img id='{$tag}_IMAGE' height=20px src='{$siteOEMTag[$oemid][1]}'>";
  else $html.="<img id='{$tag}_IMAGE' height=20px src=''>"; 
  $html.="&nbsp;<select name=\"{$tag}\" id=\"{$tag}\"  onchange=\"setSelectImage('{$tag}','{$tag}_IMAGE')\">\n";
  if ($oemid=="") $html.="<option></option>\n"; //&#9660; &#x25bc     

  foreach ($siteOEMTag as $key=>$value){
  	if (!isset($_REQUEST['DEMO']))
  		if (($key=="T04") or ($key=="T05") or ($key=="K01")) continue;
    if ($oemid==$key)
      $html.="<option value=\"{$key}\" selected>{$value[0]}</option>";
    else
      $html.="<option value=\"{$key}\">{$value[0]}</option>";
  }
  $html.="</select>\n";
  return $html;
}

function updateShareAccountList($id,$command,$username){
	//if key is mac
	$list = "";
	$sqlmac = "SELECT share_account from customerservice.workeyegis WHERE id ={$id}"; 
	sql($sqlmac,$resultmac,$nummac,0);
	if ($resultmac){
		fetch($arrmac,$resultmac,0,0);
		$list = $arrmac['share_account'];
	}else return $resultmac;
	//$macNum = count(explode(';' , $list));	
	if ($command == "add"){
			$list .= ";{$username}"; //add after
	}else if ($command == "delete"){
	//extract $MAC from $list
			$list = str_replace("{$username}","",$list);
			$list = str_replace(";;",";",$list); //remove middle cam
	}
	$list = ltrim($list,";"); //only one camera case	
	$list = rtrim($list,";"); //only one camera case

  $sql = "UPDATE customerservice.workeyegis SET share_account = '{$list}' WHERE id={$id}";
if (DEBUG_FLAG == "ON")  echo $sql;
  sql($sql,$result,$num,0);
  return $result; 
}
?>
<!--html>
<head>
</head>
<body-->
<script src="../user_log/js/jquery-1.11.1.min.js"></script>
<script type="text/javascript">
function setReadOnly(tag){
		$("#"+tag).prop("readonly", true);
		$("#"+tag).css({'background-color' : 'grey'});
}

function limitCustomer(id){
	setReadOnly(id+'-TCNAME');
	setReadOnly(id+'-PURP');
	setReadOnly(id+'-OEM_ID');
	$('#'+id+'-OEM_ID').prop('disabled', true);
	setReadOnly(id+'-start_date');
	setReadOnly(id+'-end_date');
}
</script>
<link rel=stylesheet type="text/css" href="../user_log/js/style.css">
<style type="text/css">
.table_header{height:25px; background-color:#0069C9; font:bold 14px arial; color:#FFF; text-align:center; border:1px solid #fff}
.table_headerImage{height:55px; background-color:#0069C9; font:bold 14px arial; color:#FFF; text-align:center; border:1px solid #fff}
table.mytable {
   border: 1px solid #CCC;
}
td.list {
font: bold 14px arial !important; 
color:black !important;
}

tr.mylist{
 background-color:#0069C9;
 font:bold 14px arial;
 color:#FFF;
 text-align:center;
 border:1px;
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
.buttonAdd{
	background-color: #0066FF; /*Aquamarine*/
	color:white;
	width: 100px;
	text-align: center;
	vertical-align: middle;
	float: right;
	font-weight:bold;
}
.buttonEnable{
	background-color: LightPink;
	float: right;
	/*font-weight:bold;*/
}
.buttonDisable{
	background-color: #99FF99;
	float: right;
	/*font-weight:bold;*/
}

.inputReadOnly{
	background-color : grey;
}

a, a:hover #maplink{
text-decoration: none;
}
</style>

<?php
if ($installuser!=""){
echo "<div align=center><b><font size=5>".TXT_TCNAME."<a href='http://workeyemap.megasys.com.tw/map/rpic_map.php?key=KZo3i6UJbKd0bb6B5Suv' class='maplink' target=_blank>地圖</a>資料管理</font></b></div>";
}else{
?>
<div align=center><b><font size=5><?php echo TXT_TCNAME;?><a href='showGISLog.php' class='maplink' target=_blank>施工</a><a href='http://workeyemap.megasys.com.tw/map/rpic_map.php?key=KZo3i6UJbKd0bb6B5Suv' class='maplink' target=_blank>地圖</a>資料管理</font></b></div>
<form method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
<input type="hidden" name="form-type" value="search-service">
<?php if (isset($_REQUEST["debugadmin"]))  echo "<input type=hidden name=debugadmin value=1>"; ?> 
<select name="uid_filter" id="uid_filter" onchange="optionValue(this.form.uid_filter, this);this.form.submit();">
<option value="" >(FOLD/SEARCH)</option>
<option value="(MORE)" <?php if($_REQUEST['uid_filter'] =="(MORE)" ) echo "selected";?>>(MORE)</option>
<option value="(PAGE)" <?php if($_REQUEST['uid_filter'] =="(PAGE)" ) echo "selected";?>>(PAGE)</option>
</select>&nbsp;&nbsp;
<input type="text" size="30" name="cloudid" id="cloudid" value="<?php echo $searchCloudID?>" placeholder='id ,<?php echo TXT_ACNO.",".TXT_PURP.",".TXT_TCNAME;?>'>&nbsp;&nbsp;<input type="submit" value="搜尋">
<?php 
if ($_REQUEST['uid_filter'] =="(PAGE)")
	echo $pageBANNER;
if (isset($_REQUEST['DEMO']))
	echo "<input type=hidden name=DEMO value=1>";
?>
</form>
<?php
}//installuser check
?>
<div style="display: none" id="customMessage"></div>
<div style="display: none" id="myMessage">
<?php
if (isset($msgerr))
echo $msgerr;
?>
</div>
      <table style="border-collapse">
          <tr class="mylist">
            <th>編號</th>
            <th>名稱 / 廠商</th>
            <th>租賃帳號</th>
            <th>標示</th>
            <th>公開狀態</th>
            <th></th>
          </tr>
<tr><td colspan=6 align=right style="text-align: right;vertical-align: top;">
<?php if ($installuser==""){ ?>
<a href="javascript: switchPopup('tr-add-gis');switchLink('add-gis-link');" id="add-gis-link" >新增工地</a>
<?php } ?>
</td></tr>
<tr id="tr-add-gis" style="width=100%;display:none"><td colspan=6 align=right>
<table class="mytable">

<tr><td class=table_header>專案編號</td><td colspan=2><input type='text' name='ACNO' id='ACNO' placeholder='ACNO' size='20px'></td>
 <td class=table_header>專案名稱</td><td colspan=2><input type='text' name='PURP' id='PURP' placeholder='PURP' size='20px'></td></tr>
<tr><td class=table_header>施工廠商</td><td colspan=2><input type='text' name='TCNAME' id='TCNAME' placeholder='TCNAME' size='20px'></td>
 <td class=table_header>廠商電話</td><td colspan=2><input type='text' name='TC_TEL' id='TC_TEL' placeholder='TC_TEL' size='20px'></td></tr>
<tr><td class=table_header>施工單位</td><td colspan=2><input type='text' name='APNAME' id='APNAME' placeholder='APNAME' size='20px'></td>
 <td class=table_header>施工地址</td><td colspan=2><input type='text' name='DIGADD' id='DIGADD' placeholder='DIGADD' size='35px'>
<br><input type='text' name='LAT' id='LAT' placeholder='LAT' size='10px' readonly class='inputReadOnly'>
<input type='text' name='LNG' id='LNG' placeholder='LNG' size='10px' readonly class='inputReadOnly'>
<input type=button name='GISbtn' id='GISbtn' value="找坐標" onclick="window.open('getGIS.php?addr='+getAddr('DIGADD'),'popuppage','width=800,height=500,toolbar=1,resizable=1,scrollbars=yes,top=100,left=100');">
</td></tr>
<tr><td class=table_header>是否公開</td><td colspan=2 style="vertical-align: middle;"><input type=checkbox  name='is_public' id='is_public' ></td>
<td class=table_headerImage>地圖標示</td><td colspan=2><?php echo selectOEMID("OEM_ID","");?></td>
</tr>
 <tr><td class=table_header>租賃帳號</td><td colspan=2><input type='text' name='bind_account' id='bind_account' placeholder='bind_account' size='20px'>
 &nbsp;<a href="/html/member/delete_user.php" target='_blank'>須先建立帳號並綁定攝影機</a></td>
 <td class=table_header>租賃期間</td><td colspan=2><input type='text' name='start_date' id='start_date' value='<?php echo date("Y-m-d");?>' size='10px'>
 &#126; <input type='text' name='end_date' id='end_date' value='<?php echo date('Y-m-d', strtotime('+2 years'));?>' size='10px'>
 </td>
 </tr>
 <?php if (isset($_REQUEST['DEMO'])){ ?>
<tr><td class=table_header>DEMO專用</td><td colspan=5 align="center">
<input type='text' name='user_name' id='user_name' placeholder='user_name' value='ivedatest' size='20px'>
<input type='text' name='user_pwd' id='user_pwd' placeholder='user_pwd' value='<?php echo $DEFAULT_PWD['DEMO'];?>' size='20px'>
<input type='text' name='APPMODE' id='APPMODE' placeholder='APPMODE' value='DEMO專用' size='20px'>
 </td></tr>
  <?php } ?>
<!--tr><td class=table_header>分享帳號</td><td colspan=5><input type='text' name='share_account' id='share_account' placeholder='share_account'></td></tr-->

<tr><td class=table_header>註解</td><td colspan=4 align="center"><input type='text' name='note' id='note' placeholder='note' size='60px'></td><td><input type=button value="新增" onclick='addService();'  class='buttonAdd'></td></tr>

</table>
</td></tr>

<?php
            if ($searchCloudID!=""){
            	if (!is_numeric($searchCloudID)){
            		if (INSTALL_USER!="")
            			createServiceTable(" WHERE (ACNO like '%{$searchCloudID}%') OR (PURP like '%{$searchCloudID}%') OR  (TCNAME like '%{$searchCloudID}%') AND bind_account='".INSTALL_USER."'");
            		else  createServiceTable(" WHERE (ACNO like '%{$searchCloudID}%') OR (PURP like '%{$searchCloudID}%') OR  (TCNAME like '%{$searchCloudID}%')");
							}else{
								if (INSTALL_USER!="")
	            		createServiceTable("WHERE bind_account='".INSTALL_USER."'");
								else createServiceTable();
							} 
            }else if ($_REQUEST['uid_filter'] =="(MORE)" ){
            	$PAGE_LIMIT=50;
              createServiceTable();
            }else if ($_REQUEST['uid_filter'] =="(PAGE)" ){
              createServiceTable(" LIMIT {$pageSTART}, {$pageEND}");
            }else{
            	if (INSTALL_USER!="")
            		createServiceTable("WHERE bind_account='".INSTALL_USER."'");
            	else createServiceTable();
						}
?>

      </table>


  <form id="add-service" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="form-type" value="add-service"> 
    <input type="hidden" name="add-OEM_ID" id="add-OEM_ID">
    <input type="hidden" name="add-ACNO" id="add-ACNO">
    <input type="hidden" name="add-PURP" id="add-PURP">
    <input type="hidden" name="add-APNAME" id="add-APNAME">
    <input type="hidden" name="add-DIGADD" id="add-DIGADD">
    <input type="hidden" name="add-TCNAME" id="add-TCNAME">
    <input type="hidden" name="add-TC_TEL" id="add-TC_TEL">
    <input type="hidden" name="add-LAT" id="add-LAT">
    <input type="hidden" name="add-LNG" id="add-LNG">
    <input type="hidden" name="add-bind_account" id="add-bind_account">
    <input type="hidden" name="add-is_public" id="add-is_public">
    <!--input type="hidden" name="add-is_installer" id="add-is_installer"-->
    <input type="hidden" name="add-start_date" id="add-start_date">
    <input type="hidden" name="add-end_date" id="add-end_date">
    <input type="hidden" name="add-note" id="add-note">
<?php
		if (isset($_REQUEST['DEMO'])){
    echo "<input type=\"hidden\" name=\"add-user_name\" id=\"add-user_name\">";
    echo "<input type=\"hidden\" name=\"add-user_pwd\" id=\"add-user_pwd\">";
    echo "<input type=\"hidden\" name=\"add-APPMODE\" id=\"add-APPMODE\">";
    echo "<input type=hidden name=DEMO value=1>";
  	}
	if (isset($_REQUEST["debugadmin"]))  echo "<input type=hidden name=debugadmin value=1>"; 
?>
  </form>

  <form id="delete-service" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="form-type" value="delete-service">
    <input type="hidden" name="delete-cloudid" id="delete-cloudid">
    <?php if (isset($_REQUEST["debugadmin"]))  echo "<input type=hidden name=debugadmin value=1>"; ?> 
  </form>
  <form id="update-service" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="form-type" value="update-service">
    <input type="hidden" name="update-cloudid" id="update-cloudid">
    <input type="hidden" name="update-OEM_ID" id="update-OEM_ID">
    <input type="hidden" name="update-ACNO" id="update-ACNO">
    <input type="hidden" name="update-PURP" id="update-PURP">
    <input type="hidden" name="update-APNAME" id="update-APNAME">
    <input type="hidden" name="update-DIGADD" id="update-DIGADD">
    <input type="hidden" name="update-TCNAME" id="update-TCNAME">
    <input type="hidden" name="update-TC_TEL" id="update-TC_TEL">
    <input type="hidden" name="update-LAT" id="update-LAT">
    <input type="hidden" name="update-LNG" id="update-LNG">
    <input type="hidden" name="update-bind_account" id="update-bind_account">
    <input type="hidden" name="update-is_public" id="update-is_public">
    <input type="hidden" name="update-is_installer" id="update-is_installer">
    <input type="hidden" name="update-start_date" id="update-start_date">
    <input type="hidden" name="update-end_date" id="update-end_date">
    <input type="hidden" name="update-note" id="update-note">
<?php
		if (isset($_REQUEST['DEMO'])){
    echo "<input type=\"hidden\" name=\"update-user_name\" id=\"update-user_name\">";
    echo "<input type=\"hidden\" name=\"update-user_pwd\" id=\"update-user_pwd\">";
    echo "<input type=\"hidden\" name=\"update-APPMODE\" id=\"update-APPMODE\">";
    echo "<input type=hidden name=DEMO value=1>"; 
  	}
		if (isset($_REQUEST["debugadmin"]))  echo "<input type=hidden name=debugadmin value=1>"; 
?>
  </form>
  <form id="update-account-service" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="form-type" value="validate-account">
    <input type="hidden" name="valid-ACNO" id="valid-ACNO">
    <input type="hidden" name="valid-bind_account" id="valid-bind_account">
    <input type="hidden" name="valid-cloudid" id="valid-cloudid">
    <input type="hidden" name="validate-type" id="validate-type">
<?php if (isset($_REQUEST["debugadmin"]))  echo "<input type=hidden name=debugadmin value=1>"; ?>
  </form>
  <form id="default-account-service" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="form-type" value="default-account">
    <input type="hidden" name="default-bind_account" id="default-bind_account">
    <input type="hidden" name="default-cloudid" id="default-cloudid">
    <input type="hidden" name="default-DIGADD" id="default-DIGADD">
    <input type="hidden" name="default-TCNAME" id="default-TCNAME">
    <input type="hidden" name="default-TC_TEL" id="default-TC_TEL">

<?php if (isset($_REQUEST["debugadmin"]))  echo "<input type=hidden name=debugadmin value=1>"; ?>
  </form>
  <form id="propr-service" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
    <input type="hidden" name="form-type" value="propr-service">
    <input type="hidden" name="propr-cloudid" id="propr-cloudid">
    <input type="hidden" name="propr-command" id="propr-command">
    <input type="hidden" name="propr-username" id="propr-username">
<?php if (isset($_REQUEST["debugadmin"]))  echo "<input type=hidden name=debugadmin value=1>";
    ?>
  </form>
<script type="text/javascript">
<?php 
if ($searchCloudID!=""){
  if (is_numeric($searchCloudID)) //is_int cannot tell integer string 
    echo "switchPopup('tr-gis{$searchCloudID}');\nswitchLink('edit-gis-link{$searchCloudID}');";
}
if (isset($msgerr))
	echo "$('#myMessage').show();";
?>
setDefault();

var gisDBfieldName = [
"ACNO",
"PURP",
"TCNAME",
"TC_TEL",
"APNAME",
"DIGADD",
//"LAT", //=>GISbtn
//"LNG",
"OEM_ID",
"bind_account",
//"user_name",
//"user_pwd",
//"APPMODE",
"start_date",
"end_date"];

function setDefault()
{
  var ACNO="<?php echo getUniqueACNO();?>";
  if (ACNO!="")
    $('#ACNO').val(ACNO);
	$('#start_date').bind('input', function() { 
	    //$(this).val() // get the current value of the input field.
	    var end_date = $('#start_date').val();
			var newDate = new Date(end_date);
			newDate.setMonth(newDate.getMonth() + 24);
	    $('#end_date').val(newDate.toISOString().substring(0, 10));
	});
	<?php //for insert entry fail
		if ($bInputData){
			echo "switchPopup('tr-add-gis');switchLink('add-gis-link');";
			echo "$('#PURP').val('".$service['PURP']."');";
			echo "$('#TCNAME').val('".$service['TCNAME']."');";
			echo "$('#TC_TEL').val('".$service['TC_TEL']."');";
			echo "$('#APNAME').val('".$service['APNAME']."');";
			echo "$('#DIGADD').val('".$service['DIGADD']."');";
			echo "$('#LAT').val('".$service['LAT']."');";
			echo "$('#LNG').val('".$service['LNG']."');";
			echo "$('#OEM_ID').val('".$service['OEM_ID']."');";
			echo "setSelectImage('OEM_ID','OEM_ID_IMAGE');";
			echo "$('#bind_account').val('".$service['bind_account']."');";
			if (intval($service['is_public']) == 1)
				echo "$('#is_public').prop('checked', true);;";
			echo "$('#note').val('".$service['note']."');";
			echo "$('#start_date').val('".$service['start_date']."');";
			echo "$('#end_date').val('".$service['end_date']."');";
		}
	?>
}

function defaultAccount(cloudid){
  var bind_account = $('#'+cloudid+'-bind_account').val();
  $('#default-bind_account').val(bind_account);

  var TCNAME = $('#'+cloudid+'-TCNAME').val();
  var TC_TEL = $('#'+cloudid+'-TC_TEL').val();
  var DIGADD = $('#'+cloudid+'-DIGADD').val();
  $('#default-TCNAME').val(TCNAME);
  $('#default-TC_TEL').val(TC_TEL);
  $('#default-DIGADD').val(DIGADD);

  $('#default-cloudid').val(cloudid);  
  $('#default-account-service').submit();
}
function updateAccount(cloudid,formtype){
  var ACNO = $('#'+cloudid+'-ACNO').val();
  var bind_account = $('#'+cloudid+'-bind_account').val();
  $('#validate-type').val(formtype);
  $('#valid-ACNO').val(ACNO);
  $('#valid-bind_account').val(bind_account);
  $('#valid-cloudid').val(cloudid);  
  $('#update-account-service').submit();
}
function updateService(cloudid){
  hideMessage();
  resetCheckcss(cloudid);
  var OEM_ID = $('#'+cloudid+'-OEM_ID').val();
  var ACNO = $('#'+cloudid+'-ACNO').val();
  var PURP = $('#'+cloudid+'-PURP').val();
  var APNAME = $('#'+cloudid+'-APNAME').val();
  var DIGADD = $('#'+cloudid+'-DIGADD').val();
  var TCNAME = $('#'+cloudid+'-TCNAME').val();
  var TC_TEL = $('#'+cloudid+'-TC_TEL').val();
  var LAT = $('#'+cloudid+'-LAT').val();
  var LNG = $('#'+cloudid+'-LNG').val();
  var bind_account = $('#'+cloudid+'-bind_account').val();
  var is_public = 0;
  if ($('#'+cloudid+'-is_public').is(":checked")) is_public = 1;
  var is_installer = 0;
  if ($('#'+cloudid+'-is_installer').is(":checked")) is_installer = 1;
  var start_date = $('#'+cloudid+'-start_date').val();  
  var end_date = $('#'+cloudid+'-end_date').val();
  var note = $('#'+cloudid+'-note').val();
<?php
		if (isset($_REQUEST['DEMO'])){
			echo "var APPMODE = $('#'+cloudid+'-APPMODE').val();\n";
  		echo "var user_name = $('#'+cloudid+'-user_name').val();\n";
  		echo "var user_pwd = $('#'+cloudid+'-user_pwd').val();\n";
		}
?>
	if (isEmpty(PURP))	setNotice(cloudid+"-PURP");
	if (isEmpty(TCNAME))	setNotice(cloudid+"-TCNAME");
	if (isEmpty(TC_TEL))	setNotice(cloudid+"-TC_TEL");
	if (isEmpty(APNAME))	setNotice(cloudid+"-APNAME");
	if (isEmpty(DIGADD))	setNotice(cloudid+"-DIGADD");
  if (isEmpty(bind_account))	setNotice(cloudid+"-bind_account");
  if (!checkDate(start_date)){
  	setNotice(cloudid+"-start_date");
  	showCustomMessage('error','日期輸入錯誤.');
  	return;
	}
  if (!checkDate(end_date)){
		setNotice(cloudid+"-end_date");
  	showCustomMessage('error','日期輸入錯誤.');
  	return;
	}
	
  $('#update-cloudid').val(cloudid);
  $('#update-OEM_ID').val(OEM_ID);
  $('#update-ACNO').val(ACNO); 
  $('#update-PURP').val(PURP);
  $('#update-APNAME').val(APNAME);
  $('#update-DIGADD').val(DIGADD);
  $('#update-TCNAME').val(TCNAME);
  $('#update-TC_TEL').val(TC_TEL);
  $('#update-LAT').val(LAT);
  $('#update-LNG').val(LNG);
  $('#update-bind_account').val(bind_account);
  $('#update-is_public').val(is_public);
  $('#update-is_installer').val(is_installer);
  $('#update-start_date').val(start_date);
  $('#update-end_date').val(end_date);
  $('#update-note').val(note);  

<?php
		if (isset($_REQUEST['DEMO'])){
			echo "$('#update-APPMODE').val(APPMODE);\n";
  		echo "$('#update-user_name').val(user_name);\n";
  		echo "$('#update-user_pwd').val(user_pwd);\n";
		}
?>
  $('#update-service').submit();
}

function removeService(cloudid){
  if (window.confirm('確認要刪除 \''+cloudid+'\' ?') == true) {
    hideMessage();
    $('#delete-cloudid').val(cloudid);
    $('#delete-service').submit();
  }
}

function getAddr(idname){
  var idvalue=$('#'+idname).val();
  //alert(idvalue); 
  return encode_utf8(idvalue);
}

function proprService(command,username, cloudid){
	if (!isEmpty(username)) {
		$('#propr-username').val(username);
		$('#propr-command').val(command);
		$('#propr-cloudid').val(cloudid);
		$('#propr-service').submit();
	}else{
		setNotice(cloudid+"-share_account");
	}
}

function addService(){
  hideMessage();
  resetCheckcss("");
  var OEM_ID = $('#OEM_ID').val();
  var ACNO = $('#ACNO').val();
  var PURP = $('#PURP').val();
  var APNAME = $('#APNAME').val();
  var DIGADD = $('#DIGADD').val();
  var TCNAME = $('#TCNAME').val();
  var TC_TEL = $('#TC_TEL').val();
  var LAT = $('#LAT').val();
  var LNG = $('#LNG').val();
  var bind_account = $('#bind_account').val();
  var is_public = 0;
  if ($('#is_public').is(":checked")) is_public = 1;
  var is_installer = 0;
  //if ($('#is_installer').is(":checked")) is_installer = 1;
  var start_date = $('#start_date').val();  
  var end_date = $('#end_date').val();
  var note = $('#note').val();
<?php
		if (isset($_REQUEST['DEMO'])){
			echo "var APPMODE = $('#APPMODE').val();\n";
  		echo "var user_name = $('#user_name').val();\n";
  		echo "var user_pwd = $('#user_pwd').val();\n";
		}
?>
  if( (isEmpty(OEM_ID)) || (isEmpty(ACNO)) || (isEmpty(PURP)) || (isEmpty(APNAME))
     || (isEmpty(DIGADD)) || (isEmpty(TCNAME)) || (isEmpty(TC_TEL)) || (isEmpty(LAT))
     || (isEmpty(LNG)) || (isEmpty(bind_account)) || (isEmpty(start_date)) || (isEmpty(end_date)) )
  {
  	if (isEmpty(ACNO))	setNotice("ACNO");
  	if (isEmpty(PURP))	setNotice("PURP");
  	if (isEmpty(TCNAME))	setNotice("TCNAME");
  	if (isEmpty(TC_TEL))	setNotice("TC_TEL");
  	if (isEmpty(APNAME))	setNotice("APNAME");
  	if (isEmpty(DIGADD))	setNotice("DIGADD");
  	if (isEmpty(LAT))	setNotice("GISbtn");
  	if (isEmpty(OEM_ID))	setNotice("OEM_ID");
    if (isEmpty(bind_account))	setNotice("bind_account");
  	if (isEmpty(start_date))	setNotice("start_date");
  	if (isEmpty(end_date))	setNotice("end_date");
    showCustomMessage('error','必要欄位不可空白.');
    return;
  }
  if (!checkDate(start_date)){
  	setNotice("start_date");
  	showCustomMessage('error','日期輸入錯誤.');
  	return;
	}
  if (!checkDate(end_date)){
		setNotice("end_date");
  	showCustomMessage('error','日期輸入錯誤.');
  	return;
	}
  	
  $('#add-OEM_ID').val(OEM_ID);
  $('#add-ACNO').val(ACNO); 
  $('#add-PURP').val(PURP);
  $('#add-APNAME').val(APNAME);
  $('#add-DIGADD').val(DIGADD);
  $('#add-TCNAME').val(TCNAME);
  $('#add-TC_TEL').val(TC_TEL);
  $('#add-LAT').val(LAT);
  $('#add-LNG').val(LNG);
  $('#add-bind_account').val(bind_account);
  $('#add-is_public').val(is_public);
  //$('#add-is_installer').val(is_installer);
  $('#add-start_date').val(start_date);
  $('#add-end_date').val(end_date);
  $('#add-note').val(note);
<?php
		if (isset($_REQUEST['DEMO'])){
			echo "$('#add-APPMODE').val(APPMODE);\n";
  		echo "$('#add-user_name').val(user_name);\n";
  		echo "$('#add-user_pwd').val(user_pwd);\n";
		}
?>
  $('#add-service').submit();
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

function showCustomMessage(className,msg){
  var obj = $('#customMessage');
  obj.attr("class", className);
  obj.html(msg);
  obj.show();
}

function resetCheckcss(cloudid){
	var prefix ="";
	//$('#inputId').attr('readonly', true); //<jquery1.9
	//$('#inputId').prop('readonly', true); //>jquery1.9
	if (cloudid !="") prefix = cloudid + "-";
	for (var i = 0; i < gisDBfieldName.length; i++) {
		if ( $("#"+prefix+gisDBfieldName[i]).is('[readonly]') ) continue;
		$("#"+prefix+gisDBfieldName[i] ).css({'background-color' : 'white'});
	}
	$("#"+prefix+"GISbtn" ).css({'background-color' : 'white'}); //LAT check
}
function hideMessage(){
  $('#myMessage').hide();
  $('#customMessage').hide();
}

function optionValue(thisformobj, selectobj)
{
  var chosenoption=selectobj.options[selectobj.selectedIndex];
  thisformobj.value = chosenoption.value;
  //empty search value if click select
  //document.getElementById('cloudid').value="";
  selectobj.form.cloudid.value="";
}
function confirmDelete(type, name)  
{//jinho add confirmDelete
    return confirm('確認刪除'+type+'帳號'+name+'?');
}
function updateUserValue(value)
{    // this gets called from the popup window and updates
    //alert(value);
    //parse string tagname, value, value2 
    var arr = value.split(";");
    var t1="LAT";
    var t2="LNG"; 
    if (arr[0]!="")
    {
      t1= arr[0]+"-"+t1;
      t2= arr[0]+"-"+t2;
    }
    document.getElementById(t1).value = parseFloat(arr[1]).toFixed(8);
    document.getElementById(t2).value = parseFloat(arr[2]).toFixed(8);
}

function switchLink(tagname)
{
  var substring = "取消";
  var string = $('#'+tagname).text();
  //alert( string);
  if (string.indexOf(substring) !== -1){ //cancel found
    //alert(string.replace(substring, ""));
    $('#'+tagname).text(string.replace(substring, ""));
  }else{ //cancel not found
    //alert(substring + string);
    $('#'+tagname).text(substring + string);
  }
}
function switchPopup(id)
{//table-row, table-cell //block will break colspan
  var el = document.getElementById(id);
  //alert(id);
  if (el.style.display == "none"){
    if (id.indexOf("tr") !== -1)
      el.style.display = 'table-row';
    else if (id.indexOf("td") !== -1)
      el.style.display = 'table-cell';
    else    el.style.display = 'block';
  }else if (el.style.display == "block")
    el.style.display = 'none';
  else if (el.style.display == "table-row")
    el.style.display = 'none';
  else if (el.style.display == "table-cell")
    el.style.display = 'none';
}
function encode_utf8(s) {
  //return unescape(encodeURIComponent(s));
  return encodeURIComponent(s);
}

function decode_utf8(s) {
  //return decodeURIComponent(escape(s));
  return decodeURIComponent(s);
}

function setSelectImage(selid,imgid) {
    var oemid=$('#'+selid).val();
    //alert(oemid);
    if (oemid=="") $('#'+imgid).attr("src",""); 
    else $('#'+imgid).attr("src","image/"+oemid+".png");
    //var img = document.getElementById(imgid);
    //img.src = "image/"+oemid+".png";
}
function checkDate(date){
	//var pattern = new RegExp("^[0-9]{4}-[0-9]{2}-[0-9]{2}$");
	//if (pattern.test(date)){
	var d;
	  if ((d = new Date(date))|0)
	    return true; //return d.toISOString().slice(0,10) == date;
  //}
  return false;
}
</script>

</body>
</html>