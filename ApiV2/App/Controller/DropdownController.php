<?php

include_once '../Global/Util.php';

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
      
        $Data['Species']=$this->getSpecies();
        $Data['PurchaseCenters']=$this->getRmPurchaseCenters();
        $Data['PurchaseCategory']=$this->getPurchaseCategory();
        $Data['Vehicles']=$this->getVehiclesDetails();
        $Data['RmAgents']=$this->getUsers($this->Agent);
        $Data['Drivers']=$this->getUsers($this->Driver);
        $Data['RmGraders']=$this->getUsers($this->Grader);
        $Data['AdminExporters']=$this->getExporter();
        $Data['Consignee']=$this->getConsignee();
        $Data['Country']=$this->getCountry();
        $Data['ShipmentStatusCodes']=$this->getShipmentStatusCodes();
        $Data['Processor']=$this->getProcessor();
        $Data['Brand']=$this->getBrand();
        $Data['TypeProduct']=$this->getTypeProduct();
        $Data['ShortName']=$this->getShortName();

      
        return $Data;
    }

    function getSpecies(){


        $sqlQuery = "SELECT ID as SpeciesID,SpeciesName,ShortCode,Description FROM `ScrmSpeciesMaster` WHERE Status=1 ORDER by SortOrder Asc";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)){
            $Data[]=$dataRow;
        }
        return $Data;
    }
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

    function getExporter(){


        $sqlQuery = "SELECT  ExpID,ExpName FROM `AdminExporterDetails` where ExpType=1 ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)){
            $Data[]=$dataRow;
        }

        return $Data;
    }

    function getConsignee(){

        $sqlQuery = "SELECT  ConID,ConsigneeName FROM `ScrmConsigneeDetails`";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)){
            $Data[]=$dataRow;
        }

        return $Data;
    }
   
    function getCountry(){

        $sqlQuery = "SELECT  ID,name  FROM `ScrmCountriesList`";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)){
            $Data[]=$dataRow;
        }

        return $Data;
    }

    function getShipmentStatusCodes(){

        $sqlQuery = "SELECT  Code as ID,Name  FROM `ScrmShipmentStatusCodes`";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)){
            $Data[]=$dataRow;
        }

        return $Data;
    }

    function getProcessor(){


        $sqlQuery = "SELECT  ExpID,ExpName FROM `AdminExporterDetails` where ExpType=2 ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)){
            $Data[]=$dataRow;
        }

        return $Data;
    }
    
    function getBrand(){


        $sqlQuery = "SELECT BrandID as ID,BrandName as Name FROM `ScrmBrandsMaster` WHERE Status=1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)){
            $Data[]=$dataRow;
        }

        return $Data;
    }
   
    function getTypeProduct(){

        $Data = array("RAW", "COOKED", "BLANCHED");

        return $Data;
    }

    function getShortName(){

        $sqlQuery = "SELECT DISTINCT ShortName FROM `ScrmPIVarietyList`";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)){
            $Data[]=$dataRow;
        }

        return $Data;
    }

}
?>