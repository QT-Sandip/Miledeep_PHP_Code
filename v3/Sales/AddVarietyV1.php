<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


$PageTitleFolder="Master";
$PageTitle="Add Variety ";


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


$SqlBrands="SELECT * FROM `ScrmBrandsMaster` ";
$Brands=$iconn->query($SqlBrands);


$SqlPackingStyles="SELECT * FROM `AdminPackingStyles` ";
$PackingStyles=$iconn->query($SqlPackingStyles);


$SqlGrades="SELECT * FROM `ProductionGrades` ";
$Grades=$iconn->query($SqlGrades);


$SqlConsigneeDetails="SELECT * FROM `ScrmConsigneeDetails` ";
$ConsigneeDetails=$iconn->query($SqlConsigneeDetails);


extract($_POST);
extract($_GET);

	function checkEmpty($Value){
		if($Value==''){
		$Value='';	
		}
		else{
		$Value='_'.$Value;	
		}
		return $Value;
	}

if(isset($_POST['submit'])){
	
	
	$SqlVariety="SELECT * FROM `ScrmProductList` where ShortName='$ShortName'";
	$StmtVariety=$iconn->query($SqlVariety);
	$RowVariety=$StmtVariety->fetch_array();
	$ShortName=$RowVariety['ShortName'];
	$FullName=$RowVariety['FullName'];
	$ProductID=$RowVariety['ProductID'];


	

	
	$ProductFormm=checkEmpty($ShortName);
	$FreezingTypee=checkEmpty($FreezingType);
	$PreparationTypee=checkEmpty($PreparationType);
	$GlazePercc=checkEmpty($GlazePerc);
	$Glazee=checkEmpty($Glaze);
	$Treatmentt=checkEmpty($Treatment);
	
	
	// $ProductDescription=$Species.$ProductFormm.$FreezingTypee.$PreparationTypee.$GlazePercc.$Glazee.$Treatmentt; 
	
if($VarietyID==''){
	
	 $SqlCount="SELECT count(*) as Count FROM `ScrmPIVarietyList` WHERE TypeProduct='$TypeProduct' and ShortName='$ShortName'  and Glaze='$Glaze' and GlazePerc='$GlazePerc' and Treatment='$Treatment' and Grade='$Grade' and FreezingType='$FreezingType' and ProductID='$ProductID' and ProductDescription='$ProductDescription' and Species='$Species' and PreparationType='$PreparationType' and BrandName='$BrandName' and GlazeStatus='$GlazeStatus' and PackingStyle='$PackingStyle' and UltimateConsignee='$UltimateConsignee'and BuyerProdDescription='$BuyerProdDescription' and BuyerSUPC='$BuyerSUPC' ";
	$StmtCount=$iconn->query($SqlCount);
	$RowCount=$StmtCount->fetch_array();
	$Count=$RowCount['Count'];
	
	if($Count==0){
	

	 $SqlInsert="
	INSERT INTO `ScrmPIVarietyList`(`TypeProduct`,
	`ShortName`, `FullName`, `Category`, `Status`, `BuyerID`, `ItemCode`,
	`Glaze`, `GlazePerc`, `Treatment`, `Grade`, `FreezingType`, `ProductID`,
	`ProductDescription`,`Species`, `PreparationType`,
	`BrandName`, `GlazeStatus`, `PackingStyle`, `UltimateConsignee`,
	`BuyerProdDescription`, `BuyerSUPC`, `DeviSUPC`) VALUES 
	('$TypeProduct','$ShortName','$FullName'
	,'$Category','1','$BuyerID','$ItemCode','$Glaze',
	'$GlazePerc','$Treatment','$Grade','$FreezingType',
	'$ProductID','$ProductDescription','$Species'
	,'$PreparationType','$BrandName','$GlazeStatus','$PackingStyle'
	,'$UltimateConsignee','$BuyerProdDescription','$BuyerSUPC','$DeviSUPC')";
	}
	else{
		echo '<script>alert("Variety Already exists")</script>';
	}
	/*
	if($StmtInsert){
	
		header('Location: AddVariety.php');
	}
	*/
}

else{
	$oldVarietyData = legacyAuditFetchRowById('ScrmPIVarietyList', 'VarietyID', $VarietyID, 'Sales');
	 $SqlInsert="UPDATE `ScrmPIVarietyList` SET 
	 `TypeProduct`='$TypeProduct',
	 `ShortName`='$ShortName',
	 `FullName`='$FullName' , `Category`='$Category' ,`Glaze`='$Glaze',`GlazePerc`='$GlazePerc',
	 `Treatment`='$Treatment',`Grade`='$Grade',`FreezingType`='$FreezingType',
	 `ProductID`='$ProductID',`ProductDescription`='$ProductDescription',
	 `Species`='$Species',`PreparationType`='$PreparationType',
	 `BrandName`='$BrandName',`GlazeStatus`='$GlazeStatus'
,`PackingStyle`='$PackingStyle',`UltimateConsignee`='$UltimateConsignee',`BuyerProdDescription`='$BuyerProdDescription',`BuyerSUPC`='$BuyerSUPC',`DeviSUPC`='$DeviSUPC'
	 WHERE `VarietyID`='$VarietyID'";
	 
	 
}
	$StmtInsert=$iconn->query($SqlInsert);
	$NewVarietyID = $iconn->insert_id;
	
	if($StmtInsert){
		if($VarietyID==''){
			legacyAuditLogInsertById('ScrmPIVarietyList', 'VarietyID', $NewVarietyID, $_POST, [
				'ReferenceNo' => $NewVarietyID,
				'Remarks' => 'Variety created',
			], 'Sales');
		}else{
			legacyAuditLogUpdateById('ScrmPIVarietyList', 'VarietyID', $VarietyID, $oldVarietyData, $_POST, [
				'ReferenceNo' => $VarietyID,
				'Remarks' => 'Variety updated',
			], 'Sales');
		}
		header('Location: VarietyList');
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
	
	
				<div class="form-group col-sm-6" >
                  <label for="exampleInputEmail1">Devi SUPC<span style="color:red">*</span></label>
				<input  style="background: gray; color: white;"   required type="text" class="form-control" name='DeviSUPC' id="DeviSUPC" 
				  placeholder="" value="<?php echo $RowPIVariety['DeviSUPC'] ?>" >		
		
                </div>
										
			  <div class="form-group col-sm-6" >
                  <label for="exampleInputEmail1">Buyer SUPC</label>
				<input type="text" class="form-control" name='BuyerSUPC' id="BuyerSUPC" 
				  placeholder="Enter Buyer SUPC" value="<?php echo $RowPIVariety['BuyerSUPC'] ?>" >		
		 
                </div>
				
				 
				
				 </div>
				<HR></HR>
				
                <div class="row">
				
				
				<div class="form-group col-lg-3" >              									 
                <label for="exampleInputEmail1">Species<span style="color:red">*</span></label>
				<select required  class="form-control select2" name="Species" id="Species">
			     <option value=""> Select </option>
				   <?php while($RowSpecies=$StmtSpecies->fetch_array()) { ?>  
				
				<option <?php if($RowSpecies['ShortCode']==$RowPIVariety['Species']){ ?> selected=="selected"<?PHP } ?>
				Value="<?php echo $RowSpecies['ShortCode'];?>"><?php echo $RowSpecies['SpeciesName'];?></OPTION>
				
				<?php } ?>
				<select> 

                </div>

				

	            <div class="form-group col-lg-3" >              									 
                <label for="exampleInputEmail1">Product Form<span style="color:red">*</span></label>
				<select required class="form-control select2" name="ShortName" id="ShortName">
			    <option value=""> Select </option>
				   <?php while($RowVariety=$StmtVariety->fetch_array()) { ?>  
				
				<option <?php if($RowVariety['ShortName']==$RowPIVariety['ShortName']){ ?> selected=="selected"<?PHP } ?>
				
				Value="<?php echo $RowVariety['ShortName'];?>"><?php echo $RowVariety['ShortName'];?></OPTION>
				
				<?php } ?>
				</select> 

                </div>
				
				
				<div class="form-group col-lg-3" >              									 
                <label for="exampleInputEmail1">Freezing Type<span style="color:red">*</span></label>
				<select required class="form-control select2" name="FreezingType" id="FreezingType">
			     <option value=""> Select </option>
				   <?php while($RowFreezingType=$StmtFreezingType->fetch_array()) { 				?>  
				
				<option <?php if($RowFreezingType['ShortCode']==$RowPIVariety['FreezingType']){ ?> selected=="selected"
				<?PHP } ?>
				Value="<?php echo $RowFreezingType['ShortCode'];?>"><?php echo $RowFreezingType['FreezingTypeName'];?></OPTION>
				
				<?php } ?>
				</select> 

                </div>
				
				<div class="form-group col-lg-3" >              									 
                <label for="exampleInputEmail1">Preparation<span style="color:red">*</span></label>
				<select required class="form-control select2" name="PreparationType" id="PreparationType">
			    <option value=""> Select </option>
				   <?php while($RowPreparation=$StmtPreparation->fetch_array()) { ?>  
				
				<option <?php if($RowPreparation['ShortCode']==$RowPIVariety['PreparationType']){ ?> selected=="selected"
				<?PHP } ?>
				Value="<?php echo $RowPreparation['ShortCode'];?>"><?php echo $RowPreparation['PreparationName'];?></OPTION>
				
				<?php } ?>
				</select> 

                </div>
				
	            <div class="form-group col-lg-3" >              									 
                <label for="exampleInputEmail1">Type of Product<span style="color:red">*</span></label>
				<select required class="form-control select2" name="TypeProduct" id="TypeProduct">
				    <option value=""> Select</option>
					<option <?php if($RowPIVariety['TypeProduct']=='RAW') { ?> selected="selected" <?php } ?> value="RAW">RAW</option>
					<option <?php if($RowPIVariety['TypeProduct']=='COOKED') { ?> selected="selected" <?php } ?> value="COOKED">COOKED</option>				
					<option <?php if($RowPIVariety['TypeProduct']=='BLANCHED') { ?> selected="selected" <?php } ?> value="BLANCHED">BLANCHED</option>				
				</select>
                </div>
	
				
	            
	            <div class="form-group col-lg-3" >              									 
                <label for="exampleInputEmail1">Glaze Status<span style="color:red">*</span></label>
				<select required class="form-control select2" name="GlazeStatus" id="GlazeStatus">
				    <option value=""> Select</option>
					<option <?php if($RowPIVariety['GlazeStatus']=='Yes') { ?> selected="selected" <?php } ?> value="Yes">Yes</option>
					<option <?php if($RowPIVariety['GlazeStatus']=='No') { ?> selected="selected" <?php } ?> value="No">No</option>				
				</select>
                </div>
				
				
                 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Glaze %<span style="color:red">*</span></label>
				<input required type="text" class="form-control" name='GlazePerc' id="GlazePerc" 
				  placeholder="Enter Glaze(%)" value="<?php echo $RowPIVariety['GlazePerc'] ?>" 
				  <?php if($RowPIVariety['GlazeStatus']=='No' || $RowPIVariety['GlazeStatus']=='') { ?> disabled <?php } ?>>		
		
                </div>
				
	          <div class="form-group col-lg-3" >              									 
                <label for="exampleInputEmail1">Glaze Type<span style="color:red">*</span></label>
				<select required class="form-control select2" name="Glaze" id="Glaze">
			    <option value=""> Select </option>
				   <?php while($RowGlazeType=$StmtGlazeType->fetch_array()) { 				?>  
				
				<option <?php if($RowGlazeType['ShortCode']==$RowPIVariety['Glaze']){ ?> selected=="selected" <?PHP } ?>
				Value="<?php echo $RowGlazeType['ShortCode'];?>"><?php echo $RowGlazeType['GlazeType'];?></OPTION>
				
				<?php } ?>
				</select> 

                </div>
				
				
				 <div class="form-group col-lg-3" >              									 
                <label for="exampleInputEmail1">Treatment<span style="color:red">*</span></label>
				<select required class="form-control select2" name="Treatment" id="Treatment">
			     <option value=""> Select </option>
				   <?php while($RowTreatments=$StmtTreatments->fetch_array()) { 				?>  
				
				<option <?php if($RowTreatments['ShortCode']==$RowPIVariety['Treatment']){ ?> selected=="selected" <?PHP } ?>
				Value="<?php echo $RowTreatments['ShortCode'];?>"><?php echo $RowTreatments['TreatmentName'];?></OPTION>
				
				<?php } ?>
				</select> 

                </div>
				
				<div class="form-group col-lg-3" >              									 
                <label for="exampleInputEmail1">Brand<span style="color:red">*</span></label>
				<select required class="form-control select2" name="BrandName" id="BrandName">
			    <option value=""> Select </option>
				   <?php while($RowBrands=$Brands->fetch_array()) { ?>  
				
				<option <?php if($RowBrands['BrandID']==$RowPIVariety['BrandName']){ ?> selected=="selected"
				<?PHP } ?>
				Value="<?php echo $RowBrands['BrandID'];?>"><?php echo $RowBrands['BrandName'];?></OPTION>
				
				<?php } ?>
				</select> 

                </div>
				
				<div class="form-group col-lg-3" >              									 
                <label for="exampleInputEmail1">Grade<span style="color:red">*</span></label>
				<select required class="form-control select2" name="Grade" id="Grade">
			    <option value=""> Select </option>
				   <?php while($RowGrades=$Grades->fetch_array()) { ?>  
				
				<option <?php if($RowGrades['value']==$RowPIVariety['Grade']){ ?> selected=="selected"
				<?PHP } ?>
				Value="<?php echo $RowGrades['value'];?>"><?php echo $RowGrades['value'];?></OPTION>
				
				<?php } ?>
				</select> 

                </div>
				
				<div class="form-group col-lg-3" >              									 
                <label for="exampleInputEmail1">Packing Style<span style="color:red">*</span></label>
				<select required class="form-control select2" name="PackingStyle" id="PackingStyle">
			    <option value=""> Select </option>
				   <?php while($RowPackingStyles=$PackingStyles->fetch_array()) { ?>  
				
				<option <?php if($RowPackingStyles['PCKID']==$RowPIVariety['PackingStyle']){ ?> selected=="selected"
				<?PHP } ?>
				Value="<?php echo $RowPackingStyles['PCKID'];?>"><?php echo $RowPackingStyles['PackingStyle'];?></OPTION>
				
				<?php } ?>
				</select> 

                </div>
	
				<div class="form-group col-lg-3" >              									 
                <label for="exampleInputEmail1">Group Company</label>
				<select class="form-control select2" name="UltimateConsignee" id="UltimateConsignee">
			    <option value=""> Select </option>
				   <?php while($RowConsigneeDetails=$ConsigneeDetails->fetch_array()) { ?>  
				
				<option <?php if($RowConsigneeDetails['ConID']==$RowPIVariety['UltimateConsignee']){ ?> selected=="selected"
				<?PHP } ?>
				Value="<?php echo $RowConsigneeDetails['ConID'];?>"><?php echo $RowConsigneeDetails['ConsigneeName'];?></OPTION>
				
				<?php } ?>
				</select> 

                </div>
				
				
				
				 <div class="form-group col-sm-4" >
                  <label for="exampleInputEmail1">Product Description</label>
				<input style="background: gray; color: white;"  type="text" class="form-control" name='ProductDescription' id="ProductDescription" 
				  placeholder="" value="<?php echo $RowPIVariety['ProductDescription'] ?>" >		
		 
                </div>
				
				<div class="form-group col-lg-12" >
                  <label for="exampleInputEmail1">Buyer Product Description <span style="color:red">*</span></label>
				  
				  <textarea required name="BuyerProdDescription" class="form-control" rows="2" cols="50"
                    id="BuyerProdDescription"   
					placeholder="Enter Buyer Product Description" ><?php echo $RowPIVariety['BuyerProdDescription'] ?></textarea>
				 	</div>
				
				
                </div>
				
				
				
			  <div class="col-xs-12 box-footer"  align="right">
			  <a target="" href="VarietyList.php"
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
	$("#GlazePerc").val("0");
}else{	
	document.getElementById("GlazePerc").disabled=false;
	$("#GlazePerc").val("");
}
updateDescription();	 
   });
   
  
   
   
   
    $('#GlazePerc').change(function(){
	 updateDescription();
	}); 
	
	$('#Species').change(function(){
	 updateDescription();
	}); 	
	$('#ShortName').change(function(){
	 updateDescription();
	});
	$('#PreparationType').change(function(){
	 updateDescription();
	});
	
	$('#Glaze').change(function(){
	 updateDescription();
	});
	
	$('#Treatment').change(function(){
	 updateDescription();
	});
	
	
	$('#TypeProduct').change(function(){
		getDeviSupcCode();
	});
	
	$('#FreezingType').change(function(){
		getDeviSupcCode();
	});
	
	
	
	
	
  </script>
  <script>
  
  function getDeviSupcCode(){
	  
	  var TypeProduct=$('#TypeProduct').val(); 
	  var FreezingType=$('#FreezingType').val(); 
	  
	  if(TypeProduct!='' && FreezingType!=''){
		  
	  }else{
		  return;
	  }
	  
	  
	  $.ajax({
		  url:'Ajax/AjaxClientCode.php',
		  type:'POST',
		  data:{'TypeProduct':TypeProduct,'FreezingType':FreezingType},
		  success:function(data){
			  
			  
			  if(data=='InValid Code'){
			  alert(data);
			  $("#DeviSUPC").val("");
			  }else{
			  $("#DeviSUPC").val(data);
			  }
			  //$("#BuyerSUPC").val(data);
			  
			}
			});		

	  
  }
  
  
  
  
  function updateDescription(){
	  var Species= $('#Species').val(); 
	  var ShortName= $('#ShortName').val(); 
	  var PreparationType= $('#PreparationType').val(); 
	  var Glaze= $('#Glaze').val(); 
	  var Treatment= $('#Treatment').val(); 
	  var GlazePerc= $('#GlazePerc').val(); 
	  var Desc=Species+'-'+ShortName+'-'+PreparationType;
	  var Desc=Species+'-'+ShortName+'-'+PreparationType;
	  if(GlazePerc>0){
		var Desc=Species+'-'+ShortName+'-'+PreparationType+'-'+GlazePerc+'%'+'-'+Glaze+'-'+Treatment;
	  }else{
	  var Desc=Species+'-'+ShortName+'-'+PreparationType+'-'+Glaze+'-'+Treatment;
	  }
	  	$("#ProductDescription").val(Desc);
  }
  </script>
