<?php
session_start();


$PageTitleFolder="HRM";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$ID=$_GET['ID'];

if($ID==''){
	$PageTitle="New Holiday";
}
else{
$PageTitle="Edit Holiday";
	
}

 $SqlHolidays = "SELECT * from HrmHolidays WHERE ID = '$ID'";
		$StmtHolidays = $iconn->query($SqlHolidays);
		  $Count=mysqli_num_rows($StmtHolidays); 

		$RowHolidays = $StmtHolidays->fetch_assoc();
 ?>



<!DOCTYPE html>

<html lang="en">

    <head>
        
                <?php include ($IncludesPath."title-meta.php"); ?>
		 		<?php  include ("Includes/DbLinks.php");?>
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

                        <?php include ($IncludesPath."page-title.php");?>
			

      
       <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
									
										<div class="row">
			
                <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Holiday Name</label>
                  <input required type="text" class="form-control" name='HolidayName' id="HolidayName"
				  placeholder="Enter Holiday Name" value="<?php echo $RowHolidays['Name']; ?>">
                </div>
			
				<div class="form-group col-lg-3">
				<label for="exampleInputEmail1">Date</label>
                <input required type="date" class="form-control" name='HolidayDate' id="HolidayDate"
				  placeholder="" value="<?php echo $RowHolidays['Date'];  ?>">
                </div>
                </div>
				
				
				
				
			  <div class="col-xs-12 box-footer"  align="right">
                <a target="" href="Holidays.php"   class="btn btn-danger  "> Close</a>
                <!-- <a target="" href="YearlyHolidaysList.php"   class="btn btn-danger  "> Close</a> -->
<?php if($Count==0){ ?>			   <button  id="AddHoliday"   class="btn btn-success waves-effect waves-light">
				
				Submit</button>
				
				<?php } else{ ?>
			                <button  id="UpdateHoliday"   class="btn btn-success waves-effect waves-light">
							Update</button>

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


$(document).ready(function(){
	
$(document).on('click', '#AddHoliday', function(e){
    e.preventDefault();
	var HolidayName = $('#HolidayName').val();
	var HolidayDate = $('#HolidayDate').val();
	var Type = "AddHoliday";
	
 $('#addnewholiday').modal('hide');
$.ajax({
    type: 'POST',
    url: 'Ajax/AjDeptDetails.php',
	    data: {'Type':Type,'HolidayName':HolidayName,'HolidayDate':HolidayDate},

	dataType: 'json',

    success: function(response){
		 var obj = JSON.parse(JSON.stringify(response));
		 console.log(obj);
		 if(obj.error == null){
			 alert(obj.Msg);
			 //location.reload();
			 			window.location.href = 'YearlyHolidaysList.php';

		 }else{
			 alert(obj.error);
		 }
    
    }
  });


  });
  
  
  $(document).on('click', '#UpdateHoliday', function(e){
    e.preventDefault();
	//console.log('check');

		//var id = $(this).data('ID');
				        var Id ='<?php echo $ID ?>';

		var Type = "UpdateHoliday";
		var HolidayName = $('#HolidayName').val();
		var HolidayDate = $('#HolidayDate').val();
		//var id = $('#hol_id').val();
	
	 $.ajax({
    type: 'POST',
    url: 'Ajax/AjDeptDetails.php',
	data: {'Id':Id,'Type':Type,'HolidayName':HolidayName,'HolidayDate':HolidayDate},
	dataType: 'json',
    

    success: function(response){
		 // $('#editholiday').modal('show');
		 var obj = JSON.parse(JSON.stringify(response));
		 
		 $('#HolidayName').val(response.HolidayName);
		 $('#HolidayDate').val(response.HolidayDate);
      $('#id').val(Id);
		 console.log(obj);
		 if(obj.error == null){
			 alert(obj.Msg);
			//location.reload();
			window.location.href = 'Holidays.php';
		 }else{
			 alert(obj.error);
		 }
			   

     }
  });

  });
  
  
  
  
    });    

</script>





</html>

