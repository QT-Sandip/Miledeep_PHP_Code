<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api =API_CONTRACEABILITY_REPORT;


$PageNo = trim($_POST["pageNo"]);
$bodyContent = array(
    'FromDate' => trim($_POST["fromDate"]),
    'ToDate' => trim($_POST["toDate"]),
    'PageNo' => trim($_POST["pageNo"]),
    'SearchKey' => trim($_POST["searchKey"]),
    'LoadAll' => trim($_POST["loadAll"]),
);
$result = CallAPI($api, "GET", "", $bodyContent);

header('Content-Type: application/json');

echo json_encode(fnTraceabilityTable($result));

function fnTraceabilityTable($result)
{
    $error = $result->Error;
    $msg = $result->Msg;
   
    
    if (isset($result->TraceabilityReportData) && !empty($result->TraceabilityReportData)) {
        $resData = end($result->TraceabilityReportData);
        
    } else {
        $resData = null;
    }
    if (!isset($error) || empty($error)) {
        $List = $result->TraceabilityReportData;
        $htmlResponse = '
        <div class="card">
            <div class="card-body">
                <table id="tblDataTable" class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th>ID</th>
                        <th>Farmer Name</th>
                        <th>Farmer Address</th>
                        <th>Traceability</th>
                        <th>Tonnage</th>
                        <th>Start Date</th>
                        <th>Expiry Date</th>
                        
                    </tr>
                </thead>
                    <tbody>';
        $i = 1;
        foreach ($List as $row) {
            $htmlResponse .= '
                <tr>
                    <td id="'.$row->TraceID .'">'. $i .'</td>
                    <td id="'.$row->TraceID .'">'.$row->FarmerName.'</td>
                    <td id="'.$row->TraceID .'"> '.$row->AddressContinued .'</td>
                    <td id="p_number'. $row->TraceID .'">'.$row->Tracebility .'</td>
                    <td id="tonnage'. $row->TraceID .'">' . $row->Tonnage .'</td>
                    <td id="startdate'. $row->TraceID .'">'.$row->StartDate .'</td>
                    <td id="expirydate'.$row->TraceID .'">'.$row->ExpiryDate.'</td>
                
                    </td>
                </tr>';
            $i++;
        }
        $htmlResponse .= '
                    </tbody>
                </table>
                
            </div>
        </div>';
    }


    $response = array('htmlRes' => $htmlResponse, 'TraceabilityReportData' => $resData, "Error" => $error,"Msg" => $msg );
    return $response;
}