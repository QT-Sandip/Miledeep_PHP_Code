<?php
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);*/
include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class HLVABillDetailsController
{
    private $conn;
    private $auditLogger;
    private $db_table = "ScrmApplicants";
    private $utilController;
    private $Error = null;
    private $Msg = '';


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
        $this->auditLogger = new CentralAuditLogger($this->conn, 'PreProcessing');
    }

    public function getHLVABillDetails($RequestBody)
    {
        $BillID = $RequestBody['BillID'];
        //$BreakUpData = $this->getArrivalBreakUp($ArrivalID);
        $BillData = $this->getHLVABillData($BillID,$RequestBody['RstrictNoOfPersons']);
        // $GrandTotal = $this->getGrandTotal($ArrivalID);
        // $overAllData = $this->getOverAllArrival($ArrivalID);


        // $ArrivalData['OverAllWeight'] = $overAllData['OverAllWeight'];
        // $ArrivalData['OverAllAmount'] = $overAllData['OverAllAmount'];
        // $ArrivalData['OverAllBalWeight'] = $overAllData['OverAllBalWeight'];
        // $ArrivalData['OverAllBalAmount'] = $overAllData['OverAllBalAmount'];

        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        //$Data['BreakUpData'] = $BreakUpData;
        $Data['Data'] = $BillData;
        //$Data['GrandTotal'] = $GrandTotal;
        return $Data;
    }

    public function postDeleteHLVABIllBrkUP($RequestBody)
    {
        $BillID = $RequestBody['BillID'];
        $BreakUpID = $RequestBody['BreakUpID'];
        $Type = $RequestBody['Type'];

        $SqlBDetail = "SELECT Type from PpcDailyBatchDetails where ID='$BillID'";
        $StmtBDetail = $this->conn->prepare($SqlBDetail);
        $StmtBDetail->execute();
        $RowBDetail = $StmtBDetail->fetch(PDO::FETCH_ASSOC);
        $Type=$RowBDetail['Type'];

        if ($Type == 1) {
            $existingBreakup = $this->auditLogger->fetchRowById('PpcDailyBatchBreakUpDeatils', 'BreakupID', $BreakUpID);
            $sqlQuery = "delete  from `PpcDailyBatchBreakUpDeatils` where  BreakupID='$BreakUpID'";
        } else if ($Type == 2) {
            $existingBreakup = $this->auditLogger->fetchRowById('PpcDailyBatchPeelingBreakUpDeatils', 'BreakupID', $BreakUpID);
            $sqlQuery = "delete  from `PpcDailyBatchPeelingBreakUpDeatils` where  BreakupID='$BreakUpID'";

        }
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();


if($stmt){

        if ($Type == 1) {
            $this->auditLogger->logDelete('PpcDailyBatchBreakUpDeatils', $BreakUpID, $existingBreakup, $RequestBody, [
                'ReferenceNo' => $BillID,
                'Remarks' => 'HLVA bill breakup deleted',
            ]);
        } else if ($Type == 2) {
            $this->auditLogger->logDelete('PpcDailyBatchPeelingBreakUpDeatils', $BreakUpID, $existingBreakup, $RequestBody, [
                'ReferenceNo' => $BillID,
                'Remarks' => 'HLVA peeling bill breakup deleted',
            ]);
        }

        $BillData = $this->getHLVABillData($BillID,$RequestBody['RstrictNoOfPersons']);

        $Data['Error'] = null;
        $Data['Msg'] = 'Deleted  Successful';

        $Data['Data'] = $BillData;
}else{
    $Data['Error'] = 'Unable To Delete  ';;
}



        return $Data;
    }

    public function postHLVABillDetails($RequestBody)
    {

       // echo '<pre>'; print_r($RequestBody);die;
        if(!empty($RequestBody['methodType']) && $RequestBody['methodType'] == 'GetWorkersBySubContractorID'){
            $batchData = $this->GetWorkersBySubContractorID($RequestBody['batchId']);
            if(!empty($batchData)){
                $Data['Error'] = null;
                $Data['Msg'] = 'Success';
                $Data['Data'] = $batchData;
            }else{
                $Data['Error'] = 'Error';
                $Data['Msg'] = null;
                $Data['Data'] = $batchData;
            }
            return $Data;
        }
        if(!empty($RequestBody['methodType']) && $RequestBody['methodType'] == 'GetWorkersByContractorID'){
            $workerData = $this->GetWorkersByContractorID($RequestBody['ContractorID']);
            if(!empty($workerData)){
                $Data['Error'] = null;
                $Data['Msg'] = 'Success';
                $Data['Data'] = $workerData;
            }else{
                $Data['Error'] = 'Error';
                $Data['Msg'] = null;
                $Data['Data'] = $workerData;
            }
            return $Data;
        }
        $PostedUserID = 1;
         $BillID = $RequestBody['BillID'];
       // $ArrivalID = $RequestBody['ArrivalID'];

        $PlantID = $RequestBody['Plant'];
        $StationID = $RequestBody['Center'];
        $Date = $RequestBody['TransactionDate'];
        $Type = $RequestBody['Type'];
        $BatchID = $RequestBody['BatchID'];
        $Persons = $RequestBody['Persons'];
        $Shift = $RequestBody['Shift'];
        $Adjustments = $RequestBody['Adjustments'];
        $Extras = $RequestBody['Extras'];
        $Remarks = $RequestBody['Remarks'];
        $Status = 0;
        // $VarietyID = $RequestBody['VarietyID'];
        // $HLCount = $RequestBody['HLCount'];
        // $HLQuantity = $RequestBody['HLWeight'];


        $AryCurrentDate = getCurrentDateTime();

        $PostedDateTime = $AryCurrentDate['TimeStamp'];
        $Year = $AryCurrentDate['Year'];

        $stmt = false;
        $insertFields = $insertValues = $updateFields = '';
        if(!empty($RequestBody['RstrictNoOfPersons'])){
           $insertFields = ",`WorkersIds`";
            $WorkersIds = !empty($RequestBody['WorkersIds'])?$RequestBody['WorkersIds']:'';
            $insertValues = ",'$WorkersIds'";
            $updateFields = ",`WorkersIds`=$WorkersIds";
        }

        if ($BillID == '') {
         $sqlQuery = "INSERT INTO `PpcDailyBatchDetails`(`BatchID`, `Persons`, `Adjustments`, `Extras`, `Date`,
            `StationID`, `PlantID`, `Remarks`, `ShiftID`, `Type`, `PostedUserId`, `PostedDateTime`, `Status`)
            values('$BatchID','$Persons','$Adjustments','$Extras','$Date','$StationID','$PlantID','$Remarks','$Shift','$Type','$PostedUserID','$PostedDateTime','$Status')";
            $stmt = $this->conn->prepare($sqlQuery);
            $stmt->execute();
            $BillID = $this->conn->lastInsertId();

            $newBatch = $this->auditLogger->fetchRowById('PpcDailyBatchDetails', 'ID', $BillID);
            $this->auditLogger->logInsert('PpcDailyBatchDetails', $BillID, $newBatch, $RequestBody, [
                'ReferenceNo' => $BillID,
                'Remarks' => 'HLVA daily batch details created',
            ]);
        }else{
            $existingBatch = $this->auditLogger->fetchRowById('PpcDailyBatchDetails', 'ID', $BillID);
            $sqlQuery = "update  `PpcDailyBatchDetails` set
            `BatchID`='$BatchID',
            `Persons`='$Persons',
            `Adjustments`='$Adjustments',
            `Extras`='$Extras',
            `Date`='$Date',
            `StationID`='$StationID',
             `PlantID`='$PlantID',
             `Remarks`='$Remarks',
             `ShiftID`='$Shift',
             `Type`='$Type',
              `PostedUserId`='$PostedUserID',
               `PostedDateTime`='$PostedDateTime',
               `Status`='1'
               where ID='$BillID'";

            $stmt = $this->conn->prepare($sqlQuery);
            $stmt->execute();

            $updatedBatch = $this->auditLogger->fetchRowById('PpcDailyBatchDetails', 'ID', $BillID);
            $this->auditLogger->logUpdate('PpcDailyBatchDetails', $BillID, $existingBatch, $updatedBatch, $RequestBody, [
                'ReferenceNo' => $BillID,
                'Remarks' => 'HLVA daily batch details updated',
            ]);
        }

            if ($Type == 1) {
              $Data['Msg'] =  $this->postHLBillBreakUp($BillID, $RequestBody);
            } else if ($Type == 2) {
                $Data['Msg'] =$this->postPeelingBillBreakUp($BillID, $RequestBody);
            }
            $RequestBody['BillID'] = $BillID;
           // $Data['Data']=$this->getHLVABillDetails($RequestBody);
            $Data['Data']=$this->getHLVABillData($BillID, $RequestBody['RstrictNoOfPersons']);
            //return $this->getHLVABillDetails($RequestBody);
            return $Data;



        // } else {
        //     return $Data = array('Error' => $this->Error, "Msg" => $this->Error);
        // }
    }

    function postHLBillBreakUp($BillID, $RequestBody)
    {
            $HLQty = trim($RequestBody['HLWeight']);
        $ArrivalID = $RequestBody['ArrivalID'];
        //$BatchID = $RequestBody['BatchID'];
        $BatchID = $RequestBody['ContractorID'];


        $PostedUserId = 1;

        $AryCurrentDate = getCurrentDateTime();

        $PostedDateTime = $AryCurrentDate['TimeStamp'];


$SqlWeight = "SELECT
(SELECT sum(PurchaseWeight) FROM `RmLotDetatils` WHERE RmLotID='$ArrivalID') as Arrivalweight,
(SELECT sum(ProductionWeight) FROM `RmLotDetatils` WHERE RmLotID='$ArrivalID') as ProductionWeight,
(SELECT sum(HLQty) FROM PpcDailyBatchBreakUpDeatils WHERE LotID='$ArrivalID')as BreakupQty";
        $StmtWeight = $this->conn->prepare($SqlWeight);
        $StmtWeight->execute();
        $RowWeight = $StmtWeight->fetch(PDO::FETCH_ASSOC);
        $PurchaseWeight = $RowWeight['Arrivalweight'];
        $ProductionWeight = $RowWeight['ProductionWeight'];
        $BreakupQty = $RowWeight['BreakupQty'] + $HLQty;
if($ProductionWeight >0){

$Weight=$ProductionWeight;
}else{
$Weight=$PurchaseWeight;

}


        if ($BreakupQty <= $Weight && $HLQty >0) {

            $insertFields = $insertValues = $updateFields = '';
            if(!empty($RequestBody['RstrictNoOfPersons'])){
                $insertFields = ",`WorkersIds`,Count";
                $WorkersIds = !empty($RequestBody['WorkersIds'])?$RequestBody['WorkersIds']:'';
                $HLCount = !empty($RequestBody['HLCount'])?$RequestBody['HLCount']:'';
                $insertValues = ",'$WorkersIds','$HLCount'";
                $updateFields = ",`WorkersIds`=$WorkersIds,Count='$HLCount'";
            }

                 $SqlUpdate = "INSERT INTO `PpcDailyBatchBreakUpDeatils`(`DailyBatchID`, `LotID`, `HLQty`, `PostedUserId`, `PostedDateTime`, `BatchId`$insertFields)
            VALUES ('$BillID','$ArrivalID','$HLQty','$PostedUserId','$PostedDateTime','$BatchID' $insertValues)";
            $StmtUpdate = $this->conn->prepare($SqlUpdate);
            $StmtUpdate->execute();

         if($StmtUpdate){
             $Data= 'Breakup posted successfully';
         }else{
            $Data= 'Unable To Post Data successfully';
         }

        } else {
           // $Data['Error'] = null;
            $Data = 'Weight exceeds';
           // print_r($Data); exit;

        }
        return $Data;

    }
    function postPeelingBillBreakUp($BillID, $RequestBody)
    {
        $HLQty = $RequestBody['HLWeight'];
        $ArrivalID = $RequestBody['ArrivalID'];
        //$BatchID = $RequestBody['BatchID'];
        $BatchID = $RequestBody['ContractorID'];
        $Count = $RequestBody['HLCount'];
        $VarietyID = $RequestBody['VarietyID'];


        $PostedUserId = 1;

        $AryCurrentDate = getCurrentDateTime();

        $PostedDateTime = $AryCurrentDate['TimeStamp'];

        $SqlWeight = "SELECT sum(HLWeight) as HLWeight,(SELECT sum(HLQty) FROM
        PpcDailyBatchPeelingBreakUpDeatils WHERE LotID='$ArrivalID' and Count='$Count')as BreakupQty
        FROM `PpcHLGradingBreakup` as PGB WHERE PGB.LotID='$ArrivalID' and HLCount='$Count'";

        $StmtWeight = $this->conn->prepare($SqlWeight);
        $StmtWeight->execute();
        $RowWeight = $StmtWeight->fetch(PDO::FETCH_ASSOC);


        // $Weight=$RowWeight['HLWeight'];
// $BreakupQty=$RowWeight['BreakupQty']+$HLQty;


         if( $ArrivalID !='' && $HLQty >0){
            $insertFields = $insertValues = $updateFields = '';
            if(!empty($RequestBody['RstrictNoOfPersons'])){
                $insertFields = ",`WorkersIds`";
                $WorkersIds = !empty($RequestBody['WorkersIds'])?$RequestBody['WorkersIds']:'';
                $insertValues = ",'$WorkersIds'";
                $updateFields = ",`WorkersIds`=$WorkersIds";
            }
              $SqlUpdate = "INSERT INTO `PpcDailyBatchPeelingBreakUpDeatils`(`DailyBatchID`, `LotID`, `HLQty`, `PostedUserId`, `PostedDateTime`, `BatchId`, `Count`, `VarietyID`$insertFields) 
          VALUES ('$BillID','$ArrivalID','$HLQty','$PostedUserId','$PostedDateTime','$BatchID','$Count','$VarietyID' $insertValues)";

        $StmtUpdate = $this->conn->prepare($SqlUpdate);
        $StmtUpdate->execute();

         }

         if($StmtUpdate){
            $Data= 'Breakup posted successfully';
        }else{
           $Data= 'Unable To Post Data successfully';
        }


        return $Data;

    }



    function getHLVABillData($BillID, $RstrictNoOfPersons = '')
    {

       $SqlType=" SELECT Type FROM `PpcDailyBatchDetails` WHERE ID='$BillID' ORDER BY `ID` DESC";
       $StmtType = $this->conn->prepare($SqlType);
       $StmtType->execute();
       $dataRow = $StmtType->fetch(PDO::FETCH_ASSOC);
       $Type=$dataRow['Type'];

        $dataRow = null;

        if($Type==1){
            
        $sqlFields = "`ID`, PDBBD.BreakupID, `Persons`, `Adjustments`,PDBD. `Extras`, `Date`, PDBD.`StationID`, PDBD.`PlantID`,
         PDBD.`Remarks`,PCM.Type AS SupplierTypeID,PCM.ContractorID, SubContractorName as BatchName,
        HLQty,PDBD.`ShiftID`, PDBD.`Type`, LotNumber,StationName,UnitName,ContrctrName ,PPO.Name as Shift,PDBD.BatchID";
        if(!empty($RstrictNoOfPersons)){
            $sqlFields .= ',PDBBD.Count';
        }
        $sqlQuery = "SELECT  $sqlFields FROM `PpcDailyBatchDetails` as PDBD 
        left join PpcDailyBatchBreakUpDeatils PDBBD on PDBBD.DailyBatchID=PDBD.ID 
        left join RmLotDetatils RAD on RAD.RmLotID=PDBBD.LotID 
        left join PpcOptions PPO on PPO.OptID=PDBD.ShiftID
         left join PpcVAStations PVS on PVS.StationID=PDBD.StationID 
         left join RmProcessingStations RPS on RPS.ProcessingStationID=PDBD.PlantID
         left join PpcSubContractorMaster PSCM on PSCM.SubContactorID=PDBD.BatchID
          left join PpcContractMaster PCM on PCM.ContractorID=PSCM.ContactorID WHERE ID='$BillID'";
        }
        else if($Type==2){
            $sqlFields = "`ID`, PDBBD.BreakupID, `Persons`, `Adjustments`,PDBD. `Extras`, `Date`, PDBD.`StationID`, PDBD.`PlantID`, PDBD.`Remarks`,PDBBD.VarietyID,
            HLQty,PDBD.`ShiftID`, PDBD.`Type`, LotNumber,StationName,UnitName,ContrctrName , SubContractorName as BatchName,PPO.Name as Shift,PPOV.ShortName as Variety,PDBD.BatchID,PDBBD.Count,PCM.Type AS SupplierTypeID ,PCM.ContractorID";
            $sqlQuery = "SELECT  $sqlFields FROM `PpcDailyBatchDetails` as PDBD 
            left join PpcDailyBatchPeelingBreakUpDeatils PDBBD on PDBBD.DailyBatchID=PDBD.ID 
            left join RmLotDetatils RAD on RAD.RmLotID=PDBBD.LotID 
            left join PpcOptions PPO on PPO.OptID=PDBD.ShiftID
            left join PpcHLVAVarietyList PPOV on PPOV.VarietyID=PDBBD.VarietyID
             left join PpcVAStations PVS on PVS.StationID=PDBD.StationID 
             left join RmProcessingStations RPS on RPS.ProcessingStationID=PDBD.PlantID
             left join PpcSubContractorMaster PSCM on PSCM.SubContactorID=PDBD.BatchID
          left join PpcContractMaster PCM on PCM.ContractorID=PSCM.ContactorID WHERE ID='$BillID'";
        }
      //echo  $sqlQuery; exit;
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        $i = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $BillData['SNo'] = ++$i;
            $BillData['BreakUpID'] = $dataRow['BreakupID'];
            $BillData['TransactionDate'] =  $this->utilController->getFormatedDate($dataRow['Date']);
            $BillData['BillDate'] = $this->utilController->getFormatedDate($dataRow['Date']);
            $BillData['LotNo'] = $dataRow['LotNumber'];
            $BillData['Plant'] = $dataRow['StationName'];
            $BillData['Center'] = $dataRow['UnitName'];
            $BillData['Persons'] = $dataRow['Persons'];
            $BillData['Shift'] = $dataRow['Shift'];
            $BillData['Adjustments'] = $dataRow['Adjustments'];
            $BillData['Extras'] = $dataRow['Extras'];
            $BillData['HLQty'] = $dataRow['HLQty'];
            $BillData['Type'] = $dataRow['Type'];
            $BillData['Remarks'] = $dataRow['Remarks'];
            $BillData['CenterID'] = $dataRow['StationID'];
            $BillData['PlantID'] = $dataRow['PlantID'];
            $BillData['ContractorName'] = $dataRow['ContrctrName'];
            $BillData['ShiftID'] = $dataRow['ShiftID'];
            $BillData['BatchID'] = $dataRow['BatchID'];
            $BillData['BatchName'] = $dataRow['BatchName'];
            $BillData['ContractorID'] = $dataRow['ContractorID']; 
            $BillData['BillID'] = $dataRow['ID'];
            $BillData['Variety'] = $dataRow['Variety'];
            $BillData['Count'] = $dataRow['Count'];
            $BillData['VarietyID'] = $dataRow['VarietyID'];
            $BillData['SupplierTypeID'] = $dataRow['SupplierTypeID'];


//if($dataRow['BreakupID']!=''){
            $ArrayData[] = $BillData;
            $TotalHLQuantity +=$dataRow['HLQty'];
//}

        }
        $ArrayTotals['TotalHLQuantity']=$TotalHLQuantity;
        $Data['BillData'] = $ArrayData;
        $Data['Totals']=$ArrayTotals;

        return $Data;
    }
    function GetWorkersBySubContractorID($id){
        if(!empty($id)){
            $sqlNoOfPersons = "SELECT NoOfPersonsPerBatch FROM PpcSubContractorMaster WHERE SubContactorID=$id";
            $stmtNoOfPersons = $this->conn->prepare($sqlNoOfPersons);
            $stmtNoOfPersons->execute();
            $dataRowNoOfPersons = $stmtNoOfPersons->fetch(PDO::FETCH_ASSOC);
            $Data['NoOfPersonsPerBatch'] = !empty($dataRowNoOfPersons['NoOfPersonsPerBatch'])?$dataRowNoOfPersons['NoOfPersonsPerBatch']:0;
            $sqlWorkers = "SELECT `WorkerID`,`WorkerName` FROM PpcWorkersMaster WHERE `SubContractorID`=$id";
            $stmtWorkers = $this->conn->prepare($sqlWorkers);
            $stmtWorkers->execute();
            $workersArr = [];
            while ($dataRowWorkers = $stmtWorkers->fetch(PDO::FETCH_ASSOC)) {
                $workersArr[] = $dataRowWorkers;
            }
            $Data['workers'] = $workersArr;
        }
        return $Data;
    }
    function GetWorkersByContractorID($ContractorID){
         if(!empty($ContractorID)){
            $sqlWorkers = "SELECT PWM.`WorkerID`,PWM.`WorkerName`
                            FROM PpcWorkersMaster PWM
                            LEFT JOIN PpcSubContractorMaster PSCM ON PSCM.`SubContactorID`=PWM.`SubContractorID`
                            WHERE PSCM.`ContactorID`=$ContractorID";
            $stmtWorkers = $this->conn->prepare($sqlWorkers);
            $stmtWorkers->execute();
            $workersArr = [];
            while ($dataRowWorkers = $stmtWorkers->fetch(PDO::FETCH_ASSOC)) {
                $workersArr[] = $dataRowWorkers;
            }
            $Data['workers'] = $workersArr;
        }
        return $Data;
    }

}
?>
