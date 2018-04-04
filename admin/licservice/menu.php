<?PHP
/****************
 *Validated on Nov-25,2014,  
 * Revised from Qlync menu
 * TBD: licservice does not have menu items, need to revised!!! 
 *Writer: JinHo, Chang   
*****************/
        echo "<div class=\"menu\">\n";
        echo "<ul>\n";

foreach($menu1 as $key0=>$value0){

		if(($_SESSION["ID_admin_qlync"] ==1 ) || ($value0["valid"]==1))
		{
			echo "<li class=\"project\">{$value0["name"]}";

		}
          echo "<div class=\"submenu\">\n";
          echo "<ul>\n";

          foreach($value0 as $key1 => $value1){
          if($key1 <> "name" and $key1 <> "fright" and $key1 <> "valid") {
//echo "debug1:".$value1["name"];
		   if (($_SESSION["ID_admin_qlync"] ==1 ) || ($value1["valid"]==1))
		                {
					 echo "<li><a href=\"{$value1["link"]}\">{$value1["name"]}</a></li>\n";
		                }
              }
           }

	echo "</ul>";
     echo "</div>\n";
    echo "</li>\n";

}


// index
echo "<BR><BR>";
if(isset($_SESSION["ID_qlync"])){
	if (!isset($_SERVER['REQUEST_URI']))
     {
       $_SERVER['REQUEST_URI'] = substr($_SERVER['PHP_SELF'],1 );
       if (isset($_SERVER['QUERY_STRING'])) { $_SERVER['REQUEST_URI'].='?'.$_SERVER['QUERY_STRING']; }
    	  //$uri=explode("?",mysql_real_escape_string($_SERVER["REQUEST_URI"]));
       $uri[0] = $_SERVER["REQUEST_URI"];
     }


	$sql="select ID,Level,Name,FID from menu where Link='{$uri[0]}'";
	//sql($sql,$result_menu,$num_menu,0);
	//fetch($db_menu,$result_menu,0,0);

     $link=lic_getLink();
     $result_menu=mysql_query($sql,$link);
     if ($result_menu){
          $num_menu = mysql_num_rows($result_menu);
          myfetch($db_menu,$result_menu,0);

     //     echo $sql;
     	if($num_menu >0 and $_SESSION["ID_qlync"] <> "")
     	{
          	if($db_menu["Level"] =="0")
          	{
          		echo "<a><font face=arial color=#999999>{$db_menu["Name"]}</a>\n";

          	}
          	else
          	{
          		$sql="select ID,Name from menu where ID='{$db_menu["FID"]}'";
          		//sql($sql,$result_menu0,$num_menu0,0);
          		//fetch($db_menu0,$result_menu0,0,0);
                    $result_menu0=mysql_query($sql,$link);
                    $num_menu0 = mysql_num_rows($result_menu0);
                    fetch($db_menu0,$result_menu0,0);
          		echo "<a><font size=2 face=arial color=#999999>{$db_menu0["Name"]}</a>\n";
          		echo ">>\n";
          		echo "<a><font face=arial color=#0094F2>{$db_menu["Name"]}</a>\n";
          	}
     	}
      }//if query true
echo "<HR>\n";
}
//echo "<div class=\"bg_top mgtop22\"></div>\n";
?>