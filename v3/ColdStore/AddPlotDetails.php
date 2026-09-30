<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include "Constants.php";

$PageTitleFolder="ColdStore";
$PageTitle="HeadOn Details";
$ID=$_GET['ID'];


 $sqlPlotData="SELECT * FROM `ProdPlotData` where PlotID='$ID' ";
$stmtPlotData=$iconn->query($sqlPlotData);
$RowPlotData=$stmtPlotData->fetch_array();

 $COUNT=mysqli_num_rows($stmtPlotData);


extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	 
	IF($COUNT==0){	
	 $sql_org_insert="
	 INSERT INTO `ProdPlotData`(`PendingHOn`, `LocalHOn`, 
	 `OutsideHOn`, `GradingWt`, `HLWt`, `PeelingWt`, `PlotDate`, 
	 `PostedDate`, `UserID`) VALUES ('$PendingHOn','$LocalHOn',
	 '$OutsideHOn','$GradingWt','$HLWt',
	 '$PeelingWt','$PlotDate','$PostedDate','$UserID')"; 
	 
		
	$stmt=$iconn->query($sql_org_insert);
	$BuyerID = $iconn->insert_id;
	}else{
	
	$sql_org_insert="UPDATE `ProdPlotData`
	SET `PendingHOn`='$PendingHOn',
	`LocalHOn`='$LocalHOn',
	`OutsideHOn`='$OutsideHOn',
	`GradingWt`='$GradingWt',
	`HLWt`='$HLWt',
	`PeelingWt`='$PeelingWt',
	`PlotDate`='$PlotDate',
	`PostedDate`='$PostedDate'
	WHERE PlotID='$ID'";	
	
	$stmt=$iconn->query($sql_org_insert);
	
	
	

		
	}
if($stmt){
	echo '<script language="javascript" type="text/javascript"> 
                alert("Success");
                window.location = "PlotList.php";
        </script>';
		
		
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
	 <div class="alert alert-success alert-dismissible fade show" role="alert">
                                                <i class="mdi mdi-check-all mr-2"></i>
                                                      <?php echo $Status;
$_SESSION['Status']='';	  ?>
                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>

<?php } ?>

                        <?php include ($IncludesPath."page-title.php");?>

	
          
	
<div class="row">
                            <div class="col-12">
                                <div class="card">
								
								<form role="form" method="post">
                                    <div class="card-body">
									
										<div class="row">
		
	
               <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Plot Date</label>
                  <input required type="date" class="form-control" name='PlotDate' 
				  id="PlotDate" placeholder="Enter Plot Date" 
				  value="<?php echo $RowPlotData['PlotDate']?>">
                </div>
				
			
			<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">HeadOn Pending</label>
                  <input required type="text" class="form-control" name='PendingHOn' 
				  id="PendingHOn" placeholder="Enter HeadOn Pending" 
				  value="<?php echo $RowPlotData['PendingHOn']?>">
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Local HeadOn</label>
                  <input required type="text"  class="form-control" name='LocalHOn' 
				  id="LocalHOn" placeholder="Enter Local HeadOn" 
				  value="<?php echo $RowPlotData['LocalHOn']?>">
                </div>
				
			  
			   <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">OutSide HeadOn</label>
                  <input required type="text" class="form-control" name='OutsideHOn' id="OutsideHOn"
				  placeholder="Enter OutSide HeadOn" value="<?php echo $RowPlotData['OutsideHOn']?>">
                </div> 

				
				
                </div>
				
				
			
				 
				
				</div>
	
	 </div>		 
	</div>
				  
				<h4>Floor Balance</h4>
				
				 <div class="col-12">
                                <div class="card">
							
			
				  <div class="card-body">
									
										<div class="row">
		
               <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Grading Weight</label>
                  <input required type="text" class="form-control" name='GradingWt' 
				  id="GradingWt" placeholder="Enter Grading Weight" 
				  value="<?php echo $RowPlotData['GradingWt']?>">
                </div>
			
	
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Headless Weight</label>
                  <input required type="text"  class="form-control" name='HLWt' 
				  id="HLWt" placeholder="Enter Headless Weight" 
				  value="<?php echo $RowPlotData['HLWt']?>">
                </div>
				
			  
			   <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Peeling Weight</label>
                  <input required type="text" class="form-control" name='PeelingWt' id="PeelingWt"
				  placeholder="Enter Peeling Weight" value="<?php echo $RowPlotData['PeelingWt']?>">
                </div> 

				
				
                </div>
				
				
			<div class="col-xs-12 box-footer"  align="right">
				
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
