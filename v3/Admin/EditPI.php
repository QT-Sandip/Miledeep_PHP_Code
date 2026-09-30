<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


$PageTitleFolder="SalesNew";
$PageTitle="Proforma Invoice Edit";



 
 $POID=$_GET['PIID'];
 


	$sqlPIData="SELECT * FROM $TblScrmPIData  WHERE PoID='$POID'";
	$stmtPIData=$iconn->query($sqlPIData);
	$RowPIData=$stmtPIData->fetch_array();
	 
	 
 $SqlPayment="SELECT * FROM $TblScrmPoData 
 WHERE PoID='$POID'"; 
 $StmtPayment=$iconn->query($SqlPayment);
 $PoCount=mysqli_num_rows($StmtPayment);
$RowPaymentTerms = $StmtPayment->fetch_assoc();
if($PoCount!=0){
$PaymentTerms=$RowPaymentTerms['PaymentTerms'];	
}
else{
$PaymentTerms=$RowPIData['PaymentTerms'];	
	
}
//echo $PaymentTerms; exit;

	$SqlPaymentTerms="SELECT * FROM $TblScrmPaymentTerms";
	$StmtPaymentTerms=$iconn->query($SqlPaymentTerms);



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
	
	$PoRefId=$RowPIData['PoRefId'];
	$InvNo=$RowPIData['InvNo'];
	$InvDate=$RowPIData['InvDate'];
	$ExporterID=$RowPIData['ExporterID'];
	$ProcessorID=$RowPIData['ProcessorID'];
	$LotNo=$RowPIData['LotNo'];
	$SciName=$RowPIData['SciName'];
	$ConsigneeID=$RowPIData['ConsigneeID'];
	$BuyerID=$RowPIData['BuyerID'];
	/*$PoNo=$RowPIData['PoNo'];
	$PoDate=$RowPIData['PoDate'];*/
	$DestinationCountry=$RowPIData['DestinationCountry'];
	$TypeCountry=$RowPIData['TypeCountry'];
	$PoStatus=$RowPIData['PoStatus'];
	$YEAR=$RowPIData['Year'];
	$BankID=$RowPIData['BankID'];
	$PortOfDischarge=$RowPIData['PortOfDischarge'];
	$FinalDestination=$RowPIData['FinalDestination'];
	//$PaymentTerms=$RowPIData['PaymentTerms'];
	$ShippingTerms=$RowPIData['ShippingTerms'];
	$DeliveryTerms=$RowPIData['DeliveryTerms'];
	$PreparedBy=$RowPIData['PreparedBy'];
	$PRInstrctns=$RowPIData['PRInstrctns'];
	$ShipByDate=$RowPIData['ShipByDate'];
	
	

	$LoginId=$_SESSION["r_id"];
	
	
		
		  $sql_update="UPDATE `$TblScrmPIData` SET `PoNo`='$PoNo',`PoDate`='$PoDate',`Status`='5'
 WHERE PoID='$POID'";  
 if($PoCount==0){
	 $SqlPoUpdate="INSERT INTO `ScrmPoData`(`PoID`, `PoRefId`, `InvNo`, `InvDate`, `ExporterID`, `ProcessorID`, `LotNo`, `SciName`,
	  `ConsigneeID`, `BuyerID`, `PoNo`, `PoDate`, `DestinationCountry`, `TypeCountry`, `PoStatus`, `Year`, `BankID`
	  , `PortOfDischarge`, `FinalDestination`, `PaymentTerms`, `ShippingTerms`, `DeliveryTerms`, `PreparedBy`,
	  `PRInstrctns`, `ShipByDate`,  `Status`, `LoginId`, `ApprovedBy`, `Remarks`) 
	  VALUES ('$POID','$PoRefId','$InvNo','$InvDate','$ExporterID','$ProcessorID',
'$LotNo','$SciName','$ConsigneeID','$BuyerID','$PoNo','$PoDate','$DestinationCountry',
'$TypeCountry','$PoStatus','$YEAR','$BankID','$PortOfDischarge',
'$FinalDestination','$PaymentTerms','$ShippingTerms','$DeliveryTerms','$PreparedBy' ,'$PRInstrctns',
	 '$ShipByDate','5','$LoginId','$LoginId','$Remarks')";
   }
 
 else{
	$SqlPoUpdate="UPDATE `$TblScrmPoData` SET 
`PoNo`='$PoNo',`PoDate`='$PoDate',`Status`='5',`PaymentTerms`='$ByPayment' WHERE PoID ='$POID'";
  
 }
	$oldPiDataRow = legacyAuditFetchRowById($TblScrmPIData, 'PoID', $POID, 'Admin');
	$oldPoDataRow = null;
	if($PoCount>0){
		$oldPoDataRow = legacyAuditFetchRowById($TblScrmPoData, 'PoID', $POID, 'Admin');
	}
	$stmt1=$iconn->query($sql_update);
	$StmtPoUpdate=$iconn->query($SqlPoUpdate);
	$poInsertId = $iconn->insert_id;
	
	if($stmt1){
		legacyAuditLogUpdateById($TblScrmPIData, 'PoID', $POID, $oldPiDataRow, array(), array(), 'Admin');
		if($StmtPoUpdate){
			if($PoCount==0){
				legacyAuditLogInsertById('ScrmPoData', 'PoID', $POID, array(), array(), 'Admin');
			} else {
				legacyAuditLogUpdateById($TblScrmPoData, 'PoID', $POID, $oldPoDataRow, array(), array(), 'Admin');
			}
		}
		header('Location: PIList.php');
		
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
                  <label for="exampleInputPassword1"> P.O No</label>
                  <input  type="text" class="form-control" name='PoNo' value="<?php  echo $RowPIData['PoNo'];?>"  
				  id="PoNo" placeholder="Enter P.O Number">
                </div> 
				
				
				
				<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1"> P.O Date</label>
                  <input  type="date" value="<?PHP ECHO $RowPIData['PoDate']; ?>" 
				  class="form-control" name='PoDate' id="PoDate" placeholder="Enter P.O Date">
                </div>
				
			  <div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Payment Term</label>
                 
				   <select  required class="form-control select2" name="ByPayment" id="ByPayment">
				  <option value="">Select Payment</option>
				   <?php while ($RowPayment=$StmtPaymentTerms->fetch_array()){ ?>
				     <option 
				<?php	if($RowPayment['ID']==$PaymentTerms) { ?> selected=="selected" <?php } ?>
					 value="<?php echo $RowPayment['ID'] ?>">
					 <?php echo $RowPayment['PaymentName']?></option>
				   
				 <?php  }?>	  
				  </select>
                </div> 
                </div> 
			  	
				<div class="col-xs-12 box-footer"  align="right">
				<a target="" href="DocAddReports.php?PoID=<?php echo $POID ?>"
       class="btn btn-danger  "> Close</a>
  		
                <button type="submit"  name='submit' class="btn btn-primary">Submit</button>
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
</html>				

