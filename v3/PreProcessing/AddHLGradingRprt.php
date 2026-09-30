<?php
$Status=$_SESSION['Status']; 
$UserID=$_SESSION['UserID'];
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Util.php");
include_once $_SERVER['DOCUMENT_ROOT'] .'/config/global.php';
include_once ROOT.'config/api-call.php';

$ClientConfigData = (object)$_SESSION['ClientConfigData'];
$ConsolidateLotCount = $ClientConfigData->ConsolidateLotCount;

$ApiUrl=BASEURL.'Api?f=postHlGradingReport'; 

$AryCurrentDate=getCurrentDateTime();
$PostedDateTime=$AryCurrentDate['TimeStamp'];
global $PostedDateTime;			


$MixLotID = $LotID=$_GET['ID'];

$Mix=$_GET['Mix'];

if($ConsolidateLotCount == 0){
	$V2LotIDs = str_replace('_', ',', $LotID);
	$LotIDsSql = "SELECT GROUP_CONCAT(RLD2.`RmLotID` SEPARATOR '_') AS RmLotID
					FROM RmLotDetatils RLD 
					JOIN RmLotDetatils RLD2 ON RLD2.RmReferenceID = RLD.RmReferenceID and RLD2.PurchaseCount = RLD.PurchaseCount
					WHERE RLD.`RmLotID` IN($V2LotIDs)";
	$StmtLotIDs = $iconn->query($LotIDsSql);
	$RowLotIDs = $StmtLotIDs->fetch_assoc();
	$pos = strpos($RowLotIDs['RmLotID'], "_");
	if ($pos !== false) { 
		$Mix = 1;
		$MixLotID = $RowLotIDs['RmLotID'];
	}
}
if($Mix==1){
	$SqlGradingMix="SELECT * FROM `PpcHLGradingDetails` where MixIDs='$MixLotID' AND CreatedFrom=0";
	$StmtGradingMix=$iconn->query($SqlGradingMix);
}
else{
	$SqlGradingMix="SELECT * FROM `PpcHLGradingDetails` where LotID='$LotID' AND CreatedFrom=0";
	$StmtGradingMix=$iconn->query($SqlGradingMix);
}
$Count=mysqli_num_rows($StmtGradingMix);
if($Count==1){
	$RowGradingMix=$StmtGradingMix->fetch_array();
	$MixIDs=$RowGradingMix['MixIDs'];
}
else{
	//$MixIDs=$_GET['ID'];
	$MixIDs = $MixLotID;
}
//}
extract($_POST);
extract($_GET);

//ROBO
/*if(isset($_GET['Mix'])){
	$Mix=$_GET['Mix'];
}else{
	$Mix='0';
}*/
//echo $Mix; exit;

if($Mix==0){
	$SqlGradingHl="SELECT * FROM `PpcHLGradingDetails` where LotID='$LotID'  AND CreatedFrom=0";
}else{
	 $IDS=str_replace('_' , ',' ,$MixIDs);
	$SqlGradingHl="SELECT * FROM `PpcHLGradingDetails` where LotID in ($IDS)  AND CreatedFrom=0";
}
  //echo $SqlGradingHl; exit;
$StmtGradingHl=$iconn->query($SqlGradingHl);
$RowCount=$StmtGradingHl->num_rows;
$RowGradingHl=$StmtGradingHl->fetch_array();

if( $RowGradingHl['IsMix']!='' && $RowGradingHl['MixIds']!='NA'){
 $MixedIds=$RowGradingHl['MixIds'];
 $Mix=$RowGradingHl['IsMix'];
}


if($Mix==0){	
 $SqlLots="SELECT * FROM `RmLotDetatils` where RmLotID='$LotID'";
$StmtLots=$iconn->query($SqlLots);
$RowLots=$StmtLots->fetch_array();

if($RowLots['ProductionWeight']!=0){
	$LotWeight+=$RowLots['ProductionWeight'];
}else{
   $LotWeight+= $RowLots['PurchaseWeight'] ;
}

$RowLots['PurchaseWeight']=$LotWeight;

}else{
$IDS=str_replace('_' , ',' ,$MixIDs);
 $SqlLots="SELECT * FROM `RmLotDetatils` where RmLotID in ($IDS)"; 
 $StmtLots=$iconn->query($SqlLots);
$LotNumbers='';
$LotWeight='';
while($RowLots = $StmtLots->fetch_array()){

	 $RowLots['ProductionWeight'];
	if($RowLots['ProductionWeight']!=0){
	 	$LotWeight+=$RowLots['ProductionWeight'];
	}else{
		$LotWeight+= $RowLots['PurchaseWeight'] ;
	}
//$LotWeight+=$RowLots['PurchaseWeight'];
if(!in_array($RowLots['LotNumber'], $AryLotNumbers) && !in_array($RowLots['PurchaseCount'], $AryCounts)){
	$AryLotNumbers[]=$RowLots['LotNumber'];
	$AryCounts[]=$RowLots['PurchaseCount'];
}
}
$RowLots['LotNumber']=implode(',',$AryLotNumbers);
$RowLots['PurchaseCount']=implode(',',$AryCounts);
$RowLots['PurchaseWeight']=$LotWeight;

}

	

$LotNumer=$RowLots['LotNumber'];
$Count=$RowLots['PurchaseCount'];
 $Weight=$RowLots['PurchaseWeight']; 

	

if($Mix==0){
$GradingBreakUpSql="SELECT * FROM `PpcHLGradingBreakup` where LotID='$LotID'  AND CreatedFrom=0";

}
else{
$IDS=str_replace('_' , ',' ,$MixIDs);
 $GradingBreakUpSql="SELECT * FROM `PpcHLGradingBreakup` where LotID in ($IDS)  AND CreatedFrom=0";	
}

$GradingBreakUpStmt=$iconn->query($GradingBreakUpSql);

//ROBO
$SqlGradMachines="SELECT * FROM `AdminGradingMachines`";
$StmtGradMachines=$iconn->query($SqlGradMachines);

$SqlProcessingSheds="SELECT * FROM `PpcVAStations` WHERE UnitStatus=1";
$StmtProcessingSheds=$iconn->query($SqlProcessingSheds);


$PageTitle="Add Grading Report  - ".$LotNumer.'('.$Count.'C/'.$Weight.'Kgs)';

$PageTitleFolder="PreProcessing";


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
										
					 <div class="form-group col-sm-3" >
                  <label for="exampleInputPassword1">Select Station  </label>
				<select required class="form-control select2" name="StationID" id="StationID">
				 <option value=''>Select </option>
				<?php while($RowProcessingSheds=$StmtProcessingSheds->fetch_array()) {	?>
				<option 
				<?php if($RowProcessingSheds['StationID']==$RowRmLots["StationID"]){ ?> selected="selected" <?php } ?>
				Value="<?php echo $RowProcessingSheds['StationID'];?>">
				<?php echo $RowProcessingSheds['UnitName'];?></OPTION>
				<?php } ?>
				<select>
				  
			   </div>
			   

    		 <div class="form-group col-sm-3" >
                  <label for="exampleInputPassword1">Select Grading Machine  </label>
				<select class="form-control select2" name="GradingMachine" id="GradingMachine">
				 
				<?php while($RowGradMachines=$StmtGradMachines->fetch_array()) {	?>
				<option 
				<?php if($RowGradMachines['MachineID']==$RowRmLots["StationID"]){ ?> selected="selected" <?php } ?>
				Value="<?php echo $RowGradMachines['MachineID'];?>">
				<?php echo $RowGradMachines['MachineName'];?></OPTION>
				<?php } ?>
				<select>
				  
			   </div>
			   
			   <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Grading Date</label>
                 <input required type="date" class="form-control" name='GradingDate' id="GradingDate"
				  value="" placeholder="Enter Count" max="<?php  echo date("Y-m-d");?>">
				
                </div>
			   
			    <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Start Time</label>
                 <input required type="time" class="form-control" name='StartTime' id="StartTime"
				  value="" placeholder="Enter Count">
				
                </div>
				
				 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">End Time</label>
                 <input required type="time" class="form-control" name='EndTime' id="EndTime"
				  value="" placeholder="Enter Count">
				</div>
				
				 <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Break Time(Minutes)</label>
                 <input required type="number" class="form-control" name='BreakMin' id="BreakMin"
				  value="" placeholder="Enter Break Time In Minutes">
				
                </div>

				</div>
				
					 
            </form>
			<div class="col-lg-12 "  align="right">
			<a target="" href="#"  onclick="window.close();" class="btn btn-danger  "> Close</a>

			<?php if($RowCount > 0){ ?>
			
			  
			  <a class="btn btn-success"  href="AddHLGradingBreakUp.php?ID=<?php echo $LotID; ?>">Add Break Up</a>
			<!-- <button class="btn  btn-warning Delete" 
		data-id="<?php echo $LotID; ?>" 
			
							data-toggle="tooltip" data-placement="top" title="Delete" value=""
							data-original-title="Delete">Delete</button>-->
							
<button class="btn  btn-link text-danger Delete" 
		data-id="<?php echo $LotID; ?>" 
			
							data-toggle="tooltip" data-placement="top" title="Grading Delete" value=""
							data-original-title="Delete"><i class="mdi mdi-trash-can font-size-16">
							</i></button>
			<?php } else { ?>
			  
			
                <button   name='add' id='add' class="btn btn-success">Save</button>
				
				<!--<button type="button" class="btn btn-primary waves-effect waves-light" id="sa-params">Delete</button>-->
			<?php } ?>
              </div>
       <!-- end col -->
</div> <!-- end row -->
</div> <!-- end row -->
</div> <!-- end card -->
                           
                         
<!--<div id="tdetails" class="table-responsive col-sm-12" >  -->

	<div   class="col-12">
    <div class="card">
          
        <div class="card-body" id="ShowDataTable">
                    

  <!--<table id="" class="table table-bordered table-hover table-responsive" >-->
  <table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">
      <tr >
	  
		  <th>S.No</th>
	  <th>Grading Date</th>
	  <th>Count</th>
	  <th>Total Weight</th>	  
	  <!--<th >Tubs</th>-->
	  <th>Actions</th>

      </tr>
    </thead>
    <tbody>

	<?php
	
//ROBO
$s_no=1;
$HlWeight=0;
	while($GradingBreakUpRow=$GradingBreakUpStmt->fetch_array()){
		$HLWeight+=$GradingBreakUpRow['HLWeight'];


$S_NO=1;
	//echo $sql;
   
   	?>
	<tr >
			
	<td  id="<?php  echo $GradingBreakUpRow['ID'];?>" > <?php echo ++$Sno; ?>    </td>
	<td  id="<?php  echo $GradingBreakUpRow['ID'];?>" > <?php echo getFormatedDate($RowGradingHl['GradingDate']); ?>    </td>
		<td id="<?php  echo $GradingBreakUpRow['ID'];?>" > <?php echo $GradingBreakUpRow['HLCount']; ?> </td>	

	<td id="<?php  echo $GradingBreakUpRow['ID'];?>" > <?php echo $GradingBreakUpRow['HLWeight']; ?> </td>
	<!--<td id="<?php  echo $GradingBreakUpRow['ID'];?>" > <?php echo $GradingBreakUpRow['Tubs']; ?> </td>-->


	<TD>
	
	<button class="btn btn-link text-danger DeleteGrade" 
		data-id="<?php echo $GradingBreakUpRow['DataID']; ?>" 
			
							data-toggle="tooltip" data-placement="top" title="Delete" value=""
							data-original-title="Delete"><i class="mdi mdi-trash-can font-size-16">
							</i></button>
	</TD> 
	
	
	
    </tr>
	<?php } ?>

   <tr >

    
	 <th>Total HL</th>
	 <th></th>
    <td><?php echo $HLWeight ?> Kgs</td>
    <th>Yield (%)</th>
    <!--<td> <?php //echo round(($HlWeight/$ROW_LOTS['WEIGHT_IN_KGS'])*100,2) ?>-- % </td>-->
     </tr>


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
$('document').ready(function(){
//showData();


});
    	$(document).on('click','#add',function(e){
		e.preventDefault();

			//alert('Hi');
			var IsValid=false;

			var LotID='<?php echo $LotID ?>';
			var UserID='<?php echo $UserID ?>';
			var Mix='<?php echo $Mix ?>';
			
			var GradingMachine= $('#GradingMachine').val();
			var GradingDate= $('#GradingDate').val();
			var StartTime= $('#StartTime').val();
			var EndTime= $('#EndTime').val();
			var BreakMin= $('#BreakMin').val();
			var StationID= $('#StationID').val();
			
			 if(StationID==''){
				alert("Please Select Station");
				$('#StationID').focus();
			}else if(GradingDate==''){
				alert("Enter Grading Date");
				$('#GradingDate').focus();
			}else if(StartTime==''){
				alert("Enter StartTime");
				$('#StartTime').focus();
			}else if(EndTime==''){
				alert("Enter EndTime");
				$('#EndTime').focus();
			}else if(BreakMin==''){
				alert("Enter Break Time");
				$('#BreakMin').focus();
			}else{
				IsValid=true;
			}
			
			if(!IsValid){
				return;
			}




			var finalData = {
				'LotID':LotID,
				'UserID':UserID,
				'GradingMachine':GradingMachine,
				'GradingDate':GradingDate,
				'StartTime':StartTime,
				'EndTime':EndTime,
				'BreakMin':BreakMin,
				'Mix': Mix,				
				'StationID': StationID,				
				'From':'Web',
				'CreatedFrom':'0',//v2
				};


		$.ajax({
		  url:'Ajax/AjaxGrading',
		  type:'POST',
		 // dataType: 'json',
		  data:{'Data':JSON.stringify(finalData),'Type':'Report'},
		 
		  success:function(data){
			  // var obj = JSON.parse(JSON.stringify(data));
			  var obj = JSON.parse(data);
			    console.log(obj.Msg);//return;
			 if(obj.Error==null){
				alert(obj.Msg);
				window.location.href="AddHLGradingBreakUp.php?ID=<?php echo $LotID ?>";
				}else{
				alert(obj.Error);
				}
			}
			});
  });






$(document).on('click', '.Delete', function(e){
		  var LotID =$(this).data('id');


   		var r = confirm("Are you sure do you want To delete Total Grading"+LotID+"?");
   if (r == true) {

		 //alert(LotID);		  
		$.ajax({
		  url:'Ajax/AjDltAl.php',
		  type:'POST',
          data:{'TYPE':'GRDNG_DELETE','LotID':LotID},
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


$(document).on('click', '.DeleteGrade', function(e){
		  var GradeID =$(this).data('id');


   		var r = confirm("Are you sure do you want To delete "+GradeID+"?");
   if (r == true) {

		 //alert(LotID);		  
		$.ajax({
		  url:'Ajax/AjDltAl.php',
		  type:'POST',
          data:{'TYPE':'GRDNGID_DELETE','GradeID':GradeID},
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
  function reply_click(clicked_id) {

var LOT_ID='<?php echo $LOT ?>';



		// alert(clicked_id);
	//	 alert("No Access Pls Contact Admin");
	$.ajax({
		  url:'Ajax/AjDltAl.php',
		  type:'POST',
		  data:{'TYPE':'HL_GRDNG_DELETE','LOT_ID':LOT_ID, 'COUNT' : clicked_id},
		  success:function(data){
			  console.log(data);

			 $("#CODE_LIST").html(data);
			 alert("Deleted");
			location.reload();
			}
			});
  }
</script>
<Script>

$('document').ready(function(){

	var LOT_ID='<?php echo $count ?>';
	//alert(LOT_ID);

	if(LOT_ID !=''){
	// document.getElementById("GRADING_MACHINE").readOnly  = true;
	 document.getElementById("GRADING_MACHINE").disabled = true;
	}




});

</script>


