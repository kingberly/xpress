<?php
/****************
 *Validated on Jan-8,2015,  List OK
 *Writer: JinHo, Chang   
*****************/
//require_once '_auth_.inc';
require_once ("/var/www/qlync_admin/doc/config.php");
require_once 'dbutil.php';
//global value
    
//scandir(DL_PATH, SCANDIR_SORT_DESCENDING), SCANDIR_SORT_DESCENDING=1
$files = scandir(DL_PATH, 1);
?>

<html>
<head>
</head>
<body>
<font color=#000000>
<?php
  for($i=0;$i<sizeof($files);$i++){
        if ($files[$i]=="..")
            break;
?>
          <a href='<?php echo dirname($_SERVER['REQUEST_URI'])."/".DL_PATH.$files[$i];?>' download>Download Billing report <?php echo $files[$i];?></a><br>
<?php
  }
?>
</font>
</body>
</html>
