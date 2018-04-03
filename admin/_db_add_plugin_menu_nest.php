<?php
require_once ("_db_add_plugin_menu.inc");
//reqruied $pluginPath, $menuArray, $menupos, $menuParent/$mvalue[1], right_tree_flag, ID_admin definitions
if (!isMaster()) die("Not Master Admin! Stop update Menu\n");

if (!isset($pluginPath)  or  !isset($menuArray) or !isset($menupos) or !isset($right_tree_flag) )
  die("menu variable is not set.\n");

if (!isset($link))
  $link = connect_db($mysql_ip,$mysql_id,$mysql_pwd,"qlync");

if (file_exists($pluginPath)) //folder
{
//check if plugin menu exist, if not exist=added, if exist Updated
  foreach ($menuArray as $mkey => $mvalue){
    if (strpos($mvalue[0],"http") === false){ //url not found, proceed check
      if (!file_exists(ROOT_FOLDER.$mvalue[0])){
        echo "filePath ".ROOT_FOLDER."{$mvalue[0]} Not Exist. CleanUp Menu!\n";
        cleanMenuDB($mvalue[0],$link);
        printMessage("DELETE Menu",array("Name"=>$mkey,"Link"=>$mvalue[0]));
        continue;
      }
    }
      if (is_null($ID=checkMenuID($link,$mkey,$mvalue[0]))){
        addMenu($link,$mkey,$mvalue[1],$menupos,$mvalue[0]); //level default 1
        printMessage("INSERT Menu",array("Name"=>$mkey,"Link"=>$mvalue[0],"FID"=>$mvalue[1],"OID"=>$menupos));
        $menupos++;
      }else{
        updateMenu($link,$mvalue[1],$ID);
        printMessage("UPDATE Menu({$ID})",array("Name"=>$mkey,"FID"=>$mvalue[1]));
      }
  }

  if ($right_tree_flag)
  {
    foreach ($menuArray as $mkey => $mvalue){
      if (strpos($mvalue[0],"http") === false){ //url not found, proceed check
        if (!file_exists(ROOT_FOLDER.$mvalue[0]))     continue;
      }
      $mID = checkMenuID($link,$mkey,$mvalue[0]);
      if ( is_null(checkRightTreeID($link,$mID,$mvalue[2])) ){
        //ID_0x update AID $mvalue[3]
        if ( (strpos($mvalue[2],"ID_0") !== false) or (strpos($mvalue[2],"ID_1") !== false) ){ //found, skip add
        }else{
          addSubMenuRight($link, $mvalue[2],$mID);
          printMessage("ADD MenuRight({$mID})",array("MenuName"=>$mkey,"Fright"=>$mvalue[2]));
        }
        if (sizeof($mvalue) > 3){//parse array
        if (isAID($link))
          foreach ($mvalue[3] as $AIDarray){
            addSubMenuRight($link, $AIDarray[0],$mID,1,$AIDarray[1]);
            printMessage("ADD MenuRight({$mID})",array("MenuName"=>$mkey,"Fright"=>$AIDarray[0],"AID"=>$AIDarray[1]));
          }
        }
      }else{//update
        if ( (strpos($mvalue[2],"ID_0") !== false) or (strpos($mvalue[2],"ID_1") !== false) ){ //found, skip add
        }else{
          updateMenuRight($link, $mvalue[2],$mID);
          printMessage("UPDATE MenuRight({$mID})",array("MenuName"=>$mkey,"Fright"=>$mvalue[2]));
        }
        //no need update AIDarray
      }
    }
  }
  //php /var/www/qlync_admin/html/common/menu_update.php
}else{
  printf ("{$pluginPath} NOT EXIST!!\n");
}

?>