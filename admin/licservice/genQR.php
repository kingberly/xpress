<?php
/****************
 *Validated on Apr-27,2016,  
 * to generate QR code from text
 *Writer: JinHo, Chang
sed -i -e '/?>/ {
i\
if($_SESSION["ID_admin_oem"] or $_SESSION["ID_admin_qlync"]) {
i\
$turtorial[Tools][1][q]	="Plugin Tools:";
i\
$turtorial[Tools][1][a][]	="<a href='https://xpress.megasys.com.tw:8080/plugin/licservice/genQR.php'>Generate QR code</a>"; }
}' /var/www/qlync_admin/html/faq/turtorial_content.php
*****************/
require_once '_auth_.inc';

?>
<html>
<head>
<title>Geneate QR code</title>
</head>
<body>
<form id="genQRform">
<input type=text name=qrtext size=80 value='Test Me'>
<br>
<input type=button onclick="genQRPageByInput(this.form.qrtext.value);" value='generate QR' class="btn_1">
</form>
<script>
function genQRPageByInput(input)
{
  if (input=="") {
    alert("No Input!");
    return;
  }
    var data = encodeURIComponent(input);
    var cmd = "listLicense_camQR.php?data="+data;
    window.open(cmd, '_blank', config='height=300,width=250');
}
</script>
<?php
echo "<p/><p/><br><hr>";
include("urlshort.php");
?>
<!--/body>
</html-->