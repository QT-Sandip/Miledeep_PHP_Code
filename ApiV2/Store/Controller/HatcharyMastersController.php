<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class HatcharyMastersController
{
    private $conn;
    private $utilController;
    private $Error = null;
    private $Msg = '';
    private $auditLogger;


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->auditLogger = new CentralAuditLogger($this->conn, 'Store');
        // $this->utilController = new UtilController($this->conn);
    }

    public function postHatcharyDetails($RequestBody)
    {
        $HatcheryID = $RequestBody['HatcheryID'];
        $HatcryName = $RequestBody['HatcryName'];
        $HatcryLocation = $RequestBody['HatcryLocation'];
        $HatcryCapacity = $RequestBody['HatcryCapacity'];
        $Status = 1;

        if ($HatcheryID == '') {
            $sqlInsert = "INSERT INTO GsHatchryMaster( HatcryName, HatcryLocation, HatcryCapacity, Status) 
            VALUES ('$HatcryName','$HatcryLocation','$HatcryCapacity','$Status')";

            $stmtInsert = $this->conn->prepare($sqlInsert);
            $stmtInsert->execute();

            if ($stmtInsert) {
                $lastInsertedId = $this->conn->lastInsertId();
                $createdHatchery = $this->auditLogger->fetchRowById('GsHatchryMaster', 'HatcheryID', $lastInsertedId);
                $this->auditLogger->logInsert('GsHatchryMaster', $lastInsertedId, $createdHatchery, $RequestBody, [
                    'ReferenceNo' => $lastInsertedId,
                    'Remarks' => 'Store hatchery created',
                ]);
                $this->Error = null;
                $this->Msg = "Hatchery Posted Successfully";
            }
        } else {
            $existingHatchery = $this->auditLogger->fetchRowById('GsHatchryMaster', 'HatcheryID', $HatcheryID);
            $sqlUpdate = "UPDATE GsHatchryMaster SET HatcryName='$HatcryName', HatcryLocation='$HatcryLocation', 
            HatcryCapacity='$HatcryCapacity',Status='$Status'
            WHERE HatcheryID='$HatcheryID'";

            $stmtUpdate = $this->conn->prepare($sqlUpdate);
            $stmtUpdate->execute();

            if ($stmtUpdate) {
                $updatedHatchery = $this->auditLogger->fetchRowById('GsHatchryMaster', 'HatcheryID', $HatcheryID);
                $this->auditLogger->logUpdate('GsHatchryMaster', $HatcheryID, $existingHatchery, $updatedHatchery, $RequestBody, [
                    'ReferenceNo' => $HatcheryID,
                    'Remarks' => 'Store hatchery updated',
                ]);
                $this->Error = null;
                $this->Msg = "Hatchery Updated Successfully";
            }
        }

        $getData = $this->getHatcheryDetails($RequestBody);
        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        $Data['Data'] = $getData['Data'];

        return $Data;
    }

    // function deleteFsAuditDetails($RequestBody)
    // {
    //     $FsaID = $RequestBody['FsaID'];

    //     $sqlDelete = "DELETE FROM `GsFoodNSocialAuditDetails` WHERE FsaID='$FsaID'";

    //     $stmtUpdate = $this->conn->prepare($sqlDelete);
    //     $stmtUpdate->execute();

    //     if ($stmtUpdate) {
    //         $fsAuditData = $this->getFsAuditDetails($RequestBody);
    //         $Data['Error'] = null;
    //         $Data['Msg'] = 'FS Audit Deleted Successfully';
    //         $Data['Data'] = $fsAuditData['Data'];
    //         return $Data;
    //     } else {
    //         $Data['Error'] = 'Unable to Delete FS Audit';
    //         $Data['Msg'] = $this->Error;
    //         return $Data;
    //     }
    // }

    function getHatcheryDetails($RequestBody)
    {

        $RecordsPerPage = 10;
        $HatcheryID = $RequestBody['HatcheryID'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];

        $Query = '';

        $RecordsPerPage = 10;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query = " and CONCAT_WS('', HatcheryID, HatcryName, HatcryLocation, HatcryCapacity, Status) LIKE '%$SearchKey%'";
        }

        $sqlVarietyCount = "SELECT COUNT(*) as Total FROM `GsHatchryMaster` WHERE 1=1 $Query ";

        $stmt = $this->conn->prepare($sqlVarietyCount);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "HatcheryID, HatcryName, HatcryLocation, HatcryCapacity, Status";

        if ($HatcheryID != '') {

            $sqlQuery = "SELECT $sqlFields FROM GsHatchryMaster 
            WHERE HatcheryID='$HatcheryID'";
        } else {

            $sqlQuery = "SELECT $sqlFields FROM GsHatchryMaster 
            WHERE 1=1 $Query ORDER BY HatcheryID $Limit";
        }
        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($errorInfo);

        $ArrayHatcheryData = [];

        $i = $LimitMax;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayHatcheryList['SNO'] = ++$i;
            $ArrayHatcheryList['HatcheryID'] = $dataRow['HatcheryID'];
            $ArrayHatcheryList['HatcryName'] = $dataRow['HatcryName'];
            $ArrayHatcheryList['HatcryLocation'] = $dataRow['HatcryLocation'];
            $ArrayHatcheryList['HatcryCapacity'] = $dataRow['HatcryCapacity'];
            $ArrayHatcheryList['Status'] = $dataRow['Status'];
            $ArrayHatcheryList['EditLink'] = "hatchery-create?HatcheryID=" .$dataRow['HatcheryID'];

            $ArrayHatcheryData[] =  $ArrayHatcheryList;
        }

        if (count($ArrayHatcheryData) < $RecordsPerPage) {
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
        $data['Data'] = $ArrayHatcheryData;

        return $data;
    }
}
