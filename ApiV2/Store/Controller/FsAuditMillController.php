<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class FsAuditMillController
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

    public function postFsAuditMillDetails($RequestBody)
    {
        $AuditID = $RequestBody['AuditID'];

        if ($AuditID == '') {
            $Data = $this->insertFsAuditMillDetails($RequestBody);
        } else {
            $Data = $this->updateFsAuditMillDetails($RequestBody);
        }

        return $Data;
    }

    public function insertFsAuditMillDetails($RequestBody)
    {
        $AuditID = $RequestBody['AuditID'];
        $AuditType = $RequestBody['AuditType'];
        $FarmerID = $RequestBody['FarmerID'];
        $HatchryID = $RequestBody['HatchryID'];
        $PlantID = $RequestBody['PlantID'];
        $CertifiedBody = $RequestBody['CertifiedBody'];
        $AuthorityID = $RequestBody['AuthorityID'];
        $RegistrationNo = $RequestBody['RegistrationNo'];
        $BAPNo = $RequestBody['BAPNo'];
        $ProductionPerAnnum = $RequestBody['ProductionPerAnnum'];
        $WaterSpreadArea = $RequestBody['WaterSpreadArea'];
        $AuditDate = $RequestBody['AuditDate'];
        $IssuedOn = $RequestBody['IssuedOn'];
        $ValidtyUpto = $RequestBody['ValidtyUpto'];
        $Status = $RequestBody['Status'];

        $sqlInsert = "INSERT INTO GsAuditFHFDetails(AuditID, AuditType, FarmerID, HatchryID, PlantID, CertifiedBody, 
        AuthorityID, RegistrationNo, BAPNo, ProductionPerAnnum, WaterSpreadArea, AuditDate, IssuedOn, ValidtyUpto, Status) 
        VALUES ('$AuditID','$AuditType','$FarmerID','$HatchryID','$PlantID','$CertifiedBody','$AuthorityID','$RegistrationNo',
        '$BAPNo', '$ProductionPerAnnum', '$WaterSpreadArea', '$AuditDate', '$IssuedOn', '$ValidtyUpto', '$Status')";

        $stmtInsert = $this->conn->prepare($sqlInsert);
        $stmtInsert->execute();

        if ($stmtInsert) {

            $lastInsertedId = $this->conn->lastInsertId();
            $RequestBody['AuditID'] = $lastInsertedId;
            $createdAudit = $this->auditLogger->fetchRowById('GsAuditFHFDetails', 'AuditID', $lastInsertedId);
            $this->auditLogger->logInsert('GsAuditFHFDetails', $lastInsertedId, $createdAudit, $RequestBody, [
                'ReferenceNo' => $lastInsertedId,
                'Remarks' => 'Store FHF audit created',
            ]);

            $fsAuditMillData = $this->getFsAuditMillDetails($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'FS Mill Audit Posted Successfully';
            $Data['Data'] = $fsAuditMillData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to Insert FS Mill Audit';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    public function updateFsAuditMillDetails($RequestBody)
    {
        $AuditID = $RequestBody['AuditID'];
        $AuditType = $RequestBody['AuditType'];
        $FarmerID = $RequestBody['FarmerID'];
        $HatchryID = $RequestBody['HatchryID'];
        $PlantID = $RequestBody['PlantID'];
        $CertifiedBody = $RequestBody['CertifiedBody'];
        $AuthorityID = $RequestBody['AuthorityID'];
        $RegistrationNo = $RequestBody['RegistrationNo'];
        $BAPNo = $RequestBody['BAPNo'];
        $ProductionPerAnnum = $RequestBody['ProductionPerAnnum'];
        $WaterSpreadArea = $RequestBody['WaterSpreadArea'];
        $AuditDate = $RequestBody['AuditDate'];
        $IssuedOn = $RequestBody['IssuedOn'];
        $ValidtyUpto = $RequestBody['ValidtyUpto'];
        $Status = $RequestBody['Status'];
        $existingAudit = $this->auditLogger->fetchRowById('GsAuditFHFDetails', 'AuditID', $AuditID);

        $sqlUpdate = "UPDATE GsAuditFHFDetails SET AuditType='$AuditType', FarmerID='$FarmerID',HatchryID='$HatchryID', 
        PlantID='$PlantID', CertifiedBody='$CertifiedBody', AuthorityID='$AuthorityID',RegistrationNo='$RegistrationNo',
        BAPNo='$BAPNo', ProductionPerAnnum='$ProductionPerAnnum', WaterSpreadArea='$WaterSpreadArea', AuditDate='$AuditDate',
        IssuedOn='$IssuedOn',ValidtyUpto='$ValidtyUpto',Status='$Status'
        WHERE AuditID='$AuditID'";

        $stmtUpdate = $this->conn->prepare($sqlUpdate);
        $stmtUpdate->execute();

        // $errorInfo = $stmtUpdate->errorInfo();
        // var_dump($errorInfo);

        if ($stmtUpdate) {
            $updatedAudit = $this->auditLogger->fetchRowById('GsAuditFHFDetails', 'AuditID', $AuditID);
            $this->auditLogger->logUpdate('GsAuditFHFDetails', $AuditID, $existingAudit, $updatedAudit, $RequestBody, [
                'ReferenceNo' => $AuditID,
                'Remarks' => 'Store FHF audit updated',
            ]);
            $fsAuditMillData = $this->getFsAuditMillDetails($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'FS Mill Audit Updated Successfully';
            $Data['Data'] = $fsAuditMillData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to update FS Mill Audit';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    // function deleteFsAuditPaymentDetails($RequestBody)
    // {
    //     $AuditID = $RequestBody['AuditID'];

    //     $sqlDelete = "DELETE FROM `GsAuditFHFDetails` WHERE AuditID='$AuditID'";

    //     $stmtUpdate = $this->conn->prepare($sqlDelete);
    //     $stmtUpdate->execute();

    //     if ($stmtUpdate) {
    //         $fsAuditMillData = $this->getFsAuditMillDetails($RequestBody);
    //         $Data['Error'] = null;
    //         $Data['Msg'] = 'FS Mill Audit Deleted Successfully';
    //         $Data['Data'] = $fsAuditMillData['Data'];
    //         return $Data;
    //     } else {
    //         $Data['Error'] = 'Unable to Delete FS Mill Audit';
    //         $Data['Msg'] = $this->Error;
    //         return $Data;
    //     }
    // }

    function getFsAuditMillDetails($RequestBody)
    {
        $RecordsPerPage = 10;
        $AuditID = $RequestBody['AuditID'];
        // $ChemPoDate = $RequestBody['ChemPoDate'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];

        $Query = '';


        if ($AuditID != "") {
            $Query .= " and GAFD.AuditID = '$AuditID'";
        }

        // if ($ChemPoDate != "") {
        //     $yearFilter = date('Y', strtotime($ChemPoDate));
        //     $Query .= " AND YEAR(GCPs.ChemPoDate) = '$yearFilter'";
        // }

        $RecordsPerPage = 10;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query = " and CONCAT_WS('', RegistrationNo,FarmerName,HatcryName,StationName,HatcryLocation, BAPNo) LIKE '%$SearchKey%'";
        }

        $sqlVarietyCount = "SELECT COUNT(*) as Total  FROM `GsAuditFHFDetails`";

        $stmt = $this->conn->prepare($sqlVarietyCount);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        //     CASE GFSA.AuditNameID 
        //     WHEN 1 THEN 'APCB'
        //     WHEN 2 THEN 'Plant Approval'
        //     ELSE ''
        // END AS AuditName

        $sqlFields = "GAFD.AuditID, GAFD.AuditType, 
        CASE GAFD.AuditType WHEN 1 THEN 'Farm' WHEN 2 THEN 'Hatchery' WHEN 3 THEN 'Feed Mill' ELSE '' END AS Audit,
        GAFD.FarmerID, GAFD.HatchryID, GAFD.PlantID, GAFD.CertifiedBody,
        CASE GAFD.CertifiedBody WHEN 1 THEN 'BAP' WHEN 2 THEN 'AOC' ELSE '' END AS CertiBody, GAFD.AuthorityID, 
        CASE GAFD.AuthorityID WHEN 1 THEN 'MPEDA' WHEN 2 THEN 'EIA' WHEN 3 THEN 'MINISTRY OF FISHIRIES' ELSE '' END AS Authority,
        GAFD.RegistrationNo, GAFD.BAPNo, GAFD.ProductionPerAnnum, GAFD.WaterSpreadArea, GAFD.Status, GAFD.AuditDate, GAFD.IssuedOn,
        GAFD.ValidtyUpto, GHM.HatcryName, GHM.HatcryLocation, QTL.FarmerName, RPS.StationName,
        GROUP_CONCAT(CONCAT(GHM.HatcryName, ' - ', GHM.HatcryLocation) ORDER BY  GAFD.HatchryID SEPARATOR ', ') AS Hatchery";

        if ($AuditID != '') {

            $sqlQuery = "SELECT $sqlFields FROM GsAuditFHFDetails AS GAFD 
            LEFT JOIN GsHatchryMaster AS GHM ON GAFD.HatchryID = GHM.HatcheryID
            LEFT JOIN QcTracebilityList AS QTL ON GAFD.FarmerID = QTL.TraceID
            LEFT JOIN RmProcessingStations AS RPS ON GAFD.PlantID = RPS.ProcessingStationID
            WHERE AuditID='$AuditID'";
        } else {
 
            $sqlQuery = "SELECT $sqlFields FROM GsAuditFHFDetails AS GAFD 
            LEFT JOIN GsHatchryMaster AS GHM ON GAFD.HatchryID = GHM.HatcheryID
            LEFT JOIN QcTracebilityList AS QTL ON GAFD.FarmerID = QTL.TraceID
            LEFT JOIN RmProcessingStations AS RPS ON GAFD.PlantID = RPS.ProcessingStationID
            WHERE 1=1 
            GROUP BY GAFD.AuditID, GAFD.AuditType, GAFD.FarmerID, GAFD.HatchryID, GAFD.PlantID, GAFD.CertifiedBody,GAFD.AuthorityID, 
            GAFD.RegistrationNo, GAFD.BAPNo, GAFD.ProductionPerAnnum, GAFD.WaterSpreadArea, GAFD.Status, GAFD.AuditDate, GAFD.IssuedOn, 
            GAFD.ValidtyUpto
            ORDER BY AuditID " . $Limit;
        }
        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        $ArrayFsAuditMillData = [];

        $i = $LimitMax;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayFsAuditMillList['SNO'] = ++$i;
            $ArrayFsAuditMillList['AuditID'] = $dataRow['AuditID'];
            $ArrayFsAuditMillList['AuditType'] = $dataRow['AuditType'];
            $ArrayFsAuditMillList['Audit'] = $dataRow['Audit'];
            $ArrayFsAuditMillList['FarmerID'] = $dataRow['FarmerID'];
            $ArrayFsAuditMillList['FarmerName'] = $dataRow['FarmerName'];
            $ArrayFsAuditMillList['HatchryID'] = $dataRow['HatchryID'];
            $ArrayFsAuditMillList['Hatchery'] = $dataRow['Hatchery'];
            $ArrayFsAuditMillList['HatcryName'] = $dataRow['HatcryName'];
            $ArrayFsAuditMillList['HatcryLocation'] = $dataRow['HatcryLocation'];
            $ArrayFsAuditMillList['PlantID'] = $dataRow['PlantID'];
            $ArrayFsAuditMillList['StationName'] = $dataRow['StationName'];
            $ArrayFsAuditMillList['CertifiedBody'] = $dataRow['CertifiedBody'];
            $ArrayFsAuditMillList['CertiBody'] = $dataRow['CertiBody'];
            $ArrayFsAuditMillList['AuthorityID'] = $dataRow['AuthorityID'];
            $ArrayFsAuditMillList['Authority'] = $dataRow['Authority'];
            $ArrayFsAuditMillList['RegistrationNo'] = $dataRow['RegistrationNo'];
            $ArrayFsAuditMillList['BAPNo'] = $dataRow['BAPNo'];
            $ArrayFsAuditMillList['ProductionPerAnnum'] = $dataRow['ProductionPerAnnum'];
            $ArrayFsAuditMillList['WaterSpreadArea'] = $dataRow['WaterSpreadArea'];
            $ArrayFsAuditMillList['AuditDate'] = $dataRow['AuditDate'];
            $ArrayFsAuditMillList['IssuedOn'] = $dataRow['IssuedOn'];
            $ArrayFsAuditMillList['ValidtyUpto'] = $dataRow['ValidtyUpto'];
            $ArrayFsAuditMillList['Status'] = $dataRow['Status'];

            $ArrayFsAuditMillData[] =  $ArrayFsAuditMillList;
        }

        if (count($ArrayFsAuditMillData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TotalCount'] = $TotalCount;
        $data['RemainingCount'] = "";
        $data['EditLink'] = "FsAuditMillCreate.php?AuditID=";
        $data['AddLink'] = "FsAuditMillPaymentCreate.php?FHFAuditID=";
        $data['Data'] = $ArrayFsAuditMillData;
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;

        return $data;
    }
}
