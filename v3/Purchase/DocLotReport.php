<style>
	table tr td {

		font-size: 12px;
		text-align: left;
	}

	table tr th {

		font-size: 15px;
		text-align: left;
		background: white;
		position: sticky;
		top: 0;
	}
</style>

<?php
//ini_set( 'display_errors', 1 );
session_start();

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
include($_SERVER['DOCUMENT_ROOT'] . "/Libraries/tcpdf/tcpdf.php");

$QueryParms = $_GET['Q'];

if ($QueryParms != '') {
	$QueryParms = urlDecode64($QueryParms);
	$queries = array();
	parse_str($QueryParms, $_GET);
}

$Type = $_GET['Type'];

$Month = $_GET['Month'];
$Year = $_GET['Year'];
$PsID = $_GET['PsID'];
$SType = $_GET['SType'];

if ($PsID != '') {
	$PsID = $_GET['PsID'];
	$Query = "and PurchaseStationID='$PsID'";
} else {
	$Query = "";
}


$table_data = getP_TABLE($iconn, $from_date, $to_date);
echo $table_data;


//$pdf->writeHTML($table_data);


$file_name = 'DS.pdf' . $row['PO_NUM'];


function getP_TABLE($iconn, $from_date, $to_date)
{

	$date_end = $date . ' 23:59:59';
	global $Query, $Month, $Year, $Type, $SType;

	if ($Type == 1) {
		$Query = "month(PurchaseDate)='$Month'  and year(PurchaseDate)='$Year'  $Query";
	} else {
		$Query = "PurchaseDate between '$Month'  and '$Year'  $Query";
	}

	if ($SType != '') {
		$Query .= " and Species ='$SType'";
	}


	// 	 $LotQuery="select PurchaseDate,RmLotID,LotNumber,FinancierName,RF.PanNumber,ActualDeductions,Deductions,Yield, 
	// (SELECT sum(HLWeight) from PpcHLGradingBreakup WHERE  LotID=RBD.ArrivalID ) as HLWeight, 
	// (SELECT Weight from RmBillBreakup WHERE Description='Hl Grading Hon' and LotID=RMD.RmLotID ) as HONWeight, 
	// (SELECT Count from RmBillBreakup WHERE Description='Hl Grading Hon' and LotID=RMD.RmLotID ) as HONCount, 
	// (SELECT Rate from RmBillBreakup WHERE Description='Hl Grading Hon' and LotID=RMD.RmLotID ) as HONRate, 
	// (SELECT GROUP_CONCAT(Weight) from RmBillBreakup WHERE Description='Hon Packed' and LotID=RMD.RmLotID ) as HonPackedWeight,
	// (SELECT GROUP_CONCAT(Count) from RmBillBreakup WHERE Description='Hon Packed' and LotID=RMD.RmLotID ) as HonPackedCount, 
	// (SELECT GROUP_CONCAT(Rate) from RmBillBreakup WHERE Description='Hon Packed' and LotID=RMD.RmLotID ) as HonPackedRate, 
	// (SELECT sum(Weight*Rate) from RmBillBreakup WHERE Description='Hon Packed' and LotID=RMD.RmLotID ) as HonPackedPrice, 
	// (SELECT GROUP_CONCAT(DefectName) from RmBillBreakup left join RmDefectsList on RmBillBreakup.Description=RmDefectsList.DefectID and LotID=RMD.RmLotID ) as DefectName, 
	// (SELECT GROUP_CONCAT(Rate) from RmBillBreakup WHERE Description!='Hon Packed' and Description!='Hl Grading Hon' and LotID=RMD.RmLotID ) as DefectRate,
	// (SELECT sum(Weight*Rate) from RmBillBreakup WHERE Description!='Hon Packed' and Description!='Hl Grading Hon' and LotID=RMD.RmLotID ) as DefectPrice, RUL_G.UserName as GraderName,RUL_A.UserName as AgentName 

	// from RmLotDetatils as RMD left join RmUsersList as RUL_G on RMD.GraderID=RUL_G.ID 
	// left join RmUsersList as RUL_A on RMD.AgentID=RUL_A.ID 
	// left JOIN RmBillDetails as RBD on RMD.RmLotID=RBD.ArrivalID 
	// LEFT JOIN RmSuppliers as RF on RBD.FinancierID=RF.ID 
	// LEFT JOIN QcRmOrgnReport as RQR on RQR.LotID=RMD.RmLotID 
	// where   $Query
	// 	 ORDER BY PurchaseDate ASC,LotNumber ASC"; 

	$LotQuery = "select PurchaseWeight,PurchaseDate,RmLotID,RBD.ArrivalID,LotNumber,FinancierName,RF.PanNumber,ActualDeductions,Deductions,
	InterestRate,CreditDays,
(SELECT sum(HLWeight) from PpcHLGradingBreakup WHERE  LotID=RMD.RmLotID ) as HLWeight,
(SELECT sum(Weight) from RmBillBreakup WHERE Description='Head On' and ArrivalID=RMD.RmReferenceID  ) as HONWeight, 
(SELECT GROUP_CONCAT(Count) from RmBillBreakup WHERE Description='Head On' and ArrivalID=RMD.RmReferenceID  ) as HONCount, 
(SELECT GROUP_CONCAT(Rate) from RmBillBreakup WHERE Description='Head On' and ArrivalID=RMD.RmReferenceID  ) as HONRate, 
(SELECT GROUP_CONCAT(Weight) from RmBillBreakup WHERE Description='Hon Packed' and ArrivalID=RMD.RmReferenceID  ) as HonPackedWeight,
(SELECT GROUP_CONCAT(Count) from RmBillBreakup WHERE Description='Hon Packed' and ArrivalID=RMD.RmReferenceID  ) as HonPackedCount, 
(SELECT GROUP_CONCAT(Rate) from RmBillBreakup WHERE Description='Hon Packed' and ArrivalID=RMD.RmReferenceID  ) as HonPackedRate, 
(SELECT sum(Weight*Rate) from RmBillBreakup WHERE Description='Hon Packed' and ArrivalID=RMD.RmReferenceID  ) as HonPackedPrice, 
(SELECT GROUP_CONCAT(DefectName) from RmBillBreakup left join RmDefectsList on RmBillBreakup.Description=RmDefectsList.DefectID and ArrivalID=RMD.RmReferenceID  ) as DefectName, 
(SELECT GROUP_CONCAT(Rate) from RmBillBreakup WHERE Description!='Hon Packed' and Description!='Head On' and ArrivalID=RMD.RmReferenceID  ) as DefectRate,
(SELECT sum(Weight*Rate) from RmBillBreakup WHERE Description!='Hon Packed' and Description!='Head On' and ArrivalID=RMD.RmReferenceID  ) as DefectPrice,
RUL_G.UserName as GraderName,RUL_A.UserName as AgentName,
(SELECT PurchaseType FROM RmPurchaseCategory WHERE CatID = RMD.PurchaseCategoryID ) AS PurchaseType

from RmLotDetatils as RMD left join RmUsersList as RUL_G on RMD.GraderID=RUL_G.ID 
left join RmArrivalDetatils as RAD on RAD.RmArrivalID  = RMD.RmReferenceID 
left join RmUsersList as RUL_A on RMD.AgentID=RUL_A.ID 
left JOIN RmBillDetails as RBD on RMD.RmReferenceID=RBD.ArrivalID 
LEFT JOIN RmSuppliers as RF on RBD.FinancierID=RF.ID 
LEFT JOIN QcRmOrgnReport as RQR on RQR.LotID=RMD.RmLotID  
where $Query ORDER BY PurchaseDate ASC,LotNumber ASC";

	//echo $LotQuery ; exit; //EDITED QUERY  REPLACED RmLotID WITH RmReferenceID 
	$ExecLots = $iconn->query($LotQuery);



	$content = '';

	$size = 40;
	$size1 = 60;
	$w1 = "10";
	$TextL = 5;
	$TextS = 2;







	$content .= '<table id="example1" border="1" cellspacing="0" cellpadding="3">	

						
			

				

				<tr>  

                <th>S No</th>
				<th>Purchase Date</th>
                <th>Lot Number</th>
				<th>Bill Name </th>
				<th>Pan Number </th>
                <th>Agent Name</th>
				<th>Site Weighment BY</th>
				<th>Grader Name</th>';
				// <th>Defects</th>
				// <th>Defect Rate</th>
				// <th>Head on Qty(Packing)</th>				
				// <th>Head on Count(Packing)</th>
				
				// <th>Head on Rate(Packing)</th>							
	$content .= '<th>Head on Weight</th>
				<th>Head on Count</th>
				
				<th>Rate</th>
				<th>Headless Qty</th>
				<th>Lab Deduction</th>			
				<th>Acc Deduction</th>			
				<th>TDS</th>			
				<th>Credit</th>			
				
                <th>Yield(%) </th>
				<th>Bill Amount</th>
				</tr>';






	$C = 0;
	$SNo = 0;
	$WEIGHT_TOTAL = 0;
	while ($RowLots = $ExecLots->fetch_array()) {

		$PurchaseDate = date('d-m-Y', strtotime($RowLots['PurchaseDate']));
		$LotNumber = $RowLots['LotNumber'];
		$RmLotID = $RowLots['RmLotID'];
		$BillName = $RowLots['FinancierName'];
		$PanNumber = $RowLots['PanNumber'];
		$AgentName = $RowLots['AgentName'];
		$PurchaseType = $RowLots['PurchaseType'];
		$GraderName = $RowLots['GraderName'];
		$HONWeight = $RowLots['HONWeight'];
		$HONCount = $RowLots['HONCount'];
		$HONRate = $RowLots['HONRate'];
		$HonPackedWeight = $RowLots['HonPackedWeight'];
		$HonPackedCount = $RowLots['HonPackedCount'];
		$HonPackedRate = $RowLots['HonPackedRate'];
		$DefectsName = $RowLots['DefectName'];
		$DefectRate = $RowLots['DefectRate'];
		$DefectAmount = $RowLots['DefectPrice'];

		$LabDeduction = $RowLots['ActualDeductions'];
		$AccDeduction = $RowLots['Deductions'];
		$TdsDeduction = $RowLots['LotNumber'];
		$Yield = $RowLots['Yield'];
		$HLGrdngQty = $RowLots['HLWeight'];
		$RmQty = $RowLots['PurchaseWeight'];
		$GradYield=round(($HLGrdngQty/$RmQty)*100,2);
		$CreditDays = $RowLots['CreditDays'];
		$InterestRate = $RowLots['InterestRate'];

		$HeadOnAmount = $HONWeight * $HONRate;
		$HeadPackedAmount = $RowLots['HonPackedPrice'];

		$SqlHonGrdngQty = "SELECT SUM(HonWeight) as HonGrdngQty FROM `PpcHonGradingBreakup`WHERE LotID='$RmLotID'";
		$StmtHonGrdngQty = $iconn->query($SqlHonGrdngQty);
		$RowHonGrdngQty = $StmtHonGrdngQty->fetch_array();

		$HonGrdngQty = $RowHonGrdngQty['HonGrdngQty'];
		/*
			$SqlHLGrdngQty="SELECT SUM(HLWeight) as HLGrdngQty FROM `PpcHLGradingBreakup`WHERE LotID='$RmLotID'";
			$StmtHLGrdngQty=$iconn->query($SqlHLGrdngQty);
			$RowHLGrdngQty= $StmtHLGrdngQty->fetch_array();


			$HLGrdngQty=$RowHLGrdngQty['HLGrdngQty'];

			$TotalGrdngQty=$HonGrdngQty+$HLGrdngQty;
		*/



		$BillWeight = $HONWeight + $HonPackedWeight;
		$LabAmount = $BillWeight * $LabDeduction;	  // On Total Weight Hon+HonPacked+Defects
		$AccAmount = $BillWeight * $AccDeduction;  // On Total Weight

		$BillAmount = ($HeadOnAmount + $HeadPackedAmount + $DefectAmount + round($AccAmount)) - round($LabAmount);
		// $BillAmount = ($HeadOnAmount + $HeadPackedAmount + $DefectAmount + $AccAmount) - $LabAmount;

		// echo $BillAmount.' / ';

		$interestPerLakh = 100000 * ($InterestRate / 100);
		$interestPerLakh = round($interestPerLakh / 360);
		$TotalInterest = ($BillAmount / 100000) * $interestPerLakh;
		$TotalInterest = $TotalInterest * $CreditDays;
		$TotalInterest = round($TotalInterest);


		$BillAmount = $BillAmount + $TotalInterest;

		// echo $BillAmount;
		// echo $BillAmount;
		// exit;


		if ($PanNumber != '') {
		} else {
		}
		if ($PanNumber != '' && strlen($PanNumber) == 10) {
			$TDSperc = '0.1%';
			$TdsAmount = ($BillAmount * 0.1) / 100;
		} else {
			$TDSperc = '5%';
			$PanNumber = 'Wrong Pan Number' - $PanNumber;
			$TdsAmount = ($BillAmount * 5) / 100;
		}

		$TdsAmount = round($TdsAmount);
		// echo $TdsAmount; 
		//  $BillAmount=$RowLots['BillPrice']; //uma
		$BillAmount = $BillAmount - $TdsAmount; //uma
		//  $BillAmount=$RowLots['BillPrice']; //uma



		$HonPackedWeightTotal += $HonPackedWeight;
		$HONWeightTotal += $HONWeight;
		$HLGrdngQtyTotal += $HLGrdngQty;
		$BillAmountTotal += $BillAmount;
		$TdsAmountTotal += $TdsAmount;


		$content .= ' 
			
				
				<tr> 
				<td>' . ++$SNo . '</td>
				<td>' . $PurchaseDate . '</td>
				<td>' . $LotNumber . '</td>
				<td>' . $BillName . '</td>
				<td>' . $PanNumber . '</td>
				<td>' . $AgentName . '</td>
				<td>' . $PurchaseType . '</td>
				<td>' . $GraderName . '</td>';
				// <td>' . $DefectsName . '</td>
				// <td>' . $DefectRate . '</td>
				// <td>' . $HonPackedWeight . '</td>
				// <td>' . $HonPackedCount . '</td>
				// <td>' . $HonPackedRate . '</td>				
		$content .= ' 
				<td>' . $HONWeight . '</td>
				<td>' . $HONCount . '</td>				
				<td>' . $HONRate . '</td>
				<td>' . $HLGrdngQty . '</td>
				<td>' . $LabDeduction . '</td>
				<td>' . $AccDeduction . '</td>
				<td>' . $TdsAmount . '</td>
				<td>' . $TotalInterest . '</td>
				<td>' . $GradYield . '</td>
				<td>' . round($BillAmount) . '</td>
				</tr>';
	}


	$content .= '
				<tr>
				<th  align="left" width="5%"><font size="' . $TextS . '"></td>
				<th  align="left" width="20%"><font size="' . $TextS . '">Total Lots</td>
                <th  align="left" width="20%"><font size="' . $TextS . '">' . $SNo . '</td>
                <th  align="left" width="5%"><font size="' . $TextS . '"></td>
                <th  align="left" width="5%"><font size="' . $TextS . '"></td>
				<th  align="left" width="20%"><font size="' . $TextS . '">Total Weight(Kgs)</td>';
				// <th  align="left" width="20%"><font size="' . $TextS . '">--</td>
				// <th  align="left" width="20%"><font size="' . $TextS . '">--</td>
				// <th  align="left" width="20%"><font size="' . $TextS . '">' . $HonPackedWeightTotal . '</td>
				// <th  align="left" width="20%"><font size="' . $TextS . '">--</td>
				// <th  align="left" width="20%"><font size="' . $TextS . '">--</td>
	$content .= '
				<th  align="left" width="20%"><font size="' . $TextS . '">' . $HONWeightTotal . '</td>
				<th  align="left" width="20%"><font size="' . $TextS . '">--</td>
				<th  align="left" width="20%"><font size="' . $TextS . '">--</td>
				<th  align="left" width="20%"><font size="' . $TextS . '">' . $HLGrdngQtyTotal . '</td>
				<th  align="left" width="20%"><font size="' . $TextS . '">--</td>
				<th  align="left" width="20%"><font size="' . $TextS . '">--</td>
				<th  align="left" width="20%"><font size="' . $TextS . '">' . $TdsAmountTotal . '</td>
				<th  align="left" width="20%"><font size="' . $TextS . '">--</td>
				<th  align="left" width="50%"><font size="' . $TextS . '">' . $BillAmountTotal . '/-</td>
				</tr>';

	$content .= '
		
		</table>';




	//header("Content-Type: application/xls");
	//	header("Content-Disposition: attachment; filename=Purchase_Report".$from_date."&".$to_date.".xls");


	return $content;
}

?>