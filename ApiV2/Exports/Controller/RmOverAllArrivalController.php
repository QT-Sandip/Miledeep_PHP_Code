<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class RmOverAllArrivalController
{
	private $conn;
	private $db_table = "ScrmApplicants";
	private $utilController;
	private $Error = null;
	private $Msg = '';
    private $auditLogger;


	// Db connection
	public function __construct($db)
	{
		$this->conn = $db;
		$this->utilController = new UtilController($this->conn);
        $this->auditLogger = new CentralAuditLogger($this->conn, 'Exports');
	}

	public function getArrivalDetails($RequestBody)
	{
		$ArrivalID = $RequestBody['ArrivalID'];
		$ArrivalData = $this->getArrivalData($ArrivalID);
		$GrandTotal = $this->getGrandTotal($ArrivalID);
		
		$Data['Error'] = null;
		$Data['Msg'] = 'Arrival Posted Successful';
		$Data['ArrivalData'] = $ArrivalData; 
$Data['GrandTotal'] = $GrandTotal;
		return $Data;
	}

	public function postDeleteArrivalBrkUP($RequestBody)
    {
		$ArrivalID = isset($RequestBody['ArrivalID']) ? $RequestBody['ArrivalID'] :'';
		$BreakUpID=$RequestBody['BreakUpID'];
        $existingArrival = $this->auditLogger->fetchRowById('RmOverAllArrival', 'ID', $ArrivalID);


		$sqlQuery="delete  from `RmOverAllArrival` where ID='$ArrivalID'";																	
		$stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();


		$ArrivalID = $RequestBody['ArrivalID'];
		$ArrivalData = $this->getArrivalData($ArrivalID);
		$GrandTotal = $this->getGrandTotal($ArrivalID);
		

		$Data['Error'] = null;
		$Data['Msg'] = 'Deleted Posted Successful';
        $this->auditLogger->logDelete('RmOverAllArrival', $ArrivalID, $existingArrival, $RequestBody, [
            'ReferenceNo' => $ArrivalID,
            'Remarks' => 'Export overall arrival deleted',
        ]);
		$Data['ArrivalData'] = $ArrivalData;
		$Data['GrandTotal'] = $GrandTotal;

		return $Data;
	}

	public function postArrivalDetails($RequestBody)
	{


		$PostedUserID = 1;
		$ArrivalDate = date("Y-m-d", strtotime($RequestBody['ArrivalDate']));
		$CompanyID = $RequestBody['CompanyID'];
		$PlantID = $RequestBody['PlantID'];
		$CenterID = $RequestBody['CenterID'];
		$Species = $RequestBody['Species'];
		$TotalAmount = $RequestBody['TotalAmount'];
		$TotalWeight = $RequestBody['TotalWeight'];
		$ArrivalID = $RequestBody['ArrivalID'];
		
		$AryCurrentDate = getCurrentDateTime();

		$CreatedDateTime = $AryCurrentDate['TimeStamp'];
		$Year = $AryCurrentDate['Year'];

		$stmt = false;

		if ($this->Error == null && $ArrivalID == '') {
			$sqlQuery = "INSERT INTO `RmOverAllArrival`(`ID`, `ArrivalDate`, `CompanyID`, `PlantID`, `CenterID`,
			 `Species`, `TotalWeight`, `TotalAmount`,`PostedID`) VALUES   
				('$ArrivalDate', '$CompanyID', '$PlantID', '$CenterID',
			 '$Species', '$TotalWeight', '$TotalAmount','$PostedUserID')";

			$stmt = $this->conn->prepare($sqlQuery);
			$stmt->execute();
			$ArrivalID = $this->conn->lastInsertId();
            $RequestBody['ArrivalID'] = $ArrivalID;

		} else if ($ArrivalID != '') {
            $existingArrival = $this->auditLogger->fetchRowById('RmOverAllArrival', 'ID', $ArrivalID);
			$sqlQuery = "Update `RmOverAllArrival` set
				`ArrivalDate`='$ArrivalDate',
				`CompanyID`='$CompanyID',
				 `PlantID`='$PlantID',
				 `CenterID`='$CenterID',
				 `Species`='$Species',
				`TotalWeight`='$TotalWeight',
				 `TotalAmount`='$TotalAmount',
				 `PostedID`='$PostedUserID',
				`IsEdited`=1 where 	ID ='$ArrivalID'";

			$stmt = $this->conn->prepare($sqlQuery);
			$stmt->execute();
		}
 


		if ($stmt) {
            if (!empty($existingArrival)) {
                $updatedArrival = $this->auditLogger->fetchRowById('RmOverAllArrival', 'ID', $ArrivalID);
                $this->auditLogger->logUpdate('RmOverAllArrival', $ArrivalID, $existingArrival, $updatedArrival, $RequestBody, [
                    'ReferenceNo' => $ArrivalID,
                    'Remarks' => 'Export overall arrival updated',
                ]);
            } else {
                $createdArrival = $this->auditLogger->fetchRowById('RmOverAllArrival', 'ID', $ArrivalID);
                $this->auditLogger->logInsert('RmOverAllArrival', $ArrivalID, $createdArrival, $RequestBody, [
                    'ReferenceNo' => $ArrivalID,
                    'Remarks' => 'Export overall arrival created',
                ]);
            }
			return $this->getArrivalDetails($RequestBody);
		}else{
			return $Data = array('Error' => $this->Error,"Msg" =>$this->Error);
		}
	}

	


	function getArrivalData($ArrivalID)
	{
		$dataRow=null;
		$sqlFields = "`ID` as ArrivalID, `ArrivalDate`, `CompanyID`, `PlantID`, `CenterID`, `Species`, `TotalWeight`, `TotalAmount`,";
		$sqlQuery = "SELECT  $sqlFields FROM `RmOverAllArrival` WHERE  ID='$ArrivalID'";
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();
		$dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
		return $dataRow;
	}


	
	
	function getGrandTotal($ArrivalID){
		$Data=array();
		 $sqlFields="sum(TotalWeight) as WeightTotal ,sum(TotalWeight*TotalAmount) as ValueTotal";
            $sqlQuery="select $sqlFields  from `RmOverAllArrival` where ID='$ArrivalID'";																	
				$stmt = $this->conn->prepare($sqlQuery);
                $stmt->execute();
				$dataRow = $stmt->fetch(PDO::FETCH_ASSOC);

				return $dataRow;
    }
}
?>
