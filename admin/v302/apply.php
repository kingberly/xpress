<?PHP

include("../../doc/config.php");
include("../../doc/mysql_connect.php");
include("../../doc/sql.php");
if($_REQUEST["step"]=="test")
{
	header("HTTP/1.1 301 Moved Permanently");
	header("Location: godwatch://{$oem}{$_REQUEST["scid"]}.app");
}
if($_REQUEST["step"]=="add")
{
//automatically add end user section
        $web_address = "http://{$api_id}:{$api_pwd}@{$api_path}";
        $path='/manage_user.php';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);


        # 1. Add a user account
        if(trim($_REQUEST["name"]) <> "" and trim($_REQUEST["mobile"])<> "" and trim($_REQUEST["email"])<> "")
        {
	$mobile_tmp=explode("-",$_REQUEST["mobile"]);
if (!isset($_REQUEST["cid"])) $_REQUEST["cid"]="G09";//jinho
        $params = array(
                'command'       =>'add',
                'pwd'          => substr($mobile_tmp[0],-4),
                'name'           =>"{$_REQUEST["mobile"]}",
                'reg_email'     =>"{$_REQUEST["email"]}",
                'group_id'      =>"{$_REQUEST["scid"]}3110021", // only open temple activity function
                //'oem_id'        =>"{$oem}" );
                'oem_id'        =>"{$_REQUEST["cid"]}" );

        $url = $web_address . $path . '?' . http_build_query($params);
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);
        $content=json_decode($result,true);
	curl_close($ch);
	print_r($content);


		if($content["status"]=='success') // bind camera
		{

			$sql="select id from isat.user where reg_email='{$_REQUEST["email"]}' limit 0,1";
			sql($sql,$result_user,$num_user,0);
			fetch($db_user,$result_user,0,0);
//			echo "123";
		        $web_address = "http://{$api_id}:{$api_pwd}@{$api_path}";
		        $path='/manage_user.php';
		        $ch = curl_init();
		        curl_setopt($ch, CURLOPT_HEADER, false);
		        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
		        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		        $params = array(
		                'command'       =>'add_metadata',
		                'id'          	=> $db_user["id"],
		                'field'           =>"sex",
		                'value'        	=>$_REQUEST["sex"] );

		        $url = $web_address . $path . '?' . http_build_query($params);
		        curl_setopt($ch, CURLOPT_URL, $url);
		        $result = curl_exec($ch);
		        $content=json_decode($result,true);



                        $params = array(
                                'command'       =>'add_metadata',
                                'id'            => $db_user["id"],
                                'field'           =>"address",
                                'value'         =>$_REQUEST["address"] );
                        $url = $web_address . $path . '?' . http_build_query($params);
                        curl_setopt($ch, CURLOPT_URL, $url);
                        $result = curl_exec($ch);
                        $content=json_decode($result,true);

                        $params = array(
                                'command'       =>'add_metadata',
                                'id'            => $db_user["id"],
                                'field'           =>"name",
                                'value'         =>$_REQUEST["name"] );
                        $url = $web_address . $path . '?' . http_build_query($params);
                        curl_setopt($ch, CURLOPT_URL, $url);
                        $result = curl_exec($ch);
                        $content=json_decode($result,true);

                        $params = array(
                                'command'       =>'add_metadata',
                                'id'            => $db_user["id"],
                                'field'           =>"birthday",
                                'value'         =>$_REQUEST["bd_y"]."-".$_REQUEST["bd_m"]."-".$_REQUEST["bd_d"] );
                        $url = $web_address . $path . '?' . http_build_query($params);
                        curl_setopt($ch, CURLOPT_URL, $url);
                        $result = curl_exec($ch);
                        $content=json_decode($result,true);

		        curl_close($ch);





			$sql="select cid,pid,mac,activated_code from isat.series_number,isat.license where left(mac,1)='V' and sid is null and license_id=license.id limit 0,1";
			sql($sql,$result_mac,$num_mac,0);
			fetch($db_mac,$result_mac,0,0);

		        $import_target_url = "http://{$api_id}:{$api_pwd}@{$api_path}/order.php";
			//    $import_data_array = array();
		                $import_data_array[] = array('mac' => "{$db_mac["mac"]}", 'ac' => "{$db_mac["activated_code"]}",'user' => "{$_REQUEST["mobile"]}");



			// $data_array[] = array('mac' => 'xxxxxx', 'ac' => 'yyyyyy');
			// ...
	                $data = array('action'=>'bind_device_order','data'=>json_encode($import_data_array),'cid' => "{$db_mac["cid"]}",'pid' => "{$db_mac["pid"]}");
			// CURL Post
		        $ch = curl_init();
		        curl_setopt($ch, CURLOPT_URL,$import_target_url);
		        curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            		curl_setopt($ch,CURLOPT_HTTPHEADER,array('Expect:'));

            		$result=curl_exec($ch);

			//              echo curl_error($ch);


			    // JSON Decoded Array to $content
		            $content=array();
		            $content=json_decode($result,true);
			//    var_dump($content);
		            curl_close($ch);
			if($content["status"]=='success')
			{
				echo "binding success!!";
			// cahnge the camera name
			        $web_address = "http://{$api_id}:{$api_pwd}@{$api_path}";
			        $path='/manage_device.php';
			        $ch = curl_init();
			        curl_setopt($ch, CURLOPT_HEADER, false);
			        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
			        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);


			        if(trim($db_mac["mac"]) <> "")
			        {
				        $mobile_tmp=explode("-",$_REQUEST["mobile"]);

				        $params = array(
				                'command'       =>'rename_device',
				                'new_name'      =>"History",
				                'mac_addr'      =>$db_mac["mac"] );


				        $url = $web_address . $path . '?' . http_build_query($params);
				        curl_setopt($ch, CURLOPT_URL, $url);
				        $result = curl_exec($ch);
				        $content=json_decode($result,true);
			        }
				curl_close($ch);

				//change the dataplan and recycle_day as live only wiht one day
		                $web_address = "http://{$api_id}:{$api_pwd}@{$api_path}/";
		                $path = 'manage_streamserver.php';
		                $ch = curl_init();
		                curl_setopt($ch, CURLOPT_HEADER, false);
		                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		                $params = array(
		                        'command'       => 'update_camera',
		                        'mac'           => $db_mac["mac"],
		                        'days'          => '1',
		                        'dataplan'      => 'LV'
		                );
		                $url = $web_address . $path . '?' . http_build_query($params);
		                curl_setopt($ch, CURLOPT_URL, $url);
		                $result = curl_exec($ch);
		                $content_r=json_decode($result,true);
		//              print_r($content_r);
		                curl_close();



			}

		}
                if($_REQUEST["cid"] <> "")
                {
                        header("HTTP/1.1 301 Moved Permanently");
                        header("Location: godwatch://{$_REQUEST["cid"]}{$_REQUEST["scid"]}.app");
                }
                else
                {
                        echo "<H1> Success!</H1>\n";
                }
	
        }
        else
        {
                echo "Account info cannot leave as blank";
        }
        curl_close();

/*
//adding the user metadata
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Expect:'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $path='/backstage_user.php';

        $params = array(
                'command'       =>'add',
                'pwd'          => substr($mobile_tmp[0],-4),
                'name'           =>"{$_REQUEST["mobile"]}",
                'reg_email'     =>"{$_REQUEST["email"]}",
                'group_id'      =>"{$_REQUEST["scid"]}3110021", // only open temple activity function
                'oem_id'        =>"{$oem}" );

        $url = $web_address . $path . '?' . http_build_query($params);
        curl_setopt($ch, CURLOPT_URL, $url);
//        $result = curl_exec($ch);
        $content=json_decode($result,true);
        }
        else
        {
                echo "Account info cna't leave as blank";
        }
        curl_close();

*/

	
}


if($_REQUEST["scid"] <> "")
{
        $sql="select * from qlync.scid where SCID='{$_REQUEST["scid"]}'";
        sql($sql,$result,$num,0);
        fetch($db,$result,0,0);
}
?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title>信眾註冊</title>
<link rel="stylesheet" type="text/css" href="/css/form.css">

<meta name="viewport" content="width=device-width, initial-scale=1.2, user-scalable=no, minimum-scale=1.0, maximum-scale=1.2" />
<meta http-equiv="Content-Language" content="utf-8">
<meta http-equiv="Content-Type" content="text/javascript; charset=utf-8">
<meta http-equiv="Page-Exit" content="revealTrans(Duration=4.0,Transition=2)">
<meta http-equiv="Site-Enter" content="revealTrans(Duration=4.0,Transition=1)">
<link href="/css/layout.css" rel="stylesheet" type="text/css"  charset="utf-8" />
<link href="/css/form.css" rel="stylesheet" type="text/css"  charset="utf-8" />
<link href="/css/menu.css" rel="stylesheet" type="text/css"  charset="utf-8" />
<link href="/css/nav.css" rel="stylesheet" type="text/css"  charset="utf-8" />
<script>
function hasWhiteSpace(email) {
  return /\s/g.test(email);
}
function validateEmail(email) {
    var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    return re.test(email);
}
function validateForm(myform) {

  if (myform.name.value=="") 
  {
    alert("姓名未輸入");  
    myform.name.focus();  
    return false;
  }
  if (/^[0-9]{10,13}$/.test(myform.mobile.value) === false) 
  {
    alert("手機號碼輸入不正確!");
    myform.mobile.style.borderColor = "red";  
    myform.mobile.focus();  
    return false;
  }  

  if ( (myform.bd_y.value=="") || (myform.bd_m.value=="") || (myform.bd_d.value=="") )
  {
    alert("出生年月日輸入不完全!");  
    myform.bd_y.focus();  
    return false;
  }
  if( (myform.email.value == "")|| hasWhiteSpace(myform.email.value))  
  {
    alert("電子郵件輸入不正確!");
    myform.mobile.style.borderColor = "red";  
    myform.email.focus();  
    return false;    
  }
  myform.submit();  
  return true;    
}
</script>
</head>
<body>

<?


echo "<div align=center>\n";
echo "<table>";
        echo "<form method=post action=".$_SERVER['PHP_SELF'].">\n";
        echo "<tr class=topic_main>\n";
                echo "<td><H2>{$db["Name"]}-個人資料</td>\n";
        echo "</tr>\n";
        echo "<tr>\n";
                echo "<td><H2>姓名\n";
                echo "<input type=text name=name size=6></td>\n";
        echo "</tr>\n";
        echo "<tr>\n";
                echo "<td><H2>性別\n";
                //echo "<H2>\n";
                echo "<label><input type=radio name=sex value='0'>女</label>";
                echo "<label><input type=radio name=sex value='1' checked>男</label>";
                echo "</td>\n";
        echo "</tr>\n";
        echo "<tr>\n";
                echo "<td><H2>手機號碼\n";
                echo "<input type=text name=mobile placeholder='必填' size=10></td>\n";
        echo "</tr>\n";
        echo "<tr>\n";
                echo "<td><H2>出生年月日</td></tr><tr>\n";
                echo "<td>\n";
                echo "<H2>民國<input type='text' name='bd_y' size='3' placeholder='yyy' value=''>年";
                echo "<input type='text' name='bd_m' size='1' placeholder='mm' value=''>月";
                echo "<input type='text' name='bd_d' size='1' placeholder='dd' value=''>日";
                echo "</td>\n";
        echo "</tr>\n";
        echo "<tr>\n";
                echo "<td><H2>地址</td></tr><tr>\n";
                echo "<td><input type=text name=address></td>\n";
        echo "</tr>\n";
        echo "<tr>\n";
                echo "<td><H2>電子郵件</td></tr><tr>\n";
                echo "<td><input type=text name=email placeholder='必填,可填手機號碼'></td>\n";
        echo "</tr>\n";
        echo "<tr>\n";
                //echo "<td><H2>功能</td>\n";
                echo "<td>\n";
                echo "<input type=hidden name=step value=add>\n";
                echo "<input type=button class=btn_1 value='註冊送出' onclick='validateForm(this.form)'>\n";
                echo "<br><font color=red>帳號為手機號碼, 密碼為號碼末四碼</font>";
                echo "<input type=hidden name=scid value='{$_REQUEST["scid"]}'>\n";
        echo "</tr>\n";
        echo "</form>\n";

echo "</table>\n";
//echo "<a href=\"apply.php?step=test\">Test link</a>";
?>

