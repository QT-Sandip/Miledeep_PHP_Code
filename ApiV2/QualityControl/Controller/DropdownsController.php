<?php

include_once '../Global/Util.php';

class DropdownsController
{

    private $conn;
    private $db_table = "ScrmBrandsMaster";
    private $Driver = "1";
    private $Grader = "2";
    private $Agent = "3";


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }
    // GET ALL
    public function getDropdowns()
    {

        $Data['Plant'] = $this->getProductionPlantsList();
        $Data['LabNames'] = $this->getLabNames();
        $Data['TestList'] = $this->getTestList();
        $Data['LbuDestination'] = $this->getDestination();
        $Data['AuditCategory'] = $this->getAuditCategory();
        $Data['AuditCategoryOptions'] = $this->getAuditCategoryOptions();
        $Data['ProductType'] = $this->getProductType();
        $Data['HlVaBillVariety'] = $this->getHlVaBillVarietiesList();
        $Data['Species'] = $this->getSpecies();
        $Data['CertificationBody'] = $this->getCertificationBody();
        $Data['CategoryOptions'] = $this->getCategoryOptions();
        $Data['PaymentTypes'] = $this->getPaymentTypes();
        $Data['GovernmentIssuingAuthority'] = $this->getGovernmentIssuingAuthority();
        $Data['Farms'] = $this->getFarms();
        $Data['Farmers'] = $this->getFarmer();
        $Data['SiteOptions'] = $this->getSiteOptions();
        $Data['HatcheryOptions'] = $this->getHatcheryOptions();
        $Data['Hatchery'] = $this->getHatchery();
        $Data['Grade'] = $this->getGrade();
        $Data['ImproperPeeling'] = $this->getImproperPeeling();
         $Data['ImproperCutting'] = $this->getImproperCutting();
        $Data['Vein'] = $this->getVein();
        $Data['Broken'] = $this->getBroken();
        $Data['OtherObjectionDefects'] = $this->getOtherObjectionDefects();
         $Data['CorrectiveAction'] = $this->getCorrectiveAction();
         $Data['Bap'] = $this->getBap();
        $Data['TypeOfCut'] = $this->getTypeOfCut();
         $Data['Center'] = $this->getCentersList();

        return $Data;
    }


    function getLabNames()
    {
        $sqlQuery = "SELECT LabId, LabName FROM `QcEiaLabNames`";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getDestination()
    {
        $sqlQuery = "SELECT LUID, LbuName, LbuDesignation, CONCAT(LbuName, ' - ', LbuDesignation) AS LbuND, PlantID FROM `QcLabTechnologist`";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getTestList()
    {
        $sqlQuery = "SELECT DISTINCT(TestCategory) FROM `QcTestList`";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getAuditCategory()
    {
        $sqlQuery = "SELECT AuditID ,CategoryName,PlantID,AuditCertificationNo FROM `QcAuditCategoriesMaster` WHERE Status=1 ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getAuditCategoryOptions()
    {
        $sqlQuery = "SELECT AuditOptionID,AuditID,OptionName FROM `QcAuditCategoriesOptionsMaster` WHERE Status=1 ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }



    function getProductionPlantsList()
    {


        $sqlQuery = "SELECT ProcessingStationID,StationName,FullName FROM `RmProcessingStations` WHERE StationStatus=1 ORDER by SortOrder Asc";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }
    function getProductType()
    {

        $Data = array("RAW", "COOKED");

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

    function getSpecies()
    {
        $sqlQuery = "SELECT ID as SpeciesID,SpeciesName,ShortCode,Description FROM `ScrmSpeciesMaster` WHERE Status=1 ORDER by SortOrder Asc";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getCertificationBody()
    {
        $sqlQuery = "SELECT CertificationBodyID, AuditNameID, PlantID, CertificationBodyName FROM `QcAuditCertificationBodyMaster` WHERE Status=1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getCategoryOptions()
    {
        // $sqlQuery = "SELECT  OptionCategoryName,PlantID FROM QcAuditDropdownMasters GROUP BY OptionCategoryName";
        // $stmt = $this->conn->prepare($sqlQuery);
        // $stmt->execute();
        // while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
        //     $Data[] = $dataRow;
        // }
        // return $Data;

        $Data = array("Audit Hatchery", "Site Options", "Audit Farms", "Government Issuing Authority", "Type of Payment");

        return $Data;
    }

    function getPaymentTypes()
    {
        $sqlQuery = "SELECT  OptionID,OptionName,OptionCategoryName,PlantID FROM QcAuditDropdownMasters 
        WHERE OptionCategoryName = 'Type of Payment' AND Status = '1'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getGovernmentIssuingAuthority()
    {
        $sqlQuery = "SELECT  OptionID,OptionName,OptionCategoryName,PlantID FROM QcAuditDropdownMasters 
        WHERE OptionCategoryName = 'Government Issuing Authority' AND Status = '1'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getFarms()
    {
        $sqlQuery = "SELECT  OptionID,OptionName,OptionCategoryName,PlantID FROM QcAuditDropdownMasters 
        WHERE OptionCategoryName = 'Audit Farms' AND Status = '1'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getSiteOptions()
    {
        $sqlQuery = "SELECT  OptionID,OptionName,OptionCategoryName,PlantID FROM QcAuditDropdownMasters 
        WHERE OptionCategoryName = 'Site Options' AND Status = '1'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getHatcheryOptions()
    {
        $sqlQuery = "SELECT  OptionID,OptionName,OptionCategoryName,PlantID FROM QcAuditDropdownMasters 
        WHERE OptionCategoryName = 'Audit Hatchery' AND Status = '1'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getFarmer()
    {
        $sqlFields = "FarmID, FarmName, FarmRegistrationNo, FarmerName, FarmerAddress, PhoneNumber, TonnageValue, Status";

        $sqlQuery = "SELECT $sqlFields FROM QcAuditFarmMasters WHERE Status = '1'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getHatchery()
    {
        $sqlFields = "HatcheryNameID, HatcheryName, HatcheryLocation, PlantID, Status";

        $sqlQuery = "SELECT $sqlFields FROM QcAuditHatcheryMasters WHERE Status = '1'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getGrade()
    {

        $sqlQuery = "SELECT ID,GradeGroup,`value` FROM `ProductionGrades`";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }

        return $Data;
    }

    
    function getImproperPeeling()
{
      $sqlFields = "ID, Type, Name";
    $sqlQuery = "SELECT $sqlFields FROM QcImproperTypes WHERE Type = 'PEELING'";
    $stmt = $this->conn->prepare($sqlQuery);
    $stmt->execute();

    $Data = [];
    while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $Data[] = (object)$dataRow; 
    }
    return $Data;
}

 
    function getImproperCutting()
{
      $sqlFields = "ID, Type, Name";
    $sqlQuery = "SELECT $sqlFields FROM QcImproperTypes WHERE Type = 'CUTTING'";
    $stmt = $this->conn->prepare($sqlQuery);
    $stmt->execute();

    $Data = [];
    while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $Data[] = (object)$dataRow; 
    }
    return $Data;
}


    function getVein()
{
      $sqlFields = "ID, Type, Name";
    $sqlQuery = "SELECT $sqlFields FROM QcImproperTypes WHERE Type = 'VEIN'";
    $stmt = $this->conn->prepare($sqlQuery);
    $stmt->execute();

    $Data = [];
    while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $Data[] = (object)$dataRow; 
    }
    return $Data;
}

  function getBroken()
{
      $sqlFields = "ID, Type, Name";
    $sqlQuery = "SELECT $sqlFields FROM QcImproperTypes WHERE Type = 'BROKEN'";
    $stmt = $this->conn->prepare($sqlQuery);
    $stmt->execute();

    $Data = [];
    while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $Data[] = (object)$dataRow; 
    }
    return $Data;
}

 function getOtherObjectionDefects()
{
      $sqlFields = "ID, Type, Name";
    $sqlQuery = "SELECT $sqlFields FROM QcImproperTypes WHERE Type = 'OBJECTION_DEFECTS'";
    $stmt = $this->conn->prepare($sqlQuery);
    $stmt->execute();

    $Data = [];
    while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $Data[] = (object)$dataRow; 
    }
    return $Data;
}
function getCorrectiveAction()
{
      $sqlFields = "ID, Type, Name";
    $sqlQuery = "SELECT $sqlFields FROM QcImproperTypes WHERE Type = 'CORRECTIVE_ACTION'";
    $stmt = $this->conn->prepare($sqlQuery);
    $stmt->execute();

    $Data = [];
    while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $Data[] = (object)$dataRow; 
    }
    return $Data;
}

 function getBap()
    {
        $sqlFields = "ID, Name, Status";
        $sqlQuery = "SELECT $sqlFields  FROM BapTypes WHERE Status = '1'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

     
    function getTypeOfCut()
    {
        $sqlFields = "ID, Type, Name";
        $sqlQuery = "SELECT $sqlFields FROM QcImproperTypes WHERE Type = 'CUT'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        $Data = [];
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = (object)$dataRow; 
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

}
