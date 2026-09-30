<?php

$PageTitle="Forward Contract";
$PageTitleFolder="Sales";

$AccessType=$_SESSION['AccessType']; 


include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$SqlForwardContract="select * from `ScrmForwardContractsDetails` WHERE Status=1 ";
$StmtForwardContract=$iconn->query($SqlForwardContract);



?>
<!DOCTYPE html>

<html lang="en">

<link href="https://serp.silverlights.in/Clients/Jlsf/User.css"  rel="stylesheet" type="text/css" />
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
                                    <div class="card-body" id="ShowDataTable">
									
							<div class="col-lg-12 box-footer"  align="center">
			  	
							 <a href="AddForwardContract.php" class="btn btn-success waves-effect waves-light">
				<i class="fa fa-plus"></i> New Forward Contract</a>
					</div>			
			
<table id="key-datatable"  data-paging="false"
										class="table dt-responsive nowrap w-100">
					  <thead>
			  
			  <tr>
        <th>ID</th>
		<th>Forward Contract No</th>
        <th>Start Dt</th>
        <th>End Dt</th>
        <th>Value</th>
		<th>Actions</th>

                  
               
				</tr>
				</thead>
                <tbody>
                  <?php
				  
				  $SNo=1;
                   
                    while($RowFC = $StmtForwardContract->fetch_assoc()){
						//$StrtDt= getFormatedDateNA($RowFC['StartDt']);
						//$EndDt= getFormatedDateNA($RowFC['EndDt']);
						?>
                       
					   <tr>
                       
		<td> <?php echo $SNo ?></td>
        <td ><?php echo $RowFC['FCNumber']; ?> </td>	  
		<td><?php echo date("d-m-Y", strtotime($RowFC['StartDt'])) //$RowFC['StartDt']; ?></td>
		<td><?php echo date("d-m-Y", strtotime($RowFC['StartDt'])) //$RowFC['EndDt']; ?></td>
		<td><?php echo $RowFC['NetValue']; ?></td>

						  <td>
						  
						  
                         
     <a target="_blank" href="AddForwardContract.php?FCID=<?php  echo 
	 $RowFC['FCID'];?>" class="mr-3 text-primary Edit" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="mdi mdi-pencil font-size-18"></i></a>
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
         <?php include ($IncludesPath."ShowDataTable.php");?>


       
    </body>
</html>				

<script>
$(document).ready(function(){
	   showData();

	
    
	});


/*function showData(data){
			
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
		 bSort : false,
		  "bStateSave": true,
		   "fnStateSave": function (oSettings, oData) {
            localStorage.setItem('key-datatable', JSON.stringify(oData));
        },
        "fnStateLoad": function (oSettings) {
            return JSON.parse(localStorage.getItem('key-datatable'));
        }
    } );
	
		}*/


		


</script>
