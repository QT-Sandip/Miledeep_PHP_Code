<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';

class StoreOnFloorStockController
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
    function number_format_inr($number) {
    $number = (string) $number;
    $decimal = '';

    if (strpos($number, '.') !== false) {
        list($number, $decimal) = explode('.', $number);
        $decimal = '.' . substr($decimal, 0, 2);
    }

    if (strlen($number) <= 3) {
        return $number . $decimal;
    }

    $last3 = substr($number, -3);
    $rest  = substr($number, 0, -3);

    $rest = preg_replace("/\B(?=(\d{2})+(?!\d))/", ",", $rest);

    return $rest . ',' . $last3 . $decimal;
}


    function getAnalytics($RequestBody){
        $data = [];
        $ColdstoreID = $RequestBody['ColdstoreID'];
        $TenantID = $RequestBody['TenantID'];
        $Query = " StockStatus=1 AND (AvalMcs > 0 OR LooseBags > 0)";
        if (isset($ColdstoreID) && $ColdstoreID != "") {
            $Query = " StockStatus=1 and UnitID='$ColdstoreID' ";
        }
        if (isset($TenantID) && $TenantID != "") {
            $Query .= " and StockHolderID='$TenantID'";
        }
        if (isset($TenantID) && $TenantID != "") {

        $sqlGetTotalAnalytics = " SELECT
            count(StockID) as cnt,
            CSD.ProductionTypeID,
            CSD.TransactionDate,
            SUM(AvalMcs + (LooseBags/TotalSlabs)) AS AvalMcs,
            Sum(ActualLooseBags) as loosebags,
            SUM(AvalKgs) AS AvalKgs
            FROM CsStockData CSD
            LEFT JOIN AdminPackingStyles APS ON APS.PCKID = CSD.PackingStyleID
            LEFT JOIN CsStoresRackData CSRD
                ON CSRD.RowID = CSD.StoreRackID

            LEFT JOIN CsStoresBlockData CSBD
                ON CSBD.BlockID = CSRD.BlockID
            where UnitID=$ColdstoreID and (AvalMcs !=0 OR LooseBags !=0) ";
    }else{
            $sqlGetTotalAnalytics = " SELECT
            count(StockID) as cnt,
            CSD.ProductionTypeID,
            CSD.TransactionDate,
            SUM(AvalMcs + (LooseBags/TotalSlabs)) AS AvalMcs,
            Sum(ActualLooseBags) as loosebags,
            SUM(AvalKgs) AS AvalKgs
            FROM CsStockData CSD
            LEFT JOIN AdminPackingStyles APS ON APS.PCKID = CSD.PackingStyleID
            LEFT JOIN CsStoresRackData CSRD
                ON CSRD.RowID = CSD.StoreRackID

            LEFT JOIN CsStoresBlockData CSBD
                ON CSBD.BlockID = CSRD.BlockID
                where UnitID=$ColdstoreID and (AvalMcs !=0 OR LooseBags !=0) ";

        }
        //echo $sqlGetTotalAnalytics;
        $resGetTotalAnalytics = $this->conn->query($sqlGetTotalAnalytics);
        $rowGetAnalytics = array('cnt'=>0,'AvalKgs'=>0,'loosebags'=>0);
        if($resGetTotalAnalytics){
            $rowGetAnalytics = $resGetTotalAnalytics->fetch(PDO::FETCH_ASSOC);
        }
        $data['totalActiveStocks'] = $this->number_format_inr($rowGetAnalytics['cnt']);
        $data['totalMcs'] = $this->number_format_inr($rowGetAnalytics['AvalKgs']);
        $data['totalLooseBags'] = $this->number_format_inr($rowGetAnalytics['loosebags']);

        $sqlGetStoreOnFloor = "SELECT StockFloorStatus, count(StockID) as cnt FROM `CsStockData` where $Query  GROUP by StockFloorStatus;";
        $resGetStoreOnFloor = $this->conn->query($sqlGetStoreOnFloor);
        while($rowGetStoreOnFloor = $resGetStoreOnFloor->fetch(PDO::FETCH_ASSOC)){
            if( $rowGetStoreOnFloor['StockFloorStatus'] == 0 ){
                $data['totalFloorStocks'] = "On Floor: ".$this->number_format_inr($rowGetStoreOnFloor['cnt']);
            }else{
                $data['totalStoreStocks'] = "On Store: ".$this->number_format_inr($rowGetStoreOnFloor['cnt']);
            }

        }

        $sqlGetTotal24 = "SELECT COUNT(StockID) AS totalLst24 FROM CsStockData WHERE $Query  ";
        $resGetTotal24 = $this->conn->query($sqlGetTotal24);
        $rowGet24Analytics = $resGetTotal24->fetch(PDO::FETCH_ASSOC);

        $data['totalLst24'] = $rowGet24Analytics['totalLst24'];
        return $data;
    }
    function getStoreOnFloorDetails($RequestBody)
    {
        $RecordsPerPage = 100;
        $PoGradeId = $RequestBody['PoGradeId'];
        $VarietyID = $RequestBody['VarietyID'];
        $PackingStyleID = $RequestBody['PackingStyleID'];
        $PackingType = $RequestBody['PackingType'];
        $ColdstoreID = $RequestBody['ColdstoreID'];
        $TenantID = $RequestBody['TenantID'];
        $StockType = $RequestBody['StockType'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];
        $PoID = $RequestBody['PoNumber'];
        $Blocks = $RequestBody['Blocks'];
        $Species = $RequestBody['Species'];
        $FreezingType = $RequestBody['FreezingType'];
    $StartDate = $EndDate ="";
        if( isset($RequestBody['StartDate']) && $RequestBody['StartDate'] && isset($RequestBody['EndDate']) && $RequestBody['EndDate'] ){
            $StartDate = $RequestBody['StartDate'];
            $EndDate = $RequestBody['EndDate'];
        }
        $Group = $RequestBody['Group'];

        $TypeProduct = $RequestBody['TypeProduct'];
        $ShortName = $RequestBody['ShortName'];
          $ProductBatchCode = $RequestBody['ProductBatchCode'];
        // $Species= $RequestBody['Species'];

        $Brand = $RequestBody['Brand'];

        $Query = '';

        if ($PoID != "") {
            $Query .= " and CPID.PoID='$PoID'";
        }

        if ($PoGradeId != "") {
            $Query .= " and CPID.Grade='$PoGradeId'";
        }
        if ($VarietyID != "" || $TypeProduct != "") {

            if ($VarietyID != "") {
                $sQuery = " and  ProductID='$VarietyID'";
            }

            // if($Species!=''){
            //     $sQuery.=" and Species='$Species' ";
            // }

            if ($TypeProduct != "") {
                $sQuery .= " and TypeProduct='$TypeProduct'";
            }

            $TypeProduct = $RequestBody['TypeProduct'];
            $ShortName = $RequestBody['ShortName'];
            //    $Species= $RequestBody['Species'];

            $sql2 = "SELECT COALESCE(GROUP_CONCAT(VarietyID),0) VarietyID FROM ScrmPIVarietyList WHERE 1=1  $sQuery  ";
            $stmt2 = $this->conn->prepare($sql2);
            $stmt2->execute();

            $dataRow2 = $stmt2->fetch(PDO::FETCH_ASSOC);
            $VarietyID = $dataRow2['VarietyID'];

            if ($VarietyID != 0) {
                $Query .= " and CPID.VarietyID in ($VarietyID)";
            } else {
                $Query .= " and CPID.VarietyID =''";
            }
        }
        if ($PackingStyleID != "") {
            $Query .= " and CPID.PackingStyleID='$PackingStyleID'";
        }
        if ($PackingType != "") {
            $Query .= " and CPID.PackingTypeID='$PackingType'";
        }
        if ($TenantID != "") {
            $Query .= " and CPID.StockHolderID='$TenantID'";
        }
        if ($ColdstoreID != "") {
            $Query .= " and (CPID.UnitID='$ColdstoreID' or CSRD.StoreID='$ColdstoreID')";
        }
        if ($Blocks != "") {
            $Query .= " and CSBD.BlockID='$Blocks'";
        }
        if ($StockType != "") {
            $Query .= " and CPID.StockFloorStatus='$StockType'";
        }
        if ($Species != '') {
            $Query .= " and VL.Species='$Species'";
        }
        if ($FreezingType != '') {
            $Query .= " and CPID.FreezingType='$FreezingType'";
        }
         if ($Group != "") {
            $Query .= " and CPID.ProductionTypeID='$Group'";
        }

        if ($Brand != "") {
            $Query .= " and VL.`BrandName` = '$Brand'";
        }

        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }
    if( $StartDate && $EndDate ){
            $Query .= " and CPID.ProductionDate BETWEEN '$StartDate' and '$EndDate' ";
            $Limit = '';
        }

    /*if( !empty($RequestBody['SearchKey']) ) {
                $searchTerm = trim($SearchKey);
                $escapedTerm = $searchTerm;
                $likePattern = "%" . $escapedTerm . "%";
                $searchWhere .= " AND ";
                $searchWhere .= " ( StockID LIKE '" . $likePattern . "' ";
                $searchWhere .= " OR StockID LIKE '" . $likePattern . "' ";
                $searchWhere .= " OR CBS.BarCodeID LIKE '" . $likePattern . "' ";
                $searchWhere .= " OR CPID.Grade LIKE '" . $likePattern . "' ";
                $searchWhere .= " OR TrackCode LIKE '" . $likePattern . "' ";
                $searchWhere .= " OR CPID.SockingType LIKE '" . $likePattern . "' ";
                $searchWhere .= " OR CPID.FreezingType LIKE '" . $likePattern . "' ";
                $searchWhere .= " OR CPID.PackingTypeID LIKE '" . $likePattern . "' ";
                $searchWhere .= " OR CPID.AbStatus LIKE '" . $likePattern . "' ";
                $searchWhere .= " OR CPID.AvalMcs LIKE '" . $likePattern . "' ";
                $searchWhere .= " OR CPID.LooseBags LIKE '" . $likePattern . "' ";
                $searchWhere .= " OR PoNo LIKE '" . $likePattern . "' ";
                $searchWhere .= " OR CPID.StoreRackID LIKE '" . $likePattern . "' ";
                $searchWhere .= " OR CPID.ProductCategoryID LIKE '" . $likePattern . "' ";
                $searchWhere .= " OR VL.Species LIKE '" . $likePattern . "' ";
                $searchWhere .= " OR ShortName LIKE '" . $likePattern . "' ";
                $searchWhere .= " OR CONCAT(VL.Species, ' - ', ShortName) LIKE '" . $likePattern . "' ";
                $searchWhere .= " OR CSP.PackingStyle LIKE '" . $likePattern . "' ";
                $searchWhere .= " OR DATE_FORMAT(CPID.ProductionDate, '%d-%m-%Y') LIKE '" . $likePattern . "' )";
            }*/
        $LotNoFilter = $Query;

        if ($SearchKey != '') {
            $Query .= " and CONCAT_WS('', CBS.BarCodeID, PoNo,CPID.AbStatus,CPID.SockingType,ShortName,CSP.PackingStyle, LotNo,CPID.ProductionID,
            StockID, CPID.AvalMcs, CPID.LooseBags,CPID.PackingTypeID,CPID.Grade,CPID.FreezingType, 
            CPID.ProductionDate,CPID.`ProductionTypeID`, CPID.ProductCategoryID,CPID.Remarks, CPID.UnitID,TrackCode, CPID.BatchCode, CDPD.MixedBatch, SBM.BrandName) LIKE '%$SearchKey%'";
        }

        $sqlVarietyCount = "SELECT COUNT(*) as Total FROM `CsStockData` AS CPID 
        LEFT JOIN ScrmPIVarietyList AS VL ON VL.VarietyID=CPID.VarietyID 
        LEFT JOIN AdminPackingStyles AS CSP ON CSP.PckID=CPID.PackingStyleID 
        LEFT JOIN ScrmPoData AS CsPos ON CsPos.PoID=CPID.PoID 
        LEFT JOIN CsStoresRackData as CSRD on CSRD.RowID=CPID.StoreRackID
        LEFT JOIN CsStoresBlockData AS CSBD on CSBD.BlockID=CSRD.BlockID
        LEFT JOIN CsStores AS CSR ON CPID.UnitID=CSR.StoreID
        LEFT JOIN CsDailyProductionData AS CDPD ON CDPD.ProductionID=CPID.ProductionID
        LEFT JOIN RmReceivingDetails AS RRD ON RRD.ReceivingID=CDPD.TrackID
        LEFT JOIN ScrmBrandsMaster SBM ON SBM.BrandID = VL.`BrandName`
        LEFT JOIN `CsBarCodes` AS CBS   ON CBS.BarCodeID = CPID.BarCodeID 
        WHERE CPID.StockStatus='1' $Query
        AND (CPID.AvalMcs!=0 or CPID.LooseBags!=0) AND CPID.StockID IS NOT NULL ";

        $stmt = $this->conn->prepare($sqlVarietyCount);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "CBS.BarCodeID, PoNo,CPID.AbStatus,CPID.SockingType,ShortName,CSP.PackingStyle, LotNo,CPID.ProductionID,
        StockID, CPID.AvalMcs, CPID.LooseBags,CPID.PackingTypeID,CPID.Grade,CPID.FreezingType,CPID.StoreRackID,VL.Species,
        CPID.ProductionDate,CPID.`ProductionTypeID`, CPID.ProductCategoryID,CPID.Remarks as REMARKS, CPID.UnitID,RackName,StoreName,
        CSRD.BlockID,BlockName,TrackCode,DATE_FORMAT(RRD.ArrivalDate, '%d-%m-%Y') AS ArrivalDate,  RRD.TrackCode,
        CASE 
        WHEN RLD.LotNumber IS NOT NULL AND RLD.LotNumber != '' 
            THEN RLD.LotNumber
        WHEN CPID.BatchCode IS NOT NULL AND CPID.BatchCode != '' AND CPID.ProductionTypeID = 'Physical In' 
            THEN CONCAT('P-', CPID.BatchCode)
        WHEN CDPD.MixedBatch IS NOT NULL AND CDPD.MixedBatch != '' 
            THEN CONCAT('M-', CDPD.MixedBatch)
        ELSE ''
        END AS LotNumber,
         (SELECT   CSOD.`CsStockOutID`
            FROM CsStockOutDetails CSOD 		 
            WHERE CSOD.`StockOutID`= CDPD.StockOutID) AS CsStockOutID
        ,CDPD.ProdBatchCode,CSP.TotalSlabs 
        , SBM.BrandName";

        $sqlQuery = "SELECT  $sqlFields FROM `CsStockData` AS CPID
        LEFT JOIN `CsBarCodes` AS CBS   ON CBS.BarCodeID = CPID.BarCodeID 
        LEFT JOIN ScrmPIVarietyList AS VL ON VL.VarietyID=CPID.VarietyID 
        LEFT JOIN AdminPackingStyles AS CSP ON CSP.PckID=CPID.PackingStyleID 
        LEFT JOIN ScrmPoData AS CsPos ON CsPos.PoID=CPID.PoID 
        LEFT JOIN CsStoresRackData as CSRD on CSRD.RowID=CPID.StoreRackID
        LEFT JOIN CsStoresBlockData AS CSBD on CSBD.BlockID=CSRD.BlockID
        left join CsStores as CS on CS.StoreID=CSRD.StoreID OR CS.StoreID=CPID.UnitID
        LEFT JOIN CsDailyProductionData AS CDPD ON CDPD.ProductionID=CPID.ProductionID
        LEFT JOIN RmReceivingDetails AS RRD ON RRD.ReceivingID=CDPD.TrackID
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
        LEFT JOIN ScrmBrandsMaster SBM ON SBM.BrandID = VL.`BrandName`
        WHERE CPID.StockStatus='1' $Query
        AND (CPID.AvalMcs!=0 or CPID.LooseBags!=0) AND CPID.StockID IS NOT NULL  ORDER BY CPID.ProductionDate DESC " . $Limit;

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($errorInfo);

        $ArrayStoreData = [];

        $i = $LimitMax;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
            if ($dataRow['LotNo'] != '') {
                $LotNo = '(' . $dataRow['LotNo'] . ')';
            }

            $ArrayStoreList['SNO'] = ++$i;
            $ArrayStoreList['StockID'] = $dataRow['StockID'];
            $ArrayStoreList['ProductionID'] = $dataRow['ProductionID'];
            $ArrayStoreList['ProductionDate'] = $dataRow['ProductionDate'];
            $ArrayStoreList['PackingTypeID'] = $dataRow['PackingTypeID'];
            $ArrayStoreList['PoNo'] = $dataRow['PoNo'];
            $ArrayStoreList['LotNo'] = $LotNo;
            $ArrayStoreList['VarietyName'] = $dataRow['ShortName'];
            $ArrayStoreList['Grade'] = $dataRow['Grade'];
            $ArrayStoreList['SockingType'] = $dataRow['SockingType'];
            $ArrayStoreList['AvalMcs'] = $dataRow['AvalMcs'];
            $ArrayStoreList['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayStoreList['LooseBags'] = $dataRow['LooseBags'];
            $ArrayStoreList['BarCodeID'] = $dataRow['BarCodeID'];
            $ArrayStoreList['StoreRackID'] = $dataRow['StoreRackID'];
            $ArrayStoreList['AbStatus'] = $dataRow['AbStatus'];
            $ArrayStoreList['ProductionTypeID'] = $dataRow['ProductionTypeID'];
            $ArrayStoreList['ProductCategoryID'] = $dataRow['ProductCategoryID'];
            $ArrayStoreList['Location'] = $dataRow['BlockName'] . ' - ' . $dataRow['RackName'];
            $ArrayStoreList['StoreName'] = $dataRow['StoreName'];
            $ArrayStoreList['StoreID'] = $dataRow['UnitID'];
            $ArrayStoreList['BlockID'] = $dataRow['BlockID'];
            $ArrayStoreList['BlockName'] = $dataRow['BlockName'];
            $ArrayStoreList['Species'] = $dataRow['Species'];
            $ArrayStoreList['TrackCode'] = $dataRow['TrackCode'];

            $ArrayStoreList['ArrivalDate'] = $dataRow['ArrivalDate'];
            $ArrayStoreList['LotNumber'] = $dataRow['LotNumber'];

            if(!empty($dataRow['CsStockOutID']) && empty($dataRow['LotNumber']) && !empty($RequestBody['ShowBatchCode'])){
                $stockOutLotDetails = $this->GetLotNumberByStockOutId($dataRow['CsStockOutID'], $LotNoFilter);
                $ArrayStoreList['LotNumber'] = $stockOutLotDetails['LotNumber'];
                $ArrayStoreList['TrackCode'] = empty($dataRow['TrackCode']) ? $stockOutLotDetails['TrackCode'] :'';
                $ArrayStoreList['ArrivalDate'] = empty($dataRow['ArrivalDate']) ? $stockOutLotDetails['ArrivalDate'] : '';
            }

             $ArrayStoreList['ProdBatchCode'] = $dataRow['ProdBatchCode'];
             $ArrayStoreList['TotalSlabs'] = $dataRow['TotalSlabs'];
             $ArrayStoreList['BrandName'] = $dataRow['BrandName'];

            $ArrayStoreData[] = $ArrayStoreList;
        }

        if (count($ArrayStoreData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TotalCount'] = $TotalCount;
        $data['RemainingCount'] = "";
        $data['EditLink'] = "";
        $data['Data'] = $ArrayStoreData;
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;

        return $data;
    }
    function GetLotNumberByStockOutId($CsStockOutID, $FilterQuery = ''){
        if(!empty($CsStockOutID)){
            $join = '';
            if($FilterQuery){
                $join = " LEFT JOIN ScrmPIVarietyList AS VL ON VL.VarietyID=CPID.VarietyID 
                        LEFT JOIN CsStoresRackData as CSRD on CSRD.RowID=CPID.StoreRackID
                        LEFT JOIN CsStoresBlockData AS CSBD on CSBD.BlockID=CSRD.BlockID ";
            }
            $sql = "SELECT 
                    GROUP_CONCAT(LotNumber SEPARATOR ',<br>') AS LotNumber,
                    GROUP_CONCAT(TrackCode SEPARATOR ',<br>') AS TrackCode,
                    GROUP_CONCAT(ArrivalDate SEPARATOR ',<br>') AS ArrivalDate
                    FROM
                    (
                        SELECT GROUP_CONCAT( DISTINCT RLD.LotNumber ORDER BY PFD.TrackID ASC SEPARATOR ',<br>' ) LotNumber,
                                GROUP_CONCAT( DISTINCT RRD.TrackCode ORDER BY PFD.TrackID ASC  SEPARATOR ',<br>' ) TrackCode,
                                GROUP_CONCAT( DISTINCT DATE_FORMAT(RRD.ArrivalDate, '%d-%m-%Y') ORDER BY PFD.TrackID ASC  SEPARATOR ',<br>' ) ArrivalDate
                        FROM CsStockOutDetails CSOD
                        LEFT JOIN CsStockData CPID ON CPID.`StockID` = CSOD.`StockID`
                        $join
                        LEFT JOIN CsDailyProductionData AS CDPD ON CDPD.ProductionID=CPID.ProductionID
                        LEFT JOIN (
                            SELECT TrackID, TubNo, LineNumber, MAX(ProductionID) AS MaxDataID
                            FROM ProdFreezingData
                            GROUP BY TrackID, TubNo, LineNumber
                        ) PFDx
                        ON CDPD.TrackID = PFDx.TrackID 
                        AND CDPD.TubNo = PFDx.TubNo 
                        AND CDPD.LineNumber = PFDx.LineNumber
                        LEFT JOIN ProdFreezingData PFD ON PFD.ProductionID = PFDx.MaxDataID
                        LEFT JOIN PpcHLGradingBreakup AS PGB ON PFD.GradingBreakupID=PGB.DataID 
                        LEFT JOIN RmLotDetatils AS RLD ON PGB.LotID=RLD.RmLotID 
                        LEFT JOIN RmReceivingDetails AS RRD ON RRD.ReceivingID=PFD.TrackID
                        WHERE CSOD.`CsStockOutID`=$CsStockOutID  AND CPID.ProductionID != 0 $FilterQuery
                        UNION
                        SELECT 
                        GROUP_CONCAT(DISTINCT
                            CONCAT(
                                LEFT(CPID.ProductionTypeID, 1),
                                '-',
                                CASE
                                    WHEN CDPD.MixedBatch IS NOT NULL
                                        AND CDPD.MixedBatch != ''
                                    THEN CDPD.MixedBatch
                                    ELSE CPID.BatchCode
                                END
                            )
                            ORDER BY CSOD.`StockID` ASC SEPARATOR ',<br>'
                        )
                        AS LotNumber
                        ,'' TrackCode,'' ArrivalDate

                        FROM CsStockOutDetails CSOD
                        LEFT JOIN CsStockData CPID ON CPID.`StockID` = CSOD.`StockID`
                        $join
                        LEFT JOIN CsDailyProductionData AS CDPD ON CDPD.ProductionID=CPID.ProductionID
                        WHERE CSOD.`CsStockOutID` IN($CsStockOutID) AND CPID.ProductionID = 0 $FilterQuery
                    ) a";
                 //   echo '<pre>';print_r($sql);die;
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            $data = $stmt->fetch(PDO::FETCH_ASSOC);
            return $data;
        }
    }
}
