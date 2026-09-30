<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

  $methodType = trim($_POST["methodType"]); 
// $ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
// // $hasPpcStandardYields = $ArrayClientConfigData['hasPpcStandardYields'];
// // $hasWageSelector = $ArrayClientConfigData['hasWageSelector'];
if (isset($methodType) && !empty($methodType)) {

    $api = API_MATERAIL_DISPATCH_CREATE;
    //    exit;

    if ($methodType == "get") {

        if (isset($_POST["ArrivalDate"])) {
            $bodyContentPost = array(
                "ArrivalDate" => trim($_POST["ArrivalDate"]),
                "PlantID" => trim($_POST["PlantID"]),
                "DataType"=>"getArrivals"

            );
            $result = CallAPI($api, "GET", "", $bodyContentPost);
            header('Content-Type: application/json');
            $response = array('ArrivalsList' => $result->ArrivalsList, 'Error' => $error, 'Msg' => $msg);
            echo json_encode($result);
            $error = $result->Error;
            $msg = $result->Msg; 
            $ArrivalsList = $result->Data->ArrivalsList;

        } 
     

    } 
    else if ($methodType == "getFormData") {

        if (isset($_POST["materialdispatchid"])) {
            $MaterialDispatchID = trim($_POST["materialdispatchid"]);
            $bodyContentPost = array(               
                "DispatchMaterialID" => $MaterialDispatchID,
                "DataType" => "getDispatchMaterialList"   
            );
            // var_dump($bodyContentPost);
    
            $result = CallAPI($api, "GET", "", $bodyContentPost);
            //   var_dump($result);
    
    
            $error = $result->Error ;
            $msg = $result->Msg ;
            $DispatchMaterialList = $result->Data->DispatchMaterialList;
    
            $response = array(
                'DispatchMaterialList' => $DispatchMaterialList,
                'Error' => $error,
                'Msg' => $msg
            );
    
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        }
    }
    
    
    
    else if ($methodType == "post") {
        // echo "hi";exit;

        $ArrivalID = trim($_POST["drpBatchNo"]);
        $FromStationID = trim($_POST["drpFromStation"]);
        $ToStationID = trim($_POST["drpToStation"]);
        $TransferredDate = trim($_POST["txtTransferredDate"]);
        $Grade = trim($_POST["drpGrade"]);
        $VarietyID = trim($_POST["drpVariety"]);
        $Quantity = trim($_POST["txtQuantity"]);
        $DispatchedCrates = trim($_POST["txtDispatchedCrates"]);
        $ReceivedCrates = trim($_POST["txtReceivedCrates"]);
        $Status = trim($_POST["drpStatus"]);
        $MaterialDispatchID = trim($_POST["materialdispatchid"]);
        $PlantID = trim($_POST["drpProductionPlant"]);
        $ArrivalDate = trim($_POST["txtArrivalDate"]);

        

        $bodyContentPost = array(
            "ArrivalID" => $ArrivalID,
            "FromStationID" => $FromStationID,
            "ToStationID" => $ToStationID,
            "TransferredDate" => $TransferredDate,
            "Grade" => $Grade,
            "VarietyID" => $VarietyID,                     
            "Quantity" => $Quantity,
            "DispatchedCrates" => $DispatchedCrates,
            "ReceivedCrates" => $ReceivedCrates,
            "Status" => $Status,
            "MaterialDispatchID" => $MaterialDispatchID,
            "PlantID" => $PlantID,
            "ArrivalDate" =>$ArrivalDate

            
        );

        $result = CallAPI($api, "POST", "", $bodyContentPost);


header('Content-Type: application/json');
echo json_encode($result); // ✅ return the cleaned-up response

    }

   

    // $error = $result->Error;
    // $msg = $result->Msg; 
    // // $Data = end($result->Data->ValueAdditionData);
    // // $List = $result->Data->ValueAdditionData;

    // $ArrivalsList = $result->Data->ArrivalsList;

    // $response = array('html' => $htmlResponse, 'json' => $Data, 'arrivalslist' => $ArrivalsList, "Error" => $error, "Msg" => $msg);
    // echo json_encode($response);

    // var_dump($ArrivalsList); exit;

    // if (!isset($error) || empty($error)) {
    //     $htmlResponse = '
    //         <div class="card">
    //             <div class="card-body">
    //                 <table id="tblDataTable" class="table table-hover table-bordered">
    //                     <thead class="thead-light">
    //                             <tr>
    //                                 <th style="width:50px;">S.No</th>
    //                                 <th>Batch No </th>
    //                                 <th>From station</th>
    //                                 <th>To station</th>
    //                                 <th>Transferred Date</th>
    //                                 <th>Grade</th>
    //                                 <th>Variety </th>
    //                                 <th>Quantity</th>
    //                                 <th>Dispatched Crates</th>
    //                                 <th>Actions</th>
    //                             </tr>
    //                     </thead>
    //                     <tbody>';
    //     $i = 1;
    //     foreach ($List as $item) {
    //         // var_dump($item);
    //         $htmlResponse .= ' <tr>
    //                                 <td >'.$item->SNo.'</td>
    //                                 <td>Batch</td>
    //                                 <td class="tdPlant" data-plantid="">From station</td>
    //                                 <td>To station</td>
    //                                 <td class="tdPlant" data-plantid="">Transferred Date</td>
    //                                 <td>Grade</td>
    //                                 <td class="tdPlant" data-plantid="">Variety</td>
    //                                 <td>Quantity</td>
    //                                 <td class="tdPlant" data-plantid="">Dispatched Crates</td>                                    
    //                                 <td>
    //                                     <button type="button" id="btnDelete" name="btnDelete" data-ArrivalId="' . $item->ArrivalID . '" data-ArrivalBrkUpID="' . $item->BreakUpID . '" class="btn btn-link p-0 deleteBrkUp" ' . $DeleteAccess . ' >
    //                                         <i class="mdi mdi-delete font-size-16 text-danger"></i>
    //                                     </button>

    //                                     <button type="button"  data-ArrivalId="' . $item->ArrivalID . '" data-ArrivalBrkUpID="' . $item->BreakUpID . '" class="btn btn-link p-0 btnEdit" ' . $DeleteAccess . ' >
    //                                         <i class="mdi mdi-pencil font-size-16 text-primary"></i>
    //                                     </button>
    //                                 </td>  
    //                             </tr>';
    //         $i++;
    //     }
    //     $htmlResponse .= '
    //                     </tbody>
    //                    <!-- <tfoot>
    //                         <tr>
    //                             <td></td> 
    //                             <td><span  style="font-weight: 600;font-size: 16px;">Grand Total</span></td>
    //                             <td><span style="font-weight: 600;font-size: 16px;">' . $result->Data->Totals->TotalHLQuantity . '</span></td>
    //                             <td></td>
    //                             <td></td>
    //                             <td></td>
    //                             <td></td>
    //                             <td></td>
    //                             <td></td>
    //                             <td></td>
    //                         </tr>
    //                     </tfoot> -->
    //                 </table>
    //             </div>
    //         </div>';
    // }
    

    // echo json_encode($result, JSON_PRETTY_PRINT);
    // exit();
} 

// else {

//     $api = API_RM_PRODUCTION_LIST;

//     $ArrivalDate = trim($_POST["ArrivalDate"]);
//     $Type = trim($_POST["Type"]);

//     $bodyContent = array(
//         'ArrivalDate' => $ArrivalDate,
//         'Type' => $Type,
//         'ArrivalPlant' => "",
//         'Supplier' => "",
//         'PsId' => ""
//     );

//     $resList = CallAPI($api, "GET", "", $bodyContent);

//     // var_dump($resList);
//     // exit;
//     header('Content-Type: application/json');
//     echo json_encode($resList);
// }
