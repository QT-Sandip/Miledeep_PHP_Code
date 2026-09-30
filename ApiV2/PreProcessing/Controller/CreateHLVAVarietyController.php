<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class CreateHLVAVarietyController
{
    private $conn;
    //	private $db_table = "ScrmApplicants";
    private $utilController;
    private $Error = null;
    private $Msg = '';
    private $auditLogger;


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
        $this->auditLogger = new CentralAuditLogger($this->conn, 'PreProcessing');
    }


    public function postHLVAVarietyDetails($RequestBody)
    {
        $VarietyID = $RequestBody['VarietyID'];

        if ($VarietyID == '') {
            $Data = $this->insertHLVAVarietyDetails($RequestBody);
        } else {
            $Data = $this->updateHLVAVarietyDetails($RequestBody);
        }

        return $Data;
    }

    public function insertHLVAVarietyDetails($RequestBody)
    {
        $VarietyID = $RequestBody['VarietyID'];
        $Species = $RequestBody['Species'];
        $TypeProduct = $RequestBody['TypeProduct'];
        $ShortName = $RequestBody['ShortName'];
        $FullName = $RequestBody['FullName'];
        $Category = $RequestBody['Category'];
        $Status = $RequestBody['Status'];
        $ProductID = $RequestBody['ProductID'];
        $PdDescription = $RequestBody['ProductDescription'];
        $ScrmProductID = $RequestBody['ScrmProductID'];

        $existingRecord = $this->checkDuplicateShortName($Species,$ShortName, $ProductID, $TypeProduct);

        if (!$existingRecord) {

            $sqlInsert = "INSERT INTO PpcHLVAVarietyList(VarietyID, Species, TypeProduct, ShortName, FullName, Category, Status, ProductID,ScrmProductID, ProductDescription) 
            VALUES ('$VarietyID','$Species','$TypeProduct','$ShortName','$FullName','$Category','$Status','$ProductID','$ScrmProductID','$PdDescription')";

            $stmtInsert = $this->conn->prepare($sqlInsert);
            $stmtInsert->execute();

            // $errorInfo = $stmtInsert->errorInfo();
            // var_dump($errorInfo);

            if ($stmtInsert) {

                $lastInsertedId = $this->conn->lastInsertId();
                $RequestBody['VarietyID'] = $lastInsertedId;
                $createdVariety = $this->auditLogger->fetchRowById('PpcHLVAVarietyList', 'VarietyID', $lastInsertedId);
                $this->auditLogger->logInsert('PpcHLVAVarietyList', $lastInsertedId, $createdVariety, $RequestBody, [
                    'ReferenceNo' => $lastInsertedId,
                    'Remarks' => 'PreProcessing HLVa variety created',
                ]);

                $HLVAVarietyDetails = $this->getHLVAVarietyDetails($RequestBody);
                $Data['Error'] = null;
                $Data['Msg'] = 'Insert Success';
                $Data['Data'] = $HLVAVarietyDetails;

                return $Data;
            } else {
                $Data['Error'] = 'Unable to Insert Variety';
                $Data['Msg'] = $this->Error;

                return $Data;
            }
        } else {
            $Data['Error'] = 'Short Name already exists';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    public function updateHLVAVarietyDetails($RequestBody)
    {
        $VarietyID = $RequestBody['VarietyID'];
        $Species = $RequestBody['Species'];
        $TypeProduct = $RequestBody['TypeProduct'];
        $ShortName = $RequestBody['ShortName'];
        $FullName = $RequestBody['FullName'];
        $Category = $RequestBody['Category'];
        $Status = $RequestBody['Status'];
        $ProductID = $RequestBody['ProductID'];
        $ScrmProductID = $RequestBody['ScrmProductID'];
        $PdDescription = $RequestBody['ProductDescription'];
        $existingVariety = $this->auditLogger->fetchRowById('PpcHLVAVarietyList', 'VarietyID', $VarietyID);

        $existingUpdateRecord = $this->checkDuplicateUpdateShortName($Species,$ShortName, $VarietyID, $ProductID, $TypeProduct);

        if (!$existingUpdateRecord) {

            $sqlUpdate = "UPDATE `PpcHLVAVarietyList` SET `Species`='$Species', `TypeProduct`='$TypeProduct',`ShortName`='$ShortName',
            `FullName`='$FullName',`Category`='$Category',`Status`='$Status',`ProductID`='$ProductID',`ScrmProductID`='$ScrmProductID', 
            `ProductDescription`='$PdDescription' WHERE VarietyID='$VarietyID'";

            $stmtUpdate = $this->conn->prepare($sqlUpdate);
            $stmtUpdate->execute();

            // $errorInfo = $stmtUpdate->errorInfo();
            // var_dump($errorInfo);

            if ($stmtUpdate) {
                $updatedVariety = $this->auditLogger->fetchRowById('PpcHLVAVarietyList', 'VarietyID', $VarietyID);
                $this->auditLogger->logUpdate('PpcHLVAVarietyList', $VarietyID, $existingVariety, $updatedVariety, $RequestBody, [
                    'ReferenceNo' => $VarietyID,
                    'Remarks' => 'PreProcessing HLVa variety updated',
                ]);
                $HLVAVarietyDetails = $this->getHLVAVarietyDetails($RequestBody);
                $Data['Error'] = null;
                $Data['Msg'] = 'Update Success';
                $Data['Data'] = $HLVAVarietyDetails;

                return $Data;
            } else {
                $Data['Error'] = 'Unable to update Variety';
                $Data['Msg'] = $this->Error;

                return $Data;
            }
        } else {
            $Data['Error'] = 'Short Name already exists';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    // public function checkDuplicateShortName($ShortName)
    public function checkDuplicateShortName($Species,$ShortName, $ProductID, $TypeProduct)
    {
        $sqlCheck = "SELECT VarietyID FROM PpcHLVAVarietyList WHERE Species = '$Species' AND `ProductID`='$ProductID' AND
        `TypeProduct`='$TypeProduct' AND ShortName = '$ShortName'";
        $stmtCheck = $this->conn->prepare($sqlCheck);
        // $stmtCheck->bindParam(':ShortName', $ShortName, PDO::PARAM_STR);
        $stmtCheck->execute();

        $existingRecord = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        return $existingRecord;
    }

    public function checkDuplicateUpdateShortName($Species,$ShortName, $VarietyID, $ProductID, $TypeProduct)
    {
        $sqlCheck = "SELECT VarietyID FROM PpcHLVAVarietyList WHERE Species = '$Species' AND  `ProductID`='$ProductID' AND 
        `TypeProduct`='$TypeProduct' AND ShortName = '$ShortName' AND VarietyID != '$VarietyID'";
        $stmtCheck = $this->conn->prepare($sqlCheck);
        $stmtCheck->execute();
        // echo $stmtCheck;
        // echo 'ShortName ' . $ShortName;
        // echo 'VarietyID ' . $VarietyID;

        $existingUpdateRecord = $stmtCheck->fetch(PDO::FETCH_ASSOC);

        return $existingUpdateRecord;
    }

    function getHLVAVarietyDetails($RequestBody)
    {

        $VarietyID = $RequestBody['VarietyID'];
        $sqlFields = "VarietyID, Species, TypeProduct, ShortName, FullName, Category, Status, ProductID, ProductDescription";

        if ($VarietyID != '') {
            $sqlQuery = "SELECT $sqlFields FROM `PpcHLVAVarietyList` WHERE VarietyID='$VarietyID'";
        } else {
            $sqlQuery = "SELECT $sqlFields FROM `PpcHLVAVarietyList`";
        }

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        $i = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $HLVAVarietyDetails['SNo'] = ++$i;
            $HLVAVarietyDetails['VarietyID'] = $dataRow['VarietyID'];
            $HLVAVarietyDetails['Species'] = $dataRow['Species'];
            $HLVAVarietyDetails['TypeProduct'] = $dataRow['TypeProduct'];
            $HLVAVarietyDetails['ShortName'] = $dataRow['ShortName'];
            $HLVAVarietyDetails['FullName'] = $dataRow['FullName'];
            $HLVAVarietyDetails['Category'] = $dataRow['Category'];
            $HLVAVarietyDetails['Status'] = $dataRow['Status'];
            $HLVAVarietyDetails['ProductID'] = $dataRow['ProductID'];
            $HLVAVarietyDetails['ProductDescription'] = $dataRow['ProductDescription'];

            $ArrayData[] = $HLVAVarietyDetails;
        }

        $data['Data'] = $ArrayData;

        return $data;
    }
}
