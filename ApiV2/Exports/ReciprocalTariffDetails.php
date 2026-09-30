<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Config/Config.php';
include_once 'Controller/ReciprocalTariffController.php';

$database = new Database();
$restApi = new REST();

$authToken = $restApi->getRequestHeader();
$RequestBody = $restApi->getRequestBody();
$RequestMethod = $restApi->get_request_method();


    $db = $database->getConnection($authToken);
    if ($db != null) {
        $Controller = new ReciprocalTariffController($db);

        if ($RequestMethod == 'POST') {
            if(isset($RequestBody['methodType']) && $RequestBody['methodType'] == 'getPoNumbers'){
                $itemCount = $Controller->getPoNumbers($RequestBody);
            }else if(isset($RequestBody['methodType']) && $RequestBody['methodType'] == 'getReciprocalTariff'){
                $itemCount = $Controller->getReciprocalTariff($RequestBody);
            }else if(isset($RequestBody['methodType']) && $RequestBody['methodType'] == 'postReciprocalTariff'){
                $itemCount = $Controller->postReciprocalTariff($RequestBody);
            } else if(isset($RequestBody['methodType']) && $RequestBody['methodType'] == 'updateReciprocalTariff'){
                $itemCount = $Controller->updateReciprocalTariff($RequestBody);
            } else if(isset($RequestBody['methodType']) && $RequestBody['methodType'] == 'deleteReciprocalTariff'){
                $itemCount = $Controller->deleteReciprocalTariff($RequestBody);
            } else {
                $itemCount['Error'] = true;
                $itemCount['Msg'] = "Invalid method type";
                $itemCount['Data'] = [];
            }

            $responseData['Error'] = $itemCount['Error'] ? $itemCount['Error'] : false;
            $responseData['Msg'] = $itemCount['Msg'] ? $itemCount['Msg'] : "Failed";
            $responseData['Data'] = $itemCount['Data'] ? $itemCount['Data'] : [];
            $responseData = json_encode($responseData);
            $restApi->response($responseData, 200);
        }
    } else {
        $responseData['Error'] = $data['Error'];
        $responseData['Msg'] = $data['Error'];
        $responseData = json_encode($responseData);
        $restApi->response($responseData, 200);
    }

