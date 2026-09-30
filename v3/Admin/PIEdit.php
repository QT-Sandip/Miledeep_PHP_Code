

<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/SalesNew/Constants.php");


$PageTitleFolder="SalesNew";
$PageTitle="Proforma Invoice Edit";


 $POID=$_GET['id'];
 
 
$SQL_OPTIONS="SELECT * FROM `OPTIONS` WHERE  DEPT='SALES' order by ABS(OPTIONS) asc ";
$STMT_OPTIONS=$iconn->query($SQL_OPTIONS);
$ARY_OPTIONS=array();
WHILE($ROW=$STMT_OPTIONS->FETCH_ARRAY()){
	$ARY_OPTIONS[]=$ROW;
}

	$sqlPIData="SELECT * FROM $TblScrmPIData  WHERE PoID='$POID'";
	$stmtPIData=$iconn->query($sqlPIData);
	$RowPIData=$stmtPIData->fetch_array();
	 $Country=$RowPIData['DestinationCountry'];
	 $InvoiceNo=$RowPIData['InvNo'];


$sql_add_select="SELECT * FROM $TblAdminExporterDetails";
	$stmt_add_select=$iconn->query($sql_add_select);

	$SqlBuyer="SELECT * FROM $TblScrmConsigneeDetails";
	$StmtBuyer=$iconn->query($SqlBuyer);
	$StmtBuyer1=$iconn->query($SqlBuyer);
	
	$SqlBank="SELECT * FROM $TblAdminBankDetails";
	$StmtBank=$iconn->query($SqlBank);
	
    $SqlCountryList="SELECT * FROM $TblScrmCountriesList";
	$StmtCountryList=$iconn->query($SqlCountryList); 

	$SqlPaymentTerms="SELECT * FROM $TblScrmPaymentTerms";
	$StmtPaymentTerms=$iconn->query($SqlPaymentTerms);

$SqlAdminUsers="SELECT * FROM $TblAdminUserDetails WHERE DepartmentID=9 and PoUserStatus=1";
	$StmtAdminUsers=$iconn->query($SqlAdminUsers);
	
	 $SqlPorts="SELECT * FROM $TblScrmPortCities where Country='$Country'"; 
	$StmtPorts=$iconn->query($SqlPorts);
	
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

	$YEAR=date("Y");
	$PoStatus='0';
	
	

	$LoginId=$_SESSION["r_id"];
	
	/***uma(06/07/2022)**/
	
	
	 if($InvoiceNo==$InvNo){
	/***uma(06/07/2022)**/
		$oldPIData = legacyAuditFetchRowById($TblScrmPIData, 'PoID', $POID, 'Admin');
	$oldPoData = legacyAuditFetchRowById($TblScrmPoData, 'PoID', $POID, 'Admin');
		
		  $sql_update="UPDATE `$TblScrmPIData` SET `InvNo`='$InvNo',`InvDate`='$InvDate',
`ExporterID`='$ExporterID',`ProcessorID`='$ProcessorID',`LotNo`='$LotNo',`SciName`='$SciName',`ConsigneeID`='$ConsigneeID',
`BuyerID`='$BuyerID',`PoNo`='$PoNo',`PoDate`='$PoDate',`DestinationCountry`='$DestinationCountry',
`Year`='$YEAR',`BankID`='$BankID',`PortOfDischarge`='$PortOfDischarge',
`FinalDestination`='$FinalDestination',`PaymentTerms`='$ByPayment',`ShippingTerms`='$ShippingTerms',`DeliveryTerms`='$DeliveryTerms',
`PreparedBy`='$PreparedBy',`PRInstrctns`='$PRInstrctns',
`QtyUnit`='$QtyUnit',
`UnitCurrency`='$UnitCurrency',`PortOfDischarge`='$PortOfDischarge',`ShipByDate`='$ShipByDate'
 WHERE PoID='$POID'";  
 
  $SqlPoUpdate="UPDATE `$TblScrmPoData` SET 
 `InvNo`='$InvNo',`InvDate`='$InvDate',
 `ExporterID`='$ExporterID',`ProcessorID`='$ProcessorID',
 `LotNo`='$LotNo',`SciName`='$SciName',`ConsigneeID`='$ConsigneeID',
 `BuyerID`='$BuyerID',`PoNo`='$PoNo',`PoDate`='$PoDate',
 `DestinationCountry`='$DestinationCountry',
 `Year`='$YEAR',`BankID`='$BankID',`PortOfDischarge`='$PortOfDischarge',
 `FinalDestination`='$FinalDestination',`PaymentTerms`='$ByPayment',`ShippingTerms`='$ShippingTerms',
 `DeliveryTerms`='$DeliveryTerms',`PreparedBy`='$PreparedBy',`PRInstrctns`='$PRInstrctns',
 `ShipByDate`='$ShipByDate' WHERE PoID ='$POID'";



	$stmt1=$iconn->query($sql_update);
	$StmtPoUpdate=$iconn->query($SqlPoUpdate);
	
	
	//$INV_ID = $iconn->insert_id;
	
	
	
	

	if($stmt1){
		legacyAuditLogUpdateById($TblScrmPIData, 'PoID', $POID, $oldPIData, [], [], 'Admin');
		legacyAuditLogUpdateById($TblScrmPoData, 'PoID', $POID, $oldPoData, [], [], 'Admin');

		//header('Location: PoBreakUp.php?id='.$INV_ID);
		//header('Location: PIList.php');
		echo '<script language="javascript" type="text/javascript"> 
                window.location = "PIList.php";
        </script>';
	
	}
}

else{
	$StartYear=date('Y',strtotime($InvDate));
	//echo $StartMonth=date('Y-m',strtotime($InvDate)); exit;
			  $EndYear=$StartYear+1;
			  $FinStartDate=$StartYear.'-04-01'; 
			  $FinEndDate=$EndYear.'-03-31';
			//uma(21/04/2022)//
	 
	//$sqlCount="SELECT count(*) as Count FROM `$TblScrmPIData` where InvNo='$InvNo' and ExporterID='$ExporterID'";
	if($ExporterID==5){
	$sqlCount="SELECT count(*) as Count FROM `$TblScrmPIData` where InvDate BETWEEN '$FinStartDate' and '$FinEndDate' and
	InvNo='$InvNo' and ExporterID='$ExporterID'";
	}
	else{
	$sqlCount="SELECT count(*) as Count FROM `$TblScrmPIData` where InvDate BETWEEN '$FinStartDate' and '$FinEndDate' and
	InvNo='$InvNo' and ExporterID BETWEEN '1' and '3'";	
	}
	// echo $sqlCount; exit;
			$StmtCount=$iconn->query($sqlCount);
			$RowCount=$StmtCount->fetch_array();
	if($RowCount['Count']!=0){		
	 //echo '<script> alert("Update Failed  Invoice Number already exists")</script>';
echo '<script language="javascript" type="text/javascript"> 
alert("Update Failed  Invoice Number already exists");
                window.location = "PIList.php";
        </script>';
	}
	else{
		$oldPIData = legacyAuditFetchRowById($TblScrmPIData, 'PoID', $POID, 'Admin');
	$oldPoData = legacyAuditFetchRowById($TblScrmPoData, 'PoID', $POID, 'Admin');
		$sql_update="UPDATE `$TblScrmPIData` SET `InvNo`='$InvNo',`InvDate`='$InvDate',
`ExporterID`='$ExporterID',`ProcessorID`='$ProcessorID',`LotNo`='$LotNo',`SciName`='$SciName',`ConsigneeID`='$ConsigneeID',
`BuyerID`='$BuyerID',`PoNo`='$PoNo',`PoDate`='$PoDate',`DestinationCountry`='$DestinationCountry',
`Year`='$YEAR',`BankID`='$BankID',`PortOfDischarge`='$PortOfDischarge',
`FinalDestination`='$FinalDestination',`PaymentTerms`='$ByPayment',`ShippingTerms`='$ShippingTerms',`DeliveryTerms`='$DeliveryTerms',
`PreparedBy`='$PreparedBy',`PRInstrctns`='$PRInstrctns',
`QtyUnit`='$QtyUnit',
`UnitCurrency`='$UnitCurrency',`PortOfDischarge`='$PortOfDischarge',`ShipByDate`='$ShipByDate'
 WHERE PoID='$POID'";  
 
  $SqlPoUpdate="UPDATE `$TblScrmPoData` SET 
 `InvNo`='$InvNo',`InvDate`='$InvDate',
 `ExporterID`='$ExporterID',`ProcessorID`='$ProcessorID',
 `LotNo`='$LotNo',`SciName`='$SciName',`ConsigneeID`='$ConsigneeID',
 `BuyerID`='$BuyerID',`PoNo`='$PoNo',`PoDate`='$PoDate',
 `DestinationCountry`='$DestinationCountry',
 `Year`='$YEAR',`BankID`='$BankID',`PortOfDischarge`='$PortOfDischarge',
 `FinalDestination`='$FinalDestination',`PaymentTerms`='$ByPayment',`ShippingTerms`='$ShippingTerms',
 `DeliveryTerms`='$DeliveryTerms',`PreparedBy`='$PreparedBy',`PRInstrctns`='$PRInstrctns',
 `ShipByDate`='$ShipByDate' WHERE PoID ='$POID'";



	$stmt1=$iconn->query($sql_update);
	$StmtPoUpdate=$iconn->query($SqlPoUpdate);
	
	
	//$INV_ID = $iconn->insert_id;
	
	
	
	

	if($stmt1){
		legacyAuditLogUpdateById($TblScrmPIData, 'PoID', $POID, $oldPIData, [], [], 'Admin');
		legacyAuditLogUpdateById($TblScrmPoData, 'PoID', $POID, $oldPoData, [], [], 'Admin');

		//header('Location: PoBreakUp.php?id='.$INV_ID);
		//header('Location: PIList.php');
	echo '<script language="javascript" type="text/javascript"> 
                window.location = "PIList.php";
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
                  <label for="exampleInputPassword1">Select Processor</label>              			   
				  <select class="form-control select2" name="ProcessorID" id="ProcessorID">
				 
				<?php 
				$ProcessorID=$RowPIData['ProcessorID'];
				
				while($row_add=$stmt_add_select->fetch_array()) { 
				
				$ary_exp[]=$row_add;?>  
				
				<option <?php if($ProcessorID==$row_add['ExpID']){ ?> selected="selected"  <?php } ?>
				
				Value="<?php echo $row_add['ExpID'];?>"><?php echo $row_add['ExpName'].'('.$row_add['ExpApprovalNo'].','.$row_add['ExpDetails'].')';?></OPTION>
				
				<?php } ?>
				
				<select>   
                </div>
				
				
				 <div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Select Exporter</label>
               
				   <select class="form-control select2" name="ExporterID" id="ExporterID">
				  <?php 
				  $ExporterID=$RowPIData['ExporterID'];
				  foreach($ary_exp as $value ) { ?>  
				
				<option  <?php if($ExporterID==$value['ExpID']){ ?> selected="selected"  <?php } ?>
				
				Value="<?php echo $value['ExpID'];?>"><?php echo $value['ExpName'].'('.$value['ExpApprovalNo'].','.$value['ExpDetails'].')';?></OPTION>
				
				<?php } ?>
				<select>   
                </div>
				
				
				
				

		 
	
				
		
			  
			   <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Proforma Invoice No</label>
                  <input required  type="number" class="form-control" name='InvNo' 
				  id="InvNo" placeholder="Enter Proforma Invoice No" value="<?php echo $RowPIData['InvNo']; ?>">
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Proforma Invoice Date</label>
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
                  <label for="exampleInputPassword1">Bank Details</label>
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
		         
				<!--<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Scientific Name</label>
				  <select class="form-control select2" name="SciName" id="SciName">
				  <option value="PENAEUS VANNAMEI" <?php if($RowPIData['SciName']=='PENAEUS VANNAMEI')
					  echo 'selected="selected"'; ?>>PENAEUS VANNAMEI</option>
				   <option value="LITOPENAEUS VANNAMEI" <?php if($RowPIData['SciName']=='LITOPENAEUS VANNAMEI')
					  echo 'selected="selected"'; ?>>LITOPENAEUS VANNAMEI</option>
					 
		
				<select>               
                  
                </div>-->
				
				 <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Scientific Name</label>
                 
                <select class="form-control select2" name="SciName" id="SciName">
					 <?php 
					 $SciName=$RowPIData['SciName'];
					 foreach($ARY_OPTIONS as $value){
					if($value['TYPE']=='SCIENTIFIC NAME'){
						$OPTIONS= $value['OPTIONS'];
						$VALUE= $value['VALUE']; ?>
			 
					<option  <?php if($SciName==$VALUE){ ?> selected="selected"  <?php } ?>
					
					value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
					<?php  }}?>
				<select>
				
				</div>
				
				
			
				
			
		  <div class="form-group col-lg-3">  
                  <label for="exampleInputPassword1">Buyer Name</label>
                   <select  required class="form-control select2" name="BuyerID" id="BuyerID">
				  <option value="">Select Buyer</option>
				   <?php 
				   $BuyerID=$RowPIData['BuyerID'];
				   while ($Row_PO=$StmtBuyer1->fetch_array()){ ?>
				     <option <?php if($BuyerID==$Row_PO['ConID']){ ?> selected="selected"  <?php } ?>
					 
					 value="<?php echo $Row_PO['ConID'] ?>">
					 <?php echo $Row_PO['ConsigneeName'].' - 
					 '.$Row_PO['ConsigneeBrand'] ?></option>
				   
				 <?php  }?>	  
				  </select>
                </div>


			   
			   <div class="form-group col-lg-3">  
                  <label for="exampleInputPassword1">Applicant/Ultimate Consignee</label>
                   <select  required class="form-control select2" name="ConsigneeID" id="ConsigneeID">
				  <option value="">Select Consignee</option>
				   <?php $ConsigneeID=$RowPIData['ConsigneeID'];
				   while ($Row_PO=$StmtBuyer->fetch_array()){ ?>
				   
				     <option <?php if($ConsigneeID==$Row_PO['ConID']){ ?> selected="selected"  <?php } ?>
					 
					 value="<?php echo $Row_PO['ConID'] ?>">
					 <?php echo $Row_PO['ConsigneeName'].' - 
					 '.$Row_PO['ConsigneeBrand'] ?></option>
				   
				 <?php  }?>	  
				  </select>
                </div>
				

		 
				
				
				
				 <div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Destination Country</label>
                 
				  
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
				
				  <div class="form-group col-lg-3" >              
					 
					 
                  <label for="exampleInputEmail1">Destination</label>
               <select class="form-control select2" name="TypeCountry" id="TypeCountry">
					<?php $TypeCountry=$RowPIData['TypeCountry']; ?>
					
					<option <?php if($TypeCountry==1){ ?> selected="selected"  <?php } ?>
					
					value="1">NON-EU</option>
					<option <?php if($TypeCountry==2){ ?> selected="selected"  <?php } ?>
					value="2" >EU</option>
		
				<select>
                </div>
				
				
		
				
			<div class="form-group col-lg-3">
			 <label for="exampleInputPassword1">Port of Discharge</label>
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
		
			
				
				<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Payment Term</label>
                 
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

			
				
					
				<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1"> Final Destination</label>
                  <input required type="text" class="form-control" name='FinalDestination' 
				  id="FinalDestination" placeholder="Enter Final Destination" value="<?php echo $RowPIData['FinalDestination'];?>">
                </div> 
				
					
				
				
              <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Shipment Terms</label>
                 
                <select class="form-control select2" name="ShippingTerms" id="ShippingTerms">
					 <?php 
					 $ShippingTerms=$RowPIData['ShippingTerms'];
					 foreach($ARY_OPTIONS as $value){
					if($value['TYPE']=='PAYMENT_TERMS'){
						$OPTIONS= $value['OPTIONS'];
						$VALUE= $value['VALUE']; ?>
			 
					<option  <?php if($ShippingTerms==$VALUE){ ?> selected="selected"  <?php } ?>
					
					value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
					<?php  }}?>
				<select>
				
				</div>
				
				<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">ShipBy Date</label>
                  <input    type="date" class="form-control" name="ShipByDate"
				  id="ShipByDate" placeholder="Enter ShipBy Date" Value="<?php echo $RowPIData['ShipByDate']; ;?>"
				 ></input>
                </div> 
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Delivery Terms</label>
				  
				   <input required type="text" class="form-control" name='DeliveryTerms' 
				  id="DeliveryTerms" placeholder="Enter Delivery Term" VALUE="<?PHP ECHO $RowPIData['DeliveryTerms']; ?>">
                 
             
				
				</div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Quantity Units</label>
                 
                <select class="form-control select2" name="QtyUnit" id="QtyUnit">
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
                  <label for="exampleInputPassword1">Currency</label>
				  <select  required class="form-control select2" name="UnitCurrency" id="UnitCurrency">
				  
				  <?PHP $UnitCurrency=$RowPIData['UnitCurrency']; ?>
                  <option value="">Select Currency</option>
                  <option <?php if($UnitCurrency=="Dollar"){ ?> selected="selected"  <?php } ?>
				  value="Dollar">US Dollar</option>
				  </select>
                </div>
				
				
				
				
				
				
				
				<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1"> Prepared By</label>
                 
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
				</div> 
				</div> 
				
				<div class="col-xs-12 box-footer"  align="right">
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
	  $("#ConsigneeID").select2();
	  $("#BuyerID").select2();
	  $("#DestinationCountry").select2();
	  $("#PortOfDischarge").select2();

    $("#file").change(function() {
        alert('changed!');
    });
	
	

	
});
  	

</script>


<script>

$("document").ready(function(){
	  $("#ConsigneeID").select2();
	  $("#PortOfDischarge").select2();
});

$('#ConsigneeID').change(function(){
	 var BuyerId= $('#ConsigneeID').val(); 
	 if(BuyerId==''){
		 return;
	 }
   $.ajax({
    type: 'POST',
    url: 'ajax/AjBuyerDtls.php',
	dataType: 'json',
    data: {'BUYERID' : BuyerId},  
    success: function(response){
		 var myJSON = JSON.parse(JSON.stringify(response));
		
		 $("#BRAND").val(myJSON['ConsigneeBrand']);
		 $("#DestinationCountry").val(myJSON['ConsigneeCountry']);
		 $("#PORT_OF_DIS").val(myJSON['ConsigneePort']);
		 $("#ADDRESS_OF_CONSIGNEE").val(myJSON['ConsigneeAddress']);
		 $("#FISHING_PMT_NO").val(myJSON['ConsigneeFishingNo']);
		 
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
    url: 'ajax/AjPortDtls.php',
	//dataType: 'json',
    data: {'CountryId' : CountryId},  
    success: function(response){
		 //var myJSON = JSON.parse(JSON.stringify(response));
		// console.log(response);
		 $("#PortOfDischarge").html(response);
		// $("#PortOfDischarge").html(response);
		
		 
	 }
  });
  });

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

