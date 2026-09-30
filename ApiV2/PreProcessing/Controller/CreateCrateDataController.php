<?php
include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';


class CreateCrateDataController
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

    function getCrateData($RequestBody)
    {
        $Data=[];
        $SaveType=$RequestBody['SaveType'];
        $LotID=$RequestBody['LotID'];

        if (empty($this->Msg)) {
            // Only set Msg if it's not already set
            $this->Msg = "Success";  // Default message
        }
        
        $sqlLotNumber= "SELECT LotNumber FROM RmLotDetatils WHERE RmLotID='$LotID' "; 
        $stmtLotNumber = $this->conn->prepare($sqlLotNumber); 
        $stmtLotNumber->execute();
        $dataRowLotNumber = $stmtLotNumber->fetch(PDO::FETCH_ASSOC);
        $LotNumber=$dataRowLotNumber['LotNumber'];
        $Data['LotNumber']=$LotNumber;

        if($SaveType=='PlantCheck'){

            $Data['PlantCheckDetails'] = $this->getPlantCheckDetails($RequestBody);
                   
        }
        else if($SaveType=='Crate'){

           $Data['PlantCheckDetails'] = $this->getCrateDetails($RequestBody);
       
        }  else if($SaveType=='save'){

           $Data['PlantCheckDetails'] = $this->getPlantDetails($RequestBody);
       
        }  

        $responseData['Error'] = $this->Error;
        $responseData['Msg'] = $this->Msg;

        $responseData['Data'] = $Data;

        return $responseData;
    }
  
    function getPlantCheckDetails($RequestBody)
    {

        $Variety = $RequestBody['Variety'];
        $LotID = $RequestBody['LotID'];
        $ArrivalID = $RequestBody['ArrivalID'];
        $Query="";
        if($ArrivalID != ''){
             $Query=" AND ArrivalID='$ArrivalID'";
        }

      

        $sqlFields = "PCD.CrateDataID, DefectName as Variety,NoOfPieces,VarietyDefectID,PlantID,RUL.UserName,PCD.SuperVisorId";  

         $sqlQuery = "SELECT $sqlFields FROM  `PpcPlantCheckData` as PCD  
        left join RmDefectsList as RDL on RDL.DefectID =PCD.VarietyDefectID  
        LEFT JOIN RmUsersList RUL ON RUL.ID = PCD.SuperVisorId 
        WHERE LotID='$LotID' $Query";  

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();
       

        $i = 0;
        $ArrayCrateData=[];
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
          
            $CrateData['SNo'] = ++$i;
            $CrateData['CrateDataID'] = $dataRow['CrateDataID']; // ReceivingID treating as TrackID
            $CrateData['VarietyOrCrateNo'] = $dataRow['Variety']; 
            $CrateData['VarietyDefectID'] = $dataRow['VarietyDefectID']; 
            $CrateData['NoOfPiecesOrWeight'] = $dataRow['NoOfPieces'];
            $CrateData['UserName'] = $dataRow['UserName'];
            $CrateData['SuperVisorId'] = $dataRow['SuperVisorId'];
            $ArrayCrateData[]=$CrateData;

              }
              if (empty($ArrayCrateData)) {
                if (empty($this->Msg)) {
                $this->Msg = "No data found."; 
                }// Set the message when no data is found
            } else {
                if (empty($this->Msg)) {
                $this->Msg = "Success"; // Set success message
                }
            }
        
        return $ArrayCrateData;
    }

     function getPlantDetails($RequestBody)
    {

        $LotID = $RequestBody['LotID'];
        $ArrivalID = $RequestBody['ArrivalID'];
        $Query="";
        if($ArrivalID != ''){
             $Query=" AND ArrivalID='$ArrivalID'";
        }

      

        $sqlFields = "PlantID";  

         $sqlQuery = "SELECT $sqlFields FROM  `PPCPlantData` as PCD WHERE LotID='$LotID' $Query";  

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();
       

        $i = 0;
        $ArrayCrateData=[];
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
          
            $CrateData['SNo'] = ++$i;
            $CrateData['PlantID'] = $dataRow['PlantID']; 
            $ArrayCrateData[]=$CrateData;

              }
              if (empty($ArrayCrateData)) {
                if (empty($this->Msg)) {
                $this->Msg = "No data found."; 
                }// Set the message when no data is found
            } else {
                if (empty($this->Msg)) {
                $this->Msg = "Success"; // Set success message
                }
            }
        
        return $ArrayCrateData;
    }


    function getCrateDetails($RequestBody)
    {
        $LotID = $RequestBody['LotID'];
        $ArrivalID = $RequestBody['ArrivalID'];
        $Query="";


        if ($ArrivalID != '' && $ArrivalID != 0) {
            $Query = "AND ArrivalID='$ArrivalID'";
        }
        $sqlFields = "CrateNo,Weight,CrateDataBreakUpID,ActualCrateWeight";    
        

        $sqlBreakUpQuery = "SELECT $sqlFields FROM PpcCratesData WHERE LotID='$LotID' $Query"; 

        $stmtBreakupSelect = $this->conn->prepare($sqlBreakUpQuery);
        $stmtBreakupSelect->execute();
       

        $i = 0;
        $ArrayCrateData=[];
        while ($dataRowBreakup = $stmtBreakupSelect->fetch(PDO::FETCH_ASSOC)) {
            $LotNumber=$dataRowBreakup['LotNumber'];

            $CratebreakUpData['SNo'] = ++$i;
            $CratebreakUpData['CrateDataID'] = $dataRowBreakup['CrateDataBreakUpID']; // ReceivingID treating as TrackID
            $CratebreakUpData['VarietyDefectID'] = ''; 
            $CratebreakUpData['VarietyOrCrateNo'] = $dataRowBreakup['CrateNo']; 
            $CratebreakUpData['NoOfPiecesOrWeight'] = $dataRowBreakup['Weight']; 
            $CratebreakUpData['ActualCrateWeight'] = $dataRowBreakup['ActualCrateWeight']; 

           
            $ArrayCrateData[] = $CratebreakUpData;
              }

              if (empty($ArrayCrateData)) {
                if (empty($this->Msg)) {
                $this->Msg = "No data found."; // Set the message when no data is found
                }
            } else {
                if (empty($this->Msg)) {
                $this->Msg = "Success"; // Set success message
                }
            }

        return $ArrayCrateData;
    }


    public function postCrateData($RequestBody)
    {
         $Token = $this->getToken(); 
         $RequestBody['UserID']=$Token['UserID']; 
         $SaveType=$RequestBody['SaveType']; 

        if($SaveType=='PlantCheck'){
            $status = $this->postPlantCheckDetails($RequestBody); 

        }
        else if($SaveType=='Crate'){

            $status = $this->postLotWiseCrateDetails($RequestBody);  

        }   else if($SaveType=='save'){
            $status = $this->postPlantDetails($RequestBody); 
        }    
            if ($status) {
                $this->Error = null;
                $this->Msg = $status['Msg']; 
            } else {
                $this->Error = "Unable To Update Data";
                $this->Msg = "Unable To Update Data";
            }
          
      
        $receivalData = $this->getCrateData($RequestBody);
        $Data['Error'] = null;
        $Data['Msg'] = $this->Msg ;
        $Data['Data'] = $receivalData['Data'];
        return $Data; 
    }
    public function postPlantCheckDetails($RequestBody)
    {

        $LotID = $RequestBody['LotID'];
        $Variety = $RequestBody['Variety'];
        $NoOfPieces = $RequestBody['NoOfPieces'];
        $PlantID = $RequestBody['PlantID'];
        $PostedUserID=$RequestBody['UserID'];
        $ArrivalID=$RequestBody['ArrivalID'];
        $SuperVisorId=$RequestBody['drpSupervisor'];

        $sqlQuery = "SELECT PlantID FROM  `PPCPlantData` as PCD  WHERE LotID='$LotID' AND ArrivalID='$ArrivalID'";  

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();
        $PlantIDRow = $stmtSelect->fetch(PDO::FETCH_ASSOC);
        $PlantID=$PlantIDRow['PlantID'];


        date_default_timezone_set("Asia/Kolkata"); // Set to your timezone
 $PostedDateTime=date("Y-m-d H:i:s"); 
        
 if (empty($this->Msg)) {
    $this->Msg = "Success";  // Default message if not set yet
}

if($Variety!='' && $NoOfPieces!=''){

   $sqlSelect = "SELECT CrateDataID FROM `PpcPlantCheckData` WHERE LotID='$LotID' and VarietyDefectID='$Variety' ";  

        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();
        $rowCount = $stmtSelect->rowCount(); 
        $dataCrateDataID = $stmtSelect->fetch(PDO::FETCH_ASSOC);
        $QueryCrateDataID=$dataCrateDataID['CrateDataID'];

        if ($rowCount == 0) {
           
            $sqlQuery = "INSERT INTO `PpcPlantCheckData`( `LotID`, `ArrivalID`, `PlantID`, `VarietyDefectID`, `NoOfPieces`, `PostedUserID`, `PostedDateTime`,SuperVisorId) 
            VALUES('$LotID','$ArrivalID','$PlantID','$Variety','$NoOfPieces','$PostedUserID','$PostedDateTime', '$SuperVisorId')"; 
             $stmt = $this->conn->prepare($sqlQuery);
             $stmt->execute();
             if ($stmt) {
                $lastInsertId = $this->conn->lastInsertId();
                $createdPlantCheck = $this->auditLogger->fetchRowById('PpcPlantCheckData', 'CrateDataID', $lastInsertId);
                $this->auditLogger->logInsert('PpcPlantCheckData', $lastInsertId, $createdPlantCheck, $RequestBody, [
                    'ReferenceNo' => $LotID,
                    'Remarks' => 'PreProcessing plant check created',
                ]);
             }
            

        } 
        else{
            $this->Msg = "Variety Already exists";            

        $Data['Error'] = null;
        $Data['Msg'] = $this->Msg ;
        return $Data;  
    }
    if ($stmt) {
        $this->Msg = "Plant check data posted successfully";

        $Data['Error'] = null;
        $Data['Msg'] = $this->Msg ;
        return $Data;
    } 
    else {
        return $Data = array('Error' => $this->Error, "Msg" => $this->Error);
    }

    }

      else{
        $this->Msg = "Please enter Variety and NoOfPieces";

            $Data['Error'] = null;
            $Data['Msg'] = $this->Msg;
            return $Data;  
        }
       

    }


    public function postLotWiseCrateDetails($RequestBody)
    {

        $CrateNo = $RequestBody['CrateNo'];
        $Weight = $RequestBody['Weight'];
        $LotID = $RequestBody['LotID'];
        $ArrivalID=$RequestBody['ArrivalID'];
        $PlantID = $RequestBody['PlantID'];
        $ActualCrateWeight = $RequestBody['ActualCrateWeight'];


         $sqlQuery = "SELECT PlantID FROM  `PPCPlantData` as PCD  WHERE LotID='$LotID' AND ArrivalID='$ArrivalID'";  

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();
        $PlantIDRow = $stmtSelect->fetch(PDO::FETCH_ASSOC);
        $PlantID=$PlantIDRow['PlantID'];

        $PostedUserID=$RequestBody['UserID'];
        date_default_timezone_set("Asia/Kolkata"); // Set to your timezone

        $PostedDateTime=date("Y-m-d H:i:s"); 

     
        if ($CrateNo != '' && $Weight!='') {

            $sqlInsert = "INSERT INTO `PpcCratesData`( `LotID`,`ArrivalID`,`PlantID`, `CrateNo`, `Weight`,`ActualCrateWeight`, `PostedUserID`, `PostedDateTime`) 
            VALUES('$LotID','$ArrivalID','$PlantID','$CrateNo','$Weight','$ActualCrateWeight','$PostedUserID','$PostedDateTime')"; 

            $stmtInsert = $this->conn->prepare($sqlInsert);
            $stmtInsert->execute();

            if ($stmtInsert) {
                $lastInsertId = $this->conn->lastInsertId();
                $createdCrate = $this->auditLogger->fetchRowById('PpcCratesData', 'CrateDataBreakUpID', $lastInsertId);
                $this->auditLogger->logInsert('PpcCratesData', $lastInsertId, $createdCrate, $RequestBody, [
                    'ReferenceNo' => $LotID,
                    'Remarks' => 'PreProcessing crate created',
                ]);
                $Data['Error'] = null;
                $Data['Msg'] = ' Crate data  posted successfully ';

                return $Data;
            } else {
                return $Data = array('Error' => $this->Error, "Msg" => $this->Error);
            }
        }
        else{
            $Data['Error'] = null;
                $Data['Msg'] = ' Please enter CrateNo and Weight ';

                return $Data; 
        }

    }

     public function postPlantDetails($RequestBody)
    {

        $LotID = $RequestBody['LotID'];
        $ArrivalID=$RequestBody['ArrivalID'];
        $PlantID = $RequestBody['PlantID'];




        $PostedUserID=$RequestBody['UserID'];
        date_default_timezone_set("Asia/Kolkata"); // Set to your timezone

        $PostedDateTime=date("Y-m-d H:i:s"); 

     
        if ($LotID != '' && $ArrivalID!='') {
            $sqlSelect = "SELECT PpcPlantID FROM `PPCPlantData` WHERE LotID='$LotID' and ArrivalID='$ArrivalID' ";  

        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();
        $dataCrateDataID = $stmtSelect->fetch(PDO::FETCH_ASSOC);
        $QueryCrateDataID=$dataCrateDataID['CrateDataID'];
        $existingPlant = null;
        if($QueryCrateDataID != '' && $QueryCrateDataID != 0){
            $existingPlant = $this->auditLogger->fetchRowById('PPCPlantData', 'PpcPlantID', $QueryCrateDataID);
        }

        if($QueryCrateDataID != '' && $QueryCrateDataID != 0){
            $sqlInsert = "UPDATE  `PPCPlantData` SET `PlantID` = '$PlantID' WHERE PpcPlantID=$QueryCrateDataID";
        }else{

            $sqlInsert = "INSERT INTO `PPCPlantData`( `LotID`, `ArrivalID`, `PlantID`, `PostedUserID`, `PostedDateTime`) 
            VALUES('$LotID','$ArrivalID','$PlantID','$PostedUserID','$PostedDateTime')";

        }
            $stmtInsert = $this->conn->prepare($sqlInsert);
            $stmtInsert->execute();

            if ($stmtInsert) {
                if($QueryCrateDataID != '' && $QueryCrateDataID != 0){
                    $updatedPlant = $this->auditLogger->fetchRowById('PPCPlantData', 'PpcPlantID', $QueryCrateDataID);
                    $this->auditLogger->logUpdate('PPCPlantData', $QueryCrateDataID, $existingPlant, $updatedPlant, $RequestBody, [
                        'ReferenceNo' => $LotID,
                        'Remarks' => 'PreProcessing plant selection updated',
                    ]);
                } else {
                    $lastInsertId = $this->conn->lastInsertId();
                    $createdPlant = $this->auditLogger->fetchRowById('PPCPlantData', 'PpcPlantID', $lastInsertId);
                    $this->auditLogger->logInsert('PPCPlantData', $lastInsertId, $createdPlant, $RequestBody, [
                        'ReferenceNo' => $LotID,
                        'Remarks' => 'PreProcessing plant selection created',
                    ]);
                }
                $Data['Error'] = null;
                $Data['Msg'] = ' Plant data  posted successfully ';

                return $Data;
            } else {
                return $Data = array('Error' => $this->Error, "Msg" => $this->Error);
            }
        }
        else{
            $Data['Error'] = null;
                $Data['Msg'] = ' Please Select Plant ';

                return $Data; 
        }

    }


    public function deleteBreakUpData($RequestBody)
    {
        $CrateDataID = $RequestBody['CrateDataID'];
        $SaveType=$RequestBody['SaveType'];
        $LotID = $RequestBody['LotID'];


        if($SaveType=='PlantCheck'){

            $sqlPlantCheckDelete = "DELETE FROM `PpcPlantCheckData` WHERE CrateDataID='$CrateDataID'"; //delete crate data
            $stmtDelete = $this->conn->prepare($sqlPlantCheckDelete);
            $stmtDelete->execute();
            
                }
        else if($SaveType=='Crate'){
        
            $sqlCrateDelete = "DELETE FROM PpcCratesData WHERE CrateDataBreakUpID='$CrateDataID'";
            $stmtDelete = $this->conn->prepare($sqlCrateDelete);
            $stmtDelete->execute();
        
        }      
        
        if($stmtDelete){
               
            $receivalData = $this->getCrateData($RequestBody);

            $responseData['Error'] = null;
            $responseData['Msg'] = 'Breakup Deleted  Successfully';

            $responseData['Data'] = $receivalData['Data'];
    
            return $responseData;
        }
        
    }

   
}
