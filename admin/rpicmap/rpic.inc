<?php
/****************
 * Validated on Feb-7,2018,
 * include file for map plugin
 * Fix T05 NoShape issue parseMap2Array/ parseMap2Marker
 * add rtrim to K01 address
 * fix T04 MapKey bug        
 *Writer: JinHo, Chang
*****************/

define("TXT_NEW","新增");
define("TXT_UPDATE","修改");
define("TXT_DELETE","刪除");
define("TXT_CANCEL","取消");
define("TXT_SUCCESS","成功");
define("TXT_FAIL","失敗");
define("TXT_GISLIST","開工列表");
define("TXT_NOTE","註解");
define("TXT_GIS_STATUS","地圖公開狀態");
define("TXT_GIS_ENABLE","開工");
define("TXT_GIS_DISABLE","收工");
define("TXT_BIND_ACCOUNT","攝影機所屬帳號");
define("TXT_CAMERA","攝影機");
define("TXT_GIS_OPEN","公開");
define("TXT_GIS_CLOSE","非公開");
define("TXT_ADMIN_ACCOUNT","編號");
$GISMAP_TEXT = [
"N99" =>array("工地編號","專案名稱","施工單位","地址","施工廠商","廠商電話",""),//0ACNO 1PURP 2AP_NAME 3DIGADD 4TCNAME 5TC_TEL 6APPMODE
"X02" =>array("工地編號","專案名稱","施工單位","地址","施工廠商","廠商電話",""), //租賃影像開工
"T04" =>array("核備證號","挖掘目的","申請單位","施工地點","施工廠商","施工人員電話","通報類別"),
"T05" =>array("核備證號","案件名稱","施工單位","挖掘地點","施工廠商","包商電話","通報類別"),
"K01" =>array("核備證號","案件名稱","施工單位","挖掘地點","施工廠商","包商電話","通報類別"),
"C13" =>array("工程會議編號","工程名稱","工程單位","現場轄區單位","承攬商","承攬商電話","作業類型")
];
if (isset($oem)){
  if (is_null($GISMAP_TEXT[$oem])){
  define("TXT_ACNO",$GISMAP_TEXT['N99'][0]);
  define("TXT_PURP",$GISMAP_TEXT['N99'][1]);
  define("TXT_APNAME",$GISMAP_TEXT['N99'][2]);
  define("TXT_DIGADD",$GISMAP_TEXT['N99'][3]);
  define("TXT_TCNAME",$GISMAP_TEXT['N99'][4]);
  define("TXT_TC_TEL",$GISMAP_TEXT['N99'][5]);
  }else{
  define("TXT_ACNO",$GISMAP_TEXT[$oem][0]);
  define("TXT_PURP",$GISMAP_TEXT[$oem][1]);
  define("TXT_APNAME",$GISMAP_TEXT[$oem][2]);
  define("TXT_DIGADD",$GISMAP_TEXT[$oem][3]);
  define("TXT_TCNAME",$GISMAP_TEXT[$oem][4]);
  define("TXT_TC_TEL",$GISMAP_TEXT[$oem][5]);
  if ($GISMAP_TEXT[$oem][6]!="") 
    define("TXT_APPMODE",$GISMAP_TEXT[$oem][6]); //if (defined('TXT_APPMODE'))
  if  ($oem=="C13")
    define("TXT_TC_TEL2","工地負責人電話"); //if (defined('TXT_TC_TEL2'))
  }
}

define("PAGE_LIMIT",20);

//remove private map access ex:C13
$GIS_Info = [
//"T04"=>"http://dig.nco.taipei/TpdigR.net/APP/GetAppWork.ashx?userid=rpicapi&passwd=123456",
"T04"=>"http://dig.nco.taipei/TpdigR.net/APP/GetAppWork.ashx?userid=rpicapi&key=", //getKey
"T05"=>"http://epark1.tycg.gov.tw/TycDigOutWebService/DB_WebService.asmx/GetTodayCase_filter?appkey=zid6j5m9id",
"K01"=>"http://pipegis.kcg.gov.tw/webui/pipeline/api/report/getConstructionList?Query.menuId=C01&Query.userId=tdi-megasys&Query.verifyKey=",  //date yyyy/mm/dd
"X02"=>"https://xpress.megasys.com.tw:8080/plugin/rpic/rpic_getdblist.php?key=KZo3i6UJbKd0bb6B5Suv&oemid=X02",
"T06"=>"https://xpress.megasys.com.tw:8080/plugin/rpic/rpic_getdblist.php?key=KZo3i6UJbKd0bb6B5Suv&oemid=T06",
//"C13"=>"https://engeye.chimei.com.tw:8080/plugin/rpic/rpic_getdblist.php?key=KZo3i6UJbKd0bb6B5Suv&oemid=C13",
"N99"=>"http://workeyemap.megasys.com.tw/map/rpic_getList.php?userid=rpic&passwd=KZo3i6UJbKd0bb6B5Suv",
"DEMO" => "https://xpress.megasys.com.tw:8080/plugin/rpic/rpic_getdblist.php?key=KZo3i6UJbKd0bb6B5Suv&oemid=DEMO"
];
$GIS_Info_Key = [
"T04"=>"http://dig.nco.taipei/TpdigR.net/Service/GetTpdigKey.ashx?value=123456",
"K01"=>"date('Y/m/d')"
];
//local admin access need to transform to 127.0.0.1
$GIS_Info_Local = [
"T06"=>"https://192.168.1.200:80/plugin/rpic/rpic_getdblist.php?key=KZo3i6UJbKd0bb6B5Suv&oemid=T06",
"X02"=>"https://192.168.1.200:80/plugin/rpic/rpic_getdblist.php?key=KZo3i6UJbKd0bb6B5Suv&oemid=X02",
"C13"=>"https://127.0.0.1:8080/plugin/rpic/rpic_getdblist.php?key=KZo3i6UJbKd0bb6B5Suv&oemid=C13",
"DEMO" => "https://192.168.1.200:80/plugin/rpic/rpic_getdblist.php?key=KZo3i6UJbKd0bb6B5Suv&oemid=DEMO"
];
$GIS_MAP = [
"T06"=>"http://workeyemap.megasys.com.tw/map/rpic_map.php?key=KZo3i6UJbKd0bb6B5Suv",
"C13"=>"http://engeye.chimei.com.tw/map/rpic_map.php?key=KZo3i6UJbKd0bb6B5Suv"
];
$GIS_FILE = [
"T04"=>"/var/tmp/T04.map",
"T05"=>"/var/tmp/T05.map",
"T06"=>"/var/tmp/T06.map",
"K01"=>"/var/tmp/K01.map",
"X02"=>"/var/tmp/X02.map",
"C13"=>"/var/tmp/C13.map",
"DEMO"=>"/var/tmp/demo.map",
"N99"=>"/var/tmp/N99.map"
];

$GISAPPMODE["T04"]["0"] = "挖掘施工";
$GISAPPMODE["T04"]["1"] = "1";
$GISAPPMODE["T04"]["B"] = "建案復舊";
$GISAPPMODE["T04"]["2"] = "逾時";
$GISAPPMODE["T04"]["3"] = "銑鋪";
$GISAPPMODE["T04"]["4"] = "搶修";
$GISAPPMODE["T04"]["5"] = "道路維護";
$GISAPPMODE["T04"]["6"] = "人手孔施工";
//$GISAPPMODE["T05"] = TypeDetail
$GISAPPMODE["K01"][1] = "施工中通報";
$GISAPPMODE["K01"][2] = "管障通報";
$GISAPPMODE["K01"][3] = "非路證人手孔下地通報";
$GISAPPMODE["K01"][4] = "搶修通報";

$GISAPPMODE["C13"]=array("一般施工","動火作業","侷限作業","危險管路、泵浦、火線作業","屋頂作業","複合作業");
$GISAPPMODE_COLOR=[ //default 000000
 "T04挖掘施工"=>"#00FF00", //T04
 "T04逾時"=>"#FFFF00",    //ISDELAY
 "T04銑鋪"=>"#1BFEFE",
 "T04搶修"=>"#FF0000",
 "T04道路維護"=>"#D1A2FE",
 "T04人手孔施工"=>"#FEA21F",
 "T04建案復舊"=>"#7DA3FD",
 "T05其它"=>"#1E5BC8",//T05
 "T05抽換"=>"#23924F",
 "T05搶修"=>"#D3597D",
 "T05新設"=>"#92DA2F",
 "T05緊急性孔蓋啟閉"=>"#EA603C",
 "T05遷移"=>"#FF82CA",
 "T05檢修"=>"#7D62BC",
 "T05擴充"=>"#6E92CB",
 "T05孔蓋昇降"=>"#1ECDE2",
 "T05孔蓋啟閉"=>"#F1B000",
 //"T05寬頻" //?new
 //"T05區塊"
"K01施工中通報"=>"#81BF11", //K01
"K01管障通報"=>"#D11963",
"K01非路證人手孔下地通報"=>"#F36337",
"K01搶修通報"=>"#FF0000",
  "C13一般施工"=>"#00FF00", //lime
  "C13動火作業"=>"#FF0000", //red
  "C13侷限作業"=>"#FFFF00", //yellow
  "C13危險管路、泵浦、火線作業"=>"#974AF6",
  "C13屋頂作業"=>"#2200FF",
  "C13複合作業"=>"#B9643A"
];

$GISMAP_FIELD = [
"TITLE" =>array("編號","名稱","申請單位","地址","廠商名稱","廠商電話","經度","緯度","錄影機數目","通報類別","連結","user_name","user_pwd"),//0 1 2PURP 3ADDR 4 5TEL 6LAT 7LNG 8V 9APP 10URL 11:user_name 12:user_pwd
"T04" =>array("AC_NO","PURP","AP_NAME","DIGADD","TC_NA","TC_TL3","LAT","LON","VIDEONO","APPMODE","rpic.taipei","user_name","user_pwd"),
"T05" =>array("CaseID","ConstName","PPBName","SLocation","Factory","Factory_Man_Tel","Shape","Shape","","TypeDetail","https://rpic.tycg.gov.tw","user_name","user_pwd"),
"K01" =>array("pmtNo","prjName","unitName","address","contractor","contractorPhone","lat","lng","videoNo","appMode","https://kreac.kcg.gov.tw","user_name","user_pwd"),
"X02" =>array("ACNO","PURP","APNAME","DIGADD","TCNAME","TC_TEL","LAT","LNG","VIDEONO","APPMODE","https://xpress.megasys.com.tw","user_name","user_pwd"),
"C13" =>array("ACNO","PURP","APNAME","DIGADD","TCNAME","TC_TEL","LAT","LNG","VIDEONO","APPMODE","https://engeye.chimei.com.tw","user_name","user_pwd"),
"N99" =>array("ACNO","PURP","APNAME","DIGADD","TCNAME","TC_TEL","LAT","LNG","VIDEONO","APPMODE","URL","user_name","user_pwd"),
"DEMO" =>array("ACNO","PURP","APNAME","DIGADD","TCNAME","TC_TEL","LAT","LNG","VIDEONO","APPMODE","URL","user_name","user_pwd") //same as N99
];

$RPICAPP_USER_PWD = [
//oem=> baseurl, (1)pwd, loginpage, mappage, default LAT/LNG, (5)GIS name
"T04"=>array("https://rpic.taipei/","Ea9M7gOu586UQaOtXJ3e6f51",
"https://rpic.taipei/backstage_login_tpe.php?user_pwd=Ea9M7gOu586UQaOtXJ3e6f51&user_name=",
"https://rpic.taipei/backstage_mobile.php?",[25.02672012,121.51973248],"台北市道路管線中心"),

"T05"=>array("https://rpic.tycg.gov.tw/","tN8V8bMTtuKycj7BNW2Esp8p",
"https://rpic.tycg.gov.tw/backstage_login_ty.php?user_pwd=tN8V8bMTtuKycj7BNW2Esp8p&user_name=",
"https://rpic.tycg.gov.tw/backstage_mobile.php?",[25.00126656,121.29133701],"桃園市道路挖掘服務中心"),

"K01"=>array("https://kreac.kcg.gov.tw/","ydEP6Ug6uBzWTXU28gfSV3hu",
"https://kreac.kcg.gov.tw/backstage_login_rpic.php?user_pwd=ydEP6Ug6uBzWTXU28gfSV3hu&user_name=",
"https://kreac.kcg.gov.tw/backstage_mobile.php?",[22.63052969,120.31001329],"高雄市道路挖掘管理中心"),

"X02"=>array("https://xpress.megasys.com.tw/","ydEP6Ug6uBzWTXU28gfSV3hu",
"https://xpress.megasys.com.tw/backstage_login_rpic.php?user_pwd=ydEP6Ug6uBzWTXU28gfSV3hu&user_name=",
"https://xpress.megasys.com.tw/backstage_mobile.php?",[25.04571448, 121.46801949],"MegasysXpress"),

"T06"=>array("https://xpress.megasys.com.tw/","ydEP6Ug6uBzWTXU28gfSV3hu",
"https://xpress.megasys.com.tw/backstage_login_rpic.php?user_pwd=ydEP6Ug6uBzWTXU28gfSV3hu&user_name=",
"https://xpress.megasys.com.tw/backstage_mobile.php?",[25.04571448, 121.46801949],"即時工務影像"),

"C13"=>array("https://engeye.chimei.com.tw/","Cqjf6NE2R5xY5unCMXVDqqmP",
"https://engeye.chimei.com.tw/backstage_login_rpic.php?user_pwd=Cqjf6NE2R5xY5unCMXVDqqmP&user_name=",
"https://engeye.chimei.com.tw/backstage_mobile.php?",[22.92677687, 120.24321556],"奇美工務影像"),

"N99"=>array("","KZo3i6UJbKd0bb6B5Suv"),
"RPIC"=>array("","ydEP6Ug6uBzWTXU28gfSV3hu","backstage_login_rpic.php?user_pwd=&user_name=","backstage_mobile.php?")
];

function loginURL($type,$baseurl,$user_name,$user_pwd)
{
  if ($type=="VLC") {
    return $baseurl . "backstage_mobile.php?user_name=".$user_name."&user_pwd=".$user_pwd;
  }else if ($type=="JAVA"){ //T04/T05 URL is different
    return $baseurl . "backstage_login_rpic.php?user_name=".$user_name."&user_pwd=".$user_pwd;
  }
  return "";
}
function isMapAvail($oemid)
{
  global $GIS_FILE;
  $bMapAvail=false;
  foreach ($GIS_FILE as $key=>$path){
    if ($oemid == $key ) $bMapAvail = true;
  }
  return $bMapAvail;
}
function getMapKey($oemid)
{
  global $GIS_Info_Key;
  if ($oemid=="T04"){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$GIS_Info_Key[$oemid]);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $result=curl_exec($ch);
    curl_close($ch);
    return $result;
  }else if ($oemid=="K01"){
    return date("Y/m/d");
  }
  return "";
}
/*
preg_match_all('/".*?"|\'.*?\'/', $str, $matches);
"   -> Match a double quote
.*  -> Match zero or more of any character
?"  -> Match as a non-greedy match until the next double quote
|   -> or
\'  -> Match a single quote
.*  -> Match zero or more of any character
?\' -> Match as non-greedy match until the next single quote.
preg_match('/"([^"]+)"/', $ref, $m)
Double-quote / At least one non-double-quote /Double-quote
The brackets around the [^"]+ means that that portion will be returned as a separate group
*/
function getOEMID(){
  if (file_exists("/var/www/qlync_admin/doc/config.php")){
    $ref=exec("grep '\$oem' /var/www/qlync_admin/doc/config.php");
    //get m array by one double quote ==> $oem="X02";
    $m= explode('"',$ref);
    //if (preg_match('/"([^"]+)"/', $ref, $m)) { //m[1]
    if (!is_null($m[1]))
      return $m[1];
      
  }else if (file_exists("/var/www/SAT-CLOUDNVR/include/index_title.php")){
    $ref=exec("grep \"\$oem_style_list\['oem\" /var/www/SAT-CLOUDNVR/include/index_title.php");
    //export m array by single quote ==> $oem_style_list['oem_id'] = 'X02';
    $m= explode("'",$ref);
    //if (preg_match("/\'([^\']+)\'/", $ref, $m)) { //m[1] return oem_id
    if (!is_null($m[3]))
      return $m[3];

  }
  return "";
}

function getMap($oemid,&$result)
{
  global $GIS_Info,$GIS_Info_Local;
  $ch = curl_init();
  $site_oemid=getOEMID();
  $url="";
  //if get map from local db, use local instead
  if ($site_oemid=="") die( "site oemid NA!!"); 
  if ($site_oemid == $oemid) $url = $GIS_Info_Local[$oemid];

  //if no local, use remote URL     
  if ($url == "")  $url = $GIS_Info[$oemid];

  if ($url == ""){ //bypass private map
    if ($oemid!="C13") die( "No such oemid");
    return;
  }

  if (($oemid=="K01")or ($oemid=="T04")) $url.=getMapKey($oemid);
  curl_setopt($ch, CURLOPT_URL,$url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);//bypass SSL
  //curl_setopt($ch, CURLOPT_SSL_VERIFYSTATUS, false);//bypass SSL
  curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);//bypass SSL
  curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,3); //timeout
  curl_setopt($ch, CURLOPT_TIMEOUT, 3); //timeout in seconds
  curl_setopt($ch, 156, 2500); //CURLOPT_CONNECTTIMEOUT_MS should be 156
  
  $result=curl_exec($ch);
  curl_close($ch);
}
function parseMap($oemid,$result,&$content)
{//return content
  if ($oemid=="T05"){
    $content = json_decode(simplexml_load_string($result), TRUE);
  }else if ($oemid=="K01"){
    $content = json_decode($result, TRUE);
    $content = $content['cnstList'];
  }else if ($oemid=="T04"){
    $content = json_decode($result, TRUE);
  }else{ //N99, X02, T06, DEMO
    $content = json_decode($result, TRUE);
    //echo "No such oemid";
  }
  
}
function getMapFile($oemid,&$result)
{
  global $GIS_FILE;
  if (file_exists($GIS_FILE[$oemid]))
   $result = file_get_contents($GIS_FILE[$oemid]);
  else $result=NULL;
}

function mergeMapN99()
{
  global $GIS_FILE;
  $data=array();
  foreach ($GIS_FILE as $key=>$path) //merge to N99
  {
    if ($key!="N99"){
      $result=array();
      getMapFile($key,$result);
      if (!empty($result)){
        $content=array();
        parseMap($key,$result, $content);
        parseMap2Array($key,$content, $data);
      }
    }
  }
  if (!empty($data))
    writeMapFile($GIS_FILE['N99'],json_encode($data)); //write source to file
}

function writeAllMapFile()
{
  global $GIS_FILE;
  foreach ($GIS_FILE as $key=>$path) //get new map
  {
    if ($key!="N99"){
      $result=array();
      getMap($key,$result);
      writeMapFile($GIS_FILE[$key],$result);
    }
  }
  mergeMapN99();
}
function writeMapFile($path, $data)
{
  if (!file_exists($path)){
    exec(" echo '' > ".$path);
    chmod($path,0777);
  }
  $fplog=fopen($path,"w");
  $fwrite = fwrite($fplog,$data); 
  fclose($fplog);
}

function parseMap2Array($oemid, $content, &$data)
{
  global $GISAPPMODE,$RPICAPP_USER_PWD;
  $myGIS = array();
  foreach ($content as $item){
    if ($oemid=="T04"){
      //empty($var1)? $var2 : $var1; //if var1 empty, show var2
      $myGIS = array("OEM_ID" => $oemid,
      "ACNO" => $item['AC_NO'],
      "PURP" => $item['PURP'],
      "APNAME" => $item['AP_NAME'],
      "DIGADD" => $item['DIGADD'],
      "TCNAME" => $item['TC_NA'],
      "TC_TEL" => empty($item['TC_TL3'])? $item['TC_TL'] : $item['TC_TL3'],//$item['TC_TL3'], 
      "LAT" => $item['LAT'],
      "LNG" => $item['LON'],
      "VIDEONO" => $item['VIDEONO'],
      "APPMODE" => empty($GISAPPMODE[$oemid][$item['APPMODE']])? "NA":$GISAPPMODE[$oemid][$item['APPMODE']], //$GISAPPMODE[$oemid][$item['APPMODE']],
      "URL" => $RPICAPP_USER_PWD[$oemid][0],
      "user_name" => $item['AC_NO'],
      "user_pwd" => $RPICAPP_USER_PWD[$oemid][1],
      );
      array_push($data,$myGIS);
    }else if ($oemid=="T05"){
      if (strpos($item['Shape'], ";") === FALSE){  //NOT FOUND,error detection
        $s2[1]=$RPICAPP_USER_PWD[$oemid][4][0];
        $s2[0]=$RPICAPP_USER_PWD[$oemid][4][1];
      }else{
       $s1=explode(";",$item['Shape']); //split to multiple lat/long
       $s2=explode(",",$s1[0]);
      }
      $myGIS = array("OEM_ID" => $oemid,
      "ACNO" => $item['CaseID'],
      "PURP" => $item['ConstName'],
      "APNAME" => $item['PPBName'],
      "DIGADD" => $item['SLocation'],
      "TCNAME" => $item['Factory'],
      "TC_TEL" => $item['Factory_Man_Tel'], 
      "LAT" => $s2[1],
      "LNG" => $s2[0],
      "VIDEONO" => 1,
      "APPMODE" => $item['TypeDetail'],
      "URL" => $RPICAPP_USER_PWD[$oemid][0],
      "user_name" => $item['CaseID'],
      "user_pwd" => $RPICAPP_USER_PWD[$oemid][1],
      );
      array_push($data,$myGIS);
    }else if ($oemid=="K01"){
        foreach ($item['roadList'][0]['digRegionList'] as $subitem){ 
          $myGIS = array("OEM_ID" => $oemid,
          "ACNO" => $item['pmtNo'],
          "PURP" => $item['prjName'],
          "APNAME" => $item['unitName'],
          "DIGADD" => $item['roadList'][0]['address'],
          "TCNAME" => $item['contractor'],
          "TC_TEL" => $item['contractorPhone'], 
          "LAT" => $subitem['lat'],
          "LNG" => $subitem['lng'],
          "VIDEONO" => $subitem['videoNo'],
          "APPMODE" => $GISAPPMODE[$oemid][$item['appMode']],
          "URL" => $RPICAPP_USER_PWD[$oemid][0],
          "user_name" => $item['pmtNo'],
          "user_pwd" => $RPICAPP_USER_PWD[$oemid][1],
          );
          array_push($data,$myGIS);
        }
    }else{ 
    // if (($oemid=="X02")  or ($oemid=="T06") or ($oemid=="N99") or ($oemid=="DEMO")) {
      if (($oemid=="X02")  or ($oemid=="T06")) $myurl = $RPICAPP_USER_PWD[$oemid][0];
      else if ( ($oemid=="N99") or ($oemid=="DEMO")) $myurl = $item['URL']; 
      else $myurl = $RPICAPP_USER_PWD[$oemid][0];; //C13

      if (($oemid=="X02") or ($oemid=="T06")) $myoemid = $oemid;
      else if (($oemid=="N99") or ($oemid=="DEMO")) $myoemid = $item['OEM_ID'];
      else  $myoemid = $oemid;; //C13

      $myGIS = array("OEM_ID" => $myoemid,
      "ACNO" => $item['ACNO'],
      "PURP" => $item['PURP'],
      "APNAME" => $item['APNAME'],
      "DIGADD" => $item['DIGADD'],
      "TCNAME" => $item['TCNAME'],
      "TC_TEL" => $item['TC_TEL'], 
      "LAT" => $item['LAT'],
      "LNG" => $item['LNG'],
      "VIDEONO" => $item['VIDEONO'],
      "APPMODE" => $item['APPMODE'],
      "URL" => $myurl,
      "user_name" => $item['user_name'],
      "user_pwd" => $item['user_pwd']
      );
      array_push($data,$myGIS);
    }//if oemid
  }//each item
}


function parseMap2Marker($oemid, $content, &$data)
{//add auto correct GPS value Sep-25/2017
  global $GISMAP_FIELD,$RPICAPP_USER_PWD,$GISAPPMODE;
  global $GISMAP_TEXT;
  foreach ($content as $item){
    if ($oemid=="T04"){
//      $tmp = $GISMAP_FIELD['TITLE'][0].": ".$item['AC_NO']."; ".$GISMAP_FIELD['TITLE'][9].": ".$GISAPPMODE[$oemid][$item['APPMODE']].";<br>".$GISMAP_FIELD['TITLE'][1].": ".$item['PURP'].";<br>".$GISMAP_FIELD['TITLE'][2].": ".$item['AP_NAME'].";<br>".$GISMAP_FIELD['TITLE'][3].": ".$item['DIGADD'].";<br>".$GISMAP_FIELD['TITLE'][4].": ".$item['TC_NA'].";<br>".$GISMAP_FIELD['TITLE'][5].": ".$item['TC_TL3'].";<br>";
      $tmp = $GISMAP_TEXT[$oemid][0].": ".$item['AC_NO']."; ".$GISMAP_TEXT[$oemid][6].": ".$GISAPPMODE[$oemid][$item['APPMODE']].";<br>".$GISMAP_TEXT[$oemid][1].": ".$item['PURP'].";<br>".$GISMAP_TEXT[$oemid][2].": ".$item['AP_NAME'].";<br>".$GISMAP_TEXT[$oemid][3].": ".$item['DIGADD'].";<br>".$GISMAP_TEXT[$oemid][4].": ".$item['TC_NA'].";<br>".$GISMAP_TEXT[$oemid][5].": ".$item['TC_TL3'].";<br>";

      $tmp = str_replace(array('.', ' ', "\n", "\t", "\r"), '', $tmp); //remove carriage return
      if ($item['VIDEONO'] > 0){
        $tmp.="<a href=\"".$RPICAPP_USER_PWD[$oemid][2].$item['AC_NO']."\" target=\"_blank\">LiveJava</a>&nbsp;";
       $tmp.="<a href=\"".loginURL("VLC",$RPICAPP_USER_PWD[$oemid][0],$item['AC_NO'],$RPICAPP_USER_PWD[$oemid][1])."\"  onclick=\"window.open(this.href, \'\', \'width=450,height=500\'); return false;\">LiveVLC</a>";
      }
      //check GIS value
      if (!is_float($item['LAT'])){
        $item['LAT'] = $RPICAPP_USER_PWD[$oemid][4][0];
        $tmp .= "<br>*LAT was incorrect.";
      }
      if (!is_float($item['LON'])){
        $item['LON'] = $RPICAPP_USER_PWD[$oemid][4][1];
        $tmp .= "<br>*LON. was incorrect.";
      }  
      $myGIS = array("OEM_ID" => $oemid,
      "ACNO" => $item['AC_NO'],
      "LAT" => $item['LAT'],
      "LNG" => $item['LON'],
      "INFO" => $tmp
      );
      array_push($data,$myGIS);
    }else if ($oemid=="T05"){
      if (strpos($item['Shape'], ";") === FALSE){  //NOT FOUND,error detection
        $s2[1]=$RPICAPP_USER_PWD[$oemid][4][0];
        $s2[0]=$RPICAPP_USER_PWD[$oemid][4][1];
      }else{
        $s1=explode(";",$item['Shape']); //split to multiple lat/long
        $s2=explode(",",$s1[0]);
      }
      //$tmp = $GISMAP_FIELD['TITLE'][0].": ".$item['CaseID']."; ".$GISMAP_FIELD['TITLE'][9].": ".$item['TypeDetail'].";<br>".$GISMAP_FIELD['TITLE'][1].": ".$item['ConstName'].";<br>".$GISMAP_FIELD['TITLE'][2].": ".$item['PPBName'].";<br>".$GISMAP_FIELD['TITLE'][3].": ".$item['SLocation'].";<br>".$GISMAP_FIELD['TITLE'][4].": ".$item['Factory'].";<br>".$GISMAP_FIELD['TITLE'][5].": ".$item['Factory_Man_Tel'].";<br>";
      $tmp = $GISMAP_TEXT[$oemid][0].": ".$item['CaseID']."; ".$GISMAP_TEXT[$oemid][6].": ".$item['TypeDetail'].";<br>".$GISMAP_TEXT[$oemid][1].": ".$item['ConstName'].";<br>".$GISMAP_TEXT[$oemid][2].": ".$item['PPBName'].";<br>".$GISMAP_TEXT[$oemid][3].": ".$item['SLocation'].";<br>".$GISMAP_TEXT[$oemid][4].": ".$item['Factory'].";<br>".$GISMAP_TEXT[$oemid][5].": ".$item['Factory_Man_Tel'].";<br>";
      $tmp = str_replace(array('.', ' ', "\n", "\t", "\r"), '', $tmp); //remove carriage return
      $tmp.="<a href=\"".$RPICAPP_USER_PWD[$oemid][2].$item['CaseID']."\" target=\"_blank\">LiveJava</a>&nbsp;";
      $tmp.="<a href=\"".loginURL("VLC",$RPICAPP_USER_PWD[$oemid][0],$item['CaseID'],$RPICAPP_USER_PWD[$oemid][1])."\"  onclick=\"window.open(this.href, \'\', \'width=450,height=500\'); return false;\">LiveVLC</a>";
      //check GIS value
      if (!is_float(floatval($s2[0]))){
        $s2[1] = $RPICAPP_USER_PWD[$oemid][4][0];
        $s2[0] = $RPICAPP_USER_PWD[$oemid][4][1];
        $tmp .= "<br>*LAT/LON was incorrect.";
      }

      $myGIS = array("OEM_ID" => $oemid,
      "ACNO" => $item['CaseID'],
      "LAT" => $s2[1],
      "LNG" => $s2[0],
      "INFO" => $tmp
      );
      array_push($data,$myGIS);

    }else if ($oemid=="K01"){
        foreach ($item['roadList'][0]['digRegionList'] as $subitem){ 
          //$tmp = $GISMAP_FIELD['TITLE'][0].": ".$item['pmtNo']."; (".$subitem['digCaseRegion'].") ".$GISMAP_FIELD['TITLE'][9].": ".$GISAPPMODE[$oemid][$item['appMode']].";<br>".$GISMAP_FIELD['TITLE'][1].": ".$item['prjName'].";<br>".$GISMAP_FIELD['TITLE'][2].": ".$item['unitName'].";<br>".$GISMAP_FIELD['TITLE'][3].": ".rtrim($item['roadList'][0]['address'],"\n\r").";<br>".$GISMAP_FIELD['TITLE'][4].": ".$item['contractor'].";<br>".$GISMAP_FIELD['TITLE'][5].": ".$item['contractorPhone'].";<br>";
          $tmp = $GISMAP_TEXT[$oemid][0].": ".$item['pmtNo']."; (".$subitem['digCaseRegion'].") ".$GISMAP_TEXT[$oemid][6].": ".$GISAPPMODE[$oemid][$item['appMode']].";<br>".$GISMAP_TEXT[$oemid][1].": ".$item['prjName'].";<br>".$GISMAP_TEXT[$oemid][2].": ".$item['unitName'].";<br>".$GISMAP_TEXT[$oemid][3].": ".rtrim($item['roadList'][0]['address'],"\n\r").";<br>".$GISMAP_TEXT[$oemid][4].": ".$item['contractor'].";<br>".$GISMAP_TEXT[$oemid][5].": ".$item['contractorPhone'].";<br>";
          $tmp = str_replace(array('.', ' ', "\n", "\t", "\r"), '', $tmp); //remove carriage return
          if ($subitem['videoNo'] > 0){
              //$tmp.="<a href=\"".$RPICAPP_USER_PWD[$oemid][2].$item['pmtNo']."\" target=\"_blank\">LiveJava</a>&nbsp;";
              $tmp.="<a href=\"".loginURL("JAVA",$RPICAPP_USER_PWD[$oemid][0],$item['pmtNo'],$RPICAPP_USER_PWD[$oemid][1])."\" target=\"_blank\">LiveJava</a>&nbsp;";
              $tmp.="<a href=\"".loginURL("VLC",$RPICAPP_USER_PWD[$oemid][0],$item['pmtNo'],$RPICAPP_USER_PWD[$oemid][1])."\"   onclick=\"window.open(this.href, \'\', \'width=450,height=500\'); return false;\">LiveVLC</a>";

          }
        //check GIS value
        if (!is_float($subitem['lat'])){
          $subitem['lat'] = $RPICAPP_USER_PWD[$oemid][4][0];
          $tmp .= "<br>*LAT was incorrect.";
        }
        if (!is_float($subitem['lng'])){
          $subitem['lng'] = $RPICAPP_USER_PWD[$oemid][4][1];
          $tmp .= "<br>*LON. was incorrect.";
        }  
          $myGIS = array("OEM_ID" => $oemid,
          "ACNO" => $item['pmtNo'],
          "LAT" => $subitem['lat'],
          "LNG" => $subitem['lng'],
          "INFO" => $tmp
          );
          array_push($data,$myGIS);
        }//foreach
    }else{ //if (($oemid=="N99") or ($oemid=="X02")  or ($oemid=="T06") or ($oemid=="DEMO") ){
      
      if (($oemid=="N99") or ($oemid=="DEMO")) $myoemid = $item['OEM_ID'];
      //else if (($oemid=="X02")  or ($oemid=="T06")) $myoemid = $oemid;
      else  $myoemid = $oemid;
 
      //$tmp = $GISMAP_FIELD['TITLE'][0].": ".$item['ACNO']."; ".$GISMAP_FIELD['TITLE'][9].": ".$item['APPMODE'].";<br>".$GISMAP_FIELD['TITLE'][1].": ".$item['PURP'].";<br>".$GISMAP_FIELD['TITLE'][2].": ".$item['APNAME'].";<br>".$GISMAP_FIELD['TITLE'][3].": ".$item['DIGADD'].";<br>".$GISMAP_FIELD['TITLE'][4].": ".$item['TCNAME'].";<br>".$GISMAP_FIELD['TITLE'][5].": ".$item['TC_TEL'].";<br>";
      $tmp = $GISMAP_TEXT[$myoemid][0].": ".$item['ACNO']."; ".$GISMAP_TEXT[$myoemid][6].": ".$item['APPMODE'].";<br>".$GISMAP_TEXT[$myoemid][1].": ".$item['PURP'].";<br>".$GISMAP_TEXT[$myoemid][2].": ".$item['APNAME'].";<br>".$GISMAP_TEXT[$myoemid][3].": ".$item['DIGADD'].";<br>".$GISMAP_TEXT[$myoemid][4].": ".$item['TCNAME'].";<br>".$GISMAP_TEXT[$myoemid][5].": ".$item['TC_TEL'].";<br>";
      $tmp = str_replace(array('.', ' ', "\n", "\t", "\r"), '', $tmp); //remove carriage return
      if ($item['VIDEONO'] > 0){
        if (($oemid=="X02") or ($oemid=="T06")){
            $jurl =loginURL("JAVA",$RPICAPP_USER_PWD[$myoemid][0],$item['user_name'],$item['user_pwd']);
            $vurl =loginURL("VLC",$RPICAPP_USER_PWD[$myoemid][0],$item['user_name'],$item['user_pwd']);
        }else{ //N99, DEMO
          //T06/X02 use map URL
          if (($myoemid=="X02") or ($myoemid=="T06"))
              $jurl =loginURL("JAVA",$item['URL'],$item['user_name'],$item['user_pwd']);
          else  $jurl = $RPICAPP_USER_PWD[$myoemid][2].$item['user_name'];

          $vurl =loginURL("VLC",$item['URL'],$item['user_name'],$item['user_pwd'])."&share";
        }
        $tmp.="<a href=\"".$jurl."\" target=\"_blank\">LiveJava</a>&nbsp;";
        $tmp.="<a href=\"".$vurl."\" onclick=\"window.open(this.href, \'\', \'width=450,height=500\'); return false;\">LiveVLC</a>";
      }
 
      $myGIS = array("OEM_ID" => $myoemid,
      "ACNO" => $item['ACNO'],
      "LAT" => $item['LAT'],
      "LNG" => $item['LNG'],
      "INFO" => $tmp
      );
      array_push($data,$myGIS);
    }//else
  }
}

function getN99Marker($data)
{
  if ($data==NULL) return;

  foreach ($data as $arr){
    $html.="['".$arr['OEM_ID']."','".$arr['ACNO']."',".$arr['LAT'].",".$arr['LNG'].",'".$arr['INFO']."'],\n";
  }
  $html=rtrim( $html,",\n");
  echo $html;
}
function getMarker($data)
{
  if ($data==NULL) return;
  $myoem = OEM_ID;
  foreach ($data as $key=>$arr){
    if ((OEM_ID=="DEMO") or (OEM_ID=="N99")) $myoem = $arr['OEM_ID'];
    $html.="['{$myoem}','".$key."',".$arr['LAT'].",".$arr['LNG'].",'".$arr['INFO']."'],\n";
  }
  $html=rtrim( $html,",\n");
  echo $html;
}
function isMobile()
{
  $useragent=$_SERVER['HTTP_USER_AGENT'];
if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)))
  return true;
else if (strstr($useragent,'iPhone') || strstr($useragent,'iPad') )
  return true;
else return false;
}
function printMobileMeta()
{
  $meta = "<meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0, user-scalable=yes, minimum-scale=1.0, maximum-scale=2.0\" />";
  echo $meta;
}
function printRefreshMeta($iCountDown){
  $currentURL = ($_SERVER['HTTPS']) ? "https://":"http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
  echo "<meta http-equiv=\"refresh\" content=\"{$iCountDown}; URL='{$currentURL}' \"/>";
}
function parse_user_agent( $u_agent = null ) {
  if(is_null($u_agent) && isset($_SERVER['HTTP_USER_AGENT'])) $u_agent = $_SERVER['HTTP_USER_AGENT'];

  $data = array(
    'platform' => null,
    'browser'  => null,
    'version'  => null,
  );

  if(!$u_agent) return $data;

  if( preg_match('/\((.*?)\)/im', $u_agent, $regs) ) {

    preg_match_all('/(?P<platform>Android|CrOS|iPhone|iPad|Linux|Macintosh|Windows(\ Phone\ OS)?|Silk|linux-gnu|BlackBerry|Nintendo\ (WiiU?|3DS)|Xbox)
      (?:\ [^;]*)?
      (?:;|$)/imx', $regs[1], $result, PREG_PATTERN_ORDER);

    $priority = array('Android', 'Xbox');
    $result['platform'] = array_unique($result['platform']);
    if( count($result['platform']) > 1 ) {
      if( $keys = array_intersect($priority, $result['platform']) ) {
        $data['platform'] = reset($keys);
      }else{
        $data['platform'] = $result['platform'][0];
      }
    }elseif(isset($result['platform'][0])){
      $data['platform'] = $result['platform'][0];
    }
  }

  if( $data['platform'] == 'linux-gnu' ) { $data['platform'] = 'Linux'; }
  if( $data['platform'] == 'CrOS' ) { $data['platform'] = 'Chrome OS'; }

  preg_match_all('%(?P<browser>Camino|Kindle(\ Fire\ Build)?|Firefox|Safari|MSIE|AppleWebKit|Chrome|IEMobile|Opera|Silk|Lynx|Version|Wget|curl|NintendoBrowser|PLAYSTATION\ \d+)
      (?:;?)
      (?:(?:[/ ])(?P<version>[0-9A-Z.]+)|/(?:[A-Z]*))%x',
  $u_agent, $result, PREG_PATTERN_ORDER);

  $key = 0;

  $data['browser'] = $result['browser'][0];
  $data['version'] = $result['version'][0];

  if( ($key = array_search( 'Kindle Fire Build', $result['browser'] )) !== false || ($key = array_search( 'Silk', $result['browser'] )) !== false ) {
    $data['browser']  = $result['browser'][$key] == 'Silk' ? 'Silk' : 'Kindle';
    $data['platform'] = 'Kindle Fire';
    if( !($data['version'] = $result['version'][$key]) || !is_numeric($data['version'][0]) ) {
      $data['version'] = $result['version'][array_search( 'Version', $result['browser'] )];
    }
  }elseif( ($key = array_search( 'NintendoBrowser', $result['browser'] )) !== false || $data['platform'] == 'Nintendo 3DS' ) {
    $data['browser']  = 'NintendoBrowser';
    $data['version']  = $result['version'][$key];
  }elseif( ($key = array_search( 'Kindle', $result['browser'] )) !== false ) {
    $data['browser']  = $result['browser'][$key];
    $data['platform'] = 'Kindle';
    $data['version']  = $result['version'][$key];
  }elseif( $result['browser'][0] == 'AppleWebKit' ) {
    if( ( $data['platform'] == 'Android' && !($key = 0) ) || $key = array_search( 'Chrome', $result['browser'] ) ) {
      $data['browser'] = 'Chrome';
      if( ($vkey = array_search( 'Version', $result['browser'] )) !== false ) { $key = $vkey; }
    }elseif( $data['platform'] == 'BlackBerry' ) {
      $data['browser'] = 'BlackBerry Browser';
      if( ($vkey = array_search( 'Version', $result['browser'] )) !== false ) { $key = $vkey; }
    }elseif( $key = array_search( 'Safari', $result['browser'] ) ) {
      $data['browser'] = 'Safari';
      if( ($vkey = array_search( 'Version', $result['browser'] )) !== false ) { $key = $vkey; }
    }

    $data['version'] = $result['version'][$key];
  }elseif( ($key = array_search( 'Opera', $result['browser'] )) !== false ) {
    $data['browser'] = $result['browser'][$key];
    $data['version'] = $result['version'][$key];
    if( ($key = array_search( 'Version', $result['browser'] )) !== false ) { $data['version'] = $result['version'][$key]; }
  }elseif( $result['browser'][0] == 'MSIE' ){
    if( $key = array_search( 'IEMobile', $result['browser'] ) ) {
      $data['browser'] = 'IEMobile';
    }else{
      $data['browser'] = 'MSIE';
      $key = 0;
    }
    $data['version'] = $result['version'][$key];
  }elseif( $key = array_search( 'PLAYSTATION 3', $result['browser'] ) !== false ) {
    $data['platform'] = 'PLAYSTATION 3';
    $data['browser']  = 'NetFront';
  }
  return $data;
}

function chkSourceFromMegasys(&$cip)//true / false
{
  $MegasysIP = [
  "118.163.90.31",
  "59.124.70.86",
  "59.124.70.90",
  "125.227.139.173",
  "125.227.139.174",
  "123.193.125.132",
  "61.216.61.162"
  ];
    if($_SERVER['HTTP_CLIENT_IP'] !="" )
        $client_ip = $_SERVER['HTTP_CLIENT_IP'];
    if($_SERVER['HTTP_X_FORWARDED_FOR']!=""  and $client_ip=="" ) 
        $client_ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    if($_SERVER['REMOTE_ADDR']!="" and $client_ip=="" )
        $client_ip = $_SERVER['REMOTE_ADDR'];
    for ($i=0;$i<sizeof($MegasysIP);$i++){
      if (strpos($client_ip, $MegasysIP[$i]) !== FALSE){
        $cip=$MegasysIP[$i];
        return true;
      }
    }
    return false;
}

function createAdminUser($email){
  //SCID002, AID2, ID06 = Monitoring Operator
  global $oem;
  //contact is empty
  if ($oem == "X02") $contact = $email;
  else if ($oem == "C13") $contact = "";
  if (getAdminUserID($email) > 0) return true;
  $sql="INSERT INTO qlync.account (Email,Contact, Password, id_admin_oem, CID, Status, SCID, AID,ID_03) VALUES 
  ('{$email}','{$contact}',ENCODE('{$email}','".substr($email,0,5)."'),'0','{$oem}','1','002','2','1')";
  sql($sql,$result,$num,0);
  //if ($result) checkAdminUser($email);
  return $result;
}
function getAdminUserID($email)
{
  $sql="select id from qlync.account where Email='{$email}'";
  sql($sql,$result,$num,0);
  fetch($arr,$result,0,0);
  if ($num > 0) return intval($arr['id']);
  return -1;
}
function removeAdminUser($email){
  $sql = "DELETE FROM qlync.account WHERE Email='{$email}'";
  sql($sql,$result,$num,0);
  //if ($result) checkAdminUser($email);
  return $result;
}
//UPDATE qlync.account SET ID_02 ='0', ID_03 ='1' where AID ='2'
function setDefaultAdminUser($email,$pwd=""){
  //SCID002, AID2, ID03 = Dealer Tech Support
  if (getAdminUserID($email) < 0) return false;
  if ($pwd=="") $pwd = $email; //default same as email
  $sql="UPDATE qlync.account SET Contact='{$email}',
  Password = ENCODE('{$pwd}','".substr($email,0,5)."'),
  ID_01 ='0',
  ID_02 ='0',
  ID_03 ='1',
  AID ='2',
  SCID='002' WHERE Email='{$email}'";
  if (DEBUG_FLAG == "ON") echo $sql;
  sql($sql,$result,$num,0);
  //if ($result) checkAdminUser($email);
  return $result;
}
function getArrayToString($arr,$prefix){
  $str = "";
  for ($i=0;$i<sizeof($arr);$i++){
    $str.= "'{$prefix}{$arr[$i]}', ";
  }
  $str = rtrim($str,", ");  
  return $str;
}
function getSizeByTable($table)
{
    $sql = "select count(id) as total from {$table}";
    sql($sql,$result,$num,0);
    fetch($arr,$result,0,0);
    return intval($arr['total']);
}
//get camera name from mac
function getCamNameByMac($mac)
{
  $sql="select * from isat.query_info where mac_addr ='{$mac}'";
  sql($sql,$result_email,$num_email,0);
  for($i=0;$i<$num_email;$i++)
  {
    fetch($db_email,$result_email,$i,0);
    if ($db_email["name"] != $db_email["mac_addr"] ) {
      return $db_email["name"];
    }
  }
  return ""; 
}
?>