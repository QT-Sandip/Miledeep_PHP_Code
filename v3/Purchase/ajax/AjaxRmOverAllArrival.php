<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_RM_OVER_ALL_ARRIVAL;


$MethodType = trim($_POST["MethodType"]);

if (isset($_POST["Report"]) && !empty($_POST["Report"])) {
    $Report = trim($_POST["Report"]);
} else {
    $Report = "F";
}

if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {

    $ArrivalFromDate = trim($_GET["ArrivalFromDate"]);
    $ArrivalDate = trim($_GET["ArrivalDate"]);
    $Company = trim($_GET["Company"]);
    $Plant = trim($_GET["Plant"]);
    $Center = trim($_GET["Center"]);
    $Spieces = trim($_GET["Spieces"]);

    $bodyContent = array(
        "ArrivalFromDate" => $ArrivalFromDate,
        "ArrivalDate" => $ArrivalDate,
        "CompanyID" => $Company,
        "PlantID" => $Plant,
        "CenterID" => $Center,
        "Species" => $Spieces
    );

    // echo json_encode($bodyContent, JSON_PRETTY_PRINT);
    // exit;

    $resList = CallAPI($api, "GET", "", $bodyContent);
} else if ($MethodType == "Get") {

    $ArrivalFromDate = trim($_POST["ArrivalFromDate"]);
    $ArrivalDate = trim($_POST["ArrivalDate"]);
    $Company = trim($_POST["Company"]);
    $Plant = trim($_POST["Plant"]);
    $Center = trim($_POST["Center"]);
    $Spieces = trim($_POST["Spieces"]);

    $bodyContent = array(
        "ArrivalFromDate" => $ArrivalFromDate,
        "ArrivalDate" => $ArrivalDate,
        "CompanyID" => $Company,
        "PlantID" => $Plant,
        "CenterID" => $Center,
        "Species" => $Spieces
    );

    // echo json_encode($bodyContent, JSON_PRETTY_PRINT);
    // exit;

    $resList = CallAPI($api, "GET", "", $bodyContent);
    // echo json_encode($resList, JSON_PRETTY_PRINT);
    // exit;
} else if ($MethodType == "Delete") {

    $ArrivalID = trim($_POST["ArrivalID"]);
    $ArrivalDate = trim($_POST["ArrivalDate"]);

    $bodyContent = array(
        'ArrivalID' => $ArrivalID,
        'ArrivalDate' => $ArrivalDate
    );

    $resList = CallAPI($api, "DELETE", "", $bodyContent);

    header('Content-Type: application/json');

    $response = array("Error" => $resList->Error, "Msg" => $resList->Msg);
    echo json_encode($response);

    exit();
} else if ($MethodType == "Post") {

    $ArrivalID = trim($_POST["ArrivalID"]);
    $ArrivalDate = trim($_POST["ArrivalDate"]);
    $CompanyID = trim($_POST["CompanyID"]);
    $PlantID = trim($_POST["PlantID"]);
    $CenterID = trim($_POST["CenterID"]);
    $Species = trim($_POST["Species"]);
    $TotalWeight = trim($_POST["TotalWeight"]);
    $TotalAmount = trim($_POST["TotalAmount"]);
    $AvgRate = trim($_POST["AvgRate"]);

    $bodyContent = array(
        'ArrivalID' => $ArrivalID,
        "ArrivalDate" => $ArrivalDate, // $ArrivalDate
        "CompanyID" => $CompanyID,
        "PlantID" => $PlantID,
        "CenterID" => $CenterID,
        "Species" => $Species,
        "TotalWeight" => $TotalWeight,
        "TotalAmount" => $TotalAmount,
        "AvgRate" => $AvgRate
    );

    // var_dump($bodyContent);

    $resList = CallAPI($api, "POST", "", $bodyContent);

    // echo json_encode($resList, JSON_PRETTY_PRINT);
    // exit;

    if (isset($resList) && !empty($resList)) {
        if (!isset($resList->Error) || empty($resList->Error)) {
            if (!isset($error) || empty($error)) {
                echo '<input type="hidden" id="hdnMsg" value="' . $resList->Msg . '" />';
            }
        }
    }
} else {
    exit();
}

if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {

        $resArrivalData = $resList->ArrivalData;
        $resGrandTotal = $resList->GrandTotal;

        if (!isset($error) || empty($error)) {

            $content = '

            <table id="tblDataTable" class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th class="text-center" style="min-width: 50px;">SNO</th>
                        <th>Date</th>
                        <th>Plant</th>
                        <th>Center</th>
                        <th>Species</th>
                        <th>Weight</th>
                        <th>Balance Weight</th>
                        <th>Status</th>
                        <th>Average Rate</th>
                        <th>Amount</th>';
            if ($Report == "F") {
                $content .= '<th class="text-center" style="min-width: 80px;">Actions</th>';
            }
            $content .= '</tr>
                </thead>
                <tbody>';
            $i = 1;
            foreach ($resArrivalData as $item) {
                $content .= '<tr>
                            <td class="text-center slno" data-arrivalid="' . $item->ArrivalID . '">' . $i . '</td>
                            <td class="tdDate" data-date="' . $item->ArrivalDate . '">' . $item->ArrivalDate . '</td>
                            <td class="tdCenter">' . $item->Plant . '</td>
                <td class="tdCenter">' . $item->Center . '</td>
                <th class="tdSpieces">' . $item->Species . '</th>
                <th class="tdWeight" style="text-align:right;">' . sprintf('%0.2f', ($item->TotalWeight), 2) . '</th>
                <th class="tdBalanceWeight" style="text-align:right;">' . sprintf('%0.2f', ($item->BalanceWeight), 2) . '</th>
                <th>
                    <div class="bg-active color-palette text-center">
                        <button class="btn btn-link badge badge-warning font-size-10" style="width:70px;background: ' . $item->StatusColour . ';color:white" data-toggle="popover" data-trigger="focus">
                            ' . $item->Status . '
                        </button>
                    </div>
                </th>
                <td class="tdAverageRate" style="text-align:right;">' . sprintf('%0.2f', ($item->AvgRate), 2) . '</td>
                <td class="tdAmount " style="text-align:right;">' . sprintf('%0.2f', ($item->TotalAmount), 2) . '</td>';
                if ($Report == "F") {
                    $content .= '<td class="text-center">
                        <input type="hidden" class="hdnCompany" value="' . $item->CompanyID . '" />
                        <input type="hidden" class="hdnPlant" value="' . $item->PlantID . '" />
                        <input type="hidden" class="hdnCenter" value="' . $item->CenterID . '" />
                        <button class="mr-3 text-primary btnActions btnEdit" data-id="' . $item->ArrivalID . '">
                            <i class="mdi mdi-pencil font-size-16"></i>
                        </button>
                        <button class="mr-3 text-danger btnActions btnDelete" data-id="' . $item->ArrivalID . '">
                            <i class="mdi mdi-trash-can font-size-16"></i>
                        </button>';
                    if ($item->Status == "Pending") {
                        $content .= ' <a href="create-arrival.php?OverallArrivalID=' . $item->ArrivalID . '&ArrivalDate=' . $item->ArrivalDate . '" class="mr-3 text-success btnActions btnView" data-id="' . $item->ArrivalID . '">
                                <i class="dripicons-preview font-size-16"></i>
                            </a>';
                    }
                    $content .= '</td>';
                }
                $content .= '</tr>';
                $i++;
            }
            $content .= '</tbody>
            <tfoot>
                <tr>
                    <th>&nbsp;</th>
                    <th>&nbsp;</th>
                    <th>&nbsp;</th>
                    <th>&nbsp;</th>
                    <th>Total</th>
                    <th style="text-align:right;">' . sprintf('%0.2f', ($resGrandTotal->WeightTotal), 2) . '</th>
                    <th>&nbsp;</th>
                    <th>&nbsp;</th>
                    <th>&nbsp;</th>
                    <th style="text-align:right;">' . sprintf('%0.2f', ($resGrandTotal->ValueTotal), 2) . '</th>';
            if ($Report == "F") {
                $content .= '<th>&nbsp;</th>';
            }
            $content .= ' </tr>
            </tfoot>
            </table>';
        } else {
            echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : " . $error . "</div>";
        }
    }
}

if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {

    $filename = 'Rm_Overall_Arrival_Report.xls';
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment; filename=" . $filename . "");
}

echo $content;
