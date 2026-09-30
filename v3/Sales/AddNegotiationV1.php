<?php
$Disable='style="background: gray; color: white;"';

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

$PageTitleFolder="Sales";		
$PoID=$_GET['PIID'];

$AryPrices=getPaymentValues($iconn,$PoID);
$InvValueUSD=$AryPrices['InvValueUSD']; 


$SqlPaymentTerms="SELECT NoOfDays,ConditionDate FROM `ScrmCommercialData` as SCD  
left join `ScrmPaymentTerms` as SPT on SCD.ComPaymentTerms=SPT.ID WHERE PoID='$PoID'";
$StmtPaymentTerms=$iconn->query($SqlPaymentTerms);
$RowPaymentTerms=$StmtPaymentTerms->fetch_array();
$NoOfDays=$RowPaymentTerms['NoOfDays'];
$ConditionDate=$RowPaymentTerms['ConditionDate'];
//Globals
$DueDate=getPaymentDueDate($iconn,$NoOfDays,$ConditionDate,$PoID);


$SqlUtilisedValues="SELECT sum(UtilizedValue) as UtilizedValue FROM `ScrmCollectionBreakUP` WHERE PiID='$PoID'";
$StmtUtilisedValues=$iconn->query($SqlUtilisedValues);
$RowUtilisedValues=$StmtUtilisedValues->fetch_array();
$UtilizedValueOld=$RowUtilisedValues['UtilizedValue'];
$UtilizedValueOld=$RowUtilisedValues['UtilizedValue'];

$ShortFallExcCharges=$InvValueUSD-$UtilizedValueOld;


//(ShortFall+EtraAmount)-(BnkChrges+OtherAmount);

$SqlPI="SELECT * FROM `ScrmPoData` SPID 
LEFT JOIN ScrmCustomInvoices SCI ON SCI.PiID=SPID.PoID
LEFT JOIN ScrmBuyerDetails SBD ON SBD.BuyerID=SPID.BuyerID
WHERE SPID.PoID='$PoID'"; 
$StmtPI=$iconn->query($SqlPI);
$RowPI=$StmtPI->fetch_array();
$ExporterID=$RowPI['ExporterID'];
$PoID=$RowPI['PoID'];


$PageTitle="Negotiations (".getInvoiceNumber($iconn,$ExporterID,$PoID).')';


$BuyerName=$RowPI['BuyerName'];

$SqlPaymentTerms="SELECT * FROM `ScrmPaymentTerms` WHERE Status='1'";
$StmtPaymentTerms=$iconn->query($SqlPaymentTerms);

$SqlCollections="SELECT * FROM `ScrmPaymentCollections` WHERE PIID='$PoID'";
$StmtCollections=$iconn->query($SqlCollections);
$Count=mysqli_num_rows($StmtCollections);




$SqlFcDetails="SELECT * FROM `ScrmForwardContractsDetails` where Status=1";
$StmtFcDetails=$iconn->query($SqlFcDetails);

 $SqlSbDetails="SELECT ShippingBillDate,ETA_POD FROM `ScrmShippingBillDetails` as SB 
 left join ScrmISFDetails as ISF on ISF.PIID=SB.InvID where InvID='$PoID'";
$StmtSbDetails=$iconn->query($SqlSbDetails);
$RowSb=$StmtSbDetails->fetch_array();
 $ShippingBillDate=$RowSb['ShippingBillDate'];
 $ETA_POD=$RowSb['ETA_POD'];

	
$RowCollections=$StmtCollections->fetch_array();

if($RowCollections['DueDte']!=''){
	$DueDate=$RowCollections['DueDte'];
}
$ShortFall=$InvValueUSD-$UtilizedValueOld+$RowCollections['EtraAmount']-$RowCollections['BnkChrges']
						-$RowCollections['OtherAmount'];

$AryPriceWeightData=getInvoiceWeight($iconn,$GradeID,$PoID);
$AryPriceData=getPaymentValues($iconn,$PoID);

 $NetWeight=$AryPriceWeightData['Qty']; 
 $GrossWeight=$AryPriceWeightData['GrossWeightKgs'];
 $InvValueUSD=$AryPriceData['InvValueUSD'];

extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){	
 $ShortFall=$InvValueUSD+$EtraAmount-$BnkChrges-$UtilizedValue-$UtilizedValueOld-$OtherAmount;

if($ShortFall==0){
	$Status=1;
}else{
	$Status=0;
}



  	 if($Count==0){	 
    $SqlInsert="INSERT INTO `ScrmPaymentCollections`( 
	`PIID`, `CollectionRte`,
	`DueDte`,  `ETAPOD`, `BLDate`,`BnkChrges`, `OtherAmount`, `EtraAmount`, 
	`Comments`,`Status`)		   
	VALUES ('$PoID','$CollectionRte',
	'$DueDte','$ETAPOD','$BLDate','$BnkChrges','$OtherAmount',
	'$EtraAmount','$Comments','$Status')"; 
	
	 }else{
		$oldPcData = legacyAuditFetchRowById('ScrmPaymentCollections', 'PiID', $PoID, 'Sales');
		
       $SqlInsert= "UPDATE `ScrmPaymentCollections` SET 
	  `CollectionRte`='$CollectionRte',
      `DueDte`='$DueDte',
      `ETAPOD`='$ETAPOD',
      `BLDate`='$BLDate',
      `BnkChrges`='$BnkChrges',
      `OtherAmount`='$OtherAmount',
      `EtraAmount`='$EtraAmount',
      `Status`='$Status',
      `Comments`='$Comments'
	   WHERE `PiID`='$PoID'";	
		 
	 }
			
	$StmtInsert=$iconn->query($SqlInsert);
	
	
	
	if($StmtInsert){
		if($Count==0){
			legacyAuditLogInsertById('ScrmPaymentCollections', 'PiID', $PoID, $_POST, [
				'ReferenceNo' => $PoID,
				'Remarks' => 'Payment collection created',
			], 'Sales');
		}else{
			legacyAuditLogUpdateById('ScrmPaymentCollections', 'PiID', $PoID, $oldPcData, $_POST, [
				'ReferenceNo' => $PoID,
				'Remarks' => 'Payment collection updated',
			], 'Sales');
		}

		$SqlBreakUp="INSERT INTO `ScrmCollectionBreakUP`( `PiID`, `ColType`, `FcID`, 
		`FcNumber`, `BankRefNo`, `ColDate`, `UtilizedValue`, 
		`UtilizedDate`, `SpotRate`, `Col_Neg`) VALUES (
		'$PoID',
		'$ColType',
		'$FcID',
		'$FCNumber',
		'$BankRef',
		'$ColDate',
		'$UtilizedValue',
		'$UtilizedDate',
		'$SpotRate','1'
		) "; 
		$StmtInsert=$iconn->query($SqlBreakUp);
		if($StmtInsert){
			legacyAuditLogInsertById('ScrmCollectionBreakUP', 'PiID', $PoID, $_POST, [
				'ReferenceNo' => $PoID,
				'Remarks' => 'Collection breakup added in negotiation V1',
			], 'Sales');
		}
	}	

if($StmtInsert){
	$oldFcV1Data = legacyAuditFetchRowById('ScrmForwardContractsDetails', 'FCNumber', $FCNumber, 'Sales');
	
	$SqlUtilisedValue="SELECT NetValue,
	(select sum(UtilizedValue) from ScrmCollectionBreakUP where FCNumber='$FCNumber') as UtilizedValue
	FROM `ScrmForwardContractsDetails` where FCNumber='$FCNumber'";
	
	$StmtUtilisedValue=$iconn->query($SqlUtilisedValue);
	$RowUtilised=$StmtUtilisedValue->fetch_array();
	$NetValue=$RowUtilised['NetValue'];
	$UtilizedValue=$RowUtilised['UtilizedValue'];
	$Balance=$NetValue-$UtilizedValue;

 $SqlFcDetails="UPDATE `ScrmForwardContractsDetails` SET `UtilizedValue` ='$UtilizedValue',
 BalancedValue='$Balance' where FCNumber='$FCNumber'";
$StmtFcDetails=$iconn->query($SqlFcDetails);
	if($StmtFcDetails){
		legacyAuditLogUpdateById('ScrmForwardContractsDetails', 'FCNumber', $FCNumber, $oldFcV1Data, $_POST, [
			'ReferenceNo' => $FCNumber,
			'Remarks' => 'Forward contract utilized value updated in negotiation V1',
		], 'Sales');
	}
	

}
	//echo $SqlFcDetails; exit;


	if($StmtInsert){
	
	echo '<script language="javascript" type="text/javascript"> 
                alert("Success");
                window.location = "CollectionsList.php";
        </script>';
		//header('Location: PIList.php');
		
					 		 //automail($PIId,"1","");
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
								
								<form role="form" onsubmit="return validateForm()" method="post">
                                    <div class="card-body">
									
									<!--<h5 style="color:Brown;"><?php echo $BuyerName;?>/Wt:<?php echo $NetWeight;?>/USD:<?php echo $InvValueUSD;?>$</h5>
				-->
										<div class="row">
										
										
				<!---<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Payment Terms<span style="color:red">*</span></label>
                 
				   <select  required class="form-control select2" name="ByPayment" id="ByPayment">
				  <option value="">Select Payment</option>
				   <?php 
				   $PaymentTerms=$RowPI['PaymentTerms'];
				   
				   while ($RowPayment=$StmtPaymentTerms->fetch_array()){ ?>
				     <option 
					 <?php if($PaymentTerms==$RowPayment['ID']){ ?> selected="selected"  <?php } ?>
					 value="<?php echo $RowPayment['ID'] ?>">
					 <?php echo $RowPayment['PaymentName']?></option>
				   
				 <?php  }?>	  
				  </select>
                </div> -->
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">BL Date</label>
				<input <?php echo $Disable ?> readonly  type="date" class="form-control" name='BLDate' id="BLDate" 
				  placeholder="" value="<?PHP ECHO $ShippingBillDate ?>">			
                </div>
					
					<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">ETA POD</label>
				<input <?php echo $Disable ?> readonly type="date" class="form-control" name='ETAPOD' id="ETAPOD" 
				  placeholder="" value="<?PHP ECHO $ETA_POD ?>">			
                </div>
				
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Invoice Value</label>
				<input <?php echo $Disable ?> readonly type="decimal" class="form-control" name='InvValueUSD' id="InvValueUSD" 
				  placeholder="Enter Short Fall " value="<?PHP ECHO $InvValueUSD ?>">
                </div>
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Short Fall</label>
				<input <?php echo $Disable ?> readonly type="decimal" class="form-control" name='ShortFall' id="ShortFall" 
				  placeholder="Enter Short Fall " value="<?PHP ECHO $ShortFall ?>">
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Collection Rate<span style="color:red">*</span></label>
				<input required type="decimal" class="form-control" name='CollectionRte' id="CollectionRte" 
				  placeholder="Enter Collection Rate" value="<?PHP ECHO $RowCollections['CollectionRte'] ?>">
                </div>
	

				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Due Date<span style="color:red">*</span></label>
				<input required type="date" class="form-control" name='DueDte' id="DueDte" 
				  placeholder="" value="<?PHP ECHO $DueDate ?>">			
                </div>
				
					
				
	
				
				

					
				
			<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Bank Charges</label>
				<input  type="decimal" class="form-control" name='BnkChrges' id="BnkChrges" 
				  placeholder="Enter Bank Charges" value="<?PHP ECHO $RowCollections['BnkChrges'] ?>">
                </div>
					
						
					
						<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Other Amount(Deductions)</label>
				<input type="decimal" class="form-control" name='OtherAmount' id="OtherAmount" 
				  placeholder="Enter Other Amount" value="<?PHP ECHO $RowCollections['OtherAmount'] ?>">
                </div>	
					
					<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Extra Amount</label>
				<input  type="decimal" class="form-control" name='EtraAmount' id="EtraAmount" 
				  placeholder="Enter Extra Amount " value="<?PHP ECHO $RowCollections['EtraAmount'] ?>">
                </div>
					
					<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Comments</label>
			 <input  type="text" class="form-control" name='Comments' id="Comments" 
				  placeholder="Comments" value="<?PHP ECHO $RowCollections['Comments'] ?>">	
                </div>
				
				</div>
				
				<hr>
				
				
				<div class="row">
				<div class="form-group col-lg-3" > 
				<label for="exampleInputEmail1">Payment Mode
				<span style="color:red">*</span></label>
                 <select required class="form-control select2" id="ColType" name="ColType" >
				  <option  value="">Select</option>
				 
				 <option <?php if($RowSelect['PaymentMode']=='Forward Contract') {?>selected="selected"<?php } ?>			  
				  value="Forward Contract">Forward Contract</option>
				  
				  <option  <?php if($RowSelect['PaymentMode']=='Spot Rate') {?>selected="selected"<?php } ?>
				  value="Spot Rate">Spot Rate </option>
				
				
						</select>
					</div>
					
						<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Forward Contract Number<span style="color:red">*</span></label>
                 <input hidden id="FcID" name="FcID" value="" >
				   <select  required class="form-control select2" name="FCNumber" id="FCNumber">
				  <option value="">Forward Contract Number</option>
				   <?php 
				   
				   
				   while ($RowFcDetails=$StmtFcDetails->fetch_array()){ ?>
				     <option 
					 
					 value="<?php echo $RowFcDetails['FCNumber'] ?>">
					 <?php echo $RowFcDetails['FCNumber']?></option>
				   
				 <?php  }?>	  
				  </select>
                </div> 
					
					
					<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Bank Ref No<span style="color:red">*</span></label>
				<input required type="text" class="form-control" name='BankRef' id="BankRef" 
				  placeholder="" value="">			
                </div>	
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Collection Date:<span style="color:red">*</span></label>
				<input required type="date" class="form-control" name='ColDate' id="ColDate" 
				  placeholder="" value="">			
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Utilized Value $<span style="color:red">*</span></label>
				<input required type="text" class="form-control" name='UtilizedValue' id="UtilizedValue" 
				  placeholder="" value="">			
                </div>

				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Utilized Date<span style="color:red">*</span></label>
				<input required type="date" class="form-control" name='UtilizedDate' id="UtilizedDate" 
				  placeholder="" value="">			
                </div>	
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Spot Rate:<span style="color:red">*</span></label>
				<!--<input required type="number" class="form-control" name='SpotRate' id="SpotRate" 
				  placeholder="" value="">-->	

<input required class="form-control" oninput="" step="0.01" value="0.00" type="number" name="SpotRate" id="SpotRate">				  
                </div>	
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Balance<span style="color:red">*</span></label>
				<input readonly <?php echo $Disable ?> required type="number" class="form-control" name='Balance' id="Balance" 
				  placeholder="" value="">			
                </div>	
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Value INR</label>
				<input readonly  <?php echo $Disable ?>type="number" class="form-control" 
				name='ValInr' id="ValInr" 
				  placeholder="" value="">			
                </div>	
				 
				
				</div>
				
				
				<div class="col-xs-12 box-footer"  align="right">
			  <a target="" href="CollectionsList.php"
       class="btn btn-danger  "> Close</a>
				
					<?php if($Count==0){	 ?>
			  
  		
             <button type="submit"  name='submit' class="btn btn-primary">
			 Add</button>
			 
              
					<?php } else{	 ?>
					
			 
  		       <button type="submit"  name='submit' class="btn btn-primary">
			 Update</button>
                				 


              
					<?php } ?>
					</div>

            </form>
       <!-- end col -->
                        </div> <!-- end row -->
    
	</div> <!-- end card body-->
	
	
					
                            <div class="col-12">

						
        <div class="card">
          
        <div class="card-body" id="ShowDataTable">
                    

  <table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">
      <tr>
	  
	  
	  <th>Transaction Type</th>
	  <th>Transaction Ref.No</th>
	  <th>Utilised Value</th>
      <th>Utilized Date</th>
	  <th>Rate</th>
	  <th>Utilized in INR</th>
	 
	  <th> Actions</th>
	  	
      </tr>
    </thead>
    <tbody>
	<?php
	//echo $sql;
	$SqlPaymentBrkUp="SELECT * FROM `ScrmCollectionBreakUP` WHERE PiID='$PIID'";
	$StmtPaymentBrkUp=$iconn->query($SqlPaymentBrkUp);
	
	$TotalUtilizedValue=0;
	$TotalUtilizedValueinINR=0;
   while($row=$StmtPaymentBrkUp->fetch_array()){
	  $PO_ID=$row['ID'];  
	  $UtilizedinINR=$row['UtilizedValue']*$row['SpotRate'];  
	  $UtilizedValue=$row['UtilizedValue'];
	  $TotalUtilizedValue +=$UtilizedValue;
	  $TotalUtilizedValueinINR +=$UtilizedinINR;
	
	?>
	<tr>			
	
	<td><?php echo $row['ColType']; ?> </td>	
	<td><?php echo $row['BankRefNo']; ?> </td>	
	<td><?php echo $row['UtilizedValue']; ?> $</td>	
	<td><?php echo $row['UtilizedDate']; ?> </td>	
	<td><?php echo $row['SpotRate']; ?> </td>	
	<td><?php echo $UtilizedinINR; ?> </td>	
	
	<td>
	
	<button class="btn btn-link text-danger Delete" data-id="<?php echo $row['ColBrkID'];?>" data-value="<?php echo $row['FcNumber'];?>"		
	data-toggle="tooltip" data-placement="top" title="" value=""
	data-original-title="Delete"><i class="mdi mdi-trash-can font-size-18"></i></button>						
	
	</td>
       

        </tr>

	  <?php 

   }
	  ?>
	  
	  <tr>
	  <td><span style="color:red">Total</span></td>	
	<td></td>	
	<td><?php echo $TotalUtilizedValue; ?> $</td>	
	<td></td>	
	<td></td>	
	<td><?php echo $TotalUtilizedValueinINR; ?> </td>	
	
	<td></td>
	  </tr>

     </tbody>

  </table>
</div>
    
	</div> <!-- end card body-->
                                </div> <!-- end card -->
	
	
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
	  var CollectionBreakUpID =$(this).data('id');
	  var FcNumber =$(this).data('value');
      var PoID='<?php echo $PIID; ?>';

   		var r = confirm("Do you want to delete this ?");
  if (r == true) { 
		 	  
		$.ajax({
		  url:'Ajax/AjaxDeleteCollections.php',
		  type:'POST',
		  data:{'TYPE':'CollectionBreakUpdelete','FcNumber':FcNumber,'CollectionBreakUpID':CollectionBreakUpID,'PoID':PoID},
		    
    success: function(response){
		var data=JSON.parse(response);
			if(data['sts']=='Failed'){
			alert(data['msg']);
			
			}else{
				$('#myModal').modal('hide');
			alert(data['msg']);
			location.reload();
				
			}
	 }
			});	

} else {
			
  }			
  });


function setTwoNumberDecimal(el) {
        el.value = parseFloat(el.value).toFixed(5);
    };

function validateForm(){

var ShortFall ='<?php echo $ShortFallExcCharges ?>';
//var ShortFall =$("#ShortFall").val();
var UtilizedValue =$("#UtilizedValue").val();
var BnkChrges =$("#BnkChrges").val();
var EtraAmount =$("#EtraAmount").val();
var OtherAmount =$("#OtherAmount").val();
var Balance =$("#Balance").val();
var ColType =$("#ColType").val();
var TotalExtraAmount=Number(ShortFall)+Number(EtraAmount)-Number(BnkChrges)-Number(OtherAmount);


	if(ShortFall=='0'){
	alert('No Short Fall Amount');
	return false;
	}else if(UtilizedValue > TotalExtraAmount ){
	alert('Utilized Value Should Not Exceed '+TotalExtraAmount);
	return false;
	}else if(ColType=='Forward Contract' && UtilizedValue > Balance){
	alert('Utilized Value Should Not Exceed '+Balance);
	return false;
	}else {
	return true;
	}		
}

function updateINR(){
	
	UtilizedValue =$("#UtilizedValue").val();
	SpotRate =$("#SpotRate").val();
	$('#ValInr').val(Math.round((UtilizedValue*SpotRate)));	
}

		
		 
		$("#BnkChrges").change(function(e){
			e.preventDefault();	
			updateShortFall();
		});	
		$("#OtherAmount").change(function(e){
			e.preventDefault();	
			updateShortFall();
		});	
		
		$("#EtraAmount").change(function(e){
			e.preventDefault();	
			updateShortFall();
		});	
	

function updateShortFall(){
	
	var SFallExcChar='<?php echo $ShortFallExcCharges ?>';
	var BnkChrges =$("#BnkChrges").val();
	var EtraAmount =$("#EtraAmount").val();
	var OtherAmount =$("#OtherAmount").val();
	var ShortFallExcCharges=Number(SFallExcChar)+Number(EtraAmount)-Number(BnkChrges)-Number(OtherAmount);
	$('#ShortFall').val((ShortFallExcCharges));	
}	
		
		
		$("#UtilizedValue").change(function(e){
			e.preventDefault();	
			updateINR();
		});	
		
		$("#SpotRate").change(function(e){
			e.preventDefault();	
			updateINR();
		});	

		
		$("#FCNumber").change(function(e){
			e.preventDefault();	
			getFcNumber();
		});	
		
		$("#ColType").change(function(e){
			e.preventDefault();	
			
			var ColType =$("#ColType").val();
			
			if(ColType!='Forward Contract'){
				$('#CollectionRte').prop('required',false);
				document.getElementById("FCNumber").disabled=true;
				//$("#FCNumber").val("");
				 document.getElementById("SpotRate").readOnly =false;
				 document.getElementById("SpotRate").style.backgroundColor='WHITE'; 
				 document.getElementById("SpotRate").style.color='black'; 
				$('#FCNumber').val('').change();
				$('#SpotRate').val('');
				$('#Balance').val('0');
				$('#CollectionRte').val('');	 
				$('#FcID').val('');				
			}else{
				$('#CollectionRte').prop('required',true);
				document.getElementById("FCNumber").disabled=false;
				 $('#SpotRate').disabled=true;
				 document.getElementById("SpotRate").readOnly =true;
				  document.getElementById("SpotRate").style.backgroundColor='GRAY';
				document.getElementById("SpotRate").style.color='white'; 				  
			}
			
		});




function getFcNumber(){


var FCNumber=$('#FCNumber').val(); 
var $PoID=<?php echo $PoID ?> 

//alert(FCNumber);
	
	if(FCNumber==''){
		return;
	}
 
	  $.ajax({
		  url:'Ajax/AjaxFcNumberDetails.php',
		  type:'POST',
		  dataType: 'json',
		  data:{'FCNumber':FCNumber,'PoID':$PoID},
		  success:function(response){
			var myJSON =  JSON.parse(JSON.stringify(response));
		    var NetValue=myJSON['NetValue'];  		  
		    var WithDrawFcBalance=myJSON['WithDrawFcBalance'];  		  
		    var FCID=myJSON['FCID'];  		  
		    var UtilizedValue=myJSON['UtilizedValue'];  		  
		    var BalancedValue=myJSON['BalancedValue'];  		  
		    var TotalRate=myJSON['TotalRate'];  		  
		    var SpotRte=myJSON['SpotRte'];  		  
		    var Balance=myJSON['Balance'];  		  
			$('#FcID').val(FCID);	  
			$('#UtilizedValue').val(WithDrawFcBalance);	  
			$('#SpotRate').val(TotalRate);	  
			$('#CollectionRte').val(TotalRate);	  
			$('#Balance').val(Balance);	  
			$('#ValInr').val(TotalRate*WithDrawFcBalance);	  
				
				//alert(UtilizedValue);
			}
			});			  
  }


</script>



	
