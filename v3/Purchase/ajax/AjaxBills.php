<?php
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$methodType = trim($_POST["methodType"]);

$api = API_RM_BILLS;

if ($methodType == "get") {

    $ArrivalID = $_POST["ArrivalID"];

    $bodyContent = array(
        "ArrivalID" => $ArrivalID
    );

    $result = CallAPI($api, "GET", "", $bodyContent);


} else {

    $ArrivalID = trim($_POST["ArrivalID"]); // ArrivalID == Empty (Insert new) or Edit Arrival by ID
    $RmSupplierID = trim($_POST["RmSupplierID"]);
    $AgentID = trim($_POST["AgentID"]);
    $txtExtra = trim($_POST["txtExtra"]);
    $txtBillRemarks = trim($_POST["txtBillRemarks"]);
    $BillBreakup = trim($_POST["BillBreakup"]);

    $bodyContentPost = array(
        "ArrivalID" => $ArrivalID,
        "RmSupplierID" => $RmSupplierID, // For later purpose
        "AgentID" => $AgentID,
        "Extra" => $txtExtra,
        "BillRemarks" => $txtBillRemarks,
        "BillBreakup" => $BillBreakup
    );

    $result = CallAPI($api, "POST", "", $bodyContentPost);
}
if ($result->Error != null) {
    // echo var_dump($result); exit;
/* 
var_dump($result);
echo json_encode($result);
print_r($result->Error);
//var_dump($result->Error);
echo $error = $result->Error;
*/
}
$resArrivalData = $result->Data->ArrivalData;
$resBreakUpData = $result->Data->BreakUpData;
$resGrandTotal = isset($result->Data->GrandTotal) && !empty($result->Data->GrandTotal) ? $result->Data->GrandTotal : "";
$error = $result->Error;
$msg = $result->Data->Msg;
//exit;
$DeleteAccess = $result->DeleteAccess ? '' : 'disabled';

?>

<?php

if (!isset($error) || empty($error)) {
    $htmlResponse = ' 
            <table id="tblDataTable" class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>S.No</th>
                        <th>Variety</th>
                        <th>Count</th>
                        <th>Weight</th>
                        <th>Master Rate</th> 
                        <th>Harvest Com.</th>
                        <th>Rate</th>
                        <th>Total Value</th>
                        <!--<th>Actions</th>-->
                    </tr>
                    </thead>
                    <tbody>';
    $i = 1;
    foreach ($resBreakUpData as $item) {

        $deleteAccess = $result->DeleteAccess ? "disabled" : "";
        $MasterRate = $item->MasterRate;
        $MasterList = explode("/", $MasterRate);

        $htmlResponse .= '
                        <tr class="brkupArrivalBrkUpID" data-trArrivalBrkUpID="' . $item->ArrivalBrkUpID . '">
                            <td>' . $i . '</td>
                            <td class="brkupVariety">' . $item->Variety . '</td>
                            <td class="brkupCount"><input type="text" value="' . $item->Count . '" class="form-control" readonly /></td>
                            <td class="brkupWeight"><input type="text" value="' . $item->Weight . '"  class="form-control " readonly  /></td>
                            <td class="brkupMasterRate"> <input type="text" value="' . $item->MasterRate . '"  class="form-control " readonly />
                            <td><input type="text" value="' . $item->HarvestCommission . '"  class="form-control " readonly  /></td>
                               <!-- <div class="col-12 form-group">
                                    <select class="drpSelect " readonly>';
        //foreach ($MasterList as $list) {
        //$htmlResponse .= '<option>' . $list . '</option>';
        // }
        $htmlResponse .= '  </select>
                                </div> -->
                            </td>
                            <td class="brkupRate"><input type="number" value="' . $item->Rate . '"  class="form-control txtBrkupRate" /></td>
                            <td class="brkupBreakUpTotal"><input type="text" value="' . $item->BreakupTotalIncHC . '"  class="form-control " readonly /></td>
                            <!--
                            <td>    
                                <button type="button" id="btnDelete" name="btnDelete" data-ArrivalBrkUpID="' . $item->ArrivalBrkUpID . '" class="btn btn-link p-0 deleteBrkUp" ' . $DeleteAccess . ' >
                                    <i class="mdi mdi-delete font-size-16 text-danger"></i>
                                </button>
                            </td> 
                            -->
                        </tr>';
        $i++;
    }

    $htmlResponse .= '
                    </tbody>';
    $htmlResponse .= '
                    <tfoot>
                        <tr>
                            <td colspan="3"><span  style="font-weight: 600;font-size: 16px;">Grand Total</span></td> 
                            <td><span style="font-weight: 600;font-size: 16px;">' . $resGrandTotal->WeightTotal . '</span></td>
                            <td></td>
                            <td><span style="font-weight: 600;font-size: 16px;">' . $resGrandTotal->AvgRate . '</span></td>
                            <td><span style="font-weight: 600;font-size: 16px;">' . $resGrandTotal->ValueIncTdsTotal . '</span></td>
                            <!--<td></td>-->
                        </tr>
                    </tfoot>';
    $htmlResponse .= '
                </table>';
    $response = array('html' => $htmlResponse, 'json' => $resArrivalData, "Error" => $error, "Msg" => $msg);
} else {

    $response = array("Error" => $error);
}
echo json_encode($response);

?>