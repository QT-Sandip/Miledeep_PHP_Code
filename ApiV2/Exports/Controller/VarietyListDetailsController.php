<?php

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
class VarietyListDetailsController
{

    private $conn;
    private $db_table = "ScrmPIVarietyList";


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }
    // GET ALL
    public function getVarietyListDetails()
    {
        $SqlFields = "ShortName,FullName,Grade,FreezingType,Treatment,PreparationType,
        BrandName,PackingStyle, productDescription,TypeProduct,BuyerSUPC,DeviSUPC";

        $sqlQuery = "SELECT $SqlFields FROM " . $this->db_table . " ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        $i = 0;
        $Controller = new UtilController($this->conn);

        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayVarietyList['SNO'] = ++$i;
            $ArrayVarietyList['ShortName'] = $dataRow['ShortName'];
            $ArrayVarietyList['FullName'] = $dataRow['FullName'];
            $ArrayVarietyList['Grade'] = $dataRow['Grade'];
            $ArrayVarietyList['FreezingType'] = $dataRow['FreezingType'];
            $ArrayVarietyList['Treatment'] = 'Doubt';
            $ArrayVarietyList['Preparation'] = $dataRow['PreparationType'];
            $ArrayVarietyList['BrandName'] = 'Doubt';

            $Packing = $dataRow['PackingStyle'];
            $PackingDetails = $Controller->getCountryDetails($Packing);
            $ArrayVarietyList['PackingStyle'] = $PackingDetails['PackingStyle'];

            $ArrayVarietyList['productDescription'] = $dataRow['productDescription'];
            $ArrayVarietyList['Type'] = $dataRow['TypeProduct'];
            $ArrayVarietyList['BuyerSUPC'] = $dataRow['BuyerSUPC'];
            $ArrayVarietyList['OurSUPC'] = $dataRow['DeviSUPC'];
            $Data[] = $ArrayVarietyList;



        }
        return $Data;
    }

}
?>