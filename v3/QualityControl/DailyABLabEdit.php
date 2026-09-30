<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$ClientConfigData = $_SESSION['ClientConfigData'];
$EnableTrackCodeInAbReport = $ClientConfigData['EnableTrackCodeInAbReport'];//anil

$PageTitleFolder="Quality Control";
$PageTitle="Daily Lab AB Report";
// ROBO
//echo '<pre>';print_r($_POST);die;
extract($_POST);
extract($_GET);

  $DOA=$_GET['DOA'];
  $DOR=$_GET['DOR']; 
  $id=$_GET['id']; 

$SqlLabReports="SELECT * FROM `QcRmAbReport` as QRA
left join RmLotDetatils as RLD on RLD.RmLotID=QRA.LotID where  AbRepID='$id'";
$StmtLabReports=$iconn->query($SqlLabReports);

$SqlLabUsers="SELECT * FROM `QcLabTechnologist` where LbuStatus=1";
$stmt_LAB_USERS=$iconn->query($SqlLabUsers);

$LabUsersList = [];
while ($LAB_USERS_details=$stmt_LAB_USERS->fetch_assoc()){
	$LabUsersList[] =  $LAB_USERS_details;
}

   
$SqlAbReportData="SELECT * FROM `QcRmAbReport` where  AbRepID='$id'";
$StmtAbReportData=$iconn->query($SqlAbReportData);
$ROW_CODE_LIST=$StmtAbReportData->fetch_array();

$SqlDropDownOptions="SELECT * FROM `QcABOptions`  ORDER BY SortOrder ASC   "; 
$StmtDropDownOptions=$iconn->query($SqlDropDownOptions);
$ArrayDropDownOptions=array();
WHILE($RowDropDownOptions=$StmtDropDownOptions->FETCH_ARRAY()){
	$ArrayDropDownOptions[]=$RowDropDownOptions;
}

if(isset($_POST['submit'])){
//$D_O_ANALYSIS = date("Y-m-d", strtotime($ROW_LAB_REPORTS['ANALYSIS_DATE'])); 
//$D_O_COMPLEATION = date("Y-m-d", strtotime($ROW_LAB_REPORTS['REPORT_DATE'])); 
 


	if($SAMPLE_TYPE=='Harvest'){
	 $LOT_NUMBER=$ENTRY_LOT_NUMBER;	
	 }else{
	$LOT_ID=0;
	}
	$Fields = '';
	if(!empty($EnableTrackCodeInAbReport)){
		if($PlantIdHdn){
			$PlantId = $PlantIdHdn;
		}
		$Fields = ",`VerifiedBy`= '$VerifiedBy',`PlantId` = '$PlantId' ";
	}
	 $oldRow = legacyAuditFetchRowById('QcRmAbReport', 'AbRepID', $id, 'QualityControl');
	 $SqlUpdateData="UPDATE `QcRmAbReport` SET 
	`AnalysisDate`='$ANALYSIS_DATE',`ReportDate`='$REPORT_DATE',`SampleDetails`='$SAMPLE_TYPE',
	`Result`='$RESULT',`AOZ`='$AOZ',`SEM`='$SEM',`CAP`='$CAP',`AHD`='$AHD',`AMOZ`='$AMOZ',
	`Parameter`='$PARAMETER',`Sulphite`='$SULPHITE',`Remarks`='$REMARKS',`Shift`='$SHIFT',
	 `TestedBy`='$TESTED_BY',`AbStatus`='$AbStatus'$Fields WHERE AbRepID=$id";
		//echo '<pre>';echo $SqlUpdateData;die;
	$StmtUpdateData=$iconn->query($SqlUpdateData);

	if($StmtUpdateData){
		legacyAuditLogUpdateById('QcRmAbReport', 'AbRepID', $id, $oldRow, $_POST, ['Remarks' => 'AB Report Updated'], 'QualityControl');
		
	
$SqlCount="SELECT count(`AnalysisDate`) as TOTAL,
(SELECT count(`Shift`) FROM `QcRmAbReport` where `Shift`='DAY' and `AnalysisDate`='$ANALYSIS_DATE') as DAY,
(SELECT count(`Shift`) FROM `QcRmAbReport` where `Shift`='NIGHT' and `AnalysisDate`='$ANALYSIS_DATE') as NIGHT
 FROM `QcRmAbReport` where   `AnalysisDate`='$ANALYSIS_DATE'";
$StmtCount=$iconn->query($SqlCount);
$row=$StmtCount->fetch_array();

$TOTAL_REPORTS=$row['TOTAL'];
$DAY_TOTAL=$row['DAY'];
$NIGHT_TOTAL=$row['NIGHT'];


 

$ANTI_BIOTIC_STATUS='';

if($LOADING_STATUS=='GRAY'){
	$ANTI_BIOTIC_STATUS='0';
}else if($LOADING_STATUS=='RED'){
	$ANTI_BIOTIC_STATUS='1';
}else if($LOADING_STATUS=='GREEN'){
	$ANTI_BIOTIC_STATUS='2';
}if($LOADING_STATUS=='BLUE'){
	$ANTI_BIOTIC_STATUS='3';
}



if($TOTAL_REPORTS==1){
	
	$SqlInsertData="INSERT INTO `DAILY_LAB_DATA`( `ANALYSIS_DATE`, `REPORT_DATE`, `TOTAL_REPORTS`, `DAY_TOTAL`,
								`NIGHT_TOTAL`, `REJECT`, `HOLD`) VALUES
	('$ANALYSIS_DATE','$REPORT_DATE','$TOTAL_REPORTS','$DAY_TOTAL','$NIGHT_TOTAL','0','0')";
	$stmt1=$iconn->query($SqlInsertData);
}else{
	
	 $SqlInsertData="UPDATE `DAILY_LAB_DATA` SET 
      `TOTAL_REPORTS`='$TOTAL_REPORTS',`DAY_TOTAL`='$DAY_TOTAL',
	  `NIGHT_TOTAL`='$NIGHT_TOTAL' WHERE `ANALYSIS_DATE`='$ANALYSIS_DATE'";

    $stmt1=$iconn->query($SqlInsertData);
}

		echo "<script>alert('Success')</script>";

		//header('Location: printelisreport.php?id='.$iid);
		//header('Location: DailyLabReportCreate.php?DOA='.$D_O_ANALYSIS.'&DOR='.$D_O_COMPLEATION);
		header('Location: DailyABLabEdit.php?id='.$id);

	}

}

$PlantList = [];
if(!empty($EnableTrackCodeInAbReport)){
	$PlantSql = "SELECT ProcessingStationID,
						CASE 
						WHEN `ReportName` IS NOT NULL THEN `ReportName` ELSE `StationName` END AS `StationName`
				FROM RmProcessingStations";
	$stmtPlant=$iconn->query($PlantSql);
	
	while ($Plantdetails = $stmtPlant->fetch_assoc()){
		$PlantList[] =  $Plantdetails;
	}
}
//robo


 ?>


<!DOCTYPE html>
<html lang="en">
<link href="<?php echo  $href ?>"  rel="stylesheet" type="text/css" />
    <head>
        
            <?php include ($IncludesPath."title-meta.php"); ?>
		 	<?php  include ($IncludesPath."DbLinks.php");?>
            <?php include ($IncludesPath."head-css.php");?>

    </head>

    <body data-sidebar="dark">

	
	 <?php	include ($IncludesPath."PreLoader.php"); ?>

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

	
	<div class="modal fade" id="myModal" role="dialog">

    <div class="modal-dialog">

        <!-- Modal content-->

        <div class="modal-content">

            <div class="modal-header">

                <button type="button" class="close" data-dismiss="modal">&times;</button>

                <h4 class="modal-title modheading"></h4>

            </div>

            <div class="modal-body">


			<div class="box-body" id="ATT_DATA">
              
            </div>




            </div>

            <div class="modal-footer">

                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				
				

            </div>

        </div>

    </div>

</div>



          
	
<div class="row">
                            <div class="col-12">
                                <div class="card">
								
								<form role="form" method="post" enctype="multipart/form-data">
                                    <div class="card-body">
									
										<div class="row">


                <div class="form-group col-sm-3" >

                  <label for="exampleInputEmail1">Analysis Date</label>

                  <input required type="date" class="form-control" name='ANALYSIS_DATE'
				  id="ANALYSIS_DATE" value="<?php echo date("Y-m-d", strtotime($ROW_CODE_LIST['AnalysisDate']));?>">

                </div>
				


                <div class="form-group col-sm-3">

                  <label for="exampleInputPassword1">Report Date</label>

                  <input required type="date" class="form-control" name='REPORT_DATE' id="REPORT_DATE" 
				  value="<?php  echo date("Y-m-d", strtotime($ROW_CODE_LIST['ReportDate']));?>">

                </div>
				
				

				
				

				<div class="form-group col-sm-3">

                  <label for="exampleInputPassword1">Sample Type</label>
				     

                <select class="form-control select2" name="SAMPLE_TYPE" id="SAMPLE_TYPE">
					<option value="Pre Harvest"<?php if($ROW_CODE_LIST['SampleDetails']=="Pre Harvest") echo 'selected="selected"';?>>Pre Harvest</option>
					<option value="Harvest" <?php if($ROW_CODE_LIST['SampleDetails']=="Harvest") echo 'selected="selected"';?>>Harvest</option>
					<option value="Finished" <?php if($ROW_CODE_LIST['SampleDetails']=="Finished") echo 'selected="selected"';?>>Finished</option>
					<option value="Store Material" <?php if($ROW_CODE_LIST['SampleDetails']=="Store Material") echo 'selected="selected"';?>>Store Material</option>
					<option  value="Babu Basker" <?php if($ROW_CODE_LIST['SampleDetails']=="Babu Basker") echo 'selected="selected"';?>>Babu Basker</option>
					<option  value="SPC Material" <?php if($ROW_CODE_LIST['SampleDetails']=="SPC Material") echo 'selected="selected"';?>>SPC Material</option>
					<option  value="Finished product" <?php if($ROW_CODE_LIST['SampleDetails']=="Finished product") echo 'selected="selected"';?>>Finished product</option>
				<select>
                </div>
				

              
                <div class="form-group col-sm-3" >

                  <label for="exampleInputEmail1">Parameter</label>

                  <select class="form-control select2" name="PARAMETER" id="PARAMETER" 
				  <?php echo !empty($EnableTrackCodeInAbReport)?'disabled':'';?>>
				  
				  				
				  
				  <option value="NA"<?php if($ROW_CODE_LIST['PARAMETER']=="NA") echo 'selected="selected"';?>>NA</option>
				  <option value="AOZ"<?php if($ROW_CODE_LIST['PARAMETER']=="AOZ") echo 'selected="selected"';?>>AOZ</option>
				  <option value="AMOZ"<?php if($ROW_CODE_LIST['PARAMETER']=="AMOZ") echo 'selected="selected"';?>>AMOZ</option>
				  <option value="AHD"<?php if($ROW_CODE_LIST['PARAMETER']=="AHD") echo 'selected="selected"';?>>AHD</option>
				  <option value="SEM"<?php if($ROW_CODE_LIST['PARAMETER']=="SEM") echo 'selected="selected"';?>>SEM</option>
				  <option value="CAP"<?php if($ROW_CODE_LIST['PARAMETER']=="CAP") echo 'selected="selected"';?>>CAP</option>
				
				  <select>
                </div>

               <!-- <div class="form-group col-sm-3">

                  <label for="exampleInputPassword1"> Loading Status</label>

                  <select class="form-control" name="LOADING_STATUS" id="LOADING_STATUS">
				   <option value="GRAY" <?php if($ROW_CODE_LIST['LoadingStatus']=="GRAY") echo 'selected="selected"';?>>GRAY - TEST PENDING</option>
				  <option value="GREEN" <?php if($ROW_CODE_LIST['LoadingStatus']=="GREEN") echo 'selected="selected"';?>>GREEN - ACCEPTED</option>
				  <option value="RED" <?php if($ROW_CODE_LIST['LoadingStatus']=="RED") echo 'selected="selected"';?>>RED - REJECT</option>
				  <option value="BLUE" <?php if($ROW_CODE_LIST['LoadingStatus']=="BLUE") echo 'selected="selected"';?>>BLUE - ON HOLD</option>
				 
				 
				  <select>
                </div>-->
				
				<div class="form-group col-lg-3">

                  <label for="exampleInputPassword1"> Loading Status</label>

                  <select class="form-control select2" name="AbStatus" id="AbStatus">
		         <option Value=""> Select </option>

					 <?php foreach($ArrayDropDownOptions as $DropDownOptions){
					if($DropDownOptions['Category']=='TestStatus'){
						$Options= $DropDownOptions['Name'];
						$Value= $DropDownOptions['OptID']; ?>
			 
					<option <?php if($Value==$ROW_CODE_LIST['AbStatus']){ ?> selected=="selected"<?php } ?>
					value="<?php echo $Value ?>"><?php echo $Options ?></option>
					<?php  }}?>
					                  </select>
				

                 
                </div>


				

				 <div class="form-group col-sm-3">

                  <label for="exampleInputPassword1">Shift</label>

                  <select class="form-control select2" name="SHIFT" id="SHIFT" >
				  	<option value="DAY" <?php if($ROW_CODE_LIST['Shift']=="DAY") echo 'selected="selected"';?>>DAY</option>
				  	<option value="NIGHT" <?php if($ROW_CODE_LIST['Shift']=="NIGHT") echo 'selected="selected"';?>>NIGHT</option>
				 	<option value="GENERAL" <?php if($ROW_CODE_LIST['Shift']=="GENERAL") echo 'selected="selected"';?>>GENERAL</option>
				
				  <select>
                </div> 
				
				 <div class="form-group col-sm-3">

                  <label for="TESTED_BY">Tested By</label>			
				 <select class="form-control select2" name="TESTED_BY" id="TESTED_BY">				   
				   <?php 
				  $T_NAME= $ROW_CODE_LIST['TestedBy'];
				   
				   foreach ($LabUsersList as $row){ ?>
				     <option value="<?php echo $row['LUID']; ?>" 
					 <?php if($row['LUID']==$T_NAME) echo 'selected="selected"';?>><?php echo $row['LbuName']; ?></option>
				   
				 <?php  }?>
               </select>
                </div>   
				<?php 
				if(!empty($EnableTrackCodeInAbReport)){?>
					 <div class="form-group col-sm-3">

						<label for="VerifiedBy">Verified By</label>			
						<select class="form-control select2" name="VerifiedBy" id="VerifiedBy">				   
							<?php 
							$V_NAME= $ROW_CODE_LIST['VerifiedBy'];
							
							foreach ($LabUsersList as $row){ ?>
								<option value="<?php echo $row['LUID']; ?>" 
								<?php if($row['LUID']==$V_NAME) echo 'selected="selected"';?>><?php echo $row['LbuName']; ?></option>
							
							<?php  }?>
						</select>
					</div>
					<div class="form-group col-sm-3">

						<label for="PlantId">Plant</label>

						    <input type="hidden" name="PlantIdHdn" id="PlantIdHdn" value="<?php echo $ROW_CODE_LIST['PlantId'];?>">
							<select class="form-control select2" name="PlantId" id="PlantId">
							<option value="">Select</option>
							<?php foreach ($PlantList as $row){ ?>
								<option value="<?php echo $row['ProcessingStationID'] ?>"
								<?php if($row['ProcessingStationID']==$ROW_CODE_LIST['PlantId']) echo 'selected="selected"';?>
								><?php echo $row['StationName'] ?></option>
							
							<?php  }?>
						
								
						</select>
				
					</div> 
				<?php } ?>                    
				
				
				<div class="form-group col-sm-3" >

                  <label for="exampleInputEmail1">Sulphite</label>			  
				   <input <?php echo !empty($EnableTrackCodeInAbReport)?'disabled':'';?>  type="text" class="form-control" name='SULPHITE'
				   id="SULPHITE" placeholder="Enter Quantity"  value="<?php  echo $ROW_CODE_LIST['Sulphite']?>">
                </div>
				

				
				
                <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Remarks</label>				  
		<input  type="text" class="form-control" name='REMARKS' id="REMARKS" 
		placeholder="Enter Remarks" value="<?php  echo $ROW_CODE_LIST['Remarks']?>">				
                </div>
				
				
				 <div class="form-group col-sm-3">
                  <label for="exampleInputPassword1">Result</label>
                 <input  type="text" class="form-control"  name="RESULT" 
				 id="RESULT" value="<?php  echo $ROW_CODE_LIST['Result']?>"
				  placeholder="Enter Result">
               </div>


				 <div class="form-group col-sm-2">
                  <label for="exampleInputPassword1">AOZ</label>
                 <input  type="text" class="form-control"  name="AOZ" id="AOZ" value="<?php  echo $ROW_CODE_LIST['AOZ']?>"
				  placeholder="Enter Result">
                </div>
				
				
				 <div class="form-group col-sm-2">
                <label for="exampleInputPassword1">SEM</label>
                 <input  type="text" class="form-control"  name="SEM" id="SEM" value="<?php  echo $ROW_CODE_LIST['SEM']?>"
				  placeholder="Enter Result">
                </div>
				 
				 
			<div class="form-group col-sm-2">
                  <label for="exampleInputPassword1">CAP</label>
                 <input  type="text" class="form-control"  name="CAP" id="CAP" value="<?php  echo $ROW_CODE_LIST['CAP']?>"
				  placeholder="Enter CAP">
             </div>
				
				 <div class="form-group col-sm-2">
                  <label for="exampleInputPassword1">AHD</label>
                 <input  type="text" class="form-control"  name="AHD" id="AHD" value="<?php  echo $ROW_CODE_LIST['AHD']?>"
				  placeholder="Enter AHD">
                </div>
				
				
				 <div class="form-group col-sm-2">
                  <label for="exampleInputPassword1">AMOZ</label>
                 <input  type="text" class="form-control"  name="AMOZ" id="AMOZ" value="<?php  echo $ROW_CODE_LIST['AMOZ']?>"
				  placeholder="Enter AMOZ">
                </div>
				
				<div class="form-group col-sm-8">
                 
                </div>
				
				 <div class="col-sm-12 box-footer"  align="right">
                
				<!--<a href="DailyLabReports.php?id=0" class="btn btn-primary">Submit</a>-->
				<a target="" href="#"  onclick="window.close();" class="btn btn-danger  "> Close</a>

				<button type="submit"  name='submit' class="btn btn-success">Update</button>

   </div>
</div>
            </form>
           
       <!-- end col -->
                        </div> <!-- end row -->
                        </div> <!-- end row -->
				<div class="row">
                            <div class="col-12">

        <div class="card">
          
        <div class="card-body" id="ShowDataTable">
                    

  <table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >	
 <thead class="thead-light">

      <tr>
	  
	  
	  <th>Sample Details</th>
	  <th>Lot Number</th>
	  <th>Count</th>
	  <th>Quantity</th>
	  <th>AOZ</th>
	  <th>SEM</th>
	  <th>CAP</th>
	  <th>AHD</th>
	  <th>AMOZ</th>
	  <th>Result</th>
	  <th>Parameter</th>
	  <th>Loading Status</th>
	  <th>Shift</th>
	  <th>Remarks</th>
	  <th>Tested By</th>
	 
      </tr>
    </thead>
    <tbody>
	<?php
	//echo $sql;
   while($ROW_LAB_REPORTS=$StmtLabReports->fetch_array()){

		$ID = $row['ID'];
		$TestedBy = $ROW_LAB_REPORTS['TestedBy'];
		$sqlTester = "SELECT LbuName AS UserName FROM `QcLabTechnologist`WHERE  LUID='$TestedBy' ";
		$StmtTester = $iconn->query($sqlTester);
		$RowTester = $StmtTester->fetch_array();			
		$TestedBy = $RowTester['UserName'];

	?>
	<tr>			
	

		
	<td id="<?php  echo $ROW_LAB_REPORTS['DAY_CODE'];?>" > <?php echo $ROW_LAB_REPORTS['SampleDetails']; ?></td>
	<td id="<?php  echo $ROW_LAB_REPORTS['DAY_CODE'];?>" > <?php echo $ROW_LAB_REPORTS['LotNO']; ?></td>
	<td id="<?php  echo $ROW_LAB_REPORTS['DAY_CODE'];?>" > <?php echo $ROW_LAB_REPORTS['PurchaseCount']; ?></td>
	<td id="<?php  echo $ROW_LAB_REPORTS['DAY_CODE'];?>" > <?php echo $ROW_LAB_REPORTS['PurchaseWeight']; ?></td>
	<td id="<?php  echo $ROW_LAB_REPORTS['DAY_CODE'];?>" > <?php echo $ROW_LAB_REPORTS['AOZ']; ?></td>
	<td id="<?php  echo $ROW_LAB_REPORTS['DAY_CODE'];?>" > <?php echo $ROW_LAB_REPORTS['SEM']; ?></td>
	<td id="<?php  echo $ROW_LAB_REPORTS['DAY_CODE'];?>" > <?php echo $ROW_LAB_REPORTS['CAP']; ?></td>
	<td id="<?php  echo $ROW_LAB_REPORTS['DAY_CODE'];?>" > <?php echo $ROW_LAB_REPORTS['AHD']; ?></td>
	<td id="<?php  echo $ROW_LAB_REPORTS['DAY_CODE'];?>" > <?php echo $ROW_LAB_REPORTS['AMOZ']; ?></td>
	<td id="<?php  echo $ROW_LAB_REPORTS['DAY_CODE'];?>" > <?php echo $ROW_LAB_REPORTS['Result']; ?></td>
	<td id="<?php  echo $ROW_LAB_REPORTS['DAY_CODE'];?>" > <?php echo $ROW_LAB_REPORTS['Parameter']; ?></td>
    <td id="<?php  echo $ROW_LAB_REPORTS['DAY_CODE'];?>" > <?php echo $ROW_LAB_REPORTS['LoadingStatus']; ?></td>	
	<td id="<?php  echo $ROW_LAB_REPORTS['DAY_CODE'];?>" > <?php echo $ROW_LAB_REPORTS['Shift']; ?></td>
	<td id="<?php  echo $ROW_LAB_REPORTS['DAY_CODE'];?>" > <?php echo $ROW_LAB_REPORTS['Remarks']; ?></td>
	<td id="<?php  echo $ROW_LAB_REPORTS['DAY_CODE'];?>" > <?php echo $TestedBy; ?></td>
	 

      

        </tr>

	  <?php 

   }
	  ?>

     </tbody>

  </table>
</div>
    
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
		<?php include ($IncludesPath."ShowDataTable.php");?>
  
       
    </body>
	
	
<Script>



$('document').ready(function(){
	<?php 
	if(!empty($EnableTrackCodeInAbReport)){?>
		var PlantId = "<?php echo !empty($_SESSION['UnitID']) ? $_SESSION['UnitID'] : '';?>";
		var isFound = false;

		$("#PlantId option").each(function () {
			if ($(this).prop("selected") && $(this).val() != '') {
				isFound = true;
				return false;
			}
			if (parseInt($(this).val()) === parseInt(PlantId) && PlantId != 0) {
				isFound = true;
				$("#PlantId").val(PlantId).trigger('change');
				$('#PlantIdHdn').val(PlantId);
				return false; // break loop
			}
		});

		$("#PlantId").prop("disabled", isFound);
	<?php }?>

 $(document).on('change','#SAMPLE_TYPE',function(e){
		e.preventDefault();
		var SAMPLE_TYPE=$(this).val();
		//alert(SAMPLE_TYPE);
		
		if(SAMPLE_TYPE=='Harvest'){
		
		$.ajax({
				url:'Ajax/AjaxLotsList.php',
				type:'POST',
				data:{'MONTH' : 06,'YEAR': 2020},
				success:function(data){
					console.log(data);
					$("#LOT_NUMBER").html(data);
					$('#myModal').modal('toggle');
					$('#ATT_DATA').html(data);
					$('#example1').DataTable();
				}
			})
		}else{
			$('#SAMPLE_DETAILS').val('');
			 $('#COUNT').val('');
			 $('#QUANTITY').val('');
			 $('#LOT_ID').val('');
		}
		

	})


	$(document).on('click','#lot_id',function(e){
		
		
		
		 $('#COUNT').val('');
		$('#QUANTITY').val('');
		$('#NAME_ADDRESS').val('');
			
		e.preventDefault();

		
		var LOT_ID=$(this).val();
		//alert(LOT_ID);
		$('#myModal').modal('toggle');	
			$.ajax({
				url:'Ajax/AjaxAbLots.php',
				type:'POST',
				data:{'lid':LOT_ID,'type':''},
				success:function(data){
					console.log(data);
					 var obj = JSON.parse(data);
			 $('#LOT_ID').val(LOT_ID);
			 $('#SAMPLE_DETAILS').val(obj['LotNumber']);
			 $('#COUNT').val(obj['PurchaseCount']);
			 $('#QUANTITY').val(obj['PurchaseWeight']);
				}
			})
		
		
	});
   



    

    	$('#continuebtn').on('click',function(){

    

    		$('form').submit();

    	});

    });    



</script>


</html>

			

