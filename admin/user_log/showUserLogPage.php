<?php
/*************
 *Validated on Feb-1,2016,  Page Log list  OK
 * sudo mv showUserLogPage.php /var/www/qlync_admin/plugin/user_log/ 
 * update jquery library to local js
 * performance improvement 
 * ************/ 
require_once '_auth_.inc';
define("PAGE_LIMIT",50);
$name = '';
if(isset($_POST['name'])){
  $name = $_POST['name'];
}

$PAGE = 1;
if(isset($_POST['page'])){
  $PAGE = $_POST['page'];
}
$QUERY_TOTAL =0;

$services = query($name);

function query($name){
  global $PAGE, $QUERY_TOTAL;
  //$link = lic_getLink();
  //count is faster than mysql_num_rows
  $sql = "select count(id) as total from isat.user_log";
  //$result=mysql_query($sql,$link);
  sql($sql,$result,$num,0);
  $QUERY_TOTAL = mysql_result($result,0,'total');
  $sql = "select * from isat.user_log";
  if($name!='')
    $sql.=" where name='$name'";
    if ($QUERY_TOTAL < (($PAGE-1)*PAGE_LIMIT))
       $PAGE = 1;
    $sql .=" order by ts DESC limit ".PAGE_LIMIT." offset ".(($PAGE-1)*PAGE_LIMIT);
//| id | name      | reg_email              | ts                  | action | result  | ip_addr       | oem_id | method | cloud_type | platform | browser | browser_version | info |
  //$result=mysql_query($sql,$link);
  sql($sql,$result,$num,0);
  $services = array();
  for($i=0;$i<mysql_num_rows($result);$i++){
    fetch($arr,$result,$i,0);
    $services[$i] = $arr;
  }
  return $services;
}

function createNameList()
{
	  $html = '<option>Select name</option>';
		$sql = "select name from isat.user_log group by name limit ".PAGE_LIMIT; 
		sql($sql,$result,$num,0);
		for($i=0;$i<$num;$i++){
			$name = mysql_result($result,$i,'name');
			$html.="<option value=$name>$name</option>\n";
		}
		if ($num ==PAGE_LIMIT) $html.="<option>--50 only--</option>\n";
    echo $html;
}

function createServiceTable($services){
  $html = '';
  foreach($services as $service){
    $html.="\n<tr>";
    $name = $service['name'];
    $reg_email = $service['reg_email'];

    //platform 
    $arr = explode(',', $service['platform']);
    $platform = '';
    foreach($arr as $item){
      $platform.=$item."\n";
    }
    $browser = '';
    $arr = explode(',', $service['browser']);
    foreach($arr as $item){
      $browser.=$item."\n";
    }
    $browser_version = '';
    $arr = explode(',', $service['browser_version']);
    foreach($arr as $item){
      $browser_version.=$item."\n";
    }
    // action
    $action = '';
    $arr = explode(',', $service['action']);
    foreach($arr as $item){
      $action.=$item."\n";
    }

    //result
    $result = '';
    $arr = explode(',', $service['result']);
    foreach($arr as $item){
      $result.=$item."\n";
    }
    // ts
    $ip_addr = '';
    $arr = explode(',', $service['ip_addr']);
    foreach($arr as $item){
      $ip_addr.=$item."\n";
    }
    // ts
    $ts = '';
    $arr = explode(',', $service['ts']);
    foreach($arr as $item){
      $ts.=$item."\n";
    }

    $html.="\n<td style=\"text-align: center\">$name";
    $html.=" ($reg_email)</td>";
    $html.="\n<td>$platform";
    $html.="\n($browser/$browser_version)</td>";
    $html.="\n<td>$action";
    $html.="\n / $result</td>";
    $html.="\n<td>$ip_addr</td>";
    $html.="\n<td>$ts</td>";
    $html.="</tr>";

  }
  echo $html;
}
?>

<script src="js/jquery-1.11.1.min.js"></script>
<link rel=stylesheet type="text/css" href="js/style.css">
<script>
function optionValue(thisformobj, selectobj)
{
  var chosenoption=selectobj.options[selectobj.selectedIndex];
  thisformobj.value = chosenoption.value;
}
</script>
<font color=black>
  <div id="container">
<!--new added log-->
<?php
if (file_exists($_SERVER["DOCUMENT_ROOT"]."/plugin/debug/showApiLog.php")) 
  echo "<a href='/plugin/debug/showApiLog.php' target='_blank'>API Log</a>\n&nbsp;&nbsp;";
if (file_exists($_SERVER["DOCUMENT_ROOT"]."/plugin/taipei/showShareLog.php"))
  echo "<a href='/plugin/taipei/showShareLog.php' target='_blank'>Share Log</a>\n&nbsp;&nbsp;";
?>
<!--new added log end-->
    <form id="searchForm" method="post"
      action="<?php echo $_SERVER['PHP_SELF']; ?>">
      Account <small><a href='#' onClick="window.open('showUserLogPie.php','text',config='height=600,width=800');">Pie chart</a></small>&nbsp;&nbsp;
               <input type="text" size="8" name="name" id="name" value="<?php echo $name;?>">&nbsp;&nbsp;
        <select name="sname" id="sname" onchange="optionValue(this.form.name,this);this.form.submit();">
        <?php createNameList();?>
        </select>
           <?php
           echo "(".count($services)." / ".$QUERY_TOTAL.") ";
           echo "PAGE&nbsp;&nbsp;<Select id='page' name='page' onChange=\"this.form.submit();\">";
           for ($i=1;$i<ceil($QUERY_TOTAL/PAGE_LIMIT)+1;$i++)
           {
               if ($PAGE==$i)
                 echo "<Option value='" . $i ."' selected>".$i."</Option>";
               else
                 echo "<Option value='" . $i ."'>".$i."</Option>";
           }
           echo "</Select>";
           ?>
    </form>

    <p><br>
      <table style="margin-top: -5px" id="tbl" border=1>
        <thead>
          <tr>
            <th>Account (Email)</th>
            <th>Platform / Version</th>
            <th>Action</th>
            <th>Address</th>
            <th>Timestamp</th>
          </tr>
        </thead>
        <tbody>
          <?php createServiceTable($services);?>
        </tbody>
      </table>
  </div>
</font>
</body>

</html>