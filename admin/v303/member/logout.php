<?
include_once("../../header.php");


if($_REQUEST["step"]=="logout")
{
		session_destroy();

//		$_SESSION["ID_qlync"]="";
//		$_SESSION["Status_qlync"]="";
//		$_SESSION["ID_webmaster_qlync"]="";
//		$_SESSION["ID_admin_qlync"]="";
//		$_SESSION["ID_admin_oem_qlync"]="";
		if (!$_SERVER['HTTPS']) //jinho
			header( 'Location: http://' . $_SERVER['HTTP_HOST'] );
		else //jinho
		header("Location:".str_replace("/:",":",$home_url));
		exit();

}
