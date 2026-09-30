<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include($_SERVER['DOCUMENT_ROOT'] . "/Libraries/tcpdf/tcpdf.php");
include_once ROOT . 'config/api-call.php';

$api = API_BUYER_REPORT;
$BuyerID[] = $_GET['BuyerID'];

$ArrivalPlantName = trim($_GET['ArrivalPlantName']);

$bodyContent = array(
    'ProductionPlant' => trim($_GET["ProductionPlant"]),
    'Year' => trim($_GET["Year"]),
    'ConsigneeID' => trim($_GET['BuyerID']),
    // 'BuyerID' => trim($BuyerID),
);
// var_dump($bodyContent);
// exit;
$resList = CallAPI($api, "GET", "", $bodyContent);
// echo json_encode($result, JSON_PRETTY_PRINT);
$List = $resList->BuyerData;

class MYPDF extends TCPDF
{
}

$pdf = new MYPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Buyer Request List : ');
$pdf->SetHeaderData('', '', '', '');
$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont('helvetica');
$pdf->SetFont('dejavusans', '', 10);
$pdf->SetFooterMargin(1);
$pdf->SetHeaderMargin(1);
$pdf->SetMargins(PDF_MARGIN_LEFT, 1, PDF_MARGIN_RIGHT);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetAutoPageBreak(TRUE, 1);
$pdf->SetFont('helvetica', '', 10);
$pdf->AddPage();

$data = getP_TABLE($List, $ArrivalPlantName);
$pdf->writeHTML($data);

ob_end_clean();
$pdf->Output('Buyer Request List.pdf', 'I', '.pdf');

function getP_TABLE($List, $ArrivalPlantName)
{
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
            <div class="row justify-content-center">
                <div class=" text-center">
                    <div class="content">';
    if ($ArrivalPlantName != '') {
        $content .= '<div class="text-center">
        <h3 style=" text-align: center">' . $ArrivalPlantName . '</h3>
        </div>';
    }

    $content .= '<table class="table table-hover">
                <thead class="thead-light">
                    <tr style="font-weight: bold";>
                        <th>SNO</th>
                        <th>Po No</th>
                        <th>Product Description</th>
                        <th>Packing Style</th>
                        <th>Grades</th>
                        <th>Request Sent Date</th>
                        <th>Sample Sent Date</th>
                        <th>Report Expecting Date</th>
                    </tr>
                </thead>
                <tbody>';
    foreach ($List as $item) {
        $content .= '<tr style=" background-color: orange;">
                            <td colspan="8" style=" text-align: center"><b>' . $item->ConsigneeName . '</b></td>
                        </tr>';
        foreach ($item->BuyerBreakUpData as $ItemBreakUp) {
            $content .= '<tr>
                                <td>' . $ItemBreakUp->SNO . '</td>
                                <td>' . $ItemBreakUp->PoNo . '</td>
                                <td>' . $ItemBreakUp->ProductDescription . '</td>
                                <td>' . $ItemBreakUp->PackingStyle . '</td>
                                <td>' . $ItemBreakUp->Grade . '</td>
                                <td>' . $ItemBreakUp->RequestSentDate . '</td>
                                <td>' . $ItemBreakUp->SampleSendingDate . '</td>
                                <td>' . $ItemBreakUp->ReportExpectingDate . '</td>
                            </tr>';
        }
    }
    $content .= '</tbody>
            </table>';
    $content .= '</div>
            </div>
        </div>
        <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
        <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    </body>
    </html>';

    return $content;
}
