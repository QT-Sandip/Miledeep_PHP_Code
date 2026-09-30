<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
require ROOT . 'Libraries/PHPExcel/Classes/PHPExcel.php'; // Adjust path if needed

$methodType = trim($_POST["methodType"]);

if (isset($methodType) && !empty($methodType)) {

    $api = API_GRADING_INSPECTION_DETAILS;

    if ($methodType == "post" || $methodType == "update") {

        // Extract POST variables from AJAX request
        $ArrivalDate = trim($_POST["ArrivalDate"]);
        $ArrivalID = trim($_POST["ArrivalID"]);
        $InspectionID = trim($_POST["InspectionID"]);
        $BatchCode = trim($_POST["BatchCode"]);
        $Variety = trim($_POST["Variety"]);
        $Grade = trim($_POST["Grade"]);
        $GeneralAppearance = trim($_POST["GeneralAppearance"]);
        $Color = trim($_POST["Color"]);
        $Freshness = trim($_POST["Freshness"]);
        $Odour = trim($_POST["Odour"]);
        $Texture = trim($_POST["Texture"]);
        $Temperature = trim($_POST["Temperature"]);
        $CountPerLb = trim($_POST["CountPerLb"]);
        $BigPieceperGram = trim($_POST["BigPieceperGram"]);
        $SmallPieceperGram = trim($_POST["SmallPieceperGram"]);
        $Uniformity = trim($_POST["Uniformity"]);
        $TotalPiecesperGram = trim($_POST["TotalPiecesperGram"]);
        $Deterioration = trim($_POST["Deterioration"]);
        $Discolouration = trim($_POST["Discolouration"]);
        $BlackSpots = trim($_POST["BlackSpots"]);
        $SoftShell = trim($_POST["SoftShell"]);
        // $HONQuantity = trim($_POST["HONQuantity"]);
        $HLQuantity = trim($_POST["HLQuantity"]);
        // $Yield = trim($_POST["Yield"]);
        $HangingMeat = trim($_POST["HangingMeat"]);
        $VerifiedBy = trim($_POST["VerifiedBy"]);
        $InspectedBy = trim($_POST["InspectedBy"]);
        $Remarks = trim($_POST["Remarks"]);
        $methodType = trim($_POST["methodType"]);

        $bodyContentPost = array(
            "ArrivalDate" => date('Y-m-d', strtotime($ArrivalDate)),
            "ArrivalID" => $ArrivalID,
            "InspectionID" => $InspectionID,
            "BatchCode" => $BatchCode,
            "Variety" => $Variety,
            "Grade" => $Grade,
            "GeneralAppearance" => $GeneralAppearance,
            "Color" => $Color,
            "Freshness" => $Freshness,
            "Odour" => $Odour,
            "Texture" => $Texture,
            "Temperature" => $Temperature,
            "CountPerLb" => $CountPerLb,
            "BigPieceperGram" => $BigPieceperGram,
            "SmallPieceperGram" => $SmallPieceperGram,
            "Uniformity" => $Uniformity,
            "TotalPiecesperGram" => $TotalPiecesperGram,
            "Deterioration" => $Deterioration,
            "Discolouration" => $Discolouration,
            "BlackSpots" => $BlackSpots,
            "SoftShell" => $SoftShell,
            // "HONQuantity" => $HONQuantity,
            "HLQuantity" => $HLQuantity,
            // "Yield" => $Yield,
            "HangingMeat" => $HangingMeat,
            "VerifiedBy" => $VerifiedBy,
            "InspectedBy" => $InspectedBy,
             "Remarks" => $Remarks,
            "Type" => $methodType
        );
        // var_dump( $bodyContentPost); exit;
        $resList = CallAPI($api, "POST", "", $bodyContentPost);
        header('Content-Type: application/json');
        echo json_encode($resList);
    } else if ($methodType == "delete") {
        $InspectionID = $_POST["InspectionID"];

        $bodyContentPost = array(
            "InspectionID" => $InspectionID,
            'Type' => 'delete',
        );
        $resList = CallAPI($api, "DELETE", "", $bodyContentPost);

        header('Content-Type: application/json');
        echo json_encode($resList);
    } else if ($methodType == "getLotDetails") {
        $ArrivalDate = trim($_POST["ArrivalDate"]);

        $bodyContent = array(
            'ArrivalDate' => $ArrivalDate,
            'Type' => 'getLotDetails',
        );
        $resList = CallAPI($api, "GET", "", $bodyContent);

        header('Content-Type: application/json');
        echo json_encode($resList);
    }
  else if ($methodType == "getGrade") {
       $ArrivalDate = trim($_POST["ArrivalDate"]);
    $batchCode = trim($_POST['batchCode']);
    $bodyContent = array(
        'Type' => 'getGrade',
         'ArrivalDate' => $ArrivalDate,
      'batchCode' => $batchCode
    );

    $resList = CallAPI($api, "GET", "", $bodyContent);
    header('Content-Type: application/json');
    echo json_encode($resList);
}
    
    else if ($methodType == "getReportLotDetails") {
        $ArrivalDate = trim($_POST["ArrivalDate"]);

        $bodyContent = array(
            'ArrivalDate' => $ArrivalDate,
            'Type' => 'getReportLotDetails',
        );
        $resList = CallAPI($api, "GET", "", $bodyContent);

        header('Content-Type: application/json');
        echo json_encode($resList);
    } else if ($methodType == "getReportDetails") {
        $ArrivalDate = isset($_POST["ArrivalDate"]) ? trim($_POST["ArrivalDate"]) : trim($_GET["ArrivalDate"]);

        $bodyContent = array(
            'ArrivalDate' => $ArrivalDate,
            'Type' => 'getReportDetails',
        );

        $resList = CallAPI($api, "GET", "", $bodyContent);

        if (isset($resList->Error) && $resList->Error == false) {

            $content = '<div class="card">
        <div class="card-body">
            <table id="tblDataTable" ' . $border . ' class="table table-hover table-bordered">';
            // echo json_encode($resList);

            $content .= '
            <thead class="thead-light">
                    <tr>
                        <th style="min-width:150px;">S.NO</th>
                        <th style="min-width:150px;">Arrival Date</th>
                        <th style="min-width:150px;">Batch Number</th>
                        <th style="min-width:150px;">Variety</th>
                        <th style="min-width:150px;">Grade</th>
                        <th style="min-width:150px;">General Appearance</th>
                        <th style="min-width:150px;">Color</th>
                        <th style="min-width:150px;">Freshness</th>
                        <th style="min-width:150px;">Odour</th>
                        <th style="min-width:150px;">Texture</th>
                        <th style="min-width:150px;">Temperature</th>
                        <th style="min-width:150px;">Count Per Lb</th>
                        <th style="min-width:150px;">Big Piece</th>
                        <th style="min-width:150px;">Small Piece</th>
                        <th style="min-width:150px;">Uniformity</th>
                        <th style="min-width:150px;">Total Pieces</th>
                        <th style="min-width:150px;">Deterioration</th>
                        <th style="min-width:150px;">Discolouration</th>
                        <th style="min-width:150px;">Black Spots</th>
                        <th style="min-width:150px;">Soft Shell</th>
                        <th style="min-width:150px;">HL Quantity</th>
                        <th style="min-width:150px;">Hanging Meat</th>
                   </tr>
                    </thead>
            <tbody>';

            $i = 1;
            foreach ($resList->Data as $row) {
                $content .= '
                
                <tr>
                        <td>' . $i . '</td>
                        <td>' . date("d-m-Y", strtotime($row->ArrivalDate)) . '</td>
                        <td>' . $row->BatchCode . '</td>
                        <td>' . $row->Variety . '</td>
                        <td>' . $row->Grade . '</td>
                        <td>' . $row->GeneralAppearance . '</td>
                        <td>' . $row->Color . '</td>
                        <td>' . $row->Freshness . '</td>
                        <td>' . $row->Odour . '</td>
                        <td>' . $row->Texture . '</td>
                        <td>' . $row->Temperature . '</td>
                        <td>' . $row->CountPerLb . '</td>
                        <td>' . $row->BigPieceperGram . '</td>
                        <td>' . $row->SmallPieceperGram . '</td>
                        <td>' . $row->Uniformity . '</td>
                        <td>' . $row->TotalPiecesperGram . '</td>
                        <td>' . $row->Deterioration . '</td>
                        <td>' . $row->Discolouration . '</td>
                        <td>' . $row->BlackSpots . '</td>
                        <td>' . $row->SoftShell . '</td>
                
                        <td>' . $row->HLQuantity . '</td>
                
                        <td>' . $row->HangingMeat . '</td>
                </tr>';
                $i++;
            }

            $content .= '</tbody></table></div></div>';
        }

        echo $content;
    } else if ($methodType == "get") {
        $bodyContent = array(
            'Type' => 'get',
            'ArrivalDate' => isset($_POST['ArrivalDate']) ? trim($_POST['ArrivalDate']) : '',
        );
        $resList = CallAPI($api, "GET", "", $bodyContent);
        // echo var_dump($resLis);
        // Changed from array access to object property access
        if (isset($resList->Error) && $resList->Error == false) {
            $content = '
            <div class="table-responsive" style="overflow-x: auto;">
                <table id="tblDataTable" class="table table-hover table-bordered" style="width: 100%; white-space: nowrap;">
                 <thead class="thead-light">
                    <tr>
                        <th style="min-width:150px;">S.NO</th>
                        <th style="min-width:150px;">Arrival Date</th>
                        <th style="min-width:150px;">Batch Number</th>
                        <th style="min-width:150px;">Variety</th>
                        <th style="min-width:150px;">Grade</th>
                        <th style="min-width:150px;">General Appearance</th>
                        <th style="min-width:150px;">Color</th>
                        <th style="min-width:150px;">Freshness</th>
                        <th style="min-width:150px;">Odour</th>
                        <th style="min-width:150px;">Texture</th>
                        <th style="min-width:150px;">Temperature</th>
                        <th style="min-width:150px;" >Count Per Lb</th>
                        <th style="min-width:150px;">Big Piece </th>
                        <th style="min-width:150px;">Small Piece </th>
                        <th style="min-width:150px;">Uniformity</th>
                        <th style="min-width:150px;">Total Pieces</th>
                        <th style="min-width:150px;">Deterioration</th>
                        <th style="min-width:150px;">Discolouration</th>
                        <th style="min-width:150px;">Black Spots</th>
                        <th style="min-width:150px;">Soft Shell</th>
                       
                        <th style="min-width:150px;">HL Quantity</th>
                        
                        <th style="min-width:150px;">Hanging Meat %</th>
                          <th style="min-width:150px;">Inspected By</th>
                         <th style="min-width:150px;">Verified By </th>
                           <th style="min-width:150px;">Remarks </th>
                        <th style="width:180px;">Actions</th>
                        
                   </tr>
                    </thead>
                    <tbody>';
            $i = 1;
            foreach ($resList->Data as $row) {
                $content .= '
                    <tr>
                        <td>' . $i . '</td>
                        <td>' . date("d-m-Y", strtotime($row->ArrivalDate)) . '</td>
                        <td>' . $row->BatchCode . '</td>
                        <td>' . $row->Variety . '</td>
                        <td>' . $row->Grade . '</td>
                        <td>' . $row->GeneralAppearance . '</td>
                        <td>' . $row->Color . '</td>
                        <td>' . $row->Freshness . '</td>
                        <td>' . $row->Odour . '</td>
                        <td>' . $row->Texture . '</td>
                        <td>' . $row->Temperature . '</td>
                        <td>' . $row->CountPerLb . '</td>
                        <td>' . $row->BigPieceperGram . '</td>
                        <td>' . $row->SmallPieceperGram . '</td>
                        <td>' . $row->Uniformity . '</td>
                        <td>' . $row->TotalPiecesperGram . '</td>
                        <td>' . $row->Deterioration . '</td>
                        <td>' . $row->Discolouration . '</td>
                        <td>' . $row->BlackSpots . '</td>
                        <td>' . $row->SoftShell . '</td>
                       
                        <td>' . $row->HLQuantity . '</td>
                       
                        <td>' . $row->HangingMeat . '</td>
                         <td>' . $row->InspectedBy . '</td>
                         <td>' . $row->VerifiedBy . '</td>
                        <td>' . $row->Remarks . '</td>
                        
                        
                        <td>
                            <button class="btn txt-primary btn-sm btnEdit" data-id="' . $row->InspectionID . '" data-arrivalid="' . $row->ArrivalId . '"><i class="mdi mdi-pencil font-size-16 text-primary"></i></button>
                            <button class="btn txt-danger btn-sm btnDelete" data-id="' . $row->InspectionID . '"data-arrivalid="' . $row->ArrivalId . '"><i class="mdi mdi-delete font-size-16 text-danger"></i></button>
                        </td>
                    </tr>
                ';
                $i++;
            }
            $content .= '
                    </tbody>
                </table>
            </div>';

            header('Content-Type: application/json');
            echo json_encode($response = array(
                'Error' => false,
                'Msg' => $resList->Msg,  // Changed to object property access
                'html' => $content
            ));
        }
    }
} else {
    $api = API_GRADING_INSPECTION_DETAILS;

    $ArrivalDate = trim($_GET["ArrivalDate"]);
    // $BatchCode = trim($_GET["Batch"]);

    $bodyContent = array(
        'ArrivalDate' => $ArrivalDate,
        //'BatchCode' => $BatchCode,
        'Type' => 'getReportDetails',
    );
    $resList = CallAPI($api, "GET", "", $bodyContent);

    if (isset($resList->Error) && $resList->Error == false) {

        $content = '<div class="card">
        <div class="card-body">
            <table id="tblDataTable" border="1" class="table table-hover table-bordered style="text-align:center; width:100%; border-collapse:collapse;">';

        if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
            $content .= '
                <tr><td colspan="22" align="center"><b>COASTAL AQUA PRIVATE LIMITED</b></td></tr>
                <tr><td colspan="22" align="center"><b>PATHARLAGADDA, KAKINADA</b></td></tr>
                <tr><td colspan="22" align="center"><b>GRADING INSPECTION REPORT</b></td></tr>
                <tr>
                 
                
                </tr>';
        }
        // echo json_encode($List);

        $content .= '
            <thead class="thead-light">
                  <tr>
                <th style="text-align:center;">S.NO</th>
                <th style="text-align:center;">Arrival Date</th>
                <th style="text-align:center;">Batch Number</th>
                <th style="text-align:center;">Variety</th>
                <th style="text-align:center;">Grade</th>
                <th style="text-align:center;">General Appearance</th>
                <th style="text-align:center;">Color</th>
                <th style="text-align:center;">Freshness</th>
                <th style="text-align:center;">Odour</th>
                <th style="text-align:center;">Texture</th>
                <th style="text-align:center;">Temperature</th>
                <th style="text-align:center;">Count Per Lb</th>
                <th style="text-align:center;">Big Piece</th>
                <th style="text-align:center;">Small Piece</th>
                <th style="text-align:center;">Uniformity</th>
                <th style="text-align:center;">Total Pieces</th>
                <th style="text-align:center;">Deterioration</th>
                <th style="text-align:center;">Discolouration</th>
                <th style="text-align:center;">Black Spots</th>
                <th style="text-align:center;">Soft Shell</th>
                <th style="text-align:center;">HL Quantity</th>
                <th style="text-align:center;">Hanging Meat %</th>
           </tr>
        </thead>
        <tbody>';
        $i = 1;
        foreach ($resList->Data as $row) {
            $content .= '
                
               <tr>
                <td style="text-align:center;">' . $i . '</td>
                <td style="text-align:center;">' . date("d-m-Y", strtotime($row->ArrivalDate)) . '</td>
                <td style="text-align:center;">' . $row->BatchCode . '</td>
                <td style="text-align:center;">' . $row->Variety . '</td>
                <td style="text-align:center; mso-number-format:\'\\@\';">' . htmlspecialchars((string)$row->Grade) . '</td>
                <td style="text-align:center;">' . $row->GeneralAppearance . '</td>
                <td style="text-align:center;">' . $row->Color . '</td>
                <td style="text-align:center;">' . $row->Freshness . '</td>
                <td style="text-align:center;">' . $row->Odour . '</td>
                <td style="text-align:center;">' . $row->Texture . '</td>
                <td style="text-align:center;">' . $row->Temperature . '</td>
                <td style="text-align:center;">' . $row->CountPerLb . '</td>
                <td style="text-align:center;">' . $row->BigPieceperGram . '</td>
                <td style="text-align:center;">' . $row->SmallPieceperGram . '</td>
                <td style="text-align:center;">' . $row->Uniformity . '</td>
                <td style="text-align:center;">' . $row->TotalPiecesperGram . '</td>
                <td style="text-align:center;">' . $row->Deterioration . '</td>
                <td style="text-align:center;">' . $row->Discolouration . '</td>
                <td style="text-align:center;">' . $row->BlackSpots . '</td>
                <td style="text-align:center;">' . $row->SoftShell . '</td>
                <td style="text-align:center;">' . $row->HLQuantity . '</td>
                <td style="text-align:center;">' . $row->HangingMeat . '</td>
            </tr>';
        $i++;
    }

    $content .= '</tbody></table></div></div>';
}


    if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
        $filename = 'Grading_Inspection_Report.xls';
        header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=" . $filename);
    }

   

if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {

    // Collect names
    $verifiedByArr = [];
    $inspectedByArr = [];
    foreach ($resList->Data as $row) {
        if (!empty($row->VerifiedBy)) {
            $verifiedByArr[] = trim($row->VerifiedBy);
        }
        if (!empty($row->InspectedBy)) {
            $inspectedByArr[] = trim($row->InspectedBy);
        }
    }

    // Remove duplicates
    $verifiedByArr = array_unique($verifiedByArr);
    $inspectedByArr = array_unique($inspectedByArr);

    // Implode with commas
    $verifiedByStr  = implode(", ", $verifiedByArr);
    $inspectedByStr = implode(", ", $inspectedByArr);

    // Footer table with bold data also
    $content .= '
        <br><br>
        <table style="width:100%; font-family:Arial, sans-serif; font-size:12px; border-collapse:collapse;">

        <tr>
                <td colspan="8" style="border:none; width:50%;"><b>Inspected By :</b> <b>' . $inspectedByStr . '</b></td>
            </tr>
            
            <tr>
                <td colspan="8" style="border:none; width:50%;"><b>Verified By :</b> <b>' . $verifiedByStr . '</b></td>
            </tr>
            
        </table>';
}

echo $content;
}