<?php
//include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

function getCountryName($Country){
	
	if($Country==''){
		$Country='GENERAL';
	}
	else{
		$Country;
	}
	
 return $Country;
}

function getVarityName($ID,$iconn){
	global $TblQcPoVarityList; 	
	 $sql="SELECT * FROM $TblQcPoVarityList  WHERE VarietyID='$ID'";
	$stmt_REP=$iconn->query($sql);
	$row=$stmt_REP->fetch_array();
	$name=$row['FullName'];
 return $name;
}



function getPIInvoiceNumber($iconn,$ExpID,$PoID){
	
  $ExporterData=getExporterDetails($ExpID);
 
   $InvShortCode=$ExporterData['InvShortCode'];
  $InvDateFormat=$ExporterData['InvDateFormat']; 

 $CustomInvoiceSql="SELECT * FROM ScrmPIData   WHERE PoID='$PoID'";
$CustomInvoiceExec=$iconn->query($CustomInvoiceSql); 
$CustomInvoiceRow = $CustomInvoiceExec->fetch_assoc();
	
	
$CurrentMonth = date('n',strtotime($CustomInvoiceRow['InvDate']));   
if($InvDateFormat=='Short'){		
if($CurrentMonth<=3){
 $NxtYear = date("y",strtotime($CustomInvoiceRow['InvDate']));  	
 $CurrentYear = date("y",strtotime($CustomInvoiceRow['InvDate']))-1;  	
} 
else{ 
$CurrentYear = date("y",strtotime($CustomInvoiceRow['InvDate']));  	 
$NxtYear = date("y",strtotime($CustomInvoiceRow['InvDate']))+1;  	
 
}
}else{	

if($CurrentMonth<=3){
 $NxtYear = date('y',strtotime($CustomInvoiceRow['InvDate']));  	
 $CurrentYear = date('Y',strtotime($CustomInvoiceRow['InvDate']))-1;  		
}else{ 
$CurrentYear = date('Y',strtotime($CustomInvoiceRow['InvDate']));  	 
$NxtYear = date('y',strtotime($CustomInvoiceRow['InvDate']))+1;  	
  
}
}
 
 return $InvShortCode.'PI/'.$CustomInvoiceRow['InvNo'].'/'.$CurrentYear.'-'.$NxtYear; 
  
	
}


function getPoDetails($INV_DATA_ARY,$iconn){

	global $TblQcPoVarityList; 	
	global $TblQcCodeList; 	
	global $TblAdminPackingStyles; 	
	global $TblScrmConsigneeDetails; 	
	
	//print_r($INV_DATA_ARY);
	/*****uma*******/
	
	$ExpID=$INV_DATA_ARY['ExporterID'];
	$PoID=$INV_DATA_ARY['PoID'];
			$ExporterData=getExporterDetails($ExpID);
			$InvShortCode=$ExporterData['InvShortCode']; 

	/*****uma*******/
	$ConsigneeID=$INV_DATA_ARY['ConsigneeID'];
	
	
	 $INVOICE_ID=$INV_DATA_ARY['PoID'];
	 
	/*****uma*****/
 
	
//include ($_SERVER['DOCUMENT_ROOT']."/".$_SESSION["IncludePath"]."Constants.php");
//include("Constants.php");


	/*if($INV_DATA_ARY['ExporterID']==5)		
{
							$InvNO=$InvShortCode.$INV_DATA_ARY['CInvNo'].'/'.$CurrentYear.'-'.$NxtYear;
			}
			else{
							$InvNO=$InvShortCode.$INV_DATA_ARY['CInvNo'].'/'.$CurrentYear.'-'.$NxtYear;

			}*/
			  $InvNO=getInvoiceNumber($iconn,$ExpID,$PoID);

/*****uma*****/

	   $SqlConDetails="SELECT * FROM $TblScrmConsigneeDetails  WHERE `ConID`='$ConsigneeID'";
	 $StmtConDetails=$iconn->query($SqlConDetails);
	 $ConData=$StmtConDetails->fetch_array();
	 $ConsigneeName=$ConData['ConsigneeName'];
	 
	 
	 $sql="SELECT FullName FROM $TblQcPoVarityList  WHERE `InvID`='$INVOICE_ID'";
	 $stmt_po_v_list=$iconn->query($sql);
	 
	 
	
	while($v_data=$stmt_po_v_list->fetch_array()){
		$AryVariety[]=$v_data['FullName'];		
	}
	
	$AryVariety=array_unique($AryVariety);
	$FULL_NAME=implode(',', $AryVariety);
	// To GET VARIETY DETAILS  END
	
	// To GET VARIETY DETAILS
	
	$SQL_CODE_LIST="SELECT * FROM $TblQcCodeList as Qcl left join 
	$TblAdminPackingStyles as Aps On Qcl.PackingStyleID=Aps.PCKID where InvID='$INVOICE_ID'";
	
	$STMT_CODE_LIST=$iconn->query($SQL_CODE_LIST);
	
	
	
	
	while($CODE=$STMT_CODE_LIST->fetch_array()){

	$AryPackings[]=$CODE['PackingStyle'];
	$AryBrands[]=$CODE['Brand'];
	
	}
	
	$AryPackings=array_unique($AryPackings);
	$AryBrands=array_unique($AryBrands);
	$PACKING=implode(',', $AryPackings);
	$Brands=implode(',', $AryBrands);

	
$PO_TIME_STAMP = strtotime($INV_DATA_ARY['CInvDate']);
$INVOICE_DATE = date("d-m-Y", $PO_TIME_STAMP);

	
$PO_TIME_STAMP = strtotime($INV_DATA_ARY['PoDate']);
$PO_DATE = date("d-m-Y", $PO_TIME_STAMP);

	
	$header_size=9;
	$size=20;
	$size2=26;
	$size3=45;
	$content = '<table border="1" cellspacing="0" cellpadding="3">  
				<tr>  
           		<th  align="center"  width="'.($size).'%"><font size="'.$header_size.'"><b>BUYER</b></font></th>
                <th  align="left" width="'.($size2).'%"><font size="'.$header_size.'"><b>'.$ConsigneeName.'</b></font></th>
                <th  align="left" width="'.($size3).'%"><font size="'.$header_size.'"><b>VARIETY: '. $FULL_NAME .'</b></font></th>
				</tr>
				
				
				<tr>  
           		<th  align="center"  width="'.($size).'%"><font size="'.$header_size.'"><b>PO NO & Date</b></font></th>
                <th  align="left" width="'.($size2).'%"><font size="'.$header_size.'"><b>'.$INV_DATA_ARY['PoNo'].' Date'.$PO_DATE.'</b></font></th>
                <th  align="left" width="'.($size3).'%"><font size="'.$header_size.'"><b>PACKING : '.$PACKING.'</b></font></th>
				</tr>
				
				
				<tr>  
           		<th  align="center"  width="'.($size).'%"><font size="'.$header_size.'"><b>INVOICE NO & DATE</b></font></th>
                <th  align="left" width="'.($size2).'%"><font size="'.$header_size.'"><b>'.$InvNO.' Date'.$INVOICE_DATE.'</b></font></th>
                <th  align="left" width="'.($size3).'%"><font size="'.$header_size.'"><b>BRAND:'.$Brands.'</b></font></th>
				</tr>
				</table>';
				return $content;
	
} 


function getTotals($PoID,$iconn,$T_GRADES_ARY){
		
	global $TblQcCodeList; 	
		
	 $SqlCodeList="SELECT sum(McsTotal) as TotalMcs FROM `QcCodeList` WHERE InvID='$PoID'";
	$StmtCodeList=$iconn->query($SqlCodeList);
	$RowData = $StmtCodeList->fetch_assoc();
	$TotalMcs=$RowData['TotalMcs'];
	
	//exit;	
		
		 $size=count($T_GRADES_ARY);
				$size=100/$size;
				$size=14;	
			$header_size=9;				
		
		$contents .= '<td align="center" width="'.$size.'%"  ><font size="'.$header_size.'"><b>'.$TotalMcs.'</b></font></td>';
		
		
		return $contents; 

	}
	
	
	function getRowWidth($T_GRADES_ARY,$HEADERS_COUNT){
	 $size=count($T_GRADES_ARY)+$HEADERS_COUNT;
	 $size=100/$size;
		return $size;
	}

/*
function getRowWidth($T_GRADES_ARY){ 
 
	 $size=count($T_GRADES_ARY)+5;
	 $size=100/$size;	
	 $t_width=0;
		
		foreach($T_GRADES_ARY as $value){
					$t_width=$t_width+$size;
		}		
		return $t_width;
}*/

function getExporterDetails($Id) {
global $iconn;
global $TblAdminExporterDetails;

$TblAdminExporterDetails;
$sql_exp="SELECT * FROM `$TblAdminExporterDetails` WHERE ExpID='$Id'";
$stmt_exp=$iconn->query($sql_exp);
 //$row_exp=$stmt_exp->fetch_array();
return $row_exp=$stmt_exp->fetch_array();


}


function getInvoiceNumber($iconn,$ExpID,$PoID){
  $ExporterData=getExporterDetails($ExpID);
  $InvShortCode=$ExporterData['InvShortCode'];

$CustomInvoiceSql="SELECT *,PI.ExporterID as ExporterID FROM ScrmPIData  as PI
left join ScrmCustomInvoices as SC on SC.PiID=PI.PoID WHERE PiID='$PoID'"; 
$CustomInvoiceExec=$iconn->query($CustomInvoiceSql); 
$CustomInvoiceRow = $CustomInvoiceExec->fetch_assoc();
	
	
$CurrentMonth = date('n',strtotime($CustomInvoiceRow['CInvDate']));   
if($CustomInvoiceRow['ExporterID']==5){		
if($CurrentMonth<=3){
 $NxtYear = date("y",strtotime($CustomInvoiceRow['CInvDate']));  	
 $CurrentYear = date("y",strtotime($CustomInvoiceRow['CInvDate']))-1;  	
} 
else{ 
$CurrentYear = date("y",strtotime($CustomInvoiceRow['CInvDate']));  	 
$NxtYear = date("y",strtotime($CustomInvoiceRow['CInvDate']))+1;  	
 
}
}else{	

if($CurrentMonth<=3){
 $NxtYear = date('y',strtotime($CustomInvoiceRow['CInvDate']));  	
 $CurrentYear = date('Y',strtotime($CustomInvoiceRow['CInvDate']))-1;  		
}else{ 
$CurrentYear = date('Y',strtotime($CustomInvoiceRow['CInvDate']));  	 
$NxtYear = date('y',strtotime($CustomInvoiceRow['CInvDate']))+1;  	
  
}
}
 
 return $InvShortCode.$CustomInvoiceRow['CInvNo'].'/'.$CurrentYear.'-'.$NxtYear; 
  
	
}

function getDayCodeDate($Day_Code){
//$Day_Code='9H07';
$D_C_S = str_split($Day_Code);

$Year=$D_C_S[0];
$Month=$D_C_S[1];
$Day=$D_C_S[2].$D_C_S[3];

$Date='';
if($Year!=''){
 $Y='202'.$Year;
}

/*
if($Year=='9'){

	$Y='2019';

}
else if($Year=='0'){

	$Y='2020';

}else if($Year=='1'){

	$Y='2021';

}else if($Year=='2'){

	$Y='2022';

}else if($Year=='2'){

	$Y='2023';

}else if($Year=='4'){

	$Y='2024';

}
*/

if($Month=='A'){
	$M='01';
}else if($Month=='B'){
	$M='02';
}else if($Month=='C'){
	$M='03';
}else if($Month=='D'){
	$M='04';
}else if($Month=='E'){
	$M='05';
}else if($Month=='F'){
	$M='06';
}else if($Month=='G'){
	$M='07';
}else if($Month=='H'){
	$M='08';
}else if($Month=='J'){
	$M='09';
}else if($Month=='K'){
	$M='10';
}else if($Month=='L'){
	$M='11';
}else if($Month=='M'){

	$M='12';
}



return $Date=$Day.'-'.$M.'-'.$Y;

}
function getUsebyDate($DATE){

 $TIME_STAMP = strtotime($DATE); 


    $dt = new DateTime($DATE);
   $dt->modify('+730 days');

 $DATE= $dt->format('r') . PHP_EOL;

	$TIME_STAMP = strtotime($DATE);
return $formated_date = date("d-m-Y", $TIME_STAMP);


// return $DATE;


}

function convertThreeDigit($digit1, $digit2, $digit3)
{
    $buffer = "";

    if ($digit1 == "0" && $digit2 == "0" && $digit3 == "0")
    {
        return "";
    }

    if ($digit1 != "0")
    {
        $buffer .= convertDigit($digit1) . " hundred";
        if ($digit2 != "0" || $digit3 != "0")
        {
            $buffer .= "  ";
        }
    }

    if ($digit2 != "0")
    {
        $buffer .= convertTwoDigit($digit2, $digit3);
    }
    else if ($digit3 != "0")
    {
        $buffer .= convertDigit($digit3);
    }

    return $buffer;
}

function convertTwoDigit($digit1, $digit2)
{
    if ($digit2 == "0")
    {
        switch ($digit1)
        {
            case "1":
                return "ten";
            case "2":
                return "twenty";
            case "3":
                return "thirty";
            case "4":
                return "forty";
            case "5":
                return "fifty";
            case "6":
                return "sixty";
            case "7":
                return "seventy";
            case "8":
                return "eighty";
            case "9":
                return "ninety";
        }
    } else if ($digit1 == "1")
    {
        switch ($digit2)
        {
            case "1":
                return "eleven";
            case "2":
                return "twelve";
            case "3":
                return "thirteen";
            case "4":
                return "fourteen";
            case "5":
                return "fifteen";
            case "6":
                return "sixteen";
            case "7":
                return "seventeen";
            case "8":
                return "eighteen";
            case "9":
                return "nineteen";
        }
    } else
    {
        $temp = convertDigit($digit2);
        switch ($digit1)
        {
            case "2":
                return "twenty $temp";
            case "3":
                return "thirty $temp";
            case "4":
                return "forty $temp";
            case "5":
                return "fifty $temp";
            case "6":
                return "sixty $temp";
            case "7":
                return "seventy $temp";
            case "8":
                return "eighty $temp";
            case "9":
                return "ninety $temp";
        }
    }
}

function convertDigit($digit)
{
    switch ($digit)
    {
        case "0":
            return "zero";
        case "1":
            return "one";
        case "2":
            return "two";
        case "3":
            return "three";
        case "4":
            return "four";
        case "5":
            return "five";
        case "6":
            return "six";
        case "7":
            return "seven";
        case "8":
            return "eight";
        case "9":
            return "nine";
    }
}


function numberTowords($number)
{
	$number=number_format($number, 2, '.', '');
    list($integer, $fraction) = explode(".", (string) $number);

    $output = "";

    if ($integer{0} == "-")
    {
        $output = "negative ";
        $integer    = ltrim($integer, "-");
    }
    else if ($integer{0} == "+")
    {
        $output = "positive ";
        $integer    = ltrim($integer, "+");
    }

    if ($integer{0} == "0")
    {
        $output .= "zero";
    }
    else
    {
        $integer = str_pad($integer, 36, "0", STR_PAD_LEFT);
        $group   = rtrim(chunk_split($integer, 3, " "), " ");
        $groups  = explode(" ", $group);

        $groups2 = array();
        foreach ($groups as $g)
        {
            $groups2[] = convertThreeDigit($g{0}, $g{1}, $g{2});
        }

        for ($z = 0; $z < count($groups2); $z++)
        {
            if ($groups2[$z] != "")
            {
                $output .= $groups2[$z] . convertGroup(11 - $z) . (
                        $z < 11
                        && !array_search('', array_slice($groups2, $z + 1, -1))
                        && $groups2[11] != ''
                        && $groups[11]{0} == '0'
                            ? "  "
                            : " "
                    );
            }
        }

        $output = rtrim($output, ", ");
		
    }

   /* if ($fraction > 0)
    {
        $output .= " Dollar and";
        for ($i = 0; $i < strlen($fraction); $i++)
        {
            $output .= " " . convertDigit($fraction{$i})." cents";
        }
    }*/
	
		
	//uma
	
	if($fraction > 0){ 
$output .= " Dollars and "; 
if($fraction < 20){ 
$output .= convertTwoDigit($fraction)." cents"; 
}elseif($fraction <100){ 
$output .= convertTwoDigit(substr($fraction,0,1)); 
if(convertDigit(substr($fraction,1,1))!='zero'){
$output .= "  ".convertDigit(substr($fraction,1,1))." cents"; 
}
ELSE{
	$output .= " cents"; 

}
}
 
//$rettxt .= "Dollar and no cents";
} 
else{
	$output .= " Dollars and no cents";
}
	
	
	

    return strtoupper($output);
	//uma 
}



function convertGroup($index)
{
    switch ($index)
    {
        case 11:
            return " decillion";
        case 10:
            return " nonillion";
        case 9:
            return " octillion";
        case 8:
            return " septillion";
        case 7:
            return " sextillion";
        case 6:
            return " quintrillion";
        case 5:
            return " quadrillion";
        case 4:
            return " trillion";
        case 3:
            return " billion";
        case 2:
            return " million";
        case 1:
            return " thousand";
        case 0:
            return "";
    }
}


function getPaymentValues($iconn, $PoID, $DocType)
{

	/*
		  $LbsFactor=0.454; 			// To Convert Lbs into Kgs  multiply value
		  $Discount=0;					// Get from Custom Invoice
		  $InsurancePerc=0.11;			// Varible
		  $AntiDumpingDFactor=101.35	// Anti Dumping Duty Factor(Formula)
		  $AntiDumpingMFactor=1.35		// Anti Dumping Duty Factor(Formula)
		  $StandardDutyCharges=125;	// Standard Value
		  $TermName=125;	// Standard Value
		  $CenttoUsd=100;	// Standard Value 1 usd=100 cents
		  
		  */

	//  echo $PoID; exit;

	$sqlDiscountType = "SELECT DiscountType FROM `AdminConfigDetails`";
        $stmtDiscountType = $iconn->query($sqlDiscountType);
        // $stmtDiscountType->execute();
        $dataRowDiscountType = $stmtDiscountType->fetch_assoc();
        $DiscountType=$dataRowDiscountType['DiscountType']; 

	// $SqlCInvDate = "SELECT CInvDate FROM `ScrmCustomInvoices` WHERE PiID=$PoID";
	// $StmtCInvDate = $iconn->query($SqlCInvDate);
	// $RowCInvDate = $StmtCInvDate->fetch_assoc();
	// $CInvDate = $RowCInvDate['CInvDate'];

	$CenttoUsd = 100;
	if ($DocType == 'CMI') {
		$SqlCInvData = "SELECT *,ExchngRte as ExRate,SPD.ExporterID as ExpID FROM ScrmPoData as SPD 
			LEFT JOIN ScrmCommercialData as SCD on 
			SPD.PoID=SCD.PoID WHERE SPD.PoID=$PoID";


	} else {
		$SqlCInvData = "SELECT *,SPD.ExporterID as ExpID FROM ScrmPoData as SPD 
			LEFT JOIN ScrmCustomInvoices as SCI on 
			SPD.PoID=SCI.PiID WHERE SPD.PoID=$PoID";
	}
		// echo $SqlCInvData; 


	$StmtCInvData = $iconn->query($SqlCInvData);
	$RowCInvData = $StmtCInvData->fetch_assoc();
	$TermName = $RowCInvData['ShippingTerms'];
	$Discount = $RowCInvData['Discount'];
	$FrieghtCharge = $RowCInvData['Freight'];
	$ExchangeRate = $RowCInvData['ExRate'];
	$QtyUnit = $RowCInvData['QtyUnit'];
	$CInvCode = $RowCInvData['CInvCode'];
	$ExporterID = $RowCInvData['ExpID'];

	/**uma(14-10-2023) */
	// $SqlGstPerc = "SELECT DISTINCT GstPerc FROM `ScrmPoGradeData` as SPGD 
	// 	left join ScrmPIVarietyList as SPVL on SPVL.VarietyID=SPGD.VarietyID 
	// 	left join ScrmPreparationMaster as SPM on SPM.ShortCode=SPVL.PreparationType WHERE InvId='$PoID'";
		
		$SqlGstPerc = "SELECT DISTINCT GstPerc,FDAEntryDate,CInvDate FROM `ScrmPoGradeData` as SPGD 
		left join ScrmCustomInvoices as SCI on SCI.PiID=SPGD.InvId
		left join ScrmPIVarietyList as SPVL on SPVL.VarietyID=SPGD.VarietyID 
		left join ScrmISFDetails as ISF on ISF.PIID=SPGD.InvId		
		 left join ScrmPreparationMaster as SPM on SPM.ShortCode=SPVL.PreparationType WHERE InvId='$PoID'";


	$StmtGstPerc = $iconn->query($SqlGstPerc);
	$RowGstPerc = $StmtGstPerc->fetch_assoc();
	//while($RowHsCode=$StmtHsCode->fetch_assoc()){
	// $ArrayHsCode[]=$RowHsCode['HSCode'];
	// $CInvCode = implode(',', $ArrayHsCode);

	//}

	$IgstPerc = $RowGstPerc['GstPerc'];
	$FDAEntryDate = $RowGstPerc['FDAEntryDate'];
	$CInvDate = $RowGstPerc['CInvDate'];
	// if($CInvCode=='03061720'){
	// 	$IgstPerc=5;
	// }else{
	// 	$IgstPerc=12;	
	// }

	/**uma (14-10-2023)*/
	$QtyUnit = $RowCInvData['QtyUnit'];
	$Query=" and FromDate<='$CInvDate'";
	if ($DocType == 'CMI') {
	if($FDAEntryDate!=''){
		$Query=" and FromDate<='$FDAEntryDate'";
	}
}
	// else{
	// 	$Query=" and FromDate<='$CInvDate'";
	// }

	 $SqlShipTermsMaster = "SELECT * FROM `ScrmShipmentTermsMaster` where TermName='$TermName'"; 
	$StmtShipTermsMaster = $iconn->query($SqlShipTermsMaster);
	$RowShipTermsMaster = $StmtShipTermsMaster->fetch_assoc();

	// Update With ExportId Condition
	//$SqlExporterTaxRates="SELECT * FROM `ScrmExporterTaxRates`";
	// $SqlExporterTaxRates = "SELECT * FROM `ScrmExporterTaxRates` where FromDate<='$CInvDate'  ORDER BY ExpTRID DESC limit 1";
	  $SqlExporterTaxRates = "SELECT * FROM `ScrmExporterTaxRates` where ExpID='$ExporterID' $Query  ORDER BY ExpTRID DESC limit 1"; 
	$StmtExporterTaxRates = $iconn->query($SqlExporterTaxRates);
	$RowExporterTaxRates = $StmtExporterTaxRates->fetch_assoc();

	$AntiDumpingDutyPerc = $RowExporterTaxRates['AntiDumpingDuty'];
	$CountervailingDutyPerc = $RowExporterTaxRates['CountervailingDuty'];//uma cvd calculation included //18-03-2024
	 $MerchandiseProcessFeePerc = $RowExporterTaxRates['MerchandiseProcessFee']; 
	$HarbourMaintenanceFeePerc = $RowExporterTaxRates['HarbourMaintenanceFee'];
	$ISFStaticDuty = $RowExporterTaxRates['ISFStaticDuty'];
	$InsurancePerc = $RowExporterTaxRates['InsurancePremiumRate'];
	// if ($DocType == 'CMI') {
	$ClearanceChargePerc = $RowExporterTaxRates['ClearanceCharge'];//uma ClearanceCharge calculation included //01-04-2024
	// }
	// $TotalDutyPerc = 100 + $AntiDumpingDutyPerc +
	// 	$MerchandiseProcessFeePerc +
	// 	$HarbourMaintenanceFeePerc;
		
		$TotalDutyPerc = 100 + $AntiDumpingDutyPerc +
		$MerchandiseProcessFeePerc +
		$HarbourMaintenanceFeePerc+$CountervailingDutyPerc; //uma cvd calculation included //18-03-2024

// echo  $TotalDutyPerc; exit;

	$IsFreight = $RowShipTermsMaster['Freight'];
	$IsDuty = $RowShipTermsMaster['Duty'];
	$Insurance = $RowShipTermsMaster['Insurance'];
	$AntiDumpingDFactor = $RowShipTermsMaster['AntiDumpingDFactor'];
	$AntiDumpingMFactor = $RowShipTermsMaster['AntiDumpingMFactor'];
	$StandardDutyCharges = $RowShipTermsMaster['StandardDutyCharges'];



	$sqlExt = "sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
				sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
				sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
				sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs";


	$SqlTotal = "SELECT sum(Mcs) as MCS,  $sqlExt  ,
			sum(Price) as Price,			
			sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
			sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs 
			FROM `ScrmPoGradeData` 
			as PGD left join AdminPackingStyles as PS on 
			PGD.PackingStyleID=PS.PCKID WHERE InvId='$PoID'";
	$StmtTotal = $iconn->query($SqlTotal);
	$RowTotal = $StmtTotal->fetch_assoc();

	$NetWgtKgs = $RowTotal['NetWgtLbs'] * $LbsFactor;
	$NetWgtLbs = $RowTotal['NetWgtLbs'];

	$GrossWeightKgs = $RowTotal['GrossWeightKgs'];
	$GrossWeightLbs = $RowTotal['GrossWeightLbs'];



	if ($QtyUnit == 'KGS') {
		$Weight = $RowTotal['NetWgtKgs'];
		 $InvoiceValue = $RowTotal['PriceKgs']; 
	} else {
		$Weight = $RowTotal['NetWgtLbs'] * $LbsFactor;
		 $InvoiceValue = $RowTotal['PriceLbs'];
	}
	
	if ($Discount > 0) {
		
		if($DiscountType==0){
		$DiscountAmount = ($InvoiceValue * $Discount) / 100;
		$InvoiceValueAfterDiscount = $InvoiceValue; 
		$InvoiceValueAfterDiscount = $InvoiceValue - $DiscountAmount;	// By Rantagiri 04/17/2023 5.44			
		}

		else{
			$DiscountAmount =$Discount;
			$InvoiceValueAfterDiscount = $InvoiceValue;
			$InvoiceValueAfterDiscount = $InvoiceValue - $DiscountAmount;  
		}

	} 
	
	else {
		$InvoiceValueAfterDiscount = $InvoiceValue;
	}

	if ($Insurance == 1) {
		//$InsuranceValue= ($PriceAfterDiscount*$InsurancePerc)/100;
		$InsuranceValue = ($InvoiceValueAfterDiscount * $InsurancePerc) / 100;
	} else {
		$InsuranceValue = 0;
	}

	 

	if ($IsDuty == 1) {

		$ClearanceChargeFee = (($InvoiceValueAfterDiscount- $InsuranceValue - $FrieghtCharge)*$ClearanceChargePerc)/100;//#jme uma ClearanceCharge calculation included //01-04-2024

		$MerchandiseProcessFee = (($InvoiceValueAfterDiscount - $InsuranceValue - $FrieghtCharge) / $TotalDutyPerc) * $MerchandiseProcessFeePerc;
		//$MerchandiseProcessFee=round((($EffInvValue*$MerchandiseProcessFeePerc)/$TotalDutyPerc),2);
		if ($MerchandiseProcessFeePerc == 0) {
			$ISFStatic = false;
			$MerchandiseProcessFee = $ISFStaticDuty;
			$EffInvValue = $InvoiceValueAfterDiscount - $InsuranceValue - $FrieghtCharge;
			// $MerchandiseProcessFee=round((($EffInvValue*$MerchandiseProcessFeePerc)/$TotalDutyPerc),2);

		} else if ($MerchandiseProcessFee > $ISFStaticDuty) {
			$ISFStatic = true;
			 $MerchandiseProcessFee = $ISFStaticDuty; 
			$TotalDutyPerc = $TotalDutyPerc - $MerchandiseProcessFeePerc;
			$EffInvValue = $InvoiceValueAfterDiscount - $InsuranceValue - $FrieghtCharge - $MerchandiseProcessFee;
		} else {
			$ISFStatic = false;
			$EffInvValue = $InvoiceValueAfterDiscount - $InsuranceValue - $FrieghtCharge; 
			$MerchandiseProcessFee = round((($EffInvValue * $MerchandiseProcessFeePerc) / $TotalDutyPerc), 2);
			// $MerchandiseProcessFee=$ISFStaticDuty;

		}
		// ECHO $EffInvValue; EXIT;
		 $EffInvValue=$EffInvValue-$ClearanceChargeFee;//#jme uma ClearanceCharge calculation included //01-04-2024

		 $AntiDumpingCharges = round((($EffInvValue * $AntiDumpingDutyPerc) / $TotalDutyPerc), 2);
		$HarbourMaintenanceFee = round((($EffInvValue * $HarbourMaintenanceFeePerc) / $TotalDutyPerc), 2);
		$CountervailingDutyFee = round((($EffInvValue * $CountervailingDutyPerc) / $TotalDutyPerc), 2);//uma cvd calculation included //18-03-2024
		
		if ($ISFStatic) {
			// $DutiesPrice = $AntiDumpingCharges + $HarbourMaintenanceFee;
		 	 $DutiesPrice = $AntiDumpingCharges + $HarbourMaintenanceFee+$CountervailingDutyFee; 
		 	// $DutiesPrice = $AntiDumpingCharges + $HarbourMaintenanceFee+$CountervailingDutyFee+$ClearanceChargeFee; //uma ClearanceCharge calculation included //01-04-2024
		} else {
			// $DutiesPrice = $AntiDumpingCharges + $MerchandiseProcessFee + $HarbourMaintenanceFee;
			$DutiesPrice = $AntiDumpingCharges + $MerchandiseProcessFee + $HarbourMaintenanceFee+$CountervailingDutyFee;//uma cvd calculation included //18-03-2024
			// $DutiesPrice = $AntiDumpingCharges + $MerchandiseProcessFee + $HarbourMaintenanceFee+$CountervailingDutyFee+$ClearanceChargeFee;//uma ClearanceCharge calculation included //01-04-2024
		}

	} else {
		 $DutiesPrice = $DutiesPrice + $StandardDutyCharges; 
	}

	// echo $DutiesPrice; exit;


	$InvValueUSD = $InvoiceValueAfterDiscount; 
// echo	$DutiesPrice; exit;
	if ($ISFStatic) {

		//commented by uma
		// $InvValueUSDWithOutDuties = $InvoiceValueAfterDiscount - $FrieghtCharge - $ISFStaticDuty;
		// $InvValueINRWithOutDuties = $InvValueUSDWithOutDuties * $ExchangeRate;
		// $FOBValueUSD = $InvoiceValueAfterDiscount - $FrieghtCharge - $DutiesPrice - $ISFStaticDuty; 

		//commented by uma
		
		$InvValueUSDWithOutDuties = $InvoiceValueAfterDiscount - $FrieghtCharge - $ISFStaticDuty-$InsuranceValue;//#jme uma ClearanceCharge calculation included //01-04-2024
		$InvValueINRWithOutDuties = $InvValueUSDWithOutDuties * $ExchangeRate;
		$FOBValueUSD = $InvoiceValueAfterDiscount - $FrieghtCharge - $DutiesPrice - $ISFStaticDuty-$InsuranceValue-$ClearanceChargeFee;//#jme uma ClearanceCharge calculation included //01-04-2024
	} else {
		// $InvValueUSDWithOutDuties = $InvoiceValueAfterDiscount - $FrieghtCharge;
		// $InvValueINRWithOutDuties = $InvValueUSDWithOutDuties * $ExchangeRate;
		// $FOBValueUSD = $InvoiceValueAfterDiscount - $FrieghtCharge - $DutiesPrice;
		
		// $InvValueUSDWithOutDuties = $InvoiceValueAfterDiscount - $FrieghtCharge-$InsuranceValue;
		$InvValueUSDWithOutDuties = $InvoiceValueAfterDiscount - $FrieghtCharge-$InsuranceValue;//#jme uma ClearanceCharge calculation included //01-04-2024
		$InvValueINRWithOutDuties = $InvValueUSDWithOutDuties * $ExchangeRate;
		$FOBValueUSD = $InvoiceValueAfterDiscount - $FrieghtCharge - $DutiesPrice-$InsuranceValue-$ClearanceChargeFee;//#jme uma ClearanceCharge calculation included //01-04-2024
	}


	$InvValueINR = $InvValueUSD * $ExchangeRate;
	$FOBValueINR = $FOBValueUSD * $ExchangeRate;

	$IgstValue = ($InvValueINR * $IgstPerc) / 100;



	$AryPrices['DiscountPerc'] = $Discount;
	$AryPrices['ExchangeRate'] = $ExchangeRate;
	$AryPrices['DiscountAmount'] = $DiscountAmount;
	$AryPrices['InsuranceValue'] = $InsuranceValue;
	$AryPrices['DutiesPrice'] = $DutiesPrice;
	$AryPrices['FrieghtCharge'] = $FrieghtCharge;

	$AryPrices['InvValueUSD'] = $InvValueUSD;
	$AryPrices['InvValueUSDWithOutDuties'] = $InvValueUSDWithOutDuties;
	$AryPrices['InvValueINRWithOutDuties'] = $InvValueINRWithOutDuties;
	$AryPrices['InvValueINR'] = $InvValueINR;
	$AryPrices['FOBValueUSD'] = $FOBValueUSD;
	$AryPrices['FOBValueINR'] = $FOBValueINR;
	$AryPrices['PaymentTerm'] = $TermName;
	$AryPrices['IgstPerc'] = $IgstPerc;
	$AryPrices['IgstValue'] = $IgstValue;
	$AryPrices['AntiDumpingDuty'] = $AntiDumpingCharges; 
	$AryPrices['MerchantDuty'] = $MerchandiseProcessFee;
	 $AryPrices['HarborDuty'] = $HarbourMaintenanceFee; 
	 $AryPrices['CountervailingDuty'] = $CountervailingDutyFee; //uma cvd calculation included //18-03-2024
	 $AryPrices['ClearanceCharge'] = round($ClearanceChargeFee,2); //uma ClearanceCharge calculation included //01-04-2024
	$AryPrices['ISFStatic'] = $ISFStatic; 
	$AryPrices['CenttoUsd'] = $CenttoUsd;
	$AryPrices['ActualPrice'] = $InvoiceValue;
	 $AryPrices['HarbourMaintenanceFeePerc'] = $HarbourMaintenanceFeePerc;
	$AryPrices['CountervailingDutyPerc'] = $CountervailingDutyPerc;//uma cvd calculation included //18-03-2024
	$AryPrices['ClearanceChargePerc'] = $ClearanceChargePerc;//uma ClearanceCharge calculation included //01-04-2024
	$AryPrices['AntiDumpingDutyPerc'] = $AntiDumpingDutyPerc;
	$AryPrices['MerchandiseProcessFeePerc'] = $MerchandiseProcessFeePerc;
	

	return $AryPrices;

}
 
 function getfilename($iconn,$ExpID,$PoID){
  $ExporterData=getExporterDetails($ExpID);
   $InvShortCode=$ExporterData['InvShortCode'];
  $InvDateFormat=$ExporterData['InvDateFormat'];

$CustomInvoiceSql="SELECT *,PI.ExporterID as ExporterID FROM ScrmPIData  as PI
left join ScrmCustomInvoices as SC on SC.PiID=PI.PoID WHERE PiID='$PoID'"; 
$CustomInvoiceExec=$iconn->query($CustomInvoiceSql); 
$CustomInvoiceRow = $CustomInvoiceExec->fetch_assoc();
	
	
$CurrentMonth = date('n',strtotime($CustomInvoiceRow['CInvDate']));   
if($InvDateFormat=='Short'){		
if($CurrentMonth<=3){
 $NxtYear = date("y",strtotime($CustomInvoiceRow['CInvDate']));  	
 $CurrentYear = date("y",strtotime($CustomInvoiceRow['CInvDate']))-1;  	
} 
else{ 
$CurrentYear = date("y",strtotime($CustomInvoiceRow['CInvDate']));  	 
$NxtYear = date("y",strtotime($CustomInvoiceRow['CInvDate']))+1;  	
 
}
}else{	

if($CurrentMonth<=3){
 $NxtYear = date('y',strtotime($CustomInvoiceRow['CInvDate']));  	
 $CurrentYear = date('Y',strtotime($CustomInvoiceRow['CInvDate']))-1;  		
}else{ 
$CurrentYear = date('Y',strtotime($CustomInvoiceRow['CInvDate']));  	 
$NxtYear = date('y',strtotime($CustomInvoiceRow['CInvDate']))+1;  	
  
}
}

$AryFilename['InvShortCode']=$InvShortCode;
$AryFilename['CInvNo']=$CustomInvoiceRow['CInvNo'];
$AryFilename['FinancialYear']=$CurrentYear.'-'.$NxtYear;
 
 //return $InvShortCode.$CustomInvoiceRow['CInvNo'].'/'.$CurrentYear.'-'.$NxtYear; 
 return $AryFilename; 
  
	
}


?>