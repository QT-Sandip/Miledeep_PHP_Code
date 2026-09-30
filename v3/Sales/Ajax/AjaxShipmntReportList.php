<?php
include("../RestAPI.php");
$FromDate = trim($_POST["FromDate"]);
$ToDate = trim($_POST["ToDate"]);
$Exporter = trim($_POST["Exporter"]);
$Country = trim($_POST["Country"]);

$ClientID = $_SESSION['ClientID'];
$DisplayCFCharges = isset($ClientID) && $ClientID === 'DFLV3' ? true : false;
$DisplayTariff = isset($ClientID) && $ClientID === 'DFLV3' ? true : false;
$ShowReciprocalTariffPerc = isset($ClientID) && $ClientID === 'SMEV3' ? true : false;
$DisplayProductInfo = isset($ClientID) && $ClientID === 'CAPLV3' ? 1 : 0;

$api = GetShipmentReportDataAPI;

$bodyContent = array(
	"FromDate" => $FromDate,
	"ToDate" => $ToDate,
	"Exporter" => $Exporter,
	"Country" => $Country
);

// var_dump($bodyContent);
$resGetShipmentReportDataAPI = CallAPI($api, "GET", "", $bodyContent);

// echo json_encode($resGetShipmentReportDataAPI, JSON_PRETTY_PRINT);exit;
// echo $_SESSION["Token"]." pp";

$resTotals = $resGetShipmentReportDataAPI->Data->Totals;
$resList = $resGetShipmentReportDataAPI->Data->ShipmentReportData;

// $api = GetPaymentValuesAPI;
// $resGetPaymentValuesAPI = CallAPI($api, "GET", "", $bodyContent);
// var_dump($resList);
?>
<table id="key-datatable" data-paging="false" class="table  wrap w-150">
	<thead class="thead-light">
		<tr>
			<th style="min-width: 60px;" class="sticky etaStatusCol1">S.No.</th>
			<th style="min-width: 160px;" class="sticky etaStatusCol1">Invoice No</th>
			<th style="min-width: 120px;">Invoice date</th>
			<th style="min-width: 220px;">Po No</th>
			<th style="min-width: 120px;">Po Date</th>
			<th style="min-width: 120px;">Stock Out Date</th>
			<th style="min-width: 200px;">Consignee</th>
			<th style="min-width: 80px;">CY/SD</th>
			<th style="min-width: 200px;">Terms of Delivery</th>
			<th style="min-width: 400px;">Terms of Payment</th>
			<th style="min-width: 150px;">Port of loading</th>
			<th style="min-width: 150px;">Country</th>
			<th style="min-width: 150px;">Port of Discharge</th>
			<th style="min-width: 180px;">Final Destination</th>
			<?php 
			if(!empty($DisplayProductInfo)){?>
				<th style="min-width: 150px;">Packing Style</th>
				<th style="min-width: 100px;">Grade</th>
				<th style="min-width: 200px;">Product Description</th>
			<?php } ?>
			<th style="min-width: 180px;">On Board Date</th>
			<th style="min-width: 180px;">Shipping Bill No</th>
			<th style="min-width: 180px;">Shipping Bill Date</th>
			<th style="min-width: 210px;">Shipping Bill Exchange Rate</th>
			<th style="min-width: 210px;">FDA Entry Date</th>
			<th style="min-width: 210px;">FDA Entry number</th>
			<th style="min-width: 180px;">AntiDumping Duty</th>
			<th style="min-width: 180px;">Countervailing Duty</th>
			<th style="min-width: 180px;">Merchandise Process Fee</th>
			<th style="min-width: 180px;">Harbour Maintenance Fee</th>
			<th style="min-width: 180px;">Reciprocal Tariffs Fee</th>
			<?php if ($ShowReciprocalTariffPerc) { ?>
				<th style="min-width: 180px;">Reciprocal Tariffs %</th>
			<?php } ?>
			<?php if ($DisplayCFCharges) { ?>
				<th style="min-width: 180px;">Clearing & Forwarding Charges</th>
			<?php } ?>
			<?php if ($DisplayTariff) { ?>
				<th style="min-width: 180px;">Tariff</th>
			<?php } ?>
			<th style="min-width: 180px;">Custom Invoice Freight $</th>
			<th style="min-width: 200px;">BL.NO</th>
			<th style="min-width: 120px;">BL.Date</th>
			<th style="min-width: 150px;">Container No</th>
			<th style="min-width: 150px;">Liner Seal Number</th>
			<th style="min-width: 150px;">Custom Seal No</th>
			<th style="min-width: 200px;">Quantity Net weight KGs</th>
			<th style="min-width: 200px;">Quantity Net weight LBs</th>
			<th style="min-width: 180px;">Invoice Value $</th>
			<th style="min-width: 180px;">FOB Value</th>
			<th style="min-width: 180px;"> Commercial Freight $</th>
			<th style="min-width: 180px;">LEO Date</th>
			<th style="min-width: 180px;">Shipping Line</th>
		</tr>
	</thead>
	<tbody>
		<?php
		$TotalNetWeightKgs = 0;
		$TotalNetWeightLbs = 0;
		$TotalInvoiceValueUSD = 0;
		$TotalFOBValueUSD = 0;

		foreach ($resList as $item) {
			// echo print_r($item->ArrayPaymentValues);
			// $paymentValues = json_decode($item->ArrayPaymentValues);
			$TotalNetWeightKgs = $QuantityNetWeightKgs;
			$TotalNetWeightLbs = $QuantityNetWeightLbs;
			$TotalInvoiceValueUSD = $TotalInvoiceValueUSD;
			$TotalFOBValueUSD = $FOBValue;
			$country = trim(strtoupper($item->Country));
			?>
			<tr>
				<td class="sticky etaStatusCol1"><?php echo $item->sno ?></td>
				<td class="sticky etaStatusCol1 bodyTotInv"><?php echo $item->InvNo ?></td>
				<td><?php echo $item->InvoiceDate // date('d/m/Y', strtotime($item->InvoiceDate)); // $item->InvoiceDate 
						?>
				</td>
				<td><?php echo preg_replace('/^\s+|\s+$/u', '', $item->PoNo); ?></td>
				<td><?php echo $item->PoDate ?></td>
				<td><?php echo $item->StockOutDate ?></td>
				<td><?php echo $item->Consignee ?></td>
				<td><?php echo $item->CYSD ?></td>
				<td><?php echo $item->TermsofDelivery ?></td>
				<td><?php echo $item->TermsofPayment ?></td>
				<td><?php echo $item->PortofLoading ?></td>
				<td><?php echo $item->Country ?></td>
				<td><?php echo $item->PortOfDischarge ?></td>
				<td><?php echo $item->FinalDestination ?></td>
				<?php 
				if(!empty($DisplayProductInfo)){?>
					<td><?php echo $item->PackingStyle ?></td>
					<td><?php echo $item->Grade ?></td>
					<td><?php echo $item->ProductDescription ?></td>
				<?php }?>
				<td><?php echo $item->OnBoardDate ?></td>
				<td><?php echo $item->ShippingBillNo ?></td>
				<td><?php echo $item->ShippingBillDate ?></td>
				<td><?php echo $item->ShippingBillExchangeRate ?></td>
				<td><?php echo $item->FDAEntryDate ?></td>
				<td><?php echo $item->FDAEntryNumber ?></td>

				<td class="bodyTotAntiDumpingDuty"><?php

				if ($country === "U.S.A." || $country === "U.S.A" || $country === "USA") {
					echo $item->AntiDumpingDuty;
				}
				?></td>

				<td class="bodyTotCountervailingDuty"><?php

				if ($country === "U.S.A." || $country === "U.S.A" || $country === "USA") {
					echo $item->CountervailingDuty;
				}
				?></td>

				<td class="bodyTotMerchantDuty"><?php

				if ($country === "U.S.A." || $country === "U.S.A" || $country === "USA") {
					echo $item->MerchantDuty;
				}
				?></td>

				<td class="bodyTotHarborDuty"><?php

				if ($country === "U.S.A." || $country === "U.S.A" || $country === "USA") {
					echo $item->HarborDuty;
				}
				?></td>
				<td class="bodyTotReciprocalTariffsFee">
					<?php
					if ($country === "U.S.A." || $country === "U.S.A" || $country === "USA") {
						echo $item->ReciprocalTariffsFee;
					}
					?>
				</td>
				<?php if ($ShowReciprocalTariffPerc) { ?>
					<td class="">
						<?php
						if ($country === "U.S.A." || $country === "U.S.A" || $country === "USA") {
							echo $item->ArrayPaymentValues->ReciprocalTariffsPerc;
						}
						?>
					</td>
				<?php } ?>
				<?php if ($DisplayCFCharges) { ?>
					<td><?php echo $item->ArrayPaymentValues->ClearingAndForwardingCharges ?></td>
				<?php } ?>
				<?php if ($DisplayTariff) { ?>
					<td class="bodyTotalTariff"><?php echo $item->TariffFee ?></td>
				<?php } ?>
				<td class="bodyTotCustomFreight"><?php echo $item->CustomFreight ?></td>
				<td><?php echo $item->BLNo ?></td>
				<td><?php echo $item->BLDate ?></td>
				<td><?php echo $item->Containerno ?></td>
				<td><?php echo $item->LinerSealNo ?></td>
				<td><?php echo $item->CustomSealNo ?></td>
				<td class="bodyTotNetWeightKgs"><?php echo $item->QuantityNetWeightKgs ?></td>
				<td class="bodyTotNetWeightLbs"><?php echo $item->QuantityNetWeightLbs ?></td>
				<td class="bodyTotInvoiceValueUSD"><?php echo $item->InvoiceValueUSD ?></td>
				<td class="bodyTotFOBValueUSD"><?php echo $item->FOBValue ?></td>
				<td><?php echo $item->Freight ?></td>
				<td><?php echo $item->LEODate ?></td>
				<td><?php echo $item->LinerName ?></td>
			</tr>
		<?php } ?>
	</tbody>

	<tfoot>
		<tr>

			<th style="min-width: 60px;" class="sticky etaStatusCol1 bodyTotInv"><b style="font-size: 15px;">Total</b>
			</th>
			<th style="min-width: 160px;">&nbsp;</th>
			<th style="min-width: 120px;">&nbsp;</th>
			<th style="min-width: 220px;">&nbsp;</th>
			<th style="min-width: 120px;">&nbsp;</th>
			<th style="min-width: 120px;">&nbsp;</th>
			<th style="min-width: 200px;">&nbsp;</th>
			<th style="min-width: 80px;">&nbsp;</th>
			<th style="min-width: 200px;">&nbsp;</th>
			<th style="min-width: 400px;">&nbsp;</th>
			<th style="min-width: 400px;">&nbsp;</th>
			<th style="min-width: 150px;">&nbsp;</th>
			<th style="min-width: 150px;">&nbsp;</th>
			<th style="min-width: 150px;">&nbsp;</th>
			<?php 
			if(!empty($DisplayProductInfo)){?>
				<th style="min-width: 150px;">&nbsp;</th>
				<th style="min-width: 100px;">&nbsp;</th>
				<th style="min-width: 200px;">&nbsp;</th>
			<?php }?>
			<th style="min-width: 180px;">&nbsp;</th>
			<th style="min-width: 180px;">&nbsp;</th>
			<th style="min-width: 180px;">&nbsp;</th>
			<th style="min-width: 180px;">&nbsp;</th>
			<th style="min-width: 180px;">&nbsp;</th>
			<th style="min-width: 180px;">&nbsp;</th>
			<th style="min-width: 180px;"><b style="font-size: 15px;"
					class="footTotAntiDumpingDuty"><?php echo $resTotals->TotalAntiDumpingDuty ?></b></th>
			<th style="min-width: 180px;"><b style="font-size: 15px;"
					class="footTotCountervailingDuty"><?php echo $resTotals->TotalCountervailingDuty ?></b></th>
			<th style="min-width: 180px;"><b style="font-size: 15px;"
					class="footTotMerchantDuty"><?php echo $resTotals->TotalMerchantDuty ?></b></th>
			<th style="min-width: 180px;"><b style="font-size: 15px;"
					class="footTotHarborDuty"><?php echo $resTotals->TotalHarborDuty ?></b></th>
			<th style="min-width: 180px;"><b style="font-size: 15px;"
					class="footTotReciprocalTariffsFee"><?php echo $resTotals->TotalReciprocalTariffsFee ?></b></th>
			<?php if ($ShowReciprocalTariffPerc) { ?>
				<th style="min-width: 180px;">&nbsp;</th>
			<?php } ?>

			<?php if ($DisplayCFCharges) { ?>
				<th style="min-width: 180px;">&nbsp;</th>
			<?php } ?>
			<?php if ($DisplayTariff) { ?>
				<th style="min-width: 180px;"><b style="font-size: 15px;"
						class="footTotTariff"><?php echo $resTotals->TotalTariffFee ?></b></th>
			<?php } ?>
			<th style="min-width: 180px;">&nbsp;</th>

			<th style="min-width: 200px;">&nbsp;</th>
			<th style="min-width: 120px;">&nbsp;</th>
			<th style="min-width: 150px;">&nbsp;</th>
			<th style="min-width: 150px;">&nbsp;</th>
			<th style="min-width: 150px;">&nbsp;</th>
			<th style="min-width: 200px;"><b style="font-size: 15px;"
					class="footTotNetWeightKgs"><?php echo $resTotals->TotalNetWeightKgs ?></b></th>
			<th style="min-width: 200px;"><b style="font-size: 15px;"
					class="footTotNetWeightLbs"><?php echo $resTotals->TotalNetWeightLbs ?></b></th>
			<th style="min-width: 180px;"><b style="font-size: 15px;"
					class="footTotInvoiceValueUSD"><?php echo $resTotals->TotalInvoiceValueUSD ?></b></th>
			<th style="min-width: 180px;"><b style="font-size: 15px;"
					class="footTotFOBValueUSD"><?php echo $resTotals->TotalFOBValueUSD ?></b></th>
			<th style="min-width: 180px;">&nbsp;</th>
			<th style="min-width: 180px;">&nbsp;</th>
			<th style="min-width: 180px;">&nbsp;</th>
		</tr>
	</tfoot>

</table>
<?php
/*
session_start();

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

	$Header='ORDER`S LIST REPORT';


$Year=$_POST['Year']; 
	$Exporter=$_POST['Exporter']; 
	$Consignee=$_POST['Consignee']; 
	$Country=$_POST['Country']; 


	$DateFilter=getFinancialYear($Year);

$Query='';
if($Exporter!=''){
$Query.="and SPD.ExporterID in ($Exporter)";
}

if($Consignee!=''){
$Query.="and SPD.ConsigneeID in ($Consignee)";
}
if($Country!=''){
$Query.="and SPD.DestinationCountry in ($Country)";
}


$file_name='Orders List -'.$from_date.'-'.$to_date.'.xlsx';
$date_end=$to_date.' 23:59:59'; 

// $sql="SELECT GROUP_CONCAT(PoID) as PoID  FROM ScrmPoData as SPD 
// 					left join ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID 
// 					left join ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id 
// 					left join ScrmShipmentStatusCodes as SSC on SPD.Status=SSC.Code
// 					left join ScrmShipmentDetailsBreif as SDB on SDB.PiID=SPD.PoID
// 					WHERE AvailableDate!='' and (SDB.TransportedDate ='' or SDB.TransportedDate is null) 
// 					$Query order by PoID desc";

// $stmt=$iconn->query($sql); 
// $RowData=$stmt-> fetch_array();
// $PoID=$RowData['PoID'];
// $AryPoID=explode(',',$PoID);
// $OrderCount= Count($AryPoID);




	// $SqlGroup="SELECT Category,ShortName,GROUP_CONCAT(VarietyID) as VarietyID 
		// FROM `ScrmPIVarietyList` GROUP BY Category order by Category";
	// $StmtGroup=$iconn->query($SqlGroup);


	// while($RowData=$StmtGroup-> fetch_array()){

	// $AryData= getTableDate($RowData['VarietyID'],$PoID); 

	// if($AryData['RowCount']>0){
	// //echo '<h3>'.$RowData['Category']. '-'. $RowData['ShortName'] .'</h3>';

	// echo $AryData['TableData'];
	// }
	// }



	//function getTableDate($VarietyID,$PoID){
	global $iconn,$Query;
		$sql="SELECT BuyerName,SCI.PiID,PoNo,PODate,PreparedBy,SCL.name as DestinationCountry,
	 ISF.ContainerNo as ctnnmbr,ConsigneeName,ConsigneeZipCode,PaymentName,SPF.Name as FinalDestination,ShippingTerms,CInvDate
FROM ScrmCustomInvoices as  SCI 
left join ScrmPoData as SPD  on SPD.PoID=SCI.PiID
LEFT JOIN ScrmPoGradeData as SPGD on SPGD.InvId=SPD.PoID 
LEFT JOIN ScrmConsigneeDetails as SCD on SCD.ConID=SPD.ConsigneeID 
LEFT JOIN ScrmPortCities as SPF on SPF.PortId=SPD.FinalDestination 
left join ScrmBuyerDetails as SBD on SPD.BuyerID=SBD.BuyerID
left join ScrmCountriesList  SCL on SCL.id=SPD.DestinationCountry
left join ScrmISFDetails  ISF on ISF.PIID=SCI.PiID
left join ScrmPaymentTerms  SPT on SPT.ID=SPD.PaymentTerms

WHERE  SCI.CInvDate $DateFilter $Query ";


$stmt=$iconn->query($sql);




$data='<table  id="key-datatable" data-paging="false" 
class="table  wrap w-150" >										

<thead class="thead-light">
				   <th style="min-width: 45px;">S No.</th>              
				   <th style="min-width: 120px;">INV #</th>
				   <th style="min-width: 60px;">INV #(1)</th>
				   <th style="min-width: 110px;">PO NO</th>					   				      
				   <th style="min-width: 110px;">PO Date</th>	
				   <th style="min-width: 150px;">BRAND</th>	
				   <th style="min-width: 200px;">Customer Name</th>	
				   <th style="min-width: 180px;">Customer Zip Code</th>	
				   <th style="min-width: 330px;">Production Description</th>	
				   <th style="min-width: 400px;">Payment Terms</th>	
				   <th style="min-width: 100px;">COUNTRY</th>	
				   <th style="min-width: 200px;">Final Destination and State</th>	
				   <th style="min-width: 180px;">Terms of Delivery</th>	
				   <th style="min-width: 180px;">Sale Invoice Date</th>	

				   <th  style="min-width: 150px;">Transaction Type</th>	
				   <th  style="min-width: 150px;">Date of Discounting with Bank</th>	
				   <th  style="min-width: 150px;">Date of Receipt of Payment</th>	
				   <th  style="min-width: 150px;">Date of Receipt of Payment1</th>	
				   <th  style="min-width: 150px;">U.S. ENTRY DATE</th>	
				   <th  style="min-width: 110px;">Raw/Cooked</th>	
				   <th  style="min-width: 120px;">Parts of Body</th>	
				   <th  style="min-width: 150px;">Blk/ Iqf/Semi-Block</th>	
				   <th  style="min-width: 70px;">Species</th>	
				   <th  style="min-width: 70px;">Treatment</th>	
				   <th  style="min-width: 60px;">Glaze %</th>	
				   <th  style="min-width: 60px;">Count</th>	
				   <th  style="min-width: 60px;">Grade</th>	
				   <th  style="min-width: 60px;">M/C</th>	
				   <th  style="min-width: 60px;">S/W</th>	
				   <th  style="min-width: 60px;">K/L/OZ</th>	
				   <th  style="min-width: 60px;">Cont Wt.</th>	
				   <th  style="min-width: 60px;">Master Cartons</th>	
				   <th  style="min-width: 60px;">KG/LB (Gross)</th>	
				   <th  style="min-width: 60px;">Rate</th>	
				   <th  style="min-width: 60px;">Invoice Value($)</th>	
				   <th  style="min-width: 60px;">Exporter</th>	
				   <th  style="min-width: 150px;">Manufacturer / Processor & Packer</th>	
				   <th  style="min-width: 150px;">Importer of Record</th>	

	</thead>
	<tbody>';

$Sno=1;

	while($row= $stmt->fetch_array()){
		$PoID=$row['PiID'];
		$BuyerName=$row['BuyerName'];
		$PODate=getFormatedDatelines($row['PODate']);
		$CInvDate=getFormatedDatelines($row['CInvDate']);
		$PoNo=$row['PoNo'];
		$DestinationCountry=$row['DestinationCountry'];
		$FinalDestination=$row['FinalDestination'];
		$ContainerNo = $row['ctnnmbr'];
		$ConsigneeName = $row['ConsigneeName'];
		$ConsigneeZipCode = $row['ConsigneeZipCode'];
		$PaymentName = $row['PaymentName'];
		$ShippingTerms = $row['ShippingTerms'];


	$CInvNo=getInvoiceNumber($iconn,$ExporterID,$PoID);



$LastUpdatedDate = date('d-m-Y',strtotime($row['LastUpdatedDate']));



//$PoNo=mb_strimwidth($row['PoNo'], 0, 15, "...");
	$SqlVarietyMcs="SELECT Description,Variety,Brand,TypeProduct,SPV.ShortName,SPV.FreezingType,SPV.Species,SPV.Treatment
 ,SPV.GlazePerc ,SPV.Grade
 FROM `ScrmPoGradeData` as SPGD
 left join ScrmPIVarietyList as SPV  on SPV.VarietyID= SPGD.VarietyID
 WHERE InvId='$PoID' GROUP by Variety,PackingStyleID,Brand 
			 Order by SPGD.ID asc"; 
		$StmtVarietyMcs=$iconn->query($SqlVarietyMcs);
		unset($AryDescription);
		unset($AryVariety);
		unset($AryGrade);
		while($VarietyMcsRow=$StmtVarietyMcs->fetch_assoc()){

		$AryDescription[]=$VarietyMcsRow['Description'];
		$AryVariety[]=$VarietyMcsRow['Variety'];
		$AryGrade[]=$VarietyMcsRow['Grade'];
		$Brand=$VarietyMcsRow['Brand'];
		$TypeProduct=$VarietyMcsRow['TypeProduct'];
		$ShortName=$VarietyMcsRow['ShortName'];
		$FreezingType=$VarietyMcsRow['FreezingType'];
		$Species=$VarietyMcsRow['Species'];
		$Treatment=$VarietyMcsRow['Treatment'];
		$GlazePerc=$VarietyMcsRow['GlazePerc'];

		$Variety=implode('<br>',$AryVariety);
		$Grade=implode('<br>',$AryGrade);
		$Description=implode('<br>',$AryDescription);

		}
		if($Description==''){
		$Description=$Variety;
			}
			else{
			$Description; 
			} 
	$data.='<tr> 
	<td>'.$Sno.'</td>		
	<td>'. $CInvNo.'</td> 
	<td> </td> 
	<td>'.$PoNo.'</td>
	<td>'. $PODate.' </td>
	<td>'. $Brand.'</td>
	<td>'.$ConsigneeName.' </td>
	<td>'.$ConsigneeZipCode.'</td>
	<td>'.$Description.'</td>
	<td>'.$PaymentName.'</td>
	<td>'.$DestinationCountry.'</td>
	<td>'.$FinalDestination.' , '.$DestinationCountry.' </td>
	<td>'.$ShippingTerms.'</td>
	<td>'.$CInvDate.'</td>
	<td></td>
	<td> </td>
	<td> </td>
	<td> </td>
	<td> </td>
	<td>'.$TypeProduct.'</td>
	<td>'.$ShortName.'</td>
	<td>'.$FreezingType.'</td>
	<td>'.$Species.'</td>
	<td>'.$Treatment.'</td>
	<td>'.$GlazePerc.'</td>
	<td></td>
	<td>'.$Grade.'</td>
	<td> </td>
	<td> </td>
	<td> </td>
	<td> </td>
	<td> </td>
	<td> </td>
	<td> </td>
	<td> </td>
	<td> </td>
	<td> </td>
	<td> </td>
	';


$Sno++; 
}
	$data .='</tbody></table>';
$AryData['TableData']=$data;
//$AryData['RowCount']=$Rcount;


	echo $data;


	//}


*/
?>
