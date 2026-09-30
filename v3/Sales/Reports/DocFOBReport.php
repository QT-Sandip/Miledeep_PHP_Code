<style>
	table tr td {

		font-size: 16px;
		text-align: left;
	}

	table tr th {

		font-size: 13px;
		text-align: left;
		background: white;
		position: sticky;
		top: 0;
	}
</style>
<?php

session_start();

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
$AdminConfigDetails = json_decode(json_encode($_SESSION['ClientConfigData']), true);
$ClientId = $_SESSION['ClientID'];

$QueryParms = $_GET['Q'];

if ($QueryParms != '') {
	$QueryParms = urlDecode64($QueryParms);
	$queries = array();
	parse_str($QueryParms, $_GET);
}

$Type = $_GET['Type'];

if ($Type == 2 || $Type == 1) {

	$Month = $_GET['Month'];
	$Year = $_GET['Year'];
	$PsID = $_GET['PsID'];
} else {
	$Month = $_GET['from_date'];
	$Year = $_GET['to_date'];
}


$B_TYPE = $_GET['TYPE'];
$BAR_CODE_ID = $_GET['BID'];
$LOT_ID = $_GET['ID'];

$Exporter = $_GET['Exporter'];

// echo print_r($Type);
// exit;

if ($Type == 1) {
	$Query = "month(CInvDate)='$Month'  and year(CInvDate)='$Year'  ";
} else {
	$Query = "CInvDate between '$Month'  and '$Year'  ";
}

if ($Exporter != '') {
	//$Query.='';
	$Query .= ' and SPD.ExporterID=' . $Exporter;


	// if($Exporter==1){
	// 	$Query.='and SPD.ExporterID!=5';
	// }
	// 	else{

	// 	$Query.=' and SPD.ExporterID='.$Exporter;
	// 	}
	$SqlExporter = "SELECT * FROM AdminExporterDetails where ExpID=$Exporter";
	$StmtExporter = $iconn->query($SqlExporter);

	$RowExporter = $StmtExporter->fetch_array();
	$ExporterName = $RowExporter['ExpName'];
}
//$Query='';

$monthName = date("F", mktime(0, 0, 0, $Month, 10));

if ($Type == 1) {
	$Header = 'FOB Report-(' . $monthName . ')';
} else {
	$Header = 'FOB Report-(' . getFormatedDate($Month) . ' To ' . getFormatedDate($Year) . ')';
}

$file_name = 'FOB Report -' . $from_date . '-' . $to_date . '.xlsx';

$date_end = $to_date . ' 23:59:59';



$SqlPo = "SELECT *,SCL.name as DestinationCountryName FROM `ScrmCustomInvoices` as SCI 
	 LEFT JOIN ScrmPoData as SPD on SPD.PoID=SCI.PiID
     LEFT JOIN  ScrmConsigneeDetails as SCD on SCD.ConID=SPD.ConsigneeID	 
     left JOIN  ScrmShipmentDetails as SSD on SSD.PiID=SCI.PiID
     left JOIN ScrmShipmentTermsMaster as SSTM on SSTM.TermName=SPD.ShippingTerms
     left JOIN ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
      where $Query  ORDER BY SCI.CInvNo ASC";
$StmtPo = $iconn->query($SqlPo);



$content = '';


$size = 40;

$size1 = 60;





$S_NO = 1;
$width = 11;
$width_s = 8;
$width_w = $width + 5;

$content .= '
				<h3 align="center">' . $ExporterName . '</h3>
			  <h3 align="center"> ' . $Header . '</h3>
			  
			  
			  <table width = "100%" border="1" cellspacing="0" cellpadding="5">
			   <tr>  

           		

                <th  align="center" ><B>S No.</B></th>              
				 
				<th  align="center" ><B>Report ID</B></th>
				<th  align="center" ><B> C Inv No</B></th>
				   <th  align="center" ><B>C Inv Date</B></th>			
				   <th  align="center" ><B> Buyer Name</B></th>					   
				   <th  align="center" ><B>Destination</B></th>					   
				   <th  align="center" ><B> Total Inv Value in US$</B></th>	
				   <th  align="center" ><B>Total Invoice in INR₹</B></th>				   
				   <th  align="center" ><B>FOB Value in US$</B></th>					   
				   <th  align="center" ><B>FOB Value in INR ₹</B></th>			
				   <th  align="center" ><B>Net Weight(KGS)</B></th>			
				   <th  align="center" ><B>Net Weight(LBS)</B></th>			
                <th  align="center"     ><B>EIA Code </B></th>
                
    
               </tr> 
			   
			  ';


//	
$TotalFOBUS = 0;
$TotalFOB = 0;
$GrandTotalDiscount = 0;
$TotalInvoiceInINR = 0;
$TotalNetWgtKgs = 0;
$TotalNetWgtLbs = 0;


while ($RowPo = $StmtPo->fetch_array()) {

	$ProcessorID = $RowPo['ProcessorID'];
	$Discount = $RowPo['Discount'];
	$PiID = $RowPo['PoID'];
	$FrieghtCharge = $RowPo['FrieghtCharge'];
	//$DestinationCountryId=$RowPo['DestinationCountry']; 
	$DestinationCountryName = $RowPo['DestinationCountryName'];
	$ExchangeRate = $RowPo['ExchangeRate'];
	$CInvCode = $RowPo['CInvCode'];
	$IGST = $RowPo['IGST'];
	$ShippingTerms = $RowPo['ShippingTerms'];
	$CInvDate = $RowPo['CInvDate'];
	$DestinationCountryId = $RowPo['DestinationCountry'];



	//$CInvCode=$RowPo['CInvCode'];

	/*if($CInvCode==16052900){
				$POType='Cooked';
				
			}

else if($CInvCode==03061720){
				$POType='Raw';
				
			}
else{
	$POType='Raw & Cooked';
}
*/




	$SqlProcessor = "SELECT * FROM `AdminExporterDetails` WHERE ExpID='$ProcessorID'";
	$StmtProcessor = $iconn->query($SqlProcessor);
	$RowProcessor = $StmtProcessor->fetch_array();

	$CanadaLbsFactor = $DestinationCountryId == 23 && $CInvDate >= '2026-03-01' ? 2.2046 : 'LbsFactor';


	$sqlExtAll = "sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
				sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
				sum(Mcs*TotalSlabs*SlabWeight*$CanadaLbsFactor) as NetWgtLbs,
				sum(Mcs*GrossWeight*$CanadaLbsFactor) as GrossWeightLbs";

	$sqlExtSme = "sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
		sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
		CASE WHEN PS.Uom = 'GRAMS' THEN
			sum(Mcs*TotalSlabs*SlabWeight*MFactor*$CanadaLbsFactor)
		ELSE
			sum(Mcs*TotalSlabs*SlabWeight*LbsFactor)
		END AS NetWgtLbs,
		CASE WHEN PS.Uom = 'GRAMS' THEN
			sum(Mcs*GrossWeight*MFactor*$CanadaLbsFactor)
		ELSE
			sum(Mcs*GrossWeight*LbsFactor)
		END AS GrossWeightLbs";

	$sqlExt = $ClientId == 'SMEV3' ? $sqlExtSme : $sqlExtAll;

	/********uma********/
	// $sqlExt = "sum(Mcs*TotalSlabs*SlabWeight*MFactor) as NetWgtKgs,
	// 			sum(Mcs*GrossWeight*MFactor) as GrossWeightKgs,
	// 			sum(Mcs*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,
	// 			sum(Mcs*GrossWeight*LbsFactor) as GrossWeightLbs";


	$SqlTotal = "SELECT sum(Mcs) as MCS,  $sqlExt  ,
			sum(Price) as Price,			
			sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
			sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs			FROM `ScrmPoGradeData` 
			as PGD left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE InvId='$PiID'";
	$StmtTotal = $iconn->query($SqlTotal);
	$RowTotal = $StmtTotal->fetch_assoc();

	//$NetWgtKgs = $RowTotal['NetWgtKgs'];
	//$NetWgtKgs = $RowTotal['NetWgtLbs']*0.454;
	$NetWgtLbs = $RowTotal['NetWgtLbs'];
	$GrossWeightKgs = $RowTotal['GrossWeightKgs'];
	$GrossWeightLbs = $RowTotal['GrossWeightLbs'];

	$RowPo['QtyUnit'];

	if ($RowPo['QtyUnit'] == 'KGS') {
		//$Weight = $RowTotal['NetWgtKgs'];	
		$NetWgtKgs = $RowTotal['NetWgtKgs'];
		$Price = $RowTotal['PriceKgs'];
	} else {
		//$Weight = $RowTotal['NetWgtLbs'] *0.454;
		$NetWgtKgs = $CanadaLbsFactor > 0 ? $RowTotal['NetWgtLbs'] / $CanadaLbsFactor : $RowTotal['NetWgtLbs'] * 0.454;
		$Price = $RowTotal['PriceLbs'];
	}

	$UsdValue = $Price;
	//echo	$NetWgtKgs;  exit;

	$DutiesExemptionShippingTerms = ['CIF', 'CNF', 'CFR', 'FOB'];

	$AdminConfigDetails['CanadaLbsFactor'] = $CanadaLbsFactor;
	$AryPaymentData = getPaymentValues($iconn, $PiID, '', $AdminConfigDetails);
	$InsuranceValue = $AryPaymentData['InsuranceValue'];
	$ExchangeRate = $AryPaymentData['ExchangeRate'];
	$DiscountPerc = $AryPaymentData['DiscountPerc'];
	$DiscountAmount = $AryPaymentData['DiscountAmount'];
	$IgstPerc = $AryPaymentData['IgstPerc'];
	$IgstValue = $AryPaymentData['IgstValue'];
	$InvValueUSD = $AryPaymentData['InvValueUSD'];
	$InvValueInr = $AryPaymentData['InvValueINR'];


	$TotalMcs = $AryPaymentData['TotalMcs'];
	$Price = $AryPaymentData['Price'];

	$InvValueUSDWithOutDuties = $AryPaymentData['InvValueUSDWithOutDuties'];
	$InvValueINRWithOutDuties = $AryPaymentData['InvValueINRWithOutDuties'];

	if ($RowPo['ShowFrightCharge'] == 1 && $RowPo['ShowFrieght'] == 1 && !in_array($ShippingTerms, $DutiesExemptionShippingTerms)) {
		$FOBValueUSD = $AryPaymentData['FOBValueUSD'];
		$FOBValueINR = $AryPaymentData['FOBValueINR'];
	} else {
		//$AryData['ShowFrightCharge']=false;	

		$FOBValueUSD = $AryPaymentData['InvValueUSDWithOutDuties'];
		$FOBValueINR = $AryPaymentData['InvValueINRWithOutDuties'];
	}


	$TotalFOBUS += $FOBValueUSD;
	$TotalFOB += $FOBValueINR;
	$GrandTotalDiscount += $InvValueUSD;
	$TotalInvoiceInINR += $InvValueInr;
	//$GrandTotalDiscount+=$UsdValue;
	$TotalNetWgtKgs += $NetWgtKgs;
	$TotalNetWgtLbs += $NetWgtLbs;



	$content .=

		'<tr>
				 <td  align="center" >' . $S_NO . '</td>
				 <td  align="center" >' . $PiID . '</td>
				 <td  align="center" >' . $RowPo['CInvNo'] . '</td>
				 <td  align="center" >' . date('d-m-Y', strtotime($RowPo['CInvDate'])) . '</td>
				 <td  align="left"   >' . $RowPo['ConsigneeName'] . '</td>
				 <td  align="left"   >' . $DestinationCountryName . '</td>
				<!-- <td  align="center" >' . number_format($UsdValue, 2) . '</td>-->
				 <td  align="center" >' . number_format($InvValueUSD, 2) . '</td>
				<td  align="center" >' . number_format($InvValueInr, 2) . '</td>
				 <td  align="center" >' . number_format($FOBValueUSD, 2) . '</td>
				 <td  align="center" >' . round($FOBValueINR) . '.00</td>
				 <td  align="center" >' . number_format($NetWgtKgs, 3) . '</td>
				 <td  align="center" >' . number_format($NetWgtLbs, 3) . '</td>
				 <td  align="center" >' . $RowProcessor['ExpApprovalNo'] . '</td>
				  
				 </tr>';

	$S_NO++;
}

$content .=

	'<tr>  
	           <td  align="center">Total</td>				 
               <td  align="center" ></td>				 
               <td  align="center" ></td>				 
               <td  align="center" ></td>				 
               <td  align="center" ></td>				 
               <td  align="center" ></td>				 
               <td  align="center" >' . number_format($GrandTotalDiscount, 2) . '</td>
			   	<td  align="center" >' . number_format($TotalInvoiceInINR, 2) . '</td>			 
               <td  align="center" >' . number_format($TotalFOBUS, 2) . '</td>				 			 
			   <td  align="center" >' . round($TotalFOB) . '.00 </td>
			   <td  align="center" >' . number_format($TotalNetWgtKgs, 3) . ' </td>
			   <td  align="center" >' . number_format($TotalNetWgtLbs, 3) . ' </td>
			   <td  align="center" > </td>
 
		 </tr>';

$content .= '</table>';


echo $content;


?>