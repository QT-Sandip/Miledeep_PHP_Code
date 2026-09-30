<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Config/Config.php';
include_once 'Controller/RmArrivalBalanceWeightController.php';

$database = new Database();
$restApi = new REST();
$isValid=false;

$authToken = $restApi->getRequestHeader();
$RequestBody = $restApi->getRequestBody();
$RequestMethod = $restApi->get_request_method();
$payload = Token::Verify($authToken);
if (is_array($payload)) {
    $RequestBody['AuthPayload'] = $payload;
    $RequestBody['AuthUserID'] = isset($payload['UserID']) ? $payload['UserID'] : null;
}

if($RequestMethod=='GET'){
    $data = validateGetData($authToken, $RequestBody);
}else{

}



if ($data['IsValid']) {
    $db = $database->getConnection($authToken);
    if ($db != null) {
        // $Controller = new RmArrivalController($db);
        $Controller = new RmArrivalBalanceWeightController($db);
        if($RequestMethod=='GET'){
            $itemCount = $Controller->getArrivalDetails($RequestBody);
        }else if($RequestMethod=='POST'){
            $itemCount = $Controller->postArrivalDetails($RequestBody);
        }else if($RequestMethod=='DELETE'){
            $itemCount = $Controller->postDeleteArrivalBrkUP($RequestBody);
        }
       
        $responseData['Error']=$itemCount['Error'];
        $responseData['Msg']= $itemCount['Msg'];
        $responseData['EditLink'] = '#';
        $responseData['EditAccess'] = true;
        $responseData['DeleteAccess'] = true;
        $responseData['BreakUpData'] = isset($itemCount['BreakUpData'])  ? $itemCount['BreakUpData']: null;
        $responseData['ArrivalData'] = isset($itemCount['ArrivalData'])  ? $itemCount['ArrivalData']: null;
        $responseData['GrandTotal'] = isset($itemCount['GrandTotal'])  ? $itemCount['GrandTotal']: null;
        $responseData = json_encode($responseData);
        $restApi->response($responseData, 200);
       
    }
} else {
    $responseData['Error'] = $data['Error'];
    $responseData['Msg'] = $data['Error'];
    $responseData = json_encode($responseData);
    $restApi->response($responseData, 200);
}





function validateGetData($authToken, $RequestBody)
{

 //   var_dump($RequestBody);

 //echo $RequestBody['GraderID'];
    $isValid = false;
    $error = null;

    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    }
    
//     else if( $RequestBody['OverallArrivalID']==''){

//       if (!isset($RequestBody['CenterID']) || empty($RequestBody['CenterID'])) {
//         $error = 'Center Details not Found';
//     } else  if (!isset($RequestBody['PlantID']) || empty($RequestBody['PlantID'])) {
//         $error = 'Plant Details not Found';
//     }else  if (!isset($RequestBody['Species']) || empty($RequestBody['Species'])) {
//         $error = 'Species  not Found';
//     }  else  if (!isset($RequestBody['ArrivalDate']) || empty($RequestBody['ArrivalDate'])) {
//         $error = 'Arrival Date not Found';
//     }  
// }

    else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;

}

?>

