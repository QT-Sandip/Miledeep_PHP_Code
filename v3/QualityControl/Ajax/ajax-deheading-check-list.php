<?php

// session_start();
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';


$methodType = trim($_POST["methodType"]);

// echo print_r($methodType);
// exit;
$api = API_DEHEADING_CHECK_LIST;

if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
    $ArrivalDate = trim($_GET["ArrivalDate"]);
    $BatchCode = trim($_GET["BatchCode"]);
  
    $bodyContent = array(
         "ArrivalDate" => $ArrivalDate,
        "BatchCode" => $BatchCode,
        "MethodType" => '',
    );
}
if ($methodType == 'GET') {
  
    $bodyContent = array(
        "ArrivalDate" => trim($_POST['ArrivalDate']),
       "BatchCode"=> trim($_POST['BatchCode']),
         "MethodType" => '',
    );
    $result = CallAPI($api, "GET", "", $bodyContent);
// echo print_r($result);
// exit;

     if (!isset($error) || empty($error)) {

            header('Content-Type: application/json');
            echo json_encode(fnDataTableList($result));
        }
}


else if ($methodType == 'GetBatchNumbers') {
    $bodyContent = array(
        "ArrivalDate" => trim($_POST['ArrivalDate']),
        "MethodType" => 'BATCH_NUMBERS',
    );
    $result = CallAPI($api, "GET", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}

else if ($methodType == 'deleteRow') {
    $bodyContent = array(
    "Deheadingid" => trim($_POST["Deheadingid"]),
    );
    $result = CallAPI($api, "DELETE", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode($result);
}

// exit;
else if ($methodType == 'TableSave') {



 $Deheadingid = trim($_POST["Deheadingid"]);
    $ArrivalDate = trim($_POST["ArrivalDate"]);
    $BatchCode = trim($_POST["BatchCode"]);
    $ArrivalNumber = trim($_POST["ArrivalNumber"]);
    $Variety = trim($_POST["Variety"]);
    $Count = trim($_POST["Count"]);
    $ReceivingQuantity = trim($_POST["ReceivingQuantity"]);
    $ReceivingTime = trim($_POST["ReceivingTime"]);
    $GeneralAppearance = trim($_POST["GeneralAppearance"]);
    $Colour = trim($_POST["Colour"]);
    $Freshness = trim($_POST["Freshness"]);
    $Odour = trim($_POST["Odour"]);
    $Texture = trim($_POST["Texture"]);
    $Temperature = trim($_POST["Temperature"]);
    $Deterioration = trim($_POST["Deterioration"]);
    $SendingQuantity = trim($_POST["SendingQuantity"]);
     $Discolouration = trim($_POST["Discolouration"]);
    $BlackSpots = trim($_POST["BlackSpots"]);
    $BrokenDamaged = trim($_POST["BrokenDamaged"]);
    $SoftShell = trim($_POST["SoftShell"]);
    $ForeignMeterial = trim($_POST["ForeignMeterial"]);
    $SendingTime = trim($_POST["SendingTime"]);
    // $HangingMeat = trim($_POST["HangingMeat"]);
    $HangingMeat = trim($_POST["HangingMeat"]);
   $AttachedLegs = trim($_POST["AttachedLegs"]);
    $VerifiedBy = trim($_POST["VerifiedBy"]);
    $InspectedBy = trim($_POST["InspectedBy"]);
  $Remarks = isset($_POST['Remarks']) ? trim($_POST['Remarks']) : '';
    
    $PageNo = trim($_POST["PageNo"]);


    $bodyContent = array(
         'Deheadingid' => $Deheadingid,
        'ArrivalDate' => $ArrivalDate,
        'BatchCode' => $BatchCode,
        'ArrivalNumber' => $ArrivalNumber,
        'Variety' => $Variety,
        'Count' => $Count,
        'ReceivingQuantity' => $ReceivingQuantity,
        'GeneralAppearance' => $GeneralAppearance,
         'ReceivingTime' => $ReceivingTime,
        'Colour' => $Colour,
        'Freshness' => $Freshness,
        'Odour' => $Odour,
        'Texture' => $Texture,
        'Temperature' => $Temperature,
        'Deterioration' => $Deterioration,
        'SendingQuantity' => $SendingQuantity,
        'Discolouration' => $Discolouration,
        'BlackSpots' => $BlackSpots,
        'SoftShell' => $SoftShell,
        'BrokenDamaged' => $BrokenDamaged,
        'ForeignMeterial' => $ForeignMeterial,
        'SendingTime' => $SendingTime,
        'HangingMeat' => $HangingMeat,
        'AttachedLegs' => $AttachedLegs,
         'VerifiedBy' => $VerifiedBy,
        'InspectedBy' => $InspectedBy,
         'Remarks' => $Remarks,
        
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
     $List = $result->DeheadingData;
    
    $html =
        '<div class="card">
        <div class="card-body">
        <table id="tblDataTable" class="table table-hover table-bordered">
        <thead class="thead-light">
            <tr>
                <th>S.No</th>
                <th>Batch Number</th>
                <th>Variety</th>
                <th>Count</th>
                <th>HeadOn Weight</th>
                <th>Receiving Time</th>
                <th>General Appearance</th>
                <th>Colour</th>
                <th>Freshness</th>
                <th>Odour</th>
                <th>Texture</th>
                <th>Temperature</th>
                <th>Deterioration</th>
                <th>Discolouration</th>
                <th>BlackSpots</th>
                <th>SoftShell</th>
                <th>BrokenDamaged</th>
                 <th>ForeignMeterial</th>
                 <th>HL QTY</th>
                 <th>SendingTime</th>
                <th>Hanging Meat</th>
                <th>AttachedLegs</th>
                <th>Inspected By</th>
                 <th>Verified By</th>
                 <th>Remarks</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>';

     $i = 1;            
foreach ($List as $item) {
    // echo print_r($List);
    // exit;

    $html .= '
    <tr>
        <td>' . $i . '</td>
        <td class="tdBatchCode" data-value="' . $item->BatchCode . '">' . $item->BatchCodeName .'</td>
       <td class="tdVariety">' . $item->Variety . '</td>
        <td class="tdCount">' . $item->Count . '</td>
        <td class="tdReceivingQuantity">' . $item->ReceivingQuantity . '</td>
         <td class="tdReceivingTime">' . $item->ReceivingTime . '</td>
        <td class="tdGeneralAppearance">' . $item->GeneralAppearance . '</td>
        <td class="tdColour">' . $item->Colour . '</td>
        <td class="tdFreshness">' . $item->Freshness . '</td>
        <td class="tdOdour">' . $item->Odour . '</td>
        <td class="tdTexture">' . $item->Texture . '</td>
        <td class="tdTemperature">' . $item->Temperature . '</td>
        <td class="tdDeterioration">' . $item->Deterioration . '</td>
         <td class="tdDiscolouration">' . $item->Discolouration . '</td>
        <td class="tdBlackSpots">' . $item->BlackSpots . '</td>
        <td class="tdSoftShell">' . $item->SoftShell . '</td>
        <td class="tdBrokenDamaged">' . $item->BrokenDamaged . '</td>
        <td class="tdForeignMeterial">' . $item->ForeignMeterial . '</td>
        <td class="tdSendingQuantity">' . $item->SendingQuantity . '</td>
        <td class="tdSendingTime">' . $item->SendingTime . '</td>
        <td class="tdHangingMeat">' . $item->HangingMeat . '</td>
          <td class="tdAttachedLegs">' . $item->AttachedLegs . '</td>
           <td class="tdInspectedBy">' . $item->InspectedBy . '</td>
        <td class="tdVerifiedBy">' . $item->VerifiedBy . '</td>
      <td class="tdRemarks">' . $item->Remarks . '</td>
        <td>
            
       
        <button class="btnEdit" data-id="'.  $item->DeheadingID .'" data-arrival="' . $item->ArrivalDate . '" title="Edit" style="border: none; background: none; padding: 0; cursor: pointer;">
                            <i class="mdi mdi-pencil font-size-16 text-primary"></i>
                           </button>

                           <button class="btn btn-link text-danger btnActions btnDelete"
    style="position: relative; top: -3px;"
    data-id="'.$item->DeheadingID.'" data-toggle="tooltip" data-placement="top" title="Delete" value="" data-original-title="Delete">
    <i class="mdi mdi-trash-can font-size-16"></i>
</button>

                            </td>
                            
    </tr>';
    $i++;
}


}
 return $html;
}

