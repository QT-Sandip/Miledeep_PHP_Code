<?php
	include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
		$Type=$_POST['Type'];
		$PackingStyle=$_POST['PackData'];
		
if($PackingStyle!='MASTER'){ 
 $sql=
 "SELECT DISTINCT(AvalCPID.VarietyID),ShortName FROM `CsStockData` AS AvalCPID 
  left join CsBarCodes as AvalCbd on AvalCbd.BarCodeID=AvalCPID.BarCodeID
  left join ScrmPIVarietyList as SPV on SPV.VarietyID=AvalCPID.BarCodeID
  WHERE  PackingTypeID='DUMMY' AND (AvalCbd.BarCodeStatus =1 or AvalCbd.BarCodeStatus=2) ";
		}else{
 $sql="SELECT ShortName,SPV.VarietyID from CsStockData as AvalCPID
left join CsBarCodes as AvalCbd on AvalCbd.BarCodeID=AvalCPID.BarCodeID 
  left join ScrmPIVarietyList as SPV on SPV.VarietyID=AvalCPID.BarCodeID
WHERE (AvalCbd.BarCodeStatus =1 or AvalCbd.BarCodeStatus=2)
and PoGradeId !=0 GROUP by ShortName ";
		}
//ECHO $sql;  exit;
$stmt=$iconn->query($sql);
$ary_prod_codes=array();

?>	
<option value="BOTH">All </option> <?php

while($row=$stmt->fetch_array()){

	IF($PackingStyle!='MASTER'){?>	
						
<option value="<?php echo $row["VarietyID"]; ?>"><?php echo
 $row["ShortName"]; ?></option>
				
				<?php  

		}ELSE{		?>	
										
<option value="<?php echo $row["VarietyID"]; ?>"><?php echo $row["ShortName"]; ?></option>
				
				<?php  

		}

}
?>