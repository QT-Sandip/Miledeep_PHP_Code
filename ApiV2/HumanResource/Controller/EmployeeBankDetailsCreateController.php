<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class EmployeeBankDetailsCreateController
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

    public function getEmployeeBankDetails($RequestBody)
    {
        if( isset($RequestBody['accountNumber']) && $RequestBody['accountNumber']>0 ){

            $sqlQuery = "SELECT count('BankAccID') as cnt
            FROM `HrmEmpBankAccountDetails`
            WHERE AccNumber='".$RequestBody['accountNumber']."' ";
            $stmt = $this->conn->prepare($sqlQuery);
            $stmt->execute();
            $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
            if($dataRow['cnt']){
                if(isset($RequestBody['breakupId']) && $RequestBody['breakupId']){
                    $sqlQuery = "SELECT *
                    FROM `HrmEmpBankAccountDetails`
                    WHERE BankAccID='".$RequestBody['breakupId']."' ";
                    $stmt = $this->conn->prepare($sqlQuery);
                    $stmt->execute();
                    $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
                    if( $dataRow['AccNumber'] == $RequestBody['accountNumber'] ){
                        $EmployeeBankDetails = 0;
                    }else{
                        $EmployeeBankDetails = 1;
                    }
                }else{
                    $EmployeeBankDetails = 1;
                }
            }else{
                $EmployeeBankDetails = 0;
            }
            $Data['Error'] = null;
            $Data['Msg'] = 'Success';

            $Data['Data'] = $EmployeeBankDetails;

            return $Data;
        }else{
            $EmployeeID = $RequestBody['EmployeeID'];
            $EmployeeBankDetails = $this->getEmployeeBankData($EmployeeID);



            $Data['Error'] = null;
            $Data['Msg'] = 'Success';

            $Data['Data'] = $EmployeeBankDetails;

            return $Data;
        }
    }

    // public function postDeleteDeheading($RequestBody)
    // {
    //  $ArrivalID = $RequestBody['ArrivalID'];
    //  $BreakUpID = $RequestBody['BreakUpID'];


    //  $sqlQuery = "DELETE FROM `PpcDeheading` WHERE DHID='$BreakUpID'";
    //  $stmt = $this->conn->prepare($sqlQuery);
    //  $stmt->execute();



    //  $ArrivalID = $RequestBody['ArrivalID'];

    //  $DeheadingData = $this->getDeheadingData($ArrivalID);



    //  $Data['Error'] = null;
    //  $Data['Msg'] = 'Deleted  Successfully';
    //  $Data['Data'] = $DeheadingData;


    //  return $Data;
    // }

    public function postEmployeeBankDetails($RequestBody)
    {


        //$PostedUserID = 1;

        $EmployeeID = $RequestBody['EmployeeID'];
        $BankID = $RequestBody['BankID'];
        $AccountNumber = $RequestBody['AccountNumber'];
        $Ifsc = $RequestBody['Ifsc'];
        $AccountType = $RequestBody['AccountType'];
        $DateOfCreation = $RequestBody['DateOfCreation'];
        $BreakUpId = $RequestBody['BreakUpId'];


        $sqlBioMetricID = "SELECT BioMetricID FROM `HrmEmployeeDetails` WHERE EmpID='$EmployeeID'";
        $stmtBioMetricID = $this->conn->prepare($sqlBioMetricID);
        $stmtBioMetricID->execute();
        $dataBioMetricID = $stmtBioMetricID->fetch(PDO::FETCH_ASSOC);
        $BioMetricID = $dataBioMetricID['BioMetricID'];
        // $AryCurrentDate = getCurrentDateTime();

        // $CreatedDateTime = $AryCurrentDate['TimeStamp'];

if($BreakUpId==''){
        $sqlCheckRecord = "SELECT count(*) as Count FROM `HrmEmpBankAccountDetails` WHERE EmpID='$EmployeeID' and BankID='$BankID' and AccNumber='$AccountNumber'";
        $stmtCheckRecord = $this->conn->prepare($sqlCheckRecord);
        $stmtCheckRecord->execute();
        $dataRowCheckRecord = $stmtCheckRecord->fetch(PDO::FETCH_ASSOC);
        $Count = $dataRowCheckRecord['Count'];



        if ($Count == 0 ) {
            $stmt = false;


            $sqlQuery = "INSERT INTO `HrmEmpBankAccountDetails`( `EmpID`, `EmpBioMetricID`, `AccNumber`, `BankID`, `IFSC`, `AccType`, `DateOfCreation`)
              VALUES ('$EmployeeID','$BioMetricID','$AccountNumber','$BankID','$Ifsc','$AccountType','$DateOfCreation')";

            $stmt = $this->conn->prepare($sqlQuery);
            $stmt->execute();


            if ($stmt) {

                $newBankId = $this->conn->lastInsertId();
                $newBankData = $this->auditLogger->fetchRowById('HrmEmpBankAccountDetails', 'BankAccID', $newBankId);
                $this->auditLogger->logInsert('HrmEmpBankAccountDetails', $newBankId, $newBankData, $RequestBody, [
                    'ReferenceNo' => $AccountNumber,
                    'Remarks' => 'Employee bank account added',
                ]);

                $EmployeeBankDetails = $this->getEmployeeBankData($EmployeeID);
                $Data['Error'] = null;
                $Data['Msg'] = 'Success';
                $Data['Data'] = $EmployeeBankDetails;
                return $Data;
            }
            // else {
            //     return $Data = array('Error' => $this->Error, "Msg" => $this->Error);
            // }
        } else {
            $EmployeeBankDetails = $this->getEmployeeBankData($EmployeeID);
            $Data['Error'] = null;
            $Data['Msg'] = 'Data Already Exists';
            $Data['Data'] = $EmployeeBankDetails;
            return $Data;
        }

    }
else{

    $existingBank = $this->auditLogger->fetchRowById('HrmEmpBankAccountDetails', 'BankAccID', $BreakUpId);

   $sqlQuery = "  UPDATE `HrmEmpBankAccountDetails` SET `EmpID`='$EmployeeID',`EmpBioMetricID`='$BioMetricID',`AccNumber`='$AccountNumber',
   `BankID`='$BankID',`IFSC`='$Ifsc',`AccType`='$AccountType',`DateOfCreation`='$DateOfCreation' WHERE `BankAccID`='$BreakUpId'";

  $stmt = $this->conn->prepare($sqlQuery);
  $stmt->execute();


  if ($stmt) {

      $updatedBank = $this->auditLogger->fetchRowById('HrmEmpBankAccountDetails', 'BankAccID', $BreakUpId);
      $this->auditLogger->logUpdate('HrmEmpBankAccountDetails', $BreakUpId, $existingBank, $updatedBank, $RequestBody, [
          'ReferenceNo' => $AccountNumber,
          'Remarks' => 'Employee bank account updated',
      ]);

      $EmployeeBankDetails = $this->getEmployeeBankData($EmployeeID);
      $Data['Error'] = null;
      $Data['Msg'] = 'Success';
      $Data['Data'] = $EmployeeBankDetails;
      return $Data;
  }
}

    }



    function getEmployeeBankData($EmployeeID)
    {


        $dataRow = null;
        $sqlFields = "EmpName,`BankAccID`, `EmpBioMetricID`, `AccNumber`, ID as BankID, `IFSC`, `AccType`, HBD.`DateOfCreation`,BankName";

        $sqlQuery = "SELECT $sqlFields
        FROM `HrmEmpBankAccountDetails` as HBD 
        left join HrmEmployeeDetails HED on HED.EmpID=HBD.EmpID 
        left join HrmBanksMaster HBM on HBM.ID=HBD.BankID
        WHERE HBD.EmpID='$EmployeeID' ORDER BY `BankAccID` ASC";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        $i = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            //$Yield = round((($dataRow['WtAfterDH'] / $dataRow['HonWeight']) * 100), 2);

            $EmployeeBankDetails['SNo'] = ++$i;
            $EmployeeBankDetails['BreakupID'] = $dataRow['BankAccID'];
            $EmployeeBankDetails['EmployeeName'] = $dataRow['EmpName'];
            $EmployeeBankDetails['BiometricID'] = $dataRow['EmpBioMetricID'];
            $EmployeeBankDetails['AccountNumber'] = $dataRow['AccNumber'];
            $EmployeeBankDetails['BankID'] = $dataRow['BankID'];
            $EmployeeBankDetails['Ifsc'] = $dataRow['IFSC'];
            $EmployeeBankDetails['AccountType'] = $dataRow['AccType'];
            $EmployeeBankDetails['BankName'] = $dataRow['BankName'];           
            $EmployeeBankDetails['DateOfCreation'] = $this->utilController->getFormatedDate($dataRow['DateOfCreation']);


            $ArrayData[] = $EmployeeBankDetails;
           

        }
     
        $Data['EmployeeBankDetails'] = $ArrayData;
     

        return $Data;

    }

}
?>
