<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include("../Includes/IncludePage.php");

$ExporterID = $_POST['ExporterID'];

if ($ExporterID != '') {

	$ExporterTaxRates = getExporterTaxRates($iconn, $ExporterID);

	$Data['Error'] = null;
	$Data['Msg'] = 'Success';
	$Data['ExporterTaxRates'] = $ExporterTaxRates;
	// $Data['EditMode']='T';
	echo json_encode($Data);
} else {
	$Data['Error'] = 'Unable to load Exporter Tax Rates';
	$Data['Msg'] = '';
	echo json_encode($Data);
}

?>