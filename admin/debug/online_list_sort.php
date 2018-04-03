<?
/*
** Revised from /html/member/online_list.php 
*/
include("../../header.php");
include("../../menu.php");
ini_set('memory_limit', '256M');
include("../../html/common/country.php");
include("../../html/common/geoip.inc");
include("../../html/common/geoipcity.inc");
include("../../html/common/geoipregionvars.php");
require_once '_auth_.inc';

$path="{$api_temp}/online_list.csv";
$fileLastModifyTime=new DateTime();
$fileLastModifyTime->setTimestamp(filemtime($path));
$now = new DateTime();
$diff=$now->diff($fileLastModifyTime);
if ($diff->i > 3){    //jinho added, get when file is older than 3 minutes
#Get Data File
$url="http://{$api_id}:{$api_pwd}@{$api_path}/fetch_online_clients.php";
exec("wget ".$url." -O ".$path);
chmod($path,0777);
}
$handle=fopen($path,"r");
$content=fread($handle,filesize($path));
$content=str_replace("\n",",",$content);
$total=explode(",",$content);
flush($account_group);
$item=8;
###########Temp section
if (isset($_REQUEST['debugadmin'])) $pagesize=500;
else $pagesize=30;
$total_dev=sizeof($total)/$item;
$total_page=ceil($total_dev/$pagesize);

if ( !isset($_GET["page"]) ) {
 $page=1; //設定起始頁
     //Fix search result empty issue: jinho serach file
    if ( isset($_REQUEST["uid"])  or isset($_REQUEST["account"]) 
        or isset($_REQUEST["sortip"]) or isset($_REQUEST["sortmodel"]) ) 
    {
        if ( $_REQUEST["uid"]!="" )
          $search      = $_REQUEST["uid"];
        else if ( $_REQUEST["sortip"]!="" )
          $search      = $_REQUEST["sortip"];
        else if ( $_REQUEST["sortmodel"]!="" )
          $search      = $_REQUEST["sortmodel"];
        else $search = $_REQUEST["account"];
        $lines       = file("{$api_temp}/online_list.csv");

        $line_number = false;
        $total_found = 0;
        //while (list($key, $line) = each($lines) and !$line_number) {
        foreach ($lines as $key => $line) {
          //jinho: search check
          $myline=$line;
          if ( $_REQUEST["uid"]!="" ){
            $pos1=strpos($myline,",");
            $myline=substr($myline,$pos1+1,strpos($myline,",",$pos1+1)-$pos1-1);
          }else if ( $_REQUEST["account"]!="" ){
            $myline=substr($myline,0,strpos($myline,","));
          }else if ( $_REQUEST["sortip"]!="" ){
            $poslast=strrpos($myline,",");
            $pos1=strrpos($myline,",",$poslast-strlen($myline)-1)+1;
            $myline=substr($myline,$pos1,$poslast-$pos1);
          }else if ( $_REQUEST["sortmodel"]!="" ){
            $pos1=strpos($myline,",");//1
            $pos1=strpos($myline,",",$pos1+1);//2
            $pos1=strpos($myline,",",$pos1+1);//3
            $pos4=strpos($myline,",",$pos1+1);//4
            $myline=substr($myline,$pos1+1,$pos4-$pos1-1);
          }
          
          //echo "[".$myline."]:(s=".$search.");";
          //end of serach line
         //$line_number = (strpos(strtolower($line),strtolower($search)) !== FALSE) ? $key + 1 : $line_number; //case insensitive
          //if (strpos(strtolower($myline),strtolower($search)) !== false){
          if (stripos($myline,$search) !== false){
            if (!$line_number) $line_number=$key+1;
            $total_found++;
          }
        }

        if ($line_number !==false)//first line
          $page=ceil(($line_number)/$pagesize);
        if ($page==0)     $page=1;

    }//if searched      
    //jinho search end 
 } else {
 $page = intval($_GET["page"]); //確認頁數只能夠是數值資料
 $page = ($page > 0) ? $page : 1; //確認頁數大於零
 $page = ($total_page > $page) ? $page : $total_page; //確認使用者沒有輸入太神奇的數字
 }
$start = ($page-1)*$pagesize; //每頁起始資料序號
$stop  =  $page*$pagesize;

###########
for($i=$start;$i<$stop;$i++)
{
  $account[$i]= $total[$i*$item];
  $uid[$i]    = $total[$i*$item+1];
  $uid_cid[$i]= substr($uid[$i],0,3);
  $company[$i]= $total[$i*$item+2];
  $model[$i]  = $total[$i*$item+3];
  $fw[$i]     = $total[$i*$item+4];
        $online_time[$i]= $total[$i*$item+5];
  $ip[$i]   = $total[$i*$item+6];
  $library[$i]  = $total[$i*$item+7];
  // CID group from Device UID
//  if(array_search($account[$i],$account)==$i)
//    $account_group[]=$account[$i];

  if(array_search($uid_cid[$i],$uid_cid)==$i)
    $uid_cid_group[]=$uid_cid[$i];
  if($account[$i] <> "")
    $account_group[$account[$i]]="1";
}

fclose($handle);
for($j=0;$j<sizeof($uid_filter);$j++)
{
  echo $uid_filter[$j];
}
$company_group=array_unique($company);
$uid_cid_count=array_count_values($uid_cid);
$gi = geoip_open("{$home_path}/html/common/GeoIP.dat", GEOIP_STANDARD);
$gi2 = geoip_open("{$home_path}/html/common/GeoLiteCity.dat",GEOIP_STANDARD);


echo "<div class=bg_mid>
\n";
echo "<div class=content>
\n";

############## List Section
echo "<table  class=table_main>";
  echo "<tr class=tr_2>";
    echo "<td colspan=8>";
    echo "<H3>";
    if($oem_id=="N99" or $oem_id="I02")
    {
                        if($_REQUEST["step"] <> "search")
      {
        echo ""._("User Account List")." #: ".(sizeof($account_group));
      }
    }
                        if($_REQUEST["step"] <> "search")
      {
        //echo " | "._("Online Device")." #: ";
        //jinho changed to show total count
        echo " | "._("Total")." / "._("Online Device")." #: ";
        if (array_sum($uid_cid_count)!=0)
          echo ceil($total_dev) ." / ". array_sum($uid_cid_count);
        else //jinho end
          echo array_sum($uid_cid_count); 
                
      }
        if(isset($_REQUEST['total_found'])) $total_found =$_REQUEST['total_found']; //jinho added 
        if(isset($total_found)) echo "Found :".$total_found; //jinho added search result
// seperate the page section
                        echo "  |  "._("Pages").": <a href=?page=1&step={$_REQUEST["step"]}&sortip={$_REQUEST["sortip"]}&uid={$_REQUEST["uid"]}&account={$_REQUEST["account"]}&sortmodel={$_REQUEST["sortmodel"]}&total_found={$total_found}> "._("First")."</a> ";
                        for( $i=1 ; $i<=$total_page ; $i++ ) {
                                 if ( $page-5 < $i && $i < $page+5){
                                  echo "<a href=?page=".$i."&step={$_REQUEST["step"]}&sortip={$_REQUEST["sortip"]}&uid={$_REQUEST["uid"]}&account={$_REQUEST["account"]}&sortmodel={$_REQUEST["sortmodel"]}&total_found={$total_found}>".$i."</a> ";
                                 }
                        } //分頁頁碼
                        echo "  <a href=?page=".$total_page."&step={$_REQUEST["step"]}&sortip={$_REQUEST["sortip"]}&uid={$_REQUEST["uid"]}&account={$_REQUEST["account"]}&sortmodel={$_REQUEST["sortmodel"]}&total_found={$total_found}> "._("Last")."</a>  |  ";
                        echo ''._("Page").' <font color=#FF0033>'.$page.'</font> / '.$total_page.'';

    echo " <a href=".$_SERVER['PHP_SELF'].">Full</a>&nbsp;";
    echo " <a href=".$_SERVER['PHP_SELF']."?debugadmin>Debug</a></td>";
  echo "</tr>";
  echo "</table>";
  ############## Search Section
if (!isset($_REQUEST['debugadmin'])){
echo "<form action='".htmlentities($_SERVER['PHP_SELF'])."' method=post>";
echo ""._("Model")." : <input type=text size=3 name=sortmodel value='".mysql_real_escape_string($_REQUEST["sortmodel"])."'>\n";
  echo ""._("IP")." : <input type=text size=10  name=sortip value='".mysql_real_escape_string($_REQUEST["sortip"])."'>\n";
  echo "<br>";
  echo ""._("Account")." "._("Keyword").": <input type=text name=account value='".mysql_real_escape_string($_REQUEST["account"])."'>\n";
  echo ""._("MAC")." "._("Keyword").": <input type=text name=uid value='".mysql_real_escape_string($_REQUEST["uid"])."'>\n";
  echo "<input type=hidden name=step value=search>\n";
  echo "<input type=submit class=btn_2 value="._("Search").">\n";
 echo "</form>";
#################### list
  /*
  if (isset($_REQUEST["sortip"])){
  array_multisort($ip,SORT_ASC,$uid,SORT_ASC,$account,SORT_ASC,$company,$model,SORT_ASC,$fw,SORT_ASC,$online_time,SORT_ASC, $library,SORT_ASC);
  }else if (isset($_REQUEST["sortmodel"])){
  array_multisort($model,SORT_ASC,$uid,SORT_ASC,$account,SORT_ASC,$company,$fw,SORT_ASC,$online_time,SORT_ASC, $ip,SORT_ASC,$library,SORT_ASC);
  }else*/{
  array_multisort($account,SORT_ASC,$uid,SORT_ASC,$company,$model,SORT_ASC,$fw,SORT_ASC,$online_time,SORT_ASC, $ip,SORT_ASC,$library,SORT_ASC);
  }
}else{
  array_multisort($model,SORT_ASC,$uid,SORT_ASC,$online_time,SORT_ASC,$account,SORT_ASC,$company,$fw,SORT_ASC, $ip,SORT_ASC,$library,SORT_ASC);
}//if debugadmin

$row=1;
  echo "<table class=table_main>";
    echo "<tr class=topic_main>\n";
      echo "<td>"._("Account")."</td>";
      echo "<td>UID</td>\n";
if (!isset($_REQUEST['debugadmin'])){
//      echo "<td>Company</td>\n";
      echo "<td>"._("Model")."</td>\n";
      echo "<td>"._("FW Ver")."</td>\n";
      echo "<td>"._("Online")."</td>\n";
      echo "<td>IP \n";
//      echo "<td>Ver.</td>\n";
}
    echo "</tr>";
  for($i=0;$i<sizeof($uid);$i++)
  {
if (isset($_REQUEST['debugadmin'])){
		if($account[$i] == "") continue;
      echo "<tr class=tr_".($row+$i%2).">"; 
      echo "<td>";  
      echo $account[$i];
      echo "</td>";
      echo "<td nowrap>";
      echo substr($uid[$i],6,12)."<BR>";
      echo "</td>";
      echo "</tr>";
      
}else{    
    if($account[$i] <> "")
    {
      $pos=strripos($account[$i],$_REQUEST["account"]);

if ($_REQUEST["sortip"] <> "")
  $posipid=strripos($ip[$i],$_REQUEST["sortip"]);
else if ($_REQUEST["sortmodel"] <> "")
  $posmodelid=strripos($model[$i],$_REQUEST["sortmodel"]);
else
  $posuid=strripos(substr($uid[$i],0,18),$_REQUEST["uid"]);
    if($pos ===false && $_REQUEST["step"]=="search" && $_REQUEST["account"] <> "") {
    }else if  ($posipid ===false && $_REQUEST["step"]=="search" && $_REQUEST["sortip"] <> ""){
    }else if  ($posmodelid ===false && $_REQUEST["step"]=="search" && $_REQUEST["sortmodel"] <> ""){
    }else if($posuid ===false && $_REQUEST["step"]=="search" && $_REQUEST["uid"] <> ""){
    }else{        

    	if($oem_id == "N99" or $oem_id == substr($uid[$i],0,3) or $oem_id="I02" )   {   
      echo "<tr class=tr_".($row+$i%2).">"; 
      echo "<td>";  
      echo $account[$i];
        echo "</td>";
        echo "<td nowrap>";
          //echo substr($uid[$i],0,18)."<BR>";
        echo "<a href='#' target=popup onclick=\"window.open('online_player.php?user={$account[$i]}&uid=".substr($uid[$i],0,18)."','',config='height=450,width=500')\">".substr($uid[$i],0,18)."</a>";
        echo "&nbsp;&nbsp;<small><a href='#' target=popup onclick=\"window.open('playback_list.php?user={$account[$i]}&mac=".substr($uid[$i],6,12)."','',config='height=650,width=600')\">[List]</a></small><br>";
        echo "</td>";   
        echo "<td>";
        echo $model[$i];
        echo "</td>";
        echo "<td>";
        echo $fw[$i];
        echo "</td>";
        echo "<td nowrap>";

                                                echo $online_time[$i]." /  ";
            $start_date=new DateTime($online_time[$i]);
            $since_start=$start_date->diff(new DateTime(date("Y-m-d H:i:s")));
            echo "<font color=#0000FF>".$since_start->days."</font> d <font color=#0000FF>".($since_start->h)."</font> h <font color=#0000FF>".$since_start->i."</font> m <font color=#0000FF>".$since_start->s."</font> s ";
                                               

          echo "</td>";
          echo "<td nowrap>";
          $country=geoip_country_code_by_addr($gi, $ip[$i]);
          $city   =geoip_record_by_addr($gi2, $ip[$i]);
	        echo country_code_to_country($country);
	        echo "<br>\n";
	        echo $city->city;
	        echo "<BR>{$ip[$i]}";
          echo "</td>";
		      echo "</tr>";
        }  //oem_id check section end
        } // end with search loop
      }//account <>
}//if debugadmin
      } //for
echo "</table>";

?>