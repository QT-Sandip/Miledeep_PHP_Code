<?php
include("../RestAPI.php");


$FromDate = trim($_POST["FromDate"]);
$ToDate = trim($_POST["ToDate"]);
$Exporter = trim($_POST["Exporter"]);
$Processor = trim($_POST["Processor"]);
$Country = trim($_POST["Country"]);
$Consignee = trim($_POST["Consignee"]);

$api = GetPendingNegotiationReportAPI;

$bodyContent = array(
	"FromDate" => $FromDate,
	"ToDate" => $ToDate,
	"Exporter" => $Exporter,
	"Processor" => $Processor,
	"Country" => $Country,
	"Consignee" => $Consignee
);

// var_dump($bodyContent);

$resGetPendingNegotiationReportAPI = CallAPI($api, "GET", "", $bodyContent);

$resList = $resGetPendingNegotiationReportAPI->Data->PendingNegotiationReportData;
$Totals = $resGetPendingNegotiationReportAPI->Data->Totals;
// echo $resGetPendingNegotiationReportAPI->Data->Totals;
// exit;
// var_dump($resGetPendingNegotiationReportAPI->Data->Totals);
// echo json_encode($resGetPendingNegotiationReportAPI, JSON_PRETTY_PRINT);
// exit;

?>
<table id="key-datatable" data-paging="false" class="table wrap w-150">
	<thead class="thead-light">
		<tr>
			<th style="min-width: 60px;" class="sticky sno">S.No.</th>
			<th style="min-width: 140px;" class="sticky invNo">Inv No</th>
			<th style="min-width: 200px;">Buyer Name</th>
			<th style="min-width: 160px;">Final Destination</th>
			<th style="min-width: 350px;">Payment Terms</th>
			<th style="min-width: 100px;">No. of Carton</th>
			<th style="min-width: 160px;">Inv. Value Currency</th>
			<th style="min-width: 120px;">On Board Date</th>
			<th style="min-width: 120px;">ETA Date</th>
			<th style="min-width: 120px;">Delivered Date</th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($resList as $item) { ?>
			<tr>
				<td class="sticky sno"><?php echo $item->Sno ?></td>
				<td class="sticky invNo bodyTotInv"><?php echo $item->InvNo ?></td>
				<td><?php echo str_replace(["&"], "-", $item->BuyerName); //$item->BuyerName 
					?></td>
				<td><?php echo $item->FinalDestination ?></td>
				<td><?php echo $item->PaymentTerms ?></td>
				<td><?php echo $item->NoofCartons ?></td>
				<td class="bodyInvValUSD" uda-currency="<?php echo $item->UnitCurrency ?>"
					uda-totalvalue="<?php echo $item->TotalInvoiceCurrencyValue ?>"
					currency-symbol="<?php echo $item->CurrencySymbol; ?>"><?php echo $item->CurrencySymbol;
					   echo " ";
					   echo $item->TotalInvoiceCurrencyValue ? $item->TotalInvoiceCurrencyValue : 0 ?></td>
				<td><?php echo $item->OnBoardDate ?></td>
				<td><?php echo $item->ETADate ?></td>
				<td><?php echo $item->DeliveredDate ?></td>
			</tr>
		<?php } ?>
	</tbody>

	<tfoot>
		<tr>
			<th class="sticky sno" style="min-width: 60px;"><b style="font-size: 15px;">Total</b></th>
			<th class="sticky invNo" style="min-width: 140px;"><b style="font-size: 15px;"
					class="footTotInv"><?php echo $resGetPendingNegotiationReportAPI->Data->Totals->TotalInvoices ?></b>
			</th>
			<th style="min-width: 400px;">&nbsp;</th>
			<th style="min-width: 160px;">&nbsp;</th>
			<th style="min-width: 400px;">&nbsp;</th>
			<th style="min-width: 120px;text-align:left;">
				<b style="font-size: 15px;">Total Value USD</b>
			</th>
			<th style="min-width: 160px;"><b style="font-size: 15px;" class="footInvValUSD">
					$ <?php echo $Totals->TotalInvoiceValueUSD ?></b>
			</th>
			<!-- <th style="min-width: 120px;text-align:left;">
				&nbsp;<b style="font-size: 15px;">Total Value USD</b><br />&nbsp;
				<b style="font-size: 15px;"> Total Value POUND</b><br />&nbsp;
				<b style="font-size: 15px;"> Total Value EURO</b><br />&nbsp;
				<b style="font-size: 15px;"> Total Value GBP</b><br />&nbsp;
				<b style="font-size: 15px;"> Total Value INR</b><br />&nbsp;
				<b style="font-size: 15px;"> Total Value CAD</b>
			</th>
			<th style="min-width: 160px;"><b style="font-size: 15px;" class="footInvValUSDaaaa">
				<?php echo $Totals->TotalInvoiceValueUSD ?></b><br />
				<?php echo $Totals->TotalInvoiceValuePOUND ?></b><br />
				<?php echo $Totals->TotalInvoiceValueEURO ?></b><br />
				<?php echo $Totals->TotalInvoiceValueGBP ?></b><br />
				<?php echo $Totals->TotalInvoiceValueINR ?></b><br />
				<?php echo $Totals->TotalInvoiceValueCAD ?></b>
			</th> -->
			<th style="min-width: 120px;">&nbsp;</th>
			<th style="min-width: 120px;">&nbsp;</th>
			<th style="min-width: 120px;">&nbsp;</th>
		</tr>
		<?php if ($Totals->TotalInvoiceValueINR > 0) { ?>
			<tr>
				<th class="sticky sno" style="min-width: 60px;"></th>
				<th class="sticky invNo" style="min-width: 140px;"></th>
				<th style="min-width: 400px;">&nbsp;</th>
				<th style="min-width: 160px;">&nbsp;</th>
				<th style="min-width: 400px;">&nbsp;</th>
				<th style="min-width: 120px;text-align:left;">
					<b style="font-size: 15px;">Total Value INR</b>
				</th>
				<th style="min-width: 160px;"><b style="font-size: 15px;" class="footInvValINR">
						₹ <?php echo $Totals->TotalInvoiceValueINR ?></b><br />
				</th>
				<th style="min-width: 120px;">&nbsp;</th>
				<th style="min-width: 120px;">&nbsp;</th>
				<th style="min-width: 120px;">&nbsp;</th>
			</tr>
		<?php } ?>
		<?php if ($Totals->TotalInvoiceValuePOUND > 0) { ?>
			<tr>
				<th class="sticky sno" style="min-width: 60px;"></th>
				<th class="sticky invNo" style="min-width: 140px;"></th>
				<th style="min-width: 400px;">&nbsp;</th>
				<th style="min-width: 160px;">&nbsp;</th>
				<th style="min-width: 400px;">&nbsp;</th>
				<th style="min-width: 120px;text-align:left;">
					<b style="font-size: 15px;">Total Value POUND</b>
				</th>
				<th style="min-width: 160px;"><b style="font-size: 15px;" class="footInvValPOUND">
						£ <?php echo $Totals->TotalInvoiceValuePOUND ?></b><br />
				</th>
				<th style="min-width: 120px;">&nbsp;</th>
				<th style="min-width: 120px;">&nbsp;</th>
				<th style="min-width: 120px;">&nbsp;</th>
			</tr>
		<?php } ?>
		<?php if ($Totals->TotalInvoiceValueEURO > 0) { ?>
			<tr>
				<th class="sticky sno" style="min-width: 60px;"></th>
				<th class="sticky invNo" style="min-width: 140px;"></th>
				<th style="min-width: 400px;">&nbsp;</th>
				<th style="min-width: 160px;">&nbsp;</th>
				<th style="min-width: 400px;">&nbsp;</th>
				<th style="min-width: 120px;text-align:left;">
					<b style="font-size: 15px;">Total Value EURO</b>
				</th>
				<th style="min-width: 160px;"><b style="font-size: 15px;" class="footInvValEURO">
						€ <?php echo $Totals->TotalInvoiceValueEURO ?></b><br />
				</th>
				<th style="min-width: 120px;">&nbsp;</th>
				<th style="min-width: 120px;">&nbsp;</th>
				<th style="min-width: 120px;">&nbsp;</th>
			</tr>
		<?php } ?>
		<?php if ($Totals->TotalInvoiceValueGBP > 0) { ?>
			<tr>
				<th class="sticky sno" style="min-width: 60px;"></th>
				<th class="sticky invNo" style="min-width: 140px;"></th>
				<th style="min-width: 400px;">&nbsp;</th>
				<th style="min-width: 160px;">&nbsp;</th>
				<th style="min-width: 400px;">&nbsp;</th>
				<th style="min-width: 120px;text-align:left;">
					<b style="font-size: 15px;">Total Value GBP</b>
				</th>
				<th style="min-width: 160px;"><b style="font-size: 15px;" class="footInvValGBP">
						£ <?php echo $Totals->TotalInvoiceValueGBP ?></b><br />
				</th>
				<th style="min-width: 120px;">&nbsp;</th>
				<th style="min-width: 120px;">&nbsp;</th>
				<th style="min-width: 120px;">&nbsp;</th>
			</tr>
		<?php } ?>
		<?php if ($Totals->TotalInvoiceValueCAD > 0) { ?>
			<tr>
				<th class="sticky sno" style="min-width: 60px;"></th>
				<th class="sticky invNo" style="min-width: 140px;"></th>
				<th style="min-width: 400px;">&nbsp;</th>
				<th style="min-width: 160px;">&nbsp;</th>
				<th style="min-width: 400px;">&nbsp;</th>
				<th style="min-width: 120px;text-align:left;">
					<b style="font-size: 15px;">Total Value CAD</b>
				</th>
				<th style="min-width: 160px;"><b style="font-size: 15px;" class="footInvValCAD">
						C$ <?php echo $Totals->TotalInvoiceValueCAD ?></b><br />
				</th>
				<th style="min-width: 120px;">&nbsp;</th>
				<th style="min-width: 120px;">&nbsp;</th>
				<th style="min-width: 120px;">&nbsp;</th>
			</tr>
		<?php } ?>
	</tfoot>

</table>
<?php


/*
session_start();

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Util.php");
   $Header='ORDER`S LIST REPORT';



 $Year=$_GET['Year']; 
 $Exporter=$_GET['Exporter']; 


 $DateFilter=getFinancialYear($Year);

$Query='';
if($Exporter!=''){
$Query.="and ExporterID in ($Exporter)";
}



$file_name='Orders List -'.$from_date.'-'.$to_date.'.xlsx';
$date_end=$to_date.' 23:59:59';  

$sql="SELECT GROUP_CONCAT(PoID) as PoID  FROM ScrmPoData as SPD 
					left join ScrmConsigneeDetails as SCD on SPD.ConsigneeID=SCD.ConID 
					left join ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id 
					left join ScrmShipmentStatusCodes as SSC on SPD.Status=SSC.Code
					left join ScrmShipmentDetailsBreif as SDB on SDB.PiID=SPD.PoID
					WHERE AvailableDate!='' and (SDB.TransportedDate ='' or SDB.TransportedDate is null) 
					$Query order by PoID desc";

			$stmt=$iconn->query($sql);					
			$RowData=$stmt-> fetch_array();
			 $PoID=$RowData['PoID'];
			 $AryPoID=explode(',',$PoID);
			 $OrderCount= Count($AryPoID); 




		 $SqlGroup="SELECT Category,ShortName,GROUP_CONCAT(VarietyID) as VarietyID 
		FROM `ScrmPIVarietyList` GROUP BY Category order by Category";
		$StmtGroup=$iconn->query($SqlGroup);


		while($RowData=$StmtGroup-> fetch_array()){

		$AryData= getTableDate($RowData['VarietyID'],$PoID);	

		if($AryData['RowCount']>0){
			//echo '<h3>'.$RowData['Category']. '-'. $RowData['ShortName']  .'</h3>';

			echo $AryData['TableData'];
		}
		}



	function getTableDate($VarietyID,$PoID){
	global $iconn,$Query;
	 $sql="SELECT *,PoID,PoNo,PODate,PreparedBy,Name,ShipByDate,
sum(SPGD.Mcs) as Mcs,GROUP_CONCAT(SPGD.Grade) as Grade FROM ScrmPoData as SPD
LEFT JOIN ScrmPoGradeData as SPGD on SPGD.InvId=SPD.PoID 
LEFT JOIN ScrmConsigneeDetails as SCD on SCD.ConID=SPD.ConsigneeID 
LEFT JOIN ScrmPortCities as SPC on SPC.PortId=SPD.PortOfDischarge 
WHERE   SPGD.VarietyID in ($VarietyID)
and SPD.PoID  in($PoID) 

GROUP by PoID order by PoID Asc"; 


$stmt=$iconn->query($sql);


$data='<table  id="key-datatable" data-paging="false" 
class="table  wrap w-150" >										

<thead class="thead-light">
				   <th  align="center"   >S No.</th>              
				   <th  align="center"  >REPORT ID</th>
				   <th  align="center"  >ORDER NO</th>
				   <th  align="center"  ><B> BUYER NAME</B></th>					   
				  <!-- <th  align="center"  ><B> BUYER AGENT</B></th>					   
				   <th  align="center"  ><B> PLANT</B></th>-->					   
				   <th  align="center"  ><B>BRAND</B></th>	
				   <th  align="center"  ><B>VARIETY</B></th>
				   <th  align="center"  ><B>MASTER CARTONS</B></th>
				   <th  align="center"  ><B>PACK</B></th>			
				   <th  align="center"  ><B>GRADE</B></th>						   
				   <!--<th  align="center"  ><B>PACKAGING READY</B></th>				   
				   <th  align="center"  ><B>CODE ISSUE</B></th>			
				   <th  align="center"  ><B>LAB SAMPLE</B></th>	 		
				   <th  align="center"  ><B>QTY</B></th>			
				   <th  align="center"  ><B>MAJOR GRADE</B></th>	-->			
				   <th  align="center"  ><B>SHIPMENT DATE AS PER PO</B></th>			
				   <th  align="center"  ><B>DESTINATION</B></th>	

	</thead>
	<tbody>';

$Sno=0;

  while($row= $stmt->fetch_array()){




$LastUpdatedDate = date('d-m-Y',strtotime($row['LastUpdatedDate']));




if($row['AvalMcs']==''){
	$AvalMcs='0';
} 
else{
	$AvalMcs=$row['AvalMcs'];
}


$ConsigneeName=mb_strimwidth($row['ConsigneeName'], 0, 20, "...");
$PoNo=mb_strimwidth($row['PoNo'], 0, 15, "...");

$ShipByDate=getFormatedDate($row['ShipByDate']);		
$PoDate=getFormatedDate($row['PoDate']);		
$ConsigneeName=$row['ConsigneeName'];		


$PoID=$row['PoID'];
$BuyerAgent=$row['PoID'];
$Plant=$row['PoID'];
$Brand=$row['Brand'];
$Grade=$row['Grade'];
 $PreparedBy=$row['PreparedBy'];
$Destination=$row['Name'];
$ShipByDate=$row['ShipByDate'];
$Rcount=0;		
$AryPoID[]=$PoID;	
	$data.='<tr> 
	<td> '. ++$Sno.'</td>		
	<td> '. $PoID.'</td> 
	<td> '. $PoNo.'-('.$PoDate.')</td> 
	<td> '. $ConsigneeName.'</td>
	<!--<td> '. $BuyerAgent.'</td>
	<td> '. $Plant.'</td>-->
	<td> '. $Brand.'</td>';


				//  $SqlVariety="SELECT sum(Mcs) as Mcs,ShortName,
				//  GROUP_CONCAT(PackingStyle) as PackingStyle,
				// GROUP_CONCAT(Grade) as Grade				 FROM `ScrmPoGradeData` SPDT lEFT join AdminPackingStyles APS on SPDT.PackingStyleID=APS.PCKID LEFT join ScrmPackingData SPD on SPDT.ID=SPD.POGradeId left Join ScrmPIVarietyList as SVL on 
				// SVL.VarietyID=SPDT.VarietyID WHERE InvId='$PoID' 
				// and SPDT.VarietyID in ($VarietyID) GROUP By SPDT.VarietyID";  

				$SqlVariety="SELECT sum(Mcs) as Mcs,ShortName,
				 GROUP_CONCAT(APS.PackingStyle) as PackingStyle,
				GROUP_CONCAT(SPDT.Grade,'-',SPDT.Mcs) as Grade	FROM `ScrmPoGradeData` SPDT lEFT join AdminPackingStyles APS on SPDT.PackingStyleID=APS.PCKID LEFT join ScrmPackingData SPD on SPDT.ID=SPD.POGradeId left Join ScrmPIVarietyList as SVL on 
				SVL.VarietyID=SPDT.VarietyID WHERE InvId='$PoID' 
				and SPDT.VarietyID in ($VarietyID) GROUP By SPDT.VarietyID";  
			$Stmtvariety=$iconn->query($SqlVariety);
			$RowCount=mysqli_num_rows($Stmtvariety);
			if($RowCount==0){

	$data.='<td ></td>

	<td ></td>
	<td ></td>
	<td ></td>

	</tr>';
			}

			while($RowVariety=$Stmtvariety->fetch_array()){

			if($Rcount==0){

			}else{
				$data .='

				 <td ></td>
				 <td ></td>
				 <td ></td>
				 <td ></td>
				 <td ></td>'; 
			}





				  $data .='
				 <td>'.$RowVariety['ShortName'].'</td>
				 <td>'.$RowVariety['Mcs'].'</td>
				 <td>'.$RowVariety['PackingStyle'].'</td>
				 <td>'.$RowVariety['Grade'].''.$RowVariety['GradeMcs'].'</td>
				 <td>'.getFormatedDate($ShipByDate).'</td>
				';

				 if($Rcount==0){
				$data .='
				 <td >'.$Destination.'</td>'; 
			}else{
				$data .='
				 <td ></td>
				 <td ></td>'; 
			}

				$Rcount++;		 
		$data .='  </tr>';


			}	

}
  $data .='</tbody></table>';
$AryData['TableData']=$data;
$AryData['RowCount']=$Rcount;


	 return $AryData;


	}

*/

?>