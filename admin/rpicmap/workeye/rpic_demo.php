<?php
header("Content-Type:application/json; charset=utf-8");
$mapData = array();
$myGIS= array(
			"OEM_ID" => "K01", //must include
			"ACNO" => "IVD98",
			"PURP" => "DEMO",
			"APNAME" => "高雄市道路挖掘管理中心",
			"DIGADD" => "高雄市新興區中正三路25號12F",
			"TCNAME" => "松華",
			"TC_TEL" => "02.29997699", 
			"LAT" => 22.6305815,
			"LNG" => 120.3099995,
			"VIDEONO" => 1,
			"APPMODE" => "DEMO",
			"URL" => "https://kreac.kcg.gov.tw/",  //must include
			"user_name" => "ivedatest",
			"user_pwd" => "1qaz2wsx",
			);
array_push($mapData,$myGIS);
$myGIS= array(
			"OEM_ID" => "T05", //must include
			"ACNO" => "IVD97",
			"PURP" => "DEMO",
			"APNAME" => "桃園市道路挖掘服務中心",
			"DIGADD" => "桃園風禾公園",
			"TCNAME" => "松華",
			"TC_TEL" => "02.29997699", 
			"LAT" => 25.0016072,
			"LNG" => 121.2912472,
			"VIDEONO" => 1,
			"APPMODE" => "DEMO",
			"URL" => "https://rpic.tycg.gov.tw/",  //must include
			"user_name" => "ivedatest",
			"user_pwd" => "1qaz2wsx",
			);
array_push($mapData,$myGIS);
$myGIS= array(
			"OEM_ID" => "T04", //must include
			"ACNO" => "IVD96",
			"PURP" => "DEMO",
			"APNAME" => "台北市道路管線中心",
			"DIGADD" => "台北市中正區和平西路一段59號",
			"TCNAME" => "松華",
			"TC_TEL" => "02.29997699", 
			"LAT" => 25.0268327,
			"LNG" => 121.5197896,
			"VIDEONO" => 1,
			"APPMODE" => "DEMO",
			"URL" => "https://rpic.taipei/",  //must include
			"user_name" => "ivedatest",
			"user_pwd" => "1qaz2wsx",
			);
array_push($mapData,$myGIS);
echo json_encode($mapData);
?>
