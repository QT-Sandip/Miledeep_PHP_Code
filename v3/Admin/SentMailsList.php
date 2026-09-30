<?php

$PageTitle="Mails";
$PageTitleFolder="SalesNew";
$AccessType=$_SESSION['AccessType']; 


include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$SqlMailsLog="select * from `ScrmMailsLog` ORDER BY MailID desc"; 
$StmtMailsLog=$iconn->query($SqlMailsLog);


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
			  	
							 <a href="CreateMail.php" class="btn btn-primary waves-effect waves-light">
				<i class="fa fa-plus"></i> Add</a> 
				
					</div>		
										
			
<table id="datatable-buttons" 
										class="table table-striped 
										table-bordered dt-responsive nowrap w-100" >                        
			  <thead>
			  <tr>
                
                 
                 <th>ID</th>
		<th>From Mail</th>
        <th>To Mail</th>
        <th>CC Mail</th>
        <th>Subject</th>
        <th>Message</th>
        <th>Sent Date</th>
               
				</tr>
				</thead>
                <tbody>
                  <?php
				  
				  $SNo=1;
                   
                    while($RowMailsLog = $StmtMailsLog->fetch_assoc()){
				$LinerAddress=mb_strimwidth($RowMailsLog['LinerAddress'], 0, 25, "...");
		
						?>
                       
					   <tr>
                       
						  <td> <?php echo $SNo; ?></td>
        <td ><?php echo  mb_strimwidth($RowMailsLog['FromMail'], 0, 20, "..."); ?> </td>	  
		<td><?php echo mb_strimwidth($RowMailsLog['ToMail'], 0, 20, "..."); ?></td>
		<td><?php echo mb_strimwidth($RowMailsLog['CCMail'], 0, 20, "..."); ?></td>

		<td><?php echo mb_strimwidth($RowMailsLog['Subject'], 0, 20, "..."); ?></td>
		<td><?php echo mb_strimwidth($RowMailsLog['Message'], 0, 20, "..."); ?></td>
		<td><?php echo date('d-m-Y H:i:s',strtotime($RowMailsLog['SentDateTime'])); ?></td>

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
