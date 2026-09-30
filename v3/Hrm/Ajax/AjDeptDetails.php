<?php 
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

	$Type=$_POST['Type'];
	
	
	if($Type=='AddDept'){
		$DEPARTMENT_NAME = $_POST['DeptName']; 
		$OT_AVAILABILITY = $_POST['OtAval'];
		
		
		$sql = "SELECT * FROM HrmDepartments WHERE DepartmentName = '$DEPARTMENT_NAME'";
		$query = $iconn->query($sql);

		if($query->num_rows > 0){
			$error = 'Department Already Exist';
			$Msg = 'Department added successfully';
		}else{
			$sql = "INSERT INTO HrmDepartments ( `DepartmentName`, `OtAval`, `CausalLeaves`,
	`SickLeaves`, `NoticePeriod`)
				VALUES ('$DEPARTMENT_NAME','$OT_AVAILABILITY','0','0','0')";
			 	
				$DeptStmt=$iconn->query($sql);
				if($DeptStmt){
					legacyAuditLogInsertById('HrmDepartments', 'DepartmentID', $iconn->insert_id, $_POST, ['DepartmentName' => $DEPARTMENT_NAME, 'Remarks' => 'Department added'], 'Hrm');
					$error = null;
					$Msg = 'Department added successfully';
				}else{
					$error =  $iconn->error;
				}
		}
		$data['error']=$error;
		$data['Msg']=$Msg;
		
		echo json_encode($data);
		
	}else if($Type=='AddSubDept'){
		$SUB_DEPARTMENT_NAME = $_POST['SubDeptName']; 
		$DEPT_ID= $_POST['DepartmentId'];
		
		
		$sql = "SELECT * FROM HrmSubDepartments WHERE SubDeptName = '$SUB_DEPARTMENT_NAME'  and  DeptID='$DEPT_ID' ";
		$query = $iconn->query($sql);

		if($query->num_rows > 0){
			$error = 'Sub Department Already Exist';
			$Msg = 'Sub Department added successfully';
		}else{
			$sql = "INSERT INTO `HrmSubDepartments`( `DeptID`, `SubDeptName`) VALUES ('$DEPT_ID','$SUB_DEPARTMENT_NAME')";
			$SubDeptStmt=$iconn->query($sql);
			if($SubDeptStmt){
				legacyAuditLogInsertById('HrmSubDepartments', 'SubDeptID', $iconn->insert_id, $_POST, ['SubDeptName' => $SUB_DEPARTMENT_NAME, 'DeptID' => $DEPT_ID, 'Remarks' => 'Sub department added'], 'Hrm');
					$error = null;
					$Msg = 'Sub Department added successfully';
				}else{
					$error =  $iconn->error;
				}
		}
		$data['error']=$error;
		$data['Msg']=$Msg;
		
		echo json_encode($data);
		
	}else if($Type=='AddDesignation'){
		$SubDeptId = $_POST['SubDeptId']; 
		$DEPT_ID= $_POST['DepartmentId'];
		$DESIGNATION_NAME= $_POST['DesignationName'];
		
		
		 $sql = "SELECT * FROM HRM_DESIGNATION WHERE DESIGNATION_NAME ='$DESIGNATION_NAME' and SUB_ID='$SubDeptId'";  
		$query = $iconn->query($sql);

		if($query->num_rows > 0){
			$error = 'Designation Already Exist';
			$Msg = 'Designation added successfully';
		}else{
$sql = "INSERT INTO `HRM_DESIGNATION`( `SUB_ID`, `DESIGNATION_NAME`)
				VALUES ('$SubDeptId','$DESIGNATION_NAME')";
						$DesigStmt=$iconn->query($sql);
						if($DesigStmt){
					legacyAuditLogInsertById('HRM_DESIGNATION', 'DesigID', $iconn->insert_id, $_POST, ['DesignationName' => $DESIGNATION_NAME, 'SubDeptId' => $SubDeptId, 'Remarks' => 'Designation added'], 'Hrm');
					$error = null;
					$Msg = 'Designation added successfully';
				}else{
					$error =  $iconn->error;
				}
		}
		$data['error']=$error;
		$data['Msg']=$Msg;
		
		echo json_encode($data);
		
	}
	
	
	else if($Type=='AddShift'){
		$ShiftName = $_POST['ShiftName']; 
		$TimeIn= $_POST['TimeIn'];
		$TimeOut= $_POST['TimeOut'];
		$BreakStartTime= $_POST['BreakStartTime'];
		$BreakEndTime= $_POST['BreakEndTime'];
		
		
		 $SqlShift = "SELECT * FROM `HrmShiftDetails` WHERE ShiftName='$ShiftName'";  
		$query = $iconn->query($SqlShift);

		if($query->num_rows > 0){
			$error = 'Shift  Already Exist';
			$Msg = 'Shift added successfully';
		}else{
$SqlShift = "INSERT INTO `HrmShiftDetails`( `ShiftName`, `StartTime`, `EndTime`, `BreakStartTime`, `BreakEndTime`) 
VALUES ('$ShiftName','$TimeIn','$TimeOut','$BreakStartTime','$BreakEndTime')";
						$ShiftStmt=$iconn->query($SqlShift);
						if($ShiftStmt){
					legacyAuditLogInsertById('HrmShiftDetails', 'ShiftID', $iconn->insert_id, $_POST, ['ShiftName' => $ShiftName, 'Remarks' => 'Shift added'], 'Hrm');
					$error = null;
					$Msg = 'Shift added successfully';
				}else{
					$error =  $iconn->error;
				}
		}
		$data['error']=$error;
		$data['Msg']=$Msg;
		
		echo json_encode($data);
		
	}
	
	
	
	else if($Type=='UpdateSchedule'){
		
		
		$ID = $_POST['Id']; 
		$ShiftName = $_POST['ShiftName']; 
		$TimeIn = $_POST['TimeIn']; 
		$TimeOut = $_POST['TimeOut']; 
		$BreakStartTime= $_POST['BreakStartTime'];
		$BreakEndTime= $_POST['BreakEndTime'];
		
		
		$oldShift = legacyAuditFetchRowById('HrmShiftDetails', 'ShiftID', $ID, 'Hrm');
		$SqlShift = "UPDATE HrmShiftDetails SET ShiftName = '$ShiftName', StartTime = '$TimeIn', EndTime = '$TimeOut',
		BreakStartTime = '$BreakStartTime', BreakEndTime = '$BreakEndTime'
		WHERE ShiftID = '$ID'"; 
		
		
		$ShiftUpStmt=$iconn->query($SqlShift);
		if($ShiftUpStmt){
			legacyAuditLogUpdateById('HrmShiftDetails', 'ShiftID', $ID, $oldShift, $_POST, ['ShiftName' => $ShiftName, 'Remarks' => 'Shift schedule updated'], 'Hrm');
					$error = null;
					$Msg = 'Updated  successfully';
				}else{
					$error =  $iconn->error;
				}
		
		
		
		
	
		$data['error']=$error;
		$data['Msg']=$Msg;
		
		echo json_encode($data);
		
	}
	
	
	
	else if($Type=='UpdateDept'){
		
		
		$ID = $_POST['id']; 
		$DEPARTMENT_NAME = $_POST['DeptName']; 
		$OT_AVAILABILITY = $_POST['OtAval']; 
		
		
		$oldDept = legacyAuditFetchRowById('HRM_DEPARTMENT_MASTER', 'DEPARTMENT_ID', $ID, 'Hrm');
		 $sql = "UPDATE HRM_DEPARTMENT_MASTER SET DEPARTMENT_NAME = '$DEPARTMENT_NAME', OT_AVAILABILITY = '$OT_AVAILABILITY'
		WHERE DEPARTMENT_ID = '$ID'"; 
		
		
		$DeptUpStmt=$iconn->query($sql);
		if($DeptUpStmt){
			legacyAuditLogUpdateById('HRM_DEPARTMENT_MASTER', 'DEPARTMENT_ID', $ID, $oldDept, $_POST, ['DepartmentName' => $DEPARTMENT_NAME, 'Remarks' => 'Department master updated'], 'Hrm');
					$error = null;
					$Msg = 'Updated  successfully';
				}else{
					$error =  $iconn->error;
				}
		
		
		
		
	
		$data['error']=$error;
		$data['Msg']=$Msg;
		
		echo json_encode($data);
		
	}else if($Type=='EditDept'){
		$id = $_POST['id'];
		$sql = "SELECT * from HRM_DEPARTMENT_MASTER WHERE DEPARTMENT_ID = '$id'";
		$query = $iconn->query($sql);
		$row = $query->fetch_assoc();
		
		echo json_encode($row);
	
	}
	else if($Type=='DeleteDept'){
		$id = $_POST['id'];
		$sql = "SELECT * from HRM_DEPARTMENT_MASTER WHERE DEPARTMENT_ID = '$id'";
		$query = $iconn->query($sql);
		$row = $query->fetch_assoc();
		
		echo json_encode($row);
	
	}
	else if($Type=='DeleteOD'){
		 $ODId = $_POST['ODId']; 
		$oldOD = legacyAuditFetchRowById('HrmOnDutyRegister', 'OdID', $ODId, 'Hrm');
		  $sql = "DELETE FROM `HrmOnDutyRegister` WHERE OdID='$ODId'";  
		/*$query = $iconn->query($sql);
		$row = $query->fetch_assoc();*/
		
		$ODDelStmt=$iconn->query($sql);
		if($ODDelStmt){
			legacyAuditLogDeleteById('HrmOnDutyRegister', 'OdID', $ODId, $oldOD, $_POST, ['ODId' => $ODId, 'Remarks' => 'On duty record deleted'], 'Hrm');
					$error = null;
					$Msg = 'Deleted  successfully';
				}else{
					$error =  $iconn->error;
				}
		
		
		
		
	
		$data['error']=$error;
		$data['Msg']=$Msg;
		
		echo json_encode($data);
		//echo json_encode($row);
	
	}
	
	else if($Type=='Delete'){
		
		//echo "hi";
		//$ID = $_POST['id']; 
		
		
		 $id = $_POST['id'];
		$oldDeptDel = legacyAuditFetchRowById('HRM_DEPARTMENT_MASTER', 'DEPARTMENT_ID', $id, 'Hrm');
		$sql = "DELETE FROM HRM_DEPARTMENT_MASTER WHERE DEPARTMENT_ID = '$id'";
		$DeptDelStmt=$iconn->query($sql);
		if($DeptDelStmt){
			legacyAuditLogDeleteById('HRM_DEPARTMENT_MASTER', 'DEPARTMENT_ID', $id, $oldDeptDel, $_POST, ['DEPARTMENT_ID' => $id, 'Remarks' => 'Department master deleted'], 'Hrm');
					$error = null;
					$Msg = 'Deleted  successfully';
				}else{
					$error =  $iconn->error;
				}
		
		
		
		
	
		$data['error']=$error;
		$data['Msg']=$Msg;
		
		echo json_encode($data);
		
	}else if($Type=='DeleteLeave'){
		
		//echo "hi";
		//$ID = $_POST['id']; 
		
		
		 $LeaveID = $_POST['LeaveID'];
		$oldLeave = legacyAuditFetchRowById('HrmLeaveRegister', 'LeaveID', $LeaveID, 'Hrm');
		$sql = "DELETE FROM HrmLeaveRegister WHERE LeaveID = '$LeaveID'";
		$LeaveDelStmt=$iconn->query($sql);
		if($LeaveDelStmt){
			legacyAuditLogDeleteById('HrmLeaveRegister', 'LeaveID', $LeaveID, $oldLeave, $_POST, ['LeaveID' => $LeaveID, 'Remarks' => 'Leave record deleted'], 'Hrm');
					$error = null;
					$Msg = 'Deleted  successfully';
				}else{
					$error =  $iconn->error;
				}
		
		
		
		
	
		$data['error']=$error;
		$data['Msg']=$Msg;
		
		echo json_encode($data);
		
	}
	else if($Type=='AddHoliday'){
		$HOLIDAY_NAME = $_POST['HolidayName']; 
		$HOLIDAY_DATE = $_POST['HolidayDate'];
		
		
		$sql =  "SELECT * FROM `HrmHolidays` where Date='$HOLIDAY_DATE'";
		$query = $iconn->query($sql);

		if($query->num_rows > 0){
			$error = 'Holiday Already Exist';
			$Msg = 'Holiday added successfully';
		}else{
			$sql = "INSERT INTO `HrmHolidays`(`Name`, `Date`, `Status`) VALUES ('$HOLIDAY_NAME','$HOLIDAY_DATE','1')";
				$HolidayStmt=$iconn->query($sql);
				if($HolidayStmt){
					legacyAuditLogInsertById('HrmHolidays', 'ID', $iconn->insert_id, $_POST, ['HolidayName' => $HOLIDAY_NAME, 'HolidayDate' => $HOLIDAY_DATE, 'Remarks' => 'Holiday added'], 'Hrm');
					$error = null;
					$Msg = 'Holiday added successfully';
				}else{
					$error =  $iconn->error;
				}
		}
		$data['error']=$error;
		$data['Msg']=$Msg;
		
		echo json_encode($data);
		
	}
	/*else if($Type=='EditHoliday'){
		$id = $_POST['id'];
		$sql = "SELECT * from $HrmHolidays WHERE ID = '$id'";
		$query = $iconn->query($sql);
		$row = $query->fetch_assoc();
		
		echo json_encode($row);
	
	}*/
	
	else if($Type=='UpdateHoliday'){
		
		
		$ID = $_POST['Id']; 
		$HolidayName = $_POST['HolidayName']; 
		$HolidayDate = $_POST['HolidayDate']; 
		
		
		$oldHoliday = legacyAuditFetchRowById('HrmHolidays', 'ID', $ID, 'Hrm');
		 $sql = "UPDATE `HrmHolidays` SET `Name` = '$HolidayName', `Date` = '$HolidayDate' WHERE ID = '$ID'";
		
		
		$HolidayUpStmt=$iconn->query($sql);
		if($HolidayUpStmt){
			legacyAuditLogUpdateById('HrmHolidays', 'ID', $ID, $oldHoliday, $_POST, ['HolidayName' => $HolidayName, 'HolidayDate' => $HolidayDate, 'Remarks' => 'Holiday updated'], 'Hrm');
					$error = null;
					$Msg = 'Updated  successfully';
				}else{
					$error =  $iconn->error;
				}
		
		
		
		
	
		$data['error']=$error;
		$data['Msg']=$Msg;
		
		echo json_encode($data);
		
	}
	else if($Type=='DeleteHoliday'){
		$Id = $_POST['Id'];
		$sql = "SELECT * from HrmHolidays WHERE ID = '$Id'";
		$query = $iconn->query($sql);
		$row = $query->fetch_assoc();
		
		echo json_encode($row);
	
	}else if($Type=='DeleteHday'){
		
		//echo "hi";
		//$ID = $_POST['id']; 
		
		
		 $Id = $_POST['Id'];
		$oldHday = legacyAuditFetchRowById('HrmHolidays', 'ID', $Id, 'Hrm');
		$sql = "DELETE FROM `HrmHolidays` WHERE `ID` = '$Id'";
		$HdayDelStmt=$iconn->query($sql);
		if($HdayDelStmt){
			legacyAuditLogDeleteById('HrmHolidays', 'ID', $Id, $oldHday, $_POST, ['ID' => $Id, 'Remarks' => 'Holiday deleted'], 'Hrm');
					$error = null;
					$Msg = 'Deleted  successfully';
				}else{
					$error =  $iconn->error;
				}
		
		
		
		
	
		$data['error']=$error;
		$data['Msg']=$Msg;
		
		echo json_encode($data);
		
	}
	
	
	else if($Type=='DeleteSchedule'){
		$Id = $_POST['Id'];
		$oldSchedule = legacyAuditFetchRowById('HrmShiftDetails', 'ShiftID', $Id, 'Hrm');
		  $sql = "DELETE FROM HrmShiftDetails WHERE ShiftID ='$Id'"; 
		
		$SchedDelStmt=$iconn->query($sql);
		if($SchedDelStmt){
			legacyAuditLogDeleteById('HrmShiftDetails', 'ShiftID', $Id, $oldSchedule, $_POST, ['ShiftID' => $Id, 'Remarks' => 'Shift schedule deleted'], 'Hrm');
					$error = null;
					$Msg = 'Shift deleted  successfully';
				}else{
					$error =  $iconn->error;
				}
		
		
		
		
	
		$data['error']=$error;
		$data['Msg']=$Msg;
		
		echo json_encode($data);
		
		
				//	echo json_encode($row);
	


		
		
	}else if($Type=='AddContractor'){
		
		$ContractorName = $_POST['ContractorName']; 
		$Commission = $_POST['Commission']; 
		$UserID = $_POST['UserID'];
		$CurrentDateTime = $_POST['CurrentDateTime'];
		
		
		$sqlContractorName =  "SELECT * FROM `HrmContractorList` where ContractorName='$ContractorName'";
		$StmtContractorName = $iconn->query($sqlContractorName);

		if($StmtContractorName->num_rows > 0){
			$error = 'Contractor Already Exist';
			$Msg = 'Contractor added successfully';
		}else{
			$sqlContractorName = "INSERT INTO `HrmContractorList`( `ContractorName`,`Commission`,
			`PostedUserID`, `PostedDateTime`) VALUES ('$ContractorName','$Commission','$UserID','$CurrentDateTime')";
				$ContractorStmt=$iconn->query($sqlContractorName);
				if($ContractorStmt){
					legacyAuditLogInsertById('HrmContractorList', 'ContractorID', $iconn->insert_id, $_POST, ['ContractorName' => $ContractorName, 'Remarks' => 'Contractor added'], 'Hrm');
					$error = null;
					$Msg = 'Contractor added successfully';
				}else{
					$error =  $iconn->error;
				}
		}
		$data['error']=$error;
		$data['Msg']=$Msg;
		
		echo json_encode($data);
		
	}else if($Type=='UpdateContractor'){
		
		
		$ID = $_POST['Id']; 
		$ContractorName = $_POST['ContractorName']; 
		$Commission = $_POST['Commission']; 
		$UserID = $_POST['UserID'];
		$CurrentDateTime = $_POST['CurrentDateTime'];
		
		
		$oldContractor = legacyAuditFetchRowById('HrmContractorList', 'ContractorID', $ID, 'Hrm');
		 $sql = "UPDATE `HrmContractorList` SET 
		 `ContractorName` = '$ContractorName', 
		 `Commission` = '$Commission', 
		 `PostedUserID` = '$PostedUserID' ,
		 `PostedDateTime` = '$PostedDateTime' WHERE ContractorID = '$ID'";
		
		
		$ContractorUpStmt=$iconn->query($sql);
		if($ContractorUpStmt){
			legacyAuditLogUpdateById('HrmContractorList', 'ContractorID', $ID, $oldContractor, $_POST, ['ContractorName' => $ContractorName, 'Remarks' => 'Contractor updated'], 'Hrm');
					$error = null;
					$Msg = 'Updated  successfully';
				}else{
					$error =  $iconn->error;
				}
		
		
		
		
	
		$data['error']=$error;
		$data['Msg']=$Msg;
		
		echo json_encode($data);
		
	}

		else if($Type =='UserStatus'){
		 $UserStatus=$_POST['UserStatus'];
		$UserId=$_POST['UserId'];
		
		 $Sql="SELECT * FROM `$AdminLoginDetails` WHERE `UserID`='$UserId'";
		$Stmt=$iconn->query($Sql);
		$Row=$Stmt->fetch_assoc();
        $AccessType=$Row['AccessType']; 
		
		if($UserStatus=='0'){
			$UserStatus='1';
		}else if($UserStatus=='1'){
			$UserStatus='0';
		}
		if($AccessType!=1){
		$oldUserStatus = legacyAuditFetchRowById($AdminLoginDetails, 'UserID', $UserId, 'Hrm');
		 $SqlUpdate = "UPDATE $AdminLoginDetails SET `Status`='$UserStatus' WHERE `UserID`='$UserId'"; 
		 
		 $UserStatusStmt=$iconn->query($SqlUpdate);
		 if($UserStatusStmt){
			legacyAuditLogUpdateById($AdminLoginDetails, 'UserID', $UserId, $oldUserStatus, $_POST, ['UserID' => $UserId, 'Remarks' => 'User status toggled'], 'Hrm');
					$error = null;
					$Msg = 'Updated Successfully';
				}else{
					$error =  $iconn->error;
				}
		}
		else{
			
					$error = 'Fail';
					$Msg = 'Failed to Update Please Contact Admin';
				
		}
		 
		
		
		
		
	
		$data['error']=$error;
		$data['Msg']=$Msg;
		
		echo json_encode($data);
		}
		
		
		
	
?>
