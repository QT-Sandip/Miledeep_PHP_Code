<?php
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/
include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once __DIR__ . '/AuditLogger.php';

class RmAgentCreateController
{
    private $conn;
    //	private $db_table = "ScrmApplicants";
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

    public function postPpcStationsDetails($RequestBody)
    {
        $Agent = $RequestBody['ID'];

        if ($Agent == '') {
            $Data = $this->insertRmAgentDetails($RequestBody);
        } else {
            $Data = $this->updateAgentDetails($RequestBody);
        }

        return $Data;
    }

    public function insertRmAgentDetails($RequestBody)
    {
        $AgentID = $RequestBody['ID'];
        $UserName = $RequestBody['UserName'];
        $BankName = $RequestBody['BankName'];
        $BankAccNo = $RequestBody['BankAccNo'];
        $GstNo = $RequestBody['GstNo'];
        $IFSC = $RequestBody['IFSC'];
        $PanNumber = $RequestBody['PanNumber'];
        $IsCompany = $RequestBody['IsCompany'];

        $existingPANRecord = $this->checkDuplicatePanNumber($PanNumber);

        if (!$existingPANRecord) {

            if (empty($GstNo)) {
                $sqlInsert = "INSERT INTO `RmUsersList`(`ID`,`UserName`,`UserType`,`BankName`, `BankAccNo`, `GstNo`, `IFSC`, `PanNumber`, `IsCompany`, `Status`) 
                VALUES ('$AgentID', '$UserName','3','$BankName','$BankAccNo','$GstNo','$IFSC','$PanNumber','$IsCompany','1')";

                $stmtInsert = $this->conn->prepare($sqlInsert);
                $stmtInsert->execute();

                if ($stmtInsert) {

                    $lastInsertedId = $this->conn->lastInsertId();
                    $RequestBody['ID'] = $lastInsertedId;
                    $createdAgent = $this->auditLogger->fetchRowById('RmUsersList', 'ID', $lastInsertedId);
                    $this->auditLogger->logInsert('RmUsersList', $lastInsertedId, $createdAgent, $RequestBody, [
                        'ReferenceNo' => $UserName,
                        'Remarks' => 'Agent created',
                    ]);

                    $AgentData = $this->getAgentDetails($RequestBody);
                    $Data['Error'] = null;
                    $Data['Msg'] = 'Agent Posted Successfully';
                    $Data['Data'] = $AgentData;
                    return $Data;
                } else {
                    $Data['Error'] = 'Unable to Insert Agent';
                    $Data['Msg'] = $this->Error;
                    return $Data;
                }
            } else {
                $existingGSTRecord = $this->checkDuplicateGSTNumber($GstNo);

                if (!$existingGSTRecord) {
                    $sqlInsert = "INSERT INTO `RmUsersList`(`ID`,`UserName`,`UserType`,`BankName`, `BankAccNo`, `GstNo`, `IFSC`, `PanNumber`, `IsCompany`, `Status`) 
                    VALUES ('$AgentID', '$UserName','3','$BankName','$BankAccNo','$GstNo','$IFSC','$PanNumber','$IsCompany','1')";

                    $stmtInsert = $this->conn->prepare($sqlInsert);
                    $stmtInsert->execute();

                    if ($stmtInsert) {

                        $lastInsertedId = $this->conn->lastInsertId();
                        $RequestBody['ID'] = $lastInsertedId;
                        $createdAgent = $this->auditLogger->fetchRowById('RmUsersList', 'ID', $lastInsertedId);
                        $this->auditLogger->logInsert('RmUsersList', $lastInsertedId, $createdAgent, $RequestBody, [
                            'ReferenceNo' => $UserName,
                            'Remarks' => 'Agent created',
                        ]);

                        $AgentData = $this->getAgentDetails($RequestBody);
                        $Data['Error'] = null;
                        $Data['Msg'] = 'Agent Posted Successfully';
                        $Data['Data'] = $AgentData;
                        return $Data;
                    } else {
                        $Data['Error'] = 'Unable to Insert Agent';
                        $Data['Msg'] = $this->Error;
                        return $Data;
                    }
                } else {
                    $panError = $existingPANRecord ? 'PAN Number already exists' : '';
                    $gstError = $existingGSTRecord ? 'GST Number already exists' : '';
                    $Data['Error'] = trim($panError . ' ' . $gstError);
                    $Data['Msg'] = $this->Error;
                    return $Data;
                }
            }
        } else {
            $panError = $existingPANRecord ? 'PAN Number already exists' : '';
            $Data['Error'] = $panError;
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    public function updateAgentDetails($RequestBody)
    {
        $AgentID = $RequestBody['ID'];
        $UserName = $RequestBody['UserName'];
        $BankName = $RequestBody['BankName'];
        $BankAccNo = $RequestBody['BankAccNo'];
        $GstNo = $RequestBody['GstNo'];
        $IFSC = $RequestBody['IFSC'];
        $PanNumber = $RequestBody['PanNumber'];
        $IsCompany = $RequestBody['IsCompany'];
        $existingAgent = $this->auditLogger->fetchRowById('RmUsersList', 'ID', $AgentID);

        $existingPANUpdateRecord = $this->checkDuplicateUpdatePanNumber($PanNumber, $AgentID);

        if (!$existingPANUpdateRecord) {

            if (empty($GstNo)) {
                $sqlUpdate = "UPDATE `RmUsersList` SET `UserName`='$UserName', `BankName`='$BankName',  `BankAccNo`='$BankAccNo', `GstNo`='$GstNo',
                `IFSC`='$IFSC', `PanNumber`='$PanNumber',  `IsCompany`='$IsCompany'
                where ID='$AgentID'";

                $stmtUpdate = $this->conn->prepare($sqlUpdate);
                $stmtUpdate->execute();

                if ($stmtUpdate) {
                    $updatedAgent = $this->auditLogger->fetchRowById('RmUsersList', 'ID', $AgentID);
                    $this->auditLogger->logUpdate('RmUsersList', $AgentID, $existingAgent, $updatedAgent, $RequestBody, [
                        'ReferenceNo' => $UserName,
                        'Remarks' => 'Agent updated',
                    ]);
                    $AgentData = $this->getAgentDetails($RequestBody);
                    $Data['Error'] = null;
                    $Data['Msg'] = 'Agent Updated Successfully';
                    $Data['Data'] = $AgentData;
                    return $Data;
                } else {
                    $Data['Error'] = 'Unable to update Agent';
                    $Data['Msg'] = $this->Error;
                    return $Data;
                }
            } else {
                $existingGSTUpdateRecord = $this->checkDuplicateUpdateGSTNumber($GstNo, $AgentID);
                if (!$existingGSTUpdateRecord) {
                    $sqlUpdate = "UPDATE `RmUsersList` SET `UserName`='$UserName', `BankName`='$BankName',  `BankAccNo`='$BankAccNo', `GstNo`='$GstNo',
                `IFSC`='$IFSC', `PanNumber`='$PanNumber',  `IsCompany`='$IsCompany'
                where ID='$AgentID'";

                    $stmtUpdate = $this->conn->prepare($sqlUpdate);
                    $stmtUpdate->execute();

                    if ($stmtUpdate) {
                        $updatedAgent = $this->auditLogger->fetchRowById('RmUsersList', 'ID', $AgentID);
                        $this->auditLogger->logUpdate('RmUsersList', $AgentID, $existingAgent, $updatedAgent, $RequestBody, [
                            'ReferenceNo' => $UserName,
                            'Remarks' => 'Agent updated',
                        ]);
                        $AgentData = $this->getAgentDetails($RequestBody);
                        $Data['Error'] = null;
                        $Data['Msg'] = 'Agent Updated Successfully';
                        $Data['Data'] = $AgentData;
                        return $Data;
                    } else {
                        $Data['Error'] = 'Unable to update Agent';
                        $Data['Msg'] = $this->Error;
                        return $Data;
                    }
                } else {
                    $panUpdateError = $existingPANUpdateRecord ? 'PAN Number already exists' : '';
                    $gstUpdateError = $existingGSTUpdateRecord ? 'GST Number already exists' : '';
                    $Data['Error'] = trim($panUpdateError . ' ' . $gstUpdateError);
                    $Data['Msg'] = $this->Error;
                    return $Data;
                }
            }
        } else {
            $panUpdateError = $existingPANUpdateRecord ? 'PAN Number already exists' : '';
            $Data['Error'] = $panUpdateError;
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    public function checkDuplicatePanNumber($PanNumber)
    {
        $sqlCheck = "SELECT ID FROM RmUsersList WHERE PanNumber = :PanNumber";
        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->bindParam(':PanNumber', $PanNumber, PDO::PARAM_STR);
        $stmtCheck->execute();

        $existingRecord = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        return $existingRecord;
    }

    public function checkDuplicateGSTNumber($GstNo)
    {
        $sqlCheck = "SELECT ID FROM RmUsersList WHERE GstNo = :GstNo";
        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->bindParam(':GstNo', $GstNo, PDO::PARAM_STR);
        $stmtCheck->execute();

        $existingRecord = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        return $existingRecord;
    }

    public function checkDuplicateUpdatePanNumber($PanNumber, $AgentID)
    {
        $sqlCheck = "SELECT ID FROM RmUsersList WHERE PanNumber = :PanNumber AND ID != :ID";
        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->bindParam(':PanNumber', $PanNumber, PDO::PARAM_STR);
        $stmtCheck->bindParam(':ID', $AgentID, PDO::PARAM_INT);
        $stmtCheck->execute();

        $existingUpdateRecord = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        return $existingUpdateRecord;
    }

    public function checkDuplicateUpdateGSTNumber($GstNo, $AgentID)
    {
        $sqlCheck = "SELECT ID FROM RmUsersList WHERE GstNo = :GstNo AND ID != :ID";
        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->bindParam(':GstNo', $GstNo, PDO::PARAM_STR);
        $stmtCheck->bindParam(':ID', $AgentID, PDO::PARAM_INT);
        $stmtCheck->execute();

        $existingUpdateRecord = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        return $existingUpdateRecord;
    }

    function getAgentDetails($RequestBody)
    {
        $AgentID = $RequestBody['ID'];

        $sqlFields = "ID,UserName,BankName,PanNumber,BankAccNo,IFSC,GstNo,IsCompany";

        if ($AgentID != '') {
            $sqlQuery = "SELECT $sqlFields FROM `RmUsersList` WHERE ID='$AgentID'";
        } else {
            $sqlQuery = "SELECT $sqlFields FROM `RmUsersList` WHERE UserType=3 and Status=1 ";
        }
        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        // $i = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayAgentData[] =  $dataRow;
        }

        $data['Data'] = $ArrayAgentData;

        return $data;
    }
}
