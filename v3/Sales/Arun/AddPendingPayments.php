<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");


include ("TestAutoMail.php");



$PageTitleFolder="Sales";
$PageTitle="Pending Payments";


$PIId=$_GET['PIID'];
$ExporterID=$_GET['ExporterID'];
//From Global
$AryPriceWeightData=getInvoiceWeight($iconn,$GradeID,$PIId);
$AryPriceData=getPaymentValues($iconn,$PIId);
//print_r($AryPriceWeightData); exit;
 $NetWeight=$AryPriceWeightData['Qty']; 
 $GrossWeight=$AryPriceWeightData['GrossWeightKgs'];
 $DutiesPrice=$AryPriceData['DutiesPrice'];

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



 $SqlPI="SELECT ExporterID FROM `ScrmPoData`  WHERE PoID='$PIId'"; 
$StmtPI=$iconn->query($SqlPI);
$RowPI=$StmtPI->fetch_array();

$ExpID=$RowPI['ExporterID'];
		
$CalculatedValues=getPaymentValues($iconn,$PIId);		
 $SqlCount="SELECT * FROM `ScrmCustomInvoices` WHERE PiID='$PIId'";
		
		$StmtCount=$iconn->query($SqlCount);
		$RowPendingPayments=$StmtCount->fetch_array();
		$InvNo=$RowPendingPayments['CInvNo'];
		$TotalGrossWt=$RowPendingPayments['TotalGrossWt'];
		if($TotalGrossWt!=''){
		$GrossWeight=$TotalGrossWt;
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



if(isset($_POST['submit'])){	
		 if($Count==0){
			 
			  $StartYear=date('Y',strtotime($CInvDate));
			  $EndYear=$StartYear+1;
			  $FinStartDate=$StartYear.'-04-01';
			  $FinEndDate=$EndYear.'-03-31';
			 
			 	if($ExpID==5){

			 $SqlInvCount="SELECT * FROM `ScrmCustomInvoices`
			 WHERE CInvDate BETWEEN '$FinStartDate' and '$FinEndDate' and ExporterID='$ExpID' and CInvNo='$CInvNo'";
 
				}
				else{
				$SqlInvCount="SELECT * FROM `ScrmCustomInvoices`
			 WHERE CInvDate BETWEEN '$FinStartDate' and '$FinEndDate' and ExporterID BETWEEN '1' and '3' and CInvNo='$CInvNo'";
 
				}
				
		
				$StmtInvCount=$iconn->query($SqlInvCount);
			 $InvCount=mysqli_num_rows($StmtInvCount);		    
			 
			 if($InvCount==0){

			 
        $SqlInsert="INSERT INTO `ScrmCustomInvoices`
		(`PiID`, `CInvNo`, `CInvDate`,`CInvCode`, `IGST`, `Discount`, 
		`BankID`, `NoOfDays`,`ExporterID`,`AdvanceAmount`,
		`OverSeasCommission`,`Description`, `CIDescription`, 
		`NameofRepresentative`, `PackDescription`,
		`QCertificateNo`, `ContainerNo`, `ProductTypes`, `USClearance`,
		`PaymentType`, `ByRoad`, `IRNNumber`, `ClearenceCharges`,
		`TotalNetWt`, `TotalGrossWt`, `Duty`, `Freight`, `ExRate`,
         `StockOutDate`, `Vessel`, `VoyageNo`, `Remarks`)		   
	VALUES ('$PIId','$CInvNo','$CInvDate','$CInvCode','$IGST','$Discount',
	'$BankID','$NoOfDays','$ExpID','$AdvanceAmount','$OverSeasCommission',
	'$Description','$CIDescription','$NameofRepresentative',
	'$PackDescription','$QCertificateNo','$ContainerNo',
	'$ProductTypes','$USClearance','$PaymentType',
	'$ByRoad','$IRNNumber','$ClearenceCharges','$TotalNetWt','$TotalGrossWt'
	,'$Duty','$Freight','$ExRate','$StockOutDate','$Vessel'
	,'$VoyageNo','$Remarks')"; 
		
		
			$StmtInsert=$iconn->query($SqlInsert);
		/*echo 	$SqlInsert;
		exit;*/
			$AryCInvNo=explode("-",$CInvDate);
			$FinYear=$AryCInvNo[0];
			$CurrentMonth=$AryCInvNo[1];
			
			if($CurrentMonth > 3){	
				$FinYear=$FinYear;	
			}else{
				$FinYear=$FinYear-1;
			}
			
			$SqlSdb="UPDATE `ScrmShipmentDetailsBreif` SET 
			`CInvNo`='$CInvNo',
			`CInvDate`='$CInvDate',
			`FinYear`='$FinYear' WHERE PiID='$PIId'";
	
			$StmtSdb=$iconn->query($SqlSdb);


	if($StmtInsert){
		postUpdateShipmentBreif($iconn,$CInvDate,$CInvNo,$PIId);
	if($CIGradeCount==0){
		
		$SqlGradeData="SELECT * FROM `ScrmPoGradeData` where InvId='$PIId'  ORDER BY ID DESC"; 
$StmtGradeData=$iconn->query($SqlGradeData); 
   while($RowGradeData=$StmtGradeData->fetch_array()){

	
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
		
		
		
		
		$SqlGradeInsert="INSERT INTO `ScrmCIGradeData`(`Grade`, `Mcs`, `FreezingType`, `InvId`, `PackingStyleID`,
	 `GlazeType`, `Variety`, `GlazePerc`, `Treatment`, `Brand`, `Price`,`BAP`,`VarietyID`) VALUES (
	 '$PoGrade','$PoMcs','$PoFreezingType','$PIId','$PoPackingStyleID','$PoGlazeType','$PoVariety','$PoGlazePerc',
	 '$PoTreatment','$PoBrand','$PoPrice','$PoBAP','$PoVarietyID')"; 
		$StmtGradeInsert=$iconn->query($SqlGradeInsert);
		
	//	ECHO $SqlGradeInsert;
	}
	}
	
	echo '<script language="javascript" type="text/javascript"> 
                alert("Success");
                window.location = "DocAddReports.php?PoID='.$PIId.'";
        </script>';
		//header('Location: PIList.php');
		
					 		 automail($PIId,"1","");
	}	 
			 
			 }	 
		else{
			echo '<script language="javascript" type="text/javascript"> 
                alert("'.$CInvNo.'Invoice Number Already Exists");
                window.location = "AddCustomInvoice.php?PIID='.$PIId.'&ExporterID='.$ExporterID.'";
        </script>';
		}	 
			 
			 
			 }
			 
			 
			 
 else{
				 if($InvNo==$CInvNo){
					
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
		 `ClearenceCharges`='$ClearenceCharges'
		 
		 WHERE `PiID`='$PIId'"; 

		
			$StmtCIInsert=$iconn->query($SqlCIInsert);
			if($StmtCIInsert){
							postUpdateShipmentBreif($iconn,$CInvDate,$CInvNo,$PIId);

			}


	echo '<script language="javascript" type="text/javascript"> 
       alert("Successfully Updated");

                window.location = "DocAddReports.php?PoID='.$PIId.'";
        </script>';
					 		 automail($PIId,"1","Edited");

			 }
	else{
		
	$StartYear=date('Y',strtotime($CInvDate));
	 $EndYear=$StartYear+1; 
			  $FinStartDate=$StartYear.'-04-01';
			  
             $FinEndDate=$EndYear.'-03-31';
			 
			 	if($ExpID==5){

			 $SqlInvCount="SELECT * FROM `ScrmCustomInvoices`
			 WHERE CInvDate BETWEEN '$FinStartDate' and '$FinEndDate' and ExporterID='$ExpID' and CInvNo='$CInvNo'";
 
				}
				else{
				$SqlInvCount="SELECT * FROM `ScrmCustomInvoices`
			 WHERE CInvDate BETWEEN '$FinStartDate' and '$FinEndDate' and ExporterID BETWEEN '1' and '3' and CInvNo='$CInvNo'";
 
				}
				//echo $SqlInvCount; exit;
				
				$StmtInvCount=$iconn->query($SqlInvCount);
			 $InvCount=mysqli_num_rows($StmtInvCount);		    
			 
			 if($InvCount!=0){
	 echo '<script language="javascript" type="text/javascript"> 
	 alert("Update Failed  Invoice Number already exists");
	                 window.location = "AddCustomInvoice.php?PIID='.$PIId.'";
</script>';
			 }
		else{	 
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
		 `ClearenceCharges`='$ClearenceCharges'
		
		 WHERE `PiID`='$PIId'"; 
			
		
			$StmtCIInsert=$iconn->query($SqlCIInsert);
						if($StmtCIInsert){
							postUpdateShipmentBreif($iconn,$CInvDate,$CInvNo,$PIId);

			}
			 	
		echo '<script language="javascript" type="text/javascript"> 
                	 alert("Successfully Updated");

                window.location = "DocAddReports.php?PoID='.$PIId.'";
        </script>';
					 		 automail($PIId,"1","Edited");
	}
			
		}	 
			
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
								
								<form role="form" method="post">
                                    <div class="card-body">
									
										<div class="row">
										
										
				
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Company<span style="color:red">*</span></label>
			   <select class="form-control select2" id="CompanyName" name="CompanyName" >
				 
				  <option  value="">Select Company Name</option>	
				</select>
                </div>
				
					<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Booking Date<span style="color:red">*</span></label>
				<input required type="date" class="form-control" name='BookingDate' id="BookingDate" 
				  placeholder="" value="">			
                </div>
				
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Value<span style="color:red">*</span></label>
				<input  required type="number" class="form-control" name='Value' id="Value" 
				  placeholder="Enter Value " value="<?php echo $RowPendingPayments['Value']?>">
                </div>

				<div class="form-group col-sm-3"  >
                  <label for="exampleInputEmail1">Utilized Value</label>
				<input readonly type="number" class="form-control" name='UtilizedValue' id="UtilizedValue" 
				  placeholder="Utilized Value" value=""style="background-color: lightgrey;">	
                </div>

				<div class="form-group col-sm-3"  >
                  <label for="exampleInputEmail1">Value</label>
				<input readonly type="number" class="form-control" name='BalanceValue' id="BalanceValue" 
				  placeholder="Total Rate" value=""style="background-color: lightgrey;">	
                </div>
					
					
				  <div class="form-group col-sm-3" > 
				<label for="exampleInputEmail1">Period</label>
                 <select class="form-control select2" id="Period" name="Period" >
				 
				  <option  value="">Select Period</option>
				  </select>
				  
					</div>
					
					<div class="form-group col-sm-3" > 
				<label for="exampleInputEmail1">Month</label>
				
			<select class="form-control select2" id="Month" name="Month" >
			<option  value="">Select Month</option>
					<?php
					$months = array(
						1 => "January",
						2 => "February",
						3 => "March",
						4 => "April",
						5 => "May",
						6 => "June",
						7 => "July",
						8 => "August",
						9 => "September",
						10 => "October",
						11 => "November",
						12 => "December"
					);

					foreach ($months as $key => $value) {
						echo "<option value='$key'>$value</option>";
					}
					?>
				</select>

					</div>
					
				
					
		
					
					<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Premium Rate</label>
				<input  type="number" class="form-control" name='PremiumRate' id="PremiumRate" 
				  placeholder="Enter Premium Rate " value="">
                </div>	
					
						<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Forward Contract No<span style="color:red">*</span></label>
				<input required type="number" class="form-control" name='ForwardContractNo' id="ForwardContractNo" 
				  placeholder="Enter Forward Contract No " value="">
                </div>	
					
					
							<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Start Date<span style="color:red">*</span></label>
				<input required type="date" class="form-control" name='StartDate' id="StartDate" 
				  placeholder="" value="">			
                </div>
				
						<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">End Date<span style="color:red">*</span></label>
				<input required type="date" class="form-control" name='EndDate' id="EndDate" 
				  placeholder="" value="">			
                </div>


				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Spot Rate<span style="color:red">*</span></label>
				<input required type="number" class="form-control" name='SpotRate' id="SpotRate" 
				  placeholder="Enter Spot Rate " value="">
                </div>

				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Branch Margin<span style="color:red">*</span></label>
				<input required type="number" class="form-control" name='BranchMargin' id="BranchMargin" 
				  placeholder="Enter Branch Margin " value="">
                </div>
				
				
				<div class="form-group col-sm-3"  >
                  <label for="exampleInputEmail1">Total Rate</label>
				<input readonly type="number" class="form-control" name='TotalRate' id="TotalRate" 
				  placeholder="Total Rate" value=""style="background-color: lightgrey;">	
                </div>
				
				
				</div>
				
				
				
				
					<?php if($Count==0){	 ?>
			  <div class="col-xs-12 box-footer"  align="right">
			  <a target="" href="DocAddReports.php?PoID=<?php echo $PIId ?>"
       class="btn btn-danger  "> Close</a>
  		
                <button type="submit"  name='submit' class="btn btn-primary">Add</button>
			 <!--  <a href="CIBreakUp.php?PIId=<?php echo $PIId; ?>" id="<?php echo $PIId; ?>"  class="btn btn-success">ShortShipment  </a>
-->
              </div>
					<?php } else{	 ?>
					
			  <div class="col-xs-12 box-footer"  align="right">
			  <a target="" href="DocAddReports.php?PoID=<?php echo $PIId ?>"
       class="btn btn-danger  "> Close</a>
  		
                <button type="submit"  name='submit' class="btn btn-primary">Update</button>
			<!-- <a href="CIBreakUp.php?PIId=<?php echo $PIId; ?>"  id="<?php echo $PIId; ?>"  class="btn btn-success">ShortShipment  </a>
-->
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
function reply_click(clicked_id) {

   		var r = confirm("Are You Sure Do You Want To Delete "+clicked_id+"?");
  if (r == true) { 
		 //alert(clicked_id);		  
		$.ajax({
		  url:'Ajax/AjaxDelete.php',
		  type:'POST',
		  data:{'TYPE':'CustomDelete','PiID':clicked_id},
		  success:function(data){
			  
			  
			// $("#example1").html(data);
			 alert("Deleted Successfully");
			location.reload();
			
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
	