<?php


include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class GradeMasterHlCountController
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
        $this->auditLogger = new CentralAuditLogger($this->conn, 'ColdStore');
        // $this->utilController = new UtilController($this->conn);
    }

    public function postGradeDetails($RequestBody)
    {
        $HeadonDataID = $RequestBody['HeadonDataID'];
        $Species = $RequestBody['Species'];
        $TypeProduct = $RequestBody['TypeProduct'];
        $ProductForm = $RequestBody['ProductForm'];
        $FreezingType = $RequestBody['FreezingType'];
        $PreparationType = $RequestBody['Preparation'];
        $GlazeStatus = $RequestBody['GlazeStatus'];
        $GlazePercentage = $RequestBody['GlazePerc'];
        $GlazeType = $RequestBody['GlazeType'];
        $Treatment = $RequestBody['Treatment'];
        $Grade = $RequestBody['Grade'];
        $PackingCount = $RequestBody['PackingCount'];
        $HeadonCount = $RequestBody['HonCount'];
        $Yield = $RequestBody['Yield'];
        $Status = $RequestBody['Status'];
        $Type = $RequestBody['Type'];

        date_default_timezone_set("Asia/Kolkata");
        $PostedDate = date("Y-m-d H:i:s");

        if ($HeadonDataID == '') {

            if ($this->checkValidCombination($RequestBody)) {

                $sqlInsert = " INSERT INTO CsHeadonRequirementMasters(HeadonDataID, TypeProduct, GlazeType, ShortName,Treatment, Grade, FreezingType, 
                    Species, PreparationType, PackingCount, HeadonCount, Yield, PostedDate,  Status, GlazeStatus, GlazePercentage) 
                    VALUES ('$HeadonDataID','$TypeProduct','$GlazeType','$ProductForm','$Treatment','$Grade','$FreezingType','$Species',
                    '$PreparationType', '$PackingCount', '$HeadonCount','$Yield','$PostedDate','$Status','$GlazeStatus','$GlazePercentage')";

                $stmtInsert = $this->conn->prepare($sqlInsert);
                $stmtInsert->execute();

                if ($stmtInsert) {
                    $lastInsertedId = $this->conn->lastInsertId();
                    $RequestBody['HeadonDataID'] = $lastInsertedId;
                    $createdGrade = $this->auditLogger->fetchRowById('CsHeadonRequirementMasters', 'HeadonDataID', $lastInsertedId);
                    $this->auditLogger->logInsert('CsHeadonRequirementMasters', $lastInsertedId, $createdGrade, $RequestBody, [
                        'ReferenceNo' => $ProductForm,
                        'Remarks' => 'Cold store HL count grade created',
                    ]);
                    $this->Error = null;
                    $this->Msg = 'Grade(HL Count) Posted Successfully';
                } else {
                    $this->Error = 'Unable Save Data';
                    $this->Msg = 'Unable Save Data';
                }
            } else {
                $this->Error = 'Data is Already Exists';
                $this->Msg = 'Data is Already Exists';
            }
        } else {

            if ($this->checkValidCombination($RequestBody)) {
                $existingGrade = $this->auditLogger->fetchRowById('CsHeadonRequirementMasters', 'HeadonDataID', $HeadonDataID);

                $sqlUpdate = "UPDATE CsHeadonRequirementMasters SET TypeProduct = '$TypeProduct', GlazeType = '$GlazeType', ShortName = '$ProductForm',
                    Treatment = '$Treatment',Grade = '$Grade',FreezingType = '$FreezingType',Species = '$Species', Status = '$Status',
                    PreparationType = '$PreparationType', PackingCount = '$PackingCount', HeadonCount = '$HeadonCount',Yield = '$Yield',PostedDate = '$PostedDate',
                    GlazeStatus = '$GlazeStatus',GlazePercentage = '$GlazePercentage'
                    WHERE HeadonDataID = '$HeadonDataID'";

                $stmtUpdate = $this->conn->prepare($sqlUpdate);
                $stmtUpdate->execute();

                if ($stmtUpdate) {
                    $updatedGrade = $this->auditLogger->fetchRowById('CsHeadonRequirementMasters', 'HeadonDataID', $HeadonDataID);
                    $this->auditLogger->logUpdate('CsHeadonRequirementMasters', $HeadonDataID, $existingGrade, $updatedGrade, $RequestBody, [
                        'ReferenceNo' => $ProductForm,
                        'Remarks' => 'Cold store HL count grade updated',
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

        $getData = $this->getGradeDetails($RequestBody);
        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        $Data['Data'] = $getData['Data'];

        return $Data;
    }

    function checkValidCombination($RequestBody)
    {

        $isValid = false;


        $HeadonDataID = $RequestBody['HeadonDataID'];

        if ($HeadonDataID != '') {
            $Qry = "and HeadonDataID != '$HeadonDataID'";
        }
        $Species = $RequestBody['Species'];
        $TypeProduct = $RequestBody['TypeProduct'];
        $ProductForm = $RequestBody['ProductForm'];
        $FreezingType = $RequestBody['FreezingType'];
        $Treatment = $RequestBody['Treatment'];
        $Grade = $RequestBody['Grade'];


        $sqlCheckInsert = "SELECT count(*) as Count FROM `CsHeadonRequirementMasters` WHERE Species='$Species' AND TypeProduct='$TypeProduct' AND 
        ShortName='$ProductForm' and  Treatment='$Treatment' 
        AND Grade='$Grade' AND FreezingType='$FreezingType'  $Qry";
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

    public function deleteGradeDetails($RequestBody)
    {
        $HeadonDataID = $RequestBody['HeadonDataID'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];
        $existingGrade = $this->auditLogger->fetchRowById('CsHeadonRequirementMasters', 'HeadonDataID', $HeadonDataID);

        $sqlDelete = "DELETE FROM CsHeadonRequirementMasters WHERE HeadonDataID='$HeadonDataID'";
        $stmtDelete = $this->conn->prepare($sqlDelete);
        $stmtDelete->execute();

        if ($stmtDelete) {
            $this->auditLogger->logDelete('CsHeadonRequirementMasters', $HeadonDataID, $existingGrade, $RequestBody, [
                'ReferenceNo' => isset($existingGrade['ShortName']) ? $existingGrade['ShortName'] : null,
                'Remarks' => 'Cold store HL count grade deleted',
            ]);
            $getData = $this->getGradeDetails($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'Grade(HL Count) Deleted Successfully';
            $Data['Data'] = $getData['Data'];
        } else {
            $Data['Error'] = 'Unable to Delete';
            $Data['Msg'] = 'Unable to Delete';
        }

        return $Data;
    }


    function getGradeDetails($RequestBody)
    {
        $RecordsPerPage = 100;
        $HeadonDataID = $RequestBody['HeadonDataID'];
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

        $sqlSpeciesCount = "SELECT COUNT(*) as Total FROM CsHeadonRequirementMasters WHERE 1=1 $Query";
        $stmt = $this->conn->prepare($sqlSpeciesCount);
        $stmt->execute();

        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "HeadonDataID, TypeProduct, ShortName, GlazeType, Treatment, Grade, FreezingType, Species, PreparationType,PackingCount,
        HeadonCount, Yield, PostedDate, PostedUserID, CHD.Status, GlazeStatus, GlazePercentage,SPM.PreparationName,SpeciesName,TreatmentName";

        if ($HeadonDataID != '') {
            $sqlQuery = "SELECT $sqlFields FROM `CsHeadonRequirementMasters` AS CHD
            LEFT JOIN ScrmPreparationMaster AS SPM ON CHD.PreparationType = SPM.ShortCode
            LEFT JOIN ScrmSpeciesMaster AS SSM ON CHD.Species = SSM.ShortCode
            LEFT JOIN ScrmTreatmentsMaster AS STM ON CHD.Treatment = STM.ShortCode    
            WHERE HeadonDataID='$HeadonDataID'";
        } else {
            $sqlQuery = "SELECT $sqlFields FROM `CsHeadonRequirementMasters` AS CHD
            LEFT JOIN ScrmPreparationMaster AS SPM ON CHD.PreparationType = SPM.ShortCode
            LEFT JOIN ScrmSpeciesMaster AS SSM ON CHD.Species = SSM.ShortCode
            LEFT JOIN ScrmTreatmentsMaster AS STM ON CHD.Treatment = STM.ShortCode    
            WHERE 1=1 $Query ORDER by HeadonDataID desc " . $Limit;
        }

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
            $ArrayGradeList['HeadonDataID'] = $dataRow['HeadonDataID'];
            $ArrayGradeList['TypeProduct'] = $dataRow['TypeProduct'];
            $ArrayGradeList['ShortName'] = $dataRow['ShortName'];
            $ArrayGradeList['GlazeType'] = $dataRow['GlazeType'];
            $ArrayGradeList['Treatment'] = $dataRow['Treatment'];
            $ArrayGradeList['TreatmentName'] = $dataRow['TreatmentName'];
            $ArrayGradeList['Grade'] = $dataRow['Grade'];
            $ArrayGradeList['FreezingType'] = $dataRow['FreezingType'];
            $ArrayGradeList['Species'] = $dataRow['Species'];
            $ArrayGradeList['SpeciesName'] = $dataRow['SpeciesName'];
            $ArrayGradeList['PreparationType'] = $dataRow['PreparationType'];
            $ArrayGradeList['PreparationName'] = $dataRow['PreparationName'];
            $ArrayGradeList['PackingCount'] = $dataRow['PackingCount'];
            $ArrayGradeList['HeadonCount'] = $dataRow['HeadonCount'];
            $ArrayGradeList['Yield'] = $dataRow['Yield'];
            $ArrayGradeList['Status'] = $dataRow['Status'];
            $ArrayGradeList['GlazeStatus'] = $dataRow['GlazeStatus'];
            $ArrayGradeList['GlazePercentage'] = $dataRow['GlazePercentage'];
            $ArrayGradeList['EditLink'] = "grade-master-hl-count?HeadonDataID=" . $dataRow['HeadonDataID'];

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
