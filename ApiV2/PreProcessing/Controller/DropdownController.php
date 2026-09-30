<?php
session_start();

include_once '../Global/Util.php';

// $DeheadingPlantID = $_SESSION['UnitID'];
class DropdownController
{

    private $conn;
    private $db_table = "ScrmBrandsMaster";
    // private $Driver = "1";
    // private $Grader = "2";
    // private $Agent = "3";


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }
    // GET ALL
    public function getDropdowns($RequestBody)
    {

        $Data['Center'] = $this->getCentersList();
        $Data['Plant'] = $this->getProductionPlantsList();
        $Data['Shift'] = $this->getShiftsList();
        $Data['ProductionShift'] = $this->getProductionShifts();
        $Data['ProductionLine'] = $this->getProductionLine($RequestBody);
        $Data['Supervisor'] = $this->getSupervisorsList();
        $Data['Gradingmachine'] = $this->getGradingMachinesList();
        $Data['Defects'] = $this->getDefectsList();
        $Data['Variety'] = $this->getVarietiesList(); // depreceated
        $Data['Contractor'] = $this->getContractorsList();
        $Data['SubContractor'] = $this->getSubContractorsList();
        $Data['HlVaBillVariety'] = $this->getHlVaBillVarietiesList(); //depreacted
        $Data['ProductList'] = $this->getProductList();
        // PreProcessing @Durga
        $Data['FreezingType'] = $this->getFreezingType();
        $Data['ProductType'] = $this->getProductType();
        $Data['Preparation'] = $this->getPreparation();
        $Data['TypeGlaze'] = $this->getTypeGlaze();
        $Data['Treatment'] = $this->getTreatment();
        $Data['Brand'] = $this->getBrand();
        $Data['Grade'] = $this->getGrade();
        $Data['PackingStyle'] = $this->getPackingStyle();
        $Data['GroupCompany'] = $this->getGroupCompany();
        $Data['Wages'] = $this->getWages();


        return $Data;
    }

    function getWages()
    {
        $sqlQuery = "SELECT OptID,Name,Value FROM AdminGeneralOptions WHERE OptionType='WAGE_TYPE' and Status=1 ORDER by SortOrder asc";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }


    function getCentersList()
    {


        $sqlQuery = "SELECT StationID,UnitName FROM `PpcVAStations`";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getProductionPlantsList()
    {


        $sqlQuery = "SELECT ProcessingStationID,StationName,FullName FROM `RmProcessingStations` WHERE StationStatus=1 ORDER BY SortOrder ASC";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }
    function getShiftsList()
    {


        $sqlQuery = "SELECT ShiftID as OptID,ShiftName as Name FROM `AdminShiftDetails`";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getProductionShifts()
    {
        $sqlQuery = "SELECT OptID,Name,Value FROM AdminGeneralOptions WHERE OptionType='Shifts' and Status=1 ORDER by SortOrder asc";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getProductionLine($RequestBody = null)
    {
        $sqlQuery = "SELECT OptID,Name,Value FROM AdminGeneralOptions WHERE OptionType='IQFLine' and Status=1 ";
        if(!empty($RequestBody['LoadingLinersBasedOnPlant']) && !empty($RequestBody['UnitId'])){
            $sqlQuery .= "  AND FIND_IN_SET(".$RequestBody['UnitId'].", UnitIds) > 0 ";
        }
        $sqlQuery .= " ORDER by SortOrder asc";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }
    function getSupervisorsList()
    {


        $sqlQuery = "SELECT ID,UserName FROM `RmUsersList` WHERE UserType=5";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }
    function getGradingMachinesList()
    {
        $sqlQuery = "SELECT MachineID,MachineName, UnitID FROM `AdminGradingMachines`";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }


    function getDefectsList()
    {


        $sqlQuery = "SELECT DefectID ,DefectName  FROM `RmDefectsList`";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }

        return $Data;
    }
    function getVarietiesList()
    {


        $sqlQuery = "SELECT OptID,Name FROM `PpcOptions` WHERE Value='VARIETY' and Status=1 ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }

        return $Data;
    }

    function getProductList()
    {


        $sqlQuery = "SELECT ProductID,ShortName FROM `ScrmProductList` WHERE  Status=1 ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }

        return $Data;
    }

    function getHlVaBillVarietiesList()
    {


        $sqlQuery = "SELECT VarietyID,ShortName, Species FROM `PpcHLVAVarietyList`"; //PpcHLVAVarietyList
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }
    function getContractorsList()
    {


        $sqlQuery = "SELECT ContrctrName,ContractorID,Type FROM `PpcContractMaster` WHERE Status=1 ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }

        return $Data;
    }



    function getSubContractorsList()
    {


        $sqlQuery = "SELECT SubContractorName,SubContactorID,ContactorID FROM `PpcSubContractorMaster`  ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }

        return $Data;
    }
    // PreProcessing @Durga
    function getFreezingType()
    {


        $sqlQuery = "SELECT * FROM `ScrmFreezingTypeMaster` ORDER BY SortOrder asc";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }

        return $Data;
    }
    function getProductType()
    {

        $Data = array("RAW", "COOKED", "BLANCHED");

        return $Data;
    }
    function getPreparation()
    {


        $sqlQuery = "SELECT PreparationName FROM `ScrmPreparationMaster` ORDER BY SortOrder asc";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }

        return $Data;
    }
    function getTypeGlaze()
    {


        $sqlQuery = "SELECT * FROM `ScrmGlazeTypeMaster` where Status=1 ORDER BY SortOrder asc";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }

        return $Data;
    }
    function getTreatment()
    {


        $sqlQuery = "SELECT * FROM `ScrmTreatmentsMaster` where Status=1 ORDER BY SortOrder asc";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }

        return $Data;
    }
    function getBrand()
    {


        $sqlQuery = "SELECT BrandID,BrandName FROM `ScrmBrandsMaster` where Status=1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }

        return $Data;
    }
    function getGrade()
    {

        $sqlQuery = "SELECT ID,GradeGroup,value FROM `ProductionGrades`";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }

        return $Data;
    }
    function getPackingStyle()
    {

        $sqlQuery = "SELECT PCKID,PackingStyle FROM `AdminPackingStyles` ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }

        return $Data;
    }
    function getGroupCompany()
    {

        $sqlQuery = "SELECT ConID,ConsigneeName FROM `ScrmConsigneeDetails`";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }

        return $Data;
    }
}
