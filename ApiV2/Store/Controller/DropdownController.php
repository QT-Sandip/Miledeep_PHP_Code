<?php

include_once '../Global/Util.php';

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
    public function getDropdowns()
    {

        $Data['Buyer'] = $this->getBuyer();
        $Data['Supplier'] = $this->getSupplier();
        $Data['ChemicalName'] = $this->getChemicalName();
        $Data['ProcessingPlants'] = $this->getProcessingPlants();
        $Data['PoNo'] = $this->getPoNo();
        $Data['PoChemicals'] = $this->getPoChemicals();
        $Data['FarmName'] = $this->getFarmName();
        $Data['Hatchery'] = $this->getHatchery();
        $Data['AuditPaymt'] = $this->getAuditPaymt();
        $Data['AuditName'] = $this->getAuditName();
        $Data['AuditType'] = $this->getAuditType();
        $Data['AuditProgram'] = $this->getAuditProgram();
        $Data['CertAuth'] = $this->getCertAuth();
        $Data['AuditGrade'] = $this->getAuditGrade();
        $Data['Country'] = $this->getCountry();
        $Data['Port'] = $this->getPort();
        $Data['Currency'] = $this->getCurrency();
        // $Data['Status']=$this->getStatus();

        return $Data;
    }


    function getBuyer()
    {

        $sqlQuery = "SELECT ChemBuyerID, BuyerName FROM GsChemBuyersData";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getSupplier()
    {

        $sqlQuery = "SELECT ChemSupplierID, SupplierName, Status FROM GsChemSuppliersData where  SupplierName != ''
  AND SupplierName NOT LIKE '%SALT%'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getChemicalName()
    {
        $sqlQuery = "SELECT ChemID, ChemicalName,NoOfKgPerBag FROM GsChemicalMaster WHERE Status=1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getProcessingPlants()
    {

        $sqlQuery = "SELECT ProcessingStationID,StationName,FullName FROM RmProcessingStations WHERE StationStatus=1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getPoNo()
    {

        $sqlQuery = "SELECT ChemPoID,ChemPoNo FROM GsChemicalPos where ChemPoNo != ''
  AND ChemPoNo NOT LIKE '%Opening%'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getPoChemicals()
    {

        $sqlQuery = "SELECT GCP.ChemPoID,ChemPoNo,GROUP_CONCAT(DISTINCT ChemicalID) AS ChemicalID FROM GsChemicalPos AS GCP
        LEFT JOIN GsChemicalPoBreakUp AS GCPB ON GCP.ChemPoID =  GCPB.ChemPoID
        where GCP.ChemPoNo != '' 
    AND GCP.ChemPoNo NOT LIKE '%Opening%'
        GROUP BY GCP.ChemPoID";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }
    function getAuditPaymt()
    {

        $sqlQuery = "SELECT PaymentID,PaymentName FROM GsPaymentNamesMaster";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }
    function getFarmName()
    {

        $sqlQuery = "SELECT TraceID,FarmerName,TRACEBILITY FROM QcTracebilityList";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }
    function getCountry()
    {

        $sqlQuery = "SELECT id,name FROM ScrmCountriesList";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getHatchery()
    {

        $sqlQuery = "SELECT HatcheryID, GROUP_CONCAT(CONCAT(HatcryName, ' - ', HatcryLocation) ORDER BY HatcryName SEPARATOR ', ') AS Hatchery
        FROM GsHatchryMaster GROUP BY HatcheryID";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getCurrency()
    {

        $sqlQuery = "SELECT CurrencyID,CurrencyName,CurrenySymbol
        FROM ScrmCurrencyMaster";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    function getPort()
    {
        // echo 
        $sqlQuery = "SELECT PortId, GROUP_CONCAT(CONCAT(PortCode, ' - ', Name) ORDER BY Name SEPARATOR ', ') AS PortName
        FROM ScrmPortCities  WHERE IsPortofLoading = 1 GROUP BY PortId ORDER BY PortId DESC";

        // (SELECT PortId, ReferencePortID, Country, Function, LoCode, Name, NameWoDiac, Latitude, Longitude, Status, 
        // CountryCode, PortCode, IsPortofLoading FROM ScrmPortCities ) SAVE ID ,DISPLAY NAME 

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }

    // 23-01-2024 
    function getAuditName()
    {

        $Data = array("APCB", "Plant Approval");

        return $Data;
    }

    function getAuditType()
    {

        $Data = array("FOOD", "SOCIAL", "GOVERNMENT");

        return $Data;
    }
    function getCertAuth()
    {

        $Data = array("Cert Authority1", "Cert Authority2");

        return $Data;
    }

    function getAuditProgram()
    {

        $Data = array("Announced", "UnAnnounced");

        return $Data;
    }
    function getAuditGrade()
    {

        $Data = array("Grade A", "Grade B");

        return $Data;
    }

    // function getStatus (){

    //     $Data = array("In-Active", "Active");

    //     return $Data;
    // }

}
