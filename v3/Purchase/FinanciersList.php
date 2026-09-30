<?php

$PageTitle="Financiers List";
$PageTitleFolder="Purchase";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

  $SqlFinanciers = "SELECT * FROM `RmSuppliers` 
WHERE   FinancierName !='Other' and FinancierName !='Select Financier' and Status=1";
  $StmtFinanciers = $iconn->query($SqlFinanciers);

?>
<!DOCTYPE html>

<html lang="en">
<link href="https://serp.silverlights.in/Clients/JME/User.css"  rel="stylesheet" type="text/css" />

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
								 <div align="CENTER" class="col-lg-12">	
									 <a href="CreateFinancier.php" class="btn btn-success waves-effect waves-light">
				<i class="fa fa-plus"></i> New Financier</a>	
			</div>
<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">
      
			  <tr>
                
                 
         <th>S.No</th>
	     <th><?php echo $USER ?> List</th>
         <th> PAN No</th>
         <th> Bank Name</th>
         <th> Account No</th>
         <th> IFSC</th>
         <th> GST No</th>

				</tr>
				</thead>
                <tbody>
                  <?php
				  
				  $SNo=1;
                   
                    while($RowFinanciers= $StmtFinanciers->fetch_assoc()){?>
                       
					   <tr>
                       
		<td><?php echo $SNo;?></td>
		<td id="a_type<?php  echo $RowFinanciers['ID'];?>"><?php echo $RowFinanciers['FinancierName']; ?></td>
		<td ><?php echo $RowFinanciers['PanNumber']; ?></td>
		<td ><?php echo $RowFinanciers['BankName']; ?></td>
		<td ><?php echo $RowFinanciers['BankAccNo']; ?></td>
		<td ><?php echo $RowFinanciers['IFSC']; ?></td>
		<td ><?php echo $RowFinanciers['GstNo']; ?></td>

						 
						   
                         
                         
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
