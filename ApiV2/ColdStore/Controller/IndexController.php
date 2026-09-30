<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';

class IndexController
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

    function getIndexDetails($RequestBody)
    {
        $UnitID = $RequestBody['UnitID'];
        $Month = $RequestBody['Month'];
        $Year = $RequestBody['Year'];

        $Month = sprintf('%02d', $Month);
        $DateStart = $Year . '-' . $Month . '-01';
        $DateEnd = $Year . '-' . $Month . '-31';
        $DateFilter = "Between '$DateStart' and '$DateEnd'";

        $Query = '';

        if ($UnitID != '') {
            $Query .= " AND CSD.UnitID='$UnitID'";
        }

        $sqlSelect = "SELECT GROUP_CONCAT(StockID) as ActiveStockID FROM `CsStockData` as CSD 
        LEFT JOIN CsBarCodes AS CBC on CSD.BarCodeID=CBC.BarCodeID 
        WHERE CBC.BarCodeStatus=1 AND (CSD.AvalMcs!=0 OR CSD.LooseBags!=0) $Query ";

        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();

        $dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC);

        $ActiveStockID = $dataRow['ActiveStockID'];


        $sqlABC = "SELECT ProductionTypeID,ProductionDate, 
        sum((AvalMcs*TotalSlabs*SlabWeight*MFactor)+(LooseBags*SlabWeight*MFactor)) AS AvalKgs, 
        (SELECT COALESCE(sum((OutMcs*TotalSlabs*SlabWeight*MFactor)+(OutLooseBags*SlabWeight*MFactor)),0) 
        FROM CsStockOutDetails AS CSOD_O 
        LEFT JOIN CsStockData AS CSD_O ON CSOD_O.StockID=CSD_O.StockID 
        LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID=CSD_O.PackingStyleID 
        WHERE CSD_O.ProductionDate=CSD.ProductionDate ) AS ProdOutKgs, 
        (SELECT COALESCE(sum(OutMcs*TotalSlabs*SlabWeight*MFactor),0) FROM CsStockOutDetails AS CSOD_O 
        LEFT JOIN CsStockData AS CSD_O ON CSOD_O.StockID=CSD_O.StockID 
        LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID=CSD_O.PackingStyleID 
        WHERE STR_TO_DATE(CSOD_O.PostedDate, '%Y-%m-%d')=CSD.ProductionDate ) AS OutKgs FROM `CsStockData` AS CSD 
        LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID=CSD.PackingStyleID 
        WHERE CSD.ProductionDate $DateFilter $Query GROUP BY ProductionDate";

        $stmtABC = $this->conn->prepare($sqlABC);
        $stmtABC->execute();

        $ArrayABCData = [];
        $TotalsABCData = [];

        while ($dataRowABC = $stmtABC->fetch(PDO::FETCH_ASSOC)) {

            $ArrayABCList['ProductionTypeID'] = $dataRowABC['ProductionTypeID'];
            $ArrayABCList['ProductionDate'] = date('d-m-Y', strtotime($dataRowABC['ProductionDate']));
            $Orders = round($dataRowABC['AvalKgs'] + $dataRowABC['ProdOutKgs'], 2);
            $Shipments = round($dataRowABC['OutKgs'], 2);

            $ArrayABCList['Orders'] = $Orders;
            $ArrayABCList['Shipments'] = $Shipments;

            $TotalsABCData['TotalOrders'] += $Orders;
            $TotalsABCData['TotalShipments'] += $Shipments;

            $ArrayABCData[] = $ArrayABCList;

            $InOutData['InOutData'] = $ArrayABCData;
            $InOutData['InOutTotals'] = $TotalsABCData;
        }
        // ********************************** Inward - Outward ***************************//

        // ********************************** Store Details ***************************//
        $sqlBlockFields = "COALESCE(BlockName,'OnFloor') as BlockName , sum(TotalSlabs*SlabWeight*AvalMcs*MFactor) as KGS, 
        sum(SlabWeight*MFactor*LooseBags) as LKGS, COALESCE(StoreCapacity,'0') as StoreCapacity, COALESCE(ColorCode,'#FF0000') as ColorCode";

        $sqlBlockWise = "SELECT $sqlBlockFields FROM `CsStockData` AS CSD 
        LEFT JOIN CsBarCodes AS CBC ON CSD.BarCodeID=CBC.BarCodeID 
        LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID=CSD.PackingStyleID 
        LEFT JOIN CsStoresRackData AS CSRD ON CSRD.BarCodeID=CBC.BarCodeID 
        LEFT JOIN CsStoresBlockData AS CSBD ON CSBD.BlockID=CSRD.BlockID 
        WHERE CSD.StockID IN ($ActiveStockID) GROUP BY CSRD.BlockID";

        $stmtBlockWise = $this->conn->prepare($sqlBlockWise);
        $stmtBlockWise->execute();

        $BlockWiseArray = [];
        $OverallTotalKGs = [];

        while ($rowBlockWise = $stmtBlockWise->fetch(PDO::FETCH_ASSOC)) {

            $TotalKGs = round(($rowBlockWise['KGS'] + $rowBlockWise['LKGS'] + 0), 2);

            $ArrayBlockWise['BlockName'] = $rowBlockWise['BlockName'];
            $ArrayBlockWise['ColorCode'] = $rowBlockWise['ColorCode'];
            $ArrayBlockWise['StoreKGs'] = round(($rowBlockWise['KGS'] + $rowBlockWise['LKGS'] + 0) , 2);
            $ArrayBlockWise['TotalKGs'] = round(($rowBlockWise['KGS'] + $rowBlockWise['LKGS'] + 0) / 1000, 2);

            $OverallTotalKGs['OverallKGs'] += $TotalKGs;

            $BlockWiseArray[] = $ArrayBlockWise;

            $StoreData['StoreData'] = $BlockWiseArray;
            $StoreData['StoreTotals'] = $OverallTotalKGs;
        }
        // ********************************** Store Details ***************************//

        // ********************************** Stock Details ***************************//

        $varietyFields = "ShortName,sum(AvalMcs) as MCS , (sum(AvalMcs * TotalSlabs * SlabWeight *MFactor)) as KGS, 
        (sum(LooseBags * SlabWeight *MFactor)) as LKGS, PackingStyleID";

        $sqlVariety = "SELECT $varietyFields FROM CsStockData AS CPID 
        LEFT JOIN CsBarCodes AS CBD ON CBD.BarCodeID=CPID.BarCodeID 
        LEFT JOIN AdminPackingStyles AS CPS ON CPID.PackingStyleID=CPS.PCKID 
        LEFT JOIN ScrmPIVarietyList AS VL ON VL.VarietyID=CPID.VarietyID 
        WHERE CPID.StockID IN ($ActiveStockID) GROUP BY ShortName";

        $stmtVariety = $this->conn->prepare($sqlVariety);
        $stmtVariety->execute();

        $VarietyArray = [];
        $OverallTotal = [];

        while ($rowVariety = $stmtVariety->fetch(PDO::FETCH_ASSOC)) {

            $TotalKGs = round($rowVariety['KGS'] + $rowVariety['LKGS']);

            $ArrayVarietyList['PackingStyleID'] = $rowVariety['PackingStyleID'];
            $ArrayVarietyList['Label'] = $rowVariety['ShortName'];
            $ArrayVarietyList['MCS'] = $rowVariety['MCS'];
            $ArrayVarietyList['TotalKGs'] = round($TotalKGs);
            $ArrayVarietyList['Color'] = sprintf("#%06x", rand(0, 16777215));
            $ArrayVarietyList['Highlight'] = '#32CD32';
            $ArrayVarietyList['CjsKGs'] = round(($TotalKGs / 1000), 2);

            $OverallTotal['OverallTotal'] += round(($TotalKGs / 1000), 2);
            $VarietyArray[] = $ArrayVarietyList;

            $StockData['StockData'] = $VarietyArray;
            $StockData['StockTotals'] = $OverallTotal;
        }
        // ********************************** Stock Details ***************************//

        // ********************************** Dummy Stock ***************************//

        $dummyFields = "CPID.Grade AS X_Axis,SUM(AvalMcs) AS MCS , (SUM(AvalMcs * TotalSlabs * SlabWeight *MFactor)) AS KGS, 
        (SUM(LooseBags * SlabWeight *MFactor)) AS LKGS, PackingStyleID";

        $sqlDummy = "SELECT $dummyFields FROM CsStockData AS CPID 
        LEFT JOIN CsBarCodes AS CBD ON CBD.BarCodeID=CPID.BarCodeID 
        LEFT JOIN AdminPackingStyles AS CPS ON CPID.PackingStyleID=CPS.PCKID 
        LEFT JOIN ScrmPIVarietyList AS VL ON VL.VarietyID=CPID.VarietyID 
        WHERE CPID.StockID IN ($ActiveStockID) AND PackingTypeID='DUMMY' AND (PoID=0 OR PoID IS NULL) 
        GROUP BY CPID.Grade ORDER BY Abs(CPID.Grade)";

        $stmtDummy = $this->conn->prepare($sqlDummy);
        $stmtDummy->execute();

        $DummyList = [];
        $DummyTotals = [];

        while ($rowDummy = $stmtDummy->fetch(PDO::FETCH_ASSOC)) {

            $KGs = $rowDummy['KGS'] + $rowDummy['LKGS'];
            $MCS = $rowDummy['MCS'];
            $ArrayDummy['PackingStyleID'] = $rowDummy['PackingStyleID'];
            $ArrayDummy['X_Axis'] = $rowDummy['X_Axis'];
            $ArrayDummy['MCS'] = $MCS;
            $ArrayDummy['KGS'] = $KGs;
          
            $DummyList[] = $ArrayDummy;

            $DummyTotals['TotalMCS'] +=$MCS;
            $DummyTotals['TotalKGs'] +=$KGs;

            $DummyData['DummyData'] = $DummyList;
            $DummyData['DummyTotals'] = $DummyTotals;
        }

        // ********************************** Dummy Stock ***************************//


        $data['Error'] = null;
        $data['Msg'] = 'Success';
        // $data['ActiveStockID'] = $ActiveStockID;
        $data['InOutDetails'] = $InOutData;
        $data['StoreDetails'] = $StoreData;
        $data['StockDetails'] = $StockData;
        $data['DummyDetails'] = $DummyData;

        return $data;
    }
}
