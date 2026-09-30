<?php
session_start();
include "../includes/config.php";


$TYPE=$_POST['TYPE'];
$D_ID=$_POST['D_ID'];
$REFERENCE_ID=$_POST['ref_id'];


if($TYPE=='COMBINED_MB_DELETE'){
    $oldRow = legacyAuditFetchRowById('BUYER_MB_RESULT', 'ID', $D_ID, 'QualityControl');
    $sql="DELETE  FROM `BUYER_MB_RESULT` WHERE `ID`='$D_ID'";
	$stmt=$iconn->query($sql);
	
	if($stmt){
		legacyAuditLogDeleteById('BUYER_MB_RESULT', 'ID', $D_ID, $oldRow, $_POST, ['Remarks' => 'Combined MB result deleted'], 'QualityControl');
		
			$sql="SELECT * FROM `BUYER_MB_REPORT` where ID='$REFERENCE_ID'";
			$stmt=$iconn->query($sql);
			$post_details=$stmt->fetch_array();
		
			$sql="select * from BUYER_MB_RESULT where MB_REPORT_ID='$REFERENCE_ID'  ";
	
			$stmt=$iconn->query($sql);
	

	 while($row=$stmt->fetch_array()){

		if($post_details['PROD_REG']!='NA'){ ?>
		
		<tr>
		
		<td id="<?php  echo $row['ID'];?>" > <?php echo $row['P_CODES_REG_NUM']; ?></td>
		
		<?php 
		}
		?>
		
		<?php  
		
		if($post_details['DOA_DOC']!='NA'){ ?>
		<td id="c_name<?php  echo $row['C_ID'];?>"><?php $DOA_DOC=$row['DOA_DOC']; echo $row['DOA_DOC']; ?></td>
		
		<?php }?>
		
		<?php  if($post_details['VARITY']!='NA'){ ?>
		<td id="a_type<?php  echo $row['C_ID'];?>"><?php echo $row['VARITY']; ?></td>

		<?php }?>
		<?php  if($post_details['TPC']!='NA'){ ?>
		<td id="p_number<?php  echo $row['C_ID'];?>"><?php echo $row['TPC']; ?></td>
		<?php }?>
		
		<td style="width:250px;">  	
	 	 <button id="<?php  echo $row['ID'];?>" onClick="reply_click(this.id)" class=" glyphicon glyphicon-remove btn btn-danger"> Delete</button>
	   </td>		

</tr>	   <?php
	   
	   }

	}
 }else if($TYPE=='CODE_LIST_DELETE'){
	 
	 $CODE_LIST_ID=$_POST['D_ID'];
	 $INVOICE_ID=$_POST['INVOICE_ID'];

	 $oldRow = legacyAuditFetchRowById('CODE_LIST_CREATE', 'CODE_LIST_ID', $CODE_LIST_ID, 'QualityControl');
      $sql="DELETE  FROM `CODE_LIST_CREATE` WHERE `CODE_LIST_ID`='$CODE_LIST_ID'";
	 $stmt=$iconn->query($sql);
	
	if($stmt){
		legacyAuditLogDeleteById('CODE_LIST_CREATE', 'CODE_LIST_ID', $CODE_LIST_ID, $oldRow, $_POST, ['Remarks' => 'Code list deleted'], 'QualityControl');
		
$SQL_CODE_LIST="SELECT * FROM `CODE_LIST_CREATE` where INV_ID='$INVOICE_ID'";
$STMT_CODE_LIST=$iconn->query($SQL_CODE_LIST);
	

 while($row=$STMT_CODE_LIST->fetch_array()){

	

	?>
	<tr>
	
	<?php $CODE_LIST_ID= $row['CODE_LIST_ID']; ?>
	
	<td > <input  type="text" class="form-control"  name="DAY_CODE" id="DAY_CODE<?php echo $CODE_LIST_ID; ?>"
				  placeholder="Enter DAY CODE" value="<?php echo $row['DAY_CODE']; ?>"> </td>
				  
	<td > <input  type="text" class="form-control"  name="SHORT_NAME" id="SHORT_NAME<?php echo $CODE_LIST_ID; ?>"
				  placeholder="Enter DAY CODE" value="<?php echo $row['SHORT_NAME']; ?>"> </td>

	<td > <input  type="text" class="form-control"  name="PACKING" id="PACKING<?php echo $CODE_LIST_ID; ?>"
				  placeholder="Enter DAY CODE" value="<?php echo $row['PACKING']; ?>"> </td>

	<td > <input  type="text" class="form-control"  name="GLAZE_PERC" id="GLAZE_PERC<?php echo $CODE_LIST_ID; ?>"
				  placeholder="Enter DAY CODE" value="<?php echo $row['GLAZE_PERC']; ?>"> </td>
	
	<td > <input  type="text" class="form-control"  name="FARM_REG_ID" id="FARM_REG_ID<?php echo $CODE_LIST_ID; ?>"
				  placeholder="Enter DAY CODE" value="<?php echo $row['FARM_REG_ID']; ?>"> </td>

	<td > <input  type="text" class="form-control"  name="BRAND" id="BRAND<?php echo $CODE_LIST_ID; ?>"
				  placeholder="Enter DAY CODE" value="<?php echo $row['BRAND']; ?>"> </td>

	<td > <input  type="text" class="form-control"  name="GRADES" id="GRADES<?php echo $CODE_LIST_ID; ?>"
				  placeholder="Enter DAY CODE" value="<?php echo $row['GRADES']; ?>"> </td>	

	<td > <input  type="text" class="form-control"  name="T_O_MCS" id="T_O_MCS<?php echo $CODE_LIST_ID; ?>"
				  placeholder="Enter DAY CODE" value="<?php echo $row['T_O_MCS']; ?>"> </td>				  
		
	<td style="width:250px;">  	
	 	 <button id="<?php  echo $row['CODE_LIST_ID'];?>" onClick="reply_click(this.id)" class=" glyphicon glyphicon-remove btn btn-danger"> Delete</button>
	 	 <button id="<?php  echo $row['CODE_LIST_ID'];?>" onClick="update_click(this.id)" class=" glyphicon glyphicon-edit btn btn-primary">  Update</button>
	 </td>	
        

        </tr>

	  <?php 

	}}}else if($TYPE=='CODE_LIST_UPDATE'){
		
	 
	 $CODE_LIST_ID=$_POST['D_ID'];
	 $INVOICE_ID=$_POST['ref_id'];
	 

	 $FARM_REG_ID=$_POST['FARM_REG_ID'];
	 $NAME_OF_FARMER=$_POST['NAME_OF_FARMER'];
	 $ADD_OF_FARMER=$_POST['ADD_OF_FARMER'];
	

     $oldRow = legacyAuditFetchRowById($TblQcCodeList, 'CLID', $CODE_LIST_ID, 'QualityControl');
     $sql="UPDATE `$TblQcCodeList` SET 
	 `TracebilityID`='$FARM_REG_ID'
	
	
	 WHERE `CLID`='$CODE_LIST_ID'";
	 $stmt=$iconn->query($sql);
	
	if($stmt){
		$newRow = legacyAuditFetchRowById($TblQcCodeList, 'CLID', $CODE_LIST_ID, 'QualityControl');
		legacyAuditLogUpdateById($TblQcCodeList, 'CLID', $CODE_LIST_ID, $oldRow, $_POST, ['Remarks' => 'Code list traceability updated'], 'QualityControl');
		
$SQL_CODE_LIST="SELECT * FROM `$TblQcCodeList` where InvID='$INVOICE_ID'";

$STMT_CODE_LIST=$iconn->query($SQL_CODE_LIST);
	

 while($row=$STMT_CODE_LIST->fetch_array()){

	

	?>
	<tr>
	
	<?php $CODE_LIST_ID= $row['CLID']; ?>
	
	
 <td>
			<select class="form-control" name="FARM_REG_ID" id="FARM_REG_ID<?php echo $CODE_LIST_ID; ?>">				  
				  <?php 
				   $T_ID=$row['TracebilityID']; 
				 $SQL_trace="SELECT * FROM `$TblQcTracebilityList`";
                    $STMT_trace=$iconn->query($SQL_trace);
					$row_trace=$STMT_trace->fetch_array();
					$FarmerName=$row_trace['FarmerName'];
					$Address=$row_trace['AddressContinued'];
					
					$STMT_trace=$iconn->query($SQL_trace);

		
		while($row_trace=$STMT_trace->fetch_array()){?>
					<option value="<?php echo $row_trace['TraceID']?>"  
					<?php if($row_trace['TraceID']==$T_ID) echo 'selected="selected"'; ?>><?php echo $row_trace['Tracebility']?></option>
				  <?php } ?>
			<select>				  
				  
	</td>

	
				
				<td style="width:200px;" > <input  type="text" class="form-control"  name="NAME_OF_FARMER" 
	id="NAME_OF_FARMER<?php echo $CODE_LIST_ID; ?>"
				  placeholder="Enter Farmer Name" value="<?php echo $FarmerName; ?>"> </td>

	<td > <input  type="text" class="form-control"  name="ADD_OF_FARMER" id="ADD_OF_FARMER<?php echo $CODE_LIST_ID; ?>"
				  placeholder="Enter Farmer Address" value="<?php echo $Address; ?>"> </td>
	
	

        </tr>

	  <?php 

	}}}
	
/*****************************************************************po edit */


else if($TYPE=='PO_DELETE'){
	 
	 $VARIETY_ID=$_POST['D_ID'];
	 $INVOICE_ID=$_POST['INVOICE_ID'];

	 $oldRow = legacyAuditFetchRowById('PO_VARIETY_LIST', 'ID', $VARIETY_ID, 'QualityControl');
      $sql="DELETE  FROM `PO_VARIETY_LIST` WHERE `ID`='$VARIETY_ID'";
	  $stmt=$iconn->query($sql);
	
	if($stmt){
		legacyAuditLogDeleteById('PO_VARIETY_LIST', 'ID', $VARIETY_ID, $oldRow, $_POST, ['Remarks' => 'PO variety deleted'], 'QualityControl');
		
$SQL_CODE_LIST="SELECT * FROM `PO_VARIETY_LIST` where INV_ID='$INVOICE_ID'";
$STMT_PO_LIST=$iconn->query($SQL_CODE_LIST);
	

	while($row=$STMT_PO_LIST->fetch_array()){ ?>
	
	<tr>
	
	<?php $CODE_LIST_ID= $row['CODE_LIST_ID']; ?>
	

				  
				  <select class="form-control" name="TYPE" id="TYPE">
					<option value="Raw" 
					<?php if($row['TYPE']=='Raw') echo 'selected="selected"'; ?>>Raw</option>
					<option value="COOKED"
					<?php if($row['TYPE']=='COOKED') echo 'selected="selected"'; ?>>COOKED</option>
					</option>		
				<select>
				  </td>
				  
	<td width="15%" > <input  type="text" class="form-control"  name="SHORT_NAME" id="SHORT_NAME"
				  placeholder="Enter SHORT NAME " value="<?php echo $row['SHORT_NAME']; ?>"> </td>

	<td > <input  type="text" class="form-control"  name="PACKING" id="FULL_NAME"
				  placeholder="Enter FULL NAME" value="<?php echo $row['FULL_NAME']; ?>"> </td>

				  
		
 <td style="width:250px;">  	
	 	 <button id="<?php  echo $row['ID'];?>" onClick="reply_click(this.id)" class=" glyphicon glyphicon-remove btn btn-danger"> Delete</button>
	 	 <button id="<?php  echo $row['ID'];?>" onClick="update_click(this.id)" class=" glyphicon glyphicon-edit btn btn-primary">  Update</button>
	 </td>	
        

        </tr>

	<?php }}
	
	
	}else if($TYPE=='PO_UPDATE'){
	 
	 $VARIETY_ID=$_POST['D_ID'];
	 $INVOICE_ID=$_POST['ref_id'];
	 
	 $SHORT_NAME=$_POST['SHORT_NAME'];
	 $FULL_NAME=$_POST['FULL_NAME'];
	 $VARITY_TYPE=$_POST['VARITY_TYPE'];
	
	 $oldRow = legacyAuditFetchRowById('PO_VARIETY_LIST', 'ID', $VARIETY_ID, 'QualityControl');

     $sql="UPDATE `PO_VARIETY_LIST` SET 
	 `TYPE`='$VARITY_TYPE',
	 `SHORT_NAME`='$SHORT_NAME',
	 `FULL_NAME`='$FULL_NAME'
	 WHERE `ID`='$VARIETY_ID'";
	 $stmt=$iconn->query($sql);
	
	 if($stmt){
		$newRow = legacyAuditFetchRowById('PO_VARIETY_LIST', 'ID', $VARIETY_ID, 'QualityControl');
		legacyAuditLogUpdateById('PO_VARIETY_LIST', 'ID', $VARIETY_ID, $oldRow, $_POST, ['Remarks' => 'PO variety updated'], 'QualityControl');
	 }
	
	if($stmt){
		
	 $sql="UPDATE `CODE_LIST_CREATE` SET 
	 `SHORT_NAME`='$VARITY_TYPE'
	  WHERE `FULL_NAME`='$VARIETY_ID' and INV_ID='$INVOICE_ID'";
	 $stmt=$iconn->query($sql);
		
		
$SQL_CODE_LIST="SELECT * FROM `PO_VARIETY_LIST` where INV_ID='$INVOICE_ID'";
$STMT_PO_LIST=$iconn->query($SQL_CODE_LIST);
	

	while($row=$STMT_PO_LIST->fetch_array()){ ?>
	
	<tr>
	
	<?php $CODE_LIST_ID= $row['ID']; ?>
	
	<td width="10%" > <select class="form-control" name="TYPE" id="TYPE">
					<option value="Raw" 
					<?php if($row['TYPE']=='Raw') echo 'selected="selected"'; ?>>Raw</option>
					<option value="COOKED"
					<?php if($row['TYPE']=='COOKED') echo 'selected="selected"'; ?>>COOKED</option>
					</option>		
				<select></td>
				  
	<td width="15%" > <input  type="text" class="form-control"  name="SHORT_NAME" id="SHORT_NAME<?php echo $CODE_LIST_ID ?>"
				  placeholder="Enter SHORT NAME " value="<?php echo $row['SHORT_NAME']; ?>"> </td>

	<td > <input  type="text" class="form-control"  name="PACKING" id="FULL_NAME<?php echo $CODE_LIST_ID ?>"
				  placeholder="Enter FULL NAME" value="<?php echo $row['FULL_NAME']; ?>"> </td>

				  
		
 <td style="width:250px;">  	
	 	 <button id="<?php  echo $row['ID'];?>" onClick="reply_click(this.id)" class=" glyphicon glyphicon-remove btn btn-danger"> Delete</button>
	 	 <button id="<?php  echo $row['ID'];?>" onClick="update_click(this.id)" class=" glyphicon glyphicon-edit btn btn-primary">  Update</button>
	 </td>	
        

        </tr>

	<?php }}}

?>
