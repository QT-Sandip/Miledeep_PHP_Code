<?php

$Status=$_SESSION['Status']; 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


   $UserType=$_GET['Type'];
//$uid=$_SESSION['r_id'];



if($UserType==1){
	$User="Drivers";
}else if($UserType==2){
	$User="Graders";
}else if($UserType==3){
	$User="Agents";
}else if($UserType==4){
	$User="Supervisors";
}else {
$User="Unknown";
}



$PageTitle=$User ." List";

$PageTitleFolder="Purchase";

$SqlRmUsersList="SELECT * FROM `RmUsersList` WHERE UserType='$UserType'";

$StmtRmUsersList=$iconn->query($SqlRmUsersList);

 ?>

<!DOCTYPE html>

<html lang="en">
<link href="https://serp.silverlights.in/Clients/JME/User.css"  rel="stylesheet" type="text/css" />

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

	
          
	
<div class="row">
                            <div class="col-12">
                                <div class="card">
								                                    <div class="card-body">



				<div align="text-left" class="col-lg-12">
									 <a href="CreateDetails.php?Type=<?php echo$UserType; ?>" class="btn btn-success waves-effect waves-light">
				<i class="fa fa-plus"></i> New 	<?php echo $User; ?></a>	
			</div>		
  <table  id="key-datatable" data-paging="true" class="table dt-responsive nowrap w-100" >
		
<thead class="thead-light">
      

      <tr>
	  <th>S.No</th> 
      <th>Name</th> 
	  
	  </tr>

    </thead>
<tbody>



	<?php
	
	$SNo=1;
 while($RowRmUsersList=$StmtRmUsersList->fetch_array()) { ?>


		<td id="a_type<?php  echo $RowRmUsersList['ID'];?>"><?php echo $SNo; ?></td>
		<td id="a_type<?php  echo $RowRmUsersList['ID'];?>"><?php echo $RowRmUsersList['UserName']; ?></td>


        </tr>

	  <?php
    $SNo++;
   }

	  ?>
</tbody>

  </table>
  </div> <!-- end row -->
    
	</div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->
                        </div>
                        <!-- end row-->
                        
                    </div> 
					<?php include ($IncludesPath."footer.php");?>
                
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        

		<?php include ($IncludesPath."vendor-scripts.php");?>
		<?php include ($IncludesPath."NewScripts.php");?>
		
  
       
    </body>
</html>				


<script>
$(document ).ready(function() {
   showData();
});
 /* $(function () {
    $('#example1').DataTable()
    $('#example2').DataTable({
      'paging'      : true,
      'lengthChange': false,
      'searching'   : false,
      'ordering'    : true,
      'info'        : true,
      'autoWidth'   : false
    })
  })*/
  
  function showData(data){
			
					$("#EmpList").html(data);
 
 		var table = $('#key-datatable').DataTable( {
        scrollY: "500px",
         scrollX:true,
          //sScrollX: "100%",
		sScrollXInner: "150%", 
        // scrollCollapse:true,
        paging:true,
        destroy: true,
        fixedColumns: true,
		 bSort : true,
         language: {
                lengthMenu: "Show _MENU_ records",
                paginate: {
                    previous: "<i class='mdi mdi-chevron-left'>",
                    next: "<i class='mdi mdi-chevron-right'>"
                }
            },
            drawCallback: function () {
                $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
            },
            dom: '<"row mb-2"<"col-md-6 d-flex align-items-center"lf><"col-md-6 d-flex justify-content-end"B>>rtip',
            buttons:[],
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

</html>
