<?php

// include_once '../Global/Util.php';

class DropdownController
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

        /*  
        $Data['PurchaseCenters']=$this->getRmPurchaseCenters();
        $Data['PurchaseCategory']=$this->getPurchaseCategory();
        $Data['Vehicles']=$this->getVehiclesDetails();
        $Data['RmAgents']=$this->getUsers($this->Agent);
        $Data['Drivers']=$this->getUsers($this->Driver);
        $Data['RmGraders']=$this->getUsers($this->Grader);*/
        $Data['Species'] = $this->getSpecies();
        $Data['AdminExporters'] = $this->getExporter();
        $Data['ExporterPacker'] = $this->getExpPacker();
        $Data['FreezingType'] = $this->getFreezingType();
        $Data['Currency'] = $this->getCurrency();
        $Data['Consignee'] = $this->getConsignee();
        $Data['Country'] = $this->getCountry();
        $Data['ShipmentStatusCodes'] = $this->getShipmentStatusCodes();
        $Data['Processor'] = $this->getProcessor();
        $Data['Brand'] = $this->getBrand();
        $Data['TypeProduct'] = $this->getTypeProduct();
        $Data['ShortName'] = $this->getShortName();
        $Data['GradeType'] = $this->getGradeType();
        $Data['BrandType'] = $this->getBrandType();
        $Data['PrintStatus'] = $this->getPrintStatus();
        $Data['GradeOptions'] = $this->getGradeOptions();
        $Data['UOMDetails'] = $this->getUOM();
        $Data['PortDestinations'] = $this->getPortDestinations();
        $Data['CustomsCancelStatus'] = $this->getCustomsCancelStatus();
        $Data['ProductionPlantsList'] = $this->getProductionPlantsList();
        $Data['LinerList'] = $this->getLinersList();
        $Data['VarietyList'] = $this->getVarietyList();
        $Data['Grade'] = $this->getGrade();
        $Data['FreezingType'] = $this->getFreezingType();



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
    /*
    function getRmPurchaseCenters(){


        $sqlQuery = "SELECT * FROM `RmPurchaseStations`";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)){
            $Data[]=$dataRow;
        }
        return $Data;
    }
    function getPurchaseCategory(){


         $sqlQuery = "SELECT CatID as RmCategoryID,PurchaseType as RmCategoryName FROM `RmPurchaseCategory`";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)){
            $Data[]=$dataRow;
        }
        return $Data;
    }
    function getVehiclesDetails(){


        $sqlQuery = "SELECT ID as VehicalID,VehicalNumber FROM `RmVehicalDetails` order by VehicalNumber asc";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        while($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)){
            $Data[]=$dataRow;
        }
        return $Data;
    }
    function getUsers($UserType){


        $sqlQuery = "SELECT ID as UserID ,UserName FROM `RmUsersList`  WHERE UserType='$UserType' order By UserName asc";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)){
            $Data[]=$dataRow;
        }

        return $Data; 
    }

    */

    function getExporter()
    {


        $sqlQuery = "SELECT  ExpID,ExpName FROM `AdminExporterDetails` where ExpType=1 ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }

        return $Data;
    }
    function getCurrency()
    {


        $sqlQuery = "SELECT CurrencyID, CurrencyName, CurrencyCode, CurrenySymbol, CurrencyShortCode FROM `ScrmCurrencyMaster` ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }

        return $Data;
    }
    function getExpPacker()
    {

        $sqlQuery = "SELECT ExpID,ExpName,ExpApprovalNo,ExpType, 
        CASE ExpType WHEN 1 THEN 'Exporter' WHEN 2 THEN 'Processor' WHEN 3 THEN 'Both' END AS ExpTypeName,
        CONCAT(ExpName, ' - ', CASE ExpType WHEN 1 THEN 'Exporter' WHEN 2 THEN 'Processor' WHEN 3 THEN 'Both' END, ' (', ExpApprovalNo, ')') AS ExporterType FROM AdminExporterDetails WHERE Status=1";
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

    function getFreezingType()
    {

        $sqlQuery = "SELECT ShortCode,FreezingTypeName FROM `ScrmFreezingTypeMaster` ORDER BY SortOrder asc";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }

        return $Data;
    }

    function getGradeType()
    {

        $sqlQuery = "SELECT DropCatID,CatName FROM `AdminDropDownCategories` where Status=1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }

        return $Data;
    }

    function getBrandType()
    {

        $sqlQuery = "SELECT BrandID, BuyerID, BrandName, Remarks, Status, PrintStatus, BrandType FROM `ScrmBrandsMaster` WHERE BrandType in (0,1)";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }

        return $Data;
    }

    function getPrintStatus()
    {

        $sqlQuery = "SELECT BrandID, BuyerID, BrandName, Remarks, Status, PrintStatus, BrandType FROM `ScrmBrandsMaster` WHERE BrandType in (0,1)";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }

        return $Data;
    }

    function getGradeOptions()
    {

        $sqlQuery = "SELECT DISTINCT Grade FROM `ProductionGradesMaster`";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }

        return $Data;
    }

    function getCountry()
    {

        $sqlQuery = "SELECT  ID,name  FROM `ScrmCountriesList`";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }

        return $Data;
    }

    function getShipmentStatusCodes()
    {

        $sqlQuery = "SELECT  Code as ID,Name  FROM `ScrmShipmentStatusCodes` WHERE PInvStatus=1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }

        return $Data;
    }

    function getProcessor()
    {


        $sqlQuery = "SELECT  ExpID,ExpName FROM `AdminExporterDetails` where ExpType=2 ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }

        return $Data;
    }

    function getBrand()
    {


        $sqlQuery = "SELECT BrandID as ID,BrandName as Name FROM `ScrmBrandsMaster` WHERE Status=1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }

        return $Data;
    }

    function getTypeProduct()
    {

        $Data = array("RAW", "COOKED", "BLANCHED");

        return $Data;
    }

    function getUOM()
    {

        $Data = ["LBS", "KGS", "GRAMS", "OZ"];
        $result = [];

        foreach ($Data as $key) {
            $result[] = ["UOM" => $key];
        }

        return $result;
        // return $Data;
    }

    function getShortName()
    {

        $sqlQuery = "SELECT DISTINCT ShortName FROM `ScrmPIVarietyList`";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }

        return $Data;
    }

    function getPortDestinations()
    {

        $sqlQuery = "SELECT PortId, Name FROM `ScrmPortCities`";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }

        return $Data;
    }

    function getCustomsCancelStatus()
    {

        $sqlQuery = "SELECT Name,Code as Value FROM ScrmShipmentStatusCodes WHERE CInvStatus=1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }

        return $Data;
    }
    function getProductionPlantsList()
    {


        $sqlQuery = "SELECT ProcessingStationID,StationName,FullName FROM `RmProcessingStations` WHERE StationStatus=1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }


    function getLinersList()
    {
        $SqlLinerMaster = "SELECT * FROM `ScrmLinerMaster`  where Status=1";
        $StmtLinerMaster = $this->conn->prepare($SqlLinerMaster);
        $StmtLinerMaster->execute();
        while ($dataRow = $StmtLinerMaster->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getVarietyList()
    {

        $sqlQuery = "SELECT DISTINCT ShortName,ProductDescription FROM `ScrmPIVarietyList` ORDER BY ShortName";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }

        return $Data;
    }
}
