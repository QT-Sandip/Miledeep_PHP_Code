<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");



$PageTitleFolder="Master";
$PageTitle="Add Charges";


$ID=$_GET['id'];
$PostedUserId = $_SESSION['UserID'];
$PageUrl = ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME));


$SqlCountries="SELECT * FROM `ScrmCountriesList`";
$StmtCountries=$iconn->query($SqlCountries);


 $SqlCharges="SELECT * FROM `ScrmMiscCharges` ORDER BY ID DESC LIMIT 1;";
$StmtCharges=$iconn->query($SqlCharges);
$RowCharges=$StmtCharges->fetch_array();


$SqlChargesList="SELECT * FROM `ScrmMiscCharges` ";
$StmtChargesList=$iconn->query($SqlChargesList);



extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	$PostedDate=date("Y-m-d");
	
	if($ID==''){
	 $sql_org_insert="INSERT INTO `ScrmMiscCharges`(`CountryID`, `ClearanceCharge`, `AntiDumpingCharge`, `AmcChargePerc`, `ApplicableDate`, `GeneralType`, `ValidUpTo`, `MerchandiseProcessFee`, `USClearance`, `HarbourMaintenanceFee`, `DrawbackRateperKG`, `DrawbackRateperPercentage`, `ISFStaticDuty`, `ServiceTaxPercentage`, `PremiumRate`, `BLServiceTax`, `BLSWCES`, `PostedDate`) VALUES ('$CountryID','$ClearanceCharge','$AntiDumpingCharge','$AmcChargePerc','$ApplicableDate','$GeneralType','$ValidUpTo','$MerchandiseProcessFee','$USClearance','$HarbourMaintenanceFee','$DrawbackRateperKG','$DrawbackRateperPercentage','$ISFStaticDuty','$ServiceTaxPercentage','$PremiumRate','$BLServiceTax','$BLSWCES','$PostedDate')";
	

	 $stmt1=$iconn->query($sql_org_insert);
	 $NewID = $iconn->insert_id;

	 if($stmt1){
		legacyAuditLogInsertById('ScrmMiscCharges', 'ID', $NewID, $_POST, [
			'ReferenceNo' => $NewID,
			'Remarks' => 'Misc charges created',
		], 'Sales');
		echo '<script language="javascript" type="text/javascript"> 
                alert("Success");
                window.location = "MiscChargesList";
		</script>';
		//  header('Location: MiscChargesList.php');
	 }
}

else{
	$oldChargesData = legacyAuditFetchRowById('ScrmMiscCharges', 'ID', $ID, 'Sales');
	$sql_org_insert="UPDATE `ScrmMiscCharges` SET `CountryID`='$CountryID',`ClearanceCharge`='$ClearanceCharge',`AntiDumpingCharge`='$AntiDumpingCharge',`AmcChargePerc`='$AmcChargePerc',`ApplicableDate`='$ApplicableDate',`GeneralType`='$GeneralType',`ValidUpTo`='$ValidUpTo',`MerchandiseProcessFee`='$MerchandiseProcessFee',
	`USClearance`='$USClearance',`HarbourMaintenanceFee`='$HarbourMaintenanceFee',`DrawbackRateperKG`='$DrawbackRateperKG',`DrawbackRateperPercentage`='$DrawbackRateperPercentage',`ISFStaticDuty`='$ISFStaticDuty',`ServiceTaxPercentage`='$ServiceTaxPercentage',`PremiumRate`='$PremiumRate',`BLServiceTax`='$BLServiceTax',`BLSWCES`='$BLSWCES',
	`PostedDate`='$PostedDate' WHERE `ID`='$ID'";

$stmt1=$iconn->query($sql_org_insert);

if($stmt1){
	legacyAuditLogUpdateById('ScrmMiscCharges', 'ID', $ID, $oldChargesData, $_POST, [
		'ReferenceNo' => $ID,
		'Remarks' => 'Misc charges updated',
	], 'Sales');
	$ArgumentsArray[PostedUserId]=$PostedUserId;
            $ArgumentsArray[PageUrl]=$PageUrl;
            $ArgumentsArray[Remarks]=$Remarks;
            $ArgumentsArray[ReferenceID]=$ID;
            postUserEditLog($ArgumentsArray);

			echo '<script language="javascript" type="text/javascript"> 
						alert("Success");
						window.location = "MiscChargesList";
				</script>';

	// header('Location: MiscChargesList.php');
}
}
	  
	

}

 ?>


<!DOCTYPE html>
<html lang="en">

    <head>
        
            <?php include ($IncludesPath."title-meta.php"); ?>
		 	<?php  include ($IncludesPath."DbLinks.php");?>
            <?php include ($IncludesPath."head-css.php");?>

    </head>

    <body data-sidebar="dark">

	
	 <?php
			include ($IncludesPath."PreLoader.php"); ?>

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
					
						 <?php
	if($Status !=''){
	//Show Alert 
	?>
	 <div class="alert alert-success alert-dismissible fade show" role="alert">
                                                <i class="mdi mdi-check-all mr-2"></i>
                                                      <?php echo $Status;
$_SESSION['Status']='';	  ?>
                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>

<?php } ?>

                        <?php include ($IncludesPath."page-title.php");?>

	
          
	
<div class="row">
                            <div class="col-12">
                                <div class="card">
								
								<form role="form" method="post">
                                    <div class="card-body">
									
										<div class="row">

                  <div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Select Country<span style="color:red">*</span></label>              			   
				  <select required class="form-control select2" name="CountryID" id="CountryID">
				 <option> All </Option>
				<?php while($RowCountries=$StmtCountries->fetch_array()) { ?>  
				
				<option Value="<?php echo $RowCountries['id'];?>"><?php echo $RowCountries['name'];?></OPTION>
				
				<?php } ?>
				
				<select>   
                </div> 


                <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Clearance Charge<span style="color:red">*</span></label>
				<input required type="number" step="any" class="form-control" name='ClearanceCharge' id="ClearanceCharge" 
				  placeholder="Enter Clearance Charge" value="<?php echo $RowCharges['ClearanceCharge'] ?>">		
		
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Anti Dumping Charge<span style="color:red">*</span></label>
				<input required type="number" step="any" class="form-control" name='AntiDumpingCharge' id="AntiDumpingCharge" 
				  placeholder="Enter Anti Dumping Charge" value="<?php echo $RowCharges['AntiDumpingCharge'] ?>">		
		
                </div>	
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Amc Charge (%)<span style="color:red">*</span></label>
				<input required  type="number" step="any" class="form-control" name='AmcChargePerc' id="AmcChargePerc" 
				  placeholder="Enter Amc Charge(%)" value="<?php echo $RowCharges['AmcChargePerc'] ?>">		
		
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Applicable Date<span style="color:red">*</span></label>
				<input required type="date" onkeydown="return false" class="form-control" name='ApplicableDate' id="ApplicableDate" 
				  placeholder="Enter ApplicableDate" value="<?php echo $RowCharges['ApplicableDate'] ?>">		
		
                </div>
				
				<!--<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Valid UpTo</label>
				<input  type="date" class="form-control" name='ValidUpTo' id="ValidUpTo" 
				  placeholder="Enter Valid UpTo" value="<?php echo $RowCharges['ValidUpTo'] ?>">		
		
                </div>-->
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Merchandise Process Fee<span style="color:red">*</span></label>
				<input required type="number" class="form-control" step="any" name='MerchandiseProcessFee' id="MerchandiseProcessFee" 
				  placeholder="Enter Merchandise Process Fee" value="<?php echo $RowCharges['MerchandiseProcessFee'] ?>">		
		
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">US Clearance<span style="color:red">*</span></label>
				<input required type="number" step="any" class="form-control" name='USClearance' id="USClearance" 
				  placeholder="Enter US Clearance" value="<?php echo $RowCharges['USClearance'] ?>">		
		
                </div>
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Harbour MaintenanceFee<span style="color:red">*</span></label>
				<input required type="number" step="any" class="form-control" name='HarbourMaintenanceFee' id="HarbourMaintenanceFee" 
				  placeholder="Enter Harbour MaintenanceFee" value="<?php echo $RowCharges['HarbourMaintenanceFee'] ?>">		
		
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Drawback Rate per KG<span style="color:red">*</span></label>
				<input required type="number" step="any" class="form-control" name='DrawbackRateperKG' id="DrawbackRateperKG" 
				  placeholder="Enter Drawback Rate per KG" value="<?php echo $RowCharges['DrawbackRateperKG'] ?>">		
		
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Drawback Rate Percentage<span style="color:red">*</span></label>
				<input required type="number" step="any" class="form-control" name='DrawbackRateperPercentage' id="DrawbackRateperPercentage" 
				  placeholder="Enter Drawback Rate (%)" value="<?php echo $RowCharges['DrawbackRateperPercentage'] ?>">		
		
                </div>	

				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">ISF Static Duty<span style="color:red">*</span></label>
				<input required type="number" step="any" class="form-control" name='ISFStaticDuty' id="ISFStaticDuty" 
				  placeholder="Enter ISF Static Duty" value="<?php echo $RowCharges['ISFStaticDuty'] ?>">		
		
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Service Tax Percentage<span style="color:red">*</span></label>
				<input required type="number" step="any" class="form-control" name='ServiceTaxPercentage' id="ServiceTaxPercentage" 
				  placeholder="Enter Service Tax Percentage" value="<?php echo $RowCharges['ServiceTaxPercentage'] ?>">		
		
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Premium Rate<span style="color:red">*</span></label>
				<input required type="number" step="any" class="form-control" name='PremiumRate' id="ServiceTaxPercentage" 
				  placeholder="Enter Premium Rate" value="<?php echo $RowCharges['PremiumRate'] ?>">		
		
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">BL Service Tax<span style="color:red">*</span></label>
				<input required type="number" step="any" class="form-control" name='BLServiceTax' id="BLServiceTax" 
				  placeholder="Enter BL Service Tax" value="<?php echo $RowCharges['BLServiceTax'] ?>">		
		
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">BL SW CES<span style="color:red">*</span></label>
				<input required type="number" step="any" class="form-control" name='BLSWCES' id="BLSWCES" 
				  placeholder="Enter BL SW CES" value="<?php echo $RowCharges['BLSWCES'] ?>">		
		
                </div>
				
				
                </div>
				
				
				
			  <div class="col-xs-12 box-footer"  align="right">
			  <a target="" href="MiscChargesList.php"
       class="btn btn-danger  "> Close</a>
			     <?php    if($ID==''){ ?>
  
                <button type="submit"  name='submit' class="btn btn-success">Add</button>
				 <?php    } else { ?>
 
                <button type="submit"  name='submit' class="btn btn-success">Update</button>
				 <?php    } ?>

              </div>	</div>
				
				
					
				
				
								

            </form>
       <!-- end col -->
                        </div> <!-- end row -->
    
	</div> <!-- end card body-->
	
	
                       <!--     <div class="col-12">

						
        <div class="card">
          
        <div class="card-body" id="ShowDataTable">
                    

 <table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">
      <tr>
	  
	 <th>S.No</th> 
	  <th>Country</th>
      <th>Clearance Charge</th>
       <th>AntiDumping Charge</th>
      <th>Amc Charge Perc</th>
      <th>Applicable Date</th>
      <th>General Type</th>
      <th>Valid UpTo</th>
      <th>Merchandise Process Fee</th>
      <th>US Clearance</th>
      <th>Harbour Maintenance Fee</th>
      <th>DrawbackRate per KG</th>
      <th>DrawbackRate per  Percentage</th>
      <th>DISF Static Duty</th>
      <th>Service Tax Percentage</th>
      <th>Premium Rate</th>
      <th>BL Service Tax</th>
      <th>BL SW CES</th>
      <th>Posted Date</th>
      <th>Actions</th>
	  	
      </tr>
    </thead>
    <tbody>
	<?php
	//echo $sql;
   /*while($RowMiscCharges=$StmtChargesList->fetch_array()){
	 $Country=$RowMiscCharges['CountryID'];	
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
		$PostedDate= $RowMiscCharges['PostedDate'];?>
                       
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
	 <a  href="AddMiscCharges.php?id=<?php echo $RowMiscCharges['ID'];?>&PoID=<?php echo $INVOICE_ID;?>" 
						 class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
						 title="" data-original-title="Edit"><i class="mdi mdi-pencil font-size-18"></i></a>
        

	<!--<button class="btn btn-link text-danger Delete" data-id="<?php echo $row['ID'];?>" 
			
							data-toggle="tooltip" data-placement="top" title="" value=""
							data-original-title="Delete"><i class="mdi mdi-trash-can font-size-18"></i></button> -->						
	
	</td>
       

        </tr>

	  <?php 

   }*/
	  ?>

     </tbody>

  </table>
</div>
    
	</div> 
	
                                </div>-->
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

