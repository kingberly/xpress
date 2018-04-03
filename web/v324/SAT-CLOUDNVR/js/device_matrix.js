/* jshint -W069, -W041 */

if (typeof window.CONFIG === 'undefined') {
    window.CONFIG = window.parent.CONFIG;
}
/**
 * device_p2p.signal
 *  0: offline          red
 *  1: disconnected     yellow
 *  2: online           green
 *  3: stopped
 */

var device_list = new Array();
var nvr_list = new Array();
var tunnel_server = null;
if (parent.JavaAPI_ver == undefined) JavaAPI_ver = 2;
else JavaAPI_ver = parent.JavaAPI_ver;
var player_fail_recovery = true;
var p2p_data = Object();
var Timeout_checkWebSettingButton = 0;
var singal_timer = 0; //QuerySignal Timer
var redisplay_delay = 3000;

/**
 * Applet loading status
 *  0 - default
 *  1 - waiting for creating applet element.
 *  2 - applet loaded.
 *
 * @const {Number}
 */
var JAVA_LOADING = 0; //other java loading signal

var enlargeWindow = null;

var fullscreen_size = {'width':'630px','height':'360px'};
var normal_size = {'width':'288px','height':'155px'};
var autoupdate_list = [];
var autoupdate_command = [];
var update_server = ["118.163.3.187", "60000"];

var fullscreen_pos = {"0":{"top":"-47px","left":"159px"},
"1":{"top":"-47px","left":"-167px"},
"2":{"top":"-47px","left":"-493px"},
"3":{"top":"-247px","left":"159px"},
"4":{"top":"-247px","left":"-167px"},
"5":{"top":"-247px","left":"-493px"}};

function SET_JAVA_LOADNIG(i){
	JAVA_LOADING = i;

    if (2 == JAVA_LOADING) {
        $('#device_matrix .applet_warning').addClass('off');
    }
}

function RefreshDisplay( mode, matrix_display_mode, order_by, shared_mode )
{
	device_list = new Array();
	// convert to int first
	matrix_display_mode = parseInt(matrix_display_mode);

	// calculate limit & offset
	//var limit = matrix_display_mode*matrix_display_mode;
	var limit = 6;
	var offset = limit*g_page_control_current_page_no; // g_page_control_current_page_no is defined in page_control.js

	// get data
	$.getJSON(
		"backstage_device.php",
		{
			command: "list",
			mode: mode,
			order_by: order_by,
			limit: limit,
			offset: offset,
			isfavorite: favorite_mode,
			now_page: g_page_control_current_page_no,
			list_mode: false,
			shared_mode: shared_mode,
			request_device_type: request_device_type,
			request_service_type: request_service_type
		},
		function( data )
		{
			device_list = new Array();
			if( data != null )
			{
				if( data.device_list != null )
				{
					device_list = data.device_list;
					if ( data.nvr_list != null )
						nvr_list = data.nvr_list;
					else {
						nvr_list = new Array();
					}
					if ( !tunnel_server && data.tunnel_server )
						tunnel_server = data.tunnel_server;
					//if (device_list.length > 6) device_list.pop();

					ReDisplay(matrix_display_mode);

					//there is no nail item in public mode, so skip it
					if (mode != "public")
						max_nail = data.MaxNail + 1;

					// update page control
					UpdateTotalPageNumber( data.device_total_count, limit );
				}
			}


		}
	);
}


function destroySinglePlayer(id) {
	p2p_data[id]["start"] = false;
	p2p_data[id]["webport"] = -1;
	p2p_data[id]["signal"] = 0;
	p2p_data[id]["relay_time"] = 0;
	p2p_data[id]["disconnect_time"] = 0;
	p2p_data[id]["connect_type"] = "";

	var matrix_iconbox = $('#device_matrix ._matrix_iconbox[ref_id="'+id+'"]').eq(0);
	//reset LED time
	$(matrix_iconbox).next('._matrix_time').eq(0).html("");
	//reset web setting button
	$(matrix_iconbox).find('._matrixbox_web_setting').removeClass('_matrixbox_web_setting').addClass('_matrixbox_web_setting_off').removeAttr( 'onClick' );
	$(matrix_iconbox).find('._matrixbox_fullscreen_on').removeClass('_matrixbox_fullscreen_on').addClass('_matrixbox_fullscreen_off').removeAttr( 'onClick' );
	$(matrix_iconbox).find('.muteButton').removeClass('_matrixbox_mute_setting_on_silent').removeClass('_matrixbox_mute_setting_on_sound').addClass('_matrixbox_mute_setting_off').attr('title',t_js.device_matrix_MSG['0006']).removeAttr( 'onClick' );

	var applet_nvr = $(matrix_iconbox).parent().find("applet_nvr");
	$(applet_nvr).removeClass("applet_nvr_on").addClass("applet_nvr_off").removeAttr( 'onClick' );

	if ( typeof parent["DestroyPort"] == "function" )
		parent.DestroyPort(p2p_data[id]['uid']);
}

//refresh a single java vlc player
function refreshSinglePlayer(id) {
    destroySinglePlayer(id);
	createPlayer(id,p2p_data[id]["fullscreen"]);
}

function stopAllPlayers() {
    $('#device_matrix .p2p_container').each(function() {
        $(this).find('.applet_vlc').get(0).Stop_Safe();
        destroySinglePlayer(id);
    });
}
function playAllPlayers() {
    $('#device_matrix .p2p_container').each(function() {
        refreshSinglePlayer( $(this).data('id') );
    });
}

//IndexOf , IE doesnt support this Command, so write a function
function INDEXOF(array,s){
	var counter = 0;
	for(var i in array){
		if(array[i] == s)
			return counter;
		counter++;
	}
	return -1;
}

function RefreshAfterDelete( data )
	{
        //location.reload(true);
		var thishref = window.location.href;
		$.getJSON(
			"backstage_device.php",
			{
				command: "assigniframe",
				thishref: thishref,
			},
			function( data )
			{
				parent.window.location = "index.php?thishref=true";
			}
		);
	}

function ReDisplay( matrix_display_mode )
{
	// get size of each object
	var width_of_object = parseInt(($(window).width()-20) / matrix_display_mode);
	var height_of_object = parseInt(($(window).height()-40*matrix_display_mode) / matrix_display_mode);

	var isFullscreening = false;
	var isFullscreening_id = -1;
	var whoFullscreen_pos = false;

	for(var i in p2p_data)
		if (p2p_data[i]["fullscreen"]){
			isFullscreening = true;
			parent.listDevice(1); //refresh fullscreen's device list
			try{
				//page changed, so p2p_data will match failed with applet_id, continue
				whoFullscreen_pos = document.getElementById("applet_" + i).getAttribute("pos");
			}
			catch(err){
				//contionue
			}
			break;
		}


	p2p_data = new Object();

	// prepare html
	var html_to_insert = "";
	sequential_list = Array();
	for( var i=0; i<device_list.length; i++ )
	{
		if (device_list[i].sequential == "null" || device_list[i].sequential == null) continue;
		if (p2p_data[device_list[i].id]!= undefined) continue;

		var favoritecon = "_matrixbox_favorite_off";
		var lockicon ="_matrixbox_lock_off";
		var idstring = device_list[i].id.toString();

		var favoritebutton = "_matrixbox_favorite";
		var	closebutton = "_matrixbox_close";
		var	fullscreenbutton = "_matrixbox_fullscreen_off";
		var controlpanelbutton = "_matrixbox_control_panel_off";
		var	websettingbutton = "_matrixbox_web_setting_off";
		var	mutesettingbutton = "muteButton _matrixbox_mute_setting_off";
		var	refreshbutton = "_matrixbox_refresh";
		var	lockbutton = "_matrixbox_lock";
		var singal_default = "singal_default singal_red";
		var publicstyle = "";
		var fav_publicstyle ="";
		var basicframe = "_device_single_frame";
		var disabled = "";

		var nailpage = -1;
		var nailpos = -1;
		/*if (favorite_mode) {
			nailpage = device_list[i].myfavorite_nail_page;
			nailpos = device_list[i].myfavorite_nail_position;
		}*/

		//is this lock?
		if (nailpage != -1 && mode != "public") {
			lockicon = "_matrixbox_lock_on";
			disabled = "ui-state-disabled";
		}

		//is this on favorite?
		//if (INDEXOF(favorite_list,idstring)!=-1 && mode != "public")



		if (device_list[i].sequential == null) device_list[i].sequential = device_list[i].id;
		var sequential_id = device_list[i].sequential;
		//if (favorite_mode) sequential_id = device_list[i].myfavorite_seq;
		html_to_insert += "<div class='"+ basicframe + " " + disabled + "' seq_id='" + sequential_id + "' id='"+i+"' nail_position='" + nailpos +"'>"+
						     "<div class='_device_loading_cover'><div class='_device_loading_cover_text'></div></div>"+
							 "<div class='_matrix_title' title='"+device_list[i].name+"'>"+ ReduceStrLength(device_list[i].name, 15) +"</div>"+
							 "<div class='_matrix_iconbox' ref_uid='" + device_list[i].uid + "' ref_id='"+ device_list[i].id +"' >";

		sequential_list.push(sequential_id);


		//public mode
		if (mode=="public" || user_id == ""){
			closebutton = "_matrixbox_close_dummy";
			//favoritebutton = "_matrixbox_favorite_dummy";
			lockbutton = "_matrixbox_lock_dummy";
			publicstyle = "opacity:0.4";
			//favoritecon = "";
			lockicon ="";
		}
		if (user_id == ""){
			fav_publicstyle= "opacity:0.4";
			favoritebutton = "_matrixbox_favorite_dummy";
			favoritecon = "";
		}

		if( device_list[i].service_type == "P2P" || device_list[i].device_type == "p2p");
		else
			refreshbutton = "";

		if (device_list[i].service_type == "nvr") {
			websettingbutton = "";
			fullscreenbutton = "";
			mutesettingbutton = "";
			refreshbutton = "";
		}

		html_to_insert += 	"<span style='"+ fav_publicstyle +"' class='" + favoritebutton + " " + favoritecon + "' title='"+ t_js.device_matrix_MSG['0001'] + "'></span>" +
								"<span style='"+ publicstyle +"' class='" + lockbutton + " " + lockicon + "' title='"+t_js.device_matrix_MSG['0002']+"' ></span>" + //nail_position='" + nailpos +"'
								"<span style='" + publicstyle + "' class='"+ singal_default +"' title='"+t_js.device_matrix_MSG['0003']+"'></span>" +
								"<span style='" + publicstyle + "' class='"+ fullscreenbutton +"' title='"+t_js.device_matrix_MSG['0004']+"'></span>" +
								//"<span style='" + publicstyle + "' class='"+ controlpanelbutton +"' title='Control Panel'></span>" +
								"<span style='" + publicstyle + "' class='"+ websettingbutton +"' title='"+t_js.device_matrix_MSG['0005']+"'></span>" +
								"<span style='" + publicstyle + "' class='"+ mutesettingbutton +"' title='"+t_js.device_matrix_MSG['0006']+"'></span>" +
								"<span style='" + publicstyle + "' class='"+ refreshbutton +"' title='"+t_js.device_matrix_MSG['0007']+"' onclick='refreshSinglePlayer("+device_list[i].id+")'></span>" +
								//"<span style='" + publicstyle + "' class='"+ closebutton +"' title='Remove'></span>" +
							 "</div>" + "<div class='_matrix_time'></div>" +
							 "<div class='_matrix_boxcontent'>";


		if(true)
		{
			if( device_list[i].service_type == "WEB" && device_list[i].device_type == "") //old style, deprecated
			{
				html_to_insert += "<div class='device_matrix_y'>";
				html_to_insert += "<div class='icon_nb' style='z-index:100;cursor:pointer;margin-top:10px;margin-left:60px;' ";
				html_to_insert += " onClick='PopupDeviceViewWindow(device_list[" ;
				html_to_insert += i ;
				html_to_insert += "]);' /></div>" ;
			}
			else if( device_list[i].device_type == "p2p" && (device_list[i].service_type == "camera" || device_list[i].service_type == "nvr"))
			{
				var GetArray = PrepareDeviceGetParameters(device_list[i]);
				var URL = 'device.php?' + GetArray + "&ToTab=0";

				html_to_insert +=  '<div id="p2p_' + device_list[i].id + '" class="p2p-container" data-id="' + device_list[i].id + '">' +
								   '</div>';

				p2p_data[device_list[i].id] = Object();
				p2p_data[device_list[i].id]["pos"] = i;
				p2p_data[device_list[i].id]["uid"] = device_list[i].uid;

				//Get Quality from cookie, and set url_path
				var quality = GetCookie("quality_grp_" + device_list[i].uid);
				if (quality == "") {
					if (device_list[i].ME_URL != null && device_list[i].ME_URL != "null")
						quality = 1;
					else
						quality = 2;
				}
				else {
					quality = parseInt(quality);
				}
				var _url_path = device_list[i].url_path;
				var _port = device_list[i].port;
				if (device_list[i].stream_url_path) {
					_url_path = device_list[i].stream_url_path;
					_port = device_list[i].stream_port;
				}
				else {
					if (quality == 0 && (device_list[i].HI_URL != null && device_list[i].HI_URL != "null")) _url_path = device_list[i].HI_URL;
					if (quality == 1 && (device_list[i].ME_URL != null && device_list[i].ME_URL != "null")) _url_path = device_list[i].ME_URL;
					if (quality == 2 && (device_list[i].LO_URL != null && device_list[i].LO_URL != "null")) _url_path = device_list[i].LO_URL;
				}

				if (device_list[i].dataplan != "D") {
					p2p_data[device_list[i].id]["url_prefix"] = device_list[i].url_prefix;
					p2p_data[device_list[i].id]["url_path"] = _url_path;
					p2p_data[device_list[i].id]["local_uri"] = device_list[i].local_uri;
					p2p_data[device_list[i].id]["stream_local_uri"] = device_list[i].stream_local_uri;
				}
				p2p_data[device_list[i].id]["start"] = false;
				p2p_data[device_list[i].id]["relay_time"] = 0;
				p2p_data[device_list[i].id]["connect_type"] = "";
				p2p_data[device_list[i].id]["signal"] = 0;
				p2p_data[device_list[i].id]["state"] = 0;
				p2p_data[device_list[i].id]["disconnect_time"] = 0;
				p2p_data[device_list[i].id]["playing"] = 0;
				p2p_data[device_list[i].id]["webport"] = -1;
				if (device_list[i].dataplan != "D") {
					p2p_data[device_list[i].id]["uri"] = ""; //ip address
					p2p_data[device_list[i].id]["tcp_socket_port"] = -1;
					p2p_data[device_list[i].id]["req_cm_port"] = device_list[i].CM_PORT;
					p2p_data[device_list[i].id]["req_rtsp_port"] = _port;
					if ( device_list[i].purpose == "WBNV" && !device_list[i].req_web_port)
						p2p_data[device_list[i].id]["req_web_port"] = device_list[i].port;
					else
						p2p_data[device_list[i].id]["req_web_port"] = device_list[i].req_web_port;
					p2p_data[device_list[i].id]["req_web_url"] = device_list[i].WBSS_URL;
				}
				p2p_data[device_list[i].id]["features"] = fillEmptyString(device_list[i].features);
				p2p_data[device_list[i].id]["default_id"] = fillEmptyString(device_list[i].default_id);
				p2p_data[device_list[i].id]["default_pw"] = fillEmptyString(device_list[i].default_pw);
				p2p_data[device_list[i].id]["model_id"] = device_list[i].model_id;
				p2p_data[device_list[i].id]["model"] = fillEmptyString(device_list[i].model);
				p2p_data[device_list[i].id]["service_type"] = device_list[i].service_type;
				p2p_data[device_list[i].id]["owner_id"] = device_list[i].owner_id;

				for (var j=0; j<nvr_list.length; j++) {
					if (nvr_list[j].linked == device_list[i].uid) {
						if (nvr_list[j].purpose == "WBNV") {
							p2p_data[device_list[i].id]["nvr_id"] = nvr_list[j].uid;
							p2p_data[device_list[i].id]["req_nvr_port"] = nvr_list[j].port;
						}
						else if (nvr_list[j].purpose == "WBNA") {
							p2p_data[device_list[i].id]["req_nvr_port2"] = nvr_list[j].port;
						}
					}
				}

				if (isFullscreening && i==0){
					isFullscreening_id = device_list[i].id;
					p2p_data[device_list[i].id]["fullscreen"] = true;
				}
				else
					p2p_data[device_list[i].id]["fullscreen"] = false;
				p2p_data[device_list[i].id]["mac_addr"] = device_list[i].mac_addr;

			}
			else if ( device_list[i].service_type == "REMOTE" )
			{
				html_to_insert += "<div class='matrix_remote'  onClick='RemoteClick(" ;
				html_to_insert += i ;
				html_to_insert += ");' ></div>" ;
			}
			else if ( device_list[i].device_type == "syncme" || device_list[i].service_type == "syncme" ) //device_list[i].device_type == "syncme" : 1.0 rule
			{
				html_to_insert += "<div class='device_matrix_y'>";
				html_to_insert += "<div class='icon_nb' style='z-index:100;cursor:pointer;margin-top:10px;margin-left:60px;' ";
				html_to_insert += " onClick='openSyncme(device_list[" ;
				html_to_insert += i ;
				html_to_insert += "]);' /></div>" ;
			}
			else html_to_insert +=  GetViewFullItem(device_list[i]) ;
		}

		html_to_insert +=   "</div>"+
						  "</div>";
	}

	// apply the html
	$('div#device_matrix').html( html_to_insert );

	//dummy frame
	PutInDummy();

	if (isFullscreening){ //if fullscreening , reset POPVLCframe on 1st vlc
		parent.POPVLCFrame(isFullscreening_id,0,p2p_data[isFullscreening_id].mac_addr);
	}

	// fix iframe java loading bug
        if (isFullscreening) {
                setTimeout("createAllPlayers(true);", redisplay_delay);
        }
        else {
                setTimeout("createAllPlayers(false);", redisplay_delay);
        }
        redisplay_delay = 0; // only delay first load

        setTimeout(function(){
                window.clearInterval(Timeout_checkWebSettingButton);
                Timeout_checkWebSettingButton = self.setInterval("checkWebSettingButton();",1000);
        },5000);
}

function createAllPlayers(isFullscreening) {
	var counter = 1;
	for(var i in p2p_data){
		if (p2p_data[i]['pos'] == 0 && isFullscreening) {
			createPlayer(i,true,isFullscreening);
		}
		else {
			createPlayer(i,false,isFullscreening);
		}
		counter++;
	}
}

function openControlPanel(i){ //Switch to Full Screen
	window.open("java_panel.php?port="+i,"Ratting","width=130,height=60,menubar=0,status=0,location=0,resizable=0,scrollbars=0,toolbar=0");
}

function openJavaTutorial(i){
	parent.openJavaTutorial(i);
}

function resetAppletWarning(i){
	if ($.client.os != "Mac")
		$('#p2p_'+i+' .applet_text_area').find('.applet_warning').removeClass('off');
	$('#p2p_'+i+' .applet_text_area').find('.relay_alert_layer').addClass('off');
	$('#p2p_'+i+' .applet_text_area').find('.relay_button_layer .btn_sharef').eq(0).removeClass('off');
	resetRelayCounter(i);
	p2p_data[i]["signal"] = 1;
	document.getElementById("applet_" + i).Play_Live_Safe();
}

function relayCounterHandle(i){
	try{
		var isPlaying = GetIsPlaying(i);
	}catch(err){
		return;
	}
	if (p2p_data[i]["relay_time"] <= 0 ) return;
	if (isPlaying == true) p2p_data[i]["relay_time"]--;
	//p2p_data[i]["relay_time"]
	//var played_sec = MAX_MIN*60 - Math.floor((new Date() - p2p_data[i]["relay_time"])/1000);
	var sec = p2p_data[i]["relay_time"] % 60;
	var min = (p2p_data[i]["relay_time"]-sec) / 60;
	var LEDTime = ConverIntegerToTwoDigit(min)+":"+ConverIntegerToTwoDigit(sec);
	$('#device_matrix ._matrix_iconbox[ref_id="'+i+'"]').next('._matrix_time').eq(0).html(LEDTime);
	if (p2p_data[i]["fullscreen"] == true)
		parent.PrintTimer(LEDTime);

	if (p2p_data[i]["relay_time"] <= 0){
		p2p_data[i]["signal"] = 3;
		document.getElementById("applet_" + i).Stop_Safe();
		parent.RelayAlertLayer(true,p2p_data[i]["connect_type"],i);
		MinimizeOne(i);
		$('#p2p_'+i+' .applet_text_area').find('.applet_warning').addClass('off');
		switch(p2p_data[i]["connect_type"]){
			case "relay":
				$('#p2p_'+i+' .applet_text_area').find('.relay_alert_layer').removeClass('off').find('.ps_message').html(t_js.device_matrix_MSG['0008']);
			break;
			case "cloud":
				$('#p2p_'+i+' .applet_text_area').find('.relay_button_layer .btn_sharef').eq(0).addClass('off');
				$('#p2p_'+i+' .applet_text_area').find('.relay_alert_layer').removeClass('off').find('.ps_message').html(t_js.device_matrix_MSG['0009']);
			break;
		}
	}
}

//put on Web Setting Button if aquire p2p WEB port
function checkWebSettingButton(){
	for(var i in p2p_data){
		if (p2p_data[i]["signal"] == 1){
			relayCounterHandle(i);
			//continue;
		}
		if (p2p_data[i]["signal"] == 2){
			refreshSinglePlayer(i);
			continue;
		}
		if (p2p_data[i]["webport"] != -1) continue;
		try { //Applet on Chrome will generate slower, need try catch prevent error
			var port = document.getElementById("applet_" + i).GetWebPort().toString();
			var uri = document.getElementById("applet_" + i).GetWebURI();
			//eval("var port = document.applet_" + i  + ".GetWebPort().toString();");
		}
		catch(err){
			continue;
		}
		if (port == -1) continue;	//acquiring port
		else if (port == 0) p2p_data[i]["webport"] = 0; // acquire port failed.
		else{
			if (p2p_data[i]["start"] == true) continue;
			p2p_data[i]['connect_type'] = document.getElementById("applet_" + i).GetConnectionType().toString();
			resetRelayCounter(i);
			relayCounterHandle(i);
			p2p_data[i]["start"] = true;
			p2p_data[i]["uri"] = uri;
			p2p_data[i]["webport"] = port;
			p2p_data[i]["tcp_socket_port"] = document.getElementById("applet_" + i).GetCCPort().toString();

			$('#device_matrix ._matrix_iconbox').each(function(){
				var ref_id = $(this).attr('ref_id');
				if (i.toString() == ref_id){ //id match
					var web_button = $(this).find('._matrixbox_web_setting_off').eq(0);
					if (p2p_data[i].owner_id == user_id) {
						web_button.removeClass('_matrixbox_web_setting_off').addClass('_matrixbox_web_setting').attr('onClick','openWeb("'+uri+'",' + port + ');');
					}
					if (p2p_data[i]["service_type"] != 'nvr') {
						var fulls_button = $(this).find('._matrixbox_fullscreen_off').eq(0);
						fulls_button.removeClass('_matrixbox_fullscreen_off').addClass('_matrixbox_fullscreen_on').attr('onClick','openFullScreen(' + i + ');');
						var fulls_button = $(this).find('.muteButton').eq(0);
						fulls_button.removeClass('_matrixbox_mute_setting_off').addClass('_matrixbox_mute_setting_on_silent').attr('onClick','mutePlayer(' + i + ');');
					}
					var control_panel_button = $(this).find('._matrixbox_control_panel_off').eq(0);
					control_panel_button.removeClass('_matrixbox_control_panel_off').addClass('_matrixbox_control_panel_on').attr('onClick','openControlPanel(' + port + ');');
					var applet_nvr = $(this).parent().find("div.applet_nvr").eq(0);
					$(applet_nvr).removeClass("applet_nvr_off").addClass("applet_nvr_on").attr('onClick','openWeb("'+uri+'",' + port + ');');
					return false;
				}
			});
		}
	}
}



function openWeb(uri,i){
	window.open("http://" + uri + ":" + i);
}

function remove_loading(id){
	$('._matrix_iconbox').each(function(){
		if (id == $(this).attr('ref_id')){
			$(this).parent().find('._device_loading_cover_text').eq(0).html("");
			$(this).parent().find('._device_loading_cover').eq(0).removeClass('_device_loading_cover_showtext');
			$(this).parent().find('._device_loading_cover').eq(0).removeClass('displayblock');

			return false;
		}
	});
}

function openSyncme(device_entry){
	if( device_entry == null ) return;

	var view_url = "";

	if (device_entry.device_type=="p2p"){ //device_entry.device_type=="p2p"
		var url_path = device_entry.url_path;
			if (url_path=="/")
				url_path = "/SMW-100/";

		$('._matrix_iconbox').each(function(){
			if ($(this).attr('ref_id') == device_entry.device_id){
				$(this).parent().find('._device_loading_cover_text').eq(0).html("");
				$(this).parent().find('._device_loading_cover').eq(0).removeClass('_device_loading_cover_showtext');
				$(this).parent().find('._device_loading_cover').eq(0).addClass('displayblock');
				return false;
			}
		});


		var obj = parent.QueryPort(device_entry.uid , device_entry.port);
		if (obj["status"] == "success"){
			if (obj["port1"] == 0) //P2P initialize failed
			{
				$('._matrix_iconbox').each(function(){
					if ($(this).attr('ref_id') == device_entry.device_id){
						$(this).parent().find('._device_loading_cover').eq(0).addClass('_device_loading_cover_showtext');
						$(this).parent().find('._device_loading_cover_text').eq(0).html(t_js.device_matrix_MSG['0010']);
						return false;
					}
				});

				setTimeout("remove_loading("+device_entry.device_id+");",3000);
				return false;
			}
			else if (obj["port1"] == -1 || obj["port1"] == -2) //P2P still initializing
			{

				clearTimeout(Timeout_checkWebSettingButton);
				Timeout_checkWebSettingButton = setTimeout(function(){
					openSyncme(device_entry);
				},1000);
				return false;
			}
			else{		//complete
				remove_loading(device_entry.device_id);
				view_url = device_entry.url_prefix + "127.0.0.1" + ":" + obj["port1"] + url_path;
			}
		}else{
			$('._matrix_iconbox').each(function(){
				if ($(this).attr('ref_id') == device_entry.device_id){
					$(this).parent().find('._device_loading_cover').eq(0).addClass('_device_loading_cover_showtext');
					$(this).parent().find('._device_loading_cover_text').eq(0).html(t_js.device_matrix_MSG['0011']);
					return false;
				}
			});
			setTimeout("remove_loading("+device_entry.device_id+");",3000);
			return false;
		}
	}
	else{
	// the view url
		view_url = GetPopupViewURL( device_entry );
	}

	/*window.open(view_url,"_self");*/
	parent.loginAndShow(view_url);
}

//Mac Special CSS difination
function Mac_Specify_FullScreen(bool){
	if ($.client.os != "Mac") return;
	$('div#device_matrix').find('._device_single_frame').each(function(){
		var ref_id = $(this).find('._matrix_iconbox').attr('ref_id');
		if (bool){
			if (ref_id == undefined){
				$(this).addClass('hidden');
				return true;
			}
			if (p2p_data[ref_id]["fullscreen"] == false)
				$(this).addClass('hidden');
			else
				$(this).removeClass('hidden');
		}
		else{
			if (ref_id == undefined){
				$(this).removeClass('hidden');
				return true;
			}
			$(this).removeClass('hidden');
		}
	});
}

function RemoteClick(i){
	var GetArray = PrepareDeviceGetParameters(device_list[i]);
	var URL = 'device.php?' + GetArray + "&ToTab=0";
	if (device_list[i].device_type != "p2p")
		CallParentIframe(URL);
	else {
		OpenInvisIframe(URL);
	}
}

function PutInDummy(){
	var dummy = "<div class='_device_single_frame ui-state-disabled' style=''><div class='_matrix_title isdummy'>&nbsp;&nbsp;</div><div class='_matrix_iconbox isdummy'><span 					class='_matrixbox_favorite_dummy'></span><span class='_matrixbox_lock_dummy'></span>"+
				"</div>"+
				"<div class='_matrix_boxcontent isdummy'></div></div>";

  var s_count = $('div#device_matrix').find('._device_single_frame').length;//var s_count = $('div#device_matrix').find('._device_single_frame').size();//jinho fix
	//alert(s_count);
	var needed = 6 - s_count;
	for(var i =0 ; i<needed; i++) {
		$('div#device_matrix').html($('div#device_matrix').html()+dummy);
	}
}

function QuerySignal(shared_mode){
	if (shared_mode) {
		command = "list_client_by_visitor";
	}
	else {
		command = "list_client_by_user";
	}
	$.getJSON(
	"backstage_signal.php",
	{
		command: command,
	},
	function( data )
	{
		if (data["status"] == "success") {
			var isFullscreen = false;
			for(var j in p2p_data){
				if(p2p_data[j]["fullscreen"] == true)
					isFullscreen = true;
			}
			$('._device_single_frame').each(function(){
				var uid = $(this).find('._matrix_iconbox').attr('ref_uid');
				var state = 0; //0 = red , 1 = yellow , 2 = green

				if (data["clients"].hasOwnProperty(uid) == true) state = 1; //found on Signal List = Yellow

				var ref_id = $(this).find('._matrix_iconbox').attr('ref_id');
				if (!ref_id || (isFullscreen && !p2p_data[ref_id]["fullscreen"]))
					return;

				if (ref_id != undefined){
					try{
						var isplaying = GetIsPlaying(ref_id);
						if (isplaying)
							state = 2;
					}catch(err){
					}
					try{
						if ((state == 2) && p2p_data[ref_id]['signal'] == 0){
							resetRelayCounter(ref_id);
							p2p_data[ref_id]['signal'] = 1;
						}
						if (state == 0 && p2p_data[ref_id]['signal'] == 1 && p2p_data[ref_id]["disconnect_time"] < 60)
							p2p_data[ref_id]["disconnect_time"]++;
						if ((state == 1 || state == 2) && p2p_data[ref_id]["signal"] == 1 && p2p_data[ref_id]["disconnect_time"] > 1)
							p2p_data[ref_id]["signal"] = 2;
					}catch(err){
					}
					p2p_data[ref_id]['state'] = state;
				}
			});
		}

		//device add, refresh trigger
		if (data["refresh_trigger"] && !Shadowbox.isOpen() && !parent.isCoverOpen()){
			window.location.href = window.location.href.replace("checkdevice=true","checkdevice=");
		}
	}).error(function() {
		/** Query Signal Error **/
	});
}
var retries = {};
function changeSignalLight(){
    if (enlargeWindow) { // ignore when enlargeWindow is open.
        return;
    }
	var isFullscreen = false;
	for(var j in p2p_data){
		if(p2p_data[j]["fullscreen"] == true)
			isFullscreen = true;
	}

	$('._device_single_frame').each(function(){
		var ref_id = $(this).find('._matrix_iconbox').attr('ref_id');
		if (!ref_id || (isFullscreen && !p2p_data[ref_id]["fullscreen"]))
			return;
		var state = (p2p_data.hasOwnProperty(ref_id)) ? p2p_data[ref_id]["state"] : 0;
		try{
			var isplaying = GetIsPlaying(ref_id);
			if (isplaying)
				state = 2;
		}
		catch(err){
            if (ref_id && err.message === 'Error calling method on NPObject.') {
                    refreshSinglePlayer(ref_id);
            }
            else if (ref_id && err.name == "TypeError") {
				if (!(ref_id in retries)) {
					retries[ref_id] = 1;
				}
				else if (retries[ref_id] < 5) {
					retries[ref_id]++;
				}
				else {
					refreshSinglePlayer(ref_id);
				}
			}
		}
		$(this).find('.singal_default').removeClass('singal_red').removeClass('singal_yellow').removeClass('singal_green');
		switch(state){
			case 0:
				$(this).find('.singal_default').addClass('singal_red');
			break;
			case 1:
				$(this).find('.singal_default').addClass('singal_yellow');
			break;
			case 2:
				$(this).find('.singal_default').addClass('singal_green');
			break;
			default:
				$(this).find('.singal_default').addClass('singal_red');
			break;
		}
		if ( p2p_data.hasOwnProperty(ref_id) && p2p_data[ref_id]["fullscreen"] && typeof parent["changeSignal"] == "function" )
			parent.changeSignal(state);
	});
}

function resetRelayCounter(i){
	var MAX_MIN = 0;
	switch(p2p_data[i]["connect_type"]){
		case "relay":
		MAX_MIN = 2;
		break;
		case "cloud":
		MAX_MIN = 3;
		break;
		default:
		return;
	}
	p2p_data[i]['relay_time'] = MAX_MIN * 60;
}

function  frame_loading_cover(bool){
	if (bool){
		$('#frame_loading_cover').removeClass('off');
	}
	else{
		$('#frame_loading_cover').addClass('off');
	}
}

function frame_cover_init(){
	if (Object.size(p2p_data)==0) return;
	MinimizeAll();
	frame_loading_cover(true);
	frame_close_detect();
}
function frame_close_detect(){
	for(var i in p2p_data){
		try{
			document.getElementById("applet_" + i).Stop_Safe();
		}catch(err){
			setTimeout("frame_close_detect()",1000);
			return;
		}
		frame_loading_cover(false);
		ResizeAll();
		return;
	}
}

function GetIsPlaying(ref_id) {
	var isplaying;
	if (p2p_data[ref_id]["service_type"] == "nvr")
		isplaying = parseInt(document.getElementById("applet_" + ref_id).GetWebPort().toString())>0;
	else
		isplaying = document.getElementById("applet_" + ref_id).GetisPlaying();
	return isplaying;
}

function RefreshInfoSystem() {
	ret = TimestampCookie("info");
	if (!ret[0]) {
		InfoSystemClose();
		return;
	}
	check_timestamp = ret[1];
	$.getJSON("backstage_info_system.php", function(data) {
		if (data && data.status == "success")
			createCookie("check_timestamp", check_timestamp, 1);

		if (data && data.update_server_addr) {
			update_server = data.update_server;
		}

		if (data && data.status == "success" && data.devices && data.devices.length>0) {
			autoupdate_list = data.devices;
			content='<div class="reg_frame">' +
				'<div class="infosystem_frame" id="infosystem_frame">' +
						'<table id="infosystem_table" class="layouttable_2"><thead>' +
							'<tr><th>' + t_js.device_matrix_MSG['0012'] + '</th><th>' + t_js.device_matrix_MSG['0013'] + '</th><th>' + t_js.device_matrix_MSG['0014'] + '</th><th>' + t_js.device_matrix_MSG['0015'] + '</th><th>' + t_js.device_matrix_MSG['0016'] + '</th></tr></thead><tbody id="infosystem_body">';
			var count = autoupdate_list.length>10?autoupdate_list.length:10;
			for (var i=0; i<count; i++) {
				var cls = i%2?"odd":"even";
				if (i>=autoupdate_list.length) {
					content = content + '<tr class="' + cls + '"><td></td><td></td><td></td><td></td><td></td>';
				}
				else {
					content = content + '<tr id="info_system_' + i + '" class="' + cls + '"><td>' + autoupdate_list[i].name + '</td><td>' + autoupdate_list[i].mac_addr + '</td><td>' + autoupdate_list[i].current + '</td><td>' + autoupdate_list[i].proposed + '</td><td><div class="cursprpointer btn_device_matrix _checkbox-enable" onclick="toggle_checkbox(this);"></div></td>';
				}
			}

			content = content + '</tbody></table></div>' +
				'<div class="btn_sharef" id="infosystem_step_ok" style="position:absolute;right:120px;bottom:20px;" onclick="AutoUpdate();">'+
				t_js.device_matrix_MSG['0017'] + '</div>' +
				'<div class="btn_sharef" id="infosystem_step_later" style="position:absolute;right:30px;bottom:20px;" onclick="InfoSystemClose();">'+
				t_js.device_matrix_MSG['0018'] + '</div>' +
					'</div>';
			ShadowboxPage=6;
			Shadowbox.open({content: content,player:"html",title:"",height:400,width:800});
		}
		else {
			InfoSystemClose();
		}
	});
}

function InfoSystemClose() {
	if ($("#sb-container").is(":visible")) {
		ShadowBoxClose();
	}
	matrix_display_mode = parseInt($("select#display_type-select").val());
	RefreshDisplay( mode, $("select#display_type-select").val(), order_by );
}


function AutoUpdate() {

	$("#infosystem_body tr").each(function(index, value) {
		if ($(this).find("._checkbox-enable").length>0) {
			if (index < autoupdate_list.length) {
				command = autoupdate_list[index].uid + " " + autoupdate_list[index].proposed + " " + autoupdate_list[index].fw_url;
				autoupdate_command.push(command);
			}
		}
	});

	if (autoupdate_command.length ==0) {
		InfoSystemClose();
		return;
	}
	ShadowBoxClose();
	applyBegin();

	var player = document.createElement("applet");
	try {
		if (p2p_jars)
			player.setAttribute("archive", p2p_jars);
		else
			player.setAttribute("archive", CONFIG.ROOT_URL + "jar/ref_library.jar," + CONFIG.ROOT_URL + "jar/SAT_P2P.jar");
	}
	catch (e) {
		player.setAttribute("archive", CONFIG.ROOT_URL + "jar/ref_library.jar," + CONFIG.ROOT_URL + "jar/SAT_P2P.jar");
	}
	player.setAttribute("code", "com/qlync/AutoUpdate.class");
	player.id = "autoupdate_applet";
	player.style.width="0px";
	player.style.height="0px";
	var param;
	param = document.createElement('param');
	param.name = "java_status_events";
	param.value = "true";
	player.appendChild(param);

	param = document.createElement('param');
	param.name = "java_arguments";
	param.value = "-Xmx256m";
	player.appendChild(param);

	param = document.createElement('param');
	param.name = "update_server_addr";
	param.value = update_server[0];
	player.appendChild(param);

	param = document.createElement('param');
	param.name = "update_server_port";
	param.value = "" + update_server[1];
	player.appendChild(param);

	document.body.appendChild(player);

	window.setTimeout(AutoUpdateSend, 1000);
}

function AutoUpdateSend() {
	var applet = document.getElementById("autoupdate_applet");
	if (!applet) {
		applyEnd();
		return;
	}

	var status = 1;
	try {status = applet.status}
	catch (e) {status = 1;}
	//alert(status);
	if (typeof status == "undefined")
		status = 1;
	if (status == 1) {
		window.setTimeout(AutoUpdateSend, 500);
	}
	else if (status == 2) {
		for (var i=0; i<autoupdate_command.length; i++) {
			applet.SendUpdate(autoupdate_command[i]);
		}
		window.setTimeout(AutoUpdateJoin, 1000);
	}
}

function AutoUpdateJoin() {
	var applet = document.getElementById("autoupdate_applet");
	if (applet.isRunning()) {
		//alert("running");
		window.setTimeout(AutoUpdateJoin, 500);
	}
	else {
		//alert("finished");
	}
	applyEnd();
	window.location.href=window.location.href;
}

/**
 * Open large matrix view.
 */
function OpenLargeMatrix() {
    var loc = window.location;

    // Path should relative to ROOT_URL
    var viewLocation = CONFIG.OEM_URL.substr(CONFIG.ROOT_URL.length) + loc.pathname.substr(1);

    // Append view_location to queryString
    var queryString = loc.search;
    queryString += ( 0 === queryString.indexOf('?') ? '&' : '?' ) + 'view_location=' + encodeURIComponent(viewLocation);

    var url = CONFIG.OEM_URL + 'index.php' + queryString;

    window.top.location.href = url;
}
