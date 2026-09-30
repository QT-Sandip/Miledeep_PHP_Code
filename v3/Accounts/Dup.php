<?php
session_start();
include "../includes/config.php";
include "../includes/Globals.php";
include "../includes/FuncGradingDetails.php";
include "AjaxCostingTable.php";

$LOT_ID=$_GET['id'];
$USER_ID=$_SESSION["r_id"];

// getHlGradingReport($iconn,$LOT_ID);
 // exit;
 
 

 $SqlRmBd="SELECT count(*) as TOTAL FROM `RM_BILL_DETAILS` where LOT_ID='$LOT_ID'";
$StmtRmBd=$iconn->query($SqlRmBd);
$RowRmBd=$StmtRmBd -> fetch_array();

if($RowRmBd['TOTAL'] !=0){
	
	
	echo "<script>
	alert('Bill Already Created Plesae Refresh Bills');
	closeCurrentWindow();
	</script>";
	
echo  "<script type='text/javascript'>";
echo "window.close();";
echo "</script>";


	
	//header('Location: AccDash.php');
	
	header('Location: AccDash.php?Msg=Bill Already Created Plesae Refresh Bills');

}




//echo getHlGradingReport($iconn,$LOT_ID);

//exit;
extract($_POST);
if(isset($_POST['submit'])){	
	
	$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
	$SUBMISSION_DATE=$dateTime->format("Y-m-d  H:i:s"); 
	
	
	
	

	
	 $sql_org_insert="INSERT INTO `RM_BILL_DETAILS`
	(`LOT_ID`, `USER_ID`, `SUBMISSION_DATE`,
	`WEIGHT`, `COUNT`, `RATE`,`DEDUCTIONS`, `REMARKS` ,`YIELD`)
	VALUES ('$LOT_ID','$USER_ID','$SUBMISSION_DATE','$WEIGHT','$COUNT','$RATE','$DEDUCTIONS','$REMARKS','$YIELD')";	
					

	$stmt1=$iconn->query($sql_org_insert); 
	if($stmt1){
	$insertId = $iconn->insert_id;
	legacyAuditLogInsertById('RM_BILL_DETAILS', 'ID', $insertId, array('LOT_ID' => $LOT_ID, 'USER_ID' => $USER_ID), array(), 'Accounts');
	
	
$sql="SELECT * FROM `RM_DEFECTS_DETAILS` where LOT_ID='$LOT_ID'";
$STMT_DEFECTS=$iconn->query($sql);
while($DEFECTS_ROW=$STMT_DEFECTS -> fetch_array()){ 

$DEF_WEIGHT=$_POST['DEF_WEIGHT' . $DEFECTS_ROW['ID']];
$DEF_RATE=$_POST['DEF_RATE' . $DEFECTS_ROW['ID']];
$DEF_ID=$DEFECTS_ROW['ID'];

	$oldRowDef = legacyAuditFetchRowById('RM_DEFECTS_DETAILS', 'ID', $DEF_ID, 'Accounts');
		$sql_org_insert="UPDATE `RM_DEFECTS_DETAILS` SET 
`WEIGHT`='$DEF_WEIGHT',
`RATE`='$DEF_RATE',
`STATUS`='1' WHERE ID='$DEF_ID'";	
	
	$stmt1=$iconn->query($sql_org_insert);
	if($stmt1){
	    legacyAuditLogUpdateById('RM_DEFECTS_DETAILS', 'ID', $DEF_ID, $oldRowDef, array('WEIGHT' => $DEF_WEIGHT, 'RATE' => $DEF_RATE, 'STATUS' => '1'), array(), 'Accounts');
	}
	}
	
	


		echo "<script>alert('Success');</script>";
		header('Location: CostingPreview.php?id='.$LOT_ID);
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
		
		
          <form role="form" method="post">
		  
		  
		 
              
			    <div class="box-body"><br>
				
				<div class="form-group col-sm-12"  >
                  <label for="exampleInputEmail1">Remarks</label>
                  <input required type="text" class="form-control" name='REMARKS' id="REMARKS"
				  placeholder="Enter Remarks">
                </div>  
				
               
			   <div class="form-group col-sm-6" >
                  <label for="exampleInputEmail1">Hon Quantity</label>
                  <input readonly required type="text" class="form-control" name='WEIGHT' id="WEIGHT"
				  placeholder="Enter HeadON Quantity" >
                </div>
				
				
				
				
				<div class="form-group col-sm-6">
                  <label for="exampleInputPassword1">Price /Kg</label>
                  <input readonly required type="text" class="form-control"  name="RATE" id="RATE" 
				  placeholder="Enter Price/Kg">
                </div>
				
				<div class="form-group col-sm-6"  >
                  <label for="exampleInputEmail1">Hon Count</label>
                  <input readonly required type="text" class="form-control" name='COUNT' id="COUNT"
				  placeholder="Enter HeadON Count">
                </div>  
				
				
				<div class="form-group col-sm-6">
                  <label for="exampleInputPassword1">Addition/Deduction Per Kg</label>
                  <input required type="number" step="any" class="form-control"  name="DEDUCTIONS" id="DEDUCTIONS" 
				  placeholder="Enter Price/Kg">
                </div>
				
				
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
                  <input required type="text" class="form-control" 
				  name="DEF_RATE<?php echo $DEFECTS_ROW['ID']; ?>" 
				  id="DEF_RATE" 
				  placeholder="Enter Price/Kg" value="">
				  </div>
			<!--<?php echo $DEFECTS_ROW['RATE'] ?> -->
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
						
						
						

    		
  						

<div>



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
	

   var grading_table ='<?php echo json_encode(getHlGradingReport($iconn,$LOT_ID)); ?>';
   var bill_data ='<?php  echo getBillData($iconn,$LOT_ID);?>';
   var costing_table ='<?php echo  getCosingData($iconn,$LOT_ID) ;?>';
   
   var grading_table = grading_table.replace("[", "");
   var grading_table = grading_table.replace("]", "");
   var grading_table = grading_table.replace(/["']/g, "");
   var grading_table = grading_table.split(",");
   
  var costing_table = costing_table.replace(/["']/g, "");
  var bill_data = bill_data.replace(/["']/g, "");
   
 
  alert(grading_table[4]);
   $("#WEIGHT").val(grading_table[0]);
   $("#COUNT").val(grading_table[1]);
   $("#grade_table").html(grading_table[2]);
   $("#RATE").val(grading_table[3]);
  // $("#price_head").value(grading_table[3]);
  
  // document.getElementById('price_head').innerHTML = 'Price/Kg ( '+ grading_table[4] +' )';
   var S_YIELD =$('#S_yield').text();
   $("#YIELD").val(S_YIELD);
  
  
  // $("#costing_table").html(dataa);
   $("#purchase_rates_table").html(costing_table);
   $("#bill_details").html(bill_data);
  //console.log('test');`
 // console.log(dataaa);  


$('#yield').click(function() {
	alert('hii ');
	var LOT_ID ='<?php  echo $LOT_ID ?>';
	var YIELD =$('#yield_v').val();
	var S_YIELD =$('#S_yield').text();
	
	if(YIELD < S_YIELD){
	alert("Yield Value Less Than Standard Yield");
	}
	
	
	$.ajax({
			  	url:'../includes/Globals.php',
			  	type:'POST',
			  	data:{'YIELD':YIELD ,'LOT_ID' :LOT_ID},
			  	success:function(data){
//console.log(data);   	
	 var strArray = JSON.parse(data);
  
  
   $("#HON_WEIGHT").val(strArray[0]);
   $("#GRADING_COUNT").val(strArray[1]);
   $("#RATE").val(strArray[3]);
   
   $("#grade_table").html(strArray[2]);
 //  console.log(strArray[2]);
				}
			});		
	
	  
	
});

$('#update_costing').click(function() {
	 
		var SIZE =$('#SIZE').val();	  
		// alert(SIZE);
			var USD_ARY = [];
			var GRADE_ID_ARY= [];
			var WEIGHT_ARY= [];
		 for (i = 0; i < SIZE; i++) {
			  
			   var WEIGHT_ID='#WEIGHT_ID'+i;
			   var WEIGHT=$(WEIGHT_ID+"").val();
			//  alert(WEIGHT);
			   
			   var USD_ID='#USD'+i;
			   var USD=$(USD_ID+"").val();
				
			   var GRADE_ID='#GRADE_ID'+i;
			   var GRADE=$(GRADE_ID+"").val();
				
				USD_ARY.push(USD);
				GRADE_ID_ARY.push(GRADE);
				WEIGHT_ARY.push(WEIGHT);
			}
			
			//console.log(USD_ARY);
			//console.log(GRADE_ID_ARY);

	 
		$.ajax({
		  url:'AjaxUpdateUsd.php',
		  type:'POST',
		  data:{'GRADES':GRADE_ID_ARY,'RATES':USD_ARY,'WEIGHTS' : WEIGHT_ARY},
		  success:function(data){
			  console.log(data);
			 //  window.location = "/Accounts/HlGrading.php";
			  
			 //$("#CODE_LIST").html(data);
			 alert("DONE");
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

