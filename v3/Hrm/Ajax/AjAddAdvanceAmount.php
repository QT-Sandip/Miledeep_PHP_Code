<?php
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");



$AdvDate=$_POST['AdvDate'];
$AdvAmount=$_POST['AdvAmount'];
$Installments=$_POST['Installments'];
$Remarks=$_POST['Remarks'];
$EmpID=$_POST['EmpID'];
$Type=$_POST['Type'];
$CAdvID=$_POST['AdvDedId'];
$DeductionAmount=$_POST['DeductionAmount'];

$year = date('Y');

$firstDate = date('Y-m-d', strtotime("$year-$AdvDate-01"));

IF($Type=='Add'){


	// Check existing record
	$CheckSql = "SELECT CAdvID FROM HrmCashAdvance
	             WHERE AdvDate = '$firstDate'
	             AND EmpID = '$EmpID'";

	$CheckStmt = $iconn->query($CheckSql);

	if ($CheckStmt && $CheckStmt->num_rows > 0) {
		$oldAdvance = legacyAuditFetchRowById('HrmCashAdvance', 'CAdvID', $CAdvID, 'Hrm');
	    // Record exists - Update
	    $UpdateSql = "UPDATE HrmCashAdvance
	                  SET Remarks = '$Remarks',
	                      Status = '0'
	                  WHERE AdvDate = '$firstDate'
	                  AND EmpID = '$EmpID'";

	    $StmtAdvance = $iconn->query($UpdateSql);

	    if ($StmtAdvance) {
	    	legacyAuditLogUpdateById('HrmCashAdvance', 'CAdvID', $CAdvID, $oldAdvance, $_POST, ['EmpID' => $EmpID, 'Remarks' => $Remarks], 'Hrm');
	        $row['sts'] = 'success';
	        $row['msg'] = 'Updated Successfully';
	        echo json_encode($row);
	    } else {
	        $row['sts'] = 'Failed';
	        $row['msg'] = 'Update Failed';
	        echo json_encode($row);
	    }

	} else {

	    // Record does not exist - Insert
	    $SqlAdvance = "INSERT INTO `HrmCashAdvance`
	                   (`AdvDate`, `EmpID`, `Remarks`, `Status`)
	                   VALUES (
	                   '$firstDate',
	                   '$EmpID',
	                   '$Remarks',
	                   '0')";

	    $StmtAdvance = $iconn->query($SqlAdvance);

	    if ($StmtAdvance) {

	        legacyAuditLogInsertById(
	            'HrmCashAdvance',
	            'CAdvID',
	            $iconn->insert_id,
	            $_POST,
	            ['EmpID' => $EmpID, 'Remarks' => $Remarks],
	            'Hrm'
	        );

	        $row['sts'] = 'success';
	        $row['msg'] = 'Inserted Successfully';
	        echo json_encode($row);

	    } else {
	        $row['sts'] = 'Failed';
	        $row['msg'] = 'Insert Failed';
	        echo json_encode($row);
	    }
	}


}

else IF($Type=='UpdateAdvDeduction'){

	$oldAdvance = legacyAuditFetchRowById('HrmCashAdvance', 'CAdvID', $CAdvID, 'Hrm');
	 $SqlAdvance="UPDATE `HrmCashAdvance` SET `DueAmount`='$DeductionAmount',`Installments`='$DeductionAmount' WHERE `CAdvID`='$CAdvID'";
  	 $StmtAdvance=$iconn->query($SqlAdvance);


	 if($StmtAdvance){
		legacyAuditLogUpdateById('HrmCashAdvance', 'CAdvID', $CAdvID, $oldAdvance, $_POST, ['EmpID' => $EmpID, 'Remarks' => $Remarks], 'Hrm');
		 $row['sts']='success';
		$row['msg']=' Success';
		echo json_encode($row);
	 }else{
		$row['sts']='Failed';
		$row['msg']='Failed';
		 echo json_encode($row);
	 }


}
?>
