<?php



session_start();

include "../includes/config.php";

$INVOICE_ID=$_GET['id'];
$ID=$_GET['id'];

include "Constants.php";


$sql_invoice="SELECT * FROM `INVOICE_DATA` where ID='$INVOICE_ID'";
$stmt_invoice=$iconn->query($sql_invoice);
$invoice_details=$stmt_invoice->fetch_array();


$INVOICE_ID=$_GET['id'];


$sql_LAB_USERS="SELECT * FROM `LAB_USERS` ";
$stmt_LAB_USERS=$iconn->query($sql_LAB_USERS);
$stmt_LAB_USERS1=$iconn->query($sql_LAB_USERS);


extract($_POST);
extract($_GET);

if(isset($_POST['submit'])){

	
	$sql_org_insert="INSERT INTO `QcIHSwabRprt`(`SampleDate`, `TPCValue`, 
	`AnalysedBy`, `VerifiedBy`) 
	VALUES ('$SampleDate','$TPCValue','$AnalysedBy','$VerifiedBy')";
	
	  $sql_org_insert;

	$stmt1=$iconn->query($sql_org_insert);
	$iid = $iconn->insert_id;
//echo $iid;
	if($stmt1){
		legacyAuditLogInsertById('QcIHSwabRprt', 'ID', $iid, $_POST, ['Remarks' => 'Ice & Water Swab Report Added'], 'QualityControl');
		header('Location: DocAddReports.php?id='.$INVOICE_ID);
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

        
<h3> SANITARY STANDARD ASSESSING OF UNIT WATER & ICE-<span style="color:Tomato; bold"><?php echo $invoice_details['PO_NUM'] ?></span></h3>




	</a>



			</a>


<ol class="breadcrumb">

        <li><a href="#"><i class="fa fa-dashboard"></i> Home</a></li>

        <li class="active"> SANITARY STANDARD ASSESSING OF UNIT WATER & ICE</li>

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
                  <label for="exampleInputEmail1">Date of Sampling</label>
               <input required type="date" value="<?php  echo date("Y-m-d");?>"class="form-control"
			   name='SampleDate' id="SampleDate" placeholder="Enter Date of Sampling"
			   value="<?php echo $invoice_details['SampleDate']?>">
   
                </div>
			
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Analysed By</label>
                   <select required type="text" class="form-control" name='AnalysedBy' id="AnalysedBy">

				    <?php while ($LAB_USERS_details=$stmt_LAB_USERS->fetch_array()){ ?>
				     <option value="<?php echo $LAB_USERS_details['USER_NAME'] ?>">
					 <?php echo $LAB_USERS_details['USER_NAME'] ?></option>
				   
				 <?php  }?>
                 </select>
               
                </div>
				
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Verified By </label>             
				  <select class="form-control" name="VerifiedBy" id="VerifiedBy">
				  <?php while ($LAB_USERS_details=$stmt_LAB_USERS1->fetch_array()){ ?>
				     <option value="<?php echo $LAB_USERS_details['USER_NAME'] ?>">
					 <?php echo $LAB_USERS_details['USER_NAME'] ?></option>
				   
				 <?php  }?>			  
				  </select>
   		 
               
                </div>
                

			
				
                              
				</div>
				
				              <div class="box-body">

				<div><h3 style="color: green"> Enter TPC Values</h3></div>
					
   
   <div class="form-group col-sm-2" >
                  <label for="exampleInputEmail1">Water</label>
                   <input required type="text" class="form-control" 
				   name='WATER' id="WATER"  placeholder="Enter TPC Value" 
				   value="">
   </div>
   <div class="form-group col-sm-2" >
                  <label for="exampleInputEmail1">Flake Ice</label>
                   <input required type="text" class="form-control" 
				   name='FLAKE' id="FLAKE"  placeholder="Enter TPC Value" 
				   value="">
  </div>
  <div class="form-group col-sm-2" >
                  <label for="exampleInputEmail1">Tube Ice</label>
                   <input required type="text" class="form-control" 
				   name='TUBE' id="TUBE"  placeholder="Enter TPC Value" 
				   value="">
   </div>
   <div class="form-group col-sm-2" >
                  <label for="exampleInputEmail1">Drinking Water</label>
                   <input required type="text" class="form-control" 
				   name='DRINK' id="DRINK"  placeholder="Enter TPC Value" 
				   value="">
   </div>
               
               
				
				<div class="form-group col-sm-2" >
                  <label for="exampleInputEmail1">Water</label>
                   <input required type="text" class="form-control" 
				   name='WATER_TPC' id="WATER_TPC"  placeholder="Enter TPC Value" 
				   value="">
				
                </div>
				
              <!-- /.box-body -->

		</div>	  
			   

        <div class="box-body">


	<div class="col-xs-12 box-footer"  align="right">
                <button type="submit"  name='submit' class="btn btn-success">Submit</button>
              </div>
             
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
  <?php include "../includes/AjaxLoader.php"; ?>
  </div>



</body>


</html>

