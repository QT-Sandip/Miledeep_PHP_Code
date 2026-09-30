<?php 
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
/*		  'StationID' : StationID,
		  'SupplierType' : SupplierType,
		  'VarietyId' : VarietyId,	  
		  'Shift': Shift,
		  'EffectiveDate': EffectiveDate*/
		  
 $StationID=$_POST['StationID'];
 $SupplierType=$_POST['SupplierType'];
 $VarietyId=$_POST['VarietyId'];
 $Shift=$_POST['Shift'];
 $EffectiveDate=$_POST['EffectiveDate'];
 
 
echo getCostingRates($iconn);

function getCostingRates($iconn){
 
 global $StationID,
		$SupplierType,
		$VarietyId,
		$Shift,
		$EffectiveDate
		;	

  $sqlCheckHistory="SELECT * FROM `PpcHLVARatesHistory` WHERE 
 StationID='$StationID' and 
 ContractorType='$SupplierType' and 
 Variety='$VarietyId' and 
 Shift='$Shift' and 
 EffectiveDate='$EffectiveDate'";
 $stmtCheckHistory=$iconn->query($sqlCheckHistory);	
 $RowHistory=$stmtCheckHistory->fetch_array();	
 
 $TrackId=$RowHistory['ID'];
$IsEdit=false;
if($TrackId!=''){
$IsEdit=true;
$sql="SELECT * FROM PpcHLVARates WHERE RateID='$TrackId' order by abs(Grade) asc";
}else{
	if($VarietyId=='1'){
		$SType='HL';		
	}else{
		$SType='VA';
	}
$sql="SELECT * FROM PpcGradesGroupMaster WHERE SType='$SType' order by abs(Grade) asc";
}
//echo $sql;
		$stmt=$iconn->query($sql);
			$content = '<table >
							
<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">
				<tr>
				
				
				<th  align="CENTER" width="4%"><font size="'.$text.'"> <B>Range</B></font></th>
				<th  align="CENTER" width="4%"><font size="'.$text.'"> <B>Rate (INR)</B></font></th>
				
				</tr>
				</thead>
				';
		
			$count=0;
			while($PostDetails = $stmt->fetch_array()){
				 $CostRateID= $count;
			
				$content.= '<th  id="Grade'.$CostRateID.'"  align="CENTER" width="4%"><font size="'.$text.'"><B>'.$PostDetails['Grade'].'</B></font></th>';
				if($IsEdit){
				$content.='<th><input required type="text" step="0.5" class="form-control"  name="DayCode" 
				id="DayCode'.$CostRateID.'" placeholder="Enter " value="'.$PostDetails['PricePerKg'].'"></th></tr>';
				}else{
					$content.='<th><input required type="text" step="0.5" class="form-control"  name="DayCode"
					id="DayCode'.$CostRateID.'" placeholder="Enter " value=""></th></tr>';
				
				}
				
				
				$count++;
			}
			
		
				
				$content.= '</table>
				<tr>
				<button align="right"  id="SubmitBtn" value="" onClick="reply_click()" class="btn btn-success"> Submit</button>
				</tr>';
			
			$DATA['Count']=$count;
			 $DATA['Table']=$content;
			$DATA['IsEdit']=$IsEdit;
		return json_encode($DATA);
	} ?>