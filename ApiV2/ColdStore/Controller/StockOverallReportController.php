<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include($_SERVER['DOCUMENT_ROOT'] . "/includes/cache.php");

class StockOverallReportController
{
    private $conn;
    private $utilController;
    private $appController;
    private $Error = null;
    private $Msg = '';

    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }

    function getStockOverallDetails($RequestBody)
    {
        // Capture DataTables pagination variables safely (PHP 5.6 compliant)
        $Start  = isset($RequestBody['Start']) ? (int)$RequestBody['Start'] : 0;
        $Length = isset($RequestBody['Length']) ? (int)$RequestBody['Length'] : 10;

        $ClientID = $RequestBody['ClientID'];
        $ColdStore = $RequestBody['ColdStore'];
        $PlantID = $RequestBody['PlantID'];
        $Variety = $RequestBody['Variety'];
        $Grade = $RequestBody['Grade'];
        $Group = $RequestBody['Group'];
        $IsReturn = $RequestBody['IsReturn'];
        $Transtype = $RequestBody['Transtype'];
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $PackingStyle = $RequestBody['PackingStyle'];
        $ProductCategory = $RequestBody['ProductCategory'];
        $SockingType = $RequestBody['Soaking'];
        $TypeProduct = $RequestBody['TypeProduct'];
        $PackingType = $RequestBody['PackingType'];
        $FreezingType = $RequestBody['FreezingType'];
        $Species = $RequestBody['Species'];
        $AbStatus = $RequestBody['AbStatus'];

        $Query = '';
        if ($ColdStore != '') {
            $Query .= " AND CSD.UnitID = '$ColdStore'";
        }
        if ($PlantID != '') {
            $Query .= " AND CSD.PlantID = '$PlantID'";
        }

        if ($Variety != '') {
            $Query .= " AND SPVL.ShortName = '$Variety'";
        }

        if ($Grade != '') {
            $Query .= " AND CSD.Grade = '$Grade'";
        }

        if ($Group != '') {
            if ($IsReturn == 1) {
                $InwardGroup = " AND ProductionTypeID = '$Group'";
            }
            if ($Transtype == 2) {
                $OutwardGroup = " AND OutType = '$Group'";
            }
        }
        if ($PackingStyle != '') {
            $Query .= " AND CSD.PackingStyleID = '$PackingStyle'";
        }
        if ($ProductCategory != '') {
            $Query .= " AND CSD.ProductCategoryID = '$ProductCategory'";
        }

        if ($SockingType != '') {
            $Query .= " AND CSD.SockingType='$SockingType'";
        }
        if ($TypeProduct != '') {
            $Query .= " AND SPVL.TypeProduct='$TypeProduct'";
        }
        if ($PackingType != '') {
            $Query .= " AND CSD.PackingTypeID='$PackingType'";
        }
        if ($FreezingType != '') {
            $Query .= " AND CSD.FreezingType='$FreezingType'";
        }
        if ($Species != '') {
            $Query .= " AND SPVL.Species = '$Species'";
        }
        if ($AbStatus != '') {
            $Query .= " AND CSD.AbStatus='$AbStatus'";
        }

        $DateFilters = " BETWEEN '$FromDate' AND '$ToDate'";

        $sqlInwardFields = "CSD.ActualKgs AS KGs,CSD.ActualMcs AS Mcs,CSD.ActualLooseBags AS LooseBags,(CSD.ActualMcs * APS.TotalSlabs) AS McsSlabs,CSD.ProductionDate,SPVL.ProductDescription,CSD.ProductionTypeID,CSD.PoID,CSD.PlantID,RPS.StationName,CSD.UnitID,CSR.StoreName,CSD.Grade,APS.PackingStyle,CSD.ProductCategoryID,SPVL.ShortName,APS.TotalSlabs,Yields.HLValue,Yields.HONValue";

        $sqlInwardSelect = "
                SELECT $sqlInwardFields
                FROM CsStockData AS CSD
                LEFT JOIN ScrmPIVarietyList AS SPVL ON SPVL.VarietyID = CSD.VarietyID
                LEFT JOIN AdminPackingStyles AS APS ON CSD.PackingStyleID = APS.PCKID
                LEFT JOIN RmProcessingStations AS RPS ON CSD.PlantID = RPS.ProcessingStationID
                LEFT JOIN CsStores AS CSR ON CSD.UnitID = CSR.StoreID
                LEFT JOIN (
                    SELECT
                        Variety,        MAX(CASE WHEN TypeOfMaterial = 'HL' THEN YieldPercentage END) AS HLValue,
                        MAX(CASE WHEN TypeOfMaterial = 'HEAD ON' THEN YieldPercentage END) AS HONValue
                    FROM ProdStandardYields
                    GROUP BY Variety
                ) AS Yields ON SPVL.ShortName = Yields.Variety

                WHERE CSD.ProductionDate $DateFilters $Query $InwardGroup
                ORDER BY CSD.ProductionDate, SPVL.ShortName, CSD.UnitID, CSD.Grade ASC";

        $stmtInwardSelect = $this->conn->prepare($sqlInwardSelect);
        $stmtInwardSelect->execute();

        $ArrayInwardData = array();
        $serialNumber = 1;
        $TotalMcs = 0;
        $TotalKgs = 0;
        $TotalSlabs = 0;
        while ($dataInward = $stmtInwardSelect->fetch(PDO::FETCH_ASSOC)) {

            $Mcs = $dataInward['Mcs'] ? ($dataInward['Mcs']) : 0;
            $LooseBags = $dataInward['LooseBags'] ? ($dataInward['LooseBags'] / $dataInward['TotalSlabs']) : 0;

            $ArrayInwardList = array();
            $ArrayInwardList['SNO'] = $serialNumber++;
            $ArrayInwardList['OutDate'] = "";
            $ArrayInwardList['ProductionDate'] = $dataInward['ProductionDate'] ? date('d-m-Y', strtotime($dataInward['ProductionDate'])) : '';
            $ArrayInwardList['ProductDescription'] = $dataInward['ProductDescription'];

            if($dataInward['ProductionTypeID'] == 'Repacking Out'){
                $ArrayInwardList['ProductionTypeID'] = str_replace('Repacking Out', 'Repacking In', $dataInward['ProductionTypeID']);
            }else{
                $ArrayInwardList['ProductionTypeID'] = $dataInward['ProductionTypeID'] . ' +';
            }
            $ArrayInwardList['StationName'] = $dataInward['StationName'];
            $ArrayInwardList['StoreName'] = $dataInward['StoreName'];
            $ArrayInwardList['KGs'] = round($dataInward['KGs'], 3);
            $ArrayInwardList['Mcs'] = round(($Mcs + $LooseBags), 2);
            $ArrayInwardList['McsSlabs'] = $dataInward['McsSlabs'] + $dataInward['LooseBags'];
            $ArrayInwardList['LooseBags'] = $dataInward['LooseBags'];
            $ArrayInwardList['Grade'] = $dataInward['Grade'];
            $ArrayInwardList['PackingStyle'] = $dataInward['PackingStyle'];
            $ArrayInwardList['ProductCategory'] = $dataInward['ProductCategoryID'];
            $ArrayInwardList['Variety'] = $dataInward['ShortName'];
            $TotalMcs += $ArrayInwardList['Mcs'];
            $TotalKgs += $ArrayInwardList['KGs'];
            $TotalSlabs += $ArrayInwardList['McsSlabs'];

            $HLYield = $dataInward['HLValue'];
            $HONYield = $dataInward['HONValue'];
            if ($HLYield && $HLYield != '') {
                $ArrayInwardList['HLYield'] = round($HLYield, 3);
                $ArrayInwardList['HLTotal'] = round(($ArrayInwardList['KGs'] / $HLYield) * 100, 3);
            } else {
                $ArrayInwardList['HLYield'] = "--";
                $ArrayInwardList['HLTotal'] = "--";
            }
            if ($HONYield && $HONYield != '') {
                $ArrayInwardList['HONYield'] = round($HONYield, 3);
                $ArrayInwardList['HONTotal'] = round(($ArrayInwardList['KGs'] / $HONYield) * 100, 3);
            } else {
                $ArrayInwardList['HONYield'] = "--";
                $ArrayInwardList['HONTotal'] = "--";
            }

            $ArrayInwardData[] = $ArrayInwardList;
        }

        $sqlFields = "  SUM(CSOD.OutMcs) AS OutMcs,
            SUM(CSOD.OutLooseBags) AS OutLooseBags,
            SUM(CSOD.ActualOutKgs) AS ActualOutKgs,
            SUM(CSOD.OutMcs *TotalSlabs * SlabWeight * MFactor) AS KGs,
            SUM(CSOD.OutLooseBags * SlabWeight * MFactor) AS LKGs,
            OutType,
            CSD.Grade,
            CSD.UnitID,
            CSD.PackingStyleID,
            SPVL.ProductDescription,
            SPVL.ShortName,
            Yields.HLValue,
            Yields.HONValue,
            CSOD.OutDate,
            CSD.ProductionDate,
            APS.PackingStyle,
            RPS.StationName,
            CSR.StoreName,
            CSD.ProductCategoryID,
            TotalSlabs,
            SUM(CSOD.OutMcs *TotalSlabs) AS McsSlabs";

        $sqlSelect = "SELECT $sqlFields FROM CsStockOutDetails AS CSOD
            LEFT JOIN CsStockData AS CSD ON CSOD.StockID = CSD.StockID
            LEFT JOIN RmProcessingStations AS RPS ON CSD.PlantID = RPS.ProcessingStationID
            LEFT JOIN CsStores AS CSR ON CSD.UnitID = CSR.StoreID
            LEFT JOIN ScrmPIVarietyList AS SPVL ON SPVL.VarietyID = CSD.VarietyID
            LEFT JOIN (
                SELECT
                    Variety,
                    MAX(CASE WHEN TypeOfMaterial = 'HL' THEN YieldPercentage END) AS HLValue,
                    MAX(CASE WHEN TypeOfMaterial = 'HEAD ON' THEN YieldPercentage END) AS HONValue
                FROM ProdStandardYields
                GROUP BY Variety
            ) AS Yields ON Yields.Variety = SPVL.ShortName
            LEFT JOIN AdminPackingStyles AS APS ON CSD.PackingStyleID = APS.PCKID
            WHERE OutDate $DateFilters $Query $OutwardGroup
            GROUP BY OutDate, CSD.VarietyID, CSD.Grade, OutType, CSD.UnitID,CSD.PackingStyleID
            ORDER BY OutDate, CSD.VarietyID, CSD.UnitID, CSD.Grade ASC";

        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();

        $ArrayOutwardData = array();
        while ($dataOutward = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
            $Mcs = $dataOutward['OutMcs'] ? ($dataOutward['OutMcs']) : 0;
            $LooseBags = $dataOutward['OutLooseBags'] ? ($dataOutward['OutLooseBags'] / $dataOutward['TotalSlabs']) : 0;

            $ArrayOutwardList = array();
            $ArrayOutwardList['Mcs'] = round(($Mcs + $LooseBags) ,2);
            $ArrayOutwardList['SNO'] = $serialNumber++;
            $ArrayOutwardList['ProductDescription'] = $dataOutward['ProductDescription'];
            $ArrayOutwardList['OutDate'] = $dataOutward['OutDate'] ? date('d-m-Y', strtotime($dataOutward['OutDate'])) : '';
            $ArrayOutwardList['ProductionDate'] =  $dataOutward['ProductionDate'] ? date('d-m-Y', strtotime($dataOutward['ProductionDate'])) : '';

            if($dataOutward['OutType'] == 'Repacking Out'){
                $ArrayOutwardList['ProductionTypeID'] = $dataOutward['OutType'];
            }else{
                $ArrayOutwardList['ProductionTypeID'] = $dataOutward['OutType'] . ' -';
            }
            $ArrayOutwardList['StationName'] = $dataOutward['StationName'];
            $ArrayOutwardList['StoreName'] = $dataOutward['StoreName'];
            $ArrayOutwardList['KGs'] = $dataOutward['ActualOutKgs'];

            $ArrayOutwardList['Mcs'] = $Mcs;
            $ArrayOutwardList['McsSlabs'] = $dataOutward['McsSlabs'] + $dataOutward['OutLooseBags'];
            $ArrayOutwardList['LooseBags'] = $dataOutward['LooseBags'];
            $ArrayOutwardList['Grade'] = $dataOutward['Grade'];
            $ArrayOutwardList['PackingStyle'] = $dataOutward['PackingStyle'];
            $ArrayOutwardList['Variety'] = $dataOutward['ShortName'];
            $ArrayOutwardList['ProductCategory'] = $dataOutward['ProductCategoryID'];

            $TotalMcs += $ArrayOutwardList['Mcs'];
            $TotalKgs += $ArrayOutwardList['KGs'];
            $TotalSlabs += $ArrayOutwardList['McsSlabs'];

            $HLYield = $dataOutward['HLValue'];
            $HONYield = $dataOutward['HONValue'];
            if ($HLYield && $HLYield != '') {
                $ArrayOutwardList['HLYield'] = round($HLYield, 3);
                $ArrayOutwardList['HLTotal'] = round(($ArrayOutwardList['KGs'] / $HLYield) * 100, 3);
            } else {
                $ArrayOutwardList['HLYield'] = "--";
                $ArrayOutwardList['HLTotal'] = "--";
            }
            if ($HONYield && $HONYield != '') {
                $ArrayOutwardList['HONYield'] = round($HONYield, 3);
                $ArrayOutwardList['HONTotal'] = round(($ArrayOutwardList['KGs'] / $HONYield) * 100, 3);
            } else {
                $ArrayOutwardList['HONYield'] = "--";
                $ArrayOutwardList['HONTotal'] = "--";
            }

            $ArrayOutwardData[] = $ArrayOutwardList;
        }

        if ($IsReturn == 1) {
            $ArrayOutwardData = array();
        }
        if ($Transtype == 2) {
            $ArrayInwardData = array();
        }

        $TotalsData = array();
        $TotalsData['totalKgs'] = round($TotalKgs, 3);
        $TotalsData['totalMcs'] = $TotalMcs;
        $TotalsData['totalSlabs'] = $TotalSlabs;

        // Merge datasets completely
        $ArrayData = array_merge($ArrayInwardData, $ArrayOutwardData);

        // --- PAGINATION INTERCEPT ADDED HERE ---
        // 1. Calculate the total records before slicing
        $TotalRecordCount = count($ArrayData);

        // 2. Slice the exact chunk matching current DataTables view page parameters
        //$SlicedData = array_slice($ArrayData, $Start, $Length);
        if ($Length < 0) {
            $SlicedData = $ArrayData;
        } else {
            $SlicedData = array_slice($ArrayData, $Start, $Length);
        }

        // 3. Package it together nicely for the API bridge output
        $Data = array();
        $Data['Error'] = null;
        $Data['Msg'] = 'Msg';
        $Data['TotalRecords'] = $TotalRecordCount; // Passed back for tracking metrics
        $Data['StockOverallReport'] = $SlicedData; // Return ONLY the current page items
        $Data['Totals'] = $TotalsData;

        return $Data;
    }
}
