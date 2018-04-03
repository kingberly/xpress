/* jshint -W069, -W041 */

//close 1 fullscreen
function closeAFullScreen(j){
    $('#applet_' + j).removeClass('fullscreen');
    $('body').removeClass('has-fullscreen-vlc');

    if (p2p_data[j]) {
        p2p_data[j].fullscreen = false;
    }
}

function MinimizeOne(j){
    $('#applet_' + j)
        .removeClass('fullscreen')
        .addClass('minimize');

    if (p2p_data[j]) {
        p2p_data[j].fullscreen = false;
    }
}

function MinimizeAll(){
    for(var j in p2p_data)
        MinimizeOne(j);
}

function ResizeAll(){
	try{
		var has_fullscreen = false;
		for(var j in p2p_data){
			if(p2p_data[j]["fullscreen"] == true){
				has_fullscreen = true;
				break;
			}
		}

        var $applet;
		for(var j in p2p_data){
            $applet = $('#applet_' + j);
			if(p2p_data[j]["fullscreen"] == true){
				var pos = p2p_data[j]["pos"];
				if (p2p_data[j]["signal"] != 3)
					DoFullScreen(j);
				else
					MinimizeOne(j);
			}
			else{
				if (has_fullscreen){
                    $applet.addClass('minimize');
                } else {
                    $applet.removeClass('minimize fullscreen');
                }
			}
		}
	}catch(err){
	}
}

//close all fullscreen at once
function closeAllFullScreen(){
    $("._matrix_boxcontent")
       .find('.applet_vlc').removeClass('fullscreen minimize').end()
       .show();

	for(var j in p2p_data){
		if (p2p_data[j]['signal'] != 3)
			closeAFullScreen(j);
	}
}

//applet play
function changeplaysrc(src){
	player_fail_recovery = false;
	for(var j in p2p_data){
		if(p2p_data[j]["fullscreen"] == true && p2p_data[j]['signal'] != 3){
			document.getElementById("applet_" + j).Play_Safe(src);
			return;
		}
	}
}

//check applet is error happened (for download playback file, sometimes file will error)
function isErrorHappend(){
	for(var j in p2p_data){
		if(p2p_data[j]["fullscreen"] == true){
			return document.getElementById("applet_" + j).isErrorHappend();
		}
	}
}

//applet play countinue
function play_player(){
	for(var j in p2p_data){
		if(p2p_data[j]["fullscreen"] == true && p2p_data[j]["signal"] != 3){
			document.getElementById("applet_" + j).Play_Con_Safe();
			return;
		}
	}
}

//applet stop
function stop_player(){
	for(var j in p2p_data){
		if(p2p_data[j]["fullscreen"] == true){
			document.getElementById("applet_" + j).Stop_Safe();
			return;
		}
	}
}

//applet stop
function set_PlayBackControl(){
	for(var j in p2p_data){
		if(p2p_data[j]["fullscreen"] == true){
			document.getElementById("applet_" + j).setToPlayBackControl();
			return;
		}
	}
}

//applet pause
function pause_player(){
	for(var j in p2p_data){
		if(p2p_data[j]["fullscreen"] == true){
			document.getElementById("applet_" + j).Pause_Safe();
			return;
		}
	}
}

//applet open_setting
function setup_player(){
	for(var j in p2p_data){
		if(p2p_data[j]["fullscreen"] == true){
			if (p2p_data[j].owner_id == user_id)
				openWeb(p2p_data[j]["uri"],p2p_data[j]["webport"]);
			return;
		}
	}
}

//applet refresh
function refresh_player(){
	for(var j in p2p_data){
		if(p2p_data[j]["fullscreen"] == true){
			var dp = parent.DestroyPort(p2p_data[j]['uid']); //if port = 0 , recreate port
			p2p_data[j]['start'] = false;
			refreshSinglePlayer(j,true);
			//p2p_data[j]["fullscreen"] = false;
			openFullScreen(j);
			return;
		}
	}
}

//applet play live
function play_live_player_fullscreen(){
	for(var j in p2p_data){
		if(p2p_data[j]["fullscreen"] == true){
			try{
				play_live_player(j);
			}
			catch(err){
			}
			parent.nowPlayingFile = "";
			return;
		}
	}
}

function play_live_player(j){
	if(p2p_data.hasOwnProperty(j) && p2p_data[j].hasOwnProperty("signal") && p2p_data[j]["signal"] != 3)
		document.getElementById("applet_" + j).Play_Live_Safe();
}

//make all vlc player start play live stream
function play_live_player_all(){
	for(var j in p2p_data){
		try{
			play_live_player(j);
		}
		catch(err){
		}
	}
}

//make all vlc player stop except fullscreen one
function stop_player_all_except_fullscreen(){
	for(var j in p2p_data){
		try{
			if (p2p_data[j]["fullscreen"] == false){
				document.getElementById("applet_" + j).Stop_Safe();
			}
		}
		catch(err){
		}
	}
}

//make all vlc player stop
function stop_player_all(){
	for(var j in p2p_data){
		try{
			document.getElementById("applet_" + j).Stop_Safe();
		}
		catch(err){
		}
	}
}


//to FullScreen by vlc position
function openFullScreen_ByPos(j){
	for(var i in p2p_data){
		if (parseInt(p2p_data[i]['pos'],10) == parseInt(j,10)){
			openFullScreen(i);
			return false;
		}
	}
}

function SnapshotFullScreenPlayer(){
	for(var j in p2p_data){
		if (p2p_data[j]["fullscreen"] == true){
			try{
				document.getElementById("applet_" + j).CaptureScreen_Safe();
			}catch(err){
			}
			return;
		}
	}
}

function muteFullScreenPlayer(){
	for(var j in p2p_data){
		if (p2p_data[j]["fullscreen"] == true)
			return mutePlayer(j);
	}
	return false;
}


function isFullScreenPlayerMute(){
	for(var j in p2p_data){
		if (p2p_data[j]["fullscreen"] == true){

			if ($('._matrix_iconbox[ref_id='+j+']').find('.muteButton').eq(0).hasClass('_matrixbox_mute_setting_on_sound'))
			return false;
			else return true;
		}
	}
	return false;
}

function mutePlayer(i){
	try{
		var result = document.getElementById("applet_" + i).Player_Mute_Safe();
		if (result == true || result == "true") //isMute
			$('._matrix_iconbox[ref_id='+i+']').find('.muteButton').eq(0).removeClass('_matrixbox_mute_setting_on_sound').addClass('_matrixbox_mute_setting_on_silent').attr('title','Mute');
		else
			$('._matrix_iconbox[ref_id='+i+']').find('.muteButton').eq(0).removeClass('_matrixbox_mute_setting_on_silent').addClass('_matrixbox_mute_setting_on_sound').attr('title','Unmute');
	}catch(err){}
	return result;
}

function DoFullScreen(i){
    $('body').addClass('has-fullscreen-vlc');
    $('#applet_' + i).addClass('fullscreen');
}

//Switch to Full Screen
function openFullScreen(i){
    var device_p2p = p2p_data[i];
	//JQuery Might cause error on applet.
	if (device_p2p.fullscreen == false){ //to fullscreen

        // clear previous state
		for(var j in p2p_data) {
			p2p_data[j].fullscreen = false;
			if (p2p_data[j].features.indexOf("audio")>=0)
				document.getElementById("applet_" + j).TwoWayAudio_Safe(false);
		}

		device_p2p.fullscreen = true;
		parent.POPVLCFrame(i,device_p2p['pos'],device_p2p.mac_addr);

		if (device_p2p.signal != 3)
			DoFullScreen(i);
		else
			MinimizeOne(i);

		for(var j in p2p_data){
			if (j==i) continue;
			p2p_data[j].fullscreen = false;
            $('#applet_' + j).addClass('minimize');
		}
		parent.checkMuteBtn();
	}
}

//get fullscreen camera uid
function get_fullscreen_uid(){
	for(var j in p2p_data){
		if (p2p_data[j]["fullscreen"] == true){
			return p2p_data[j]["uid"];
		}
	}
}

//get fullscreen camera id
function get_fullscreen_id(){
	for(var j in p2p_data){
		if (p2p_data[j]["fullscreen"] == true){
			return j;
		}
	}
}
function createPlayer(id,fullscreen,isFullscreening){
	switch(JAVA_LOADING){
		case 0:
			JAVA_LOADING = 1;
		break;
		case 1:
			setTimeout("createPlayer("+id+","+fullscreen+","+isFullscreening+");",1000);
		return;
		case 2:
		default:
		break;
	}
	if (fullscreen==undefined || fullscreen=='undefined' ) fullscreen = false;
	if (isFullscreening==undefined || isFullscreening=='undefined' ) isFullscreening = false;


	var p = p2p_data[id];

    // PLAYER PROPERTIES
    var playerClassName = 'applet_vlc';
    var playerParams;
    var playerStyle = {};
    var playerAttrs = {
        id: 'applet_' + id,
        pos: p.pos,
        name: p2p_vlc_name,
        archive: null, // assign later
        code: "com/qlync/vlc/VLC_Player_Single_v2.class"
    };

    // assign archive attribute
	try {
		if (p2p_jars)
			playerAttrs.archive = p2p_jars;
		else
			playerAttrs.archive = CONFIG.ROOT_URL + "jar/ref_library.jar," + CONFIG.ROOT_URL + "jar/SAT_P2P.jar";
	}
	catch (e) {
        playerAttrs.archive = CONFIG.ROOT_URL + "jar/ref_library.jar," + CONFIG.ROOT_URL + "jar/SAT_P2P.jar";
	}


    // style for fullscreen
    if (fullscreen) {
        playerClassName += ' fullscreen';
    } else if (isFullscreening || p2p_data[id].service_type  == "nvr") {
        playerClassName += ' minimize';
    }


    // assign player params
    playerParams = {
        java_arguments: "-Xmx256m",
        remote_id:      p.uid,

        //url_prefix: p.url_prefix,
        url_prefix:   'http://',
        url_path:     p.url_path,

        addr1:        ('camera' === p.service_type ? p.stream_local_uri : p.local_uri),
        port1:        p.req_rtsp_port,

        addr2:        p.local_uri,
        port2:        p.req_web_port,

        model_id:     p.model_id,
        model:        p.model,

        req_web_url:  p.req_web_url,

        req_cm_addr:  p.local_uri,
        req_cm_port:  p.req_cm_port,

	direct_connection: direct_connection,

        //uri:        location.host,
        refresh_token:   ((parseInt(window.location.port) != 1019)  ? '' : refresh_token),
        user_id:         p.default_id,
        user_pw:         p.default_pw,
        cookie_userinfo: GetCookie('_uid' + p.uid)
    };

    if (tunnel_server) {
        $.extend(playerParams, {
            tunnel_server_uid:  tunnel_server.uid,
            tunnel_server_addr: tunnel_server.external_address,
            tunnel_server_port: tunnel_server.external_port
        });
    }

	if (p.nvr_id) {
        playerParams.nvr_id = p.nvr_id;
        playerParams.request_nvr_port = p.req_nvr_port;

		if (p.req_nvr_port2) {
            playerParams.request_nvr_port2 = p.req_nvr_port2;
		}
	}

	if ('nvr' === p.service_type) {
		playerParams.autoplay = "false";
	}
    // PLAYER PROPERTIES - END


    // APPLET WARNING CONTENT
	var warning_html = '';
	var jres = deployJava.getJREs();
	var hide_applet_warning = '';

    if ($.client.os == "Mac" && $.client.browser != "Chrome") {
        hide_applet_warning = "off";
    }

	if (!jres || jres.length == 0) {
		warning_html = '<div class="applet_warning '+hide_applet_warning+'">' +
                            t_js.java_vlc_control_MSG['0001'] +
                            '<br><br><span class="link" style="width:100%;display:inline-block;text-align:center;" onclick="openJavaTutorial(0);">' +
                                t_js.java_vlc_control_MSG['0002'] +
                            '</span>' +
                        '</div>';
	}

	if ($.client.os != "Windows" && $.client.os != "Mac"){ // platform not supported
		warning_html = '<div class="applet_warning '+hide_applet_warning+'">' + t_js.java_vlc_control_MSG['0003'] + '</div>';
	}
	else if (p2p_data[id]["service_type"]  == "nvr") {
		warning_html = '<div class="applet_warning applet_nvr applet_nvr_off"></div>';
	}

    if (warning_html == "") { // Default message, tell user to enable java applet.
        warning_html = '<div class="applet_warning '+hide_applet_warning+'">' + t_js.java_vlc_control_MSG['0004'] + '</div>';
    }

    // Reconnect button
	warning_html += '<div class="relay_alert_layer off">' +
                        '<div class="ps_message"></div>' +
					    '<div class="relay_button_layer">' +
						    '<div class="btn_sharef" onclick="closeAFullScreen('+id+');resetAppletWarning('+id+');">'+t_js.java_vlc_control_MSG['0006']+'</div>' +
					    '</div>' +
				    '</div>';
    // APPLET WARNING CONTENT - END



    // CREATE APPLET WARNING
    var applet_warning = '<div class="applet_text_area">' + warning_html + '</div>';

    // CREATE PLAYER ELEMENT

    // When creating an applet by jQuery,
    // the param tags have to be inside the applet for IE compatibility.
    var player = '<applet class="' + playerClassName + '"' +
                    $.map(playerAttrs, function(value, key) {
                        return key + '="' + value + '"';
                    }).join(' ') +
                    '>' +
                    $.map(playerParams, function(value, key) {
                        return '<param name="' + key + '" value="' + value + '" />';
                    }).join('') +
                 '</applet>';

    var $player = $(player).css(playerStyle);

    // Attach warning and player to context element
    $('#p2p_' + id)
        .empty()
        .append( applet_warning )
        .append( $player );

    if (isFullscreening) {
        $('body').addClass('has-fullscreen-vlc');
    }
}

function ToggleTwoWayAudio(on, id) {
	var _id;
	if (id) {
		_id = id;
	}
	else {
		_id = get_fullscreen_id();
	}

    var el;
    if ( _id && ( el = document.getElementById("applet_" + _id)) ) {
        try { el.TwoWayAudio_Safe(on); } catch(e) {}
    }
}

function TogglePixordSplitMode(mode) {
	var id = get_fullscreen_id();
	if (id && document.getElementById("applet_" + id))
		document.getElementById("applet_" + id).CustomHTTP_Safe("GET", "/cgi-bin/viewer/fe.cgi", "action=display_mode&cmd=" + mode, "parent.UpdatePixordStatus");
}
