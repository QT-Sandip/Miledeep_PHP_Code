<?php
$Status=$_SESSION['Status']; 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Util.php");


$SqlSuperVisor="SELECT * FROM `RmUsersList` WHERE UserType=5";
$StmtSuperVisor=$iconn->query($SqlSuperVisor);	



$AryCurrentDate=getCurrentDateTime();
$PostedDateTime=$AryCurrentDate['TimeStamp'];
global $PostedDateTime;			

$LotID=$_GET['LotID'];
if(isset($_GET['VaDate'])){
 $VaDate=date('Y-m-d',strtotime($_GET['VaDate']));
}
$TodayDate=date('Y-m-d');


$Query='';
if($VaDate!=''  && $LotID==0){
$Query="and ProcessingDate='$VaDate'";
}
else if($VaDate==''  && $LotID==0){
	 $Query="and ProcessingDate='$TodayDate'";
}

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



  $SqlLotVA="SELECT * FROM `PpcValueAddition` WHERE LotID='$LotID' $Query";
$StmtLotVA=$iconn->query($SqlLotVA);

	
$SqlContractor=" SELECT * FROM `PpcContractMaster` WHERE Status=1";
$StmtContractor=$iconn->query($SqlContractor);

$SqlDeheadingSheds="SELECT * FROM `PpcVAStations` WHERE UnitStatus=1";
$StmtDeheadingSheds=$iconn->query($SqlDeheadingSheds);


$SqlShifts="SELECT * FROM `PpcOptions` WHERE Value='SHIFTS'";
$StmtShifts=$iconn->query($SqlShifts);

$SqlVariety="SELECT * FROM `PpcOptions` WHERE Value='VARIETY'";
$StmtVariety=$iconn->query($SqlVariety);	

if($LotID!=0){
$PageTitle="Add ValueAddition Report  - ".$LotNumer.'('.$Count.'C/'.$Weight.'Kgs)';
}
else{
$PageTitle="Add ValueAddition Report";
}
$PageTitleFolder="PreProcessing";

if(isset($_POST['submit'])){
	
	
	$SqlDHInsert="INSERT INTO `PpcValueAddition`(`LotID`,
	`SentCount`, `SentWeight`, `ReceivedWeight`, 
	`ReturnWeight`, `ProcessingDate`, `PostedDate`, 
	`WtAftrVA`, `CountAftrVA`, `VarietyID`, `ShiftID`, 
	`StationID`, `PostedUserID`, `Remarks`, `Supervisor`, 
	`IceConsumption`,`ContractorID`,`BatchID`)VALUES('$LotID','$HonCount','$HonWeight',
	'$ReceivedWeight','$ReturnWeight','$ProcessingDate',
	'$PostedDateTime','$WtAftrVA','$CountAftrVA',
	'$VarietyID','$ShiftID','$StationID','$UserID',
	'$Remarks','$Supervisor','$IceConsumption','$ContractorID','$BatchID')";

	$StmtDHInsert=$iconn->query($SqlDHInsert);
	$VLID = $iconn->insert_id;
	 
	if($StmtDHInsert){
		legacyAuditLogInsertById('PpcValueAddition', 'VLID', $VLID, $_POST, [
			'ReferenceNo' => $VLID,
			'Remarks' => 'Value addition report created',
		], 'PreProcessing');
		
		
echo '<script language="javascript" type="text/javascript"> 
                alert("Success");
                window.location = "AddVAReport.php?LotID='.$LotID.'";
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

   <!-- <body data-sidebar="dark">-->
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
                  <label for="exampleInputPassword1">SuperVisor<span style="color:red">*</span>  </label>
				 <select required class="form-control select2" name="Supervisor" id="Supervisor">
				 <option value=""> Select</option>
				   <?php while($RowSupervisor=$StmtSuperVisor->fetch_array()) {
				?>
				<option Value="<?php echo $RowSupervisor['ID'];?>">
				<?php echo $RowSupervisor['UserName'];?></OPTION>
				<?php } ?>
				<select>
				  
			   </div>
				
			   
			   
		
			   
			    <div class="form-group col-sm-3" >
                  <label for="exampleInputPassword1">Select Shift  </label>
				 <select class="form-control select2" name="ShiftID" id="ShiftID">
				 
				   <?php while($RowShifts=$StmtShifts->fetch_array()) {
				?>
				<option 
				<?php if($RowShifts['OptID']==$RowRmLots["ShiftID"]){ ?> selected="selected" <?php } ?>
				Value="<?php echo $RowShifts['OptID'];?>">
				<?php echo $RowShifts['Name'];?></OPTION>
				<?php } ?>
				<select>
				  
			   </div>
			   
			   <div class="form-group col-sm-3" >
                  <label for="exampleInputPassword1">Select Variety  </label>
				 <select class="form-control select2" name="VarietyID" id="VarietyID">
				 
				   <?php while($RowVariety=$StmtVariety->fetch_array()) {
				?>
				<option 
				<?php if($RowVariety['OptID']==$RowRmLots["VarietyID"]){ ?> selected="selected" <?php } ?>
				Value="<?php echo $RowVariety['OptID'];?>">
				<?php echo $RowVariety['Name'];?></OPTION>
				<?php } ?>
				<select>
				  
			   </div>
			   
			   
			    <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Sent Date</label>
                  <input required type="date" class="form-control" name='ProcessingDate' id="ProcessingDate"
				 max="<?php echo date("Y-m-d"); ?>" placeholder="Enter Sent Date" 
				 value="<?php echo date("Y-m-d"); ?>" >
                
				</div>

                <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">HL Wt(Kgs)</label>
                  <input required type="text" class="form-control" name='HonWeight' id="HonWeight"
				  placeholder="Enter HL Weight">
                </div>
				
				
				<div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">HL Count</label>
                  <input type="text" class="form-control"  name="HonCount" id="HonCount" 
				  placeholder="Enter HL Count">
                </div>
				
				
				
				
                
				<div class="form-group col-sm-3">
                 <label for="exampleInputPassword1">Received HL Wt(Kgs)</label>
                  <input type="text" class="form-control"  name="ReceivedWeight" id="ReceivedWeight" 
				  placeholder="Enter Received HL Weight ">
                </div>
				
				
				<div class="form-group col-sm-3">
                 <label for="exampleInputPassword1">Weight after VA(Kgs)</label>
                  <input type="text" class="form-control"  name="WtAftrVA" id="WtAftrVA" 
				  placeholder="Enter Weight after VA(Kgs) ">
                </div>
				
				<div class="form-group col-sm-3">
                 <label for="exampleInputPassword1">Count after VA</label>
                  <input type="text" class="form-control"  name="CountAftrVA" id="CountAftrVA" 
				  placeholder="Enter Count after VA ">
                </div>
				
				<div class="form-group col-sm-3">
                 <label for="exampleInputPassword1">Return Wt(Kgs)</label>
                  <input type="text" class="form-control"  name="ReturnWeight" id="ReturnWeight" 
				  placeholder="Enter Return Weight ">
                </div>
				
				
				
			
					
				
				
								<div class="form-group col-sm-3">
                 <label for="exampleInputPassword1">Ice consumption</label>
                  <input type="text" class="form-control"  name="IceConsumption" id="IceConsumption" 
				  placeholder="Enter Ice consumption">
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

</div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->

                        
		<div   class="col-12">

    <div class="card">
          
        <div class="card-body" id="ShowDataTable">
                    

 <table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">
      <tr >
	  
	  <th  >S.No</th>
	  <th >Station</th>
	  <th >Sent Date</th>
	  <th >Variety</th>
	  <th >Sent <br>HL WT(Kgs)</th>
	  <th >Received<br> HL QTY(Kgs) </th>
	  <th >HL Count</th>
	  <th >Count<br> After VA</th>
	  <th >Weight<br> After VA</th>
	  <th >Returned <br>Weight<br>(if any)</th>
	  <th >Yield(%)</th>
	  <th >Ice</th>
	  <th >Remarks</th>
	  <th >Remove</th>

      </tr>
    </thead>
    <tbody>

	<?php
	
	



$S_NO=1;
	//echo $sql;
   while($RowLotVA=$StmtLotVA->fetch_array()){

   $StationID=$RowLotVA['StationID'];
   $VarietyID=$RowLotVA['VarietyID'];
   
   $SqlVarietyName="SELECT * FROM `PpcOptions` where OptID='$VarietyID'";
   $StmtVarietyName=$iconn->query($SqlVarietyName);
   $RowVarietyName=$StmtVarietyName->fetch_array();
   
   $VarietyName=$RowVarietyName['Name'];

   $SqlShedName="SELECT * FROM `PpcVAStations` where StationID='$StationID'";
   $StmtShedName=$iconn->query($SqlShedName);
   $RowShedName=$StmtShedName->fetch_array();
   
   $YEILD=ROUND(($RowLotVA['WtAftrVA']/$RowLotVA['SentWeight'])*100);
   $SHORTAGE=$RowLotVA['SentWeight']-$RowLotVA['ReceivedWeight'];
	
	?>
	<tr >
			
	<td  id="<?php  echo $RowLotVA['VLID'];?>" > <?php echo $S_NO; ?>    </td>
	<td id="<?php  echo $RowLotVA['VLID'];?>" > <?php echo $RowShedName['UnitName']; ?> </td>
	<td id="<?php  echo $RowLotVA['VLID'];?>" > <?php echo getFormatedDate($RowLotVA['ProcessingDate']); ?> </td>
	<td id="<?php  echo $RowLotVA['VLID'];?>" > <?php echo $VarietyName; ?> </td>
	<td id="<?php  echo $RowLotVA['VLID'];?>" > <?php echo $RowLotVA['SentWeight']; ?> </td>
	<td id="<?php  echo $RowLotVA['VLID'];?>" > <?php echo $RowLotVA['ReceivedWeight']; ?> </td>
	<td id="<?php  echo $RowLotVA['VLID'];?>" > <?php echo  $RowLotVA['SentCount'];?> </td>
	<td id="<?php  echo $RowLotVA['VLID'];?>" > <?php echo $RowLotVA['CountAftrVA'];  ?> </td>
	<td id="<?php  echo $RowLotVA['VLID'];?>" > <?php echo  $RowLotVA['WtAftrVA']; ?> </td>
	<td id="<?php  echo $RowLotVA['VLID'];?>" > <?php echo  $RowLotVA['ReturnWeight']; ?> </td>
	<td id="<?php  echo $RowLotVA['VLID'];?>" > <?php echo  $YEILD; ?> </td>
	<td id="<?php  echo $RowLotVA['VLID'];?>" > <?php echo  $RowLotVA['IceConsumption']; ?> </td>
	<td id="<?php  echo $RowLotVA['VLID'];?>" > <?php echo  $RowLotVA['Remarks']; ?> </td>
	<TD>
	
	<button class="btn btn-link text-danger Delete" 
		data-id="<?php echo $RowLotVA['VLID']; ?>" 
			
							data-toggle="tooltip" data-placement="top" title="Delete" value=""
							data-original-title="Delete"><i class="mdi mdi-trash-can font-size-16">
							</i></button>
	</TD>
	
	
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
	  var VaID =$(this).data('id');

   		var r = confirm("Are You Sure You Want To Delete "+VaID+"?");
   if (r == true) {

		 //alert(VaID);		  
		$.ajax({
		  url:'Ajax/AjDltAl.php',
		  type:'POST',
		  data:{'TYPE':'VA_REPORT','D_ID':VaID},
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
			   
			  /* $("#StationID").change(function(e){
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

</html>				

