<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

 $api = API_EDIT_LOT;


$PageNo = trim($_POST["PageNo"]);
$SearchKey = trim($_POST["SearchKey"]);
$LoadAll = trim($_POST["LoadAll"]);

// $bodyContent = array(
//     'SearchKey' => $SearchKey,
//     'PageNo' => $PageNo,
//     'LoadAll' => $LoadAll
// );

$bodyContent=$_POST;
$resList = CallAPI($api, "GET", "", $bodyContent);

// var_dump($resList);
// echo json_encode($resList, JSON_PRETTY_PRINT);
// exit;
if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        // $PageCount = $resList->PageCount;
        // $resTotal = $resList->TotalCount;
        // $List = $resList->Data;
        // var_dump($resTotal);
        // exit;

// if (!isset($error) || empty($error)) { 
//   echo  json_encode($resList, JSON_PRETTY_PRINT);
//  } else {
//         echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : " . $error . "</div>";
//     }

 //   echo  json_encode($resList, JSON_PRETTY_PRINT);
} 
echo  json_encode($resList, JSON_PRETTY_PRINT);
}

?>