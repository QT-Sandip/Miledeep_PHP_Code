<?php
include_once '../../Config/Config.php';
include_once '../Global/Util.php';
include_once '../../App/Controller/AppUtilController.php';
include_once 'Controller/UtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class QCTreatmentRegisterReportController
{

    private $conn;
    private $restApi;
    private $utilController;
    private $Error = null;
    private $Msg = 'Success';
    private $auditLogger;


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->restApi = new REST();
        $this->utilController = new UtilController($this->conn);
        $this->auditLogger = new CentralAuditLogger($this->conn, 'QualityControl');
    }
    // GET ALL

    /**********************************Dont Change Above************************************************* */

    function getToken()
    {
        $authToken = $this->restApi->getRequestHeader();
        return $payload = Token::Verify($authToken);
    }
    function getDropdowns()
    {
        $chemicalQuery = "SELECT ChemID,ChemicalName 
                            FROM GsChemicalMaster 
                          WHERE Status=1   AND ChemID!=3 ORDER BY ChemID DESC"; //exclude slat
        $stmtCh = $this->conn->prepare($chemicalQuery);
        $stmtCh->execute();
        $chResults = [];
        $chResultsData = [];
        while ($dataChRow = $stmtCh->fetch(PDO::FETCH_ASSOC)) {
            $chResults['ChemID'] = $dataChRow['ChemID'];
            $chResults['ChemicalName'] = $dataChRow['ChemicalName'];
            $chResultsData[] =  $chResults;
        }
        if (!empty($chResultsData)) {
            $data['chemical_data'] = $chResultsData;
        } else {
            $data['chemical_data'] = [];
        }
        /* $sql_LAB_USERS = "SELECT * FROM QcLabTechnologist where LbuStatus=1 ";
        $stmtLAB_USERS = $this->conn->prepare($sql_LAB_USERS);
        $stmtLAB_USERS->execute(); */
        $VerifiedByData = [];  $InspectedByData = [];
        /* while ($dataLAB_USERSRow = $stmtLAB_USERS->fetch(PDO::FETCH_ASSOC)) {
            $VerifiedByData[] =  $dataLAB_USERSRow['LbuName'];
            $InspectedByData[] =  $dataLAB_USERSRow['LbuName'];
        } */

        $verifiedBySql = "SELECT DISTINCT `VerifiedBy` FROM TreatmentRegisterDetailsList WHERE VerifiedBy IS NOT NULL";
        $stmtVerifiedBy = $this->conn->prepare($verifiedBySql);
        $stmtVerifiedBy->execute();
        $VerifiedByData = [];
        while ($dataVerifiedByRow = $stmtVerifiedBy->fetch(PDO::FETCH_ASSOC)) {
            $VerifiedByData[] =  $dataVerifiedByRow['VerifiedBy'];
        }
        if (!empty($VerifiedByData)) {
            $data['VerifiedBy'] = $VerifiedByData;
        } else {
            $data['VerifiedBy'] = [];
        }
        $InspectedBySql = "SELECT DISTINCT `InspectedBy` FROM TreatmentRegisterDetailsList WHERE InspectedBy IS NOT NULL";
        $stmtInspectedBy = $this->conn->prepare($InspectedBySql);
        $stmtInspectedBy->execute();
        $InspectedByData = [];
        while ($dataInspectedByRow = $stmtInspectedBy->fetch(PDO::FETCH_ASSOC)) {
            $InspectedByData[] =  $dataInspectedByRow['InspectedBy'];
        }
        if (!empty($InspectedByData)) {
            $data['InspectedBy'] = $InspectedByData;
        } else {
            $data['InspectedBy'] = [];
        } 
        $shiftQuery = "SELECT `ShiftID`,`ShiftName` FROM AdminShiftDetails";
        $stmtShift = $this->conn->prepare($shiftQuery);
        $stmtShift->execute();
        $ShiftData = [];
        $shiftRes = [];
        while ($dataShiftRow = $stmtShift->fetch(PDO::FETCH_ASSOC)) {
            $ShiftData['ShiftID'] =  $dataShiftRow['ShiftID'];
            $ShiftData['ShiftName'] =  $dataShiftRow['ShiftName'];
            $shiftRes[] = $ShiftData;
        }
        if (!empty($shiftRes)) {
            $data['shiftData'] = $shiftRes;
        } else {
            $data['shiftData'] = [];
        }
        $data['Veraities'] = $this->getAllgetVeraities();
        $data['Grades'] = $this->getAllGrades();
        $data['Error'] = null;
        $data['Msg'] = "Success";
        $records = array('Data' => $data,);
        $this->response($this->json($records), 200);
    }
    function getAllgetVeraities(){
        $VeraitySql = "SELECT VarietyID ,CONCAT(`TypeProduct`,' - ',`ShortName`) AS VarietyName FROM PpcHLVAVarietyList";
        $stmtVeraity = $this->conn->prepare($VeraitySql);
        $stmtVeraity->execute();
        $VeraityData = [];
        while ($dataVeraityRow = $stmtVeraity->fetch(PDO::FETCH_ASSOC)) {
            $VeraityData[] =  $dataVeraityRow;
        }
        return $VeraityData;
    }
    function getAllGrades(){
        //$sqlQuery = "SELECT Grade FROM `ProductionGradesMaster`";
        $sqlQuery = "SELECT ID,`value` AS Grade,`Options` FROM ProductionGrades ORDER BY ABS(VALUE) ASC";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow['Grade'];
        }
        return $Data;
    }
    function GetUniqueBatchIdsByArrivalDate()
    {
        $json = file_get_contents('php://input');
        $Pdata = json_decode($json, true);
        if (!empty($Pdata['ArrivalDate'])) {
            $where = '';
            //For checking dublicates
            if (!empty($Pdata['TreatmentId'])) {
                $where .= "OR RmLotID IN(SELECT GROUP_CONCAT(`BatchId`) FROM `TreatmentRegisterDetailsList` WHERE `TreatmentId`=" . $Pdata['TreatmentId'] . ")";
                ///$where .= "OR FIND_IN_SET(RmLotID,(SELECT GROUP_CONCAT(`BatchId`) FROM `TreatmentRegisterDetailsList` WHERE `TreatmentId`=".$Pdata['TreatmentId']."))";
            }
            /* $batchIdsQuery = "SELECT ArrivalNumber,RmArrivalID 
                                FROM RmReceivingBreakup AS RRB 
                                LEFT JOIN RmArrivalDetatils AS RAD ON RAD.RmArrivalID=RRB.ArrivalID 
                            WHERE  RAD.ArrivalDate='" . $Pdata['ArrivalDate'] . "'AND LotStatus =1 
                            GROUP BY RmArrivalID";  */
            /* $batchIdsQuery = "SELECT `RmLotID` AS RmArrivalID,`LotNumber` AS ArrivalNumber FROM `RmLotDetatils` rld
                                LEFT JOIN RmArrivalDetatils AS RAD ON RAD.RmArrivalID=rld.RmReferenceID 
                                WHERE  RAD.ArrivalDate='" . $Pdata['ArrivalDate'] . "'AND RAD.LotStatus =1
                                AND NOT FIND_IN_SET(RmLotID,(SELECT GROUP_CONCAT(`BatchId`) FROM `TreatmentRegisterDetailsList`))
                                $where
                                GROUP BY RAD.RmArrivalID"; */
            /* $batchIdsQuery = "SELECT `RmLotID` AS RmArrivalID,`LotNumber` AS ArrivalNumber
                                FROM ProdSoakingDetails psd
                                LEFT JOIN RmLotDetatils rld ON rld.RmLotID=psd.LotID 
                                LEFT JOIN RmArrivalDetatils AS RAD ON RAD.RmArrivalID=rld.RmReferenceID 
                                WHERE  RAD.ArrivalDate='" . $Pdata['ArrivalDate'] . "'AND RAD.LotStatus =1
                                AND NOT EXISTS (
                                    SELECT 1
                                    FROM `TreatmentRegisterDetailsList` q
                                    WHERE `RmLotID`=q.`BatchId`
                                    -- WHERE FIND_IN_SET(`RmLotID`,q.`BatchId`)
                                )
                                $where
                                GROUP BY RAD.RmArrivalID"; */
            /* $batchIdsQuery = "SELECT `RmLotID` AS RmArrivalID,`LotNumber` AS ArrivalNumber
                                FROM ProdSoakingDetails psd
                                LEFT JOIN RmLotDetatils rld ON rld.RmLotID=psd.LotID 
                                LEFT JOIN RmArrivalDetatils AS RAD ON RAD.RmArrivalID=rld.RmReferenceID 
                                WHERE  RAD.ArrivalDate='" . $Pdata['ArrivalDate'] . "'AND RAD.LotStatus =1
                                
                                GROUP BY RAD.RmArrivalID"; */
            $batchIdsQuery = "SELECT PDH.`LotID` AS RmArrivalID,`LotNumber` AS ArrivalNumber
                                FROM ProdSoakingDetails PDH 
                                LEFT JOIN RmLotDetatils RLD ON RLD.RmLotID=PDH.LotID
                                LEFT JOIN RmArrivalDetatils AS RAD ON RAD.RmArrivalID=RLD.RmReferenceID 
                                WHERE  SoakingDate= '" . $Pdata['ArrivalDate'] . "' AND RAD.LotStatus =1
                                GROUP BY PDH.`LotID`";
            //echo '<pre>';print_r($batchIdsQuery);die;
            $stmtbBatchIds = $this->conn->prepare($batchIdsQuery);
            $stmtbBatchIds->execute();
            $batchIdsResults = [];
            $batchIdsResultsData = [];
            while ($dataBatchIdsRow = $stmtbBatchIds->fetch(PDO::FETCH_ASSOC)) {
                $batchIdsResults['ArrivalId'] = $dataBatchIdsRow['RmArrivalID'];
                $batchIdsResults['ArrivalNumber'] = $dataBatchIdsRow['ArrivalNumber'];
                $batchIdsResultsData[] =  $batchIdsResults;
            }
            if (!empty($batchIdsResultsData)) {
                $data['BatchData'] = $batchIdsResultsData;
                $data['Error'] = null;
                $data['Msg'] = "Success";
                $records = array('Data' => $data,);
                $this->response($this->json($records), 200);
            } else {
                $data['Error'] = "Unable To Fetch Data";
                $records = array('Data' => $data);
                $this->response($this->json($records), 200);
            }
        } else {
            $data['Error'] = "Due to Arrival Date is missed, Unable To Fetch Data";
            $records = array('Data' => $data);
            $this->response($this->json($records), 200);
        }
    }
    function PostData()
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $data_original = $data;
        //echo '<pre>';print_r($data);die;
        if(empty($data['BatchId'])){
            $data['BatchId'] = '';
        }
        if(empty($data['Variety'])){
            $data['Variety'] = '';
        }
        if(empty($data['Grade'])){
            $data['Grade'] = '';
        }
        date_default_timezone_set("Asia/Calcutta");
        $currentDate = date('Y-m-d H:i:s');
        $userId = $data['UserId'];
        unset($data['UserId']);
        //$data['BatchId'] = implode(',', $data['BatchId']);
        if (!empty($data['VerifiedByNew']) && !empty($data['VerifiedBy']) && $data['VerifiedBy'] == 'Others') {
            $data['VerifiedBy'] = $data['VerifiedByNew'];
        }
        if (!empty($data['InspectedByNew']) && !empty($data['InspectedBy']) && $data['InspectedBy'] == 'Others') {
            $data['InspectedBy'] = $data['InspectedByNew'];
        }
        unset($data['VerifiedByNew'],$data['InspectedByNew'],$data['ClientID'],$data['IPAddress'],$data['BrowserInfo'],$data['UserID']);
        $existingRecord = null;
        $TreatmentId = null;
        if (empty($data['TreatmentId'])) {
            $fields = array_keys($data);
            $fields = implode(',', $fields);
            $values = array_values($data);
            $values = implode('","', $values);
            $sql = 'INSERT INTO `TreatmentRegisterDetailsList` (' . $fields . ',CreatedOn,CreatedBy) VALUES ("' . $values . '","' . $currentDate . '","' . $userId . '")';
        } else {
            $TreatmentId = $data['TreatmentId'];
            $existingRecord = $this->auditLogger->fetchRowById('TreatmentRegisterDetailsList', 'TreatmentId', $TreatmentId);
            unset($data['TreatmentId']);

            $update = '';
            foreach ($data as $key => $value) {
                $update .= $key . '="' . $value . '",';
            }
            $update .= "UpdatedBy=$userId,UpdatedOn='$currentDate'";
            $update = trim($update, ",");
            $sql = "UPDATE TreatmentRegisterDetailsList SET " . $update . " WHERE TreatmentId= $TreatmentId";
        }
        $StmtTickets = $this->conn->prepare($sql);

        if ($StmtTickets->execute()) {
            if (empty($TreatmentId)) {
                $lastInsertId = $this->conn->lastInsertId();
                $createdRecord = $this->auditLogger->fetchRowById('TreatmentRegisterDetailsList', 'TreatmentId', $lastInsertId);
                $this->auditLogger->logInsert('TreatmentRegisterDetailsList', $lastInsertId, $createdRecord, $data_original, [
                    'ReferenceNo' => $lastInsertId,
                    'Remarks' => 'QC treatment register created',
                ]);
            } else {
                $updatedRecord = $this->auditLogger->fetchRowById('TreatmentRegisterDetailsList', 'TreatmentId', $TreatmentId);
                $this->auditLogger->logUpdate('TreatmentRegisterDetailsList', $TreatmentId, $existingRecord, $updatedRecord, $data_original, [
                    'ReferenceNo' => $TreatmentId,
                    'Remarks' => 'QC treatment register updated',
                ]);
            }
            $temp = array('Error' => null, "Msg" => "Treatment Register Posted Succesfully");
            $this->response($this->json($temp), 200);
        } else {
            $records = array('Error' => "Error " . mysqli_errno($this->conn));
            $this->response($this->json($records), 200);
        }
    }
    function DeleteTreatmentById()
    {
        $json = file_get_contents('php://input');
        $Pdata = json_decode($json, true);
        if (!empty($Pdata['treatment_id'])) {
            $id = $Pdata['treatment_id'];
            $existingRecord = $this->auditLogger->fetchRowById('TreatmentRegisterDetailsList', 'TreatmentId', $id);
            $deleteQry = "DELETE FROM TreatmentRegisterDetailsList WHERE TreatmentId=$id";
            $stmt = $this->conn->prepare($deleteQry);
            $stmt->execute();
            if (!empty($stmt)) {
                $this->auditLogger->logDelete('TreatmentRegisterDetailsList', $id, $existingRecord, $Pdata, [
                    'ReferenceNo' => $id,
                    'Remarks' => 'QC treatment register deleted',
                ]);
                $temp = array('Error' => null, "Msg" => "Deleted Succesfully");
                $this->response($this->json($temp), 200);
            } else {
                $records = array('Error' => "Error " . mysqli_errno($this->conn));
                $this->response($this->json($records), 200);
            }
        } else {
            $records = array('Error' => "Parameter Missed.");
            $this->response($this->json($records), 200);
        }
    }
    function GetListByPagin()
    {
        $json = file_get_contents('php://input');
        $Pdata = json_decode($json, true);
        // echo '<pre>';print_r($Pdata);die;
        $PageNo = !empty($Pdata['pageNo']) ? $Pdata['pageNo'] : 1;
        $RecordsPerPage = 100;
        $where = '';

        $Limit = '';
        $TotalCount = $PageCount = $LimitMax = 0;
        if (empty($Pdata['loadAll'])) {
            $sqlCount = "SELECT COUNT(TreatmentId) AS CNT FROM TreatmentRegisterDetailsList $where";
            $stmtCount = $this->conn->prepare($sqlCount);
            $stmtCount->execute();
            $dataCount = $stmtCount->fetch(PDO::FETCH_ASSOC);
            $TotalCount = $dataCount['CNT'];
            $PageCount = ceil($TotalCount / $RecordsPerPage);
            $LimitMax = ($PageNo - 1) * $RecordsPerPage;

            $Limit .= 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;
        }

        $query = "SELECT trd.*, ch.`ChemicalName` ,
                        (SELECT RLD.`LotNumber` FROM RmLotDetatils RLD WHERE RLD.`RmLotID`= trd.BatchId) AS ArrivalNumber
                    -- (SELECT GROUP_CONCAT(RLD.`LotNumber`) FROM RmLotDetatils RLD WHERE FIND_IN_SET(RLD.`RmLotID`,trd.BatchId)) AS ArrivalNumber
                    -- ,(SELECT `SoakingDate` FROM ProdSoakingDetails WHERE `LotID`=trd.BatchId AND `VAGrade`=trd.Grade  GROUP BY trd.BatchId,trd.Grade) AS `SoakingDate`
                    ,
                    CASE 
                    WHEN ReglazeBatchNo!='' THEN
                        (SELECT CONCAT( `TypeProduct`,'-',`ShortName`) FROM PpcHLVAVarietyList WHERE  trd.Variety = `VarietyID` ) 
                    WHEN trd.SoakInQuantity IS NOT NULL THEN 
                        (SELECT CONCAT( `TypeProduct`,'-',`ShortName`,' (',SoakInQuantity,')') FROM PpcHLVAVarietyList WHERE  trd.Variety = `VarietyID` )
                    ELSE
                        (SELECT CONCAT( `TypeProduct`,'-',`ShortName`,' (',SoakInQty,')') FROM PpcHLVAVarietyList WHERE  trd.Variety = `VarietyID` ) 
                    END AS   VarietyName,SoakInQty,trd.TubNumber
                    FROM TreatmentRegisterDetailsList trd
                    LEFT JOIN GsChemicalMaster ch ON ch.ChemID=trd.ChemicalId
                    LEFT JOIN ProdSoakingDetails PSD ON `LotID`=trd.BatchId AND PSD.Variety = trd.Variety  AND `VAGrade`=trd.Grade  AND PSD.Variety = trd.Variety
                      -- AND `SoakInQty`=trd.SoakInQuantity
                     $where
                     GROUP BY TreatmentId,SoakInQuantity,trd.TubNumber
                      ORDER BY TreatmentId DESC  $Limit";
        $ResultsData = [];
        $i = $LimitMax;
        $stmtList = $this->conn->prepare($query);
        $stmtList->execute();
        while ($dataRow = $stmtList->fetch(PDO::FETCH_ASSOC)) {
            $RowData['SNO'] = ++$i;
            $RowData['TreatmentId'] = $dataRow['TreatmentId'];
            $RowData['ArrivalDate'] = $dataRow['ArrivalDate'];
            //$RowData['SoakingDate'] = $dataRow['SoakingDate'];
            $RowData['BatchNoType'] = $dataRow['BatchNoType'];
            $RowData['BatchId'] = $dataRow['BatchId'];
            $RowData['Variety'] = $dataRow['Variety'];
            $RowData['VarietyName'] = $dataRow['VarietyName'];
            $RowData['Grade'] = $dataRow['Grade'];
            $RowData['ArrivalNumber'] = $dataRow['ArrivalNumber'];
            $RowData['ChemicalId'] = $dataRow['ChemicalId'];
            $RowData['ChemicalName'] = $dataRow['ChemicalName'];
            $RowData['InTemperature'] = $dataRow['InTemperature'];
            $RowData['OutTemperature'] = $dataRow['OutTemperature'];
            $RowData['ChlorineDioxide'] = $dataRow['ChlorineDioxide'];
            $RowData['Remarks'] = $dataRow['Remarks'];
            $RowData['VerifiedBy'] = $dataRow['VerifiedBy'];
            $RowData['InspectedBy'] = $dataRow['InspectedBy'];
            $RowData['SaltBatchNumber'] = $dataRow['SaltBatchNumber'];
            $RowData['PhosphateBatchNumber'] = $dataRow['PhosphateBatchNumber'];
            $RowData['NonPhosphateBatchNumber'] = $dataRow['NonPhosphateBatchNumber'];
            $RowData['ReglazeBatchNo'] = $dataRow['ReglazeBatchNo'];
            $RowData['Shift'] = $dataRow['Shift'];

            $RowData['SoakInQuantity'] = !empty($dataRow['SoakInQuantity']) ? $dataRow['SoakInQuantity'] : $dataRow['SoakInQty'];
            $RowData['TubNumber'] = $dataRow['TubNumber'];
            $ResultsData[] =  $RowData;
        }
        //if (!empty($TotalCount)) {
        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TotalCount'] = $TotalCount;
        $data['PageCount'] = $PageCount;
        $data['limit'] = $RecordsPerPage;
        $data['Data'] = $ResultsData;
        $data['Error'] = null;
        $data['Msg'] = "Success";
        $records = array('Data' => $data,);
        $this->response($this->json($records), 200);
        /*  } else {
            $data['Error'] = "Unable To Fetch Data";
            $records = array('Data' => $data);
            $this->response($this->json($records), 200);
        } */
    }
    /*report download code start*/
    function DownloadReport()
    {
        $json = file_get_contents('php://input');
        $Pdata = json_decode($json, true);
        $SoakDate = $Pdata['SoakDate'];
        $Shift = $Pdata['Shift'];

        $Query = '';

        /* if ($PlantID != '') {
            $Query .= " AND  PlantID='$PlantID' ";
        } */

        if ($Shift != '') {
            $Query .= " AND trd.Shift='$Shift'";
        }
        $DateFilter = " SoakingDate= '$SoakDate' ";

        $i = 0;

        $sqlFields = "SckID,StationName,TrackCode,RRD.ArrivalDate,(select distinct LotNumber from RmLotDetatils RLD where RLD.RmLotID=PDH.LotID) as LotNumber,DATE_FORMAT(SoakingDate, '%d-%m-%Y') AS SoakingDate,TubNumber,
        LineNumber,
            ChemicalName,ChemicalQty,SaltQty as Salt,SoakInCount as SourceCount,SoakInQty as InQuantity, SoakInTime as StartTime,TimePeriod,
            SoakOutCount as OutCount,SoakOutQty as OutQuantity,PDH.Remarks,
            COALESCE(PFD.TotalFreezingWeight, 0) AS TotalFreezingWeight,IceQtyPercentage,WaterQtyPercentage,SoakInTemperature,SoakOutTemperature,TRDL.*"; //sriram 14-02-25 ShiftName


        $sqlSoakingBreakup = "SELECT  $sqlFields FROM `ProdSoakingDetails`  PDH        
            -- left join AdminShiftDetails ASD on ASD.ShiftID=PDH.Shift 		
           		
            left join GsChemicalMaster GCM on GCM.ChemID=PDH.ChemicalID
            left join RmReceivingDetails RRD on RRD.ReceivingID=PDH.TrackID 
            left join RmProcessingStations RPS on RPS.ProcessingStationID=PDH.PlantID
            LEFT JOIN (SELECT TrackID, TubNo, SUM(ProductionWeight + RejectionWeight) AS TotalFreezingWeight FROM ProdFreezingData 
            GROUP BY TrackID, TubNo) PFD ON PFD.TrackID = PDH.TrackID AND PFD.TubNo = PDH.TubNumber

            JOIN (
                SELECT `BatchId`,trd.Grade,InTemperature,`OutTemperature`,gc.ChemicalName trdl_ChemicalName,`ChlorineDioxide`,trd.`Remarks` AS trdl_Remarks,
                    ShiftName, SaltBatchNumber,PhosphateBatchNumber,NonPhosphateBatchNumber,VerifiedBy,ShiftID,InspectedBy
                    ,PHVL.ShortName as Variety,PHVL.Species
                FROM TreatmentRegisterDetailsList trd
                LEFT JOIN GsChemicalMaster gc ON gc.ChemID=trd.`ChemicalId`
                LEFT JOIN AdminShiftDetails ASD ON ASD.ShiftID=trd.Shift
                LEFT JOIN ProdSoakingDetails PSD ON `LotID`=trd.BatchId AND PSD.Variety = trd.Variety  AND `VAGrade`=trd.Grade
                left join PpcHLVAVarietyList PHVL on PHVL.VarietyID=trd.Variety 
                GROUP BY BatchId,trd.Grade
			) TRDL ON PDH.LotID=TRDL.BatchId AND PDH.VAGrade=TRDL.Grade
            -- ON FIND_IN_SET(PDH.LotID,TRDL.BatchId)

            where $DateFilter  $Query ORDER BY `SoakingDate` asc";

            $sqlSoakingBreakup = "SELECT `TreatmentId`,DATE_FORMAT(trd.`ArrivalDate`, '%d-%m-%Y') AS SoakingDate
                                    -- CASE WHEN SoakingDate IS NOT NULL THEN DATE_FORMAT(SoakingDate, '%d-%m-%Y') ELSE DATE_FORMAT(trd.`ArrivalDate`, '%d-%m-%Y') END AS SoakingDate
                                    ,`BatchId`,trd.Grade,InTemperature,`OutTemperature`,gc.ChemicalName trdl_ChemicalName,`ChlorineDioxide`,trd.`Remarks` AS trdl_Remarks,
                                    ShiftName, SaltBatchNumber,PhosphateBatchNumber,NonPhosphateBatchNumber,VerifiedBy,ShiftID,InspectedBy
                                    ,PHVL.ShortName AS Variety,PHVL.Species,
                                    PDH.SckID,StationName,RRD.ArrivalDate,
                                    CASE WHEN BatchNoType IS NULL OR BatchNoType='Arrival'  THEN
                                        (CONCAT(BatchNoType,'-',(SELECT DISTINCT LotNumber FROM RmLotDetatils RLD WHERE RLD.RmLotID=PDH.LotID)))
                                    ELSE CONCAT(BatchNoType,'-',ReglazeBatchNo) END 
                                    AS LotNumber,
                                    LineNumber,
                                    ChemicalName,ChemicalQty,SaltQty AS Salt,SoakInCount AS SourceCount,SoakInQty AS InQuantity, SoakInTime AS StartTime,TimePeriod,
                                    SoakOutCount AS OutCount,SoakOutQty AS OutQuantity,PDH.Remarks,
                                    IceQtyPercentage,WaterQtyPercentage
                                FROM TreatmentRegisterDetailsList trd
                                LEFT JOIN ProdSoakingDetails PDH  ON PDH.LotID=trd.BatchId   AND PDH.VAGrade=trd.Grade -- AND PDH.Variety = trd.Variety 
                                LEFT JOIN RmReceivingDetails RRD ON RRD.ReceivingID=PDH.TrackID 
                                LEFT JOIN RmProcessingStations RPS ON RPS.ProcessingStationID=PDH.PlantID
                                LEFT JOIN GsChemicalMaster gc ON gc.ChemID=trd.`ChemicalId`
                                LEFT JOIN AdminShiftDetails ASD ON ASD.ShiftID=trd.Shift
                                LEFT JOIN PpcHLVAVarietyList PHVL ON PHVL.VarietyID=trd.Variety 
                                WHERE  
                                trd.`ArrivalDate`='$SoakDate'  $Query
                                -- GROUP BY BatchId ,trd.Grade
                                GROUP BY TreatmentId,SoakInQuantity
                                ORDER BY `SoakingDate` ASC";

        //echo '<pre>';print_r($sqlSoakingBreakup);die;

        $stmtSoakingBreakup = $this->conn->prepare($sqlSoakingBreakup);
        $stmtSoakingBreakup->execute();
        $TotalSoakInQty = $TotalSoakOutQty = 0;
        $SaltBatchNumber = $PhosphateBatchNumber = $NonPhosphateBatchNumber = $ShiftName = $lineNumber = $VerifiedBy =  $InspectedBy = '';
        while ($dataRowSoakingBreakup = $stmtSoakingBreakup->fetch(PDO::FETCH_ASSOC)) {

            $TotalSoakInQty += $dataRowSoakingBreakup['InQuantity'];
            if (!empty($dataRowSoakingBreakup['ShiftName'])) {
                $ShiftName = $dataRowSoakingBreakup['ShiftName'];
            }
            if (!empty($dataRowSoakingBreakup['LineNumber'])) {
                $lineNumber = $dataRowSoakingBreakup['LineNumber'];
            }
            if (!empty($dataRowSoakingBreakup['SaltBatchNumber'])) {
                $SaltBatchNumber = $dataRowSoakingBreakup['SaltBatchNumber'];
            }
            if (!empty($dataRowSoakingBreakup['PhosphateBatchNumber']) && $dataRowSoakingBreakup['PhosphateBatchNumber'] != '-') {
                $PhosphateBatchNumber = $dataRowSoakingBreakup['PhosphateBatchNumber'];
            }
            if (!empty($dataRowSoakingBreakup['NonPhosphateBatchNumber']) && $dataRowSoakingBreakup['NonPhosphateBatchNumber'] != '-') {
                $NonPhosphateBatchNumber = $dataRowSoakingBreakup['NonPhosphateBatchNumber'];
            }
            if (!empty($dataRowSoakingBreakup['VerifiedBy'])) {
                $VerifiedBy = $dataRowSoakingBreakup['VerifiedBy'];
            }
            if (!empty($dataRowSoakingBreakup['InspectedBy'])) {
                $InspectedBy = $dataRowSoakingBreakup['InspectedBy'];
            }
            // $TotalSoakOutQty += $dataRowSoakingBreakup['TotalFreezingWeight'];

            // $SoakingYield = ($dataRowSoakingBreakup['TotalFreezingWeight'] / $dataRowSoakingBreakup['InQuantity']) * 100;
            // // $SoakingYield = ($dataRowSoakingBreakup['OutQuantity'] / $dataRowSoakingBreakup['InQuantity']) * 100;
            $StartTime = $dataRowSoakingBreakup['StartTime'];
            $TimePeriod = $dataRowSoakingBreakup['TimePeriod'];
            $EndTime = '';
            if(!empty($StartTime) && !empty($TimePeriod)){
                $EndTime = date("H:i", strtotime($StartTime . " +$TimePeriod minutes"));
            }

            $SoakOutQty = $dataRowSoakingBreakup['OutQuantity'];
            $SoakInQty = $dataRowSoakingBreakup['InQuantity'];
            // $PickUp = ($SoakOutQty / $SoakInQty) / 100;
            $PickUp = ($SoakOutQty - $SoakInQty) / $SoakInQty * 100;

            $AryBreakUp['SNo'] = ++$i;
            $AryBreakUp['ArrivalDate'] = $this->utilController->getFormatedDate($dataRowSoakingBreakup['ArrivalDate']);
            $AryBreakUp['TrackCode'] = $dataRowSoakingBreakup['TrackCode'];
            $AryBreakUp['LotNo'] = $dataRowSoakingBreakup['LotNumber'];
            $AryBreakUp['Plant'] = $dataRowSoakingBreakup['StationName'];
            $AryBreakUp['SoakingDate'] = $dataRowSoakingBreakup['SoakingDate'];
            $AryBreakUp['TubNumber'] = $dataRowSoakingBreakup['TubNumber'];
            $AryBreakUp['LineNumber'] = $dataRowSoakingBreakup['LineNumber'];
            $AryBreakUp['Variety'] = $dataRowSoakingBreakup['Variety'];
            $AryBreakUp['Grade'] = $dataRowSoakingBreakup['Grade']?$dataRowSoakingBreakup['Grade']:'-';
            // $AryBreakUp['ChemicalName'] = $dataRowSoakingBreakup['ChemicalName'];
            $AryBreakUp['ChemicalQty'] = $dataRowSoakingBreakup['ChemicalQty']?$dataRowSoakingBreakup['ChemicalQty']:'-';
            $AryBreakUp['Salt'] = $dataRowSoakingBreakup['Salt']?$dataRowSoakingBreakup['Salt']:'-';
            $AryBreakUp['SourceCount'] = $dataRowSoakingBreakup['SourceCount']?$dataRowSoakingBreakup['SourceCount']:'-';
            $AryBreakUp['InQuantity'] = $dataRowSoakingBreakup['InQuantity']?$dataRowSoakingBreakup['InQuantity']:'-';
            $AryBreakUp['StartTime'] = $dataRowSoakingBreakup['StartTime']?$dataRowSoakingBreakup['StartTime']:'-';
            $AryBreakUp['TimePeriod'] = $dataRowSoakingBreakup['TimePeriod']?$dataRowSoakingBreakup['TimePeriod']:'-';
            $AryBreakUp['OutCount'] = $dataRowSoakingBreakup['OutCount']?$dataRowSoakingBreakup['OutCount']:'-';
            $AryBreakUp['EndTime'] = $EndTime?$EndTime:'-';
            $AryBreakUp['IceQtyPercentage'] = $dataRowSoakingBreakup['IceQtyPercentage']?$dataRowSoakingBreakup['IceQtyPercentage']:'-';
            $AryBreakUp['WaterQtyPercentage'] = $dataRowSoakingBreakup['WaterQtyPercentage']?$dataRowSoakingBreakup['WaterQtyPercentage']:'-';
            //$AryBreakUp['SoakInTemperature'] = $dataRowSoakingBreakup['SoakInTemperature'];
            // $AryBreakUp['SoakOutTemperature'] = $dataRowSoakingBreakup['SoakOutTemperature'];
            $AryBreakUp['ShiftName'] = $dataRowSoakingBreakup['ShiftName']?$dataRowSoakingBreakup['ShiftName']:'-';
            $AryBreakUp['PickUp'] = $PickUp?number_format($PickUp, 2):'-';


            //sriram 14-02-25
            // $AryBreakUp['OutQuantity'] = $dataRowSoakingBreakup['OutQuantity'];
            $AryBreakUp['OutQuantity'] = $dataRowSoakingBreakup['TotalFreezingWeight']?$dataRowSoakingBreakup['TotalFreezingWeight']:'-';
            //$AryBreakUp['Remarks'] = $dataRowSoakingBreakup['Remarks'];
            $AryBreakUp['Species'] = $dataRowSoakingBreakup['Species'];

            /*treatment register changes starts*/
            $AryBreakUp['ChemicalName'] = $dataRowSoakingBreakup['trdl_ChemicalName']?$dataRowSoakingBreakup['trdl_ChemicalName']:'-';
            $AryBreakUp['SoakInTemperature'] = $dataRowSoakingBreakup['InTemperature']?$dataRowSoakingBreakup['InTemperature']:'-';
            $AryBreakUp['SoakOutTemperature'] = $dataRowSoakingBreakup['OutTemperature']?$dataRowSoakingBreakup['OutTemperature']:'-';
            $AryBreakUp['Remarks'] = $dataRowSoakingBreakup['trdl_Remarks']?$dataRowSoakingBreakup['trdl_Remarks']:'-';
            $AryBreakUp['VerifiedBy'] = $dataRowSoakingBreakup['VerifiedBy']?$dataRowSoakingBreakup['VerifiedBy']:'-';
            $AryBreakUp['ChlorineDioxide'] = $dataRowSoakingBreakup['ChlorineDioxide']?$dataRowSoakingBreakup['ChlorineDioxide']:'-';

            $ArrayBreakupData[] = $AryBreakUp;
        }
        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        $ArrayTotals['TotalSoakInQty'] = $TotalSoakInQty;
        $ArrayTotals['TotalSoakOutQty'] = $TotalSoakOutQty;

        $report['ShiftName'] = $ShiftName;
        $report['lineNumber'] = $lineNumber;
        $report['NonPhosphateBatchNumber'] = $NonPhosphateBatchNumber;
        $report['SaltBatchNumber'] = $SaltBatchNumber;
        $report['PhosphateBatchNumber'] = $PhosphateBatchNumber;
        $report['NonPhosphateBatchNumber'] = $NonPhosphateBatchNumber;
        $report['VerifiedBy'] = $VerifiedBy;
        $report['InspectedBy'] = $InspectedBy;

        $Data['SoakingData'] = $ArrayBreakupData;
        $Data['Totals'] = $ArrayTotals;
        $Data['for_report'] = $report;
        $records = array('Data' => $Data,);
        $this->response($this->json($records), 200);
    }
    /*report download code ends*/
    function getGradesByLotId()
    {
        $json = file_get_contents('php://input');
        $Pdata = json_decode($json, true);
        $lotId = !empty($Pdata['LotId']) ? $Pdata['LotId'] : '';
        $Variety = !empty($Pdata['Variety']) ? $Pdata['Variety'] : '';
        $TreatmentId = !empty($Pdata['TreatmentId']) ? $Pdata['TreatmentId'] : '';
        if (!empty($lotId) && !empty($Variety)) {
            $GradeQry = "SELECT `LotID`,`VAGrade` FROM `ProdSoakingDetails` WHERE `LotID`=$lotId AND Variety=$Variety ";
            // echo $GradeQry;die;
            $stmtGrade = $this->conn->prepare($GradeQry);
            $stmtGrade->execute();
            $GradeResults = [];
            $GradeResultsData = [];
            while ($dataGradeRow = $stmtGrade->fetch(PDO::FETCH_ASSOC)) {
                $batchIdsResults['LotID'] = $dataGradeRow['LotID'];
                $batchIdsResults['VAGrade'] = $dataGradeRow['VAGrade'];
                $GradeResultsData[] =  $batchIdsResults;
            }
            if (!empty($GradeResultsData)) {
                $data['GradeData'] = $GradeResultsData;
                $data['Error'] = null;
                $data['Msg'] = "Success";
                $records = array('Data' => $data,);
                $this->response($this->json($records), 200);
            } else {
                $data['Error'] = "Unable To Fetch Data";
                $records = array('Data' => $data);
                $this->response($this->json($records), 200);
            }
        } else {
            $data['Error'] = "Due to Batch Id is missed, Unable To Fetch Data";
            $records = array('Data' => $data);
            $this->response($this->json($records), 200);
        }
    }
    function getVeraityByLotId()
    {
        $json = file_get_contents('php://input');
        $Pdata = json_decode($json, true);
        $lotId = !empty($Pdata['LotId']) ? $Pdata['LotId'] : '';
        $TreatmentId = !empty($Pdata['TreatmentId']) ? $Pdata['TreatmentId'] : '';
        if (!empty($lotId)) {
            $VarityQry = "SELECT SckID,LotID,PSD.Variety ,`TypeProduct`,`ShortName`,`FullName`,`VAGrade`,SoakInQty
                        ,CONCAT('SKM-',TubNumber) AS TubNumber
                        FROM ProdSoakingDetails PSD 
                        JOIN PpcHLVAVarietyList V ON PSD.Variety = V.`VarietyID`
                        WHERE `LotID`=$lotId
                        -- GROUP BY Variety,VAGrade,SoakInQty
                        ";
            $stmtVarity = $this->conn->prepare($VarityQry);
            $stmtVarity->execute();
            $VarityResults = [];
            $VarityResultsData = [];
            while ($dataVarityRow = $stmtVarity->fetch(PDO::FETCH_ASSOC)) {
                $VarityResults['LotID'] = $dataVarityRow['LotID'];
                $VarityResults['Variety'] = $dataVarityRow['Variety'];
                $VarityResults['VAGrade'] = $dataVarityRow['VAGrade'];
                $VarityResults['SoakInQty'] = $dataVarityRow['SoakInQty'];
                $VarityResults['TubNumber'] = $dataVarityRow['TubNumber'];
                $VarityResults['VarietyName'] = $dataVarityRow['TypeProduct'].'-'.$dataVarityRow['ShortName'].' ('.$dataVarityRow['VAGrade'].') ('.$dataVarityRow['SoakInQty'].')('.$dataVarityRow['TubNumber'].')';
                $disabled = false;
                $statusSql = "SELECT COUNT(`TreatmentId`) AS CNT FROM TreatmentRegisterDetailsList 
                                WHERE `BatchId`='$lotId' AND `Variety`=".$dataVarityRow['Variety']." AND `SoakInQuantity`=".$dataVarityRow['SoakInQty']." AND `Grade`='".$dataVarityRow['VAGrade']."' AND `TubNumber`='".$dataVarityRow['TubNumber']."' ";
                if($TreatmentId){
                    $statusSql .=  " AND TreatmentId!=$TreatmentId";
                }
                $stmtStatus = $this->conn->prepare($statusSql);
                $stmtStatus->execute();
                $StatusRes = $stmtStatus->fetch(PDO::FETCH_ASSOC)['CNT'];
                if(!empty($StatusRes)){
                    $disabled = true;
                }
                $VarityResults['status'] = $disabled;

                $VarityResultsData[] =  $VarityResults;
            }
            if (!empty($VarityResultsData)) {
                $data['VarietyData'] = $VarityResultsData;
                $data['Error'] = null;
                $data['Msg'] = "Success";
                $records = array('Data' => $data,);
                $this->response($this->json($records), 200);
            } else {
                $data['Error'] = "Unable To Fetch Data";
                $records = array('Data' => $data);
                $this->response($this->json($records), 200);
            }
        } else {
            $data['Error'] = "Due to Batch Id is missed, Unable To Fetch Data";
            $records = array('Data' => $data);
            $this->response($this->json($records), 200);
        }
    }
    /**********************************Dont Change Below************************************************* */

    public function response($data, $status)
    {

        // $this->set_headers($status);
        echo $data;
        exit;
    }

    private function json($data)
    {
        if (is_array($data)) {
            return json_encode($data);
        }

        mysqli_close($this->conn);
    }
}
