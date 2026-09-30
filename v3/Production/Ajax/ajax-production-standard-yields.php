<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$methodType = trim($_POST["methodType"]);



if ($methodType == "post") {

    $api = API_PRODUCTION_STANDARD_YIELDS;

    $bodyContentPost = array(
        "YieldID" => trim($_POST["yieldID"]),
        "Species" => trim($_POST["species"]),
        "TypeProduct" => trim($_POST["typeProduct"]),
        "ProductForm" => trim($_POST["variety"]),
        "FreezingType" => trim($_POST["freezingType"]),
        "Treatment" => trim($_POST["treatment"]),
        "Yield" => trim($_POST["yield"]),
        "Status" => trim($_POST["status"]),
        "Grade" => trim($_POST["Grade"]),
        "Count" => trim($_POST["count"]),
        "TypeOfMaterial" => trim($_POST["typeofMaterial"]),
        "Type" => "formSave",

    );
    // echo json_encode($bodyContentPost, JSON_PRETTY_PRINT);
    // exit;
    $result = callAPI($api, "POST", "", $bodyContentPost);

    header('Content-Type: application/json');
    echo json_encode($result);

} else if ($methodType == "checkCombo") {

    $api = API_GRADE_MASTER_HL_COUNT;

    $bodyContentPost = array(
        "TypeProduct" => trim($_POST["typeProduct"]),
        "Preparation" => trim($_POST["preparation"]),
        "FreezingType" => trim($_POST["freezingType"]),
        "Type" => "checkCombo",
    );
    $result = callAPI($api, "POST", "", $bodyContentPost);
    header('Content-Type: application/json');
    echo json_encode($result);
}

// 	}
// }