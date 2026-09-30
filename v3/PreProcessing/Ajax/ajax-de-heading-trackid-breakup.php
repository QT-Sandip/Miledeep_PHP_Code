<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$methodType = trim($_POST["methodType"]);
$userId = $_SESSION['UserID'];
$type = $_POST['type'];

function fnDataTableList($result)
{
    global $type;

    $resDetails = $result->Data;

    // $resTotal = $result->Data->Totals;
    $error = $result->Error;
    $msg = $result->Msg;

    $html =
        '<div class="card">
        <div class="card-body">
        <table id="tblDataTable" class="table table-hover table-bordered">
        <thead class="thead-light">
            <tr>
                <th>S.No</th>
                <th>De Heading Station</th>
                <th>SuperVisor</th>
                 <th>Variety</th>';

    $html .= ' <th>NoOfPeaces</th>
              <th>Created On</th>
               <th>Updated On</th>'.
               (empty($type) && $type != 'view' ?
                ' <th>Actions</th>' : '' ).
            '</tr>
        </thead>
        <tbody>';

    foreach ($resDetails as $item) {
        $html .=
            '<tr>
                    <td> ' . $item->SNo . '</td>
                    <td> ' . $item->UnitName . '</td>
                    <td> ' . $item->UserName . '</td>
                    <td> ' . $item->Name . '</td>';

        $html .= '  <td> ' . $item->NoOfPeaces . '</td>
                     <td> ' . $item->CreatedOn . '</td>
                    <td> ' . $item->UpdatedOn . '</td>'. 
                     (empty($type) && $type != 'view' ?
                    '<td>
                            <a href="javascript:void(0)" class="btn btn-link text-primary btnActions btnEdit"
                                data-id="' . $item->BreakUpId . '"
                                data-Noof_peaces="' . $item->NoOfPeaces . '"
                                data-variety_id="' . $item->VarietyID . '"
                                 data-center_id="' . $item->CenterId . '"
                                  data-supervisor_id="' . $item->SuperVisorId . '"
                                >
                                <i class="mdi mdi-pencil font-size-16"></i>
                            </a>
                            <button class="btn btn-link text-danger btnActions btnDelete" style="position: relative;top: -3px;" data-breakup_id="' . $item->BreakUpId . '"  
                                            data-toggle="tooltip" data-placement="top" title="Delete" value="" data-original-title="Delete">
                                                <i class="mdi mdi-trash-can font-size-16"></i></button>
                    </td>'
                    :''   ).
                '</tr>';
    }
    $html .= '
        </tbody>
        <tfoot>';

    $html .= '</tfoot>
    </table>
    </div>
    </div>';
    $response = array('htmlRes' => $html, 'Error' => $error, 'Msg' => $msg);

    return $response;
}

if (isset($methodType) && !empty($methodType)) {
    $api = API_DEHEADING_BREAKUP_DATA;
    if ($methodType == "getData") {
        $bodyContent = array(
            "DHID" => trim($_POST['DHID']),
        );
        $result = CallAPI($api, "GET", "", $bodyContent);

        if (!isset($error) || empty($error)) {

            header('Content-Type: application/json');
            echo json_encode(fnDataTableList($result));



        }
    } else if ($methodType == "FormPost") {
        $bodyContent = array(
            "DHID" => trim($_POST["dhid"]),
            "Variety" => trim($_POST["drpVarietyID"]),
            "NoofPieces" => trim($_POST["txtNoofPieces"]),
            "BreakupId" => trim($_POST["hdnID"]),
            "UserID" => $userId,
            "superVisor" => trim($_POST["superVisor"]),
            "center" => trim($_POST["center"]),
        );

        $result = CallAPI($api, "POST", "", $bodyContent);
        header('Content-Type: application/json');
        //  var_dump(json_encode($bodyContent)); exit;
        echo json_encode(fnDataTableList($result));

    } else if ($methodType == "deleteRow") {

        $bodyContent = array(
            "ID" => trim($_POST["ID"]),
            "DHID" => trim($_POST["dhid"])

        );

        $result = CallAPI($api, "DELETE", "", $bodyContent);
        header('Content-Type: application/json');
        echo json_encode(fnDataTableList($result));
    }
}
