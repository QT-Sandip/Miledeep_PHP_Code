<?php 
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$Month=10;
$Year=2022;


	$SqlEmpList = "SELECT * FROM `HrmAuditEmployeeDetails`";
	$StmtEmpList = $iconn->query($SqlEmpList);
while($RowEmpList = $StmtEmpList->fetch_array()){
	$EmpID=$RowEmpList['EmpID'];
	
	$URL="https://serp.silverlights.in/Clients/Jlsf/Hrm/TimeCardAPrintPdf?Month=".$Month."&Year=".$Year."&EmpID=".$EmpID;
	
	//header("Location: TimeCardAPrintPdf?Month=".$Month."&Year=".$Year."&EmpID=".$EmpID);
	echo "<script>  Object.assign(document.createElement('a'), { target: '_blank', href: '{$URL}'}).click(); </script>" ;
}


?>