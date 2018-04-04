<?php
/****************
 *Validated on Oct-14,2015,  
 * Camera license logistic Pie chart by CID, Model, Note 
 *Writer: JinHo, Chang   
*****************/
//require_once '_auth_.inc';
require_once ("/var/www/qlync_admin/doc/config.php");
include("/var/www/qlync_admin/doc/mysql_connect.php"); 
include("/var/www/qlync_admin/doc/sql.php");
if (!isset($_SESSION["Contact"])) exit(1);

if (isset($_REQUEST['cloudtype']))
	$cloudtype=	$_REQUEST['cloudtype'];
else if (isset($_REQUEST['fieldname'])){
	if (updateDBfield($_REQUEST['fieldname'], $_REQUEST['field-old'], $_REQUEST['field-new']))
		$msgerr = "<font color=blue>Success to Change from {$_REQUEST['field-old']} to {$_REQUEST['field-new']}</font>\n";
	else $msgerr = "<font color=red>Fail to Change from {$_REQUEST['field-old']} to {$_REQUEST['field-new']}</font>\n";
	$cloudtype=$_REQUEST['fieldname'];
}else{
 	$cloudtype="CID";
}
$services = query($cloudtype);

function updateDBfield($field, $valueOld, $valueNew){
	$sql ="Update licservice.qlicense set {$field}='{$valueNew}' WHERE {$field}='{$valueOld}'";
	sql($sql,$result,$num,0);
	return $result;
}    
function query($type){
//byItem 1=MAC
//byItem 2=code
//byItem 3=Serial (Order_num)
//byItem 4=Model (Hw)
//byItem 5=Note (filename)
//byItem 6=CID
 //if (strcmp($byItem,"4")==0)

  global $PAGE, $QUERY_TOTAL;

  $sql.="select {$type} as name, COUNT(*) as count FROM licservice.qlicense group by {$type}";
  sql($sql,$result,$num,0);
	if ($result){
    	$services = array();
    	for($i=0;$i<$num;$i++){
        fetch($arr,$result,$i,0);
    		$services[$i] = $arr;
    	}//for
  }
	return $services;
}

function createServiceTable($services){
	$html = '';   // ['ivedatest',4],
	foreach($services as $service){
    $name = $service['name'];
    $count = $service['count']; 
    $html.="['$name',$count],\n";
  }
  $html = rtrim($html, ","); //remove last,
	echo $html;
}
?>

<html>
<head>
<title>Camera License Pie Chart</title>
<script src="../user_log/js/jquery-1.11.1.min.js"></script>
<link rel=stylesheet type="text/css" href="../user_log/js/style.css">
  <script type="text/javascript" src="//www.google.com/jsapi"></script>
  <script type="text/javascript">
  //load package
  google.load('visualization', '1', {packages: ['corechart']});
  google.setOnLoadCallback(drawPieChart);
  function drawPieChart() {
      // Create and populate the data table.
      var data = google.visualization.arrayToDataTable([
          ['name', 'activity'],
          //['P04',4],
          //['V04',11]
          <?php
          createServiceTable($services);
          ?>
      ]);
       var options = {
          title: 'camera distribution'
        };
      // Create and draw the visualization.
      var chart = new google.visualization.PieChart(document.getElementById('piechart'));
      chart.draw(data, options);
   }
function optionValue(thisformobj, selectobj)
{
  var chosenoption=selectobj.options[selectobj.selectedIndex];
  thisformobj.value = chosenoption.value;
  //empty search value if click select
  //document.getElementById('cloudid').value="";
  //selectobj.form.cloudid.value="";
}
</script>
</head>
<body>
<form id="searchForm" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<select id="cloudtype" name="cloudtype" onchange="optionValue(this.form.cloudtype, this);this.form.submit();">
<option value="CID" <?php if ($cloudtype=="CID") echo "selected";?>>CID</option>
<option value="Hw" <?php if ($cloudtype=="Hw") echo "selected";?>>Model</option>
<option value="filename" <?php if ($cloudtype=="filename") echo "selected";?>>Note</option>
</select>
</form>
<div id="piechart" style="width: 900px; height: 500px;"></div>

<hr>
<?php
	if (isset($msgerr))
		echo "<small>{$msgerr}</small><br>\n";
?>
<form id="updateField" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>">
<select id="fieldname" name="fieldname">
<option></option>
<option value="Hw" <?php if ($_REQUEST['fieldname']=="Hw") echo "selected";?>>Model</option>
<option value="filename" <?php if ($_REQUEST['fieldname']=="filename") echo "selected";?>>Note</option>
</select>
<input type=text name="field-old" id="field-old" value="Z3505" size=5>
<input type=submit name="update-field" id="update-field" value="Update to">
<input type=text name="field-new" id="field-new" value="Z-3505"  size=5>
</form>

</body>
</html>