<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class SalaryStructureMasterController
{
	private $conn;
	private $auditLogger;
	private $utilController;
	private $Error = null;
	private $Msg = '';


	// Db connection
	public function __construct($db)
	{
		$this->conn = $db;
		$this->utilController = new UtilController($this->conn);
		$this->auditLogger = new CentralAuditLogger($this->conn, 'HumanResource');
	}

	public function getSalaryStructureMasterDetails($RequestBody)
	{
		$StructureID = $RequestBody['StructureID'];
		$SalaryStructureData = $this->getStructureMasterData($StructureID);



		$Data['Error'] = null;
		$Data['Msg'] = ' Posted Successful';

		$Data['Data'] = $SalaryStructureData;

		return $Data;
	}

	// public function postDeleteSalaryElementsMasterDetails($RequestBody)
	// {
	// 	$ArrivalID = $RequestBody['ArrivalID'];
	// 	$BreakUpID = $RequestBody['BreakUpID'];


	// 	$sqlQuery = "DELETE FROM `PpcDeheading` WHERE DHID='$BreakUpID'";
	// 	$stmt = $this->conn->prepare($sqlQuery);
	// 	$stmt->execute();



	// 	$ArrivalID = $RequestBody['ArrivalID'];

	// 	$DeheadingData = $this->getDeheadingData($ArrivalID);



	// 	$Data['Error'] = null;
	// 	$Data['Msg'] = 'Deleted  Successfully';
	// 	$Data['Data'] = $DeheadingData;


	// 	return $Data;
	// }

	public function postSalaryStructureMasterDetails($RequestBody)
	{

		//	var_dump($RequestBody);
		$PostedUserID = 1;

		$RoleName = $RequestBody['StructureName'];
		$Addition = $RequestBody['Addition'];
		$Deduction = $RequestBody['Deduction'];
		$StructureID = $RequestBody['StructureID'];


        // $AryCurrentDate = getCurrentDateTime();

		// $CreatedDateTime = $AryCurrentDate['TimeStamp'];




		$stmt = false;

		if ($this->Error == null && $StructureID == '') {
			$sqlQuery = "INSERT INTO `HrmSalaryStructureMaster`( `RoleName`, `DeptID`, `Additions`, `Deductions`, `Status`)
			 VALUES('$RoleName','','$Addition','$Deduction','1')";


			$StructureID = $this->conn->lastInsertId();

		}

		else{
			$existingStructure = $this->auditLogger->fetchRowById('HrmSalaryStructureMaster', 'ID', $StructureID);
			$sqlQuery = "UPDATE `HrmSalaryStructureMaster` SET `RoleName`='$RoleName',`DeptID`='',`Additions`='$Addition',
			`Deductions`='$Deduction' WHERE `ID`='$StructureID'";
		}
		//echo $sqlQuery; exit;
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();

		if ($stmt) {

            if ($existingStructure) {
                $updatedStructure = $this->auditLogger->fetchRowById('HrmSalaryStructureMaster', 'ID', $StructureID);
                $this->auditLogger->logUpdate('HrmSalaryStructureMaster', $StructureID, $existingStructure, $updatedStructure, $RequestBody, [
                    'ReferenceNo' => $RoleName,
                    'Remarks' => 'Salary structure updated',
                ]);
            } else {
                $newStructure = $this->auditLogger->fetchRowById('HrmSalaryStructureMaster', 'ID', $StructureID);
                $this->auditLogger->logInsert('HrmSalaryStructureMaster', $StructureID, $newStructure, $RequestBody, [
                    'ReferenceNo' => $RoleName,
                    'Remarks' => 'Salary structure created',
                ]);
            }

           // $DeheadingData = $this->getSalaryElementsMasterData($ElementID);
            $SalaryStructureData = $this->getStructureMasterData($StructureID);
            $Data['Error'] = null;
		$Data['Msg'] = 'Success';	
		$Data['Data'] = $SalaryStructureData;
			return $Data;
		} else {
			return $Data = array('Error' => $this->Error, "Msg" => $this->Error);
		}
	}

	

	function getStructureMasterData($StructureID)
	{
       

		$dataRow = null;
		$SelectedIds='';
if($StructureID!=''){
        $Query='and ID=$StructureID';
		1;

		$SqlSelectedElementIDs="SELECT ID as StructureID, RoleName,GROUP_CONCAT(Additions,',',Deductions) as SelectedIds FROM `HrmSalaryStructureMaster` WHERE ID='$StructureID'"; 
		$stmtSelectedElementIDs = $this->conn->prepare($SqlSelectedElementIDs);
		$stmtSelectedElementIDs->execute();
		$dataElementIDsRow = $stmtSelectedElementIDs->fetch(PDO::FETCH_ASSOC);
		$SelectedIds=$dataElementIDsRow['SelectedIds'];
		$RoleName=$dataElementIDsRow['RoleName'];
		$StructureID=$dataElementIDsRow['StructureID'];

		$SqlElementIDs="SELECT GROUP_CONCAT(SElementID) as SElementIDs,CalcType FROM `HrmSalaryElementsMaster` where SElementID in ($SelectedIds) GROUP by CalcType"; 
}else{
	$SqlElementIDs="SELECT GROUP_CONCAT(SElementID) as SElementIDs,CalcType FROM `HrmSalaryElementsMaster` GROUP by CalcType"; 
}

/*
$SqlElementIDs="SELECT GROUP_CONCAT(SElementID) as SElementIDs,CalcType FROM `HrmSalaryElementsMaster` GROUP by CalcType"; 
$SqlElementIDs="SELECT GROUP_CONCAT(SElementID) as SElementIDs,CalcType FROM `HrmSalaryElementsMaster` GROUP by CalcType"; */
$stmtElementIDs = $this->conn->prepare($SqlElementIDs);
		$stmtElementIDs->execute();
        while($dataElementIDs = $stmtElementIDs->fetch(PDO::FETCH_ASSOC)){

            $ElementIds=$dataElementIDs['SElementIDs'];
            $HeaderName=$dataElementIDs['CalcType'];
      

		$sqlFields = "`SElementID`,`ElementName`,`CalcType`,`ElementType`, `ElementsValues`, `BasedOn`";
       
    $sqlQuery = "SELECT  $sqlFields FROM `HrmSalaryElementsMaster`  WHERE Status=1 and SElementID in ($ElementIds)   ORDER BY `SElementID` asc";
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();
		$ArrayOptionsData=array();
        $i = 0;
		while($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)){

$SalaryStructureItem['SNo']= ++$i;
$SalaryStructureItem['ElementID']=$dataRow['SElementID'];
$SalaryStructureItem['ElementName']=$dataRow['ElementName'];
$SalaryStructureItem['ElementType']=$dataRow['ElementType'];
$SalaryStructureItem['ElementsValues']=$dataRow['ElementsValues'];
$SalaryStructureItem['BasedOn']=$dataRow['BasedOn'];
//$SalaryStructureItem['CalcType']=$dataRow['CalcType'];




$ArrayOptionsData[]=$SalaryStructureItem;


        }

		$AryHeaderItem['Header']=$HeaderName;
		$AryHeaderItem['Options']=$ArrayOptionsData;

		$AryData[]=$AryHeaderItem;

    }
       // $ArrayTotals['TotalHLQuantity']=$TotalHLQuantity;
        $Data['AllValues']=$AryData;
        $Data['SelectedValues']=explode(',',$SelectedIds);
        $Data['StructureName']=$RoleName;
        $Data['StructureID']=$StructureID;

		return $Data;

        }
	
}
?>
