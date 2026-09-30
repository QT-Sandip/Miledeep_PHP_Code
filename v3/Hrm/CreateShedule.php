<?php
session_start();
$PageTitle="Schedule";
$PageTitleFolder="HRM";
$PageTitleLink="home";


include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$ID=$_GET['ID'];


		$SqlShift = "SELECT * from HrmShiftDetails WHERE ShiftID = '$ID'";
		$StmtShift = $iconn->query($SqlShift);
		$count=mysqli_num_rows($StmtShift); 
		$RowShift = $StmtShift->fetch_assoc();

 ?>



<!DOCTYPE html>

<html lang="en">

    <head>
        
         <?php include ($IncludesPath."title-meta.php"); ?>
		<?php  include ("Includes/DbLinks.php"); ?>
		<?php include ($IncludesPath."head-css.php"); ?>

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

                        <?php include ($IncludesPath."page-title.php");?>
			

      
       <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
									
										<div class="row">
                <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Shift Name</label>
                  <input required type="text" class="form-control" name='ShiftName' id="ShiftName"
				  placeholder="Enter Shift Name" value="<?php echo $RowShift['ShiftName'];?>">
                </div>
			
				<div class="form-group col-lg-3">
				<label for="exampleInputEmail1">Time In</label>
                  <input required type="time" class="form-control" name='TimeIn' id="TimeIn"
				  placeholder="" value="<?php echo $RowShift['StartTime'];?>">  
                </div>
				
				<div class="form-group col-lg-3">
				<label for="exampleInputEmail1">Time Out</label>
                  <input required type="time" class="form-control" name='TimeOut' id="TimeOut"
				  placeholder="" value="<?php echo $RowShift['EndTime'];?>">  
                </div>
				
				<div class="form-group col-lg-3">
				<label for="exampleInputEmail1">Break Time Start</label>
                  <input required type="time" class="form-control" name='BreakStartTime' id="BreakStartTime"
				  placeholder="" value="<?php echo $RowShift['BreakStartTime'];?>">  
                </div>
				
				<div class="form-group col-lg-3">
				<label for="exampleInputEmail1">Break Time End</label>
                  <input required type="time" class="form-control" name='BreakEndTime' id="BreakEndTime"
				  placeholder="" value="<?php echo $RowShift['BreakEndTime'];?>">  
                </div>
                </div>
				
				
				
				
				
			  <div class="col-xs-12 box-footer"  align="right">
			   <a target="" href="Schedule.php"   class="btn btn-danger  "> Close</a>
			  <?php if($count==0){ ?>
                <button  id="AddSchedule"   class="btn btn-success waves-effect waves-light">
				Submit</button>
           
           
			<?php } else{?>
			
			
				
			  
                <button  id="UpdateSchedule"   class="btn btn-success waves-effect waves-light">Update</button>
           
           
			<?php } ?>
				
				   </div>
			  
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
<Script>

$('document').ready(function(){

$(document).on('click', '#AddSchedule', function(e){
    e.preventDefault();
	var ShiftName = $('#ShiftName').val();
	 var TimeIn = $('#TimeIn').val();
	 var TimeOut = $('#TimeOut').val();
	 var TimeOut = $('#TimeOut').val();
	 var BreakEndTime = $('#BreakEndTime').val();
	 var BreakStartTime = $('#BreakStartTime').val();
	var Type = "AddShift";
	
if(ShiftName==''){
	alert("Enter Shift Name");
	return;
}



  $.ajax({ 
    type: 'POST',
    url: 'Ajax/AjDeptDetails.php',
	dataType: 'json',
    data: {'ShiftName':ShiftName,'TimeIn':TimeIn,'TimeOut':TimeOut,'BreakEndTime':BreakEndTime,'BreakStartTime':BreakStartTime,'Type':Type},

    success: function(response){
		 var obj = JSON.parse(JSON.stringify(response));
		 console.log(obj);
		 if(obj.error == null){
			 alert(obj.Msg);
			 //location.reload();
			 			 			window.location.href = 'Schedule.php';

		 }else{
			 alert(obj.error);
		 }
			   
			   
	
    
    }
  });


  });	


$(document).on('click', '#UpdateSchedule', function(e){
    e.preventDefault();
	//console.log('check');

		//var id = $(this).data('ID');
				        var Id ='<?php echo $ID ?>';

		var Type = "UpdateSchedule";
		var ShiftName = $('#ShiftName').val();
		var TimeIn = $('#TimeIn').val();
		var TimeOut = $('#TimeOut').val();
			 var BreakEndTime = $('#BreakEndTime').val();
	 var BreakStartTime = $('#BreakStartTime').val();
	
	 $.ajax({
    type: 'POST',
    url: 'Ajax/AjDeptDetails.php',
    data: {'Id':Id,'ShiftName':ShiftName,'TimeIn':TimeIn,'TimeOut':TimeOut,'BreakEndTime':BreakEndTime,'BreakStartTime':BreakStartTime,'Type':Type},
	dataType: 'json',
    

    success: function(response){
		 // $('#editholiday').modal('show');
		 var obj = JSON.parse(JSON.stringify(response));
		 
		 $('#ShiftName').val(response.ShiftName);
		 $('#TimeIn').val(response.TimeIn);
		 $('#TimeOut').val(response.TimeOut);
      $('#id').val(Id);
		 console.log(obj);
		 if(obj.error == null){
			 alert(obj.Msg);
			//location.reload();
			window.location.href = 'Schedule.php';
		 }else{
			 alert(obj.error);
		 }
			   

     }
  });

  });
  
  



    });    




</script>





</html>

