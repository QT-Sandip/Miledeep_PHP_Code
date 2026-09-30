
<?php
//ini_set('display_errors', 1);

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Util.php");

//$Type=$_POST['Type'];

$ContractorID=$_POST['ContractorID'];
$SupplierType=$_POST['SupplierType'];
$PurchaseStationId=$_POST['PsId'];
$BatchID=$_POST['BatchID'];
$Date=$_POST['Date'];
$EntryType=$_POST['EntryType'];

$_SESSION['D_ID'];
$_SESSION['D_ID'];
$_SESSION['ACCESS_TYPE'];
$Query="";

/*if($PurchaseStationId !=0){
  $Query="and VA.StationID='$PurchaseStationId'";
  
}*/
if($SupplierType !=''){
  $Query=" WHERE Type='$SupplierType'";
 
}
if($ContractorID !=''){
  $Query.=" and  PCM.ContractorID='$ContractorID'";
 
}


$SqlShifts="SELECT * FROM `PpcOptions` WHERE Value='SHIFTS' and Status=1";
$StmtShifts=$iconn->query($SqlShifts);
 while($RowShifts=$StmtShifts->fetch_array()) {
	 $ArrayShifts[]=$RowShifts;
 }
 $accessType=$_SESSION['ACCESS_TYPE'];


    $SqlDetails="SELECT SubContactorID,SubContractorName,PSC.AutoCharge FROM PpcSubContractorMaster as PSC
  left join PpcContractMaster as PCM on PCM.ContractorID=PSC.ContactorID
  $Query and PCM.Status=1"; 

$StmtDetails=$iconn->query($SqlDetails);


$content .= '<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100">										
		
<thead class="thead-light">
          
				 
                  <th>Batch Name</th>
                  <th>Date</th>
                  <th>Bus/Auto</th>
				  <th>Persons <span style="color:red">*</span></th>
				  <th>Shift </th>
				  <th>Adjustments</th>
				  <th>Extras</th>
				  <th>Remarks</th>
				   <th>Actions</th>
      </thead>
    <tbody>';

	$PURCHASE_AMOUNT=0;
	$sno=1;

	while($RowDetails=$StmtDetails->fetch_array()){
		
		$BatchID= $RowDetails['SubContactorID'];
		
	/*$AryData=getBillingType($iconn,$BatchID,$Date,$PurchaseStationId);
	$BillingType=$AryData['BillingType'];
	$Note=$AryData['Note'];*/
	
	$SqlBatch="Select * from `PpcDailyBatchDetails` where BatchID='$BatchID' and Date='$Date' and StationID='$PurchaseStationId'";
	$StmtBatch=$iconn->query($SqlBatch);
	$RowBatch=$StmtBatch->fetch_array();
	$BatchCount=mysqli_num_rows($StmtBatch);
	
    $BillingType=$RowBatch['Type'];
	if($BillingType==1){
		$Note="In Deheading";
	}else if($BillingType==2){
		$Note="In Peeling";
	}
	

 $DailyBatchId=$RowBatch['ID'];
 if($DailyBatchId!=''){
	$BatchID= $DailyBatchId;
 }
 else{
	$BatchID=$BatchID;
 }
if($EntryType==1){
	$Url="AddHLBatchDetailsBreakup.php?DailyBatchId=$BatchID";
	
}
else{
$Url="AddPeelingBatchDetailsBreakup.php?DailyBatchId=$BatchID";
}

				$content .= '<tr>

                <td>'.$RowDetails['SubContractorName'].'</td>
                <td><B>'.getFormatedDatelines($RowBatch['Date']).'</B></td>
                <td>'.$RowDetails['AutoCharge'].'</td>
                <td> <input required type="text" class="form-control"  name="Persons" id="Persons'.$BatchID.'"
				  placeholder="Enter Persons" value="'.$RowBatch['Persons'].'"></td>
				 <td> <select required class="form-control select2" name="ShiftID" id="ShiftID'.$BatchID.'">';
				 
				    foreach($ArrayShifts as $RowShifts) {
				
				$content .= '<option ';if($RowBatch['ShiftID']==$RowShifts['OptID']){
					
				$content .= ' selected=="selected"';}
				$content .= ' Value="'.$RowShifts['OptID'].'">
				'.$RowShifts['Name'].'</OPTION>';
				 } 
			$content .= '	</select></td>
                <td> <input  type="text" class="form-control"  name="Adjustments" id="Adjustments'.$BatchID.'"
				  placeholder="Enter Adjustments" value="'.$RowBatch['Adjustments'].'"></td>
                <td> <input  type="text" class="form-control"  name="Extras" id="Extras'.$BatchID.'"
				  placeholder="Enter Extras" value="'.$RowBatch['Extras'].'"></td>
                <td> <input  type="text" class="form-control"  name="Remarks" id="Remarks'.$BatchID.'"
				  placeholder="Enter Remarks" value="'.$RowBatch['Remarks'].'"></td>
                
				';
				
				if($BatchCount!=0){
					
					if($BillingType==$EntryType){
				$content.=  '
				<td> <button class="btn btn-link text-primary BtnEditBatchDetails" data-id="'.$BatchID.'" 	
                data-value="'.$EntryType.'" data-toggle="tooltip" data-placement="top" title="" value=""
							data-original-title="Edit"><i class="mdi mdi-pencil font-size-18"></i></button> 
						 
				<a target="_blank" href="'.$Url.'"
						 class="mr-3 text-primary View" data-toggle="tooltip" data-placement="top"
						 title="View" data-original-title="View"><i class="mdi mdi-eye font-size-18"></i></a></td> ';
					}
						 	else{
					$content.=  '<td> <p style="color:red">'.$Note.'</p></td>';
					
				}
				}
				 else{
					// if($BillingType==$EntryType){
						$content.=  '
				<td> <button class="btn btn-link text-primary BtnAddBatchDetails" data-id="'.$BatchID.'" 			
							data-toggle="tooltip" data-placement="top" title="" data-value="'.$Date.'"
							data-value2="'.$PurchaseStationId.'"data-value3="'.$EntryType.'"
							data-original-title="Add"><i class="mdi mdi-plus font-size-18"></i></button></td> ';
						 
				
						
				 }
					
						 
				
				 
				$content.=  ' </tr>';
$sno++;

	}
	$content.='</tbody></table>'	;

echo $content;


/*function getBillingType($iconn,$BatchID,$Date,$PurchaseStationId){
	$SqlType="Select Type from `PpcDailyBatchDetails` where BatchID='$BatchID' and Date='$Date' 
	and StationID='$PurchaseStationId'";
	$StmtType=$iconn->query($SqlType);
	$RowType=$StmtType->fetch_array();
	$BillingType=$RowType['Type'];
	if($BillingType==1){
		$Note="In Deheading";
	}else if($BillingType==2){
		$Note="In Peeling";
	}
	
	
	$ArrayData['BillingType']=$BillingType;
	$ArrayData['Note']=$Note;
	return  $ArrayData;
}*/

?>
		 <script src="<?php echo $BaseDist ?>assets/libs/select2/js/select2.min.js"></script>


