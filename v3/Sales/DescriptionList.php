<?php

$PageTitle="Description List";
$PageTitleFolder="Sales";
$BranchID=$_SESSION['BranchID']; 
$AccessType=$_SESSION['AccessType']; 


include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

$SqlDescription="SELECT * FROM `ScrmDescriptionMaster` SDM
LEFT JOIN AdminExporterDetails AED ON AED.ExpID=SDM.ExporterID";

 $StmtDescription=$iconn->query($SqlDescription);



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
			  	
							 <a href="AddDescription.php" class="btn btn-success waves-effect waves-light">
				<i class="fa fa-plus"></i> New Description</a> 
				
					</div>		
										
			
<table  id="key-datatable" data-paging="false" 
class="table dt-responsive wrap w-100" >	
 <thead class="thead-light">
			  <tr>
                
                 
      <th>S.No</th>
   
      <th>Applicable Date</th>
      <th>Exporter</th>
      <th>Custom Note</th>
      <th>Chapter Note</th>
      <th>Shipment Note</th>
      <th>DrawBack Scheme Note</th>
      <th>Tax Note</th>
      <th>Declaration Note</th>
      <th>Additional Note</th>
      <th>C.Invoice Note</th>
      <th>Proforma Note</th>
      <th>Notifications</th>
      <th>Actions</th>
                  
               
				</tr>
				</thead>
                <tbody>
                  <?php
				  
				  $SNo=1;
        while($RowDescription=$StmtDescription->fetch_array()){
			
			
		$EffectiveFromDate= getFormatedDate($RowDescription['EffectiveFromDate']);
        $CustomNote=	$RowDescription['CustomNote'];
        $ChapterNote=	$RowDescription['ChapterNote'];
        $ShipmentNote=	$RowDescription['ShipmentNote'];
        $DrawBackNote=	$RowDescription['DrawBackNote'];
        $TaxNote=	$RowDescription['TaxNote'];
        $DeclarationNote=	$RowDescription['DeclarationNote'];
        $AdditionalNote=	$RowDescription['AdditionalNote'];
        $CommercialNote=	$RowDescription['CommercialNote'];
        $ProformaNote=	$RowDescription['ProformaNote'];
        $Notifications=	$RowDescription['Notifications'];
        $ExpName=	$RowDescription['ExpName'];

						?>     
					   <tr>
                       
		<td> <?php echo $SNo ?></td>
	
		<td> <?php echo $EffectiveFromDate?></td>  
		<td> <?php echo $ExpName?></td>  
		<td> <?php echo $CustomNote?></td>  
		<td> <?php echo $ChapterNote?></td>  
		<td> <?php echo $ShipmentNote?></td>  
		<td> <?php echo $DrawBackNote?></td>  
		<td> <?php echo $TaxNote?></td>  
		<td> <?php echo $DeclarationNote?></td>  
		<td> <?php echo $AdditionalNote?></td>  
		<td> <?php echo $CommercialNote?></td>  
		<td> <?php echo $ProformaNote?></td>  
		<td> <?php echo $Notifications?></td>  
		
						  <td>
						  
						  
                         
						 <a target="" href="AddDescription.php?id=<?php  echo $RowDescription['DesID'];?>" 
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
