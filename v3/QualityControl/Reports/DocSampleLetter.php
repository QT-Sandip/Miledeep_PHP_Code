<?php

$PoID=$_GET['PoID'];

 include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
 include ($cp."Includes/IncludePage.php");



$CurrentYear=date("Y");
$LastDate=$CurrentYear.'-03-31';
$NextYear=$CurrentYear+1;
$YearNumber=substr($NextYear, -2);
$FinancialYear=$CurrentYear.'-'.$YearNumber;

$sql_invoice="SELECT * FROM `$TblScrmPoData` where PoID='$PoID'";
$stmt_invoice=$iconn->query($sql_invoice);
$invoice_details=$stmt_invoice->fetch_array();

 /*$sql_varities="SELECT * FROM `VARITIES_DATA`"; 
$stmt_varities=$iconn->query($sql_varities);*/

 $sql_tests="SELECT * FROM $TblQcTestList ORDER BY `TestCategory` ASC"; 
$stmt_tests=$iconn->query($sql_tests);

$SqlEiaLabs="SELECT * FROM `$QcEiaLabNames` WHERE Status=1;"; 
$StmtEiaLabs=$iconn->query($SqlEiaLabs);

$sql_LAB_USERS="SELECT * FROM `$TblQcLabTechnologist` where LbuStatus=1";
$stmt_LAB_USERS=$iconn->query($sql_LAB_USERS);
$stmt_LAB_USERS1=$iconn->query($sql_LAB_USERS);


extract($_POST);
extract($_GET);

if(isset($_POST['submit'])){
	
	if($PoID==''){
		$PoID=0;
	}
	
	$doa = date("d-m-Y", strtotime($doa));
	$doi = date("d-m-Y", strtotime($doi));
	
	$SqlReportCount="Select count(*) as COUNT from `$TblQcSamplingLettersExt` where Date > '$LastDate'  ";
	$StmtReportCount=$iconn->query($SqlReportCount);
    $ReportCount=$StmtReportCount->fetch_array();
    $Count=$ReportCount['COUNT'];
   //  echo $SqlReportCount; exit;
	 $Number=$Count+1;
	
	// $ReferenceNumber='SME/'.$Number.'/'.$FinancialYear;
	
	
	$sql_org_insert="INSERT INTO `$TblQcSamplingLettersExt`
	(`SampleNumber`,`SampleLocation`, `TypeProduct`, `SampleDrawnBy`,`AnalysisReq`, 
	`TestingLab`, `Date`, `VerifiedBy`, `InvID`)
	VALUES ('$ReferenceNumber','$SAMPLE_LOCATION','$TYPE_N_NATURE',
	'$notrtwhdts','$ar','$LAB_NAME','$DATE','$VERIFIED_BY','$PoID')";
	
	
			
	 $sql_org_insert;
	$stmt1=$iconn->query($sql_org_insert);
	$iid = $iconn->insert_id;
     $ar = explode (",", $ar);
	 $type = explode (",", $type);
	 //print_r($ar);
$length=count($ar);

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
  $sql_org_insert1="INSERT INTO `$TblQcEiaAnalysisRequired`
 (`ReportId`, `Type`, `TestName`) VALUES ('$iid','$type[$k]','$ar[$i]')";
	 $sql_org_insert1;
	
	$stmt1=$iconn->query($sql_org_insert1);
 }
 }
}
	 
		header('Location: DocAddReports.php?PoID='.$PoID);
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

        

<h2 align="center">SAMPLE REGISTRATION  TEST REPORT - <span style="color:Tomato; bold"><?php echo $invoice_details['PoNo'] ?></span></h3>


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
			
			<?php /*while($row=$stmt_varities->fetch_array()){ ?>				
				
				
				 <div class = "checkbox">
               <label><input type = "checkbox" 
			   value="<?php echo $row['VARITY_NAME'].'(TREATED)';?>"><?php echo $row['VARITY_NAME'].'(TREATED)';?></label></div>
			   
			   
			    <div class = "checkbox">
               <label><input type = "checkbox" 
			   value="<?php echo $row['VARITY_NAME'].'(NON TREATED)';?>"><?php echo $row['VARITY_NAME'].'(NON TREATED)';?></label></div>
				
				<?php } */?>
			
			
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
			  ?>			   
			   
			  <?php while($row=$stmt_tests->fetch_array()){

			  if($Name!= $row['TestCategory']){
				$Name=$row['TestCategory'];
				?>
		
		 <h3><?php echo $row['TestCategory'];?></h3>	
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
				<?php while($RowEiaLabs=$StmtEiaLabs->fetch_array()){?>
				<option value="<?php echo $RowEiaLabs['LabName'];?>"><?php echo $RowEiaLabs['LabName'];?></option>
				<!--<option >VIMTA LAB</option>
				 <option >NCML Laboratories</option>
				<option >BV Lab PVT LTD</option>
				<option >SGS LAB</option>
				  <option >IF LAB</option>-->
				<?php } ?>
				  <select>
                </div>
			  
			   <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Nature of Material</label>
                <select class="form-control" name="TYPE_N_NATURE" id="TYPE_N_NATURE"  required>
				  <option >RAW</option>
				  <option >COOKED</option>
				 
				  <select>
                </div>
			  
               <!-- <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Reference No</label>
                  <input type="text" class="form-control" name='REFERENCE_NUMBER' id="REFERENCE_NUMBER" placeholder="Enter Reference Number"
				  value="<?php echo $invoice_details['REFERENCE_NUMBER']?>"   required>
                </div>
				
				--->
               
                 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Sample Location</label>
                  <input type="text" class="form-control" name='SAMPLE_LOCATION' id="SAMPLE_LOCATION" placeholder="Enter Sample Location"
				  value="<?php echo $invoice_details['SAMPLE_LOCATION']?>"   required>
                </div>
				
				
               
               
				
				 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Report Date</label>
                  <input type="date"value="<?php  echo date("Y-m-d");?>" class="form-control" name='DATE' id="DATE" value="<?php echo $invoice_details['']?>"    required>
                </div>
                
			  
                
			
				
				
                <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Name of the Representation</label>
                  
				  
               
			   
			    <select class="form-control" name="notrtwhdts" id="notrtwhdts">
				   
				  <option value="DRAWN BY TUV LAB REPRESENTATIVE">DRAWN BY TUV LAB REPRESENTATIVE</option>
				 <option value="DRAWN BY VIMTA LAB REPRESENTATIVE">DRAWN BY VIMTA LAB REPRESENTATIVE</option>
				<option value="DRAWN BY BV LAB REPRESENTATIVE">DRAWN BY BV LAB REPRESENTATIVE</option>
				<option value="DRAWN BY ITC LAB REPRESENTATIVE">DRAWN BY ITC LAB REPRESENTATIVE</option>
				<option value="SUBMITTED BY PARTY">SUBMITTED BY PARTY</option>
				 
				
						  
				  </select>

			   </div>
				
				 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Verified By </label>
                 
					
					 <select class="form-control" name="VERIFIED_BY" id="VERIFIED_BY">
				   
				   <?php while ($LAB_USERS_details=$stmt_LAB_USERS->fetch_array()){ ?>
				     <option value="<?php echo $LAB_USERS_details['LbuName'] ?>"><?php echo $LAB_USERS_details['LbuName'] ?></option>
				   
				 <?php  }?>
				
						  
				  </select>
                </div>
                
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Analysis Required</label>
                  <input type="text" autocomplete="off" class="form-control"  name="ar" id="ar" placeholder="Click to select Required Analysis"
   required>
                      <input type="hidden" class="form-control"  name="type" id="type"  required>
                            
			  </div>
             
			  
			  
			 </div>
			 
			 
			 <div class="box-footer" align="right">
			  <button  type="submit"  name='submit' class="btn btn-success right">Submit</button>
			  </div>
              
			 
			 
</div>



    </section>

    <!-- /.content -->

 

  <!-- /.content-wrapper -->

   <?php include "../includes/footer.php"; ?>
   <?php include "../includes/scripts.php"; ?>

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

