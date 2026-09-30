<?php
/* include_once '../../Config/Config.php';
include_once '../../App/Controller/AppUtilController.php'; */

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class QCQualityAssuranceChekListController
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
    function getDropdowns()
    {
        $Data['Grade'] = $this->getProductionGrades();
        $Data['PackingType'] = $this->getPackingType();
        $Data['Brand'] = $this->getBrand();
        $Data['VerifiedBy'] = $this->getVerifiedByCheckedBy('VerifiedBy');
        $Data['CheckedBy'] = $this->getVerifiedByCheckedBy('CheckedBy');
        $Data['FreezingType'] = $this->getFreezingType();
        $data['Error'] = null;
        $data['Msg'] = "Success";
        $records = array('Data' => $Data,);
        $this->response($this->json($records), 200);
    }
    function getProductionGrades()//Sales popup Grades
    {

       // $sqlQuery = "SELECT Grade FROM `ProductionGradesMaster`";
        $sqlQuery = "SELECT ID,`value` AS Grade,`Options` FROM ProductionGrades ORDER BY ABS(VALUE) ASC";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow['Grade'];
        }
        return $Data;
    }
    function getVerifiedByCheckedBy($column)//Sales popup Grades
    {
        $Data = [];
        if(!empty($column)){
            $sqlQuery = "SELECT DISTINCT $column FROM OnlineOrganolepticInspectionReport WHERE $column IS NOT NULL";
            //$sqlQuery = "SELECT ID,VALUE,OPTIONS FROM ProductionGrades ORDER BY ABS(VALUE) ASC;";
            $stmt = $this->conn->prepare($sqlQuery);
            $stmt->execute();
            while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $Data[] = $dataRow[$column];
            }
        }        
        return $Data;
    }
    function getBrand()
    {
        $sqlQuery = "SELECT BrandID, BrandName FROM ScrmBrandsMaster WHERE Status=1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }
    function getPackingType()
    {
        //$sqlQuery = "SELECT OptID,Name,Value FROM AdminGeneralOptions WHERE OptionType='PACKING_TYPE' and Status=1 ORDER by SortOrder asc";
        $sqlQuery = "SELECT PCKID OptID, PackingStyle Value FROM AdminPackingStyles WHERE Status=1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }
    function getBatchData()
    {
        $json = file_get_contents('php://input');
        $Pdata = json_decode($json, true);
        if (!empty($Pdata['ArrivalDate'])) {
             $ArrivalDate = $Pdata['ArrivalDate'];
            /*$sqlVehicleFields = "ArrivalNumber,RmArrivalID,RRD.ReceivingID,VehicalID,VehicalNumber ,ArrivalStationID as CenterID,
            ProcessingStationID as PlantID,TrackCode,RRB.ReceivingCount";
            // $sqlVehicle = "SELECT $sqlVehicleFields FROM RmArrivalDetatils AS RAD
            $sqlVehicle = "SELECT $sqlVehicleFields FROM RmReceivingBreakup As RRB 
            LEFT JOIN RmArrivalDetatils as RAD on RAD.RmArrivalID=RRB.ArrivalID 
            LEFT JOIN RmVehicalDetails AS RVD ON RAD.VehicalID=RVD.ID
            LEFT JOIN RmReceivingDetails AS RRD ON RAD.ArrivalDate=RRD.ArrivalDate AND RAD.VehicalID = RRD.VehicleID  
            AND RAD.ProcessingStationID = RRD.ReceivingPlantID 
            WHERE  RAD.ArrivalDate='$ArrivalDate'AND LotStatus =1"; */
            $where = '';
            if (empty($Pdata['pageType'])) {
                $where .= " AND (NOT EXISTS (
                        SELECT 1
                        FROM `OnlineOrganolepticInspectionReport` q
                        WHERE FIND_IN_SET(v.`LotID`,q.`BatchCode`)
                    ) ";
            }
            if (!empty($Pdata['AssuranceId'])) {
                $where .= " OR FIND_IN_SET(RmLotID,(SELECT GROUP_CONCAT(`BatchCode`) FROM `OnlineOrganolepticInspectionReport` WHERE AssuranceId=".$Pdata['AssuranceId']."))";
            }
             if (empty($Pdata['pageType'])) {
                $where .= ")";
             }
            /* $SQL ="SELECT v.`LotID` AS RmArrivalID,`LotNumber` AS ArrivalNumber 
                    FROM `PpcValueAddition` v 
                    JOIN `RmLotDetatils` L ON L.`RmLotID`=v.`LotID` 
                    JOIN RmArrivalDetatils RAD ON RAD.RmArrivalID=L.RmReferenceID 
                    WHERE RAD.ArrivalDate='$ArrivalDate' 
                    $where
                    GROUP BY LotID"; */
                $SQL ="SELECT RLD.RmLotID AS RmArrivalID,`LotNumber` AS ArrivalNumber 
                        FROM `ProdFreezingData` PFD 
                        JOIN PpcHLGradingBreakup AS PHGB ON PHGB.DataID=PFD.GradingBreakupID 
                        JOIN PpcHLGradingDetails AS PHGD ON PHGD.LotID=PHGB.LotID 
                        JOIN RmLotDetatils AS RLD ON RLD.RmLotID=PHGD.LotID
                        WHERE ProductionDate='$ArrivalDate' 
                    GROUP BY RLD.RmLotID";
            /* echo '<pre>';
            echo $SQL;
            exit; */

            $stmt = $this->conn->prepare($SQL);
            $stmt->execute();

            $vehicleData = [];
            while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
               // $ArrayVehicleData['VehicleID'] = $dataRow['VehicalID'] ? $dataRow['VehicalID'] : '0';
               // $ArrayVehicleData['VehicalNumber'] = $dataRow['VehicalNumber'] ? $dataRow['VehicalNumber'] : 'Others';
                $ArrayVehicleData['ArrivalNumber'] = $dataRow['ArrivalNumber'] ? $dataRow['ArrivalNumber'] : '';
               // $ArrayVehicleData['ReceivingCount'] = $dataRow['ReceivingCount'] ? $dataRow['ReceivingCount'] : '';
                $ArrayVehicleData['ArrivalId'] = $dataRow['RmArrivalID'];
                $vehicleData[] = $ArrayVehicleData;
            }
            $data['BatchData'] = $vehicleData;
            $data['Error'] = null;
            $data['Msg'] = "Success";
            $records = array('Data' => $data,);
            $this->response($this->json($records), 200);
        }else {
            $data['Error'] = "Due to Arrival Date is missed, Unable To Fetch Data";
            $records = array('Data' => $data);
            $this->response($this->json($records), 200);
        }
    }
    function PostData()
    {
        $json = file_get_contents('php://input');
        $data = json_decode($json, true);
        $OriginalrequestBody = $data;
        date_default_timezone_set("Asia/Calcutta");
        $currentDate = date('Y-m-d H:i:s');
        $userId = $data['UserId'];
        unset($data['UserId']);
        $data['BatchCode'] = implode(',', $data['BatchCode']);
        if (!empty($data['VerifiedByNew']) && !empty($data['VerifiedBy']) && $data['VerifiedBy'] == 'Others') {
            $data['VerifiedBy'] = $data['VerifiedByNew'];
        }
        if (!empty($data['CheckedByNew']) && !empty($data['CheckedBy']) && $data['CheckedBy'] == 'Others') {
            $data['CheckedBy'] = $data['CheckedByNew'];
        }
        unset($data['VerifiedByNew'],$data['CheckedByNew'],$data['ClientID'],$data['IPAddress'],$data['BrowserInfo'],$data['UserID']);
        $existingRecord = null;
        $AssuranceId = null;
        if (empty($data['AssuranceId'])) {
            $fields = array_keys($data);
            $fields = implode(',', $fields);
            $values = array_values($data);
            $values = implode('","', $values);
            $sql = 'INSERT INTO `OnlineOrganolepticInspectionReport` (' . $fields . ',CreatedOn,CreatedBy) VALUES ("' . $values . '","' . $currentDate . '","' . $userId . '")';
        } else {
            $AssuranceId = $data['AssuranceId'];
            $existingRecord = $this->auditLogger->fetchRowById('OnlineOrganolepticInspectionReport', 'AssuranceId', $AssuranceId);
            unset($data['AssuranceId']);

            $update = '';
            foreach ($data as $key => $value) {
                $update .= $key . '="' . $value . '",';
            }
            $update .= "UpdatedBy=$userId,UpdatedOn='$currentDate'";
            $update = trim($update, ",");
            $sql = "UPDATE OnlineOrganolepticInspectionReport SET " . $update . " WHERE AssuranceId= $AssuranceId";
        }
        $StmtTickets = $this->conn->prepare($sql);

        if ($StmtTickets->execute()) {
            if (empty($AssuranceId)) {
                $lastInsertId = $this->conn->lastInsertId();
                $createdRecord = $this->auditLogger->fetchRowById('OnlineOrganolepticInspectionReport', 'AssuranceId', $lastInsertId);
                $this->auditLogger->logInsert('OnlineOrganolepticInspectionReport', $lastInsertId, $createdRecord, $OriginalrequestBody, [
                    'ReferenceNo' => $lastInsertId,
                    'Remarks' => 'QC online organoleptic inspection created',
                ]);
            } else {
                $updatedRecord = $this->auditLogger->fetchRowById('OnlineOrganolepticInspectionReport', 'AssuranceId', $AssuranceId);
                $this->auditLogger->logUpdate('OnlineOrganolepticInspectionReport', $AssuranceId, $existingRecord, $updatedRecord, $OriginalrequestBody, [
                    'ReferenceNo' => $AssuranceId,
                    'Remarks' => 'QC online organoleptic inspection updated',
                ]);
            }
            $temp = array('Error' => null, "Msg" => "Posted Succesfully");
            $this->response($this->json($temp), 200);
        } else {
            $records = array('Error' => "Error " . mysqli_errno($this->conn));
            $this->response($this->json($records), 200);
        }
    }
    function DeleteQualityCheckListById()
    {
        $json = file_get_contents('php://input');
        $Pdata = json_decode($json, true);
        if (!empty($Pdata['AssuranceId'])) {
            $id = $Pdata['AssuranceId'];
            $existingRecord = $this->auditLogger->fetchRowById('OnlineOrganolepticInspectionReport', 'AssuranceId', $id);
            $deleteQry = "DELETE FROM OnlineOrganolepticInspectionReport WHERE AssuranceId=$id";
            $stmt = $this->conn->prepare($deleteQry);
            $stmt->execute();
            if (!empty($stmt)) {
                $this->auditLogger->logDelete('OnlineOrganolepticInspectionReport', $id, $existingRecord, $Pdata, [
                    'ReferenceNo' => $id,
                    'Remarks' => 'QC online organoleptic inspection deleted',
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
    function GetQualityCheckListById()
    {
        $json = file_get_contents('php://input');
        $Pdata = json_decode($json, true);
        if (!empty($Pdata['AssuranceId'])) {
            $id = $Pdata['AssuranceId'];
            $deleteQry = "SELECT * FROM OnlineOrganolepticInspectionReport WHERE `AssuranceId`=$id";
            $stmt = $this->conn->prepare($deleteQry);
            $stmt->execute();
            $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!empty($dataRow)) {
                $temp = array('Data'=> $dataRow,'Error' => null, "Msg" => "Deleted Succesfully");
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
    function GetQualityCheckList(){
        $json = file_get_contents('php://input');
        $Pdata = json_decode($json, true);
        $PageNo = !empty($Pdata['pageNo']) ? $Pdata['pageNo'] : 1;
        $RecordsPerPage = 100;
        $where = '';
        if(!empty($Pdata['ArrivalDate'])){
            $where .= " AND ArrivalDate='".$Pdata['ArrivalDate']."'";
        }
        if(!empty($Pdata['FreezingType'])){
            $where .= " AND FreezingType='".$Pdata['FreezingType']."'";
        }
        if(!empty($Pdata['BatchCode'])){
            $bCode = '';
            foreach($Pdata['BatchCode'] as $BatchCode){
                if(!empty($BatchCode)){
                    $bCode .= " FIND_IN_SET($BatchCode,`BatchCode`) OR ";
                }
            }
            if(!empty($BatchCode)){
                $bCode = rtrim($bCode,' OR ');
                $where .= " AND ($bCode) ";
            }
        }
        $Limit = '';$TotalCount = $PageCount = 0;
        if(empty( $Pdata['excelDownload'])){       
            $sqlCount = "SELECT COUNT(AssuranceId) AS CNT FROM OnlineOrganolepticInspectionReport WHERE 1 $where";
            $stmtCount = $this->conn->prepare($sqlCount);
            $stmtCount->execute();
            $dataCount = $stmtCount->fetch(PDO::FETCH_ASSOC);
            $TotalCount = $dataCount['CNT'];
            $PageCount = ceil($TotalCount / $RecordsPerPage);
            $LimitMax = ($PageNo - 1) * $RecordsPerPage;
            $Limit .= 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;          
        }
         
        $query = "SELECT qad.* ,b.BrandName,ps.PackingStyle   AS Packing,
                    CASE WHEN BatchNoType !='' AND BatchNoType!='Arrival' THEN CONCAT(BatchNoType,'-',ReglazeBatchNo) ELSE
                    CONCAT(BatchNoType,'-',(SELECT GROUP_CONCAT(LotNumber) FROM RmLotDetatils WHERE FIND_IN_SET(`RmLotID`,BatchCode)) )
                    END AS ArrivalNumber,DATE_FORMAT(ArrivalDate, '%d-%m-%Y') AS ArrivalDate
                    FROM OnlineOrganolepticInspectionReport qad
                    LEFT JOIN ScrmBrandsMaster b ON b.BrandID=qad.`Brand`
                    LEFT JOIN AdminPackingStyles ps ON ps.PCKID=qad.`TypeOfPacking`
                     WHERE 1  $where ORDER BY AssuranceId DESC  $Limit";
                     //echo '<pre>';print_r( $query);die;
        $ResultsData = [];
        $i = $LimitMax;
        $stmtList = $this->conn->prepare($query);
        $stmtList->execute();
        $VerifiedBy = $CheckedBy = '';
        $VerifiedByArr = $CheckedByArr = [];
        while ($dataRow = $stmtList->fetch(PDO::FETCH_ASSOC)) {
            
            $dataRow['SNO'] = ++$i;
            /* if (empty($VerifiedBy)) {
                $VerifiedBy = $dataRow['VerifiedBy'];
            }
            if (empty($CheckedBy)) {
                $CheckedBy = $dataRow['CheckedBy'];
            } */
            $VerifiedByArr[] = $dataRow['VerifiedBy'];
            $CheckedByArr[] = $dataRow['CheckedBy'];
            $ResultsData[] =  $dataRow;            
        }
        if(!empty($VerifiedByArr)){
            $VerifiedBy = implode(',',array_unique($VerifiedByArr));
        }
        if(!empty($CheckedByArr)){
            $CheckedBy = implode(',',array_unique($CheckedByArr));
        }
        
        $report['VerifiedBy'] = $VerifiedBy;
        $report['CheckedBy'] = $CheckedBy;
        $Data['for_report'] = $report;
        $Data['ResultsData'] = $ResultsData;
        //if (!empty($TotalCount)) {
            $data['Error'] = null;
            $data['Msg'] = 'Success';
            $data['TotalCount'] = $TotalCount;
            $data['PageCount'] = $PageCount;
            $data['limit'] = $RecordsPerPage;
            $data['Data'] = $Data;
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
    function getFreezingType()
    {


        $sqlQuery = "SELECT * FROM `ScrmFreezingTypeMaster` ORDER BY SortOrder asc";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }

        return $Data;
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
?>
