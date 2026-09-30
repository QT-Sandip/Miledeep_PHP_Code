<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';

class RackWiseStockReportController
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

    function getRackwiseStockDetails($RequestBody)
    {

        // var_dump($RequestBody);

        $RecordsPerPage = 100;
        $ColdstoreID = $RequestBody['ColdstoreID'];
        $TenantID = $RequestBody['TenantID'];
        $PoID = $RequestBody['PoID'];
        $VarietyID = $RequestBody['VarietyID'];
        $ShortNames = $RequestBody['ShortName'];
        $Grades = $RequestBody['Grade'];
        $PackingStyleID = $RequestBody['PackingStyleID'];
        $PackingTypeIDs = $RequestBody['PackingTypeID'];
        $ProductCategoryIDs = $RequestBody['ProductCategoryID'];
        $Blocks = $RequestBody['Blocks'];
        $AntiBiotic = $RequestBody['AbStatus'];
        $Soakings = $RequestBody['Soaking'];
        $FreezingTypes = $RequestBody['FreezingType'];
        $Species = $RequestBody['Species'];
        $TypeofProducts = $RequestBody['TypeofProduct'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];
        $StockType = $RequestBody['StockType'];
        $GlazePerc = $RequestBody['GlazePerc'];
        $Brand = $RequestBody['Brand'];
        $RequestFrom = $RequestBody['RequestFrom'];
        $ShowBrandInCSReport = $RequestBody['ShowBrandInCSReport'];
         $ProductBatchCode = $RequestBody['ProductBatchCode'];
        
        

        $Query = '';

        if ($ColdstoreID != "") {
            $Query .= " and (CPID.UnitID IN ($ColdstoreID) or cr.StoreID IN ($ColdstoreID))";
        }

        if ($Blocks != "") {
            $Query .= " and CBD.BlockID='$Blocks'";
        }

        if ($TenantID != "") {
            $Query .= " and CPID.StockHolderID IN ($TenantID)";
        }

        if ($PoID != "") {
            $Query .= " and CPID.PoID='$PoID'";
        }
        if ($Species != '') {
            $Query .= " and VL.Species='$Species'";
        }
        if ($TypeofProducts != '') {
            // $Query .= " and VL.TypeProduct IN ($TypeofProduct)";
            $TypeofProduct = "'" . implode("','", array_map('trim', explode(',', $TypeofProducts))) . "'";
            $Query .= " and VL.TypeProduct IN ($TypeofProduct)";
        }
        if ($FreezingTypes != '') {
            // $Query .= " and CPID.FreezingType IN ($FreezingType)";
            $FreezingType = "'" . implode("','", array_map('trim', explode(',', $FreezingTypes))) . "'";
            $Query .= " and CPID.FreezingType IN ($FreezingType)";
        }
        if ($ShortNames != "") {
             $ShortName = "'" . implode("','", array_map('trim', explode(',', $ShortNames))) . "'";
            $Query .= " AND VL.ShortName IN ($ShortName)";
        }
        if ($Grades != "") {
            $Grade = "'" . implode("','", array_map('trim', explode(',', $Grades))) . "'";
            $Query .= " and CPID.Grade IN ($Grade)";
        }
        if ($Soakings != '') {
            // $Query .= " AND CPID.`SockingType` IN ($Soaking)";
             $Soaking = "'" . implode("','", array_map('trim', explode(',', $Soakings))) . "'";
            $Query .= " and CPID.SockingType IN ($Soaking)";
        }
        if ($AntiBiotic != '') {
            $Query .= " AND CPID.`AbStatus` = '$AntiBiotic' ";
        }
        if ($PackingStyleID != "") {
            $Query .= " and CPID.PackingStyleID IN ($PackingStyleID)";
        }
        if ($PackingTypeIDs != "") {
            // $Query .= " and CPID.PackingTypeID IN ($PackingTypeID)";
            $PackingTypeID = "'" . implode("','", array_map('trim', explode(',', $PackingTypeIDs))) . "'";
        $Query .= " and CPID.PackingTypeID IN ($PackingTypeID)";
        }
        if ($ProductCategoryIDs != "") {
            // $Query .= " and CPID.ProductCategoryID IN ($ProductCategoryID)";
               $ProductCategoryID = "'" . implode("','", array_map('trim', explode(',', $ProductCategoryIDs))) . "'";
            $Query .= " and CPID.ProductCategoryID IN ($ProductCategoryID)";
        }
        if ($Brand != "") {
            $Query .= " and VL.BrandName='$Brand'";
        }

        if ($RequestFrom != "") {
            $Query .= " and VL.GlazePerc='$GlazePerc'";
        }
        if ($StockType != "") {
            if ($StockType == "0") {
                $Query .= " and (cr.RackName = '' or CPID.StoreRackID = '' or CPID.StoreRackID = '0')";
            } else if ($StockType == "1") {
                $Query .= " and cr.RackName!=''";
            }
        }

        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $SearchQuery = " and CONCAT_WS('', ShortName,CSP.PackingStyle, LotNo,ProductionID,
            StockID, AvalMcs, LooseBags,PackingTypeID,CPID.Grade,CPID.FreezingType,CPID.AbStatus,PoNo,
            ProductionDate,`ProductionTypeID`, ProductCategoryID,CPID.Remarks, UnitID, CPID.BatchCode, CDPD.MixedBatch) LIKE '%$SearchKey%'";
        }

        $sqlVarietyCount = "SELECT COUNT(*) as Total FROM `CsStockData` as CPID 
        left join `CsStoresRackData` as cr on CPID.StoreRackID = cr.RowID
        left join `CsStoresBlockData` as CBD on CBD.BlockID = cr.BlockID 		
        left join `ScrmPoData` as CPOS on  CPID.PoID = CPOS.PoID 
        left join `ScrmPIVarietyList` as VL on CPID.VarietyID = VL.VarietyID
        left join `AdminPackingStyles` as CSP on CPID.PackingStyleID = CSP.PckID
        left join CsStores as CS on cr.StoreID = CS.StoreID OR CPID.UnitID = CS.StoreID";
        if($ShowBrandInCSReport == 1){
            $sqlVarietyCount .= " LEFT JOIN `ScrmBrandsMaster` SBM ON SBM.`BrandID`= VL.`BrandName` ";
         }
        $sqlVarietyCount .= " where  (CPID.AvalMcs!=0 or CPID.LooseBags !=0)  $Query $SearchQuery";

        $stmt = $this->conn->prepare($sqlVarietyCount);
        $stmt->execute();

        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "
        (CSP.TotalSlabs*CPID.AvalMcs) AS McsSlabs,
        (CPID.LooseBags) AS LooseSlabs,coalesce(PoNo,'NA') as PoNo,
        CPID.BarCodeID,ShortName,CPID.FreezingType,CPID.Grade, CPID.PackingTypeID,CPID.AbStatus,
        CPID.AvalMcs,CPID.LooseBags,CPID.AvalKgs,CPID.PackingTypeID,CSP.PackingStyle,CSP.TotalSlabs,CPID.ProductionTypeID,CPID.ProductionDate,RackName,BlockName,cr.BlockID,
        CPID.ProductCategoryID,CPID.UnitID,VL.DeviSUPC,BlockName,StoreName,VL.TypeProduct,VL.Species, VL.Treatment as Soaking,DATE_FORMAT(RRD.ArrivalDate, '%d-%m-%Y') AS ArrivalDate,  RRD.TrackCode,
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
        if($ShowBrandInCSReport == 1){
            $sqlFields .=',SBM.`BrandName`'; 
        }
        $sqlQuery = "SELECT $sqlFields FROM `CsStockData` as CPID 
        left join `CsStoresRackData` as cr on CPID.StoreRackID = cr.RowID
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
            LEFT JOIN ProdFreezingData PFD
            ON PFD.ProductionID = PFDx.MaxDataID
            left join PpcHLGradingBreakup as PGB on PFD.GradingBreakupID=PGB.DataID 
            left join RmLotDetatils as RLD on PGB.LotID=RLD.RmLotID 
        left join `CsStoresBlockData` as CBD on CBD.BlockID = cr.BlockID 		
        left join `ScrmPoData` as CPOS on  CPID.PoID = CPOS.PoID 
        left join `ScrmPIVarietyList` as VL on CPID.VarietyID = VL.VarietyID
        left join `AdminPackingStyles` as CSP on CPID.PackingStyleID = CSP.PckID
        -- left join CsStores as CS on cr.StoreID = CS.StoreID OR CPID.UnitID = CS.StoreID 
        LEFT JOIN CsStores AS CS ON CS.StoreID = COALESCE(NULLIF(cr.StoreID, 0), CPID.UnitID)";
        if($ShowBrandInCSReport == 1){
            $sqlQuery .= " LEFT JOIN `ScrmBrandsMaster` SBM ON SBM.`BrandID`= VL.`BrandName` ";
        }
        $sqlQuery .= "where  (CPID.AvalMcs!=0 or CPID.LooseBags !=0) $Query $SearchQuery order by ShortName asc " . $Limit;

        // echo $sqlQuery;exit;
        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($errorInfo);

        $ArrayStoreData = [];
        $TotalSlabs = '';
        $TotalsList = '';
        $TotalLooseMcs = '';
        $TotalKgs = '';
        $i = $LimitMax;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

          $PackingSlabs = $dataRow['TotalSlabs'];
            $AvalMcs = $dataRow['AvalMcs'];
            $LooseBags = $dataRow['LooseBags'];
            $LooseMcs = floor($LooseBags/$PackingSlabs);
            $LooseBags = $LooseBags - ($LooseMcs * $PackingSlabs);
            $AvalMcs += $LooseMcs;
            $AvalKgs = $dataRow['AvalKgs'];

            $ArrayStoreList['SNO'] = ++$i;
            $ArrayStoreList['PoNo'] = $dataRow['PoNo'];
            $ArrayStoreList['StockID'] = $dataRow['StockID'];
            $ArrayStoreList['BarCodeID'] = $dataRow['BarCodeID'];
            $ArrayStoreList['Variety'] = $dataRow['ShortName'];
            $ArrayStoreList['Grade'] = $dataRow['Grade'];
            $ArrayStoreList['SUPC'] = $dataRow['DeviSUPC'];
            $ArrayStoreList['Mcs'] = $AvalMcs;
            $ArrayStoreList['Loose'] = $LooseBags;
            $ArrayStoreList['Kgs'] = $AvalKgs;
            $ArrayStoreList['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayStoreList['PackingType'] = $dataRow['PackingTypeID'];
            $ArrayStoreList['FreezingType'] = $dataRow['FreezingType'];
            $ArrayStoreList['Group'] = $dataRow['ProductionTypeID'];
            $ArrayStoreList['Category'] = $dataRow['ProductCategoryID'];
            $ArrayStoreList['ProductionDate'] = $dataRow['ProductionDate'];
            $ArrayStoreList['RackNumber'] = $dataRow['RackName'];
            $ArrayStoreList['Block'] = $dataRow['BlockName'];
            $ArrayStoreList['Store'] = $dataRow['StoreName'];
            $ArrayStoreList['Species'] = $dataRow['Species'];
            $ArrayStoreList['AbStatus'] = $dataRow['AbStatus'];
            $ArrayStoreList['TypeofProduct'] = $dataRow['TypeProduct'];
            $ArrayStoreList['McsSlabs'] = $dataRow['McsSlabs'];
            $ArrayStoreList['LooseSlabs'] = $dataRow['LooseSlabs'];
            $ArrayStoreList['Soaking'] = $dataRow['Soaking'];

            $ArrayStoreList['ArrivalDate'] = $dataRow['ArrivalDate'];
            $ArrayStoreList['TrackCode'] = $dataRow['TrackCode'];
            $ArrayStoreList['LotNumber'] = $dataRow['LotNumber'];
             $ArrayStoreList['ProdBatchCode'] = $dataRow['ProdBatchCode'];
            if($ShowBrandInCSReport == 1){
                $ArrayStoreList['BrandName'] = $dataRow['BrandName'];
            }
            $McsSlabs = $dataRow['McsSlabs'] ? $dataRow['McsSlabs'] : 0;
            $LooseSlabs = $dataRow['LooseSlabs'] ? $dataRow['LooseSlabs'] : 0;
            $TotalSlabs = $McsSlabs + $LooseSlabs;
            $ArrayStoreList['TotalSlabs'] = $TotalSlabs;

            $ArrayStoreData[] = $ArrayStoreList;

            $TotalsList['TotalMcs'] += $AvalMcs;
            $TotalsList['TotalLoose'] += $LooseBags;
            $TotalsList['TotalLooseMcs'] += ($AvalMcs + round(($LooseBags/$PackingSlabs),2));
            $TotalsList['TotalSlabs'] += $TotalSlabs;
            $TotalsList['TotalKgs'] += $AvalKgs;
        }

        if (count($ArrayStoreData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

    //     if (!empty($PackingStyleID) && $PackingStyleID != 0) {
    //         $sqlPacking = "SELECT TotalSlabs FROM AdminPackingStyles WHERE PCKID=$PackingStyleID LIMIT 1";
    //          $stmtPacking = $this->conn->prepare($sqlPacking);
    //         $stmtPacking->execute();

    //         $dataRowPacking = $stmtPacking->fetch(PDO::FETCH_ASSOC);
    //        $TotalPackSlabs = 0;

    // if (!empty($dataRowPacking['TotalSlabs'])) {
    //     $TotalPackSlabs = (float)$dataRowPacking['TotalSlabs'];
    // }

    // if ($TotalPackSlabs > 0) {
    //     $TotalLoose = isset($TotalsList['TotalLoose'])
    //         ? (float)$TotalsList['TotalLoose']
    //         : 0;
    //     $TotalMcs = isset($TotalsList['TotalMcs'])
    //         ? (float)$TotalsList['TotalMcs']
    //         : 0;

    //     $LooseMcs = floor($TotalLoose / $TotalPackSlabs);

    //     $TotalsList['TotalLoose'] =
    //         $TotalLoose - ($LooseMcs * $TotalPackSlabs);

    //     $TotalsList['TotalMcs'] =
    //         $TotalMcs + $LooseMcs;
    // }
    //     }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TotalCount'] = $TotalCount;
        $data['RemainingCount'] = "";
        $data['EditLink'] = "";
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;
        $data['Data'] = $ArrayStoreData;
        $data['Totals'] = $TotalsList;


        return $data;
    }
}
