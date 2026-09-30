<?php


session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

extract($_POST);
extract($_GET);

	$PageTitle="Add BoreWater Analysis Report" ;


$PageTitleFolder="Quality Control";

$ID=$_GET['ID'];

include "Constants.php";



$sqlBoreWtr="SELECT * FROM `QcIHBoreWater` where ID='$ID'";
$stmtBoreWtr=$iconn->query($sqlBoreWtr);
$RowBoreWtr=$stmtBoreWtr->fetch_array();

$sql_LAB_USERS="SELECT * FROM `LAB_USERS`  WHERE Stats='1'";
$stmt_LAB_USERS=$iconn->query($sql_LAB_USERS);
$stmt_LAB_USERS1=$iconn->query($sql_LAB_USERS);

if(isset($_POST['submit'])){
	$COUNT=mysqli_num_rows($stmtBoreWtr);

	$COLOUR='Transperant';
	$ID=$_GET['ID'];
	IF($COUNT==0){
	$sql_org_insert="INSERT INTO `QcIHBoreWater`(`SampleDate`, `SampleTime`, 
	`PHValue`, `Temperature`, `Colour`, `Salinity`, `DissolvedSolids`, 
	`Hardness`, `Chlorine`,`VerifiedBy`,`AnalysiedBy`) VALUES ('$SampleDate','$SampleTime','$PHValue',
	'$Temperature','$COLOUR','$Salinity','$DissolvedSolids',
	'$Hardness','$Chlorine','$VerifiedBy','$AnalysiedBy')";
	}ELSE{
	$sql_org_insert="
	UPDATE `QcIHBoreWater` SET 
	`SampleDate`='$SampleDate',
	`SampleTime`='$SampleTime',
	`PHValue`='$PHValue',
	`Temperature`='$Temperature',`Salinity`='$Salinity',
	`DissolvedSolids`='$DissolvedSolids',`Hardness`='$Hardness',
	`Chlorine`='$Chlorine',`VerifiedBy`='$VerifiedBy',
	`AnalysiedBy`='$AnalysiedBy'
	WHERE ID='$ID'"	;
		
	}
	  $sql_org_insert;

	$stmt1=$iconn->query($sql_org_insert);
	$iid = $iconn->insert_id;
//echo $iid;
	if($stmt1){
		
		header('Location: CommonSearchList.php?id=1');
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

	
	 <?php	include ($IncludesPath."PreLoader.php"); ?>

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




    <!-- Main content -->



          <!--<div class="row">
                            <div class="col-12">-->
		<form role="form" method="post" enctype="multipart/form-data">

                                <div class="card">
								
                                    <div class="card-body">
									
										<div class="row">

                 
                <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Sample collection Time</label>
                  <input required type="time" class="form-control" name="SampleTime" id="SampleTime"
				  placeholder="Enter Sample collection Time" value="<?php echo $RowBoreWtr['SampleTime']?>">
                </div>
				

				
                <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Date of Sampling</label>
               <input required type="date" value="<?php  echo $RowBoreWtr['SampleDate']?>"class="form-control"
			   name='SampleDate' id="SampleDate" placeholder="Enter Date of Sampling" max="<?php  echo date("Y-m-d");?>">
   
                </div>
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Verified By </label>
                   <select required type="text" class="form-control select2" name='AnalysiedBy' id="AnalysiedBy">

				    <?php while ($LAB_USERS_details=$stmt_LAB_USERS->fetch_array()){ ?>
				     <option 
					               <?php if($RowBoreWtr['AnalysiedBy']==$LAB_USERS_details['USER_NAME']){ ?> selected="selected" <?php }?>
					 value="<?php echo $LAB_USERS_details['USER_NAME'] ?>">
					 <?php echo $LAB_USERS_details['USER_NAME'] ?></option>
				   
				 <?php  }?>
                 </select>
               
                </div>
				
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Tested By</label>             
				  <select class="form-control select2" name="VerifiedBy" id="VerifiedBy">
				  <?php while ($LAB_USERS_details=$stmt_LAB_USERS1->fetch_array()){ ?>
				     <option

              <?php if($RowBoreWtr['VerifiedBy']==$LAB_USERS_details['USER_NAME']){ ?> selected="selected" <?php }?>
					 value="<?php echo $LAB_USERS_details['USER_NAME'] ?>">
					 <?php echo $LAB_USERS_details['USER_NAME'] ?></option>
				   
				 <?php  }?>			  
				  </select>
   		 
               
                </div>
				</div>
				</div>
				</div>
                
				               <h3 style="color:green">Parameter Values</h3>

              <div class="card">
              <div class="card-body">
										<div class="row">


				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">PH Value</label>
                   <input required type="text" class="form-control" 
				   name='PHValue' id="PHValue"  placeholder="Enter PH VALUE" 
				   value="<?php echo $RowBoreWtr['PHValue']?>">
   
               
                </div>
				
				 <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Temperature</label>
                  <input required type="text" class="form-control" name='Temperature' id="Temperature"
				  placeholder="Enter Temperature" value="<?php echo $RowBoreWtr['Temperature']?>">
                </div>
				
			 <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Salinity</label>
                  <input required type="text" class="form-control" name='Salinity' id="Salinity"
				  placeholder="Enter Salinity" value="<?php echo $RowBoreWtr['Salinity']?>">
                </div>

			  <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Hardness</label>
                  <input required type="text" class="form-control" name='Hardness' id="Hardness"
				  placeholder="Enter HARDNESS" value="<?php echo $RowBoreWtr['Hardness']?>">
                </div>
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Chlorine</label>
                  <input required type="text" class="form-control" name='Chlorine' id="Chlorine"
				  placeholder="Enter CHLORINE" value="<?php echo $RowBoreWtr['Chlorine']?>">
                </div>
				 <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Total Dissolved Solids </label>
                  <input required type="text" class="form-control" name='DissolvedSolids' id="DissolvedSolids"
				  placeholder="Enter TDS" value="<?php echo $RowBoreWtr['DissolvedSolids']?>">
                </div>
                </div>
                </div>
                </div>
                

               
                
  <div class="col-xs-12 card-footer"  align="right">
                
				
				<button type="submit"  name='submit' class="btn btn-success">Submit</button>
</div>
     </form>

   </div> <!-- end row -->
    
	</div> <!-- end card body-->
                                </div> <!-- end card -->
                          

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
