<?php
/********
 *Add auto Cycle for Share Page for K01, 
 * compatible with v3.0.3 / v2.3.9 and v2.2.1
 *JinHo Change
 ********/
// note that this file is only used for mms play now
include_once( "./include/global.php" );
include_once( "./include/utility.php" );
include_once( "./include/index_title.php" );
if (!IsUserLoggedIn()) {header('Location: login.php'); exit;}

includeOemGlobal( (isset($_SESSION['oem']) ? $_SESSION['oem'] : null) );
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!--[if lt IE 7 ]> <html class="ie6" xmlns="http://www.w3.org/1999/xhtml"> <![endif]-->
<!--[if IE 7 ]>    <html class="ie7" xmlns="http://www.w3.org/1999/xhtml"> <![endif]-->
<!--[if IE 8 ]>    <html class="ie8" xmlns="http://www.w3.org/1999/xhtml"> <![endif]-->
<!--[if IE 9 ]>    <html class="ie9" xmlns="http://www.w3.org/1999/xhtml"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html class="" xmlns="http://www.w3.org/1999/xhtml"> <!--<![endif]-->
<head>
<title>Matrix View</title>

<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><![endif]-->

<?php require( "./include/common_css_include.php" ); ?>
<?php require( "./include/common_js_include.php" ); ?>
<link href="<?php echo $oem_style_list['css']['device_matrix']; ?>" rel="stylesheet" type="text/css" charset="utf-8">
<link href="<?php echo $oem_style_list['css']['pagelist']; ?>" rel="stylesheet" type="text/css" charset="utf-8">
<link href="shadowbox-3.0.3/shadowbox.css?20120708" rel="stylesheet" type="text/css" charset="utf-8">
<script type="text/javascript" src="js/jquery.client.js"></script>
<script type="text/javascript" src="js/device_common.js?20130906"></script>
<script type="text/javascript" src="js/deployJava.js"></script>
<script type="text/javascript" src="js/page_control.js?20120526"></script>
<script type="text/javascript" src="js/jquery.media.js"></script>
<script type="text/javascript" src="js/jquery-ui.min.js"></script>
<script type="text/javascript" src="js/customSort.js"></script>
<script type="text/javascript" src="js/java_vlc_control.js?20120567"></script>
<script type="text/javascript" src="js/device_matrix.js?20130906"></script>
<script type="text/javascript" src="shadowbox-3.0.3/shadowbox.js"></script>
<script type="text/javascript" src="js/googleRelay.js?20120605"></script>
<script type="text/javascript">
	var p2p_jars = "<?php echo $oem_style_list['p2p_jars']; ?>";
	var p2p_vlc_name = "<?php echo $oem_style_list['java_p2p_vlc_title']; ?>";
	var refresh_token = "<?php if (isset($_SESSION["refresh_token"])) echo $_SESSION["refresh_token"]; else echo "";?>"; //"p2p";

	var request_device_type = "<?php if (isset($_SESSION["request_device_type"])) echo $_SESSION["request_device_type"]; else echo "";?>"; //"p2p";
	var request_service_type = "<?php if (isset($_SESSION["request_service_type"])) echo $_SESSION["request_service_type"]; else echo "camera";?>";
	var shared_mode = "true";

	var checkdevice = "<?php if (isset($_GET["checkdevice"])) echo $_GET["checkdevice"]; else echo "";?>";
	var autobinding_cid = "<?php echo $oem_style_list['autobinding']?>";

	//is favorite mode
	var favorite_mode = '<?php if (isset($_GET["favorite_mode"])) echo $_GET["favorite_mode"]; ?>';
	if (favorite_mode=='true')
		favorite_mode = true;
	else
		favorite_mode = false;
	//favorite_list = favorite_list.split(',');

	my_favorite = Array();
	// mode
	var mode = '<?php if(isset($_GET["mode"])) echo $_GET["mode"]; else echo "public"; ?>';
	//if (mode== "" ) mode = "public";
	// get user name
	var user_name = '<?php echo $_SESSION["user_name"];?>';
	var user_id = '<?php echo $_SESSION["user_id"];?>';
	var direct_connection = '<?php echo $oem_style_list['direct_connection']?'true':'false';?>';
	var public_stream_auth = <?php echo PUBLIC_STREAM_AUTH?'true':'false';?>;

	// get a random number for matrix ordery
	var order_by = Math.round((Math.random()+1)*2147483647)%15 + 1;

	var max_nail = 0;

	//timer for snapshot
	var snapshot_timer;

	// if already login, user device id for order

	if( mode != "public" ) order_by = "sequential";//order_by = "sequential";
	//if(favorite_mode) order_by = "1";//order_by = "myfavorite_seq";

	//store sequential list , for debug
	var sequential_list = Array();

	function PageControlCallback( current_page_no )
	{
		RefreshDisplay( mode, $("select#display_type-select").val(), order_by, shared_mode );
	}

	// new page control
	var page_control_object = null;

	$(function(){
		<?php
			//if user login , trigger Java P2P keep alive program
			if(isset($_GET["mode"]) && $_GET["mode"]=="personal" && isset($_SESSION["user_id"]) && $_SESSION["user_id"]!=""){
				echo "try{";
				echo "parent.initJavaBackStage();";
				echo "} catch(err) {}";
			}
		?>
		// get user pref matrix display mode
		var matrix_display_mode = '<?php echo $_SESSION["user_matrix_display_mode"]; ?>';
		if( matrix_display_mode == "" || isNaN(parseInt(matrix_display_mode)) ) matrix_display_mode = 3; // default to 3x3
		else matrix_display_mode = parseInt(matrix_display_mode);
		$("select#display_type-select").val(matrix_display_mode);

		// setup the page control button layer
		setupBtnLayer();

		// generate hte page control object
		InitializePageControl(
			"select#page_control-select",
			"div#page_first-btn",
			"div#page_prev-btn",
			"div#page_next-btn",
			"div#page_last-btn",
			PageControlCallback
		);

		RefreshDisplay( mode, $("select#display_type-select").val(), order_by, shared_mode );

		// display type on change
		$("select#display_type-select").change(function(){
			RefreshDisplay( mode, $("select#display_type-select").val(), order_by, shared_mode );
		});

		ActiveShadowBoxInit();
		setTimeout("QuerySignal(true);",2000);
		singal_timer = self.setInterval("QuerySignal(true);", 10000);
		self.setInterval("changeSignalLight();", 1000);

		if (!autobinding_cid)
			setTimeout("checkDeviceCount();",700);
	});
</script>
</head>
<body>
<div class="off" id="frame_loading_cover"></div>
<!--display mode select-->
<select id="display_type-select" style="float:left ; display:none">
	<option value="2">2X2</option>
	<option value="3">3X3</option>
	<option value="4">4X4</option>
</select>

<!--page control select-->
<select id="page_control-select" style="float:left; display:none"></select>

<div id="toolbar_div">
	<div class="toolbarL">
	<div id="btn_plus" class="displaynone" onclick="OpenActiveBox();"></div>

        <?php if (defined('OEM_URL')): ?>
        <div id="btn_enlarge" title="<?php echo _('Enlarge') ?>" onclick="OpenLargeMatrix();"></div>
        <?php endif; ?>
    </div>
    <div class="toolbarR">


    </div>
</div>
<div id="device_matrix" class="left_list"></div>

<div id="downer_div">
	<div id="whole_button_cover">
	<!--page control buttons-->
	<div class="_page_button_cover">
        <div id="page_first-btn" class="btn_sharef floatleft" title="<?php echo _("First Page");?>"><span class="btn_page_firstpage"></span></div>
        <div id="page_prev-btn" class="btn_sharef floatleft"  title="<?php echo _("Previous Page"); ?>"><span class="btn_page_pre"></span><?php echo _('Prev');?></div>
        <div id="page_next-btn" class="btn_sharef floatleft"  title="<?php echo _("Next Page"); ?>"><?php echo _('Next');?><span class="btn_page_next"></span></div>
        <div id="page_last-btn" class="btn_sharef floatleft"  title="<?php echo _("Last Page"); ?>"><span class="btn_page_lastpage"></span></div>
	</div>
	<div id="_page_button">
		<div class="center_cover">
			<input id="_page_now" type="text" value="" id="" /> / <span id="_page_total">0</span>
			<!-- jinho added to share page auto cycle--->
			<span id='auto_cycle'>
				<div id='cb_auto_cycle' class='_checkbox-disable' style='display: inline-block'></div><?php echo _("Auto Cycle");?>
		</div>
	</div>
</div>


<?php include_once("./include/tail.php"); ?>
</body>
</html>
