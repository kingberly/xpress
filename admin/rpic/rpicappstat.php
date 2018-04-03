<?php
/****************
 *Validated on Jan-25,2018,
 *get value from php file name with parameter cc
 *fix javascript printYYYYMMOption iternation
 *add 404 validation
 *fix javascript generate yyyymm error     
*****************/
include("../../header.php");
include("../../menu.php");
if (!isset($_SESSION["Email"]) ) exit();
//define("LOG_PATH","/var/tmp/");
$RPICAPP = [
//oem=> folder / php name  "rpic.plist.php"
"T04"=>array("/taipei/","rpic.php","ivedamobile.php","rpic.norefresh.php","rpic.plist.php"),
"T05"=>array("/ty/","rpic.ty.php","ivedamobile.ty.php"),
"K01"=>array("/rpic/","rpic.php","ivedamobile.php","rpic.k01.1121.php"),
"C13"=>array("/rpic/","xpress.php","mobilecam.php","xpress.plist.php","mobilecam.plist.php"),
"X02"=>array("/rpic/","rpic.t06.php","ivedamobile.php")
//"T06"=>array("/rpic/","rpic.php","ivedamobile.php")
];
define("MSG_SUCCEED"," 成功!");
define("MSG_FAIL"," 失敗!");
define("DL_COUNT","下載次數"); //Download Count
define("HOSTNAME_IP","伺服器資訊"); //HostName/IP
//var_dump($_REQUEST);
if ($_REQUEST['cc']=="")
  if (isset($_REQUEST['oc']))  $_REQUEST['cc'] = $_REQUEST['oc']; 
function getValue($url)
{
//echo $url;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);//ignore invalid SSL
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, $url);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if($httpCode == 404) return "NA";

    $result = curl_exec($ch);
    $result = str_replace("\n", '', $result); // remove new lines
    curl_close($ch);
    if ($result=="Error") return "NA"; //no file
    else if (strpos($result, "Resource not found") !== false) return "NA";
    return $result;
}

function getWebTable($cc=""){
  global $oem, $RPICAPP;
  if ($cc!="")//check cc value
    if (!preg_match("/^[0-9]{4}(0[1-9]|1[0-2])$/",$cc)) return "";
  foreach ($RPICAPP[$oem] as $apkurl){
    if (strpos($apkurl,".php")===FALSE) //folder, not found
      continue;
    $tcount = 0;
    $html .= "<table border=1><tr><th colspan=2>APP ".rtrim($apkurl,".php")."  {$cc}</th></tr><tr><th>".HOSTNAME_IP."</th><th>".DL_COUNT."</th></tr>\n";
    $sql = "select hostname, internal_address from isat.web_server"; 
    sql($sql,$result,$num,0); 
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      if ($cc == "")
        $count = getValue("http://".$arr['internal_address'].$RPICAPP[$oem][0]."{$apkurl}?cc");
      else  $count = getValue("http://".$arr['internal_address'].$RPICAPP[$oem][0]."{$apkurl}?cc={$cc}");
      $html .="<tr><td>{$arr['hostname']} / {$arr['internal_address']}</td><td align=center>{$count}</td></tr>\n";
      $tcount += intval($count);
    }
    $html.= "<tr><td colspan=2 align=right>Total:  {$tcount}</td></tr></table>\n";
  }
  return $html;
}
?>
<!--html>
<head>
</head>
<body-->
<script type="text/javascript">
function optionValue(thisformobj, selectobj)
{//onchange="optionValue(this.form.server_filter, this);this.form.submit();"
	var chosenoption=selectobj.options[selectobj.selectedIndex];
  thisformobj.value = chosenoption.value;
}
var selectedOption = "<?php echo $_REQUEST['cc'];?>";
function printYYYYMMOption(num){
  var today = new Date();
  var preMonth = today.getMonth(); //this is previous month, January is 0
  var yyyy = today.getFullYear();
  var tag=""; 
  document.write("<option value=''>(本月)</option>\n");
  for (var i = 0; i < num; i++) {
    checkdigit = parseInt(preMonth) - i;
    if ( checkdigit >0 ){
      if (checkdigit >= 10) tag = yyyy.toString()+checkdigit.toString();
      else tag = yyyy.toString()+"0"+checkdigit.toString();
    }else{ //jan -1, -2
      if ((checkdigit + 12) >= 10) tag = (yyyy -1).toString()+(checkdigit + 12).toString();
      else tag = (yyyy -1).toString()+"0"+(checkdigit + 12).toString();
    }

    if (selectedOption == tag)
      document.write("<option value='"+tag+"' selected>"+tag+"</option>\n");
    else  document.write("<option value='"+tag+"'>"+tag+"</option>\n");
  }
}
</script>
<div align=center><b><font size=5>APP下載計數</font></b></div>
<div id="container">
<form  method=post action='<?php echo $_SERVER['PHP_SELF'];?>'>
<input type=submit value='輸入年份月份查詢:'>&nbsp;&nbsp;&nbsp;
<input type=text size=5 placeholder="YYYYMM" name=cc value='<?php if (isset($_REQUEST['cc']))  echo $_REQUEST['cc'];?>'>&nbsp; 或下拉選 &nbsp;
<select name=oc onchange="optionValue(this.form.cc, this);this.form.submit();">
<script>printYYYYMMOption(6);</script>
</select>

 </form></p>
<?php
if ($_REQUEST['cc'] != "")
  echo getWebTable($_REQUEST['cc']);
else  echo getWebTable();

?>
</div>
</body>
</html>