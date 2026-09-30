<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once 'UtilController.php';
class DashboardGraphsController
{

    private $conn;
    private $utilController;
    private $Error = null;
    private $Msg = '';

    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
    }
    public function getAntibioticGraphData($RequestBody)
    {
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        // SQL: Grouped counts by month
        $query = "
            SELECT 
            LotID, AnalysisDate,SampleDetails, AOZ,SEM,CAP,AHD,AMOZ
            FROM QcRmAbReport
            WHERE AnalysisDate BETWEEN '$FromDate' AND '$ToDate'
            ORDER BY AnalysisDate ASC
        ";

        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $dbRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // echo print_r($dbRows); exit;
        // Track distinct LotIDs per month
        $monthlyLotIdSet = [];
        $monthlyDetectedCount = [];

        foreach ($dbRows as $row) {
            $month = date('Y-m', strtotime($row['AnalysisDate']));
            $lotIds = explode(',', $row['LotID']);

            $AOZ = $row['AOZ'];
            $SEM = $row['SEM'];
            $CAP = $row['CAP'];
            $AHD = $row['AHD'];
            $AMOZ = $row['AMOZ'];
            foreach ($lotIds as $id) {
                if ($id !== '') {
                    if (isset($monthlyLotIdSet[$month][$id])) {
                        if (
                            (is_numeric($AOZ) && floatval($AOZ) > 0) ||
                            (is_numeric($SEM) && floatval($SEM) > 0) ||
                            (is_numeric($AMOZ) && floatval($AMOZ) > 0) ||
                            (is_numeric($AHD) && floatval($AHD) > 0) ||
                            (is_numeric($CAP) && floatval($CAP) > 0)
                        ) {
                            $monthlyLotIdSet[$month][$id] = true;
                        } else {
                            continue;
                        }
                    }
                    $monthlyLotIdSet[$month][$id] = true;
                    if (
                        (is_numeric($AOZ) && floatval($AOZ) > 0) ||
                        (is_numeric($SEM) && floatval($SEM) > 0) ||
                        (is_numeric($CAP) && floatval($CAP) > 0) ||
                        (is_numeric($AHD) && floatval($AHD) > 0) ||
                        (is_numeric($AMOZ) && floatval($AMOZ) > 0)
                    ) {
                        $monthlyDetectedCount[$month][$id] = true;
                    }
                }
            }
        }

        // Now count unique LotIDs per month
        $monthlyCounts = [];
        foreach ($monthlyLotIdSet as $month => $lotIdSet) {
            $monthlyCounts[] = [
                'ReportMonth' => $month,
                'testedSamples' => count($lotIdSet),
                'detectedSamples' => isset($monthlyDetectedCount[$month]) ? count(array_filter($monthlyDetectedCount[$month])) : 0
            ];
        }

        // Generate all months between range
        $allMonths = $this->getMonthRange($FromDate, $ToDate);

        // Fill with 0s first
        $monthsFormatted = array_keys($allMonths);
        $testedSamples = array_fill(0, count($monthsFormatted), 0);
        $detectedSamples = array_fill(0, count($monthsFormatted), 0);
        foreach ($monthlyCounts as $row) {
            $month = $row['ReportMonth'];
            if (isset($allMonths[$month])) {
                $index = array_search($month, $monthsFormatted);
                $testedSamples[$index] = (int) $row['testedSamples'];
                $detectedSamples[$index] = (int) $row['detectedSamples'];
            }
        }

        // Final structure for ApexCharts
        $chartData = [
            'months' => $monthsFormatted,
            'series' => [
                [
                    'name' => 'Tested Samples',
                    'data' => $testedSamples
                ],
                [
                    'name' => 'Detected Samples',
                    'data' => $detectedSamples
                ]
            ]
        ];

        $result = [];
        $result['Error'] = false;
        $result['Msg'] = "Fetch Antibiotic Data Success ";
        $result['Data'] = $chartData;
        return $result;
    }

    // Helper: Generate all months in Y-m format
    private function getMonthRange($start, $end)
    {
        $start = new DateTime(date('Y-m-01', strtotime($start)));
        $end = new DateTime(date('Y-m-01', strtotime($end)));
        $end->modify('+1 month');

        $months = [];
        while ($start < $end) {
            $months[$start->format('Y-m')] = true;
            $start->modify('+1 month');
        }

        return $months;
    }



    public function getAntibioticData($RequestBody)
    {
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $ReqType = $RequestBody['ReqType'];
        $result = [];
        if ($ReqType == "Total Samples") {
            $result['Error'] = false;
            $result['Msg'] = "Fetch Antibiotic Data Success ";
            $result['Data'] = [];
            return $result;
        }
        $DateFilter = " between '$FromDate' and '$ToDate'";

        $SqlFields = "LotNO,LotID,AnalysisDate,ReportDate,SampleDetails,SamplerName,QLTA.LbuName as AnalyzedBy,
        QLTA.LbuName as ReceivedBy , QLTV.LbuName as VerifiedBy,GatePassNo,Result,LabName,group_concat(DISTINCT Parameter) as Parameter,
        ExternalResult,ExternalIssuedResultDate,ExternalSampleSendingDate,ExternalLab,group_concat(DISTINCT AHD)AHD,group_concat(DISTINCT CAP)CAP,group_concat(DISTINCT SEM)SEM,
        group_concat(DISTINCT AMOZ)AMOZ,group_concat(DISTINCT AOZ)AOZ";
        $SqlAntibioticReportData = "SELECT $SqlFields FROM `QcRmAbReport` as QRAR 
            LEFT JOIN QcLabTechnologist AS QLTA ON QLTA.LUID = QRAR.AnalyzedBy 
            LEFT JOIN QcLabTechnologist AS QLTR ON QLTR.LUID = QRAR.ReceivedBy 
            LEFT JOIN QcLabTechnologist AS QLTV ON QLTV.LUID = QRAR.VerifiedBy 
            LEFT JOIN QcEiaLabNames AS QELN ON QELN.LabId = QRAR.ExternalLab 
            WHERE AnalysisDate $DateFilter GROUP BY SampleDetails,LotID
            order by AnalysisDate";
        // error_log("✅ SQL QcRmAbReport: " . print_r($SqlAntibioticReportData, true));
        $stmtAntibioticReportData = $this->conn->prepare($SqlAntibioticReportData);
        $stmtAntibioticReportData->execute();
        $i = 0; // Ensure $i is initialized
        $ArrayAntibioticReportData = [];

        if ($stmtAntibioticReportData) {
            $addedLotIDs = []; // Track already added LotIDs

            while ($dataRowAntibioticReportData = $stmtAntibioticReportData->fetch(PDO::FETCH_ASSOC)) {

                $LotIDs = !empty($dataRowAntibioticReportData['LotID']) ? explode(',', $dataRowAntibioticReportData['LotID']) : [];

                if (!empty($LotIDs)) {
                    foreach ($LotIDs as $lot) {
                        // echo "hii"; exit;
                        // $lot = trim($lot); 

                        $SqlLotNumber = "SELECT LotNumber FROM `RmLotDetatils` WHERE RmLotID in($lot)";
                        $StmtLotNumber = $this->conn->prepare($SqlLotNumber);
                        $StmtLotNumber->execute();
                        $dataRowLotNumber = $StmtLotNumber->fetch(PDO::FETCH_ASSOC);


                        // Ensure values are not NULL
                        $AOZ = isset($dataRowAntibioticReportData['AOZ']) ? trim($dataRowAntibioticReportData['AOZ'], ',') : null;
                        $SEM = isset($dataRowAntibioticReportData['SEM']) ? trim($dataRowAntibioticReportData['SEM'], ',') : null;
                        $AMOZ = isset($dataRowAntibioticReportData['AMOZ']) ? trim($dataRowAntibioticReportData['AMOZ'], ',') : null;
                        $AHD = isset($dataRowAntibioticReportData['AHD']) ? trim($dataRowAntibioticReportData['AHD'], ',') : null;
                        $CAP = isset($dataRowAntibioticReportData['CAP']) ? trim($dataRowAntibioticReportData['CAP'], ',') : null;
                        // build logic here to enter only of above values are numeric and >0
                       $AOZ1 = array_sum(array_map('floatval', explode(',', $AOZ)));
                       $SEM1 = array_sum(array_map('floatval', explode(',', $SEM)));
                       $AMOZ1 = array_sum(array_map('floatval', explode(',', $AMOZ)));
                       $AHD1 = array_sum(array_map('floatval', explode(',', $AHD)));
                       $CAP1 = array_sum(array_map('floatval', explode(',', $CAP)));
                       
                        if (in_array($lot, $addedLotIDs)) {
                            if (
                                (is_numeric($AOZ1) && floatval($AOZ1) > 0) ||
                                (is_numeric($SEM1) && floatval($SEM1) > 0) ||
                                (is_numeric($AMOZ1) && floatval($AMOZ1) > 0) ||
                                (is_numeric($AHD1) && floatval($AHD1) > 0) ||
                                (is_numeric($CAP1) && floatval($CAP1) > 0)
                            ) {
                                $ArrayData = [];
                                $ArrayData = $this->setData($dataRowLotNumber, $dataRowAntibioticReportData, $AOZ, $CAP, $AHD, $AMOZ, $SEM, $lot, ++$i);
                                $ArrayAntibioticReportData[$lot.'-'.$dataRowAntibioticReportData['SampleDetails']] = $ArrayData;
                                $addedLotIDs[] = $lot;
                            } else {
                                continue; // Skip if already added
                            }
                        }
                        if ($ReqType == "Detected Samples") {

                            if (
                                (is_numeric($AOZ1) && floatval($AOZ1) > 0) ||
                                (is_numeric($SEM1) && floatval($SEM1) > 0) ||
                                (is_numeric($AMOZ1) && floatval($AMOZ1) > 0) ||
                                (is_numeric($AHD1) && floatval($AHD1) > 0) ||
                                (is_numeric($CAP1) && floatval($CAP1) > 0)
                            ) {


                                $ArrayData = [];
                                $ArrayData = $this->setData($dataRowLotNumber, $dataRowAntibioticReportData, $AOZ, $CAP, $AHD, $AMOZ, $SEM, $lot, ++$i);
                                $ArrayAntibioticReportData[$lot.'-'.$dataRowAntibioticReportData['SampleDetails']] = $ArrayData;
                                $addedLotIDs[] = $lot;
                            }
                            // At least one value is numeric and > 0
                        } else {
                            $ArrayData = [];
                            $ArrayData = $this->setData($dataRowLotNumber, $dataRowAntibioticReportData, $AOZ, $CAP, $AHD, $AMOZ, $SEM, $lot, ++$i);

                            $ArrayAntibioticReportData[$lot.'-'.$dataRowAntibioticReportData['SampleDetails']] = $ArrayData;
                            $addedLotIDs[] = $lot;
                            // All values are either non-numeric or <= 0
                        }
                    }
                }
            }
            $ArrayAntibioticReportData = array_values($ArrayAntibioticReportData);
        }

        if (!empty($ArrayAntibioticReportData)) {
            $result['Error'] = false;
            $result['Msg'] = "Fetch Antibiotic Data Success ";
            $result['Data'] = $ArrayAntibioticReportData;
        } else {
            $result['Error'] = "No data found";
            $result['Msg'] = "Fetch Antibiotic Data Failed ";
            $result['Data'] = [];
        }

        return $result;
    }
    function formatValue($value)
    {
        return ($value !== "" && $value !== null) ? ($value > 0 ? "<span style='color:red;'>$value</span>" : $value) : "--";
    }
    // public function getMicroGraphData($RequestBody)
    // {
    // $FromDate = $RequestBody['FromDate'];
    // $ToDate = $RequestBody['ToDate'];

    // $sqlMicroSamples = "SELECT DATE_FORMAT(SampleDate, '%Y-%m') AS ReportMonth,COUNT(*) AS testedSamples FROM swabproducts WHERE SampleDate BETWEEN :fromDate AND :toDate  GROUP BY ReportMonth
    //         ORDER BY ReportMonth ASC";
    // $stmtMicroSamples = $this->conn->prepare($sqlMicroSamples);
    // $stmtMicroSamples->bindParam(':fromDate', $FromDate);
    // $stmtMicroSamples->bindParam(':toDate', $ToDate);
    // $stmtMicroSamples->execute();
    // $microSamplesRow = $stmtMicroSamples->fetchAll(PDO::FETCH_ASSOC);

    // $allMonths = $this->getMonthRange($FromDate, $ToDate);

    // // Fill with 0s first
    // $monthsFormatted = array_keys($allMonths);
    // $testedSamples = array_fill(0, count($monthsFormatted), 0);
    // $detectedSamples = array_fill(0, count($monthsFormatted), 0);


    // foreach ($microSamplesRow as $testedRow) {
    // $testedMonth = $testedRow['ReportMonth'];
    // if (isset($allMonths[$testedMonth])) {
    // $testedIndex = array_search($testedMonth, $monthsFormatted);
    // $testedSamples[$testedIndex] = (int)$testedRow['testedSamples'];
    // }
    // }

    // $chartData = [
    // 'months' => $monthsFormatted,
    // 'series' => [
    // [
    // 'name' => 'Tested Samples',
    // 'data' => $testedSamples
    // ],
    // [
    // 'name' => 'Detected Samples',
    // 'data' => $detectedSamples
    // ]
    // ]
    // ];

    // $result = [];
    // $result['Error'] = false;
    // $result['Msg'] = "Fetch Micro Graph Details Success ";
    // $result['Data'] = $chartData;
    // return $result;
    // }


    public function getMicroGraphData($RequestBody)
    {
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];

        // 🔹 Check if OnlyDetected filter is requested
        $onlyDetected = isset($RequestBody['OnlyDetected']) && $RequestBody['OnlyDetected'] == '1';

        // Base SQL to fetch all rows in date range
        $sql = "SELECT ReportDate, TpcResult, EcoliResult, SaureusResult,
                   VcholeraeResult, VparahaemolyticusResult, Salmonella
            FROM swabproducts
            WHERE ReportDate BETWEEN :fromDate AND :toDate";

        // Append detection filter if OnlyDetected is true
        if ($onlyDetected) {
            $searchQueryParts[] = "(
        (TpcResult REGEXP '^[0-9]+$' AND CAST(TpcResult AS UNSIGNED) > 70000)
        OR UPPER(TpcResult) = 'TNTC'
        OR (EcoliResult REGEXP '<([0-9]+)CFU' AND CAST(REGEXP_SUBSTR(EcoliResult, '[0-9]+') AS UNSIGNED) > 20)
        OR (SaureusResult REGEXP '<([0-9]+)CFU' AND CAST(REGEXP_SUBSTR(SaureusResult, '[0-9]+') AS UNSIGNED) > 10)
        OR LOWER(VcholeraeResult) = 'positive'
        OR LOWER(VparahaemolyticusResult) = 'positive'
        OR LOWER(Salmonella) = 'positive'
    )";
        }


        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':fromDate', $FromDate);
        $stmt->bindParam(':toDate', $ToDate);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // If no rows found
        if (!$rows || count($rows) === 0) {
            return [
                'Error' => false,
                'Msg' => 'No Micro Data Found in Date Range',
                'Data' => null // <-- return null if no data
            ];
        }
        // Generate months range
        $allMonths = $this->getMonthRange($FromDate, $ToDate);
        $monthsFormatted = array_keys($allMonths);
        $testedSamples = array_fill(0, count($monthsFormatted), 0);
        $detectedSamples = array_fill(0, count($monthsFormatted), 0);
        $monthIndexMap = array_flip($monthsFormatted);

        foreach ($rows as $row) {
            $month = date('Y-m', strtotime($row['ReportDate']));
            if (!isset($monthIndexMap[$month]))
                continue;
            $idx = $monthIndexMap[$month];

            // Count tested sample
            $testedSamples[$idx]++;

            // Detection logic
            $isDetected = false;

            // TPC
            if ((is_numeric($row['TpcResult']) && intval($row['TpcResult']) > 70000) || strtoupper(trim($row['TpcResult'])) === 'TNTC') {
                $isDetected = true;
            }

            // Staphylococcus aureus
            if (preg_match('/([\d\.]+)/', $row['SaureusResult'], $m) && floatval($m[1]) > 10)
                $isDetected = true;
            elseif (stripos($row['SaureusResult'], 'positive') !== false)
                $isDetected = true;

            // E.Coli
            if (preg_match('/([\d\.]+)/', $row['EcoliResult'], $m) && floatval($m[1]) > 20)
                $isDetected = true;
            elseif (stripos($row['EcoliResult'], 'positive') !== false)
                $isDetected = true;

            // Salmonella
            if (stripos($row['Salmonella'], 'positive') !== false || stripos($row['Salmonella'], 'present') !== false)
                $isDetected = true;

            // Vibrio Cholerae
            if (stripos($row['VcholeraeResult'], 'positive') !== false || stripos($row['VcholeraeResult'], 'present') !== false)
                $isDetected = true;

            // Vibrio Parahaemolyticus
            if (stripos($row['VparahaemolyticusResult'], 'positive') !== false || stripos($row['VparahaemolyticusResult'], 'present') !== false)
                $isDetected = true;

            // Count detected
            if ($isDetected)
                $detectedSamples[$idx]++;
        }

        return [
            'Error' => false,
            'Msg' => 'Fetch Micro Graph Details Success',
            'Data' => [
                'months' => $monthsFormatted,
                'series' => [
                    ['name' => 'Tested Samples', 'data' => $testedSamples],
                    ['name' => 'Detected Samples', 'data' => $detectedSamples]
                ]
            ]
        ];
    }










    public function getMicroData($RequestBody)
    {
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];

        $onlyDetected = isset($RequestBody['OnlyDetected']) && $RequestBody['OnlyDetected'] == '1';

        // Fetch all reference data
        $allLots = $this->fetchLookupMap($this->conn, "SELECT RmLotID, LotNumber FROM RmLotDetatils", 'RmLotID', ['LotNumber']);
        $allPlants = $this->fetchLookupMap($this->conn, "SELECT ProcessingStationID, StationName FROM RmProcessingStations", 'ProcessingStationID', ['StationName']);
        $allVarieties = $this->fetchLookupMap($this->conn, "SELECT ProductID, ShortName FROM ScrmProductList", 'ProductID', ['ShortName']);
        $allParameters = $this->fetchLookupMap($this->conn, "SELECT TestID, TestName FROM QcTestList", 'TestID', ['TestName']);
        $allMedia = $this->fetchLookupMap($this->conn, "SELECT MediaID, MediaName FROM usedmediamaster", 'MediaID', ['MediaName']);
        $allInstruments = $this->fetchLookupMap($this->conn, "SELECT InstrumentId, InstrumentName FROM instrumentmaster", 'InstrumentId', ['InstrumentName']);

        // Userlist names
        $sqlQuery = "SELECT MIN(LUID) as USER_ID, LbuName as USER_NAME FROM `QcLabTechnologist` WHERE LbuStatus=1 Group by LbuName ";
        $stmtUsers = $this->conn->query($sqlQuery);
        $RowUsersLists = [];
        while ($RowUsers = $stmtUsers->fetch_array()) {
            $RowUsersLists[$RowUsers['USER_ID']] = $RowUsers['USER_NAME'];
        }
        //end here

        $request = $_REQUEST;

        $columns = ['BatchNumber', 'Plant', 'SampleDate', 'DrawnBy', 'GatePassNo', 'AnalysisDate', 'ReportDate', 'Variety', 'Parameter', 'NameMediaUsed', 'EquipmentUsed', 'TpcResult', 'EcoliResult', 'SaureusResult', 'VcholeraeResult', 'VparahaemolyticusResult', 'Salmonella', 'CheckedBy', 'VerfiedBy', 'Remarks'];



        $searchValue = $this->conn->real_escape_string($request['search']['value']);

        $searchQueryParts = [];

        if (!empty($FromDate) && !empty($ToDate)) {
            $searchQueryParts[] = " ReportDate BETWEEN '$FromDate' and '$ToDate' ";
        }

        if (!empty($searchValue)) {
            $searchQueryParts[] = "(" . $columns[0] . " LIKE '%" . $searchValue . "%' OR " . $columns[1] . " LIKE '%" . $searchValue . "%' OR " . $columns[2] . " LIKE '%" . $searchValue . "%'  OR " . $columns[3] . " LIKE '%" . $searchValue . "%' OR " . $columns[4] . " LIKE '%" . $searchValue . "%' OR " . $columns[5] . " LIKE '%" . $searchValue . "%' OR " . $columns[6] . " LIKE '%" . $searchValue . "%' OR " . $columns[7] . " LIKE '%" . $searchValue . "%' OR " . $columns[8] . " LIKE '%" . $searchValue . "%')";
        }



        if ($onlyDetected) {
            $searchQueryParts[] = "(
        (TpcResult REGEXP '^[0-9]+$' AND CAST(TpcResult AS UNSIGNED) > 70000)
        OR UPPER(TpcResult) = 'TNTC'
        OR EcoliResult REGEXP '<([0-9]+)CFU' AND CAST(REGEXP_SUBSTR(EcoliResult, '[0-9]+') AS UNSIGNED) > 20
        OR SaureusResult REGEXP '<([0-9]+)CFU' AND CAST(REGEXP_SUBSTR(SaureusResult, '[0-9]+') AS UNSIGNED) > 10
        OR LOWER(VcholeraeResult) = 'positive'
        OR LOWER(VparahaemolyticusResult) = 'positive'
        OR LOWER(Salmonella) = 'positive'
    )";
        }



        $searchQuery = count($searchQueryParts) > 0 ? ' AND ' . implode(' AND ', $searchQueryParts) : '';

        $data = [];
        $sql = "SELECT * from swabproducts where 1=1 $searchQuery";
        //echo $sql; exit;
        $result = $this->conn->query($sql);
        if ($result && $result->num_rows > 0) {
            $i = 0;
            while ($RowLeaveList = $result->fetch_assoc()) {
                $i++;
                $singlerow = array();

                $singlerow['Sno'] = $i;

                // Batch number(s)
                $batchIDs = explode(",", $RowLeaveList['BatchNumber']);
                $batchNames = array_map(function ($id) use ($allLots) {
                    return $allLots[$id]['LotNumber'] ? $allLots[$id]['LotNumber'] : '';
                }, $batchIDs);
                $singlerow['BatchNumber'] = implode(",", array_filter($batchNames));
                $singlerow['SampleDate'] = $RowLeaveList['SampleDate'];
                $singlerow['DrawnBy'] = $RowUsersLists[$RowLeaveList['DrawnBy']] ? $RowUsersLists[$RowLeaveList['DrawnBy']] : '';

                $singlerow['AnalysisDate'] = $RowLeaveList['AnalysisDate'];
                $singlerow['ReportDate'] = $RowLeaveList['ReportDate'];
                $singlerow['Variety'] = $allVarieties[$RowLeaveList['Variety']]['ShortName'] ? $allVarieties[$RowLeaveList['Variety']]['ShortName'] : '';
                if ($fromPage == "") {
                    $singlerow['Plant'] = $allPlants[$RowLeaveList['Plant']]['StationName'] ? $allPlants[$RowLeaveList['Plant']]['StationName'] : '';

                    $singlerow['Parameter'] = $allParameters[$RowLeaveList['Parameter']]['TestName'] ? $allParameters[$RowLeaveList['Parameter']]['TestName'] : '';
                    $singlerow['NameMediaUsed'] = $allMedia[$RowLeaveList['NameMediaUsed']]['MediaName'] ? $allMedia[$RowLeaveList['NameMediaUsed']]['MediaName'] : '';
                    $singlerow['EquipmentUsed'] = $allInstruments[$RowLeaveList['EquipmentUsed']]['InstrumentName'] ? $allInstruments[$RowLeaveList['ec_equipmentUsed']]['InstrumentName'] : '';
                    $tpc_bgcolor = $this->setColor($RowLeaveList['TpcResult']);
                    $singlerow['TpcResult'] = "<span style='background-color:" . $tpc_bgcolor . "'>" . $RowLeaveList['TpcResult'] . "<span>";
                    $singlerow['tp_completed_date'] = $RowLeaveList['tp_completed_date'];

                    $singlerow['ec_parameter'] = $allParameters[$RowLeaveList['ec_parameter']]['TestName'] ? $allParameters[$RowLeaveList['ec_parameter']]['TestName'] : '';
                    $singlerow['ec_nameMediaUsed'] = $allMedia[$RowLeaveList['ec_nameMediaUsed']]['MediaName'] ? $allMedia[$RowLeaveList['ec_nameMediaUsed']]['MediaName'] : '';
                    $singlerow['ec_equipmentUsed'] = $allInstruments[$RowLeaveList['ec_equipmentUsed']]['InstrumentName'] ? $allInstruments[$RowLeaveList['ec_equipmentUsed']]['InstrumentName'] : '';
                    $ec_bgcolor = $this->setColor($RowLeaveList['EcoliResult']);
                    $singlerow['EcoliResult'] = "<span style='background-color:" . $ec_bgcolor . "'>" . $RowLeaveList['EcoliResult'] . "<span>";
                    //$singlerow['EcoliResult'] = $RowLeaveList['EcoliResult'];
                    $singlerow['ec_completed_date'] = $RowLeaveList['ec_completed_date'];

                    $singlerow['sa_parameter'] = $allParameters[$RowLeaveList['sa_parameter']]['TestName'] ? $allParameters[$RowLeaveList['sa_parameter']]['TestName'] : '';
                    $singlerow['sa_nameMediaUsed'] = $allMedia[$RowLeaveList['sa_nameMediaUsed']]['MediaName'] ? $allMedia[$RowLeaveList['sa_nameMediaUsed']]['MediaName'] : '';
                    $singlerow['sa_equipmentUsed'] = $allInstruments[$RowLeaveList['sa_equipmentUsed']]['InstrumentName'] ? $allInstruments[$RowLeaveList['sa_equipmentUsed']]['InstrumentName'] : '';
                    $sa_bgcolor = $this->setColor($RowLeaveList['SaureusResult']);
                    $singlerow['SaureusResult'] = "<span style='background-color:" . $sa_bgcolor . "'>" . $RowLeaveList['SaureusResult'] . "<span>";
                    //$singlerow['SaureusResult'] = $RowLeaveList['SaureusResult'];
                    $singlerow['sa_completed_date'] = $RowLeaveList['sa_completed_date'];

                    $singlerow['vc_parameter'] = $allParameters[$RowLeaveList['vc_parameter']]['TestName'] ? $allParameters[$RowLeaveList['vc_parameter']]['TestName'] : '';
                    $singlerow['vc_nameMediaUsed'] = $allMedia[$RowLeaveList['vc_nameMediaUsed']]['MediaName'] ? $allMedia[$RowLeaveList['vc_nameMediaUsed']]['MediaName'] : '';
                    $singlerow['vc_equipmentUsed'] = $allInstruments[$RowLeaveList['vc_equipmentUsed']]['InstrumentName'] ? $allInstruments[$RowLeaveList['vc_equipmentUsed']]['InstrumentName'] : '';
                    $vc_bgcolor = $this->setColor($RowLeaveList['VcholeraeResult']);
                    $singlerow['VcholeraeResult'] = "<span style='background-color:" . $vc_bgcolor . "'>" . $RowLeaveList['VcholeraeResult'] . "<span>";
                    //$singlerow['VcholeraeResult'] = $RowLeaveList['VcholeraeResult'];
                    $singlerow['vc_completed_date'] = $RowLeaveList['vc_completed_date'];

                    $singlerow['vp_parameter'] = $allParameters[$RowLeaveList['vp_parameter']]['TestName'] ? $allParameters[$RowLeaveList['vp_parameter']]['TestName'] : '';
                    $singlerow['vp_nameMediaUsed'] = $allMedia[$RowLeaveList['vp_nameMediaUsed']]['MediaName'] ? $allMedia[$RowLeaveList['vp_nameMediaUsed']]['MediaName'] : '';
                    $singlerow['vp_equipmentUsed'] = $allInstruments[$RowLeaveList['vp_equipmentUsed']]['InstrumentName'] ? $allInstruments[$RowLeaveList['vp_equipmentUsed']]['InstrumentName'] : '';
                    $vp_bgcolor = $this->setColor($RowLeaveList['VparahaemolyticusResult']);
                    $singlerow['VparahaemolyticusResult'] = "<span style='background-color:" . $vp_bgcolor . "'>" . $RowLeaveList['VparahaemolyticusResult'] . "<span>";
                    //$singlerow['VparahaemolyticusResult'] = $RowLeaveList['VparahaemolyticusResult'];
                    $singlerow['vp_completed_date'] = $RowLeaveList['vp_completed_date'];

                    $singlerow['sal_parameter'] = $allParameters[$RowLeaveList['sal_parameter']]['TestName'] ? $allParameters[$RowLeaveList['sal_parameter']]['TestName'] : '';
                    $singlerow['sal_nameMediaUsed'] = $allMedia[$RowLeaveList['sal_nameMediaUsed']]['MediaName'] ? $allMedia[$RowLeaveList['sal_nameMediaUsed']]['MediaName'] : '';
                    $singlerow['sal_equipmentUsed'] = $allInstruments[$RowLeaveList['sal_equipmentUsed']]['InstrumentName'] ? $allInstruments[$RowLeaveList['sal_equipmentUsed']]['InstrumentName'] : '';
                    $sal_bgcolor = $this->setColor($RowLeaveList['Salmonella']);
                    $singlerow['Salmonella'] = "<span style='background-color:" . $sal_bgcolor . "'>" . $RowLeaveList['Salmonella'] . "<span>";
                    //$singlerow['Salmonella'] = $RowLeaveList['Salmonella'];
                    $singlerow['sal_completed_date'] = $RowLeaveList['sal_completed_date'];

                    $singlerow['analysed_by'] = $RowUsersLists[$RowLeaveList['analysed_by']] ? $RowUsersLists[$RowLeaveList['analysed_by']] : '';
                    $singlerow['CheckedBy'] = $RowUsersLists[$RowLeaveList['CheckedBy']] ? $RowUsersLists[$RowLeaveList['CheckedBy']] : '';
                    $singlerow['VerfiedBy'] = $RowUsersLists[$RowLeaveList['VerfiedBy']] ? $RowUsersLists[$RowLeaveList['VerfiedBy']] : '';
                    $singlerow['Remarks'] = $RowLeaveList['Remarks'];
                } else {
                    if ($fromPage == "Create") {
                        $singlerow['Action'] = "<a href='in-house-microbiology-report-for-product-create?FromDate=" . $FromDate . "&ToDate=" . $ToDate . "&id=" . $RowLeaveList['id'] . "'><i class='mdi mdi-pencil font-size-16'></i></a>
                            <button class='btn btn-link text-danger  btnDelete' style='position: relative;top: -3px' data-id='" . $RowLeaveList['id'] . "' data-toggle='tooltip' data-placement='top' title='' value='' data-original-title='Delete'>
                                <i class='mdi mdi-trash-can font-size-16'></i></button>
                        ";
                    } else if ($fromPage == "Listpage") {
                        $singlerow['Action'] = "<a href='in-house-microbiology-report-for-product-create?FromDate=" . $RowLeaveList['AnalysisDate'] . "&ToDate=" . $RowLeaveList['AnalysisDate'] . "'><i class='mdi mdi-plus font-size-16'></i></a>";
                    }
                }
                $data[] = $singlerow;
            }
        }
        $result = [];
        $result['Error'] = false;
        $result['Msg'] = "Fetch Micro Details Success ";
        $result['Data'] = $data;
        return $result;
    }
    function fetchLookupMap($iconn, $query, $keyField, $valueFields)
    {
        $result = $iconn->query($query);
        $map = [];
        while ($row = $result->fetch_assoc()) {
            $key = $row[$keyField];
            $value = [];
            foreach ($valueFields as $field) {
                $value[$field] = $row[$field];
            }
            $map[$key] = $value;
        }
        return $map;
    }

    function setColor($val)
    {
        $color = "";
        if (is_numeric($val) && $val > 70000) {
            $color = "Red";
        } else if (strtolower($val) == "positive") {
            $color = "Red";
        } else if (strtolower($val) == "suspect") {
            $color = "Orange";
        }
        return $color;
    }
    private function setData($dataRowLotNumber, $dataRowAntibioticReportData, $AOZ, $CAP, $AHD, $AMOZ, $SEM, $lot, $i)
    {
        $ArrayData['SNO'] = $i;
        // $ArrayData['BatchNumber'] = $dataRowAntibioticReportData['LotNO'];
        $ArrayData['BatchNumber'] = $dataRowLotNumber['LotNumber'];

        $ArrayData['LotID'] = $lot;

        if (!$ArrayData['BatchNumber']) {
            $ArrayData['BatchNumber'] = $ArrayData['LotID'];
        }
        // Formatted dates
        $ArrayData['AnalysisDate'] = $this->utilController->getFormatedDateLines($dataRowAntibioticReportData['AnalysisDate']);
        $ArrayData['CompletionDate'] = $this->utilController->getFormatedDateLines($dataRowAntibioticReportData['ReportDate']);

        // Other details
        $ArrayData['SampleType'] = $dataRowAntibioticReportData['SampleDetails'];
        $ArrayData['SampleDetails'] = $dataRowAntibioticReportData['GatePassNo'];
        $ArrayData['SamplerName'] = $dataRowAntibioticReportData['SamplerName'];
        $ArrayData['AnalyzedBy'] = $dataRowAntibioticReportData['AnalyzedBy'];
        $ArrayData['ReceivedBy'] = $dataRowAntibioticReportData['ReceivedBy'];
        $ArrayData['VerifiedBy'] = $dataRowAntibioticReportData['VerifiedBy'];

        // Conditional styling for values > 0


        $ArrayData['AOZ'] = $this->formatValue($AOZ);
        $ArrayData['CAP'] = $this->formatValue($CAP);
        $ArrayData['AHD'] = $this->formatValue($AHD);
        $ArrayData['AMOZ'] = $this->formatValue($AMOZ);
        $ArrayData['SEM'] = $this->formatValue($SEM);

        // External lab details
        $ArrayData['ExternalLab'] = $dataRowAntibioticReportData['LabName'];
        $ArrayData['ExternalResult'] = $dataRowAntibioticReportData['ExternalResult'];
        $ArrayData['ExternalSampleSendingDate'] = $this->utilController->getFormatedDateLines($dataRowAntibioticReportData['ExternalSampleSendingDate']);
        $ArrayData['ExternalIssuedResultDate'] = $this->utilController->getFormatedDateLines($dataRowAntibioticReportData['ExternalIssuedResultDate']);
        $ArrayData['Result'] = $dataRowAntibioticReportData['Result'];

        return $ArrayData;
    }


    // public function getSwabGraphData($RequestBody)
    // {
    // $FromDate = $RequestBody['FromDate'];
    // $ToDate = $RequestBody['ToDate'];

    // $swabStaticValues = array(
    // array('parameters' => 'WORKERS SWAB', 'InternalLimit' => 80, 'Vibrio Cholerae' => 'ABSENT'),
    // array('parameters' => 'FOOD CONTACT SURFACES SWAB', 'InternalLimit' => 80, 'Vibrio Cholerae' => '-'),
    // array('parameters' => 'FLATE ICE & PLATE ICE CHUTE', 'InternalLimit' => 80, 'Vibrio Cholerae' => 'ABSENT'),
    // );

    // $sql = "SELECT tpc_paramater, tpc_result, vcholerae_result, tpc_completion_date
    //             FROM micro_biology_workers_hand
    //             WHERE SampleDate BETWEEN :fromDate AND :toDate
    //             ORDER BY SampleDate ASC";

    // $stmt = $this->conn->prepare($sql);
    // $stmt->bindParam(':fromDate', $FromDate);
    // $stmt->bindParam(':toDate', $ToDate);
    // $stmt->execute();
    // $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // $allMonths = $this->getMonthRange($FromDate, $ToDate);
    // $monthsFormatted = array_keys($allMonths);

    // $testedSamplesArr = array_fill(0, count($monthsFormatted), 0);
    // $detectedSamplesArr = array_fill(0, count($monthsFormatted), 0);

    // $monthlyTested = array();
    // $monthlyDetected = array();

    // foreach ($rows as $row) {
    // $month = date('Y-m', strtotime($row['tpc_completion_date']));

    // if (!isset($monthlyTested[$month])) {
    // $monthlyTested[$month] = 0;
    // $monthlyDetected[$month] = 0;
    // }

    // // Count as tested
    // $monthlyTested[$month]++;

    // // Find matching parameter
    // $paramLimit = null;
    // foreach ($swabStaticValues as $limit) {
    // if ($limit['parameters'] === $row['tpc_paramater']) {
    // $paramLimit = $limit;
    // break;
    // }
    // }

    // $tpcDetected = false;
    // $vcholeraeDetected = false;

    // if ($paramLimit) {
    // // TPC detection
    // if (is_numeric($row['tpc_result']) && $row['tpc_result'] > $paramLimit['InternalLimit']) {
    // $tpcDetected = true;
    // }

    // // Vibrio Cholerae detection
    // if ($paramLimit['Vibrio Cholerae'] !== '-' &&
    // strtoupper($row['vcholerae_result']) !== strtoupper($paramLimit['Vibrio Cholerae'])) {
    // $vcholeraeDetected = true;
    // }
    // }

    // if ($tpcDetected || $vcholeraeDetected) {
    // $monthlyDetected[$month]++;
    // }
    // }

    // foreach ($monthsFormatted as $idx => $month) {
    // if (isset($monthlyTested[$month])) {
    // $testedSamplesArr[$idx] = $monthlyTested[$month];
    // } else {
    // $testedSamplesArr[$idx] = 0;
    // }

    // if (isset($monthlyDetected[$month])) {
    // $detectedSamplesArr[$idx] = $monthlyDetected[$month];
    // } else {
    // $detectedSamplesArr[$idx] = 0;
    // }
    // }

    // return array(
    // 'Error' => false,
    // 'Msg' => 'Fetch Swab Graph Data Success',
    // 'Data' => array(
    // 'months' => $monthsFormatted,
    // 'series' => array(
    // array('name' => 'Tested Samples', 'data' => $testedSamplesArr),
    // array('name' => 'Detected Samples', 'data' => $detectedSamplesArr)
    // )
    // )
    // );
    // }









    // Apply OnlyDetected filter in SQL for numeric TPC and standard V. cholerae / coliform


    public function getSwabGraphData($RequestBody)
    {
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];

        // -----------------------------
        // 1. Fetch Workers Swab Data
        // -----------------------------
        $sqlWorkers = "SELECT vcholerae_completion_date ,  
        tpc_result AS TPC_Result_Hand,
        vcholerae_result AS VCholerae_Result_Hand
                   FROM micro_biology_workers_hand 
                   WHERE vcholerae_completion_date BETWEEN :fromDate AND :toDate 
                   ORDER BY vcholerae_completion_date ASC";

        $stmtWorkers = $this->conn->prepare($sqlWorkers);
        $stmtWorkers->bindParam(':fromDate', $FromDate);
        $stmtWorkers->bindParam(':toDate', $ToDate);
        $stmtWorkers->execute();
        $workersRows = $stmtWorkers->fetchAll(PDO::FETCH_ASSOC);

        // -----------------------------
        // 2. Fetch Food Contact Surface Data
        // -----------------------------
        $sqlFoodContact = "SELECT Dateofcompletion, tpc_result AS TPC_Result_Surface
                       FROM micro_biology_workers_food_surface 
                       WHERE Dateofcompletion BETWEEN :fromDate AND :toDate 
                       ORDER BY Dateofcompletion ASC";
        $stmtFoodContact = $this->conn->prepare($sqlFoodContact);
        $stmtFoodContact->bindParam(':fromDate', $FromDate);
        $stmtFoodContact->bindParam(':toDate', $ToDate);
        $stmtFoodContact->execute();
        $foodContactRows = $stmtFoodContact->fetchAll(PDO::FETCH_ASSOC);

        // -----------------------------
        // 3. Fetch Flake Ice & Plate Ice Chute Data
        // -----------------------------
        $sqlFlakeIce = "SELECT Dateofcompletion,instant_swab,tpc_result,vcholerae_result
                     FROM micro_biology_ice_chutes_water_tanks 
                     WHERE Dateofcompletion BETWEEN :fromDate AND :toDate 
                     ORDER BY Dateofcompletion ASC";
        $stmtFlakeIce = $this->conn->prepare($sqlFlakeIce);
        $stmtFlakeIce->bindParam(':fromDate', $FromDate);
        $stmtFlakeIce->bindParam(':toDate', $ToDate);
        $stmtFlakeIce->execute();
        $flakeIceRows = $stmtFlakeIce->fetchAll(PDO::FETCH_ASSOC);

        // -----------------------------
        // 4. Generate complete month range
        // -----------------------------
        $allMonths = $this->getMonthRange($FromDate, $ToDate);
        $monthsFormatted = array_keys($allMonths);

        // -----------------------------
        // 5. Initialize series data
        // -----------------------------
        $seriesData = [];
        foreach ($monthsFormatted as $month) {
            $seriesData[$month] = [
                'month' => $month,
                'Workers Swab' => 0,
                'FOOD CONTACT SURFACES SWAB' => 0,
                'FLATE ICE & PLATE ICE CHUTE' => 0,
                'Detected Samples' => 0
            ];
        }

        // -----------------------------
        // 6. Populate Workers Swab counts
        // -----------------------------
        foreach ($workersRows as $row) {

            $TPC_Result_Hand = $row['TPC_Result_Hand'];
            $valTrim = trim($TPC_Result_Hand);
            $detected = false;
            if (is_numeric($valTrim) || preg_match('/\d+(\.\d+)?/', $valTrim, $matches)) {
                $num = is_numeric($valTrim) ? floatval($valTrim) : floatval($matches[0]);
                if ($num > 80) {
                    $detected = true;
                }
            } elseif (strtoupper($valTrim) === "TNTC") {
                $detected = true;
            }


            $VCholerae_Result_Hand = isset($row['VCholerae_Result_Hand']) ? $row['VCholerae_Result_Hand'] : '';

            if (strtolower(trim($VCholerae_Result_Hand)) === "positive") {
                $detected = true;
            }


            if (!empty($row['vcholerae_completion_date'])) {
                $month = date('Y-m', strtotime($row['vcholerae_completion_date']));
                if (isset($seriesData[$month])) {
                    $seriesData[$month]['Workers Swab']++;
                }

                if ($detected) {
                    $seriesData[$month]['Detected Samples']++;
                }
            }
        }

        // -----------------------------
        // 7. Populate Food Contact Surface counts
        // -----------------------------
        foreach ($foodContactRows as $row) {
            $tpc_surface_color = isset($row['TPC_Result_Surface']) ? $row['TPC_Result_Surface'] : '';
            $valTrim = trim($tpc_surface_color);
            $detected = false;
            // Check numeric > 80
            if (preg_match('/\d+(\.\d+)?/', $valTrim, $m)) {
                $num = (float) $m[0];
                if ($num > 80) {
                    $detected = true;
                }
            }
            // Check TNTC (case-insensitive)
            elseif (strtoupper($valTrim) === "TNTC") {
                $detected = true;

            }


            if (!empty($row['Dateofcompletion'])) {
                $month = date('Y-m', strtotime($row['Dateofcompletion']));
                if (isset($seriesData[$month])) {
                    $seriesData[$month]['FOOD CONTACT SURFACES SWAB']++;
                }
                if ($detected) {
                    $seriesData[$month]['Detected Samples']++;
                }
            }


        }


        // -----------------------------
        // 8. Populate Flake Ice counts
        // -----------------------------
        foreach ($flakeIceRows as $row) {
            $detected = false;

            if (
                $this->is_detected('InstantSwab', isset($row['instant_swab']) ? $row['instant_swab'] : null)
                || $this->is_detected('TpcResult', isset($row['tpc_result']) ? $row['tpc_result'] : null)
                || $this->is_detected('VcholeraeResult', isset($row['vcholerae_result']) ? $row['vcholerae_result'] : null)
            ) {
                $detected = true;

            }


            if (!empty($row['Dateofcompletion'])) {

                $month = date('Y-m', strtotime($row['Dateofcompletion']));
                if (isset($seriesData[$month])) {
                    $seriesData[$month]['FLATE ICE & PLATE ICE CHUTE']++;
                }

                if ($detected) {
                    $seriesData[$month]['Detected Samples']++;
                }
            }
        }

        // -----------------------------
        // 9. Final series array
        // -----------------------------
        $finalSeries = array_values($seriesData);

        // -----------------------------
        // 10. Return result
        // -----------------------------
        return [
            'Error' => false,
            'Msg' => 'Fetch Swab Graph Data Success',
            'Data' => [
                'months' => $monthsFormatted,
                'series' => $finalSeries
            ]
        ];
    }





    public function getSwabData($RequestBody)
    {
        $FromDate = $RequestBody['FromDate'] . ' 00:00:00';
        $ToDate = $RequestBody['ToDate'] . ' 23:59:59';



        $swabStaticValues = [
            ['parameters' => 'WORKERS SWAB', 'InternalLimit' => 80, 'Vibrio Cholerae' => 'ABSENT'],
            ['parameters' => 'FOOD CONTACT SURFACES SWAB', 'InternalLimit' => 80, 'Vibrio Cholerae' => '-'],
            ['parameters' => 'FLATE ICE & PLATE ICE CHUTE', 'InternalLimit' => 80, 'Vibrio Cholerae' => 'ABSENT'],
        ];

        $sql = "SELECT *
            FROM micro_biology_workers_hand
            WHERE vcholerae_completion_date BETWEEN :fromDate AND :toDate
            ORDER BY vcholerae_completion_date DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':fromDate', $FromDate);
        $stmt->bindParam(':toDate', $ToDate);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $formattedData = [];
        $sno = 1;

        foreach ($rows as $row) {
            $tpcDetected = false;
            $vcholeraeDetected = false;

            // Match parameter
            $paramLimit = null;
            foreach ($swabStaticValues as $limit) {
                if ($limit['parameters'] === $row['tpc_paramater']) {
                    $paramLimit = $limit;
                    break;
                }
            }

            if ($paramLimit) {
                if (is_numeric($row['tpc_result']) && $row['tpc_result'] > $paramLimit['InternalLimit']) {
                    $tpcDetected = true;
                }

                if (
                    $paramLimit['Vibrio Cholerae'] !== '-' &&
                    strtoupper($row['vcholerae_result']) !== strtoupper($paramLimit['Vibrio Cholerae'])
                ) {
                    $vcholeraeDetected = true;
                }
            }

            $formattedData[] = [
                'Sno' => $sno++,
                'id' => $row['mbwh_idPrimary'],
                'sample_drawn_by' => $row['sample_drawn_by'],
                'sample_collection_area' => $row['sample_collection_area'],
                'name_food_worker_hand' => $row['name_food_worker_hand'],
                'emp_id' => $row['emp_id'],
                'tpc_paramater' => $row['tpc_paramater'],
                'tpc_media' => $row['tpc_media'],
                'tpc_Kitlotno' => $row['tpc_Kitlotno'],
                'tpc_UsedWells' => $row['tpc_UsedWells'],
                'tpc_equipement' => $row['tpc_equipement'],
                'tpc_result' => $row['tpc_result'],
                'tpc_completion_date' => $row['tpc_completion_date'],
                'vcholerae_paramater' => $row['vcholerae_paramater'],
                'vcholerae_media' => $row['vcholerae_media'],
                'vcholerae_Kitlotno' => $row['vcholerae_Kitlotno'],
                'vcholerae_UsedWells' => $row['vcholerae_UsedWells'],
                'vcholerae_equipement' => $row['vcholerae_equipement'],
                'vcholerae_result' => $row['vcholerae_result'],
                'vcholerae_completion_date' => $row['vcholerae_completion_date'],
                'analysed_by' => $row['analysed_by'],
                'checked_by' => $row['checked_by'],
                'verified_by' => $row['verified_by'],
                'remarks' => $row['remarks'],
                'user_id' => $row['user_id'],
                'created_date' => $row['created_date'],
                'update_date' => $row['update_date'],
                'tpc_detected' => $tpcDetected ? 1 : 0,
                'vcholerae_detected' => $vcholeraeDetected ? 1 : 0
            ];
        }





        return [
            'Error' => false,
            'Msg' => 'Fetch Swab Data Success',
            'Data' => $formattedData
        ];
    }






    // public function getWaterIceGraphData($RequestBody)
    // {
    // $FromDateTime = $RequestBody['FromDate'] . ' 00:00:00';
    // $ToDateTime = $RequestBody['ToDate'] . ' 23:59:59';

    // // Water & Ice static values as reference limits
    // $waterIceStaticValues = [
    // ['parameters' => '22°C', 'InternalLimit' => 50, 'Vibrio Cholerae' => 'ABSENT', 'Coliforms' => 'NIL'],
    // ['parameters' => '37°C', 'InternalLimit' => 15, 'Vibrio Cholerae' => 'ABSENT', 'Coliforms' => 'NIL'],
    // // add more parameters if needed
    // ];

    // $sql = "
    //     SELECT 
    //         DATE_FORMAT(tpc_completion_date, '%Y-%m') AS ReportMonth,
    //         tpc_paramater, tpc_result1, tpc_result2,
    //         vcholerae_result1, vcholerae_result2
    //     FROM micro_biology_water_ice
    //     WHERE sample_collection IS NOT NULL
    //       AND sample_collection BETWEEN :fromDate AND :toDate
    //     ORDER BY sample_collection ASC
    // ";

    // $stmt = $this->conn->prepare($sql);
    // $stmt->bindParam(':fromDate', $FromDateTime);
    // $stmt->bindParam(':toDate', $ToDateTime);
    // $stmt->execute();
    // $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // $allMonths = $this->getMonthRange($FromDateTime, $ToDateTime);
    // $monthsFormatted = array_keys($allMonths);

    // $testedSamplesArr = array_fill(0, count($monthsFormatted), 0);
    // $detectedSamplesArr = array_fill(0, count($monthsFormatted), 0);

    // $graphData = [];

    // foreach ($rows as $row) {
    // $month = $row['ReportMonth'];

    // if (!isset($graphData[$month])) {
    // $graphData[$month] = ['testedSamples' => 0, 'detectedSamples' => 0];
    // }

    // // Every row is a tested sample
    // $graphData[$month]['testedSamples']++;

    // $tpcDetected = false;
    // $vcholeraeDetected = false;

    // // Find the matching parameter in static values
    // $param = $row['tpc_paramater'];
    // $limit = null;
    // foreach ($waterIceStaticValues as $l) {
    // if ($l['parameters'] == $param) {
    // $limit = $l;
    // break;
    // }
    // }

    // if ($limit) {
    // // TPC detection
    // if ((isset($row['tpc_result1']) && is_numeric($row['tpc_result1']) && $row['tpc_result1'] > $limit['InternalLimit']) ||
    // (isset($row['tpc_result2']) && is_numeric($row['tpc_result2']) && $row['tpc_result2'] > $limit['InternalLimit'])
    // ) {
    // $tpcDetected = true;
    // }

    // // Vibrio Cholerae detection
    // if ((isset($row['vcholerae_result1']) && strtoupper($row['vcholerae_result1']) !== strtoupper($limit['Vibrio Cholerae'])) ||
    // (isset($row['vcholerae_result2']) && strtoupper($row['vcholerae_result2']) !== strtoupper($limit['Vibrio Cholerae']))
    // ) {
    // $vcholeraeDetected = true;
    // }
    // }

    // if ($tpcDetected || $vcholeraeDetected) {
    // $graphData[$month]['detectedSamples']++;
    // }
    // }

    // foreach ($monthsFormatted as $idx => $month) {
    // if (isset($graphData[$month])) {
    // $testedSamplesArr[$idx] = $graphData[$month]['testedSamples'];
    // $detectedSamplesArr[$idx] = $graphData[$month]['detectedSamples'];
    // }
    // }

    // return [
    // 'Error' => false,
    // 'Msg' => 'Fetch Water & Ice Graph Data Success',
    // 'Data' => [
    // 'months' => $monthsFormatted,
    // 'series' => [
    // ['name' => 'Tested Samples', 'data' => $testedSamplesArr],
    // ['name' => 'Detected Samples', 'data' => $detectedSamplesArr]
    // ]
    // ]
    // ];
    // }



    // public function getWaterIceGraphData($RequestBody)
    // {
    // $FromDate = isset($RequestBody['FromDate']) ? $RequestBody['FromDate'] : null;
    // $ToDate = isset($RequestBody['ToDate']) ? $RequestBody['ToDate'] : null;

    // if (!$FromDate || !$ToDate) {
    // return array(
    // 'Error' => true,
    // 'Msg' => 'FromDate or ToDate not provided',
    // 'Data' => array()
    // );
    // }

    // // Internal limits for deduction
    // $internalLimits = array(
    // '22°C' => 50,
    // '37°C' => 15
    // );

    // $sql = "
    //         SELECT 
    //             DATE_FORMAT(sample_collection, '%Y-%m') AS ReportMonth,
    //             tpc_paramater,
    //             tpc_result1,
    //             tpc_result2,
    //             vcholerae_result1,
    //             vcholerae_result2
    //         FROM micro_biology_water_ice
    //         WHERE sample_collection IS NOT NULL
    //           AND sample_collection BETWEEN :fromDate AND :toDate
    //         ORDER BY sample_collection ASC
    //     ";

    // $stmt = $this->conn->prepare($sql);
    // $stmt->bindParam(':fromDate', $FromDate);
    // $stmt->bindParam(':toDate', $ToDate);
    // $stmt->execute();
    // $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // $allMonths = $this->getMonthRange($FromDate, $ToDate);
    // $monthsFormatted = array_keys($allMonths);

    // $testedTPC1 = array_fill(0, count($monthsFormatted), 0);
    // $testedTPC2 = array_fill(0, count($monthsFormatted), 0);
    // $deductions = array_fill(0, count($monthsFormatted), 0);

    // $monthData = array();

    // foreach ($rows as $row) {
    // $month = isset($row['ReportMonth']) ? $row['ReportMonth'] : '';
    // if ($month == '') continue;

    // $param = isset($row['tpc_paramater']) ? $row['tpc_paramater'] : '';
    // $limit = isset($internalLimits[$param]) ? $internalLimits[$param] : null;

    // $r1 = (isset($row['tpc_result1']) && is_numeric($row['tpc_result1'])) ? floatval($row['tpc_result1']) : null;
    // $r2 = (isset($row['tpc_result2']) && is_numeric($row['tpc_result2'])) ? floatval($row['tpc_result2']) : null;

    // // Create month bucket if not exist
    // if (!isset($monthData[$month])) {
    // $monthData[$month] = array(
    // 'testedTPC1' => 0,
    // 'testedTPC2' => 0,
    // 'deduction' => 0
    // );
    // }

    // // Count TPC tests
    // if ($r1 !== null) $monthData[$month]['testedTPC1']++;
    // if ($r2 !== null) $monthData[$month]['testedTPC2']++;

    // // Deduction logic
    // $deductionFlag = false;

    // // Internal limit check
    // if ($limit !== null && (
    // ($r1 !== null && $r1 > $limit) ||
    // ($r2 !== null && $r2 > $limit)
    // )) {
    // $deductionFlag = true;
    // }

    // // Vibrio Cholerae presence check
    // if ((isset($row['vcholerae_result1']) && strtoupper(trim($row['vcholerae_result1'])) !== 'ABSENT') ||
    // (isset($row['vcholerae_result2']) && strtoupper(trim($row['vcholerae_result2'])) !== 'ABSENT')) {
    // $deductionFlag = true;
    // }

    // if ($deductionFlag) {
    // $monthData[$month]['deduction']++;
    // }
    // }

    // // Fill arrays for chart
    // foreach ($monthsFormatted as $idx => $month) {
    // if (isset($monthData[$month])) {
    // $testedTPC1[$idx] = $monthData[$month]['testedTPC1'];
    // $testedTPC2[$idx] = $monthData[$month]['testedTPC2'];
    // $deductions[$idx] = $monthData[$month]['deduction'];
    // }
    // }

    // return array(
    // 'Error' => false,
    // 'Msg' => 'Fetch Water & Ice Graph Data Success',
    // 'Data' => array(
    // 'months' => $monthsFormatted,
    // 'series' => array(
    // array('name' => 'TPC Result 1', 'data' => $testedTPC1),
    // array('name' => 'TPC Result 2', 'data' => $testedTPC2)
    // // array('name' => 'Deductions', 'data' => $deductions, 'color' => '#e74c3c') // optional
    // )
    // )
    // );
    // }




    public function getWaterIceGraphData($RequestBody)
    {
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $onlyDetected = !empty($RequestBody['OnlyDetected']) && $RequestBody['OnlyDetected'] == 1;

        // Internal limits for deduction
        $internalLimits = array(
            '22°C' => 50,
            '37°C' => 15
        );

        // Base SQL
        $sql = "
        SELECT 
            DATE_FORMAT(cholerae_completion_date, '%Y-%m') AS ReportMonth,
            tpc_paramater,
            tpc_result1,
            tpc_result2,
            vcholerae_result1,
            vcholerae_result2,
            cholerae_result1
        FROM micro_biology_water_ice
        WHERE cholerae_completion_date IS NOT NULL
          AND cholerae_completion_date BETWEEN :fromDate AND :toDate
    ";

        // Apply OnlyDetected filter in SQL
        if ($onlyDetected) {
            $sql .= " AND (
            (tpc_paramater LIKE '%22%' AND ((tpc_result1 IS NOT NULL AND tpc_result1 > 50) OR (tpc_result2 IS NOT NULL AND tpc_result2 > 50)))
            OR (tpc_paramater LIKE '%37%' AND ((tpc_result1 IS NOT NULL AND tpc_result1 > 15) OR (tpc_result2 IS NOT NULL AND tpc_result2 > 15)))
            OR (vcholerae_result1 IS NOT NULL AND TRIM(UPPER(vcholerae_result1)) <> 'ABSENT/100ML' AND TRIM(vcholerae_result1) <> '')
            OR (cholerae_result1 IS NOT NULL AND TRIM(UPPER(cholerae_result1)) <> '<1MPN/ML' AND TRIM(cholerae_result1) <> '')
        )";
        }

        $sql .= " ORDER BY cholerae_completion_date ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':fromDate', $FromDate);
        $stmt->bindParam(':toDate', $ToDate);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Prepare month buckets
        $allMonths = $this->getMonthRange($FromDate, $ToDate);
        $monthsFormatted = array_keys($allMonths);

        $testedTPC1 = array_fill(0, count($monthsFormatted), 0);
        $testedTPC2 = array_fill(0, count($monthsFormatted), 0);
        $deductions = array_fill(0, count($monthsFormatted), 0);

        $monthData = array();

        foreach ($rows as $row) {
            $month = isset($row['ReportMonth']) ? $row['ReportMonth'] : '';
            if ($month === '')
                continue;

            $r1 = (isset($row['tpc_result1']) && is_numeric($row['tpc_result1'])) ? floatval($row['tpc_result1']) : null;
            $r2 = (isset($row['tpc_result2']) && is_numeric($row['tpc_result2'])) ? floatval($row['tpc_result2']) : null;

            if (!isset($monthData[$month])) {
                $monthData[$month] = array(
                    'testedTPC1' => 0,
                    'testedTPC2' => 0,
                    'deduction' => 0
                );
            }

            // Count tested TPC samples
            if ($r1 !== null)
                $monthData[$month]['testedTPC1']++;
            if ($r2 !== null)
                $monthData[$month]['testedTPC2']++;

            // Deduction detection (check all TPC independently)
            $deductionFlag = false;

            // TPC 37°C limit check
            if ($r1 !== null && $r1 > $internalLimits['37°C']) {
                $deductionFlag = true;
            }

            // TPC 22°C limit check
            if ($r2 !== null && $r2 > $internalLimits['22°C']) {
                $deductionFlag = true;
            }

            // Vibrio Cholerae detection
            if (
                (isset($row['vcholerae_result1']) && strtoupper(trim($row['vcholerae_result1'])) !== 'ABSENT/100ML' && $row['vcholerae_result1'] !== '') ||
                (isset($row['vcholerae_result2']) && strtoupper(trim($row['vcholerae_result2'])) !== 'ABSENT/100ML' && $row['vcholerae_result2'] !== '')
            ) {
                $deductionFlag = true;
            }

            // Coliform / Cholera detection
            if (isset($row['cholerae_result1']) && strtoupper(trim($row['cholerae_result1'])) !== '<1MPN/ML' && $row['cholerae_result1'] !== '') {
                $deductionFlag = true;
            }

            if (
                (isset($row['tpc_result1']) && trim($row['tpc_result1']) === 'TNTC') ||
                (isset($row['tpc_result2']) && trim($row['tpc_result2']) === 'TNTC')
            ) {
                $deductionFlag = true;
            }

            if ($deductionFlag) {
                $monthData[$month]['deduction']++;
            }
        }

        // Prepare arrays for chart output
        foreach ($monthsFormatted as $idx => $month) {
            if (isset($monthData[$month])) {
                $testedTPC1[$idx] = $monthData[$month]['testedTPC1'];
                $testedTPC2[$idx] = $monthData[$month]['testedTPC2'];
                $deductions[$idx] = $monthData[$month]['deduction'];
            }
        }

        return array(
            'Error' => false,
            'Msg' => 'Fetch Water & Ice Graph Data Success',
            'Data' => array(
                'months' => $monthsFormatted,
                'series' => array(
                    // array('name' => 'TPC Result 1', 'data' => $testedTPC1),
                    // array('name' => 'TPC Result 2', 'data' => $testedTPC2),
                    array('name' => 'TPC 37°C', 'data' => $testedTPC1),
                    array('name' => 'TPC 22°C', 'data' => $testedTPC2),

                    array('name' => 'Detected Samples', 'data' => $deductions, 'color' => '#e74c3c')
                )
            )
        );
    }





    // public function getWaterIceData($RequestBody)
    // {
    // $FromDateTime = $RequestBody['FromDate'] . ' 00:00:00';
    // $ToDateTime = $RequestBody['ToDate'] . ' 23:59:59';

    // // Water & Ice static values as reference limits
    // $waterIceStaticValues = [
    // ['parameters' => '22°C', 'InternalLimit' => 50, 'Vibrio Cholerae' => 'ABSENT', 'Coliforms' => 'NIL'],
    // ['parameters' => '37°C', 'InternalLimit' => 15, 'Vibrio Cholerae' => 'ABSENT', 'Coliforms' => 'NIL'],
    // // add more parameters if needed
    // ];

    // $sql = "
    //     SELECT *
    //     FROM micro_biology_water_ice
    //     WHERE sample_collection BETWEEN :fromDate AND :toDate
    //     ORDER BY sample_collection DESC
    // ";

    // $stmt = $this->conn->prepare($sql);
    // $stmt->bindParam(':fromDate', $FromDateTime);
    // $stmt->bindParam(':toDate', $ToDateTime);
    // $stmt->execute();
    // $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // $formattedData = [];
    // $i = 0;

    // foreach ($rows as $row) {
    // $i++;

    // $tpcDetected = false;
    // $vcholeraeDetected = false;

    // // Match the row parameter with static limits
    // $param = $row['tpc_paramater'];
    // $limit = null;
    // foreach ($waterIceStaticValues as $l) {
    // if ($l['parameters'] == $param) {
    // $limit = $l;
    // break;
    // }
    // }

    // if ($limit) {
    // // TPC detection
    // if ((isset($row['tpc_result1']) && is_numeric($row['tpc_result1']) && $row['tpc_result1'] > $limit['InternalLimit']) ||
    // (isset($row['tpc_result2']) && is_numeric($row['tpc_result2']) && $row['tpc_result2'] > $limit['InternalLimit'])
    // ) {
    // $tpcDetected = true;
    // }

    // // Vibrio Cholerae detection
    // if ((isset($row['vcholerae_result1']) && strtoupper($row['vcholerae_result1']) !== strtoupper($limit['Vibrio Cholerae'])) ||
    // (isset($row['vcholerae_result2']) && strtoupper($row['vcholerae_result2']) !== strtoupper($limit['Vibrio Cholerae']))
    // ) {
    // $vcholeraeDetected = true;
    // }
    // }

    // $formattedData[] = [
    // 'Sno' => $i,
    // 'mbwi_idPrimary' => $row['mbwi_idPrimary'],
    // 'report_type' => $row['report_type'],
    // 'sample_drawn_by' => $row['sample_drawn_by'],
    // 'sample_collection' => $row['sample_collection'],
    // 'plant_id' => $row['plant_id'],
    // 'sample_desc' => $row['sample_desc'],
    // 'instant_swab' => $row['instant_swab'],
    // 'location' => $row['location'],
    // 'tap_no_for_water' => $row['tap_no_for_water'],
    // 'itype_of_ice' => $row['itype_of_ice'],

    // 'tpc_paramater' => $row['tpc_paramater'],
    // 'tpc_media' => $row['tpc_media'],
    // 'tpc_Kitlotno' => $row['tpc_Kitlotno'],
    // 'tpc_UsedWells' => $row['tpc_UsedWells'],
    // 'tpc_equipement' => $row['tpc_equipement'],
    // 'tpc_result1' => $row['tpc_result1'],
    // 'tpc_result2' => $row['tpc_result2'],
    // 'tpc_completion_date' => $row['tpc_completion_date'],

    // 'vcholerae_paramater' => $row['vcholerae_paramater'],
    // 'vcholerae_media' => $row['vcholerae_media'],
    // 'vcholerae_Kitlotno' => $row['vcholerae_Kitlotno'],
    // 'vcholerae_UsedWells' => $row['vcholerae_UsedWells'],
    // 'vcholerae_equipement' => $row['vcholerae_equipement'],
    // 'vcholerae_result1' => $row['vcholerae_result1'],
    // 'vcholerae_result2' => $row['vcholerae_result2'],
    // 'vcholerae_completion_date' => $row['vcholerae_completion_date'],

    // 'cholerae_paramater' => $row['cholerae_paramater'],
    // 'cholerae_media' => $row['cholerae_media'],
    // 'cholerae_Kitlotno' => $row['cholerae_Kitlotno'],
    // 'cholerae_UsedWells' => $row['cholerae_UsedWells'],
    // 'cholerae_equipement' => $row['cholerae_equipement'],
    // 'cholerae_result1' => $row['cholerae_result1'],
    // 'cholerae_completion_date' => $row['cholerae_completion_date'],

    // 'analysed_by' => $row['analysed_by'],
    // 'checked_by' => $row['checked_by'],
    // 'verified_by' => $row['verified_by'],
    // 'remarks' => $row['remarks'],
    // 'user_id' => $row['user_id'],
    // 'created_date' => $row['created_date'],
    // 'update_date' => $row['update_date'],

    // // flags using static limits
    // 'tpc_detected' => $tpcDetected ? 1 : 0,
    // 'vcholerae_detected' => $vcholeraeDetected ? 1 : 0
    // ];
    // }

    // return [
    // 'Error' => false,
    // 'Msg' => 'Fetch Water & Ice Data Success',
    // 'Data' => $formattedData
    // ];
    // }





    // public function getWaterIceData($RequestBody)
    // {
    // $FromDate = $RequestBody['FromDate'];
    // $ToDate = $RequestBody['ToDate'];

    // // ✅ Water & Ice internal limits
    // $waterIceLimits = [
    // '22°C' => [
    // 'InternalLimit' => 50,
    // 'Vibrio Cholerae' => 'ABSENT',
    // 'Coliforms' => 'NIL'
    // ],
    // '37°C' => [
    // 'InternalLimit' => 15,
    // 'Vibrio Cholerae' => 'ABSENT',
    // 'Coliforms' => 'NIL'
    // ],
    // ];

    // $sql = "
    //         SELECT *
    //         FROM micro_biology_water_ice
    //         WHERE sample_collection BETWEEN :fromDate AND :toDate
    //         ORDER BY sample_collection DESC
    //     ";

    // $stmt = $this->conn->prepare($sql);
    // $stmt->bindParam(':fromDate', $FromDate);
    // $stmt->bindParam(':toDate', $ToDate);
    // $stmt->execute()
    // $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // $formattedData = [];
    // $i = 0;

    // foreach ($rows as $row) {
    // $i++;

    // // Flags for detection
    // $tpcDetected = false;
    // $vcholeraeDetected = false;
    // $coliformDetected = false;
    // $deduction = false;

    // // Get limits for the current parameter (22°C or 37°C)
    // $param = $row['tpc_paramater'];
    // $limit = isset($waterIceLimits[$param]) ? $waterIceLimits[$param] : null;

    // if ($limit) {
    // // TPC check
    // $tpc1 = is_numeric($row['tpc_result1']) ? (float)$row['tpc_result1'] : null;
    // $tpc2 = is_numeric($row['tpc_result2']) ? (float)$row['tpc_result2'] : null;

    // if (($tpc1 !== null && $tpc1 > $limit['InternalLimit']) ||
    // ($tpc2 !== null && $tpc2 > $limit['InternalLimit'])) {
    // $tpcDetected = true;
    // $deduction = true;
    // }

    // // vibrio Cholerae check
    // if ((isset($row['vcholerae_result1']) && strcasecmp(trim($row['vcholerae_result1']), $limit['Vibrio Cholerae']) !== 0) ||
    // (isset($row['vcholerae_result2']) && strcasecmp(trim($row['vcholerae_result2']), $limit['Vibrio Cholerae']) !== 0)) {
    // $vcholeraeDetected = true;
    // $deduction = true;
    // }

    // // Coliform check
    // if ((isset($row['coliform_result1']) && strcasecmp(trim($row['coliform_result1']), $limit['Coliforms']) !== 0) ||
    // (isset($row['coliform_result2']) && strcasecmp(trim($row['coliform_result2']), $limit['Coliforms']) !== 0)) {
    // $coliformDetected = true;
    // $deduction = true;
    // }
    // }

    // // Prepare formatted row
    // $formattedData[] = [
    // 'Sno' => $i,
    // 'mbwi_idPrimary' => $row['mbwi_idPrimary'],
    // 'report_type' => $row['report_type'],
    // 'sample_drawn_by' => $row['sample_drawn_by'],
    // 'sample_collection' => $row['sample_collection'],
    // 'plant_id' => $row['plant_id'],
    // 'sample_desc' => $row['sample_desc'],
    // 'instant_swab' => $row['instant_swab'],
    // 'location' => $row['location'],
    // 'tap_no_for_water' => $row['tap_no_for_water'],
    // 'itype_of_ice' => $row['itype_of_ice'],

    // 'tpc_paramater' => $row['tpc_paramater'],
    // 'tpc_media' => $row['tpc_media'],
    // 'tpc_Kitlotno' => $row['tpc_Kitlotno'],
    // 'tpc_UsedWells' => $row['tpc_UsedWells'],
    // 'tpc_equipement' => $row['tpc_equipement'],
    // 'tpc_result1' => $row['tpc_result1'],
    // 'tpc_result2' => $row['tpc_result2'],
    // 'tpc_completion_date' => $row['tpc_completion_date'],

    // 'vcholerae_paramater' => $row['vcholerae_paramater'],
    // 'vcholerae_media' => $row['vcholerae_media'],
    // 'vcholerae_Kitlotno' => $row['vcholerae_Kitlotno'],
    // 'vcholerae_UsedWells' => $row['vcholerae_UsedWells'],
    // 'vcholerae_equipement' => $row['vcholerae_equipement'],
    // 'vcholerae_result1' => $row['vcholerae_result1'],
    // 'vcholerae_result2' => $row['vcholerae_result2'],
    // 'vcholerae_completion_date' => $row['vcholerae_completion_date'],

    // 'cholerae_paramater' => $row['cholerae_paramater'],
    // 'cholerae_media' => $row['cholerae_media'],
    // 'cholerae_Kitlotno' => $row['cholerae_Kitlotno'],
    // 'cholerae_UsedWells' => $row['cholerae_UsedWells'],
    // 'cholerae_equipement' => $row['cholerae_equipement'],
    // 'cholerae_result1' => $row['cholerae_result1'],
    // 'cholerae_completion_date' => $row['cholerae_completion_date'],

    // 'analysed_by' => $row['analysed_by'],
    // 'checked_by' => $row['checked_by'],
    // 'verified_by' => $row['verified_by'],
    // 'remarks' => $row['remarks'],
    // 'user_id' => $row['user_id'],
    // 'created_date' => $row['created_date'],
    // 'update_date' => $row['update_date'],

    // // Flags
    // 'tpc_detected' => $tpcDetected ? 1 : 0,
    // 'vcholerae_detected' => $vcholeraeDetected ? 1 : 0,
    // 'coliform_detected' => $coliformDetected ? 1 : 0,
    // 'deduction' => $deduction ? 1 : 0 // Combined Deduction Flag
    // ];
    // }

    // return [
    // 'Error' => false,
    // 'Msg' => 'Fetch Water & Ice Data Success',
    // 'Data' => $formattedData
    // ];
    // }







    public function getWaterIceData($RequestBody)
    {
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $onlyDetected = !empty($RequestBody['OnlyDetected']) && $RequestBody['OnlyDetected'] == 1;

        // Define detection limits
        $waterIceLimits = [
            '22°C' => [
                'InternalLimit' => 50,
                'Vibrio Cholerae' => 'ABSENT',
                'Coliforms' => 'NIL'
            ],
            '37°C' => [
                'InternalLimit' => 15,
                'Vibrio Cholerae' => 'ABSENT',
                'Coliforms' => 'NIL'
            ],
        ];

        // Base SQL with date filter
        $sql = "
        SELECT *
        FROM micro_biology_water_ice
        WHERE cholerae_completion_date BETWEEN :fromDate AND :toDate
    ";

        // Apply OnlyDetected filter in SQL for numeric TPC and standard V. cholerae / coliform
        if ($onlyDetected) {
            $sql .= " AND (
        (LOWER(tpc_paramater) LIKE '%35%' AND ((tpc_result1 IS NOT NULL AND tpc_result1 > 15) OR (tpc_result2 IS NOT NULL AND tpc_result2 > 15)))
        OR (LOWER(tpc_paramater) LIKE '%22%' AND ((tpc_result1 IS NOT NULL AND tpc_result1 > 50) OR (tpc_result2 IS NOT NULL AND tpc_result2 > 50)))
        OR (vcholerae_result1 IS NOT NULL AND TRIM(UPPER(vcholerae_result1)) <> 'ABSENT/100ML' AND TRIM(vcholerae_result1) <> '')
        OR (cholerae_result1 IS NOT NULL AND TRIM(UPPER(cholerae_result1)) <> '<1MPN/ML' AND TRIM(cholerae_result1) <> '')
    )";
        }



        $sql .= " ORDER BY cholerae_completion_date DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':fromDate', $FromDate);
        $stmt->bindParam(':toDate', $ToDate);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $formattedData = [];
        $i = 0;

        foreach ($rows as $row) {
            $i++;

            $tpcDetected = false;
            $vcholeraeDetected = false;
            $coliformDetected = false;
            $deduction = false;

            $param = $row['tpc_paramater'];
            $limit = isset($waterIceLimits[$param]) ? $waterIceLimits[$param] : null;

            if ($limit) {
                $tpc1 = is_numeric($row['tpc_result1']) ? (float) $row['tpc_result1'] : null;
                $tpc2 = is_numeric($row['tpc_result2']) ? (float) $row['tpc_result2'] : null;

                // TPC detection
                if (
                    ($tpc1 !== null && $tpc1 > $limit['InternalLimit']) ||
                    ($tpc2 !== null && $tpc2 > $limit['InternalLimit'])
                ) {
                    $tpcDetected = true;
                    $deduction = true;
                }

                // Vibrio Cholerae detection
                if (
                    (isset($row['vcholerae_result1']) && strcasecmp(trim($row['vcholerae_result1']), $limit['Vibrio Cholerae']) !== 0 && trim($row['vcholerae_result1']) !== '') ||
                    (isset($row['vcholerae_result2']) && strcasecmp(trim($row['vcholerae_result2']), $limit['Vibrio Cholerae']) !== 0 && trim($row['vcholerae_result2']) !== '')
                ) {
                    $vcholeraeDetected = true;
                    $deduction = true;
                }

                // Coliform / Cholera detection
                if (
                    (isset($row['cholerae_result1']) && strtoupper(trim($row['cholerae_result1'])) !== '<1MPN/ML' && trim($row['cholerae_result1']) !== '')
                    // (isset($row['cholerae_result2']) && strcasecmp(trim($row['cholerae_result2']), $limit['Coliforms']) !== 0 && trim($row['cholerae_result2']) !== '')
                ) {
                    $coliformDetected = true;
                    $deduction = true;
                }
            }

            $formattedData[] = [
                'Sno' => $i,
                'mbwi_idPrimary' => $row['mbwi_idPrimary'],
                'report_type' => $row['report_type'],
                'sample_drawn_by' => $row['sample_drawn_by'],
                'sample_collection' => $row['sample_collection'],
                'plant_id' => $row['plant_id'],
                'sample_desc' => $row['sample_desc'],
                'instant_swab' => $row['instant_swab'],
                'location' => $row['location'],
                'tap_no_for_water' => $row['tap_no_for_water'],
                'itype_of_ice' => $row['itype_of_ice'],

                'tpc_paramater' => $row['tpc_paramater'],
                'tpc_media' => $row['tpc_media'],
                'tpc_Kitlotno' => $row['tpc_Kitlotno'],
                'tpc_UsedWells' => $row['tpc_UsedWells'],
                'tpc_equipement' => $row['tpc_equipement'],
                'tpc_result1' => $row['tpc_result1'],
                'tpc_result2' => $row['tpc_result2'],
                'tpc_completion_date' => $row['tpc_completion_date'],

                'vcholerae_paramater' => $row['vcholerae_paramater'],
                'vcholerae_media' => $row['vcholerae_media'],
                'vcholerae_Kitlotno' => $row['vcholerae_Kitlotno'],
                'vcholerae_UsedWells' => $row['vcholerae_UsedWells'],
                'vcholerae_equipement' => $row['vcholerae_equipement'],
                'vcholerae_result1' => $row['vcholerae_result1'],
                'vcholerae_result2' => $row['vcholerae_result2'],
                'vcholerae_completion_date' => $row['vcholerae_completion_date'],

                'cholerae_paramater' => $row['cholerae_paramater'],
                'cholerae_media' => $row['cholerae_media'],
                'cholerae_Kitlotno' => $row['cholerae_Kitlotno'],
                'cholerae_UsedWells' => $row['cholerae_UsedWells'],
                'cholerae_equipement' => $row['cholerae_equipement'],
                'cholerae_result1' => $row['cholerae_result1'],
                'cholerae_completion_date' => $row['cholerae_completion_date'],

                'analysed_by' => $row['analysed_by'],
                'checked_by' => $row['checked_by'],
                'verified_by' => $row['verified_by'],
                'remarks' => $row['remarks'],
                'user_id' => $row['user_id'],
                'created_date' => $row['created_date'],
                'update_date' => $row['update_date'],

                // Flags
                'tpc_detected' => $tpcDetected ? 1 : 0,
                'vcholerae_detected' => $vcholeraeDetected ? 1 : 0,
                'coliform_detected' => $coliformDetected ? 1 : 0,
                'deduction' => $deduction ? 1 : 0 // Combined Deduction Flag
            ];
        }

        // If OnlyDetected, filter again in PHP (extra safety)
        if ($onlyDetected) {
            $formattedData = array_filter($formattedData, function ($row) {
                return $row['deduction'] == 1;
            });
            $formattedData = array_values($formattedData); // reindex
        }


        return [
            'Error' => false,
            'Msg' => 'Fetch Water & Ice Data Success',
            'Data' => $formattedData
        ];
    }

    //----------------------------------------------------------------------------------------------------------------------------------
    // ⬇️ New Functions for Food Contact Surface and Flake Ice ⬇️
    //----------------------------------------------------------------------------------------------------------------------------------

    // public function getFoodContactSurfaceGraphData($RequestBody)
    // {
    // $FromDate = $RequestBody['FromDate'];
    // $ToDate = $RequestBody['ToDate'];

    // // Fetch all Food Contact Surface samples in the date range
    // $sql = "SELECT sample_collection_date 
    //         FROM micro_biology_workers_food_surface 
    //         WHERE sample_collection_date BETWEEN :fromDate AND :toDate 
    //         ORDER BY sample_collection_date ASC";

    // $stmt = $this->conn->prepare($sql);
    // $stmt->bindParam(':fromDate', $FromDate);
    // $stmt->bindParam(':toDate', $ToDate);
    // $stmt->execute();
    // $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // // Generate a complete list of months in the date range
    // $allMonths = $this->getMonthRange($FromDate, $ToDate);
    // $monthsFormatted = array_keys($allMonths);

    // // Initialize series data
    // $seriesData = [];
    // foreach ($monthsFormatted as $month) {
    // $seriesData[$month] = 0; // tested samples count
    // }

    // // Aggregate counts per month
    // foreach ($rows as $row) {
    // $month = date('Y-m', strtotime($row['sample_completion_date']));
    // if (isset($seriesData[$month])) {
    // $seriesData[$month]++;
    // }
    // }

    // // Prepare final series
    // $testedSamplesArr = array_values($seriesData);

    // return [
    // 'Error' => false,
    // 'Msg' => 'Fetch Food Contact Surface Graph Data Success',
    // 'Data' => [
    // 'months' => $monthsFormatted,
    // 'series' => [
    // ['name' => 'Tested Samples', 'data' => $testedSamplesArr]
    // ]
    // ]
    // ];
    // }


    // public function getFoodContactData($RequestBody)
    // {
    // $FromDate = $RequestBody['fromDate'] . ' 00:00:00';
    // $ToDate = $RequestBody['toDate'] . ' 23:59:59';

    // $sql = "SELECT *
    //             FROM micro_biology_workers_food_surface
    //             WHERE sample_collection_date BETWEEN :fromDate AND :toDate
    //             ORDER BY sample_collection_date DESC";

    // $stmt = $this->conn->prepare($sql);
    // $stmt->bindParam(':fromDate', $FromDate);
    // $stmt->bindParam(':toDate', $ToDate);
    // $stmt->execute();
    // $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // $formattedData = [];
    // $sno = 1;

    // foreach ($rows as $row) {
    // $formattedData[] = [
    // 'Sno' => $sno++,
    // 'id' => $row['mbwfs_id'],
    // 'sample_drawn_by' => $row['sample_drawn_by'],
    // 'sample_collection_area' => $row['sample_collection_area'],
    // 'name_food_contact_surface' => $row['name_food_contact_surface'],
    // 'tpc_paramater' => $row['tpc_paramater'],
    // 'tpc_media' => $row['tpc_media'],
    // 'tpc_Kitlotno' => $row['tpc_Kitlotno'],
    // 'tpc_UsedWells' => $row['tpc_UsedWells'],
    // 'tpc_equipement' => $row['tpc_equipement'],
    // 'tpc_result' => $row['tpc_result'],
    // 'tpc_completion_date' => $row['tpc_completion_date'],
    // 'vcholerae_paramater' => $row['vcholerae_paramater'],
    // 'vcholerae_media' => $row['vcholerae_media'],
    // 'vcholerae_Kitlotno' => $row['vcholerae_Kitlotno'],
    // 'vcholerae_UsedWells' => $row['vcholerae_UsedWells'],
    // 'vcholerae_equipement' => $row['vcholerae_equipement'],
    // 'vcholerae_result' => $row['vcholerae_result'],
    // 'vcholerae_completion_date' => $row['vcholerae_completion_date'],
    // 'analysed_by' => $row['analysed_by'],
    // 'checked_by' => $row['checked_by'],
    // 'verified_by' => $row['verified_by'],
    // 'remarks' => $row['remarks'],
    // 'user_id' => $row['user_id'],
    // 'created_date' => $row['created_date'],
    // 'update_date' => $row['update_date'],
    // ];
    // }

    // return [
    // 'Error' => false,
    // 'Msg' => 'Fetch Food Contact Surface Data Success',
    // 'Data' => $formattedData
    // ];
    // }






    // public function getFlakeIceGraphData($RequestBody)
    // {
    // $FromDate = $RequestBody['FromDate'];
    // $ToDate = $RequestBody['ToDate'];
    // $ReqType = $RequestBody['ReqType'];

    // $sql = "SELECT DATE_FORMAT(sample_completion_date, '%Y-%m') AS ReportMonth,
    //             COUNT(*) AS testedSamples,
    //             SUM(CASE WHEN `result_tp_count_detected` = 1 OR `result_ecoli_detected` = 1 OR `result_saureus_detected` = 1 OR `result_listeria_detected` = 1 OR `result_salmonella_detected` = 1 OR `result_coliforms_detected` = 1 OR `result_yeast_mold_detected` = 1 THEN 1 ELSE 0 END) AS detectedSamples
    //             FROM micro_biology_ice_chutes_water_tanks
    //             WHERE sample_collection_date BETWEEN :fromDate AND :toDate
    //             GROUP BY ReportMonth
    //             ORDER BY ReportMonth ASC";

    // $stmt = $this->conn->prepare($sql);
    // $stmt->bindParam(':fromDate', $FromDate);
    // $stmt->bindParam(':toDate', $ToDate);
    // $stmt->execute();
    // $dbRows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // $allMonths = $this->getMonthRange($FromDate, $ToDate);
    // $monthsFormatted = array_keys($allMonths);
    // $testedSamplesArr = array_fill(0, count($monthsFormatted), 0);
    // $detectedSamplesArr = array_fill(0, count($monthsFormatted), 0);

    // foreach ($dbRows as $row) {
    // $month = $row['ReportMonth'];
    // if (isset($allMonths[$month])) {
    // $index = array_search($month, $monthsFormatted);
    // $testedSamplesArr[$index] = (int)$row['testedSamples'];
    // $detectedSamplesArr[$index] = (int)$row['detectedSamples'];
    // }
    // }

    // return [
    // 'Error' => false,
    // 'Msg' => 'Fetch Flake Ice Graph Data Success',
    // 'Data' => [
    // 'months' => $monthsFormatted,
    // 'series' => [
    // ['name' => 'Tested Samples', 'data' => $testedSamplesArr],
    // ['name' => 'Detected Samples', 'data' => $detectedSamplesArr]
    // ]
    // ]
    // ];
    // }

    // public function getFlakeIceData($RequestBody)
    // {
    // $FromDate = $RequestBody['FromDate'];
    // $ToDate = $RequestBody['ToDate'];
    // $ReqType = $RequestBody['ReqType'];

    // $sql = "SELECT * FROM micro_biology_ice_chutes_water_tanks 
    //             WHERE sample_collection_date BETWEEN :fromDate AND :toDate 
    //             ORDER BY sample_collection_date DESC";

    // $stmt = $this->conn->prepare($sql);
    // $stmt->bindParam(':fromDate', $FromDate);
    // $stmt->bindParam(':toDate', $ToDate);
    // $stmt->execute();
    // $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // $processedData = [];
    // $sno = 1;

    // if ($result) {
    // foreach ($result as $item) {
    // $isDetected = $item['result_tp_count_detected'] == 1 ||
    // $item['result_ecoli_detected'] == 1 ||
    // $item['result_saureus_detected'] == 1 ||
    // $item['result_listeria_detected'] == 1 ||
    // $item['result_salmonella_detected'] == 1 ||
    // $item['result_coliforms_detected'] == 1 ||
    // $item['result_yeast_mold_detected'] == 1;

    // if ($ReqType === 'Detected Samples' && !$isDetected) {
    // continue;
    // }

    // $processedData[] = [
    // 'Sno' => $sno++,
    // 'BatchNumber' => isset($item['batch_number']) ? $item['batch_number'] : '--',
    // 'SampleType' => isset($item['sample_type']) ? $item['sample_type'] : '--',
    // 'SampleDetails' => isset($item['sample_details']) ? $item['sample_details'] : '--',
    // 'SamplerName' => isset($item['sample_drawn_by']) ? $item['sample_drawn_by'] : '--',
    // 'ReceivedBy' => isset($item['sample_received_by']) ? $item['sample_received_by'] : '--',
    // 'AnalysisDate' => isset($item['analysis_date']) ? $item['analysis_date'] : '--',
    // 'CompletionDate' => isset($item['sample_completion_date']) ? $item['sample_completion_date'] : '--',
    // 'AnalyzedBy' => isset($item['analysed_by']) ? $item['analysed_by'] : '--',
    // 'VerifiedBy' => isset($item['verified_by']) ? $item['verified_by'] : '--',
    // 'Result' => isset($item['result']) ? $item['result'] : '--',
    // 'Ecoli' => isset($item['result_ecoli']) ? $item['result_ecoli'] : '--',
    // 'Saureus' => isset($item['result_saureus']) ? $item['result_saureus'] : '--',
    // 'Listeria' => isset($item['result_listeria']) ? $item['result_listeria'] : '--',
    // 'Salmonella' => isset($item['result_salmonella']) ? $item['result_salmonella'] : '--',
    // 'Coliforms' => isset($item['result_coliforms']) ? $item['result_coliforms'] : '--',
    // 'YeastandMold' => isset($item['result_yeast_mold']) ? $item['result_yeast_mold'] : '--',
    // ];
    // }
    // }

    // return [
    // 'Error' => empty($processedData),
    // 'Msg' => empty($processedData) ? 'No data found' : 'Success',
    // 'Data' => $processedData,
    // 'TotalCount' => count($processedData)
    // ];
    // }

    //----------------------------------------------------------------------------------------------------------------------------------
    // ⬆️ New Functions for Food Contact Surface and Flake Ice ⬆️
    //----------------------------------------------------------------------------------------------------------------------------------


    private function formatResult($value, $isDetected)
    {
        if ($isDetected) {
            return "<span style='color:red;'>$value</span>";
        }
        return $value;
    }

    public function is_detected($field, $val)
    {
        $val = trim(str_replace(',', '', $val));
        $isDetected = false;

        if ($field === 'TpcResult') {
            // Remove CFU/SQCM if present
            $numericVal = floatval(preg_replace('/[^0-9.]/', '', $val));

            if (is_numeric($numericVal) && $numericVal > 80) {
                $isDetected = true;
            } elseif (strtoupper($val) === "TNTC") {
                $isDetected = true;

            }

        } elseif ($field === 'VcholeraeResult') {
            if (strtolower($val) === "positive") {
                $isDetected = true;

            }
        } elseif ($field === 'InstantSwab') {
            // New condition for instant swab
            if (strtolower($val) === "positive") {
                $isDetected = true;

            }
        }

        return $isDetected;
    }
}
