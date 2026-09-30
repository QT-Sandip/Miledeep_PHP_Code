<?php

$PageTitle="Barcode List";
$PageTitleFolder="Cold Store";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$ClientID=$_SESSION["ClientID"];
$BarcodeDate=$_GET["id"];

 $sql="SELECT * FROM `CsBarCodes` where  BarcodeDate='$BarcodeDate'"; 
	 //echo $sql;
 //exit;
 $stmt=$iconn->query($sql);

 
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
                                    <div class="card-body" id="TraceabilityList">
									        <div class="card-body" id="ShowDataTable">
											
							<div class="col-lg-12 box-footer"  align="center">

						
								</div>		
			
<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">
			  <tr>
                
                 
                  <th>S.No</th>				  		 
                  <th>Batch codes</th>
                  <th>Printed Barcodes</th>
				  <th>USED Barcodes</th>
				  <th>UNUSED Barcodes</th>
				   <th>Actions</th>
                  
               
				</tr>
				</thead>
                <tbody>
                  <?php
				  
				  $SNo=1;
                   
                    while($row1 = $stmt->fetch_array()){
						
                 $BarCodeValue =$row1['BarCodeValue'];	
                 $BarCodeID =$row1['BarCodeID'];	
                // $BarCodeIDS =$row1['BarCodeIDS'];	
                 
           
 
  $SqlTrace = "
SELECT COUNT(*) AS PRINTED ,
(SELECT COUNT(*) FROM `CsBarCodeList` WHERE Status!='0' AND BarCodeID='$BarCodeID') as USED,
(SELECT COUNT(*) FROM `CsBarCodeList` WHERE Status='0' AND BarCodeID='$BarCodeID') as UNUSED
FROM `CsBarCodeList` WHERE BarCodeID='$BarCodeID'  
 ORDER BY `BarCodeSeriesID` DESC";
 
  $StmtTrace = $iconn->query($SqlTrace);
  $RowTrace = $StmtTrace->fetch_array();
//ECHO $SqlTrace; EXIT;
						
						?>
                       
					   <tr>
                       
						  <td><?php echo $SNo;?></td>
						  <td><?php echo  $BarCodeValue;?></td>
						  <td><?php echo  $RowTrace['PRINTED'];?></td>
						  <td><?php echo  $RowTrace['USED'];?></td>
						  <td><?php echo  $RowTrace['UNUSED'];?></td>
						   
						   
                         
                          <td>						  
                         
						 <a target="_blank" href="ViewBCLogList.php?ID=<?php  echo $RowTrace['TraceID'];?>" 
						 class="mr-3 text-primary Edit" data-toggle="tooltip" data-placement="top"
						 title="" data-original-title="View"><i class="mdi mdi-eye font-size-18"></i></a>
                      		  
		
					   </td>
                        </tr>
                      <?php
                $SNo++;    
				}
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
