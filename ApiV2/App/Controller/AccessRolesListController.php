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

	public function getUserAccessRoles($RequestBody)
	{
		$UserID = $RequestBody['UserID'];
		$AccessRoleID = $RequestBody['AccessRoleID'];
		$SalaryStructureData = $this->getAccessRoles($RequestBody, $AccessRoleID);



		$Data['Error'] = null;
		$Data['Msg'] = ' Posted Successful';

		$Data['Data'] = $SalaryStructureData;

		return $Data;
	}





	function getAccessRoles($RequestBody, $AccessRoleID)
	{
		$UserID=$RequestBody['UserID'];
		$aryUserData = $this->appUtilController->getClientRoleID($UserID);

		$ClientID=$aryUserData['ClientID'];
/*
		$SqlRoles = "SELECT * FROM `AdminUserRoles` where ClientID='$ClientID'";
		$stmtRoles = $this->conn->prepare($SqlRoles);
		$stmtRoles->execute();
		$rowAccessRoles = $stmtRoles->fetch(PDO::FETCH_ASSOC);

		$RoleName = $rowAccessRoles['RoleName'];
		$EditAccess = $rowAccessRoles['EditAccess'];
		$DeleteAccess = $rowAccessRoles['DeleteAccess'];
		$ArrayRoleValues['HeaderIDs'] = explode(',', $rowAccessRoles['Header']);
		$ArrayRoleValues['OptionsIDs'] = explode(',', $rowAccessRoles['Options']);

		$Data['RoleName'] = $RoleName; 
		$Data['AccessRoleID'] = $AccessRoleID;
		$Data['EditAccess'] = $EditAccess;
		$Data['DeleteAccess'] = $DeleteAccess;
		$Data['AllValues'] = $AryData;
		$Data['SelectedValues'] = $ArrayRoleValues;
*/

		 $SqlRoles = "SELECT * FROM `AdminUserRoles`  where ClientID='$ClientID' or DefaultRole=1";
		$stmtRoles = $this->conn->prepare($SqlRoles);
		$stmtRoles->execute();
		


		while($rowAccessRoles = $stmtRoles->fetch(PDO::FETCH_ASSOC)){
			$RoleName = $rowAccessRoles['RoleName'];
			$Header = $rowAccessRoles['Header'];
			$Options = $rowAccessRoles['Options'];
		
			$SqlElementIDs = "SELECT GROUP_CONCAT(Header) as HeaderNames from `ClientSideBarHeaders` where Sid in ($Header)";
			$stmtElementIDs = $this->conn->prepare($SqlElementIDs);
			$stmtElementIDs->execute();
			$dataElementIDs = $stmtElementIDs->fetch(PDO::FETCH_ASSOC);


			 $sqlQuery = "SELECT GROUP_CONCAT(Name) as OptionsNames FROM `ClientSideBarOptions` where ID in ($Options)";
			$stmt = $this->conn->prepare($sqlQuery);
			$stmt->execute();
			$dataRow = $stmt->fetch(PDO::FETCH_ASSOC);


			$rowAccessRoles['HeaderNames']=$dataElementIDs['HeaderNames'];
			$rowAccessRoles['SubHeaderNames']=$dataRow['OptionsNames'];
			$AryData[] = $rowAccessRoles;
		}





		return $AryData;

	}

}
?>