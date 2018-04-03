<?php
/****************
 *Validated on Jun-10,2015,
 * App download list page  for android using http only on web, 
 *  auto switch https or http
 * Translated to Chinese page  
 *Writer: JinHo, Chang
 ****************/

/* //check for redirect if rpic SSL is not working for iOS
if ( preg_match("/rpic.taipei/",$_SERVER['HTTP_HOST']) ){
  $u_agent = $_SERVER['HTTP_USER_AGENT'];
  if( preg_match("/OS /",$u_agent) )
    if( ! preg_match("/OS 8_/",$u_agent, $regs) )
      header("Location: https://xpress.megasys.com.tw:8080/plugin/taipei/appdownload.php");
    //else if( preg_match("/OS 7_/",$u_agent, $regs) ) echo "OS7\n";
}
*/


function listAppFile()
{
define("APK_FILE_POSTFIX",".apk");
define("IOS_FILE_POSTFIX",".plist");
    $html = "<h2>裝置應用下載</h2>";
    $files = scandir(".", SCANDIR_SORT_ASCENDING);
      $u_agent = $_SERVER['HTTP_USER_AGENT'];
      $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
    for($i=0;$i<sizeof($files);$i++){
      if (strpos($files[$i],APK_FILE_POSTFIX)!== FALSE){
            if( preg_match("/Android/",$u_agent) )
              if (strpos($files[$i],"rpic")!== FALSE)
                $html.= "\n安卓 <br><a href='".$protocol.$_SERVER['HTTP_HOST']."/taipei/{$files[$i]}'>道管施工影像即時監控</a><br>";
              else
                $html.= "\n安卓 <br><a href='".$protocol.$_SERVER['HTTP_HOST']."/taipei/{$files[$i]}'>{$files[$i]}</a><br>";
      }
      if (strpos($files[$i],IOS_FILE_POSTFIX)!== FALSE){
            if( preg_match("/OS /",$u_agent) )
                //$html.= "\n蘋果 <br><a href='https://".$_SERVER['HTTP_HOST']."/plugin/taipei/{$files[$i]}'>{$files[$i]}</a><br>";
                if (strpos($files[$i],"rpic")!== FALSE)
                  $html.= "\n蘋果 <br><a href='itms-services://?action=download-manifest&url=".$protocol.$_SERVER['HTTP_HOST']."/taipei/{$files[$i]}'>道管施工影像即時監控</a><br>";
                else
                  $html.= "\n蘋果 <br><a href='itms-services://?action=download-manifest&url=".$protocol.$_SERVER['HTTP_HOST']."/taipei/{$files[$i]}'>{$files[$i]}</a><br>";
      }
    }//for
  $html .= "\n";   //add table end
  if( preg_match("/OS /",$u_agent) )
      if( ! preg_match("/OS 8_/",$u_agent, $regs) ){
        $html.= "<ul>如果您出現無法連接rpic, 請先安裝政府簽署憑證<a href=http://grca.nat.gov.tw/repository/Certs/GRCA.cer target=blank>GRCA</a>&nbsp;<a href=http://grca.nat.gov.tw/repository/Certs/GRCA2.cer target=blank>GRCA2</a>\n";
      }
	echo $html;
}

?>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body>
<?php
listAppFile();
?>
</body>
</html>