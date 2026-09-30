<?php



session_start();

include "../includes/config.php";

$REFERENCE_ID=$_GET['id'];
$INV_ID=$_GET['PoID']; 


$sql_invoice="SELECT * FROM `$TblScrmPoData` where PoID='$INV_ID'";
$stmt_invoice=$iconn->query($sql_invoice);
$invoice_details=$stmt_invoice->fetch_array();

$sql_CODE_LIST="SELECT * FROM `$TblQcCodeList` where InvID='$INV_ID'";
$STMT_CODE_LIST=$iconn->query($sql_CODE_LIST);



$sql="SELECT * FROM `inputmaterial_details` where REFERENCE_ID='$REFERENCE_ID'";

$stmt=$iconn->query($sql);

$post_details=$stmt->fetch_array();



$sql="SELECT * FROM `$TblQcIHMBReport` where ID='$REFERENCE_ID'";

$stmt_report=$iconn->query($sql);
$report_details=$stmt_report->fetch_array();

$sql_result="SELECT * FROM `IH_MICROBIOLOGY_TEST_RESULT` where M_T_REPORT_ID='$REFERENCE_ID'";

$stmt_result=$iconn->query($sql_result);
$result_details=$stmt_result->fetch_array();

$sql_varities="SELECT * FROM `VARITIES_DATA`";
$stmt_varities=$iconn->query($sql_varities);
$stmt_varities2=$iconn->query($sql_varities);


extract($_POST);
extract($_GET);

if(isset($_POST['submit'])){
	
	$PRODUCT_SAMPLE_NAME=$PRODUCT_SAMPLE_NAME.'('.$TREATE_MENT .')';
	
	
	$sql_update="UPDATE `IH_MICROBIOLOGY_TEST_REPORT` SET `R_NUM_DATE`='$R_NUM_DATE',`TEST_DATE`='$TEST_DATE',`NAME_ADDRESS`='$NAME_ADDRESS',
	`FARM_REG_NO`='$FARM_REG_NO',`NAME_OF_SPECIES`='$NAME_OF_SPECIES',
	`TYPE_OF_PACKING`='$TYPE_OF_PACKING',`NO_OF_M_CARTON`='$NO_OF_M_CARTON',`PRODUCTION_CODES`='$PRODUCTION_CODES',
	`PRODUCT_SAMPLE_NAME`='$PRODUCT_SAMPLE_NAME',`BUYER_ORDER_NO`='$BUYER_ORDER_NO',`D_O_SAMPLING`='$D_O_SAMPLING',
	`NAME_OF_REP`='$NAME_OF_REP',`LAB_SAMPLE_ID`='$LAB_SAMPLE_ID',`D_O_ANALYSIS`='$D_O_ANALYSIS',
	`D_O_COMPLEATION`='$D_O_COMPLEATION',`TESTED_BY`='$TESTED_BY',`VARIFIED_BY`='$VARIFIED_BY',`IS_EDITED`='0' WHERE ID=$REFERENCE_ID";
	

	
	
	
	

	$stmt1=$iconn->query($sql_update);
	//$M_T_REPORT_ID = $iconn->insert_id;
//echo $iid;
	if($stmt1){
		
	

	 $sql_org_insert="UPDATE `IH_MICROBIOLOGY_TEST_RESULT` SET `P_CODES_REG_NUM`='$P_CODES_REG_NUM',
	`VARITY`='$VARITY',`DOA_DOC`='$DOA_DOC',`T_P_C`='$T_P_C',`E_COLI`='$E_COLI',
	`STAPHYLO_COCCUS`='$STAPHYLO_COCCUS',`V_CHOLERAE`='$V_CHOLERAE',`V_PARAHA_EMOLYTICU`='$V_PARAHA_EMOLYTICU',
	`SALMOELLA`='$SALMOELLA',`A_REPORT`='$A_REPORT',`IS_EDITED`='0' WHERE M_T_REPORT_ID=$REFERENCE_ID";	
	
	
	
	$sql_org_insert;
	
	
		

	$stmt1=$iconn->query($sql_org_insert);						
		
		
		
		header('Location: DocAddReports.php?id='.$INV_ID);
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

        

<h2 align="center">IN-HOUSE TEST REPORT FOR MICROBIOLOGY- <span style="color:Tomato; bold"><?php echo $invoice_details['PO_NUM'] ?></span></h2>


</a>
</a>

			

			



<ol class="breadcrumb">

        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>

        <li class="active">Edit Mb Report</li>

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
                  <label for="exampleInputEmail1">Test Report No</label>
                  <input type="text" class="form-control" name='R_NUM_DATE' id="R_NUM_DATE"
				  placeholder="Enter Test Report No & Date" value="<?php echo $report_details['R_NUM_DATE']?>">
                </div> 
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Test Report Date</label>
                  <input type="text" class="form-control" name='TEST_DATE' id="TEST_DATE"
				  placeholder="Enter Test Report No & Date" value="<?php echo $report_details['TEST_DATE']?>">
                </div>
                
               
				
				 <div class="form-group col-sm-3" >
                 <label for="exampleInputEmail1">Farm Registration No</label>               				  
				 <select class="form-control" name="FARM_REG_NO" id="FARM_REG_NO">				
				<?php while($row=$STMT_CODE_LIST->fetch_array()){ ?>				
				<option value="<?php echo $row['CODE_LIST_ID'];?>" <?php if($report_details['FARM_REG_NO']==$row['CODE_LIST_ID']) 
					echo 'selected="selected"'; ?> ><?php echo $row['FARM_REG_ID'];?></option> 
				
				
				<?php } ?>								
				</select>	
                </div>
               
                <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Scientific Name of the Species</label>
                  
				  
				  <select class="form-control" name="NAME_OF_SPECIES" id="NAME_OF_SPECIES">
		 <option value="PENAEUS VANNAMEI" <?php if($report_details['NAME_OF_SPECIES']=="PENAEUS VANNAMEI") echo 'selected="selected"'; ?> 
		 >PENAEUS VANNAMEI</option>
		 <option value="LITOPENAUS VANNAMEI" <?php if($report_details['NAME_OF_SPECIES']=="LITOPENAUS VANNAMEI") 
			 echo 'selected="selected"'; ?> >LITOPENAUS VANNAMEI</option>
		
		 <select>
				  
                </div>
				
				
                <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Date Of Sampling</label>
                  <input type="date" class="form-control"  name="D_O_SAMPLING" id="D_O_SAMPLING" 
				  placeholder="Enter Date Of Sampling " value="<?php echo $report_details['D_O_SAMPLING']?>">
                </div>
                <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Name of the Technologist</label>
                   
				   <input type="text" class="form-control"  name="NAME_OF_REP" id="NAME_OF_REP"
				   placeholder="Enter Date Of Sampling " value="<?php echo $report_details['NAME_OF_REP']?>">
                
				 
               
                </div>
                <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Lab Sample ID</label>
                  <input type="text" class="form-control"  name="LAB_SAMPLE_ID" id="LAB_SAMPLE_ID"
				  placeholder="Enter Lab Sample ID" value="<?php echo $report_details['LAB_SAMPLE_ID']?>">
                </div>
				
				
                <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Date Of Analysis</label>
                  <input type="date" class="form-control" name="D_O_ANALYSIS" id="D_O_ANALYSIS"  
				  placeholder="Enter Date Of Analysis" value="<?php echo $report_details['D_O_ANALYSIS']?>">
                </div>
                <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Date of Completion</label>
                  <input type="date" class="form-control"  name="D_O_COMPLEATION" id="D_O_COMPLEATION" 
				  placeholder="Enter Date of Issuance" value="<?php echo $report_details['D_O_COMPLEATION']?>">
                </div>
				
				 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Tested By</label>
				  
				
                    <input type="text" class="form-control"  name="TESTED_BY" id="TESTED_BY" 
					placeholder="Enter Date of Issuance" value="<?php echo $report_details['TESTED_BY']?>">
               
               
                </div>
				
				 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Verified By</label>
                  
               
			      <input type="text" class="form-control"  name="VARIFIED_BY" id="VARIFIED_BY" 
				  placeholder="Enter Date of Issuance" value="<?php echo $report_details['VARIFIED_BY']?>">
               
                </div>
                
				</div>
              <!-- /.box-body -->
	

        <div class="box-body">

		

		

		

 <div class="row">



    		</div>


		
<div class="col-xs-12"> 

<th><span></span></th>
<h2 align="center">Test Results</h2>
<th><strong></strong></th>

</div>
    		


			<table class="table table-bordered" >

	<thead>

                                <tr>

        							
        							
									
        							
        							<th class="text"><strong>T.p.c/Cfu/gm@ 37+-1C</strong></th>
        							<th class="text"><strong>E.coli/Cfu/gm@ 37+-1C</strong></th>
        							<th class="text"><strong>Staphylococcus aureus Cfu/gm  @37+-1C</th>
        							<th class="text"><strong>V cholerae /25 g</strong></th>
        							<th class="text"><strong>V parahaemolyticus /25 g</strong></th>
        							<th class="text"><strong>Salmonella/375 g</strong></th>
        							<th class="text"><strong>Analytic Report</strong></th>

							    

                                </tr>

   </thead>


    						<tbody>
							
							
							
								<tr> 
        							
								<td style="width:5%;" class="text-left">
								  <input type="text" class="form-control"  name="T_P_C" id="T_P_C" 
								   placeholder="Enter Value" 
								  value="<?php echo $result_details['T_P_C']?>">
								</td>
								
								<td style="width:5%;" class="text-left">
								  
								  <select class="form-control" name="E_COLI" id="E_COLI">
								  <option value="Nil" <?php if($result_details['E_COLI']=="Nil") echo 'selected="selected"'; ?> >Nil</option>
                                  <option value="Present" <?php if($result_details['E_COLI']=="Present") echo 'selected="selected"'; ?> >Present</option>

									
								<select>
								</td>
								
								 
								
								<td style="width:5%;" class="text-left">
								
								<select class="form-control" name="STAPHYLO_COCCUS" id="STAPHYLO_COCCUS">
								<option value="Nil" <?php if($result_details['STAPHYLO_COCCUS']=="Nil") echo 'selected="selected"'; ?> >Nil</option>
                                <option value="Present" <?php if($result_details['STAPHYLO_COCCUS']=="Present") echo 'selected="selected"'; ?> >Present</option>

									
								<select>
				 
								</td>
								
								<td style="width:5%;" class="text-left">
								 <select class="form-control" name="V_CHOLERAE" id="V_CHOLERAE">
								 <option value="Absent" <?php if($result_details['V_CHOLERAE']=="Absent") echo 'selected="selected"'; ?> >Absent</option>
                                  <option value="Present" <?php if($result_details['V_CHOLERAE']=="Present") echo 'selected="selected"'; ?> >Present</option>

								<select>
								
								</td>
								
								<td style="width:5%;" class="text-left">
								  
								<select class="form-control" name="V_PARAHA_EMOLYTICU" id="V_PARAHA_EMOLYTICU">
								<option value="Absent" <?php if($result_details['V_PARAHA_EMOLYTICU']=="Absent") echo 'selected="selected"'; ?> >Absent</option>
                                 <option value="Present" <?php if($result_details['V_PARAHA_EMOLYTICU']=="Present") echo 'selected="selected"'; ?> >Present</option>

				  
				  <select>
								</td>
								
								
								<td style="width:5%;" class="text-left">
								  
								<select class="form-control" name="SALMOELLA" id="SALMOELLA">
								<option value="Absent" <?php if($result_details['SALMOELLA']=="Absent") echo 'selected="selected"'; ?> >Absent</option>
                                <option value="Present" <?php if($result_details['SALMOELLA']=="Present") echo 'selected="selected"'; ?> >Present</option>

				  				  <select>
								</td>
								
								<td style="width:5%;" class="text-left">
								 
								<select class="form-control" name="A_REPORT" id="A_REPORT">
								<option value="Satisafactory" <?php if($result_details['A_REPORT']=="Satisafactory") echo 'selected="selected"'; ?> >Satisafactory</option>
                                <option value="UnSatisafactory" <?php if($result_details['A_REPORT']=="UnSatisafactory") echo 'selected="selected"'; ?> >UnSatisafactory</option>

								  <select>
								</td>
								
								
								
                                
                                </tr>
							
							
    						
							</tbody>
							<tfoot>
   <!-- <tr class="text"><p><strong>6).Test Results are basedd on ELISA Screening</strong></p></tr>-->
 </tfoot>
    						
<style>
tfoot {
  display: table-footer-group;
  vertical-align: middle;
  border-color: inherit;
}
</style>


  
  </table>  

	


    			
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

   <?php include "footer.php"; ?>

  </div>



</body>

<Script>

$('document').ready(function(){
	
	var reg_pc=$('#PRODUCTION_CODES').val()+' & '+ $('#FARM_REG_NO').val();			
    		$('#P_CODES_REG_NUM').val(reg_pc);
			
			var reg_pc=$('#D_O_ANALYSIS').val()+' & '+ $('#D_O_COMPLEATION').val();
			
    		$('#DOA_DOC').val(reg_pc);
    
    	$('#PRODUCTION_CODES').on('change',function(){
			var reg_pc=$('#PRODUCTION_CODES').val()+' & '+ $('#FARM_REG_NO').val();			
    		$('#P_CODES_REG_NUM').val(reg_pc);
    	});
		
		
		
		$('#D_O_COMPLEATION').on('change',function(){
    
    		
			var reg_pc=$('#D_O_ANALYSIS').val()+' & '+ $('#D_O_COMPLEATION').val();
			
    		$('#DOA_DOC').val(reg_pc);
    	});
    
    	$('#continuebtn').on('click',function(){
    
    		$('form').submit();
    	});
    });    

</script>





</html>

