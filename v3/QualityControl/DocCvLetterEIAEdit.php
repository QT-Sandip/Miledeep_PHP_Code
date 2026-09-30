<?php


$QueryParms=$_GET['Q'];  
$QueryParm=$_GET['Q']; 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

include ($cp."Includes/IncludePage.php");


$REFERENCE_ID=$_GET['RID'];
$INV_ID=$_GET['PoID'];

$sql_invoice="SELECT * FROM `$TblScrmPoData` where PoID='$INV_ID'";
$stmt_invoice=$iconn->query($sql_invoice);
$invoice_details=$stmt_invoice->fetch_array();


 $sql="SELECT * FROM `$TblQcSamplingLettersEIA` where ID='$REFERENCE_ID'";

$stmt=$iconn->query($sql);

$post_details=$stmt->fetch_array();




/*$sql_varities="SELECT * FROM `VARITIES_DATA`";
$stmt_varities=$iconn->query($sql_varities);*/

//$sql_tests="SELECT * FROM `ANALYSIS_TEST` ORDER BY `ANALYSIS_TEST`.`TEST_CATEGORY` ASC";
 $sql_tests="SELECT * FROM $TblQcTestList ORDER BY `TestCategory` ASC"; 

$stmt_tests=$iconn->query($sql_tests);


extract($_POST);
extract($_GET);
if(isset($_POST['submit'])){
	

	$oldRowSampling = legacyAuditFetchRowById('QcSamplingLettersEIA', 'ID', $REFERENCE_ID, 'QualityControl');

	echo $sql_update="UPDATE  `$TblQcSamplingLettersEIA` SET  
 SPECIES_NAME='$SPECIES_NAME', 
 PLACE_DATE_TIME_SAMP='$PLACE_DATE_TIME_SAMP',
 NO_CARTON='$NO_CARTON',
 CDPR_SIGN='$CDPR_SIGN',
 COLD_STORE_TEMP='$COLD_STORE_TEMP',
 PRODUCT_CORE_TEMP='$PRODUCT_CORE_TEMP', 
QTY_NO_SAMPLE_TESTING='$QTY_NO_SAMPLE_TESTING',
QTY_NO_SAM_REF='$QTY_NO_SAM_REF',
CARTON_SEAL_NO='$CARTON_SEAL_NO',
SAMPLE_SEAL_NO='$SAMPLE_SEAL_NO',
BOX_SEAL_NO='$BOX_SEAL_NO',
PARMS_TO_BE_TESTED='$PARMS_TO_BE_TESTED',
REMARKS='$REMARKS',
SAMPLED_BY='$SAMPLED_BY',
SING_EIA_OFF='$SING_EIA_OFF',
SING_REP='$SING_REP',
LAB='$LAB',
DATE_TIME_OF_REC_SAMPLE='$DATE_TIME_OF_REC_SAMPLE',
PRODUCT_NAME='$PRODUCT_NAME',
PAYMENT_REMARKS='$PAYMENT_REMARKS'
					

                        WHERE ID='$REFERENCE_ID'";
	exit;
	 $sql_update;

	$stmt1=$iconn->query($sql_update);
	legacyAuditLogUpdateById('QcSamplingLettersEIA', 'ID', $REFERENCE_ID, $oldRowSampling, $_POST, ['Remarks' => 'Updated EIA Sampling Letter'], 'QualityControl');
	
		if($stmt1){
	
	

	$ar = explode (",", $PARMS_TO_BE_TESTED);
	$type = explode (",", $type);	 

	if($type[0]!=''){
		 $oldRowAnalysis = legacyAuditFetchRowById('QcEiaAnalysisRequired', 'ReportId', $REFERENCE_ID, 'QualityControl');
		 $sql_update="DELETE FROM `$TblQcEiaAnalysisRequired` WHERE ReportId='$REFERENCE_ID'";
		 $stmt1=$iconn->query($sql_update);
		 legacyAuditLogDeleteById('QcEiaAnalysisRequired', 'ReportId', $REFERENCE_ID, $oldRowAnalysis, ['Remarks' => 'Deleted EIA Analysis Required records'], 'QualityControl');
		 
		 $length=count($ar);
		$length1=count($type); 

	if($stmt1){
		
		for ($i = 0; $i < $length; $i++) {
			for($k=0;$k<$length1;$k++){
				
				 if($k==$i){
				$sql_org_insert1="INSERT INTO `$TblQcEiaAnalysisRequired`(`ReportId`, `Type`, `TestName`) VALUES
				('$REFERENCE_ID','$type[$k]','$ar[$i]')";	
				$stmt2=$iconn->query($sql_org_insert1);
				$eaInsId = $iconn->insert_id;
				legacyAuditLogInsertById('QcEiaAnalysisRequired', 'ID', $eaInsId, $_POST, ['Remarks' => 'Re-inserted EIA Analysis Required'], 'QualityControl');
				 }
				
				}
				}
		
		header('Location: DocAddReports.php?PoID='.$INV_ID);
	}
}else{
	//header('Location: Eia_Reports.php');
	header('Location: DocAddReports.php?PoID='.$INV_ID);
}
	
	
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

 <?php include "sidebar.php"; ?>

  <!-- Content Wrapper. Contains page content -->

  <div class="content-wrapper">

    <!-- Content Header (Page header) -->

    <section class="content-header">

        

<h2 align="center">EIA SAMPLING COVERING NOTE  - <span style="color:Tomato; bold"><?php echo $invoice_details['PoNo'] ?></span></h2>








			



	</a>



			</a>

			

			



<ol class="breadcrumb">

        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>

        <li class="active">Edit Eia Report</li>

      </ol>

    </section>

	
	
<div class="modal fade" id="myModal" role="dialog">    
<div class="modal-dialog">        <!-- Modal content-->        
<div class="modal-content">            
<div class="modal-header">                
<button type="button" class="close" data-dismiss="modal">&times;</button> 
               <h4 class="modal-title modheading"></h4>            </div>  
			


 <div class="modal-body checkdiv">
			
			<?php /*while($row=$stmt_varities->fetch_array()){ ?>				
				
				
				<div class = "checkbox">
               <label>
			   <input type = "checkbox" 
			   value="<?php echo $row['VARITY_NAME'].'(TREATED)';?>"><?php echo $row['VARITY_NAME'].'(TREATED)';?>
			   </label>
			   </div>
			   
			   
			    <div class = "checkbox">
               <label><input type = "checkbox" 
			   value="<?php echo $row['VARITY_NAME'].'(NON TREATED)';?>"><?php echo $row['VARITY_NAME'].'(NON TREATED)';?></label></div>
				
				<?php }*/ ?>
			
			
			</div>
			
			   <div class="modal-footer">                
			   <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>   
			   </div>        
			   </div>    
			   </div></div>	
			   <div class="modal fade" id="myModal1" role="dialog"> 
			   
			   <div class="modal-dialog">        <!-- Modal content-->        
			   <div class="modal-content">            
			   <div class="modal-header">               
			   <button type="button" class="close" data-dismiss="modal">&times;</button>   
			   <h4 class="modal-title modheading"></h4>            </div>   
			   <div class="modal-body checkdiv1">		

<?php 

			  $Name='';
			  ?>			   
			   
			  <?php while($row=$stmt_tests->fetch_array()){

			  if($Name!= $row['TestCategory']){
				$Name=$row['TestCategory'];
				?>
		
		 <h1><?php echo $row['TestCategory'];?></h1>	
		<?php }?>

			  
			 			  
			   
			  

				<div class = "checkbox">
               <label>
			   
			    
				<input 
				type = "checkbox" id="<?php echo $row['TestCategory'];?>" 
				value = "<?php echo $row['TestName'];?>">
				<?php echo $row['TestName'];?>
			   
			   </label>
			   </div>
			   
			  <?php } ?>
			   
			   
			   </div>            
			   <div class="modal-footer">                
			   <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>            
			   </div>        </div>    </div></div>

	

	



    <!-- Main content -->

    <section class="content">                   

      <!-- Small boxes (Stat box) -->
	  
	  

	  
	 
    <div id="tdetails" class="table-responsive" >  

        <div class="box">
		
		
          <form role="form" method="post">
              <div class="box-body">
			  
			 
			  
                
                <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Reference Number</label>
                  <input required type="text" class="form-control" name='REFERENCE_NUMBER' id="REFERENCE_NUMBER"
				  placeholder="Enter Reference Number" value="<?php echo $post_details['REFERENCE_NUMBER']?>">
                </div>


				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Date </label>
                  <input required type="date" class="form-control" name='DATE_TIME_OF_REC_SAMPLE' id="DATE_TIME_OF_REC_SAMPLE"
				  placeholder="Enter Date" value="<?php echo $post_details['DATE_TIME_OF_REC_SAMPLE']?>">
                </div>

				
				 <!--value="<?php  echo date("Y-m-d");?>"-->
                
				
				 <div class="form-group col-sm-3" >              									 
                <label for="exampleInputEmail1">Process Type</label>
				<select class="form-control" name="PRODUCT_NAME" id="PRODUCT_NAMEE">
					<option value="RAW">RAW</option>
					<option value="COOKED">COOKED</option>	

					
				<select>
                </div>	
				
               
				
				<!--<div class="form-group col-sm-3" >              									 
                <label for="exampleInputEmail1">Select Species Name</label>
				<select class="form-control" name="SPECIES_NAME" id="SPECIES_NAME">

		 <option value="PENAEUS VANNAMEI" <?php if($post_details['SCIENTIFIC_NAME']=="PENAEUS VANNAMEI") echo 'selected="selected"'; ?> 
		 >PENAEUS VANNAMEI</option>
		 <option value="LITOPENAUS VANNAMEI" <?php if($post_details['SCIENTIFIC_NAME']=="LITOPENAUS VANNAMEI") 
			 echo 'selected="selected"'; ?> >LITOPENAUS VANNAMEI</option>
		
		 <select>					 
				
                </div>	-->
				
                <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Place,date and Time of Sampling </label>
               <input type="text" class="form-control" name="PLACE_DATE_TIME_SAMP" id="PLACE_DATE_TIME_SAMP"
			   placeholder="Enter Place,date and time" value="<?php echo $post_details['PLACE_DATE_TIME_SAMP']?>">
                </div>
				
				
               
               
             
				
                <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">No.of cartons selected for sampling</label>
                  <input type="text" class="form-control" name="NO_CARTON" id="NO_CARTON"  
				  placeholder="Enter no.of cartons selected for sampling" value="<?php echo $post_details['NO_CARTON']?>">
                </div>
                <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Whether the CDPR&Despatch register</label>
                  <input type="text" class="form-control"  name="CDPR_SIGN" id="CDPR_SIGN"  value="<?php echo $post_details['CDPR_SIGN']?>">
                </div>
				
			<!--	 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Cold storage temp</label>
                    <input type="text" class="form-control"  name="COLD_STORE_TEMP" id="COLD_STORE_TEMP"
					placeholder="Enter cold storage temp" value="<?php echo $post_details['COLD_STORE_TEMP']?>">
               
                </div>
				
				 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Product core temp</label>
                   <input type="text" class="form-control"  name="PRODUCT_CORE_TEMP" id="PRODUCT_CORE_TEMP"
				   placeholder="Enter Product core Temp" value="<?php echo $post_details['PRODUCT_CORE_TEMP']?>">
               
                </div>-->
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Quantity and No. of sample for testing</label>
                   <input type="text" class="form-control"  name="QTY_NO_SAMPLE_TESTING" id="QTY_NO_SAMPLE_TESTING" value="<?php echo $post_details['QTY_NO_SAMPLE_TESTING']?>" >
               
                </div>
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Quantity and No. of sample for reference</label>
                   <input type="text" class="form-control"  name="QTY_NO_SAM_REF" id="QTY_NO_SAM_REF" value="<?php echo $post_details['QTY_NO_SAM_REF']?>">                
                </div>
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Sample carton seal No.</label>
                   <input type="text" class="form-control"  name="CARTON_SEAL_NO" id="CARTON_SEAL_NO" value="<?php echo $post_details['CARTON_SEAL_NO']?>"> 
               
                </div>
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Sample seal No.</label>
                   <input type="text" class="form-control"  name="SAMPLE_SEAL_NO" id="SAMPLE_SEAL_NO" value="<?php echo $post_details['SAMPLE_SEAL_NO']?>">
               
                </div>
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Sample Box seal No.</label>
                   <input type="text" class="form-control"  name="BOX_SEAL_NO" id="BOX_SEAL_NO"
				   placeholder="Enter Sample Box Seal No" value="<?php echo $post_details['BOX_SEAL_NO']?>">
               
                </div>
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Parameter to be tested</label>
                   <input autocomplete='off' type="text" class="form-control"  name="PARMS_TO_BE_TESTED" id="PARMS_TO_BE_TESTED"
				   placeholder="Click To Select" value="<?php echo $post_details['PARMS_TO_BE_TESTED']?>">
                <input type="hidden" class="form-control"  name="type" id="type"  required>
                </div>
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Details of Testing fee paid</label>
                   <input type="text" class="form-control"  name="PAYMENT_REMARKS" id="PAYMENT_REMARKS"
				   placeholder="Enter Testing Fee Paid" value="<?php echo $post_details['PAYMENT_REMARKS']?>">
               
                </div>
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Remarks</label>
                   <input type="text" class="form-control"  name="REMARKS" id="REMARKS"
				   placeholder="Sampled by" value="<?php echo $post_details['REMARKS']?>">
               
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Sampled by</label>
                   <input type="text" class="form-control"  name="SAMPLED_BY" id="SAMPLED_BY"
				   placeholder="Sampled by" value="<?php echo $post_details['SAMPLED_BY']?>">
               
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Signature of the rep of the establishment:</label>
                   <input required type="text" class="form-control"  name="SING_REP" id="SING_REP" placeholder="Sampled by" 
				   value="<?php echo $post_details['SING_REP']?>">
               
                </div>

                
				</div>
              <!-- /.box-body -->
			  <div class="col-xs-12 box-footer"  align="right">
                <button type="submit"  name='submit' class="btn btn-success">Submit</button>
              </div>

             
            </form>

        <div class="box-body">

		

		

		

 <div class="row">



    		</div>


		
<div class="col-xs-12"> 



    </section>

    <!-- /.content -->

 

  <!-- /.content-wrapper -->

   <?php include "../includes/footer.php"; ?>

  </div>



</body>

<Script>


$('document').ready(function(){   
 $('.checkdiv input').change(function() {		
 var arr=[];	  if (this.checked) {    arr.push(this.value);  } 
 else {   arr.splice(arr.indexOf(this.value), 1);  } 
 $('#PRODUCT_NAME').val(arr + '');  });$('#PRODUCT_NAME').click(function() {	    $('#myModal').modal('toggle'); });$('#PARMS_TO_BE_TESTED').click(function() {   $('#myModal1').modal('toggle'); });    var ar=[];  var type=[];	$('.checkdiv1 input').change(function() {  if (this.checked) {    ar.push(this.value);	 type.push(this.id);  }  else {   ar.splice(ar.indexOf(this.value), 1);  type.splice(type.indexOf(this.value), 1);   }  $('#PARMS_TO_BE_TESTED').val(ar + ''); $('#type').val(type + '');  });});

</script>

<Script>
/*
$('document').ready(function(){
    var arr=[];
	$('.checkdiv input').change(function() {
  if (this.checked) {
    arr.push(this.value);
  }
  else {
   arr.splice(arr.indexOf(this.value), 1);
  }
  $('#PRODUCT_NAME').val(arr + '');
});
    	
    	$('#continuebtn').on('click',function(){
    
    		$('form').submit();
    	});
        
$('#PRODUCT_NAME').click(function(e) {
     //alert('clicked');
	 $('#myModal').modal('toggle');
//$('.modal fade').modal('show');
//$('#myModal').modal('hide');
 });
 $('#ar').click(function(e) {
     //alert('clicked');
	 $('#myModal1').modal('toggle');
//$('.modal fade').modal('show');
//$('#myModal').modal('hide');
 });
 
 
  var ar=[];
  var type=[];
	$('.checkdiv1 input').change(function() {
  if (this.checked) {
    ar.push(this.value);
	 type.push(this.id);
  }
  else {
   ar.splice(ar.indexOf(this.value), 1);
  type.splice(type.indexOf(this.value), 1);
  
 }
  $('#ar').val(ar + '');
 $('#type').val(type + '');

  });
});

*/
  </script>






</html>

