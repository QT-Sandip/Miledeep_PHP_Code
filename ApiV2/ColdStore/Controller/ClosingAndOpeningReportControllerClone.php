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


        $Query = '';
        // $DateFilter="CPID.ProductionDate='$ProductionDate'";  
        if ($StockType == 'ClosingDate') {

            $DateFilter = "CPID.ProductionDate<'$ProductionDate'";
        } else {
            $DateFilter = "CPID.ProductionDate='$ProductionDate'";
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

        //Opening Stock
        $sqlFields = "Species,StationName,PIVL.ShortName as Variety,PIVL.TypeProduct, CPID.SockingType, CPS.PackingStyle,
        CPID.Grade,SUM(AvalMcs) AS Mcs, SUM(LooseBags) AS LooseBags,
        SUM(AvalKgs) AS Kgs,SlabWeight,MFactor,TotalSlabs, CPID.VarietyID,CPID.PackingStyleID,PIVL.Glaze";

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
        // $errorInfo = $stmtSlabs->errorInfo();
        // var_dump($errorInfo);
        while ($rowSlabs = $stmtSlabs->fetch(PDO::FETCH_ASSOC)) {

            // $ArrayList = [];

            $SockingType = $rowSlabs['SockingType'];
            $PackingStyleID = $rowSlabs['PackingStyleID'];
            $Grade = $rowSlabs['Grade'];
            $Variety = $rowSlabs['Variety'];
            $Species = $rowSlabs['Species'];
            $Glaze = $rowSlabs['Glaze'];
            $TypeProduct = $rowSlabs['TypeProduct'];

            $Mcs = $rowSlabs['Mcs'] ? $rowSlabs['Mcs'] : 0;
            $LooseBags = $rowSlabs['LooseBags'] ? $rowSlabs['LooseBags'] : 0;
            $LooseMcs = round($LooseBags / $rowSlabs['TotalSlabs']);

            $TotalMcs = $Mcs + $LooseMcs;
            $SlabList['TotalMcs'] = $TotalMcs;
            $OpeningSlabs = $rowSlabs['Kgs'] / ($rowSlabs['SlabWeight'] * $rowSlabs['MFactor']);
            $OpeningMcs = $TotalMcs;

            $ArrayList['SNo'] = ++$SNo;
            $ArrayList['Plant'] = $rowSlabs['StationName'];
            $ArrayList['Variety'] = $rowSlabs['Variety'];
            $ArrayList['Species'] = $rowSlabs['Species'];
            $ArrayList['SockingType'] = $SockingType;
            $ArrayList['PackingStyle'] = $rowSlabs['PackingStyle'];
            $ArrayList['Grade'] = $Grade;
            $ArrayList['TypeProduct'] = $rowSlabs['TypeProduct'];
            $ArrayList['Glaze'] = $rowSlabs['Glaze'];
            // Opening Stock

            // Inward Group
            $sqlInwardGroup = "SELECT GROUP_CONCAT(CONCAT('\'', TransCategory, '\'') SEPARATOR ',') as TransCategory FROM CsTransactionTypes WHERE TransStatus=1 AND IsReturnable=1";
            $stmtInwardGroup = $this->conn->prepare($sqlInwardGroup);
            $stmtInwardGroup->execute();
            $rowInwardGroup = $stmtInwardGroup->fetch(PDO::FETCH_ASSOC);

            $TransCategory = $rowInwardGroup['TransCategory'];
            $slabFields = "SUM(ActualMcs) AS Mcs, SUM(ActualLooseBags) AS LooseBags, CPS.PackingStyle,SUM(ActualKgs) AS Kgs,
            SlabWeight,MFactor,TotalSlabs";
            $sqlInwardData = "SELECT $slabFields FROM `CsStockData` AS CPID 
                    LEFT JOIN AdminPackingStyles AS CPS ON CPS.PCKID=CPID.PackingStyleID 
                    LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID  
                    LEFT JOIN ScrmProductList AS CPL ON CPID.ProductionID=CPL.ProductID
                    WHERE PIVL.ShortName = '$Variety' AND CPID.PackingStyleID = '$PackingStyleID' 
                    AND PIVL.Treatment='$SockingType' AND CPID.Grade = '$Grade' AND ProductionTypeID in ($TransCategory) and PIVL.TypeProduct ='$TypeProduct' and 
                    PIVL.Glaze ='$Glaze'  and PIVL.Species ='$Species' and CPID.ProductionDate<'$ProductionDate' ";
            $stmtInwardData = $this->conn->prepare($sqlInwardData);
            $stmtInwardData->execute();
            // $ArrayVarietyData = [];
            $rowInwardData = $stmtInwardData->fetch(PDO::FETCH_ASSOC);

            $Mcs = $rowInwardData['Mcs'] ? $rowInwardData['Mcs'] : 0;
            $LooseBags = $rowInwardData['LooseBags'] ? $rowInwardData['LooseBags'] : 0;
            $LooseMcs = round($LooseBags / $rowInwardData['TotalSlabs']);

            $TotalMcs = $Mcs + $LooseMcs;
            $SlabList['TotalMcs'] = $TotalMcs;
            $InwardSlabs = $rowInwardData['Kgs'] / ($rowInwardData['SlabWeight'] * $rowInwardData['MFactor']);
            $InwardMcs = $TotalMcs;

            // Inward Group

            // Outward Group
            $sqlOutwardGroup = "SELECT GROUP_CONCAT(CONCAT('\'', TransCategory, '\'') SEPARATOR ',') as TransCategory FROM CsTransactionTypes WHERE TransType=2 AND TransStatus=1";
            $stmtOutwardGroup = $this->conn->prepare($sqlOutwardGroup);
            $stmtOutwardGroup->execute();
            $rowOutwardGroup = $stmtOutwardGroup->fetch(PDO::FETCH_ASSOC);
            $TransCategory = $rowOutwardGroup['TransCategory'];


            $slabFields = "SUM(OutMcs) AS Mcs, SUM(OutLooseBags) AS LooseBags, CPS.PackingStyle,SUM(OutMcs*TotalSlabs*SlabWeight*MFactor) as McsKgs,
                    SUM(OutLooseBags*SlabWeight*MFactor) as LooseKgs ,SlabWeight,MFactor,TotalSlabs";
            $sqlOutwardData = "SELECT $slabFields FROM `CsStockOutDetails` AS CSD 
            LEFT JOIN CsStockData AS CPID ON CSD.StockID=CPID.StockID 
            LEFT JOIN AdminPackingStyles AS CPS ON CPS.PCKID=CPID.PackingStyleID 
            LEFT JOIN ScrmPIVarietyList as PIVL on CPID.VarietyID=PIVL.VarietyID  
            LEFT JOIN ScrmProductList AS CPL ON CPID.ProductionID=CPL.ProductID
            WHERE PIVL.ShortName = '$Variety' AND CPID.PackingStyleID = '$PackingStyleID' 
            AND PIVL.Treatment='$SockingType' AND CPID.Grade = '$Grade' AND OutType in ($TransCategory) and PIVL.TypeProduct ='$TypeProduct' and 
            PIVL.Glaze ='$Glaze'  and PIVL.Species ='$Species' and CPID.ProductionDate<'$ProductionDate'";
            $stmtOutwardData = $this->conn->prepare($sqlOutwardData);
            $stmtOutwardData->execute();
            // $ArrayVarietyData = [];

            $rowOutwardData = $stmtOutwardData->fetch(PDO::FETCH_ASSOC);

            $Mcs = $rowOutwardData['Mcs'] ? $rowOutwardData['Mcs'] : 0;
            $McsKgs = $rowOutwardData['McsKgs'] ? $rowOutwardData['McsKgs'] : 0;
            $LooseKgs = $rowOutwardData['LooseKgs'] ? $rowOutwardData['LooseKgs'] : 0;
            $OutKgs = $McsKgs + $LooseKgs;
            $OutwardSlabs = $OutKgs / ($rowOutwardData['SlabWeight'] * $rowOutwardData['MFactor']);

            $LooseBags = $rowOutwardData['LooseBags'] ? $rowOutwardData['LooseBags'] : 0;
            $LooseMcs = round($LooseBags / $rowOutwardData['TotalSlabs']);

            $TotalMcs = $Mcs + $LooseMcs;
            $SlabList['TotalMcs'] = $TotalMcs;
            $OutwardMcs = $TotalMcs;

            // $ClosingMcs = $OpeningMcs + $InwardMcs - $OutwardMcs;
            // $ClosingSlabs = $OpeningSlabs + $InwardSlabs - $OutwardSlabs;
            
             $ClosingMcs = $InwardMcs - $OutwardMcs;
            $ClosingSlabs = $InwardSlabs - $OutwardSlabs;

            if ($StockType == 'ClosingDate') {
                $ArrayList['Slabs'] = $ClosingSlabs;
                $ArrayList['Mcs'] = $ClosingMcs;
            } else {
                $ArrayList['Slabs'] = $OpeningSlabs;
                $ArrayList['Mcs'] = $OpeningMcs;
            }

            $ArrayVarietyData[] = $ArrayList;
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['Data'] = $ArrayVarietyData;

        return $data;
    }
}
