<?PHP
include("../../doc/config.php");
include("../../doc/mysql_connect.php");
include("../../doc/sql.php");
header("Cache-Control: private, no-store, no-cache, must-revalidate, post-check=0, pre-check=0, max-age=0");
header("Pragma: no-cache");
?>
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Lesson</title>
<link rel="stylesheet" type="text/css" href="/css/form.css">
<meta name="viewport" content="width=device-width, initial-scale=1.2, user-scalable=no, minimum-scale=1.2, maximum-scale=1.2" />

<meta http-equiv="Content-Language" content="utf-8">
<meta http-equiv="Content-Type" content="text/javascript; charset=utf-8">
<meta http-equiv="Page-Exit" content="revealTrans(Duration=4.0,Transition=2)">
<meta http-equiv="Site-Enter" content="revealTrans(Duration=4.0,Transition=1)">
<link href="/css/layout.css" rel="stylesheet" type="text/css"  charset="utf-8" />
<link href="/css/form.css" rel="stylesheet" type="text/css"  charset="utf-8" />
<link href="/css/menu.css" rel="stylesheet" type="text/css"  charset="utf-8" />
<link href="/css/nav.css" rel="stylesheet" type="text/css"  charset="utf-8" />

</head>
<body>
<div style="text-align: center">
<table><tr><td>

<?

################3
#Role_ID fixed as 02
#scid is required
#account name is required
#
	$sql="select name,user_name,mac_addr from isat.query_info where mac_addr='{$_REQUEST["mac"]}' limit 0,1";
	sql($sql,$result_tmp,$num_tmp,0);
	fetch($db_tmp,$result_tmp,0,0);

        $sql="select * from qlync.application_info where Role_type='02' and Owner='{$db_tmp["user_name"]}' and Time_start > '".(date("Y")-1911)."-".date("m")."-".date("d")."' order by Time_start asc limit 0,3";
        sql($sql,$result_list,$num_list,0);
if(is_file($home_path."/html/scid/{$_REQUEST["scid"]}/02/{$db_tmp["user_name"]}/cover.jpg"))
{
	//echo "<img src=\"/html/scid/{$_REQUEST["scid"]}/02/{$db_tmp["user_name"]}/cover.jpg\" width=120 style=\"float: left; margin: 15px\" >\n";
  echo "<img src=\"/html/scid/{$_REQUEST["scid"]}/02/{$db_tmp["user_name"]}/cover.jpg\" width=200 >\n";
}
echo "</td></tr>";

	for($i=0;$i<$num_list;$i++)
	{
		echo "<tr><td>\n";//echo "<HR>\n";
		fetch($db,$result_list,$i,0);
		echo $db["Name"]."<BR>";
		$t=array();
		$t=explode("-",$db["Time_start"]);
		echo "{$t[0]} / {$t[1]} / {$t[2]}  {$t[3]} : {$t[4]}<BR>\n";
		echo $db["Info"]."<BR>";
    echo "</td></tr>\n";
	}


?>
</table>
</div>
</body>
</html>
