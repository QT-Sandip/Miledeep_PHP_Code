<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$methodType = trim($_POST["methodType"]);

if (isset($methodType) && !empty($methodType)) {

    $api = API_PO_TAX_CHARGES;

    if ($methodType == "getData") {
        $bodyContent = [
            "Exporter" => trim($_POST["exporter"]),
            "FromDate" => trim($_POST["fromDate"]),
            "PageNo" => 1,
        ];

        $result = callAPI($api, "GET", "", $bodyContent);
        // echo json_encode($result, JSON_PRETTY_PRINT);
        // exit;
        header('Content-Type: application/json');
        echo json_encode($result);
    } else if ($methodType == "postData") {

        $bodyContentPost = array(
            "PoID" => trim($_POST["PoID"]),
            "InvoiceType" => trim($_POST["InvoiceType"]),
            "RevisedPo" => trim($_POST["RevisedPo"]),
            "PoDate" => trim($_POST["PoDate"]),
            "ExpID" => trim($_POST["Exporter"]),
            "ClearanceCharge" => trim($_POST["ClearanceChargePercentage"]),
            "AntiDumpingDuty" => trim($_POST["AntiDumpDuty"]),
            "MerchandiseProcessFee" => trim($_POST["MerchandiseProcessFee"]),
            "HarbourMaintenanceFee" => trim($_POST["HarbourMaintenanceFee"]),
            "CountervailingDuty" => trim($_POST["CountervailingDuty"]),
            "ServiceTaxPercentage" => trim($_POST["ServiceTaxPercentage"]),
            "DrawbackRateperKG" => trim($_POST["DrawbackRateperKG"]),
            "DrawbackPercentage" => trim($_POST["DrawbackPercentage"]),
            "BLServiceTax" => trim($_POST["BLServiceTax"]),
            "ISFStaticDuty" => trim($_POST["ISFStaticDuty"]),
            "InsurancePremiumRate" => trim($_POST["InsurancePremiumRate"]),
            "UsClearance" => trim($_POST["UsClearance"]),
            "RevisedReciprocalTariff" => trim($_POST["ReciprocalTariffs"]),
            "ClearingCharges" => trim($_POST["ClearingCharges"]),
            "OtherTax" => trim($_POST["OtherTax"]),
            "Tariff" => trim($_POST["Tariff"]),
            "PrintTariff" => trim($_POST["PrintTariff"]),
            "PrintReciprocalTariff" => trim($_POST["PrintReciprocalTariff"]),
        );

        $result = callAPI($api, "POST", "", $bodyContentPost);

        header('Content-Type: application/json');
        echo json_encode($result);
    } else if ($methodType == "getPoTaxDetails") {
        $bodyContent = [
            "PoID" => trim($_POST["PoID"]),
            "InvoiceType" => trim($_POST["InvoiceType"]),
        ];
        $result = callAPI($api, "GET", "", $bodyContent);
        header('Content-Type: application/json');
        echo json_encode($result);
    }
}
