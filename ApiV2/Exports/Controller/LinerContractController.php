<?php

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class LinerContractController
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
        $this->auditLogger = new CentralAuditLogger($this->conn, 'Exports');
    }

    function getLinerContractData($RequestBody)
    {        
        $Liner = $RequestBody['Liner'];
        $ContractNumber = $RequestBody['ContractNumber'];
        $Value= $RequestBody['Value'];
        $Period = $RequestBody['Period'];
        $month = $RequestBody['Month'];
        $StartDate = $RequestBody['StartDate'];
        $EndDate = $RequestBody['EndDate'];
        $PageNo = $RequestBody['PageNo'];

        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = "LIMIT $RecordsPerPage OFFSET $LimitMax";

    
        $sqlFields = "Liner,ContractNumber,Value,period,Month,StartDate,EndDate,SPV.LinerName,CountryId,SCL.name AS CountryName ";


        $sql = "SELECT *,SCL.name AS CountryName  FROM LinerContractDetails AS LCD
        LEFT JOIN  ScrmLinerMaster AS SLM ON SLM.LinerID=LCD.Liner
        LEFT JOIN ScrmCountriesList SCL ON SCL.id=LCD.CountryId";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        // $dataCount = $stmt->fetch(PDO::FETCH_ASSOC);
        // $TotalCount = $dataCount['TotalCount'];
        // $PageCount = ceil($TotalCount / $RecordsPerPage);

        $i = 0;
        $LinerContractData = [];
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            // echo print_r($dataRow);
            
            $LinerContractList['SNO'] = ++$i;
            $LinerContractList['LinerContractID'] = $dataRow['LinerContractID'];
            $LinerContractList['LinerName'] = $dataRow['LinerName'];
            $LinerContractList['Liner'] = $dataRow['Liner'];
            $LinerContractList['ContractNumber'] = $dataRow['ContractNumber'];
            $LinerContractList['Value'] = $dataRow['Value'];
            $LinerContractList['Period'] = $dataRow['Period'];
            //  $LinerContractList['Month'] = $dataRow['Month'];
            $monthNumber = $dataRow['Month'];
            $monthName = date('F', mktime(0, 0, 0, $monthNumber, 10));
            $LinerContractList['Month'] = $monthNumber; 
            $LinerContractList['MonthName'] = $monthName;
            $LinerContractList['StartDate'] = $dataRow['StartDate'];
            $LinerContractList['EndDate'] = $dataRow['EndDate'];
            $LinerContractList['CountryId'] = $dataRow['CountryId'];
            $LinerContractList['CountryName'] = $dataRow['CountryName'];
            $LinerContractData[] = $LinerContractList;
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['LinerContractData'] = $LinerContractData;

        return $data;
    }
    public function postLinerContractData($RequestBody)
    {
        if(!empty($RequestBody['MethodType']) && $RequestBody['MethodType'] == 'getLinerContractNos'){
            $results = $this->GetLinerContractNoByLinerAndPort($RequestBody);
            $data['Error'] = null;
            $data['Msg'] = 'Success';
            $data['LinerContractData'] = $results;
            return $data;
        }
        // echo print_r($RequestBody);
        // exit;
        $LinerContractID = $RequestBody['LinerContractID'];
        $Liner = $RequestBody['Liner'];
        $ContractNumber = $RequestBody['ContractNumber'];
        $Value = $RequestBody['Value'];
        $Period = $RequestBody['Period'];
        $Month = $RequestBody['Month'];
        $StartDate = $RequestBody['StartDate'];
        $EndDate = $RequestBody['EndDate'];
        $CountryId = $RequestBody['CountryId'];

        if ($LinerContractID != '') {
            $existingContract = $this->auditLogger->fetchRowById('LinerContractDetails', 'LinerContractID', $LinerContractID);
            $sqlQuery = "Update `LinerContractDetails` set
                `Liner`='$Liner',
                `ContractNumber`='$ContractNumber',
                 `Value`='$Value',
                 `Period`='$Period',
                `Month`='$Month',
                 `StartDate`='$StartDate',
                 `EndDate`='$EndDate',
                 `CountryId`='$CountryId'
                 where 	LinerContractID='$LinerContractID'";
    
            $stmt = $this->conn->prepare($sqlQuery);
            $stmt->execute();
            if ($stmt) {
                $updatedContract = $this->auditLogger->fetchRowById('LinerContractDetails', 'LinerContractID', $LinerContractID);
                $this->auditLogger->logUpdate('LinerContractDetails', $LinerContractID, $existingContract, $updatedContract, $RequestBody, [
                    'ReferenceNo' => $ContractNumber,
                    'Remarks' => 'Export liner contract updated',
                ]);
                $Data['Error'] = "";
                $Data['Msg'] = "Data updated Success";
                $Data['Data'] = $LinerContractID;
             }
        }
        else{
            $sqlQuery = "INSERT INTO `LinerContractDetails` 
            ( `Liner`,  `ContractNumber`, `Value`, `Period`, `Month`, 
            `StartDate`, `EndDate`,`CountryId`) 
            VALUES ('$Liner','$ContractNumber','$Value','$Period','$Month',
            '$StartDate','$EndDate','$CountryId')";
    
         $stmt = $this->conn->prepare($sqlQuery);
         $stmt->execute();
         $lastInsertId = $this->conn->lastInsertId(); 
         
         if ($stmt) {
            $createdContract = $this->auditLogger->fetchRowById('LinerContractDetails', 'LinerContractID', $lastInsertId);
            $this->auditLogger->logInsert('LinerContractDetails', $lastInsertId, $createdContract, $RequestBody, [
                'ReferenceNo' => $ContractNumber,
                'Remarks' => 'Export liner contract created',
            ]);
            $Data['Error'] = "";
            $Data['Msg'] = "Data saved Success";
            $Data['Data'] = $lastInsertId;
         }
        }
        
    
    }
    public function GetLinerContractNoByLinerAndPort($RequestBody){
        $from_date = $RequestBody['from_date'];
        $to_date = $RequestBody['to_date'];
        $liner_id = $RequestBody['liner_id'];
        $Country = $RequestBody['Country'];
        if(!empty($from_date ) && !empty($to_date ) && !empty($liner_id ) && !empty($Country )){
                $sql = "SELECT `LinerContractID`,`ContractNumber`
                        FROM LinerContractDetails 
                        WHERE `CountryId`=$Country AND `Liner`=$liner_id 
                        AND `StartDate`<='$from_date' AND `EndDate`>='$to_date'";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute();
                while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $data[] = $dataRow;
                }
                return $data;
        }
         return false;
    }
}




