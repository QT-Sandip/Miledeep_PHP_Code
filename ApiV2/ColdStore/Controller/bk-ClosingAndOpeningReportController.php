<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';

class ClosingAndOpeningReportController
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

    function getClosingAndOpeningDetails($RequestBody)
    {

        $ColdstoreID = $RequestBody['ColdstoreID'];
        $ProductionDate = $RequestBody['ProductionDate'];
        $VarietyID = $RequestBody['VarietyID'];
        $Grade = $RequestBody['Grade'];
        $PackingStyle = $RequestBody['PackingStyle'];
        $SockingType = $RequestBody['Soaking'];
        $StockType = $RequestBody['StockType'];
        $TypeProduct = $RequestBody['TypeProduct'];
        $ProductCategory = $RequestBody['ProductCategory'];
        $PackingType = $RequestBody['PackingType'];
        $FreezingType = $RequestBody['FreezingType'];
        $Species = $RequestBody['Species'];
        $AbStatus = $RequestBody['AbStatus'];

        $Query = '';
        // $DateFilter="CPID.ProductionDate='$ProductionDate'";  
        if ($StockType == 'ClosingDate') {

            $DateFilter = "CPID.ProductionDate<='$ProductionDate'";
        } else {
            $DateFilter = "CPID.ProductionDate<='$ProductionDate'";
        }
        if ($ColdstoreID != '') {
            $Query .= "AND CPID.`UnitID`='$ColdstoreID'";
        }
        if ($VarietyID != '') {
            $Query .= "AND PIVL.ShortName='$VarietyID'";
        }
        if ($Grade != '') {
            $Query .= "AND CPID.`Grade`='$Grade'";
        }
        if ($PackingStyle != '') {
            $Query .= "AND CPID.`PackingStyleID`='$PackingStyle'";
        }
        if ($SockingType != '') {
            $Query .= " AND CPID.SockingType='$SockingType'";
        }
        if ($TypeProduct != '') {
            $Query .= " AND PIVL.TypeProduct='$TypeProduct'";
        }
        if ($ProductCategory != '') {
            $Query .= " AND CPID.ProductCategoryID='$ProductCategory'";
        }
        if ($PackingType != '') {
            $Query .= " AND CPID.PackingTypeID='$PackingType'";
        }
        if ($FreezingType != '') {
            $Query .= " AND CPID.FreezingType='$FreezingType'";
        }
        if ($Species != '') {
            $Query .= " AND PIVL.Species = '$Species'";
        }
        if ($AbStatus != '') {
            $Query .= " AND CPID.AbStatus='$AbStatus'";
        }

        $sqlFields = "Species,group_ConCat(DISTINCT StockID)as StockIDs ,StationName,PIVL.ShortName as Variety,PIVL.TypeProduct, 
        CPID.SockingType, CPS.PackingStyle,CPS.TotalSlabs as PckTotalSlabs,
        CPID.Grade,SUM(ActualMcs) AS Mcs, SUM(ActualLooseBags) AS LooseBags,
        SUM(ActualKgs) AS Kgs,SlabWeight,MFactor,TotalSlabs, CPID.VarietyID,CPID.PackingStyleID,PIVL.Glaze,
        SUM(ActualMcs*TotalSlabs) AS McsSlabls, CPID.ProductCategoryID,CPID.PackingTypeID,CPID.FreezingType";

        $sqlSlabs = "SELECT $sqlFields FROM CsStockData AS CPID 
        LEFT JOIN AdminPackingStyles AS CPS ON CPS.PCKID=CPID.PackingStyleID 
        LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID  
        LEFT JOIN ScrmProductList AS CPL ON CPID.ProductionID=CPL.ProductID
        LEFT JOIN RmProcessingStations AS RPS ON CPID.PlantID=RPS.ProcessingStationID
        WHERE $DateFilter $Query   
        GROUP BY PIVL.ShortName, CPID.PackingStyleID, PIVL.Treatment,PIVL.TypeProduct,CPID.Grade,PIVL.Glaze,Species";


        $stmtSlabs = $this->conn->prepare($sqlSlabs);
        $stmtSlabs->execute();
        $ArrayVarietyData = [];
        $SNo = 0;
        $TotalStockMcs = 0;
        $TotalStockSlabs = 0;

        while ($rowSlabs = $stmtSlabs->fetch(PDO::FETCH_ASSOC)) {

            $StockIDs = $rowSlabs['StockIDs'];

            $arrayStockOutData = $this->getOutStock($ProductionDate, $StockIDs, $RequestBody);

            $StockOutMcs = isset($arrayStockOutData['OutMcs']) ? $arrayStockOutData['OutMcs'] : 0;// 4403
            $StockOutLooseBags = isset($arrayStockOutData['OutLooseBags']) ? $arrayStockOutData['OutLooseBags'] : 0;// 519

            $McsSlabls = $rowSlabs['McsSlabls'];//3944
            $LooseBags = $rowSlabs['LooseBags'];//0
            $PckTotalSlabs = $rowSlabs['PckTotalSlabs'];//1

            $StockOutMcsSlabls = $StockOutMcs * $PckTotalSlabs;// 2379 * 6 = 14274//4403
            $TotalOpeningSlabs = $McsSlabls + $LooseBags;//16944 + 19 = 16963//3944
            $TotalOutSlabs = $StockOutMcsSlabls + $StockOutLooseBags;// 14274 + 6 = 14280//4922

            $TotalKgs = $rowSlabs['Kgs'];
            $TotalOutKgs = $arrayStockOutData['OutKgs'];
            $BalanceKgs = round($TotalKgs, 2) - round($TotalOutKgs, 2);
            /* if($SNo == 41){
                echo '<pre>';print_r($arrayStockOutData);die;
            } */
//4403
            $TotalOpeningMcs = floor($TotalOpeningSlabs / $PckTotalSlabs); //16963 / 6 = 2827//3944
            $TotalOutMcs = floor($TotalOutSlabs / $PckTotalSlabs);// 14280/6 = 2380//4922

            $openingBalMcs = $TotalOpeningMcs - $TotalOutMcs; // 2827 - 2380 = 447
            $openingBalSlabs = $TotalOpeningSlabs - $TotalOutSlabs; //16963 - 14280 = 2683

            $SockingType = $rowSlabs['SockingType'];

            $Grade = $rowSlabs['Grade'];
            $TypeProduct = $rowSlabs['TypeProduct'];

            $ArrayList['SNo'] = ++$SNo;
            $ArrayList['Plant'] = $rowSlabs['StationName'];
            $ArrayList['Variety'] = $rowSlabs['Variety'];
            $ArrayList['Species'] = $rowSlabs['Species'];
            $ArrayList['SockingType'] = $SockingType;
            $ArrayList['PackingStyle'] = $rowSlabs['PackingStyle'];
            $ArrayList['Grade'] = $Grade;
            $ArrayList['TypeProduct'] = $rowSlabs['TypeProduct'];
            $ArrayList['Glaze'] = $rowSlabs['Glaze'];
            $ArrayList['Slabs'] = $openingBalSlabs > 0 ? $openingBalSlabs : 0;
            $ArrayList['Mcs'] = $openingBalMcs > 0 ? $openingBalMcs : 0;
            // $ArrayList['Kgs'] = round(($openingBalMcs * $rowSlabs['TotalSlabs'] * $rowSlabs['SlabWeight'] * $rowSlabs['MFactor']), 2);
            $ArrayList['Kgs'] = $BalanceKgs> 0 ? round(($BalanceKgs), 2) : 0 ;
            // 447 * 6 * 3.800 * 0.454 = 4626.9864

            $ArrayList['ProductCategory'] = $rowSlabs['ProductCategoryID'];
            $ArrayList['FreezingType'] = $rowSlabs['FreezingType'];
            $ArrayList['PackingType'] = $rowSlabs['PackingTypeID'];
            $ArrayList['StockOutData'] = $arrayStockOutData;

            $TotalStockMcs += $ArrayList['Mcs'];
            $TotalStockSlabs += $ArrayList['Slabs'];
            $ArrayVarietyData[] = $ArrayList;
        }
        $ArrayTotals['TotalStockMcs'] = $TotalStockMcs;
        $ArrayTotals['TotalStockSlabs'] = $TotalStockSlabs;
        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['Totals'] = $ArrayTotals;
        $data['Data'] = $ArrayVarietyData;
        $data['Query'] = $sqlSlabs;

        return $data;
    }


    function getOutStock($ProductionDate, $StockIds, $RequestBody)
    {
        $StockType = $RequestBody['StockType'];
        if ($StockType == 'ClosingDate') {

            $DateFilter = "OutDate <='$ProductionDate'";
        } else {
            $DateFilter = "OutDate <'$ProductionDate'";
        }
        $sqlOutMcs = "SELECT sum(OutMcs) as  OutMcs,sum(OutLooseBags) as OutLooseBags,SUM(ActualOutKgs) AS OutKgs  from CsStockOutDetails WHERE StockID in($StockIds) 
        and $DateFilter";
        $stmtOutMcs = $this->conn->prepare($sqlOutMcs);
        $stmtOutMcs->execute();
        $rowOutMcs = $stmtOutMcs->fetch(PDO::FETCH_ASSOC);
        $rowOutMcs['Query'] = $sqlOutMcs;

        return $rowOutMcs;
    }
}
