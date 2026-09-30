<?php
session_start();


include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$lid=$_POST['lid'];
$type=$_POST['type'];
$qtype=$_POST['qtype'];

if($qtype !=''){
	if($lid=='Pre Harvest'){
	 $sql="select * from inputmaterial_details where ANTI_BIOTIC_STATUS='0' and SAMPLE_TYPE='$lid' ORDER BY `id` DESC LIMIT 500";
	}else{
	 $sql="select * from inputmaterial_details where ANTI_BIOTIC_STATUS='0' and SAMPLE_TYPE !='Pre Harvest' ORDER BY `id` DESC LIMIT 500";
	
	}

					$stmt=$iconn->query($sql);

					while($row=$stmt->fetch_array()){?>
						
						<option value="<?php echo $row["id"]; ?>"><?php echo $row["COUNT"].' C--> '.$row["LOT_NO"]; ?></option>
				<?php   }
				  
}
if($type=='Pre Harvest'){
	$SAMPLE_TYPE='PRE HARVEST';
}else{
	$SAMPLE_TYPE='';
}

   $sql_count="SELECT `LotNumber`,`PurchaseCount`,`PurchaseWeight` FROM `RmLotDetatils` WHERE `RmLotID`='$lid'";
 //exit;
 

$stmt_ip=$iconn->query($sql_count);
$row=$stmt_ip->fetch_assoc();
$rsult=array();
$rsult[]=$row;
echo json_encode($row);




?>