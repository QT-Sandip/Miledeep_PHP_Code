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
	
	
	
		
	 $sql_org_insert="
	 INSERT INTO `$TblScrmConsigneeDetails`(`ConsigneeName`, `ConsigneeAddress`,
	 `ConsigneeCountry`, `ConsigneeSplIns`, `ConsigneeFishingNo`)
	 VALUES ('$CONSIGNEE_NAME','$CONSIGNEE_ADDRESS',
	 '$BUYER_COUNTRY','$SPECIAL_INST','$FISHING_PMT_NO')"; 
	 
	 
	
	$stmt1=$iconn->query($sql_org_insert);
	$iid = $iconn->insert_id;
	
	if($stmt1){
		legacyAuditLogInsertById('ScrmConsigneeDetails', 'ConID', $iid, $_POST, ['Remarks' => 'Buyer Added'], 'QualityControl');
		
	header('Location: ./AddBuyer.php?Msg=Buyer Added Successfully');
	//header('Location:./QcDashboard.php');
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
	
        

<h2 align="center">Buyer Creation</h2>




	</a>



			</a>

			

			



<ol class="breadcrumb">

        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>

        <li class="active">Buyer Details</li>

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
                  <label for="exampleInputEmail1">Buyer Name</label>
                  <input required type="text" class="form-control" name='CONSIGNEE_NAME' 
				  id="CONSIGNEE_NAME" placeholder="Enter Buyer Name">
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Buyer Address</label>
                  <input required type="text" value="" class="form-control" name='CONSIGNEE_ADDRESS' 
				  id="CONSIGNEE_ADDRESS" placeholder="Enter Buyer Address">
                </div>
			  
			   <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Buyer Country</label>
                  <input required type="text" class="form-control" name='BUYER_COUNTRY' id="BUYER_COUNTRY"
				  placeholder="Enter Country name">
                </div> 
				
          
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Fishing Permit Number</label>
                  <input required   type="text" class="form-control" name="FISHING_PMT_NO" 
				  id="FISHING_PMT_NO" placeholder="Enter Fishing Permit Number" >
                </div>
				
				<div class="form-group col-sm-6">
                  <label for="exampleInputPassword1">Any Special Specifications</label>
                  <input required   type="text" class="form-control" name="SPECIAL_INST" id="SPECIAL_INST" 
				  placeholder="Enter special instructions" >
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

