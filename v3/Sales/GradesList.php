<?php

$PageTitle="Grades List";
$PageTitleFolder="ColdStore";
$BranchID=$_SESSION['BranchID']; 
$AccessType=$_SESSION['AccessType']; 


include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$SqlGrade="SELECT * FROM `ProductionGrades`";
$StmtGrade=$iconn->query($SqlGrade);



?>
<!DOCTYPE html>

<html lang="en">
<link href="https://serp.silverlights.in/Clients/SaiMarine/User.css"  rel="stylesheet" type="text/css" />

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
			  	
							 <a href="AddOptions.php" class="btn btn-success waves-effect waves-light">
							 <i class="fa fa-plus"></i> New Grade</a> 
				
					</div>				
			
<table  id="key-datatable" data-paging="true" class="table dt-responsive nowrap w-100" >
		
<thead class="thead-light">
			  <tr>
                
                 
        <th>ID</th>

        <th>Type</th>

        <th>Name</th>
        <th>Group</th>
     <!--   <th style="width: 10%">Actions</th>-->
                  
               
				</tr>
				</thead>
                <tbody>
                  <?php
				  
				  $SNo=1;
                   
                    while($RowGrade = $StmtGrade->fetch_assoc()){
              ?>
                       
					   <tr>
                       
		<td> <?php echo $SNo; ?></td>
        <td><?php echo $RowGrade['value']; ?> </td>
        <td><?php echo $RowGrade['Options']; ?> </td>
        <td><?php echo $RowGrade['GradeGroup']; ?> </td>
	  
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
 
 		 var table = $('#key-datatable').DataTable( {
        scrollY: "600px",
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
            dom: '<"row mb-2"<"col-md-6 d-flex align-items-center"lf>>rtip',
		  "bStateSave": true,
		   "fnStateSave": function (oSettings, oData) {
            localStorage.setItem('key-datatable', JSON.stringify(oData));
        },
        "fnStateLoad": function (oSettings) {
            return JSON.parse(localStorage.getItem('key-datatable'));
        }
    } );
	
	Stable.buttons().container()
        .appendTo('#scroll-vertical-datatable_wrapper .col-md-6:eq(0)');
		}


		


</script>
