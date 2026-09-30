<?php
//DocComInvoiceContentRgular.php
function getHeaderTable($iconn, $ID)
{
	//include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
	global $AryData, $style, $PType, $AryLables, $DocType, $ExporterPrefix;
	$content = '';
	$size = 45;
	$size1 = 55;

	foreach ($AryData as $key => $val) {
		$$key = $val;
	}
	$ShippingBillDate = (isset($ShippingBillDate) && $ShippingBillDate != "") ? date("d-m-Y", strtotime($ShippingBillDate)) : "";
	if ($ComShippingTermExt != '') {

		$ShippingTermsDisplay = $ShippingTerms;
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
				<br>' . $ExporterPrefix . $ExporterName . ' 
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
	$content .= '<tr>
				<th  align="LEFT" > <b><u>Consignee</u></b>
				<br>' . $ConsigneeName . ',<br>' . nl2br($ConsigneeAddress) . '  </th>
				</tr>';
	if (!empty($ComShippingTermExt)) {
		$content .= '
		<tr> 
			<th  align="LEFT" > <b><u>Terms of Delivery</u></b>
				<br>' . $ComShippingTermExt . '
			</th>
		</tr>';
	}
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
	if ($ShowShippingBillnDate && $ShowShippingDetails == '1') {
		$ShippingDetails = '<th  align="LEFT" width="100%;">
								<b> Shipping Bill No :' . $ShippingBillNo . '</b><br>
								<b> Shipping Bill Date :' . $ShippingBillDate . '</b>
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
				<br align="center">' . $PortOfLoading . ' </th>
				
				</tr>
				
				<tr>  
           		
				<th  align="LEFT" width="50%" ><b> Port of Discharge</b>
				<br align="center">' . $PortOfDischarge . ' </th>
				<th  align="LEFT" width="50%" ><b> Final Destination </b>
				<br align="center"> ' . $FinalDestination . ',' . $DestinationCountryName . ' </th>
				
				</tr>
				
				<!--<tr>  
           		
				<th  align="LEFT" width="50%" >
				 Marks & Nos  
				</th>
				<th  align="LEFT" width="50%" >
				 No.& Kind of Pkgs <br><b align="center" >' . $TotalMcs . ' M/Cs. </b> 
				</th>
				</tr>-->
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
	$content .= '
				</table>
				</th>
				
				
				<th width="55%">
				<table border="1" cellspacing="0" cellpadding="1" nobr="true">
				
                    <tr > 
                        <th  align="LEFT"  > <b> Invoice No. & Date.
                        ' . $InvNo . ',Dated ' . $InvDate . ' </b> 
                        </th>
                    </tr>
                    
                    <tr> 
                        <th  align="LEFT" > <b> Purchase Order No. & Date.
                            <br> ' . $PoNo . $PoDate . '</b>  
                        </th>
                    </tr>';
	$content .= '
                     <tr > 
                        <th  align="LEFT"  > <b> Other Reference(s) :
                            ' . $ReferenceNo . '  </b>				
                        </th>
                    </tr >';
	if ($showImporter) {

		$content .= '	<tr> 
                            <th  align="left" width="100%">
				                <b><u> Importer of Record:</u></b> <br>  ' . $ImporterName . '
				                <br> ' . nl2br($ImporterAddress) . '
                            </th>
				        </tr>';
	}
	if ($ShowCWiseBuyer) {
		if ($BuyerID != 0) {

			$content .= '	<tr>            		
                                <th  align="left" width="100%">  <b><u>Buyer 
                                    (if other than consignee) :</b></u><br>' . $BuyerName . ',<br>' . nl2br($BuyerAddress) . '<br> 
                                </th>
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
	$content .= '<tr>
                    <th border="1"  align="left" height="50%" width="49%">  Country of Origin of Goods 	
                        <br><b align="center"> INDIA</b> 
                    </th>				
                    <th border="1"  align="left" width="51%">  Country of Final Destination  
                        <br><b align="center">  ' . $DestinationCountry . '</b> 
                    </th>               
				</tr>
                <tr> 
                    <th border="1"  align="left" height="50%" width="49%">  Pre-Carriage by 	
                        <br><b align="center"> </b> 
                    </th>				
                    <th border="1"  align="left" width="51%">  Place of Receipt by Pre-carrier  
                        <br><b align="center"> </b> 
                    </th>               
				</tr>
				<tr> 				
				    <th  align="LEFT"  width="100%"  style="border-bottom:1px solid black;"> <b> <u>Terms of Delivery and Payment:</u>
				    </b><br><BR> ' . $PaymentTerms . ' <br>
				 
				 <!--SHIPMENT TERM : ' . $ShippingTerms . ' ' . strtoupper($PortOfDischarge) . ', ' .
		strtoupper($DestinationCountry) . ' -->';
	$content .= ' </th>
				</tr>';

	$content .= '<tr>';
	if ($ShowBLNo) {
		$content .= '<th  align="right"  width="40%"  style="border-bottom:1px solid white;padding-right:10px;"><b>' . $AryLables['BLNo'] . ': ' . $BLNo . '</b></th>';
	}
	$content .= '<th  align="LEFT" style="border:1px solid white;" width="20%"></th>';
	if ($ShowBLDate) {
		$content .= '<th  align="LEFT"  width="40%"  style="border-bottom:1px solid white;padding-right:10px;"><b>' . $AryLables['BLDate'] . ': ' . $BLDate . '</b></th>';
	}
	$content .= '</tr>
			<tr>             		
				<th  align="LEFT"  style="border-top:1px solid white;">
                <!-- <b><u>CONTAINER NO. ' . $ContainerNo . '</b></u> <b style="color:white">______________</b>   <b><u>SEAL NO. ' . $SealNo . '</b></u>-->
                 <br><br>';

	$content .= ' </th>
				    </tr>
                    <tr>            		
                        <th  align="left" style="border-top:1px solid white;">
                           <!--  <b  ><u> Shipping Term</u></b>
						    <br>
                            <span align="left"> <b>' . $ShippingTermsDisplay . ' ' . $ComShippingTermExt /* $FinalDestination.','.$DestinationCountryName*/ . '</span></b>
                        -->
                        
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
	global $AryData, $style, $PType, $DocType, $AryLables;
	$content = $style;
	$size = 45;
	$size1 = 55;
	foreach ($AryData as $key => $val) {
		$$key = $val;
	}
	if ($QtyUnit == 'KGS') {
		$Unit = 'KG';
	} elseif ($QtyUnit == 'LBS') {
		$Unit = 'LB';
	} else {
		$Unit = $QtyUnit;
	}

	$content = '
				<table border="1" cellspacing="0" cellpadding="2" nobr="false">
                    <tr>
                        <th  align="left" width="13.5%"  style="border-bottom:1px solid black;border-right:1px solid white;">Marks & No\'s  </th>
                        <th  align="left" width="11.5%"  style="border-bottom:1px solid black;border-right:1px solid white;">No. & Kind of Pkgs <br><!--<b align="center" >' . $TotalMcs . ' M/Cs. </b>--> </th>
                        <th  align="center" width="30%" style="border-bottom:1px solid black;border-right:1px solid black;">Description of Goods </th>
                        <!--<th  align="center" width="13.5%" >
                        Quantity in <br>' . $QtyUnit . ' ' . $AryLables['UnitInBreakUp'] . '  </th>
                        <th  align="center" width="13.5%" > Rate/' . $Unit . ' ' . $AryLables['UnitInBreakUp'] . ' <br>' . $UnitCurrency . ' </th>
                        --> 
                        <th  align="center" width="10%" style="border-bottom:1px solid black;border-right:1px solid black;"> Net Quantity </th>
						<th  align="center" width="10%" style="border-bottom:1px solid black;border-right:1px solid black;"> Gross Quantity </th>
                        <th  align="center" width="11.5%" style="border-bottom:1px solid black;border-right:1px solid black;" > Rate per pack </th>
                        <th  align="center" width="13.5%" style="border-bottom:1px solid black;border-right:1px solid black;" > Amount in ' . $UnitCurrency . '  </th>
                    </tr>
                    <tr>
                        <th  align="center" width="55%" style="font-size:9px;border-bottom:1px solid white;border-right:1px solid black;"> ';
	$content .= !empty($ContainerNo) ? '<b><u>CONTAINER NO. ' . $ContainerNo . '</b></u>' : '';
	$content .=   !empty($SealNo) ? '<b><u>  SEAL NO. ' . $SealNo . '</b></u>' : '';
	$content .=  '
						</th>         
                        <th  align="center" width="10%" style=" border-bottom:1px solid white;border-right:1px solid black;"></th>
						<th  align="center" width="10%" style=" border-bottom:1px solid white;border-right:1px solid black;"></th>
                        <th  align="center" width="11.5%"  style="border-bottom:1px solid white;border-right:1px solid black;"></th>
                        <th  align="center" width="13.5%"  style="border-bottom:1px solid white;border-right:1px solid black;"></th>
                    </tr>
                    ';

	$CurrentCount = 0;


	// $SqlVarietyMcs="SELECT sum(McS) As VarietyMcs,BuyerItemCode,PrintStatus,
	// (SELECT CIDescription from ScrmCIGradeData WHERE PoGradeID=ScrmPoGradeData.ID) as Description,Variety,Treatment,Grade,
	//  PackingStyle,Brand,PackingStyleID,BAP FROM `ScrmPoGradeData` 
	//  left join `AdminPackingStyles` as Aps on PackingStyleID=Aps.PCKID
	//  left join `ScrmBrandsMaster` SBM on  SBM.BrandName=Brand
	//  WHERE InvId='$ID' GROUP by Variety,PackingStyleID,Brand 
	//  Order By ID asc";

	$SqlVarietyMcs = "SELECT Variety,PoGradeID,sum(McS) As VarietyMcs,BuyerItemCode,PrintStatus,
                        CIDescription as Description,Variety,Treatment,Grade,
                        PackingStyle,Brand,PackingStyleID,BAP,v.HSCode,v.FreezingType,v.Glaze 
                        FROM `ScrmCIGradeData` s
                        left join `AdminPackingStyles` as Aps on PackingStyleID=Aps.PCKID
                        left join `ScrmBrandsMaster` SBM on  SBM.BrandName=Brand
                        LEFT JOIN (
                                    SELECT HSCode,v.VarietyID,FreezingType,Glaze FROM ScrmPreparationMaster spm 
                                    LEFT JOIN ScrmPIVarietyList v ON v.PreparationType=spm.ShortCode 
                                ) v ON s.`VarietyID`= v.`VarietyID`
                        WHERE InvId='$ID' GROUP by CIDescription,PackingStyleID,Brand
                        Order By ID asc";
	$StmtVarietyMcs = $iconn->query($SqlVarietyMcs);
	$RowCount = mysqli_num_rows($StmtVarietyMcs);
	$garndTotalPacks  = 0;
	$grandTotalGrossWt = 0;
	$custom_i = 0;
	while ($VarietyMcsRow = $StmtVarietyMcs->fetch_assoc()) {
		//echo '<pre>';print_r($SqlVarietyMcs);die;
		$CurrentCount++;
		$Variety = $VarietyMcsRow['Variety'];
		$Grade = $VarietyMcsRow['Grade'];
		$Brand = $VarietyMcsRow['Brand'];
		$BAP = $VarietyMcsRow['BAP'];
		$BuyerItemCode = $VarietyMcsRow['BuyerItemCode'];
		$breakUpDataDescription = $VarietyMcsRow['Description'];
		$PackingStyleID = $VarietyMcsRow['PackingStyleID'];
		$Treatment = $VarietyMcsRow['Treatment'];
		$BrandPrintStatus = $VarietyMcsRow['PrintStatus'];
		$HSCode = $VarietyMcsRow['HSCode'];
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

		/*breakup loop start*/
		$SqlVariety = "SELECT * FROM `ScrmCIGradeData` as PGD
                        left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID
						LEFT JOIN ScrmPIVarietyList v ON PGD.`VarietyID`= v.`VarietyID`
						LEFT JOIN `ScrmSpeciesMaster` ssm ON ssm.`ShortCode`=v.`Species`
						WHERE 
                        InvId='$ID' and CIDescription='$breakUpDataDescription' and Brand='$Brand' 
                        and	PackingStyleID='$PackingStyleID' order by abs(PGD.Grade)";

		$StmtVariety = $iconn->query($SqlVariety);
		$ArySubBreakUp = array();
		$descriptionArray = [];
		$breakUpData = '';
		$totalPacks = 0;
		$totalGrossWt = 0;
		while ($RowVariety = $StmtVariety->fetch_assoc()) {

			//$ArySubBreakUp[]=$RowVariety['ID']; 
			$ArySubBreakUp[] = $RowVariety['PoGradeID']; //uma
			$AryPriceBreakUp = getBreakUpData($iconn, $RowVariety['PoGradeID']);
			$Qty = $AryPriceBreakUp['Qty'];
			$UsdValue = $AryPriceBreakUp['UsdValue'];
			$TotalMcs = $AryPriceBreakUp['TotalMcs'];
			$Price = $AryPriceBreakUp['Price'];
			$UnitPrice = $AryPriceBreakUp['UnitPrice'];
			$GrossWt = $AryPriceBreakUp['GrossWt'];
			$AryPaymentData = getPaymentValues($iconn, $ID, $DocType);

			//echo $DocType; exit;
			$InvValueUSD = $AryPaymentData['InvValueUSD'];
			$InvValueInr = $AryPaymentData['InvValueINR'];
			$ExchangeRate = $AryPaymentData['ExchangeRate'];
			$DiscountPerc = $AryPaymentData['DiscountPerc'];
			$DiscountAmount = $AryPaymentData['DiscountAmount'];
			$IgstPerc = $AryPaymentData['IgstPerc'];
			$IgstValue = $AryPaymentData['IgstValue'];
			$InPacks = $TotalMcs * $RowVariety['TotalSlabs'];
			$totalPacks += $InPacks;
			$totalGrossWt += $GrossWt;
			$descriptionArray[] = $RowVariety['BuyerItemCode'] . ', ' . $RowVariety['Brand'] . ', ' . $RowVariety['Grade'] . ', ' . $RowVariety['Mcs'] . ' CARTONS ' . $InPacks . '  PACK ' . $UnitCurrency . '/' . $Price . ' SPECIES :' . $RowVariety['SpeciesName'];
			/*uma if($PType=='KGS'){
											  $Price=sprintf("%0.7f",$UnitPrice);
										  }else{
											  $Price=sprintf("%0.2f",$Price);
										  }*/
			$breakUpData  .= '<tr>
							<td  align="left" width="15%" style="border-right:1px solid white;" >';
			$breakUpData .= $RowVariety['BuyerItemCode'] != '#N/A' ? $RowVariety['BuyerItemCode'] : '';
			$breakUpData .= '</td>';
			$breakUpData .= '<td align="left" width="10%"  style="border-right:1px solid white;" > 
                        ' . $RowVariety['Grade'] . ' 
                        </td>';

			$breakUpData .= '<td align="center" width="15%" style="border-right:1px solid white;"  > 
                            ' . $TotalMcs . '   </td>
                            <!--<td align="center" width="20%" style="border-right:1px solid black;"  > 
                            </td>	-->
                            <td align="center" width="15%" style="border-right:1px solid black;"  >
                            ' . $InPacks . '  </td>
                            <td  align="center"   width="10%" style="border-right:1px solid black;" >' . number_format($Qty, 3) . '</td>
							<td  align="center"   width="10%" style="border-right:1px solid black;" >' . number_format($GrossWt, 3) . '</td>
							<td align="center" width="11.5%" style="border-right:1px solid black;"  > 
                            ' . $Price . ' </td>
                            
                            <td align="center" width="13.5%" style="border-right:1px solid black;" > 
                            ' . number_format($UsdValue, 2) . ' </td>                            
                        </tr>';
		}

		/*breakup loop ends*/
		$content .= '';
		//$Description=$VarietyMcsRow['Variety'].''.$Treatment.'<br>'.$breakUpDataDescription.'<br>';
		if ($BrandPrintStatus) {
			$ShowBrand = 'BRAND : ' . $VarietyMcsRow['Brand'] . '';
		}
		/* $Description ='PROD.CODE/LOT:DEV 25/006 & FACTORY APPROVAL NO. INDIA 990,<br>';
		if ($breakUpDataDescription != '') {
			$Description .= $breakUpDataDescription . '<br>';
		} else {
			$Description .= $VarietyMcsRow['Variety'] . '' . $Treatment . '<br>';
		} */
		$Description = '';
		$custom_i++;
		if ($breakUpDataDescription != '') {
			if($ID == "4947"){
				if($custom_i == 5){
					$Description .='<br><br><br><br><br>';
					$Description .= '<br><br><br>'. $breakUpDataDescription . '<br><br>';
				}else{
					$Description .= '<br>'. $breakUpDataDescription . '<br>';
				}
			}else{
				$Description .= $breakUpDataDescription . '<br>';

			}
		} else {
			$Description .= $VarietyMcsRow['Variety'] . '' . $Treatment . '<br>';
		}
		/* if ($DestinationCountryId != 26) {
			$Description .= 'PACKING : ' . $VarietyMcsRow['PackingStyle'] . ' ' . $ShowBrand . '<B><br>' . $BAP . '</B>';
		} else {
			$Description .= '	PACKING : ' . $VarietyMcsRow['PackingStyle'] . ' ' . $ShowBrand . ' ';
		} */
		/* $Description .= '<br>PACKED : ' . $VarietyMcsRow['PackingStyle'] .' NW PER CARTON '.$VarietyMcsRow['FreezingType'] .' HS CODE : '.$HSCode.',<br>';
		$Description .= implode('<br>',$descriptionArray).'<br>';
        $Description .='FARM ASC LICENSE NO. IN045682/1, EXPIRY DATE: 21.01.2027
                        FACTORY ASC LICENSE NO. ASC-C-01785'; */
		$content .= '
			<tr>
			    <td width="55%" style="border-right: 1px solid black;" > ' . nl2br($Description) . ' </td>
				<td align="center" width="10%" style="border-right: 1px solid black;">  </td>
			    <td align="center" width="10%" style="border-right: 1px solid black;">  </td>
			    <td align="center" width="11.5%" style="border-right: 1px solid black;">  </td>
			</tr>';
		//if ($CurrentCount < 2) {

		$content .= '<tr>';
		$content .= '<th align="left" width="15%" style="border-right:1px solid white;font-size:7px"><b><u>CODE NO.</u></b> </th>';
		$content .= '<th align="left" width="10%" style="border-right:1px solid white;font-size:7px"><b><u>GRADE</u></b> </th>';
		$content .= '<th align="center" width="15%" style="border-right:1px solid white;font-size:7px"><b><u>NO. OF CTNS</u> </b></th>
                            <td align="center" width="15%" style="border-right:1px solid black;font-size:7px"><b><u>IN PACK</u> </b></td>
                            <td align="center" width="10%" style="border-right:1px solid black;font-size:7px"><b><u>QTY. IN KGS.</u> </b></td>
							<td align="center" width="10%" style="border-right:1px solid black;font-size:7px"><b><u></u></b></td>
                            <td align="center" width="11.5%" style="border-right:1px solid black;font-size:7px"><b><u>IN ' . $UnitCurrency . '</u></b></td>
            </tr>';

		//}
		$content .= $breakUpData;

		$SubVarityBrkUp = implode(',', $ArySubBreakUp);

		$AryPriceData = getBreakUpData($iconn, $SubVarityBrkUp);
		$Qty = $AryPriceData['Qty'];
		$UsdValue = $AryPriceData['UsdValue'];
		$TotalMcs = $AryPriceData['TotalMcs'];
		$Price = $AryPriceData['Price'];
		$garndTotalPacks += $totalPacks;
		$grandTotalGrossWt += $totalGrossWt;
		if ($RowCount > 1) {
			$content .= '<tr>
                            <td align="center" width="15%" style="border-right:1px solid white;"></td>
                            <td align="center" width="10%"  style="border-right:1px solid white;" > 
                                <b>Total</b	>
                            </td>
                            <td align="center" width="15%" style="
                                border-top:1px solid black;
                                border-bottom:1px solid black;
                                border-right:1px solid white;
                                " >  ' . $TotalMcs . ' </td>                            
                            <!--<td align="center" width="20%" style="border-right:1px solid black;"></td>-->
                            <td align="center" width="15%" style="
																 border-left:1px solid white;
																 border-right:1px solid black;
																 border-top:1px solid black;
																 border-bottom:1px solid black;
																 "> ' . $totalPacks . ' </td>
                            <td align="center" width="10%" style="border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;">
							' . number_format($Qty, 3) . '
							</td>
							<td align="center" width="10%" style="border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;">
							' . number_format($totalGrossWt, 3) . '
							</td>
                            <td align="center" width="11.5%" style="border-right:1px solid black;">  </td> 
                            <td align="center" width="13.5%" style="border-top:1px solid black;
                            border-bottom:1px solid black;">														 
                            ' . number_format($UsdValue, 2) . ' </td>				
                        </tr>';
		}
	}

	$AryPriceData = getBreakUpData($iconn, $RowVariety['PoGradeID'], $ID);
	// echo json_encode($AryPriceData);exit;
	$Qty = $AryPriceData['Qty'];
	$GrossWt = $AryPriceData['GrossWt'];
	$UsdValue = $AryPriceData['UsdValue'];
	$TotalMcs = $AryPriceData['TotalMcs'];
	$Price = $AryPriceData['Price'];
	$totalPacks = $Qty * $TotalMcs;

	$content .= '<tr>			
                    <td align="center" width="10%" style="border-right:1px solid white;border-bottom:1px solid black;"></td>
                    <td align="center" width="15%" style="border-top: 1px solid white;border-bottom: 1px solid black;" > <B>
                    GRAND TOTAL </B> </td>
                    <td align="center" width="15%" style="border-top: 1px solid black; " > 
                    ' . $TotalMcs . ' </td>
                    <td align="center" width="15%" style="border-top: 1px solid black; border-right: 1px solid black;" >' . $garndTotalPacks . '</td>
                    <td align="center" width="10%" style="border-top: 1px solid black;border-right: 1px solid black;" >' . number_format($Qty, 3) . '</td>
                    <td align="center" width="10%" style="border-top: 1px solid black;border-right: 1px solid black;" >' . number_format($GrossWt, 3) . '</td>
                    <td align="center" width="11.5%" style="border-top: 1px solid white;border-bottom: 1px solid black;border-right: 1px solid black;" ></td>
                    <td align="center" width="13.5%" style="
                    border-top: 1px solid black;
                    border-bottom: 1px solid black;
                    border-right: 1px solid black;"> 
                    <b>' . number_format($UsdValue, 2) . '</b> </td>
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
	/* if ($showIgst) {
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
	} */

	$content .= '</table>';


	return $content;
}
function getFooterTable($iconn, $ID)
{
	//include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

	global $AryData, $style, $DocType;
	$content = $style;
	$size = 45;
	$size1 = 55;

	$AryPriceData = getBreakUpData($iconn, $RowVariety['PoGradeID'], $ID);

	foreach ($AryPriceData as $pkey => $pval) {
		$$pkey = $pval;
	}

	//	print_r($AryPriceData); exit;
	foreach ($AryData as $key => $val) {
		$$key = $val;
	}
	//$AryBankData = getBankDetails($iconn, $BankID);
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


	if ($CurrenySymbol == '₹') {
		$BILL_WORD = convert_rupees_to_words($UsdValue);
	} else {
		$BILL_WORD = numberTowords($UsdValue);
	}


	$NetWgtKgs = $AryPriceData['NetWgtKgs'];
	$NetWgtLbs = $AryPriceData['NetWgtLbs'];
	$GrossWeightKgs = $AryPriceData['GrossWeightKgs'];
	$GrossWeightLbs = $AryPriceData['GrossWeightLbs'];

	//$AryPaymentData = getPaymentValues($iconn, $ID, $DocType);

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


	$content .= $style;
	$content .= '
			<table border="1" cellspacing="0" cellpadding="2" nobr="true">
			
			<tr>
			<td width="55%">
			
			<table border="0" cellspacing="0" cellpadding="0" nobr="true">';
	$ShowInvoicevalue = true;
	//$ShowFrightCharge=false;
	/*if ($ShowFrightCharge) { 

		$content .= '<tr>
			<td  width="1%"></td>
			<td align="left" width="70%"> INVOICE VALUE   </td>
			<td align="left" width="10%" >  <b>: </b> </td>
			<td align="left" width="20%" >  <b> $ ' . number_format($InvValueUSD, 2) . '</b> </td>
			</tr>';

		$content .= '
			<tr>
			<td  width="1%"></td>
			<td align="left" width="70%"> LESS : OCEAN FREIGHT   </td>
			<td align="left" width="10%" >  <b>: </b> </td>
			<td align="left" width="20%" >  <b> $ <u>' . number_format($FrieghtCharge, 2) . '</u></b> </td>
			</tr>';
		if ($ISFStatic) {
			$content .= '
			<tr>
			<td  width="1%"></td>
			<td align="left" width="70%"> (LESS : MERCHANDISE PROCESS FEE @ 0.3464%) </td>
			<td align="left" width="10%" >  <b>: </b> </td>
			<td align="left" width="20%" >  <b> $ ' . number_format($MerchantDuty, 2) . '</b> </td>
			</tr>';
		}
		$content .= '
			<tr>
			<td  width="1%"></td>
			<td align="left" width="70%"> SUB TOTAL </td>
			<td align="left" width="10%" >  <b>: </b> </td>
			<td align="left" width="20%" >  <b> $ ' . number_format($InvValueUSDWithOutDuties, 2) . '</b> </td>
			</tr>';
		$content .= '
			<tr>
			<td  width="1%"></td>
			<td align="left" width="55%"><font size="7">(LESS : ANTI-DUMPING DUTY @ ' . $AntiDumpingDutyPerc . '%) </font></td>
			<td align="left" width="44%" ><font size="7"> <b>: $ ' . number_format($AntiDumpingDuty, 2) . '</b></font></td>
			</tr>';
		//uma cvd calculation included //18-03-2024
		if ($CountervailingDutyPerc != 0) {
			$content .= '
				<tr>
				<td  width="1%"></td>
				<td align="left" width="55%"><font size="7">(LESS : COUNTER VAILING DUTY @ ' . $CountervailingDutyPerc . '%) </font></td>
				<td align="left" width="44%" ><font size="7"> <b>: $ ' . number_format($CountervailingDuty, 2) . '</b></font></td>
				</tr>';
		}
		//uma cvd calculation included //18-03-2024
		if (!$ISFStatic) {
			$content .= '
			<tr>
			<td  width="1%"></td>
			<td align="left" width="70%"> (LESS : MERCHANDISE PROCESS FEE @ ' . $MerchandiseProcessFeePerc . '%) </td>
			<td align="left" width="30%" >  <b>: $ ' . number_format($MerchantDuty, 2) . '</b> </td>
			</tr>';
		}
		$content .= '<tr>
			<td  width="1%"></td>
			<td align="left" width="70%"> (LESS : HARBOR MAINTENANCE FEE @ ' . $HarbourMaintenanceFeePerc . '%) </td>
			<td align="left" width="30%" >  <b>: $ ' . number_format($HarborDuty, 2) . '</b> </td>
			</tr>';

		$content .= '<tr>
			<td  width="1%"></td>
			<td align="left" width="70%"> TOTAL DUTY </td>
			<td align="left" width="30%" >  <b>: $ <u>' . number_format($DutiesPrice, 2) . '</u></b> </td>
			</tr>';

		$content .= '<tr>
			<td  width="1%"></td>
			<td align="left" width="70%"> FOB VALUE  IN USD   </td>
			<td align="left" width="30%" >  <b>: $ ' . number_format($FOBValueUSD, 2) . '</b> </td>
			</tr>';
	} else if ($ShowInvoicevalue) {

		//echo "hii"; exit;
		//  $content .= '<tr>
		// 	<td  width="1%"></td>
		// 	<td align="left" width="35%"> INVOICE VALUE ' . $UnitCurrency . ' ' . $CurrenySymbol . ' </td>
		// 	<td align="left" width="15%" >  <b>: </b> </td>
		// 	<td align="left" width="60%" >  <b> ' . number_format($InvValueUSD, 2) . '</b> </td>
		// 	</tr>'; 
	} else {
		$content .= '<tr>
			<td  width="1%"></td>
			<td align="left" width="35%"> FOB VALUE IN US $  </td>
			<td align="left" width="15%" >  <b>: </b> </td>
			<td align="left" width="60%" > <b> ' . number_format($InvValueUSDWithOutDuties, 2) . '</b> </td>

			<!--<td align="left" width="50%" >  
			<b> FOB VALUE  :  ' . round($InvValueINRWithOutDuties) . '.00</b> 
			</td>-->
			</tr>';
	}*/
	$NetWgtKgs = $AryPriceData['NetWgtKgs'];
	$NetWgtLbs = $AryPriceData['NetWgtLbs'];
	$GrossWeightKgs = $AryPriceData['GrossWeightKgs'];
	$GrossWeightLbs = $AryPriceData['GrossWeightLbs'];
	if ($GrossWeightKgs == $TotalGrossWtKgs) {
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
	}


	//	echo $GlazePerc; exit;
	if ($QtyUnit == 'KGS') {
		$content .= '<tr>
                        <td align="right" width="55%" style="font-size: 7px;border-bottom: 1px solid white;border-right: 1px solid white;"> <b>GROSS WEIGHT:</b>  </td>
                        <td align="left" width="47.1%"  style="border-bottom: 1px solid white;">  <b>' . number_format($GrossWeightKgs, 3) . ' KGS</b> </td>
                        <td width="16.9%" style="border-right: 1px solid black;"></td>
                        <td width="18.5%" style="border-right: 1px solid black;border-top:none;"></td>
                    </tr>';
		$content .= '<tr>
                        <td align="right" width="55%"  style="font-size: 7px;border-bottom: 1px solid white;border-right: 1px solid white"> <b>NET WEIGHT: </b> </td>
                        <td align="left" width="47.1%"  style="border-bottom: 1px solid white;">  <b>' . number_format($NetWgtKgs, 3) . ' KGS</b> </td>
                        <td width="16.9%" style="border-right: 1px solid black;"></td>
                        <td width="18.5%" style="border-right: 1px solid black;"></td>
                    </tr>';

		if (getIsGlazeInvoice($iconn, $ID)) {
			$content .= '<tr>
                            <td align="right" width="55%"  style="font-size: 7px;border-bottom: 1px solid white;border-right: 1px solid white"> <b>NET WEIGHT WITHOUT GLAZE: </b> </td>
                            <td align="left" width="45%"  style="border-bottom: 1px solid white;">  <b> ' . number_format($ProductWeightWOGKgs, 3) . ' KGS</b> </td>
                            <td width="16.9%" style="border-right: 1px solid black;"></td>
                            <td width="17.1%" style="border-right: 1px solid black;"></td>
                        </tr>';
		}
	} else {
		$content .= '<tr>
                        <td align="right" width="55%"  style="font-size: 7px;border-bottom: 1px solid white;border-right: 1px solid white"> <b>GROSS WEIGHT: </b> </td>
                        <td align="left" width="45%"  style="border-bottom: 1px solid white;">  <b>: ' . number_format($GrossWeightLbs, 3) . ' LBS</b> </td>
                        <!--<td align="center" width="35%" >  <b> ' . number_format($GrossWeightKgs, 3) . ' KGS</b> </td>-->
                        <td width="11.9%" style="border-right: 1px solid black;"></td>
                        <td width="17.1%" style="border-right: 1px solid black;"></td>
                    </tr>';

		$content .= '<tr>
                        <td align="right" width="55%"  style="font-size: 7px;border-bottom: 1px solid white;border-right: 1px solid white"> <b>NET WEIGHT:</b>  </td>
                        <td align="left" width="45%"  style="border-bottom: 1px solid white;">  <b>: ' . number_format($NetWgtLbs, 3) . ' LBS</b> </td>
                        <!--<td align="center" width="35%" >  <b> ' . number_format($NetWgtKgs, 3) . ' KGS</b> </td>-->
                        <td width="11.9%" style="border-right: 1px solid black;"></td>
                        <td width="17.1%" style="border-right: 1px solid black;"></td>
                    </tr>';

		if ($GlazePerc != 0) {
			$content .= '<tr>
                            <td align="right" width="55%"  style="font-size: 7px;border-bottom: 1px solid white;border-right: 1px solid white"> <b>NET WEIGHT WITHOUT GLAZE:</b>  </td>
                            <td align="left" width="45%"  style="border-bottom: 1px solid white;">  <b> ' . number_format($ProductWeightWOGLbs, 3) . ' LBS</b> </td>
                            <td width="11.9%" style="border-right: 1px solid black;"></td>
                            <td width="17.1%" style="border-right: 1px solid black;"></td>
                        </tr>';
		}
	}
	$content .= '</table></td>';

	$content .= '<td width="0">			
			<table border="0" cellspacing="0" cellpadding="1" nobr="false" style="">';

	$content .= '</table>';
	$content .= '</td>
				</tr>
		</table>';
	$content .= '<table border="1" cellspacing="0" cellpadding="5" nobr="true">
					<tr>';

	$formated_date = date("Y-m-d", strtotime($InvDate));
	$AryDescription = getInvoiceDescription($iconn, $formated_date);
	$CommercialNote = $AryDescription['CommercialNote'];
	// echo $CommercialNote; exit;

	//$ArrayProductDescription = getProductSpec($iconn, $ID);

	$content .= '</tr>
					<tr>  
						<th width="67.5%"  style="border-bottom: 1px solid white;border-right: 1px solid black;text-align: justify;">';
	// $ComDescriptionSpl = str_replace(' ', '&nbsp;', $ComDescriptionSpl);
	//if($CommercialNote==''){
	if ($CommercialNote != '' && $ShowCWiseComDesNote) {
		//echo $CommercialNote;
		//echo $ComDescriptionSpl; exit;
		$content .= '' . nl2br(htmlspecialchars($CommercialNote)) . '<br>';
	}
	if ($ComDescriptionSpl != '' && $ShowCWiseComSplNote) {
		$content .= '<br>' . nl2br(htmlspecialchars($ComDescriptionSpl)) . '<br> ';
	}
	$content .= '
						</th> 
						<td width="7.5%" style="border-right: 1px solid black;"></td>
						<td width="11.5%" style="border-bottom: 1px solid white;border-right: 1px solid black;"></td>
						<td width="13.5%" style="border-right: 1px solid black;"></td>
					</tr>';
	$ComInvDescription = trim($ComInvDescription);
	if (!empty($ComInvDescription)) {
		//  $ComInvDescription = str_replace(' ', '&nbsp;', $ComInvDescription);
		$content .= '
						<tr>  
							<th   width="67.5%"  style="border-top: 1px solid white;border-right: 1px solid black;text-align: justify;">
							' . nl2br(htmlspecialchars($ComInvDescription)) . '
							</th> 
							<!--<td width="2.5%" style="border-right: 1px solid black;"></td>-->
							<td width="7.5%" style="border-right: 1px solid black;"></td>
							<td width="11.5%" style="border-bottom: 1px solid white;border-right: 1px solid black;"></td>
							<!--<td width="13.5%" style="border-right: 1px solid black;"></td>-->
						</tr>';
	}
	/* $content .= '
					<tr>  
						<th  width="67.5%"  style="border-top: 1px solid white;;border-right: 1px solid black;text-align: justify;">
							<!--<b>PROCESSOR / PACKER  :</b>  DEVI FISHERIES LIMITED, 2-159, PANASAPADU, APSP CAMP,
												KAKINADA, ANDHRA PRADESH - 533005, INDIA EIC APPROVAL NO. 990
								-->				
						</th> 
						<td width="7.5%" style="border-right: 1px solid black;"></td>
						<td width="11.5%" style="border-bottom: 1px solid white;border-right: 1px solid black;"></td>
					</tr>'; */

	$content .= '
								<table border="1" cellspacing="0" cellpadding="2" nobr="true">
									<tr>  
										<td  align="LEFT"  width="58.5%" style="border-bottom: 1px solid white;border-right: 1px solid white;"> AMOUNT CHARGABLE</td>
										<td  width="13.5%" style="border-right: 1px solid white;"></td>
										<td  align="center" width="14.5%" style="border-bottom: 1px solid white;border-right: 1px solid black;">Total</td>
										<td  align="center"  width="13.5%" style="border-right: 1px solid black;border-bottom: 1px solid black;"><b>' . number_format($UsdValue, 2) . '</b></td>
									</tr>
									<tr>
										<td  align="LEFT"  width="58.5%"  style="border-top: 1px solid white;border-bottom: 1px solid white;border-right: 1px solid white;"> <b> (in words) (IN ' . $UnitCurrency . '): 
												(' . $BILL_WORD . ' ONLY)</b> <BR> 
												';
	if ($ShowCWiseWeightPerctText) {
		$content .= '' . nl2br($WtPerCtnTxt) . '<br>';
	}
	/* if ($ShowCWiseProductSpecification) {
														//$content.=getProductSpec($iconn,$ID);
														$content .= $ArrayProductDescription['Desc'];

													}

													if ($ShowCWiseSpecification) {
														$content .= $ArrayProductDescription['Speciesdesc'];

													} */
	$content .= '
										</td>
										<td width="13.5%" style="border-right: 1px solid white;border-bottom: 1px solid white;"></td>
										<td width="13.5%" style="border-right: 1px solid white;"></td>
										<td width="14.5%" style="border-right: 1px solid black;"></td>
									</tr>';
	//$ShowBank=true;
	/* if ($ShowBank && $ShowCWiseBankDetails) {
											$content .= '
													<table border="1" cellspacing="0" cellpadding="2" nobr="true">
													<tr><td style="margin-left: 1em;">
													<b>BANK DETAILS  </b>' . $BankDetails . '';
											$content .= '<br><b>Negotiating BANK  </b>' . $NegotiatingBank;
											$content .= '<br><b>ROUTING THROUGH   </b>' . $RoutingThrough . '
													</td></tr></table>';
										}  */
	$content .= '<tr  >
											<th  align="bottom"  width="75%" style="font-size:8px;border-top: 1px solid white;"> <br><br><br><br><B>Declaration:</B> 
													We declare that this invoice shows the actual price of the goods
													described and that all particulars are true and correct
											</th>
											
											<th  align="center"  width="25%">  
													<B> for ' . $AuthSign . ',</B><BR><BR><BR>
													<p STYLE="text-align:center" > <b>AUTHORISED SIGNATORY <br> Signature & Date </b></p> </th>
										</tr>	
								</table>';


	$content .= '</table>';

	return $content;
}
