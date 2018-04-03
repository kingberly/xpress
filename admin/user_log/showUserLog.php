<?php
/*************
 *Validated on Jan-11,2018
 * Add serach user/platform/IP/datetime
 * @/var/www/qlync_admin/plugin/user_log/
 *Writer: JinHo, Chang   
 *************/
//require_once '_auth_.inc';
include("/var/www/qlync_admin/header.php");
include("/var/www/qlync_admin/menu.php");
if ( !isset($_SESSION["ID_qlync"]) )  exit();
//var_dump($_REQUEST);
header("Content-Type:text/html; charset=utf-8");
define("PAGE_LIMIT",50);

$name = '';
$action = '';
$targetip = '';
$datestr = '';
if ($_REQUEST['isclean']=="false"){
if(isset($_REQUEST['name'])){
	$name = trim($_REQUEST['name']);
}
if(isset($_REQUEST['action'])){
	$action = $_REQUEST['action'];
}
if(isset($_REQUEST['targetip'])){
	$targetip = trim($_REQUEST['targetip']);
}
if(isset($_REQUEST['datestr'])){
	$datestr = trim($_REQUEST['datestr']);
}
}
function createServiceTable($limit,$wparam=""){
	$html = '';
	$sql = "select * from isat.user_log {$wparam}";
  if ($limit == 0)  $sql .=" order by id DESC";
  else  $sql .=" order by id DESC limit ". PAGE_LIMIT;
  sql($sql,$result,$num,0);
	$services = array();
	for($i=0;$i<$num;$i++){
    fetch($arr,$result,$i,0);
    $services[$i] = $arr;
	}

	$html .= '<font color=black><table id="tbl" style="margin-top: 5px" border="1"><thead><tr>';
	$html .="<th>({$num})</th><th>帳號(Email)</th><th>平台(瀏覽器)</th><th>指令/結果</th>";
  $html .="<th>IP位址</th><th>時間</th></tr></thead><tbody>";
  		
  foreach($services as $service)
  {
		$html.= "\n<tr>\n";
    $html.= "<td>{$service['id']}</td>\n";
    $html.= "<td width=150px>{$service['name']} ({$service['reg_email']})</td>\n";
    $html.= "<td>{$service['platform']} ({$service['browser']} / {$service['browser_version']})</td>\n";
    $html.= "<td width=150px>{$service['action']} / {$service['result']}</td>\n";
    $html.= "<td>{$service['ip_addr']}";
    $html.= "<td>{$service['ts']}</td>\n";
    $html.= "</tr>\n";
	}
  $html .="</tbody></table></font>";
	echo $html;
}
?>

<script src="js/jquery-1.11.1.min.js"></script>
<script>
function submitResetValue(formobj)
{
  formobj.isclean.value = "true";
  formobj.submit();
}
function validateForm(myform) {
  if (myform.name.value!="")
    if (/^[a-zA-Z0-9\-]{4,32}$/.test(myform.name.value) === false){
      alert("帳號輸入不正確,最短需4位!");
      myform.name.style.borderColor = "red";  
      myform.name.focus();  
      return;
    }
  if (myform.targetip.value!="")
  if (/^[0-9.]{5,15}$/.test(myform.targetip.value) === false){
    alert("IP格式輸入不正確! 例: N.N.N.N");
    myform.targetip.style.borderColor = "red";  
    myform.targetip.focus();  
    return;
  }
  if (myform.datestr.value!="")
  if (/^[0-9\-]{4,10}$/.test(myform.datestr.value) === false){
    alert("日期格式輸入不正確! 例: NNNN-NN-NN");
    myform.datestr.style.borderColor = "red";  
    myform.datestr.focus();  
    return;
  }
  myform.submit();  
  return true;
}
</script>
<link rel=stylesheet type="text/css" href="js/style.css">
	<div id="container">
	<form id="searchForm" method="post"  onsubmit="validateForm(this.form);" action="<?php echo $_SERVER['PHP_SELF']; ?>">
  帳號:&nbsp;<input type="text" size="10" name="name" id="name" value="<?php echo $name;?>" onkeypress="if (event.keyCode == 13) validateForm(this.form);">
  &nbsp;&nbsp;
  指令:&nbsp;<select name="action" id="action">
  <option value=""></option>
  <option value="LOGIN" <?php if ($action=="LOGIN") echo "selected";?>>一般登入</option>
  <option value="APP LOGIN" <?php if ($action=="APP LOGIN") echo "selected";?>>APP登入</option>
  </select>&nbsp;&nbsp;
  IP:&nbsp;<input type="text" size="10" name="targetip" id="targetip" placeholder="127.0.0.1" value="<?php echo $targetip;?>" onkeypress="if (event.keyCode == 13) validateForm(this.form);">
  日期:&nbsp;<input type="text" size="10" name="datestr" id="datestr" placeholder="2018-01-01" value="<?php echo $datestr;?>" onkeypress="if (event.keyCode == 13) validateForm(this.form);">
  &nbsp;&nbsp;
  <input id="isclean" name="isclean" type="hidden" value="false">
  <input id="btnclean" name="btnclean" type="button" value="清除" onclick='submitResetValue(this.form);'>
  &nbsp;&nbsp;
  <input type="button" value="搜尋" onclick="validateForm(this.form);" >
	</form>
<?php
echo "紀錄<a target='userlogpie' href='showUserLogPie.php' onclick=\"javascript: window.open(this.href,'userlogpie','height=600,width=650');return false;\">圖表</a> (搜尋 {$name}/{$action}/{$targetip}/{$datestr}) :\n";
$param="";
if ($name !="") $param ="where name like '%{$name}%'";
if ($action !="")
  if (strpos($param,"where")!==FALSE) $param.=" and action = '{$action}'";
  else  $param.="where action = '{$action}'";
if ($targetip !="")
  if (strpos($param,"where")!==FALSE) $param.=" and ip_addr like '%{$targetip}%'";
  else  $param.="where ip_addr like '%{$targetip}%'";
if ($datestr !="")
  if (strpos($param,"where")!==FALSE) $param.=" and ts like '%{$datestr}%'";
  else $param.="where ts like '%{$datestr}%'";
createServiceTable(50,$param);
?>

	</div>
</body>
</html>