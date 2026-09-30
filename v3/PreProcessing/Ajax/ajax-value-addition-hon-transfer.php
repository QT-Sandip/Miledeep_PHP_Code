<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$methodType = trim($_POST["methodType"]);

if (isset($methodType) && !empty($methodType)) {

    $api = API_VALUE_ADDITION_HON_TRANSFER;

    if ($methodType == "getBillsData") {

        $bodyContent = array(
            "TrackID" => trim($_POST["trackID"]),
        );
        $result = CallAPI($api, "GET", "", $bodyContent);
        $BillsData = $result->BillNumbers;
        header('Content-Type: application/json');
        echo json_encode($BillsData);
    } else if ($methodType == "getCountsData") {

        $bodyContent = array(
            "TrackID" => trim($_POST["trackID"]),
            "ArrivalID" => trim($_POST["arrivalID"]),
        );
        $result = CallAPI($api, "GET", "", $bodyContent);
        // echo json_encode($result, JSON_PRETTY_PRINT);
        // exit;
        $CountsData = $result->HonCount;
        $HtmlTable = fnTable($result);
        $ArrayData = [
            "CountsData" => $CountsData,
            "HtmlTable" => $HtmlTable,
        ];
        header('Content-Type: application/json');
        echo json_encode($ArrayData);
    } else if ($methodType == "postForm") {

        $bodyContentPost = array(
            "VATransferID" => trim($_POST["vaTransferID"]),
            "ArrivalDate" => trim($_POST["arrivalDate"]),
            "TrackID" => trim($_POST["trackID"]),
            "ArrivalPlant" => trim($_POST["arrivalPlant"]),
            "ArrivalID" => trim($_POST["arrivalCode"]),
            "HonCount" => trim($_POST["honCount"]),
            "VaWeight" => trim($_POST["weight"]),
            "TranferPlant" => trim($_POST["transferPlant"]),
            "TransferDate" => trim($_POST["transferDate"]),
            "HonWeight" => trim($_POST["honWeight"]),
            "HonRate" => trim($_POST["HonRate"]),
            "HonLotID" => trim($_POST["honLotID"]),
        );
        $result = CallAPI($api, "POST", "", $bodyContentPost);

        $CountsData = $result->HonCount;
        $VaWeight = $result->VaWeight;
        $Error = $result->Msg;
        $Msg = $result->Msg;
        $HtmlTable = fnTable($result);

        $ArrayData = [
            "VaWeight" => $VaWeight,
            "CountsData" => $CountsData,
            "HtmlTable" => $HtmlTable,
            "Error" => $Error,
            "Msg" => $Msg,
        ];
        header('Content-Type: application/json');
        echo json_encode($ArrayData);
    } else if ($methodType == "getVaWeight") {
        $bodyContent = array(
            "Count" => trim($_POST["count"]),
            "TrackID" => trim($_POST["trackID"]),
            "ArrivalID" => trim($_POST["arrivalID"]),
            "LotID" => trim($_POST["lotId"]),
        );
        $result = CallAPI($api, "GET", "", $bodyContent);
        $VaWeight = $result->VaWeight;
        $ArrayData = [
            "VaWeight" => $VaWeight,
        ];
        header('Content-Type: application/json');
        echo json_encode($ArrayData);
    } else if ($methodType == "delete") {
        $bodyContent = array(
            "VATransferID" => trim($_POST["vaTransferID"]),
            "TrackID" => trim($_POST["trackID"]),
            "ArrivalID" => trim($_POST["arrivalCode"]),
            "Count" => trim($_POST["honCount"]),
        );
        $result = CallAPI($api, "DELETE", "", $bodyContent);
        $CountsData = $result->HonCount;
        $HtmlTable = fnTable($result);
        $VaWeight = $result->VaWeight;
        $Error = $result->Msg;
        $Msg = $result->Msg;
        $ArrayData = [
            "CountsData" => $CountsData,
            "HtmlTable" => $HtmlTable,
            "VaWeight" => $VaWeight,
            "Error" => $Error,
            "Msg" => $Msg,
        ];
        header('Content-Type: application/json');
        echo json_encode($ArrayData);
    }
} else {

    $api = API_RM_PRODUCTION_TRACK_LIST;

    $bodyContent = array(
        'ArrivalDate' => trim($_POST["arrivalDate"]),
        'ReceivingPlantID' => trim($_POST['plantID']),
        'Type' => trim($_POST["type"])
    );
    $resList = CallAPI($api, "GET", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode($resList);
}

function fnTable($result)
{
    $VaTransferData = $result->VaTransferData;

    $html = '';
    if (isset($VaTransferData) && !empty($VaTransferData)) {
        $html .= '<table id="tblDataTable" class="table table-hover table-bordered">
        <thead class="thead-light">
            <tr>
                <th style="width: 50px;">S.No</th>
                <th style="min-width: 100px;">Arrival Date</th>
                <th style="min-width: 100px;">Bill Date</th>
                <th style="min-width: 100px;">Bill Number</th>
                <th style="min-width: 100px;">Production Plant </th>
                <th style="min-width: 100px;">Purchase Count </th>
                <th style="min-width: 100px;">Purchase Weight </th>
                <th style="min-width: 100px;">Rm Production plant </th>
                <th style="min-width: 100px;">Transfer Date</th>
                <th style="min-width: 100px;">VA Weight Qty</th>
                <th style="min-width: 100px;">Actions</th>
            </tr>
        </thead>
        <tbody>';
        $i = 0;
        foreach ($VaTransferData as $Item) {
            $html .= '<tr>
                <td>' . ++$i . '</td>	
                <td>' . $Item->ArrivalDate . '</td>
                <td>' . $Item->BillDate . '</td>
                <td>' . $Item->LotNumber . '</td>	
                <td>' . $Item->ArrivalPlantName . '</td>	
                <td>' . $Item->HonCount . '</td>	
                <td>' . $Item->HonWeight . '</td>	
                <td>' . $Item->TranferPlantName . '</td>
                <td>' . $Item->TransferDate . '</td>	
                <td>' . $Item->VaWeight . '</td>	
                <td>
                    <button type="button" name="btnDelete" data-vaTransferID="' . $Item->VATransferID . '" data-trackID="' . $Item->TrackID . '" data-arrivalID="' . $Item->ArrivalCode . '" data-honcount="' . $Item->HonCount . '" data-honlotid="' . $Item->HonLotID . '"  class="btn btn-link p-0 btnDelete"  >
                        <i class="mdi mdi-delete font-size-16 text-danger"></i>
                    </button>
                </td>	
            </tr>';
        }
        $html .= '</tbody>
        </table>';
    } else {
        $html .= "<div class='text-center mt-3 mb-3 text-bold'>NOTE : No Data Available</div>";
    }
    return $html;
}


// <td>
//     <button type="button" name="btnEdit" data-vaTransferID="' . $Item->VATransferID . '" data-transferDate="' . $Item->TransferDateTwo . '" class="btn btn-link p-0 btnEdit"  >
//         <i class="mdi mdi-pencil font-size-16 text-primary"></i>
//     </button>
//     <button type="button" name="btnDelete" data-vaTransferID="' . $Item->VATransferID . '" data-trackID="' . $Item->TrackID . '" data-arrivalID="' . $Item->ArrivalCode . '" class="btn btn-link p-0 btnDelete"  >
//         <i class="mdi mdi-delete font-size-16 text-danger"></i>
//     </button>
// </td>	