<?PHP
require_once ("/var/www/qlync_admin/doc/config.php");
require_once "{$home_path}/header.php";

#Authentication Section
$sql="select * from qlync.menu where Name = 'Model List'";
sql($sql,$result,$num,0);
fetch($db,$result,0,0);
$sql="select * from qlync.right_tree where Cfun='{$db["ID"]}' and `Right` = 1";
sql($sql,$result,$num,0);
$right=0;
$oem_id="";
for($i=0;$i<$num;$i++)
{
        fetch($db,$result,$i,0);
        if($_SESSION["{$db["Fright"]}_qlync"] ==1)
        {
                $right+=1;
                if($db["Oem"] == "0")
                {
                        $oem_id="N99";
                }
                if($db["Oem"] == "1" and $oem_id == "")
                {
                        $oem_id=$_SESSION["CID"];
                }
        }
}
//if($right  == "0")
//        exit();
############  Authentication Section End



$title=array("id","manufacturer","model","version","default_id","default_pw","model_id","features");

$url="http://{$api_id}:{$api_pwd}@{$api_path}/backstage_manage.php?command=refresh_device_models";
$path="{$api_temp}/model_list.php";
exec("wget ".$url." -O ".$path);
chmod($path,0777);
$handle=fopen($path,"r");
$pc=fread($handle,filesize($path));
$filter1=explode(":[",$pc);
$filter2=explode("];",$filter1[1]);
$temp=str_replace(":","=>",$filter2[0]);
$temp=str_replace("{","",$temp);
$temp=str_replace("]","",$temp);
$filter3=explode("},",$temp);//  Each Model info
for($i=0;$i<sizeof($filter3);$i++)
{
	$filter3[$i]=str_replace('}','',$filter3[$i]);	
	$filter4=explode(',"',$filter3[$i]); //seperated the parameter in each model
	for($j=0;$j<sizeof($filter4);$j++)
	{
		
		//echo $filter4[$j]."<BR>";
		$filter4[$j]=str_replace('"','',$filter4[$j]);
		$filter5=explode("=>",$filter4[$j]);  // fetch the value
	  $model_list[$i][$title[$j]]=$filter5[1];
	
	
	}

}

for($i=0;$i<sizeof($filter3);$i++)
{
		$id[$i]=$model_list[$i][$title[0]];
		$company[$i]=$model_list[$i][$title[1]];
		// change the camera brand to CID
		if(in_array($company[$i],$cid_camera[$_SESSION["CID"]]))
		{
			$company[$i]=$cid_list[$_SESSION["CID"]];
		}
		$model[$i]=$model_list[$i][$title[2]];
		$version[$i]=$model_list[$i][$title[3]];
		$default_id[$i]=$model_list[$i][$title[4]];
		$default_pw[$i]=$model_list[$i][$title[5]];
		$model_id[$i]=$model_list[$i][$title[7]];
		$features[$i]=$model_list[$i][$title[6]];
//new style
		$m2_list[$company[$i]][$model[$i]][index][]  =$id[$i];
		$m2_list[$company[$i]][$model[$i]][version][]=$version[$i];
		$m2_list[$company[$i]][$model[$i]][feature][]=$features[$i];
		$m2_list[$company[$i]][$model[$i]][id][]=$default_id[$i];
		$m2_list[$company[$i]][$model[$i]][pw][]=$default_pw[$i];
  $m2_list[$company[$i]][$model[$i]][model][]=$model[$i]; //jinho fix model
###########temp input database section
#$sqlt="insert into qlync.fw_info ( FID, CID , Model,FW,Model_id ) Values ('{$id[$i]}','{$company[$i]}')";
#sql($sqlt,$resultt,$numt,2);
##########################################

}


#################sort section
array_multisort($company,SORT_ASC,$model,SORT_ASC,$version,SORT_ASC,$features,SORT_ASC);
$model_count=array_count_values($model);

$last_model="";
ksort($m2_list);
######################

//echo "<div class=bg_mid>\n";
//echo "<div class=content>\n";
	############## Search Section
echo "<hr>";
//echo "</table>";
#################### list
$row=1;
	echo "<table class=table_main>";
		echo "<tr class=topic_main>\n";
			echo "<td>Manufacture</td>";
			echo "<td>Model</td>\n";
			echo "<td>Version</td>\n";
			echo "<td>ID/PWD</td>\n";
			echo "<td>Features</td>\n";
			echo "<td>Action</td>\n";
		echo "</tr>";
	
		{
foreach($m2_list as $key1=>$value1){  // Sort by Branding CID
                ksort($value1);
        foreach($value1 as $key2=>$value2){  // Sort by Model
		array_multisort($value2["version"],$value2["index"],$value2["feature"],$value2["id"],$value2["pw"]);
		unset($fill);
                foreach($value2["version"] as $key3=>$value3){
//                }
                //echo $value3."<BR>";

//jinho fix                      if(/*$value2["id"][$key3]=="null" and $value2["pw"][$key3]=="null" and*/ $value2["feature"][$key3]=="null")
                      if($value2["feature"][$key3]=="null" or $value2["feature"][$key3]=="") //jinho fix
                      // filter the last model as the null by new added
                      {
					echo "<tr>";
                    //jinho fix
                    echo "<td>{$key1}</td>";  //jinho fix again
                    echo "<td>{$value2["model"][$key3]}</td>";
                    //jinho fix end
	                                echo "<td>{$value3} / {$value2["index"][$key3]}</td>";
	                                echo "<td><font color=#0000FF>{$value2["id"][$key3]} / {$value2["pw"][$key3]}</font></td>\n";
	                                //echo "<td>{$value2["feature"][$key3]}-$key3</td>";
					                       echo "<td>SET as {$fill["id"]} / {$fill["pw"]} / {$fill["feature"]}</td>\n";
					//if($value2["id"][$key3-1]=="null" and $value2["pw"][$key3-1]=="null" and $value2["feature"][$key3-1]=="null" )  // filter the reference feature is not nuul
          //jinho fix					if($fill["feature"]=="null")
				   if($fill["feature"]!="null" or $fill["feature"]!="") //jinho fix
					{
						/*
						echo "<td>".($value3)."{$value2["index"][$key3]}</td>";
		                                echo "<td><font color=#0000FF>{$value2[id][$key3-1]} / {$value2[pw][$key3-1]}</font></td>\n";
		                                echo "<td>{$value2["feature"][$key3-1]}-".($key3-1)."</td>";
						*/
		        			$url="http://{$api_id}:{$api_pwd}@{$api_path}/backstage_manage.php?command=update_device_models\&id={$value2["index"][$key3]}\&features={$fill["feature"]}\&submit=Update";
						echo "<td>{$url}</td>\n";
						$path="{$api_temp}/model_list_execute.php";
						exec("wget ".$url." -O ".$path);
						chmod($path,0777);
						exec("rm {$path}");
					}
					echo "</tr>\n";
        }else // not full null
				{
					$fill["id"]=$value2["id"][$key3];
					$fill["pw"]=$value2["pw"][$key3];
					$fill["feature"]=$value2["feature"][$key3];
				}
		} // end by foreach of $vallue2 as Key3


        } //end by model sort
	} //end by branding sort
}//blank
					
echo "</table>";

?>


