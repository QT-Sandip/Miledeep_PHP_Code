<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


//$TYPE=$_POST['TYPE'];
 $Type=$_POST['Type']; 
$D_ID=$_POST['D_ID'];
$REFERENCE_ID=$_POST['ref_id'];


if($Type=='MBResultDelete'){
	   $sql="DELETE  FROM `QcMBResult` WHERE `ID`='$D_ID'";   
	$stmt=$iconn->query($sql);
	
	if($stmt){
		legacyAuditLogDeleteById('QcMBResult', 'ID', $D_ID, null, $_POST, [
			'ReferenceNo' => $REFERENCE_ID,
			'Remarks' => 'MB result deleted',
		], 'QualityControl');

		$sqlBreakupCount=	"SELECT * FROM `QcMBResult` WHERE MbReportID='$REFERENCE_ID' ";
		$StmtBreakupCount=$iconn->query($sqlBreakupCount);
		$BreakupCount=mysqli_num_rows($StmtBreakupCount); 

		if($BreakupCount==0){
			$SqlMbReportDelete="DELETE FROM `QcMBReport` WHERE ID='$REFERENCE_ID'";
			$StmtMbReportDelete=$iconn->query($SqlMbReportDelete);
			if($StmtMbReportDelete){
				legacyAuditLogDeleteById('QcMBReport', 'ID', $REFERENCE_ID, null, $_POST, [
					'ReferenceNo' => $REFERENCE_ID,
					'Remarks' => 'MB report auto-deleted with last result',
				], 'QualityControl');
			}
		}
		
			$sql="SELECT * FROM `QcMBReport` where ID='$REFERENCE_ID'";
			$stmt=$iconn->query($sql);
			$post_details=$stmt->fetch_array();
		
			$sql="select * from QcMBResult where MbReportID='$REFERENCE_ID'  ";
	
			$stmt=$iconn->query($sql);
	

	 while($row=$stmt->fetch_array()){

	 $ClID=$row['CLID']; 
	 $SqlCode="SELECT * FROM QcCodeList as cd LEFT JOIN QcTracebilityList as tl on 
	 tl.TraceID=cd.TracebilityID LEFT JOIN $TblQcPoVarityList as pvl on pvl.VarietyID=cd.VarietyID WHERE CLID='$ClID'"; 

	$StmtCode=$iconn->query($SqlCode);
	if($StmtCode){
		$RowCode=$StmtCode->fetch_array();
}

	?>
		
		

		
	
	  <?php  if($post_details['PROD_REG']!='NA'){ ?>
		<td id="<?php  echo $row['ID'];?>" > <?php echo $RowCode['DayCode'].'&'.$RowCode['Tracebility']; ?></td>
		<?php }?>
		
		<?php  if($post_details['DOA_DOC']!='NA'){ ?>
		<td id="c_name<?php  echo $row['ID'];?>"><?php $DOA_DOC=$row['DOA_DOC']; echo $row['DateOfAnalysis'] .'/'.$row['DateOfIssuance']; ?></td>
		<?php }?>
		
		<?php  if($post_details['VARITY']!='NA'){ ?>
		<td id="a_type<?php  echo $row['ID'];?>"><?php echo $RowCode['ShortName']; ?></td>

		<?php }?>
		<?php  if($post_details['TPC']!='NA'){ ?>
		<td id="p_number<?php  echo $row['ID'];?>"><?php echo $row['Tpc']; ?></td>
		<?php }?>
		
		<td style="width:250px;">  	
	 	 <button id="<?php  echo $row['ID'];?>" onClick="reply_click(this.id)" class=" glyphicon glyphicon-remove btn btn-danger"> Delete</button>
	   </td>		
		
		
</tr>	   <?php
	   
	   }

	}
 }else if($Type=='CodeListDelete'){
	 
	 $CODE_LIST_ID=$_POST['D_ID'];
	 $INVOICE_ID=$_POST['INVOICE_ID'];

      $sql="DELETE  FROM `QcCodeList` WHERE `CLID`='$CODE_LIST_ID'";
	 $stmt=$iconn->query($sql);
	
	if($stmt){
		legacyAuditLogDeleteById('QcCodeList', 'CLID', $CODE_LIST_ID, null, $_POST, [
			'ReferenceNo' => $INVOICE_ID,
			'Remarks' => 'Code list deleted',
		], 'QualityControl');
		
$error = null;
					$Msg = 'Deleted Successfully';
				}else{
					$error =  $iconn->error;
				}
		
		
		
		
	
		$data['error']=$error;
		$data['Msg']=$Msg;
		
		echo json_encode($data);
		
}

else if($Type=='CodeListUpdate'){
	 
	 $CODE_LIST_ID=$_POST['D_ID'];
	 $INVOICE_ID=$_POST['ref_id'];
	 
	 $DayCode=$_POST['DayCode'];
	 $PackingStyleID=$_POST['PackingStyleID'];
	 $GlazePerc=$_POST['GlazePerc'];
	 $VarietyID=$_POST['VarietyID'];
	 $Trace=$_POST['Trace'];
	 $Brand=$_POST['Brand'];
	 $Grades=$_POST['Grades'];
	 $McsGrade=$_POST['McsGrade'];
	 $McsTotal=$_POST['McsTotal'];
	 $Yeild=$_POST['Yeild'];




     $sql="UPDATE `QcCodeList` SET 
	 `DayCode`='$DayCode',
	
	 `PackingStyleID`='$PackingStyleID',
	 `GlazePerc`='$GlazePerc',
	 `VarietyID`='$VarietyID',
	 
	 `Brand`='$Brand',
	 `Grades`='$Grades',
	 `Yeild`='$Yeild',
	 `McsGrade`='$McsGrade',
	 `McsTotal`='$McsTotal',
	 `TracebilityID`='$Trace'
	 WHERE `CLID`='$CODE_LIST_ID'";
	 $stmt=$iconn->query($sql);
	
	if($stmt){
		legacyAuditLogUpdateById('QcCodeList', 'CLID', $CODE_LIST_ID, null, $_POST, [
			'ReferenceNo' => $CODE_LIST_ID,
			'Remarks' => 'Code list updated',
		], 'QualityControl');
		$error = null;
					$Msg = 'Updated Successfully';
				}else{
					$error =  $iconn->error;
				}
		
		
		
		
	
		$data['error']=$error;
		$data['Msg']=$Msg;
		
		echo json_encode($data);
		
}
	
/*****************************************************************po edit */


else if($Type=='DeleteVariety'){
	 
	 $VarietyID=$_POST['ClickedId'];
	 $PoID=$_POST['PoID'];

      $sql="DELETE  FROM `$TblQcPoVarityList` WHERE `VarietyID`='$VarietyID'";
	  $stmt=$iconn->query($sql);
	
	if($stmt){
		legacyAuditLogDeleteById($TblQcPoVarityList, 'VarietyID', $VarietyID, null, $_POST, [
			'ReferenceNo' => $PoID,
			'Remarks' => 'QC PO variety deleted',
		], 'QualityControl');

	$error = null;
					$Msg = 'Deleted Successfully';
				}else{
					$error =  $iconn->error;
				}
		
		
		
		
	
		$data['error']=$error;
		$data['Msg']=$Msg;
		
		echo json_encode($data);
		
	
	
	
	
	}
	
	
else if($Type=='MpedaRegDelete'){
	 
	 $VARIETY_ID=$_POST['D_ID'];
	 $RmPurID=$_POST['RmPurID'];

      $sql="DELETE  FROM `QcRawMaterialPurchaseReg` WHERE `RmPurID`='$RmPurID'";
	  $stmt=$iconn->query($sql);
	
	if($stmt){
		legacyAuditLogDeleteById('QcRawMaterialPurchaseReg', 'RmPurID', $RmPurID, null, $_POST, [
			'ReferenceNo' => $VARIETY_ID,
			'Remarks' => 'Raw material purchase registration deleted',
		], 'QualityControl');
		
$error = null;
					$Msg = 'Deleted Successfully';
				}else{
					$error =  $iconn->error;
				}
		
		
		
		
	
		$data['error']=$error;
		$data['Msg']=$Msg;
		
		echo json_encode($data);
		
	
	
	}
	
	
	else if($Type=='VarietyUpdate'){
	 
	 $VarietyID=$_POST['D_ID'];
	 $INVOICE_ID=$_POST['ref_id'];
	 
	 $ShortName=$_POST['ShortName'];
	 $FullName=$_POST['FullName'];
	 $TypeProduct=$_POST['TypeProduct'];
	 $YieldID=$_POST['YieldID'];
	 $Description=$_POST['Description'];
	
	

     $sql="UPDATE `$TblQcPoVarityList` SET 
	 `TypeProduct`='$TypeProduct',
	 `ShortName`='$ShortName',
	 `FullName`='$FullName',
	 `YieldID`='$YieldID'
	 WHERE `VarietyID`='$VarietyID'"; 
	 
	 
	 $sqlupdate="UPDATE `ScrmPoGradeData` SET 
	 `QcDescription`='$Description'  WHERE `ID `='$VarietyID'";
	 $stmt=$iconn->query($sqlupdate);
	
	
	if($stmt){
		legacyAuditLogUpdateById('ScrmPoGradeData', 'ID', $VarietyID, null, $_POST, [
			'ReferenceNo' => $VarietyID,
			'Remarks' => 'QC description updated in PO grade data',
		], 'QualityControl');
		
	  
	  $sql="UPDATE `QcCodeList` SET 
	 
	  WHERE `VarietyID`='$VarietyID' and InvID='$INVOICE_ID'";
	 $stmt=$iconn->query($sql);
		
		
$error = null;
					$Msg = 'Updated Successfully';
				}else{
					$error =  $iconn->error;
				}
		
		$data['error']=$error;
		$data['Msg']=$Msg;
		
		echo json_encode($data);
		
	
	
	
	
	}
	
	ELSE IF($Type=='AddTraceability'){
	     $Tracebility=$_POST['Tracebility'];
	     $FarmerName=$_POST['FarmerName'];
	     $AddressContinued=$_POST['AddressContinued'];
	     $RegAuthority=$_POST['RegAuthority'];
		 //$InvId=implode(',', $SelectedbulkInvId);

    
		 $SqlPI="INSERT INTO QcTracebilityList
	 (`FarmerName`, `AddressContinued`, `Tracebility`, `RegAuthority`) VALUES 
		('$FarmerName','$AddressContinued','$Tracebility','$RegAuthority')"; 
   
  	 $StmtPI=$iconn->query($SqlPI);
	 if($StmtPI){
		$insertedId = $iconn->insert_id;
		legacyAuditLogInsertById('QcTracebilityList', 'ID', $insertedId, null, $_POST, [
			'ReferenceNo' => $insertedId,
			'Remarks' => 'Traceability list inserted',
		], 'QualityControl');
		$row['sts']='success';
		$row['msg']='Inserted Sucessfully';
		echo json_encode($row);
	 }
	 
	 else{
		$row['sts']='Failed';
		$row['msg']='Failed';
		 echo json_encode($row);
	 }
	
}

?>
