<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
//include_once 'Controller/UtilController.php';
include_once '../ColdStore/Controller/UtilController.php';

class ReturnContainerReport1Controller
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

    function getStockData($RequestBody)
    {

        $PageNo = $RequestBody['start']?$RequestBody['start']:1;
        $SearchKey = $RequestBody['SearchKey'];
        $LoadAll = $RequestBody['LoadAll'];

        $RecordsPerPage = 100;
        $LimitMax = ($PageNo) * $RecordsPerPage;
        //$Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        $Query = " WHERE SPD.Status =4 AND ISF.FDAContainerStatus REGEXP 'Return Container' ";

        //echo "<pre>"; print_r($RequestBody); exit;

        if( isset( $RequestBody['FromDate']) && isset($RequestBody['ToDate']) ){
            //$Query .= " AND SFD.emptyReturn Between '".$RequestBody['FromDate']."' AND '".$RequestBody['ToDate']."' ";
        }

        if( isset( $RequestBody['productionPlant'] ) && $RequestBody['productionPlant'] ){
            $Query .= " AND SPD.ProductionPlantID = '".$RequestBody['productionPlant']."' ";
        }

        if( isset( $RequestBody['Grade'] ) && $RequestBody['Grade'] ){
            $Query .= " AND SPGD.`Grade` = '".$RequestBody['Grade']."' ";
        }

        if( isset( $RequestBody['PackingStyle'] ) && $RequestBody['PackingStyle'] ){
            $Query .= " AND SPGD.`PackingStyleID` = '".$RequestBody['PackingStyle']."' ";
        }

        
        $dataRow = null;        
        
        $sqlQuery = "SELECT PoID,PoNo,ProductionPlantID,PoDate,InvNo,SPGD.DeviSupc,SPVL.ShortName,SPGD.Grade,APS.PackingStyle,SPGD.Mcs,(Mcs*TotalSlabs*SlabWeight*Price * Mfactor) as PriceKgs FROM ScrmPoData as SPD 
        left join ScrmISFDetails as ISF on ISF.PIID=SPD.PoID
        left join ScrmPoGradeData as SPGD on SPGD.InvId = SPD.InvNo
        left join ScrmPIVarietyList as SPVL on SPVL.VarietyID = SPGD.VarietyID
        left join AdminPackingStyles as APS on APS.PCKID = SPGD.PackingStyleID
        $Query
         ";
        //echo $sqlQuery; exit;
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $Data['BarCodeID'] = $BarCodeID;
        //$Data['BalanceKgs'] = $this->BalanceKgs;
        $i=0;
        while( $dataRow = $stmt->fetch(PDO::FETCH_ASSOC) ){
            $i++;

            $ArrayStockData['Sno'] = $i;
            $ArrayStockData['StockID'] = $dataRow['StockID'];
            $ArrayStockData['TransactionDate'] = $dataRow['TransactionDate'];
            $BreakUpData = $this->getProductionlist(" where CSD.PoID = '".$dataRow['PoID']."' ");
            $ArrayStockData['Remarks'] = $dataRow['Remarks'];
            $ArrayStockData['Mcs'] = $dataRow['AvalMcs'];
            $ArrayStockData['LooseBags'] = $dataRow['LooseBags'];
            $ArrayStockData['UnitID'] = $dataRow['UnitID'];
            $dataRow['PriceKgs'] = $dataRow['PriceKgs']?round($dataRow['PriceKgs'],2):'';
            $dataRow['BreakUpData'] = $BreakUpData;
            $finalDatac[] = $dataRow;
        }
        $finalData['Error'] = null;
        $finalData['Msg'] = 'Success';
        $finalData['TotalCount'] = $totalFiltered;
        $finalData['RemainingCount'] = "";
        $finalData['PageCount'] = $totalFiltered;
        $finalData['RecordsAval'] = $totalFiltered;
        $finalData['Data'] = $finalDatac;
        return $finalData;
    }

    function getProductionlist($Query)
    {
        
        $sqlFields = "SPV.DeviSupc, ProductDescription,ProductionID,`AvalMcs`, `LooseBags`, `PackingTypeID`, CSD.`Grade`,   
        `PoGradeId`, CSD.`FreezingType`, CSD.`VarietyID`, `Galze`, 
`SockingType`, `Salt`, `ProductionDate`, CSD.`PoID`, `RmLotID`,  `ProductionTypeID`, `ProductCategoryID`, `UnitID`, 
`ShiftID`, `BarCodeID`, CSD.`PackingStyleID`, `StockHolderID`, CSD.`Remarks`, CSD.PlantID,
`StockStatus`, `AbStatus`, `ProdDayCode`,`ProdBatchCode`, `SoakingDate`, `TubNo`, `LineNumber`,`PoNo`,AGO.Name as Shift,ShortName as Variety,
APS.PackingStyle,TenantName,StoreName,PoNo,RPS.StationName,
(ActualMcs*TotalSlabs*SlabWeight*MFactor) as Kgs, (ActualLooseBags*SlabWeight*MFactor) as LKgs,ActualMcs,ActualLooseBags";

        $sqlQuery = "SELECT $sqlFields  FROM `CsDailyProductionData` as CSD 
left join ScrmPoData as SPD on SPD.PoID=CSD.PoID
left join AdminGeneralOptions as AGO on AGO.OptID=CSD.ShiftID
left join ScrmPIVarietyList as SPV on SPV.VarietyID=CSD.VarietyID
left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
left join CsTenant as CST on CST.TenantID=CSD.StockHolderID        
left join CsStores as CSS on CSS.StoreID=CSD.UnitID
left join RmProcessingStations as RPS on RPS.ProcessingStationID=CSD.PlantID $Query order by  DeviSupc,ShortName asc LIMIT 0,10";

//return $sqlQuery; 
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $i = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $AvalMcs = $dataRow['AvalMcs'];
            $LooseBags = $dataRow['LooseBags'];
            $ActualMcs = $dataRow['ActualMcs'];
            $ActualLooseBags = $dataRow['ActualLooseBags'];
            $BalanceMcs = $ActualMcs - $AvalMcs;
            $BalanceBags = $ActualLooseBags - $LooseBags;

            $TotalWeight = round($dataRow['Kgs'] + $dataRow['LKgs'], 2);
            //$aryBalance = $this->getBalanceKgs($TotalWeight, $dataRow['ProductionID'], $dataRow);
            $BalanceMcs = $aryBalance['BalanceMcs'];
            $BalanceBags = $aryBalance['BalanceLooseBags'];
            $BalanceKgs = $aryBalance['BalanceWeight'];
            if ($BalanceKgs <= 0) {
                $Status = 1;
            } else {
                $Status = 0;
            }
            
            $ArrayStockData['SNo'] = ++$i;
            $ArrayStockData['OurSupc'] = $dataRow['DeviSupc'];
            $ArrayStockData['ProductDescription'] = $dataRow['ProductDescription'];
            $ArrayStockData['ProductionID'] = $dataRow['ProductionID'];
            $ArrayStockData['TransactionDate'] = $dataRow['TransactionDate'];
            $ArrayStockData['ProductionDate'] = $dataRow['ProductionDate'];
            $ArrayStockData['Group'] = $dataRow['ProductionTypeID'];
            $ArrayStockData['StockHolderID'] = $dataRow['StockHolderID'];
            $ArrayStockData['StockHolderName'] = $dataRow['TenantName'];
            $ArrayStockData['PoNo'] = $dataRow['PoNo'];
            $ArrayStockData['PoID'] = $dataRow['PoID'];
            $ArrayStockData['StoreID'] = $dataRow['UnitID'];
            $ArrayStockData['StoreName'] = $dataRow['StoreName'];
            $ArrayStockData['PlantID'] = $dataRow['PlantID'];
            $ArrayStockData['ProductionPlantName'] = $dataRow['StationName'];
            $ArrayStockData['Coldstore'] = $dataRow['UnitID'];
            $ArrayStockData['ShiftID'] = $dataRow['ShiftID'];
            $ArrayStockData['Shift'] = $dataRow['Shift'];
            $ArrayStockData['BalanceMcs'] = $BalanceMcs;
            $ArrayStockData['BalanceBags'] = $BalanceBags;

            $ArrayStockData['ProductionCode'] = $dataRow['ProdDayCode'];
            $ArrayStockData['RmLotID'] = $dataRow['RmLotID'];
            $ArrayStockData['BatchCode'] = $dataRow['ProdBatchCode'];
            $ArrayStockData['PoNumber'] = $dataRow['PoNo'];
            $ArrayStockData['Variety'] = $dataRow['Variety'];
            $ArrayStockData['VarietyID'] = $dataRow['VarietyID'];
            $ArrayStockData['PoGradeId'] = $dataRow['PoGradeId'];
            $ArrayStockData['PackingType'] = $dataRow['PackingTypeID'];
            $ArrayStockData['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayStockData['PackingStyleID'] = $dataRow['PackingStyleID'];
            $ArrayStockData['Grade'] = $dataRow['Grade'];
            $ArrayStockData['FreezingType'] = $dataRow['FreezingType'];
            $ArrayStockData['Galze'] = $dataRow['Galze'];
            $ArrayStockData['GlazePerc'] = $dataRow['GlazePerc'];
            $ArrayStockData['Soaking'] = $dataRow['SockingType'];
            // $ArrayStockData['Salt'] = $dataRow['Salt'];
            $ArrayStockData['ProductCategory'] = $dataRow['ProductCategoryID'];
            $ArrayStockData['ProductCategory'] = $dataRow['ProductCategoryID'];
            $ArrayStockData['AbStatus'] = $dataRow['AbStatus'];
            // $ArrayStockData['TubNo'] = $dataRow['TubNo'];
            $ArrayStockData['LineNumber'] = $dataRow['LineNumber'];
            $ArrayStockData['Remarks'] = $dataRow['Remarks'];
            $ArrayStockData['Mcs'] = $dataRow['AvalMcs'];
            $ArrayStockData['LooseBags'] = $dataRow['LooseBags'];
            $ArrayStockData['BalanceMcs'] = $BalanceMcs;
            $ArrayStockData['BalanceBags'] = $BalanceBags;
            $ArrayStockData['Kgs'] = sprintf('%0.2f', $TotalWeight);
            $ArrayStockData['BalanceKgs'] = sprintf('%0.2f', $BalanceKgs);
            $ArrayStockData['Status'] = $Status;


            $AryData[] = $ArrayStockData;
        }

        return $AryData;
    }

    }