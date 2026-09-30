<?php
session_start();
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


//$TYPE=$_POST['TYPE'];
 $Type=$_POST['Type']; 
$D_ID=$_POST['D_ID'];
$REFERENCE_ID=$_POST['ref_id'];


if($Type=='MBResultDelete'){
    $sql="DELETE  FROM `$TblQcMBResult` WHERE `ID`='$D_ID'"; 
	$stmt=$iconn->query($sql);
	
	if($stmt){
		
			$sql="SELECT * FROM `$TblQcMBReport` where ID='$REFERENCE_ID'";
			$stmt=$iconn->query($sql);
			$post_details=$stmt->fetch_array();
		
			$sql="select * from $TblQcMBResult where MbReportID='$REFERENCE_ID'  ";
	
			$stmt=$iconn->query($sql);
	

	 while($row=$stmt->fetch_array()){

	 $ClID=$row['CLID']; 
	 $SqlCode="SELECT * FROM $TblQcCodeList as cd LEFT JOIN $TblQcTracebilityList as tl on 
	 tl.TraceID=cd.TracebilityID LEFT JOIN $TblQcPoVarityList as pvl on pvl.VarietyID=cd.VarietyID WHERE CLID='$ClID'"; 
	$StmtCode=$iconn->query($SqlCode);
	$RowCode=$StmtCode->fetch_array();

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

      $sql="DELETE  FROM `$TblQcCodeList` WHERE `CLID`='$CODE_LIST_ID'";
	 $stmt=$iconn->query($sql);
	
	if($stmt){
		
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
	



     $sql="UPDATE `$TblQcCodeList` SET 
	 `DayCode`='$DayCode',
	
	 `PackingStyleID`='$PackingStyleID',
	 `GlazePerc`='$GlazePerc',
	 `VarietyID`='$VarietyID',
	 
	 `Brand`='$Brand',
	 `Grades`='$Grades',
	 `McsGrade`='$McsGrade',
	 `McsTotal`='$McsTotal',
	 `TracebilityID`='$Trace'
	 WHERE `CLID`='$CODE_LIST_ID'";
	 $stmt=$iconn->query($sql);
	
	if($stmt){
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
	
	

     $sql="UPDATE `$TblQcPoVarityList` SET 
	 `TypeProduct`='$TypeProduct',
	 `ShortName`='$ShortName',
	 `FullName`='$FullName',
	 `YieldID`='$YieldID'
	 WHERE `VarietyID`='$VarietyID'"; 
	 $stmt=$iconn->query($sql);
	
	
	if($stmt){
		
	/* $sql="UPDATE `$TblQcCodeList` SET 
	 `SHORT_NAME`='$VARITY_TYPE'
	  WHERE `FULL_NAME`='$VARIETY_ID' and INV_ID='$INVOICE_ID'";
	 */ 
	  
	  $sql="UPDATE `$TblQcCodeList` SET 
	 
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

?>