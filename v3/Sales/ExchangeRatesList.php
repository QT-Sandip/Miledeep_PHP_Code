<?php

$PageTitle="Exchange Rates List";
$PageTitleFolder="Sales";
$BranchID=$_SESSION['BranchID']; 
$AccessType=$_SESSION['AccessType']; 


include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

$SqlExchangeRate="SELECT * FROM `ScrmExchangeRates` ORDER BY ExID desc";

 $StmtExchangeRate=$iconn->query($SqlExchangeRate);



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
			  	
							 <a href="AddExchangeRate.php" class="btn btn-success waves-effect waves-light">
				<i class="fa fa-plus"></i> New Exchange Rate</a> 
				
					</div>		
										
			
<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >	
 <thead class="thead-light">
			  <tr>
                
                 
      <th>S.No</th>
   
      <th>Currency</th>
      <th>Exchange Rate</th>
      <th>Applicable Date</th>
      <th>Actions</th>
                  
               
				</tr>
				</thead>
                <tbody>
                  <?php
				  
				  $SNo=1;
        while($RowExchangeRate=$StmtExchangeRate->fetch_array()){
		$ExchangeRate=	$RowExchangeRate['ExchangeRate'];	
		$CurrencyType=	$RowExchangeRate['CurrencyType'];	
		$FromDate= getFormatedDate($RowExchangeRate['FromDate']);


						?>
                       
					   <tr>
                       
		<td> <?php echo $SNo ?></td>
	
		<td> <?php echo $CurrencyType?></td>  
		<td> <?php echo $ExchangeRate?></td>  
		<td> <?php echo $FromDate?></td>  
		
						  <td>
						  
						  
                         
						 <a target="" href="AddExchangeRate.php?id=<?php  echo $RowExchangeRate['ExID'];?>" 
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
