<?php

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
        LEFT JOIN ScrmPreparationMaster spm ON v.PreparationType = spm.ShortCode
        WHERE HSCode IS NOT NULL AND InvId = '$ID' GROUP BY HSCode ORDER BY SCGD.ID ASC";
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
    $content .= '<tr>
            <th width="45%">
            <table border="1" cellspacing="0" cellpadding="2" nobr="true">';

    $content .= '
				<tr>
				<th  align="LEFT" > <b>Exporter :</b>
				<br>' . $ExporterPrefix . $ExporterName . ' 
				<BR> ' . nl2br($ExporterAddress) . '';

    /* if ($showExpUsFda) {
        $content .= '	USFDA REGISTRATION NO  : ' . $ExporterUsfdaNo . '<BR>';
    }
    if ($showExpIECode) {
        $content .= ' IE CODE  : ' . $ExporterIeCode;
    }
    if ($showExpGSTNo) {
        $content .= '<BR> GST NO  : ' . $ExporterGstNo;
    } */


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
                    <tr>
                        <th  align="LEFT"  > <b> Invoice No. & Date.
                        ' . $InvNo . ',Dated ' . $InvDate . ' </b>
                        </th>
                    </tr>
                    <tr>
                        <th  align="LEFT" > <b> Purchase Order No. & Date.
                            <br> ' . $PoNo . $PoDate . '</b>
                        </th>
                    </tr>';
    $content .= '<tr>
                    <th  align="LEFT"  > <b> Other Reference(s) :
                        ' . $ReferenceNo . '  </b>
                    </th>
                </tr >';
    if ($showImporter) {
        $content .= '<tr> 
                        <th  align="left" width="100%">
                            <b><u> Importer of Record:</u></b> <br>  ' . $ImporterName . '
                            <br> ' . nl2br($ImporterAddress) . '
                        </th>
                    </tr>';
    }
    if ($ShowCWiseBuyer) {
        if ($BuyerID != 0) {
            $content .= '<tr>            		
                            <th  align="left" width="100%">  <b><u>Buyer 
                                (if other than consignee) :</b></u><br>' . $BuyerName . ',<br>' . nl2br($BuyerAddress) . '<br>
                            </th>
                        </tr>';
        }
    }

    $content .= '<tr>
				    <th  align="LEFT">
				<u><b>' . $AryLables['Manufacturer'] . '</u> :</b><br> ' . $ProcessorName . '<br> ' . nl2br($ProcessorAddress) . '';
    if ($ShowCWiseApprovalNo) {
        $content .= '<br>  ' . $AryLables['ApprovalNo'] . ' ' . $ProcessorApprovalNo . '';
    }

    if ($ShowCWiseUsfda) {
        $content .= '<br> BIO TERRORISM / USFDA REGISTRATION NO.  ' . $ProcessorUsfdaNo;
    }

    /* if ($showProFEI) {
        $content .= '<BR> FEI # :  ' . $ProcessorFEI;
    } */

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
    global $AryData, $style, $PType, $AryLables;
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
    if ($CountryId == 26) {
        $Grossweightin = '<th  align="center" width="20%" >
				<font size="7">Gross Weight <br>' . $QtyUnit . ' ' . $AryLables['UnitInBreakUp'] . '</font></th>';
    } else {
        $Grossweightin = '<th  align="center" width="20%" ><font size="7"></font></th>';
    }


    $content .= '
    <table border="1" cellspacing="0" cellpadding="2" nobr="flase" >
        <tr>
            <th  align="left" width="13.5%"  style="border-bottom:1px solid black;border-right:1px solid white;"> Marks & No\'s  </th>
            <th  align="left" width="14.5%"  style="border-bottom:1px solid black;border-right:1px solid white;">   No. & Kind of Pkgs <br><!--<b align="center" >' . $TotalMcs . ' M/Cs. </b>--> </th>
            <th  align="center" width="48%" style="border-bottom:1px solid black;border-right:1px solid black;">   Description of Goods </th>
            <th  align="center" width="17%" style="border-bottom:1px solid black;border-right:1px solid black;"> Net Quantity </th>
            <th  align="center" width="7%" style="border-bottom:1px solid black;border-right:1px solid black;" >  </th>
        </tr>
        <tr>
            <th  align="center" width="76%" style="font-size:9px;border-bottom:1px solid white;border-right:1px solid black;">';
    $content .= !empty($ContainerNo) ? '<b><u>CONTAINER NO. ' . $ContainerNo . '  </b></u>' : '';
    $content .= !empty($SealNo) ? '<b><u>SEAL NO. ' . $SealNo . '</b></u>' : '';
    $content .= ' </th>         
            <th  align="center" width="17%" style=" border-bottom:1px solid white;border-right:1px solid black;">  </th>
            <th  align="center" width="7%"  style="border-bottom:1px solid white;border-right:1px solid black;"> </th>
        </tr>';
    $CurrentCount = 0;

    //   $SqlVarietyMcs="
    // SELECT sum(McS) As VarietyMcs, BuyerItemCode, 
    // (SELECT CIDescription from ScrmCIGradeData WHERE PoGradeID=SPG.ID) as Description,SPG.Variety,SPG.Treatment,SPG.Grade,Aps.PackingStyle,SPG.Brand,SPG.PackingStyleID,BAP,SpeciesName FROM `ScrmPoGradeData` as SPG
    // left join `AdminPackingStyles` as Aps on PackingStyleID=Aps.PCKID
    // left join `ScrmPIVarietyList` as SPV on SPV.VarietyID=SPG.VarietyID
    // left join `ScrmSpeciesMaster` as SPM on SPM.ShortCode=SPV.Species
    //  WHERE InvId='$ID' GROUP by Variety,PackingStyleID,Brand order by SPG.ID";
    $SqlVarietyMcs = "SELECT sum(McS) As VarietyMcs,BuyerItemCode,PrintStatus,
                CIDescription as Description,Variety,Treatment,Grade,
                PackingStyle,Brand,PackingStyleID,BAP ,v.HSCode,v.FreezingType,v.Glaze
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
    $garndTotalPacks = 0;
    while ($VarietyMcsRow = $StmtVarietyMcs->fetch_assoc()) {
        $CurrentCount++;
        $Variety = $VarietyMcsRow['Variety'];
        $Grade = $VarietyMcsRow['Grade'];
        $Brand = $VarietyMcsRow['Brand'];
        $BAP = $VarietyMcsRow['BAP'];
        $BuyerItemCode = $VarietyMcsRow['BuyerItemCode'];
        $BreakUpDescription = $VarietyMcsRow['Description'];
        $PackingStyleID = $VarietyMcsRow['PackingStyleID'];
        $HSCode = $VarietyMcsRow['HSCode'];
        $Treatment = checkValueAval($VarietyMcsRow['Treatment']);
        $SpeciesName = checkValueAval($VarietyMcsRow['SpeciesName']);
        if ($BAP == 'NA') {
            $BAP = '';
        } else {
            $BAP = $BAP;
        }


        $FontSize = "7px";

        $SqlVariety = "SELECT * FROM `ScrmCIGradeData` as PGD
                left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE 
                InvId='$ID' and CIDescription='$BreakUpDescription' and Brand='$Brand' 
                and	PackingStyleID='$PackingStyleID' order by abs(Grade)";

        $StmtVariety = $iconn->query($SqlVariety);
        $ArySubBreakUp = array();
        $descriptionArray = [];
        $breakUpData = '';
        $totalPacks = 0;

        while ($RowVariety = $StmtVariety->fetch_assoc()) {

            //$ArySubBreakUp[]=$RowVariety['ID'];
            $ArySubBreakUp[] = $RowVariety['PoGradeID']; //uma

            $AryPriceBreakUp = getBreakUpData($iconn, $RowVariety['PoGradeID']);
            $Qty = $AryPriceBreakUp['Qty'];
            $UsdValue = $AryPriceBreakUp['UsdValue'];
            $TotalMcs = $AryPriceBreakUp['TotalMcs'];
            $Price = $AryPriceBreakUp['Price'];
            $UnitPrice = $AryPriceBreakUp['UnitPrice'];
            $GrossWeight = $AryPriceBreakUp['GrossWeight'];

            $InPacks = $TotalMcs * $RowVariety['TotalSlabs'];
            $totalPacks += $InPacks;
            $descriptionArray[] = $RowVariety['BuyerItemCode'] . ', ' . $RowVariety['Brand'] . ', ' . $RowVariety['Grade'] . ', ' . $RowVariety['Mcs'] . ' CARTONS ' . $InPacks . '  PACK ' . $UnitCurrency . '/' . $Price . ' SPECIES :' . $RowVariety['SpeciesName'];

            //print_r($AryPriceBreakUp); exit;

            if ($PType == 'KGS') {
                $Price = sprintf("%0.7f", $UnitPrice);
            } else {
                $Price = sprintf("%0.2f", $Price);
            }

            //print_r($AryPriceBreakUp); exit;
            // if($EnteredGrossWt!=''){
            // 	$GrossWeight=$EnteredGrossWt;
            // }



            /*  if ($CountryId == 26) {
                 $Grossweightin = '<td align="center" width="20%" style="border-right:1px solid black;"  >   
                     ' . number_format($GrossWeight, 3) . '    </td>';

             } else {
                 $Grossweightin = '<td align="center" width="20%" style="border-right:1px solid black;">
                         </td>';
             } */


            $breakUpData .= '
                <tr>
                    <td  align="left" width="15%" style="border-right:1px solid white;" >';
            $breakUpData .= $RowVariety['BuyerItemCode'] != '#N/A' ? $RowVariety['BuyerItemCode'] : '';
            $breakUpData .= '
                    </td>';
            $breakUpData .= '
                    <td align="center" width="20%"  style="border-right:1px solid white;" > 
                    ' . $RowVariety['Grade'] . ' 
                    </td>';
            $breakUpData .= '
                    <td align="center" width="21%" style="border-right:1px solid white;"  > 
                    ' . $TotalMcs . '   </td>
                    <td align="center" width="20%" style="border-right:1px solid black;"  >
                    ' . $InPacks . '  </td>
                    <td  align="center"   width="17%" style="border-right:1px solid black;" >' . number_format($Qty, 3) . '</td>
                </tr>';
        }

        /*$content.='
        <tr>';	
                /*if($BuyerItemCode!=''){
                    $content.='
                    <th align="center" width="15%" style="border-right:1px solid white;"  >    
                        <u>Item Code</u>   
                    </th>
                    <th align="center" width="15%" style="border-right:1px solid white;"  >    
                        <u>SIZE</u>   
                    </th>';
                }else{*/
        /*$content.='
        <th align="center" width="30%" style="border-right:1px solid white;" >   
            <u>SIZE</u>   
        </th>';
    //}
    $content.='
    <th align="center" width="29%" style="border-right:1px solid black;" >   
        <u>NO. OF CTNS</u>   
    </th>
    <th align="center" width="21%" style="border-right:1px solid black;"></th>
</tr>';*/
        $description_head = '<B><u>Description of Goods: </u></B><BR>1 X 40 FCL <BR>';
        $W_SPACE = '<b style="color:white"> yuhjutgfrtgujkiolmnbvgftrkliomkjhygtfrdeswderfgtyhjmnkih</b>';
        $Whitspace = '<b style="color:white"> yuhju</b>';
        $FontSize = "7";
        //$TOTAL_MCS=0;
        $content .= '';
        //$Description=$VarietyMcsRow['Variety'].''.$Treatment.'<br>'.$BreakUpDescription.'<br>';
        //$Description ='PROD.CODE/LOT:DEV 25/006 & FACTORY APPROVAL NO. INDIA 990,<br>';
        $Description = '';
        if ($BreakUpDescription != '') {
            $Description .= $BreakUpDescription . '';
        } else {
            //$Description=$VarietyMcsRow['Variety'].''.$Treatment.'<br>';
            $Description .= $VarietyMcsRow['Variety'] . '';
        }

        /* if ($DestinationCountryId != 26) {
            $Description .= '<u>PACKING : ' . $VarietyMcsRow['PackingStyle'] . ' ' . $SpeciesName . '</u>
            <b><br>' . $BAP . '</b>';
        } else {
            $Description .= '	PACKING : ' . $VarietyMcsRow['PackingStyle'] . ' ' . $SpeciesName . '' . $Whitspace . ' ';
        } */

        /* $Description .= '<br>PACKED : ' . $VarietyMcsRow['PackingStyle'] .' NW PER CARTON '.$VarietyMcsRow['FreezingType'] .' HS CODE : '.$HSCode.',<br>';
        $Description .= implode('<br>',$descriptionArray).'<br>';
        $Description .='FARM ASC LICENSE NO. IN045682/1, EXPIRY DATE: 21.01.2027
                        FACTORY ASC LICENSE NO. ASC-C-01785'; */
        $content .= '
            <tr>
                <td width="76%" style="border-right: 1px solid black;" >' . nl2br($Description) . '</td>
                <td align="center" width="17%" style="border-right: 1px solid black;">      </td>
                <td align="center" width="6.5%" style="border-right: 1px solid white;">      </td>
            </tr>';
        //if ($CurrentCount < 2) {
        $content .= '
                <tr>';
        /*if($BuyerItemCode!=''){
            $content.='
            <th align="center" width="15%" style="border-right:1px solid white;"  >    <u>Item Code</u>   </th>
            <th align="center" width="15%" style="border-right:1px solid white;"  >    <u>SIZE</u>   </th>';
        }else{*/
        $content .= '
                        <th align="left" width="15%" style="border-right:1px solid white;;font-size:6px" ><b><u>CODE NO.</u></b></th>
                        <th align="center" width="20%" style="border-right:1px solid white;;font-size:6px" ><b><u>GRADE</u></b></th>';
        //}
        $content .= '<th align="center" width="21%" style="border-right:1px solid white;;font-size:6px" > <b>  <u>NO.OF CTNS</u>  </b> </th>
                    <th align="center" width="20%" style="border-right:1px solid black;;font-size:6px"><b> <u> IN PACK </u> </b> </th>
                    <th align="center" width="17%" style="border-right:1px solid black;;font-size:6px"><b> <u> QTY. IN KGS. </u> </b> </th>
                </tr>';
        //}

        //   $SqlVariety="SELECT * FROM `ScrmPoGradeData` as PGD
        // left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE 
        // InvId='$ID' and Variety='$Variety' and Brand='$Brand' 
        // and	PackingStyleID='$PackingStyleID' order by abs(Grade)";   

        $content .= $breakUpData;

        $SubVarityBrkUp = implode(',', $ArySubBreakUp);

        $AryPriceData = getBreakUpData($iconn, $SubVarityBrkUp);
        $Qty = $AryPriceData['Qty'];
        $UsdValue = $AryPriceData['UsdValue'];
        $TotalMcs = $AryPriceData['TotalMcs'];
        $Price = $AryPriceData['Price'];
        $GrossWeight = $AryPriceData['GrossWeight'];
        //$EnteredGrossWt=$AryPriceData['EnteredGrossWt'];
        $garndTotalPacks += $totalPacks;

        /* if ($CountryId == 26) {
            $Grossweightin = '<td align="center" width="20%" >   
                    ' . number_format($GrossWeight, 3) . '   </td>';

        } else {
            $Grossweightin = '<td align="center" width="20%" style="border-right:1px solid black;">
                        </td>';
        } */

        if ($RowCount > 1) {
            $content .= '
                    <tr>
                        <td align="center" width="15%" style="border-right:1px solid white;"></td>
                        <td align="center" width="20%"  style="border-right:1px solid white;" >
                            <b>Total</b	>
                        </td>
                        <td align="center" width="21%" style="
                                        border-top:1px solid black;
                                        border-bottom:1px solid black;
                                        border-right:1px solid white;" >										
                        ' . $TotalMcs . '   
                        </td>
                        
                        <td align="center" width="20%" style=" border-left:1px solid white;
                                                                        border-right:1px solid black;
                                                                        border-top:1px solid black;
                                                                        border-bottom:1px solid black;">   
                        ' . $totalPacks . '
                        </td>
                        <td align="center" width="17%" style="border-top:1px solid black;border-bottom:1px solid black;border-right:1px solid black;">
                        ' . number_format($Qty, 3) . '
                        </td>				
                    </tr>';
        }
    }

    $AryPriceData = getBreakUpData($iconn, $RowVariety['PoGradeID'], $ID);
    $Qty = $AryPriceData['Qty'];
    $UsdValue = $AryPriceData['UsdValue'];
    $TotalMcs = $AryPriceData['TotalMcs'];
    $Price = $AryPriceData['Price'];
    $GrossWeight = $AryPriceData['GrossWeight'];

    /* if ($CountryId == 26) {
        $Grossweightin = '<td align="center" width="20%" >   
                    ' . number_format($GrossWeight, 3) . '   </td>';

    } else {
        $Grossweightin = '<td align="center" width="20%" style="border-right:1px solid black;"></td>';
    } */
    $content .= '
            <tr>
                <td align="center" width="15%" style="border-right:1px solid white;border-bottom:1px solid black;"></td>
                <td align="center" width="20%" style="border-top: 1px solid white;border-bottom: 1px solid black;" > <B>
                GRAND TOTAL </B> </td>
                <td align="center" width="21%" style="border-top: 1px solid black; " > 
                ' . $TotalMcs . ' </td>
                <td align="center" width="20%" style="border-top: 1px solid black; border-right: 1px solid black;" >' . $grandTotalPacks . '</td>
                <td align="center" width="17%" style="border-top: 1px solid black;border-right: 1px solid black;" >' . number_format($Qty, 3) . '</td>
            </tr>';
    $content .= '
    </table>';
    return $content;
}


function getFooterTable($iconn, $ID)
{
    //include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

    global $AryData, $style;
    $content = $style;


    $size = 45;
    $size1 = 55;

    foreach ($AryData as $key => $val) {
        $$key = $val;
    }
    $AryPriceData = getBreakUpData($iconn, $RowVariety['PoGradeID'], $ID);
    $Qty = $AryPriceData['Qty'];
    $UsdValue = $AryPriceData['UsdValue'];
    $TotalMcs = $AryPriceData['TotalMcs'];
    $Price = $AryPriceData['Price'];
    $GlazePerc = $AryPriceData['GlazePerc'];
    $ProductWeightWOGLbs = $AryPriceData['ProductWeightWOGLbs'];
    $ProductWeightWOGKgs = $AryPriceData['ProductWeightWOGKgs'];
    $WtPerCtnTxt = $AryPriceData['WtPerCtnTxt'];


    //print_r($AryPriceData); exit;



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


    //$BILL_WORD = numberTowords($UsdValue);


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
    //$AryPaymentData = getPaymentValues($iconn, $ID);

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
    $content .= '
    <table border="1" cellspacing="0" cellpadding="2" nobr="false">
		<tr>';
    $content .= '
            <td width="100%">
			    <table border="0" cellspacing="0" cellpadding="2" nobr="true">';
    if ($QtyUnit == 'KGS') {
        $content .= '<tr>
                                        <td align="left" width="30%">   <b>GROSS WEIGHT:</b>    </td>
                                        <td align="left" width="35%" >    <b>' . number_format($GrossWeightKgs, 3) . ' KGS</b>   </td>
                                    </tr>';
        $content .= '<tr>
                                        <td align="left" width="30%">   <b>NET WEIGHT: </b>   </td>
                                        <td align="left" width="35%" >    <b>' . number_format($NetWgtKgs, 3) . ' KGS</b>   </td>
                                    </tr>';

        if ($GlazePerc != 0) {
            $content .= '<tr>
                                            <td align="left" width="30%">   <b>NET WEIGHT WITHOUT GLAZE </b>   </td>
                                            <td align="left" width="35%" >    <b> ' . number_format($ProductWeightWOGKgs, 3) . ' KGS</b>   </td>
                                        </tr>';
        }
    } else {
        $content .= '<tr>
                                        <td align="left" width="30%">   GROSS WEIGHT    </td>
                                        <td align="left" width="35%" >    <b>: ' . number_format($GrossWeightLbs, 3) . ' LBS</b>   </td>';
        if ($ShowCWiseGrossWeight) {
            $content .= '<td align="left" width="35%" >    <b> ' . number_format($GrossWeightKgs, 3) . ' KGS</b>   </td>';
        }
        $content .= '</tr>';

        $content .= '<tr>
                                        <td align="left" width="30%">   NET WEIGHT    </td>
                                        <td align="left" width="35%" >    <b>: ' . number_format($NetWgtLbs, 3) . ' LBS</b>   </td>';
        if ($ShowCWiseGrossWeight) {
            $content .= '
                                            <td align="left" width="35%" >    <b> ' . number_format($NetWgtKgs, 3) . ' KGS</b>   </td>';
        }
        $content .= '</tr>';

        if ($GlazePerc != 0) {
            $content .= '<tr>
                                            <td align="left" width="30%">   <b>NET WEIGHT WITHOUT GLAZE</b>    </td>
                                            <td align="left" width="35%" >    <b> ' . number_format($ProductWeightWOGLbs, 3) . ' LBS</b>   </td>
                                        </tr>';
        }
    }
    $content .= '
                </table>';
    $content .= '
            </td>
        </tr>
    </table>';
    $content .= '
    <table border="1" cellspacing="0" cellpadding="5"  nobr="true">
        <tr>';
    $formated_date = date("Y-m-d", strtotime($InvDate));
    $AryDescription = getInvoiceDescription($iconn, $formated_date);
    $CommercialNote = $AryDescription['CommercialNote'];
    //$ArrayProductDescription = getProductSpec($iconn, $ID);
    $content .= '
        </tr>';

    /*if($CustomNote!=''){
            $content.='
            <tr>  
                <th  align="LEFT"  width="100%"><font size="8"> '.nl2br($CustomNote).'<BR><BR> 
                </th> 
            </tr>';
      }*/
    $content .= '
        <tr>
			<th  align="bottom"  width="93%" style="text-align: justify;border-bottom: 1px solid black;border-right: 1px solid black;">   ';
    if ($ShowCWiseWeightPerctText) {
        $content .= '' . $WtPerCtnTxt . '<br><br>';
    }
    // $ComDescriptionSpl=str_replace(' ', '&nbsp;', $ComDescriptionSpl);
    if ($CommercialNote != '' && $ShowCWiseComDesNote) {
        //echo $CommercialNote; 
        $content .= '' . nl2br($CommercialNote) . '<br><br>';
    }
    if ($ComDescriptionSpl != '' && $ShowCWiseComSplNote) {
        //$content.=nl2br($ComDescriptionSpl).'<br>';
    }
    /* if ($ShowCWiseProductSpecification) {
        //$content.=getProductSpec($iconn,$ID);
        $content .= $ArrayProductDescription['Desc'];

    } */
    $PackingRemarks = trim($PackingRemarks);
    if ($PackingRemarks != '') {
        $PackingRemarks = str_replace(' ', '&nbsp;', $PackingRemarks);
        $content .= nl2br($PackingRemarks);
    }
    /* if ($ShowCWiseSpecification) {
        $content .= $ArrayProductDescription['Speciesdesc'];
    } */
    //}
    $content .= '   
            </th>
            <th width="7%" style="border-bottom: 1px solid black;border-right: 1px solid black;"></th>
	    </tr>';
    /* if(!empty($PackingRemarks)){
        $content .= ' 
        <tr>
            <th width="93%" style="text-align: justify;border-top: 1px solid white;border-bottom: 1px solid white;border-right: 1px solid black;">
               '.$PackingRemarks.'
            </th>
            <th width="7%" style="border-top: 1px solid white;border-bottom: 1px solid white;border-right: 1px solid black;"></th>
        </tr>';
    } */
    /*  $content .= ' 
     <tr>
         <th  width="93%" style="text-align: justify;border-top: 1px solid white;border-bottom: 1px solid black;border-right: 1px solid black;">
             <!--<b>PROCESSOR / PACKER  :</b>  DEVI FISHERIES LIMITED, 2-159, PANASAPADU, APSP CAMP,
             KAKINADA, ANDHRA PRADESH - 533005, INDIA
             EIC APPROVAL NO. 990-->
         </th>
         <th width="7%" style="border-top: 1px solid white;border-bottom: 1px solid black;border-right: 1px solid black;"></th>
     </tr>
     <tr>
         <td  width="75%"  style="border-bottom: 1px solid white;border-right: 1px solid white;"></td>
         <td  width="25%"  style="border-left: 1px solid white;"></td>
     </tr>'; */
    $content .= '
        <tr>
            <td  width="75%"  style="border-bottom: 1px solid white;border-right: 1px solid white;border-top: 1px solid black;"></td>
            <td  width="25%"  style="border-left: 1px solid white;border-bottom: 1px solid black;border-top: 1px solid black;"></td>
        </tr>
        <tr> 
            <th  align="left"  width="75%" style="border-top: 1px solid white;border-right: 1px solid black;">
                <BR><BR> CATCHAREA : AQUACULTURE IN INDIA
            </th>
            <th  align="center"  width="25%"  style="border-top: 1px solid black;">    
                <B> for ' . $AuthSign . ',</B><BR><BR>
                    <p STYLE="text-align:center" > <b>AUTHORISED SIGNATORY <br> Signature & Date </b></p>   
            </th>
        </tr>
    </table>';
    return $content;
}
