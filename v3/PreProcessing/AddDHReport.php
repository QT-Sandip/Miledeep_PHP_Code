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

if($RowLotDetatils['ProductionWeight']!=0){
	$Weight=$RowLotDetatils['ProductionWeight'];
}else{
	$Weight= $RowLotDetatils['PurchaseWeight'] ;
}


$SqlLotDeheading="SELECT * FROM `PpcDeheading` WHERE LotID='$LotID'";
$StmtLotDeheading=$iconn->query($SqlLotDeheading);


$SqlDeheadingSheds="SELECT * FROM `PpcVAStations` WHERE UnitStatus=1";
$StmtDeheadingSheds=$iconn->query($SqlDeheadingSheds);

$SqlContractor=" SELECT * FROM `PpcContractMaster` WHERE Status=1";
$StmtContractor=$iconn->query($SqlContractor);

$SqlShifts="SELECT * FROM `PpcOptions` WHERE Value='SHIFTS'";
$StmtShifts=$iconn->query($SqlShifts);	

$SqlSuperVisor="SELECT * FROM `RmUsersList` WHERE UserType=5";
$StmtSuperVisor=$iconn->query($SqlSuperVisor);	


$PageTitle="Add Deheading Report  - "
.$LotNumer
.'('
.$Count
.'C/'
.$Weight
.'Kgs)';

$PageTitleFolder="PreProcessing";

if(isset($_POST['submit'])){
	
	$SqlDHInsert="INSERT INTO `PpcDeheading` 
	(`LotID`, `HonCount`, `HonWeight`, 
	`ReceivedWeight`, `ReturnWeight`, `ProcessingDate`, 
	`PostedDate`, `WtAfterDH`, `CountAfterDH`, `ShiftID`,`SupervisorID`,
	`StationID`, `UserID`, `Remarks`, 
	`VarietyID`,`NetCount`,`IceConsumption`,`CheckingCount`,`ContractorID`,`BatchID`)VALUES
	('$LotID','$HonCount','$HonWeight','$ReceivedWeight',
	'$ReturnWeight','$ProcessingDate','$PostedDateTime',
	'$WtAfterDH','$CountAfterDH','$ShiftID','$SupervisorID'
	,'$StationID','$UserID','$Remarks','$VarietyID',
	'$NetCount','$IceConsumption','$CheckingCount','$ContractorID','$BatchID')";

	$StmtDHInsert=$iconn->query($SqlDHInsert);
	$DHID = $iconn->insert_id;
	 
	if($StmtDHInsert){
		legacyAuditLogInsertById('PpcDeheading', 'DHID', $DHID, $_POST, [
			'ReferenceNo' => $DHID,
			'Remarks' => 'Deheading report created',
		], 'PreProcessing');
		
		echo '<script language="javascript" type="text/javascript"> 
                alert("Success");
                window.location = "AddDHReport.php?LotID='.$LotID.'";
		</script>';
	}
}

 ?>



<!DOCTYPE html>
<html lang="en">
<link href="<?php echo  $href ?>"  rel="stylesheet" type="text/css" />
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
										
										<?php include "IncludeContractorBatch.php" ?>
								</div>
								<hr>
			   
		
			   <div class="row">
			   
			   
			   <div class="form-group col-sm-3" >
                  <label for="exampleInputPassword1">Select Shift<span style="color:red">*</span>  </label>
				 <select required class="form-control select2" name="ShiftID" id="ShiftID">
				 
				   <?php while($RowShifts=$StmtShifts->fetch_array()) {
				?>
				<option Value="<?php echo $RowShifts['OptID'];?>">
				<?php echo $RowShifts['Name'];?></OPTION>
				<?php } ?>
				<select>
				  
			   </div>
			   
			    <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">DeHeading Date<span style="color:red">*</span></label>
                  <input required type="date" class="form-control" name='ProcessingDate' id="ProcessingDate"
				 max="<?php echo date("Y-m-d"); ?>" placeholder="Enter Sent Date" 
				 value="<?php echo date("Y-m-d"); ?>" >
                
				</div>

                <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">HeadON Wt(Kgs)<span style="color:red">*</span></label>
                  <input required type="text" class="form-control" name='HonWeight' id="HonWeight"
				  placeholder="Enter Hon Weight">
                </div>
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">HeadON Count<span style="color:red">*</span></label>
                  <input required type="text" class="form-control"  name="HonCount" id="HonCount" 
				  placeholder="Enter Hon Count">
                </div>
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">HeadON Count After Checking Count-2<span style="color:red">*</span></label>
                  <input required type="text" class="form-control"  name="CheckingCount" id="CheckingCount" 
				  placeholder="Enter After Checking Count">
                </div>
				
				
				

				
			<!--<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Supervisor</label>
                  <input type="text" class="form-control"  name="SupervisorID" id="SupervisorID" 
				  placeholder="Enter Supervisor Name">
                </div>-->
				
				
				 <div class="form-group col-sm-3" >
                  <label for="exampleInputPassword1">SuperVisor<span style="color:red">*</span>  </label>
				 <select required class="form-control select2" name="SupervisorID" id="SupervisorID">
				 <option value=""> Select</option>
				   <?php while($RowSupervisor=$StmtSuperVisor->fetch_array()) {
				?>
				<option Value="<?php echo $RowSupervisor['ID'];?>">
				<?php echo $RowSupervisor['UserName'];?></OPTION>
				<?php } ?>
				<select>
				  
			   </div>
				
                
				<div class="form-group col-sm-3">
                 <label for="exampleInputPassword1">Received HeadON Wt(Kgs)<span style="color:red">*</span></label>
                  <input required type="text" class="form-control"  name="ReceivedWeight" id="ReceivedWeight" 
				  placeholder="Enter Received Weight ">
                </div>
				
				
				<div class="form-group col-sm-3">
                 <label for="exampleInputPassword1">HL Quantity(Kgs)<span style="color:red">*</span></label>
                  <input required type="text" class="form-control"  name="WtAfterDH" id="WtAfterDH" 
				  placeholder="Enter HL Quantity(Kgs) ">
                </div>
				
				<div class="form-group col-sm-3">
                 <label for="exampleInputPassword1">HL Count<span style="color:red">*</span></label>
                  <input required type="text" class="form-control"  name="CountAfterDH" id="CountAfterDH" 
				  placeholder="Enter HL Count ">
                </div>
				
				<div class="form-group col-sm-3">
                 <label for="exampleInputPassword1">Count Net Yield(%)<span style="color:red">*</span> </label>
                  <input required type="text" class="form-control"  name="NetCount" id="NetCount" 
				  placeholder="Enter Net Count ">
                </div>
				
				<div class="form-group col-sm-3">
                 <label for="exampleInputPassword1">Return Wt(Kgs)</label>
                  <input type="text" class="form-control"  name="ReturnWeight" id="ReturnWeight" 
				  placeholder="Enter Return Weight ">
                </div>
				
				<div class="form-group col-sm-3">
                 <label for="exampleInputPassword1">Ice Consumption<span style="color:red">*</span></label>
                  <input  required type="text" class="form-control"  name="IceConsumption" id="IceConsumption" 
				  placeholder="Enter Ice Consumption ">
                </div>
				
				<div class="form-group col-sm-3">
                 <label for="exampleInputPassword1">Remarks</label>
                  <input type="text" class="form-control"  name="Remarks" id="Remarks" 
				  placeholder="Enter Remarks ">
                </div>
				
							</div>
				
			  <div class="col-xs-12 box-footer"  align="right">
			                  <a target="" href="#"  onclick="window.close();" class="btn btn-danger  "> Close</a>

                <button type="submit"  name='submit' class="btn btn-success">Submit</button>
              </div>
            </form>
       <!-- end col -->
</div> <!-- end row -->
</div> <!-- end row -->
</div> <!-- end card -->
                            </div><!-- end col-->
                         
<!--<div id="tdetails" class="table-responsive col-sm-12" >  -->

	
    <div class="card">
          
        <div class="card-body" id="ShowDataTable">
                    

  <!--<table id="" class="table table-bordered table-hover table-responsive" >-->
  <table  id="key-datatable" data-paging="false" 
  class="table dt-responsive wrap w-100" >										
		
<thead class="thead-light">
 
	  
	  <th>S.No</th>
	  <th>Station</th>
	  <th>Supplier</th>
	  <th>Batch</th>
	  <th>DeHeading Date</th>
	  <th>HeadON WT(Kgs)</th>
	  <th>Received HeadON Wt(Kgs) </th>
	  <th>HeadON Count</th>
	  <th>HeadON Count After Checking</th>
	  <th>HeadLess Count</th>
	  <th>HeadLess Weight</th>
	  <th>Returned Wt(if any)</th>
	  <th>Yield(%)</th>
	  <th>Count Net Yield(%)</th>
	  <th>Ice Consumption</th>
	  <th>Remarks</th>
	  <th>Remove</th>
	 

    </thead>
    <tbody>

	<?php
	
	



$S_NO=1;
	//echo $sql;
   while($RowLotDeheading=$StmtLotDeheading->fetch_array()){

   $StationID=$RowLotDeheading['StationID'];

   $SqlShedName="SELECT * FROM `PpcVAStations` where StationID='$StationID'";
   $StmtShedName=$iconn->query($SqlShedName);
   $RowShedName=$StmtShedName->fetch_array();
   
   $YEILD=ROUND(($RowLotDeheading['WtAfterDH']/$RowLotDeheading['HonWeight'])*100);
   $SHORTAGE=$RowLotDeheading['HonWeight']-$RowLotDeheading['ReceivedWeight'];
	
	?>
	<tr >
	
	
			
	<td  id="<?php  echo $RowLotDeheading['DHID'];?>" > <?php echo $S_NO; ?>    </td>
	<td id="<?php  echo $RowLotDeheading['DHID'];?>" > <?php echo $RowShedName['UnitName']; ?> </td>
	<td id="<?php  echo $RowLotDeheading['DHID'];?>" > <?php echo getFormatedDate($RowLotDeheading['ProcessingDate']); ?> </td>
	<td id="<?php  echo $RowLotDeheading['DHID'];?>" > <?php echo $RowLotDeheading['HonWeight']; ?> </td>
	<td id="<?php  echo $RowLotDeheading['DHID'];?>" > <?php echo $RowLotDeheading['HonWeight']; ?> </td>
	<td id="<?php  echo $RowLotDeheading['DHID'];?>" > <?php echo $RowLotDeheading['HonWeight']; ?> </td>
	<td id="<?php  echo $RowLotDeheading['DHID'];?>" > <?php echo $RowLotDeheading['ReceivedWeight']; ?> </td>
	<td id="<?php  echo $RowLotDeheading['DHID'];?>" > <?php echo  $RowLotDeheading['HonCount'];?> </td>
	<td id="<?php  echo $RowLotDeheading['DHID'];?>" > <?php echo  $RowLotDeheading['CheckingCount'];?> </td>
	<td id="<?php  echo $RowLotDeheading['DHID'];?>" > <?php echo $RowLotDeheading['CountAfterDH'];  ?> </td>
	<td id="<?php  echo $RowLotDeheading['DHID'];?>" > <?php echo  $RowLotDeheading['WtAfterDH']; ?> </td>
	<td id="<?php  echo $RowLotDeheading['DHID'];?>" > <?php echo  $RowLotDeheading['ReturnWeight']; ?> </td>
	<td id="<?php  echo $RowLotDeheading['DHID'];?>" > <?php echo  $YEILD; ?> </td>
	<td id="<?php  echo $RowLotDeheading['DHID'];?>" > <?php echo  $RowLotDeheading['NetCount']; ?> </td>
	<td id="<?php  echo $RowLotDeheading['DHID'];?>" > <?php echo  $RowLotDeheading['IceConsumption']; ?> </td>
	<td id="<?php  echo $RowLotDeheading['DHID'];?>" > <?php echo  $RowLotDeheading['Remarks']; ?> </td>
	<td>
	
	<button class="btn btn-link text-danger Delete" 
		data-id="<?php echo $RowLotDeheading['DHID']; ?>" 
		data-toggle="tooltip" data-placement="top" title="Delete" value=""
		data-original-title="Delete"><i class="mdi mdi-trash-can font-size-16">
	</i>
	</button>
	</td>
	
	
    </tr>
	
	
	  <?php 
	  $S_NO++;

   }
	  ?>

     </tbody>

  </table>
</div>

</div>

    
	
                
                </div>
                <!-- End Page-content -->

                 <?php include ($IncludesPath."footer.php");?>
                
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        

		<?php include ($IncludesPath."vendor-scripts.php");?>
		<?php include ($IncludesPath."NewScripts.php");?>
		<?php include ($IncludesPath."ShowDataTable.php");?>
  
       
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

 
 
 
</script>


<script>
/*$("#Type").change(function(e){
			e.preventDefault();
var Type=$('#Type').val(); 
if(Type!=''){			
			getContractors();
}
		});	

$("#ContractorID").change(function(e){
			e.preventDefault();	
			var ContractorID=$('#ContractorID').val(); 
			if(ContractorID!=''){
			getBatches();
			}
		});			
		
		
		
		
function getContractors(){
var Type=$('#Type').val(); 
	 $.ajax({
    type: 'POST',
    url: 'Ajax/AjSupplierDetails.php',
    data: {'Type' : Type},  
    success: function(response){
		 $("#ContractorID").html(response); 
	 }
  });	  
  }
  
  function getBatches(){
var ContractorID=$('#ContractorID').val(); 
	 $.ajax({
    type: 'POST',
    url: 'Ajax/AjBatchDetails.php',
    data: {'ContractorID' : ContractorID},  
    success: function(response){
		 $("#BatchID").html(response); 
	 }
  });	  
  }*/

</script>

<script>
			   
			   /*$("#StationID").change(function(e){
				e.preventDefault();	
			
			var StationID =$("#StationID").val();
		
			if(StationID==9 || StationID==12){
				document.getElementById("Type").disabled=false;
				document.getElementById("ContractorID").disabled=false;
				document.getElementById("BatchID").disabled=false;
				$('#Type').val('').change();
				$('#ContractorID').val('').change();
				$('#BatchID').val('').change();
			
			}else{
			    document.getElementById("Type").disabled=true;
				document.getElementById("ContractorID").disabled=true;
				document.getElementById("BatchID").disabled=true;
				$('#Type').val('').change();
				$('#ContractorID').val('').change();
				$('#BatchID').val('').change();
			}

		});*/

 </script>
