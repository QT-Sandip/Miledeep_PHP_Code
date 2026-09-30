<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


$PageTitleFolder="SalesNew";
$PageTitle="Grade Wise Data";

$GradesId=$_GET['id'];
$PoID=$_GET['PoID'];

$SqlPI="SELECT * FROM `ScrmPIData`   WHERE PoID='$PoID'";

$StmtPI=$iconn->query($SqlPI);
$RowPI=$StmtPI->fetch_array();
$PoRefId=$RowPI['PoRefId'];


$SqlPIRef="SELECT * FROM `ScrmPIData`   WHERE PoRefId='$PoRefId'";

$StmtPIRef=$iconn->query($SqlPIRef);

$SQL_OPTIONS="SELECT * FROM `OPTIONS` WHERE  DEPT='CS' order by ABS(OPTIONS) asc";
$STMT_OPTIONS=$iconn->query($SQL_OPTIONS);
$ARY_OPTIONS=array();
WHILE($ROW=$STMT_OPTIONS->FETCH_ARRAY()){
	$ARY_OPTIONS[]=$ROW;
}


  $SQL_CODE_LIST="SELECT 
  PGD.ID,Grade,GlazeType,InvId,Mcs,FreezingType,PackingStyleID,
  Variety,VarietyID,GlazePerc,Treatment,Brand,Price,BAP,SKUNo
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
	
	 	
	$SqlPIVarietyList="SELECT * FROM `$ScrmPIVarietyList` WHERE VarietyID='$VARITY_ID'";
$StmtPIVarietyList=$iconn->query($SqlPIVarietyList);
$RowPIVarietyList=$StmtPIVarietyList->fetch_array();

	 $VarietyName=$RowPIVarietyList['FullName'].' '.$FREEZING_TYPE; 
	
	$oldGradeRow = legacyAuditFetchRowById('ScrmPoGradeData', 'ID', $GradesId, 'Admin');
	
	  $sql_org_insert="UPDATE `ScrmPoGradeData` SET 
	`Grade`='$GRADE',
	`Mcs`='$MCS',
	`FreezingType`='$FREEZING_TYPE',
	`PackingStyleID`='$PackStyle',
	`GlazeType`='$GLAZE',
	`GlazePerc`='$GLAZE_PERC',
	`Variety`='$VarietyName',
	`VarietyID`='$VARITY_ID',
	`Treatment`='$SOCKING',
	`Price`='$Price',
	`Brand`='$BRAND',
	`BAP`='$BAP',
	`SKUNo`='$SKUNo'
	WHERE ID in ($GradesId)"; 
	$stmt1=$iconn->query($sql_org_insert); 
	if($stmt1){
		legacyAuditLogUpdateById('ScrmPoGradeData', 'ID', $GradesId, $oldGradeRow, array(), array(), 'Admin');

		
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
										
										<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Select PI</label>
                 
                <select multiple class="form-control" name="SelectedInvId[]" id="SelectedInvId">
					

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
                  <label for="exampleInputEmail1">Variety Name</label>
                 
                <select class="form-control" name="VARITY_ID" id="VARITY_ID">
					 <?php while ($RowPIVarietyList=$StmtPIVarietyList->fetch_array()){
						$OPTIONS= $RowPIVarietyList['ShortName'];
						$FullName= $RowPIVarietyList['FullName'];
						$VALUE= $RowPIVarietyList['VarietyID'];
						 ?>
			 
					<option <?php if( $ROW['VarietyID']==$VALUE){
					 echo 'selected="selected"'; } ?> value="<?php echo $VALUE ?>" >
					<?php echo $OPTIONS;?></option>
					<?php  }?>
				<select>
				
				</div>
				
				
				
				
				
				
				
				<div class="form-group col-sm-3" > 
				  <label for="exampleInputEmail1">Grade</label>
                 
				   <select class="form-control" name="GRADE" id="GRADE">
				   <?php foreach($ARY_OPTIONS as $value){
					if($value['TYPE']=='GRADES'){
						$OPTIONS= $value['OPTIONS'];
						$VALUE= $value['VALUE']; ?>
			 
						<option <?php if($ROW['Grade']==$VALUE){ ?> selected="selected" <?php }?>
			 value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
					<?php  }}?>
				<select>
                </div>
				
				
			 <div class="form-group col-sm-3">  
                  <label for="exampleInputPassword1">Packing Style</label>
                   <select required class="form-control" name="PackStyle" id="PackStyle">
				   <option   value="">Select Packing</option>	
				   <?php while ($RowPacking=$StmtPacking->fetch_array()){ ?>
				  
				   <?php if($RowPacking['PCKID']==$ROW['PackingStyleID']){ ?>
					<option selected="selected" 
					value="<?php echo $RowPacking['PCKID'] ?>"> 
					<?php echo $RowPacking['PackingStyle'].' - '.$RowPacking['GrossWeight'].'KGS';?> </option> 
					<?php }else{ ?>
					<option value="<?php echo $RowPacking['PCKID'] ?>"><?php echo $RowPacking['PackingStyle'].' - '.$RowPacking['GrossWeight'].'KGS';?> </option> 
					<?php }}?>
		  
				  </select>
                </div>
				

				 		
				
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Glaze(%)</label> 
                  <input required type="number" class="form-control" name='GLAZE_PERC' id="GLAZE_PERC"
				  placeholder="Enter GLAZE PERCENTAGE" value="<?php echo $ROW['GlazePerc']; ?>">
                </div>
				
				
				 <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Glaze</label>
				  <select class="form-control" name="GLAZE" id="GLAZE">
					<option <?php if($ROW['GlazeType']=='NW/NC'){ ?> selected="selected" <?php }?>value="NW/NC">NW/NC</option>
				   <option <?php if($ROW['GlazeType']=='FW/FC'){ ?> selected="selected" <?php }?>value="FW/FC">FW/FC</option>						
                 <select>	
                </div>
				
				
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Treatment</label>
				  <select class="form-control" name="SOCKING" id="SOCKING">
				  					<option value="">Select</option>

				  <option <?php if($ROW['Treatment']=='STPP'){ ?> selected="selected" <?php }?>value="STPP">STPP</option>
				  <option <?php if($ROW['Treatment']=='Non-Phosphate'){ ?> selected="selected" <?php }?>value="Non-Phosphate">Non-Phosphate</option>
				  <option <?php if($ROW['Treatment']=='Chem Free'){ ?> selected="selected" <?php }?>value="Chem Free">Chem Free</option>
				  <option <?php if($ROW['Treatment']=='Citrus'){ ?> selected="selected" <?php }?>value="Citrus">Citrus</option>
				  <option <?php if($ROW['Treatment']=='Not Socked'){ ?> selected="selected" <?php }?>value="Not Socked">Not Socked</option>

				<select>
			   </div>
			   
			 	<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Freezing Type</label>
				  <select class="form-control" name="FREEZING_TYPE" id="FREEZING_TYPE">
				  
				  <option <?php if($ROW['FreezingType']=='IQF'){ ?> selected="selected" <?php }?>value="IQF">IQF</option>
				  <option <?php if($ROW['FreezingType']=='SEMI IQF'){ ?> selected="selected" <?php }?>value="SEMI IQF">SEMI IQF</option>
				  <option <?php if($ROW['FreezingType']=='BLOCK'){ ?> selected="selected" <?php }?>value="BLOCK">BLOCK</option>
				  <option <?php if($ROW['FreezingType']=='COOKED'){ ?> selected="selected" <?php }?>value="COOKED">COOKED</option>
				  <option <?php if($ROW['FreezingType']=='BLACHED'){ ?> selected="selected" <?php }?>value="BLACHED">BLACHED</option>
						
                 <select>
                </div>
				
							
				
				 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Brand</label>
                  <input required type="text" class="form-control" name="BRAND" id="BRAND" 
				  placeholder="Enter Brand" value="<?php echo $ROW['Brand']; ?>">
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">SKU No</label>
                  <input required type="text" class="form-control" name="SKUNo" id="SKUNo" 
				  placeholder="Enter SKU No" value="<?php echo $ROW['SKUNo']; ?>">
                </div>
				
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">MCS</label>
                  <input required type="number" class="form-control"  name="MCS" id="MCS"
				  placeholder="Enter No Mcs" value="<?php echo $ROW['Mcs']; ?>" >
                </div>
				
               <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Rate/Unit</label>
                  <input required type="number" step="0.001" class="form-control"  name="Price" id="Price"
				  placeholder="Enter Price/Unit" value="<?php echo $ROW['Price']; ?>" >
                </div>
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">BAP</label>
                  <input  type="text"  class="form-control"  name="BAP" id="BAP" value="<?php echo $ROW['BAP']; ?>"
				  placeholder="Enter BAP " >
                </div>
                </div>
				  
				
               
			   <div class="col-xs-12 box-footer"  align="right">
              
				<!--href="ViewReports.php?id=<?php echo $INVOICE_ID ?>"-->
				<button type="submit"  name='submit' class="btn btn-primary">Update</button>
			 <!-- <a href="CsPoList.php"  id="<?php echo $INVOICE_ID; ?>"  class="btn btn-primary eia_report">SUBMIT  </a> -->
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
		  //$("#Variety").select2();

    var arr=[];


$('#FULL_NAME').change(function() {
	var test=this.value;
		alert(test);
});

    	
    	$('#continuebtn').on('click',function(){
    
    		$('form').submit();
    	});
    });    
$('#VARITY').click(function(e) {
     //alert('clicked');
	 $('#myModal').modal('toggle');
//$('.modal fade').modal('show');
//$('#myModal').modal('hide');
 });
 $('#ar').click(function(e) {
     //alert('clicked');
	 $('#myModal1').modal('toggle');
//$('.modal fade').modal('show');
//$('#myModal').modal('hide');
 });
 
 
 
 //mil functionlity
 
 
 $('.eia_report').click(function(e) {
      var iid=$(this).attr('id');
		alert(iid);
	 
	    $.ajax({
			type:'POST',
			url:'test_mil.php',
			data:{'id':iid},
		    success:function(data){
			alert(data);
			return false;
			
			window.location.replace("ViewReports.php?id="+iid);
			console.log(data);

		} 		
	});
	});
 
 
  var ar=[];
  var type=[];
	$('.checkdiv1 input').change(function() {
  if (this.checked) {
    ar.push(this.value);
	 type.push(this.id);
  }
  else {
   ar.splice(ar.indexOf(this.value), 1);
  type.splice(type.indexOf(this.value), 1);
  
 }
  $('#ar').val(ar + '');
 $('#type').val(type + '');

  });
</script>





</html>

			

