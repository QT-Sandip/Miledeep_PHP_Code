<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


$PageTitleFolder="SalesNew";
$PageTitle="Add Advance"; 


$PIId=$_GET['PIID'];



extract($_POST);
extract($_GET);

	

		
$SqlCount="SELECT * FROM `$ScrmCustomInvoices` WHERE PiID='$PIId'";
		$StmtCount=$iconn->query($SqlCount);
		$RowCustomInvoice=$StmtCount->fetch_array();
		

if(isset($_POST['submit'])){
	
		$oldAdvanceData = legacyAuditFetchRowById($ScrmCustomInvoice, 'PiID', $PIId, 'Sales');
		 
			$SqlUpdate= "UPDATE `$ScrmCustomInvoice` set `AdvanceAmount`='$AdvanceAmount' WHERE PiID='$PIId'"; 
			 $StmtUpdate=$iconn->query($SqlUpdate);
			 
	
	if($StmtUpdate){
		legacyAuditLogUpdateById($ScrmCustomInvoice, 'PiID', $PIId, $oldAdvanceData, $_POST, [
			'ReferenceNo' => $PIId,
			'Remarks' => 'Advance amount updated',
		], 'Sales');
	echo '<script language="javascript" type="text/javascript"> 
                alert("Success");
                window.location = "DocAddReports.php?PoID='.$PIId.'";
        </script>';
	
		//header('Location: PIList.php');
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


				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Advance Amount in USD</label> 
                  <input required type="number" class="form-control" name='AdvanceAmount' id="AdvanceAmount"
				  placeholder="Enter Advance Amount in USD" value="<?php echo $RowCustomInvoice['AdvanceAmount']; ?>">
                </div>
				
			
                </div>
				  
				<div class="col-xs-12 box-footer"  align="right">
			  <a target="" href="DocAddReports.php?PoID=<?php echo $PIId ?>"
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
<script>

$("document").ready(function(){
	  $("#PortOfLoading").select2();
	  $("#PortofDischarge").select2();
	  $("#Description").select2();
	  
    $("#file").change(function() {
        alert('changed!');
    });
	
	
	

	
});
  	

</script>



</html>

			

