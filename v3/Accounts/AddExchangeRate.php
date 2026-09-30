<?php
$Status=$_SESSION['Status']; 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");


$PageTitle="Add Exchange Rates";

$PageTitleFolder="Accounts";

 $UserID=$_SESSION['UserID']; 

  
extract($_POST);
extract($_GET);


if(isset($_POST['submit'])){
	
	
  
	$PresentDateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
	$PostedDate=$PresentDateTime->format("Y-m-d  H:i:s"); 
	
	
	
	
 $SqlInsert="INSERT INTO `$RmCostingExpanses`(`UserID`,`ExchangeRate`, `Expanse`, `DEPB`, `UpdatedDate`) 
VALUES ('$UserID','$Exchange','$Expanse','$Depb','$Date')"; 
		$StmtInsert=$iconn->query($SqlInsert);


if($StmtInsert){
	legacyAuditLogInsertById($RmCostingExpanses, 'ID', $iconn->insert_id, array('UserID'=>$UserID,'ExchangeRate'=>$Exchange,'Expanse'=>$Expanse,'DEPB'=>$Depb), array(), 'Accounts');
?>
	
	<script>

	alert("Success");

	</script>
	
<?php } 
	//header('Location: index');
}
else{
		
		
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

	
	 <?php include ($IncludesPath."PreLoader.php"); ?>

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

    
                <div class="form-group col-lg-3"  >
                  <label for="exampleInputEmail1">Exchange Rate</label>
                  <input required type="text" 
				  class="form-control" name='Exchange' id="Exchange"
				  placeholder="Exchange Rate">
                </div> 
				
				 <div class="form-group col-lg-3"  >
                  <label for="exampleInputEmail1">Expanse</label>
                  <input required type="text" 
				  class="form-control" name='Expanse' id="Expanse"
				  placeholder="Expanse/Kg">
                </div> 
				
				 <div class="form-group col-lg-3"  >
                  <label for="exampleInputEmail1">DEPB</label>
                  <input required type="text" 
				  class="form-control" name='Depb' id="Depb"
				  placeholder="Enter Depb">
                </div> 
				
				<div class="form-group col-lg-3"  >
                  <label for="exampleInputEmail1">Date</label>
                  <input required type="date" 
				  class="form-control" name='Date' id="Date"
				  placeholder="Enter Depb">
                </div> 
				
				
			 
                </div>
				
				
				

				
				
				
			  <div class="col-xs-12 box-footer"  align="right">
                <button type="submit"  name='submit' class="btn btn-success">Add</button>
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

