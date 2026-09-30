<?php



session_start();

include "../includes/config.php";

$MB_REPORT_ID=$_GET['id'];
$COUNTRY=$_GET['COUNTRY'];


if($MB_REPORT_ID!=''){
	 $sql="SELECT * FROM `TEST_RESULT_PARMS` where TEST_ID='4' and COUNTRY='$COUNTRY'";

$stmt=$iconn->query($sql);

	
$post_details=$stmt->fetch_array();

$INVOICE_ID=$post_details['INVOICE_ID'];
$sql="select * from BUYER_MB_RESULT where MB_REPORT_ID='$MB_REPORT_ID'  ";

$stmt=$iconn->query($sql);
}



$sql_varities="SELECT * FROM `VARITIES_DATA` where FREEZING_DETAILS='IQF'";
$stmt_varities2=$iconn->query($sql_varities);

$BUYER_MB_REPORT_SQL="select * from BUYER_MB_REPORT where ID='$MB_REPORT_ID'";

$BUYER_MB_REPORT_STMT=$iconn->query($BUYER_MB_REPORT_SQL);
$BUYER_MB_REPORT_ROW=$BUYER_MB_REPORT_STMT->fetch_array();

$INVOICE_ID=$BUYER_MB_REPORT_ROW['INVOICE_ID'];
$TYPE=$BUYER_MB_REPORT_ROW['VARITY'];

  $SQL_CODE_LIST="SELECT * FROM `CODE_LIST_CREATE` where INV_ID='$INVOICE_ID' and SHORT_NAME='$TYPE'";
$STMT_CODE_LIST=$iconn->query($SQL_CODE_LIST);

$PO_VARIETY_LIST_SQL="SELECT * FROM `PO_VARIETY_LIST` WHERE INV_ID='$INVOICE_ID' and TYPE='$TYPE'";
$STMT_PO_VARIETY_LIST=$iconn->query($PO_VARIETY_LIST_SQL);




extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	
	$D_O_ANALYSIS = date("d-m-Y", strtotime($D_O_ANALYSIS));
	$D_O_COMPLEATION = date("d-m-Y", strtotime($D_O_COMPLEATION));
	
	 $CODE_LIST_ID = $REG_NO;
	
	 $SQL_CODE_LIST="SELECT * FROM `CODE_LIST_CREATE` where CODE_LIST_ID='$REG_NO'";
	 $STMT_CODE_LIST=$iconn->query($SQL_CODE_LIST);
	 $CODE=$STMT_CODE_LIST->fetch_array();
	 
	 $FARM_REG_ID = $CODE['FARM_REG_ID'];
	 $P_CODES_REG_NUM=$P_CODES.'&'.$FARM_REG_ID;
	
	$DOA_DOC=$DOA.'/'.$DOC;
	
	
	$sql_org_insert="INSERT INTO `BUYER_MB_RESULT`(
	`MB_REPORT_ID`, 
	`P_CODES_REG_NUM`,`CODE_LIST_ID`, `DOA_DOC`,
	`VARITY`, `TPC`, `ECOLI`, `STAPHYLO_COCCUS`, `V_CHOLERAE`, 
	`V_PRAHAE`, `SALMONELLA`,
	`LISTERIA`,`A_REPORT`) 
	VALUES ('$MB_REPORT_ID','$P_CODES_REG_NUM','$CODE_LIST_ID','$DOA_DOC','$VARITY','$TPC',
	'$ECOLI','$STAPHYLO_COCCUS','$V_CHOLERAE','$V_PRAHAE','$SALMONELLA','$LISTERIA','$A_REPORT')";
	
	 $sql_org_insert;

	$stmt1=$iconn->query($sql_org_insert);
	$M_T_REPORT_ID = $iconn->insert_id;
//echo $iid;
	if($stmt1){
		legacyAuditLogInsertById('BUYER_MB_RESULT', 'ID', $M_T_REPORT_ID, $_POST, ['Remarks' => 'MB Result Added'], 'QualityControl');
		
		
		
		
		
		
		header('Location: CombineMbResult_Create.php?id='.$MB_REPORT_ID.'&COUNTRY='.$COUNTRY);
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

        

<h2 align="center">IN-HOUSE TEST REPORT FOR MICROBIOLOGY</h2>








			



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
	  
				
				
				 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Production Codes</label>
                  <select class="form-control" name="P_CODES" id="P_CODES">
				   
				  <?php $ary_prod_codes=array();?>
				 <option value="">Select Production Code</option>
				 <?php while($CODE=$STMT_CODE_LIST->fetch_array()){
					echo $tedt=$CODE['DAY_CODE'];
					 if(in_array($tedt, $ary_prod_codes)){
					
					}else{
						$ary_prod_codes[]=$tedt;
						
					
					 ?>	
				 
					<option value="<?php echo $tedt ;?>" ><?php  echo $tedt; ?></option>
				<?php  }  } ?>
				  <select>
				  </div>
				  
				  
				  
				  <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Reg no</label>
                  <select class="form-control" name="REG_NO" id="REG_NO">
				   <option value="">Select Reg Number</option>
				  <select>
				  </div>
				  
				   <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Packing Size</label>
                  <select class="form-control" name="PACK_SIZE" id="PACK_SIZE">
				   <option value="">Select Packing</option>
				  <select>
				  </div>
				  
				   <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Varity</label>
                  <select class="form-control" name="VARITY" id="VARITY">
				 <?php while($row=$STMT_PO_VARIETY_LIST->fetch_array()){

if($row['SHORT_NAME']!='')
{						$CODE=$row['SHORT_NAME'];			?>
								
									<option value="<?php echo $CODE ;?>" <?php if($result_details['VARITY']==$CODE) 
										echo 'selected="selected"'; ?> ><?php echo $CODE ?></option>
								
<?php  } } ?>
				  <select>
				  </div>
                
                <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Date of analysis</label>
                  <input required type="date" class="form-control" name='DOA' id="DOA_DOC" placeholder="Enter Date of analysis">
                </div>
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Date of Completion</label>
                  <input required type="date" class="form-control" name='DOC' id="DOA_DOC" placeholder="Date of Completion">
                </div>
               
				  
				 
				 
				  <?php  if($post_details['TPC']!='NA'){ ?>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1"><?php  echo $post_details['TPC']; ?></label>
                <input required type="text" class="form-control" name='TPC' id="TPC" placeholder="Enter Value">
                
				  </div>
					
				<?php } ?>
				  
				
				<?php  if($post_details['ECOLI']!='NA'){ ?>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1"><?php  echo $post_details['ECOLI']; ?></label>
                <select class="form-control" name="ECOLI" id="ECOLI">
				  <option >Nil</option>
				  <option >Present</option>
				  <select>
				  </div>
					
				<?php } ?>
				
				
				<?php  if($post_details['STAP']!='NA'){ ?>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1"><?php  echo $post_details['STAP']; ?></label>
                <select class="form-control" name="STAPHYLO_COCCUS" id="STAPHYLO_COCCUS">
				  <option >Nil</option>
				  <option >Present</option>
				  <select>
				  </div>
					
				<?php } ?>
				
			
				
				
	
               
               
				
				<?php  if($post_details['V_CHLORAE']!='NA'){ ?>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1"><?php  echo $post_details['V_CHLORAE']; ?></label>
                 <select class="form-control" name="V_CHOLERAE" id="V_CHOLERAE">
				   <option >Absent</option>
				  <option >Present</option>
				  <select>
				  </div>
					
				<?php } ?>
                
				  
				  <?php  if($post_details['V_PARAHA']!='NA'){ ?>
				 
                <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1"><?php  echo $post_details['V_PARAHA']; ?></label>
                 <select class="form-control" name="V_PRAHAE" id="V_PRAHAE">
				  <option >Absent</option>
				  <option >Present</option>
				  <select>
				  </div>
				  
				 
				  
				  <?php } ?>
				  
				  <?php  if($post_details['SALMONELLA']!='NA'){ ?>
				  
                <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1"><?php  echo $post_details['SALMONELLA']; ?></label>
                <select class="form-control" name="SALMONELLA" id="SALMONELLA">
				  <option >Absent</option>
				  <option >Present</option>
				  <select>
                </div>
				
				<?php } ?> <?php  if($post_details['LISTERIA']!='NA'){ ?>
				  
                <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1"><?php  echo $post_details['LISTERIA']; ?></label>
                <select class="form-control" name="LISTERIA" id="LISTERIA">
				  <option >Absent</option>
				  <option >Present</option>
				  <select>
                </div>
				
				<?php } ?>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Analytic Report</label>
                <select class="form-control" name="A_REPORT" id="A_REPORT">
				  <option >Satisfactory</option>
				  <option >UnSatisfactory</option>
				  <select>
                </div>
				
				
				
				
		</div>
             
			  <div class="col-xs-12 box-footer"  align="right">
              
				
				<button type="submit"  name='submit' class="btn btn-primary">ADD RESULT</button>
			  <a  id='eia_report' href="ViewReports.php?id=<?php echo $INVOICE_ID ?>" class="btn btn-primary">SUBMIT RESULT </a>
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
		
		<?php  if($post_details['DOA_DOC']!='NA'){ ?>
		<th><?php  echo $post_details['DOA_DOC']; ?></th>
		<?php }?>
		
		<?php  if($post_details['VARITY']!='NA'){ ?>
		<th><?php  echo $post_details['VARITY']; ?></th>
		<?php }?>
		<?php  if($post_details['TPC']!='NA'){ ?>
		<th><?php  echo $post_details['TPC']; ?></th>
		<?php }?>
		<?php  if($post_details['ECOLI']!='NA'){ ?>
		<th><?php  echo $post_details['ECOLI']; ?></th>
		<?php }?>
		<?php  if($post_details['STAP']!='NA'){ ?>
		<th><?php  echo $post_details['STAP']; ?></th>
		<?php }?>
		<?php  if($post_details['V_CHLORAE']!='NA'){ ?>
		<th><?php  echo $post_details['V_CHLORAE']; ?></th>
		<?php }?>
		
		<?php  if($post_details['SALMONELLA']!='NA'){ ?>
		<th><?php  echo $post_details['SALMONELLA']; ?></th>
		<?php }?>
		
		

		
		
		
		<th>Analytic Report</th>
		
		

		

      

      </tr>

    </thead>

	

	

	 

			

			

			

    <tbody>

	

	<?php

	//echo $sql;

	

   while($row=$stmt->fetch_array()){

	

	?>
		
		

		
	
	  <?php  if($post_details['PROD_REG']!='NA'){ ?>
		<td id="<?php  echo $row['ID'];?>" > <?php echo $row['P_CODES_REG_NUM']; ?></td>
		<?php }?>
		
		<?php  if($post_details['DOA_DOC']!='NA'){ ?>
		<td id="c_name<?php  echo $row['C_ID'];?>"><?php $DOA_DOC=$row['DOA_DOC']; echo $row['DOA_DOC']; ?></td>
		<?php }?>
		
		<?php  if($post_details['VARITY']!='NA'){ ?>
		<td id="a_type<?php  echo $row['C_ID'];?>"><?php echo $row['VARITY']; ?></td>

		<?php }?>
		<?php  if($post_details['TPC']!='NA'){ ?>
		<td id="p_number<?php  echo $row['C_ID'];?>"><?php echo $row['TPC']; ?></td>
		<?php }?>
		<?php  if($post_details['ECOLI']!='NA'){ ?>
		<td id="address<?php  echo $row['C_ID'];?>"><?php echo $row['ECOLI']; ?></td>
		<?php }?>
		<?php  if($post_details['STAP']!='NA'){ ?>
		<td id="address<?php  echo $row['C_ID'];?>"><?php echo  $row['STAPHYLO_COCCUS']; ?></td>
		<?php }?>
		<?php  if($post_details['V_CHLORAE']!='NA'){ ?>
		<td id="address<?php  echo $row['C_ID'];?>"><?php echo  $row['V_CHOLERAE']; ?></td>
		
		
		<?php }?>
		
		<?php  if($post_details['V_CHLORAE']!='NA'){ ?>
		<td id="address<?php  echo $row['C_ID'];?>"><?php echo  $row['V_PRAHAE']; ?></td>
		
		<?php }?>
		
	

        
		
		
		<td id="address<?php  echo $row['C_ID'];?>"><?php echo  $row['A_REPORT']; ?></td>
		
		

     	

	 				


        

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
	var doa_doc='<?php echo $DOA_DOC?>';
	//var doa_doc='<?php echo $COUNTRY?>';
	
	//alert(doa_doc);
	
    $("#DOA_DOC").val(doa_doc);
    	$(document).on('change','#P_CODES',function(e){
			
    	e.preventDefault();
			 
                         
			var DAY_CODE=$(this).val();
			var INV_ID=<?php echo $INVOICE_ID ?>;
			
			//alert(DAY_CODE);
			$.ajax({
		  url:'AjaxCodeList.php',
		  type:'POST',
		  data:{'D_CODE':DAY_CODE,'INV_ID': INV_ID},
		  success:function(data){
			  console.log(data);
			  // var obj = JSON.parse(data);
			$("#REG_NO").html(data);
			$("#PACK_SIZE").html('<option value="">Select Size</option> ');
					}
			})
			//return false;
			
    		
    
    	});
		
		
		$(document).on('change','#REG_NO',function(e){			
			e.preventDefault();                         
			var REG_NO=$(this).val();
			var INV_ID=<?php echo $INVOICE_ID ?>;
			
			//alert(INV_ID);
			$.ajax({
		  url:'AjaxCodeList.php',
		  type:'POST',
		  data:{'REG_NO':REG_NO,'INV_ID': INV_ID},
		  success:function(data){
			  console.log(data);
			  // var obj = JSON.parse(data);
			$("#PACK_SIZE").html(data);
					}
			})
			//return false;
			
    		
    
    	});
    
    	$('#continuebtn').on('click',function(){
			alert('hi');
    
    		$('form').submit();
    	});
    });    

</script>





</html>

