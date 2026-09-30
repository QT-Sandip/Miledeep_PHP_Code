<?php

$PageTitle="Buyers List";
$PageTitleFolder="ColdStore";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

  $SqlConsignee = "SELECT * FROM `ScrmConsigneeDetails` ";
  $StmtConsignee = $iconn->query($SqlConsignee);

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
									
										
			
     <table id="datatable-buttons" class="table table-striped 
										table-bordered dt-responsive nowrap" >                        
			  <thead>
			  <tr>
                
                 
         <th> S.No</th>
	     <th> Buyer name</th>
         <th> Buyer Address</th>
         <th> Port Of Discharge</th>
         <th> Country</th>
         <th> Brand</th>
         <th> Tools</th>

				</tr>
				</thead>
                <tbody>
                  <?php
				  
				  $SNo=1;
                   
        while($RowConsignee= $StmtConsignee->fetch_assoc()){?>
                       
					   <tr>
                       
		<td><?php echo $SNo;?></td>
		<td id="a_type<?php  echo $RowConsignee['ConID'];?>">
		<?php echo $RowConsignee['ConsigneeName']; ?></td>
		<td ><?php echo $RowConsignee['ConsigneeAddress']; ?></td>
		<td ><?php echo $RowConsignee['ConsigneePort']; ?></td>
		<td ><?php echo $RowConsignee['ConsigneeCountry']; ?></td>
		<td ><?php echo $RowConsignee['ConsigneeBrand']; ?></td>
		<td >
		 <a target="_blank" href="CreateBuyer.php?ID=<?PHP ECHO $RowConsignee['ConID'];?>" 
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
	  showData();

	
	
    
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
