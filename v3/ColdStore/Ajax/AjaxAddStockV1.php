<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


$block=$_POST['block'];
$floor=$_POST['floor'];
$rackid=$_POST['rackid'];
$TYPE=$_POST['type'];
$MONTH=$_POST['MONTH'];
$YEAR=$_POST['YEAR'];
$ColdstoreID=$_POST['ColdstoreID']; 
 $TenantID=$_POST['TenantID']; 

	if( $TYPE =='repck' ){
      $query='';
	  
	  if( $block!=''){
		$query.=" and cr.BlockID='".$block."'";
	  }
	  
	  //if(isset($floor)){
	  if($floor!='' && $rackid ==''){
		$query.=" and cr.FloorID='".$floor."'";
	  }else if($floor!='' && $rackid !=''){
		$query=" and cr.FloorID='".$floor."'and cr.RowID='".$rackid."'";
	  }
	// }
	  
	  if($rackid!=''){
		$query.=" and cr.RowID='".$rackid."'";
	  }
	  if($TenantID!=''){
$query .=" and CPID.StockHolderID='$TenantID'";
}
	  $sql="SELECT cb.BarCodeID,
		BlockName,SockingType,
		coalesce(PoNo,'NA') as PoNo,
		RowID,cr.BlockID,RackName,ShortName,CPID.FreezingType,CPID.Grade,CPID.AbStatus,
	 PackingTypeID,ProductionTypeID,ProductionID,
	 StockID,AvalMcs,LooseBags,PackingTypeID,CSP.PackingStyle,
	 ProductionDate, CPID.PoID,cb.BarCodeValue,LotNo,
	 ProductCategoryID,CPID.UnitID FROM `CsStoresRackData` as cr 
			left join `CsBarCodes` as cb   on cb.BarCodeID = cr.BarCodeID
            left join `CsStockData` as CPID on cb.BarCodeID = CPID.BarCodeID			
			left join `AdminGeneralOptions` as CGOPG on CGOPG.OptID = CPID.ProductionTypeID 
			left join `CsStoresBlockData` as CBD on CBD.BlockID = cr.BlockID 		
			left join `ScrmPoData` as CPOS on CPOS.PoID  = CPID.PoID 
            left join `ScrmPIVarietyList` as VL on VL.VarietyID=CPID.VarietyID
			left join `AdminPackingStyles` as CSP on CSP.PckID = CPID.PackingStyleID
            where  CPID.PlantID ='$ColdstoreID' 
			and (cb.BarCodeStatus='1' OR cb.BarCodeStatus='2') 
			and (CPID.AvalMcs!=0 or CPID.LooseBags!=0)
			and cb.OnFloor=1 ".$query." order by abs(RackName) asc"; 
	 
	 $stmt=$iconn->query($sql);
	

$data='<div id="table-scroll"> 


<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">
                 <tr>
				 
        <th>S.No</th>
		<th>Rack</th>	
        <th>Prod Date</th>
		<th>Po No</th>
		
		<th>Variety</th>
        <th>Grade</th>	
		<th>MCS</th>
		<th>Loose</th>
		<th>Packing Style</th> 
		<th >Category</th>
		<th >Soaking</th>
		<th >AB Status</th>
		<th>Freezing</th>
		<th>Group</th>		
		<th>Packing Type</th>
		
		<th>Actions</th>
		</tr>               
        </thead>
        <tbody id >';
				
$Sno='1';
				
  while($row1 = $stmt->fetch_array()){
	$i++;
	
	if($row1['RackName'] == ''){
		 $RACK_NAME ='NA';
	}else{
		 $RACK_NAME =$row1['RackName'];
		
	}
		
	
	$BarCodeValue =$row1['BarCodeValue'];	
	$BLOCK_NAME =$row1['BlockName'];	
	$PO_NUM     = $row1['PoNo'];	
	$LotNo     = '-'.$row1['LotNo'] ?: '';	
	$FREEZING   = $row1['FreezingType'];
	$GRADE = $row1['Grade'];
	$VARITY = $row1['ShortName'];
	$PACKING = $row1['PackingStyle'];	
	$CARTONS_NUMBER = $row1['AvalMcs'];	
	$SockingType = $row1['SockingType'];	
	$StockID = $row1['BarCodeID'];
	$DUMMY_MASTER=$row1['PackingTypeID'];
    $ProductionDate = $row1['ProductionDate'];
	$GROUP=$row1['ProductionTypeID'];
	$LooseBags=$row1['LooseBags'];
	$AbStatus=$row1['AbStatus'];
	$ProductCategoryID=$row1['ProductCategoryID'];
	
	$ProductionDate = date("d-m-Y", strtotime($ProductionDate));


		$data .='
		
		<tr id="tr'.$row1['BarCodeID'].'">
		
		<td >'.$Sno++.'</td>
		<td >'. $RACK_NAME .'</td>
		<td >'.$ProductionDate.'</td> 
		<td >'.$PO_NUM .$LotNo .'</td>
		
		<td >'.$VARITY.'</td>	
		<td >'.$GRADE.'</td>
		<td >'.$CARTONS_NUMBER.'</td>
		<td >'.$LooseBags.'</td>
		<td >'.$PACKING .'</td>
		<td >'.$ProductCategoryID .'</td>
		<td >'.$SockingType .'</td>
		<td >'.$AbStatus .'</td>
		<td >'.$FREEZING .'</td>
		<td >'.$GROUP.'</td>
		<td >'.$DUMMY_MASTER.'</td>
	
		
	
		<td> 
		<!--<a target="_blank" href="CreateStock?TYPE=0&BID='.$row1['BarCodeID'].'" 
						 class="mr-3 text-primary"  data-placement="top"
						 title="Add Stock" data-original-title="Edit"
						 ><i class="mdi mdi-plus font-size-16"></i></a> -->
		
		<a target="_blank" href="inward?ProductionDataID='.$row1['ProductionID'].'
					&BarCodeID='.$row1['BarCodeID'].'" 
						 class="mr-3 text-primary"  data-placement="top"
						 title="Edit Stock" data-original-title="Edit"
						 ><i class="mdi mdi-eye font-size-16"></i></a>
		
		<a target="_blank" href="CsBarCodeLabelPrint.php?BID='.$row1['BarCodeID'].'"	
		class="mr-3 text-primary"  data-placement="top"
						 title="Print Label" data-original-title="Print"
						 ><i class="mdi mdi-printer font-size-16"></i></a>
						
						
<!--<Button type="text" class="btn btn-danger repck_bt" name="repck_bt" id="'.$row1['BarCodeID'].'">Out</Button>
-->
				<button  class="btn btn-danger"
					 data-toggle="popover" data-trigger="focus" 
					 
					 data-id="'.$row1['BarCodeID'].'" id="unplace" 
					 value="'.$row1['BarCodeID'].'">UnPlace
							 </button>
							 
							


							 <button  class="btn btn-danger"
					 data-toggle="popover" data-trigger="focus" 
					 
					 data-id="'.$row1['StockID'].'" id="repck_bt" 
							 value="'.$row1['StockID'].'">Out
							 </button>							
		
		
		<!-- <div class="btn-group">
                  <button type="button" class="btn btn-info">Options</button>
                  <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown">
                    <span class="caret"></span>
                    <span class="sr-only">Toggle Dropdown</span>
                  </button>
                  <ul class="dropdown-menu" role="menu">
				  <li>
					<a target="_blank" 
					 href="CsPalletInputData.php?TYPE=&BID='.$row1['BarCodeID'].'" >Add Stock</a>
				  </li>
					
                    <li>
					<a target="_blank" href="CsStockEdit.php?id='.$row1['StockID'].'
					&TYPE=&BID='.$row1['BarCodeID'].'" >Edit Stock </a>
					</li>
					
                    
                  
				  
				 
				<li>
					<a target="_blank" href="CsBarCodePrint.php?BID='.$row1['BarCodeID'].'" >Print Label</a>
				</li>
					
					<li>
				  <button 
					value="'.$row1['BarCodeID'].'"id="unplace">UnPlace</button>
				  </li> 
				 
					
               
			
                  </ul>
                </div>-->


				
				
				</td>
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
		 legacyAuditLogUpdateById('CsStoresRackData', 'BarCodeID', $BID, $oldRackData, $_POST, ['Remarks' => 'Rack data cleared for stock out - repck_updt'], 'ColdStore');
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
		$row['msg']=' Unplaced Successfully';
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
