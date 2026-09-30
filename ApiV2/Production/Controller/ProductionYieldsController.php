<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class ProductionYieldsController
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
        $this->auditLogger = new CentralAuditLogger($this->conn, 'Production');
        // $this->utilController = new UtilController($this->conn);
    }

    public function postProductionYields($RequestBody)
    {
        $YieldID = $RequestBody['YieldID'];
        $Species = $RequestBody['Species'];
        $TypeProduct = $RequestBody['TypeProduct'];
        $ProductID = $RequestBody['ProductForm'];
        $FreezingType = $RequestBody['FreezingType'];
        $Treatment = $RequestBody['Treatment'];
        $Yield = $RequestBody['Yield'];
        $Status = $RequestBody['Status'];
        $Grade = $RequestBody['Grade'];
        $Count = $RequestBody['Count'];
        // $EffectedDate = $RequestBody['EffectedDate'];
        $TypeOfMaterial = $RequestBody['TypeOfMaterial'];
        $PostedUserID = 1;


        date_default_timezone_set("Asia/Kolkata");
        $PostedDate = date("Y-m-d H:i:s");

        $sqlProductID = "SELECT ShortName FROM `ScrmProductList` WHERE ProductID='$ProductID'";
        $stmtProductID = $this->conn->prepare($sqlProductID);
        $stmtProductID->execute();
        $dataRowProductID = $stmtProductID->fetch(PDO::FETCH_ASSOC);
        $ProductForm = $dataRowProductID['ShortName'];

        if ($YieldID == '') {

            if ($this->checkValidCombination($RequestBody)) {
                // echo $this->checkValidCombination($RequestBody);
                // exit;
                $sqlInsert = "INSERT INTO `ProdStandardYields`( `Variety`, `ProductID`, `TypeOfProduct`,`Grade`,`Count`, `YieldPercentage`, 
                    `Species`, `TypeOfMaterial`, `Treatment`,`FreezingType`, `Status`, `PostedUserID`, `PostedDate`)
                     VALUES ('$ProductForm','$ProductID','$TypeProduct','$Grade','$Count','$Yield','$Species','$TypeOfMaterial','$Treatment','$FreezingType',
                    '$Status','$PostedUserID','$PostedDate')";


                $stmtInsert = $this->conn->prepare($sqlInsert);
                $stmtInsert->execute();

                if ($stmtInsert) {
                    $lastInsertId = $this->conn->lastInsertId();
                    $createdYield = $this->auditLogger->fetchRowById('ProdStandardYields', 'YieldID', $lastInsertId);
                    $this->auditLogger->logInsert('ProdStandardYields', $lastInsertId, $createdYield, $RequestBody, [
                        'ReferenceNo' => $lastInsertId,
                        'Remarks' => 'Production standard yield created',
                    ]);
                    $this->Error = null;
                    $this->Msg = ' Posted Successfully';
                } else {
                    $this->Error = 'Unable Save Data';
                    $this->Msg = 'Unable Save Data';
                }
            } else {
                $this->Error = 'Data is Already Exists';
                $this->Msg = 'Data is Already Exists';
            }
        } else {
            $existingYield = $this->auditLogger->fetchRowById('ProdStandardYields', 'YieldID', $YieldID);

            if ($this->checkValidCombination($RequestBody)) {

                $sqlUpdate = " UPDATE `ProdStandardYields` SET `Variety`='$ProductForm',`ProductID`='$ProductID',`TypeOfProduct`='$TypeProduct',`Grade`='$Grade',`Count`='$Count',`YieldPercentage`='$Yield',
                    `Species`='$Species',`TypeOfMaterial`='$TypeOfMaterial',`Treatment`='$Treatment',`FreezingType`='$FreezingType',`Status`='$Status',`PostedUserID`='$PostedUserID',`PostedDate`='$PostedDate'
                     where `YieldID`='$YieldID'";

                $stmtUpdate = $this->conn->prepare($sqlUpdate);
                $stmtUpdate->execute();

                if ($stmtUpdate) {
                    $updatedYield = $this->auditLogger->fetchRowById('ProdStandardYields', 'YieldID', $YieldID);
                    $this->auditLogger->logUpdate('ProdStandardYields', $YieldID, $existingYield, $updatedYield, $RequestBody, [
                        'ReferenceNo' => $YieldID,
                        'Remarks' => 'Production standard yield updated',
                    ]);
                    $this->Error = null;
                    $this->Msg = 'Data Updated Successfully';
                } else {
                    $this->Error = 'Unable Update Data';
                    $this->Msg = 'Unable Update Data';
                }
            } else {
                $this->Error = 'Data is Already Exists'; // Exists
                $this->Msg = 'Data is Already Exists';
            }

        }

        $getData = $this->getProductionYields($RequestBody);
        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        $Data['Data'] = $getData['Data'];

        return $Data;
    }

    function checkValidCombination($RequestBody)
    {

        $isValid = false;


        $YieldID = $RequestBody['YieldID'];

        if ($YieldID != '') {
            $Qry = "and YieldID != '$YieldID'";
        }
        $Species = $RequestBody['Species'];
        $TypeProduct = $RequestBody['TypeProduct'];
        $ProductID = $RequestBody['ProductForm'];
        $FreezingType = $RequestBody['FreezingType'];
        $Treatment = $RequestBody['Treatment'];
        $Grade = $RequestBody['Grade'];
        $TypeOfMaterial = $RequestBody['TypeOfMaterial'];


        $sqlCheckInsert = "SELECT count(*) as Count FROM `ProdStandardYields` WHERE Species='$Species' AND TypeOfProduct='$TypeProduct' AND `Grade`='$Grade' AND
        ProductID='$ProductID' and  Treatment='$Treatment'   AND FreezingType='$FreezingType'AND TypeOfMaterial='$TypeOfMaterial'  $Qry";

        $stmtCheckInsert = $this->conn->prepare($sqlCheckInsert);
        $stmtCheckInsert->execute();

        $checkCount = $stmtCheckInsert->fetch(PDO::FETCH_ASSOC);
        $rowCount = $checkCount['Count'];

        if ($rowCount == 0) {
            $isValid = true;
        } else {
            $isValid = false;
        }

        return $isValid;
    }

    public function deleteProductionYields($RequestBody)
    {
        $YieldID = $RequestBody['YieldID'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];

        $existingYield = $this->auditLogger->fetchRowById('ProdStandardYields', 'YieldID', $YieldID);
        $sqlDelete = "DELETE FROM ProdStandardYields WHERE YieldID='$YieldID'";
        $stmtDelete = $this->conn->prepare($sqlDelete);
        $stmtDelete->execute();

        if ($stmtDelete) {
            $this->auditLogger->logDelete('ProdStandardYields', $YieldID, $existingYield, $RequestBody, [
                'ReferenceNo' => $YieldID,
                'Remarks' => 'Production standard yield deleted',
            ]);
            $getData = $this->getProductionYields($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = ' Deleted Successfully';
            $Data['Data'] = $getData['Data'];
        } else {
            $Data['Error'] = 'Unable to Delete';
            $Data['Msg'] = 'Unable to Delete';
        }

        return $Data;
    }


    function getProductionYields($RequestBody)
    {
        $RecordsPerPage = 100;
        $YieldID = $RequestBody['YieldID'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];

        $Query = '';

        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query = " and CONCAT_WS('', ID, SpeciesName, ShortCode, QcSpeciesShortCode, Description, Status) LIKE '%$SearchKey%'";
        }

        $sqlSpeciesCount = "SELECT COUNT(*) as Total FROM ProdStandardYields WHERE 1=1 $Query";
        $stmt = $this->conn->prepare($sqlSpeciesCount);
        $stmt->execute();

        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "YieldID, TypeOfProduct, Variety, ProductID, Treatment,  FreezingType, Species, 
         YieldPercentage, PostedDate, PostedUserID, CHD.Status, SpeciesName,TreatmentName,TypeOfMaterial,Grade,Count";

        if ($YieldID != '') {
            $sqlQuery = "SELECT $sqlFields FROM `ProdStandardYields` AS CHD           
            LEFT JOIN ScrmSpeciesMaster AS SSM ON CHD.Species = SSM.ShortCode
            LEFT JOIN ScrmTreatmentsMaster AS STM ON CHD.Treatment = STM.ShortCode    
            WHERE YieldID='$YieldID'";
        } else {
            $sqlQuery = "SELECT $sqlFields FROM `ProdStandardYields` AS CHD           
            LEFT JOIN ScrmSpeciesMaster AS SSM ON CHD.Species = SSM.ShortCode
            LEFT JOIN ScrmTreatmentsMaster AS STM ON CHD.Treatment = STM.ShortCode    
            WHERE 1=1 $Query ORDER by YieldID desc " . $Limit;
        }
        // echo $sqlQuery;
        // exit;
        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($errorInfo);

        $ArrayGradeData = [];

        $i = $LimitMax;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            // Grade, FreezingType, Species, PreparationType,
            // HeadonCount, Yield, PostedDate, PostedUserID, Status, GlazeStatus, GlazePercentage";
            $ArrayGradeList['SNO'] = ++$i;
            $ArrayGradeList['YieldID'] = $dataRow['YieldID'];
            $ArrayGradeList['TypeProduct'] = $dataRow['TypeOfProduct'];
            $ArrayGradeList['Variety'] = $dataRow['Variety'];
            $ArrayGradeList['ProductID'] = $dataRow['ProductID'];
            $ArrayGradeList['Treatment'] = $dataRow['Treatment'];
            $ArrayGradeList['TreatmentName'] = $dataRow['TreatmentName'];
            $ArrayGradeList['Grade'] = $dataRow['Grade'];
            $ArrayGradeList['FreezingType'] = $dataRow['FreezingType'];
            $ArrayGradeList['Species'] = $dataRow['Species'];
            $ArrayGradeList['SpeciesName'] = $dataRow['SpeciesName'];
            $ArrayGradeList['YieldPercentage'] = $dataRow['YieldPercentage'];
            $ArrayGradeList['TypeOfMaterial'] = $dataRow['TypeOfMaterial'];
            $ArrayGradeList['Status'] = $dataRow['Status'];
            $ArrayGradeList['Count'] = $dataRow['Count'];
            $ArrayGradeList['EditLink'] = "production-standard-yields?YieldID=" . $dataRow['YieldID'];

            $ArrayGradeData[] = $ArrayGradeList;
        }

        if (count($ArrayGradeData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TotalCount'] = $TotalCount;
        $data['RemainingCount'] = "";
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;
        $data['Data'] = $ArrayGradeData;

        return $data;
    }
}
