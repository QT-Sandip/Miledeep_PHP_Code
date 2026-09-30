<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

include ("TestAutoMail.php");

$PageTitleFolder = "Sales";
$PageTitle = "Audit Haturey";

$PIId = $_GET['PIID'];
$ExporterID = $_GET['ExporterID'];

// From Global
$AryPriceWeightData = getInvoiceWeight($iconn, $GradeID, $PIId);
$AryPriceData = getPaymentValues($iconn, $PIId);
//print_r($AryPriceWeightData); exit;
$NetWeight = $AryPriceWeightData['Qty'];
$GrossWeight = $AryPriceWeightData['GrossWeightKgs'];
$DutiesPrice = $AryPriceData['DutiesPrice'];

$SQL_OPTIONS = "SELECT * FROM `AdminGeneralOptions` WHERE DeptID='SALES'  order by SortOrder asc ";
$STMT_OPTIONS = $iconn->query($SQL_OPTIONS);
$ARY_OPTIONS = array();
while ($ROW = $STMT_OPTIONS->fetch_array()) {
    $ARY_OPTIONS[] = $ROW;
}

$SqlBank = "SELECT * FROM AdminBankDetails";
$StmtBank = $iconn->query($SqlBank);

$SqlLiner = "SELECT * FROM `ScrmLinerMaster` WHERE Status=1";
$StmtLiner = $iconn->query($SqlLiner);

$SqlPI = "SELECT ExporterID FROM `ScrmPoData`  WHERE PoID='$PIId'";
$StmtPI = $iconn->query($SqlPI);
$RowPI = $StmtPI->fetch_array();

$ExpID = $RowPI['ExporterID'];

$CalculatedValues = getPaymentValues($iconn, $PIId);
$SqlCount = "SELECT * FROM `ScrmCustomInvoices` WHERE PiID='$PIId'";

$StmtCount = $iconn->query($SqlCount);
$RowPendingPayments = $StmtCount->fetch_array();
$InvNo = $RowPendingPayments['CInvNo'];
$TotalGrossWt = $RowPendingPayments['TotalGrossWt'];
if ($TotalGrossWt != '') {
    $GrossWeight = $TotalGrossWt;
}

$Count = mysqli_num_rows($StmtCount);

$SqlGradeData = "SELECT * FROM `ScrmCIGradeData` where InvId='$PIId'  ORDER BY ID DESC";
$StmtGradeData = $iconn->query($SqlGradeData);
$CIGradeCount = mysqli_num_rows($StmtGradeData);

$SqlPIVarietyList = "SELECT * FROM `ScrmPIVarietyList`";
$StmtPIVarietyList = $iconn->query($SqlPIVarietyList);

$SqlUserDetails = "SELECT * FROM `AdminUserDetails` WHERE CustomUserID='1' and DepartmentID=9";
$StmtUserDetails = $iconn->query($SqlUserDetails);

// exit;

extract($_POST);
extract($_GET);

if (isset($_POST['submit'])) {
    if ($Count == 0) {
        $SqlInsert = "INSERT INTO `ScrmAuditHaturyDetails`(`HaturyID`, `LocationID`, `CertifBodyID`, `RegNum`, `BAPNum`, `ProdctnPerannum`, `AuditDt`, `ExpiryDt`, `RenewlDt`) 
        VALUES ('$HaturyID','$LocationID','$CertifBodyID','$RegNum','$BAPNum','$ProdctnPerannum', '$AuditDt','$ExpiryDt','$RenewlDt')";

        $StmtInsert = $iconn->query($SqlInsert);

        if ($StmtInsert) {
            echo '<script language="javascript" type="text/javascript"> 
                alert("Successfully Updated");
                window.location = "ForwrdContrctList";
            </script>';
        } else {
            echo '<script language="javascript" type="text/javascript"> 
                alert("Unable To Save Data");
            </script>';
        }
    } else { // have to change table name
        $SqlCIInsert = "UPDATE `ScrmCustomInvoices` SET 
        `HaturyID`='$HaturyID',
        `LocationID`='$LocationID',
        `CertifBodyID`='$CertifBodyID',
        `RegNum`='$RegNum',
        `BAPNum`='$BAPNum',
        `ProdctnPerannum`='$ProdctnPerannum',
        `AuditDt`='$AuditDt',
        `ExpiryDt`='$ExpiryDt',
        `RenewlDt`='$RenewlDt'
        WHERE `PiID`='$PIId'";

        $StmtInsert = $iconn->query($SqlInsert);

        if ($StmtInsert) {
            echo '<script language="javascript" type="text/javascript"> 
                alert("Successfully Updated");
                window.location = "ForwrdContrctList";
            </script>';
        } else {
            echo '<script language="javascript" type="text/javascript"> 
                alert("Unable To Save Data");
            </script>';
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
                  <label for="exampleInputEmail1">Hatchery Name<span style="color:red">*</span></label>
			   <select required class="form-control select2" id="HaturyID" name="HaturyID" >
				 
				  <option  value="">Select Hatchery Name</option>
				   
				</select>	
                </div>
				
				
					<div class="form-group col-sm-3" > 
				<label for="exampleInputEmail1">Location<span style="color:red">*</span></label>
                 <select required class="form-control select2" id="LocationID" name="LocationID" >
				 
				  <option  value="">Select Location</option>
				   
				</select>
				
					</div>
					
					<div class="form-group col-sm-3" > 
				<label for="exampleInputEmail1">Certifying Body<span style="color:red">*</span></label>
                 <select required class="form-control select2" id="CertifBodyID" name="CertifBodyID" >
				 
				  <option  value="">Select Certifying Body</option>
				   
				</select>
				
					</div>
			
			
			
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Govt Registration Number</label>
			 <input type="number" class="form-control" name='RegNum' id="RegNum" 
				  placeholder="Enter Govt Registration Number" value="">	
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">BAP Number</label>
			 <input type="number" class="form-control" name='BAPNum' id="BAPNum" 
				  placeholder="Enter BAP Number" value="">	
                </div>
				
				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Production Per Annum</label>
			 <input type="number" class="form-control" name='ProdctnPerannum' id="ProdctnPerannum" 
				  placeholder="Enter Production Per Annum" value="">	
                </div>
				
			
					<div class="form-group col-sm-3" > 
				<label for="exampleInputEmail1">Status<span style="color:red">*</span></label>
                 <select readonly class="form-control select2" id="Status" name="Status"  >
				 
				  <option  value="">Select Status</option>
				   
				</select>
				
					</div>
					
					
					<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Audit Date</label>
				<input  type="date" class="form-control" name='AuditDt' id="AuditDt" 
				  placeholder="" value="">
                </div>
				
				
					<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Expiry Date</label>
				<input  type="date" class="form-control" name='ExpiryDt' id="ExpiryDt" 
				  placeholder="" value="">
                </div>

				<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Renuwal Date</label>
				<input  type="date" class="form-control" name='RenewlDt' id="RenewlDt" 
				  placeholder="" value="">
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
		