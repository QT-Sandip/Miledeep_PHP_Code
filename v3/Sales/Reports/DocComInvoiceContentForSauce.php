<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

//DocComInvoiceContentRgular.php
function getHeaderTable($iconn, $ID)
{
	//include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
	global $AryData, $style, $PType, $AryLables, $DocType;

	$content = '';


	$size = 45;
	$size1 = 55;






	$ExporterID = $AryData['ExporterID'];
	$ExporterName = $AryData['ExporterName'];
	$ExporterAddress = $AryData['ExporterAddress'];
	$ExporterUsfdaNo = $AryData['ExporterUsfdaNo'];
	$DestinationCountryId = $AryData['DestinationCountryId'];
	$ExporterIeCode = $AryData['ExporterIeCode'];
	$ExporterGstNo = $AryData['ExporterGstNo'];

	$ConsigneeID = $AryData['ConsigneeID'];
	$BuyerID = $AryData['BuyerID'];
	$ConsigneeName = $AryData['ConsigneeName'];
	$ConsigneeAddress = $AryData['ConsigneeAddress'];
	$ConsigneeCountry = $AryData['ConsigneeCountry'];
	$BuyerName = $AryData['BuyerName'];
	$BuyerAddress = $AryData['BuyerAddress'];

	$PortOfDischarge = $AryData['PortOfDischarge'];
	$FinalDestination = $AryData['FinalDestination'];
	$DestinationCountryName = $AryData['DestinationCountryName'];
	$VesselNumber = $AryData['VesselNumber'];

	$ShowCWiseLc = $AryData['ShowCWiseLc'];
	$ShowCWiseLcDate = $AryData['ShowCWiseLcDate'];
	$ShowCWiseLcIssuedBy = $AryData['ShowCWiseLcIssuedBy'];
	$ShowCWiseWeightPerctText = $AryData['ShowCWiseWeightPerctText'];
	$ShowCWiseNotifyPrty = $AryData['ShowCWiseNotifyPrty'];


	$InvNo = $AryData['InvNo'];
	$InvDate = $AryData['InvDate'];
	$PoNo = $AryData['PoNo'];
	$ReferenceNo = $AryData['ReferenceNo'];
	$PoDate = $AryData['PoDate'];
	$ShowBank = $AryData['ShowBank'];

	$ProcessorName = $AryData['ProcessorName'];
	$ProcessorAddress = $AryData['ProcessorAddress'];
	$ProcessorUsfdaNo = $AryData['ProcessorUsfdaNo'];
	$ProcessorFEI = $AryData['ProcessorFEI'];
	$ProcessorApprovalNo = $AryData['ProcessorApprovalNo'];

	$ShowShippingTerms = $AryData['ShowShippingTerms'];
	$ShowCWiseBuyer = $AryData['ShowCWiseBuyer'];
	$showImporter = $AryData['showImporter'];
	$showNotifyParty = $AryData['showNotifyParty'];
	$ShippingTerms = $AryData['ShippingTerms'];
	$ProcessorIeCustomsNo = $AryData['ProcessorIeCustomsNo'];
	//$ExporterRecAdd=$AryData['ExporterRecAdd'];
	$NotifyPartyName = $AryData['NotifyPartyName'];
	$NPartyAddress = $AryData['NPartyAddress'];
	$NPartyContactNumber = $AryData['NPartyContactNumber'];
	$ImporterName = $AryData['ImporterName'];
	$ImporterAddress = $AryData['ImporterAddress'];

	$PaymentTerms = $AryData['PaymentTerms'];
	$PortOfDischarge = $AryData['PortOfDischarge'];
	$PortofLoading = $AryData['PortOfLoading'];
	$DestinationCountry = $AryData['DestinationCountry'];
	//$PortofLoadingCountry=$AryData['PortofLoadingCountry'];
	$DeliveryTerms = $AryData['DeliveryTerms'];
	$ShowDeliveryTerms = $AryData['ShowDeliveryTerms'];
	$IRNNumber = $AryData['IRNNumber'];
	$QtyUnit = $AryData['QtyUnit'];
	$UnitCurrency = $AryData['UnitCurrency'];
	$TotalMcs = $AryData['TotalMcs'];
	$ShipByDate = $AryData['ShipByDate'];
	$SignURL = $AryData['SignURL'];
	$AuthSign = $AryData['AuthSign'];
	$PreparedBy = $AryData['PreparedBy'];
	$BankID = $AryData['BankID'];
	$Remarks = $AryData['Remarks'];
	$showRemarks = $AryData['showRemarks'];
	$showExpUsFda = $AryData['showExpUsFda'];
	$showExpIECode = $AryData['showExpIECode'];
	$showExpGSTNo = $AryData['showExpGSTNo'];
	$ShippingTerms = $AryData['ShippingTerms'];

	$ShowCWiseUsfda = $AryData['ShowCWiseUsfda'];
	$ShowCWiseApprovalNo = $AryData['ShowCWiseApprovalNo'];
	$showProFEI = $AryData['showProFEI'];

	$ContainerNo = $AryData['ContainerNo'];
	$SealNo = $AryData['SealNo'];
	$BLDate = $AryData['BLDate'];
	//$BLDate=getFormatedDateDMMY($AryData['BLDate']);
	$SBDate = $AryData['SBDate'];
	$BLNo = $AryData['BLNo'];
	$SBNo = $AryData['SBNo'];
	$OrgVesselName = $AryData['OrgVesselName'];
	$OrgVOYNO = $AryData['OrgVOYNO'];
	$ByRoad = $AryData['ByRoad'];
	$ComShippingTermExt = $AryData['ComShippingTermExt'];
	$ShowBLNo = $AryData['ShowBLNo'];
	$ShowBLDate = $AryData['ShowBLDate'];
	$ShowShippingBillnDate = $AryData['ShowShippingBillnDate'];
	$ShowShippingDetails = $AryData['ShowShippingDetails'];
	// $ShowShippingDetails = 0;
	// if($ShowBLNo==0){

	//     $BLNo='';	

	// }ELSE{

	// $BLNo=$BLNo;	
	// }

	// if($ShowBLDate==0){

	//  $BLDate='';	
	// }ELSE{

	// $BLDate=$BLDate;	
	// }	


	if ($ComShippingTermExt != '') {

		$ShippingTermsDisplay = $AryData['ShippingTerms'];
		$ShippingTermsDisplay = '';
	}

	$SqlVarietyHSN = "SELECT HSCode
							FROM ScrmCIGradeData SCGD
							LEFT JOIN ScrmPIVarietyList v ON SCGD.`VarietyID`= v.`VarietyID`
							LEFT JOIN ScrmPreparationMaster spm ON v.PreparationType=spm.ShortCode 
                    	WHERE HSCode IS NOT NULL AND InvId='$ID' GROUP BY HSCode ORDER BY SCGD.ID ASC";
	$StmtVarietyHSN = $iconn->query($SqlVarietyHSN);
	$ResVarietyHSN = mysqli_num_rows($StmtVarietyHSN);
	$hsnCode = [];
	while ($VarietyHsnRow = $StmtVarietyHSN->fetch_assoc()) {
		$HSCode = $VarietyHsnRow['HSCode'];
		$firstSix = substr($HSCode, 0, 6);

		if (strpos($firstSix, '.') !== false) {
			$HSCode = substr($HSCode, 0, 7);
		} else {
			$HSCode = substr($HSCode, 0, 6);
		}
		$hsnCode[] = $HSCode;
	}
	$hsnCode = implode(' & ', $hsnCode);

	$content .= $style;
	$content .= '
	 
	 <table border="1" cellspacing="0" cellpadding="0" nobr="true">  ';
	if ($PType == 'KGS') {

		$content .= '<tr><th width="100%"> <br><br> <b> IRN NO: ' . $IRNNumber . '<br></b> </th></tr>';
	}
	$content .= '	 <tr>
				 
				
				
				<th width="45%">
				<table border="1" cellspacing="0" cellpadding="2" nobr="true">';

	$content .= '
				
				<tr>
				<th  align="LEFT" > <b>Exporter :</b>
				<br>' . $ExporterName . ' 
				<BR> ' . nl2br($ExporterAddress) . '';

	/*	if($showExpUsFda){
																																																						  $content .= '	USFDA REGISTRATION NO  : '.$ExporterUsfdaNo.'<BR>';
																																																						  }

																																																						  if($showExpIECode){
																																																						  $content .= ' IE CODE  : '.$ExporterIeCode;

																																																						  }

																																																						  if($showExpGSTNo){
																																																						  $content .= '<BR> GST NO  : '.$ExporterGstNo;
																																																						  }*/


	$content .= ' </th></tr>';






	$content .= '
				<tr> 
				<th  align="LEFT" > <b><u>Consignee</u></b>
				<br>' . $ConsigneeName . ',<br>' . nl2br($ConsigneeAddress) . '  </th>
				</tr>';
	if ($ShowCWiseNotifyPrty) {
		if ($showNotifyParty) {
			$content .= '		<tr>  
           		
				<th  align="LEFT" > <b> <u>Notify Party</u></b>
				<br>' . $NotifyPartyName . ',<br>' . nl2br($NPartyAddress) . '
				
				
				 </th>
				</tr>';
		}
	}
	$W_SPACE = '<b style="color:white"> yuhjutgfrtgujkiolmnbvgfgtyhjmnkih</b>';
	if ($ShowShippingDetails && $ShowShippingBillnDate & (isset($AryData['ShippingBillDate']) && $AryData['ShippingBillDate'] != "") && (isset($AryData['ShippingBillNo']) && $AryData['ShippingBillNo'] != "")) {
		$ShippingDetails = '<th  align="LEFT" width="100%;">
								<b> Shipping Bill No :' . $AryData['ShippingBillNo'] . '</b><br>
								<b> Shipping Bill Date :' . date("d-m-Y", strtotime($AryData['ShippingBillDate'])) . '</b>
							</th>';
	} else {
		$ShippingDetails = "";
	}
	/*<tr>  

																																																						 <th  align="LEFT" width="50%" > Pre-Carriage by
																																																						 <br  align="center">REFER CONTAINER </th>
																																																						 <th  align="LEFT" width="50%" > Place of Receipt by
																																																						 <br align="center">ANY PORT, INDIA </th>
																																																						 below lines
																																																						 <th  align="left"  width="12.5%"style="border-right: 1px solid black;"> Shipping Marks: <br><br><BR align="center"> NO MARKS </th>
																																																						 <th  align="LEFT" width="10%" style="border-right: 1px solid black;"> No. & kind of packings <br><BR><b> '.$TotalMcs.'</b> MCS </th>
																																																						 <th  align="LEFT" width="22.5%" style="border-right: 1px solid black;"> Container Number <br><br><br><b align="center">1 X 40 FCL</b> </th>
																																																						 <th  align="LEFT" width="13.5%" style="border-right: 1px solid black;"> Seal Number <br><br><br><b align="center">'.$SealNo.'</b> </th>

																																																						 </tr>*/
	$content .= '<tr>  
           		
				<th  align="LEFT" width="50%" ><b> Vessel / Flight No. </b>
               <BR align="LEFT"> ' . $OrgVesselName . '  <br> ' . $OrgVOYNO . '
			    <br> </th>
				<th  align="LEFT" width="50%" ><b> Port of Loading </b>
				<br align="center">' . $PortofLoading . ' </th>
				
				</tr>
				
				<tr>  
           		
				<th  align="LEFT" width="50%" ><b> Port of Discharge</b>
				<br align="center">' . $PortOfDischarge . ' </th>
				<th  align="LEFT" width="50%" ><b> Final Destination </b>
				<br align="center"> ' . $FinalDestination . ',' . $DestinationCountryName . ' </th>
				
				</tr>
				
				<tr>  
           		
				<th  align="LEFT" width="50%" >
				 Marks & Nos  
				</th>
				<th  align="LEFT" width="50%" >
				 No.& Kind of Pkgs <br><b align="center" >' . $TotalMcs . ' M/Cs. </b> 
				</th>
				</tr>
				<tr>  
           		' . $ShippingDetails . '
				</tr>';
	if ($hsnCode) {
		$content .= '<tr>  
									<th  align="LEFT" width="100%" >
									<b>HSN Code :</b> ' . $hsnCode . '
									</th>
								</tr>';
	}

	$content .= '</table>
				</th>
				
				
				<th width="55%">
				<table border="1" cellspacing="0" cellpadding="1" nobr="true">
				
				<tr > 
				<th  align="LEFT"  > <b> Invoice No. & Date.
				 ' . $InvNo . ',Dated ' . $InvDate . ' </b> </th>
				</tr>
				
				<tr> 
				<th  align="LEFT" > <b> Purchase Order No. & Date.
				<br> ' . $PoNo . $PoDate . '</b>  </th>
				</tr>';







	$content .= '		<tr > 
				<th  align="LEFT"  > <b> Other Reference(s) :
				' . $ReferenceNo . '  </b>
				
				 </th></tr >';
	if ($showImporter) {

		$content .= '	<tr> 
           		
		
                <th  align="left" width="100%">
				<b><u> Importer of Record:</u></b> <br>  ' . $ImporterName . '
				<br> ' . nl2br($ImporterAddress) . '</th>
				</tr>';
	}
	if ($ShowCWiseBuyer) {
		if ($BuyerID != 0) {

			$content .= '	<tr> 
           		
                <th  align="left" width="100%">  <b><u>Buyer 
				(if other than consignee) :</b></u><br>' . $BuyerName . ',<br>' . nl2br($BuyerAddress) . '<br> </th>
				</tr>';
		}
	}


	$content .= '<tr >
				<th  align="LEFT"  > 
				 <u><b>' . $AryLables['Manufacturer'] . '</u> :</b><br> ' . $ProcessorName . '<br> ' . nl2br($ProcessorAddress) . '';
	if ($ShowCWiseApprovalNo) {
		$content .= '<br>  ' . $AryLables['ApprovalNo'] . ' ' . $ProcessorApprovalNo . '';
	}


	if ($ShowCWiseUsfda) {
		$content .= '<br> BIO TERRORISM / USFDA REGISTRATION NO.  ' . $ProcessorUsfdaNo;
	}

	/*if($showProFEI){			
																																																						 $content .= '<BR> FEI # :  '.$ProcessorFEI;
																																																						 }
																																																				 */
	$content .= ' </th></tr>';



	$content .= '	<tr> 
				<th border="1"  align="left" height="50%" width="49%">  Country of Origin of Goods 	
				<br><b align="center"> INDIA</b> </th>
				
				<th border="1"  align="left" width="51%">  Country of Final Destination  
				<br><b align="center">  ' . $DestinationCountry . '</b> </th>
				
				
				
               
				</tr>
				<tr> 
				
			
				
				<th  align="LEFT"  width="100%" > <b> <u>Terms of Payment:</u>
				</b><br><BR> ' . $PaymentTerms . ' <br>
				 
				 <!--SHIPMENT TERM : ' . $ShippingTerms . ' ' . strtoupper($PortOfDischarge) . ', ' .
		strtoupper($DestinationCountry) . ' -->
				';




	$content .= '  </th>
				</tr>
			<tr>  
           		
				<th  align="LEFT" ><b><u>CONTAINER NO. ' . $ContainerNo . '</b></u> <b style="color:white">______________</b>   <b><u>SEAL NO. ' . $SealNo . '</b></u>
                 <br><br>';
	if ($ShowBLNo) {
		$content .= '<b>' . $AryLables['BLNo'] . ' ' . $BLNo . '</b>';
	}
	if ($ShowBLDate) {
		$content .= '<b style="color:white">_________________</b>  <b>' . $AryLables['BLDate'] . '   ' . $BLDate . '</b>';
	}


	$content .= ' </th>
				</tr>
				<tr>  
           		
				<th  align="left" > <b  ><u> Shipping Term</u></b>
                  <br>
				 <span align="left"> <b>' . $ShippingTermsDisplay . ' ' . $ComShippingTermExt /* $FinalDestination.','.$DestinationCountryName*/ . '</span></b>
				
				
				 </th>
				</tr>
				
				</table>
				</th>
				
				</tr> ';




	$content .= '</table>';

	return $content;
}

function getBreakTable($iconn, $ID)
{
	//include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
	global $AryData, $style, $PType, $DocType, $AryLables, $AdminConfigDetails, $HasSauce, $BreakUpGradeIDs;
	$content = $style;


	$size = 45;
	$size1 = 55;






	$ExporterID = $AryData['ExporterID'];
	$ExporterName = $AryData['ExporterName'];
	$ExporterAddress = $AryData['ExporterAddress'];
	$ExporterUsfdaNo = $AryData['ExporterUsfdaNo'];
	$DestinationCountryId = $AryData['DestinationCountryId'];
	$ExporterIeCode = $AryData['ExporterIeCode'];
	$ExporterGstNo = $AryData['ExporterGstNo'];

	$ConsigneeID = $AryData['ConsigneeID'];
	$BuyerID = $AryData['BuyerID'];
	$ConsigneeName = $AryData['ConsigneeName'];
	$ConsigneeAddress = $AryData['ConsigneeAddress'];
	$BuyerName = $AryData['BuyerName'];
	$BuyerAddress = $AryData['BuyerAddress'];

	$PortOfDischarge = $AryData['PortOfDischarge'];
	$FinalDestination = $AryData['FinalDestination'];
	$TotalGrossWtKgs = $AryData['TotalGrossWtKgs'];


	$InvNo = $AryData['InvNo'];
	$InvDate = $AryData['InvDate'];
	$PoNo = $AryData['PoNo'];
	$PoDate = $AryData['PoDate'];
	$ShowBank = $AryData['ShowBank'];

	$ProcessorName = $AryData['ProcessorName'];
	$ProcessorAddress = $AryData['ProcessorAddress'];
	$ProcessorUsfdaNo = $AryData['ProcessorUsfdaNo'];
	$ProcessorFEI = $AryData['ProcessorFEI'];
	$ProcessorApprovalNo = $AryData['ProcessorApprovalNo'];

	$ShowShippingTerms = $AryData['ShowShippingTerms'];
	$ShowCWiseBuyer = $AryData['ShowCWiseBuyer'];
	$showImporter = $AryData['showImporter'];
	$showNotifyParty = $AryData['showNotifyParty'];
	$ShippingTerms = $AryData['ShippingTerms'];
	//echo $AryData['ComShippingTermExt']; exit;
	$ComShippingTermExt = $AryData['ComShippingTermExt'];
	$ProcessorIeCustomsNo = $AryData['ProcessorIeCustomsNo'];
	//$ExporterRecAdd=$AryData['ExporterRecAdd'];
	$NotifyPartyName = $AryData['NotifyPartyName'];
	$NPartyAddress = $AryData['NPartyAddress'];
	$NPartyContactNumber = $AryData['NPartyContactNumber'];
	$ImporterName = $AryData['ImporterName'];
	$ImporterAddress = $AryData['ImporterAddress'];

	$PaymentTerms = $AryData['PaymentTerms'];
	$PortOfDischarge = $AryData['PortOfDischarge'];
	$PortofLoading = $AryData['PortofLoading'];
	$DestinationCountry = $AryData['DestinationCountry'];
	//$PortofLoadingCountry=$AryData['PortofLoadingCountry'];
	$DeliveryTerms = $AryData['DeliveryTerms'];
	$ShowDeliveryTerms = $AryData['ShowDeliveryTerms'];
	$QtyUnit = $AryData['QtyUnit'];
	$UnitCurrency = $AryData['UnitCurrency'];
	$TotalMcs = $AryData['TotalMcs'];
	$ShipByDate = $AryData['ShipByDate'];
	$SignURL = $AryData['SignURL'];
	$AuthSign = $AryData['AuthSign'];
	$PreparedBy = $AryData['PreparedBy'];
	$BankID = $AryData['BankID'];
	$Remarks = $AryData['Remarks'];
	$showRemarks = $AryData['showRemarks'];
	$showExpUsFda = $AryData['showExpUsFda'];
	$showExpIECode = $AryData['showExpIECode'];
	$showExpGSTNo = $AryData['showExpGSTNo'];

	$ShowCWiseUsfda = $AryData['ShowCWiseUsfda'];
	$showProFEI = $AryData['showProFEI'];
	$showIgst = $AryData['showIgst'];
	$showDiscount = $AryData['showDiscount'];
	$TextSize = $AryData['TextSize'];

	if ($QtyUnit == 'KGS') {
		$Unit = 'KG';
	} elseif ($QtyUnit == 'LBS') {
		$Unit = 'LB';
	} else {
		$Unit = $QtyUnit;
	}

	if ($PType == 'UNITS') {
		$QtyUnit = 'UNITS';
		$Unit = 'UNITS';
		$HasSauce = 0;
	}



	$content = '
				<table border="1" cellspacing="0" cellpadding="2" nobr="true">
				<tr>
				<th  align="LEFT" width="58.5%" >   Description of Goods <b align="center"></b> </th>
                <th  align="center" width="13.5%" >
				 Quantity in <br>' . $QtyUnit . ' ' . $AryLables['UnitInBreakUp'] . '  </th>
                <th  align="center" width="13.5%" > Rate/' . $Unit . ' ' . $AryLables['UnitInBreakUp'] . ' <br>' . $UnitCurrency . ' </th>
                <th  align="center" width="14.5%" > Amount in ' . $UnitCurrency . '  </th>
                </tr>';

	$CurrentCount = 0;
	$GrandTotalWtKgs = 0;
	$GrandTotalWtLbs = 0;
	$GrandTotalUsdValue = 0;
	$GrandTotalCups = 0;

	// $SqlVarietyMcs="SELECT sum(McS) As VarietyMcs,BuyerItemCode,PrintStatus,
	// (SELECT CIDescription from ScrmCIGradeData WHERE PoGradeID=ScrmPoGradeData.ID) as Description,Variety,Treatment,Grade,
	//  PackingStyle,Brand,PackingStyleID,BAP FROM `ScrmPoGradeData` 
	//  left join `AdminPackingStyles` as Aps on PackingStyleID=Aps.PCKID
	//  left join `ScrmBrandsMaster` SBM on  SBM.BrandName=Brand
	//  WHERE InvId='$ID' GROUP by Variety,PackingStyleID,Brand 
	//  Order By ID asc";

	$SqlVarietyMcs = "SELECT sum(McS) As VarietyMcs,BuyerItemCode,PrintStatus,
			CIDescription as Description,Variety,Treatment,Grade,
			 PackingStyle,Brand,PackingStyleID,BAP FROM `ScrmCIGradeData` 
			 left join `AdminPackingStyles` as Aps on PackingStyleID=Aps.PCKID
			 left join `ScrmBrandsMaster` SBM on  SBM.BrandName=Brand
			 WHERE InvId='$ID' GROUP by CIDescription
			 Order By ID asc";
	$StmtVarietyMcs = $iconn->query($SqlVarietyMcs);
	$RowCount = mysqli_num_rows($StmtVarietyMcs);
	$FontSizedes = 8;
	if ($RowCount > 4) {
		$FontSizedes = 7;
	}

	$GrandTotalUsdValue = 0;
	while ($VarietyMcsRow = $StmtVarietyMcs->fetch_assoc()) {
		$CurrentCount++;


		$Variety = $VarietyMcsRow['Variety'];
		$Grade = $VarietyMcsRow['Grade'];
		$Brand = $VarietyMcsRow['Brand'];
		$BAP = $VarietyMcsRow['BAP'];
		$BuyerItemCode = $VarietyMcsRow['BuyerItemCode'];
		$BreakUpDescription = $VarietyMcsRow['Description'];
		$PackingStyleID = $VarietyMcsRow['PackingStyleID'];
		$Treatment = $VarietyMcsRow['Treatment'];
		$BrandPrintStatus = $VarietyMcsRow['PrintStatus'];
		if ($BAP == 'NA') {
			$BAP = '';
		} else {
			$BAP = $BAP;
		}
		if ($Treatment == '') {
			$Treatment = '';
		} else {
			$Treatment = '(' . $Treatment . ')';
			$Treatment = '';
		}




		$FontSize = "7px";


		$description_head = '<B><u>Description of Goods: </u></B><BR>1 X 40 FCL <BR>';
		$W_SPACE = '<b style="color:white"> yuhjutgfrtgujkiolmnbvgftrkliomkjhygtfrdeswderfgtyhjmnkih</b>';
		$Whitspace = '<b style="color:white"> yuhju</b>';
		$FontSize = "7";
		//$TOTAL_MCS=0;
		$content .= '
			';



		//$Description=$VarietyMcsRow['Variety'].''.$Treatment.'<br>'.$BreakUpDescription.'<br>';
		if ($BrandPrintStatus) {

			$ShowBrand = 'BRAND : ' . $VarietyMcsRow['Brand'] . '';
		}

		if ($BreakUpDescription != '') {
			$Description = $BreakUpDescription . '<br>';
		} else {
			$Description = $VarietyMcsRow['Variety'] . '' . $Treatment . '<br>';
		}

		if ($DestinationCountryId != 26) {
			$Description .= 'PACKING : ' . $VarietyMcsRow['PackingStyle'] . ' ' . $ShowBrand . '<B><br>' . $BAP . '</B>';
		} else {
			$Description .= '	PACKING : ' . $VarietyMcsRow['PackingStyle'] . ' ' . $ShowBrand . ' ';
		}

		if ($BreakUpDescription != '') {
			$Description = $BreakUpDescription . '<br>';
		}


		//$Description.='	PACKING : '.$VarietyMcsRow['PackingStyle'].''.$Whitspace.' ';	
		//$Description.='	PACKING : '.$VarietyMcsRow['PackingStyle'].''.$Whitspace.' ';	


		$content .= '
			<tr>
			<td width="58.5%" style="border-right: 1px solid black;" > <font size="' . $FontSizedes . '">' . nl2br($Description) . ' </font></td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;">  </td>
			<td align="center" width="13.5%" style="border-right: 1px solid black;">  </td>
			</tr>';
		if ($CurrentCount < 2) {

			$content .= '<tr>';
			/* if ($BuyerItemCode != '') {
				$content .= '
					<th align="center" width="10%" style="border-right:1px solid white;"  >  <u>Item Code</u> </th>
					<th align="center" width="10%" style="border-right:1px solid white;"  >  <u>SIZE</u> </th>';
			} else {
			} */
			$content .= '<th align="center" width="10%" style="border-right:1px solid white;"><font size="' . $FontSize . '">
			<B><u>SIZE</u></B></font></th>';
			// $content .= '<th align="center" width="13%" style="border-right:1px solid white;"><font size="' . $FontSize . '">
			// <B><u>PRODUCT</u></B></font></th>';
			$content .= '<th align="center" width="28%" style="border-right:1px solid white;"><font size="' . $FontSize . '">
			<B><u>PACKING</u></B></font></th>';
			$content .= '<th align="center" width="12.5%" style="border-right:1px solid white;"><font size="' . $FontSize . '">
			<B><u>NO. OF CTNS</u></B></font></th>
			<td align="center" width="8%" style="border-right:1px solid black;"><font size="' . $FontSize . '"></font></td>
			<td align="center" width="13.5%" style="border-right:1px solid black;"><font size="' . $FontSize . '"></font></td>
			<td align="center" width="13.5%" style="border-right:1px solid black;"><font size="' . $FontSize . '"></font></td>
			</tr>';
		}
		// echo  $SqlVariety="SELECT * FROM `ScrmPoGradeData` as PGD
		// left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE 
		// InvId='$ID' and Variety='$Variety' and Brand='$Brand' 
		// and	PackingStyleID='$PackingStyleID' order by abs(Grade)"; exit;  //uma

		$TotalWtKgs = 0;
		$TotalWtLbs = 0;
		$TotalVarietyUsdValue = 0;

		$SqlVariety = "SELECT * FROM `ScrmCIGradeData` as PGD
			left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE 
			InvId='$ID' and CIDescription='$BreakUpDescription' and Brand='$Brand' 
			and	PackingStyleID='$PackingStyleID' order by abs(Grade)";


		$StmtVariety = $iconn->query($SqlVariety);
		$ArySubBreakUp = array();

		while ($RowVariety = $StmtVariety->fetch_assoc()) {

			//$ArySubBreakUp[]=$RowVariety['ID']; 
			$ArySubBreakUp[] = $RowVariety['PoGradeID']; //uma


			$AryPriceBreakUp = getBreakUpData($iconn, $RowVariety['PoGradeID']);
			// echo json_encode($RowVariety);
			// echo "<br>present in break up";
			// echo json_encode($AryPriceBreakUp);
			// exit;
			$Qty = $AryPriceBreakUp['Qty'];
			$UsdValue = $AryPriceBreakUp['UsdValue'];
			$TotalMcs = $AryPriceBreakUp['TotalMcs'];
			$Price = $AryPriceBreakUp['Price'];
			$UnitPrice = $AryPriceBreakUp['UnitPrice'];
			$IsSauce = checkSauceExists($iconn, $ID);
			if ($IsSauce && $IsSauce == 1) {
				$AryPaymentData = getShrimpPaymentValues($iconn, $ID, $DocType, $AdminConfigDetails, $PType);
			} else {
				$AryPaymentData = getPaymentValues($iconn, $ID, $DocType, $AdminConfigDetails);
			}
			// $AryPaymentData = getPaymentValues($iconn, $ID, $DocType, $AdminConfigDetails);

			//echo $DocType; exit;
			$InvValueUSD = $AryPaymentData['InvValueUSD'];
			$InvValueInr = $AryPaymentData['InvValueINR'];
			$ExchangeRate = $AryPaymentData['ExchangeRate'];
			$DiscountPerc = $AryPaymentData['DiscountPerc'];
			$DiscountAmount = $AryPaymentData['DiscountAmount'];
			$IgstPerc = $AryPaymentData['IgstPerc'];
			$IgstValue = $AryPaymentData['IgstValue'];

			$AryShrimpData = getShrimpData($iconn, $RowVariety['VarietyID']);
			$ArySauceData = getSauceData($iconn, $RowVariety['VarietyID']);
			$AryPoGradeData = getPoGradeData($iconn, $RowVariety['PoGradeID']);
			$AryShrimpPacking = getShrimpBreakUpData($iconn, $RowVariety['PackingStyleID'], $RowVariety['PoGradeID']);
			$TotalVarietyUsdValue += $AryShrimpPacking['UsdValue'];

			$currentRowTotalUsdValue = $AryShrimpPacking['UsdValue'];
			/*uma if($PType=='KGS'){
																																																																																																																																																													   $Price=sprintf("%0.7f",$UnitPrice);
																																																																																																																																																												   }else{
																																																																																																																																																													   $Price=sprintf("%0.2f",$Price);
																																																																																																																																																												   }*/




			$content .= '<tr>';

			/* if ($BuyerItemCode != '') {
				$content .= '
				<td align="center" width="10%" style="border-right:1px solid white;"  > 
				' . $RowVariety['BuyerItemCode'] . ' 
				</td><td align="center" width="10%" style="border-right:1px solid white;"  > 
				' . $RowVariety['Grade'] . ' 
				</td>';
			} else {
			} */

			$content .= '<td align="center" width="10%"  style="border-right:1px solid white;" > 
				 ' . $RowVariety['Grade'] . ' 
				 </td>';

			//<td align="center" width="20%" style="border-right:1px solid black;"  > 
			//		'.$RowVariety['Brand'].'   </td>
			// <td align="center" width="13.5%" style="border-right:1px solid black;"  > 
			// 	' . number_format($AryShrimpPacking['Qty'], 3) . '  </td>
			// <td align="center" width="14.5%" style="border-right:1px solid black;" > 
			// 	' . number_format($AryShrimpPacking['UsdValue'], 2) . ' </td>
			$pricePlaceholder = '{{PRICE_' . $RowVariety['PoGradeID'] . '}}';
			$usdValuePlaceholder = '{{USDVALUE_' . $RowVariety['PoGradeID'] . '}}';
			// $content .= '
			// 	<td align="center" width="13%" style="border-right:1px solid white;"><B>SHRIMP</B></td>';

			if ($HasSauce && $ArySauceData && $ArySauceData['HasSauce'] == 'Yes') {
				// echo $PType;exit;

				$SauceQty = $TotalMcs * $ArySauceData['SauceTotalSlabs'] * $ArySauceData['SauceSlabWeight'];
				if ($ArySauceData['SauceUom'] == 'OZ') {
					$SauceQty = $SauceQty * $ArySauceData['SauceLbsFactor'];
					if ($PType != 'LBS') {
						$SauceQty = $SauceQty * 0.454;
					}
				} else if ($ArySauceData['SauceUom'] == 'LBS') {
					$SauceQty = $SauceQty * 0.454;
				}

				$TotalWtKgs += $SauceQty;

				$SauceUsdValue = $SauceQty * $AryPoGradeData['CupsPrice'];
				// if ($PType == 'LBS') {
				//     $SauceUsdValue = $SauceUsdValue * 0.454;
				// }
				if ($QtyUnit == 'LBS' && $PType == 'KGS') {
					// $RowVariety['CupsPrice'] = sprintf("%0.7f", $RowVariety['CupsPrice'] / 0.454);
					$SauceUsdValue = $SauceUsdValue / 0.454;
				}
				$TotalVarietyUsdValue += $SauceUsdValue;
				$SauceTotalUsdValue += $SauceUsdValue;
				$currentRowTotalUsdValue += $SauceUsdValue;
				$GrandTotalCups += $AryPoGradeData['CUPS'];

				// $content .= '<tr>';

				// $content .= '<td align="center" width="10%"  style="border-right:1px solid white;" >
				// </td>
				// <td align="center" width="13%" style="border-right:1px solid white;"  >
				// <B>SAUCE</B>
				// </td>';
				// $content .= '<td align="center" width="15%" style="border-right:1px solid white;"  >
				// ' . $ArySauceData['SaucePackingStyle'] . '</td>
				// <td align="center" width="12.5%" style="border-right:1px solid white;"  >
				// ' . $AryPoGradeData['CUPS'] . '</td>
				// <td align="center" width="8%" style="border-right:1px solid black;"  >CUPS</td>';

				// if ($QtyUnit == 'LBS' && $PType == 'KGS') {
				// 	$AryPoGradeData['CupsPrice'] = sprintf("%0.7f", $AryPoGradeData['CupsPrice'] / 0.454);
				// }

				// $content .= '
				// <td align="center" width="13.5%" style="border-right:1px solid black;"  >
				// ' . number_format($SauceQty, 3) . '</td>
				// <td align="center" width="13.5%" style="border-right:1px solid black;"  >
				// ' . $AryPoGradeData['CupsPrice'] . '
				// </td>
				// <td align="center" width="14.5%" style="border-right:1px solid black;" >
				// ' . number_format($SauceUsdValue, 2) . '</td>
				// </tr>';
			}



			$pricePlaceholder = $currentRowTotalUsdValue / $Qty;

			$content .= '
				<td align="center" width="28%" style="border-right:1px solid white;"  > 
				' . $VarietyMcsRow['PackingStyle'] . '</td>
				<td align="center" width="12.5%" style="border-right:1px solid white;"  > 
				' . $TotalMcs . '   </td>
				<td align="center" width="8%" style="border-right:1px solid black;">M/CS</td>
				<td align="center" width="13.5%" style="border-right:1px solid black;"  > 
				' . number_format($Qty, 3) . '  </td>
				<td align="center" width="13.5%" style="border-right:1px solid black;"  > 
				' . number_format($pricePlaceholder, 2) . '</td>
				<td align="center" width="14.5%" style="border-right:1px solid black;" > 
				' . number_format($currentRowTotalUsdValue, 2) . '</td>
				</tr>';

			$GrandTotalWtKgs += $TotalWtKgs;
			$GrandTotalWtLbs += $TotalWtLbs;
			$GrandTotalUsdValue += $currentRowTotalUsdValue;
		}

		$SubVarityBrkUp = implode(',', $ArySubBreakUp);

		$BreakUpGradeIDs .= $SubVarityBrkUp . ',';

		$AryPriceData = getBreakUpData($iconn, $SubVarityBrkUp);
		$Qty = $AryPriceData['Qty'];
		// $content = str_replace(
		// 	$usdValuePlaceholder,
		// 	number_format($TotalVarietyUsdValue, 2),
		// 	$content
		// );
		$ShrimpSauceUnitPrice = $TotalVarietyUsdValue / $Qty;
		// $content = str_replace(
		// 	$pricePlaceholder,
		// 	number_format($ShrimpSauceUnitPrice, 5),
		// 	$content
		// );
		$UsdValue = $AryPriceData['UsdValue'];
		$TotalMcs = $AryPriceData['TotalMcs'];
		$Price = $AryPriceData['Price'];
		if ($RowCount > 1) {
			$content .= '
				<tr>
				<td align="center" width="20%"  style="border-right:1px solid white;" > 
				<b>Total</b	>
				 </td>
				 <td align="center" width="20%" style="border-right:1px solid white;">
														
														  </td>
				<td align="center" width="8.5%" style="
				border-top:1px solid black;
				
				" >
														
														 
				' . $TotalMcs . ' </td>
				
				<td align="center" width="10%" style="border-right:1px solid black;">
														
														  </td>
				<td align="center" width="13.5%" > 
				' . number_format($Qty, 3) . ' </td>
				<td align="center" width="13.5%" style="border-right:1px solid black;">
														  </td>
				<td align="center" width="14.5%" style="border-top:1px solid black;
				border-bottom:1px solid black;">
														 
				' . number_format($TotalVarietyUsdValue, 2) . ' </td>
				
				</tr>';
		}
	}

	$BreakUpGradeIDs = rtrim($BreakUpGradeIDs, ',');
	$AryPriceData = getBreakUpData($iconn, $BreakUpGradeIDs, $ID);
	$Qty = $AryPriceData['Qty'];
	$UsdValue = $AryPriceData['UsdValue'];
	$TotalMcs = $AryPriceData['TotalMcs'];
	$Price = $AryPriceData['Price'];

	$content .= ' 
				<tr>
				
				<td align="center" width="20%" style="border-top: 1px solid black;" > <B>
				GRAND TOTAL </B></td>
				<td align="center" width="18.5%" style="border-top: 1px solid black; " > 
				' . $TotalMcs . ' </td>
				<td align="center" width="20%" style="border-top: 1px solid black; border-right: 1px solid black;" > 
				 </td>
				<td align="center" width="13.5%" style="border-top: 1px solid black;border-right: 1px solid black;"> 
				' . number_format($Qty, 3) . ' </td>
				<td align="center" width="13.5%" style="border-top: 1px solid black;border-right: 1px solid black;" > 
				 </td>
				<td align="center" width="14.5%" style="
				border-top: 1px solid black;
				border-bottom: 1px solid black;
				border-right: 1px solid black;"> 
				<b>' . number_format($GrandTotalUsdValue, 2) . '</b> </td>
				
				
				</tr>';


	//$showDiscount=true;
	if ($showDiscount) {
		$content .= '	<tr>
				<td align="right" width="85.5%" style="border-top: 1px solid black;border-right: 1px solid black;" > <B>
				LESS TRADE DISCOUNT ' . $DiscountPerc . '%</B> </td>
				<td align="center" width="14.5%" style="border-top: 1px solid black;" > <B>
				' . number_format($DiscountAmount, 2) . '</B> </td>
				</tr><tr>
				<td align="right" width="85.5%" style="border-top: 1px solid black;
				border-bottom: 1px solid black;border-right: 1px solid black;" > <B>
				TOTAL AMOUNT:</B> </td>
				<td align="center" width="14.5%" style="border-top: 1px solid black;
				border-bottom: 1px solid black;" > <B>
				' . number_format($InvValueUSD, 2) . '</B> </td>
				</tr>';
	}

	//Static DFL # Guru Prasad Dont want Display
	$showIgst = false;
	if ($showIgst) {
		$content .= '	<tr> 
				<td align="right" width="85.5%" style="border-top: 1px solid black;border-right: 1px solid black;" > <B>
				INVOICE VALUE IN R.S@' . $ExchangeRate . '  </B> </td>
				<td align="center" width="14.5%" style="border-top: 1px solid black;" > <B>
				' . number_format($InvValueInr, 2) . '</B> </td>
				</tr>
				<tr>
				<td align="right" width="85.5%" style="border-top: 1px solid black;;border-right: 1px solid black;border-bottom: 1px solid black;" > <B>
				IGST@' . $IgstPerc . ' % </B> </td>
				<td align="center" width="14.5%" style="border-top: 1px solid black;border-bottom: 1px solid black;" > <B>
				' . number_format($IgstValue, 2) . '</B> </td>
				</tr>';
	}

	$content .= '</table>';

	return $content;
}




function getFooterTable($iconn, $ID)
{
	//include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

	global $PType, $PoID, $AryData, $style, $DocType, $AdminConfigDetails, $HasCLFCharges, $BreakUpGradeIDs, $HasTariff;
	$content = $style;


	$size = 45;
	$size1 = 55;




	$AryPriceData = getBreakUpData($iconn, $BreakUpGradeIDs, $ID);
	$Qty = $AryPriceData['Qty'];
	$UsdValue = $AryPriceData['UsdValue'];
	$TotalMcs = $AryPriceData['TotalMcs'];
	$Price = $AryPriceData['Price'];
	$GlazePerc = $AryPriceData['GlazePerc'];
	$ProductWeightWOGLbs = $AryPriceData['ProductWeightWOGLbs'];
	$ProductWeightWOGKgs = $AryPriceData['ProductWeightWOGKgs'];
	$WtPerCtnTxt = $AryPriceData['WtPerCtnTxt'];


	//	print_r($AryPriceData); exit;


	$ExporterID = $AryData['ExporterID'];
	$ExporterName = $AryData['ExporterName'];
	$ExporterAddress = $AryData['ExporterAddress'];
	$ExporterUsfdaNo = $AryData['ExporterUsfdaNo'];
	$DestinationCountryId = $AryData['DestinationCountryId'];
	$ExporterIeCode = $AryData['ExporterIeCode'];
	$ExporterGstNo = $AryData['ExporterGstNo'];
	$TotalGrossWtKgs = $AryData['TotalGrossWtKgs'];
	$UnitCurrency = $AryData['UnitCurrency'];

	$ConsigneeID = $AryData['ConsigneeID'];
	$BuyerID = $AryData['BuyerID'];
	$ConsigneeName = $AryData['ConsigneeName'];
	$ConsigneeAddress = $AryData['ConsigneeAddress'];
	$BuyerName = $AryData['BuyerName'];
	$BuyerAddress = $AryData['BuyerAddress'];

	$PortOfDischarge = $AryData['PortOfDischarge'];
	$FinalDestination = $AryData['FinalDestination'];
	$CIDescriptionspl = $AryData['CIDescriptionspl'];
	$CurrenySymbol = $AryData['CurrenySymbol'];


	$InvNo = $AryData['InvNo'];
	$InvDate = $AryData['InvDate'];
	$PoNo = $AryData['PoNo'];
	$PoDate = $AryData['PoDate'];
	$ShowBank = $AryData['ShowBank'];
	$ShowCWiseBankDetails = $AryData['ShowCWiseBankDetails'];
	$ShowCWiseProductSpecification = $AryData['ShowCWiseProductSpecification'];
	$ShowCWiseSpecification = $AryData['ShowCWiseSpecification'];

	$ProcessorName = $AryData['ProcessorName'];
	$ProcessorAddress = $AryData['ProcessorAddress'];
	$ProcessorUsfdaNo = $AryData['ProcessorUsfdaNo'];
	$ProcessorFEI = $AryData['ProcessorFEI'];
	$ProcessorApprovalNo = $AryData['ProcessorApprovalNo'];

	$ShowShippingTerms = $AryData['ShowShippingTerms'];
	$ShowCWiseBuyer = $AryData['ShowCWiseBuyer'];
	$showImporter = $AryData['showImporter'];
	$showNotifyParty = $AryData['showNotifyParty'];
	$ShippingTerms = $AryData['ShippingTerms'];
	$ProcessorIeCustomsNo = $AryData['ProcessorIeCustomsNo'];
	//$ExporterRecAdd=$AryData['ExporterRecAdd'];
	$NotifyPartyName = $AryData['NotifyPartyName'];
	$NPartyAddress = $AryData['NPartyAddress'];
	$NPartyContactNumber = $AryData['NPartyContactNumber'];
	$ImporterName = $AryData['ImporterName'];
	$ImporterAddress = $AryData['ImporterAddress'];

	$PaymentTerms = $AryData['PaymentTerms'];
	$PortOfDischarge = $AryData['PortOfDischarge'];
	$PortofLoading = $AryData['PortofLoading'];
	$DestinationCountry = $AryData['DestinationCountry'];
	//$PortofLoadingCountry=$AryData['PortofLoadingCountry'];
	$DeliveryTerms = $AryData['DeliveryTerms'];
	$ShowDeliveryTerms = $AryData['ShowDeliveryTerms'];
	$QtyUnit = $AryData['QtyUnit'];
	$UnitCurrency = $AryData['UnitCurrency'];
	$TotalMcs = $AryData['TotalMcs'];
	$ShipByDate = $AryData['ShipByDate'];
	$SignURL = $AryData['SignURL'];
	$AuthSign = $AryData['AuthSign'];
	$PreparedBy = $AryData['PreparedBy'];
	$BankID = $AryData['BankID'];
	$Remarks = $AryData['Remarks'];
	$showRemarks = $AryData['showRemarks'];
	$showExpUsFda = $AryData['showExpUsFda'];
	$showExpIECode = $AryData['showExpIECode'];
	$showExpGSTNo = $AryData['showExpGSTNo'];

	$ShowCWiseUsfda = $AryData['ShowCWiseUsfda'];
	$showProFEI = $AryData['showProFEI'];
	$ShowBank = $AryData['ShowBank'];
	$ShowShippingDetails = $AryData['ShowShippingDetails'];
	$ShowInvoicevalue = $AryData['ShowInvoicevalue'];
	$ShowFrightCharge = $AryData['ShowFrightCharge'];
	$UnitCurrency = $AryData['UnitCurrency'];
	$ComDescriptionSpl = $AryData['ComInvDescription'];
	$ShowCWiseWeightPerctText = $AryData['ShowCWiseWeightPerctText'];
	$ShowCWiseComSplNote = $AryData['ShowCWiseComSplNote'];
	$ShowCWiseComDesNote = $AryData['ShowCWiseComDesNote'];

	// echo $ShowShippingDetails;

	// $ShowCWiseBankDetails = 0;


	$AryBankData = getBankDetails($iconn, $BankID);

	//print_r($AryBankData); exit;


	$BenfName = $AryBankData['AccntName'];
	$BenfAccNo = $AryBankData['AccountNumber'];
	$BenfBankName = $AryBankData['BankName'];
	$BenfBankAddress = $AryBankData['BankAddress'];
	$BenfBankCode = $AryBankData['SwiftCode'];

	$BankDetails = $AryBankData['BankDetails'];
	$RoutingThrough = $AryBankData['RoutingThrough'];
	$NegotiatingBank = $AryBankData['NegotiatingBank'];


	// $BILL_WORD = numberTowords($UsdValue);
	// $CurrenySymbol=$AryData['CurrenySymbol'];





	$NetWgtKgs = $AryPriceData['NetWgtKgs'];
	$NetWgtLbs = $AryPriceData['NetWgtLbs'];
	$GrossWeightKgs = $AryPriceData['GrossWeightKgs'];
	$GrossWeightLbs = $AryPriceData['GrossWeightLbs'];

	$IsSauce = checkSauceExists($iconn, $ID);
	$AdminConfigDetails['HasCLFCharges'] = $HasCLFCharges;
	if ($IsSauce && $IsSauce == 1) {
		$AryPaymentData = getShrimpPaymentValues($iconn, $ID, $DocType, $AdminConfigDetails, $PType);
	} else {
		$AryPaymentData = getPaymentValues($iconn, $ID, $DocType, $AdminConfigDetails);
	}

	//print_r($AryPaymentData); exit;
	$FrieghtCharge = $AryPaymentData['FrieghtCharge'];
	$DiscountAmount = $AryPaymentData['DiscountAmount'];
	$InvValueUSDWithOutDuties = $AryPaymentData['InvValueUSDWithOutDuties'];
	$InvValueINRWithOutDuties = $AryPaymentData['InvValueINRWithOutDuties'];
	$DutiesPrice = $AryPaymentData['DutiesPrice'];
	$FOBValueUSD = $AryPaymentData['FOBValueUSD'];
	$AntiDumpingDuty = round($AryPaymentData['AntiDumpingDuty'], 2);
	$MerchantDuty = round($AryPaymentData['MerchantDuty'], 2);
	$HarborDuty = round($AryPaymentData['HarborDuty'], 2);
	$FOBValueINR = round($AryPaymentData['FOBValueINR']);
	$InvValueUSD = $AryPaymentData['InvValueUSD'];
	$ISFStatic = $AryPaymentData['ISFStatic'];
	$AntiDumpingDutyPerc = $AryPaymentData['AntiDumpingDutyPerc'];
	$HarbourMaintenanceFeePerc = $AryPaymentData['HarbourMaintenanceFeePerc'];
	$CountervailingDutyPerc = $AryPaymentData['CountervailingDutyPerc'];
	$MerchandiseProcessFeePerc = $AryPaymentData['MerchandiseProcessFeePerc'];
	$CountervailingDuty = round($AryPaymentData['CountervailingDuty'], 2);
	$ReciprocalTariffsPerc = $AryPaymentData['ReciprocalTariffsPerc'];
	$ReciprocalTariffsFee = round($AryPaymentData['ReciprocalTariffsFee'], 2);
	$ClearingAndForwardingCharges = $AryPaymentData['ClearingAndForwardingCharges'];
	$TariffPerc = $AryPaymentData['TariffPerc'];
	$TariffFee = round($AryPaymentData['TariffFee'], 2);
	$PrintTariff = $AryPaymentData['PrintTariff'];
	$PrintReciprocalTariff = $AryPaymentData['PrintReciprocalTariff'];

	if ($CurrenySymbol == '₹') {
		$BILL_WORD = convert_rupees_to_words($InvValueUSD);
	} else {
		$BILL_WORD = numberTowords($InvValueUSD);
	}
	// echo print_r($AryPaymentData);
	// exit;



	$ShowInvoicevalue = true;

	$content .= $style;

	$content .= '
    <table border="1" cellspacing="0" cellpadding="2">
        <tr>
            <td width="58.5%">
                <table border="0" cellspacing="0" cellpadding="0">';


	if ($ShowFrightCharge) {
		$content .= '<tr>
        <td width="1%"></td>
        <td align="left" width="70%">INVOICE VALUE</td>
        <td align="left" width="10%"><b>:</b></td>
        <td width="20%">
			<table width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td width="10%" align="left"><b>$</b></td>
					<td width="75%" align="right"><b>' . number_format($InvValueUSD, 2) . '</b></td>
				</tr>
			</table>
		</td>
    	</tr>';

		$content .= '<tr>
        <td width="1%"></td>
        <td align="left" width="70%">LESS : OCEAN FREIGHT</td>
        <td align="left" width="10%"><b>:</b></td>
        <td width="20%">
			<table width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td width="10%" align="left"><b>$</b></td>
					<td width="75%" align="right"><b>' . number_format($FrieghtCharge, 2) . '</b></td>
				</tr>
			</table>
		</td>
    	</tr>';

		if ($HasCLFCharges && $ClearingAndForwardingCharges > 0) {
			$content .= '<tr>
				<td width="1%"></td>
				<td align="left" width="70%">LESS : CLEARING & FORWARDING CHARGES</td>
				<td align="left" width="10%"><b>:</b></td>
				<td width="20%" style="border-bottom:1px solid black;margin:0 auto;">
					<table width="100%" cellpadding="0" cellspacing="0">
						<tr>
							<td width="10%" align="left"><b>$</b></td>
							<td width="75%" align="right"><b>' . number_format($ClearingAndForwardingCharges, 2) . '</b></td>
						</tr>
					</table>
				</td>
				</tr>';
		}

		if ($ISFStatic) {
			$content .= '<tr>
            <td width="1%"></td>
            <td align="left" width="70%">LESS : MERCHANDISE PROCESS FEE @ 0.3464%</td>
            <td align="left" width="10%"><b>: </b></td>
            <td align="left" width="20%"><b> $ ' . number_format($MerchantDuty, 2) . '</b></td>
        	</tr>';
		}

		$content .= '<tr>
        <td width="1%"></td>
        <td align="left" width="70%"><b>SUB TOTAL</b></td>
        <td align="left" width="10%"><b>: </b></td>
        <td width="20%" align="left">
			<table width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td width="10%" align="left"><b>$</b></td>
					<td width="75%" align="right">
						<b>' . number_format($InvValueUSDWithOutDuties, 2) . '</b>
					</td>
				</tr>
			</table>
		</td>
    	</tr>';

		$content .= '<tr>
        <td width="1%"></td>
        <td align="left" width="70%">LESS : ANTI-DUMPING DUTY @ ' . $AntiDumpingDutyPerc . '%</td>
		<td width="30%" align="left">
			<table width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td width="15%" align="left"><b>: $</b></td>
					<td width="40%" align="right">
						<b>' . number_format($AntiDumpingDuty, 2) . '</b>
					</td>
				</tr>
			</table>
		</td>
    	</tr>';

		if ($CountervailingDutyPerc != 0) {
			$content .= '<tr>
            <td width="1%"></td>
            <td align="left" width="70%">LESS : COUNTERVAILING DUTY @ ' . $CountervailingDutyPerc . '%</td>
            <td width="30%" align="left">
				<table width="100%" cellpadding="0" cellspacing="0">
					<tr>
						<td width="15%" align="left"><b>: $</b></td>
						<td width="40%" align="right">
							<b>' . number_format($CountervailingDuty, 2) . '</b>
						</td>
					</tr>
				</table>
			</td>
        	</tr>';
		}

		if (!$ISFStatic) {
			$content .= '<tr>
            <td width="1%"></td>
            <td align="left" width="70%">LESS : MERCHANDISE PROCESS FEE @ ' . $MerchandiseProcessFeePerc . '% </td>
            <td width="30%" align="left">
				<table width="100%" cellpadding="0" cellspacing="0">
					<tr>
						<td width="15%" align="left"><b>: $</b></td>
						<td width="40%" align="right">
							<b>' . number_format($MerchantDuty, 2) . '</b>
						</td>
					</tr>
				</table>
			</td>
        	</tr>';
		}

		$content .= '<tr>
        <td width="1%"></td>
        <td align="left" width="70%">LESS : HARBOR MAINTENANCE FEE @ ' . $HarbourMaintenanceFeePerc . '% </td>
        <td width="30%" align="left">
			<table width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td width="15%" align="left"><b>: $</b></td>
					<td width="40%" align="right">
						<b>' . number_format($HarborDuty, 2) . '</b>
					</td>
				</tr>
			</table>
		</td>
    	</tr>';

		if ($PrintReciprocalTariff && $PrintReciprocalTariff == 1 && $ReciprocalTariffsPerc > 0) {
			$content .= '
			<tr>
			<td  width="1%"></td>
			<td align="left" width="70%">LESS : RECIPROCAL TARIFFS @ ' . $ReciprocalTariffsPerc . '% </td>
			<td width="30%" align="left">
				<table width="100%" cellpadding="0" cellspacing="0">
					<tr>
						<td width="15%" align="left"><b>: $</b></td>
						<td width="40%" align="right">
							<b>' . number_format($ReciprocalTariffsFee, 2) . '</b>
						</td>
					</tr>
				</table>
			</td>
			</tr>';
		}

		if ($PrintTariff && $PrintTariff == 1 && $HasTariff && $TariffFee > 0) {
			$content .= '
			<tr>
			<td  width="1%"></td>
			<td align="left" width="70%">LESS : TARIFF @ ' . $TariffPerc . '% </td>
			<td width="30%" align="left">
				<table width="100%" cellpadding="0" cellspacing="0">
					<tr>
						<td width="15%" align="left"><b>: $</b></td>
						<td width="45%" align="right">
							<b>' . number_format($TariffFee, 2) . '</b>
						</td>
					</tr>
				</table>
			</td>
			</tr>';
		}

		$content .= '<tr>
        <td width="1%"></td>
        <td align="left" width="70%"><b>TOTAL DUTY</b></td>
		<td align="left" width="10%"><b>:</b></td>
        <td width="20%">
			<table width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td width="10%" align="left"><b>$</b></td>
					<td width="75%" align="right">
						<b>' . number_format($DutiesPrice, 2) . '</b>
					</td>
				</tr>
			</table>
		</td>
    	</tr>';

		$content .= '<tr>
        <td width="1%"></td>
        <td align="left" width="70%"><b>FOB VALUE IN USD</b></td>
		<td align="left" width="10%"><b>: </b></td>
		<td style="border-bottom:1px solid black; border-top:1px solid black; width:20%; margin:0 auto;">
			<table width="100%" cellpadding="0" cellspacing="0">
				<tr>
					<td width="10%" align="left"><b>$</b></td>
					<td width="75%" align="right">
						<b>' . number_format($FOBValueUSD, 2) . '</b>
					</td>
				</tr>
			</table>
		</td>
    	</tr>';
	} elseif ($ShowInvoicevalue) {
		$content .= '<tr>
        <td width="1%"></td>
        <td align="left" width="35%">INVOICE VALUE ' . htmlspecialchars($UnitCurrency) . ' ' . htmlspecialchars($CurrenySymbol) . ' </td>
        <td align="left" width="15%"><b>: </b></td>
        <td align="left" width="60%"><b>' . number_format($InvValueUSD, 2) . '</b></td>
    </tr>';
	} else {
		$content .= '<tr>
        <td width="1%"></td>
        <td align="left" width="35%">FOB VALUE IN US $ </td>
        <td align="left" width="15%"><b>: </b></td>
        <td align="left" width="60%"><b>' . number_format($InvValueUSDWithOutDuties, 2) . '</b></td>
    </tr>';
	}




	$NetWgtKgs = $AryPriceData['NetWgtKgs'];
	$NetWgtLbs = $AryPriceData['NetWgtLbs'];
	$GrossWeightKgs = $AryPriceData['GrossWeightKgs'];
	$GrossWeightLbs = $AryPriceData['GrossWeightLbs'];
	if ($TotalGrossWtKgs != '') {
		if ($QtyUnit == 'KGS') {
			$GrossWeightKgs = $TotalGrossWtKgs;
			$GrossWeightLbs = $TotalGrossWtKgs * 2.204;
		} else {
			$GrossWeightKgs = $TotalGrossWtKgs * 0.454;
			$GrossWeightLbs = $TotalGrossWtKgs;
		}
	} else {
		$GrossWeightKgs = $AryPriceData['GrossWeightKgs'];
		$GrossWeightLbs = $AryPriceData['GrossWeightLbs'];
	}


	//	echo $GlazePerc; exit;



	$content .= '</table></td>';

	$content .= '<td width="41.5%">
			
			<table border="0" cellspacing="0" cellpadding="1" nobr="true" style="">';

	if ($QtyUnit == 'KGS') {
		$content .= '<tr>
			<td align="left" width="55%" style="font-size: 7px;border-bottom: 1px solid #333333;border-right: 1px solid #333333"> <b>GROSS WEIGHT:</b>  </td>
			<td align="left" width="45%"  style="border-bottom: 1px solid #333333;">  <b>' . number_format($GrossWeightKgs, 3) . ' KGS</b> </td>
			</tr>';
		$content .= '<tr>
			<td align="left" width="55%"  style="font-size: 7px;border-bottom: 1px solid #333333;border-right: 1px solid #333333"> <b>NET WEIGHT: </b> </td>
			<td align="left" width="45%"  style="border-bottom: 1px solid #333333;">  <b>' . number_format($NetWgtKgs, 3) . ' KGS</b> </td>
			</tr>';

		if (getIsGlazeInvoice($iconn, $ID)) {
			$content .= '<tr>
			<td align="left" width="55%"  style="font-size: 7px;border-bottom: 1px solid #333333;border-right: 1px solid #333333"> <b>NET WEIGHT WITHOUT GLAZE </b> </td>
			<td align="left" width="45%"  style="border-bottom: 1px solid #333333;">  <b> ' . number_format($ProductWeightWOGKgs, 3) . ' KGS</b> </td>
			</tr>';
		}
	} else {
		$content .= '<tr>
			<td align="left" width="55%"  style="font-size: 7px;border-bottom: 1px solid #333333;border-right: 1px solid #333333"> GROSS WEIGHT  </td>
			<td align="left" width="45%"  style="border-bottom: 1px solid #333333;">  <b>: ' . number_format($GrossWeightLbs, 3) . ' LBS</b> </td>
			<!--<td align="left" width="35%" >  <b> ' . number_format($GrossWeightKgs, 3) . ' KGS</b> </td>-->
			</tr>';

		$content .= '<tr>
			<td align="left" width="55%"  style="font-size: 7px;border-bottom: 1px solid #333333;border-right: 1px solid #333333"> NET WEIGHT  </td>
			<td align="left" width="45%"  style="border-bottom: 1px solid #333333;">  <b>: ' . number_format($NetWgtLbs, 3) . ' LBS</b> </td>
			<!--<td align="left" width="35%" >  <b> ' . number_format($NetWgtKgs, 3) . ' KGS</b> </td>-->
			</tr>';

		if ($GlazePerc != 0) {
			$content .= '<tr>
			<td align="left" width="55%"  style="font-size: 7px;border-bottom: 1px solid #333333;border-right: 1px solid #333333"> <b>NET WEIGHT WITHOUT GLAZE</b>  </td>
			<td align="left" width="45%"  style="border-bottom: 1px solid #333333;">  <b> ' . number_format($ProductWeightWOGLbs, 3) . ' LBS</b> </td>
			</tr>';
		}
	}



	$content .= '</table>';



	$content .= '</td></tr></table>';








	$content .= '<table border="1" cellspacing="0" cellpadding="5" nobr="true"><tr>';

	$formated_date = date("Y-m-d", strtotime($InvDate));
	$AryDescription = getInvoiceDescription($iconn, $formated_date);
	$CommercialNote = $AryDescription['CommercialNote'];

	$ArrayProductDescription = getProductSpec($iconn, $ID);



	$content .= '
				<table border="1" cellspacing="0" cellpadding="2" nobr="true">
				<tr>  
           		<td  align="LEFT"  width="100%"> <b> AMOUNT CHARGABLE (IN ' . $UnitCurrency . '): 
				(' . $BILL_WORD . ' ONLY)</b> <BR> 
				 ';
	if ($ShowCWiseWeightPerctText) {
		$content .= '' . nl2br($WtPerCtnTxt) . '<br>';
	}
	if ($ShowCWiseProductSpecification) {
		//$content.=getProductSpec($iconn,$ID);
		$content .= $ArrayProductDescription['Desc'];
	}

	if ($ShowCWiseSpecification) {
		// Added this Consignee condition 28-04-2025
		if ($ConsigneeID != '' && $ConsigneeID == '53' && $ArrayProductDescription['Speciesdesc'] === 'LITOPENAEUS VANNAMEI') {
			$content .= ' SPECIES : ' . 'PENAEUS VANNAMEI';
		} else {
			$content .= ' SPECIES : ' . $ArrayProductDescription['Speciesdesc'];
		}
	}



	$content .= '
				
				 
				</td> 
				</tr>
				</table>';

	//$ShowBank=true;
	if ($ShowBank && $ShowCWiseBankDetails) {
		$content .= '
				<table border="1" cellspacing="0" cellpadding="2" nobr="true">
				<tr><td style="margin-left: 1em;">
				<b>BANK DETAILS  </b>' . $BankDetails . '';
		$content .= '<br><b>Negotiating BANK  </b>' . $NegotiatingBank;
		$content .= '<br><b>ROUTING THROUGH   </b>' . $RoutingThrough . '
				</td></tr></table>';
	}







	$content .= '</tr><tr>  
           		<th  align="LEFT"  width="100%">';
	$ComDescriptionSpl = str_replace(' ', '&nbsp;', $ComDescriptionSpl);
	//if($CommercialNote==''){
	if ($CommercialNote != '' && $ShowCWiseComDesNote) {
		//echo $CommercialNote; 
		//echo $ComDescriptionSpl; exit;
		$content .= ' 
				 ' . nl2br($CommercialNote) . '<br> ';
	}
	if ($ComDescriptionSpl != '' && $ShowCWiseComSplNote) {
		$content .= ' <br>' . nl2br($ComDescriptionSpl) . '<br> ';
	}
	$content .= '
				</th> 
				</tr>';




	$content .= '<tr  >
				<th  align="bottom"  width="70%"> <br><br><br><br><B><U> DECLARATION:</U></B> WE HEREBY DECLARE THAT THIS INVOICE SHOWS
				THE ACTUAL PRICE OF GOODS DESCRIBED AND THAT ALL PARTICULARS ARE TRUE AND CORRECT </th>
           
		   <th  align="center"  width="30%">  
				<B> for ' . $AuthSign . ',</B><BR><BR><BR>
				 
							
				
				<p STYLE="text-align:center" > <b>AUTHORISED SIGNATORY <br> Signature & Date </b></p> </th>
			</tr>


				
				
				</table>';

	return $content;
}
