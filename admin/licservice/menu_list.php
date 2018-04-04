<?php
/****************
 *Validated on Nov-25,2014,  
 * revised from Qlync menu
 * Apply only running as stand alone plugin  
 *Writer: JinHo, Chang   
*****************/
$menu1[1]["name"]="Account Mgr.";
$menu1[1]["valid"]=1;
$menu1[1]["fright"][0]="ID_none";
$menu1[6]["name"]="License";
$menu1[6]["valid"]=0;
$menu1[6]["fright"][0]="ID_fae";
$menu1[6]["fright"][1]="ID_admin_oem";
$menu1[6]["fright"][2]="ID_admin";
$menu1[55]["name"]="Support";
$menu1[55]["valid"]=0;
$menu1[55]["fright"][0]="ID_admin";
$menu1[55]["fright"][1]="ID_admin_oem";
$menu1[55]["fright"][2]="ID_fae";
$menu1[55]["fright"][3]="ID_none";
$menu1[1][2]["name"]="Login";
$menu1[1][2]["link"]="/plugin/licservice/login.php";
$menu1[1][2]["valid"]=1;
$menu1[1][2]["fright"][0]="ID_none";
$menu1[1][4]["name"]="Account Information";
$menu1[1][4]["link"]="/plugin/licservice/maintain.php";
$menu1[1][4]["valid"]=1;
$menu1[1][4]["fright"][0]="ID_fae";
$menu1[1][4]["fright"][1]="ID_admin_oem";
$menu1[1][4]["fright"][2]="ID_admin";
$menu1[6][8]["name"]="Camera License List";
$menu1[6][8]["link"]="/plugin/licservice/listLicensePage.php";
$menu1[6][8]["valid"]=0;
$menu1[6][8]["fright"][0]="ID_admin";
$menu1[6][8]["fright"][1]="ID_admin_oem";
$menu1[6][8]["fright"][2]="ID_fae";
$menu1[6][46]["name"]="Camera License Upload";
$menu1[6][46]["link"]="/plugin/licservice/addLicense_cam.php";
$menu1[6][46]["valid"]=0;
$menu1[6][46]["fright"][0]="ID_admin";
$menu1[6][46]["fright"][1]="ID_admin_oem";
$menu1[6][48]["name"]="Tunnel Server License Upload";
$menu1[6][48]["link"]="/plugin/licservice/addLicense_tun.php";
$menu1[6][48]["valid"]=0;
$menu1[6][48]["fright"][0]="ID_admin";
$menu1[6][85]["name"]="Stream Server License Upload";
$menu1[6][85]["link"]="/plugin/licservice/addLicense_evo.php";
$menu1[6][85]["valid"]=0;
$menu1[6][85]["fright"][0]="ID_admin";
/*
$menu1[55][56]["name"]="Camera Note Update";
$menu1[55][56]["link"]="/plugin/licservice/addLicense_camNote.php";
$menu1[55][56]["valid"]=0;
$menu1[55][56]["fright"][0]="ID_admin_oem";
$menu1[55][56]["fright"][1]="ID_fae";
$menu1[55][56]["fright"][2]="ID_admin";
$menu1[55][56]["name"]="Camera Note Update";
$menu1[55][57]["link"]="/plugin/licservice/listLicensePageNote.php";
$menu1[55][57]["valid"]=0;
$menu1[55][57]["fright"][0]="ID_admin_oem";
$menu1[55][57]["fright"][1]="ID_fae";
$menu1[55][57]["fright"][2]="ID_admin";
*/
?>