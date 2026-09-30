<?php

// ini_set('display_errors', 1);
// error_reporting(E_ALL);

$IsHeader = 0;
$IsFooter = 0;
$QueryParms = $_GET['Q'];


include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
include "PrintHeaderInc.php";

if ($QueryParms != '') {
	$QueryParms = urlDecode64($QueryParms);
	$queries = array();
	parse_str($QueryParms, $_GET);
}

$PoID = $_GET['PoID'];
$ReportName = $_GET['ReportName'];


$sql_code_list = "SELECT * FROM QcCodeList WHERE InvID='$PoID' ORDER BY `CLID` ASC";
$stmt_code_list = $iconn->query($sql_code_list);

 $SqlProductList = "SELECT Species,QcDescription,Variety FROM `ScrmPoGradeData` as SPGD 
left join ScrmPIVarietyList SPVL on SPVL.VarietyID=SPGD.VarietyID 
left join QcCodeList QCL on QCL.PoGradeID=SPGD.ID
WHERE SPGD.InvId='$PoID' ORDER BY QCL.CLID ASC"; 

 

//echo $SqlProductList; exit;
while ($code_list_row = $stmt_code_list->fetch_array()) {

	$DAY_CODE = $code_list_row['DayCode'];
	$PACKING_ID = $code_list_row['PackingStyleID'];
	$GLAZE_PERC = $code_list_row['GlazePerc'];
	$MCS = $code_list_row['McsTotal'];
	if(trim($code_list_row['Brand'])!='NEUTRAL'){
	$AryBrand[] = trim($code_list_row['Brand']);
	}

	$T_MCS = $T_MCS + $MCS;

	$sql_PACKING_STYLE = "SELECT * FROM $TblAdminPackingStyles WHERE PCKID='$PACKING_ID' ";
	$stmt_PACKING_STYLE = $iconn->query($sql_PACKING_STYLE);
	$ROW_PACKING_STYLE = $stmt_PACKING_STYLE->fetch_array();
	$PACKING = $ROW_PACKING_STYLE['PackingStyle'];

	if (in_array($DAY_CODE, $ary_code, true)) {
	} else {
		$ary_code[] = $DAY_CODE;

		if ($T_DAY_CODE == '') {
			$T_DAY_CODE = $DAY_CODE;
		} else {
			$T_DAY_CODE = $T_DAY_CODE . ',' . $DAY_CODE;
		}
	}

	if (in_array($PACKING, $ary_PACKING, true)) {
	} else {
		$ary_PACKING[] = $PACKING;

		if ($T_PACKING == '') {
			if ($GLAZE_PERC == 0) {
				$T_PACKING = $PACKING;
			} else {
				$T_PACKING = $PACKING . '(' . $GLAZE_PERC . ' % GLAZE)';
				//$T_PACKING = $PACKING;
			}

		} else {
			if ($GLAZE_PERC == 0) {
				$T_PACKING = $T_PACKING . ',' . $PACKING;
			} else {
				$T_PACKING = $T_PACKING . ',' . $PACKING . '(' . $GLAZE_PERC . ' % GLAZE)';
				//$T_PACKING = $T_PACKING . ',' . $PACKING;
			}

		}

	}

}

$Brand = implode(",", array_unique($AryBrand));

$T_MCS = $T_MCS ;


$SqlPoData = "SELECT *,SCL.name as CountryName,SPC.Name as Port,PD.ExporterID from QcCoveringLetter as CL 
left join ScrmPoData as PD on PD.PoID=CL.InvID 
left join ScrmConsigneeDetails as CD on CD.ConID=PD.ConsigneeID
left join ScrmCustomInvoices as SCI on SCI.PiID=PD.PoID
left join ScrmCountriesList as SCL on SCL.id=PD.DestinationCountry
left join ScrmPortCities as SPC on SPC.PortId=PD.PortOfDischarge
LEFT JOIN QcLabTechnologist as QLT on QLT.LbuName=CL.VerifiedBy 
WHERE InvID ='$PoID'";
$StmtPoData = $iconn->query($SqlPoData);
$RowPoData = $StmtPoData->fetch_array();
$ExpID = $RowPoData['ExporterID'];
$ProcessorID = $RowPoData['ProcessorID'];
$PortofLoading = $RowPoData['PortofLoading'];

$PortofLoading = getPortDetails($RowPoData['PortofLoading'],$iconn)['PortName'];
$FinalDestination = getPortDetails($RowPoData['FinalDestination'],$iconn)['PortName'];

$SqlLoadingPort= "SELECT * FROM ScrmPortCities where PortId='$PortofLoading'";
$StmtLoadingPort = $iconn->query($SqlLoadingPort);
$RowLoadingPort= $StmtLoadingPort->fetch_array();
 //$PortofLoading = $RowLoadingPort['Name']; 


$DestinationCountry = $RowPoData['DestinationCountry'];
$SqlCountryList = "SELECT * FROM $TblScrmCountriesList where id='$DestinationCountry'";
$StmtCountryList = $iconn->query($SqlCountryList);
$RowCountry = $StmtCountryList->fetch_array();
$DestinationCountryName = $RowCountry['name'];
$CountryType = $RowCountry['CountryType'];

IF($CountryType=='2'){
$EuAdmission='YES';	
}ELSE{
$EuAdmission='NO';	
}

$ExporterData = getExporterDetails($ExpID);
$ExporterName=$ExporterData['ExpName'];
$ExporterAddress=$ExporterData['ExpAddress'];

$ProcessorData = getExporterDetails($ProcessorID);
$ProcessorName=$ExporterData['ExpName'];
$ProcessorAddress=$ExporterData['PackerAddress'];
$ProcApprovalNo=$ExporterData['ExpApprovalNo'];

function getHeader($ID, $iconn)
{

	global $TblScrmPoData;
	global $RowPoData, $PoID;

	$contents = '';

	$CONSIGNMENT_EXPORTED = $RowPoData['SciName'];
	 $PO_DATE = date("d-m-Y", strtotime($RowPoData['PoDate'])); 
	$INV_DATE = date("d-m-Y", strtotime($RowPoData['InvDate']));

	if ($RowPoData['CountryType'] == 1) {
		$COUNTRY_TYPE = 'NON-EU';
	} else {
		$COUNTRY_TYPE = 'EU';
	}

	include "Constants.php";

	$ExpID = $RowPoData['ExporterID'];


	$CurrentYear = date('Y', strtotime($PO_DATE));
	;
	$NxtYear = $CurrentYear + 1;


	$ExporterData = getExporterDetails($ExpID);
    $ExporterName=$ExporterData['ExpName'];
	
	$InvNO = $ExporterData['InvShortCode'] . $RowPoData['CInvNo'] . '/' . $CurrentYear . '-' . $NxtYear;
	$InvNO = $RowPoData['CustomsInvNo'];
	//$InvNO=getInvoiceNumber($iconn,$ExpID,$PoID);

	$InvDate = getFormatedDate($RowPoData['CInvDate']);
	$PoDate = getFormatedDate($RowPoData['CpoDate']);

	if ($InvDate == '01-01-1970') {

		$InvDate = ': na';
	}
	/*****uma***/
	$contents .= '<p>Sir,<br/>Sub :- Request for Health Certificate for Fishery products to be exported to <B>' . $RowPoData['CountryName'] . '</B> <BR/><BR/>
Please issue   Health certificate for the consignment bearing Purchase Order No: <B>' . $RowPoData['PoNo'] . ', </B>
Dt. <B>' . $PO_DATE . ' </B>
against Invoice No. <B>' . $InvNO . ', </B>
 Dt. <B>' . $InvDate . ' </B>
The Particulars of Purchase Order are given Below are  to be exported to . <B>' . $COUNTRY_TYPE . ' </B>
Destination .The Certificate fee is paid through Online System and details are attached .
The Certificate for Export relating to this consignment (Pink copy of Certificate NO: <B>' . $RowPoData['QCertNo'] . ' </B>
for export) are enclosed</p>';
	return $contents;
}






$from_title = date('d M, Y');
$to_title = date('d M, Y');






//require_once('Constants.php');



$StmtProductist = $iconn->query($SqlProductList);

while($RowProductName=$StmtProductist->fetch_assoc()){
	
	$QcDescription=$RowProductName['QcDescription'] ? $RowProductName['QcDescription'] : $RowProductName['Variety'];
	$AryProductName[]=trim($QcDescription);
}

 $AryProductName=array_unique($AryProductName); 
 $ProductNameList=implode(" ",array_unique($AryProductName)); 

// echo  $ProductNameList; exit; 

$pdf->SetTitle('Covering Note: ' . $row['PO_NUM'] . ' - ' . $row['PO_DATE']);

$content = '';

// $pdf->Cell(180 ,35,'',0,1,'L');

$content .= getHeader($ID, $iconn);




//$pdf->Cell(180 ,35,'',0,1,'L');
$header = 10;
$sub_header = 9;




$pdf->SetFont($FONT_ARI, '', $header);
//$pdf->Cell(180, 30, 'To,', 0, 1, 'L');
$pdf->Cell(180, 5, 'To,', 0, 1, 'L');
$pdf->Cell(150, 5, 'The Deputy Director,', 0, 0, 'L');
$pdf->Cell(49, 5, '', 0, 1, 'L');
$pdf->Cell(150, 5, 'Export Inspection Agency -Chennai,', 0, 0, 'L');

$DO_SAMPLING = date("d-m-Y", strtotime($RowPoData['Date']));


$pdf->Cell(49, 5, 'Date:' . $DO_SAMPLING, 0, 1, 'L');


$pdf->SetFont($FONT_ARI, 'BU', $header);
$pdf->Cell(0, 5, 'S.O : VISAKHAPATNAM', 0, 1, 'L');
$pdf->Ln();
$pdf->SetFont($FONT_ARI, '', $header);


$pdf->writeHTML($content);
$pdf->Ln();
$pdf->Cell($W1, $H1, '', 0, 1, 'L');

$W1 = 80;
$W2 = 100;
$H1 = 5;
$pdf->SetFont($FONT_ARI, '', $sub_header);
$pdf->Cell($W1, $H1, 'CENTRAL COMPETENT AUTHORITY', 0, 0, 'L');
$pdf->Cell($W2, $H1, ': EXPORT INSPECTION COUNCIL OF INDIA,', 0, 1, 'L');
$pdf->Cell($W1, $H1, '', 0, 0, 'L');
$pdf->Cell($W2, $H1, '  (MINISTRY OF COMMERCE & INDUSTRY GOVT OF INDIA)', 0, 1, 'L');
$pdf->Cell($W1, $H1, '', 0, 0, 'L');
$pdf->Cell($W2, $H1, '  3rd FLOOR,NDYMCA CULTURAL CENTRAL BUILDING ,', 0, 1, 'L');
$pdf->Cell($W1, $H1, '', 0, 0, 'L');
$pdf->Cell($W2, $H1, '  1JAI SING ROAD,NEW DELHI, 110001.INDIA. ', 0, 1, 'L');

$pdf->Cell($W1, $H1, 'LOCAL COMPETENT AUTHORITY', 0, 0, 'L');
$pdf->Cell($W2, $H1, ': EXPORT INSPECTION AGENCY, CHENNAI', 0, 1, 'L');
$pdf->Cell($W1, $H1, '', 0, 0, 'L');
$pdf->Cell($W2, $H1, '  SUB OFFICE - EIA VISAKHAPATNAM.', 0, 1, 'L');



$pdf->Cell($W1, $H1, 'A.Details identifying the fishery products:', 0, 1, 'L');
$pdf->Cell($W1, $H1, '	         I.Product Name:', 0, 1, 'L');
$pdf->Ln();
$pdf->SetFont($FONT_ARI, 'B', $sub_header);

foreach( $AryProductName as $Item){

	$pdf->Cell($W1, $H1, $Item, 0, 1, 'L');
}



//exit;

$ary_varity_list=implode(",",array_unique($ary_varity_list));

$pdf->SetFont($FONT_ARI, '', $sub_header);
$pdf->Ln();

$COUNT = 1;

$count_v = count($ary_varity_list);

if ($count_v > 5) {
	$c_pd = 2;
} else {
	$c_pd = 6;
}
// $SqlVList;

/** <tr>
				<th  width="5%" align="left"></th>
           		<th width="50%" align="left">IV.State of type Processing</th>
                <th width="45%" align="left">: ' . $RowPoData['TypeProcessing'] . '</th>
           </tr>
***/

$SqlVList = "SELECT Species,QcDescription,Variety FROM `ScrmPoGradeData` as SPGD 
left join ScrmPIVarietyList SPVL on SPVL.VarietyID=SPGD.VarietyID 
 WHERE InvId='$PoID'";
 
$StmtVList= $iconn->query($SqlVList);

$RowSpecies=$StmtVList->fetch_assoc();
$SpeciesName=getSpeciesDetails($RowSpecies['Species'],$iconn)['SpeciesName'];

$FARMER_NAME .= '<table border="0" cellspacing="0" padding-left: 10px; cellpadding="' . $c_pd . '">

		<tr>
				<th  width="5%" align="left"></th>
           		<th  width="50%" align="left">II.Discription of Fishery/ Aquaculture products</th>
                <th width="45%" align="left">: AQUACULTURE ORIGIN</th>
           </tr>


		   <tr>
				<th  width="5%" align="left"></th>
           		<th width="50%" align="left">III.Species (scientifc name)</th>
                <th width="45%" align="left">: ' . $SpeciesName . '</th>
           </tr>


			

			<tr>
				<th  width="5%" align="left"></th>
           		<th width="50%" align="left">IV.Brand</th>
                <th width="45%" align="left">: ' . $Brand . '</th>
           </tr>

		   <tr>
				<th  width="5%" align="left"></th>
           		<th width="50%" align="left">V.Codes</th>
                <th width="45%" align="left">: ' . $T_DAY_CODE . '</th>
           </tr>

			<tr>
				<th  width="5%" align="left"></th>
           		<th width="50%" align="left">VI.Type of Packing</th>
                <th width="45%" align="left">: ' . $T_PACKING . '(PER M/CS)</th>
           </tr>

		   <tr>
				<th  width="5%" align="left"></th>
           		<th width="50%" align="left">VII. Number of Packages</th>
                <th width="45%" align="left">: ' . $T_MCS . '   (CORRUGATED MASTER CARTONS)</th>
           </tr>

		   <tr>
				<th  width="5%" align="left"></th>
           		<th width="50%" align="left">VIII. Net Weight</th>
						<th width="45%" align="left">: ' . getPriceDetails($PoID,'KGS',$iconn)['NetWeightKGS'] . ' KGS</th>
           </tr>
		   
		   <tr>
				<th  width="5%" align="left"></th>
           		<th width="50%" align="left">IX. Gross Weight</th>
						<th width="45%" align="left">: ' .ROUND( getPriceDetails($PoID,'KGS',$iconn)['GrossWeightKGS'],2) . ' KGS</th>
           </tr>
		   <tr>
				<th  width="5%" align="left"></th>
           		<th width="50%" align="left">X. Commodities Certified for</th>
						<th width="45%" align="left">:  HUMAN CONSUMPTION </th>
           </tr>
		   <tr>
				<th  width="5%" align="left"></th>
           		<th width="50%" align="left">XI. For Import or admission to EU</th>
						<th width="45%" align="left">:'.$EuAdmission.' </th>
           </tr>
		   
		   <tr>
				<th  width="5%" align="left"></th>
           		<th width="50%" align="left">XII. Enter BIP in EU</th>
						<th width="45%" align="left">: NA </th>
           </tr>
		    <tr>
				<th  width="5%" align="left"></th>
           		<th width="50%" align="left">XIII. Commodity Code (HS Code)</th>
						<th width="45%" align="left">: 030617 </th>
           </tr>

		 


		   </table>
		';


$pdf->writeHTML($FARMER_NAME);



/* next page*/

$pdf->AddPage();



$W2 = 85;
$W3 = 100;
$H1 = 5;


//$pdf->Cell(180 ,35,'',0,1,'L');

//$consign_exp = explode(',', );

$CONSIGNMENT_EXPORTED_TO = $RowPoData['TypeTransport'] . ', CONTAINER NO :' . $RowPoData['ContainerNo'] . ',
 ' . 'VESSEL NAME :' . $RowPoData['VesselNo'] . ', SEAL NO :' . $RowPoData['SealNo'];




$FARMER_NAME = '<table border="0" cellspacing="0" cellpadding="2">
          <tr>
				<th  width="5%" align="left"></th>
           		<th width="50%" align="left">XIV.Means Of Transport :-</th>
                <th width="45%" align="left">: SHIP </th>
           </tr>
            <tr>
				<th  width="5%" align="left"></th>
           		<th width="50%" align="left">XV.Temperature required during storage and Transport:-</th>
                <th width="45%" align="left">: -18 .0°C </th>
           </tr>

		   <tr>
		   <th  width="5%" align="left"></th>
			  <th width="50%" align="left">XVI.Place of Loading:-</th>
		   <th width="45%" align="left">: '.$PortofLoading.' </th>
	     </tr>

		   <tr>

           		<th width="55%" align="left">B. Region of Origin</th>
                <th width="45%" align="left">: ANDHRA PRADESH  </th>
           </tr>

			<tr>
           		<th width="55%" align="left">C.Provenance of the Fishery Product</th>
                <th width="45%" align="left"></th>
           </tr>

		   <tr>
				<th  width="5%" align="left"></th>
           		<th width="50%" align="left">	 I.Address of Processing Establishments where the Goods are Processed</th>
                <th width="45%" align="left">: ' . $ProcessorName . '<br>' . nl2br($ProcessorAddress) . '</th>
           </tr>

		    <tr>
           		<th  width="5%" align="left"></th>
				<th width="50%" align="left">	 II.EIA Approval Number</th>
                <th width="45%" align="left">: ' . $ProcApprovalNo . '</th>
           </tr>

		   <tr>

           		<th width="55%" align="left">D.Destination of Fishery Products</th>
                <th width="45%" align="left"></th>
           </tr>

		   <tr>
           		<th  width="5%" align="left"></th>
				<th width="50%" align="left">I.Place/Port from where the Consignment is to be shipped for Export</th>
                <th width="45%" align="left">: ' . $PortofLoading . ' - INDIA</th>
           </tr>

		   <tr>
           		<th  width="5%" align="left"></th>
				<th width="50%" align="left">II. Country and Place of destination</th>
                <th width="45%" align="left">: ' .strtoupper($FinalDestination) .','.  $DestinationCountryName. '</th>
           </tr>



		 <!--   <tr>
           		<th  width="5%" align="left"></th>
				<th width="50%" align="left">III. Means of Transport:</th>
                <th width="45%" align="left">: ' . $CONSIGNMENT_EXPORTED_TO . '</th>
           </tr>-->

		    <tr>
           		<th  width="5%" align="left"></th>
				<th width="50%" align="left">III. Name and Address of the Consigner :</th>
                <th width="45%" align="left">: ' . $ExporterName . '<br>' . nl2br($ExporterAddress) . '</th>
           </tr>

		   <tr>
           		<th  width="5%" align="left"></th>
				<th width="50%" align="left">IV. Name and Address of the Consignee</th>
                <th width="45%" align="left">: ' . $RowPoData['ConsigneeName'] . nl2br($RowPoData['ConsigneeAddress']) . '</th>
           </tr>

		   </table>';

$pdf->writeHTML($FARMER_NAME);
$contents = '<p><b>The product is Fit for human consumption.</b></p>';
$pdf->writeHTML($contents);

$pdf->Cell(0, 5, '', 0, 1, 'L');
$contents = '<p style="font-size: 9px;">IV.     It is here by certified that the Fishery Products described above have been handled,
prepared or processed, identified stored and transported under hygienic conditions as laid down
in the Export of Fresh Frozen and processed Fish and Fishery products,Rules(Quality control,
Inspection and Monitoring) 1995 and that the establishment where the Fishery products have
been processed is approved and regularly monitored by the EXPORT INSPECTION
AGENCY....(COMPETENT AUTHORITY)</p>';
$pdf->writeHTML($contents);
$PO_TIME_STAMP = strtotime($RowPoData['Date']);
$DO_SAMPLING = date("d-m-Y", $PO_TIME_STAMP);

$pdf->Cell(0, 5, '', 0, 1, 'L');
$contents = '<p>Place: <b>Patharlagadda</b><br></b><br>Yours Faithfully<br>
For <b>' . $ExporterName . '</b></p>';
$pdf->writeHTML($contents);

//Date:<b>' . $DO_SAMPLING . '

$pdf->Cell(0, 1, '', 0, 1, 'L');

$pdf->Cell(0, 5, $RowPoData['VerifiedBy'], 0, 1, 'L');
$pdf->Cell(0, 5, $RowPoData['LbuDesignation'], 0, 1, 'L');



$pdf->Ln();


ob_clean();

if ($FileType == '') {
	$FileType = 'I';
	$FileName = $ReportName . '_' . $RowPoData['InvNumFull'];
	$FileName = getFormatedFileName($FileName);
	$pdf->Output($FileName.'.pdf', 'I');
} else {
	$Fname = $MailTempFolder;
	$FileName = $_GET['FileName'];
	$FileName = str_replace('/', '', $FileName);
	$FileName = $Fname . $FileName.'.pdf';
	$pdf->Output($FileName, $FileType);

}

?>