<?php 
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

	if(isset($_POST['RACK_ID'])){
	
	$RACK_ID=$_POST['RACK_ID'];
			
	$sql_bd="SELECT * FROM `CsStoresRackData` WHERE RowID = '$RACK_ID'";
	$stmt_crd=$iconn->query($sql_bd);
	$ROW_RD=$stmt_crd->fetch_array();
	$BID=$ROW_RD['BarCodeID'];
	$DataCount=$stmt_crd->num_rows;		
			
	 $sql="SELECT * FROM `CsStockData` as CSD 
			left JOIN CsStoresRackData as CRD on CRD.BarCodeID=CSD.BarCodeID
			left JOIN ScrmPIVarietyList as VL on VL.VarietyID=CSD.VarietyID
			left join AdminPackingStyles as APS 
			on APS.PCKID=CSD.PackingStyleID  where CSD.BarCodeID = '$BID' 
			AND (AvalMcs!=0 or LooseBags !=0)";
	
	$stmt_pid=$iconn->query($sql);
	

	
	if($DataCount=='0'){
			$Data['Error']='No Data Found';
			$Data['Msg']='No Data Found';
	}else{
		
		
	$row_csrd=$stmt_crd->fetch_array();
	
	

	 $data .='<thead>
		
		
		<tr>
		<th>ID</th>
		<th>Variety</th>
		<th>Grade</th>
		<th>Packing Type </th>
		<th>Packing Style</th>	
		<th>Freezing Type</th>
		<th>Category</th>
		<th>Shift</th>
		<th>Mcs</th>
		<th>Loose</th>
		</tr>
		</thead>
		
		<tbody>';
	 while($RowData=$stmt_pid->fetch_array()){

	
			$StockID = $RowData['StockID'];
			$Grade = $RowData['Grade'];
			$VarietyName = $RowData['ShortName'];		
			$PackingType = $RowData['PackingTypeID'];
			$AvalMcs = $RowData['AvalMcs'];
			$LooseBags = $RowData['LooseBags'];
			$PackingStyle = $RowData['PackingStyle'];
            $RackName=$RowData['RACK_ID'];
            $FrezingType=$RowData['FreezingType'];
            $Category=$RowData['ProductCategoryID'];
            $Shift=$RowData['ShiftID'];
	
	

			
			
				$data .='
				<tr>
		
	
		
		<td >'.$StockID.'</td>
		<td >'.$VarietyName.'</td>
		<td >'.$Grade.'</td>
		
		<td >'.$PackingType .'</td>
		<td >'.$PackingStyle .'</td>
		<td >'.$FrezingType.'</td>
		<td >'.$Category.'</td>
		<td >'.$Shift.'</td>
		<td>'.$AvalMcs.'</td>
		<td >'.$LooseBags.'</td>
	
			</tr>';
					  
		}
					
		echo $data .='</tbody>';
	}	
		
	}
	
?>