<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once '../Config/Config.php';
include_once 'Controller/AuthLoginController.php';

$database = new Database();
$restApi = new REST();
$db = $database->getAuthConnection();
$LoginController = new AuthLoginController($db);

 $RequestBody = $restApi->getRequestBody();

 $data = validateData($RequestBody);
 //echo $isValid=$data['isValid'];

if ($data['IsValid']) {
    $reqMethod = $restApi->get_request_method();
    $json = $restApi->getRequestHeader();
    $itemCount = $LoginController->postAuthorization($RequestBody);
    $responseData['Error']= null;
    $responseData['Data']= $itemCount;
    $responseData= json_encode($responseData);
    $restApi-> response($responseData,200);
} else {
    $responseData['Error']=$data['Error'];
    $responseData['Msg']=$data['Error'];
    $responseData=json_encode($responseData);
    $restApi-> response($responseData,200);
}

function validateData($RequestBody)
{
    $isValid = false;
    $error=null;

    if (!isset($RequestBody['LoginID']) && empty($RequestBody['LoginID'])) {
        $error = 'User Name Required';
    } else if (!isset($RequestBody['Password']) && empty($RequestBody['Password'])) {
        $error = 'Password Required';
    } else if (!isset($RequestBody['IpAddress'])  && empty($RequestBody['IpAddress'])) {
        $error = 'UnAuthrised Access';
    } else {
        $isValid = true;
    }

    $Data['Error']=$error;
    $Data['IsValid']=$isValid;
   
    return $Data;

}





/*
 if($itemCount > 0){       
        $employeeArr = array();
        $employeeArr["body"] = array();
        $employeeArr["itemCount"] = $itemCount;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)){
            extract($row);
            $e = array(
                "id" => $id,
                "name" => $name,
                "email" => $email,
                "age" => $age,
                "designation" => $designation,
                "created" => $created
            );
            array_push($employeeArr["body"], $row);
        }
        echo json_encode($employeeArr);
    }else{
        http_response_code(404);
        echo json_encode(
            array("message" => "No record found.")
        );
    }
    
    */
?>