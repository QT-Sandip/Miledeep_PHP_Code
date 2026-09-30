<?php
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/
include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class EmployeeCreationController
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

	public function getEmployeeCreationDetails($RequestBody)
	{
		$EmployeeID = $RequestBody['EmployeeID'];
		$EmployeeData = $this->getEmployeeCreationData($EmployeeID);

		$Data['Error'] = null;
		$Data['Msg'] = 'Success';

		$Data['Data'] = $EmployeeData;

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

	public function postEmployeeCreatioDetails($RequestBody)
	{

		//	var_dump($RequestBody);
		// $PostedUserID = 1;
		$EmployeeID = $RequestBody['EmployeeID'];



		///max employees checking
		if ($EmployeeID == '') {
			 $IsMax = $this->getMaxEmployeeLimit();
			if ($IsMax!=0) {
				$Data['Error'] = null;
				$Data['Msg'] = 'Max Limit Reached';
				//return $Data = array('Error' => $this->Error, "Msg" => $this->Error);

			} else {
				//echo 'i'; exit;
				//$InsertEmployeeDetails = $this->postEmployeeDetails($RequestBody);
				$Msg=$this->postEmployeeDetails($RequestBody);
				$Data['Error'] = null;
				$Data['Msg'] = $Msg;
				//return $Data = array('Error' => $this->Error, "Msg" => $this->Error);


			}


		}
		else {
			$this->postEmployeeDetails($RequestBody);
			$Data['Error'] = null;
				$Data['Msg'] = 'Updated Successfully';
				// $Data = array('Error' => $this->Error, "Msg" => $this->Error);
				//return $Data = array('Error' => $this->Error, "Msg" => $this->Error);



		}
return $Data;

	}



	function getEmployeeCreationData($EmployeeID)
	{


		$dataRow = null;

		$sqlSalaryElementsQuery = "SELECT   `ElementID`  , `ElementValue` as Value FROM `HrmSalaryElementsValues`
		WHERE `EmpID`='$EmployeeID'";
	   $stmtSalaryElements = $this->conn->prepare($sqlSalaryElementsQuery);
	   $stmtSalaryElements->execute();
	   while($rowSalaryElements =  $stmtSalaryElements->fetch(PDO::FETCH_ASSOC)){
		   $ArraySalaryData[]=$rowSalaryElements;
	   }
	  // print_r($ArraySalaryData); exit;

		$sqlFields = "BranchID,DeptID,SubDeptID,BioMetricID,EmpName,ContractorID,EmpGender,EmpCategory,EmpSubCategory,
		DesigID,ShiftID,JoiningDate,LeavingDt,LeavingReason,Rejoinee,PrevEmpID,EmpCategory,BloodGroup,FatherName,SpouseName,Grade,Division,
		MaritalStatus,PhoneNumber,AltPhoneNumber,PresentAddress,PermanentAddress,DateOfBirth,AadharNumber,PANNo,SalaryStructure,
		SalaryCalculateFrom,ESIApplicable,ESIDispensary,PFApplicable,PFNoforDeptFile,RestrictPF,ZeroPension,ZeroPT,UANNumber,WashingAllowance
		,PFJoinDt,ESICRegDt,CTC,Basic,TDSApplicable ";

		$sqlQuery = "SELECT  $sqlFields FROM `HrmEmployeeDetails` as HED
		 left join HrmEmpPersonalDetails as HPD on HPD.EmpID=HED.EmpID
		 left join HrmEmpSalaryDetails as HSD on HSD.EmpID=HED.EmpID
		 where HED.EmpID='$EmployeeID'";
		$stmt = $this->conn->prepare($sqlQuery);
		$stmt->execute();


		$i = 0;
		$dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
		//while ()) {


			$EmployeeData['SNo'] = ++$i;
			$EmployeeData['BranchID'] = $dataRow['BranchID'];
			$EmployeeData['DepartmentID'] = $dataRow['DeptID'];
			//$EmployeeData['SubDepartmentID'] = $dataRow['SubDeptID'];
			$EmployeeData['BioMetricID'] = $dataRow['BioMetricID'];
			$EmployeeData['EmployeeName'] = $dataRow['EmpName'];
			$EmployeeData['ContractorID'] = $dataRow['ContractorID'];
			$EmployeeData['EmpGender'] = $dataRow['EmpGender'];
			//$EmployeeData['EmpCategory'] = $dataRow['EmpCategory'];
			$EmployeeData['EmpSubCategory'] = $dataRow['EmpSubCategory'];
			$EmployeeData['DesigID'] = $dataRow['DesigID'];
			$EmployeeData['ShiftID'] = $dataRow['ShiftID'];
			$EmployeeData['JoiningDate'] =$dataRow['JoiningDate'];// $this->utilController->getFormatedDate($dataRow['JoiningDate']);
			$EmployeeData['BloodGroup'] = $dataRow['BloodGroup'];
			$EmployeeData['FatherName'] = $dataRow['FatherName'];
			$EmployeeData['SpouseName'] = $dataRow['SpouseName'];
			$EmployeeData['Grade'] = $dataRow['Grade'];
			$EmployeeData['Division'] = $dataRow['Division'];
			$EmployeeData['SalaryStructure'] = $dataRow['SalaryStructure'];
			$EmployeeData['SalaryCalculateFrom'] = $dataRow['SalaryCalculateFrom'];//$this->utilController->getFormatedDate($dataRow['SalaryCalculateFrom']);
			$EmployeeData['LeavingDt'] = $dataRow['LeavingDt'];
			$EmployeeData['LeavingReason'] = $dataRow['LeavingReason'];
			$EmployeeData['ESIApplicable'] = $dataRow['ESIApplicable'];
			$EmployeeData['ESIDispensary'] = $dataRow['ESIDispensary'];
			$EmployeeData['PFApplicable'] = $dataRow['PFApplicable'];
			$EmployeeData['PFNoforDeptFile'] = $dataRow['PFNoforDeptFile'];
			$EmployeeData['RestrictPF'] = $dataRow['RestrictPF'];
			$EmployeeData['ZeroPension'] = $dataRow['ZeroPension'];
			$EmployeeData['ZeroPT'] = $dataRow['ZeroPT'];
			$EmployeeData['UANNumber'] = $dataRow['UANNumber'];
			$EmployeeData['Rejoinee'] = $dataRow['Rejoinee'];
			$EmployeeData['PrevEmployeeIDReferenceNO'] = $dataRow['PrevEmpID'];
		//	$EmployeeData['WashingAllowance'] = $dataRow['WashingAllowance'];
			$EmployeeData['EmployeeCategory'] = $dataRow['EmpCategory'];
			$EmployeeData['PFDateofJoinining'] =$dataRow['PFJoinDt'];// $this->utilController->getFormatedDate($dataRow['PFJoinDt']);
			$EmployeeData['ESICRegistrationDate'] = $dataRow['ESICRegDt'];//$this->utilController->getFormatedDate($dataRow['ESICRegDt']);
			$EmployeeData['CTC'] = $dataRow['CTC'];
			$EmployeeData['GrossSalary'] = $dataRow['Basic'];
			$EmployeeData['TDSApplicable'] = $dataRow['TDSApplicable'];
			$EmployeeData['MaritalStatus'] = $dataRow['MaritalStatus'];
			$EmployeeData['PhoneNumber'] = $dataRow['PhoneNumber'];
			$EmployeeData['AlternatePhoneNumber'] = $dataRow['AltPhoneNumber'];
			$EmployeeData['PresentAddress'] = $dataRow['PresentAddress'];
			$EmployeeData['PermanentAddress'] = $dataRow['PermanentAddress'];
			$EmployeeData['DateOfBirth'] =$dataRow['DateOfBirth'];// $this->utilController->getFormatedDate($dataRow['DateOfBirth']);
			$EmployeeData['AadharNumber'] = $dataRow['AadharNumber'];
			$EmployeeData['PanNumber'] = $dataRow['PANNo'];




			//$ArrayData[] =;
		//}

		$Data['EmployeeData'] =  $EmployeeData;
		$Data['SalaryStructureObj'] =  $ArraySalaryData;


		return $Data;

	}


	function getMaxEmployeeLimit()
	{
		 $SqlAdminConfig = "SELECT MaxEmpoyees,
		(Select count(*) from HrmEmployeeDetails ) as PresentEmployees
		FROM `AdminConfigDetails`";

		$StmtAdminConfig = $this->conn->prepare($SqlAdminConfig);
		$StmtAdminConfig->execute();
		$RowAdminConfig = $StmtAdminConfig->fetch(PDO::FETCH_ASSOC);


		$MaxEmpoyees = $RowAdminConfig['MaxEmpoyees'];
		$PresentEmployees = $RowAdminConfig['PresentEmployees'];
		if ($PresentEmployees >= $MaxEmpoyees) {

			$IsMax =1;
		} else {
			//echo "hi"; exit;
			$IsMax = 0;
		}
		//echo $IsMax; exit;
		return $IsMax;
	}

	//function postEmployeeDetails($RequestBody,$EmployeeID){
	function postEmployeeDetails($RequestBody)
	{


		 $EmployeeID = $RequestBody['EmployeeID'];
		$BranchID = $RequestBody['BranchID'];
		$DeptID = $RequestBody['DepartmentID'];
		//$SubDeptID = $RequestBody['SubDepartmentID'];
		$BioMetricID = $RequestBody['BiometricId'];
		$EmpName = $RequestBody['EmployeeName'];
		$ContractorID = $RequestBody['ContractorNameID'];
		$EmpGender = $RequestBody['GenderID'];
		$EmpCategory = $RequestBody['CategoryID'];
		$EmpSubCategory = $RequestBody['SubCategoryID'];
		$DesigID = $RequestBody['DesignationID'];
		$ShiftID = $RequestBody['ShiftCategoryID'];
		$JoiningDate = $RequestBody['JoiningDate'];
		$Rejoinee= $RequestBody['ReJoinee'];
		$PrevEmpID= $RequestBody['PrevEmployeIDRefno'];



		$SqlEmployeeDetails = "Select count(*) as BioCount from HrmEmployeeDetails where BioMetricID='$BioMetricID'";
		$StmtEmployeeDetails = $this->conn->prepare($SqlEmployeeDetails);
		$StmtEmployeeDetails->execute();
		$RowEmployeeDetails = $StmtEmployeeDetails->fetch(PDO::FETCH_ASSOC);
		 $BioCount = $RowEmployeeDetails['BioCount'];


		$DateOfCreation = date('Y-m-d');




		if ($EmployeeID == '') {
			if ($BioCount == 0) {
				$SqlNewEmployee = "INSERT INTO `HrmEmployeeDetails`( `BioMetricID`, `DeptID`, `EmpName`, `EmpGender`, `EmpCategory`,
		`EmpSubCategory`, `DesigID`, `ShiftID`, `DateOfCreation`, `JoiningDate`, `EmpStatus`, `BranchID`,  `ContractorID`,
		 `Rejoinee`, `PrevEmpID`)
		VALUES('$BioMetricID','$DeptID','$EmpName','$EmpGender','$EmpCategory','$EmpSubCategory','$DesigID',
		'$ShiftID','$DateOfCreation','$JoiningDate','1','$BranchID','$ContractorID','$Rejoinee','$PrevEmpID')";
				$StmtNewEmployee = $this->conn->prepare($SqlNewEmployee);
				$StmtNewEmployee->execute();
				if ($StmtNewEmployee) {
					$EmployeeID = $this->conn->lastInsertId();

				 $this->postEmployeePersonalDetails($RequestBody, $EmployeeID);
					$this->postEmployeeSalaryDetails($RequestBody, $EmployeeID);

					$newEmployee = $this->auditLogger->fetchRowById('HrmEmployeeDetails', 'EmpID', $EmployeeID);
					$this->auditLogger->logInsert('HrmEmployeeDetails', $EmployeeID, $newEmployee, $RequestBody, [
						'ReferenceNo' => $EmpName,
						'Remarks' => 'Employee created',
					]);

					$Data = 'Employee added successfully';

				} else {
					$Data = "Unable To Create Employee";

				}

			} else {
				$Data = "Biometric Id Alreay Exists";

			}
		} else {


			$existingEmployee = $this->auditLogger->fetchRowById('HrmEmployeeDetails', 'EmpID', $EmployeeID);

			 $SqlNewEmployee = "UPDATE `HrmEmployeeDetails` SET `BioMetricID`='$BioMetricID',`DeptID`='$DeptID',
			`EmpName`='$EmpName',`EmpGender`='$EmpGender',`EmpCategory`='$EmpCategory',`EmpSubCategory`='$EmpSubCategory',`DesigID`='$DesigID',
			`ShiftID`='$ShiftID',`DateOfCreation`='$DateOfCreation',`JoiningDate`='$JoiningDate',`BranchID`='$BranchID',
			`Rejoinee`='$Rejoinee', `PrevEmpID`='$PrevEmpID',
			`ContractorID`='$ContractorID' WHERE `EmpID`='$EmployeeID'";

			$StmtNewEmployee = $this->conn->prepare($SqlNewEmployee);
			$StmtNewEmployee->execute();

			if ($StmtNewEmployee) {
				$this->postEmployeePersonalDetails($RequestBody, $EmployeeID);
					$this->postEmployeeSalaryDetails($RequestBody, $EmployeeID);

				$updatedEmployee = $this->auditLogger->fetchRowById('HrmEmployeeDetails', 'EmpID', $EmployeeID);
				$this->auditLogger->logUpdate('HrmEmployeeDetails', $EmployeeID, $existingEmployee, $updatedEmployee, $RequestBody, [
					'ReferenceNo' => $EmpName,
					'Remarks' => 'Employee updated',
				]);

				$Data = 'Employee Details Updated successfully';
			
			} else {
				$Data = "Unable To Update Employee Details";
				
			}

		}

		return $Data;
	}


	function postEmployeePersonalDetails($RequestBody, $EmployeeID)
	{

		$BloodGroup = $RequestBody['BloodGroupID'];
		$FatherName = $RequestBody['FatherName'];
		$SpouseName = $RequestBody['SpouseName'];
		$Grade = $RequestBody['Grade'];
		$Division = $RequestBody['Division'];
		$MaritalStatus = $RequestBody['MaritalStatusID'];
		$PhoneNumber = $RequestBody['PhoneNumber'];
		$AltPhoneNumber = $RequestBody['AlternatePhoneNumber'];
		$PresentAddress = $RequestBody['PresentAddress'];
		$PermanentAddress = $RequestBody['PermanentAddress'];
		$DateOfBirth = $RequestBody['DOB'];
		$AadharNumber = $RequestBody['AadharNumber'];
		$PANNo = $RequestBody['PANNumber'];
		//$EmployeeID = $RequestBody['EmployeeID'];



		$SqlEmpDetails = "SELECT count(*) as PersonalDetailsCount FROM `HrmEmpPersonalDetails` WHERE EmpID='$EmployeeID'";
		$StmtEmpDetails = $this->conn->prepare($SqlEmpDetails);
		$StmtEmpDetails->execute();
		$RowEmployeeDetails = $StmtEmpDetails->fetch(PDO::FETCH_ASSOC);
		$PersonalDetailsCount = $RowEmployeeDetails['PersonalDetailsCount'];

		//$PersonalDetailsCount = $StmtEmpDetails->rowCount();
		/***Personal Details***/

		if ($PersonalDetailsCount == 0) {



			 $sqlInsertPersonalDetails = "INSERT INTO `HrmEmpPersonalDetails`(`EmpID`, `PhoneNumber`, `AltPhoneNumber`, `MaritalStatus`,
			 `PresentAddress`, `PermanentAddress`, `DateOfBirth`, `AadharNumber`,  `PANNo`, `BloodGroup`, `FatherName`, 
			 `SpouseName`, `Grade`, `Division`) VALUES  ('$EmployeeID','$PhoneNumber','$AltPhoneNumber','$MaritalStatus','$PresentAddress',
			 '$PermanentAddress','$DateOfBirth'
			 ,'$AadharNumber','$PANNo','$BloodGroup','$FatherName','$SpouseName','$Grade','$Division')";


		} else {
			 $sqlInsertPersonalDetails = "UPDATE `HrmEmpPersonalDetails` SET `PhoneNumber`='$PhoneNumber',
					`AltPhoneNumber`='$AltPhoneNumber',`MaritalStatus`='$MaritalStatus',`PresentAddress`='$PresentAddress',
					`PermanentAddress`='$PermanentAddress',`DateOfBirth`='$DateOfBirth',`AadharNumber`='$AadharNumber',
					`PANNo`='$PANNo' ,`BloodGroup`='$BloodGroup' ,`FatherName`='$FatherName' ,`SpouseName`='$SpouseName',
					`Grade`='$Grade' ,`Division`='$Division'	WHERE `EmpID`='$EmployeeID'";
		}

		$StmtInsertPersonalDetails = $this->conn->prepare($sqlInsertPersonalDetails);
		$StmtInsertPersonalDetails->execute();

	}



	function postEmployeeSalaryDetails($RequestBody, $EmployeeID)
	{
		$SalaryCalculateFrom = $RequestBody['SalaryCalculateID'];
		$ESIApplicable = $RequestBody['ESIApplicableID'];
		$ESIDispensary = $RequestBody['ESIDispensary'];
		$PFApplicable = $RequestBody['PFApplicableID'];
		$PFNoforDeptFile = $RequestBody['PFNoForDeptFile'];
		$RestrictPF = $RequestBody['RestrictPF'];
		$ZeroPension = $RequestBody['ZeroPension'];
		$ZeroPT = $RequestBody['ZeroPT'];
		$UANNumber = $RequestBody['UANNo'];
		//$WashingAllowance = $RequestBody['WashingAllowance'];
		$PFJoinDt = $RequestBody['PFDateOfJoining'];
		$ESICRegDt = $RequestBody['ESICRegistrationDate'];
		$CTC = $RequestBody['CTC'];
		$Basic = $RequestBody['GrossSalary'];
		$TDSApplicable = $RequestBody['TDSApplicable'];
		$SalaryStructure = $RequestBody['SalaryStructureID'];
		//$EmployeeID = $RequestBody['EmployeeID'];

		$SqlBioMetricID = "SELECT BioMetricID FROM `HrmEmployeeDetails` WHERE  EmpID= '$EmployeeID'";
		$StmtBioMetricID = $this->conn->prepare($SqlBioMetricID);
		$StmtBioMetricID->execute();
		$RowBioMetricID = $StmtBioMetricID->fetch(PDO::FETCH_ASSOC);
		$EmpBioMetricID = $RowBioMetricID['BioMetricID'];



		$SqlEmpSalaryDetails = "SELECT count(*) as SalaryDetailsCount  FROM `HrmEmpSalaryDetails` WHERE EmpID='$EmployeeID'";
		$StmtEmpSalaryDetails = $this->conn->prepare($SqlEmpSalaryDetails);
		$StmtEmpSalaryDetails->execute();
		$RowEmpSalaryDetails = $StmtEmpSalaryDetails->fetch(PDO::FETCH_ASSOC);
		$SalaryDetailsCount = $RowEmpSalaryDetails['SalaryDetailsCount'];

		//$SalaryDetailsCount = $StmtEmpSalaryDetails->rowCount();
		/***Personal Details***/

		if ($SalaryDetailsCount == 0) {



			$sqlInsertSalaryDetails = "INSERT INTO `HrmEmpSalaryDetails`( `EmpID`, `EmpBioMetricID`, `Basic`,  `SalaryStructure`, 
			`SalaryCalculateFrom`, `ESIApplicable`, `ESIDispensary`, `PFApplicable`, `PFNoforDeptFile`, `RestrictPF`, `ZeroPension`,
			 `ZeroPT`,  `UANNumber`, `PFJoinDt`, `ESICRegDt`, `CTC`, `TDSApplicable`) 
			 VALUES ('$EmployeeID','$EmpBioMetricID','$Basic','$SalaryStructure','$SalaryCalculateFrom','$ESIApplicable','$ESIDispensary',
			 '$PFApplicable','$PFNoforDeptFile', '$RestrictPF','$ZeroPension','$ZeroPT','$UANNumber','$PFJoinDt',
			 '$ESICRegDt','$CTC','$TDSApplicable' )";


		} else {
			$sqlInsertSalaryDetails = "UPDATE `HrmEmpSalaryDetails` SET `EmpBioMetricID`='$EmpBioMetricID',`Basic`='$Basic',
			`SalaryStructure`='$SalaryStructure',`SalaryCalculateFrom`='$SalaryCalculateFrom',`ESIApplicable`='$ESIApplicable',
			`ESIDispensary`='$ESIDispensary',`PFApplicable`='$PFApplicable',`PFNoforDeptFile`='$PFNoforDeptFile',
			`RestrictPF`='$RestrictPF',`ZeroPension`='$ZeroPension',`ZeroPT`='$ZeroPT',
			`UANNumber`='$UANNumber',`PFJoinDt`='$PFJoinDt',`ESICRegDt`='$ESICRegDt',`CTC`='$CTC',`TDSApplicable`='$TDSApplicable'
			 WHERE `EmpID`='$EmployeeID'";
		}

		$StmtInsertSalaryDetails = $this->conn->prepare($sqlInsertSalaryDetails);
		$StmtInsertSalaryDetails->execute();

		if($StmtInsertSalaryDetails){
			$this->postSalaryBreakUp($EmployeeID, $RequestBody);
		}
	}

	function postSalaryBreakUp($EmployeeID, $RequestBody)
    {
       // echo $PaymentID; exit;
        $AryError = NULL;
		$SalaryBreakUp= $RequestBody['SalaryStructureObj'];//need to update this data	
		// {
        //     "ElementID" : "",
        //      "Value": ""
        //  }	
		
      //  $PaymentBreakUp = $RequestBody['PaymentBreakUp'];


        foreach ($SalaryBreakUp as $Item) {
            //$BreakUpID = isset($Item['ElementID']) ? $Item['ElementID'] : null;

            $ElementID = $Item['ElementID']; 
            $Value = $Item['Value']; 
			$AryCurrentDate = getCurrentDateTime();

			$CreatedDateTime = $AryCurrentDate['TimeStamp'];

			 $SqlCount="SELECT count(*) as Count FROM `HrmSalaryElementsValues` WHERE EmpID='$EmployeeID' and ElementID='$ElementID'";
			$StmtCount = $this->conn->prepare($SqlCount);
			$StmtCount->execute(); 

			$RowCount = $StmtCount->fetch(PDO::FETCH_ASSOC);
		 $Count = $RowCount['Count'];


			
			if ($Count == 0) {

                $SqlSalaryBreakUp = "INSERT INTO `HrmSalaryElementsValues`( `EmpID`, `ElementID`, `ElementValue`, `CreatedAt`) 
				VALUES ('$EmployeeID','$ElementID','$Value','$CreatedDateTime') ";
            } else {

                $SqlSalaryBreakUp = "UPDATE `HrmSalaryElementsValues` SET `ElementValue`='$Value',
				`UpdatedAt`='$CreatedDateTime' WHERE `EmpID`='$EmployeeID'and `ElementID`='$ElementID'";
            }
			$StmtSalaryBreakUp= $this->conn->prepare($SqlSalaryBreakUp);
			$StmtSalaryBreakUp->execute(); 

         
           
        }

        if ($StmtSalaryBreakUp) {
			$Data = array('Error' => null, "Msg" => "Saved Successfully");

           

        } else {
			$Data = array('Error' => null, "Msg" => "Unable to Update");
        }

        return $Data;
    }

}
?>
