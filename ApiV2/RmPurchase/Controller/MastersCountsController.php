<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';
include_once __DIR__ . '/AuditLogger.php';

class MastersCountsController
{
    private $conn;
    // private $db_table = "ScrmApplicants";
    private $utilController;
    private $auditLogger;
    private $Error = null;
    private $Msg = '';


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        // $this->utilController = new UtilController($this->conn);
        $this->auditLogger = new AuditLogger($this->conn, 'Purchase');
    }

    public function postCountsDetails($RequestBody)
    {
        $RateId = $RequestBody['RateId'];
        $RmCount = $RequestBody['RmCount'];
        // $CountGroup = $RequestBody['CountGroup'];
        // $Status = $RequestBody['Status'];
        // $SortOrder = $RequestBody['SortOrder'];
        $Stype = $RequestBody['Stype'];


        if ($RateId == '') {

            $sqlCheckInsert = "SELECT count(*) as RowCount FROM RmRatesRange WHERE RmCount = '$RmCount' AND Stype = '$Stype'";

            $stmtCheckInsert = $this->conn->prepare($sqlCheckInsert);
            $stmtCheckInsert->execute();
            // $rowCount = $stmtCheckInsert->rowCount();
            $dataRow = $stmtCheckInsert->fetch(PDO::FETCH_ASSOC);
            $RowCount = $dataRow['RowCount'];

            if ($RowCount == 0) {

                $sqlInsert = " INSERT INTO `RmRatesRange`( `RmCount`, `Stype`) 
                VALUES ('$RmCount','$Stype')";

                $stmtInsert = $this->conn->prepare($sqlInsert);
                $stmtInsert->execute();

                // $errorInfo = $stmtInsert->errorInfo();
                // var_dump($errorInfo);


                if ($stmtInsert) {
                    $newRateId = $this->conn->lastInsertId();
                    $newRate = $this->auditLogger->fetchRowById('RmRatesRange', 'RateId', $newRateId);
                    $this->auditLogger->logInsert('RmRatesRange', $newRateId, $newRate, $RequestBody, [
                        'ReferenceNo' => $RmCount,
                        'Remarks' => 'Rate count master created',
                    ]);
                    $this->Error = null;
                    $this->Msg = 'Counts Posted Successfully';
                }
            } else {
                $this->Error = 'Counts Name Already Exists';
                $this->Msg = 'Counts Name Already Exists';
            }
        } else {

            $sqlUpdateCheck = "SELECT count(*) as RowCount FROM RmRatesRange  WHERE RmCount = '$RmCount' AND Stype = '$Stype'";

            $stmtUpdateCheck = $this->conn->prepare($sqlUpdateCheck);
            $stmtUpdateCheck->execute();
            // $rowCountUpdate = $stmtUpdateCheck->rowCount();
            $dataRow = $stmtUpdateCheck->fetch(PDO::FETCH_ASSOC);
            $RowCount = $dataRow['RowCount'];


            if ($RowCount == 0) {
                $existingRate = $this->auditLogger->fetchRowById('RmRatesRange', 'RateId', $RateId);

                $sqlUpdate = "UPDATE `RmRatesRange`  SET  `RmCount` = '$RmCount',`Stype` = '$Stype' WHERE `RateId` = '$RateId'";

                $stmtUpdate = $this->conn->prepare($sqlUpdate);
                $stmtUpdate->execute();

                // $errorInfo = $stmtUpdate->errorInfo();
                // var_dump($errorInfo);

                if ($stmtUpdate) {
                    $updatedRate = $this->auditLogger->fetchRowById('RmRatesRange', 'RateId', $RateId);
                    $this->auditLogger->logUpdate('RmRatesRange', $RateId, $existingRate, $updatedRate, $RequestBody, [
                        'ReferenceNo' => $RmCount,
                        'Remarks' => 'Rate count master updated',
                    ]);
                    $this->Error = null;
                    $this->Msg = 'Counts Updated Successfully';
                }
            } else {
                $this->Error = 'Counts Name Already Exists';
                $this->Msg = 'Counts Name Already Exists';
            }
        }

        $getData = $this->getCountsDetails($RequestBody);
        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        $Data['Data'] = $getData['Data'];

        return $Data;
    }


    function getCountsDetails($RequestBody)
    {
        $RecordsPerPage = 100;
        $RateId = $RequestBody['RateId'];
        $Stype = $RequestBody['Stype'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];

        $Query = '';

        if ($Stype != '') {
            $Query .= " AND Stype = '$Stype'";
        }

        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query .= " and CONCAT_WS('', RateId, RmCount, RmRR.Status,RmRR.SortOrder, SpeciesName,Stype) LIKE '%$SearchKey%'";
        }

        $sqlCountsCount = "SELECT COUNT(*) as Total FROM RmRatesRange  WHERE 1=1 $Query";
        $stmt = $this->conn->prepare($sqlCountsCount);
        $stmt->execute();

        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "RateId, RmCount, RmRR.Status, RmRR.SortOrder, SpeciesName,Stype";

        if ($RateId != '') {
            $sqlQuery = "SELECT $sqlFields FROM `RmRatesRange`  AS RmRR 
           LEFT JOIN ScrmSpeciesMaster AS SSM ON  SSM.ShortCode = RmRR.Stype
            WHERE RateId='$RateId'";
        } else {
            $sqlQuery = "SELECT $sqlFields FROM `RmRatesRange` AS RmRR 
             LEFT JOin ScrmSpeciesMaster AS SSM ON SSM.ShortCode = RmRR.Stype 
             WHERE RmRR.Status=1 $Query " . $Limit;
        }

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($errorInfo);

        $ArrayBranchData = [];

        $i = $LimitMax;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayCountsList['SNO'] = ++$i;
            $ArrayCountsList['RateId'] = $dataRow['RateId'];
            $ArrayCountsList['RmCount'] = $dataRow['RmCount'];
            $ArrayCountsList['Status'] = $dataRow['Status'];
            $ArrayCountsList['SortOrder'] = $dataRow['SortOrder'];
            $ArrayCountsList['Stype'] = $dataRow['Stype'];
            $ArrayCountsList['SpeciesName'] = $dataRow['SpeciesName'];
            $ArrayCountsList['EditLink'] = "master-add-counts?RateId=" . $dataRow['RateId'];

            $ArrayCountsData[] =  $ArrayCountsList;
        }

        if (count($ArrayCountsData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TotalCount'] = $TotalCount;
        $data['RemainingCount'] = "";
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;
        $data['Data'] = $ArrayCountsData;

        return $data;
    }
}
