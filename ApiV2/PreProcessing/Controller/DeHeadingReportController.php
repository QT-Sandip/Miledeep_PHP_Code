<?php
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/
include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class DeHeadingReportController
{
	private $conn;
	//	private $db_table = "ScrmApplicants";
	private $utilController;
	private $Error = null;
	private $Msg = '';


	// Db connection
	public function __construct($db)
	{
		$this->conn = $db;
		$this->utilController = new UtilController($this->conn);
	}

	public function getDeHeadingReport($RequestBody)
	{
		$StartDate = $RequestBody['StartDate'];
        $EndDate = $RequestBody['EndDate'];
        $Plant = $RequestBody['Plant'];

		$DateFilter="between '$StartDate' and '$EndDate'";

		$Query='';

		if($Plant!=''){
            $Query=" and ProcessingStationID='$Plant'";
        }

		$SqlFields = "";

		$sqlDeHeadingReport = "SELECT 
		WHERE ";

		$stmt = $this->conn->prepare($sqlDeHeadingReport);
		$stmt->execute();

		$i = 0;
		$TotalQuantity = 0;
		$TotalHLQuantity = 0;
		$TotalAmountt = 0;
		$Data=array();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
			
			$ArrayDeHeadingList['SNO'] = ++$i;
			$ArrayDeHeadingList['RMDate'] = $this->utilController->getFormatedDateEmpty($dataRow['RMDate']);
			$
		}

	}
}
	
?>