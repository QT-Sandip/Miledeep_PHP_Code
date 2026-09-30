

<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/SalesNew/Constants.php");
include ($cp."Includes/IncludePage.php");



$PageTitleFolder="Sales";
$PageTitle="Proforma Invoice Edit";


 $POID=$_GET['id'];
 $PostedUserId = $_SESSION['UserID'];
$PageUrl = ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME));


 
$SQL_OPTIONS="SELECT * FROM `OPTIONS` WHERE  DEPT='SALES' order by ABS(OPTIONS) asc ";
$STMT_OPTIONS=$iconn->query($SQL_OPTIONS);
$ARY_OPTIONS=array();
WHILE($ROW=$STMT_OPTIONS->FETCH_ARRAY()){
	$ARY_OPTIONS[]=$ROW;
}

	$sqlPIData="SELECT * FROM ScrmPIData  WHERE PoID='$POID'";
	$stmtPIData=$iconn->query($sqlPIData);
	$RowPIData=$stmtPIData->fetch_array();
	 $Country=$RowPIData['DestinationCountry'];
	 $InvoiceNo=$RowPIData['InvNo'];
	 $ExpID=$RowPIData['ExporterID'];

      $SqlImporter="SELECT * FROM `ScrmImporterOfRecord` WHERE Status=1";
	$StmtImporter=$iconn->query($SqlImporter);
	
	$SqlNotifyparty="SELECT * FROM `ScrmNotifyparty` WHERE Status=1";
	$StmtNotifyparty=$iconn->query($SqlNotifyparty);
	
$SqlExporter="SELECT * FROM AdminExporterDetails WHERE ExpType in(1,3)";
	$StmtExporter=$iconn->query($SqlExporter);
  
    $SqlProcessor="SELECT * FROM AdminExporterDetails WHERE ExpType in(2,3)";
	$StmtProcessor=$iconn->query($SqlProcessor);

	$SqlCertification="SELECT * FROM `ScrmOrderCertification` WHERE Status=1";
	$StmtCertification=$iconn->query($SqlCertification);
	
	$SqlConsignee="SELECT * FROM ScrmConsigneeDetails WHERE Status=1";
	$StmtConsignee=$iconn->query($SqlConsignee);
	
	$SqlBuyer="SELECT * FROM ScrmBuyerDetails WHERE Status=1";
	$StmtBuyer=$iconn->query($SqlBuyer);
	
	$SqlAgent="SELECT * FROM `ScrmPiAgentMaster` WHERE Status=1";
	$StmtAgent=$iconn->query($SqlAgent);
	
	$SqlBank="SELECT * FROM ScrmBankDetailsMaster WHERE `BankStatus` = '1'";
	$StmtBank=$iconn->query($SqlBank);
	
	$SqlCurrency="SELECT * FROM `ScrmCurrencyMaster` WHERE `Status` = '1'";
	$StmtCurrency=$iconn->query($SqlCurrency);
	
    $SqlCountryList="SELECT * FROM ScrmCountriesList";
	$StmtCountryList=$iconn->query($SqlCountryList); 

	$SqlPaymentTerms="SELECT * FROM ScrmPaymentTerms";
	$StmtPaymentTerms=$iconn->query($SqlPaymentTerms);

    $SqlAdminUsers="SELECT * FROM AdminUserDetails WHERE DepartmentID=9 and PoUserStatus=1";
	$StmtAdminUsers=$iconn->query($SqlAdminUsers);
	
	$SqlPorts1="SELECT * FROM ScrmPortCities WHERE IsPortofLoading=1";
    $StmtPorts1=$iconn->query($SqlPorts1);

	$SqlUltimateConsignee="SELECT * FROM ScrmUltimateConsigneeDetails WHERE Status=1";
	$StmtUltimateConsignee=$iconn->query($SqlUltimateConsignee);
	
	
	 $SqlPorts="SELECT * FROM ScrmPortCities where Country='$Country'"; 
	$StmtPorts=$iconn->query($SqlPorts);
	
		$SqlShipmentTerms="SELECT * FROM `ScrmShipmentTermsMaster` ";
	$StmtShipmentTerms=$iconn->query($SqlShipmentTerms);
	
	
	while($RowPo=$StmtPorts->fetch_array()){
		$AryPorts[]=$RowPo;
	}

		$AccAval=true;
			$CYear=date("Y");
			$AYear=$CYear+1;
			while($AccAval){
			$PoRefId=generateAccountId();
			//$PINo="SME/".$CYear.'-'.$AYear.'/'.$PoRefId;
			$sql="SELECT count(*) as Total FROM `$TblScrmPoData` where PoRefId='$PoRefId'";
			$sqlca=$iconn->query($sql);
			$rlt = mysqli_fetch_array($sqlca,MYSQLI_ASSOC);
			if($rlt['Total'] == 0 ){
				$AccAval=false;
			}			
			}
	
	 function generateAccountId(){
				$generator = "1357902468"; 
                $result = ""; 
                for ($i = 1; $i <= 5; $i++) { 
                    $result .= substr($generator, (rand()%(strlen($generator))), 1); 
                }  
				
				return $result;
		}
	
extract($_POST);
extract($_GET);


if(isset($_POST['submit'])){
	 	
	$SqlCountryType="SELECT CountryType FROM `ScrmCountriesList` where id='$DestinationCountry' ";
	$StmtCountryType=$iconn->query($SqlCountryType);
	$RowCountryType=$StmtCountryType->fetch_array();
	
	$TypeCountry=$RowCountryType['CountryType'];

	


	$YEAR=date("Y");
	$PoStatus='0';
	
	

	$LoginId=$_SESSION["r_id"];
	$InvNo = sprintf( '%03d', $InvNo);
	/***uma(06/07/2022)**/
	
	
	 //if($InvoiceNo==$InvNo){
		 /***uma(06/07/2022)**/
	 if($InvoiceNo==$InvNo && $ExpID==$ExporterID){
	/***uma(10/04/2023)**/
	$InvNumFull=getFullPiInvoiceNumbertoSave($ExporterID,$InvDate,$InvNo);
		  $oldPi1 = legacyAuditFetchRowById('ScrmPIData', 'PoID', $POID, 'Sales');
		  $sql_update="UPDATE `ScrmPIData` SET `InvNo`='$InvNo',`InvNumFull`='$InvNumFull',`InvDate`='$InvDate',
`ExporterID`='$ExporterID',`ProcessorID`='$ProcessorID',`ConsigneeID`='$ConsigneeID',
`BuyerID`='$BuyerID',`PoNo`='$PoNo',`PoDate`='$PoDate',`DestinationCountry`='$DestinationCountry',`TypeCountry`='$TypeCountry',
`Year`='$YEAR',`BankID`='$BankID',`PortOfDischarge`='$PortOfDischarge',
`FinalDestination`='$FinalDestination',`PaymentTerms`='$ByPayment',`ShippingTerms`='$ShippingTerms',`DeliveryTerms`='$DeliveryTerms',`PreparedBy`='$PreparedBy',`PRInstrctns`='$PRInstrctns',`QtyUnit`='$QtyUnit',
`UnitCurrency`='$UnitCurrency',`PortOfDischarge`='$PortOfDischarge',`ShipByDate`='$ShipByDate',`Remarks`='$Remarks',`ImporterID`='$ImporterID',`NotifyPartyID`='$NotifyPartyID',`ReferenceNo`='$ReferenceNo',`Agent`='$Agent',`Certification`='$Certification',`PortofLoading`='$PortofLoading',`ShowBank`='$ShowBank',
`UltimateConsigneeID`='$UltimateConsigneeID',
`Freight`='$Freight' WHERE PoID='$POID'";  
  
  $oldPo1 = legacyAuditFetchRowById('ScrmPoData', 'PoID', $POID, 'Sales');
  $SqlPoUpdate="UPDATE `ScrmPoData` SET
 `InvNo`='$InvNo',`InvNumFull`='$InvNumFull',`InvDate`='$InvDate',
 `ExporterID`='$ExporterID',`ProcessorID`='$ProcessorID',
 `LotNo`='$LotNo',`SciName`='$SciName',`ConsigneeID`='$ConsigneeID',
 `BuyerID`='$BuyerID',`PoNo`='$PoNo',`PoDate`='$PoDate',
 `DestinationCountry`='$DestinationCountry',`TypeCountry`='$TypeCountry',
 `Year`='$YEAR',`BankID`='$BankID',`PortOfDischarge`='$PortOfDischarge',
 `FinalDestination`='$FinalDestination',`PaymentTerms`='$ByPayment',`ShippingTerms`='$ShippingTerms',
 `DeliveryTerms`='$DeliveryTerms',`PreparedBy`='$PreparedBy',`PRInstrctns`='$PRInstrctns',
 `QtyUnit`='$QtyUnit',`UnitCurrency`='$UnitCurrency',
 `ShipByDate`='$ShipByDate',`Remarks`='$Remarks',`ImporterID`='$ImporterID',
 `NotifyPartyID`='$NotifyPartyID',`ReferenceNo`='$ReferenceNo',
 `Agent`='$Agent',`Certification`='$Certification',
 `PortofLoading`='$PortofLoading',`ShowBank`='$ShowBank',
 `UltimateConsigneeID`='$UltimateConsigneeID',
 `Freight`='$Freight'
 
 
 WHERE PoID ='$POID'";



	$stmt1=$iconn->query($sql_update);
	$StmtPoUpdate=$iconn->query($SqlPoUpdate);
	
	
	//$INV_ID = $iconn->insert_id;
	
	
	
	

	if($stmt1){
		legacyAuditLogUpdateById('ScrmPIData', 'PoID', $POID, $oldPi1, $_POST, array(), 'Sales');
		if($StmtPoUpdate){
			legacyAuditLogUpdateById('ScrmPoData', 'PoID', $POID, $oldPo1, $_POST, array(), 'Sales');
		}
		$ArgumentsArray[PostedUserId]=$PostedUserId;
            $ArgumentsArray[PageUrl]=$PageUrl;
            $ArgumentsArray[Remarks]=$Remarks;
            $ArgumentsArray[ReferenceID]=$POID;
            postUserEditLog($ArgumentsArray);
	

		//header('Location: PoBreakUp.php?id='.$INV_ID);
		//header('Location: PIList.php');
		echo '<script language="javascript" type="text/javascript"> 
		alert("Proforma Updated");
                 window.close();
        </script>';
	
	}
}

else{
	$StartYear=date('Y',strtotime($InvDate));
	
			  $EndYear=$StartYear+1;
			  $FinStartDate=$StartYear.'-04-01'; 
			  $FinEndDate=$EndYear.'-03-31';

			  $InvNumFull=getFullPiInvoiceNumbertoSave($ExporterID,$InvDate,$InvNo);
			
	$sqlCount="SELECT count(*) as Count FROM `$TblScrmPIData` where InvDate BETWEEN '$FinStartDate' and '$FinEndDate' and
	InvNo='$InvNo' and ExporterID='$ExporterID' and Status not in(2,4,7)";
	
			$StmtCount=$iconn->query($sqlCount);
			$RowCount=$StmtCount->fetch_array();
	if($RowCount['Count']!=0){		
echo '<script language="javascript" type="text/javascript"> 
alert("Update Failed  Invoice Number already exists");
                window.location = "PIList.php";
        </script>';
	}
	else{
		
		$oldPi2 = legacyAuditFetchRowById('ScrmPIData', 'PoID', $POID, 'Sales');
		$sql_update="UPDATE `ScrmPIData` SET `InvNo`='$InvNo',`InvNumFull`='$InvNumFull',`InvDate`='$InvDate',
`ExporterID`='$ExporterID',`ProcessorID`='$ProcessorID',`LotNo`='$LotNo',`SciName`='$SciName',`ConsigneeID`='$ConsigneeID',
`BuyerID`='$BuyerID',`PoNo`='$PoNo',`PoDate`='$PoDate',`DestinationCountry`='$DestinationCountry',`TypeCountry`='$TypeCountry',
`Year`='$YEAR',`BankID`='$BankID',`PortOfDischarge`='$PortOfDischarge',
`FinalDestination`='$FinalDestination',`PaymentTerms`='$ByPayment',`ShippingTerms`='$ShippingTerms',`DeliveryTerms`='$DeliveryTerms',`PreparedBy`='$PreparedBy',`PRInstrctns`='$PRInstrctns',`QtyUnit`='$QtyUnit',`UnitCurrency`='$UnitCurrency',`PortOfDischarge`='$PortOfDischarge',`ShipByDate`='$ShipByDate',`Remarks`='$Remarks',`ImporterID`='$ImporterID',`NotifyPartyID`='$NotifyPartyID',`ReferenceNo`='$ReferenceNo',`Agent`='$Agent',`Certification`='$Certification',`PortofLoading`='$PortofLoading',`ShowBank`='$ShowBank',`UltimateConsigneeID`='$UltimateConsigneeID'
 WHERE PoID='$POID'";  
  
  $oldPo2 = legacyAuditFetchRowById('ScrmPoData', 'PoID', $POID, 'Sales');
  $SqlPoUpdate="UPDATE `ScrmPoData` SET 
 `InvNo`='$InvNo',`InvNumFull`='$InvNumFull',`InvDate`='$InvDate',
 `ExporterID`='$ExporterID',`ProcessorID`='$ProcessorID',
 ConsigneeID`='$ConsigneeID',
 `BuyerID`='$BuyerID',`PoNo`='$PoNo',`PoDate`='$PoDate',
 `DestinationCountry`='$DestinationCountry',`TypeCountry`='$TypeCountry',
 `Year`='$YEAR',`BankID`='$BankID',`PortOfDischarge`='$PortOfDischarge',
 `FinalDestination`='$FinalDestination',`PaymentTerms`='$ByPayment',`ShippingTerms`='$ShippingTerms',
 `DeliveryTerms`='$DeliveryTerms',`PreparedBy`='$PreparedBy',`PRInstrctns`='$PRInstrctns',
 `QtyUnit`='$QtyUnit',`UnitCurrency`='$UnitCurrency',
  `ShipByDate`='$ShipByDate',`Remarks`='$Remarks',`ImporterID`='$ImporterID',`NotifyPartyID`='$NotifyPartyID' 
  ,`ReferenceNo`='$ReferenceNo',`Agent`='$Agent',`Certification`='$Certification',`PortofLoading`='$PortofLoading',`ShowBank`='$ShowBank',`UltimateConsigneeID`='$UltimateConsigneeID' WHERE PoID ='$POID'";



	$stmt1=$iconn->query($sql_update);
	$StmtPoUpdate=$iconn->query($SqlPoUpdate);
	

	if($stmt1){
		legacyAuditLogUpdateById('ScrmPIData', 'PoID', $POID, $oldPi2, $_POST, array(), 'Sales');
		if($StmtPoUpdate){
			legacyAuditLogUpdateById('ScrmPoData', 'PoID', $POID, $oldPo2, $_POST, array(), 'Sales');
		}
		$ArgumentsArray[PostedUserId]=$PostedUserId;
            $ArgumentsArray[PageUrl]=$PageUrl;
            $ArgumentsArray[Remarks]=$Remarks;
            $ArgumentsArray[ReferenceID]=$POID;
            postUserEditLog($ArgumentsArray);

		echo '<script language="javascript" type="text/javascript"> 
		alert("Proforma Updated");
                 window.close();
        </script>';
	
	}
	}
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

	
          
	
<div class="row">
                            <div class="col-12">
                                <div class="card">
								
								<form role="form" method="post" enctype="multipart/form-data">
                                    <div class="card-body">
									
										<div class="row">
										
										
			<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Select Exporter <span style="color:red">*</span></label>              			   
				  <select required class="form-control select2" name="ExporterID" id="ExporterID">
				 <option value="">Select Exporter</option> 
				<?php while($RowExporter=$StmtExporter->fetch_array()) { ?>  
				
				<option <?php if($RowPIData['ExporterID']==$RowExporter['ExpID']){ ?> selected="selected"  <?php } ?>
				Value="<?php echo $RowExporter['ExpID'];?>"><?php echo $RowExporter['ExpName'];?></OPTION>
				
				<?php } ?>
				
				<select>   
                </div>
										
										 <div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Select Processor <span style="color:red">*</span></label>              			   
				  <select required class="form-control select2" name="ProcessorID" id="ProcessorID">
				  <option value="">Select Processor</option>
				<?php while($RowProcessor=$StmtProcessor->fetch_array()) { ?>  
				
				<option <?php if($RowPIData['ProcessorID']==$RowProcessor['ExpID']){ ?> selected="selected"  <?php } ?>
				Value="<?php echo $RowProcessor['ExpID'];?>"><?php echo $RowProcessor['ExpName'].'('.$RowProcessor['ExpApprovalNo'].')';?></OPTION>
				
				<?php } ?>
				
				<select>   
                </div> 
			  <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Reference Number</label>
                  <input   type="text" class="form-control" name='ReferenceNo' 
				  id="ReferenceNo" placeholder="Enter Reference Number" value="<?php echo $RowPIData['ReferenceNo'];?>">
                </div>	
			
				<div class="form-group col-lg-3" ></div>
				 				
			
		
			  
			   <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Proforma Invoice No<span style="color:red">*</span></label>
                  <input required  type="number" class="form-control" name='InvNo' 
				  id="InvNo" placeholder="Enter Proforma Invoice No" value="<?php echo $RowPIData['InvNo']; ?>">
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Proforma Invoice Date<span style="color:red">*</span></label>
                  <input required type="date" value="<?php  echo $RowPIData['InvDate'];?>" 
				  class="form-control" name='InvDate' id="InvDate" 
				  placeholder="Enter Proforma Invoice No ">
                </div>
			  
			  <div class="form-group col-lg-3">
                  <label for="exampleInputPassword1"> P.O No</label>
                  <input  type="text" class="form-control" name='PoNo' value="<?php  echo $RowPIData['PoNo'];?>"  
				  id="PoNo" placeholder="Enter P.O Number">
                </div> 
				
				<?php  //echo date("Y-m-d");?>
				
				<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1"> P.O Date</label>
                  <input  type="date" value="<?PHP ECHO $RowPIData['PoDate']; ?>" 
				  class="form-control" name='PoDate' id="PoDate" placeholder="Enter P.O Date">
                </div>
			  
			  
               				
		 	
		         
				
				
				
			
		 	<div class="form-group col-lg-3">  
                  <label for="exampleInputPassword1">Consignee<span style="color:red">*</span></label>
                   <select  required class="form-control select2" name="ConsigneeID" id="ConsigneeID">
				  <option value="">Select Consignee</option>
				   <?php while ($RowConsignee=$StmtConsignee->fetch_array()){ ?>
				     <option <?php if($RowPIData['ConsigneeID']==$RowConsignee['ConID']){ ?> selected="selected"  <?php } ?>
					 value="<?php echo $RowConsignee['ConID'] ?>">
					 <?php echo $RowConsignee['ConsigneeName'] ?></option>
				   
				 <?php  }?>	  
				  </select>
                </div> 			
		 	
		       <div class="form-group col-lg-3">  
                  <label for="exampleInputPassword1">Ultimate Consignee</label>
                   <select   class="form-control select2" name="UltimateConsigneeID" id="UltimateConsigneeID">
				  <option value="">Select</option>
				   <?php while ($RowUltimateConsignee=$StmtUltimateConsignee->fetch_array()){ ?>
				     <option <?php if($RowPIData['UltimateConsigneeID']==$RowUltimateConsignee['ConID']){ ?> selected="selected"  <?php } ?>
					 value="<?php echo $RowUltimateConsignee['ConID'] ?>">
					 <?php echo $RowUltimateConsignee['ConsigneeName'] ?></option>
				   
				 <?php  }?>	  
				  </select>
                </div> 
				
			
		  <div class="form-group col-lg-3">  
                  <label for="exampleInputPassword1">Buyer Name</label>
                   <select   class="form-control select2" name="BuyerID" id="BuyerID">
				  <option value="">Select Buyer</option>
				   <?php while ($RowBuyer=$StmtBuyer->fetch_array()){ ?>
				     <option <?php if($RowPIData['BuyerID']==$RowBuyer['BuyerID']){ ?> selected="selected"  <?php } ?>
					 value="<?php echo $RowBuyer['BuyerID'] ?>">
					 <?php echo $RowBuyer['BuyerName']?></option>
				   
				 <?php  }?>	  
				  </select>
                </div>

                <div class="form-group col-lg-3">  
                  <label for="exampleInputPassword1">Notify Party</label>
                   <select   class="form-control select2" name="NotifyPartyID" id="NotifyPartyID">
				  <option value="">Select</option>
				   <?php while ($RowNotifyparty=$StmtNotifyparty->fetch_array()){ ?>
				     <option  <?php if($RowPIData['NotifyPartyID']==$RowNotifyparty['NotifyPartyID']){ ?> selected="selected"  <?php } ?>
					 value="<?php echo $RowNotifyparty['NotifyPartyID'] ?>">
					 <?php echo $RowNotifyparty['NotifyPartyName'] ?></option>
				   
				 <?php  }?>	  
				  </select>
                </div>
			   
			   
				
				<div class="form-group col-lg-3">  
                  <label for="exampleInputPassword1">Importer of Record</label>
                   <select   class="form-control select2" name="ImporterID" id="ImporterID">
				  <option value="">Select</option>
				   <?php while ($RowImporter=$StmtImporter->fetch_array()){ ?>
				     <option <?php if($RowPIData['ImporterID']==$RowImporter['ImporterID']){ ?> selected="selected"  <?php } ?>
					 value="<?php echo $RowImporter['ImporterID'] ?>">
					 <?php echo $RowImporter['ImporterName'] ?></option>
				   
				 <?php  }?>	  
				  </select>
                </div>
				
				
				
				<div class="form-group col-lg-3">  
                  <label for="exampleInputPassword1">Agent</label>
                   <select   class="form-control select2" name="Agent" id="Agent">
				  <option value="">Select</option>
				   <?php while ($RowAgent=$StmtAgent->fetch_array()){ ?>
				     <option <?php if($RowPIData['Agent']==$RowAgent['AgentID']){ ?> selected="selected"  <?php } ?>
					 value="<?php echo $RowAgent['AgentID'] ?>">
					 <?php echo $RowAgent['AgentName'] ?></option>
				   
				 <?php  }?>	  
				  </select>
                </div>
				
				<div class="form-group col-lg-3">  
                  <label for="exampleInputPassword1">Certification</label>
                   <select   class="form-control select2" name="Certification" id="Certification">
				  <option value="">Select</option>
				   <?php while ($RowCertification=$StmtCertification->fetch_array()){ ?>
				     <option <?php if($RowPIData['Certification']==$RowCertification['CerificationID']){ ?> selected="selected"  <?php } ?>
					
					 value="<?php echo $RowCertification['CerificationID'] ?>">
					 <?php echo $RowCertification['CerificationName'] ?></option>
				   
				 <?php  }?>	  
				  </select>
                </div>
				
				<div class="form-group col-lg-3">  
                  <label for="exampleInputPassword1">Port of Loading</label>
                   <select  required class="form-control select2" name="PortofLoading" id="PortofLoading">
				  <option value="">Select</option>
				   <?php while ($RowPorts=$StmtPorts1->fetch_array()){ ?>
				     <option <?php  if($RowPIData['PortofLoading']==$RowPorts['PortId']) { ?> 
					 selected=="selected"<?php } ?>
					 value="<?php echo $RowPorts['PortId'] ?>">
					 <?php echo $RowPorts['Name'] ?></option>
				   
				 <?php  }?>	  
				  </select>
                </div>
				
				
				 <div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Destination Country<span style="color:red">*</span></label>
                 
				  
				  <select required class="form-control select2" name="DestinationCountry" id="DestinationCountry">
				  <option value="">Select Country</option>
				   <?php 
				   $DestinationCountry=$RowPIData['DestinationCountry'];
				   while ($RowCountry=$StmtCountryList->fetch_array()){ ?>
				     <option <?php if($DestinationCountry==$RowCountry['id']){ ?> selected="selected"  <?php } ?>
					 
					 value="<?php echo $RowCountry['id'] ?>">
					 <?php echo $RowCountry['name']?></option>
				   
				 <?php  }?>	  
				  </select>
                </div>
				
				
			
		
				
			       <div class="form-group col-lg-3">
			      <label for="exampleInputPassword1">Port of Discharge<span style="color:red">*</span></label>
		          <select  required class="form-control select2" name="PortOfDischarge" id="PortOfDischarge">
				  <option value="">Select Port</option>
				   <?php 
				   $PortOfDischarge=$RowPIData['PortOfDischarge'];
				   foreach ($AryPorts as $RowPort ){ ?>
				     <option <?php if($PortOfDischarge==$RowPort['PortId']){ ?> selected="selected"  <?php } ?>
					 
					 value="<?php echo $RowPort['PortId'] ?>">
					 <?php echo $RowPort['Name']?></option>
				   
				 <?php  }?>	  
				  </select>				  
				  
	             </div>	
	
	          <!--   <div class="form-group col-lg-3">
                  <label for="exampleInputPassword1"> Final Destination<span style="color:red">*</span></label>
                  <input required type="text" class="form-control" name='FinalDestination' 
				  id="FinalDestination" placeholder="Enter Final Destination" value="<?php echo $RowPIData['FinalDestination'];?>">
                </div> -->
				
				 <div class="form-group col-lg-3">
                  <label for="exampleInputPassword1"> Final Destination<span style="color:red">*</span></label>
                  
				  <!--<input required type="text" class="form-control" name='FinalDestination' 
				  id="FinalDestination" placeholder="Enter Final Destination" value="<?php echo $RowPIData['FinalDestination'];?>">
               -->
			   
			   <select  required class="form-control select2" name="FinalDestination" id="FinalDestination">
				  <option value="">Select Final Destination</option>
				   <?php 
				   $FinalDestination=$RowPIData['FinalDestination'];
				   foreach ($AryPorts as $RowPort ){ ?>
				     <option <?php if($FinalDestination==$RowPort['PortId']){ ?> selected="selected"  <?php } ?>
					 
					 value="<?php echo $RowPort['PortId'] ?>">
					 <?php echo $RowPort['Name']?></option>
				   
				 <?php  }?>	  
				  </select>

			   </div> 
				
		        <div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">ShipBy Date</label>
                  <input    type="date" class="form-control" name="ShipByDate"
				  id="ShipByDate" placeholder="Enter ShipBy Date" Value="<?php echo $RowPIData['ShipByDate'] ;?>"
				 ></input>
                </div> 
				
				<div class="form-group col-lg-3">  
                  <label for="exampleInputPassword1">Bank Details<span style="color:red">*</span></label>
                   <select required class="form-control select2" name="BankID" id="BankID">
				  <option value="">Select Bank</option>
				   <?php 
				   $BankID=$RowPIData['BankID'];
				   while ($RowBank=$StmtBank->fetch_array()){ ?>
				     <option <?php if($BankID==$RowBank['ID']){ ?> selected="selected"  <?php } ?>
					 
					 
					 value="<?php echo $RowBank['ID'] ?>">
					 <?php echo $RowBank['AccntName'].' - 
					 '.$RowBank['BankName'].'-'.$RowBank['AccountNumber'] ?></option>
				   
				 <?php  }?>	  
				  </select>
                </div>
				
				
				<div class="form-group col-lg-3" >              									 
                <label for="exampleInputEmail1">Show Bank<span style="color:red">*</span></label>
				<select required class="form-control select2" name="ShowBank" id="ShowBank">
				 	<option  <?php if($RowPIData['ShowBank']==0){ ?> selected="selected"  <?php } ?> value="0">No</option>
				 	<option  <?php if($RowPIData['ShowBank']==1){ ?> selected="selected"  <?php } ?> value="1">Yes</option>
										
				</select>
                </div>
				
				<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Payment Terms<span style="color:red">*</span></label>
                 
				   <select  required class="form-control select2" name="ByPayment" id="ByPayment">
				  <option value="">Select Payment</option>
				   <?php 
				   $PaymentTerms=$RowPIData['PaymentTerms'];
				   
				   while ($RowPayment=$StmtPaymentTerms->fetch_array()){ ?>
				     <option 
					 <?php if($PaymentTerms==$RowPayment['ID']){ ?> selected="selected"  <?php } ?>
					 value="<?php echo $RowPayment['ID'] ?>">
					 <?php echo $RowPayment['PaymentName']?></option>
				   
				 <?php  }?>	  
				  </select>
                </div> 

			
				
				
              <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Shipment Terms<span style="color:red">*</span></label>
                 
                <select required class="form-control select2" name="ShippingTerms" id="ShippingTerms">
					 <?php 
					 $ShippingTerms=$RowPIData['ShippingTerms'];
					 while($RowShipterms=$StmtShipmentTerms->fetch_array()){?>
			 
					<option  <?php if($ShippingTerms==$RowShipterms['TermName']){ ?> selected="selected"  <?php } ?>
					
					value="<?php echo $RowShipterms['TermName'] ?>"><?php echo $RowShipterms['TermName'] ?></option>
					<?php  }?>
				<select>
				
				</div>
				
				
				
				
				
				<div class="form-group col-lg-3" >              									 
                <label for="exampleInputEmail1">Delivery Terms<span style="color:red">*</span></label>
				<select required class="form-control select2" name="DeliveryTerms" id="DeliveryTerms">
				 	<option  value="">Select</option>
				 	<option  <?php if($RowPIData['DeliveryTerms']=='CY/CY'){ ?> selected=="selected" <?php } ?>
					value="CY/CY">CY/CY</option>
					<option  <?php if($RowPIData['DeliveryTerms']=='CY/SD'){ ?> selected=="selected" <?php } ?> 
					value="CY/SD">CY/SD</option>
									
				</select>
                </div>
				
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Quantity Units<span style="color:red">*</span></label>
                 
                <select required class="form-control select2" name="QtyUnit" id="QtyUnit">
					 <?php 
					 $QtyUnit=$RowPIData['QtyUnit'];
					 foreach($ARY_OPTIONS as $value){
					if($value['TYPE']=='UOM'){
						$OPTIONS= $value['OPTIONS'];
						$VALUE= $value['VALUE']; ?>
			 
					<option <?php if($QtyUnit==$VALUE){ ?> selected="selected"  <?php } ?>
					
					
					value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
					<?php  }}?>
				<select>
				
				</div>
				
				
				
				<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Currency<span style="color:red">*</span></label>
				  <select  required class="form-control select2" name="UnitCurrency" id="UnitCurrency">
                  <option value="">Select Currency</option>
                  
				   <?php while ($RowCurrency=$StmtCurrency->fetch_array()){ ?>
				     <option <?php if($RowPIData['UnitCurrency']==$RowCurrency['CurrencyCode']){ ?> selected="selected"  <?php } ?>
					 value="<?php echo $RowCurrency['CurrencyCode'] ?>">
					 <?php echo $RowCurrency['CurrencyName'].' - 
					 '.$RowCurrency['CurrencyCode'] .'('.$RowCurrency['CurrenySymbol'].')'?></option>
				   
				 <?php  }?>	 
				  </select>
                </div>
				
				
				
				
				
				<!--<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1"> Prepared By<span style="color:red">*</span></label>
                 
                <select required class="form-control select2" name="PreparedBy" id="PreparedBy">
					  <option   value="">Select Prepared By</option>
					<?php while($RowAdminUsers=$StmtAdminUsers->fetch_array()){
					$UserName=$RowAdminUsers['UserName']; 
					$PreparedBy=$RowPIData['PreparedBy'];
					?>
			 
					<option <?php if($PreparedBy==$UserName){ ?> selected="selected"  <?php } ?>
					
					value="<?php echo $UserName ?>"><?php echo $UserName ?></option>
					<?php  }?>
				<select>
				</div> -->
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Freight</label>
				<input  type="text" class="form-control" name='Freight' id="Freight" 
				  placeholder="Enter Freight" value="<?php echo $RowPIData['Freight']?>">				
                </div>
				
				<div class="form-group col-lg-12" >
                  <label for="exampleInputEmail1">Remarks</label>
				  
				  <textarea name="Remarks" class="form-control" rows="2" cols="50"
                    id="Remarks"   
					placeholder="Enter Remarks" ><?php echo $RowPIData['Remarks']; ?></textarea>
				 		
		
                </div>
				
				</div> 
				
				<div class="col-xs-12 box-footer"  align="right">
				<button type="button" id="close"  name="close"  class="btn btn-danger">Close </button>
            
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
<script>

 $('#close').click(function(e) {
 window.close();
 });
 
$('#Cminus').on('click', function(){
	
      var NoCon= $("#NoContainers").val();
	  
	  if(NoCon > 1){
		   NoCon= parseInt(NoCon)-1;
		 $("#NoContainers").val(NoCon); 
	  }
});

$('#Cplus').on('click', function(){
	 
       var NoCon= $("#NoContainers").val();
		  if(NoCon < 10){
			 NoCon= parseInt(NoCon)+1;
		 $("#NoContainers").val(NoCon); 
	  }
});
</script>

<script>

$("document").ready(function(){
	  /*$("#ConsigneeID").select2();
	  $("#BuyerID").select2();
	  $("#DestinationCountry").select2();
	  $("#PortOfDischarge").select2();*/

    $("#file").change(function() {
        alert('changed!');
    });
	
	

	
});
  	

</script>


<script>

$("document").ready(function(){
	 /* $("#ConsigneeID").select2();
	  $("#PortOfDischarge").select2();*/
});

$('#ConsigneeID').change(function(){
	 var BuyerId= $('#ConsigneeID').val(); 
	 if(BuyerId==''){
		 return;
	 }
   $.ajax({
    type: 'POST',
    url: 'Ajax/AjBuyerDtls.php',
	dataType: 'json',
    data: {'BUYERID' : BuyerId},  
    success: function(response){
		 var myJSON = JSON.parse(JSON.stringify(response));
		$("#DestinationCountry").val(myJSON['ConsigneeCountry']).change();
		
		/* $("#BRAND").val(myJSON['ConsigneeBrand']);
		 
		 $("#PORT_OF_DIS").val(myJSON['ConsigneePort']);
		 $("#ADDRESS_OF_CONSIGNEE").val(myJSON['ConsigneeAddress']);
		 $("#FISHING_PMT_NO").val(myJSON['ConsigneeFishingNo']);*/
		 
	 }
  });
  });
  
  
  $('#DestinationCountry').change(function(){
	 var CountryId= $('#DestinationCountry').val(); 
	 if(CountryId==''){
		 return;
	 }
   $.ajax({
    type: 'POST',
    url: 'Ajax/AjPortDtls.php',
	//dataType: 'json',
    data: {'CountryId' : CountryId},  
    success: function(response){
		 //var myJSON = JSON.parse(JSON.stringify(response));
		// console.log(response);
		 $("#PortOfDischarge").html(response);
		// $("#PortOfDischarge").html(response);
		
		 
	 }
  });
 

     $.ajax({
    type: 'POST',
    url: 'Ajax/AjPortFinalDest.php',
	//dataType: 'json',
    data: {'CountryId' : CountryId},  
    success: function(response){
		 //var myJSON = JSON.parse(JSON.stringify(response));
		// console.log(response);
		// $("#PortOfDischarge").html(response);
		 $("#FinalDestination").html(response);
		
		 
	 }
  });
  });


    $('#ExporterID').change(function(){

	  getBankDetails();
	 
	});
	
	function getBankDetails(){
			 var ExpID= $('#ExporterID').val(); 
	    $.ajax({
    type: 'POST',
    url: 'Ajax/AjExpBankDetails.php',
	//dataType: 'json',
    data: {'ExpID' : ExpID},  
    success: function(response){
		 $("#BankID").html(response);		 
	 }
  });
	 
	}

</script>


<Script>

$('document').ready(function(){

  //alert('hiiiii');
    $('#ConsigneeID').on("keyup", function(){
        /* Get input value on change */
		alert('hiiii');
        var inputVal = $(this).val();
		
		//alert(inputVal);
		var resultDropdown = $(this).siblings(".result");
        if(inputVal.length){
            $.get("AjaxConsigneeSearch.php", {term: inputVal}).done(function(data){
                // Display the returned data in browser
				
				//console.log('test');
				console.log(data);
				//alert(data);
                resultDropdown.html(data);
            });
        } else{
            resultDropdown.empty();
        }
		
    });
	
	    // Set search input value on click of result item
$(document).on("click", ".result p", function(){
        var iid=$(this).attr('id');
//alert(iid);
$("#ConsigneeID").val(iid);
        $(this).parent(".result").empty();
         //alert(cat);
		$.ajax({
			type:'POST',
			url:'AjaxConsigneeDetails.php',
			data:{'id':iid},
		success:function(data){
			//alert(data);
			console.log(data);
var obj = JSON.parse(data);


var C_NAME=obj.ConsigneeName;
var C_ADD_id=obj.ConsigneeAddress;
var C_PORT_OF_DIS=obj.ConsigneePort;
var BUYER_COUNTRY=obj.ConsigneeCountry;
var BRAND_NAME=obj.ConsigneeBrand;
var SPECIAL_INST=obj.ConsigneeSplIns;
var FISHING_PMT_NO=obj.ConsigneeFishingNo;
alert(C_NAME);


$('#ConsigneeId').val(iid);
$('#ConsigneeAddress').val(C_NAME);
$('#ConsigneeBrand').val(BRAND_NAME); 
$('#ConsigneeCountry').val(BUYER_COUNTRY); 
$('#ConsigneePort').val(C_PORT_OF_DIS); 
$('#ConsigneeAddress').val(C_ADD_id); 
$('#ConsigneeSplIns').val(SPECIAL_INST);  
$('#ConsigneeFishingNo').val(FISHING_PMT_NO); 

		} 		
	});
$(this).parent(".result").empty();
        
});

});   	

</script>
</html>

