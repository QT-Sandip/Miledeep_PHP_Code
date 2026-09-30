<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_RM_COUNT_WISE_PURCHASE_REPORT;

if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
    $plant = trim($_GET["Plant"]);
    $center = trim($_GET["Center"]);
    $supplierId = trim($_GET["Supplier"]);
    $harestCommission = trim($_GET["HarestCommission"]);
    $startDate = trim($_GET["StartDate"]);
    $endDate = trim($_GET["EndDate"]);

    $border = "border='1'";
} else {
    $plant = trim($_POST["Plant"]);
    $center = trim($_POST["Center"]);
    $supplierId = trim($_POST["SupplierID"]);
    $harestCommission = trim($_POST["HarestCommission"]);
    // $Count = trim($_POST["Count"]);
    $startDate = trim($_POST["StartDate"]);
    $endDate = trim($_POST["EndDate"]);

    $border = "";
}



$bodyContent = array(
    'Plant' => $plant,
    'CenterID' => $center,
    'SupplierID' => $supplierId,
    'HarestCommission' => $harestCommission,
    // 'Count' => $Count,
    'StartDate' => $startDate,
    'EndDate' => $endDate
);

// echo json_encode($bodyContent, JSON_PRETTY_PRINT);
// exit;

$resList = CallAPI($api, "GET", "", $bodyContent);

// var_dump($resList);
$Totals = $resList->Data->Totals;


if (isset($resList) && !empty($resList)) {
    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $List = $resList->Data->CountwiseReportData;

        $content = '
<div class="card">
    <div class="card-body">                          
        <table id="tblDataTable"' . $border . ' class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th style="min-width: 50px;">S.No</th>
                    <th style="min-width: 160px;">Variety</th>
                    <th style="min-width: 130px;">Count	</th>
                    <th style="min-width: 120px;">Quantity</th>';
        if ($harestCommission == "T") {
            $content .= '<th style="min-width: 100px;">  Inc Harvest</th>';
        }

        $content .= '<th style="min-width: 160px;"> ';
        if ($harestCommission == "T") {
            $content .= ' Inc ';
        } else {
            $content .= ' Exc';
        }
        $content .= 'Amount</th>';
        $content .= '<th style="min-width: 160px;">Ave.Rate</th>
                </tr>
            </thead>
            <tbody>';
        foreach ($List as $item) {
            $content .= '<tr>
                        <td>' . $item->SNO . '</td>	
                        <td>' . $item->Species . '</td>
                        <td>' . $item->Count . '</td>	
                        <td>' . $item->TotalWeight . '</td>';
            if ($harestCommission == "T") {
                $content .= ' <td>' . $item->HarvestExpanses . '</td>';
            }
            $content .= '<td>' . $item->Amount . '</td>	
                        <td>' . $item->AvgRate . '</td>
                    </tr>';
        }

        $content .= ' </tbody>
        
 <tfoot>
            <tr>
                <th align="center">Total</th>
                <th align="center"></th>
                <th align="center"></th>
                <th align="center"> ' . $Totals->TotalQuantity . '</th>';
        if ($harestCommission == "T") {
            $content .= '<th align="center"></th>';
        }
        $content .= '<th align="center"> ' . $Totals->TotalAmount . ' </th>
                <th align="center"></th>
            </tr>';
    }
    $content .= '  
            </tfoot>
        </table> 

        ';
} else {
    echo "Note: " . $error;
}
if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {

    $filename = 'Purchase_Count_Wise_Report.xls';
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment; filename=" . $filename . "");
}

echo $content;
