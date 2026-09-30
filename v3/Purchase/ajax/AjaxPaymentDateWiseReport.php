<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_RM_PAYMENT_DATE_WISE_REPORT;

if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
    $PaymentMode = trim($_GET["PaymentMode"]);
    $Center = trim($_GET["Center"]);
    $StartDate = trim($_GET["StartDate"]);
    $EndDate = trim($_GET["EndDate"]);
    $border = "border='1'";
} else {
    $Center = trim($_POST["Center"]);
    $Supplier = trim($_POST["Supplier"]);
    $StartDate = trim($_POST["StartDate"]);
    $EndDate = trim($_POST["EndDate"]);
    $PaymentMode = trim($_POST["PaymentMode"]);
    $border = "";
}



$bodyContent = array(
    'CenterID' => $Center,
    'Supplier' => $Supplier,
    'StartDate' => $StartDate,
    'EndDate' => $EndDate,
    'PaymentMode' => $PaymentMode

);

$resList = CallAPI($api, "GET", "", $bodyContent);
$Totals = $resList->Data->Totals;
// var_dump($Totals);
// exit;

$error = $resList->Error;
$List = $resList->Data->PaymentDateWiseReportData;

// echo json_encode($List, JSON_PRETTY_PRINT);



if (!isset($error) || empty($error)) {
    $content = '                                         
        <table id="tblDataTable" ' . $border . 'class="table table-hover table-bordered">
            <thead class="thead-light">
                <tr>
                    <th style="min-width: 50px;">Sl.No</th>
                    <th style="min-width: 110px;">Bill Date</th>
                    <th style="min-width: 100px;">Pay Date</th>
                    <th style="min-width: 130px;">Bill Number</th>
                    <th style="min-width: 130px;">Supplier</th>
                    <th style="min-width: 110px;">Voucher </th>
                    <th style="min-width: 110px;">Cheque No </th>
                    <th style="min-width: 130px;">Paid Amount</th>
                    <th style="min-width: 130px;">Sub Total</th>
                </tr>
            </thead>
            <tbody>';


    foreach ($List as $item) {
        if ($item->SNO != "") {
            $content .= ' <tr>
                            <td>' . $item->SNO . '</td>	
                            <td>' . $item->BillDate . '</td>	
                            <td>' . $item->PayDate . '</td>
                            <td>' . $item->BillNumber . '</td>	
                            <td>' . $item->Supplier . '</td>
                            <td>' . $item->Voucher . '</td>	
                            <td>' . $item->ChequeNo . '</td>	
                            <td>' . $item->Amount . '</td>                            
                            <td></td>    
                        </tr>';
        } else {
            $content .= '  <tr>
                            <td> </td>	
                            <td> </td>	
                            <td> </td>
                            <td> </td>	
                            <td><b style="color: #6eaeff">Sub Total<b></td>
                            <td><b style="color: #6eaeff">' .  $item->Voucher . '</b></td>
                            <td></td>
                            <td><b style="color: #6eaeff">  </b></td>
                            <td><b style="color: #6eaeff">' .  $item->SubTotal . '</b></td>
                        </tr>';
        }
    }
    $content .= ' </tbody>
            <tfoot>
                <tr>
                    <td>Total</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td>' . $Totals->TotalAmount . '</td>
                </tr>';
    $content .= ' 
            </tfoot>
        </table>
        ';
} else {
    echo "Note : " . $error;
}
if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {

    $filename = 'Payment_Date_Wise_Report.xls';
    header('Content-Type: application/vnd.ms-excel');
    header("Content-Disposition: attachment; filename=" . $filename . "");
}
echo $content;
