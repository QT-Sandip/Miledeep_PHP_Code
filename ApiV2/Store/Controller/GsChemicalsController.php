<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class GsChemicalsController
{
    private $conn;
    //	private $db_table = "ScrmApplicants";
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

    public function postChemicalDetails($RequestBody)
    {
        $ChemID = $RequestBody['ChemID'];

        if ($ChemID == '') {
            $Data = $this->insertChemicalDetails($RequestBody);
        } else {
            $Data = $this->updateChemicalDetails($RequestBody);
        }

        return $Data;
    }

    public function insertChemicalDetails($RequestBody)
    {
        $ChemID = $RequestBody['ChemID'];
        $ChemicalName = $RequestBody['ChemicalName'];
        $ChemicalDescription = $RequestBody['ChemicalDescription'];
        $NoOfKgPerBag = $RequestBody['NoOfKgPerBag'];
        $Status = $RequestBody['Status'];

        $sqlInsert = "INSERT INTO GsChemicalMaster(ChemID, ChemicalName, ChemicalDescription, NoOfKgPerBag, Status) 
            VALUES ('$ChemID','$ChemicalName','$ChemicalDescription','$NoOfKgPerBag', 1)";

        $stmtInsert = $this->conn->prepare($sqlInsert);
        $stmtInsert->execute();

        if ($stmtInsert) {

            $lastInsertedId = $this->conn->lastInsertId();
            $RequestBody['ChemID'] = $lastInsertedId;
            $createdChemical = $this->auditLogger->fetchRowById('GsChemicalMaster', 'ChemID', $lastInsertedId);
            $this->auditLogger->logInsert('GsChemicalMaster', $lastInsertedId, $createdChemical, $RequestBody, [
                'ReferenceNo' => $lastInsertedId,
                'Remarks' => 'Store chemical created',
            ]);

            $ChemicalData = $this->getChemicalDetails($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'Chemical Posted Successfully';
            $Data['Data'] = $ChemicalData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to Insert Chemical';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    public function updateChemicalDetails($RequestBody)
    {
        $ChemID = $RequestBody['ChemID'];
        $ChemicalName = $RequestBody['ChemicalName'];
        $ChemicalDescription = $RequestBody['ChemicalDescription'];
        $NoOfKgPerBag = $RequestBody['NoOfKgPerBag'];
        $Status = $RequestBody['Status'];
        $existingChemical = $this->auditLogger->fetchRowById('GsChemicalMaster', 'ChemID', $ChemID);

        $sqlUpdate = "UPDATE GsChemicalMaster SET ChemicalName='$ChemicalName',ChemicalDescription='$ChemicalDescription',
        NoOfKgPerBag='$NoOfKgPerBag', Status=1 
        WHERE ChemID='$ChemID'";

        $stmtUpdate = $this->conn->prepare($sqlUpdate);
        $stmtUpdate->execute();

        if ($stmtUpdate) {
            $updatedChemical = $this->auditLogger->fetchRowById('GsChemicalMaster', 'ChemID', $ChemID);
            $this->auditLogger->logUpdate('GsChemicalMaster', $ChemID, $existingChemical, $updatedChemical, $RequestBody, [
                'ReferenceNo' => $ChemID,
                'Remarks' => 'Store chemical updated',
            ]);
            $ChemicalData = $this->getChemicalDetails($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'Chemical Updated Successfully';
            $Data['Data'] = $ChemicalData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to update Chemical';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    function getChemicalDetails($RequestBody)
    {
        $RecordsPerPage = 10;
        $ChemID = $RequestBody['ChemID'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];

        $Query = '';

        if ($ChemID != "") {
            $Query .= " and GsChemicalMaster.ChemID = '$ChemID'";
        }

        $RecordsPerPage = 10;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query = " and CONCAT_WS('', ChemID,ChemicalName,ChemicalDescription,NoOfKgPerBag) LIKE '%$SearchKey%'";
        }

        $sqlVarietyCount = "SELECT COUNT(*) as Total  FROM `GsChemicalMaster` WHERE Status=1";

        $stmt = $this->conn->prepare($sqlVarietyCount);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "ChemID, ChemicalName, ChemicalDescription, NoOfKgPerBag";

        if ($ChemID != '') {
// echo 
            $sqlQuery = "SELECT $sqlFields FROM GsChemicalMaster WHERE Status=1 AND ChemID='$ChemID'";

        } else {
// echo 
            $sqlQuery = "SELECT $sqlFields FROM GsChemicalMaster
            WHERE Status=1 $Query" . $Limit;
        }
        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        $ArrayChemicalData = [];

        $i = $LimitMax;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayChemicalList['SNO'] = ++$i;
            $ArrayChemicalList['ChemID'] = $dataRow['ChemID'];
            $ArrayChemicalList['ChemicalName'] = $dataRow['ChemicalName'];
            $ArrayChemicalList['ChemicalDescription'] = $dataRow['ChemicalDescription'];
            $ArrayChemicalList['NoOfKgPerBag'] = $dataRow['NoOfKgPerBag'];

            $ArrayChemicalData[] =  $ArrayChemicalList;
        }

        if (count($ArrayChemicalData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TotalCount'] = $TotalCount;
        $data['RemainingCount'] = "";
        $data['EditLink'] = "GsChemicalCreate.php?ChemID=";
        $data['Data'] = $ArrayChemicalData;
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;

        return $data;
    }
}
