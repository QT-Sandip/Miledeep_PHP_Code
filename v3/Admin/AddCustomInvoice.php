<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ("TestAutoMail.php");


$PageTitleFolder="SalesNew";
$PageTitle="Custom Invoice Creation";


$PIId=$_GET['PIID'];
$ExporterID=$_GET['ExporterID'];

$SQL_OPTIONS="SELECT * FROM `OPTIONS` WHERE  DEPT='SALES'  order by SortOrder asc ";
$STMT_OPTIONS=$iconn->query($SQL_OPTIONS);
$ARY_OPTIONS=array();
WHILE($ROW=$STMT_OPTIONS->FETCH_ARRAY()){
	$ARY_OPTIONS[]=$ROW;
}

 	$SqlBank="SELECT * FROM $TblAdminBankDetails";
	$StmtBank=$iconn->query($SqlBank);



 $SqlPI="SELECT ExporterID FROM `$TblScrmPIData`  WHERE PoID='$PIId'"; 
$StmtPI=$iconn->query($SqlPI);
$RowPI=$StmtPI->fetch_array();

$ExpID=$RowPI['ExporterID'];
		
		
 $SqlCount="SELECT * FROM `$ScrmCustomInvoices` WHERE PiID='$PIId'";
		
		$StmtCount=$iconn->query($SqlCount);
				$RowCustomInvoice=$StmtCount->fetch_array();
		$InvNo=$RowCustomInvoice['CInvNo'];

		//$RowPI=$StmtCount->fetch_array();

//echo 'eee';	
		
		$Count=mysqli_num_rows($StmtCount);	

	 
$SqlGradeData="SELECT * FROM `$ScrmCIGradeData` where InvId='$PIId'  ORDER BY ID DESC"; 
$StmtGradeData=$iconn->query($SqlGradeData); 
$CIGradeCount=mysqli_num_rows($StmtGradeData);

 $SqlPIVarietyList="SELECT * FROM `$ScrmPIVarietyList`"; 
$StmtPIVarietyList=$iconn->query($SqlPIVarietyList);

$SqlUserDetails="SELECT * FROM `$TblAdminUserDetails` WHERE CustomUserID='1' and DepartmentID=9";
$StmtUserDetails=$iconn->query($SqlUserDetails);

//exit;		


extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	  //uma
	/*if($IGST!='LUT'){
		$BankID=0;
	}*/
	
		 if($Count==0){
			 
			  $StartYear=date('Y',strtotime($CInvDate));
			  $EndYear=$StartYear+1;
			  $FinStartDate=$StartYear.'-04-01';
			  $FinEndDate=$EndYear.'-03-31';
			 
			 	if($ExpID==5){

			 $SqlInvCount="SELECT * FROM `$ScrmCustomInvoices`
			 WHERE CInvDate BETWEEN '$FinStartDate' and '$FinEndDate' and ExporterID='$ExpID' and CInvNo='$CInvNo'";
 
				}
				else{
				$SqlInvCount="SELECT * FROM `$ScrmCustomInvoices`
			 WHERE CInvDate BETWEEN '$FinStartDate' and '$FinEndDate' and ExporterID BETWEEN '1' and '3' and CInvNo='$CInvNo'";
 
				}
				
				
				$StmtInvCount=$iconn->query($SqlInvCount);
			 $InvCount=mysqli_num_rows($StmtInvCount);		    
			 
			 if($InvCount==0){

			 
        $SqlInsert="INSERT INTO `$ScrmCustomInvoices`
		(`PiID`, `CInvNo`, `CInvDate`,`CInvCode`, `IGST`, `Discount`, 
		`BankID`, `NoOfDays`,`ExporterID`,`AdvanceAmount`,
		`OverSeasCommission`,`Description`, `CIDescription`, `NameofRepresentative`, `PackDescription`)
	VALUES ('$PIId','$CInvNo','$CInvDate','$CInvCode','$IGST','$Discount','$BankID','$NoOfDays','$ExpID',
	'$AdvanceAmount','$OverSeasCommission','$Description','$CIDescription','$NameofRepresentative','$PackDescription')"; 
		
		
			$StmtInsert=$iconn->query($SqlInsert);
			 
			/* else{
				 
			 }*/
	if($StmtInsert){
		legacyAuditLogInsertById($ScrmCustomInvoices, 'PiID', $PIId, $_POST, ['Remarks' => 'Custom invoice created'], 'Admin');
	if($CIGradeCount==0){
		
		$SqlGradeData="SELECT * FROM `$TblScrmPoGradeData` where InvId='$PIId'  ORDER BY ID DESC"; 
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
		
		
		
		
		$SqlGradeInsert="INSERT INTO `$ScrmCIGradeData`(`Grade`, `Mcs`, `FreezingType`, `InvId`, `PackingStyleID`,
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
					 //echo"hi";exit;
		$oldCustInvData = legacyAuditFetchRowById($ScrmCustomInvoices, 'PiID', $PIId, 'Admin');
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
		 `CIDescription`='$CIDescription',
		 `NameofRepresentative`='$NameofRepresentative',
		 `PackDescription`='$PackDescription'
		 WHERE `PiID`='$PIId'"; 
			


			$StmtCIInsert=$iconn->query($SqlCIInsert);
	if($StmtCIInsert) legacyAuditLogUpdateById($ScrmCustomInvoices, 'PiID', $PIId, $oldCustInvData, $_POST, ['Remarks' => 'Custom invoice updated (same inv no)'], 'Admin');
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

			 $SqlInvCount="SELECT * FROM `$ScrmCustomInvoices`
			 WHERE CInvDate BETWEEN '$FinStartDate' and '$FinEndDate' and ExporterID='$ExpID' and CInvNo='$CInvNo'";
 
				}
				else{
				$SqlInvCount="SELECT * FROM `$ScrmCustomInvoices`
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
		$oldCustInvData2 = legacyAuditFetchRowById($ScrmCustomInvoices, 'PiID', $PIId, 'Admin'); 
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
		 `CIDescription`='$CIDescription',
		 `NameofRepresentative`='$NameofRepresentative',
		 `PackDescription`='$PackDescription'
		 WHERE `PiID`='$PIId'"; 
			
		
			$StmtCIInsert=$iconn->query($SqlCIInsert);
			if($StmtCIInsert) legacyAuditLogUpdateById($ScrmCustomInvoices, 'PiID', $PIId, $oldCustInvData2, $_POST, ['Remarks' => 'Custom invoice updated (new inv no)'], 'Admin'); 	
		echo '<script language="javascript" type="text/javascript"> 
                	 alert("Successfully Updated");

                window.location = "DocAddReports.php?PoID='.$PIId.'";
        </script>';
					 		 automail($PIId,"1","Edited");
	}
			
		}	 
			
			 }
			 
			 IF($StmtInsert || $StmtCIInsert){
				 $oldPiStatusData = legacyAuditFetchRowById($TblScrmPIData, 'PoID', $PIId, 'Admin');
				 $oldPoStatusData = legacyAuditFetchRowById($TblScrmPoData, 'PoID', $PIId, 'Admin');				 
				 $SqlPIStatusUpdate="UPDATE `ScrmPIData` SET Status='6'	 WHERE `PoID`='$PIId'";
				 $SqlPOStatusUpdate="UPDATE `ScrmPoData` SET Status='6'	WHERE `PoID`='$PIId'";
				 
				 
			$StmtPIStatusUpdate=$iconn->query($SqlPIStatusUpdate);
			$StmtPOStatusUpdate=$iconn->query($SqlPOStatusUpdate);
			if($StmtPIStatusUpdate) legacyAuditLogUpdateById($TblScrmPIData, 'PoID', $PIId, $oldPiStatusData, ['Status' => '6'], ['Remarks' => 'PI status set to 6'], 'Admin');
			if($StmtPOStatusUpdate) legacyAuditLogUpdateById($TblScrmPoData, 'PoID', $PIId, $oldPoStatusData, ['Status' => '6'], ['Remarks' => 'PO status set to 6'], 'Admin');
			//automail($PIId,1);
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
                  <label for="exampleInputEmail1">Custom Invoice</label>
				<input required type="text" class="form-control" name='CInvNo' id="CInvNo" 
				  placeholder="Enter Custom Invoice No" value="<?php echo $RowCustomInvoice['CInvNo']?>">		
		
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Custom Invoice Date</label>
 <input  required type="date" class="form-control" name='CInvDate' id="CInvDate" 
				  placeholder=" " value="<?php echo $RowCustomInvoice['CInvDate']?>">
                </div>

					<!--<div class="form-group col-sm-3" > 
				<label for="exampleInputEmail1">Custom Invoice Code</label>
                 <select class="form-control select2" id="CInvCode" name="CInvCode" >
				  <option <?php if($RowCustomInvoice['CInvCode']=='03061720,16052900') {?>selected="selected"<?php } ?>
				  value="03061720,16052900">Both</option>
				  <option <?php if($RowCustomInvoice['CInvCode']=='03061720') {?>selected="selected"<?php } ?>
				  
				  value="03061720">Raw</option>
				  <option  <?php if($RowCustomInvoice['CInvCode']=='16052900') {?>selected="selected"<?php } ?>
				  value="16052900">Cooked</option>
				  </select>
					</div>-->
					
					
					 <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Custom Invoice Code</label>
                 
                <select class="form-control select2" name="CInvCode" id="CInvCode">
					 <?php 
					 //$SciName=$RowPIData['SciName'];
					 foreach($ARY_OPTIONS as $value){
					if($value['TYPE']=='CINVOICE CODE'){
						$OPTIONS= $value['OPTIONS'];
						$VALUE= $value['VALUE']; ?>
			 
					<option  <?php if($RowCustomInvoice['CInvCode']==$VALUE){ ?> selected="selected"  <?php } ?>
					
					value="<?php echo $VALUE ?>"><?php echo $OPTIONS?></option>
					<?php  }}?>
				<select>
				
				</div>
					
					
				  <div class="form-group col-sm-3" > 
				<label for="exampleInputEmail1">IGST/Discount</label>
                 <select class="form-control select2" id="IGST" name="IGST" >
				  <option  value="">Select</option>
				  <option <?php if($RowCustomInvoice['IGST']=='IGST') {?>selected="selected"<?php } ?>
				  
				  value="IGST">IGST</option>
				  <option  <?php if($RowCustomInvoice['IGST']=='LUT') {?>selected="selected"<?php } ?>
				  value="LUT">LUT</option>
				<option  <?php if($RowCustomInvoice['IGST']=='DISCOUNT') {?>selected="selected"<?php } ?>
				  value="DISCOUNT">DISCOUNT</option>
				
				
						</select>
					</div>
					
					<div class="form-group col-lg-3">  
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
                </div>
					
					<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Discount</label>
				<input  type="decimal" class="form-control" name='Discount' id="Discount" 
				  placeholder="Enter Discount" value="<?php echo $RowCustomInvoice['Discount']?>">		
		
                </div>
				

				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Terms No.of Days</label>
				<input  type="text" class="form-control" name='NoOfDays' id="NoOfDays" 
				  placeholder="Enter No.of Days" value="<?php echo $RowCustomInvoice['NoOfDays']?>">		
		
                </div>
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Advance Amount</label>
				<input  type="text" class="form-control" name='AdvanceAmount' id="AdvanceAmount" 
				  placeholder="Enter Advance Amount" value="<?php echo $RowCustomInvoice['AdvanceAmount']?>">		
		
                </div>
                <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">OverSeas Commission</label>
				<input  type="text" class="form-control" name='OverSeasCommission' id="OverSeasCommission" 
				  placeholder="Enter OverSeas Commission" value="<?php echo $RowCustomInvoice['OverSeasCommission']?>">		
		
                </div>
				
				
					<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Description</label>
                 
                <select class="form-control select2" name="Description" id="Description">
									  <option   value="">Select</option>

					 <?php while ($RowPIVarietyList=$StmtPIVarietyList->fetch_array()){
						$OPTIONS= $RowPIVarietyList['ShortName'];
						$FullName= $RowPIVarietyList['FullName'];
						$VALUE= $RowPIVarietyList['VarietyID'];
						 ?>
			 
					<option value="<?php echo $FullName ?>"<?php if( $FullName==$RowCustomInvoice['Description'])
					  echo 'selected="selected"'; ?>><?php echo $OPTIONS;?></option>
					<?php  }?>
				<select>
				
				</div>
				<div class="form-group col-lg-3">
                  <label for="exampleInputEmail1">Name of the Representative</label>
                 
                <select required class="form-control select2" name="NameofRepresentative" id="NameofRepresentative">
					  <option   value="">Select</option>
					<?php while($RowUserDetails=$StmtUserDetails->fetch_array()){
					$UserName=$RowUserDetails['UserName'];
					$UserID=$RowUserDetails['UserID'];
					$Designation=$RowUserDetails['Designation']; ?>
			 
					<option <?php if($UserID==$RowCustomInvoice['NameofRepresentative']){ ?> selected=='selected'  <?php } ?>
					
					value="<?php echo $UserID ?>"><?php echo $UserName .' - '.$Designation?></option>
					<?php  }?>
				<select>
				</div> 
				
				

				<div class="form-group col-lg-12" >
                  <label for="exampleInputEmail1">Commercial Inv Description</label>
				  
				  <textarea name="CIDescription" class="form-control" rows="4" cols="50"
                    id="CIDescription"   
					placeholder="Enter Description(Optional)" ><?php echo $RowCustomInvoice['CIDescription'] ?></textarea>
				 		
		
                </div>	
				
				<div class="form-group col-lg-12" >
                  <label for="exampleInputEmail1">Packing List Description</label>
				  
				  <textarea name="PackDescription" class="form-control" rows="7" cols="50"
                    id="PackDescription"   
					placeholder="Enter Description(Optional)" ><?php echo $RowCustomInvoice['PackDescription'] ?></textarea>
				 		
		
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
