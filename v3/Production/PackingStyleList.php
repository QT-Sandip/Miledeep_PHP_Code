<?php

$PageTitle="Packing Styles List";
$PageTitleFolder="Production";

$AccessType=$_SESSION['AccessType']; 


include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$SqlPacking="SELECT * FROM `ProductionPackingStyles` Where Status='1'
   order by PCKID DESC  ";

$StmtPacking=$iconn->query($SqlPacking);



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

									<a href="CreatePackingStyles.php" class="btn btn-primary waves-effect waves-light">
				<i class="fa fa-plus"></i> New Packing Style</a>
				
								</div>		
			
<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">
			  <tr>
                
      <th>ID</th>
      <th>Packing Style</th>
	  <th>Gross Weight</th>
	  <th>Net Weight</th>
	  <!--<th>Total Slabs</th>-->
      <th>UOM</th>
      <!--<th>Slab Weight</th>-->
      
	  
               
				</tr>
				</thead>
                <tbody>
                  <?php
				  
				  $SNo=1;
                   
                    while($RowPacking = $StmtPacking->fetch_assoc()){
						$Netweight=$RowPacking['TotalSlabs']*$RowPacking['SlabWeight'];?>
                       
					   <tr>
                       
						  <td> <?php echo $SNo ?></td>
		
		
		<td > <?php echo $RowPacking['PackingStyle']?></td>
		<td> <?php echo $RowPacking['GrossWeight']?></td>  
		<td> <?php echo $Netweight?></td>  
		<td> <?php echo $RowPacking['Uom']?></td>     
		<!--<td> <?php echo $RowPacking['SlabWeight']?></td> -->
        
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
