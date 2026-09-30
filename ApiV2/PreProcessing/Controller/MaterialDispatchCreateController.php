<?php 
include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';


class MaterialDispatchCreateController
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

    function getMaterialDispatch($RequestBody)
    {
        $Data=[];
        $DataType=$RequestBody['DataType'];
       
        if($DataType=="getArrivals"){
            $Data['ArrivalsList'] =$this->getArrivals($RequestBody);
         }

         else if($DataType=="getDispatchMaterialList"){            

            $Data['DispatchMaterialList'] =$this->getDispatchMaterialDetails($RequestBody);
            
         }

        if (empty($this->Msg)) {
            // Only set Msg if it's not already set
            $this->Msg = "Success";  // Default message
        }
       

        $responseData['Error'] = $this->Error;
        $responseData['Msg'] = $this->Msg;

        $responseData['Data'] = $Data;

        return $responseData;
    }
  
    function getDispatchMaterialDetails($RequestBody)
    {

        $FromStation = $RequestBody['FromStation'];
        $ToStation = $RequestBody['ToStation'];
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $DispatchMaterialID = $RequestBody['DispatchMaterialID'];     
        $Query="";

        if($DispatchMaterialID==''){
            $DateFilter=" TransferredDate Between '$FromDate' and '$ToDate'";

            if($ToStation!=''){
                $Query .= " and ToStationID='$ToStation'";
            }
            if($FromStation!=''){
               $Query .=" and  FromStationID='$FromStation' "; 
            }

            
        }
        else{
$Query .=" MaterialDispatchID ='$DispatchMaterialID'";
        }
             

        $sqlFields = " PVSF.UnitName as FromStation,PVST.UnitName as ToStation,TransferredDate,DATE_FORMAT(PGMD.TransferredDate, '%d-%m-%Y') AS FormattedTransferDate, 
        PGMD.Grade, PHVL.ShortName as Variety,Quantity,DispatchedCrates,ReceivedCrates,PGMD.Status,PGMD.VarietyID,FromStationID,ToStationID,
        MaterialDispatchID,PGMD.ArrivalID,PGMD.PlantID,PGMD.ArrivalDate,RAD.ArrivalNumber ";  

        $sqlQuery = "SELECT $sqlFields
FROM `PpcGradingMaterialDispatchData` PGMD
left join PpcVAStations PVSF on PVSF.StationID=PGMD.FromStationID
left join PpcVAStations PVST on PVST.StationID=PGMD.ToStationID
left join PpcHLVAVarietyList PHVL on PHVL.VarietyID=PGMD.VarietyID
left join RmArrivalDetatils as RAD on RAD.RmArrivalID=PGMD.ArrivalID
WHERE  $DateFilter $Query  ";  

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();
       

        $i = 0;
        $ArrayMaterialDispatchData=[];
        
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
            $EditAndDeleteAccess=1;
            $StatusName="Pending";
            $StatusColour="red";


            $Status=$dataRow['Status'];

            if($Status==1){
$EditAndDeleteAccess=0;
$StatusName="Received";
$StatusColour="green";
            }
          
            $MaterialDispatchData['SNo'] = ++$i;
            $MaterialDispatchData['MaterialDispatchID'] = $dataRow['MaterialDispatchID']; 
            $MaterialDispatchData['ArrivalID'] = $dataRow['ArrivalID']; 
            $MaterialDispatchData['BatchNumber'] = $dataRow['ArrivalNumber'];          
            $MaterialDispatchData['FromStationName'] = $dataRow['FromStation']; 
            $MaterialDispatchData['FromStationID'] = $dataRow['FromStationID']; 
            $MaterialDispatchData['ToStationName'] = $dataRow['ToStation']; 
            $MaterialDispatchData['ToStationID'] = $dataRow['ToStationID']; 
            $MaterialDispatchData['TransferredDate'] = $dataRow['TransferredDate']; 
            $MaterialDispatchData['FormattedTransferDate'] = $dataRow['FormattedTransferDate']; 
            $MaterialDispatchData['Grade'] = $dataRow['Grade']; 
            $MaterialDispatchData['VarietyName'] = $dataRow['Variety']; 
            $MaterialDispatchData['VarietyID'] = $dataRow['VarietyID']; 
            $MaterialDispatchData['Quantity'] = $dataRow['Quantity']; 
            $MaterialDispatchData['ReceivedCrates'] = $dataRow['ReceivedCrates']; 
            $MaterialDispatchData['DispatchedCrates'] = $dataRow['DispatchedCrates']; 
            $MaterialDispatchData['PlantID'] = $dataRow['PlantID'];  
            $MaterialDispatchData['ArrivalDate'] = $dataRow['ArrivalDate'];                         
            $MaterialDispatchData['Status'] = $Status; 
            $MaterialDispatchData['StatusName'] = $StatusName; 
            $MaterialDispatchData['StatusColour'] = $StatusColour; 

            
            $MaterialDispatchData['EditAndDeleteAccess'] = $EditAndDeleteAccess;             
            $MaterialDispatchData['Difference'] = $dataRow['ReceivedCrates']-$dataRow['DispatchedCrates']; 

            $ArrayMaterialDispatchData[]=$MaterialDispatchData;

              }
              if (empty($ArrayMaterialDispatchData)) {
                if (empty($this->Msg)) {
                $this->Msg = "No data found."; 
                }// Set the message when no data is found
            } else {
                if (empty($this->Msg)) {
                $this->Msg = "Success"; // Set success message
                }
            }
        
        return $ArrayMaterialDispatchData;
    }


    public function postMaterialDispatch($RequestBody)
    {
         $Token = $this->getToken(); 
         $RequestBody['UserID']=$Token['UserID']; 

        $ArrivalID = $RequestBody['ArrivalID'];
        $FromStationID = $RequestBody['FromStationID'];
        $ToStationID = $RequestBody['ToStationID'];
        $TransferredDate = $RequestBody['TransferredDate'];       
        $Grade=$RequestBody['Grade'];
        $VarietyID=$RequestBody['VarietyID'];
        $Quantity=$RequestBody['Quantity'];
        $DispatchedCrates=$RequestBody['DispatchedCrates'];
        $ReceivedCrates=$RequestBody['ReceivedCrates'];
        $Status=$RequestBody['Status'];        
        $MaterialDispatchID=$RequestBody['MaterialDispatchID']; 
        $PlantID=$RequestBody['PlantID']; 
        $ArrivalDate=$RequestBody['ArrivalDate']; 


        
              

        $PostedUserID=$RequestBody['UserID'];
        date_default_timezone_set("Asia/Kolkata"); // Set to your timezone
 $PostedDateTime=date("Y-m-d H:i:s"); 
        $existingDispatch = null;
        if ($MaterialDispatchID != '') {
            $existingDispatch = $this->auditLogger->fetchRowById('PpcGradingMaterialDispatchData', 'MaterialDispatchID', $MaterialDispatchID);
        }
        
 if (empty($this->Msg)) {
    $this->Msg = "Success";  // Default message if not set yet
}

        if ($MaterialDispatchID == '') {
           
            $sqlQuery = "INSERT INTO `PpcGradingMaterialDispatchData`( `PlantID`, `ArrivalDate`,`ArrivalID`, `FromStationID`, `ToStationID`, `TransferredDate`, `Grade`, `VarietyID`, 
            `Quantity`, `DispatchedCrates`, `Status`, `PostedDateTime`, `PostedUserID`) 
            VALUES('$PlantID','$ArrivalDate','$ArrivalID','$FromStationID','$ToStationID','$TransferredDate','$Grade','$VarietyID','$Quantity', '$DispatchedCrates','$Status',
            '$PostedDateTime','$PostedUserID')"; 
             $stmt = $this->conn->prepare($sqlQuery);
             $stmt->execute();            
        } 
      
        else{

            $sqlQuery = "UPDATE `PpcGradingMaterialDispatchData` SET `PlantID`='$PlantID',`ArrivalDate`='$ArrivalDate',`ArrivalID`='$ArrivalID',`FromStationID`='$FromStationID',`ToStationID`='$ToStationID',`TransferredDate`='$TransferredDate',
            `Grade`='$Grade',`VarietyID`='$VarietyID',`Quantity`='$Quantity', `DispatchedCrates`='$DispatchedCrates',`ReceivedCrates`='$ReceivedCrates',`Status`='$Status',
            `PostedDateTime`='$PostedDateTime',`PostedUserID`='$PostedUserID' WHERE `MaterialDispatchID`='$MaterialDispatchID'"; 
             $stmt = $this->conn->prepare($sqlQuery);
             $stmt->execute();

             }
    if ($stmt) {
        if ($MaterialDispatchID == '') {
            $lastInsertId = $this->conn->lastInsertId();
            $createdDispatch = $this->auditLogger->fetchRowById('PpcGradingMaterialDispatchData', 'MaterialDispatchID', $lastInsertId);
            $this->auditLogger->logInsert('PpcGradingMaterialDispatchData', $lastInsertId, $createdDispatch, $RequestBody, [
                'ReferenceNo' => $ArrivalID,
                'Remarks' => 'PreProcessing material dispatch created',
            ]);
        } else {
            $updatedDispatch = $this->auditLogger->fetchRowById('PpcGradingMaterialDispatchData', 'MaterialDispatchID', $MaterialDispatchID);
            $this->auditLogger->logUpdate('PpcGradingMaterialDispatchData', $MaterialDispatchID, $existingDispatch, $updatedDispatch, $RequestBody, [
                'ReferenceNo' => $MaterialDispatchID,
                'Remarks' => 'PreProcessing material dispatch updated',
            ]);
        }
        $this->Msg = "Data posted successfully";

        $Data['Error'] = null;
        $Data['Msg'] = $this->Msg ;
        return $Data;
    } 
    else {
        return $Data = array('Error' => $this->Error, "Msg" => $this->Error);
    }
        
        $DispatchMaterialData = $this->getDispatchMaterialDetails($RequestBody);
        $Data['Error'] = null;
        $Data['Msg'] = $this->Msg ;
        $Data['Data'] = $DispatchMaterialData['Data'];
        return $Data; 
  

} 

    public function deleteMaterialDispatch($RequestBody)
    {
        $MaterialDispatchID = $RequestBody['MaterialDispatchID'];        
        $existingDispatch = $this->auditLogger->fetchRowById('PpcGradingMaterialDispatchData', 'MaterialDispatchID', $MaterialDispatchID);

            $sqlMaterialDispatchData = "DELETE FROM `PpcGradingMaterialDispatchData` WHERE MaterialDispatchID='$MaterialDispatchID'"; //delete crate data
            $stmtDelete = $this->conn->prepare($sqlMaterialDispatchData);
            $stmtDelete->execute();         
     
        
        if($stmtDelete){
            $this->auditLogger->logDelete('PpcGradingMaterialDispatchData', $MaterialDispatchID, $existingDispatch, $RequestBody, [
                'ReferenceNo' => $MaterialDispatchID,
                'Remarks' => 'PreProcessing material dispatch deleted',
            ]);
               
            // $DispatchMaterialData = $this->getDispatchMaterialDetails($RequestBody);

            $responseData['Error'] = null;
            $responseData['Msg'] = 'Deleted  Successfully';

            // $responseData['Data'] = $DispatchMaterialData['Data'];
    
            return $responseData;
        }
        
    }

    function getArrivals($RequestBody){
        $ArrivalDate = $RequestBody['ArrivalDate'];
        $DataType=$RequestBody['DataType'];
        $PlantID = $RequestBody['PlantID'];
    
       $SqlArrivalsList="SELECT RmArrivalID,ArrivalNumber  FROM `RmArrivalDetatils`  WHERE ProcessingStationID='$PlantID' and ArrivalDate='$ArrivalDate'";
       $stmtArrivalsList = $this->conn->prepare($SqlArrivalsList);
       $stmtArrivalsList->execute();
    
       while ($dataRowArrivalsList = $stmtArrivalsList->fetch(PDO::FETCH_ASSOC)) {
    $ArrayArrivalList[]=$dataRowArrivalsList;
    
    }
    return $ArrayArrivalList;
    
    }
   
}
