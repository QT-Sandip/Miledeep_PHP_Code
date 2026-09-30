<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include($_SERVER['DOCUMENT_ROOT'] . "/Libraries/tcpdf/tcpdf.php");
include_once ROOT . 'config/api-call.php';


$api = API_STOCK_SUMMARY;

$FromDate = trim($_GET['FromDate']);
$ToDate = trim($_GET['ToDate']);

$bodyContent = array(
	'FromDate' => $FromDate,
	'ToDate' => $ToDate,
);

$result = CallAPI($api, "GET", "", $bodyContent);

// $error = $result->Error;
// echo $ID;
// var_dump($result);
// exit;


class MYPDF extends TCPDF
{
	// ... Your existing TCPDF customization code ...
}

$pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('STOCK SUMMARY : ');
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
$pdf->AddPage('P');

$data = getP_TABLE($result);
$pdf->writeHTML($data);

ob_end_clean();
$pdf->Output('StockSummary', 'I', '.pdf');

function getP_TABLE($result)
{
	$ToDate = trim($_GET['ToDate']);

	$ProductionData = $result->ProductionData;
	$InwardData = $result->InwardData->Data;
	$TotalInward = $result->InwardData->TotalInwardKGs;
	$OutWardData = $result->OutWardData->Data;
	$TotalOutWard = $result->OutWardData->TotalOutwardKGs;

	$ProductionKGs = $result->ProductionData->ProductionKGs;
	// $TotalInwardKGs = $TotalInward + $ProductionKGs;

	$width1 = 35;
	$width2 = 20;
	$width3 = 18;

	// $formattedDate = date('d-m-Y', strtotime($List[0]->FromDate));
	// $SType = $List[0]->SType;

	$content = ' <h5 align="center" width="16" ><font size="18">Stock Summary</font></h5>';

	$content .= '<table border="0" cellspacing="0" cellpadding="3"  style="float:right nobr="true"">';

	$content .= '<tr>  
					<th align="right" width="' . $width1 . '%"><font size="14">' . $ProductionData->ClosingMonth . ' CLOSING</font></th>
					<th align="left" width="' . $width2 . '%"><font size="14">' . $ProductionData->ClosingDate . '</font></th>
					<th align="left" width="' . $width1 . '%"><font size="14"> ' . $ProductionData->ClosingKGs . ' /-</font></th>
				</tr>';

	$content .= '<tr>  
					<th align="left" width="' . $width1 . '%"><font size="12"></font></th>
					<th align="left" width="' . $width2 . '%"><font size="12"></font></th>
					<th align="left" width="' . $width1 . '%"><font size="12"></font></th>
				</tr>';

	$content .= '<tr>  
           		 	<th align="left" width="' . $width1 . '%"><font size="12">' . strtoupper(date('F', strtotime($ToDate))) . ' PRODUCTION</font></th>
				 	<th align="right" width="' . $width2 . '%"><font size="12">+</font></th>
				 	<th align="right" width="' . $width3 . '%"><font size="12">' . $InwardData->Production . ' /- </font></th>
               	</tr>';

	$content .= '<tr>  
					<th align="left" width="' . $width1 . '%"><font size="12">REGLAGE</font></th>
					<th align="right" width="' . $width2 . '%"><font size="12">+</font></th>
					<th align="right" width="' . $width3 . '%"><font size="12">' . $InwardData->Reglazing . '/- </font></th>
				</tr>';

	$content .= '<tr>  
					<th align="left" width="' . $width1 . '%"><font size="12">STORAGE ONLY</font></th>
					<th align="right" width="' . $width2 . '%"><font size="12">+</font></th>
					<th align="right" width="' . $width3 . '%"><font size="12">' . $InwardData->StorageOnly . ' /- </font></th>
				</tr>';

	$content .= '<tr>  
					<th align="left" width="' . $width1 . '%"><font size="12">REPACKING</font></th>
					<th align="right" width="' . $width2 . '%"><font size="12">+</font></th>
					<th align="right" width="' . $width3 . '%"><font size="12">' . $InwardData->Repacking . ' /- </font></th>
				</tr>';
	$content .= '<tr>  
					<th align="left" width="' . $width1 . '%"><font size="12">MELTING</font></th>
					<th align="right" width="' . $width2 . '%"><font size="12">+</font></th>
					<th align="right" width="' . $width3 . '%"><font size="12">' . $InwardData->Melting . ' /- </font></th>
				</tr>';

	$content .= '<tr>  
					<th align="left" width="' . $width1 . '%" ><font size="12"></font></th>
					<th align="left" width="' . $width2 . '%" ><font size="12"><b>TOTAL</b></font></th>
					<th align="right" border="0" width="' . $width3 . '%" ><font size="12"><b>' . $TotalInward . ' /- </b></font></th>
				</tr>';

	$content .= '<tr>  
					<th align="left" width="' . $width1 . '%"><font size="12"></font></th>
					<th align="right" width="' . $width2 . '%"><font size="12"></font></th>
					<th align="left" width="' . $width1 . '%"><font size="12"></font></th>
				</tr>';

	$content .= '<tr>  
					<th align="left" width="' . $width1 . '%"><font size="12">SHIPMENTS</font></th>
					<th align="right" width="' . $width2 . '%"><font size="12">-</font></th>
					<th align="right" width="' . $width3 . '%"><font size="12">NA /- </font></th>
				</tr>';

	$content .= '<tr>  
					<th align="left" width="' . $width1 . '%"><font size="12">MELTING</font></th>
					<th align="right" width="' . $width2 . '%"><font size="12">-</font></th>
					<th align="right" width="' . $width3 . '%"><font size="12">' . $OutWardData->Melting . ' /- </font></th>
				</tr>';

	$content .= '<tr>  
					<th align="left" width="' . $width1 . '%"><font size="12">AUDIT OUT</font></th>
					<th align="right" width="' . $width2 . '%"><font size="12">-</font></th>
					<th align="right" width="' . $width3 . '%"><font size="12">' . $OutWardData->AuditOut . ' /- </font></th>
				</tr>';

	$content .= '<tr>  
					<th align="left" width="' . $width1 . '%"><font size="12">REPACKING</font></th>
					<th align="right" width="' . $width2 . '%"><font size="12">-</font></th>
					<th align="right" width="' . $width3 . '%"><font size="12">' . $OutWardData->Repacking . ' /- </font></th>
				</tr>';

	$content .= '<tr>  
					<th align="left" width="' . $width1 . '%"><font size="12">REGLAGE</font></th>
					<th align="right" width="' . $width2 . '%"><font size="12">-</font></th>
					<th align="right" width="' . $width3 . '%"><font size="12">' . $OutWardData->Reglazing . ' /- </font></th>
				</tr>';

	$content .= '<tr>  
					<th align="left" width="' . $width1 . '%"><font size="12">LOCAL SALES</font></th>
					<th align="right" width="' . $width2 . '%"><font size="12">-</font></th>
					<th align="right" width="' . $width3 . '%"><font size="12">' . $OutWardData->LocalSales . ' /- </font></th>
				</tr>';

	$content .= '<tr>  
					<th align="left" width="' . $width1 . '%"><font size="12"></font></th>
					<th align="left" width="' . $width2 . '%"><font size="12"><b>TOTAL</b></font></th>
					<th align="right" width="' . $width3 . '%"><font size="12"><b>' . $TotalOutWard . ' /- </b></font></th>
				</tr>';

	$content .= '<tr>  
					<th align="left" width="' . $width1 . '%"><font size="12"></font></th>
					<th align="left" width="' . $width2 . '%"><font size="12"></font></th>
					<th align="left" width="' . $width1 . '%"><font size="12"></font></th>
				</tr>';

	$GrandTotal = $TotalInward - $TotalOutWard;
	$content .= '<tr>  
					<th align="left" width="' . $width1 . '%"><font size="12"></font></th>
					<th align="left" width="' . $width2 . '%"><font size="12"><b>GRAND TOTAL</b></font></th>
					<th align="right" width="' . $width3 . '%"><font size="12"><b>' . $GrandTotal . ' /- </b></font></th>
				</tr>';

	$content .= '<tr>  
					<th align="left" width="' . $width1 . '%"><font size="12"></font></th>
					<th align="left" width="' . $width2 . '%"><font size="12"></font></th>
					<th align="left" width="' . $width1 . '%"><font size="12"></font></th>
				</tr>';

	$content .= '<tr>  
					<th align="left" width="' . $width1 . '%"><font size="12"></font></th>
					<th align="left" width="' . $width2 . '%"><font size="12"></font></th>
					<th align="left" width="' . $width1 . '%"><font size="12"></font></th>
				</tr>';

	$content .= '<tr>  
					<th align="right" width="' . $width1 . '%"><font size="14">' . strtoupper(date('F', strtotime($ToDate))) . '  CLOSING</font></th>
					<th align="left" width="' . $width2 . '%"><font size="14">' . date('d-m-Y', strtotime($ToDate)) . '</font></th>
					<th align="left" width="' . $width1 . '%"><font size="14">' . $GrandTotal . ' /-</font></th>
				</tr>';

	$content .= '</table>';

	return $content;
}
