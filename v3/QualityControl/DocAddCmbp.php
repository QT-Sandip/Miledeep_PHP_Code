<?php
 $QueryParms=$_GET['Q']; 
 $QueryParm=$_GET['Q']; 
 
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");


$ClientID=$_SESSION["ClientID"];

$REFERENCE_ID=$_GET['id'];


if($QueryParms!=''){
  $QueryParms=urlDecode64($QueryParms);  
  $queries = array();
  parse_str($QueryParms, $_GET);
  }

$PoID=$_GET['PoID'];

$SqlPo="SELECT * FROM  $TblScrmPoData   WHERE PoID='$PoID'"; 

$StmtPo=$iconn->query($SqlPo);

$RowPo=$StmtPo->fetch_array();


$PageTitle="IN-HOUSE TEST REPORT FOR MICROBIOLOGY - ".$RowPo['PoNo'];
$PageTitleFolder="Quality Control";


$SqlCodeList="SELECT * FROM $TblQcCodeList  
left join $TblQcTracebilityList  on $TblQcCodeList.TracebilityID= $TblQcTracebilityList.TraceID where InvID='$PoID'";
$StmtCodeList=$iconn->query($SqlCodeList);

/*$SqlVarieties="SELECT * FROM $TblQcPoVarityList";
$StmtVarieties=$iconn->query($SqlVarieties);
$StmtVarieties1=$iconn->query($SqlVarieties);*/


$SqlLabUsers="SELECT * FROM $TblQcLabTechnologist ";
$StmtLabUsers=$iconn->query($SqlLabUsers);
$StmtLabUsers1=$iconn->query($SqlLabUsers);

//$InvID=$RowShipment['InvID'];


extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	
	if($PoID==''){
		$PoID=0;
	}
	
	$PRODUCT_SAMPLE_NAME=$PRODUCT_SAMPLE_NAME.'('.$TREATE_MENT .')';

	
	
	 $sql_org_insert="INSERT INTO $TblQcIHMBReport
	 (`ReportNo`, `ReportDate`, `DateSampling`, `Representation`
	 , `LabSampleID`, `DateAnalysis`, `DateIssuence`, `TestedBy`
	 , `VerifiedBy`, `InvID`, `FarmRegNo`,`TPCvalue`) 
	 VALUES ('$ReportNo','$ReportDate','$DateSampling',
	 '$Representation','$LabSampleID','$DateAnalysis',
	 '$DateIssuence','$TestedBy','$VerifiedBy',
	 '$PoID','$FarmRegNo','$TPCvalue')";
	
	

	$stmt1=$iconn->query($sql_org_insert);
	$M_T_REPORT_ID = $iconn->insert_id;
//echo $iid;
	if($stmt1){
		legacyAuditLogInsertById('QcIHMBReport', 'ReportID', $M_T_REPORT_ID, $_POST, ['Remarks' => 'Created IHMB Report'], 'QualityControl');
		
	

	/*$sql_org_insert="INSERT INTO `IH_MICROBIOLOGY_TEST_RESULT`(
							  `M_T_REPORT_ID`, `P_CODES_REG_NUM`, `DOA_DOC`,`VARITY`, `T_P_C`, `E_COLI`,
							 `STAPHYLO_COCCUS`, `V_CHOLERAE`, `V_PARAHA_EMOLYTICU`, `SALMOELLA`, `A_REPORT`, `IS_EDITED`
					) VALUES (
	
	'$M_T_REPORT_ID','$P_CODES_REG_NUM','$DOA_DOC','$VARITY','$T_P_C',
	'$E_COLI','$STAPHYLO_COCCUS','$V_CHOLERAE','$V_PARAHA_EMOLYTICU',
	'$SALMOELLA','$A_REPORT','0')";
	
	

	$stmt1=$iconn->query($sql_org_insert);*/						
		
	header('Location: DocAddReports.php?Q='.$QueryParm);
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
									
										<div class="row">
			 <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Test Report No</label>
                  <input required type="text" class="form-control" name='ReportNo' id="ReportNo" 
				  placeholder="Enter Test Report No" value="<?php echo $invoice_details['TEST_REPORT_NUMBER']?>">
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Test Report Date</label>
                  <input required type="date"value="<?php  echo date("Y-m-d");?>"class="form-control" name='ReportDate' id="ReportDate"
				  placeholder="Enter Test Report Date" value="<?php echo $invoice_details['TEST_REPORT_DATE']?>">
                </div>
               
			   
                
				
				 <div class="form-group col-lg-3" >
                 <label for="exampleInputEmail1">Farm Registration No</label>               				  
				 <select class="form-control select2" name="FarmRegNo" id="FarmRegNo">				
				<?php while($row=$StmtCodeList->fetch_array()){ ?>				
				<option value="<?php echo $row['TraceID'];?>"><?php echo $row['Tracebility'];?></option> 
				<?php } ?>								
				</select>	
                </div>
				
 		
				
				
                <div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Date Of Sampling</label>
                  <input required type="date" value="<?php  echo date("Y-m-d");?>"class="form-control"  name="DateSampling" id="DateSampling" 
				  placeholder="Enter Date Of Sampling " value="<?php echo $invoice_details['Representation']?>">
                </div>
                <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Name of the Technologist</label>
                   
				   <input required type="text" class="form-control"  name="Representation" id="Representation"
				   placeholder="Enter Name - Designation " value="<?php echo $invoice_details['TEST_REPORT_NUMBER']?>">
                
				 
               
                </div>
                <div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Lab Sample ID</label>
                  <input required type="text" class="form-control"  name="LabSampleID" id="LabSampleID"
				  placeholder="Enter Lab Sample ID" value="<?php echo $invoice_details['LabSampleID']?>">
                </div>
				
				
                <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Date Of Analysis</label>
                  <input required type="date"value="<?php  echo date("Y-m-d");?>" class="form-control" name="DateAnalysis" id="DateAnalysis" 
				  placeholder="Enter Date Of Analysis" value="<?php echo $invoice_details['DATE_OF_ANALYSIS']?>">
                </div>
                <div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Date of Completion</label>
                  <input required type="date" value="<?php  echo date("Y-m-d");?>"class="form-control"  name="DateIssuence" id="DateIssuence" 
				   placeholder="Enter Date of Issuance" value="<?php echo $invoice_details['DATE_OF_ISSUANCE']?>">
                </div>
				
				 
				
				
				<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Tested By</label>
					<select required type="text" class="form-control select2" name='TestedBy' id="TestedBy">
					<?php while ($RowLabUsers=$StmtLabUsers->fetch_array()){ ?>
				     <option value="<?php echo $RowLabUsers['LbuName'] ?>"><?php echo $RowLabUsers['LbuName'] ?></option>
				   
				 <?php  }?>
				</select>
                </div>
				
                <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Verified By</label>
				   <select class="form-control select2" name="VerifiedBy" id="VerifiedBy">				   
				    <?php while ($RowLabUsers=$StmtLabUsers1->fetch_array()){ ?>
				     <option value="<?php echo $RowLabUsers['LbuName'] ?>"><?php echo $RowLabUsers['LbuName'] ?></option>
					 
				 <?php  }?>
				</select>               
                </div>
			 
			 <div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">TPC</label>
                  <input required type="text" class="form-control"  name="TPCvalue" id="TPCvalue"
				  placeholder="Enter TPC value" >
                </div>
 	</div>
              
					

			  <div class="col-xs-12 box-footer"  align="right">
			  			   <a  href="DocAddReports.php?Q=<?php echo $QueryParm ?>" class="btn btn-danger">Close</a>

                <button type="submit"  name="submit"   class="btn btn-success waves-effect waves-light">
				
				Submit</button>
              </div>
			  </form>

</div>
</div>
        
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

