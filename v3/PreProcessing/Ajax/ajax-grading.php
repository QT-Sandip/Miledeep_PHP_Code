<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$methodType = trim($_POST["methodType"]);

if (isset($methodType) && !empty($methodType)) {

    $api = API_GRADING_DETAILS;
    //  exit;

    if ($methodType == "get") {

        $ArrivalID = trim(implode(",", $_POST["ArrivalID"]));

        $bodyContentPost = array(
            "ArrivalID" => $ArrivalID
        );



        $result = CallAPI($api, "GET", "", $bodyContentPost);

        // echo json_encode($result, JSON_PRETTY_PRINT); exit;

    } else if ($methodType == "post") {

        $ArrivalDate = trim($_POST["ArrivalDate"]);
        $ArrivalID = trim($_POST["ArrivalID"]);
        $MixIds = trim($_POST["MixIds"]);
        $AriavalLotCode = trim($_POST["drpLotCode"]);
        $ProductionPlant = trim($_POST["drpProductionPlant"]);
        $Center = trim($_POST["drpCenter"]);
        $TransactionDate = trim($_POST["txtTransactionDate"]);
        $GradingMachine = trim($_POST["drpGradingMachine"]);
        $StartTime = trim($_POST["txtStartTime"]);
        $EndTime = trim($_POST["txtEndTime"]);
        $BreakTime = trim($_POST["txtBreakTime"]);
        $Defects = trim($_POST["drpDefects"]);
        $Variety = trim($_POST["drpVariety"]);
        $Grade = trim($_POST["drpGrade"]);
        $ArrivalCount = trim($_POST["txtArrivalCount"]);
        $ArrivalWeight = trim($_POST["txtArrivalWeight"]);
        $NoOfCrates = trim($_POST["txtNoOfCrates"]);
        $Remarks = trim($_POST["txtRemarks"]);

        $bodyContentPost = array(
            "ArrivalID" => $ArrivalID,
            "Plant" => $ProductionPlant,
            "Center" => $Center,
            "TransactionDate" => $TransactionDate,
            "Count" => $ArrivalCount,
            "Weight" => $ArrivalWeight,
            "MachineID" => $GradingMachine,
            "StartTime" => $StartTime,
            "EndTime" => $EndTime,
            "BreakTime" => $BreakTime,
            "MixIDs" => $MixIds,
            "NoCrates" => $NoOfCrates,
            "Defect" => $Defects,
            "Variety" => $Variety,
            "Grade" => $Grade,
            "Remarks" => $Remarks
        );

        // echo json_encode($bodyContentPost, JSON_PRETTY_PRINT);
        // exit;

        $result = callAPI($api, "POST", "", $bodyContentPost);

        // echo json_encode($result, JSON_PRETTY_PRINT);
        // exit();

    } else if ($methodType == "delete") {

        $ArrivalID = $_POST["ArrivalID"];
        $BreakUpID = $_POST["BreakUpID"];
        $Type = $_POST["Type"];

        $bodyContent = array(
            "ArrivalID" => $ArrivalID,
            "BreakUpID" => $BreakUpID,
            "Type" => $Type
        );

        // echo json_encode($bodyContent, JSON_PRETTY_PRINT);

        $result = CallAPI($api, "DELETE", "", $bodyContent);
    }
    $error = $result->Error;
    $msg = $result->Msg;
    $Data = end($result->Data->GradingData);
    $List = $result->Data->GradingData;

    // echo json_encode($List, JSON_PRETTY_PRINT);
    // exit;

    if (!isset($error) || empty($error)) {
        $htmlResponse = '
      <!--<div class="card">
          <div class="card-body">-->
              <table id="tblDataTable" class="table table-hover table-bordered">
                 <thead class="thead-light">
                    <tr>
                      <th style="width:50px;">S.No</th>
                      <th>Transaction Date</th>
                      <th>Arrival Code </th>
                      <th>Production Plant</th>
                      <th>Center</th>
                      <th>Grading Machine</th>
                      <th>Start Time</th>
                      <th>End Time</th>
                      <th>Break Time(Minutes)</th>
                      <th>HL Count</th>
                      <th>HL Weight</th>
                      <th>No Of Crates</th>
                      <td>Remarks</td>
                      <th style="width: 60px;">Actions</th>
                    </tr>
                  </thead>
                  <tbody>';
        $i = 1;
        foreach ($List as $item) {
            $htmlResponse .= '
                      <tr>
                        <td>' . $item->SNo . '</td>
                        <td>' . $item->TransactionDate . '</td>
                        <td>' . $item->LotNo . '</td>
                        <!-- <td>$item->Defect</td> -->
                        <td>' . $item->Plant . '</td>
                        <td>' . $item->Center . '</td>
                        <td>' . $item->MachineName . '</td>
                        <td>' . $item->StartTime . '</td>
                        <td>' . $item->EndTime . '</td>
                        <td>' . $item->BreakTime . '</td>
                        <td>' . $item->Count . '</td>
                        <td>' . $item->Weight . '</td>
                        <th>' . $item->NoCrates . '</th>
                        <td>' . $item->Remarks . '</td>
                        <td>
                          <button type="button" id="btnDelete" name="btnDelete" data-arrivalid="' . $item->ArrivalID . '" data-arrivalbrkupid="' . $item->BreakUpID . '" class="btn btn-link p-0 deleteBrkUp" ' . $DeleteAccess . ' >
                            <i class="mdi mdi-delete font-size-16 text-danger"></i>
                          </button>
                        </td>
                      </tr>';
            $i++;
        }
        $htmlResponse .= '
                  </tbody>
                    <tfoot>
                      <tr >
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
                        <td>' . $result->Data->Totals->TotalWeight . '</td>
                        <td></td>
                        <td></td>
                      </tr>
                    </tfoot>
                </table>
          <!--</div>
      </div>-->';
    }

    $response = array('html' => $htmlResponse, 'json' => $Data, "Error" => $error, "Msg" => $msg);
    echo json_encode($response);
} else {

    $api = API_RM_PRODUCTION_LIST;

    $ArrivalDate = trim($_POST["ArrivalDate"]);
    $Type = trim($_POST["Type"]);

    $bodyContent = array(
        'ArrivalDate' => $ArrivalDate,
        'Type' => $Type,
        'ArrivalPlant' => "",
        'Supplier' => "",
        'PsId' => ""
    );

    $resList = CallAPI($api, "GET", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode($resList);
}
