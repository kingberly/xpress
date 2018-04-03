<?php
class MyConfig{
public $DB_ENABLE = 0;
/**
 * Invoke to change the field $DB_ENABLE from external
 * @param boolean
 * @ http://url:8080/html/api/util.php?setdb=0 
 */
  function setDB($status){
  		$file = 'util.php';
  		$find = "DB_ENABLE = $this->DB_ENABLE";
  		$replace = "DB_ENABLE = $status";
  		file_put_contents($file,str_replace($find,$replace,file_get_contents($file)));
  }
}

if(isset($_GET['setdb'])){
	$stat = $_GET['setdb'];
	$config = new MyConfig();
	$config->setDB($stat);
	echo 'Set DB MODE as '.$stat;
}

 
function InsertLog($action, $result) {
  $myconfig = new MyConfig();
    if ($myconfig->DB_ENABLE==0) return;
    $user_agent= parse_user_agent();    
    if (!is_null($user_agent))     
      $user_agent_str = $user_agent['platform']."(".$user_agent['browser']."/".$user_agent['version'].")";
    else $user_agent_str = "";
    $client_ip="";//limit 63 char = 4 x IP(15)
    if(isset($_SERVER['HTTP_CLIENT_IP']))
        $client_ip = $_SERVER['HTTP_CLIENT_IP'];
    if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) ) 
        $client_ip .= "/" . $_SERVER['HTTP_X_FORWARDED_FOR'];
    //pick one
    if(isset($_SERVER['HTTP_X_FORWARDED']) ) 
        $client_ip .= "/" . $_SERVER['HTTP_X_FORWARDED'];
    else if(isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) ) 
        $client_ip .= "/" . $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
    else if(isset($_SERVER['HTTP_FORWARDED_FOR']) ) 
        $client_ip .= "/" . $_SERVER['HTTP_FORWARDED_FOR'];
    else if(isset($_SERVER['HTTP_FORWARDED']) ) 
        $client_ip .= "/" . $_SERVER['HTTP_FORWARDED'];

    if(isset($_SERVER['REMOTE_ADDR'])){
        $client_ip .= "/" . $_SERVER['REMOTE_ADDR'];
        if(isset($_SERVER['HTTP_VIA']) and ($_SERVER['HTTP_VIA']!=$_SERVER['REMOTE_ADDR']) )
            $client_ip .= "/" . $_SERVER['HTTP_VIA'];
    }

    $sql ="insert into customerservice.api_log (api,action,result,ip_addr, user_agent) values
    ('".$_SERVER['PHP_SELF']."','{$action}','{$result}','{$client_ip}','{$user_agent_str}' )";

    //echo $sql;
    sql($sql,$result,$num,0);
}
/**
* Parses a user agent string into its important parts
*
* @author Jesse G. Donat <donatj@gmail.com>
* @link https://github.com/donatj/PhpUserAgent
* @link http://donatstudios.com/PHP-Parser-HTTP_USER_AGENT
* @param string $u_agent
* @return array an array with browser, version and platform keys
*/
function parse_user_agent( $u_agent = null ) {
	if(is_null($u_agent) && isset($_SERVER['HTTP_USER_AGENT'])) $u_agent = $_SERVER['HTTP_USER_AGENT'];

	$data = array(
		'platform' => null,
		'browser'  => null,
		'version'  => null,
	);

	if(!$u_agent) return $data;

	if( preg_match('/\((.*?)\)/im', $u_agent, $regs) ) {

		preg_match_all('/(?P<platform>Android|CrOS|iPhone|iPad|Linux|Macintosh|Windows(\ Phone\ OS)?|Silk|linux-gnu|BlackBerry|Nintendo\ (WiiU?|3DS)|Xbox)
			(?:\ [^;]*)?
			(?:;|$)/imx', $regs[1], $result, PREG_PATTERN_ORDER);

		$priority = array('Android', 'Xbox');
		$result['platform'] = array_unique($result['platform']);
		if( count($result['platform']) > 1 ) {
			if( $keys = array_intersect($priority, $result['platform']) ) {
				$data['platform'] = reset($keys);
			}else{
				$data['platform'] = $result['platform'][0];
			}
		}elseif(isset($result['platform'][0])){
			$data['platform'] = $result['platform'][0];
		}
	}

	if( $data['platform'] == 'linux-gnu' ) { $data['platform'] = 'Linux'; }
	if( $data['platform'] == 'CrOS' ) { $data['platform'] = 'Chrome OS'; }

	preg_match_all('%(?P<browser>Camino|Kindle(\ Fire\ Build)?|Firefox|Safari|MSIE|AppleWebKit|Chrome|IEMobile|Opera|Silk|Lynx|Version|Wget|curl|NintendoBrowser|PLAYSTATION\ \d+)
			(?:;?)
			(?:(?:[/ ])(?P<version>[0-9A-Z.]+)|/(?:[A-Z]*))%x',
	$u_agent, $result, PREG_PATTERN_ORDER);

	$key = 0;

	$data['browser'] = $result['browser'][0];
	$data['version'] = $result['version'][0];

	if( ($key = array_search( 'Kindle Fire Build', $result['browser'] )) !== false || ($key = array_search( 'Silk', $result['browser'] )) !== false ) {
		$data['browser']  = $result['browser'][$key] == 'Silk' ? 'Silk' : 'Kindle';
		$data['platform'] = 'Kindle Fire';
		if( !($data['version'] = $result['version'][$key]) || !is_numeric($data['version'][0]) ) {
			$data['version'] = $result['version'][array_search( 'Version', $result['browser'] )];
		}
	}elseif( ($key = array_search( 'NintendoBrowser', $result['browser'] )) !== false || $data['platform'] == 'Nintendo 3DS' ) {
		$data['browser']  = 'NintendoBrowser';
		$data['version']  = $result['version'][$key];
	}elseif( ($key = array_search( 'Kindle', $result['browser'] )) !== false ) {
		$data['browser']  = $result['browser'][$key];
		$data['platform'] = 'Kindle';
		$data['version']  = $result['version'][$key];
	}elseif( $result['browser'][0] == 'AppleWebKit' ) {
		if( ( $data['platform'] == 'Android' && !($key = 0) ) || $key = array_search( 'Chrome', $result['browser'] ) ) {
			$data['browser'] = 'Chrome';
			if( ($vkey = array_search( 'Version', $result['browser'] )) !== false ) { $key = $vkey; }
		}elseif( $data['platform'] == 'BlackBerry' ) {
			$data['browser'] = 'BlackBerry Browser';
			if( ($vkey = array_search( 'Version', $result['browser'] )) !== false ) { $key = $vkey; }
		}elseif( $key = array_search( 'Safari', $result['browser'] ) ) {
			$data['browser'] = 'Safari';
			if( ($vkey = array_search( 'Version', $result['browser'] )) !== false ) { $key = $vkey; }
		}

		$data['version'] = $result['version'][$key];
	}elseif( ($key = array_search( 'Opera', $result['browser'] )) !== false ) {
		$data['browser'] = $result['browser'][$key];
		$data['version'] = $result['version'][$key];
		if( ($key = array_search( 'Version', $result['browser'] )) !== false ) { $data['version'] = $result['version'][$key]; }
	}elseif( $result['browser'][0] == 'MSIE' ){
		if( $key = array_search( 'IEMobile', $result['browser'] ) ) {
			$data['browser'] = 'IEMobile';
		}else{
			$data['browser'] = 'MSIE';
			$key = 0;
		}
		$data['version'] = $result['version'][$key];
	}elseif( $key = array_search( 'PLAYSTATION 3', $result['browser'] ) !== false ) {
		$data['platform'] = 'PLAYSTATION 3';
		$data['browser']  = 'NetFront';
	}

	return $data;
}

?>