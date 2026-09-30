<?php

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");


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
//if(isset($Month)){

$StartDate = $Year . '-' . $Month . '-' . '01';
$EndDate = $Year . '-' . $Month . '-' . '31 23:59:59';


$Query = '';

if ($PsID != '') {
	$Query .= " and PurchaseStationID='$PsID'";
} else {
	$Query .= '';
}


if ($Type == 1) {
	$Query = "month(PurchaseDate)='$Month'  and year(PurchaseDate)='$Year'  $Query";
} else {
	$Query = "PurchaseDate between '$Month'  and '$Year'  $Query";
}


global $Query;
/*$EXCHANGE_RATE=$_POST['EXCHANGE_RATE'];
$Expanse=$_POST['Expanse'];
$DEPB=$_POST['DEPB'];
*/

//left join RM_PURCHASE_DETAILS as PD on IM.RmLotID= PD.LotID
$SqlHlReportsList = "
	select * from PpcHLGradingDetails as PHGD 
	left join RmLotDetatils AS RLD ON PHGD.LotID=RLD.RmLotID 
	left join RmBillDetails AS RBD ON RBD.ArrivalID=RLD.RmLotID 
	
	WHERE $Query
	 order by 
	RLD.PurchaseDate,RLD.LotNumber ASC";
//ECHO $SqlHlReportsList; EXIT;
$StmtHlReportsList = $iconn->query($SqlHlReportsList);



$BilledLotsCount = $StmtHlReportsList->num_rows;
$table = getCostingTable($iconn, $StmtHlReportsList, $BilledLotsCount);


//echo 'Ungraded Lots -'.$UN_GRADED_LOTS.' / Total Lots- '.$TOTAL_LOTS;
echo $table;



function getCostingTable($iconn, $StmtHlReportsList, $BilledLotsCount)
{
	global $RmBillDetails, $RmBillBreakup, $PpcHonGradingDetails, $PpcHonGradingBreakup, $PpcHLGradingBreakup;
	global $SalesCostingRates;
	$ARY_COSTING_RATES = array();

	$SQL_COSTING = "SELECT * FROM SalesCostingRates WHERE TrackID= (SELECT MAX(TrackID) from SalesCostingRates)";

	$STMT_SQL_COSTING = $iconn->query($SQL_COSTING);

	while ($ROW_COSTING = $STMT_SQL_COSTING->fetch_assoc()) {
		$ARY_COSTING_RATES[] = $ROW_COSTING;
	}

	$ARY_DAY_GRADES = array();


	$PO_TMSP = strtotime($date);
	$PURCHASE_DATE = date("d-m-Y", $PO_TMSP);


	$from_dt = date('d-m-Y', strtotime($StartDate));
	$to_dt = date('d-m-Y', strtotime($EndDate));


	$PO_TMSP = strtotime($date);
	$PURCHASE_DATE = date("d-m-Y", $PO_TMSP);

	$from_dt = date('d-m-Y', strtotime($StartDate));
	$to_dt = date('d-m-Y', strtotime($EndDate));
	$text = 4;
	$text1 = 2;

	$exchange_rate = $EXCHANGE_RATE;



	$content .= '<table border="1" cellspacing="0" cellpadding="1"> 
				
			  <tr> 
           		
                <th  align="CENTER" width="5%"><font size="' . $text . '"> <B>S No</B></font></th>
                <th  align="CENTER" width="5%"><font size="' . $text . '"> <B>DATE</B></font></th>
                <th  align="CENTER" width="5%"><font size="' . $text . '"> <B>BILL NO</B></font></th>';

	//foreach($ARY_COSTING_RATES as $ROW_COSTING){
	//$content .= ' <th  align="CENTER" width="5%"><font size="'.$text.'"> 
	//<B>'.$ROW_COSTING['GRADE'].'</B></font></th>';

	//}

	$content .= ' 
			   
			    <th  align="CENTER" width="5%"><font size="' . $text . '"> <B>HON.QTY</B></font></th>
				 <th  align="CENTER" width="5%"><font size="' . $text . '"> <B>HON WASTE</B></font></th>
			    <th  align="CENTER" width="5%"><font size="' . $text . '"> <B>HON/PACKING</B></font></th>
			    <th  align="CENTER" width="5%"><font size="' . $text . '"> <B>HON RATE</B></font></th>
				 
			  
			
			   
			   </tr>
				
			
               
                ';



	$SNO = 1;
	$PREV_DATE = '';


	while ($RowHlReportsList = $StmtHlReportsList->fetch_array()) {


		$LotID = $RowHlReportsList['RmLotID'];
		$sql = "SELECT * FROM `RmBillDetails` as RBD 
		 left join `RmBillBreakup` as RBB on RBB.ArrivalID=RBD.ArrivalID 
		 where RBD.ArrivalID='$LotID'";

		//ECHO  $sql; EXIT;
		$stmt = $iconn->query($sql);
		$bill_details = $stmt->fetch_array();

		$SqlHLGrading = "SELECT SUM(HLWeight) as HLWeight  FROM `PpcHLGradingBreakup` WHERE LotID='$LotID'";
		$StmtHLGrading = $iconn->query($SqlHLGrading);
		$RowHLGrading = $StmtHLGrading->fetch_array();
		$HLWeight = $RowHLGrading['HLWeight'];

		//$FWEIGHT=$aval_hl_reports_sql['WEIGHT_IN_KGS'];
		//$RATE=$bill_details['Rate'];
		$WEIGHT = $RowHlReportsList['PurchaseWeight'];

		//$LOT_COUNT=$bill_details['Count'];
		//$SHED_YIELD=$bill_details['Yield'];
		//$T_HON+=$WEIGHT;


		/*$DEFECT_WGT=$bill_details['DEFECT_WGT'];
	  $DEFECT_RATE=$bill_details['DEFECT_RATE'];*/
		/*$TOTAL_HON=$WEIGHT;
	  $TOTAL_HON_PRICE=$TOTAL_HON*$RATE;*/
		//$DEFECT_HON_PRICE=$DEFECT_WGT*$DEFECT_RATE;
		//$PURCHASE_AMOUNT=$TOTAL_HON_PRICE+$DEFECT_HON_PRICE;


		$LotNumber = $RowHlReportsList['LotNumber'];
		$PurchaseDate = $RowHlReportsList['PurchaseDate'];


		if ($PREV_DATE == '') {
			$PREV_DATE = $RowHlReportsList['PurchaseDate'];
		}


		//umask
		$PurchaseDate = date("d-m-Y", strtotime($PurchaseDate));

		/* */

		$content .= '
				<tr>  
           		
                <th  align="CENTER" width="5%"><font size="' . $text . '"><B>' . $SNO . '</B></font></th>
                <th  align="CENTER" width="5%"><font size="2"><B>' . $PurchaseDate . '</B></font></th>
                <th  align="CENTER" width="5%"><font size="' . $text . '"><B>' . $LotNumber . '</B></font></th>';
		$SNO++;

		$LotNumber = $RowHlReportsList['LotNumber'];
		$RATE = $RowHlReportsList['RATE'];
		$exchange_rate = $RowHlReportsList['EXCHANGE_RATE'];
		$Expance_kg = $Expanse;
		$DEPB = $DEPB;

		$PURCHASE_WEIGHT = $PURCHASE_WEIGHT + $RowHlReportsList['HON_QTY'];
		//$DAY_PUR_TOT+=$PURCHASE_AMOUNT;

		$data = $RowHlReportsList['HOSO_W_DATA'];
		$json_decode_data = json_decode($data, true);
		$ary_counts = array();
		$ary_samples = array();

		foreach ($json_decode_data as $value) {


			$ary_samples[] = $value;  // Colecting all weightments as single arrary					
			if (!in_array($value['Count'], $ary_counts, true)) {
				$ary_counts[] = $value['Count'];
			}
		}

		sort($ary_counts);


		$HL_LOT_WEIGHT = 0;

		$ary_lot_grade = array();

		foreach ($ARY_COSTING_RATES as $ROW_COSTING) {

			$GRADE_SLAB;
			$weight_sum = 0;
			$range = explode("/", $ROW_COSTING['Grade']);
			$start = (int) $range[0];
			$end = (int) $range[1];

			$aval = false;
			$weight_sum = 0;

			foreach ($ary_counts as $countt) {
				$count = (int) $countt;
				if ($count >= $start && $count <= $end) {
					$aval = true;

					foreach ($ary_samples as $value) {
						if ($value['Count'] == $countt) {
							$weight_sum = $weight_sum + $value['NetWeight'];
						}
					}
				}
			}



			$sample['GRADE'] = $ROW_COSTING['GRADE'];
			$sample['WEIGHT'] = $weight_sum;
			$ary_lot_grade[] = $sample;
			$HL_LOT_WEIGHT += $weight_sum;
			$HEADWAEST = $WEIGHT - $HLWeight;


			$HEADWAEST = 0;
			$SType = $_GET['SType'];
			if ($SType == "BT") {
				$HEADWAEST = $WEIGHT * 0.4;
			}
			if ($SType == "VM") {
				$HEADWAEST = $WEIGHT * 0.3;
			}
			$T_WST += $HEADWAEST;
		}

		$ARY_DAY_GRADES[] = $ary_lot_grade;


		/*$SqlDefectsDetails="SELECT * FROM `RM_DEFECTS_DETAILS` where LotID='$LotID'";
				  $StmtDefectsDetails=$iconn->query($SqlDefectsDetails);
				  
				  $DefectsWeight=0;
				  $DefectsPrice=0;
				   while($RowDefectsDetails=$StmtDefectsDetails->fetch_array()){
					  $DefectsWeight += $RowDefectsDetails['WEIGHT'];
					  $DefectsPrice += ($RowDefectsDetails['WEIGHT'] * $RowDefectsDetails['RATE']) ;
				   }
	  */

		/*   $SqlQcRmDetails="SELECT * from ORGN_INSP_REPORT where LotID='$LotID'";
				  $StmtQcRmDetails=$iconn->query($SqlQcRmDetails);
				  $RowQcRmDetails = $StmtQcRmDetails->fetch_assoc();
				  
				  
				  
				  
				  $LabDeductionKg= $RowQcRmDetails['DEDUCTIONS'];
				  $Remarks= $RowQcRmDetails['AUTH_REMARKS'];
				  $LabDeduction= $RowQcRmDetails['DEDUCTIONS']*$TOTAL_HON;
				  $AccDeductions= $AccDeductionsKg*$TOTAL_HON;
				  
				  $PURCHASE_AMOUNT=$PURCHASE_AMOUNT+$DefectsPrice;
				  $PURCHASE_AMOUNT=$PURCHASE_AMOUNT-$LabDeduction;
				  $PURCHASE_AMOUNT=$PURCHASE_AMOUNT+$AccDeductions;*/

		$SqlHonGrading = "SELECT * FROM `PpcHonGradingDetails` as HGD 
			left join PpcHonGradingBreakup as HGB on HGB.LotID=HGD.LotID
			WHERE HGD.LotID='$LotID'";

		//ECHO $SqlHonGrading; EXIT;
		$StmtHonGrading = $iconn->query($SqlHonGrading);
		$RowHonGrading = $StmtHonGrading->fetch_array();

		if ($RowHonGrading != 0) {
			$HonUsed = $RowHonGrading['HonWeight'];
			$HonUsedRate = $bill_details['Rate'];
			$HonUsedCount = $RowHonGrading['HonCount'];

			$HonUsedAmount = $HonUsedRate * $HonUsed;
		} else {
			$HonUsed = 0;
			$HonUsedAmount = 0;
			$HonUsedRate = 0;
		}



		$content .= '

<th  align="CENTER" width="5%"><font size="' . $text1 . '">' . $WEIGHT . '</font></th>
<th  align="CENTER" width="5%"><font size="' . $text1 . '">' . $HEADWAEST . '</font></th>
<th  align="CENTER" width="5%"><font size="' . $text1 . '">' . $HonUsed . '</font></th>
<th  align="CENTER" width="5%"><font size="' . $text1 . '">' . $HonUsedRate . '</font></th>



</tr>';
	}


	$content .= '<tr>
	
<td  align="CENTER" width="5%"><font size="' . $text . '"><B></B></font></td>';
	$content .= '<td  align="CENTER" width="5%"><font size="' . $text . '"><B></B></font></td>';
	$content .= '<td  align="CENTER" width="5%"><font size="' . $text . '"><B></B></font></td>';


	foreach ($ARY_COSTING_RATES as $ROW_COSTING) {
		$weight = 0;

		foreach ($ARY_DAY_GRADES as $ROW_GRADES) {
			foreach ($ROW_GRADES as $ROW_GRADE) {
				//echo '=='.$ROW_GRADE[0]['GRADE'];
				//print_r($ROW_GRADE);

				if ($ROW_GRADE['GRADE'] == $ROW_COSTING['Grade']) {
					//ECHO 'M'.$ROW_COSTING['COUNT'];
					$weight += $ROW_GRADE['WEIGHT'];
					$T_HL += $ROW_GRADE['WEIGHT'];
				}
				if ($ROW_weight['WEIGHT'] == $ROW_COSTING['WEIGHT']) {
				}
			}
		}
	}
	$ARY_DAY_GRADES = array();

	$content .= '<td  align="CENTER" width="5%"><font size="' . $text . '"><B></B></font></td>';

	//$DAY_PUR_TOT=0;
	$T_HL = 0;

	$content .= '  
				
				</table>';

	/*header("Content-Type: application/xls");
			   header("Content-Disposition: attachment; filename=download.xls");*/


	return $content;
}

/*function formatToInr($number){
		$number=round($number,2);
		// windows is not supported money_format
	if(setlocale(LC_MONETARY, 'en_IN')){
		 return money_format('%!'.$decimal.'n', $number);
		 }
	 else {
		if(floor($number) == $number) {
			$append='.00';
		}else{
			$append='';
		}
		$number = preg_replace("/(\d+?)(?=(\d\d)+(\d)(?!\d))(\.\d+)?/i", "$1,", $number);
		return $number.$append;
		}
		
	}*/
