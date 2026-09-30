<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$methodType = trim($_POST["methodType"]);
$api = API_QUANTITY_CHECK_VALUE;
$border = "";

if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
    $bodyContent = array(
        "ArrivalDate" => trim($_GET['ArrivalDate']),
        "MethodType" => '',
    );
    $result = CallAPI($api, "GET", "", $bodyContent);
    $border = "border='1'";
} else if ($methodType == 'GET') {
    $bodyContent = array(
        "ArrivalDate" => trim($_POST['arrivalDate']),
        "MethodType" => '',
    );
    $result = CallAPI($api, "GET", "", $bodyContent);
} else if ($methodType == 'QualityCheck') {
    $bodyContent = array(
        "ArrivalDate" => trim($_POST['arrivalDate']),
        "MethodType" => 'BATCH_NUMBERS',
    );
    $result = CallAPI($api, "GET", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
} else if ($methodType == 'deleteRow') {
    $bodyContent = array(
        "QuantityCheckid" => trim($_POST["QuantityCheckid"]),
    );
    $result = CallAPI($api, "DELETE", "", $bodyContent);

    header('Content-Type: application/json');
    echo json_encode($result);
}

if (isset($result) && !empty($result)) {
    $error = $result->Error;
    $msg = $result->Msg;

    if (!isset($error) || empty($error)) {
        $List = $result->QualityData;

        $content = '<div class="card"><div class="card-body">';
        $content .= '<table id="tblDataTable" ' . $border . ' class="table table-hover table-bordered" style="text-align:center; border-collapse: collapse; width: 100%;">';

        if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
            $content .= '
                <tr><td colspan="25" align="center"><b>COASTAL AQUA PRIVATE LIMITED</b></td></tr>
                <tr><td colspan="25" align="center"><b>PATHARLAGADDA, KAKINADA<b></td></tr>
                <tr><td colspan="25" align="center"><b>QUALITY CHECK FOR VALUE ADDED SECTION</b></td></tr>
                <tr><td colspan="12">Head Less Quantity: ' . $_GET["Head Less Quantity"] . '</td>
                    <td colspan="13" style="text-align:right"><b>REVISION NO:</b> 001</td></tr>
                <tr><td colspan="12">Quantity: ' . $_GET["Quantity"] . '</td>
                    <td colspan="13" style="text-align:right"><b>APPROVAL NO:</b> 8816/FMT/SOP/0956</td></tr>
                <tr><td colspan="12">Yield: ' . $_GET["Yield"] . '</td>
                    <td colspan="13" style="text-align:right">Date: ' . date("d-m-Y", strtotime($_GET["ArrivalDate"])) . '</td></tr>';
        }

        $content .= '
            <thead class="thead-light">
                <tr>
                  

                    <th style="text-align:center;" rowspan="2">S.No</th>
                    <th style="text-align:center;" rowspan="2">Value Addition Date</th>
                     <th style="text-align:center;" rowspan="2">VAStation</th>
                    <th style="text-align:center;" rowspan="2">Batch Number</th>
                    <th style="text-align:center;" rowspan="2">Variety</th>
                    <th style="text-align:center;" rowspan="2">Room Temperature</th>
                    <th style="text-align:center;" colspan="2">Temperature</th>
                    <th style="text-align:center;" rowspan="2">Number Samples Checked</th>
                    <th style="text-align:center;" rowspan="2">Table No</th>
                    <th style="text-align:center;" rowspan="2">Grade</th>
                    <th style="text-align:center;" rowspan="2">Type Of Cut</th>
                    <th style="text-align:center;" rowspan="2">Improper Peeling</th>
                    <th style="text-align:center;" rowspan="2">Improper Cutting</th>
                    <th style="text-align:center;" rowspan="2">Vein</th>
                    <th style="text-align:center;" rowspan="2">Broken</th>
                    <th style="text-align:center;" rowspan="2">Other Objection Defects</th>
                    <th style="text-align:center;" rowspan="2">Total Peeling Girls</th>
                    <th style="text-align:center;" rowspan="2">Head Less Quantity</th>
                    <th style="text-align:center;" rowspan="2">Quantity</th>
                    <th style="text-align:center;" rowspan="2">Yield (%)</th>
                    <th style="text-align:center;" rowspan="2">Checking Girls</th>
                    <th style="text-align:center;" rowspan="2">Supervisor</th>
                     <th style="text-align:center;" rowspan="2">CorrectiveAction</th>
                     <th style="text-align:center;" rowspan="2">Remarks</th>
                  

                </tr>
 <tr>
        <th style="text-align:center;">Table Temperature</th>
        <th style="text-align:center;">Crate Temperature</th>
    </tr>
                
            </thead>
            <tbody>';

        $i = 1;
        foreach ($List as $item) {
            $content .= '
            <tr>
                <td style="text-align:center;">' . $i . '</td>
                <td class="tdArrivalDate">' . date("d-m-Y", strtotime($item->ArrivalDate)) . '</td>
                <td style="text-align:center;">' . $item->VAStation . '</td>
                <td style="text-align:center;">' . $item->ArrivalNumber . '</td>
                <td style="text-align:center;">' . $item->Variety . '</td>
                <td style="text-align:center;">' . $item->RoomTemperature . '</td>
                 <td style="text-align:center;">' . $item->TableTemperature . '</td>
                <td style="text-align:center;">' . $item->CrateTemperature . '</td>
                <td style="text-align:center;">' . $item->NumberSamplesChecked . '</td>
                <td style="text-align:center;">' . $item->TableNo . '</td>
                <td style="text-align:center;">' . $item->Grade . '</td>
                <td style="text-align:center;">' . $item->TypeOfCut . '</td>
                <td style="text-align:center;">' . $item->ImproperPeeling . '</td>
                <td style="text-align:center;">' . $item->ImproperCutting . '</td>
                <td style="text-align:center;">' . $item->Vein . '</td>
                <td style="text-align:center;">' . $item->Broken . '</td>
                <td style="text-align:center;">' . $item->OtherObjectionDefects . '</td>
                <td style="text-align:center;">' . $item->TotalPeelingGirls . '</td>
                <td style="text-align:center;">' . $item->HeadLessQuantity . '</td>
                <td style="text-align:center;">' . $item->Quantity . '</td>
                <td style="text-align:center;">' . $item->Yield . '</td>
                <td style="text-align:center;">' . $item->CheckingGirls . '</td>
                <td style="text-align:center;">' . $item->SuperVisor . '</td>
                 <td style="text-align:center;">' . $item->CorrectiveAction . '</td>
                 <td style="text-align:center;">' . $item->Remarks . '</td>
              
            </tr>';
            $i++;
        }

        $content .= '</tbody></table></div></div>';

        // if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
        //     $content .= '
        //         <br><br>
        //         <table style="width:100%; font-family:Arial, sans-serif; font-size:12px; border-collapse:collapse; text-align:center;">
        //             <tr>
        //                 <td colspan="3" style="border:none;"><b>Signature of the Q.C: ' . $List[0]->InspectedBy . '</b></td>
        //                 <td colspan="3" style="border:none;"><b>Verified By: ' . $List[0]->VerifiedBy . '</b></td>
        //             </tr>
        //         </table>';
        //     $filename = 'Quality_Check_Value_Report.xls';
        //     header("Content-Type: application/vnd.ms-excel; charset=utf-8");
        //     header("Content-Disposition: attachment; filename=" . $filename);
        // }


        if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
    // Collect all VerifiedBy & InspectedBy
    $verifiedByArr = [];
    $inspectedByArr = [];
    foreach ($List as $row) {
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

    // Join into strings with commas
    $verifiedByStr  = implode(", ", $verifiedByArr);
    $inspectedByStr = implode(", ", $inspectedByArr);

    // Footer table
    $content .= '
        <br><br>
        <table style="width:100%; font-family:Arial, sans-serif; font-size:12px; border-collapse:collapse; text-align:left;">
         <tr>
                <td colspan="8" style="border:none;"><b>Inspected By :</b> <b>' . $inspectedByStr . '</b></td>
            </tr> 
        
        <tr>
                <td colspan="8" style="border:none;"><b>Verified By :</b> <b>' . $verifiedByStr . '</b></td>
            </tr>
           
        </table>';
    
    $filename = 'Quality_Check_Value_Report.xls';
    header("Content-Type: application/vnd.ms-excel; charset=utf-8");
    header("Content-Disposition: attachment; filename=" . $filename);
}


        echo $content;
    }
}
