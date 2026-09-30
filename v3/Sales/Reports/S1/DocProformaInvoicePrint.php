<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include($_SERVER['DOCUMENT_ROOT'] . "/Libraries/tcpdf/tcpdf.php");
include($cp . "Includes/IncludePage.php");
include_once ROOT . 'config/api-call.php';

/** Folder Includes **/

$ID = $_GET['id'];
$Type = $_GET['Type'];
$PoID = $_GET['id'];
$DocType = 'PI';
//echo $_SESSION["SignURL"]; exit;

$SqlStatus = "SELECT * FROM ScrmPIData  WHERE PoID='$PoID'";
$StmtStatus = $iconn->query($SqlStatus);
$PoStatus = $StmtStatus->fetch_assoc();

$SqlScrmPoData = "SELECT *,SPD.NotifyPartyID AS NPID,SCDB.BuyerName as BuyerName,SCDB.BuyerAddress as BuyerAddress,
SCD.ConsigneeName as ConsigneeName,
	SCD.ConsigneeName as ConsigneeName,
	 COALESCE(
        (
            SELECT Address 
            FROM ScrmConsigneeAddressHistory AS SCAH
            WHERE SCAH.ConID = SCD.ConID AND SCAH.EffectiveFrom <= SPD.PoDate
            ORDER BY SCAH.EffectiveFrom DESC
            LIMIT 1
        ),
        SCD.ConsigneeAddress
    ) AS ConsigneeAddress,
SPR.name as PlaceofReceiptName,
SC.name as PortofDischargeCountry ,
SPC.CountryCode as CountryCode ,SPCF.CountryCode as FinalDestinationCountryCode ,SPC.Name as PortOfDischarge,SPCF.Name as FinalDestination,SCF.name as FinalDestinationCountry,
SOC.CerificationName as CerificationName, SCFD.name AS FinalDestinationCountryName,SPD.Remarks
FROM ScrmPIData as SPD 
left JOIN ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID
left JOIN ScrmBuyerDetails as SCDB on SPD.BuyerID=SCDB.BuyerID
left JOIN ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
left JOIN ScrmNotifyparty as SNPT on SPD.NotifyPartyID=SNPT.NotifyPartyID
left JOIN ScrmPortCities as SPC on SPD.PortOfDischarge=SPC.PortId
left JOIN ScrmPortCities as SPCF on SPD.FinalDestination=SPCF.PortId
left JOIN ScrmPortCities as SPR on SPD.PlaceofReceipt=SPR.PortId
left JOIN ScrmCountriesList as SCF on SCF.id=SPCF.Country
LEFT JOIN ScrmCountriesList AS SCFD ON SCFD.id = SPD.FinalDestinationCountry
left JOIN ScrmPaymentTerms as SPT on SPD.PaymentTerms=SPT.ID
left JOIN ScrmImporterOfRecord as SIR on SIR.ImporterID=SPD.ImporterID
left JOIN ScrmCountriesList as SC on SC.id=SPC.Country
left JOIN ScrmOrderCertification as SOC on SOC.CerificationID =SPD.Certification
WHERE PoID='$PoID'";

//  echo $SqlScrmPoData;
//  exit;

$StmtPoData = $iconn->query($SqlScrmPoData);
$PoDataRow = $StmtPoData->fetch_assoc();
$ExpID = $PoDataRow['ExporterID'];
$ProcessorID = $PoDataRow['ProcessorID'];
$ApprovedBy = $PoDataRow['ApprovedBy'];
$CountryID = $PoDataRow['DestinationCountry'];
$BuyerID = $PoDataRow['ConsigneeID'];
$LoginId = $PoDataRow['LoginId'];
$Certification = $PoDataRow['CerificationName'];
$InvDate = $PoDataRow['InvDate'];
$DestinationCountryId = $PoDataRow['DestinationCountry'];

/***Api call to get signature */
$api = API_DIGITAL_SIGNATURE;

// $bodyContent = array("ApprovedByID" => $ApprovedBy);
$bodyContent = array("ApprovedByID" => $ApprovedBy, "LoginID" => $LoginId, "Type" => "Sign");
$resgetDigitalSignature = CallAPI($api, "GET", "", $bodyContent);

$result = $resgetDigitalSignature->Data;
//var_dump($result); exit;

$SignURL = $result->SignURL;

/***Api call to get signature */


/***Api call to get prepared by */
$api = API_DIGITAL_SIGNATURE;

// $bodyContent = array("ApprovedByID" => $ApprovedBy);
$bodyContent = array("ApprovedByID" => $ApprovedBy, "LoginID" => $LoginId, "Type" => "Preparedby");
$resgetPreparedby = CallAPI($api, "GET", "", $bodyContent);

$result = $resgetPreparedby->Data;
//var_dump($result); exit;

$Preparedby = $result->Preparedby;


//$Preparedby=getUserDetails($LoginId);
/***Api call to get prepared by */




// $SqlLogin="SELECT * FROM AdminLoginDetails  WHERE UserID='$ApprovedBy'";
// $StmtLogin=$iconn->query($SqlLogin); 
// $RowLogin = $StmtLogin->fetch_assoc();
// $SignURL=$RowLogin['SignURL'];


// $ExporterData=getExporterDetails($ExpID,$PoID,$CountryID,$DocType);
// $ProcessorData=getExporterDetails($ProcessorID,$PoID,$CountryID,$DocType);

$ExporterData = getExporterDetails($ExpID, $CountryID, $DocType);
$ProcessorData = getExporterDetails($ProcessorID, $CountryID, $DocType);
// echo json_encode($ExporterData, JSON_PRETTY_PRINT);
// echo json_encode($ProcessorData, JSON_PRETTY_PRINT);
//$ProcessorData=getExporterDetails($ProcessorID);
//print_r($ProcessorData);

$PaymentTerms = $PoDataRow['PaymentTerms'];
$DeliveryTerms = $PoDataRow['DeliveryTerms'];
$BankID = $PoDataRow['BankID'];
$Status = $PoStatus['Status'];

//ROBO
if ($PoDataRo['NPID'] != 0) {
	$showNotifyParty = true;
} else {
	$showNotifyParty = false;
}


// $showNotifyParty;


//ROBO

//require_once('../tcpdf/tcpdf.php'); 


$CanadaLbsFactor = $DestinationCountryId == 23 && $InvDate >= '2026-08-27' ? 2.202643171806167 : 'LbsFactor';

class MYPDF extends TCPDF
{
	public function Header()
	{
		global $Status;

		if ($Status == 0) {
			$StatusTerm = "(Approval Pending)";
		} else if ($Status == 2) {
			$StatusTerm = "(cancelled)";
		} else if ($Status == 4) {
			$StatusTerm = "(Rejected)";
		}
		global $Header;
		$image_file = 'images/ds_logo.jpg';
		$this->SetFont('helvetica', 'B', 8);
		$this->Cell(110, 0, 'PROFORMA INVOICE ' . $Header, 0, 0, 'R', 0, '', 0, false, 'T', 'M');
		$this->SetTextColor(255, 0, 0);
		$this->SetFont('helvetica', '', 8);
		$this->Cell(10, 0, '' . $StatusTerm, 0, 1, 'L', 0, '', 0, false, 'T', 'M');
	}

	public function Footer()
	{
		$this->SetFont('helvetica', 'I', 8);
		$this->Cell(0, 10, 'Page ' . $this->getAliasNumPage() . '/' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
		$this->SetY(-5);
	}
}



$pdf = new MYPDF('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);



$pdf->SetCreator(PDF_CREATOR);
$pdf->SetTitle('Proforma Invoice: ' . $Header);
$pdf->SetHeaderData('', '', '', '');
$pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
$pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
$pdf->SetDefaultMonospacedFont('helvetica');
$pdf->SetFooterMargin(10);
$pdf->SetHeaderMargin(5);
$pdf->SetMargins(10, 10, 10, 5);
$pdf->setPrintHeader(true);
$pdf->setPrintFooter(true);

$pdf->SetAutoPageBreak(TRUE, 5);
$pdf->SetFont('helvetica', '', 11);
$pdf->AddPage();



$table_data = getDS_TABLE($iconn, $ID);
$pdf->writeHTML($table_data);
$pdf->SetFont('helvetica', '', 6);
/***uma 28-07-2022**/
$CurrentMonth = date('n', strtotime($PoDataRow['InvDate']));
if ($PoDataRow['ExporterID'] == 5) {
	if ($CurrentMonth <= 3) {


		$NxtYear = date('y', strtotime($PoDataRow['InvDate']));
		$CurrentYear = date('y', strtotime($PoDataRow['InvDate'])) - 1;
	} else {

		$CurrentYear = date('y', strtotime($PoDataRow['InvDate']));
		$NxtYear = date('y', strtotime($PoDataRow['InvDate'])) + 1;
	}
} else {
	if ($CurrentMonth <= 3) {



		$NxtYear = date('y', strtotime($PoDataRow['InvDate']));
		$CurrentYear = date('Y', strtotime($PoDataRow['InvDate'])) - 1;
	} else {

		$CurrentYear = date('Y', strtotime($PoDataRow['InvDate']));
		$NxtYear = date('y', strtotime($PoDataRow['InvDate'])) + 1;
	}
}
/***uma 28-07-2022**/

$file_name = $ExporterData['InvShortCode'] . '-PI-' . $PoDataRow['InvNo'] . '-' . $CurrentYear . '-' . $NxtYear . '.pdf';
$file_name = str_replace('/', '', $file_name);;
$file_name = $Fname . $file_name;

//sme/pi/233/2022-23


if (ob_get_level()) {
	ob_end_clean();
}

if ($Type == '') {
	$Type = 'I';
} else {
	if (!is_dir($MailTempFolder)) {
		mkdir($MailTempFolder, 0755, true);
	}

	$Fname = $MailTempFolder; //From Config 
	$file_name = $_GET['FileName'];
	$file_name = str_replace('/', '', $file_name);
	$file_name = $Fname . $file_name;
}

if (ob_get_level()) {
	ob_end_clean();
}

$pdf->Output($file_name, $Type);


function getDS_TABLE($iconn, $ID)
{

	global $PoDataRow;

	$otherTaxMultiplier = 1;

	// echo print_r($PoDataRow);
	// exit;

	include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");




	$F_NAME = '';
	$T_O_MCS = '';
	$DOE = '';
	$DOS = '';
	$TOTAL_MCS = 0;
	$TOTAL_SLAB = 0;
	$T_EACH_SLAB = 0;
	$TOTAL_NET_WT_KGS = 0;
	$TOTAL_NET_WT_LBS = 0;


	//uma

	$SQL_PO_GRADE_DATA = "SELECT * FROM `ScrmPoGradeData` where InvId='$ID'  ";
	$STMT_PO_GRADE_DATA = $iconn->query($SQL_PO_GRADE_DATA);

	while ($PO_GRADE_DATA = $STMT_PO_GRADE_DATA->fetch_assoc()) {

		$SLAB = $PO_GRADE_DATA['SLAB'];
		$TOTAL_SLAB += $SLAB;

		$EACH_SLAB = $PO_GRADE_DATA['EACH_SLAB'];
		$T_EACH_SLAB += $EACH_SLAB;

		//$MCS= $PO_GRADE_DATA['Mcs']; 
		$MCS = $PO_GRADE_DATA['PIMcs']; //uma 
		$TOTAL_MCS += $MCS;

		$UOM = $PO_GRADE_DATA['UOM'];
		/* $BAP= $PO_GRADE_DATA['BAP']; 
																																																																																										if($BAP=='NA'){
																																																																																										$BAP=''; 
																																																																																											}
																																																																																										ELSE{
																																																																																											$BAP=$BAP; 
																																																																																										}*/

		if ($UOM == "LBS") {
			$NET_WT_LBS = (($SLAB * ($EACH_SLAB)) * $MCS) . "LBS";
			// $NET_WT_KGS=(($SLAB*($EACH_SLAB*453))*$MCS)/1000 ."KGS"; 
			$NET_WT_KGS = (($SLAB * ($EACH_SLAB)) * $MCS) / 2.2 . "KGS";
		} else if ($UOM == "KGS") {
			$NET_WT_KGS = (($SLAB * ($EACH_SLAB)) * $MCS) . "KGS";


			$NET_WT_LBS = (($SLAB * ($EACH_SLAB * 2.2)) * $MCS) . "LBS";
		}
		$TOTAL_NET_WT_KGS = $NET_WT_KGS + $NET_WT_KGS;
		$TOTAL_NET_WT_LBS = $NET_WT_LBS + $NET_WT_LBS;
	}
	$TOTAL_NET_WT_KGS;
	$TOTAL_NET_WT_LBS;
	$TOTAL_MCS;

	$SHIPMENT_DATE = date('d-m-Y', strtotime($PoDataRow['ShipByDate']));








	global $PoDataRow, $ExpID;
	global $ExporterData;
	global $ProcessorData;
	global $TblAdminBankDetails;
	global $BankID;
	global $TblScrmPaymentTerms;
	global $TblAdminExporterDetails;
	global $SignURL, $ExpID, $Preparedby;
	global $CanadaLbsFactor;

	// echo $Preparedby; exit;

	$CountryName = $PoDataRow['name'];


	//Exporter /Shipper Details

	//$ExporterID=$ExporterData['ExpID'];
	$ExporterName = $ExporterData['ExpName'];
	$ExporterAddress = $ExporterData['ExpAddress'];
	$ExporterUsfdaNo = $ExporterData['ExpUsfdaNo'];
	$ExporterApprovalNo = $ExporterData['ExpApprovalNo'];
	$ExporterIeCustomsNo = $ExporterData['ExpIeCustomsNo'];
	$ExporterGstNo = $ExporterData['ExpGstNo'];
	$ExporterIeCode = $ExporterData['ExpIeCode'];
	$AuthSign = $ExporterData['AuthSign'];
	$ExporterRecAdd = $PoDataRow['ImporterAddress'];
	$ImporterName = $PoDataRow['ImporterName'];
	$ImporterCode = $PoDataRow['ImporterCode'];
	$InvShortCode = $ExporterData['InvShortCode'];
	//Processor/Packer

	$ProcessorID = $PoDataRow['ProcessorID'];
	$ExporterID = $PoDataRow['ExporterID'];
	$ProcessorNamee = $ProcessorData['ExpName'];
	$ProcessorIeCustomsNo = $ProcessorData['ExpIeCustomsNo'];
	$DestinationCountryId = $PoDataRow['DestinationCountry'];

	if ($ProcessorID == 3) {
		$SqlProcessor = "select substring_index ('$ProcessorNamee',' ',4) AS STRING  ";
		$StmtProcessor = $iconn->query($SqlProcessor);
		$RowProcessor = $StmtProcessor->fetch_assoc();
		$ProcessorName = $RowProcessor['STRING'];

		$SqlProcessor1 = "select substring_index ('$ProcessorNamee',' ',-2) AS STRING  ";
		$StmtProcessor1 = $iconn->query($SqlProcessor1);
		$RowProcessor1 = $StmtProcessor1->fetch_assoc();
		// $ProcessorName1=$RowProcessor1['STRING']; 
	} else {
		$ProcessorName = $ProcessorNamee;
	}
	// $InvDate=date('m',strtotime($PoDataRow['InvDate'])); 

	// $CurrentMonth = strftime("%m"); 
	$CurrentMonth = date('n', strtotime($PoDataRow['InvDate']));
	if ($PoDataRow['ExporterID'] == 5) {
		if ($CurrentMonth <= 3) {

			/*$NxtYear = strftime("%y"); 
																																																																																																																																						  $CurrentYear = strftime("%y")-1; */
			$NxtYear = date('y', strtotime($PoDataRow['InvDate']));
			$CurrentYear = date('y', strtotime($PoDataRow['InvDate'])) - 1;
		} else {
			/* $CurrentYear = strftime("%Y"); 
																																																																																																																																						  $NxtYear = strftime("%y")+1; */
			$CurrentYear = date('y', strtotime($PoDataRow['InvDate']));
			$NxtYear = date('y', strtotime($PoDataRow['InvDate'])) + 1;
		}
	} else {
		if ($CurrentMonth <= 3) {

			/*$NxtYear = strftime("%y"); 
																																																																																																																																						  $CurrentYear = strftime("%Y")-1; */

			$NxtYear = date('y', strtotime($PoDataRow['InvDate']));
			$CurrentYear = date('Y', strtotime($PoDataRow['InvDate'])) - 1;
		} else {
			/*$CurrentYear = strftime("%Y"); 
																																																																																																																																						  $NxtYear = strftime("%y")+1; */
			$CurrentYear = date('Y', strtotime($PoDataRow['InvDate']));
			$NxtYear = date('y', strtotime($PoDataRow['InvDate'])) + 1;
		}
	}

	$ProcessorName = $ProcessorData['ExpName'];
	$ProcessorAddress = $ProcessorData['ExpAddress'];
	//$ProcessorAddress=$ProcessorData['PackerAddress'];
	$ProcessorUsfdaNo = $ProcessorData['ExpUsfdaNo'];
	$ProcessorApprovalNo = $ProcessorData['ExpApprovalNo'];
	$ProcessorFEI = $ProcessorData['ExpFeiNo'];

	//$InvNO='SME/PI/'.$PoDataRow['InvNo'].'/2021-22';


	/*if($ExpID==5){
																																													  $InvNO=$InvShortCode.$PoDataRow['InvNo'].'/'.$CurrentYear.'-'.$NxtYear;
																																													  }*/
	//else{
	// $InvNO=$InvShortCode.'PI/'.$PoDataRow['InvNo'].'/'.$CurrentYear.'-'.$NxtYear;
	//$InvNO=getPIInvoiceNumber($iconn,$ExpID,$PoDataRow['PoID']);
	$InvNO = $PoDataRow['InvNumFull'];

	//}
	$InvDate = getFormatedDate($PoDataRow['InvDate']);

	$PoNo = $PoDataRow['PoNo'];
	$PoDate = getFormatedDate($PoDataRow['PoDate']);
	$PoDate = "& Dated :" . $PoDate;
	if ($PoNo == '') {

		$PoDate = ' ';
	}


	$ConsigneeName = $PoDataRow['ConsigneeName'];
	$ConsigneeAddress = $PoDataRow['ConsigneeAddress'];
	/*09/08/2022*/
	$BuyerName = $PoDataRow['BuyerName'];
	$BuyerAddress = $PoDataRow['BuyerAddress'];
	$ConsigneeID = $PoDataRow['ConsigneeID'];
	$BuyerID = $PoDataRow['BuyerID'];
	/*09/08/2022*/

	$PlaceofReceipt = $PoDataRow['PlaceofReceiptName'] != '' ? $PoDataRow['PlaceofReceiptName'] : 'ANY PORT, INDIA';

	$PortOfDischarge = $PoDataRow['PortOfDischarge'];
	$PortofDischargeCountry = $PoDataRow['PortofDischargeCountry'];
	$country = !empty($PoDataRow['FinalDestinationCountryName'])
		? $PoDataRow['FinalDestinationCountryName']
		: $PoDataRow['FinalDestinationCountry'];
	$FinalDestination = $PoDataRow['FinalDestination'] . ' ' . $PoDataRow['FinalDestinationCountryCode'] . ', ' . $country;
	$DestinationCountry = $PoDataRow['name'];
	$ByPayment = $PoDataRow['ByPayment'];
	// $PreparedBy=$PoDataRow['PreparedBy'];
	$PaymentTerms = $PoDataRow['PaymentName'];
	$DeliveryTerms = $PoDataRow['DeliveryTerms'];
	$ShippingTerms = $PoDataRow['ShippingTerms'];
	$QtyUnit = $PoDataRow['QtyUnit'];


	$NotifyPartyName = $PoDataRow['NotifyPartyName'];
	$NPartyAddress = $PoDataRow['NPartyAddress'];
	$NPartyContactNumber = $PoDataRow['NPartyContactNumber'];



	$content = '';


	$size = 45;
	$size1 = 55;



	$StyleTbl = 'Style="border: 1px solid #ccc;
  color: #37393b;
  margin: 10px 0 0 0;
  text-shadow: #fff 1px -1px 1px;
  text-align: left;
  width: 100%;"';

	$StyleTd = 'Style=" margin: 0;
  padding: 0;
  border: 0;
  font-size: 100%;
  vertical-align: baseline;
  background: transparent;"';

	$content .= '<table border="1" cellspacing="0" cellpadding="0">  
				
				 <tr>  
				<th width="45%">
				<table border="1" cellspacing="0" cellpadding="2">';

	if ($PoDataRow['ExporterID'] == 2) {
		$content .= '<tr>
           		
				<th  align="LEFT" ><font size="7"><b>Exporter / Shipper:</b>
				<br>' . $ExporterName . ' 
				<BR> ' . nl2br($ExporterAddress) . '<BR>IE CODE  : ' . $ExporterIeCode . '<BR>GST NO  : ' . $ExporterGstNo . '
				</font></th>
		</tr>';
	} else {
		$content .= '<tr> 
           		
				<th  align="LEFT" ><font size="7"><b>Exporter / Shipper:</b>
				<br>' . $ExporterName . ' 
				<BR> ' . nl2br($ExporterAddress) . '<BR>';
		if ($DestinationCountryId != '157' || $PoDataRow['ExporterID'] != 2) {

			$content .= 'IE CODE  : ' . $ExporterIeCode . '<BR>GST NO  : ' . $ExporterGstNo . '</font></th></tr>';
		} else {
			$content .= 'USFDA REGISTRATION NO  : ' . $ExporterUsfdaNo . '<BR>IE CODE  : ' . $ExporterIeCode . '<BR>GST NO  : ' . $ExporterGstNo . '</font></th>
				</tr>';
		}
	}
	$BuyerLabelText = "Applicant";
	$consineeLabelText = '';
	if ($_SESSION['ClientID'] == 'SMEV3') {
		$BuyerLabelSql = "SELECT InvDate FROM ScrmPIData WHERE PoID=$ID";
		$StmtBuyerLabel = $iconn->query($BuyerLabelSql);
		$RowBuyerLabel = $StmtBuyerLabel->fetch_assoc();
		$InvDate1 = $RowBuyerLabel['InvDate'];
		if (!empty($InvDate1) && $InvDate1 >= '2026-04-01') {
			$BuyerLabelText = "Buyer";
			$consineeLabelText = 'Ultimate ';
		}
	}
	if ($BuyerID == '' || $BuyerID == 0) { {
			$content .= '		<tr>  
						   
						<th  align="LEFT" ><font size="7"><b><u>' . $BuyerLabelText . '/Ultimate Consignee</u></b>
						<br>' . $ConsigneeName . ',<br>' . $ConsigneeAddress . '
						
						
						</font></th>
						</tr>';
		}
	} else if ($ConsigneeID != $BuyerID) {
		$content .= '		<tr>  
           		
				<th  align="LEFT" ><font size="7"><b><u>' . $BuyerLabelText . '</u></b>
				<br>' . $BuyerName . ',<br>' . $BuyerAddress . '
				<br><b><u>' . $consineeLabelText . 'Consignee</u></b><br>
				' . $ConsigneeName . ',<br>' . $ConsigneeAddress . '
				
				
				</font></th>
				</tr>';
	} else {
		$content .= '		<tr>  
           		
				<th  align="LEFT" ><font size="7"><b><u>' . $BuyerLabelText . '/Ultimate Consignee</u></b>
				<br>' . $ConsigneeName . ',<br>' . $ConsigneeAddress . '
				
				
				</font></th>
				</tr>';
	}

	//ECHO 'HII'; $PoDataRo['NPID']; EXIT;
	if ($PoDataRow['NPID'] != 0) {
		$content .= '		<tr>  
           		
				<th  align="LEFT" ><font size="7"><b> <u>Notify Party</u></b>
				<br>' . $NotifyPartyName . ',<br>' . nl2br($NPartyAddress) . '
				<!--<br>TEL:' . $NPartyContactNumber . '-->
				
				
				</font></th>
				</tr>';
	}

	$content .= '	<tr>  
           		
				<th  align="LEFT" width="50%" ><font size="7">Pre-Carriage by
				<br  align="center">REFER CONTAINER</font></th>
				<th  align="LEFT" width="50%" ><font size="7">Place of Receipt by
				<br align="center">' . $PlaceofReceipt . '</font></th>
				
				</tr>
				<tr>  
           		
				<th  align="LEFT" width="50%" ><font size="7">Vessel / Freight No.  <BR>' . $VESSEL_FREIGHT_NO . '  
				<br></font></th>
				<th  align="LEFT" width="50%" ><font size="7">Port of Loading
				<br align="center">ANY PORT, INDIA</font></th>
				
				</tr>
				
				<tr>  
           		
				<th  align="LEFT" width="50%" ><font size="7">Port of Discharge
				<br align="center">' . strtoupper($PortOfDischarge) . ' ' . strtoupper($PoDataRow['CountryCode']) . ', ' . $PortofDischargeCountry . '</font></th>
				<th  align="LEFT" width="50%" ><font size="7">Final Destination
				<br align="center">' . strtoupper($FinalDestination) . '</font></th>
				
				</tr>
				</table>
				</th>
				
				
				<th width="55%">
				<table border="1" cellspacing="0" cellpadding="1">
				
				<tr > 
				<th  align="LEFT"  ><font size="7" ><b> Invoice No. & Date.</b>
				<br> ' . $InvNO . ',Dated ' . $InvDate . ' </font></th>
				</tr>
				
				<tr> 
				<th  align="LEFT" ><font size="7"><b> Buyer`s Order No. & Date. </b>
				<br> ' . $PoNo . $PoDate . ' </font></th>
				</tr>';
	if ($ProcessorID == 3) {
		$content .= '		<tr > 
				<th  align="LEFT"  ><font size="7" ><b> Other Refrence(s) :
				<b style="color:white">_________________________</b>  E.I.A. APPROVAL# ' . $ProcessorApprovalNo . '</b>
				<br>
				<u><b> Packer</u> :</b><br> ' . $ProcessorName . '' . $ProcessorName1 . '<br> ' . $ProcessorAddress . ' ';
		if ($DestinationCountryId == '157') {
			$content .= '
				
				<br> USFDA REGISTRATION NO  : ' . $ProcessorUsfdaNo . '<BR> FEI # :  ' . $ProcessorFEI . '';
		}
		$content .= '</font></th>
			</tr>';
	} else {
		$content .= '		<tr > 
				<th  align="LEFT"  ><font size="7" ><b> Other Refrence(s) :
				<b style="color:white">_________________________</b>  E.I.A. APPROVAL# ' . $ProcessorApprovalNo . '</b>
				<br>
				<u><b> Packer</u> :</b><br> ' . $ProcessorName . '<br> ' . $ProcessorAddress . '  
				';
		if ($DestinationCountryId == '157') {
			$content .= '
				
				<br> USFDA REGISTRATION NO  : ' . $ProcessorUsfdaNo . '<BR> FEI # :  ' . $ProcessorFEI . '';
		}
		$content .= '</font></th>
			</tr>';
	}



	if ($PoDataRow['ShippingTerms'] == 'DDP') {

		$content .= '	<tr> 
           		
                <th  align="left" width="100%"><font size="7"> Buyer (If other than Consignee) :<br>
				<b><u> Importer of Record:</u></b> <br> CUSTOMS ASSIGN # ' . $ImporterCode . '
				<br> ' . $ImporterName . '
				<br> ' . $ExporterRecAdd . ' </font></th>
				</tr>';
	} else {

		$content .= '	<tr> 
           		
                <th  align="left" width="100%"><font size="7"> Buyer (If other than Consignee) :<br></font></th>
				</tr>';
	}

	$content .= '	<tr> 
				<th border="1"  align="left" height="50%" width="50%"><font size="7"> Country of Origin of Goods 	
				<br><b align="center"> INDIA</b></font></th>
                <th border="1"  align="left" width="50%"><font size="7"> Country of Final Destination  
				<br><b align="center">  ' . $DestinationCountry . '</b></font></th>
				</tr>
				<tr> 
				<th  align="LEFT"  width="100%" ><font size="7"><b><u> Terms of Delivery and Payment:</u>
				</b><br><BR> ' . $PaymentTerms . ' <br>
				 SHIPMENT TERM : ' . $ShippingTerms . ' ' . strtoupper($PortOfDischarge) . ', ' .
		strtoupper($DestinationCountry) . ' ';
	if ($DestinationCountryId != 26) {


		$content .= '(' . $DeliveryTerms . ')<br>';
	}

	$content .= '</font> </th>
				</tr>
			
				
				</table>
				</th>
				
				</tr> 
				
				
				
				
				
				
				<tr> 
				<th width="100%" >
				<table border="0" cellspacing="0" cellpadding="2">	
<tr> 				
           		<th  align="left"  width="12.5%"style="border-right: 1px solid black;"><font size="7">Shipping Marks: <br><br><BR align="center"> NO MARKS</font></th>
                <th  align="LEFT" width="10%" style="border-right: 1px solid black;"><font size="7">No. & kind of packings <br><BR><b> ' . $TOTAL_MCS . '</b> MCS</font></th>
                <th  align="LEFT" width="22.5%" style="border-right: 1px solid black;"><font size="7">Container Number <br><br><br><b align="center">1 X 40 FCL</b></font></th>
                <th  align="LEFT" width="13.5%" style="border-right: 1px solid black;"><font size="7">Seal Number <br><br><br><b align="center">' . $SEAL_NO . '</b></font></th>
                <th  align="center" width="13.5%" style="border-right: 1px solid black;border-bottom: none">
				<font size="7">Quantity in ' . $QtyUnit . ' </font></th>
                <th  align="center" width="13.5%" style="border-right: 1px solid black;border-bottom: none"><font size="7">Unit Price <br>USD/' . $QtyUnit . ' ' . $PoDataRow['ShippingTerms'] . ' <br><b>' . strtoupper($PortOfDischarge) . ', ' .
		strtoupper($DestinationCountry) . '</b></font></th>
                <th  align="center" width="14.5%" style="border-right: 1px solid black;border-bottom: none"><font size="7">Amount in U.S.D ' . $PoDataRow['ShippingTerms'] . '<br><b> ' . strtoupper($PortOfDischarge) . ', ' .
		strtoupper($DestinationCountry) . '</b></font></th>
                </tr>
				
				</table>
				</th>
				</tr>
				
				';


	$description_head = '<B><u>Description of Goods: </u></B><BR>1 X 40 FCL <BR>';
	$W_SPACE = '<b style="color:white"> yuhjutgfrtgujkiolmnbvgftrkliomkjhygtfrdeswderfgtyhjmnkih</b>';
	$Whitspace = '<b style="color:white"> yuhju</b>';
	$FontSize = "7";
	//$TOTAL_MCS=0;
	$content .= '<tr  >
				<th width="100%" >
			<table border="0" cellspacing="0" cellpadding="1">
			
			';


	// $SqlVarietyMcs="
	// SELECT sum(McS) As VarietyMcs,PrintStatus,Variety,Grade,PackingStyle,Brand,PackingStyleID,BAP
	// FROM `ScrmPoGradeData` as PGD
	// left join `AdminPackingStyles` as Aps on PackingStyleID=Aps.PCKID
	// left join `ScrmBrandsMaster` SBM on  SBM.BrandName=PGD.Brand
	//  WHERE InvId='$ID' GROUP by Variety,PackingStyleID,Brand"; 

	$SqlVarietyMcs = "
			SELECT sum(PIMcs) As VarietyMcs,BuyerItemCode,PrintStatus,Description as Description,Variety,Grade,PackingStyle,Brand,PackingStyleID,BAP
			FROM `ScrmPoGradeData` as PGD
			left join `AdminPackingStyles` as Aps on PackingStyleID=Aps.PCKID
			left join `ScrmBrandsMaster` SBM on  SBM.BrandName=PGD.Brand
			 WHERE InvId='$ID' GROUP by Variety,PackingStyleID,Brand";
	$StmtVarietyMcs = $iconn->query($SqlVarietyMcs);
	while ($VarietyMcsRow = $StmtVarietyMcs->fetch_assoc()) {


		$Variety = $VarietyMcsRow['Variety'];
		$Grade = $VarietyMcsRow['Grade'];
		$Brand = $VarietyMcsRow['Brand'];
		$BAP = $VarietyMcsRow['BAP'];
		$PackingStyleID = $VarietyMcsRow['PackingStyleID'];
		$BrandPrintStatus = $VarietyMcsRow['PrintStatus'];
		$BreakUpDescription = $VarietyMcsRow['Description'];
		$BuyerItemCode = $VarietyMcsRow['BuyerItemCode'];


		if ($BrandPrintStatus) {
			$BrandName = '<B> BRAND </B>: ' . $VarietyMcsRow['Brand'] . ' ';
		} else {
			$BrandName = '';
		}


		if ($BAP == 'NA') {
			$BAP = '';
		} else {
			$BAP = $BAP;
		}

		// if($BreakUpDescription!=''){
		// $Description= $VarietyMcsRow['VarietyMcs'].' CARTONS OF '.$BreakUpDescription.'<br>';
		// }


		// else{
		// $Description=$VarietyMcsRow['VarietyMcs'].' CARTONS OF '.$VarietyMcsRow['Variety'].'<br>';
		// }
		$Description = $VarietyMcsRow['VarietyMcs'] . ' CARTONS OF ' . $VarietyMcsRow['Variety'];

		if ($PoDataRow['CerificationName']) {
			$Description .= ' (' . $PoDataRow['CerificationName'] . ')';
		}
		$Description .= '<br>';
		if ($DestinationCountryId != 26) {
			$Description .= 'PACKING : ' . $VarietyMcsRow['PackingStyle'] . ' NET WEIGHT PER CARTON.' . $Whitspace . $BrandName . ' <B><br>' . $BAP . '</B>';
		} else {
			$Description .= '<b>PACKING : ' . $VarietyMcsRow['PackingStyle'] . $Whitspace . '</b>';
		}


		$content .= '
			<tr>
			<td width="58.5%" style="border-right: 1px solid black;" ><font size="' . $FontSize . '" >'
			. nl2br($Description) . '</font></td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '"></font></td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '"></font></td>
			</tr>
			

			
			<tr>';
		if ($BuyerItemCode) {
			$content .= '<th align="center" width="10%" ><font size="' . $FontSize . '"> <u>ITEM NUMBER</u></font></th>';
		} else {
			$content .= '<th align="center" width="10%" ><font size="' . $FontSize . '"> </font></th>';
		}

		$content .= '<th align="center" width="30%" ><font size="' . $FontSize . '"> <u>SIZE</u></font></th>
			<th align="center" width="18.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '"><u>QTY IN CS</u></font></th>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '"></font></td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '"></font></td>
			</tr>';

		//UMA
		$SqlVariety = "SELECT * FROM `ScrmPoGradeData` as PGD
			left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE 
			InvId='$ID' and Variety='$Variety' and Brand='$Brand' 
		and	PackingStyleID='$PackingStyleID' order by abs(Grade)";

		// AND Grade='$Grade'
		$StmtVariety = $iconn->query($SqlVariety);
		$paymentValues = getExporterTaxRates($iconn, $ExporterID);
		$OtherTax = $paymentValues['OtherTax'];

		// $otherTaxMultiplier = 1;
		if ($PoDataRow['OtherCharges'] && $OtherTax > 0) {
			$otherTaxMultiplier += $OtherTax / 100; // e.g. 10% becomes 1.10
			$otherTaxMultiplier = round($otherTaxMultiplier, 3);
		}


		while ($RowVariety = $StmtVariety->fetch_assoc()) {
			$RowPrice = round($RowVariety['Price'] * $otherTaxMultiplier, 3);


			// 26 ID China
			// if ($PoDataRow['QtyUnit'] == 'KGS') {
			// 	$Qty = $RowVariety['PIMcs'] * $RowVariety['TotalSlabs'] * $RowVariety['SlabWeight'] * $RowVariety['MFactor'];
			// 	$UsdValue = ($Qty) * $RowPrice;
			// } else {
			// 	$Qty = $RowVariety['PIMcs'] * $RowVariety['TotalSlabs'] * $RowVariety['SlabWeight'] * $RowVariety['LbsFactor'];
			// 	$UsdValue = ($Qty) * $RowPrice;
			// }

			$LbsFactor = is_double($CanadaLbsFactor) && $RowVariety['Uom'] == 'GRAMS' ? $CanadaLbsFactor / 1000 : $RowVariety['LbsFactor'];
			$Qty = $RowVariety['PIMcs'] * $RowVariety['TotalSlabs'] * $RowVariety['SlabWeight'];
			switch ($PoDataRow['QtyUnit']) {
				case 'KGS':
					$Qty *= $RowVariety['MFactor'];
					break;
				case 'LBS':
					$Qty *= $LbsFactor;
					break;
			}

			$UsdValue = ($Qty) * $RowPrice;

			// '.$RowVariety['Treatment'].'
			$content .= '
				<tr>
				<!--	<td align="center" width="12.5%" ><font size="7">
					' . $RowVariety['Variety'] . '</font></td> -->
				';
			if ($BuyerItemCode) {
				$content .= '<td align="center" width="10%"  ><font size="' . $FontSize . '">
					<b>' . $RowVariety['BuyerItemCode'] . '</b></font></td>';
			} else {
				$content .= '<td align="center" width="10%"  ><font size="' . $FontSize . '"></font></td>';
			}

			$content .= '<td align="center" width="30%"  ><font size="' . $FontSize . '">
				' . $RowVariety['SKUNo'] . ' <b>' . $RowVariety['Grade'] . '</b></font></td>
				<!--<td align="center" width="22.5%"><font size="7">
				' . $RowVariety['PackingStyle'] . '</font></td> -->
				<td align="center" width="18.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '">
				' . $RowVariety['PIMcs'] . '</font></td>
				
				<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '">
				' . number_format($Qty, 3) . '</font></td>
				
				<td align="center" width="13.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '">
				' . number_format($RowPrice, 3) . '</font></td>
				
				<td align="center" width="14.5%" style="border-right: 1px solid black;"><font size="' . $FontSize . '">
				' . number_format($UsdValue, 2) . '</font></td>
				
				</tr>';
		}
	} //UMA

	// $sqlExt = "sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
	// 			sum(Mcs*TotalSlabs*GrossWeight*MFactor) as GrossWeightKgs,
	// 			sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
	// 			sum(Mcs*TotalSlabs*GrossWeight*LbsFactor) as GrossWeightLbs";

	$CanadaLbsFactor = is_double($CanadaLbsFactor) ? $CanadaLbsFactor : 'LbsFactor*1000';

	$sqlExt = "sum(PIMcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
			sum(PIMcs*GrossWeight*MFactor) as GrossWeightKgs,
			sum(CASE WHEN PS.Uom = 'GRAMS' THEN
				PIMcs*TotalSlabs*SlabWeight*MFactor*$CanadaLbsFactor
			ELSE
				PIMcs*TotalSlabs*SlabWeight*LbsFactor
			END) AS NetWgtLbs,
			sum(CASE WHEN PS.Uom = 'GRAMS' THEN
				PIMcs*GrossWeight*MFactor*$CanadaLbsFactor
			ELSE
				PIMcs*GrossWeight*LbsFactor
			END) AS GrossWeightLbs";

	// $sqlExt = "sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
	// 		sum(Mcs*GrossWeight) as GrossWeightKgs,
	// 		sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
	// 		sum(Mcs*GrossWeight) as GrossWeightLbs";

	$SqlTotal = "SELECT sum(PIMcs) as MCS, $sqlExt,
			sum(Price) as Price,
			sum(PIMcs * TotalSlabs * SlabWeight * Price * Mfactor) as PriceKgs,
			sum(CASE WHEN PS.Uom = 'GRAMS' THEN
				PIMcs * TotalSlabs * SlabWeight * Price * MFactor * $CanadaLbsFactor
			ELSE
				PIMcs * TotalSlabs * SlabWeight * Price * LbsFactor
			END) AS PriceLbs
			FROM `ScrmPoGradeData`
			as PGD left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE InvId='$ID'";


	$SqlTotal = "SELECT SUM(PIMcs) AS MCS, $sqlExt,
    SUM(Price) AS Price,
    SUM(PIMcs * TotalSlabs * SlabWeight * Price * Mfactor * $otherTaxMultiplier) AS PriceKgs,
	sum(CASE WHEN PS.Uom = 'GRAMS' THEN
		PIMcs * TotalSlabs * SlabWeight * Price * MFactor * $CanadaLbsFactor * $otherTaxMultiplier
	ELSE
		PIMcs * TotalSlabs * SlabWeight * Price * LbsFactor * $otherTaxMultiplier
	END) AS PriceLbs
	FROM `ScrmPoGradeData` AS PGD
	LEFT JOIN AdminPackingStyles AS PS ON PGD.PackingStyleID = PS.PCKID
	WHERE InvId = '$ID'";

	// ECHO $SqlTotal; EXIT;

	$StmtTotal = $iconn->query($SqlTotal);
	$RowTotal = $StmtTotal->fetch_assoc();

	$NetWgtKgs = $RowTotal['NetWgtKgs'];
	$NetWgtLbs = $RowTotal['NetWgtLbs'];
	$GrossWeightKgs = $RowTotal['GrossWeightKgs'];
	$GrossWeightLbs = $RowTotal['GrossWeightLbs'];

	if ($PoDataRow['QtyUnit'] == 'KGS') {
		$Weight = $RowTotal['NetWgtKgs'];
		$Price = $RowTotal['PriceKgs'];
	} else {
		$Weight = $RowTotal['NetWgtLbs'];
		$Price = $RowTotal['PriceLbs'];
	}

	$DiscountAmount = 0;
	if ($PoDataRow['OtherCharges'] == 1 && $OtherTax > 0) {
		$DiscountAmount = ($Price * $OtherTax) / 100;
		// $Price = $Price - $DiscountAmount;
	}

	$content .= '
				<tr>
				<td align="center" width="40%" style="border-top: 1px solid black;" ><font size="7"><B>
				TOTAL</B></font></td>
				<td align="center" width="18.5%" style="border-top: 1px solid black; border-right: 1px solid black;" ><font size="7">
				' . $RowTotal['MCS'] . '</font></td>
				<td align="center" width="13.5%" style="border-top: 1px solid black;border-right: 1px solid black;"><font size="7">
				' . number_format($Weight, 3) . '</font></td>
				<td align="center" width="13.5%" style="border-top: 1px solid black;border-right: 1px solid black;" ><font size="7">
				</font></td>
				<td align="center" width="14.5%" style="border-top: 1px solid black;border-right: 1px solid black;"><font size="7">
				' . number_format($Price, 2) . '</font>';

	$content .= '</td>
			</tr>';

	if ($PoDataRow['OtherCharges'] == 1 && $OtherTax > 0 && false) {
		$content .= '
			<tr>
			<td align="center" width="40%" style="border-top: 1px solid black;" ></td>
			<td align="center" width="18.5%" style="border-top: 1px solid black;" ></td>
			<td align="center" width="13.5%" style="border-top: 1px solid black;"></td>
			<td align="center" width="13.5%" style="border-top: 1px solid black;border-right: 1px solid black;" ><font size="7">
			Other Tax: ' . $OtherTax . '%</font></td>
			<td align="center" width="14.5%" style="border-top: 1px solid black;border-right: 1px solid black;"><font size="7">
			' . number_format($DiscountAmount, 2) . '</font></td>
			</tr>';
	}

	$content .= '</table>
	</th></tr>';
	// $SqlBank="SELECT * FROM $TblAdminBankDetails WHERE ID='$BankID'";
	$SqlBank = "SELECT * FROM ScrmBankDetailsMaster WHERE ID='$BankID'";
	$StmtBank = $iconn->query($SqlBank);
	$RowBank = $StmtBank->fetch_assoc();
	$BenfName = $RowBank['AccntName'];
	$BenfAccNo = $RowBank['AccountNumber'];
	$BenfBankName = $RowBank['BankName'];
	$BenfBankAddress = $RowBank['BankAddress'];
	$BenfBankCode = $RowBank['SwiftCode'];


	$BILL_WORD = numberTowords($Price);
	$content .= '<tr > ' .
		$TOTAL . '
				</tr>
				<tr>  
           		<th  align="LEFT"  width="100%"><font size="7"> SHIPPING TERMS   : ' . $PoDataRow['ShippingTerms'] . '  ' . strtoupper($FinalDestination) . '
				<BR> TOLERANCE           : QUANTITY/AMOUNT 10% PLUS OR MINUS
				<BR> SHIPMENT DATE :  ' . $SHIPMENT_DATE . ' OR EARLY<BR> <BR><B><U style="background-color:grey">OUR BANKING DETAILS        : </U></B><br>
				<br> BENEFICIARY NAME<b style="color:white">-----------------------</b>: <b>' . $RowBank['AccntName'] . '</b>
				<BR> BENEFICIARY ACCOUNT NUMBER<b style="color:white">----</b>: <b>' . $RowBank['AccountNumber'] . '</b> 
				<BR> BANK NAME<b style="color:white">------------------------------------</b>: <b>' . $RowBank['BankName'] . ' </b>
				<BR> BANK ADDRESS<b style="color:white">------------------------------</b>: <b>' . $RowBank['BankAddress'] . ' </b>
				<BR> SWIFT CODE<b style="color:white">-----------------------------------</b>: <b>' . $RowBank['SwiftCode'] . '</b>';


	$NetWgtKgs = $RowTotal['NetWgtKgs'];
	$NetWgtLbs = $RowTotal['NetWgtLbs'];




	/***uma (07-11-2023) */
	// $GrossWeightKgs = $RowTotal['GrossWeightKgs'];
	// $GrossWeightLbs = $RowTotal['GrossWeightLbs'];

	$TotalGrossWtKgs = getGrossweight($iconn, $ID);
	if ($TotalGrossWtKgs != 0) {
		if ($QtyUnit == 'KGS') {
			$GrossWeightKgs = $TotalGrossWtKgs;
			$GrossWeightLbs = $TotalGrossWtKgs * 2.204;
		} else {
			$GrossWeightKgs = $TotalGrossWtKgs * 0.454;
			$GrossWeightLbs = $TotalGrossWtKgs;
		}
	} else {
		$GrossWeightKgs = $RowTotal['GrossWeightKgs'];
		$GrossWeightLbs = $RowTotal['GrossWeightLbs'];
	}

	// echo $GrossWeightKgs; exit;
	/***uma (07-11-2023) */



	if ($QtyUnit == 'KGS') {
		$content .= '<BR><BR><b> NET WEIGHT <b style="color:white">------</b>: ' . number_format($NetWgtKgs, 3, '.', '') . ' KGS</b>
				<BR><b> GROSS WEIGHT <b style="color:white">-</b>: ' . number_format($GrossWeightKgs, 3, '.', '') . ' KGS</b> 
				</font></th>';
	} else {
		$content .= '<BR><BR> NET WEIGHT<b style="color:white">-----------------------------------</b>: 
				<b>' . number_format($NetWgtLbs, 3, '.', '') . 'LBS</b>
				<b style="color:white">--------------------------</b><b>' . number_format($NetWgtKgs, 3, '.', '') . ' KGS</b>
				<BR> GROSS WEIGHT<b style="color:white">------------------------------</b>: 
				<b>' . number_format($GrossWeightLbs, 3, '.', '') . 'LBS</b>
				<b style="color:white">--------------------------</b><b>' . number_format(($GrossWeightLbs * 0.454), 3, '.', '') . ' KGS</b> 
				</font></th>';
	}

	$taxConditionDate = '2025-07-31';
	$taxConditionDateRT = '2025-09-01';
	$PInvDate = date('Y-m-d', strtotime($InvDate));
	if ($PInvDate >= $taxConditionDateRT) {
		$tariffPercent = '50%';
	} else if ($PInvDate >= $taxConditionDate) {
		$tariffPercent = '25%';
	} else {
		$tariffPercent = '10%';
	}
	if ($PInvDate >= '2026-02-10') { //CAM-2293
		$tariffPercent = '18%';
	}

	$RemarksCountryList = ['157', '41', '124']; // List of country IDs for which remarks should be added
	$content .= '</tr>
				
						
	        
				<tr>  
					<th  align="LEFT"  width="70%"><font size="7"> AMOUNT CHARGABLE IN US.$: <b>' . $BILL_WORD . ' ONLY</b><BR> <BR> 
					<BR> PREPARED BY:  ' . $Preparedby . '<BR><BR><BR><B><U> DECLARATION:</U></B> WE DECLARE THAT THIS INVOICE SHOWS
					THE ACTUAL PRICE OF GOODS DESCRIBED AND THAT ALL PARTICULARS ARE TRUE AND CORRECT
					<BR><BR>';
	if (in_array($DestinationCountryId, $RemarksCountryList) && $ShippingTerms == 'DDP') {
		if ($PInvDate >= '2026-03-10') {
			$Remarks = $PoDataRow['Remarks'];
			$content .= '<B> ' . $Remarks . '</B>';
		} else {
			$content .= '<B> NOTE:  TARIFF ADDED ' . $tariffPercent . ' IF ANY INCREASE OR DECREASE AT THE TIME TO ENTRY MUST BE TO BUYER </B>';
		}
	}

	$content .= '</font></th>

					
					
					<th  align="center"  width="30%"><font size="7"> Signature:<br><br>
					<B> ' . $AuthSign . ',</B>
					
					<img src="' . $SignURL . '" 
					width="100" height="30"style="width: 100" align="center"     valign="middle"/>
									
					
					<p STYLE="text-align:center" ><b> Authorised Signature </b> </p></font></th>
				</tr>
				
				
				</table>';

	// return tcpdf_encode($content);


	// return htmlspecialchars($content, ENT_QUOTES, 'UTF-8');


	// return base64_encode($content);

	// return mb_convert_encoding($content, 'UTF-8', 'auto');
	return $content;
}

function getGrossweight($iconn, $ID)
{

	// $SqlGrossweight="SELECT sum(GrossWt) as TotalGrossweight FROM `ScrmPoGradeData` WHERE InvId ='$ID'"; 
	$SqlGrossweight = "SELECT sum(GrossWt) as TotalGrossweight FROM `ScrmPoGradeData` WHERE InvId ='$ID'";

	$stmtGrossweight = $iconn->query($SqlGrossweight);
	$dataRowGrossweight = $stmtGrossweight->fetch_assoc();

	$TotalGrossweight = $dataRowGrossweight['TotalGrossweight'];

	return $TotalGrossweight;
}
