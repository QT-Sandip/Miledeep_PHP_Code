<?php
session_start();
include "../includes/config.php";
include "../includes/Globals.php";

$LOT_ID=$_GET['id'];
$USER_ID=$_SESSION["r_id"];

$table= getCostingTable($iconn,$LOT_ID);




   $sql_count="SELECT  HOSO_W_DATA FROM `PPC_HOSO_W_DATA` where LOT_ID='$LOT_ID'";
 $stmt_count=$iconn->query($sql_count);

        $r_count = $stmt_count->fetch_array();


   $json=$r_count['HOSO_W_DATA'];
	 $counts=json_decode($json,true);
					
	 foreach($counts as $value){
		 				$ary_samples[]=$value['Count'];  // Colecting all weightments as single arrary	


		 $count=$value['Count']; 

		if( !in_array( $count ,$arycount ) )
{
	$arycount[]=$count;
	
}
	}
print_r ($arycount);
		 
			foreach($ARY_COSTING_RATES as $ROW_COSTING){
				
				 $GRADE_SLAB;
					$weight_sum=0;	
					$range=explode("/",$ROW_COSTING['COUNT']);
					
					 $start=(int) $range[0];
					$end=(int) $range[1];
			
		foreach($ary_counts  as $count){		
						$count=(int) $count;
		if($count >= $start  && $count <=$end){			
			
			$weight_sum=0;
			
			  $ROW_COSTING['COUNT'];
			foreach($ary_samples  as $value){						
			if($value['Count'] == $count){
				 $weight_sum=$weight_sum + $value['NetWeight'];
			}						
			}
			
			$SALE_PRICE=$weight_sum*$ROW_COSTING['USD']*$exchange_rate;
			
			$ary_final_samples_per_grade_each= array();
			if($weight_sum!=0){
				
					$ary_final_samples_per_grade_each[]=$new_str = preg_replace("/\s+/", "",$ROW_COSTING['COUNT']);;
				$ary_final_samples_per_grade_each[]=$weight_sum;
				$ary_final_samples_per_grade_each[]=$ROW_COSTING['USD'];
				$ary_final_samples_per_grade_each[]=$SALE_PRICE;
				
				$ary_final_samples_per_grade[]=$ary_final_samples_per_grade_each;
				}
				
		}
		}
				
}


extract($_POST);
if(isset($_POST['submit'])){	
	
	//$D_O_ANALYSIS = date("d-m-Y", strtotime($D_O_ANALYSIS));
	$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
		$SUBMISSION_DATE=$dateTime->format("Y-m-d  H:i:s"); 
	//$SUBMISSION_DATE = now();
	
	$sql_org_insert="INSERT INTO `PPC_PRICING_AND_WEIGHT`( 
	`LOT_ID`, `USER_ID`, `SUBMISSION_DATE`, `RATE`,`ACC_ADD`,`ACC_ADD_REMARKS`,
					`ACC_DEDUCTION`, `EXPANSE`, `DEPB`, `GRADING_COUNT`,`HON_QTY`, `EXCHANGE_RATE`) VALUES ('$LOT_ID','$USER_ID','$SUBMISSION_DATE',
					'$RATE','$ACC_ADD','$ACC_ADD_REMARKS','$ACC_DEDUCTION','$EXPANSE','$DEPB','$GRADING_COUNT','$HON_WEIGHT','$EXCHANGE_RATE')";	
//	echo $sql_org_insert; exit;

	$stmt1=$iconn->query($sql_org_insert);
//	$M_T_REPORT_ID = $iconn->insert_id;

	if($stmt1){
	$insertId = $iconn->insert_id;
	legacyAuditLogInsertById('PPC_PRICING_AND_WEIGHT', 'ID', $insertId, array('LOT_ID' => $LOT_ID, 'USER_ID' => $USER_ID), array(), 'Accounts');

	$oldRow = legacyAuditFetchRowById('PPC_HOSO_W_DATA', 'LOT_ID', $LOT_ID, 'Accounts');
	$sql_org_insert="UPDATE `PPC_HOSO_W_DATA` SET STATUS=1 WHERE LOT_ID='$LOT_ID'";	
	

	$stmt1=$iconn->query($sql_org_insert);
	if($stmt1){
	    legacyAuditLogUpdateById('PPC_HOSO_W_DATA', 'LOT_ID', $LOT_ID, $oldRow, array('STATUS' => 1), array(), 'Accounts');
	}
		echo "<script>alert('Success');</script>";
		header('Location: CostingReportPrint.php?id='.$LOT_ID);
	}

	}


$sql="SELECT * FROM `inputmaterial_details` where id='$LOT_ID'";
$stmt=$iconn->query($sql);
$post_details=$stmt->fetch_array();

$sql="SELECT * FROM `PURCHASE_DETAILS` where LOT_ID='$LOT_ID'";
$stmt=$iconn->query($sql);
$bill_details=$stmt->fetch_array();


	
	
	
	
	
			



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

	
	
	<div class="modal fade" id="myModal" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title modheading"></h4>
            </div>
            <div class="modal-body">
			
                
				
						
						
						 <table id="example1" class="table table-bordered table-hover">

              

							<thead class="process_dt_dt" id="process_dt_dt">

                                
								
								</thead>
								
								</table>
		
								
				
				
				
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

	

	



    <!-- Main content -->

    <section class="content">

      <!-- Small boxes (Stat box) -->
	  
	  

	  
	  

    <div id="tdetails" class="table-responsive" >  

        <div class="box">

          

        <div class="box-body">

		

		

		

 <div class="row">



    			<div class="col-xs-12">
    			


					
					
					
					  <table id="example1" class="table table-bordered table-hover">

              

							<thead>

                                <tr>
								
								<th><strong>Purchase Date</strong></th>
								<th><strong>LOT Number</strong></th>
        							
								<th><strong>Supervisor Name</strong></th>
								<th><strong>Type</strong></th>
								<th><strong>Count</strong></th>
								<th><strong>Rate</strong></th>
								<th><strong>HON Qty (Kgs)</strong></th>
								<th><strong>Amount</strong></th>
								
								</tr>
								
									</thead>
									
										<tbody>
									<td><?php echo $post_details['PURCHASE_DATE']; ?></td>
								
								
        							
									<td><?php echo $post_details['LOT_NO']; ?></td>
								
									
                                	
									<td><?php echo $post_details['SUPERVISOR']; ?></td>
									<td><?php echo $post_details['PURCHASE_TYPE']; ?></td>
									<td><?php echo $bill_details['COUNT']; ?></strong></td>
									<td><?php echo $bill_details['RATE']; ?></strong></td>
									<td><?php echo $bill_details['HON_USED']+$bill_details['HON_GRADED']; ?></strong></td>
									<td><?php echo ($bill_details['HON_USED']+$bill_details['HON_GRADED'])* $bill_details['RATE'] ; ?></strong></td>
									
                                
									
									
                                
								</tr>
								
								
									</tbody>
							
								
								</table>

    				</div>
</div>


		
<!--<div class="col-xs-12"> 

<th><span></span></th>
<h2 align="center">Purchase Rates <a target='_blank' class="btn btn-primary" href='../AddPurchaseRates.php'>Update </a>  </h2>
<th><strong></strong></th>

</div>  -->
                 

  <table id="purchase_rates_table" class="table table-bordered table-hover">
 </table>
 

 
 <div class="form-group col-sm-2 " >
                  <label for="exampleInputEmail1">Yield</label>
                  <input required type="text" class="form-control"  id="yield_v"
				  placeholder="Enter Yield%" >
                </div>
				
				<div class="form-group col-sm-1 " >
                  <label for="exampleInputEmail1"></label>
                  <Button required type="text"class="form-control" id="yield" >GET</button>
                </div>
<th><span></span></th>
<h2 align="center">Grading  Details </h2>
<th><strong></strong></th>

<div id="wait" style="display:none;width:69px;height:89px;border:1px solid black;position:absolute;top:20%;left:50%;padding:2px;">
		  <img src='https://www.w3schools.com/jquery/demo_wait.gif' width="64" height="64" /><br>Loading..</div>
		
  <table id="grade_table" class="table table-bordered table-hover">
 </table>
 
   
   <div class="box">
		
		
          <form role="form" method="post">
		  
		  
		  	
		  
              
			    <div class="box-body">
               
			   <div class="form-group col-sm-3 " >
                  <label for="exampleInputEmail1">Hon Quantity</label>
                  <input required type="text" class="form-control" name='HON_WEIGHT' id="HON_WEIGHT"
				  placeholder="Enter HeadON Quantity" >
                </div>
				
				<div class="form-group col-sm-3 "  >
                  <label for="exampleInputEmail1">Hon Count</label>
                  <input required type="text" class="form-control" name='GRADING_COUNT' id="GRADING_COUNT"
				  placeholder="Enter HeadON Count">
                </div>  
				
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Price /Kg</label>
                  <input required type="text" class="form-control"  name="RATE" id="RATE" 
				  placeholder="Enter Price/Kg">
                </div>
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Exchange Rate</label>
                  <input required type="text" class="form-control" VALUE="71" name="EXCHANGE_RATE" id="EXCHANGE_RATE" 
				  placeholder="Enter Exchange Rate">
                </div>
				
				
				
				<div class="form-group col-sm-3">
                 <label for="exampleInputPassword1">Deduction/Kgs</label>
                  <input type="text" class="form-control"  name="ACC_DEDUCTION" id="ACC_DEDUCTION" 
				  placeholder="Enter Deduction/Kgs">
                </div>
				
				<div class="form-group col-sm-3">
                 <label for="exampleInputPassword1">Additions/kg</label>
                  <input type="text" class="form-control"  name="ACC_ADD" id="ACC_ADD" 
				  placeholder="Enter Deduction/Kgs">
                </div>
				
				<div class="form-group col-sm-3">
                 <label for="exampleInputPassword1">Additions Remarks</label>
                  <input type="text" class="form-control"  name="ACC_ADD_REMARKS" id="ACC_ADD_REMARKS" 
				  placeholder="Enter Deduction/Kgs">
                </div>
				
				<div class="form-group col-sm-3">
                 <label for="exampleInputPassword1">Expanse</label>
                  <input required type="text" class="form-control" VALUE="80" name="EXPANSE" id="EXPANSE" 
				  placeholder="Enter expenses">
                </div>
				
				<div class="form-group col-sm-3">
                 <label for="exampleInputPassword1">DEPB</label>
                  <input required type="text" class="form-control" VALUE="9" name="DEPB" id="DEPB" 
				  placeholder="Enter DEPB ">
                </div>
				
				
				 <div class="col-xs-12"> 

<th><span> </span></th>
<h2 align="center">Costing  Details  <a target='_blank' class="btn btn-primary" href='../AddCostingRates.php'>Update </a> </h2>


</div>

       	

  <table id="costing_table" class="table table-bordered table-hover">
 </table>
				
		 		 <div  class="col-xs-12">    

				<table  class="table table-bordered table-hover">
 	<h2 align="center">Grades </h2>
				
				<tr> 
				<th> Grade</th>
				<th>price</th>
				</tr>
				
				
				<tr>
				<td><?php echo $count?>
  </td>
				
				<td></td>

				
				</tr>
</table>				

		
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
	
	
   var data ='<?php echo json_encode(getHlGradingReport($iconn,$LOT_ID)); ?>';
   var dataa ='<?php  echo getCostingTable($iconn,$LOT_ID);?>';
   var dataaa ='<?php echo  getPurchaseRates($iconn,$LOT_ID) ;?>';
   
   var data = data.replace("[", "");
   var data = data.replace("]", "");
   var data = data.replace(/["']/g, "");
  
  var dataa = dataa.replace(/["']/g, "");
  var dataaa = dataaa.replace(/["']/g, "");
   
   var strArray = data.split(",");
  // var test = JSON.parse(data);
   //alert(test);
  
   $("#HON_WEIGHT").val(strArray[0]);
   $("#GRADING_COUNT").val(strArray[1]);
   $("#RATE").val(strArray[3]);
   $("#grade_table").html(strArray[2]);
   $("#costing_table").html(dataa);
  // $("#purchase_rates_table").html(dataaa);
  //console.log('test');`
  console.log(data);  


$('#yield').click(function() {
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
});


$(document).ajaxStart(function(){
    $("#wait").css("display", "block");
  });
  $(document).ajaxComplete(function(){
    $("#wait").css("display", "none");
  });

    	

</script>





</html>

