<?php

include_once $_SERVER['DOCUMENT_ROOT'] .'/config/global.php';
include_once ROOT.'config/api-call.php';
$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
$RstrictNoOfPersons = !empty($ArrayClientConfigData['RstrictNoOfPersons'])?$ArrayClientConfigData['RstrictNoOfPersons']:0;  //anil, new client requirement


$methodType = trim($_POST["methodType"]);

if(isset($methodType) && !empty($methodType)) {

    $api = API_HAVL_BILL_DETAILS;
    //  exit;

    if ($methodType == "get") {

        $BillID = trim($_POST["BillID"]);

        $bodyContentPost = array(
            "BillID" => $BillID,
            'RstrictNoOfPersons' => $RstrictNoOfPersons
        );        

        $result = CallAPI($api, "GET", "", $bodyContentPost);        
        // echo json_encode($result, JSON_PRETTY_PRINT);
        // exit();
    
    } else if ($methodType == "post") {
            
        $ArrivalDate = trim($_POST["ArrivalDate"]);
        $ArrivalID = trim($_POST["ArrivalID"]);
        $AriavalLotCode = trim($_POST["drpAriavalLotCode"]);
        $ProductionPlant = trim($_POST["drpProductionPlant"]);
        $Center = trim($_POST["drpCenter"]);
        $TransactionDate = trim($_POST["txtTransactionDate"]);
        $Type = trim($_POST["drpType"]);
        $Supplier = trim($_POST["drpSupplier"]);
        $Contractor = trim($_POST["drpContractor"]);
        $Batch = trim($_POST["drpBatch"]);
        $Person = trim($_POST["txtPerson"]);
        $Shift = trim($_POST["drpShift"]);
        $Adjustments = trim($_POST["txtAdjustments"]);
        $Extras = trim($_POST["txtExtras"]);
        $Remarks = trim($_POST["txtRemarks"]);
        $Variety = trim($_POST["drpVariety"]);
        $HLCount = trim($_POST["txtHLCount"]);
        $HLWt = trim($_POST["txtHLWt"]);
        $BillID = trim($_POST["BillID"]); //uma
        
        $bodyContentPost = array(
            "ArrivalID" => $ArrivalID,
            "Plant"=> $ProductionPlant,
            "Center"=> $Center,
            "TransactionDate"=> $TransactionDate,
            "Type" => $Type,
            // "Supplier"=> $Supplier,
            "ContractorID"=> $Contractor,
            "BatchID"=> $Batch,
            "Persons"=> $Person,
            "Shift"=> $Shift,
            "Adjustments"=> $Adjustments,
            "Extras"=> $Extras,
            "Remarks"=> $Remarks,
            "VarietyID"=> $Variety,
            "HLCount"=> $HLCount,
            "HLWeight"=> $HLWt,
            "BillID" => $BillID,
            "WorkersIds" => trim($_POST["WorkersIds"]),
            "RstrictNoOfPersons" => trim($_POST["RstrictNoOfPersons"]),
        );

        // echo json_encode($bodyContentPost, JSON_PRETTY_PRINT);
        
        $result = CallAPI($api, "POST", "", $bodyContentPost);
        // echo json_encode($result, JSON_PRETTY_PRINT);
        

    } else if ($methodType == "delete") {
        
        $BillID = $_POST["bID"];
        $BreakUpID = $_POST["breakUpID"];
    
        $bodyContent = array(
            "BillID" => $BillID,
            "BreakUpID" => $BreakUpID,
            'RstrictNoOfPersons' => $RstrictNoOfPersons
        );
    
        $result = CallAPI($api, "DELETE", "", $bodyContent);
        
    }else if ($methodType == "GetWorkersBySubContractorID") {
        $result = CallAPI($api, "POST", "", $_POST);
        echo json_encode($result,true);
        die;
    }else if ($methodType == "GetWorkersByContractorID") {
        $result = CallAPI($api, "POST", "", $_POST);
        echo json_encode($result,true);
        die;
    }

        $error = $result->Error;
        $msg = $result->Msg;
        $Data = end($result->Data->BillData);  // Get the Last Record in Json Array of Object
        $List = $result->Data->BillData;
        //echo '<pre>';print_r($result);die;
        
        if (!isset($error) || empty($error)) {
            $htmlResponse = '
            <div class="card">
                <div class="card-body">
                    <table id="tblDataTable" class="table table-hover table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th style="width:50px;">S.No</th>
                                <th>Transaction Date</th>
                                <th>LotId </th>
                                <th>Production Plant</th>
                                <th>Center</th>
                                <th>Batch Name</th>
                                <th>Shift</th>
                                <th>Persons</th>
                                <th>Adjustments</th>
                                <th>Extras</th>
                                <th>Variety</th>
                                <th>Count</th>
                                <th>Quantity(Kgs)</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>';
                        $i = 1;
                        foreach ($List as $item) {
                            $htmlResponse .='
                            <tr>
                                <td>'. $item->SNo .'</td>
                                <td>'. $item->TransactionDate .'</td>
                                <td>'. $item->LotNo .'</td>
                                <td>'. $item->Plant .'</td>
                                <td>'. $item->Center .'</td>
                                <td>'. $item->BatchName .'</td>
                                <td>'. $item->Shift .'</td>
                                <td>'. $item->Persons .'</td>
                                <td>'. $item->Adjustments .'</td>
                                <td>'. $item->Extras .'</td>
                                <td>'. $item->Variety .'</td>
                                <td>'. $item->Count .'</td>
                                <td>'. $item->HLQty .'</td>
                                <td>';
                                    if(isset($item->BreakUpID) && !empty($item->BreakUpID)) {
                                        $isShow = "";
                                    }else{
                                        $isShow = "display: none";
                                    }
                                $htmlResponse .='
                                    <button type="button" id="btnDelete" name="btnDelete" style="'.$isShow.'"
                                        data-brkupid="'. $item->BreakUpID.'" data-billid="'.$item->BillID.'"
                                        class="btn btn-link p-0 deleteBrkUp" '. $DeleteAccess .'>
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
                                <td></td>
                                <td><span style="font-weight: 600;font-size: 16px;">'. $result->Data->Totals->TotalHLQuantity .'</span></th>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>';
        }

        $response = array('html' => $htmlResponse, 'json' => $Data, "Error"=> $error, "Msg" => $msg);
        echo json_encode($response);

}  else {
    
    $api = API_RM_PRODUCTION_LIST;

    $ArrivalDate = trim($_POST["ArrivalDate"]);

    $bodyContent = array(
        'ArrivalDate' => $ArrivalDate,
        'ArrivalPlant' =>"",
        'Supplier' =>"",
        'PsId' => ""
    );

    $resList = CallAPI($api, "GET", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode($resList);
}

?>