<?php


include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class CsGroupMasterController
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
        $this->auditLogger = new CentralAuditLogger($this->conn, 'ColdStore');
        // $this->utilController = new UtilController($this->conn);
    }

    public function postGroupDetails($RequestBody)
    {
        $TrnsID = $RequestBody['TrnsID'];
        $TransCategory = $RequestBody['TransCategory'];
        $TransType = $RequestBody['TransType'];
        $IsReturnable = $RequestBody['IsReturnable'];
        $TransStatus = $RequestBody['TransStatus'];

        if ($TrnsID == '') {
            $sqlInsert = " INSERT INTO CsTransactionTypes (TransCategory, TransType, IsReturnable, TransStatus) 
            VALUES ('$TransCategory','$TransType','$IsReturnable','$TransStatus')";

            $stmtInsert = $this->conn->prepare($sqlInsert);
            $stmtInsert->execute();

            if ($stmtInsert) {
                $lastInsertedId = $this->conn->lastInsertId();
                $RequestBody['TrnsID'] = $lastInsertedId;
                $createdGroup = $this->auditLogger->fetchRowById('CsTransactionTypes', 'TrnsID', $lastInsertedId);
                $this->auditLogger->logInsert('CsTransactionTypes', $lastInsertedId, $createdGroup, $RequestBody, [
                    'ReferenceNo' => $TransCategory,
                    'Remarks' => 'Cold store transaction type created',
                ]);
                $this->Error = null;
                $this->Msg = 'Group Posted Successfully';
            } else {
                $this->Error = 'Unable Save Data';
                $this->Msg = 'Unable Save Data';
            }
        } else {
            $existingGroup = $this->auditLogger->fetchRowById('CsTransactionTypes', 'TrnsID', $TrnsID);

            $sqlUpdate = "UPDATE CsTransactionTypes SET TransCategory = '$TransCategory', TransType = '$TransType', 
            IsReturnable = '$IsReturnable',TransStatus = '$TransStatus' WHERE TrnsID = '$TrnsID'";

            $stmtUpdate = $this->conn->prepare($sqlUpdate);
            $stmtUpdate->execute();

            if ($stmtUpdate) {
                $updatedGroup = $this->auditLogger->fetchRowById('CsTransactionTypes', 'TrnsID', $TrnsID);
                $this->auditLogger->logUpdate('CsTransactionTypes', $TrnsID, $existingGroup, $updatedGroup, $RequestBody, [
                    'ReferenceNo' => $TransCategory,
                    'Remarks' => 'Cold store transaction type updated',
                ]);
                $this->Error = null;
                $this->Msg = 'Group Updated Successfully';
            } else {
                $this->Error = 'Unable Update Data';
                $this->Msg = 'Unable Update Data';
            }
        }

        $getData = $this->getGroupDetails($RequestBody);
        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        $Data['Data'] = $getData['Data'];

        return $Data;
    }

    public function deleteGroupDetails($RequestBody)
    {
        $TrnsID = $RequestBody['TrnsID'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];

        $sqlDelete = "DELETE FROM CsTransactionTypes WHERE TrnsID='$TrnsID'";
        $existingGroup = $this->auditLogger->fetchRowById('CsTransactionTypes', 'TrnsID', $TrnsID);
        $stmtDelete = $this->conn->prepare($sqlDelete);
        $stmtDelete->execute();

        if ($stmtDelete) {
            $this->auditLogger->logDelete('CsTransactionTypes', $TrnsID, $existingGroup, $RequestBody, [
                'ReferenceNo' => isset($existingGroup['TransCategory']) ? $existingGroup['TransCategory'] : null,
                'Remarks' => 'Cold store transaction type deleted',
            ]);
            $getData = $this->getGroupDetails($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'Group Deleted Successfully';
            $Data['Data'] = $getData['Data'];
        } else {
            $Data['Error'] = 'Unable to Delete';
            $Data['Msg'] = 'Unable to Delete';
        }

        return $Data;
    }


    function getGroupDetails($RequestBody)
    {
        $RecordsPerPage = 100;
        $TrnsID = $RequestBody['TrnsID'];
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
            $Query = " and CONCAT_WS('', TransCategory, TransType, IsReturnable, TransStatus) LIKE '%$SearchKey%'";
        }

        // $sqlSpeciesCount = "SELECT COUNT(*) as Total FROM CsTransactionTypes WHERE 1=1 $Query";
        // $stmt = $this->conn->prepare($sqlSpeciesCount);
        // $stmt->execute();

        // $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        // $TotalCount = $dataRow['Total'];
        // $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "TrnsID, TransCategory, TransType, IsReturnable, TransStatus,
        CASE TransType WHEN '1' THEN 'Production' WHEN '2' THEN 'Stock Out' END AS TransTypeName,
        CASE IsReturnable WHEN '0' THEN 'NO' WHEN '1' THEN 'YES' END AS IsReturnableName";

        if ($TrnsID != '') {
            $sqlQuery = "SELECT $sqlFields FROM CsTransactionTypes WHERE TrnsID='$TrnsID'";
        } else {
            $sqlQuery = "SELECT $sqlFields FROM CsTransactionTypes WHERE 1=1 $Query ORDER BY TrnsID DESC " . $Limit;
        }

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        $TotalCount = $stmtSelect->rowCount();
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($errorInfo);

        $ArrayGroupData = [];

        $i = $LimitMax;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayGroupList['SNO'] = ++$i;
            $ArrayGroupList['TrnsID'] = $dataRow['TrnsID'];
            $ArrayGroupList['TransCategory'] = $dataRow['TransCategory'];
            $ArrayGroupList['TransType'] = $dataRow['TransType'];
            $ArrayGroupList['TransTypeName'] = $dataRow['TransTypeName'];
            $ArrayGroupList['IsReturnable'] = $dataRow['IsReturnable'];
            $ArrayGroupList['IsReturnable'] = $dataRow['IsReturnable'];
            $ArrayGroupList['IsReturnableName'] = $dataRow['IsReturnableName'];
            $ArrayGroupList['TransStatus'] = $dataRow['TransStatus'];
            $ArrayGroupList['EditLink'] = "cs-groups-create?TrnsID=" . $dataRow['TrnsID'];

            $ArrayGroupData[] = $ArrayGroupList;
        }

        if (count($ArrayGroupData) < $RecordsPerPage) {
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
        $data['Data'] = $ArrayGroupData;

        return $data;
    }
}
