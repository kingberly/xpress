<?php
include_once( "./include/global.php" );
include_once( "./include/index_title.php" );
include_once( "./include/utility.php" );
includeOemGlobal( (isset($_SESSION['oem']) ? $_SESSION['oem'] : null) );

// Remove return_url if r != y
if (!isset($_GET['r']) || $_GET['r'] != 'y') {
	unset($_SESSION['return_url']);
}

if (FORCE_HTTPS && !$_SERVER['HTTPS']) {
	header( 'Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] ) ;
	exit(0);
}

if (isset($_SESSION['google_openid'])) {
	$name = $_SESSION['reg_email'];
}
elseif (isset($_SESSION['user_name'])) {
	$name = $_SESSION['user_name'];
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!--[if lt IE 7 ]> <html class="ie6"> <![endif]-->
<!--[if IE 7 ]>    <html class="ie7"> <![endif]-->
<!--[if IE 8 ]>    <html class="ie8"> <![endif]-->
<!--[if IE 9 ]>    <html class="ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html class=""> <!--<![endif]-->
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<!--[if IE]><meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1"><![endif]-->
<title><?php echo $oem_style_list['title']; ?></title>
<link href="<?php echo $oem_style_list['css']['default']; ?>" rel="stylesheet" type="text/css" charset="utf-8">
<link href="<?php echo $oem_style_list['css']['secondpage']; ?>" rel="stylesheet" type="text/css" charset="utf-8">
<link href="<?php echo $oem_style_list['css']['timeline']; ?>" rel="stylesheet" type="text/css" charset="utf-8">
<link href="<?php echo $oem_style_list['css']['index']; ?>" rel="stylesheet" type="text/css" charset="utf-8">
<link href="<?php echo $oem_style_list['css']['button']; ?>" rel="stylesheet" type="text/css" charset="utf-8">
<link href="css/menu.css" rel="stylesheet" type="text/css" charset="utf-8">
<link href="css/jquery-ui.min.css" rel="stylesheet" type="text/css" charset="utf-8">
<link href="shadowbox-3.0.3/shadowbox.css?20120708" rel="stylesheet" type="text/css" charset="utf-8">
<link href="css/event_download.css" rel="stylesheet" type="text/css" charset="utf-8">

<script type="text/javascript">
var CONFIG = <?php echo json_encode(array(
    'ROOT_URL' => ROOT_URL,
    'OEM_URL' => (defined('OEM_URL') ? OEM_URL : ROOT_URL)
)) ?>;
</script>

<script type="text/javascript" src="js/translatejs.php"></script>
<script type="text/javascript" src="js/jquery-1.7.2.min.js"></script>
<script type="text/javascript" src="js/jquery-ui.min.js"></script>
<script type="text/javascript" src="js/jquery.plugin.menuTree.pack.js"></script>
<script type="text/javascript" src="js/device_common.js?20140331"></script>
<script type="text/javascript" src="js/jquery.tools.overlay.min.js"></script>
<script type="text/javascript" src="js/jquery.client.js"></script>
<script type="text/javascript" src="js/common.js?20120568"></script>


<script type="text/javascript" src="js/java_camera_control.js?20130906"></script>
<script type="text/javascript" src="js/timeline.js?20130906"></script>
<script type="text/javascript" src="js/event_download.js"></script>
<script type="text/javascript" src="js/index.js?20120545"></script>
<script type="text/javascript" src="js/googleRelay.js?20120608"></script>

<script type="text/javascript" src="shadowbox-3.0.3/shadowbox.js"></script>
<script type="text/javascript">
	var p2p_jars = "<?php echo $oem_style_list['p2p_jars']; ?>";
	var request_device_type = "<?php if (isset($_SESSION["request_device_type"])) echo $_SESSION["request_device_type"]; else echo "";?>";
	var request_service_type = "<?php if (isset($_SESSION["request_service_type"])) echo $_SESSION["request_service_type"]; else echo "camera";?>";
	var JavaAPI_ver = 2;

	// init shadowbox for map selector
	Shadowbox.init({
		// let's skip the automatic setup because we don't have any
		// properly configured link elements on the page
		skipSetup: true
	});
	// get username/login count from session
	var user_id = '<?php if (isset($_SESSION["user_id"])) echo $_SESSION["user_id"]; ?>';
	var user_group_id = '<?php if (isset($_SESSION["user_group_id"])) echo $_SESSION["user_group_id"]; ?>';
	var user_name = '<?php echo $name; ?>';
	//var user_login_count = '<?php if (isset($_SESSION["user_login_count"])) echo $_SESSION["user_login_count"]; ?>';
	var user_favorite = '<?php if (isset($_SESSION["favorite"])) echo $_SESSION["favorite"]; ?>';
	var assigniframe = '<?php if (isset($_GET["thishref"]) && $_GET["thishref"]=="true" && isset($_SESSION["thishref"])) echo $_SESSION["thishref"]; ?>';
	relayVar.auth = <?php if (isset($_SESSION["refresh_token"]) && $_SESSION["refresh_token"]!="") {$hasGoogleAuth = true; echo "true";} else {$hasGoogleAuth = false; echo "false";}?>;

	// get current language
	var user_language = '<?php if (isset($_SESSION["user_language"])) echo $_SESSION["user_language"]; ?>';

	var _gaq = _gaq || [];
 	_gaq.push(['_setAccount', 'UA-5862912-11']);
 	_gaq.push(['_trackPageview']);

	$(function(){
        if ($.client.os=="Mac")
			$('#vlc_cover .background_overlay').addClass('off');

		if (request_service_type == "remotedesktop") {
			toActive("menu_device");
		}
		else if(request_service_type == "" && request_device_type == ""){
			toActive("menu_setting");
		}
		else{
			toActive("menu_device");
		}

		var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
    	ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
	var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);

		// set menu width & content width
		$("div#main div#main_menu").width(200);
		//$("div#main div#main_frame").width($(window).width()-250);

		// display menu quick view item
		RefreshMenuDisplay( null );

		// check user name
		// have logged in
		if( user_name != "" )
		{
			// show welcome msg
			$('#welcome_msg_prefix').html(t_js.index_MSG.Welcome);
			$('#header_my_account').html(user_name);
			$('#header_logout').html(t_js.index_MSG.Logout);


			// gray central manage for normal user
			$("li#central_management-li").show();
			//alert(user_group_id);
			if(user_group_id == "0" )
			{
				$('#menu_user_list').show();
				//$('#menu_analysis').show();
			}
			else
			{
				$('#menu_user_list').hide();
			}
		}
		// not login
		else
		{
			// reset welcome msg
			$("#welcome_msg-h1").hide();
			$("#logout-h1").hide();
			$("#logoinfo").hide();

			$("li#central_management-li").hide();

			$('#menubar .menubar_title').eq(0).hide();
			$('#menubar .menubar_title').eq(2).hide();
			$('#menubar .menubar_title').eq(3).hide();
			$('#menubar .menubar_title').eq(4).show();
			$('#menubar .menubar_title').eq(5).hide();

			$('#my_favorite').hide();
		}

		// select the current language
		$("select#user_language-select").val( user_language );

		// change language on change
		$("select#user_language-select").change(function(){
			// send data
			$.getJSON(
				"backstage_language.php",
				{
					command: "switch",
					user_language: $("select#user_language-select").val()
				},
				function( data )
				{
					if( data.status == "success" ) window.location.replace("index.php");
				}
			);
		});

		if (assigniframe!="")
			open_page(assigniframe);

		$(".bottom_menu").bind("click", function() {
			var id = this.id;
			if (id == "menu_device") {
				$(".menu_device").show();
				$(".menu_setting").hide();
			}
			else if (id == "menu_setting") {
				$(".menu_device").hide();
				$(".menu_setting").show();
			}
			else {
				$(".menu_device").hide();
				$(".menu_setting").hide();
			}
		});

		timeline_init();


        $(document).on('focus.autoselect', '.autoselect', function(event) {
            setTimeout(function() {
                event.currentTarget.select();
            });
        });
	});
	function CloseCover(){
		$('#vlc_cover').addClass('off');
		//$('#vlc_cover').removeClass('off');
	}
	function toActive(thisid){
		$('ul.navi').find('li').removeClass('activate');
		$('#'+thisid).addClass('activate');
	}
</script>
</head>
<body>
	<div class='timeline_pop_menu displaynone'>
		<div class="pop_menu_up"><div class="datalist"></div></div>
		<div class="pop_menu_down">
			<div class="pop_menu_bothside"></div>
			<div class="pop_menu_bothside" style="right:0px;"></div>
			<div class="pop_menu_downside"></div>
		</div>
		<div class="pop_hover"></div>
	</div>
	<div id="vlc_cover" class="off">
		<div class="app_agent_layer" id="c_c_layer">
		</div>
		<div class="_matrixbox_close" onclick="CloseVLCFrame();"></div>
		<div class="background_overlay"></div>
		<div class="Javatutorial">
			<div id="java_unsupport" class='java_ps off'>
                <div class="close" onclick="CloseVLCFrame();"></div>
                <?php echo _('Please use Chrome, Firefox, or Internet Explorer') ?>
			</div>
			<div id="java_ps" class='java_ps off'>
                <div class="close" onclick="CloseVLCFrame();"></div>
				<div onclick="openJavaTutorial(1);" class="btn_sharef" style="position:absolute;left:350px;top:42px;">Click Here</div>
				<div onclick="openJavaTutorial(2);" class="btn_sharef" style="position:absolute;left:350px;top:87px;">Click Here</div>
			</div>
            <div class="java_tut_install off" style="width: 1100px">
                <div class="close" onclick="CloseVLCFrame();"></div>
                <div id="java_link" onclick="window.open('http://www.java.com');"></div>
                <div id="java_pic_link" onclick="window.open('http://www.java.com');"></div>
                <img src="" style="">
            </div>
			<div class="java_installed off" style="width: 1100px">
                <div class="close" onclick="CloseVLCFrame();"></div>
                <div class="legend legend-chrome displaynone">
                    <div class="desc">
                        Install Extension IE Tab<input class="autoselect" type="text" value="https://chrome.google.com/webstore/detail/ie-tab/hehijbfgiekmjfkfjpbkbammjbdenadd?hl=zh-TW" />, enable as IE10 force standard mode. 
                        <!--In the address bar, enter <input class="autoselect" type="text" value="chrome://flags/#enable-npapi" />, then click enable button.-->
                    </div>
                    <div class="desc">
                        Click relaunch button to restart chrome.
                    </div>
                </div>
                <div class="image"><img src=""></div>
			</div>
		</div>

		<div class="playback_panel" id="playback_main">
			<div class="small_overlay displaynone"></div>
			<div class="small_overlay_text loading_bg displaynone"></div>
			<div class="videoframe">
				<div class="video_bg">
					<div class="video_border">
						<div id="video_pop_alert" class="off">

						</div>
						<div id="v_relay" class="v_relay_alert_layer ">
							<div class="btn_message_layer">
							</div>
							<div class="btn_2_layer">
								<div class="btn_sharef" id="btn_relay_reconnect">Reconnect</div>
							</div>
						</div>
						<div class="v_content" id="center_frame">

						</div>
					</div>
					<div class="li_button li_up" onclick="devicePageUp();"></div>
					<ul class="live_list">
						<li id="Camera_1" class="" onclick="Change_Cam(0);"></li>
						<li id="Camera_2" class="" onclick="Change_Cam(1);"></li>
						<li id="Camera_3" class="" onclick="Change_Cam(2);"></li>
						<li id="Camera_4" class="" onclick="Change_Cam(3);"></li>
						<li id="Camera_5" class="" onclick="Change_Cam(4);"></li>
						<li id="Camera_6" class="" onclick="Change_Cam(5);"></li>
					</ul>
					<div class="li_button li_down" onclick="devicePageDown();"></div>
				</div>
				<div class="buttonbar">
					<div class="signal_light_position">
						<div class="signal_light signal_light_green"></div>
						<div id="event_download_holder"><div id="event_download_indicator">
							<span id="event_download_name"></span>
							<div id="event_download_progress_background">
								<div id="event_download_progress"></div>
							</div>
							<span id="event_download_percentage"></span>
							<span id="event_download_status"></span>
						</div></div>
					</div>
					<div class="mode_block">
						<span id="mode_3" class="mode_button" onclick="change_video_mode(3);"><?php echo _('Cloud');?></span>
						<span id="mode_1" class="mode_button" onclick="change_video_mode(1);"><?php echo _('Playback');?></span>
						<span id="mode_2" class="mode_button mode_button_selected" onclick="change_video_mode(2);"><?php echo _('Live View');?></span>
					</div>
					<div class="tool_block">
						<!--span class="tool_button tool_btn_close" onclick="CloseVLCFrame();"></span-->
						<span class="tool_button tool_btn_setup" onclick="window.main_frame_iframe.setup_player();"></span>
						<span class="tool_button tool_btn_refresh" onclick="refreshPlayer();"></span>
						<span id="muteBtn" class="tool_button tool_btn_mute_silent" onclick="mutePlayer();"></span>
						<span class="tool_button tool_btn_snapshot" onclick="SnapShotPlayer();"></span>
						<span class="tool_button tool_btn_fullscreen displaynone"></span>
						<!--span class="tool_button tool_btn_ptz"></span-->
						<span class="tool_button tool_btn_matrix"  onclick="CloseVLCFrame();"></span>
						<span id="tool_btn_audio" class="tool_button tool_btn_audio_disabled" onclick="ToggleTwoWayAudio();"></span>
						<?php if ($oem_style_list['brand'] == "iPixord"|| $oem_style_list['brand'] == "CloudLync") echo '<span id="tool_btn_pixord_split" class="pixord_camera_case tool_button tool_btn_split_single" onclick="TogglePixordSplitMode(this);"></span>';?>
						<span class="tool_timer"></span>
					</div>
				</div>
			</div>
			<div class="timeline_selecter">
				<div class="timeline_condition" id="timeline_hour" onclick="new_timeline('hour');"><?php echo _('Hour');?></div>
				<div class="timeline_condition" id="timeline_day" onclick="new_timeline('day');"><?php echo _('Day');?></div>
				<div class="timeline_condition timeline_condition_selected" id="timeline_week" onclick="new_timeline('week');"><?php echo _('Week');?></div>
			</div>
			<div id="slider" class="timeline_slider"></div>

			<div class="timeline_div_left"></div>
			<div class="timeline_div_top"></div>
			<div class="timeline_div">
				<div id='scheduled_popup' class='scheduled_popup'></div>
				<div class="inner_timeline" id="main_inner_timeline">
				</div>
			</div>
			<div class="timeline_div_right"></div>
			<div class="timeline_div_bottom"></div>
			<span id="timeline_datepicker_wrapper">
				<label><?php echo _('Select date:');?></label>
				<input type="date" id="timeline_datepicker" />
			</span>
			<div class='control_playback_panel_table displaynone'>
				<table>
					 <tr>
					 <td><div class="control_button control_stop" onclick="window.main_frame_iframe.stop_player();"></div></td>
					 <td><div class="control_button control_play" onclick="window.main_frame_iframe.play_player();"></div></td>
					 <td><div class="control_button control_pause" onclick="window.main_frame_iframe.pause_player();"></div></td>
					 </tr>
					 <tr>
						 <td style="text-align:center;" colspan="3">
							<div class="control_button control_backward" style="display:inline-block;margin-right:-5px;" onclick="player_backward();"></div>
							<div class="control_button control_forward" style="display:inline-block;" onclick="player_forward();"></div>
						 </td>
					 </tr>
					 <tr>
						<td style="text-align:center;" colspan="3">
						</td>
					 </tr>
				</table>
				<div style="position:absolute;left:-20px;" id="vlc_control">
				</div>
			</div>
			<div class='ptz_overlay displaynone'></div>
			<div id="pixord_camera_case" class='pixord_camera_case camera_ptz_selector displaynone'>
				<div class="camera_ptz_condition camera_ptz_condition_selected" onclick="camera_ptz_click(0);">Cam 1</div>
				<div class="camera_ptz_condition" style="margin-left: 16px;" onclick="camera_ptz_click(1);">Cam 2</div>
			</div>
			<table class='control_panel_table'>
				 <tr>
				 <td onClick="ShowSingle(7);"><div class="control_button control_button_pointer control_left_up"></div></td>
				 <td onClick="ShowSingle(8);"><div class="control_button control_button_pointer  control_up"></div></td>
				 <td onClick="ShowSingle(9);"><div class="control_button control_button_pointer  control_right_up"></div></td>
				 <td onClick="ShowSingle(10);"><div class="control_mini control_button_pointer control_add"></div></td>
				 </tr>
				 <tr>
				 <td onClick="ShowSingle(4);"><div class="control_button control_button_pointer  control_left"></div></td>
				 <td onClick="ShowSingle(5);"><div class="control_button control_button_pointer  control_home"></div></td>
				 <td onClick="ShowSingle(6);"><div class="control_button control_button_pointer  control_right"></div></td>
				 <td onClick=""></td>
				 </tr>
				 <tr>
				 <td onClick="ShowSingle(1);"><div class="control_button control_button_pointer  control_left_down"></div></td>
				 <td onClick="ShowSingle(2);"><div class="control_button control_button_pointer  control_down"></div></td>
				 <td onClick="ShowSingle(3);"><div class="control_button control_button_pointer  control_right_down"></div></td>
				 <td onClick="ShowSingle(11);"><div class="control_mini control_button_pointer control_minus"></div></td>
				 </tr>
			 </table>
		</div>
		<!--div class="inner_timeline">
			</div>
			<input id ="" type="button" value="Left" onclick="SlideLeft()">
			<input id ="" type="button" value="ToTop" onclick="new_timeline()">
			<input id ="" type="button" value="Right" onclick="SlideRight()">
			<div onClick="getTimeLineList();" class="off">Test Click</div-->
	</div>
	<div id="banner">

    	<div class="bannerMain">
        	<div class="info_login">
				<span id="welcome_msg-h1">
					<span id='welcome_msg_prefix'></span>
					<a href='my_account.php' id='header_my_account' class='state-active open_page account_row'></a>!
				</span>
				<span id="logout-h1"><a href="logout.php" id="header_logout" class="state-active open_page account_row"></a></span>
		  <label>
            <select id="user_language-select">
              <option value="cs_CZ">Čeština</option>
              <option value="de_DE">Deutsch</option>
              <option selected="selected" value="en_US">English</option>
              <option value="fr_FR">Français</option>
              <option value="es_ES">Español</option>
              <option value="es_US">Español (Estados Unidos)</option>
              <option value="it_IT">Italiano</option>
              <option value="ja_JP">日本語</option>
              <option value="ko_KR">한국어</option>
              <option value="nl_NL">Nederlands</option>
              <option value="pl_PL">Polski</option>
              <option value="ru_RU">Pyccĸий</option>
              <option value="tr_TR">Tϋrkçe</option>
              <option value="zh_CN">简中</option>
              <option value="zh_TW">繁中</option>
            </select>
   	 	  </label>
   	        </div>
            <div class="bannerLogo">
		<div id="logoinfo">
                    <ul>
                    <li><a id="menu_device" class="open_page bottom_menu" href="device_matrix.php?mode=<?php echo $mode;?>" onClick="toActive(this.id);"><?php echo _("Live View"); ?></a></li>
                    <li><a id="menu_setting" class="open_page bottom_menu" href="device_list.php?mode=<?php echo $mode;?>" onClick="toActive(this.id);"><?php echo _("Setting"); ?></a></li>
                    <li><a id="menu_share" class="navi_link open_page bottom_menu" href="shared_matrix.php?mode=<?php echo $mode;?>" onClick="toActive(this.id);"><?php echo _("Share");?></a></li>
					<?php
						if (isset($_SESSION["user_group_id"]) && $_SESSION["user_group_id"]==0){
							echo '<li><a id="menu_analysis" style="display:none;" class="navi_link open_page bottom_menu" onClick="toActive(this.id);" href="user_list.php?mode=' . $mode . '">Analysis</a></li>';
							echo '<li><a id="menu_user_list" style="display:none;" class="navi_link open_page bottom_menu" onClick="toActive(this.id);" href="user_list.php?mode=' . $mode . '">Admin</a></li>';
						}
					?>
                    </ul>
                </div>
            </div>
			<div id="<?php echo $oem_style_list['logo_id']; ?>" class="<?php echo $oem_style_list['logo_class']; ?>"></div>
        </div>
        <div class="line_banner"></div>
	</div>
    <div id="minimal-banner">
        <div class="bannerMain clearfix">
            <div class="floatright">
                <a class="btn-logout unselectable" href="logout.php" id="header_logout" class="state-active open_page account_row"><?php echo _('Logout') ?></a>
            </div>
        </div>
    </div>
	<div id="main">

		<div id="main_menu_side"></div>
		<div id="main_frame">
			<div id="overlay_mask"></div>
				<?php if (isset($_SESSION["user_id"])) $mode = "personal"; else $mode = "public"; ?>
			<div id="main_top_frame">
				<div id="toolbarR">  <!--16px upper each time, when click "Create Account" under firefox, strange bug-->
					<div class="act_1 open_page menu_device" style="float: right;" href="device_matrix.php?mode=<?php echo $mode;?>"><?php echo _("Matrix"); ?></div>
					<div class="act_1 open_page menu_device" style="float: right;" href="device_map.php?mode=<?php echo $mode;?>"><?php echo _("Map"); ?></div>
					<div class="act_1 open_page menu_setting" style="float: right; display: none;" href="device_list.php?mode=<?php echo $mode;?>"><?php echo _("Device"); ?></div>
					<div class="act_1 open_page menu_setting" style="float: right; display: none;" href="my_account"><?php echo _("Account"); ?></div>
					<div class="select_cover">
					</div>
				</div>
			</div>

		<div id="main_down_frame">
			<iframe id="main_frame_iframe" name="main_frame_iframe" src="<?php
				if( $_GET["view_location"] == "" ) print('login.php'); // print( "device_map.php?mode=" . $mode );
				else if ( $_GET["view_location"] == "login.php?mode=google" ) print('login.php?mode=google');
				else if ( $_GET["view_location"] == "user_register.php" ) print('user_register.php');
				else if ( $_GET["view_location"] == "password_recovery.php" ) print('password_recovery.php');
				else print( $_GET["view_location"] . "?mode=" . $mode . "&page_no=" . $_GET["page_no"] . "&device_id=" . $_GET["device_id"] . "&tab=" . $_GET["tab"] );
		?>" frameborder="0" border="0" cellspacing="0" onLoad="CloseCover();CloseIFrame();"></iframe>
			</div>

        <div id="footer_div">
			<div id="footer_logo" style="<?php echo $oem_style_list['footer_logo_style']; ?>" class="<?php echo $oem_style_list['footer_logo_class']; ?>"><?php echo $oem_style_list['footer_logo_html']; ?></div>
			<div id="copyright"><?php echo "Version " . SVW_VERSION . SVN_REVISION . "<br />" . $oem_style_list['copyright_html']; ?></div>
            <div id="bottom_menu_div">
                <ul class="navi">
                </ul>
			</div>
		</div>
	</div>
	</div>
    <div id="login_id" style="display:none" ><?php if (isset($_SESSION["user_id"])) echo $_SESSION["user_id"]; ?></div>
    <div id="apply_overlay">
		<iframe></iframe>
	</div>

	<div id="hidden_iframe" style="display:none;"><iframe></iframe></div>
	<iframe id="p2p_applet_layer" name="p2p_applet_layer" style="visibility:hidden;position:absolute;top:0;height:1px;width:1px;"></iframe>
	<div id="xmlresult" style="display:none;"></div>
</body>
</html>
