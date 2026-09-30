<?php

$PageTitle="Payment Terms";
$PageTitleFolder="Sales";

$AccessType=$_SESSION['AccessType']; 


include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$SqlPayment="select * from `ScrmPaymentTerms` WHERE Status=1  ORDER BY `ID` DESC limit 100";
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
                                    <div class="card-body" id="ShowDataTable">
									
							<div class="col-lg-12 box-footer"  align="center">
			  	
							 <a href="AddPymntTrms.php" class="btn btn-success waves-effect waves-light">
				<i class="fa fa-plus"></i> New Payment Term</a>
					</div>			
			
<table id="key-datatable"  data-paging="false"
			class="table wrap w-100">
					  <thead class="thead-light">
			  
			  <tr>
        <th style="min-width: 50px">ID</th>
		<th>Payment Name</th>
        <th style="min-width: 150px">Payment Mode</th>
        <th style="min-width: 150px">Condition</th>
        <th style="min-width: 170px">Number of Days</th>
		<th>Actions</th>

                  
               
				</tr>
				</thead>
                <tbody>
                  <?php
				  
				  $SNo=1;
                   
                    while($RowPayment = $StmtPayment->fetch_assoc()){?>
                       
					   <tr>
                       
		<td> <?php echo $SNo ?></td>
        <td ><?php echo $RowPayment['PaymentName']; ?> </td>	  
		<td><?php echo $RowPayment['PaymentMode']; ?></td>
        <td><?php echo $RowPayment['ConditionDate']; ?></td>
        <td><?php echo str_replace("DAYS","",$RowPayment['NoOfDays']) ; ?></td>

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
