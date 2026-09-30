<?php

include_once '../Config/Config.php';
include_once 'Controller/GradingInspectionDetailsController.php';

$database = new Database();
$restApi = new REST();

$authToken = $restApi->getRequestHeader();
$RequestBody = $restApi->getRequestBody();
$RequestMethod = $restApi->get_request_method();

$db = $database->getConnection($authToken);
if ($db != null) {
    $Controller = new GradingInspectionDetailsController($db);

    if($RequestMethod=='GET'){
        if(isset($RequestBody['Type']) && $RequestBody['Type'] == "getLotDetails") {
            $itemCount = $Controller->getLotsDetails($RequestBody);
        }else if(isset($RequestBody['Type']) && $RequestBody['Type'] == "getReportLotDetails") {
            $itemCount = $Controller->getReportLotsDetails($RequestBody);
        }else if(isset($RequestBody['Type']) && $RequestBody['Type'] == "get") {
            $itemCount = $Controller->getGradingInspectionDetails($RequestBody);
        }else if(isset($RequestBody['Type']) && $RequestBody['Type'] == "getReportDetails") {
            $itemCount = $Controller->getGradingInspectionReportDetails($RequestBody);
        }
    else if (isset($RequestBody['Type']) && $RequestBody['Type'] == "getGrade") {
           $itemCount = $Controller->getGrade($RequestBody);
        
    // echo json_encode($result);
    // exit;
}
    }else if($RequestMethod=='POST'){
        if(isset($RequestBody['Type']) && $RequestBody['Type'] == "post" ) {
            $itemCount = $Controller->postGradingInspectionDetails($RequestBody);
        }else if (isset($RequestBody['Type']) && $RequestBody['Type'] == "update"){
            $itemCount = $Controller->updateGradingInspectionDetails($RequestBody);
        }
    }else if($RequestMethod=='DELETE'){
        $itemCount = $Controller->deleteGradingInspectionDetails($RequestBody);
    }

    $responseData['Error'] =$itemCount['Error'];
    $responseData['Msg'] = $itemCount['Msg'];
    $responseData['Data'] = $itemCount['Data'];
    $responseData = json_encode($responseData);
    $restApi->response($responseData, 200);

}
// if ($data['IsValid']) {
// } else {
//     $responseData['Error'] = $data['Error'];
//     $responseData['Msg'] = $data['Error'];
//     $responseData = json_encode($responseData);
//     $restApi->response($responseData, 200);
// }

// function validateData($authToken, $RequestBody)
// {
//     $isValid = false;
//     $error = null;

//     if (!isset($authToken) && empty($authToken)) {
//         $error = 'Token Not Found';
//     }else {
//         $isValid = true;
//     }

//     $Data['Error'] = $error;
//     $Data['IsValid'] = $isValid;

//     return $Data;

// }

?>