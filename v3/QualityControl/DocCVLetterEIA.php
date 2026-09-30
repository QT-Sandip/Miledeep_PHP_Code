<?php
session_start();
$QueryParms=$_GET['Q']; 
 $QueryParm=$_GET['Q']; 
//ini_set( 'display_errors', 1 );
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

extract($_POST);
extract($_GET);




$PoID=$_GET['PoID'];


$SqlInvoice="SELECT * FROM $TblScrmPoData where PoID='$PoID'";
$StmtInvoice=$iconn->query($SqlInvoice);
$RowInvoice=$StmtInvoice->fetch_array();

$sqlSamplingLetter="SELECT * FROM $TblQcSamplingLettersEIA where INVOICE_ID='$PoID'";
$stmtSamplingLetter=$iconn->query($sqlSamplingLetter);
$RowSamplingLetter=$stmtSamplingLetter->fetch_array();



$SqlTests="SELECT * FROM $TblQcTestList ORDER BY `TestCategory` ASC";
$StmtTests=$iconn->query($SqlTests);



 $PageTitle="EIA SAMPLING COVERING NOTE - ".$RowInvoice['PoNo'];

$PageTitleFolder="Quality Control";

extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	
if($PoID==''){
		$PoID=0;
	}
	
	$COLD_STORE_TEMP='-18.0˚C';
	$PRODUCT_CORE_TEMP='-21.0˚C';
	
	 $SqlInsert="INSERT INTO `QcSamplingLettersEIA`
	 (`PLACE_DATE_TIME_SAMP`,`NO_CARTON`,`CDPR_SIGN`, `COLD_STORE_TEMP`,
	 `PRODUCT_CORE_TEMP`, `FARM_ID`, `CARTON_SEAL_NO`, 
	 `SAMPLE_SEAL_NO`, `BOX_SEAL_NO`, `PARMS_TO_BE_TESTED`, 
	 `REMARKS`, `SAMPLED_BY`, `SING_EIA_OFF`, `SING_REP`,
	 `LAB`, `DATE_TIME_OF_REC_SAMPLE`, `REFERENCE_NUMBER`,
	 `INVOICE_ID`, `PAYMENT_REMARKS`,`QTY_NO_SAM_REF`,`QTY_NO_SAMPLE_TESTING`) VALUES (
	'$PLACE_DATE_TIME_SAMP','$NO_CARTON','$CDPR_SIGN','$COLD_STORE_TEMP',
	'$PRODUCT_CORE_TEMP','$FARM_ID','$CARTON_SEAL_NO',
	'$SAMPLE_SEAL_NO','$BOX_SEAL_NO','$PARMS_TO_BE_TESTED','$REMARKS',
	'$SAMPLED_BY','$SING_EIA_OFF','$SING_REP',
	'$LAB','$DATE_TIME_OF_REC_SAMPLE','$REFERENCE_NUMBER',
	'$PoID','$PAYMENT_REMARKS','$QTY_NO_SAM_REF','$QTY_NO_SAMPLE_TESTING')";
	
	//echo $SqlInsert; exit;

	$StmtInsert=$iconn->query($SqlInsert);
	$iid = $iconn->insert_id;

	if($StmtInsert){
		legacyAuditLogInsertById('QcSamplingLettersEIA', 'ID', $iid, $_POST, ['Remarks' => 'Created EIA Sampling Letter'], 'QualityControl');
	}

	$ar = explode (",", $PARMS_TO_BE_TESTED);	
	$type = explode (",", $type);	


	
	$length=count($ar); 
	$length1=count($type); 

	if($StmtInsert){
		
		for ($i = 0; $i < $length; $i++) {
			for($k=0;$k<$length1;$k++){
				
				 if($k==$i){
				$sql_org_insert1="INSERT INTO $TblQcEiaAnalysisRequired
				(`ReportId`, `Type`, `TestName`) VALUES 
				('$iid','$type[$k]','$ar[$i]')";
				$stmt2=$iconn->query($sql_org_insert1);
				$eaAnalysisId = $iconn->insert_id;
				legacyAuditLogInsertById('QcEiaAnalysisRequired', 'ID', $eaAnalysisId, $_POST, ['Remarks' => 'Created EIA Analysis Required'], 'QualityControl');
				 }
				
				}
				}
		
		header('Location: DocAddReports.php?Q='.$QueryParm);
	}
	}
 ?>
 
 
<!DOCTYPE html>

<html lang="en">

    <head>
        
         <?php include ($IncludesPath."title-meta.php"); ?>
		 		<?php  include ($IncludesPath."DbLinks.php");?>
 

            <?php include ($IncludesPath."head-css.php");?>

    </head>

    <body data-sidebar="dark">

	
	 <?php
			include ($IncludesPath."PreLoader.php"); ?>

        <!-- Begin page -->
        <div id="layout-wrapper">


            <?php
			include ($IncludesPath."topbar.php");
			include ($IncludesPath."sidebar.php"); 
			?>



 <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">

                <div class="page-content">
                    <div class="container-fluid">
					
						 <?php
	if($Status !=''){
	//Show Alert 
	?>
	 <div class="alert alert-success alert-dismissible fade show" role="alert">
                                                <i class="mdi mdi-check-all mr-2"></i>
                                                      <?php echo $Status;
$_SESSION['Status']='';	  ?>
                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>

<?php } ?>

                        <?php include ($IncludesPath."page-title.php");?>

	
<div class="modal fade" id="myModal1" role="dialog">
    <div class="modal-dialog">
        <!-- Modal content-->
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal">&times;</button>
                <h4 class="modal-title modheading"></h4>
            </div>
            <div class="modal-body checkdiv1">
			
			<?php 

			  $Name='';
			  ?>			   
			   		<?php while($RowTests=$StmtTests->fetch_array()){

			  if($Name!= $RowTests['TestCategory']){
				$Name=$RowTests['TestCategory'];
				?>
		
		  <h3><?php echo $RowTests['TestCategory'];?></h3>	
		<?php }?>

				<div class = "checkbox">
               <label>
			   
			    
				<input 
				type = "checkbox" id="<?php echo $RowTests['TestCategory'];?>" 
				value = "<?php echo $RowTests['TestName'];?>">
				<?php echo $RowTests['TestName'];?>
			   
			   </label>
			   </div>
			   
			  <?php } ?>
			</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

	 






  <div class="row">
                            <div class="col-12">
                                <div class="card">
								
								<form role="form" method="post" >
                                    <div class="card-body">
									
										<div class="row">

    
             <div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Reference Number</label>
                  <input required type="text" class="form-control" name='REFERENCE_NUMBER' id="REFERENCE_NUMBER"
				  placeholder="Enter Reference Number" value="<?php echo $RowSamplingLetter['REFERENCE_NUMBER']?>">
                </div>
				
				<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Date </label>
                  <input required type="date"value="<?php  echo date("Y-m-d");?>" class="form-control" name='DATE_TIME_OF_REC_SAMPLE' id="DATE_TIME_OF_REC_SAMPLE"
				  placeholder="Enter Date" value="<?php echo $RowSamplingLetter['DATE_TIME_OF_REC_SAMPLE']?>">
                </div>
		
			<div class="form-group col-sm-3" >              									 
                <label for="exampleInputEmail1">Process Type</label>
				<select class="form-control select2" name="PRODUCT_NAME" id="PRODUCT_NAMEE">
					<option value="RAW">RAW</option>
					<option value="COOKED">COOKED</option>		
				<select>
                </div>	
               	
               
				
		
				
               
                <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Place,Date and Time of Sampling </label>
               <input required type="text" class="form-control" name="PLACE_DATE_TIME_SAMP" id="PLACE_DATE_TIME_SAMP" 
			   placeholder="Enter Place,Date and Time" value="">
                </div>
				
				
				
				
                <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">No.of Cartons Selected for Sampling</label>
                  <input required type="text" class="form-control" name="NO_CARTON" id="NO_CARTON"  
				  placeholder="Enter no.of cartons selected for sampling" value="<?php echo $RowSamplingLetter['NO_CARTON']?>">
                </div>
                
				
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Sample Carton Seal No.</label>
                   <input required type="text" class="form-control"  name="CARTON_SEAL_NO" 
				   id="CARTON_SEAL_NO"  placeholder="Enter Sample carton seal number"> 
               
                </div>
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Sample Seal No.</label>
                   <input required type="text" class="form-control"  
				   name="SAMPLE_SEAL_NO" id="SAMPLE_SEAL_NO"
                   placeholder="Enter Sample seal number"				   >
               
                </div>
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Sample Box Seal No.</label>
                   <input required type="text" class="form-control"  
				   name="BOX_SEAL_NO" id="BOX_SEAL_NO" placeholder="Enter Sample Box Seal No">
               
                </div>
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Parameter to be Tested</label>
                   <input required  autocomplete='off' type="text" class="form-control"  name="PARMS_TO_BE_TESTED" id="PARMS_TO_BE_TESTED" 
				   placeholder="Click To Select">
                <input type="hidden" class="form-control"  name="type" id="type"  required>
                </div>
				
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Details of Testing Fee Paid</label>
                   <input required type="text" class="form-control"  name="PAYMENT_REMARKS" id="PAYMENT_REMARKS" placeholder="Enter Testing Fee Paid">
               
                </div>
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Sampled by</label>
                   <input required type="text" class="form-control"  name="SAMPLED_BY" id="SAMPLED_BY"
				   placeholder="Enter Sampler Name"
				   value="<?php echo $invoice_details['']?>">
               
                </div>
				
				
				
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Remarks</label>
                   <input required type="text" class="form-control"  name="REMARKS" id="REMARKS" 
				   placeholder="Enter Remarks">
               
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Whether the CDPR & Dispatch Register</label>
                <select class="form-control select2" name="CDPR_SIGN" id="CDPR_SIGN"  required>
				  <option >Yes</option>
				  <option >No</option>
				  <option >NOT-APPLICABLE</option>
				  <select>
                </div>
				
				
				
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Quantity and No.of Sample for Testing</label>
                   <input required type="text" class="form-control"  
				   name="QTY_NO_SAMPLE_TESTING" id="QTY_NO_SAMPLE_TESTING"
				   placeholder="Enter Quantity and No. of samples for testing">
               
                </div>
				
				
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Quantity and No.of Sample for Reference</label>
                   <input required type="text" class="form-control"  name="QTY_NO_SAM_REF"
				   id="QTY_NO_SAM_REF" placeholder="Enter Quantity and No. of samples for reference">                
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Signature of the Rep of the Establishment:</label>
                   <input required type="text" class="form-control"  name="SING_REP" id="SING_REP" placeholder="Signature">
               
                </div>
                </div>

       
				
				
				
			  <div class="col-xs-12 box-footer"  align="right">
			  <a  href="DocAddReports.php?Q=<?php echo $QueryParm ?>" class="btn btn-danger">Close</a>

                <button type="submit"  name='submit' class="btn btn-primary">Submit</button>
              </div>
            </form>

   </div> <!-- end row -->
    
	</div> <!-- end card body-->
                                </div> <!-- end card -->
                          




                </div>
                <!-- End Page-content -->

                 <?php include ($IncludesPath."footer.php");?>
                
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        

		<?php include ($IncludesPath."vendor-scripts.php");?>
		<?php include ($IncludesPath."NewScripts.php");?>
		
  
       
    </body>
</html>				


<Script>


$('document').ready(function(){   
 $('.checkdiv input').change(function() {		
 var arr=[];	  if (this.checked) {    arr.push(this.value);  } 
 else {   arr.splice(arr.indexOf(this.value), 1);  } 
 $('#PRODUCT_NAME').val(arr + '');  });$('#PRODUCT_NAME').click(function() {	
 $('#myModal').modal('toggle'); });$('#PARMS_TO_BE_TESTED').click(function() {  
 $('#myModal1').modal('toggle'); });    var ar=[];  var type=[];	
 $('.checkdiv1 input').change(function() {  if (this.checked) {
	 ar.push(this.value);	 type.push(this.id);
	 }  else {   ar.splice(ar.indexOf(this.value), 1); 
	 type.splice(type.indexOf(this.value), 1);   } 
	 $('#PARMS_TO_BE_TESTED').val(ar + ''); $('#type').val(type + '');  });});

</script>

