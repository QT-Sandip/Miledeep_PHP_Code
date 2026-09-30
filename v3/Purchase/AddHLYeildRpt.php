<?php
$Status=$_SESSION['Status']; 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Util.php");


$AryCurrentDate=getCurrentDateTime();
$PostedDateTime=$AryCurrentDate['TimeStamp'];
global $PostedDateTime;			

$LotID=$_GET['LotID'];

extract($_POST);
extract($_GET);


$SqlLotDetatils="SELECT * FROM `RmLotDetatils` WHERE RmLotID='$LotID'";
$StmtLotDetatils=$iconn->query($SqlLotDetatils);
$RowLotDetatils=$StmtLotDetatils->fetch_array();

$LotNumer=$RowLotDetatils['LotNumber'];
$Count=$RowLotDetatils['PurchaseCount'];
//$Weight=$RowLotDetatils['PurchaseWeight'];

if($RowLotDetatils['ProductionWeight']!=0){
	$Weight=$RowLotDetatils['ProductionWeight'];
}else{
	$Weight= $RowLotDetatils['PurchaseWeight'] ;
}

$SqlYeildData="SELECT * FROM `PpcHLYeildData` WHERE LotID='$LotID'";
$StmtYeildData=$iconn->query($SqlYeildData);
$RowYeildData=$StmtYeildData->fetch_array();

$YeildCount=mysqli_num_rows($StmtYeildData);

$SqlShifts="SELECT * FROM `PpcOptions` WHERE Value='SHIFTS'";
$StmtShifts=$iconn->query($SqlShifts);	


$PageTitle="Add HL Yeild Report  - "
.$LotNumer
.'('
.$Count
.'C/'
.$Weight
.'Kgs)';

$PageTitleFolder="PreProcessing";

if(isset($_POST['submit'])){
	$auditLogger = legacyAuditGetLogger('Purchase');
	$existingYieldRow = null;
	if ($YeildCount != 0 && $auditLogger) {
		$existingYieldRows = $auditLogger->fetchRowsByConditions('PpcHLYeildData', array('LotID' => $LotID));
		if (!empty($existingYieldRows)) {
			$existingYieldRow = $existingYieldRows[0];
		}
	}
	if($YeildCount==0){
	$SqlDHInsert="INSERT INTO `PpcHLYeildData`(`LotID`, `BillCount`, 
	`CheckingCount1`, `CheckingCount2`,`CountNetQty`, `Batch1`, `Batch2`, `Batch3`, 
	`Batch4`, `Batch5`, `Batch6`) VALUES 
	('$LotID','$BillCount','$CheckingCount1','$CheckingCount2',
	'$CountNetQty',
	'$Batch1','$Batch2','$Batch3','$Batch4','$Batch5','$Batch6')";
	}else{
		
		$SqlDHInsert="UPDATE `PpcHLYeildData` SET 
		BillCount='$BillCount',
		
		CheckingCount1='$CheckingCount1',
		CheckingCount2='$CheckingCount2',
		CountNetQty='$CountNetQty',
		Batch1='$Batch1',
		Batch2='$Batch2',
		Batch3='$Batch3',
		Batch4='$Batch4',
		Batch5='$Batch5',
		Batch6='$Batch6' WHERE LotID='$LotID'
		";
	}
	$StmtDHInsert=$iconn->query($SqlDHInsert);
	 
	if($StmtDHInsert){
		if($YeildCount==0){
			legacyAuditLogInsertById('PpcHLYeildData', 'LotID', $LotID, array(
				'LotID' => $LotID,
				'BillCount' => $BillCount,
				'CheckingCount1' => $CheckingCount1,
				'CheckingCount2' => $CheckingCount2,
				'CountNetQty' => $CountNetQty,
			), array(
				'ReferenceNo' => $LotID,
				'Remarks' => 'HL yield report created from legacy purchase screen',
			), 'Purchase');
		}else if ($existingYieldRow !== null){
			legacyAuditLogUpdateById('PpcHLYeildData', 'LotID', $LotID, $existingYieldRow, array(
				'LotID' => $LotID,
				'BillCount' => $BillCount,
				'CheckingCount1' => $CheckingCount1,
				'CheckingCount2' => $CheckingCount2,
				'CountNetQty' => $CountNetQty,
			), array(
				'ReferenceNo' => $LotID,
				'Remarks' => 'HL yield report updated from legacy purchase screen',
			), 'Purchase');
		}
		
		echo '<script language="javascript" type="text/javascript"> 
                alert("Success");
                window.location = "BillsList.php";
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

    <!--<body data-sidebar="dark">-->
	<body data-sidebar="dark" class="sidebar-enable vertical-collpsed">

	
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
								
								<form role="form" method="post">
                                    <div class="card-body">
									
										<div class="row">

    	
             <!--   <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Bill Count</label>
                  <input required type="text" class="form-control" name='BillCount' id="BillCount"
				  placeholder="Enter Bill Count" value="<?php echo $RowYeildData['BillCount']?>">
                </div>-->
				
			  <!-- <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Head On Qty</label>
                  <input required type="text" class="form-control" name='HeadOnQtyEntered' id="HeadOnQtyEntered"
				  placeholder="Enter Head On Qty" value="<?php echo $RowYeildData['HeadOnQtyEntered']?>">
                </div>-->
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Checking Count-1</label>
                  <input type="text" class="form-control"  name="CheckingCount1" id="CheckingCount1" 
				  placeholder="Enter Checking Count 1" value="<?php echo $RowYeildData['CheckingCount1']?> ">
                </div>
				<!--
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Checking Count-2</label>
                  <input type="text" class="form-control"  name="CheckingCount2" id="CheckingCount2" 
				  placeholder="Enter Checking Count 2" value="<?php echo $RowYeildData['CheckingCount2']?>">
                </div>
				-->
				<!--<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Count Net Qty(Kgs) </label>
                  <input type="text" class="form-control"  name="CountNetQty" id="CountNetQty" 
				  placeholder="Enter Count Net Qty(Kgs)" value="<?php echo $RowYeildData['CountNetQty']?>">
                </div>-->
				
			
			<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Batch-1</label>
                  <input type="text" class="form-control"  name="Batch1" id="Batch1" 
				  placeholder="Enter Batch1"value="<?php echo $RowYeildData['Batch1']?>">
                </div>
			
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Batch-2</label>
                  <input type="text" class="form-control"  name="Batch2" id="Batch2" 
				  placeholder="Enter Batch -2" value="<?php echo $RowYeildData['Batch2']?>">
                </div>
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Batch-3</label>
                  <input type="text" class="form-control"  name="Batch3" id="Batch3" 
				  placeholder="Enter Batch-3" value="<?php echo $RowYeildData['Batch3']?>">
                </div>
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Batch-4</label>
                  <input type="text" class="form-control"  name="Batch4" id="Batch4" 
				  placeholder="Enter Batch-4" value="<?php echo $RowYeildData['Batch4']?>">
                </div>
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Batch-5</label>
                  <input type="text" class="form-control"  name="Batch5" id="Batch5" 
				  placeholder="Enter Batch-5"value="<?php echo $RowYeildData['Batch5']?>">
                </div>
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Batch-6</label>
                  <input type="text" class="form-control"  name="Batch6" id="Batch6" 
				  placeholder="Enter Batch6"value="<?php echo $RowYeildData['Batch6']?>">
                </div>
                
				
				</div>
				

			  <div class="col-xs-12 box-footer"  align="right">
                <button type="submit"  name='submit' class="btn btn-success">SUBMIT</button>
              </div>
            </form>
       <!-- end col -->
</div> <!-- end row -->
</div> <!-- end row -->
</div> <!-- end card -->
                            </div><!-- end col-->
                         
<!--<div id="tdetails" class="table-responsive col-sm-12" >  -->

	
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
$(document).on('click', '.Delete', function(e){
	  var DeheadID =$(this).data('id');

   		var r = confirm("Are You Sure You Want To Delete "+DeheadID+"?");
   if (r == true) {

		 //alert(VaID);		  
		$.ajax({
		  url:'Ajax/AjDltAl.php',
		  type:'POST',
		  data:{'TYPE':'DeheadingReport','D_ID':DeheadID},
		  success: function(response){
		var data=JSON.parse(response);
			if(data['sts']=='Failed'){
			alert(data['msg']);
location.reload();
			}else{
				$('#myModal').modal('hide');
			alert(data['msg']);
							location.reload();
				
			}
	 }
			});	
}
  else{}			
  }); 

 
  /* function showData(data){
			
					$("#EmpList").html(data);
 
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

*/
function showData(data){
			
			$("#EmpList").html(data);	

		var Stable=	$("#key-datatable").DataTable({
		order: [[0, 'desc']],
	   keys:!0,
			language:{
				paginate:{
					previous:"<i class='mdi mdi-chevron-left'>",
					next:"<i class='mdi mdi-chevron-right'>"
					}
					},
					drawCallback:function(){
						$(".dataTables_paginate > .pagination").addClass("pagination-rounded")}

				});			

}

  

 
</script>


