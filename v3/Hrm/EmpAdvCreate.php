<?php

$Status=$_SESSION['Status'];


$PageTitleFolder="HRM";
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

 
extract($_POST);
extract($_GET); 
$EmpID=$_GET['EmpID'];

$SqlEmployeeDetails="Select * from HrmEmployeeDetails where EmpID='$EmpID'";
$StmtEmployeeDetails=$iconn->query($SqlEmployeeDetails);
$RowEmployeeDetails=$StmtEmployeeDetails->fetch_array();
$BioMetricID=$RowEmployeeDetails['BioMetricID'];

$PageTitle="Employee Advance - "  . $RowEmployeeDetails['EmpName']  ;

 $SqlEmpLeaveDetails="Select * from HrmCashAdvance where EmpID='$EmpID'"; 
 $StmtEmpLeaveDetails=$iconn->query($SqlEmpLeaveDetails);
 
 $SqlOptions="Select * from AdminOptions where DeptName='HRM' ORDER BY SortOrder ASC"; 
 $StmtOptions=$iconn->query($SqlOptions);

if(isset($_POST['submit'])){
	
	
	$StartDate=$StartDate;
	$EndDate=$EndDate;
	
 	$SqlSalaryCheck="SELECT *,SUM(Basic+HRA+DailyAllowance+SpecialAllowance+MedicalAllowance+Conveyence+Others+Incentives) 
	as TotalSalary FROM `HrmEmpSalaryDetails` WHERE EmpID ='$EmpID'";
	$StmtSalaryCheck=$iconn->query($SqlSalaryCheck);
	$RowSalaryCheck = $StmtSalaryCheck->fetch_array();
	$TotalSalary=$RowSalaryCheck['TotalSalary'];

	$SqlAdvAmount="SELECT COALESCE(sum(CurrentPending),0) as CurrentPending from HrmCashAdvance WHERE EmpID='$EmpID'"; 
    $StmtAdvAmount=$iconn->query($SqlAdvAmount);  
	$RowAdvAmount = $StmtAdvAmount->fetch_array();
	$CurrentPending=$RowAdvAmount['CurrentPending'];
	
	/*$AvalLimit= (2*$TotalSalary)-$CurrentPending;
	
	if($AdvAmount > $AvalLimit){
	echo '<script>alert("Available Loan Amount Is'.$AvalLimit .'")</script>'; 
	}else{*/
			
  $SqlInsertLeave="INSERT INTO `HrmCashAdvance`(`AdvDate`, `EmpID`, `AdvAmount`, `CurrentPending`, 
									`DueDate`, `DueAmount`, `Installments`, `Remarks`, `Status`) VALUES (
									'$AdvDate','$EmpID','$AdvAmount','$AdvAmount','','$Installments','$Installments',
									'$Remarks','0')"; 
	//}

   if($StmtInsertLeave=$iconn->query($SqlInsertLeave)){
	legacyAuditLogInsertById('HrmCashAdvance', 'CAdvID', $iconn->insert_id, $_POST, array(), 'Hrm');
	$_SESSION['Status'] = 'Advance Added successfully';
	header("Location:EmpAdvCreate.php?EmpID=".$EmpID);
	}
	
	else{
	$_SESSION['Status'] = $iconn->error ."Unable To Add Adavance";
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
			
		
				

				<div class="form-group  col-lg-3">
				<label for="exampleInputEmail1">Reason</label>
                    <input class="form-control" type="text"
					id="Remarks" name="Remarks"
					placeholder="Enter Reason" value="" required>                 
                 </div>
				
				
				<div class="form-group  col-lg-3">
				<label for="exampleInputEmail1">Amount</label>
                    <input class="form-control" type="text"
					id="AdvAmount" name="AdvAmount"
					placeholder="Enter Amount" value="" required>                 
                 </div>
				 
				 
				 <div class="form-group  col-lg-3">
				<label for="exampleInputEmail1">Deduction %(Per Month)</label>
                    <input class="form-control" type="text"
					id="Installments" name="Installments"
					placeholder="Enter Amount" value="" required>                 
                 </div>
				
				
				
							

				<div class="form-group  col-lg-3">
				<label for="exampleInputEmail1">Loan Date</label>
                    <input class="form-control" type="date"
					placeholder="" value="<?php echo date('Y-m-d')?>" 
					id="AdvDate" name="AdvDate" required>                 
                  </div>
				  
				  
				
				
			  
        <!-- end row -->
					</div>
			<div class="col-xs-12 box-footer"  align="right">
                <button  type="submit"  name='submit'  class="btn btn-primary waves-effect waves-light">
				<i class= 'ri-add-fill align-middle ml-2'>
				 </i>
			   Add</button> 
              </div>
			  
			  </form>
			  
			  
			   <table id="example2" class="table table-bordered table-hover">
              

    <thead>

      <tr>
	  
	  <th>S.No</th>
	  <th>Reason</th>
	  <th>Amount</th>
	  <th>Date</th>
	  <th>Deduction %(Month) </th>
	  <th>Tools</th>
	  
	  	
      </tr>

    </thead>

    <tbody>
	<?php
$SNo=1;
   while($RowEmpLeaveDetails=$StmtEmpLeaveDetails->fetch_array()){
	   
	   $AdvAmount=$RowEmpLeaveDetails['AdvAmount'];
	   $Remarks=$RowEmpLeaveDetails['Remarks'];
	   $AdvAmount=$RowEmpLeaveDetails['AdvAmount'];
	   $Installments=$RowEmpLeaveDetails['Installments'];
	   $AdvDate=$RowEmpLeaveDetails['AdvDate'];
	   $AdvDate=date('d-m-Y' ,strtotime($RowEmpLeaveDetails['AdvDate']));
	
 
	?>
	
	<tr>
			
	<td id="<?php  echo $RowEmpLeaveDetails['EmpID'];?>" > <?php echo $SNo; ?></td>
	<td id="<?php  echo $RowEmpLeaveDetails['EmpID'];?>" > <?php echo $Remarks; ?></td>
	<td id="<?php  echo $RowEmpLeaveDetails['EmpID'];?>" > <?php echo $AdvAmount; ?></td>
	<td id="<?php  echo $RowEmpLeaveDetails['EmpID'];?>" > <?php echo $AdvDate; ?></td>
	<td id="<?php  echo $RowEmpLeaveDetails['EmpID'];?>" > <?php echo $Installments; ?></td>

	<td>
	
	 <a target="_blank" href="EmpAdvEdit?CAdvID=<?php  echo $RowEmpLeaveDetails['CAdvID'];?>" 
						 class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
						 title="" data-original-title="Add"><i class="mdi mdi-pencil font-size-18"></i></a>
	<!--<button class="btn btn-link text-danger Delete" data-id="<?php echo $RowEmpLeaveDetails['LeaveID']; ?>" 
			
							data-toggle="tooltip" data-placement="top" title="" value=""
							data-original-title="Delete"><i class="mdi mdi-trash-can font-size-18"></i></button>  -->						
	</td>		
        </tr>

	  <?php 
$SNo++;
   
   }
	  ?>

     </tbody>

  </table>

			  
			  
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
