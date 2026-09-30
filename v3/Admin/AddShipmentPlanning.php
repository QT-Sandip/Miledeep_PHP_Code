<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


$PageTitleFolder="SalesNew";
$PageTitle="Shipment Details";


$PIId=$_GET['PIID'];
//$ExporterID=$_GET['ExporterID'];

$SqlCount="SELECT * FROM `$ScrmShipmentDetails` WHERE PiID='$PIId'";
		$StmtCount=$iconn->query($SqlCount);
					$Count=mysqli_num_rows($StmtCount);				
		$RowCustomInvoice=$StmtCount->fetch_array();


 

$SqlPorts="SELECT * FROM $TblScrmPortCities ";
	$StmtPorts=$iconn->query($SqlPorts);
	
	$SqlPorts1="SELECT * FROM $TblScrmPortCities WHERE Country=72";
	$StmtPorts1=$iconn->query($SqlPorts1);
	
	
	while($RowPo=$StmtPorts->fetch_array()){
		$AryPorts[]=$RowPo;
	}
	while($RowPo1=$StmtPorts1->fetch_array()){
		$AryPorts1[]=$RowPo1;
	}
 

 	


extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	  
		 if($Count==0){
			 
			 
			 
       $SqlInsert="INSERT INTO `$ScrmShipmentDetails`(`PiID`, `ContainerNo`, 
		`SealNo`, `PackSealNo`, `PlaceOfRecipt`, `FinalDestination`, `FrieghtCharge`, `ExchangeRate`, 
		`VehicleNumber`, `EWayBillNo`, `PortOfLoading`, `PortofDischarge`)
	VALUES ('$PIId','$ContainerNo','$SealNo','$PackSealNo',
	'$PlaceOfRecipt','$FinalDestination','$FrieghtCharge','$ExchangeRate','$VehicleNumber',
	'$EWayBillNo','$PortOfLoading','$PortofDischarge')";
		
		
			$StmtInsert=$iconn->query($SqlInsert);
			 
			
	if($StmtInsert){
		legacyAuditLogInsertById($ScrmShipmentDetails, 'PiID', $PIId, $_POST, ['Remarks' => 'Shipment planning created'], 'Admin');
	echo '<script language="javascript" type="text/javascript"> 
                alert("Success");
				                window.location = "AddCustomInvoice.php?PIID='.$PIId.'";

        </script>';
	}	 
			 
			 
			 
			 
			 }
			 
			 
			 
			 
			 else{
			$oldShipData = legacyAuditFetchRowById($ScrmShipmentDetails, 'PiID', $PIId, 'Admin');
			$SqlUpdate= "UPDATE `$ScrmShipmentDetails` SET `ContainerNo`='$ContainerNo',
`SealNo`='$SealNo',`PackSealNo`='$PackSealNo',`PlaceOfRecipt`='$PlaceOfRecipt',
`FinalDestination`='$FinalDestination',`FrieghtCharge`='$FrieghtCharge',`ExchangeRate`='$ExchangeRate',
`VehicleNumber`='$VehicleNumber',`EWayBillNo`='$EWayBillNo',`PortOfLoading`='$PortOfLoading',
`PortofDischarge`='$PortofDischarge' WHERE `PiID`='$PIId'";
			

			$StmtUpdate=$iconn->query($SqlUpdate);
			 
	
	if($StmtUpdate){
		legacyAuditLogUpdateById($ScrmShipmentDetails, 'PiID', $PIId, $oldShipData, $_POST, ['Remarks' => 'Shipment planning updated'], 'Admin');
		echo '<script language="javascript" type="text/javascript"> 
                alert("Success");
			    window.location = "AddCustomInvoice.php?PIID='.$PIId.'";
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
						
					
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Container No</label>
				<input  type="text" class="form-control" name='ContainerNo' id="ContainerNo" 
				  placeholder="Enter Container No" value="<?php echo $RowCustomInvoice['ContainerNo']?>">		
		
                </div>
				
				
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Liner Seal No</label>
				<input  type="text" class="form-control" name='SealNo' id="SealNo" 
				  placeholder="Enter Seal No" value="<?php echo $RowCustomInvoice['SealNo']?>">	
</div>		
			<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Pack Seal No</label>
				<input  type="text" class="form-control" name='PackSealNo' id="PackSealNo" 
				  placeholder="Enter Pack Seal No" value="<?php echo $RowCustomInvoice['PackSealNo']?>">	
</div>	
	
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Frieght Charge</label>
				<input  type="text" class="form-control" name='FrieghtCharge' id="FrieghtCharge" 
				  placeholder="Enter Frieght Charge" value="<?php echo $RowCustomInvoice['FrieghtCharge']?>">		
		
                </div>
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Exchange Rate</label>
				<input  type="text" class="form-control" name='ExchangeRate' id="ExchangeRate" 
				  placeholder="Enter Exchange Rate" value="<?php echo $RowCustomInvoice['ExchangeRate']?>">		
		
                </div>	

				
				
			<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Vehicle Number</label>
				<input  type="text" class="form-control" name='VehicleNumber' id="VehicleNumber" 
				  placeholder="Enter Vehicle Number" value="<?php echo $RowCustomInvoice['VehicleNumber']?>">		
		
                </div>
				
				
					<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">E-WAY BILL NUMBER</label>
				<input  type="text" class="form-control" name='EWayBillNo' id="EWayBillNo" 
				  placeholder="Enter E-WAY Bill  Number" value="<?php echo $RowCustomInvoice['EWayBillNo']?>">		
		
                </div>

			<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Place Of Receipt</label>
				<input  type="text" class="form-control" name='PlaceOfRecipt' id="PlaceOfRecipt" 
				  placeholder="Enter Place Of Receipt" value="<?php echo $RowCustomInvoice['PlaceOfRecipt']?>">		
		
                </div>		
			

				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Final Destination</label>
				<input  type="text" class="form-control" name='FinalDestination' id="FinalDestination" 
				  placeholder="Enter Final Destination" value="<?php echo $RowCustomInvoice['FinalDestination']?>">		
		
                </div>
			
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Port Of Loading</label>
				 <select  required class="form-control select2" name="PortOfLoading" id="PortOfLoading">
				  <option value="">Select Port</option>
				   <?php foreach ($AryPorts1 as $RowPort ){ ?>
				     <option 
					 <?php if($RowCustomInvoice['PortOfLoading']==$RowPort['PortId']){ ?> selected="selected" <?php } ?>
					 value="<?php echo $RowPort['PortId'] ?>">
					 <?php echo $RowPort['Name']?></option>
				   
				 <?php  }?>	  
				  </select>
                </div>
				
				
				
				
				<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Port of Discharge</label>
				<select  required class="form-control select2" name="PortofDischarge" id="PortofDischarge">
				  <option value="">Select Port</option>
				   <?php foreach ($AryPorts as $RowPort ){ ?>
				     <option 
					 					 <?php if($RowCustomInvoice['PortofDischarge']==$RowPort['PortId']){ ?> selected="selected" <?php } ?>

					 value="<?php echo $RowPort['PortId'] ?>">
					 <?php echo $RowPort['Name']?></option>
				   
				 <?php  }?>	  
				  </select>
                </div>	
				
				
				 
				
				</div>
				
				
					
				
				
					<?php if($Count==0){	 ?>
			  <div class="col-xs-12 box-footer"  align="right">
			  <a target="" href="DocAddReports.php?PoID=<?php echo $PIId ?>"
       class="btn btn-danger  "> Close</a>
  		
                <button type="submit"  name='submit' class="btn btn-primary">Add</button>
			  <!-- <a href="CIBreakUp.php?PIId=<?php echo $PIId; ?>" id="<?php echo $PIId; ?>"  class="btn btn-success">ShortShipment  </a>-->

              </div>
					<?php } else{	 ?>
					
			  <div class="col-xs-12 box-footer"  align="right">
			  <a target="" href="DocAddReports.php?PoID=<?php echo $PIId ?>"
       class="btn btn-danger  "> Close</a>
  		
                <button type="submit"  name='submit' class="btn btn-primary">Update</button>
		<!--	 <a href="CIBreakUp.php?PIId=<?php echo $PIId; ?>"  id="<?php echo $PIId; ?>"  class="btn btn-success">ShortShipment  </a>-->

								 


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
