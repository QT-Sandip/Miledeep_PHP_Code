<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once '../Global/Util.php';
include_once 'AppUtilController.php';

class EmpSalaryStractureListController
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
		//$this->appUtilController = new AppUtilController($this->conn);
    
	}

	public function getEmpSalaryStractureList($RequestBody)
	{
		
		$SalaryStructureData = $this->empSalaryStractureList();

		$ResData['Error'] = null;
		$ResData['Msg'] = 'List arrived Successful';
		$ResData['Data'] = $SalaryStructureData;

		return $ResData;
	}


 


	function empSalaryStractureList()
	{

		$SqlSalaryStracturesList= "SELECT * FROM `HrmSalaryStructureMaster`  where Status=1";
		$stmtSalaryStracturesList= $this->conn->prepare($SqlSalaryStracturesList);
		$stmtSalaryStracturesList->execute();
		//echo $stmtSalaryStracturesList->rowCount();
		//$RowSalaryStracturesList = $stmtSalaryStracturesList->fetch(PDO::FETCH_ASSOC);
		
		
		    

		while($RowSalaryStracturesList = $stmtSalaryStracturesList->fetch(PDO::FETCH_ASSOC)){
			$RoleName = $RowSalaryStracturesList['RoleName'];
			$Additions = $RowSalaryStracturesList['Additions'];
			$Deductions = $RowSalaryStracturesList['Deductions'];
			

		 	$SqlAdditionIDs = "SELECT GROUP_CONCAT(ElementName) as AdditionNames from `HrmSalaryElementsMaster`
			where SElementID in ($Additions)";
			$stmtAdditionIDs = $this->conn->prepare($SqlAdditionIDs);
			$stmtAdditionIDs->execute();
			$dataAdditionIDs = $stmtAdditionIDs->fetch(PDO::FETCH_ASSOC);


			$SqlDeductionIDs = "SELECT GROUP_CONCAT(ElementName) as DeductionNames FROM `HrmSalaryElementsMaster` where
			 SElementID in ($Deductions)";
			$stmt = $this->conn->prepare($SqlDeductionIDs);
			$stmt->execute();
			$dataRow = $stmt->fetch(PDO::FETCH_ASSOC);


			$RowSalaryStracturesList['Additions']=$dataAdditionIDs['AdditionNames'];
			$RowSalaryStracturesList['Deductions']=$dataRow['DeductionNames'];
			$AryData[] = $RowSalaryStracturesList;
		}





		return $AryData;

	}

}
?>