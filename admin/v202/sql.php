<?php
//update Admin base function as mysql_* was deprecated in php5.x and removed in php7.0
function sql(&$sql,&$result,&$num,$err)
{
global $mysqli;
		if($err ==""|| err=="1")
		{
			//$result=mysql_query($sql);
      $result =$mysqli->query($sql);
//var_dump($result);
			if ($result===false)//if(!$result)
			{
				$num="";
				//echo mysql_error();
        echo $mysqli->error;
			}
			else
			{
				//$num=mysql_num_rows($result);
        if (strpos(strtolower($sql), 'update') !== false) //update does not required to fetch num
          $num="";
        else
          if (is_bool($result)=== false) //jinho added, resource to proceed 
          $num=mysqli_num_rows($result);//$result->num_rows;
			}
		}
		if($err>0)
			echo "<BR>{$sql}<BR>";
}
#-----------------------------------------------
function fetch(&$db, &$result,$i)
{
		//mysql_data_seek($result,$i);
    mysqli_data_seek($result,$i);
		//$db=mysql_fetch_array($result,MYSQL_BOTH);
    $db=mysqli_fetch_array($result,MYSQLI_BOTH);
}
#-----------------------------------------------
function insert($db,&$col,&$value,$err)
{
	$sql="insert into {$db} (";
	for($i=0;$i<(sizeof($col)-1);$i++)
	{
		$sql="{$sql} {$col[$i]}, ";
	}
	$sql="{$sql} {$col[$i]} ) values ( ";

	for($i=0;$i<(sizeof($col)-1);$i++)
	{
		$sql="{$sql} '{$value[$i]}', ";
	}
	$sql="{$sql} '{$value[$i]}' ) ";
	
	sql($sql,$result,$num,$err);

}
#-----------------------------------------------
function insert2($db,&$col,$value,$err)
{
	$sql="insert into {$db} (";
	for($i=0;$i<(sizeof($col)-1);$i++)
	{
		$sql="{$sql} {$col[$i]}, ";
	}
	$sql="{$sql} {$col[$i]} ) values ( ";


		$sql="{$sql} {$value} ) ";
	
	sql($sql,$result,$num,$err);

}
#-----------------------------------------------
function update($db,&$col,&$val,$id,$err)
{
	$sql="update {$db} set ";
	for($i=0;$i<(sizeof($col)-1);$i++)
	{
		$sql="{$sql} {$col[$i]}='{$val[$i]}', ";
	}
	$sql="{$sql} {$col[$i]}='{$val[$i]}'  ";
	$sql="{$sql} where ID='{$id}'  ";

	sql($sql,$result,$num,$err);

}
#-----------------------------------------------


function table($i,$j,$title,&$data,&$titledata, $class)
{
	echo "<table class=\"{$class}\">\n";
		for($ii=0;$ii<$i;$ii++)
		{
			if($title==1and $ii==0)
			{
				echo "<thead>\n";
				for($t=0;$t<$j;$t++)
				{
					echo "<th>\n";
						echo $titledata[$t];
					echo "</th>\n";
				}
				echo "</thead>\n";
			}
			echo "<tr>\n";
			for($jj=0;$jj<$j;$jj++)
			{
				echo "<td>\n";
					echo $data[$ii*$j+$jj];
					#echo $ii.$jj;
				echo "</td>\n";
			}
			echo "</tr>\n";
		}
	echo "</table>\n";
}

function table2($i,$j,$title,&$data,&$titledata, $class)
{
	echo "<table class=\"{$class}\">\n";
		for($ii=0;$ii<$i;$ii++)
		{
			echo "<tr>\n";
				echo "<td>";
					echo $titledata[$ii];
				echo "</td>";
			for($jj=1;$jj<$j;$jj++)
			{
				echo "<td>\n";
					echo $data[($ii-1)*($j-1)+$jj];
				echo "</td>\n";
			}
			echo "</tr>\n";
		}
	echo "</table>\n";
}


function   sub_str($text,   $length) 
{ 
for   ($i=0;   $i <$length;   $i++) 
{ 
$chr   =   substr($text,   $i,   1); 
if   (ord($chr)   >   0x80)//¦r²Å¬O¤¤¤å 
{ 
$length++; 
$i++; 
} 

} 
$str   =   substr($text,   0,   $length);   
return   $str; 
}
?>
