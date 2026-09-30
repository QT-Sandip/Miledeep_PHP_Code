<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


$PageTitleFolder="Sales";
$PageTitle="Grade Wise Data";


$INVOICE_ID=$_GET['id'];

$SqlPI="SELECT * FROM `ScrmPIData`   WHERE PoID='$INVOICE_ID'";

$StmtPI=$iconn->query($SqlPI);
$RowPI=$StmtPI->fetch_array();
$PoRefId=$RowPI['PoRefId'];
$QtyUnit=$RowPI['QtyUnit'];
$UnitCurrency=$RowPI['UnitCurrency'];


$SqlPIRef="SELECT * FROM `ScrmPIData`   WHERE PoRefId='$PoRefId'";

$StmtPIRef=$iconn->query($SqlPIRef);
$RowCurrency=$StmtPIRef->fetch_array();
$UnitCurrency=$RowCurrency['UnitCurrency'];

$StmtPIRef=$iconn->query($SqlPIRef);


$sql_packing="SELECT * FROM `$TblAdminPackingStyles` Where Status='1' order by abs(PackingStyle) Asc  limit 100";

$stmt_packing=$iconn->query($sql_packing);


/*$SQL_CODE_LIST="SELECT * FROM `ScrmPoGradeData` where InvId='$INVOICE_ID'";
$STMT_CODE_LIST=$iconn->query($SQL_CODE_LIST);*/

 $SQL_PO_GRADE_DATA="SELECT * FROM `ScrmPoGradeData` where InvId='$INVOICE_ID'  ORDER BY ID ASC";
$STMT_PO_GRADE_DATA=$iconn->query($SQL_PO_GRADE_DATA); 

$GRADE_ROW=$STMT_PO_GRADE_DATA->FETCH_ARRAY();

$STMT_PO_GRADE_DATA=$iconn->query($SQL_PO_GRADE_DATA); 

$SQL_OPTIONS="SELECT * FROM `OPTIONS` WHERE  DEPT='CS' order by ABS(OPTIONS) asc ";
$STMT_OPTIONS=$iconn->query($SQL_OPTIONS);
$ARY_OPTIONS=array();
WHILE($ROW=$STMT_OPTIONS->FETCH_ARRAY()){
	$ARY_OPTIONS[]=$ROW;
}

  $SqlPIVarietyList="SELECT * FROM `ScrmPIVarietyList` as  SPV 
	left join ScrmBrandsMaster as  SBM on SBM.BrandID =SPV.BrandName
	left join AdminPackingStyles as  APS on APS.PCKID =SPV.PackingStyle
	left join ScrmPreparationMaster as  SPM on SPM.ShortCode =SPV.PreparationType
		where SPV.Status='1'";
$StmtPIVarietyList=$iconn->query($SqlPIVarietyList);

function checkEmpty($Value){
		if($Value==''){
		$Value='';	
		}
		else{
		$Value=' '.$Value;	
		}
		return $Value;
	}
	
	$AryVarietyList=array();

while($RowPIVarietyList=$StmtPIVarietyList->fetch_array()){
	$AryItem=array();
	$PreparationName=checkEmpty($RowPIVarietyList['PreparationName']);
	$TypeProduct=checkEmpty($RowPIVarietyList['TypeProduct']);
	$ProductFormm=checkEmpty($RowPIVarietyList['FullName']);
	$FreezingTypee=checkEmpty($RowPIVarietyList['FreezingType']);
	$PreparationTypee=checkEmpty($RowPIVarietyList['PreparationType']);
	$GlazePercc=checkEmpty($RowPIVarietyList['GlazePerc']);
	$Glazee=checkEmpty($RowPIVarietyList['Glaze']);
	$Treatmentt=checkEmpty($RowPIVarietyList['Treatment']);
	$Grade=checkEmpty($RowPIVarietyList['Grade']);
	$PackingStyle=checkEmpty($RowPIVarietyList['PackingStyle']);
	$BrandName=checkEmpty($RowPIVarietyList['BrandName']);
	$PreparationDesc=checkEmpty($RowPIVarietyList['PreparationDesc']);
	$ProductDescription=checkEmpty($RowPIVarietyList['ProductDescription']);
	$DeviSUPC=checkEmpty($RowPIVarietyList['DeviSUPC']);
	$BuyerSUPC=checkEmpty($RowPIVarietyList['BuyerSUPC']);
	
	
	// $Data=$Species.$ProductFormm.$FreezingTypee.$PreparationTypee.$GlazePercc.$Glazee.$Treatmentt.$Grade.$PackingStyle.$BrandName; 
	 $Description=$DeviSUPC.'-'.$Grade.$PackingStyle.$ProductDescription .$FreezingTypee.$GlazePercc. '%' .$Glazee.$Treatmentt.$BrandName; 
	$AryItem['Name']=$Description;
	$AryItem['ID']=$RowPIVarietyList['VarietyID'];
	$AryVarietyList[]=$AryItem;
	
}


$sql_invoice="SELECT * FROM `ScrmPIData` where PoID='$INVOICE_ID'";
$stmt_invoice=$iconn->query($sql_invoice);
$invoice_details=$stmt_invoice->fetch_array();

$UnitCurrency=$invoice_details['UnitCurrency'];

	

extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
		 $count= count($SelectedInvId); 
		 $InvId=implode(',', $SelectedInvId);
		 
		 $SqlPIRef="SELECT * FROM `ScrmPIData`   WHERE PoID in ($InvId)";

$StmtPIRef=$iconn->query($SqlPIRef);

	 	
	$SqlPIVarietyList="SELECT * FROM `$ScrmPIVarietyList` as SPV 
	left join ScrmBrandsMaster as  SBM on SBM.BrandID =SPV.BrandName
	left join AdminPackingStyles as  APS on APS.PCKID =SPV.PackingStyle
	WHERE VarietyID='$Variety'";
$StmtPIVarietyList=$iconn->query($SqlPIVarietyList);
$RowPIVarietyList=$StmtPIVarietyList->fetch_array();

	 //$VarietyName=$RowPIVarietyList['FullName'].' '.$FREEZING_TYPE; 
	 $VarietyName=$FullName; 
	 $Glaze=$RowPIVarietyList['Glaze'];
	 $GlazePerc=$RowPIVarietyList['GlazePerc'];
	 $Treatment=$RowPIVarietyList['Treatment'];
	 $FreezingType=$RowPIVarietyList['FreezingType'];
	 $GRADE=$RowPIVarietyList['Grade'];
	 $PackingStyleID=$RowPIVarietyList['PCKID'];
	 $BRAND=$RowPIVarietyList['BrandName'];
	 
	 
	
 	
	$D_O_ANALYSIS = date("d-m-Y", strtotime($D_O_ANALYSIS));
	$D_O_COMPLEATION = date("d-m-Y", strtotime($D_O_COMPLEATION));

	
	$ARY_GRADE=explode(',',$GRADE);
	$ARY_MCS=explode(',',$MCS); 
	$ARY_AMOUNT_PER_LB=explode(',',$Price);
	
	if(count($ARY_GRADE)==count($ARY_MCS) && count($ARY_GRADE)==count($ARY_AMOUNT_PER_LB)){
		
		for($I=0; $I<count($ARY_GRADE) ; $I++){
	
	$GRADE=$ARY_GRADE[$I];
	$MCS=$ARY_MCS[$I];
	$Price=$ARY_AMOUNT_PER_LB[$I];
	
	
	if($InvId!=''){
			for ($i=0; $i<$count ; $i++){
		while($RowPIRef=$StmtPIRef->fetch_array()){
		$Inv_Id=$RowPIRef['PoID'];
			
			$sql_org_insert="INSERT INTO `ScrmPoGradeData`(`Grade`, `Mcs`, `FreezingType`, `InvId`, `PackingStyleID`,
	 `GlazeType`, `Variety`, `GlazePerc`, `Treatment`, `Brand`, `Price`,`BAP`,`VarietyID`,`BuyerItemCode`,`Description`,`CodeType`) VALUES (
	 '$GRADE','$MCS','$FreezingType','$Inv_Id','$PackingStyleID','$Glaze','$VarietyName','$GlazePerc',
	 '$Treatment','$BRAND','$Price','$BAP','$Variety','$BuyerItemCode','$Description','$CodeType')"; 
		$stmt1=$iconn->query($sql_org_insert);
		if($stmt1){
			$lastGradeId = $iconn->insert_id;
			legacyAuditLogInsertById('ScrmPoGradeData', 'ID', $lastGradeId, $_POST, array('InvId' => $Inv_Id), 'Sales');
		}
			}
		}
	}
	
		}

	}else{
		echo '<script>alert("Grades And Break Up Mismatch") </script>';
	}
//echo $iid;
	if($stmt1){
		
			/* $SQL_INV_UPDATE="UPDATE `INVOICE_DATA` SET 
				`DEC_STATUS`='1'
				WHERE ID='$INVOICE_ID'";
	
	
	$STMT_INV_UPDATE=$iconn->query($SQL_INV_UPDATE);
		
		*/
		header('Location: PoBreakUp.php?id='.$INVOICE_ID);
	}
}

 ?>


<!DOCTYPE html>
<html lang="en">
<link href="<?php echo  $href; ?>"  rel="stylesheet" type="text/css" />

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
					<option value="<?php echo $RefPoID ?>"<?php if($RefPoID==$GRADE_ROW['InvId'])
					  echo 'selected="selected"'; ?>><?php echo $InvNo;?></option>
					<?php  }?>
				<select>
				
				</div>
				
				
				
				 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Variety Name<span style="color:red">*<span></label>
                <select required class="form-control" name="Variety" id="Variety">
				                 <option value=""> Select </option>

					 <?php foreach ($AryVarietyList as $VlItem){
						
						
						 ?>
			 
					<option value="<?php echo $VlItem['ID'] ?>"><?php echo $VlItem['Name'];?></option>
					<?php  }?>
				<select>
				
				</div>
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Mcs<span style="color:red">*<span></label>
                  <input required type="number" class="form-control"  name="MCS" id="MCS"
				  placeholder="Enter MASTER CARTONS " >
                </div>
				
			<!--	<div class="form-group col-sm-3" > 
				  <label for="exampleInputEmail1">Grade<span style="color:red">*<span></label>
                 
				  
				  <select required class="form-control" name="GRADE" id="GRADE">
					 <?php /*foreach($ARY_OPTIONS as $value){
					if($value['TYPE']=='GRADES'){
						$OPTIONS= $value['OPTIONS'];
						$VALUE= $value['VALUE']; ?>
			 
					<option value="<?php echo $VALUE ?>" <?php if( $VALUE==$GRADE_ROW['Grade'])
					  echo 'selected="selected"'; ?>><?php echo $OPTIONS;?></option>
					<?php  }}*/?>
				<select>
                </div>
			
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Packing Style<span style="color:red">*<span></label>
                  <select required class="form-control" name="PackingStyleID" id="PackingStyleID">
				  <option value="">Select Packing</option>
				   <?php /*while ($Row_PO=$stmt_packing->fetch_array()){ ?>
				     <option value="<?php echo $Row_PO['PCKID'] ?>"<?php if( $Row_PO['PCKID']==$GRADE_ROW['PackingStyleID'])
					  echo 'selected="selected"'; ?>><?php echo $Row_PO['PackingStyle'].' - '.$Row_PO['GrossWeight'].'(GW)';?></option>
					 
				   
				 <?php  }*/?>	  
				  </select>
				  
				  </div>-->
				  
				
	<!--<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Glaze</label>
				  <select class="form-control select2" name="GlazeType" id="GlazeType">
					<option value="NW/NC"<?php if( "NW/NC"==$GRADE_ROW['GlazeType'])
					  echo 'selected="selected"'; ?>>NW/NC</option>
					<option value="FW/FC" <?php if( "FW/FC"==$GRADE_ROW['GlazeType'])
					  echo 'selected="selected"'; ?>>FW/FC</option>	
						
                 <select>	
                </div>
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Glaze Percentage</label> 
                  <input required type="number" class="form-control" name='GlazePerc' id="GlazePerc"
				  placeholder="Enter GlazeType PERCENTAGE" value="<?php echo $GRADE_ROW['GlazePerc']; ?>">
                </div>
				
				
				 
				
				
				
				 <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Treatment</label>
				<select class="form-control select2" name="Treatment" id="Treatment">
					<option value="">Select</option>
					<option value="STPP">STPP</option>
					<option value="Non-Phospate" >Non-Phospate</option>							
					<option value="Chem Free" >Chem Free</option>							
					<option value="Citrus" >Citrus</option>							
					<option value="No Soak" >No Soak</option>							
                 <select>				  
			   </div>
				 
				 
				 
		 
                 				
				 <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Freezing Type</label>
				  <select class="form-control select2" name="FREEZING_TYPE" id="FREEZING_TYPE">
					<option value="IQF">IQF</option>
					<option value="SEMI IQF">SEMI IQF</option>
					<option value="BLOCK" >BLOCK</option>	
					<option value="COOKED" >COOKED</option>	
					<option value="BLACHED" >BLACHED</option>	
                 <select>
                </div>-->
				
								
				
				<!-- <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Brand<span style="color:red">*<span></label>
                  <input required type="text" class="form-control" name="BRAND" id="BRAND" 
				  placeholder="Enter BRAND" value="<?php echo $GRADE_ROW['BRAND']; ?>">
                </div>-->
				
				
				
				
				
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Rate/<?php echo $QtyUnit ?><span style="color:red">*<span></label>
                  <input required type="number" step="0.001" class="form-control"  name="Price" id="Price"
				  placeholder="Enter Rate/Unit(<?php echo $UnitCurrency ?>) " >
                </div>
				  
				  
				<!--  <div class="form-group col-lg-3" >              									 
                <label for="exampleInputEmail1">Code Type</label>
				<select  class="form-control select2" name="CodeType" id="CodeType">
				 	<option  value="">Select</option>
				 	<option  value="Po Number">Po Number</option>
					<option  value="EPGC">EPGC Code</option>
					<option  value="Buyer Code">Buyer Code</option>
									
				</select>
                </div>-->
				  
				 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1"> Code</label>
                  <input  type="text" class="form-control" name="BuyerItemCode" id="BuyerItemCode" 
				  placeholder="Enter Buyer Item Code" value="">
                </div>
				
				<div class="form-group col-sm-12 col-lg-12" >
                  <label for="exampleInputEmail1">Full Name</label>
                  <input  readonly type="text" class="form-control" 
				  style=" background: grey;color: white;"
				  name="FullName" id="FullName" 
				  placeholder="" value="">
                </div>
				
				
				<div class="form-group col-lg-12" >
                  <label for="exampleInputEmail1">Buyer Description</label>
				  
				  <textarea name="Description" class="form-control" rows="2" cols="50"
                    id="Description"   
					placeholder="Enter Description(Optional)" ></textarea>
				 		
		
                </div>	
                </div>	
				
				  
				
                 
				  
				  
				  
				
				
               
			   <div class="form-group col-sm-12"  align="right">
              
				
				 <!--<a target="" href="PIList.php"
					class="btn btn-danger  "> Close</a>-->
					
				<button type="button" id="close"  name="close"  class="btn btn-danger">Close </button>
            
				<button type="submit"  name='submit' class="btn btn-success">ADD TO PO </button>
				<!--<a href="PIList.php"  id="<?php echo $INVOICE_ID; ?>"  class="btn btn-primary eia_report">SUBMIT  </a>-->
             
			 </div>
             
            </form>
			
			
  		
           
       <!-- end col -->
                        </div> <!-- end row -->
                        </div> <!-- end row -->
                        </div> <!-- end row -->
				
                            <div class="col-12">

						
        <div class="card">
          
        <div class="card-body" id="ShowDataTable">
                    

 <table  id="key-datatable" data-paging="false" 
	   class="table table-bordered  wrap w-100" >
	   <thead class="thead-light">
      <tr>
	  
	  
	   <th>Variety</th>
	   <th>Grade</th>
	   <th>Packing Style</th>
	   <th>Treatment</th>
	   <th>Freezing Type</th>
	   <th>Brand</th>
	   <th> Mcs</th>
	   <th> Unit Price<br>(<?php echo $QtyUnit ?>)</th>
	   <th> Amount in <br>(<?php echo $UnitCurrency ?>)</th>
	   <th> Actions </th>
	  	
      </tr>
    </thead>
    <tbody>
	<?php
	//echo $sql;
	$TotalMcs=0;
	$TotalAmount=0;
	
   while($row=$STMT_PO_GRADE_DATA->fetch_array()){
	 $PoGradeId=$row['ID'];  
	  $PackingID=$row['PackingStyleID'];  
	 
	// $SqlPckStyl="SELECT * FROM `AdminPackingStyles` WHERE PCKID ='$PackingID'";
$SqlTotal="SELECT PackingStyle,sum(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs,
			sum(Mcs*TotalSlabs*SlabWeight*Price * LbsFactor) as PriceLbs FROM `ScrmPoGradeData` 
			as PGD left join AdminPackingStyles as PS on PGD.PackingStyleID=PS.PCKID WHERE InvId='$INVOICE_ID' 
			and ID='$PoGradeId'";
$StmtTotal=$iconn->query($SqlTotal);
$RowTotal=$StmtTotal->fetch_array();

	if( $invoice_details['QtyUnit']=='KGS'){				
				$Weight = $RowTotal['NetWgtKgs'];	
				$Price = $RowTotal['PriceKgs'];	
			}else{
				$Weight = $RowTotal['NetWgtLbs'];
				$Price = $RowTotal['PriceLbs'];
			}

			
	 /*$StmtPckStyl=$iconn->query($SqlPckStyl); 
     $RowPckStyl=$StmtPckStyl->fetch_array();
	 $Packing=$RowPckStyl['PackingStyle'];*/
	 $TotalMcs +=$row['Mcs'];
	 $Packing=$RowTotal['PackingStyle'];
	 $TotalAmount += $Price;
	?>
	<tr>			
	
	<td><?php echo $row['Variety']; ?> </td>	
	<td><?php echo $row['Grade']; ?> </td>	
	<td><?php echo $Packing; ?> </td>	
	<td><?php echo $row['Treatment']; ?> </td>	
	<td><?php echo $row['FreezingType']; ?> </td>	
	<td><?php echo $row['Brand']; ?> </td>	
	<td><?php echo $row['Mcs']; ?> </td>	
	<td><?php echo $row['Price']; ?> </td>	
	<td><?php echo number_format($Price,2); ?> </td>	

	
	
	<td>
	 <a  href="CsPoGradesEdit.php?id=<?php echo $row['ID'];?>&PoID=<?php echo $INVOICE_ID;?>" 
						 class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
						 title="" data-original-title="Edit"><i class="mdi mdi-pencil font-size-18"></i></a>
        

	<button class="btn btn-link text-danger Delete" data-id="<?php echo $row['ID'];?>" 
			
							data-toggle="tooltip" data-placement="top" title="" value=""
							data-original-title="Delete"><i class="mdi mdi-trash-can font-size-18"></i></button> 						
	
	</td>
       

        </tr>

	  <?php 

   }
	  ?>
	  <tr>
<th> Total</th>
<th> </th>
<th> </th>
<th> </th>
<th> </th>
<th> </th>
<th><?php echo $TotalMcs ?></th>
<th></th>
<th><?php echo number_format($TotalAmount,2) ?></th>

<th> </th>

</tr>
     </tbody>

  </table>
</div>
    
	</div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->
                        </div>
                        <!-- end row-->
                        


                 <?php include ($IncludesPath."footer.php");?>
                
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        

		<?php include ($IncludesPath."vendor-scripts.php");?>
		<?php include ($IncludesPath."NewScripts.php");?>
		<?php include ($IncludesPath."ShowDataTable.php");?>

  
       
    </body>

<script>
  
  	

	 $(document).on('click', '.Delete', function(e){
	  var D_ID =$(this).data('id');


   		var r = confirm("Are you sure to delete this Grade?");
  if (r == true) { 
		 
			
			
			$.ajax({
		  url:'Ajax/AjaxDelete.php',
		  type:'POST',
		  data:{'TYPE':'BREAKUP_DELETE','D_ID':D_ID},
		  success:function(data){
			  console.log(data);
			  
			 alert("Deleted");
			  		showData(data);
					location.reload();
			}
			});		

} else {
  }			
  });
 
  
  
  
  
</script>





<Script>



$('document').ready(function(){
	
	  $("#Variety").select2();
	  $("#PackingStyleID").select2();
	  $("#GRADE").select2();
	  $("#InvId").select2();
	  $("#SelectedInvId").select2();

	
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


 $('#close').click(function(e) {
 window.close();
 });
 
 
 
 //mil functionlity
 
 
 $('.eia_report').click(function(e) {
      var iid=$(this).attr('id');
		//alert(iid);
	 
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




<Script>

$('document').ready(function(){

  //alert('hiiiii');
  
	 
	//UMA
	
	
	
	    // Set search input value on click of result item
$(document).on("click", "#update", function(){
        
		var clicked_id=$(this).val();
	  var ref_id='<?php echo $INVOICE_ID?>';
	  var VARITY_NAME_ID='#Variety'+clicked_id;	
	  var FREEZING_TYPE_ID='#FREEZING_TYPE'+clicked_id;	
	  var Treatment_ID='#Treatment'+clicked_id;	
	  var AMOUNT_PER_LB_ID='#Price'+clicked_id;	
	  
	  var BRAND_ID='#BRAND'+clicked_id;
	  var GRADES_ID='#GRADE'+clicked_id;
	  var MCS_ID='#MCS'+clicked_id;
	  
	  
	  
	  
	  
	  
	  var FREEZING_TYPE=$(FREEZING_TYPE_ID+"").val();
	  var Variety=$(VARITY_NAME_ID+"").val();
	  var Treatment=$(Treatment_ID+"").val();
	  var Price=$(AMOUNT_PER_LB_ID+"").val();
	  var BRAND=$(BRAND_ID+"").val();
	  var GRADE=$(GRADES_ID+"").val();
	  var MCS=$(MCS_ID+"").val();
		//alert(FARM_REG_ID);
		  
		$.ajax({
		  url:'PoBreakUp_Edit.php',
		  type:'POST',
		  data:{
		  
		  'TYPE':'GRADE_DATA_UPDATE',
		  'D_ID':clicked_id,
		  'ref_id':ref_id,
		  'FREEZING_TYPE':FREEZING_TYPE,
		  'Variety':Variety,
		  'Treatment':Treatment,
		  'Price':Price,
		  'BRAND':BRAND,
		  'GRADE':GRADE,
		  'MCS':MCS
		  
		  },
		  success:function(data){
			  console.log(data);
			  
			 $("#PACK_SIZE").html(data);
			 alert("Grade data Updated");
			}
			});	
	
        
});

});   	

</script>
<script>

$('#Variety').change(function(){
getVarietyData();
   });
  


function getVarietyData(){
	 var PoId= '<?php echo $INVOICE_ID; ?>';
	 var Variety= $('#Variety').val(); 
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
		 
		 
		
		
		$("#displayinformation").val(Description);
		$("#FullName").val(FullName);
		//$("input:text").val(Data);
		 
	 }
  });
	
}

function checkEmpty(Value){
	
		if(Value==''){
		 Value='';	
		}
		else{
		Value='('+ Value+')';	
		}
		return Value;
	}

</script>


</html>

			

