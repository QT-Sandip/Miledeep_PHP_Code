<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

 $api = API_RM_PURCHASE_DATE_WISE_REPORT;

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
 $Totals = $resList->Data->Totals;

 $error = $resList->Error;
 $List = $resList->Data->DateWiseReportData;



 if (!isset($error) || empty($error)) { 
    $content = '                                     
        <table id="tblDataTable"' . $border . ' class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th style="min-width: 60px;">S.No</th>
                    <th style="min-width: 80px;">Date</th>
                    <th style="min-width: 100px;">HONV Quantity</th>
                    <th style="min-width: 110px;">HONV Value</th>
                    <th style="min-width: 110px;">HONBT Quantity</th>
                    <th style="min-width: 110px;">HONBT Value</th>
                    <th style="min-width: 110px;">Total Quantity</th>
                    <th style="min-width: 120px;">Total Value</th>		       
                </tr>
            </thead>
            <tbody>';

                

                  foreach ($List as $item) { 
                    $content .= ' <tr>
                            <td>'. $item->SNO .'</td>	
                            <td>'. $item->Date .'</td>	
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
                    <td align="center">Total</td>
                    <td align="center"></td>
                    <td align="center">'.$Totals->TotalHonVmQty .'</td>
                    <td align="center">'.$Totals->TotalHonVmValue .'</td>
                    <td align="center">'.$Totals->TotalHonBTQty .'</td>
                    <td align="center">'.$Totals-> TotalHonBTValue.'</td>
                    <td align="center">'.$Totals->TotalQuantity .'</td>
                    <td align="center">'.$Totals->TotalAmount .'</td>
                </tr>';
                $content .= '
            </tfoot>
        </table>
        ';

} 
else{
    echo "Note:". $error;
}
if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {

    $filename = 'Purchase_Date_Wise_Report.xls';
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment; filename=" . $filename . "");
    }
    echo $content;
    
