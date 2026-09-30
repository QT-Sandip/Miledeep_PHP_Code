<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


$PageTitleFolder="Master";
$PageTitle="Add Item Codes ";


$VarietyID=$_GET['id'];

$SqlVariety="SELECT * FROM `ScrmProductList`";
$StmtVariety=$iconn->query($SqlVariety);


$SqlPIVariety="SELECT * FROM `ScrmPIVarietyList` WHERE VarietyID='$VarietyID'";
$StmtPIVariety=$iconn->query($SqlPIVariety);
$RowPIVariety=$StmtPIVariety->fetch_array();

$SqlSpecies="SELECT * FROM `ScrmSpeciesMaster` ORDER BY SortOrder asc";
$StmtSpecies=$iconn->query($SqlSpecies);


$SqlFreezingType="SELECT * FROM `ScrmFreezingTypeMaster` ORDER BY SortOrder asc";
$StmtFreezingType=$iconn->query($SqlFreezingType);

$SqlPreparation="SELECT * FROM `ScrmPreparationMaster` ORDER BY SortOrder asc";
$StmtPreparation=$iconn->query($SqlPreparation);


$SqlGlazeType="SELECT * FROM `ScrmGlazeTypeMaster` ORDER BY SortOrder asc";
$StmtGlazeType=$iconn->query($SqlGlazeType);


$SqlTreatments="SELECT * FROM `ScrmTreatmentsMaster` ORDER BY SortOrder asc";
$StmtTreatments=$iconn->query($SqlTreatments);


extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	$ProductID=$ShortName;
	$SqlVariety="SELECT * FROM `ScrmProductList` where ProductID='$ProductID'";
$StmtVariety=$iconn->query($SqlVariety);
$RowVariety=$StmtVariety->fetch_array();
$ShortName=$RowVariety['ShortName'];
$FullName=$RowVariety['FullName'];


	
	function checkEmpty($Value){
		if($Value==''){
		$Value='';	
		}
		else{
		$Value='_'.$Value;	
		}
		return $Value;
	}
	
	$ProductFormm=checkEmpty($ShortName);
	$FreezingTypee=checkEmpty($FreezingType);
	$PreparationTypee=checkEmpty($PreparationType);
	$GlazePercc=checkEmpty($GlazePerc);
	$Glazee=checkEmpty($Glaze);
	$Treatmentt=checkEmpty($Treatment);
	
	
	 $ProductDescription=$Species.$ProductFormm.$FreezingTypee.$PreparationTypee.$GlazePercc.$Glazee.$Treatmentt; 
	
if($VarietyID==''){

	 $SqlInsert="
	INSERT INTO `ScrmPIVarietyList`(`TypeProduct`,
	`ShortName`, `FullName`, `Category`, `Status`, `BuyerID`, `ItemCode`,
	`Glaze`, `GlazePerc`, `Treatment`, `Grade`, `FreezingType`, `ProductID`,
	`ProductDescription`,`Species`, `PreparationType`, `BrandName`, `GlazeStatus`) VALUES  ('$TypeProduct','$ShortName','$FullName'
	,'$Category','1','$BuyerID','$ItemCode','$Glaze',
	'$GlazePerc','$Treatment','$Grade','$FreezingType',
	'$ProductID','$ProductDescription','$Species'
	,'$PreparationType','$BrandName','$GlazeStatus')";
	
	
	
}

else{
	$oldVarietyData = legacyAuditFetchRowById('ScrmPIVarietyList', 'VarietyID', $VarietyID, 'Sales');
	 $SqlInsert="UPDATE `ScrmPIVarietyList` SET 
	 `TypeProduct`='$TypeProduct',
	 `ShortName`='$ShortName',`Glaze`='$Glaze',`GlazePerc`='$GlazePerc',
	 `Treatment`='$Treatment',`Grade`='$Grade',`FreezingType`='$FreezingType',
	 `ProductID`='$ProductID',`ProductDescription`='$ProductDescription',
	 `Species`='$Species',`PreparationType`='$PreparationType',
	 `BrandName`='$BrandName',`GlazeStatus`='$GlazeStatus',
	 `FullName`='$FullName' WHERE `VarietyID`='$VarietyID'";
}
	$StmtInsert=$iconn->query($SqlInsert);
	$NewVarietyID = $iconn->insert_id;
	
	if($StmtInsert){
		if($VarietyID==''){
			legacyAuditLogInsertById('ScrmPIVarietyList', 'VarietyID', $NewVarietyID, $_POST, [
				'ReferenceNo' => $NewVarietyID,
				'Remarks' => 'Product code created',
			], 'Sales');
			header('Location: AddVariety.php');
		}else{
			legacyAuditLogUpdateById('ScrmPIVarietyList', 'VarietyID', $VarietyID, $oldVarietyData, $_POST, [
				'ReferenceNo' => $VarietyID,
				'Remarks' => 'Product code updated',
			], 'Sales');
			header('Location: VarietyList');
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
	  <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Product Code</label>
				<input  type="text" class="form-control" name='GlazePerc' id="GlazePerc" 
				  placeholder="Enter Glaze(%)" value="<?php echo $RowPIVariety['GlazePerc'] ?>" disabled>		
		
      </div>
	  
	    <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Connum Doc</label>
				<input  type="text" class="form-control" name='GlazePerc' id="GlazePerc" 
				  placeholder="Enter Glaze(%)" value="<?php echo $RowPIVariety['GlazePerc'] ?>" disabled>		
		
                </div>
				
				  <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Connum Sold</label>
				<input  type="text" class="form-control" name='GlazePerc' id="GlazePerc" 
				  placeholder="Enter Glaze(%)" value="<?php echo $RowPIVariety['GlazePerc'] ?>" disabled>		
		
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">SUPC</label>
				<input  type="text" class="form-control" name='GlazePerc' id="GlazePerc" 
				  placeholder="Enter Glaze(%)" value="<?php echo $RowPIVariety['GlazePerc'] ?>" disabled>		
		
                </div>
				
				<div class="form-group col-sm-6" >
                  <label for="exampleInputEmail1">Product Description </label>
				<input  type="text" class="form-control" name='GlazePerc' id="GlazePerc" 
				  placeholder="Enter Glaze(%)" value="<?php echo $RowPIVariety['GlazePerc'] ?>" disabled>		
		
                </div>
				
				<div class="form-group col-sm-6" >
                  <label for="exampleInputEmail1">Buyer Product Desc</label>
				<input  type="text" class="form-control" name='GlazePerc' id="GlazePerc" 
				  placeholder="Enter Glaze(%)" value="<?php echo $RowPIVariety['GlazePerc'] ?>" disabled>		
		
                </div>
										
			    </div>
				<HR></HR>
				
                <div class="row">
				
				
				<div class="form-group col-lg-3" >              									 
                <label for="exampleInputEmail1">Cooked Form</label>
				<select  class="form-control select2" name="Species" id="Species">
			     <option value=""> Select </option>
				   <?php while($RowSpecies=$StmtSpecies->fetch_array()) { ?>  
				
				<option <?php if($RowSpecies['ShortCode']==$RowPIVariety['Species']){ ?> selected=="selected"<?PHP } ?>
				Value="<?php echo $RowSpecies['ShortCode'];?>"><?php echo $RowSpecies['SpeciesName'];?></OPTION>
				
				<?php } ?>
				<select> 

                </div>

				 <div class="form-group col-lg-3" >              									 
                <label for="exampleInputEmail1">Variety</label>
				<select class="form-control select2" name="ShortName" id="ShortName">
			    <option value=""> Select </option>
				   <?php while($RowVariety=$StmtVariety->fetch_array()) { ?>  
				
				<option <?php if($RowVariety['ShortName']==$RowPIVariety['ShortName']){ ?> selected=="selected"<?PHP } ?>
				
				Value="<?php echo $RowVariety['ProductID'];?>"><?php echo $RowVariety['ShortName'];?></OPTION>
				
				<?php } ?>
				</select> 

                </div>
				 <div class="form-group col-lg-3" >              									 
                <label for="exampleInputEmail1">Head Status</label>
				<select class="form-control select2" name="ShortName" id="ShortName">
			    <option value=""> Select </option>
				   <?php while($RowVariety=$StmtVariety->fetch_array()) { ?>  
				
				<option <?php if($RowVariety['ShortName']==$RowPIVariety['ShortName']){ ?> selected=="selected"<?PHP } ?>
				
				Value="<?php echo $RowVariety['ProductID'];?>"><?php echo $RowVariety['ShortName'];?></OPTION>
				
				<?php } ?>
				</select> 

                </div>
				 <div class="form-group col-lg-3" >              									 
                <label for="exampleInputEmail1">Organic Certification</label>
				<select class="form-control select2" name="ShortName" id="ShortName">
			    <option value=""> Select </option>
				   <?php while($RowVariety=$StmtVariety->fetch_array()) { ?>  
				
				<option <?php if($RowVariety['ShortName']==$RowPIVariety['ShortName']){ ?> selected=="selected"<?PHP } ?>
				
				Value="<?php echo $RowVariety['ProductID'];?>"><?php echo $RowVariety['ShortName'];?></OPTION>
				
				<?php } ?>
				</select> 

                </div>

	            <div class="form-group col-lg-3" >              									 
                <label for="exampleInputEmail1">Shell Name</label>
				<select class="form-control select2" name="ShortName" id="ShortName">
			    <option value=""> Select </option>
				   <?php while($RowVariety=$StmtVariety->fetch_array()) { ?>  
				
				<option <?php if($RowVariety['ShortName']==$RowPIVariety['ShortName']){ ?> selected=="selected"<?PHP } ?>
				
				Value="<?php echo $RowVariety['ProductID'];?>"><?php echo $RowVariety['ShortName'];?></OPTION>
				
				<?php } ?>
				</select> 

                </div>
				
				<div class="form-group col-lg-3" >              									 
                <label for="exampleInputEmail1">Vien Name</label>
				<select class="form-control select2" name="ShortName" id="ShortName">
			    <option value=""> Select </option>
				   <?php while($RowVariety=$StmtVariety->fetch_array()) { ?>  
				
				<option <?php if($RowVariety['ShortName']==$RowPIVariety['ShortName']){ ?> selected=="selected"<?PHP } ?>
				
				Value="<?php echo $RowVariety['ProductID'];?>"><?php echo $RowVariety['ShortName'];?></OPTION>
				
				<?php } ?>
				</select> 

                </div>
				
				<div class="form-group col-lg-3" >              									 
                <label for="exampleInputEmail1">Tail Name</label>
				<select class="form-control select2" name="ShortName" id="ShortName">
			    <option value=""> Select </option>
				   <?php while($RowVariety=$StmtVariety->fetch_array()) { ?>  
				
				<option <?php if($RowVariety['ShortName']==$RowPIVariety['ShortName']){ ?> selected=="selected"<?PHP } ?>
				
				Value="<?php echo $RowVariety['ProductID'];?>"><?php echo $RowVariety['ShortName'];?></OPTION>
				
				<?php } ?>
				</select> 

                </div>
				
				<div class="form-group col-lg-3" >              									 
                <label for="exampleInputEmail1">Grade Size</label>
				<select class="form-control select2" name="ShortName" id="ShortName">
			    <option value=""> Select </option>
				   <?php while($RowVariety=$StmtVariety->fetch_array()) { ?>  
				
				<option <?php if($RowVariety['ShortName']==$RowPIVariety['ShortName']){ ?> selected=="selected"<?PHP } ?>
				
				Value="<?php echo $RowVariety['ProductID'];?>"><?php echo $RowVariety['ShortName'];?></OPTION>
				
				<?php } ?>
				</select> 

                </div>
				
				
				<div class="form-group col-lg-3" >              									 
                <label for="exampleInputEmail1">Shrimp Preparation</label>
				<select class="form-control select2" name="FreezingType" id="FreezingType">
			     <option value=""> Select </option>
				   <?php while($RowFreezingType=$StmtFreezingType->fetch_array()) { 				?>  
				
				<option <?php if($RowFreezingType['ShortCode']==$RowPIVariety['FreezingType']){ ?> selected=="selected"
				<?PHP } ?>
				Value="<?php echo $RowFreezingType['ShortCode'];?>"><?php echo $RowFreezingType['FreezingTypeName'];?></OPTION>
				
				<?php } ?>
				</select> 

                </div>
				
				<div class="form-group col-lg-3" >              									 
                <label for="exampleInputEmail1">Frozen Form</label>
				<select class="form-control select2" name="ShortName" id="ShortName">
			    <option value=""> Select </option>
				   <?php while($RowVariety=$StmtVariety->fetch_array()) { ?>  
				
				<option <?php if($RowVariety['ShortName']==$RowPIVariety['ShortName']){ ?> selected=="selected"<?PHP } ?>
				
				Value="<?php echo $RowVariety['ProductID'];?>"><?php echo $RowVariety['ShortName'];?></OPTION>
				
				<?php } ?>
				</select> 

                </div>
				
				<div class="form-group col-lg-3" >              									 
                <label for="exampleInputEmail1">Flavoring</label>
				<select class="form-control select2" name="ShortName" id="ShortName">
			    <option value=""> Select </option>
				   <?php while($RowVariety=$StmtVariety->fetch_array()) { ?>  
				
				<option <?php if($RowVariety['ShortName']==$RowPIVariety['ShortName']){ ?> selected=="selected"<?PHP } ?>
				
				Value="<?php echo $RowVariety['ProductID'];?>"><?php echo $RowVariety['ShortName'];?></OPTION>
				
				<?php } ?>
				</select> 

                </div>
				
				<div class="form-group col-lg-3" >              									 
                <label for="exampleInputEmail1">Pack Style</label>
				<select class="form-control select2" name="ShortName" id="ShortName">
			    <option value=""> Select </option>
				   <?php while($RowVariety=$StmtVariety->fetch_array()) { ?>  
				
				<option <?php if($RowVariety['ShortName']==$RowPIVariety['ShortName']){ ?> selected=="selected"<?PHP } ?>
				
				Value="<?php echo $RowVariety['ProductID'];?>"><?php echo $RowVariety['ShortName'];?></OPTION>
				
				<?php } ?>
				</select> 

                </div>
				
				<div class="form-group col-lg-3" >              									 
                <label for="exampleInputEmail1">Presentation</label>
				<select class="form-control select2" name="PreparationType" id="PreparationType">
			    <option value=""> Select </option>
				   <?php while($RowPreparation=$StmtPreparation->fetch_array()) { ?>  
				
				<option <?php if($RowPreparation['ShortCode']==$RowPIVariety['PreparationType']){ ?> selected=="selected"
				<?PHP } ?>
				Value="<?php echo $RowPreparation['ShortCode'];?>"><?php echo $RowPreparation['PreparationName'];?></OPTION>
				
				<?php } ?>
				</select> 

                </div>
				
				<div class="form-group col-lg-3" >              									 
                <label for="exampleInputEmail1">Species</label>
				<select class="form-control select2" name="PreparationType" id="PreparationType">
			    <option value=""> Select </option>
				   <?php while($RowPreparation=$StmtPreparation->fetch_array()) { ?>  
				
				<option <?php if($RowPreparation['ShortCode']==$RowPIVariety['PreparationType']){ ?> selected=="selected"
				<?PHP } ?>
				Value="<?php echo $RowPreparation['ShortCode'];?>"><?php echo $RowPreparation['PreparationName'];?></OPTION>
				
				<?php } ?>
				</select> 

                </div>
				
				<div class="form-group col-lg-3" >              									 
                <label for="exampleInputEmail1">Preservative</label>
				<select class="form-control select2" name="PreparationType" id="PreparationType">
			    <option value=""> Select </option>
				   <?php while($RowPreparation=$StmtPreparation->fetch_array()) { ?>  
				
				<option <?php if($RowPreparation['ShortCode']==$RowPIVariety['PreparationType']){ ?> selected=="selected"
				<?PHP } ?>
				Value="<?php echo $RowPreparation['ShortCode'];?>"><?php echo $RowPreparation['PreparationName'];?></OPTION>
				
				<?php } ?>
				</select> 

                </div>
				
				<div class="form-group col-lg-3" >              									 
                <label for="exampleInputEmail1">Glaze Type</label>
				<select class="form-control select2" name="PreparationType" id="PreparationType">
			    <option value=""> Select </option>
				   <?php while($RowPreparation=$StmtPreparation->fetch_array()) { ?>  
				
				<option <?php if($RowPreparation['ShortCode']==$RowPIVariety['PreparationType']){ ?> selected=="selected"
				<?PHP } ?>
				Value="<?php echo $RowPreparation['ShortCode'];?>"><?php echo $RowPreparation['PreparationName'];?></OPTION>
				
				<?php } ?>
				</select> 

                </div>
				

	
				
	            
	            <div class="form-group col-lg-3" >              									 
                <label for="exampleInputEmail1">Treatment</label>
				<select required class="form-control select2" name="GlazeStatus" id="GlazeStatus">
				    <option value=""> Select</option>
					<option <?php if($RowPIVariety['GlazeStatus']=='Yes') { ?> selected="selected" <?php } ?> value="Yes">Yes</option>
					<option <?php if($RowPIVariety['GlazeStatus']=='No') { ?> selected="selected" <?php } ?> value="No">No</option>				
				</select>
                </div>
				
				<div class="form-group col-lg-3" >              									 
                <label for="exampleInputEmail1">Brand</label>
				<select required class="form-control select2" name="GlazeStatus" id="GlazeStatus">
				    <option value=""> Select</option>
					<option <?php if($RowPIVariety['GlazeStatus']=='Yes') { ?> selected="selected" <?php } ?> value="Yes">Yes</option>
					<option <?php if($RowPIVariety['GlazeStatus']=='No') { ?> selected="selected" <?php } ?> value="No">No</option>				
				</select>
                </div>
				
				<div class="form-group col-lg-3" >              									 
                <label for="exampleInputEmail1">Group Company</label>
				<select required class="form-control select2" name="GlazeStatus" id="GlazeStatus">
				    <option value=""> Select</option>
					<option <?php if($RowPIVariety['GlazeStatus']=='Yes') { ?> selected="selected" <?php } ?> value="Yes">Yes</option>
					<option <?php if($RowPIVariety['GlazeStatus']=='No') { ?> selected="selected" <?php } ?> value="No">No</option>				
				</select>
                </div>
				
				
                
				
				
				
				
                </div>
				
				
				
			  <div class="col-xs-12 box-footer"  align="right">
			  <a target="" href="ProductList.php"
       class="btn btn-danger  "> Close</a>
			     <?php    if($VarietyID==''){ ?>
  
                <button type="submit"  name='submit' class="btn btn-success">ADD</button>
				 <?php    } else { ?>
  
                <button type="submit"  name='submit' class="btn btn-success">Update</button>
				 <?php    } ?>

              </div>	</div>
				
				
					
				
				
								

            </form>
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
		
  <script>
  $('#GlazeStatus').change(function(){
var GlazeStatus= $('#GlazeStatus').val(); 

if(GlazeStatus!='Yes'){

	document.getElementById("GlazePerc").disabled=true;
	
}else{
	
	document.getElementById("GlazePerc").disabled=false;
}	 
   });
  
  </script>
