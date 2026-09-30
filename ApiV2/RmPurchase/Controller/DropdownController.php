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

        $ClientID = $RequestBody['ClientID'];
        $PageType = $RequestBody['PageType'];

        $Data['Species'] = $this->getSpecies();
        $Data['PurchaseCenters'] = $this->getRmPurchaseCenters($PageType);
        $Data['PurchaseCentersNew'] = $this->getRmPurchaseCentersNew();
        $Data['ProcessingStations'] = $this->getRmProcessingCenters($PageType);
        $Data['PurchaseCategory'] = $this->getPurchaseCategory();
        $Data['Vehicles'] = $this->getVehiclesDetails();
        $Data['RmAgents'] = $this->getUsers($this->Agent);
        //$Data['RmAgents']=$this->getAgents();
        $Data['Drivers'] = $this->getUsers($this->Driver);
        $Data['RmGraders'] = $this->getUsers($this->Grader);
        $Data['RmSuppliers'] = $this->getRMSuppliers($RequestBody);
        $Data['RmVariety'] = $this->getDefectsList();
        $Data['Exporter'] = $this->getExporter();
        $Data['BoysGroup'] = $this->getBoysGroup();
        $Data['BillAndArrivalDiff'] = $this->appController->getClientConfig($ClientID)['BillAndArrivalDiff'];
        $Data['RmSupervisors'] = $this->getSupervisors();



        return $Data;
    }


    function getExporter()
    {


        $sqlQuery = "SELECT ExpID,ExpName FROM `AdminExporterDetails` where ExpType=1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getRmProcessingCenters($PageType)
    {
        $StationStatus = 1;
        if ($PageType == 'REPORTS') {
            $StationStatus = '0,1';
        }

        $sqlQuery = "SELECT ProcessingStationID,StationName FROM `RmProcessingStations` WHERE `StationStatus` IN($StationStatus) ORDER BY SortOrder ASC";
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
    function getRmPurchaseCenters($PageType)
    {


        $StationStatus = 1;
        if ($PageType == 'REPORTS') {
            $StationStatus = '0,1';
        }

        $sqlQuery = "SELECT * FROM `RmPurchaseStations` WHERE StationStatus IN($StationStatus)";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }
    function getRmPurchaseCentersNew()
    {


        $sqlQuery = "SELECT PurchaseStationID,StationName FROM `RmPurchaseStations`";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }
    function getPurchaseCategory()
    {


        $sqlQuery = "SELECT CatID as RmCategoryID,PurchaseType as RmCategoryName FROM `RmPurchaseCategory` ORDER by SortOrder Asc";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }
    function getVehiclesDetails()
    {


        $sqlQuery = "SELECT ID as VehicalID,VehicalNumber FROM `RmVehicalDetails` order by VehicalNumber asc";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }
    function getUsers($UserType)
    {


        $sqlQuery = "SELECT ID as UserID ,UserName FROM `RmUsersList`  WHERE UserType='$UserType' and Status=1 order By UserName asc";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }

        return $Data;
    }

    // function getAgents(){


    //     $sqlQuery = "SELECT ID as UserID ,AgentName as UserName FROM `RmAgentsDetails`   order By AgentName asc";
    //     $stmt = $this->conn->prepare($sqlQuery);
    //     $stmt->execute();
    //     while($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)){
    //         $Data[]=$dataRow;
    //     }

    //     return $Data; 
    // }

    function getRMSuppliers($RequestBody)
    {
        $ClientID = $RequestBody['ClientID'];
        $PageType = $RequestBody['PageType'];
        $pr_page_name = $RequestBody['pr_page_name'];
        $where = "";
        $newFinancer = 0;
        if (isset($ClientID) && $ClientID == "DFLV3") {
            $newFinancer = 1;
            //if( isset($pr_page_name) && $pr_page_name == "createarrival"){
            $where = " where Status=1 ";
            //}
        } else if (isset($ClientID) && $ClientID == "JMEV3") {
            if (isset($PageType) && $PageType == "REPORTS") {
                $where = " where Status IN (0,1)";
            } else {
                $where = " where Status=1";

            }
        }

        $sqlQuery = "SELECT  ID as SupplierID,FinancierName,PanNumber as PanNO FROM `RmSuppliers` $where ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($newFinancer && $dataRow['Status'] == 1) {
                //$dataRow['PanNumber'] = $dataRow['PanNumber']. "(New)";
            }
            $Data[] = $dataRow;
        }
        return $Data;
    }


    function getDefectsList()
    {


        $sqlQuery = "SELECT DefectID As VarietyID,DefectName as VarietyName FROM `RmDefectsList`";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }

        return $Data;
    }
    function getBoysGroup()
    {
        $sqlQuery = "SELECT `GroupName`, `GroupID` FROM `RmBoysGroupMasters`";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }
    function getSupervisors()
    {
        $sqlQuery = "SELECT ID AS UserID,UserName FROM `RmUsersList` WHERE UserType=4 AND Status=1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }
}
