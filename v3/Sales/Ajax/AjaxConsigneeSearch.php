<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
 
 /*$term = $_REQUEST['term'];
  $sql = "SELECT DISTINCT(NAME_ADD_OF_CONSIGNEE) FROM INVOICE_DATA WHERE  NAME_ADD_OF_CONSIGNEE LIKE '" . $term . "%'";
	$stmt=$iconn->query($sql);

	while($row=$stmt->fetch_array()){
		$str=$row["NAME_ADD_OF_CONSIGNEE"];
	$NAME=explode('/',$str);
echo '<p id ='.$str.'>'.$str.'</p>


';*/
$term = $_REQUEST['term'];
  $sql = "SELECT CONSIGNEE_NAME,`CONSIGNEE_ADDRESS`,`CONSIGNEE_ID` FROM SCRM_CONSIGNEE_DETAILS WHERE  CONSIGNEE_NAME LIKE '" . $term . "%'";
	//echo $sql;
	$stmt=$iconn->query($sql);

	while($row=$stmt->fetch_array()){
		$str=$row["CONSIGNEE_NAME"];
	$NAME=explode('/',$str);
echo '<p id ='.$row['CONSIGNEE_ID'].'>'.$str.'</p>
<input type="hidden" id="add'.$row['CONSIGNEE_ID'].'" value='.$row["CONSIGNEE_ADDRESS"].'>


';
		
}
 

?>