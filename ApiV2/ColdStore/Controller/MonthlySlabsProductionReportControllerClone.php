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
        $Species = $RequestBody['Species'];
        $ProductionPlant = $RequestBody['ProductionPlant'];
        $Type = $RequestBody['Type'];

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

        $DateFilters = " CPID.ProductionDate BETWEEN '$FromDate' AND '$ToDate'";

        $sqlFields = "CPID.VarietyID, PIVL.ShortName, PIVL.Species, CPID.PackingStyleID, CPID.SockingType, PIVL.TypeProduct, CPS.PackingStyle";
        $sqlSelect = "SELECT $sqlFields FROM `CsStockData` AS CPID 
        LEFT JOIN AdminPackingStyles AS CPS ON CPS.PCKID=CPID.PackingStyleID 
        LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID  
        LEFT JOIN ScrmProductList AS CPL ON CPID.ProductionID=CPL.ProductID
        WHERE $DateFilters  $Query AND CPID.ProductionTypeID='Production'
        GROUP BY PIVL.ShortName, CPID.PackingStyleID, PIVL.Treatment, PIVL.Species, PIVL.TypeProduct";

        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();

        $ProductionDates = $this->getDates($RequestBody);
        $ArrayVarietyData = [];
        $SNo = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayList['SNo'] = ++$SNo;
            $ArrayList['ShortName'] = $dataRow['ShortName'];
            $ArrayList['SockingType'] = $dataRow['SockingType'];
            $ArrayList['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayList['Species'] = $dataRow['Species'];
            $ArrayList['TypeProduct'] = $dataRow['TypeProduct'];

            $ShortName = $dataRow['ShortName'];
            $SockingType = $dataRow['SockingType'];
            $PackingStyle = $dataRow['PackingStyle'];
            $PackingStyleID = $dataRow['PackingStyleID'];
            $Species = $dataRow['Species'];
            $TypeProduct = $dataRow['TypeProduct'];

            $SlabsArray = [];
            $GrandTotals = 0;
            foreach ($ProductionDates as $ProductionDate) {
                $ProductionDate =  date('Y-m-d', strtotime($ProductionDate));
                $slabFields = "SUM(TotalSlabs*AvalMcs) AS McsSlabs, SUM(LooseBags) AS LooseSlabs";
                $sqlSlabs = "SELECT $slabFields FROM `CsStockData` AS CPID 
                LEFT JOIN AdminPackingStyles AS CPS ON CPS.PCKID=CPID.PackingStyleID 
                LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID  
                LEFT JOIN ScrmProductList AS CPL ON CPID.ProductionID=CPL.ProductID
                WHERE PIVL.ShortName = '$ShortName' AND CPID.PackingStyleID = '$PackingStyleID' 
                AND PIVL.Treatment='$SockingType' AND PIVL.TypeProduct = '$TypeProduct' AND CPID.ProductionDate = '$ProductionDate' 
                AND PIVL.Species = '$Species' AND CPID.ProductionTypeID='Production'";
                $stmtSlabs = $this->conn->prepare($sqlSlabs);
                $stmtSlabs->execute();

                while ($rowSlabs = $stmtSlabs->fetch(PDO::FETCH_ASSOC)) {

                    $Mcs = $rowSlabs['McsSlabs'] ? $rowSlabs['McsSlabs'] : 0;
                    $LooseBags = $rowSlabs['LooseSlabs'] ? $rowSlabs['LooseSlabs'] : 0;

                    $TotalSlabs = $Mcs + $LooseBags;
                    $SlabsList['TotalSlabs'] = $TotalSlabs;
                    $GrandTotals += $TotalSlabs;
                    $SlabsArray[] = $SlabsList;
                }
            }
            $SlabsArray[] = ['TotalSlabs' => $GrandTotals];
            $ArrayList['SlabsData'] = $SlabsArray;
            $ArrayVarietyData[] = $ArrayList;
        }

        $Dates = $this->getDates($RequestBody);
        $TypeProductData = $this->getTypeProductMonthWiseData($RequestBody);
        $MonthWiseData = $this->getMonthWiseSlabsData($RequestBody);
        $TypeProductDateWiseData = $this->getTypeProductDateWiseData($RequestBody);
        $MonthWiseTotals = $this->getMonthWiseTotals($RequestBody);
        $TypeProductDateWiseTotals = $this->getTypeProductDateWiseTotals($RequestBody);

        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['MonthWiseTotals'] = $MonthWiseTotals;
        $Data['DayWiseData'] = $ArrayVarietyData;
        $Data['MonthWiseData'] = $MonthWiseData;
        $Data['Dates'] = $Dates;
        $Data['TypeProductData'] = $TypeProductData;
        $Data['TypeProductDateWiseData'] = $TypeProductDateWiseData;
        $Data['TypeProductDateWiseTotals'] = $TypeProductDateWiseTotals;

        return $Data;
    }

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



    function getTypeProductDateWiseTotals($RequestBody)
    {
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $TypeProduct = $RequestBody['TypeProduct'];
        $Species = $RequestBody['Species'];
        $ProductionPlant = $RequestBody['ProductionPlant'];
        $Type = $RequestBody['Type'];

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

        $DateFilters = " CPID.ProductionDate BETWEEN '$FromDate' AND '$ToDate'";

        $sqlFields = "CPID.VarietyID, PIVL.ShortName, PIVL.Species, CPID.PackingStyleID, CPID.SockingType, PIVL.TypeProduct, CPS.TotalSlabs ";
        $sqlSelect = "SELECT $sqlFields FROM `CsStockData` AS CPID 
        LEFT JOIN AdminPackingStyles AS CPS ON CPS.PCKID=CPID.PackingStyleID 
        LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID  
        LEFT JOIN ScrmProductList AS CPL ON CPID.ProductionID=CPL.ProductID
        WHERE $DateFilters  $Query AND CPID.ProductionTypeID='Production'
        GROUP BY PIVL.ShortName, CPID.PackingStyleID, PIVL.Treatment, PIVL.Species, PIVL.TypeProduct";

        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();

        $ProductionDates = $this->getDates($RequestBody);
        $ArrayVarietyData = [];
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ShortName = $dataRow['ShortName'];
            $SockingType = $dataRow['SockingType'];
            $PackingStyleID = $dataRow['PackingStyleID'];
            $Species = $dataRow['Species'];
            $TypeProduct = $dataRow['TypeProduct'];

            $SlabsArray = [];
            $GrandTotals = 0;
            foreach ($ProductionDates as $ProductionDate) {
                $ProductionDate =  date('Y-m-d', strtotime($ProductionDate));
                $slabFields = "SUM(TotalSlabs*AvalMcs) AS McsSlabs, SUM(LooseBags) AS LooseSlabs";
                $sqlSlabs = "SELECT $slabFields FROM `CsStockData` AS CPID 
                LEFT JOIN AdminPackingStyles AS CPS ON CPS.PCKID=CPID.PackingStyleID 
                LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID  
                LEFT JOIN ScrmProductList AS CPL ON CPID.ProductionID=CPL.ProductID
                WHERE PIVL.ShortName = '$ShortName' AND CPID.PackingStyleID = '$PackingStyleID' 
                AND PIVL.Treatment='$SockingType' AND PIVL.TypeProduct = '$TypeProduct' AND CPID.ProductionDate = '$ProductionDate' 
                AND PIVL.Species = '$Species' AND CPID.ProductionTypeID='Production'";
                $stmtSlabs = $this->conn->prepare($sqlSlabs);
                $stmtSlabs->execute();

                while ($rowSlabs = $stmtSlabs->fetch(PDO::FETCH_ASSOC)) {

                    $Mcs = $rowSlabs['McsSlabs'] ? $rowSlabs['McsSlabs'] : 0;
                    $LooseBags = $rowSlabs['LooseSlabs'] ? $rowSlabs['LooseSlabs'] : 0;

                    $TotalBags = $Mcs + $LooseBags;
                    $SlabsList['TotalSlabs'] = $TotalBags;
                    $GrandTotals += $TotalBags;
                    $SlabsArray[] = $SlabsList;
                }
                $SlabsArray[] = ['TotalSlabs' => $GrandTotals];
                $ArrayList['SlabsData'] = $SlabsArray;
                $ArrayVarietyData[] = $ArrayList;
            }
        }
        $Data['DayWiseData'] = $ArrayVarietyData;


        return $Data;
    }


    public function getTypeProductMonthWiseData($RequestBody)
    {

        $Month = $RequestBody['Month'];
        $Year = $RequestBody['Year'];
        $Type = $RequestBody['Type'];

        $TypeProduct = $RequestBody['TypeProduct'];
        $Species = $RequestBody['Species'];
        $ProductionPlant = $RequestBody['ProductionPlant'];

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
            $slabFields = "SUM(TotalSlabs*AvalMcs) AS McsSlabs, SUM(LooseBags) AS LooseSlabs, 
            SUM(ActualMcs*TotalSlabs*SlabWeight*MFactor) as Kgs,
            SUM(ActualLooseBags*SlabWeight*MFactor) as LKgs";
            $sqlSlabs = "SELECT $slabFields FROM `CsStockData` AS CPID 
            LEFT JOIN AdminPackingStyles AS CPS ON CPS.PCKID=CPID.PackingStyleID 
            LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID  
            LEFT JOIN ScrmProductList AS CPL ON CPID.ProductionID=CPL.ProductID
            WHERE $DateFilters $Query AND PIVL.TypeProduct = '$TypeProduct' AND CPID.ProductionTypeID='Production'";
            $stmtSlabs = $this->conn->prepare($sqlSlabs);
            $stmtSlabs->execute();

            while ($rowProduct = $stmtSlabs->fetch(PDO::FETCH_ASSOC)) {

                $TypeWiseLooseBags = $rowProduct['LooseSlabs'] ? $rowProduct['LooseSlabs'] : 0;
                $TypeWiseMcs = $rowProduct['McsSlabs'] ? $rowProduct['McsSlabs'] : 0;

                $KGs = $rowProduct['Kgs'] ? $rowProduct['Kgs'] : 0;
                $LKGS = $rowProduct['LKgs'] ? $rowProduct['LKgs'] : 0;

                $TotalSlabs = ($TypeWiseMcs + $TypeWiseLooseBags);
                $GrossWeight = $KGs + $LKGS;

                $TypeWiseTotalSlabs = $TotalSlabs;
                $TypeWiseGrossWeight = $GrossWeight;
                $TypeWiseList['TypeWiseTotalSlabs'] = $TypeWiseTotalSlabs;
                $TypeWiseList['TypeWiseGrossWeight'] = $TypeWiseGrossWeight;
            }
            $TypeProductArray[$TypeProduct] = $TypeWiseList;
        }
        return $TypeProductArray;
    }

    public function getTypeProductDateWiseData($RequestBody)
    {
        $TypeProduct = $RequestBody['TypeProduct'];
        $Species = $RequestBody['Species'];
        $ProductionPlant = $RequestBody['ProductionPlant'];

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

        $sqlType = "SELECT DISTINCT TypeProduct FROM ScrmPIVarietyList WHERE 1=1 $QueryTwo ";
        $stmtType = $this->conn->prepare($sqlType);
        $stmtType->execute();

        $ProductionDates = $this->getDates($RequestBody);
        $TypeProductArray = [];
        $TypeWiseTotalSlabs = 0;
        while ($rowType = $stmtType->fetch(PDO::FETCH_ASSOC)) {
            $TypeWiseArray = [];
            $GrandTotals = 0;

            foreach ($ProductionDates as $ProductionDate) {
                $ProductionDate =  date('Y-m-d', strtotime($ProductionDate));
                $TypeProduct = $rowType['TypeProduct'];
                $slabFields = "SUM(TotalSlabs*AvalMcs) AS McsSlabs, SUM(LooseBags) AS LooseSlabs";
                $sqlSlabs = "SELECT $slabFields FROM `CsStockData` AS CPID 
                LEFT JOIN AdminPackingStyles AS CPS ON CPS.PCKID=CPID.PackingStyleID 
                LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID  
                LEFT JOIN ScrmProductList AS CPL ON CPID.ProductionID=CPL.ProductID
                WHERE CPID.ProductionDate = '$ProductionDate' $Query AND PIVL.TypeProduct = '$TypeProduct' AND CPID.ProductionTypeID='Production'";
                $stmtSlabs = $this->conn->prepare($sqlSlabs);
                $stmtSlabs->execute();

                while ($rowProduct = $stmtSlabs->fetch(PDO::FETCH_ASSOC)) {
                    $TypeWiseLooseBags = $rowProduct['LooseSlabs'] ? $rowProduct['LooseSlabs'] : 0;
                    $TypeWiseMcs = $rowProduct['McsSlabs'] ? $rowProduct['McsSlabs'] : 0;

                    $TypeWiseTotalSlabs = $TypeWiseLooseBags + $TypeWiseMcs;
                    $TypeWiseList['TypeWiseTotalSlabs'] = $TypeWiseTotalSlabs;
                    $GrandTotals += $TypeWiseTotalSlabs;

                    $TypeWiseArray[] = $TypeWiseList;
                }
            }
            $TypeWiseArray[] = ['TypeWiseTotalSlabs' => $GrandTotals];
            $TypeProductArrayList['TypeProduct'] = $TypeProduct;
            $TypeProductArrayList['TypeProductData'] = $TypeWiseArray;
            $TypeProductArray[] = $TypeProductArrayList;
        }
        return $TypeProductArray;
    }

    public function getMonthWiseSlabsData($RequestBody)
    {

        $TypeProduct = $RequestBody['TypeProduct'];
        $Species = $RequestBody['Species'];
        $ProductionPlant = $RequestBody['ProductionPlant'];
        $Month = $RequestBody['Month'];
        $Year = $RequestBody['Year'];
        $FromDate = $Year . '-' . $Month . '-01';
        $ToDate = $Year . '-' . $Month . '-31';

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

        $DateFilters = " CPID.ProductionDate BETWEEN '$FromDate' AND '$ToDate'";

        $sqlFields = "CPID.VarietyID, PIVL.ShortName, PIVL.Species, CPID.PackingStyleID, CPID.SockingType, PIVL.TypeProduct, CPS.PackingStyle";
        $sqlSelect = "SELECT $sqlFields FROM `CsStockData` AS CPID 
        LEFT JOIN AdminPackingStyles AS CPS ON CPS.PCKID=CPID.PackingStyleID 
        LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID  
        LEFT JOIN ScrmProductList AS CPL ON CPID.ProductionID=CPL.ProductID
        WHERE $DateFilters  $Query AND CPID.ProductionTypeID='Production'
        GROUP BY PIVL.ShortName, CPID.PackingStyleID, PIVL.Treatment, PIVL.Species, PIVL.TypeProduct";
        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();

        $MonthWiseArrayData = [];
        $GrandTotalSlabs = 0;
        $GrandTotalGrossweight = 0;
        $SNo = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
            $ArrayList['SNo'] = ++$SNo;
            $ArrayList['ShortName'] = $dataRow['ShortName'];
            $ArrayList['SockingType'] = $dataRow['SockingType'];
            $ArrayList['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayList['Species'] = $dataRow['Species'];
            $ArrayList['TypeProduct'] = $dataRow['TypeProduct'];

            $ShortName = $dataRow['ShortName'];
            $SockingType = $dataRow['SockingType'];
            $PackingStyleID = $dataRow['PackingStyleID'];
            $Species = $dataRow['Species'];
            $TypeProduct = $dataRow['TypeProduct'];

            $slabFields = "SUM(TotalSlabs*AvalMcs) AS McsSlabs, SUM(LooseBags) AS LooseSlabs, 
            SUM(AvalMcs*TotalSlabs*SlabWeight*MFactor) as Kgs,
            SUM(LooseBags*SlabWeight*MFactor) as LKgs";
            $sqlMonthWiseSlabs = "SELECT $slabFields FROM `CsStockData` AS CPID 
            LEFT JOIN AdminPackingStyles AS CPS ON CPS.PCKID=CPID.PackingStyleID 
            LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID  
            LEFT JOIN ScrmProductList AS CPL ON CPID.ProductionID=CPL.ProductID
            WHERE $DateFilters AND PIVL.ShortName = '$ShortName' AND CPID.PackingStyleID = '$PackingStyleID' 
            AND PIVL.Treatment='$SockingType' AND PIVL.TypeProduct = '$TypeProduct' 
            AND PIVL.Species = '$Species' AND CPID.ProductionTypeID='Production'";
            $stmtMonthWiseSlabs = $this->conn->prepare($sqlMonthWiseSlabs);
            $stmtMonthWiseSlabs->execute();
            while ($rowMonthWise = $stmtMonthWiseSlabs->fetch(PDO::FETCH_ASSOC)) {

                $McsSlabs = $rowMonthWise['McsSlabs'] ? $rowMonthWise['McsSlabs'] : 0;
                $LooseSlabs = $rowMonthWise['LooseSlabs'] ? $rowMonthWise['LooseSlabs'] : 0;
                $KGs = $rowMonthWise['Kgs'] ? $rowMonthWise['Kgs'] : 0;
                $LKGS = $rowMonthWise['LKgs'] ? $rowMonthWise['LKgs'] : 0;
                $GrossWeight =

                    $TotalSlabs = ($McsSlabs + $LooseSlabs);
                $GrossWeight = $KGs + $LKGS;
            }
            $ArrayList['TotalSlabs'] = $TotalSlabs;
            $ArrayList['GrossWeight'] = $GrossWeight;
            $GrandTotalSlabs += $TotalSlabs;
            $GrandTotalGrossweight += $GrossWeight;
            $MonthWiseArrayData[] = $ArrayList;
        }

        $Data['MonthWiseArrayData'] = $MonthWiseArrayData;
        $Data['GrandTotalGrossweight'] = round($GrandTotalGrossweight, 3);
        $Data['GrandTotalSlabs'] = $GrandTotalSlabs;
        return $Data;
    }

    public function getMonthWiseTotals($RequestBody)
    {
        $TypeProduct = $RequestBody['TypeProduct'];
        $Species = $RequestBody['Species'];
        $ProductionPlant = $RequestBody['ProductionPlant'];
        $Month = $RequestBody['Month'];
        $Year = $RequestBody['Year'];
        $FromDate = $Year . '-' . $Month . '-01';
        $ToDate = $Year . '-' . $Month . '-31';

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

        $DateFilters = " CPID.ProductionDate BETWEEN '$FromDate' AND '$ToDate'";

        $sqlFields = "CPID.VarietyID, PIVL.ShortName, PIVL.Species, CPID.PackingStyleID, CPID.SockingType, CPS.PackingStyle";
        $sqlSelect = "SELECT $sqlFields FROM `CsStockData` AS CPID 
        LEFT JOIN AdminPackingStyles AS CPS ON CPS.PCKID=CPID.PackingStyleID 
        LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID  
        LEFT JOIN ScrmProductList AS CPL ON CPID.ProductionID=CPL.ProductID
        WHERE $DateFilters  $Query AND CPID.ProductionTypeID='Production'
        GROUP BY PIVL.ShortName, CPID.PackingStyleID, PIVL.Treatment, PIVL.Species";
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
            $PackingStyle = $dataRow['PackingStyle'];
            $PackingStyleID = $dataRow['PackingStyleID'];
            $Species = $dataRow['Species'];

            $sqlType = "SELECT DISTINCT TypeProduct FROM ScrmPIVarietyList WHERE 1=1 $QueryTwo";
            $stmtType = $this->conn->prepare($sqlType);
            $stmtType->execute();

            while ($rowType = $stmtType->fetch(PDO::FETCH_ASSOC)) {
                $TypeProduct = $rowType['TypeProduct'];
                $slabFields = "SUM(TotalSlabs*AvalMcs) AS McsSlabs, SUM(LooseBags) AS LooseSlabs, 
                SUM(AvalMcs*TotalSlabs*SlabWeight*MFactor) as Kgs,
                SUM(LooseBags*SlabWeight*MFactor) as LKgs";
                $sqlMonthWiseSlabs = "SELECT $slabFields FROM `CsStockData` AS CPID 
                LEFT JOIN AdminPackingStyles AS CPS ON CPS.PCKID=CPID.PackingStyleID 
                LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID  
                LEFT JOIN ScrmProductList AS CPL ON CPID.ProductionID=CPL.ProductID
                WHERE $DateFilters AND PIVL.ShortName = '$ShortName' AND CPID.PackingStyleID = '$PackingStyleID' 
                AND PIVL.Treatment='$SockingType' AND PIVL.TypeProduct = '$TypeProduct' 
                AND PIVL.Species = '$Species' AND CPID.ProductionTypeID='Production'";
                $stmtMonthWiseSlabs = $this->conn->prepare($sqlMonthWiseSlabs);
                $stmtMonthWiseSlabs->execute();

                $TotalSlabs = 0;
                $GrossWeight = 0;
                while ($rowMonthWise = $stmtMonthWiseSlabs->fetch(PDO::FETCH_ASSOC)) {

                    $McsSlabs = $rowMonthWise['McsSlabs'] ? $rowMonthWise['McsSlabs'] : 0;
                    $LooseSlabs = $rowMonthWise['LooseSlabs'] ? $rowMonthWise['LooseSlabs'] : 0;
                    $KGs = $rowMonthWise['Kgs'] ? $rowMonthWise['Kgs'] : 0;
                    $LKGS = $rowMonthWise['LKgs'] ? $rowMonthWise['LKgs'] : 0;
                    $GrossWeight =

                        $TotalSlabs = ($McsSlabs + $LooseSlabs);
                    $GrossWeight = $KGs + $LKGS;
                }
                $ArrayList['TotalSlabs'] = $TotalSlabs;
                $ArrayList['GrossWeight'] = $GrossWeight;

                if (!isset($GrandTotals[$TypeProduct])) {
                    $GrandTotals[$TypeProduct] = [
                        'TotalSlabs' => 0,
                        'GrossWeight' => 0
                    ];
                }
                $GrandTotals[$TypeProduct]['TotalSlabs'] += $TotalSlabs;
                $GrandTotals[$TypeProduct]['GrossWeight'] += $GrossWeight;
            }
        }

        $Data['GrandTotals'] = $GrandTotals;
        return $GrandTotals;
    }
}
