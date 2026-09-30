

<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");




$PIId=$_GET['PIID'];
$SQL_OPTIONS="SELECT * FROM `OPTIONS` WHERE  DEPT='SALES'  order by SortOrder asc ";
$STMT_OPTIONS=$iconn->query($SQL_OPTIONS);
$ARY_OPTIONS=array();
WHILE($ROW=$STMT_OPTIONS->FETCH_ARRAY()){
	$ARY_OPTIONS[]=$ROW;
}


$CustomInvoiceSql="SELECT *,SPD.ExporterID as ExpId FROM $ScrmCustomInvoices as SCV 
 left JOIN $TblScrmPIData as SPD on SPD.PoID=SCV.PiID WHERE PiID='$PIId'";
$CustomInvoiceExec=$iconn->query($CustomInvoiceSql); 
$CustomInvoiceRow = $CustomInvoiceExec->fetch_assoc();
$ExpID=$CustomInvoiceRow['ExpId'];

$ExporterData=getExporterDetails($ExpID);

$CurrentMonth = date('n',strtotime($CustomInvoiceRow['CInvDate']));   
if($CustomInvoiceRow['ExpId']==5){		
if($CurrentMonth<=3){
	
 
 $NxtYear = date('y',strtotime($CustomInvoiceRow['CInvDate']));  	
 $CurrentYear = date('y',strtotime($CustomInvoiceRow['CInvDate']))-1;  	
	
} 
else{ 

 $CurrentYear = date('y',strtotime($CustomInvoiceRow['CInvDate']));  	 
$NxtYear = date('y',strtotime($CustomInvoiceRow['CInvDate']))+1;  	
 
}
		}
		else{		
if($CurrentMonth<=3){
	
 	
 
 $NxtYear = date('y',strtotime($CustomInvoiceRow['CInvDate']));  	
 $CurrentYear = date('Y',strtotime($CustomInvoiceRow['CInvDate']))-1;  	

	
} 
else{ 
 
$CurrentYear = date('Y',strtotime($CustomInvoiceRow['CInvDate']));  	 
$NxtYear = date('y',strtotime($CustomInvoiceRow['CInvDate']))+1;  	
  
}
		}


 $file_name=$ExporterData['InvShortCode'].'-CI-'.$CustomInvoiceRow['CInvNo'].'-'.$CurrentYear.'-'.$NxtYear;

/**uma 22/08/2022**/

$PageTitleFolder="SalesNew";
$PageTitle="Export Report Data(".$file_name.")";
/**uma 22/08/2022**/
/**lokesh 02/09/2022**/
 
 /****shipment**/
 $SqlShipmentCount="SELECT * FROM `$ScrmShipmentDetails` WHERE PiID='$PIId'";
		$StmtShipmentCount=$iconn->query($SqlShipmentCount);
					$ShipmentCount=mysqli_num_rows($StmtShipmentCount);				
		$RowShipmentDetails=$StmtShipmentCount->fetch_array();


 

$SqlPorts="SELECT * FROM $TblScrmPortCities ";
	$StmtPorts=$iconn->query($SqlPorts);
	
	$SqlPorts1="SELECT * FROM $TblScrmPortCities WHERE Country=72";
	$StmtPorts1=$iconn->query($SqlPorts1);
	
	
	while($RowPo=$StmtPorts->fetch_array()){
		$AryPorts[]=$RowPo;
	}
	while($RowPo1=$StmtPorts1->fetch_array()){
		$AryPorts1[]=$RowPo1;
	}
 

 	

 /****shipment**/

	/**custom**/
	
	$SqlPIVarietyList="SELECT * FROM `$ScrmPIVarietyList`"; 
$StmtPIVarietyList=$iconn->query($SqlPIVarietyList);

	 $SqlBank="SELECT * FROM $TblAdminBankDetails";
	$StmtBank=$iconn->query($SqlBank);
	
	$SqlPI="SELECT ExporterID FROM `$TblScrmPIData`  WHERE PoID='$PIId'"; 
$StmtPI=$iconn->query($SqlPI);
$RowPI=$StmtPI->fetch_array();

$ExpID=$RowPI['ExporterID'];
		
		
$SqlCount="SELECT * FROM `$ScrmCustomInvoices` WHERE PiID='$PIId'";
		$StmtCount=$iconn->query($SqlCount);
					$CICount=mysqli_num_rows($StmtCount);				
		$RowCustomInvoice=$StmtCount->fetch_array();
		$InvNo=$RowCustomInvoice['CInvNo'];
		
		 $SqlUserDetails="SELECT * FROM `$TblAdminUserDetails` WHERE CustomUserID='1' and DepartmentID=9";
		$StmtUserDetails=$iconn->query($SqlUserDetails);

$SqlGradeData="SELECT * FROM `$ScrmCIGradeData` where InvId='$PIId'  ORDER BY ID DESC"; 
$StmtGradeData=$iconn->query($SqlGradeData); 
$CIGradeCount=mysqli_num_rows($StmtGradeData);

	/**custom**/
	
	/***Bill Of Exchange***/
	 $SqlCInvoice="SELECT ConsigneeName,ConsigneeAddress ,LcStatus
		FROM `$TblScrmPIData` as SPD
left join $TblScrmPaymentTerms as SPT	on SPT.ID=SPD.PaymentTerms	
left join $TblScrmConsigneeDetails as SCD on SCD.ConID=SPD.BuyerID	
		
		WHERE PoID='$PIId'"; 
		$StmtCInvoice=$iconn->query($SqlCInvoice);
		$RowCInvoice=$StmtCInvoice->fetch_array();
		
				$ConsigneeAddress=$RowCInvoice['ConsigneeAddress'];
						 $Status=$RowCInvoice['LcStatus']; 


		
		$SqlBillCount="SELECT * FROM `$ScrmBillOfExchange` WHERE PIID='$PIId'";
		$StmtBillCount=$iconn->query($SqlBillCount);
		$BillCount=mysqli_num_rows($StmtBillCount);				
		$RowBillofExchage=$StmtBillCount->fetch_array();
		 $ToAddress=$RowBillofExchage['ToAddress'];
		 if($ToAddress==''){
		
				$ToAddress=$RowCInvoice['ConsigneeName'].$RowCInvoice['ConsigneeAddress'];
		}

	/***Bill Of Exchange***/
	
	/***tenplustwo**/
	$SqlLogistics="SELECT * FROM `$ScrmLogisticsMaster`";
		$StmtLogistics=$iconn->query($SqlLogistics);
		
		$SqlLiner="SELECT * FROM `$ScrmLinerMaster` WHERE Status=1";
$StmtLiner=$iconn->query($SqlLiner);

		
$SqlLogisticsCount="SELECT * FROM `$ScrmLogisticsDetails` WHERE PiID='$PIId'";
		$StmtLogisticsCount=$iconn->query($SqlLogisticsCount);
					$LogisticsCount=mysqli_num_rows($StmtLogisticsCount);				
		$RowTenPlusTwo=$StmtLogisticsCount->fetch_array();


	/***tenplustwo**/
	
	/***cargodispatch**/
	$SqlShipmentDispatchCount="SELECT * FROM `$ScrmShipmentDispatchDetails` WHERE PIID='$PIId'";
		$StmtShipmentDispatchCount=$iconn->query($SqlShipmentDispatchCount);
					$ShipmentDispatchCount=mysqli_num_rows($StmtShipmentDispatchCount);				
		$RowShipmentDispatch=$StmtShipmentDispatchCount->fetch_array();

	/***cargodispatch**/
	
	
	
/**lokesh 02/09/2022**/



		
extract($_POST);
extract($_GET);

if(isset($_POST['submit'])){
	
	/***custom Start***/
	
	 if($CICount==0){
			 
			  $StartYear=date('Y',strtotime($CInvDate));
			  $EndYear=$StartYear+1;
			  $FinStartDate=$StartYear.'-04-01';
			  $FinEndDate=$EndYear.'-03-31';
			 
			 	if($ExpID==5){

			 $SqlInvCount="SELECT * FROM `$ScrmCustomInvoices`
			 WHERE CInvDate BETWEEN '$FinStartDate' and '$FinEndDate' and ExporterID='$ExpID' and CInvNo='$CInvNo'";
 
				}
				else{
				$SqlInvCount="SELECT * FROM `$ScrmCustomInvoices`
			 WHERE CInvDate BETWEEN '$FinStartDate' and '$FinEndDate' and ExporterID BETWEEN '1' and '3' and CInvNo='$CInvNo'";
 
				}
				
				
				$StmtInvCount=$iconn->query($SqlInvCount);
			 $InvCount=mysqli_num_rows($StmtInvCount);		    
			 
			 if($InvCount==0){

			 
        $SqlCIInsert="INSERT INTO `$ScrmCustomInvoices`
		(`PiID`, `CInvNo`, `CInvDate`,`CInvCode`, `IGST`, `Discount`, 
		`BankID`, `NoOfDays`,`ExporterID`,`AdvanceAmount`,
		`OverSeasCommission`,`Description`, `CIDescription`, `NameofRepresentative`, `PackDescription`)
	VALUES ('$PIId','$CInvNo','$CInvDate','$CInvCode','$IGST','$Discount','$BankID','$NoOfDays','$ExpID',
	'$AdvanceAmount','$OverSeasCommission','$Description','$CIDescription','$NameofRepresentative','$PackDescription')"; 
		
		
			$StmtCIInsert=$iconn->query($SqlCIInsert);
			 
			
	if($StmtCIInsert){
		$cilastId = $iconn->insert_id;
		legacyAuditLogInsertById($ScrmCustomInvoices, 'CInvID', $cilastId, $_POST, array(), 'Sales');
	if($CIGradeCount==0){
		
		$SqlGradeData="SELECT * FROM `$TblScrmPoGradeData` where InvId='$PIId'  ORDER BY ID DESC"; 
$StmtGradeData=$iconn->query($SqlGradeData); 
   while($RowGradeData=$StmtGradeData->fetch_array()){

	
	$PoGrade=$RowGradeData['Grade'];
	$PoMcs=$RowGradeData['Mcs'];
	$PoFreezingType=$RowGradeData['FreezingType'];
	$PoPackingStyleID=$RowGradeData['PackingStyleID'];
	$PoGlazeType=$RowGradeData['GlazeType'];
	$PoVariety=$RowGradeData['Variety'];
	$PoGlazePerc=$RowGradeData['GlazePerc'];
	$PoTreatment=$RowGradeData['Treatment'];
	$PoBrand=$RowGradeData['Brand'];
	$PoPrice=$RowGradeData['Price'];
	$PoBAP=$RowGradeData['BAP'];
	$PoVarietyID=$RowGradeData['VarietyID'];
		
		
		
		
		$SqlGradeInsert="INSERT INTO `$ScrmCIGradeData`(`Grade`, `Mcs`, `FreezingType`, `InvId`, `PackingStyleID`,
	 `GlazeType`, `Variety`, `GlazePerc`, `Treatment`, `Brand`, `Price`,`BAP`,`VarietyID`) VALUES (
	 '$PoGrade','$PoMcs','$PoFreezingType','$PIId','$PoPackingStyleID','$PoGlazeType','$PoVariety','$PoGlazePerc',
	 '$PoTreatment','$PoBrand','$PoPrice','$PoBAP','$PoVarietyID')"; 
		$StmtGradeInsert=$iconn->query($SqlGradeInsert);
		if($StmtGradeInsert){
			$gradeLastId = $iconn->insert_id;
			legacyAuditLogInsertById($ScrmCIGradeData, 'ID', $gradeLastId, $_POST, array('PIID' => $PIId), 'Sales');
		}
		
	}
	}
	
	
	}	 
			 
			 }	 
		else{
			echo '<script language="javascript" type="text/javascript"> 
                alert("'.$CInvNo.'Invoice Number Already Exists");
                window.location = "DocAddDetails.php?PIID='.$PIId.'";
        </script>';
		}	 
			 
			 
			 }
			 
			 
			 
			 
			 else{
				 if($InvNo==$CInvNo){
					 //echo"hi";exit;
		$oldCIData = legacyAuditFetchRowById('ScrmCustomInvoices', 'PiID', $PIId, 'Sales');
		$SqlCIInsert= "UPDATE `ScrmCustomInvoices` SET
		`CInvNo`='$CInvNo',
        `CInvDate`='$CInvDate',
		`CInvCode`='$CInvCode',
		`IGST`='$IGST',
		`Discount`='$Discount',
         `BankID`='$BankID',
		 `NoOfDays`='$NoOfDays',
		 `AdvanceAmount`='$AdvanceAmount',
		 `OverSeasCommission`='$OverSeasCommission',
		 `Description`='$Description',
		 `CIDescription`='$CIDescription',
		 `NameofRepresentative`='$NameofRepresentative',
		 `PackDescription`='$PackDescription'
		 WHERE `PiID`='$PIId'"; 
			


			$StmtCIInsert=$iconn->query($SqlCIInsert);
			if($StmtCIInsert){
				legacyAuditLogUpdateById('ScrmCustomInvoices', 'PiID', $PIId, $oldCIData, $_POST, array(), 'Sales');
			}
	
		 
			 }
	else{
		
	$StartYear=date('Y',strtotime($CInvDate));
	 $EndYear=$StartYear+1; 
			  $FinStartDate=$StartYear.'-04-01';
			  
             $FinEndDate=$EndYear.'-03-31';
			 
			 	if($ExpID==5){

			 $SqlInvCount="SELECT * FROM `$ScrmCustomInvoices`
			 WHERE CInvDate BETWEEN '$FinStartDate' and '$FinEndDate' and ExporterID='$ExpID' and CInvNo='$CInvNo'";
 
				}
				else{
				$SqlInvCount="SELECT * FROM `$ScrmCustomInvoices`
			 WHERE CInvDate BETWEEN '$FinStartDate' and '$FinEndDate' and ExporterID BETWEEN '1' and '3' and CInvNo='$CInvNo'";
 
				}
				//echo $SqlInvCount; exit;
				
				$StmtInvCount=$iconn->query($SqlInvCount);
			 $InvCount=mysqli_num_rows($StmtInvCount);		    
			 
			 if($InvCount!=0){
	 echo '<script language="javascript" type="text/javascript"> 
	 alert("Update Failed  Invoice Number already exists");
	                 window.location = "DocAddDetails.php?PIID='.$PIId.'";
</script>';
			 }
		else{	 
		$oldCIData = legacyAuditFetchRowById('ScrmCustomInvoices', 'PiID', $PIId, 'Sales');
       $SqlCIInsert= "UPDATE `ScrmCustomInvoices` SET
		`CInvNo`='$CInvNo',
        `CInvDate`='$CInvDate',
		`CInvCode`='$CInvCode',
		`IGST`='$IGST',
		`Discount`='$Discount',
         `BankID`='$BankID',
		 `NoOfDays`='$NoOfDays',
		 `AdvanceAmount`='$AdvanceAmount',
		 `OverSeasCommission`='$OverSeasCommission',
		 `Description`='$Description',
		 `CIDescription`='$CIDescription',
		 `NameofRepresentative`='$NameofRepresentative',
		 `PackDescription`='$PackDescription'
		 WHERE `PiID`='$PIId'"; 
			
		
			$StmtCIInsert=$iconn->query($SqlCIInsert);
			if($StmtCIInsert){
				legacyAuditLogUpdateById('ScrmCustomInvoices', 'PiID', $PIId, $oldCIData, $_POST, array(), 'Sales');
			}
	
		
	}
	
	
	/*if($StmtCIInsert&&$CInvDate!=''){
		$ShippedPO="UPDATE `$TblScrmPoData` SET `Status`='6'	WHERE PoID='$PIId'";
		
		$stmtShippedPO=$iconn->query($ShippedPO);
		
		if($stmtShippedPO){
			
	$SqlShippedPI="UPDATE `$TblScrmPIData` SET `Status`='6'	WHERE PoID='$PIId'";
	
    $StmtShippedPI=$iconn->query($SqlShippedPI);
		
		}
	}
	*/		/*echo '<script language="javascript" type="text/javascript"> 
		                window.location = "DocAddReports.php?PoID='.$PIId.'";
        </script>';*/

		}	 
			
			 }
	if($StmtCIInsert&&$CInvDate!=''){
		$oldPoDataStatus = legacyAuditFetchRowById($TblScrmPoData, 'PoID', $PIId, 'Sales');
		$ShippedPO="UPDATE `$TblScrmPoData` SET `Status`='6'	WHERE PoID='$PIId'";
		
		$stmtShippedPO=$iconn->query($ShippedPO);
		if($stmtShippedPO){
			legacyAuditLogUpdateById($TblScrmPoData, 'PoID', $PIId, $oldPoDataStatus, array('Status' => '6'), array(), 'Sales');
		}
		
		if($stmtShippedPO){
	$oldPiDataStatus = legacyAuditFetchRowById($TblScrmPIData, 'PoID', $PIId, 'Sales');		
	$SqlShippedPI="UPDATE `$TblScrmPIData` SET `Status`='6'	WHERE PoID='$PIId'";
	
    $StmtShippedPI=$iconn->query($SqlShippedPI);
	if($StmtShippedPI){
		legacyAuditLogUpdateById($TblScrmPIData, 'PoID', $PIId, $oldPiDataStatus, array('Status' => '6'), array(), 'Sales');
	}
		
		}
	}
		
	
	
	
	
	/***custom End***/
	
	/****shipment start**/
	 if($ShipmentCount==0){
			 
			 
			 
       $SqlShipmentInsert="INSERT INTO `$ScrmShipmentDetails`(`PiID`, `ContainerNo`, 
		`SealNo`, `PackSealNo`, `PlaceOfRecipt`, `FinalDestination`, `FrieghtCharge`, `ExchangeRate`, 
		`VehicleNumber`, `EWayBillNo`, `PortOfLoading`, `PortofDischarge`)
	VALUES ('$PIId','$ContainerNo','$SealNo','$PackSealNo',
	'$PlaceOfRecipt','$FinalDestination','$FrieghtCharge','$ExchangeRate','$VehicleNumber',
	'$EWayBillNo','$PortOfLoading','$PortofDischarge')";
		
			 }
			 
			 
			 
			 
			 else{
			$oldShipmentData = legacyAuditFetchRowById($ScrmShipmentDetails, 'PiID', $PIId, 'Sales');
			$SqlShipmentInsert= "UPDATE `$ScrmShipmentDetails` SET `ContainerNo`='$ContainerNo',
`SealNo`='$SealNo',`PackSealNo`='$PackSealNo',`PlaceOfRecipt`='$PlaceOfRecipt',
`FinalDestination`='$FinalDestination',`FrieghtCharge`='$FrieghtCharge',`ExchangeRate`='$ExchangeRate',
`VehicleNumber`='$VehicleNumber',`EWayBillNo`='$EWayBillNo',`PortOfLoading`='$PortOfLoading',
`PortofDischarge`='$PortofDischarge' WHERE `PiID`='$PIId'";
			

			
	
	
			 
			 }
	
	
	/****shipment End**/
	
	
	/****Bill of Exchange Start**/
	
	if($BillCount==0){
        $SqlBillInsert="INSERT INTO `$ScrmBillOfExchange`(`PIID`,`CreditNo`,`CreditDate`,`DraftDate`,`ExchangeInvoicevalue`,`IssuedBy`,`ToAddress`,`Status`) 
		VALUES  ('$PIId','$CreditNo','$CreditDate','$DraftDate','$ExchangeInvoicevalue','$IssuedBy','$ToAddress','$Status')"; 
		
			 
			 }
			 else{
			$oldBillData = legacyAuditFetchRowById($ScrmBillOfExchange, 'PIID', $PIId, 'Sales');
			$SqlBillInsert= "UPDATE `$ScrmBillOfExchange` SET `CreditNo`='$CreditNo',`CreditDate`='$CreditDate',
			`DraftDate`='$DraftDate',`ExchangeInvoicevalue`='$ExchangeInvoicevalue',`IssuedBy`='$IssuedBy',`ToAddress`='$ToAddress' WHERE `PIID`='$PIId'";
			 

			 
			 }
	
	/****Bill of Exchange End**/
	
	/****10+2 Start**/
	 if($LogisticsCount==0){

       $SqlLogisticsInsert="INSERT INTO `$ScrmLogisticsDetails`(`PiID`, `VesselName`, `VOY`, `VesselDepartureDate`, `ImportingVessel`, `ImportingVOY`, 
	   `SteamshipCompany`, `ConsolidatorNameAddress`, `ShippingToNameAddress`, `ETD`, `ETA`, `VesselNumber`)VALUES ('$PIId','$VesselName','$VOY','$VesselDepartureDate','$ImportingVessel','$ImportingVOY',
	'$SteamshipCompany','$ConsolidatorNameAddress','$ShippingToNameAddress','$ETD','$ETA','$VesselNumber')";
		 
			 
			 }
			 else{
			$oldLogisticsData = legacyAuditFetchRowById($ScrmLogisticsDetails, 'PiID', $PIId, 'Sales');
			$SqlLogisticsInsert= "UPDATE `$ScrmLogisticsDetails` SET `VesselName`='$VesselName',`VOY`='$VOY',
			`VesselDepartureDate`='$VesselDepartureDate',`ImportingVessel`='$ImportingVessel',`ImportingVOY`='$ImportingVOY',`SteamshipCompany`='$SteamshipCompany',
			`ConsolidatorNameAddress`='$ConsolidatorNameAddress',`ShippingToNameAddress`='$ShippingToNameAddress'
			,`ETD`='$ETD',`ETA`='$ETA',`VesselNumber`='$VesselNumber' WHERE `PiID`='$PIId'";
			 
	
	
			 
			 }
	
	/****10+2 End**/
	
	
	/****Cargo Dispatch Start**/
	
	if($ShipmentDispatchCount==0){
	
	  $SqlShipmentDispatchInsert="
	 INSERT INTO `$ScrmShipmentDispatchDetails`(`PIID`,
	 `ETD`, `ETA`, `BLDate`, `SBNo`, `SBDate`, `SealTime`,`BLNo`)  
	 VALUES ('$PIId','$ETD','$ETA','$BLDate',
	 '$SBNo', '$SBDate','$SealTime','$BLNo')";
	

	}
else{
	$oldDispatchData = legacyAuditFetchRowById('ScrmShipmentDispatchDetails', 'PIID', $PIId, 'Sales');
	$SqlShipmentDispatchInsert="
	 UPDATE `ScrmShipmentDispatchDetails` SET
	 `ETD`='$ETD',`ETA`='$ETA',`BLDate`='$BLDate',
	 `SBNo`='$SBNo',`SBDate`='$SBDate',`SealTime`='$SealTime',`BLNo`='$BLNo' WHERE `PIID`='$PIId'";
	

	
}
	
	/****Cargo Dispatch End**/
	
	//echo $SqlShipmentDispatchInsert;exit;	
	
	/*if($StmtCIInsert){
	$stmtShipmentDispatchInsert=$iconn->query($SqlShipmentDispatchInsert);
        $StmtShipmentInsert=$iconn->query($SqlShipmentInsert);
		$StmtBillInsert=$iconn->query($SqlBillInsert);
	    $StmtLogisticsInsert=$iconn->query($SqlLogisticsInsert);
			header('Location:DocAddReports.php?PoID='.$PIId);

	}*/
	
	$stmtShipmentDispatchInsert=$iconn->query($SqlShipmentDispatchInsert);
	if($ShipmentDispatchCount==0){
		if($stmtShipmentDispatchInsert) legacyAuditLogInsertById('ScrmShipmentDispatchDetails', 'PIID', $PIId, $_POST, array(), 'Sales');
	} else {
		if($stmtShipmentDispatchInsert) legacyAuditLogUpdateById('ScrmShipmentDispatchDetails', 'PIID', $PIId, $oldDispatchData, $_POST, array(), 'Sales');
	}

        $StmtShipmentInsert=$iconn->query($SqlShipmentInsert);
	if($ShipmentCount==0){
		if($StmtShipmentInsert) legacyAuditLogInsertById($ScrmShipmentDetails, 'PiID', $PIId, $_POST, array(), 'Sales');
	} else {
		if($StmtShipmentInsert) legacyAuditLogUpdateById($ScrmShipmentDetails, 'PiID', $PIId, $oldShipmentData, $_POST, array(), 'Sales');
	}

		$StmtBillInsert=$iconn->query($SqlBillInsert);
	if($BillCount==0){
		if($StmtBillInsert) legacyAuditLogInsertById($ScrmBillOfExchange, 'PIID', $PIId, $_POST, array(), 'Sales');
	} else {
		if($StmtBillInsert) legacyAuditLogUpdateById($ScrmBillOfExchange, 'PIID', $PIId, $oldBillData, $_POST, array(), 'Sales');
	}

	    $StmtLogisticsInsert=$iconn->query($SqlLogisticsInsert);
	if($LogisticsCount==0){
		if($StmtLogisticsInsert) legacyAuditLogInsertById($ScrmLogisticsDetails, 'PiID', $PIId, $_POST, array(), 'Sales');
	} else {
		if($StmtLogisticsInsert) legacyAuditLogUpdateById($ScrmLogisticsDetails, 'PiID', $PIId, $oldLogisticsData, $_POST, array(), 'Sales');
	}
	
	if($StmtCIInsert||$stmtShipmentDispatchInsert||$StmtShipmentInsert||$StmtBillInsert||$StmtLogisticsInsert){
			//header('Location:DocAddReports.php?PoID='.$PIId);
echo '<script language="javascript" type="text/javascript"> 
	 alert("Success");

                window.location = "DocAddReports.php?PoID='.$PIId.'";
        </script>';
	}
	
	
	

}
 ?>


<!DOCTYPE html>
<html lang="en">

    <head>
        
            <?php include ($IncludesPath."title-meta.php"); ?>
		 	<?php  include ($IncludesPath."DbLinks.php");?>
            <?php include ($IncludesPath."head-css.php");?>

    </head>

    <body data-sidebar="dark">

	
	 <?php
			include ($IncludesPath."PreLoader.php"); ?>

        <!-- Begin page -->
        <div id="layout-wrapper">


            <?php
			include ($IncludesPath."topbar.php");
			include ($IncludesPath."sidebar.php"); 
			?>



 <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">

                <div class="page-content">
                    <div class="container-fluid">
					
						 <?php
	if($Status !=''){
	//Show Alert 
	?>
	 
<?php } ?>

                        <?php include ($IncludesPath."page-title.php");?>

	
          
	
<!--<div class="row">
                            <div class="col-12">
                                <div class="card">-->
								
				
		
		
			<h3 align="LEFT"><B> CUSTOM INVOICE CREATION</B></h3>
		<form role="form" method="post" enctype="multipart/form-data">

        <div class="card">
		
         

              <div class="card-body">
			  	

			  <div class="row">
			  			
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Custom Invoice</label>
				<input required  type="text" class="form-control" name='CInvNo' id="CInvNo" 
				  placeholder="Enter Custom Invoice No" value="<?php echo $RowCustomInvoice['CInvNo']?>">		
		
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Custom Invoice Date</label>
 <input required   type="date" class="form-control" name='CInvDate' id="CInvDate" 
				  placeholder=" " value="<?php echo $RowCustomInvoice['CInvDate']?>">
                </div>

					<!--<div class="form-group col-sm-3" > 
				<label for="exampleInputEmail1">Custom Invoice Code</label>
                 <select class="form-control select2" id="CInvCode" name="CInvCode" >
				  <option <?php if($RowCustomInvoice['CInvCode']=='03061720,16052900') {?>selected="selected"<?php } ?>
				  value="03061720,16052900">Both</option>
				  <option <?php if($RowCustomInvoice['CInvCode']=='03061720') {?>selected="selected"<?php } ?>
				  
				  value="03061720">Raw</option>
				  <option  <?php if($RowCustomInvoice['CInvCode']=='16052900') {?>selected="selected"<?php } ?>
				  value="16052900">Cooked</option>
				  </select>
					</div>-->
					
					 <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Custom Invoice Code</label>
                 
                <select class="form-control select2" name="CInvCode" id="CInvCode">
					 <?php 
					 //$SciName=$RowPIData['SciName'];
					 foreach($ARY_OPTIONS as $value){
					if($value['TYPE']=='CINVOICE CODE'){
						$OPTIONS= $value['OPTIONS'];
						$VALUE= $value['VALUE']; ?>
			 
					<option  <?php if($RowCustomInvoice['CInvCode']==$VALUE){ ?> selected="selected"  <?php } ?>
					
					value="<?php echo $VALUE ?>"><?php echo $OPTIONS?></option>
					<?php  }}?>
				<select>
				
				</div>
					
				  <div class="form-group col-sm-3" > 
				<label for="exampleInputEmail1">IGST/Discount</label>
                 <select class="form-control select2" id="IGST" name="IGST" >
				  <option  value="">Select</option>
				  <option <?php if($RowCustomInvoice['IGST']=='IGST') {?>selected="selected"<?php } ?>
				  
				  value="IGST">IGST</option>
				  <option  <?php if($RowCustomInvoice['IGST']=='LUT') {?>selected="selected"<?php } ?>
				  value="LUT">LUT</option>
				<option  <?php if($RowCustomInvoice['IGST']=='DISCOUNT') {?>selected="selected"<?php } ?>
				  value="DISCOUNT">DISCOUNT</option>
				
				
						</select>
					</div>
					
					<div class="form-group col-lg-3">  
                  <label for="exampleInputPassword1">Bank Details</label>
                   <select  class="form-control select2" name="BankID" id="BankID">
				 				  <option value="">Select Bank</option>

				   <?php if($RowCustomInvoice['BankID']==0){
				   $BankID=$RowPIData['BankID'];
				   }
				   else{
				   $BankID=$RowCustomInvoice['BankID'];
				   }
				   while ($RowBank=$StmtBank->fetch_array()){ ?>
				     <option <?php if($BankID==$RowBank['ID']){ ?> selected="selected"  <?php } ?>
					 
					 
					 value="<?php echo $RowBank['ID'] ?>">
					 <?php echo $RowBank['AccntName'].' - 
					 '.$RowBank['BankName'] ?></option>
				   
				 <?php  }?>	  
				  </select>
                </div>
					
					<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Discount</label>
				<input  type="decimal" class="form-control" name='Discount' id="Discount" 
				  placeholder="Enter Discount" value="<?php echo $RowCustomInvoice['Discount']?>">		
		
                </div>
				

				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Terms No.of Days</label>
				<input  type="number" class="form-control" name='NoOfDays' id="NoOfDays" 
				  placeholder="Enter No.of Days" value="<?php echo $RowCustomInvoice['NoOfDays']?>">		
		
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Advance Amount</label>
				<input  type="text" class="form-control" name='AdvanceAmount' id="AdvanceAmount" 
				  placeholder="Enter Advance Amount" value="<?php echo $RowCustomInvoice['AdvanceAmount']?>">		
		
                </div>
                <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">OverSeas Commission</label>
				<input  type="text" class="form-control" name='OverSeasCommission' id="OverSeasCommission" 
				  placeholder="Enter OverSeas Commission" value="<?php echo $RowCustomInvoice['OverSeasCommission']?>">		
		
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Description</label>
                 
                <select class="form-control select2" name="Description" id="Description">
									  <option   value="">Select</option>

					 <?php while ($RowPIVarietyList=$StmtPIVarietyList->fetch_array()){
						$OPTIONS= $RowPIVarietyList['ShortName'];
						$FullName= $RowPIVarietyList['FullName'];
						$VALUE= $RowPIVarietyList['VarietyID'];
						 ?>
			 
					<option value="<?php echo $FullName ?>"<?php if( $FullName==$RowCustomInvoice['Description'])
					  echo 'selected="selected"'; ?>><?php echo $OPTIONS;?></option>
					<?php  }?>
				<select>
				
				</div> 
				
 <div class="form-group col-lg-3">
                  <label for="exampleInputEmail1">Name of the Representative</label>
                 
                <select required  class="form-control select2" name="NameofRepresentative" id="NameofRepresentative">
					  <option   value="">Select</option>
					<?php while($RowUserDetails=$StmtUserDetails->fetch_array()){
					$UserName=$RowUserDetails['UserName'];
					$UserID=$RowUserDetails['UserID'];
					$Designation=$RowUserDetails['Designation']; ?>
			 
					<option <?php if($UserID==$RowCustomInvoice['NameofRepresentative']){ ?> selected=='selected'  <?php } ?>
					
					value="<?php echo $UserID ?>"><?php echo $UserName .' - '.$Designation?></option>
					<?php  }?>
				<select>
				</div> 
				
				

				<div class="form-group col-lg-12" >
                  <label for="exampleInputEmail1">Commercial Inv Description</label>
				  
				  <textarea name="CIDescription" class="form-control" rows="4" cols="50"
                    id="CIDescription"   
					placeholder="Enter Description(Optional)" ><?php echo $RowCustomInvoice['CIDescription'] ?></textarea>
				 		
		
                </div>	
				
				<div class="form-group col-lg-12" >
                  <label for="exampleInputEmail1">Packing List Description</label>
				  
				  <textarea name="PackDescription" class="form-control" rows="7" cols="50"
                    id="PackDescription"   
					placeholder="Enter Description(Optional)" ><?php echo $RowCustomInvoice['PackDescription'] ?></textarea>
				 		
		
                </div>	
				
				
				
				</div>            
				</div>            
             
           			

            </div>
		
		<h3 align="LEFT"><B>   SHIPMENT DETAILS	</B></h3>
		          <form role="form" method="post" enctype="multipart/form-data">
		
        <div class="card">
		
        <!--  <form role="form" method="post" enctype="multipart/form-data">-->

              <div class="card-body">
			  
			 
<div class="row">
<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Container No</label>
				<input  type="text" class="form-control" name='ContainerNo' id="ContainerNo" 
				  placeholder="Enter Container No" value="<?php echo $RowShipmentDetails['ContainerNo']?>">		
		
                </div>
				
				
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Liner Seal No</label>
				<input  type="text" class="form-control" name='SealNo' id="SealNo" 
				  placeholder="Enter Seal No" value="<?php echo $RowShipmentDetails['SealNo']?>">	
</div>		
			<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Pack Seal No</label>
				<input  type="text" class="form-control" name='PackSealNo' id="PackSealNo" 
				  placeholder="Enter Pack Seal No" value="<?php echo $RowShipmentDetails['PackSealNo']?>">	
</div>	
	
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Frieght Charge</label>
				<input  type="text" class="form-control" name='FrieghtCharge' id="FrieghtCharge" 
				  placeholder="Enter Frieght Charge" value="<?php echo $RowShipmentDetails['FrieghtCharge']?>">		
		
                </div>
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Exchange Rate</label>
				<input  type="text" class="form-control" name='ExchangeRate' id="ExchangeRate" 
				  placeholder="Enter Exchange Rate" value="<?php echo $RowShipmentDetails['ExchangeRate']?>">		
		
                </div>	

				
				
			<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Vehicle Number</label>
				<input  type="text" class="form-control" name='VehicleNumber' id="VehicleNumber" 
				  placeholder="Enter Vehicle Number" value="<?php echo $RowShipmentDetails['VehicleNumber']?>">		
		
                </div>
				
				
					<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">E-WAY BILL NUMBER</label>
				<input  type="text" class="form-control" name='EWayBillNo' id="EWayBillNo" 
				  placeholder="Enter E-WAY Bill  Number" value="<?php echo $RowShipmentDetails['EWayBillNo']?>">		
		
                </div>

			<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Place Of Receipt</label>
				<input  type="text" class="form-control" name='PlaceOfRecipt' id="PlaceOfRecipt" 
				  placeholder="Enter Place Of Receipt" value="<?php echo $RowShipmentDetails['PlaceOfRecipt']?>">		
		
                </div>		
			

				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Final Destination</label>
				<input  type="text" class="form-control" name='FinalDestination' id="FinalDestination" 
				  placeholder="Enter Final Destination" value="<?php echo $RowShipmentDetails['FinalDestination']?>">		
		
                </div>
			
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Port Of Loading</label>
				 <select    class="form-control select2" name="PortOfLoading" id="PortOfLoading">
				  <option value="">Select Port</option>
				   <?php foreach ($AryPorts1 as $RowPort ){ ?>
				     <option 
					 <?php if($RowShipmentDetails['PortOfLoading']==$RowPort['PortId']){ ?> selected="selected" <?php } ?>
					 value="<?php echo $RowPort['PortId'] ?>">
					 <?php echo $RowPort['Name']?></option>
				   
				 <?php  }?>	  
				  </select>
                </div>
				
				
				
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Port of Discharge</label>
				<select    class="form-control select2" name="PortofDischarge" id="PortofDischarge">
				  <option value="">Select Port</option>
				   <?php foreach ($AryPorts as $RowPort ){ ?>
				     <option 
					 					 <?php if($RowShipmentDetails['PortofDischarge']==$RowPort['PortId']){ ?> selected="selected" <?php } ?>

					 value="<?php echo $RowPort['PortId'] ?>">
					 <?php echo $RowPort['Name']?></option>
				   
				 <?php  }?>	  
				  </select>
                </div>	
				
					</div>
             
           
			
			</div>
			</div>
				
				
			<h3 align="LEFT"><B> BILL OF EXCHANGE(LC) CREATION</B></h3>
				
        <div class="card">
		

              <div class="card-body">
			  		<div class="row">	 	
				<!--<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Credit No</label>
				<input  type="text" class="form-control" name='CreditNo' id="CreditNo" 
				  placeholder="Enter Credit No" value="<?php echo $RowBillofExchage['CreditNo']?>">		
		
                </div>	
				
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Credit Date</label>
				<input  type="date" class="form-control" name='CreditDate' id="CreditDate" 
				  placeholder="Enter Credit Date" value="<?php echo $RowBillofExchage['CreditDate']?>">		
		
                </div>-->
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Draft Date</label>
				<input  type="date" class="form-control" name='DraftDate' id="DraftDate" 
				  placeholder="Enter Draft Date" value="<?php echo $RowBillofExchage['DraftDate']?>">		
		
                </div>
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Invoice Value</label>
				<input  type="text" class="form-control" name='ExchangeInvoicevalue' id="ExchangeInvoicevalue" 
				  placeholder="Enter Invoice Value" value="<?php echo $RowBillofExchage['ExchangeInvoicevalue']?>">		
		
                </div>
				<?php  if($Status==1){ ?>
				
				
				<div class="form-group col-lg-4" >
                  <label for="exampleInputEmail1">To Address(LC)</label>
				  
				  <textarea name="ToAddress" class="form-control" rows="4" cols="50" 
                    id="ToAddress"   
					placeholder="Enter To Address" ><?php echo $RowBillofExchage['ToAddress'] ?></textarea>
				 		
		
                </div>
				
				<div class="form-group col-lg-4" >
                  <label for="exampleInputEmail1">Issued By</label>
				  
				  <textarea name="IssuedBy" class="form-control" rows="4" cols="50" 
                    id="IssuedBy"   
					placeholder="Enter Issued By" ><?php echo $RowBillofExchage['IssuedBy'] ?></textarea>
				 		
		
                </div>
				<?php } else{ ?> 
				<div class="form-group col-lg-4" >
                  <label for="exampleInputEmail1">To Address(NON-LC)</label>
				  
				  <textarea name="ToAddress" class="form-control" rows="4" cols="50" 
                    id="ToAddress"   
					placeholder="Enter To Address" ><?php echo $ToAddress ?></textarea>
				 		
		
                </div>
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Issued By</label>
<input  readonly type="text" class="form-control" name='IssuedBy' id="IssuedBy" 
				  placeholder="Enter , for next line " value="">
                </div>
				
				<?php } ?> 
				
				
				</div>
				
				</div>           
           
            </div>
            </div>

				<h3 align="LEFT"><B>10+2 REPORT CREATION</B></h3>
				
        <div class="card">
		

              <div class="card-body">
			  <div class="row">
			  		
                 	<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Vessel Name</label>
				<input   type="text" class="form-control" name='VesselName' id="VesselName" 
				  placeholder="Enter Vessel Name" value="<?php echo $RowTenPlusTwo['VesselName']?>">		
		
                </div>
				
		<!--		<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Vessel Number</label>
				<input  type="text" class="form-control" name='VesselNumber' id="VesselNumber" 
				  placeholder="Enter Vessel Number" value="<?php echo $RowTenPlusTwo['VesselNumber']?>">		
		
                </div>-->
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">VOY</label>
					<input   type="text" class="form-control" name='VOY' id="VOY" 
				  placeholder="Enter VOY " value="<?php echo $RowTenPlusTwo['VOY']?>">
                </div>


					<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Departure Date</label>
				<input  type="date" class="form-control" name='VesselDepartureDate' id="VesselDepartureDate" 
				  placeholder="Enter Discount" value="<?php echo $RowTenPlusTwo['VesselDepartureDate']?>">		
		
                </div>
				
					
					
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Final Importing Vessel Name</label>
				<input  type="text" class="form-control" name='ImportingVessel' id="ImportingVessel" 
				  placeholder="Enter Final Importing Vessel" value="<?php echo $RowTenPlusTwo['ImportingVessel']?>">		
		
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Importing VOY</label>
				<input  type="text" class="form-control" name='ImportingVOY' id="ImportingVOY" 
				  placeholder="Enter Importing VOY" value="<?php echo $RowTenPlusTwo['ImportingVOY']?>">		
		
                </div>
				
				<!--<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Steamship Company</label>
				<input  type="text" class="form-control" name='SteamshipCompany' id="SteamshipCompany" 
				  placeholder="Enter Steamship Company" value="<?php echo $RowTenPlusTwo['SteamshipCompany']?>">	
</div>	-->
  <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Steamship Company</label>
                 
                <select class="form-control select2" name="SteamshipCompany" id="SteamshipCompany">
									  <option   value="">Select</option>

					 <?php while ($RowLiner=$StmtLiner->fetch_array()){
						$Name= $RowLiner['LinerName'];
						$VALUE= $RowLiner['LinerID'];
						 ?>
			 
					<option value="<?php echo $VALUE ?>"<?php if( $VALUE==$RowTenPlusTwo['SteamshipCompany'])
					  echo 'selected="selected"'; ?>><?php echo $Name;?></option>
					<?php  }?>
				<select>
				
				</div>	
				
			<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Ship To Name</label>
				<input  type="text" class="form-control" name='ShippingToNameAddress' id="ShippingToNameAddress" 
				  placeholder="Enter Ship To Name" value="<?php echo $RowTenPlusTwo['ShippingToNameAddress']?>">	
</div>		
		<!--	<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">ETD</label>
				<input  type="date" class="form-control" name='ETD' id="ETD" 
				  placeholder="Enter Discount" value="<?php echo $RowTenPlusTwo['ETD']?>">		
		
                </div><div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">ETA</label>
				<input  type="date" class="form-control" name='ETA' id="ETA" 
				  placeholder="Enter Discount" value="<?php echo $RowTenPlusTwo['ETA']?>">		
		
                </div>	-->
		
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Consolidator</label>
                 
                <select class="form-control select2" name="ConsolidatorNameAddress" id="ConsolidatorNameAddress">
									  <option   value="">Select</option>

					 <?php while ($RowLogistics=$StmtLogistics->fetch_array()){
						$Name= $RowLogistics['Name'];
						$VALUE= $RowLogistics['ID'];
						 ?>
			 
					<option value="<?php echo $VALUE ?>"<?php if( $VALUE==$RowTenPlusTwo['ConsolidatorNameAddress'])
					  echo 'selected="selected"'; ?>><?php echo $Name;?></option>
					<?php  }?>
				<select>
				
				</div>
					
				</div>           
            </div>
            </div>
			
			<h3 align="LEFT"><B> CARGO DISPATCH DETAILS</B></h3>
				
        <div class="card">
		

              <div class="card-body">
			  <div class="row">
    
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">ETA</label>
              <input    type="date" class="form-control" name='ETA' id="ETA" 
				  placeholder=" Enter ETA" value="<?php echo $RowShipmentDispatch['ETA']?>">
                </div> 
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">ETD</label>
				<input  type="date" class="form-control" name='ETD' id="ETD" 
				  placeholder="Enter ETD" 
				  value="<?php echo $RowShipmentDispatch['ETD']?>">		
		
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">BL No</label>
				<input   type="text" class="form-control" name='BLNo' id="BLNo" 
				  placeholder="Enter BL Number" value="<?php echo $RowShipmentDispatch['BLNo']; ?>">		
		
                </div>
			  <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">BL Date</label>
                  <input   type="date" class="form-control"
				  name='BLDate' id="BLDate" placeholder=" Enter BLDate" value="<?php echo $RowShipmentDispatch['BLDate']?>">
                </div> 	
				 
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">SB No</label>
				<input  type="text" class="form-control" name='SBNo' id="SBNo" 
				  placeholder="Enter SB Number" value="<?php echo $RowShipmentDispatch['SBNo']?>">		
		
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">SB Date</label>
				<input  type="date" class="form-control" name='SBDate' id="SBDate" 
				  placeholder="Enter SB Date" value="<?php echo $RowShipmentDispatch['SBDate']?>">		
		
                </div>
				
				

				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">SealTime</label>
				<input  type="time" class="form-control" name='SealTime' id="SealTime" 
				  placeholder="Enter Seal Time" value="<?php echo $RowShipmentDispatch['SealTime']?>">		
		
                </div> 

				
			</div>           
			</div>           
            <div>
			            	

			
			
				<div class="col-xs-12 card-footer"  align="right">
				 <a target="" href="DocAddReports.php?PoID=<?php echo $PIId ?>"
       class="btn btn-danger  "> Close</a>
  		
                <button type="submit"  name='submit' class="btn btn-success">Submit</button>
              </div>
				
				</div>
            </form>
       <!-- end col -->
                        </div> <!-- end row -->
    
	</div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->
                        </div>
                        <!-- end row-->
                        
                    </div> <!-- container-fluid -->
                </div>
                <!-- End Page-content -->

                 <?php include ($IncludesPath."footer.php");?>
                
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        

		<?php include ($IncludesPath."vendor-scripts.php");?>
		<?php include ($IncludesPath."NewScripts.php");?>
		
  
       
    </body>

