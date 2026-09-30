<?php

$Status=$_SESSION['Status'];


$PageTitleFolder="HRM";
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

 
extract($_POST);
extract($_GET); 
$CAdvID=$_GET['CAdvID'];
$EmpID=$_GET['CAdvID'];

 $SqlCashAdv="Select * from HrmCashAdvance where CAdvID='$CAdvID'"; 
 $StmtCashAdv=$iconn->query($SqlCashAdv);
 $RowCashAdv=$StmtCashAdv->fetch_array();
 
 $EmpID=$RowCashAdv['EmpID'];
 

$SqlEmployeeDetails="Select * from HrmEmployeeDetails where EmpID='$EmpID'";
$StmtEmployeeDetails=$iconn->query($SqlEmployeeDetails);
$RowEmployeeDetails=$StmtEmployeeDetails->fetch_array();
$BioMetricID=$RowEmployeeDetails['BioMetricID'];

$PageTitle="Employee Advance - "  . $RowEmployeeDetails['EmpName']  ;


 
 $SqlOptions="Select * from AdminOptions where DeptName='HRM' ORDER BY SortOrder ASC"; 
 $StmtOptions=$iconn->query($SqlOptions);

if(isset($_POST['submit'])){
	
	
	$StartDate=$StartDate;
	$EndDate=$EndDate;
	$DuePerc=$Installments.'%';
	
 	$SqlSalaryCheck="UPDATE `HrmCashAdvance` SET `Installments`='$Installments',DueAmount='$DuePerc'  WHERE CAdvID='$CAdvID'";
	$oldCashAdv = legacyAuditFetchRowById('HrmCashAdvance', 'CAdvID', $CAdvID, 'Hrm');
	$StmtSalaryCheck=$iconn->query($SqlSalaryCheck);
	
	if($StmtSalaryCheck){
	legacyAuditLogUpdateById('HrmCashAdvance', 'CAdvID', $CAdvID, $oldCashAdv, $_POST, array(), 'Hrm');
	echo '<script>alert("Available Loan Amount Is'.$AvalLimit .'")</script>'; 
	header("Location:EmpAdvCreate.php?EmpID=".$EmpID);	
	}
}
	
		
	  
	  


?>




<!DOCTYPE html>


<html lang="en">

    <head>
        
                <?php include ($IncludesPath."title-meta.php"); ?>
		 		<?php  include ("Includes/DbLinks.php");?>
                <?php include ($IncludesPath."head-css.php");?>

    </head>

    <body data-sidebar="dark">

	
	 <?php	include ($IncludesPath."PreLoader.php"); ?>

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

      
       <div class="row">
                            <div class="col-12">
                                <div class="card">
								
								<form role="form" method="post">
                                    <div class="card-body">
									
										<div class="row">
			
		
				

				<!--<div class="form-group  col-lg-3">
				<label for="exampleInputEmail1">Reason</label>
                    <input disabled class="form-control" type="text" 
					id="Remarks" name="Remarks" value="<?php echo $RowCashAdv['Remarks'] ?>"
					placeholder="Enter Reason"  required>                 
                 </div>
				
				
				<div class="form-group  col-lg-3">
				<label for="exampleInputEmail1">Amount</label>
                    <input disabled class="form-control" type="text"
					id="AdvAmount" name="AdvAmount"  
					placeholder="Enter Amount" value="<?php echo $RowCashAdv['AdvAmount'] ?>" required>                 
                 </div>  -->
				 
				 
				 <div class="form-group  col-lg-3">
				<label for="exampleInputEmail1">Deduction %(Per Month)</label>
                    <input class="form-control" type="text"
					id="Installments" name="Installments"
					placeholder="Enter Amount" value="<?php echo $RowCashAdv['Installments'] ?>" required>                 
                 </div>
				
				
				
					<!--		

				<div class="form-group  col-lg-3">
				<label for="exampleInputEmail1">Loan Date</label>
                    <input class="form-control" type="date" disabled
					placeholder="" value="<?php echo $RowCashAdv['AdvDate'] ?>" 
					id="AdvDate" name="AdvDate" required>                 
                  </div>
				  --->
				  
				
				
			  
        <!-- end row -->
					</div>
			<div class="col-xs-12 box-footer"  align="right">
                <button  type="submit"  name='submit'  class="btn btn-primary waves-effect waves-light">
				<i class= 'ri-add-fill align-middle ml-2'>
				 </i>
			   Update</button> 
              </div>
			  
			  </form>
			  
			  
			  

			  
			  
									</div>
									</div>
        
                                         <!-- end col -->
                        </div> <!-- end row -->
    
 



 </div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->
                        </div>
                        <!-- end row-->
                        
                    </div> <!-- container-fluid -->
                </div>
                <!-- End Page-content -->

                 <?php include ($IncludesPath."footer.php");?>
                
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        

		<?php include ($IncludesPath."vendor-scripts.php");?>
		<?php include ("Includes/NewScripts.php");?>
      
  
       
    </body>
</html>	
<script>
 
  $(document).on('click', '.Delete', function(e){
e.preventDefault();
	  var LeaveID =$(this).data('id');

	//alert(<?php echo $ODId ?>);
	
	 /*var DeptID=$('#DeptID').val();
	 var SubDeptID=$('#SubDeptID').val();*/
	 //var EmpStatus = $(this).val();
	  var Type = 'DeleteLeave';
   
     var r = confirm("Are You Sure You Want To Delete?");
  if (r == true) {
   
	$.ajax({
	
	type: 'POST',
    url: 'Ajax/AjDeptDetails.php',
    data: {'LeaveID': LeaveID,'Type': Type},
    
    success: function(response){
		var obj = JSON.parse(JSON.stringify(response));

		//showData(data);
		 if(obj.error == null){
			 alert("Deleted  Successfully");
			//alert(obj.Msg);
         location.reload();
		showData(data);
		 }else{
			 			 alert(obj.error);

		 }

	 
     }
  });
  }
else{}
    
  });
 

</script>
