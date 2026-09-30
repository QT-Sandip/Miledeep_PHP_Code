<?php



session_start();

include "../includes/config.php";
include "Constants.php";

if(isset($_GET['Msg'])){
	echo "<script>
	alert('".$_GET['Msg']."');
	
	</script>";
	
	
}




extract($_POST);
extract($_GET);

if(isset($_POST['submit'])){ 	
	
	
	
	 $sql_org_insert=" INSERT INTO `QcTracebilityList`(`FarmerName`, `AddressContinued`, `LicenceAuth`, `Address`, `Tracebility`,
	 `Area`, `NoOfPond`, `TotalArea`, `Taluk`, `Village`, `RegAuthority`)
	 VALUES ('$FARMER_NAME','$ADDRESS_CONT','$LIC_AUG','$ADDRESS','$TRACE','$AREA','$POND',
					'$TOTAL_AREA','$TALUK','$VILLAGE','$REG_AUTH') 
	
		";
		
	
	$stmt1=$iconn->query($sql_org_insert);
	$iid = $iconn->insert_id;
	
	
//echo $iid;
	if($stmt1){
		legacyAuditLogInsertById('QcTracebilityList', 'TraceID', $iid, $_POST, ['Remarks' => 'Farmer Added'], 'QualityControl');
		
	// $SHORT_NAME,$FULL_NAME,$D_TYPE;
	

		
	header('Location: ./AddFarmer.php?Msg=Farmer Added Successfully');
	}
}

 ?>



<!DOCTYPE html>

<html>

<head>

<?php include "../includes/meta.php"; ?>

 </head>

<body class="hold-transition skin-blue sidebar-mini fixed">

   

<div class="wrapper">

<?php include "../includes/header.php"; ?>

  <!-- Left side column. contains the logo and sidebar -->

 <?php include "../includes/sidebar.php"; ?>

  <!-- Content Wrapper. Contains page content -->

  <div class="content-wrapper">

    <!-- Content Header (Page header) -->
	

    <section class="content-header">
	
        

<h2 align="center">Farmer Creation</h2>




	</a>



			</a>

			

			



<ol class="breadcrumb">

        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>

        <li class="active">Farmer Details</li>

      </ol>

    </section>

	


    <!-- Main content -->

    <section class="content">

      <!-- Small boxes (Stat box) -->
	  
	  
	  
	 
    <div id="tdetails" class="table-responsive" >  

        <div class="box">
		          <form role="form" method="post">
              <div class="box-body">
		
			  
			   <div class="form-group col-sm-6" >
                  <label for="exampleInputEmail1">Farmer Name</label>
                  <input required type="text" class="form-control" name='FARMER_NAME' 
				  id="FARMER_NAME" placeholder="Enter Farmer Name">
                </div>
				
				<div class="form-group col-sm-6" >
                  <label for="exampleInputEmail1">Farmer Address Continued</label>
                  <input required type="text" value="" class="form-control" name='ADDRESS_CONT' 
				  id="ADDRESS_CONT" placeholder="Enter Farmer Address Continued">
                </div>
			  
			   <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Licence Authority</label>
                  <input required type="text" class="form-control" name='LIC_AUG' id="LIC_AUG"
				  placeholder="Enter Licence Authority">
                </div> 
				
		
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Farmer Address</label>
                  <input required type="text" value="" class="form-control" name='ADDRESS' 
				  id="ADDRESS" placeholder="Enter Farmer Address">
                </div>
			  
		 
		        <div class="form-group col-sm-3">  
                  <label for="exampleInputPassword1">Tracebility</label>
                  <input required  type="text" class="form-control" name="TRACE" id="TRACE" placeholder="Enter Farm Registration Number" >
                </div>
               
			  																
	<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Registration Authority</label>
                                  <select class="form-control" name="REG_AUTH" id="REG_AUTH">
					<option value="MPEDA" >MPEDA</option>
					<option value="CCA" >CCA</option>
					
					<option value="STATE FISHERIES" >STATE FISHERIES</option>
		
		 <select>
				
				</div>
	
				<div class="form-group col-sm-3">  
                  <label for="exampleInputPassword1">Area</label>
                  <input   type="text" class="form-control" name="AREA" id="AREA" placeholder="Enter Area" >
                </div>
				
				<div class="form-group col-sm-3">  
                  <label for="exampleInputPassword1">Pond Number</label>
                  <input   type="text" class="form-control" name="POND" id="POND" placeholder="Enter Pond Number" >
                </div>

               <div class="form-group col-sm-3">  
                  <label for="exampleInputPassword1">Total Area</label>
                  <input   type="text" class="form-control" name="TOTAL_AREA" id="TOTAL_AREA" placeholder="Enter Total Area" >
                </div>
				
				<div class="form-group col-sm-3">  
                  <label for="exampleInputPassword1">Taluk</label>
                  <input   type="text" class="form-control" name="TALUK" id="TALUK" placeholder="Enter Taluk" >
                </div>
				
				<div class="form-group col-sm-3">  
                  <label for="exampleInputPassword1">Village</label>
                  <input   type="text" class="form-control" name="VILLAGE" id="VILLAGE" placeholder="Enter Village" >
                </div>
					
				
				
				<div class="col-xs-12 box-footer"  align="right">
                <button type="submit"  name='submit' class="btn btn-success">Submit</button>
              </div>
				
				</div>
				
              <!-- /.box-body -->

            </form>		
			

<div>



</div>

</div>

</div>
</div>



    </section>

    <!-- /.content -->

 

  <!-- /.content-wrapper -->

   <?php include "../includes/footer.php"; ?>
   <?php include "../includes/scripts.php"; ?>

  </div>

</body>
</html>

