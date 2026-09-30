<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class FarmMasterController
{
    private $conn;
    private $utilController;
    private $Error = null;
    private $Msg = '';
    private $auditLogger;

    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->auditLogger = new CentralAuditLogger($this->conn, 'QualityControl');
        // $this->utilController = new UtilController($this->conn);
    }

    public function postFarmMasterDetails($RequestBody)
    {
        $FarmID = $RequestBody['FarmID'];
        $FarmName = $RequestBody['FarmName'];
        $FarmRegistrationNo = $RequestBody['FarmRegistrationNo'];
        $FarmerName = $RequestBody['FarmerName'];
        $FarmerAddress = $RequestBody['FarmerAddress'];
        $PhoneNumber = $RequestBody['PhoneNumber'];
        $TonnageValue = $RequestBody['TonnageValue'];
        $Status = $RequestBody['Status'];

        if ($FarmID == '') {
            // $sqlInsertCheck = "SELECT COUNT(*) AS InsertRowCount FROM QcAuditFarmMasters WHERE FarmName = '$FarmName'";
            // $stmtInsertCheck = $this->conn->prepare($sqlInsertCheck);
            // $stmtInsertCheck->execute();
            // $rowInsertCheck =  $stmtInsertCheck->fetch(PDO::FETCH_ASSOC);
            // $InsertRowCount = $rowInsertCheck['InsertRowCount'];
            // if ($InsertRowCount == 0) {
                $sqlInsert = "INSERT INTO QcAuditFarmMasters(FarmName, FarmRegistrationNo, FarmerName, FarmerAddress, PhoneNumber, TonnageValue, Status) 
                VALUES ('$FarmName', '$FarmRegistrationNo', '$FarmerName', '$FarmerAddress', '$PhoneNumber', '$TonnageValue', '$Status')";
                $stmtInsert = $this->conn->prepare($sqlInsert);
                $stmtInsert->execute();
                // $errorInfo = $stmtInsert->errorInfo();
                // var_dump($errorInfo);

                if ($stmtInsert) {
                    $lastInsertedId = $this->conn->lastInsertId();
                    $createdFarm = $this->auditLogger->fetchRowById('QcAuditFarmMasters', 'FarmID', $lastInsertedId);
                    $this->auditLogger->logInsert('QcAuditFarmMasters', $lastInsertedId, $createdFarm, $RequestBody, [
                        'ReferenceNo' => $lastInsertedId,
                        'Remarks' => 'QC farm master created',
                    ]);
                    $this->Error = null;
                    $this->Msg = 'Farm Posted Successfully';
                } else {
                    $this->Error = "Unable to Post Farm";
                    $this->Msg = "Unable to Post Farm";
                }
            // } else {
            //     $this->Error = "Farm Name already exist";
            //     $this->Msg = "Farm Name already exist";
            // }
        } else {
            // $sqlInsertCheck = "SELECT COUNT(*) AS InsertRowCount FROM QcAuditFarmMasters 
            // WHERE FarmName = '$FarmName' AND FarmID != '$FarmID'";
            // $stmtInsertCheck = $this->conn->prepare($sqlInsertCheck);
            // $stmtInsertCheck->execute();
            // $rowInsertCheck =  $stmtInsertCheck->fetch(PDO::FETCH_ASSOC);
            // $InsertRowCount = $rowInsertCheck['InsertRowCount'];
            // if ($InsertRowCount == 0) {
                $existingFarm = $this->auditLogger->fetchRowById('QcAuditFarmMasters', 'FarmID', $FarmID);
                $sqlUpdate = "UPDATE QcAuditFarmMasters SET FarmName='$FarmName',
                FarmRegistrationNo='$FarmRegistrationNo',FarmerName='$FarmerName',FarmerAddress='$FarmerAddress', 
                PhoneNumber='$PhoneNumber',TonnageValue='$TonnageValue',Status='$Status' 
                WHERE FarmID='$FarmID'";
                $stmtUpdate = $this->conn->prepare($sqlUpdate);
                $stmtUpdate->execute();
                // $errorInfo = $stmtUpdate->errorInfo();
                // var_dump($errorInfo);

                if ($stmtUpdate) {
                    $updatedFarm = $this->auditLogger->fetchRowById('QcAuditFarmMasters', 'FarmID', $FarmID);
                    $this->auditLogger->logUpdate('QcAuditFarmMasters', $FarmID, $existingFarm, $updatedFarm, $RequestBody, [
                        'ReferenceNo' => $FarmID,
                        'Remarks' => 'QC farm master updated',
                    ]);
                    $this->Error = null;
                    $this->Msg = 'Farm Updated Successfully';
                } else {
                    $this->Error = "Unable to Update Farm";
                    $this->Msg = "Unable to Update Farm";
                }
            // } else {
            //     $this->Error = "Farm Name already exist";
            //     $this->Msg = "Farm Name already exist";
            // }
        }

        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;

        return $Data;
    }

    function getFarmMasterData($RequestBody)
    {
        $FarmID = $RequestBody['FarmID'];
        $PageNo = $RequestBody['PageNo'];
        $SearchKey = $RequestBody['SearchKey'];
        $LoadAll = $RequestBody['LoadAll'];

        $Query = '';

        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query .= " and CONCAT_WS('',FarmID, FarmName, FarmRegistrationNo, FarmerName, FarmerAddress, PhoneNumber, TonnageValue, QAFM.Status,OptionName) LIKE '%$SearchKey%'";
        }

        $sqlCount = "SELECT COUNT(*) AS TotalCount FROM QcAuditFarmMasters AS QAFM
        LEFT JOIN QcAuditDropdownMasters AS QADM ON QADM.OptionID = QAFM.FarmName
        WHERE 1=1 $Query ";
        $stmtCount = $this->conn->prepare($sqlCount);
        $stmtCount->execute();
        $dataCount = $stmtCount->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataCount['TotalCount'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "FarmID, FarmName, FarmRegistrationNo, FarmerName, FarmerAddress, PhoneNumber, TonnageValue, QAFM.Status, OptionName";

        if ($FarmID) {
            $sqlSelect = "SELECT $sqlFields FROM QcAuditFarmMasters AS QAFM
            LEFT JOIN QcAuditDropdownMasters AS QADM ON QADM.OptionID = QAFM.FarmName
            WHERE FarmID = '$FarmID'";
        } else {
            $sqlSelect = "SELECT $sqlFields FROM QcAuditFarmMasters AS QAFM
            LEFT JOIN QcAuditDropdownMasters AS QADM ON QADM.OptionID = QAFM.FarmName
            WHERE 1=1 $Query " . $Limit;
        }
        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();
        // $erroInfo = $stmtSelect->errorInfo();
        // var_dump($erroInfo);

        $ArrayData = [];
        $i = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayList['SNO'] = ++$i;
            $ArrayList['FarmID'] = $dataRow['FarmID'];
            $ArrayList['FarmName'] = $dataRow['FarmName'];
            $ArrayList['OptionName'] = $dataRow['OptionName'];
            $ArrayList['FarmRegistrationNo'] = $dataRow['FarmRegistrationNo'];
            $ArrayList['FarmerName'] = $dataRow['FarmerName'];
            $ArrayList['FarmerAddress'] = $dataRow['FarmerAddress'];
            $ArrayList['PhoneNumber'] = $dataRow['PhoneNumber'];
            $ArrayList['TonnageValue'] = $dataRow['TonnageValue'];
            $ArrayList['Status'] = $dataRow['Status'];
            $ArrayList['EditLink'] = 'farm-master-create?FarmID=' . $dataRow['FarmID'];

            $ArrayData[] = $ArrayList;
        }

        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['TotalCount'] = $TotalCount;
        $Data['PageCount'] = $PageCount;
        $Data['FarmData'] = $ArrayData;

        return $Data;
    }

    public function deleteFarmMasterDetals($RequestBody)
    {
        $FarmID = $RequestBody['FarmID'];
        $existingFarm = $this->auditLogger->fetchRowById('QcAuditFarmMasters', 'FarmID', $FarmID);

        $sqlDelete = "DELETE FROM QcAuditFarmMasters WHERE FarmID = '$FarmID'";
        $stmtDelete = $this->conn->prepare($sqlDelete);
        $stmtDelete->execute();
        // $errorInfo = $stmtDelete->errorInfo();
        // var_dump($errorInfo);

        if ($stmtDelete) {
            $this->auditLogger->logDelete('QcAuditFarmMasters', $FarmID, $existingFarm, $RequestBody, [
                'ReferenceNo' => $FarmID,
                'Remarks' => 'QC farm master deleted',
            ]);
            $this->Error = null;
            $this->Msg = 'Farm Delete Successfully';
        } else {
            $this->Error = "Unable to Delete Farm";
            $this->Msg = "Unable to Delete Farm";
        }

        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        return  $Data;
    }
}
