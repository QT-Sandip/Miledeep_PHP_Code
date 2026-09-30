<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");



$Country = $_POST['CountryId'];
if ($Country == "") {
	$sql = "SELECT * FROM ScrmPortCities where 1=1 ORDER BY Name ASC";

} else {
	$sql = "SELECT * FROM ScrmPortCities where Country='$Country' ORDER BY Name ASC";

}
$stmt = $iconn->query($sql);

echo '<option value="">Select Port</option>';

while ($Data = $stmt->fetch_array()) {


	echo '<option value="' . $Data['PortId'] . '">' . $Data['Name'] . '</option>';
}





?>