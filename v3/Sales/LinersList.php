<?php

$PageTitle="Liners List";
$PageTitleFolder="Sales";
$BranchID=$_SESSION['BranchID']; 
$AccessType=$_SESSION['AccessType']; 


include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$SqlLiner="SELECT * FROM `ScrmLinerMaster` WHERE Status=1 order by LinerID DESC  limit 100";
	


$StmtLiner=$iconn->query($SqlLiner);



?>
<!DOCTYPE html>

<html lang="en">


<link href="<?php echo  $href ?>"  rel="stylesheet" type="text/css" />
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
			  	
							 <a href="AddLiner.php" class="btn btn-success waves-effect waves-light">
				<i class="fa fa-plus"></i> New Liner</a> 
				
					</div>		
										
			
<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >	
 <thead class="thead-light">
			  <tr>
                
                 
                 <th>S.No</th>
      <th>Liner Name</th>
      <th>Address</th>
      <th>Contact No</th>
      <th>Tracking Url</th>
        <th >Actions</th>
                  
               
				</tr>
				</thead>
                <tbody>
                  <?php
				  
				  $SNo=1;
                   
                    while($RowLiner = $StmtLiner->fetch_assoc()){
				$LinerAddress=mb_strimwidth($RowLiner['LinerAddress'], 0, 25, "...");
		
						?>
                       
					   <tr>
                       
						  <td> <?php echo $SNo ?></td>
		<td> <?php echo $RowLiner['LinerName']?></td>  
		<td> <?php echo $LinerAddress?></td>  
		<td> <?php echo $RowLiner['ContactNo']?></td>  
		<td> <?php echo mb_strimwidth($RowLiner['ContainerTrackingUrl'], 0, 25, "...")?></td>  
		
						  <td>
						  
						  
                         
						 <a target="" href="AddLiner.php?id=<?php  echo $RowLiner['LinerID'];?>" 
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



</script>
