<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';

class StockSummaryReportController
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

    function getTotalSummary($RequestBody)
    {
        $ProductionDate = $RequestBody['ProductionDate'];
        $ColdstoreID = $RequestBody['ColdstoreID'];
        $TypeProduct = $RequestBody['TypeProduct'];
        $Type = $RequestBody['Type'];
        $FreezingType = $RequestBody['FreezingType'];
        $Variety = $RequestBody['Variety'];
        $Treatment = $RequestBody['Treatment'];
        $PackingStyle = $RequestBody['PackingStyle'];
        $Grade = $RequestBody['Grade'];
        $Species  = $RequestBody['Species'];

        $Query = '';

        if ($ColdstoreID != '') {
            $Query .= " AND CPID.UnitID = '$ColdstoreID'";
        }

        if ($FreezingType != '') {
            $Query .= " AND PIVL.FreezingType = '$FreezingType'";
        }

        if ($Variety != '') {
            $Query .= " AND PIVL.ShortName = '$Variety'";
        }

        if ($Treatment != '') {
            $Query .= " AND CPID.SockingType = '$Treatment'";
        }

        if ($PackingStyle != '') {
            $Query .= " AND CPID.PackingStyleID = '$PackingStyle'";
        }

        if ($Grade != '') {
            $Query .= " AND CPID.Grade = '$Grade'";
        }

        if ($Species != '') {
            $Query .= " AND PIVL.Species = '$Species'";
        }

        if ($TypeProduct != '') {
            $Query .= " AND TypeProduct = '$TypeProduct'";
        }

        $StockOutDate = date('Y-m-d', strtotime($ProductionDate . '-1 day'));

        $ShipmentData = $this->getShipmentDetails($StockOutDate, $Query);
        $MeltingData = $this->getMeltingDetails($StockOutDate, $Query);
        $RepackingData = $this->getRepackingDetails($StockOutDate, $Query);
        $PhysicalLessData = $this->getPhysicalStockDetails($StockOutDate, $Query);
        $PhysicalExcessData = $this->getPhysicalExcessStockDetails($StockOutDate, $Query);
        $IBTData = $this->getIBTSummmaryDetails($StockOutDate, $Query);

        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['ShipmentDetails'] = $ShipmentData;
        $Data['MeltingDetails'] = $MeltingData;
        $Data['RepackingDetails'] = $RepackingData;
        $Data['PhysicalLessDetails'] = $PhysicalLessData;
        $Data['PhysicalExcessDetails'] = $PhysicalExcessData;
        $Data['IBTDetails'] = $IBTData;

        return $Data;
    }

    function getShipmentDetails($StockOutDate, $Query)
    {
        $sqlShipmentFields = "OutMcs, OutLooseBags, OutType, CPID.FreezingType, CPID.VarietyID, CPID.SockingType, 
        CPID.PoID, SPD.PoNo, CPID.UnitID, CPID.PlantID, Mcs,CustomsInvNo, StockOutDate, CPID.PackingTypeID, CPID.PackingStyleID, 
        ProductDescription, CPID.Grade, CSP.PackingStyle, PIVL.Species";
        $sqlShimpentSelect = "SELECT $sqlShipmentFields FROM CsStockOutDetails AS CSOD 
        LEFT JOIN CsStockData AS CPID ON CPID.StockID = CSOD.StockID
        LEFT JOIN ScrmCustomInvoices AS SCI ON SCI.PiID = CPID.PoID 
        LEFT JOIN ScrmPoData AS SPD ON CPID.PoID = SPD.PoID
        LEFT JOIN ScrmPoGradeData AS SPGD ON SPGD.InvId=SPD.PoID
        LEFT JOIN ScrmPIVarietyList AS PIVL ON CPID.VarietyID=PIVL.VarietyID 
        LEFT JOIN AdminPackingStyles AS CSP ON CSP.PCKID = CPID.PackingStyleID
        WHERE SCI.StockOutDate = '$StockOutDate' AND CSOD.OutType = 'Shipment Out' $Query ";
        $stmtShipmentSelect = $this->conn->prepare($sqlShimpentSelect);
        $stmtShipmentSelect->execute();

        $ShipmentArrayData = [];
        $i = 0;
        while ($rowShipment = $stmtShipmentSelect->fetch(PDO::FETCH_ASSOC)) {
            $ShipmentArrayList['SNO'] = ++$i;
            $ShipmentArrayList['PoNo'] = $rowShipment['PoNo'];
            $ShipmentArrayList['OrderedMcs'] = $rowShipment['Mcs'];
            $ShipmentArrayList['OutMcs'] = $rowShipment['OutMcs'];
            $ShipmentArrayList['OutLooseBags'] = $rowShipment['OutLooseBags'];
            $ShipmentArrayList['OutType'] = $rowShipment['OutType'];
            $ShipmentArrayList['FreezingType'] = $rowShipment['FreezingType'];
            $ShipmentArrayList['VarietyID'] = $rowShipment['VarietyID'];
            $ShipmentArrayList['SockingType'] = $rowShipment['SockingType'];
            $ShipmentArrayList['UnitID'] = $rowShipment['UnitID'];
            $ShipmentArrayList['PlantID'] = $rowShipment['PlantID'];
            $ShipmentArrayList['CustomsInvNo'] = $rowShipment['CustomsInvNo'];
            $ShipmentArrayList['StockOutDate'] = $rowShipment['StockOutDate'];
            $ShipmentArrayList['PackingTypeID'] = $rowShipment['PackingTypeID'];
            $ShipmentArrayList['PackingStyleID'] = $rowShipment['PackingStyleID'];
            $ShipmentArrayList['ProductDescription'] = $rowShipment['ProductDescription'];
            $ShipmentArrayList['Grade'] = $rowShipment['Grade'];
            $ShipmentArrayList['Species'] = $rowShipment['Species'];
            $ShipmentArrayList['PackingStyle'] = $rowShipment['PackingStyle'];

            $ShipmentArrayData[] = $ShipmentArrayList;
        }
        return $ShipmentArrayData;
    }

    function getMeltingDetails($StockOutDate, $Query)
    {
        $sqlMeltingOutFields = "SUM(OutMcs) AS OutMcs, SUM(OutLooseBags) AS OutLooseBags, 
        ProductDescription, CPID.Grade, CPID.FreezingType, CPID.SockingType, CPID.PackingTypeID, 
        CSOD.OutDate, APS.PackingStyle, PoNo, PIVL.DeviSUPC, PIVL.ShortName,OutType,DeviSUPC";
        $sqlMeltingOutQuery = "SELECT $sqlMeltingOutFields FROM CsStockOutDetails AS CSOD 
        LEFT JOIN CsStockData AS CPID ON CSOD.StockID = CPID.StockID
        LEFT JOIN ScrmPoData AS SPD ON SPD.PoID=CPID.PoID
        LEFT JOIN ScrmPIVarietyList AS PIVL ON PIVL.VarietyID=CPID.VarietyID
        LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID=CPID.PackingStyleID
        WHERE CSOD.OutDate = '$StockOutDate' $Query AND OutType IN ('Melting OUT', 'Melting IN','Melting') GROUP BY CPID.VarietyID";
        $stmtMeltingOutSelect = $this->conn->prepare($sqlMeltingOutQuery);
        $stmtMeltingOutSelect->execute();

        $MeltingOutArray = [];
        $i = 0;
        while ($rowMeltingOut = $stmtMeltingOutSelect->fetch(PDO::FETCH_ASSOC)) {
            $MeltingOutList['SNO'] = ++$i;
            $MeltingOutList['OutMcs'] = $rowMeltingOut['OutMcs'];
            $MeltingOutList['OutLooseBags'] = $rowMeltingOut['OutLooseBags'];
            $MeltingOutList['ProductDescription'] = $rowMeltingOut['ProductDescription'];
            $MeltingOutList['Grade'] = $rowMeltingOut['Grade'];
            $MeltingOutList['FreezingType'] = $rowMeltingOut['FreezingType'];
            $MeltingOutList['PackingStyle'] = $rowMeltingOut['PackingStyle'];

            $MeltingOutArray[] = $MeltingOutList;
        }

        $sqlMeltingInFields = "SUM(CPID.ActualMcs) AS ActualMcs, SUM(CPID.ActualLooseBags) AS LooseBags, 
        ProductDescription, CPID.Grade, CPID.FreezingType, CPID.SockingType, CPID.PackingTypeID, 
        APS.PackingStyle,CPID.ProductionDate, CPID.ProductionTypeID";

        $sqlMeltingInSelect = "SELECT $sqlMeltingInFields FROM CsStockData AS CPID
        LEFT JOIN CsDailyProductionData AS CDPD ON CDPD.ProductionID = CPID.ProductionID
        LEFT JOIN CsStockOutDetails AS CSOD ON CDPD.StockOutID = CSOD.StockOutID
        LEFT JOIN ScrmPoData AS SPD ON SPD.PoID=CPID.PoID
        LEFT JOIN ScrmPIVarietyList AS SPVL ON SPVL.VarietyID=CPID.VarietyID
        LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID=CPID.PackingStyleID
        WHERE CPID.ProductionDate = '$StockOutDate' $Query AND OutType IN ('Melting OUT', 'Melting IN','Melting') GROUP BY CPID.VarietyID";
        $stmtMeltingInSelect = $this->conn->prepare($sqlMeltingInSelect);
        $stmtMeltingInSelect->execute();

        $MeltingInArray = [];
        $i = 0;
        while ($rowMeltingIn = $stmtMeltingInSelect->fetch(PDO::FETCH_ASSOC)) {
            $MeltingInList['SNO'] = ++$i;
            $MeltingInList['ActualMcs'] = $rowMeltingIn['ActualMcs'];
            $MeltingInList['LooseBags'] = $rowMeltingIn['LooseBags'];
            $MeltingInList['ProductDescription'] = $rowMeltingIn['ProductDescription'];
            $MeltingInList['Grade'] = $rowMeltingIn['Grade'];
            $MeltingInList['PackingStyle'] = $rowMeltingIn['PackingStyle'];

            $MeltingInArray[] = $MeltingInList;
        }

        $Data['MeltingOut'] = $MeltingOutArray;
        $Data['MeltingIn'] = $MeltingInArray;

        return $Data;
    }

    function getRepackingDetails($StockOutDate, $Query)
    {
        $sqlRepackingOutFields = "SUM(OutMcs) AS OutMcs, SUM(OutLooseBags) AS OutLooseBags, 
        ProductDescription, CPID.Grade, CPID.FreezingType, CPID.SockingType, CPID.PackingTypeID, 
        CSOD.OutDate, APS.PackingStyle, PoNo, PIVL.DeviSUPC, PIVL.ShortName,OutType,DeviSUPC";
        $sqlRepackingOutQuery = "SELECT $sqlRepackingOutFields FROM CsStockOutDetails AS CSOD 
        LEFT JOIN CsStockData AS CPID ON CSOD.StockID = CPID.StockID
        LEFT JOIN ScrmPoData AS SPD ON SPD.PoID=CPID.PoID
        LEFT JOIN ScrmPIVarietyList AS PIVL ON PIVL.VarietyID=CPID.VarietyID
        LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID=CPID.PackingStyleID
        WHERE CSOD.OutDate = '$StockOutDate' $Query AND OutType IN ('Repacking') GROUP BY CPID.VarietyID";
        $stmtRepackingOutSelect = $this->conn->prepare($sqlRepackingOutQuery);
        $stmtRepackingOutSelect->execute();

        $RepackingOutArray = [];
        $i = 0;
        while ($rowMeltingOut = $stmtRepackingOutSelect->fetch(PDO::FETCH_ASSOC)) {
            $RepackingOutList['SNO'] = ++$i;
            $RepackingOutList['OutMcs'] = $rowMeltingOut['OutMcs'];
            $RepackingOutList['OutLooseBags'] = $rowMeltingOut['OutLooseBags'];
            $RepackingOutList['ProductDescription'] = $rowMeltingOut['ProductDescription'];
            $RepackingOutList['Grade'] = $rowMeltingOut['Grade'];
            $RepackingOutList['FreezingType'] = $rowMeltingOut['FreezingType'];
            $RepackingOutList['PackingStyle'] = $rowMeltingOut['PackingStyle'];

            $RepackingOutArray[] = $RepackingOutList;
        }

        $sqlRepackingInFields = "SUM(CPID.ActualMcs) AS ActualMcs, SUM(CPID.ActualLooseBags) AS LooseBags, 
        ProductDescription, CPID.Grade, CPID.FreezingType, CPID.SockingType, CPID.PackingTypeID, 
        APS.PackingStyle,CPID.ProductionDate, CPID.ProductionTypeID";

        $sqlRepackingInSelect = "SELECT $sqlRepackingInFields FROM CsStockData AS CPID
        LEFT JOIN CsDailyProductionData AS CDPD ON CDPD.ProductionID = CPID.ProductionID
        LEFT JOIN CsStockOutDetails AS CSOD ON CDPD.StockOutID = CSOD.StockOutID
        LEFT JOIN ScrmPoData AS SPD ON SPD.PoID=CPID.PoID
        LEFT JOIN ScrmPIVarietyList AS SPVL ON SPVL.VarietyID=CPID.VarietyID
        LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID=CPID.PackingStyleID
        WHERE CPID.ProductionDate = '$StockOutDate' $Query AND OutType IN ('Repacking') GROUP BY CPID.VarietyID";
        $stmtRepackingInSelect = $this->conn->prepare($sqlRepackingInSelect);
        $stmtRepackingInSelect->execute();

        $RepackingInArray = [];
        $i = 0;
        while ($rowMeltingIn = $stmtRepackingInSelect->fetch(PDO::FETCH_ASSOC)) {
            $RepackingInList['SNO'] = ++$i;
            $RepackingInList['ActualMcs'] = $rowMeltingIn['ActualMcs'];
            $RepackingInList['LooseBags'] = $rowMeltingIn['LooseBags'];
            $RepackingInList['ProductDescription'] = $rowMeltingIn['ProductDescription'];
            $RepackingInList['Grade'] = $rowMeltingIn['Grade'];
            $RepackingInList['PackingStyle'] = $rowMeltingIn['PackingStyle'];

            $RepackingInArray[] = $RepackingInList;
        }
        $Data['RepackingOut'] = $RepackingOutArray;
        $Data['RepackingIn'] = $RepackingInArray;

        return $Data;
    }

    function getPhysicalStockDetails($StockOutDate, $Query)
    {
        $sqlTransCategory = "SELECT TransCategory FROM CsTransactionTypes 
        WHERE TransType=2 AND TransStatus=1 AND TransCategory NOT IN ('Repacking','Melting OUT', 'Melting IN','Melting')";
        $stmtTransCategory = $this->conn->prepare($sqlTransCategory);
        $stmtTransCategory->execute();

        $ArrayData = [];
        while ($rowTransCategory = $stmtTransCategory->fetch(PDO::FETCH_ASSOC)) {
            $TransCategory = $rowTransCategory['TransCategory'];

            $sqlPhysicalLessFields = "SUM(OutMcs) AS OutMcs, SUM(OutLooseBags) AS OutLooseBags, 
            ProductDescription, CPID.Grade, CPID.FreezingType, CPID.SockingType, CPID.PackingTypeID, 
            CSOD.OutDate, APS.PackingStyle, PoNo, PIVL.DeviSUPC, PIVL.ShortName,OutType,DeviSUPC";

            $sqlPhysicalLessQuery = "SELECT $sqlPhysicalLessFields FROM CsStockOutDetails AS CSOD 
            LEFT JOIN CsStockData AS CPID ON CSOD.StockID = CPID.StockID
            LEFT JOIN ScrmPoData AS SPD ON SPD.PoID=CPID.PoID
            LEFT JOIN ScrmPIVarietyList AS PIVL ON PIVL.VarietyID=CPID.VarietyID
            LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID=CPID.PackingStyleID
            WHERE CSOD.OutDate = '$StockOutDate' $Query AND OutType = '$TransCategory' GROUP BY CPID.VarietyID";
            $stmtPhysicalLesSelect = $this->conn->prepare($sqlPhysicalLessQuery);
            $stmtPhysicalLesSelect->execute();

            $PhysicalLessArray = [];
            $i = 0;
            while ($rowPhysicalLess = $stmtPhysicalLesSelect->fetch(PDO::FETCH_ASSOC)) {
                $PhysicalLessList['SNO'] = ++$i;
                $PhysicalLessList['OutMcs'] = $rowPhysicalLess['OutMcs'];
                $PhysicalLessList['OutLooseBags'] = $rowPhysicalLess['OutLooseBags'];
                $PhysicalLessList['ProductDescription'] = $rowPhysicalLess['ProductDescription'];
                $PhysicalLessList['Grade'] = $rowPhysicalLess['Grade'];
                $PhysicalLessList['FreezingType'] = $rowPhysicalLess['FreezingType'];
                $PhysicalLessList['PackingStyle'] = $rowPhysicalLess['PackingStyle'];

                $PhysicalLessArray[] = $PhysicalLessList;
            }


            $ArrayList = [];
            if (!empty($PhysicalLessArray)) {
                // if (!is_null($PhysicalLessArray) && !empty($PhysicalLessArray)) {
                $ArrayList['TransCategory'] = $TransCategory;
                $ArrayList['PhysicalLessData'] = $PhysicalLessArray;
            }
            $ArrayData[] = $ArrayList;
        }

        return $ArrayData;
    }

    function getPhysicalExcessStockDetails($StockOutDate, $Query)
    {
        $sqlPhysicalExcessFields = "SUM(ActualMcs) AS AvalMcs, SUM(ActualLooseBags) AS LooseBags, 
        ProductDescription, CPID.Grade, CPID.FreezingType, CPID.SockingType, CPID.PackingTypeID, 
        APS.PackingStyle, PoNo, PIVL.DeviSUPC, PIVL.ShortName,DeviSUPC";

        $sqlPhysicalExcessQuery = "SELECT $sqlPhysicalExcessFields FROM CsStockData AS CPID 
        LEFT JOIN ScrmPoData AS SPD ON SPD.PoID=CPID.PoID
        LEFT JOIN ScrmPIVarietyList AS PIVL ON PIVL.VarietyID=CPID.VarietyID
        LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID=CPID.PackingStyleID
        WHERE CPID.ProductionDate = '$StockOutDate' $Query AND ProductionTypeID = 'Storage Only' GROUP BY CPID.VarietyID";
        $stmtPhysicalExcesSelect = $this->conn->prepare($sqlPhysicalExcessQuery);
        $stmtPhysicalExcesSelect->execute();

        // $errorInfo = $stmtPhysicalExcesSelect->errorInfo();
        // var_dump($errorInfo);
        $PhysicalExcessArray = [];
        $i = 0;
        while ($rowPhysicalLess = $stmtPhysicalExcesSelect->fetch(PDO::FETCH_ASSOC)) {
            $PhysicalExcessList['SNO'] = ++$i;
            $PhysicalExcessList['AvalMcs'] = $rowPhysicalLess['AvalMcs'];
            $PhysicalExcessList['LooseBags'] = $rowPhysicalLess['LooseBags'];
            $PhysicalExcessList['ProductDescription'] = $rowPhysicalLess['ProductDescription'];
            $PhysicalExcessList['Grade'] = $rowPhysicalLess['Grade'];
            $PhysicalExcessList['FreezingType'] = $rowPhysicalLess['FreezingType'];
            $PhysicalExcessList['PackingStyle'] = $rowPhysicalLess['PackingStyle'];

            $PhysicalExcessArray[] = $PhysicalExcessList;
        }

        return $PhysicalExcessArray;
    }

    function getIBTSummmaryDetails($StockOutDate, $Query)
    {
        $sqlIBTOutFields = "FromStore, OrderDate, ToStore, CITBD.VarietyID, SUM(NoOfMcs) AS MCS, SUM(NoOfLooseBags) AS LooseBags, 
        CITBD.StockID,ProductDescription, CPID.Grade, APS.PackingStyle, CPID.PackingTypeID, RPS.StationName AS FromStoreName, 
        RPS2.StationName AS ToStoreName, CPID.UnitID";
        $sqlIBTOutSelect = "SELECT $sqlIBTOutFields FROM CsIBTRequestBreakUpDetails AS CITBD
        LEFT JOIN CsIBTRequestDetails AS CIRD ON CITBD.IBTRequestID = CIRD.IBTID
        LEFT JOIN CsStockData AS CPID ON CITBD.StockID = CPID.StockID
        LEFT JOIN ScrmPIVarietyList AS PIVL ON PIVL.VarietyID=CPID.VarietyID
        LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID=CPID.PackingStyleID
        left join RmProcessingStations as RPS on RPS.ProcessingStationID=CIRD.FromStore 
        left join RmProcessingStations as RPS2 on RPS2.ProcessingStationID=CIRD.ToStore
        WHERE CIRD.OrderDate = '$StockOutDate' $Query  GROUP BY CPID.VarietyID";
        $stmtIBTOutSelect = $this->conn->prepare($sqlIBTOutSelect);
        $stmtIBTOutSelect->execute();

        $IBTOutArray = [];
        $i = 0;
        while ($rowIBTOut = $stmtIBTOutSelect->fetch(PDO::FETCH_ASSOC)) {
            $IBTOutList['SNO'] = ++$i;
            $IBTOutList['MCS'] = $rowIBTOut['MCS'];
            $IBTOutList['LooseBags'] = $rowIBTOut['LooseBags'];
            $IBTOutList['ProductDescription'] = $rowIBTOut['ProductDescription'];
            $IBTOutList['Grade'] = $rowIBTOut['Grade'];
            $IBTOutList['PackingStyle'] = $rowIBTOut['PackingStyle'];
            $IBTOutList['FromStore'] = $rowIBTOut['FromStore'];
            $IBTOutList['FromStoreName'] = $rowIBTOut['FromStoreName'];
            $IBTOutList['ToStore'] = $rowIBTOut['ToStore'];
            $IBTOutList['ToStoreName'] = $rowIBTOut['ToStoreName'];
            $IBTOutList['UnitID'] = $rowIBTOut['UnitID'];
            $IBTOutList['OrderDate'] = $rowIBTOut['OrderDate'];

            $IBTOutArray[] = $IBTOutList;
        }

        $sqlIBTInFields = "SUM(ActualMcs) AS AvalMcs, SUM(ActualLooseBags) AS LooseBags, 
        ProductDescription, CPID.Grade, CPID.FreezingType, CPID.SockingType, CPID.PackingTypeID, 
        APS.PackingStyle, PoNo, PIVL.DeviSUPC, PIVL.ShortName,DeviSUPC,FromStore,IBTOutID,
        CPID.ProductionID,RPS.StationName AS FromStoreName";

        $sqlIBTInQuery = "SELECT $sqlIBTInFields FROM CsStockData AS CPID 
        LEFT JOIN ScrmPoData AS SPD ON SPD.PoID=CPID.PoID
        LEFT JOIN ScrmPIVarietyList AS PIVL ON PIVL.VarietyID=CPID.VarietyID
        LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID=CPID.PackingStyleID
        LEFT JOIN CsDailyProductionData AS CSDP ON CSDP.ProductionID=CPID.ProductionID
        LEFT JOIN CsIBTRequestDetails AS CIRD ON CSDP.IBTOutID = CIRD.IBTID
        left join RmProcessingStations as RPS on RPS.ProcessingStationID=CIRD.FromStore 
        WHERE CPID.ProductionDate = '$StockOutDate' $Query AND ProductionTypeID = 'IBT IN' GROUP BY CPID.VarietyID";
        $stmtIBTInSelect = $this->conn->prepare($sqlIBTInQuery);
        $stmtIBTInSelect->execute();

        $IBTInArray = [];
        $i = 0;
        while ($rowIBTIn = $stmtIBTInSelect->fetch(PDO::FETCH_ASSOC)) {
            $IBTInList['SNO'] = ++$i;
            $IBTInList['AvalMcs'] = $rowIBTIn['AvalMcs'];
            $IBTInList['LooseBags'] = $rowIBTIn['LooseBags'];
            $IBTInList['ProductDescription'] = $rowIBTIn['ProductDescription'];
            $IBTInList['Grade'] = $rowIBTIn['Grade'];
            $IBTInList['FreezingType'] = $rowIBTIn['FreezingType'];
            $IBTInList['PackingStyle'] = $rowIBTIn['PackingStyle'];
            $IBTInList['FromStoreName'] = $rowIBTIn['FromStoreName'];

            $IBTInArray[] = $IBTInList;
        }

        $Data['IBTOutDetails'] = $IBTOutArray;
        $Data['IBTInDetails'] = $IBTInArray;

        return $Data;
    }
}
