<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Util.php");


$UnitID=$_POST['UnitID'];
$PoID=$_POST['PoID'];
$VarietyID=$_POST['VarietyID'];
$GradeID=$_POST['GradeID'];
$VarietyID=$_POST['VarietyID'];
$PackingStyleID=$_POST['PackingStyleID'];
$PackingTypeID=$_POST['PackingTypeID'];
$ProductCategoryID=$_POST['ProductCategoryID'];
$AbStatus=$_POST['AbStatus'];
$TYPE=$_POST['Type'];
$ColdstoreID=$_POST['ColdstoreID']; 
 $TenantID=$_POST['TenantID']; 


	if( $TYPE =='1' ){
      $query='';
	  
	  
	  if($PoID !=''){
		$query.="and CPID.PoID='$PoID'";
	  }
	  
	  if($VarietyID!='' ){
		$query.="and CPID.VarietyID='$VarietyID'";
	  }
	  
	  if($GradeID!='' ){
		$query.="and CPID.Grade='$GradeID'";
	  }
	  
	  if($PackingStyleID !=''){
		$query.="and CPID.PackingStyleID='$PackingStyleID'";
	  }
	  if($PackingTypeID!='' ){
		$query.="and CPID.PackingTypeID='$PackingTypeID'";
	  }
	  if($ProductCategoryID!=''){
		$query.="and CPID.ProductCategoryID='$ProductCategoryID'";
	  }
	  
	  if($TenantID!=''){
     $query .=" and CPID.StockHolderID='$TenantID'";
     }
if($ColdstoreID!=''){
			$query.=" and CPID.UnitID ='$ColdstoreID'";	
			}
	  
	  
	 $sql="SELECT cb.BarCodeID,
BlockName,
coalesce(PoNo,'NA') as PoNo,
RowID,cr.BlockID,BarCodeValue,RackName,ShortName,CPID.FreezingType,CPID.Grade,
	 PackingTypeID,
	 AvalMcs,PackingTypeID,CSP.PackingStyle,
	 ProductionDate,ProductionTypeID,
	 ProductCategoryID,CPID.UnitID FROM `CsBarCodes` as cb 
			left join `CsStoresRackData` as cr on cb.BarCodeID = cr.BarCodeID
            left join `CsStockData` as CPID on cb.BarCodeID = CPID.BarCodeID			
			
			left join `CsStoresBlockData` as CBD on CBD.BlockID = cr.BlockID 		
			left join `ScrmPoData` as CPOS on CPOS.PoID  = CPID.PoID 
            left join `ScrmPIVarietyList` as VL on VL.VarietyID=CPID.VarietyID
			left join `AdminPackingStyles` as CSP on CSP.PckID = CPID.PackingStyleID
            where cb.BarCodeStatus=1 and (CPID.AvalMcs!=0 or CPID.LooseBags !=0) 
			 ".$query.""; 
			 
			 //and cb.OnFloor=1

// echo  $sql;
	 
	 $stmt=$iconn->query($sql);
	

$data='
<div id="table-scroll"> 
<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">
                 <tr>
		<th>Po No</th>
		<th>BarCode</th>
		<th>Variety</th>
        <th>Grade</th>	
		<th>Mcs</th>
		<th>Packing Style</th>
		<th>Packing Type</th>
		<th>Freezing</th>
		<th>Group</th>
		<th>Category</th>
		<th>Prod Date</th>
		<th>Rack Number</th>
		</tr>               
        </thead>
        <tbody id >';
				
$i='0';
				
  while($row1 = $stmt->fetch_array()){
	$i++;
	
	if($row1['RackName'] == ''){
		 $RACK_NAME ='NA';
	}else{
		 $RACK_NAME =$row1['RackName'];
		
	}
		
	
	$BLOCK_NAME =$row1['BlockName'];	
	$PO_NUM     = $row1['PoNo'];	
	$FREEZING   = $row1['FreezingType'];
	$GRADE = $row1['Grade'];
	$VARITY = $row1['ShortName'];
	$PACKING = $row1['PackingStyle'];	
	$CARTONS_NUMBER = $row1['AvalMcs'];	
	$StockID = $row1['StockID'];
	$DUMMY_MASTER=$row1['PackingTypeID'];
    $ProductionDate = $row1['ProductionDate'];
	$ProductionTypeID=$row1['ProductionTypeID'];
	$BarCode=$row1['BarCodeValue'];
	$ProductCategoryID=$row1['ProductCategoryID'];
	
	 $ProductionDate =getFormatedDate($ProductionDate);


		$data .='
		
		<tr id="tr'.$row1['BarCodeID'].'">
		
		<td >'.$PO_NUM.'</td>
		<td >'.$BarCode.'</td>
		<td >'.$VARITY.'</td>	
		<td >'.$GRADE.'</td>
		<td >'.$CARTONS_NUMBER.'</td>
		<td >'.$PACKING .'</td>
		<td >'.$DUMMY_MASTER.'</td>
		<td >'.$FREEZING .'</td>
		<td >'.$ProductionTypeID.'</td>
		<td >'.$ProductCategoryID.'</td>
		<td >'.$ProductionDate.'</td>
		
		<td >'. $RACK_NAME .'</td>
		
	
			</tr>';
					  
	$PO_NUM='';				  
	$VARITY='';				  
	$GRADE='';				  
	$CARTONS_NUMBER='';				  
	$PACKING='';				  
	$FREEZING='';				  
	$BLOCK_NAME='';				  
	$RACK_NAME='';				  
}
					
					echo $data .='</tbody></table></div>';


	
}
else if($TYPE =='repck_updt' ){
$BID=$_POST['bid'];
	$rckid=$_POST['rckid'];
	 $OUT_TYPE=$_POST['OUT_TYPE'];
	
	
	
	 $sql="SELECT * FROM `CsBarCodes` WHERE `BarCodeID`='$BID'";
	 $stmt=$iconn->query($sql);
	 $row1 = $stmt->fetch_array();
     $row_id=$row1['BarCodeID'];
	 //echo $sql;
	// exit;
	 $oldBarCodeData = legacyAuditFetchRowById('CsBarCodes', 'BarCodeID', $BID, 'ColdStore');
	 $sql_BCD_update = "UPDATE  `CsBarCodes` SET BarCodeStatus ='3' WHERE BarCodeID ='$BID'";
	 $stmt_BCD_update = $iconn->query($sql_BCD_update);
	 if($stmt_BCD_update){
		 legacyAuditLogUpdateById('CsBarCodes', 'BarCodeID', $BID, $oldBarCodeData, $_POST, ['Remarks' => 'Barcode status set to 3 (stock out) - repck_updt'], 'ColdStore');
	 }
	 
	 $oldRackData = legacyAuditFetchRowById('CsStoresRackData', 'BarCodeID', $BID, 'ColdStore');
	  $sql_RD_update = "UPDATE `CsStoresRackData` SET STATUS ='0',BarCodeID='0' WHERE BarCodeID ='$BID'";
	 $stmt_RD_update = $iconn->query($sql_RD_update);
	 if($stmt_RD_update){
		 legacyAuditLogUpdateById('CsStoresRackData', 'BarCodeID', $BID, $oldRackData, $_POST, ['Remarks' => 'Rack data cleared - repck_updt'], 'ColdStore');
	 }
	 
	// $sql_RD_thistry = "INSERT INTO `CS_TRANSACTION_HISTORY`( `BarCodeID`, `ROW_ID`,`OUT_TYPE`, `STATUS`) VALUES 
	// ('$BID','$row_id','$OUT_TYPE','3')";
	 
	 //echo $sql_RD_thistry;
	 //exit;
// $stmt_RD_histry = $iconn->query($sql_RD_thistry);
	 
	 
	 if($sql_RD_update){
		 $row['sts']='success';
		$row['msg']='Data Updated Sucessfully';
		echo json_encode($row);
	 }else{
		$row['sts']='Failed';
		$row['msg']='Faild To Update Data';
		 echo json_encode($row);
	 }
	
		
		
	
}else if($TYPE=='PALLET_UNPLACE'){
	$BID=$_POST['BID'];
	
	$oldBarCodeData = legacyAuditFetchRowById('CsBarCodes', 'BarCodeID', $BID, 'ColdStore');
	$sql_BCD_update = "UPDATE  `CsBarCodes` SET OnFloor ='0' WHERE BarCodeID ='$BID'";
	$stmt_BCD_update = $iconn->query($sql_BCD_update);
	if($stmt_BCD_update){
		legacyAuditLogUpdateById('CsBarCodes', 'BarCodeID', $BID, $oldBarCodeData, $_POST, ['Remarks' => 'Set OnFloor=0 - PALLET_UNPLACE'], 'ColdStore');
	$oldRackData = legacyAuditFetchRowById('CsStoresRackData', 'BarCodeID', $BID, 'ColdStore');
	$sql_RD_update = "UPDATE `CsStoresRackData` SET STATUS ='0',BarCodeID='0' WHERE BarCodeID ='$BID'";
	 $stmt_RD_update = $iconn->query($sql_RD_update);
	 if($stmt_RD_update){
		 legacyAuditLogUpdateById('CsStoresRackData', 'BarCodeID', $BID, $oldRackData, $_POST, ['Remarks' => 'Rack data cleared - PALLET_UNPLACE'], 'ColdStore');
	 }
	}
	
	 if($sql_RD_update){
		 $row['sts']='success';
		$row['msg']='Data Updated Sucessfully';
		echo json_encode($row);
	 }else{
		$row['sts']='Failed';
		$row['msg']='Faild To Update Data';
		 echo json_encode($row);
	 }
}else if($TYPE=='PALLET_DELETE'){
	$PID=$_POST['PID'];
	
	$oldStockData = legacyAuditFetchRowById('CsStockData', 'StockID', $PID, 'ColdStore');
	$sql_BCD_update = "DELETE FROM CsStockData WHERE StockID ='$PID'";
	$stmt_BCD_update = $iconn->query($sql_BCD_update);

	
	 if($stmt_BCD_update){
		 legacyAuditLogDeleteById('CsStockData', 'StockID', $PID, $oldStockData, $_POST, ['Remarks' => 'Stock data deleted - PALLET_DELETE'], 'ColdStore');
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
