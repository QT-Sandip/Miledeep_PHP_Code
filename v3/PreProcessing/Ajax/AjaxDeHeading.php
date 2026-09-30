
<?php

include_once $_SERVER['DOCUMENT_ROOT'] .'/config/global.php';
include_once ROOT.'config/api-call.php';

$methodType = trim($_POST["methodType"]);
$ClientConfigData = (object)$_SESSION['ClientConfigData'];
$ConsolidateLotCount = $ClientConfigData->ConsolidateLotCount;
if(isset($methodType) && !empty($methodType)) {

    $api = API_DE_HEADING_DETAILS;

    if ($methodType == "get") {

        $ArrivalID = trim($_POST["ArrivalID"]);
$TransactionDate = trim($_POST["txtTransactionDate"]);
        $bodyContentPost = array(
            "ArrivalID" => $ArrivalID,
            "TransactionDate"=> $TransactionDate,
            'RequestFrom' => "V2",
            "ConsolidateLotCount" => $ConsolidateLotCount
        );

        $result = CallAPI($api, "GET", "", $bodyContentPost);
        //echo '<pre>';print_r($result);die;
        // echo json_encode($result, JSON_PRETTY_PRINT);
        

    } else if ($methodType == "post") {
            
        $ArrivalDate = trim($_POST["ArrivalDate"]);
        $ArrivalID = trim($_POST["ArrivalID"]);
        // $AriavalLotCode = trim($_POST["drpAriavalLotCode"]);
        $ProductionPlant = trim($_POST["drpProductionPlant"]);
        $Center = trim($_POST["drpCenter"]);
        $TransactionDate = trim($_POST["txtTransactionDate"]);
        $Shift = trim($_POST["drpShift"]);
        $SuperVisor = trim($_POST["drpSuperVisor"]);
        $HeadONCount = trim($_POST["txtHeadONCount"]);
        $HLCount = trim($_POST["txtHLCount"]);
        $HONCount = trim($_POST["txtHONCount"]);
        $HLQuantity = trim($_POST["txtHLQuantity"]); 
        $HonQuantity  = trim($_POST["txtHONQuantity"]); 
        $CountNetYield = trim($_POST["txtCountNetYield"]);
        $IceConsumption = trim($_POST["txtIceConsumption"]);
        $Remarks = trim($_POST["txtRemarks"]);
    
        // alert($HonQuantity); 
        $bodyContentPost = array(
            "ArrivalID" => $ArrivalID,
            "Plant"=> $ProductionPlant,
            "Center"=> $Center,
            "TransactionDate"=> $TransactionDate,
            "Shift"=> $Shift,
            "Supervisor"=> $SuperVisor,
            "HeadonCountAfterChecking"=> $HeadONCount,
            "HLCount"=> $HLCount,
            "HONCount"=> $HONCount,
            "HLQuantity"=> $HLQuantity,
            "HonQuantity"=> $HonQuantity,
            "CountNetYield"=> $CountNetYield,
            "IceConsumption"=> $IceConsumption,
            "Remarks"=> $Remarks,
            'RequestFrom' => "V2",
            "ConsolidateLotCount" => $ConsolidateLotCount
        );
    
        //print_r($bodyContentPost); exit;
       //echo json_encode($bodyContentPost); EXIT;
        $result = CallAPI($api, "POST", "", $bodyContentPost);

    } else if ($methodType == "delete") {
        
        $ArrivalID = $_POST["ArrivalID"];
        $BreakUpID = $_POST["BreakUpID"];
    $TransactionDate = trim($_POST["txtTransactionDate"]);
        $bodyContent = array(
            "ArrivalID" => $ArrivalID,
            "BreakUpID" => $BreakUpID,
            "TransactionDate"=> $TransactionDate,
            'RequestFrom' => "V2",
            "ConsolidateLotCount" => $ConsolidateLotCount
        );
    
        $result = CallAPI($api, "DELETE", "", $bodyContent);
        
    }
    
        $error = $result->Error;
        $msg = $result->Msg;
        $Data = end($result->Data->DeheadingData);  // Get the Last Record in Json Array of Object
        $BalanceWeight = $result->Data->BalanceWeight;  // Get the Last Record in Json Array of Object
        $List = $result->Data->DeheadingData;
        // echo json_encode($List, JSON_PRETTY_PRINT);
        // exit;
    
    
        if (!isset($error) || empty($error)) { 
            $htmlResponse = '
            <div class="card">
                <div class="card-body">
                    <table id="tblDataTable" class="table table-hover table-bordered">
                        <thead class="thead-light">
                            <tr>
                                <th>S.No</th>
                                <th>Arrival Code</th>
                                <th>Production Plant</th>
                                <th>Center</th>
                                <th>De Heading Date</th>
                                
                                <th>Shift</th>
                                <th>SuperVisor</th>
                                <th>Checking Count-2</th>
                                <th>HL Count</th>
                                <th>HON Count</th>
                                
                                <th>HON Quantity(Kgs)</th> 
                                <th>HL Quantity(Kgs)</th> 
                                <th>Count Net Yield(%)</th>
                                <th>Yield(%)</th>
                                <th>Ice Consumption</th>
                                <th>Remarks</th>
                                
                                <th>Actions</th>                            
                            </tr>
                            </thead>
                            <tbody>';
            $i = 1;
            foreach($List as $item) { 
                // echo $result->DeleteAccess;
                // $deleteAccess = $result->DeleteAccess ? "disabled" : "";  
                $htmlResponse .= '
                                <tr >
                                    <td>'. $item->SNo .'</td>
                                    <td>'. $item->LotNo .'</td>
                                    <td>'. $item->Plant .'</td>
                                    <td>'. $item->Center .'</td>
                                    <td>'. $item->TransactionDate .'</td>
                                    
                                    <td>'. $item->Shift .'</td>
                                    <td>'. $item->UserName .'</td>
                                    <td>'. $item->HeadonCountAfterCheckingCount2 .'</td>
                                    <td>'. $item->HLCount .'</td>
                                    <td>'. $item->HONCount .'</td>
                                    
                                    <td>'. $item->HonQuantity .'</td>
                                    <td>'. $item->HLQuantityKgs .'</td>
                                    <td>'. $item->CountNetYield .'</td>
                                    <td>'. $item->Yield .'</td>
                                    <td>'. $item->IceConsumption .'</td>
                                    <td>'. $item->Remarks .'</td>
                                    
                                    <td>
                                        <button type="button" id="btnDelete" name="btnDelete" data-ArrivalId="'. $item->ArrivalID.'" data-ArrivalBrkUpID="'. $item->BreakUpID.'" class="btn btn-link p-0 deleteBrkUp" '. $DeleteAccess .' >
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
                                    <td><span  style="font-weight: 600;font-size: 16px;">Grand Total</span></td> 
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    
                                    <td></td>
                                    <td></td>
                                    <td></td>                                 
                                    <td></td>                                   
                                    <td></td>                                   

                                    <td><span style="font-weight: 600;font-size: 16px;">' . $result->Data->Totals->TotalHOnQuantity . '</span></td>                                    
                                    <td><span style="font-weight: 600;font-size: 16px;">' . $result->Data->Totals->TotalHLQuantity . '</span></td>                                    
                                    <td></td>
                                    <td><span style="font-weight: 600;font-size: 16px;">' . $result->Data->Totals->AvgYield . '</span></td>
                                    <td></td>
                                    <td></td>

                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>';
        }
    
        $response = array('html' => $htmlResponse, 'json' => $Data, 'balanceWeight' => $BalanceWeight, "Error"=> $error, "Msg" => $msg);
        echo json_encode($response);
        
} else {
    
    $api = API_RM_PRODUCTION_LIST; // API_RM_ARRIVAL_LIST;

    $ArrivalDate = trim($_POST["ArrivalDate"]);

    $bodyContent = array(
        'ArrivalDate' => $ArrivalDate,
        'ArrivalPlant' =>"",
        'Supplier' =>"",
        'PsId' => "",
        'Type' => "DeHeading",
        'RequestFrom' => "V2",
        "ConsolidateLotCount" => $ConsolidateLotCount
    );

    $resList = CallAPI($api, "GET", "", $bodyContent);
    
    header('Content-Type: application/json');
    echo json_encode($resList);
}

?>