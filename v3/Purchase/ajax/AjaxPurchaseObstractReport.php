<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_RM_PURCHASE_OBSTRACT_REPORT;

if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
    $Plant = trim($_GET["Plant"]);
    $Center = trim($_GET["Center"]);
    $StartDate = trim($_GET["StartDate"]);
    $EndDate = trim($_GET["EndDate"]);
    $border = "border='1'";
} else {
    $Plant = trim($_POST["Plant"]);
    $Center = trim($_POST["Center"]);
    $StartDate = trim($_POST["StartDate"]);
    $EndDate = trim($_POST["EndDate"]);
    $border = "";
}



$bodyContent = array(
    'Plant' => $Plant,   
    'CenterID' => $Center,
    'StartDate' => $StartDate,
    'EndDate' => $EndDate
);

$resList = CallAPI($api, "GET", "", $bodyContent);

$error = $resList->Error;
$List = $resList->Data->AbstractReportData;
$Totals = $resList->Data->Totals;



 if (!isset($error) || empty($error)) { 
    $content = '
                                            
        <table id="tblDataTable" ' . $border . 'class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th style="min-width: 50px;">S.No</th>
                    <th style="min-width: 100px;">Name Of The Center</th>
                    <th style="min-width: 110px;">HONV Quantity</th>
                    <th style="min-width: 130px;">HONV Value</th>
                    <th style="min-width: 100px;">HONBT Quantity</th>
                    <th style="min-width: 120px;">HONBT Value</th>
                    <th style="min-width: 100px;">Total Quantity</th>
                    <th style="min-width: 100px;">Total Value</th>
                    	  
                </tr>
            </thead>
            <tbody>';

                

                foreach ($List as $item) { 
                    $content .= '<tr>
                            <td>'. $item->SNO .'</td>	
                            <td>'. $item->CenterName .'</td>	
                            <td>'. $item->HONVMQTY .'</td>	
                            <td>'. $item->HONVMValue .'</td>
                            <td>'. $item->HONBTQTY .'</td>	
                            <td>'. $item->HONBTValue .'</td>	
                            <td>'. $item->TotalQty .'</td>
                            <td>'. $item->TotalValue .'</td>			
                        </tr> ';
                 } 
                 $content .= '</tbody>
            <tfoot>
                <tr>
                    <td align="center">Totals</td>
                    <td align="center"></td>
                    <td align="center">'. $Totals->TotalHonVmQty .'</td>
                    <td align="center">'. $Totals->TotalHonVmValue .'</td>
                    <td align="center">'. $Totals->TotalHonBTQty .'</td>
                    <td align="center">'. $Totals->TotalHonBTValue .'</td>
                    <td align="center">'. $Totals->TotalQuantity .'</td>
                    <td align="center">'. $Totals->TotalAmount .'</td>
                </tr>';
                $content .= '
            </tfoot>
        </table>
        ';

} 
else{
    echo "Note :". $error;
}
if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {

    $filename = 'Purchase_Obstract_Report.xls';
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment; filename=" . $filename . "");
    }
    echo $content;
    
