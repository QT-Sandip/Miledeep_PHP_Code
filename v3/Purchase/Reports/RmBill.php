<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$LotID = $_GET['LotID'];

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($_SERVER['DOCUMENT_ROOT'] . "/Libraries/tcpdf/tcpdf.php");
include($cp . "Includes/IncludePage.php");

$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
$IsHideCreditInfoAtRmBill = $ArrayClientConfigData['IsHideCreditInfoAtRmBill'];

$RmLotSql = "SELECT * FROM `RmLotDetatils`  WHERE `RmLotID`='$LotID'";
$RmLotStmt = $iconn->query($RmLotSql);
$RmLotRow = $RmLotStmt->fetch_assoc();

$ArrivalID = $RmLotRow['RmReferenceID'];
$LotNumber = $RmLotRow['LotNumber'];


$RmBillSql = "SELECT * FROM `RmBillDetails`  WHERE `ArrivalID`='$ArrivalID'";
$RmBillExec = $iconn->query($RmBillSql);
$RmBillRow = $RmBillExec->fetch_assoc();


$SqlLotDetails = "SELECT * FROM `RmArrivalDetatils` AS RLD LEFT
	 JOIN RmPurchaseStations AS RPS ON RLD.ArrivalStationID=RPS.PurchaseStationID
	 WHERE `RmArrivalID`='$ArrivalID'";
$StmtLotDetails = $iconn->query($SqlLotDetails);
$RowLotDetails = $StmtLotDetails->fetch_array();
$PurchaseType = $RowLotDetails['PurchaseType'];
$ProcessingStnID = $RowLotDetails['ProcessingStationID'];
$UNIT = $RowLotDetails['UNIT'];

$SqlClientDetails = "SELECT * FROM `RmProcessingStations` WHERE `ProcessingStationID`='$ProcessingStnID'";
$StmtClientDetails = $iconn->query($SqlClientDetails);
$RowClientDetails = $StmtClientDetails->fetch_array();

$CompanyName = $RowClientDetails['FullName'];
$GST = $RowClientDetails['GSTNumber'];
$Logo = $RowClientDetails['CompanyLogo'];

if ($RowClientDetails['StationName'] == 'Mourya') {
	$Logo = "https://camaroo.in/Images/mourya_Logo.png";
}

class MYPDF extends TCPDF
{

	public function Header()
	{

		// include  "../qc/Constants.php";
		global $UNIT;
		global $CompanyName;
		global $GST;
		global $Logo;


		$this->Image($Logo, 15, 5, 25, 25, 'JPG', '', 'T', false, 400, '', false, false, 0, false, false, false);
		$this->SetFont('helvetica', '', 6);
		$this->SetFont('helvetica', 'B', 12);
		$this->Cell(0, 5, '', 0, 1, 'L', 0, '', 0, false, 'T', 'M');
		//$this->Cell(0, 6, $BillCopy , 0,1, 'C', 0, '', 0, false, 'T', 'M');
		$this->Cell(0, 6, $CompanyName, 0, 1, 'C', 0, '', 0, false, 'T', 'M');
		$this->SetFont('helvetica', '', 10);
		$this->Cell(0, 6, 'GSTIN No : ' . $GST, 0, 1, 'C', 0, '', 0, false, 'T', 'M');
		$this->SetFont('helvetica', 'B', 10);
		$this->Cell(0, 6, 'RAW MATERIAL PURCHASE REPORT CUM RECEIVED NOTE', 0, 1, 'C', 0, '', 0, false, 'T', 'M');
	}

	public function Footer()
	{

		$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
		$dt = $dateTime->format("d-m-Y  H:i:s");
		$this->SetFont('helvetica', 'I', 8);
		//$this->Image('images/jme_footer.jpg', 15, 260, 180, 23, 'JPG', '', '', false, 150, '', false, false, 0, false, false, false);
		$this->Cell(0, 10, 'Dated ' . $dt . ', Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		$this->SetY(-5);
	}
}



$pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);



$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Purchase Bill: ');
$pdf->SetHeaderData('', '', '', '');
$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont('helvetica');
$pdf->SetFooterMargin(10);
$pdf->SetHeaderMargin(0);
$pdf->SetMargins(10, 25, 10, 5);
$pdf->setPrintHeader(true);
$pdf->setPrintFooter(true);

$pdf->SetAutoPageBreak(TRUE, 5);
$pdf->SetFont('helvetica', '', 11);
$pdf->AddPage();



$table_data = getDS_TABLE($iconn, $LotID);
$pdf->writeHTML($table_data);
$pdf->SetFont('helvetica', '', 6);
ob_end_clean();


$file_name = $LotNumber . '_bill.pdf';
$pdf->Output($file_name, 'I');





function getDS_TABLE($iconn, $LotID)
{

	global $ArrivalID;

	$BillQuery = "SELECT
    RMD.*,
	RLD.*,
	RFD.*,
	RUL1.*,
	QCR.*,
	QCOR.*,
    RUL1.UserName AS AgentName,
    RUL2.UserName AS DriverName,
    RUL3.UserName AS GraderName,
    QCR.AbStatus AS ABStatus,
    RPS.Location AS PurchaseCenter,
    RPC.PurchaseType AS PurchaseType,
    RVD.VehicalNumber AS TruckNumber,
    RBD.Remarks AS BillRemarks,
    RFD.BankName AS SupBankName,
    RFD.BankAccNo AS SupBankAccNo,
    RFD.IFSC AS SupIFSC,
    RFD.PanNumber AS SupPANNumber
FROM
    `RmArrivalDetatils` AS RMD
LEFT JOIN RmLotDetatils RLD ON
    RLD.RmReferenceID = RMD.RmArrivalID
LEFT JOIN RmBillDetails RBD ON
    RMD.RmArrivalID = RBD.ArrivalID
LEFT JOIN QcRmOrgnReport QCOR ON
    RLD.RmLotID = QCOR.LotID
LEFT JOIN RmSuppliers RFD ON
    RFD.ID = RBD.FinancierID
LEFT JOIN RmUsersList RUL1 ON
    RUL1.ID = RMD.AgentID
LEFT JOIN RmUsersList RUL2 ON
    RUL2.ID = RMD.DriverID
LEFT JOIN RmUsersList RUL3 ON
    RUL3.ID = RMD.GraderID
LEFT JOIN QcRmAbReport QCR ON
    QCR.LotID = RLD.RmLotID
LEFT JOIN RmPurchaseStations RPS ON
    RPS.PurchaseStationID = RMD.ArrivalStationID
LEFT JOIN RmPurchaseCategory RPC ON
    RPC.CatID = RMD.PurchaseCategoryID
LEFT JOIN RmVehicalDetails RVD ON
    RVD.ID = RMD.VehicalID
WHERE
    RLD.RmLotID = '$LotID'";

	$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
	$IsHideCreditInfoAtRmBill = $ArrayClientConfigData['IsHideCreditInfoAtRmBill'];

	// 	$BillQuery = "SELECT *,
// (SELECT UserName FROM `RmUsersList` WHERE ID=RMD.AgentID ) as AgentName,
// (SELECT UserName FROM `RmUsersList` WHERE ID=RMD.DriverID ) as DriverName,
// (SELECT UserName FROM `RmUsersList` WHERE ID=RMD.GraderID ) as GraderName,
// (SELECT AbStatus FROM `QcRmAbReport` WHERE LotID=RLD.RmLotID ) as ABStatus,
// (SELECT Location FROM `RmPurchaseStations` WHERE PurchaseStationID=RMD.ArrivalStationID ) as PurchaseCenter,
// (SELECT PurchaseType FROM `RmPurchaseCategory` WHERE CatID=RMD.PurchaseCategoryID ) as PurchaseType,
// (SELECT VehicalNumber FROM `RmVehicalDetails` WHERE ID=RMD.VehicalID ) as TruckNumber,
// RBD.Remarks as BillRemarks, RFD.BankName as SupBankName, RFD.BankAccNo as SupBankAccNo, RFD.IFSC as SupIFSC,
// RFD.PanNumber AS SupPANNumber
// FROM `RmArrivalDetatils` as RMD
// LEFT JOIN RmLotDetatils RLD on RLD.RmReferenceID=RMD.RmArrivalID
// LEFT JOIN RmBillDetails RBD on RMD.RmArrivalID = RBD.ArrivalID
// LEFT JOIN QcRmOrgnReport QCOR on RLD.RmLotID = QCOR.LotID 
// LEFT JOIN RmSuppliers RFD on RFD.ID = RBD.FinancierID 
// LEFT JOIN RmUsersList RUL on RUL.ID = RMD.AgentID 
// WHERE RLD.RmLotID = '$LotID'";


	// (SELECT AbStatus FROM `QcRmAbReport` WHERE LotID=RMD.RmArrivalID ) as ABStatus,

	// ECHO $BillQuery ; exit;
	$BillExec = $iconn->query($BillQuery);

	$BillPanNumber = $center = $PurchaseDate = $headlessCount = $headlessQty = "";
	if (!empty($BillExec)) {

		$BillRow = $BillExec->fetch_assoc();

		//var_dump($BillRow); exit;

		$ABStatusID = $BillRow['ABStatus'];
		$RmLotID = $BillRow['RmLotID'];

		$SqlABReport = "SELECT * FROM `QcABOptions` WHERE OptID='$ABStatusID'";
		$StmtABReport = $iconn->query($SqlABReport);
		$RowABReport = $StmtABReport->fetch_assoc();


		$SqlArrivalBreakUp = "SELECT * FROM `RmArrivalBreakUpDetails` WHERE ArrivalID='$ArrivalID' and Variety='Head On'";
		//exit;
		$StmtArrivalBreakUp = $iconn->query($SqlArrivalBreakUp);
		$RowArrivalBreakUp = $StmtArrivalBreakUp->fetch_assoc();

		$PurchaseCount = $RowArrivalBreakUp['Count'];
		$PurchaseWeight = $RowArrivalBreakUp['Weight'];

		$ABStatus = $RowABReport['Name'];
		$center = $BillRow['PurchaseCenter'];
		$PurchaseDate = $BillRow['BillDate'];
		$BillDate = date("d-m-Y", strtotime($PurchaseDate));
		$BillPanNumber = $BillRow['SupPANNumber']; //=1234567890;
		$Tdsperc = $BillRow['Tds'];
		$FarmAddress = $BillRow['FarmAddress'];
		$C_COUNT = $BillRow['C_COUNT'];
		$PurchaseCount = $BillRow['PurchaseCount'];
		$PurchaseWeight = $BillRow['PurchaseWeight'];
		$TruckNumber = $BillRow['TruckNumber'];
		$DriverName = $BillRow['DriverName'];
		$C_QTY = $BillRow['WEIGHT'];
		$RATE = $BillRow['RATE'];
		$LotNumber = $BillRow['ArrivalNumber'];
		$TUBS = $BillRow['TUBS'];
		$FinancierName = $BillRow['FinancierName'];
		$AgentName = $BillRow['UserName'];
		$VARITY = $BillRow['VARITY'];
		//	$AccDedKg= $BillRow['Deductions'];
		$LabActDedKg = $BillRow['ActualDeductions'];
		$LabSugDedKg = $BillRow['SuggestedDeductions'];
		$PurchaseType = $BillRow['PurchaseType'];
		$GraderName = $BillRow['GraderName'];
		$HarvestCommission = $BillRow['HarvestCommission'];
		$ACCOUNT_REMARKS = $BillRow['BillRemarks'];
		$FinancierBankName = $BillRow['SupBankName'];
		$FinancierBankAccNo = $BillRow['SupBankAccNo'];
		$FinancierIFSC = $BillRow['SupIFSC'];
	}



	$SqlArrivalDtls = "SELECT Remarks,Deductions,FinancialDate,CreditDays,CreditRemarks,InterestRate FROM `RmBillDetails` WHERE ArrivalID='$ArrivalID'";
	$StmtArrivalDtls = $iconn->query($SqlArrivalDtls);
	$RowArrivalDtls = $StmtArrivalDtls->fetch_assoc();

	$LotRemarks = $RowArrivalDtls['Remarks'];
	$AccDedKg = $RowArrivalDtls['Deductions'];
	$CreditDays = $RowArrivalDtls['CreditDays'];
	$CreditRemarks = $RowArrivalDtls['CreditRemarks'];
	$InterestRate = $RowArrivalDtls['InterestRate'];
	if ($RowArrivalDtls['FinancialDate']) {
		$DueDate = date('d-m-Y', strtotime($RowArrivalDtls['FinancialDate']));
	} else {
		$DueDate = '';
	}


	$SqlBillBrkUpQuery = "SELECT * from RmBillBreakup WHERE  ArrivalID = '$ArrivalID'";
	$SqlBillBrkUpExec = $iconn->query($SqlBillBrkUpQuery);

	while ($SqlBillRow = $SqlBillBrkUpExec->fetch_assoc()) {

		$BillWeight += $SqlBillRow['Weight'];
		$BillCount = $SqlBillRow['Count'];
	}


	$content = '';

	$content .= '<h5  style="text-align:right"> AB REPORT: ' . $ABStatus . '</h5>';
	$content .= '<h5  style="text-align:center"></h5>';


	$size1 = 100 / 6;
	$size2 = 100 / 6;
	$size = 100 - $size1 - $size2;
	$size3 = 60;




	$content .= '<table border="1" cellspacing="0" cellpadding="3">  
				<tr>  
           		<th  align="LEFT"  width="' . ($size) . '%"><font size="10">Bill Name: <b>' . $FinancierName . '</font></b></th>
				<th  align="LEFT"  width="' . ($size2) . '%"><font size="10">Date :  <br>Due Date : </font></th>
                <th  align="LEFT" width="' . ($size1) . '%"><font size="10"><b>' . $BillDate . '<br>' . $DueDate . '</b></font></th>
                </tr>
				
				<tr>
				<th  align="LEFT"  width="' . ($size2) . '%"><font size="10">Farmer Name:</font></th>
                <th  align="LEFT" width="' . ($size2) . '%"><font size="10"></font></th>
				<th  align="LEFT"  width="' . ($size2) . '%"><font size="10">Cell Number:</font></th>
                <th  align="LEFT" width="' . ($size2) . '%"><font size="10"></font></th>
				<th  align="LEFT"  width="' . ($size2) . '%"><font size="10">Village: </font></th>
                <th  align="LEFT" width="' . ($size2) . '%"><font size="10"><B>' . $FarmAddress . '</B></font></th>
				  
           		

				</tr>
				
				<tr>

                <th  align="LEFT"  width="' . ($size2) . '%"><font size="10">Agent Name:</font></th>
                <th  align="LEFT" width="' . ($size2) . '%"><font size="10"><b>' . $AgentName . '</font></b></th>
				<th  align="LEFT"  width="' . ($size2) . '%"><font size="10">Cell Number:</font></th>
                <th  align="LEFT" width="' . ($size2) . '%"><font size="10"></font></th>
				<th  align="LEFT"  width="' . ($size2) . '%"><font size="10">Lot No:</font></th>
                <th  align="LEFT" width="' . ($size2) . '%"><font size="10"><b>' . $LotNumber . '</font></b></th>				
           		
               </tr>
				
				<tr>  
           		<th  align="LEFT"  width="50%"><font size="10">Company Count/Quantity : <b>' . $BillCount . 'c / ' . $BillWeight . '</b> Variety :<b>' . $VARITY . ' </b></font></th>
           		<th  align="LEFT"  width="50%"><font size="10"> Farmer Count/Quantity : <b>' . $PurchaseCount . 'c / ' . $PurchaseWeight . '</b> Variety :<b>' . $VARITY . ' </b></font></th>
               </tr>
				
				
				<tr>  
           		<th  align="LEFT"  width="20%"><font size="9"> Details </font></th>
                <th  align="LEFT" width="15%"><font size="9">Total Pieces 5Kg </font></th>
                <th  align="LEFT" width="15%"><font size="9"> 1Kg C </font></th>
                <th  align="LEFT" width="15%"><font size="9"> Total Pies 5Kg</font></th>
                <th  align="LEFT" width="15%"><font size="9"> 1Kg C </font></th>
                <th  align="LEFT" width="20%"><font size="9"> Average </font></th>
                
				</tr>
				
				<tr>  
           		<th  align="LEFT"  width="20%"><font size="9"> 5 Kg Count </font></th>
                <th  align="LEFT" width="15%"><font size="9"> ' . (5 * $headlessCount) . '</font></th>
                <th  align="LEFT" width="15%"><font size="9"><b>' . $headlessCount . '</b> </font></th>
                <th  align="LEFT" width="15%"><font size="9"> </font></th>
                <th  align="LEFT" width="15%"><font size="9"> </font></th>
                <th  align="LEFT" width="20%"><font size="9"> </font></th>
                
				</tr>
				
				<tr>  
           		<th  align="LEFT"  width="20%"><font size="9"> Peeling </font></th>
                <th  align="LEFT" width="15%"><font size="9"> </font></th>
                <th  align="LEFT" width="15%"><font size="9">  </font></th>
                <th  align="LEFT" width="15%"><font size="9"> </font></th>
                <th  align="LEFT" width="15%"><font size="9"> </font></th>
                <th  align="LEFT" width="20%"><font size="9"> </font></th>
                
				</tr>
				
				
				<tr>  
           		<th  align="LEFT"  width="20%"><font size="9"> Discoloured </font></th>
                <th  align="LEFT" width="15%"><font size="9"> </font></th>
                <th  align="LEFT" width="15%"><font size="9">  </font></th>
                <th  align="LEFT" width="15%"><font size="9"> </font></th>
                <th  align="LEFT" width="15%"><font size="9"> </font></th>
                <th  align="LEFT" width="20%"><font size="9"> </font></th>
                
				</tr>
				
				<tr>  
           		<th  align="LEFT"  width="20%"><font size="9"> Black spot </font></th>
                <th  align="LEFT" width="15%"><font size="9"> </font></th>
                <th  align="LEFT" width="15%"><font size="9">  </font></th>
                <th  align="LEFT" width="15%"><font size="9"> </font></th>
                <th  align="LEFT" width="15%"><font size="9"> </font></th>
                <th  align="LEFT" width="20%"><font size="9"> </font></th>
                
				</tr>
				
				<tr>  
           		<th  align="center"  width="50%"><font size="10"><b>Quantity Details</b></font></th>
           		<th  align="center"  width="50%"><font size="10"><b> Transport Details </b></font></th>
               </tr>
				
				<tr>  
           		<th  align="center"  width="50%"><font size="10">Tubs Details</font></th>
           		<th  align="left"  width="50%"><font size="10"> Truck No : <b>' . $TruckNumber . '</B> </font></th>
               </tr>
				
				<tr>  
           		<th  align="center"  width="14%"><font size="10"> Material Tubs </font></th>
           		<th  align="center"  width="10%"><font size="10"> Empty </font></th>
           		<th  align="center"  width="12%"><font size="10"> Total Tubs </font></th>
           		<th  align="center"  width="14%"><font size="10">Quantity </font></th>
           		<th  align="left"  width="50%"><font size="10"> Driver :<b>' . $DriverName . '</B> </font></th>
               </tr>
				
					<tr>  
           		<th  align="center"  width="14%"><font size="10"> ' . $TUBS . ' </font></th>
           		<th  align="center"  width="10%"><font size="10">Hon Qty  </font></th>
           		<th  align="center"  width="12%"><font size="10"> ' . $BillCount . ' C</font></th>
           		<th  align="center"  width="14%"><font size="10"> <B>' . $BillWeight . ' Kgs</B></font></th>
           		<th  align="left"  width="50%"><font size="10"> Dispatched : <b>' . $center . '</B> </font></th>
               </tr>';








	$content .= '<tr>  
           		<th  align="center"  width="24%"><font size="10"> <br> <br>  Driver Sign <br> </font></th>
           		<th  align="center"  width="26%"><font size="10"> <br> GRADER:<B>' . $GraderName . '<BR><br>' . $PurchaseType . ' </B><br> </font></th>
           		<th  align="center"  width="25%"><font size="10"> <br> <br>  Supervisor Sign <br> </font></th>
           		<th  align="center"  width="25%"><font size="10">  <br> <br>  Agent/Farm Sign <br> </font></th>
           		
               </tr>
				
				
				
				<tr>  
           		<th  align="center"  width="100%"><font size="9"><b>For Office use only   
				BillNo.   Certificate /      NoCertificate </b></font></th>    
				</tr>
				
				 <tr>  
           		<th  align="center"  width="20%"><font size="10"> Variety/Quality </font></th>
           		<th  align="center"  width="15%"><font size="10"> Count  </font></th>
           		<th  align="center"  width="15%"><font size="10"> Quantity </font></th>
           		<th  align="center"  width="15%"><font size="10"> Rate/Kg </font></th>
           		<th  align="center"  width="15%"><font size="10"> Deduction/Kg </font></th>
           		<th  align="center"  width="20%"><font size="10"> Amount :  </font></th>
           		
               </tr>';


	$SqlSpecies = "SELECT * FROM `RmArrivalDetatils` as RAD left JOIN RmLotDetatils as RLD on RAD.RmArrivalID=RLD.RmReferenceID 
			   WHERE RLD.RmLotID ='$LotID'";
	$StmtSpecies = $iconn->query($SqlSpecies);
	$RowSpecies = $StmtSpecies->fetch_array();
	$Species = $RowSpecies['Species'];

	$SqlHonGrading = "SELECT * FROM RmLotDetatils as RMD 
						left JOIN PpcHonGradingBreakup as HOG on RMD.RmLotID=HOG.LotID 
						left JOIN RmPurchaseCategory as RPC  on RPC.CatID=RMD.PurchaseCategoryID
                        left JOIN RmPurchaseStations as RPS  on RPS.PurchaseStationID=RMD.PurchaseStationID						
						WHERE LotID='$LotID'";
	$StmtHonGrading = $iconn->query($SqlHonGrading);

	if ($StmtHonGrading->num_rows > 0) {

		$RowHonGrading = $StmtHonGrading->fetch_array();
		$HonWeight = $RowHonGrading['HonWeight'];
		$HonCount = $RowHonGrading['HonCount'];

		$content .= '<tr>  
		<th  align="left"  width="20%"><font size="10"><B>' . $Species . '-Hon Packed</B></font></th>
		<th  align="right"  width="15%"><font size="10"> ' . $HonCount . '  C </font></th>
		<th  align="right"  width="15%"><font size="10"> ' . $HonWeight . '  Kgs  </font></th>
		<th  align="right"  width="15%"><font size="10"> </font></th>
		<th  align="right"  width="15%"><font size="10"> <B></b></font></th>
		<th  align="right"  width="20%"><font size="10"><B> </b></font></th>
		
	</tr>';
	}

	$BillBrkUpQuery = "SELECT * FROM `RmBillBreakup`  WHERE ArrivalID='$ArrivalID'";

	$BillBrkUpExec = $iconn->query($BillBrkUpQuery);
	$BillWeight = 0;
	while ($BillRow = $BillBrkUpExec->fetch_assoc()) {

		if ($BillRow['DefectName'] != '') {
			$Description = $BillRow['DefectName'];
		} else {
			$Description = $BillRow['Description'];
		}

		$Rate = $BillRow['Rate']; //+$HarvestCommission-$AccDedKg;
		$BillWeight += $BillRow['Weight'];
		$BillPrice += round($BillRow['Weight'] * $Rate);

		$content .= '<tr>  
           		<th  align="left"  width="20%"><font size="10"><B>' . $Species . '-' . $Description . '</B></font></th>
           		<th  align="right"  width="15%"><font size="10"> ' . $BillRow['Count'] . '  C </font></th>
           		<th  align="right"  width="15%"><font size="10"> ' . $BillRow['Weight'] . '  Kgs  </font></th>
           		<th  align="right"  width="15%"><font size="10"> ' . $Rate . ' / Kgs  </font></th>
           		<th  align="right"  width="15%"><font size="10"> <B></b></font></th>
           		<th  align="right"  width="20%"><font size="10"><B>' . round($BillRow['Weight'] * $Rate) . ' /-  </b></font></th>
           		
               </tr>';
	}
	;




	if ($LabSugDedKg != 0) {
		$LabRemarks = 'A DEDUCTION OF RS.' . round($LabSugDedKg) . '/- IS RECOMMENDED FOR QUALITY LOSS';
	} else {
		$LabRemarks = 'NILL';
	}

	if ($AccDedKg > 0) {
		$Sing = '+';
	} else {
		$Sing = '';
	}


	$LabActDed = round($LabActDedKg * $BillWeight);
	$AccDed = round($AccDedKg * $BillWeight);
	// $TotalInterest = round($InterestRate * $BillWeight);

	$BillTotal = $BillPrice + $AccDed - $LabActDed; //+ $TotalInterest

	// echo $BillTotal;
	// exit;

	/*
	   1. Credit Days Text 
	   
		2. Bill Amount 18% / 360 x No of Credit Days 
	   
		3. Bill Amount + Total Credit Days Bill - Tds
	   
		*/
	$interestPerLakh = 100000 * ($InterestRate / 100);
	$interestPerLakh = round($interestPerLakh / 360);
	$TotalInterest = ($BillTotal / 100000) * $interestPerLakh;
	$TotalInterest = $TotalInterest * $CreditDays;
	$TotalInterest = round($TotalInterest);

	$BillTotal = $BillTotal + $TotalInterest;


	$content .= '
				
				 <tr>  
           		<th  align="left"  width="20%"><font size="10"><b>Sub Total</b></font></th>
           		<th  align="center"  width="15%"><font size="10"> </font></th>
           		<th  align="center"  width="15%"><font size="10"><b>' . round($BillWeight, 2) . ' Kgs</b></font></th>
           		<th  align="center"  width="15%"><font size="10"></font></th>
           		<th  align="center"  width="15%"><font size="10"></font></th>
           		<th  align="right"  width="20%"><font size="10"><B>' . $BillPrice . ' /- </b> </font></th>
           		
               </tr> 
			   <tr>  
           		<th  align="left"  width="20%"><font size="10"><b>Lab </b></font></th>
           		
				
           		<th  align="center"  width="45%"><font size="10">' . $LabRemarks . '</font></th>
           		
           		<th  align="center"  width="15%"><font size="10">' . $LabActDedKg . '</font></th>
           		<th  align="right"  width="20%"><font size="10"><B> - ' . $LabActDed . ' /- </b> </font></th>
           		
               </tr>

			  
			   <tr>  
           		<th  align="left"  width="20%"><font size="10"><b>Accounts </b></font></th>	
           		<th  align="center"  width="45%"><font size="10">' . $LotRemarks . '</font></th>
           		<th  align="center"  width="15%"><font size="10">' . $AccDedKg . '</font></th>
           		<th  align="right"  width="20%"><font size="10"><B>' . $AccDed . ' /- </b> </font></th>
           		
               </tr>
			   ' . (($IsHideCreditInfoAtRmBill) ?
		'<tr><th  align="left"  width="80%"><font size="10"></font></th>  	
           		<th  align="right"  width="20%"><font size="10"><B>' . $TotalInterest . ' /- </b> </font></th>
               </tr>'
		:
		'<tr><th  align="left"  width="20%"><font size="10"><b>Credit </b></font></th>  	
           		<th  align="center"  width="45%"><font size="10">' . $CreditRemarks . '</font></th>
           		<th  align="center"  width="15%"><font size="10">' . $InterestRate . ' % </font></th>
           		<th  align="right"  width="20%"><font size="10"><B>' . $TotalInterest . ' /- </b> </font></th>
               </tr>') . '';

	if ($PurchaseDate > '2021-06-30') {

		/*
	  1. Credit Days Text 

	   2. Bill Amount 18% / 360 x No of Credit Days 

	   3. Bill Amount + Total Credit Days Bill - Tds

	   */

		/*	$BillTotal = $BillPrice + $AccDed  - $LabActDed; //+ $TotalInterest




		$TotalInterest=$BillTotal*($InterestRate/100);
		$TotalInterest=($TotalInterest/360)*$CreditDays;
		$TotalInterest=round($TotalInterest,2);

		$BillTotal =$BillTotal+$TotalInterest;
		*/


		// $BillTotal = $BillPrice + $AccDed - $LabActDed;

		$TdsAmount = ($BillTotal * $Tdsperc) / 100;

		// comment after bill creation updated
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

		$totalBill = $BillTotal + $TotalInterest;

		$content .= '<tr>  
				<th  align="center"  width="80%"><font size="10"><B> Bill Total</B></font></th>
				<th  align="right"  width="20%"><font size="10">Rs. <B>' . round($BillTotal) . ' /- </b> </font></th>
				</tr>';

		//echo $TdsAmount; exit; 
		$content .= '<tr>  
           		<th  align="left"  width="20%"><font size="10"><b>TDS </b></font></th>	
           		<th  align="center"  width="45%"><font size="10">PAN Number : ' . $BillPanNumber . '</font></th>
           		<th  align="center"  width="15%"><font size="10">' . $TDSperc . '</font></th>
           		
           		<th  align="right"  width="20%"><font size="10"><B>' . round($TdsAmount) . ' /- </b> </font></th>
           		
               </tr>';
		// $BillTotal = $BillTotal + $TotalInterest - $TdsAmount; // 26-04-2024 durgaPrasad $TotalInterest
		$BillTotal = $BillTotal - $TdsAmount;
	} else {
		//$BillTotal=$PURCHASE_AMOUNT + $DefectsAmount +$HonUsedAmount + $AccDeductions -$LabDeduction;
		$BillTotal = $BillPrice + $AccDed + $TotalInterest - $LabActDed; // 26-04-2024 durgaPrasad $TotalInterest
		// $BillTotal = $BillPrice + $AccDed  - $LabActDed;
	}

	$content .= ' <tr>  
           		<th  align="center"  width="80%"><font size="10"><B> Net Payable</B></font></th>
           		
           		<th  align="right"  width="20%"><font size="10">Rs. <B>' . round($BillTotal) . ' /- </b> </font></th>
           		
               </tr>
				
				
			   
                <tr>  
           		<th  align="center"  width="20%"><font size="10"> <br> <br>  Pur.Mgr   <br></font></th>
                <th  align="center" width="20%"><font size="10"> <br> <br>  Pur.Authorised <br></font></th>
                <th  align="center" width="20%"><font size="10"> <br> <br>  Plant Mgr  <br></font></th>
                <th  align="center" width="20%"><font size="10"> <br> <br>  Account Mgr <br></font></th>
                <th  align="center" width="20%"><font size="10"> <br> <br>  Authorised  <br></font></th>
                
				</tr>
				
				<tr>  
           		<th  align="LEFT"  width="100%"><font size="10"><U><B>Bank Details :</B></U><br><B> Bank Name:</B>' . $FinancierBankName . '
				<br><B> Bank Account No:</B>' . $FinancierBankAccNo . '<br><B> IFSC:</B>' . $FinancierIFSC . '</font></th>
			
                
				</tr>
				
				</table>';

	return $content;
}
