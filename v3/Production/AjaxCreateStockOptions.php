<?php
session_start();
include "../includes/config.php";


$Type=$_POST['Type'];
$PoId=$_POST['PoId'];




if($Type=='GRADES'){
//if($PoId!=''){	
/*$sql="SELECT PGD.ID,GRADE,MCS,FREEZING_TYPE,PACKING_STYLE_ID,VARITY_ID,GLAZE_PERC,SOCKING,BRAND,SALT,PACKING_STYLE FROM `PO_GRADE_DATA` as PGD 
left Join CS_PACKING_STYLES as CPS on PGD.PACKING_STYLE_ID=CPS.ID WHERE INV_ID=$PoId";*/

$sql="SELECT Grade,FreezingType,PackingStyleID,Variety,Brand,PackingStyle FROM `ScrmPoGradeData` as SPG 
LEFT JOIN AdminPackingStyles as APS on SPG.PackingStyleID=APS.PCKID WHERE InvId=$PoId";
/*}else{
$sql="SELECT VALUE as Grade FROM `OPTIONS` WHERE DEPT='CS' and TYPE='GRADES'";
}*/
//echo $sql; exit;

$stmt=$iconn->query($sql);
//$PoGradeId=$_POST['PoGradeId'];

?>
				<option value="">Select Grade</option> <?php
					while($row=$stmt->fetch_array()){
						 ?>				
					
					<option  value="<?php echo $row["Grade"];?>">
					<?php echo $row["Grade"].' - '.$row["PackingStyle"]; ?>
					</option>
					
					<?php    }
}
else if($Type=='Packing'){
$Grades=$_POST['Grades'];
$PoId=$_POST['PoId'];
if($Grades!=''){
/*$sql="SELECT PGD.ID,GRADE,MCS,FREEZING_TYPE,PACKING_STYLE_ID,VARITY_ID,GLAZE_PERC,SOCKING,BRAND,SALT,PACKING_STYLE FROM `PO_GRADE_DATA` 
as PGD left Join CS_PACKING_STYLES as CPS on PGD.PACKING_STYLE_ID=CPS.ID where
 PGD.ID='$Variety'";*/
 $sql="SELECT Grade,FreezingType,PackingStyleID,Variety,Brand,PackingStyle FROM `ScrmPoGradeData` as SPG 
LEFT JOIN AdminPackingStyles as APS on SPG.PackingStyleID=APS.PCKID WHERE InvId=$PoId and Grade='$Grades'";
 
}else{
/*$sql="SELECT VALUE as GRADE FROM `OPTIONS` WHERE DEPT='CS'   
and TYPE='GRADES' order by SortOrder";*/
}
//echo $sql;exit;
$stmt=$iconn->query($sql);


$Data=$stmt->fetch_array();
echo json_encode($Data);


}/*else if($Type=='PackStyle'){
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

	
}*/
?>
