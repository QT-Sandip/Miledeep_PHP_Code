<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");
 


$PageTitleFolder="Sales";
$PageTitle="Custom Invoice Creation";


$PIId=$_GET['PIID'];
$ExporterID=$_GET['ExporterID'];

$AryPriceWeightData=getInvoiceWeight($iconn,$GradeID,$PIId);
$AryPriceData=getPaymentValues($iconn,$PIId);

 $NetWeight=$AryPriceWeightData['Qty']; 
 $GrossWeight=$AryPriceWeightData['GrossWeightKgs'];
 $DutiesPrice=$AryPriceData['DutiesPrice'];
 $InvValueUSD=$AryPriceData['InvValueUSD'];

$SQL_OPTIONS="SELECT * FROM `AdminGeneralOptions` WHERE  DeptID='SALES'  order by SortOrder asc ";
$STMT_OPTIONS=$iconn->query($SQL_OPTIONS);
$ARY_OPTIONS=array();
WHILE($ROW=$STMT_OPTIONS->FETCH_ARRAY()){
	$ARY_OPTIONS[]=$ROW;
}

$SqlBank="SELECT * FROM AdminBankDetails";
$StmtBank=$iconn->query($SqlBank);
	
$SqlLiner="SELECT * FROM `ScrmLinerMaster` WHERE Status=1";
$StmtLiner=$iconn->query($SqlLiner);

$SqlExchangeRate="SELECT * FROM `ScrmExchangeRates` ORDER by FromDate DESC LIMIT 1";
$StmtExchangeRate=$iconn->query($SqlExchangeRate);
$RowExchangeRate=$StmtExchangeRate->fetch_array();
$ExchangeRate=$RowExchangeRate['ExchangeRate'];
//script


 $SqlPackingStyle="SELECT DISTINCT(PackingStyleID) as PackingStyleID FROM `ScrmPoGradeData` WHERE InvId='$PIId'";
$StmtPackingStyle=$iconn->query($SqlPackingStyle);


$AryPackingStyleIds=array();
while($RowPackingStyle=$StmtPackingStyle-> fetch_array()){
$AryPackingStyleIds[]=$RowPackingStyle['PackingStyleID'];
}

$AryPoGradeID=array();

//script


 $SqlPI="SELECT ExporterID,Freight,QtyUnit FROM `ScrmPoData`  WHERE PoID='$PIId'"; 
$StmtPI=$iconn->query($SqlPI);
$RowPI=$StmtPI->fetch_array();

$ExpID=$RowPI['ExporterID'];
$PIFreight=$RowPI['Freight'];
$QtyUnit=$RowPI['QtyUnit'];
		
$CalculatedValues=getPaymentValues($iconn,$PIId);		
 $SqlCount="SELECT * FROM `ScrmCustomInvoices` WHERE PiID='$PIId'";
		
		$StmtCount=$iconn->query($SqlCount);
		
		//echo $SqlCount;
		$RowCustomInvoice=$StmtCount->fetch_array();
		$InvNo=$RowCustomInvoice['CInvNo'];
		$ExRate=$RowCustomInvoice['ExRate'];
		
		$TotalGrossWt=$RowCustomInvoice['TotalGrossWt'];
		if($TotalGrossWt!=''){
		$GrossWeight=$TotalGrossWt;
		}

if($PIFreight==''){
$Freight=$RowCustomInvoice['Freight'];	
}
else{
$Freight=$PIFreight;	
}
	
if($ExRate==''){
$ExchangeRate=$RowExchangeRate['ExchangeRate'];	
}
else{
$ExchangeRate=$ExRate;	
}
//$RowPI=$StmtCount->fetch_array();
//echo $SqlCount;	
		
		$Count=mysqli_num_rows($StmtCount);	

	 
$SqlGradeData="SELECT * FROM `ScrmCIGradeData` where InvId='$PIId'  ORDER BY ID DESC"; 
$StmtGradeData=$iconn->query($SqlGradeData); 
 $CIGradeCount=mysqli_num_rows($StmtGradeData); 

 $SqlPIVarietyList="SELECT * FROM `ScrmPIVarietyList`"; 
$StmtPIVarietyList=$iconn->query($SqlPIVarietyList);

$SqlUserDetails="SELECT * FROM `AdminUserDetails` WHERE CustomUserID='1' and DepartmentID=9";
$StmtUserDetails=$iconn->query($SqlUserDetails);

//exit;		


extract($_POST);
extract($_GET);

function postComBreakUp(){
	global $iconn,$PIId;
	
 $SqlGradeData="SELECT * FROM `ScrmPoGradeData` where InvId='$PIId'  ORDER BY ID ASC"; 
$StmtGradeData=$iconn->query($SqlGradeData); 
   while($RowGradeData=$StmtGradeData->fetch_array()){

	
	$PoGradeID=$RowGradeData['ID'];
	$PoGrade=$RowGradeData['Grade'];
	$PoMcs=$RowGradeData['Mcs'];
	$PoFreezingType=$RowGradeData['FreezingType'];
	$PoPackingStyleID=$RowGradeData['PackingStyleID'];
	$PoGlazeType=$RowGradeData['GlazeType'];
	$PoVariety=$RowGradeData['Variety'];
	$PoGlazePerc=$RowGradeData['GlazePerc'];
	$PoTreatment=$RowGradeData['Treatment'];
	$PoBrand=$RowGradeData['Brand'];
	$PoPrice=$RowGradeData['Price'];
	$PoBAP=$RowGradeData['BAP'];
	$PoVarietyID=$RowGradeData['VarietyID'];
	$BuyerItemCode=$RowGradeData['BuyerItemCode'];
		
		
		
		
	 	$SqlGradeInsert="INSERT INTO `ScrmCIGradeData`(`PoGradeID`,`Grade`, `Mcs`, `FreezingType`, `InvId`, `PackingStyleID`,
	 `GlazeType`, `Variety`, `GlazePerc`, `Treatment`, `Brand`, 
	 `Price`,`BAP`,`VarietyID`,`BuyerItemCode`) VALUES ('$PoGradeID',
	'$PoGrade','$PoMcs','$PoFreezingType','$PIId','$PoPackingStyleID','$PoGlazeType','$PoVariety','$PoGlazePerc',
	 '$PoTreatment','$PoBrand','$PoPrice','$PoBAP','$PoVarietyID','$BuyerItemCode')"; 
		$StmtGradeInsert=$iconn->query($SqlGradeInsert); 
		
	
	}
	if($StmtGradeInsert){
		return true;
	}else{
	return false;
	}
}



if(isset($_POST['submit'])){	
if($Count==0){
$SqlCIInsert="INSERT INTO `ScrmCustomInvoices`
		(`PiID`, `CInvNo`, `CInvDate`,`CInvCode`, `IGST`, `Discount`, 
		`BankID`, `NoOfDays`,`ExporterID`,`AdvanceAmount`,
		`OverSeasCommission`,`Description`, `CIDescription`, 
		`NameofRepresentative`, `PackDescription`,
		`QCertificateNo`, `ContainerNo`, `ProductTypes`, `USClearance`,
		`PaymentType`, `ByRoad`, `IRNNumber`, `ClearenceCharges`,
		`TotalNetWt`, `TotalGrossWt`, `Duty`, `Freight`, `ExRate`,
         `StockOutDate`, `Vessel`, `VoyageNo`, `Remarks`, `CIDescriptionspl`,`ShippingLineID`, `CodeType`, `Note`)		   
		VALUES ('$PIId','$CInvNo','$CInvDate','$CInvCode','$IGST','$Discount',
		'$BankID','$NoOfDays','$ExpID','$AdvanceAmount','$OverSeasCommission',
		'$Description','$CIDescription','$NameofRepresentative',
		'$PackDescription','$QCertificateNo','$ContainerNo',
		'$ProductTypes','$USClearance','$PaymentType',
		'$ByRoad','$IRNNumber','$ClearenceCharges','$TotalNetWt','$TotalGrossWt'
		,'$Duty','$Freight','$ExRate','$StockOutDate','$Vessel'
		,'$VoyageNo','$Remarks','$CIDescriptionspl','$ShippingLineID','$CodeType','$Note')"; 
		
$StmtInsert=$iconn->query($SqlCIInsert);
}else{
	$oldCustInvData = legacyAuditFetchRowById('ScrmCustomInvoices', 'PiID', $PIId, 'Sales');
	 $SqlCIInsert= "UPDATE `ScrmCustomInvoices` SET
		`CInvNo`='$CInvNo',
        `CInvDate`='$CInvDate',
		`CInvCode`='$CInvCode',
		`IGST`='$IGST',
		`Discount`='$Discount',
         `BankID`='$BankID',
		 `NoOfDays`='$NoOfDays',
		 `AdvanceAmount`='$AdvanceAmount',
		 `OverSeasCommission`='$OverSeasCommission',
		 `Description`='$Description',
		 `QCertificateNo`='$QCertificateNo',
		 `ContainerNo`='$ContainerNo',
		 `ProductTypes`='$ProductTypes',
		 `USClearance`='$USClearance',
		 `PaymentType`='$PaymentType',
		 `ByRoad`='$ByRoad',
		 `IRNNumber`='$IRNNumber',
		 `TotalNetWt`='$TotalNetWt',
		 `TotalGrossWt`='$TotalGrossWt',
		 `Duty`='$Duty',
		 `Vessel`='$Vessel',
		 `VoyageNo`='$VoyageNo',
		 `ExRate`='$ExRate',
		 `Remarks`='$Remarks',
		 `StockOutDate`='$StockOutDate',
		 `Freight`='$Freight',
		 `CIDescriptionspl`='$CIDescriptionspl',
		 `ShippingLineID`='$ShippingLineID',
		 `ClearenceCharges`='$ClearenceCharges',
		 `CodeType`='$CodeType',
		 `Note`='$Note'
		 
		 WHERE `PiID`='$PIId'"; 
		 exit;

$StmtInsert=$iconn->query($SqlCIInsert);
	
	
}


	if($StmtInsert){
	if($Count==0){
		legacyAuditLogInsertById('ScrmCustomInvoices', 'PiID', $PIId, $_POST, [
			'CInvNo' => $CInvNo,
			'ReferenceNo' => $PIId,
			'Remarks' => 'Custom invoice created',
		], 'Sales');
	}else{
		legacyAuditLogUpdateById('ScrmCustomInvoices', 'PiID', $PIId, $oldCustInvData, $_POST, [
			'CInvNo' => $CInvNo,
			'ReferenceNo' => $PIId,
			'Remarks' => 'Custom invoice updated',
		], 'Sales');
	}
	if($CIGradeCount==0){	
		postComBreakUp();
	}
	postUpdateShipmentBreif($iconn,$CInvDate,$CInvNo,$PIId);
	postContainerNo($PIId,$ContainerNo,$DocType);
	callBackUrl();
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

                        <?php include ($IncludesPath."page-title.php");
						include "PopUpTable.php"; 
						 include $cp."Includes/ProformaView.php";
						?>

	
         
	
<div class="row">
                            <div class="col-12">
                                <div class="card">
								
								<form role="form" method="post" >
                                    <div class="card-body">
									
										<div class="row">
	<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Custom Invoice<span style="color:red">*</span></label>
				<input required type="text" class="form-control" name='CInvNo' id="CInvNo" 
				  placeholder="Enter Custom Invoice No" value="<?php echo $RowCustomInvoice['CInvNo']?>">		
		
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Custom Invoice Date<span style="color:red">*</span></label>
 <input  required type="date" class="form-control" name='CInvDate' id="CInvDate" 
				  placeholder=" " value="<?php echo $RowCustomInvoice['CInvDate']?>">
                </div>

					
					 
					
				<!--	 <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Custom Invoice Code</label>
                 
                <select class="form-control select2" name="CInvCode" id="CInvCode">
					 <?php 
					 //$SciName=$RowPIData['SciName']; 
					 foreach($ARY_OPTIONS as $value){
					if($value['OptionType']=='CInvCode'){
						$OPTIONS= $value['Name'];
						$VALUE= $value['Value']; ?>
			 
					<option  <?php if($RowCustomInvoice['CInvCode']==$VALUE){ ?> selected="selected"  <?php } ?>
					
					value="<?php echo $VALUE ?>"><?php echo $OPTIONS?></option>
					<?php  }}?>
				<select>
				
				</div>-->
					
					
				  <div class="form-group col-sm-3" > 
				<label for="exampleInputEmail1">Invoice Type (IGST/LUT)<span style="color:red">*</span></label>
                 <select required class="form-control select2" id="IGST" name="IGST" >
				  <option  value="">Select</option>
				  <option <?php if($RowCustomInvoice['IGST']=='IGST') {?>selected="selected"<?php } ?>
				  
				  value="IGST">IGST</option>
				  <option  <?php if($RowCustomInvoice['IGST']=='LUT') {?>selected="selected"<?php } ?>
				  value="LUT">LUT</option>
				
				
				
						</select>
					</div>
					
					<div class="form-group col-sm-3" > 
				<label for="exampleInputEmail1">Product Type<span style="color:red">*</span></label>
                 <select required class="form-control select2" id="ProductTypes" name="ProductTypes" >
				  <option  value="">Select</option>
				  <option <?php if($RowCustomInvoice['ProductTypes']=='PTAAPTA') {?>selected="selected"<?php } ?>	  
				  value="PTAAPTA">PTAAPTA</option>
				  <option  <?php if($RowCustomInvoice['ProductTypes']=='NO PTA/FTA') {?>selected="selected"<?php } ?>
				  value="NO PTA/FTA">NO PTA/FTA</option>		
						</select>
					</div>
					
					<div class="form-group col-sm-3" > 
				<label for="exampleInputEmail1">Code Type</label>
                 <select class="form-control select2" id="CodeType" name="CodeType" >
				  <option  value="">Select</option>
				  <option <?php if($RowCustomInvoice['CodeType']=='ITEM CODE') {?>selected="selected"<?php } ?>	  
				  value="ITEM CODE">ITEM CODE</option>
				  <option  <?php if($RowCustomInvoice['CodeType']=='EPCG LICENSE NUMBER') {?>selected="selected"<?php } ?>
				  value="EPCG LICENSE NUMBER">EPCG LICENSE NUMBER</option>		
				 <option  <?php if($RowCustomInvoice['CodeType']=='PO-NO') {?>selected="selected"<?php } ?>
				  value="PO-NO">PO-NO</option>		
				<option  <?php if($RowCustomInvoice['CodeType']=='PCS/BAGS') {?>selected="selected"<?php } ?>
				  value="PCS/BAGS">PCS/BAGS</option>		
						</select>
					</div>
					
					<div class="form-group col-sm-3" > 
				<label for="exampleInputEmail1">Payment Type<span style="color:red">*</span></label>
                 <select required class="form-control select2" id="PaymentType" name="PaymentType" >
				  <option  value="">Select</option>
				  <option <?php if($RowCustomInvoice['PaymentType']=='Collections') {?>selected="selected"<?php } ?>				  
				  value="Collections">Collections</option>
				  <option  <?php if($RowCustomInvoice['PaymentType']=='Negotiations') {?>selected="selected"<?php } ?>
				  value="Negotiations">Negotiations</option>		
						</select>
					</div>
					
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Q Certificate No</label>
				<input  type="text" class="form-control" name='QCertificateNo' id="QCertificateNo" 
				  placeholder="Enter Q Certificate No" value="<?php echo $RowCustomInvoice['QCertificateNo']?>">		
		
                </div>
					
					
					
					
					<!--<div class="form-group col-lg-3">  
                  <label for="exampleInputPassword1">Bank Details</label>
                   <select  class="form-control select2" name="BankID" id="BankID">
				 				  <option value="">Select Bank</option>

				   <?php if($RowCustomInvoice['BankID']==0){
				   $BankID=$RowPIData['BankID'];
				   }
				   else{
				   $BankID=$RowCustomInvoice['BankID'];
				   }
				   while ($RowBank=$StmtBank->fetch_array()){ ?>
				     <option <?php if($BankID==$RowBank['ID']){ ?> selected="selected"  <?php } ?>
					 
					 
					 value="<?php echo $RowBank['ID'] ?>">
					 <?php echo $RowBank['AccntName'].' - 
					 '.$RowBank['BankName'].'-'.$RowBank['AccountNumber'] ?></option>
				   
				 <?php  }?>	  
				  </select>
                </div>-->
					
					<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Discount</label>
				<input  type="decimal" class="form-control" name='Discount' id="Discount" 
				  placeholder="Enter Discount" value="<?php echo $RowCustomInvoice['Discount']?>">		
		
                </div>
				

				<!--<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Terms No.of Days</label>
				<input  type="number" class="form-control" name='NoOfDays' id="NoOfDays" 
				  placeholder="Enter No.of Days" value="<?php echo $RowCustomInvoice['NoOfDays']?>">		
		
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Advance Amount</label>
				<input  type="text" class="form-control" name='AdvanceAmount' id="AdvanceAmount" 
				  placeholder="Enter Advance Amount" value="<?php echo $RowCustomInvoice['AdvanceAmount']?>">	
		
                </div>-->	
                <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">OverSeas Commission(Cents/KG)</label>
				<input  type="text" class="form-control" name='OverSeasCommission' id="OverSeasCommission" 
				  placeholder="Enter OverSeas Commission" value="<?php echo $RowCustomInvoice['OverSeasCommission']?>">		
		
                </div>
				
			<!-- <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">US Clearance Charges</label>
				<input  type="text" class="form-control" name='ClearenceCharges' id="ClearenceCharges" 
				  placeholder="Enter US Clearance Charges" value="<?php echo $RowCustomInvoice['ClearenceCharges']?>">		
		
                </div>
				
				 <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Transport Date</label>
				<input  type="Date" class="form-control" name='StockOutDate' id="StockOutDate" 
				  placeholder="Enter Transport Date" value="<?php echo $RowCustomInvoice['StockOutDate']?>">		
		
                </div>-->
				
				<div class="form-group col-lg-3">  
                  <label for="exampleInputPassword1">Shipping Line</label>
                   <select  class="form-control select2" name="ShippingLineID" id="ShippingLineID">
				 				  <option value="">Select</option>

				   <?php  while ($RowLiner=$StmtLiner->fetch_array()){ ?>
				     <option <?php if($RowCustomInvoice['ShippingLineID']==$RowLiner['LinerID']){ ?> selected="selected"  <?php } ?>
					value="<?php echo $RowLiner['LinerID'] ?>"><?php echo $RowLiner['LinerName'] ?></option>
				   
				 <?php  }?>	  
				  </select>
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Container No</label>
				<input  type="text" class="form-control" name='ContainerNo' id="ContainerNo" 
				  placeholder="Enter Container No" value="<?php echo $RowCustomInvoice['ContainerNo']?>">		
		
                </div>
				
				
				
				<!--<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Vessel</label>
				<input  type="text" class="form-control" name='Vessel' id="Vessel" 
				  placeholder="Enter Vessel" value="<?php echo $RowCustomInvoice['Vessel']?>">		
		
                </div>
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Voyage No</label>
				<input  type="text" class="form-control" name='VoyageNo' id="VoyageNo" 
				  placeholder="Enter Voyage No" value="<?php echo $RowCustomInvoice['VoyageNo']?>">		
		
                </div>-->
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">By Road</label>
				<input  type="text" class="form-control" name='ByRoad' id="ByRoad" 
				  placeholder="Enter By Road Details" value="<?php echo $RowCustomInvoice['ByRoad']?>">		
		
                </div>
				
				
                </div>
				<HR></HR>

                <div class="row">
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Invoice Value</label>
				<input readonly  type="text" class="form-control" name='InvValueUSD' id="InvValueUSD" 
				style="background: gray;color: white;"
				  placeholder="Enter Total Invoice Value" value="<?php echo number_format($InvValueUSD,2)?>">		
		
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Total NetWt</label>
				<input readonly  type="text" class="form-control" name='TotalNetWt' id="TotalNetWt" 
				style="background: gray;color: white;"
				  placeholder="Enter Total Net Weight" value="<?php echo $NetWeight?>">		
		
                </div>
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Total GrossWt(<?php echo $QtyUnit; ?>)<span style="color:red">*</span></label>
				 <button  id="AddGrossWtPerBrkUp" data-id="<?php echo$PIId ?>" data-value="
	           <?php echo $PIId ?>" class="text">
				 <b> Add + </b></button>

				
				<input readonly type="text" style="background: gray;color: white;" class="form-control" name='TotalGrossWt' id="TotalGrossWt" 
				  placeholder="Enter Total Gross Weight" value="<?php echo $GrossWeight?>">		
		
                </div>
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Duty</label>
				<input readonly type="text" class="form-control" name='Duty' id="Duty" style="background: gray;color: white;" 
				  placeholder="Enter Duty" value="<?php echo $DutiesPrice?>">		
		
                </div>
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Freight<span style="color:red">*</span></label>
				<input required type="text" class="form-control" name='Freight' id="Freight" 
				  placeholder="Enter Freight" value="<?php echo $Freight; ?>">		
		
                </div>
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">ExRate<span style="color:red">*</span></label>
				<input required type="text" class="form-control" name='ExRate' id="ExRate" 
				  placeholder="Enter Exchange Rate" value="<?php echo $ExchangeRate; ?>">		
		
                </div>
				
				
				
				<div class="form-group col-lg-12" >
                  <label for="exampleInputEmail1">IRN Number</label>
				  
				  <textarea name="IRNNumber" class="form-control" rows="2" cols="50"
                    id="IRNNumber"   
					placeholder="Enter IRN Number" ><?php echo $RowCustomInvoice['IRNNumber'] ?></textarea>
				 		
		
                </div>
				
				<div class="form-group col-lg-12" >
                  <label for="exampleInputEmail1">Remarks</label>
				  
				  <textarea name="Remarks" class="form-control" rows="2" cols="50"
                    id="Remarks"   
					placeholder="Enter Remarks" ><?php echo $RowCustomInvoice['Remarks'] ?></textarea>
				 		
		
                </div>	
				
				
				<div class="form-group col-lg-12" >
                  <label for="exampleInputEmail1">Note(Display Only)</label>
				  
				  <textarea name="Note" class="form-control" rows="2" cols="50"
                    id="Note"   
					placeholder="Enter Note" ><?php echo $RowCustomInvoice['Note'] ?></textarea>
				 		
		
                </div>	
				
				</div>
				
				
				
				
					<?php if($Count==0){	 ?>
			  <div class="col-xs-12 box-footer"  align="right">
			  <button type="button" id="close"  name="close"  class="btn btn-danger">Close </button>
            
  		
                <button hidden type="submit"  name='submit' id="Create" class="btn btn-primary">Add</button>
				<button type="button"  id="CreateInv" class="btn btn-primary">Add</button>
              </div>
					<?php } else{	 ?>
					
			  <div class="col-xs-12 box-footer"  align="right">
			  <button type="button" id="close"  name="close"  class="btn btn-danger">Close </button>
            
  		
                <button hidden type="submit"  name='submit' id="Create" class="btn btn-primary">Update</button>
			<button type="button"  id="CreateInv" class="btn btn-primary">Update</button>

<button id="<?php echo  $PIId;?>" style="width:100px;"
	onClick="reply_click(this.id)" class="btn btn-warning "> Delete</button>
	
								 


              </div>
					<?php } ?>

            </form>
       <!-- end col -->
                        </div> <!-- end row -->
    
	</div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->
                        </div>
                       


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

 $('#close').click(function(e) {
 window.close();
 });
function reply_click(clicked_id) {

   		var r = confirm("Are You Sure Do You Want To Delete "+clicked_id+"?");
  if (r == true) { 
		 //alert(clicked_id);		  
		$.ajax({
		  url:'Ajax/AjaxDelete.php',
		  type:'POST',
		  data:{'TYPE':'CustomDelete','PiID':clicked_id},
		  success:function(data){
			  
			 alert("Deleted Successfully");
			 window.close();
			
			}
			});	

} else {
			
  }			
  }

</script>

<script>

function showData(data){
			
					//$("#PIList").html(data);
 
 		var Stable=    $('#scroll-vertical-datatable').DataTable({
        "scrollY":        "450px",
        "scrollCollapse": true,
        "paging":         false,
        "language": {
            "paginate": {
                "previous": "<i class='mdi mdi-chevron-left'>",
                "next": "<i class='mdi mdi-chevron-right'>"
            }
        },
        "drawCallback": function () {
            $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
        },
		buttons: ['copy', 'excel', 'pdf', 'colvis']
    });
	
	Stable.buttons().container()
        .appendTo('#scroll-vertical-datatable_wrapper .col-md-6:eq(0)');
		}

</script>



<script>


 $(document).on('click','#AddGrossWtPerBrkUp',function(e){
	 			e.preventDefault();

	var PoNo=$(this).data('value');
	var PoID =$(this).data('id');
	  
	     $('#Update').val(PoID);	 
	     showPoPopUp(PoNo,PoID);
	  
    	});
		
function showPoPopUp(PoNo,PoID){
	
			 $.ajax({
				url:'Ajax/AjaxGrossWtPopUp.php',
				type:'POST',
				data:{'PoID':PoID},
				success:function(data){
				//  $("#Update").hide();

					$('.modal-title').text('PO Status Details - '+PoNo);
					$("#PopUpTable").html(data);

					$('#myModal').modal('show');

					var table = $('#key-datatable').DataTable({			 
						bSort : true,
						// scrollY: "380px",
						// scrollX:true,
						// sScrollXInner: "100%", 
						// paging:true,
						// destroy: true,
						// fixedColumns: true,
						// "bStateSave": true,
						// "fnStateSave": function (oSettings, oData) {
						// 	localStorage.setItem('key-datatable', JSON.stringify(oData));
						// },
						// "fnStateLoad": function (oSettings) {
						// 	return JSON.parse(localStorage.getItem('key-datatable'));
						// }
					});


				}
			});

}	

			  	
$(document).on('click','#Update',function(e){
	updateGrossWeight();
});
function updateGrossWeight(){
	
	  var InvoiceID='<?php echo $PIId?>';
	  var AryPackingStyleIds=<?php echo json_encode($AryPackingStyleIds); ?>;
	  var ClickedId =$(this).data('id');
	 
	  	  var AryBreakUp = [];
	  	 
	   
	   for (i = 0; i < AryPackingStyleIds.length; i++) {
		   ClickedId=AryPackingStyleIds[i];
		   
	  var GrossWeight='#GrossWeight'+ClickedId;
	  var GrossWeight=$(GrossWeight+"").val();
	 
	  
	  
	  var jsonData = {};
	  
	  jsonData['PckId'] = ClickedId;
	  jsonData['GrossWeight'] = GrossWeight;
	  
	  
	  AryBreakUp.push(jsonData);
					  
					  
	   }
	
	 
	   	
	 
		if(GrossWeight==''){
			alert('Please Enter Gross wt');
			return;	
		}
		
	
	$.ajax({
    type: 'POST',
	    dataType: "json",
    url: 'Ajax/AjCsUpdates.php',
    data: {'InvoiceID':InvoiceID,
	'Type':'GrosswtUpdate','AryBreakUp':JSON.stringify(AryBreakUp)},
	
    
     success:function(data){
			  
			  var obj = JSON.parse(JSON.stringify(data));  
		 if(obj.error == null){
			 $('#TotalGrossWt').val(obj.TotalGrossWt);
			 
			 alert("Updated Successfully");
				
		 }else{
			 alert(obj.error);
		 }
			  
			  

			}
  });

}	



$('#CreateInv').on('click',function(){
	
	
	var InvNo=$('#CInvNo').val();
	var InvDate=$('#CInvDate').val();
		 var submitButton = document.getElementById("Create");	
	if(InvNo==''){
		alert('Enter Invoice No');
		return;
	}else if(InvDate==''){
		alert('Enter Invoice Date')
		return;
	}
	
	var PoID=<?php echo $PIId ?>;
	$.ajax({
    type: 'POST',
	dataType: "json",
    url: 'Ajax/AjCheckInvNo.php',
    data: {'InvNo':InvNo,'InvDate':InvDate,'PoID':PoID},
    success:function(data){
	var obj = JSON.parse(JSON.stringify(data));  
		 if(obj.Error == null){ 
		  submitButton.click();
		
		 }else{			 
			 alert(obj.Error);
		 }
		 }
  });
		
});

</script>
