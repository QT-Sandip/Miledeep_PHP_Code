<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include($_SERVER['DOCUMENT_ROOT'] . "/Libraries/tcpdf/tcpdf.php");
include_once ROOT . 'config/api-call.php';

$api = API_CHEMICAL_PO_PRINT;

$ChemPoID = trim($_GET['ChemPoID']);
$bodyContent = array(
    'ChemPoID' => $ChemPoID
);
$result = CallAPI($api, "POST", "", $bodyContent);
// echo json_encode($result, JSON_PRETTY_PRINT);
$List = $result->Data;
$resTotals = $result->Total;

class MYPDF extends TCPDF {}

$pdf = new MYPDF('P', PDF_UNIT, 'A4', true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Chemical PO Print : ');
$pdf->SetHeaderData('', '', '', '');
$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont('helvetica');
$pdf->SetFont('dejavusans', '', 11);
$pdf->SetFooterMargin(1);
$pdf->SetHeaderMargin(1);
$pdf->SetMargins(PDF_MARGIN_LEFT, 1, PDF_MARGIN_RIGHT);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetAutoPageBreak(TRUE, 1);
$pdf->SetFont('helvetica', '', 11);
$pdf->AddPage();

$data = getP_TABLE($List, $resTotals);
$pdf->writeHTML($data);

// ob_end_clean();
$pdf->Output('Chemical PO Print', 'I', '.pdf');

function getP_TABLE($List, $resTotals)
{
    $formattedDate = date('d-m-Y', strtotime($List[0]->ChemPoDate));
    $ChemPoNo = $List[0]->ChemPoNo;
    $SupplierName = $List[0]->SupplierName;
    $SupplierAddress = $List[0]->SupplierAddress;

    $CurrencyCode = (!empty($List[0]->CurrencyCode)) ? $List[0]->CurrencyCode : '';
    // $CurrenySymbol = (!empty($List[0]->CurrenySymbol)) ? $List[0]->CurrenySymbol : '';

    $content = ' <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>DEVI FISHERIES LIMITED</title>
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.3.0/css/font-awesome.min.css">
        <style>
            .header {
                text-align: center;
                color: blue;
            }
            table {
                border-collapse: collapse;
                width: 100%;
            }
            th, td {
                border: 1px solid #000000;
                padding: 8px;
                text-align: center;
            }
            th {
                background-color: #f2f2f2;
                height: 100px;
            }
            td {
                height: 100px; 
            }
            tfoot td {
                height: 50px; 
            }
        </style>
    </head>
    <body>
        <div class="container-lg">
            <div><br></div>
            <div><br></div>
            <hr>
            <div class="row justify-content-center">
                <div class=" text-center">
                    <div class="content">
                        <div class="po-info">
                            PO NO : <b>' . $ChemPoNo . '</b><br>
                            Date: ' . $formattedDate . '
                        </div>
                        <div class="recipient">
                            The Sales Manager,<br>
                            ' . $SupplierName . '<br>
                            ' . nl2br($SupplierAddress) . '
                        </div>
                        <div class="letter">
                            Dear Sir,<br>
                            Sub: Purchase Order - Reg.<br>
                            Ref: Your E-mail quotation<br><br>
                            With reference to above we are pleased to place an order for the following goods for our Factor:<br>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="thead-light">
                                    <tr>
                                        <th style="width: 35px;">SNo</th>
                                        <th>Particulars</th>
                                        <th>Quantity (Kgs)</th>
                                        <th>Price</th>
                                  <th>Amount (' .htmlspecialchars($CurrencyCode) . ')</th>
                                    </tr>
                                </thead>
                                <tbody>';
    foreach ($List as $item) {
        $content .= '
                                    <tr>
                                        <td style="width: 35px;">' . $item->SNO . '</td>
                                        <td>' . $item->ChemicalName . '</td>
                                        <td>' . number_format($item->QuantityInKgs, 3) . '</td>
                                        <td>' . number_format($item->RatePerKgInRupees, 2) . '</td>';
        // if ($item->CurrenySymbol == "") {
        //     $content .= '<td><span style="font-family:dejavusans;">&#8377;</span>' . htmlspecialchars($item->CurrenySymbol) . ' ' .  number_format($item->Amount, 2) . '</td>';
        // } else {
        //     $content .= '<td>' . htmlspecialchars($item->CurrenySymbol) . ' ' .  number_format($item->Amount, 2) . '</td>';
        // }

        if (empty($item->CurrenySymbol)) {
    $content .= '<td><span style="font-family:dejavusans;"></span> ' . number_format($item->Amount, 2) . '</td>';
} else {
    $content .= '<td><span style="font-family:dejavusans;">' . $item->CurrenySymbol . '</span> ' . number_format($item->Amount, 2) . '</td>';
}

        $content .= ' </tr>';
    }
    $content .= '
                                </tbody>
                                <tfoot>';

    $totalAmount = 0;
    $finalCurrencySymbol = '₹'; // Default if no symbol is found

foreach ($List as $item) {
    $totalAmount += $item->Amount;
    if (!empty($item->CurrenySymbol)) {
        $finalCurrencySymbol = $item->CurrenySymbol;
    }
}

$content .= '<tr>
    <td colspan="4">Total Amount:</td>';

if (empty($finalCurrencySymbol)) {
    $content .= '<td><span style="font-family:dejavusans;">₹</span> ' . number_format($totalAmount, 2) . '</td>';
} else {
    $content .= '<td><span style="font-family:dejavusans;">' . htmlspecialchars($finalCurrencySymbol) . '</span> ' . number_format($totalAmount, 2) . '</td>';
}

    // foreach ($List as $item) {
    //     $totalAmount += $item->Amount;
        
    // }
    // $content .= '<tr>
    // <td colspan="4">Total Amount:</td>';
    // if ($item->CurrenySymbol == "") {
    //     $content .= '<td><span style="font-family:dejavusans;">&#8377;</span>' . htmlspecialchars($item->CurrenySymbol) . ' ' . number_format($totalAmount, 2) . '</td>';
    // } else {
    //     $content .= '<td>' . htmlspecialchars($item->CurrenySymbol) . ' ' . number_format($totalAmount, 2) . '</td>';
    // }
    $content .= '</tr>';
    $content .= '</tfoot>
                            </table>
                        </div>
                    </div>
                    <div>
                        MODE OF IMPORT : ' . $List[0]->ModeOfImport . ' <br>
                        ' . $List[0]->Remarks . '
                    </div>
                    <div>
                        GSTIN: 37AAACD7852Q1ZZ<br>
                        IEC: 2697000283 <br>
                        PAN: AAACD7852Q<br><br>
                    </div>
                    <div class="footer">
                        Thanking you<br><br>
                        Yours faithfully<br>
                        For DEVI FISHERIES LTD<br><br>
                        Authorised Signatory
                    </div>
                </div>
            </div>
        </div>
        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    </body>
    </html>';

    return $content;
}


// <div class="header">
// <h2><b>DEVI FISHERIES LIMITED</b></h2>
// <p>CIN: U05005AP1997PLC027100<br>
// (A Government of India Recognised Export House)<br>
// #6-21-7, East Point Colony, VISAKHAPATNAM-530 017, INDIA<br>
// TEL: 0891-2595672, 2595673 E-mail: accounts@devifisheries.com</p>
// </div>