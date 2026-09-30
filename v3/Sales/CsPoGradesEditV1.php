<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


$PageTitleFolder="Sales";
$PageTitle="Grade Wise Data";

$GradesId=$_GET['id'];
$PoID=$_GET['PoID'];

$SqlPI="SELECT * FROM `ScrmPIData`   WHERE PoID='$PoID'";

$StmtPI=$iconn->query($SqlPI);
$RowPI=$StmtPI->fetch_array();
$PoRefId=$RowPI['PoRefId'];


$SqlPIRef="SELECT * FROM `ScrmPIData`   WHERE PoRefId='$PoRefId'";
$StmtPIRef=$iconn->query($SqlPIRef);

$RowCurrency=$StmtPIRef->fetch_array();
$UnitCurrency=$RowCurrency['UnitCurrency'];

$StmtPIRef=$iconn->query($SqlPIRef);

$SQL_OPTIONS="SELECT * FROM `OPTIONS` WHERE  DEPT='CS' order by ABS(OPTIONS) asc";
$STMT_OPTIONS=$iconn->query($SQL_OPTIONS);
$ARY_OPTIONS=array();
WHILE($ROW=$STMT_OPTIONS->FETCH_ARRAY()){
	$ARY_OPTIONS[]=$ROW;
}


  $SQL_CODE_LIST="SELECT 
  PGD.ID,Grade,GlazeType,InvId,Mcs,FreezingType,PackingStyleID,
  Variety,VarietyID,GlazePerc,Treatment,Brand,Price,BAP,BuyerItemCode,Description
  FROM `ScrmPoGradeData` as PGD 
left Join AdminPackingStyles as CPS on
 PGD.PackingStyleID=CPS.PCKID WHERE PGD.ID='$GradesId'"; 
$STMT_PO_GRADE_DATA=$iconn->query($SQL_CODE_LIST);

$ROW=$STMT_PO_GRADE_DATA->FETCH_ARRAY();

$InvId=$ROW['InvId'];

$SQL_po="SELECT * FROM `ScrmPoData` where PoID='$InvId'";
$STMT_PO=$iconn->query($SQL_po);

$ROW_po=$STMT_PO->FETCH_ARRAY();


 $SqlPackingStyles="SELECT * FROM `AdminPackingStyles` Where Status='1' ORDER by Uom,ABS(PackingStyle)";
$StmtPacking=$iconn->query($SqlPackingStyles);

 $SqlPIVarietyList="SELECT * FROM `$ScrmPIVarietyList`"; 
$StmtPIVarietyList=$iconn->query($SqlPIVarietyList);


extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	 $SelectedInvId=implode(',', $SelectedInvId);

  $SqlBreakUpDataOld="SELECT Grade,PackingStyleID,VarietyID FROM `ScrmPoGradeData` WHERE ID='$GradesId'"; 
  $StmtBreakUpDataOld=$iconn->query($SqlBreakUpDataOld);

	$RowOldData=$StmtBreakUpDataOld->FETCH_ARRAY();	 
	$GradeOld=$RowOldData['Grade'];
	$PackingStyleOld=$RowOldData['PackingStyleID'];
	$VarityOld=$RowOldData['VarietyID'];
	
	
	  $SqlSelectedBreakUp="select GROUP_CONCAT(ID) as GradeID from ScrmPoGradeData where 
	`Grade`='$GradeOld' and 
	PackingStyleID='$PackingStyleOld' and 
	VarietyID='$VarityOld' and InvId in ($SelectedInvId)";
	$StmtSelectedBreakUp=$iconn->query($SqlSelectedBreakUp);
	$RowSelectedBreakUp=$StmtSelectedBreakUp->fetch_array();
	 $GradesId=$RowSelectedBreakUp['GradeID'];
	
	 	
	$SqlPIVarietyList="SELECT * FROM `ScrmPIVarietyList` as SPV 
	left join ScrmBrandsMaster as  SBM on SBM.BrandID =SPV.BrandName
	left join AdminPackingStyles as  APS on APS.PCKID =SPV.PackingStyle
	 WHERE VarietyID='$VARITY_ID'";
$StmtPIVarietyList=$iconn->query($SqlPIVarietyList);
$RowPIVarietyList=$StmtPIVarietyList->fetch_array();

	/* $VarietyName=$RowPIVarietyList['FullName'].' '.$FREEZING_TYPE; 
	$Glaze=$RowPIVarietyList['Glaze'];
	 $GlazePerc=$RowPIVarietyList['GlazePerc'];
	 $Treatment=$RowPIVarietyList['Treatment'];
	 $FreezingType=$RowPIVarietyList['FreezingType'];
	 */


	 //$VarietyName=$RowPIVarietyList['FullName'].' '.$FREEZING_TYPE; 
	 $VarietyName=$FullName; 
	 $Glaze=$RowPIVarietyList['Glaze'];
	 $GlazePerc=$RowPIVarietyList['GlazePerc'];
	 $Treatment=$RowPIVarietyList['Treatment'];
	 $FreezingType=$RowPIVarietyList['FreezingType'];
	 $GRADE=$RowPIVarietyList['Grade'];
	 $PackingStyleID=$RowPIVarietyList['PCKID'];
	 $BRAND=$RowPIVarietyList['BrandName'];
	 
	
	
	  $sql_org_insert="UPDATE `ScrmPoGradeData` SET 
	`Grade`='$GRADE',
	`Mcs`='$MCS',
	`FreezingType`='$FreezingType',
	`PackingStyleID`='$PackingStyleID',
	`GlazeType`='$Glaze',
	`GlazePerc`='$GlazePerc',
	`Variety`='$VarietyName',
	`VarietyID`='$VARITY_ID',
	`Treatment`='$Treatment',
	`Price`='$Price',
	`Brand`='$BRAND',
	`BAP`='$BAP',
	`BuyerItemCode`='$BuyerItemCode',
	`Description`='$Description'
	WHERE ID in ($GradesId)";  
	$oldCsGradeDataV1 = legacyAuditFetchRowById('ScrmPoGradeData', 'ID', $GradesId, 'Sales');
	$stmt1=$iconn->query($sql_org_insert); 
	if($stmt1){
		legacyAuditLogUpdateById('ScrmPoGradeData', 'ID', $GradesId, $oldCsGradeDataV1, $_POST, [
			'InvId' => $InvId,
			'Remarks' => 'CS PO grades updated V1',
		], 'Sales');

		
		header('Location: PoBreakUp.php?id='.$InvId);
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
	 
<?php } ?>

                        <?php include ($IncludesPath."page-title.php");?>

	
          
	
<div class="row">
                            <div class="col-12">
                                <div class="card">
								
								
								
								<form role="form" method="post" enctype="multipart/form-data">
                                    <div class="card-body">
									
										<div class="row">
										
										 <div class="form-group col-lg-12">        
				  
              
			   <input type="text" class="form-control" hidden id="displayinformation" readonly >

                </div>
										
										<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Select PI<span style="color:red">*<span></label>
                 
                <select multiple class="form-control" name="SelectedInvId[]" id="SelectedInvId" required>
					

					 <?php while($RowPIRef=$StmtPIRef->fetch_array()){
						 $InvNo=$RowPIRef['InvNo'];
						 $RefPoID=$RowPIRef['PoID'];
					 ?>
					<option value="<?php echo $RefPoID ?>"<?php if( $RefPoID==$PoID)
					  echo 'selected="selected"'; ?>><?php echo $InvNo;?></option>
					<?php  }?>
				<select>
				
				</div>

			 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Variety Name<span style="color:red">*<span></label>
                 
                <select class="form-control" name="VARITY_ID" id="VARITY_ID" required>
					 <?php while ($RowPIVarietyList=$StmtPIVarietyList->fetch_array()){
						$OPTIONS='('.$RowPIVarietyList['DeviSUPC'].')' .$RowPIVarietyList['ProductDescription'];
						
						$FullName= $RowPIVarietyList['FullName'];
						$VALUE= $RowPIVarietyList['VarietyID'];
						 ?>
			 
					<option <?php if( $ROW['VarietyID']==$VALUE){
					 echo 'selected="selected"'; } ?> value="<?php echo $VALUE ?>" >
					<?php echo $OPTIONS;?></option>
					<?php  }?>
				<select>
				
				</div>
				
				
				
				
				
				
				
				
				
				
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">MCS<span style="color:red">*<span></label>
                  <input required type="number" class="form-control"  name="MCS" id="MCS"
				  placeholder="Enter No Mcs" value="<?php echo $ROW['Mcs']; ?>" >
                </div>
				
               <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Rate/Unit(<?php echo $UnitCurrency ?>)<span style="color:red">*<span></label>
                  <input required type="number" step="0.001" class="form-control"  name="Price" id="Price"
				  placeholder="Enter Price/Unit" value="<?php echo $ROW['Price']; ?>" >
                </div>
				 
				 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Buyer Item Code</label>
                  <input  type="text" class="form-control" name="BuyerItemCode" id="BuyerItemCode" 
				  placeholder="Enter Buyer Item Code" value="<?php echo $ROW['BuyerItemCode']; ?>">
                </div>
				
				<div class="form-group col-sm-12 col-lg-12" >
                  <label for="exampleInputEmail1">Full Name</label>
                  <input  readonly type="text" class="form-control" 
				  style=" background: grey;color: white;"
				  name="FullName" id="FullName" 
				  placeholder="" value="<?php echo $ROW['Variety']; ?>">
                </div>
				
				<div class="form-group col-lg-12" >
                  <label for="exampleInputEmail1"> Description</label>
				  
				  <textarea name="Description" class="form-control" rows="2" cols="50"
                    id="Description"   
					placeholder="Enter Description(Optional)" ><?php echo $ROW['Description'] ?></textarea>
				 		
		
                </div>	
                </div>	
				  
				
               
			   <div class="col-xs-12 box-footer"  align="right">
              
				<a target="" href="PoBreakUp.php?id=<?php echo $PoID; ?>" class="btn btn-danger  "> Close</a>
				<button type="submit"  name='submit' class="btn btn-success">Update</button>
              </div>
				
				</div>
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
		
  
       
    </body>
<Script>

$('document').ready(function(){
									
$("#GRADE").select2();
  $("#PackStyle").select2();
  $("#VARITY_ID").select2();
   $("#SelectedInvId").select2();

    });    


$('#VARITY_ID').change(function(){
getVarietyData();
   });
  


function getVarietyData(){
	 var PoId= '<?php echo $INVOICE_ID; ?>';
	 var Variety= $('#VARITY_ID').val(); 
	  document.getElementById('displayinformation').hidden = false;
   $.ajax({
    type: 'POST',
    url: 'Ajax/AjaxAutoFillOptions.php',
	dataType: 'json',
    data: {'Type':'VARIETY','PoId' : PoId,'Variety': Variety},  
    success: function(response){
		 var myJSON = JSON.parse(JSON.stringify(response));
		 
		 
	
	
		 var Species=myJSON['Species'];
		 var ProductFormm=myJSON['FullName'];
		 var FreezingTypee=myJSON['FreezingType'];
		 var PreparationTypee=myJSON['PreparationType'];
		 var GlazePercc=myJSON['GlazePerc'];
		 var Glazee=myJSON['Glaze'];
		 var Treatmentt=myJSON['Treatment'];
		 var Grade=myJSON['Grade'];
		 var PackingStyle=myJSON['PackingStyle'];
		 var BrandName=myJSON['BrandName'];
		 var FullName=myJSON['FullName'];
		 var Description=myJSON['Description'];
		 
		 
		
		
		$("#Description").val("");
		$("#displayinformation").val(Description);
		$("#FullName").val(FullName);
		//$("input:text").val(Data); 
		 
	 }
  });
	
}

 

 

</script>





</html>

			

