<?php
include("../RestAPI.php");

if (isset($_POST["methodType"]) && !empty($_POST["methodType"])) {
    $methodType = trim($_POST["methodType"]);
} else {
    $methodType = "";
}

// echo print_r($_SESSION);exit;
$ClientId = $_SESSION['ClientID'];
$AccessType = $_SESSION['AccessType'];
$DeptID = $_SESSION['DeptID'];
$IsAccess = 0;
if ($ClientId == 'SMEV3' && $AccessType == 2 && ($DeptID == 8 || $DeptID == 4)) {
    $IsAccess = 1;
} else {
    $IsAccess = 0;
}

$resGetClearingAgentReportDataAPI = '';
$resApprovalData = '';
if ($methodType == 'Approve') {

    $PoId = trim($_POST['PoId']);
    $api = ApproveClearingAgentReportDataAPI;
    $bodyContent = array(
        "PoID" => $PoId,
    );
    $resApprovalData = CallAPI($api, "POST", "", $bodyContent);

    if ($resApprovalData->Error == '') {
        $api = GetClearingAgentReportDataAPI;
        $Exporter = trim($_POST['Exporter']);
        $Consignee = trim($_POST['Consignee']);
        $ClearingAgent = trim($_POST['ClearingAgent']);
        $DateSelection = trim($_POST['DateSelection']);
        $FromDate = trim($_POST['FromDate']);
        $ToDate = trim($_POST['ToDate']);
        $Approval = trim($_POST['Approval']);
        $border = "";
        $bodyContent = array(
            "Exporter" => $Exporter,
            // "Country" => $Country,
            "Consignee" => $Consignee,
            "ClearingAgent" => $ClearingAgent,
            "DateSelection" => $DateSelection,
            "FromDate" => $FromDate,
            "ToDate" => $ToDate,
            "ApprovalStatus" => $Approval,
            "Approval" => 'YES'
        );
        $resGetClearingAgentReportDataAPI = CallAPI($api, "POST", "", $bodyContent);
    } else {
        $resGetClearingAgentReportDataAPI = '';
    }
} else {

    if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {

        $Exporter = trim($_GET['Exporter']);
        // $Country = trim($_GET['Country']);
        $Consignee = trim($_GET['Consignee']);
        $ClearingAgent = trim($_GET['ClearingAgent']);
        $DateSelection = trim($_GET['DateSelection']);
        $FromDate = trim($_GET['FromDate']);
        $ToDate = trim($_GET['ToDate']);
        $Approval = trim($_GET['Approval']);
        $border = "border='1'";
    } else {
        $Exporter = trim($_POST['Exporter']);
        // $Country = trim($_POST['Country']);
        $Consignee = trim($_POST['Consignee']);
        $ClearingAgent = trim($_POST['ClearingAgent']);
        $DateSelection = trim($_POST['DateSelection']);
        $FromDate = trim($_POST['FromDate']);
        $ToDate = trim($_POST['ToDate']);
        $Approval = trim($_POST['Approval']);
        $border = "";
    }
    $api = GetClearingAgentReportDataAPI;

    $bodyContent = array(
        "Exporter" => $Exporter,
        // "Country" => $Country,
        "Consignee" => $Consignee,
        "ClearingAgent" => $ClearingAgent,
        "DateSelection" => $DateSelection,
        "FromDate" => $FromDate,
        "ToDate" => $ToDate,
        "ApprovalStatus" => $Approval,
        "Approval" => 'YES'
    );
    $resGetClearingAgentReportDataAPI = CallAPI($api, "POST", "", $bodyContent);
}

if ($resGetClearingAgentReportDataAPI != '') {
    //echo json_encode($resGetClearingAgentReportDataAPI, JSON_PRETTY_PRINT);
//exit;
    if ($_SERVER['REMOTE_ADDR'] == "49.204.237.3") {
        //echo "<pre>"; print_r($resGetClearingAgentReportDataAPI); exit;
    }
    $resTotals = $resGetClearingAgentReportDataAPI->Data->Totals;
    $resList = $resGetClearingAgentReportDataAPI->Data->getClearingAgentReportData;
    $ArrayClearingAgentTransactionsData = $resGetClearingAgentReportDataAPI->Data->ArrayClearingAgentTransactionsData;
    $ArrayDetailBills = $resGetClearingAgentReportDataAPI->Data->getClearingAgentReportData;
    //echo "<pre>aaa"; print_r($ArrayDetailBills); exit;

    $rowspan = ''; // No rowspan needed

    $content = '<table border="1" id="key-datatable" data-paging="false" class="table" style="border-collapse: collapse;">
<thead class="thead-light">
<tr style="height: 20px;">
    <th style="min-width: 40px;" class="sticky etaStatusCol1" ' . $rowspan . '>S.No</th>
    <th style="min-width: 140px;" class="sticky etaStatusCol1" ' . $rowspan . '>INV NO</th>
    <th style="min-width: 150px;" class="sticky etaStatusCol1" ' . $rowspan . '>INVOICE DATE</th>
    <th style="min-width: 200px;" ' . $rowspan . '>Invoice Value $</th>
    <th style="min-width: 200px;" ' . $rowspan . '>Entered FOB VALUE</th>
    <th style="min-width: 200px;" ' . $rowspan . '>Quantity (in LBS)</th>
    <th style="min-width: 150px;" ' . $rowspan . '>Agent Name</th>
    <th style="min-width: 150px;" ' . $rowspan . '>U.S. ENTRY NO.</th>
    <th style="min-width: 180px;" ' . $rowspan . '>Customs entry date</th>
    <th style="min-width: 220px;" ' . $rowspan . '>U.S Anti Dumping duty ($)</th>
    <th style="min-width: 220px;" ' . $rowspan . '>CVD Duty</th>
    <th style="min-width: 180px;" ' . $rowspan . '>Reciprocal Tariffs Fee</th>
    <th style="min-width: 100px;" ' . $rowspan . '>HMF</th>
    <th style="min-width: 100px;" ' . $rowspan . '>MPF</th>';

    foreach ($ArrayClearingAgentTransactionsData as $agent) {
        $content .= '<th style="min-width: 350px; text-align:center">
        <table border="1" style="width:100%; border-collapse: collapse; font-size:12px;">
            <tr>
                <th style="min-width: 350px; text-align:center" colspan="3">' . htmlspecialchars($agent) . '</th>
            </tr>
            <tr>
                <th style="width:100px;">Bill Number</th>
                <th style="width:100px;">Bill Date</th>
                <th style="width:100px;">Bill Amount</th>
            </tr>
        </table>
    </th>';
    }

    $content .= '<th style="min-width: 100px;" ' . $rowspan . '>Total Bill Amount</th>';
    if ($IsAccess == 1) {
        $content .= '
<th style="min-width: 100px;" ' . $rowspan . '>Actions</th>';
    }
    $content .= '
</tr>
</thead>
<tbody>';


    $sno = 1;
    foreach ($resList as $item) {
        $content .= '<tr style="height: 23px;">
        <td class="sticky etaStatusCol1">' . $sno++ . '</td>
        <td class="sticky etaStatusCol1 bodyTotInv">' . htmlspecialchars($item->InvoiceNo) . '</td>
        <td class="sticky etaStatusCol1">' . htmlspecialchars($item->CInvDate) . '</td>
        <td class="bodyInvoiceValue">' . $item->InvoiceValue . '</td>
        <td class="bodyEnteredvalueFobValue">' . $item->EnteredvalueFobValue . '</td>
        <td>' . $item->QuantityinLbs . '</td>
        <td>' . htmlspecialchars($item->AgentName) . '</td>
        <td>' . htmlspecialchars(trim($item->USEntryNumber)) . '</td>
        <td>' . htmlspecialchars($item->CustomsEntryDate) . '</td>
        <td>' . $item->USAntiDumpingDuty . '</td>
        <td>' . $item->CountervailingDuty . '</td>
        <td>' . $item->ReciprocalTariffsFee . '</td>
        <td>' . $item->HMF . '</td>
        <td>' . $item->MPF . '</td>';

        foreach ($ArrayClearingAgentTransactionsData as $agentKey) {
            $agentKeyClean = str_replace(" ", "", $agentKey);
            $content .= '<td style="padding: 0px !important;margin: 0px !important;">';
            $content .= '<table border="1" style="width:100%; border-collapse: collapse;  font-size:12px;min-height:50px;padding: 0px !important;margin: 0px !important;">';
            if (!empty($item->MbillData->$agentKeyClean)) {

                foreach ($item->MbillData->$agentKeyClean as $bill) {
                    $content .= '<tr>
                    <td style="text-align: left;width:100px;">' . htmlspecialchars($bill->BillNumber) . '</td>
                    <td style="mso-number-format:\@;text-align: right;width:100px;">' . htmlspecialchars($bill->BillDate) . '</td>
                    <td style="mso-number-format:\#\,\#\#0\.00;text-align: right;width:100px;">' . $bill->BillAmount . '</td>
                </tr>';
                }
            } else {
                $content .= '<tr>
                <td style="mso-number-format:\@;text-align: right;width:100px;"></td>
                <td style="mso-number-format:\@;text-align: right;width:100px;"></td>
                <td style="mso-number-format:\@;text-align: right;width:100px;"></td>
                </tr>';
            }
            $content .= '</table>';
            $content .= '</td>';
        }

        $content .= '<td>' . $item->TotalClearingAgentTransactions . '</td>';
        if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
            if ($IsAccess == 1 && $item->AddApproval == 1 && $item->ApprovalStatus != 1) {
                $content .= '<td style="color: #f09307;">
           Approval Pending
        </td>';
            } else if ($IsAccess == 1 && $item->AddApproval == 1 && $item->ApprovalStatus == 1) {
                $content .= '<td style="color: green;">
            Approved
        </td>';
            } else if ($IsAccess == 1) {
                $content .= '<td style="color: red;">
            Pending
        </td>';
            }
        } else {
            if ($IsAccess == 1 && $item->AddApproval == 1 && $item->ApprovalStatus != 1) {
                $content .= '<td style="color: #f09307; cursor: pointer;font-weight: bolder;" onclick="approveRecord(' . $item->PoID . ')">
           Approve <i class="fa fa-check" style="margin-left: 1px;"></i>
        </td>';
            } else if ($IsAccess == 1 && $item->AddApproval == 1 && $item->ApprovalStatus == 1) {
                $content .= '<td style="color: green;">
            Approved
        </td>';
            } else if ($IsAccess == 1) {
                $content .= '<td style="color: red;">
            Pending
        </td>';
            }
        }

        $content .= '</tr>';
    }

    $content .= '</tbody>
<tfoot>
<tr style="height: 23px;">
    <th class="sticky etaStatusCol1"></th>
    <th class="sticky etaStatusCol1"></th>
    <th class="sticky etaStatusCol1"></th>
    <th class="footTotalInvoiceValueUSD">' . $resTotals->TotalInvoiceValueUSD . '</th>
    <th class="footTotalEnteredvalueFobValue">' . $resTotals->TotalEnteredvalueFobValue . '</th>
    <th class="footTotalNetWgtLbs">' . $resTotals->TotalNetWgtLbs . '</th>
    <th></th><th></th><th></th>
    <th></th><th></th><th></th><th></th><th></th>';

    foreach ($ArrayClearingAgentTransactionsData as $agent) {
        $content .= '<th></th>';
    }

    $content .= '<th></th>';
    if ($IsAccess == 1) {
        $content .= '<th></th>';
    }
    $content .= '
</tr>
</tfoot>
</table>';




    if (isset($_GET["excelDownload"]) && $_GET["excelDownload"] == "T") {
        $filename = 'Clearing_Agent_Report.xls';
        header('Content-Type: application/vnd.ms-excel');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        echo $content; // send only the HTML table
        exit;
    } else {
        $response = [
            'content' => $content,
            'Data' => $resApprovalData
        ];
        echo json_encode($response);
    }

    //    echo $content;
}
