<?php
/****************
 *Validated on Mar-27,2015,
 * list isat.user and qlync.account info
 * Updated to Taro's table style 
 *Writer: JinHo, Chang
*****************/
require_once '_auth_.inc';
require_once 'dbutil.php';

if($_REQUEST["step"]=="Add Vendor" )
{
    if(preg_match("/^[A-Z]{3,10}/",strtoupper($_REQUEST["prefix"])) )
        $result = insertAdmin(strtoupper($_REQUEST["prefix"]),$_REQUEST["email"] );
    else $result = false;
    if ($result) $msg_err = "Add Admin Prefix ".strtoupper($_REQUEST["prefix"]). " Success!";
    else $msg_err = "<font color=red><img src=\"css\\error.png\">Add Admin Prefix ".$_REQUEST["prefix"]. " FAIL!</font>";
    
}//if add admin

if($_REQUEST["btnAction"]=="Remove" )
{
  if($_REQUEST["step"]=="deladmin" ){
      $result = deleteAdmin ($_REQUEST["id"]);
      if ($result) $msg_err = "<font color=blue><img src=\"css\\success.png\">Delete Admin ".$_REQUEST["id"]. " SUCCESS!</font>";
      else $msg_err = "<font color=red><img src=\"css\\error.png\">Delete Admin ".$_REQUEST["id"]. " FAIL!</font>"; 
  }
    
}//if Remove

function insertAdmin($prefix, $email)
{
    $sql = "insert into customerservice.vendor_info (cloudprefix, Email) values ('{$prefix}','{$email}')";
    $link = getLink();
    $result=mysql_query($sql,$link);
	if ($result) return true;
  return false;
  
}

function createAdminTable()
{
    $sql = "select * from customerservice.vendor_info";
    $link = getLink();
    $result=mysql_query($sql,$link);
	if ($result){
       	$services = array();
    	   $index = 0;
    	   for($i=0;$i<mysql_num_rows($result);$i++){
            myfetch($arr,$result,$i,0);
            $services[$index] = $arr;
        		$index++;
    	   }//for
	}

  $html = "<table style='margin-top: 5px'><thead><tr><th>ID</th><th>Prefix</th><th>Account Email</th><th></th></tr></thead><tbody>";
  $now = time();
  foreach($services as $service)
  {
		$html.= "\n<tr>\n";
    $html.= "<td>{$service['id']}</td>\n";
    $html.= "<td>{$service['cloudprefix']}</td>\n";
    $html.= "<td>{$service['Email']}</td>\n";
  if(isset($_REQUEST["debugadmin"]) ) {
        $html.= "<td><form action=\"".$_SERVER['PHP_SELF']."\" method=POST>\n";
 
        $html.= "<input type=submit name='btnAction' value=\"Remove\" class=\"btn_blue_short\">\n";
        $html.= "<input type=hidden name='step' value=\"deladmin\" >\n";
        $html.= "<input type=hidden name='debugadmin' value=\"1\" >\n";
        $html.= "<input type=hidden name='id' value=\"{$service['id']}\" >\n";
        $html.= "</form></td\n";
  }else $html.= "<td></td>\n"; 
    
	  $html.= "</tr>\n";
	}
  $html.= "</tbody></table>\n";
	echo $html;
}

function selectQlyncAdmin()
{
    $sql = "select Email from qlync.account";
    sql($sql,$result,$num,0);
    $html = "";
    if ($result){
  	   for($i=0;$i<$num;$i++){
          fetch($arr,$result,$i,0);
          $html.="<option name='Email' value='".$arr[Email]."'>".$arr[Email]."</option>";
        }
    }
    echo $html;  
}
?>
<html>
<head>
<script src="../user_log/js/jquery-1.11.1.min.js"></script>
<link rel=stylesheet type="text/css" href="../user_log/js/style.css">
</head>
<body> 
<div align=center><b><font size=5>Vendor Prefix Management</font></b></div>
<div id="container">
<form method=post action="<?php echo $_SERVER['PHP_SELF'];?>">
<table style="margin-top: 5px">
				<thead>
					<tr>
						<th>Prefix</th>
						<th>Admin Email</th>
						<th>Function</th>

					</tr>
				</thead>
				<tbody>
<tr>
<td>
<input type=text  size=5 name=prefix value='<?php echo $prefix;?>' ><br>
alphabet characters only
</td><td>
<!--input type=text  size=16 name=Email value=''-->
<select name=email>
<?php selectQlyncAdmin(); ?>
</select>
<br>
</td><td>
<?php
if($_SESSION["ID_admin_oem"] or $_SESSION["ID_admin_qlync"]) { ?>
<input type=submit name=step value="Add Vendor" class="btn_1">
<?php }?>
</td></tr>
</tbody>
</table>
</form>
<HR>
<?php
if (isset($msg_err))
echo $msg_err;

if($_SESSION["ID_admin_oem"] or $_SESSION["ID_admin_qlync"]) {
 createAdminTable();
}
?>
	</div>
</body>
</html>  