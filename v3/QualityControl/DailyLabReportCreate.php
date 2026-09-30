<?php


session_start();
include "../includes/config.php";

extract($_POST);
extract($_GET);

$DOA=$_GET['DOA'];
$DOR=$_GET['DOR'];

$D_O_ANALYSIS = date("Y-m-d", strtotime($DOA));

$SQL_CODE_LIST="SELECT * FROM `DAILY_LAB_REPORT_LIST` where  `ANALYSIS_DATE`='$D_O_ANALYSIS'  order by id desc";
$STMT_CODE_LIST=$iconn->query($SQL_CODE_LIST);


if(isset($_POST['submit'])){

	$D_O_ANALYSIS = date("d-m-Y", strtotime($ANALYSIS_DATE));
	$D_O_COMPLEATION = date("d-m-Y", strtotime($REPORT_DATE));
	
	
	if($ENTRY_LOT_NUMBER!=''){
	$LOT_NUMBER=$ENTRY_LOT_NUMBER;	
	}
	
	$sql_lot_details="SELECT * from `inputmaterial_details`  where id='$LOT_NUMBER'";
    $stmt_lot_details=$iconn->query($sql_lot_details);
	$LOT_ROW=$stmt_lot_details->fetch_array();
$LOT_NO=$LOT_ROW['LOT_NO'];
	

	 $sql_org_insert="INSERT INTO `DAILY_LAB_REPORT_LIST`
		( `ANALYSIS_DATE`, `REPORT_DATE`, `SAMPLE_DETAILS`, `LOT_NUMBER`,
    `COUNT`, `QUANTITY`, `RESULT`, `PARAMETER`, `SULPHITE` , `LOADING_STATUS`, `REMARKS`, `SHIFT`, `TESTED_BY`) VALUES
	('$ANALYSIS_DATE','$REPORT_DATE','$SAMPLE_DETAILS','$LOT_NO',
	'$COUNT','$QUANTITY','$RESULT','$PARAMETER','$SULPHITE' ,
	'$LOADING_STATUS','$REMARKS','$SHIFT','$TESTED_BY')";

	





	$stmt1=$iconn->query($sql_org_insert);

	$iid = $iconn->insert_id;

//echo $iid;

	if($stmt1){
		legacyAuditLogInsertById('DAILY_LAB_REPORT_LIST', 'id', $iid, $_POST, ['Remarks' => 'Daily Lab Report Created'], 'QualityControl');
		
		$sql_count="SELECT count(`ANALYSIS_DATE`) as TOTAL,
(SELECT count(`SHIFT`) FROM `DAILY_LAB_REPORT_LIST` where `SHIFT`='DAY' and `ANALYSIS_DATE`='$ANALYSIS_DATE') as DAY,
(SELECT count(`SHIFT`) FROM `DAILY_LAB_REPORT_LIST` where `SHIFT`='NIGHT' and `ANALYSIS_DATE`='$ANALYSIS_DATE') as NIGHT

 FROM `DAILY_LAB_REPORT_LIST` where   `ANALYSIS_DATE`='$ANALYSIS_DATE'";
 
 

$stmt_ip=$iconn->query($sql_count);
$row=$stmt_ip->fetch_array();

$TOTAL_REPORTS=$row['TOTAL'];
$DAY_TOTAL=$row['DAY'];
$NIGHT_TOTAL=$row['NIGHT'];


 

$ANTI_BIOTIC_STATUS='';

if($LOADING_STATUS=='GRAY'){
	$ANTI_BIOTIC_STATUS='0';
}else if($LOADING_STATUS=='RED'){
	$ANTI_BIOTIC_STATUS='1';
}else if($LOADING_STATUS=='GREEN'){
	$ANTI_BIOTIC_STATUS='2';
}if($LOADING_STATUS=='BLUE'){
	$ANTI_BIOTIC_STATUS='3';
}


$sql_org_insert="UPDATE `inputmaterial_details` SET 
`ANTI_BIOTIC_STATUS`='$ANTI_BIOTIC_STATUS'  WHERE `id`='$LOT_NUMBER'";

$stmt1=$iconn->query($sql_org_insert);

if($TOTAL_REPORTS==1){
	
	$sql_org_insert="INSERT INTO `DAILY_LAB_DATA`
								( `ANALYSIS_DATE`, `REPORT_DATE`, `TOTAL_REPORTS`, `DAY_TOTAL`,
								`NIGHT_TOTAL`, `REJECT`, `HOLD`) VALUES
	('$ANALYSIS_DATE','$REPORT_DATE','$TOTAL_REPORTS','$DAY_TOTAL',
	'$NIGHT_TOTAL','0','0')";

	$stmt1=$iconn->query($sql_org_insert);
}else{
	
	

	
	 $sql_org_insert="UPDATE `DAILY_LAB_DATA` SET 
`TOTAL_REPORTS`='$TOTAL_REPORTS',`DAY_TOTAL`='$DAY_TOTAL',`NIGHT_TOTAL`='$NIGHT_TOTAL' WHERE `ANALYSIS_DATE`='$ANALYSIS_DATE'";

$stmt1=$iconn->query($sql_org_insert);

}

		echo "<script>alert('Success')</script>";

		//header('Location: printelisreport.php?id='.$iid);
		header('Location: DailyLabReportCreate.php?DOA='.$D_O_ANALYSIS.'&DOR='.$D_O_COMPLEATION);

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



        



<h2 align="center">Daily Lab AB Report</h2>



	</a>


			</a>




<ol class="breadcrumb">



        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>



        <li class="active">Invoice Preview</li>



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

		

		 
          <form role="form" method="post">

              <div class="box-body">

                <div class="form-group col-sm-3" >

                  <label for="exampleInputEmail1">Analysis Date</label>

                  <input required type="date" class="form-control" name='ANALYSIS_DATE' id="ANALYSIS_DATE" value="<?php  echo date("Y-m-d", strtotime($DOA));?>">

                </div>
				


                <div class="form-group col-sm-3">

                  <label for="exampleInputPassword1">Report Date</label>

                  <input required type="date" class="form-control" name='REPORT_DATE' id="REPORT_DATE" value="<?php  echo date("Y-m-d", strtotime($DOR));?>">

                </div>
				
				

				
				

				<div class="form-group col-sm-3">

                  <label for="exampleInputPassword1">Sample Details</label>

                 <select class="form-control" name="SAMPLE_DETAILS" id="SAMPLE_DETAILS">
				<option value="Pre Harvest" >Pre Harvest</option>
				<option value="Harvest" >Harvest</option>
				  <option value="Finished" >Finished</option>
				  <option value="Store Material" >Store Material</option>
				  <option  value="Babu Basker" >Babu Basker</option>
				  <option  value="SPC Material" >SPC Material</option>
				  <select>
                </div>
				
				<div class="form-group col-sm-3">

                  <label for="exampleInputPassword1">Lot Number Options</label>

                 <select class="form-control" name="LOT_OPTIONS" id="LOT_OPTIONS">
				 <option value="Select Lot" >Select Lot</option>
		<option value="Create Lot" >Create Lot</option>   
				
				 
				  <select>
                </div>

                <div class="form-group col-sm-3 LOT_NUMBERdiv">

                  <label for="exampleInputEmail1">Lot Number</label>

                 <select class="form-control" name="LOT_NUMBER" id="LOT_NUMBER">
				 
				 <option value="">Select Lot</option>
				
				   
				  <select>
                </div>
				
				
				
				<div class="form-group col-sm-3 NAME_ADDRESSdiv">

                  <label for="exampleInputPassword1">Enter Lot Number</label>

                  <input  type="text" class="form-control" name='ENTRY_LOT_NUMBER' id="NAME_ADDRESS" 
				  placeholder="Enter Lot Number">

                </div>
				
				

                <div class="form-group col-sm-3">

                  <label  for="exampleInputPassword1">Count</label>

                  <input required type="text" class="form-control" name="COUNT" id="COUNT" 
				  placeholder="Enter Count">

                </div>

                <div class="form-group col-sm-3" >

                  <label for="exampleInputEmail1">Quantity</label>

                  <input required type="text" class="form-control"  name="QUANTITY" id="QUANTITY" 
				  placeholder="Enter Quantity">

                </div>

                <div class="form-group col-sm-3">

                  <label for="exampleInputPassword1">Result</label>

                 <input required type="text" class="form-control"  name="RESULT" id="RESULT" 
				  placeholder="Enter Result">

                </div>

				
				

				

                <div class="form-group col-sm-3" >

                  <label for="exampleInputEmail1">Parameter</label>

                  <select class="form-control" name="PARAMETER" id="PARAMETER">
				  <option >NA</option>
				  <option >AOZ</option>
				  <option >AMOZ</option>
				  <option >AHD</option>
				  <option >SEM</option>
				  <option >CAP</option>
				
				  <select>
                </div>

                <div class="form-group col-sm-3">

                  <label for="exampleInputPassword1"> Loading Status</label>

                  <select class="form-control" name="LOADING_STATUS" id="LOADING_STATUS">
				   <option value="GRAY">GRAY - TEST PENDING</option>
				  <option value="GREEN">GREEN - ACCEPTED</option>
				  <option value="RED">RED - REJECT</option>
				  <option value="BLUE">BLUE - ON HOLD</option>
				 
				 
				  <select>
                </div>


				

				 <div class="form-group col-sm-3">

                  <label for="exampleInputPassword1">Shift</label>

                  <select class="form-control" name="SHIFT" id="SHIFT">
				  <option >DAY</option>
				  <option >NIGHT</option>
				 
				
				  <select>
                </div>
				
				
				<div class="form-group col-sm-3">

                  <label for="exampleInputPassword1">Tested By</label>

                 <select class="form-control" name="TESTED_BY" id="TESTED_BY">
					<option value="R AKHIL - JME REPRESENTATIVE">R AKHIL - JME REPRESENTATIVE</option>
					<option value="Y  MADHU - JME REPRESENTATIVE" >Y  MADHU - JME REPRESENTATIVE</option>
					<option value="RAJU - JME REPRESENTATIVEv" >RAJU - JME REPRESENTATIVE</option>
					<option value="K Bhagya - JME REPRESENTATIVE" >K Bhagya - JME REPRESENTATIVE</option>
					<option value="K PRASAD - JME REPRESENTATIVE" >K PRASAD - JME REPRESENTATIVE</option>
					<option value="V MAHESH - JME REPRESENTATIVE" >V MAHESH  - JME REPRESENTATIVE</option>
		
			    </select>
               
                </div>                       
				
				
				<div class="form-group col-sm-3" >

                  <label for="exampleInputEmail1">SULPHITE</label>

				  
				   <input required type="text" class="form-control" name='SULPHITE' id="SULPHITE" placeholder="Enter Quantity">

				

                </div>
				

				
				
                <div class="form-group col-sm-3" >

                  <label for="exampleInputEmail1">Remarks</label>

				  
				   <input required type="text" class="form-control" name='REMARKS' id="REMARKS" placeholder="Enter Remarks">

				

                </div>
				

				

				

               
                
  <div class="col-xs-12 box-footer"  align="right">
                
				<a href="DailyLabReports.php" class="btn btn-primary">Submit</a>
				
				<button type="submit"  name='submit' class="btn btn-primary">Save</button>

   </div>
				</div>

              <!-- /.box-body -->



             

            </form>






<div id="tdetails" class="table-responsive" >  
        <div class="box">
          
        <div class="box-body">
                    

  <table id="example1" class="table table-bordered table-hover">
              

    <thead>

      <tr>
	  
	  <th>Sample Details</th>
	  <th>Lot Number</th>
	  <th>Count</th>
	  <th>Quantity</th>
	  <th>Result</th>
	  <th>Parameter</th>
	  <th>Loading Status</th>
	  <th>Shift</th>
	  <th>Remarks</th>
	  <th>Tested By</th>
	  
	

      </tr>

    </thead>

	

	

	 

			

			

			

    <tbody>

	

	<?php

	//echo $sql;

	

   while($row=$STMT_CODE_LIST->fetch_array()){

	

	?>
	<tr>
	

		
	<td id="<?php  echo $row['DAY_CODE'];?>" > <?php echo $row['SAMPLE_DETAILS']; ?></td>
	<td id="<?php  echo $row['DAY_CODE'];?>" > <?php echo $row['LOT_NUMBER']; ?></td>
	<td id="<?php  echo $row['DAY_CODE'];?>" > <?php echo $row['COUNT']; ?></td>
	<td id="<?php  echo $row['DAY_CODE'];?>" > <?php echo $row['QUANTITY']; ?></td>
	<td id="<?php  echo $row['DAY_CODE'];?>" > <?php echo $row['RESULT']; ?></td>
	<td id="<?php  echo $row['DAY_CODE'];?>" > <?php echo $row['PARAMETER']; ?></td>
    <td id="<?php  echo $row['DAY_CODE'];?>" > <?php echo $row['LOADING_STATUS']; ?></td>
	
	<td id="<?php  echo $row['DAY_CODE'];?>" > <?php echo $row['SHIFT']; ?></td>
	<td id="<?php  echo $row['DAY_CODE'];?>" > <?php echo $row['REMARKS']; ?></td>
	<td id="<?php  echo $row['DAY_CODE'];?>" > <?php echo $row['TESTED_BY']; ?></td>
       

        </tr>

	  <?php 

   }

	  ?>

	  

	 

     </tbody>

  </table>
</div>

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



<Script>



$('document').ready(function(){

	
	
var vl=$('#SAMPLE_DETAILS').val();
		console.log(vl);
		$.ajax({
				url:'dcount.php',
				type:'POST',
				data:{'lid':vl,'qtype':'qtype'},
				success:function(data){
					console.log(data);
					$("#LOT_NUMBER").html(data);
			//		 var obj = JSON.parse(data);
			// $('#COUNT').val(obj['COUNT']);
			// $('#QUANTITY').val(obj['WEIGHT_IN_KGS']);
				}
			})

		if(vl=='Pre Harvest'){
			
			$('.LOT_NUMBERdiv').show();
		    $('.NAME_ADDRESSdiv').hide();
			
			
		}
		else{
			$('.LOT_NUMBERdiv').hide();
			$('.NAME_ADDRESSdiv').show();
		
		}
    $(document).on('change','#SAMPLE_DETAILS',function(e){
		e.preventDefault();
//alert('hii');
		var vl=$(this).val();
		console.log(vl);
		$.ajax({
				url:'dcount.php',
				type:'POST',
				data:{'lid':vl,'qtype':'qtype'},
				success:function(data){
					console.log(data);
					$("#LOT_NUMBER").html(data);
			//		 var obj = JSON.parse(data);
			// $('#COUNT').val(obj['COUNT']);
			// $('#QUANTITY').val(obj['WEIGHT_IN_KGS']);
				}
			})

		
		$('#NAME_ADDRESS').val('');
		
		if(vl=='Pre Harvest'){
			
			
			
			if($('#LOT_OPTIONS').val()=='Create Lot'){
			$('.LOT_NUMBERdiv').show();
		    $('.NAME_ADDRESSdiv').hide();
			
			//alert('Please Select From Lot List');
			}else{
				//alert('Select Lot');
			$('.LOT_NUMBERdiv').show();
			$('.NAME_ADDRESSdiv').hide();
			}
			
		}
		else{
			
			if($('#LOT_OPTIONS').val()=='Create Lot'){
				
			$('.LOT_NUMBERdiv').hide();
			$('.NAME_ADDRESSdiv').show();
			
			}else{
				//alert('Select Lot');
			$('.LOT_NUMBERdiv').show();
			$('.NAME_ADDRESSdiv').hide();
			
			}
			
			
		
		}
	})

	$(document).on('change','#LOT_NUMBER',function(e){
		 $('#COUNT').val('');
		$('#QUANTITY').val('');
		$('#NAME_ADDRESS').val('');
			
		e.preventDefault();
		//alert('Please Select Lot');	
		
		var vl=$(this).val();
		if(vl=='Create Lot'){
			
			
		$('.NAME_ADDRESSdiv').show();
		$('.LOT_NUMBERdiv').hide();
			
			
		}else{
			$('.NAME_ADDRESSdiv').hide();
			$('.LOT_NUMBERdiv').show();
			var type=$('#SAMPLE_DETAILS').val();
		//	alert(type);
			
			$.ajax({
				url:'dcount.php',
				type:'POST',
				data:{'lid':vl,'type':type},
				success:function(data){
					console.log(data);
					 var obj = JSON.parse(data);
			 $('#COUNT').val(obj['COUNT']);
			 $('#QUANTITY').val(obj['WEIGHT_IN_KGS']);
				}
			})
		
		}
	})
    $(document).on('change','#LOT_OPTIONS',function(e){
		 $('#COUNT').val();
		$('#QUANTITY').val();
		$('#NAME_ADDRESS').val('');
			
		e.preventDefault();
		//alert('hii');
		var vl=$(this).val();
		
		
		if(vl=='Create Lot'){
			var vl=$('#SAMPLE_DETAILS').val();
			if(vl=='Pre Harvest'){
			alert('Please Select Lot');	
			return;
			}
			
		$('.NAME_ADDRESSdiv').show();
		$('.LOT_NUMBERdiv').hide();
			
			
		}else{
			$('.NAME_ADDRESSdiv').hide();
			$('.LOT_NUMBERdiv').show();
			
		
		}
	})
    	$(document).on('click','#payBtn',function(e){

    	e.preventDefault();

			 $('.trremove').remove();

                         

			var refid=$(this).val();

			$.ajax({

		  url:'bd_purchasepreview.php',

		  type:'POST',

		  data:{'r_id':refid},

		  success:function(data){

			  //console.log(data);

			   var obj = JSON.parse(data);

			 //console.log(obj.length);

			 

			  for (var i = 0; i < obj.length; i++) {

if(obj[i].STATUS==0){

									

									var msg="Request Created at";

									var status="Request Sent";

								}else if(obj[i].STATUS==1){

									var msg="Request Accepted at";

									var status="Request Accepted";

								}else if(obj[i].STATUS==2){

									var msg="Request Rejected at";

									var status="Request Rejected";

								}else if(obj[i].STATUS==3){

									var msg="Dispatch from hub at";

									var status="Under Transport ";

								}else if(obj[i].STATUS==4){

									var msg="Recieved Destination at";

									var status="sent to weight";

								}else if(obj[i].STATUS==5){

									var msg="Weighted at";

									$status="sent to process";

								}else if(obj[i].STATUS==6){

									var msg="Process Compleated at";

									var status="Ready for ReAssign/Return";

								}

					var large2='<tr class="trremove"><th style="width:25%;"><strong>'+msg+'</strong></th><td style="width:25%;">'+obj[i].DATE_TIME+'</td><td style="width:25%;">'+status+'</td></tr>';

								                      

                       $('.process_dt_dt').append(large2);

					   //console.log(refid);



						$('.modheading').text(refid);		

  

}

		  }

			})

			//return false;

			

    		$('#myModal').modal('toggle');

    

    	});

    

    	$('#continuebtn').on('click',function(){

    

    		$('form').submit();

    	});

    });    



</script>











</html>




