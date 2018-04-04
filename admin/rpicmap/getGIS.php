 <?php
/****************
 *Validated on Feb-26,2018,
 * Google map Locator
 * parameter: addr, mygps = geo/addr
 * force reload 6 sec
 *Writer: JinHo, Chang
*****************/
define("TXT_TITLE","地圖找坐標");
define("TXT_USER_GIS_LOC","使用者地理位置定位");
include("/var/www/qlync_admin/doc/config.php");
if (isset($_REQUEST['oemid']))
  define ("OEM_ID", $_REQUEST['oemid']);
else define ("OEM_ID", $oem);            
header("Content-Type:text/html; charset=utf-8");
require_once ("share.inc");
require_once ("rpic.inc");
if (isset($_REQUEST['maptype']))
  define ("MapTypeId", $_REQUEST['maptype']);
else{
  define ("MapTypeId", "hybrid");
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<?php
if (isMobile()) //detect user agent
  printMobileMeta();
?>
<title><?php echo TXT_TITLE;?></title>
<!--force disable compatibility view--->
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<meta charset="UTF-8" />
<style type="text/css">
#map {
  width: 100%;
  height: 100%;
}
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
<script type="text/javascript">
function sendValue(value)
{
    //window.opener.updateValue(parentId, value);
    window.opener.updateUserValue(value);
    setTimeout(function() {
    window.close();
    },1000);
}
</script>
<script src="//maps.googleapis.com/maps/api/js?key=AIzaSyDo5ubYMOIIrpg8on7pfmMpUx9bTSF53K8" type="text/javascript"></script>
<!--a style="cursor: pointer;" onclick="myNavFunc()">Take me there!</a-->
</head>
<body>
<script type="text/javascript">
var map;
//https://sites.google.com/site/gmapicons/home/
 var icons = {
          T04: {
            zoom: 12,
            gis: { lat: 25.07, lng: 121.50 }
          },
          T05: {
            zoom: 12,
            gis: { lat: 24.99, lng: 121.28 }
          },
          K01: {
            zoom: 11,
            gis: { lat: 22.65, lng: 120.41 }
          },
          X02: {
            zoom: 12,
            gis: { lat: 25.04, lng: 121.46 }
          },
          T06: {
            zoom: 12,
            gis: { lat: 25.066, lng: 121.234 }
          },
          C13: {
            zoom: 18,
            gis: { lat: 22.926689, lng: 120.243476 }
          },
          DEMO: {
            zoom: 16,
            gis: { lat: 25.04, lng: 121.46 }
          },  
          N99: {
            zoom: 8,
            gis: { lat: 23.9, lng: 120.67 }
          }
        };

var addr = "<?php echo urldecode($_REQUEST['addr']);?>";
var mygps = "<?php echo $_REQUEST['mygps'];?>";//addr or geo or (empty)
var targetlat = "<?php echo $_REQUEST['mylat'];?>";
var targetlng = "<?php echo $_REQUEST['mylng'];?>";

var geolocation=window.navigator.geolocation; //html5 gps
var gpsType;
//global infowindow for one infowindow only
var infowindow = new google.maps.InfoWindow();
var myMarkerArr=[];
var myMarker;

icons['DEMO'].gis = icons['<?php echo OEM_ID;?>'].gis;
if (icons['<?php echo OEM_ID;?>'].zoom > icons['DEMO'].zoom) 
  icons['DEMO'].zoom = icons['<?php echo OEM_ID;?>'].zoom;

//default myMarker
if (mygps == "addr")
    codeAddress(); //run this to set addr @ center
else {
  if (geolocation) codeGEOLoc();    
  else codeAddress(); //run this to set addr @ center
}

//var location_timeout = setTimeout("showPositionFail()", 5000);
function showPosition(position) {
    //clearTimeout(location_timeout); //iOS11 has 50% chance fail to load GPS
    //x.innerHTML = "Latitude: " + position.coords.latitude +"<br>Longitude: " + position.coords.longitude;
    icons['DEMO'].gis.lat=position.coords.latitude;
    icons['DEMO'].gis.lng=position.coords.longitude;
    if (targetlat=="") targetlat = icons['DEMO'].gis.lat;
    if (targetlng=="") targetlng = icons['DEMO'].gis.lng;
    //alert(targetlat+ ";"+ targetlng);
    myMarker=placeMarker(icons['DEMO'].gis);
}

function codeGEOLoc()
{
  //if (geolocation){ //run to set current gps @ center
      geolocation.getCurrentPosition(showPosition,
      function (error) {
        if (error.code == error.PERMISSION_DENIED){
            alert("瀏覽器拒絕使用GPS");
            window.open("https://support.google.com/chrome/answer/142065?hl=zh-Hant");
            window.location.href=window.location.href+'&mygps=addr';
        }else{
          alert("無法取得GPS");
          window.location.href=window.location.href+'&mygps=addr';
        }
      });
      gpsType = "<?php echo TXT_USER_GIS_LOC;?>";
   //}
}

function showPositionFail(){
  clearTimeout(location_timeout);
  alert("無法取得GPS");
}
function codeAddress() {
    if (addr=="") return;
    if (! addr.match(/[\u3040-\u30ff\u3400-\u4dbf\u4e00-\u9fff\uf900-\ufaff\uff66-\uff9f]/)) return;
    var geocoder= new google.maps.Geocoder();
    geocoder.geocode( { 'address': addr}, function(results, status) {
      if (status == google.maps.GeocoderStatus.OK) {
      //alert(addr+";"+results[0].geometry.location.lat()+";"+results[0].geometry.location.lng());

        icons['DEMO'].gis.lat=results[0].geometry.location.lat();
        icons['DEMO'].gis.lng=results[0].geometry.location.lng();
        myMarker=placeMarker(icons['DEMO'].gis);
        if (targetlat=="") targetlat = icons['DEMO'].gis.lat;
        if (targetlng=="") targetlng = icons['DEMO'].gis.lng;
        gpsType = addr;
      } else {
        alert("地址查詢失敗原因: " + status);
        gpsType = "";
      }
    });
}

function initialize() {
    map = new google.maps.Map(document.getElementById('map'), {
    zoom: icons['DEMO'].zoom,
    center: icons['DEMO'].gis,
    mapTypeId: '<?php echo MapTypeId;?>'
  });

  if (myMarker!=null) {//init marker display
    myMarker.setMap(map);
    if (infowindow !== null){ //new add 11/23
    infowindow.setContent(gpsType+"<br>"+icons['DEMO'].gis.lat+";"+icons['DEMO'].gis.lng+";");
    infowindow.open(map,myMarker);
    }
  }
  google.maps.event.addListener(map, 'click', function(event) {
     //setMapOnAll(null); //clear previous marker
     clearMarkers();
     myMarker=placeMarker(event.latLng);
  });
  if (myMarker!=null) {//init marker
  myMarker.addListener('click', function() { //click on marker to locate
   sendValue("<?php echo $_REQUEST['tag'];?>;"+targetlat+";"+targetlng+";");//tagname      
  });
  } 
  google.maps.event.addListener(map, 'click', function(event) {
   var content= 'lat: ' + event.latLng.lat() + '<br>lng.: ' + event.latLng.lng();
   if (infowindow !== null){ //new add 11/23
   infowindow.setContent(content);
   infowindow.open(map,myMarker);
   }
   sendValue("<?php echo $_REQUEST['tag'];?>;"+event.latLng.lat()+";"+event.latLng.lng()+";");//tagname
});
  /*
  var legend = document.getElementById('legend');
  for (var key in icons) {
    if (icons[key].icon!=""){
      addLegend("newCenterLoc("+icons[key].gis.lat+","+icons[key].gis.lng+","+icons[key].zoom+")","<img src=" + icons[key].icon + ">");
    //var div = document.createElement('div');
    //div.innerHTML = '<a href="javascript: newCenterLoc('+icons[key].gis.lat+','+icons[key].gis.lng+','+icons[key].zoom+');"><img src="' + icons[key].icon + '"></a>' + icons[key].name;
    //legend.appendChild(div);
    }
  }
  */
  map.controls[google.maps.ControlPosition.LEFT_TOP].push(legend);
}
 
function placeMarker(location) {
    var marker = new google.maps.Marker({
        position: location, 
        map: map
    });
    myMarkerArr.push(marker);
    return marker;
}

function clearMarkers(){
    for(i=0; i<myMarkerArr.length; i++){
        myMarkerArr[i].setMap(null);
    }
    if (infowindow !== null) //new added 11/23
    infowindow.close()
}

function newCenterLoc(newLat,newLng, newZoom)
{
  map.setCenter({ lat : newLat, lng : newLng});
  map.setZoom(newZoom);
}

function showMyMarker() //global function
{
  if (targetlat !=""){
    icons['DEMO'].gis.lat=targetlat;
    icons['DEMO'].gis.lng=targetlng;
    //icons['DEMO'].zoom=14; //default 12
    myMarker=placeMarker(icons['DEMO'].gis);
    map.setZoom(icons['DEMO'].zoom);
    gpsType = "<?php echo TXT_USER_GIS_LOC;?>";
    myMarker.setMap(map);
    infowindow.setContent(gpsType+"<br>"+targetlat+";"+targetlng+";");
    infowindow.open(map,myMarker);

    myMarker.addListener('click', function() { //click on marker to locate
     sendValue("<?php echo $_REQUEST['tag'];?>;"+targetlat+";"+targetlng+";");//tagname      
    });
    newCenterLoc(targetlat,targetlng,icons['DEMO'].zoom);
  }else
    location.reload(); 
}
// https://developers.google.com/maps/documentation/javascript/browsersupport
var isIE = /*@cc_on!@*/false || !!document.documentMode;
var isLOAD=true;
if (isIE){  //10 OK,
  //document.write("IE:"+navigator.userAgent+"<br>");
  var ua = navigator.userAgent;
  var msie = ua.indexOf("MSIE ");
  if (isNaN(parseInt(ua.substring(msie + 5, ua.indexOf(".", msie))))) {
     if (navigator.appName == 'Netscape') {
        var re = new RegExp("Trident/.*rv:([0-9]{1,}[\.0-9]{0,})");
        if (re.exec(ua) != null) {
            rv = parseFloat(RegExp.$1);
            //document.write("Version:"+rv);
        }
        isLOAD = false;
     }
  }else{  //<IE11
    var msieV=parseInt(ua.substring(msie + 5, ua.indexOf(".", msie)));
    //document.write("Version:"+msieV);
    if (msieV<=9)  {
      document.write("<br>Google Map不支援相容性檢視及IE9以前版本<br>");
      isLOAD = false;
    } 
  }
document.write("<br>Google Map不支援相容性檢視及IE9以前版本<br>");
}
if (isLOAD){
//if no cannot use <body onload="initialize()"> use below instead
google.maps.event.addDomListener(window, 'load', initialize);
}
 
</script> 
<div id="map"></div>
<div id="legend"></div>
<script type="text/javascript">
if (mygps != "addr"){
   //addLegend ("window.location.href=window.location.href+'&mygps=addr<?php echo "&oemid=".OEM_ID ?>'","地址找GPS");
   addLegend ("window.location.href='//' + window.location.host + window.location.pathname+'?mygps=addr<?php echo "&oemid=".OEM_ID ?>'","地址找GPS");
    setTimeout(function(){
      if  (targetlat ==""){
        //alert("empty:"+targetlat);
        location.reload();
      }else{
        showMyMarker();//force repaint
        //alert("repaint:"+targetlat);
        //window.location.href='//' + window.location.host + window.location.pathname+'?targetlat='+targetlat+'&targetlng='+targetlng+'&addr='+addr;
      }
    },6000);
}else{
  addLegend ("window.location.href='//' + window.location.host + window.location.pathname+'?addr="+addr+"&mygps=geo<?php echo "&oemid=".OEM_ID ?>'","使用者GPS");
} 

function addLegend(js, tag)
{
  var legend = document.getElementById('legend');
  var tdiv = document.createElement('div');
  tdiv.innerHTML = "<a href=\"javascript: "+js+";\">"+tag+"</a>";
  legend.appendChild(tdiv);
}
</script>
</body>
</html>