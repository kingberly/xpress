// device_list info
var device_list = new Array();
var used_list = new Array();
var currentpage = 1;
var device_total_count = -1;
var total_page = -1;

// redisplay 
function ReDisplay( data )
{	
	// check return data empty
	if( data != null )
	{
		//update Myfavorite Sequence
		myfavorite_seq = Array();
		if (data.myfavorite_seq)
			for(var i in data.myfavorite_seq)
				myfavorite_seq.push(data.myfavorite_seq[i]['device_id']);
		
		// show error msg when fail
		if( data.status != "success" )
		{
			Alert( data.error_msg );
			return;
		}

		// only close config dialog when success
		CloseDialog(); 
		
		// set add user button		
		if (request_service_type == "camera")
			$("div#add-btn").show();
		if (request_service_type == "remotedesktop" || request_service_type == "syncme")
			$("div#add-btn-old").show();
			
		// update data
		
		
		device_list = data.device_list;
		device_total_count = data.device_total_count;
		
		// display device list
		DisplayDevice( "device_list_table" );
		
		RefreshButtonDisplay(false);
	}
}

function ShowDefaultDevice( default_device_id, default_tab )
{
	for( var i=0; i<device_list.length; i++ )
	{
		if( device_list[i].id == default_device_id )
		{
			var GetArray = PrepareDeviceGetParameters(device_list[i]);
			var URL = 'device.php?' + GetArray + "&ToTab=" + default_tab;			
			CallParentIframe(URL);			
			break;
		}
	}
}

// use getJason to get data & call redisplay
function RefreshDisplay( default_device_id, default_tab )
{
	var limit = $('#select_limit').val();	
	var current_page = $('#current_page').val();
	if (current_page<=0) return false;
	currentpage = current_page;
	
	var offset = (currentpage-1) * limit;
	$.getJSON(
		"backstage_device.php",
		{
			command: "list",
			limit: limit,
			offset: offset,
			mode: mode,
			order_by: order_by,
			asc_order: asc_order,
			list_mode: true,
			showseq: true,
			request_device_type:request_device_type,
			request_service_type:request_service_type
		},
		function( data )
		{
			ReDisplay( data );
			// display the default device
			ShowDefaultDevice( default_device_id, default_tab );
		}
	);	
}

function CloseDialog()
{
	pane_close("#device_list", "#device_config_dlg");
}

function ModifyDevice( id )
{	
	var counter=0;
	$('#device_config_table').find('tr').each(function(){ //remix color
		//if(	counter==0) return true;
		//alert(counter);
		if (counter%2==1) 
			$(this).removeClass('even').addClass('odd');
		else
			$(this).removeClass('odd').addClass('even');		
		counter++;	
	});	
	
	
	$('#owner_id-edit').val(user_id);
	
	if (id == -1 ){ //add mode
		$('#mac_addr-edit').attr('disabled', false);
	}else
		$('#mac_addr-edit').attr('true', false);
	
	
	// set config dlg content
	SetDeviceConfigDlgContent( device_list[id] );	

	// open dialog
	_pane_open('#device_list', '#device_config_dlg');
}

function ApplyModifyDevice()
{
	// get parameter array

	var parameter_array = PrepareDeviceModifyParameterArray();
	//zzz= parameter_array;
	if( parameter_array == null ) return;
	
	//new add
	parameter_array.command="new";
	parameter_array.mode = mode;
	
	var limit = $('#select_limit').val();
	parameter_array.limit = limit;
	parameter_array.offset = 0;
	parameter_array.order_by = order_by;
	parameter_array.asc_order= asc_order;
	parameter_array.list_mode= true;
	parameter_array.showseq= true;
	parameter_array.request_device_type = request_device_type;
	parameter_array.request_service_type = request_service_type;
	
	// black screen
	applyBegin();

	// get parameter ok, start to send data
	$.getJSON(
		"backstage_device.php",
		parameter_array,
		function( data )
		{
			if( data.status == "success" )
			{
				RefreshButtonDisplay(false);
				RefreshDisplay();
				/*currentpage = 1;
				ReDisplay( data );
				try{parent.RefreshMenuDisplay(basename(window.location.href));} catch (e) {}*/
			}
			else Alert( data.error_msg );
			
			// remove black screen
			applyEnd();
		}
	);
}

function LocateDevice( id )
{
	var locate_url = GetLocateURL( device_list[id] );
	window.open( locate_url, "myWindow", "status = 1, height = 300, width = 400, resizable = 0" );
}

function RefreshAfterDelete( data )
{
	if ( device_list.length == 1 && currentpage > 1) currentpage --;
	RefreshButtonDisplay(false);
	RefreshDisplay();
	//ReDisplay( data );
	//try{parent.RefreshMenuDisplay(basename(window.location.href));} catch (e) {}
}



function DisplayDevice( table_id )
{
	var table = document.getElementById(table_id);
	while ( table.tBodies[0].childNodes.length > 1 ) {
		table.tBodies[0].removeChild( table.tBodies[0].firstChild.nextSibling );
	}

	// check if user list is null
	if( device_list == null ) return;
	used_list = new Array();
	// loop to display device list
	for( var i=0; i<device_list.length; i++ )
	{
		//p2p camera , exclude http ,2012/07/23
		if (request_service_type == "camera" && !(device_list[i].purpose == "RVLO" || device_list[i].purpose == "WBMJ" || device_list[i].url_prefix == "rtsp://" ) && device_list[i].model_id != 45 && device_list[i].service_type != "nvr") continue;
		if (used_list.hasOwnProperty(device_list[i].uid)) continue;
		used_list[device_list[i].uid] = true;
		var tr = document.createElement('tr');
		tr.className = i%2==0?'even':'odd';
		tr.setAttribute('id',device_list[i].id);
		
                	
		// id
		/*var td = document.createElement('td');
		td.innerHTML = device_list[i].id;
		
		tr.appendChild(td);*/
		
		// name
		var td = document.createElement('td');
		td.innerHTML = device_list[i].name;
		tr.appendChild(td);

		// owner
		var td = document.createElement('td');
		td.innerHTML = device_list[i].owner_name;
		tr.appendChild(td);

		// mac_addr
		var td = document.createElement('td');
		td.innerHTML = device_list[i].mac_addr;
		tr.appendChild(td);

		// identifier
		/*var td = document.createElement('td');
		td.innerHTML = device_list[i].identifier;
		tr.appendChild(td);*/

		// service type
		var td = document.createElement('td');
		var innerText = service_type_list[device_list[i].service_type];	
		if (innerText == undefined){
			switch(device_list[i].service_type){
				case "WEB": //v1.0 syncme
				if (device_list[i].device_type == "syncme")
					innerText = "SyncMe";
				break;
				case "REMOTE": //v1.0 syncme
				if (device_list[i].device_type == "remotedesktop")
					innerText = "Remote";
				break;
				default:
					innerText = "Unknown";				
			}
		}
		td.innerHTML = innerText;
		tr.appendChild(td);
		
		// device type
		/*var td = document.createElement('td');
		var innerText = device_type_list[device_list[i].device_type];		
		td.innerHTML = innerText;
		tr.appendChild(td);*/

		// ip_addr
		//p2p camera , exclude ip ,2012/07/23
		if (request_service_type != "camera"){
			var td = document.createElement('td');
			var innerText_ip_addr = device_list[i].url_prefix + device_list[i].ip_addr + device_list[i].url_path;
			if(device_list[i].device_type == "p2p")
				innerText_ip_addr = "N/A";
			td.innerHTML = innerText_ip_addr;
			tr.appendChild(td);
		}

		// port
		var td = document.createElement('td');
		td.innerHTML = device_list[i].port;
		tr.appendChild(td);
		
		//i am not admin, not my device
		var notadmin = "";
		//p2p & camera & prefix = http, cannot add to matrix
		if( mode == 'public' || ( js_common_user_group_id != "0" && device_list[i].owner_id != js_common_user_id ))
			notadmin = "notadmin";
		
		// query_geo_locate
		//p2p camera , exclude autolocate ,2012/07/23
		if (request_service_type != "camera"){
			var td = document.createElement('td');
			td.align = "center";
			if( device_list[i].query_geo_locate == 1 )
				td.innerHTML = "<div class='cursorpointer btn_auto_locate _checkbox-enable "+ notadmin +"'>"
			else td.innerHTML = "<div class='cursorpointer btn_auto_locate _checkbox-disable "+ notadmin +"'>"
			tr.appendChild(td);
		}
		
		// device_matrix
		var td = document.createElement('td');
		td.align = "center";
		if( device_list[i].seq == -1 ) td.innerHTML = "<div class='cursorpointer btn_device_matrix _checkbox-enable'>";
		else td.innerHTML = "<div class='cursorpointer btn_device_matrix _checkbox-disable'>";
		tr.appendChild(td);
		                                                                            
		// action
		var td = document.createElement('td');
		var action_html = "<div class='_icon_cover' id='"+ i +"'><div class='_s_button _s_view' title='"+t_js.device_list_MSG['0001']+"' style='display:none;' /></div>";
		action_html += "<div class='_s_button _s_locate' style='display:none;' title='"+t_js.device_list_MSG['0002']+"' /></div>";
		
		var disabled ="";
		var download_disabled = "";
		var delete_disabled = ""; //jinho added delete_disabled;
		var delete_event = "onClick='DelDevice(" + device_list[i].id + ", RefreshAfterDelete)'";
		// i'm not admin and it's not my device (disable delete for non-admin users)		
		if(mode == "public" || (js_common_user_group_id != "0" && device_list[i].owner_id != js_common_user_id )) {
			disabled ="disabled";
			delete_event ="";
			delete_disabled = "disabled"; //jinho
		}
		if (mode == "rpic"){ //jinho added
			delete_event ="";
			delete_disabled = "disabled";
      //download_disabled = "disabled"; //C13 disable download
		}
		if (device_list[i].dataplan != "SR" && device_list[i].dataplan != "AR") {
			download_disabled = "disabled";
		}
		//jinho update disabled to delete_disabled	
		action_html += "<div class='_s_button _s_modify "+disabled+"' title='"+t_js.device_list_MSG['0003']+"' /></div>" +
			"<div class='_s_button _s_delete "+delete_disabled+"' "+ delete_event +" title='"+t_js.device_list_MSG['0004']+"' /></div>" + 
			"<div class='_s_button _s_share "+disabled+"' title='"+t_js.device_list_MSG['0005']+"' /></div>" +
			"<div class='_s_button _s_download "+download_disabled+"' title='"+t_js.device_list_MSG['0006']+"' /></div>" +
			"</div>";
		td.innerHTML = action_html;
		tr.appendChild(td);

		table.tBodies[0].appendChild(tr);
	}	
	
	tableButton();
	
	$('#'+table_id).find('td').css('white-space','nowrap').css('overflow','hidden');
	$('#'+table_id).find('tr').each(function(){
		//$(this).css('width','960px');
		ReduceContent($(this),4,22);
		ReduceContent($(this),0,25);
		ReduceContent($(this),1,12);
	});
	
	/*$('#'+table_id).find('th').css('width','100px');
	$('#'+table_id).find('td').css('width','100px');*/
	
}

function ReduceContent(JQ,td_No,length){
	var html = JQ.find('td').eq(td_No).html();
	if (html != null && html.length > length){	
		JQ.find('td').eq(td_No).attr('title',html);
		html = html.substr(0,length-3) + "...";
		JQ.find('td').eq(td_No).html(html);	
	}
}

//problem about syncme directory level, fix by this function 
function url_prefix_forsyncme(device){
	if (device == null) return "";	
	if (device['service_type'] == "syncme" || (device['device_type'] == "syncme" && device['service_type'] == "WEB"))
		return "../";
	else
		return "";
}

function tableButton(){	
	$('._s_view').click(function(){
		var id = $(this).parent().attr('id');
		var GetArray = PrepareDeviceGetParameters(device_list[id]);
		var url_prefix = url_prefix_forsyncme(device_list[id]);		
		var URL = url_prefix + 'device.php?' + GetArray + "&ToTab=0";
		//CallParentIframe(URL);
		if (device_list[id].device_type != "p2p")
			CallParentIframe(URL);
		else {
			OpenInvisIframe(URL);
		}
	});

	$('._s_locate').click(function(){
		var id = $(this).parent().attr('id');
		var GetArray = PrepareDeviceGetParameters(device_list[id]);
		var url_prefix = url_prefix_forsyncme(device_list[id]);		
		var URL = url_prefix + 'device.php?' + GetArray + "&ToTab=1";	
		CallParentIframe(URL);
	});
	
	if (mode=="public")  //below button, only work when user login
	{
		InitializeFavoriteButton();
		return false;
	}
		
	
	$('._s_modify').click(function(){
		if ($(this).hasClass('disabled'))
			return;
		var id = $(this).parent().attr('id');
		var GetArray = PrepareDeviceGetParameters(device_list[id]);
		var url_prefix = url_prefix_forsyncme(device_list[id]);
		var URL = url_prefix + 'device.php?' + GetArray + "&ToTab=2";	
		CallParentIframe(URL);
		//location.replace('device.php?' + GetArray + "&ToTab=2");
		/*$('#apply_overlay iframe').attr('src','device.php?' + GetArray + "&ToTab=2");
		$('#apply_overlay').overlay().load();*/
	});

	$('._s_share').click(function(){
		if ($(this).hasClass('disabled'))
			return;
		var id = $(this).parent().attr('id');
		var GetArray = "uid=" + device_list[id].uid + "&name=" + device_list[id].name;
		var URL = 'share_device.php?'+GetArray;	
		CallParentIframe(URL);
	});

	$('._s_download').click(function(){
		if ($(this).hasClass('disabled'))
			return;
		var id = $(this).parent().attr('id');
		var GetArray = "uid=" + device_list[id].uid + "&name=" + device_list[id].name;
		var URL = 'download_recording.php?'+GetArray;	
		CallParentIframe(URL);
	});
	
	$('.btn_public_status').click(function(){
		if ($(this).hasClass('notadmin'))	 return;
		var JQThis = $(this);
		var id = $(this).parent().parent().attr('id');
		if (id == undefined) return false;
		var isEnable = 1;
		if ($(this).hasClass('_checkbox-enable')) isEnable = 0;
		$.getJSON(
			"backstage_device.php",
			{
				command: "update_public_status",
				device_id: id,
				is_enable: isEnable,
				request_device_type: request_device_type,
				request_service_type: request_service_type
			},
			function( data )
			{
				if (isEnable == 1)
					JQThis.addClass('_checkbox-enable').removeClass('_checkbox-disable');
				else
					JQThis.removeClass('_checkbox-enable').addClass('_checkbox-disable');				
			}
		);
	});

	$('.btn_device_matrix').click(function(){
		if ($(this).hasClass('notadmin'))	 return;
		var JQThis = $(this);
		var id = $(this).parent().parent().attr('id');
		if (id == undefined) return false;
		var isEnable = true;
		if ($(this).hasClass('_checkbox-enable')) isEnable = false;
		$.getJSON(
			"backstage_device.php",
			{
				command: "addremoveseq",
				device_id: id,
				isadd: isEnable,
				isfavorite: false,
				request_device_type: request_device_type,
				request_service_type: request_service_type
			},
			function( data )
			{
				if (isEnable == true)
					JQThis.addClass('_checkbox-enable').removeClass('_checkbox-disable');
				else
					JQThis.removeClass('_checkbox-enable').addClass('_checkbox-disable');				
			}
		);
	});

	InitializeFavoriteButton();

	$('.btn_auto_locate').click(function(){
		if ($(this).hasClass('notadmin'))	 return;
		var JQThis = $(this);
		var id = $(this).parent().parent().attr('id');
		if (id == undefined) return false;
		var isEnable = 1;
		if ($(this).hasClass('_checkbox-enable')) isEnable = 0;
		$.getJSON(
			"backstage_device.php",
			{
				command: "update_auto_locate",
				device_id: id,
				is_enable: isEnable,
				request_device_type: request_device_type,
				request_service_type: request_service_type
			},
			function( data )
			{
				if (isEnable == 1)
					JQThis.addClass('_checkbox-enable').removeClass('_checkbox-disable');
				else
					JQThis.removeClass('_checkbox-enable').addClass('_checkbox-disable');				
			}
		);
	});	
	
	//bind Service Type Check
	$('#service_type-edit').unbind().change(function(){
		CheckSelectServiceType();
	});
}

function InitializeFavoriteButton(){
	$('.btn_myfavorite_matrix').click(function(){
		if ($(this).hasClass('notadmin'))	 return;
		var JQThis = $(this);
		var id = $(this).parent().parent().attr('id');
		if (id == undefined) return false;
		var isEnable = true;
		if ($(this).hasClass('_checkbox-enable')) isEnable = false;
		$.getJSON(
			"backstage_device.php",
			{
				command: "addremoveseq",
				device_id: id,
				isadd: isEnable,
				isfavorite: true,
				request_device_type: request_device_type,
				request_service_type: request_service_type
			},
			function( data )
			{
				if (isEnable == true)
					JQThis.addClass('_checkbox-enable').removeClass('_checkbox-disable');
				else
					JQThis.removeClass('_checkbox-enable').addClass('_checkbox-disable');				
			}
		);
	});
}

//IndexOf , IE doesnt support "IndexOf" , so write a function
function INDEXOF(array,s){
	var counter = 0;
	for(var i in array){		
		if(array[i] == s)
			return counter;
		counter++;	
	}
	return -1;
}
