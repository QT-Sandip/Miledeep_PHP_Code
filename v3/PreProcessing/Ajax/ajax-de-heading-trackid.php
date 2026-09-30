<?php

session_start();
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// SESSION VALUES 
$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
$ShowDeheadingStationID = $ArrayClientConfigData['ShowDeheadingStationID'];
$mergeHonLoose = $ArrayClientConfigData['mergeHonLoose'];
$ShowTotalDeheadingWeight = $ArrayClientConfigData['ShowTotalDeheadingWeight'];
$DeHeadingBreakup = $ArrayClientConfigData['DeHeadingBreakup'];
$CheckingCount2 = $ArrayClientConfigData['CheckingCount2'];

// $DeheadingPlantID = $_SESSION['UnitID'];

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$methodType = trim($_POST["methodType"]);

function fnDataTableList($result)
{
    global $ShowTotalDeheadingWeight,$DeHeadingBreakup, $CheckingCount2;

    // echo json_encode($result, JSON_PRETTY_PRINT);
    // exit;

    $resList = $result->DeheadingBreakUp->DeheadingData;
    $resTotals = $result->DeheadingBreakUp->Totals;
    $resDeheadingDetails = $result->DeheadingDetails;
    $error = $result->Error;
    $msg = $result->Msg;
    $YieldLableText = 'Yield';
    if($CheckingCount2 == 1){
        $YieldLableText = 'Count Net Yield';
    }

    // <thead class="thead-light">

    $html =
        '<div class="card">
        <div class="card-body">
        <table id="tblDataTable" class="table table-hover table-bordered">
            <thead>
                <tr>
                    <th>S.No</th>
                    <th>Bill No</th>
                    <th>Variety</th>
                    <th style="min-width:70px">Count</th>
                    <th style="min-width:70px">Weight</th>
                    <th>Sample HO Qty</th> 
                    <th>Count/Noof Pcs</th>
                    <th>Sample HL Qty / Body Yield</th>'.(
                        (($CheckingCount2 == 1)?'<th>Checking Count-2</th>':'').
                        (($CheckingCount2 == 1)?'<th>HON Quantity(Kgs)</th>':'').
                        (($ShowTotalDeheadingWeight)?'<th>Total Deheading Weight</th>':'')
                    ).'<th>'.$YieldLableText.'</th>'.(
                        ($CheckingCount2 == 1)?'<th>HL Yield</th>':''
                    ).'<th>Actions</th>
                </tr>
            </thead>
            <tbody>';
    foreach ($resList as $item) {
        if(!$item->DeheadingBreakUpID || $item->HLQuantityKgs == 0){
            $item->EditAccess = 1;
        }
        $html .=
            '<tr>
                    <td>' . $item->SNo . '</td>
                    <td>' . $item->LotNo . '</td>
                    <td>' . $item->OverallVariety . '</td>
                    <td><input type="text"class="form-control" id="txtCount" name="txtCount" readonly value="' . $item->ArrivalCount . '" /></td>
                    <td><input type="text" class="form-control" id="txtWeight" name="txtWeight" readonly value="' . $item->ArrivalWeight . '" /></td>
                    <td><input type="number" min="0" step="0.001" class="form-control txthonQty" id="txthonQty" name="txthonQty" value="' . ($item->HonQuantity == 0 ? ' ' : $item->HonQuantity) . '" /></td>
                    <td><input type="number" min="0"  class="form-control txthlCount" id="txthlCount" name="txthlCount" value="' . ($item->HLCount == 0 ? ' ' : $item->HLCount) . '" /></td>
                    <td><input type="number" min="0"  class="form-control txthlQty" id="txthlQty" name="txthlQty" value="' . ($item->HLQuantityKgs == 0 ? ' ' : $item->HLQuantityKgs) . '" /></td>'.
                    (
                        (
                            ($CheckingCount2 == 1)?'<td>
                                                        <input type="number" min="0"  class="form-control txtCheckingCount2" id="txtCheckingCount2" name="txtCheckingCount2" value="' . ($item->CheckingCount2 == 0 ? ' ' : $item->CheckingCount2) . '" />
                                                    </td>':''
                        ).
                        (
                            ($CheckingCount2 == 1)?'<td>
                                                        <input type="number" class="form-control txtHONQuantity" id="txtHONQuantity" pattern="^\d*(\.\d{0,2})?$" name="txtHONQuantity"  value="' . ($item->HonQty == 0 ? ' ' : $item->HonQty) . '">
                                                    </td>':''
                        ).
                        (
                            ($ShowTotalDeheadingWeight)?'<td>
                                                            <input type="number" min="0"  class="form-control txtTotalDeheadingWeight" id="txtTotalDeheadingWeight" name="txtTotalDeheadingWeight" value="' . ($item->TotalDeheadingWeight == 0 ? ' ' : $item->TotalDeheadingWeight) . '" />
                                                        </td>':''
                        )
                    ).'<td><input type="text"  class="form-control" id="txtYield" name="txtYield" readonly value="' . $item->Yield . '" /></td>'.(
                        ($CheckingCount2 == 1)?'<td><input type="text"  class="form-control" id="txtHLYield" name="txtHLYield" readonly value="' . $item->HLYield . '" /></td>':''
                    ).'
                    <td>';
        if (isset($item->DeheadingBreakUpID) && !empty($item->DeheadingBreakUpID)) {
            $display = "show";
        } else {
            // $display = "hide";
            $display = "show";
        }
        if ($item->EditAccess == 1) {

        if (isset($item->DeheadingBreakUpID) && !empty($item->DeheadingBreakUpID)) {
            $html .=
                '<button class="mr-3 text-primary  btnActions btnUpdateRow" data-placement="top" title="Update BreakUP" 
                    style=""
                    data-deheading-breakupid="' . $item->DeheadingBreakUpID . '" data-arrival-id="' . $item->ArrivalID . '" data-recv-breakup-id="' . $item->ReceivalBreakUpID . '" 
                    data-original-title="Edit" data-editaccess="' . $item->EditAccess . '">
                    <i class="btn-primary fa fa-save font-size-16"></i> <!-- pencil -->
                </button>';
        } else {
            $html .=
                '<button class="mr-3 text-primary btnActions btnSaveRow" data-placement="top" title="ADD BreakUP" 
                    style=""
                    data-deheading-breakupid="' . $item->DeheadingBreakUpID . '" data-arrival-id="' . $item->ArrivalID . '" data-recv-breakup-id="' . $item->ReceivalBreakUpID . '" data-original-title="Edit">
                    <i class="btn-primary fa fa-save font-size-16"></i> <!-- plus -->
                </button>';
        }
        }
        if($DeHeadingBreakup == 1 && !empty($item->DeheadingBreakUpID ) && ($item->EditAccess == 1 || $item->DeleteAccess == 1)){            
            $Q = "dhid= $item->DeheadingBreakUpID";
            $html .=
                    '<a href="de-heading-trackid-breakup.php?Q=' . base64_encode($Q) . '" target="_blank" class="mr-3 text-success" data-placement="top" title="ADD De-Heading BreakUP" 
                        style=""
                        data-deheading-breakupid="' . $item->DeheadingBreakUpID . '" data-arrival-id="' . $item->ArrivalID . '" data-recv-breakup-id="' . $item->ReceivalBreakUpID . '" data-original-title="Plus">
                        <i class="btn-success fa fa-plus font-size-16"></i> <!-- plus -->
                    </a>';
        }else if(!empty($DeHeadingBreakup)){
            $Q = "dhid= $item->DeheadingBreakUpID&type=view";
            $html .=
                    '<a href="de-heading-trackid-breakup.php?Q=' . base64_encode($Q) . '" target="_blank" class="mr-3 text-success" data-placement="top" title="View De-Heading BreakUP" 
                        style=""
                        data-deheading-breakupid="' . $item->DeheadingBreakUpID . '" data-arrival-id="' . $item->ArrivalID . '" data-recv-breakup-id="' . $item->ReceivalBreakUpID . '" data-original-title="Eye">
                        <i class="btn-success fa fa-eye font-size-16"></i> <!-- plus -->
                    </a>';
        }
        if ($item->DeleteAccess == 1) {

        $html .=
            '<button class="btn btn-link text-danger btnActions btnDelete" style="position: relative;top: -3px;" data-breakup-id="' . $item->DeheadingBreakUpID . '"  
                data-toggle="tooltip" data-placement="top" title="Delete" value="" data-original-title="Delete" >
                <i class="mdi mdi-trash-can font-size-16"></i></button>';
        }
        $html .=
            '</td>
        </tr>';
    }
    $html .= '
            </tbody>
            <tfoot>
                <tr>
                    <td></td>
                    <td>Totals</td>
                    <td></td>
                    <td></td>
                    <td>' . $resTotals->TotalArrivalWeight . '</td>
                    <td>' . $resTotals->TotalHOnQuantity . '</td>
                    <td></td>
                    <td>' . $resTotals->TotalHLQuantity . '</td>'.(
                        (($CheckingCount2 == 1) ? '<td></td>
                                                    <td></td>' : '').
                        (($ShowTotalDeheadingWeight)?'<td></td>':'')
                    ).'<td>' . $resTotals->AvgYield . '</td>'.(
                        (($CheckingCount2 == 1) ? '<td></td>' : '')
                    ).'<td></td>
                </tr>
            </tfoot>
        </table>
        </div>
        </div>';

    $response = array('htmlRes' => $html, 'FormData' => $resDeheadingDetails, 'Error' => $error, 'Msg' => $msg);

    return $response;
}

if (isset($methodType) && !empty($methodType)) {

    $api = API_DE_HEADING_TRACKING_DETAILS;

    if ($methodType == "getBreakUp") {

        $bodyContentPost = array(
            "TrackID" => trim($_POST["trackID"]),
            "ArrivalDate" => trim($_POST["arrivalDate"]),
            "PlantID" => trim($_POST["plantID"]),
            "IsHONPacking" => trim($_POST["IsHONPacking"]),
            "mergeHonLoose" => $mergeHonLoose,
        );
        // echo json_encode($bodyContentPost, JSON_PRETTY_PRINT);
        $result = CallAPI($api, "GET", "", $bodyContentPost);
       // echo '<pre>';print_r($result);die;
        // echo json_encode($result, JSON_PRETTY_PRINT);
        // exit;

        if (!isset($error) || empty($error)) {

            header('Content-Type: application/json');
            echo json_encode(fnDataTableList($result));
        }
    } else if ($methodType == "formPost") {

        if ($ShowDeheadingStationID == 0) {
            $Center = "0";
        } else {
            $Center = trim($_POST["center"]);
        }
        $bodyContent = array(

            "TrackID" => trim($_POST["trackCodeID"]),
            "Plant" => trim($_POST["productionPlant"]),
            "Center" => $Center,
            "TransactionDate" => trim($_POST["transactionDate"]),
            "Shift" => trim($_POST["shift"]),
            "Supervisor" => trim($_POST["superVisor"]),
            "GradingPlantID" => trim($_POST["gradingPlantID"]),
            "Remarks" => trim($_POST["remarks"]),
            "Type" => "",
            "IsPlantFilter" => trim($_POST["IsPlantFilter"]),
            "mergeHonLoose" => $mergeHonLoose,
             'DeHeadingBreakup' => $DeHeadingBreakup,
        );
        // echo 'Center Code : '.$Center;
        // echo 'SESSION ID ' . $ShowDeheadingStationID;
        // echo json_encode($bodyContent, JSON_PRETTY_PRINT);
        // exit;

        $result = CallAPI($api, "POST", "", $bodyContent);
        //echo '<pre>';print_r($result);die;
        header('Content-Type: application/json');
        // echo json_encode(fnDataTableList($result));
        echo json_encode($result);
    } else if ($methodType == "Transfer") {

        $bodyContent = array(

            "TrackID" => trim($_POST["trackCodeID"]),
            "Plant" => trim($_POST["productionPlant"]),
            "GradingPlantID" => trim($_POST["gradingPlant"]),
            "Center" => trim($_POST["center"]),
            "TransactionDate" => trim($_POST["transactionDate"]),
            "Shift" => trim($_POST["shift"]),
            "Supervisor" => trim($_POST["superVisor"]),
            "Remarks" => trim($_POST["remarks"]),
            "TransferPlant" => "true",
            "Type" => "",
            "mergeHonLoose" => $mergeHonLoose,
        );

        $result = CallAPI($api, "POST", "", $bodyContent);
        header('Content-Type: application/json');
        echo json_encode($result);
    } else if ($methodType == "TableSave") {

        $bodyContent = array(
            "ArrivalID" => trim($_POST["arrivalID"]),
            "ReceivalBreakUpID" => trim($_POST["recvBreakupID"]), //ReceivalBreakUpID
            "DeheadingBreakUpID" => trim($_POST["deheadingBreakupID"]),
            "TrackID" => trim($_POST["trackID"]),
            "Plant" => trim($_POST["productionPlant"]),
            "Center" => trim($_POST["center"]),
            "TransactionDate" => trim($_POST["transactionDate"]),
            "Shift" => trim($_POST["shift"]),
            "Supervisor" => trim($_POST["superVisor"]),
            "Remarks" => trim($_POST["remarks"]),
            "HonQuantity" => trim($_POST["honQty"]),
            "HLCount" => trim($_POST["hlCount"]),
            "HLQuantity" => trim($_POST["hlQty"]),
            "Type" => "Breakup",
           "mergeHonLoose" => $mergeHonLoose,
            "totalDeheadingWeight" => trim($_POST["totalDeheadingWeight"]),
            "CheckingCount2" => trim($_POST["txtCheckingCount2"]),
            "HONQuantity" => trim($_POST["txtHONQuantity"]),
        );

        $result = CallAPI($api, "POST", "", $bodyContent);
        header('Content-Type: application/json');
        echo json_encode(fnDataTableList($result));
    } else if ($methodType == "TableUpdate") {

        $bodyContent = array(

            "ArrivalID" => trim($_POST["arrivalID"]),
            "ReceivalBreakUpID" => trim($_POST["recvBreakupID"]),
            "DeheadingBreakUpID" => trim($_POST["deheadingBreakupID"]),
            "TransactionDate" => trim($_POST["transactionDate"]),
            "TrackID" => trim($_POST["trackID"]),
            "HonQuantity" => trim($_POST["honQty"]),
            "HLCount" => trim($_POST["hlCount"]),
            "HLQuantity" => trim($_POST["hlQty"]),
            "Type" => "Breakup",
            "mergeHonLoose" => $mergeHonLoose,
            "totalDeheadingWeight" => trim($_POST["totalDeheadingWeight"]),
            "CheckingCount2" => trim($_POST["txtCheckingCount2"]),
            "HONQuantity" => trim($_POST["txtHONQuantity"]),
        );

        $result = CallAPI($api, "POST", "", $bodyContent);
        // echo '<pre>';print_r($result);die;
        header('Content-Type: application/json');
        echo json_encode(fnDataTableList($result));
        // echo json_encode($result);

    } else if ($methodType == "deleteRow") {

        $bodyContent = array(
            "DeheadingBreakUpID" => trim($_POST["breakUpID"]),
            "TrackID" => trim($_POST["trackID"]),
            "mergeHonLoose" => $mergeHonLoose,
        );

        $result = CallAPI($api, "DELETE", "", $bodyContent);
        header('Content-Type: application/json');
        echo json_encode(fnDataTableList($result));

        // } else if ($methodType == "popUp") {


    }
} else {

    $api = API_RM_PRODUCTION_TRACK_LIST;

    $bodyContent = array(
        'ArrivalDate' => trim($_POST["arrivalDate"]),
        'ReceivingPlantID' => trim($_POST['plantID']),
        'Supplier' => "",
        'PsId' => "",
        'Type' => trim($_POST["type"])
    );

    $resList = CallAPI($api, "GET", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode($resList);
}




// if (isset($item->DeheadingBreakUpID) &&  !empty($item->DeheadingBreakUpID)) {
//     $display = "show";
// } else {
//     $display = "hide";
// }
// if (isset($item->DeheadingBreakUpID) && !empty($item->DeheadingBreakUpID) && $item->EditAccess == 1 && $item->DeleteAccess == 1) {
//     $html .=
//         '<button class="mr-3 text-primary  btnActions btnUpdateRow" data-placement="top" title="Update BreakUP" 
//             style="' . ($display == "show" ? "" : "display: none") . '"
//             data-deheading-breakupid="' . $item->DeheadingBreakUpID . '" data-arrival-id="' . $item->ArrivalID . '" data-recv-breakup-id="' . $item->ReceivalBreakUpID . '" 
//             data-original-title="Edit">
//             <i class="btn-primary fa fa-save font-size-16"></i> <!-- pencil -->
//         </button>';
// } else {
//     $html .=
//         '<button class="mr-3 text-primary btnActions btnSaveRow" data-placement="top" title="ADD BreakUP" 
//             style="' . ($display == "show" ? "" : "display: none") . '"
//             data-deheading-breakupid="' . $item->DeheadingBreakUpID . '" data-arrival-id="' . $item->ArrivalID . '" data-recv-breakup-id="' . $item->ReceivalBreakUpID . '" data-original-title="Edit">
//             <i class="btn-primary fa fa-save font-size-16"></i> <!-- plus -->
//         </button>';
// }

// $html .=
//     '<button class="btn btn-link text-danger btnActions btnDelete" style="position: relative;top: -3px;' . ($display == "show" ? "" : "display: none") . '" data-breakup-id="' . $item->DeheadingBreakUpID . '"  
//         data-toggle="tooltip" data-placement="top" title="Delete" value="" data-original-title="Delete">
//         <i class="mdi mdi-trash-can font-size-16"></i></button>';
// $html .=
//     '</td>
// </tr>';