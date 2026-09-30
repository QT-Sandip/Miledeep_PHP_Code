<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once '../Global/Util.php';
include_once 'AppUtilController.php';

class AccessRolesController
{
	private $conn;
	//	private $db_table = "ScrmApplicants";
	//private $utilController;
	private $appUtilController;
	private $Error = null;
	private $Msg = '';


	// Db connection
	public function __construct($db)
	{
		$this->conn = $db;
		//$this->utilController = new UtilController($this->conn);
		$this->appUtilController = new AppUtilController($this->conn);
    
	}

	public function getSalaryStructureMasterDetails($RequestBody)
	{
		$UserID = $RequestBody['UserID'];
		$AccessRoleID = $RequestBody['AccessRoleID'];
		$SalaryStructureData = $this->getStructureMasterData($RequestBody, $AccessRoleID);



		$Data['Error'] = null;
		$Data['Msg'] = ' Posted Successful';

		$Data['Data'] = $SalaryStructureData;

		return $Data;
	}



	public function postSalaryStructureMasterDetails($RequestBody)
	{

		//	var_dump($RequestBody);
		$PostedUserID = 1;

		$RoleName = $RequestBody['RoleName'];
		$ClientID = $RequestBody['ClientID'];
		$HeaderID = $RequestBody['HeaderID'];
		$SubMenuID = $RequestBody['SubMenuID'];
		$AccessRoleID = $RequestBody['AccessRoleID'];
		$EditAccess = $RequestBody['EditAccess'];
		$DeleteAccess = $RequestBody['DeleteAccess'];




		$stmt = false;

		if ($this->Error == null && $AccessRoleID == '') {
			$sqlQuery = "INSERT INTO `AdminUserRoles`( `ClientID`,`RoleName`, `DeptID`, `Header`, `Options`,`EditAccess`,`DeleteAccess`)
			 VALUES('$ClientID','$RoleName','','$HeaderID','$SubMenuID','$EditAccess','$DeleteAccess')";




		} else {
			$sqlQuery = "UPDATE `AdminUserRoles` SET `RoleName`='$RoleName',`DeptID`='',
			`Header`='$HeaderID',
			`EditAccess`='$EditAccess',
			`DeleteAccess`='$DeleteAccess',
			`Options`='$SubMenuID' WHERE `ID`='$AccessRoleID'";
		}

		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();
		if ($AccessRoleID == '') {
			$AccessRoleID = $this->conn->lastInsertId();
		}

		if ($stmt) {
			$SalaryStructureData = $this->getStructureMasterData($RequestBody, $AccessRoleID);
			$Data['Error'] = null;
			$Data['Msg'] = 'Success';
			$Data['Data'] = $SalaryStructureData;
			return $Data;
		} else {
			return $Data = array('Error' => $this->Error, "Msg" => $this->Error);
		}
	}



	function getStructureMasterData($RequestBody, $AccessRoleID)
	{
		$UserID=$RequestBody['UserID'];
		$aryUserData = $this->appUtilController->getClientRoleID($UserID);
		$ClientAccessRoleID=$aryUserData['RoleID'];
		 $SqlRoles = "SELECT * FROM `AdminClientRoles` WHERE ID=$ClientAccessRoleID";
		$stmtRoles = $this->conn->prepare($SqlRoles);
		$stmtRoles->execute();
		$rowAccessRoles = $stmtRoles->fetch(PDO::FETCH_ASSOC);
		$Header = $rowAccessRoles['Header'];
		$Options = $rowAccessRoles['Options'];


		$SqlElementIDs = "SELECT * FROM `ClientSideBarHeaders` where Sid in ($Header)";
		$stmtElementIDs = $this->conn->prepare($SqlElementIDs);
		$stmtElementIDs->execute();
		while ($dataElementIDs = $stmtElementIDs->fetch(PDO::FETCH_ASSOC)) {

			$HeaderID = $dataElementIDs['Sid'];
			$HeaderName = $dataElementIDs['Header'];
			$sqlQuery = "SELECT * FROM `ClientSideBarOptions` where HeaderId=$HeaderID and ID in ($Options)";
			$stmt = $this->conn->prepare($sqlQuery);
			$stmt->execute();
			$ArrayOptionsData = array();
			$i = 0;
			while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

				$SalaryStructureItem['SNo'] = ++$i;
				$SalaryStructureItem['HeaderID'] = $HeaderID;
				$SalaryStructureItem['SubMenuID'] = $dataRow['ID'];
				$SalaryStructureItem['SubMenuName'] = $dataRow['Name'];
				$ArrayOptionsData[] = $SalaryStructureItem;
			}

			$AryHeaderItem['Header'] = $HeaderName;
			$AryHeaderItem['HeaderID'] = $HeaderID;
			$AryHeaderItem['Options'] = $ArrayOptionsData;

			$AryData[] = $AryHeaderItem;

		}

		if ($AccessRoleID != '') {

			$SqlRoles = "SELECT * FROM `AdminUserRoles` where ID='$AccessRoleID'";
			$stmtRoles = $this->conn->prepare($SqlRoles);
			$stmtRoles->execute();
			$rowAccessRoles = $stmtRoles->fetch(PDO::FETCH_ASSOC);

			$RoleName = $rowAccessRoles['RoleName'];
			$EditAccess = $rowAccessRoles['EditAccess'];
			$DeleteAccess = $rowAccessRoles['DeleteAccess'];
			$ArrayRoleValues['HeaderIDs'] = explode(',', $rowAccessRoles['Header']);
			$ArrayRoleValues['OptionsIDs'] = explode(',', $rowAccessRoles['Options']);
		}


		$Data['RoleName'] = $RoleName; 
		$Data['AccessRoleID'] = $AccessRoleID;
		$Data['EditAccess'] = $EditAccess;
		$Data['DeleteAccess'] = $DeleteAccess;
		$Data['AllValues'] = $AryData;
		$Data['SelectedValues'] = $ArrayRoleValues;


		return $Data;

	}

}
?>