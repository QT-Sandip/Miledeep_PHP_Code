<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$PageTitle="Edit Stock";
$PageTitleFolder="Cold Store";
$UserID=$_SESSION['UserID']; 
//Query Parms


 $StockId= $_GET['id'];
 $BarCodeType=$_GET['TYPE'];
 $BarCodeID=$_GET['BID'];
   $UnitID=$_SESSION['UnitID'];  

//$SqlVarietyList="SELECT * FROM `ScrmPIVarietyList` ORDER by ABS(ShortName)";
//$StmtVarietyList=$iconn->query($SqlVarietyList);
//Page Constants
 
$TodayDate=date('Y-m-d');


if($BarCodeType == ''){ 
	$BarCodeType = 1;
}



 $GeneralOptionsQuery="SELECT * FROM `AdminGeneralOptions` WHERE  DeptID='CS' or DeptID='7' ORDER BY SortOrder asc"; 
$GeneralOptionsExec=$iconn->query($GeneralOptionsQuery);
$ARY_OPTIONS=array();
WHILE($ROW=$GeneralOptionsExec->FETCH_ARRAY()){
	$ARY_OPTIONS[]=$ROW;
}
$SqlShifts="SELECT * FROM `AdminShiftDetails` order by ShiftID asc";
$StmtShifts=$iconn->query($SqlShifts);
//$GetPoData="select * from CsPoData where PoStatus = 0 order by PoID  DESC  limit 100";
$GetPoData="SELECT * FROM `ScrmPoData` as CPD  
 left join ScrmShipmentDetailsBreif as SDB on CPD.PoID=SDB.PiID

  WHERE  (TransportedDate='' or TransportedDate is null) order by PoID DESC"; //ProductionStartedDate!=''and
$StmtPoData=$iconn->query($GetPoData);

 $SqlPackingStyles="SELECT * FROM `AdminPackingStyles` ORDER by Uom,ABS(PackingStyle)"; 
$StmtPacking=$iconn->query($SqlPackingStyles);

$SqlStockData="SELECT * from CsStockData where StockID ='$StockId'";
$StmtStockData=$iconn->query($SqlStockData);
$RowStockData=$StmtStockData->fetch_array();

$SqlStock="SELECT * from CsStockData where BarCodeID ='$BarCodeID'";
$StmtStock=$iconn->query($SqlStock);
 $COUNT=$StmtStock->num_rows;

$SqlCsTenant="SELECT * FROM `CsTenant` ORDER by TenantStatus desc,TenantName asc";
$StmtCsTenant=$iconn->query($SqlCsTenant);
extract($_POST);
extract($_GET);

$SqlProductList = "SELECT * FROM `ScrmProductList` ORDER by ABS(ShortName)"; 
$StmtProductList = $iconn->query($SqlProductList);

$SqlGrades = "SELECT * FROM `ProductionGrades`  ORDER by ABS(value)";
$StmtGrades = $iconn->query($SqlGrades);

//ECHO $SqlProductList; EXIT;
if(isset($_POST['submit'])){ 

$urltxt='TYPE='.$TYPE.'&BID='.$BarCodeID;

 if($PoGradeId!=0){
 $SqlVarietyId="SELECT * FROM `ScrmPoGradeData` WHERE ID='$PoGradeId'";
$StmtVarietyId=$iconn->query($SqlVarietyId);
$RowVarietyId=$StmtVarietyId->fetch_array();
$VarietyID=$RowVarietyId['VarietyID'];
}

	$query='';
	if($COUNT == 1){
		$query="`UnitID`='$UnitID',`RmLotID`='$RmLotID',";
	}

	if($RowStockData['ActualMcs']==$RowStockData['AvalMcs']){
			 $SqlUpdate= "UPDATE `CsStockData` SET `AvalMcs`='$AvalMcs',`ActualMcs`='$AvalMcs',`LooseBags`='$LooseBags',
	 `PackingTypeID`='$PackingTypeID',`Grade`='$GradeID',`PoGradeId`='$PoGradeId',`FreezingType`='$FreezingTypeID',
	 `VarietyID`='$VarietyID',`Galze`='$Galze',`SockingType`='$SockingType',`Salt`='$Salt',ProdDayCode='$ProdDayCode',
	 `ProductionDate`='$ProductionDate',`PoID`='$PoID',$query
	 `ProductionTypeID`='$ProductionTypeID',`ProductCategoryID`='$ProductCategoryID',`ShiftID`='$ShiftID',
	 `PackingStyleID`='$PackingStyleID',`Remarks`='$Remarks',`StockHolderID`='$StockHolderID',`AbStatus`='$AbStatus' WHERE `StockID`='$StockId'";
	}else{
		echo '<script>alert("No Access To Edit")</script>';
	}
	
	 $StmtUpdate=$iconn->query($SqlUpdate);
	
	if($StmtUpdate){		
	header('Location: CreateStock.php?'.$urltxt);
	}else{
		// echo ("Error description: " . mysqli_error($iconn));
		echo '<script>alert("No Access To Edit")</script>';
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

    <body data-sidebar="dark" > <!--class="vertical-collpsed"-->

	
	 <?php		include ($IncludesPath."PreLoader.php"); ?>

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
								
								<form role="form"  id="PalletForm" method="post">
                                    <div class="card-body">
									
										<div class="row">

			<!-- <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Station</label>
				<select class="form-control select2" name="UnitID" id="UnitID">
			    <?php /*foreach($ARY_OPTIONS as $value){
					if($value['OptionType']=='CS_STATIONS'){
						$OPTIONS= $value['Name'];
						$VALUE= $value['OptID'];
				?>
			 <option <?php if($UNIT==$VALUE){ ?> selected="selected" <?php }?>
			 value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
					
					<?php  }}*/ ?>
				<select>
			</div>-->
			
			<div class="form-group col-sm-3">  
			<label for="exampleInputPassword1">Transaction Date<span style="color:red">*</span></label>
                  <input required  type="date" class="form-control" name="ProductionDate" id="ProductionDate"
				  max="<?php echo $TodayDate; ?>" value="<?php echo $RowStockData['ProductionDate']; ?>" placeholder="Enter Transaction Date" >
            </div>

			<div class="form-group col-sm-3" >              
					 
					 
			<label for="exampleInputEmail1">Group<span style="color:red">*</span></label>
                   <select class="form-control select2" name="ProductionTypeID" id="ProductionTypeID">
					 <?php foreach($ARY_OPTIONS as $value){
					if($value['OptionType']=='PRODUCT_GROUP'){
						$OPTIONS= $value['Name'];
						$VALUE= $value['Name']; ?>
			 
					<option  <?php if($RowStockData['ProductionTypeID']==$VALUE){ ?> selected="selected" <?php }?>
					
					value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
					<?php  }}?>
		
		
				<select>
            </div>

			 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Station<span style="color:red">*</span></label>
				<select  class="form-control select2" name="StockHolderID" id="StockHolderID">
			    <?php while($RowCsTenant=$StmtCsTenant->fetch_array()){ ?>

			 <option  <?php if($RowStockData['StockHolderID']==$RowCsTenant['TenantID']){?> selected="selected" <?php }?>
			 value="<?php echo $RowCsTenant['TenantID'] ?>"><?php echo $RowCsTenant['TenantName'] ?></option>
					
					<?php  }?>
				</select>
			</div>

			<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Shift<span style="color:red">*</span></label>			  
				<select class="form-control select2" name="ShiftID" id="ShiftID">
					<?php while($RowShifts=$StmtShifts->fetch_array()){
					//if($value['OptionType']=='Shifts'){
						$OPTIONS= $RowShifts['ShiftName'];
						$VALUE= $RowShifts['ShiftID']; ?>
			 
					<option <?php if($RowStockData['ShiftID']==$VALUE){ ?> selected="selected" <?php }?>
					value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
					<?php  }?>
					
					
		
				<select>
			</div>		

			
			<div class="form-group col-sm-3">
												<label for="exampleInputPassword1">Production Code<span
														style="color:red">*</span></label>
												<input required type="text" class="form-control" name="ProdDayCode"
													id="ProdDayCode" placeholder="Enter Production Code" value="<?php echo $RowStockData['ProdDayCode'] ?>">
											</div>

											<div class="form-group col-sm-3">
												<label for="exampleInputPassword1">Batch Code<span
														style="color:red">*</span></label>
												<input required type="text" class="form-control" name="RmLotID"
													id="RmLotID" placeholder="Enter Batch Code" value="<?php echo $RowStockData['RmLotID'] ?>">
											</div>
			
              

			
			
				

				
			
				
				
			<div class="form-group col-sm-3">  
                  <label for="exampleInputPassword1">Po Number<span style="color:red">*</span></label>
                   <select class="form-control select2" name="PoID" id="PoID">
				  <option value="">Select PO</option>
				   <?php while ($RowPoData=$StmtPoData->fetch_array()){ ?>
				     <option <?php if($RowStockData['PoID']==$RowPoData['PoID']){ ?> selected="selected" <?php }?>
					 
					 value="<?php echo $RowPoData['PoID'] ?>">
					 <?php $LotNo='-'. $RowPoData['LotNo'] ? :  '' ;
					 	 echo $RowPoData['PoNo'].$LotNo ?></option>
				   
				 <?php  }?>	  
				  </select>
            </div>
			
			

				
                <div class="form-group col-sm-3" >
				  
                  <label for="exampleInputEmail1">Variety<span style="color:red">*</span></label>
				  <input hidden type="text" id="PoGradeId" name="PoGradeId"  class="form-control hidden" value="<?php echo $RowStockData['PoGradeId'] ?> ">
                 <select required class="form-control select2" name="VarietyID" id="VarietyID">
				 				  <option value="">Select Variety</option>
					<?php while ($RowProductData=$StmtProductList->fetch_array()){ ?>
				     <option <?php if($RowStockData['VarietyID']==$RowProductData['ProductID']){ ?> selected="selected" <?php }?>
					 value="<?php echo $RowProductData['ProductID'] ?>">
					 <?php echo $RowProductData['ShortName']?></option>
				   
				 <?php  }?>	  
				</select>
            </div>

			<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Packing Type<span style="color:red">*</span></label>
                  <select class="form-control select2" name="PackingTypeID" id="PackingTypeID">
					 <?php foreach($ARY_OPTIONS as $value){
					if($value['OptionType']=='PACKING_TYPE'){
						$OPTIONS= $value['Name'];
						$VALUE= $value['Name']; ?>
			 
					<option <?php if($RowStockData['PackingTypeID']==$VALUE){ ?> selected="selected" <?php }?>
					
					value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
					<?php  }}?>		
				<select>
            </div>

			<div class="form-group col-sm-3">  
                  <label for="exampleInputPassword1">Packing Style<span style="color:red">*</span></label>
                   <select required class="form-control select2" name="PackingStyleID" id="PackingStyleID">	
				   <option value="">Select </option>

				   
				   <?php while ($RowPAcking=$StmtPacking->fetch_array()){ ?>
				     <option  
					 <?php if($RowStockData['PackingStyleID']==$RowPAcking['PCKID']){ ?> selected="selected" <?php }?> 
					 value="<?php echo $RowPAcking['PCKID'] ?>">
					 <?php echo $RowPAcking['PackingStyle'] ?></option>
				   
				 <?php  }?>
		  
				  </select>
            </div>
				
		    <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Grade<span style="color:red">*</span></label>
               <select required class="form-control select2" name="GradeID" id="GradeID">
			   				 				  <option value="">Select </option>


											  <?php while ($RowGrades = $StmtGrades->fetch_array()){ ?>
			 
					<option <?php if($RowStockData['Grade']==$RowGrades['value']){ ?> selected="selected" <?php }?>
					value="<?php echo $RowGrades['value'] ?>"><?php echo $RowGrades['Options']?></option>
					<?php  }?>
		

					
														

														
				<select>
            </div>
				
				
			
			
			  		   
			  
			<div class="form-group col-sm-3" >                
					 
					 
               <label for="exampleInputEmail1">Freezing Type</label>
               <select required class="form-control select2" name="FreezingTypeID" id="FreezingTypeID">
			   <option value="">Select </option>

					 <?php foreach($ARY_OPTIONS as $value){
					if($value['OptionType']=='FREEZING'){
						$OPTIONS= $value['Name'];
						$VALUE= $value['Name']; ?>
			 
					<option 	<?php if($RowStockData['FreezingType']==$OPTIONS){ ?> selected="selected" <?php }?> 

					value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
					<?php  }}?>
		
				<select>
            </div>
				

			<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Glaze(%)</label>
                  <input required type="number" class="form-control" 
				  name='Galze' id="Galze"  value="<?php echo $RowStockData['Galze'] ?>" 
				  placeholder="Enter Glaze %">
            </div>
				
			<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Soaking</label>
				 <select required class="form-control select2" name="SockingTypeID" id="SockingTypeID">
					 <?php foreach($ARY_OPTIONS as $value){
					if($value['OptionType']=='Soaking'){
						$OPTIONS= $value['Name'];
						$VALUE= $value['Name']; ?>
			 
					<option <?php if($RowStockData['SockingType']==$OPTIONS){ ?> selected="selected" <?php }?> 
					value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
					<?php  }}?>
		
				<select>
			</div>
	   
			<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Salt</label>
                  <input required type="number" class="form-control" 
				  name='Salt' id="Salt" value="<?php echo $RowStockData['Salt'] ?>"
				  placeholder="Enter Salt %">
             </div>
               
			<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Mcs</label>
                  <input required type="number" class="form-control" 
				  name='AvalMcs' id="AvalMcs" value="<?php echo $RowStockData['AvalMcs'] ?>"
				  placeholder="Enter No Of Mcs">
            </div>
				
			<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Loose Bags</label>
                  <input required type="number" class="form-control" 
				  name='LooseBags' id="LooseBags"  value="<?php echo $RowStockData['LooseBags'] ?>"
				  placeholder="Enter No Of Loose Bags">
            </div>
				
				
			   
		   	
			
			   
			<div class="form-group col-sm-3" >              
				<label for="exampleInputEmail1">Product Category</label>
               <select class="form-control select2" name="ProductCategoryID" id="ProductCategoryID">
					<?php foreach($ARY_OPTIONS as $value){
					if($value['OptionType']=='PRODUCT_CATEGORY'){
						$OPTIONS= $value['Name'];
						$VALUE= $value['Name']; ?>
			 
					<option <?php if($RowStockData['ProductCategoryID']==$VALUE){ ?> selected="selected" <?php }?>
					value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
					<?php  }}?>
					
					
		
				<select>
            </div>	
              <div class="form-group col-sm-3" >              
				<label for="exampleInputEmail1">Ab Status</label>
               <select class="form-control select2" name="AbStatus" id="AbStatus">
					<?php foreach($ARY_OPTIONS as $value){
					if($value['OptionType']=='AbStatus'){
						$OPTIONS= $value['Name'];
						$VALUE= $value['Name']; ?>
			 
					<option <?php if($RowStockData['AbStatus']==$VALUE){ ?> selected="selected" <?php }?>
					value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
					<?php  }}?>
					
					
		
				<select>
            </div>	
        
           		
				
			<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Remarks</label>
                  <input  type="text" class="form-control" name='Remarks' 
				  id="Remarks" placeholder="Enter Remarks" value="<?php echo $RowStockData['Remarks'] ?>">
            </div>
			  
				
            
				
				
				</div>
				
              <!-- /.box-body -->


	<div class="col-xs-12 box-footer"  align="right">
	
	
				<button type="submit"  name='submit' class="btn btn-primary">Update</button>
			
				 </div>





				</div>
              <!-- /.box-body -->


            </form>
			
			


             </div> <!-- end row -->
    
	</div> <!-- end card body-->
	
	
  
  
    </section>
	
	</div>

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
<Script>

	$('document').ready(function () {

		changePoDropDowns(true);
		var VarietyID='<?php echo $RowStockData['PoGradeId'] ?>';
		if(VarietyID==0){
			var VarietyID='<?php echo $RowStockData['VarietyID'] ?>';
		}

  
  $('#PoID').change(function(){

changePoDropDowns(false);
  });


  
  function changePoDropDowns(onload){
	
			var PoId = $('#PoID').val();
			if (PoId != '') {
				document.getElementById("GradeID").disabled = true;
				document.getElementById("PackingStyleID").disabled = true;
				document.getElementById("FreezingTypeID").disabled = true;
				document.getElementById("Galze").disabled = true;
				document.getElementById("SockingTypeID").disabled = true;
				document.getElementById("Salt").disabled = true;
			} else {
				document.getElementById("GradeID").disabled = false;
				document.getElementById("PackingStyleID").disabled = false;
				document.getElementById("FreezingTypeID").disabled = false;
				document.getElementById("Galze").disabled = false;
				document.getElementById("SockingTypeID").disabled = false;
				document.getElementById("Salt").disabled = false;
			}
			$.ajax({
				type: 'POST',
				url: 'Ajax/AjaxCreateStockOptions.php',
				data: { 'Type': 'FORM', 'PoId': PoId },
				success: function (response) {
					$("#VarietyID").html(response);
					if(onload){	
		var PoGradeId= $('#PoGradeId').val();			
			$("#VarietyID").val(VarietyID)			
		}

		
				}
			});
		}


		$('#PackingTypeID').change(function () {
			var PackType = $('#PackingTypeID').val();
			var Variety = $('#VarietyID').val();
			//alert(PackType);

			if (PackType == 'DUMMY') {
				document.getElementById("PackingStyleID").disabled = false;
				document.getElementById("PoID").required = false;
			} else {
				document.getElementById("PackingStyleID").disabled = true;
				document.getElementById("PoID").required = true;
			}

			if (Variety == '') {
				return;
			} else {
				getGradeBreakUpData();
			}
		});


		$('#VarietyID').change(function () {

			getGradeBreakUpData();
		});


		$('#SOAKING').change(function () {
			var SOAKING = $('#SOAKING').val();


			if (SOAKING == 'Salt') {
				$('#Salt').val('');
				document.getElementById("Salt").disabled = false;
			} else {
				$('#Salt').val(0);
				document.getElementById("Salt").disabled = true;
			}

		});

		$('#Salt').val(0);
		document.getElementById("Salt").disabled = true;

		var LOT_NUM = '<?php echo $LOT_NUM ?>';


		if (LOT_NUM != '') {

			document.getElementById("UnitID").disabled = true;
		}


		$("#PalletForm").submit(function (event) {
			document.getElementById("GradeID").disabled = false;
			document.getElementById("PackingStyleID").disabled = false;
			document.getElementById("FreezingTypeID").disabled = false;
			document.getElementById("Galze").disabled = false;
			document.getElementById("SockingTypeID").disabled = false;
			document.getElementById("Salt").disabled = false;
		});



	});

</script>
<script>
	function getGradeBreakUpData() {
		var PoId = $('#PoID').val();
		var Variety = $('#VarietyID').val();
		var PackType = $('#PackingTypeID').val();

		
		
		if (PoId == '') {
			//$("#VarietyInput").val(Variety);
			var Variety=$('#VarietyID').val();
			var BreakUpID=0;
			//return;
		}else{
			var Variety=0;
			var BreakUpID=$('#VarietyID').val();
		}
		$.ajax({
			type: 'POST',
			url: 'Ajax/AjaxCreateStockOptions.php',
			dataType: 'json',
			data: { 'Type': 'GRADES', 'PoId': PoId, 'Variety': Variety,'BreakUpID':BreakUpID },
			success: function (response) {
				var myJSON = JSON.parse(JSON.stringify(response));

				if (PackType == 'DUMMY') {
					document.getElementById('GradeID').disabled = true;
					document.getElementById('GradeID').readOnly = true;
					document.getElementById("PackingStyleID").disabled = false;
				} else {
					document.getElementById("PackingStyleID").disabled = true;
					document.getElementById('GradeID').disabled = true;
					document.getElementById('GradeID').readOnly = true;
				}

				document.getElementById("GradeID").disabled = true;
				document.getElementById("PackingStyleID").disabled = false;
				document.getElementById("FreezingTypeID").disabled = true;
				document.getElementById("Galze").readOnly = true;
				document.getElementById("SockingTypeID").disabled = true;
				document.getElementById("Salt").disabled = true;

				alert(myJSON['GradeName']);

				$("#PoGradeId").val(myJSON['PoGradeID']);
				$("#GradeID").val(myJSON['GradeName']).change();
				$("#PackingStyleID").val(myJSON['PackingStyleID']).change();
				$("#FreezingTypeID").val(myJSON['FreezingTypeID']).change();

				$("#Galze").val(myJSON['GlazePerc']);
				$("#SOAKING").val(myJSON['SockingTypeID']);
				$("#SALT").val(myJSON['Salt']);

			}
		});

	}
</script>
