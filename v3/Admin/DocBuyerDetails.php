<?php

$PageTitle="Buyers List";
$PageTitleFolder="SalesNew";
$BranchID=$_SESSION['BranchID']; 
$AccessType=$_SESSION['AccessType']; 


include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$SqlBuyer="select * from `$TblScrmConsigneeDetails` ";
	


$StmtBuyer=$iconn->query($SqlBuyer);



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
			  	
							 <a href="AddBuyer.php" class="btn btn-primary waves-effect waves-light">
				<i class="fa fa-plus"></i>New Buyer</a> 
				
					</div>				
			
<table id="datatable-buttons" 
										class="table table-striped 
										table-bordered dt-responsive nowrap w-100" >                        
			  <thead>
			  <tr>
                
                 
                  <th>ID</th>

        <th>Buyer name</th>

        <th>Buyer Address</th>

		<th>Country</th>

        <th>Buyer Email Address</th>

        <th style="width: 10%">Actions</th>
                  
               
				</tr>
				</thead>
                <tbody>
                  <?php
				  
				  $SNo=1;
                   
                    while($RowBuyer = $StmtBuyer->fetch_assoc()){
               $ConsigneeAddress=mb_strimwidth($RowBuyer['ConsigneeAddress'], 0, 25, "...");
	           $ConsigneeName=mb_strimwidth($RowBuyer['ConsigneeName'], 0, 20, "...");

						?>
                       
					   <tr>
                       
						  <td> <?php echo $RowBuyer['ConID']; ?></td>
        <td><?php echo $ConsigneeName; ?> </td>
	  
		<td><?php echo $ConsigneeAddress; ?></td>

		<td><?php echo $RowBuyer['ConsigneeCountry']; ?></td>
		
		<td><?php echo $RowBuyer['ConsigneeEmailAddress'];?></td>
						  <td>
						  
						  
                         
						 <a target="" href="AddBuyer.php?ID=<?php  echo $RowBuyer['ConID'];?>" 
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
