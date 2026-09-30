<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

header('Content-Type: application/json');

function qcPreviewCsvInts($value)
{
    if ($value === null || $value === '') {
        return '';
    }
    $parts = array_filter(array_map('intval', explode(',', $value)));
    return implode(',', $parts);
}

function qcPreviewCsvStrings($iconn, $value)
{
    if ($value === null || $value === '') {
        return array();
    }
    $parts = array();
    foreach (explode(',', $value) as $item) {
        $item = trim($item);
        if ($item !== '') {
            $parts[] = "'" . mysqli_real_escape_string($iconn, $item) . "'";
        }
    }
    return $parts;
}
function qcPreviewFetchAll($iconn, $sql)
{
    $result = mysqli_query($iconn, $sql);
    if (!$result) {
        throw new Exception(mysqli_error($iconn));
    }
    $data = array();
    while ($row = mysqli_fetch_assoc($result)) {
        $data[] = $row;
    }
    return $data;
}

function qcPreviewGetMonthlyData($iconn, $fromDate, $toDate)
{
    $fromDate = mysqli_real_escape_string($iconn, $fromDate);
    $toDate = mysqli_real_escape_string($iconn, $toDate);
    $sql = "SELECT
            DATE_FORMAT(SCI.CInvDate, '%b %Y') AS MonthYear,
            SUM(CASE WHEN COALESCE(QC.Clist, 0) > 0 THEN 1 ELSE 0 END) AS CompletedCount,
            SUM(CASE WHEN COALESCE(QC.Clist, 0) = 0 THEN 1 ELSE 0 END) AS PendingCount
        FROM ScrmPoData POD
        LEFT JOIN ScrmCustomInvoices AS SCI ON SCI.PiID = POD.PoID
        LEFT JOIN (
            SELECT InvID, COUNT(*) AS Clist
            FROM QcCodeList
            GROUP BY InvID
        ) QC ON QC.InvID = POD.PoID
        WHERE 1=1
            AND SCI.CInvDate BETWEEN '$fromDate' AND '$toDate'
        GROUP BY YEAR(SCI.CInvDate), MONTH(SCI.CInvDate)
        ORDER BY YEAR(SCI.CInvDate), MONTH(SCI.CInvDate)";
    return qcPreviewFetchAll($iconn, $sql);
}
function qcPreviewGetCenterWiseData($iconn, $fromDate, $toDate)
{
    $fromDate = mysqli_real_escape_string($iconn, $fromDate);
    $toDate = mysqli_real_escape_string($iconn, $toDate);
    $sql = "SELECT
            ps.PurchaseStationID,
            ps.StationName AS CenterName,
            SUM(b.Weight) AS TotalBreakupWeight,
            SUM(CASE WHEN LOWER(qrar.Result) LIKE '%positive%' OR LOWER(qrar.Result) LIKE '%possitive%' THEN 1 ELSE 0 END) AS PositiveCount,
            SUM(CASE WHEN qrar.Result IS NOT NULL AND qrar.Result <> '' AND LOWER(qrar.Result) NOT LIKE '%positive%' AND LOWER(qrar.Result) NOT LIKE '%possitive%' THEN 1 ELSE 0 END) AS NegativeCount
        FROM RmArrivalDetatils a
        LEFT JOIN RmLotDetatils l ON a.RmArrivalID = l.RmReferenceID
        LEFT JOIN RmArrivalBreakUpDetails b ON l.BreakUpID = b.ArrivalBrkUpID
        LEFT JOIN QcRmAbReport qrar ON qrar.LotID = l.RmLotID
        LEFT JOIN RmPurchaseStations ps ON l.PurchaseStationID = ps.PurchaseStationID
        WHERE a.ArrivalDate BETWEEN '$fromDate' AND '$toDate'
        GROUP BY ps.PurchaseStationID, ps.StationName
        ORDER BY PositiveCount DESC, TotalBreakupWeight DESC
        LIMIT 5";
    return qcPreviewFetchAll($iconn, $sql);
}

function qcPreviewGetSupplierWiseData($iconn, $fromDate, $toDate)
{
    $fromDate = mysqli_real_escape_string($iconn, $fromDate);
    $toDate = mysqli_real_escape_string($iconn, $toDate);
    $sql = "SELECT
            s.ID,
            s.FinancierName AS SupplierName,
            SUM(CASE WHEN LOWER(qrar.Result) LIKE '%positive%' OR LOWER(qrar.Result) LIKE '%possitive%' THEN 1 ELSE 0 END) AS PositiveCount,
            SUM(CASE WHEN LOWER(qrar.Result) LIKE '%positive%' OR LOWER(qrar.Result) LIKE '%possitive%' THEN b.Weight ELSE 0 END) AS PositiveWeight,
            SUM(CASE WHEN qrar.Result IS NOT NULL AND qrar.Result <> '' AND LOWER(qrar.Result) NOT LIKE '%positive%' AND LOWER(qrar.Result) NOT LIKE '%possitive%' THEN 1 ELSE 0 END) AS NegativeCount,
            SUM(CASE WHEN qrar.Result IS NOT NULL AND qrar.Result <> '' AND LOWER(qrar.Result) NOT LIKE '%positive%' AND LOWER(qrar.Result) NOT LIKE '%possitive%' THEN b.Weight ELSE 0 END) AS NegativeWeight,
            SUM(b.Weight) AS TotalBreakupWeight
        FROM RmArrivalDetatils a
        LEFT JOIN RmLotDetatils l ON a.RmArrivalID = l.RmReferenceID
        LEFT JOIN RmArrivalBreakUpDetails b ON l.BreakUpID = b.ArrivalBrkUpID
        LEFT JOIN RmPurchaseStations ps ON l.PurchaseStationID = ps.PurchaseStationID
        LEFT JOIN QcRmAbReport qrar ON qrar.LotID = l.RmLotID
        LEFT JOIN RmSuppliers s ON a.RmSupplierID = s.ID
        WHERE a.ArrivalDate BETWEEN '$fromDate' AND '$toDate'
        GROUP BY s.ID, s.FinancierName
        ORDER BY PositiveCount DESC, TotalBreakupWeight DESC
        LIMIT 5";
    return qcPreviewFetchAll($iconn, $sql);
}

function qcPreviewGetLotsData($iconn, $fromDate, $toDate)
{
    $fromDate = mysqli_real_escape_string($iconn, $fromDate);
    $toDate = mysqli_real_escape_string($iconn, $toDate);
    $sql = "SELECT
            l.RmLotID,
            l.LotNumber,
            SUM(b.Weight) AS TotalBreakupWeight
        FROM RmArrivalDetatils a
        LEFT JOIN RmLotDetatils l ON a.RmArrivalID = l.RmReferenceID
        LEFT JOIN RmArrivalBreakUpDetails b ON l.BreakUpID = b.ArrivalBrkUpID
        LEFT JOIN RmPurchaseStations ps ON l.PurchaseStationID = ps.PurchaseStationID
        LEFT JOIN RmSuppliers s ON a.RmSupplierID = s.ID
        WHERE a.ArrivalDate BETWEEN '$fromDate' AND '$toDate'
        GROUP BY l.RmLotID, l.LotNumber
        ORDER BY TotalBreakupWeight DESC
        LIMIT 300";
    return qcPreviewFetchAll($iconn, $sql);
}

function qcPreviewGetLotsDetailsData($iconn, $fromDate, $toDate)
{
    $fromDate = mysqli_real_escape_string($iconn, $fromDate);
    $toDate = mysqli_real_escape_string($iconn, $toDate);
    $sql = "SELECT
            a.RmArrivalID,
            a.ArrivalDate,
            a.BillDate,
            a.LotStatus,
            l.RmLotID,
            l.LotNumber,
            l.PurchaseStationID,
            l.ProductionWeight,
            b.Weight AS BreakupWeight,
            s.FinancierName AS SupplierName,
            ps.StationName AS PurchaseStation,
            ps.Location AS PurchaseLocation,
            qrar.AnalysisDate,
            qrar.ReportDate,
            qrar.AOZ,
            qrar.SEM,
            qrar.CAP,
            qrar.AHD,
            qrar.AMOZ,
            qrar.Parameter,
            qrar.Result AS ab_result,
            b.Weight AS TotalBreakupWeight,
            CASE WHEN LOWER(qrar.Result) LIKE '%positive%' OR LOWER(qrar.Result) LIKE '%possitive%' THEN 'YES' WHEN qrar.Result IS NULL OR qrar.Result = '' THEN 'UNKNOWN' ELSE 'NO' END AS ab_positive
        FROM RmArrivalDetatils a
        LEFT JOIN RmLotDetatils l ON a.RmArrivalID = l.RmReferenceID
        LEFT JOIN RmArrivalBreakUpDetails b ON l.BreakUpID = b.ArrivalBrkUpID
        LEFT JOIN QcRmAbReport qrar ON qrar.LotID = l.RmLotID
        LEFT JOIN RmSuppliers s ON a.RmSupplierID = s.ID
        LEFT JOIN RmPurchaseStations ps ON l.PurchaseStationID = ps.PurchaseStationID
        WHERE a.ArrivalDate BETWEEN '$fromDate' AND '$toDate'
        ORDER BY TotalBreakupWeight DESC
        LIMIT 300";
    return qcPreviewFetchAll($iconn, $sql);
}

try {
    $financialYear = isset($_POST['FinancialYear']) ? intval($_POST['FinancialYear']) : intval(date('Y'));
    $sessionPlantId = isset($_SESSION['UnitID']) ? intval($_SESSION['UnitID']) : 0;
    $plantIds = $sessionPlantId !== 0
        ? (string) $sessionPlantId
        : qcPreviewCsvInts(isset($_POST['PlantID']) ? $_POST['PlantID'] : '');
    $poIds = qcPreviewCsvInts(isset($_POST['PoID']) ? $_POST['PoID'] : '');
    $buyerIds = qcPreviewCsvInts(isset($_POST['BuyerID']) ? $_POST['BuyerID'] : '');
    $countryIds = qcPreviewCsvInts(isset($_POST['DestinationCountryID']) ? $_POST['DestinationCountryID'] : '');
    $labNames = qcPreviewCsvStrings($iconn, isset($_POST['LabName']) ? $_POST['LabName'] : '');
    $status = isset($_POST['Status']) ? trim($_POST['Status']) : '';

    $fromDate = $financialYear . "-04-01";
    $toDate = ($financialYear + 1) . "-03-31";

    $where = array(
        "SPD.Status NOT IN (2,3,4)",
        "(SCI.StockOutDate IS NULL OR SCI.StockOutDate = '')"
    );

    if ($plantIds !== '') {
        $where[] = "SPD.ProductionPlantID IN ($plantIds)";
    }
    if ($poIds !== '') {
        $where[] = "SPD.PoID IN ($poIds)";
    }
    if ($buyerIds !== '') {
        $where[] = "SPD.ConsigneeID IN ($buyerIds)";
    }
    if ($countryIds !== '') {
        $where[] = "SPD.DestinationCountry IN ($countryIds)";
    }

    $outerWhere = array();
    if (count($labNames) > 0) {
        $labList = implode(',', $labNames);
        $outerWhere[] = "(InternalLabName IN ($labList) OR ExternalLabName IN ($labList))";
    }

    if ($status === 'qc_completed') {
        $outerWhere[] = "(IFNULL(DayCode, '') <> '' OR IFNULL(PsiID, '') <> '')";
    } else if ($status === 'qc_pending') {
        $outerWhere[] = "(IFNULL(DayCode, '') = '' AND IFNULL(PsiID, '') = '')";
    } else if ($status === 'lab_pending') {
        $outerWhere[] = "(IFNULL(InternalLabName, '') = '' AND IFNULL(ExternalLabName, '') = '')";
    } else if (strpos($status, 'lab_status:') === 0) {
        $labStatus = mysqli_real_escape_string($iconn, substr($status, strlen('lab_status:')));
        $outerWhere[] = "(InternalStatus = '$labStatus' OR ExternalStatus = '$labStatus')";
    }

    $innerWhereSql = implode(' AND ', $where);
    $outerWhereSql = count($outerWhere) > 0 ? " WHERE " . implode(' AND ', $outerWhere) : "";

    $sql = "SELECT *
        FROM (
            SELECT
                SPD.PoID,
                SPGD.ID AS PoGradeID,
                SPD.PoNo,
                CONCAT(VL.Grade, '-', VL.ProductDescription) AS ProductType,
                BM.BrandName AS Brand,
                CSP.PackingStyle,
                IFNULL(SPGD.Mcs, 0) AS OrderedMcs,
                IFNULL(CDPD.AssignedMcs, 0) AS AssignedMcs,
                IFNULL(CSD.PackedMcs, 0) AS PackedMcs,
                IFNULL(CSD.PackedMcs, 0) AS AvalMcs,
                ROUND(IFNULL(CDPD.AssignedMcs, 0) * 100 / NULLIF(SPGD.Mcs, 0), 2) AS AvailabilityPercentage,
                QC.DayCode,
                QC.PsiID,
                LAB.InternalLab AS InternalLabName,
                LAB.InternalDate AS InternalDate,
                LAB.InternalStatus AS InternalStatus,
                LAB.ExternalLab AS ExternalLabName,
                LAB.ExternalDate AS ExternalDate,
                LAB.ExternalStatus AS ExternalStatus,
                CASE WHEN IFNULL(CD.ConsigneeCode, '') <> '' THEN CD.ConsigneeCode ELSE CD.ConsigneeName END AS BuyerName,
                C.name AS FinalDestinationCountry,
                SPD.PoDate,
                SPD.ShipByDate,
                sampling_type AS SamplingType,
                LAB.ExpectedDate AS ExpectedSamplingDate
            FROM ScrmPoGradeData AS SPGD
            INNER JOIN ScrmPoData AS SPD ON SPD.PoID = SPGD.InvId
            LEFT JOIN ScrmCustomInvoices AS SCI ON SCI.PiID=SPD.PoID
            LEFT JOIN ScrmPIVarietyList AS VL ON VL.VarietyID = SPGD.VarietyID
            LEFT JOIN ScrmBrandsMaster AS BM ON BM.BrandID = VL.BrandName
            LEFT JOIN ScrmConsigneeDetails CD ON SPD.ConsigneeID = CD.ConID
            LEFT JOIN ScrmCountriesList C ON SPD.DestinationCountry = C.id
            LEFT JOIN AdminPackingStyles AS CSP ON CSP.PckID = SPGD.PackingStyleID
            LEFT JOIN (
                SELECT
                    CSD1.PoGradeId,
                    SUM(IFNULL(CSD1.ActualMcs, 0)) AS PackedMcs
                FROM CsStockData AS CSD1
                LEFT JOIN AdminPackingStyles AS APS ON APS.PckID = CSD1.PackingStyleID
                WHERE CSD1.PackingTypeID = 'MASTER'
                GROUP BY CSD1.PoGradeId
            ) AS CSD ON CSD.PoGradeId = SPGD.ID
            LEFT JOIN (
                SELECT
                    CDPD1.PoID,
                    CDPD1.PoGradeId,
                    ROUND(SUM(
                        IFNULL(CDPD1.ActualMcs, 0) +
                        IFNULL(CDPD1.ActualLooseBags, 0) / IFNULL(NULLIF(COALESCE(NULLIF(CDPD1.PackingCount, ''), APS2.TotalSlabs), 0), 1)
                    ), 2) AS AssignedMcs
                FROM CsDailyProductionData AS CDPD1
                LEFT JOIN ScrmPoGradeData AS SPGD2 ON SPGD2.ID = CDPD1.PoGradeId
                LEFT JOIN AdminPackingStyles AS APS2 ON APS2.PckID = SPGD2.PackingStyleID
                WHERE IFNULL(CDPD1.PoID, '') <> '' AND IFNULL(CDPD1.PoGradeId, 0) <> 0 AND IFNULL(CDPD1.ProductionTypeID, '') = 'Production'
                GROUP BY CDPD1.PoID, CDPD1.PoGradeId
            ) AS CDPD ON CDPD.PoID = SPD.PoID AND CDPD.PoGradeId = SPGD.ID
            LEFT JOIN (
                SELECT
                    PoGradeId,
                    GROUP_CONCAT(DISTINCT NULLIF(DayCode, '') ORDER BY DayCode SEPARATOR '\n') AS DayCode,
                    GROUP_CONCAT(DISTINCT NULLIF(PsiID, '') ORDER BY PsiID SEPARATOR '\n') AS PsiID
                FROM QcCodeList
                GROUP BY PoGradeId
            ) AS QC ON QC.PoGradeId = SPGD.ID
            LEFT JOIN (
                SELECT
                    SL.InvID AS poid,
                    PLST.type AS sampling_type,
                    SL.CompletedDate AS ExpectedDate,
                    MAX(CASE WHEN SL.SamplingType = 1 THEN SL.TestingLab END) AS InternalLab,
                    MAX(CASE WHEN SL.SamplingType = 1 THEN SL.Date END) AS InternalDate,
                    MAX(CASE WHEN SL.SamplingType = 1 THEN SL.Status END) AS InternalStatus,
                    MAX(CASE WHEN IFNULL(SL.SamplingType, 0) IN (0, 2) THEN SL.TestingLab END) AS ExternalLab,
                    MAX(CASE WHEN IFNULL(SL.SamplingType, 0) IN (0, 2) THEN SL.Date END) AS ExternalDate,
                    MAX(CASE WHEN IFNULL(SL.SamplingType, 0) IN (0, 2) THEN SL.Status END) AS ExternalStatus
                FROM QcSamplingLettersExt AS SL
                LEFT JOIN QcPoLabSamplingType AS PLST ON PLST.poid = SL.InvID
                WHERE (
                    (PLST.type = 1 AND SL.SamplingType = 1)
                    OR (PLST.type = 2 AND IFNULL(SL.SamplingType, 0) IN (0, 2))
                    OR (PLST.type = 3 AND IFNULL(SL.SamplingType, 0) IN (0, 1, 2))
                    OR PLST.type IS NULL
                )
                GROUP BY SL.InvID
            ) AS LAB ON LAB.poid = SPD.PoID
            WHERE $innerWhereSql
        ) AS LAB_AVAILABILITY
        $outerWhereSql
        ORDER BY DayCode DESC, PoID DESC";

    $data = qcPreviewFetchAll($iconn, $sql);
    $monthlyData = qcPreviewGetMonthlyData($iconn, $fromDate, $toDate);
    $centerWiseData = qcPreviewGetCenterWiseData($iconn, $fromDate, $toDate);
    $supplierData = qcPreviewGetSupplierWiseData($iconn, $fromDate, $toDate);
    $lotsData = qcPreviewGetLotsData($iconn, $fromDate, $toDate);
    $lotsDetailsData = qcPreviewGetLotsDetailsData($iconn, $fromDate, $toDate);

    echo json_encode(array(
        "Error" => null,
        "Data" => $data,
        "MonthlyData" => $monthlyData,
        "CenterWiseData" => $centerWiseData,
        "SupplierData" => $supplierData,
        "LotsData" => $lotsData,
        "LotsDetailsData" => $lotsDetailsData,
    ));
} catch (Exception $e) {
    echo json_encode(array(
        "Error" => $e->getMessage(),
        "Data" => array(),
        "MonthlyData" => array(),
        "CenterWiseData" => array(),
        "SupplierData" => array(),
        "LotsData" => array(),
        "LotsDetailsData" => array(),
    ));
}
?>