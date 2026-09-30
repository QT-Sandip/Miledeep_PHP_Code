<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Config/Config.php';
include_once 'Controller/EmpSalaryStractureListController.php';


$database = new Database();
$restApi = new REST();
$isValid=false;

$authToken = $restApi->getRequestHeader();
$RequestBody = $restApi->getRequestBody();
$RequestMethod = $restApi->get_request_method();

if($RequestMethod=='GET'){
    $data = validateGetData($authToken, $RequestBody);
}else{

}



if ($data['IsValid']) {
    $db = $database->getConnection($authToken);//$authToken
    if ($db != null) {
       // $payLoad=Token::Verify($authToken);
		//var_dump($payLoad);
       // $RequestBody['UserID']=$payLoad['UserID'];
        $Controller = new EmpSalaryStractureListController($db);
        if($RequestMethod=='GET'){
          
            $itemCount = $Controller->getEmpSalaryStractureList($RequestBody);
        }
       
       //var_dump($itemCount);
       
        $responseData['Error']=$itemCount['Error'];
        $responseData['Msg']= $itemCount['Msg'];
        $responseData['EditLink'] = 'create-salary-structure.php';
     //   $responseData['EditAccess'] = $itemCount['Data']['EditAccess'];
     //   $responseData['DeleteAccess'] = $itemCount['Data']['DeleteAccess'];   

       $responseData['Data'] = isset($itemCount['Data'])  ? $itemCount['Data']: null;        
        $responseData = json_encode($responseData);
		
		 //var_dump($responseData);
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

    $isValid = false;
    $error = null;

    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    }else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;

}

?>

