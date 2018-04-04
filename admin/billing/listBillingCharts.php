<?php
/****************
 *Validated on Feb-14, 2017
 *Writer: JinHo, Chang
*****************/
require_once ("/var/www/qlync_admin/doc/config.php");
require_once 'dbutil.php';
ini_set('memory_limit', '64M');
//var_dump($_REQUEST);
if (isset($_REQUEST['logyear'])){
  include_once($_REQUEST['logyear']);
}else{
  include_once("billing.log");
  $_REQUEST['logyear'] = "billing.log";
}
//global value oem
if(isset($_REQUEST['ctype'])){
	$ctype = $_REQUEST['ctype'];
}else $ctype = "Monthly";
reset($billing_data);
$START_DATE =key($billing_data[$oem]);
end($billing_data[$oem]);
$END_DATE =key($billing_data[$oem]);

function printLogFileOption($sel)
{
  $files = scandir(".", SCANDIR_SORT_ASCENDING);
  for($i=0;$i<sizeof($files);$i++){
      if ( (strpos($files[$i],"billing.log")!== FALSE) ){
          if ($files[$i] == $sel){
            $html.="<option value=\"{$files[$i]}\" selected>{$files[$i]}</option>\n";
          }else $html.="<option value=\"{$files[$i]}\">{$files[$i]}</option>\n";
      }
   } 
  
  echo $html;
}

function createBillingTableDaily($data){
     global $oem;
	$html = '';   // ['20150111',68, 25],
	foreach($data[$oem] as $key=>$values){
         $date = $key;
         $licnum = $values["lic"];
         $bindnum=$values["binded"];
         $html.="['$date',$licnum,$bindnum],\n";
  }
  $html = rtrim($html, ","); //remove last,
	echo $html;
}

function createBillingTableMonthly($data){
     global $oem, $START_DATE, $END_DATE;
     $START_DATE = substr($START_DATE,0,6);
     $END_DATE = substr($END_DATE,0,6);
     $key_monthly="";
	$html = '';   // ['201501',68, 25],
	foreach($data[$oem] as $key=>$values){
         if (($key_monthly=="") or ($key_monthly < substr($key,0,6))){
              $key_monthly = substr($key,0,6);
              $date = $key_monthly;
              $licnum = $values["lic"];
              $bindnum=$values["binded"];
              $html.="['$date',$licnum,$bindnum],\n";
         }
  }
  $html = rtrim($html, ","); //remove last,
	echo $html;
}

?>

<html>
<head>
  <script type="text/javascript" src="//www.google.com/jsapi"></script>
  <script type="text/javascript">
  //load package
  google.load('visualization', '1.1', {packages: ['bar']});
  google.setOnLoadCallback(drawChart);
  function drawChart() {
      // Create and populate the data table.
      var data = google.visualization.arrayToDataTable([
          ['Date', 'Uploaded','Binded'],
          <?php
          if ($ctype == "Monthly")
             createBillingTableMonthly($billing_data);
          else
              createBillingTableDaily($billing_data);
          ?>
      ]);
       var options = {
          chart:{
            title: 'Camera License status <?php echo "@ ".$oem;?>',
            subtitle: 'Uploaded License, Binded License: <?php echo "{$START_DATE}-{$END_DATE}";?>',
          }
        };
      // Create and draw the visualization.
      var chart = new google.charts.Bar(document.getElementById('columnchart_material'));
      chart.draw(data, options);
   }
</script>
<script>
function optionValue(thisformobj, selectobj)
{
	var chosenoption=selectobj.options[selectobj.selectedIndex];
  thisformobj.value = chosenoption.value;
}
</script>
</head>
<body>
  <div id="columnchart_material" style="width: 900px; height: 500px;"></div>
          <?php
          /*
          if ($ctype == "Monthly")
             createBillingTableMonthly($billing_data);
          else
              createBillingTableDaily($billing_data);
          */
          ?>
		<form id="searchForm" method="post"	action="<?php echo $_SERVER['PHP_SELF']; ?>">
        <select name="ctype" id="ctype" onchange="optionValue(this.form.ctype,this);this.form.submit();">
        <option value="Monthly" <?php if($ctype=="Monthly") echo "selected";?>>Monthly</option>
        <option value="Daily" <?php if($ctype=="Daily") echo "selected";?>>Daily</option>
        </select>
        <select name="logyear" id="logyear" onchange="optionValue(this.form.logyear,this);this.form.submit();">
        <?php
            printLogFileOption($_REQUEST['logyear']);
        ?>
        </select>
        </form>
</body>
</html>