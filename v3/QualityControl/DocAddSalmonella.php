<?php
 $QueryParms=$_GET['Q']; 
 $QueryParm=$_GET['Q']; 
 include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");


$ClientID=$_SESSION["ClientID"];



if($QueryParms!=''){
  $QueryParms=urlDecode64($QueryParms);  
  $queries = array();
  parse_str($QueryParms, $_GET);
  }

$PoID=$_GET['PoID'];

$SqlPo="SELECT * FROM  $TblScrmPoData   WHERE PoID='$PoID'"; 

$StmtPo=$iconn->query($SqlPo);

$RowPo=$StmtPo->fetch_array();


$PageTitle="Salmonella Test Report - USA- ".$RowPo['PoNo'];
$PageTitleFolder="Quality Control";


$sqlLabUser="SELECT * FROM $TblQcLabTechnologist WHERE LbuStatus=1";
$stmtLabUser=$iconn->query($sqlLabUser);
$stmtLabtester=$iconn->query($sqlLabUser);



extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	
	//$LabSampleID=$TestReportNo.' & '.$TestReportDate;
	  $sql_org_insert="INSERT INTO $TblQcSalmonellaReport(
	`CLID`, `LabSampleID`,`SampleDate`, `NetWeight`, `TestedBy`, 
	`VerifiedBy`,`AnalysisDate`, `CompletionDate`, `InvID`) VALUES 
	('$CLID','$TestReportNo','$TestReportDate','$NetWeight','$TestedBy',
	'$VerifiedBy','$AnalysisDate','$CompletionDate','$PoID')"; 
	
	

	$stmt1=$iconn->query($sql_org_insert);
	$MB_REPORT_ID = $iconn->insert_id;
//echo $iid;
	
	
	if($stmt1){
		legacyAuditLogInsertById($TblQcSalmonellaReport, 'ID', $MB_REPORT_ID, $_POST, ['Remarks' => 'Salmonella Report Created'], 'QualityControl');
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
                  <input required type="text" class="form-control" name='TestReportNo' id="TestReportNo" 
				  value="" placeholder="Enter Test Report No">
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Test Report Date</label>
                  <input required type="date" class="form-control" name='TestReportDate' id="TestReportDate" 
				  value="" placeholder="Enter Test Report Date">
                </div> 
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Date of analysis</label>
                  <input required type="date" class="form-control" name='AnalysisDate' id="AnalysisDate" 
				  value="" placeholder="Enter Date of analysis">
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Date of completed</label>
                  <input required type="date" class="form-control" name='CompletionDate' id="CompletionDate" 
				  value="" placeholder="Enter Date of completed">
                </div>
				
				
			   
			     <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Tested By</label>

   
                  <select required type="text" class="form-control select2" name='TestedBy' id="TestedBy">
				   
				   
				    <?php while ($RowLabUser=$stmtLabUser->fetch_array()){ ?>
				     <option value="<?php echo $RowLabUser['LbuName'] ?>"><?php echo $RowLabUser['LbuName'] ?></option>
				   
				 <?php  }?>
                  </select>
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Verified By </label>

                     <select required type="text" class="form-control select2" name='VerifiedBy' id="VerifiedBy">
				   
				   
				    <?php while ($RowLabUser=$stmtLabtester->fetch_array()){ ?>
				     <option value="<?php echo $RowLabUser['LbuName'] ?>"><?php echo $RowLabUser['LbuName'] ?></option>
				   
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

