<?php

$PageTitle="Tax Details";
$PageTitleFolder="Sales";
$BranchID=$_SESSION['BranchID']; 
$AccessType=$_SESSION['AccessType']; 


include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

$SqlMiscCharges="SELECT * FROM `ScrmMiscCharges` as SMC
left join ScrmCountriesList as SCL  on SMC.CountryID=SCL.id";
	
 $StmtMiscCharges=$iconn->query($SqlMiscCharges);



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
			  	
							 <a href="AddMiscCharges.php" class="btn btn-success waves-effect waves-light">
				<i class="fa fa-plus"></i> Add Charges</a> 
				
					</div>		
										
			
<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >	
 <thead class="thead-light">
			  <tr>
                
                 
      <th>S.No</th> 
	  <th>Country</th>
      <th>Clearance <BR> Charge</th>
       <th>AntiDumping <BR> Charge</th>
      <th>Amc Charge<BR>Perc(%)</th>
      <th>Applicable <BR>Date</th>
      <th>General<BR> Type</th>
      <th>Valid<BR> UpTo</th>
      <th>Merchandise<BR>  Process Fee</th>
      <th>US <BR>Clearance</th>
      <th>Harbour <BR>Maintenance Fee</th>
      <th>DrawbackRate <BR>per KG</th>
      <th>DrawbackRate per <BR>  Perc(%)</th>
      <th>DISF Static<BR> Duty</th>
      <th>Service Tax <BR> Perc(%)</th>
      <th>Premium <BR>Rate</th>
      <th>BL Service<BR> Tax</th>
      <th>BL SW <BR>CES</th>
      <th>Posted <BR>Date</th>
      <th>Actions</th>
                  
               
				</tr>
				</thead>
                <tbody>
                  <?php
				  
				  $SNo=1;
        while($RowMiscCharges=$StmtMiscCharges->fetch_array()){
		$Country=$RowMiscCharges['name'];	
		$ClearanceCharge= $RowMiscCharges['ClearanceCharge'];
		$AntiDumpingCharge= $RowMiscCharges['AntiDumpingCharge'];
		$AmcChargePerc= $RowMiscCharges['AmcChargePerc'];
		$ApplicableDate= $RowMiscCharges['ApplicableDate'];
		$GeneralType= $RowMiscCharges['GeneralType'];
		$ValidUpTo= $RowMiscCharges['ValidUpTo'];
		$MerchandiseProcessFee= $RowMiscCharges['MerchandiseProcessFee'];
		$USClearance= $RowMiscCharges['USClearance'];
		$HarbourMaintenanceFee= $RowMiscCharges['HarbourMaintenanceFee'];
		$DrawbackRateperKG= $RowMiscCharges['DrawbackRateperKG'];
		$DrawbackRateperPercentage= $RowMiscCharges['DrawbackRateperPercentage'];
		$ISFStaticDuty= $RowMiscCharges['ISFStaticDuty'];
		$ServiceTaxPercentage= $RowMiscCharges['ServiceTaxPercentage'];
		$PremiumRate= $RowMiscCharges['PremiumRate'];
		$BLServiceTax= $RowMiscCharges['BLServiceTax'];
		$BLSWCES= $RowMiscCharges['BLSWCES'];
		$PostedDate= $RowMiscCharges['PostedDate'];
		
		
		IF($Country==''){
			
			$Country='General';
		}?>
                       
					   <tr>
                       
		<td> <?php echo $SNo ?></td>
		<td> <?php echo $Country?></td>  
		<td> <?php echo $ClearanceCharge?></td>  
		<td> <?php echo $AntiDumpingCharge?></td>  
		<td> <?php echo $AmcChargePerc?></td>  
		<td> <?php echo getFormatedDatelines($ApplicableDate)?></td>  
		<td> <?php echo $GeneralType?></td>  
		<td> <?php echo getFormatedDatelines($ValidUpTo)?></td>  
		<td> <?php echo $MerchandiseProcessFee?></td>  
		<td> <?php echo $USClearance?></td>  
		<td> <?php echo $HarbourMaintenanceFee?></td>  
		<td> <?php echo $DrawbackRateperKG?></td>  
		<td> <?php echo $DrawbackRateperPercentage?></td>  
		<td> <?php echo $ISFStaticDuty?></td>  
		<td> <?php echo $ServiceTaxPercentage?></td>  
		<td> <?php echo $PremiumRate?></td>  
		<td> <?php echo $BLServiceTax?></td>  
		<td> <?php echo $BLSWCES?></td>  
		<td> <?php echo getFormatedDatelines($PostedDate) ?></td>  
		
		<td>
		<a target="" href="AddMiscCharges.php?id=<?php  echo $RowMiscCharges['ID'];?>" 
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
