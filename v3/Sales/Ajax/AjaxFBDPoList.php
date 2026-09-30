<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");


$uid = $_SESSION['r_id'];
$D_ID = $_SESSION["D_ID"];
$ClientID = $_SESSION['ClientID'];

$AccessType = $_SESSION["ACCESS_TYPE"];
$YEAR = $_POST['YEAR'];
$EXPORTER = $_POST['EXPORTER'];
$TYPE = $_POST['TYPE'];
$LinerID = $_POST['LinerID'];
$processor = $_POST['processor'];
$ConsigneeID = $_POST['ConsigneeID'];
$ETAToDate = $_POST['ETAToDate'];
$ETAFromDate = $_POST['ETAFromDate'];




$_SESSION["SelectedYear"] = $_POST['YEAR'];
//$DateFilter = getFinancialYear($YEAR);

$Query = '';

if ($EXPORTER != "") {
	$Query = ' and POD.ExporterID in (' . $EXPORTER . ')';
}
if ($processor != "") {
	$Query .= ' and POD.ProcessorID in (' . $processor . ')';
}
if ($ConsigneeID != "") {
	$Query .= ' and POD.ConsigneeID in (' . $ConsigneeID . ')';
}
if ($LinerID != "") {
	$Query .= ' and SLM.LinerID in (' . $LinerID . ')';
}
if ($ETAFromDate != "" && $ETAToDate != "") {
	$DateFilter .= " and EstimatedETA BETWEEN '$ETAFromDate' and '$ETAToDate'";
}



function getEtaFDDate($PoID)
{
	global $iconn, $ClientID;
	$PortField = ($ClientID === 'SMEV3') ? 'SPD.FinalDestination' : 'SPD.PortOfDischarge';
	$sql = "SELECT $PortField,TranshipDt FROM `ScrmPoData` as SPD 
	left join ScrmPortCities as SPC on SPC.PortId=$PortField  
	left join ScrmTranshipmentPortsList  as STL  on STL.ToTOPID=SPC.Name
	WHERE  SPD.PoID='$PoID' and STL.PIID='$PoID'";

	$stmt = $iconn->query($sql);
	$row = $stmt->fetch_array();
	$TranshipDt = $row['TranshipDt'];
	$oldIsfData = legacyAuditFetchRowById('ScrmISFDetails', 'PIID', $PoID, 'Sales');
	$sql = "update  `ScrmISFDetails` set ETA_FD='$TranshipDt' WHERE PIID='$PoID'";
	$stmt = $iconn->query($sql);
	if ($stmt) {
		legacyAuditLogUpdateById('ScrmISFDetails', 'PIID', $PoID, $oldIsfData, [], ['Remarks' => 'ETA FD updated from FBD PO list'], 'Sales');
	}
	return $TranshipDt;

}
function getVariety($PoID)
{
	global $iconn;
	$sql = "SELECT GROUP_CONCAT(SPVL.Grade,'-',ProductDescription) as Variety, GROUP_CONCAT(Brand) as brand, GROUP_CONCAT(Mcs) as Mcs FROM  `ScrmPoGradeData` as SPGD 
		left join `ScrmPIVarietyList`  as  SPVL on  SPVL.VarietyID=SPGD.VarietyID WHERE InvId='$PoID'";

	$stmt = $iconn->query($sql);
	$row = $stmt->fetch_array();
	$Variety = $row['Variety'];
	$Variety = str_replace(',', ',<br>', $Variety);

	$Brand = str_replace(',', ',<br>', $row['brand']);
	$Mcs = str_replace(',', ',<br>', $row['Mcs']);
	return $Variety . '$' . $Brand . '$' . $Mcs;

}

$sql = "SELECT CustomsInvNo,SCI.CInvDate,SCI.ContainerNo as ctnnmbr,POD.PoNo,SCD.ConsigneeCode,POD.PoID as PoID,
 ISF.GetOutFull as GetOutFullDt,ISF.EmptyReturn as EmptyReturnDt,FDAEntryNumber	as EntryNumber,IsFDA,GetOutFull,
 SCI.PiID, COALESCE(DATEDIFF(EstimatedETA,BLDate),0) as TransitDays,
 COALESCE(DATEDIFF(ETA_FD,ShippingOnboardDt),0) as TransitDaysperLiner,POD.DeliveryTerms,
BLDate,EstimatedETA,LinerName,ContainerTrackingUrl,ETA_FD,ETARemarks,BLNo,ConsigneeName,SPC.Name as FinalDestination
 from  ScrmCustomInvoices  SCI 
left join ScrmPoData  POD on POD.PoID=SCI.PiID
left join ScrmPortCities  SPC on SPC.PortId=POD.FinalDestination
left join ScrmISFDetails  ISF on ISF.PIID=SCI.PiID
left join ScrmConsigneeDetails as SCD on POD.ConsigneeID=SCD.ConID
left join ScrmLinerMaster  SLM on SLM.LinerID=ISF.ShippingLineID
LEFT JOIN ScrmShipmentDetailsBreif as SDB on SDB.PiID=SCI.PiID
WHERE SDB.TransportedDate!='' 
and (SDB.ClosedDate ='' or SDB.ClosedDate is null)and 
(ISF.EmptyReturn='' or ISF.EmptyReturn is null) 
 $Query $DateFilter  and POD.Status not in(4) order by ISF.EstimatedETA asc";

$stmt = $iconn->query($sql);
//ECHO $sql; EXIT;
$AryWholeData = array();
$ArySortedData = array();
while ($row = $stmt->fetch_array()) {
	$AryWholeData[] = $row;
	if ($row['EstimatedETA'] != '') {
		$ArySortedData[] = $row;
	}

}

foreach ($AryWholeData as $row) {
	if ($row['EstimatedETA'] == '') {
		$ArySortedData[] = $row;
	}
}

//echo $sql; exit;

$data = '	<table  id="key-datatable" data-paging="false" 
class="table nowrap w-100" >										
		
<thead class="thead-light">

      <tr>
	  <th style="min-width: 45px;">S.No</th>
	  <th class="sticky etaStatusCol1" style="min-width: 45px;">ID</th>
	  <th class="sticky etaStatusCol2">Inv No</th>
	  <!--<th class="sticky etaStatusCol3">Inv Date</th>-->
	  <th style="Width : 10%">Buyer</th>
	  <th>PO.NO</th>
	  <th>PRODUCT</th>
	   <th>BRAND</th>
	   <th>Mcs</th>
	  <th>BL No</th>
	  
	  <th>Container No</th>
	  <th>Final Destination</th>
	  <th>On Board Date</th>
	  <th>ActualETA</th> 
	  <th>ETA FD</th>
	  <th>Gate Out Date</th>
	  <th>Entry Number</th>
	  <th>Liner</th>	  
	  <th>Delivery Terms</th>	  
	  <th>Transit Days</th>
	  <th>Transit Days Per Liner</th>	 	
	<!--  <th>Empty CY Date</th>	-->		  
	  <th>Is FDA</th>
	  		<!-- <th>Remarks</th>	-->
	  <th>Actions</th>
	  </tr>

    </thead>
	<tbody>';

$i = 0;
foreach ($ArySortedData as $row) {
	$ContainerNo = $row['ctnnmbr'];
	$Port = $row['FinalDestination'];


	$CInvDate = getFormatedDatelines($row['CInvDate']);
	$BLDate = getFormatedDatelines($row['BLDate']);
	$GetOutFull = getFormatedDatelines($row['GetOutFull']);
	$ContainerGateOutDate = $row['ContainerGateOutDate'];
	$EmptyContainerReturnDate = $row['EmptyContainerReturnDate'];
	$IsFDA = $row['IsFDA'];
	$DeliveryTerms = $row['DeliveryTerms'];

	$PoID = $row['PiID'];
	$ExporterID = $row['ExporterID'];
	$CInvNo = $row['CustomsInvNo'];

	$ContainerTrackingUrl = $row['ContainerTrackingUrl'];
	$LinerName = $row['LinerName'];



	$EstimatedETA = $row['EstimatedETA'];
	// $EstimatedETA= getEtaFDDate($row['PoID']);
	$ActualETA = getFormatedDatelines($row['ETA_FD']);
	$ActualETA = getFormatedDatelines(getEtaFDDate($row['PoID']));

	if ($EstimatedETA == '') {
		$EstimatedETA = getEtaFDDate($row['PoID']);
	}

	$ETARemarks = $row['ETARemarks'];
	$BLNo = $row['BLNo'];
	$TransitDays = $row['TransitDays'];
	$GetOutFullDt = $row['GetOutFullDt'];
	$EmptyReturnDt = $row['EmptyReturnDt'];
	$EntryNumber = $row['EntryNumber'];
	$TransitDaysperLiner = $row['TransitDaysperLiner'];
	$PoNo = $row['PoNo'];
	$Variety = getVariety($row['PiID']);
	$Variety_Brand = explode("$", $Variety);


	$ConsigneeName = $row['ConsigneeName'];
	//$ConsigneeName=$row['ConsigneeCode']? $row['ConsigneeCode'] : $row['ConsigneeName'];

	$data .= '<tr> 
	<td id="' . $row['PiID'] . '" class="sticky etaStatusCol1"> ' . ++$i . '</td>
	<td id="' . $row['PiID'] . '" class="sticky etaStatusCol1"> ' . $row['PiID'] . '</td>
	<td id="' . $row['PiID'] . '" class="sticky etaStatusCol2"> ' . $CInvNo . '</td>
	<!--<td id="' . $row['PiID'] . '" class="sticky etaStatusCol3"> ' . $CInvDate . '</td>-->
	<td id="' . $row['PiID'] . '" > ' . $ConsigneeName . '</td>
	<td>' . $PoNo . '</td>
	<td>' . $Variety_Brand[0] . '</td>
	<td>' . $Variety_Brand[1] . '</td>
	<td>' . $Variety_Brand[2] . '</td>
	<td id="' . $row['PiID'] . '" > ' . $BLNo . '</td>	
	<td id="' . $row['PiID'] . '" > ' . $ContainerNo . '</td>
	<td id="' . $row['PiID'] . '" > ' . $Port . '</td>
	<td id="' . $row['PiID'] . '" > ' . $BLDate . '</td>
	<td id="' . $row['PiID'] . '" > ' . $ActualETA . '</td>
	<td> <input  type="date" onkeydown="return false" class="form-control"  name="EstimatedETA" id="EstimatedETA' . $row['PiID'] . '"
				  placeholder="" value="' . $EstimatedETA . '"><span style="display: none">' . $EstimatedETA . '</span></td>
	<td>' . $GetOutFull . '</td>	
	<td id="' . $row['PiID'] . '" > ' . $EntryNumber . '</td>		  
	<td id="' . $row['PiID'] . '" > ' . $LinerName . '</td>	
	<td id="' . $row['PiID'] . '" > ' . $DeliveryTerms . '</td>	
	<td id="' . $row['PiID'] . '" > ' . $TransitDays . '</td>
	<td id="' . $row['PiID'] . '" > ' . $TransitDaysperLiner . '</td>
	
	
				  <!--	<td><input  type="date"  onkeydown="return false" class="form-control"  name="ContainerGateOutDate" id="ContainerGateOutDate' . $row['PiID'] . '" placeholder="" value="' . $GetOutFullDt . '"> </td>		
	<td><input  type="date" onkeydown="return false" class="form-control"  name="EmptyContainerReturnDate" id="EmptyContainerReturnDate' . $row['PiID'] . '"
				  placeholder="" value="' . $EmptyReturnDt . '"> </td>	-->
	
	
	<td class="tdCheckList text-center">
		<div class="p-2">
			<input type="checkbox"';
	$checked = ($IsFDA == "1") ? 'checked' : '';
	$data .= 'class="rowCheck big-checkbox" ' . $checked . ' name="IsFDA" id="IsFDA' . $row['PiID'] . '" value=""  />
		</div>
	</td>

	<!--<td><input  type="text" class="form-control"  name="EntryNumber" id="EntryNumber' . $row['PiID'] . '"
				  placeholder="" value="' . $EntryNumber . '"> </td>	-->
	
	<!--<td><input  type="text" class="form-control"  name="ETARemarks" id="ETARemarks' . $row['PiID'] . '"
				  placeholder="" value="' . $ETARemarks . '"> </td>	-->	 				
	<td>	   <button class="btn btn-link text-primary BtnEditETA" data-id="' . $row['PiID'] . '" 			
							data-toggle="tooltip" data-placement="top" title="Edit" value=""
							data-original-title="Delete"><i class="mdi mdi-pencil font-size-18"></i></button> 						
			
	
		
	<!--	<a target="_blank" href="DocAddEIRReport.php?id=' . $row['PiID'] . '" title="Add Export Information"><i class="mdi mdi-plus font-size-18"></i></a>  -->

						 
                   <!--<input type="hidden"  id="ContainerNo" value="' . $ContainerNo . '">-->
                   <input type="hidden"  id="ContainerNo' . $row['PiID'] . '" value="' . $ContainerNo . '">

				<!--<button target="_blank" id="BtnTrack" value="' . $row['ContainerTrackingUrl'] . '" type="button" class="btn btn-success btn-sm btn-flat">Track</button>-->
				
				<button  target="_blank" data-id="' . $row['PiID'] . '" id="BtnTrack" value="' . $row['ContainerTrackingUrl'] . '" type="button" class="btn btn-success btn-sm btn-flat">Track</button>
				
					 </td></tr>
					';


}
// $i++;
echo $data .= '</tbody></table>';




?>