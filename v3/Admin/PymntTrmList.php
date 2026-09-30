<?php

$PageTitle="Payment Terms";
$PageTitleFolder="SalesNew";

$AccessType=$_SESSION['AccessType']; 


include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$SqlPayment="select * from `$TblScrmPaymentTerms` ";
$StmtPayment=$iconn->query($SqlPayment);



?>
<!DOCTYPE html>

<html lang="en">

    <head>
        
                <?php include ($IncludesPath."title-meta.php"); ?>
		 		<?php include ($IncludesPath."DbLinks.php");?>
                <?php include ($IncludesPath."head-css.php");?>

    </head>

    <body data-sidebar="dark">
			<?php include ($IncludesPath."PreLoader.php"); ?>

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
                                    <div class="card-body" id="ClientList">
									
							<div class="col-lg-12 box-footer"  align="center">
			  	
							 <a href="AddPymntTrms.php" class="btn btn-primary waves-effect waves-light">
				<i class="fa fa-plus"></i>Add</a>
					</div>			
			
<table id="key-datatable" 
										class="table dt-responsive nowrap w-100">
					  <thead>
			  
			  <tr>
        <th>ID</th>
		<th>Payment Name</th>
        <th>Payment Mode</th>
		<th>Actions</th>

                  
               
				</tr>
				</thead>
                <tbody>
                  <?php
				  
				  $SNo=1;
                   
                    while($RowPayment = $StmtPayment->fetch_assoc()){?>
                       
					   <tr>
                       
		<td> <?php echo $RowPayment['ID']; ?></td>
        <td ><?php echo  mb_strimwidth($RowPayment['PaymentName'], 0, 30, "..."); ?> </td>	  
		<td><?php echo $RowPayment['PaymentMode']; ?></td>

						  <td>
						  
						  
                         
						 <a target="_blank" href="AddPymntTrms.php?ID=<?php  echo $RowPayment['ID'];?>" 
						 class="mr-3 text-primary Edit" data-toggle="tooltip" data-placement="top"
						 title="" data-original-title="Edit"><i class="mdi mdi-pencil font-size-18"></i></a>
 </td>
                      
                        </tr>
                      <?php
                $SNo++;    }
                  ?>
                </tbody>
              </table>
            	
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
		<?php include ($IncludesPath."NewScripts.php");?>
      

       
    </body>
</html>				

<script>
$(document).ready(function(){
	  // showData();

	
    
	});


function showData(data){
			
					$("#ClientList").html(data);
 
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
