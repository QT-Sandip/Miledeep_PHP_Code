<?php

$PageTitle="Buyers List";
$PageTitleFolder="Quality Control";
$BranchID=$_SESSION['BranchID']; 
$AccessType=$_SESSION['AccessType']; 


include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$SqlBuyer="select * from `$TblScrmConsigneeDetails` ";
	


$StmtBuyer=$iconn->query($SqlBuyer);



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
                                    <div class="card-body" id="ClientList">
									
						<div class="col-lg-12 box-footer"  align="center">
			  	
							 <a href="AddBuyer.php" class="btn btn-success waves-effect waves-light">
				Add</a> 
				
					</div>				
			
<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">
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
