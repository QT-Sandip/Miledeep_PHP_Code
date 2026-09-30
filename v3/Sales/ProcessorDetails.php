<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


$PageTitle="Processor Details";
$PageTitleFolder="Masters";
// $BranchID=$_SESSION['BranchID']; 
// $AccessType=$_SESSION['AccessType']; 


include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

$SqlAdminProcessor="SELECT * FROM `AdminExporterDetails` WHERE ExpType = 2 ORDER BY ExpID desc";

$StmtAdminProcessor = $iconn->query($SqlAdminProcessor) ;

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
							        <a href="AddProcessor.php" class="btn btn-success waves-effect waves-light"><i class="fa fa-plus"></i> New Processor</a> 				
					            </div>		
										
			
                                <table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >	
                                    <thead class="thead-light">
                                        <tr>                 
                                            <th>S.No</th>
                                            <th>Name</th>
                                            <th style="width: 400px;">Address</th>
                                            <!-- <th>City</th>
                                            <th>Zip Code</th>
                                            <th>Country</th> -->
                                            <th>US FDA REG. No</th>
                                            <th>EIA NO</th>
                                            <th>IE CODE</th>
                                            <th>Invoice Code</th>
                                            <th>Actions</th>
                        				</tr>
                                    </thead>
                                    <tbody>
                                        <?php				  
                                            $SNo=1;   
                                            // printf ($StmtAdminExporter->fetch_array());
                                            while($RowAdminProcessor=$StmtAdminProcessor->fetch_array()) {
                                                $ExpID =  $RowAdminProcessor['ExpID'];
                                                $ExpName =  $RowAdminProcessor['ExpName'];
                                                $ExpAddress =  $RowAdminProcessor['ExpAddress'];
                                                // $ProcessCity =  $RowAdminProcessor['ProcessCity'];
                                                // $ProcessZipCode =  $RowAdminProcessor['ProcessZipCode'];
                                                // $ProcessCountry =  $RowAdminProcessor['ProcessCountry'];
                                                $ExpUsfdaNo =  $RowAdminProcessor['ExpUsfdaNo'];
                                                $ExpApprovalNo =  $RowAdminProcessor['ExpApprovalNo'];
                                                $ExpIeCode =  $RowAdminProcessor['ExpIeCode'];
                                                $InvShortCode =  $RowAdminProcessor['InvShortCode'];

                                            // $ExchangeRate=	$RowExchangeRate['ExchangeRate'];	
                                            // $FromDate= getFormatedDate($RowExchangeRate['FromDate']);
                                        ?>                       
                                        <tr>                       
                                            <td> <?php echo $SNo ?></td>                        
                                            <td> <?php echo $ExpName?></td>  
                                            <td> <p style="white-space: break-spaces;"><?php echo $ExpAddress?></p></td>  
                                            <!-- <td>  <?php // echo $ProcessCity?></td>  
                                            <td> <?php // echo $ProcessZipCode?></td>  
                                            <td> <?php // echo $ProcessCountry?></td>   -->
                                            <td> <?php echo $ExpUsfdaNo?></td>  
                                            <td> <?php echo $ExpApprovalNo?></td>  
                                            <td> <?php echo $ExpIeCode?></td> 
                                            <td> <?php echo $InvShortCode?></td>  
                                            <td>
                                                <a target="" href="AddProcessor.php?id=<?php echo $ExpID;?>" 
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
