<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class BapSheetReportController
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

    function getBapSheetReportData($RequestBody)
    {
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $PageNo = $RequestBody['PageNo'];
        $SearchKey = $RequestBody['SearchKey'];
        $PlantID = $RequestBody['PlantID'];
        $LoadAll = $RequestBody['LoadAll'];

        $Query = '';
        $FromDayCode = $this->utilController->getDaycodeofDate($FromDate);
        $ToDayCode = $this->utilController->getDaycodeofDate($ToDate);

        // $DateFilters = " BETWEEN '$FromDate' AND '$ToDate'";
        $DateFilters = " BETWEEN '$FromDayCode' AND '$ToDayCode'";

        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($PlantID) {
            $Query = "AND ProductionPlantID='$PlantID'";
        }

        if ($SearchKey != '') {
            $Query .= " and CONCAT_WS('',DayCode, McsTotal, ProductDescription, SPGD.Grade,APS.PackingStyle, SPGD.Brand, TraceNumber,
            PoNo, DestinationCountry,SCL.name, ContainerNo,ConsigneeName, ShipByDate) LIKE '%$SearchKey%'";
        }



        $sqlCount = "SELECT COUNT(*) AS TotalCount FROM QcCodeList AS QCL
        LEFT JOIN ScrmPoGradeData AS SPGD ON QCL.VarietyID = SPGD.ID
        LEFT JOIN ScrmPoData AS SPD ON SPGD.InvId = SPD.PoID
        LEFT JOIN ScrmPIVarietyList AS SPVL ON SPGD.VarietyID = SPVL.VarietyID
	    LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID = SPGD.PackingStyleID
        LEFT JOIN QcTracebilityList AS QLT ON QCL.TracebilityID = QLT.TraceID
        LEFT JOIN ScrmCustomInvoices AS SCI ON SCI.PiID=SPD.PoID
        LEFT JOIN ScrmCountriesList AS SCL ON SPD.DestinationCountry = SCL.ID
        LEFT JOIN ScrmConsigneeDetails AS SCD ON SPD.ConsigneeID = SCD.ConID
	    WHERE DayCode $DateFilters $Query order by DayCode asc";

        // WHERE ReceivingDt $DateFilters $Query
        $stmtCount = $this->conn->prepare($sqlCount);
        $stmtCount->execute();
        $dataCount = $stmtCount->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataCount['TotalCount'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "DayCode, McsTotal, ProductDescription, SPGD.Grade,APS.PackingStyle, SPGD.Brand, Tracebility,
        PoNo, DestinationCountry,SCL.name as Destination, ContainerNo,ConsigneeName, ShipByDate,CerificationName,QtyUnit, Yeild,
        SUM(TotalSlabs*SlabWeight*MFactor*McsTotal) as NetWgtKgs ,SUM(McsTotal*TotalSlabs*SlabWeight*LbsFactor) as NetWgtLbs,HeadlessYield,
        PhosphateOrNonPhosphateChemicalBatchNo, SaltOrNpt, PackingMasterCartons, PackingPouch,SCI.CustomsInvNo,SPD.PoID,SPGD.ID";

        $sqlSelect = "SELECT $sqlFields FROM QcCodeList AS QCL
        LEFT JOIN ScrmPoGradeData AS SPGD ON QCL.VarietyID = SPGD.ID
        LEFT JOIN ScrmPoData AS SPD ON SPGD.InvId = SPD.PoID
        LEFT JOIN ScrmPIVarietyList AS SPVL ON SPGD.VarietyID = SPVL.VarietyID
	    LEFT JOIN AdminPackingStyles AS APS ON APS.PCKID = SPGD.PackingStyleID
        LEFT JOIN QcTracebilityList AS QLT ON QCL.TracebilityID = QLT.TraceID
        LEFT JOIN ScrmCustomInvoices AS SCI ON SCI.PiID=SPD.PoID
        LEFT JOIN ScrmCountriesList AS SCL ON SPD.DestinationCountry = SCL.ID
        LEFT JOIN ScrmConsigneeDetails AS SCD ON SPD.ConsigneeID = SCD.ConID
        LEFT JOIN ScrmOrderCertification AS SOC ON SPD.Certification = SOC.CerificationID
	    WHERE DayCode $DateFilters $Query GROUP BY TracebilityID ORDER BY DayCode " . $Limit;
        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();

        $ArrayData = [];
        $i = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $Yield = $dataRow['Yeild'];
            $HeadlessYield = $dataRow['HeadlessYield'];
            $QtyUnit = $dataRow['QtyUnit'];

            if ($QtyUnit == 'KGS') {
                $FinishedQty = $dataRow['NetWgtKgs'];
            } else {
                $FinishedQty = $dataRow['NetWgtLbs'] * 0.454;
            }

            // $UsedQty = ((($FinishedQty / $Yield) * 100) + 0.001);
            $ReceivedQty = (($FinishedQty / $Yield) * 100);
            // $HeadlessQty = (($FinishedQty / $HeadlessYield) * 100);
            $HeadlessQty = ($ReceivedQty * $HeadlessYield) / 100;

            $Tracebility = $dataRow['Tracebility'];
            $TracebilityArray = explode("-", $Tracebility);
            $FarmerID = $TracebilityArray[0] . '-' . $TracebilityArray[2];  // NKV-001
            $RegistrationNumber = $TracebilityArray[1];

            $OrderID = $dataRow['PoID'];
            $OrderBrakupId = $dataRow['ID'];

            $SqlLineItem = " SELECT COUNT(*) + 1 AS LineNo
                                FROM ScrmPoGradeData
                                WHERE `InvId` = $OrderID
                                AND `ID` < (
                                    SELECT `ID`
                                    FROM ScrmPoGradeData
                                    WHERE `ID` = $OrderBrakupId
                                        AND `InvId` = $OrderID
                                )";
            $stmtLineItem = $this->conn->prepare($SqlLineItem);
            $stmtLineItem->execute();
            $LineNo = $stmtLineItem->fetch(PDO::FETCH_ASSOC)['LineNo'];

            $ArrayList['SNO'] = ++$i;
            $ArrayList['DayCode'] = $dataRow['DayCode'];
            $ArrayList['McsTotal'] = $dataRow['McsTotal'];
            $ArrayList['ProductDescription'] = $dataRow['ProductDescription'];
            $ArrayList['Grade'] = $dataRow['Grade'];
            $ArrayList['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayList['Brand'] = $dataRow['Brand'];
            $ArrayList['TraceNumber'] = $dataRow['TraceNumber'];
            $ArrayList['FarmerID'] = $FarmerID;
            $ArrayList['RegistrationNumber'] = $RegistrationNumber;
            $ArrayList['PoNo'] = $dataRow['PoNo'];
            $ArrayList['Destination'] = $dataRow['Destination'];
            $ArrayList['ContainerNo'] = $dataRow['ContainerNo'];
            $ArrayList['ConsigneeName'] = $dataRow['ConsigneeName'];
            $ArrayList['ShipByDate'] = $dataRow['ShipByDate'] ? date('d-m-Y', strtotime($dataRow['ShipByDate'])) : '--';
            $ArrayList['CerificationName'] = $dataRow['CerificationName'];
            $ArrayList['ReceivedQty'] = round($ReceivedQty, 3);
            $ArrayList['HeadlessQty'] = round($HeadlessQty, 3);
            $ArrayList['FinishedQty'] = round($FinishedQty, 3);
            $ArrayList['ChemicalBatchNo'] = $dataRow['PhosphateOrNonPhosphateChemicalBatchNo'];
            $ArrayList['SaltOrNpt'] = $dataRow['SaltOrNpt'];
            $ArrayList['PackingMasterCartons'] = $dataRow['PackingMasterCartons'];
            $ArrayList['PackingPouch'] = $dataRow['PackingPouch'];
            $ArrayList['CustomsInvNo'] = $dataRow['CustomsInvNo'];
            $ArrayList['SaleInvoiceLineNumber'] = !empty($dataRow['CustomsInvNo']) ? $dataRow['CustomsInvNo']. '-' . sprintf('%02d',$LineNo) : '';

            $ArrayData[] = $ArrayList;
        }

        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['TotalCount'] = $TotalCount;
        $Data['PageCount'] = $PageCount;
        $Data['BapReportData'] = $ArrayData;

        return $Data;
    }
}
