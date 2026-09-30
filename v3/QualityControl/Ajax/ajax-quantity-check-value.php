<?php

// session_start();
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$methodType = trim($_POST["methodType"]);
$api =  API_QUANTITY_CHECK_VALUE;

if ($methodType == 'GET') {
  
    $bodyContent = array(
        "ArrivalDate" => trim($_POST['arrivalDate']),
        "BatchCode" => trim($_POST['batchCode']),
        "MethodType" => '',
    );
    $result = CallAPI($api, "GET", "", $bodyContent);
    header('Content-Type: application/json');
    echo json_encode(fnDataTableList($result));

}
else if ($methodType == 'QualityCheck') {
    $bodyContent = array(
        "ArrivalDate" => trim($_POST['arrivalDate']),
        "MethodType" => 'BATCH_NUMBERS',
    );
    $result = CallAPI($api, "GET", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}
else if ($methodType == 'getCounts') {
    $bodyContent = array(
        "ArrivalDate" => trim($_POST['arrivalDate']),
        "BatchCode" => trim($_POST['batchCode']),
        "Variety" => trim($_POST['variety']),
        "MethodType" => 'HL_COUNT',
    );
    $result = CallAPI($api, "GET", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}

else if($methodType == 'GET') {
  
    $bodyContent = array(
        "ValueAdditionDate" => trim($_POST['valueAdditionDate']),
        "BatchCode" => trim($_POST['batchCode']),
        "MethodType" => '',
    );
    $result = CallAPI($api, "GET", "", $bodyContent);
    header('Content-Type: application/json');
    echo json_encode(fnDataTableList($result));

}


else if ($methodType == 'GeValueAdditionDate') {
    $bodyContent = array(
        "ValueAdditionDate" => trim($_POST['valueAdditionDate']),
        "MethodType" => 'BATCH_NUMBERS',
    );
    $result = CallAPI($api, "GET", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}

else if ($methodType == 'deleteRow') {
    $bodyContent = array(
    "QuantityCheckid" => trim($_POST["QuantityCheckid"]),
    );
    $result = CallAPI($api, "DELETE", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode($result);
}

// exit;
else if ($methodType == 'TableSave') {
    

 $QuantityCheckid = trim($_POST["QuantityCheckid"]);
   $ArrivalDate = trim($_POST["ArrivalDate"]);
 $ValueAdditionDate = trim($_POST["ValueAdditionDate"]);
$BatchCode = trim($_POST["BatchCode"]);
$RoomTemperature = trim($_POST["RoomTemperature"]);
$TableNo = trim($_POST["TableNo"]);
$Variety = trim($_POST["Variety"]);
$Grade = trim($_POST["Grade"]);
$TypeOfCut = trim($_POST["TypeOfCut"]);
$ImproperPeeling = trim($_POST["ImproperPeeling"]);
$ImproperCutting = trim($_POST["ImproperCutting"]);
$Vein = trim($_POST["Vein"]);
$Broken = trim($_POST["Broken"]);
$OtherObjectionDefects = trim($_POST["OtherObjectionDefects"]);
$TotalPeelingGirls = trim($_POST["TotalPeelingGirls"]);
$HeadLessQuantity = trim($_POST["HeadLessQuantity"]);
$Quantity = trim($_POST["Quantity"]);
$Yield = trim($_POST["Yield"]);
$CheckingGirls = trim($_POST["CheckingGirls"]);
$SuperVisor = trim($_POST["SuperVisor"]);
$VerifiedBy = trim($_POST["VerifiedBy"]);
$InspectedBy = trim($_POST["InspectedBy"]);
$Remarks = trim($_POST["Remarks"]);
$CorrectiveAction = trim($_POST["CorrectiveAction"]);
$HLCount = trim($_POST["HLCount"]);
 $PageNo = trim($_POST["PageNo"]);
 $CheckedBy = trim($_POST["CheckedBy"]);
 $TableTemperature = trim($_POST["TableTemperature"]);
 $CrateTemperature = trim($_POST["CrateTemperature"]);
 $NumberSamplesChecked = trim($_POST["NumberSamplesChecked"]);
$BatchNoType = trim($_POST["BatchNoType"]);
$VAStation = trim($_POST["VAStation"]);



$bodyContent = array(
    'QuantityCheckid' => $QuantityCheckid,
    'ArrivalDate' => $ArrivalDate,
    'BatchCode' => $BatchCode,
    'RoomTemperature' => $RoomTemperature,
    'TableNo' => $TableNo,
    'Variety' => $Variety,
    'Grade' => $Grade,
    'TypeOfCut' => $TypeOfCut,
    'ImproperPeeling' => $ImproperPeeling,
    'ImproperCutting' => $ImproperCutting,
    'Vein' => $Vein,
    'Broken' => $Broken,
    'OtherObjectionDefects' => $OtherObjectionDefects,
    'TotalPeelingGirls' => $TotalPeelingGirls,
    'HeadLessQuantity' => $HeadLessQuantity,
    'Quantity' => $Quantity,
    'Yield' => $Yield,
    'CheckingGirls' => $CheckingGirls,
    'SuperVisor' => $SuperVisor,
    'VerifiedBy' => $VerifiedBy,
     'InspectedBy' => $InspectedBy,
    'Remarks' => $Remarks,
    'CorrectiveAction' => $CorrectiveAction,
    'HLCount' => $HLCount,
     'CheckedBy' => $CheckedBy,
   'ReglazeBatchNo' => trim($_POST["ReglazeBatchNo"]),
   'TableTemperature' => $TableTemperature,
    'CrateTemperature' => $CrateTemperature,
    'NumberSamplesChecked' => $NumberSamplesChecked,
    'BatchNoType' => $BatchNoType,
     'VAStation' => $VAStation,
    'PageNo' => $PageNo,
);

  

    $result = CallAPI($api, "POST", "", $bodyContent);
    header('Content-Type: application/json');
    echo json_encode($result);
} 

    // $resList = CallAPI($api, "GET", "", array());

    // echo json_encode($resList, JSON_PRETTY_PRINT);
       

 function fnDataTableList($result)
{
    $error = $result->Error;
    $msg = $result->Msg;

     if (isset($result) && !empty($result)) {
        $error = $result->Error;
     }
        if (!isset($error) || empty($error)) {
     $List = $result->QualityData;
    
    $html =
        '<div class="card">
        <div class="card-body">
        <table id="tblDataTable" class="table table-hover table-bordered">
        <thead class="thead-light">
            <tr>
            <th>S.No</th>
            <th>Value Addition Date</th>
             <th>VAStation</th>
            <th>Batch Number</th>
            <th>Variety</th>
            <th>Room Temperature</th>
             <th>Table Temperature</th>
            <th>Crate Temperature</th>
            <th>Number Samples Checked</th>
            <th>Table No</th>
            <th>Grade</th>
            <th>Type Of Cut</th>
            <th>Improper Peeling</th>
            <th>Improper Cutting</th>
            <th>Vein</th>
            <th>Broken</th>
            <th>Other Objection Defects</th>
            <th>Total Peeling Girls</th>
            <th>Head Less Quantity</th>
            <th>Quantity</th>
            <th>Yield (%)</th>
            <th>Checking Girls</th>
            <th>Supervisor</th>
            <th>Inspected By</th>
            <th>Verified By</th>
             
             <th>CorrectiveAction</th>
            
            <th>Actions</th>
            </tr>
        </thead>
        <tbody>';

     $i = 1;            
foreach ($List as $item) {
   
    $html .= '
    <tr>
        <td>' . $i . '</td>
         <td class="tdArrivalDate" data-arrival_date="' . $item->ArrivalDate . '">' . date("d-m-Y", strtotime($item->ArrivalDate)) . '</td>
        <td class="tdVAStation">' . $item->VAStation . '</td>
         <td class="tdBatchCode" data-batch_no_type="' . $item->BatchNoType . '"   data-batch_no="' . $item->BatchCode . '" data-reglaze_batch_no="' . $item->ReglazeBatchNo . '">' . $item->ArrivalNumber . '</td>
        <td class="tdVariety">' . $item->Variety . '</td>
        <td class="tdRoomTemperature">' . $item->RoomTemperature . '</td>
         <td class="tdTableTemperature">' . $item->TableTemperature . '</td>
        <td class="tdCrateTemperature">' . $item->CrateTemperature . '</td>
         <td class="tdNumberSamplesChecked">' . $item->NumberSamplesChecked . '</td>
        <td class="tdTableNo">' . $item->TableNo . '</td>
       <td class="tdGrade" data-id="' . $item->GradeId . '">' . $item->Grade . '</td>
        <td class="tdTypeOfCut" data-id="' . $item->TypeOfCutId . '">' . $item->TypeOfCut . '</td>
        <td class="tdImproperPeeling" data-id="' . $item->ImproperPeelingId . '">' . $item->ImproperPeeling . '</td>
        <td class="tdImproperCutting" data-id="' . $item->ImproperCuttingId . '">' . $item->ImproperCutting . '</td>
        <td class="tdVein" data-id="' . $item->VeinId . '">' . $item->Vein . '</td>
        <td class="tdBroken" data-id="' . $item->BrokenId . '">' . $item->Broken . '</td>
        <td class="tdOtherObjectionDefects" data-id="' . $item->OtherObjectionDefectsId . '">' . $item->OtherObjectionDefects . '</td>
        <td class="tdTotalPeelingGirls">' . $item->TotalPeelingGirls . '</td>
        <td class="tdHLCount">' . $item->HeadLessQuantity . '</td>
        <td class="tdQuantity">' . $item->Quantity . '</td>
        <td class="tdYield">' . $item->Yield . '</td>
        <td class="tdCheckingGirls">' . $item->CheckingGirls . '</td>
        <td class="tdSuperVisor">' . $item->SuperVisor . '</td>
         <td class="tdInspectedBy">' . $item->InspectedBy . '</td>
        <td class="tdVerifiedBy">' . $item->VerifiedBy . '</td>
       
         <td class="tdCorrectiveAction" data-id="' . $item->CorrectiveActionId . '">' . $item->CorrectiveAction . '</td>
       

        <td>
            
       
        <button class="btnEdit" data-id="'.  $item->QuantityCheckID .'" data-arrival="' . $item->ArrivalDate . '" title="Edit" style="border: none; background: none; padding: 0; cursor: pointer;">
                            <i class="mdi mdi-pencil font-size-16 text-primary"></i>
                           </button>

                           <button class="btn btn-link text-danger btnActions btnDelete"
    style="position: relative; top: -3px;" data-id="'.$item->QuantityCheckID.'" data-toggle="tooltip" data-placement="top" title="Delete" value="" data-original-title="Delete">
    <i class="mdi mdi-trash-can font-size-16"></i>
</button>
           </td>
                            
    </tr>';
    $i++;
}


}
 $response = array(
        'htmlRes' => $html,
        'data' => $List,
        "Error" => $error,
        "Msg" => $msg
    );

    return $response;
}

