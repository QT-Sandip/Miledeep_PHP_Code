<?php
$blockid=$_POST['id'];
$rck_nme=$_POST['rck_nme'];

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


$sql_serch = "SELECT * FROM `CsStoresRackData` WHERE `BlockID`='$blockid' and Path!='0' ORDER BY abs(RackName) ASC";
$query_serch = $iconn->query($sql_serch);
?>
					<option value="">Select Rack</option> <?php
					while($row=$query_serch->fetch_array()){?>
					<option value="<?php echo $row["RowID"] ; ?>">
					<?php  echo $row["RackName"]; ?>
					</option>
					<?php  }

?>		