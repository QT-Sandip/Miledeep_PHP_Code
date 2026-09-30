<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
class SiscoPsipReportPdfController
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
        $ConsigneeID = $RequestBody['BuyerID'];
        $ProductionPlant = $RequestBody['PlantID'];
        $Query = '';

        if ($ConsigneeID != '') {
            $Query .= " AND SPD.ConsigneeID IN ($ConsigneeID)";
        }
        if ($ProductionPlant != '') {
            // $Query .= " AND SPD.ProductionPlantID IN ($ProductionPlant)";
            $Query .= " AND FIND_IN_SET($ProductionPlant, SPD.ProductionPlantID) > 0";
        }
        $FromDate = $Year . '-04-01';
        $ToDate = ($Year + 1) . '-03-31';

        $DateFilters = "BETWEEN '$FromDate' AND '$ToDate'";

        $sqlConsignee = "SELECT SPD.ConsigneeID, ConsigneeName FROM ScrmPoData AS SPD
        LEFT JOIN ScrmConsigneeDetails AS SCD ON SPD.ConsigneeID = SCD.ConID 
        LEFT JOIN ScrmCustomInvoices AS SCI ON SPD.PoID = SCI.PiID 
        WHERE PoDate $DateFilters $Query GROUP BY ConsigneeID";

        $stmtConsignee = $this->conn->prepare($sqlConsignee);
        $stmtConsignee->execute();
        // $errorInfo = $stmtConsignee->errorInfo();
        // var_dump($errorInfo);

        $ArrayConsingeeData = [];
        while ($dataConsignee = $stmtConsignee->fetch(PDO::FETCH_ASSOC)) {

            $ConsigneeID = $dataConsignee["ConsigneeID"];
            $ConsigneeName = $dataConsignee["ConsigneeName"];

            $sqlPoFields = "SPD.PoNo, SPD.PoID, ConsigneeName,CustomsInvNo,SCL.name as DestinationCountry,
            ShipmentDate,QNFD.Status,group_concat(distinct SPVL.ShortName) as ShortName,SPVL.Species,QCL.VarietyID,
        GROUP_CONCAT(distinct CONCAT(DayCode, ' - ', QCL.Grades, ' - ', McsTotal) SEPARATOR '<br>') AS Product, 
        QCL.PsiID, QCL.PsipSentDate, QNFD.ShipmentDate";

            $sqlPoSelect = "SELECT $sqlPoFields FROM ScrmPoData AS SPD
            LEFT JOIN ScrmConsigneeDetails AS SCD ON SPD.ConsigneeID = SCD.ConID
            LEFT JOIN ScrmCustomInvoices AS SCI ON SPD.PoID = SCI.PiID 
            LEFT JOIN QcCodeList AS QCL ON SPD.PoID = QCL.InvID
            LEFT JOIN ScrmCountriesList AS SCL ON SCL.id = SPD.DestinationCountry
            LEFT JOIN QcNewAddonFieldsData AS QNFD ON QCL.InvID = QNFD.PoID
            LEFT JOIN ScrmPoGradeData AS SPGD ON QCL.VarietyID = SPGD.ID
             LEFT JOIN ScrmPIVarietyList AS SPVL ON SPGD.VarietyID = SPVL.VarietyID
            WHERE PoDate $DateFilters and SPD.PoID in (SELECT distinct InvID FROM `QcCodeList` WHERE PsiID!='') 
            and SPD.PoID not in (SELECT PiID FROM `ScrmCustomInvoices` WHERE StockOutDate!='') AND SPD.ConsigneeID = $ConsigneeID 
            $Query GROUP BY PoID ";





            // GROUP BY PoID
            $stmtaPoSelect = $this->conn->prepare($sqlPoSelect);
            $stmtaPoSelect->execute();

            $ArrayBuyerData = [];
            $SNO = 0;
            while ($dataBuyerRow = $stmtaPoSelect->fetch(PDO::FETCH_ASSOC)) {

                // $PoID=$dataBuyerRow['PoID'];
                // $

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
                // $BreakUpData = $this->getSiscoBreakUpData($dataBuyerRow['PoID']);

                // foreach ($BreakUpData as $ItemBreakUpData) {
                $ArrayBuyerList['SNO'] = ++$SNO;
                $ArrayBuyerList['PoID'] = $dataBuyerRow['PoID'];
                $ArrayBuyerList['PoNo'] = $dataBuyerRow['PoNo'];
                $ArrayBuyerList['ConsigneeName'] = $ConsigneeName;
                $ArrayBuyerList['InvoiceNumber'] = $dataBuyerRow['CustomsInvNo'];
                $ArrayBuyerList['DestinationCountry'] = $dataBuyerRow['DestinationCountry'];
                $ArrayBuyerList['ShipmentDate'] = $this->utilController->getFormatedDateEmpty($dataBuyerRow['ShipmentDate']);
                $ArrayBuyerList['DisplayMessage'] = $DisplayMessage;
                $ArrayBuyerList['DisplayColor'] = $DisplayColor;
                $ArrayBuyerList['TextColor'] = $TextColor;
                $ArrayBuyerList["ShortName"] = $dataBuyerRow["ShortName"];
                $ArrayBuyerList["Species"] = $dataBuyerRow["Species"];
                $ArrayBuyerList["Product"] = $dataBuyerRow["Product"];
                $ArrayBuyerList["PsiID"] = $dataBuyerRow["PsiID"];
                $ArrayBuyerList["PsipSentDate"] = $dataBuyerRow["PsipSentDate"] ? date('d-m-Y', strtotime($dataBuyerRow["PsipSentDate"])) : '--';
                $ArrayBuyerList["ShipmentDate"] = $dataBuyerRow["ShipmentDate"] ? date('d-m-Y', strtotime($dataBuyerRow["ShipmentDate"])) : '--';
                $Status = $dataBuyerRow["Status"];

                // $ArrayBuyerList["ShortName"] = $ItemBreakUpData["ShortName"];
                // $ArrayBuyerList["Species"] = $ItemBreakUpData["Species"];
                // $ArrayBuyerList["Product"] = $ItemBreakUpData["Product"];
                // $ArrayBuyerList["PsiID"] = $ItemBreakUpData["PsiID"];
                // $ArrayBuyerList["PsipSentDate"] = $ItemBreakUpData["PsipSentDate"] ? date('d-m-Y', strtotime($ItemBreakUpData["PsipSentDate"])) : '--';
                // // $ArrayBuyerList["ShipmentDate"] = $ItemBreakUpData["ShipmentDate"] ? date('d-m-Y', strtotime($ItemBreakUpData["ShipmentDate"])) : '--';

                $ArrayBuyerData[] = $ArrayBuyerList;
                // }
            }

            $ArrayConsingeeList['ConsigneeName'] = $ConsigneeName;
            $ArrayConsingeeList['PoData'] = $ArrayBuyerData;

            if ($ArrayBuyerData) {
                $ArrayConsingeeData[] = $ArrayConsingeeList;
            }
        }

        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['SiscoData'] = $ArrayConsingeeData;

        return $Data;
    }

    // function getSiscoBreakUpData($PoID)
    // {
    //     $sqlBreakUpFields = "SPVL.ShortName,SPVL.Species,QCL.VarietyID,
    //     GROUP_CONCAT(CONCAT(DayCode, ' - ', QCL.Grades, ' - ', McsTotal) SEPARATOR '<br>') AS Product, 
    //     QCL.PsiID, QCL.PsipSentDate, QNFD.ShipmentDate, QNFD.Status ";
    //     $sqlBreakupSelect = "SELECT $sqlBreakUpFields FROM QcCodeList AS QCL
    //     LEFT JOIN QcNewAddonFieldsData AS QNFD ON QCL.InvID = QNFD.PoID
    //     LEFT JOIN ScrmPoGradeData AS SPGD ON QCL.VarietyID = SPGD.ID
    //     LEFT JOIN ScrmPIVarietyList AS SPVL ON SPGD.VarietyID = SPVL.VarietyID
    //     WHERE QCL.InvID = '$PoID' AND QCL.PsiID != '' GROUP BY SPGD.VarietyID";

    //     $stmtBreakupSelect = $this->conn->prepare($sqlBreakupSelect);
    //     $stmtBreakupSelect->execute();

    //     $SNO = 0;
    //     $ArrayBreakupData = [];
    //     while ($dataBreakupRow = $stmtBreakupSelect->fetch(PDO::FETCH_ASSOC)) {
    //         $ArrayBreakupList["SNO"] = ++$SNO;
    //         $ArrayBreakupList["ShortName"] = $dataBreakupRow["ShortName"];
    //         $ArrayBreakupList["Species"] = $dataBreakupRow["Species"];
    //         $ArrayBreakupList["Product"] = $dataBreakupRow["Product"];
    //         $ArrayBreakupList["PsiID"] = $dataBreakupRow["PsiID"];
    //         $ArrayBreakupList["PsipSentDate"] = $dataBreakupRow["PsipSentDate"] ? date('d-m-Y', strtotime($dataBreakupRow["PsipSentDate"])) : '--';
    //         $ArrayBreakupList["ShipmentDate"] = $dataBreakupRow["ShipmentDate"] ? date('d-m-Y', strtotime($dataBreakupRow["ShipmentDate"])) : '--';
    //         $Status = $dataBreakupRow["Status"];
    //         if ($Status == '') {
    //             $DisplayMessage = "Not Entered";
    //             $DisplayColor = "red";
    //             $TextColor = "white";
    //         } else {
    //             $DisplayMessage = $Status;
    //             $DisplayColor = "green";
    //             $TextColor = "white";
    //         }
    //         $ArrayBreakupList["DisplayMessage"] = $DisplayMessage;
    //         $ArrayBreakupList["DisplayColor"] = $DisplayColor;
    //         $ArrayBreakupList["TextColor"] = $TextColor;

    //         // $ArrayBreakupList["Status"] = $dataBreakupRow["Status"] ? $dataBreakupRow["Status"] : 'Not Selected';

    //         $ArrayBreakupData[] = $ArrayBreakupList;
    //     }
    //     return $ArrayBreakupData;
    // }
}
