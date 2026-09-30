<?php
$Disable='style="background: gray; color: white;"';

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");
 
$PageTitleFolder="Sales";		
$PoID=$_GET['PIID'];

//From Global

$AryPrices=getPaymentValues($iconn,$PoID);
$InvValueUSD=$AryPrices['InvValueUSD'];


$SqlUtilisedValues="SELECT sum(UtilizedValue) as UtilizedValue FROM
 `ScrmCollectionBreakUP` WHERE PiID='$PoID'";
$StmtUtilisedValues=$iconn->query($SqlUtilisedValues);
$RowUtilisedValues=$StmtUtilisedValues->fetch_array();
$UtilizedValue=$RowUtilisedValues['UtilizedValue'];

$ShortFall=$InvValueUSD-$UtilizedValue;

$SqlPI="SELECT * FROM `ScrmPoData` SPID 
LEFT JOIN ScrmCustomInvoices SCI ON SCI.PiID=SPID.PoID
LEFT JOIN ScrmBuyerDetails SBD ON SBD.BuyerID=SPID.BuyerID
WHERE SPID.PoID='$PoID'"; 
$StmtPI=$iconn->query($SqlPI);
$RowPI=$StmtPI->fetch_array();
$ExpID=$RowPI['ExporterID'];
$CInvNo=getInvoiceNumber($iconn,$ExpID,$PoID);

 
$PageTitle="Negotiations (".$CInvNo.')';


$BuyerName=$RowPI['BuyerName'];

$SqlPaymentTerms="SELECT * FROM `ScrmPaymentTerms` WHERE Status='1'";
$StmtPaymentTerms=$iconn->query($SqlPaymentTerms);

$SqlNegotitions="SELECT * FROM `ScrmPaymentNegotitions` WHERE PIID='$PoID'";
$StmtNegotitions=$iconn->query($SqlNegotitions);
$Count=mysqli_num_rows($StmtNegotitions);

$SqlFcDetails="SELECT * FROM `ScrmForwardContractsDetails` where Status=1";
$StmtFcDetails=$iconn->query($SqlFcDetails);

	
//$RowNegotitions=$StmtNegotitions->fetch_array();


$AryPriceWeightData=getInvoiceWeight($iconn,$GradeID,$PoID);
$AryPriceData=getPaymentValues($iconn,$PoID);

 $NetWeight=$AryPriceWeightData['Qty']; 
 $GrossWeight=$AryPriceWeightData['GrossWeightKgs'];
 $InvValueUSD=$AryPriceData['InvValueUSD'];

extract($_POST);
extract($_GET);




if(isset($_POST['submit'])){	
		


		if($Count==0){
	
        $SqlInsert="INSERT INTO `ScrmPaymentNegotitions`( `PIID`, `NegoDate`, 
		`NegoRate`, `BnkRefNo`, `DueDate`, `BankDueDate`, `TrnsctnType`)
		VALUES('$PIID','$NegoDate','$NegoRate','$BnkRefNo','$DueDate',
	    '$BankDueDate','$TrnsctnType')"; 
	
	}else{
		$oldNegData = legacyAuditFetchRowById('ScrmPaymentNegotitions', 'PIID', $PIID, 'Sales');
				
		$SqlInsert= "UPDATE `ScrmPaymentNegotitions` SET
		`NegoDate`='$NegoDate',
        `NegoRate`='$NegoRate',
		`BnkRefNo`='$BnkRefNo',
		 `DueDate`='$DueDate',
		 `BankDueDate`='$BankDueDate',
		 `TrnsctnType`='$TrnsctnType' 
		 WHERE `PIID`='$PIID'"; 
 }
		
		$StmtInsert=$iconn->query($SqlInsert);
		if($StmtInsert){
			if($Count==0){
				legacyAuditLogInsertById('ScrmPaymentNegotitions', 'PIID', $PIID, $_POST, [
					'ReferenceNo' => $PIID,
					'Remarks' => 'Negotiation created',
				], 'Sales');
			}else{
				legacyAuditLogUpdateById('ScrmPaymentNegotitions', 'PIID', $PIID, $oldNegData, $_POST, [
					'ReferenceNo' => $PIID,
					'Remarks' => 'Negotiation updated',
				], 'Sales');
			}

		$SqlBreakUp="INSERT INTO `ScrmCollectionBreakUP`( `PiID`, `ColType`, `FcID`, 
		`FcNumber`, `BankRefNo`, `ColDate`, `UtilizedValue`, 
		`UtilizedDate`, `SpotRate`,`Col_Neg`) VALUES (
		'$PoID',
		'$ColType',
		'$FcID',
		'$FCNumber',
		'$BankRef',
		'$ColDate',
		'$UtilizedValue',
		'$UtilizedDate',
		'$SpotRate','2')"; 
		$StmtInsert=$iconn->query($SqlBreakUp);
		if($StmtInsert){
			legacyAuditLogInsertById('ScrmCollectionBreakUP', 'PiID', $PoID, $_POST, [
				'ReferenceNo' => $PoID,
				'Remarks' => 'Collection breakup added',
			], 'Sales');
		}
	}

if($StmtInsert){
	$oldFcData = legacyAuditFetchRowById('ScrmForwardContractsDetails', 'FCNumber', $FCNumber, 'Sales');

 $SqlFcDetails="UPDATE `ScrmForwardContractsDetails` SET `UtilizedValue` =
 (select sum(UtilizedValue) from ScrmNegonBreakUP where FCNumber='$FCNumber')
 where FCNumber='$FCNumber'";
$StmtFcDetails=$iconn->query($SqlFcDetails);
	if($StmtFcDetails){
		legacyAuditLogUpdateById('ScrmForwardContractsDetails', 'FCNumber', $FCNumber, $oldFcData, $_POST, [
			'ReferenceNo' => $FCNumber,
			'Remarks' => 'Forward contract utilized value updated',
		], 'Sales');
	}
	

}	
      if($StmtInsert){
	echo '<script language="javascript" type="text/javascript"> 
       alert("Successfully Updated");

                window.location = "CollectionsList.php?PymtType=Negotiations";
        </script>';
					 		 
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

                        <?php include ($IncludesPath."page-title.php");?>

         
	
<div class="row">
                            <div class="col-12">
                                <div class="card">
								
								<form role="form" onsubmit="return validateForm()" method="post">
                                    <div class="card-body">
									
									<!--<h5 style="color:Brown;"><?php echo $BuyerName;?>/Wt:<?php echo $NetWeight;?>/USD:<?php echo $InvValueUSD;?>$</h5>-->
				
										<div class="row">
										
										
			<!--	<div class="form-group col-lg-3">
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
                  <label for="exampleInputEmail1">Negotiation Rate</label>
				<input  type="decimal" class="form-control" name='NegoRate' id="NegoRate" 
				  placeholder="Enter Negotiation  Rate" value="<?PHP ECHO $RowNegotiations['NegoRate'] ?>">
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Bank Ref No</label>
				<input  type="decimal" class="form-control" name='BnkRefNo' id="BnkRefNo" 
				  placeholder="Enter Bank Ref No" value="<?PHP ECHO $RowNegotiations['BnkRefNo'] ?>">
                </div>
	
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Short Fall</label>
				<input <?php echo $Disable ?> readonly type="decimal" class="form-control" name='ShortFall' id="ShortFall" 
				  placeholder="Enter Short Fall " value="<?PHP ECHO $ShortFall ?>">
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Negotiation Date<span style="color:red">*</span></label>
				<input required type="date" class="form-control" name='NegoDate' id="NegoDate" 
				  placeholder="" value="<?PHP ECHO $RowNegotiations['NegoDate'] ?>">			
                </div>
				

				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Due Date<span style="color:red">*</span></label>
				<input required type="date" class="form-control" name='DueDte' id="DueDte" 
				  placeholder="" value="<?PHP ECHO $RowNegotiations['DueDte'] ?>">			
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1"> Bank Due Date<span style="color:red">*</span></label>
				<input required type="date" class="form-control" name='BankDueDate' id="BankDueDate" 
				  placeholder="" value="<?PHP ECHO $RowNegotiations['BankDueDate'] ?>">			
                </div>	
				
					<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">ETA POD</label>
				<input  type="date" class="form-control" name='ETAPOD' id="ETAPOD" 
				  placeholder="" value="<?PHP ECHO $RowNegotiations['ETAPOD'] ?>">			
                </div>
				
					<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">BL Date<span style="color:red">*</span></label>
				<input required type="date" class="form-control" name='BLDate' id="BLDate" 
				  placeholder="" value="<?PHP ECHO $RowNegotiations['BLDate'] ?>">			
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Extra Amount</label>
				<input  type="decimal" class="form-control" name='EtraAmount' id="EtraAmount" 
				  placeholder="Enter Extra Amount " value="<?PHP ECHO $RowNegotiations['EtraAmount'] ?>">
                </div>	
					
						<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Other Amount</label>
				<input type="decimal" class="form-control" name='OtherAmount' id="OtherAmount" 
				  placeholder="Enter Other Amount" value="<?PHP ECHO $RowNegotiations['OtherAmount'] ?>">
                </div>	
					
					
					<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Comments</label>
			 <input  type="text" class="form-control" name='Comments' id="Comments" 
				  placeholder="Comments" value="<?PHP ECHO $RowNegotiations['Comments'] ?>">	
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

<input class="form-control" oninput="" step="0.01" value="0.00" type="number" name="SpotRate" id="SpotRate">				  
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
			  <a target="" href="NegotiationList.php"
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
   while($row=$StmtPaymentBrkUp->fetch_array()){
	  $PO_ID=$row['ID'];  
	  $UtilizedinINR=$row['UtilizedValue']*$row['SpotRate'];  
	
	
	?>
	<tr>			
	
	<td><?php echo $row['ColType']; ?> </td>	
	<td><?php echo $row['BankRefNo']; ?> </td>	
	<td><?php echo $row['UtilizedValue']; ?> $</td>	
	<td><?php echo $row['UtilizedDate']; ?> </td>	
	<td><?php echo $row['SpotRate']; ?> </td>	
	<td><?php echo $UtilizedinINR; ?> </td>	
	
	<td>
	
	<button class="btn btn-link text-danger Delete" data-id="<?php echo $row['ColBrkID'];?>" 		
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
	  var PIID =$(this).data('id');


   		var r = confirm("Do you Want To Delete This PI "+PIID+"?");
  if (r == true) { 
		 	  
		$.ajax({
		 // url:'Ajax/AjaxDelete.php',
		  type:'POST',
		  data:{'TYPE':'PI_DELETE','D_ID':PIID},
		  success:function(data){
			  console.log(data);
			  alert("Deleted");
			  		
	//getPiList();

			}
			});	

} else {
			
  }			
  });
  
 
  




function setTwoNumberDecimal(el) {
        el.value = parseFloat(el.value).toFixed(5);
    };

function validateForm(){
var ShortFall =$("#ShortFall").val();
var UtilizedValue =$("#UtilizedValue").val();
	if(ShortFall=='0'){
	alert('No Short Fall Amount');
	return false;
	}else if(UtilizedValue > ShortFall ){
	alert('Utilized Value Should Below Short Fall Amount');
	return false;
	}else{
	return true;
	}		
}

function updateINR(){
	
	UtilizedValue =$("#UtilizedValue").val();
	SpotRate =$("#SpotRate").val();
	$('#ValInr').val(UtilizedValue*SpotRate);	
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
				document.getElementById("FCNumber").disabled=true;
				//$("#FCNumber").val("");
				 document.getElementById("SpotRate").readOnly =false;
				 document.getElementById("SpotRate").style.backgroundColor='WHITE'; 
				 document.getElementById("SpotRate").style.color='black'; 
				$('#FCNumber').val('').change();
				$('#SpotRate').val('0');
				$('#Balance').val('0');	
			}else{
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
		    var UtilizedValue=myJSON['UtilizedValue'];  		  
		    var BalancedValue=myJSON['BalancedValue'];  		  
		    var TotalRate=myJSON['TotalRate'];  		  
		    var SpotRte=myJSON['SpotRte'];  		  
		    var Balance=myJSON['Balance'];  		  
			$('#UtilizedValue').val(WithDrawFcBalance);	  
			$('#SpotRate').val(TotalRate);	  
			$('#Balance').val(Balance);	  
			$('#ValInr').val(TotalRate*WithDrawFcBalance);	  
				
				//alert(UtilizedValue);
			}
			});			  
  }


</script>
