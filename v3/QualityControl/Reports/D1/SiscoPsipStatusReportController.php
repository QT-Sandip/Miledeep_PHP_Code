<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
class SiscoPsipStatusReportController
{
    private $conn;
    private $utilController;
    private $Error = null;
    private $Msg = '';

    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
    }

    function getSiscoData($RequestBody)
    {
        $Year = $RequestBody['Year'];
        // echo $ConsigneeID = implode(',', $RequestBody['BuyerID']);
        // exit;
        $ConsigneeID = $RequestBody['BuyerID'];
        $ProductionPlant = $RequestBody['PlantID'];
        $Query = '';

        if ($ConsigneeID != '') {
            $Query .= " AND SPD.ConsigneeID IN ($ConsigneeID)";
        }
        if ($ProductionPlant != '') {
            $Query .= " AND SPD.ProductionPlantID IN ($ProductionPlant)";
        }
        $FromDate = $Year . '-04-01';
        $ToDate = ($Year + 1) . '-03-31';

        $DateFilters = "BETWEEN '$FromDate' AND '$ToDate'";

        $sqlBuyerFields = "SPD.PoNo, SPD.PoID, ConsigneeName,CustomsInvNo,SCL.name as DestinationCountry,ShipmentDate,QNFD.Status";
        $sqlBuyerSelect = "SELECT $sqlBuyerFields FROM ScrmPoData AS SPD
        LEFT JOIN ScrmConsigneeDetails AS SCD ON SPD.ConsigneeID = SCD.ConID
        LEFT JOIN ScrmCustomInvoices AS SCI ON SPD.PoID = SCI.PiID 
        LEFT JOIN QcCodeList AS QCL ON SPD.PoID = QCL.InvID
        LEFT JOIN ScrmCountriesList AS SCL ON SCL.id = SPD.DestinationCountry
        LEFT JOIN QcNewAddonFieldsData AS QNFD ON QCL.InvID = QNFD.PoID
        WHERE PoDate $DateFilters AND StockOutDate = '' AND QCL.PsiID != '' $Query GROUP BY PoID,ConsigneeID ";

        // GROUP BY PoID
        $stmtBuyerSelect = $this->conn->prepare($sqlBuyerSelect);
        $stmtBuyerSelect->execute();

        $ArrayBuyerData = [];
        $SNO = 0;
        while ($dataBuyerRow = $stmtBuyerSelect->fetch(PDO::FETCH_ASSOC)) {

            $Status = $dataBuyerRow["Status"];
            if ($Status == '') {
                $DisplayMessage = "Not Entered";
                $DisplayColor = "red";
                $TextColor = "white";
            } else {
                $DisplayMessage = $Status;
                $DisplayColor = "green";
                $TextColor = "white";
            }
            $BreakUpData = $this->getSiscoBreakUpData($dataBuyerRow['PoID']);
            $ArrayBuyerList['SNO'] = ++$SNO;
            $ArrayBuyerList['PoID'] = $dataBuyerRow['PoID'];
            $ArrayBuyerList['PoNo'] = $dataBuyerRow['PoNo'];
            $ArrayBuyerList['ConsigneeName'] = $dataBuyerRow['ConsigneeName'];
            $ArrayBuyerList['InvoiceNumber'] = $dataBuyerRow['CustomsInvNo'];
            $ArrayBuyerList['DestinationCountry'] = $dataBuyerRow['DestinationCountry'];
            $ArrayBuyerList['ShipmentDate'] = $this->utilController->getFormatedDateEmpty($dataBuyerRow['ShipmentDate']);
            $ArrayBuyerList['DisplayMessage'] = $DisplayMessage;
            $ArrayBuyerList['DisplayColor'] = $DisplayColor;
            $ArrayBuyerList['TextColor'] = $TextColor;
            $ArrayBuyerList['SiscoBreakUpData'] = $BreakUpData;

            $ArrayBuyerData[] = $ArrayBuyerList;
        }

        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['SiscoData'] = $ArrayBuyerData;

        return $Data;
    }

    function getSiscoBreakUpData($PoID)
    {
        $sqlBreakUpFields = "SPVL.ShortName,SPVL.Species,QCL.VarietyID,
        GROUP_CONCAT(CONCAT(DayCode, ' - ', QCL.Grades, ' - ', McsTotal) SEPARATOR '<br>') AS Product, 
        QCL.PsiID, QCL.PsipSentDate, QNFD.ShipmentDate, QNFD.Status ";
        $sqlBreakupSelect = "SELECT $sqlBreakUpFields FROM QcCodeList AS QCL
        LEFT JOIN QcNewAddonFieldsData AS QNFD ON QCL.InvID = QNFD.PoID
        LEFT JOIN ScrmPoGradeData AS SPGD ON QCL.VarietyID = SPGD.ID
        LEFT JOIN ScrmPIVarietyList AS SPVL ON SPGD.VarietyID = SPVL.VarietyID
        WHERE QCL.InvID = '$PoID' AND QCL.PsiID != '' GROUP BY SPGD.VarietyID";

        $stmtBreakupSelect = $this->conn->prepare($sqlBreakupSelect);
        $stmtBreakupSelect->execute();

        $SNO = 0;
        $ArrayBreakupData = [];
        while ($dataBreakupRow = $stmtBreakupSelect->fetch(PDO::FETCH_ASSOC)) {
            $ArrayBreakupList["SNO"] = ++$SNO;
            $ArrayBreakupList["ShortName"] = $dataBreakupRow["ShortName"];
            $ArrayBreakupList["Species"] = $dataBreakupRow["Species"];
            $ArrayBreakupList["Product"] = $dataBreakupRow["Product"];
            $ArrayBreakupList["PsiID"] = $dataBreakupRow["PsiID"];
            $ArrayBreakupList["PsipSentDate"] = $dataBreakupRow["PsipSentDate"] ? date('d-m-Y', strtotime($dataBreakupRow["PsipSentDate"])) : '--';
            $ArrayBreakupList["ShipmentDate"] = $dataBreakupRow["ShipmentDate"] ? date('d-m-Y', strtotime($dataBreakupRow["ShipmentDate"])) : '--';
            $Status = $dataBreakupRow["Status"];
            if ($Status == '') {
                $DisplayMessage = "Not Entered";
                $DisplayColor = "red";
                $TextColor = "white";
            } else {
                $DisplayMessage = $Status;
                $DisplayColor = "green";
                $TextColor = "white";
            }
            $ArrayBreakupList["DisplayMessage"] = $DisplayMessage;
            $ArrayBreakupList["DisplayColor"] = $DisplayColor;
            $ArrayBreakupList["TextColor"] = $TextColor;

            // $ArrayBreakupList["Status"] = $dataBreakupRow["Status"] ? $dataBreakupRow["Status"] : 'Not Selected';

            $ArrayBreakupData[] = $ArrayBreakupList;
        }
        return $ArrayBreakupData;
    }
}
