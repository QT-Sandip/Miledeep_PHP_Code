<?php
include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';

class PlantCheckReportController
{
    private $conn;
    private $utilController;
    private $Error = null;
    private $Msg = 'Success';
    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
    }

    public function getPlantCheckReportDetails($RequestBody)
    {
        $ArrivalID = $RequestBody['ArrivalID'];
        
        $i = 0;
        $HarvestDetailsArray=[];

        $sqlLotDetails = "SELECT group_concat(RmLotID)as RmLotID FROM `RmLotDetatils` WHERE RmReferenceID='$ArrivalID'";
        $stmtLotDetails = $this->conn->prepare($sqlLotDetails);
        $stmtLotDetails->execute();
        $dataRowLotDetails = $stmtLotDetails->fetch(PDO::FETCH_ASSOC);
        $RequestBody['LotID']=$dataRowLotDetails['RmLotID'];


       $HarvestDetailsArray= $this->getHarvestDetails($RequestBody);
       $PreprocessingDetailsArray= $this->getPreprocessingDetails($RequestBody);


        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;        
        $Data['HarvestDetails'] = $HarvestDetailsArray;
        $Data['PreprocessingDetails'] = $PreprocessingDetailsArray;
        return $Data;
    }

    function getHarvestDetails($RequestBody){
        $ArrivalID= $RequestBody['ArrivalID'];  
        $SqlArrivalNumber=" SELECT ArrivalNumber as BatchNumber,NumberOfPieces,CheckingCount,CheckingQty,DefectRemarks,
        (SELECT GROUP_CONCAT(UserName)  FROM `RmUsersList` WHERE FIND_IN_SET(ID, RHD.SupervisorORGrader)
        ) as HarvestSupervisor FROM `RmArrivalDetatils`  RAD left join RmBookingDetatils RBD on RBD.BookingNumber=RAD.ArrivalNumber
        left join RmHarvestingDetails RHD on RHD.BookingID = RBD.RmBookingID
        WHERE RmArrivalID='$ArrivalID'"; 
    $stmtArrivalNumber= $this->conn->prepare($SqlArrivalNumber);
    $stmtArrivalNumber->execute();
    $dataRowArrivalNumber = $stmtArrivalNumber->fetch(PDO::FETCH_ASSOC);

    return $dataRowArrivalNumber;

    }
    function getPreprocessingDetails($RequestBody){
        $ArrivalID= $RequestBody['ArrivalID']; 
        

            $SqlReceivingDetails = "SELECT
                PVS.UnitName AS HoPlant,
                RUL.UserName AS ReceivingSupervisor,
                RRB.ReceivingCount AS ReceivedCount,
                RRB.SampleQuantity AS ReceivedQuantity,
                PVS.StationID,
                RRB.SampleQuantity,
                RRB.NoofPieces,
                RRB.NoofPieces AS ReceivedNoofPieces,
                -- COALESCE(PD.WtAfterDH, '') AS BodyYield,

              CASE WHEN PD.HonWeight > 0 THEN ROUND((PD.WtAfterDH / PD.HonWeight) * 100, 2) ELSE 0 END AS BodyYield,

                COALESCE(RLUD.UserName, '') AS DeheadingSuperVisor,
                COALESCE(PVS.UnitName, '') AS VAUnitName,
                PHGD.StationID AS GradingStationID,
                PHGD.PlantID,
                COALESCE(PVGS.UnitName, '') AS GradingStationName,

                PD.DHID,
                PD.HonWeight,
                PD.CountAfterDH,
                -- ROUND((PD.CountAfterDH/PD.HonWeight),2) AS PlantCheckCnt
                CASE WHEN PD.HonWeight > 0 THEN ROUND(PD.CountAfterDH / PD.HonWeight, 2) ELSE 0 END AS PlantCheckCnt

            FROM RmReceivingBreakup RRB
            LEFT JOIN RmReceivingDetails RRD
                ON RRD.ReceivingID = RRB.ReceivingID
            LEFT JOIN RmUsersList RUL
                ON RUL.ID = RRD.SupervisorID
            LEFT JOIN RmProcessingStations RPS
                ON RPS.ProcessingStationID = RRD.ReceivingPlantID
            LEFT JOIN PpcDeheading PD
                ON PD.TrackBreakUpID = RRB.ReceivalBreakupID
            LEFT JOIN RmUsersList RLUD
                ON RLUD.ID = PD.SupervisorID
            LEFT JOIN PpcVAStations PVS
                ON PVS.StationID = PD.StationID
            LEFT JOIN PpcHLGradingDetails PHGD
                ON PHGD.LotID = PD.LotID
            LEFT JOIN PpcVAStations PVGS
                ON PVGS.StationID = PHGD.StationID
            WHERE ArrivalID = '$ArrivalID'";


        

        // echo print_r($SqlReceivingDetails);exit;

    
        $stmtReceivingDetails= $this->conn->prepare($SqlReceivingDetails);
        $stmtReceivingDetails->execute();

        
        while($dataRowReceivingDetails= $stmtReceivingDetails->fetch(PDO::FETCH_ASSOC)){

            $DHID = $dataRowReceivingDetails['DHID'];
            $dataRowReceivingDetails['BreakUp'] = $this->getDeheadingBreakUpDetails($DHID);
           // $AryReceivingData[]['BreakUp'] = $breackupArr;
            
            $AryReceivingData[]=$dataRowReceivingDetails;
            
        }

        $ArrayReceivingDetails['ReceivingData']=$AryReceivingData;
        $ArrayReceivingDetails['PlantCheckData']=$this->getPlantCheckDetails($RequestBody);
        $ArrayReceivingDetails['harvestSupervisor']=$this->getHarvestSupervisorDetails($RequestBody);
        $ArrayReceivingDetails['CratesData']=$this->getCrateDetails($RequestBody);
        $ArrayReceivingDetails['GradingData']=$this->getGradingDetails($RequestBody);

            return $ArrayReceivingDetails;

            
    }

    function getPlantCheckDetails($RequestBody){
        $ArrivalID= $RequestBody['ArrivalID']; 

        $SqlPlantCheckDetails="SELECT NoOfPieces as PlantCheckNoOfPieces,DefectName as PlantCheckVariety,RUL.UserName
         FROM `PpcPlantCheckData` PPCD 
        left join RmDefectsList RDL on RDL.DefectID=PPCD.VarietyDefectID 
        LEFT JOIN RmUsersList RUL ON RUL.ID = PPCD.SuperVisorId 
        WHERE ArrivalID='$ArrivalID'";
        $stmtPlantCheckDetails= $this->conn->prepare($SqlPlantCheckDetails);
        $stmtPlantCheckDetails->execute();
        $i=0;
        while($dataRowPlantCheckDetails= $stmtPlantCheckDetails->fetch(PDO::FETCH_ASSOC)){

            $ArrayPlantCheckDetails['SNo']=++$i;
            $ArrayPlantCheckDetails['PlantCheckNoOfPieces']=$dataRowPlantCheckDetails['PlantCheckNoOfPieces'];
            $ArrayPlantCheckDetails['PlantCheckVariety']=$dataRowPlantCheckDetails['PlantCheckVariety'];
            $ArrayPlantCheckDetails['PlantCheckSupervisorName']=$dataRowPlantCheckDetails['UserName'];

            $AryPlantCheckDetails[]=$ArrayPlantCheckDetails;

        }
        return  $AryPlantCheckDetails;
    }

    function getHarvestSupervisorDetails($RequestBody){
        $ArrivalID= $RequestBody['ArrivalID']; 

        $SqlSupervisorData=" SELECT
        GROUP_CONCAT(DISTINCT RmUL2.UserName) AS Supervisors  FROM PpcCratesData PCD
        left join `RmArrivalDetatils` RAD  on PCD.ArrivalID=RAD.RmArrivalID
        left join RmBookingDetatils RBD on RBD.BookingNumber=RAD.ArrivalNumber
        left join RmHarvestingDetails RHD on RHD.BookingID=RBD.RmBookingID
        LEFT JOIN RmUsersList AS RmUL2 ON FIND_IN_SET(RmUL2.ID, RHD.SupervisorORGrader)
        WHERE RmArrivalID='$ArrivalID' GROUP BY RHD.HarvestingID"; 
    $StmtSupervisorData= $this->conn->prepare($SqlSupervisorData);
        $StmtSupervisorData->execute();
        $dataRowSupervisor= $StmtSupervisorData->fetch(PDO::FETCH_ASSOC);
        return $dataRowSupervisor;
    }

    function getCrateDetails($RequestBody){
        $ArrivalID= $RequestBody['ArrivalID']; 

        $SqlCratesData=" SELECT PCD.CrateNo,PCD.Weight as ReceivedCrateWeight,RHBD.Weight as ActualCrateWeight,RPS.FullName as Plant
        FROM PpcCratesData PCD
        left join `RmArrivalDetatils` RAD  on PCD.ArrivalID=RAD.RmArrivalID
        left join RmBookingDetatils RBD on RBD.BookingNumber=RAD.ArrivalNumber
        left join RmHarvestingDetails RHD on RHD.BookingID=RBD.RmBookingID
        left join RmHarvestingBreakupDetails RHBD on RHBD.CrateNumber=PCD.CrateNo and RHBD.HarvestingID=RHD.HarvestingID 
        left join RmProcessingStations RPS on RPS.ProcessingStationID=PCD.PlantID
        WHERE RmArrivalID='$ArrivalID'"; 




        $stmtCratesData= $this->conn->prepare($SqlCratesData);
        $stmtCratesData->execute();
        $i=0;
        while($dataRowCratesData= $stmtCratesData->fetch(PDO::FETCH_ASSOC)){

        $WeightDifference=$dataRowCratesData['ReceivedCrateWeight']- $dataRowCratesData['ActualCrateWeight'];

        if($WeightDifference>0){
        $ColourCode="green";
        }
        else if($WeightDifference<0){
            $ColourCode="red";

        }
        else{
            $ColourCode="black";
        
        }

            $ActualCrateWeight   = round((float)$dataRowCratesData['ActualCrateWeight'], 3);
            $ReceivedCrateWeight = round((float)$dataRowCratesData['ReceivedCrateWeight'], 3);
            $WeightDifference    = round($ReceivedCrateWeight - $ActualCrateWeight, 3);

            $ArrayCratesData['SNo']=++$i;
            $ArrayCratesData['CrateNo']=$dataRowCratesData['CrateNo'];        
            // $ArrayCratesData['ActualCrateWeight']=$dataRowCratesData['ActualCrateWeight'];        
            $ArrayCratesData['Plant']=$dataRowCratesData['Plant'];
            // $ArrayCratesData['ReceivedCrateWeight']=$dataRowCratesData['ReceivedCrateWeight'];
            // $ArrayCratesData['WeightDifference']=$dataRowCratesData['ReceivedCrateWeight']- $dataRowCratesData['ActualCrateWeight'];
            $ArrayCratesData['ActualCrateWeight']   = number_format($ActualCrateWeight, 3, '.', '');
            $ArrayCratesData['ReceivedCrateWeight'] = number_format($ReceivedCrateWeight, 3, '.', '');
            $ArrayCratesData['WeightDifference']    = number_format($WeightDifference, 3, '.', '');
            $ArrayCratesData['ColourCode']=$ColourCode;
        
            $AryCratesData[]=$ArrayCratesData;

        }
        return  $AryCratesData;

        
    }

    function getGradingDetails($RequestBody){
        $LotID= $RequestBody['LotID']; 
          $ArrivalID= $RequestBody['ArrivalID']; 
       
    
        $SqlGradingPlant= "SELECT MixIDs AS MixedID , COALESCE(GROUP_CONCAT( DISTINCT StationName),'') as GradingPlant,RLUG.UserName as GradingSupervisor 
        FROM `PpcHLGradingDetails` PHGD 
        left join RmProcessingStations RPS on RPS.ProcessingStationID=PHGD.PlantID      
        left join RmUsersList  RLUG on RLUG.ID=PHGD.GradingSupervisorID
        WHERE LotID in ($LotID) OR FIND_IN_SET($LotID, MixIDs) >= 1"; 
       
        $stmtGradingPlant= $this->conn->prepare($SqlGradingPlant);
        $stmtGradingPlant->execute();
        $dataRowGradingPlant= $stmtGradingPlant->fetch(PDO::FETCH_ASSOC);


        $sql = " SELECT GROUP_CONCAT( DISTINCT CONCAT(RAD.ArrivalNumber,' - ',RLD.PurchaseWeight)
        Order by RAD.ArrivalNumber SEPARATOR ', ') AS MixedBatchCodes,
        (
            SELECT SUM(RLD2.PurchaseWeight)
            FROM RmLotDetatils RLD2
            WHERE FIND_IN_SET(
                RLD2.RmLotID,
                (
                    SELECT GROUP_CONCAT(DISTINCT MixedID)
                    FROM PpcHLGradingBreakup
                    WHERE LotID = PGB.LotID
                )
            )
        ) AS TotalHonQty
        FROM PpcHLGradingBreakup as PGB
        left join RmLotDetatils as RLD ON FIND_IN_SET(RLD.RmLotID, PGB.MixedID)
        left join RmArrivalDetatils as RAD  ON RAD.RmArrivalID = RLD.RmReferenceID
        WHERE PGB.LotID ='$LotID' OR FIND_IN_SET($LotID, MixedID) >= 1";
         


        $stmt = $this->conn->prepare($sql);
        // echo print_r($sql);exit;
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $MixedBatchCodes = isset($row['MixedBatchCodes']) ? $row['MixedBatchCodes'] : '';
        $TotalHonQty = (float)(isset($row['TotalHonQty']) ? $row['TotalHonQty'] : 0);
        

        $AryGradingDetails=$this->getHeadOnCount($LotID);
        $FinalYield=$AryGradingDetails['BillYield']; 
        $TotalHlWeight=$AryGradingDetails['TotalHlWeight']; 

        $SqlGradingDetails=" SELECT PackingGrade,HLCount,HLWeight,ShortName as Variety FROM `PpcHLGradingBreakup` as PHGB 
        left join PpcHLVAVarietyList as PHVA on PHVA.VarietyID=PHGB.VarietyID
        where LotID in ($LotID)  OR FIND_IN_SET($LotID, MixedID) >= 1 Order by HLCount ASC"; 
        $stmtGradingDetails= $this->conn->prepare($SqlGradingDetails);
        $stmtGradingDetails->execute();
        $ArrayGradingDetails=[];
        $i=0;
        while($dataRowGradingDetails= $stmtGradingDetails->fetch(PDO::FETCH_ASSOC)){
        
            // $EachGradeHlWeight = $dataRowGradingDetails['HLWeight'];
            // $count = $dataRowGradingDetails['HLCount'];
            // $EstimatedHeadon= ($EachGradeHlWeight / $FinalYield) * 100;
            // $Pieces = $EachGradeHlWeight * $count * 2.2;
            // $HeadonCount= $Pieces / $EstimatedHeadon;         
            // $TruncatedHeadonCount = floor($HeadonCount * 10) / 10; // if display single digit 31.992 =31.9
            // $TruncatedHeadonCount = floor($HeadonCount * 100) / 100; // if display two digits 31.992 =31.99
    

       
            //  HL count * 2.2 * Hon % = Head on Count  (new calculation)
            $HLCount = (float)$dataRowGradingDetails['HLCount'];
            $HonPercent = $this->getHonPercentByHLCount($HLCount);
            $HeadonCount = $HLCount * 2.2 * ($HonPercent / 100);
            $TruncatedHeadonCount = floor($HeadonCount * 10) / 10;

            $AryGrading['SNo']=++$i;
            $AryGrading['Grade']=$dataRowGradingDetails['PackingGrade'];
            // $AryGrading['Count']=$dataRowGradingDetails['HLCount'];
            // $AryGrading['GradingWeight']=$dataRowGradingDetails['HLWeight'];
            $AryGrading['Count'] = number_format((float)$dataRowGradingDetails['HLCount'], 1, '.', '');
            $AryGrading['GradingWeight'] = number_format((float)$dataRowGradingDetails['HLWeight'], 3, '.', '');
            $AryGrading['Variety']=$dataRowGradingDetails['Variety'];
            // $AryGrading['HeadOnCount']=$TruncatedHeadonCount;
            $AryGrading['FinalYield']=$FinalYield;
            $AryGrading['HeadOnCount'] = number_format($TruncatedHeadonCount, 1, '.', '');
            $AryGrading['HonPercent'] = $HonPercent;

            $ArrayGradingDetails[]=$AryGrading;

        }
        $Data['GradingDetails']=$ArrayGradingDetails;
        $Data['GradingPlant']=$dataRowGradingPlant['GradingPlant'];
        $Data['GradingSupervisor']=$dataRowGradingPlant['GradingSupervisor'];
        $Data['BatchNumbers'] = $MixedBatchCodes;
        $Data['TotalHonQty'] = $TotalHonQty;
      

        return $Data;

        
        
    }



    function getHonPercentByHLCount($HLCount)
    {
        if ($HLCount >= 16 && $HLCount <= 34) {
            return 70;
        } elseif ($HLCount >= 35 && $HLCount <= 49) {
            return 69;
        } elseif ($HLCount >= 50 && $HLCount <= 80) {
            return 68;
        } elseif ($HLCount >= 81) {
            return 67;
        }
        return 0;
    }


    function getHeadOnCount($LotID)
    {


        $SqlRmLotDetails = "SELECT
        im.PurchaseDate, 
        im.LotNumber,
        im.AgentID,
        RPC.IsCalcVales,
        RPC.Comission,
        im.DriverID,
        im.VehicalID,
        im.FarmAddress,
        im.PurchaseWeight AS ExpQty,
        im.PurchaseCount AS ExpCount,
        im.VehicalID ,
        RPS.SType ,
        im.DriverID  FROM
        RmLotDetatils as im
        left JOIN RmPurchaseCategory as RPC  on RPC.CatID=im.PurchaseCategoryID 
        left JOIN RmPurchaseStations as RPS  on RPS.PurchaseStationID=im.PurchaseStationID
        WHERE im.RmLotID in ($LotID)";

        $StmtLotDtls= $this->conn->prepare($SqlRmLotDetails);
        $StmtLotDtls->execute();
        $RowLotDetails= $StmtLotDtls->fetch(PDO::FETCH_ASSOC);

        $PurchaseDate = $RowLotDetails['PurchaseDate'];
        $ExpCount = $RowLotDetails['ExpCount'];
        $ExpQty = $RowLotDetails['ExpQty'];
        $SType = $RowLotDetails['SType'];


        $SqlHonGrading = "SELECT sum(HonWeight) as HonPacked FROM 
                `PpcHonGradingBreakup` WHERE LotID='$LotID'";
        $StmtHonGrading= $this->conn->prepare($SqlHonGrading);
        $StmtHonGrading->execute();
        $RowHonGrading= $StmtHonGrading->fetch(PDO::FETCH_ASSOC);
        $HonPacked = $RowHonGrading['HonPacked'];

        $SqlGrading=" SELECT PackingGrade,HLCount,HLWeight,ShortName as Variety FROM `PpcHLGradingBreakup` as PHGB 
        left join PpcHLVAVarietyList as PHVA on PHVA.VarietyID=PHGB.VarietyID
        where LotID in ($LotID) Order by HLCount ASC"; 	

        $StmtGrading= $this->conn->prepare($SqlGrading);
        $StmtGrading->execute();

        $AryGrades = array();
        while ($RowGrading= $StmtGrading->fetch(PDO::FETCH_ASSOC)) {
            $HLWeight = $HLWeight + $RowGrading['HLWeight'];

            $AryGrades[] = $RowGrading;
        }

        if ($RowLotDetails['IsCalcVales'] == 0) {

            $StandardYield = $this->getStandardYeild($ExpCount, $SType);
            $HonQty = $ExpQty - $HonPacked;
            $BillYield = round(($HLWeight / $HonQty) * 100, 2);	
            $YieldError = 0;
        } else {
            $fh = fopen($filename, "a");			
            $TempYield = $this->getStandardYeild($ExpCount, $SType);
            $TempCount = $this->getActualCount($TempYield, $AryGrades);
            $AvgCount = $this->getActualCount($TempYield, $AryGrades);
            $StandardYield = $this->getStandardYeild($AvgCount, $SType);
            // $LoopCount = 0;
            $YieldError = 0;

            while ($TempYield != $StandardYield) {
                $TempCount = $this->getActualCount($StandardYield, $AryGrades);
                $TempYield = $this->getStandardYeild($TempCount, $SType);
                $AvgCount = $this->getActualCount($TempYield, $AryGrades);
                $StandardYield = $this->getStandardYeild($AvgCount, $SType);
                $AvgCount = $this->getActualCount($StandardYield, $AryGrades);

                // if ($LoopCount > 10) {
                    $YieldError = 1;
                    if ($TempYield < $StandardYield) {
                        $StandardYield = $TempYield;
                        $AvgCount = $TempCount;
                    }
                // 	break;
                // }
                // $LoopCount++;
            }

            $BillYield = $StandardYield;
            
        }

        $DATA['BillYield']=$BillYield;
        $DATA['TotalHlWeight']=$BillHonWeight;   
        $DATA['AryGrades']=$AryGrades;
        return $DATA;
    }

    function getActualCount($StandardYield, $AryGrades)
    {

        $Weight = 0;
        $Pieces = 0;

        foreach ($AryGrades as $Item) {
            $Weight += $Item['HLWeight'];
            $Pieces += $Item['HLWeight'] * $Item['HLCount'] * 2.2;
        }

        $HonQty = ($Weight / $StandardYield) * 100;

        $ActCount = $Pieces / $HonQty;
        $ActCount = round($ActCount, 2);	

        return $ActCount;
    }


    function getStandardYeild($AVG_COUNT, $TYPE)
    {

        global $PurchaseDate;
        $sql_yield = "SELECT * FROM `RmStandardYields`
            where Type='$TYPE' and EffectiveDate <= '$PurchaseDate'
            and LowerCount <= '$AVG_COUNT'
            and UpperCount >='$AVG_COUNT' ORDER by EffectiveDate Desc LIMIT 1";	

        $stmt= $this->conn->prepare($sql_yield);
        $stmt->execute();
        $row= $stmt->fetch(PDO::FETCH_ASSOC);

        $STRND_YEILD = $row['Yield'];

        return $STRND_YEILD;
    }
    function getDeheadingBreakUpDetails($DHID){
        if($DHID){
            $sql = "SELECT PDB.NoOfPeaces,RDL.DefectName AS Name,PVS.UnitName,RUL.UserName
             FROM PpcDeheadingBreakup PDB
            -- LEFT JOIN PpcOptions PO ON PO.OptID=PDB.VarietyID
            LEFT JOIN RmDefectsList RDL ON RDL.DefectID=PDB.VarietyID
            LEFT JOIN PpcVAStations PVS ON PVS.StationID = PDB.CenterId
            LEFT JOIN RmUsersList RUL ON RUL.ID = PDB.SuperVisorId
             WHERE ParentDHID=$DHID";
            $stmt= $this->conn->prepare($sql);
            $stmt->execute();
            $data = [];
            while ($row= $stmt->fetch(PDO::FETCH_ASSOC)) {
                $data[] = $row;
            }
            return $data;
        }
    }

}
