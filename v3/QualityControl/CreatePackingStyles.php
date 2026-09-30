<?php



session_start();

include "../includes/config.php";

//$INVOICE_ID=$_GET['id'];
$LOT_ID=$_GET['ID'];

extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	
      $dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
      $TIME_STAMP=$dateTime->format("Y-m-d  H:i:s"); 
	  
	  
	  if($UOM=='LBS'){
		  $MFactor=0.454;
	  }
	  elseif($UOM=='KGS'){
		  $MFactor=1;
	  }
	  elseif($UOM=='GRAMS'){
		  $MFactor=0.001;;
	  }
	 
	$PACKING_STYLE=$TOTAL_SLABS .' X '. $SLAB_WEIGHT .' '. $UOM;
	
	 $sql_org_insert="INSERT INTO $TblAdminPackingStyles
	 (`TotalSlabs`, `SlabWeight`, `Uom`, `PackingStyle`, `MFactor`)
 VALUES ('$TOTAL_SLABS','$SLAB_WEIGHT','$UOM','$PACKING_STYLE','$MFactor')";
	

	$stmt1=$iconn->query($sql_org_insert);
	$MB_REPORT_ID = $iconn->insert_id;
//echo $iid;
	if($stmt1){
		legacyAuditLogInsertById('AdminPackingStyles', 'PackingStyleID', $MB_REPORT_ID, $_POST, ['Remarks' => 'Packing Style Created'], 'QualityControl');
	
		header('Location: CreatePackingStyles.php');
	}
}

			


 ?>



<!DOCTYPE html>

<html>

<head>

<?php include "../includes/meta.php"; ?>

 </head>

<body class="hold-transition skin-blue sidebar-mini sidebar-collapse">

<div class="wrapper">

<?php include "../includes/header.php"; ?>
  <!-- Left side column. contains the logo and sidebar -->
 <?php include "../includes/sidebar.php"; ?>
  <!-- Content Wrapper. Contains page content -->
  <div class="content-wrapper">
    <!-- Content Header (Page header) -->
   <section class="content-header">

<h2 align="center">Packing Styles Creation  <span style="color:Tomato; bold">
</span></h3>

	</a>
		</a>

<ol class="breadcrumb">

        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>

        <li class="active">Packing Styles</li>

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
                  <label for="exampleInputEmail1">Total Slabs </label>
				<input required type="text" class="form-control" name='TOTAL_SLABS' id="TOTAL_SLABS" 
				  placeholder="Enter Total No of Slabs" value="">		
		
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Slab Weight</label>
<input required type="text" class="form-control" name='SLAB_WEIGHT' id="SLAB_WEIGHT" 
				  placeholder=" Enter Slab Weight" value="">
                </div> 
				
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">UOM</label>
				    
				  <select class="form-control" name="UOM" id="UOM" required>
				   
				  <option Value="">Select UOM</OPTION>

				
				<option Value="LBS">LBS</OPTION>
				<option Value="KGS">KGS</OPTION>
				<option Value="GRAMS">GRAMS</OPTION>
				
				<select> 
                </div> 
				
				
				
				 
				
				
			  <div class="col-xs-12 box-footer"  align="center">
                <button type="submit"  name='submit' class="btn btn-primary">ADD</button>
              </div>
            </form>

</div>

    </section>
	
</div>

    <?php include "../includes/footer.php"; ?>
	   <?php include "../includes/scripts.php"; ?>

  </div>

</body>

</html>

