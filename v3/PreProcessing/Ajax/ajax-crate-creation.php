<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$methodType = trim($_POST["methodType"]);
$SaveType = trim($_POST["SaveType"]);



function fnDataTableList($result)
{
    $SaveType = trim($_POST["SaveType"]);
    $pageType = trim($_POST["pageType"]);

    if ($SaveType == "PlantCheck") {
        $VarietyOrCrateNo = "Variety";
        $NoOfPiecesOrWeight = "No.of Pieces";
    } else if ($SaveType == "Crate") {
        $VarietyOrCrateNo = "Crate Number";
        $NoOfPiecesOrWeight = "Received Crate Weight";

    }

    $resCrateBreakUpDetails = $result->Data->PlantCheckDetails;
    $resCrateDetails = $result->Data;

    // $resTotal = $result->Data->Totals;
    $error = $result->Error;
    $msg = $result->Msg;

    $html =
        '<div class="card">
        <div class="card-body">
        <table id="tblDataTable" class="table table-hover table-bordered">
        <thead class="thead-light">
            <tr>
                <th>S.No</th>';
                if ($SaveType == "PlantCheck") {
                    $html .= ' <th>Supervisor</th>';
                }
                $html .= '
                <th>' . $VarietyOrCrateNo . '</th>';
                 

                $html .= ' 
                <th>' . $NoOfPiecesOrWeight . '</th>'.
                ($pageType != 'view' ?
                 '<th>Actions</th>'
                 : '' ).'
            </tr>
        </thead>
        <tbody>';

    foreach ($resCrateBreakUpDetails as $item) {
        $html .=
            '<tr>
                    <td> ' . $item->SNo . '</td>';
                    if ($SaveType == "PlantCheck") {
                        $html .= '  <td> ' . $item->UserName . '</td>';
                    }
                    $html .= '
                    <td> ' . $item->VarietyOrCrateNo . '</td>';

                    $html .= '  
                    <td> ' . $item->NoOfPiecesOrWeight . '</td>'
                       . ($pageType != 'view' ?
                    '<td>
                        <button class="btn btn-link text-danger btnActions btnDelete" style="position: relative;top: -3px;' . ($display == "show") . '" data-breakup-id="' . $item->CrateDataID . '"  
                                                data-toggle="tooltip" data-placement="top" title="Delete" value="" data-original-title="Delete">
                                                    <i class="mdi mdi-trash-can font-size-16"></i></button>'
                                                    :''
                                                    ).'

                    </td>
                </tr>';
    }
    $html .= '
        </tbody>
        <tfoot>';

    $html .= '</tfoot>
    </table>
    </div>
    </div>';
    $response = array('htmlRes' => $html, 'FormData' => $resCrateDetails, 'Error' => $error, 'Msg' => $msg);

    return $response;
}

if (isset($methodType) && !empty($methodType)) {

    $api = API_CREATE_CRATE_DATA;


    if ($methodType == "getLotData") {
        $bodyContent = array(
            // "Variety" => trim($_POST['Variety']),
            "LotID" => trim($_POST['LotID']),
            "ArrivalID" => trim($_POST['ArrivalID']),
            "SaveType" => $SaveType,


        );
        $result = CallAPI($api, "GET", "", $bodyContent);

        if (!isset($error) || empty($error)) {

            if ($SaveType != "save") {
                header('Content-Type: application/json');
                echo json_encode(fnDataTableList($result));
            } else {
                header('Content-Type: application/json');
                echo json_encode(array('FormData' => $result->Data, 'Error' => $error, 'Msg' => $msg));
            }



        }
    } else if ($methodType == "FormPost") {
        $bodyContent = array(
            "LotID" => trim($_POST["lotID"]),
            "Variety" => trim($_POST["drpVarietyID"]),
            "ArrivalID" => trim($_POST["arrivalID"]),
            "PlantID" => trim($_POST["arrivalPlantId"]),
            "NoOfPieces" => trim($_POST["txtNoofPieces"]),
            "CrateNo" => trim($_POST["txtCrateNumber"]),
            "Weight" => trim($_POST["txtReceivedCrateWeight"]),
            "ActualCrateWeight" => trim($_POST["txtActualCrateWeight"]),
            "SaveType" => $SaveType,
            // "BreakUpdate" => false
             "drpSupervisor" => trim($_POST["drpSupervisor"]),
        );

        $result = CallAPI($api, "POST", "", $bodyContent);
        header('Content-Type: application/json');
        //  var_dump(json_encode($bodyContent)); exit;
        echo json_encode(fnDataTableList($result));

    } else if ($methodType == "deleteRow") {

        $bodyContent = array(
            "CrateDataID" => trim($_POST["CrateDataID"]),
            "LotID" => trim($_POST["LotID"]),
            "SaveType" => $SaveType

        );

        $result = CallAPI($api, "DELETE", "", $bodyContent);
        header('Content-Type: application/json');
        echo json_encode(fnDataTableList($result));
    }




}
