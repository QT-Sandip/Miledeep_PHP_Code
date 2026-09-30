<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
$SqlLoginDetails = "SELECT * FROM `AdminLoginDetails` ";
$StmtLoginDetails = $iconn->query($SqlLoginDetails);
$PageTitle="Users";
$PageTitleFolder="Hrm";

?>


<!DOCTYPE html>

<html lang="en">

    <head>
        
                <?php include ($IncludesPath."title-meta.php"); ?>
		 		<?php  include ("Includes/DbLinks.php");?>
                <?php include ($IncludesPath."head-css.php");?>

    </head>

    <body data-sidebar="dark">
<?php		include ($IncludesPath."PreLoader.php"); ?>

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
			
<!--<h2 align="center">Add Holiday</h2>-->

      
       <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body" id="Users">
									
			
                                       <table id="datatable-buttons" 	class="table table-striped table-bordered dt-responsive nowrap" >
                <thead>
                  <th>S.No</th>
				  <th> User Name</th>			 
                  <th>User Login ID</th>
				  <th>Actions</th>
                  
                </thead>
                <tbody>
                  <?php
				  
				  $SNo=1;
                    
                    while($RowLoginDetails = $StmtLoginDetails->fetch_assoc()){
						
						//echo $Status=$RowLoginDetails['Status']; 
?>

                    
                     <tr>
						  <td><?php echo $SNo;?></td>
						  <td><?php echo $RowLoginDetails['UserName']?></td>
						  <td><?php echo$RowLoginDetails['UserLoginID']?></td>
						 
                          <td>
						 
<div class="button-items">
						<?php if($RowLoginDetails['Status']=='1'){ ?>
						
						
             

						
					   <button class="btn btn-link badge badge-success font-size-12 UserActive"
					   type="button"
					 data-toggle="popover" data-trigger="focus"  
					 
								data-id="<?php echo $RowLoginDetails['UserID']; ?>"  id="act<?php echo $RowLoginDetails['UserID']; ?>"
								value="<?php echo $RowLoginDetails['Status']; ?>">Active</button>
                          
					<?php } else { ?>
					
											

					 <button  class="btn btn-link badge badge-warning font-size-12 UserActive" 
					 data-toggle="popover" data-trigger="focus"  
					 
					 data-id="<?php echo $RowLoginDetails['UserID']; ?>" id="inact<?php echo $RowLoginDetails['UserID']; ?>" 
							 value="<?php echo $RowLoginDetails['Status']; ?>">
							 In Active
							 </button>
                          
						<?php } ?>
						</div>			  
                          </td>
                        </tr>
                      
                   <?php  $SNo++;
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
                		<?php include 'Includes/add_holiday_modal.php'; ?>

            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        

		<?php include ($IncludesPath."vendor-scripts.php");?>
		<?php include ("Includes/NewScripts.php");?>
      

       
    </body>
</html><script>

$(document).ready(function(){
	
	  showData();
});
  
  $(document).on('click', '.UserActive', function(e){
e.preventDefault();
	//alert('hiiiii');
   
     var UserId =$(this).data('id');
	 var UserStatus = $(this).val();
	 var Type = 'UserStatus';
	 
	
	$.ajax({
	
	type: 'POST',
    url: 'Ajax/AjDeptDetails.php',
    dataType: 'json',
	data: {'UserId': UserId,'UserStatus':UserStatus,'Type':Type},
    success: function(data){
		 showData(data);
	 			//location.reload();
				
				var obj = JSON.parse(JSON.stringify(data));
		 
		 
    
		 if(obj.error == null){
			// alert("Status Updated Successfully");
			 alert(obj.Msg);
			location.reload();
		 }else{
			//alert("Failed to Update Please Contact Admin");
			 alert(obj.Msg);
			 location.reload();

			
		 }

     }
  });

    //getRow(id,type);
  });
  

  
  /*$(document).on('click', '#Deleteholiday', function(e){
	  	  showData();

    e.preventDefault();
	//console.log('check');

		var Id = $(this).data('id');
		var Type = "DeleteHday";
		
		var Id = $('#del_attid').val();
	
	 $.ajax({
    type: 'POST',
    url: 'Ajax/AjDeptDetails.php',
	data: {'Id':Id,'Type':Type},
	dataType: 'json',
    

    success: function(response){
		  $('#deleteholiday').modal('show');
		 var obj = JSON.parse(JSON.stringify(response));
		 
		 
      $('#id').val(Id);
		 console.log(obj);
		 if(obj.error == null){
			 alert(obj.Msg);
			location.reload();
		 }else{
			 alert(obj.error);
		 }
			   

     }
  });

  });
  
  */
 
  
  
  
  
  


function showData(data){
			
					$("#Users").html(data);
 
 		var Stable=    $('#scroll-vertical-datatable').DataTable({
        "scrollY":        "450px",
        "scrollCollapse": true,
        "paging":         false,
        "language": {
            "paginate": {
                "previous": "<i class='mdi mdi-chevron-left'>",
                "next": "<i class='mdi mdi-chevron-right'>"
            }
        },
        "drawCallback": function () {
            $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
        },
		buttons: ['copy', 'excel', 'pdf', 'colvis']
    });
	
	Stable.buttons().container()
        .appendTo('#scroll-vertical-datatable_wrapper .col-md-6:eq(0)');
		}

</script>

