<?php



//session_start();

include "../includes/config.php";

$INVOICE_ID=$_GET['id'];


$sql_invoice="SELECT * FROM `INVOICE_DATA` where ID='$INVOICE_ID'";
$stmt_invoice=$iconn->query($sql_invoice);
$invoice_details=$stmt_invoice->fetch_array();

$sql_varities="SELECT * FROM `VARITIES_DATA`";
$stmt_varities=$iconn->query($sql_varities);

$sql_tests="SELECT * FROM `ANALYSIS_TEST` ORDER BY `ANALYSIS_TEST`.`TEST_CATEGORY` ASC";
$stmt_tests=$iconn->query($sql_tests);


extract($_POST);
extract($_GET);

if(isset($_POST['submit'])){
	
	if($INVOICE_ID==''){
		$INVOICE_ID=0;
	}
	
	$doa = date("d-m-Y", strtotime($doa));
	$doi = date("d-m-Y", strtotime($doi));
	
	$sql_org_insert="INSERT INTO `SAMPLE_REG_FORM_DATA`(`REFERENCE_NUMBER`,`APPROVAL_NO`, `NAME_OF_EXPORTER`, `LOCATION_OF_SMAPLING`, `NAME_OF_IMPORTER_ADDRESS`,
	`TYPE_N_NATURE`, `NAME_OF_SPECIES`, `TYPE_OF_PACKING`, `TOTAL_NO_M_CARTON`, `PROD_CODES`, `GRADES`, 
	`BRAND`, `SAMPLE_NAME`, `PO_ORDER_NUMBER`, `PO_ORDER_DATE`, `POND_ID_N_FARM_REG`, `SAMPLE_DRAWN_BY`, `ANALYSIS_REQ`,`DATE`,`LAB_NAME`,`VERIFIED_BY`,`INVOICE_ID`) VALUES (
	
	'$REFERENCE_NUMBER','$approvalno','$exporter','$locationofsampling',
	'$nmeofimpdd','$TYPE_N_NATURE','$NAME_OF_SPECIES','$tofpcking','$TnofMc','$Productioncode','$grades',
	'$brand','$productionsname','$P_ORDER_N_P_Number','$P_ORDER_N_P_DATE','$POND_ID_N_FARM_REG','$notrtwhdts','$ar','$DATE','$LAB_NAME','$VERIFIED_BY','$INVOICE_ID')";
	
	 $sql_org_insert;
	$stmt1=$iconn->query($sql_org_insert);
	$iid = $iconn->insert_id;
	legacyAuditLogInsertById('SAMPLE_REG_FORM_DATA', 'ID', $iid, $_POST, ['Remarks' => 'Created Sample Registration Form Data'], 'QualityControl');
     $ar = explode (",", $ar);
	 $type = explode (",", $type);
	 //print_r($ar);
$length=count($ar);
//echo $length;
//echo $length1; 
$length1=count($type);
//echo $length1; 
//print_r($ar); 
//print_r($type);
if($stmt1){
	
	 for ($i = 0; $i < $length; $i++) {
 for ($k = 0; $k < $length1; $k++) {
	// echo $k.$i;

// echo $type[k];
// echo $ar[$i];
 if($k==$i){
	 //print $value;
  $sql_org_insert1="INSERT INTO `SAMPLE_REG_ANALYSIS_REQUIRED`
 (`SAMPLE_REG_ID`, `TYPE`, `TEST_NAME`) VALUES ('$iid','$type[$k]','$ar[$i]')";
	 $sql_org_insert1;
	
	$stmt1=$iconn->query($sql_org_insert1);
	$saAnalysisId = $iconn->insert_id;
	legacyAuditLogInsertById('SAMPLE_REG_ANALYSIS_REQUIRED', 'ID', $saAnalysisId, $_POST, ['Remarks' => 'Created Sample Registration Analysis Required'], 'QualityControl');
 }
 }
}
	 //print_r($type);
	 
	 
	 //exit;
		
		//header('Location: printelisreport.php?id='.$iid);
		//header('Location: SampleRegistrationReports.php');
		header('Location: DocAddReports.php?id='.$INVOICE_ID);
	}
}

 ?>



<!DOCTYPE html>

<html>

<head>

<?php include "../includes/meta.php"; ?>

 </head>

<body class="hold-transition skin-blue sidebar-mini fixed">

   

<div class="wrapper">

<?php include "../includes/header.php"; ?>

  <!-- Left side column. contains the logo and sidebar -->

 <?php include "../includes/sidebar.php"; ?>

  <!-- Content Wrapper. Contains page content -->

  <div class="content-wrapper">

    <!-- Content Header (Page header) -->

    <section class="content-header">

        

<h2 align="center">SAMPLE REGISTRATION  TEST REPORT - <span style="color:Tomato; bold"><?php echo $invoice_details['PO_NUM'] ?></span></h3>


	</a>



			</a>

<ol class="breadcrumb">

        <li><a href="index.php"><i class="fa fa-dashboard"></i> Home</a></li>

        <li class="active">New Sample Registration Report </li>

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
            <div class="modal-body checkdiv">
			
			<?php while($row=$stmt_varities->fetch_array()){ ?>				
				
				
				 <div class = "checkbox">
               <label><input type = "checkbox" 
			   value="<?php echo $row['VARITY_NAME'].'(TREATED)';?>"><?php echo $row['VARITY_NAME'].'(TREATED)';?></label></div>
			   
			   
			    <div class = "checkbox">
               <label><input type = "checkbox" 
			   value="<?php echo $row['VARITY_NAME'].'(NON TREATED)';?>"><?php echo $row['VARITY_NAME'].'(NON TREATED)';?></label></div>
				
				<?php } ?>
			
			
			</div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

	
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
f			  ?>			   
			   
			  <?php while($row=$stmt_tests->fetch_array()){

			  if($Name!= $row['TEST_CATEGORY']){
				$Name=$row['TEST_CATEGORY'];
				?>
		
		 <h3><?php echo $row['TEST_CATEGORY'];?></h3>	
		<?php }?>

			  
			 			  
			   
			  

				<div class = "checkbox">
               <label>
			   
			    
				<input 
				type = "checkbox" id="<?php echo $row['TEST_CATEGORY'];?>" 
				value = "<?php echo $row['TEST_NAME'];?>">
				<?php echo $row['TEST_NAME'];?>
			   
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

	 


    <!-- Main content -->

    <section class="content">

      <!-- Small boxes (Stat box) -->
	  
	  

	  
	 
    <div id="tdetails" class="table-responsive" >  

        <div class="box">

          <form role="form" method="post">
              <div class="box-body">
			  
			 
				
				 
			 
			 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Lab Name</label>
                <select class="form-control" name="LAB_NAME" id="LAB_NAME"  required>
				  <option >IF LAB</option>
				  <option >NCML LAB</option>
				 
				  <select>
                </div>
			  
			   <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">NATURE OF MATERIAL</label>
                <select class="form-control" name="TYPE_N_NATURE" id="TYPE_N_NATURE"  required>
				  <option >RAW</option>
				  <option >COOKED</option>
				 
				  <select>
                </div>
			  
                <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">REFERENCE NO</label>
                  <input type="text" class="form-control" name='REFERENCE_NUMBER' id="REFERENCE_NUMBER" placeholder="Enter Name of Reference Number"
				  value="<?php echo $invoice_details['REFERENCE_NUMBER']?>"   required>
                </div><div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">APPROVAL NO</label>
                  <input type="text" class="form-control" name='approvalno' id="approvalno" value="999"   required>
                </div>
               
               
				
				 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">REPORT DATE</label>
                  <input type="date"value="<?php  echo date("Y-m-d");?>" class="form-control" name='DATE' id="DATE" value="<?php echo $invoice_details['']?>"    required>
                </div>
                
			  
                
			
				
				
                <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Name of the Reprsentation</label>
                  
				  <input type="text" class="form-control"  name="notrtwhdts" id="notrtwhdts" placeholder="Enter Name - Designation "
value="<?php echo $invoice_details['NAME_OF_REP']?>"				  required>
                </div>
				
				 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Verified By </label>
                  <input type="text" class="form-control" name='VERIFIED_BY' id="VERIFIED_BY" 
value="<?php echo $invoice_details['VARIFIED_ID']?>"	 placeholder="Enter Name - Designation "			  required>
                </div>
                
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Analysis Required</label>
                  <input type="text" autocomplete="off" class="form-control"  name="ar" id="ar" placeholder="Click To select Requied Analysis"
   required>
                      <input type="hidden" class="form-control"  name="type" id="type"  required>
                            
			  </div>
              <button type="submit"  name='submit' class="btn btn-primary right">Submit</button>
              
			  
			  
			 </div>
</div>



    </section>

    <!-- /.content -->

 

  <!-- /.content-wrapper -->

   <?php include "footer.php"; ?>

  </div>

<div class="modal fade" id="modal-default">
          <div class="modal-dialog">
            <div class="modal-content">
              <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title">Default Modal</h4>
              </div>
              <div class="modal-body">
                <p>One fine body&hellip;</p>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-default pull-left" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary">Save changes</button>
              </div>
            </div>
            <!-- /.modal-content -->
          </div>
          <!-- /.modal-dialog -->
        </div>

</body>

<Script>

$('document').ready(function(){
    var arr=[];
	$('.checkdiv input').change(function() {
  if (this.checked) {
    arr.push(this.value);
  }
  else {
   arr.splice(arr.indexOf(this.value), 1);
  }
  $('#tnofproduct').val(arr + '');
});
    	
    	$('#continuebtn').on('click',function(){
    
    		$('form').submit();
    	});
        
$('#tnofproduct').click(function(e) {
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
  </script>





</html>

