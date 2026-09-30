<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';

class StockSummaryController
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

    public function getStockDetails($RequestBody)
    {
        $Data['Error'] = null;
        $Data['Msg'] = "Success";

        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $PlantID = $RequestBody['PlantID'];
        $ColdStoreID = $RequestBody['ColdStoreID'];
        $PDCategory = $RequestBody['PDCategory'];

        $Query = '';
        if ($PlantID != '') {
            $Query .= " AND CSD.PlantID = '$PlantID'";
        }
        if ($ColdStoreID != '') {
            $Query .= " AND CSD.UnitID = '$ColdStoreID'";
        }
        if($PDCategory != ''){
            $Query .= " AND CSD.ProductCategoryID = '$PDCategory'";
        }
        $DateFilters = " BETWEEN '$FromDate' AND '$ToDate'";

        $sqlProductionType = "SELECT TransCategory  FROM CsTransactionTypes WHERE IsReturnable=1 AND TransTypeID != 9";
        $stmtProductionType = $this->conn->prepare($sqlProductionType);
        $stmtProductionType->execute();

        $ArrayInWardData = [];
        $TotalInwardKGs = '';
        while ($rowProductionType = $stmtProductionType->fetch(PDO::FETCH_ASSOC)) {
            $ProductionType = $rowProductionType['TransCategory'];

            $sqlInwardFields = "SUM(ActualMcs) AS ActualMcs, SUM(ActualLooseBags) AS ActualLooseBags, SUM(ActualKgs) AS ActualKgs,
            SUM(ActualMcs*TotalSlabs*SlabWeight*MFactor) AS KGs, SUM(ActualLooseBags*SlabWeight*MFactor) AS LKGs";

            $sqlInwardSelect = "SELECT $sqlInwardFields FROM CsStockData AS CSD
            LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID = CSD.PackingStyleID 
            WHERE ProductionDate $DateFilters AND CSD.ProductionTypeID='$ProductionType' $Query";
            $stmtInwardSelect = $this->conn->prepare($sqlInwardSelect);
            $stmtInwardSelect->execute();
            $dataInwardRow = $stmtInwardSelect->fetch(PDO::FETCH_ASSOC);
            $KGs = $dataInwardRow['KGs'];
            $LKGs = $dataInwardRow['LKGs'];
            $InwardKGs =  $dataInwardRow['ActualKgs'];

            //$ArrayInwardList['Group'] = ($ProductionType == 'Repacking Out') ? $ProductionType.' (In)' : $ProductionType;
            $ArrayInwardList['Group'] = str_replace('Repacking Out', 'Repacking In', $ProductionType);
            $ArrayInwardList['InwardKGs'] = $InwardKGs;
            $ArrayInwardList['ActualMcs'] = $dataInwardRow['ActualMcs'];
            $ArrayInwardList['ActualLooseBags'] = $dataInwardRow['ActualLooseBags'];
            $ArrayInwardList['ActualKgs'] = $dataInwardRow['ActualKgs'];

            if ($InwardKGs > 0) {
                $TotalInwardKGs += $InwardKGs;
                $ArrayInWardData[] = $ArrayInwardList;
            }
        }

        $Data['InwardData'] = $ArrayInWardData;
        $Data['TotalInwardKGs'] = $TotalInwardKGs;

        $sqlOutType = "SELECT TransCategory  FROM CsTransactionTypes WHERE TransType=2";
        $stmtOutType = $this->conn->prepare($sqlOutType);
        $stmtOutType->execute();

        $ArrayOutwardData = [];
        $TotalOutwardKGs = '';
        while ($rowOutType = $stmtOutType->fetch(PDO::FETCH_ASSOC)) {
            $OutType = $rowOutType['TransCategory'];

            $sqlOutwardFields = "SUM(OutMcs) AS OutMcs, SUM(OutLooseBags) AS OutLooseBags, SUM(ActualOutKgs) AS ActualOutKgs,
            SUM(OutMcs*TotalSlabs*SlabWeight*MFactor) AS KGs, SUM(OutLooseBags*SlabWeight*MFactor) AS LKGs";

            $sqlOutwardSelect = "SELECT $sqlOutwardFields FROM CsStockOutDetails AS CSOD
            LEFT JOIN CsStockData AS CSD ON CSOD.StockID=CSD.StockID 
            LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID = CSD.PackingStyleID 
            WHERE OutDate $DateFilters AND CSOD.OutType='$OutType' $Query";


            $stmtOutwardSelect = $this->conn->prepare($sqlOutwardSelect);
            $stmtOutwardSelect->execute();

            $dataOutwardRow = $stmtOutwardSelect->fetch(PDO::FETCH_ASSOC);

            $KGs = $dataOutwardRow['KGs'];
            $LKGs = $dataOutwardRow['LKGs'];
            $OutwardKGs = $dataOutwardRow['ActualOutKgs'];

            //$ArrayOutwardList['Group'] = ($OutType == 'Repacking Out') ? $OutType.' (Out)' : $OutType;
            $ArrayOutwardList['Group'] = $OutType;
            $ArrayOutwardList['OutwardKGs'] = $OutwardKGs;
            $ArrayOutwardList['OutMcs'] = $dataOutwardRow['OutMcs'];
            $ArrayOutwardList['OutLooseBags'] = $dataOutwardRow['OutLooseBags'];
            $ArrayOutwardList['ActualOutKgs'] = $dataOutwardRow['ActualOutKgs'];

            if ($OutwardKGs > 0) {
                $TotalOutwardKGs += $OutwardKGs;
                $ArrayOutwardData[] = $ArrayOutwardList;
            }
        }

        $Data['OutwardDate'] = $ArrayOutwardData;
        $Data['TotalOutwardKGs'] = $TotalOutwardKGs; //

        $OpeningData = $this->openingBalance($FromDate, $Query);
        $ClosinggData = $this->closingBalance($ToDate, $Query);

        $Data['OpeningData'] = $OpeningData;
        $Data['ClosinggData'] = $ClosinggData;

        return $Data;
    }

    function openingBalance($FromDate, $Query)
    {

        $sqlOpeningFields = "SUM(ActualMcs) AS ActualMcs, SUM(ActualLooseBags) AS ActualLooseBags, SUM(ActualKgs) AS ActualKgs,
        SUM(ActualMcs*TotalSlabs*SlabWeight*MFactor) AS KGs, SUM(ActualLooseBags*SlabWeight*MFactor) AS LKGs";
        $sqlOpeningSelect = "SELECT $sqlOpeningFields FROM CsStockData AS CSD
        LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID = CSD.PackingStyleID 
        WHERE ProductionDate < '$FromDate' $Query";
        $stmtOpeningSelect = $this->conn->prepare($sqlOpeningSelect);
        $stmtOpeningSelect->execute();

        $dataOpeningBalance = $stmtOpeningSelect->fetch(PDO::FETCH_ASSOC);
        $ActualMcs = $dataOpeningBalance['ActualMcs'];
        $ActualLooseBags = $dataOpeningBalance['ActualLooseBags'];
        $ActualKgs = $dataOpeningBalance['ActualKgs'];
        $KGs = $dataOpeningBalance['KGs'];
        $LKGs = $dataOpeningBalance['LKGs'];
        $InwardKGs = $KGs + $LKGs;

        $sqlOutwardFields = "SUM(OutMcs) AS OutMcs, SUM(OutLooseBags) AS OutLooseBags, SUM(ActualOutKgs) AS ActualOutKgs,
        SUM(OutMcs*TotalSlabs*SlabWeight*MFactor) AS KGs, SUM(OutLooseBags*SlabWeight*MFactor) AS LKGs";
        $sqlOutwardSelect = "SELECT $sqlOutwardFields FROM CsStockOutDetails AS CSOD
        LEFT JOIN CsStockData AS CSD ON CSOD.StockID=CSD.StockID 
        LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID = CSD.PackingStyleID 
        WHERE OutDate < '$FromDate' $Query";
        
        $stmtOpeningSelect = $this->conn->prepare($sqlOutwardSelect);
        $stmtOpeningSelect->execute();

        $dataOutwardList = $stmtOpeningSelect->fetch(PDO::FETCH_ASSOC);
        $OutMcs = $dataOutwardList['OutMcs'];
        $OutLooseBags = $dataOutwardList['OutLooseBags'];
        $ActualOutKgs = $dataOutwardList['ActualOutKgs'];
        $KGs = $dataOutwardList['KGs'];
        $LKGs = $dataOutwardList['LKGs'];
        $OutwardKGs = $KGs + $LKGs;

        $ArrayData['OpeningMcs'] = $ActualMcs - $OutMcs;
        $ArrayData['OpeningLooseBags'] = $ActualLooseBags - $OutLooseBags;
        $ArrayData['OpeningActualKGs'] = $ActualKgs - $ActualOutKgs;
        $ArrayData['OpeningKGs'] = $InwardKGs - $OutwardKGs;

        return $ArrayData;
    }

    function closingBalance($ToDate, $Query)
    {

        $sqlOpeningFields = "SUM(ActualMcs) AS ActualMcs, SUM(ActualLooseBags) AS ActualLooseBags, SUM(ActualKgs) AS ActualKgs,
        SUM(ActualMcs*TotalSlabs*SlabWeight*MFactor) AS KGs, SUM(ActualLooseBags*SlabWeight*MFactor) AS LKGs";
        $sqlOpeningSelect = "SELECT $sqlOpeningFields FROM CsStockData AS CSD
        LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID = CSD.PackingStyleID 
        WHERE ProductionDate <= '$ToDate' $Query";
        $stmtOpeningSelect = $this->conn->prepare($sqlOpeningSelect);
        $stmtOpeningSelect->execute();
        $dataOpeningBalance = $stmtOpeningSelect->fetch(PDO::FETCH_ASSOC);
        $ActualMcs = $dataOpeningBalance['ActualMcs'];
        $ActualLooseBags = $dataOpeningBalance['ActualLooseBags'];
        $ActualKgs = $dataOpeningBalance['ActualKgs'];
        $KGs = $dataOpeningBalance['KGs'];
        $LKGs = $dataOpeningBalance['LKGs'];
        $InwardKGs = $KGs + $LKGs;

        $sqlOutwardFields = "SUM(OutMcs) AS OutMcs, SUM(OutLooseBags) AS OutLooseBags, SUM(ActualOutKgs) AS ActualOutKgs,
        SUM(OutMcs*TotalSlabs*SlabWeight*MFactor) AS KGs, SUM(OutLooseBags*SlabWeight*MFactor) AS LKGs";
        $sqlOutwardSelect = "SELECT $sqlOutwardFields FROM CsStockOutDetails AS CSOD
        LEFT JOIN CsStockData AS CSD ON CSOD.StockID=CSD.StockID 
        LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID = CSD.PackingStyleID 
        WHERE OutDate <= '$ToDate' $Query";
        $stmtOpeningSelect = $this->conn->prepare($sqlOutwardSelect);
        $stmtOpeningSelect->execute();
        $dataOutwardList = $stmtOpeningSelect->fetch(PDO::FETCH_ASSOC);
        $OutMcs = $dataOutwardList['OutMcs'];
        $OutLooseBags = $dataOutwardList['OutLooseBags'];
        $ActualOutKgs = $dataOutwardList['ActualOutKgs'];
        $KGs = $dataOutwardList['KGs'];
        $LKGs = $dataOutwardList['LKGs'];
        $OutwardKGs = $KGs + $LKGs;

        $ArrayData['ClosingMcs'] = $ActualMcs - $OutMcs;
        $ArrayData['ClosingLooseBags'] = $ActualLooseBags - $OutLooseBags;
        $ArrayData['ClosingActualKGs'] = $ActualKgs - $ActualOutKgs;
        $ArrayData['ClosingKGs'] = $InwardKGs - $OutwardKGs;

        return $ArrayData;
    }
}
