<?PHP
include_once("/var/www/qlync_admin/header.php");
include_once("/var/www/qlync_admin/menu.php");
//http://workeyemap.megasys.com.tw/map/rpic_map.php?key=KZo3i6UJbKd0bb6B5Suv
if ($_SESSION["ID_qlync"] <> "")  $key="KZo3i6UJbKd0bb6B5Suv";
else die("Please Login");

?>
<script>
window.open("http://workeyemap.megasys.com.tw/map/rpic_map.php?key=KZo3i6UJbKd0bb6B5Suv",'MAP');
/* //Access-Control-Allow-Origin
var xhr = new XMLHttpRequest();
url="http://workeyemap.megasys.com.tw/map/rpic_map.php";
var params = "key=KZo3i6UJbKd0bb6B5Suv";
xhr.open("POST", url, true);
xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded; charset=UTF-8");
xhr.send(params);
/*xhr.onreadystatechange = function() {//Call a function when the state changes.
    if(xhr.readyState == 4 && xhr.status == 200) {
        alert(xhr.responseText);
    }
}
*/
</script>
<br><br><br><br>
<p>
<h3>If Map does not Pop-up, Please click <a href="http://workeyemap.megasys.com.tw/map/rpic_map.php?key=KZo3i6UJbKd0bb6B5Suv" target=_blank>WorkeyMAP</a>.
</p>
</ul>
</body>
</html>
