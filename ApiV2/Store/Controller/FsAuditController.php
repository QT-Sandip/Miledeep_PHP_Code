<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class FsAuditController
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

    public function postFsAuditDetails($RequestBody)
    {
        $FsaID = $RequestBody['FsaID'];

        if ($FsaID == '') {
            $Data = $this->insertFsAuditDetails($RequestBody);
        } else {
            $Data = $this->updateFsAuditDetails($RequestBody);
        }

        return $Data;
    }

    public function insertFsAuditDetails($RequestBody)
    {
        $FsaID = $RequestBody['FsaID'];
        $PlantID = $RequestBody['PlantID'];
        $AuditNameID = $RequestBody['AuditNameID'];
        $AuditTypeID = $RequestBody['AuditTypeID'];
        $CertAuthorityID = $RequestBody['CertAuthorityID'];
        $RegistrartionNo = $RequestBody['RegistrartionNo'];
        $CertificateNo = $RequestBody['CertificateNo'];
        $AuditStartDt = $RequestBody['AuditStartDt'];
        $AuditEndDt = $RequestBody['AuditEndDt'];
        $IssuedOnDt = $RequestBody['IssuedOnDt'];
        $AuditProgram = $RequestBody['AuditProgram']; 
        $ValidUpTo = $RequestBody['ValidUpTo'];
        $RenewaLDt = $RequestBody['RenewaLDt'];
        $AuditGrade = $RequestBody['AuditGrade'];
        $Status = $RequestBody['Status'];

        $sqlInsert = "INSERT INTO GsFoodNSocialAuditDetails(FsaID, PlantID, AuditNameID, AuditTypeID, CertAuthorityID, RegistrartionNo, 
        CertificateNo, AuditStartDt, AuditEndDt, IssuedOnDt, AuditProgram, ValidUpTo, RenewaLDt, AuditGrade, Status) 
        VALUES ('$FsaID','$PlantID','$AuditNameID','$AuditTypeID','$CertAuthorityID','$RegistrartionNo','$CertificateNo','$AuditStartDt',
        '$AuditEndDt', '$IssuedOnDt', '$AuditProgram', '$ValidUpTo', '$RenewaLDt', '$AuditGrade', '$Status')";

        $stmtInsert = $this->conn->prepare($sqlInsert);
        $stmtInsert->execute();

        if ($stmtInsert) {

            $lastInsertedId = $this->conn->lastInsertId();
            $RequestBody['FsaID'] = $lastInsertedId;
            $createdAudit = $this->auditLogger->fetchRowById('GsFoodNSocialAuditDetails', 'FsaID', $lastInsertedId);
            $this->auditLogger->logInsert('GsFoodNSocialAuditDetails', $lastInsertedId, $createdAudit, $RequestBody, [
                'ReferenceNo' => $lastInsertedId,
                'Remarks' => 'Store food and social audit created',
            ]);

            $fsAuditData = $this->getFsAuditDetails($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'FS Audit Posted Successfully';
            $Data['Data'] = $fsAuditData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to Insert FS Audit';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    public function updateFsAuditDetails($RequestBody)
    {
        $FsaID = $RequestBody['FsaID'];
        $PlantID = $RequestBody['PlantID'];
        $AuditNameID = $RequestBody['AuditNameID'];
        $AuditTypeID = $RequestBody['AuditTypeID'];
        $CertAuthorityID = $RequestBody['CertAuthorityID'];
        $RegistrartionNo = $RequestBody['RegistrartionNo'];
        $CertificateNo = $RequestBody['CertificateNo'];
        $AuditStartDt = $RequestBody['AuditStartDt'];
        $AuditEndDt = $RequestBody['AuditEndDt'];
        $IssuedOnDt = $RequestBody['IssuedOnDt'];
        $AuditProgram = $RequestBody['AuditProgram'];
        $ValidUpTo = $RequestBody['ValidUpTo'];
        $RenewaLDt = $RequestBody['RenewaLDt'];
        $AuditGrade = $RequestBody['AuditGrade'];
        $Status = $RequestBody['Status'];
        $existingAudit = $this->auditLogger->fetchRowById('GsFoodNSocialAuditDetails', 'FsaID', $FsaID);

        $sqlUpdate = "UPDATE GsFoodNSocialAuditDetails SET PlantID='$PlantID', AuditNameID='$AuditNameID', AuditTypeID='$AuditTypeID',
        CertAuthorityID='$CertAuthorityID', RegistrartionNo='$RegistrartionNo', CertificateNo='$CertificateNo', AuditStartDt='$AuditStartDt',
        AuditEndDt='$AuditEndDt', IssuedOnDt='$IssuedOnDt', AuditProgram='$AuditProgram', ValidUpTo='$ValidUpTo', RenewaLDt='$RenewaLDt',
        AuditGrade='$AuditGrade', Status='$Status'
        WHERE FsaID='$FsaID'";

        $stmtUpdate = $this->conn->prepare($sqlUpdate);
        $stmtUpdate->execute();

        if ($stmtUpdate) {
            $updatedAudit = $this->auditLogger->fetchRowById('GsFoodNSocialAuditDetails', 'FsaID', $FsaID);
            $this->auditLogger->logUpdate('GsFoodNSocialAuditDetails', $FsaID, $existingAudit, $updatedAudit, $RequestBody, [
                'ReferenceNo' => $FsaID,
                'Remarks' => 'Store food and social audit updated',
            ]);
            $fsAuditData = $this->getFsAuditDetails($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'FS Audit Updated Successfully';
            $Data['Data'] = $fsAuditData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to update FS Audit';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    function deleteFsAuditDetails($RequestBody)
    {
        $FsaID = $RequestBody['FsaID'];
        $existingAudit = $this->auditLogger->fetchRowById('GsFoodNSocialAuditDetails', 'FsaID', $FsaID);

        $sqlDelete = "DELETE FROM `GsFoodNSocialAuditDetails` WHERE FsaID='$FsaID'";

        $stmtUpdate = $this->conn->prepare($sqlDelete);
        $stmtUpdate->execute();

        if ($stmtUpdate) {
            $this->auditLogger->logDelete('GsFoodNSocialAuditDetails', $FsaID, $existingAudit, $RequestBody, [
                'ReferenceNo' => $FsaID,
                'Remarks' => 'Store food and social audit deleted',
            ]);
            $fsAuditData = $this->getFsAuditDetails($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'FS Audit Deleted Successfully';
            $Data['Data'] = $fsAuditData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to Delete FS Audit';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    function getFsAuditDetails($RequestBody)
    {

        $RecordsPerPage = 10;
        $FsaID = $RequestBody['FsaID'];
        $PlantID = $RequestBody['PlantID'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];

        $Query = '';

        if ($FsaID != "") {
            $Query .= " and GFSA.FsaID = '$FsaID'";
        }

        if ($PlantID != "") {
            $Query .= " and GFSA.PlantID = '$PlantID'";
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
            $Query = " and CONCAT_WS('', StationName,AuditNameID,AuditTypeID,CertAuthorityID, RegistrartionNo, CertificateNo) LIKE '%$SearchKey%'";
        }

        $sqlVarietyCount = "SELECT COUNT(*) as Total FROM `GsFoodNSocialAuditDetails`";

        $stmt = $this->conn->prepare($sqlVarietyCount);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "GFSA.FsaID, GFSA.PlantID, GFSA.AuditNameID, 
        CASE GFSA.AuditNameID WHEN 1 THEN 'APCB' WHEN 2 THEN 'Plant Approval' ELSE '' END AS AuditName,
        GFSA.AuditTypeID, 
        CASE GFSA.AuditTypeID WHEN 1 THEN 'FOOD' WHEN 2 THEN 'SOCIAL' WHEN 3 THEN 'GOVERNMENT' ELSE '' END AS AuditType,
        GFSA.CertAuthorityID, 
        CASE GFSA.CertAuthorityID WHEN 1 THEN 'Authority1' WHEN 2 THEN 'Authority2' ELSE '' END AS CertAuthority, 
        GFSA.RegistrartionNo, GFSA.CertificateNo, GFSA.AuditStartDt, GFSA.AuditEndDt, GFSA.IssuedOnDt,
        GFSA.AuditProgram, 
        CASE  GFSA.AuditProgram WHEN 0 THEN 'UnAnnounced' WHEN 1 THEN 'Announced' ELSE '' END AS AuditPrgm,
        GFSA.ValidUpTo, GFSA.RenewaLDt, 
        GFSA.AuditGrade,
        CASE GFSA.AuditGrade WHEN 1 THEN 'GradeA' WHEN 2 THEN 'GradeB' ELSE '' END AS AuditGrd,
        GFSA.Status, RPS.StationName";

        if ($FsaID != '') {

            $sqlQuery = "SELECT $sqlFields FROM GsFoodNSocialAuditDetails AS GFSA
            LEFT JOIN RmProcessingStations AS RPS ON GFSA.PlantID = RPS.ProcessingStationID
            WHERE FsaID='$FsaID'";

        } else {
 
            $sqlQuery = "SELECT $sqlFields FROM GsFoodNSocialAuditDetails AS GFSA
            LEFT JOIN RmProcessingStations AS RPS ON GFSA.PlantID = RPS.ProcessingStationID
            WHERE 1=1 $Query ORDER BY FsaID $Limit";

        }
        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();
        
        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($errorInfo);

        $ArrayFsAuditData = [];

        $i = $LimitMax;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayFsAuditList['SNO'] = ++$i;
            $ArrayFsAuditList['FsaID'] = $dataRow['FsaID'];
            $ArrayFsAuditList['PlantID'] = $dataRow['PlantID'];
            $ArrayFsAuditList['StationName'] = $dataRow['StationName'];
            $ArrayFsAuditList['AuditNameID'] = $dataRow['AuditNameID'];
            $ArrayFsAuditList['AuditName'] = $dataRow['AuditName'];
            $ArrayFsAuditList['AuditTypeID'] = $dataRow['AuditTypeID'];
            $ArrayFsAuditList['AuditType'] = $dataRow['AuditType'];
            $ArrayFsAuditList['CertAuthorityID'] = $dataRow['CertAuthorityID'];
            $ArrayFsAuditList['CertAuthority'] = $dataRow['CertAuthority'];
            $ArrayFsAuditList['RegistrartionNo'] = $dataRow['RegistrartionNo'];
            $ArrayFsAuditList['CertificateNo'] = $dataRow['CertificateNo'];
            $ArrayFsAuditList['AuditStartDt'] = $dataRow['AuditStartDt'];
            $ArrayFsAuditList['AuditEndDt'] = $dataRow['AuditEndDt'];
            $ArrayFsAuditList['IssuedOnDt'] = $dataRow['IssuedOnDt'];
            $ArrayFsAuditList['AuditProgram'] = $dataRow['AuditProgram'];
            $ArrayFsAuditList['AuditPrgm'] = $dataRow['AuditPrgm'];
            $ArrayFsAuditList['ValidUpTo'] = $dataRow['ValidUpTo'];
            $ArrayFsAuditList['RenewaLDt'] = $dataRow['RenewaLDt'];
            $ArrayFsAuditList['AuditGrade'] = $dataRow['AuditGrade'];
            $ArrayFsAuditList['AuditGrd'] = $dataRow['AuditGrd'];
            $ArrayFsAuditList['Status'] = $dataRow['Status'];

            $ArrayFsAuditData[] =  $ArrayFsAuditList;
        }

        if (count($ArrayFsAuditData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TotalCount'] = $TotalCount;
        $data['RemainingCount'] = "";
        $data['EditLink'] = "FsAuditCreate.php?FsaID=";
        $data['AddLink'] = "FsAuditPaymentCreate.php?AuditID=";
        $data['Data'] = $ArrayFsAuditData;
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;
        
        return $data;
    }


}
