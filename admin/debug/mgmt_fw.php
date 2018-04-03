<?php
/****************
 *Validated on Jul-28,2015,
 * list fw list/model mgmt
 * add debugadmin feature to hide remove button  
 *Writer: JinHo, Chang
*****************/
include("../../header.php");
include("../../menu.php");
require_once '_auth_.inc';
if($_REQUEST["step"]=="delfw")
{
    if (isset($_REQUEST["id"]))
    {
        $result = deleteFWByID($_REQUEST["id"]);
        if ($result){
            $msg_err = "<font color=blue>delete FW ".$_REQUEST["id"]. " SUCCESS!</font><br>\n";
        }else $msg_err = "<font color=red>delete FW ".$_REQUEST["id"]. " FAIL!</font><br>\n";
    }
}

function deleteFWByID ($id)
{
    $sql = "delete from isat.device_models where id ={$id}";
    sql($sql,$result,$num,0);
    if ($result) return true;
    return false;     
} 

function createFWTable()
{
    $sql = "select * from isat.device_models";
    sql($sql,$result,$num,0);
    $services = array();
    $index = 0;
    for($i=0;$i<$num;$i++){
      fetch($arr,$result,$i,0);
      $services[$index] = $arr;
    	$index++;
    }//for
  $html = "Total: {$num}";
  $html .= "\n<table id='tbl4' class=table_main><tr class=topic_main>";
  $html .= "<td>ID</td><td>manufacturer</td><td>model / version / client_version</td><td>default_id</td><td>features</td>"; //add table header 3
  $html .= "<td></td></tr>";
  foreach($services as $service)
  {
		$html.= "\n<tr class=tr_2>\n";
    $html.= "<td>{$service['id']}</td>\n";
    $html.= "<td>{$service['manufacturer']}</td>\n";
    $html.= "<td>{$service['model']} / {$service['version']} / {$service['client_version']}</td>\n";
    $html.= "<td>{$service['default_id']}</td>\n";
    $html.= "<td>{$service['features']}</td>\n";
    if (isset($_REQUEST["debugadmin"])){
    $html.= "<td><form action=\"".$_SERVER['PHP_SELF']."\" method=POST>\n";
    $html.= "<input type=submit name='btnAction' value=\"Remove\" class=\"btn_2\">\n";
    $html.= "<input type=hidden name='step' value=\"delfw\" >\n";
    $html.= "<input type=hidden name='id' value=\"{$service['id']}\" >\n";
    $html.= "<input type=hidden name='debugadmin' value=1 >\n";
    $html.= "</form></td>\n";
    }else $html.= "<td></td>\n"; 
    $html.= "</tr>\n";
	}
  $html .= "</table>\n";   //add table end
	echo $html;
}

?>
<!--html>
<head>
</head>
<body-->
<div align=center><b><font size=5>Mgmt FW</font></b></div>
<div id="container">
<?php
if (isset($msg_err))
  echo $msg_err."<hr>";
?>
<h2>FW Table</h2>
<a href = "/html/common/auto_model_list_feature.php" target=blank>Manual Auto_model_update</a><br>
<?php
     createFWTable();
?>
<br>
	</div>
</body>
</html>