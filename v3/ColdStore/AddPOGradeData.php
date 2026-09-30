<?php

//$Status=$_SESSION['Status'];


$PageTitleFolder="ColdStore";
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

 
extract($_POST);
extract($_GET); 

$UserID=$_SESSION['UserID'];
$PoID=$_GET['id'];


  
$SqlPoNumber="SELECT PoNumber FROM `CsPoData` WHERE PoID='$PoID'";
$StmtPoNumber=$iconn->query($SqlPoNumber);
$RowPoNumber=$StmtPoNumber->fetch_array();
 

$PageTitle="Gradewise data - "  . $RowPoNumber['PoNumber']  ;


$SqlPoGradeData="
Select CGV.Name as VarietyName,CGG.Name as GradeName,CGF.Name as FreezingType,
CGS.Name as SockingType,CGGT.Name as GlazeType,CPS.PackingStyle,CPGD.Grade,CPGD.PoGradeID, CPGD.RequiredCases, CPGD.FreezingType, CPGD.PoID, CPGD.PackingStyleID, CPGD.GlazeType, CPGD.VarietyID, CPGD.GlazePerc, CPGD.SockingType, CPGD.Brand, CPGD.Salt
from CsPoGradeData AS CPGD
LEFT JOIN CsGeneralOptions AS CGV ON CGV.CsOptID=CPGD.VarietyID
LEFT JOIN CsGeneralOptions AS CGG ON CGG.CsOptID=CPGD.Grade
LEFT JOIN CsGeneralOptions AS CGF ON CGF.CsOptID=CPGD.FreezingType
LEFT JOIN CsGeneralOptions AS CGS ON CGS.CsOptID=CPGD.SockingType
LEFT JOIN CsGeneralOptions AS CGGT ON CGGT.CsOptID=CPGD.GlazeType
LEFT JOIN CsPackingStyles  AS CPS  ON CPS.PckID=CPGD.PackingStyleID
where PoID='$PoID'";
 
// echo $SqlPoGradeData;
$StmtPoGradeData=$iconn->query($SqlPoGradeData);
//$RowPoGradeData=$StmtPoGradeData->fetch_array();


 $GeneralOptionsQuery="SELECT * FROM `CsGeneralOptions` ORDER BY SortOrder asc";
$GeneralOptionsExec=$iconn->query($GeneralOptionsQuery);
$ArratOptions=array();
WHILE($ROW=$GeneralOptionsExec->FETCH_ARRAY()){
	$ArratOptions[]=$ROW;
}



 $SqlPackingStyles="Select * from CsPackingStyles "; 
 $StmtPackingStyles=$iconn->query($SqlPackingStyles);
 
 
	$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
	$CurrentDateTime=$dateTime->format("Y-m-d  H:i:s");
	 

if(isset($_POST['submit'])){
	
	Global $PoID;
	Global $CurrentDateTime;
	Global $UserID;
 	
   $InsertPOGradeData="INSERT INTO `CsPoGradeData`(`Grade`, `RequiredCases`, `FreezingType`, `PoID`, `PackingStyleID`, `GlazeType`, `VarietyID`, `GlazePerc`, `SockingType`, `Brand`, `Salt`, `Usd`, `PostedUserID`, `PostedDate`) 
   VALUES('$Grade','$RequiredCases','$FreezingType','$PoID','$PackingStyleID','$GlazeType','$VarietyID','$GlazePerc','$SockingType','$Brand','$Salt','$Usd','$UserID ','$CurrentDateTime')"; 

   $ExePOGradeData=$iconn->query($InsertPOGradeData);
	
	if($ExePOGradeData){ 
				
		header('Location: AddPOGradeData.php?id='.$PoID);
		
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

	
	 <?php	include ($IncludesPath."PreLoader.php"); ?>

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
								
								<form role="form" method="post">
                                    <div class="card-body">
									
										<div class="row">
			
				

				
				
			<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Variety</label>
				   <select class="form-control select2" name="VarietyID" id="VarietyID">
			        <?php foreach($ArratOptions as $value){
					if($value['OptionType']=='PRODUCT_VARIETY'){
						$OptionName= $value['Name'];
						$OptionValue= $value['CsOptID'];
				     ?>
			      <option <?php if($UNIT==$OptionValue){ ?> selected="selected" <?php }?>
			        value="<?php echo $OptionValue ?>"><?php echo $OptionName ?></option>
					
					<?php  }}?>
				   <select>
			</div>
			
			<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Grade</label>
				   <select class="form-control select2" name="Grade" id="Grade">
			        <?php foreach($ArratOptions as $value){
					if($value['OptionType']=='GRADES'){
						$OptionName= $value['Name'];
						$OptionValue= $value['CsOptID'];
				     ?>
			      <option <?php if($UNIT==$OptionValue){ ?> selected="selected" <?php }?>
			        value="<?php echo $OptionValue ?>"><?php echo $OptionName ?></option>
					
					<?php  }}?>
				   <select>
			</div>
			


                <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Packing Style</label>
                   <select required type="text" class="form-control select2" name='PackingStyleID' id="PackingStyleID">

				    <?php while ($RowPackingStyles=$StmtPackingStyles->fetch_array()){ ?>
				     <option 
					            
					 value="<?php echo $RowPackingStyles['PckID'] ?>">
					 <?php echo $RowPackingStyles['PackingStyle'] ?></option>
				   
				 <?php  }?>
                 </select>
               
                </div>

			    <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Freezing Type</label>
				   <select class="form-control select2" name="FreezingType" id="FreezingType">
			        <?php foreach($ArratOptions as $value){
					if($value['OptionType']=='FREEZING'){
						$OptionName= $value['Name'];
						$OptionValue= $value['CsOptID'];
				     ?>
			      <option <?php if($UNIT==$OptionValue){ ?> selected="selected" <?php }?>
			        value="<?php echo $OptionValue ?>"><?php echo $OptionName ?></option>
					
					<?php  }}?>
				   <select>
			    </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Soaking</label>
				   <select class="form-control select2" name="SockingType" id="SockingType">
			        <?php foreach($ArratOptions as $value){
					if($value['OptionType']=='Soaking'){
						$OptionName= $value['Name'];
						$OptionValue= $value['CsOptID'];
				     ?>
			      <option <?php if($UNIT==$OptionValue){ ?> selected="selected" <?php }?>
			        value="<?php echo $OptionValue ?>"><?php echo $OptionName ?></option>
					
					<?php  }}?>
				   <select>
			    </div>
				
				

                <div class="form-group  col-lg-3">
				 <label for="exampleInputEmail1"> Glaze(%)</label>
                    <input class="form-control" type="text"id="GlazePerc" name="GlazePerc" placeholder="Enter Glaze(%)" value="" required>                 
                 </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Glaze</label>
				   <select class="form-control select2" name="GlazeType" id="GlazeType">
			        <?php foreach($ArratOptions as $value){
					if($value['OptionType']=='GlazeType'){
						$OptionName= $value['Name'];
						$OptionValue= $value['CsOptID'];
				     ?>
			      <option <?php if($UNIT==$OptionValue){ ?> selected="selected" <?php }?>
			        value="<?php echo $OptionValue ?>"><?php echo $OptionName ?></option>
					
					<?php  }}?>
				   <select>
			    </div>
				

				
				<div class="form-group  col-lg-3">
				 <label for="exampleInputEmail1"> Salt</label>
                    <input class="form-control" type="text" id="Salt" name="Salt"
					placeholder="Enter Salt" value="" required>                 
                 </div>
				 
				 <div class="form-group  col-lg-3">
				 <label for="exampleInputEmail1"> Brand</label>
                    <input class="form-control" type="text" id="Brand" name="Brand"
					placeholder="Enter Brand" value="" required>                 
                 </div>
				 
				 <div class="form-group  col-lg-3">
				 <label for="exampleInputEmail1"> Mcs</label>
                    <input class="form-control" type="text"id="RequiredCases" name="RequiredCases"
					placeholder="Enter MCS" value="" required>                 
                 </div>
				
			  
        <!-- end row -->
					</div>
			<div class="col-xs-12 box-footer"  align="right">
                <button  type="submit"  name='submit'  class="btn btn-primary waves-effect waves-light">
				<i class= 'ri-add-fill align-middle ml-2'>
				 </i>
			   Add</button> 
              </div>
			  
			  </form>
	
			  
			   <table id="example2" class="table table-bordered table-hover">
              

    <thead>

      <tr>
	  
	  <th>S.No</th>
	  <th>Variety</th>
	  <th>Grade</th>
	  <th>Packing Style</th>
	  <th>Freezing </th>
	  <th>Glaze</th>
	  <th>Soaking</th>
	  <th>Salt</th>
	  <th>Brand</th>
	  <th>Mcs</th>
	  <th>Actions</th>
	 
      </tr>

    </thead>

    <tbody>
	<?php
$SNo=1;
   while($RowPoGradeData=$StmtPoGradeData->fetch_array()){
	   
	   echo $RowPoGradeData['ID'];
	 // exit; 
	   $VarietyName=$RowPoGradeData['VarietyName'];
	   $GradeName=$RowPoGradeData['GradeName'];
	   $FreezingType=$RowPoGradeData['FreezingType'];
	   $SockingType=$RowPoGradeData['SockingType'];
	   $GlazeType=$RowPoGradeData['GlazeType'];
	   $PackingStyle=$RowPoGradeData['PackingStyle'];
	   $Salt=$RowPoGradeData['Salt'];
	   $Brand=$RowPoGradeData['Brand'];
	   $Mcs=$RowPoGradeData['RequiredCases'];
	   
	  /*$SqlDefectName="SELECT * FROM `RmDefectsList` WHERE DefectID='$DefectID'";
      $StmtDefectName=$iconn->query($SqlDefectName);
      $RowDefectName=$StmtDefectName->fetch_array();*/

	?>
	
	<tr> 
			
	<td id="<?php  echo $RowPoGradeData['PoGradeID'];?>" > <?php echo $SNo; ?></td>
	<td id="<?php  echo $RowPoGradeData['PoGradeID'];?>" > <?php echo $VarietyName; ?></td>
	<td id="<?php  echo $RowPoGradeData['PoGradeID'];?>" > <?php echo $GradeName; ?></td>
	<td id="<?php  echo $RowPoGradeData['PoGradeID'];?>" > <?php echo $PackingStyle; ?></td>
	<td id="<?php  echo $RowPoGradeData['PoGradeID'];?>" > <?php echo $FreezingType; ?></td>
	<td id="<?php  echo $RowPoGradeData['PoGradeID'];?>" > <?php echo $GlazeType; ?></td>
	<td id="<?php  echo $RowPoGradeData['PoGradeID'];?>" > <?php echo $SockingType; ?></td>
	<td id="<?php  echo $RowPoGradeData['PoGradeID'];?>" > <?php echo $Salt; ?></td>
	<td id="<?php  echo $RowPoGradeData['PoGradeID'];?>" > <?php echo $Brand; ?></td>
	<td id="<?php  echo $RowPoGradeData['PoGradeID'];?>" > <?php echo $Mcs; ?></td>
	
 
	<td>
	<button class="btn btn-link text-danger Delete" data-id="<?php echo $RowPoGradeData['PoGradeID']; ?>" data-toggle="tooltip" data-placement="top" title="" value=""
							data-original-title="Delete"><i class="mdi mdi-trash-can font-size-18"></i></button> 						
	</td>		
        </tr>

	  <?php 
$SNo++;
   
   }
	  ?>

     </tbody>

  </table>
			  
									</div>
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
      
  
       
    </body>
</html>	
<script>
 
  $(document).on('click', '.Delete', function(e){
e.preventDefault();
	  var ODId =$(this).data('id');
	  var Type = 'DeleteOD';
	  
     alert(ODId);
     var r = confirm("Are You Sure You Want To Delete?");
  if (r == true) {
   
	$.ajax({
	
	type: 'POST',
   url: 'Ajax/AjDeleteDetails.php',
    data: {'ODId': ODId,'Type': Type},
    
    success: function(response){
		var obj = JSON.parse(JSON.stringify(response));

		//showData(data);
		 if(obj.error == null){
			 alert("Deleted  Successfully");
			//alert(obj.Msg);
         location.reload();
		showData(data);
		 }else{
			 			 alert(obj.error);

		 }

	 
     }
  });
  }
else{}
    
  });
 

</script>