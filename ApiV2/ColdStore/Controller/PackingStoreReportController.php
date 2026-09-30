<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';

class PackingStoreReportController

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

    function getPackingStoreDetails($RequestBody)
    {
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $ProductionPlant = $RequestBody['ProductionPlant'];
        $ColdStore = $RequestBody['ColdStore'];
        $drpGroup = $RequestBody['drpGroup'];

        $Query = '';
        if ($ProductionPlant != "") {
            $Query .= " AND CPSD.PlantID = '$ProductionPlant'";
        }
        if ($ColdStore != "") {
            $Query .= " AND CPSD.UnitID = '$ColdStore'";
        }
        if ($drpGroup != "") {
            $Query .= " AND CPSD.Group = '$drpGroup'";
        }
        $mcsWhere = " AND PlantID = CPSD.PlantID AND UnitID = CPSD.UnitID AND `Group`= CPSD.`Group` AND Brand=CPSD.Brand  AND Variety=CPSD.Variety AND PackingStyleID=CPSD.PackingStyleID AND PackingTypeID=CPSD.PackingTypeID ";
        $PouchesWhere = " AND PlantID = CPSD.PlantID AND UnitID = CPSD.UnitID AND `Group`= CPSD.`Group` AND Brand=CPSD.Brand  AND Variety=CPSD.Variety AND SlabWeight=CPSD.SlabWeight AND PackingTypeID=CPSD.PackingTypeID  ";
        $StickersWhere = " AND PlantID = CPSD.PlantID AND UnitID = CPSD.UnitID AND `Group`= CPSD.`Group` AND Brand=CPSD.Brand AND PackingStyleID=CPSD.PackingStyleID AND PackingTypeID=CPSD.PackingTypeID AND abs(Grade) = abs(CPSD.Grade) ";
        $TapRollsWhere = " AND PlantID = CPSD.PlantID AND UnitID = CPSD.UnitID AND `Group`= CPSD.`Group` AND Brand=CPSD.Brand AND Colour=CPSD.Colour ";
        $SpacersWhere = " AND PlantID = CPSD.PlantID AND UnitID = CPSD.UnitID  AND `Group`= CPSD.`Group`  AND Brand=CPSD.Brand  AND PackingTypeID=CPSD.PackingTypeID ";
        $CountSlipsWhere = " AND PlantID = CPSD.PlantID AND UnitID = CPSD.UnitID AND `Group`= CPSD.`Group`  AND Brand=CPSD.Brand  AND PackingTypeID=CPSD.PackingTypeID  AND abs(Grade) = abs(CPSD.Grade)  ";

        //$DataFilters = " BETWEEN '$FromDate' AND '$ToDate'";
        $DataFilters = " <= '$ToDate'";

        $sqlQuery = "SELECT -- MAX(`TransactionDate`) TransactionDate,`BillNo`,SPD.PoNo,
                        BrandName,`Sizes`,Dimention,`Colour`,`Group`,`PackingTypeID`,
                    CASE WHEN `Group` = 'Pouches' THEN CPSD.SlabWeight ELSE APS.PackingStyle END AS PackingStyle
                    ,CPSD.Variety,
                    -- OpeningBal starts
                    CASE 
                    WHEN `Group` = 'Mcs' THEN
                        (SELECT IFNULL(SUM(Mcs - (DummyStock)),0) FROM CsPackingStoreData WHERE TransactionDate < '$ToDate'  $mcsWhere)
                    WHEN `Group` = 'Pouches' THEN
                        (SELECT IFNULL(SUM(Pouches- (DummyStock)),0) FROM CsPackingStoreData WHERE TransactionDate < '$ToDate' $PouchesWhere)
                    WHEN `Group` = 'Spacers' THEN
                        (SELECT IFNULL(SUM(`Spacers`),0) FROM CsPackingStoreData WHERE TransactionDate < '$ToDate' $SpacersWhere)
                    WHEN `Group` = 'Stickers' THEN
                        (SELECT IFNULL(SUM(`Count`),0) FROM CsPackingStoreData WHERE TransactionDate < '$ToDate' $StickersWhere)
                    WHEN `Group` = 'Tape Rolls' THEN
                        (SELECT IFNULL(SUM(`Count`),0) FROM CsPackingStoreData WHERE TransactionDate < '$ToDate' $TapRollsWhere)
                    WHEN `Group` ='Count Slips' THEN
                        (SELECT IFNULL(SUM(`Count`),0) FROM CsPackingStoreData WHERE TransactionDate < '$ToDate' $CountSlipsWhere)
                        
                    END AS
                    OpeningBal,
                    -- RecievedQty starts
                    CASE 
                    WHEN `Group` = 'Mcs' THEN
                        (SELECT IFNULL(SUM(Mcs-`DummyStock`),0) FROM CsPackingStoreData WHERE TransactionDate = '$ToDate' $mcsWhere)
                    WHEN `Group` = 'Pouches' THEN
                        (SELECT IFNULL(SUM(Pouches-`DummyStock`),0) FROM CsPackingStoreData WHERE TransactionDate = '$ToDate' $PouchesWhere)
                    WHEN `Group` = 'Spacers' THEN
                        (SELECT IFNULL(SUM(`Spacers`),0) FROM CsPackingStoreData WHERE TransactionDate = '$ToDate' $SpacersWhere)
                    WHEN `Group` = 'Stickers' THEN
                        (SELECT IFNULL(SUM(`Count`),0) FROM CsPackingStoreData WHERE TransactionDate = '$ToDate' $StickersWhere)
                    WHEN `Group` = 'Tape Rolls' THEN
                        (SELECT IFNULL(SUM(`Count`),0) FROM CsPackingStoreData WHERE TransactionDate = '$ToDate' $TapRollsWhere)
                    WHEN `Group` = 'Count Slips' THEN
                        (SELECT IFNULL(SUM(`Count`),0) FROM CsPackingStoreData WHERE TransactionDate = '$ToDate' $CountSlipsWhere)
                        
                    END AS
                    RecievedQty,
                    -- Utilized starts
                    CASE 
                    WHEN `Group` = 'Mcs' THEN
                        (SELECT IFNULL(SUM(`UtilizedMcs`),0) FROM CsPackingStoreData WHERE TransactionDate <= '$ToDate' $mcsWhere)
                    WHEN `Group` = 'Pouches' THEN
                        (SELECT IFNULL(SUM(`UtilizedPouches`),0) FROM CsPackingStoreData WHERE TransactionDate <= '$ToDate' $PouchesWhere)
                    WHEN `Group` = 'Spacers' THEN
                        (SELECT IFNULL(SUM(`UtilizedSpacers`),0) FROM CsPackingStoreData WHERE TransactionDate <= '$ToDate' $SpacersWhere)
                    WHEN `Group` = 'Stickers' THEN
                        (SELECT IFNULL(SUM(`UtilizedCountVal`),0) FROM CsPackingStoreData WHERE TransactionDate <= '$ToDate' $StickersWhere)
                    WHEN `Group` = 'Tape Rolls' THEN
                        (SELECT IFNULL(SUM(`UtilizedCountVal`),0) FROM CsPackingStoreData WHERE TransactionDate <= '$ToDate' $TapRollsWhere)
                    WHEN `Group` = 'Count Slips' THEN
                        (SELECT IFNULL(SUM(`UtilizedCountVal`),0) FROM CsPackingStoreData WHERE TransactionDate <= '$ToDate' $CountSlipsWhere)
                        
                    END AS
                    UtilizedQty,
                    -- Damazed starts 
                    CASE 
                    WHEN `Group` IN ('Mcs','Pouches') THEN
                        (SELECT IFNULL(SUM(`DamagedStock`),0) FROM CsPackingStoreData WHERE TransactionDate <= '$ToDate' $mcsWhere AND SlabWeight = CPSD.SlabWeight)
                    ELSE 0
                    END AS Damazed,
                    -- Available Qty Starts
                    CASE 
                    WHEN `Group` = 'Mcs' THEN
                        (SELECT IFNULL(SUM(Mcs - (`DummyStock` + `UtilizedMcs`+`DamagedStock`)),0) FROM CsPackingStoreData WHERE TransactionDate <= '$ToDate'  $mcsWhere)
                    WHEN `Group` = 'Pouches' THEN
                        (SELECT IFNULL(SUM(Pouches - (`DummyStock` + `UtilizedPouches`+`DamagedStock`)),0) FROM CsPackingStoreData WHERE TransactionDate <= '$ToDate' $PouchesWhere)
                    WHEN `Group` = 'Spacers' THEN
                        (SELECT IFNULL(SUM(`Spacers` - UtilizedSpacers),0) FROM CsPackingStoreData WHERE TransactionDate <= '$ToDate' $SpacersWhere)
                    WHEN `Group` = 'Stickers' THEN
                        (SELECT IFNULL(SUM(`Count` - UtilizedCountVal),0) FROM CsPackingStoreData WHERE TransactionDate <= '$ToDate' $StickersWhere)
                    WHEN `Group` = 'Tape Rolls' THEN
                        (SELECT IFNULL(SUM(`Count` - UtilizedCountVal),0) FROM CsPackingStoreData WHERE TransactionDate <= '$ToDate' $TapRollsWhere)
                    WHEN `Group` = 'Count Slips' THEN
                        (SELECT IFNULL(SUM(`Count` - UtilizedCountVal),0) FROM CsPackingStoreData WHERE TransactionDate <= '$ToDate' $CountSlipsWhere)
                        
                    END AS
                    AvailableQty


                    FROM CsPackingStoreData CPSD
                    -- LEFT JOIN ScrmPoData AS SPD ON SPD.PoID=CPSD.PoNo
                    LEFT JOIN ScrmBrandsMaster SBM ON SBM.BrandID=CPSD.Brand
                    LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID=CPSD.PackingStyleID 
                    WHERE `TransactionDate` $DataFilters $Query
                    GROUP BY
                    `Group`,
                    `Brand`,

                    CASE
                        WHEN `Group` = 'Mcs'
                        THEN PackingStyleID
                        WHEN `Group` = 'Stickers'
                        THEN PackingStyleID
                        ELSE NULL
                    END,

                    CASE
                        WHEN `Group` = 'Mcs'
                        THEN Variety
                        WHEN `Group` = 'Pouches'
                        THEN Variety
                        ELSE NULL
                    END,

                    CASE
                        WHEN `Group` = 'Pouches'
                        THEN CPSD.SlabWeight
                        ELSE NULL
                    END,

                    CASE
                        WHEN `Group` = 'Stickers'
                        THEN ABS(`Grade`)
                        WHEN `Group` = 'Count Slips'
                        THEN ABS(`Grade`)
                        ELSE NULL
                    END,

                    CASE
                        WHEN `Group` = 'Tape Rolls'
                        THEN Colour
                        ELSE NULL
                    END
                    -- `Group`,`Brand`,`PackingTypeID`,`PackingStyleID`,CPSD.`SlabWeight`,`Variety`,abs(`Grade`),`Dimention`,`Colour`
                    HAVING (
                        OpeningBal != 0
                        OR RecievedQty != 0
                        OR UtilizedQty != 0
                        OR Damazed != 0
                        OR AvailableQty != 0
                    )
                    ORDER BY 
                    CASE 
                    WHEN `Group` = 'Mcs' THEN 1
                    WHEN `Group` = 'Pouches' THEN 2
                    WHEN `Group` = 'Stickers' THEN 3
                    WHEN `Group` = 'Tape Rolls' THEN 4
                    WHEN `Group` = 'Spacers' THEN 5
                    ELSE 6
                    END ";
                  //  echo '<pre>';print_r($sqlQuery);die;
        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();
        $TotalOpeningBal = $TotalRecievedQty =$TotalUtilizedQty = $TotalDamazed = $TotalAvailableQty = 0;
        $Totals = [];
        $i = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
            $ArrayPackingStore['SNO'] = ++$i;
            $ArrayPackingStore['BillNo'] = $dataRow['BillNo'];
            $ArrayPackingStore['PoNo'] = $dataRow['PoNo'] ? $dataRow['PoNo'] : 'NA';
            $ArrayPackingStore['BrandName'] = $dataRow['BrandName'];
            $ArrayPackingStore['Sizes'] = $dataRow['Sizes'];
            $ArrayPackingStore['Dimention'] = $dataRow['Dimention'];
            $ArrayPackingStore['Grade'] = $dataRow['Grade'];
            $ArrayPackingStore['Colour'] = $dataRow['Colour'];
            $ArrayPackingStore['TransactionDate'] = $dataRow['TransactionDate'] ? date('d-m-Y', strtotime($dataRow['TransactionDate'])) : 'NA';
            $ArrayPackingStore['Group'] = $dataRow['Group'];
            $ArrayPackingStore['PackingTypeID'] = $dataRow['PackingTypeID'];
            $ArrayPackingStore['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayPackingStore['Variety'] = $dataRow['Variety'];
            $ArrayPackingStore['OpeningBal'] = $dataRow['OpeningBal'];
            $ArrayPackingStore['RecievedQty'] = $dataRow['RecievedQty'];
            $ArrayPackingStore['UtilizedQty'] = $dataRow['UtilizedQty'];
            $ArrayPackingStore['Damazed'] = $dataRow['Damazed'];
            $ArrayPackingStore['AvailableQty'] = $dataRow['AvailableQty'];

            $TotalOpeningBal += $dataRow['OpeningBal'];
            $TotalRecievedQty += $dataRow['RecievedQty'];
            $TotalUtilizedQty += $dataRow['UtilizedQty'];
            $TotalDamazed += $dataRow['Damazed'];
            $TotalAvailableQty += $dataRow['AvailableQty'];

            $ArrayPackingStoreData[] =  $ArrayPackingStore;

            $Totals = [
                "TotalOpeningBal" => $TotalOpeningBal,
                "TotalRecievedQty" => $TotalRecievedQty,
                "TotalUtilizedQty" => $TotalUtilizedQty,
                "TotalDamazed" => $TotalDamazed,
                "TotalAvailableQty" => $TotalAvailableQty,
            ];
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['Data'] = $ArrayPackingStoreData;
        $data['Totals'] = $Totals;

        return $data;
    }
}
