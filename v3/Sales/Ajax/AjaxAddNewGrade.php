<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

$PoID=$_POST['PoID'];

if($PoID!=''){
	
 $sql_org_insert="INSERT INTO `ScrmPoGradeData`(`Grade`, `Mcs`, `FreezingType`, `InvId`, `PackingStyleID`,
	 `GlazeType`, `Variety`, `GlazePerc`, `Treatment`, `Brand`, `Price`,`BAP`,`VarietyID`,`BuyerItemCode`,`Description`,`CodeType`,`DdpPrice`) VALUES (
	 '$GRADE','$MCS','$FreezingType','$PoID','$PackingStyleID','$Glaze','Click Change','$GlazePerc',
	 '$Treatment','$BRAND','$Price','$BAP','$VarietyId','$BuyerItemCode','$Description','$CodeType','$DdpPrice')"; 
$stmt1=$iconn->query($sql_org_insert);
$lastInsertedID = mysqli_insert_id($iconn);

if($stmt1){
	legacyAuditLogInsertById('ScrmPoGradeData', 'ID', $lastInsertedID, $_POST, ['PoID' => $PoID, 'Remarks' => 'PO grade data created'], 'Sales');

	$SqlItems = "SELECT ID as PoGradeID,Mcs  FROM `ScrmPoGradeData` WHERE InvId='$PoID' order by ID asc";
	$StmtItems = $iconn->query($SqlItems);
		while ($RowItem = $StmtItems->fetch_array()) {
			$PoGradeID=$RowItem['PoGradeID'];
			if($RowItem['Mcs'] > 0){
				$Mcs=$RowItem['Mcs'];
			}else{
				$Mcs=0;	
			}
			
			postUpdateGrossweight($iconn, $PoID, $PoGradeID, '', $Mcs);
		}
	
$Data['Error']=null;
$Data['Msg']='Added';
$Data['lastInsertedID']=$lastInsertedID;
// $Data['EditMode']='T';
 echo json_encode($Data);
/*
echo '<tr data-rowid="'.$Data['lastInsertedID'].'" id="rowid'.$Data['lastInsertedID'].'" role="row">
		<td id="PoGradeId'.$Data['lastInsertedID'].'">'.$Data['lastInsertedID'].'</td>
		<td>
			<p class="fullname Variety" id="Variety'.$Data['lastInsertedID'].'">Click Change</p>
			<input type="hidden" id="VarietyId'.$Data['lastInsertedID'].'" name="VarietyId'.$Data['lastInsertedID'].'" class="form-control hidden" value="0">
		</td>
		<td>
			<span id="tdGrade'.$Data['lastInsertedID'].'"></span> - 
			<span id="tdPackingStyle'.$Data['lastInsertedID'].'"></span> - 
			<span id="tdBrand'.$Data['lastInsertedID'].'"></span>		
			<textarea name="Description'.$Data['lastInsertedID'].'" class="form-control" rows="2" cols="50" placeholder="Enter Description(Optional)" id="Description'.$Data['lastInsertedID'].'" ></textarea>
		</td>
		<td>
			<input name="code" type="text" class="form-control" id="code'.$Data['lastInsertedID'].'" value="">
		</td>
		<td>
			<input required="" type="number" max="999999" min="1" class="form-control mcs" value="" name="MCS'.$Data['lastInsertedID'].'" id="MCS'.$Data['lastInsertedID'].'" placeholder="Enter MASTER CARTONS" > 
		</td>
		<td>
			<input required="" type="number" step="0.001" class="form-control price" name="Price'.$Data['lastInsertedID'].'" id="Price'.$Data['lastInsertedID'].'" value="" placeholder="Enter Rate/Unit(USD) " >
		</td>
		<td>0.00</td>
		<td>
			<button class="btn btn-link text-danger Delete" data-id="'.$Data['lastInsertedID'].'" data-toggle="tooltip" data-placement="top" title="" value=""  data-original-title="Delete" style="padding:2px;">
			<i class="mdi mdi-trash-can font-size-18"></i></button>
		</td>
	</tr>';
	
	*/

}
}else{
$Data['Error']='Unable Add Item';
$Data['Msg']='';
echo "Error";
// echo json_encode($Data);	
}

?>
