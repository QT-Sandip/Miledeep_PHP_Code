<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$methodType = trim($_POST["methodType"]);

if (isset($methodType) && !empty($methodType)) {
    // echo
    $api = API_CREATE_PPC_STATIONS;

    if ($methodType == "get") {

        $StationID = trim($_POST['stationID']);

        $bodyContentGet = array(
            "StationID" => $StationID
        );

        $result = CallAPI($api, "GET", "", $bodyContentGet);
    } else if ($methodType == "post") {

        $StationID = trim($_POST["stationID"]);
        $UnitName = trim($_POST["unitName"]); 
        $UnitLocation = trim($_POST["unitLocation"]);
        $UnitStatus = trim($_POST["unitStatus"]); 
        $ExtraPercPerKgs = trim($_POST["extraPercPerKgs"]);
        $ExtraAmountPerPerson = trim($_POST["extraAmountPerPerson"]);

        $bodyContentPost = array(
            "StationID" => $StationID, 
            "UnitName" => $UnitName, 
            "UnitLocation" => $UnitLocation, 
            "UnitStatus" => $UnitStatus,  
            "ExtraPercPerKgs" => $ExtraPercPerKgs, 
            "ExtraAmountPerPerson" => $ExtraAmountPerPerson 
        );

        $result = CallAPI($api, "POST", "", $bodyContentPost);
    }

    $error = $result->Error;
    $msg = $result->Msg;

    if (!isset($error) || empty($error)) {

        $List = $result->Data->Data;
        if (isset($List) && !empty($List)) {
            echo "<script>alert('$msg');</script>";
            echo '<script>window.location.href = "ppc-stations-list.php";</script>';
            exit;
        }
    } else {
        echo "<div class='text-center mt-3 mb-3 text-bold'>NOTE : " . $error . "</div>";
    }
}
?>
