<?
// This api is used for telcom to setting the each camera recycle days as the mask api
// which should be combined with their billing system
// the internal_api id and pwd is setting in the config.php for limited users

include("../../header.php");
if($_REQUEST["id"]==$internal_api_id and $_REQUEST["pwd"]==$internal_api_pwd)
{
	$set=0;
	$web_address = "http://{$api_id}:{$api_pwd}@{$api_ip}:{$api_port}";
	$path = '/manage/manage_streamserver.php';
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_HEADER, false);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$params = '?command=update_camera'."&mac={$_REQUEST["mac"]}";
	if($_REQUEST["resolution"] <> "")
	{//jinho fixed for resolution0
    if($_REQUEST["resolution"]==0)
      $params.="&purpose=RVHI";
		else if($_REQUEST["resolution"]==1)
			$params.="&purpose=RVME";
		else if($_REQUEST["resolution"]==2)
			$params.="&purpose=RVLO";
		$set=1;
	}
	if($_REQUEST["package"] <> "")
	{
		$params.="&dataplan={$_REQUEST["package"]}";
		$set=1;
	}
	curl_setopt($ch, CURLOPT_URL, $web_address . $path . $params);
	if($set ==1 )
	{
		$result = curl_exec($ch);
	}
	$content=json_decode($result,true);
	if ($content['status'] != 'success') {
	    	print $content['error_msg'];
	}
	else {
	    	print 'Success';
	}




}
else
{
	print ' Please Enter Correct ID/ PWD !';
}
curl_close($ch);

