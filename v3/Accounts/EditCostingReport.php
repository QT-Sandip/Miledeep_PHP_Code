<?php
session_start();
include "../includes/config.php";
include "../includes/Globals.php";
include "../includes/FuncMsg.php";
include "../includes/AjaxPurchaseDetails.php";

include "../includes/AjaxCostingCreate.php";

$LOT_ID=$_GET['id'];
$USER_ID=$_SESSION["r_id"];

  $IS_GRADED = getCostingBreakUp($iconn,$LOT_ID);
 


//getHlGradingReport($iconn,$LOT_ID);

$SqlCostingReport="SELECT * FROM `RM_COSTING_DETAILS` where LOT_ID='$LOT_ID' ";
$StmtCostingReport=$iconn->query($SqlCostingReport);
$RowCostingReport=$StmtCostingReport -> fetch_array();

$SqlCostingGrades="SELECT * FROM `RM_COSTING_GRADES` where LOT_ID='$LOT_ID' order by COUNT ASC";
$StmtCostingGrades=$iconn->query($SqlCostingGrades);

extract($_POST);
if(isset($_POST['submit'])){	

	$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
	$SUBMISSION_DATE=$dateTime->format("Y-m-d  H:i:s"); 
	
	
	$SqlGrading="SELECT * FROM `RM_COSTING_GRADES` where LOT_ID='$LOT_ID'";
	$StmtGrading=$iconn->query($SqlGrading);
	
	while($RowGrading=$StmtGrading -> fetch_array()){

	$GRAD_GRADE=$_POST['GRAD_GRADE' . $RowGrading['ID']];
	$GRAD_USD=$_POST['GRAD_USD' . $RowGrading['ID']];
	$GRAD_ID=$RowGrading['ID'];
	
	$oldCostingGrade = legacyAuditFetchRowById('RM_COSTING_GRADES', 'ID', $GRAD_ID, 'Accounts');
	
	 $SqlUpdate="UPDATE `RM_COSTING_GRADES` SET 
`USD`='$GRAD_USD',
`SUBMISSION_DATE`='$SUBMISSION_DATE' WHERE ID='$GRAD_ID'";	
	
	$stmt1=$iconn->query($SqlUpdate);
	if($stmt1){
	  legacyAuditLogUpdateById('RM_COSTING_GRADES', 'ID', $GRAD_ID, $oldCostingGrade, array('USD'=>$GRAD_USD), array(), 'Accounts');
	}
	//exit;
	
	}
	
	$oldCostingDetail = legacyAuditFetchRowById('RM_COSTING_DETAILS', 'LOT_ID', $LOT_ID, 'Accounts');
	$SqlUpdate="UPDATE `RM_COSTING_DETAILS` SET `SUBMISSION_DATE`='$SUBMISSION_DATE',
	`EXPANSE`='$Expanse',`DEPB`='$Depb',`EXCHANGE_RATE`='$Exchange'  WHERE LOT_ID='$LOT_ID'";	
	
	$stmt1=$iconn->query($SqlUpdate);
	if($stmt1){
	  legacyAuditLogUpdateById('RM_COSTING_DETAILS', 'LOT_ID', $LOT_ID, $oldCostingDetail, array('EXPANSE'=>$Expanse,'DEPB'=>$Depb,'EXCHANGE_RATE'=>$Exchange), array(), 'Accounts');
	}
	
	
	echo "<script>alert('Success');</script>";
	header('Location: CostingReportPrint.php?id='.$LOT_ID);
	

	}else{
		
		$OTP=generateOtp();
$oldOtpRow = legacyAuditFetchRowById('RM_COSTING_GRADES', 'LOT_ID', $LOT_ID, 'Accounts');
 $sql_org_insert="UPDATE `RM_COSTING_GRADES` SET 
`OTP`='$OTP'
 WHERE LOT_ID='$LOT_ID' and OTP!=0"; 
$stmt1=$iconn->query($sql_org_insert);
if($stmt1){
  legacyAuditLogUpdateById('RM_COSTING_GRADES', 'LOT_ID', $LOT_ID, $oldOtpRow, array('OTP'=>'[set]'), array(), 'Accounts');
} 
	}





	
	
	
	
	
			



 ?>



<!DOCTYPE html>

<html>

<head>

<?php include "../includes/meta.php"; ?>

 </head>

<body class="hold-transition skin-blue sidebar-mini sidebar-collapse fixed">

   

<div class="wrapper">

<?php include "../includes/header.php"; ?>

  <!-- Left side column. contains the logo and sidebar -->

 <?php include "../includes/sidebar.php"; ?>

  <!-- Content Wrapper. Contains page content -->

  <div class="content-wrapper">

    <!-- Content Header (Page header) -->

    <section class="content-header">

        

<h2 align="center">Procurement Details</h2>




	</a>



			</a>

			

			



		<ol class="breadcrumb">

        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>

        <li class="active">Raw Material Purchase Cum Received Note</li>

      </ol>

    </section>

	


	

	



    <!-- Main content -->

    <section class="content">

      <!-- Small boxes (Stat box) -->
	  
	  

	  
	  

    <div id="tdetails" class="table-responsive" >  

        <div class="box">
        <div class="box-body">

		<div class="row">


                 

<table id="bill_details" class="table table-bordered table-hover">
 </table>


 
 
<h2 align="center">Grading  Details </h2>

<div   class="form-group col-sm-12"  align="right"><br>
                <button id="sendOtp"  name='sendOtp' 
				class="btn btn-primary">SEND OTP</button>
				</div>
				
				<hr>
 
 
  <form role="form" method="post">
		  
		  
		 
              <div class="form-group col-sm-12"  >
                  <label for="exampleInputEmail1">OTP</label>
                  <input required type="text" 
				  class="form-control" name='OTP' id="OTP"
				  placeholder="Enter Otp">
                </div> 
			   
				
			
				
				
				 <div class="form-group col-sm-4"  >
                  <label for="exampleInputEmail1">Exchange Rate</label>
                  <input required type="text" 
				  class="form-control" name='Exchange' id="Exchange"
				   value="<?php echo $RowCostingReport['EXCHANGE_RATE'] ?>"
				  placeholder="Exchange Rate">
                </div> 
				
				 <div class="form-group col-sm-4"  >
                  <label for="exampleInputEmail1">Expanse</label>
                  <input required type="text" 
				  class="form-control" name='Expanse' id="Expanse"
				   value="<?php echo $RowCostingReport['EXPANSE'] ?>"
				  placeholder="Expanse/Kg">
                </div> 
				
				 <div class="form-group col-sm-4"  >
                  <label for="exampleInputEmail1">DEPB</label>
                  <input required type="text" 
				  class="form-control" name='Depb' id="Depb"
				   value="<?php echo $RowCostingReport['DEPB'] ?>"
				  placeholder="Enter Depb">
                </div> 
				
				
				
				
				<div class="form-group col-sm-6" >
				 <label id="price_head">GRADES</label>
                 
                </div>
				<div class="form-group col-sm-6" >
				 <label id="price_head">USD</label>
                 
                </div>
				
				<?php 
				
				while($RowCostingGrades=$StmtCostingGrades->fetch_array()){ 
				
				
				
				?>
					
				<div class="form-group col-sm-6" >
				
                  <input readonly required type="text"  class="form-control" 
				  name='GRAD_GRADE<?php echo $RowCostingGrades['ID']; ?>'
				   value="<?php echo $RowCostingGrades['GRADE'] ?>"
				  placeholder="Enter HeadON Quantity" >
                </div>
				
				
				
				
				<div class="form-group col-sm-6">
                 
                  <input  required type="text" class="form-control" 
				  name="GRAD_USD<?php echo $RowCostingGrades['ID']; ?>" 
				  
				  placeholder="Enter Price/Kg" value="<?php echo $RowCostingGrades['USD']; ?>">
				  </div>
			
			<?php }?>
				
				<div class=" hidden form-group col-sm-3" >
                  <label for="exampleInputEmail1">Yield</label>
                  <input  type="text" class="form-control" name='YIELD' id="YIELD"
				  placeholder="Enter Hon Packed" value="0">
                </div>
				
				
			  <div class="col-xs-12 box-footer"  align="right">
                <button type="submit"  name='submit' class="btn btn-primary">Submit</button>
              </div>
            </form>

 
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

<script>

$('document').ready(function(){
	
	var bill_data ='<?php  echo getBillData($iconn,$LOT_ID);?>';

    var bill_data = bill_data.replace(/["']/g, "");
   
   $("#bill_details").html(bill_data); 


$('#sendOtp').click(function() {
	
	
	 var LOT_ID ='<?php echo $LOT_ID; ?>';
	
	$.ajax({
		  url:'../includes/FuncMsg.php',
		  type:'POST',
		  data:{'LOT_ID':LOT_ID,'TYPE':'COST_EDIT'},
		  success:function(data){
			  console.log(data);
					alert(data);
			}
			});	
	
	});	


});


$(document).ajaxStart(function(){
    $("#wait").css("display", "block");
  });
  $(document).ajaxComplete(function(){
    $("#wait").css("display", "none");
  });

    	

</script>





</html>

