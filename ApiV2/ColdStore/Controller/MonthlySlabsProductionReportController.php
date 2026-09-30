<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';

class MonthlySlabsProductionReportController
{
    private $conn;
    private $Error = null;
    private $Msg = '';

    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }

    function getMonthlySlabsProductionDetails($RequestBody)
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

        $Query = '';

        if ($ProductionPlant != '') {
            $Query .= " AND CPID.PlantID='$ProductionPlant'";
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
            $Query .= " AND CPID.UnitID='$ColdStore'";
        }

        $DateFilters = " CPID.ProductionDate BETWEEN '$FromDate' AND '$ToDate'";

        $sqlFields = "CPID.VarietyID, PIVL.ShortName, PIVL.Species, CPID.PackingStyleID, CPID.SockingType, 
        PIVL.TypeProduct, CPS.PackingStyle, PIVL.FreezingType";
        $sqlSelect = "SELECT $sqlFields FROM `CsStockData` AS CPID 
        LEFT JOIN AdminPackingStyles AS CPS ON CPS.PCKID=CPID.PackingStyleID 
        LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID  
        LEFT JOIN ScrmProductList AS CPL ON CPID.ProductionID=CPL.ProductID
        WHERE $DateFilters  $Query
        GROUP BY PIVL.ShortName, CPID.PackingStyleID, PIVL.Treatment, PIVL.Species, PIVL.TypeProduct,PIVL.FreezingType
        ORDER BY PIVL.Species,PIVL.TypeProduct,PIVL.FreezingType,PIVL.ShortName,PIVL.Treatment, ABS(CPS.PackingStyle)";

        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();

        $ProductionDates = $this->getDates($RequestBody);
        $ArrayVarietyData = [];
        $SNo = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ShortName = $dataRow['ShortName'];
            $SockingType = $dataRow['SockingType'];
            $PackingStyleID = $dataRow['PackingStyleID'];
            $Species = $dataRow['Species'];
            $TypeProduct = $dataRow['TypeProduct'];
            $FreezingType = $dataRow['FreezingType'];

            $SlabsArray = [];
            $GrandTotals = 0;
            // $GrandKgsTotal = 0;
            foreach ($ProductionDates as $ProductionDate) {
                $ProductionDate =  date('Y-m-d', strtotime($ProductionDate));
                $slabFields = "SUM(TotalSlabs*ActualMcs) AS McsSlabs, SUM(ActualLooseBags) AS LooseSlabs, SUM(ActualKgs) AS Kgs,SUM(ActualMcs) AS ActualMcs,TotalSlabs";//ActualMcs*TotalSlabs*SlabWeight*MFactor
                $sqlSlabs = "SELECT $slabFields FROM `CsStockData` AS CPID 
                LEFT JOIN AdminPackingStyles AS CPS ON CPS.PCKID=CPID.PackingStyleID 
                LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID  
                LEFT JOIN ScrmProductList AS CPL ON CPID.ProductionID=CPL.ProductID
                WHERE PIVL.ShortName = '$ShortName' AND CPID.PackingStyleID = '$PackingStyleID' 
                AND PIVL.Treatment='$SockingType' AND PIVL.TypeProduct = '$TypeProduct' AND PIVL.FreezingType = '$FreezingType' 
                AND CPID.ProductionDate = '$ProductionDate' AND PIVL.Species = '$Species' AND CPID.ProductionTypeID IN ('Production','Melting')
                 $Query";
                $stmtSlabs = $this->conn->prepare($sqlSlabs);
                $stmtSlabs->execute();

                while ($rowSlabs = $stmtSlabs->fetch(PDO::FETCH_ASSOC)) {

                    $Mcs = $rowSlabs['McsSlabs'] ? $rowSlabs['McsSlabs'] : 0;
                    $LooseBags = $rowSlabs['LooseSlabs'] ? ($rowSlabs['LooseSlabs']) : 0;

                    if($SlabType == "MCS"){
                        $LooseMcs = $rowSlabs['LooseSlabs'] ? ($rowSlabs['LooseSlabs']/$rowSlabs['TotalSlabs']) : 0;
                        $ActualMcs = $rowSlabs['ActualMcs'] ? $rowSlabs['ActualMcs'] : 0;
                        $SlabsList['TotalSlabs'] = round($ActualMcs + $LooseMcs,2);
                        $GrandTotals += round($ActualMcs + $LooseMcs,2);
                    }else if($SlabType == "KGS"){
                        $Kgs = $rowSlabs['Kgs'] ? $rowSlabs['Kgs'] : 0;
                        $SlabsList['TotalSlabs'] = round($Kgs,2);
                        $GrandTotals += $Kgs;

                    }else{
                        $TotalSlabs = $Mcs + $LooseBags;
                        $SlabsList['TotalSlabs'] = $TotalSlabs;
                        $GrandTotals += $TotalSlabs;
                    }
                    $SlabsArray[] = $SlabsList;
                }

            }
            if ($GrandTotals != 0) {
                $ArrayList['SNo'] = ++$SNo;
                $ArrayList['ShortName'] = $dataRow['ShortName'];
                $ArrayList['SockingType'] = $dataRow['SockingType'];
                $ArrayList['PackingStyle'] = $dataRow['PackingStyle'];
                $ArrayList['Species'] = $dataRow['Species'];
                $ArrayList['TypeProduct'] = $dataRow['TypeProduct'] . ' - ' . $dataRow['FreezingType'];

                $SlabsArray[] = ['TotalSlabs' => round($GrandTotals,2)];
                $ArrayList['SlabsData'] = $SlabsArray;
                $ArrayVarietyData[] = $ArrayList;
            }
        }

        if ($Type == 'DayWise') {
            $Dates = $this->getDates($RequestBody);
            $TypeProductDateWiseData = $this->getTypeProductDateWiseData($RequestBody);
            $TypeProductDateWiseTotals = $this->getTypeProductDateWiseTotals($RequestBody);
        } else if ($Type == 'MonthWise') {

            $MonthWiseTotals = $this->getMonthWiseTotals($RequestBody);
            $MonthWiseData = $this->getMonthWiseSlabsData($RequestBody);
        }

        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        if ($Type == 'MonthWise') {
            $Data['MonthWiseTotals'] = $MonthWiseTotals;
            $Data['MonthWiseData'] = $MonthWiseData;
        } else if ($Type == 'DayWise') {
            $Data['DayWiseData'] = $ArrayVarietyData;
            $Data['Dates'] = $Dates;
            $Data['TypeProductDateWiseData'] = $TypeProductDateWiseData;
            $Data['DateWiseGrandTotals'] = $TypeProductDateWiseTotals;
        }

        return $Data;
    }

    // Using
    public function getDates($RequestBody)
    {
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];

        $startDate = new DateTime($FromDate);
        $endDate = new DateTime($ToDate);

        $dateArray = [];

        while ($startDate <= $endDate) {
            $dateArray[] = $startDate->format('d-m-Y');
            $startDate->modify('+1 day');
        }

        return $dateArray;
    }

    // Using
    function getTypeProductDateWiseTotals($RequestBody)
    {
        $TypeProduct = $RequestBody['TypeProduct'];
        $Species = $RequestBody['Species'];
        $ProductionPlant = $RequestBody['ProductionPlant'];
        $FreezingType = $RequestBody['FreezingType'];
        $ColdStore = $RequestBody['ColdStore'];
        $SlabType = $RequestBody['SlabType'];

        $Query = '';

        if ($ProductionPlant != '') {
            $Query .= " AND CPID.PlantID='$ProductionPlant'";
        }
        if ($Species != '') {
            $Query .= " AND PIVL.Species='$Species'";
        }
        if ($TypeProduct != '') {
            $Query .= " AND TypeProduct='$TypeProduct'";
        }
        if ($FreezingType != '') {
            $Query .= " AND PIVL.FreezingType='$FreezingType'";
        }
        if($ColdStore != ''){
            $Query .= " AND CPID.UnitID='$ColdStore'";
        }

        $ProductionDates = $this->getDates($RequestBody);
        $TypeProductArray = [];
        $TypeWiseTotalSlabs = 0;
        $TypeWiseArray = [];
        $GrandTotals = 0;
        // $GrandKgsTotal = 0;

        foreach ($ProductionDates as $ProductionDate) {
            $ProductionDate =  date('Y-m-d', strtotime($ProductionDate));

            $slabFields = "SUM(TotalSlabs*ActualMcs) AS McsSlabs, 
            SUM(ActualLooseBags) AS LooseSlabs, 
            SUM(ActualLooseBags / NULLIF(CPS.TotalSlabs, 0)) AS LooseMcs,
            SUM(ActualKgs) AS Kgs, SUM(ActualMcs) AS ActualMcs,TotalSlabs";//ActualMcs*TotalSlabs*SlabWeight*MFactor
            $sqlSlabs = "SELECT $slabFields FROM `CsStockData` AS CPID 
            LEFT JOIN AdminPackingStyles AS CPS ON CPS.PCKID=CPID.PackingStyleID 
            LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID  
            LEFT JOIN ScrmProductList AS CPL ON CPID.ProductionID=CPL.ProductID
            WHERE CPID.ProductionDate = '$ProductionDate' AND CPID.ProductionTypeID IN ('Production','Melting') $Query
            -- AND CPID.ProductionTypeID='Production'
            ";
            $stmtSlabs = $this->conn->prepare($sqlSlabs);
            $stmtSlabs->execute();

            while ($rowProduct = $stmtSlabs->fetch(PDO::FETCH_ASSOC)) {
                $TypeWiseLooseBags = $rowProduct['LooseSlabs'] ? $rowProduct['LooseSlabs'] : 0;
                $TypeWiseMcs = $rowProduct['McsSlabs'] ? $rowProduct['McsSlabs'] : 0;

                // $TypeWiseTotalSlabs = $TypeWiseLooseBags + $TypeWiseMcs;
                // $TypeWiseList['TypeWiseTotalSlabs'] = $TypeWiseTotalSlabs;
                // $GrandTotals += $TypeWiseTotalSlabs;

                // $Kgs = $rowProduct['Kgs'] ? $rowProduct['Kgs'] : 0;
                // $TypeWiseList['TypeWiseTotalKgs'] = round($Kgs,2);
                // $GrandKgsTotal += $Kgs;

                if($SlabType == "MCS"){
                    //$LooseMcs = $rowProduct['LooseSlabs'] ? ($rowProduct['LooseSlabs']/$rowProduct['TotalSlabs']) : 0;
                    $LooseMcs = $rowProduct['LooseMcs'] ? ($rowProduct['LooseMcs']) : 0;
                    $ActualMcs = $rowProduct['ActualMcs'] ? $rowProduct['ActualMcs'] : 0;
                    $TypeWiseList['TypeWiseTotalSlabs'] = round($ActualMcs + $LooseMcs,2);
                    $GrandTotals += ($ActualMcs + $LooseMcs);
                }else if($SlabType == "KGS"){
                    $Kgs = $rowProduct['Kgs'] ? $rowProduct['Kgs'] : 0;
                    $TypeWiseList['TypeWiseTotalSlabs'] = round($Kgs,2);
                    $GrandTotals += $Kgs;

                }else{
                    $TypeWiseTotalSlabs = $TypeWiseLooseBags + $TypeWiseMcs;
                    $TypeWiseList['TypeWiseTotalSlabs'] = $TypeWiseTotalSlabs;
                    $GrandTotals += $TypeWiseTotalSlabs;
                }

                $TypeWiseArray[] = $TypeWiseList;
            }
        }

        $TypeWiseArray[] = ['TypeWiseTotalSlabs' => round($GrandTotals,2)];
        if ($GrandTotals != 0) {
            $TypeProductArrayList['TypeProduct'] = 'Grand Totals';
            $TypeProductArrayList['TypeProductData'] = $TypeWiseArray;
            $TypeProductArray[] = $TypeProductArrayList;
        }
        return $TypeProductArray;
    }

    // Not using
    public function getTypeProductMonthWiseData($RequestBody)
    {

        $Month = $RequestBody['Month'];
        $Year = $RequestBody['Year'];
        $Type = $RequestBody['Type'];

        $TypeProduct = $RequestBody['TypeProduct'];
        $Species = $RequestBody['Species'];
        $ProductionPlant = $RequestBody['ProductionPlant'];
        $FreezingType = $RequestBody['FreezingType'];
        $ColdStore = $RequestBody['ColdStore'];
        $SlabType = $RequestBody['SlabType'];

        $Query = '';
        $QueryTwo = '';

        if ($ProductionPlant != '') {
            $Query .= " AND CPID.PlantID='$ProductionPlant'";
        }
        if ($Species != '') {
            $Query .= " AND PIVL.Species='$Species'";
        }

        if ($FreezingType != '') {
            $Query .= " AND PIVL.FreezingType='$FreezingType'";
        }

        if ($TypeProduct != '') {
            $QueryTwo .= " AND TypeProduct='$TypeProduct'";
        }
        if($ColdStore != ''){
            $Query .= " AND CPID.UnitID='$ColdStore'";
        }

        if ($Type == 'DayWise') {
            $FromDate = $RequestBody['FromDate'];
            $ToDate = $RequestBody['ToDate'];
        } else if ($Type == 'MonthWise') {
            $FromDate = $Year . '-' . $Month . '-01';
            $ToDate = $Year . '-' . $Month . '-31';
        }

        $DateFilters = " CPID.ProductionDate BETWEEN '$FromDate' AND '$ToDate'";

        $sqlType = "SELECT DISTINCT TypeProduct FROM ScrmPIVarietyList WHERE 1=1 $QueryTwo";
        $stmtType = $this->conn->prepare($sqlType);
        $stmtType->execute();

        $TypeProductArray = [];
        $TypeWiseTotalSlabs = 0;
        while ($rowType = $stmtType->fetch(PDO::FETCH_ASSOC)) {

            $TypeProduct = $rowType['TypeProduct'];
            $slabFields = "SUM(TotalSlabs*ActualMcs) AS McsSlabs, SUM(ActualLooseBags) AS LooseSlabs, 
            SUM(ActualMcs*TotalSlabs*SlabWeight*MFactor) as Kgs,
            SUM(ActualLooseBags*SlabWeight*MFactor) as LKgs,SUM(ActualMcs) AS ActualMcs,sum(ActualKgs) as ActualKgs";
            $sqlSlabs = "SELECT $slabFields FROM `CsStockData` AS CPID 
            LEFT JOIN AdminPackingStyles AS CPS ON CPS.PCKID=CPID.PackingStyleID 
            LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID  
            LEFT JOIN ScrmProductList AS CPL ON CPID.ProductionID=CPL.ProductID
            WHERE $DateFilters AND CPID.ProductionTypeID IN ('Production','Melting') $Query AND PIVL.TypeProduct = '$TypeProduct' 
            -- AND CPID.ProductionTypeID='Production'
            ";
            $stmtSlabs = $this->conn->prepare($sqlSlabs);
            $stmtSlabs->execute();

            while ($rowProduct = $stmtSlabs->fetch(PDO::FETCH_ASSOC)) {

                $TypeWiseLooseBags = $rowProduct['LooseSlabs'] ? $rowProduct['LooseSlabs'] : 0;
                if($SlabType == "MCS"){
                    $TypeWiseMcs = $rowProduct['ActualMcs'] ? $rowProduct['ActualMcs'] : 0;
                }else{
                $TypeWiseMcs = $rowProduct['McsSlabs'] ? $rowProduct['McsSlabs'] : 0;
                }

                $KGs = $rowProduct['Kgs'] ? $rowProduct['Kgs'] : 0;
                $LKGS = $rowProduct['LKgs'] ? $rowProduct['LKgs'] : 0;
                $ActualKgs = $rowProduct['ActualKgs'] ? $rowProduct['ActualKgs'] : 0;


                $TotalSlabs = ($TypeWiseMcs + $TypeWiseLooseBags);
                // $GrossWeight = $KGs + $LKGS;
                $GrossWeight = $ActualKgs;


                $TypeWiseTotalSlabs = $TotalSlabs;
                $TypeWiseGrossWeight = $GrossWeight;
                $TypeWiseList['TypeWiseTotalSlabs'] = $TypeWiseTotalSlabs;
                $TypeWiseList['TypeWiseGrossWeight'] = $TypeWiseGrossWeight;
            }
            $TypeProductArray[$TypeProduct] = $TypeWiseList;
        }
        return $TypeProductArray;
    }

    // Using
    public function getTypeProductDateWiseData($RequestBody)
    {
        $TypeProduct = $RequestBody['TypeProduct'];
        $FreezingType = $RequestBody['FreezingType'];
        $Species = $RequestBody['Species'];
        $ProductionPlant = $RequestBody['ProductionPlant'];
        $ColdStore = $RequestBody['ColdStore'];
        $SlabType = $RequestBody['SlabType'];

        $Query = '';
        $QueryTwo = '';

        if ($ProductionPlant != '') {
            $Query .= " AND CPID.PlantID='$ProductionPlant'";
        }
        if ($Species != '') {
            $Query .= " AND PIVL.Species='$Species'";
        }
        if ($TypeProduct != '') {
            $QueryTwo .= " AND TypeProduct='$TypeProduct'";
        }
        if ($FreezingType != '') {
            $QueryTwo .= " AND FreezingType='$FreezingType'";
        }
        if($ColdStore != ''){
            $Query .= " AND CPID.UnitID='$ColdStore'";
        }

        $sqlType = "SELECT TypeProduct,FreezingType FROM ScrmPIVarietyList 
        WHERE 1=1 $QueryTwo GROUP BY TypeProduct,FreezingType ORDER BY TypeProduct ASC";

        $stmtType = $this->conn->prepare($sqlType);
        $stmtType->execute();

        $ProductionDates = $this->getDates($RequestBody);
        $TypeProductArray = [];
        $TypeWiseTotalSlabs = 0;
        while ($rowType = $stmtType->fetch(PDO::FETCH_ASSOC)) {
            $TypeWiseArray = [];
            $GrandTotals = 0;
            // $GrandKgsTotal = 0;

            foreach ($ProductionDates as $ProductionDate) {
                $ProductionDate =  date('Y-m-d', strtotime($ProductionDate));
                $TypeProduct = $rowType['TypeProduct'];
                $FreezingType = $rowType['FreezingType'];
                $TypeProductCombination = $rowType['TypeProduct'] . '-' . $rowType['FreezingType'];
                $slabFields = "SUM(TotalSlabs*ActualMcs) AS McsSlabs, 
                SUM(ActualLooseBags) AS LooseSlabs, 
                SUM(ActualLooseBags / NULLIF(CPS.TotalSlabs, 0)) AS LooseMcs,
                SUM(ActualKgs) AS Kgs, SUM(ActualMcs) AS ActualMcs,TotalSlabs";//ActualMcs*TotalSlabs*SlabWeight*MFactor
                $sqlSlabs = "SELECT $slabFields FROM `CsStockData` AS CPID 
                LEFT JOIN AdminPackingStyles AS CPS ON CPS.PCKID=CPID.PackingStyleID 
                LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID  
                LEFT JOIN ScrmProductList AS CPL ON CPID.ProductionID=CPL.ProductID
                WHERE CPID.ProductionDate = '$ProductionDate' AND CPID.ProductionTypeID IN ('Production','Melting') $Query 
                AND PIVL.TypeProduct = '$TypeProduct' AND PIVL.FreezingType = '$FreezingType'
                -- AND CPID.ProductionTypeID='Production'
                ";
                $stmtSlabs = $this->conn->prepare($sqlSlabs);
                $stmtSlabs->execute();

                while ($rowProduct = $stmtSlabs->fetch(PDO::FETCH_ASSOC)) {
                    $TypeWiseLooseBags = $rowProduct['LooseSlabs'] ? $rowProduct['LooseSlabs'] : 0;
                    $TypeWiseMcs = $rowProduct['McsSlabs'] ? $rowProduct['McsSlabs'] : 0;

                    // $TypeWiseTotalSlabs = $TypeWiseLooseBags + $TypeWiseMcs;
                    // $TypeWiseList['TypeWiseTotalSlabs'] = $TypeWiseTotalSlabs;
                    // $GrandTotals += $TypeWiseTotalSlabs;

                    // $Kgs = $rowProduct['Kgs'] ? $rowProduct['Kgs'] : 0;
                    // $TypeWiseList['TypeWiseTotalKgs'] = round($Kgs,2);
                    // $GrandKgsTotal += $Kgs;
                    if($SlabType == "MCS"){
                        //$LooseMcs = $rowProduct['LooseSlabs'] ? ($rowProduct['LooseSlabs']/$rowProduct['TotalSlabs']) : 0;
                       $LooseMcs = $rowProduct['LooseMcs'] ? ($rowProduct['LooseMcs']) : 0;
                        $ActualMcs = $rowProduct['ActualMcs'] ? $rowProduct['ActualMcs'] : 0;
                        $TypeWiseList['TypeWiseTotalSlabs'] = round($ActualMcs + $LooseMcs,2);
                        $GrandTotals += ($ActualMcs + $LooseMcs);

                        // $ActualMcs = $rowProduct['ActualMcs'] ? $rowProduct['ActualMcs'] : 0;
                        // $TypeWiseList['TypeWiseTotalSlabs'] = round($ActualMcs + $TypeWiseLooseBags,2);
                        // $GrandTotals += $ActualMcs;
                    }else if($SlabType == "KGS"){
                        $Kgs = $rowProduct['Kgs'] ? $rowProduct['Kgs'] : 0;
                        $TypeWiseList['TypeWiseTotalSlabs'] = round($Kgs,2);
                        $GrandTotals += $Kgs;
    
                    }else{
                        $TypeWiseTotalSlabs = $TypeWiseLooseBags + $TypeWiseMcs;
                        $TypeWiseList['TypeWiseTotalSlabs'] = $TypeWiseTotalSlabs;
                        $GrandTotals += $TypeWiseTotalSlabs;
                    }
                    $TypeWiseArray[] = $TypeWiseList;
                }
            }

            $TypeWiseArray[] = ['TypeWiseTotalSlabs' => round($GrandTotals,2)];
            if ($GrandTotals != 0) {
                $TypeProductArrayList['TypeProduct'] = $TypeProductCombination;
                $TypeProductArrayList['TypeProductData'] = $TypeWiseArray;
                $TypeProductArray[] = $TypeProductArrayList;
            }
        }
        return $TypeProductArray;
    }

    // using
    public function getMonthWiseSlabsData($RequestBody)
    {
        $TypeProduct = $RequestBody['TypeProduct'];
        $FreezingType = $RequestBody['FreezingType'];
        $Species = $RequestBody['Species'];
        $ProductionPlant = $RequestBody['ProductionPlant'];
        $Month = $RequestBody['Month'];
        $Year = $RequestBody['Year'];
        $FromDate = $Year . '-' . $Month . '-01';
        $ToDate = $Year . '-' . $Month . '-31'; 
        $ColdStore = $RequestBody['ColdStore'];
        $SlabType = $RequestBody['SlabType'];

        $Query = '';

        if ($ProductionPlant != '') {
            $Query .= " AND CPID.PlantID='$ProductionPlant'";
        }
        if ($Species != '') {
            $Query .= " AND PIVL.Species='$Species'";
        }
        if ($TypeProduct != '') {
            $Query .= " AND PIVL.TypeProduct='$TypeProduct'";
        }
        if ($FreezingType != '') {
            $Query .= " AND PIVL.FreezingType='$FreezingType'";
        }
        if($ColdStore != ''){
            $Query .= " AND CPID.UnitID='$ColdStore'";
        }

        $DateFilters = " CPID.ProductionDate BETWEEN '$FromDate' AND '$ToDate'";

        $sqlFields = "CPID.VarietyID, PIVL.ShortName, PIVL.Species, CPID.PackingStyleID, CPID.SockingType, 
        PIVL.TypeProduct, CPS.PackingStyle, PIVL.FreezingType";
        $sqlSelect = "SELECT $sqlFields FROM `CsStockData` AS CPID 
        LEFT JOIN AdminPackingStyles AS CPS ON CPS.PCKID=CPID.PackingStyleID 
        LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID  
        WHERE $DateFilters  $Query
         AND CPID.ProductionTypeID IN ('Production','Melting')
        GROUP BY PIVL.ShortName, CPID.PackingStyleID, PIVL.Treatment, PIVL.Species, PIVL.TypeProduct,PIVL.FreezingType
        ORDER BY PIVL.Species,PIVL.TypeProduct,PIVL.FreezingType,PIVL.ShortName,PIVL.Treatment, ABS(CPS.PackingStyle)";
        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();

        $MonthWiseArrayData = [];
        $GrandTotalSlabs = 0;
        $GrandTotalGrossweight = 0;
        $SNo = 0;
        $TotalSlabs = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ShortName = $dataRow['ShortName'];
            $SockingType = $dataRow['SockingType'];
            $PackingStyleID = $dataRow['PackingStyleID'];
            $Species = $dataRow['Species'];
            $TypeProduct = $dataRow['TypeProduct'];
            $FreezingType = $dataRow['FreezingType'];

            $slabFields = "SUM(TotalSlabs*ActualMcs) AS McsSlabs, 
            SUM(ActualLooseBags) AS LooseSlabs, 
            SUM(ActualLooseBags / NULLIF(CPS.TotalSlabs, 0)) AS LooseMcs,
            SUM(ActualMcs*TotalSlabs*SlabWeight*MFactor) as Kgs,
            SUM(ActualLooseBags*SlabWeight*MFactor) as LKgs,SUM(ActualMcs) AS ActualMcs,sum(ActualKgs) as ActualKgs,TotalSlabs";
            $sqlMonthWiseSlabs = "SELECT $slabFields FROM `CsStockData` AS CPID 
            LEFT JOIN AdminPackingStyles AS CPS ON CPS.PCKID=CPID.PackingStyleID 
            LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID  
            WHERE $DateFilters AND PIVL.ShortName = '$ShortName' AND CPID.PackingStyleID = '$PackingStyleID' 
            AND PIVL.Treatment='$SockingType' AND PIVL.TypeProduct = '$TypeProduct' AND PIVL.FreezingType = '$FreezingType' 
            AND PIVL.Species = '$Species' 
            AND CPID.ProductionTypeID IN ('Production','Melting')
             $Query";
            $stmtMonthWiseSlabs = $this->conn->prepare($sqlMonthWiseSlabs);
            $stmtMonthWiseSlabs->execute();
            $TotalSlabs = 0;
            while ($rowMonthWise = $stmtMonthWiseSlabs->fetch(PDO::FETCH_ASSOC)) {

                if($SlabType == "MCS"){
                    $McsSlabs = $rowMonthWise['ActualMcs'] ? $rowMonthWise['ActualMcs'] : 0;
                    //$LooseSlabs = $rowMonthWise['LooseSlabs'] ? ($rowMonthWise['LooseSlabs']/$rowMonthWise['TotalSlabs']) : 0;
                    $LooseSlabs = $rowMonthWise['LooseMcs'] ? ($rowMonthWise['LooseMcs']) : 0;
                }else{
                    $McsSlabs = $rowMonthWise['McsSlabs'] ? $rowMonthWise['McsSlabs'] : 0;
                    $LooseSlabs = $rowMonthWise['LooseSlabs'] ? ($rowMonthWise['LooseSlabs']) : 0;
                }
                // $McsSlabs = $rowMonthWise['McsSlabs'] ? $rowMonthWise['McsSlabs'] : 0;
               
                $KGs = $rowMonthWise['Kgs'] ? $rowMonthWise['Kgs'] : 0;
                $LKGS = $rowMonthWise['LKgs'] ? $rowMonthWise['LKgs'] : 0;
                $ActualKgs = $rowMonthWise['ActualKgs'] ? $rowMonthWise['ActualKgs'] : 0;


                $TotalSlabs = round($McsSlabs + $LooseSlabs ,2);
                // $GrossWeight = $KGs + $LKGS;
                $GrossWeight = $ActualKgs;

            }
            if ($TotalSlabs != 0) {
                $ArrayList['SNo'] = ++$SNo;
                $ArrayList['ShortName'] = $dataRow['ShortName'];
                $ArrayList['SockingType'] = $dataRow['SockingType'];
                $ArrayList['PackingStyle'] = $dataRow['PackingStyle'];
                $ArrayList['Species'] = $dataRow['Species'];
                $ArrayList['TypeProduct'] = $dataRow['TypeProduct'] . ' - ' . $dataRow['FreezingType'];

                $ArrayList['TotalSlabs'] = $TotalSlabs;
                $ArrayList['GrossWeight'] = $GrossWeight;
                $GrandTotalSlabs += $TotalSlabs;
                $GrandTotalGrossweight += $GrossWeight;

                $MonthWiseArrayData[] = $ArrayList;
            }
        }

        $Data['MonthWiseArrayData'] = $MonthWiseArrayData;
        $Data['GrandTotalGrossweight'] = round($GrandTotalGrossweight, 3);
        $Data['GrandTotalSlabs'] = round($GrandTotalSlabs,2);
        return $Data;
    }

    // using
    public function getMonthWiseTotals($RequestBody)
    {
        $TypeProduct = $RequestBody['TypeProduct'];
        $Species = $RequestBody['Species'];
        $ProductionPlant = $RequestBody['ProductionPlant'];
        $FreezingType = $RequestBody['FreezingType'];
        $Month = $RequestBody['Month'];
        $Year = $RequestBody['Year'];
        $FromDate = $Year . '-' . $Month . '-01';
        $ToDate = $Year . '-' . $Month . '-31';
        $ColdStore = $RequestBody['ColdStore'];
        $SlabType = $RequestBody['SlabType'];

        $Query = '';
        $QueryTwo = '';

        if ($ProductionPlant != '') {
            $Query .= " AND CPID.PlantID='$ProductionPlant'";
        }
        if ($Species != '') {
            $Query .= " AND PIVL.Species='$Species'";
        }
        if ($TypeProduct != '') {
            $QueryTwo .= " AND TypeProduct='$TypeProduct'";
        }
        if ($FreezingType != '') {
            $QueryTwo .= " AND FreezingType='$FreezingType'";
        }
        if($ColdStore != ''){
            $Query .= " AND CPID.UnitID='$ColdStore'";
        }

        $DateFilters = " CPID.ProductionDate BETWEEN '$FromDate' AND '$ToDate'";

        $sqlFields = "CPID.VarietyID, PIVL.ShortName, PIVL.Species, CPID.PackingStyleID, CPID.SockingType, CPS.PackingStyle";
        $sqlSelect = "SELECT $sqlFields FROM `CsStockData` AS CPID 
        LEFT JOIN AdminPackingStyles AS CPS ON CPS.PCKID=CPID.PackingStyleID 
        LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID  
        LEFT JOIN ScrmProductList AS CPL ON CPID.ProductionID=CPL.ProductID
        WHERE $DateFilters  $Query 
        AND CPID.ProductionTypeID IN ('Production','Melting')
        GROUP BY PIVL.ShortName, CPID.PackingStyleID, PIVL.Treatment, PIVL.Species ORDER BY PIVL.TypeProduct ASC";
        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();

        $GrandTotals = [];
        $SNo = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
            $ArrayList['SNo'] = ++$SNo;
            $ArrayList['ShortName'] = $dataRow['ShortName'];
            $ArrayList['SockingType'] = $dataRow['SockingType'];
            $ArrayList['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayList['Species'] = $dataRow['Species'];

            $ShortName = $dataRow['ShortName'];
            $SockingType = $dataRow['SockingType'];
            $PackingStyleID = $dataRow['PackingStyleID'];
            $Species = $dataRow['Species'];

            $sqlType = "SELECT TypeProduct,FreezingType FROM ScrmPIVarietyList 
            WHERE 1=1 $QueryTwo GROUP BY TypeProduct,FreezingType ORDER BY TypeProduct ASC";
            $stmtType = $this->conn->prepare($sqlType);
            $stmtType->execute();

            while ($rowType = $stmtType->fetch(PDO::FETCH_ASSOC)) {
                $TypeProduct = $rowType['TypeProduct'];
                $FreezingType = $rowType['FreezingType'];
                $TypeProductCombination = $rowType['TypeProduct'] . '-' . $rowType['FreezingType'];
                $slabFields = "SUM(TotalSlabs*ActualMcs) AS McsSlabs, 
                SUM(ActualLooseBags) AS LooseSlabs, 
                SUM(ActualLooseBags / NULLIF(CPS.TotalSlabs, 0)) AS LooseMcs,
                SUM(ActualMcs) AS ActualMcs,
                SUM(ActualMcs*TotalSlabs*SlabWeight*MFactor) as Kgs,
                SUM(ActualLooseBags*SlabWeight*MFactor) as LKgs,sum(ActualKgs) as ActualKgs";

                $sqlMonthWiseSlabs = "SELECT $slabFields FROM `CsStockData` AS CPID 
                LEFT JOIN AdminPackingStyles AS CPS ON CPS.PCKID=CPID.PackingStyleID 
                LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID  
                LEFT JOIN ScrmProductList AS CPL ON CPID.ProductionID=CPL.ProductID
                WHERE $DateFilters AND PIVL.ShortName = '$ShortName' AND CPID.PackingStyleID = '$PackingStyleID' 
                AND PIVL.Treatment='$SockingType' AND PIVL.TypeProduct = '$TypeProduct' AND PIVL.FreezingType = '$FreezingType'
                AND PIVL.Species = '$Species' 
                AND CPID.ProductionTypeID IN ('Production','Melting')
                $Query ";
                $stmtMonthWiseSlabs = $this->conn->prepare($sqlMonthWiseSlabs);
                $stmtMonthWiseSlabs->execute();
                $TotalSlabs = 0;
                $GrossWeight = 0;
                while ($rowMonthWise = $stmtMonthWiseSlabs->fetch(PDO::FETCH_ASSOC)) {
                    if($SlabType == "MCS"){
                        $McsSlabs = $rowMonthWise['ActualMcs'] ? $rowMonthWise['ActualMcs'] : 0;                        
                        $LooseSlabs = $rowMonthWise['LooseMcs'] ? $rowMonthWise['LooseMcs'] : 0;
                    }else{
                        $McsSlabs = $rowMonthWise['McsSlabs'] ? $rowMonthWise['McsSlabs'] : 0;                        
                        $LooseSlabs = $rowMonthWise['LooseSlabs'] ? $rowMonthWise['LooseSlabs'] : 0;
                    }
                    // $McsSlabs = $rowMonthWise['McsSlabs'] ? $rowMonthWise['McsSlabs'] : 0;
                    $KGs = $rowMonthWise['Kgs'] ? $rowMonthWise['Kgs'] : 0;
                    $LKGS = $rowMonthWise['LKgs'] ? $rowMonthWise['LKgs'] : 0;
                    $ActualKgs = $rowMonthWise['ActualKgs'] ? $rowMonthWise['ActualKgs'] : 0;


                    $TotalSlabs = round($McsSlabs + $LooseSlabs, 2);
                    // $GrossWeight = $KGs + $LKGS;
                    $GrossWeight = $ActualKgs;

                }
                $ArrayList['TotalSlabs'] = $TotalSlabs;
                $ArrayList['GrossWeight'] = $GrossWeight;

                if ($TotalSlabs > 0 && $GrossWeight > 0) { 
                    $GrandTotals[$TypeProductCombination]['TotalSlabs'] += $TotalSlabs;
                    $GrandTotals[$TypeProductCombination]['GrossWeight'] += $GrossWeight;
                }
            }
        }

        $Data['GrandTotals'] = $GrandTotals;
        return $GrandTotals;
    }
}
