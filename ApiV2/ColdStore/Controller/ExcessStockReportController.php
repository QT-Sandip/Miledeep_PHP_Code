<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';

class ExcessStockReportController
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


    function getExcessStockDetails($RequestBody)
    {
        $ColdStore = $RequestBody['ColdStore'];
        $PoID = $RequestBody['PoID'];
        $Species = $RequestBody['Species'];
        $TypeProduct = $RequestBody['ProductType'];
        $FreezingType = $RequestBody['FreezingType'];
        $Grade = $RequestBody['Grade'];
        $Variety = $RequestBody['Variety'];
        $ProductCategory = $RequestBody['ProductCategory'];
        $Soaking = $RequestBody['Soaking'];
        $AbStatus = $RequestBody['AbStatus'];
        $ProductBatchCode = $RequestBody['ProductBatchCode']; 
        $PckgStyle = $RequestBody['PckgStyle']; 


        $Query = '';
        $PoQuery = '';
        if ($PoID != '') {
            $Query .= " AND CSD.PoID = '$PoID'";
            $PoQuery .= " AND SPD.PoID = '$PoID'";
        }
        if ($Species != '') {
            $Query .= " AND SPVL.Species = '$Species'";
        }
        if ($TypeProduct != '') {
            $Query .= " AND SPVL.TypeProduct = '$TypeProduct'";
        }
        if ($FreezingType != '') {
            $Query .= " AND SPVL.FreezingType = '$FreezingType'";
        }
        if ($ColdStore != '') {
            $Query .= " AND CSD.UnitID = '$ColdStore'";
        }
        if ($Variety != '') {
            $Query .= " AND SPVL.ShortName = '$Variety'";
        }
        if ($Grade != '') {
            $Query .= " AND SPVL.Grade = '$Grade'";
        }

        if ($ProductCategory != '') {
            $Query .= " AND CSD.ProductCategoryID = '$ProductCategory'";
        }
        if ($Soaking != '') {
            $Query .= " AND CSD.SockingType = '$Soaking'";
        }
        if ($AbStatus != '') {
            $Query .= " AND CSD.AbStatus='$AbStatus'";
        }
        if($PckgStyle != ''){
            $Query .= " AND CSD.PackingStyleID='$PckgStyle'";
        }

        $sqlFields = "SUM(CSD.AvalKgs) as AvailableKgsinStore, SUM(CSD.AvalMcs) AS StoreMcs, SUM(CSD.LooseBags) AS StoreLooseBags,TotalSlabs,
        SUM(CSD.AvalMcs*TotalSlabs) AS McsSlabs, SPVL.TypeProduct,SPVL.ShortName,SPVL.Glaze,SPVL.Treatment,SPVL.Grade,SPVL.GlazePerc,
        SPVL.FreezingType,SPVL.Species,SPVL.PreparationType,CSD.ProductCategoryID,APS.PackingStyle,SPVL.DeviSUPC,CSD.PackingStyleID,
        GROUP_CONCAT(DATE_FORMAT(RRD.ArrivalDate, '%d-%m-%Y')) AS ArrivalDate, GROUP_CONCAT(RRD.TrackCode) AS TrackCode,GROUP_CONCAT(RLD.LotNumber) AS LotNumber,CDPD.ProdBatchCode";
        $sqlSelect = "SELECT $sqlFields FROM CsStockData AS CSD 
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
        LEFT JOIN ScrmPIVarietyList AS SPVL ON SPVL.VarietyID = CSD.VarietyID 
        LEFT JOIN AdminPackingStyles AS APS ON CSD.PackingStyleID = APS.PCKID
        WHERE (CSD.AvalMcs!=0 OR CSD.LooseBags !=0) AND CSD.PackingTypeID='DUMMY' $Query
        GROUP BY SPVL.TypeProduct,SPVL.ShortName,SPVL.Glaze,SPVL.Treatment,SPVL.Grade,SPVL.FreezingType,SPVL.Species, SPVL.GlazePerc,CSD.AbStatus";
        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();
        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($errorInfo);

        $i = 0;
        $ArrayExcessStockData = [];
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
            $FilterSpecies = $dataRow['Species'];
            $FilterGrade = $dataRow['Grade'];
            $FilterShortName = $dataRow['ShortName'];
            $FilterTypeProduct = $dataRow['TypeProduct'];
            $FilterFreezingType = $dataRow['FreezingType'];
            $FilterGlaze = $dataRow['Glaze'];
            $FilterGlazePerc = $dataRow['GlazePerc'];
            $FilterTreatment = $dataRow['Treatment'];

            $FilterQuery = " AND SPVL.Species = '$FilterSpecies' AND SPVL.Grade = '$FilterGrade' AND SPVL.ShortName = '$FilterShortName'
            AND SPVL.TypeProduct = '$FilterTypeProduct' AND SPVL.FreezingType = '$FilterFreezingType' AND SPVL.Glaze = '$FilterGlaze'
            AND SPVL.Treatment = '$FilterTreatment' AND SPVL.GlazePerc = '$FilterGlazePerc' $PoQuery";

            $HonFilterQuery = " AND Species = '$FilterSpecies' AND Grade = '$FilterGrade' AND ShortName = '$FilterShortName'
            AND TypeProduct = '$FilterTypeProduct' AND FreezingType = '$FilterFreezingType' AND Treatment = '$FilterTreatment'";

            $OrderedData = $this->getOrderedDetails($FilterQuery);
            $HonCountData = $this->getHeadOnDetails($HonFilterQuery);

            $OrderedKGs = $OrderedData['OrderedKGs'];
            $OrderedMcs = $OrderedData['OrderedMcs'];
            $OrderedSlabs = $OrderedData['OrderedSlabs'];

            $StoreMcs = $dataRow['StoreMcs'];
            $TotalSlabs = $dataRow['TotalSlabs'];
            $StoreKGs = $dataRow['AvailableKgsinStore'];
            $StoreLooseBags = $dataRow['StoreLooseBags'];
            $StoreSlabs = $dataRow['McsSlabs'] + $StoreLooseBags;
            $StoreMcs = round(($StoreSlabs/$TotalSlabs) , 2);

            $BalanceKGs = $StoreKGs + $OrderedData['PackedKgs'] - $OrderedKGs;
            $BalanceMCS = $StoreMcs + $OrderedData['PackedMcs'] - $OrderedMcs;
            $BalanceSlabs = $StoreSlabs + $OrderedData['PackedSlabs'] - $OrderedSlabs;
            $BalanceLoose = $StoreLooseBags;

            $HeadonCount = $HonCountData['HeadonCount'];
            $Yield = $HonCountData['Yield'];
            $HeadonQuantityinKgs = ($BalanceKGs / $Yield) * 100;
            $HeadonQuantityinTons = $HeadonQuantityinKgs / 1000;

            $DisplayKgColor = '';
            if ($BalanceKGs < 0) {
                $DisplayKgColor = 'red';
            } else if ($BalanceKGs > 0) {
                $DisplayKgColor = 'green';
            }

            $DisplayMcsColor = '';
            if ($BalanceMCS < 0) {
                $DisplayMcsColor = 'red';
            } else if ($BalanceMCS > 0) {
                $DisplayMcsColor = 'green';
            }

            if ($BalanceKGs > 0 || $BalanceMCS > 0) {
                // if ($BalanceKGs > 0) {
                $ArrayExcessStockList['SNO'] = ++$i;
                $ArrayExcessStockList['Species'] = $dataRow['Species'];
                $ArrayExcessStockList['Grade'] = $dataRow['Grade'];
                $ArrayExcessStockList['ShortName'] = $dataRow['ShortName'];
                $ArrayExcessStockList['TypeProduct'] = $dataRow['TypeProduct'];
                $ArrayExcessStockList['FreezingType'] = $dataRow['FreezingType'];
                $ArrayExcessStockList['Glaze'] = $dataRow['Glaze'];
                $ArrayExcessStockList['GlazePerc'] = $dataRow['GlazePerc'];
                $ArrayExcessStockList['Treatment'] = $dataRow['Treatment'];
                $ArrayExcessStockList['Style'] = $dataRow['PackingStyle'];
                $ArrayExcessStockList['SUPC'] = $dataRow['DeviSUPC'];

                $ArrayExcessStockList['BalanceKGs'] = round($BalanceKGs, 3);
                $ArrayExcessStockList['BalanceMCS'] = $BalanceMCS;
                $ArrayExcessStockList['BalanceSlabs'] = $BalanceSlabs;
                $ArrayExcessStockList['BalanceLoose'] = $BalanceLoose;

                $ArrayExcessStockList['StoreMcs'] = $StoreMcs;
                $ArrayExcessStockList['StoreKGs'] = round($StoreKGs, 3);
                $ArrayExcessStockList['StoreLooseBags'] = $StoreLooseBags;
                $ArrayExcessStockList['StoreSlabs'] = $StoreSlabs;

                $ArrayExcessStockList['OrderedKGs'] = $OrderedKGs ? round($OrderedKGs, 3) : '0';
                $ArrayExcessStockList['OrderedMcs'] = $OrderedMcs ? $OrderedMcs : '0';
                $ArrayExcessStockList['OrderedSlabs'] = $OrderedSlabs ? $OrderedSlabs : '0';

                $ArrayExcessStockList['HeadonCount'] = $HeadonCount;
                $ArrayExcessStockList['HeadonQuantityinKgs'] = round($HeadonQuantityinKgs, 3);
                $ArrayExcessStockList['HeadonQuantityinTons'] = round($HeadonQuantityinTons, 3);

                $ArrayExcessStockList['DisplayKgColor'] = $DisplayKgColor;
                $ArrayExcessStockList['DisplayMcsColor'] = $DisplayMcsColor;

                $ArrayExcessStockList['ProductCategory'] = $dataRow['ProductCategoryID'];

                $ArrayExcessStockList['ArrivalDate'] = $dataRow['ArrivalDate'];
                $ArrayExcessStockList['TrackCode'] = $dataRow['TrackCode'];
                $ArrayExcessStockList['LotNumber'] = $dataRow['LotNumber'];
                    $ArrayExcessStockList['ProdBatchCode'] = $dataRow['ProdBatchCode'];

                $ArrayExcessStockData[] = $ArrayExcessStockList;
            }
        }

        $Data['Error'] = null;
        $Data['Msg'] = 'Msg';
        $Data['ExcessStockData'] = $ArrayExcessStockData;

        return $Data;
    }

    function getOrderedDetails($FilterQuery)
    {
        $sqlOrderedFields = "SUM(Mcs*TotalSlabs*SlabWeight*MFactor) as OrderedKgs, SUM(Mcs) AS OrderedMcs, SUM(Mcs*TotalSlabs) AS OrderedSlabs,GROUP_CONCAT(SPGD.ID) AS PoGradeID";
        $sqlOrderedSelect = "SELECT $sqlOrderedFields FROM `ScrmPoData` AS SPD   
        LEFT JOIN ScrmPoGradeData AS SPGD ON SPGD.InvId = SPD.PoID
        LEFT JOIN ScrmPIVarietyList AS SPVL ON SPVL.VarietyID=SPGD.VarietyID
        LEFT JOIN AdminPackingStyles AS CSP ON CSP.PCKID = SPGD.PackingStyleID
        WHERE PoID not in (SELECT PiID FROM `ScrmCustomInvoices` WHERE StockOutDate!='') and SPD.Status not in (2,3,4) $FilterQuery";
        $stmtOrderSelect = $this->conn->prepare($sqlOrderedSelect);
        $stmtOrderSelect->execute();

        $rowOrderedData = $stmtOrderSelect->fetch(PDO::FETCH_ASSOC);

        $OrderedKgs = $rowOrderedData['OrderedKgs'];
        $OrderedMcs = $rowOrderedData['OrderedMcs'];
        $OrderedSlabs = $rowOrderedData['OrderedSlabs'];
        $PoGradeId = $rowOrderedData['PoGradeID'];
        $final = '';
        if ($PoGradeId != '') {
            $final = $this->getFinalData($PoGradeId);
        }
        if ($final != '') {
            $Data = [
                "OrderedKGs" => $OrderedKgs,
                "OrderedMcs" => $OrderedMcs,
                "OrderedSlabs" => $OrderedSlabs,
                "PackedMcs" => $final['AvalMcs'],
                "PackedSlabs" => $final['AvalSlabs'],
                "PackedKgs" => $final['FinishedKgs'],
            ];
        } else {
            $Data = [
                "OrderedKGs" => $OrderedKgs,
                "OrderedMcs" => $OrderedMcs,
                "OrderedSlabs" => $OrderedSlabs,
                "PackedMcs" => 0,
                "PackedSlabs" => 0,
                "PackedKgs" => 0,
            ];
        }
        return $Data;
    }

    function getHeadOnDetails($FilterQuery)
    {
        $SqlHeadonData = " SELECT `HeadonCount`, `Yield`  FROM `CsHeadonRequirementMasters` WHERE 1=1 $FilterQuery";
        $stmtHeadonData = $this->conn->prepare($SqlHeadonData);
        $stmtHeadonData->execute();
        $dataRowHeadonData = $stmtHeadonData->fetch(PDO::FETCH_ASSOC);
        $ArrayHeadonData['HeadonCount'] = $dataRowHeadonData['HeadonCount'];
        $ArrayHeadonData['Yield'] = $dataRowHeadonData['Yield'];
        return $ArrayHeadonData;
    }

    function getFinalData($PoGradeId) // Valu: arrayes displaying as 0 
    {

        $sqlFields = "COALESCE(sum(AvalMcs),0) as AvalMcs,COALESCE(sum(AvalMcs*TotalSlabs*SlabWeight*MFactor),0) as FinishedKgs,COALESCE(sum(LooseBags),0) as LooseBags,
        COALESCE(sum(LooseBags*SlabWeight*MFactor),0) as LooseBagsKgs,COALESCE(sum(AvalMcs*TotalSlabs),0) as AvalSlabs,COALESCE(sum(AvalKgs),0) as AvalKgs";

        $sqlFinalsData = "SELECT $sqlFields FROM `CsStockData` as CSD 
        LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID=CSD.PackingStyleID 
        WHERE PoGradeId='$PoGradeId'";
        $stmtFinalsData = $this->conn->prepare($sqlFinalsData);
        $stmtFinalsData->execute();

        $rowFinalsData = $stmtFinalsData->fetch(PDO::FETCH_ASSOC);

        $finalsData['AvalMcs'] = $rowFinalsData['AvalMcs'];
        $finalsData['AvalSlabs'] = $rowFinalsData['AvalSlabs'];
        // $finalsData['FinishedKgs'] = $rowFinalsData['FinishedKgs'] + $rowFinalsData['LooseBagsKgs'];
        $finalsData['FinishedKgs'] = $rowFinalsData['AvalKgs'];


        return $finalsData;
    }
}
