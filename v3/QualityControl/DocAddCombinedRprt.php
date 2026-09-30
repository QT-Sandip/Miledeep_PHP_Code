<?php
session_start();

$QueryParms=$_GET['Q']; 
 $QueryParm=$_GET['Q']; 
 $RID=$_GET['id'];

 
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");



$PoID=$_GET['PoID'];

$SqlPo="SELECT * FROM  $TblScrmPoData   WHERE PoID='$PoID'"; 

$StmtPo=$iconn->query($SqlPo);

$RowPo=$StmtPo->fetch_array();


$PageTitle="IH-TEST REPORT FOR MICROBIOLOGY - ".$RowPo['PoNo'];
$PageTitleFolder="Quality Control";


$SqlVarieties="SELECT DISTINCT(TypeProduct) FROM $TblQcPoVarityList where InvID='$PoID'";
$StmtVarieties=$iconn->query($SqlVarieties);

$SqlLabUsers="SELECT * FROM $TblQcLabTechnologist WHERE LbuStatus=1";
$StmtLabUsers=$iconn->query($SqlLabUsers);
$StmtLabUsers1=$iconn->query($SqlLabUsers);



//$InvID=$RowShipment['InvID'];


extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	
		
	 $sql_org_insert="INSERT INTO $TblQcMBReport(
	`TypeProduct`, `ReportID`,`TestedBy`, 
	`VerifiedBy`, `InvID`) VALUES 
	('$TypeProduct','$ReportNo',
	'$TestedBy','$VerifiedBy','$PoID')"; 
	
	

	$stmt1=$iconn->query($sql_org_insert);
	$RID = $iconn->insert_id;
	if($stmt1){
		legacyAuditLogInsertById($TblQcMBReport, 'ID', $RID, $_POST, ['Remarks' => 'MB Report Created'], 'QualityControl');
		header('Location: DocAddCombinedResult.php?RID='.$RID.'&Q='.$QueryParm);
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
			<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Test Report Number</label>
                  <input required type="text" class="form-control"  name="ReportNo" id="ReportNo" 
				  value="" placeholder="Enter Test Report Number">
                </div>
				
				
				
				<!--<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Product Type</label>
                 <select class="form-control select2" name="TypeProduct" id="TypeProduct">
				 <?php while($row=$StmtVarieties->fetch_array()){ ?>	
				  <option ><?php echo $row['TypeProduct'] ?></option>
				  <?php } ?>
				 					  
				  <select>               
			   </div>--->

			   <div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Product Type</label>
                 <select class="form-control select2" name="TypeProduct" id="TypeProduct">
				 <option >RAW</option>
				 <option >COOKED</option>
				 					  
				  <select>               
			   </div>
			
				
				<!--<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Country</label>
                 <select class="form-control select2" name="Country" id="Country">
				  <option >CHINA</option>
				  <option >USA</option>
				  <option >NEWZEALAND</option>
				  <option >GENERAL </option>
				  
				  <select>               

			   </div>-->
			   
			   
			   
                <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Tested By</label>
                   
					<select required type="text" class="form-control select2" name='TestedBy' id="TestedBy">
					<?php while ($RowLabUsers=$StmtLabUsers->fetch_array()){ ?>
				     <option value="<?php echo $RowLabUsers['LbuName'] ?>"><?php echo $RowLabUsers['LbuName'] ?></option>
				   
				 <?php  }?>
				</select>
               
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Verified By </label> 
			      <select class="form-control select2" name="VerifiedBy" id="VerifiedBy">
				 <?php while ($RowLabUsers=$StmtLabUsers1->fetch_array()){ ?>
				     <option value="<?php echo $RowLabUsers['LbuName'] ?>"><?php echo $RowLabUsers['LbuName'] ?></option>
					 
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

