<?php
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/
include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class SalaryElementsMasterListController
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

	public function getSalaryElementsMasterDetails($RequestBody)
	{
		$ElementID = $RequestBody['ElementID'];
		$SalaryElementsData = $this->getSalaryElementsMasterData($ElementID);



		$Data['Error'] = null;
		$Data['Msg'] = ' Posted Successful';

		$Data['Data'] = $SalaryElementsData;

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

	public function postSalaryElementsMasterDetails($RequestBody)
	{

		//	var_dump($RequestBody);
		$PostedUserID = 1;

		$ElementName = $RequestBody['ElementName'];
		$ElementType = $RequestBody['ElementType'];
		$ElementsValues = $RequestBody['ElementsValues'];
		$BasedOn = $RequestBody['BasedOn'];
		$CalcType = $RequestBody['CalcType'];


        // $AryCurrentDate = getCurrentDateTime();

		// $CreatedDateTime = $AryCurrentDate['TimeStamp'];




		$stmt = false;

		//if ($this->Error == null && $ArrivalID == '') {
			$sqlQuery = "INSERT INTO `HrmSalaryElementsMaster`( `ElementName`, `ElementType`, `ElementsValues`, `BasedOn`,`CalcType`, `Status`)
            Values('$ElementName','$ElementType','$ElementsValues','$BasedOn','$CalcType','1')";

			$stmt = $this->conn->prepare($sqlQuery);
			$stmt->execute();
			$ElementID = $this->conn->lastInsertId();

		//}



		if ($stmt) {

            $newElement = $this->auditLogger->fetchRowById('HrmSalaryElementsMaster', 'SElementID', $ElementID);
            $this->auditLogger->logInsert('HrmSalaryElementsMaster', $ElementID, $newElement, $RequestBody, [
                'ReferenceNo' => $ElementName,
                'Remarks' => 'Salary element master created',
            ]);

           // $DeheadingData = $this->getSalaryElementsMasterData($ElementID);
            $SalaryElementsData = $this->getSalaryElementsMasterData('');
            $Data['Error'] = null;
		$Data['Msg'] = 'Success';	
		$Data['Data'] = $SalaryElementsData;
			return $Data;
		} else {
			return $Data = array('Error' => $this->Error, "Msg" => $this->Error);
		}
	}

	

	function getSalaryElementsMasterData($ElementID)
	{
       

		$dataRow = null;
if($ElementID!=''){
        $Query='and SElementID=$ElementID';
}
		$sqlFields = "`SElementID`, `ElementName`, `ElementType`, `ElementsValues`, `BasedOn`,`CalcType` ";
	
    $sqlQuery = "SELECT  $sqlFields FROM `HrmSalaryElementsMaster`  WHERE Status=1 $Query ORDER BY `SElementID` desc";
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();
      //  $ArrayData[]='';
       // $TotalHLQuantity=0;
        $i = 0;
		while($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)){

$SalaryElementsData['SNo']= ++$i;
$SalaryElementsData['ElementID']=$dataRow['SElementID'];
$SalaryElementsData['ElementName']=$dataRow['ElementName'];
$SalaryElementsData['ElementType']=$dataRow['ElementType'];
$SalaryElementsData['ElementsValues']=$dataRow['ElementsValues'];
$SalaryElementsData['BasedOn']=$dataRow['BasedOn'];
$SalaryElementsData['CalcType']=$dataRow['CalcType'];



$ArrayData[]=$SalaryElementsData;
//$TotalHLQuantity +=$DeheadingData['HLQuantityKgs'];

        }
       // $ArrayTotals['TotalHLQuantity']=$TotalHLQuantity;
        $Data['SalaryElementsData']=$ArrayData;
        //$Data['Totals']=$ArrayTotals;

		return $Data;

        }
	
}
?>
