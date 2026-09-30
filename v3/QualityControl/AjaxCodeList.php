<?php
session_start();
include "../includes/config.php";


$D_CODE=$_POST['D_CODE'];
$REG_NO=$_POST['REG_NO'];
$INV_ID=$_POST['INV_ID'];

if($D_CODE!=''){

  $sql="SELECT * FROM `CODE_LIST_CREATE` WHERE `DAY_CODE`='$D_CODE' and INV_ID='$INV_ID'";
 

	$stmt=$iconn->query($sql);
	$ary_prod_codes=array();

	
	
?>	<option value="">Select Reg Number</option> <?php

				
					while($row=$stmt->fetch_array()){
	
					$FARM_REG_ID=$row['FARM_REG_ID'];
					 if(in_array($FARM_REG_ID, $ary_prod_codes)){
					
					}else{
						$ary_prod_codes[]=$FARM_REG_ID;?>	
						
					<option value="<?php echo $row["FARM_REG_ID"]; ?>"><?php echo $row["FARM_REG_ID"]; ?></option>
				<?php   }}


}else{
	 
	 $sql="SELECT * FROM `CODE_LIST_CREATE` WHERE `FARM_REG_ID`='$REG_NO' and INV_ID='$INV_ID'";
	$stmt=$iconn->query($sql);
	$ary_prod_codes=array();
	?>	<option value="">Select Packing</option> <?php

	while($row=$stmt->fetch_array()){
	
					$FARM_REG_ID=$row['PACKING'];
					 if(in_array($FARM_REG_ID, $ary_prod_codes)){
					
					}else{
						$ary_prod_codes[]=$FARM_REG_ID;?>	
						
					<option value="<?php echo $row["PACKING"]; ?>"><?php echo $row["PACKING"]; ?></option>
				<?php   }}
}


?>