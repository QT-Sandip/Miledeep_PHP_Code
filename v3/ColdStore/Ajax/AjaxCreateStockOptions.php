<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


 $Type=$_POST['Type']; 
 $PoId=$_POST['PoId']; 




if($Type=='FORM'){
if($PoId!=''){	


 $sql="SELECT ShortName ,FullName,PGD.Grade as GradeName,ID as PoGradeID,MCS as RequiredCases,PGD.FreezingType,
PackingStyleID,PGD.VarietyID,PGD.GlazePerc,PGD.Treatment as SockingType,Brand,CPS.PackingStyle FROM `ScrmPoGradeData` as PGD 
left Join AdminPackingStyles as CPS on PGD.PackingStyleID=CPS.PckID 
left Join ScrmPIVarietyList as VL on VL.VarietyID=PGD.VarietyID 
WHERE InvId=$PoId";
}else{
//$sql="SELECT Name as Variety,OptID  FROM `AdminGeneralOptions` WHERE DeptID='CS' and OptionType='PRODUCT_VARIETY'";
//$sql="SELECT * FROM `ScrmPIVarietyList` ORDER by ABS(ShortName)";
$sql="SELECT * FROM `ScrmProductList` where BrandName = 1  ORDER by ABS(ShortName)";
  $sql="SELECT ShortName ,FullName,VL.Grade as GradeName,VL.FreezingType, VL.PackingStyle as  PackingStyleID,VL.VarietyID,VL.GlazePerc,VL.Treatment 
  as SockingType,VL.BrandName as Brand,
 CPS.PackingStyle FROM ScrmPIVarietyList as VL left Join AdminPackingStyles as CPS on VL.PackingStyle=CPS.PckID WHERE VL.BrandName='101'";
}
//echo $sql; exit;
$stmt=$iconn->query($sql);
$PoGradeId=$_POST['PoGradeId']; 
 
?>
				<option value="">Select Variety</option> <?php
					while($row=$stmt->fetch_array()){
					if($PoId==''){?>
					<option value="<?php echo $row["VarietyID"] ; ?>">
					<?php  echo $row["ShortName"] .' - '.$row["GradeName"].' - '.$row["PackingStyle"].' - '.$row["Brand"];?>
					</option>
					<?php }else { ?>				
					<option <?php if($row['PoGradeID']==$PoGradeId){ ?> selected="selected" <?php }?> 
					value="<?php echo $row["PoGradeID"]  ?>">
					<?php echo $row["ShortName"] .' - '.$row["GradeName"].' - '.$row["PackingStyle"]; ?>
					</option>
					
					<?php   } }
}
else if($Type=='GRADES'){
 $Variety=$_POST['Variety']; 
 $BreakUpID=$_POST['BreakUpID']; 

 if($BreakUpID!='0'){
	 $sql="SELECT VarietyID FROM `ScrmPoGradeData` WHERE ID=$BreakUpID";
	$stmt=$iconn->query($sql);
	$Data=$stmt->fetch_array();
	$Variety=$Data['VarietyID'];
}
 $sql="SELECT Grade as GradeName,
FreezingType as FreezingTypeID,
PackingStyle as PackingStyleID,
VarietyID,
GlazePerc,Treatment as SockingType,PackingStyle

FROM `ScrmPIVarietyList` WHERE VarietyID='$Variety'";





$stmt=$iconn->query($sql);
$Data=$stmt->fetch_array();
$Data['PoGradeID']=$BreakUpID;
echo json_encode($Data,true);


}



else if($Type=='PackStyle'){
$Variety=$_POST['Variety'];
$Gardes=$_POST['GRADES'];
if($Variety!=''){
 $sql="SELECT CS_PACKING_STYLES.ID,PACKING_STYLE FROM `PO_GRADE_DATA` 
left join CS_PACKING_STYLES on 
PO_GRADE_DATA.PACKING_STYLE_ID=CS_PACKING_STYLES.ID  
WHERE INV_ID='$PoId' and VARITY_ID='$Variety' and GRADE='$Gardes'";
}else{
$sql="SELECT VALUE as PACKING_STYLE FROM `CS_PACKING_STYLES` ";
}
//echo $sql;exit;
$stmt=$iconn->query($sql); 
	
}else if($Type=='Freezing'){
$Variety=$_POST['Variety'];
$Gardes=$_POST['GRADES'];
$PackStyle=$_POST['PackStyle'];
if($Variety!=''){
 $sql="SELECT FREEZING_TYPE FROM `PO_GRADE_DATA` 
WHERE INV_ID='$PoId' and VARITY_ID='$Variety' and GRADE='$Gardes' and 
PACKING_STYLE_ID='$PackStyle'";
}else{
$sql="SELECT VALUE as PACKING_STYLE FROM `CS_PACKING_STYLES` ";
}

	
}
?>	