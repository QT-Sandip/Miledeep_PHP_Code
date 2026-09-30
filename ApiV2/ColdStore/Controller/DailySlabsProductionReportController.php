<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';

class DailySlabsProductionReportController
{
    private $conn;
    private $Error = null;
    private $Msg = '';

    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }

    function getDailySlabsProductionDetails($RequestBody)
    {
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $TypeProduct = $RequestBody['TypeProduct'];
        $FreezingType = $RequestBody['FreezingType'];
        $Species = $RequestBody['Species'];
        $ProductionPlant = $RequestBody['ProductionPlant'];
        $Type = $RequestBody['Type'];
        $ColdStore = $RequestBody['ColdStore'];
        $SlabType = $RequestBody['SlabType'];

        $Brand = $RequestBody['Brand'];
        $Variety = $RequestBody['Variety'];
        $Grade = $RequestBody['Grade'];
        $Group = $RequestBody['Group'];
        $PckgStyle = $RequestBody['PckgStyle'];
        $PackingType = $RequestBody['PackingType'];

        $Query = '';

        if ($ProductionPlant != '') {
            $Query .= " AND CSD.PlantID='$ProductionPlant'";
        }
        if ($Species != '') {
            $Query .= " AND PIVL.Species='$Species'";
        }
        if ($TypeProduct != '') {
            $Query .= " AND PIVL.TypeProduct='$TypeProduct'";
        }
        if ($FreezingType != '') {
            $Query .= " AND PIVL.FreezingType = '$FreezingType'";
        }
        if($ColdStore != ''){
            $Query .= " AND CSD.UnitID='$ColdStore'";
        }
        
        if($Brand != ''){
            $Query .= " AND SBM.BrandID='$Brand'";
        }
        if($Variety != ''){
            $Query .= " AND PIVL.ShortName='$Variety'";
        }
        if($Grade != ''){
            $Query .= " AND CSD.Grade='$Grade'";
        }
        if($PackingType != ''){
            $Query .= " AND CSD.PackingTypeID='$PackingType'";
        }
        if($PckgStyle != ''){
            $Query .= " AND CSD.PackingStyleID='$PckgStyle'";
        }
        if($Group != ''){
            $Query .= " AND CSD.ProductionTypeID='$Group'";
        }

        $DateFilters = " CSD.ProductionDate BETWEEN '$FromDate' AND '$ToDate'";

        $sqlFields = "CSPD.`PackingTypeID`,
                        CASE WHEN SPD.PoNo != '' THEN SPD.PoNo ELSE 'DUMMY' END AS PoNo,
                         PIVL.ShortName, APS.PackingStyle,CSD.Grade,
                        CASE WHEN SBM.BrandName != '' THEN SBM.BrandName ELSE 'DUMMY' END AS BrandName,
                        SUM((APS.TotalSlabs * CSD.ActualMcs) + CSD.ActualLooseBags) AS Slabs,ROUND(SUM(CSD.ActualKgs),3) AS Kgs";
        $sqlSelect = "SELECT $sqlFields FROM `CsStockData` AS CSD 
                        LEFT JOIN CsDailyProductionData CSPD ON CSPD.`ProductionID`=CSD.`ProductionID`
                        LEFT JOIN ScrmPoData AS SPD ON SPD.PoID=CSD.PoID
                        LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID=CSD.PackingStyleID 
                        LEFT JOIN ScrmPIVarietyList AS PIVL ON CSD.VarietyID=PIVL.VarietyID 
                        LEFT JOIN ScrmBrandsMaster SBM ON SBM.BrandID=PIVL.BrandName
                        WHERE $DateFilters  $Query --  AND CSD.ProductionTypeID='Production'
                        GROUP BY CSD.PoID,PIVL.ShortName,PIVL.BrandName,CSD.PackingStyleID,CSD.Grade
                        ORDER BY
                        CASE 
                        WHEN CSPD.`PackingTypeID`= 'MASTER' THEN 1
                        ELSE  2 END";

        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();
        $ArrayData = [];
        $SNo = 0;
        $TotalSlabs = $TotalKgs = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
            $dataRow['SNo'] = ++$SNo;
            $ArrayData[] = $dataRow;
            $TotalSlabs += $dataRow['Slabs'];
            $TotalKgs += $dataRow['Kgs'];            
        }   

        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['Data'] = $ArrayData;
        $Data['Totals']['Slabs'] = $TotalSlabs;
        $Data['Totals']['Kgs'] = $TotalKgs;

        return $Data;
    }
   
}
