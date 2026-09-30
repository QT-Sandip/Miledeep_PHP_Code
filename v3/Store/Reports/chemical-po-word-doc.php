<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_CHEMICAL_PO_PRINT;

$ChemPoID = trim($_GET['ChemPoID']);
$bodyContent = array(
    'ChemPoID' => $ChemPoID
);
$result = CallAPI($api, "POST", "", $bodyContent);

// echo json_encode($result, JSON_PRETTY_PRINT);
// exit;
$List = $result->Data;
$resTotals = $result->Total;

$formattedDate = date('d-m-Y', strtotime($List[0]->ChemPoDate));
$ChemPoNo = $List[0]->ChemPoNo;
$SupplierName = $List[0]->SupplierName;
$SupplierAddress = $List[0]->SupplierAddress;
$CurrencyCode = (!empty($List[0]->CurrencyCode)) ? $List[0]->CurrencyCode : '';

$content = '';
$content .= '<html xmlns:w="urn:schemas-microsoft-com:office:word">
<head>
    <meta charset="UTF-8">
    <title>Chemical PO Print</title>
    <style>
        body { font-family: Arial, sans-serif; }
        .header { text-align: center; color: blue; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #000; padding: 8px; text-align: center; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
  
    <div><br></div>
    <div><br></div>
    <div><br></div>
    <div><br></div>
    <div>
        <p>PO NO : <b>' . $ChemPoNo . '</b><br>
        Date: ' . $formattedDate . '</p>
        <p>The Sales Manager,<br>
        ' . $SupplierName . '<br>
         ' . nl2br($SupplierAddress) . '</p>
        <p>Dear Sir,<br>
        Sub: Purchase Order - Reg.<br>
        Ref: Your E-mail quotation<br><br>
        With reference to above we are pleased to place an order for the following goods for our Factory:<br>
        </p>
    </div>
    <table>
        <thead>
            <tr>
                <th style="width: 35px;">SNo</th>
                <th>Particulars</th>
                <th>Quantity (Kgs)</th>
                <th>Price</th>
                  <th>Amount (' . htmlspecialchars($CurrencyCode) . ')</th>
            </tr>
        </thead>
        <tbody>';

foreach ($List as $item) {
    $content .= '<tr>
                    <td style="width: 35px;">' . $item->SNO . '</td>
                    <td>' . $item->ChemicalName . '</td>
                    <td>' . number_format($item->QuantityInKgs, 3) . '</td>
                    <td>' . number_format($item->RatePerKgInRupees, 2) . '</td>';
    // $formatter = new NumberFormatter("en_IN", NumberFormatter::CURRENCY);
    // // return $formatter->formatCurrency($amount,"INR");
    // if ($item->CurrenySymbol == "") {
    //     // $content .= '<td><span style="font-family:dejavusans;">&#8377;</span>' . htmlspecialchars($item->CurrenySymbol) . ' ' . number_format($item->Amount, 2,) . '</td>';
    //     $content .= '<td><span style="font-family:dejavusans;">&#8377;</span>' . htmlspecialchars($item->CurrenySymbol) . ' ' . $formatter->formatCurrency($item->Amount, 2, $item->CurrenySymbol) . '</td>';
    // } else {
    //     // $content .= '<td>' . htmlspecialchars($item->CurrenySymbol) . ' ' . number_format($item->Amount, 2, '.', ',') . '</td>';
    //     $content .= '<td>' . $formatter->formatCurrency($item->Amount, $item->CurrenySymbol) . '</td>';
    // }

    if (empty($item->CurrenySymbol)) {
        $content .= '<td><span style="font-family:dejavusans;">₹</span> ' . number_format($item->Amount, 2) . '</td>';
    } else {
        $content .= '<td><span style="font-family:dejavusans;">' . $item->CurrenySymbol . '</span> ' . number_format($item->Amount, 2) . '</td>';
    }
    $content .= ' </tr>';
}

$content .= '</tbody>
<tfoot>';
$totalAmount = 0;
foreach ($List as $item) {
    $totalAmount += $item->Amount;
}
$content .= '<tr>
                <td colspan="4">Total Amount:</td>';
if ($item->CurrenySymbol == "") {
    $content .= '<td><span style="font-family:dejavusans;">&#8377;</span>' . htmlspecialchars($item->CurrenySymbol) . ' ' . number_format($totalAmount, 2) . '</td>';
} else {
    $content .= '<td>' . htmlspecialchars($item->CurrenySymbol) . ' ' . number_format($totalAmount, 2) . '</td>';
}
$content .= '</tr>';
$content .= '</tfoot>
    </table>
    <div>
        <p>MODE OF IMPORT : ' . $List[0]->ModeOfImport . '<br>
        ' . $List[0]->Remarks . '</p>
        <p>GSTIN: 37AAACD7852Q1ZZ<br>
        IEC: 2697000283<br>
        PAN: AAACD7852Q<br><br>
        Thanking you<br><br>
        Yours faithfully<br>
        For DEVI FISHERIES LTD<br><br>
        Authorised Signatory
        </p>
    </div>
</body>
</html>';

$filename = 'Chemical_PO_Print(' . $ChemPoNo . ').doc';
header("Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");
echo $content;
