<?php
/****************
 * Validated on Feb-9,2018,
 * Google map Display (add conntype=direct)
 * parameter key=KZo3i6UJbKd0bb6B5Suv
 * option oemid
 * option maptype (default roadmap),satellite, terrain, hybrid     
 * http://workeyemap.megasys.com.tw/map/rpic_map.php 
 *Writer: JinHo, Chang
*****************/
if (file_exists("/var/www/qlync_admin/doc/config.php")){
	require_once ("/var/www/qlync_admin/doc/config.php"); 
}else if (file_exists("/var/www/SAT-CLOUDNVR/include/index_title.php")){
	require_once ("/var/www/SAT-CLOUDNVR/include/index_title.php");
  $oem = $oem_style_list['oem_id']; 
}
//if ($oem=="X02")
  if ( ($_REQUEST['key']!="KZo3i6UJbKd0bb6B5Suv")) die("No Permission");

require_once("rpic.inc");
header("Content-Type:text/html; charset=utf-8");
//how to define a demo set
//define ("OEM_DEMO", "C13"); //if (OEM_DEMO == OEM_ID) getMap("T06",$result);

if (isset($_REQUEST['oemid'])){
  //bypass error oemid
  if (($oem=="X02") or ($oem=="T06")) //only xpress site can access other map
	 define ("OEM_ID", $_REQUEST['oemid']);
  else  define ("OEM_ID", $oem);
}else if ($oem=="X02") 
  define ("OEM_ID", "N99"); 
else define ("OEM_ID", $oem);

if (OEM_ID=="N99"){
  define ("TXT_TITLE", "全民監工網");
  define ("TXT_TITLE_IMAGE", "image/T06_logo192.png");
}else{
  define ("TXT_TITLE", "地圖");
  define ("TXT_TITLE_IMAGE", "/images/config/product_logo.png");
}

if (isset($_REQUEST['maptype']))
	define ("MapTypeId", $_REQUEST['maptype']);
else{
  if ((OEM_ID == "C13")) define ("MapTypeId", "hybrid");
 else  define ("MapTypeId", "roadmap");
}

$mapData = array();
readGIS($mapData);
//var_dump($mapData);
function readGIS(&$data)
{
	$result=array();
	$content=array();
if (($_REQUEST['conntype'] =="direct")and (OEM_ID!="N99") ){ //default file
	getMap(OEM_ID,$result);
	parseMap(OEM_ID,$result, $content);
//var_dump($content);
  parseMap2Marker(OEM_ID,$content, $data);
}else{
  define (CACHE_FILE, $GIS_FILE[OEM_ID]);
  $diff = time()-filemtime(CACHE_FILE); //in seconds 60 
  if ($diff > 900){
    getMap(OEM_ID,$result);
  }else{
	getMapFile(OEM_ID,$result);
  }
	parseMap(OEM_ID,$result, $content);
//var_dump($content);
  parseMap2Marker(OEM_ID,$content, $data);
}
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<!--force disable compatibility view--->
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<title><?php echo TXT_TITLE;?></title>
<!-- To support ios sizes -->
<link rel="apple-touch-icon" href="<?php echo TXT_TITLE_IMAGE;?>">
<!-- To support android sizes -->
<link rel="icon" href="<?php echo TXT_TITLE_IMAGE;?>">
<!--link rel="icon" sizes="192×192" href="image/T06_logo192.png">
<link rel="icon" sizes="128×128" href="image/T06_logo192.png"-->
<?php
if (isMobile())
{
printMobileMeta();
}else{//detect user agent
	printRefreshMeta(600);
}
?>
<style type="text/css">
 <?php
if (isMobile())
{
?>
#map {
	width: device-width;
  height: 500px;
}
<?php
	}else{
?>
#map {
	width: 100%;
  height: 500px;
}
<?php
	}//detect user agent
?>
/* Optional: Makes the sample page fill the window. */
html, body {
  height: 100%;
  margin: 0;
  padding: 0;
}
#legend {
  font-family: Arial, sans-serif;
  background: #fff;
  padding: 10px;
  margin: 10px;
  border: 3px solid #000;
}
#legend h3 {
  margin-top: 0;
}
#legend img {
  vertical-align: middle;
}
a img {
    border:none;
    outline:none;
}
</style>
</head>
<body>
<!--script src="//maps.googleapis.com/maps/api/js?key=AIzaSyD2D2DbGan19m9dYFWhfqaY-R9BzLuH5TU" type="text/javascript"></script-->
<script src="//maps.googleapis.com/maps/api/js?key=AIzaSyDo5ubYMOIIrpg8on7pfmMpUx9bTSF53K8" type="text/javascript"></script>

<script type="text/javascript">
var targetlat = "<?php echo $_REQUEST['mylat'];?>";
var targetlng = "<?php echo $_REQUEST['mylng'];?>";
//var targetmarker;
var routes = [
<?php
	getN99Marker($mapData); 
?>
];
var map;
//https://sites.google.com/site/gmapicons/home/
 var icons = {
          T04: {
            name: '台北道管',
            icon: "image/T04.png",
            zoom: 12,
            gis: { lat: 25.07, lng: 121.50 }
          },
          T05: {
            name: '桃園道管',
            icon: "image/T05.png",
            zoom: 12,
            gis: { lat: 24.99, lng: 121.28 }
          },
          K01: {
            name: '高雄道管',
            icon: "image/K01.png",
            zoom: 11,
            gis: { lat: 22.65, lng: 120.41 }
          },
          X02: {
            name: 'Xpress',
            icon: "image/X02.png",
            zoom: 12,
            gis: { lat: 25.04, lng: 121.46 }
          },
          T06: {
            name: '工務影像',
            icon: "image/T06.png",
            zoom: 12,
            gis: { lat: 25.066, lng: 121.234 }
          },
          C13: {
            name: '奇美工務',
            icon: "", //image/C13.png
            zoom: 18,
            gis: { lat: 22.926689, lng: 120.243476 }
          },
        	DEMO: {
            name: 'DEMO',
            icon: "",
            zoom: 12,
            gis: { lat: 25.04, lng: 121.46 }
          },  
	        N99: {
            name: '台灣',
            icon: "",
            zoom: 8,
            gis: { lat: 23.9, lng: 120.67 }
          }
        };
var APPMODE_Array=[
<?php
  $tmp = "";
  if (!is_null($GISAPPMODE_COLOR)){
    foreach($GISAPPMODE_COLOR as $mode=>$color){
      $tmp.= "\n[\"{$mode}\",\"{$color}\"], ";
    }
    $tmp = rtrim($tmp, ", ");
  }
  $tmp.= "\n";
  echo $tmp;
?>
];
function findColor(str){
  var tmpString;
  for (var i = 0; i < APPMODE_Array.length; i++) {//match to find color
    tmpString = APPMODE_Array[i][0].replace("<?php echo OEM_ID;?>","");
    if (str.indexOf(tmpString) >=0)
      return APPMODE_Array[i][1].replace('#','');
  }
  return "";
}
//global infowindow for one infowindow only
var infowindow = new google.maps.InfoWindow();

function initialize() {
  map = new google.maps.Map(document.getElementById('map'), {
    zoom: <?php echo "icons['".OEM_ID."'].zoom";?>,
    center: <?php echo "icons['".OEM_ID."'].gis";?>,
    mapTypeId: '<?php echo MapTypeId;?>'
  });
  //make sure infowindow is init.
  if(infowindow == null) infowindow = new google.maps.InfoWindow();
  setMarkers(map);
 	var legend = document.getElementById('legend');
  for (var key in icons) {
    if (icons[key].icon!=""){
    var div = document.createElement('div');
    //<a href="javascript: newCenterLoc(); return false;">
    //+"?oemid=<?php echo OEM_ID;?>"
    div.innerHTML = '<a href="javascript: newCenterLoc('+icons[key].gis.lat+','+icons[key].gis.lng+','+icons[key].zoom+');"><img src="' + icons[key].icon + '"></a>' + "<a href='rpic_map.php?key=<?php echo $_REQUEST['key'];?>&oemid="+key+"'>"+icons[key].name+"</a>";
    <?php if (OEM_ID!="N99") echo "if (key==\"".OEM_ID."\")";?>  
        legend.appendChild(div);
    }
  }
	map.controls[google.maps.ControlPosition.LEFT_TOP].push(legend);
	
	if (targetlat !=""){ 	//new center if var is set
		newCenterLoc(parseFloat(targetlat),parseFloat(targetlng),icons['<?php echo OEM_ID;?>'].zoom);
    //infowindow.open(map,targetmarker);
  }
}
function setMarkers(map) {
  for (var i = 0; i < routes.length; i++) {
    var route = routes[i];
    var content = route[4];
    var pinColor,pinImage;
    pinImage =  icons[route[0]].icon;
    <?php
      if (OEM_ID != "N99") {
    ?>
    pinColor = findColor(content); 
    if  ( pinColor != ""){
      pinImage = new google.maps.MarkerImage("//chart.apis.google.com/chart?chst=d_map_pin_letter&chld=%E2%80%A2|" + pinColor,
        new google.maps.Size(21, 34),
        new google.maps.Point(0,0),
        new google.maps.Point(10, 34));
    }//else if (pinColor == "") pinColor = "000000";
    <?php
      }//marker image
    ?> 
    var marker = new google.maps.Marker({
      position: {lat: route[2], lng: route[3]},
      map: map,
      icon: pinImage,//icons[route[0]].icon,
      title: route[1]
    });
    //if ( (route[2]==targetlat) and (route[3]==targetlng))
    //  targetmarker = marker; 
    
    if(infowindow == null){
      var infow = new google.maps.InfoWindow();
		google.maps.event.addListener(marker,'click', (function(marker,content,infow){ 
      return function() {
          infow.setContent(content);
          infow.open(map,marker);
      };
    })(marker,content,infow));

    }else{
    //click, domready
    //google.maps.event.addListener(infowindow, 'domready', function() {
		google.maps.event.addListener(marker,'click', (function(marker,content,infowindow){ 
      return function() {
      	 if (infowindow) {
      			infowindow.close();
      	 }
         //if(content !== null && content !== '')
          infowindow.setContent(content);
          infowindow.open(map,marker);
      };
    })(marker,content,infowindow));
  //});//domready
  }//iffowindow null
  }//for
}
function newCenterLoc(newLat,newLng, newZoom)
{
	map.setCenter({		lat : newLat,		lng : newLng	});
	map.setZoom(newZoom);
}
//if no cannot use <body onload="initialize()"> use below instead
google.maps.event.addDomListener(window, 'load', initialize);
</script>
<div id="map"  style="width:100%;height:100%;"></div>
<div id="legend"><!--h3>Legend</h3-->
<?php
if (file_exists($GIS_FILE[OEM_ID]))
  if (($_REQUEST['conntype'] =="direct")and (OEM_ID!="N99") )
	   echo "<small><b>".date("Y-m-d H:i:s")."</small></b>";
  else  echo "<small><b>".date("Y-m-d H:i:s",filemtime($GIS_FILE[OEM_ID]))."</small></b>";
?>
</div>
  </body>
</html>