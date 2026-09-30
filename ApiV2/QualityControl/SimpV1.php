<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Config/Config.php';
include_once 'Controller/SimpV1Controller.php';

$database = new Database();
$restApi = new REST();
$authToken = $restApi->getRequestHeader();
$RequestBody = $restApi->getRequestBody();
$data = validateData($authToken, $RequestBody);

if ($data['IsValid']) {
    $db = $database->getConnection($authToken);
    if ($db != null) {
        $Controller = new SimpV1Controller($db);

        $itemCount = $Controller->getSimpDetails($RequestBody);
        $ItemExporterCount = $Controller->getExporterData($RequestBody);
        $responseData['Error'] = null;
        $responseData['Msg'] = 'Success';
        $BLDate = $ItemExporterCount['BLDate'];
        $BLDate = date("m/d/Y", strtotime($BLDate));
        //$responseData['ItemExporterCount'] = $itemCount['Data'];
        $responseData['ExpName'] = $ItemExporterCount['ExpName'];
        $responseData['ProcessorName'] = $ItemExporterCount['ExpName'];
        $responseData['ExpPhoneNumber'] = $ItemExporterCount['ExpPhoneNumber'];
        $responseData['PackerAddress'] = $ItemExporterCount['ImporterRecordAddress'];
        $responseData['ExpAddress'] = $ItemExporterCount['ExpAddress'];
        $responseData['ImporterRecordAddress'] = $ItemExporterCount['ImporterRecordAddress'];
        $responseData['ExpApprovalNo'] = $ItemExporterCount['ExpApprovalNo'];
        $responseData['ExpUsfdaNo'] = $ItemExporterCount['ExpUsfdaNo'];
        $responseData['ExpFax'] = $ItemExporterCount['ExpFax'];
        $responseData['ExpMailID'] = $ItemExporterCount['ExpMailID'];
        $responseData['VesselName'] = $ItemExporterCount['VesselName'];
        $responseData['VesselNO'] = $ItemExporterCount['VOYNO'];
        $responseData['NetWeightKgs'] = number_format($ItemExporterCount['TotalNetWt'], 3);
        $responseData['ContainerNo'] = $ItemExporterCount['ContainerNo'];
        $responseData['LoadingDate'] = $BLDate;


        $responseData['RecipientName'] = $ItemExporterCount['RecipientName'];
        $responseData['InvNumber'] = $ItemExporterCount['CustomsInvNo'];
        $responseData['ConsigneeName'] = $ItemExporterCount['ConsigneeName'];
        $responseData['PoNumber'] = $ItemExporterCount['PoNo'];
        $responseData['ConsigneeIFTPCODE'] = $ItemExporterCount['ConsigneeIFTPCODE']; //doubt
        $responseData['BillOfLading'] = $ItemExporterCount['BLNo']; //doubt

        $responseData['Data'] = $itemCount['Data'];
        $responseData['VarietyData'] = $itemCount['QcBreakUpDetails'];
        $responseData['FileName'] = $ItemExporterCount['FileName'];


        //$responseData['Data'] = $itemCount['Data'];
        $responseData = json_encode($responseData);
        $restApi->response($responseData, 200);

    }
} else {
    $responseData['Error'] = $data['Error'];
    $responseData['Msg'] = $data['Error'];
    $responseData = json_encode($responseData);
    $restApi->response($responseData, 200);
}

function validateData($authToken, $RequestBody)
{
    $isValid = false;
    $error = null;

    if (!isset($authToken) && empty($authToken)) {
        $error = 'Token Not Found';
    } else if (!isset($RequestBody['OrderID']) || empty($RequestBody['OrderID'])) {
        $error = 'OrderID Not Found';
    } else {
        $isValid = true;
    }

    $Data['Error'] = $error;
    $Data['IsValid'] = $isValid;

    return $Data;

}

?>