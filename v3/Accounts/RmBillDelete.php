<?php
session_start();
include "../includes/config.php";
include "../includes/FuncMsg.php";
include "../includes/Globals.php";
include "../includes/AjaxPurchaseDetails.php";
include "../includes/AjaxGradingDetails.php";

$LOT_ID=$_GET['id'];
$USER_ID=$_SESSION["r_id"];

 //getHlGradingReport($iconn,$LOT_ID);





$SqlRmBd="SELECT * FROM `RM_BILL_DETAILS`  where LOT_ID='$LOT_ID'";
$StmtRmBd=$iconn->query($SqlRmBd);
$RowRmBd=$StmtRmBd -> fetch_array();

$SqlRmd="SELECT * FROM `inputmaterial_details` where id='$LOT_ID'";
$StmtRmd=$iconn->query($SqlRmd);
$RowImd=$StmtRmd -> fetch_array();

$SqlHonGrading="SELECT * FROM `PPC_GRADING_HON` WHERE LOT_ID='$LOT_ID'";
$StmtHonGrading=$iconn->query($SqlHonGrading);
$RowHonGrading=$StmtHonGrading->fetch_array();

//echo $RowRmBd['LOT_ID'];

if($RowRmBd['LOT_ID'] ==0){


	echo "<script>
	alert('Bill Not Created Plesae Refresh Bills');
	closeCurrentWindow();
	</script>";

echo  "<script type='text/javascript'>";
echo "window.close();";
echo "</script>";



	//header('Location: AccDash.php');

	header('Location: AccDash.php?Msg=Bill Not Created Plesae Refresh Bills');

}else{



}




// echo getHlGradingReport($iconn,$LOT_ID);

// exit;


extract($_POST);
if(isset($_POST['submit'])){

	$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
	$SUBMISSION_DATE=$dateTime->format("Y-m-d  H:i:s");

	  $SqlOtpVerify="SELECT * FROM `RM_BILL_DETAILS` WHERE LOT_ID='$LOT_ID'";
	$StmtOtpVerify=$iconn->query($SqlOtpVerify);
	$RowVerify=$StmtOtpVerify -> fetch_array();
	  $RowVerify['OTP'];
	if($OTP==$RowVerify['OTP']){



	 $oldRowRmBd = legacyAuditFetchRowById('RM_BILL_DETAILS', 'LOT_ID', $LOT_ID, 'Accounts');
	 $SqlBillDelete="DELETE FROM  `RM_BILL_DETAILS` WHERE LOT_ID='$LOT_ID'";
   $StmtBillDelete=$iconn->query($SqlBillDelete);
   if($StmtBillDelete){
       legacyAuditLogDeleteById('RM_BILL_DETAILS', 'LOT_ID', $LOT_ID, $oldRowRmBd, array('LOT_ID' => $LOT_ID), array(), 'Accounts');
   }

   $oldRowRmDef = legacyAuditFetchRowById('RM_DEFECTS_DETAILS', 'LOT_ID', $LOT_ID, 'Accounts');
   $SqlRmDefects="DELETE FROM `RM_DEFECTS_DETAILS` where LOT_ID='$LOT_ID'";
   $StmtDefects=$iconn->query($SqlRmDefects);
   if($StmtDefects){
       legacyAuditLogDeleteById('RM_DEFECTS_DETAILS', 'LOT_ID', $LOT_ID, $oldRowRmDef, array('LOT_ID' => $LOT_ID), array(), 'Accounts');
   }

   $oldRowHon = legacyAuditFetchRowById('PPC_GRADING_HON', 'LOT_ID', $LOT_ID, 'Accounts');
   $SqlHeadOn="DELETE FROM `PPC_GRADING_HON` WHERE LOT_ID='$LOT_ID'";
   $StmtHeadon=$iconn->query($SqlHeadOn);
   if($StmtHeadon){
       legacyAuditLogDeleteById('PPC_GRADING_HON', 'LOT_ID', $LOT_ID, $oldRowHon, array('LOT_ID' => $LOT_ID), array(), 'Accounts');
   }

   $oldRowHl = legacyAuditFetchRowById('PPC_GRADING_HL', 'LOT_ID', $LOT_ID, 'Accounts');
   $SqlHeadOn="DELETE FROM `PPC_GRADING_HL` WHERE LOT_ID='$LOT_ID'";
   $StmtHeadon=$iconn->query($SqlHeadOn);
   if($StmtHeadon){
       legacyAuditLogDeleteById('PPC_GRADING_HL', 'LOT_ID', $LOT_ID, $oldRowHl, array('LOT_ID' => $LOT_ID), array(), 'Accounts');
   }

   $oldRowFinalHl = legacyAuditFetchRowById('PPC_FINAL_GRADING_HL', 'LOT_ID', $LOT_ID, 'Accounts');
   $SqlHeadOn="DELETE FROM `PPC_FINAL_GRADING_HL` WHERE LOT_ID='$LOT_ID'";
   $StmtHeadon=$iconn->query($SqlHeadOn);
   if($StmtHeadon){
       legacyAuditLogDeleteById('PPC_FINAL_GRADING_HL', 'LOT_ID', $LOT_ID, $oldRowFinalHl, array('LOT_ID' => $LOT_ID), array(), 'Accounts');
   }

   $oldRowCostDet = legacyAuditFetchRowById('RM_COSTING_DETAILS', 'LOT_ID', $LOT_ID, 'Accounts');
   $SqlHeadOn="DELETE FROM `RM_COSTING_DETAILS` WHERE LOT_ID='$LOT_ID'";
   $StmtHeadon=$iconn->query($SqlHeadOn);
   if($StmtHeadon){
       legacyAuditLogDeleteById('RM_COSTING_DETAILS', 'LOT_ID', $LOT_ID, $oldRowCostDet, array('LOT_ID' => $LOT_ID), array(), 'Accounts');
   }

   $oldRowCostGrd = legacyAuditFetchRowById('RM_COSTING_GRADES', 'LOT_ID', $LOT_ID, 'Accounts');
   $SqlHeadOn="DELETE FROM `RM_COSTING_GRADES` WHERE LOT_ID='$LOT_ID'";
   $StmtHeadon=$iconn->query($SqlHeadOn);
   if($StmtHeadon){
       legacyAuditLogDeleteById('RM_COSTING_GRADES', 'LOT_ID', $LOT_ID, $oldRowCostGrd, array('LOT_ID' => $LOT_ID), array(), 'Accounts');
   }





		echo "<script>alert('Success');</script>";
		header('Location: AccDash.php?Msg=Bill Updated Please Refresh Bills');
	}else{
		echo "<script>alert('Wrong Otp');</script>";
	}

	}else{

		$OTP=generateOtp();
 $oldRowOtp = legacyAuditFetchRowById('RM_BILL_DETAILS', 'LOT_ID', $LOT_ID, 'Accounts');
 $sql_org_insert="UPDATE `RM_BILL_DETAILS` SET
`OTP`='$OTP'
 WHERE LOT_ID='$LOT_ID' and OTP!=0";
$stmt1=$iconn->query($sql_org_insert);
if($stmt1){
    legacyAuditLogUpdateById('RM_BILL_DETAILS', 'LOT_ID', $LOT_ID, $oldRowOtp, array('OTP' => $OTP), array(), 'Accounts');
}
	}


$sql="SELECT * FROM `inputmaterial_details` where id='$LOT_ID'";
$stmt=$iconn->query($sql);
$post_details=$stmt->fetch_array();

 $sql="SELECT * FROM `RM_DEFECTS_DETAILS` where LOT_ID='$LOT_ID'";
$STMT_DEFECTS=$iconn->query($sql);












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



    			<div class="col-xs-12">






					    <table id="bill_details" class="table table-bordered table-hover">
 </table>

    				</div>
</div>



<!--<div class="col-xs-12">

<th><span></span></th>
<h2 align="center">Purchase Rates <a target='_blank' class="btn btn-primary" href='../AddPurchaseRates.php'>Update </a>  </h2>
<th><strong></strong></th>

</div>  -->





<h2 align="center">Grading  Details </h2>
<th><strong></strong></th>

 <table id="grade_table" class="table table-bordered table-hover">
 </table>

  <h2 align="center">Bill  Details </h2>


   <div class="box">

				<div   class="form-group col-sm-12"  align="right"><br>
                <button id="sendOtp"  name='sendOtp'
				class="btn btn-primary">Send Otp</button>
				</div>

				<hr>

          <form role="form" method="post">




			    <div class="box-body"><br>

				<div class="form-group col-sm-6"  >
                  <label for="exampleInputEmail1">OTP</label>
                  <input required type="text"
				  class="form-control" name='OTP' id="OTP"
				  placeholder="Enter Otp">
                </div>



				<div class="form-group col-sm-6"  >
                  <label for="exampleInputEmail1">Bill Name</label>
                  <input required type="text" value="<?php echo $RowImd['BILL_NAME'] ?>"
				  class="form-control" name='BILL_NAME' id="BILL_NAME"
				  placeholder="Enter Bill Name">
                </div>


				<div class="form-group col-sm-12"  >
                  <label for="exampleInputEmail1">Remarks</label>
                  <input required type="text" class="form-control"
				  name='REMARKS' id="REMARKS" value="<?php echo $RowRmBd['REMARKS'] ?>"
				  placeholder="Enter Remarks">
                </div>




			   <div class="form-group col-sm-4" >
                  <label for="exampleInputEmail1">Hon Quantity</label>
                  <input readonly  required type="text" class="form-control"
				  name='WEIGHT' id="WEIGHT"
				  placeholder="Enter HeadON Quantity" >
                </div>


				<div class="form-group col-sm-4"  >
                  <label for="exampleInputEmail1">Hon Count</label>
                  <input readonly required type="text" class="form-control" name='COUNT' id="COUNT"
				  placeholder="Enter HeadON Count">
                </div>


				<div class="form-group col-sm-4">
                  <label for="exampleInputPassword1">Price /Kg</label>
                  <input readonly required type="text" class="form-control"
				  name="RATE" id="RATE"
				  placeholder="Enter Price/Kg">
                </div>

			<!--------------- Head On Packing ----------------------------->


<?php if($RowHonGrading!=0){			?>

				<div class="form-group col-sm-4" >
                  <label for="exampleInputEmail1">Packed Quantity</label>
                  <input readonly  required type="text" class="form-control"
				  name='HONPACKED' id="HONPACKED"
				  placeholder="Enter HeadON Packed Quantity" >
                </div>


				<div class="form-group col-sm-4"  >
                  <label for="exampleInputEmail1">Packed Count</label>
                  <input readonly required type="text" class="form-control" name='HONPACKEDCOUNT' id="HONPACKEDCOUNT"
				  placeholder="Enter Head ON Packed Count">
                </div>


				<div class="form-group col-sm-4">
                  <label for="exampleInputPassword1">Packed Price /Kg</label>
                  <input readonly required type="text" class="form-control"
				  name="HONPACKEDRATE" id="HONPACKEDRATE"
				  placeholder="Enter Head On Packed Price/Kg">
                </div>

				 <!--------------- Head On Packing ----------------------------->


<?php } ?>


				<?php

				while($DEFECTS_ROW=$STMT_DEFECTS -> fetch_array()){
				$AryDef[]=$DEFECTS_ROW;


				?>

					<div class="form-group col-sm-6" >
                  <label for="exampleInputEmail1"><?php echo $DEFECTS_ROW['NAME'] .' (Kgs)'?></label>
                  <input required type="text" readonly class="form-control"
				  name='DEF_WEIGHT<?php echo $DEFECTS_ROW['ID']; ?>'
				  id="DEF_WEIGHT" value="<?php echo $DEFECTS_ROW['WEIGHT'] ?>"
				  placeholder="Enter HeadON Quantity" >
                </div>




				<div class="form-group col-sm-6">
                  <label id="price_head">Price /Kg</label>
                  <input required type="number" class="form-control"
				  name="DEF_RATE<?php echo $DEFECTS_ROW['ID']; ?>"
				  id="DEF_RATE"
				  placeholder="Enter Price/Kg" value="<?php echo $DEFECTS_ROW['RATE'] ?>">
				  </div>
			<!-- -->
			<?php }?>


				<div class="form-group col-sm-6">
                  <label for="exampleInputPassword1">Addition/Deduction Per Kg</label>
                  <input  required type="number" step="any" class="form-control"
				  name="DEDUCTIONS" id="DEDUCTIONS" value="<?php echo $RowRmBd['DEDUCTIONS'] ?>"
				  placeholder="Enter Price/Kg">
                </div>

				<div class="  form-group col-sm-6" >
                  <label for="exampleInputEmail1">Yield</label>
                  <input  readonly type="text" class="form-control" name='YIELD' id="YIELD"
				  placeholder="Enter Hon Packed" value="0">
                </div>


			  <div class="col-xs-12 box-footer"  align="right">
                <button type="submit"  name='submit' class="btn btn-danger">Delete</button>
              </div>
            </form>



</div>


</div>







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


<script>

$('document').ready(function(){


   var grading_table ='<?php echo json_encode(getHlGradingReport($iconn,$LOT_ID)); ?>';
   var bill_data ='<?php  echo getBillData($iconn,$LOT_ID);?>';


   var grading_table = grading_table.replace("[", "");
   var grading_table = grading_table.replace("]", "");
   var grading_table = grading_table.replace(/["']/g, "");
   var grading_table = grading_table.split(",");


  var bill_data = bill_data.replace(/["']/g, "");

    $("#bill_details").html(bill_data);
  alert(grading_table[4]);
   $("#WEIGHT").val(grading_table[0]);
   $("#COUNT").val(grading_table[1]);
   $("#grade_table").html(grading_table[2]);
   $("#RATE").val(grading_table[3]);
  // $("#price_head").value(grading_table[3]);

  // document.getElementById('price_head').innerHTML = 'Price/Kg ( '+ grading_table[4] +' )';
   var S_YIELD =$('#S_yield').text();
   $("#YIELD").val(grading_table[5]);
    if(grading_table[6]!=0){
		alert('Yield Error');
   }
   $("#HONPACKED").val(grading_table[7]);
   $("#HONPACKEDCOUNT").val(grading_table[8]);
   $("#HONPACKEDRATE").val(grading_table[9]);



  //console.log('test');`
 // console.log(dataaa);




$('#sendOtp').click(function() {


	 var LOT_ID ='<?php echo $LOT_ID; ?>';

	$.ajax({
		  url:'../includes/FuncMsg.php',
		  type:'POST',
		  data:{'LOT_ID':LOT_ID,'TYPE':'BILL_DELETE'},
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
