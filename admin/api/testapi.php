<?php
/****************
 *Validated on Jun-17,2016,
 * @ /var/www/qlync_admin/html/api           
 *Writer: JinHo, Chang
*****************/

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);//ignore invalid SSL
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //$url ="https://camera.vinaphone.vn:8080/html/api/getinfo_camera.php?id=root&pwd=1qazxdr5&mac=0050C2F53F31&type=activation_date";
$url ="https://camera.vinaphone.vn:8080/html/api/getinfo_camera.php?id=root&pwd=1qazxdr5&type=bind_mac_list";
//$url ="https://camera.vinaphone.vn:8080/html/api/getinfo_camera.php?id=root&pwd=1qazxdr5&type=online_mac_list";
$url ="https://camera.vinaphone.vn:8080/html/api/getinfo_camera.php?id=root&pwd=1qazxdr5&type=mac_list";
        curl_setopt($ch, CURLOPT_URL, $url);
        $result = curl_exec($ch);
        $result = str_replace("\n", '', $result); // remove new lines
        curl_close($ch);
//echo $result;
if ($result =="Success") echo "OK";
else if ($result =="Fail") echo "Error";
else if ($result =="-1") echo "NA";
else echo $result;
?>