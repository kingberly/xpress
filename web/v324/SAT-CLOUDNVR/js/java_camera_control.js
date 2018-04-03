if (typeof window.CONFIG === 'undefined') {
    window.CONFIG = window.parent.CONFIG;
}
function addJavaAgent(p2p_data_index,mac_addr){
	window.clearInterval(downloadtimeout); //Wait for Download "TimelineList" Timer
	clearTimeout(xmllist_timer); //Wait for "timeline list" Timer
	if (window.main_frame_iframe.p2p_data[p2p_data_index]['webport'] == -1){
		//addJavaAgentTimeout = setTimeout("addJavaAgent('"+p2p_data_index+"','"+mac_addr+"');",500);
		//console.log("noport " + p2p_data_index);
	}
	else if (window.main_frame_iframe.p2p_data[p2p_data_index]['webport'] == 0){
		$('#main_inner_timeline').html('');
		removeWarningMessage();
		//console.log("0 port " + p2p_data_index);
	}
	else{
		timeline_guid = "";

		$('#main_inner_timeline').html('');
		timeline_data = null;
		timeline_timer = 0;	//timeLine Timeout Timer
		if (control_panel['playback']){
			getTimeLineList();
			TimelineEnable(true);
		}
		else{
			TimelineEnable(false);
			smallOverlay(false);
		}
	}
}

//clear Java HttpAgent
function removeJavaAgent(){
	var contentbox =document.getElementById('c_c_layer');
	while(contentbox.hasChildNodes())
		contentbox.removeChild(contentbox.childNodes[0]);
}

function setStatus(result){
	var obj = jQuery.parseJSON(result ? result : "null");//var obj = jQuery.parseJSON(result); //jinho fix
	TimeLineVar.CameraState = obj;
	UpdatePixordDisplayMode();
}

//download playback file and play
function playBackFile(recordingid,name,type){
	NVSRProgressSetup(type);
	try{
		TimeLineVar.VideoName = name;
		DisableCams();
		if (!type)
			type="WBEV";
		if (type == "CNEV") {
			return playBackCloudNVREvent(recordingid, name);
		}
		else {

			try{ //IE java load slower, need try catch prevent error
				timeline_guid = window.frames['main_frame_iframe'].document.getElementById('applet_' + TimeLineVar.nowP2PIndex).downloadPlay_Safe(recordingid, type);
			}
			catch (err){}
		}
		nowPlayingFile = {url:recordingid, type: type};
		window.clearInterval(downloadtimeout);
		timeline_timer = 0;
		downloadtimeout = self.setInterval("getDownload();",1000);
	}
	catch(err){
		showWarningMessage(t_js.java_camera_control_MSG['0001']);
		EnableCams();
		return;
	}
}

function playBackCloudNVREvent(recordingid, name) {
	if (cloud_nvr_data) {
		var path = recordingid.replace("/var/evostreamms/media","/vod");
		var string_d = name.replace(/[\-\.: ]*/g, '');
		var d = StringToDate(string_d, false);
		// pre-recording 15 seconds
		d.setSeconds(d.getSeconds()-15, false);
		var start = DateToString(d, false);
		var seek_in_millis = 0;
		var urls = [];
		var cloud_nvrs = findCloudNvrs(start, 2);
		if (cloud_nvrs.length > 0) {
			for (var i=0; i<cloud_nvrs.length; i++) {
				var cloud_nvr_datum = cloud_nvrs[i];
				var url = makeCloudNvrUrl(cloud_nvr_datum);
				urls.push(url);
			}

			seek_in_millis = StringToDate(start, false).getTime() - StringToDate(cloud_nvrs[0].start, true).getTime();
		}
		playStreamFiles(urls,name,"CNSR",seek_in_millis);
	}
}

function playStreamFiles(urls,name,type,seek_in_millis){
	NVSRProgressSetup(type);
	try{
		TimeLineVar.VideoName = name;
		DisableCams();

		try{ //IE java load slower, need try catch prevent error
			var applet = window.frames['main_frame_iframe'].document.getElementById('applet_' + TimeLineVar.nowP2PIndex);
			applet.setPlayerOption(false, false, true);
			applet.setPlayListFromString(urls.join("|"), 0);
			timeline_guid = applet.Play_Safe(urls[0], seek_in_millis);
		}
		catch (err){}

		nowPlayingFile = {url:urls[0], type: type};
	}
	catch(err){
		showWarningMessage(t_js.java_camera_control_MSG['0001']);
		EnableCams();
		return;
	}
}

function playVideo(success){
	window.clearInterval(downloadtimeout);
	NVSRProgressStart();
	if (success){
		smallOverlay(false);
		try{
			document.getElementById('pbc').setTitle(TimeLineVar.VideoName);
		}catch(err){}
		EnableCams();
	}
	else{
		showWarningMessage(t_js.java_camera_control_MSG['0002']);
		EnableCams();
		return;
	}
}

//handler for Check Downloaded File
function getDownload(){
	if (timeline_timer > 30){
		window.clearInterval(downloadtimeout);
		showWarningMessage(t_js.java_camera_control_MSG['0002']);
		EnableCams();
	}
	timeline_timer++;
}

function AddPlayerControl(bool){
	var contentbox =document.getElementById('vlc_control');
	while(contentbox.hasChildNodes())
		contentbox.removeChild(contentbox.childNodes[0]);

	var player_ctrl = document.createElement('applet');
		player_ctrl.setAttribute("name", "control_panel");
		try {
			if (p2p_jars)
				player_ctrl.setAttribute("archive", p2p_jars);
			else
				player_ctrl.setAttribute("archive", CONFIG.ROOT_URL + "jar/ref_library.jar," + CONFIG.ROOT_URL + "jar/SAT_P2P.jar");
		}
		catch (e) {
			player_ctrl.setAttribute("archive", CONFIG.ROOT_URL + "jar/ref_library.jar," + CONFIG.ROOT_URL + "jar/SAT_P2P.jar");
		}
		player_ctrl.setAttribute("code", "com/qlync/vlc/gui/Playback_Control.class");
		player_ctrl.style.height = 50;
		player_ctrl.style.width = 145;
		player_ctrl.id = "pbc";

		var param = document.createElement('param');
		param.name = "java_arguments";
		param.value = "-Xmx256m";
		player_ctrl.appendChild(param);

	if (bool)
		contentbox.appendChild(player_ctrl);
}

//PTZ move camera
function ShowSingle(i){
	var _id = TimeLineVar.nowP2PIndex;
	if (_id === "") return;
	var channel = "";
	try{
		if (control_panel.fisheye_multiwindow || TimeLineVar.pixord_models.hasOwnProperty(window.main_frame_iframe.p2p_data[_id]['model'])){ //pixord camera case
			if (TimeLineVar.CameraState !== null || TimeLineVar.CameraState !== ""){
				var index = $('#pixord_camera_case').find('.camera_ptz_condition_selected').index();
				if (TimeLineVar.CameraState.displaymode === "double"){
					if (index == 0) channel = "0"; else channel = "1";
				}
				else{
					if (index == 0) channel = "1"; else channel = "2";
				}
			}
			else return;
		}

		switch(i){
			case 11:
				window.frames['main_frame_iframe'].document.getElementById('applet_' + _id).Move_Camera_Safe("zoomout",channel);
			break;
			case 10:
				window.frames['main_frame_iframe'].document.getElementById('applet_' + _id).Move_Camera_Safe("zoomin",channel);
			break;
			case 9:
				window.frames['main_frame_iframe'].document.getElementById('applet_' + _id).Move_Camera_Safe("upright",channel);
			break;
			case 8:
				window.frames['main_frame_iframe'].document.getElementById('applet_' + _id).Move_Camera_Safe("up",channel);
			break;
			case 7:
				window.frames['main_frame_iframe'].document.getElementById('applet_' + _id).Move_Camera_Safe("upleft",channel);
			break;
			case 6:
				window.frames['main_frame_iframe'].document.getElementById('applet_' + _id).Move_Camera_Safe("right",channel);
			break;
			case 5:
				window.frames['main_frame_iframe'].document.getElementById('applet_' + _id).Move_Camera_Safe("home",channel);
			break;
			case 4:
				window.frames['main_frame_iframe'].document.getElementById('applet_' + _id).Move_Camera_Safe("left",channel);
			break;
			case 3:
				window.frames['main_frame_iframe'].document.getElementById('applet_' + _id).Move_Camera_Safe("downright",channel);
			break;
			case 2:
				window.frames['main_frame_iframe'].document.getElementById('applet_' + _id).Move_Camera_Safe("down",channel);
			break;
			case 1:
				window.frames['main_frame_iframe'].document.getElementById('applet_' + _id).Move_Camera_Safe("downleft",channel);
			break;
			default:
			break;
		}
	}catch(err){}
}

function ToggleTwoWayAudio() {
	if ($("#tool_btn_audio").hasClass("tool_btn_audio_off")) {
		window.frames['main_frame_iframe'].ToggleTwoWayAudio(true);
		$("#tool_btn_audio").addClass("tool_btn_audio_on").removeClass("tool_btn_audio_off");
	}
	else if ($("#tool_btn_audio").hasClass("tool_btn_audio_on")) {
		window.frames['main_frame_iframe'].ToggleTwoWayAudio(false);
		$("#tool_btn_audio").addClass("tool_btn_audio_off").removeClass("tool_btn_audio_on");
	}
}

function TogglePixordSplitMode(obj) {
	var nextmode;
	if ($(obj).hasClass("tool_btn_split_single"))
		nextmode = "double";
	else if ($(obj).hasClass("tool_btn_split_double"))
		nextmode = "triple";
	else
		nextmode = "broad";
	window.frames['main_frame_iframe'].TogglePixordSplitMode(nextmode);
}

function ResetTwoWayAudioButton(enabled) {
	if(enabled) {
		$("#tool_btn_audio").removeClass("tool_btn_audio_on tool_btn_audio_disabled").addClass("tool_btn_audio_off");
	}
	else {
		$("#tool_btn_audio").removeClass("tool_btn_audio_on tool_btn_audio_off").addClass("tool_btn_audio_disabled");
	}
}
