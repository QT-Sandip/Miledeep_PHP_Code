<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
//uma

$block=$_POST['block'];
$floor=$_POST['floor'];
$rackid=$_POST['rackid'];
$TYPE=$_POST['type'];

if( $TYPE =='repck' ){
      $query='';
	  if(isset($block) && $block!=''){
    $query.=" and CBD.BlockID='".$block."'";
	  }
	  if(isset($floor) && $floor!='' && $rackid ==''){
    $query.=" and FloorID='".$floor."'";
	  }
	  if(isset($rackid) && $rackid!=''){
    $query.=" and RackName like'%".$rackid."%'";
	  }
	 
	 $SqlData="SELECT cb.BarCodeID,BarCodeValue,coalesce(PoNumber,'NA') as PoNumber,RowID,CBD.BlockID,RackName,
			StockID,AvalMcs,BlockName,PackingStyle,CGO.Name as Grade,CGF.Name as FreezingType,
			CGV.Name as Variety,CGPT.Name as`ProductionType`, CGPC.Name as ProductCategory,
			CGU.Name as Unit FROM `CsBarCodes` as cb 
			left join `CsStoresRackData` as cr on cb.BarCodeID = cr.BarCodeID 
			left join CsStoresBlockData as CBD on CBD.BlockID=cr.BlockID
			left join `CsStockData` as CPID on cb.BarCodeID = CPID.BarCodeID
			left join CsPackingStyles as CSP on CSP.PckID = CPID.PackingStyleID			
			left join CsGeneralOptions as CGO on CGO.CsOptID = CPID.GradeID			
			left join CsGeneralOptions as CGF on CGF.CsOptID = CPID.FreezingTypeID			
			left join CsGeneralOptions as CGV on CGV.CsOptID = CPID.VarietyID			
			left join CsGeneralOptions as CGPT on CGPT.CsOptID = CPID.ProductionTypeID			
			left join CsGeneralOptions as CGPC on CGPC.CsOptID = CPID.ProductCategoryID			
			left join CsGeneralOptions as CGU on CGU.CsOptID = CPID.UnitID			
			 WHERE `Status`='1' and cb.BarCodeID!='0' ".$query."";
	// echo $SqlData; exit;
	 $StmtData=$iconn->query($SqlData);
	
$data='<!--<div id="table-scroll"> -->

<table id="key-datatable" 
										class="table dt-responsive nowrap w-100">
								<thead>
                 <tr>	  
        <th>ID</th>
        <th>PO Number</th>
		<th>Bar Code</th>
		<th>Unit</th>
		<th>Variety</th>
        <th>Grade</th>	
		<th>Mcs</th>
		<th>Packing</th> 
		<th>Freezing</th>
		<th>Group</th>		
		<th>Store</th>
		<th>Rack</th>	
		<th>Actions</th>
		
		</tr>               
                </thead>
               <!-- <tbody id >-->
                <tbody>
				';
				
$i='0';
				
  while($RowData = $StmtData->fetch_array()){
	$i++;
	
	 $BID =$RowData['BarCodeID'];
	 $BarCodeValue =$RowData['BarCodeValue'];
	 $Unit =$RowData['Unit'];
	 $RackName =$RowData['RackName'];
	 $BlockName =$RowData['BlockName'];
	 $PoNumber = $RowData['PoNumber'];
	 $FreezingType = $RowData['FreezingType'];
	$Grade = $RowData['Grade'];
	$Variety = $RowData['Variety'];
	$AvalMcs = $RowData['AvalMcs'];
	$PackingStyle = $RowData['PackingStyle'];
	$ProductionType = $RowData['ProductionType'];

	

		$data .='
		
		<tr id="tr'.$RowData['BarCodeID'].'">
		
		<td >'.$BID.'</td>
		<td >'. $PoNumber.'</td>
		<td >'. $BarCodeValue.'</td>
		<td >'. $Unit.'</td>
		
		<td >'.$Variety.'</td>
		<td >'.$Grade.'</td>
		<td >'.$AvalMcs.'</td>
		<td >'.$PackingStyle .'</td>
		<td >'.$FreezingType .'</td>
		<td >'.$ProductionType.'</td>
		<td >'.$BlockName.'</td>
		<td >'. $RackName .'</td>
		
	
		<td>  <Button type="text" class="btn btn-danger repck_bt" name="repck_bt" id="'.$RowData['BarCodeID'].'">Out</Button>
				</td>
			</tr>';
					  
	$PoNumber='';				  
	$Variety='';				  
	$Grade='';				  
	$AvalMcs='';				  
	$PackingStyle='';				  
	$FreezingType='';				  
	$BlockName='';				  
	$RackName='';				  
}
					
					echo $data .='</tbody></table>
					<!--</div>-->
					';


	
}
else if($TYPE =='repck_updt' ){
$BID=$_POST['bid'];
	$rckid=$_POST['rckid'];
	 $OUT_TYPE=$_POST['OUT_TYPE'];
	
	
	
	 $SqlBarCodes="SELECT * FROM `$CsBarCodes` WHERE `BarCodeID`='$BID'";
	 $StmtBarCodes=$iconn->query($SqlBarCodes);
	 $RowBarcodes = $StmtBarCodes->fetch_array();
     $BBarCodeID=$RowBarcodes['BarCodeID'];
	// $BID1 =$RowBarcodes['BarCodeValue'];
	 //echo $sql;
	// exit;
	 $oldBarCodeData = legacyAuditFetchRowById($CsBarCodes, 'BarCodeID', $BID, 'ColdStore');
	 $SqlBlockDataUpdate = "UPDATE  `$CsBarCodes` SET BarCodeStatus ='3' WHERE BarCodeID ='$BID'";
	 $StmtBlockDataUpdate = $iconn->query($SqlBlockDataUpdate);
	 if($StmtBlockDataUpdate){
		 legacyAuditLogUpdateById($CsBarCodes, 'BarCodeID', $BID, $oldBarCodeData, $_POST, ['Remarks' => 'Barcode status set to 3 (stock out) - repck_updt'], 'ColdStore');
	 }
	 
	 $oldRackData = legacyAuditFetchRowById($CsStoresRackData, 'BarCodeID', $BID, 'ColdStore');
	  $SqlRackDataUpdate = "UPDATE `$CsStoresRackData` SET Status ='0',BarCodeID='0' WHERE BarCodeID ='$BID'";
	 $StmtRackDataUpdate= $iconn->query($SqlRackDataUpdate);
	 if($StmtRackDataUpdate){
		 legacyAuditLogUpdateById($CsStoresRackData, 'BarCodeID', $BID, $oldRackData, $_POST, ['Remarks' => 'Rack data cleared - repck_updt'], 'ColdStore');
	 }
	 
	 $SqlStockOutHistory = "INSERT INTO `$CsStockOutHistory`( `BarCodeID`,`RowID`,`OutType`,`Status `) VALUES 
	 ('$BID','$BBarCodeID','$OUT_TYPE','3')";
	 
	 //echo $SqlStockOutHistory;
	 //exit;
$StmtStockOutHistory= $iconn->query($SqlStockOutHistory);
	 if($StmtStockOutHistory){
		 legacyAuditLogInsertById($CsStockOutHistory, 'ID', $iconn->insert_id, $_POST, ['Remarks' => 'Stock out history logged - repck_updt'], 'ColdStore');
	 }
	 
	 
	 if($SqlRackDataUpdate){
		 $row['sts']='success';
		$row['msg']='Data Updated Sucessfully';
		echo json_encode($row);
	 }else{
		$row['sts']='Failed';
		$row['msg']='Faild To Update Data';
		 echo json_encode($row);
	 }
	
		
		
	
}
?>	
