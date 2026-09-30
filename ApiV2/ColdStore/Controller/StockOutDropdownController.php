<?php

include_once '../Global/Util.php';
// include_once 'Controller/DropdownController';

class StockOutDropdownController
{

    private $conn;
    private $db_table = "ScrmBrandsMaster";

    // private $dropdowns;
    private $Driver = "1";
    private $Grader = "2";
    private $Agent = "3";


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        // $this->dropdowns = new DropdownController($this->conn);
    }
    // GET ALL
    public function getDropdowns($RequestBody)
    {
        $exclude_list_array = $RequestBody['exclude_list'] ? $RequestBody['exclude_list'] : [];
        $UnitId = $RequestBody['UnitId'];
        $Data['ColdStores'] = in_array("ColdStores", $exclude_list_array) ? [] : $this->getStores();
        $Data['Blocks'] = in_array("Blocks", $exclude_list_array) ? [] : $this->getStoreBlocks();
        $Data['StockBelongsTo'] = in_array("StockBelongsTo", $exclude_list_array) ? [] : $this->getStockBelongsTo();
        $Data['ProductGroup'] = in_array("ProductGroup", $exclude_list_array) ? [] : $this->getProductGroup();
        $Data['TypeProduct'] = in_array("TypeProduct", $exclude_list_array) ? [] : $this->getTypeProduct();
        $Data['Species'] = in_array("Species", $exclude_list_array) ? [] : $this->getSpecies();
        $Data['Brand'] = in_array("Brand", $exclude_list_array) ? [] : $this->getBrand();
        $Data['PackingType'] = in_array("PackingType", $exclude_list_array) ? [] : $this->getPackingType();
        $Data['FreezingType'] = in_array("FreezingType", $exclude_list_array) ? [] : $this->getFreezingType();
        $Data['Soaking'] = in_array("Soaking", $exclude_list_array) ? [] : $this->getSoaking();
        $Data['ProductCategory'] = in_array("ProductCategory", $exclude_list_array) ? [] : $this->getProductCategory();
        $Data['Abstatus'] = in_array("Abstatus", $exclude_list_array) ? [] : $this->getAbstatus();
        $Data['Grade'] = in_array("Grade", $exclude_list_array) ? [] : $this->getGrade();
        $Data['VarietyList'] = in_array("VarietyList", $exclude_list_array) ? [] : $this->getVarietyList();
        $Data['PoNumber'] = in_array("PoNumber", $exclude_list_array) ? [] : $this->getPoNumber($UnitId);
        $Data['PackingStyle'] = in_array("PackingStyle", $exclude_list_array) ? [] : $this->getPackingStyle();
        $Data['CsTransactionTypes'] = in_array("CsTransactionTypes", $exclude_list_array) ? [] : $this->getCsTransactionTypes();
        $Data['OutPoNumbers'] = in_array("OutPoNumbers", $exclude_list_array) ? [] : $this->getOutPoNumbers($UnitId);

        return $Data;
    }


    function getStores()
    {
        $sqlQuery = "SELECT StoreID ,StoreName,StoreBlocks,ProductionPlantID  FROM `CsStores` ORDER BY StoreID";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }


    function getStoreBlocks()
    {
        $sqlQuery = "SELECT BlockID ,ProcessingStationID as StoreID,BlockName,Floors  FROM `CsStoresBlockData`";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }


    function getStoreFloors()
    {
        $sqlQuery = "SELECT FloorID ,BlockID ,FloorName  FROM `CsStoresFloorData`";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getStock()
    {
        $sqlQuery = "SELECT StockID  ,AvalMcs FROM `CsStockData`";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getStockBelongsTo()
    {
        $sqlQuery = "SELECT TenantID,TenantName FROM `CsTenant`";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }
    function getProductGroup()
    {
        $sqlQuery = "SELECT Name FROM AdminGeneralOptions WHERE OptionType='PRODUCT_GROUP' and Status=1 ORDER by SortOrder asc";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }
    function getBrand()
    {
        $sqlQuery = "SELECT BrandID, BrandName FROM ScrmBrandsMaster WHERE Status=1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }
    function getTypeProduct()
    {
        $Data = ["RAW", "COOKED", "BLANCHED"];

        return $Data;
    }
    function getType()
    {
        $Data = ["MCS", "KGs"];

        return $Data;
    }
    function getSpecies()
    {
        $sqlQuery = "SELECT ID, SpeciesName, ShortCode, Description FROM ScrmSpeciesMaster WHERE Status=1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }


    function getShifts()
    {
        $sqlQuery = "SELECT OptID,Name,Value FROM AdminGeneralOptions WHERE OptionType='Shifts' and Status=1 ORDER by SortOrder asc";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getPackingType()
    {
        $sqlQuery = "SELECT OptID,Name,Value FROM AdminGeneralOptions WHERE OptionType='PACKING_TYPE' and Status=1 ORDER by SortOrder asc";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getFreezingType()
    {
        $sqlQuery = "SELECT ID, FreezingTypeName, ShortCode FROM ScrmFreezingTypeMaster WHERE Status=1 ORDER BY SortOrder asc";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getSoaking()
    {
        $sqlQuery = "SELECT TreatmentName,ShortCode FROM ScrmTreatmentsMaster WHERE Status=1 order by SortOrder asc";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getProductCategory()
    {
        $sqlQuery = "SELECT OptID,Name,Value FROM AdminGeneralOptions WHERE OptionType='PRODUCT_CATEGORY' and Status=1 ORDER by SortOrder asc";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getAbstatus()
    {
        $sqlQuery = "SELECT OptID,Name,Value FROM AdminGeneralOptions WHERE OptionType='Abstatus' and Status=1 ORDER by SortOrder asc";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getOutType()
    {
        $sqlQuery = "SELECT OptID,Name,Value FROM AdminGeneralOptions WHERE OptionType='CS_STOCK_OUT_OPTIONS' and Status=1 ORDER by SortOrder asc";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }
    function getOptType()
    {
        $sqlQuery = "SELECT DISTINCT OptionType FROM AdminGeneralOptions WHERE Status=1 ORDER by SortOrder asc";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getGrade()
    {
        $sqlQuery = "SELECT ID,value,Options FROM ProductionGrades ORDER BY SortOrder asc";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }


    function getVarietyList()
    {
        /*$sqlQuery = "SELECT  ProductID,TypeProduct,ShortName,Species FROM `ScrmPIVarietyList` AS SPVL
        LEFT JOIN CsStockData AS CSD ON SPVL.VarietyID=CSD.VarietyID
        WHERE SPVL.Status=1 AND SPVL.VarietyID IN (SELECT CSD.VarietyID FROM CsStockData WHERE AvalMcs >0 or LooseBags>0) 
        GROUP by ShortName order by ShortName ASC";*/
        $sqlQuery = "WITH AvailableVarieties AS ( SELECT DISTINCT VarietyID FROM CsStockData WHERE AvalMcs > 0 OR LooseBags > 0 ) SELECT DISTINCT SPVL.ProductID, SPVL.TypeProduct, SPVL.ShortName, SPVL.Species FROM ScrmPIVarietyList AS SPVL JOIN AvailableVarieties AS AV ON SPVL.VarietyID = AV.VarietyID WHERE SPVL.Status = 1 GROUP BY SPVL.ShortName ORDER BY SPVL.ShortName ASC";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $dataRow['DisplayName'] = $dataRow['ShortName'];
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getPoNumber()
    {
        // $sqlQuery = "SELECT CSD.PoID, SPD.PoNo,SPD.ProductionPlantID,CSD.Grade,CSD.VarietyID,CSD.PackingStyleID,
        // GROUP_CONCAT(DISTINCT CSD.PackingStyleID SEPARATOR ',') AS PackingStyles FROM CsStockData AS CSD
        // LEFT JOIN ScrmPoData as SPD ON SPD.PoID = CSD.PoID 
        // WHERE CSD.PoID!='' GROUP BY CSD.PoID ORDER BY CSD.PoID";
        $Query = '';
        if(!empty($Plant) && $Plant != '' && $Plant != 0){
           $Query = ' AND (FIND_IN_SET($Plant,SPD.ProductionPlantID) > 0)';
        }
        $sqlQuery = "SELECT
    SPD.PoID,
    SPD.PoNo,
    SPD.ProductionPlantID,
    CSD.Grade,
    CSD.VarietyID,
    GROUP_CONCAT(DISTINCT CSD.PackingStyleID ORDER BY CSD.PackingStyleID ASC SEPARATOR ',') AS PackingStyles
FROM
    ScrmPoData AS SPD
LEFT JOIN 
    CsStockData AS CSD ON SPD.PoID = CSD.PoID
WHERE
    SPD.Status NOT IN (2, 3, 4)
    AND (CSD.AvalMcs > 0 OR CSD.LooseBags > 0)$Query
GROUP BY
    SPD.PoID
ORDER BY
    SPD.PoID ASC";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getPackingStyle()
    {
        $sqlQuery = "SELECT PCKID, PackingStyle,TotalSlabs FROM AdminPackingStyles WHERE Status=1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getProductionPlantsList()
    {


        $sqlQuery = "SELECT ProcessingStationID,StationName FROM `RmProcessingStations` WHERE StationStatus=1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getProductionGblCtgList()
    {


        $sqlQuery = "SELECT  CategoryName FROM AdminGeneralCategories WHERE Status=1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getStatus()
    {

        $Data = array(0 => "In-Active", 1 => "Active");

        return $Data;
    }

    function getTenantType()
    {
        // Tenant Type --> dropdown --> For Other(1),For Own(0)
        $Data = array(0 => "For Own", 1 => "For Other");

        return $Data;
    }

    function getProductionLines()
    {
        $sqlQuery = "SELECT OptID,Name,Value FROM AdminGeneralOptions WHERE OptionType='IQFLine' and Status=1 ORDER by SortOrder asc";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getCsTransactionTypes()
    {
        $sqlQuery = "SELECT TrnsID, TransTypeID, TransType, IsReturnable, TransCategory, TransStatus, IsStageSelection  FROM CsTransactionTypes WHERE TransStatus=1 AND TransType NOT IN (3)";
        // SELECT ID, SpeciesName, ShortCode, Description FROM ScrmSpeciesMaster WHERE Status=1
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getOutPoNumbers($Plant)
    {
        // $sqlQuery = "SELECT CSD.PoID, SPD.PoNo,SPD.ProductionPlantID,CSD.Grade,CSD.VarietyID,CSD.PackingStyleID,
        // GROUP_CONCAT(DISTINCT CSD.PackingStyleID SEPARATOR ',') AS PackingStyles FROM CsStockData AS CSD
        // LEFT JOIN ScrmPoData as SPD ON SPD.PoID = CSD.PoID 
        // WHERE CSD.PoID!='' GROUP BY CSD.PoID ORDER BY CSD.PoID";
       $Query = '';
        if (!empty($Plant) && $Plant != '' && $Plant != 0) {
            $Query = " AND (FIND_IN_SET(" . intval($Plant) . ", SPD.ProductionPlantID) > 0)";
        }
        $sqlQuery = "SELECT
    SPD.PoID,
    SPD.PoNo,
    SPD.ProductionPlantID,
    CSD.Grade,
    CSD.VarietyID,
    GROUP_CONCAT(DISTINCT CSD.PackingStyleID ORDER BY CSD.PackingStyleID ASC SEPARATOR ',') AS PackingStyles
FROM
    ScrmPoData AS SPD
LEFT JOIN 
    CsStockData AS CSD ON SPD.PoID = CSD.PoID
WHERE
    (SPD.Status NOT IN (2, 3, 4)
    AND (CSD.AvalMcs > 0 OR CSD.LooseBags > 0)) OR (SPD.Status IN (4)
    AND (CSD.AvalMcs > 0 OR CSD.LooseBags > 0) AND CSD.ProductionTypeID='Return Container' AND CSD.StockStatus=1)$Query
GROUP BY
    SPD.PoID
ORDER BY
    SPD.PoID ASC";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }
}
