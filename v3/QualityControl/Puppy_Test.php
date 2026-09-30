<?php
include "../includes/config.php";

//Consignee  Details
/*
$sql="select * from  SCRM_CONSIGNEE_DETAILS";
//$sql="UPDATE `inputmaterial_details` SET `RM_ANA_STATUS`='0'";
$stmt=$iconn->query($sql);



while($row=$stmt->fetch_array()){
	$ConID=$row['CONSIGNEE_ID']	;
	$ConsigneeName=$row['CONSIGNEE_NAME']	;
	$ConsigneeAddress=$row['CONSIGNEE_ADDRESS']	;
	$ConsigneeCountry=$row['CONSIGNEE_PORT_OF_DISCHARGE']	;
	$ConsigneeFishingNo=$row['FISHING_PMT_NO']	;
	$sql="INSERT INTO `ScrmConsigneeDetails`(`ConID`, `ConsigneeName`, 
	`ConsigneeAddress`, `ConsigneeCountry`, `ConsigneeSplIns`,
	`ConsigneeFishingNo`) VALUES ('$ConID','$ConsigneeName','$ConsigneeAddress','$ConsigneeCountry','NA','$ConsigneeFishingNo')";
$stmtt=$iconn->query($sql);
echo $row['LOT_ID']	;
}

*/

//PoDetails
/*

$sql="SELECT * FROM `INVOICE_DATA`";
//$sql="UPDATE `inputmaterial_details` SET `RM_ANA_STATUS`='0'";
$stmt=$iconn->query($sql);



while($row=$stmt->fetch_array()){
	
	
	
	$PoID=$row['ID']	;
	$PoRefId=$row['ID']	;
	$InvNo=$row['INVOICE_NUMBER']	;
	$InvDate=$row['INVOICE_DATE']	;
	$ExporterID=$row['EXPORTER_ID']	;
	$ProcessorID=$row['PROCESSOR_ID']	;
	$LotNo=$row['CONSIGNEE_ID']	;
	$ConsigneeID=$row['CONSIGNEE_ID']	;
	$SciName=$row['SCIENTIFIC_NAME']	;
	$PoNo=$row['PO_NUM']	;
	$PoDate=$row['PO_DATE']	;
	$DestinationCountry=$row['DESTINATION_COUNTRY']	;
	$TypeCountry=$row['TYPE_OF_COUNTRY']	;
	$PoStatus=$row['PO_STATUS']	;
	$Year=$row['YEAR']	;
	
	$sql="INSERT INTO `ScrmPoData`(`PoID`, `PoRefId`, `InvNo`, `InvDate`,
	`ExporterID`, `ProcessorID`, `LotNo`, `SciName`, `ConsigneeID`, `PoNo`,
	`PoDate`, `DestinationCountry`, `TypeCountry`, `PoStatus`, `Year`) VALUES (
	'$PoID',
	'$PoRefId',
	'$InvNo',
	'$InvDate',
	'$ExporterID',
	'$ProcessorID',
	'$LotNo',
	'$SciName',
	'$ConsigneeID',
	'$PoNo',
	'$PoDate',
	'$DestinationCountry',
	'$TypeCountry',
	'$PoStatus',
	'$Year'
	)";
$stmtt=$iconn->query($sql);
echo $row['LOT_ID']	;
}

*/

/*
$sql="SELECT * FROM `INVOICE_DATA` where CERTIFICATE_NO!='' ";
//$sql="UPDATE `inputmaterial_details` SET `RM_ANA_STATUS`='0'";
$stmt=$iconn->query($sql);



while($row=$stmt->fetch_array()){
	
	
	
	$InvID=$row['ID']	;
	$Date=$row['DO_SAMPLING']	;
	$VerifiedBy=$row['VARIFIED_ID']	;
	$Quantity=$row['QUANTITY_IN_KGS']	;
	$QCertNo=$row['CERTIFICATE_NO']	;
	$TypeProcessing=''	;
	$TypeTransport=$row['MEANS_OF_TRANSPORT']	;
	$TypeTreated=$row['PHOSPHATE']	;
	$PortDischarge=$row['PORT_OF_DIS']	;
	$SealNo=''	;
	$VesselNo=''	;
	$ContainerNo=''	;

	
	$sql="INSERT INTO `QcCoveringLetter`( `InvID`, `Date`, `VerifiedBy`, `Quantity`, 
	`QCertNo`, `TypeProcessing`, `TypeTransport`, `TypeTreated`, `PortDischarge`,
	`SealNo`, `VesselNo`, `ContainerNo`) VALUES (
	'$InvID',
	'$Date',
	'$VerifiedBy',
	'$Quantity',
	'$QCertNo',
	'$TypeProcessing',
	'$TypeTransport',
	'$TypeTreated',
	'$PortDischarge',
	'$SealNo',
	'$VesselNo',
	'$ContainerNo'
	)";
$stmtt=$iconn->query($sql);
echo $row['LOT_ID']	;
}





$sql="SELECT * FROM `PO_VARIETY_LIST`";
//$sql="UPDATE `inputmaterial_details` SET `RM_ANA_STATUS`='0'";
$stmt=$iconn->query($sql);



while($row=$stmt->fetch_array()){
	
	
	
	$VarietyID=$row['ID']	;
	$InvID=$row['INV_ID']	;
	$TypeProduct=$row['TYPE']	;
	$ShortName=$row['SHORT_NAME']	;
	$FullName=$row['FULL_NAME']	;
	
	$sql="INSERT INTO `QcPoVarietyList`(`VarietyID`, `InvID`, `TypeProduct`, `ShortName`, `FullName`)
	VALUES (
	'$VarietyID',
	'$InvID',
	'$TypeProduct',
	'$ShortName',
	'$FullName'
	)";
$stmtt=$iconn->query($sql);
echo $row['LOT_ID']	;
}

*/
$sql="SELECT * FROM `CODE_LIST_CREATE`";
//$sql="UPDATE `inputmaterial_details` SET `RM_ANA_STATUS`='0'";
$stmt=$iconn->query($sql);



while($row=$stmt->fetch_array()){
	
	
	
	$CLID=$row['CODE_LIST_ID']	;
	$InvID=$row['INV_ID']	;
	$VarietyID=$row['FULL_NAME']	;
	$Brand=$row['BRAND']	;
	$McsTotal=$row['T_O_MCS']	;
	$Grades=$row['GRADES']	;
	$McsGrade=$row['MCS']	;
	$GlazePerc=$row['GLAZE_PERC']	;
	$DayCode=$row['DAY_CODE']	;
	$TracebilityID=0	;//
	$VerifiedBy=$row['VERIFIED_BY']	;
	$PackingStyleID=0	;//
	
	$sql="INSERT INTO `QcCodeList`(`CLID`, `InvID`, `VarietyID`, `Brand`, 
	`McsTotal`, `Grades`, `McsGrade`, `GlazePerc`, `DayCode`, 
	`TracebilityID`, `VerifiedBy`, `PackingStyleID`) VALUES (
	'$CLID',
	'$InvID',
	'$VarietyID',
	'$Brand',
	'$McsTotal',
	'$Grades',
	'$McsGrade',
	'$GlazePerc',
	'$DayCode',
	'$TracebilityID',
	'$VerifiedBy',
	'$PackingStyleID'
	)";
$stmtt=$iconn->query($sql);
if($stmtt){
	$QcCodeListInsertId = $iconn->insert_id;
	legacyAuditLogInsertById('QcCodeList', 'CLID', $QcCodeListInsertId, $row, ['Remarks' => 'Code List Migrated'], 'QualityControl');
}
echo $row['LOT_ID']	;
}





?> 
