<?php 
$PageTitle="Purchase Details";
$PageTitleFolder="HRM";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/AjaxPurchaseDetails.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/AjaxGradingDetails.php");

$LotID=$_GET['LotID'];

$SqlFinaciers="SELECT * FROM `RmSuppliers` WHERE Status=1";
$StmtFinanciers=$iconn->query($SqlFinaciers);

$sql="SELECT * FROM `RmDefectsDetails` where LotID='$LotID'";
$STMT_DEFECTS=$iconn->query($sql);

$RowHonGrading=1;


extract($_POST);
if(isset($_POST['submit'])){	
	
	$dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata')); 
	$CreatedDateTime=$dateTime->format("Y-m-d  H:i:s"); 
	$auditLogger = legacyAuditGetLogger('Purchase');
	$existingHonGrading = legacyAuditFetchRowById('PPC_GRADING_HON', 'LOT_ID', $LOT_ID, 'Purchase');
	
	
	
	

	
	 $sql_org_insert="INSERT INTO `RmBillDetails`
			(`LotID`, `UserID`,`FinancierID`, `CreatedDateTime`,`Deductions`,`Yield`,`Remarks`)
	VALUES ('$LOT_ID','$USER_ID','$FinancierID','$CreatedDateTime','$DEDUCTIONS','$YIELD','$REMARKS')";	
					

	$stmt1=$iconn->query($sql_org_insert); 
	if($stmt1){	
		$insertedBillId = $iconn->insert_id;
		legacyAuditLogInsertById('RmBillDetails', 'ID', $insertedBillId, array(
			'LotID' => $LOT_ID,
			'FinancierID' => $FinancierID,
			'Remarks' => $REMARKS,
		), array(
			'ReferenceNo' => $LOT_ID,
			'Remarks' => 'Bill details created from legacy purchase screen',
		), 'Purchase');
	
	$SqlExDepb="SELECT * FROM `RmCostingExpanses` ORDER by ExID DESC LIMIT 1";
	$StmtExDepb=$iconn->query($SqlExDepb);
	$RowExDepb=$StmtExDepb->fetch_array();
	
	$EXPANSE=$RowExDepb['Expanse'];
	$DEPB=$RowExDepb['DEPB'];
	$EXCHANGE_RATE=$RowExDepb['ExchangeRate'];

	$SqlRmCD="INSERT INTO `RM_COSTING_DETAILS`
	(`LOT_ID`, `USER_ID`, `SUBMISSION_DATE`, `REMARKS`, `EXPANSE`, `DEPB`, `EXCHANGE_RATE`) 
	VALUES ('$LOT_ID','$USER_ID','$SUBMISSION_DATE','$REMARKS_C','$EXPANSE','$DEPB','$EXCHANGE_RATE')";	
	
	$StmtRmCD=$iconn->query($SqlRmCD);
	


if($RowHonGrading!=0){
 $sql_org_insert="UPDATE `PPC_GRADING_HON` SET 
`HON_PACKED_RATE`='$HONPACKEDRATE',
`HON_PACKED_COUNT`='$HONPACKEDCOUNT'

 WHERE LOT_ID='$LOT_ID'"; 
 
$stmt1=$iconn->query($sql_org_insert); 	
if($stmt1 && $existingHonGrading){
	legacyAuditLogUpdateById('PPC_GRADING_HON', 'LOT_ID', $LOT_ID, $existingHonGrading, array(
		'LOT_ID' => $LOT_ID,
		'HON_PACKED_RATE' => $HONPACKEDRATE,
		'HON_PACKED_COUNT' => $HONPACKEDCOUNT,
	), array(
		'ReferenceNo' => $LOT_ID,
		'Remarks' => 'HON grading updated from legacy purchase screen',
	), 'Purchase');
}
}

	
	
	
$sql="SELECT * FROM `RM_DEFECTS_DETAILS` where LOT_ID='$LOT_ID'";
$STMT_DEFECTS=$iconn->query($sql);
while($DEFECTS_ROW=$STMT_DEFECTS -> fetch_array()){ 

$DEF_WEIGHT=$_POST['DEF_WEIGHT' . $DEFECTS_ROW['ID']];
$DEF_RATE=$_POST['DEF_RATE' . $DEFECTS_ROW['ID']];
$DEF_ID=$DEFECTS_ROW['ID'];
	$existingDefectRow = legacyAuditFetchRowById('RM_DEFECTS_DETAILS', 'ID', $DEF_ID, 'Purchase');
	
		$sql_org_insert="UPDATE `RM_DEFECTS_DETAILS` SET 
`WEIGHT`='$DEF_WEIGHT',
`RATE`='$DEF_RATE',
`STATUS`='1' WHERE ID='$DEF_ID'";	
	
	$stmt1=$iconn->query($sql_org_insert);
	if($stmt1 && $existingDefectRow){
		legacyAuditLogUpdateById('RM_DEFECTS_DETAILS', 'ID', $DEF_ID, $existingDefectRow, array(
			'LOT_ID' => $LOT_ID,
			'WEIGHT' => $DEF_WEIGHT,
			'RATE' => $DEF_RATE,
		), array(
			'ReferenceNo' => $LOT_ID,
			'Remarks' => 'Bill defect pricing updated from legacy purchase screen',
		), 'Purchase');
	}
	}
	
	
 if($_SESSION['D_ID']== 1  ){
		header('Location: CostingPreview.php?id='.$LOT_ID);
		
 }else{
		echo "<script>alert('Success'); window.close();</script>";
		header('Location: DocFramerBill.php?LOT_ID='.$LOT_ID);
 }
 
 
	}

	}

?>

<!doctype html>
<html lang="en">

    <head>
        
         <?php include ($IncludesPath."title-meta.php"); ?>

        <!-- DataTables -->
        <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-select-bs4/css//select.bootstrap4.min.css" rel="stylesheet" type="text/css" />

        <!-- Responsive datatable examples -->
        <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />     


		<link href="<?php echo $BaseDist ?>assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet">
        <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css" rel="stylesheet">
        <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css" rel="stylesheet" />
        <?php include ($IncludesPath."head-css.php");?>

    </head>

    <body data-sidebar="dark">
	
	 <?php include ($IncludesPath."PreLoader.php"); ?>
	


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
                                    <div class="card-body">
									
										<div class="row">
                                               
											 <table id="PurchaseDetails" class="table table-bordered table-hover">
											</table> 
											
                                           <table id="GradingTable" class="table table-bordered table-hover">
											</table>      
												
												 


										</div>
                        <!-- end row -->
									</div>
									</div>
									</div>
        
                                         <!-- end col -->
                        </div> <!-- end row -->

                        <div class="row">
                            <div class="col-12">
                                <div class="card" >
                                <div  id="BillData" >
                                </div>
								
								
								 
                                      <form role="form" method="post">
                      
				
				<div class="card-body" >
				  <h4 class="card-title">Bill Details</h4><hr>
				<div class="row">
			
			
			<div class="form-group col-sm-3" >
							<label for="exampleInputEmail1">Bill Name</label>

							<select class="form-control select2" name="FinancierID" id="FinancierID">
								<option Value="Other">Other</option>
							<?php while($FinRow=$StmtFinanciers->fetch_array()) {

						?>
						<option Value="<?php echo $FinRow['ID'];?>">
						<?php echo $FinRow['FinancierName'] .' - '. $FinRow['PanNumber'];?></OPTION>
				<?php } ?>
				<select>
			</div>
			
			<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Addition/Deduction Per Kg</label>
                  <input required type="number" step="any" class="form-control"  name="Deductions" id="Deductions" 
				  placeholder="Enter Price/Kg"  >
                </div>
			
				
				
				
				<div class="form-group col-sm-3"  >
                  <label for="exampleInputEmail1">Remarks</label>
                  <input required type="text" class="form-control" name='Remarks' id="Remarks"
				  placeholder="Enter Remarks">
                </div>  
				</form>
				 </div> <!-- end card body-->
			  
            <div class="col-xs-12 box-footer"  align="right">
                <button   name='BtnBillCreate' id='BtnBillCreate' class="btn btn-primary">Submit</button>
              </div>


                                   
                                </div> <!-- end card -->
								 
								 
								
                            </div><!-- end col-->
                        </div>
                        <!-- end row-->


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
      

        <!-- Required datatable js -->
		 <script src="<?php echo $BaseDist ?>assets/libs/select2/js/select2.min.js"></script>
		  
        <script src="<?php echo $BaseDist ?>assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/bootstrap-maxlength/bootstrap-maxlength.min.js"></script>
		 <script src="<?php echo $BaseDist ?>assets/js/pages/form-advanced.init.js"></script>

		 
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
        <!-- Buttons examples -->
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/jszip/jszip.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/pdfmake/build/pdfmake.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/pdfmake/build/vfs_fonts.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons/js/buttons.html5.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons/js/buttons.print.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons/js/buttons.colVis.min.js"></script>

        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-select/js/dataTables.select.min.js"></script>
        
        <!-- Responsive examples -->
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

        <!-- Datatable init js -->
        <script src="<?php echo $BaseDist ?>assets/js/pages/datatables.init.js"></script>

        <script src="<?php echo $BaseDist ?>assets/js/app.js"></script>

    </body>
</html> 

<script>
$('document').ready(function(){
		getData();
    });
	
	
function getData(){
	
		var LotID='<?php echo $LotID ?>';

		 $.ajax({
		  url:'Ajax/AjaxGradingDetails.php',
		  type:'POST',
		  data:{'LotID' : LotID,'Type' : 'BillPreview'},
		  success:function(data){
			console.log(data);
			  var JsonData = JSON.parse(data);
			 //alert(JsonData[1]);


$("#PurchaseDetails").html(JsonData['PurchaseTable']);
$("#BillData").html(JsonData['BillDetails']);
$("#GradingTable").html(JsonData['GradingTable']);
$("#COUNT").val(JsonData['BillCount']);
$("#WEIGHT").val(JsonData['HlWeight']);
$("#RATE").val(JsonData['BillRate']);
$("#YIELD").val(JsonData['Yield']);

					}
			});
	
}

</script>



<Script>

$('document').ready(function(){

    	$(document).on('click','#BtnBillCreate',function(e){
		e.preventDefault();
			
			var FinancierID= $('#FinancierID').val();        
		    var Deductions= $('#Deductions').val();
            var Remarks= $('#Remarks').val();           
			var LotID= '<?PHP ECHO $LotID ?>'
            var PostedUserID= '<?PHP ECHO $UserID ?>'
            
			var validation=false;

/**uma**/
			if(Deductions>=100){
			alert("Enter Valid Deduction Max Limit 2 Digits");
			//$('#Deductions').focus();
			return;
			}else{
				validation=true;
			}
			/**uma**/
			
			if(FinancierID==""){
			alert("Select Bill Name");
			$('#LotNumber').focus();
			}else{
				validation=true;
			}

			if(validation==false){
			return;
			}

			var MData = {
				'LotID': LotID,
				'PostedUserID': PostedUserID,
				'FinancierID': FinancierID,
				'Deductions': Deductions,
				'Remarks': Remarks,
				'Type': 'BillCreate'
				};
			$.ajax({ 
		  url:'Ajax/AjaxGradingDetails.php',
		  type:'POST',
		  dataType: 'json',
		  data: MData,
		  success:function(data){
			 console.log(data);
			   var obj = JSON.parse(JSON.stringify(data));
			 
			   console.log(obj);
			 if(obj.Error==null){
				alert(obj.Msg);
				}else{
				alert(obj.Error);
				}

			}});


    	});
    });

</script>

