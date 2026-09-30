<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


$PageTitleFolder="SalesNew";
$PageTitle="Bill of Exchange(LC) Creation";


$PIId=$_GET['PIID'];


	/*$SqlBank="SELECT * FROM $TblAdminBankDetails";
	$StmtBank=$iconn->query($SqlBank);
	$StmtBank1=$iconn->query($SqlBank);*/



		 $SqlCInvoice="SELECT CInvNo,ConsigneeName,ConsigneeAddress 
		FROM `$ScrmCustomInvoices` as SCI
left join $TblScrmPIData as SPD	on SPD.PoID=SCI.PiID	
left join $TblScrmPaymentTerms as SPT	on SPT.ID=SPD.PaymentTerms	
left join $TblScrmConsigneeDetails as SCD on SCD.ConID=SPD.BuyerID	
		
		WHERE PiID='$PIId'"; 
		$StmtCInvoice=$iconn->query($SqlCInvoice);
		$RowCInvoice=$StmtCInvoice->fetch_array();
		
				$ConsigneeAddress=$RowCInvoice['ConsigneeAddress'];
						 $Status=$RowCInvoice['LcStatus']; 


		
		$SqlCount="SELECT * FROM `$ScrmBillOfExchange` WHERE PIID='$PIId'";
		$StmtCount=$iconn->query($SqlCount);
		$Count=mysqli_num_rows($StmtCount);				
		$RowBillofExchage=$StmtCount->fetch_array();
		 

extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	  if($Count==0){
        $SqlInsert="INSERT INTO `$ScrmBillOfExchange`(`PIID`,`CreditNo`,`CreditDate`,`DraftDate`,`IssuedBy`,`ToAddress`,`Status`) 
		VALUES  ('$PIId','$CreditNo','$CreditDate','$DraftDate','$IssuedBy','$ToAddress','$Status')"; 
		$StmtInsert=$iconn->query($SqlInsert);
	
	if($StmtInsert){
		legacyAuditLogInsertById($ScrmBillOfExchange, 'PIID', $PIId, $_POST, ['Remarks' => 'Bill of exchange created'], 'Admin');
	echo '<script language="javascript" type="text/javascript"> 
                alert("Success");
                window.location = "DocAddReports.php?PoID='.$PIId.'";
        </script>';
	}	 
			 
			 }
			 else{
			$oldBillData = legacyAuditFetchRowById($ScrmBillOfExchange, 'PIID', $PIId, 'Admin');
			$SqlUpdate= "UPDATE `$ScrmBillOfExchange` SET `CreditNo`='$CreditNo',`CreditDate`='$CreditDate',`DraftDate`='$DraftDate',`IssuedBy`='$IssuedBy',`ToAddress`='$ToAddress' WHERE `PIID`='$PIId'";
			 $StmtUpdate=$iconn->query($SqlUpdate);
			 
	
	if($StmtUpdate){
		legacyAuditLogUpdateById($ScrmBillOfExchange, 'PIID', $PIId, $oldBillData, $_POST, ['Remarks' => 'Bill of exchange updated'], 'Admin');
			
			echo '<script language="javascript" type="text/javascript"> 
                alert("Success");
                window.location = "DocAddReports.php?PoID='.$PIId.'";
        </script>';
	
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
					


                        <?php include ($IncludesPath."page-title.php");?>

	
          
	
<div class="row">
                            <div class="col-12">
                                <div class="card">
								
								<form role="form" method="post">
                                    <div class="card-body">
									

    						 <?php
	if($Status!=1){ ?>
											<div class="row">

           <div class="form-group col-sm-12" >
				<?php echo '<span style="color: red; font-size: 14px;">'.'* Fileds are disabled because  Non Lc payment mode is selected  in Proforma invoice</span>';?>
                </div>
                </div>
					
	 
<?php } ?>
                 	
														<div class="row">

				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Custom Inv No</label>
				<input readonly type="text" class="form-control" name='CInvNo' id="CInvNo" 
				   value="<?php echo 	$RowCInvoice['CInvNo'];?>">		
		
                </div>
				
				
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Credit No</label>
				<input  type="text" class="form-control" name='CreditNo' id="CreditNo" 
				  placeholder="Enter Credit No" value="<?php echo $RowBillofExchage['CreditNo']?>">		
		
                </div>	
				
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Credit Date</label>
				<input  type="date" class="form-control" name='CreditDate' id="CreditDate" 
				  placeholder="Enter Credit Date" value="<?php echo $RowBillofExchage['CreditDate']?>">		
		
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Draft Date</label>
				<input  type="date" class="form-control" name='DraftDate' id="DraftDate" 
				  placeholder="Enter Draft Date" value="<?php echo $RowBillofExchage['DraftDate']?>">		
		
                </div>
				<?php  if($Status==1){ ?>
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">To Address(LC)</label>
				<input required type="text" class="form-control" name='ToAddress' id="ToAddress" 
				  placeholder="Enter , for next line" value="<?php echo $RowBillofExchage['ToAddress']?>">		
		
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Issued By</label>
<input  required type="text" class="form-control" name='IssuedBy' id="IssuedBy" 
				  placeholder="Enter , for next line " value="<?php echo $RowBillofExchage['IssuedBy']?>">
                </div>
				<?php } else{ ?> 
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">To Address(NON-LC)</label>
				<input readonly type="text" class="form-control" name='ToAddress' id="ToAddress" 
				  placeholder="Enter , for next line" value="<?php echo $ConsigneeAddress?>">		
		
                </div>
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Issued By</label>
<input  readonly type="text" class="form-control" name='IssuedBy' id="IssuedBy" 
				  placeholder="Enter , for next line " value="">
                </div>
				
				<?php } ?> 
				
				</div>

					
				
					<?php if($Count==0){	 ?>
			  <div class="col-xs-12 box-footer"  align="right">
			  <a target="" href="DocAddReports.php?PoID=<?php echo $PIId ?>"
       class="btn btn-danger  "> Close</a>
  		
                <button type="submit"  name='submit' class="btn btn-primary">Add</button>

              </div>
					<?php } else{	 ?>
					
			  <div class="col-xs-12 box-footer"  align="right">
			  <a target="" href="DocAddReports.php?PoID=<?php echo $PIId ?>"
       class="btn btn-danger  "> Close</a>
  		
                <button type="submit"  name='submit' class="btn btn-primary">Update</button>

	
								 


              </div>
					<?php } ?>
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

