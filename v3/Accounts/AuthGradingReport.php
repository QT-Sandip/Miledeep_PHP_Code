<?php
session_start();
include "../includes/config.php";
include "../includes/Globals.php";
include "../includes/FuncMsg.php";
include "../includes/AjaxPurchaseDetails.php";
include "../includes/AjaxGradingDetails.php";

$LOT_ID=$_GET['id'];
$USER_ID=$_SESSION["r_id"];

getHlGradingReport($iconn,$LOT_ID);

$SqlGrading="SELECT * FROM `PPC_FINAL_GRADING_HL` where LOT_ID='$LOT_ID'";
$StmtGrading=$iconn->query($SqlGrading);

$SqlRmBd="SELECT * FROM `PPC_GRADING_HL`  where LOT_ID='$LOT_ID'";
$StmtRmBd=$iconn->query($SqlRmBd);
$RowRmBd=$StmtRmBd -> fetch_array();

if($RowRmBd['STATUS'] !=0){
	
	
	echo "<script>
	alert('Grading Authorised Plesae Refresh List');
	closeCurrentWindow();
	</script>";
	
echo  "<script type='text/javascript'>";
echo "window.close();";
echo "</script>";


	
	//header('Location: AccDash.php');
	
	header('Location: AccDash.php?Msg=Bill Not Created Plesae Refresh Bills');

}

extract($_POST);
if(isset($_POST['submit'])){	

	$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
	$SUBMISSION_DATE=$dateTime->format("Y-m-d  H:i:s"); 
	
	
	$SqlGrading="SELECT * FROM `PPC_FINAL_GRADING_HL` where LOT_ID='$LOT_ID'";
	$StmtGrading=$iconn->query($SqlGrading);
	
	while($RowGrading=$StmtGrading -> fetch_array()){

	$GRAD_WEIGHT=$_POST['GRAD_WEIGHT' . $RowGrading['ID']];
	$GRAD_COUNT=$_POST['GRAD_COUNT' . $RowGrading['ID']];
	$GRAD_ID=$RowGrading['ID'];		
	
	$oldGrading = legacyAuditFetchRowById('PPC_FINAL_GRADING_HL', 'ID', $GRAD_ID, 'Accounts');

	 $SqlUpdate="UPDATE `PPC_FINAL_GRADING_HL` SET 
`COUNT`='$GRAD_COUNT',
`WEIGHT`='$GRAD_WEIGHT' WHERE ID='$GRAD_ID'";	
	
	$stmt1=$iconn->query($SqlUpdate);
	if($stmt1){
	  legacyAuditLogUpdateById('PPC_FINAL_GRADING_HL', 'ID', $GRAD_ID, $oldGrading, array('COUNT'=>$GRAD_COUNT,'WEIGHT'=>$GRAD_WEIGHT), array(), 'Accounts');
	}
	
	}
	
	$oldStatus = legacyAuditFetchRowById('PPC_GRADING_HL', 'LOT_ID', $LOT_ID, 'Accounts');
	$SqlGrading="UPDATE `PPC_GRADING_HL` SET `STATUS`='1' WHERE LOT_ID='$LOT_ID'";
	$StmtGrading=$iconn->query($SqlGrading);
	if($StmtGrading){
	  legacyAuditLogUpdateById('PPC_GRADING_HL', 'LOT_ID', $LOT_ID, $oldStatus, array('STATUS'=>'1'), array(), 'Accounts');
	}
	if($StmtGrading){
	echo "<script>alert('Success');
	
	window.close();</script>";
		
		
		
	}else{
		echo "<script>alert('Unable To Authorise');window.close();</script>";
		
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


 
 
  <form role="form" method="post">
		  
		  
			   
				
				
				
				
				<div class="form-group col-sm-6" >
				 <label id="price_head">Count</label>
                 
                </div>
				<div class="form-group col-sm-6" >
				 <label id="price_head">Weight</label>
                 
                </div>
				
				<?php 
				
				$WTOTAL=0;
				while($RowGrading=$StmtGrading->fetch_array()){ 
				$AryDef[]=$RowGrading;
				$WTOTAL+=$RowGrading['WEIGHT'];
				
				?>
					
				<div class="form-group col-sm-6" >
				
                  <input required type="text"  class="form-control" 
				  name='GRAD_COUNT<?php echo $RowGrading['ID']; ?>'
				   value="<?php echo $RowGrading['COUNT'] ?>"
				  placeholder="Enter HeadON Quantity" >
                </div>
				
				
				
				
				<div class="form-group col-sm-6">
                 
                  <input required type="text" class="form-control" 
				  name="GRAD_WEIGHT<?php echo $RowGrading['ID']; ?>" 
				  
				  placeholder="Enter Price/Kg" value="<?php echo $RowGrading['WEIGHT']; ?>">
				  </div>
			
			<?php }?>
				
				<div class="  form-group col-sm-6" >
				 <input readonly  type="text" class="form-control" name='YIELD' id="YIELD"
				   value="Total HeadLess Weight">
                   <label for="exampleInputEmail1"></label>
                </div>
				<div class="  form-group col-sm-6" >
                 
                  <input readonly type="text" class="form-control" name='YIELD' id="YIELD"
				  placeholder="Enter Hon Packed" value="<?php echo  $WTOTAL ?>">
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
		  data:{'LOT_ID':LOT_ID,'TYPE':'GRAD_EDIT'},
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

