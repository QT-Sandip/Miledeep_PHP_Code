<?php
session_start();
include "../includes/config.php";


$D_CODE=$_POST['D_CODE'];
$REG_NO=$_POST['REG_NO'];
$INV_ID=$_POST['INV_ID'];

if($D_CODE!=''){

echo  $sql="SELECT * FROM `CODE_LIST_CREATE` WHERE `DAY_CODE`='$D_CODE' and INV_ID='$INV_ID'";
 

	$stmt=$iconn->query($sql);
	$ary_prod_codes=array();

	
	
?>	<option value="">Select Reg Number</option> <?php

				
					while($row=$stmt->fetch_array()){
	
					$FARM_REG_ID=$row['FARM_REG_ID'].'-'.$row['T_O_MCS'];
					 if(in_array($FARM_REG_ID, $ary_prod_codes)){
					
					}else{
						$ary_prod_codes[]=$FARM_REG_ID;?>	
						
					<option value="<?php echo $row["CODE_LIST_ID"]; ?>"><?php echo $row["FARM_REG_ID"].'-'.$row['T_O_MCS'].' MCS'; ?></option>
				<?php   }}


}else{
	 
	//ECHO $sql="SELECT * FROM `CODE_LIST_CREATE` WHERE `FARM_REG_ID`='$REG_NO' and INV_ID='$INV_ID'";
	echo $sql="SELECT * FROM `CODE_LIST_CREATE` WHERE `CODE_LIST_ID`='$REG_NO' and INV_ID='$INV_ID'";
	$stmt=$iconn->query($sql);
	$ary_prod_codes=array();
	?>	<option value="">Select Packing</option> <?php

	while($row=$stmt->fetch_array()){
	
					$FARM_REG_ID=$row['PACKING'];
					 if(in_array($FARM_REG_ID, $ary_prod_codes,true)){
					
					}else{
						$ary_prod_codes[]=$FARM_REG_ID;?>	
						
					<option value="<?php echo $row["PACKING"]; ?>"><?php echo $row["PACKING"]; ?></option>
				<?php   }}
}


?>