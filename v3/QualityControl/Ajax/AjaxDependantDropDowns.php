<?php

 
 include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
 //echo 'hiii';
 

$Type=$_POST['Type'];
$VarietyID=$_POST['VarietyID'];
$McsGrade=$_POST['McsGrade'];
$PoBreakupID=$_POST['PoBreakupID'];
$Grades=$_POST['Grades'];
$PoId=$_POST['PoId'];
 
 


if($Type=='Variety'){
			if($VarietyID!=''){	
$sql="SELECT * FROM `ScrmPIVarietyList` WHERE VarietyID='$VarietyID'";

			}
else{
$sql="SELECT DISTINCT VarietyID FROM `ScrmPoGradeData` WHERE InvID='$PoID'";
		
}
$stmt=$iconn->query($sql);
$Data=$stmt->fetch_array();
echo json_encode($Data);

}

if($Type=='CodeListAutoFillValues'){
				
 if($VarietyID!=''){
  $sql="SELECT *,SPD.Grade AS Grade FROM `ScrmPoGradeData` as SPD 
left join ScrmPIVarietyList As SPV on SPD.VarietyID=SPV.VarietyID
left join AdminPackingStyles as APS on SPD.PackingStyleID=APS.PCKID
 WHERE SPD.InvID=$PoId  and SPD.ID='$VarietyID'"; }
 
 else{
$sql="SELECT *,
  SPV.ShortName as PoShortname FROM `ScrmPoGradeData` as SPD
  
left join ScrmPIVarietyList As SPV on SPD.VarietyID=SPV.VarietyID
left join AdminPackingStyles as APS on SPD.PackingStyleID=APS.PCKID
WHERE  SPD.InvId='$PoId'";
}
$stmt=$iconn->query($sql);
$Data=$stmt->fetch_array();
echo json_encode($Data,true);


}

if($Type=='McsGradeValue'){
			

      $SqlMcs="SELECT Mcs,VarietyID FROM `ScrmPoGradeData` WHERE ID='$PoBreakupID' "; 
   
  	 $StmtMcs=$iconn->query($SqlMcs);
  	 $RowMcs=$StmtMcs->fetch_array();
	 $BreakupMcs=$RowMcs['Mcs'];
	 $VarietyID=$RowMcs['VarietyID'];
	 
	 $SqlClMcs=" SELECT sum(McsGrade) as ClMcs FROM `QcCodeList` as Cl left join QcPoVarietyList as QPL on QPL.VarietyID=Cl.VarietyID WHERE Cl.InvID=$PoId and YieldID='$VarietyID' and Grades='$Grades'";
	 $StmtClMcs=$iconn->query($SqlClMcs);
	 $RowClMcs=$StmtClMcs->fetch_array();
	 $McsGrade=$McsGrade+$RowClMcs['ClMcs'];
	 
	 if($McsGrade>$BreakupMcs){
		
		 $row['sts']='failed';
		$row['msg']='Mcs value exceed,please enter correct mcs';
		echo json_encode($row);
	 }

}
?>



 