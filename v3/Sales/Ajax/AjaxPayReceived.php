<?php
//  ini_set('display_errors', 1);
//  ini_set('display_startup_errors', 1);
//  error_reporting(E_ALL); 
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_REALIZATION_DONE;

$Type = trim($_POST["Type"]);

// if(isset($Type) && !empty($Type)) {
if ($Type == 'Dashboard') {

    // echo $Type = trim($_POST["Type"]); exit;
    $FromDate = trim($_POST["FromDate"]); 
    $Month = trim($_POST["Month"]);
    $Exporter = trim($_POST["Exporter"]);
    $Buyer = trim($_POST["Buyer"]);
    $Brand = trim($_POST["Brand"]);
    $Country = trim($_POST["Country"]);
    $Processor = trim($_POST["Processor"]);

    $bodyContent = array(
        //'Year' => $FromDate,
        'Year' => trim($_POST["Year"]),
        'ExporterID' => $Exporter,
        'Type' => "",
        'Month' => $Month,
        'Buyer' => $Buyer,
        'Brand' => $Brand,
        'Processor' => $Processor,
        'Country' => $Country,
        'FromDate' => trim($_POST["FromDate"]),
	    'ToDate' => trim($_POST["ToDate"]),
        'DashBoard' => trim($_POST["Dashboard"]),
    );


    if ($Exporter != "") {
        $Query .= 'and POD.ExporterID in (' . $Exporter . ')';
    }
    if ($Processor != "") {
        $Query .= 'and POD.ProcessorID=' . $Processor;
    }
    if ($Country != "") {
        $Query .= 'and POD.DestinationCountry=' . $Country;
    }

    if ($Buyer != "") {
        $Query .= 'and POD.ConsigneeID=' . $Buyer;
    }

    if ($Brand != '') {
        $Query .= " and POD.PoID  in (SELECT DISTINCT(InvId) FROM `ScrmPoGradeData` WHERE Brand='$Brand')";
    }
} else {

    $Year = trim($_POST['Year']);
    $ExporterID = trim($_POST["ExporterID"]);
    $Type = trim($_POST["Type"]);
    $Country = trim($_POST["Country"]);

    $bodyContent = array(
        'Year' => $Year,
        'ExporterID' => $ExporterID,
        'Type' => $Type,
        'Country' => $Country,
    );
}
// var_dump($bodyContent);

$resList = CallAPI($api, "POST", "", $bodyContent);

if (isset($resList) && !empty($resList)) {


    //var_dump($resList);
    $error = $resList->Error;
    // $List = $resList->Data->QCertificateReportData;
    // $ListTotal = $resList->Data->Totals;

?>

    <?php if (!isset($error) || empty($error)) {

        $List = $resList->Data;

        // if (isset($Type) && !empty($Type)) {
            if ($Type == 'Dashboard') {
            echo '<table  id="key-datatableFullscreen" data-paging="false" class="table  wrap w-150 table-bordered" >';
        } else {
            echo '<table id="tblDataTable" class="table table-hover table-bordered">';
        }

    ?>
        <thead class="thead-light">
            <tr>
                <th style="min-width:45px" class="sticky etdEtaReportCol1">SI.No.</th>
                <th style="min-width:110px" class="sticky etdEtaReportCol2">Invoice NO </th>
                <th style="min-width:120px">Consignee </th>
                <th style="min-width:120px">Sales Invoice Date </th>
                <th style="min-width:150px">Transaction Type </th>
                <th style="min-width:150px">Total Invoice Value$</th>
                <th style="min-width:130px">Realised Value$</th>
                <th style="min-width:160px">Date Of Discounting With Bank</th>
                <th style="min-width:100px">Bank Ref No </th>
                <th style="min-width:160px">Date Of Receipt Of Payment-1 </th>
                <th style="min-width:160px">Amount Of Receipt Of Payment-1 </th>
                <th style="min-width:100px">Bank Ref No </th>
                <th style="min-width:160px">Date Of Receipt Of Payment-2 </th>
                <th style="min-width:160px">Amount Of Receipt Of Payment-2 </th>
                <th style="min-width:100px">Bank Ref No </th>
                <th style="min-width:160px">Date Of Receipt Of Payment-3 </th>
                <th style="min-width:160px">Amount Of Receipt Of Payment-3 </th>
                <th style="min-width:160px">Total Utilized Value$ </th>
                <th style="min-width:160px">Total Utilized Value INR </th>
                <th style="min-width:100px">Short/ Excess </th>
                <th style="min-width:90px">Bank charges </th>
                <th style="min-width:90px">FBK Charges </th>
                <th style="min-width:90px">Other Charges </th>
                <th style="min-width:90px">Remarks </th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($List as $item) { ?>
                <tr>
                    <td class="sticky etdEtaReportCol1"><?php echo $item->SNO ?></td>
                    <td class="sticky etdEtaReportCol2"><?php echo $item->InvoiceNo ?></td>
                     <td><?php echo $item->Buyer ?></td>
                    <td><?php echo $item->SaleInvoiceDate ?></td>
                    <td><?php echo $item->TransactionType ?></td>
                    <td><?php echo $item->TotalInvoiceValueUSD ?></td>
                    <td><?php echo $item->RealisedValueUSD ?></td>
                    <td><?php echo $item->DateofDiscountingwithBank ?></td>
                    <td><?php echo $item->BankRefNo1 ?> </td>
                    <td><?php echo $item->UtilizedDate1 ?></td>
                    <td><?php echo $item->UtilizedValue1 ?></td>
                    <td><?php echo $item->BankRefNo2 ?></td>
                    <td><?php echo $item->UtilizedDate2 ?></td>
                    <td><?php echo $item->UtilizedValue2 ?> </td>
                    <td><?php echo $item->BankRefNo3 ?></td>
                    <td><?php echo $item->UtilizedDate3 ?></td>
                    <td><?php echo $item->UtilizedValue3  ?></td>
                    <td><?php echo $item->TotalUtlizedValue ?></td>
                    <td><?php echo $item->TotalUtlizedValueINR ?></td>
                    <td><?php echo $item->EtraAmount ?></td>
                    <td><?php echo $item->BnkChrges ?></td>
                    <td><?php echo $item->FbkChrges ?></td>
                    <td><?php echo $item->OtherAmount ?></td>
                    <td>-</td>
                </tr>
            <?php  } ?>
        </tbody>
        </table>


<?php } else {
        echo "<div class='text-center mt-3 mb-3 text-bold'>NOTE : " . $error . "</div>";
    }
} ?>