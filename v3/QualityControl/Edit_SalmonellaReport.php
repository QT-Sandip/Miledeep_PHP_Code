<?php
 $QueryParms=$_GET['Q']; 
  $QueryParm=$_GET['Q']; 
 include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");



$PoID=$_GET['PoID'];

$REFERENCE_ID=$_GET['RID'];

$SqlPo="SELECT * FROM  ScrmPoData   WHERE PoID='$PoID'"; 

$StmtPo=$iconn->query($SqlPo);

$RowPo=$StmtPo->fetch_array();


$sqlLabUser="SELECT * FROM QcLabTechnologist WHERE LbuStatus=1";
$stmtLabUser=$iconn->query($sqlLabUser);
$stmtLabtester=$iconn->query($sqlLabUser);



 $sql="SELECT * FROM `QcSalmonellaReport` where ID='$REFERENCE_ID'"; 
$stmt=$iconn->query($sql);
$post_details=$stmt->fetch_array();
 $ReportNo=explode('&',$post_details['LabSampleID']);
 $date=$ReportNo[1];  


$PageTitle=" Edit Salmonella Test Report - USA- ".$RowPo['PoNo'];
$PageTitleFolder="Quality Control";




extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	
		
		//$LabSampleID=$LAB_TEST_ID.' & '.$TEST_REP_DT;

	
	 $sql_update="UPDATE `QcSalmonellaReport` SET `LabSampleID`='$LAB_TEST_ID',`SampleDate`='$TEST_REP_DT',`TestedBy`='$TESTED_BY',`VerifiedBy`='$VERIFIED_BY',
	`AnalysisDate`='$DOA',`CompletionDate`='$DOC',`InvID`='$PoID' WHERE ID=$REFERENCE_ID";
	
		
        
	
	
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
                  <label for="exampleInputEmail1">Test Report No </label>
                  <input required type="text" class="form-control" name='LAB_TEST_ID' id="LAB_TEST_ID" 
				  placeholder="Enter Test Report No" value="<?php echo $post_details['LabSampleID']?>">
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Test Report Date</label>
                  <input required type="date" class="form-control" name='TEST_REP_DT' id="TEST_REP_DT" 
				  value="<?php echo $post_details['SampleDate']?>" placeholder="Enter Test Report Date">
                </div> 
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Date of Analysis</label>
                  <input required type="date" class="form-control" name='DOA' id="DOA" 
				  value="<?php echo $post_details['AnalysisDate']?>" placeholder="Enter Date of analysis">
                </div>
			
	

				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Date of Completion</label>
                  <input required type="date" class="form-control" name='DOC' id="DOC" 
				  value="<?php echo $post_details['CompletionDate']?>" placeholder="Enter Date of completed">
                </div>
				
				
			   
			     <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Tested By</label>

   
                  <select required type="text" class="form-control select2" name='TESTED_BY' id="TESTED_BY">
				   
				   
				    <?php while ($RowLabUser=$stmtLabUser->fetch_array()){ ?>
				     <option 
					 <?php if($post_details['TestedBy']==$RowLabUser['LbuName']){ ?>selected="selected"<?php } ?>
					 
					 value="<?php echo $RowLabUser['LbuName'] ?>"><?php echo $RowLabUser['LbuName'] ?></option>
				   
				 <?php  }?>
                  </select>
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Verified By </label>

                     <select required type="text" class="form-control  select2" name='VERIFIED_BY' id="VERIFIED_BY">
				   
				   
				    <?php while ($RowLabUser=$stmtLabtester->fetch_array()){ ?>
				     <option 
					 					 <?php if($post_details['VerifiedBy']==$RowLabUser['LbuName']){ ?>selected="selected"<?php } ?>

					 value="<?php echo $RowLabUser['LbuName'] ?>"><?php echo $RowLabUser['LbuName'] ?></option>
				   
				 <?php  }?>
                  </select>
               
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

