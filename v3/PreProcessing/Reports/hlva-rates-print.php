<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include ($_SERVER['DOCUMENT_ROOT'] . "/Libraries/tcpdf/tcpdf.php");
include_once ROOT . 'config/api-call.php';


$api = API_HLVA_RATES_PRINT;

$RateID = trim($_GET['RateID']);

$bodyContent = array(
	'RateID' => $RateID
);

$result = CallAPI($api, "POST", "", $bodyContent);

$error = $result->Error;
$List = $result->Data;
// echo $List ;  exit;
class MYPDF extends TCPDF
{
	// ... Your existing TCPDF customization code ...
}

$pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('RATES HISTORY : ');
$pdf->SetHeaderData('', '', '', '');
$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont('helvetica');
$pdf->SetFooterMargin(15);
$pdf->SetHeaderMargin(15);
$pdf->SetMargins(PDF_MARGIN_LEFT, 15, PDF_MARGIN_RIGHT, 15);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetAutoPageBreak(TRUE, 15);
$pdf->SetFont('helvetica', '', 11);
$pdf->AddPage('L');

$data = getP_TABLE($List);
$pdf->writeHTML($data);

ob_end_clean();
$pdf->Output('Rates History', 'I', '.pdf');


function getP_TABLE($List)
{
	$width = 10;
	$formattedDate = date('d-m-Y', strtotime($List[0]->EffectiveDate));
	$Variety = $List[0]->ShortName;
	$ShiftID = $List[0]->Shift;
	$ContractorType = $List[0]->ContractorType;
	if ($ShiftID == 1) {
		$Shift = "Day Shift";
	} else if ($ShiftID == 2) {
		$Shift = "Night Shift";
	} else {
		$Shift = "Both";
	}

	$content = ' <h5 align="left" width="16" ><font size="9">DATE:' . $formattedDate . ' - VARIETY:' . $Variety . ' - SHIFT:' . $Shift . ' - Supplier type:' .$ContractorType.'</font></h5>';
	$content .= '<table border="1" cellspacing="0" cellpadding="3"  style="float:right nobr="true"">
			   <tr>  
           		 <th  align="center" width="' . $width . '%" ><font size="9"><b> Grade </b></font></th>
				 <th  align="center" width="' . $width . '%" ><font size="9">Rate</font></th>
               </tr>';
	if (!empty($List)) {
		foreach ($List as $item) {

			$content .= '<tr>  
           		 <td  align="center" width="' . $width . '%" ><font size="9"><b>' . $item->Grade . '</b></font></td>
				 <td  align="center" width="' . $width . '%" ><font size="9">' . $item->PricePerKg . '</font></td>
               </tr>';
		}
		$content .= '</table>';
		return $content;
	}
}
