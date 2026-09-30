<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once '../Global/Util.php';
//include_once 'Controller/UtilController.php';

class OutWardReportController
{
    private $conn;
    //private $db_table = "ScrmApplicants";
    private $utilController;
    private $Error = null;
    private $Msg = '';


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        // $this->utilController = new UtilController($this->conn);
    }


    function getOutWardReport($RequestBody)
    {


        //   echo print_r($RequestBody );
        //     exit;
        

        $RecordsPerPage = 100;
        $ColdstoreID = $RequestBody['ColdstoreID'];
        $TenantID = $RequestBody['TenantID'];
        $PoID = $RequestBody['PoID'];
        $VarietyID = $RequestBody['VarietyID'];
        $Grade = $RequestBody['Grade'];
        $Group = $RequestBody['Group'];
        $PackingStyle = $RequestBody['PackingStyle'];
        $PackingType = $RequestBody['PackingType'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'] . ' 23:59:59';
        $Blocks = $RequestBody['Blocks'];
        $TypeProduct = $RequestBody['TypeProduct'];
        $Soaking = $RequestBody['Soaking'];
        $PDCategory = $RequestBody['PDCategory'];
        $FreezingType = $RequestBody['FreezingType'];
        $AbStatus = $RequestBody['AbStatus'];

        $DateFilters = " CSD.OutDate BETWEEN '$FromDate' AND '$ToDate'";
        $ShowBrandInCSReport = $RequestBody['ShowBrandInCSReport'];
        $ProductBatchCode = $RequestBody['ProductBatchCode'];
        $Query = '';

        if ($ColdstoreID != '') {
            // $Query .= " and CS.PlantID ='$ColdstoreID'";
            $Query .= " and CS.UnitID ='$ColdstoreID'";
        }
        if ($TenantID != '') {
            $Query .= " and CS.StockHolderID ='$TenantID'";
        }
        if ($PoID != '') {
            $Query .= " and CS.PoID ='$PoID'";
        }
        if ($VarietyID != '') {
            $Query .= " and AGO.ShortName ='$VarietyID'";
        }
        if ($Grade != '') {
            $Query .= " and CS.Grade ='$Grade'";
        }
        if ($Group != '') {
            $Query .= " and CSD.OutType ='$Group'";
        }
        if ($PackingStyle != '') {
            $Query .= " and CS.PackingStyleID ='$PackingStyle'";
        }
        if ($PackingType != '') {
            $Query .= " and CS.PackingTypeID ='$PackingType'";
        }
        if ($Blocks != "") {
            $Query .= " and CSBD.BlockID='$Blocks'";
        }
        if ($TypeProduct != '') {
            $Query .= "AND AGO.TypeProduct='$TypeProduct'";
        }
        if ($Soaking != '') {
            $Query .= " AND CS.SockingType='$Soaking'";
        }
        if ($FreezingType != '') {
            $Query .= "AND CS.FreezingType='$FreezingType'";
        }
        if ($PDCategory != '') {
            $Query .= " AND CS.ProductCategoryID='$PDCategory'";
        }
        if ($AbStatus != '') {
            $Query .= " AND CS.AbStatus='$AbStatus'";
        }

        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query .= " and CONCAT_WS('', StationName,ProductionDate,PoNo,ShortName,CS.FreezingType,ProductionTypeID,CS.Grade,
            CSP.PackingStyle,ProductCategoryID,OutMcs,OutLooseBags,CSD.Remarks,CS.PackingTypeID, CS.BatchCode,CDPD.MixedBatch) LIKE '%$SearchKey%'";
        }


        $sqlCount = "SELECT COUNT(*) AS Total FROM `CsStockOutDetails` AS CSD 
        LEFT JOIN CsStockData AS CS ON CSD.StockID=CS.StockID 
        LEFT JOIN AdminPackingStyles AS CSP ON CSP.PCKID = CS.PackingStyleID 
        LEFT JOIN ScrmPIVarietyList AS AGO ON CS.VarietyID=AGO.VarietyID 
        LEFT JOIN CsTenant AS AGOU ON CS.UnitID=AGOU.TenantID 
        left join RmProcessingStations as RmPS on CS.PlantID=RmPS.ProcessingStationID 
        LEFT JOIN ScrmPoData AS CSPOS ON CSPOS.PoID = CS.PoID 
        LEFT JOIN CsStoresRackData AS CRD ON CRD.RowID =CS.StoreRackID
        LEFT JOIN CsStoresBlockData AS CSBD ON CSBD.BlockID=CRD.BlockID
        LEFT JOIN CsStores AS CSS ON  CS.UnitID=CSS.StoreID";
        if($ShowBrandInCSReport == 1){
            $sqlCount .= " LEFT JOIN `ScrmBrandsMaster` SBM ON SBM.`BrandID`= AGO.`BrandName` ";
         }
        $sqlCount .= " WHERE $DateFilters  $Query GROUP BY StockOutID";
        $stmtCount = $this->conn->prepare($sqlCount);
        $stmtCount->execute();
        $TotalCount = $stmtCount->rowCount();
        $PageCount = ceil($TotalCount / $RecordsPerPage);


        $sqlFields = "CSD.StockOutID,CS.ProductionDate,CS.TransactionDate, CSD.PostedDate,RmPS.StationName as Unit,COALESCE(PoNo,'NA')AS PoNo, ShortName, 
        RackName,StoreRackID, CS.FreezingType as FreezingType, CS.ProductionTypeID, CS.Grade, CSP.PackingStyle,CS.ProductCategoryID, OutMcs, OutLooseBags, 
        CSD.Remarks,CSD.OutType, CS.PlantID, CS.PoID,CS.PackingStyleID,CS.PackingTypeID,CS.VarietyID,CS.UnitID,CS.StockID,BlockName,StoreName,
        (SELECT sum(OutMcs*TotalSlabs*SlabWeight*MFactor) from CsStockData as MCPID LEFT JOIN AdminPackingStyles AS CSP ON CSP.PCKID = MCPID.PackingStyleID where MCPID.StockID=CSD.StockID) as KGS,
        (SELECT sum(OutLooseBags*SlabWeight*MFactor) from CsStockData as MCPID LEFT JOIN AdminPackingStyles AS CSP ON CSP.PCKID = MCPID.PackingStyleID where MCPID.StockID=CSD.StockID) as LKGS, 
        (SELECT sum(ActualOutKgs) from CsStockData as MCPID where MCPID.StockID=CSD.StockID) as ActualOutKgs, 
        (CSD.OutMcs*TotalSlabs) + CSD.OutLooseBags  AS McsSlabs,
        CSD.OutDate,AGO.Species,AGO.TypeProduct,CS.SockingType,CS.AbStatus,CS.ProductionID,DATE_FORMAT(RRD.ArrivalDate, '%d-%m-%Y') AS ArrivalDate,  RRD.TrackCode,
        CASE 
        WHEN RLD.LotNumber IS NOT NULL AND RLD.LotNumber != '' 
            THEN RLD.LotNumber
        WHEN CS.BatchCode IS NOT NULL AND CS.BatchCode != '' 
            THEN CONCAT('P-', CS.BatchCode)
        WHEN CDPD.MixedBatch IS NOT NULL AND CDPD.MixedBatch != '' 
            THEN CONCAT('M-', CDPD.MixedBatch)
        ELSE ''
        END AS LotNumber
        ,CDPD.ProdBatchCode";

        if($ShowBrandInCSReport == 1){
            $sqlFields .=',SBM.`BrandName`'; 
        }
        // BlockName,StoreName
        $sqlQuery = "SELECT $sqlFields FROM `CsStockOutDetails` AS CSD 
        LEFT JOIN CsStockData AS CS ON CSD.StockID=CS.StockID
        left join CsDailyProductionData AS CDPD ON CS.ProductionID=CDPD.ProductionID 
        left join RmReceivingDetails AS RRD ON CDPD.TrackID=RRD.ReceivingID
           LEFT JOIN (
                SELECT TrackID, TubNo, LineNumber, MAX(ProductionID) AS MaxDataID
                FROM ProdFreezingData
                GROUP BY TrackID, TubNo, LineNumber
            ) PFDx
            ON CDPD.TrackID = PFDx.TrackID 
            AND CDPD.TubNo = PFDx.TubNo 
            AND CDPD.LineNumber = PFDx.LineNumber
            LEFT JOIN ProdFreezingData PFD
            ON PFD.ProductionID = PFDx.MaxDataID
            left join PpcHLGradingBreakup as PGB on PFD.GradingBreakupID=PGB.DataID 
            left join RmLotDetatils as RLD on PGB.LotID=RLD.RmLotID 
        LEFT JOIN AdminPackingStyles AS CSP ON CSP.PCKID = CS.PackingStyleID 
        LEFT JOIN ScrmPIVarietyList AS AGO ON CS.VarietyID=AGO.VarietyID 
        LEFT JOIN CsTenant AS AGOU ON CS.UnitID=AGOU.TenantID 
        left join RmProcessingStations as RmPS on CS.PlantID=RmPS.ProcessingStationID 
        LEFT JOIN ScrmPoData AS CSPOS ON CSPOS.PoID = CS.PoID 
        LEFT JOIN CsStoresRackData AS CRD ON CRD.RowID =CS.StoreRackID
        LEFT JOIN CsStoresBlockData AS CSBD ON CSBD.BlockID=CRD.BlockID
        LEFT JOIN CsStores AS CSS ON  CS.UnitID=CSS.StoreID ";
        if($ShowBrandInCSReport == 1){
            $sqlQuery .= " LEFT JOIN `ScrmBrandsMaster` SBM ON SBM.`BrandID`= AGO.`BrandName` ";
        }
        $sqlQuery .= "WHERE $DateFilters  $Query GROUP BY StockOutID " . $Limit;

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        $i = 0;
        $Totals = [];
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

             $ProductionID = $dataRow['ProductionID'];

            // $SqlTrackCode=" SELECT distinct  DATE_FORMAT(RRD.ArrivalDate, '%d-%m-%Y') AS ArrivalDate,  RRD.TrackCode,RLD.LotNumber FROM RmReceivingDetails RRD
            // left join  `CsDailyProductionData` CPD  on RRD.ReceivingID=CPD.TrackID
            // LEFT JOIN (
            //     SELECT TrackID, TubNo, LineNumber, MAX(ProductionID) AS MaxDataID
            //     FROM ProdFreezingData
            //     GROUP BY TrackID, TubNo, LineNumber
            // ) PFDx
            // ON CDPD.TrackID = PFDx.TrackID 
            // AND CDPD.TubNo = PFDx.TubNo 
            // AND CDPD.LineNumber = PFDx.LineNumber
            // LEFT JOIN ProdFreezingData PFD
            // ON PFD.ProductionID = PFDx.MaxDataID
            // left join PpcHLGradingBreakup as PGB on PFD.GradingBreakupID=PGB.DataID 
            // left join RmLotDetatils as RLD on PGB.LotID=RLD.RmLotID
            // WHERE ProductionID='$ProductionID'"; 
            //  $StmtTrackCode= $this->conn->prepare($SqlTrackCode);
            //  $StmtTrackCode->execute();
            //  $RowTrackCode = $StmtTrackCode->fetch(PDO::FETCH_ASSOC);

            $RackName = $dataRow['RackName'];
            $BarCodeStatus = $dataRow['BarCodeStatus'];
            $StoreRackID = $dataRow['StoreRackID'];
            $StockOutID = $dataRow['StockOutID'];

            $sqlOutStage = "SELECT DISTINCT RRD.OutStage FROM `RmReceivingBreakup` as RRB 
            left join RmReceivingDetails as RRD on RRB.ReceivingID=RRD.ReceivingID WHERE CsStockOutID='$StockOutID'";
            $stmtOutStage = $this->conn->prepare($sqlOutStage);
            $stmtOutStage->execute();
            $dataRowOutStage = $stmtOutStage->fetch(PDO::FETCH_ASSOC);
            if($dataRowOutStage['OutStage']==1){
                $OutStage="Value Addition";
            } 
            else if($dataRowOutStage['OutStage']==2){
                $OutStage="Soaking";
            } else if($dataRowOutStage['OutStage']==3){
                $OutStage="Freezing";
            }
           

            $BlockName = '';



            if ($StoreRackID != 0) {
                $BlockName .= $dataRow['BlockName'] . ' - ' . $RackName;
                $StoreName = $dataRow['StoreName'];

            } else {
                $BlockName = '--';
                $StoreName = '--';
            }

            $KGS = $dataRow['KGS'];
            $LKGS = $dataRow['LKGS'];
            $Mcs = $dataRow['OutMcs'];
            $LooseBags = $dataRow['OutLooseBags'];
            $ActualOutKgs = $dataRow['ActualOutKgs'];


            // $KGs = $KGS + $LKGS;
            $KGs = $ActualOutKgs; //uma


            $ArrayStockData['SNo'] = ++$i;
            $ArrayStockData['StockID'] = $dataRow['StockID'];
            $ArrayStockData['ProductionDate'] = date('d-m-Y', strtotime($dataRow['ProductionDate']));
            $ArrayStockData['TransactionDate'] = date('d-m-Y', strtotime($dataRow['TransactionDate']));
            $ArrayStockData['OutDate'] = date('d-m-Y', strtotime($dataRow['OutDate']));
            $ArrayStockData['Unit'] = $dataRow['Unit'];
            $ArrayStockData['UnitID'] = $dataRow['UnitID'];
            $ArrayStockData['PlantID'] = $dataRow['PlantID'];
            $ArrayStockData['PoID'] = $dataRow['PoID'];
            $ArrayStockData['PoNo'] = $dataRow['PoNo'];
            $ArrayStockData['BarCodeValue'] = $dataRow['BarCodeValue'];
            $ArrayStockData['VarietyID'] = $dataRow['VarietyID'];
            $ArrayStockData['ShortName'] = $dataRow['Species'] . "-" . $dataRow['ShortName'];
            $ArrayStockData['FreezingType'] = $dataRow['FreezingType'];
            $ArrayStockData['ProductionTypeID'] = $dataRow['ProductionTypeID'];
            
            $ArrayStockData['ArrivalDate'] = $dataRow['ArrivalDate'];
            $ArrayStockData['TrackCode'] = $dataRow['TrackCode'];
            $ArrayStockData['LotNumber'] = $dataRow['LotNumber'];

            $ArrayStockData['OutType'] = $dataRow['OutType'];
            $ArrayStockData['Grade'] = $dataRow['Grade'];
            $ArrayStockData['PackingStyleID'] = $dataRow['PackingStyleID'];
            $ArrayStockData['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayStockData['ProductCategoryID'] = $dataRow['ProductCategoryID'];
            $ArrayStockData['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayStockData['PackingTypeID'] = $dataRow['PackingTypeID'];
            $ArrayStockData['Store'] = $StoreName;
            $ArrayStockData['Location'] = $BlockName;
            $ArrayStockData['OutMcs'] = $Mcs;
            $ArrayStockData['OutLooseBags'] = $LooseBags;
            $ArrayStockData['Remarks'] = $dataRow['Remarks'];
            $ArrayStockData['OutStage'] = $OutStage;
            $ArrayStockData['KGs'] = $KGs;
            $ArrayStockData['McsSlabs'] = $dataRow['McsSlabs'];
            $ArrayStockData['Soaking'] = $dataRow['SockingType'];
            $ArrayStockData['TypeProduct'] = $dataRow['TypeProduct'];
            $ArrayStockData['AbStatus'] = $dataRow['AbStatus'];
            $ArrayStockData['ProdBatchCode'] = $dataRow['ProdBatchCode'];
            if($ShowBrandInCSReport == 1){
                $ArrayStockData['BrandName'] = $dataRow['BrandName'];
            }
            $AryData[] = $ArrayStockData;

            $Totals['TotalMcs'] += $Mcs;
            $Totals['TotalLooseBags'] += $LooseBags;
            $Totals['TotalKGs'] += $KGs;
        }
        if (count($ArrayStockData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['TotalCount'] = $TotalCount;
        $Data['RemainingCount'] = "";
        $Data['PageCount'] = $PageCount;
        $Data['RecordsAval'] = $RecordsAval;
        $Data['Data'] = $AryData;
        $Data['Totals'] = $Totals;

        return $Data;
    }

}