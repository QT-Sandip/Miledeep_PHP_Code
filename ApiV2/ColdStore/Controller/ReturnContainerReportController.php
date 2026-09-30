<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
//include_once 'Controller/UtilController.php';
include_once '../ColdStore/Controller/UtilController.php';

class ReturnContainerReportController
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

        $Query = " where ProductionTypeID = 'Return Container' ";

        //echo "<pre>"; print_r($RequestBody); exit;

        if( isset( $RequestBody['FromDate']) && isset($RequestBody['ToDate']) ){
            //$Query .= " AND SFD.emptyReturn Between '".$RequestBody['FromDate']."' AND '".$RequestBody['ToDate']."' ";
        }

        if( isset( $RequestBody['productionPlant'] ) && $RequestBody['productionPlant'] ){
            $Query .= " AND CSD.PlantID = '".$RequestBody['productionPlant']."' ";
        }

        if( isset( $RequestBody['Grade'] ) && $RequestBody['Grade'] ){
            $Query .= " AND CSD.`Grade` = '".$RequestBody['Grade']."' ";
        }

        if( isset( $RequestBody['PackingStyle'] ) && $RequestBody['PackingStyle'] ){
            $Query .= " AND CSD.`PackingStyleID` = '".$RequestBody['PackingStyle']."' ";
        }

        
        $dataRow = null;
        
        /*$sqlFields = " CSD.UnitID,StockID,`AvalMcs`, `LooseBags`, `PackingTypeID`, CSD.`Grade`,   `PoGradeId`, CSD.`FreezingType`, CSD.`VarietyID`, `Galze`, 
        `SockingType`, `Salt`, CSD.`ProductionDate`,  CSD.`PoID`, `RmLotID`,  `ProductionTypeID`, `ProductCategoryID`, `UnitID`, 
        `ShiftID`, `BarCodeID`, CSD.`PackingStyleID`, `StockHolderID`, CSD.`Remarks`, CSD.PlantID,
        `StockStatus`,TransactionDate, `AbStatus`, `ProdDayCode`,`ProdBatchCode`, `SoakingDate`, `TubNo`, `LineNumber`,`PoNo`,AGO.Name as Shift,SPV.ShortName as Variety,
        APS.PackingStyle,TenantName,StoreName,PoNo,RPS.StationName,SPV.DeviSupc,SPV.ProductDescription,SPD.PoDate,SPD.PoNo,SVL.DeviSUPC,SFD.emptyReturn ";

        $sqlQuery = "SELECT $sqlFields  FROM `CsStockData` as CSD 
        left join ScrmPoData as SPD on SPD.PoID=CSD.PoID
        left join AdminGeneralOptions as AGO on AGO.OptID=CSD.ShiftID
        left join ScrmPIVarietyList as SPV on SPV.VarietyID=CSD.VarietyID
        left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
        left join CsTenant as CST on CST.TenantID=CSD.StockHolderID        
        left join CsStores as CSS on CSS.StoreID=CSD.UnitID
        left join scrmpivarietylist as SVL on CSD.VarietyID=SVL.VarietyID
        left join scrmisfdetails as SFD on CSD.PoID=SFD.PIID
        left join RmProcessingStations as RPS on RPS.ProcessingStationID=CSD.PlantID
        $Query
        $Limit ";*/
        $sqlQuery = "SELECT PoID,PoNo,ProductionPlantID FROM ScrmPoData as SPD 
        left join ScrmISFDetails  ISF on ISF.PIID=SPD.PoID
        WHERE SPD.Status in (4) AND ISF.FDAContainerStatus REGEXP 'Return Container' ";
        //echo $sqlQuery; exit;
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $Data['BarCodeID'] = $BarCodeID;
        //$Data['BalanceKgs'] = $this->BalanceKgs;
        $i=0;
        while( $dataRow = $stmt->fetch(PDO::FETCH_ASSOC) ){
            //$dataRow['PoID'] = 3774;
            $Query_breackup = " where CSD.PoID = '".$dataRow['PoID']."' ";
                $breakupData = $this->getProductionlist($Query_breackup);
                //echo "<pre>"; print_r($breakupData); exit;
            $i++;

            $ArrayStockData['Sno'] = $i;
            $ArrayStockData['StockID'] = $dataRow['StockID'];
            $ArrayStockData['TransactionDate'] = $dataRow['TransactionDate'];

            $ArrayStockData['Remarks'] = $dataRow['Remarks'];
            $ArrayStockData['Mcs'] = $dataRow['AvalMcs'];
            $ArrayStockData['LooseBags'] = $dataRow['LooseBags'];
            $ArrayStockData['UnitID'] = $dataRow['UnitID'];
            $dataRow['Breackup'] = $breakupData;
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
        $sqlFields = " ProductionID ,ActualMcs as Mcs,SPV.DeviSupc,ProductDescription,ActualLooseBags as Bags,ShortName,CSD.Grade,PackingTypeID,APS.PackingStyle,
        (ActualMcs*TotalSlabs*SlabWeight*MFactor) as Kgs, (ActualLooseBags*SlabWeight*MFactor) as LKgs,LineNumber,ProdBatchCode,AvalMcs,LooseBags,
        ActualMcs,ActualLooseBags";



        $sqlQuery = "SELECT $sqlFields FROM `CsDailyProductionData` as CSD
        left join ScrmPIVarietyList as SPV on SPV.VarietyID=CSD.VarietyID
        left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID
        $Query ";



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
            $aryBalance = $this->getBalanceKgs($TotalWeight, $dataRow['ProductionID'], $dataRow);
            $BalanceMcs = $aryBalance['BalanceMcs'];
            $BalanceBags = $aryBalance['BalanceLooseBags'];
            $BalanceKgs = $aryBalance['BalanceWeight'];
            if ($BalanceKgs <= 0) {
                $Status = 1;
            } else {
                $Status = 0;
            }


            // $ArrayDailyProductionData['SNo'] = ++$i;
            /*   $ArrayDailyProductionData['ProductionID'] = $dataRow['ProductionID'];
            $ArrayDailyProductionData['Variety'] = $dataRow['ShortName'];
            $ArrayDailyProductionData['Grade'] = $dataRow['Grade'];
            $ArrayDailyProductionData['PackingType'] = $dataRow['PackingTypeID'];
            $ArrayDailyProductionData['Mcs'] = $dataRow['Mcs'];
            $ArrayDailyProductionData['Bags'] = $dataRow['Bags'];
            $ArrayDailyProductionData['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayDailyProductionData['ProductDescription'] = $dataRow['ProductDescription'];
            $ArrayDailyProductionData['OurSupc'] = $dataRow['DeviSupc'];
            $ArrayDailyProductionData['LineNumber'] = $dataRow['LineNumber'];
            $ArrayDailyProductionData['BatchCode'] = $dataRow['ProdBatchCode'];
            $ArrayDailyProductionData['BalanceMcs'] = $BalanceMcs;
            $ArrayDailyProductionData['BalanceBags'] = $BalanceBags;
            $ArrayDailyProductionData['Kgs'] = sprintf('%0.2f', $TotalWeight);
            $ArrayDailyProductionData['BalanceKgs'] = sprintf('%0.2f', $BalanceKgs);
            $ArrayDailyProductionData['Status'] = $Status;
*/
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

    public function getBalanceKgs($TotalWeight, $ProductionID, $proddataRow)
    {
        $sqlFields = "sum((ActualMcs*TotalSlabs*SlabWeight*MFactor)) as Kgs,
        sum((ActualLooseBags*SlabWeight*MFactor)) as LKgs, 
        sum(ActualMcs) as ActualMcs,
        sum(ActualLooseBags) as ActualLooseBags";

        $sqlQuery = "SELECT $sqlFields  
         FROM `CsStockData` as CSD 
        left join AdminPackingStyles as APS on APS.PCKID=CSD.PackingStyleID where ProductionID='$ProductionID' ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();


        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalWeight;
        $TotalWeight = round($TotalWeight - ($dataRow['Kgs'] + $dataRow['LKgs']), 2);
        $ArrayBalance['BalanceMcs'] = $proddataRow['ActualMcs'] - $dataRow['ActualMcs'];
        $ArrayBalance['BalanceLooseBags'] = $proddataRow['ActualLooseBags'] - $dataRow['ActualLooseBags'];
        $ArrayBalance['BalanceWeight'] = $TotalWeight;

        return  $ArrayBalance;
    }
    }