<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';

class BuyerReportController
{
    private $conn;
    private $utilController;
    private $Error = null;
    private $Msg = '';

    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
    }

    function getBuyersData($RequestBody)
    {
        $Year = $RequestBody['Year'];
        $ProductionPlant = $RequestBody['ProductionPlant'];
        $ConsigneeID = implode(',', $RequestBody['BuyerID']);
        $BuyerID = $RequestBody['ConsigneeID'];




        $Query = '';
        if ($ProductionPlant != '') {
            // $Query .= " AND SPD.ProductionPlantID IN ($ProductionPlant)";
            $Query .= " AND FIND_IN_SET($ProductionPlant, SPD.ProductionPlantID) > 0";
        }

        if ($BuyerID != '') {
            $Query .= " AND SPD.ConsigneeID IN ($BuyerID)";
        }

        if ($ConsigneeID != '') {
            $Query .= " AND SPD.ConsigneeID IN ($ConsigneeID)";
        }

        $FromDate = $Year . '-04-01';
        $ToDate = ($Year + 1) . '-03-31';

        $DateFilters = "BETWEEN '$FromDate' AND '$ToDate'";

        // $sqlBuyerFields = "ConsigneeID, ConsigneeName,GROUP_CONCAT(ProductionPlantID) AS ProductionPlantID";
        $sqlBuyerFields = "ConsigneeID, ConsigneeName";
        $sqlBuyerSelect = "SELECT $sqlBuyerFields FROM ScrmPoData AS SPD
        LEFT JOIN ScrmConsigneeDetails AS SCD ON SPD.ConsigneeID = SCD.ConID
        LEFT JOIN ScrmCustomInvoices AS SCI ON SPD.PoID = SCI.PiID 
        LEFT JOIN QcNewAddonFieldsData AS QNAD ON SPD.PoID = QNAD.PoID 
        WHERE InvDate $DateFilters AND RequestSentDate != '' $Query GROUP BY ConsigneeID ";


        // AND StockOutDate = ''
        $stmtBuyerSelect = $this->conn->prepare($sqlBuyerSelect);
        $stmtBuyerSelect->execute();

        $ArrayBuyerData = [];
        while ($dataBuyerRow = $stmtBuyerSelect->fetch(PDO::FETCH_ASSOC)) {
            $BreakUpData = $this->getBuyerBreakUpData($dataBuyerRow['ConsigneeID']);
            $ArrayBuyerList['ConsigneeID'] = $dataBuyerRow['ConsigneeID'];
            $ArrayBuyerList['ConsigneeName'] = $dataBuyerRow['ConsigneeName'];
            // $ArrayBuyerList['ProductionPlantID'] = $dataBuyerRow['ProductionPlantID'];
            $ArrayBuyerList['BuyerBreakUpData'] = $BreakUpData;

            $ArrayBuyerData[] = $ArrayBuyerList;
        }

        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['BuyerData'] = $ArrayBuyerData;

        return $Data;
    }

    function getBuyerBreakUpData($ConsigneeID)
    {
        $sqlBreakUpFields = "SPD.PoNo, SPVL.ProductDescription, APS.PackingStyle, SPGD.Grade, 
        QNFD.RequestSentDate, QNFD.SampleSendingDate, QNFD.ReportExpectingDate";
        $sqlBreakupSelect = "SELECT $sqlBreakUpFields FROM ScrmPoData AS SPD
        LEFT JOIN ScrmPoGradeData AS SPGD ON SPD.PoID = SPGD.InvId
        LEFT JOIN ScrmPIVarietyList AS SPVL ON SPGD.VarietyID = SPVL.VarietyID
        LEFT JOIN AdminPackingStyles AS APS ON SPGD.PackingStyleID = APS.PCKID
        LEFT JOIN QcNewAddonFieldsData AS QNFD ON SPD.PoID = QNFD.PoID
        LEFT JOIN ScrmCustomInvoices AS SCI ON SPD.PoID = SCI.PiID 
        WHERE ConsigneeID = '$ConsigneeID' AND RequestSentDate != '' ";
        // AND StockOutDate = ''
        $stmtBreakupSelect = $this->conn->prepare($sqlBreakupSelect);
        $stmtBreakupSelect->execute();

        $SNO = 0;
        $ArrayBreakupData = [];
        while ($dataBreakupRow = $stmtBreakupSelect->fetch(PDO::FETCH_ASSOC)) {
            $ArrayBreakupList["SNO"] = ++$SNO;
            $ArrayBreakupList["PoNo"] = $dataBreakupRow["PoNo"];
            $ArrayBreakupList["ProductDescription"] = $dataBreakupRow["ProductDescription"];
            $ArrayBreakupList["PackingStyle"] = $dataBreakupRow["PackingStyle"];
            $ArrayBreakupList["Grade"] = $dataBreakupRow["Grade"];
            $ArrayBreakupList["RequestSentDate"] = $dataBreakupRow["RequestSentDate"] ? date('d-m-Y', strtotime($dataBreakupRow["RequestSentDate"])) : '-';
            $ArrayBreakupList["SampleSendingDate"] = $dataBreakupRow["SampleSendingDate"] ? date('d-m-Y', strtotime($dataBreakupRow["SampleSendingDate"])) : '-';
            $ArrayBreakupList["ReportExpectingDate"] = $dataBreakupRow["ReportExpectingDate"] ? date('d-m-Y', strtotime($dataBreakupRow["ReportExpectingDate"])) : '-';

            $ArrayBreakupData[] = $ArrayBreakupList;
        }
        return $ArrayBreakupData;
    }
}
