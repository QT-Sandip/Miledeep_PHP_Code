<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class AntibioticReportController
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

    public function getAntibioticReportDetails($RequestBody)
    {
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $DateFilter = " between '$FromDate' and '$ToDate'";
        $Type = $RequestBody['Type'];
        $LotIds = isset($RequestBody['LotIds']) ? $RequestBody['LotIds'] : '';
        $PageNo = !empty($RequestBody['pageNo']) ? $RequestBody['pageNo'] : 1;
        $SearchKey = $RequestBody['SearchKey'];
        $RecordsPerPage = 50;

        $Query = '';

        if ($Type == 'GET_BATCH') {
            $lotsSql = "SELECT DISTINCT RLD.RmLotID, RLD.LotNumber
                FROM RmLotDetatils AS RLD
                JOIN QcRmAbReport AS QRAR
                -- ON FIND_IN_SET(RLD.RmLotID, QRAR.LotID) > 0
                ON FIND_IN_SET(RLD.LotNumber, QRAR.`LotNO`) > 0
                WHERE QRAR.LotStatus = 0
                AND QRAR.AnalysisDate $DateFilter
                ORDER BY QRAR.AnalysisDate DESC, RLD.LotNumber";
            $stmtLotsSql = $this->conn->prepare($lotsSql);
            $stmtLotsSql->execute();
            $ArrayAntibioticLotsData = $stmtLotsSql->fetchAll(PDO::FETCH_ASSOC);
            $response = !empty($ArrayAntibioticLotsData) ? [
                'Error' => null,
                'Msg' => 'Success',
                'AntibioticLotsData' => $ArrayAntibioticLotsData
            ] : [
                'Error' => 'No data found',
                'Msg' => 'Failed',
                'AntibioticLotsData' => []
            ];

            return $response;
        }

        $LotIdsArray = explode(',', $LotIds); // ["123", "456"]

        $conditions = [];
        foreach ($LotIdsArray as $id) {
            $id = trim($id);
            if ($id !== '') {
                //$conditions[] = "FIND_IN_SET('$id', LotID) > 0";
                $conditions[] = "FIND_IN_SET('$id', LotNO) > 0";
            }
        }

        $lotQuery = '';
        if (!empty($conditions)) {
            $lotQuery = 'AND (' . implode(' OR ', $conditions) . ')';
        }
        $Limit = '';
        $TotalCount = $PageCount = $LimitMax = $totalCnt = 0;
        if (empty($RequestBody['loadAll'])) {
            $sqlCount = "SELECT LotNO,SampleDetails,
                            ExternalResult,group_concat(DISTINCT AHD)AHD,group_concat(DISTINCT CAP)CAP,group_concat(DISTINCT SEM)SEM,
                            group_concat(DISTINCT AMOZ)AMOZ,group_concat(DISTINCT AOZ)AOZ
                             FROM QcRmAbReport
                            WHERE AnalysisDate  $DateFilter $lotQuery
                            GROUP BY SampleDetails,LotID ORDER BY AnalysisDate";
            $stmtCount = $this->conn->prepare($sqlCount);
            $stmtCount->execute();
            $rows = $stmtCount->fetchAll(PDO::FETCH_ASSOC);
            $TotalCount = count($rows);
            $addedLotIDsC = $cntArr = [];
            foreach ($rows as $val) {
                $LotIDs = !empty($val['LotNO']) ? explode(',', $val['LotNO']) : [];
                if (!empty($LotIDs)) {
                    foreach ($LotIDs as $lot) {
                        $lot = trim($lot);
                        $SqlLotNumber = "SELECT RmLotID,LotNumber FROM `RmLotDetatils` WHERE LotNumber in('$lot')";
                        $StmtLotNumber = $this->conn->prepare($SqlLotNumber);
                        $StmtLotNumber->execute();
                        $dataRowLotNumber = $StmtLotNumber->fetch(PDO::FETCH_ASSOC);
                        //$lot_sampledetailsC = $dataRowLotNumber['RmLotID'] . '_' . $val['SampleDetails'];
                        $lot_sampledetailsC = $dataRowLotNumber['LotNumber'] . '_' . $val['SampleDetails'];
                        // Ensure values are not NULL
                        $AOZ = isset($val['AOZ']) ? trim($val['AOZ'], ',') : null;
                        $SEM = isset($val['SEM']) ? trim($val['SEM'], ',') : null;
                        $AMOZ = isset($val['AMOZ']) ? trim($val['AMOZ'], ',') : null;
                        $AHD = isset($val['AHD']) ? trim($val['AHD'], ',') : null;
                        $CAP = isset($val['CAP']) ? trim($val['CAP'], ',') : null;
                        $ExternalResult = isset($val['ExternalResult']) ? trim($val['ExternalResult'], ',') : null;
                        if (in_array($lot_sampledetailsC, $addedLotIDsC)) {
                            if (
                                (is_numeric($AOZ) && floatval($AOZ) > 0) ||
                                (is_numeric($SEM) && floatval($SEM) > 0) ||
                                (is_numeric($AMOZ) && floatval($AMOZ) > 0) ||
                                (is_numeric($AHD) && floatval($AHD) > 0) ||
                                (is_numeric($CAP) && floatval($CAP) > 0) ||
                                (is_numeric($ExternalResult) && floatval($ExternalResult) > 0)
                            ) {
                                $cntArr[$lot_sampledetailsC] = $val;
                                $addedLotIDsC[] = $lot_sampledetailsC;
                            } else {
                                continue; // Skip if already added
                            }
                        }
                        if ($LotIds != '') {
                            $lot_sampledetailsCArr = explode('_', $lot_sampledetailsC);
                            if (in_array($lot_sampledetailsCArr[0], $LotIdsArray)) {
                                $cntArr[$lot_sampledetailsC] = $val;
                                $addedLotIDsC[] = $lot_sampledetailsC;
                            }
                        } else {
                            $cntArr[$lot_sampledetailsC] = $val;
                            $addedLotIDsC[] = $lot_sampledetailsC;
                        }
                    }
                }
            }
            //echo '<pre>';print_r($cntArr);die;
            $PageCount = ceil($TotalCount / $RecordsPerPage);
            $LimitMax = ($PageNo - 1) * $RecordsPerPage;
            $totalCnt = count($cntArr);

            $Limit .= 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;
        }

        if ($SearchKey != '') {
            if (preg_match('/^\d{2}-\d{2}-\d{4}$/', $SearchKey)) {
                // Convert to YYYY-MM-DD for DB search
                $dateParts = explode('-', $SearchKey);
                $SearchKey = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
            }
            $Query = " and CONCAT_WS('', AnalysisDate,LotNO,LotID,SamplerName,SampleDetails,QLTA.LbuName) LIKE '%$SearchKey%'";
        }

        $SqlFields = "LotNO,LotID,AnalysisDate,ReportDate,SampleDetails,SamplerName,QLTA.LbuName as AnalyzedBy,
 QLTA.LbuName as ReceivedBy , QLTV.LbuName as VerifiedBy,GatePassNo,Result,LabName,group_concat(DISTINCT Parameter) as Parameter,
 ExternalResult,ExternalIssuedResultDate,ExternalSampleSendingDate,ExternalLab,group_concat(DISTINCT AHD)AHD,group_concat(DISTINCT CAP)CAP,group_concat(DISTINCT SEM)SEM,
 group_concat(DISTINCT AMOZ)AMOZ,group_concat(DISTINCT AOZ)AOZ";
         $SqlAntibioticReportData = "SELECT $SqlFields FROM `QcRmAbReport` as QRAR 
              LEFT JOIN QcLabTechnologist AS QLTA ON QLTA.LUID = QRAR.AnalyzedBy 
       LEFT JOIN QcLabTechnologist AS QLTR ON QLTR.LUID = QRAR.ReceivedBy 
       LEFT JOIN QcLabTechnologist AS QLTV ON QLTV.LUID = QRAR.VerifiedBy 
       LEFT JOIN QcEiaLabNames AS QELN ON QELN.LabId = QRAR.ExternalLab 
       WHERE AnalysisDate $DateFilter 
       $Query $lotQuery  group by SampleDetails,LotID order by AnalysisDate  $Limit";
        //    echo $SqlAntibioticReportData; exit;
        ///echo '<pre>';print_r($SqlAntibioticReportData);die;
        $stmtAntibioticReportData = $this->conn->prepare($SqlAntibioticReportData);
        $stmtAntibioticReportData->execute();
        // while ($dataRowAntibioticReportData = $stmtAntibioticReportData->fetch(PDO::FETCH_ASSOC)) {
        //     $LotID = $dataRowAntibioticReportData['LotID'];

        //     // $SqlLotNumber = "SELECT group_concat(LotNumber)  as LotNumber FROM `RmLotDetatils` WHERE RmLotID in($LotID)";
        //     // $StmtLotNumber = $this->conn->prepare($SqlLotNumber);
        //     // $StmtLotNumber->execute();
        //     // $dataRowLotNumber = $StmtLotNumber->fetch(PDO::FETCH_ASSOC);

        //     $AOZ = trim($dataRowAntibioticReportData['AOZ'], ',');
        //     $SEM = trim($dataRowAntibioticReportData['SEM'], ',');
        //     $AMOZ = trim($dataRowAntibioticReportData['AMOZ'], ',');
        //     $AHD = trim($dataRowAntibioticReportData['AHD'], ',');
        //     $CAP = trim($dataRowAntibioticReportData['CAP'], ',');

        //     $ColourCode='red';
        //     $ArrayData['SNO'] = ++$i;
        //     // $ArrayData['BatchNumber'] = $dataRowLotNumber['LotNumber'];
        //     $ArrayData['BatchNumber'] = $dataRowAntibioticReportData['LotNO'];

        //     $ArrayData['AnalysisDate'] = $this->utilController->getFormatedDateLines($dataRowAntibioticReportData['AnalysisDate']);
        //     $ArrayData['CompletionDate'] = $this->utilController->getFormatedDateLines($dataRowAntibioticReportData['ReportDate']);
        //     $ArrayData['SampleType'] = $dataRowAntibioticReportData['SampleDetails'];
        //     $ArrayData['SampleDetails'] = $dataRowAntibioticReportData['GatePassNo'];
        //     $ArrayData['SamplerName'] = $dataRowAntibioticReportData['SamplerName'];
        //     $ArrayData['AnalyzedBy'] = $dataRowAntibioticReportData['AnalyzedBy'];
        //     $ArrayData['ReceivedBy'] = $dataRowAntibioticReportData['ReceivedBy'];
        //     $ArrayData['VerifiedBy'] = $dataRowAntibioticReportData['VerifiedBy'];

        //     $ArrayData['AOZ'] = ($AOZ !== "" && $AOZ !== null) ? ($AOZ > 0 ? "<span style='color:red;'>$AOZ</span>" : $AOZ) : "--";
        //     $ArrayData['CAP'] = ($CAP !== "" && $CAP !== null) ? ($CAP > 0 ? "<span style='color:red;'>$CAP</span>" : $CAP) : "--";
        //     $ArrayData['AHD'] = ($AHD !== "" && $AHD !== null) ? ($AHD > 0 ? "<span style='color:red;'>$AHD</span>" : $AHD) : "--";
        //     $ArrayData['AMOZ'] = ($AMOZ !== "" && $AMOZ !== null) ? ($AMOZ > 0 ? "<span style='color:red;'>$AMOZ</span>" : $AMOZ) : "--";
        //     $ArrayData['SEM'] = ($SEM !== "" && $SEM !== null) ? ($SEM > 0 ? "<span style='color:red;'>$SEM</span>" : $SEM) : "--";


        //     $ArrayData['ExternalLab'] = $dataRowAntibioticReportData['LabName'];
        //     $ArrayData['ExternalResult'] = $dataRowAntibioticReportData['ExternalResult'];
        //     $ArrayData['ExternalSampleSendingDate'] = $this->utilController->getFormatedDateLines($dataRowAntibioticReportData['ExternalSampleSendingDate']);
        //     $ArrayData['ExternalIssuedResultDate'] = $this->utilController->getFormatedDateLines($dataRowAntibioticReportData['ExternalIssuedResultDate']);

        //     $ArrayAntibioticReportData[] = $ArrayData;

        // }

        // $response['Error'] = null;
        // $response['Msg'] = 'Success';
        // $response['AntibioticReportData'] = $ArrayAntibioticReportData;
        // return $response;

        //$i = $RequestBody['hdnTotalRecords']; // Ensure $i is initialized
        $i = 0;
        $ArrayAntibioticReportData = [];

        if ($stmtAntibioticReportData) {
            $addedLotIDs = []; // Track already added LotIDs

            while ($dataRowAntibioticReportData = $stmtAntibioticReportData->fetch(PDO::FETCH_ASSOC)) {

                $LotIDs = !empty($dataRowAntibioticReportData['LotNO']) ? explode(',', $dataRowAntibioticReportData['LotNO']) : [];

                if (!empty($LotIDs)) {
                    foreach ($LotIDs as $lot) {
                        // echo "hii"; exit;
                        $lot = trim($lot);

                        $SqlLotNumber = "SELECT RmLotID,LotNumber FROM `RmLotDetatils` WHERE LotNumber in('$lot')";
                        $StmtLotNumber = $this->conn->prepare($SqlLotNumber);
                        $StmtLotNumber->execute();
                        $dataRowLotNumber = $StmtLotNumber->fetch(PDO::FETCH_ASSOC);
                        $AnalysisDate = $this->utilController->getFormatedDateLines($dataRowAntibioticReportData['AnalysisDate']);
                        //$lot_sampledetails = $dataRowLotNumber['RmLotID'] . '_' . $dataRowAntibioticReportData['SampleDetails'];
                        $lot_sampledetails = $dataRowLotNumber['LotNumber'] . '_' . $dataRowAntibioticReportData['SampleDetails'];
                        // Ensure values are not NULL
                        $AOZ = isset($dataRowAntibioticReportData['AOZ']) ? trim($dataRowAntibioticReportData['AOZ'], ',') : null;
                        $SEM = isset($dataRowAntibioticReportData['SEM']) ? trim($dataRowAntibioticReportData['SEM'], ',') : null;
                        $AMOZ = isset($dataRowAntibioticReportData['AMOZ']) ? trim($dataRowAntibioticReportData['AMOZ'], ',') : null;
                        $AHD = isset($dataRowAntibioticReportData['AHD']) ? trim($dataRowAntibioticReportData['AHD'], ',') : null;
                        $CAP = isset($dataRowAntibioticReportData['CAP']) ? trim($dataRowAntibioticReportData['CAP'], ',') : null;
                        $ExternalResult = isset($dataRowAntibioticReportData['ExternalResult']) ? trim($dataRowAntibioticReportData['ExternalResult'], ',') : null;
                        if (array_key_exists($lot_sampledetails, $ArrayAntibioticReportData)) {
                            $i = $i;
                        } else {
                            ++$i;
                        }
                        if (in_array($lot_sampledetails, $addedLotIDs)) {
                            if (
                                (is_numeric($AOZ) && floatval($AOZ) > 0) ||
                                (is_numeric($SEM) && floatval($SEM) > 0) ||
                                (is_numeric($AMOZ) && floatval($AMOZ) > 0) ||
                                (is_numeric($AHD) && floatval($AHD) > 0) ||
                                (is_numeric($CAP) && floatval($CAP) > 0) ||
                                (is_numeric($ExternalResult) && floatval($ExternalResult) > 0)
                            ) {
                                $ArrayData = [];
                                $ArrayData = $this->setData($dataRowLotNumber, $dataRowAntibioticReportData, $AOZ, $CAP, $AHD, $AMOZ, $SEM, $lot_sampledetails, $ExternalResult, $i);
                                $ArrayAntibioticReportData[$lot_sampledetails] = $ArrayData;
                                $addedLotIDs[] = $lot_sampledetails;
                            } else {
                                continue; // Skip if already added
                            }
                        }
                        if ($LotIds != '') {
                            $lot_sampledetailsArr = explode('_', $lot_sampledetails);
                            if (in_array($lot_sampledetailsArr[0], $LotIdsArray)) {
                                $ArrayData = [];

                                $ArrayData = $this->setData($dataRowLotNumber, $dataRowAntibioticReportData, $AOZ, $CAP, $AHD, $AMOZ, $SEM, $lot_sampledetails, $ExternalResult, $i);

                                $ArrayAntibioticReportData[$lot_sampledetails] = $ArrayData;
                                $addedLotIDs[] = $lot_sampledetails;
                            } else {
                                --$i;
                            }
                        } else {
                            $ArrayData = [];
                            $ArrayData = $this->setData($dataRowLotNumber, $dataRowAntibioticReportData, $AOZ, $CAP, $AHD, $AMOZ, $SEM, $lot_sampledetails, $ExternalResult, $i);

                            $ArrayAntibioticReportData[$lot_sampledetails] = $ArrayData;
                            $addedLotIDs[] = $lot_sampledetails;
                        }
                    }
                }
            }
            $ArrayAntibioticReportData = array_values($ArrayAntibioticReportData);
        }

        $response = !empty($ArrayAntibioticReportData) ? [
            'Error' => null,
            'Msg' => 'Success',
            'TotalCount' => $TotalCount,
            'PageCount' => $PageCount,
            'limit' => $limit,
            'totalRows' => $totalCnt,
            'AntibioticReportData' => $ArrayAntibioticReportData
        ] : [
            'Error' => 'No data found',
            'Msg' => 'Failed',
            'AntibioticReportData' => []
        ];

        return $response;



    }
    function formatValue($value)
    {
        return ($value !== "" && $value !== null) ? ($value > 0 ? "<span style='color:red;'>$value</span>" : $value) : "--";
    }
    private function setData($dataRowLotNumber, $dataRowAntibioticReportData, $AOZ, $CAP, $AHD, $AMOZ, $SEM, $lot, $ExternalResult, $i)
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
        $ArrayData['ExternalResult'] = $this->formatValue($ExternalResult);

        // External lab details
        $ArrayData['ExternalLab'] = $dataRowAntibioticReportData['LabName'];
        $ArrayData['Result'] = $dataRowAntibioticReportData['Result'];
        // $ArrayData['ExternalResult'] = $dataRowAntibioticReportData['ExternalResult'];
        $ArrayData['ExternalSampleSendingDate'] = $this->utilController->getFormatedDateLines($dataRowAntibioticReportData['ExternalSampleSendingDate']);
        $ArrayData['ExternalIssuedResultDate'] = $this->utilController->getFormatedDateLines($dataRowAntibioticReportData['ExternalIssuedResultDate']);

        return $ArrayData;
    }
}