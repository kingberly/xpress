//timeline_datepicker , timeline()
var timeline_timer = 0; //timeLine Timeout Timer
var timeline_guid = ""; //timeLine unique guid , for query data
var timeline_data = null; //Timeline XML data
var nvr_event_data = null;
var nvr_scheduled_data = null;
var nvr_dataplan = "";
var cloud_nvr_data = null;
var cloud_nvr_event_data = null;
var google_backup_data = null;
var downloadtimeout = 0; //Java Agent Timer, prevent duplicate download
var nvsr_progress_interval = 0;
var nvsr_progress_right = 0;
var timeline_returned = {};

var nowPlayingFile = null;
var control_panel = { "ptz": false, "playback": false, "zoom": false, "fisheye_multiwindow": false};
var TimeLineVar = {
	now_timeline_width : 0,
	listDeviceTimeout : 0,
	smallOverlayTimeout : 0, //smalloverlay Timer, prevent duplicate overlay
	showWarningMessageTimeout : 0, //warning message Timer, prevent duplicate message
	VideoName : "",
	googleRelayMode : false,
	videoMode: 1,
	nowP2PIndex: -1,
	buttonLock: false,
	CameraState : null,
	phase: "week",
	pixord_models : {"PB731":true,"PB731I":true,"PB731P":true} //pixord models
};

function setCameraModule(p2p_data_index,mac_addr){
	control_panel = { "ptz": false, "playback": false, "zoom": false, "home": false, "fisheye_multiwindow": false};

	var features = window.main_frame_iframe.p2p_data[p2p_data_index]['features'];
	if (features.indexOf(",") != -1){
		var slice_feature = features.split(",");

		if (jQuery.inArray("recording", slice_feature) != -1)
			control_panel['playback'] = true;

		// Not allowed for shared cameras
		if (window.main_frame_iframe.p2p_data[p2p_data_index].owner_id != user_id)
			return;

		if (jQuery.inArray("pan", slice_feature) != -1 || jQuery.inArray("tilt", slice_feature) != -1)
			control_panel['ptz'] = true;
		if (jQuery.inArray("zoom", slice_feature) != -1)
			control_panel['zoom'] = true;
		if (jQuery.inArray("home", slice_feature) != -1)
			control_panel['home'] = true;
		if (jQuery.inArray("fisheye_multiwindow", slice_feature) != -1)
			control_panel['fisheye_multiwindow'] = true;
	}
}

function slider_handle(value){
	var targetwidth = TimeLineVar.now_timeline_width + parseInt($('#main_inner_timeline').css('padding-left'), 10) + parseInt($('#main_inner_timeline').css('padding-right'), 10);
	var block_width = $('.timeline_div').width();
	var diff_width = block_width - targetwidth;
	var tick_value = diff_width / 100;
	$('#main_inner_timeline').css('margin-left', tick_value * value);
}

function timeline_init(){
	$( "#slider" ).slider({
		max:100,
		step:0.1,
		slide: function(event, ui) {
			slider_handle(ui.value);
		}
	});

	$('#playback_main').mousedown(function() {
		return false;
	});

	$('#timeline_datepicker').datepicker({
		dateFormat: "yy-mm-dd",
		maxDate: 0,
		minDate: -180,
		onSelect: function() {
			var end_date = $(this).datepicker("getDate");
			end_date.setDate(end_date.getDate()+1);
			getTimeLineList(true);
		}
	}).click(function() {
		$(this).datepicker("show");
	});

	//browser specific modify
	switch ($.client.browser){
		case "Firefox":
			$('.videoframe').css('background-color','transparent');
			$('.video_bg').css('background-image','url(images/timeline/bg_window.png )');
			$('.video_border').css('background-image','url()');
			$('.v_content').css('background-image','url()');
			$('#main_inner_timeline').css('z-index','3');
		break;
		case "Chrome":
		case "Safari":
			$('.videoframe').css('background-color','transparent');
			$('.video_bg').css('background-image','url(images/timeline/bg_window.png )');
			$('.video_border').css('background-image','url()');
			$('.v_content').css('background-image','url()');
			$('.control_playback_panel_table').css('right','142px');
			$('.control_panel_table').css('right','153px');
			$('.timeline_div_top').css('top','81px');
		break;
	}
	popmenu_event();
}


function popmenu_event(){
	$('.pop_menu_bothside').unbind().hover(function(){
		$('.timeline_pop_menu').addClass('displaynone');
	});
	$('.pop_menu_downside').unbind().hover(function(){
		$('.timeline_pop_menu').addClass('displaynone');
	});
	$('.pop_menu_up').unbind().hover(function(){
	},function(){
		$('.timeline_pop_menu').addClass('displaynone');
	});
}
function isTimelineEnable(){
	if ($('#slider .ui-slider-handle').eq(0).hasClass('displaynone'))
		return false;
	else
		return true;
}

function clearTimeline(){
	$('#main_inner_timeline').html('');
}

function TimelineEnable(enableTimeLine){
	if(enableTimeLine){
		$('#slider .ui-slider-handle').removeClass('displaynone');
		$('.timeline_selecter .timeline_condition').removeClass('timeline_condition_disabled');
	}
	else{
		timeline_timer = 0;
		$('#slider .ui-slider-handle').addClass('displaynone');
		$('.timeline_selecter .timeline_condition').addClass('timeline_condition_disabled');
	}
}

function camera_ptz_click(i){

	$('.camera_ptz_selector .camera_ptz_condition').removeClass('camera_ptz_condition_selected').eq(i).addClass('camera_ptz_condition_selected');
}

function UpdatePixordStatus(result) {
	if (result) {
		var params = result.split("&");
		for (var i=0; i<params.length; i++) {
			if (params[i].match(/^DisplayMode/)) {
				UpdatePixordDisplayMode(params[i].substr(12));
			}
		}
	}
}

function UpdatePixordDisplayMode(mode) {
	if (mode) {
		if (!TimeLineVar.CameraState)
			TimeLineVar.CameraState = {};
		TimeLineVar.CameraState.displaymode = mode;
	}
	if (TimeLineVar.CameraState !== null && TimeLineVar.CameraState !== "") {
		switch(TimeLineVar.CameraState.displaymode) {
		case "double":
			$("#tool_btn_pixord_split").removeClass("tool_btn_split_single tool_btn_split_triple").addClass("tool_btn_split_double");
			break;
		case "triple":
			$("#tool_btn_pixord_split").removeClass("tool_btn_split_single tool_btn_split_double").addClass("tool_btn_split_triple");
			break;
		default:
			$("#tool_btn_pixord_split").removeClass("tool_btn_split_double tool_btn_split_triple").addClass("tool_btn_split_single");
			break;
		}
	}
}

function change_panel(i){
	if (i==2){
		if(control_panel['ptz']) {
			try{
				if ( control_panel['fisheye_multiwindow'] || TimeLineVar.pixord_models.hasOwnProperty(window.main_frame_iframe.p2p_data[TimeLineVar.nowP2PIndex]['model']) ) {
					$('.pixord_camera_case').removeClass('displaynone');
					UpdatePixordDisplayMode();
				}
				else
					$('.pixord_camera_case').addClass('displaynone');
			}catch(err){}
			$('.control_panel_table .control_left_up_disabled').addClass('control_left_up').removeClass('control_left_up_disabled');
			$('.control_panel_table .control_up_disabled').addClass('control_up').removeClass('control_up_disabled');
			$('.control_panel_table .control_right_up_disabled').addClass('control_right_up').removeClass('control_right_up_disabled');
			$('.control_panel_table .control_left_disabled').addClass('control_left').removeClass('control_left_disabled');
			if (control_panel['home'])
				$('.control_panel_table .control_home_disabled').addClass('control_home').removeClass('control_home_disabled');
			else
				$('.control_panel_table .control_home').removeClass('control_home').addClass('control_home_disabled');
			$('.control_panel_table .control_right_disabled').addClass('control_right').removeClass('control_right_disabled');
			$('.control_panel_table .control_left_down_disabled').addClass('control_left_down').removeClass('control_left_down_disabled');
			$('.control_panel_table .control_down_disabled').addClass('control_down').removeClass('control_down_disabled');
			$('.control_panel_table .control_right_down_disabled').addClass('control_right_down').removeClass('control_right_down_disabled');
			$('.control_panel_table .control_button').addClass('control_button_pointer');
			$('.control_panel_table .control_mini').addClass('control_button_pointer');
			$('.control_panel_table td').each(function(){
				$(this).attr('onClick',$(this).attr('onclick_rel'));
				$(this).removeAttr('onclick_rel');
			});
		}
		else{
			$('.control_panel_table .control_left_up').removeClass('control_left_up').addClass('control_left_up_disabled');
			$('.control_panel_table .control_up').removeClass('control_up').addClass('control_up_disabled');
			$('.control_panel_table .control_right_up').removeClass('control_right_up').addClass('control_right_up_disabled');
			$('.control_panel_table .control_left').removeClass('control_left').addClass('control_left_disabled');
			$('.control_panel_table .control_home').removeClass('control_home').addClass('control_home_disabled');
			$('.control_panel_table .control_right').removeClass('control_right').addClass('control_right_disabled');
			$('.control_panel_table .control_left_down').removeClass('control_left_down').addClass('control_left_down_disabled');
			$('.control_panel_table .control_down').removeClass('control_down').addClass('control_down_disabled');
			$('.control_panel_table .control_right_down').removeClass('control_right_down').addClass('control_right_down_disabled');
			$('.control_panel_table .control_button').removeClass('control_button_pointer');
			$('.control_panel_table .control_mini').removeClass('control_button_pointer');
			$('.control_panel_table td').each(function(){
				$(this).attr('onclick_rel',$(this).attr('onClick'));
				$(this).removeAttr('onClick');
			});
		}
		if(control_panel['zoom']) {
			$('.control_panel_table .control_add_disabled').removeClass('control_add_disabled').addClass('control_add');
			$('.control_panel_table .control_minus_disabled').removeClass('control_minus_disabled').addClass('control_minus');
		}
		else {
			$('.control_panel_table .control_add').removeClass('control_add').addClass('control_add_disabled');
			$('.control_panel_table .control_minus').removeClass('control_minus').addClass('control_minus_disabled');
		}
	}
}

function restore_camera_timeline(){
	if (timeline_data || nvr_scheduled_data || nvr_event_data)
	{
		if (Object.prototype.hasOwnProperty.call(main_frame_iframe,"play_live_player_fullscreen"))
			window.main_frame_iframe.play_live_player_fullscreen();
		new_timeline();
	}
}

function change_video_mode(i){
	if (TimeLineVar.buttonLock) return;
	TimeLineVar.videoMode = i;
	if (i == 1 && control_panel['playback']){ //playback
		TimelineEnable(true);
		video_alert(false);
		if (TimeLineVar.googleRelayMode)
			clearTimeline();
		TimeLineVar.googleRelayMode = false;
		restore_camera_timeline();
		centerEmbedFrame(false);
		$('.control_playback_panel_table').removeClass('displaynone');
		$('.control_panel_table').addClass('displaynone');
		//window.main_frame_iframe.stop_player();
		window.main_frame_iframe.set_PlayBackControl();
		window.main_frame_iframe.changeplaysrc(''); //player null file to clean live stream
		$('.signal_light').addClass('displaynone');
		changeModeButton(i);
		TimeLineVar.buttonLock = false;
	}
	else if (i==2){ //live + ptz
		TimelineEnable(true);
		video_alert(false);
		if (TimeLineVar.googleRelayMode)
			clearTimeline();
		TimeLineVar.googleRelayMode = false;
		restore_camera_timeline();
		centerEmbedFrame(false);
		$('.control_playback_panel_table').addClass('displaynone');
		$('.control_panel_table').removeClass('displaynone');
		window.main_frame_iframe.play_live_player_fullscreen();
		$('.signal_light').removeClass('displaynone');
		changeModeButton(i);
		TimeLineVar.buttonLock = false;
	}
	else if(i==3 && control_panel['playback']){ //cloud	//
		TimeLineVar.buttonLock = true;
		TimelineEnable(true);
		TimeLineVar.googleRelayMode = true;
		centerEmbedFrame(true);
		if (relayVar.auth == false){
			video_alert(true);
			return false;
		}
		$('.control_playback_panel_table').addClass('displaynone');
		$('.control_panel_table').addClass('displaynone');
		try{
			window.main_frame_iframe.stop_player();
			window.main_frame_iframe.set_PlayBackControl();
			window.main_frame_iframe.changeplaysrc(''); //player null file to clean live stream
		}
		catch(err){}
		$('.signal_light').addClass('displaynone');
		changeModeButton(i);
		clearTimeline();
		ResetGoogleDocQuery();
		GetGoogleDocList();

	}
	change_panel(i);
}

//enable center embed frame
function centerEmbedFrame(bool){
	if (bool) {
		$('#center_frame').html('<iframe id="preview" width="630" height="360" style="background-color:#000;" frameborder="0" border="0" cellspacing="0"></iframe>');
		if (Object.prototype.hasOwnProperty.call(main_frame_iframe,"MinimizeAll"))
			window.main_frame_iframe.MinimizeAll();
	}
	else {
		$('#center_frame').html('');
		window.main_frame_iframe.p2p_data[TimeLineVar.nowP2PIndex]['fullscreen'] = true;
		window.main_frame_iframe.ResizeAll();
	}
}

//change between button in mode_block
function changeModeButton(i){
	$('.mode_button').removeClass('mode_button_selected');
	$('#mode_'+i).addClass('mode_button_selected');
}

function SlideRight(){
	var left = $('#main_inner_timeline').eq(0).css('left');
	var leftint = parseInt(left,10) - 80;
	$('#main_inner_timeline').eq(0).css('left', leftint );
}

function SlideLeft(){
	var left = $('#main_inner_timeline').eq(0).css('left');
	var leftint = parseInt(left,10) + 80;
	if (leftint > 0 ) leftint = 0;
	$('#main_inner_timeline').eq(0).css('left', leftint );
}

function Change_Cam(i){
	if ($('#Camera_'+(i+1)).hasClass('opacity50') || $('#Camera_'+(i+1)).hasClass('selected')) return;
	control_panel = { "ptz": false, "playback": false};

	window.clearTimeout(downloadtimeout);
	timeline_data = null;
	nvr_event_data = null;
	nvr_scheduled_data = null;
	google_backup_data = null;
	PrintTimer('');
	window.main_frame_iframe.openFullScreen_ByPos(i);
}



function select_camera(i){
	$('.live_list li').removeClass('selected');
	$('#Camera_'+i).addClass('selected');
}

function get_playlist() {
	if (nowPlayingFile && nowPlayingFile.type) {
		switch (nowPlayingFile.type) {
			case "NVEV":
			case "CNEV":
				return nvr_event_data;
				break;
			case "GDEV":
				return google_backup_data;
				break;
			case "RVEV":
			case "WBEV":
			default:
				return timeline_data;
				break;
		}
		return timeline_data;
	}
}

function player_backward(){
	var playlist = get_playlist();

	if (!playlist)
		return;

	for (var i=0; i<playlist.length; i++) {
		var recordingid = playlist[i].url;
		if (nowPlayingFile.url == recordingid){
			if (i==0) {
				showWarningMessage(t_js.timeline_MSG["0010"]);
				return false;
			}
			else{
				smallOverlay(true);
				playBackFile(playlist[i-1].url,makeFileName(playlist[i-1]),playlist[i-1].type);
				return true;;
			}
		}
	}
}

function player_forward(){
	var playlist = get_playlist();

	if (!playlist)
		return;

	for (var i=0; i<playlist.length; i++) {
		var recordingid = playlist[i].url;
		if (nowPlayingFile.url == recordingid){
			if (i+1>=playlist.length) {
				showWarningMessage(t_js.timeline_MSG["0011"]);
				return false;
			}
			else{
				smallOverlay(true);
				playBackFile(playlist[i+1].url,makeFileName(playlist[i+1]),playlist[i+1].type);
				return true;;
			}
		}
	}
}

//Gerenate XML Content for Jquery
function GetXMLContent(tmpXml){
	/************* TimeLineFormat Template *************/
	/*var tmpXml = '<?xml version="1.0" ?><root><recordings>\
	<recording recordingid="20120601062653_MD.avi" diskid="SD_DISK" size="" recordingtype="" starttime="062657" date="20120906" />\
	<recording recordingid="20120601062653_MD.avi" diskid="SD_DISK" size="" recordingtype="" starttime="062654" date="20120906" />\
	<recording recordingid="20120601062653_MD.avi" diskid="SD_DISK" size="" recordingtype="" starttime="062653" date="20120831" />\
	</recordings></root>';*/
	/************* TimeLineFormat Template *************/

	//tmpXml = jQuery.parseXML( tmpXml );
	if($.browser.msie && tmpXml != null && tmpXml != undefined){
		var xmlDoc = new ActiveXObject("Microsoft.XMLDOM");
		xmlDoc.loadXML(tmpXml);
		tmpXml = xmlDoc;
	}
	return $(tmpXml);
}

//check XML traceable
function XMLDataCheck(incXml){
	try{
		if($(incXml).find('recording').size() == 0)
			return false;
		else
			return true;
	}
	catch(err){
		return false;
	}
}

function smallOverlay(open){
	clearTimeout(TimeLineVar.smallOverlayTimeout);
	if(open){
		try{
			document.getElementById("pbc").style.height = "0px";
			document.getElementById("pbc").style.width = "0px";
		}catch(err){}
		$('.small_overlay').removeClass('displaynone');
		$('.small_overlay_text').removeClass('displaynone');
	}
	else{
		try{
			document.getElementById("pbc").style.height = "50px";
			document.getElementById("pbc").style.width = "145px";
		}catch(err){}
		$('.small_overlay').addClass('displaynone');
		$('.small_overlay_text').addClass('displaynone');
		TimeLineVar.buttonLock = false;
	}
}

function SortByTime(a,b){
	return a.time - b.time;
}
function SortByDate(a,b){
	return a.date - b.date;
}
function SortByStart(a,b){
	return a.start - b.start;
}

function new_timeline(phase,request)
{
	NVSRProgressSetup();
	$('#main_inner_timeline').removeClass('timeline_on');
	smallOverlay(true);

	var MinDate = "";
	var MaxDate = "";

	var dates = []; // timeline serieses

	//data content null detect
	if (!TimeLineVar.googleRelayMode) {
		if (timeline_data && timeline_data.length>0)
			dates.push({data: timeline_data, cls: "", deftype: "WBEV"});
		if (nvr_event_data && nvr_event_data.length>0)
			dates.push({data: nvr_event_data, cls: "nvr_tick", deftype: "NVEV"});
	}
	else {
		if (google_backup_data && google_backup_data.length>0)
			dates.push({data: google_backup_data, cls: "", deftype: "GDEV"});
	}
	if (dates.length ==0 && (!nvr_scheduled_data || nvr_scheduled_data.length == 0)) {
		showWarningMessage(t_js.timeline_MSG["0002"]);
		TimelineEnable(false);
		return;
	}

	// Get min and max date
	for (var i=0; i<dates.length; i++) {
		dates[i].data.sort(SortByDate);
		if (!MinDate || MinDate > dates[i].data[0].date)
			MinDate = dates[i].data[0].date;
		if (!MaxDate || MaxDate < dates[i].data[dates[i].data.length-1].date)
			MaxDate = dates[i].data[dates[i].data.length-1].date;
	}
	// Scheduled recording
	if (!TimeLineVar.googleRelayMode && nvr_scheduled_data && nvr_scheduled_data.length>0) {
		nvr_scheduled_data.sort(SortByStart);
		if (!MinDate || MinDate > nvr_scheduled_data[0].start.substr(0,8))
			MinDate = nvr_scheduled_data[0].start.substr(0,8);
		if (!MaxDate || MaxDate < nvr_scheduled_data[nvr_scheduled_data.length-1].end.substr(0,8))
			MaxDate = nvr_scheduled_data[nvr_scheduled_data.length-1].end.substr(0,8);
	}

	TimelineEnable(true);
	control_panel["playback"] = true;
	if (phase=="second") return; //there is no second phase right now
	if (phase==undefined) phase = "week";
	TimeLineVar.phase = phase;
	$('#main_inner_timeline').eq(0).css('margin-left', 0 );
	var htmlstring = "<div id='scheduled_progress' class='scheduled_progress'></div>";
	dateArray = new Object();

	//convert to javascript Date Object
	var min_date = new Date(MinDate.substr(0,4) , MinDate.substr(4,2)-1, MinDate.substr(6,2));
	var max_date = new Date(MaxDate.substr(0,4) , MaxDate.substr(4,2)-1, MaxDate.substr(6,2));

	if ( (max_date - min_date) / (3600 * 24 * 1000) >= 14 ){
		var tmp_date = new Date(max_date.getTime() - (86400000*13));
		min_date = new Date(tmp_date.getTime());
		var compare_date = min_date.format("yyyyMMdd");
		MinDate = compare_date;

		// remove all entries older than 7 days
		for (var i=0; i<dates.length; i++) {
			while (dates[i].data.length > 0 && compare_date > dates[i].data[0].date.substr(0,8))
				dates[i].data.shift();
			if (!dates[i].data || dates[i].data.length == 0)
				dates.splice(i,1);
		}
		// Scheduled Recording
		if (!TimeLineVar.googleRelayMode && nvr_scheduled_data && nvr_scheduled_data.length>0) {
			while (nvr_scheduled_data.length > 0 && compare_date > nvr_scheduled_data[0].end.substr(0,8))
				nvr_scheduled_data.shift();
			if (nvr_scheduled_data.length > 0 && compare_date > nvr_scheduled_data[0].start.substr(0,8))
				nvr_scheduled_data[0].start = compare_date + "000000";
		}
	}

	var day_counter = min_date.getDate();
	var compare_date = min_date.format("yyyyMMdd");
	var fakeweek = 0; //this is offset day
	var diffday = ( max_date - min_date ) / 86400000;

	//for data which less then 7 day, need fill with 7 day.
	if (phase == "week" && diffday < 6 && diffday >= 0){
		fakeweek = diffday - 6;
		min_date.setDate(min_date.getDate() + fakeweek);
		compare_date = min_date.format("yyyyMMdd");
		day_counter = compare_date.substr(6,2);
	}

	//full dataArray with all days.
	var miniTime = min_date.getTime()/1000;
	while(compare_date <= MaxDate){
		if (dateArray[compare_date] == undefined && typeof compare_date != "undefined"){
			dateArray[compare_date] = [];
			for (var j=0; j<dates.length; j++) {
				dateArray[compare_date].push({count: 0, recordings: []});
			}
		}
		day_counter = min_date.getDate();
		day_counter++;
		min_date.setDate(day_counter);
		compare_date = min_date.format("yyyyMMdd");
	}

	//store all XML contents to dataArray
	for (var j=0; j<dates.length; j++) {
		for (var i=0; i<dates[j].data.length; i++) {
			var thisdate = dates[j].data[i].date.substr(0,8);
			var thistime = dates[j].data[i].date.substr(8);
			var thisid = dates[j].data[i].url;
			var thistype = dates[j].data[i].purpose?dates[j].data[i].purpose:dates[j].deftype;
			if (thisdate == undefined) return true;
			if (dateArray[thisdate] == undefined){
				dateArray[thisdate]=[];
			}
			if (dateArray[thisdate][j] == undefined){
				dateArray[thisdate][j] = {count: 0, recordings: []};
			}
			var timedata = {date:thisdate,time:thistime,recordingid:thisid,type:thistype};
			dateArray[thisdate][j] ['recordings'].push(timedata);
			dateArray[thisdate][j] ['count']++;
		}
	}

	/**Objects seems cannot use dateArray.length , so use item_count **/
	var item_count = 0;
	for(var i in dateArray)
		item_count++;

	var nextstage = "hour";

	var multiple = 1;
	var time_interval_width = 106*item_count*multiple;
	var background = "background_w";
	var firefox_long = 29998;
	$('.timeline_condition').removeClass('timeline_condition_selected');
	switch(phase){
		case "week":
			$('#timeline_week').addClass('timeline_condition_selected');
		break;
		case "day":
			multiple = 8;
			time_interval_width = 93*item_count*multiple;
			firefox_long = 29988;
			background = "background_d";
			$('#timeline_day').addClass('timeline_condition_selected');
		break;
		case "hour":
			multiple = 6*24;
			time_interval_width = 123*item_count*multiple;
			firefox_long = 29889;
			background = "background_h";
			$('#timeline_hour').addClass('timeline_condition_selected');
		break;
		case "minute":
			multiple = 6*60*24;
			time_interval_width = 74*item_count*multiple;
			background = "background_m";
		break;
	}

	/******fireFox Exception ******/

	if ($.client.browser=="Firefox")
		$('#main_inner_timeline').nextAll().detach();
	if ($.client.browser=="Firefox" && time_interval_width > firefox_long){
		var needed = (time_interval_width-time_interval_width%firefox_long)/firefox_long;
		for(var i2=0;i2<needed;i2++)
			$('<div class="inner_timeline"></div>').insertAfter('#main_inner_timeline');
		$('#main_inner_timeline').css('width',firefox_long);
		$('#main_inner_timeline').eq(-1).css('width',time_interval_width%firefox_long);
	}
	else{
		$('#main_inner_timeline').css('width',time_interval_width);
	}
	/******************************/

	$('#main_inner_timeline').css('margin-left','0px');
	$('#main_inner_timeline').removeClass('background_w').removeClass('background_d').removeClass('background_h').removeClass('background_m').addClass(background);

	var left_pos = 0;

	if (nvr_dataplan != "EV" && !TimeLineVar.googleRelayMode && nvr_scheduled_data) {
		for (var i=0; i<nvr_scheduled_data.length; i++) {
			var this_time;
			var this_date;
			var this_seconds;

			this_time = nvr_scheduled_data[i].start.substr(8,6);
			this_date = nvr_scheduled_data[i].start.substr(0,8);
			this_seconds = parseInt(this_time.substr(0,2),10)*60*60 + parseInt(this_time.substr(2,2),10)*60 + parseInt(this_time.substr(4,2),10);
			var left = calculateLeft(this_seconds,this_date,item_count,time_interval_width,miniTime);

			this_time = nvr_scheduled_data[i].end.substr(8,6);
			this_date = nvr_scheduled_data[i].end.substr(0,8);
			this_seconds = parseInt(this_time.substr(0,2),10)*60*60 + parseInt(this_time.substr(2,2),10)*60 + parseInt(this_time.substr(4,2),10);
			var right = calculateLeft(this_seconds,this_date,item_count,time_interval_width,miniTime);


			var width = right-left;

			htmlstring += "<div class='scheduled_range' style='left: " + left + "px; width: "
						+ width + "px' id='scheduled_"+nvr_scheduled_data[i].start +"'></div>";
		}
	}

	switch(phase){
		case "week":
			for(var i in dateArray){
				htmlstring += 	"<div class='time_tick' style='left:"+left_pos+"px'>"+i+"</div>";
				left_pos += 106;
				for (var k=0; k<dateArray[i].length; k++) {
					var time_mount = Object();
					time_mount[0] = Object(); //00:00~08:00
					time_mount[0]['left'] = 0;
					time_mount[0]['count'] = 0;
					time_mount[0]['item'] = Object();
					time_mount[1] = Object(); //08:00~16:00
					time_mount[1]['left'] = 0;
					time_mount[1]['count'] = 0;
					time_mount[1]['item'] = Object();
					time_mount[2] = Object(); //16:00~24:00
					time_mount[2]['left'] = 0;
					time_mount[2]['count'] = 0;
					time_mount[2]['item'] = Object();
					for(var j in dateArray[i][k]['recordings']){
						if (j == "indexOf") continue; //IE bugs
						var this_time = dateArray[i][k]['recordings'][j]['time'];
						var this_date = dateArray[i][k]['recordings'][j]['date'];
						var this_seconds = parseInt(this_time.substr(0,2),10)*60*60 + parseInt(this_time.substr(2,2),10)*60 + parseInt(this_time.substr(4,2),10);
						var this_left = calculateLeft(this_seconds,this_date,item_count,time_interval_width,miniTime);


						if (0 <= this_seconds && this_seconds < 8*60*60){ //00:00~08:00
							time_mount[0]['left'] += this_left;
							time_mount[0]['item'][time_mount[0]['count']] = dateArray[i][k]['recordings'][j];
							time_mount[0]['count']++;
						}
						else if(8*60*60 <= this_seconds && this_seconds < 16*60*60){ //08:00~16:00
							time_mount[1]['left'] += this_left;
							time_mount[1]['item'][time_mount[1]['count']] = dateArray[i][k]['recordings'][j];
							time_mount[1]['count']++;
						}
						else{ //16:00~24:00
							time_mount[2]['left'] += this_left;
							time_mount[2]['item'][time_mount[2]['count']] = dateArray[i][k]['recordings'][j];
							time_mount[2]['count']++;
						}
					}

					htmlstring = makeEventTick(time_mount,htmlstring, dates[k].cls);
				}
			}
		break;
		case "day":
			for(var i in dateArray){
				for(var j=0;j<multiple*3;j++){
					if(j%3==0)
					{
						var t_content = (parseInt(j-1, 10) + 101).toString().substr(1)+":00";
						htmlstring += 	"<div class='time_tick' style='left:"+left_pos+"px'>"+t_content+"</div>";
						if (j==0){
							htmlstring += 	"<div class='date_tick' style='left:"+left_pos+"px'>"+i.substr(4,4)+"</div>";
						}
						left_pos += 93;
					}
				}
				for (var k=0; k<dateArray[i].length; k++) {
					var time_mount = Object();
					for(var i1=0;i1<24;i1++){
						time_mount[i1] = Object(); //3 hour period
						time_mount[i1]['left'] = 0;
						time_mount[i1]['count'] = 0;
						time_mount[i1]['item'] = Object();
					}
					for(var j in dateArray[i][k]['recordings']){
						if (j == "indexOf") continue; //IE bugs
						var this_time = dateArray[i][k]['recordings'][j]['time'];
						var this_date = dateArray[i][k]['recordings'][j]['date'];
						var this_hour = parseInt(this_time.substr(0,2),10);
						var this_seconds = this_hour*60*60 + parseInt(this_time.substr(2,2),10)*60 + parseInt(this_time.substr(4,2),10);
						var this_left = calculateLeft(this_seconds,this_date,item_count,time_interval_width,miniTime);

						var j2 = this_hour;
						time_mount[j2]['left'] += this_left;
						time_mount[j2]['item'][time_mount[j2]['count']] = dateArray[i][k]['recordings'][j];
						time_mount[j2]['count']++;
					}

					htmlstring = makeEventTick(time_mount,htmlstring,dates[k].cls);
				}
			}
		break;
		case "hour":
			for(var i in dateArray){
				for(var j=0;j<multiple;j++){
					var k = j%6;
					var l = (j-k)/6;
					var t_content = (parseInt(l-1, 10) + 101).toString().substr(1)+":"+k+"0";
					htmlstring += 	"<div class='time_tick' style='left:"+left_pos+"px'>"+t_content+"</div>";
					if (j==0){
						htmlstring += 	"<div class='date_tick' style='left:"+left_pos+"px'>"+i.substr(4,4)+"</div>";
					}
					left_pos += 123;
				}
				for (var k=0; k<dateArray[i].length; k++) {
					var time_mount = Object();
					for(var i1=0;i1<24;i1++){
						for(var j1=0;j1<60;j1=j1+2){
							var index = (parseInt(i1-1, 10) + 101).toString().substr(1) + ":" + (parseInt(j1-1, 10) + 101).toString().substr(1);
							time_mount[index] = Object();
							time_mount[index]['left'] = 0;
							time_mount[index]['count'] = 0;
							time_mount[index]['item'] = Object();
						}
					}
					for(var j in dateArray[i][k]['recordings']){
						if (j == "indexOf") continue; //IE bugs
						var this_time = dateArray[i][k]['recordings'][j]['time'];
						var this_date = dateArray[i][k]['recordings'][j]['date'];
						var this_hour = parseInt(this_time.substr(0,2),10);
						var this_minute = parseInt(this_time.substr(2,2),10);
						var this_seconds = this_hour*60*60 + parseInt(this_time.substr(2,2),10)*60 + parseInt(this_time.substr(4,2),10);
						var this_left = calculateLeft(this_seconds,this_date,item_count,time_interval_width,miniTime);

						var j1 = (this_minute - this_minute % 2);
						var j2 = (parseInt(this_hour-1, 10) + 101).toString().substr(1) + ":" + (parseInt(j1-1, 10) + 101).toString().substr(1);
						time_mount[j2]['left'] += this_left;
						time_mount[j2]['item'][time_mount[j2]['count']] = dateArray[i][k]['recordings'][j];
						time_mount[j2]['count']++;
					}

					htmlstring = makeEventTick(time_mount,htmlstring,dates[k].cls);
				}
			}
		break;
		case "minute":
		break;
	}
	//htmlstring += "<div class='time_interval_last'></div>";
	$('#main_inner_timeline').html(htmlstring);
	$('#main_inner_timeline').addClass('timeline_on');

	$('.event_tick').hover(function(){
		var etl = 170+12; //event tick class defined length + 12
		var block_count = parseInt($(this).find('.data_block').size(),10);
		if (block_count == 0) block_count = 1;
		if (block_count > 3) block_count = 3;
		var offtop = $(this).offset().top - 95;
		var offleft = $(this).offset().left + 5 - etl * block_count / 2;


		//adjust timeline_pop_menu width
		var datalist = $(this).find('.datalist').eq(0).html();
		$('.timeline_pop_menu').find('.datalist').eq(0).width(etl*block_count-3).html(datalist);
		$('.timeline_pop_menu').width(etl*block_count);

		//adjust pop_hover position
		$('.timeline_pop_menu').find('.pop_hover').eq(0).css('left',(block_count*etl-13)/2);

		//adjust close trigger area
		$('.pop_menu_downside').width(parseInt($('.timeline_pop_menu').css('width'),10));
		$('.pop_menu_bothside').width((etl/2-1)*block_count-8);//(etl/2-9)+(etl/2-1)*(block_count-1)

		$('.timeline_pop_menu').find('.datalist').eq(0).find('.download_single_data').click(function(){
			if (!TimeLineVar.googleRelayMode){ //google relay
				var name = $(this).siblings('.single_data').html();
				downloadFile($(this).attr('rid'), $(this).attr('type'), name);
			}
		});
		$('.timeline_pop_menu').find('.datalist').eq(0).find('.single_data').click(function(){
			if (!TimeLineVar.googleRelayMode){ //google relay
				smallOverlay(true);
				change_video_mode(1);
				$('.pop_menu_up').parent().addClass('displaynone');
				playBackFile($(this).attr('rid'),$(this).html(), $(this).attr('type'));
			}
			else{
				loadVideo($(this).attr('rid'));
			}
		});

		popmenu_event();
		if ($('#playback_main').offset().left > offleft ) {
			offleft = $('#playback_main').offset().left;
			var shift_diff = $(this).offset().left - offleft - 1;
			$('.timeline_pop_menu').find('.pop_hover').eq(0).css('left',shift_diff);
			$('.pop_menu_down').find('.pop_menu_bothside').eq(0).css('width',shift_diff - 4 );
			$('.pop_menu_down').find('.pop_menu_bothside').eq(1).css('width',parseInt($('.timeline_pop_menu').css('width'),10) - shift_diff - 12 );
		}
		$('.timeline_pop_menu').css('left',offleft).css('top',offtop).removeClass('displaynone');
	},function(){
		//$('.timeline_pop_menu').addClass('displaynone');
	});

	$(".scheduled_range").hover(
				function() {$("#scheduled_popup").show();},
				function() {$("#scheduled_popup").hide();}
			).mousemove(
				function(e) {

					y = $("#scheduled_popup").offset().top;
					offset = $(this).offset();
					position = $(this).position();
					left = e.pageX - offset.left + position.left;
					time = calculateTime(left,item_count,time_interval_width,miniTime);
					var d = new Date();
					d.setTime(time*1000);
					var start = pad(d.getFullYear(),'0',4) + "."
							+ pad(d.getMonth()+1, '0', 2) + "."
							+ pad(d.getDate(), '0', 2) + " - "
							+ pad(d.getHours(), '0', 2) + ":"
							+ pad(d.getMinutes(), '0', 2) + ":"
							+ pad(d.getSeconds(), '0', 2);
					$("#scheduled_popup").offset({left: e.pageX, top: y}).html(start);
				}
			).click(function(e) {
					offset = $(this).offset();
					position = $(this).position();
					left = e.pageX - offset.left + position.left;
					nvsr_progress_right = position.left + $(this).width();
					time = calculateTime(left,item_count,time_interval_width,miniTime);
					var d = new Date();
					d.setTime(time*1000);
					var start = pad(d.getFullYear(),'0',4)
							+ pad(d.getMonth()+1, '0', 2)
							+ pad(d.getDate(), '0', 2)
							+ pad(d.getHours(), '0', 2)
							+ pad(d.getMinutes(), '0', 2)
							+ pad(d.getSeconds(), '0', 2);
					var urls = [];
					var seek_in_millis = 0;
					var cloud_nvrs = findCloudNvrs(start, 10);
					if (cloud_nvrs.length > 0) {
						for (var i=0; i<cloud_nvrs.length; i++) {
							var cloud_nvr_datum = cloud_nvrs[i];
							var url = makeCloudNvrUrl(cloud_nvr_datum);
							urls.push(url);
						}

						seek_in_millis = StringToDate(start, false).getTime() - StringToDate(cloud_nvrs[0].start, true).getTime();
					}
					$("#scheduled_progress").show().css({left: left + "px"});
					playStreamFiles(urls,makeFileName({date:start}),"CNSR",seek_in_millis);
				}
			);

	smallOverlay(false);
	TimeLineVar.now_timeline_width = time_interval_width;
	slider_handle($('#slider').slider("value"));
}

//calcuate left position of event tick
function calculateLeft(this_seconds,this_date,item_count,time_interval_width,miniTime){
	var cur_date = new Date(this_date.substr(0,4),this_date.substr(4,2)-1,this_date.substr(6,2)); // Current date
	return Math.round(((cur_date.getTime()/1000 - miniTime + this_seconds)/(item_count*24*60*60)) * time_interval_width);
}

function calculateTime(left, item_count,time_interval_width, miniTime) {
	return miniTime + (left / time_interval_width * item_count*24*60*60);
}

function DisableCams(){
	$('.live_list li').addClass('opacity50');
}
function EnableCams(){
	$('.live_list li').each(function(){
		if ($(this).attr('title')!="")
			$(this).removeClass('opacity50');
	});
}

function SnapShotPlayer(){
	window.main_frame_iframe.SnapshotFullScreenPlayer();
}

function makeEventTick(time_mount,htmlstring, cls){
	if (cls && cls != "event_tick")
		cls="event_tick " + cls;
	else
		cls="event_tick";
	for(var k in time_mount){
		if (time_mount[k]['count']==0) continue;
		var avg_left = time_mount[k]['left']/time_mount[k]['count'];

		htmlstring += "<div class='" + cls + "' style='left:"+avg_left+"px'><div class='datalist displaynone'>";

		for(var i1 in time_mount[k]['item']){
			var rid = time_mount[k]['item'][i1]['recordingid'];
			var type = time_mount[k]['item'][i1]['type'];
			var name = makeFileName(time_mount[k]['item'][i1]);
			var download_single_data = '';
			download_single_data = "<div class='download_single_data' rid='"+ rid + "' type='" + type + "' title='" + name + "'></div>"
			htmlstring += "<div class='data_block'>" +
 			"<span class='single_data' rid='" + rid + "' type='" + type + "'>" + name  + "</span>" +
			download_single_data +
			"</div>";
		}

		htmlstring += "</div></div>";
	}
	return htmlstring;
}

function makeFileName(target){
	if (target['time']) {
		return target['date'].substr(0,4)+"."+target['date'].substr(4,2)+"."+target['date'].substr(6,2)
				+ " - " + target['time'].substr(0,2)+":"+target['time'].substr(2,2)+":"+target['time'].substr(4,2);
	}
	else {
		return target['date'].substr(0,4)+"."+target['date'].substr(4,2)+"."+target['date'].substr(6,2)
				+ " - " + target['date'].substr(8,2)+":"+target['date'].substr(10,2)+":"+target['date'].substr(12,2);
	}
}

function changeSignal(i){
	$('.signal_light').removeClass('signal_light_green').removeClass('signal_light_red').removeClass('signal_light_yellow');
	switch(i){
		case 0:
			$('.signal_light').addClass('signal_light_red');
		break;
		case 1:
			$('.signal_light').addClass('signal_light_yellow');
		break;
		case 2:
			$('.signal_light').addClass('signal_light_green');
		break;
		default:
			$('.signal_light').addClass('signal_light_red');
		break;
	}
}



function checkPlayer(){
	if (window.main_frame_iframe.isErrorHappend()){
		showWarningMessage(t_js.timeline_MSG["0003"]);
	}
	EnableCams();
}

function mutePlayer(){
	var result = window.main_frame_iframe.muteFullScreenPlayer();
	if (result == true || result == "true") //isMute
		$('#muteBtn').removeClass('tool_btn_mute_sound').addClass('tool_btn_mute_silent');
	else
		$('#muteBtn').removeClass('tool_btn_mute_silent').addClass('tool_btn_mute_sound');
}

function checkMuteBtn(){
	if (window.main_frame_iframe.isFullScreenPlayerMute())
		$('#muteBtn').removeClass('tool_btn_mute_sound').addClass('tool_btn_mute_silent');
	else
		$('#muteBtn').removeClass('tool_btn_mute_silent').addClass('tool_btn_mute_sound');
}

function refreshPlayer(){
	var j = 0;
	$('.live_list li').each(function(i){
		if ($(this).hasClass('selected')){
			j = i;
			return false;
		}
	});
	window.main_frame_iframe.refresh_player();
}

//show Warning Message
function showWarningMessage(str,remove){
	if (remove=='undefined' || remove==undefined) remove = true;
	clearTimeout(TimeLineVar.showWarningMessageTimeout);
	smallOverlay(true);
	$('.small_overlay_text').removeClass('loading_bg').removeClass('displaynone').html(str);
	TimeLineVar.buttonLock = false;
	if (remove)
		TimeLineVar.showWarningMessageTimeout = setTimeout(function(){
			removeWarningMessage();
		},4000);
}

function removeWarningMessage(){
	clearTimeout(TimeLineVar.showWarningMessageTimeout);
	smallOverlay(false);
	$('.small_overlay_text').addClass('loading_bg').html('').addClass('displaynone');
}

function listDevice(j){
	var device_list = window.main_frame_iframe.device_list;
	if (device_list.length == 0) {
		clearTimeout(TimeLineVar.listDeviceTimeout);
		TimeLineVar.listDeviceTimeout = setTimeout('listDevice('+j+');',500);
	}
	else{
		for(var i=0;i<6;i++){
			if (device_list[i] != undefined){
				var device_name = device_list[i].name;
				if (device_name.length > 13) device_name = device_name.substr(0,13) + '...';
				$('#Camera_'+(i+1)).removeClass('opacity50').html(device_name).attr('title',device_list[i].name);
			}
			else{
				$('#Camera_'+(i+1)).html('Camera '+(i+1)).addClass('opacity50').attr('title','');
			}
		}
		if (j!='undefined')
			select_camera(j);
	}

}

Date.prototype.format = function(format)
{
	var o = {
	"M+" : this.getMonth()+1, //month
	"d+" : this.getDate(),    //day
	"h+" : this.getHours(),   //hour
 	"m+" : this.getMinutes(), //minute
	"s+" : this.getSeconds(), //second
 	"q+" : Math.floor((this.getMonth()+3)/3),  //quarter
 	"S" : this.getMilliseconds() //millisecond
	}

	if(/(y+)/.test(format))
	{
		format=format.replace(RegExp.$1,(this.getFullYear()+"").substr(4 - RegExp.$1.length));
	}

 	for(var k in o)
	{
		if(new RegExp("("+ k +")").test(format))
		{
 			format = format.replace(RegExp.$1,RegExp.$1.length==1 ? o[k] : ("00"+ o[k]).substr((""+ o[k]).length));
		}
	}
	return format;
}



function devicePageUp(){
	window.main_frame_iframe.doPreviousPage();
}

function devicePageDown(){
	window.main_frame_iframe.doNextPage();
}

function timeline_range_param() {
	var date = new Date();
	var sd = new Date(date);
	sd.setDate(sd.getDate() - 7);
	var ed = new Date(date);
	ed.setDate(ed.getDate());
	var start = pad(sd.getFullYear(),'0',4)
			+ pad(sd.getMonth()+1, '0', 2)
			+ pad(sd.getDate(), '0', 2)
			+ pad(sd.getHours(), '0', 2)
			+ pad(sd.getMinutes(), '0', 2)
			+ pad(sd.getSeconds(), '0', 2);
	var end = pad(ed.getFullYear(),'0',4)
			+ pad(ed.getMonth()+1, '0', 2)
			+ pad(ed.getDate(), '0', 2)
			+ pad(ed.getHours(), '0', 2)
			+ pad(ed.getMinutes(), '0', 2)
			+ pad(ed.getSeconds(), '0', 2);
	return [start, end];
}

//show timeline list
function getTimeLineList(update_date){
	if (!update_date) {
		// triggered by opening timeline
		$("#timeline_datepicker").datepicker("setDate", new Date());
	}

	try{ //IE java load slower, need try catch prevent error
		var mac = window.frames['main_frame_iframe'].p2p_data[TimeLineVar.nowP2PIndex].mac_addr;
		var uid = window.frames['main_frame_iframe'].p2p_data[TimeLineVar.nowP2PIndex].uid;
		nvr_event_data = null;
		nvr_scheduled_data = null;
		var timeline_range = timeline_range_param();
		var param = "mac="+mac+"&start=" + timeline_range[0] + "&end="+timeline_range[1];
		getCloudNvrEvent(uid);
		timeline_returned = {"nvr":"pending"};
		downloadtimeout = setTimeout("getTimeLineFinalize(true);", 30000);
	}
	catch (err){
		timeline_timer++;
		if (timeline_timer < 30) {
			setTimeout("getTimeLineList();",1000);
		}
		else {
			timeline_returned = {"nvr":"failed"};
			getTimeLineFinalize(false);
		}
	}
}

function DateToString(date, utc) {
	if (utc) {
		return pad(date.getUTCFullYear(), '0', 4) +
				pad(date.getUTCMonth()+1, '0', 2) +
				pad(date.getUTCDate(), '0', 2) +
				pad(date.getUTCHours(), '0', 2) +
				pad(date.getUTCMinutes(), '0', 2) +
				pad(date.getUTCSeconds(), '0', 2);	}
	else {
		return pad(date.getFullYear(), '0', 4) +
				pad(date.getMonth()+1, '0', 2) +
				pad(date.getDate(), '0', 2) +
				pad(date.getHours(), '0', 2) +
				pad(date.getMinutes(), '0', 2) +
				pad(date.getSeconds(), '0', 2);
	}
}

function getCloudNvrEvent(uid) {
	var range = getTimelineDateRange();
	$.getJSON("backstage_cloud_event.php", {uid: uid, command: 'list', start: range[0], end: range[1]}, function(data) {
		if (data && data.events) {
			cloud_nvr_event_data = data.events;
			nvr_event_data = [];
			for (var i=0; i< data.events.length; i++) {
				date = DateToString(StringToDate(data.events[i].date,true),false);
				nvr_event_data.push({id:data.events[i].id, purpose:'CNEV', date:date, url:data.events[i].url});
			}
		}
		getCloudNvrRecording(uid);
	});
}

function getCloudNvrRecording(uid) {
	var range = getTimelineDateRange();
	$.getJSON("backstage_recording_list.php", {uid: uid, start: range[0], end: range[1]}, function(data) {
		if (data && data.recording_list) {
			cloud_nvr_data = data;
			if (data.recording_list.length>0) {
				data.recording_list.sort(SortByStart);
				var start = StringToDate(data.recording_list[0].start, true);
				var end = StringToDate(data.recording_list[0].end, true);
				nvr_scheduled_data = [{start:DateToString(start, false), end: DateToString(end, false)}];
				var j = 0;
				var lastEnd = end;
				for (var i=1; i<data.recording_list.length; i++) {
					start = StringToDate(data.recording_list[i].start, true);
					end = StringToDate(data.recording_list[i].end, true);
					if (start - lastEnd < 5000 && end > lastEnd) {
						nvr_scheduled_data[j].end = DateToString(end, false);
					}
					else {
						nvr_scheduled_data.push({start:DateToString(start, false), end: DateToString(end, false)});
						j++;
					}
					lastEnd = end;
				}
			}
			timeline_returned.nvr = "success";
		}
		else {
			timeline_returned.nvr = "failed";
		}
		getCloudNvrPackage(uid);
	});
}

function getCloudNvrPackage(uid) {
	$.getJSON("backstage_package.php", {command: 'get_package', uid: uid}, function(data) {
		if (data && data.dataplan) {
			nvr_dataplan = data.dataplan;
		}
		getTimeLineFinalize(false);
	});

}

function findCloudNvrs(start, num) {
	var cloud_nvrs = [];
	if (cloud_nvr_data && cloud_nvr_data.recording_list) {
		var pos = -1;
		var d = StringToDate(start, false);
		for (var i=0; i<cloud_nvr_data.recording_list.length; i++) {
			d_start = StringToDate(cloud_nvr_data.recording_list[i].start, true);
			d_end = StringToDate(cloud_nvr_data.recording_list[i].end, true);
			if (d >= d_start && d < d_end) {
				pos = i;
				break;
			}
		}
		if (pos >= 0) {
			var end = cloud_nvr_data.recording_list.length;
			if (pos + num < end) {
				end = pos + num;
			}
			cloud_nvrs = cloud_nvr_data.recording_list.slice(pos, end);
		}
	}
	return cloud_nvrs;
}

function makeCloudNvrUrl(cloud_nvr_datum) {
	var url = "";
	var applet = window.frames['main_frame_iframe'].document.getElementById('applet_' + TimeLineVar.nowP2PIndex);
	if (applet) {
		var address = applet.GetRtspURI();
		var port = applet.GetRtspPort().toString();
		var path = cloud_nvr_datum.path.replace("/var/evostreamms/media","/vod");
		url = "http://" + address + ":" + port + path;
	}
	return url;
}

function getTimeLineFinalize(isTimeout) {
	if (isTimeout) {
		// Timeout, all pending is now failed
		for (var type in timeline_returned) {
			if (timeline_returned[type] == "pending")
				timeline_returned[type] = "failed"
		}
	}
	var success = false;
	for (var type in timeline_returned) {
		// Wait longer if request pending
		if (timeline_returned[type] == "pending")
			return;

		// At least on successful
		if (timeline_returned[type] == "success")
			success = true;
	}

	window.clearTimeout(downloadtimeout);
	if (success) {
		window.main_frame_iframe.play_live_player_fullscreen();
		new_timeline();
	}
	else {
		showWarningMessage(t_js.timeline_MSG["0004"]);
		window.main_frame_iframe.play_live_player_fullscreen();
	}
}

function nvrEventCallback() {
	var applet = window.frames['main_frame_iframe'].document.getElementById('applet_' + TimeLineVar.nowP2PIndex);
	try {
		eval ("nvr_event_data = " + applet.nvrget_data);
	} catch (e) {
		nvr_event_data = null;
	}
	if (nvr_event_data) {
		for (var i=0; i<nvr_event_data.length; i++) {
			nvr_event_data[i].type = "NVEV";
		}
	}

	// Get scheduled recording
	var mac = window.frames['main_frame_iframe'].p2p_data[TimeLineVar.nowP2PIndex].mac_addr;

	var date = new Date();
	var sd = new Date(date);
	sd.setDate(sd.getDate() - 7);
	var ed = new Date(date);
	ed.setDate(ed.getDate());
	var timeline_range = timeline_range_param();
	var param = "mac="+mac+"&start=" + timeline_range[0] + "&end="+timeline_range[1];
	applet.NVRHTTP_Safe("GET", "/cgi-bin/recording_time.cgi", param, "parent.nvrScheduledRecordingCallback");
}

function nvrScheduledRecordingCallback() {
	var applet = window.frames['main_frame_iframe'].document.getElementById('applet_' + TimeLineVar.nowP2PIndex);
	try {
		eval ("nvr_scheduled_data = " + applet.nvrget_data);
	} catch (e) {
		nvr_scheduled_data = null;
	}
	timeline_returned.nvr = "success";
	getTimeLineFinalize(false);
}

function showTimelineResult(success,result){
	if (success){
		try {
			if (result)
				eval("timeline_data = " + result + ";");
			else
				eval("timeline_data = " +
					window.frames['main_frame_iframe'].document.getElementById('applet_' + TimeLineVar.nowP2PIndex).timeline_data
					+ ";");
		}
		catch (e) {
			timeline_data = null;
		}
		timeline_returned.camera = "success";
	}
	else {
		timeline_returned.camera = "failed";
	}
	getTimeLineFinalize(false);
}

function video_alert(bool){
	t_content = '<div class="reg_frame"><div class="reg_titletab"><div class="close_b" onClick="video_alert(false);"></div></div>'+
				'<div class="reg_text reg_text_s0">'+
				t_js.timeline_MSG['0005'] +
				'</div>' +
				'<div class="reg_key_frame">'+
					'<div class="btn_sharef floatleft" onClick="video_alert(false);">'+ t_js.timeline_MSG['0006'] +'</div>'+
					'<div class="btn_sharef floatleft" onClick="window.main_frame_iframe.GoogleAuthPage(false);video_alert(false);">'+ t_js.timeline_MSG['0007'] +'</div>'+
				'</div>'+
			'</div>';
	if (bool){
		$('#video_pop_alert').removeClass('off').html(t_content);
	}
	else{
		$('#video_pop_alert').addClass('off').html('');
		centerEmbedFrame(false);
		TimeLineVar.buttonLock = false;
	}
}

function PrintTimer(t){
	$('#vlc_cover .tool_timer').eq(0).html(t);
}

function RelayAlertLayer(bool,mode,i){
	if (bool){
		$('#v_relay').removeClass('off');
		if (i!=undefined)
			$('#v_relay .btn_2_layer').find('#btn_relay_reconnect').attr('onclick','resetRelayAlertLayer('+i+');');
		switch(mode){
			case "cloud":
				$('#v_relay .btn_2_layer').css('padding-right','90px');
				$('#v_relay .btn_2_layer').find('.btn_sharef').eq(0).addClass('off');
				$('#v_relay .btn_message_layer').html(t_js.timeline_MSG['0008']);
			break;
			case "relay":
			default:
				$('#v_relay .btn_2_layer').find('.btn_sharef').eq(0).removeClass('off');
				$('#v_relay .btn_2_layer').css('padding-right','0px');
				$('#v_relay .btn_message_layer').html(t_js.timeline_MSG['0009']);
			break;
		}
	}
	else{
		$('#v_relay').addClass('off');
	}
}

function resetRelayAlertLayer(i){
	RelayAlertLayer(false);
	window.main_frame_iframe.resetAppletWarning(i);
	window.main_frame_iframe.openFullScreen(i);
}

function NVSRProgressSetup(type) {
	if (nvsr_progress_interval) {
		window.clearInterval(nvsr_progress_interval);
		nvsr_progress_interval = 0;
	}
	if (type != "NVSR" && type != 'CNSR') {
		$("#scheduled_progress").hide();
	}
}

function NVSRProgressStart() {
	if ($("#scheduled_progress").css("display") != "none") {
		var interval
		switch (TimeLineVar.phase) {
			case "week":
				interval = 815094; // week: 86400000/106
				break;
			case "day":
				interval = 116129 // 10800000/93
				break;
			case "hour":
				interval = 4818; // 600000/123
				break;
			default:
				interval = 0;
				break;
		}
		if (interval) {
			nvsr_progress_interval = window.setInterval(function() {
				var obj = $("#scheduled_progress");
				var position = obj.position();
				var left = position.left + 1;
				if (left >= nvsr_progress_right) {
					NVSRProgressSetup();
				}
				else {
					obj.css({left: left+"px"});
				}
			}, interval);
		}
	}
}

function getTimelineDateRange() {
	var date = $("#timeline_datepicker").datepicker("getDate");
	date.setDate(date.getDate()+1);
	var end =  DateToString(date, true);
	date.setDate(date.getDate()-8);
	var start =  DateToString(date, true);
	return [start, end];
}
