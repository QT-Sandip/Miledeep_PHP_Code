<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include($_SERVER['DOCUMENT_ROOT'] . "/Libraries/tcpdf/tcpdf.php");
include_once ROOT . 'config/api-call.php';

$api = API_RM_PRICES;

$bodyContent = [
    'RateID' => trim($_GET['RateID'])
];
$result = CallAPI($api, "GET", "", $bodyContent);

$RmData = $result->data->rmDeductionData;
$RmBreakUpData = $result->data->rmDeductionBreakUpData;

$EffectedDate = date('d-m-Y', strtotime($RmData->EffectedDate));

class MYPDF extends TCPDF
{
    public function Header()
    {
        $this->SetFont('helvetica', 'B', 15);
        $this->Cell(0, 5, '', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
        $this->Cell(0, 5, $GLOBALS['CompanyName'], 0, 1, 'C', 0, '', 0, false, 'T', 'M');
    }

    public function Footer()
    {
        $dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
        $dt = $dateTime->format("d-m-Y  H:i:s");
        $this->SetFont('helvetica', 'I', 8);
        $this->Cell(0, 10, 'Dated ' . $dt . ', Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}

$pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('RM Price Variation : ');
$pdf->SetHeaderData('', '', '', '');
$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont('helvetica');
$pdf->SetFooterMargin(45);
$pdf->SetHeaderMargin(15);
$pdf->SetMargins(PDF_MARGIN_LEFT, 15, PDF_MARGIN_RIGHT, 45);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
// $pdf->setPrintFooter(true);
$pdf->SetAutoPageBreak(TRUE, 40);
$pdf->SetFont('helvetica', 'B', 13);
$pdf->AddPage('P');
$pdf->Cell(0, 5, '', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
$pdf->Cell(0, 5, 'RM Price Variation', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
$pdf->Cell(0, 5, '', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
$table_data = getP_TABLE((array) $RmData, (array) $RmBreakUpData);

$pdf->writeHTML($table_data);

$pdf->Output('RM Price Variation ' . $EffectedDate . '.pdf', 'I');

function getP_TABLE($RmData, $RmBreakUpData)
{
    $EffectedDate = date('d-m-Y', strtotime($RmData['EffectedDate']));
    $Species = $RmData['Species'];
    $PurchaseCategory = $RmData['PurchaseCategoryName'];

    $content = '';
    $content .= '<h5>' . $EffectedDate . ' - ' . $Species . ' - ' . $PurchaseCategory . '</h5>';
    $content .= '<table border="1" cellspacing="0" cellpadding="3">	
				<tr>  
					<th align="center" width="50px"><font size="9"><B>S No</B></font></th>
					<th align="center"><font size="9"><B>Start Count</B></font></th>
					<th align="center"><font size="9"><B>End Count</B></font></th>
					<th align="center"><font size="9"><B>Start Weight</B></font></th>
					<th align="center"><font size="9"><B>End Weight</B></font></th>
					<th align="center"><font size="9"><B>Extra Rate</B></font></th>
				</tr>';
    foreach ($RmBreakUpData as $Item) {
        $content .= '
				<tr>  
					<th align="center"><font size="9">' . $Item->SNo . '</font></th>
					<th align="center"><font size="9">' . $Item->StartCount . '</font></th>
					<th align="center"><font size="9">' . $Item->EndCount . '</font></th>
					<th align="center"><font size="9">' . $Item->StartWeight . '</font></th>
					<th align="center"><font size="9">' . $Item->EndWeight . '</font></th>
					<th align="center"><font size="9">' . $Item->Extra . '</font></th>
               </tr>';
    }
    $content .= '</table>';
    return $content;
}
