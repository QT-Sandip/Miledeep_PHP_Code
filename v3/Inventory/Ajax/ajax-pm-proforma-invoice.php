<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

// header('Content-Type: application/json');

$api = API_PM_PROFORMA_INVOICE;

$methodType = isset($_POST['methodType']) ? $_POST['methodType'] : '';

if ($methodType == "varieties") {

    $apiDepDropdowns = API_DEPENDANT_DROPDOWNS;
    $bodyContent = array(
        'Type' => "Variety"
    );
    $resDepDropdowns = CallAPI($apiDepDropdowns, "GET", "", $bodyContent);
    $resVariety = $resDepDropdowns->DependentDropdownsData->VarietyData;
    $resReadonlyDropdowns = $resDepDropdowns->DependentDropdownsData->ReadonlyDropdowns;
    $error = $resDepDropdowns->Error;
    $msg = $resDepDropdowns->Msg;

    $html = '
    <table class="popVarietyTable table table-hover table-bordered dataTable no-footer" style="width: 100%;">
        <thead>
            <th>S.No</th>
            <th>ShortName</th>
            <th>Description</th>
            <th>Grade</th>                                        
            <th>BrandName</th>
            <th>PackingStyle</th>  
            <th>BuyerSUPC</th>
            <th>Type</th>          
            <th>Actions</th>
        </thead>
        <tbody>';
    $i = 1;
    foreach ($resVariety as $item) {
        $html .= '
                <tr data-varietyid="' . $item->VarietyID . '">
                    <td>' . $i . '</td>
                    <td>' . $item->ShortName . '</td>
                    <td>' . $item->VarietyName . '</td>
                    <td>' . $item->Grade . '</td>                                        
                    <td>' . $item->Brand . '</td>
                    <td>' . $item->PackingStyle . '</td>
                    <td>' . $item->DeviSUPC . '</td>
                    <td>' . $item->TypeProduct . '</td>                   
                    <td>
                        <button  class="btn btn-success selectVariety" 
                        data-varietyid="' . $item->VarietyID . '" data-id="' . $item->VarietyID . '" 
                        data-po-grade-id="' . $item->PoGradeID . '" data-type="' . $item->TypeProduct . '" 
                        data-description="' . $item->VarietyName . '" data-freezing="' . $item->FreezingType . '"
                        data-grade="' . $item->Grade . '" data-packingstyle="' . $item->PackingStyleID . '" 
                        data-brand="' . $item->BrandID . '" 
                        value="' . $item->VarietyName . '" style="padding:3px 15px">Select</button>
                    </td>
                </tr>';
        $i++;
    }
    $html .= '
        </tbody>
    </table>';

    $res = array(
        "Error" => $resDepDropdowns->Error,
        "Msg" => $resDepDropdowns->Msg,
        "ReadonlyDropdowns" => $resDepDropdowns->DependentDropdownsData->ReadonlyDropdowns,
        "HtmlRes" => $html,
    );

    header('Content-Type: application/json');
    echo json_encode($res);
} else if ($methodType == 'save') {

    // $indent_id = $_POST['indent_id'];
    // $indents = explode(',',$indent_id);

    $proforma = $_POST['proforma_id'];

    $items = $_POST['item'];

    $txtVarietys = $_POST['txtVariety'];
    $txtVarietyIDs = $_POST['txtVarietyID'];
    $brands = $_POST['brand'];
    $grades = $_POST['grade'];
    $freezings = $_POST['freezing'];
    $packing_styles = $_POST['packing_style'];
    $baps = $_POST['bap'];
    $cartons = $_POST['cartons'];
    $weights = $_POST['weight'];

    $breakUpData = [];

    $count = count($txtVarietys);

    for ($i = 0; $i < $count; $i++) {
        if (empty($txtVarietys[$i])) continue;

        $breakUpData[] = [
            'proforma' => $proforma,
            'itemId'  => $items[$i],
            'txtVariety'   => $txtVarietys[$i],
            'txtVarietyID'   => $txtVarietyIDs[$i],
            'brand'       => $brands[$i],
            'grade'       => $grades[$i],
            'freezing'   => $freezings[$i],
            'packing_style' => $packing_styles[$i],
            'bap'      => $baps[$i],
            'carton'       => $cartons[$i],
            'weight'       => $weights[$i]

        ];
    }

    $bodyContent = array(
        "doc_no" => $_POST["doc_no"],
        "pdate" => $_POST["pdate"],
        "UnitCurrency" => $_POST["UnitCurrency"],
        "ExRate" => $_POST["ExRate"],
        "ConsigneeID" => $_POST["ConsigneeID"],
        "Company" => $_POST["Company"],
        "PortofLoading" => $_POST["PortofLoading"],
        "NotifyPartyID" => $_POST["NotifyPartyID"],
        "narration" => $_POST["narration"],
        "ByPayment" => $_POST["ByPayment"],
        "BuyerOrderNo" => $_POST["BuyerOrderNo"],
        "Shipmentdate" => $_POST["Shipmentdate"],
        "DestinationCountry" => $_POST["DestinationCountry"],
        "OriginCountry" => $_POST["OriginCountry"],
        "FinalDestination" => $_POST["FinalDestination"],
        "POAttachment" => $_POST["POAttachment"],
        "poAdate" => $_POST["poAdate"],
        "Packedby" => $_POST["Packedby"],
        "PortOfDischarge" => $_POST["PortOfDischarge"],
        "IsGlaze" => $_POST["IsGlaze"],
        "DeliveryTerms" => $_POST["DeliveryTerms"],
        "BuyerID" => $_POST["BuyerID"],
        "amountWords" => $_POST["amountWords"],
        "AdvanceAmount" => $_POST["AdvanceAmount"],
        "ExportReferenceNo" => $_POST["ExportReferenceNo"],
        "breakUp" => $breakUpData
    );

    $res = CallAPI($api, "POST", "", $bodyContent);
    // alert($res->Msg);
    // echo print_r($res);
    header('Content-Type: application/json');
    echo json_encode($res);
    exit;
} else if ($methodType == 'update') {

    $proforma = $_POST['ProformaDetailsId'];

    $txtVarietys = $_POST['txtVariety'] ;
    $txtVarietyIDs = $_POST['txtVarietyID'] ;
    $brands = $_POST['brand'] ;
    $grades = $_POST['grade'] ;
    $freezings = $_POST['freezing'] ;
    $packing_styles = $_POST['packing_style'] ;
    $baps = $_POST['bap'] ;
    $cartons = $_POST['cartons'] ;
    $weights = $_POST['weight'] ;
    $breakUpIDs = $_POST['BreakUpID'] ;  // <- fixed to match JS input

    $breakUpData = [];
    $count = count($txtVarietys);

    for ($i = 0; $i < $count; $i++) {
        if (empty($txtVarietys[$i])) continue;

        $breakUpData[] = [
            "BreakUpId" => intval($breakUpIDs[$i] ),  // now correctly uses sent ID
            "txtVariety" => $txtVarietys[$i],
            "txtVarietyID" => intval($txtVarietyIDs[$i] ),
            "brand" => intval($brands[$i] ),
            "grade" => $grades[$i] ,
            "freezing" => $freezings[$i] ,
            "packing_style" => intval($packing_styles[$i] ),
            "bap" => $baps[$i] ,
            "carton" => intval($cartons[$i] ),
            "weight" => floatval($weights[$i] )
        ];
    }

    $bodyContent = [
        "ProformaDetailsId" => $proforma,
        "methodType" => $_POST['methodType'] ,
        "doc_no" => $_POST['doc_no'] ,
        "pdate" => $_POST['pdate'] ,
        "UnitCurrency" => $_POST['UnitCurrency'] ,
        "ExRate" => $_POST['ExRate'] ,
        "ConsigneeID" => $_POST['ConsigneeID'] ,
        "Company" => $_POST['Company'] ,
        "PortofLoading" => $_POST['PortofLoading'] ,
        "NotifyPartyID" => $_POST['NotifyPartyID'] ,
        "narration" => $_POST['narration'] ,
        "ByPayment" => $_POST['ByPayment'] ,
        "BuyerOrderNo" => $_POST['BuyerOrderNo'] ,
        "Shipmentdate" => $_POST['Shipmentdate'] ,
        "DestinationCountry" => $_POST['DestinationCountry'] ,
        "OriginCountry" => $_POST['OriginCountry'] ,
        "FinalDestination" => $_POST['FinalDestination'] ,
        "POAttachment" => $_POST['POAttachment'] ,
        "poAdate" => $_POST['poAdate'] ,
        "Packedby" => $_POST['Packedby'] ,
        "PortOfDischarge" => $_POST['PortOfDischarge'] ,
        "IsGlaze" => $_POST['IsGlaze'] ,
        "DeliveryTerms" => $_POST['DeliveryTerms'] ,
        "BuyerID" => $_POST['BuyerID'] ,
        "amountWords" => $_POST['amountWords'] ,
        "AdvanceAmount" => $_POST['AdvanceAmount'] ,
        "ExportReferenceNo" => $_POST['ExportReferenceNo'] ,
        "breakUp" => $breakUpData
    ];

    $res = CallAPI($api, "POST", "", $bodyContent);
    header('Content-Type: application/json');
    echo json_encode($res);
    exit;
} elseif ($methodType == 'getSingle') {

    $price_db_id = $_POST['price_db_id']; // received from AJAX

    // Prepare the request body exactly as the API expects
    $bodyContent = array(
        "ProformaDetails_id" => $price_db_id,   //  match API parameter
        "methodType" => "singlePrice"
    );

    // Call the API
    $res = CallAPI($api, "GET", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode($res);
    exit;
} elseif ($methodType == 'delete') {

    $price_db_id = ($_POST['price_db_id']);

    $bodyContent = array(
        "ProformaDetails_id" => $price_db_id,  // FIXED
    );

    $res = CallAPI($api, "DELETE", "", $bodyContent);
    header('Content-Type: application/json');
    echo json_encode($res);
    exit;
} elseif ($methodType == 'load') {

    $res = CallAPI($api, "GET", "", "");
    $gstDetails = !empty($res->Data) ? $res->Data : [];
    $html = '<div class="card">
                        <div class="card-body">
                        <table id="tblDataTable" class="table table-hover table-bordered">
                        <thead class="thead-light">
                        <tr>
                            <th>S No</th>
                            <th>Doc No</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>';
    foreach ($gstDetails as $row) {
        $html .= '<tr>
                <td>' . $row->SNo . '</td>
                <td>' . $row->DocNo . '</td>
                <td>' . $row->Date . '</td>
                <td>
                     <button type="button" id="btnEdit"  name="btnEdit" data-id="' . $row->ProformaDetailsId . '" class="btn btn-link p-0 deleteBrkUp btnEdit">
                                            <i class="mdi mdi-pencil font-size-16 text-primary"></i>
                                        </button>
                                       <button type="button" id="btnDelete" name="btnDelete" 
    data-id="' . $row->ProformaDetailsId . '" 
    class="btn btn-link p-0 deleteBrkUp btnDelete">
        <i class="mdi mdi-delete font-size-16 text-danger"></i>
</button>

<button type="button"
        class="btn btn-link p-0 btnView"
        data-id="' . $row->ProformaDetailsId . '">
        <i class="mdi mdi-eye font-size-16 text-info"></i>
    </button>

                </td>
            </tr>';
    }
    $html .= '</tbody></table></div></div>';
    echo $html;
} elseif ($methodType == 'itemDetails') {

    $item_id = ($_POST['item_id']);
    $effectiveDate = date('Y-m-d', strtotime($_POST['effective_date']));

    $bodyContent = array(
        "GeneralItemId" => $item_id,
        "EffectiveDate" => $effectiveDate,
        "methodType" => "itemDetails"
    );
    $res = CallAPI($api, "GET", "", $bodyContent);
    header('Content-Type: application/json');
    echo json_encode($res);
    exit;
} elseif ($methodType == 'availableData') {

    $item_id = ($_POST['item_id']);

    $bodyContent = array(
        "GeneralItemId" => $item_id,
        "methodType" => "availableData"
    );
    $res = CallAPI($api, "GET", "", $bodyContent);
    header('Content-Type: application/json');
    echo json_encode($res);
    exit;
} elseif ($methodType == 'viewProforma') {
    $PiID = isset($_POST['PiID']) ? $_POST['PiID'] : 0;

    if (empty($PiID)) {
        echo json_encode([
            "status" => "fail",
            "Msg" => "Proforma ID missing",
            "Data" => []
        ]);
        exit;
    }

    $bodyContent = [
        "methodType" => "view",  // triggers getProformaInvoiceForView
        "PiID" => $PiID
    ];

    // Call the main API (same as save/load/delete)
    $res = CallAPI($api, "GET", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode($res);
    exit;
} else {
    echo json_encode(['status' => 'error', 'msg' => 'Invalid methodType']);
    exit;
}
