<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$methodType = trim($_POST["methodType"]);

if (isset($methodType) && !empty($methodType)) {

    $api = API_TAX_CHARGES;

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

            "ExpTRID" => trim($_POST["expTRID"]),
            "ExpID" => trim($_POST["exporter"]),
            // "CountryID" => trim($_POST["country"]),
            "AntiDumpingDuty" => trim($_POST["antiDumpDuty"]),
            "ClearanceChargePercentage" => trim($_POST["clearanceChargePercentage"]),
            "MerchandiseProcessFee" => trim($_POST["merchandiseProcessFee"]),
            "HarbourMaintenanceFee" => trim($_POST["harbourMaintenanceFee"]),
            "CountervailingDuty" => trim($_POST["countervailingDuty"]),
            "ServiceTaxPercentage" => trim($_POST["serviceTaxPercentage"]),
            "DrawbackRateperKG" => trim($_POST["drawbackRateperKG"]),
            "DrawbackPercentage" => trim($_POST["drawbackPercentage"]),
            "BLServiceTax" => trim($_POST["blServiceTax"]),
            "ISFStaticDuty" => trim($_POST["isfStaticDuty"]),
            "InsurancePremiumRate" => trim($_POST["insurancePremiumRate"]),
            "UsClearance" => trim($_POST["usClearance"]),
            "IGSTRaw" => trim($_POST["iGSTRaw"]),
            "IGSTBrainFrozen" => trim($_POST["iGSTBrainFrozen"]),
            "IGSTCooked" => trim($_POST["iGSTCooked"]),
            "IGSTCookedInShell" => trim($_POST["iGSTCookedInShell"]),
            "IGSTBlanched" => trim($_POST["iGSTBlanched"]),
            "FromDate" => trim($_POST["fromDate"]),
            "ReciprocalTariffs" => trim($_POST["reciprocalTariffs"]),
            "ClearingCharges" => trim($_POST["clearingCharges"]),
            "Tariff" => trim($_POST["tariff"]),
            // "PrintTariff" => trim($_POST["printTariff"]),
            // "PrintReciprocalTariff" => trim($_POST["printReciprocalTariff"]),
            "OtherTax" => trim($_POST["otherTax"]),


            // "UptoDate" => trim($_POST["uptoDate"]),

        );
        $result = callAPI($api, "POST", "", $bodyContentPost);

        // echo json_encode($result, JSON_PRETTY_PRINT);
        // exit;
        header('Content-Type: application/json');
        echo json_encode($result);
    }
}
