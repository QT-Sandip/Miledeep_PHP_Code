<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';

class PhysicalReportController

{
    private $conn;
    //	private $db_table = "ScrmApplicants";
    private $utilController;
    private $Error = null;
    private $Msg = '';


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        // $this->utilController = new UtilController($this->conn);
    }

    function getPhysicalDetails($RequestBody)
    {
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $ProductionPlant = $RequestBody['ProductionPlant'];
        $Variety = $RequestBody['Variety'];
        $Grade = $RequestBody['Grade'];
        $Species = $RequestBody['Species'];
        $Treatment = $RequestBody['Treatment'];
        $PackingStyle = $RequestBody['PackingStyle'];

        $Query = '';
        if ($ProductionPlant != "") {
            $Query .= " AND CSD.PlantID = '$ProductionPlant'";
        }
        if ($Variety != "") {
            $Query .= " AND SPVL.ShortName = '$Variety'";
        }
        if ($Grade != "") {
            $Query .= " AND CSD.Grade = '$Grade'";
        }
        if ($Species != "") {
            $Query .= " AND SPVL.Species = '$Species'";
        }
        if ($Treatment != "") {
            $Query .= " AND CSD.SockingType = '$Treatment'";
        }
        if ($PackingStyle != "") {
            $Query .= " AND CSD.PackingStyleID = '$PackingStyle'";
        }

        $DataFilters = " BETWEEN '$FromDate' AND '$ToDate'";

        $sqlInFields = "ProductDescription, CSD.Grade, CSD.FreezingType, CSD.SockingType, CSD.PackingTypeID,RPS.ProdCode,RPS.StationName,ProductionTypeID AS OutType,
        APS.PackingStyle, PoNo, SPVL.DeviSUPC, SPVL.ShortName,DeviSUPC,CSD.TransactionDate, CSD.ProductionDate,SUM(CSD.ActualKgs) as KGs,SUM(CSD.ActualMcs) AS Mcs,SUM(CSD.ActualLooseBags) AS LooseBags";
        $sqlInQuery = "SELECT $sqlInFields FROM CsStockData AS CSD
        LEFT JOIN ScrmPoData AS SPD ON SPD.PoID=CSD.PoID
        LEFT JOIN ScrmPIVarietyList AS SPVL ON SPVL.VarietyID=CSD.VarietyID
        LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID=CSD.PackingStyleID
        LEFT JOIN RmProcessingStations AS RPS ON CSD.PlantID=RPS.ProcessingStationID
        WHERE ((CSD.ProductionDate $DataFilters AND CSD.ProductionTypeID IN ('Physical In','FG IN'))) $Query  GROUP BY CSD.VarietyID";


        $stmtSelect = $this->conn->prepare($sqlInQuery);
        $stmtSelect->execute();

        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($sqlQuery);
        // exit;

        $ArrayPhysicalData = [];
        $TotalMcs = 0;
        $TotalLooseBags = 0;
        $TotalOutKGs = 0;
        $TotalBalanceKGs = 0;
        $ProductionMcs = 0;
        $ProductionLoose = 0;
        $TotalProductionKGs = 0;
        $TotalInMcs = 0;
        $TotalInLooseBags = 0;
        $TotalInKGs = 0;
        $PhysicalTotals = [];
        $i = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            // $StockOutID = $dataRow['StockOutIDs'];
            // $BreakUpData = $this->getThawingBreakUpData($StockOutID);
            $ArrayShipmentList['SNO'] = ++$i;
            // $ArrayShipmentList['StockOutID'] = $dataRow['StockOutIDs'];
            $ArrayShipmentList['DeviSUPC'] = $dataRow['DeviSUPC'];
            $ArrayShipmentList['PoNo'] = $dataRow['PoNo'] ? $dataRow['PoNo'] : 'NA';
            $ArrayShipmentList['ShortName'] = $dataRow['ShortName'];
            $ArrayShipmentList['ProductDescription'] = $dataRow['ProductDescription'];
            $ArrayShipmentList['Grade'] = $dataRow['Grade'];
            // $ArrayShipmentList['OutDate'] = $dataRow['OutDate'] ? date('d-m-Y', strtotime($dataRow['OutDate'])) : '-';
            $ArrayShipmentList['TransactionDate'] = $dataRow['TransactionDate'] ? date('d-m-Y', strtotime($dataRow['TransactionDate'])) : 'NA';
            $ArrayShipmentList['ProductionDate'] = $dataRow['ProductionDate'] ? date('d-m-Y', strtotime($dataRow['ProductionDate'])) : 'NA';
            $ArrayShipmentList['PackingTypeID'] = $dataRow['PackingTypeID'];
            $ArrayShipmentList['FreezingType'] = $dataRow['FreezingType'];
            $ArrayShipmentList['SockingType'] = $dataRow['SockingType'];
            $ArrayShipmentList['PackingTypeID'] = $dataRow['PackingTypeID'];
            $ArrayShipmentList['PackingStyle'] = $dataRow['PackingStyle'];
            // $ArrayShipmentList['OutMcs'] = $dataRow['OutMcs'];
            // $ArrayShipmentList['OutLooseBags'] = $dataRow['OutLooseBags'];
            $ArrayShipmentList['Mcs'] = $dataRow['Mcs'];
            $ArrayShipmentList['StationName'] = $dataRow['StationName'];
            $ArrayShipmentList['LooseBags'] = $dataRow['LooseBags'];
            
                $ArrayShipmentList['type'] = "IN";
                $TotalKGs = $dataRow['KGs'];
                $TotalInKGs += $TotalKGs;
                $ArrayShipmentList['OutType'] = $dataRow['OutType'];
                $TotalInMcs += $dataRow['Mcs'];
                $TotalInLooseBags += $dataRow['LooseBags'];
            // $TotalKGs = $dataRow['KGs'] + $dataRow['LKGs'];

            $ArrayShipmentList['TotalKGs'] = number_format($TotalKGs, 2);
            // $ProductionKGs = $BreakUpData['TotalProductionKGs'];
            // $BalanceKGs = $TotalKGs - $ProductionKGs;
            // $ArrayShipmentList['BalanceKGs'] = number_format($BalanceKGs, 2);
            // $ArrayShipmentList['BreakUpData'] = $BreakUpData['ArrayBreakUpData'];

            $TotalMcs += $dataRow['OutMcs'];
            $TotalLooseBags += $dataRow['OutLooseBags'];
            // $ProductionMcs += $BreakUpData['TotalProductionMcs'];
            // $ProductionLoose += $BreakUpData['TotalProductionLooseBags'];
            // $TotalProductionKGs += $ProductionKGs;
            $ArrayPhysicalData[] =  $ArrayShipmentList;

        }





        $sqlFields = "GROUP_CONCAT(CSOD.StockOutID) AS StockOutIDs, SUM(OutMcs) AS OutMcs, SUM(OutLooseBags) AS OutLooseBags,
        sum(ActualOutKgs) as ActualOutKgs,ProductDescription, CSD.Grade, CSD.FreezingType, CSD.SockingType, CSD.PackingTypeID,RPS.ProdCode,RPS.StationName,
        CSOD.OutDate, APS.PackingStyle, PoNo, SPVL.DeviSUPC, SPVL.ShortName,OutType,DeviSUPC,CSD.TransactionDate, CSD.ProductionDate,SUM(CSD.ActualKgs) as KGs,SUM(CSD.ActualMcs) AS Mcs,SUM(CSD.ActualLooseBags) AS LooseBags";
        $sqlQuery = "SELECT $sqlFields FROM CsStockData AS CSD
        LEFT JOIN CsStockOutDetails AS CSOD ON CSOD.StockID = CSD.StockID
        LEFT JOIN ScrmPoData AS SPD ON SPD.PoID=CSD.PoID
        LEFT JOIN ScrmPIVarietyList AS SPVL ON SPVL.VarietyID=CSD.VarietyID
        LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID=CSD.PackingStyleID
        LEFT JOIN RmProcessingStations AS RPS ON CSD.PlantID=RPS.ProcessingStationID
        WHERE ((CSOD.OutDate $DataFilters AND CSOD.OutType IN ('Physical Out'))) $Query  GROUP BY CSD.VarietyID";

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($sqlQuery);
        // exit;

        $i = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $StockOutID = $dataRow['StockOutIDs'];
            $BreakUpData = $this->getThawingBreakUpData($StockOutID);
            $ArrayShipmentList['SNO'] = ++$i;
            $ArrayShipmentList['StockOutID'] = $dataRow['StockOutIDs'];
            $ArrayShipmentList['DeviSUPC'] = $dataRow['DeviSUPC'];
            $ArrayShipmentList['PoNo'] = $dataRow['PoNo'] ? $dataRow['PoNo'] : 'NA';
            $ArrayShipmentList['ShortName'] = $dataRow['ShortName'];
            $ArrayShipmentList['ProductDescription'] = $dataRow['ProductDescription'];
            $ArrayShipmentList['Grade'] = $dataRow['Grade'];
            $ArrayShipmentList['OutDate'] = $dataRow['OutDate'] ? date('d-m-Y', strtotime($dataRow['OutDate'])) : '-';
            $ArrayShipmentList['TransactionDate'] = $dataRow['TransactionDate'] ? date('d-m-Y', strtotime($dataRow['TransactionDate'])) : 'NA';
            $ArrayShipmentList['ProductionDate'] = $dataRow['ProductionDate'] ? date('d-m-Y', strtotime($dataRow['ProductionDate'])) : 'NA';
            $ArrayShipmentList['PackingTypeID'] = $dataRow['PackingTypeID'];
            $ArrayShipmentList['FreezingType'] = $dataRow['FreezingType'];
            $ArrayShipmentList['SockingType'] = $dataRow['SockingType'];
            $ArrayShipmentList['PackingTypeID'] = $dataRow['PackingTypeID'];
            $ArrayShipmentList['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayShipmentList['OutMcs'] = $dataRow['OutMcs'];
            $ArrayShipmentList['OutLooseBags'] = $dataRow['OutLooseBags'];
            $ArrayShipmentList['Mcs'] = $dataRow['Mcs'];
            $ArrayShipmentList['StationName'] = $dataRow['StationName'];
            $ArrayShipmentList['LooseBags'] = $dataRow['LooseBags'];
                $ArrayShipmentList['type'] = "OUT";
                $TotalKGs = $dataRow['ActualOutKgs'];
                $TotalOutKGs += $TotalKGs;
                $ArrayShipmentList['OutType'] = 'Physical Out';
            
            // $TotalKGs = $dataRow['KGs'] + $dataRow['LKGs'];

            $ArrayShipmentList['TotalKGs'] = number_format($TotalKGs, 2);
            $ProductionKGs = $BreakUpData['TotalProductionKGs'];
            $BalanceKGs = $TotalKGs - $ProductionKGs;
            $ArrayShipmentList['BalanceKGs'] = number_format($BalanceKGs, 2);
            $TotalBalanceKGs += $BalanceKGs;
            $ArrayShipmentList['BreakUpData'] = $BreakUpData['ArrayBreakUpData'];

            $TotalMcs += $dataRow['OutMcs'];
            $TotalLooseBags += $dataRow['OutLooseBags'];
            $ProductionMcs += $BreakUpData['TotalProductionMcs'];
            $ProductionLoose += $BreakUpData['TotalProductionLooseBags'];
            $TotalProductionKGs += $ProductionKGs;
            $ArrayPhysicalData[] =  $ArrayShipmentList;

        }

        $PhysicalTotals = [
            "TotalOutMcs" => $TotalMcs,
            "TotalOutLooseBags" => $TotalLooseBags,
            "TotalOutKGs" => $TotalOutKGs,
            "TotalInMcs" => $TotalInMcs,
            "TotalInLooseBags" => $TotalInLooseBags,
            "TotalInKGs" => $TotalInKGs,
            "TotalBalanceKGs" => $TotalBalanceKGs,
            "ProductionMcs" => $ProductionMcs,
            "ProductionLoose" => $ProductionLoose,
            "ProductionKGS" => number_format($TotalProductionKGs, 2),
        ];

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['ArrayPhysicalData'] = $ArrayPhysicalData;
        $data['PhysicalTotals'] = $PhysicalTotals;

        return $data;
    }

    public function getThawingBreakUpData($StockOutID)
    {
        $sqlBreakUpFields = "CPID.ActualMcs, CPID.ActualLooseBags,
        (CPID.ActualMcs*TotalSlabs*SlabWeight*MFactor) as KGs, (CPID.ActualLooseBags*SlabWeight*MFactor) as LKGs,ActualKgs,
        ProductDescription, CPID.Grade, CPID.FreezingType, CPID.SockingType, CPID.PackingTypeID, RPS.StationName,
        APS.PackingStyle, PoNo, SPVL.DeviSUPC, SPVL.ShortName, CPID.ProductionDate, CPID.TransactionDate, CPID.ProductionTypeID, CPID.Remarks";
        $sqlBreakUpSelect = "SELECT $sqlBreakUpFields FROM CsStockData AS CPID
        LEFT JOIN CsDailyProductionData AS CDPD ON CDPD.ProductionID = CPID.ProductionID
        LEFT JOIN ScrmPoData AS SPD ON SPD.PoID=CPID.PoID
        LEFT JOIN ScrmPIVarietyList AS SPVL ON SPVL.VarietyID=CPID.VarietyID
        LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID=CPID.PackingStyleID
        LEFT JOIN RmProcessingStations AS RPS ON CPID.PlantID = RPS.ProcessingStationID
        WHERE CDPD.StockOutID IN ($StockOutID)";
        $stmtBreakUpSelect = $this->conn->prepare($sqlBreakUpSelect);
        $stmtBreakUpSelect->execute();

        // $errorInfo = $stmtBreakUpSelect->errorInfo();
        // var_dump($errorInfo);

        $ArrayBreakUpData = [];
        $TotalProductionMcs = 0;
        $TotalProductionLooseBags = 0;
        $TotalProductionKGs = 0;
        while ($rowBreakUpData = $stmtBreakUpSelect->fetch(PDO::FETCH_ASSOC)) {
            $ProductionDate = $rowBreakUpData['ProductionDate'];
            $TransactionDate = $rowBreakUpData['TransactionDate'];
            $ArrayBreakUpList['ProductionDate'] = $ProductionDate ? date('d-m-Y', strtotime($ProductionDate)) : '-';
            $ArrayBreakUpList['TransactionDate'] = $TransactionDate ? date('d-m-Y', strtotime($TransactionDate)) : '-';
            $ArrayBreakUpList['ActualMcs'] = $rowBreakUpData['ActualMcs'];
            $ArrayBreakUpList['ActualLooseBags'] = $rowBreakUpData['ActualLooseBags'];
            // $ProductionKGs = $rowBreakUpData['KGs'] + $rowBreakUpData['LKGs'];
            $ProductionKGs = $rowBreakUpData['ActualKgs']; //uma

            $ArrayBreakUpList['TotalKGs'] = number_format($ProductionKGs, 2);
            $ArrayBreakUpList['ProductDescription'] = $rowBreakUpData['ProductDescription'];
            $ArrayBreakUpList['Grade'] = $rowBreakUpData['Grade'];
            $ArrayBreakUpList['FreezingType'] = $rowBreakUpData['FreezingType'];
            $ArrayBreakUpList['SockingType'] = $rowBreakUpData['SockingType'];
            $ArrayBreakUpList['PackingTypeID'] = $rowBreakUpData['PackingTypeID'];
            $ArrayBreakUpList['ProductionTypeID'] = $rowBreakUpData['ProductionTypeID'];
            $ArrayBreakUpList['PackingStyle'] = $rowBreakUpData['PackingStyle'];
            $ArrayBreakUpList['PoNo'] = $rowBreakUpData['PoNo'] ? $rowBreakUpData['PoNo'] : 'NA';
            $ArrayBreakUpList['DeviSUPC'] = $rowBreakUpData['DeviSUPC'];
            $ArrayBreakUpList['ShortName'] = $rowBreakUpData['ShortName'];
            $ArrayBreakUpList['StationName'] = $rowBreakUpData['StationName'];
            $ArrayBreakUpList['Remarks'] = $rowBreakUpData['Remarks'];

            $TotalProductionMcs += $rowBreakUpData['ActualMcs'];
            $TotalProductionLooseBags += $rowBreakUpData['ActualLooseBags'];
            $TotalProductionKGs += $ProductionKGs;
            $ArrayBreakUpData[] = $ArrayBreakUpList;
        }
        $Data['ArrayBreakUpData'] = $ArrayBreakUpData;
        $Data['TotalProductionMcs'] = $TotalProductionMcs;
        $Data['TotalProductionLooseBags'] = $TotalProductionLooseBags;
        $Data['TotalProductionKGs'] = $TotalProductionKGs;

        return $Data;
    }
}
