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

$PageTitle="Employee Leaves - "  . $RowEmployeeDetails['EmpName']  ;

 $SqlEmpLeaveDetails="Select * from HrmLeaveRegister where EmpID='$EmpID'"; 
 $StmtEmpLeaveDetails=$iconn->query($SqlEmpLeaveDetails);
 
 $SqlOptions="Select * from AdminOptions where DeptName='HRM' ORDER BY SortOrder ASC"; 
 $StmtOptions=$iconn->query($SqlOptions);

if(isset($_POST['submit'])){
	
	
	$StartDate=$StartDate;
	$EndDate=$EndDate;
	
 	$SqlDateCheck="SELECT * FROM HrmDailyAttendnessLog WHERE BioMetricID='$BioMetricID'
	AND Date BETWEEN '$StartDate' AND '$EndDate'";
	$StmtDateCheck=$iconn->query($SqlDateCheck);
    $DateCount=mysqli_num_rows($StmtDateCheck);

	$SqlOnDutyEmployeeCount="Select * from HrmOnDutyRegister where EmpID='$EmpID' 
	and  StartDate between '$StartDate' and '$EndDate' and EndDate between '$StartDate' and '$EndDate'"; 
    $StmtOnDutyEmployeeCount=$iconn->query($SqlOnDutyEmployeeCount);
    $ODCount=mysqli_num_rows($StmtOnDutyEmployeeCount);
	
	$SqlHolidayCheck="SELECT * FROM HrmHolidays WHERE Date BETWEEN '$StartDate' and '$EndDate' ";
	$StmtHolidayCheck=$iconn->query($SqlHolidayCheck);
	$HolidayCount=mysqli_num_rows($StmtHolidayCheck);
//ECHO $SqlHolidayCheck;

	$SqlEmpLeaveCount="Select * from HrmLeaveRegister where EmpID='$EmpID' 
	and  StartDate between '$StartDate' and '$EndDate' and EndDate between '$StartDate' and '$EndDate'"; 
    $StmtEmpLeaveCount=$iconn->query($SqlEmpLeaveCount);
    $LeaveCount=mysqli_num_rows($StmtEmpLeaveCount);

		if($DateCount==0  && $ODCount==0 && $LeaveCount==0 && $HolidayCount==0){
			
		$start_date = strtotime($StartDate);
	    $end_date = strtotime($EndDate);
 	    $DaysTotal= ($end_date - $start_date)/60/60/24;
        echo $DaysTotal= $DaysTotal+1;
			
  $SqlInsertLeave="INSERT INTO `HrmLeaveRegister`( `EmpID`, `LeaveType`, `Reason`, `StartDate`, 
 `EndDate`, `DaysTotal`, `ApprovedBy`) VALUES
 ('$EmpID','$LeaveType','$Reason','$StartDate','$EndDate','$DaysTotal','$ApprovedBy')"; 

   if($StmtInsertLeave=$iconn->query($SqlInsertLeave)){
	legacyAuditLogInsertById('HrmLeaveRegister', 'LeaveID', $iconn->insert_id, $_POST, array(), 'Hrm');
	   	while (strtotime($StartDate) <= strtotime($EndDate)) {
		//BioMetricID
		
   // echo $StartDate;
	$TimeIn='00:00:00';
	$TimeOut='00:00:00';
	$BreakTimeIn='00:00:00';
	$BreakTimeOut='00:00:00';
	$IsManual='1';
	$Shift='0';
	$OnTime='0';
    
	 $SqlLeaveAddition="INSERT INTO `HrmDailyAttendnessLog` ( `BioMetricID`, `Date`, `TimeIn`, `Status`, `TimeOut`, `OnTime`, `Shift`, `BreakTimeIn`, `BreakTimeOut`, `IsManual`) VALUES ('$BioMetricID','$StartDate','$TimeIn','4','$TimeOut','$OnTime','$Shift','$BreakTimeIn','$BreakTimeOut','$IsManual')";
	$StmtLeaveAddition=$iconn->query($SqlLeaveAddition);
	if($StmtLeaveAddition){
	legacyAuditLogInsertById('HrmDailyAttendnessLog', 'AttID', $iconn->insert_id, $_POST, array(), 'Hrm');
	}
	$StartDate = date ("Y-m-d", strtotime("+1 days", strtotime($StartDate)));


         }

	
	   
	   
	   
	   
	   
	   
	$_SESSION['Status'] = 'OnDuty  Added successfully';
	header("Location:EmpLeaveCreate.php?EmpID=".$EmpID);
	}
	
	else{
	$_SESSION['Status'] = $iconn->error ."Unable To Add OnDuty";
	header("Location:EmpLeaveCreate.php?EmpID=".$EmpID);	
	}
		}		
	else{
		 echo '<script>alert("Leave Already Exists / Employee present on the given dates/Public Holiday")</script>'; 

		
	}	
}	  
	  


?>




<!DOCTYPE html>


<html lang="en">
<link href="https://serp.silverlights.in/Clients/Jlsf/User.css"  rel="stylesheet" type="text/css" />

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
					id="Reason" name="Reason"
					placeholder="Enter Reason" value="" required>                 
                 </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Leave Type</label>
                   <select required type="text" class="form-control select2" name='LeaveType' id="LeaveType">

				    <?php while ($RowOptions=$StmtOptions->fetch_array()){ ?>
				     <option 
					            
					 value="<?php echo $RowOptions['Value'] ?>">
					 <?php echo $RowOptions['OptionName'] ?></option>
				   
				 <?php  }?>
                 </select>
               
                </div>				

				<div class="form-group  col-lg-3">
				<label for="exampleInputEmail1">Start Date</label>
                    <input class="form-control" type="date"
					placeholder="" value="<?php echo date('Y-m-d')?>" 
					id="StartDate" name="StartDate" required>                 
                  </div>
				  
				  <div class="form-group  col-lg-3">
				<label for="exampleInputEmail1">End Date</label>
                    <input class="form-control" type="date"
					placeholder="" value="<?php echo date('Y-m-d')?>" 
					id="EndDate" name="EndDate" required>                 
                  </div>
				
				
			  
        <!-- end row -->
					</div>
			<div class="col-xs-12 box-footer"  align="right">
			<a target="" href="EmployeeLeaves.php"   class="btn btn-danger  "> Close</a>
                <button  type="submit"  name='submit'  class="btn btn-success waves-effect waves-light">
				Submit</button> 
              </div>
			  
			  </form>    </div>
			      </div>
			  
			     <div class="row">
                            <div class="col-12">
                                <div class="card">
								
							
							
                                    <div class="card-body" id="ATT_DATA">
			  <table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">

      <tr>
	  
	  <th>S.No</th>
	  <th>Reason</th>
	  <th>Leave Type</th>
	  <th>Start Date</th>
	  <th>End Date</th>
	  <th>Tools</th>
	  
	  	
      </tr>

    </thead>

    <tbody>
	<?php
$SNo=1;
   while($RowEmpLeaveDetails=$StmtEmpLeaveDetails->fetch_array()){
	   
	   $LeaveType=$RowEmpLeaveDetails['LeaveType'];
	   IF($LeaveType==0){
		   
		   $Leave='CL';
	   }elseif($LeaveType==1){
		   
		   $Leave='SL';
	   }elseif($LeaveType==2){
		   
		   $Leave='Others';
	   }
 
	?>
	
	<tr>
			
	<td id="<?php  echo $RowEmpLeaveDetails['EmpID'];?>" > <?php echo $SNo; ?></td>
	<td id="<?php  echo $RowEmpLeaveDetails['EmpID'];?>" > <?php echo $RowEmpLeaveDetails['Reason']; ?></td>
	<td id="<?php  echo $RowEmpLeaveDetails['EmpID'];?>" > <?php echo $Leave; ?></td>

	<td id="<?php  echo $RowEmpLeaveDetails['EmpID'];?>" > <?php echo date('d-m-Y',strtotime($RowEmpLeaveDetails['StartDate']));?></td>
	<td id="<?php  echo $RowEmpLeaveDetails['EmpID'];?>" > <?php echo date('d-m-Y',strtotime($RowEmpLeaveDetails['EndDate'])); ?></td>
	<td>
	<button class="btn btn-link text-danger Delete" data-id="<?php echo $RowEmpLeaveDetails['LeaveID']; ?>" 
			
							data-toggle="tooltip" data-placement="top" title="" value=""
							data-original-title="Delete"><i class="mdi mdi-trash-can font-size-18"></i></button> 						
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
 
function showData(data){
	
	$("#ATT_DATA").html(data);
			
					//$("#EmpList").html(data);
 
 		var table = $('#key-datatable').DataTable( {
        scrollY: "600px",
         scrollX:true,
          //sScrollX: "100%",
		sScrollXInner: "150%", 
        // scrollCollapse:true,
        paging:true,
        destroy: true,
        fixedColumns: true,
		 bSort : true,
		  "bStateSave": true,
		   "fnStateSave": function (oSettings, oData) {
            localStorage.setItem('key-datatable', JSON.stringify(oData));
        },
        "fnStateLoad": function (oSettings) {
            return JSON.parse(localStorage.getItem('key-datatable'));
        }
  } );
		}

</script>
