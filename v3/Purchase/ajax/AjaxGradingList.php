<?php

//ini_set( 'display_errors', 1 );

session_start();

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
$Month = $_POST['Month'];
$Month = sprintf("%02d", $Month);
$Year = $_POST['Year'];
$Type = $_POST['Type'];
$PsId = $_POST['PsId'];


$date_start = $Year . '-' . $Month . '-01';
$date_end = $Year . '-' . $Month . '-31  23:59:59';

if ($PsId != 0) {
	$PsId_SQL = "and RMD.PurchaseStationID='$PsId'";
} else {
	$PsId_SQL = "";
}


$showAddBill = false;
$showOptions = false;
$showAddCosting = false;
$showViewGrading = false;
$showEditGrading = false;
$BillWipeOff = false;



if ($_SESSION['D_ID'] == 3) {
	$showOptions = true;
	$showAddBill = true;
}

if ($_SESSION['D_ID'] == 5) {
	$showViewGrading = true;
}


if ($_SESSION['ACCESS_Type'] == 1) {

	$BillWipeOff = true;
}

if ($_SESSION['D_ID'] == 8) {

	// echo "hii";
	$showAddCosting = true;
	if ($_SESSION['ACCESS_Type'] == 1) {
		$showAddBill = true;
		$showEditGrading = true;
	}
}

//echo $showAddCosting;



$accessType = $_SESSION['ACCESS_Type'];

	$SqlBillQuery = "SELECT RMD.RmArrivalID as RmLotID, RMD.ArrivalNumber as LotNumber, RMD.BillDate as PurchaseDate, RMD.LotStatus, RmPC.PurchaseType, 
	GD.GradingDate, GD.LotID as GdAval, RMBD.ArrivalID as BillAval, RMBD.BillStatus, QCAbO.Name as AbStatus, 
	RmFin.FinancierName as FinancierName, RMBD.ArrivalID AS CostingAval,
	(SELECT COUNT(*) from QcRmOrgnReport as ORGD WHERE ORGD.LotID=RMD.RmArrivalID ) as OrgnReport from RmArrivalDetatils as RMD 
	LEFT join PpcHLGradingDetails as GD on RMD.RmArrivalID=GD.LotID 
	LEFT join RmBillDetails as RMBD on RMD.RmArrivalID=RMBD.ArrivalID 
	LEFT JOIN QcRmAbReport as QCAbR on RMD.RmArrivalID=QCAbR.LotID 
	LEFT JOIN QcABOptions as QCAbO on QCAbO.OptID=QCAbR.AbStatus 
	LEFT JOIN RmSuppliers as RmFin on RmFin.ID=RMBD.FinancierID 
	LEFT join RmArrivalDetatils as RAD on RAD.RmArrivalID=RMD.RmReferenceID
	LEFT JOIN RmPurchaseCategory as RmPC on RmPC.CatID=RAD.PurchaseCategoryID
	where BillDate between '$date_start' and '$date_end' $PsId_SQL ";

	// $SqlBillQuery = "SELECT RMD.RmArrivalID as RmLotID, RMD.ArrivalNumber as LotNumber, RMD.BillDate as PurchaseDate, RMD.LotStatus, RmPC.PurchaseType, 
	// GD.GradingDate, GD.LotID as GdAval, RMBD.ArrivalID as BillAval, RMBD.BillStatus, QCAbO.Name as AbStatus, 
	// RmFin.FinancierName as FinancierName, RMBD.ArrivalID AS CostingAval,
	// (SELECT COUNT(*) from QcRmOrgnReport as ORGD WHERE ORGD.LotID=RMD.RmArrivalID ) as OrgnReport from RmArrivalDetatils as RMD 
	// LEFT join PpcHLGradingDetails as GD on RMD.RmArrivalID=GD.LotID 
	// LEFT join RmBillDetails as RMBD on RMD.RmArrivalID=RMBD.ArrivalID 
	// LEFT JOIN QcRmAbReport as QCAbR on RMD.RmArrivalID=QCAbR.LotID 
	// LEFT JOIN QcABOptions as QCAbO on QCAbO.OptID=QCAbR.AbStatus 
	// LEFT JOIN RmSuppliers as RmFin on RmFin.ID=RMBD.FinancierID 
	// LEFT JOIN RmPurchaseCategory as RmPC on RmPC.CatID=RMD.PurchaseCategoryID 
	// where BillDate between '$date_start' and '$date_end' $PsId_SQL ";

	$SqlBillQuery = "SELECT RmLotID,RmReferenceID, LotNumber, PurchaseDate, RMD.LotStatus, RmPC.PurchaseType, 
	GD.GradingDate, GD.LotID as GdAval, RMBD.ArrivalID as BillAval, RMBD.BillStatus, QCAbO.Name as AbStatus, 
	RmFin.FinancierName as FinancierName, RMBD.ArrivalID AS CostingAval, 
	(SELECT COUNT(*) from QcRmOrgnReport as ORGD WHERE ORGD.LotID=RMD.RmLotID ) as OrgnReport from RmLotDetatils as RMD 
	LEFT JOIN PpcHLGradingDetails as GD on RMD.RmLotID=GD.LotID 
	LEFT JOIN RmArrivalDetatils as RAD on RAD.RmArrivalID=RMD.RmReferenceID
	LEFT JOIN RmBillDetails as RMBD on RMBD.ArrivalID=RMD.RmReferenceID 
	LEFT JOIN QcRmAbReport as QCAbR on RMD.RmLotID=QCAbR.LotID 
	LEFT JOIN QcABOptions as QCAbO on QCAbO.OptID=QCAbR.AbStatus 
	LEFT JOIN RmSuppliers as RmFin on RmFin.ID=RMBD.FinancierID 
	LEFT JOIN RmPurchaseCategory as RmPC on RmPC.CatID=RAD.PurchaseCategoryID 
	where PurchaseDate between '$date_start' and '$date_end' $PsId_SQL  GROUP BY RmLotID";

	// LEFT join RmArrivalDetatils as RAD on RAD.RmArrivalID=RMD.RmReferenceID
	// LEFT JOIN RmPurchaseCategory as RmPC on RmPC.CatID=RAD.PurchaseCategoryID


	// $SqlBillQuery = "SELECT  RmLotID,RmReferenceID, LotNumber,  PurchaseDate, RMD.LotStatus, RmPC.PurchaseType, 
	// GD.GradingDate, GD.LotID as GdAval, RMBD.ArrivalID as BillAval, RMBD.BillStatus, QCAbO.Name as AbStatus, 
	// RmFin.FinancierName as FinancierName, RMBD.ArrivalID AS CostingAval,
	// (SELECT COUNT(*) from QcRmOrgnReport as ORGD WHERE ORGD.LotID=RMD.RmLotID ) as OrgnReport from RmLotDetatils as RMD 
	// LEFT join PpcHLGradingDetails as GD on RMD.RmLotID=GD.LotID LEFT join RmBillDetails as RMBD on RMBD.ArrivalID=RMD.RmReferenceID
	// LEFT JOIN QcRmAbReport as QCAbR on RMD.RmLotID=QCAbR.LotID 
	// LEFT JOIN QcABOptions as QCAbO on QCAbO.OptID=QCAbR.AbStatus LEFT JOIN RmSuppliers as RmFin on RmFin.ID=RMBD.FinancierID 
	// LEFT JOIN RmPurchaseCategory as RmPC on RmPC.CatID=RMD.PurchaseCategoryID where PurchaseDate between '$date_start' and '$date_end' $PsId_SQL ";


	//  $SqlBillQuery="SELECT RMD.RmLotID, RMD.LotNumber, RMD.PurchaseDate, RMD.LotStatus, RmPC.PurchaseType, RMD.PurchaseWeight, RMBD.ArrivalID as BillAval, 
	// RMBD.BillStatus, RmFin.FinancierName as FinancierName, RMBD.ArrivalID AS CostingAval from RmLotDetatils as RMD LEFT join RmBillDetails as RMBD on RMD.RmLotID=RMBD.ArrivalID 
	// LEFT JOIN RmSuppliers as RmFin on RmFin.ID=RMBD.FinancierID LEFT JOIN RmPurchaseCategory as RmPC on RmPC.CatID=RMD.PurchaseCategoryID 
	// where PurchaseDate between '$date_start' and '$date_end' $PsId_SQL GROUP by RMD.RmReferenceID";

	// echo $SqlBillQuery; exit; 

$BillExec = $iconn->query($SqlBillQuery);





$content .= '<table  id="scroll-vertical-datatable"  data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">
      

                  <th>ID</th>
				  <th>Lot</th>
				  <th>Purchase Date</th>
				  <th>Grading Date</th>
				  <th>Bill Name</th>
				  <th>Type</th>
				 <!-- <th>Weight</th>
                  <th>AB Status</th>-->
				  <th>Status</th>
				  <th>Tools</th>
                </thead>
                <tbody>

			   ';


while ($BillRow = $BillExec->fetch_array()) {

	// var_dump($BillRow);
	// exit;

	if ($BillRow['AbStatus'] != '') {
		$AbStatus = $BillRow['AbStatus'];
		$Colour = $BillRow['Colour'];
	} else {
		$AbStatus = 'Test Pending';
		$Colour = 'gray';
	}

	if ($BillRow['LotStatus'] == 2) {
		$AbStatus = 'RETURN';
		$Colour = '#FF7F50';
	}

	$FinancierName = strlen($BillRow['FinancierName']) > 10 ? substr($BillRow['FinancierName'], 0, 15) . "..." : $BillRow['FinancierName'];
	$PurchaseDate = getFormatedDateDMMY($BillRow['PurchaseDate']);

	if ($FinancierName == '') {
		$FinancierName = 'NA';
	}

	if ($BillRow['GradingDate'] != '') {
		$GradingDate = getFormatedDateDMMY(substr($BillRow['GradingDate'], 0, 10));
	} else {
		$GradingDate = 'NA';
	}

	if ($BillRow['CostingDate'] != '') {
		$CostingDate = getFormatedDateDMMY($BillRow['CostingDate']);
	} else {
		$CostingDate = '';
	}

	$AddBill = false;
	if ($BillRow['LotStatus'] == 2) {
		$STATUS = 'Lot Return';
		$STATUS_CLR = '#f00';
	} else if ($BillRow['GdAval'] == '') {
		$STATUS = 'Grading Not Aval';
		$STATUS_CLR = '#f00';
	} else if ($BillRow['OrgnReport'] == 0) {
		$STATUS = 'Rm Report Not Aval';
		$STATUS_CLR = '#f00';
	} else if ($BillRow['BillAval'] == '') {
		$STATUS = 'Bill Not Aval';
		$STATUS_CLR = '#f00';
		$AddBill = true;
	} else if ($BillRow['BillStatus'] == '0') {
		$STATUS = 'Bill Approval Pending';
		$STATUS_CLR = '#f00';
		//$AddBill=true;
	} else if ($BillRow['BillAval'] != '') {
		$STATUS = 'Completed';
		$STATUS_CLR = 'Green';
	}

	$content .= '<tr>

                <td>' . $BillRow['RmLotID'] . '</td>
                <td><B>' . $BillRow['LotNumber'] . '</B></td>
                <td>' . $PurchaseDate . '</td>
                <td>' . $GradingDate . '</td>
                <td>' . $FinancierName . '</td>
                <td>' . $BillRow['PurchaseType'] . '</td>
				<!--<td  width="10%">' . $BillRow['PurchaseWeight'] . '</td>
				<td>
			    
				
				<div class="btn  badge badge-success font-size-12 "
				style="background: ' . $Colour . '"			>
				<span class="label" >' . $AbStatus . '</span>
				</div>
				</td>-->

				<td width="7%">
				<div style="background:' . $STATUS_CLR . '" class="btn btn-link badge badge-success font-size-12 " >
				<span class="label"  >' . $STATUS . '</span>
				</div>
			   
				</td>

		<td> ';

	if ($BillRow['BillStatus'] == '0' && $BillRow['LotStatus'] == 1) {
		$content .= '<a target="_blank" href="../Purchase/LotPreview?LotID=' . $BillRow['RmLotID'] . '" 
			class="mr-3 text-primary" data-toggle="tooltip" data-placement="top" 
			title="" data-original-title="Edit"><i class="dripicons dripicons-preview font-size-18"></i></a>';
	} else if ($AddBill) {
		// $content.='<a target="_blank" href="../Purchase/BillPreview?LotID='. $BillRow['RmLotID'].'" 
		// class="text-success" data-toggle="tooltip" data-placement="top" 
		// title="" data-original-title="Delete"><i class="mdi mdi-plus font-size-18"></i></a>';	
		$content .= '<a target="_blank" href="../Purchase/LotPreview?LotID=' . $BillRow['RmLotID'] . '" 
			class="mr-3 text-primary" data-toggle="tooltip" data-placement="top" 
			title="" data-original-title="Edit"><i class="dripicons dripicons-preview font-size-18"></i></a>';

		$content .= '<a target="_blank" href="rm-bill?ArrivalID=' . $BillRow['RmReferenceID'] . '" 
			class="text-success" data-toggle="tooltip" data-placement="top" 
			title="" data-original-title="Delete"><i class="mdi mdi-plus font-size-18"></i></a>';
	} else if ($BillRow['BillAval'] != '') {
		$content .= '<a target="_blank" href="LotPreview?LotID=' . $BillRow['RmLotID'] . '" 
					class="text-success" data-toggle="tooltip" data-placement="top" 
					title="" data-original-title="Delete"><i class="mdi mdi-pencil font-size-18"></i></a>
				  
					';
		$content .= '<a target="_blank" href="BillReports?LotID=' . $BillRow['RmLotID'] . '" 
					class="text-success" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete"><i class="mdi mdi-printer font-size-18"></i></a> ';
	} else if ($BillRow['OrgnReport'] == 0) {
		$content .= '<a target="_blank" href="../Purchase/LotPreview?LotID=' . $BillRow['RmLotID'] . '" 
					class="mr-3 text-primary" data-toggle="tooltip" data-placement="top" 
					title="" data-original-title="Edit"><i class="dripicons dripicons-preview font-size-18"></i></a>';
	} else {
		$content.='<a target="_blank" href="../Purchase/ReturnBill?LotID='. $BillRow['RmLotID'].'" 
		class="mr-3 text-primary" data-toggle="tooltip" data-placement="top" 
		title="" data-original-title="Edit"><i class="dripicons dripicons-preview font-size-18"></i></a>';
	}



	/*
				 
				 if($BillRow['LotStatus']==1){

					$content.='
			
			<a target="_blank" href="../Purchase/BillPreview?LotID='. $BillRow['RmLotID'].'" 
			class="text-success" data-toggle="tooltip" data-placement="top" 
			title="" data-original-title="Delete"><i class="mdi mdi-plus font-size-18"></i></a>
          
			';	

		$content.='<a target="_blank" href="../Purchase/LotPreview?LotID='. $BillRow['RmLotID'].'" 
		class="mr-3 text-primary" data-toggle="tooltip" data-placement="top" 
		title="" data-original-title="Edit"><i class="dripicons dripicons-preview font-size-18"></i></a>';	

		$content.='<a target="_blank" href="BillReports?LotID='. $BillRow['RmLotID'].'" 
			class="text-success" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete"><i class="mdi mdi-printer font-size-18"></i></a>
          
		  ';	
				 }
		 else{
		$content.='<a target="_blank" href="../Purchase/ReturnBill?LotID='. $BillRow['RmLotID'].'" 
		class="mr-3 text-primary" data-toggle="tooltip" data-placement="top" 
		title="" data-original-title="Edit"><i class="dripicons dripicons-preview font-size-18"></i></a>';
		} if($AddBill){
			$content.='
			
			<a target="_blank" href="../Purchase/BillPreview?LotID='. $BillRow['RmLotID'].'" 
			class="text-success" data-toggle="tooltip" data-placement="top" 
			title="" data-original-title="Delete"><i class="mdi mdi-plus font-size-18"></i></a>
          
			';	

			
		}else if($BillRow['BillStatus']=='1' && $BillRow['LotStatus']==1){
			$content.='<a target="_blank" href="../Purchase/RmBill?LotID='. $BillRow['RmLotID'].'" 
			class="text-success" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete"><i class="mdi mdi-printer font-size-18"></i></a>
          ';


			$content.='<a target="_blank" href="BillReports?LotID='. $BillRow['RmLotID'].'" 
			class="text-success" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete"><i class="mdi mdi-printer font-size-18"></i></a>
          
		  ';		  
		}

		*/



	$content .= ' </td></tr>';
}

$content .= '</tbody></table>';

echo $content;
