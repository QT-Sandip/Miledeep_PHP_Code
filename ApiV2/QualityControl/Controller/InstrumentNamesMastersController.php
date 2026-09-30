<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class InstrumentNamesMastersController
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

    public function postInstrumentDetails($RequestBody)
    {
        $InstrumentId = $RequestBody['InstrumentId'];
        $ParameterID = $RequestBody['ParameterId'];
        $MediaId = $RequestBody['MediaId'];
        $InstrumentName   = $RequestBody['InstrumentName'];
         $CalibrationDate  = $RequestBody['CalibrationDate'];
        $DueDate  = $RequestBody['DueDate'];

        $checkDup = "SELECT ParameterID,MediaID,InstrumentName from instrumentmaster where ParameterID = '".$ParameterID."' and MediaId = '".$MediaId."' and InstrumentName = '".$InstrumentName."' ";        
        $stmtCheckDuplicate = $this->conn->prepare($checkDup);
        $stmtCheckDuplicate->execute();

        if($stmtCheckDuplicate){
            $rowFindDuplicate = $stmtCheckDuplicate->fetch(PDO::FETCH_ASSOC);
            if( isset($rowFindDuplicate['ParameterID']) && $rowFindDuplicate['ParameterID'] ){
                $Data['Error'] = 'This equipment name already exist';
                $Data['Msg']   = 'This equipment name already exist';
                return $Data;
            }
        }    

         $RequestBody['CalibrationDate'] = $CalibrationDate;
        $RequestBody['DueDate'] = $DueDate;

        if ($InstrumentId == '' || $InstrumentId == 0 ) {
            $Data = $this->insertInstrumentDetails($RequestBody);
        } else {
            $Data = $this->updateInstrumentDetails($RequestBody);
        }

        return $Data;
    }

    public function insertInstrumentDetails($RequestBody)
    {

        $InstrumentName   = $RequestBody['InstrumentName'];
        $ParameterID = $RequestBody['ParameterId'];
        $MediaId = $RequestBody['MediaId'];
        $CalibrationDate  = $RequestBody['CalibrationDate'];
        $DueDate          = $RequestBody['DueDate'];

        $sqlInsert = "INSERT INTO instrumentmaster(ParameterID,MediaID,InstrumentName, CalibrationDate, DueDate, ip, CreatedBy)
        VALUES ('" . $ParameterID . "','" . $MediaId . "','" . $InstrumentName ."','" .
        $CalibrationDate ."','" .$DueDate . "', '" . $_SERVER['REMOTE_ADDR'] . "', '" . $_SESSION["UserID"] . "')";
           
        $stmtInsert = $this->conn->prepare($sqlInsert);
        $stmtInsert->execute();

        if ($stmtInsert) {

            $lastInsertedId         = $this->conn->lastInsertId();
            $RequestBody['MediaId'] = $lastInsertedId;
            $createdInstrument = $this->auditLogger->fetchRowById('instrumentmaster', 'InstrumentId', $lastInsertedId);
            $this->auditLogger->logInsert('instrumentmaster', $lastInsertedId, $createdInstrument, $RequestBody, [
                'ReferenceNo' => $lastInsertedId,
                'Remarks' => 'QC instrument master created',
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

    public function updateInstrumentDetails($RequestBody)
    {
        $MediaId     = $RequestBody['MediaId'];
        $InstrumentId   = $RequestBody['InstrumentId'];
        $InstrumentName   = $RequestBody['InstrumentName'];
        $ParameterID = $RequestBody['ParameterId'];
         $CalibrationDate  = $RequestBody['CalibrationDate'];
        $DueDate          = $RequestBody['DueDate'];
        $existingInstrument = $this->auditLogger->fetchRowById('instrumentmaster', 'InstrumentId', $InstrumentId);

        $sqlUpdate = "UPDATE instrumentmaster SET ParameterID='$ParameterID', MediaID='$MediaId', InstrumentName='$InstrumentName', CalibrationDate='$CalibrationDate',
                          DueDate='$DueDate'
        WHERE InstrumentId='$InstrumentId'";

        $stmtUpdate = $this->conn->prepare($sqlUpdate);
        $stmtUpdate->execute();

        if ($stmtUpdate) {
            $updatedInstrument = $this->auditLogger->fetchRowById('instrumentmaster', 'InstrumentId', $InstrumentId);
            $this->auditLogger->logUpdate('instrumentmaster', $InstrumentId, $existingInstrument, $updatedInstrument, $RequestBody, [
                'ReferenceNo' => $InstrumentId,
                'Remarks' => 'QC instrument master updated',
            ]);
            $InstrumentData     = $this->getInstrumentDetails($RequestBody);
            $Data['Error'] = null;
            $Data['Msg']   = 'Instrument Updated Successfully';
            $Data['Data']  = $InstrumentData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to update Media';
            $Data['Msg']   = $this->Error;
            return $Data;
        }
    }

    public function getInstrumentDetails($RequestBody)
    {

        $InstrumentId   = $RequestBody['InstrumentId'];
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
            $Query = " and CONCAT_WS('', InstrumentName,InstrumentId) LIKE '%$SearchKey%'";
        }

        $sqlVarietyCount = "SELECT COUNT(*) as Total FROM `instrumentmaster`";

        $stmt = $this->conn->prepare($sqlVarietyCount);
        $stmt->execute();
        $dataRow    = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount  = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "ParameterID, MediaID, InstrumentId, InstrumentName,CalibrationDate, DueDate";

        if ($InstrumentId != "" || $InstrumentId !=0) {
            $sqlQuery = "SELECT $sqlFields FROM instrumentmaster WHERE InstrumentId='$InstrumentId'";
        } else {
            $sqlQuery = "SELECT $sqlFields FROM instrumentmaster
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

            //Media list
            $SqlMediaList = "SELECT MediaName FROM `usedmediamaster` where MediaID = '".$dataRow['MediaID']."' ";  
            $SqlMediaList = $this->conn->prepare($SqlMediaList);
            $SqlMediaList->execute();
            if($SqlMediaList){
                $rowMediaList = $SqlMediaList->fetch(PDO::FETCH_ASSOC) ;
            }
            //end here

            $ArrayMediaList['SNO']         = ++$i;
            $ArrayMediaList['MediaName']     = $rowMediaList['MediaName'];
            $ArrayMediaList['MediaId']   = $dataRow['MediaID'];
            $ArrayMediaList['ParameterID'] = $dataRow['ParameterID'];
            $ArrayMediaList['ParameterName'] = $RowParameterList['ParameterName'];
            $ArrayMediaList['InstrumentId']   = $dataRow['InstrumentId'];
            $ArrayMediaList['InstrumentName'] = $dataRow['InstrumentName'];
              $ArrayMediaList['CalibrationDate'] = $dataRow['CalibrationDate'];
                $ArrayMediaList['DueDate'] = $dataRow['DueDate'];
            
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
