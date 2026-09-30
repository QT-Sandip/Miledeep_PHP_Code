<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';

class ExcessOrderReportController
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

    function getExcessOrderDetails($RequestBody)
    {


        $RecordsPerPage = 100;
        $ColdstoreID = $RequestBody['ColdstoreID'];
        //   $ProductionID = $RequestBody['ProductionID'];  // No Filter #teja 14-09-2024
        $TenantID = $RequestBody['TenantID'];
        // $PoID = $RequestBody['PoID'];
        $PoNo = $RequestBody['PoNo'];
        $VarietyID = $RequestBody['VarietyID'];
        $Grade = $RequestBody['Grade'];
        $PackingStyleID = $RequestBody['PackingStyleID'];
        $PackingTypeID = $RequestBody['PackingTypeID'];
        $ProductCategoryID = $RequestBody['ProductCategoryID'];
        $Species = $RequestBody['Species'];
        $TypeProduct = $RequestBody['TypeProduct'];
        // $Status = $RequestBody['Status'];
        // $OrderStatus = $RequestBody['OrderStatus'];
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];
         $ProductBatchCode = $RequestBody['ProductBatchCode'];

        // if ($FromDate != '' && $ToDate != '') {
        //     $DateFilters = " CPOS.PODate BETWEEN '$FromDate' AND '$ToDate'";
        // }
        $DateFilter = " SPD.PODate <= '$ToDate'";

        $Query = '';
        $PoQuery = '';


        if ($Grade != "") {
            $Query .= " and SPVL.Grade='$Grade'";
        }

        // if ($VarietyID != "") {
        //     $Query .= " and SPVL.VarietyID='$VarietyID'";
        // }


        // if ($VarietyID != "") {
        //     $TypeProduct = $RequestBody['TypeProduct'];
        //     // $ShortName = $RequestBody['ShortName'];
        //     $Species = $RequestBody['Species'];

        //    echo  $sql2 = "SELECT COALESCE(GROUP_CONCAT(VarietyID),0) VarietyID FROM ScrmPIVarietyList WHERE  Species='$Species' and  ProductID='$VarietyID' and TypeProduct='$TypeProduct'";
        //     $stmt2 = $this->conn->prepare($sql2);
        //     $stmt2->execute();

        //     $dataRow2 = $stmt2->fetch(PDO::FETCH_ASSOC);
        //     $VarietyID = $dataRow2['VarietyID'];

        //     if ($VarietyID != 0) {
        //         $Query .= " and SPVL.VarietyID in ($VarietyID)";
        //     } else {
        //          $Query .= " and SPVL.VarietyID =''";
        //     }
        // }

        if ($Species != "") {
            $Query .= " and SPVL.Species='$Species'";
        }
        if ($TypeProduct != "") {
            $Query .= " and SPVL.TypeProduct='$TypeProduct'";
        }


        if ($PackingStyleID != "") {
            $Query .= " and CSD.PackingStyleID='$PackingStyleID'";
        }
        if ($PoNo != "") {
            $Query .= " and SPD.PoID='$PoNo'";
        }

        if ($TenantID != "") {
            $Query .= " and CSD.StockHolderID='$TenantID'";
        }
        if ($ColdstoreID != "") {
            $Query .= " and CSD.UnitID='$ColdstoreID'";
        }

        if ($PackingTypeID != "") {
            $Query .= " and CSD.PackingTypeID='$PackingTypeID'";
        }
        if ($ProductCategoryID != "") {
            $Query .= " and CSD.ProductCategoryID='$ProductCategoryID'";
        }

        // $PoQuery = '';
        // if ($OrderStatus == 0) {
        //     $PoQuery = " WHERE SCI.PiID is null or SCI.StockOutDate =''";
        // } else {
        //     $PoQuery = " where SCI.StockOutDate!=''";
        // }


        // if ($PoID != "") {
        //     // $Query .= " and CSD.PoID='$PoID'";
        //     $PoQuery .= " and PiID='$PoID'";
        // }


        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $SearchQuery = " and CONCAT_WS('',CSD.PackingStyleID,ProductionID,SPGD.Brand,SPVL.TypeProduct,SPVL.Species,
            StockID, AvalMcs, LooseBags,CSD.PackingTypeID,SPVL.Grade,SPVL.FreezingType,SPD.PoID,SPVL.VarietyID,SPVL.TypeProduct,
            CSD.ProductCategoryID,CSD.UnitID) LIKE '%$SearchKey%'";
        }

        //  $sqlVarietyCount = "SELECT COUNT(*) as Total  FROM `CsBarCodes` as cb 
        // --  left join `CsStockData` as CSD on cb.BarCodeID = CSD.BarCodeID
        //  WHERE  (CSD.AvalMcs!=0 or CSD.LooseBags !=0)  $Query $SearchQuery";
        // $stmt = $this->conn->prepare($sqlVarietyCount);
        // $stmt->execute();
        // $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
       




        // $SqlPoIds = "SELECT GROUP_CONCAT(PoID) as PoIDs FROM  ScrmPoData as SPD 
        //             LEFT join `ScrmCustomInvoices` as SCI on SPD.PoID=SCI.PiID $PoQuery";
        // $stmtPoIds = $this->conn->prepare($SqlPoIds);
        // $stmtPoIds->execute();
        // $dataRowPoIds = $stmtPoIds->fetch(PDO::FETCH_ASSOC);
        // $PoIDs = $dataRowPoIds['PoIDs'];


        $sqlFields = "coalesce(SPD.PoNo,'NA') as PoNo,SPVL.FreezingType,SPVL.Grade,CSD.PackingTypeID,
        AvalMcs,LooseBags,AvalKgs,APS.PackingStyle,SPGD.Brand,SPVL.ProductDescription,CSD.PackingStyleID
        CSD.ProductCategoryID,CSD.UnitID,SPVL.DeviSUPC,StoreName,CDPD.ProdBatchCode";

     $sqlQuery = "
     SELECT 
     Group_Concat(StockID) as StockID,
    SPD.PoNo, 
    SPVL.DeviSUPC, 
    SPGD.Brand, 
    SPVL.ProductDescription, 
    SPVL.Treatment, 
    SPVL.FreezingType, 
    APS.PackingStyle, 
    SPVL.Grade, 
    SPVL.Glaze, 
    SPVL.GlazePerc, 
    SPGD.Mcs AS OrderMcs, 
    APS.TotalSlabs,
    (SELECT SUM(OutMcs) 
     FROM CsStockOutDetails 
     WHERE OutType = 'Shipment Out' 
     AND StockID IN (SELECT StockID FROM CsStockData WHERE PoGradeId = SPGD.ID)) AS ShipedMcs,
    SUM(CSD.AvalMcs) AS ExcessMcs,
    SUM(CSD.LooseBags) AS ExcessSlabs, 
    SPD.InvNumFull, 
    SPD.InvDate ,
    CS.StoreName,GROUP_CONCAT(DATE_FORMAT(RRD.ArrivalDate, '%d-%m-%Y')) AS ArrivalDate, GROUP_CONCAT(RRD.TrackCode) AS TrackCode,GROUP_CONCAT(RLD.LotNumber) AS LotNumber
FROM 
    ScrmPoData AS SPD 
LEFT JOIN 
    ScrmCustomInvoices AS SCI ON SCI.PiID = SPD.PoID 
LEFT JOIN 
    ScrmPoGradeData AS SPGD ON SPGD.InvId = SPD.PoID 
LEFT JOIN 
    ScrmPIVarietyList AS SPVL ON SPVL.VarietyID = SPGD.VarietyID 
LEFT JOIN 
    AdminPackingStyles AS APS ON APS.PCKID = SPVL.PackingStyle 
LEFT JOIN 
    CsStockData AS CSD ON CSD.PoGradeId = SPGD.ID 
    left join CsDailyProductionData AS CDPD ON CSD.ProductionID=CDPD.ProductionID 
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
LEFT JOIN 
    CsStores AS CS ON CS.StoreID = CSD.UnitID 
WHERE 
    SCI.StockOutDate != '' and  $DateFilter $SearchQuery $Query
GROUP BY 
    SPGD.ID
HAVING 
    SUM(CSD.AvalMcs) > 0 OR SUM(CSD.LooseBags) > 0 " . $Limit;
        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        $TotalCount =$stmtSelect->num_rows;;
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $ArrayStoreData = [];
        $TotalsList = '';
        $TotalLoose = '';
        $TotalKgs = '';
        $i = $LimitMax;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $StoreName = $this-> getStoreWiseStock($dataRow['StockID']);
            $OrderMcs = $dataRow['OrderMcs'];
            $ShipedMcs = $dataRow['ShipedMcs'];
            $ExcessSlabs  = $dataRow['ExcessSlabs'] % $dataRow['TotalSlabs'];
             $SlabsInMcs = floor($dataRow['ExcessSlabs'] / $dataRow['TotalSlabs']);
            $OrderKgs = round($dataRow['OrderKgs'],2);
          //  $ShipedMcs =$dataRow['ExcessMcs']+$SlabsInMcs
            $PackedKgs = round($dataRow['PackedKgs'],2);
            $ExcessKgs = round($PackedKgs - $OrderKgs,2);
            $ExcessMcs = $dataRow['ExcessMcs']+$SlabsInMcs;

            $ArrayStoreList['SNO'] = ++$i;
            $ArrayStoreList['PoNo'] = $dataRow['PoNo'];
            $ArrayStoreList['Brand'] = $dataRow['Brand'];
            $ArrayStoreList['Treatment'] = $dataRow['Treatment'];
            $ArrayStoreList['Glaze'] = $dataRow['Glaze'];
            $ArrayStoreList['GlazePerc'] = $dataRow['GlazePerc'];
            $ArrayStoreList['StockID'] = $dataRow['StockID'];
            // $ArrayStoreList['BarCodeID'] = $dataRow['BarCodeID'];
            $ArrayStoreList['Variety'] = $dataRow['ProductDescription'];
            $ArrayStoreList['Grade'] = $dataRow['Grade'];
            $ArrayStoreList['SUPC'] = $dataRow['DeviSUPC'];
            $ArrayStoreList['OrderMcs'] = $OrderMcs;
            $ArrayStoreList['OrderKgs'] = $OrderKgs;
            $ArrayStoreList['ShipedMcs'] = $ShipedMcs;
            $ArrayStoreList['PackedMcs'] = $PackedMcs;
            $ArrayStoreList['PackedKgs'] = $PackedKgs;
            $ArrayStoreList['ExcessKgs'] = $ExcessKgs;
            $ArrayStoreList['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayStoreList['FreezingType'] = $dataRow['FreezingType'];

            $ArrayStoreList['ArrivalDate'] = $dataRow['ArrivalDate'];
            $ArrayStoreList['TrackCode'] = $dataRow['TrackCode'];
            $ArrayStoreList['LotNumber'] = $dataRow['LotNumber'];
              $ArrayStoreList['ProdBatchCode'] = $dataRow['ProdBatchCode'];

            // $ArrayStoreList['PackingType'] = $dataRow['PackingTypeID'];
            // $ArrayStoreList['Group'] = $dataRow['ProductionTypeID'];
            // $ArrayStoreList['Category'] = $dataRow['ProductCategoryID'];
            // $ArrayStoreList['ProductionDate'] = $dataRow['ProductionDate'];
            // $ArrayStoreList['RackNumber'] = $dataRow['RackName'];
            // $ArrayStoreList['Block'] = $dataRow['BlockName'];
            $ArrayStoreList['ExcessMcs'] = $ExcessMcs;
            $ArrayStoreList['ExcessSlabs'] =  $ExcessSlabs;
            $ArrayStoreList['Store'] = $StoreName ;
            $ArrayStoreList['Plant'] = $dataRow['Plant'] = 'NA';

            $ArrayStoreData[] = $ArrayStoreList;

            $TotalsList['TotalOrderMcs'] += $OrderMcs;
            $TotalsList['TotalShipedMcs'] += $ShipedMcs;
            $TotalsList['TotalExcessMcs'] += $ExcessMcs;
            $TotalsList['TotalExcessSlabs'] +=  $ExcessSlabs;
            $TotalsList['TotalOrderKgs'] += $OrderKgs;
            $TotalsList['TotalPackedKgs'] += $PackedKgs;
            $TotalsList['TotalExcessKgs'] += $ExcessKgs;
            $TotalsList['TotalKgs'] += 0;
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
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;
        $data['Data'] = $ArrayStoreData;
        $data['Totals'] = $TotalsList;


        return $data;
    }

    function getStoreWiseStock($StockId){

       $sqlQuery=" select sum(AvalMcs) as AvalMcs,sum(LooseBags) as LooseBags,StoreName from CsStockData AS CSD  LEFT JOIN 
                    CsStores AS CS ON CS.StoreID = CSD.UnitID WHERE CSD.StockID in ($StockId) group by CSD.UnitID";

            $stmtSelect = $this->conn->prepare($sqlQuery);
            $stmtSelect->execute();

            $DataString=array();
    
            while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
                $DataString[]=$dataRow['StoreName'] ." - ".$dataRow['AvalMcs'] ."M,".$dataRow['LooseBags'] ."S";
                //$DataString=$dataRow['AvalMcs'];
            }

            return implode('<br>',$DataString);
    }
}
