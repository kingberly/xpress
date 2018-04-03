<?PHP
//$auth=array("ID_none","ID_webmaster", "ID_admin", "ID_admin_oem","ID_qlync_pm","ID_qlync_rd","ID_pm_oem","ID_qlync_sales","ID_qlync_fae","ID_qlync_qa","ID_qlync_admin");
//new 20130501
//menu1[Var_ID]	[Name]		=> Value
//             	[Fright]	=> array
//		[Valid] 	=> 0/1
//		[Var_subID]	[Name]	=> Value
//				[Fright]	=> array
//				[Valid]		=> 0/1
//				[Link]	=> Value

### 					section 1    	Partner
#$sql="select * from qlync.menu where Level=0 order by OID asc";
#sql($sql,$result0,$num0,0);
##############################
# Role for the index in the $auth array with the admin right
# Role 2 for the index in the $auth array with the flow control issue
#######################################################################
        echo "<div class=\"menu\">\n";
        echo "<ul>\n";

foreach($menu1 as $key0=>$value0){
	foreach($auth as $key_auth=>$value_auth){
		if(in_array($value_auth,$value0["fright"]) and ($_SESSION["{$value_auth}_qlync"]==1 or $_SESSION["RID"][substr($value_auth,3,2)]==1) or $value0["valid"])
		{
			echo "<li class=\"project\">"._($value0["name"])."";
			break;
		}
	}
                echo "<div class=\"submenu\">\n";
                        echo "<ul>\n";

	foreach($value0 as $key1 => $value1){
		if($key1 <> "name" and $key1 <> "fright" and $key1 <> "valid")
		{
		        foreach($auth as $value_auth){
		                if(in_array($value_auth,$value1["fright"]) and ($_SESSION["{$value_auth}_qlync"]==1 or $_SESSION["RID"][substr($value_auth,3,2)]==1) or $value1["valid"])
		                {
					 echo "<li><a href=\"{$value1["link"]}\">".gettext($value1["name"])."</a></li>\n";
		                      	//echo $value1["name"];
                        		break;
		                }

		        }	

		}
	}
	echo "</ul>";
      echo "</div>\n";
    echo "</li>\n";

}

//echo "</div>\n";
// index
echo "<BR><BR>";
//	echo "<font face=arial color=$999999> >> ";
	$uri=explode("?",mysql_real_escape_string($_SERVER["REQUEST_URI"]));
	$sql="select ID,Level,Name,FID from qlync.menu where Link='{$uri[0]}'";
	sql($sql,$result_menu,$num_menu,0);
	fetch($db_menu,$result_menu,0,0);
	if($num_menu >0 and $_SESSION["ID_qlync"] <> "")
	{
	if($db_menu["Level"] =="0")
	{
		echo "<a><font face=arial color=#999999>{$db_menu["Name"]}</a>\n";

	}
	else
	{
		$sql="select ID,Name from qlync.menu where ID='{$db_menu["FID"]}'";
		sql($sql,$result_menu0,$num_menu0,0);
		fetch($db_menu0,$result_menu0,0,0);
		echo "<a><font size=2 face=arial color=#999999>".gettext($db_menu0["Name"])."</a>\n";
		echo ">>\n";
		echo "<a><font face=arial color=#0094F2>".gettext($db_menu["Name"])."</a>\n";
	}
	}

echo "<div class=\"bg_top mgtop22\"></div>\n";


				
