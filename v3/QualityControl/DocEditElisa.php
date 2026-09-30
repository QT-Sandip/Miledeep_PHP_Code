<?php
session_start();
$QueryParms=$_GET['Q']; 
$QueryParm=$_GET['Q']; 


 
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");


$ReferenceId=$_GET['RID'];
$PoID=$_GET['PoID'];

$sql_invoice="SELECT * FROM `$TblScrmPoData` where PoID='$PoID'";
$stmt_invoice=$iconn->query($sql_invoice);
$invoice_details=$stmt_invoice->fetch_array();


$SqlLab="SELECT * FROM `$TblQcLabTechnologist`";
$StmtLab=$iconn->query($SqlLab);
$StmtLab1=$iconn->query($SqlLab);




$SqlReport="SELECT * FROM `$TblQcInHouseAbReport` where ID='$ReferenceId'";

$StmtReport=$iconn->query($SqlReport);

$RowReportDetails=$StmtReport->fetch_array();


  
$SqlCodeList="SELECT * FROM `$TblQcCodeList` as cl LEFT JOIN $TblQcTracebilityList as 
 trace on trace.TraceID=cl.TracebilityID WHERE InvID='$PoID'"; 
$StmtCodeList=$iconn->query($SqlCodeList); 




$PageTitle="Edit Elisa Test Report  - ".$RowPo['PoNo'];
$PageTitleFolder="Quality Control";





extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	
	$SAMPLE_NAME=$SAMPLE_NAME.'('.$TREATE_MENT .')';
	
	
	
	  $sql_update="UPDATE `$TblQcInHouseAbReport` SET 
	`ReportNo`='$ReportNo',
	`ReportDate`='$ReportDate',
	`TracebilityID`='$TracebilityID',
	`SamplingDate`='$SamplingDate',
	`SampleQty`='$SampleQty',
	`Representation`='$Representation',`SampleID`='$SampleID',`AnalysisDate`='$AnalysisDate',
	`IssuanceDate`='$IssuanceDate',`TestedBy`='$TestedBy',`VerifiedBy`='$VerifiedBy',`InvID`='$PoID' WHERE ID='$ReferenceId'"; 
	
	
	//echo  $sql_update;

	$stmt1=$iconn->query($sql_update);


	if($stmt1){
		
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
			     <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Test Report No</label>
                  <input type="text" class="form-control" name='ReportNo' id="ReportNo" 
				  placeholder="Enter Test Report No" value="<?php echo $RowReportDetails['ReportNo']?>">
                </div>
                
				
				 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Test Report Date</label>

                  <input type="date" class="form-control" name='ReportDate' id="ReportDate"
				  placeholder="Enter Test Report Date" value="<?php echo $RowReportDetails['ReportDate']?>">
                </div>
				
				
				
				 <div class="form-group col-sm-3" >
                  <label for="exampleInputPassword1">Farm Registration No</label>
				 <select class="form-control select2" name="TracebilityID" id="TracebilityID">				  
				  <?php while($code_list_row=$StmtCodeList->fetch_array()){
					 $CODE_LIST_ID=$code_list_row['TracebilityID'];
					 
					  $FARM_REG_ID=$RowReportDetails['TracebilityID'];
					 
					 
					  ?>
					<option value="<?php echo $CODE_LIST_ID?>" <?php if($FARM_REG_ID==$CODE_LIST_ID){?>
					selected="selected" <?php } ?>><?php echo $code_list_row['Tracebility']?></option>
					 <?php }  ?>
					<select>
				
				</div>
				
               <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Sample Quantity</label>
                  <input required type="text" class="form-control" name='SampleQty' id="SampleQty"
				  placeholder="Enter Sample Quantity" value="<?php echo $RowReportDetails['SampleQty']?>">
                </div>
			   
                <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Date of Sampling</label>
               <input type="datetime-local" class="form-control" name='SamplingDate' id="SamplingDate" 
			   placeholder="Enter Date of Sampling" value="<?php echo $RowReportDetails['SamplingDate']?>">
   
                </div>
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Name of the Representation/Technologist </label>
                   <input type="text" class="form-control" name='Representation' id="Representation"
				   placeholder="Enter name of Technologist" value="<?php echo $RowReportDetails['Representation']?>">
				   </div>
				
				 <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Lab Sample ID</label>
                  <input type="text" class="form-control" name='SampleID' id="SampleID" 
				  placeholder="Enter Lab Sample ID" value="<?php echo $RowReportDetails['SampleID']?>">
                </div>
				
				
                <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Date of Analysis</label>
                  <input type="date" class="form-control"  name="AnalysisDate" id="AnalysisDate"
				  placeholder="Enter Date Of Analysis(dd/mm/yyyy)" value="<?php echo $RowReportDetails['AnalysisDate']?>">
                </div>
                <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Date of Issuance </label>
                   <input type="date" class="form-control" name='IssuanceDate' id="IssuanceDate" 
				   placeholder="Enter Date of Issuance(dd/mm/yyyy)" value="<?php echo $RowReportDetails['IssuanceDate']?>">
   
               
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Tested By</label>
				  
 <select class="form-control select2" name="TestedBy" id="TestedBy">
				  <?php while ($RowLab=$StmtLab->fetch_array()){ ?>
				     <option <?php if($RowReportDetails['TestedBy']==$RowLab['LbuName']){ ?> selected="selected" <?php } ?>value="<?php echo $RowLab['LbuName'] ?>"><?php echo $RowLab['LbuName'] ?></option>
				   
				 <?php  }?>			  
				  </select>
				  
                   
               
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Verified By </label>
				  
				  <select class="form-control select2" name="VerifiedBy" id="VerifiedBy">
				  <?php while ($RowLab=$StmtLab1->fetch_array()){ ?>
				     <option <?php if($RowReportDetails['VerifiedBy']==$RowLab['LbuName']){ ?> selected="selected" <?php } ?>value="<?php echo $RowLab['LbuName'] ?>"><?php echo $RowLab['LbuName'] ?></option>
				   
				 <?php  }?>			  
				  </select>
				  
				  
                   
               
                </div>
                	</div>
              
					

			  <div class="col-xs-12 box-footer"  align="right">
			  			   <a  href="DocAddReports.php?Q=<?php echo $QueryParm ?>" class="btn btn-danger">Close</a>

                <button type="submit"  name="submit"   class="btn btn-primary waves-effect waves-light">
				
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

