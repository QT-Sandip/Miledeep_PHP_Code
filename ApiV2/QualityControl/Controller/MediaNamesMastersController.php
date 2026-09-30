<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class MediaNamesMastersController
{
    private $conn;
    private $utilController;
    private $Error = null;
    private $Msg   = '';
    private $auditLogger;

    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->auditLogger = new CentralAuditLogger($this->conn, 'QualityControl');
        // $this->utilController = new UtilController($this->conn);
    }

    public function postMediaDetails($RequestBody)
    {
        $MediaName   = $RequestBody['MediaName'];
        $ParameterID = $RequestBody['ParameterId'];
        
        $checkDup = "SELECT ParameterID,MediaName from usedmediamaster where ParameterID = '".$ParameterID."' and MediaName = '".$MediaName."' ";        
        $stmtCheckDuplicate = $this->conn->prepare($checkDup);
        $stmtCheckDuplicate->execute();

        if($stmtCheckDuplicate){
            $rowFindDuplicate = $stmtCheckDuplicate->fetch(PDO::FETCH_ASSOC);
            if( isset($rowFindDuplicate['ParameterID']) && $rowFindDuplicate['ParameterID'] ){
                $Data['Error'] = 'This media name already exist';
                $Data['Msg']   = 'This media name already exist';
                return $Data;
            }
        }        
        $MediaId = $RequestBody['MediaId'];

        if ($MediaId == '' || $MediaId == 0 ) {
            $Data = $this->insertMediaDetails($RequestBody);
        } else {
            $Data = $this->updateMediaDetails($RequestBody);
        }

        return $Data;
    }

    public function insertMediaDetails($RequestBody)
    {

        $MediaName   = $RequestBody['MediaName'];
        $ParameterID = $RequestBody['ParameterId'];

        // SELECT MediaId, MediaName, Address, RepresentationDescription, Status, Color FROM

        $sqlInsert = "INSERT INTO usedmediamaster(ParameterID, MediaName, ip, CreatedBy)
        VALUES ('" . $ParameterID . "','" . $MediaName . "', '" . $_SERVER['REMOTE_ADDR'] . "', '" . $_SESSION["UserID"] . "')";

        $stmtInsert = $this->conn->prepare($sqlInsert);
        $stmtInsert->execute();

        if ($stmtInsert) {

            $lastInsertedId         = $this->conn->lastInsertId();
            $RequestBody['MediaId'] = $lastInsertedId;
            $createdMedia = $this->auditLogger->fetchRowById('usedmediamaster', 'MediaId', $lastInsertedId);
            $this->auditLogger->logInsert('usedmediamaster', $lastInsertedId, $createdMedia, $RequestBody, [
                'ReferenceNo' => $lastInsertedId,
                'Remarks' => 'QC media master created',
            ]);

            //$MediaData     = $this->getMediaDetails($RequestBody);
            $Data['Error'] = null;
            $Data['Msg']   = 'Media Posted Successfully';
            $Data['Data']  = $MediaData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to Insert Media';
            $Data['Msg']   = $this->Error;
            return $Data;
        }
    }

    public function updateMediaDetails($RequestBody)
    {
        $MediaId     = $RequestBody['MediaId'];
        $MediaName   = $RequestBody['MediaName'];
        $ParameterID = $RequestBody['ParameterId'];
        $existingMedia = $this->auditLogger->fetchRowById('usedmediamaster', 'MediaId', $MediaId);

        $sqlUpdate = "UPDATE usedmediamaster SET ParameterID='$ParameterID', MediaName='$MediaName'
        WHERE MediaId='$MediaId'";

        $stmtUpdate = $this->conn->prepare($sqlUpdate);
        $stmtUpdate->execute();

        if ($stmtUpdate) {
            $updatedMedia = $this->auditLogger->fetchRowById('usedmediamaster', 'MediaId', $MediaId);
            $this->auditLogger->logUpdate('usedmediamaster', $MediaId, $existingMedia, $updatedMedia, $RequestBody, [
                'ReferenceNo' => $MediaId,
                'Remarks' => 'QC media master updated',
            ]);
            $MediaData     = $this->getMediaDetails($RequestBody);
            $Data['Error'] = null;
            $Data['Msg']   = 'Media Updated Successfully';
            $Data['Data']  = $MediaData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to update Media';
            $Data['Msg']   = $this->Error;
            return $Data;
        }
    }

    public function getMediaDetails($RequestBody)
    {

        $MediaId   = $RequestBody['MediaId'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo    = $RequestBody['PageNo'];
        $LoadAll   = $RequestBody['LoadAll'];        
        $Query = '';

        $RecordsPerPage = 10;
        $LimitMax       = ($PageNo - 1) * $RecordsPerPage;
        $Limit          = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query = " and CONCAT_WS('', MediaName,MediaId) LIKE '%$SearchKey%'";
        }
        if(isset($RequestBody['LoadAll']) && $MediaId == ''){
            $Query .= " AND Status = 1 ";
        }

        $sqlVarietyCount = "SELECT COUNT(*) as Total FROM `usedmediamaster`";

        $stmt = $this->conn->prepare($sqlVarietyCount);
        $stmt->execute();
        $dataRow    = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount  = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "ParameterID, MediaId, MediaName";

        if ($MediaId != "") {
            $sqlQuery = "SELECT $sqlFields FROM usedmediamaster WHERE MediaId='$MediaId'";
        } else {
            $sqlQuery = "SELECT $sqlFields FROM usedmediamaster
            WHERE 1=1 $Query " . $Limit;
        }
        //echo $sqlQuery; exit;
        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        $ArrayMediaData = [];

        $i = $LimitMax;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            //Paramaters list
            $SqlParameterList  = "SELECT TestName as ParameterName FROM `QcTestList` where TestID = '".$dataRow['ParameterID']."' ";
            $StmtParameterList = $this->conn->prepare($SqlParameterList);
            $StmtParameterList->execute();
            $ParameterName = "";
            if($StmtParameterList){
                $RowParameterList = $StmtParameterList->fetch(PDO::FETCH_ASSOC) ;
            }
            //end here

            $ArrayMediaList['SNO']         = ++$i;
            $ArrayMediaList['MediaId']     = $dataRow['MediaId'];
            $ArrayMediaList['MediaName']   = $dataRow['MediaName'];
            $ArrayMediaList['ParameterID'] = $dataRow['ParameterID'];
            $ArrayMediaList['ParameterName'] = $RowParameterList['ParameterName'];
            $ArrayMediaData[]              = $ArrayMediaList;
        }
        if (count($ArrayMediaData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        $data['Error']       = null;
        $data['Msg']         = 'Success';
        $data['TotalCount']  = $TotalCount;
        $data['EditLink']    = "Media-names-create.php?ParameterId=";
        $data['Data']        = $ArrayMediaData;
        $data['PageCount']   = $PageCount;
        $data['RecordsAval'] = $RecordsAval;

        return $data;
    }
}
