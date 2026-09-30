<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include($_SERVER['DOCUMENT_ROOT'] . "/Libraries/tcpdf/tcpdf.php");
include_once ROOT . 'config/api-call.php';

$api = API_SYSCO_PSIP_REPORT_PDF;

$ArrivalPlantName = trim($_GET['ArrivalPlantName']);

$bodyContent = array(
    'PlantID' => trim($_GET["ProductionPlant"]),
    'Year' => trim($_GET["Year"]),
    'BuyerID' => trim($_GET['BuyerID']),
);

$resList = CallAPI($api, "GET", "", $bodyContent);
// echo json_encode($resList, JSON_PRETTY_PRINT);
// exit;
$List = $resList->SiscoData;

class MYPDF extends TCPDF
{
}

$pdf = new MYPDF('L', PDF_UNIT, 'A4', true, 'UTF-8', false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Sysco Psip Status: ');
$pdf->SetHeaderData('', '', '', '');
$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont('helvetica');
$pdf->SetFont('dejavusans', '', 5);
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

// ob_end_clean();
$pdf->Output('Sysco Psip Status.pdf', 'I', '.pdf');

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
				height: 30px;
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
                        <th>Vendor</th>
                        <th>Psip Id</th>
                        <th>Product</th>
                        <th>Psip Sent Date</th>
                        <th>Shipment Date</th>
						<th>Status</th>
                    </tr>
                </thead>
                <tbody>';
    foreach ($List as $item) {
        $content .= '<tr style=" background-color: orange;">
					<td colspan="8" style=" text-align: center"><b>' . $item->ConsigneeName . '</b></td>
				</tr>';

        foreach ($item->PoData as $ItemBreakUp) {
            $content .= '<tr>

											<td>' . $ItemBreakUp->SNO . '</td>
											<td>' . $ItemBreakUp->PoNo . '</td>
											<td>' . $item->ConsigneeName . '</td>
											<td>' . $ItemBreakUp->PsiID . '</td>
                                           <td>' . $ItemBreakUp->ShortName . '<br>' . $ItemBreakUp->Product . '</td>
											<td>' . $ItemBreakUp->PsipSentDate . '</td>
											<td>' . $ItemBreakUp->ShipmentDate . '</td>
											<td>' . $ItemBreakUp->DisplayMessage . '</td>
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
