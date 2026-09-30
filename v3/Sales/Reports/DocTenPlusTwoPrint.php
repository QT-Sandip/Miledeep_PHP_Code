<?php
//ini_set( 'display_errors', 1 );


$style = '<style>
th {
    font-size: 10px;
	padding : 8px;
}
td {
    font-size: 10px;
	padding : 8px;
}
p {
	//line-height: 0.01;
}
</style>
';


$style2 = '<style>
th {
    font-size: 10px;
	padding : 10px;
}
td {
    font-size: 8px;
	padding : 10px;
}
p {
	//line-height: 0.01;
}
</style>
';

session_start();

//include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$PoID = $_GET['id'];
$Type = $_GET['Type'];
$DocType = 'TPT';

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($_SERVER['DOCUMENT_ROOT'] . "/Libraries/tcpdf/tcpdf.php");
include($cp . "Includes/IncludePage.php");

// $SqlScrmPoData = "SELECT * FROM ScrmPIData as SPD 
$SqlScrmPoData = "SELECT * FROM ScrmPoData as SPD 
 left JOIN ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID
 left JOIN ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
 left JOIN ScrmPortCities as SPC on SPD.PortofLoading=SPC.PortId
 WHERE PoID='$PoID'";

// left join ScrmNotifyparty as SNP on SNP.NotifyPartyID=SPD.NotifyPartyID

//echo $SqlScrmPoData;
$StmtPoData = $iconn->query($SqlScrmPoData);
$PoDataRow = $StmtPoData->fetch_assoc();
$ExpID = $PoDataRow['ExporterID'];
$PortOfDischarge = $PoDataRow['PortOfDischarge'];
$NotifyPartyID = $PoDataRow['NotifyPartyID'];
$DestinationCountry = $PoDataRow['DestinationCountry'];

$SqlCustom = "SELECT CustomsInvNo,CInvDate FROM `ScrmCustomInvoices`  as SCV WHERE PiID='$PoID'";
$StmtCustom = $iconn->query($SqlCustom);
$RowCustom = $StmtCustom->fetch_assoc();

$CustomsInvNo = $RowCustom['CustomsInvNo'];
$CustInvDate = date("d-m-Y", strtotime($RowCustom['CInvDate']));

// if ($NotifyPartyID == 0 ) {
if ($DestinationCountry != 157 || $NotifyPartyID == 0) {

	// echo "Notify Party is only display for USA country ,so no 10+2 Report";
	echo "Notify Party is only displayed for shipments to the USA; so no 10+2 Report";
	exit;
}


class MYPDF extends TCPDF
{

	public function Header()
	{

		global $Status;
	}

	public function Footer()
	{
		global $CustomsInvNo, $CustInvDate;
		$this->SetFont('helvetica', 'I', 8);
		$this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages() . '(' . $CustomsInvNo . ' / Dt.' . $CustInvDate . ')', 0, false, 'C', 0, '', 0, false, 'T', 'M');
		//$this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		$this->SetY(-5);
	}
}



$pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);



$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('10+2 Report : ' . $Header);
$pdf->SetHeaderData('', '', '', '');
$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont('helvetica');
$pdf->SetFooterMargin(10);
$pdf->SetHeaderMargin(2);
$pdf->SetMargins(10, 5, 10, 2);
$pdf->setPrintHeader(true);
$pdf->setPrintFooter(true);

$pdf->SetAutoPageBreak(TRUE, 7);
$pdf->SetFont('helvetica', '', 11);
$pdf->AddPage();

// if ($NotifyPartyID == 1 || $NotifyPartyID == 5) {
if ($NotifyPartyID == 1 || $NotifyPartyID == 5 || $NotifyPartyID == 6) {
	$table_data = getCha3($iconn, $PoID);
	$pdf->writeHTML($table_data);
	$pdf->AddPage();
	$table_data = getCha31($iconn, $PoID);
	$pdf->writeHTML($table_data);
	$pdf->AddPage();
	$table_data = getCha32($iconn, $PoID);
	$pdf->writeHTML($table_data);
} else if ($NotifyPartyID == 2) {
	$table_data = getCha1($iconn, $PoID);
	$pdf->writeHTML($table_data);
} else if ($NotifyPartyID == 3) {
	$table_data = getCha2($iconn, $PoID);
	$pdf->writeHTML($table_data);
	$pdf->AddPage();
	$table_data = getCha21($iconn, $PoID);
	$pdf->writeHTML($table_data);
}

$pdf->SetFont('helvetica', '', 10);

$ArrayFilename = getfilename($iconn, $ExpID, $PoID);

/*$InvShortCode=$ArrayFilename['InvShortCode'];
$CInvNo=$ArrayFilename['CInvNo'];
$FinancialYear=$ArrayFilename['FinancialYear'];*/

$CustomsInvNo = $ArrayFilename['CustomsInvNo'];
$file_name = str_replace('/', '_', $CustomsInvNo);
$Fname = '-TPT';
$file_name = $file_name . $Fname . '.pdf';

//$file_name=$InvShortCode.'-SD-'.$CInvNo.'-'.$FinancialYear.'.pdf';

if ($Type == '') {
	$Type = 'I';
} else {
	$Fname = $MailTempFolder; //From Config 
	$file_name = $_GET['FileName'];
	$file_name = str_replace('/', '', $file_name);
	;
	$file_name = $Fname . $file_name;
}
ob_end_clean();

$pdf->Output($file_name, $Type);
;


function getCha1($iconn, $PoID)
{

	include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
	include($_SERVER['DOCUMENT_ROOT'] . "/includes/func.php");
	include "../SalesInculdes/SalesConstants.php";

	global $PoDataRow, $style2, $DocType;


	$SqlScrmPoData = "SELECT * FROM ScrmPIData as SPD 
 left JOIN ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID
 left JOIN ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
 left JOIN ScrmPortCities as SPC on SPD.PortOfDischarge=SPC.PortId
 left JOIN AdminExporterDetails as AED on SPD.ProcessorID=AED.ExpID
 left JOIN ScrmBuyerDetails as SBD on SPD.BuyerID=SBD.BuyerID
 WHERE PoID='$PoID'";
	//echo $SqlScrmPoData;
	//EXIT;

	$StmtPoData = $iconn->query($SqlScrmPoData);
	$PoDataRow = $StmtPoData->fetch_assoc();
	$ExpID = $PoDataRow['ExporterID'];
	$ProcessorID = $PoDataRow['ProcessorID'];
	$CountryID = $PoDataRow['DestinationCountry'];




	$CustomInvoiceSql = "SELECT * FROM ScrmCustomInvoices as SCI
  left join ScrmISFDetails as SIDS on SIDS.PIID=SCI.PiID
  WHERE SCI.PiID='$PoID'";
	//echo $CustomInvoiceSql;
	//exit;
	$CustomInvoiceExec = $iconn->query($CustomInvoiceSql);
	$CustomInvoiceRow = $CustomInvoiceExec->fetch_assoc();




	$ExpID = $PoDataRow['ExporterID'];
	$ImporterID = $PoDataRow['ImporterID'];
	$PoNo = $PoDataRow['PoNo'];
	$Buyer = $PoDataRow['ConsigneeName'];
	$InvDate = $PoDataRow['InvDate'];
	$LoadingPort = $PoDataRow['Name'];
	$ConsigneeName = $PoDataRow['ConsigneeName'];
	$ConsigneeAddress = $PoDataRow['ConsigneeAddress'];
	$PoNo = $PoDataRow['PoNo'];
	$ReferenceNo = $PoDataRow['ReferenceNo'];
	$PoDate = getFormatedDateNA($PoDataRow['PoDate']);
	$CountryID = $PoDataRow['DestinationCountry'];
	$ConsigneeIRSNumber = $PoDataRow['ConsigneeIRSNumber'];
	/*$ProcsrName=$PoDataRow['ExpName'];
   $ProcsrAddress=$PoDataRow['ExpAddress'];*/

	$DocType = 'TPT';

	$ProcessorData = getExporterDetails($ProcessorID, $CountryID, $DocType);
	$ProcsrName = $ProcessorData['ExpName'];
	$ProcsrAddress = $ProcessorData['ExpAddress'];

	$BuyerName = $PoDataRow['BuyerName'];
	$BuyerAddress = $PoDataRow['BuyerAddress'];

	if ($BuyerName == '') {
		$BuyerName = $ConsigneeName;
		$BuyerAddress = $ConsigneeAddress;
	}
	$SqlPoGradeData = "SELECT SUM(Mcs) as MCS,Brand FROM `ScrmPoGradeData`  WHERE InvId='$PoID'";
	$StmtPoGradeData = $iconn->query($SqlPoGradeData);
	$RowPoGradeData = $StmtPoGradeData->fetch_assoc();


	$SqlImporterData = "SELECT * FROM `AdminExporterDetails`  WHERE ExpID='$ImporterID'";
	$StmtImporterData = $iconn->query($SqlImporterData);
	$RowImporterData = $StmtImporterData->fetch_assoc();

	$ImporterName = $RowImporterData['ExpName'];
	$ImportAddress = $RowImporterData['ExpAddress'];
	$ImportPhnNo = $RowImporterData['ExpPhoneNumber'];

	$MCS = $RowPoGradeData['MCS'];
	$Brand = $RowPoGradeData['Brand'];

	$PaymentData = getPaymentValues($iconn, $PoID);

	$ExporterData = getExporterDetails($ExpID);
	$PackSealNo = $CustomInvoiceRow['PackSealNo'];
	$SealNo = $CustomInvoiceRow['SealNo'];
	$ContainerNumber = $CustomInvoiceRow['ContainerNo'];
	$CInvCode = $CustomInvoiceRow['CInvCode'];
	$VehicalNumber = $CustomInvoiceRow['VehicalNumber'];
	$SBNo = $CustomInvoiceRow['SBNo'];
	$BLNo = $CustomInvoiceRow['BLNo'];
	$PlaceOfRecipt = $CustomInvoiceRow['PlaceOfRecipt'];
	$FinalDestination = $CustomInvoiceRow['FinalDestination'];
	$SDD = $CustomInvoiceRow['SDD'];
	$BLDate = getFormatedDateNA($CustomInvoiceRow['BLDate']);
	$ETA_POD = getFormatedDateNA($CustomInvoiceRow['ETA_POD']);
	$Discount = $CustomInvoiceRow['Discount'];
	$IGST = $CustomInvoiceRow['IGST'];
	$VoyOrg = $CustomInvoiceRow['VoyOrg'];
	$DeliveryTerms = $PoDataRow['DeliveryTerms'];
	//ECHO $DeliveryTerms; EXIT;
	$ByRoad = $CustomInvoiceRow['ByRoad'];
	$BLNo = $CustomInvoiceRow['BLNo'];



	$ETD = getFormatedDateNA($CustomInvoiceRow['ETD']);
	$ETA = getFormatedDateNA($CustomInvoiceRow['ETA']);

	$ExpName = $ExporterData['ExpName'];
	$ExpAddress = $ExporterData['ExpAddress'];
	$ExpPhnNo = $ExporterData['ExpPhoneNumber'];

	$InvNO = $ExporterData['InvShortCode'] . $CustomInvoiceRow['CInvNo'] . '/' . $CurrentYear . '-' . $NxtYear;

	$SqlPOgradeData = "SELECT * FROM `ScrmCIGradeData` where InvId='$PoID'  ";

	$StmtPOgradeData = $iconn->query($SqlPOgradeData);

	while ($RowPOgradeData = $StmtPOgradeData->fetch_assoc()) {

		$MCS = $RowPOgradeData['Mcs'];
		$TotalMcs += $MCS;
	}



	$SlHSCode = "SELECT SPGD.VarietyID,SPL.VarietyID,SPL.PreparationType, 
SPM.HSCode FROM `ScrmPoGradeData` SPGD LEFT JOIN ScrmPIVarietyList SPL ON
 SPL.VarietyID=SPGD.VarietyID LEFT JOIN ScrmPreparationMaster SPM ON
 SPL.PreparationType=SPM.ShortCode WHERE SPGD.InvId='$PoID'";
	$ExecHSCode = $iconn->query($SlHSCode);
	while ($RowHSCode = $ExecHSCode->fetch_assoc()) {

		$ArrayHSCode[] = $RowHSCode['HSCode'];
	}

	$SqlVessalName = "SELECT VesselName,VOYNO,FromTOPID,ToTOPID,TranshipDt,
SPC.Name AS LoadingPort,
SPDst.Name AS DischargePort
 FROM `ScrmTranshipmentPortsList` STPL
LEFT JOIN ScrmPIData SPD ON SPD.PoID=STPL.PIID
LEFT JOIN ScrmPortCities SPC ON SPC.PortId=SPD.PortofLoading
LEFT JOIN ScrmPortCities SPDst ON SPDst.PortId=SPD.PortOfDischarge
WHERE PIID='$PoID'";


	$StmtVessalName = $iconn->query($SqlVessalName);
	while ($RowVessalName = $StmtVessalName->fetch_assoc()) {

		if ($RowVessalName['FromTOPID'] == $RowVessalName['LoadingPort']) {

			$VessalName = $RowVessalName['VesselName'];
			$VOYNO = $RowVessalName['VOYNO'];
		}
		if ($RowVessalName['ToTOPID'] == $RowVessalName['DischargePort']) {

			$ETADischarge = getFormatedDateNA($RowVessalName['TranshipDt']);
		}
	}

	if ($DeliveryTerms == 'CY/CY') {

		$ShipToName = $ConsigneeName;
		$ShipToAddress = $ConsigneeAddress;
	} else {

		$SqlStoreDeliveryDetails = "SELECT StoreID,StoreName,StoreAddress1 FROM `ScrmStoreDeliveryDetails`
 WHERE StoreID='$SDD' ";
		$StmtStoreDeliveryDetails = $iconn->query($SqlStoreDeliveryDetails);
		$RowStoreDeliveryDetails = $StmtStoreDeliveryDetails->fetch_assoc();

		$ShipToName = $RowStoreDeliveryDetails['StoreName'];
		$ShipToAddress = $RowStoreDeliveryDetails['StoreAddress1'];
	}



	$HSCodes = implode(",", array_unique($ArrayHSCode));

	$content = '';

	$size = 45;
	$size1 = 55;

	$S_NO = 1;
	$width = 55;
	$width_s = 45;


	$content .= $style2;
	$content .= '
			  
			  <table border="1" cellspacing="0" cellpadding="2.25" align="center" width="100%">


               <tr>  	   
				 <th  align="center" width="100%" ><br><h1><B>John A. Steer Co.</B></h1></th>    
				</tr>
				
			  <tr> 	   
				 <th  align="LEFT" width="' . $width . '%" ></th>    
				 <th  align="LEFT" width="' . $width_s . '%">IMPORTER "ISF" WORKSHEET</th>
			  </tr>
			  
			  <tr> 			   
				<th  align="RIGHT" width="' . $width . '%" >VESSEL :</th>    
				<th  align="LEFT" width="' . $width_s . '%">' . $VessalName . '  ' . $VOYNO . '</th>
			  </tr>  
				
			<tr> 	   
				<th  align="RIGHT" width="' . $width . '%" >CUT-OFF:</th>    
				<th  align="LEFT" width="' . $width_s . '%">--</th>
			</tr> 

            <tr> 	   
				<th  align="RIGHT" width="' . $width . '%" >Estimated Loading Date</th>    
				<th  align="LEFT" width="' . $width_s . '%">' . $BLDate . '</th>
			</tr> 			
				
			   <tr> 
				
			   <th  align="RIGHT"  width="' . $width . '%">Estimated ETA to Discharge</th>  
				 <th  align="LEFT" width="' . $width_s . '%">' . $ETADischarge . '</th>
                </tr> 
				<tr> 
				
			   <th  align="RIGHT"  width="' . $width . '%">P.O. NUMBER</th>  
				 <th  align="LEFT" width="' . $width_s . '%">' . $PoNo . '</th>
                </tr> 
				<tr> 
				
			   <th  align="RIGHT"  width="' . $width . '%">Container Number</th>  
				 <th  align="LEFT" width="' . $width_s . '%">' . $ContainerNumber . '</th>
                </tr> 
				
				<tr> 
				
			   <th  align="LEFT"  width="100%">
			   BILL OF LADING NUMBER REQUIREMENT: PLEASE NOTE, US CUSTOMS REQUIRES THE AMS <BR>
			   HOUSE BILL OF LADING.IF AN AMS HOUSE BILL OF ADING DOES NOT EXIST FOR <BR>
			   THE SHIPMENT,THEN THE CARRIERS MASTER BILL OF LADING IS REQUIRED.</th>  
				
                </tr> 
				
				<tr> 							     
				 <th  align="LEFT" width="100%"></th>
                </tr>
				<tr> 							     
				 <th  align="LEFT" width="100%"></th>
                </tr>
				
				
				
				
				<tr> 				
			     <th  align="CENTER"  width="' . $width . '%">1.AMS BILL OF LADING IS REQUIRED</th>  
				 <th  align="LEFT" width="' . $width_s . '%"></th>
                </tr> 
				
				
				<tr> 				
			     <th  align="CENTER"  width="' . $width . '%">AMS HB/L (House/NVOCC Bill of Lading):</th>  
				 <th  align="LEFT" width="' . $width_s . '%"></th>
                </tr> 
				
				<tr> 				
			     <th  align="CENTER"  width="' . $width . '%">AMS MB/L (Master/Carrier Bill of Lading):</th>  
				 <th  align="LEFT" width="' . $width_s . '%">' . $BLNo . ',' . $BLDate . '</th>
                </tr> 
				
			   <tr> 				  
				 <th  align="CENTER" width="' . $width . '%" ></th>    
				 <th  align="LEFT" width="' . $width_s . '%"></th>
			   </tr>
			   
			   <tr> 				  
				 <th  align="CENTER" width="' . $width . '%" >2.IMPORTER OF RECORD:</th>    
				 <th  align="LEFT" width="' . $width_s . '%">' . $ImporterName . '</th>
			   </tr>
				
				<tr> 				  
				 <th  align="CENTER" width="' . $width . '%" >ADDRESS:</th>    
				 <th  align="LEFT" width="' . $width_s . '%">' . $ImportAddress . '</th>
			   </tr>
					
				<tr> 				  
				 <th  align="CENTER" width="' . $width . '%" >ADDRESS:</th>    
				 <th  align="LEFT" width="' . $width_s . '%"></th>
			   </tr>
                <tr> 				  
				 <th  align="CENTER" width="' . $width . '%" >CONTACT:</th>    
				 <th  align="LEFT" width="' . $width_s . '%">' . $ImportPhnNo . '</th>
			   </tr>
                <tr> 				  
				 <th  align="CENTER" width="' . $width . '%" >TAX/EIN#</th>    
				 <th  align="LEFT" width="' . $width_s . '%">044601-01124</th>
			   </tr>			   
				<tr> 				  
				 <th  align="CENTER" width="' . $width . '%" ></th>    
				 <th  align="LEFT" width="' . $width_s . '%"></th>
			   </tr>
			   
			    <tr> 				  
				 <th  align="CENTER" width="' . $width . '%" >3.SELLER:</th>    
				 <th  align="LEFT" width="' . $width_s . '%">' . $ExpName . '</th>
			   </tr>
				
				<tr> 				  
				 <th  align="CENTER" width="' . $width . '%" >ADDRESS:</th>    
				 <th  align="LEFT" width="' . $width_s . '%">' . $ExpAddress . '</th>
			   </tr>
					
				<tr> 				  
				 <th  align="CENTER" width="' . $width . '%" >ADDRESS:</th>    
				 <th  align="LEFT" width="' . $width_s . '%"></th>
			   </tr>
                <tr> 				  
				 <th  align="CENTER" width="' . $width . '%" >CONTACT:</th>    
				 <th  align="LEFT" width="' . $width_s . '%">' . $ExpPhnNo . '</th>
			   </tr>
                			   
				<tr> 				  
				 <th  align="CENTER" width="' . $width . '%" ></th>    
				 <th  align="LEFT" width="' . $width_s . '%"></th>
			   </tr>
			   
			       <tr> 				  
				 <th  align="CENTER" width="' . $width . '%" >4.BUYER:</th>    
				 <th  align="LEFT" width="' . $width_s . '%">' . $BuyerName . '</th>
			   </tr>
			   
			   
				
				<tr> 				  
				 <th  align="CENTER" width="' . $width . '%" >ADDRESS:</th>    
				 <th  align="LEFT" width="' . $width_s . '%">' . $BuyerAddress . '</th>
			   </tr>
					
				<tr> 				  
				 <th  align="CENTER" width="' . $width . '%" >ADDRESS:</th>    
				 <th  align="LEFT" width="' . $width_s . '%"></th>
			   </tr>
                <tr> 				  
				 <th  align="CENTER" width="' . $width . '%" >CONTACT:</th>    
				 <th  align="LEFT" width="' . $width_s . '%"></th>
			   </tr>
                			   
				<tr> 				  
				 <th  align="CENTER" width="' . $width . '%" ></th>    
				 <th  align="LEFT" width="' . $width_s . '%"></th>
			   </tr>
				    <tr> 				  
				 <th  align="CENTER" width="' . $width . '%" >5.CONSIGNEE:</th>    
				 <th  align="LEFT" width="' . $width_s . '%">' . $ConsigneeName . '</th>
			   </tr>
				
			

				
				<tr> 				  
				 <th  align="CENTER" width="' . $width . '%" >ADDRESS:</th>    
				 <th  align="LEFT" width="' . $width_s . '%">' . nl2br($ConsigneeAddress) . '</th>
			   </tr>
					
				<tr> 				  
				 <th  align="CENTER" width="' . $width . '%" >ADDRESS:</th>    
				 <th  align="LEFT" width="' . $width_s . '%"></th>
			   </tr>
                <tr> 				  
				 <th  align="CENTER" width="' . $width . '%" >CONTACT:</th>    
				 <th  align="LEFT" width="' . $width_s . '%"></th>
			   </tr>
			   
			    <tr> 				  
				 <th  align="CENTER" width="' . $width . '%" >TAX/EIN#</th>    
				 <th  align="LEFT" width="' . $width_s . '%">' . $ConsigneeIRSNumber . '</th>
			   </tr>
                			   
				<tr> 				  
				 <th  align="CENTER" width="' . $width . '%" ></th>    
				 <th  align="LEFT" width="' . $width_s . '%"></th>
			   </tr>
			   
			        <tr> 				  
				 <th  align="CENTER" width="' . $width . '%" >6.MANFACTURER/SUPPLIER</th>    
				 <th  align="LEFT" width="' . $width_s . '%">' . $ProcsrName . '</th>
			   </tr>
				
				<tr> 				  
				 <th  align="CENTER" width="' . $width . '%" >ADDRESS:</th>    
				 <th  align="LEFT" width="' . $width_s . '%">' . $ProcsrAddress . '</th>
			   </tr>
					
				<tr> 				  
				 <th  align="CENTER" width="' . $width . '%" >ADDRESS:</th>    
				 <th  align="LEFT" width="' . $width_s . '%"></th>
			   </tr>
                <tr> 				  
				 <th  align="CENTER" width="' . $width . '%" >CONTACT:</th>    
				 <th  align="LEFT" width="' . $width_s . '%"></th>
			   </tr>
                			   
				<tr> 				  
				 <th  align="CENTER" width="' . $width . '%" ></th>    
				 <th  align="LEFT" width="' . $width_s . '%"></th>
			   </tr>
			   
			     <tr> 				  
				 <th  align="CENTER" width="' . $width . '%" >7.SHIP TO PARTY:</th>    
				 <th  align="LEFT" width="' . $width_s . '%">' . $ShipToName . '</th>
			   </tr>
				
				<tr> 				  
				 <th  align="CENTER" width="' . $width . '%" >ADDRESS:</th>    
				 <th  align="LEFT" width="' . $width_s . '%">' . $ShipToAddress . '</th>
			   </tr>
					
				<tr> 				  
				 <th  align="CENTER" width="' . $width . '%" >ADDRESS:</th>    
				 <th  align="LEFT" width="' . $width_s . '%"></th>
			   </tr>
                <tr> 				  
				 <th  align="CENTER" width="' . $width . '%" >CONTACT:</th>    
				 <th  align="LEFT" width="' . $width_s . '%"></th>
			   </tr>
                			   
				<tr> 				  
				 <th  align="CENTER" width="' . $width . '%" ></th>    
				 <th  align="LEFT" width="' . $width_s . '%"></th>
			   </tr>
			   <tr> 				  
				 <th  align="CENTER" width="' . $width . '%" >COUNTRY OF ORIGIN</th>    
				 <th  align="LEFT" width="' . $width_s . '%">INDIA</th>
			   </tr>
			   <tr> 				  
				 <th  align="CENTER" width="' . $width . '%" ></th>    
				 <th  align="LEFT" width="' . $width_s . '%"></th>
			   </tr>
			   <tr> 				  
				 <th  align="CENTER" width="' . $width . '%" >COMMODITY HTS#:</th>    
				 <th  align="LEFT" width="' . $width_s . '%">' . $HSCodes . '</th>
			   </tr>
			    <tr> 				  
				 <th  align="CENTER" width="' . $width . '%" ></th>    
				 <th  align="LEFT" width="' . $width_s . '%"></th>
			   </tr>
			    <tr> 				  
				 <th  align="CENTER" width="' . $width . '%" >10.CONTAINER STUFFING LOCATION</th>    
				 <th  align="LEFT" width="' . $width_s . '%">' . $ProcsrName . '</th>
			   </tr>
				
				<tr> 				  
				 <th  align="CENTER" width="' . $width . '%" >ADDRESS:</th>    
				 <th  align="LEFT" width="' . $width_s . '%">' . $ProcsrAddress . '</th>
			   </tr>
					
				<tr> 				  
				 <th  align="CENTER" width="' . $width . '%" >ADDRESS:</th>    
				 <th  align="LEFT" width="' . $width_s . '%"></th>
			   </tr>
			   	<tr> 				  
				 <th  align="CENTER" width="' . $width . '%" ></th>    
				 <th  align="LEFT" width="' . $width_s . '%"></th>
			   </tr>
			   	<tr> 				  
				 <th  align="CENTER" width="' . $width . '%" >11.CONSOLIDATOR</th>    
				 <th  align="LEFT" width="' . $width_s . '%">' . $ExpName . '</th>
			   </tr>
				
				<tr> 				  
				 <th  align="CENTER" width="' . $width . '%" >ADDRESS:</th>    
				 <th  align="LEFT" width="' . $width_s . '%">' . $ExpAddress . '</th>
			   </tr>
					
				<tr> 				  
				 <th  align="CENTER" width="' . $width . '%" >ADDRESS:</th>    
				 <th  align="LEFT" width="' . $width_s . '%"></th>
			   </tr>
			   
			  ';


	$content .= '</table>';


	return $content;
}



function getCha2($iconn, $PoID)
{

	include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
	include($_SERVER['DOCUMENT_ROOT'] . "/includes/func.php");
	include "../SalesInculdes/SalesConstants.php";


	$style = '<style>
		th {
			font-size: 7.5px;
			padding : 9px;
		}
		td {
			font-size: 7.5px;
			padding : 9px;
		}
		p {
			//line-height: 0.01;
		}
		</style>';

	global $PoDataRow, $DocType;


	$SqlScrmPoData = "SELECT * FROM ScrmPoData as SPD
	left JOIN ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID
	left JOIN ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
	left JOIN AdminExporterDetails as AED on SPD.ProcessorID=AED.ExpID
	left JOIN ScrmPortCities as SPC on SPD.PortOfDischarge=SPC.PortId
	WHERE PoID='$PoID'";

	// echo $SqlScrmPoData;exit;
	$StmtPoData = $iconn->query($SqlScrmPoData);
	$PoDataRow = $StmtPoData->fetch_assoc();

	$ExpID = $PoDataRow['ExporterID'];
	$ProcessorID = $PoDataRow['ProcessorID'];
	$ConsigneeName = $PoDataRow['ConsigneeName'];
	$PortofLoading = $PoDataRow['PortofLoading'];
	$CountryID = $PoDataRow['DestinationCountry'];

	/* $ProcsrName = $PoDataRow['ExpName'];
	$ProcsrAddress = $PoDataRow['ExpAddress']; */

	$ProcessorData = getExporterDetails($ProcessorID, $CountryID, $DocType);
	$ProcsrName = $ProcessorData['ExpName'];
	$ProcsrAddress = $ProcessorData['ExpAddress'];

	$CustomInvoiceSql = "SELECT * FROM ScrmCustomInvoices as SCI
	left join`ScrmShipmentDetails` as  SSD on SSD.PiID=SCI.PiID
	left join ScrmISFDetails as SIDS on SIDS.PIID=SCI.PiID
	WHERE SCI.PiID='$PoID'";
	// echo $CustomInvoiceSql;exit;
	$CustomInvoiceExec = $iconn->query($CustomInvoiceSql);
	$CustomInvoiceRow = $CustomInvoiceExec->fetch_assoc();


	$InvDate = $PoDataRow['InvDate'];
	$LoadingPort = $PoDataRow['Name'];

	$SqlPoGradeData = "SELECT 
	SUM(Mcs) as MCS,Brand FROM `ScrmPoGradeData`  WHERE InvId='$PoID'";
	$StmtPoGradeData = $iconn->query($SqlPoGradeData);
	$RowPoGradeData = $StmtPoGradeData->fetch_assoc();

	$MCS = $RowPoGradeData['MCS'];
	$Brand = $RowPoGradeData['Brand'];
	// $Brand = $RowPoGradeData['Brand'];

	$SlHSCode = "SELECT SPGD.VarietyID,SPL.VarietyID,SPL.PreparationType, SPM.HSCode FROM `ScrmPoGradeData` SPGD 
	LEFT JOIN ScrmPIVarietyList SPL ON SPL.VarietyID=SPGD.VarietyID
	LEFT JOIN ScrmPreparationMaster SPM ON SPL.PreparationType=SPM.ShortCode WHERE SPGD.InvId='$PoID'";
	$ExecHSCode = $iconn->query($SlHSCode);
	while ($RowHSCode = $ExecHSCode->fetch_assoc()) {

		$ArrayHSCode[] = $RowHSCode['HSCode'];
	}

	$HSCodes = implode(",", array_unique($ArrayHSCode));
	// $HSCodes = implode(",", ($ArrayHSCode));

	$PaymentData = getPaymentValues($iconn, $PoID);

	$ExporterData = getExporterDetails($ExpID);
	$PackSealNo = $CustomInvoiceRow['PackSealNo'];
	$SealNo = $CustomInvoiceRow['SealNo'];
	$SBNo = $CustomInvoiceRow['SBNo'];
	$FinalDestination = $CustomInvoiceRow['FinalDestination'];
	$SDD = $CustomInvoiceRow['SDD'];

	$ETD = getFormatedDateNA($CustomInvoiceRow['ETD']);
	$ETA_FD = getFormatedDateNA($CustomInvoiceRow['ETA_FD']);
	$ETA_POD = getFormatedDateNA($CustomInvoiceRow['ETA_POD']);

	$ExpName = $ExporterData['ExpName'];
	$ExpAddress = $ExporterData['ExpAddress'];
	// echo $ProcessorID;exit;
	$DocType = 'TPT';

	$ProcessorData = getExporterDetails($ProcessorID, $CountryID, $DocType);
	$ProcessorName = $ProcessorData['ExpName'];
	$ProcessorAddress = $ProcessorData['ExpAddress'];


	// $SqlPOgradeData = "SELECT * FROM `ScrmCIGradeData` where InvId='$PoID'  ";
	$SqlPOgradeData = "SELECT * FROM `ScrmPoGradeData` where InvId='$PoID'  ";
	$StmtPOgradeData = $iconn->query($SqlPOgradeData);


	$SqlCIGradeData = "SELECT * FROM `ScrmCIGradeData` SCGD
	LEFT JOIN AdminPackingStyles APS ON APS.PCKID=SCGD.PackingStyleID
	LEFT JOIN `ScrmBrandsMaster` SBM on  SBM.BrandName=SCGD.Brand
	WHERE InvId='$PoID'";

	// 	$SqlCIGradeData = "SELECT *,sum(Mcs) as BreakupMcs FROM `ScrmPoGradeData` SCGD
	// LEFT JOIN AdminPackingStyles APS ON APS.PCKID=SCGD.PackingStyleID
	// left join `ScrmBrandsMaster` SBM on  SBM.BrandName=SCGD.Brand
	//  WHERE InvId='$PoID'  GROUP BY Variety,PackingStyle,Brand ORDER BY SCGD.ID"; 
	// changed on 
	$SqlCIGradeData = "SELECT sum(MCS) as Mcs,Variety,PackingStyle,Brand,PrintStatus,
	GROUP_CONCAT(Grade,'- ',MCS,' M/CTNS' ORDER BY SCGD.ID ASC) as GradeMcs FROM `ScrmPoGradeData` SCGD
	LEFT JOIN AdminPackingStyles APS ON APS.PCKID=SCGD.PackingStyleID
	LEFT JOIN `ScrmBrandsMaster` SBM on  SBM.BrandName=SCGD.Brand
	WHERE InvId='$PoID'  GROUP BY Variety,PackingStyle,Brand ORDER BY  SCGD.ID asc";

	$StmtCIGradeData = $iconn->query($SqlCIGradeData);

	while ($RowCIGradeData = $StmtCIGradeData->fetch_assoc()) {

		$BrandPrintStatus = $RowCIGradeData['PrintStatus'];
		if ($BrandPrintStatus) {
			$Brand = "BRAND:  " . $RowCIGradeData['Brand'];
		} else {
			$Brand = '';
		}

		// $AryBreakUp[] = $RowCIGradeData['Variety'] . '-' . $RowCIGradeData['Mcs'] . ' M/CTNS.<br> PACKING:' . $RowCIGradeData['PackingStyle'] . '' . $Brand;
		// $AryBreakUp[] = $RowCIGradeData['Variety'] . '-' . $RowCIGradeData['BreakupMcs'] . ' M/CTNS.<br> PACKING:' . $RowCIGradeData['PackingStyle'] . '' . $Brand;

		$AryBreakUp[] = $RowCIGradeData['Variety']
			. '.PACKING:' . $RowCIGradeData['PackingStyle'] . '   '
			. $RowCIGradeData['GradeMcs'] . '    ' . $Brand;
		// $BreakUpMcs = $RowCIGradeData['BreakupMcs'];

		$TtlMCS += $RowCIGradeData['Mcs'];

		$Sno++;
	}

	while ($RowPOgradeData = $StmtPOgradeData->fetch_assoc()) {

		$MCS = $RowPOgradeData['Mcs'];
		$TotalMcs += $MCS;
	}

	// echo $TotalMcs;
	// exit;
	$SqlLoadngPort = "SELECT Name FROM `ScrmPortCities` WHERE PortId='$PortofLoading'";
	$StmtLoadngPort = $iconn->query($SqlLoadngPort);
	$RowLoadngPort = $StmtLoadngPort->fetch_assoc();
	//PRINT DT
	$LoadingPort = $RowLoadngPort['Name'];
	$ConsigneeName = $PoDataRow['ConsigneeName'];
	$ConsigneeAddress = $PoDataRow['ConsigneeAddress'];
	$ConsigneeIRSNumber = $PoDataRow['ConsigneeIRSNumber'];
	$PoNo = $PoDataRow['PoNo'];
	$ReferenceNo = $PoDataRow['ReferenceNo'];
	$PoDate = getFormatedDateNA($PoDataRow['PoDate']);
	$BLNo = $CustomInvoiceRow['BLNo'];
	$OriginatingVessel = $CustomInvoiceRow['OriginatingVessel'];
	$ImportingVessel = $CustomInvoiceRow['ImportingVessel'];
	$BLDate = getFormatedDateNA($CustomInvoiceRow['BLDate']);
	$VoyOrg = $CustomInvoiceRow['VoyOrg'];
	$VoyFinal = $CustomInvoiceRow['VoyFinal'];
	$TranshipmentDate = getFormatedDateNA($CustomInvoiceRow['TranshipmentDate']);
	$LoadingDate = getFormatedDateNA($CustomInvoiceRow['LoadingDate']);
	$ContainerNumber = $CustomInvoiceRow['ContainerNo'];
	$DeliveryTerms = $PoDataRow['DeliveryTerms'];

	$BreakUp = implode("<br>", array_unique($AryBreakUp));
	$BreakUp = implode("<br>", ($AryBreakUp));

	//ECHO $DeliveryTerms; EXIT;

	if ($DeliveryTerms == 'CY/CY') {

		$ShipToAddress = $ConsigneeName . $ConsigneeAddress;
	} else {

		$SqlStoreDeliveryDetails = "SELECT StoreID,StoreName,StoreAddress1 FROM `ScrmStoreDeliveryDetails` WHERE StoreID='$SDD'";

		$StmtStoreDeliveryDetails = $iconn->query($SqlStoreDeliveryDetails);
		$RowStoreDeliveryDetails = $StmtStoreDeliveryDetails->fetch_assoc();

		$ShipToName = $RowStoreDeliveryDetails['StoreName'];
		$ShipToAddress = $ShipToName . $RowStoreDeliveryDetails['StoreAddress1'];
	}



	$SqlVessalName = "SELECT VesselName,VOYNO,FromTOPID,ToTOPID,TranshipDt,
	SPC.Name AS LoadingPort,FromDate,
	SPDst.Name AS DischargePort
	FROM `ScrmTranshipmentPortsList` STPL
	LEFT JOIN ScrmPIData SPD ON SPD.PoID=STPL.PIID
	LEFT JOIN ScrmPortCities SPC ON SPC.PortId=SPD.PortofLoading
	LEFT JOIN ScrmPortCities SPDst ON SPDst.PortId=SPD.PortOfDischarge
	WHERE PIID='$PoID' ";

	//   $SqlVessalName = "SELECT VesselName,VOYNO,FromTOPID,ToTOPID,TranshipDt,
	// SPC.Name AS LoadingPort,FromDate,
	// SPDst.Name AS DischargePort,(SELECT GROUP_CONCAT(CONCAT(VesselName, '( ', FromTOPID,')') SEPARATOR ',<br> ') AS ConcatenatedValues FROM 
	// ( SELECT VesselName,FromTOPID,ROW_NUMBER() 
	// OVER (ORDER BY TspID) AS RowNum FROM ScrmTranshipmentPortsList where PIID='$PoID' ) AS RankedRecords WHERE RowNum > 1) as FinalVesselName
	//  FROM `ScrmTranshipmentPortsList` STPL
	// LEFT JOIN ScrmPIData SPD ON SPD.PoID=STPL.PIID
	// LEFT JOIN ScrmPortCities SPC ON SPC.PortId=SPD.PortofLoading
	// LEFT JOIN ScrmPortCities SPDst ON SPDst.PortId=SPD.PortOfDischarge
	// WHERE PIID='$PoID' ";  //uma 


	$StmtVessalName = $iconn->query($SqlVessalName);
	$OrgVessalName = null;
	$OrgVOYNO = null;
	while ($RowVessalName = $StmtVessalName->fetch_assoc()) {
		$ArrayData[] = $RowVessalName;



		if ($RowVessalName['ToTOPID'] == $RowVessalName['DischargePort']) {

			$TranshipPort = $RowVessalName['ToTOPID'];
			$FromTranPort = $RowVessalName['FromTOPID'];
			$TranshipDt = getFormatedDateNA($RowVessalName['TranshipDt']);
			$FromDate = getFormatedDateNA($RowVessalName['FromDate']); //uma 

		} else {
			$TranshipPort = '';
			$TranshipDt = '';
			$FromDate = '';
			$FromTranPort = '';
		}


		$AryTrnsPrt[] = $FromDate . ' (' . $FromTranPort . ')'; //uma
		//$AryTrnsPrt[]=$TranshipDt.' ('.$TranshipPort.')';

		if (in_array(' ()', $AryTrnsPrt)) {
			unset($AryTrnsPrt[array_search(' ()', $AryTrnsPrt)]);
		}

		// if ($RowVessalName['FromTOPID'] == $RowVessalName['LoadingPort']) {
		// 	$OrgVessalName = $RowVessalName['VesselName'];
		// 	$OrgVOYNO = $RowVessalName['VOYNO'];
		// } else if ($RowVessalName['FromTOPID'] == 'VISAKHAPATNAM' || $RowVessalName['FromTOPID'] == 'VISAKHAPATNAM, INDIA') {
		// 	$OrgVessalName = $RowVessalName['VesselName'];
		// 	$OrgVOYNO = $RowVessalName['VOYNO'];
		// }

		if ($OrgVessalName === null && $OrgVOYNO === null) {
			$OrgVessalName = $RowVessalName['VesselName'];
			$OrgVOYNO = $RowVessalName['VOYNO'];
		}

		if ($RowVessalName['ToTOPID'] == $RowVessalName['DischargePort']) {

			// $FinalVessalName = $RowVessalName['VesselName'] . ' (' . $RowVessalName['FromTOPID'] . ')';//commented by uma 
			// $FinalVessalName = $RowVessalName['FinalVesselName']; //added by uma 09-05-2024
			$FinalVOYNO = $RowVessalName['VOYNO'] . ' (' . $RowVessalName['FromTOPID'] . ')';
			$FinalDest = $RowVessalName['ToTOPID'];
		} else {
			$FinalDest = $RowVessalName['ToTOPID'];
		}
		$ETADischarge = getFormatedDateNA($RowVessalName['TranshipDt']);
	}
	//added by uma 09-05-2024
	for ($i = 1; $i < count($ArrayData); $i++) {
		$ArrayFinalVOYNO[] = $ArrayData[$i]['VOYNO'] . '(' . $ArrayData[$i]['FromTOPID'] . ')';
		$ArrayFinalVessalName[] = $ArrayData[$i]['VesselName'] . '(' . $ArrayData[$i]['FromTOPID'] . ')';
		$ArrayTransportDates[] = getFormatedDateNA($ArrayData[$i]['FromDate']) . '(' . $ArrayData[$i]['FromTOPID'] . ')';
	}
	$FinalVOYNO = implode(',<br>', $ArrayFinalVOYNO);
	$FinalVessalName = implode(', <br> ', $ArrayFinalVessalName);
	$TrnsPrtList = implode(',<br>', $ArrayTransportDates);
	//added by uma 09-05-2024
	// $TrnsPrtList = implode("<br>   ", ($AryTrnsPrt)); //commented by uma 10-05-2024

	$content = '';

	$size = 40;
	$size1 = 60;
	$width = 100;


	$content .= $style;
	$content .= '
			  <H4 align="Center"><b><u>WILLIAMS CLARKE CO.,INC <BR>
			  IMPORTER SECURITY FILING DATA (ISF-10+2)WORKSHEET TEMPLATE </b></u></H4>
			  <p align="left" style="font-size:7.5px;"><b>Note:</u> Fields marked with an (*) designate mandatory date require for ISF filing. All required address information must contain a postal code (zip) or<br>the <b><u>ISF will be rejected by CBP. ISF date must be received a minimum of three working days prior to loading on the final vessel arriving the cargo</u></b>
			  <br><u><b>into the United States.</u></b> If available a copy of the commerical invoice and related shipping documentation would helpful for data verification and <br>unified entry filing.</p>
			  
			  <table border="1" cellspacing="0" cellpadding="5" align="center" width="100%">


               <tr>  	   
				 <th  align="LEFT" width="50%" ><B>Importer Name:</B> <BR> ' . $ExporterData['ExpName'] . '</th>    
				 <th  align="LEFT" width="35%" >P.O or Ref# <BR>' . $PoNo . '    ' . $ReferenceNo . '</th>    
				 <th  align="LEFT" width="15%" >Date: <BR>' . $PoDate . '</th>    
				</tr>
				
			  <tr> 	   
				 <th  align="LEFT" width="50%" >Master B/L w/SCAC: <BR>
				 ' . $BLNo . '</th>    
				 <th  align="LEFT" width="50%" >* House Way Bill w/SCAC(if applicable): <BR></th> 
			  </tr>
			  
			  <tr> 			   
				 <th  align="LEFT" width="30%" >Originating Importing Vessel:<BR>' . $OrgVessalName . '</th>    
				 <th  align="LEFT" width="20%" >Voy# <BR>' . $OrgVOYNO . '</th> 
				 <th  align="LEFT" width="50%" >Est. date of loading:<BR>
				 ' . $LoadingDate . '(' . $LoadingPort . ')</th> 
				 
			  </tr>  
				 
			<tr> 	   
				 <th  align="LEFT" width="30%" >Final Importing Vessel:<BR> ' . $FinalVessalName . '</th>    
				 <th  align="LEFT" width="20%" >Voy# <BR>' . $FinalVOYNO . '</th> 
				 <th  align="LEFT" width="30%" >Est. date of transshipment<BR>' . $TrnsPrtList . '</th>    
				 <th  align="LEFT" width="20%" >ETA:<BR>' . $FinalDest . '<br>' . $ETADischarge . '</th> 
			</tr> 

           
			   <tr> 
				
			   <th  align="LEFT"  width="' . $width . '%"><B>Container No.
			   (attachment if necessary)<br>' . $ContainerNumber . '</B></th>  
				 
                </tr> 
				<tr> 
				
			   <th  align="LEFT"  width="' . $width . '%"><B> 1. Importer of record number
			   (IRS #,Customs assigned#,or SS # as applicable): <BR> 
			   044601-01124</B></th>  
				
                </tr> 
				
				<tr> 
				
			   <th  align="LEFT"  width="' . $width . '%"> 2. Buyer -complete name & address: 
			   <BR> 
			   <B>' . $ConsigneeName . ',' . nl2br($ConsigneeAddress) . '</B></th>  
				
                </tr> 
				<tr> 
				
			   <th  align="LEFT"  width="' . $width . '%">3. Consignee number (IRS #,Customs assigned#,or SS # as applicable): <BR>
			   ' . $ConsigneeIRSNumber . '
			   </th>  
				
                </tr> 
				
				<tr> 
				
			   <th  align="LEFT"  width="' . $width . '%">4. Consignee name & address : <BR>
			   <B>' . $ConsigneeName . ',' . nl2br($ConsigneeAddress) . '</B></th>  
				
                </tr> 
				
				
				<tr> 				
			     <th  align="LEFT"  width="' . $width . '%">5. Ship to-complete name & address:
				 <br><b>' . nl2br($ShipToAddress) . '</b></th>  
				 
                </tr> 
				
				<tr> 				
			     <th  align="LEFT"  width="' . $width . '%">6. *Manufacture (Or Supplier)-Complete name & address:
				 <b><br>   ' . $ProcessorName . '<br>   ' . nl2br($ProcessorAddress) . '</b></th>  
				 
                </tr> 
				
				<tr> 				
			     <th  align="LEFT"  width="' . $width . '%">7. *Seller -Complete name & address:
				 <B><BR>   ' . $ExpName . ',<BR>  ' . nl2br($ExpAddress) . '</B></th>  
				 
                </tr>
				
				<tr> 				
			     <th  align="LEFT"  width="' . $width . '%">8. *Container Stuffing location-Complete name & address:
				 <b><br>    ' . $ProcessorName . '<br>   ' . nl2br($ProcessorAddress) . '</b></th>  
				 
                </tr>
				
				<tr> 				
			     <th  align="LEFT"  width="' . $width . '%">9. *Consolidator(if applicable)-Complete name & address:
				<B><BR>   ' . $ExpName . ',<BR>  ' . nl2br($ExpAddress) . '</B></th>  
				 
                </tr>
						   
				<tr> 				  
				 <th  align="LEFT" width="' . $width . '%" >10. *Country of Origin : INDIA 
				 </th>    
				
			   </tr>
			   
			    <tr> 				  
				 <th  align="LEFT" width="' . $width . '%" >11. Detailed commodity description or HTS number:
				 
				 <BR>
				 ' . $BreakUp . '
				 
				 <BR>    <B>TOTAL:</B>' . $TotalMcs . ' M/CTNS
				 <BR>    <B>HS CODE:</B>' . $HSCodes . '
				 </th>    
				
			   </tr>
				
				
              
                			   
		
			  ';


	$content .= '</table>';


	return $content;
}


function getCha21($iconn, $PoID)
{

	include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
	include($_SERVER['DOCUMENT_ROOT'] . "/includes/func.php");
	include "../SalesInculdes/SalesConstants.php";

	global $PoDataRow, $style;

	$content = '';

	$size = 40;
	$size1 = 60;
	$width = 100;


	$content .= $style;
	$content .= '
			
			  <table border="1" cellspacing="0" cellpadding="5" align="center" width="100%">
               <tr>  
			   
<th  align="LEFT" width="100%" >
 <P align="left" style="font-size:9.75px;">
 1. Importer of record number / Foreign trade zone applicant identification number. Internal Revenue Service (IRS) 
number,<BR>
Employer Identification Number (EIN), Social Security Number (SSN), or CBP assigned number of the entity liable for <BR>
payment of all duties and responsible for meeting all statutory and regulatory requirements incurred as a result of <BR>
importation. For goods intended to be delivered to a foreign trade zone (FTZ), the IRS number, EIN, SSN, or CBP 
assigned <BR>
number of the party filing the FTZ documentation with CBP must be provided<BR>
2. Buyer. Name and address of the last known entity to whom the goods are sold or agreed to be sold. If the goods are to 
be<BR>
imported otherwise than in pursuance of a purchase, the name and address of the owner of the goods must be provided . <BR>
A
widely recognized commercially accepted identification number for this party may be provided in lieu of the name and
address.<BR>
3. Consignee number(s). Internal Revenue Service (IRS) number, Employer Identification Number (EIN), Social Security
Number (SSN), or CBP assigned number of the individual(s) or firm(s) in the United States on whose account the
merchandise is shipped.<BR>
4. Consignee. Complete name and address fo individual(s) or firm(s) in the United State whose account the merchandise 
is
shipped.<BR>
5. Ship to party. Name and address of the first deliver-to party scheduled to physically receive the goods after the goods 
have <BR>
been released from customs custody. A widely recognized commercially accepted identification number for this party may <BR>
be provided in lieu of the name and address. <BR>
6. Manufacturer (or supplier). Name and address of the entity that last manufactures, assembles, produces, or grows the <BR>
commodity or name and address of the party supplying the finished goods in the country from which the goods are <BR>
leaving. <BR>
In the alternative the name and address of the manufacturer (or supplier) that is currently required by the import laws, 
rules <BR>
and regulations of the United States (i.e., entry procedures) may be provided (this is the information that is used to create 
the <BR>
existing manufacturer identification (MID) number for entry purposes). A widely recognized commercially accepted
identification number for this party may be provided in lieu of the name and address. <BR>
7. Seller. Name and address of the last known entity by whom the goods are sold or agreed to be sold. If the goods are to <BR>
be <BR>
imported otherwise than in pursuance of a purchase, the name and address of the owner of the goods must be provided . <BR>
A <BR>
widely recognized commercially accepted identification number for this party may be provided in lieu of the name and <BR>
address.<BR>
8. Container stuffing location. Name and address(es) of the physical location(s) where the goods were stuffed into the 
container.<BR>
For break bulk shipments, as defined in § 149.1 of this part, the name and address(es) of the physical location(s) where 
the <BR>
goods were made “ship ready” must be provided. A widely recognized commercially accepted identification number for 
this <BR>
element may be provided in lieu of the name and address.
9. Consolidator (stuffer). Name and address of the party who stuffed the container or arranged for the stuffing of the 
container. <BR>
For break bulk shipments, as defined in § 149.1 of this part, the name and address of the party who made the goods 
“ship <BR>
ready” or the party who arranged for the goods to be made “ship ready” must be provided. A widely recognized <BR>
commercially accepted identification number for this party may be provided in lieu of the name and address. <BR>
10 Country of origin. Country of manufacture, production, or growth of the article, based upon the import laws, rules and <BR>
regulations of the United States. <BR>
11. Commodity HTSUS number. Duty/statistical reporting number under which the article is classified in the Harmonized  <BR>
Tariff <BR>
Schedule of the United States (HTSUS). The HTSUS number must be provided to the six-digit level. The HTSUS number <BR>
may be provided up to the 10-digit level. This element can only be used for entry purposes if it is provided at the 10-digit <BR>
level or greater by the importer of record or its licensed customs broker. In lieu of the HTSUS number a complete an <BR>
detailed description of each article contained in the shipment.</P></th>    
				
				</tr>
				
		
			  ';


	$content .= '</table>';


	return $content;
}



function getCha3($iconn, $PoID)
{

	include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
	include($_SERVER['DOCUMENT_ROOT'] . "/includes/func.php");

	include "../SalesInculdes/SalesConstants.php";

	global $PoDataRow, $style, $DocType;

	$CustomInvoiceSql = "SELECT * FROM ScrmPoData as SCI
	left join`AdminExporterDetails` as  SSD on SSD.ExpID=SCI.ProcessorID
	left join`ScrmISFDetails` as  ISF on ISF.PIID=SCI.PoID
	left join ScrmNotifyparty as SNP on SNP.NotifyPartyID=SCI.NotifyPartyID 
	WHERE SCI.PoID='$PoID'";

	$CustomInvoiceExec = $iconn->query($CustomInvoiceSql);
	$CustomInvoiceRow = $CustomInvoiceExec->fetch_assoc();

	// echo json_encode($CustomInvoiceRow, JSON_PRETTY_PRINT);
	// exit;
	$SlHSCode = "SELECT SPGD.VarietyID,SPL.VarietyID,SPL.PreparationType,
	SPM.HSCode FROM `ScrmPoGradeData` SPGD LEFT 
	JOIN ScrmPIVarietyList 
	SPL ON SPL.VarietyID=SPGD.VarietyID 
	LEFT JOIN ScrmPreparationMaster SPM ON SPL.PreparationType=SPM.ShortCode 
	WHERE SPGD.InvId='$PoID'";
	$ExecHSCode = $iconn->query($SlHSCode);
	while ($RowHSCode = $ExecHSCode->fetch_assoc()) {
		$ArrayHSCode[] = $RowHSCode['HSCode'];
	}

	$HSCodes = implode(",", $ArrayHSCode);
	$ExpID = $PoDataRow['ExporterID'];
	$PoNo = $PoDataRow['PoNo'];
	$PortOfDischarge = $PoDataRow['PortOfDischarge'];
	$CountryID = $PoDataRow['DestinationCountry'];
	$ProcessorID = $CustomInvoiceRow['ProcessorID'];

	$SDD = $CustomInvoiceRow['SDD'];
	$DocType = 'TPT';

	$ExporterData = getExporterDetails($ExpID);
	$ProcessorData = getExporterDetails($ProcessorID, $CountryID, $DocType);
	$SupplierNm = $ProcessorData['ExpName'];
	$SupplierAddres = $ProcessorData['ExpAddress'];

	$Buyer = $PoDataRow['ConsigneeName'];
	$BuyerAddress = $PoDataRow['ConsigneeAddress'];
	$ConsigneeIRSNum = $PoDataRow['ConsigneeIRSNumber'];
	$ByRoad = $CustomInvoiceRow['ByRoad'];
	$DeliveryTerms = $PoDataRow['DeliveryTerms'];
	$VoyOrg = $CustomInvoiceRow['VoyOrg'];

	if ($DeliveryTerms == 'CY/CY') {

		$ShipToName = $Buyer;
		$ShipToAddress = $BuyerAddress;
	} else {

		$SqlStoreDeliveryDetails = "SELECT StoreID,StoreName,StoreAddress1 FROM `ScrmStoreDeliveryDetails`
 		WHERE StoreID='$SDD' ";
		$StmtStoreDeliveryDetails = $iconn->query($SqlStoreDeliveryDetails);
		$RowStoreDeliveryDetails = $StmtStoreDeliveryDetails->fetch_assoc();

		$ShipToName = $RowStoreDeliveryDetails['StoreName'];
		$ShipToAddress = $RowStoreDeliveryDetails['StoreAddress1'];
	}

	$content = '';

	$size = 40;
	$size1 = 60;

	$S_NO = 1;
	$width = 38;
	$width2 = 55;
	$width_s = 7;

	$TelphoneNumber = $CustomInvoiceRow['NPartyContactNumber'] ? 'TEL : ' . $CustomInvoiceRow['NPartyContactNumber'] : '';
	$content .= $style;
	$content .= '<h3 align="CENTER"><br><br><br>' . $CustomInvoiceRow['NotifyPartyName'] . '</h3>
			  	<h5 align="CENTER">' . $CustomInvoiceRow['NPartyAddress'] . '</h5>
			  	<h5 align="CENTER">' . $TelphoneNumber . '</h5>
			  	<h3 align="CENTER">10 + 2 Importer’s Security Filing Data Elements</h3>
				<table border="1" cellspacing="0" cellpadding="11" align="center" width="100%"font-size:15px>
				<tr> 
				<th  align="LEFT" width="' . $width_s . '%" ><B>1</B></th>    
				<th  align="LEFT" width="' . $width . '%" ><B>Manufacturer (or supplier) Name:</B></th>    
				<th  align="LEFT" width="' . $width2 . '%">' . $SupplierNm . '</th>
				</tr> 
				<tr> 
				<th  align="LEFT" width="' . $width_s . '%" > <B></B></th>    				   
				<th  align="LEFT" width="' . $width . '%" ><B>Manufacturer (or supplier) Address:</B></th>    
				<th  align="LEFT" width="' . $width2 . '%">' . nl2br($SupplierAddres) . '</th>
				</tr>  
				<tr> 
				<th  align="LEFT" width="' . $width_s . '%" ><B>2</B></th>    
				<th  align="LEFT" width="' . $width . '%" ><B>Seller / Shipper Name:</B></th>    
				<th  align="LEFT" width="' . $width2 . '%">' . $ExporterData['ExpName'] . '</th>
				</tr>  
				<tr> 
				<th  align="LEFT" width="' . $width_s . '%" ><B></B></th>    
				<th  align="LEFT"  width="' . $width . '%"><B>Seller / Shipper Address:</B></th>  
				<th  align="LEFT" width="' . $width2 . '%">' . $ExporterData['ExpAddress'] . '</th>
                </tr> 
				<tr> 
				<th  align="LEFT" width="' . $width_s . '%" ><B>3</B></th>    
				<th  align="LEFT"  width="' . $width . '%"><B>Buyer Name</B></th>  
				<th  align="LEFT" width="' . $width2 . '%">' . $Buyer . '</th>
                </tr> 
				<tr> 
                <th align="LEFT" width="' . $width_s . '%" ><B></B></th>    
				<th align="LEFT"  width="' . $width . '%"><B>Buyer Address:</B></th>  
				<th align="LEFT" width="' . $width2 . '%">' . nl2br($BuyerAddress) . '</th>
                </tr> 
				<tr>
           		<th  align="LEFT" width="' . $width_s . '%" ><B>4</B></th>    
				<th  align="LEFT"  width="' . $width . '%"><B>Ship To /Consignee Name:</B></th>  
			   	<th  align="LEFT" width="' . $width2 . '%">' . $ShipToName . '</th>
                </tr>
				<tr> 
				<th  align="LEFT" width="' . $width_s . '%" ><B></B></th>    				
			  	<th  align="LEFT"  width="' . $width . '%"><B>Ship To / Consignee Address:</B></th>  
				<th  align="LEFT" width="' . $width2 . '%">' . nl2br($ShipToAddress) . '</th>
                </tr> 
            
				<tr> 
               	<th  align="LEFT" width="' . $width_s . '%" ><B>5</B></th>    
				
			   	<th  align="LEFT"  width="' . $width . '%"><B>Container stuffing location:</B></th>  
				<th  align="LEFT" width="' . $width2 . '%">' . $SupplierNm . ' <br> ' . nl2br($SupplierAddres) . '</th>
                </tr> 
           
				<tr> 
				<th  align="LEFT" width="' . $width_s . '%" ><B>6</B></th>    
				<th  align="LEFT"  width="' . $width . '%"><B>Consolidator (stuffer) Name:</B></th>  
				<th  align="LEFT" width="' . $width2 . '%">' . $ExporterData['ExpName'] . '</th>
                </tr> 
            	<tr> 
				<th  align="LEFT" width="' . $width_s . '%" ><B></B></th>    
				<th  align="LEFT"  width="' . $width . '%"><B>Consolidator (stuffer) Address:</B></th>  
				<th  align="LEFT" width="' . $width2 . '%">' . $ExporterData['ExpAddress'] . '</th>
                </tr> 
				<tr> 
				<th align="LEFT" width="' . $width_s . '%" ><B>7</B></th>    
				<th align="LEFT" width="' . $width . '%" ><B>Importer of Record Number:</B></th>    
				<th align="LEFT" width="' . $width2 . '%">044601-01124</th>
				</tr>   
				<tr> 
				<th align="LEFT" width="' . $width_s . '%" ><B>8</B></th>    				
				<th align="LEFT" width="' . $width . '%" ><B>Consignee Number:</B></th>    
				<th align="LEFT" width="' . $width2 . '%">' . $ConsigneeIRSNum . '</th>
				</tr>   
				<tr> 
				<th align="LEFT" width="' . $width_s . '%" ><B>9</B></th>    				
				<th align="LEFT" width="' . $width . '%" ><B>Country of Origin:</B></th>    
				<th align="LEFT" width="' . $width2 . '%">INDIA</th>
				</tr>   
				<tr> 
				<th align="LEFT" width="' . $width_s . '%" font-size: 8px ><B>10</B></th>    
				<th align="LEFT" width="' . $width . '%" ><B>Commodity HTSUS Number:</B></th>    
				<th align="LEFT" width="' . $width2 . '%">' . $HSCodes . '</th>
				</tr>';
	$content .= '</table>';


	return $content;
}



function getCha31($iconn, $PoID)
{

	include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
	include($_SERVER['DOCUMENT_ROOT'] . "/includes/func.php");
	//include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");
	include "../SalesInculdes/SalesConstants.php";

	global $PoDataRow, $style;

	$CustomInvoiceSql = "SELECT * FROM ScrmCustomInvoices as SCI
	left join`ScrmShipmentDetails` as  SSD on SSD.PiID=SCI.PiID
	left join`ScrmPoData` as  SPID on SPID.PoID=SCI.PiID
	left join`ScrmCountriesList` as  SCL on SPID.DestinationCountry=SCL.id
	left join ScrmISFDetails as SISD on SCI.PiID=SISD.PIID
	WHERE SCI.PiID='$PoID'";

	$CustomInvoiceExec = $iconn->query($CustomInvoiceSql);
	$CustomInvoiceRow = $CustomInvoiceExec->fetch_assoc();


	$ByRoad = $CustomInvoiceRow['ByRoad'];

	$ExpID = $PoDataRow['ExporterID'];
	$PoNo = $PoDataRow['PoNo'];
	$PortOfDischarge = $PoDataRow['PortOfDischarge'];
	$NotifyPartyID = $PoDataRow['NotifyPartyID'];

	$SqlPoGradeData = "SELECT SUM(Mcs) as TMCS,Brand FROM `ScrmPoGradeData`  WHERE InvId='$PoID'";
	$StmtPoGradeData = $iconn->query($SqlPoGradeData);
	$RowPoGradeData = $StmtPoGradeData->fetch_assoc();

	$PaymentData = getPaymentValues($iconn, $PoID);

	$ExporterData = getExporterDetails($ExpID);

	$SqlDischargePrt = "SELECT * FROM `ScrmPortCities` WHERE PortId='$PortOfDischarge'";
	$StmtDischargePrt = $iconn->query($SqlDischargePrt);
	$RowDischargePrt = $StmtDischargePrt->fetch_assoc();



	// 	$SqlCIGradeData = "SELECT * FROM `ScrmPoGradeData` SCGD
	// LEFT JOIN AdminPackingStyles APS ON APS.PCKID=SCGD.PackingStyleID
	// left join `ScrmBrandsMaster` SBM on  SBM.BrandName=SCGD.Brand
	//  WHERE InvId='$PoID'";

	$SqlCIGradeData = "SELECT sum(MCS) as Mcs,Variety,PackingStyle,Brand,PrintStatus,
GROUP_CONCAT(Grade,'- ',MCS,' M/CTNS' ORDER BY SCGD.ID ASC) as GradeMcs FROM `ScrmPoGradeData` SCGD
LEFT JOIN AdminPackingStyles APS ON APS.PCKID=SCGD.PackingStyleID
left join `ScrmBrandsMaster` SBM on  SBM.BrandName=SCGD.Brand
 WHERE InvId='$PoID'  GROUP BY Variety,PackingStyle,Brand ORDER BY  SCGD.ID asc";
	$StmtCIGradeData = $iconn->query($SqlCIGradeData);

	while ($RowCIGradeData = $StmtCIGradeData->fetch_assoc()) {
		$BrandPrintStatus = $RowCIGradeData['PrintStatus'];
		if ($BrandPrintStatus) {
			$Brand = "BRAND:  " . $RowCIGradeData['Brand'];
		}

		//$AryBreakUp[]=$RowCIGradeData['Variety'].'.  PACKING:'.$RowCIGradeData['PackingStyle'].' -  '.$RowCIGradeData['Mcs'].' M/CTNS. BRAND:  '.$RowCIGradeData['Brand'];
		// $AryBreakUp[] = $RowCIGradeData['Variety'] . '.  PACKING:' . $RowCIGradeData['PackingStyle'] . ' -  ' . $RowCIGradeData['Mcs'] . ' M/CTNS. ' . $Brand;

		$AryBreakUp[] = $RowCIGradeData['Variety']
			. '.PACKING:' . $RowCIGradeData['PackingStyle'] . '   '
			. $RowCIGradeData['GradeMcs'] . '    ' . $Brand;
		$TtlMCS += $RowCIGradeData['Mcs'];

		$Sno++;
	}

	$TtlMCS = $TtlMCS . '.M/CTNS';

	$InvNO = $ExporterData['InvShortCode'] . $CustomInvoiceRow['CInvNo'] . '/' . $CurrentYear . '-' . $NxtYear;

	//PRINT DATA
	$VesselName = $CustomInvoiceRow['OriginatingVessel'];
	$Voygenumber = $CustomInvoiceRow['Voy'];
	$ContainerNumber = $CustomInvoiceRow['ContainerNo'];
	$LodingPort = $PoDataRow['Name'];
	$DischargePort = $RowDischargePrt['Name'];
	$FinalDestination = $CustomInvoiceRow['FinalDestination'];
	$DestinationCountry = $CustomInvoiceRow['name'];
	$VoyOrg = $CustomInvoiceRow['VoyOrg'];
	$LinerName = $CustomInvoiceRow['LinerName'];
	$ShippingLineID = $CustomInvoiceRow['ShippingLineID'];
	$ShippingOnboardDt = getFormatedDateNA($CustomInvoiceRow['ShippingOnboardDt']);
	$BreakUp = implode("<br>", array_unique($AryBreakUp));
	$BreakUp = implode("<br>", ($AryBreakUp));

	$SqlContnrOprtr = "SELECT LinerName FROM `ScrmLinerMaster` WHERE LinerID='$ShippingLineID'";
	$StmtContnrOprtr = $iconn->query($SqlContnrOprtr);
	$RowContnrOprtr = $StmtContnrOprtr->fetch_assoc();
	$LinerName = $RowContnrOprtr['LinerName'];

	$SqlFnlDstn = "SELECT Name AS FinalDstn FROM `ScrmPortCities` WHERE PortId='$FinalDestination'";
	$StmtFnlDstn = $iconn->query($SqlFnlDstn);
	$RowFnlDstn = $StmtFnlDstn->fetch_assoc();
	$FinalDestination = $RowFnlDstn['FinalDstn'];

	if ($ByRoad != '') {
		$FinalDestination = $ByRoad;
	} else {
		$FinalDestination = $RowFnlDstn['FinalDstn'];
	}

	$SqlOrgnVessel = "SELECT FromTOPID,VesselName,VOYNO FROM ScrmTranshipmentPortsList as TSP 
left join ScrmPoData as POD on TSP.PIID=POD.PoID 
left join ScrmPortCities  as SPC on SPC.PortId=POD.PortofLoading
WHERE TSP.FromTOPID=SPC.Name and  PoID=$PoID";
	$StmtOrgnVessel = $iconn->query($SqlOrgnVessel);
	$RowOrgnVessel = $StmtOrgnVessel->fetch_assoc();

	$VesselOrg = $RowOrgnVessel['VesselName'];
	$VoyOrg = $RowOrgnVessel['VOYNO'];


	$content = '';

	$size = 40;
	$size1 = 60;
	$S_NO = 1;
	$width = 40;
	$width2 = 60;
	$width_s = 10;


	$content .= $style;
	$content .= '
			  
			  	<h5 align="LEFT"><br><br><br><br><u>Vessel Stow Plan </u>:<br></h5>
			  	
			  <table border="0" cellspacing="3" cellpadding="5" align="center" width="100%">

			  <tr> 				   
		<th  align="LEFT" width="' . $width . '%" >Vessel Name and Operator</th>    
		<th  align="LEFT" width="' . $width2 . '%">: ' . $VesselOrg . '</th>
			   </tr>  
			   <tr> 				   
		<th  align="LEFT" width="' . $width . '%" >Voyage Number</th>    
		<th  align="LEFT" width="' . $width2 . '%">: ' . $VoyOrg . '</th>
				</tr>  
				 <tr>    
		<th  align="LEFT" width="' . $width . '%" >Container Operator</th>    
		<th  align="LEFT" width="' . $width2 . '%">: ' . $LinerName . '</th>
				</tr>  
				
			   <tr> 				
		<th  align="LEFT"  width="' . $width . '%">Equipment Number</th>  
		<th  align="LEFT" width="' . $width2 . '%">: ' . $ContainerNumber . '</th>
                </tr> 
				
				<tr> 
		<th  align="LEFT"  width="' . $width . '%">SIZE AND TYPE</th>  
		<th  align="LEFT" width="' . $width2 . '%">: 40 FEET REFRIGERATOR</th>
                </tr> 				
				<tr> 				
		<th  align="LEFT"  width="' . $width . '%">STOW POSITION</th>  
		<th  align="LEFT" width="' . $width2 . '%">: LOAD FULL</th>
                </tr> 
				
				<tr> 				
		<th  align="LEFT"  width="' . $width . '%">HAZMAT CODE</th>  
		<th  align="LEFT" width="' . $width2 . '%">: </th>
                </tr>
				<tr> 				
		<th  align="LEFT"  width="' . $width . '%">LOADING PORT</th>  
		<th  align="LEFT" width="' . $width2 . '%">: ' . $LodingPort . ',INDIA</th>
                </tr>           
			   <tr> 
		<th  align="LEFT"  width="' . $width . '%">DISCHARGE PORT</th>  
		<th  align="LEFT" width="' . $width2 . '%">: ' . $DischargePort . ',' . $DestinationCountry . '</th>
                </tr> 
          

				<tr> 				
		<th  align="LEFT"  width="' . $width . '%">FINAL DESTINATION</th>  
		<th  align="LEFT" width="' . $width2 . '%">: ' . $FinalDestination . ',' . $DestinationCountry . '</th>
                </tr> 
                <tr> 
		<th  align="LEFT"  width="' . $width . '%">CONTAINER STATUS MESSAGES</th>  
		<th  align="LEFT" width="' . $width2 . '%">: </th>
                </tr> 
           				              
				<tr> 
		<th  align="LEFT" width="' . $width . '%" >TERMINAL CONTAINER MOVEMENTS</th>    
		<th  align="LEFT" width="' . $width2 . '%">: </th>
				</tr>   

				<tr> 
		<th  align="LEFT" width="' . $width . '%" >CHANGE IN CONTAINR STATUS</th>    
		<th  align="LEFT" width="' . $width2 . '%">: </th>
				</tr>   

				<tr> 				
		<th  align="LEFT" width="' . $width . '%" >FOCUSED IN CONTAINER STATUS 
MESSAGING SET</th>    
		<th  align="LEFT" width="' . $width2 . '%">: ' . (($CustomInvoiceRow['TemperatureOfRecod'] == "") ? '-20 DEGREES CELIUS' : $CustomInvoiceRow['TemperatureOfRecod']) . '</th>
				</tr>   

				<tr> 				  
		<th  align="LEFT" width="' . $width . '%" >EQUIPMENT NUMBER</th>    
		<th  align="LEFT" width="' . $width2 . '%">: </th>
				</tr>   
               
			    <tr> 
		<th  align="LEFT" width="' . $width . '%" >EVENT DESCRIPTION</th>    
		<th  align="LEFT" width="' . $width2 . '%">: ' . $BreakUp . '<br> = ' . $TtlMCS . '</th>
				</tr>
				<tr> 
				  
        <th  align="LEFT" width="' . $width . '%" >DATE</th>    
		<th  align="LEFT" width="' . $width2 . '%">: ' . $ShippingOnboardDt . ',' . $LodingPort . '</th>
				</tr>
				<tr> 
		<th  align="LEFT" width="' . $width . '%" >TIME AND LOCATION</th>    
		<th  align="LEFT" width="' . $width2 . '%">:10:00 AM  ' . $LodingPort . ',INDIA</th>
				</tr>
				
			  ';


	$content .= '</table>';


	return $content;
}



function getCha32($iconn, $PoID)
{

	include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
	include($_SERVER['DOCUMENT_ROOT'] . "/includes/func.php");
	include "../SalesInculdes/SalesConstants.php";

	global $PoDataRow, $style;

	$CustomInvoiceSql = "SELECT * FROM ScrmCustomInvoices as SCI
  left join`ScrmShipmentDetails` as  SSD on SSD.PiID=SCI.PiID
  left join`ScrmPIData` as  SPID on SPID.PoID=SCI.PiID
  left join ScrmISFDetails as SISD on SCI.PiID=SISD.PIID
  WHERE SCI.PiID='$PoID'";

	$CustomInvoiceExec = $iconn->query($CustomInvoiceSql);
	$CustomInvoiceRow = $CustomInvoiceExec->fetch_assoc();


	$ExpID = $PoDataRow['ExporterID'];
	$PoNo = $PoDataRow['PoNo'];
	$PortOfDischarge = $PoDataRow['PortOfDischarge'];
	$TranshipmentPort = $CustomInvoiceRow['TranshipmentPort'];
	$TotalGrossWt = $CustomInvoiceRow['TotalGrossWt'];

	$SqlPoGradeData = "SELECT 
SUM(Mcs) as TMCS,Brand FROM `ScrmPoGradeData`  WHERE InvId='$PoID'";
	$StmtPoGradeData = $iconn->query($SqlPoGradeData);
	$RowPoGradeData = $StmtPoGradeData->fetch_assoc();

	$PaymentData = getPaymentValues($iconn, $PoID);
	$InvoiceData = getInvoiceWeight($iconn, $GradeID, $PoID);

	$ExporterData = getExporterDetails($ExpID);

	$SqlDischargePrt = "SELECT * FROM `ScrmPortCities` WHERE 
PortId='$PortOfDischarge'";
	$StmtDischargePrt = $iconn->query($SqlDischargePrt);
	$RowDischargePrt = $StmtDischargePrt->fetch_assoc();

	//$SqlTrnshipmntPrt="SELECT * FROM `ScrmTranshipmentPortsList` WHERE PIID='$PoID' order by TspID desc limit 1";//uma
	$SqlTrnshipmntPrt = "SELECT * FROM `ScrmTranshipmentPortsList` WHERE PIID='$PoID'";
	$StmtTrnshipmntPrt = $iconn->query($SqlTrnshipmntPrt);
	$PortCount = mysqli_num_rows($StmtPrtList);

	while ($RowTrnshipmntPrt = $StmtTrnshipmntPrt->fetch_assoc()) {


		$TrnsPrt = $RowTrnshipmntPrt['FromTOPID'];
		if ($TrnsPrt == 'VISAKHAPATNAM' || $TrnsPrt == 'VISAKHAPATNAM, INDIA') {
			$TrnsPrt = '';
		}
		$VesselName = $RowTrnshipmntPrt['VesselName'];
		$VOYNO = $RowTrnshipmntPrt['VOYNO'];
		$ETDDate = getFormatedDateNA($RowTrnshipmntPrt['FromDate']);

		if ($TrnsPrt == '') {
			$ETDDate = '';
			$VesselName = '';
			$VOYNO = '';
		}
		$AryTrnsPrt[] = $ETDDate . ' (' . $TrnsPrt . ')';
		$AryVessel[] = $VesselName . ' (' . $TrnsPrt . ')';
		$AryVOYNO[] = $VOYNO . ' (' . $TrnsPrt . ')';
		if (in_array(' ()', $AryTrnsPrt)) {
			unset($AryTrnsPrt[array_search(' ()', $AryTrnsPrt)]);
		}

		if (in_array(' ()', $AryVessel)) {
			unset($AryVessel[array_search(' ()', $AryVessel)]);
		}

		if (in_array(' ()', $AryVOYNO)) {
			unset($AryVOYNO[array_search(' ()', $AryVOYNO)]);
		}
	}

	$LastForeignPort = end($AryTrnsPrt);
	$ForeignPort = explode('(', $LastForeignPort);
	$ForeignPort = end($ForeignPort);
	$ForeignPort = str_replace(")", "", $ForeignPort);

	$TrnsPrtList = implode("<br>   ", ($AryTrnsPrt));
	$VesselList = implode("<br>  ", ($AryVessel));
	$VOYNOList = implode("<br>  ", ($AryVOYNO));


	$SqlArrivlDt = "SELECT TranshipDt FROM `ScrmTranshipmentPortsList` WHERE PIID='$PoID' 
AND FromTOPID='$ForeignPort'";
	$StmtArrivlDt = $iconn->query($SqlArrivlDt);
	$RowArrivlDt = $StmtArrivlDt->fetch_assoc();
	$ArrivalDt = getFormatedDateNA($RowArrivlDt['TranshipDt']);


	$BLNo = $CustomInvoiceRow['BLNo'];
	//$TranshipmentPort=$CustomInvoiceRow['TranshipmentPort'];
	$ETD_FD = getFormatedDateNA($CustomInvoiceRow['ETA_FD']);
	$ETA_POD = getFormatedDateNA($CustomInvoiceRow['ETA_POD']);
	$ContainerNumber = $CustomInvoiceRow['ContainerNo'];
	$SealNo = $CustomInvoiceRow['SealNo'];
	$VesselName = $CustomInvoiceRow['OriginatingVessel'];
	//$ImportingVessel=$CustomInvoiceRow['ImportingVessel'];
	$Voygenumber = $CustomInvoiceRow['Voy'];
	//$VoyFinal=$CustomInvoiceRow['VoyFinal'];
	$ContainerNumber = $CustomInvoiceRow['ContainerNo'];
	$PortOfDischarge = $RowDischargePrt['Name'];


	$content = '';

	$size = 40;
	$size1 = 60;
	$S_NO = 1;
	$width = 40;
	$width2 = 60;
	$width_s = 10;


	$content .= $style;
	$content .= '
			  

			  	
			  <table border="0" cellspacing="3" cellpadding="5" align="center" width="100%">

			  <tr> 				   
		<th  align="LEFT" width="' . $width . '%" >Bill of Lading No.</th>    
		<th  align="LEFT" width="' . $width2 . '%">: ' . $BLNo . '</th>
			   </tr>  
			   <tr> 				   
		<th  align="LEFT" width="' . $width . '%" >Foreign Port Before Vessel 
Departs for U.S.</th>    
		<th  align="LEFT" width="' . $width2 . '%">:  ' . $TrnsPrtList . '</th>
				</tr>  

				 <tr>    
		<th  align="LEFT" width="' . $width . '%" >Carrier SCAC</th>    
		<th  align="LEFT" width="' . $width2 . '%">:  </th>
				</tr>  
				
			   <tr> 				
		<th  align="LEFT"  width="' . $width . '%">Vessel Name</th>  
		<th  align="LEFT" width="' . $width2 . '%">:  ' . $VesselList . '</th>
                </tr> 
				
				<tr> 
		<th  align="LEFT"  width="' . $width . '%">Vessel country</th>  
		<th  align="LEFT" width="' . $width2 . '%">:   </th>
                </tr> 				
				<tr> 				
		<th  align="LEFT"  width="' . $width . '%">Vessel Number</th>  
		<th  align="LEFT" width="' . $width2 . '%">:  ' . $VOYNOList . '</th>
                </tr> 
				
				<tr> 				
		<th  align="LEFT"  width="' . $width . '%">Date of Arrival at First U.S. Port</th>  
		<th  align="LEFT" width="' . $width2 . '%">: ' . $ArrivalDt . '  (' . $PortOfDischarge . ')</th>
                </tr>
				<tr> 				
		<th  align="LEFT"  width="' . $width . '%">Quantity</th>  
		<th  align="LEFT" width="' . $width2 . '%">: ' . $InvoiceData['TotalMcs'] . ' MASTER CARTONS.</th>
                </tr>           
			   <tr> 
		<th  align="LEFT"  width="' . $width . '%">Unit of measure of quantity</th>  
		<th  align="LEFT" width="' . $width2 . '%">:  GROSS WT:' . number_format($TotalGrossWt, 3) . '  LBS <BR>
		NET WT     :' . number_format($InvoiceData['NetWgtLbs'], 3) . '  LBS</th>
                </tr> 
          

				<tr> 				
		<th  align="LEFT"  width="' . $width . '%">Foreign Port of Lading</th>  
		<th  align="LEFT" width="' . $width2 . '%">: ' . $ForeignPort . '</th>
                </tr> 
                <tr> 
		<th  align="LEFT"  width="' . $width . '%">Hazmat Code</th>  
		<th  align="LEFT" width="' . $width2 . '%">: </th>
                </tr> 
           				              
				<tr> 
		<th  align="LEFT" width="' . $width . '%" >Container Numbers</th>    
		<th  align="LEFT" width="' . $width2 . '%">:  ' . $ContainerNumber . '</th>
				</tr>   

				<tr> 
		<th  align="LEFT" width="' . $width . '%" >Seal Numbers</th>    
		<th  align="LEFT" width="' . $width2 . '%">:  ' . $SealNo . '</th>
				</tr>   

				<tr> 				
		<th  align="LEFT" width="' . $width . '%" >Date of departure from Foreign Port</th>    
		<th  align="LEFT" width="' . $width2 . '%">:' . $LastForeignPort . ' </th>
				</tr>   

				
				<tr> 
				  
        <th  align="LEFT" width="100%" ><BR><BR><BR><BR>Please supplement our data before submission, wherever necessary, and advise us for future shipments.<BR><BR><BR>
		Best Regards<BR>
		' . $ExporterData['ExpName'] . '</th>    
		
				</tr>
				
				
			  ';


	$content .= '</table>';


	return $content;
}
