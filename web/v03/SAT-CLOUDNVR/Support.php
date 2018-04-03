<?php
include_once( "./include/global.php" );
include_once( "./include/utility.php" );
include_once( "./include/index_title.php" );
if (!IsUserLoggedIn()) {header('Location: login.php'); exit;}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Support</title>
<?php require( "./include/common_css_include.php" ); ?>
	<!--Added New CSS-->
<link href="css/bootstrap.min.css" rel="stylesheet">
<link href="css/stick-footer.css" rel="stylesheet">
<link rel="stylesheet" href="css/normalize.css">
<link rel="stylesheet" href="css/main.css">
<?php require( "./include/common_js_include.php" ); ?>
<link href="css/device_list.css" rel="stylesheet" type="text/css" charset="utf-8">
<link href="<?php echo $oem_style_list['css']['pagelist']; ?>" rel="stylesheet" type="text/css" charset="utf-8">
<link href="shadowbox-3.0.3/shadowbox.css" rel="stylesheet" type="text/css" charset="utf-8">
<script type="text/javascript" src="js/panel.js"></script>
<script type="text/javascript" src="js/googleRelay.js?20120522"></script>
<script type="text/javascript" src="js/device_common.js?20140331"></script>
<script type="text/javascript" src="js/device_list.js?20120523"></script>
<script type="text/javascript" src="shadowbox-3.0.3/shadowbox.js"></script>
<script type="text/javascript" src="js/pagelist.js"></script>

<!--Added New JS -->
<script src="js/vendor/modernizr-2.6.2.min.js"></script>

</head>
<body>
    <div id="support_container" >
 
        <div id='header' >
            <h2 class="page-header">Hỗ trợ</h1>
        </div>
        <div id='faq'>
            <h3 class="page-header">FAQ – Những câu hỏi thường gặp</h3>
        </div>
        <div id='updates'>
             <h3 class="page-header">Nâng cấp dịch vụ</h3>
        </div>
        <div id='kBase'>
            <h3 class="page-header">Kiến thức cơ bản</h3>
            <p>Tài liệu về VNPT Cam và các camera ở đây!</p>
           <p> Z-3022<br />
            <a href="https://<?php echo $_SERVER['HTTP_HOST'];?>:8080/doc/Z3022_install_user_guide_VN.pdf" target="_blank">Hướng dẫn cài đặt và sử dụng</a></p>
                        
            <p>Z-3202PT<br />
            <a href="https://<?php echo $_SERVER['HTTP_HOST'];?>:8080/doc/Z3202PT_install_user_guide_VN.pdf" target="_blank">Hướng dẫn cài đặt và sử dụng</a></p>
            
            <p>Z-3503<br />
           <a href="https://<?php echo $_SERVER['HTTP_HOST'];?>:8080/doc/Z3503_install_user_guide_VN.pdf" target="_blank">Hướng dẫn cài đặt và sử dụng</a></p>

        </div>
</div>
<?php include_once("./include/tail.php"); ?>

<script>
$('document').ready(function(){
   bMarks('h3','header');
});
</script>
	
        <script src="js/plugins.js"></script>
        <script src="js/main.js"></script>
        <script src="js/vendor/bootstrap.min.js"></script>
</body>
</html>
