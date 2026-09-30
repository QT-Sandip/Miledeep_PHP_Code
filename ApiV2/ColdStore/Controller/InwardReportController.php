<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class InwardReportController
{
    private $conn;
    private $utilController;
    private $BalanceKgs;
    private $Error = null;
    private $Msg = '';


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
    }

    public function getInwardReportDetails($RequestBody)
    {

        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $DateFilter = "between '$FromDate' and '$ToDate 23:59:59'";
        $VarietyID = $RequestBody['Variety'];

        $RecordsPerPage = 100;
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;
        $ShowBrandInCSReport = $RequestBody['ShowBrandInCSReport'];
         $ProductBatchCode = $RequestBody['ProductBatchCode']; 

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query = " and CONCAT_WS('', CPID.VarietyID, ShortName, PackingStyle, AvalMcs, LooseBags, CPID.BatchCode, CDPD.MixedBatch) LIKE '%$SearchKey%'";
        }

        $Query = $this->getFilters($RequestBody);


        if ($VarietyID != '') {
            $Variety = "AND AGO.ShortName='$VarietyID'";
        }

        $sqlTotals = "SELECT count(*)  as Totals FROM `CsStockData` as CPID 
        left join AdminPackingStyles as CSP on CSP.PCKID = CPID.PackingStyleID
        left join ScrmPoData as CSPOS on CSPOS.PoID = CPID.PoID 
        left join ScrmPIVarietyList as AGO on CPID.VarietyID=AGO.VarietyID 
        left join RmProcessingStations as AGOU on CPID.PlantID=AGOU.ProcessingStationID 
        left join AdminGeneralOptions as HSD on HSD.OptID=CPID.ShiftID 
        left join CsStores as CS  on CPID.UnitID=CS.StoreID
        left join CsStoresRackData as CRD  on CRD.RowID =CPID.StoreRackID
        left join CsStoresBlockData as CSBD  on CSBD.BlockID=CRD.BlockID ";
        if($ShowBrandInCSReport == 1){
            $sqlTotals .= " LEFT JOIN `ScrmBrandsMaster` SBM ON SBM.`BrandID`= AGO.`BrandName` ";
         }
        $sqlTotals .= " where CPID.ProductionDate $DateFilter $Query  $Variety ";

      
        $stmtTotals = $this->conn->prepare($sqlTotals);
        $stmtTotals->execute();

        // $errorInfo = $stmtTotals->errorInfo();
        // var_dump($errorInfo);
        $RowTotals = $stmtTotals->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $RowTotals['Totals'];

        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $TotalKGs = '';
        $TotalMcs = '';
        $TotalLooseBags = '';
        $TotalSlabs = '';

        $SqlFields = "StockID,RackName,CSBD.BlockID,CPID.LooseBags,CPID.ActualMcs,CPID.ActualLooseBags,PoNo,CPID.ProductionDate
        ,ShortName,CPID.FreezingType,CPID.ProductionTypeID,CSP.PackingStyle,CPID.ProductCategoryID,CPID.AvalKgs,sum(CPID.ActualKgs) as ActualKgs,
        sum(CPID.ActualMcs*TotalSlabs*SlabWeight*MFactor) as Kgs,
        sum(CPID.ActualLooseBags*SlabWeight*MFactor) as LKgs ,
        CPID.FreezingType,HSD.Value as ShiftName,
        (CPID.ActualMcs*TotalSlabs) + CPID.ActualLooseBags  AS McsSlabs,
        CPID.Grade,CPID.Remarks,CSP.PackingStyle,AGOU.StationName as Unit,BlockName,StoreName,CPID.TransactionDate,CPID.PostedDate,CPID.UnitID,CPID.StoreRackID,AGO.Species,
        AGO.TypeProduct,CPID.SockingType,CPID.AbStatus,RRD.TrackCode,RRD.ArrivalDate,
        CASE 
        WHEN RLD.LotNumber IS NOT NULL AND RLD.LotNumber != '' 
            THEN RLD.LotNumber
        WHEN CPID.BatchCode IS NOT NULL AND CPID.BatchCode != '' 
            THEN CONCAT('P-', CPID.BatchCode)
        WHEN CDPD.MixedBatch IS NOT NULL AND CDPD.MixedBatch != '' 
            THEN CONCAT('M-', CDPD.MixedBatch)
        ELSE ''
        END AS LotNumber
        ,CDPD.ProdBatchCode";

        // if ($VarietyID != '') {
        //     $Variety = "AND AGO.ShortName='$VarietyID'";
        // }
        if($ShowBrandInCSReport == 1){
            $SqlFields .=',SBM.`BrandName`'; 
        }
         $SqlStock = "Select $SqlFields  FROM `CsStockData` as CPID 
            left join CsDailyProductionData AS CDPD ON CPID.ProductionID=CDPD.ProductionID
            left join RmReceivingDetails AS RRD ON CDPD.TrackID=RRD.ReceivingID
           LEFT JOIN (
                SELECT TrackID, TubNo, LineNumber, MAX(ProductionID) AS MaxDataID
                FROM ProdFreezingData
                GROUP BY TrackID, TubNo, LineNumber
            ) PFDx
            ON CDPD.TrackID = PFDx.TrackID 
            AND CDPD.TubNo = PFDx.TubNo 
            AND CDPD.LineNumber = PFDx.LineNumber
            LEFT JOIN ProdFreezingData PFD ON PFD.ProductionID = PFDx.MaxDataID
            left join PpcHLGradingBreakup as PGB on PFD.GradingBreakupID=PGB.DataID 
            left join RmLotDetatils as RLD on PGB.LotID=RLD.RmLotID 
            left join AdminPackingStyles as CSP on CSP.PCKID = CPID.PackingStyleID
            left join ScrmPoData as CSPOS on CSPOS.PoID = CPID.PoID 
            left join ScrmPIVarietyList as AGO on CPID.VarietyID=AGO.VarietyID 
            left join RmProcessingStations as AGOU on CPID.PlantID=AGOU.ProcessingStationID 
            left join AdminGeneralOptions as HSD on HSD.OptID=CPID.ShiftID 
            left join CsStores as CS  on CPID.UnitID=CS.StoreID
            left join CsStoresRackData as CRD  on CRD.RowID =CPID.StoreRackID
            left join CsStoresBlockData as CSBD  on CSBD.BlockID=CRD.BlockID ";
            if($ShowBrandInCSReport == 1){
                $SqlStock .= " LEFT JOIN `ScrmBrandsMaster` SBM ON SBM.`BrandID`= AGO.`BrandName` ";
            }
            $SqlStock .= " where CPID.ProductionDate $DateFilter $Query  $Variety group by CPID.StockID order by CPID.ProductionDate asc $Limit"; 

            // left join RmArrivalDetatils AS RAD ON RRD.RmArrivalID=RAD.RmArrivalID
        $StmtStock = $this->conn->prepare($SqlStock);
        $StmtStock->execute();

        while ($RowStock = $StmtStock->fetch(PDO::FETCH_ASSOC)) {
            $AryTotalStock[] = $RowStock;

            $Mcs = $RowStock['ActualMcs'];
            $LooseBags = $RowStock['ActualLooseBags'];
            // $KGs = $RowStock['Kgs'] + $RowStock['LKgs'];
            $KGs = $RowStock['ActualKgs'] ;
            $Slabs = $RowStock['McsSlabs'];

            $TotalKGs += $KGs;
            $TotalMcs += $Mcs;
            $TotalLooseBags += $LooseBags;
            $TotalSlabs += $Slabs;
        }

        $SNo = 0;
        foreach ($AryTotalStock as $RowStock) {
            $RackName = $RowStock['RackName'];

            $OnFloor = $RowStock['OnFloor'];
            $StoreRackID     = $RowStock['StoreRackID'];

            $BlockName = '';

            if ($StoreRackID == '') {
                $BlockName = '--';
                $StoreName = '--';
            } else {
                $BlockName = $RowStock['BlockName'] . ' - ' . $RackName;
                $StoreName = $RowStock['StoreName'];
            }

            // $AvalKgs = 0;
            $ActualKgs = 0;

            // $AvalKgs = $RowStock['Kgs'] + $RowStock['LKgs']; 
            $ActualKgs = $RowStock['ActualKgs'];

            $ArrayStockData['SNo'] = ++$SNo;
            $ArrayStockData['StockID'] = $RowStock['StockID'];
            $ArrayStockData['ProductionDate'] = $RowStock['ProductionDate'];
            $ArrayStockData['PostedDate'] = $RowStock['PostedDate'];
            $ArrayStockData['Date'] = $this->utilController->getFormatedDatelines($RowStock['TransactionDate']);
            $ArrayStockData['Station'] = $RowStock['Unit'];
            $ArrayStockData['PoNo'] = $RowStock['PoNo'];
            // $ArrayStockData['BarCode'] = $RowStock['BarCodeValue'];
            $ArrayStockData['LotNumber'] = $RowStock['LotNumber'];
            $ArrayStockData['Shift'] = $RowStock['ShiftName'];
            $ArrayStockData['Variety'] = $RowStock['Species']. "-" . $RowStock['ShortName'];
            $ArrayStockData['Freezing'] = $RowStock['FreezingType'];
            $ArrayStockData['Group'] = str_ireplace('Repacking Out', 'Repacking In', $RowStock['ProductionTypeID']);
            $ArrayStockData['Grade'] = $RowStock['Grade'];
            $ArrayStockData['PackingStyle'] = $RowStock['PackingStyle'];
            $ArrayStockData['Category'] = $RowStock['ProductCategoryID'];
            $ArrayStockData['ArrivalDate'] = $RowStock['ArrivalDate'];
            $ArrayStockData['TrackCode'] = $RowStock['TrackCode'];
            $ArrayStockData['Store'] = $StoreName;
            $ArrayStockData['Location'] = $BlockName;
            $ArrayStockData['OnFloor'] = $OnFloor;
            $ArrayStockData['UnitID'] = $RowStock['UnitID'];
            $ArrayStockData['StoreRackID'] = $RowStock['StoreRackID'];
            $ArrayStockData['Mcs'] = $RowStock['ActualMcs'];
            $ArrayStockData['LooseBags'] = $RowStock['ActualLooseBags'];
            $ArrayStockData['Remarks'] = $RowStock['Remarks'];
            $ArrayStockData['McsSlabs'] = $RowStock['McsSlabs'];
            // $ArrayStockData['Kgs'] = round($RowStock['AvalKgs'], 2);
            $ArrayStockData['Kgs'] = round($ActualKgs, 2);
            $ArrayStockData['Soaking'] = $RowStock['SockingType'];
            $ArrayStockData['TypeProduct'] = $RowStock['TypeProduct'];
            $ArrayStockData['AbStatus'] = $RowStock['AbStatus'];
              $ArrayStockData['ProdBatchCode'] = $RowStock['ProdBatchCode'];
            if($ShowBrandInCSReport == 1){
                $ArrayStockData['BrandName'] = $RowStock['BrandName'];
            }
            $ArrayInwardData[] = $ArrayStockData;
        }
        if (count($ArrayInwardData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        $ArrayTotals['TotalKGs'] = round($TotalKGs, 2);
        $ArrayTotals['TotalMcs'] = $TotalMcs;
        $ArrayTotals['TotalLooseBags'] = $TotalLooseBags;
         $ArrayTotals['TotalSlabs'] = $TotalSlabs;

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['PageCount'] = $PageCount;
        $data['TotalCount'] = $TotalCount;
         $data['SqlStock'] = $SqlStock;
        $data['RecordsAval'] = $RecordsAval;
        $data['Totals'] = $ArrayTotals;
        $data['Data'] = $ArrayInwardData;

        return $data;
    }


    function getFilters($RequestBody)
    {
        $ColdStoreID = $RequestBody['ColdStore'];
        $TenantID = $RequestBody['Stockbelongsto'];
        $PoID = $RequestBody['PoNo'];
        $VarietyID = $RequestBody['Variety'];
        $Grade = $RequestBody['Grade'];
        $Group = $RequestBody['Group'];
        $PackingStyleID = $RequestBody['PackingStyle'];
        $PackingType = $RequestBody['PackingType'];
        $Blocks = $RequestBody['Blocks'];
        $TypeProduct = $RequestBody['TypeProduct'];
        $Soaking = $RequestBody['Soaking'];
        $PDCategory = $RequestBody['PDCategory'];
        $FreezingType = $RequestBody['FreezingType'];
        $AbStatus = $RequestBody['AbStatus'];

        $Query = '';

        if ($ColdStoreID != '') {
            $Query .= "AND CPID.`UnitID`='$ColdStoreID'";
        }
        if ($PackingType != '') {
            $Query = "AND CPID.`PackingTypeID`='$PackingType'";
        }
        if ($Group != '') {
            $Query .= "AND CPID.`ProductionTypeID`='$Group'";
        }

        if ($PoID != '') {
            $Query .= "AND CPID.`PoID`='$PoID'";
        }
        if ($Blocks != "") {
            $Query .= " and CSBD.BlockID='$Blocks'";
        }
        if ($Grade != '') {
            $Query .= "AND CPID.`Grade`='$Grade'";
        }
        if ($PackingStyleID != '') {
            $Query .= "AND CPID.`PackingStyleID`='$PackingStyleID'";
        }
        if ($VarietyID != '') {
            $Query .= "AND AGO.ShortName='$VarietyID'";
        }
        if ($TenantID != '') {
            $Query .= " AND CPID.StockHolderID='$TenantID'";
        }
        if ($TypeProduct != '') {
            $Query .= "AND AGO.TypeProduct='$TypeProduct'";
        }
        if ($Soaking != '') {
            $Query .= " AND CPID.SockingType='$Soaking'";
        }
        if ($FreezingType != '') {
            $Query .= "AND CPID.FreezingType='$FreezingType'";
        }
        if ($PDCategory != '') {
            $Query .= " AND CPID.ProductCategoryID='$PDCategory'";
        }
        if ($AbStatus != '') {
            $Query .= " AND CPID.AbStatus='$AbStatus'";
        }

        return $Query;
    }
}
