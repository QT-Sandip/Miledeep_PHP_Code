<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$PageTitle = "Holidays";
$PageTitleFolder = "HRM";
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
 $SqlHolidays = "SELECT * FROM `HrmHolidays` ";
$StmtHoliday = $iconn->query($SqlHolidays);
$PageTitle="Holidays";
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
			
<!--<h2 align="center">Add Holiday</h2>-->

      
       <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body" id="Holidays">
                                    <div class="col-lg-12 box-footer" align="center">

<a href="CreateHolidays.php" class="btn btn-success waves-effect waves-light">Add</a>

</div>
										
		
<table id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100">

              <thead class="thead-light">
                  <!--<th class="hidden"></th>-->
                  <th>Id</th>
				  <th> Name</th>			 
                  <th>Date</th>
				  <th>Actions</th>
                  
                </thead>
                <tbody>
                  <?php
				  
				  
                    
                    while($RowHoliday = $StmtHoliday->fetch_assoc()){
						$HolidayDate = strtotime($RowHoliday['Date']);
                       $StmtDate = date("d-m-Y", $HolidayDate);?>

                    <!--  $status = ($RowHoliday['status'])<span class="label label-warning pull-right">ontime</span>':'<span class="label label-danger pull-right">late</span>
                     -->
                     <tr>
                          <!--<td class='hidden'></td>-->
						  <td><?php echo $RowHoliday['ID']?></td>
						  <td><?php echo$RowHoliday['Name']?></td>
						  <td><?php echo$StmtDate?></td>
						 
                          <td>
						 
<a target="_blank" href="CreateHolidays.php?ID=<?php  echo $RowHoliday['ID'];?>" 
						 class="mr-3 text-primary Edit" data-toggle="tooltip" data-placement="top"
						 title="" data-original-title="Edit"><i class="mdi mdi-pencil font-size-18"></i></a>
                        
	<button class="btn btn-link text-danger Delete" data-id="<?php echo $RowHoliday['ID']; ?>" 
			
							data-toggle="tooltip" data-placement="top" title="" value=""
							data-original-title="Delete"><i class="mdi mdi-trash-can font-size-18"></i></button> 						
								 
						  
						  
                          </td>
                        </tr>
                      
                   <?php }
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

  
  $(document).on('click', '.Delete', function(e){
    e.preventDefault();
	//console.log('check');

		var Id = $(this).data('id');
		var Type = "DeleteHoliday";
	
		
	 $.ajax({
    type: 'POST',
    url: 'Ajax/AjDeptDetails.php',
	dataType: 'json',
    data: {'Id':Id,'Type':Type},

    success: function(response){
		  $('#deleteholiday').modal('show');
		 var obj = JSON.parse(JSON.stringify(response));
		 		 
				 $('#del_attid').val(Id);

		
		 if(obj.error == null){
			
		 }else{
		 }
			   

     }
  });

  });
  
  
  $(document).on('click', '#Deleteholiday', function(e){
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
  
  
 
  
  
  
  
  
});

/*function showData(data){
			
					//$("#EmpList").html(data);
 
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
		}*/

        function showData(data) {

$("#Holidays").html(data);
var table = $('#key-datatable').DataTable({
  scrollY: "600px",
  scrollX: true,
  //sScrollX: "100%",
  sScrollXInner: "150%",
  // scrollCollapse:true,
  paging: true,
  destroy: true,
  fixedColumns: true,
  bSort: true,
  "bStateSave": true,
  "fnStateSave": function(oSettings, oData) {
    localStorage.setItem('key-datatable', JSON.stringify(oData));
  },
  "fnStateLoad": function(oSettings) {
    return JSON.parse(localStorage.getItem('key-datatable'));
  }
});
}

</script>

