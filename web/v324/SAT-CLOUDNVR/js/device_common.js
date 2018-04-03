var user_list = new Array();
var user_id_to_name_map = new Array();
var localhost_mac = null;
var config_ini = null;
var now_uid = null;

// service types
var service_type_list = { "web": "Web", "router": "Router" ,"remotedesktop":"Remote", "syncme":"SyncMe", "camera":"Camera", "streaming":"Streaming", "nvr":"NVR"};
// device types list
var device_type_list = { "default": "DEFAULT", "p2p": "P2P"};
var cameraFinderVar = {};

function PrepareDeviceGetParameters( device_entry )
{
	// check if null
	if( device_entry == null ) return "";

	return $.param(device_entry);
}

function GetLocateURL( device_entry )
{
	return "device_map.php?" + PrepareDeviceGetParameters(device_entry);
}

function GenerateMPlayerObject( id, src )
{
	return ("<a class=\"media {type: 'wmv'}\" href=\"" + src + "\"></a>");
}

function ParseDeviceIpAddress( device_entry )
{
	// prepare the var to return
	var ret = {
		prefix: "",
		address: "",
		port: device_entry.port,
		path: ""
	};

	// check prefix (check if it has ???://)
	var prefix_end_pos = device_entry.ip_addr.indexOf("://");
	if( prefix_end_pos == -1 )
	{
		prefix_end_pos = 0;
		address_start_pos = 0;
		ret.prefix = "http://";
	}
	else
	{
		prefix_end_pos = prefix_end_pos + 3;
		ret.prefix = device_entry.ip_addr.substr(0, prefix_end_pos);
	}

	// check address
	var port_start_pos;
	var address_end_pos = port_start_pos = device_entry.ip_addr.indexOf(":", prefix_end_pos);
	if( address_end_pos == -1 ) address_end_pos = device_entry.ip_addr.indexOf("/", prefix_end_pos);
	if( address_end_pos == -1 )
	{
		// don't have path or port, just return
		ret.address = device_entry.ip_addr.substr(prefix_end_pos);
		return ret;
	}
	else
	{
		ret.address = device_entry.ip_addr.substr(prefix_end_pos, address_end_pos-prefix_end_pos);
	}


	// check port (always use the assigned port)
	var port_end_pos;
	if( port_start_pos == -1 )
		port_end_pos = address_end_pos;
	else
	{
		port_start_pos = port_start_pos + 1;
		for( port_end_pos=port_start_pos;
			port_end_pos<device_entry.ip_addr.length && device_entry.ip_addr.substr(port_end_pos, 1) != "/";
			port_end_pos++ );
	}

	// check path
	ret.path = device_entry.ip_addr.substr(port_end_pos);

	return ret;
}

function GetViewURL( device_entry )
{
	if( device_entry == null ) return "";


	var url_path = device_entry.url_path;
	if ((device_entry.device_type=="syncme" && url_path=="/") || (device_entry.service_type=="syncme" && device_entry.device_type=="p2p" && url_path=="/"))
		url_path = "/SMW-100/";
	// for sat router conflict
//	if( device_entry.sat_router_conflict == true ) return "warning_page.php";


	// check if view from lan
	if( js_common_user_ip != "" && device_entry.ip_addr == js_common_user_ip && // the user access from LAN
		device_entry.internal_ip_addr != "" && device_entry.internal_port != "" )
		return device_entry.url_prefix + device_entry.internal_ip_addr + ":" + device_entry.internal_port + url_path;

	// return the normal result
	return device_entry.url_prefix + device_entry.ip_addr + ":" + device_entry.port + url_path;
}

function GetViewFullItem( device_entry )
{
	if( device_entry == null ) return "";

	// for WEB
	if( device_entry.service_type == "WEB" )// | device_entry.sat_router_conflict == true )
	{
		var view_url = GetViewURL(device_entry);
		var smw_path = "SMW-100/";
		if( device_entry.device_type == "syncme" ) {
			// For chrome
			if (navigator && navigator.userAgent && navigator.userAgent.indexOf("Chrome") != -1) {
				var ts = Math.round((new Date()).getTime() / 1000);
				var login_url = view_url + smw_path + "flash_login.html?r=" + ts;
				var win = window.open(login_url, "_blank", "menubar=no,resizable=no,scrollbars=no,status=no,titlebar=no,toolbar=no,width=400,height=300");
				var timer = setInterval(function() {
					var $note = $("#syncme_login_message");
					if (win.closed && $note) {
						clearInterval(timer);
						$note.attr("src", view_url + smw_path);
					}
				}, 600);
				return ("<iframe id=\"syncme_login_message\" src=\"/SAT/syncme_login_message.php\"></iframe>");
			}
			// For other browsers
			else {
				return ("<iframe src=\"" + view_url + smw_path + "\"></iframe>");
				//return ("<iframe src=\"/SAT2/login.jsp?url=" + GetViewURL(device_entry) + "&user_name=" + user_id_to_name_map[device_entry.owner_id] + "\"></iframe>");
			}
		}
		else
			return ("<iframe src=\"" + view_url + "\"></iframe>");
	}
	// for MEDIA
	else if( device_entry.service_type == "MEDIA" ) return GenerateMPlayerObject( "media_player_embed", GetViewURL(device_entry) );
	// for REMOTE
	else if( device_entry.service_type == "REMOTE" ) return ("<iframe src='javaRDP/remoteDesktop.php?ip="+ GetViewURL(device_entry) +"'></iframe>");
	// for P2P
	else if (device_entry.service_type == "P2P" || device_entry.device_type == "p2p"){
		//if (localhost_mac == null ) return "";
		//alert(device_entry.mac_addr + " "  + localhost_mac + " " + device_entry.id );
		var config_http = "../backstage_config.php?mac_addr=" + device_entry.mac_addr + "&local_mac_addr=" + localhost_mac +
						  "&remote_id=" + device_entry.id;
		$.get(config_http, function(data) {
			// this is ActiveX Code, only work under IE, need cover by Try Catch
		   try{
				ODM_T02.InputParameter = data;
				ODM_T02.LoadParameter();
			} catch(e) {}
		});
		return "";
	}
	return "";
}

function SetupServiceTypeOptions()
{
	// set select option
	var str_array = new Array();
	for( var i in service_type_list )
	{
		//hot code
		var username = "";
		try{ username = parent.user_name; }catch(e){ }
		//if (i == "P2P" && (username != "sat_demo" && username != "p2p_demo" && username != "thinx_demo")) continue;

		str_array.push( "<option value=\"" );
		str_array.push( i );
		str_array.push( "\">" );
		str_array.push( service_type_list[i] );
		str_array.push( "</option>" );
	}
	$('select#service_type-edit').html(str_array.join(''));

	// set select option
	var str_array = new Array();
	for( var i in device_type_list )
	{
		//hot code
		var username = "";
		try{ username = parent.user_name; }catch(e){ }
		//if (i == "P2P" && (username != "sat_demo" && username != "p2p_demo" && username != "thinx_demo")) continue;

		str_array.push( "<option value=\"" );
		str_array.push( i );
		str_array.push( "\">" );
		str_array.push( device_type_list[i] );
		str_array.push( "</option>" );
	}
	$('select#device_type-edit').html(str_array.join(''));
}

function GetPopupViewURL( device_entry )
{
	// the view url
	var view_url = GetViewURL( device_entry );

	// prepare the popup url
	// for WEB --> just use the view url
	// for MEDIA --> need to use our device_view
	if( device_entry.service_type == "MEDIA" ) view_url = "device_media_view.php?url=" + view_url;

	return view_url;
}

function PopupDeviceViewWindow( device_entry )
{
	if( device_entry == null ) return;

	// the view url
	var view_url = GetPopupViewURL( device_entry );

	// open the browser window
	window.open( view_url, "device_view_window", "status=1, toolbar=1, location=1, menubar=1, directories=1, resizable=1, scrollbars=1, height=600, width=800" );
}

function PrepareDeviceModifyParameterArray()
{
	var parameter_array = {
		command: "modify",
		id: $('input#record_no-edit').val(),
		name: $.trim($('input#name-edit').val()),
		owner_id: $.trim($('#owner_id-span').attr('value')),//(js_common_user_group_id=="0")?$('select#owner_id-edit').val():'',
		mac_addr: convertMAC($.trim($('input#mac_addr-edit').val())),
		identifier: $.trim($('input#identifier-edit').val()),
		service_type: $('select#service_type-edit').val(),
		device_type: $('select#device_type-edit').val(),
		ip_addr: $.trim($('input#ip_addr-edit').val()),
		port: $.trim($('input#port-edit').val()),
	};

	if (parameter_array.service_type=="SYNCME"){
		parameter_array.service_type="WEB";
		parameter_array.device_type="syncme";
	}
	//hot Code
	else if(parameter_array.service_type=="P2P"){
		parameter_array.device_type="p2p";
	}

	// check name
	if( parameter_array.name.trim() == '' || parameter_array.name.search(/["']/) != -1 )
	{
		Alert( t_js.device_common_MSG['0001'] );
		return null;
	}
	// check name
	if( parameter_array.owner_id == '' )
	{
		Alert( t_js.device_common_MSG['0002'] );
		return null;
	}

	var select_quality_val = $('input[name=quality_grp]:checked').val();
	createCookie("quality_grp_" + now_uid , select_quality_val , 7 );

	return parameter_array;
}

String.prototype.trim = function()
{
    return this.replace(/(^[\\s]*)|([\\s]*$)/g, "");
}

function SetDeviceConfigDlgContent( device_entry )
{
	if (device_entry==undefined){
		$('#device_config_table').find("input").each(function(){
			if ($(this).attr('type') == "text") $(this).val('');
			else if ($(this).attr('type') == "checkbox") $(this).val(false);
		});
		$('#device_config_table').find("select").each(function(){
			$(this).find('option').eq(0).attr('selected',true);
		});

		//add mode
		$('#service_type-edit').val(request_service_type);
		$('#device_type-edit').val(request_device_type);

		return;
	}

	// set data
	// set id
	$('input#record_no-edit').val(device_entry.id);

	// set name
	$('input#name-edit').val(device_entry.name);

	// set owner_id
	$('select#owner_id-edit').val(device_entry.owner_id);
	$('span#owner_id-span').prop('value',device_entry.owner_id);//$('span#owner_id-span').attr('value',device_entry.owner_id); //jinho

	applyUser(device_entry.owner_id);

	// set mac_addr
	$('input#mac_addr-edit').val(device_entry.mac_addr);

	// set port
	$('input#port-edit').val(device_entry.port);

	// process the config dialog for user type
	if( js_common_user_group_id == "0" )
	{
		// show all input
		$('select#owner_id-edit').show();

		// hide all span
		$('span#owner_id-span').hide();
	}
	else
	{
		// hide all input
		$('select#owner_id-edit').hide();

		// show all span
		$('span#owner_id-span').show();
	}

	//get quality from cookie , 0~2 = high ~ low
	var quality;
	now_uid = device_entry.uid;
	if (device_entry.quality == "RVHI") {
		quality = 0;
	}
	else if (device_entry.quality == "RVME") {
		quality = 1;
	}
	else {
			quality = 2;
	}

	$('input[name=quality_grp]').eq(quality).attr('checked',true);
	$('input[name=quality_grp]').attr('disabled',false);
//jinho check prop??
	if (device_entry.HI_URL == null)  $('input[name=quality_grp]').eq(0).attr('disabled',true);
	if (device_entry.ME_URL == null)  $('input[name=quality_grp]').eq(1).attr('disabled',true);
	if (device_entry.LO_URL == null)  $('input[name=quality_grp]').eq(2).attr('disabled',true);

	var _url_path = "";
	// set ip_addr
	if (quality == 0 && (device_entry.HI_URL != null && device_entry.HI_URL != "null")) _url_path = device_entry.HI_URL;
	if (quality == 1 && (device_entry.ME_URL != null && device_entry.ME_URL != "null")) _url_path = device_entry.ME_URL;
	if (quality == 2 && (device_entry.LO_URL != null && device_entry.LO_URL != "null")) _url_path = device_entry.LO_URL;
	$('input#ip_addr-edit').val(device_entry.url_prefix + device_entry.ip_addr + _url_path);

}

function SetScheduleConfigDlgContent(package_entry) {

	// Schedule
	if (package_entry.dataplan != 'SR') {
		$('input[name="schedule"]').attr('disabled', true);
	}
	else {
		$('input[name="schedule"]').attr('disabled', false);
		$('input[name="schedule"]').filter('[value="' + package_entry.schedule + '"]').attr('checked', true);
	}

}

var applyUserTimeout;
function applyUser(deviceid){
	/*clearTimeout(applyUserTimeout);
	if (user_id_to_name_map[deviceid] == undefined)
		applyUserTimeout = setTimeout("applyUser("+deviceid+");",200);
	else{
		$('span#owner_id-span').html(user_id_to_name_map[deviceid]);
	}*/
	if (user_name != undefined || user_name != "")
		$('span#owner_id-span').html(user_name);
}

function SetupMapSelector()
{
	// when click latitude/longitude, show the map
	$('input#geo_locate_lat-edit, input#geo_locate_lng-edit').click( function() {
		//parent.
		Shadowbox.open({
			content: 	"map_selector.php",
			player:     "iframe",
			title:      t_js.device_common_MSG['0006'],
			height:     $(window).height()-30,
			width:      $(window).width()
		});
	});
}

function SetupOwnerSelectOptions( input_user_list )
{
	// reset id to name map
	user_id_to_name_map = new Array();

	// check if input is null
	if( input_user_list == null ) user_list = new Array();
	else user_list = input_user_list;

	// get id to name map & add owner id option
	var str_array = new Array();
	for( var i=0; i<user_list.length; i++ )
	{
		// set id to name map for display
		user_id_to_name_map[user_list[i].id] = user_list[i].name;

		// set select option
		str_array.push( "<option value=\"" );
		str_array.push( user_list[i].id );
		str_array.push( "\">" );
		str_array.push( user_list[i].name );
		str_array.push( "</option>" );
	}
	$('select#owner_id-edit').html(str_array.join(''));
}

function DelDevice( id, callback_after_delete )
{

	Confirm(t_js.user_list_MSG['0001'], function(){
		// black screen
		var limit = $('#select_limit').val();
		// send data

		/**device.php dont need query list, so dont have following variable, put fake one, prevent null check error **/
		if (typeof(mode)==='undefined') mode = "personal";
		if (typeof(limit)==='undefined') limit = 0;
		if (typeof(order_by)==='undefined') order_by = "";
		if (typeof(asc_order)==='undefined') asc_order = "";
		/**************/
		if (typeof(request_device_type)   ===   'undefined ') request_device_type = "";
		if (typeof(request_service_type)  ===   'undefined ') request_service_type = "";

		$.getJSON(
			"backstage_device.php",
			{
				command: "delete",
				id: id,
				mode: mode,
				offset: 0,
				limit: limit,
				order_by: order_by,
				asc_order: asc_order,
				list_mode: true,
				showseq: true,
				request_device_type:request_device_type,
				request_service_type:request_service_type
			},
			function( data )
			{
				if( callback_after_delete ) callback_after_delete( data );

				// remove black screen
				applyEnd();
			}
		);
	},applyEnd());
}

function CallParentIframe(url){
	var c = $(window).eq(0)[0].parent;
	if (c != null)
		c.POPIframe(url);
}

function OpenInvisIframe(url,mac,id){
	var c = $(window).eq(0)[0].parent;
	// init shadowbox for map selector
	if (url.indexOf("&java=true") != -1){
		if (mac == undefined || id == undefined)
			return false;
		Shadowbox.init({
			skipSetup: true
		});

		Shadowbox.open({
			content:    '', /** this applet is out of update **/  //'<applet archive="IPCAMTunnel.jar" code="TestJ.class" width="200" height="200"><param name="mac_addr" value="'+mac+'"><param name="remote_id" value="'+id+'"></applet>',
			player:     "html",
			title:      t_js.device_common_MSG['0007'],
			height:     200,
			width:      200
		});
		return true;
	}

	if (c != null)
		c.IndexOpenInvisIframe(url);
}


//check DeviceCount at first time when page loaded, if count == 0 ,will ask user want add Device or not
function checkDeviceCount(){
	if (checkdevice != "true")
		return false;
	$.getJSON(
		"backstage_device.php",
		{
			command: "list",
			limit: 20,
			offset: 0,
			mode: "personal",
			order_by: 1,
			asc_order: "asc",
			list_mode: true,
			showseq: true,
			request_device_type:request_device_type,
			request_service_type:request_service_type
		},
		function( data )
		{
			if (data.device_total_count==0){
				if (request_service_type=="camera")
					OpenActiveBox();
			}
		}
	);
}

function OpenActiveBox(){
	ShadowBoxOpen(450,640);
}

function OpenGoogleAuthBox(){
	ShadowboxPage = 4;
	ShadowBoxOpen(200,450);
}

var shadowbox_messages = {
	connect: '<div class="reg_titletab"><div class="close_b" onClick="ShadowBoxClose();"></div></div>'+
				'<div class="step1_browser" id="ps_step_frame" style="margin-bottom:10px;">' +
				'<div class="ps_step_title">' + t_js.device_common_MSG['0022'] + '</div>' +
				'<div id="step1_text1">' + t_js.device_common_MSG['0028'] + '</div>' +
				'<div id="step1_text2">' + t_js.device_common_MSG['0023'] + '</div>' +
				'</div>' +
				'<div class="btn_sharef" id="ps_step_btn" onClick="stepChangePage();" style="position:absolute;right:30px;bottom:20px;">'+
				t_js.device_common_MSG['0008'] + '</div>',
	bind: '<div class="reg_frame"><div class="reg_titletab"><div class="close_b" onClick="ShadowBoxClose();"></div></div>'+
				'<div class="step2_browser" id="ps_step_frame" style="margin-bottom:10px;">' +
				'<div class="ps_step_title">' + t_js.device_common_MSG['0024'] + '</div>' +
				'<div id="step2_text1">'  + t_js.device_common_MSG['0010'] +  ':</div>' +
				'<div id="step2_text2">'  + t_js.device_common_MSG['0011'] +  ':</div>' +
				'<div id="step2_text3">'  + t_js.device_common_MSG['0025'] +  '</div>' +
				'<div id="step2_text4">'  + t_js.device_common_MSG['0026'] +  '</div>' +
				'</div>' +
				'<div class="btn_sharef" id="ps_step_btn" onClick="PreRegStep2();" style="position:absolute;right:30px;bottom:20px;">'+
				t_js.device_common_MSG['0008'] + '</div>'+
			'</div>',
	advanced: '<div class="reg_frame"><div class="reg_titletab"><div class="close_b" onClick="ShadowBoxClose();"></div></div>'+
				'<div class="step2_autobind_browser" id="ps_step_frame" style="margin-bottom:10px;">' +
				'<div class="ps_step_title">' + t_js.device_common_MSG['0024'] + '</div>' +
				'<div id="step2_text1">'  + t_js.device_common_MSG['0010'] +  ':</div>' +
				'<div id="step2_text2">'  + t_js.device_common_MSG['0011'] +  ':</div>' +
				'<div id="step2_text3">'  + t_js.device_common_MSG['0025'] +  '</div>' +
				'<div id="step2_text4">'  + t_js.device_common_MSG['0026'] +  '</div>' +
				'</div>' +
				'<div class="btn_sharef" id="ps_step_btn" onClick="PreRegStep2();" style="position:absolute;right:30px;bottom:20px;">'+
				t_js.device_common_MSG['0008'] + '</div>'+
			'</div>',
	search: '<div class="reg_titletab"><div class="close_b" onClick="ShadowBoxClose();"></div></div>'+
				'<div class="ps_step_title autosearch_title" id="ps_step_frame">' + t_js.device_common_MSG['0027'] + '</div>' +
				'<div class="autosearch_frame disabled" id="autosearch_frame">' +
				'<div class="autosearch_loading"></div>' +
				'<table id="autosearch_table" class="layouttable_2"><thead>' +
				'<tr><th class="title" colspan="2">' + t_js.device_common_MSG['0020'] + '</th></tr>' +
				'<tr><th>' + t_js.device_common_MSG['0010'] + '</th><th>' + t_js.device_common_MSG['0021'] + '</th></tr></thead><tbody id="autosearch_tbody">' +
				'<tr class="even"><td></td><td></td></tr><tr class="odd"><td></td><td></td></tr><tr class="even"><td></td><td></td></tr><tr class="odd"><td></td><td></td></tr><tr class="even"><td></td><td></td></tr><tr class="odd"><td></td><td></td></tr>' +
				'</tbody></table></div>' +
				'<div class="btn_sharef disabled" id="autosearch_step_ok" style="position:absolute;right:120px;bottom:20px;">'+
				t_js.device_common_MSG['0018'] + '</div>' +
				'<div class="btn_sharef disabled" id="autosearch_step_advanced" style="position:absolute;right:30px;bottom:20px;">'+
				t_js.device_common_MSG['0019'] + '</div>'
}

function ActiveShadowBoxInit(){
	ShadowboxPage = 1;
	Shadowbox.init({
		skipSetup: true,
		enableKeys: false,
		overlayOpacity:0.7,
		onOpen:function(){
			$('#sb-nav-close').addClass('displaynone');
		},
		onClose:function(){
			switch(ShadowboxPage){
				case 2:
					//need wait a while, or open will failed.
					setTimeout("ShadowBoxOpen(400,800);",500);
					break;
				case 3:
				case 5:
					window.location.href = window.location.href.replace("checkdevice=true","checkdevice=");
					break;
				case 6: // Info system
					try {InfoSystemClose();} catch (e){}
					break;
				default:
				break;
			}
		}
	});

}

function cameraFinder() {
	try {
		if (!cameraFinderVar.applet) {
			var applets = $("#CameraFinder");
			if (applets.length)
				cameraFinderVar.applet = applets[0];
		}

		if (!cameraFinderVar.elapsed) {
			try {
				cameraFinderVar.applet.find(5000);
			}
			catch (e) {
				return;
			}
			cameraFinderVar.elapsed = 1;
			return;
		}

		cameraFinderVar.elapsed ++;
		if (cameraFinderVar.elapsed>30) {
			throw "Auto search timeout";
		}

		if (cameraFinderVar.applet.getIsRunning() == false) {
			window.clearInterval(cameraFinderVar.interval);
			cameraFinderVar.applet.getResult();
			var r = $.parseJSON(cameraFinderVar.applet.result);
			result = [];
			for (var i=0; i<r.length && result.length<6; i++) {
				if (r[i].uid == "" && r[i].cid == autobinding_cid) {
					result.push(r[i]);
				}
			}

			$("#autosearch_table tbody tr").each(function(i, e) {
				if (i < result.length) {
					var id = result[i].cid + result[i].pid + "-" + result[i].sid;
					$(e).attr("id", id)
					if (!result[i].service_type)
						result[i].service_type = "camera";
					$(e).html("<td>" + result[i].sid + "</td><td>" + service_type_list[result[i].service_type] + "</td>");
				}
				else {
					$(e).attr("id", "");
				}
			});
			$("#autosearch_frame").removeClass("disabled");
			$("#autosearch_step_ok").removeClass("disabled").click(function() {autoBind(result);});
			$("#autosearch_step_advanced").removeClass("disabled").click(function(){stepChangePage('advanced');});
		}
	}
	catch (e) {
		window.clearInterval(cameraFinderVar.interval);
		$("#autosearch_frame").removeClass("disabled");
	}
}

function autoBindDevice() {
	for (; cameraFinderVar.index<cameraFinderVar.devices.length; cameraFinderVar.index++) {
		var param = cameraFinderVar.devices[cameraFinderVar.index];
		if (param.success != true) {
			param.command = "autobind";
			$.getJSON("backstage_device.php", param, autoBindCallback);
			return;
		}
	}
	$("#autosearch_frame").removeClass("disabled");
}

function autoBindCallback(data) {
	var param = cameraFinderVar.devices[cameraFinderVar.index];
	var id = param.cid + param.pid + "-" + param.sid;
	if (data.status == "success") {
		$("#"+id).removeClass("fail").addClass("success");
		param.success = true;
	}
	else {
		$("#"+id).removeClass("success").addClass("fail");
		cameraFinderVar.failed_count++;
	}
	cameraFinderVar.index++;
	autoBindDevice();
}

function autoBind(result) {
	ShadowboxPage = 5;
	$("#autosearch_frame").addClass("disabled");
	cameraFinderVar.devices = result;
	cameraFinderVar.index = 0;
	cameraFinderVar.failed_count = 0;
	autoBindDevice();
}

function stepChangePage(page){
	if (page){
		$(".reg_frame").html(shadowbox_messages[page]);
	}
	else if (autobinding_cid) {
		$(".reg_frame").html(shadowbox_messages.search);
		cameraFinderVar = {};
		cameraFinderVar.interval = window.setInterval(cameraFinder, 1000);
	}
	else {
		$(".reg_frame").html(shadowbox_messages.bind);
	}
}

function ShadowBoxOpen(h,w){
	var t_content = "";
	switch(ShadowboxPage){
		case 1:
			t_content = '<div class="reg_frame">' + shadowbox_messages.connect + '</div>';
			break;
		case 2:
			t_content = '<div class="reg_frame"><div class="reg_titletab"><div class="close_b" onClick="ShadowBoxClose();"></div></div><table class="layouttable_2">'+
				'<tr><th style="width:30px;">' + t_js.device_common_MSG['0009'] + '</th><th>'+
				t_js.device_common_MSG['0010'] + '</th>'
				if (!autobinding_cid)
					t_content += '<th>'+ t_js.device_common_MSG['0011'] + '</th>';
				t_content += '<th>' + t_js.device_common_MSG['0012'] + '</th></tr>';
				for(var i=1;i<8;i++){
					var colorclass = "odd";
					if (i%2==1) colorclass = "even";
					t_content += '<tr id="reg_tr_'+i+'" class="'+colorclass+'"><td>'+i+'</td><td><input type="text" style="width:150px;"/><div class="reg_error"></div></td>'
					if (!autobinding_cid)
						t_content += '<td><input type="text" style="width:150px;"/><div class="reg_error"></div></td>';
					t_content += '<td><div class="btn_sharef floatleft" onClick="ActiveCodeApply('+i+');">'+
					t_js.device_common_MSG['0012'] +'</div><div class="reg_result"></div></td></tr>';
				}

			t_content += '</table></div>';
			ShadowboxPage = 3;
			break;
		case 4:
			t_content = '<div class="reg_frame"><div class="reg_titletab"><div class="close_b" onClick="ShadowBoxClose();"></div></div>'+
				'<div class="reg_text" style="margin-top: -20px;margin-bottom: 20px;">' + t_js.device_common_MSG['0013'] + '</div>' +
				'<div class="reg_key_frame">'+
					'<div class="btn_sharef floatleft" onClick="GoogleAuthPage();ShadowBoxClose();">' + t_js.timeline_MSG['0007'] + '</div>'+
					'<div class="btn_sharef floatleft" onClick="ShadowBoxClose();">' + t_js.timeline_MSG['0006'] + '</div>'+
				'</div>'+
			'</div>';
			break;
	}
	Shadowbox.open({
		content:    t_content,
		player:     "html",
		title:      "",
		height:     h,
		width:      w
	});
}

function ShadowBoxClose(){
	if (ShadowboxPage == 3 || ShadowboxPage == 5);
	else
		ShadowboxPage = 1;
	Shadowbox.close();
}

function PreRegStep2(){
	ShadowboxPage = 2;
	Shadowbox.close();
}

function ActiveCodeApply(i){
	$("#reg_tr_" + i).find('input').eq(0).attr('disabled',true);
	$("#reg_tr_" + i).find('input').eq(1).attr('disabled',true);
	var input_mac_addr = $("#reg_tr_" + i).find('input').eq(0).val();
	var input_active_code = $("#reg_tr_" + i).find('input').eq(1).val();
	$("#reg_tr_" + i).find('.reg_error').eq(0).html('');
	$("#reg_tr_" + i).find('.reg_error').eq(1).html('');
	if (!checkMacAddressContent(input_mac_addr,null)){
		$("#reg_tr_" + i).find('.reg_error').eq(0).html(t_js.device_common_MSG['0014']);
		$("#reg_tr_" + i).find('input').eq(0).attr('disabled',false);
		$("#reg_tr_" + i).find('input').eq(1).attr('disabled',false);
		return false;
	}
	if (!autobinding_cid && input_active_code == ""){
		$("#reg_tr_" + i).find('.reg_error').eq(1).html(t_js.device_common_MSG['0015']);
		$("#reg_tr_" + i).find('input').eq(0).attr('disabled',false);
		$("#reg_tr_" + i).find('input').eq(1).attr('disabled',false);
		return false;
	}
	input_mac_addr = input_mac_addr.replace(/[:\-]/g, "").toUpperCase();
	var param = {
		request_device_type: $.trim(request_device_type),
		request_service_type: $.trim(request_service_type)
	};
	if (autobinding_cid) {
		param.command = "autobind";
		param.cid = autobinding_cid;
		param.pid = "CC";
		param.sid = $.trim(input_mac_addr);
	}
	else {
		param.command = "active_device";
		param.mac_addr = $.trim(input_mac_addr);
		param.active_code = $.trim(input_active_code);
	}

	$.getJSON(
		"backstage_device.php", param,
		function( data )
		{
			if (data["status"] == "success"){
				$("#reg_tr_" + i).find('.reg_error').eq(0).html('');
				$("#reg_tr_" + i).find('.reg_error').eq(1).html('');
				$("#reg_tr_" + i).find('.btn_sharef').eq(0).css('display','none');
				$("#reg_tr_" + i).find('.reg_result').eq(0).css('display','block').html(t_js.device_common_MSG['0016']);
				//setTimeout("RecoverActiveButton("+i+");",3000);
			}
			else{
				switch(data["error_msg"]){
					case "Wrong MAC Address":
						$("#reg_tr_" + i).find('.reg_error').eq(0).html(t_js.device_common_MSG['0014']);
					break;
					case "Wrong Active Code":
						$("#reg_tr_" + i).find('.reg_error').eq(1).html(t_js.device_common_MSG['0015']);
					break;
					case "Update series_number failed":
					case "Insert device failed.":
						//Extra Error
						$("#reg_tr_" + i).find('.reg_error').eq(0).html(t_js.device_common_MSG['0017']);
					break;
					default:
						$("#reg_tr_" + i).find('.reg_error').eq(0).html(data["error_msg"]);
					break;
				}
				$("#reg_tr_" + i).find('input').eq(0).attr('disabled',false);
				$("#reg_tr_" + i).find('input').eq(1).attr('disabled',false);
			}
		}
	);
}

function RecoverActiveButton(i){
	$("#reg_tr_" + i).find('.reg_result').eq(0).fadeOut('slow', function() {
		// Animation complete.
		$("#reg_tr_" + i).find('.btn_sharef').eq(0).css('display','block');
		$("#reg_tr_" + i).find('.reg_result').eq(0).css('display','none').html('');
		$("#reg_tr_" + i).find('.reg_result').eq(0).fadeIn(1);
		$("#reg_tr_" + i).find('input').eq(0).val('').attr('disabled',false);
		$("#reg_tr_" + i).find('input').eq(1).val('').attr('disabled',false);
	});

}

function toggle_checkbox(obj) {
	$(obj).toggleClass("_checkbox-enable _checkbox-disable");
}
