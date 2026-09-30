<?php

include_once '../Global/Util.php';
include_once '../App/Controller/AppUtilController.php';

class DropdownController
{

    private $conn;
    private $db_table = "ScrmBrandsMaster";
    private $Driver = "1";
    private $Grader = "2";
    private $Agent = "3";
    private $appController;

    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->appController = new AppUtilController($this->conn);
    }
    // GET ALL
    public function getDropdowns($RequestBody)
    {
        $exclude_list_array = $RequestBody['exclude_list'] ?$RequestBody['exclude_list']: [];
        $UnitId = $RequestBody['UnitId'];
        $Data['ColdStores'] = in_array("ColdStores", $exclude_list_array) ? [] : $this->getStores();
        $Data['Blocks'] = in_array("Blocks", $exclude_list_array) ? [] : $this->getStoreBlocks();
        $Data['Floors'] = in_array("Floors", $exclude_list_array) ? [] : $this->getStoreFloors();
        $Data['StoreRacks'] = in_array("StoreRacks", $exclude_list_array) ? [] : $this->getStoreRacks();
        $Data['Stock'] = in_array("Stock", $exclude_list_array) ? [] : $this->getStock();
        $Data['StockBelongsTo'] = in_array("StockBelongsTo", $exclude_list_array) ? [] : $this->getStockBelongsTo();
        $Data['ProductGroup'] = in_array("ProductGroup", $exclude_list_array) ? [] : $this->getProductGroup();
        $Data['Exporters'] = in_array("Exporters", $exclude_list_array) ? [] : $this->getExporters();
        $Data['Consignee'] = in_array("Consignee", $exclude_list_array) ? [] : $this->getConsignee();
        $Data['Processor'] = in_array("Processor", $exclude_list_array) ? [] : $this->getProcessor();
        $Data['Shifts'] = in_array("Shifts", $exclude_list_array) ? [] : $this->getShifts();
        $Data['PackingType'] = in_array("PackingType", $exclude_list_array) ? [] : $this->getPackingType();
        $Data['FreezingType'] = in_array("FreezingType", $exclude_list_array) ? [] : $this->getFreezingType();
        $Data['Soaking'] = in_array("Soaking", $exclude_list_array) ? [] : $this->getSoaking();
        $Data['ProductCategory'] = in_array("ProductCategory", $exclude_list_array) ? [] : $this->getProductCategory();
        $Data['Abstatus'] = in_array("Abstatus", $exclude_list_array) ? [] : $this->getAbstatus();
        $Data['OptType'] = in_array("OptType", $exclude_list_array) ? [] : $this->getOptType();
        $Data['OutType'] = in_array("OutType", $exclude_list_array) ? [] : $this->getOutType();
        $Data['Grade'] = in_array("Grade", $exclude_list_array) ? [] : $this->getGrade();
        $Data['Variety'] = in_array("Variety", $exclude_list_array) ? [] : $this->getVariety();
        $Data['VarietyList'] = in_array("VarietyList", $exclude_list_array) ? [] : $this->getVarietyList();
        $Data['StockAvailableVariety'] = in_array("StockAvailableVariety", $exclude_list_array) ? [] : $this->getStockAvailableVariety();
        $Data['StockInVarietyList'] = in_array("StockInVarietyList", $exclude_list_array) ? [] : $this->getVarietyListForStockIn();
        $Data['PoNumber'] = in_array("PoNumber", $exclude_list_array) ? [] : $this->getPoNumber($UnitId);
        $Data['PoNumberFreezing'] = in_array("PoNumberFreezing", $exclude_list_array) ? [] : $this->getPoNumberFreezing($UnitId);
        $Data['PackingStyle'] = in_array("PackingStyle", $exclude_list_array) ? [] : $this->getPackingStyle();
        $Data['Plant'] = in_array("Plant", $exclude_list_array) ? [] : $this->getProductionPlantsList();
        $Data['GeneralCategories'] = in_array("GeneralCategories", $exclude_list_array) ? [] : $this->getProductionGblCtgList();
        $Data['Status'] = in_array("Status", $exclude_list_array) ? [] : $this->getStatus();
        $Data['TenantType'] = in_array("TenantType", $exclude_list_array) ? [] : $this->getTenantType();
        $Data['ProductionLines'] = in_array("ProductionLines", $exclude_list_array) ? [] : $this->getProductionLines($RequestBody);
        $Data['Brand'] = in_array("Brand", $exclude_list_array) ? [] : $this->getBrand();
        $Data['BrandType'] = in_array("BrandType", $exclude_list_array) ? [] : $this->getBrandType();
        $Data['Species'] = in_array("Species", $exclude_list_array) ? [] : $this->getSpecies();
        $Data['Tranction'] = in_array("Tranction", $exclude_list_array) ? [] : $this->getTransction();
        $Data['TypeProduct'] = in_array("TypeProduct", $exclude_list_array) ? [] : $this->getTypeProduct();
        $Data['Type'] = in_array("Type", $exclude_list_array) ? [] : $this->getType();
        $Data['CsInwardTransactionTypes'] = in_array("CsInwardTransactionTypes", $exclude_list_array) ? [] : $this->getInwardCsTransactionTypes();
        $Data['CsOutwardTransactionTypes'] = in_array("CsOutwardTransactionTypes", $exclude_list_array) ? [] : $this->getOutWardCsTransactionTypes();
        $Data['CsTransactionTypes'] = in_array("CsTransactionTypes", $exclude_list_array) ? [] : $this->getCsTransactionTypes();
        $Data['ProductForm'] = in_array("ProductForm", $exclude_list_array) ? [] : $this->getProductForm();
        $Data['Preparation'] = in_array("Preparation", $exclude_list_array) ? [] : $this->getPreparation();
        $Data['GlazeType'] = in_array("GlazeType", $exclude_list_array) ? [] : $this->getGlazeType();
        $Data['ShipoutPoNumber'] = in_array("ShipoutPoNumber", $exclude_list_array) ? [] : $this->getShipoutPoNumber($RequestBody);
        $Data['ProductionPlant'] = in_array("ProductionPlant", $exclude_list_array) ? [] : $this->getProductionPlant();
        $Data['PoNumberNew'] = in_array("PoNumberNew", $exclude_list_array) ? [] : $this->getPoNumberNew($UnitId);
        $Data['TransactionTypesNew'] = in_array("TransactionTypesNew", $exclude_list_array) ? [] : $this->getCsTransactionTypesNew();
        $Data['InwardGroups'] = in_array("InwardGroups", $exclude_list_array) ? [] : $this->getStockInProductGroup();
        $Data['CsAllTransactionTypes'] = in_array("CsAllTransactionTypes", $exclude_list_array) ? [] : $this->getAllCsTransactionTypes();

        return $Data;
    }


    function getStores()
    {
        $sqlQuery = "SELECT StoreID ,StoreName,StoreBlocks,ProductionPlantID  FROM `CsStores` WHERE StoreStatus=1 ORDER BY StoreID ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }


    function getStoreBlocks()
    {
        $sqlQuery = "SELECT BlockID ,ProcessingStationID as StoreID,BlockName,Floors  FROM `CsStoresBlockData`WHERE BlockStatus=1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }


    function getStoreFloors()
    {
        $sqlQuery = "SELECT FloorID ,BlockID ,FloorName  FROM `CsStoresFloorData`WHERE Status=1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getStoreRacks()
    {
        $sqlQuery = "SELECT RowID ,FloorID ,RackName  FROM `CsStoresRackData` WHERE Status=1 AND RackName != ''";
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

    function getConsignee()
    {

        $sqlQuery = "SELECT  ConID,ConsigneeName FROM `ScrmConsigneeDetails`";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }

        return $Data;
    }

    function getProcessor()
    {

        $sqlQuery = "SELECT  ExpID as ProcID ,ExpName as ProcName FROM `AdminExporterDetails` where ExpType=2 ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }

        return $Data;
    }

    function getExporters()
    {
        $sqlQuery = "SELECT ExpID,ExpName FROM `AdminExporterDetails` WHERE ExpType=1 ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getStockBelongsTo()
    {
        $sqlQuery = "SELECT TenantID,TenantName,ProcessorID FROM `CsTenant` WHERE TenantStatus=1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }
    function getProductGroup()
    {
        // $sqlQuery = "SELECT Name FROM AdminGeneralOptions WHERE OptionType='PRODUCT_GROUP' and Status=1 ORDER by SortOrder asc";
        $sqlQuery = "SELECT TransCategory as Name FROM `CsTransactionTypes` WHERE TransType=2 and IsReturnable=1 and IsActive=1";
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
        $sqlQuery = "SELECT CategoryName as OptionType FROM AdminGeneralCategories WHERE Status=1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getGrade()
    {
        $sqlQuery = "SELECT ID,value,Options FROM ProductionGrades ORDER BY ABS(value) asc";
        // $sqlQuery = "SELECT ID,value,Options FROM ProductionGrades ORDER BY SortOrder asc";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getVariety()
    {
        $sqlQuery = "SELECT VarietyID,ShortName,ProductDescription,Grade,FullName,DeviSUPC,TypeProduct FROM ScrmPIVarietyList WHERE Status=1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }
    function getVarietyList() // 16-03-2024 store-on-floor-stock
    {
        $sqlQuery = "SELECT  ProductID,TypeProduct,ShortName,Species FROM `ScrmPIVarietyList` WHERE Status=1 GROUP by  ShortName";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $dataRow['DisplayName'] = $dataRow['ShortName'];
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getVarietyListForStockIn() // 16-03-2024 store-on-floor-stock
    {
        $sqlQuery = "SELECT  VarietyID,TypeProduct,ShortName,Species FROM `ScrmPIVarietyList` WHERE Status=1 GROUP by  ShortName";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $dataRow['DisplayName'] = $dataRow['ShortName'];
            $Data[] = $dataRow;
        }
        return $Data;
    }


    function getStockAvailableVariety()
    {
        $sqlQuery = "WITH AvailableVarieties AS ( SELECT DISTINCT VarietyID FROM CsStockData WHERE AvalMcs > 0 OR LooseBags > 0 ) SELECT DISTINCT SPVL.ProductID, SPVL.TypeProduct, SPVL.ShortName, SPVL.Species FROM ScrmPIVarietyList AS SPVL JOIN AvailableVarieties AS AV ON SPVL.VarietyID = AV.VarietyID WHERE SPVL.Status = 1 GROUP BY SPVL.ShortName ORDER BY SPVL.ShortName ASC";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $dataRow['DisplayName'] = $dataRow['ShortName'];
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getPoNumber($Plant)
    {
        $Query = '';
        if(!empty($Plant) && $Plant != '' && $Plant != 0){
           $Query = " AND (FIND_IN_SET($Plant,SPD.ProductionPlantID) > 0)";
        }
        $sqlQuery = "SELECT PoID,PoNo,ProductionPlantID FROM ScrmPoData as SPD 
        WHERE PoID not in (SELECT PiID FROM ScrmCustomInvoices 
        WHERE StockOutDate!='') and Status not in (2,3,4)$Query order by PoID ASC";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getPoNumberFreezing($Plant){
        $Query = '';
        if(!empty($Plant) && $Plant != '' && $Plant != 0){
        //    $Query = " AND RPS.ProcessingStationID=$Plant";
            $Query = " AND (FIND_IN_SET($Plant,SPD.ProductionPlantID) > 0)";
        }
         $sqlQuery = "SELECT PoID,PoNo,ProductionPlantID FROM ScrmPoData as SPD 
         LEFT JOIN AdminExporterDetails AS AED ON SPD.ProcessorID=AED.ExpID
         LEFT JOIN RmProcessingStations AS RPS ON AED.InvShortCode=RPS.ProdCode
        WHERE PoID not in (SELECT PiID FROM ScrmCustomInvoices 
        WHERE StockOutDate!='') and SPD.Status not in (2,3,4) $Query order by PoID ASC";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getPoNumberNew($Plant)
    {
        $Query = '';
        if(!empty($Plant) && $Plant != '' && $Plant != 0){
           $Query = " AND (FIND_IN_SET($Plant,SPD.ProductionPlantID) > 0)";
        }
        $sqlQuery = "SELECT SPD.PoID,SPD.PoNo,SPD.ProductionPlantID,CSD.Grade,CSD.VarietyID,CSD.PackingStyleID,
        GROUP_CONCAT(DISTINCT CSD.PackingStyleID SEPARATOR ',') AS PackingStyles FROM ScrmPoData as SPD 
        LEFT JOIN CsStockData AS CSD ON SPD.PoID = CSD.PoID
        where SPD.PoID not in (SELECT PiID FROM ScrmCustomInvoices 
        WHERE StockOutDate!='')  and SPD.PoID in (SELECT DISTINCT PoID FROM CsStockData) and Status not in (2,3,4)$Query group by SPD.PoID order by SPD.PoID ASC";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getPackingStyle()
    {
        $sqlQuery = "SELECT PCKID, PackingStyle,MFactor,SlabWeight,TotalSlabs FROM AdminPackingStyles WHERE Status=1";
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
        $Data = array(0 => "For Own", 1 => "For Other");
        return $Data;
    }

    function getProductionLines($RequestBody = null)
    {
        $sqlQuery = "SELECT OptID,Name,Value FROM AdminGeneralOptions WHERE OptionType='IQFLine' and Status=1 ";
        if(!empty($RequestBody['LoadingLinersBasedOnPlant']) && !empty($RequestBody['UnitId'])){
            $sqlQuery .= "  AND FIND_IN_SET(".$RequestBody['UnitId'].", UnitIds) > 0 ";
        }
        $sqlQuery .= " ORDER by SortOrder asc";         $stmt = $this->conn->prepare($sqlQuery);
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

    function getBrandType()
    {
        $sqlQuery = "SELECT BrandID, BuyerID, BrandName, Remarks, Status, PrintStatus, BrandType FROM `ScrmBrandsMaster` WHERE BrandType in (0,2)";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }

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
    function getCsTransactionTypes()
    {
        // $sqlQuery = "SELECT TrnsID, TransTypeID, TransType, IsReturnable, TransCategory, TransStatus FROM CsTransactionTypes WHERE TransStatus=1";
        $sqlQuery = "SELECT TrnsID, TransTypeID, TransType, IsReturnable, TransCategory, TransStatus, IsStageSelection FROM CsTransactionTypes 
        WHERE  IsActive=1 AND TransType NOT IN (3)";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getOutWardCsTransactionTypes()
    {
        // $sqlQuery = "SELECT TrnsID, TransTypeID, TransType, IsReturnable, TransCategory, TransStatus FROM CsTransactionTypes WHERE TransStatus=1";
        $sqlQuery = "SELECT TrnsID, TransTypeID, TransType, IsReturnable, TransCategory, TransStatus, IsStageSelection FROM CsTransactionTypes 
        WHERE TransType NOT IN (3)";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getInwardCsTransactionTypes()
    {
        // $sqlQuery = "SELECT TrnsID, TransTypeID, TransType, IsReturnable, TransCategory, TransStatus FROM CsTransactionTypes WHERE TransStatus=1";
        $sqlQuery = "SELECT TrnsID, TransTypeID, TransType, IsReturnable, TransCategory, TransStatus, IsStageSelection FROM CsTransactionTypes 
        WHERE TransType NOT In (2,4)";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getAllCsTransactionTypes()
    {
        // $sqlQuery = "SELECT TrnsID, TransTypeID, TransType, IsReturnable, TransCategory, TransStatus FROM CsTransactionTypes WHERE TransStatus=1";
        $sqlQuery = "SELECT TrnsID, TransTypeID, TransType, IsReturnable, TransCategory, TransStatus, IsStageSelection FROM CsTransactionTypes 
        GROUP BY TransCategory";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getProductForm()
    {
        $sqlQuery = "SELECT ShortName,ProductID FROM ScrmProductList ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getProductionPlant()
    {
        $sqlQuery = "SELECT ProcessingStationID, StationName FROM RmProcessingStations ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }
    function getPreparation()
    {
        $sqlQuery = "SELECT ID,ShortCode,PreparationName FROM ScrmPreparationMaster ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }
    function getShipoutPoNumber($RequestBody)
    {
        $ClientID = $RequestBody['ClientID'];
        $IsCheckStockOutDateAtCs = $this->appController->getClientConfig($ClientID)['IsStockOutDateAtCs'];

        $SqlPoIds = "SELECT GROUP_CONCAT(DISTINCT(CAST(CSD.PoID AS UNSIGNED))) AS PoID  FROM `CsStockData` as CSD
        LEFT JOIN CsStockOutDetails AS CSOD ON CSOD.StockID=CSD.StockID       
        WHERE StockStatus=2 and OutType='Shipment Out'";
        $stmtPoIds = $this->conn->prepare($SqlPoIds);
        $stmtPoIds->execute();
        $dataRowPoIds = $stmtPoIds->fetch(PDO::FETCH_ASSOC);
        $PoID = $dataRowPoIds['PoID'];
        if ($PoID != '') {
            $Query = " and SPD.PoID not in  ($PoID)";
        }
        if ($IsCheckStockOutDateAtCs) {
            $sqlQuery = "SELECT SPD.PoID,SPD.PoNo,ProductionPlantID,
            (select sum(Mcs) from ScrmPoGradeData as SPGD where SPGD.InvId=SPD.PoID ) as Mcs, SPD.ConsigneeID  FROM  ScrmPoData AS SPD
            LEFT JOIN ScrmShipmentDetailsBreif AS SDB ON SPD.PoID=SDB.PiID 
            WHERE (SPD.PoID not in (select PiID from ScrmCustomInvoices where StockOutDateAtCs!='' )) and Status  in(5) $Query";
        } else {
            $sqlQuery = "SELECT SPD.PoID,SPD.PoNo,ProductionPlantID,
            (select sum(Mcs) from ScrmPoGradeData as SPGD where SPGD.InvId=SPD.PoID ) as Mcs, SPD.ConsigneeID  FROM  ScrmPoData AS SPD
            LEFT JOIN ScrmShipmentDetailsBreif AS SDB ON SPD.PoID=SDB.PiID 
            WHERE (SPD.PoID not in (select PiID from ScrmCustomInvoices where StockOutDate!='')) and Status  in(5)
            and  (TransportedDate='' OR TransportedDate IS NULL) $Query";
        }
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getGlazeType()
    {
        $sqlQuery = "SELECT ShortCode,GlazeType FROM ScrmGlazeTypeMaster ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getCsTransactionTypesNew()
    {
        $sqlInwardGroup = "SELECT TransCategory,IsReturnable, IsActive, TransType FROM CsTransactionTypes WHERE IsReturnable=1 AND IsActive=1 ";
        $stmtInwardGroup = $this->conn->prepare($sqlInwardGroup);
        $stmtInwardGroup->execute();
        while ($dataInward = $stmtInwardGroup->fetch(PDO::FETCH_ASSOC)) {
            $InwardGroup['TransCategory'] = $dataInward['TransCategory'];
            $InwardGroup['IsReturnable'] = $dataInward['IsReturnable'];
            $InwardGroup['IsActive'] = $dataInward['IsActive'];
            $InwardGroup['TransType'] = '';
            $InwardGroup['TransCategoryName'] = $dataInward['TransCategory'] . ' +';
            $ArrayInward[] = $InwardGroup;
        }

        $sqlOutwardGroup = "SELECT TransCategory,IsReturnable, IsActive, TransType FROM CsTransactionTypes WHERE TransType=2 AND IsActive=1 ";
        $stmtOutwardGroup = $this->conn->prepare($sqlOutwardGroup);
        $stmtOutwardGroup->execute();
        while ($dataOutward = $stmtOutwardGroup->fetch(PDO::FETCH_ASSOC)) {
            $OutwardGroup['TransCategory'] = $dataOutward['TransCategory'];
            $OutwardGroup['IsReturnable'] = '';
            $OutwardGroup['IsActive'] = $dataOutward['IsActive'];
            $OutwardGroup['TransType'] = $dataOutward['TransType'];
            $OutwardGroup['TransCategoryName'] = $dataOutward['TransCategory'] . ' -';
            $ArrayOutward[] = $OutwardGroup;
        }
        $MergedArray = array_merge($ArrayInward, $ArrayOutward);

        return $MergedArray;
    }

    function getTransction()
    {
        $Data = ["Melting", "ReGlazing", "RePacking"];

        return $Data;
    }
    function getTypeProduct()
    {
        $Data = ["RAW", "COOKED", "BLANCHED"];

        return $Data;
    }
    function getType()
    {
        $Data = ["MCS", "KGS"];

        return $Data;
    }

    function getStockInProductGroup()
    {
        // $sqlQuery = "SELECT Name FROM AdminGeneralOptions WHERE OptionType='PRODUCT_GROUP' and Status=1 ORDER by SortOrder asc";
        $sqlQuery = "SELECT TransCategory as Name FROM `CsTransactionTypes` WHERE TransType=3 and IsReturnable=1 and IsActive=1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }
}