<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


$PageTitle="Exporter Details";
$PageTitleFolder="Masters";
// $BranchID=$_SESSION['BranchID']; 
// $AccessType=$_SESSION['AccessType']; 


include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

$SqlAdminExporter="SELECT * FROM `AdminExporterDetails` WHERE ExpType = 1 ORDER BY ExpID desc";

$StmtAdminExporter = $iconn->query($SqlAdminExporter) ;

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
        <?php // include ($IncludesPath."PreLoader.php"); ?>

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
							        <a href="AddExporter.php" class="btn btn-success waves-effect waves-light"><i class="fa fa-plus"></i> New Exporter</a> 				
					            </div>		
										
			
                                <table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >	
                                    <thead class="thead-light">
                                        <tr>                 
                                            <th>S.No</th>
                                            <th>Name</th>
                                            <th>Address</th>                                            
                                            <!-- <th>Country</th>
                                            <th>Zip Code</th>
                                            <th>Phone</th>
                                            <th>Fax</th>
                                            <th>IEC</th>
                                            <th>APVAT</th>
                                            <th>Cust AIG No</th>
                                            <th>PAN</th> -->
                                            <th>Tax</th>
                                            <th>GSTIN</th>
                                            <th>CINNO</th>
                                            <th>Invoice Code</th>
                                            <th>Actions</th>
                                            <!-- <th>Applicable Date</th>
                                            <th>Actions</th> -->
                        				</tr>
                                    </thead>
                                    <tbody>
                                        <?php				  
                                            $SNo=1;   
                                            // printf ($StmtAdminExporter->fetch_array());
                                            while($RowAdminExporter=$StmtAdminExporter->fetch_array()) {
                                                $ExpID =  $RowAdminExporter['ExpID'];
                                                $ExpName =  $RowAdminExporter['ExpName'];
                                                $ExpAddress =  $RowAdminExporter['ExpAddress'];
                                                // $ExpCountry =  $RowAdminExporter['ExpCountry'];
                                                // $ExpZipCode =  $RowAdminExporter['ExpZipCode'];
                                                // $ExpPhone =  $RowAdminExporter['ExpPhone'];
                                                // $ExpFax =  $RowAdminExporter['ExpFax'];
                                                // $ExpIEC =  $RowAdminExporter['ExpIEC'];
                                                // $ExpAPVAT =  $RowAdminExporter['ExpAPVAT'];
                                                // $ExpCustAIFNo =  $RowAdminExporter['ExpCustAIFNo'];
                                                // $ExpPAN =  $RowAdminExporter['ExpPAN'];
                                                $ExpTax =  $RowAdminExporter['ExpTax'];
                                                $ExpGstNo =  $RowAdminExporter['ExpGstNo'];
                                                $ExpCINNO =  $RowAdminExporter['ExpCINNO'];
                                                $InvShortCode =  $RowAdminExporter['InvShortCode'];

                                            // $ExchangeRate=	$RowExchangeRate['ExchangeRate'];	
                                            // $FromDate= getFormatedDate($RowExchangeRate['FromDate']);
                                        ?>                       
                                        <tr>                       
                                            <td> <?php echo $SNo ?></td>                        
                                            <td> <?php echo $ExpName?></td>  
                                            <td> <p style="white-space: break-spaces;"> <?php echo $ExpAddress?></p></td>  
                                            <!-- <td> <?php // echo $ExpCountry?></td>  
                                            <td> <?php // echo $ExpZipCode?></td>  
                                            <td> <?php // echo $ExpPhone?></td>  
                                            <td> <?php // echo $ExpFax?></td>  
                                            <td> <?php // echo $ExpIEC?></td>   
                                            <td> <?php // echo $ExpAPVAT?></td>  
                                            <td> <?php // echo $ExpCustAIFNo?></td>  
                                            <td> <?php // echo $ExpPAN?></td>  -->
                                            <td> <?php echo $ExpTax?></td>  
                                            <td> <?php echo $ExpGstNo?></td>  
                                            <td> <?php echo $ExpCINNO?></td>  
                                            <td> <?php echo $InvShortCode?></td>  
                                            <td>
                                                <a target="" href="AddExporter.php?id=<?php echo $ExpID;?>" 
                                                class="mr-3 text-primary Edit" data-toggle="tooltip" data-placement="top"
                                                title="" data-original-title="Edit"><i class="mdi mdi-pencil font-size-18"></i></a>
                                            </td>
                                            <!-- <td> <?php// echo $FromDate?></td>  		
                                            <td>
                                                <a target="" href="AddExchangeRate.php?id=<?php // echo $RowExchangeRate['ExID'];?>" 
                                                class="mr-3 text-primary Edit" data-toggle="tooltip" data-placement="top"
                                                title="" data-original-title="Edit"><i class="mdi mdi-pencil font-size-18"></i></a>

                                            </td> -->
                                        </tr>
                                        <?php $SNo++;  } ?>
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
