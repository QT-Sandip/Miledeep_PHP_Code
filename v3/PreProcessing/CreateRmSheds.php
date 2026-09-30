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
$sql="SELECT * FROM `RM_PROCESSING_STATIONS`   WHERE UNIT_NAME='$UNIT_NAME'";	
$stmt=$iconn->query($sql);
$count=mysqli_num_rows($stmt);
if($count==0){

	
		
	  $sql_org_insert="
	INSERT INTO `RM_PROCESSING_STATIONS`( `UNIT_NAME`, `UNIT_LOCATION`, `UINT_ADDRESS`,  `UNIT_INCHARGE_ID`,
		`UNIT_PHONE_NUMBER`) 
	VALUES ('$UNIT_NAME','$UNIT_NAME','$UNIT_ADDRESS','$UNIT_INCHRG','$PHN_NMBR')
";  
	 
	 
	
	$stmt1=$iconn->query($sql_org_insert);
	$iid = $iconn->insert_id;
	
	
//echo $iid;
	if($stmt1){
		legacyAuditLogInsertById('RM_PROCESSING_STATIONS', 'ID', $iid, $_POST, [
			'ReferenceNo' => $iid,
			'Remarks' => 'RM shed created',
		], 'PreProcessing');
		
	// $SHORT_NAME,$FULL_NAME,$D_TYPE;

		
	header('Location:CreateRmSheds.php?Msg=Shed Name successfully Created');


	//header('Location:./QcDashboard.php');
	}
}

else{
		header('Location: ./CreateRmSheds.php?Msg=Shed Name  Already Exists');

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
	
        

<h2 align="center">Rm Shed Creation</h2>




	</a>



			</a>

			

			

	

<ol class="breadcrumb">

        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>

        <li class="active">Shed Details</li>

      </ol>

    </section>

	


    <!-- Main content -->

    <section class="content">

      <!-- Small boxes (Stat box) -->
	  
	  

	  
	 
    <div id="tdetails" class="table-responsive" >  

        <div class="box">
		          <form role="form" method="post">
              <div class="box-body">
		
			  
			   <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Unit Name</label>
                  <input required type="text" class="form-control" name='UNIT_NAME' 
				  id="UNIT_NAME" placeholder="Enter Unit Name">
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Unit Address</label>
                  <input required type="text" value="" class="form-control" name='UNIT_ADDRESS' 
				  id="UNIT_ADDRESS" placeholder="Enter Unit Address">
                </div>
			  
			   <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Unit Incharge name</label>
                  <input required type="text" class="form-control" name='UNIT_INCHRG' id="UNIT_INCHRG"
				  placeholder="Enter Unit Incharge name">
                </div> 
				
          
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Unit Phone Number</label>
                  <input    type="number" class="form-control" name="PHN_NMBR" 
				  id="PHN_NMBR" placeholder="Enter Unit Phone Number" >
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

