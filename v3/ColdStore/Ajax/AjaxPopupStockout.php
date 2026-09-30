<?php 
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

	$BarCodeID=$_POST['BarCodeID'];
	$StockID=$_POST['StockID'];
	$SQL_OPTIONS="SELECT * FROM `AdminGeneralOptions` WHERE  DeptID='CS' ORDER BY Name asc";
    $STMT_OPTIONS=$iconn->query($SQL_OPTIONS);
    $ARY_OPTIONS=array();
    WHILE($ROW=$STMT_OPTIONS->FETCH_ARRAY()){
	$ARY_OPTIONS[]=$ROW;
    }

	 $sql_bd="SELECT StockID,cb.BarCodeID,BarCodeValue,coalesce(PoNo,'NA') as PoNo,AvalMcs,LooseBags,ShortName,
CSP.PackingStyle,CSD.Grade, CSD.FreezingType,ProductionDate,ProductionTypeID, ProductCategoryID FROM `CsBarCodes` as cb
left join `CsStockData` as CSD on CSD.BarCodeID = cb.BarCodeID 
left join `ScrmPoData` as CPOS on CPOS.PoID = CSD.PoID
left join  `ScrmPIVarietyList` as VL on VL.VarietyID=CSD.VarietyID
left join `AdminPackingStyles` as CSP on CSP.PckID = CSD.PackingStyleID 
left join `AdminGeneralOptions` as CGOPG on CGOPG.OptID = CSD.ProductionTypeID 
WHERE StockID = '$StockID' AND (AvalMcs !=0 or LooseBags !=0)";
	

	$stmt_crd=$iconn->query($sql_bd);
	$stmt_crd_count=$stmt_crd->num_rows;
	
	if($stmt_crd_count=='0'){
			$row=array();		
			$row['Error']='No Data Found';
			$row['Msg']='No Data Found';
			echo json_encode($row);
	}
		
	
	
	
	
	 $data .='<thead>
		
		
		<tr>
		<!--<td class ="hiden" id="bid">'.$BID.'</td>
		<td class ="hide" id="rckid" >'.$RACK_ID.'</td>-->
		<th>ID</th>
		<th>Variety</th>
		<th>Grade</th>
		<th>MCS</th>
		<th>Loose</th>
		<th>Packing</th>
		<th>Reason</th>
		<!--<th>Remarks</th>-->
		<th>MCS</th>
		<th>Loose</th>
		<th>Action</th>
		</tr>
		</thead>
		
		<tbody>';
	 while($row_pid=$stmt_crd->fetch_array()){
		 
	
	$RACK_ID=$row_pid['RackID'];
	$RACK_NAME =$row_pid['RackName'];
	$BLOCK_NAME =$row_pid['BlockName'];
	$PoNo = $row_pid['PoNo'];
	$GRADE = $row_pid['Grade'];
	$VARITY = $row_pid['ShortName'];
	$StockID = $row_pid['StockID'];
	$AvalMcs = $row_pid['AvalMcs'];
	$PACKING = $row_pid['PackingStyle'];
	$RACK_ID=$row_pid['RackID'];
	$LooseBags=$row_pid['LooseBags'];	
	
	

			
			
				$data .='
				<tr>
		
		
		
		<!--<td id='.$BID.'>'.$BID.'</td> -->
		<td >'.$StockID.'</td>
		<td >'.$VARITY.'</td>
		<td >'.$GRADE.'</td>
		<td name="mcs_aval" id="mcs_aval'.$StockID.'">'.$AvalMcs.'</td>
		<td name="LooseBags_aval" id="LooseBags_aval'.$StockID.'">'.$LooseBags.'</td>
		<td >'.$PACKING .'</td>
		<td >
		
		  <select class="form-control select2" name="OUT_TYPE" id="OUT_TYPE'.$StockID.'">';
		 foreach($ARY_OPTIONS as $value){
					if($value['OptionType']=='CS_STOCK_OUT_OPTIONS'){
						$OPTIONS= $value['Name'];
						$VALUE= $value['Value']; 
			 
		$data .='<option value="'.$VALUE.'">'.$OPTIONS.'</option>';
		 }}

		$data .='</select>';
		
		
		
		$data .='
		</td>
		<!--<td ><input type="text" class="form-control" name="reason_out" id="reason_out'.$StockID.'">	</td> -->
		<td ><input type="number" class="form-control" name="mcs_out" id="mcs_out'.$StockID.'">	</td>
		<td ><input type="number" class="form-control" name="LooseBags_out" id="LooseBags_out'.$StockID.'">	</td>
		<td>
		<Button type="text" class="btn btn-danger in_repck_bt" name="in_repck_bt" id="'.$StockID.'">Out</Button>
		</td>
			</tr>';
					  
}
					
		echo $data .='</tbody>';
?>
<Script>

$('document').ready(function(){
	
	  
	  $("#SelectedbulkInvId").select2();
});
</Script>
