<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

include ("TestAutoMail.php");


$PageTitleFolder="Sales";
$PageTitle="ISF Form";


$PIId=$_GET['PIID'];
$ExporterID=$_GET['ExporterID'];
$DocType='ISF';

//ROBO

$SqlPI="SELECT 
SPD.PoID,SPD.InvNo,SPD.InvDate,SPD.PoNo,SPD.ConsigneeID,
SPD.Status,SCD.ConsigneeName,ContainerNo,
SCD.ConsigneeAddress,SCD.ConsigneeCountry,SCI.CInvNo
FROM `ScrmPIData` SPD 
LEFT JOIN ScrmConsigneeDetails SCD ON SCD.ConID=SPD.ConsigneeID
LEFT JOIN ScrmCustomInvoices SCI ON SCI.PiID=SPD.PoID
WHERE SPD.PoID='$PIId'"; 
$StmtPI=$iconn->query($SqlPI);
$RowPI=$StmtPI->fetch_array();

$InvNo=$RowPI['InvNo'];
$InvDate=$RowPI['InvDate'];
$PoNo=$RowPI['PoNo'];
$CInvNo=$RowPI['CInvNo'];
$ConsigneeName=$RowPI['ConsigneeName'];
$ConsigneeAddress=$RowPI['ConsigneeAddress'];
$ContainerNo=$RowPI['ContainerNo'];


 
 $SqlISFDetails="SELECT * FROM `ScrmISFDetails` WHERE PIID='$PIId'";
$StmtISFDetails=$iconn->query($SqlISFDetails);
$RowISFDetails=$StmtISFDetails->fetch_array();
$Count=mysqli_num_rows($StmtISFDetails);

$SqlProcessor="SELECT * FROM AdminExporterDetails WHERE ExpType in(2,3)";
$StmtProcessor=$iconn->query($SqlProcessor);

$SqlLiner="SELECT * FROM `ScrmLinerMaster` WHERE Status=1";
$StmtLiner=$iconn->query($SqlLiner);

$SqlPorts1="SELECT * FROM ScrmPortCities ";
$StmtPorts=$iconn->query($SqlPorts1);
	

extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){	
			 
	IF($Count==0){		
        $SqlInsert="INSERT INTO `ScrmISFDetails`(`PIID`,
 `TranshipmentPort`, `TranshipmentDate`, `StuffingLocation`, 
`ReportPayments`, `LoadingDate`, `STOWPosition`, `DataLogger`,
 `DataLoggerPositions`, `SDD`, `ISFTransactionNo`, `ISFEntryNO`,
 `ISFEntryDate`, `ADDuty`, `Portdues1`, `Portdues2`, `Others`,
 `GrandTotal`, `BankRefNo`, `PaymentDate`, `FDAClearDate`, `FDAExam`,
 `StartDate`, `EndDate`, `GetOutFull`, `Remarks`, `Status`,
 `ShipmentIssues`,`TemperatureOfRecod`, `SizeNType`, `ETA_FD`, `ETA_POD`,
 `EstimatedETA`,`ImportingVessel`, `BLDate`, `ShippingOnboardDt`, `VoyOrg`,`VoyFinal`, 
 `OriginatingVessel`, `ContainerNo`, `SealNo`, `BLNo`, `FreezingDate`,
 `ConsumptionDate`, `ProductionDate`, `BestBeforeDate`,`ShippingLineID`,`ByRoad`,`LcNo`,`LcDate`,`LcIssuedBy`) 		   
	VALUES ('$PIId','$TranshipmentPort','$TranshipmentDate',
'$StuffingLocation','$ReportPayments','$LoadingDate',
	'$STOWPosition','$DataLogger','$DataLoggerPositions','$SDD',
'$ISFTransactionNo','$ISFEntryNO','$ISFEntryDate','$ADDuty',
	'$Portdues1','$Portdues2','$Others','$GrandTotal','$BankRefNo','$PaymentDate',
	'$FDAClearDate','$FDAExam','$StartDate','$EndDate','$GetOutFull'
	,'$Remarks','$Status','$ShipmentIssues','$TemperatureOfRecod','$SizeNType'
	,'$ETA_FD','$ETA_POD','$ETA_POD','$ImportingVessel','$BLDate'
	,'$ShippingOnboardDt','$VoyOrg','$VoyFinal','$OriginatingVessel','$ContainerNo'
	,'$SealNo','$BLNo','$FreezingDate','$ConsumptionDate','$ProductionDate'
	,'$BestBeforeDate','$ShippingLineID','$ByRoad','$LcNo','$LcDate','$LcIssuedBy')"; 
	}ELSE{
		$oldData = legacyAuditFetchRowById('ScrmISFDetails', 'PIID', $PIId, 'Sales');

	$SqlInsert="UPDATE `ScrmISFDetails` SET 
	`TranshipmentPort`='$TranshipmentPort',`TranshipmentDate`='$TranshipmentDate',
	`StuffingLocation`='$StuffingLocation',`ReportPayments`='$ReportPayments',
	`LoadingDate`='$LoadingDate',`STOWPosition`='$STOWPosition',
	`DataLogger`='$DataLogger',`DataLoggerPositions`='$DataLoggerPositions',
	`SDD`='$SDD',`ISFTransactionNo`='$ISFTransactionNo',
	`ISFEntryNO`='$ISFEntryNO',`ISFEntryDate`='$ISFEntryDate',
	`ADDuty`='$ADDuty',`Portdues1`='$Portdues1',`Portdues2`='$Portdues2',
	`Others`='$Others',`GrandTotal`='$GrandTotal',`BankRefNo`='$BankRefNo',
	`PaymentDate`='$PaymentDate',`FDAClearDate`='$FDAClearDate',
	`FDAExam`='$FDAExam',`StartDate`='$StartDate',
	`EndDate`='$EndDate',`GetOutFull`='$GetOutFull',`Remarks`='$Remarks',
	`Status`='$Status',`ShipmentIssues`='$ShipmentIssues',
	`TemperatureOfRecod`='$TemperatureOfRecod',`SizeNType`='$SizeNType',
	`ETA_FD`='$ETA_FD',`ETA_POD`='$ETA_POD',`ImportingVessel`='$ImportingVessel',
	`BLDate`='$BLDate',`ShippingOnboardDt`='$ShippingOnboardDt',
	`VoyFinal`='$VoyFinal',`VoyOrg`='$VoyOrg',
	`OriginatingVessel`='$OriginatingVessel',`ContainerNo`='$ContainerNo',
	`SealNo`='$SealNo',`BLNo`='$BLNo',`FreezingDate`='$FreezingDate',
	`ConsumptionDate`='$ConsumptionDate',`ProductionDate`='$ProductionDate',
	`BestBeforeDate`='$BestBeforeDate',
	`ByRoad`='$ByRoad',
	`ShippingLineID`='$ShippingLineID',
	`LcNo`='$LcNo',
	`LcDate`='$LcDate',
	`LcIssuedBy`='$LcIssuedBy' WHERE PIID='$PIId'";	
		
		
		
		
	}
		
	//echo	$SqlInsert; exit;
			$StmtInsert=$iconn->query($SqlInsert);
		
			
		
	if($StmtInsert){
	    if($Count==0){
	        legacyAuditLogInsertById('ScrmISFDetails', 'PIID', $PIId, $_POST, array(), 'Sales');
	    } else {
	        legacyAuditLogUpdateById('ScrmISFDetails', 'PIID', $PIId, $oldData, $_POST, array(), 'Sales');
	    }
	    postContainerNo($PIId,$ContainerNo,$DocType);

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
					
						 

                        <?php include ($IncludesPath."page-title.php");?>
                                 <?php include "PopUpTable.php"; ?>

	
          
	
               <div class="row">
                            <div class="col-12">
                                <div class="card">
								
								<form role="form" method="post">
                                    <div class="card-body">
									
	
               
				
				
				
			
						
			<div class="row">							
	        <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1"> Custom Invoice</label>
				<input readonly required type="text" class="form-control" name='CInvNo' id="CInvNo" 
				  style="background: gray;color: white;"placeholder="Enter Custom Invoice No" value="<?php echo $PoNo;?>">		
            </div>
			
			 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">PO No</label>
				<input readonly required type="text" class="form-control" name='CInvNo' id="CInvNo" 
				style="background: gray;color: white;"  placeholder="Enter  PO No" value="<?php echo $PoNo;?>">		
            </div>
			
		
		<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Consignee</label>
				<input readonly required type="text" class="form-control" name='ConsigneeName' id="ConsigneeName" 
				style="background: gray;color: white;"  placeholder="Enter Consignee Name" value="<?php echo $ConsigneeName?>">		
		
         </div>


         		
				
				
		<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Address</label>
				<input readonly required type="text" class="form-control" name='ConsigneeAddress' id="ConsigneeAddress" 
				style="background: gray;color: white;"  placeholder="Enter Consignee Address" value="<?php echo $ConsigneeAddress?>">		
		
         </div>
		 
		<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">ISF Transaction No</label>
				<input  type="text" class="form-control" name='ISFTransactionNo' id="ISFTransactionNo" 
				  placeholder="Enter ISF Transaction No" value="<?php echo $RowISFDetails['ISFTransactionNo']?>">				
                </div> 
		 
	 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Container Number<span style="color:red">*</span></label>
				<input required type="text" class="form-control" name='ContainerNo' id="ContainerNo" 
				  placeholder="Enter Container Number" value="<?php echo $ContainerNo;?>">		
		
         </div>
		 
		 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Seal Number<span style="color:red">*</span></label>
				<input required type="text" class="form-control" name='SealNo' id="SealNo" 
				  placeholder="Enter Seal Number" value="<?php echo $RowISFDetails['SealNo'];?>">		
		
         </div>


<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">BL Number<span style="color:red">*</span></label>
				<input required  type="text" class="form-control" name='BLNo' id="BLNo" 
				  placeholder="Enter BL Number" value="<?php echo $RowISFDetails['BLNo'];?>">		
		
         </div>
		 
		 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">BL Date<span style="color:red">*</span></label>
                  <input required  type="date" class="form-control" name='BLDate' id="BLDate" 
				  placeholder=" " value="<?php echo $RowISFDetails['BLDate'];?>">
         </div>
 
		
		 
		 
		
				
				<div class="form-group col-lg-3">  
                  <label for="exampleInputPassword1">Shipping Line<span style="color:red">*</span></label>
                   <select  required class="form-control select2" name="ShippingLineID" id="ShippingLineID">
				 				  <option value="">Select</option>

				   <?php  while ($RowLiner=$StmtLiner->fetch_array()){ ?>
				     <option <?php if($RowISFDetails['ShippingLineID']==$RowLiner['LinerID']){ ?> selected="selected"  <?php } ?>
					value="<?php echo $RowLiner['LinerID'] ?>"><?php echo $RowLiner['LinerName'] ?></option>
				   
				 <?php  }?>	  
				  </select>
                </div>
				
					<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Shipping Onboard Date<span style="color:red">*</span></label>
                  <input required  type="date" class="form-control" name='ShippingOnboardDt' id="ShippingOnboardDt" 
				  placeholder=" " value="<?php echo $RowISFDetails['ShippingOnboardDt'];?>">
         </div>
		 <!--
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Originating Vessel<span style="color:red">*</span></label>
				<input required  type="text" class="form-control" name='OriginatingVessel' id="OriginatingVessel" 
				  placeholder="Enter Originating Vessel Name" value="<?php echo $RowISFDetails['OriginatingVessel'];?>">		
		
			</div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Originating Voyage No<span style="color:red">*</span></label>
				<input  required type="text" class="form-control" name='VoyOrg' id="VoyOrg" 
				  placeholder="Enter Originating Voyage No" value="<?php echo $RowISFDetails['VoyOrg']?>">		
		
                </div>
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Final Importing Vessel</label>
                  <input   type="text" class="form-control" name='ImportingVessel' id="ImportingVessel" 
				  placeholder="Enter Importing Vessel Details" value="<?php echo $RowISFDetails['ImportingVessel'];?>">
         </div>			 
				
				
				 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Final Voyage No</label>
				<input  type="text" class="form-control" name='VoyFinal' id="VoyFinal" 
				  placeholder="Enter Voyage No" value="<?php echo $RowISFDetails['VoyFinal'];?>">		
		
         </div>
		
		 <div class="form-group col-lg-3">
			      <label for="exampleInputPassword1">Port of Transhipment<span style="color:red">*</span></label>
		          <select  required class="form-control select2" name="TranshipmentPort" id="TranshipmentPort">
				  <option value="">Select Port</option>
				   <?php 
				   $TranshipmentPort=$RowISFDetails['TranshipmentPort'];
				   while ($RowPorts=$StmtPorts->fetch_array()){ ?>
				     <option <?php if($TranshipmentPort==$RowPorts['Name']){ ?> selected="selected"  <?php } ?>
					 
					 value="<?php echo $RowPorts['Name'] ?>">
					 <?php echo $RowPorts['Name']?></option>
				   
				 <?php  }?>	  
				  </select>				  
				  
	             </div>	
		 

        <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Transhipment Date</label>
                  <input   type="date" class="form-control" name='TranshipmentDate' id="TranshipmentDate" 
				  placeholder="Enter Transhipment Date " value="<?php echo $RowISFDetails['TranshipmentDate']?>">
         </div>

    -->		 
			
				<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Stuffing Location<span style="color:red">*</span></label>              			   
				  <select class="form-control select2" name="StuffingLocation" id="StuffingLocation">
				 
				<?php while($RowProcessor=$StmtProcessor->fetch_array()) { ?>  
				
				 <option <?php if($RowISFDetails['StuffingLocation']==$RowLiner['ExpID']){ ?> selected="selected"  <?php } ?>
					value="<?php echo $RowProcessor['ExpName'] ?>"><?php echo '('.$RowProcessor['ExpApprovalNo'].')';?></option>
				   
			
				<?php } ?>
				
				<select>   
                </div> 

				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">ETA POD<span style="color:red">*</span></label>
				<input required type="date" class="form-control" name='ETA_POD' id="ETA_POD" 
				  placeholder="Enter ETA POD" value="<?php echo $RowISFDetails['ETA_POD'];?>">		
		
                </div>
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">ETA FD<span style="color:red">*</span></label>
				<input  required type="date" class="form-control" name='ETA_FD' id="ETA_FD" 
				  placeholder="Enter ETA FD" value="<?php echo $RowISFDetails['ETA_FD']?>">		
		
                </div>
						
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Date of Loading</label>
				<input  type="date" class="form-control" name='LoadingDate' id="LoadingDate" 
				  placeholder="Enter Date of Loading" value="<?php echo $RowISFDetails['LoadingDate']?>">		
		
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Size and Type</label>
				<input  type="text" class="form-control" name='SizeNType' id="SizeNType" 
				  placeholder="40 FEET" value="<?php echo $RowISFDetails['SizeNType'] ?>">		
		
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">STOW Position</label>
				<input  type="text" class="form-control" name='STOWPosition' id="STOWPosition" 
				  placeholder="Enter STOW Position" value="<?php echo $RowISFDetails['STOWPosition']?>">		
		
                </div>
				
			<!--	<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Freight</label>
				<input  type="text" class="form-control" name='Freight' id="Freight" 
				  placeholder="Enter Freight" value="<?php echo $RowISFDetails['Freight']?>">		
		
                </div>  -->
                
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Data Logger</label>
				<input  type="text" class="form-control" name='DataLogger' id="DataLogger" 
				  placeholder="Enter Data Logger Details" value="<?php echo $RowISFDetails['DataLogger']?>">		
		
                </div>
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Placed AT</label>
				<input  type="text" class="form-control" name='DataLoggerPositions' id="DataLoggerPositions" 
				  placeholder="Enter Data Logger Positions" value="<?php echo $RowISFDetails['DataLoggerPositions']?>">		
		
                </div>	

				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">SDD</label>
				<input  type="text" class="form-control" name='SDD' id="SDD" 
				  placeholder="Enter SDD" value="<?php echo $RowISFDetails['SDD']?>">				
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">By Road</label>
				<input  type="text" class="form-control" name='ByRoad' id="ByRoad" 
				  placeholder="Enter By Road Details" value="<?php echo $RowISFDetails['ByRoad']?>">		
		
                </div>
				
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Temperature of Record</label>
				<input  type="text" class="form-control" name='TemperatureOfRecod' id="TemperatureOfRecod" 
				  placeholder="-20 DEGREES CELSIUS" value="-20 DEGREES CELSIUS">				
                </div>


                <div class="form-group col-lg-6" >
				
					<a target="_blank" id="PoDetails" href="#"
				data-id="<?php echo $PIId; ?>" 
				data-value="<?php echo $PoNo; ?>"
				class="btn btn-danger  ">More Transhipments</a>
                </div>
				
                </div>
<!--				
				<HR></HR>
				<H5 style="color: BROWN;">Transactions</H5>
                <div class="row">
				
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">ISF Transaction No</label>
				<input  type="text" class="form-control" name='ISFTransactionNo' id="ISFTransactionNo" 
				  placeholder="Enter ISF Transaction No" value="<?php echo $RowISFDetails['ISFTransactionNo']?>">				
                </div>
				
			
				</div>

				<HR></HR>

<H5 style="color: BROWN;">Duty</H5>

				
                <div class="row">
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">AD Duty</label>
				<input  type="text" class="form-control" name='ADDuty' id="ADDuty" 
				  placeholder="Enter AD Duty" value="<?php echo $RowISFDetails['ADDuty']?>">				
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Port dues1</label>
				<input  type="text" class="form-control" name='Portdues1' id="Portdues1" 
				  placeholder="Enter Portdues1" value="<?php echo $RowISFDetails['Portdues1']?>">				
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Port dues2</label>
				<input  type="text" class="form-control" name='Portdues2' id="Portdues2" 
				  placeholder="Enter Port dues2" value="<?php echo $RowISFDetails['Portdues2']?>">				
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Others</label>
				<input  type="text" class="form-control" name='Others' id="Others" 
				  placeholder="Enter Others" value="<?php echo $RowISFDetails['Others']?>">				
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Grand Total</label>
				<input  type="text" class="form-control" name='GrandTotal' id="GrandTotal" 
				  placeholder="Enter Grand Total" value="<?php echo $RowISFDetails['GrandTotal']?>">				
                </div>
				
			
</div>
				
				
				<HR></HR>

<H5 style="color: BROWN;">Payment</H5>

				
                <div class="row">
				

               <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Bank Ref No</label>
				<input  type="text" class="form-control" name='BankRefNo' id="BankRefNo" 
				  placeholder="Enter Bank Ref No" value="<?php echo $RowISFDetails['BankRefNo'];?>">				
                </div>

               <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Date</label>
				<input  type="Date" class="form-control" name='PaymentDate' id="PaymentDate" 
				  placeholder="Enter Payment Date" value="<?php echo $RowISFDetails['PaymentDate'];?>">				
                </div>

                </div>
				

          
				<HR></HR>

<H5 style="color: BROWN;">FDA</H5>

				
                <div class="row">
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">FDA Clear Date</label>
				<input  type="date" class="form-control" name='FDAClearDate' id="FDAClearDate" 
				  placeholder="Enter FDA Clear Date" value="<?php echo $RowISFDetails['FDAClearDate']; ?>">				
                </div>

               <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">FDA Exam</label>
				<input  type="text" class="form-control" name='FDAExam' id="FDAExam" 
				  placeholder="Enter FDA Exam" value="<?php echo $RowISFDetails['FDAExam'];?>">				
                </div>

              <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Start Date</label>
				<input  type="date" class="form-control" name='StartDate' id="StartDate" 
				  placeholder="Enter Start Date" value="<?php echo $RowISFDetails['StartDate']; ?>">				
                </div>
                
                <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">End Date</label>
				<input  type="date" class="form-control" name='EndDate' id="EndDate" 
				  placeholder="Enter End Date" value="<?php echo $RowISFDetails['EndDate'];?>">				
                </div>

               <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Get Out Full</label>
				<input  type="date" class="form-control" name='GetOutFull' id="GetOutFull" 
				  placeholder="Enter GetOutFull" value="<?php echo $RowISFDetails['GetOutFull'];?>">				
                </div>
              <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Remarks</label>
				<input  type="TEXT" class="form-control" name='Remarks' id="Remarks" 
				  placeholder="Enter Remarks" value="<?php echo $RowISFDetails['Remarks']?>">				
                </div>
             
             <div class="form-group col-sm-3" > 
				<label for="exampleInputEmail1">status</label>
                 <select class="form-control select2" id="Status" name="Status" >
				  <option  value="">Select</option>
				  <option <?php if($RowISFDetails['Status']=='Rejected') {?>selected="selected"<?php } ?> 
				  value="Rejected">Rejected</option>
				  <option  <?php if($RowISFDetails['Status']=='Cancelled') {?>selected="selected"<?php } ?>
				  value="Cancelled">Cancelled</option>
                  <option  <?php if($RowISFDetails['Status']=='Destroyed') {?>selected="selected"<?php } ?>
				  value="Destroyed">Destroyed</option>
                 <option  <?php if($RowISFDetails['Status']=='ROB') {?>selected="selected"<?php } ?>
				  value="ROB">Container ROB</option>
                 <option  <?php if($RowISFDetails['Status']=='Return') {?>selected="selected"<?php } ?>
				  value="Return">Return Container</option>
              </select>
			</div>

         <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Shipment Issues</label>
				<input  type="TEXT" class="form-control" name='ShipmentIssues' id="ShipmentIssues" 
				  placeholder="Enter Shipment Issues" value="<?php echo $RowISFDetails['ShipmentIssues'];?>">				
                </div>
				
				</div>
				

				<HR></HR>
         <H5 style="color: BROWN;">Dates</H5>				
                <div class="row">
				
			   <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Freezing Date</label>
				<input  type="date" class="form-control" name='FreezingDate' id="FreezingDate" 
				  placeholder="Enter End Date" value="<?php echo $RowISFDetails['FreezingDate'];?>">				
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Consumption Date</label>
				<input  type="date" class="form-control" name='ConsumptionDate' id="ConsumptionDate" 
				  placeholder="Enter End Date" value="<?php echo $RowISFDetails['ConsumptionDate'];?>">				
                </div>
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Production Date</label>
				<input  type="date" class="form-control" name='ProductionDate' id="ProductionDate" 
				  placeholder="Enter End Date" value="<?php echo $RowISFDetails['ProductionDate'];?>">				
                </div>
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Best Before Date</label>
				<input  type="date" class="form-control" name='BestBeforeDate' id="BestBeforeDate" 
				  placeholder="Enter End Date" value="<?php echo $RowISFDetails['BestBeforeDate'];?>">				
                </div>


				
				</div>
									
-->	
 		
			
			  <div class="col-xs-12 box-footer"  align="right">
			  <a target="" href="DocAddReports.php?PoID=<?php echo $PIId ?>"
       class="btn btn-danger  "> Close</a>
  		<?php if($Count==0){	 ?>
                <button type="submit"  name='submit' class="btn btn-primary">Add</button>
			 
            
					<?php }  else{	 ?>
					
			
			 
  		
                <button type="submit"  name='submit' class="btn btn-primary">Update</button>
				<?php } ?>
<button id="<?php echo  $PIId;?>" style="width:100px;"
	onClick="reply_click(this.id)" class="btn btn-warning "> Delete</button>
	
								 


              </div>
			  

                        </div> <!-- end row -->
    
	</div> <!-- end card body-->
            </form>
       <!-- end col -->
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
</html>				

<script>


 $(document).on('click','#PoDetails',function(e){
	 			e.preventDefault();

	var PoNo=$(this).data('value');
	  var PoID =$(this).data('id');
	  
	 $('#Update').val(PoID);
	 
	 showPoPopUp(PoNo,PoID);
	  
	

			
    	});
		
function showPoPopUp(PoNo,PoID){
	
			 $.ajax({
		   url:'Ajax/AjTranshipList.php',
		  type:'POST',
		  data:{'PoID':PoID},
		  success:function(data){
			  $("#Update").hide();

			 $('.modal-title').text('PO Status Details - '+PoNo);
			$("#PopUpTable").html(data);

			$('#myModal').modal('show');
					}
			});

}	

$(document).on('click','.StatusBulkUpdate',function(e){
	e.preventDefault();
		var InvId=$(this).attr('id');
		var FromTOPID=$('#FromTOPID').val();
		var ToTOPID=$('#ToTOPID').val();
		var FromDate=$('#FromDate').val();
		var TranshipDt=$('#TranshipDt').val();
		var VesselName=$('#VesselName').val();
		var VOYNO=$('#VOYNO').val();
		
		
	$.ajax({
    type: 'POST',
    url: 'Ajax/AjaxTranshipmentPortUpdate.php',
    data: {'FromTOPID':FromTOPID,'ToTOPID':ToTOPID,
	'FromDate':FromDate,'TranshipDt':TranshipDt,'InvId':InvId,'VesselName':VesselName,'VOYNO':VOYNO,
	'Type':'TranshipmentPortInsert'},
	
	
    
    success: function(response){
		var data=JSON.parse(response);
			if(data['sts']=='Failed'){
			alert(data['msg']);
			

			}else{
				$('#myModal').modal('hide');
			alert(data['msg']);
							

			}
	 }
  });

	
});

 $(document).on('click', '.Delete', function(e){
	  var TspID =$(this).data('id');


   		var r = confirm("Are  you sure Do you Want To Delete ?");
  if (r == true) { 
			  
		$.ajax({
		
		  url:'Ajax/AjaxTranshipmentPortUpdate.php',
		  type:'POST',
		  data:{'Type':'TranshipmentPortDelete','TspID':TspID},
		  
			 success: function(response){
		var data=JSON.parse(response);
			if(data['sts']=='Failed'){
			alert(data['msg']);
			

			}else{
				
				
							alert(data['msg']);

				location.reload();
							

			}
	 }
			});	

} else {
			
  }			
  });

</script>



<script>
function reply_click(clicked_id) {

   		var r = confirm("Are You Sure Do You Want To Delete "+clicked_id+"?");
  if (r == true) { 
		 //alert(clicked_id);		  
		$.ajax({
		  url:'Ajax/AjaxDelete.php',
		  type:'POST',
		  data:{'TYPE':'CustomDelete','PiID':clicked_id},
		  success:function(data){
			  
			  
			// $("#example1").html(data);
			 alert("Deleted Successfully");
			location.reload();
			
			}
			});	

} else {
			
  }			
  }

</script>

<script>

function showData(data){
			
					//$("#PIList").html(data);
 
 		var Stable=    $('#scroll-vertical-datatable').DataTable({
        "scrollY":        "450px",
        "scrollCollapse": true,
        "paging":         false,
        "language": {
            "paginate": {
                "previous": "<i class='mdi mdi-chevron-left'>",
                "next": "<i class='mdi mdi-chevron-right'>"
            }
        },
        "drawCallback": function () {
            $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
        },
		buttons: ['copy', 'excel', 'pdf', 'colvis']
    });
	
	Stable.buttons().container()
        .appendTo('#scroll-vertical-datatable_wrapper .col-md-6:eq(0)');
		}

</script>
