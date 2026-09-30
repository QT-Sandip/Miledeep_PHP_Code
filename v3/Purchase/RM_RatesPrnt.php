<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
// exit;
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
include($_SERVER['DOCUMENT_ROOT'] . "/Libraries/tcpdf/tcpdf.php");

$ID = $_GET['id'];
$RateID = $ID;

class MYPDF extends TCPDF
{
}

$pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
$table_data = getP_TABLE($iconn, $RateID);


function getP_TABLE($iconn, $RateID)
{

	$SqlRates = "SELECT * FROM RmPurchaseRates WHERE DateID = $RateID ORDER BY ABS(RmCount) ASC";
	$StmtRates = $iconn->query($SqlRates);

	$sqlFields = "RmPriceDate, RmPH.SType, GROUP_CONCAT(RmPS.StationName ORDER BY RmPS.StationName ASC) AS PurchaseCenters";
	$SqlDate = "SELECT $sqlFields FROM RmPurchaseRatesHistory AS RmPH
    LEFT JOIN RmPurchaseStations AS RmPS ON FIND_IN_SET(RmPS.PurchaseStationID, RmPH.CenterID)
    WHERE RmPH.ID = $RateID";

	$StmtDate = $iconn->query($SqlDate);
	$RowDate = $StmtDate->fetch_array();
	$SType = $RowDate['SType'];
	$PurchaseCenters = $RowDate['PurchaseCenters'];
	$UpdatedDate = date("d-m-Y", strtotime($RowDate['RmPriceDate']));

	$AryRates = [];
	while ($row = $StmtRates->fetch_array()) {
		$AryRates[] = $row;
	}

	$firstPageData = array_slice($AryRates, 0, 100);
	$secondPageData = array_slice($AryRates, 100);

	$contentPage1 = '';
	$contentPage1 .= '<h5 align="left"><font size="11">DATE: ' . $UpdatedDate . ' ' . $PurchaseCenters . ' (' . $SType . ')</font></h5>';
	$contentPage1 .= '<table border="1" cellspacing="0" cellpadding="3" style="float:right; nobr=true;">';

	$recordsPerColumn = 20;
	$columns = ceil(count($firstPageData) / $recordsPerColumn);

	$headers = [];
	for ($i = 0; $i < $columns; $i++) {
		$headers[] = 'Count';
		$headers[] = 'Price';
	}

	$contentPage1 .= '<tr>';
	foreach ($headers as $header) {
		$contentPage1 .= '<th align="center" style="width: 75px; border: 1px solid black;"><b>' . $header . '</b></th>';
	}
	$contentPage1 .= '</tr>';

	for ($rowIndex = 0; $rowIndex < $recordsPerColumn; $rowIndex++) {
		$contentPage1 .= '<tr>';
		for ($colIndex = 0; $colIndex < $columns; $colIndex++) {
			$recordIndex = $rowIndex + $colIndex * $recordsPerColumn;
			if ($recordIndex < count($firstPageData)) {
				$RmCount = $firstPageData[$recordIndex]['RmCount'];
				$RmPrice = $firstPageData[$recordIndex]['RmPrice'];
				$contentPage1 .= '<td align="center" style="width: 75px; border: 1px solid black;"><font size="10"><b>' . $RmCount . ' C</b></font></td>
                <td align="center" style="width: 75px; border: 1px solid black;">' . $RmPrice . '</td>';
			} else {
				$contentPage1 .= '<td style="border: 1px solid black;"></td><td style="border: 1px solid black;"></td>';
			}
		}
		$contentPage1 .= '</tr>';
	}
	$contentPage1 .= '</table>';


	$pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	$pdf->SetTitle('RATES LIST :');
	$pdf->SetHeaderData('', '', '', '');
	$pdf->AddPage('L');
	$pdf->SetMargins(PDF_MARGIN_LEFT, 15, PDF_MARGIN_RIGHT);
	$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
	$pdf->SetDefaultMonospacedFont('helvetica');
	$pdf->SetFooterMargin(15);
	$pdf->SetHeaderMargin(15);
	$pdf->setPrintHeader(false);
	$pdf->setPrintFooter(false);

	// $pdf->SetMargins(PDF_MARGIN_LEFT, 15, PDF_MARGIN_RIGHT, 15);

	$pdf->SetFont('helvetica', 'B', 13);
	$pdf->Cell(0, 5, 'RATES LIST ', 0, 1, 'C', 0, 'B', 0);
	$pdf->Cell(0, 5, '', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
	$pdf->SetFont('helvetica', '', 11);
	$pdf->writeHTML($contentPage1);

	if (!empty($secondPageData)) {
		$contentPage2 = '';
		$contentPage2 .= '<table border="1" cellspacing="0" cellpadding="3" style="float:right; nobr=true;">';

		$columns = ceil(count($secondPageData) / $recordsPerColumn);

		$headers = [];
		for ($i = 0; $i < $columns; $i++) {
			$headers[] = 'Count';
			$headers[] = 'Price';
		}

		$contentPage2 .= '<tr>';
		foreach ($headers as $header) {
			$contentPage2 .= '<th align="center" style="width: 75px; border: 1px solid black;"><b>' . $header . '</b></th>';
		}
		$contentPage2 .= '</tr>';

		for ($rowIndex = 0; $rowIndex < $recordsPerColumn; $rowIndex++) {
			$contentPage2 .= '<tr>';
			for ($colIndex = 0; $colIndex < $columns; $colIndex++) {
				$recordIndex = $rowIndex + $colIndex * $recordsPerColumn;
				if ($recordIndex < count($secondPageData)) {
					$RmCount = $secondPageData[$recordIndex]['RmCount'];
					$RmPrice = $secondPageData[$recordIndex]['RmPrice'];
					$contentPage2 .= '<td align="center" style="width: 75px; border: 1px solid black;"><font size="10"><b>' . $RmCount . ' C</b></font></td>
                <td align="center" style="width: 75px; border: 1px solid black;">' . $RmPrice . '</td>';
				} else {
					$contentPage2 .= '<td style="border: 1px solid black;"></td><td style="border: 1px solid black;"></td>';
				}
			}
			$contentPage2 .= '</tr>';
		}

		$contentPage2 .= '</table>';


		$pdf->AddPage('L');
		$pdf->SetMargins(PDF_MARGIN_LEFT, 15, PDF_MARGIN_RIGHT);
		$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
		$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
		$pdf->SetDefaultMonospacedFont('helvetica');
		$pdf->SetFooterMargin(15);
		$pdf->SetHeaderMargin(15);
		$pdf->setPrintHeader(false);
		$pdf->setPrintFooter(false);
		$pdf->SetHeaderData('', '', '', '');
		$pdf->SetFont('helvetica', '', 11);
		$pdf->writeHTML($contentPage2);
	}
	ob_end_clean();
	// $pdf->Output('Rates List', 'I', '.pdf');
	$pdf->Output('Rates_List.pdf', 'I');
}
