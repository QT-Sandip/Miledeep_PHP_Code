<?php
//ini_set('display_errors', 1);
//session_start();
$IsHeader = 0;
$IsFooter = 0;
$IsLetterHead = 0;
$Orientation = 'L';
$QueryParms = $_GET['Q'];


include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
include "../PrintHeaderInc.php";


$PoID = $_GET['PoID'];
$ReportName = $_GET['ReportName'];
$FileType = $_GET['Type'];

$SqlScrmPodata = "SELECT PoNo FROM `ScrmPoData` WHERE PoID='$PoID'";
$StmtPo = $iconn->query($SqlScrmPodata);
$RowPo = $StmtPo->fetch_array();

$SqlVerified = "SELECT VerifiedBy FROM QcCodeList  as CL	WHERE InvID='$PoID' ORDER BY `DayCode` ASC";
$StmtVerified = $iconn->query($SqlVerified);
$RowVerified = $StmtVerified->fetch_array();

$table_data = getPageOne($iconn, $PoID);
$table_data1 = explode("&", $table_data[1]);

$pdf->SetFont($fontname, 'B', 9);

$pdf->Cell(1, -10, '', 0, 1, 'L');
$pdf->writeHTML($table_data[0]);
$pdf->Cell(60, 5, 'NAME : ' . $RowVerified['VerifiedBy'], 0, 0, 'C');
$pdf->Cell(170, 5, 'COMPANY SEAL:', 0, 1, 'R');
$pdf->Cell(60, 0, 'DESIGNATION: Q.c Technologist ', 0, 1, 'C');

ob_clean();


if ($FileType == '') {
	$FileType = 'I';
	$FileName = $ReportName . '_' . $RowPo['PoNo'];
	$FileName = getFormatedFileName($FileName);
	$pdf->Output($FileName, 'I');
} else {
	$Fname = $MailTempFolder;
	$FileName = $_GET['FileName'];
	$FileName = str_replace('/', '', $FileName);
	$FileName = $Fname . $FileName;
	$pdf->Output($FileName, $FileType);
}


function getPageOne($iconn,  $PoID)
{



	$SqlPoData = "SELECT *,POD.ExporterID FROM `ScrmPoData` AS POD 
			 LEFT JOIN AdminExporterDetails AS EXD  ON POD.ProcessorID=EXD.ExpID 
			 left join ScrmCustomInvoices as SCI on SCI.PiID=POD.PoID WHERE PoID='$PoID'";
	$StmtPoData = $iconn->query($SqlPoData);
	$RowPoData = $StmtPoData->fetch_array();


	$PONumber = $RowPoData['PoNo'];
	$InvNO = $RowPoData['CustomsInvNo'];
	$BuyerID = $RowPoData['BuyerID'];
	$ScintificName = $RowPoData['SciName'];
	$ProcessorName = $RowPoData['ExpName'];
	$ProcessorAddress = $RowPoData['ExpAddress'];
	$QtyUnit = $RowPoData['QtyUnit'];
	$AryPortDetails = getPortDetails($RowPoData['FinalDestination'], $iconn);
	$CountryName = getCountryDetails($RowPoData['DestinationCountry'], $iconn)['CountryName'];
	$ConsigneeName = getConsigneeDetails($RowPoData['ConsigneeID'], $iconn)['ConsigneeName'];
	$DeliveryLocation = $AryPortDetails['PortName'] . ' ' . $AryPortDetails['CountryCode'] . ',' . $CountryName;




	$SqlCodeList = "SELECT * FROM `QcCodeList` AS CL 
	LEFT JOIN ScrmPoGradeData AS VL ON VL.ID=CL.PoGradeID WHERE CL.InvID='$PoID' GROUP BY TracebilityID ORDER BY CL.CLID ASC";
	$StmtCodeList = $iconn->query($SqlCodeList);

	$COUNT = 1;
	$Dcount = 2;
	$Fcount = 1;

	while ($RowCodeList = $StmtCodeList->fetch_assoc()) {


		$ArrayProductForm[] = $RowCodeList['QcDescription'] ? $RowCodeList['QcDescription'] : $RowCodeList['Variety'];
		$ProductForm = implode("<br>", array_unique($ArrayProductForm));
		$ARYDateOfLanding[] = getDayCodeDate($RowCodeList['DayCode']);
		$DateOfLanding = implode(",", array_unique($ARYDateOfLanding));
		$rowDayCode  =  $RowCodeList['DayCode'];
		$AryDayCode[] = $RowCodeList['DayCode'];
		$CodeListId = $RowCodeList['CLID'];
		$DayCode = implode("<br><br>", array_unique($AryDayCode));



		if ($Daycodes == '') {
			$Daycodes = $Dcount . '.' . $RowCodeList['DayCode'];
		} else {
			$Daycodes = $Daycodes . '<br>' . $Dcount . ' .' . $RowCodeList['DayCode'];
		}
		$Dcount++;
		$TracebilityID = $RowCodeList['TracebilityID'];

		$SqlTraceList = "SELECT * FROM `QcTracebilityList` where TraceID='$TracebilityID'";
		$StmtTraceList = $iconn->query($SqlTraceList);

		while ($RowTraceList = $StmtTraceList->fetch_assoc()) {
			//$Fcount = 1;

			$RowTraceList['Address'];
			$ArrayfarmerName[] = $RowTraceList['FarmerName'] . ',' . $RowTraceList['AddressContinued']; //uma 14/12/2022
			$FarmerName = implode("<br><br>", array_unique($ArrayfarmerName));

			if ($FarmAddress == '') {
				$FarmAddress = $RowTraceList['Address'];
			} else {
				$FarmAddress = $FarmAddress . ',' . $RowTraceList['Address'];
			}


			if ($F_NAME == '') {
				$F_NAME = $COUNT . '.' . $RowTraceList['FarmerName'] . '<br>' . $RowTraceList['AddressContinued'];
			} else {
				$F_NAME = $F_NAME . '<br>' . $COUNT . ' .' . $RowTraceList['FarmerName'] . '<br>' . $RowTraceList['AddressContinued'];
			}
			$COUNT++;
		}

		$SqlCodeList = "SELECT PVL.ShortName  FROM `QcCodeList` as cl 
		LEFT JOIN `ScrmPoGradeData` as PDG on PDG.ID=cl.VarietyID
		LEFT JOIN ScrmPIVarietyList as PVL on PDG.VarietyID=PVL.VarietyID 
		WHERE CLID='$CodeListId' ORDER BY cl.CLID ASC";
		$stmtCodeList = $iconn->query($SqlCodeList);
		$RowCodeList = $stmtCodeList->fetch_array();
		$ShortName = $RowCodeList['ShortName'];



		$SqlFinalQty = "SELECT sum(McsTotal) as McsTotal,Yeild,SSM.SpeciesName,
		sum(TotalSlabs*SlabWeight*MFactor*McsTotal) as NetWgtKgs ,
		sum(McsTotal*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs
		FROM `QcCodeList` as QCL 
		LEFT join ScrmPoGradeData as SPGD on SPGD.ID=QCL.PoGradeID
		left Join ScrmPIVarietyList as PVL on SPGD.VarietyID=PVL.VarietyID
		left join AdminPackingStyles As APS on APS.PCKID=QCL.PackingStyleID
		left join ScrmSpeciesMaster As SSM on SSM.ShortCode=PVL.Species
		WHERE QCL.InvID='$PoID' and DayCode='$rowDayCode'and PVL.ShortName='$ShortName'and 
		TracebilityID='$TracebilityID'  ORDER BY QCL.CLID ASC";
		$stmtFinalQty = $iconn->query($SqlFinalQty);
		//ECHO $SqlFinalQty; EXIT;

		while ($RowFinalQty = $stmtFinalQty->fetch_array()) {

			$SpeciesName = $RowFinalQty['SpeciesName'];
			$Yield = $RowFinalQty['Yeild'];


			if ($QtyUnit == 'KGS') {
				$FinishedQty = $RowFinalQty['NetWgtKgs'];
			} else {
				$FinishedQty = $RowFinalQty['NetWgtLbs'] * 0.454;
			}
			$UsedQty = ((($FinishedQty / $Yield) * 100) + 0.001);
			$ReceivedQty = round($UsedQty, 3);




			if ($FinalWeight == '') {

				$FinalWeight = $Fcount . '.' . $ReceivedQty . ' HON    (   ) lb  or  (<span style="font-family:zapfdingbats;">3</span>)  kg <br>';
			} else {

				$FinalWeight = $FinalWeight . '<br>' . $Fcount . '.' . $ReceivedQty . ' HON    (   ) lb  or  (<span style="font-family:zapfdingbats;">3</span>)  kg <br>';
			}
		}
		$Fcount++;
	}







	$content = '';

	$header = 12;
	$text = 8;


	$Tick = "#0x2712;";

	$PO_Date = date("d-m-Y", strtotime($inv_row['PO_DATE']));

	$content .= '<table border="1" cellspacing="0" cellpadding="0">	
	  

				<tr style="background-color:#D3D3D3">  
           		<th  align="center"  width="100%"><font size="' . $header . '"><b  > Model  Catch Certificate for Traceability- Harvest and Landing Receipt </b> </font>
				
				</th>
               </tr>	 

	            <tr>  
           		<th  align="LEFT"  width="45%"><font size="' . $text . '"><b>(1)Unique Catch or Production Document Identifier*:</b></font>
				<br><font align="center" size="12"> <b>' . $InvNO . '&' . $PONumber . ' </b> </font></th>
                <th  align="LEFT" width="55%"><font face="Arial" size="' . $text . '"> <b> 
				(X)Wild Capture Fishery (Complete Section 2) <br><br><br> ';






	$content .= '(<span style="font-family:zapfdingbats;">3</span>)';

	$content .= 'Farm Raised(Complete Section 3) </b> </font></th>
                
				</tr>
				
				<tr   style="background-color:#D3D3D3" >  
           		<th  align="LEFT"  width="100%"><font size="' . $text . '"> <b>   </b> </font></th>
               </tr>	
				
				<tr>  
           		<th  align="LEFT"  width="30%"><font size="' . $text . '"><b>Flag state of Vessel </b></font></th>
                <th  align="LEFT" width="30%"><font size="' . $text . '"><b>Name of Harvesting Vessel:**</b></font></th>
                <th  align="LEFT" width="40%"><font size="' . $text . '"><b>Vessel Registration or Documentation Number:**</b></font></th>


				</tr>
				
				<tr>  
           		<th  align="LEFT"  width="30%"><font size="' . $text . '"> <b> Fishing Permit Number** </b> </font></th>
                <th  align="LEFT" width="30%"><font size="' . $text . '"> <b> Fishing / catch Area:  <br> ' . $FarmAddress . '</b> </font></th>
                <th  align="LEFT" width="40%"><font size="' . $text . '"> <b> Fishing Gear: </b> </font></th>
                

				</tr>
				
				<tr   style="background-color:#D3D3D3" >  
           		<th  align="LEFT"  width="100%"><font size="' . $text . '"> <b> (3) Complete this Section for <u> Farm
				raised </u>Fish  Product </b> </font></th>
               </tr>	
				
				<tr>  
           		<th  align="LEFT"  width="30%"><font size="' . $text . '"><b> State of Jurisdriction: </b></font>
				<br><font align="center" size="10"><b>INDIA</b></font>
				</th>
                <th  align="LEFT" width="30%"><font size="' . $text . '"><b>Facility License or Authorization**: </b>
   					<br>MARINE PRODUCT EXPORT DEVELOPMENT AUTHORITY OF INDIA				</font>
				<br><font align="center" size="12"><b>' . $tracibility . ' </b></font>
				</th>
               <!-- <th  align="LEFT" width="40%"><font size="' . $text . '"><b>Name and Location of Aquaculture Facility**: </b><br><b>' . $F_NAME . '</b></font></th>-->
                <th  align="LEFT" width="40%"><font size="' . $text . '"><b>Name and Location of Aquaculture Facility**: </b><br><b>' . $FarmerName . '</b></font></th>
                

				</tr>

				<tr   style="background-color:#D3D3D3" >  
           		<th  align="LEFT"  width="100%"><font size="' . $text . '"> <b>    </b></font></th>
               </tr>
				<tr>  
           		<th  align="LEFT"  width="30%"><font size="' . $text . '"><b>Name of Recipient, Processor or Buyer: <br>' . $ProcessorName . ' <br> ' . $ProcessorAddress . '</b></font></th>
                <th  align="LEFT" width="30%"><font size="' . $text . '"><b>Telephone: 9848599987<br><br> 
				Email:qc@suryamitraexim.com </b> </font></th>
                <th  align="LEFT" width="40%"><font size="' . $text . '"><b>Business Address: <br>' . $ProcessorName . '<BR>' . $ProcessorAddress . '</b> </font></th>
                

				</tr>    
				
				
				<tr>  
           		<th  align="LEFT"  width="30%"><font size="' . $text . '"><b>Receiving Facility or Vessel<br>' . $ConsigneeName . '</b></font></th>
                <th  align="LEFT" width="30%"><font size="' . $text . '"><b>Date of Landing/Transshipment <br> ' . $DateOfLanding . '</b></font> 
				<br><font align="center" size="12"><b>' . $date_of_landing . ' </b></font></th>
                <th  align="LEFT" width="40%"><font size="' . $text . '"><b>Landing Port or Delivery Location: <br>' . $DeliveryLocation . '<br> </b></font></th>
                

				</tr>
				
				<tr>  
           		<th  align="LEFT"  width="30%"><font size="' . $text . '"> <b> Species of fish (ASFIS 3 alpha code): <br> 1.<U>' . $SpeciesName . '<br> ' . $DayCode . '</U></b></font>

				
				<br><font align="center" size="12"><b></b></font></th>
                <th  align="LEFT" width="30%"><font size="' . $text . '"> <b> Landed or Delivered Weight: </b></font>
				<br><font align="center" size="10">' . $FinalWeight . ' </font></th>
                <th  align="LEFT" width="40%"><font size="' . $text . '"> <b> Product Form: </b></font>
				<br><font align="center" size="10"><b>' . $ProductForm . ' </b></font></th>
                

				</tr>';



	$content .= '</table>
			<p>** Not required if completing an aggregated catch certificate for small-scale vessels or aquaculture facilities.</p>';

	$data = array();
	$data[] = $content;
	$data[] = $inv_row['PO_NUM'] . '& ' . $PO_Date;
	$data[] = $CONSIGNMENT_NUMBER;
	return $data;
}



ob_end_clean();
