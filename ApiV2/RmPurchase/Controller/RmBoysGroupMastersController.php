<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';
include_once __DIR__ . '/AuditLogger.php';

class RmBoysGroupMastersController
{
    private $conn;
    //	private $db_table = "ScrmApplicants";
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

    public function postBoysGroupDetails($RequestBody)
    {
        $GroupID  = $RequestBody['GroupID'];
        $GroupName = $RequestBody['GroupName'];

        if ($GroupID == '') {

            $sqlCheckInsert = "SELECT * FROM RmBoysGroupMasters WHERE `GroupName` = '$GroupName'";

            $stmtCheckInsert = $this->conn->prepare($sqlCheckInsert);
            $stmtCheckInsert->execute();
            $rowCount = $stmtCheckInsert->rowCount();

            if ($rowCount == 0) {
                $sqlInsert = "INSERT INTO `RmBoysGroupMasters`(`GroupName`) VALUES ('$GroupName')";

                $stmtInsert = $this->conn->prepare($sqlInsert);
                $stmtInsert->execute();

                if ($stmtInsert) {
                    $newGroupId = $this->conn->lastInsertId();
                    $newGroup = $this->auditLogger->fetchRowById('RmBoysGroupMasters', 'GroupID', $newGroupId);
                    $this->auditLogger->logInsert('RmBoysGroupMasters', $newGroupId, $newGroup, $RequestBody, [
                        'ReferenceNo' => $GroupName,
                        'Remarks' => 'Boys group created',
                    ]);
                    $this->Error = null;
                    $this->Msg = 'BoysGroup Posted Successfully';
                }
            } else {
                $this->Error = 'BoysGroup Name Already Exists';
                $this->Msg = 'BoysGroup Name Already Exists';
            }
        } else {

            $sqlUpdateCheck = "SELECT * FROM RmBoysGroupMasters 
            WHERE `GroupName` = '$GroupName' AND GroupID != '$GroupID'";

            $stmtUpdateCheck = $this->conn->prepare($sqlUpdateCheck);
            $stmtUpdateCheck->execute();
            $rowCountUpdate = $stmtUpdateCheck->rowCount();

            if ($rowCountUpdate == 0) {
                $existingGroup = $this->auditLogger->fetchRowById('RmBoysGroupMasters', 'GroupID', $GroupID);

                $sqlUpdate = "UPDATE `RmBoysGroupMasters`  SET  `GroupName` = '$GroupName'
                WHERE `GroupID` = '$GroupID'";

                $stmtUpdate = $this->conn->prepare($sqlUpdate);
                $stmtUpdate->execute();

                if ($stmtUpdate) {
                    $updatedGroup = $this->auditLogger->fetchRowById('RmBoysGroupMasters', 'GroupID', $GroupID);
                    $this->auditLogger->logUpdate('RmBoysGroupMasters', $GroupID, $existingGroup, $updatedGroup, $RequestBody, [
                        'ReferenceNo' => $GroupName,
                        'Remarks' => 'Boys group updated',
                    ]);
                    $this->Error = null;
                    $this->Msg = 'BoysGroup Updated Successfully';
                }
            } else {
                $this->Error = 'BoysGroup Name Already Exists';
                $this->Msg = 'BoysGroup Name Already Exists';
            }
        }

        $getData = $this->getBoysGroupDetails($RequestBody);
        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        $Data['Data'] = $getData['Data'];

        return $Data;
    }


    function getBoysGroupDetails($RequestBody)
    {
        $RecordsPerPage = 100;


        $GroupID = $RequestBody['GroupID'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];

        $Query = '';

        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query = " and CONCAT_WS('', GroupID, GroupName) LIKE '%$SearchKey%'";
        }

        $sqlBoysGroupCount = "SELECT COUNT(*) as Total FROM RmBoysGroupMasters WHERE 1=1 $Query";
        $stmt = $this->conn->prepare($sqlBoysGroupCount);
        $stmt->execute();

        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "GroupID, GroupName";

        if ($GroupID != '') {
            $sqlQuery = "SELECT $sqlFields FROM `RmBoysGroupMasters` WHERE GroupID='$GroupID'";
        } else {
            $sqlQuery = "SELECT $sqlFields FROM `RmBoysGroupMasters` WHERE 1=1 $Query " . $Limit;
        }

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($errorInfo);

        $ArrayGroupsData = [];

        $i = $LimitMax;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayGroupsList['SNO'] = ++$i;
            $ArrayGroupsList['GroupID'] = $dataRow['GroupID'];
            $ArrayGroupsList['GroupName'] = $dataRow['GroupName'];

            $ArrayGroupsData[] =  $ArrayGroupsList;
        }

        if (count($ArrayGroupsData) < $RecordsPerPage) {
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
        $data['Data'] = $ArrayGroupsData;

        return $data;
    }
}
