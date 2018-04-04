<?php
/****************
 *Validated on Nov-25,2014,  
 * revised from Qlync logout
 * Apply only running as stand alone plugin  
 *Writer: JinHo, Chang   
*****************/
include_once("header.php");


if($_REQUEST["step"]=="logout")
{
		session_destroy();
		
		header("Location:/plugin/licservice/login.php");
		exit();

}
?>