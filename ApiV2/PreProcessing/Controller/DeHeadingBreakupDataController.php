<?php
include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';


class DeHeadingBreakupDataController
{
    private $conn;
    private $utilController;
    private $Error = null;
    private $Msg = '';
    private $auditLogger;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
        $this->restApi = new REST();
        $this->auditLogger = new CentralAuditLogger($this->conn, 'PreProcessing');

    }
    function getToken()
	{
		$authToken = $this->restApi->getRequestHeader();
		return $payload = Token::Verify($authToken);
	}

    function getBreakUpData($RequestBody)
    {
        $Data=[];
        $SaveType=$RequestBody['SaveType'];
        $DHID=$RequestBody['DHID'];

        if (empty($this->Msg)) {
            // Only set Msg if it's not already set
            $this->Msg = "Success";  // Default message
        }
        
        $sql= "SELECT *,RDL.DefectName AS Name,PVS.UnitName,RUL.UserName AS SupervisorName,
                    DATE_FORMAT(PDB.CreatedOn, '%d-%m-%Y %H:%i:%s') AS CreatedOn,
                    DATE_FORMAT(PDB.UpdatedOn, '%d-%m-%Y %H:%i:%s') AS UpdatedOn ,
                    PDB.ID AS BreakUpId
                FROM PpcDeheadingBreakup PDB 
                LEFT JOIN RmDefectsList RDL ON RDL.DefectID=PDB.VarietyID
                LEFT JOIN PpcVAStations PVS ON PVS.StationID = PDB.CenterId
                LEFT JOIN RmUsersList RUL ON RUL.ID = PDB.SuperVisorId
                WHERE ParentDHID='$DHID' "; 
        $stmt = $this->conn->prepare($sql); 
        $stmt->execute();
         $i = 0;
        $Data=[];
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $dataRow['SNo'] = ++$i;
            $Data[] = $dataRow;
        }    
        $responseData['Error'] = $this->Error;
        $responseData['Msg'] = $this->Msg;
        $responseData['Data'] = $Data;
        return $responseData;
    }
    public function postBreakUpData($RequestBody)
    { 
        $BreakupId = $RequestBody['BreakupId']; 
        $DHID = $RequestBody['DHID'];
        $Variety = $RequestBody['Variety'];
        $NoOfPieces = $RequestBody['NoofPieces'];
        $PostedUserID = $RequestBody['UserID'];
        date_default_timezone_set("Asia/Kolkata"); // Set to your timezone
        $PostedDateTime = date("Y-m-d H:i:s");
        $SuperVisorId = $RequestBody['superVisor'];
        $CenterId = $RequestBody['center'];

        if(!empty($BreakupId)){
            $sqlQuery = "UPDATE  `PpcDeheadingBreakup` SET `ParentDHID` = '$DHID',VarietyID='$Variety',NoOfPeaces='$NoOfPieces',UpdatedOn='$PostedDateTime',UpdatedBy='$PostedUserID',CenterId='$CenterId',SuperVisorId='$SuperVisorId' WHERE ID=$BreakupId";
            $msg = "Updated Successfully";
            $error = "Unable To Update Data";
        }else{
             $sqlQuery = "INSERT INTO `PpcDeheadingBreakup`( `ParentDHID`, `VarietyID`, `NoOfPeaces`, `CreatedOn`, `CreatedBy`,SuperVisorId,CenterId) 
            VALUES('$DHID','$Variety','$NoOfPieces','$PostedDateTime','$PostedUserID','$SuperVisorId','$CenterId')";  
            $msg = "Added Successfully";   
            $error = "Unable To Add Data";        
        } //echo '<pre>';echo $sqlQuery;die;
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
            
        if ($stmt) {
            $this->Error = null;
            $this->Msg = $msg; 
            if (!empty($BreakupId)) {
                $updatedBreakup = $this->auditLogger->fetchRowById('PpcDeheadingBreakup', 'ID', $BreakupId);
                $this->auditLogger->logUpdate('PpcDeheadingBreakup', $BreakupId, $existingBreakup, $updatedBreakup, $RequestBody, [
                    'ReferenceNo' => $DHID,
                    'Remarks' => 'PreProcessing deheading breakup updated',
                ]);
            } else {
                $lastInsertId = $this->conn->lastInsertId();
                $createdBreakup = $this->auditLogger->fetchRowById('PpcDeheadingBreakup', 'ID', $lastInsertId);
                $this->auditLogger->logInsert('PpcDeheadingBreakup', $lastInsertId, $createdBreakup, $RequestBody, [
                    'ReferenceNo' => $DHID,
                    'Remarks' => 'PreProcessing deheading breakup created',
                ]);
            }
        } else {
            $this->Error = $error;
            $this->Msg = $error;
        }
          
      
        $breakUpData = $this->getBreakUpData($RequestBody);
        //echo '<pre>';print_r($breakUpData['Data']);die;
        $Data['Error'] = null;
        $Data['Msg'] = $this->Msg ;
        $Data['Data'] = $breakUpData['Data'];
        return $Data; 
    }
    public function deleteBreakUpData($RequestBody)
    {
        $ID = $RequestBody['ID'];
        $existingBreakup = null;
        if($ID){
            $existingBreakup = $this->auditLogger->fetchRowById('PpcDeheadingBreakup', 'ID', $ID);
            $SQL = "DELETE FROM `PpcDeheadingBreakup` WHERE ID='$ID'"; //delete crate data
            $stmtDelete = $this->conn->prepare($SQL);
            $stmtDelete->execute();
        }

              
        $msg = "Unable to  Deleted";
        if($stmtDelete){               
            $msg = "Breakup Deleted  Successfully";
            $this->auditLogger->logDelete('PpcDeheadingBreakup', $ID, $existingBreakup, $RequestBody, [
                'ReferenceNo' => $ID,
                'Remarks' => 'PreProcessing deheading breakup deleted',
            ]);
        }
        $BreakUpData = $this->getBreakUpData($RequestBody);

        $responseData['Error'] = null;
        $responseData['Msg'] = $msg;

        $responseData['Data'] = $BreakUpData['Data'];

        return $responseData;
        
    }

   
}
