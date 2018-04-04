<?php
/****************
 *Validated on Sep-28,2017,
 * convert url to google shorten url
 * keep converted url in csv format=> <item>,<item>\n
 * chmod 777 /var/www/qlync_admin/plugin/licservice/urlshort.log             
 *Writer: JinHo, Chang
*****************/
//Server Key
include_once("/var/www/qlync_admin/header.php");
if (!isset($_SESSION["Email"]) )  die("Require Login!!\n");
define("ApiKey","AIzaSyAULzwnZrP_CoBozLFwki1Dl0P1jtYQVeI");
$sql="SELECT * FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = 'customerservice' and TABLE_NAME='urlshort'";
sql($sql,$result,$num,0);
if ($num == 0){//table not exist, use text log instead
  define("DBEntry",false);
  define("URL_LOG","../billing/log/urlshort.log");
  if (!file_exists(URL_LOG)){
      exec(" echo '' > ".URL_LOG);
      chmod(URL_LOG,0777); 
  }else exec("touch ".URL_LOG);
}else define("DBEntry",true);

if (isset($_REQUEST['longurl'])){ 
//Long to Short URL
  //$postData = array('longUrl' => $_REQUEST['longurl'], 'key' => ApiKey);
  $postData = array('longUrl' => $_REQUEST['longurl']);
  $info = httpsPost($postData);
//var_dump($info);
  if (($info != null) and (is_null($info->error))) 
  {
      $shortUrl = $info->id;
      $msg = "{$_REQUEST['name']}: {$_REQUEST['longurl']} <br>shorten as => <font color=blue>{$shortUrl}</font><br>";
      $_REQUEST['shorturl'] = $shortUrl;
      if (DBEntry)  insertEntry($_REQUEST);
      else writeLog($_REQUEST); 
  }else{
    if (isset($info))
      $msg = "<font color=red>Error: (".$info->error->code.")".$info->error->message."</font>";
  }
}
function httpsPost($postData)
{
  $curlObj = curl_init();
  $jsonData = json_encode($postData);
  curl_setopt($curlObj, CURLOPT_URL, 'https://www.googleapis.com/urlshortener/v1/url?key='.ApiKey);
  //curl_setopt($curlObj, CURLOPT_URL, 'https://www.googleapis.com/urlshortener/v1/url');
  curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt($curlObj, CURLOPT_HEADER, 0);
  curl_setopt($curlObj, CURLOPT_HTTPHEADER, array('Content-type:application/json'));
  curl_setopt($curlObj, CURLOPT_POST, 1);
  curl_setopt($curlObj, CURLOPT_POSTFIELDS, $jsonData);
  $response = curl_exec($curlObj);
  //change the response json string to object
  $json = json_decode($response);
  curl_close($curlObj);
  return $json;
}
 
function httpGet($params)
{ 
  $curlObj = curl_init('https://www.googleapis.com/urlshortener/v1/url?'.http_build_query($params));
  curl_setopt($curlObj, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($curlObj, CURLOPT_SSL_VERIFYPEER, 0);
  curl_setopt($curlObj, CURLOPT_HEADER, 0);
  curl_setopt($curlObj, CURLOPT_HTTPHEADER, array('Content-type:application/json'));
  $response = curl_exec($curlObj);
  //change the response json string to object
  $json = json_decode($response);
  curl_close($curlObj);
  return $json;
}
function getInfo($shortUrl)
{  //Short URL Information
  if (strpos($shortUrl,"http")===FALSE) return "shortURL Info"; //not url
  $params = array('shortUrl' => $shortUrl, 'key' => ApiKey,'projection' => "FULL");
  $html = "";
  $info = httpGet($params);
  if($info != null)
  {
      $html= "Crated by ".$info->created ."<br>\n clicks time:".$info->analytics->allTime->shortUrlClicks."<br>\n";
  }
  return $html;
}

/*
{
 "kind": "urlshortener#url",
 "id": "http://goo.gl/fbsS",
 "longUrl": "http://www.google.com/",
 "status": "OK",
 "created": "2009-12-13T07:22:55.000+00:00",
 "analytics": {
  "allTime": {
   "shortUrlClicks": "3227",
   "longUrlClicks": "9358",
   "referrers": [ { "count": "2160", "id": "Unknown/empty" }  ],
   "countries": [ { "count": "1022", "id": "US" }  ],
   "browsers": [ { "count": "1025", "id": "Firefox" }  ],
   "platforms": [ { "count": "2278", "id": "Windows" }  ]
  },
  "month": {  },
  "week": {  },
  "day": {  },
  "twoHours": {  }
 }
}
*/


function listTable()
{
  if (!file_exists(URL_LOG)) return;
  $f = fopen(URL_LOG, "r");
  if ($f) {
    $html = "<table border=1 style=\"table-layout: fixed;\">\n";
    $html .= "<tr><td>Name</td><td>URL</td><td>shortURL</td></tr>\n";
    while (!feof($f)) {
        $item = explode(",", str_replace(array("\n", "\t", "\r"), '', fgets($f)));
        if (sizeof($item)<3) break;
        //style overflow:hidden;
        //$html .="<tr><td>{$item[0]}</td><td style=\"word-wrap:break-word;max-width:350px;\">".((strpos($item[1],"http")!==FALSE)? "<a href=\"{$item[1]}\" target='goourl'>{$item[1]}</a>": "{$item[1]}")."</td><td><div class='tooltip'>{$item[2]}<span class='tooltiptext'>".getInfo($item[2])."</span></div>";
        $html .="<tr><td>{$item[0]}</td><td style=\"word-wrap:break-word;max-width:350px;\">".((strpos($item[1],"http")!==FALSE)? "<a href=\"{$item[1]}\" target='goourl'>{$item[1]}</a>": "{$item[1]}")."</td><td>{$item[2]}";
        $html .="<input type=button name=\"btn_info\" value=\"Status\"  onclick=\"getShortInfo('{$item[2]}');\">";
        $html .="<input type=button name=\"btn_qr\" value=\"QR\"  onclick=\"genQRPage('{$item[2]}');\">";
        $html .="</td></tr>\n";
    }
    $html .= "</table>\n";
    echo $html;
  }
}
function writeLog($arr){
  if (!file_exists(URL_LOG)) return;
  file_put_contents(URL_LOG, "{$arr['name']},{$arr['longurl']},{$arr['shorturl']}\n" . file_get_contents(URL_LOG));
}

function listDB(){
  if (!DBEntry) return;
  $sql = "select * from customerservice.urlshort";
  sql($sql,$result,$num,0);
  if ($num > 0){
    $html = "<table border=1 style=\"table-layout: fixed;\">\n";
    $html .= "<tr><td>Name</td><td>URL</td><td>shortURL</td></tr>\n";
    for ($i=0;$i<$num;$i++){
        fetch($arr,$result,$i,0);
        $html .="<tr><td>{$arr['name']}</td><td style=\"word-wrap:break-word;max-width:350px;\">".((strpos($arr['url'],"http")!==FALSE)? "<a href=\"{$arr['url']}\" target='goourl'>{$arr['url']}</a>": "{$arr['url']}")."</td><td>{$arr['shorturl']}";
        $html .="<input type=button name=\"btn_info\" value=\"Status\"  onclick=\"getShortInfo('{$arr['shorturl']}');\">";
        $html .="<input type=button name=\"btn_qr\" value=\"QR\"  onclick=\"genQRPage('{$arr['shorturl']}');\">";
        $html .="</td></tr>\n";
    }
    $html .= "</table>\n";
    echo $html;
  }
}
function insertEntry($arr)
{
  if (!DBEntry) return;
  $sql = "INSERT INTO customerservice.urlshort SET
  name = '{$arr['name']}', 
  url = '{$arr['longurl']}',
  shorturl = '{$arr['shorturl']}'";
  sql($sql,$result,$num,0);
  return $result;
}
?>
<!--html>
<head>
<title>Google Shorten URL</title>
</head>
<body-->
<style>
/* Tooltip container */
.tooltip {
    position: relative;
    display: inline-block;
    border-bottom: 1px dotted black; /* If you want dots under the hoverable text */
}
/* Tooltip text */
.tooltip .tooltiptext {
    visibility: hidden;
    width: 350px;
    background-color: black;
    color: #fff;
    text-align: center;
    padding: 5px 0;
    border-radius: 6px;
    position: absolute;
    z-index: 1;
    /* Position the tooltip text - see examples below! */
    top: 25px;
    left: 10%;
    max-width: 350px;
    //white-space: nowrap; 
}

/* Show the tooltip text when you mouse over the tooltip container */
.tooltip:hover .tooltiptext {
    visibility: visible;
}

.info,.success,.warning,.error,.validation {
	border: 1px solid;
	margin: 10px 0px;
	padding: 12px 10px 12px 50px;
	background-repeat: no-repeat;
	background-position: 10px center;
  word-wrap:break-word;
  max-width: 600px;
}

.info {
	color: #00529B;
	background-color: #BDE5F8;
}

.success {
	color: #4F8A10;
	background-color: #DFF2BF;
}

.warning {
	color: #9F6000;
	background-color: #FEEFB3;
}

.error {
	color: #D8000C;
	background-color: #FFBABA;
}
</style>
<script src="../user_log/js/jquery-1.11.1.min.js"></script>
<script src="//apis.google.com/js/client.js"> </script>
<script>
function genQRPage(myurl)
{
    var cmd = "https://xpress.megasys.com.tw:8080/plugin/licservice/listLicense_camQR.php?filename=tmp&data="+encodeURIComponent(myurl);
    window.open(cmd, 'my QR code', config='height=300,width=250');
}
function dump(obj) {
    var out = '';
    for (var i in obj) {
        out += i + ": " + obj[i] + "\n";
   }
return out;
}

function getShortInfo(shortUrl)
{
  var request = gapi.client.urlshortener.url.get({
    'shortUrl': shortUrl,
  'projection':'FULL'
  });
  request.execute(function(response) 
  {
      if(response.id!= null)//if(response.longUrl!= null)
      {
          str ="<b>Long URL: </b>"+response.longUrl+"<br>";
          str +="<b>Create On: </b>"+response.created+"<br>";
          str +="<b>Short URL Clicks: </b>"+response.analytics.allTime.shortUrlClicks+"<br>";
          //str+="<b>countries: </b>"+JSON.stringify(response.analytics.allTime.countries)+"<br>";
          //str+="<b>browsers: </b>"+response.analytics.allTime.browsers+"<br>";
          //str+="<b>platforms: </b>"+response.analytics.allTime.platforms+"<br>";
          //str +="<b>Long URL Clicks: </b>"+response.analytics.allTime.longUrlClicks+"<br>";
          showMessage("info",str);
      }
      else
      {
          showMessage("error",response.error);
          
      }
  });
}
function showMessage(className,msg){
	var obj = $('#info');
	obj.attr("class", className);
	obj.html(msg);
	obj.show();
}
function hideMessage(){
	$('#info').hide();
}
function load()
{
    //Get your own Browser API Key from  https://code.google.com/apis/console/
    gapi.client.setApiKey('AIzaSyAULzwnZrP_CoBozLFwki1Dl0P1jtYQVeI');
    gapi.client.load('urlshortener', 'v1',function(){hideMessage();});
 
}
window.onload = load;
</script>
<form id="goourl" method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
Name: <input type="text" id="name" name="name" size=20 value="<?php echo ( isset($_REQUEST) ? $_REQUEST['name']:'T05 iOS');?>" /><br/>
URL: <input type="text" id="longurl" name="longurl" size=50 value="<?php echo ( isset($_REQUEST) ? $_REQUEST['longurl']:'http://');?>" /> <br/>
<input type="submit" value="Create Short"/> <br/> <br/>
</form>
<?php
if (isset($msg)) echo $msg;
?>
<hr>
<div id="info"></div>
<?php if (DBEntry)  listDB();  else  listTable();?>
</body>
</html>