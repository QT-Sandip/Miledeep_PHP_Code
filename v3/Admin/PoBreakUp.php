<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


$PageTitleFolder="SalesNew";
$PageTitle="Grade Wise Data";


$INVOICE_ID=$_GET['id'];

$SqlPI="SELECT * FROM `ScrmPIData`   WHERE PoID='$INVOICE_ID'";

$StmtPI=$iconn->query($SqlPI);
$RowPI=$StmtPI->fetch_array();
$PoRefId=$RowPI['PoRefId'];


$SqlPIRef="SELECT * FROM `ScrmPIData`   WHERE PoRefId='$PoRefId'";

$StmtPIRef=$iconn->query($SqlPIRef);


$sql_packing="SELECT * FROM `$TblAdminPackingStyles` Where Status='1' order by abs(PackingStyle) Asc  limit 100";

$stmt_packing=$iconn->query($sql_packing);


$SQL_CODE_LIST="SELECT * FROM `$TblScrmPoGradeData` where InvId='$INVOICE_ID'";
$STMT_CODE_LIST=$iconn->query($SQL_CODE_LIST);

 $SQL_PO_GRADE_DATA="SELECT * FROM `$TblScrmPoGradeData` where InvId='$INVOICE_ID'  ORDER BY ID DESC";
$STMT_PO_GRADE_DATA=$iconn->query($SQL_PO_GRADE_DATA); 

$GRADE_ROW=$STMT_PO_GRADE_DATA->FETCH_ARRAY();


$SQL_OPTIONS="SELECT * FROM `OPTIONS` WHERE  DEPT='CS' order by ABS(OPTIONS) asc ";
$STMT_OPTIONS=$iconn->query($SQL_OPTIONS);
$ARY_OPTIONS=array();
WHILE($ROW=$STMT_OPTIONS->FETCH_ARRAY()){
	$ARY_OPTIONS[]=$ROW;
}


$SqlPIVarietyList="SELECT * FROM `$ScrmPIVarietyList`";
$StmtPIVarietyList=$iconn->query($SqlPIVarietyList);


$sql_invoice="SELECT * FROM `ScrmPIData` where PoID='$INVOICE_ID'";
$stmt_invoice=$iconn->query($sql_invoice);
$invoice_details=$stmt_invoice->fetch_array();

	

extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	 	
	$SqlPIVarietyList="SELECT * FROM `$ScrmPIVarietyList` WHERE VarietyID='$Variety'";
$StmtPIVarietyList=$iconn->query($SqlPIVarietyList);
$RowPIVarietyList=$StmtPIVarietyList->fetch_array();

	 $VarietyName=$RowPIVarietyList['FullName'].' '.$FREEZING_TYPE; 
	
  $count=mysqli_num_rows($StmtPIRef); 
  $StmtPIRef=$iconn->query($SqlPIRef);


	
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
	
	
	if($InvId=='All'){
			for ($i=0; $i<$count ; $i++){
		while($RowPIRef=$StmtPIRef->fetch_array()){
		$Inv_Id=$RowPIRef['PoID'];
			
			$sql_org_insert="INSERT INTO `$TblScrmPoGradeData`(`Grade`, `Mcs`, `FreezingType`, `InvId`, `PackingStyleID`,
	 `GlazeType`, `Variety`, `GlazePerc`, `Treatment`, `Brand`, `Price`,`BAP`,`VarietyID`,`SKUNo`) VALUES (
	 '$GRADE','$MCS','$FREEZING_TYPE','$Inv_Id','$PackingStyleID','$GlazeType','$VarietyName','$GlazePerc',
	 '$Treatment','$BRAND','$Price','$BAP','$Variety','$SKUNo')"; 
		$stmt1=$iconn->query($sql_org_insert);
		if ($stmt1) { legacyAuditLogInsertById($TblScrmPoGradeData, 'ID', $iconn->insert_id, [], [], 'Admin'); }
			}
		}
	}
	else{
	  $sql_org_insert="INSERT INTO `$TblScrmPoGradeData`(`Grade`, `Mcs`, `FreezingType`, `InvId`, `PackingStyleID`,
	 `GlazeType`, `Variety`, `GlazePerc`, `Treatment`, `Brand`, `Price`,`BAP`,`VarietyID`,`SKUNo`) VALUES (
	 '$GRADE','$MCS','$FREEZING_TYPE','$InvId','$PackingStyleID','$GlazeType','$VarietyName','$GlazePerc',
	 '$Treatment','$BRAND','$Price','$BAP','$Variety','$SKUNo')"; 
	
	$stmt1=$iconn->query($sql_org_insert);
		if ($stmt1) { legacyAuditLogInsertById($TblScrmPoGradeData, 'ID', $iconn->insert_id, [], [], 'Admin'); }
		}
		//echo $sql_org_insert; exit;
		}

	}else{
		echo '<script>alert("Grades And Break Up Mismatch") </script>';
	}
//echo $iid;
	if($stmt1){
		$oldInvData = legacyAuditFetchRowById('INVOICE_DATA', 'ID', $INVOICE_ID, 'Admin');
			 $SQL_INV_UPDATE="UPDATE `INVOICE_DATA` SET 
				`DEC_STATUS`='1'
				WHERE ID='$INVOICE_ID'";
	
	
	$STMT_INV_UPDATE=$iconn->query($SQL_INV_UPDATE);
		legacyAuditLogUpdateById('INVOICE_DATA', 'ID', $INVOICE_ID, $oldInvData, [], [], 'Admin');
		
		header('Location: PoBreakUp.php?id='.$INVOICE_ID);
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
                 
                <select  class="form-control" name="InvId" id="InvId">
							 <option value="All">	All		 </option>

					 <?php while($RowPIRef=$StmtPIRef->fetch_array()){
						 $InvNo=$RowPIRef['InvNo'];
						 $PoID=$RowPIRef['PoID'];
					 ?>
					<option value="<?php echo $PoID ?>"<?php if( $InvNo==$GRADE_ROW['InvNo'])
					  echo 'selected="selected"'; ?>><?php echo $InvNo;?></option>
					<?php  }?>
				<select>
				
				</div>
				
				
				
				 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Variety Name</label>
                 
                <select class="form-control" name="Variety" id="Variety">
					 <?php while ($RowPIVarietyList=$StmtPIVarietyList->fetch_array()){
						$OPTIONS= $RowPIVarietyList['ShortName'];
						$FullName= $RowPIVarietyList['FullName'];
						$VALUE= $RowPIVarietyList['VarietyID'];
						 ?>
			 
					<option value="<?php echo $VALUE ?>"<?php if( $FullName==$GRADE_ROW['Variety'])
					  echo 'selected="selected"'; ?>><?php echo $OPTIONS;?></option>
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
			 
					<option value="<?php echo $VALUE ?>" <?php if( $VALUE==$GRADE_ROW['Grade'])
					  echo 'selected="selected"'; ?>><?php echo $OPTIONS;?></option>
					<?php  }}?>
				<select>
                </div>
			
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Packing Style</label>
                  <select required class="form-control" name="PackingStyleID" id="PackingStyleID">
				  <option value="">Select Packing</option>
				   <?php while ($Row_PO=$stmt_packing->fetch_array()){ ?>
				     <option value="<?php echo $Row_PO['PCKID'] ?>"<?php if( $Row_PO['PCKID']==$GRADE_ROW['PackingStyleID'])
					  echo 'selected="selected"'; ?>><?php echo $Row_PO['PackingStyle'].' - '.$Row_PO['GrossWeight'].'KGS';?></option>
					 
				   
				 <?php  }?>	  
				  </select>
				  
				  </div>
				  
				
	<div class="form-group col-sm-3">
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
                </div>
				
								
				
				 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Brand</label>
                  <input required type="text" class="form-control" name="BRAND" id="BRAND" 
				  placeholder="Enter BRAND" value="<?php echo $GRADE_ROW['BRAND']; ?>">
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">SKU No</label>
                  <input  type="text" class="form-control" name="SKUNo" id="SKUNo" 
				  placeholder="Enter SKU No" value="<?php echo $GRADE_ROW['SKUNo']; ?>">
                </div>
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">MCS</label>
                  <input required type="number" class="form-control"  name="MCS" id="MCS"
				  placeholder="Enter MASTER CARTONS EX 12,13" >
                </div>
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Rate/Unit</label>
                  <input required type="number" step="0.001" class="form-control"  name="Price" id="Price"
				  placeholder="Enter Rate(USD) " >
                </div>
				  
				  
				  
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">BAP</label>
                  <input  type="TEXT"  class="form-control"  name="BAP" id="BAP"
				  placeholder="Enter BAP " >
                </div>
				  
				  
				  
				
				
               
			   <div class="form-group col-sm-12"  align="right">
              
				<!--href="ViewReports.php?id=<?php echo $INVOICE_ID ?>"-->
				 <a target="" href="DocAddReports.php?PoID=<?php echo $INVOICE_ID ?>"
       class="btn btn-danger  "> Close</a>
  		
            
				<button type="submit"  name='submit' class="btn btn-success">ADD TO PO </button>
			  <a href="PIList.php"  id="<?php echo $INVOICE_ID; ?>"  class="btn btn-primary eia_report">SUBMIT  </a>
             
			 </div>
              </div>
            </form>
           
       <!-- end col -->
                        </div> <!-- end row -->
                        </div> <!-- end row -->
				<div class="row">
                            <div class="col-12">

						<div id="tdetails" class="table-responsive" >  
        <div class="card">
          
        <div class="card-body" id="PIList">
                    

  <table id="" class="table table-bordered table-hover">
              

    <thead>

      <tr>
	  
	  
	  <th>Variety</th>
	   <th>Grade</th>
	   <th>Packing Style</th>

	  <th>Treatment</th>
	 <th>Freezing Type</th>
	  <th>Brand</th>
	  	 
	  
	  <th> Mcs</th>
	  <th> Amount</th>
	  <th> Actions</th>
	  	
      </tr>
    </thead>
    <tbody>
	<?php
	//echo $sql;
   while($row=$STMT_CODE_LIST->fetch_array()){
	 $PO_ID=$row['ID'];  
	  $PackingID=$row['PackingStyleID'];  
	 
	 $SqlPckStyl="SELECT * FROM `AdminPackingStyles` WHERE PCKID ='$PackingID'";
	 
	
	 $StmtPckStyl=$iconn->query($SqlPckStyl); 
     $RowPckStyl=$StmtPckStyl->fetch_array();
	 $Packing=$RowPckStyl['PackingStyle'];
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

	
	
	<td>
<!--	<a href="CsPoGradesEdit.php?id=<?php echo $row['ID'];?>"> <i class='fa fa-edit'></i> Edit</a>
	 <a  href="CsPoGradesEdit.php?id=<?php echo $row['ID'];?>&PoID=<?php echo $INVOICE_ID;?>" 
						 class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
						 title="" data-original-title="Edit"><i class="mdi mdi-pencil font-size-18"></i></a>-->
        

	<!--	<button id="<?php  echo $PO_ID;?>" onClick="reply_click(this.id)" class=" glyphicon glyphicon-remove btn btn-danger"> Delete</button>
	-->
	<button class="btn btn-link text-danger Delete" data-id="<?php echo $row['ID'];?>" 
			
							data-toggle="tooltip" data-placement="top" title="" value=""
							data-original-title="Delete"><i class="mdi mdi-trash-can font-size-18"></i></button> 						
	
	</td>
       

        </tr>

	  <?php 

   }
	  ?>

     </tbody>

  </table>
</div>
    
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
<script>
  /*function reply_click(clicked_id) {

  var INVOICE_ID='<?php echo $PO_ID?>';		 
		
		alert("Are you sure to delete this Grade?");		  
		$.ajax({
		  url:'Ajax/AjaxDelete.php',
		  type:'POST',
		  data:{'TYPE':'BREAKUP_DELETE','D_ID':clicked_id,'INVOICE_ID':INVOICE_ID},
		  success:function(data){
			  console.log(data);
			  
			// $("#CODE_LIST").html(data);
			 alert("Deleted");
			 location.reload()
			}
			});					  
  }*/
  
  	 


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

function showData(data){
			
			$("#PIList").html(data);	

		var Stable=	$("#key-datatable").DataTable({
			keys:!0,
			language:{
				paginate:{
					previous:"<i class='mdi mdi-chevron-left'>",
					next:"<i class='mdi mdi-chevron-right'>"
					}
					},
					drawCallback:function(){
						$(".dataTables_paginate > .pagination").addClass("pagination-rounded")}});			
 
}

</script>


</html>

			

