<?PHP
/****************
 *Validated on Nov-25,2014,  
 * Revised from Qlync header file
 *Writer: JinHo, Chang   
*****************/
include_once("dbutil.php");
include_once("menu_list.php");
if ($_SESSION ==null){
     session_start();
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>Cloud Partner</title>
<meta http-equiv="Content-Language" content="utf-8">
<meta http-equiv="Content-Type" content="text/javascript; charset=utf-8">
<meta http-equiv="Page-Exit" content="revealTrans(Duration=4.0,Transition=2)">
<meta http-equiv="Site-Enter" content="revealTrans(Duration=4.0,Transition=1)">
<link href="css/layout.css" rel="stylesheet" type="text/css"  charset="utf-8" />
<link href="css/form.css" rel="stylesheet" type="text/css"  charset="utf-8" />
<link href="css/menu.css" rel="stylesheet" type="text/css"  charset="utf-8" />
<link href="css/nav.css" rel="stylesheet" type="text/css"  charset="utf-8" />

</head>
<body>
<div class="container">
	<div class="header">
		<div class="logo"><img src="logo.png" /></div>
        <div class="div_navi">
       	<span class="navi"></span>
            <span class="navi"></span>
            <span class="navi"></span> 
		</div>
        <div class="account">
        	<ul>
        	<?php
        		if($_SESSION["ID_qlync"] =="")
        			echo "<li><a href=\"/plugin/licservice/login.php\">log in</a></li>\n";
        		else
        			{
        				echo "<li><a href=\"/plugin/licservice/logout.php?step=logout\">log out</a></li>\n";
        				echo "<li><a href=\"#\">{$_SESSION["Email"]}</a></li>\n";
        			}
        		?>
                
                
            </ul>
        </div>
	</div>
</div>
<div class="shadow_0">
    <div class="shadow_1"></div>
</div>