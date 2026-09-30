<?php

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class StockOutController
{
    private $conn;

    private $utilController;
    private $Error = null;
    private $Msg = '';
    private $auditLogger;


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
        $this->auditLogger = new CentralAuditLogger($this->conn, 'ColdStore');
    }

    public function getStockOutDetails($RequestBody)
    {
        $RecordsPerPage = 100;
        $ProductionDate = $RequestBody['ProductionDate'];
        $ProductionID = $RequestBody['ProductionID'];
        $PoID = $RequestBody['PoID'];
        $PackingType = $RequestBody['PackingType'];
        $VarietyID = $RequestBody['VarietyID'];
        $ShortName = $RequestBody['ShortName'];
        $UnitID = $RequestBody['ColdStore'];
        $Blocks = $RequestBody['Blocks'];
        $Grade = $RequestBody['Grade'];
        $PackingStyle = $RequestBody['PackingStyle'];
        $FreezingType = $RequestBody['FreezingType'];
        $Soaking = $RequestBody['Soaking'];
        $Species = $RequestBody['Species'];
        $TypeofProduct = $RequestBody['TypeofProduct'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];
        $AbStatus = $RequestBody['AbStatus'];
        $ProductCategory = $RequestBody['ProductCategory'];

        $Query = '';
        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query .= " AND CONCAT_WS('', PoNo, CSD.ProductionID,AvalMcs,LooseBags,ShortName,CSD.Grade,PackingTypeID,APS.PackingStyle,
            StockID,CSD.BarCodeID,CSRD.StoreID,CSRD.RowID,CSRD.RackName,CSRD.BlockID,CSD.ProductCategoryID, BlockName,
            StoreName,UnitID,CSD.PoID) LIKE '%$SearchKey%'";
        }

        if ($UnitID != '') {
            $Query .= " and CSD.UnitID='$UnitID'";
        }
        if ($ProductionID != '') {
            $Query .= " and CSD.ProductionID='$ProductionID'";
        }
        if ($VarietyID != '') {
            $Query .= " and CSD.VarietyID='$VarietyID'";
        }
        if ($ShortName != '') {
            $Query .= " and SPV.ShortName='$ShortName'";
        }
        if ($PoID != '') {
            $Query .= " and CSD.PoID='$PoID'";
        }
        if ($PackingType != '') {
            $Query .= " and CSD.PackingTypeID='$PackingType'";
        }
        if ($Blocks != '') {
            $Query .= " and CSBD.BlockID='$Blocks'";
        }
        if ($Grade != '') {
            $Query .= " and CSD.Grade='$Grade'";
        }
        if ($PackingStyle != '') {
            $Query .= " and CSD.PackingStyleID='$PackingStyle'";
        }
        if ($FreezingType != '') {
            $Query .= " and CSD.FreezingType='$FreezingType'";
        }
        if ($Soaking != '') {
            $Query .= " and SPV.Treatment='$Soaking'";
        }
        if ($TypeofProduct != '') {
            $Query .= " and SPV.TypeProduct='$TypeofProduct'";
        }
        if ($Species != '') {
            $Query .= " and SPV.Species='$Species'";
        }
        if ($AbStatus != '') {
            $Query .= " and CSD.AbStatus='$AbStatus'";
        }
        if ($ProductCategory != '') {
            $Query .= " and CSD.ProductCategoryID='$ProductCategory'";
        }

        // $sqlCount = "SELECT COUNT(*) as Total  FROM `CsStockData` AS CSD
        // LEFT JOIN ScrmPIVarietyList AS SPV ON SPV.VarietyID=CSD.VarietyID
        // LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID=CSD.PackingStyleID
        // LEFT JOIN CsStoresRackData AS CSRD ON CSRD.RowID =CSD.StoreRackID
        // LEFT JOIN ScrmPoData AS SPD ON SPD.PoID=CSD.PoID
        // LEFT JOIN CsStoresBlockData AS CSBD ON CSBD.BlockID=CSRD.BlockID
        // LEFT JOIN CsStores AS CS ON CS.StoreID=CSD.UnitID
        // WHERE StockID!='' $Query AND (AvalMcs!=0 OR LooseBags!=0) AND (AvalMcs > 0 OR LooseBags >0)
        // ORDER BY abs(CSD.Grade) ASC ";
        // // WHERE StockID!='' $Query AND (AvalMcs!=0 OR LooseBags!=0) AND (AvalMcs>0 OR LooseBags>0)

        // $stmtCount = $this->conn->prepare($sqlCount);
        // $stmtCount->execute();
        // $dataRow = $stmtCount->fetch(PDO::FETCH_ASSOC);
        // $TotalCount = $dataRow['Total'];
        // $PageCount = ceil($TotalCount / $RecordsPerPage);

        $dataRow = null;
        $sqlFields = "PoNo, CSD.ProductionID,CSD.AvalMcs,CSD.LooseBags,ShortName,CSD.Grade,CSD.PackingTypeID,APS.PackingStyle,CSD.StockID,CSD.BarCodeID,
        CSRD.StoreID,CSRD.RowID as RackID,CSRD.RackName,CSRD.BlockID,CSD.ProductCategoryID as ProductCategory,CSD.ProductionDate,CSD.TransactionDate,
        (CSD.AvalMcs*TotalSlabs*SlabWeight*MFactor) as AvalMcsKgs, (CSD.LooseBags*SlabWeight*MFactor) as LooseKgs,CSD.AvalKgs,
        BlockName,StoreName,CSD.UnitID,CSD.PoID,CSD.FreezingType,SPV.TypeProduct,SPV.Species,SPV.Treatment as Soaking,DATE_FORMAT(RRD.ArrivalDate, '%d-%m-%Y') AS ArrivalDate,  RRD.TrackCode,
        CASE
        WHEN RLD.LotNumber IS NOT NULL AND RLD.LotNumber != ''
            THEN RLD.LotNumber
        WHEN CSD.BatchCode IS NOT NULL AND CSD.BatchCode != ''
            THEN CONCAT('P-', CSD.BatchCode)
        WHEN CDPD.MixedBatch IS NOT NULL AND CDPD.MixedBatch != ''
            THEN CONCAT('M-', CDPD.MixedBatch)
        ELSE ''
        END AS LotNumber
        ,CDPD.ProdBatchCode";

        $sqlQuery = "SELECT $sqlFields FROM `CsStockData` AS CSD
         LEFT JOIN CsDailyProductionData AS CDPD ON CDPD.ProductionID=CSD.ProductionID
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
        LEFT JOIN ScrmPIVarietyList AS SPV ON SPV.VarietyID=CSD.VarietyID
        LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID=CSD.PackingStyleID
        LEFT JOIN CsStoresRackData AS CSRD ON CSRD.RowID =CSD.StoreRackID
        LEFT JOIN ScrmPoData AS SPD ON SPD.PoID=CSD.PoID
        LEFT JOIN CsStoresBlockData AS CSBD ON CSBD.BlockID=CSRD.BlockID
        LEFT JOIN CsStores AS CS ON CS.StoreID=CSD.UnitID
        WHERE StockID!='' $Query AND (CSD.AvalMcs!=0 OR CSD.LooseBags!=0) AND (CSD.AvalMcs > 0 OR CSD.LooseBags >0)
        ORDER BY abs(CSD.Grade) ASC " . $Limit;

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        // echo  $sqlQuery;
        // $errorInfo = $stmt->errorInfo();
        // var_dump($errorInfo);

        $i = 0;
        $TotalKgs = 0;
        $TotalCount = 0;
        $TotalMcs = 0;
        $TotalBags = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // $arrayData = $this->isStockAvailable($dataRow['StockID']);
            // $mcs = ($dataRow['AvalMcs'] - (isset($arrayData['Mcs']) ? $arrayData['Mcs'] : 0));
            // $looseBags = ($dataRow['LooseBags'] - (isset($arrayData['LooseBags']) ? $arrayData['LooseBags'] : 0));
            // echo "mcs -- " . (isset($arrayData['Mcs']) ? $arrayData['Mcs'] : 0) . " -- loose -- " . (isset($arrayData['LooseBags']) ? $arrayData['LooseBags'] : 0);
            // if ($mcs > 0 || $looseBags > 0) {
            // $Kgs = $dataRow['AvalMcsKgs'] + $dataRow['LooseKgs'];
            $Kgs = $dataRow['AvalKgs'];
            $TotalMcs += $dataRow['AvalMcs'];
            $TotalBags += $dataRow['LooseBags'];

            $ArrayStockOutData['SNo'] = ++$i;
            $ArrayStockOutData['ProductionID'] = $dataRow['ProductionID'];
            $ArrayStockOutData['ProductionDate'] = $dataRow['ProductionDate'];
            $ArrayStockOutData['TransactionDate'] = $dataRow['TransactionDate'];
            $ArrayStockOutData['Variety'] = $dataRow['ShortName'];
            $ArrayStockOutData['Grade'] = $dataRow['Grade'];
            $ArrayStockOutData['PackingType'] = $dataRow['PackingTypeID'];
            // $ArrayStockOutData['Mcs'] = $mcs;
            // $ArrayStockOutData['Bags'] = $looseBags;
            $ArrayStockOutData['Mcs'] = $dataRow['AvalMcs'];
            $ArrayStockOutData['Bags'] = $dataRow['LooseBags'];
            $ArrayStockOutData['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayStockOutData['Kgs'] = sprintf('%0.2f', round($Kgs, 2));
            $ArrayStockOutData['StockID'] = $dataRow['StockID'];
            $ArrayStockOutData['BarCodeID'] = $dataRow['BarCodeID'];
            $ArrayStockOutData['StoreID'] = $dataRow['StoreID'];
            $ArrayStockOutData['RackID'] = $dataRow['RackID'];
            $ArrayStockOutData['BlockID'] = $dataRow['BlockID'];
            $ArrayStockOutData['ProductCategory'] = $dataRow['ProductCategory'];
            $ArrayStockOutData['PoID'] = $dataRow['PoID'];
            $ArrayStockOutData['PoNumber'] = $dataRow['PoNo'];
            $ArrayStockOutData['UnitID'] = $dataRow['UnitID'];
            $ArrayStockOutData['StoreName'] = $dataRow['StoreName'];
            $ArrayStockOutData['Location'] = $dataRow['BlockName'] . ' - ' . $dataRow['RackName'];
            $ArrayStockOutData['FreezingType'] = $dataRow['FreezingType'];
            $ArrayStockOutData['Species'] = $dataRow['Species'];
            $ArrayStockOutData['TypeofProduct'] = $dataRow['TypeProduct'];
            $ArrayStockOutData['Soaking'] = $dataRow['Soaking'];

            $ArrayStockOutData['ArrivalDate'] = $dataRow['ArrivalDate'];
            $ArrayStockOutData['LotNumber'] = $dataRow['LotNumber'];
            $ArrayStockOutData['ProdBatchCode'] = $dataRow['ProdBatchCode'];

            $TotalCount += 1;
            $TotalKgs += $Kgs;
            $AryData[] = $ArrayStockOutData;
            // }

        }
        $PageCount = ceil($TotalCount / $RecordsPerPage);
        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['DailyStockOutData'] = $AryData;
        $Data['TotalKgs'] = $TotalKgs;
        $Data['PageCount'] = $PageCount;
        $Data['TotalCount'] = $TotalCount;
        $Data['TotalMcs'] = $TotalMcs;
        $Data['TotalBags'] = $TotalBags;

        return $Data;
    }

    public function postStockOutDetails($Request)  // need execute loop 03-05-2024
    {

        date_default_timezone_set("Asia/Kolkata");
        $StockOutDate = date("Y-m-d");

        if ($Request['StockOutDate'] == '') {
            $Request['StockOutDate'] = $StockOutDate;
        }


        $ProductionID = $Request['ProductionID'];
        $ProductionDate = $Request['ProductionDate'];
        $StockOutDate = $Request['StockOutDate'];
        $StockOutReason = $Request['StockOutReason'];
        $ReStockinStage = $Request['ReStockinStage'];
        $TotalMsc = $Request['TotalMsc'];
        $TotalLooseBags = $Request['TotalLooseBags'];
        $StockItems = $Request['StockItems'];
        $Remarks = $Request['Remarks'];

        // echo print_r($StockItems);exit;
        // $TotalMcs = 0;
        // $TotalLoose = 0;
        // $StockIds = "";
        //   foreach ($StockItems as $Item) {
        //         $TotalMcs += $Item['OutMcs'];
        //         $TotalLoose += $Item['OutLooseBags'];
        //         $StockIds =  $StockIds . "," + $Item['StockID'];
        //   }

        //   echo $TotalMcs . " -- " . $TotalLoose . " -- " . $StockIds;exit;


        $sqlQuery = "select * from  `CsDailyStockOutData` where StockOutDate='$StockOutDate'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $rowCount = $stmt->rowCount();
        $rowCount = $rowCount + 1;



        $BatchCode = str_replace('-', "", $StockOutDate);
        $BatchCode = $BatchCode . "-" . $rowCount;

        $sqlQuery = "INSERT INTO `CsDailyStockOutData`
           ( `ProductionID`,  `StockOutDate`, `Reason`, `ActualOutMcs`, `ActualOutLooseBags`,
           `AvalOutMcs`, `AvalOutLooseBags`, `Remarks`,`OutBatchCode`)
           VALUES ('$ProductionID','$StockOutDate','$StockOutReason','$TotalMsc','$TotalLooseBags',
           '$TotalMsc','$TotalLooseBags','$Remarks','$BatchCode')";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $lastInsertId = $this->conn->lastInsertId(); //uma
        $this->auditLogger->logInsert('CsDailyStockOutData', $lastInsertId, $this->auditLogger->fetchRowById('CsDailyStockOutData', 'CsStockOutID', $lastInsertId), $Request);
        $TotalOutKgs = 0;
        $PoID = 0;
        if ($stmt) {

            foreach ($StockItems as $Item) {
                $Data = $this->postStockOutItems($Request, $Item, $lastInsertId); //uma
                $IsShipment = $Request['IsShipment'];
                $convertLooseToMcs = $Request['convertLooseToMcs'];
                if ($IsShipment == 1 && $convertLooseToMcs == 1) {
                    $Request['remainingLooseBags'] = $Data['remainingLooseBags'];
                }
                if($PoID == 0){
                    $PoID = $Data['PoID'];
                }
                //echo "Hi";
                $TotalOutKgs += $Data['OutKgs'];
            }
            if ($ReStockinStage != '') {
                $this->getAutogeneratedTrackCode($Request, $lastInsertId);
            }

            $sqlQueryOutKgs = "SELECT SUM(`OutMcs`) AS TotalOutMcs,SUM(`OutLooseBags`) AS TotalLooseBags,SUM(ActualOutKgs) AS TotalOutKgs FROM `CsStockOutDetails` WHERE `CsStockOutID` = $lastInsertId";
            $stmtQueryOutKgs = $this->conn->prepare($sqlQueryOutKgs);
            $stmtQueryOutKgs->execute();
            $RowOutKgs = $stmtQueryOutKgs->fetch(PDO::FETCH_ASSOC);

            $TotalOutMcsqry = $RowOutKgs['TotalOutMcs'];
            $TotalOutLooseBagsqry = $RowOutKgs['TotalLooseBags'];
            $TotalOutKgsqry = $RowOutKgs['TotalOutKgs'];

            $sqlQuery = "UPDATE `CsDailyStockOutData` SET `ActualOutMcs`=$TotalOutMcsqry,`ActualOutLooseBags`=$TotalOutLooseBagsqry,`AvalOutMcs`=$TotalOutMcsqry,`AvalOutLooseBags`=$TotalOutLooseBagsqry,`ActualOutKgs`=$TotalOutKgsqry,`AvalOutKgs`=$TotalOutKgsqry WHERE CsStockOutID=$lastInsertId";

            $stmt = $this->conn->prepare($sqlQuery);
            $stmt->execute();


            if($PoID != 0 && $StockOutReason == 'Shipment Out'){
                $this->updateBalanceStockAfterShipment($PoID);
            }

        }
        return $Data;
    }

    function postStockOutItems($Request, $Item, $CsStockOutID)
    {

        $StockOutKgs = 0;
        date_default_timezone_set("Asia/Kolkata");
        $PostedDate = date("Y-m-d H:i:s");

        // $ClientID = $Request['ClientID'];
        // $IsCheckStockOutDateAtCs =$this->appController->getClientConfig($ClientID)['IsStockOutDateAtCs'];

        $StockOutReason = $Request['StockOutReason'];
        $Remarks = $Request['Remarks'];
        $ProductionID = $Item['ProductionID'];
        $StockOutDate = $Request['StockOutDate'];
        $BarCodeID = $Item['BarCodeID'];
        $RackID = $Item['RackID'];
        $BatchCode = str_replace('-', "", $StockOutDate);
        $StockID = $Item['StockID'];
        $IsDeductGlaze = $Request['IsDeductGlaze'];
        $IsDeductGlazeFW = $Request['IsDeductGlazeFW'];
        $IsDeductFWFCGlaze = $Request['IsDeductFWFCGlaze'];

        $IsShipment = $Request['IsShipment'];
        $convertLooseToMcs = $Request['convertLooseToMcs'];

        $remainingLooseBags = (isset($Request['remainingLooseBags']) && trim($Request['remainingLooseBags']) !== '')
            ? json_decode($Request['remainingLooseBags'], true) : [];
        $selectedLooseMcs = (isset($Request['selectedLooseMcs']) && trim($Request['selectedLooseMcs']) !== '')
            ? json_decode($Request['selectedLooseMcs'], true) : [];

        $OutMcs = (isset($Item['OutMcs']) && $Item['OutMcs'] !== '') ? (float) $Item['OutMcs'] : 0;
        $OutLooseBags = (isset($Item['OutLooseBags']) && $Item['OutLooseBags'] !== '') ? (float) $Item['OutLooseBags'] : 0;

        $Sqlfields = "BarCodeID,AvalMcs,LooseBags,AvalKgs,PackingStyleID,PoID,VarietyID";
        $SqlStockDetails = "select $Sqlfields from  `CsStockData` WHERE StockID ='$StockID'";
        $stmt = $this->conn->prepare($SqlStockDetails);
        $stmt->execute();
        $stockRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $BarCodeID = $stockRow['BarCodeID'];
        $PackingStyleID = $stockRow['PackingStyleID'];
        $VarietyID = $stockRow['VarietyID'];
        $BalanceMcs = $stockRow['AvalMcs'] - $OutMcs;
        $BalanceLooseBags = $stockRow['LooseBags'] - $OutLooseBags;
        $PoID = $stockRow['PoID'];
        $RowStockDetails = json_encode($stockRow, true);

        // $sqlKgsQuery = "SELECT TotalSlabs,SlabWeight,MFactor,SPV.GlazePerc,SPV.Glaze FROM `AdminPackingStyles` AS APS LEFT JOIN `ScrmPIVarietyList` AS SPV ON APS.PCKID =SPV.PackingStyle  WHERE VarietyID='$VarietyID'";
        $sqlKgsQuery = "SELECT
                            APS.TotalSlabs,
                            APS.SlabWeight,
                            APS.MFactor,
                            SPV.GlazePerc,
                            SPV.Glaze
                        FROM AdminPackingStyles AS APS
                        LEFT JOIN ScrmPIVarietyList AS SPV
                            ON (
                                (SPV.HasSauce = 'Yes' AND APS.PCKID = SPV.ShrimpPackingStyle)
                                OR
                                (SPV.HasSauce <> 'Yes' AND APS.PCKID = $PackingStyleID)
                            )
                        WHERE SPV.VarietyID = '$VarietyID'
                        ";
        $stmtKgsQuery = $this->conn->prepare($sqlKgsQuery);
        $stmtKgsQuery->execute();
        $RowStockData = $stmtKgsQuery->fetch(PDO::FETCH_ASSOC);

        if ((float) $stockRow['AvalMcs'] < (float) $OutMcs) {
            $OutMcs = (float) $stockRow['AvalMcs'];
        }
        if ((float) $stockRow['LooseBags'] < (float) $OutLooseBags) {
            $OutLooseBags = (float) $stockRow['LooseBags'];
        }

        $McsKgs = $OutMcs * $RowStockData['TotalSlabs'] * $RowStockData['SlabWeight'] * $RowStockData['MFactor'];
        $LooseKgs = $OutLooseBags * $RowStockData['SlabWeight'] * $RowStockData['MFactor'];
        $OutKgs = $McsKgs + $LooseKgs;
        // $OutKgs = $stockRow['AvalMcs'];
        if (($IsDeductGlaze == 1 && $RowStockData['GlazePerc'] > 0)) {
            // if ($Request['IsStoreProductionLinked'] == 1) {
            $GlazeWeight = ($OutKgs * $RowStockData['GlazePerc']) / 100;
            $OutKgs = $OutKgs - $GlazeWeight;
        } else {
            if (($IsDeductGlaze == 0 && $IsDeductGlazeFW == 1 && (strpos($RowStockData['Glaze'], 'FW') === 0) && $RowStockData['GlazePerc'] > 0)) {
                // $GlazeWeight = ($OutKgs * $RowStockData['GlazePerc']) / 100;
                // $OutKgs = $OutKgs + $GlazeWeight;
                 $OutKgs = ($OutKgs / ((100 - $RowStockData['GlazePerc'] )/100));
            }
             if (($IsDeductGlaze == 0 && $IsDeductFWFCGlaze == 1 && (strpos($RowStockData['Glaze'], 'FW') === 0) && $RowStockData['GlazePerc'] > 0)) {
                $GlazeWeight = ($OutKgs * $RowStockData['GlazePerc']) / 100;
                $OutKgs = $OutKgs - $GlazeWeight;
                //  $OutKgs = ($OutKgs / ((100 - $RowStockData['GlazePerc'] )/100));
            }
        }

        $StockOutKgs = $OutKgs;
        $BalanceAvailableKgs = $stockRow['AvalKgs'] - $OutKgs;

        // sriram
        $availabeMcs = $stockRow['AvalMcs'];
        $availableLooseBags = $stockRow['LooseBags'];

        if ($availabeMcs >= $OutMcs && $availableLooseBags >= $OutLooseBags) {
            $BalanceMcs = $availabeMcs - $OutMcs;
            $BalanceLooseBags = $availableLooseBags - $OutLooseBags;
        } else {
            if ($availabeMcs > $OutMcs && $availableLooseBags < $OutLooseBags) {
                $unPackMcs = ceil(($OutLooseBags - $availableLooseBags) / $RowStockData['TotalSlabs']);
                if ($availabeMcs > ($OutMcs + $unPackMcs)) {
                    $BalanceMcs = $availabeMcs - ($OutMcs + $unPackMcs);

                    $BalanceLooseBags = ($availableLooseBags + ($unPackMcs * $RowStockData['TotalSlabs'])) - $OutLooseBags;
                } else {
                    $Data['Error'] = "MCS and Loose bags exceeds available stock";
                    $Data['Msg'] = "";
                    return $Data;
                }
            } else {
                $Data['Error'] = "MCS and Loose bags exceeds available stock";
                $Data['Msg'] = "";
                return $Data;
            }
        }

        if ($IsShipment == 1 && $convertLooseToMcs == 1) {
            $remainingLoose = $remainingLooseBags[$VarietyID];
            if ($remainingLoose != 0) {
                $remainingLooseMcs = $selectedLooseMcs[$VarietyID];
                if ($remainingLooseMcs > 0) {
                    $BalanceMcs += $remainingLooseMcs;
                    $BalanceAvailableKgs = $BalanceAvailableKgs + ($remainingLooseMcs * $RowStockData['TotalSlabs'] * $RowStockData['SlabWeight'] * $RowStockData['MFactor']);
                    $remainingLooseMcs[$VarietyID] = 0;

                }
                $BalanceLooseBags = $remainingLoose;
                $BalanceAvailableKgs = $BalanceAvailableKgs + (($BalanceLooseBags - $availableLooseBags) * $RowStockData['SlabWeight'] * $RowStockData['MFactor']);
                $remainingLooseBags[$VarietyID] = 0;

                $unPackMcs = ceil(($remainingLoose - $availableLooseBags) / $RowStockData['TotalSlabs']);
                $OutMcs = $OutMcs - $unPackMcs;
                $OutLooseBags = ($availableLooseBags + ($unPackMcs * $RowStockData['TotalSlabs'])) - $BalanceLooseBags;

                $McsKgs = $OutMcs * $RowStockData['TotalSlabs'] * $RowStockData['SlabWeight'] * $RowStockData['MFactor'];
                $LooseKgs = $OutLooseBags * $RowStockData['SlabWeight'] * $RowStockData['MFactor'];
                $OutKgs = $McsKgs + $LooseKgs;

            } else {
                $BalanceLooseBags = 0;
                $OutLooseBags = $availableLooseBags;
                $OutKgs = $OutKgs + ($OutLooseBags * $RowStockData['SlabWeight'] * $RowStockData['MFactor']);
                if ($BalanceMcs == 0) {
                    $BalanceAvailableKgs = 0;
                } else {
                    $BalanceAvailableKgs = $BalanceAvailableKgs - ($availableLooseBags * $RowStockData['SlabWeight'] * $RowStockData['MFactor']);
                    if($BalanceAvailableKgs < 0){
                        $BalanceAvailableKgs = 0;
                    }
                }
            }
        }


        if ($BalanceMcs == 0 && $BalanceLooseBags == 0) {
            $StockStatus = 2;
        } else {
            $StockStatus = 1;
        }

        if ($BalanceMcs >= 0 || $BalanceLooseBags >= 0) {
            $oldStockRow = $this->auditLogger->fetchRowById('CsStockData', 'StockID', $StockID);
            $SqlStockUpdate = "UPDATE  `CsStockData` SET AvalMcs ='$BalanceMcs' , LooseBags='$BalanceLooseBags',
            AvalKgs='$BalanceAvailableKgs', StockStatus='$StockStatus' WHERE StockID ='$StockID'";
            $StmtStockUpdate = $this->conn->prepare($SqlStockUpdate);
            $StmtStockUpdate->execute();
            $this->auditLogger->logUpdate('CsStockData', $StockID, $oldStockRow, $this->auditLogger->fetchRowById('CsStockData', 'StockID', $StockID), $Request);
        } else {
            $StmtStockUpdate = false;
            $Error = " Balance Mcs or Loose Bags Should Not less Than 0";
        }


        if ($StmtStockUpdate) {
            $SqlStockOutData = "INSERT INTO `CsStockOutDetails`
            (`PostedDate`,`BarCodeID`,`StockID`,`RackID`, `OutMcs`,`OutLooseBags`,`ActualOutKgs`,`AvalOutKgs`,`Remarks`,`OutType`,`OutStatus`,`BatchCode`,`OutDate`,
            `ProductionID`,`CsStockOutID`) VALUES
            ('$PostedDate','$BarCodeID','$StockID','$RackID','$OutMcs','$OutLooseBags','$OutKgs','$OutKgs','$Remarks','$StockOutReason','3','$BatchCode','$StockOutDate',
            '$ProductionID','$CsStockOutID')";
            $StmtStockOutData = $this->conn->prepare($SqlStockOutData);
            $StmtStockOutData->execute();
            $stockOutDetailId = $this->conn->lastInsertId();
            $this->auditLogger->logInsert('CsStockOutDetails', $stockOutDetailId, $this->auditLogger->fetchRowById('CsStockOutDetails', 'StockOutID', $stockOutDetailId), $Request);
        } else {
            //$Error = "Unable To Update Stock";
        }

        if ($StmtStockOutData) {

            // $SqlStockNo = "SELECT sum(AvalMcs) AS McsTotal,sum(LooseBags) as LooseTotal,BarCodeID
            // from CsStockData where BarCodeID='$BarCodeID'";

            // $StmtStockNo = $this->conn->prepare($SqlStockNo);
            // $StmtStockNo->execute();
            // $RowStock = $StmtStockNo->fetch(PDO::FETCH_ASSOC);
            // $McsTotal = $RowStock['McsTotal'];
            // $LooseTotal = $RowStock['LooseTotal'];

            // if ($McsTotal == 0 && $LooseTotal == 0) {
            //     $SqlRackData = "UPDATE `CsStoresRackData` SET Status ='0', BarCodeID='0' WHERE BarCodeID ='$BarCodeID'";
            //     $StmtRackData = $this->conn->prepare($SqlRackData);
            //     $StmtRackData->execute();
            // }

            $SqlStockNo = "SELECT sum(AvalMcs) AS McsTotal,sum(LooseBags) as LooseTotal,BarCodeID
            from CsStockData where StockID='$StockID'";

            $StmtStockNo = $this->conn->prepare($SqlStockNo);
            $StmtStockNo->execute();
            $RowStock = $StmtStockNo->fetch(PDO::FETCH_ASSOC);
            $McsTotal = $RowStock['McsTotal'];
            $LooseTotal = $RowStock['LooseTotal'];

            if ($McsTotal == 0 && $LooseTotal == 0 && !empty($RackID)) {
                // $SqlRackData = "UPDATE `CsStoresRackData` SET Status ='0',`PlacedStockID`='', BarCodeID='0' WHERE BarCodeID ='$BarCodeID'";
                // $StmtRackData = $this->conn->prepare($SqlRackData);
                // $StmtRackData->execute();

                $SqlRackData = "
                        UPDATE CsStoresRackData
                        SET
                            PlacedStockID = TRIM(BOTH ',' FROM
                                REPLACE(
                                    CONCAT(',', PlacedStockID, ','),
                                    CONCAT(',', '$StockID', ','),
                                    ','
                                )
                            )
                        WHERE RowID = '$RackID'
                    ";

                $StmtRackData = $this->conn->prepare($SqlRackData);
                $StmtRackData->execute();


                $SqlClearRack = "
                        UPDATE CsStoresRackData
                        SET Status='0', BarCodeID='0'
                        WHERE RowID = '$RackID'
                        AND (PlacedStockID IS NULL OR PlacedStockID = '')
                    ";

                $StmtClearRack = $this->conn->prepare($SqlClearRack);
                $StmtClearRack->execute();


            } else {
                $StmtRackData = true;
            }
        } else {
            $Error = "Unable To Update Out Stock";
        }

        if ($StmtRackData) {
            $SqlTransactionHistory = "INSERT INTO `CsTransactionHistory`( `TrnsDate`,`TrnsType`, `Data`) VALUES
            ('$PostedDate','$StockOutReason','$RowStockDetails')";

            $StmtTransactionHistory = $this->conn->prepare($SqlTransactionHistory);
            $StmtTransactionHistory->execute();
            $transId = $this->conn->lastInsertId();
            $this->auditLogger->logInsert('CsTransactionHistory', $transId, $this->auditLogger->fetchRowById('CsTransactionHistory', 'TransactionID', $transId), $Request);
            if ($PoID != "") {
                $sqlCustInvQuery = "SELECT * FROM `ScrmCustomInvoices` WHERE PiID='$PoID'";
                $stmtCustInv = $this->conn->prepare($sqlCustInvQuery);
                $stmtCustInv->execute();
                $RowCustInvData = $stmtCustInv->fetch(PDO::FETCH_ASSOC);
                $custInvStockOut = "";
                if ($RowCustInvData['StockOutDate'] != '') {
                    $custInvStockOut = $RowCustInvData['StockOutDate'];
                } else {
                    $custInvStockOut = $StockOutDate;
                }
                if ($StockOutReason == "Shipment Out") {
                    if ($RowCustInvData['PiID'] != "") {
                        $SqlRackData = "UPDATE `ScrmCustomInvoices` SET StockOutDateAtCs ='$custInvStockOut' WHERE PiID='$PoID'";
                        $StmtRackData = $this->conn->prepare($SqlRackData);
                        $StmtRackData->execute();
                    }
                }
            }
        } else {
            $Error = "Unable To Update Rack Details ";
        }

        $Data['Error'] = $Error;
        $Data['Msg'] = "Success";
        $Data['OutKgs'] = $StockOutKgs;
        $Data['PoID'] = $PoID;
        $Data['remainingLooseBags'] = htmlspecialchars(json_encode($remainingLooseBags), ENT_QUOTES, 'UTF-8');
        return $Data;
    }


function updateBalanceStockAfterShipment($PoID)
{

    $Errors = [];

    $sql = "SELECT
                    CSD.*,
                    APS.TotalSlabs,
                    APS.SlabWeight,
                    APS.MFactor
                FROM CsStockData AS CSD
                LEFT JOIN (
                    SELECT
                        StockID,
                        SUM(OutMcs) AS OutMcs,
                        SUM(OutLooseBags) AS OutBags
                    FROM CsStockOutDetails
                    GROUP BY StockID
                ) AS CSOD
                    ON CSOD.StockID = CSD.StockID
                LEFT JOIN AdminPackingStyles AS APS
                    ON CSD.PackingStyleID = APS.PCKID
                WHERE CSD.StockID IN (
                    SELECT StockID
                    FROM CsStockData
                    WHERE PoGradeId != 0
                    AND (AvalMcs > 0 OR LooseBags > 0)
                    AND (EXISTS (
                        SELECT 1
                        FROM CsStockOutDetails
                        WHERE StockID = CsStockData.StockID
                        AND OutType='Shipment Out'
                    ) OR CsStockData.PoID = $PoID
                )
                )";

    $stmt = $this->conn->prepare($sql);
    if (!$stmt->execute()) {
        $Errors['_query'] = "Unable to load PO stock for shipment split: " . implode(' | ', $stmt->errorInfo());
        return $Errors;
    }

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

        $this->conn->beginTransaction();

        try {

            $insertQuery = "
            INSERT INTO CsStockData(
                ProductionID,PackingCount,ActualMcs,ActualLooseBags,
                AvalMcs,LooseBags,ActualKgs,AvalKgs,PackingTypeID,
                Grade,PoGradeId,FreezingType,VarietyID,Galze,
                SockingType,Salt,ProductionDate,TransactionDate,PostedDate,
                PoID,RmLotID,ProductionTypeID,ProductCategoryID,UnitID,
                PlantID,ShiftID,BarCodeID,StoreRackID,PackingStyleID,
                StockHolderID,Remarks,StockStatus,StockFloorStatus,AbStatus,
                ProdDayCode,ProdBatchCode,SoakingDate,TubNo,LineNumber,
                TapeRolls,Stickers,CountSlips,IsUsedMcs,IsUsedMcsPouches,
                IsUsedPouches,BatchCode,SourceStockID
            ) VALUES (
                '{$row['ProductionID']}',
                '{$row['PackingCount']}',
                '{$row['AvalMcs']}',
                '{$row['LooseBags']}',
                '{$row['AvalMcs']}',
                '{$row['LooseBags']}',
                '{$row['AvalKgs']}',
                '{$row['AvalKgs']}',
                'DUMMY',
                '{$row['Grade']}',
                0,
                '{$row['FreezingType']}',
                '{$row['VarietyID']}',
                '{$row['Galze']}',
                '{$row['SockingType']}',
                '{$row['Salt']}',
                '{$row['ProductionDate']}',
                '{$row['TransactionDate']}',
                '{$row['PostedDate']}',
                0,
                '{$row['RmLotID']}',
                '{$row['ProductionTypeID']}',
                '{$row['ProductCategoryID']}',
                '{$row['UnitID']}',
                '{$row['PlantID']}',
                '{$row['ShiftID']}',
                '{$row['BarCodeID']}',
                '{$row['StoreRackID']}',
                '{$row['PackingStyleID']}',
                '{$row['StockHolderID']}',
                '".addslashes($row['Remarks'])."',
                1,
                '{$row['StockFloorStatus']}',
                '{$row['AbStatus']}',
                '{$row['ProdDayCode']}',
                '{$row['ProdBatchCode']}',
                '{$row['SoakingDate']}',
                '{$row['TubNo']}',
                '{$row['LineNumber']}',
                '{$row['TapeRolls']}',
                '{$row['Stickers']}',
                '{$row['CountSlips']}',
                '{$row['IsUsedMcs']}',
                '{$row['IsUsedMcsPouches']}',
                '{$row['IsUsedPouches']}',
                '{$row['BatchCode']}',
                '{$row['SourceStockID']}'
            )";

            $StmtIconn = $this->conn->prepare($insertQuery);

            if (!$StmtIconn->execute()) {
                throw new Exception(implode(' | ', $StmtIconn->errorInfo()));
            }

            $RemainingMcs   = $row['ActualMcs'] - $row['AvalMcs'];
            $RemainingLoose = $row['ActualLooseBags'] - $row['LooseBags'];

            if ($RemainingLoose < 0) {

                $BorrowMcs = ceil(abs($RemainingLoose) / $row['TotalSlabs']);

                $RemainingMcs -= $BorrowMcs;
                $RemainingLoose += ($BorrowMcs * $row['TotalSlabs']);
            }

            if ($RemainingMcs < 0) {
                $RemainingMcs = 0;
            }

            if ($RemainingLoose < 0) {
                $RemainingLoose = 0;
            }

            $RemainingKgs =
                (
                    ($RemainingMcs * $row['TotalSlabs']) +
                    $RemainingLoose
                ) *
                $row['SlabWeight'] *
                $row['MFactor'];

            if ($row['Galze'] > 0) {
                $RemainingKgs -= ($RemainingKgs * ($row['Galze'] / 100));
            }

            if($RemainingKgs < 0){
                $RemainingKgs = 0;
            }

            $updateQuery = "
            UPDATE CsStockData
            SET
                ActualMcs='$RemainingMcs',
                ActualLooseBags='$RemainingLoose',
                ActualKgs='$RemainingKgs',
                AvalMcs=0,
                LooseBags=0,
                AvalKgs=0,
                StockStatus=2
            WHERE StockID='{$row['StockID']}'";

            $StmtIconn1 = $this->conn->prepare($updateQuery);
            if (!$StmtIconn1->execute()) {
                throw new Exception(implode(' | ', $StmtIconn1->errorInfo()));
            }

            $this->conn->commit();

        } catch (Exception $e) {

            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            // FIX: failure is now recorded instead of silently disappearing.
            $Errors[$row['StockID']] = $e->getMessage();
        }
    }

    return $Errors;
}

    function getAutogeneratedTrackCode($RequestBody, $lastInsertId)
    {

        $StockOutDate = $RequestBody['StockOutDate'];
        //      $PlantID = $RequestBody['PlantID'];
        $StockOutID = $lastInsertId;
        $ReStockinStage = $RequestBody['ReStockinStage'];

        $sqlPlantID = "SELECT PlantID  FROM  CsStockData as CSD
                            left join  `CsStockOutDetails` as CSOD  on CSD.StockID=CSOD.StockID
                            left join CsDailyStockOutData as CDSD on CDSD.CsStockOutID=CSOD.CsStockOutID
                            WHERE CDSD.CsStockOutID='$StockOutID'";
        $stmtPlantID = $this->conn->prepare($sqlPlantID);
        $stmtPlantID->execute();
        $dataRowPlantID = $stmtPlantID->fetch(PDO::FETCH_ASSOC);
        $PlantID = $dataRowPlantID['PlantID'];



        $sqlTrackId = "SELECT max(TrackNumber) as TrackNumber FROM RmReceivingDetails
        WHERE month(ArrivalDate)=month('$StockOutDate') and year(ArrivalDate)=year('$StockOutDate') and ReceivingPlantID='$PlantID' and IsStoreMaterial=1
        ";

        $stmtTrackId = $this->conn->prepare($sqlTrackId);
        $stmtTrackId->execute();
        $dataRowTrackId = $stmtTrackId->fetch(PDO::FETCH_ASSOC);
        $TrackNumber = $dataRowTrackId['TrackNumber'] + 1;

        $sqlSelect = "SELECT ProdCode as PlantCode FROM RmProcessingStations  WHERE ProcessingStationID='$PlantID'";
        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();
        $dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC);

        $PlantCode = $dataRow['PlantCode'];

        $AutoTrackCode = str_replace("O", "0", $TrackNumber);
        $AutoTrackCode = ltrim($AutoTrackCode, "0");
        $AutoTrackCode = preg_replace("/[^0-9]/", "", $AutoTrackCode);
        $AutoTrackCode = sprintf('%04d', $AutoTrackCode);


        $AutoTrackCode = $PlantCode . '-SM- ' . sprintf('%03d', $TrackNumber);

        $sqlInsertQuery = "INSERT INTO RmReceivingDetails(ArrivalDate,ReceivingPlantID,TrackNumber,TrackCode,OutStage,IsStoreMaterial)
            VALUES ('$StockOutDate','$PlantID','$TrackNumber','$AutoTrackCode','$ReStockinStage','1')";
        $stmtInsertQuery = $this->conn->prepare($sqlInsertQuery);
        $stmtInsertQuery->execute();
        $ReceivingID = $this->conn->lastInsertId();
        $this->auditLogger->logInsert('RmReceivingDetails', $ReceivingID, $this->auditLogger->fetchRowById('RmReceivingDetails', 'ReceivingID', $ReceivingID), $RequestBody);

        if ($stmtInsertQuery) {

            $sqlStockOutData = "SELECT CSOD.StockID,OutMcs,OutLooseBags,CSOD.ProductionID,(OutMcs*TotalSlabs*SlabWeight*MFactor) as McsKgs,
                (OutLooseBags*SlabWeight*MFactor) as LooseKgs FROM `CsStockOutDetails` as CSOD
                left join CsDailyStockOutData as CDSD on CDSD.CsStockOutID=CSOD.CsStockOutID
                left join CsStockData as CSD on CSD.StockID=CSOD.StockID left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID WHERE CDSD.CsStockOutID='$StockOutID'";

            $stmtStockOutData = $this->conn->prepare($sqlStockOutData);
            $stmtStockOutData->execute();

            while ($dataStockOutData = $stmtStockOutData->fetch(PDO::FETCH_ASSOC)) {
                $StockID = $dataStockOutData['StockID'];
                $ProductionID = $dataStockOutData['ProductionID'];

                $ReceivingQty = round(($dataStockOutData['McsKgs'] + $dataStockOutData['LooseKgs']), 3);

                $sqlInsertBreakup = "INSERT INTO RmReceivingBreakup(ReceivingID,ReceivingQty,CsProductionID, CsStockID, CsStockOutID)
            VALUES ('$ReceivingID','$ReceivingQty','$ProductionID','$StockID','$StockOutID')";

                $stmtInsertBreakup = $this->conn->prepare($sqlInsertBreakup);
                $stmtInsertBreakup->execute();
                $breakupId = $this->conn->lastInsertId();
                $this->auditLogger->logInsert('RmReceivingBreakup', $breakupId, $this->auditLogger->fetchRowById('RmReceivingBreakup', 'ID', $breakupId), $RequestBody);
            }
            $this->utilController->UpdateTrackWiseThawingMeatWeight($ReceivingID);
        }

        return "Success";
    }

    function isStockAvailable($StockId)
    {
        $sqlQuery = "SELECT COUNT(*) as Total,SUM(NoOfMcs) AS MCS,SUM(NoOfLooseBags) AS LooseBags FROM `CsIBTRequestBreakUpDetails` WHERE `StockID` = $StockId AND `Status` = '1'";
        $stmtCount = $this->conn->prepare($sqlQuery);
        $stmtCount->execute();
        $dataRow = $stmtCount->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $arrayResult = [];
        if ($TotalCount > 0) {
            $arrayResult['Mcs'] = $dataRow['MCS'];
            $arrayResult['LooseBags'] = $dataRow['LooseBags'];
        }
        return $arrayResult;
    }
}
