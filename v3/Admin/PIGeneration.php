

<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/SalesNew/Constants.php");


$PageTitleFolder="Sales";
$PageTitle="New Proforma Invoice";
$ContinueSeries=false;


//$PIId=$_GET['PIID'];

$SQL_OPTIONS="SELECT * FROM `OPTIONS` WHERE  DEPT='SALES' order by ABS(OPTIONS) asc ";
$STMT_OPTIONS=$iconn->query($SQL_OPTIONS);
$ARY_OPTIONS=array();
WHILE($ROW=$STMT_OPTIONS->FETCH_ARRAY()){
	$ARY_OPTIONS[]=$ROW;
}

	$sql_add_select="SELECT * FROM $TblAdminExporterDetails";
	$stmt_add_select=$iconn->query($sql_add_select);

	$SqlBuyer="SELECT * FROM $TblScrmConsigneeDetails";
	$StmtBuyer=$iconn->query($SqlBuyer);
	$StmtBuyer1=$iconn->query($SqlBuyer);
	
	$SqlBank="SELECT * FROM `AdminBankDetails` WHERE `BankStatus` = '1'";
	$StmtBank=$iconn->query($SqlBank);
	
    $SqlCountryList="SELECT * FROM $TblScrmCountriesList";
	$StmtCountryList=$iconn->query($SqlCountryList); 

	$SqlPaymentTerms="SELECT * FROM $TblScrmPaymentTerms";
	$StmtPaymentTerms=$iconn->query($SqlPaymentTerms);

$SqlAdminUsers="SELECT * FROM $TblAdminUserDetails WHERE DepartmentID=9 and PoUserStatus='1'";
	$StmtAdminUsers=$iconn->query($SqlAdminUsers);

		$AccAval=true;
			$CYear=date("Y");
			$AYear=$CYear+1;
			while($AccAval){
			$PoRefId=generateAccountId();
			$sql="SELECT count(*) as Total FROM `$TblScrmPoData` where PoRefId='$PoRefId'";
			$sqlca=$iconn->query($sql);
			$rlt = mysqli_fetch_array($sqlca,MYSQLI_ASSOC);
			if($rlt['Total'] == 0 ){
				$AccAval=false;
			}			
			}
	
	 function generateAccountId(){
				$generator = "1357902468"; 
                $result = ""; 
                for ($i = 1; $i <= 5; $i++) { 
                    $result .= substr($generator, (rand()%(strlen($generator))), 1); 
                }  
				
				return $result;
		}
		
	function checkInvoiceNo($iconn,$StartYear,$InvNo){
		
			  $EndYear=$StartYear+1;
			  $FinStartDate=$StartYear.'-04-01'; 
			  $FinEndDate=$EndYear.'-03-31';
	
	$sqlCount="SELECT count(*) as Count FROM `ScrmPIData` where InvDate BETWEEN '$FinStartDate' and '$FinEndDate' and
	InvNo='$InvNo' and ExporterID='$ExporterID'";
	
	$StmtCount=$iconn->query($sqlCount);
	$RowCount=$StmtCount->fetch_array();
	if($RowCount['Count']!=0){	
		return false;		
	}else{
		return true;
	}
  
		}

		

extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	 	
	$SqlCountryType="SELECT CountryType FROM `ScrmCountriesList` where id='$DestinationCountry' ";
	$StmtCountryType=$iconn->query($SqlCountryType);
	$RowCountryType=$StmtCountryType->fetch_array();
	
	$TypeCountry=$RowCountryType['CountryType'];

	$StartYear=date('Y',strtotime($InvDate));
	$LoginId=$_SESSION["UserID"];
	
	 for ($i=0; $i < $NoContainers ; $i++){
		$Series=$i+1;
		$GenInvNo=$InvNo+$i;
		$GenInvNo = sprintf( '%03d', $GenInvNo);
		if(checkInvoiceNo($iconn,$StartYear,$GenInvNo)){
			$SqlContainerValues[]="('$PoRefId','$GenInvNo','$InvDate','$ExporterID','$ProcessorID',
			'$LotNo','$SciName','$ConsigneeID','$BuyerID','$PoNo','$PoDate','$DestinationCountry',
			'$TypeCountry','$PoStatus','$StartYear','$BankID','$PortOfDischarge',
			'$FinalDestination','$ByPayment','$ShippingTerms','$DeliveryTerms',
			'$PreparedBy' ,'$PRInstrctns','$ShipByDate','0','$LoginId','$QtyUnit','$UnitCurrency'
			)";
		}else{
		$NoContainers++;
		}
		
		}
		
	 
	 
	 

	 
	 
	 $SqlContainerValues=implode(',',$SqlContainerValues);
	
	$sql_org_insert="INSERT INTO `$TblScrmPIData`( `PoRefId`, `InvNo`, `InvDate`, `ExporterID`, `ProcessorID`, `LotNo`, `SciName`,
`ConsigneeID`, `BuyerID`, `PoNo`, `PoDate`, `DestinationCountry`, `TypeCountry`, `PoStatus`, `Year`, 
`BankID`, `PortOfDischarge`, `FinalDestination`, `PaymentTerms`, `ShippingTerms`, `DeliveryTerms`, 
`PreparedBy`, `PRInstrctns`, `ShipByDate`,  `Status`,`LoginId`,`QtyUnit`,`UnitCurrency`) 
VALUES  $SqlContainerValues";
	 

 // echo $sql_org_insert; exit;

	$stmt1=$iconn->query($sql_org_insert);
	$INV_ID = $iconn->insert_id;
	legacyAuditLogInsertById($TblScrmPIData, 'PoID', $INV_ID, [], [], 'Admin');
//	}
	if($stmt1){
		if($PoNo!=''&&$PoDate!=''){
		$SqlShipmentInsert="INSERT INTO `ScrmShipmentDetailsBreif`(`PiID`,`AvailableDate`) VALUES('$INV_ID','$PoDate')";
        $StmtShipmentInsert=$iconn->query($SqlShipmentInsert);
		legacyAuditLogInsertById('ScrmShipmentDetailsBreif', 'ID', $iconn->insert_id, [], [], 'Admin');
		}
		
		header('Location: PoShipmentDetails.php?PIID='.$INV_ID);
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

			 <div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Select Processor</label>              			   
				  <select class="form-control select2" name="ProcessorID" id="ProcessorID">
				 
				<?php while($row_add=$stmt_add_select->fetch_array()) { 
				
				$ary_exp[]=$row_add;?>  
				
				<option Value="<?php echo $row_add['ExpID'];?>"><?php echo $row_add['ExpName'].'('.$row_add['ExpApprovalNo'].')';?></OPTION>
				
				<?php } ?>
				
				<select>   
                </div>
				
				
				 <div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Select Exporter</label>
               
				   <select class="form-control select2" name="ExporterID" id="ExporterID">
				  <?php foreach($ary_exp as $value ) { ?>  
				
				<option Value="<?php echo $value['ExpID'];?>"><?php echo $value['ExpName'].'('.$value['ExpApprovalNo'].')';?></OPTION>
				
				<?php } ?>
				<select>   
                </div>
				
				
					
				

		 
	
				
		
			  
			   <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Proforma Invoice No</label>
                  <input required  type="number" class="form-control" name='InvNo' 
				  id="InvNo" placeholder="Enter Proforma Invoice No" value="">
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Proforma Invoice Date</label>
                  <input required type="date" value="<?php  echo date("Y-m-d");?>" 
				  class="form-control" name='InvDate' id="InvDate" 
				  placeholder="Enter Proforma Invoice No ">
                </div>
			  
			  <div class="form-group col-lg-3">
                  <label for="exampleInputPassword1"> P.O No</label>
                  <input  type="text" class="form-control" name='PoNo' 
				  id="PoNo" placeholder="Enter P.O Number">
                </div> 
				
				<?php  //echo date("Y-m-d");?>
				
				<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1"> P.O Date</label>
                  <input  type="date" value="<?PHP ECHO DATE('Y-m-d') ?>" 
				  class="form-control" name='PoDate' id="PoDate" placeholder="Enter P.O Date">
                </div>
			  
			  
               				
		 	<div class="form-group col-lg-3">  
                  <label for="exampleInputPassword1">Bank Details</label>
                   <select required class="form-control select2" name="BankID" id="BankID">
				  <option value="">Select Bank</option>
				   <?php while ($RowBank=$StmtBank->fetch_array()){ ?>
				     <option value="<?php echo $RowBank['ID'] ?>">
					 <?php echo $RowBank['AccntName'].' - 
					 '.$RowBank['BankName'] .'-'.$RowBank['SwiftCode'] .'-'.$RowBank['AccountNumber']?></option>
				   
				 <?php  }?>	  
				  </select>
                </div>
		         
				<!--<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Scientific Name</label>
				  <select class="form-control select2" name="SciName" id="SciName">
				  <option value="PENAEUS VANNAMEI" <?php if($invoice_details['SCIENTIFIC_NAME']=='PENAEUS VANNAMEI')
					  echo 'selected="selected"'; ?>>PENAEUS VANNAMEI</option>
				   <option value="LITOPENAEUS VANNAMEI" <?php if($invoice_details['SCIENTIFIC_NAME']=='LITOPENAEUS VANNAMEI')
					  echo 'selected="selected"'; ?>>LITOPENAEUS VANNAMEI</option>
					 <option value="BLACK TIGER" <?php if($invoice_details['SCIENTIFIC_NAME']=='BLACK TIGER')
					  echo 'selected="selected"'; ?>>BLACK TIGER</option>
					 <option value="PENAEUS MONODON" <?php if($invoice_details['SCIENTIFIC_NAME']=='BLACK TIGER')
					  echo 'selected="selected"'; ?>>PENAEUS MONODON</option>
					   
		
				<select>               
                  
                </div>UMA(20/10/2022)-->
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Scientific Name</label>
                 
                <select class="form-control select2" name="SciName" id="SciName">
					 <?php foreach($ARY_OPTIONS as $value){
					if($value['TYPE']=='SCIENTIFIC NAME'){
						$OPTIONS= $value['OPTIONS'];
						$VALUE= $value['VALUE']; ?>
			 
					<option value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
					<?php  }}?>
				<select>
				
				</div>
				
				
			
				
			
		  <div class="form-group col-lg-3">  
                  <label for="exampleInputPassword1">Buyer Name</label>
                   <select  required class="form-control select2" name="BuyerID" id="BuyerID">
				  <option value="">Select Buyer</option>
				   <?php while ($Row_PO=$StmtBuyer1->fetch_array()){ ?>
				     <option value="<?php echo $Row_PO['ConID'] ?>">
					 <?php echo $Row_PO['ConsigneeName'].' - 
					 '.$Row_PO['ConsigneeBrand'] ?></option>
				   
				 <?php  }?>	  
				  </select>
                </div>


			   
			   <div class="form-group col-lg-3">  
                  <label for="exampleInputPassword1">Applicant/Ultimate Consignee</label>
                   <select  required class="form-control select2" name="ConsigneeID" id="ConsigneeID">
				  <option value="">Select Consignee</option>
				   <?php while ($Row_PO=$StmtBuyer->fetch_array()){ ?>
				     <option value="<?php echo $Row_PO['ConID'] ?>">
					 <?php echo $Row_PO['ConsigneeName'].' - 
					 '.$Row_PO['ConsigneeBrand'] ?></option>
				   
				 <?php  }?>	  
				  </select>
                </div>
				

		 
				
				
				
				 <div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Destination Country</label>
                 
				  
				  <select required class="form-control select2" name="DestinationCountry" id="DestinationCountry">
				  <option value="">Select Country</option>
				   <?php while ($RowCountry=$StmtCountryList->fetch_array()){ ?>
				     <option value="<?php echo $RowCountry['id'] ?>">
					 <?php echo strtoupper($RowCountry['name'])?></option>
				   
				 <?php  }?>	  
				  </select>
                </div>
					<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1"> Final Destination</label>
                  <input required type="text" class="form-control" name='FinalDestination' 
				  id="FinalDestination" placeholder="Enter Final Destination">
                </div>

				
				<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">No Of Containers</label>
                
				  
				  <div class="input-group" >
				 
    <span class="input-group-btn">
        <button class="btn btn-white btn-minuse" id="Cminus" type="button">-</button>
    </span>
   
	
	 <input readonly 	 required type="text" class="form-control no-padding add-color text-center height-25"
				  name="NoContainers"  id="NoContainers" 
				  placeholder="Enter Number Of Containers" maxlength="3" value="1" >
    <span class="input-group-btn">
        <button class="btn btn-red btn-pluss" id="Cplus" type="button">+</button>
    </span>
</div>
                </div>
				
				
				


				

				
				 
				
				
				<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Payment Term</label>
                 
				   <select  required class="form-control select2" name="ByPayment" id="ByPayment">
				  <option value="">Select Payment</option>
				   <?php while ($RowPayment=$StmtPaymentTerms->fetch_array()){
									  if($RowPayment['LcStatus']==1){
						 $LcStatus='LC';
  
									  }
									  else{
                         $LcStatus='Non LC';
	  
									  }
   ?>
				     <option value="<?php echo $RowPayment['ID'] ?>">
					 <?php echo $RowPayment['PaymentName'].' - '.$LcStatus?></option>
				   
				 <?php  }?>	  
				  </select>
                </div> 

				
					
			 
				
					
				
				
              <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Shipment Terms</label>
                 
                <select class="form-control select2" name="ShippingTerms" id="ShippingTerms">
					 <?php foreach($ARY_OPTIONS as $value){
					if($value['TYPE']=='PAYMENT_TERMS'){
						$OPTIONS= $value['OPTIONS'];
						$VALUE= $value['VALUE']; ?>
			 
					<option value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
					<?php  }}?>
				<select>
				
				</div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Delivery Terms</label>
				  
				   <input required type="text" class="form-control" name='DeliveryTerms' 
				  id="DeliveryTerms" placeholder="Enter Delivery Term">
                 
            
				</div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Quantity Units</label>
                 
                <select class="form-control select2" name="QtyUnit" id="QtyUnit">
					 <?php foreach($ARY_OPTIONS as $value){
					if($value['TYPE']=='UOM'){
						$OPTIONS= $value['OPTIONS'];
						$VALUE= $value['VALUE']; ?>
			 
					<option value="<?php echo $VALUE ?>"><?php echo $OPTIONS ?></option>
					<?php  }}?>
				<select>
				
				</div>
				
				<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Currency</label>
				  <select  required class="form-control select2" name="UnitCurrency" id="UnitCurrency">
                  <option value="">Select Currency</option>
                  <option value="Dollar">US Dollar</option>
				  </select>
                </div>
				
				
				
				
				
				
				<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1"> Prepared By</label>
                 
                <select required class="form-control select2" name="PreparedBy" id="PreparedBy">
					  <option   value="">Select Prepared By</option>
					<?php while($RowAdminUsers=$StmtAdminUsers->fetch_array()){
					$UserName=$RowAdminUsers['UserName']; ?>
			 
					<option value="<?php echo $UserName ?>"><?php echo $UserName ?></option>
					<?php  }?>
				<select>
				</div> 
				
				
				
				
				
				</div> 
				
				<div class="col-xs-12 box-footer"  align="right">
                <button hidden type="submit"  name='submit' id="Create" class="btn btn-success">Next</button>
              </div>
				<div class="col-xs-12 "  align="right">
			 <button type="button"  id="CreatePI" class="btn btn-success">Next</button>
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
<script>
$('#Cminus').on('click', function(){
	
      var NoCon= $("#NoContainers").val();
	  
	  if(NoCon > 1){
		   NoCon= parseInt(NoCon)-1;
		 $("#NoContainers").val(NoCon); 
	  }
});

$('#Cplus').on('click', function(){
	 
       var NoCon= $("#NoContainers").val();
		  if(NoCon < 10){
			 NoCon= parseInt(NoCon)+1;
		 $("#NoContainers").val(NoCon); 
	  }
});
</script>

<script>

$("document").ready(function(){
	  $("#ConsigneeID").select2();
	  $("#BuyerID").select2();
	  $("#DestinationCountry").select2();

    $("#file").change(function() {
        alert('changed!');
    });
	
	

	
});
  	

</script>


<script>

$("document").ready(function(){
	
	  $("#ConsigneeID").select2();
	  $("#PortOfDischarge").select2();
});

$('#ConsigneeID').change(function(){
	 var BuyerId= $('#ConsigneeID').val(); 
	 if(BuyerId==''){
		 return;
	 }
   $.ajax({
    type: 'POST',
    url: 'Ajax/AjBuyerDtls.php',
	dataType: 'json',
    data: {'BUYERID' : BuyerId},  
    success: function(response){
		 var myJSON = JSON.parse(JSON.stringify(response));
		
		 $("#BRAND").val(myJSON['ConsigneeBrand']);
		 $("#DestinationCountry").val(myJSON['ConsigneeCountry']);
		 $("#PORT_OF_DIS").val(myJSON['ConsigneePort']);
		 $("#ADDRESS_OF_CONSIGNEE").val(myJSON['ConsigneeAddress']);
		 $("#FISHING_PMT_NO").val(myJSON['ConsigneeFishingNo']);
		 
	 }
  });
  });
  
  
  $('#DestinationCountry').change(function(){
	 var CountryId= $('#DestinationCountry').val(); 
	 if(CountryId==''){
		 return;
	 }
   $.ajax({
    type: 'POST',
    url: 'Ajax/AjPortDtls.php',
	//dataType: 'json',
    data: {'CountryId' : CountryId},  
    success: function(response){
		 //var myJSON = JSON.parse(JSON.stringify(response));
		// console.log(response);
		 $("#PortOfDischarge").html(response);
		// $("#PortOfDischarge").html(response);
		
		 
	 }
  });
  });

</script>


<Script>

$('document').ready(function(){
	
	
	$('#CreatePI').on('click',function(){
    var submitButton = document.getElementById("Create");
	
	var InvNo = $('#InvNo').val();
	var NoContainers = $('#NoContainers').val();
	var InvDate = $('#InvDate').val();
	var ExporterID = $('#ExporterID').val();
	
	if(InvNo==''){
		submitButton.click();
	return;
	}
	
	$.ajax({
    type: 'POST',
    url: 'Ajax/AjCheckPINumberAvalabitity.php',
	dataType: 'json',
    data: {'InvNo' : InvNo,'NoContainers':NoContainers,'InvDate':InvDate,'ExporterID':ExporterID},  
    success: function(response){
		 var myJSON = JSON.parse(JSON.stringify(response));
		 var NotAvalNo="Invoice Number are Not Aval ";
		 var Aval="Aval Invoice Number are  -"+myJSON['AvalNo'];
		 
		 
		 
		 if(myJSON['Error'] ==null){
			submitButton.click();
		 }else{
		var r = confirm(NotAvalNo + "" +Aval);
		if (r == true) {
			submitButton.click();
		}else{ 
		//return;
		}
		 }			 
		 
	 }
  });
	
	

    	});

  //alert('hiiiii');
    $('#ConsigneeID').on("keyup", function(){
        /* Get input value on change */
		alert('hiiii');
        var inputVal = $(this).val();
		
		//alert(inputVal);
		var resultDropdown = $(this).siblings(".result");
        if(inputVal.length){
            $.get("Ajax/AjaxConsigneeSearch.php", {term: inputVal}).done(function(data){
                // Display the returned data in browser
				
				//console.log('test');
				console.log(data);
				//alert(data);
                resultDropdown.html(data);
            });
        } else{
            resultDropdown.empty();
        }
		
    });
	
	    // Set search input value on click of result item
$(document).on("click", ".result p", function(){
        var iid=$(this).attr('id');
//alert(iid);
$("#ConsigneeID").val(iid);
        $(this).parent(".result").empty();
         //alert(cat);
		$.ajax({
			type:'POST',
			url:'Ajax/AjaxConsigneeDetails.php',
			data:{'id':iid},
		success:function(data){
			//alert(data);
			console.log(data);
var obj = JSON.parse(data);


var C_NAME=obj.ConsigneeName;
var C_ADD_id=obj.ConsigneeAddress;
var C_PORT_OF_DIS=obj.ConsigneePort;
var BUYER_COUNTRY=obj.ConsigneeCountry;
var BRAND_NAME=obj.ConsigneeBrand;
var SPECIAL_INST=obj.ConsigneeSplIns;
var FISHING_PMT_NO=obj.ConsigneeFishingNo;
alert(C_NAME);


$('#ConsigneeId').val(iid);
$('#ConsigneeAddress').val(C_NAME);
$('#ConsigneeBrand').val(BRAND_NAME); 
$('#ConsigneeCountry').val(BUYER_COUNTRY); 
$('#ConsigneePort').val(C_PORT_OF_DIS); 
$('#ConsigneeAddress').val(C_ADD_id); 
$('#ConsigneeSplIns').val(SPECIAL_INST);  
$('#ConsigneeFishingNo').val(FISHING_PMT_NO); 

		} 		
	});
$(this).parent(".result").empty();
        
});

});   	

</script>
</html>

			

