// global vars
var g_page_control_select_name = "";
var g_page_control_first_btn_name = "";
var g_page_control_prev_btn_name = "";
var g_page_control_next_btn_name = "";
var g_page_control_last_btn_name = "";
var g_page_control_callback = null;
var g_page_control_total_page_no = 0;
var g_page_control_current_page_no = 0;
var g_page_control_auto_cycle_interval = 0;

function InitializePageControl(select_name, first_btn_name, prev_btn_name, next_btn_name, last_btn_name,
	page_controll_callback)
{
	// remember all our control names
	g_page_control_select_name = select_name;
	g_page_control_first_btn_name = first_btn_name;
	g_page_control_prev_btn_name = prev_btn_name;
	g_page_control_next_btn_name = next_btn_name;
	g_page_control_last_btn_name = last_btn_name;
	
	// page control callback
	g_page_control_callback = page_controll_callback;
	
	// handle mouse over event
	$(first_btn_name).unbind("mouseenter");//hover");
	$(first_btn_name).mouseenter(//$(first_btn_name).hover(
		function() { if( g_page_control_current_page_no > 0 ) $(this).addClass('ui-state-highlight'); }, 
		function() { $(this).removeClass('ui-state-highlight'); }
	);
	$(prev_btn_name).unbind("mouseenter");//hover");
	$(prev_btn_name).mouseenter(//.hover( //jinho fix
		function() { if( g_page_control_current_page_no > 0 ) $(this).addClass('ui-state-highlight'); }, 
		function() { $(this).removeClass('ui-state-highlight'); }
	);
	$(next_btn_name).unbind("mouseenter");//hover");
	$(next_btn_name).mouseenter(//.hover( //jinho fix
		function() { if( g_page_control_current_page_no < g_page_control_total_page_no - 1 ) $(this).addClass('ui-state-highlight'); }, 
		function() { $(this).removeClass('ui-state-highlight'); }
	);
	$(last_btn_name).unbind("mouseenter");//hover");
	$(last_btn_name).mouseenter(//.hover( //jinho fix
		function() { if( g_page_control_current_page_no < g_page_control_total_page_no - 1 ) $(this).addClass('ui-state-highlight'); }, 
		function() { $(this).removeClass('ui-state-highlight'); }
	);
	
	// register button click event
	$(first_btn_name).click( function() {
		if( g_page_control_current_page_no > 0 ){
			$(g_page_control_select_name).val(0).change();
			$('#_page_now').val(1);
		}
	});
	$(prev_btn_name).click( function() {
		if( g_page_control_current_page_no > 0 ){
			$(select_name).val(g_page_control_current_page_no - 1).change();
			$('#_page_now').val(g_page_control_current_page_no+1);
		}
	});
	$(next_btn_name).click( function() {
		if( g_page_control_current_page_no < g_page_control_total_page_no - 1 ){
			$(g_page_control_select_name).val(g_page_control_current_page_no + 1).change();
			$('#_page_now').val(g_page_control_current_page_no+1);
		}
	});
	$(last_btn_name).click( function() {
		if( g_page_control_current_page_no < g_page_control_total_page_no - 1 ){
			$(g_page_control_select_name).val(g_page_control_total_page_no - 1).change();
			$('#_page_now').val(g_page_control_current_page_no+1);
		}
	});
	
	$('#_page_now').bind("keypress", function (e) {
		var input_page = $('#_page_now').val();
		if( input_page > 0 && input_page < g_page_control_total_page_no + 1  ){
			//$(g_page_control_select_name).val(0).change();
			//$('#_page_now').val(input_page);
			if (e.which == 13)
				$(g_page_control_select_name).val(input_page-1).change();			 
		}
		
	 });

	$('#cb_auto_cycle').click(function() {
		if ($(this).hasClass('disabled')) {
			return;
		}
		
		if ($(this).hasClass('_checkbox-disable')) {
			$(this).addClass('_checkbox-enable')
					.removeClass('_checkbox-disable');
			StartAutoCycle();
		}
		else {
			$(this).addClass('_checkbox-disable')
					.removeClass('_checkbox-enable');
			StopAutoCycle();
		}
	});
	
	// register select change event
	$(g_page_control_select_name).change( function() {												   
		g_page_control_current_page_no = parseInt($(this).val());
		//alert(g_page_control_current_page_no);
		UpdateButtonDisableStatus();
		g_page_control_callback( g_page_control_current_page_no );
	});
}

function doNextPage(){
	$(g_page_control_next_btn_name).click();
}
function doPreviousPage(){
	$(g_page_control_prev_btn_name).click();
}

function UpdateTotalPageNumber( total_item_no, item_no_per_page )
{
	//alert(total_item_no+" "+item_no_per_page+" "+max_nail);
	var total_page_no = parseInt((total_item_no+item_no_per_page-1)/item_no_per_page)
	
	if( total_page_no != g_page_control_total_page_no )
	{
		// store the new total page no
		if (total_page_no < max_nail) total_page_no = max_nail;
		g_page_control_total_page_no = total_page_no; 
		
		// set page select options
		var html = "";
		for( var i=0; i<g_page_control_total_page_no; i++ ) html += "<option value='" + i + "'>" + (i+1) + "</option>";
		$(g_page_control_select_name).html(html);
		
		// check if current page no exceed total
		if( g_page_control_current_page_no >= g_page_control_total_page_no ){
			$(this.select_name).val(g_page_control_current_page_no).change();			
		}
		else
		{
			$(this.select_name).val(g_page_control_current_page_no);
			UpdateButtonDisableStatus()
		}		
		$('#_page_total').html(g_page_control_total_page_no);
		$('#_page_now').val(g_page_control_current_page_no+1);
	}
}

// update button disable status
function UpdateButtonDisableStatus()
{
	if( g_page_control_current_page_no <= 0 )
	{
		if( !$(g_page_control_first_btn_name).hasClass("disabled") ) $(g_page_control_first_btn_name).addClass("disabled");
		if( !$(g_page_control_prev_btn_name).hasClass("disabled") ) $(g_page_control_prev_btn_name).addClass("disabled");
	}
	else
	{
		if( $(g_page_control_first_btn_name).hasClass("disabled") ) $(g_page_control_first_btn_name).removeClass("disabled");
		if( $(g_page_control_prev_btn_name).hasClass("disabled") ) $(g_page_control_prev_btn_name).removeClass("disabled");
	}
	
	if( g_page_control_current_page_no >= g_page_control_total_page_no - 1 )
	{
		if( !$(g_page_control_next_btn_name).hasClass("disabled") ) $(g_page_control_next_btn_name).addClass("disabled");
		if( !$(g_page_control_last_btn_name).hasClass("disabled") ) $(g_page_control_last_btn_name).addClass("disabled");
	}
	else
	{
		if( $(g_page_control_next_btn_name).hasClass("disabled") ) $(g_page_control_next_btn_name).removeClass("disabled");
		if( $(g_page_control_last_btn_name).hasClass("disabled") ) $(g_page_control_last_btn_name).removeClass("disabled");
	}
	
	if (g_page_control_total_page_no>0) {
		$('#auto_cycle').removeClass('disabled');
		$('#cb_auto_cycle').removeClass('disabled');
	}
	else {
		$('#auto_cycle').addClass('disabled');
		$('#cb_auto_cycle').addClass('disabled');
	}
}

function AutoCycleNext() {
	if( g_page_control_current_page_no < g_page_control_total_page_no - 1 ){
		$(g_page_control_select_name).val(g_page_control_current_page_no + 1).change();
		$('#_page_now').val(g_page_control_current_page_no+1);
	}
	else {
		$(g_page_control_select_name).val(0).change();
		$('#_page_now').val(1);
	}
}

function StartAutoCycle() {
	if (g_page_control_auto_cycle_interval == 0) {
		g_page_control_auto_cycle_interval = setInterval(AutoCycleNext, 10000);
	}
}

function StopAutoCycle() {
	if (g_page_control_auto_cycle_interval != 0) {
		clearInterval(g_page_control_auto_cycle_interval);
	}
	g_page_control_auto_cycle_interval = 0;
}
