<?php
session_start();
include "../includes/config.php";

$LotID=$_GET['id'];

$SqlReasons="SELECT * FROM `TicketReasons`";
$StmtReasons=$iconn->query($SqlReasons);


extract($_POST);
extract($_GET);


if(isset($_POST['submit'])){
	

	$SqlTicketInsert="INSERT INTO `VEHICAL_DETAILS`(
					 `VEHICAL_NUMBER`, `VEHICAL_CAPACITY`, 
					`VEHICAL_MAKE`
					) 
				VALUES ('$VEHICAL_NUMBER','$VEHICAL_CAPACITY','$VEHICAL_MAKE')";
	
//	 $SqlTicketInsert;

	$StmtTicketInsert=$iconn->query($SqlTicketInsert);
	$M_T_REPORT_ID = $iconn->insert_id;
//echo $iid;
	if($StmtTicketInsert){
		legacyAuditLogInsertById('VEHICAL_DETAILS', 'ID', $M_T_REPORT_ID, $_POST, [
			'ReferenceNo' => $M_T_REPORT_ID,
			'Remarks' => 'Vehicle details created',
		], 'Tickets');
		
		echo "<script>alert('Success');</script>";
		
		//header('Location: users.php');
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

        

<h2 align="center">Raise Ticket</h2>


</a>
</a>

			

			



<ol class="breadcrumb">

        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>

        <li class="active">Raise Ticket</li>

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
                  <label for="exampleInputEmail1">Issue</label>
                 
				  <select class="form-control" name="Issue" id="Issue">		   
				   <?php while($RowReasons=$StmtReasons->fetch_array()) { 
				
				    ?>    				
				    <option Value="<?php echo $RowReasons['ReasonName'];?>">
				      <?php echo $RowReasons['OptionName'];?></OPTION>				
				      <?php } ?>
				 <select> 				    
			</div>
		  	
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Remarks</label>
                  <input required type="text" class="form-control"  name="Remarks" id="Remarks" 
				  placeholder="Enter Valid Reason">
                </div>
				
				

				
			  <div class="col-xs-12 box-footer"  align="right">
                <button type="submit"  name='submit' class="btn btn-success">SUBMIT</button>
              </div>
            </form>



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

