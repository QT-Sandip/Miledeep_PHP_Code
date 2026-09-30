<?php

$PageTitle="Final Reports".$row_inv['PO_NUM'];
$PageTitleFolder="Accounts";


include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

 $LotID=$_GET['LotID'];
   $AccessType=$_SESSION["AccessType"]; 


$SqlOrgnDetails="SELECT *  FROM `$QcRmOrgnDetails`  where LotID='$LotID'";
$StmtOrgnDetails=$iconn->query($SqlOrgnDetails);

  $NumRows=$StmtOrgnDetails->num_rows;
$RowOrgnDetails=$StmtOrgnDetails->fetch_array();
		$orgn_status=$RowOrgnDetails['PostedUserID'];


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
									
										
			
<table id="datatable-buttons" 
										class="table table-striped 
										table-bordered dt-responsive nowrap" >                        
			  <thead>
			  <tr>
                
                 
         <th >   Available Reports   </th>
		<th>   Actions </th>
				</tr>
				</thead>
                <tbody>
                 <?php if($orgn_status !=0){  ?>
		
	
	
	<tr>
	<td  >Internal Quality Report</td>
		<td > 	  	
	 	
		<a target="_blank" href="/Accounts/Reports/RmaPrint.php?LotID=<?php  echo $LotID ?>" 
			class="text-success" data-toggle="tooltip" data-placement="top" title="" data-original-title="Print"><i class="mdi mdi-printer font-size-18"></i></a>
   
	  </td>		
     </tr>
	 
	<?php } ?>
	 
	 
	 <tr>
		<td > Raw Material Purchase Report </td>
		<td > 
  
<?php if($NumRows !=0){?>	  	
	
	<a target="_blank" href="/Accounts/Reports/RmBill.php?LotID=<?php  echo $LotID ?>" 
			class="text-success" data-toggle="tooltip" data-placement="top" title="" data-original-title="Print"><i class="mdi mdi-printer font-size-18"></i></a>
          
<?php  } else { ?>	<b><p> Report Not Available  </p></b>  <?php } ?>
		</td>		      
     </tr>
	
	
	 <tr>
		<td  >Head Less To Grading Report</td>
		<td > 	  	
	 	
	<a target="_blank" href="/Accounts/Reports/DocWeightSlipPrint.php?LotID=<?php  echo $LotID ?>" 
			class="text-success" data-toggle="tooltip" data-placement="top" title="" data-original-title="Print"><i class="mdi mdi-printer font-size-18"></i></a>
   
	</td>		  
     </tr> 
<!-- ROBO -->
<?php if($AccessType < 4  ){

 ?>
<tr>
		<td >Costing Report</td>
		<td > 	  	
	 	
		<a target="_blank" href="/Accounts/Reports/CostingReportPrint.php?LotID=<?php  echo $LotID ?>" 
			class="text-success" data-toggle="tooltip" data-placement="top" title="" data-original-title="Print"><i class="mdi mdi-printer font-size-18"></i></a>
   
		</td>		
     </tr>
	 
<?php } ?>     </tbody>
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
