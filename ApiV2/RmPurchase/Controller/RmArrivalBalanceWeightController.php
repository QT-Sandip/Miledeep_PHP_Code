<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once __DIR__ . '/AuditLogger.php';

class RmArrivalBalanceWeightController
{
	private $conn;
	private $db_table = "ScrmApplicants";
	private $utilController;
	private $auditLogger;
	private $Error = null;
	private $Msg = '';


	// Db connection
	public function __construct($db)
	{
		$this->conn = $db;
		$this->utilController = new UtilController($this->conn);
		$this->auditLogger = new AuditLogger($this->conn, 'Purchase');
	}

	public function getArrivalDetails($RequestBody)
	{
		/*$ArrivalID = $RequestBody['ArrivalID'];
		$BreakUpData = $this->getArrivalBreakUp($ArrivalID);
		$ArrivalData = $this->getArrivalData($ArrivalID);
		$GrandTotal = $this->getGrandTotal($ArrivalID);
		*/
		$overAllData = $this->getOverAllArrival($RequestBody);

		


		$ArrivalData['OverAllWeight']=sprintf('%0.3f',$overAllData['OverAllWeight']);
		$ArrivalData['OverAllAmount']=sprintf('%0.2f',$overAllData['OverAllAmount']);
		$ArrivalData['OverAllBalWeight']=sprintf('%0.3f',$overAllData['OverAllBalWeight']);
		$ArrivalData['OverAllBalAmount']=sprintf('%0.2f',$overAllData['OverAllBalAmount']);
		$ArrivalData['CenterID']=$overAllData['CenterID'];
		$ArrivalData['ArrivalPlantID']=$overAllData['ArrivalPlantID'];
		$ArrivalData['Species']=$overAllData['Species'];

	
		
		$Data['Error'] = null;
		$Data['Msg'] = 'Arrival Posted Successful';
	//	$Data['BreakUpData'] = $BreakUpData;
		$Data['ArrivalData'] = $ArrivalData;
		//$Data['GrandTotal'] = $GrandTotal;*/
		return $Data;
	}

	public function postDeleteArrivalBrkUP($RequestBody)
    {
		$ArrivalID = $RequestBody['ArrivalID'];
		$BreakUpID=$RequestBody['BreakUpID'];
		$existingBreakup = $this->auditLogger->fetchRowById('RmArrivalBreakUpDetails', 'ArrivalBrkUpID', $BreakUpID);


		$sqlQuery="delete  from `RmArrivalBreakUpDetails` where ArrivalBrkUpID='$BreakUpID'";																	
		$stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
		if ($stmt && $existingBreakup != null) {
			$this->auditLogger->logDelete('RmArrivalBreakUpDetails', $BreakUpID, $existingBreakup, $RequestBody, [
				'ReferenceNo' => $ArrivalID,
				'Remarks' => 'Arrival balance breakup deleted',
			]);
		}


		$ArrivalID = $RequestBody['ArrivalID'];
		$BreakUpData = $this->getArrivalBreakUp($ArrivalID);
		$ArrivalData = $this->getArrivalData($ArrivalID);
		$GrandTotal = $this->getGrandTotal($ArrivalID);
		$overAllData = $this->getOverAllArrival($ArrivalID);


		$ArrivalData['OverAllWeight']=$overAllData['OverAllWeight'];
		$ArrivalData['OverAllAmount']=$overAllData['OverAllAmount'];
		$ArrivalData['OverAllBalWeight']=$overAllData['OverAllBalWeight'];
		$ArrivalData['OverAllBalAmount']=$overAllData['OverAllBalAmount'];
		

		$Data['Error'] = null;
		$Data['Msg'] = 'Deleted Posted Successful';
		$Data['BreakUpData'] = $BreakUpData;
		$Data['ArrivalData'] = $ArrivalData;
		$Data['GrandTotal'] = $GrandTotal;

		return $Data;
	}

	public function postArrivalDetails($RequestBody)
	{

	//	var_dump($RequestBody);
		$PostedUserID = 1;
		$ArrivalNumber = $RequestBody['ArrivalNumber'];
		$ArrivalStationID = $RequestBody['ArrivalStationID'];
		$VehicalID = $RequestBody['VehicalID'];
		$AgentID = $RequestBody['AgentID'];
		$DriverID = $RequestBody['DriverID'];
		$GraderID = $RequestBody['GraderID'];
		 $ArrivalID = $RequestBody['ArrivalID'];
		$Species = $RequestBody['Species'];
		$RmSupplierID = $RequestBody['RmSupplierID'];
		$ProcessingStationID = $RequestBody['ProcessingStationID'];
		$Extra = $RequestBody['Extra'];
		$HarvestCommission = $RequestBody['HarvestCommission'];

		$PurchaseCategoryID = $RequestBody['PurchaseCategoryID'];

		$FarmAddress = $RequestBody['FarmAddress'];
		$Remarks = $RequestBody['Remarks'];
		$ArrivalDate = date("Y-m-d", strtotime($RequestBody['ArrivalDate']));
		$BillDate = date("Y-m-d", strtotime($RequestBody['BillDate']));


		if ($ArrivalID == '') {
			$ArrivalNumber = $this->utilController->getFormatedArrivalNumber($ArrivalStationID, $ArrivalNumber);
			$ArrivalNoAval = $this->utilController->checkArrivalNoAval($ArrivalDate, $ArrivalNumber, $ArrivalID);

			if (!$ArrivalNoAval) {
				$this->Error = "Arrival Number Already Exist";
			}
		}




		$AryCurrentDate = getCurrentDateTime();

		$CreatedDateTime = $AryCurrentDate['TimeStamp'];
		$Year = $AryCurrentDate['Year'];

		$stmt = false;

		if ($this->Error == null && $ArrivalID == '') {
			$sqlQuery = "INSERT INTO `RmArrivalDetatils`
				(`PostedUserID`, `Species`,`ArrivalNumber`,`ProcessingStationID`, 
				`ArrivalStationID`, `VehicalID`, `AgentID`,
				`DriverID`, `GraderID`, `PurchaseCategoryID`,`RmSupplierID`,`Extra`,
			    `FarmAddress`,`Remarks`,`CreatedDateTime`, `ArrivalDate`, 
				`LotStatus`,`HarvestCommission`,`BillDate`) VALUES  
				('$PostedUserID','$Species','$ArrivalNumber','$ProcessingStationID',	
				'$ArrivalStationID','$VehicalID','$AgentID',
				'$DriverID','$GraderID',
				'$PurchaseCategoryID','$RmSupplierID','$Extra',
				'$FarmAddress','$Remarks','$CreatedDateTime','$ArrivalDate',
				'1','$HarvestCommission','$BillDate')";

			$stmt = $this->conn->prepare($sqlQuery); 
			$stmt->execute();
			$ArrivalID = $this->conn->lastInsertId();
			if ($stmt) {
				$newArrival = $this->auditLogger->fetchRowById('RmArrivalDetatils', 'RmArrivalID', $ArrivalID);
				$this->auditLogger->logInsert('RmArrivalDetatils', $ArrivalID, $newArrival, $RequestBody, [
					'ReferenceNo' => $ArrivalNumber,
					'Remarks' => 'Arrival balance created',
				]);
			}

		} else if ($ArrivalID != '') {
			$existingArrival = $this->auditLogger->fetchRowById('RmArrivalDetatils', 'RmArrivalID', $ArrivalID);
			$sqlQuery = "Update `RmArrivalDetatils` set
				`RmSupplierID`='$RmSupplierID',
				`Extra`='$Extra',
				 `ArrivalNumber`='$ArrivalNumber',
				 `ProcessingStationID`='$ProcessingStationID',
				 `Species`='$Species',
				`ArrivalStationID`='$ArrivalStationID',
				 `VehicalID`='$VehicalID',
				 `Remarks`='$Remarks',
				 `AgentID`='$AgentID',
				 `DriverID`='$DriverID',
				`GraderID`='$GraderID',
				`PurchaseCategoryID`='$PurchaseCategoryID',
			    `FarmAddress`='$FarmAddress',
				`CreatedDateTime`='$CreatedDateTime',
				`ArrivalDate`='$ArrivalDate', 
				`HarvestCommission`='$HarvestCommission', 
				`BillDate`='$BillDate', 
				`IsEdited`=1 where 	RmArrivalID ='$ArrivalID'";

			$stmt = $this->conn->prepare($sqlQuery);
			$stmt->execute();
			if ($stmt) {
				$updatedArrival = $this->auditLogger->fetchRowById('RmArrivalDetatils', 'RmArrivalID', $ArrivalID);
				$this->auditLogger->logUpdate('RmArrivalDetatils', $ArrivalID, $existingArrival, $updatedArrival, $RequestBody, [
					'ReferenceNo' => $ArrivalNumber,
					'Remarks' => 'Arrival balance updated',
				]);
			}


		}



		if ($stmt) {

			$this->postArrivalBreakUp($ArrivalID, $RequestBody);
			$RequestBody['ArrivalID']=$ArrivalID;
			$BreakUpData = $this->getArrivalDetails($RequestBody);
			return $this->getArrivalDetails($RequestBody);
		}else{
			return $Data = array('Error' => $this->Error,"Msg" =>$this->Error);
		}
	}

	function postArrivalBreakUp($ArrivalID, $RequestBody)
	{
		$Variety = $RequestBody['Variety'];
		$ArrivalCount = $RequestBody['ArrivalCount'];
		$ArrivalWeight = $RequestBody['ArrivalWeight'];
		$ArrivalRate = $RequestBody['ArrivalRate'];

		$ArrivalCount = 0;
		$ArrivalWeight = 0;
		$ArrivalRate = 0;
		if (isset($RequestBody['Variety'])) {
			$Variety = $RequestBody['Variety'];
		}
		if (isset($RequestBody['ArrivalCount'])) {
			$ArrivalCount = $RequestBody['ArrivalCount'];
		}
		if (isset($RequestBody['ArrivalWeight'])) {
			$ArrivalWeight = $RequestBody['ArrivalWeight'];
		}
		if (isset($RequestBody['ArrivalRate'])) {
			$ArrivalRate = $RequestBody['ArrivalRate'];
		}

		if ($ArrivalCount > 0 && $ArrivalWeight > 0 && $ArrivalRate > 0) {

			$sqlQuery = "INSERT INTO `RmArrivalBreakUpDetails`
				( `ArrivalID`, `Variety`,`Count`, `Weight`, `Rate`) VALUES  
				('$ArrivalID','$Variety','$ArrivalCount','$ArrivalWeight','$ArrivalRate')";

			$stmt = $this->conn->prepare($sqlQuery);
			$stmt->execute();
			if ($stmt) {
				$newBreakupId = $this->conn->lastInsertId();
				$newBreakup = $this->auditLogger->fetchRowById('RmArrivalBreakUpDetails', 'ArrivalBrkUpID', $newBreakupId);
				$this->auditLogger->logInsert('RmArrivalBreakUpDetails', $newBreakupId, $newBreakup, $RequestBody, [
					'ReferenceNo' => $ArrivalID,
					'Remarks' => 'Arrival balance breakup created',
				]);
			}
		}

	}


	function getArrivalData($ArrivalID)
	{
		$dataRow=null;
		$sqlFields = "`RmArrivalID`, `PostedUserID`, `Species`,`Remarks`,`Extra`,`RmSupplierID`,`ProcessingStationID`,`ArrivalNumber`,`BillDate`,
		 `ArrivalStationID`, `VehicalID`, `AgentID`, `DriverID`, `GraderID`,
         `PurchaseCategoryID`, `FarmAddress`, `CreatedDateTime`, `ArrivalDate`, `IsEdited`, `HarvestCommission`,`LotStatus`";
		$sqlQuery = "SELECT  $sqlFields FROM `RmArrivalDetatils` WHERE  RmArrivalID='$ArrivalID'";
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();
		$dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
		return $dataRow;
	}

	function getArrivalBreakUp($ArrivalID)
	{

		$sqlQuery = "SELECT  HarvestCommission FROM `RmArrivalDetatils` WHERE  RmArrivalID='$ArrivalID'";
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();
		$dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
		$HarvestCommission=$dataRow['HarvestCommission'];
		
		
		$Data=null;
		$ArrivalData=$this->getArrivalData($ArrivalID);
		$Species=$ArrivalData['Species'];
		$ArrivalDate=$ArrivalData['ArrivalDate'];

		$sqlFields = "`ArrivalBrkUpID`, `ArrivalID`, `Variety`,`Count`, `Weight`, `Rate`, `BrkUpStatus`";
		$sqlQuery = "select $sqlFields  from `RmArrivalBreakUpDetails` where ArrivalID='$ArrivalID'";
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();
		$Data = array();
		while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
			$AryPriceData=$this->utilController->getRmPrice($Species, $ArrivalDate,$dataRow['Count']);
			$dataRow['MasterRate']=isset($AryPriceData['RmPrice']) ? $AryPriceData['RmPrice']:0;
			$dataRow['BreakupTotal']=round($dataRow['Weight']*$dataRow['Rate'],2);
			$dataRow['Count']=sprintf('%0.2f',($dataRow['Count']));
			$dataRow['Rate']=sprintf('%0.2f',($dataRow['Rate']));
			$dataRow['HarvestCommission']=$HarvestCommission.' ('.round($dataRow['Weight']*$HarvestCommission,2).')';
			$Data[] = $dataRow;
		}
		return $Data;
	}
	
	
	function getGrandTotal($ArrivalID){
		$Data=array();
		 $sqlFields="sum(Weight) as WeightTotal ,sum(Weight*Rate) as ValueTotal";
            $sqlQuery="select $sqlFields  from `RmArrivalBreakUpDetails` where ArrivalID='$ArrivalID'";																	
				$stmt = $this->conn->prepare($sqlQuery);
                $stmt->execute();
				$dataRow = $stmt->fetch(PDO::FETCH_ASSOC);

				return $dataRow;
    }


	function getOverAllArrival($dataRow){

		$responseData=array();

	

		$OverallArrivalID=$dataRow['OverallArrivalID'];//
		$ArrivalStationID=$dataRow['CenterID'];
		$ProcessingStationID=$dataRow['PlantID'];
		$Species=$dataRow['Species'];
		$ArrivalDate=$dataRow['ArrivalDate'];
/*
	echo 	$sqlQuery="SELECT TotalAmount,TotalWeight FROM `RmOverAllArrival` WHERE 
		CompanyID='$ProcessingStationID' and PlantID='$ArrivalStationID' 
		and Species='$Species' and ArrivalDate='$ArrivalDate'";																	
$stmt = $this->conn->prepare($sqlQuery);
$stmt->execute();
$dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
*/
		 if($OverallArrivalID!=''){
			$sqlQuery="SELECT CenterID,PlantID,Species,TotalAmount,TotalWeight FROM `RmOverAllArrival` WHERE 
			ID='$OverallArrivalID'"; 

		 }
		 else{
         $sqlQuery="SELECT CenterID,PlantID,Species,TotalAmount,TotalWeight FROM `RmOverAllArrival` WHERE 
									CenterID='$ArrivalStationID' and PlantID='$ProcessingStationID' 
									and Species='$Species' and ArrivalDate='$ArrivalDate'";	
									
									// $sqlQuery="SELECT CenterID,PlantID,Species,TotalAmount,TotalWeight FROM `RmOverAllArrival` WHERE 
									// CompanyID='$ProcessingStationID' and PlantID='$ArrivalStationID' 
									// and Species='$Species' and ArrivalDate='$ArrivalDate'";	
		 }	

		// echo $sqlQuery;
		 															
				$stmt = $this->conn->prepare($sqlQuery);
                $stmt->execute();
				$dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
				$responseData['OverAllWeight']=$dataRow['TotalWeight'];
				$responseData['OverAllAmount']=$dataRow['TotalAmount'];
				$responseData['CenterID']=$dataRow['CenterID'];
				$responseData['ArrivalPlantID']=$dataRow['PlantID'];
				$responseData['Species']=$dataRow['Species'];

		$sqlQuery="SELECT sum(RABD.Weight) as RcvdWeight,sum(RABD.Weight*RAD.HarvestCommission) as HarvestCommission,
		sum(RABD.Weight*RABD.Rate) as BillAmount FROM `RmArrivalDetatils`  as RAD left JOIN RmArrivalBreakUpDetails as RABD on RAD.RmArrivalID=RABD.ArrivalID
		WHERE  ProcessingStationID= '$ProcessingStationID' and ArrivalStationID='$ArrivalStationID' and Species='$Species' and ArrivalDate='$ArrivalDate'";																	
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();

		$dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
		$responseData['OverAllBalWeight']=$responseData['OverAllWeight']- $dataRow['RcvdWeight'];
		$responseData['OverAllBalAmount']=$responseData['OverAllAmount']- ($dataRow['HarvestCommission']+ $dataRow['BillAmount']);

				return $responseData;
    }
}
?>
