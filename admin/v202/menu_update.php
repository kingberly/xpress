<?PHP
include_once("/var/www/qlync_admin/doc/config.php");
include_once "{$home_path}/doc/mysql_connect.php";
include_once "{$home_path}/doc/sql.php";
//menu array generator per day start
$fp_menu=fopen("{$home_path}/html/common/menu_list.php","w");
fwrite($fp_menu,'<?');
//new 20130501
//menu1[Var_ID] [Name]          => Value
//              [Fright]        => array
//              [Valid]         => 0/1
//              [Var_subID]     [Name]  => Value
//                              [Fright]        => array
//                              [Valid]         => 0/1
//                              [Link]  => Value
//$sql="select * from qlync.menu where Level=0 order by OID asc";
$sql="select *,CONVERT(OID,UNSIGNED INTEGER) AS orderid from qlync.menu where Level=0 order by orderid asc";//jinho fix
sql($sql,$result,$num,0);
for($i=0;$i<$num;$i++)
{
        fetch($db,$result,$i,0);
//        $menu1[$db["ID"]]["name"]=$db["Name"];
//        $menu1[$db["ID"]]["valid"]=1-$db["Auth"];
        fwrite($fp_menu, '$menu1['.$db["ID"].']["name"]="'.$db["Name"].'";'."\n");
        fwrite($fp_menu, '$menu1['.$db["ID"].']["valid"]='.(1-$db["Auth"]).';'."\n");


        $sql="select * from qlync.right_tree where Cfun='{$db["ID"]}' and `Right`=1";
        sql($sql,$result_2,$num_2,0);
        for($j=0;$j<$num_2;$j++)
        {
                fetch($db_2,$result_2,$j,0);
                $menu1[$db["ID"]]["fright"][]   = $db_2["Fright"];
                fwrite($fp_menu, '$menu1['.$db["ID"].']["fright"]['.$j.']="'.$db_2["Fright"].'";'."\n");
        }
}
// fetch the Level1 info
foreach($menu1 as $key1=>$value1){
        //$sql="select * from qlync.menu where Level=1 and FID={$key1}  order by OID asc";
        $sql="select *,CONVERT(OID,UNSIGNED INTEGER) AS orderid from qlync.menu where Level=1 and FID={$key1}  order by orderid asc";//jinho fix 
 
        sql($sql,$result,$num,0);
        for($i=0;$i<$num;$i++)
        {
                fetch($db,$result,$i,0);
                $menu1[$key1][$db["ID"]]["name"]=$db["Name"];
                $menu1[$key1][$db["ID"]]["link"]=$db["Link"];
                $menu1[$key1][$db["ID"]]["valid"]=1-$db["Auth"];
                fwrite($fp_menu, '$menu1['.$key1.']['.$db["ID"].']["name"]="'.$db["Name"].'";'."\n");
                fwrite($fp_menu, '$menu1['.$key1.']['.$db["ID"].']["link"]="'.$db["Link"].'";'."\n");
                fwrite($fp_menu, '$menu1['.$key1.']['.$db["ID"].']["valid"]='.(1-$db["Auth"]).';'."\n");

                $sql="select * from qlync.right_tree where Cfun='{$db["ID"]}' and `Right`=1";
                sql($sql,$result_2,$num_2,0);
                for($j=0;$j<$num_2;$j++)
                {
                        fetch($db_2,$result_2,$j,0);
                        $menu1[$key1][$db["ID"]]["fright"][]=$db_2["Fright"];
                        fwrite($fp_menu, '$menu1['.$key1.']['.$db["ID"].']["fright"]['.$j.']="'.$db_2["Fright"].'";'."\n");
                }
        }

}

fwrite($fp_menu,'?>');
fclose($fp_menu);
//menu array generator per day end
?>
