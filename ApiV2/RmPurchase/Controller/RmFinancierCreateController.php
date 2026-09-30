<?php
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/
include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once __DIR__ . '/AuditLogger.php';

class RmFinancierCreateController
{
    private $conn;
    //  private $db_table = "ScrmApplicants";
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

    public function postFinancierDetails($RequestBody)
    {
        $ID = $RequestBody['ID'];

        if ($ID == '') {
            $Data = $this->insertFinancierDetails($RequestBody);
        } else {
            $Data = $this->updateFinancierDetails($RequestBody);
        }

        return $Data;
    }

    public function insertFinancierDetails($RequestBody)
    {
        $ID = $RequestBody['ID'];
        $SupplierType = $RequestBody['SupplierType']; // 01-02-2024
        $FinancierName = $RequestBody['FinancierName'];
        $BankName = $RequestBody['BankName'];
        $BankAccNo = $RequestBody['BankAccNo'];
        $GstNo = $RequestBody['GstNo'];
        $IFSC = $RequestBody['IFSC'];
        $PanNumber = $RequestBody['PanNumber'];
        $Address = $RequestBody['Address']; // 01-02-2024
        $EMailID = $RequestBody['EMailID']; // 01-02-2024
        $PhoneNumber = $RequestBody['PhoneNumber']; // 01-02-2024
        $IsCompany = $RequestBody['Status'];
        $ClientID = $RequestBody['ClientId']; //Mohan 20-11-2025 for getting client config
        if (empty($GstNo) && empty($PanNumber)) {
            $sqlInsert = "INSERT INTO `RmSuppliers`(`ID`,`SupplierType`,`FinancierName`,`BankName`, `BankAccNo`, `GstNo`, `IFSC`, `PanNumber`,
            `Address`,`EMailID`,`PhoneNumber`,`IsCompany`, `Status`)
                VALUES ('$ID', '$SupplierType', '$FinancierName','$BankName','$BankAccNo','$GstNo','$IFSC','$PanNumber',
                '$Address','$EMailID','$PhoneNumber','$IsCompany',1)";

            $stmtInsert = $this->conn->prepare($sqlInsert);
            $stmtInsert->execute();

            // $errorInfo = $stmtInsert->errorInfo();
            // var_dump($errorInfo);

            if ($stmtInsert) {

                $lastInsertedId = $this->conn->lastInsertId();
                $RequestBody['ID'] = $lastInsertedId;

                $FinancierData = $this->getFinancierDetails($RequestBody);
                $createdFinancier = $this->auditLogger->fetchRowById('RmSuppliers', 'ID', $lastInsertedId);
                $this->auditLogger->logInsert('RmSuppliers', $lastInsertedId, $createdFinancier, $RequestBody, [
                    'ReferenceNo' => $FinancierName,
                    'Remarks' => 'Financier created',
                ]);
                $Data['Error'] = null;
                $Data['Msg'] = 'Financier Posted Successfully';
                $Data['Data'] = $FinancierData;
                return $Data;
            } else {
                $Data['Error'] = 'Unable to Insert Financier';
                $Data['Msg'] = $this->Error;
                return $Data;
            }
        } else {
            $RestrictDuplicatePan = $this->utilController->getClientConfig($ClientID)['RestrictDuplicatePan'];
             if ($PanNumber != '') {
                if ($RestrictDuplicatePan == 1) {
                    // Use special PAN validation
                    $existingPANRecord = $this->checkDuplicatePanNumber($PanNumber, $SupplierType);
                } else {
                    // Use simple PAN validation
                    $existingPANRecord = $this->checkDuplicatePanNumberSimple($PanNumber, $SupplierType);
                }
                // $existingPANRecord = $this->checkDuplicatePanNumber($PanNumber, $SupplierType);
            } else {
                $existingPANRecord = 0;
            }

            if ($GstNo != '') {
                $existingGSTRecord = $this->checkDuplicateGSTNumber($GstNo);
            } else {
                $existingGSTRecord = 0;
            }

            if ($existingGSTRecord == 0 && $existingPANRecord == 0) {

                $sqlInsert = "INSERT INTO `RmSuppliers`(`ID`,`SupplierType`,`FinancierName`,`BankName`, `BankAccNo`, `GstNo`, `IFSC`, `PanNumber`,
                `Address`,`EMailID`,`PhoneNumber`,`IsCompany`,Status)
                VALUES ('$ID', '$SupplierType', '$FinancierName','$BankName','$BankAccNo','$GstNo','$IFSC','$PanNumber',
                '$Address','$EMailID','$PhoneNumber','$IsCompany',1)";

                $stmtInsert = $this->conn->prepare($sqlInsert);
                $stmtInsert->execute();

                if ($stmtInsert) {

                    $lastInsertedId = $this->conn->lastInsertId();
                    $RequestBody['ID'] = $lastInsertedId;

                    $FinancierData = $this->getFinancierDetails($RequestBody);
                    $createdFinancier = $this->auditLogger->fetchRowById('RmSuppliers', 'ID', $lastInsertedId);
                    $this->auditLogger->logInsert('RmSuppliers', $lastInsertedId, $createdFinancier, $RequestBody, [
                        'ReferenceNo' => $FinancierName,
                        'Remarks' => 'Financier created',
                    ]);
                    $Data['Error'] = null;
                    $Data['Msg'] = 'Financier Posted Successfully';
                    $Data['Data'] = $FinancierData;

                    return $Data;
                } else {
                    $Data['Error'] = 'Unable to Insert Financier';
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
    }

    public function updateFinancierDetails($RequestBody)
    {
        $ID = $RequestBody['ID'];
        $SupplierType = $RequestBody['SupplierType']; // 01-02-2024
        $FinancierName = $RequestBody['FinancierName'];
        $BankName = $RequestBody['BankName'];
        $BankAccNo = $RequestBody['BankAccNo'];
        $GstNo = $RequestBody['GstNo'];
        $IFSC = $RequestBody['IFSC'];
        $PanNumber = $RequestBody['PanNumber'];
        $Address = $RequestBody['Address']; // 01-02-2024
        $EMailID = $RequestBody['EMailID']; // 01-02-2024
        $PhoneNumber = $RequestBody['PhoneNumber']; // 01-02-2024
        $IsCompany = $RequestBody['Status'];
        $ClientID = $RequestBody['ClientId']; //Mohan 20-11-2025 for getting client config
        $existingFinancier = $this->auditLogger->fetchRowById('RmSuppliers', 'ID', $ID);
        // $existingPANUpdateRecord = $this->checkDuplicateUpdatePanNumber($PanNumber, $ID);

        // if (!$existingPANUpdateRecord) {

        if (empty($GstNo) && empty($PanNumber)) {
            $sqlUpdate = "UPDATE `RmSuppliers` SET `SupplierType`='$SupplierType', `FinancierName`='$FinancierName', `BankName`='$BankName', 
            `BankAccNo`='$BankAccNo', `GstNo`='$GstNo', `IFSC`='$IFSC', `PanNumber`='$PanNumber',`Address`='$Address', 
            `EMailID`='$EMailID',`PhoneNumber`='$PhoneNumber', `IsCompany`='$IsCompany'
            where ID='$ID'";

            $stmtUpdate = $this->conn->prepare($sqlUpdate);
            $stmtUpdate->execute();

            if ($stmtUpdate) {
                $FinancierData = $this->getFinancierDetails($RequestBody);
                $updatedFinancier = $this->auditLogger->fetchRowById('RmSuppliers', 'ID', $ID);
                $this->auditLogger->logUpdate('RmSuppliers', $ID, $existingFinancier, $updatedFinancier, $RequestBody, [
                    'ReferenceNo' => $FinancierName,
                    'Remarks' => 'Financier updated',
                ]);
                $Data['Error'] = null;
                $Data['Msg'] = 'Financier Updated Successfully';
                $Data['Data'] = $FinancierData;

                return $Data;
            } else {
                $Data['Error'] = 'Unable to update Financier';
                $Data['Msg'] = $this->Error;

                return $Data;
            }
        } else {
            $RestrictDuplicatePan = $this->utilController->getClientConfig($ClientID)['RestrictDuplicatePan'];
           if ($PanNumber != '') {
                if ($RestrictDuplicatePan == 1) {
                    // Use special check for update
                    $existingPANUpdateRecord = $this->checkDuplicateUpdatePanNumber($PanNumber, $SupplierType, $ID);
                } else {
                    // Use simple check for update
                    $existingPANUpdateRecord = $this->checkDuplicateUpdatePanNumberSimple($PanNumber, $SupplierType, $ID);
                }
                // $existingPANUpdateRecord = $this->checkDuplicateUpdatePanNumber($PanNumber, $SupplierType, $ID);
            } else {
                $existingPANUpdateRecord = 0;
            }

            if ($GstNo != '') {
                $existingGSTUpdateRecord = $this->checkDuplicateUpdateGSTNumber($GstNo, $ID);
            }else {
                $existingGSTUpdateRecord = 0;
            }

            if ($existingPANUpdateRecord == 0 && $existingGSTUpdateRecord == 0) {
                $sqlUpdate = "UPDATE `RmSuppliers` SET `SupplierType`='$SupplierType', `FinancierName`='$FinancierName', `BankName`='$BankName', 
                `BankAccNo`='$BankAccNo', `GstNo`='$GstNo', `IFSC`='$IFSC', `PanNumber`='$PanNumber',`Address`='$Address', 
                `EMailID`='$EMailID',`PhoneNumber`='$PhoneNumber', `IsCompany`='$IsCompany'
                where ID='$ID'";

                $stmtUpdate = $this->conn->prepare($sqlUpdate);
                $stmtUpdate->execute();

                if ($stmtUpdate) {
                    $FinancierData = $this->getFinancierDetails($RequestBody);
                    $updatedFinancier = $this->auditLogger->fetchRowById('RmSuppliers', 'ID', $ID);
                    $this->auditLogger->logUpdate('RmSuppliers', $ID, $existingFinancier, $updatedFinancier, $RequestBody, [
                        'ReferenceNo' => $FinancierName,
                        'Remarks' => 'Financier updated',
                    ]);
                    $Data['Error'] = null;
                    $Data['Msg'] = 'Financier Updated Successfully';
                    $Data['Data'] = $FinancierData;

                    return $Data;
                } else {
                    $Data['Error'] = 'Unable to update Financier';
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
    }

    public function checkDuplicatePanNumberSimple($PanNumber, $SupplierType)
    {
         $sqlCheck = "SELECT ID FROM RmSuppliers WHERE PanNumber = :PanNumber";
        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->bindParam(':PanNumber', $PanNumber, PDO::PARAM_STR);
        $stmtCheck->execute();

        $existingRecord = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        return $existingRecord;
    }
    public function checkDuplicatePanNumber($PanNumber, $SupplierType)
    {
        $SupplierType = strtoupper($SupplierType);

        $sqlCheck = "SELECT ID 
                    FROM RmSuppliers 
                    WHERE PanNumber = :PanNumber 
                      AND SupplierType = 'BUSINESS'";

        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->bindParam(':PanNumber', $PanNumber, PDO::PARAM_STR);
        $stmtCheck->execute();
        $businessFound = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($businessFound) {
            return $businessFound;
        }

        if ($SupplierType === 'BUSINESS') {

            $sqlCheck = "SELECT ID 
                FROM RmSuppliers 
                WHERE PanNumber = :PanNumber";

            $stmtCheck = $this->conn->prepare($sqlCheck);
            $stmtCheck->bindParam(':PanNumber', $PanNumber, PDO::PARAM_STR);
            $stmtCheck->execute();

            return $stmtCheck->fetch(PDO::FETCH_ASSOC);
        }

        $sqlCheck = "SELECT ID 
            FROM RmSuppliers 
            WHERE PanNumber = :PanNumber
              AND SupplierType = :SupplierType";

        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->bindParam(':PanNumber', $PanNumber, PDO::PARAM_STR);
        $stmtCheck->bindParam(':SupplierType', $SupplierType, PDO::PARAM_STR);
        $stmtCheck->execute();

        return $stmtCheck->fetch(PDO::FETCH_ASSOC);
    }



    public function checkDuplicateGSTNumber($GstNo)
    {
        $sqlCheck = "SELECT ID FROM RmSuppliers WHERE GstNo = :GstNo";
        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->bindParam(':GstNo', $GstNo, PDO::PARAM_STR);
        $stmtCheck->execute();

        $existingRecord = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        return $existingRecord;
    }

    public function checkDuplicateUpdatePanNumberSimple($PanNumber, $SupplierType, $ID)
    {
       $sqlCheck = "SELECT ID FROM RmSuppliers WHERE PanNumber = :PanNumber AND ID != :ID";
        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->bindParam(':PanNumber', $PanNumber, PDO::PARAM_STR);
        $stmtCheck->bindParam(':ID', $ID, PDO::PARAM_INT);
        $stmtCheck->execute();

        $existingUpdateRecord = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        return $existingUpdateRecord;
    }
    public function checkDuplicateUpdatePanNumber($PanNumber, $SupplierType, $ID)
    {
        $SupplierType = strtoupper($SupplierType);

        $sqlCheck = "SELECT ID 
                 FROM RmSuppliers 
                 WHERE PanNumber = :PanNumber
                   AND SupplierType = 'BUSINESS'
                   AND ID != :ID";

        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->bindParam(':PanNumber', $PanNumber, PDO::PARAM_STR);
        $stmtCheck->bindParam(':ID', $ID, PDO::PARAM_INT);
        $stmtCheck->execute();

        $businessFound = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        if ($businessFound) {
            return $businessFound;
        }

        if ($SupplierType === 'BUSINESS') {

            $sqlCheck = "SELECT ID 
                     FROM RmSuppliers 
                     WHERE PanNumber = :PanNumber
                       AND ID != :ID";

            $stmtCheck = $this->conn->prepare($sqlCheck);
            $stmtCheck->bindParam(':PanNumber', $PanNumber, PDO::PARAM_STR);
            $stmtCheck->bindParam(':ID', $ID, PDO::PARAM_INT);
            $stmtCheck->execute();

            return $stmtCheck->fetch(PDO::FETCH_ASSOC);
        }

        $sqlCheck = "SELECT ID 
                 FROM RmSuppliers 
                 WHERE PanNumber = :PanNumber
                   AND SupplierType = :SupplierType
                   AND ID != :ID";

        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->bindParam(':PanNumber', $PanNumber, PDO::PARAM_STR);
        $stmtCheck->bindParam(':SupplierType', $SupplierType, PDO::PARAM_STR);
        $stmtCheck->bindParam(':ID', $ID, PDO::PARAM_INT);
        $stmtCheck->execute();

        return $stmtCheck->fetch(PDO::FETCH_ASSOC);
    }



    public function checkDuplicateUpdateGSTNumber($GstNo, $ID)
    {
        $sqlCheck = "SELECT ID FROM RmSuppliers WHERE GstNo = :GstNo AND ID != :ID";
        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->bindParam(':GstNo', $GstNo, PDO::PARAM_STR);
        $stmtCheck->bindParam(':ID', $ID, PDO::PARAM_INT);
        $stmtCheck->execute();

        $existingUpdateRecord = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        return $existingUpdateRecord;
    }

    function getFinancierDetails($RequestBody)
    {
        $ID = $RequestBody['ID'];

        $sqlFields = "ID,SupplierType,FinancierName,Address,PurchaseCenter,BankName,PanNumber,BankAccNo,IFSC,GstNo,Status";

        if ($ID != '') {
            $sqlQuery = "SELECT $sqlFields FROM `RmSuppliers` WHERE ID='$ID'";
        } else {
            $sqlQuery = "SELECT $sqlFields FROM `RmSuppliers`";
        }

        // SELECT ID, SupplierType, FinancierName, Address, PurchaseCenter, BankName, BankAccNo, GstNo, IFSC, 
        // PanNumber, EMailID, PhoneNumber, Status FROM RmSuppliers WHERE 1

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        $i = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayFinancierData[] = $dataRow;
        }

        $data['Data'] = $ArrayFinancierData;

        return $data;
    }
}
