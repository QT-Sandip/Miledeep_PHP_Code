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


$SqlBreakUpData="SELECT 
  PGD.ID,Grade,GlazeType,InvId,Mcs,FreezingType,PackingStyleID,
  Variety,VarietyID,GlazePerc,Treatment,Brand,Price,BAP,BuyerItemCode,Description
  FROM `ScrmPoGradeData` as PGD 
left Join AdminPackingStyles as CPS on
 PGD.PackingStyleID=CPS.PCKID WHERE PGD.ID='$GradesId'"; 
$StmtBreakUpData=$iconn->query($SqlBreakUpData);

$RowBreakUpData=$StmtBreakUpData->FETCH_ARRAY();

$InvId=$RowBreakUpData['InvId'];
$BreakupVarietyID=$RowBreakUpData['VarietyID'];

$SQL_po="SELECT * FROM `ScrmPoData` where PoID='$InvId'";
$STMT_PO=$iconn->query($SQL_po);

$ROW_po=$STMT_PO->FETCH_ARRAY();


 $SqlPackingStyles="SELECT * FROM `AdminPackingStyles` Where Status='1' ORDER by Uom,ABS(PackingStyle)";
$StmtPacking=$iconn->query($SqlPackingStyles);

  $SqlPIVarietyList="SELECT * FROM `$ScrmPIVarietyList` where VarietyID='$BreakupVarietyID'"; 
$StmtPIVarietyList=$iconn->query($SqlPIVarietyList);
$RowVarietyList=$StmtPIVarietyList->fetch_array();

function checkEmpty($Value){
		if($Value==''){
		$Value='';	
		}
		else{
		$Value=' '.$Value;	
		}
		return $Value;
	}
$PreparationName=checkEmpty($RowVarietyList['PreparationName']);
	$TypeProduct=checkEmpty($RowVarietyList['TypeProduct']);
	$ProductFormm=checkEmpty($RowVarietyList['FullName']);
	$FreezingTypee=checkEmpty($RowVarietyList['FreezingType']);
	$PreparationTypee=checkEmpty($RowVarietyList['PreparationType']);
	$GlazePercc=checkEmpty($RowVarietyList['GlazePerc']);
	$Glazee=checkEmpty($RowVarietyList['Glaze']);
	$Treatmentt=checkEmpty($RowVarietyList['Treatment']);
	$Grade=checkEmpty($RowVarietyList['Grade']);
	$PackingStyle=checkEmpty($RowVarietyList['PackingStyle']);
	$BrandName=checkEmpty($RowVarietyList['BrandName']);
	$PreparationDesc=checkEmpty($RowVarietyList['PreparationDesc']);
	$DeviSUPC=checkEmpty($RowVarietyList['DeviSUPC']);

 $VarietyDescription=$DeviSUPC.'-'.$Grade.$PackingStyle.$ProductDescription .$FreezingTypee.$GlazePercc. '%' .$Glazee.$Treatmentt.$BrandName;

extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	$SelectedInvId=implode(',', $SelectedInvId);
	
	//echo $VarietyId; exit;

  $SqlBreakUpDataOld="SELECT Grade,PackingStyleID,VarietyID FROM `ScrmPoGradeData` WHERE ID='$GradesId'"; 
  $StmtBreakUpDataOld=$iconn->query($SqlBreakUpDataOld);

	$RowOldData=$StmtBreakUpDataOld->FETCH_ARRAY();	 
	$GradeOld=$RowOldData['Grade'];
	$PackingStyleOld=$RowOldData['PackingStyleID'];
	$VarityOld=$RowOldData['VarietyID'];
	
	//Enable below Code For Batch Update Risky Code Clear Tesing Needed
	 /* $SqlSelectedBreakUp="select GROUP_CONCAT(ID) as GradeID from ScrmPoGradeData where 
	`Grade`='$GradeOld' and 
	PackingStyleID='$PackingStyleOld' and 
	VarietyID='$VarityOld' and InvId in ($SelectedInvId)";
	$StmtSelectedBreakUp=$iconn->query($SqlSelectedBreakUp);
	$RowSelectedBreakUp=$StmtSelectedBreakUp->fetch_array();
	 $GradesId=$RowSelectedBreakUp['GradeID'];
	*/
	 	
	 $SqlPIVarietyList="SELECT * FROM `ScrmPIVarietyList` as SPV 
	left join ScrmBrandsMaster as  SBM on SBM.BrandID =SPV.BrandName
	left join AdminPackingStyles as  APS on APS.PCKID =SPV.PackingStyle
	 WHERE VarietyID='$VarietyId'"; 
$StmtPIVarietyList=$iconn->query($SqlPIVarietyList);
$RowPIVarietyList=$StmtPIVarietyList->fetch_array();

	
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
	`VarietyID`='$VarietyId',
	`Treatment`='$Treatment',
	`Price`='$Price',
	`Brand`='$BRAND',
	`BAP`='$BAP',
	`BuyerItemCode`='$BuyerItemCode',
	`Description`='$Description'
	WHERE ID in ($GradesId)"; 
	$oldCsGradeData = legacyAuditFetchRowById('ScrmPoGradeData', 'ID', $GradesId, 'Sales');
	$stmt1=$iconn->query($sql_org_insert); 
	if($stmt1){
		legacyAuditLogUpdateById('ScrmPoGradeData', 'ID', $GradesId, $oldCsGradeData, $_POST, [
			'InvId' => $InvId,
			'Remarks' => 'CS PO grades updated',
		], 'Sales');

		
		header('Location: PoBreakUp.php?id='.$InvId);
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
					<?php include "PopUpTable.php"; ?>
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
                  <label for="exampleInputEmail1">Variety Name(Click to Change)<span style="color:red">*<span></label>
				    <input hidden type="text" id="VarietyId" name="VarietyId"  
					class="form-control hidden" value="<?php echo $RowBreakUpData['VarietyID'] ?>">
                <input required type="text"  class="form-control"  name="Variety" id="Variety" 
				value="<?php echo $VarietyDescription; ?>"
				data-id="<?php echo $INVOICE_ID ?>"  placeholder="Click to select" ></input>
				
				</div>
				
				
				
				
				
				
				
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">MCS<span style="color:red">*<span></label>
                  <input required type="number" class="form-control"  name="MCS" id="MCS"
				  placeholder="Enter No Mcs" value="<?php echo $RowBreakUpData['Mcs']; ?>" >
                </div>
				
               <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Rate/Unit(<?php echo $UnitCurrency ?>)<span style="color:red">*<span></label>
                  <input required type="number" step="0.001" class="form-control"  name="Price" id="Price"
				  placeholder="Enter Price/Unit" value="<?php echo $RowBreakUpData['Price']; ?>" >
                </div>
				 
				 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Buyer Item Code</label>
                  <input  type="text" class="form-control" name="BuyerItemCode" id="BuyerItemCode" 
				  placeholder="Enter Buyer Item Code" value="<?php echo $RowBreakUpData['BuyerItemCode']; ?>">
                </div>
				
				<div class="form-group col-sm-12 col-lg-12" >
                  <label for="exampleInputEmail1">Full Name</label>
                  <input  readonly type="text" class="form-control" 
				  style=" background: grey;color: white;"
				  name="FullName" id="FullName" 
				  placeholder="" value="<?php echo $RowBreakUpData['Variety']; ?>">
                </div>
				
				<div class="form-group col-lg-12" >
                  <label for="exampleInputEmail1"> Description</label>
				  
				  <textarea name="Description" class="form-control" rows="2" cols="50"
                    id="Description"   
					placeholder="Enter Description(Optional)" ><?php echo $RowBreakUpData['Description'] ?></textarea>
				 		
		
                </div>	

                					
              
                </div>	
				
				  
				
                 
				  
				  
				  
				
				
               
			   <div class="form-group col-sm-12"  align="right">
              
				
					<a target="" href="PoBreakUp.php?id=<?php echo $PoID; ?>" class="btn btn-danger  "> Close</a>
				<button type="submit"  name='submit' class="btn btn-success">Update</button>
             
			 </div>
             
            </form>
			
			
  		
           
       <!-- end col -->
                        </div> <!-- end row -->
                        </div> <!-- end row -->
                        </div> <!-- end row -->
				
                            <div class="col-12">

						
        <div class="card">
          
        <div class="card-body" id="ShowDataTable">
       
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
<Script>

$('document').ready(function(){
								

   $("#SelectedInvId").select2();

    });    
	
	$(document).on('click','#Variety',function(e){
	 			e.preventDefault();

	
	  var PoID =$(this).data('id');
	 
	 //$('#Update').val(PoID);
	 
	
	 showPoPopUp(PoID);
	  
	});
  
 function showPoPopUp(PoID){
	
			 $.ajax({
		   url:'Ajax/AjaxPopUpVarietyDetails.php',
		  type:'POST',
		  data:{'PoID':PoID},
		  success:function(data){
			  $("#Update").hide();

			
			 $('.modal-title').text('Variety List');
			$("#PopUpTable").html(data);
			$('#myModal').modal('show');
					}
			});

}	

$(document).on('click','.SelectVariety',function(e){
	e.preventDefault();
	
	 var VarietyID =$(this).data('id');
			$('#myModal').modal('hide');


		getVarietyData(VarietyID);

	
	
});
 	


/*$('#VARITY_ID').change(function(){
getVarietyData();
   });
  */


function getVarietyData(VarietyID){
	 var PoId= '<?php echo $INVOICE_ID; ?>';
	 var Variety= VarietyID;  
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
		 var VarietyDescription=myJSON['VarietyDescription'];
		 var VarietyId=myJSON['Variety'];
		 
		 //alert(FullName);
		
		
		$("#Description").val("");
		$("#displayinformation").val(Description);
		$("#FullName").val(FullName);
		$("#VarietyId").val(VarietyId);
		$("#Variety").val(VarietyDescription);
		//$("input:text").val(Data); 
		 
	 }
  });
	
}

 
</script>



</html>


