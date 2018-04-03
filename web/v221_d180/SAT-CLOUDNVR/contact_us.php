<?php
$valid_os = array(
        'windows',
        'linux',
        'mac',
        'iphone_ipod',
        'android',
        );
$mobile_os = array(
        'iphone_ipod',
        'android',
        );

$os = preg_replace('/[\/\s]/', '_', strtolower($_GET['os']));
if (array_search($os, $valid_os) === False) {
    $os = '';
}
$is_mobile = ( array_search($os, $mobile_os) !== False );

?>
<!DOCTYPE HTML>
<!--[if lt IE 7 ]> <html class="ie6"> <![endif]-->
<!--[if IE 7 ]>    <html class="ie7"> <![endif]-->
<!--[if IE 8 ]>    <html class="ie8"> <![endif]-->
<!--[if IE 9 ]>    <html class="ie9"> <![endif]-->
<!--[if (gt IE 9)|!(IE)]><!--> <html class=""> <!--<![endif]-->
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Contact Us</title>
        <?php require( "./include/common_css_include.php" ); ?>
        <?php require( "./include/common_js_include.php" ); ?>
<?php
    if ($os) {
        echo "<link rel='stylesheet' type='text/css' href='css/contact_$os.css'>\n";

    }
    if ($is_mobile) {
        echo "<meta name='viewport' content='width=device-width, user-scalable=no' />\n";
    }

?>
        <script type='text/javascript' src='js/jquery.client.js'></script>
        <script type='text/javascript'>

var isMobile = <?php echo $is_mobile?'true':'false'; ?>;

function resizeAllElements() {
    var vw = $(window).width();
    $('#contact_info, p, dl, dt, dd, h1, h2, h3, a').each( function() {
		resizeOneElement($(this), vw);
	});

}

function resizeOneElement($this, vw) {
    var dataKey = 'original-dimensions';
    var properties = ['font-size',
            'margin-top', 'margin-left', 'margin-bottom', 'margin-right',
            'padding-top', 'padding-left', 'padding-bottom', 'padding-right'];

	var original = $this.data(dataKey);
	if (! original) {
		original = {};
	    for (var i=0; i<properties.length; i++) {
			var name = properties[i];
			var value = $this.css(name);
			original[name] = value;
		}
		$this.data(dataKey, original);
	}

    for (var i=0; i<properties.length; i++) {
        var name = properties[i];
        var value = original[name];
        var newValue = (parseInt(value, 10) * vw / 225) + 'px';
        $this.css(name, newValue);
    }

}

$(function() {
    var osPattern = "[\?&]os=" + $.client.os + "(&.*)?$";
    var osRegex = new RegExp( osPattern );

    if ( window.location.search.search(osRegex) ) {
        window.location.search = 'os=' + $.client.os;
    }

	if (isMobile) {
		resizeAllElements();
		$(window).resize(resizeAllElements);
	}
});

        </script>
    </head>
    <body>
        <div id='contact_info'>
            <?php
include("include/config/contact_$os.html");
            ?>
        </div>
    </body>
</html>
