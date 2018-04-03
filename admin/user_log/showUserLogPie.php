<?php
/*************
 *Validated on Jan-11,2018
 *Google Pie chart for user_log 
 *@/var/www/qlync_admin/plugin/user_log/
 *Writer: JinHo, Chang 
 *************/
//require_once '_auth_.inc';
require_once ("/var/www/qlync_admin/doc/config.php");
include_once("/var/www/qlync_admin/doc/mysql_connect.php");
include_once("/var/www/qlync_admin/doc/sql.php");
if ( !isset($_SESSION["ID_qlync"]) )  exit();

$charttype = '';
if(isset($_POST['charttype'])){
  $charttype = $_POST['charttype'];
}else $charttype = 'platform';

function createServiceTable($type,$wparam=""){
  $html = '';   // ['ivedatest',4],
  $sql = "select {$type} as name, COUNT(*) as count from isat.user_log {$wparam} group by {$type}";
//echo $sql;
  sql($sql,$result,$num,0);
  $services = array();
  for($i=0;$i<$num;$i++){
    fetch($arr,$result,$i,0);
    $services[$i] = $arr;
  }

  foreach($services as $service){
    $html.="['{$service['name']}',{$service['count']}],\n";
  }
  $html = rtrim($html, ","); //remove last,
  echo $html;
}
?>
<html>
<head>
<title>使用者紀錄</title>
<script src="js/jquery-1.11.1.min.js"></script>
<link rel=stylesheet type="text/css" href="js/style.css">
<script>
function optionValue(selectobj)
{
  var chosenoption=selectobj.options[selectobj.selectedIndex];
  searchForm.name.value = chosenoption.value;
}
</script>
  <script type="text/javascript" src="//www.google.com/jsapi"></script>
  <script type="text/javascript">
  //load package
  google.load('visualization', '1', {packages: ['corechart']});
  google.setOnLoadCallback(drawPieChart);
  function drawPieChart() {
      // Create and populate the data table.
      var data = google.visualization.arrayToDataTable([
          ['name', 'activity'],
          <?php
          createServiceTable($charttype);
          ?>
      ]);
       var options = {
          title: '使用者紀錄分佈'
        };
      // Create and draw the visualization.
      var chart = new google.visualization.PieChart(document.getElementById('piechart'));
      chart.draw(data, options);
   }
</script>
</head>
<body>
  <div id="piechart" style="width: 900px; height: 500px;"></div>
<form id="searchForm" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
  <select name="charttype" id="charttype" onchange="optionValue(this);this.form.submit();">
  <option value=""></option>
  <option value="platform" <?php if ($charttype=="platform") echo "selected";?>>平台</option>  
  <option value="name" <?php if ($charttype=="name") echo "selected";?>>帳號</option>    
  <option value="ip_addr" <?php if ($charttype=="ip_addr") echo "selected";?>>來源位址</option>
  </select>
</form>
</body>
</html>