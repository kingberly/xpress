<?php
/****************
 *Validated on May-3,2017,
 *  https://xpress.megasys.com.tw:8080/plugin/rpic/rpic_getList.php?userid=rpic&passwd=KZo3i6UJbKd0bb6B5Suv       
 *Writer: JinHo, Chang
*****************/
require_once("rpic.inc");
define (APP_REQ, "https://xpress.megasys.com.tw:8080/plugin/rpic/rpic_getList.php");

	$data=array(
		'lat'		=> array(24.95,25.07),
		'lng'		=> array(120.41,121.50)
	);
		$params = array(
			'userid'	=> 'rpic',
			'passwd'		=> 'KZo3i6UJbKd0bb6B5Suv',
			'filter'		=> json_encode($data)
		);
//var_dump($params);
//echo "================return=============";
	$ch = curl_init(); 
	curl_setopt($ch, CURLOPT_URL, APP_REQ);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
	curl_setopt($ch,CURLOPT_HTTPHEADER,array('Expect:'));
	$result = curl_exec($ch); //willllll print result??
//var_dump($result);
	$content=json_decode($result,true);
	$mapData = array();
parseMap2Array(OEM_ID,$content, $mapData);
	//curl_setopt($ch, CURLOPT_POST, false);
//var_dump($content);
//echo count($content); 
//echo count($content,1);
$i=0;
foreach ($mapData as $item){
$i++;
}
echo $i;
?>
