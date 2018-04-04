<?php
/****************
 *Validated on Jan-7,2015,  List and Excel output OK
 *Writer: JinHo, Chang   
*****************/
require_once '_auth_.inc';
//global value  $ServerArray from dbutil.php
$CurrentDate = new DateTime();

$QUERY_TOTAL =0;
$CAM_ADDED_TOTAL =0;
$BILL_DATE = $CurrentDate->format('Y-m-d H:i:s'). "(" . date_default_timezone_get() . ")";
$BILL_SERVER = $oem." / ".getSiteName($oem);

$services = query();


function createServiceTable($services){
	$html = '';
	foreach($services as $service){
		$html.="\n<tr>";
		$html.="\n<td>".$service['Account']."</td>";
		$html.="\n<td>".$service['CameraMac']."</td>";
		$html.="\n<td>".$service['ActivationDate']."</td>";
		$html.="\n<td align=center>".$service['DaysStorage']."</td>";
		$html.="\n<td align=center>".$service['Resolution']."</td>";
		$html.="\n<td align=center>".$service['ServicePackage']."</td>";
		$html.="\n<td align=center>".$service['AccountAdded']."</td>";
    $html.="\n<td>".$service['Server']."</td>";
		$html.="</tr>";

	}
	echo $html;
}

?>

<html>
<head>
</head>
<body>
<font color="black">
			<table>
      <tr><td>Servers</td>
      <td><?php echo $BILL_SERVER;?></td>
      </tr>
      <tr><td>Date</td>
      <td><?php echo $BILL_DATE;?></td>
      </tr>
      <tr><td>Total Cameras</td>
      <td><?php echo $QUERY_TOTAL;?></td>
      </tr>
      <tr><td>Cameras added to Account</td>
      <td><?php echo $CAM_ADDED_TOTAL;?></td>
      </tr>

      </table>
			<table>
				<thead>
					<tr bgcolor="gray">
						<th>Account</th>
						<th>Camera MAC</th>
						<th bgcolor="#fac090">Activation Date</th>
						<th>Days of Storage</th>
						<th>Resolution</th>
						<th>Service Package</th>
						<th>Added to Account?</th>
						<th>Server</th>
					</tr>
				</thead>
				<tbody>
					<?php createServiceTable($services)?>
				</tbody>
			</table>
	</div>
</font>
</body>

</html>