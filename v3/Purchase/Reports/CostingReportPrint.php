<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
session_start();
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
require_once($_SERVER['DOCUMENT_ROOT'] . '/Libraries/tcpdf/tcpdf.php');

$LOT_ID = $_GET['LotID'];
$UNIT_ID = $_GET['U'];
$Download = $_GET['download'];



class MYPDF extends TCPDF
{

	public function Header()
	{
		global $NAME;
		global $LOT_NUMBER;

		// $this->Cell(0, 3, $NAME, 0,1, 'C', 0, '', 0, false, 'T', 'M');
		$this->Image($image_file, 10, 10, 35, 30, 'JPG', '', 'T', false, 400, '', false, false, 0, false, false, false);
		$this->SetFont('helvetica', 'B', 10);
		$this->Cell(0, 5, '', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
		$this->Cell(0, 5, $NAME, 0, 1, 'C', 0, '', 0, false, 'T', 'M');
		$this->Cell(0, 0, 'Costing Report  ', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
	}

	public function Footer()
	{
		$this->SetFont('helvetica', 'I', 8);
		$this->Image('images/jme_footer.jpg', 15, 260, 180, 23, 'JPG', '', '', false, 150, '', false, false, 0, false, false, false);
		$this->SetY(-55);
	}
}



$pdf = new MYPDF('L', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Costing Report');
$pdf->SetHeaderData('', '', '', '');
$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont('helvetica');
$pdf->SetFooterMargin(15);
$pdf->SetHeaderMargin(10);
$pdf->SetMargins(5, 10, 5, 10);
$pdf->setPrintHeader(true);
$pdf->setPrintFooter(false);

$pdf->SetAutoPageBreak(TRUE, 5);
// $pdf->SetFont('TIMES', '', 11);  
$pdf->AddPage('L');

$pdf->Cell(180, 20, '', 0, 1, 'C', 0);



$PurchaseData = getPurchaseTable($iconn, $LOT_ID);
$BillData = getBillTable($iconn, $LOT_ID);
$GradingData = getPackingBreakUp($iconn, $LOT_ID);
$CostingData = getCosingData($iconn, $LOT_ID);

$content = '<table border="0" cellspacing="0" cellpadding="3">
				
			   <tr>
			   <td width="70%">
			   <table border="0" cellspacing="0" cellpadding="3">
			   <tr><td>' . $PurchaseData . '</td></tr>
			   </table>
			   <table border="0" cellspacing="0" cellpadding="3">
			   <tr><td>' . $BillData . '</td></tr></table>
			   <table border="0" cellspacing="0" cellpadding="3">
			   <tr><td>' . $GradingData . '</td></tr></table>
			   </td>
				<td width="30%"> <table border="0" cellspacing="0" cellpadding="3">
			   <tr><td>' .

	$CostingData
	. '</td></tr></table></td>
			   
			   </tr>
			   
			   </table>';
$pdf->writeHTML($content);

ob_clean();
$file_name = 'Costing.pdf';
$pdf->Output($file_name, $Download ? 'D' : 'I');


function getPurchaseTable($iconn, $LotID)
{
	$SqlQuery = "SELECT *,PurchaseType,
(SELECT sum(HLWeight) from PpcHLGradingBreakup WHERE LotID=RMD.RmLotID )as HlWeight
FROM RmLotDetatils as RMD
left join RmPurchaseStations As RPS On RPS.PurchaseStationID=RMD.PurchaseStationID
left join PpcHLGradingDetails as HLG on HLG.LotID=RMD.RmLotID
left join RmPurchaseCategory as RPC on RPC.CatID=RMD.PurchaseCategoryID
where RmLotID='$LotID'";
	$StmtQuery = $iconn->query($SqlQuery);
	$RowLotDetails = $StmtQuery->fetch_array();

	$PurchaseDate = date("d-m-Y", strtotime($RowLotDetails['PurchaseDate']));
	$GradingDate = date("d-m-Y", strtotime($RowLotDetails['GradingDate']));
	$StationName = $RowLotDetails['StationName'];
	;
	$LotNumber = $RowLotDetails['LotNumber'];
	$PurchaseType = $RowLotDetails['PurchaseType'];
	$BreakTime = $RowLotDetails['BreakTime'];
	$HlWeight = $RowLotDetails['HlWeight'];
	$RmReferenceID = $RowLotDetails['RmReferenceID'];
	$GradingTime = $RowLotDetails['StartTime'] . ' - ' . $RowLotDetails['EndTime'];
	$TimeGap = strtotime($RowLotDetails['EndTime']) - strtotime("9:30");

	$TimeMin = floor($TimeGap / 60);
	$TimeGap = $TimeMin - $BreakTime;

	$HlWeightPerMin = round($HlWeight / $TimeGap);
	$HlWeightPerHour = $HlWeightPerMin * 60;

	if (($TimeGap / 60) > 1) {
		$TimeHours = $TimeGap / 60;
	} else {
		$TimeHours = 0;
	}

	$TimeMin = $TimeGap % 60;

	$TimeGap = round($TimeHours) . ' Hour : ' . $TimeMin . ' Min';


	$c = 100 / 7;
	$c = $c . '%';
	$text = 9;

	$content .= '<table border="1" cellspacing="0" cellpadding="3">  
				
			    <tr>            		
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"><B>Purchase Date</B></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"><B>Center</B></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"><B>Bill No</B></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"><B>Grading Date</B></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"><B>Grading Time</B></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"><B>Time Taken</B></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"><B>Avg Weight <br>(Hl Kg/Hour)</B></font></th>
                </tr>
				
				
				
				<tr>  
           		
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"><B style="color:red">' . $PurchaseDate . '</B></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"> ' . $StationName . '</font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"> <B>' . $LotNumber . '<br>' . $PurchaseType . '</B></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"><B>' . $GradingDate . '</B></font></th>
                <th  align="CENTER" width="' . $c . '" ><font size="' . $text . '"><B >' . $GradingTime . '</B></font></th>
                <th  align="CENTER" width="' . $c . '" ><font size="' . $text . '"><B >' . $TimeGap . '</B></font></th>
                <th  align="CENTER" width="' . $c . '" ><font size="' . $text . '"><B >' . $HlWeightPerHour . '</B></font></th>
            
				</tr>';

	$content .= '</table>';






	return $content;
}

function getBillTable($iconn, $LotID)
{



	$c = 100 / 8;
	$c = $c . '%';
	$text = 9;

	$SqlBillDetails = "SELECT *,RMFL.PanNumber,PurchaseDate,RBD.ArrivalID,
			(SELECT Sum(Weight*Rate) FROM RmBillBreakup WHERE ArrivalID=RBD.ArrivalID  ) as BillPrice,
			(SELECT Sum(Weight) FROM RmBillBreakup WHERE ArrivalID=RBD.ArrivalID  
			and (Description='HEAD ON' or  Description='Head On Packed') ) as HonTotal,
			(SELECT Sum(Weight) FROM RmBillBreakup WHERE ArrivalID=RBD.ArrivalID  
			and (Description!='HEAD ON' and  Description!='Head On Packed') ) as DefTotal
			
			FROM RmBillDetails as RBD 
			left join RmLotDetatils as RMD on RMD.RmReferenceID=RBD.ArrivalID
			left join QcRmOrgnReport as RQR on RMD.RmLotID=RQR.LotID
			left join RmSuppliers as RMFL on RMFL.ID=RBD.FinancierID
			
			WHERE RMD.RmLotID='$LotID'";
	$BillDetailsExec = $iconn->query($SqlBillDetails);
	$RowBillDetails = $BillDetailsExec->fetch_array();

	// echo json_encode($RowBillDetails, JSON_PRETTY_PRINT);
	// exit;
	$Yield = $RowBillDetails['Yield'];
	$ArrivalID = $RowBillDetails['ArrivalID'];
	$YieldPurchaseDate = $RowBillDetails['PurchaseDate'];
	$RmLotID = $RowBillDetails['RmLotID'];


	$SqlBillBrk = "SELECT *,DefectName FROM RmBillBreakup  
							LEFT join RmDefectsList on RmBillBreakup.Description=RmDefectsList.DefectID 
			where ArrivalID='$ArrivalID'";
	$BillBrkExec = $iconn->query($SqlBillBrk);




	$content .= '<table border="1" cellspacing="0" cellpadding="3">
				<tr>            		
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"><B>Description</B></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"><B>Machine</B></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"><B>Weight</B></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"><B>Count</B></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"><B>Rate</B></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"><B>Yield</B></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"><B>Add/Deductions</B></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"><B>Amount</B></font></th>
                </tr>';

	while ($RowBillBrkDetails = $BillBrkExec->fetch_array()) {
		if ($RowBillBrkDetails['DefectName'] == '') {
			$Description = $RowBillBrkDetails['Description'];
		} else {
			$Description = $RowBillBrkDetails['DefectName'];
		}
		$Weight = $RowBillBrkDetails['Weight'];
		$Count = $RowBillBrkDetails['Count'];
		$Rate = $RowBillBrkDetails['Rate'];
		$Amount = ($Weight * $Rate);
		if($Yield == ''){
			$sqlYield = "SELECT RPS.SType,RPC.IsCalcVales FROM RmLotDetatils AS RMD left JOIN RmPurchaseCategory as RPC  on RPC.CatID=RMD.PurchaseCategoryID left JOIN RmPurchaseStations as RPS  on RPS.PurchaseStationID=RMD.PurchaseStationID WHERE RMD.RmLotID=$RmLotID";

			$stmtYield = $iconn->query($sqlYield);
			$rowYield = $stmtYield->fetch_array();

			$SType = $rowYield['SType'];
			if ($rowYield['IsCalcVales'] == 0) {
				$HLWeight = 0;
				$SqlGrading = "SELECT HLWeight FROM `PpcHLGradingBreakup` where LotID='$LotID' Order by HLCount ASC";
				$StmtGrading = $iconn->query($SqlGrading);
				while ($RowGrading = $StmtGrading->fetch_assoc()) {
					$HLWeight = $HLWeight + $RowGrading['HLWeight'];
				}
				$Yield = round(($HLWeight / $Weight) * 100, 2);
			} else{
				$sql_yield = "SELECT * FROM `RmStandardYields`
				where Type='$SType' and EffectiveDate <= '$YieldPurchaseDate'
				and LowerCount <= '$Count'
				and UpperCount >='$Count' ORDER by EffectiveDate Desc LIMIT 1";
				$stmt_yield = $iconn->query($sql_yield);
				$row_yield = $stmt_yield->fetch_array();

				$Yield = $row_yield['Yield'];
			}
		}


		$content .= '<tr>  
			
				
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '">' . $Description . '</font></th>
				<th  align="CENTER" width="' . $c . '"><font size="' . $text . '"></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '">' . $Weight . '</font></th>            
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '">' . $Count . '</font></th>
				<th  align="CENTER" width="' . $c . '"><font size="' . $text . '">' . $Rate . '</font></th>
				<th  align="CENTER" width="' . $c . '"><font size="' . $text . '">' . $Yield . '</font></th>
				<th  align="CENTER" width="' . $c . '"><font size="' . $text . '"></font></th>
				
				 <th  align="RIGHT" width="' . $c . '"><font size="' . $text . '"><B>Rs. ' . round($Amount) . ' </B></font></th>
               
               </tr>';
	}




	$LabDeductionsKg = $RowBillDetails['ActualDeductions'];
	$AccDeductionsKg = $RowBillDetails['Deductions'];
	$HonTotal = $RowBillDetails['HonTotal']; //Excluding Defects
	$DefTotal = $RowBillDetails['DefTotal']; //Excluding Hon And HlHon
	$TotalWeight = $HonTotal + $DefTotal;
	$BillPrice = $RowBillDetails['BillPrice'];
	$BillPanNumber = $RowBillDetails['PanNumber'];
	$PurchaseDate = $RowBillDetails['PurchaseDate'];
	$CreditRemarks = $RowBillDetails['CreditRemarks'];
	$CreditRemarks = $RowBillDetails['CreditRemarks'];
	$InterestRate = $RowBillDetails['InterestRate'];
	$CreditDays = $RowBillDetails['CreditDays'];
	if ($RowBillDetails['FinancialDate']) {
		$DueDate = date('d-m-Y', strtotime($RowBillDetails['FinancialDate']));
	} else {
		$DueDate = '';
	}

	$LabDeductions = round($HonTotal * $LabDeductionsKg);
	$AccDeductions = round($HonTotal * $AccDeductionsKg);

	//$BillPrice=$BillPrice+$AccDeductions-$LabDeductions; 

	if ($PurchaseDate > '2021-06-30') {
		$BillTotal = $BillPrice + $AccDeductions - $LabDeductions;
		$TdsAmount = ($BillTotal * $Tdsperc) / 100;

		$aryInterestRequest['InterestRate'] = $InterestRate;
		$aryInterestRequest['CreditDays'] = $CreditDays;
		$aryInterestRequest['BillTotal'] = $BillTotal;

		$aryInterestReponse = getInterestRate($aryInterestRequest);
		$TotalInterest = $aryInterestReponse['TotalInterest'];

		$BillTotal = $BillTotal + $TotalInterest;

		if (
			$BillPanNumber != '' &&
			strlen($BillPanNumber) == 10
		) {
			$TDSperc = '0.1%';
			$TdsAmount = ($BillTotal * 0.1) / 100;
		} else {
			$TDSperc = '5%';
			$BillPanNumber = 'NA';
			$TdsAmount = ($BillTotal * 5) / 100;
		}
		$content .= '';
		// 	$BillTotal = $BillTotal - $TdsAmount;
		// } else {
		// 	$BillTotal = $BillPrice + $AccDeductions - $LabDeductions;
		// }






		$content .= '<tr>  
           		
                <th  align="LEFT" width="' . $c . '"><font size="' . $text . '"><B>Lab</B></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"></font></th>

                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"><B></B></font></th>
				<th  align="CENTER" width="' . $c . '"><font size="' . $text . '"></font></th>
                <th  align="RIGHT" width="' . $c . '"><font size="' . $text . '"><B>- ' . $LabDeductionsKg . '</B></font></th>
                <th  align="RIGHT" width="' . $c . '"><font size="' . $text . '"><B>-' . $LabDeductions . '</B></font></th>
                </tr>
				<tr>  
           		
                <th  align="LEFT" width="' . $c . '"><font size="' . $text . '"><B>Accounts</B></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"></font></th>

                <th  align="CENTER" width="' . $c . '" ><font size="' . $text . '"></font></th>
				<th  align="CENTER" width="' . $c . '"><font size="' . $text . '"></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"><B></B></font></th>
				<th  align="CENTER" width="' . $c . '"><font size="' . $text . '"></font></th>
                <th  align="RIGHT" width="' . $c . '"><font size="' . $text . '"><B>' . $AccDeductionsKg . '</B></font></th>
                <th  align="RIGHT" width="' . $c . '"><font size="' . $text . '"><B>' . $AccDeductions . '</B></font></th>
                </tr>
				
				<tr>  
           		
                <th  align="LEFT" width="' . $c . '"><font size="' . $text . '"><B>Credit</B></font></th>
                <th  align="CENTER" colspan="3"  ><font size="' . $text . '">' . $CreditRemarks . '</font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"> <b>Due Date</b> : </font></th>
				<th  align="CENTER" width="' . $c . '"><font size="' . $text . '">' . $DueDate . '</font></th>
                <th  align="RIGHT" width="' . $c . '"><font size="' . $text . '"><B>' . $InterestRate . ' % </B></font></th>
                <th  align="RIGHT" width="' . $c . '"><font size="' . $text . '"><B>' . $TotalInterest . '</B></font></th>
                </tr>
				<tr>  
           		
                <th  align="LEFT" width="' . $c . '"><font size="' . $text . '"></font></th>
                <th  align="CENTER" colspan="5"><font size="' . $text . '"><b>Bill Total</b> </font></th>

				<th  align="RIGHT" width="' . $c . '"><font size="' . $text . '"></font></th>
                <th  align="RIGHT" width="' . $c . '"><font size="' . $text . '"><B>' . round($BillTotal) . '</B></font></th>
                </tr>
				<tr>  
           		
                <th  align="LEFT" width="' . $c . '"><font size="' . $text . '"><B>TDS</B></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '">PAN Number  </font></th>

                <th  align="CENTER" width="' . $c . '" ><font size="' . $text . '">' . $BillPanNumber . '</font></th>
				<th  align="CENTER" width="' . $c . '"><font size="' . $text . '"></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"><B></B></font></th>
				<th  align="CENTER" width="' . $c . '"><font size="' . $text . '"></font></th>
                <th  align="RIGHT" width="' . $c . '"><font size="' . $text . '"><B>' . $TDSperc . '</B></font></th>
                <th  align="RIGHT" width="' . $c . '"><font size="' . $text . '"><B>-' . round($TdsAmount) . '</B></font></th>
                </tr>';

		$BillTotal = $BillTotal - $TdsAmount;
	} else {
		$BillTotal = $BillPrice + $AccDeductions - $LabDeductions;
	}
	$content .= '<tr>  
           		
                <th  align="LEFT" width="' . $c . '"><font size="' . $text . '"><B>Total</B></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"></font></th>
                <th  align="CENTER" width="' . $c . '" ><font size="' . $text . '">' . round($TotalWeight, 2) . '</font></th>
				<th  align="CENTER" width="' . $c . '"><font size="' . $text . '"></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"><B></B></font></th>
                <th  align="CENTER" width="' . $c . '"><font size="' . $text . '"></font></th>
				<th  align="CENTER" width="' . $c . '"><font size="' . $text . '"></font></th>
                <th  align="RIGHT" width="' . $c . '"><font size="' . $text . '"><B>Rs. ' . round($BillTotal) . '</B></font></th>
                </tr>';


	$content .= '</table>';

	return $content;
}

function getPackingBreakUp($iconn, $LotID)
{

	global $RmReferenceID;

	$SqlHlBrk = "select RmReferenceID from RmLotDetatils where RmLotID='$LotID'";
	$HlExec = $iconn->query($SqlHlBrk);
	$RowHlData = $HlExec->fetch_array();
	$ArrivalID = $RowHlData['RmReferenceID'];

	$SqlHlBrk = "SELECT *,
			(select ROUND(AVG(Yield),2) from RmBillBreakup where ArrivalID='$ArrivalID') as Yield,
			(select sum(HLWeight) from PpcHLGradingBreakup where LotID='$LotID') as HLWeightTotal,
			(select PurchaseCount from RmLotDetatils where RmLotID='$LotID') as PurchaseCount,
			(select PurchaseDate from RmLotDetatils where RmLotID='$LotID') as PurchaseDate
			FROM `PpcHLGradingBreakup` where LotID='$LotID'";
	//ECHO  $SqlHlBrk ; EXIT;
	$HlExec = $iconn->query($SqlHlBrk);

	$size = 40;
	$size1 = 60;
	$textw = '30%';
	$text = 9;
	$Sno = 0;

	$content .= '<table border="1" cellspacing="0" cellpadding="3">
				<tr> 
				<th  align="CENTER" width="5%"><font size="' . $text . '"> <B>S.L</B></font></th>
                <th  align="CENTER" width="7%"><font size="' . $text . '"> <B>Count</B></font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '"> <B>Qty</B></font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '"><B>Estd Hon</B></font></th>
                <th  align="CENTER" width="8%"><font size="' . $text . '"><B>Packed %</B></font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '"><B>Estd Hon Ct</B></font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '"><B>Estd Hon Rate</B></font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '"><B>Total Pcs</B></font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '"><B>Avg Hon Ct</B></font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '"><B>Pur Ct</B></font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '"><B>Diff</B></font></th>
                </tr>';


	while ($RowHlData = $HlExec->fetch_array()) {


		$HLCount = $RowHlData['HLCount'];
		$HLWeight = $RowHlData['HLWeight'];
		$Yield = $RowHlData['Yield'];
		$HLWeightTotal = $RowHlData['HLWeightTotal'];
		$PurchaseCount = $RowHlData['PurchaseCount'];
		$PurchaseDate = $RowHlData['PurchaseDate'];

		$Pcs = $HLWeight * $HLCount * 2.2;
		$EstdHon = ($HLWeight / $Yield) * 100;
		$Packed = ($HLWeight / $HLWeightTotal) * 100;
		$EstdHonCt = ($Pcs / $EstdHon);
		$TotalEstdHon += $EstdHon;
		$TotalPacked += $Packed;
		$TotalPcs += $Pcs;

		$EstdHonRate = getPrice($iconn, $PurchaseDate, $EstdHonCt);
		$EstdHonRate = $EstdHon * $EstdHonRate;
		$TotalExpectedPrice += $EstdHonRate;

		$content .= '<tr>
					
				<th  align="CENTER" width="5%"><font size="' . $text . '"> ' . ++$Sno . '</font></th>
				<th style="color:red;" align="CENTER" width="7%"><font size="' . $text . '"> <b>' . $HLCount . ' </b></font></th>
				<th  align="CENTER" width="10%"><font size="' . $text . '">' . $HLWeight . '</font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '">' . ROUND($EstdHon, 2) . '</font></th>
                <th  align="CENTER" width="8%"><font size="' . $text . '">' . ROUND($Packed, 2) . '</font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '">' . ROUND($EstdHonCt, 2) . '</font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '">' . ROUND($EstdHonRate, 2) . '</font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '">' . ROUND($Pcs, 2) . '</font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '"><B></B></font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '"><B></B></font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '"><B></B></font></th>
                </tr>';
		$c++;
	}

	$AvgCount = $TotalPcs / $TotalEstdHon;
	$DiffCount = $PurchaseCount - $AvgCount;

	$content .= '
				
				<tr>  
           		
                <th  align="CENTER" width="5%"><font size="' . $text . '"> <B></B></font></th>
                <th  align="CENTER" width="7%"><font size="' . $text . '"> <B>TOTAL</B></font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '"> <B>' . $HLWeightTotal . '</B></font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '"><B>' . round($TotalEstdHon, 2) . '</B></font></th>
                <th  align="CENTER" width="8%"><font size="' . $text . '"><B>' . round($TotalPacked, 2) . '</B></font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '"><B></B></font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '"><B>' . round($TotalExpectedPrice, 2) . '</B></font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '"><B>' . round($TotalPcs, 2) . '</B></font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '"><B style="color:red" >' . round($AvgCount, 2) . '</B></font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '"><B style="color:red">' . round($PurchaseCount, 2) . '</B></font></th>
                <th  align="CENTER" width="10%"><font size="' . $text . '"><B style="color:red">' . round($DiffCount, 2) . '</B></font></th></tr>
								 
				
				
		</table>';

	return $content;
}

function getCosingData($iconn, $LotID)
{
	$text = 9;
	$width = 25;

	$SqlHlBrk = "select RmReferenceID from RmLotDetatils where RmLotID='$LotID'";
	$HlExec = $iconn->query($SqlHlBrk);
	$RowHlData = $HlExec->fetch_array();
	$ArrivalID = $RowHlData['RmReferenceID'];


	$SqlHlExp = "SELECT * FROM `RmCostingExpanses` where UpdatedDate <= (select PurchaseDate from RmLotDetatils where RmLotID='$LotID')  
			order by UpdatedDate desc  LIMIT 1";
	$HlExpExec = $iconn->query($SqlHlExp);
	$ExpRow = $HlExpExec->fetch_array();

	$ExchangeRate = $ExpRow['ExchangeRate'];
	$ExpanseKg = $ExpRow['Expanse'];
	$Depb = $ExpRow['DEPB'];

	$SqlHlBrk = "SELECT *,
			(select ROUND(AVG(Yield),2) from RmBillBreakup where ArrivalID='$ArrivalID') as Yield,
			(select sum(HLWeight) from PpcHLGradingBreakup where LotID='$LotID') as HLWeightTotal,
			(select PurchaseCount from RmLotDetatils where RmLotID='$LotID') as PurchaseCount			
			FROM `PpcHLGradingBreakup` where LotID='$LotID'";

	$HlExec = $iconn->query($SqlHlBrk);


	$content = '<table border="1" cellspacing="0" cellpadding="3">
				
			   <tr>  
           		
                <td  align="center" width="' . ($width * 2) . '%"><font size="' . $text . '"> <B>Exchange Rate</B></font></td>
				<td  align="center" width="' . ($width * 2) . '%"><font size="' . $text . '"><B>Rs .' . $ExchangeRate . ' /-</B></font></td>             
                </tr>
				
				<tr>            		
                <td  align="center" width="' . $width . '%"><font size="' . $text . '"> <B>Count</B></font></td>
                <td  align="center" width="' . $width . '%"><font size="' . $text . '"> <B>HL QTY</B></font></td>
                <td  align="center" width="' . $width . '%"><font size="' . $text . '"> <B>USD</B></font></td>
                <td  align="center" width="' . $width . '%"><font size="' . $text . '"><B>INR</B></font></td>            
                </tr>';



	while ($GradeRow = $HlExec->fetch_array()) {

		$PackingGrade = $GradeRow['PackingGrade'];
		$HLWeight = $GradeRow['HLWeight'];
		$HLWeightTotal = $GradeRow['HLWeightTotal'];
		$Usd = $GradeRow['Usd'];
		$SalePrice = $HLWeight * $Usd * $ExchangeRate;
		$TotalSalePrice += $SalePrice;

		$content .= '
				<tr>
				 <td align="right" width="' . $width . '%"><font size="' . $text . '"> ' . $PackingGrade . '</font></td>
				 <td  align="right" width="' . $width . '%"><font size="' . $text . '"> ' . $HLWeight . '</font></td>
				 <td  align="right" width="' . $width . '%"><font size="' . $text . '"> ' . $Usd . '</font></td>
				 <td  align="right" width="' . $width . '%"><font size="' . $text . '"> <B>' . $SalePrice . '</B></font></td>
                </tr>';
	}



	$TotalExpanse = $HLWeightTotal * $ExpanseKg;
	$TotalDepb = ($Depb * $TotalSalePrice) / 100;

	$SaleTotal = $TotalSalePrice + $TotalDepb;
	$SubTotal = $TotalSalePrice + $TotalDepb - $TotalExpanse;



	/* $SqlBillDetails = "SELECT *,
			   (SELECT Sum(Weight*Rate) FROM RmBillBreakup WHERE ArrivalID=RBD.ArrivalID 
			   and Description!='Head On Packed') as BillPrice,
			   (SELECT Sum(Weight) FROM RmBillBreakup WHERE ArrivalID=RBD.ArrivalID  
			   and Description!='Head On Packed' ) as HonTotal
			   
			   FROM RmBillDetails as RBD 
			   left join QcRmOrgnReport as RQR on RBD.ArrivalID=RQR.LotID
			   WHERE RBD.ArrivalID='$ArrivalID'"; 
	   $BillDetailsExec = $iconn->query($SqlBillDetails);
	   $RowBillDetails = $BillDetailsExec->fetch_array();*/

	$SqlBillDetails = "SELECT *,RMFL.PanNumber,PurchaseDate,RBD.ArrivalID,
	(SELECT Sum(Weight*Rate) FROM RmBillBreakup WHERE ArrivalID=RBD.ArrivalID  ) as BillPrice,
	(SELECT Sum(Weight) FROM RmBillBreakup WHERE ArrivalID=RBD.ArrivalID  
	and (Description='HEAD ON' or  Description='Head On Packed') ) as HonTotal,
	(SELECT Sum(Weight) FROM RmBillBreakup WHERE ArrivalID=RBD.ArrivalID  
	and (Description!='HEAD ON' and  Description!='Head On Packed') ) as DefTotal
	
	FROM RmBillDetails as RBD 
	left join RmLotDetatils as RMD on RMD.RmReferenceID=RBD.ArrivalID
	left join QcRmOrgnReport as RQR on RMD.RmLotID=RQR.LotID
	left join RmSuppliers as RMFL on RMFL.ID=RBD.FinancierID
	
	WHERE RMD.RmLotID='$LotID'";
	$BillDetailsExec = $iconn->query($SqlBillDetails);
	$RowBillDetails = $BillDetailsExec->fetch_array();




	$LabDeductionsKg = $RowBillDetails['ActualDeductions'];
	$AccDeductionsKg = $RowBillDetails['Deductions'];
	$HonTotal = $RowBillDetails['HonTotal']; //Excluding Defects
	$BillPrice = $RowBillDetails['BillAmount'];
	$InterestRate = $RowBillDetails['InterestRate'];
	$CreditDays = $RowBillDetails['CreditDays'];

	$LabDeductions = round($HonTotal * $LabDeductionsKg);
	$AccDeductions = round($HonTotal * $AccDeductionsKg);

	$BillPrice = $BillPrice + $AccDeductions - $LabDeductions;

	$aryInterestRequest['InterestRate'] = $InterestRate;
	$aryInterestRequest['CreditDays'] = $CreditDays;
	$aryInterestRequest['BillTotal'] = $BillPrice;

	$aryInterestReponse = getInterestRate($aryInterestRequest);
	$TotalInterest = $aryInterestReponse['TotalInterest'];

	// $BillPrice=$BillPrice+$TotalInterest;

	$SubTotal = $SubTotal - $TotalInterest;

	/*	
			   if($PurchaseDate > '2021-06-30'){
					  $BillTotal=$BillPrice+$AccDeductions-$LabDeductions; 			       
					  $TdsAmount=($BillTotal*$Tdsperc)/100;
					  if($BillPanNumber !='' && 
					  strlen($BillPanNumber) ==10){
						 $TDSperc='0.1%'; 
						$TdsAmount=($BillTotal*0.1)/100;  
					  }else{
						  $TDSperc='5%';
						  $BillPanNumber='NA';
						 $TdsAmount=($BillTotal*5)/100;
					  }
				  $content.='';
				  $BillTotal=$BillTotal-$TdsAmount;
				   
				  }else{
					 $BillTotal=$BillPrice+$AccDeductions-$LabDeductions; 
				  }
				  
				  */
	//echo $SubTotal; 
	$SaleStatus = round($SubTotal - $BillPrice);

	$TransactionKg = $SaleStatus / $HLWeightTotal;

	$AvgHlInr = $SubTotal / $HLWeightTotal;
	$AvgHlUsd = $AvgHlInr / $ExchangeRate;


	if ($SaleStatus > 0) {
		$Transaction = 'Gain';
	} else {
		$Transaction = 'Loss';
	}



	$content .= '
				<tr>  
           		
                <td  align="LEFT" width="' . $width . '%"><font size="' . $text . '"><B>Sale Value</B></font></td>
                <td  align="right" width="' . $width . '%"><font size="' . $text . '"> <B>' . $HLWeightTotal . '</B></font></td>
                <td  align="LEFT" width="' . $width . '%"><font size="' . $text . '"> <B></B></font></td>
                <td  align="right" width="' . $width . '%"><font size="' . $text . '"> <B>' . ($TotalSalePrice) . '</B></font></td>
                </tr>

				<tr>            		
                <th  align="LEFT" width="' . $width . '%"><font size="' . $text . '"><B>DEPB</B></font></th>
                <th  align="right" width="' . $width . '%"><font size="' . $text . '"><B> ' . $Depb . ' %</B></font></th>
                <th  align="LEFT" width="' . $width . '%"><font size="' . $text . '"> </font></th>
                <th  align="right" width="' . $width . '%"><font size="' . $text . '"> <B> + ' . round($TotalDepb, 2) . '</B></font></th>
                </tr>

				<tr>            		
                <th  align="LEFT" width="' . $width . '%"><font size="' . $text . '"><B>Sub Total</B></font></th>
                <th  align="LEFT" width="' . $width . '%"><font size="' . $text . '"><B></B></font></th>
                <th  align="LEFT" width="' . $width . '%"><font size="' . $text . '"> </font></th>
                <th  align="right" width="' . $width . '%"><font size="' . $text . '"> <B> ' . round($SaleTotal, 2) . '</B></font></th>
                </tr>

				<tr>            		
                <th  align="LEFT" width="' . $width . '%"><font size="' . $text . '"><B>Rm Value</B></font></th>
                <th  align="right" width="' . $width . '%"><font size="' . $text . '"><B>' . $HonTotal . '</B></font></th>
                <th  align="LEFT" width="' . $width . '%"><font size="' . $text . '"> </font></th>
                <th  align="right" width="' . $width . '%"><font size="' . $text . '"> <B> -' . round($BillPrice) . '</B></font></th>
                </tr>

				<tr>            		
                <th  align="LEFT" width="' . $width . '%"><font size="' . $text . '"><B>Interest</B></font></th>
                <th  align="right" width="' . $width . '%"><font size="' . $text . '"><B>' . $InterestRate . ' %</B></font></th>
                <th  align="LEFT" width="' . $width . '%"><font size="' . $text . '"> </font></th>
                <th  align="right" width="' . $width . '%"><font size="' . $text . '"> <B> -' . round($TotalInterest) . '</B></font></th>
                </tr>
				
				<tr>            		
                <th  align="LEFT" width="' . $width . '%"><font size="' . $text . '"><B>Expanse</B></font></th>
                <th  align="right" width="' . $width . '%"><font size="' . $text . '"> <B>' . ($ExpanseKg) . ' / kg</B></font></th>
                <th  align="LEFT" width="' . $width . '%"><font size="' . $text . '"> <B></B></font></th>
                <th  align="right" width="' . $width . '%"><font size="' . $text . '"> <B> - ' . ($TotalExpanse) . '</B></font></th>
                </tr>
				
				
				
				

				
				
				<tr>            		
                <th align="LEFT" width="' . $width . '%"><font size="' . $text . '"><B>Total ' . $Transaction . '</B></font></th>
                <th  align="LEFT" width="' . $width . '%"><font size="' . $text . '"> </font></th>
                <th  align="LEFT" width="' . $width . '%"><font size="' . $text . '"> </font></th>
                <th  align="right" width="' . $width . '%"><font size="' . $text . '">
				<B style="color:red"> ' . round($SaleStatus, 2) . '</B>
                
				</font></th>
				
				</tr><tr>            		
                <th align="LEFT" width="' . $width . '%"><font size="' . $text . '"><B>' . $Transaction . ' /Kg</B></font></th>
                <th  align="right" width="' . $width . '%"><font size="' . $text . '">Per Kg</font></th>
                <th  align="LEFT" width="' . $width . '%"><font size="' . $text . '"> </font></th>
                <th  align="right" width="' . $width . '%"><font size="' . $text . '">
				<B style="color:red"> ' . round($TransactionKg, 2) . '</B>
                
				</font></th>
				
				</tr><tr>            		
                <th align="LEFT" width="' . $width . '%"><font size="' . $text . '"><B>Average </B></font></th>
                <th  align="right" width="' . $width . '%"><font size="' . $text . '">INR/HL </font></th>
                <th  align="LEFT" width="' . $width . '%"><font size="' . $text . '"> </font></th>
                <th  align="right" width="' . $width . '%"><font size="' . $text . '">
				<B style="color:red"> ' . round($AvgHlInr, 2) . '</B>
                
				</font></th>
				
				</tr><tr>            		
                <th align="LEFT" width="' . $width . '%"><font size="' . $text . '"><B>Average</B></font></th>
                <th  align="right" width="' . $width . '%"><font size="' . $text . '"> USD </font></th>
                <th  align="LEFT" width="' . $width . '%"><font size="' . $text . '"> </font></th>
                <th  align="right" width="' . $width . '%"><font size="' . $text . '">
				<B style="color:red"> ' . round($AvgHlUsd, 2) . '</B>
                
				</font></th>
				
				</tr>
				
				</table>';



	return $content;
}


function getPrice($iconn, $PurchaseDate, $FINAL_COUNT)
{



	$REExpCount = 'NA';
	$RmPrice = 'NA';




	$SqlTrackId = "SELECT * FROM `RmPurchaseRatesHistory` where RmPriceDate <= '$PurchaseDate 23:59:59' 
		order by RmPriceDate DESC, ID DESC  LIMIT 1";
	$StmtTrackId = $iconn->query($SqlTrackId);
	$RowTrackId = $StmtTrackId->fetch_array();
	$TARCK_ID = $RowTrackId['ID'];
	$RmPriceDate = $RowTrackId['RmPriceDate'];

	$FINAL_COUNT;

	if ($FINAL_COUNT > 99) {
		$temp_c = 100;
		$sql_acr = "SELECT * FROM RmPurchaseRates WHERE DateID='$TARCK_ID' and RmCount ='$temp_c'";
		$FINAL_COUNT = round($FINAL_COUNT);
	} else if ($FINAL_COUNT > 52.25 && $FINAL_COUNT < 60.01) {
		$FINAL_COUNT = ceil($FINAL_COUNT);
		$sql_acr = "SELECT * FROM RmPurchaseRates WHERE DateID='$TARCK_ID' and RmCount ='$FINAL_COUNT'";
	} else if ($FINAL_COUNT > 62.25 && $FINAL_COUNT < 70.01) {
		$FINAL_COUNT = ceil($FINAL_COUNT);
		$sql_acr = "SELECT * FROM RmPurchaseRates WHERE DateID='$TARCK_ID' and RmCount ='$FINAL_COUNT'";
	} else if ($FINAL_COUNT > 72.25 && $FINAL_COUNT < 80.01) {
		$FINAL_COUNT = ceil($FINAL_COUNT);
		$sql_acr = "SELECT * FROM RmPurchaseRates WHERE DateID='$TARCK_ID' and RmCount ='$FINAL_COUNT'";
	} else if ($FINAL_COUNT > 82.25 && $FINAL_COUNT < 90.01) {
		$FINAL_COUNT = ceil($FINAL_COUNT);
		$sql_acr = "SELECT * FROM RmPurchaseRates WHERE DateID='$TARCK_ID' and RmCount ='$FINAL_COUNT'";
	} else if ($FINAL_COUNT > 92.25 && $FINAL_COUNT < 100) {
		$FINAL_COUNT = ceil($FINAL_COUNT);
		$sql_acr = "SELECT * FROM RmPurchaseRates WHERE DateID='$TARCK_ID' and RmCount ='$FINAL_COUNT'";
	} else {

		$TEMP_DIFF = $FINAL_COUNT - round($FINAL_COUNT);

		if (abs($TEMP_DIFF) > 0.25 && abs($TEMP_DIFF) < 0.76) {

			$TEMP_DIFF;
			if ($TEMP_DIFF > 0) {
				$LC = round($FINAL_COUNT);
				$UC = ceil($FINAL_COUNT);
			} else {
				$LC = round($FINAL_COUNT) - 1;
				$UC = round($FINAL_COUNT);
			}

			$sql_acr = "SELECT SUM(RmPrice/2) as RmPrice,SUM(RmCount/2) AS COUNT FROM RmPurchaseRates
				WHERE DateID='$TARCK_ID' and RmCount >='$LC' and RmCount <='$UC'";
		} else {

			$temp_c = round($FINAL_COUNT);
			$sql_acr = "SELECT * FROM RmPurchaseRates WHERE DateID='$TARCK_ID' and RmCount ='$temp_c'";
		}
	}

	//echo $sql_acr;


	$stmt_acr = $iconn->query($sql_acr);
	$row = $stmt_acr->fetch_array();
	$RmPrice = round($row['RmPrice'], 2);
	/*$data['RmPriceDate']=$RmPriceDate;
			$data['RmPrice']=$RmPrice;
			$data['BillHonCount']=$FINAL_COUNT;*/
	//return	 $data;

	if ($FINAL_COUNT > 99) {
		$DIFF = $FINAL_COUNT - 100;
		$RmPrice = $RmPrice - $DIFF;
	}
	return $RmPrice;
}

function getInterestRate($aryInterestRequest)
{

	$InterestRate = $aryInterestRequest['InterestRate'];
	$CreditDays = $aryInterestRequest['CreditDays'];
	$BillTotal = $aryInterestRequest['BillTotal'];

	$interestPerLakh = 100000 * ($InterestRate / 100);
	$interestPerLakh = round($interestPerLakh / 360);
	$TotalInterest = ($BillTotal / 100000) * $interestPerLakh;
	$TotalInterest = $TotalInterest * $CreditDays;
	$TotalInterest = round($TotalInterest);

	$aryInterestReponse['TotalInterest'] = $TotalInterest;
	return $aryInterestReponse;

}
