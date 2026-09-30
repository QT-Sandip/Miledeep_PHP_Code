<?php


// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");
$ClientConfigData = $_SESSION['ClientConfigData'];
$EnableTrackCodeInAbReport = $ClientConfigData['EnableTrackCodeInAbReport'];//anil

$PageTitleFolder="Quality Control";
$PageTitle="Daily Lab AB Report";


$DOA=$_GET['DOA'];
$DOR=$_GET['DOR'];
$Year=$_GET['Year'];
$Month=$_GET['Month'];

$DateFilter=getDateFilter($Year,$Month);
$PlantID = $_GET['PlantId'];

$join = '';$where = '';
if(!empty($PlantID)){
	$join = " LEFT JOIN RmArrivalDetatils RAD ON RAD.`RmArrivalID`=RLD.`RmReferenceID` ";
	$where = "  AND RAD.ProcessingStationID= $PlantID ";
	if(!empty($EnableTrackCodeInAbReport)){
		$join .= " LEFT JOIN (
                        SELECT RRB.`ArrivalID`,`TrackCode`
                        FROM RmReceivingDetails RD
                        LEFT JOIN RmReceivingBreakup RRB ON RRB.`ReceivingID`=RD.`ReceivingID`
                        GROUP BY RD.`ReceivingID`
                    ) TC ON TC.`ArrivalID` = RAD.`RmArrivalID` ";
	}
}elseif(!empty($EnableTrackCodeInAbReport)){
	$join = " LEFT JOIN RmArrivalDetatils RAD ON RAD.`RmArrivalID`=RLD.`RmReferenceID`
			LEFT JOIN (
					SELECT RRB.`ArrivalID`,`TrackCode`
					FROM RmReceivingDetails RD
					LEFT JOIN RmReceivingBreakup RRB ON RRB.`ReceivingID`=RD.`ReceivingID`
					GROUP BY RD.`ReceivingID`
				) TC ON TC.`ArrivalID` = RAD.`RmArrivalID` ";
}

 $SqlLots=" select * from RmLotDetatils as RLD $join where RLD.PurchaseDate !='' 
  and RLD.PurchaseDate $DateFilter $where
				 and not exists (select 1 from QcRmAbReport QRAR where QRAR.LotID = RLD.RmLotID)
				 ORDER BY  RLD.PurchaseDate DESC, RLD.LotNumber"; 
				 $StmtLots=$iconn->query($SqlLots);

  $D_O_ANALYSIS = date("Y-m-d", strtotime($DOA)); 
   $where = '';
   if($PlantID){
		$where = " AND PlantID = '$PlantID' ";
   }
   $SQL_CODE_LIST="SELECT * FROM `QcRmAbReport` as QRA
   left join RmLotDetatils as RLD on RLD.RmLotID=QRA.LotID
   where  `AnalysisDate`='$D_O_ANALYSIS'
   $where
     order by AbRepID desc"; 
$STMT_CODE_LIST=$iconn->query($SQL_CODE_LIST);

$SqlDropDownOptions="SELECT * FROM `QcABOptions`  ORDER BY SortOrder ASC   "; 
$StmtDropDownOptions=$iconn->query($SqlDropDownOptions);
$ArrayDropDownOptions=array();
while($RowDropDownOptions=$StmtDropDownOptions->FETCH_ARRAY()){
	$ArrayDropDownOptions[]=$RowDropDownOptions;
}

$sql_LAB_USERS="SELECT * FROM `$TblQcLabTechnologist` where LbuStatus=1 ";
$stmt_LAB_USERS=$iconn->query($sql_LAB_USERS);
$LabUsersList = [];
while ($LAB_USERS_details=$stmt_LAB_USERS->fetch_assoc()){
	$LabUsersList[] =  $LAB_USERS_details;
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

extract($_POST);
extract($_GET);
$PostedDate=date("Y-m-d");


if(isset($_POST['submit'])){
	 	
	$D_O_ANALYSIS = date("d-m-Y", strtotime($ANALYSIS_DATE));
	$D_O_COMPLEATION = date("d-m-Y", strtotime($REPORT_DATE));
	
	
	
	
	/*if($SAMPLE_TYPE=='Harvest'){
	//$LOT_NUMBER=$ENTRY_LOT_NUMBER;	
	$LOT_NUMBER=$RowLotNo['LotNumber'];	
	}else{
	$LotId=0;
	}
	
	if($SAMPLE_TYPE!='Harvest'){
		$LotId=0;
	}*/
	$Fields = $Values = '';
	if(!empty($EnableTrackCodeInAbReport)){
		if($PlantIdHdn){
			$PlantId = $PlantIdHdn;
		}
		$Fields = ",`VerifiedBy`,PlantId";
		$Values = ",'$VerifiedBy','$PlantId'";
	}

	   $sql_org_insert="INSERT INTO `QcRmAbReport`
		( `AnalysisDate`, `ReportDate`, `SampleDetails`, `LotNO`,`LotID`,
     `Result`,  `AOZ`, `SEM`, `CAP`, `AHD`, `AMOZ`, 
	`Parameter`, `Sulphite` , `LoadingStatus`, `Remarks`, `Shift`, 
	`TestedBy`,`AbStatus`, `PostedDate`, `UserID`$Fields)
	VALUES
	('$ANALYSIS_DATE','$REPORT_DATE','$SAMPLE_TYPE','$SAMPLE_DETAILS','$LotId',
	'$RESULT','$AOZ','$SEM','$CAP','$AHD','$AMOZ','$PARAMETER','$SULPHITE' ,
	'$LOADING_STATUS','$REMARKS','$SHIFT','$TESTED_BY','$AbStatus','$PostedDate','$UserID'$Values)";

	

// ECHO $sql_org_insert ;
// EXIT;


	$stmt1=$iconn->query($sql_org_insert);

	$iid = $iconn->insert_id;

	if($stmt1){
		legacyAuditLogInsertById('QcRmAbReport', 'AbRepID', $iid, $_POST, ['Remarks' => 'AB Report Added'], 'QualityControl');
		
		
		$sql_count="SELECT count(`AnalysisDate`) as TOTAL,
(SELECT count(`Shift`) FROM `QcRmAbReport` where `Shift`='DAY' and `AnalysisDate`='$ANALYSIS_DATE') as DAY,
(SELECT count(`Shift`) FROM `QcRmAbReport` where `Shift`='NIGHT' and `AnalysisDate`='$ANALYSIS_DATE') as NIGHT

 FROM `QcRmAbReport` where   `AnalysisDate`='$ANALYSIS_DATE'";
 
 

$stmt_ip=$iconn->query($sql_count);
$row=$stmt_ip->fetch_array();

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


/*$sql_org_insert="UPDATE `inputmaterial_details` SET 
`ANTI_BIOTIC_STATUS`='$ANTI_BIOTIC_STATUS'  WHERE `id`='$LOT_ID'";

$stmt1=$iconn->query($sql_org_insert);*/

if($TOTAL_REPORTS==1){
	
	$sql_org_insert="INSERT INTO `DAILY_LAB_DATA`
								( `ANALYSIS_DATE`, `REPORT_DATE`, `TOTAL_REPORTS`, `DAY_TOTAL`, 
								`NIGHT_TOTAL`, `REJECT`, `HOLD`) VALUES 
	('$ANALYSIS_DATE','$REPORT_DATE','$TOTAL_REPORTS','$DAY_TOTAL',
	'$NIGHT_TOTAL','0','0')";

	$stmt1=$iconn->query($sql_org_insert);
	if($stmt1){
		$DailyLabInsertId = $iconn->insert_id;
		legacyAuditLogInsertById('DAILY_LAB_DATA', 'ID', $DailyLabInsertId, $_POST, ['Remarks' => 'Daily Lab Data Created'], 'QualityControl');
	}
}else{
	
	

	
	 $sql_org_insert="UPDATE `DAILY_LAB_DATA` SET 
`TOTAL_REPORTS`='$TOTAL_REPORTS',`DAY_TOTAL`='$DAY_TOTAL',`NIGHT_TOTAL`='$NIGHT_TOTAL' WHERE `ANALYSIS_DATE`='$ANALYSIS_DATE'";

$stmt1=$iconn->query($sql_org_insert);
if($stmt1){
	$oldDailyLabRow = legacyAuditFetchRowById('DAILY_LAB_DATA', 'ID', 0, 'QualityControl');
	legacyAuditLogUpdateById('DAILY_LAB_DATA', 'ID', 0, $oldDailyLabRow, $_POST, ['Remarks' => 'Daily Lab Data Updated'], 'QualityControl');
}

}

		echo "<script>alert('Success')</script>";

		//header('Location: DailyLabABCreate.php?DOA='.$D_O_ANALYSIS.'&DOR='.$D_O_COMPLEATION);
		$urlParam = '';
		if($PlantID){
			$urlParam = '&PlantID='.$PlantID;
		}
		header('Location: RmAbReport.php?DOA='.$D_O_ANALYSIS.'&DOR='.$D_O_COMPLEATION.'&Year='.$Year.'&Month='.$Month.$urlParam);


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

                <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Lot No</label>
						
		 <select class="form-control select2" name="LotId" id="LotId">	
				<option Value=""> Select Lot</option>
		 
 <?php while($RowLotsList=$StmtLots->fetch_array()) { 
			

        //$STATION_ID=$RowRmProcessingStations['ID'];			
				?>  
				
				<option Value="<?php echo $RowLotsList['RmLotID'];?>">
				<?php echo (!empty($EnableTrackCodeInAbReport) && !empty($RowLotsList['TrackCode'])) ? '('.$RowLotsList['TrackCode'].') '.$RowLotsList['LotNumber'] : $RowLotsList['LotNumber'];?></OPTION>				
				<?php } ?>
				<select> 
                </div>
				
                <div class="form-group col-sm-3" >

                  <label for="exampleInputEmail1">Analysis Date</label>

                  <input required type="date" class="form-control" name='ANALYSIS_DATE' id="ANALYSIS_DATE" value="<?php  echo date("Y-m-d", strtotime($DOA));?>" max="<?php  echo date("Y-m-d");?>">

                </div>
				


                <div class="form-group col-sm-3">

                  <label for="exampleInputPassword1">Report Date</label>

                  <input required type="date" class="form-control" name='REPORT_DATE' id="REPORT_DATE" value="<?php  echo date("Y-m-d", strtotime($DOR));?>" >

                </div>
				
				

				
				

				<div class="form-group col-sm-3">

                  <label for="exampleInputPassword1">Sample Type</label>

                 <select class="form-control select2" name="SAMPLE_TYPE" id="SAMPLE_TYPE">
			
					<option value="Harvest" >Harvest</option>
					<option value="Pre Harvest" >Pre Harvest</option>
					<option value="Finished product" >Finished product</option>
				  <select>
                </div>
				
				

               
				
				
				
				<div class="form-group col-sm-3">

                  <label for="exampleInputPassword1">Sample Details</label>
					<input type="hidden" name="LOT_ID" id="LOT_ID">  
                  <input  type="text" class="form-control" name='SAMPLE_DETAILS' id="SAMPLE_DETAILS" 
				  placeholder="Enter Sample Details">

                </div>
				
				

                <div class="form-group col-sm-3">

                  <label  for="exampleInputPassword1">Count</label>

                  <input required type="text" class="form-control" name="COUNT" id="COUNT" 
				  placeholder="Enter Count">

                </div>

                <div class="form-group col-sm-3" >

                  <label for="exampleInputEmail1">Quantity</label>

                  <input required type="text" class="form-control"  name="QUANTITY" id="QUANTITY" 
				  placeholder="Enter Quantity">

                </div>

               
				

                <div class="form-group col-sm-3" >

                  <label for="exampleInputEmail1">Parameter</label>

                  <select class="form-control select2" name="PARAMETER" id="PARAMETER"
				  <?php echo !empty($EnableTrackCodeInAbReport)?'disabled':'';?>>
				  <option >NA</option>
				  <option >AOZ</option>
				  <option >AMOZ</option>
				  <option >AHD</option>
				  <option >SEM</option>
				  <option >CAP</option>
				
				  <select>
                </div>

 <div class="form-group col-lg-3">

                  <label for="exampleInputPassword1"> Loading Status</label>

                  <select class="form-control select2" name="AbStatus" id="AbStatus">
		         <option Value=""> Select </option>

					 <?php foreach($ArrayDropDownOptions as $DropDownOptions){
					if($DropDownOptions['Category']=='TestStatus'){
						$Options= $DropDownOptions['Name'];
						$Value= $DropDownOptions['OptID']; ?>
			 
					<option value="<?php echo $Value ?>"><?php echo $Options ?></option>
					<?php  }}?>
					                  </select>
				

                 
                </div>
				
		

				

				 <div class="form-group col-sm-3">

                  <label for="exampleInputPassword1">Shift</label>

                  <select class="form-control select2" name="SHIFT" id="SHIFT">
					<option value="DAY" >DAY</option>
					<option Value="NIGHT" >NIGHT</option>
				 	<option value="GENERAL">GENERAL</option>
				
				  <select>
                </div>
				
				
				<div class="form-group col-sm-3">

                  <label for="exampleInputPassword1">Tested By</label>

                 
					 <select class="form-control select2" name="TESTED_BY" id="TESTED_BY">
				   
				   <?php foreach ($LabUsersList as $row){ ?>
				     <option value="<?php echo $row['LUID'] ?>"><?php echo $row['LbuName'] ?></option>
				   
				 <?php  }?>
				
						  
				  </select>
               
                </div> 
				<?php 
				if(!empty($EnableTrackCodeInAbReport)){?>
					<div class="form-group col-sm-3">

						<label for="exampleInputPassword1">Verified By</label>

						
							<select class="form-control select2" name="VerifiedBy" id="VerifiedBy">
						
						<?php foreach ($LabUsersList as $row){ ?>
							<option value="<?php echo $row['LUID'] ?>"><?php echo $row['LbuName'] ?></option>
						
						<?php  }?>
						
								
						</select>
				
					</div>

					<div class="form-group col-sm-3">

						<label for="exampleInputPassword1">Plant</label>
							<input type="hidden" name="PlantIdHdn" id="PlantIdHdn">
						
							<select class="form-control select2" name="PlantId" id="PlantId">
							<option value="">Select</option>
							<?php foreach ($PlantList as $row){ ?>
								<option value="<?php echo $row['ProcessingStationID'] ?>"
								><?php echo $row['StationName'] ?></option>
							
							<?php  }?>
						
								
						</select>
				
					</div>
				<?php }?> 
				
				
				<div class="form-group col-sm-3" >

                  <label for="exampleInputEmail1">Sulphite</label>

				  
				   <input <?php echo !empty($EnableTrackCodeInAbReport)?'disabled':'';?> required type="text" class="form-control" name='SULPHITE' id="SULPHITE" placeholder="Enter Quantity">

				

                </div>
				

				
				
                <div class="form-group col-sm-3" >

                  <label for="exampleInputEmail1">Remarks</label>

				  
				   <input  type="text" class="form-control" name='REMARKS' id="REMARKS" placeholder="Enter Remarks">

				

                </div>
                </div>
				<div class="row">
<div class="form-group col-sm-2">

                  <label for="exampleInputPassword1">AOZ</label>

                 <input required type="text" class="form-control"  name="AOZ" id="AOZ" 
				  placeholder="Enter AOZ">

                </div>

			 <div class="form-group col-sm-2">

                  <label for="exampleInputPassword1">SEM</label>

                 <input required type="text" class="form-control"  name="SEM" id="SEM" 
				  placeholder="Enter SEM">

                </div>
			 <div class="form-group col-sm-2">

                  <label for="exampleInputPassword1">CAP</label>

                 <input required type="text" class="form-control"  name="CAP" id="CAP" 
				  placeholder="Enter CAP">

                </div>
				 <div class="form-group col-sm-2">

                  <label for="exampleInputPassword1">AHD</label>

                 <input required type="text" class="form-control"  name="AHD" id="AHD" 
				  placeholder="Enter AHD">

                </div>
				 <div class="form-group col-sm-2">

                  <label for="exampleInputPassword1">AMOZ</label>

                 <input required type="text" class="form-control"  name="AMOZ" id="AMOZ" 
				  placeholder="Enter AMOZ">

                </div>
				 <div class="form-group col-sm-2">

                  <label for="exampleInputPassword1">Result</label>

                 <input required type="text" class="form-control"  name="RESULT" id="RESULT" 
				  placeholder="Enter Result">

                </div>
                </div>
                	

				

				

               
                
  <div class="col-xs-12 box-footer"  align="right">
  				<!--<a href="DailyLabReports.php?id=0" class="btn btn-danger">Close</a>-->
<a target="" href="#"  onclick="window.close();" class="btn btn-danger  "> Close</a>
               <button type="submit"  name='submit' class="btn btn-success">Submit</button>
 
				
				
   </div>
				</div>
            </form>
           
       <!-- end col -->
                        </div> <!-- end row -->
				<div class="row">
                            <div class="col-12">

        <div class="card">
          
        <div class="card-body" id="ShowDataTable">
                    

  <table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >	
 <thead class="thead-light">

      <tr>
	  
	  
	  <th>Sample </th>
	  <th>Lot No</th>
	  <th>Count</th>
	  <th>Quantity</th>
	  <th>AOZ</th>
	  <th>SEM</th>
	  <th>CAP</th>
	  <th>AHD</th>
	  <th>AMOZ</th>
	 <!-- <th>Result</th>
	  <th>Parameter</th>-->
	  <th>Loading Status</th>
	  <th>Shift</th>
	  <th>Remarks</th>
	  <th>Tested By</th>
	  <th>Actions</th>
	  
	
      </tr>
    </thead>
    <tbody>
	<?php
	//echo $sql;
   while($row=$STMT_CODE_LIST->fetch_array()){
		$TestedBy = $row['TestedBy'];
		$ID = $row['AbRepID'];

		$sqlTester = "SELECT LbuName AS UserName FROM `QcLabTechnologist`WHERE  LUID='$TestedBy' ";
		$StmtTester = $iconn->query($sqlTester);
		$RowTester = $StmtTester->fetch_array();			
		$TestedBy = $RowTester['UserName'];

	?>
	<tr>			
	

		
	<td id="<?php  echo $row['DAY_CODE'];?>" > <?php echo $row['SampleDetails']; ?></td>
	<td id="<?php  echo $row['DAY_CODE'];?>" > <?php echo $row['LotNO']; ?></td>
	<td id="<?php  echo $row['DAY_CODE'];?>" > <?php echo $row['PurchaseCount']; ?></td>
	<td id="<?php  echo $row['DAY_CODE'];?>" > <?php echo $row['PurchaseWeight']; ?></td>
	<td id="<?php  echo $row['DAY_CODE'];?>" > <?php echo $row['AOZ']; ?></td>
	<td id="<?php  echo $row['DAY_CODE'];?>" > <?php echo $row['SEM']; ?></td>
	<td id="<?php  echo $row['DAY_CODE'];?>" > <?php echo $row['CAP']; ?></td>
	<td id="<?php  echo $row['DAY_CODE'];?>" > <?php echo $row['AHD']; ?></td>
	<td id="<?php  echo $row['DAY_CODE'];?>" > <?php echo $row['AMOZ']; ?></td>
	<!--<td id="<?php  echo $row['DAY_CODE'];?>" > <?php echo $row['Result']; ?></td>
	<td id="<?php  echo $row['DAY_CODE'];?>" > <?php echo $row['Parameter']; ?></td>-->
    <td id="<?php  echo $row['DAY_CODE'];?>" > <?php echo $row['LoadingStatus']; ?></td>
	
	<td id="<?php  echo $row['DAY_CODE'];?>" > <?php echo $row['Shift']; ?></td>
	<td id="<?php  echo $row['DAY_CODE'];?>" > <?php echo $row['Remarks']; ?></td>
	<td id="<?php  echo $row['DAY_CODE'];?>" > <?php echo $TestedBy; ?></td>
	 <td> 
	 
	 <a target="_blank" href="DailyABLabEdit.php?id=<?php echo $ID?>" 
						 class="mr-3 text-primary"  data-placement="top"
						 title="Edit" data-original-title="Edit"
						 ><i class="mdi mdi-pencil font-size-16"></i></a>
	<!--<a target="_blank" href="DailyABLabEdit.php?id=<?php echo $ID?>"
					class="btn btn-primary eia_report">  Edit  </a>-->
			



			
		</td>
      

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
		var PlantId = "<?php echo !empty($_SESSION['UnitID']) ? $_SESSION['UnitID'] : (!empty($_GET['PlantId']) ? $_GET['PlantId'] : '');?>";
		var isFound = false;

		$("#PlantId option").each(function () {
			if (parseInt($(this).val()) === parseInt(PlantId) && PlantId != 0) {
				isFound = true;
				$("#PlantId").val(PlantId).trigger('change');
				$('#PlantIdHdn').val(PlantId);
				return false; // break loop
			}
		});

		$("#PlantId").prop("disabled", isFound);
	<?php }?>
	showData();
 /*$(document).on('change','#SAMPLE_TYPE',function(e){
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
*/




$(document).on('change','#LotId',function(e){
       e.preventDefault();
       var LotId=$(this).val();
	   var Type='Count';

		$.ajax({
		  url:'Ajax/AjInputvalues.php',
		  type:'POST',
		  	dataType: 'json',

		  data:{'LotId':LotId,'Type':Type},
		  success:function(response){
			  		 var myJSON = JSON.parse(JSON.stringify(response));

//alert(myJSON['IECCode']);
			//$("#COUNT").val(myJSON['PurchaseCount']+'C & Wt '+myJSON['PurchaseWeight']);
			$("#COUNT").val(myJSON['PurchaseCount']);
			$("#QUANTITY").val(myJSON['PurchaseWeight']);
			$("#SAMPLE_DETAILS").val(myJSON['LotNumber']);

				
					}
			})
		
    
    	});
		
	/*$(document).on('click','#lot_id',function(e){
		
		
		
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
   
*/


    

    	$('#continuebtn').on('click',function(){

    

    		$('form').submit();

    	});

    });    



</script>



</html>

			

