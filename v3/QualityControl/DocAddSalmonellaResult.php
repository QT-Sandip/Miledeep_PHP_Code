<?php



session_start();

include "../includes/config.php";

$MB_REPORT_ID=$_GET['id'];
$COUNTRY=$_GET['COUNTRY'];


if($MB_REPORT_ID!=''){
	$sql="SELECT * FROM `TEST_RESULT_PARMS` where ID='4'";
	$stmt=$iconn->query($sql);
	$post_details=$stmt->fetch_array();


	$sql="select * from SALMONELLA_TEST_RESULT where MB_REPORT_ID='$MB_REPORT_ID'  ";
	$stmt=$iconn->query($sql);
	$result_stmt=$iconn->query($sql);
	
	$result_details=$result_stmt->fetch_array();
}



$sql_varities="SELECT * FROM `VARITIES_DATA` where FREEZING_DETAILS='IQF'";
$stmt_varities2=$iconn->query($sql_varities);



extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	
	$D_O_ANALYSIS = date("d-m-Y", strtotime($D_O_ANALYSIS));
	$D_O_COMPLEATION = date("d-m-Y", strtotime($D_O_COMPLEATION));
	
	
	
	$sql_org_insert="INSERT INTO `SALMONELLA_TEST_RESULT`(
	`MB_REPORT_ID`, 
	`P_CODES_REG_NUM`, `DOA_DOC`,
	`VARITY`, `SALMONELLA`,`RESULT`) 
	VALUES ('$MB_REPORT_ID','$P_CODES_REG_NUM','$DOA_DOC','$VARITY','$SALMONELLA','$RESULT')";
	
	 $sql_org_insert;

	$stmt1=$iconn->query($sql_org_insert);
	$M_T_REPORT_ID = $iconn->insert_id;
//echo $iid;
	if($stmt1){
		legacyAuditLogInsertById('SALMONELLA_TEST_RESULT', 'ID', $M_T_REPORT_ID, $_POST, ['Remarks' => 'Salmonella Result Added'], 'QualityControl');
		header('Location: DocAddSalmonellaResult.php?id='.$MB_REPORT_ID.'&COUNTRY='.$COUNTRY);
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

        

<h2 align="center">SALMONELLA TEST RESULT</h2>








			



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
              
               
			  
              <!-- /.box-body -->
			  
			  <div class="col-xs-12"> 

<th><span></span></th>
<h2 align="center">Test Results</h2>
<th><strong></strong></th>

</div>



	


      <div class="box-body">
	  
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Production Code & Reg no</label>
                  <input type="text" class="form-control" name='P_CODES_REG_NUM' id="P_CODES_REG_NUM"
				  placeholder="Enter Name and Address of the Farmer" value="<?php echo $result_details['P_CODES_REG_NUM'];?>">
                </div>
                
                <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Date of analysis / Date of Completion</label>
                  <input type="text" class="form-control" name='DOA_DOC' id="DOA_DOC" placeholder="Enter Name and Address of the Farmer"
				  
				  value="<?php echo $result_details['DOA_DOC'];?>">
                </div>
                
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Varity</label>
                  <select class="form-control" name="VARITY" id="VARITY">
				  <?php while($row=$stmt_varities2->fetch_array()){

if($row['SHORT_NAME']!='')
{						$CODE=$row['SHORT_NAME'];			?>
								
									<option value="<?php echo $CODE ;?>" <?php if($result_details['VARITY']==$CODE) 
										echo 'selected="selected"'; ?> ><?php echo $CODE ?></option>
								
<?php  } } ?>
				  <select>
				  </div>
				  
				 
				 
				  
                <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1"><?php  echo $post_details['SALMONELLA']; ?></label>
                <select class="form-control" name="SALMONELLA" id="SALMONELLA">
				  <option >Absent</option>
				  <option >Present</option>
				  <select>
                </div>
				
				
				
		</div>
             
			  <div class="col-xs-12 box-footer"  align="right">
			   <button type="submit"  name='submit' class="btn btn-primary">ADD RESULT</button>
			  <a  id='eia_report' href='DocAddReports.php?id=$INVOICE_ID" class="btn btn-primary">SUBMIT RESULT </a>
               
              </div>
            </form>
</div>


</div>

 <div id="tdetails" class="table-responsive" >  
        <div class="box">
          
        <div class="box-body">
                    

  <table id="example1" class="table table-bordered table-hover">
              

    <thead>

      <tr>
	  
	  
	  
	 			  
				
			
	  
	  
		<?php  if($post_details['PROD_REG']!='NA'){ ?>
		<th><?php  echo $post_details['PROD_REG']; ?></th>
		<?php }?>
		
		<?php  if($post_details['VARITY']!='NA'){ ?>
		<th><?php  echo $post_details['VARITY']; ?></th>
		<?php }?>
		
		
		<?php  if($post_details['DOA_DOC']!='NA'){ ?>
		<th><?php  echo $post_details['DOA_DOC']; ?></th>
		<?php }?>
		
	
		
		<?php  if($post_details['SALMONELLA']!='NA'){ ?>
		<th><?php  echo $post_details['SALMONELLA']; ?></th>
		<?php }?>
		
		<th>Result</th>
		
		
		
		
		
		
		

		

      

      </tr>

    </thead>

	

	

	 

			

			

			

    <tbody>

	

	<?php

	//echo $sql;

	

   while($row=$stmt->fetch_array()){

	

	?>
		
		

		<td id="<?php  echo $row['ID'];?>" > <?php echo $row['P_CODES_REG_NUM']; ?></td>
		<td id="a_type<?php  echo $row['C_ID'];?>"><?php echo $row['VARITY']; ?></td>
		
		<td id="c_name<?php  echo $row['C_ID'];?>"><?php echo $row['DOA_DOC']; ?></td>
		<td id="address<?php  echo $row['C_ID'];?>"><?php echo  $row['SALMONELLA']; ?></td>
		<td id="address<?php  echo $row['C_ID'];?>"><?php echo  $row['SALMONELLA']; ?></td>
	
	 

        
		
		
		
		
		

     	

	 				


        

        </tr>

	  <?php 

   }

	  ?>

	  

	 

     </tbody>

  </table>
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
    
    	$(document).on('click','#payBtn',function(e){
    	e.preventDefault();
			 $('.trremove').remove();
                         
			var refid=$(this).val();
			$.ajax({
		  url:'AjaxPurchasePreview.php',
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
			alert('hi');
    
    		$('form').submit();
    	});
    });    

</script>





</html>

