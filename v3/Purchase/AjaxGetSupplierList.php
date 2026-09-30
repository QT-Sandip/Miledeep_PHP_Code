<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
$ClientConfigData = (object)$_SESSION['ClientConfigData'];
$pancardduplicate = trim($ClientConfigData->pancardduplicate);

header('Content-Type: application/json');
$fromDate = $_GET['fromDate'] ?$_GET['fromDate']: '';
$sqlQuery = "SELECT  ID as SupplierID,FinancierName,PanNumber as PanNO FROM `RmSuppliers` ";
if (!empty($fromDate) && $fromDate >= '2026-04-01' && $pancardduplicate) {
    $sqlQuery .= " WHERE status = 1";
}
        $stmt = $iconn->query($sqlQuery);
        while ($dataRow = $stmt->fetch_array()) {
            $Data[] = $dataRow;
        }
echo json_encode([
    "status" => true,
    "data" => $Data
]);
exit;
?>
