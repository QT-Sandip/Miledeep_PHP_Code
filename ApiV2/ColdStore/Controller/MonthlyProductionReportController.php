<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';

class MonthlyProductionReportController
{
    private $conn;
    private $utilController;
    private $Error = null;
    private $Msg = '';


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        // $this->utilController = new UtilController($this->conn);
    }

    function getMonthlyProductionReport($RequestBody)
    {

        $ColdstoreID = $RequestBody['ColdStoreID'];
        $Blocks = $RequestBody['Blocks'];
        $TenantID = $RequestBody['TenantID'];
        $PONumber = $RequestBody['PONumber'];
        $Species = $RequestBody['Species'];
        $TypeProduct = $RequestBody['TypeProduct'];
        $FreezingType = $RequestBody['FreezingType'];
        $Brand = $RequestBody['Brand'];
        $VarietyID = $RequestBody['VarietyID'];
        $Grade = $RequestBody['Grade'];
        $Group = $RequestBody['Group'];
        $IsReturnable = $RequestBody['IsReturnable'];
        $TransType = $RequestBody['TransType'];
        $PackingType = $RequestBody['PackingType'];
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $TotalKGs = $RequestBody['ToDate'];
        $TotalMcs = $RequestBody['ToDate'];
        $ToDate = $RequestBody['ToDate'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];
        $SearchKey = $RequestBody['SearchKey'];

        // echo $PageNo . '/';

        $Query = '';

        if ($ColdstoreID != '') {
            $Query .= " AND CPID.PlantID='$ColdstoreID'";
        }

        if ($Blocks != "") {
            $Query .= " and CSBD.BlockID='$Blocks'";
        }

        if ($TenantID != '') {
            $Query .= " AND CPID.StockHolderID='$TenantID'";
        }

        if ($PONumber != '') {
            $Query .= " AND CPID.PoID='$PONumber'";
        }

        if ($Species != '') {
            $Query .= " AND PIVL.Species='$Species'";
        }

        if ($TypeProduct != '') {
            $Query .= " AND PIVL.TypeProduct='$TypeProduct'";
        }

        if ($FreezingType != '') {
            $Query .= " AND CPID.FreezingType='$FreezingType'";
        }

        if ($Brand != '') {
            $Query .= " AND PIVL.BrandName='$Brand'";
        }

        if ($VarietyID != "") {
            $Query .= " AND PIVL.ShortName='$VarietyID'";
        }

        if ($Grade != '') {
            $Query = "AND CPID.Grade='$Grade'";
        }

        if ($IsReturnable != '') {
            $Query = "AND CPID.ProductionTypeID='$IsReturnable'";
        }
        if ($TransType != '') {
            $Query = "AND CPID.ProductionTypeID='$TransType'";
        }
        if ($PackingType != '') {
            $Query = "AND CPID.PackingTypeID='$PackingType'";
        }

        $DateFilter = " BETWEEN '$FromDate' AND '$ToDate'";

        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        $TotalCount = 0;
        $PageCount = 0;

        if ($IsReturnable == 1) {

            if ($SearchKey != '') {
                $SearchQuery = " and CONCAT_WS('', ProductionTypeID, ProductionDate, TransactionDate, PlantID, CPID.VarietyID,
                CPID.Grade, PackingTypeID, CPID.PackingStyleID,RPS.StationName,PIVL.ProductDescription,PIVL.DeviSUPC,
                APS.PackingStyle,AvalMcs, LooseBags) LIKE '%$SearchKey%'";
            }

            if ($Group != '') {
                $Query = "AND CPID.ProductionTypeID='$Group'";
            }

            $sqlCount = "SELECT COUNT(*) AS Total FROM CsStockData as CPID
            LEFT JOIN ScrmPIVarietyList as PIVL on PIVL.VarietyID=CPID.VarietyID
            LEFT JOIN AdminPackingStyles as APS on APS.PCKID=CPID.PackingStyleID
            LEFT JOIN RmProcessingStations AS RPS ON CPID.PlantID = RPS.ProcessingStationID
            LEFT JOIN CsStoresRackData AS CSRD ON CSRD.RowID =CPID.StoreRackID
            LEFT JOIN CsStoresBlockData AS CSBD ON CSBD.BlockID=CSRD.BlockID
            WHERE ProductionDate $DateFilter $Query GROUP BY CPID.VarietyID, CPID.Grade, PackingStyleID";

            $sqlInwardFields =  "ProductionTypeID AS GroupType, ProductionDate, TransactionDate, PlantID, CPID.VarietyID, CPID.Grade, 
            PackingTypeID, CPID.PackingStyleID,RPS.StationName,PIVL.ProductDescription,PIVL.DeviSUPC,APS.PackingStyle,
            SUM(AvalMcs) Mcs, SUM(AvalMcs*TotalSlabs) McsSlabs, SUM(LooseBags) LooseBags, 
            SUM(AvalMcs*TotalSlabs*SlabWeight*MFactor) as KGs,SUM(LooseBags*SlabWeight*MFactor) as LKGs";
            $sqlSelect = " SELECT $sqlInwardFields FROM CsStockData as CPID
            LEFT JOIN ScrmPIVarietyList as PIVL on PIVL.VarietyID=CPID.VarietyID
            LEFT JOIN AdminPackingStyles as APS on APS.PCKID=CPID.PackingStyleID
            LEFT JOIN RmProcessingStations AS RPS ON CPID.PlantID = RPS.ProcessingStationID
            LEFT JOIN CsStoresRackData AS CSRD ON CSRD.RowID =CPID.StoreRackID
            LEFT JOIN CsStoresBlockData AS CSBD ON CSBD.BlockID=CSRD.BlockID
            WHERE ProductionDate $DateFilter $Query $SearchQuery 
            GROUP BY CPID.VarietyID, CPID.Grade, PackingStyleID order by ProductionDate asc " . $Limit;
        } else if ($TransType == 2) {

            if ($SearchKey != '') {
                $SearchQuery = " and CONCAT_WS('', OutType,OutDate, ProductionDate, TransactionDate, PlantID, CPID.VarietyID,
                CPID.Grade, PackingTypeID, CPID.PackingStyleID,RPS.StationName,PIVL.ProductDescription,PIVL.DeviSUPC,
                APS.PackingStyle,AvalMcs, LooseBags) LIKE '%$SearchKey%'";
            }

            if ($Group != '') {
                $Query = "AND CSOD.OutType='$Group'";
            }

            $sqlCount = "SELECT COUNT(*) AS Total FROM CsStockOutDetails AS CSOD
            LEFT JOIN CsStockData AS CPID ON CSOD.StockID = CPID.StockID
            LEFT JOIN ScrmPIVarietyList as PIVL on PIVL.VarietyID=CPID.VarietyID
            LEFT JOIN AdminPackingStyles as APS on APS.PCKID=CPID.PackingStyleID
            LEFT JOIN RmProcessingStations AS RPS ON CPID.PlantID = RPS.ProcessingStationID
            LEFT JOIN CsStoresRackData AS CSRD ON CSRD.RowID =CPID.StoreRackID
            LEFT JOIN CsStoresBlockData AS CSBD ON CSBD.BlockID=CSRD.BlockID
            WHERE ProductionDate $DateFilter $Query GROUP BY CPID.VarietyID, CPID.Grade, PackingStyleID";

            $sqlOutwardFields = "OutType AS GroupType,OutDate, ProductionDate, TransactionDate, PlantID, CPID.VarietyID, CPID.Grade, 
            CPID.PackingTypeID, CPID.PackingStyleID,RPS.StationName,PIVL.ProductDescription,PIVL.DeviSUPC,APS.PackingStyle,
            SUM(OutMcs) Mcs, SUM(OutMcs*TotalSlabs) McsSlabs, SUM(OutLooseBags) LooseBags, 
            SUM(OutMcs*TotalSlabs*SlabWeight*MFactor) as KGs,SUM(OutLooseBags*SlabWeight*MFactor) as LKGs";

            $sqlSelect = "SELECT $sqlOutwardFields FROM CsStockOutDetails AS CSOD
            LEFT JOIN CsStockData AS CPID ON CSOD.StockID = CPID.StockID
            LEFT JOIN ScrmPIVarietyList as PIVL on PIVL.VarietyID=CPID.VarietyID
            LEFT JOIN AdminPackingStyles as APS on APS.PCKID=CPID.PackingStyleID
            LEFT JOIN RmProcessingStations AS RPS ON CPID.PlantID = RPS.ProcessingStationID
            LEFT JOIN CsStoresRackData AS CSRD ON CSRD.RowID =CPID.StoreRackID
            LEFT JOIN CsStoresBlockData AS CSBD ON CSBD.BlockID=CSRD.BlockID
            WHERE ProductionDate $DateFilter $Query $SearchQuery
            GROUP BY CPID.VarietyID, CPID.Grade, PackingStyleID order by ProductionDate asc " . $Limit;
        }
        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();

        // getting Record Count
        $stmtCount = $this->conn->prepare($sqlCount);
        $stmtCount->execute();

        $TotalCount = $stmtCount->rowCount();
        $PageCount = ceil($TotalCount / $RecordsPerPage);


        $i = 0;
        $ArrayData = [];
        $TotalMcs = 0;
        $TotalSlabs = 0;
        $TotalKGs = 0;
        $TotalLooseBags = 0;
        while ($rowData = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
            $ArrayList['SNO'] = ++$i;
            $ArrayList['Group'] = $rowData['GroupType'];
            $ArrayList['ProductionDate'] = $rowData['ProductionDate'] ? date('d-m-Y', strtotime($rowData['ProductionDate'])) : '-';
            $ArrayList['TransactionDate'] = $rowData['TransactionDate'] ? date('d-m-Y', strtotime($rowData['TransactionDate'])) : '-';
            $ArrayList['OutDate'] = $rowData['OutDate'] ? date('d-m-Y', strtotime($rowData['OutDate'])) : '-';
            $ArrayList['StationName'] = $rowData['StationName'];
            $ArrayList['VarietyID'] = $rowData['VarietyID'];
            $ArrayList['ProductDescription'] = $rowData['ProductDescription'];
            $ArrayList['SUPC'] = $rowData['DeviSUPC'];
            $ArrayList['Grade'] = $rowData['Grade'];
            $ArrayList['PackingTypeID'] = $rowData['PackingTypeID'];
            $ArrayList['PackingStyleID'] = $rowData['PackingStyleID'];
            $ArrayList['PackingStyle'] = $rowData['PackingStyle'];
            $ArrayList['Mcs'] = $rowData['Mcs'];
            $ArrayList['LooseBags'] = $rowData['LooseBags'];
            $Slabs = $rowData['McsSlabs'] + $rowData['LooseBags'];
            $ArrayList['Slabs'] = $Slabs;
            $KGs = $rowData['KGs'] + $rowData['LKGs'];
            $ArrayList['TotalKGS'] = $KGs;

            $TotalMcs += $rowData['Mcs'];
            $TotalLooseBags += $rowData['LooseBags'];
            $TotalSlabs += $Slabs;
            $TotalKGs += $KGs;

            $ArrayData[] = $ArrayList;
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TotalCount'] = $TotalCount;
        $data['PageCount'] = $PageCount;
        $data['Data'] = $ArrayData;
        $data['TotalMcs'] = $TotalMcs;
        $data['TotalSlabs'] = $TotalSlabs;
        $data['TotalKGs'] = $TotalKGs;
        $data['TotalLooseBags'] = $TotalLooseBags;

        return $data;
    }
}
