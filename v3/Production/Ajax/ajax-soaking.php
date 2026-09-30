<?php

include_once $_SERVER['DOCUMENT_ROOT'] .'/config/global.php';
include_once ROOT.'config/api-call.php';

$methodType = trim($_POST["methodType"]);

if(isset($methodType) && !empty($methodType)) {

   $api = API_SOAKING_DETAILS;
//    exit;

    if ($methodType == "get") {

        $ArrivalID = trim($_POST["ArrivalID"]);

        $bodyContentPost = array(
            "ArrivalID" => $ArrivalID
        );
        // var_dump($bodyContentPost);
        // exit();

        $result = CallAPI($api, "GET", "", $bodyContentPost);

    } else if ($methodType == "post") {

        $ArrivalDate = trim($_POST["ArrivalDate"]);
        $ArrivalID = trim($_POST["ArrivalID"]);
        $LotCode = trim($_POST["drpLotCode"]);
        $ProductionPlant = trim($_POST["drpProductionPlant"]);
        $SourceStation = trim($_POST["drpSourceStation"]);
        $TransactionDate = trim($_POST["txtTransactionDate"]);
        $Shift = trim($_POST["drpShift"]);
        $StartTime = trim($_POST["txtStartTime"]);
        $TimePeriod = trim($_POST["drpTimePeriod"]);
        $SaltKg = trim($_POST["txtSaltKg"]);
        $ChemicalQuantity = trim($_POST["txtChemicalQuantity"]);
        $Remarks = trim($_POST["txtRemarks"]);
        $Variety = trim($_POST["drpVariety"]);
        $TubNumber = trim($_POST["txtTubNumber"]);
        $InCount = trim($_POST["drpInCount"]);
        $SoakingInCount = trim($_POST["txtInCount"]);
        $InQuantity = trim($_POST["txtInQuantity"]);
        $OutCount = trim($_POST["txtOutCount"]);
        $OutQuantity = trim($_POST["txtOutQuantity"]);
        $hlBreakUpId = trim($_POST["hlBreakUpId"]);

        $bodyContentPost = array(
            "ArrivalID" => $ArrivalID,
            "Plant" => $ProductionPlant,
            "SourceStation" => $SourceStation,
            "TransactionDate" => $TransactionDate,
            "Shift" => $Shift,
            "StartTime" => $StartTime,
            "TimePeriod" => $TimePeriod,
            "Salt" => $SaltKg,
            "ChemicalQuantity" => $ChemicalQuantity,
            "Remarks" => $Remarks,
            "Variety" => $Variety,
            "TubNo" => $TubNumber,
            "InCount" => $InCount,
            "SoakingInCount" => $SoakingInCount,
            "InQuantity" => $InQuantity,
            "OutCount" => $OutCount,
            "OutQuantity" => $OutQuantity,
            "HLBreakupID" => $hlBreakUpId
        );

        // echo json_encode($bodyContentPost, JSON_PRETTY_PRINT);
        // exit;

        $result = CallAPI($api,"POST", "", $bodyContentPost);

        // echo json_encode($result, JSON_PRETTY_PRINT);
        // exit();

    } else if ($methodType == "delete") {

        $ArrivalID = $_POST["ArrivalID"];
        $BreakUpID = $_POST["BreakUpID"];

        $bodyContent = array(
            "ArrivalID" => $ArrivalID,
            "BreakUpID" => $BreakUpID
        );

        $result = CallAPI($api, "DELETE", "", $bodyContent);
    }
    
        $error = $result->Error;
        $msg = $result->Msg;
        
        $Data = end($result->Data->SoakingData);  // Get the Last Record in Json Array of Object
        $List = $result->Data->SoakingData;

        $HLCounts = $result->Data->HLCounts;

        // echo json_encode($result, JSON_PRETTY_PRINT);
        // exit();

        if (!isset($error) || empty($error)) {
            $htmlResponse = '
            <div class="card">
                <div class="card-body">
                    <table id="tblDataTable" class="table table-hover table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th style="width:50px;">S.No</th>
                                <th>Arrival Code </th>
                                <th>Production Plant</th>
                                <th>Center</th>
                                <th>Transaction Date</th>
                                <th>Shift</th>
                                <th>Variety</th>
                                <th>Tub Number</th>
                                <th>Time Period</th>
                                <th>Start Time</th>
                                <th>Source Count</th>
                                <th>In Count</th>
                                <th>In Qty</th>
                                <th>Out Count</th>
                                <th>Out Qty</th>
                                <th>Remarks</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>';
                        $i = 1;
                        foreach ($List as $item) {
                            $htmlResponse .= '
                            <tr >
                                <td>'. $item->SNo .'</td>
                                <td>'. $item->LotNo .'</td>
                                <td>'. $item->Plant .'</td>
                                <td>'. $item->Center .'</td>
                                <td>'. $item->TransactionDate .'</td>
                                <td>'. $item->Shift .'</td>
                                <td>'. $item->Variety .'</td>
                                <td>'. $item->TubNumber .'</td>
                                <td>'. $item->TimePeriod .'</td>
                                <td>'. $item->SoakInTime .'</td>
                                <td>'. $item->SoakInCount .'</td>
                                <td>'. $item->SoakingInCount .'</td>
                                <td>'. $item->SoakInQty .'</td>
                                <td>'. $item->SoakOutCount .'</td>
                                <td>'. $item->SoakOutQty .'</td>
                                <td>'. $item->Remarks .'</td>
                                <td>
                                    <button type="button" id="btnDelete" name="btnDelete" data-arrivalid="'. $item->ArrivalID.'"  data-arrivalbrkupid="'. $item->BreakUpID.'" class="btn btn-link p-0 deleteBrkUp" '. $DeleteAccess .' >
                                        <i class="mdi mdi-delete font-size-16 text-danger"></i>
                                    </button>
                                </td>
                            </tr>';
                            $i++;
                        }
                        $htmlResponse .= '
                        </tbody>
                        <tfoot>
                            <tr>
                                <td></td>
                                <td>Total</td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td></td>
                                <td><span style="font-weight: 600;font-size: 16px;">'.$result->Data->Totals->TotalSoakInQty.'</span></td>
                                <td></td>
                                <td><span style="font-weight: 600;font-size: 16px;">'.$result->Data->Totals->TotalSoakOutQty.'</span></td>                                
                                <td></td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>';
        }

        $response = array('html' => $htmlResponse, 'json' => $Data, 'hlBreakId' => $HLCounts, "Error"=> $error, "Msg" => $msg);
        echo json_encode($response);

} else {
    
    $api = API_RM_PRODUCTION_LIST;

    $ArrivalDate = trim($_POST["ArrivalDate"]);
    $Type = trim($_POST["Type"]);

    $bodyContent = array(
        'ArrivalDate' => $ArrivalDate,
        'Type' => $Type,
        'ArrivalPlant' =>"",
        'Supplier' =>"",
        'PsId' => ""
    );

    $resList = CallAPI($api, "GET", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode($resList);
}

?>