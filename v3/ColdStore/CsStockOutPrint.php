<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


$block=$_GET['block'];
$floor=$_GET['floor'];
$rackid=$_GET['rackid'];
$TYPE=$_GET['type'];

if( $TYPE =='repck' ){
      $query='';
	  if(isset($block) && $block!=''){
    $query.=" and BLOCK_ID='".$block."'";
	  }
	  if(isset($floor) && $floor!='' && $rackid ==''){
    $query.=" and FLOOR_ID='".$floor."'";
	  }
	  if(isset($rackid) && $rackid!=''){
    $query.=" and RACK_NAME like'%".$rackid."%'";
	  }
	 
	/* $sql="SELECT cb.BAR_CODE_ID,BAR_CODE_VALUE,coalesce(PO_NUM,'NA') as PO_NUM,ROW_ID,BLOCK_ID,RACK_NAME,
			PALLEDT_ID,CARTONS_NUMBER,BLOCK_NAME,DUMMY_MASTER,PACKING_STYLE,GRADES,TYPE,
			FORM,DATE_TIME, INT_LOT_NUMBER,`GROUP`, CATEGORY,
			JME_MOURYA FROM `CS_BAR_CODE_DATA` as cb 
			left join `CS_RACK_DATA` as cr on cb.BAR_CODE_ID = cr.BAR_CODE_ID 
			left join CS_BLOCK_DATA_TABLE as CBD on CBD.ID=cr.BLOCK_ID
			left join `CS_PALLET_INPUT_DATA` as CPID on cb.BAR_CODE_ID = CPID.BAR_CODE_ID
			left join CS_POS as CPOS on CPOS.ID  = CPID.INT_LOT_NUMBER 
			left join CS_PACKING_STYLES as CSP on CSP.ID = CPID.PACKING_STYLE_ID			
			 WHERE `STATUS`='1' and cb.BAR_CODE_ID!='0' ".$query."";*/
			 
			 
			 $sql="SELECT cb.BarCodeID,BarCodeValue,coalesce(PoNumber,'NA') as PoNumber,RowID,cr.BlockID,RackName,
			StockID,AvalMcs,BlockName,PackingTypeID,PackingStyle,GradeID,FreezingTypeID,
			VarietyID,ProductionDate, RmLotID,`ProductionTypeID`, ProductCategoryID,
			UnitID FROM `CsBarCodes` as cb 
			left join `CsStoresRackData` as cr on cb.BarCodeID = cr.BarCodeID 
			left join CsStoresBlockData as CBD on CBD.BlockID=cr.BlockID
			left join `CsStockData` as CSD on cb.BarCodeID = CSD.BarCodeID
			left join CsPoData as CPOS on CPOS.PoID  = CSD.RmLotID 
			left join CsPackingStyles as CSP on CSP.PckID = CSD.PackingStyleID			
			 WHERE `Status`='1' and cb.BarCodeID!='0' ".$query."";
	// echo $sql; exit;
	 $stmt=$iconn->query($sql);
	
//$data='<div id="table-scroll"> <table id="example1" class="table table-bordered ">
$w1="9";

$data='

 <table id="example1"  border="1" cellspacing="0" cellpadding="5" >

				<thead>
                 <tr>	  
        <th width="">ID</th>
        <th width="'.$w1.'%">PO Number</th>
		<th width="'.$w1.'%">Bar Code</th>
		<th width="'.$w1.'%">Unit</th>
		<th width="'.$w1.'%">Variety</th>
        <th width="'.$w1.'%">Grade</th>	
		<th width="6%">Mcs</th>
		<th width="'.$w1.'%">Packing</th> 
		<th width="'.$w1.'%">Freezing</th>
		<th width="'.$w1.'%">Group</th>		
		<th width="'.$w1.'%">Store</th>
		<th width="'.$w1.'%">Rack</th>	
		
		</tr>               
                </thead>
                <tbody id >';
				
$i='0';
				
  while($RowData = $stmt->fetch_array()){
	$i++;
	
	 $BID =$RowData['BAR_CODE_ID'];
	 $BarCodeValue =$RowData['BAR_CODE_VALUE'];
	 $Unit =$RowData['JME_MOURYA'];
	 $RACK_NAME =$RowData['RACK_NAME'];
	 $BLOCK_NAME =$RowData['BLOCK_NAME'];
	 $PO_NUM = $RowData['PO_NUM'];
	 $FREEZING = $RowData['TYPE'];
	$GRADE = $RowData['GRADES'];
	$VARITY = $RowData['FORM'];
	$CARTONS_NUMBER = $RowData['CARTONS_NUMBER'];
	$PACKING = $RowData['PACKING_STYLE'];
	$GROUP = $RowData['GROUP'];

	

		$data .='
		
		<tr id="tr'.$RowData['BAR_CODE_ID'].'">
		
		<td >'.$BID.'</td>
		<td >'. $PO_NUM.'</td>
		<td >'. $BarCodeValue.'</td>
		<td >'. $Unit.'</td>
		
		<td >'.$VARITY.'</td>
		<td >'.$GRADE.'</td>
		<td >'.$CARTONS_NUMBER.'</td>
		<td >'.$PACKING .'</td>
		<td >'.$FREEZING .'</td>
		<td >'.$GROUP.'</td>
		<td >'.$BLOCK_NAME.'</td>
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
					
					echo $data .='</tbody></table>';

return $data;

	
}
else if($TYPE =='repck_updt' ){
$BID=$_POST['bid'];
	$rckid=$_POST['rckid'];
	 $OUT_TYPE=$_POST['OUT_TYPE'];
	
	
	
	 $sql="SELECT * FROM `CS_BAR_CODE_DATA` WHERE `BAR_CODE_ID`='$BID'";
	 $stmt=$iconn->query($sql);
	 $row1 = $stmt->fetch_array();
     $row_id=$row1['BAR_CODE_ID'];
	 $BID1 =$row1['BAR_CODE_VALUE'];
	 //echo $sql;
	// exit;
	 $sql_BCD_update = "UPDATE  `CS_BAR_CODE_DATA` SET BAR_CODE_STATUS ='3' WHERE BAR_CODE_ID ='$BID'";
	 $stmt_BCD_update = $iconn->query($sql_BCD_update);
	 
	  $sql_RD_update = "UPDATE `CS_RACK_DATA` SET STATUS ='0',BAR_CODE_ID='0' WHERE BAR_CODE_ID ='$BID'";
	 $stmt_RD_update = $iconn->query($sql_RD_update);
	 
	 $sql_RD_thistry = "INSERT INTO `CS_TRANSACTION_HISTORY`( `BAR_CODE_ID`, `ROW_ID`,`OUT_TYPE`, `STATUS`) VALUES 
	 ('$BID','$row_id','$OUT_TYPE','3')";
	 
	 //echo $sql_RD_thistry;
	 //exit;
$stmt_RD_histry = $iconn->query($sql_RD_thistry);
	 
	 
	 if($sql_RD_update){
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