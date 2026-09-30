<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class AdminInwardApprovalController
{
    private $conn;
    //  private $db_table = "ScrmApplicants";
    private $utilController;
    private $Error = null;
    private $Msg = '';
    private $auditLogger;


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->auditLogger = new CentralAuditLogger($this->conn, 'ColdStore');
        // $this->utilController = new UtilController($this->conn);
    }

    function getStoreOnFloorDetails($RequestBody)
    {
        $RecordsPerPage = 100;
        $PoGradeId = $RequestBody['PoGradeId'];
        $VarietyID = $RequestBody['VarietyID'];
        $PackingStyleID = $RequestBody['PackingStyleID'];
        $ColdstoreID = $RequestBody['ColdstoreID'];
        $TenantID = $RequestBody['TenantID'];
        $SearchKey = $RequestBody['SearchKey'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];
        $StockStatus = $RequestBody['StockStatus'];
        $ProductionDate = $RequestBody['ProductionDate'];

        $TypeProduct = $RequestBody['TypeProduct'];
        $ShortName = $RequestBody['ShortName'];
        $Species = $RequestBody['Species'];


        $Query = '';

        if ($PoGradeId != "") {
            $Query .= " and CPID.Grade='$PoGradeId'";
        }

        if ($StockStatus != "") {
            $Query .= " and CPID.StockStatus='$StockStatus'";
        }else{
            $Query .= " and CPID.StockStatus='0'";
        }
        if ($ProductionDate != "") {
            $Query .= " and CPID.ProductionDate='$ProductionDate'";
        }

        if ($VarietyID != "" || $Species != '' || $TypeProduct != "") {

            if ($VarietyID != "") {
                $sQuery = " and  ProductID='$VarietyID'";
            }

            if ($Species != '') {
                $sQuery .= " and Species='$Species' ";
            }

            if ($TypeProduct != "") {
                $sQuery .= " and TypeProduct='$TypeProduct'";
            }

            $TypeProduct = $RequestBody['TypeProduct'];
            $ShortName = $RequestBody['ShortName'];
            $Species = $RequestBody['Species'];

            $sql2 = "SELECT COALESCE(GROUP_CONCAT(VarietyID),0) VarietyID FROM ScrmPIVarietyList WHERE 1=1  $sQuery  ";
            $stmt2 = $this->conn->prepare($sql2);
            $stmt2->execute();

            $dataRow2 = $stmt2->fetch(PDO::FETCH_ASSOC);
            $VarietyID = $dataRow2['VarietyID'];

            if ($VarietyID != 0) {
                $Query .= " and CPID.VarietyID in ($VarietyID)";
            } else {
                $Query .= " and CPID.VarietyID =''";
            }
        }
        if ($PackingStyleID != "") {
            $Query .= " and CPID.PackingStyleID='$PackingStyleID'";
        }
        if ($TenantID != "") {
            $Query .= " and CPID.StockHolderID='$TenantID'";
        }
        if ($ColdstoreID != "") {
            $Query .= " and CPID.UnitID='$ColdstoreID'";
        }

        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query = " and CONCAT_WS('', CBS.BarCodeID, PoNo,AbStatus,SockingType,ShortName,CSP.PackingStyle, LotNo,ProductionID,
            StockID, AvalMcs, LooseBags,PackingTypeID,CPID.Grade,CPID.FreezingType,
            ProductionDate,`ProductionTypeID`, ProductCategoryID,CPID.Remarks, UnitID) LIKE '%$SearchKey%'";
        }

        $sqlVarietyCount = "SELECT COUNT(*) as Total  FROM `CsBarCodes` AS CBS
        LEFT JOIN `CsStockData` AS CPID ON CBS.BarCodeID = CPID.BarCodeID
        WHERE CBS.BarCodeStatus='1' $Query
        AND (AvalMcs!=0 or LooseBags!=0) AND CPID.StockID IS NOT NULL ";

        $stmt = $this->conn->prepare($sqlVarietyCount);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "CBS.BarCodeID, PoNo,AbStatus,SockingType,ShortName,CSP.PackingStyle, LotNo,ProductionID,
        StockID, AvalMcs, LooseBags,PackingTypeID,CPID.Grade,CPID.FreezingType, CPID.StockStatus,
        ProductionDate,`ProductionTypeID`, ProductCategoryID,CPID.Remarks as REMARKS, UnitID,RackName,StoreName,CSRD.BlockID,BlockName ";

        $sqlQuery = "SELECT  $sqlFields FROM `CsBarCodes` AS CBS
        LEFT JOIN `CsStockData` AS CPID ON CBS.BarCodeID = CPID.BarCodeID
        LEFT JOIN ScrmPIVarietyList AS VL ON VL.VarietyID=CPID.VarietyID
        LEFT JOIN AdminPackingStyles AS CSP ON CSP.PckID=CPID.PackingStyleID
        LEFT JOIN ScrmPoData AS CsPos ON CsPos.PoID=CPID.PoID
        LEFT JOIN CsStoresRackData as CSRD on CSRD.BarCodeID=CPID.BarCodeID
        LEFT JOIN CsStoresBlockData AS CSBD on CSBD.BlockID=CSRD.BlockID
        LEFT JOIN CsStores AS CSR ON CPID.UnitID=CSR.StoreID
        WHERE CBS.BarCodeStatus='1' $Query
        AND (AvalMcs!=0 or LooseBags!=0) AND CPID.StockID IS NOT NULL  " . $Limit;

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($errorInfo);

        $ArrayStoreData = [];

        $i = $LimitMax;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayStoreList['SNO'] = ++$i;
            $ArrayStoreList['StockID'] = $dataRow['StockID'];
            $ArrayStoreList['ProductionID'] = $dataRow['ProductionID'];
            $ArrayStoreList['ProductionDate'] = $dataRow['ProductionDate'];
            $ArrayStoreList['PackingTypeID'] = $dataRow['PackingTypeID'];
            $ArrayStoreList['PoNo'] = $dataRow['PoNo'];
            $ArrayStoreList['LotNo'] = $dataRow['LotNo'];
            $ArrayStoreList['VarietyName'] = $dataRow['ShortName'];
            $ArrayStoreList['Grade'] = $dataRow['Grade'];
            $ArrayStoreList['SockingType'] = $dataRow['SockingType'];
            $ArrayStoreList['AvalMcs'] = $dataRow['AvalMcs'];
            $ArrayStoreList['PackingStyle'] = $dataRow['PackingStyle'];
            $ArrayStoreList['LooseBags'] = $dataRow['LooseBags'];
            $ArrayStoreList['BarCodeID'] = $dataRow['BarCodeID'];
            $ArrayStoreList['AbStatus'] = $dataRow['AbStatus'];
            $ArrayStoreList['ProductCategoryID'] = $dataRow['ProductCategoryID'];
            $ArrayStoreList['Location'] = $dataRow['BlockName'] . ' - ' . $dataRow['RackName'];
            $ArrayStoreList['StoreName'] = $dataRow['StoreName'];
            $ArrayStoreList['BlockID'] = $dataRow['BlockID'];
            $ArrayStoreList['BlockName'] = $dataRow['BlockName'];
            $ArrayStoreList['StockStatus'] = $dataRow['StockStatus'];

            $ArrayStoreData[] =  $ArrayStoreList;
        }

        if (count($ArrayStoreData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        if ($StockStatus == 0) {
            $Status = 0;
        } else {
            $Status = 1;
        }
        $data['Error'] = $this->Error;
        $data['Msg'] = $this->Msg;
        $data['TotalCount'] = $TotalCount;
        $data['RemainingCount'] = "";
        $data['EditLink'] = "";
        $data['Data'] = $ArrayStoreData;
        $data['PageCount'] = $PageCount;
        $data['StockStatus'] = $Status;
        $data['RecordsAval'] = $RecordsAval;

        return $data;
    }

    function postStockStatus($RequestBody)
    {
        $StockID = $RequestBody['StockID'];
        $PageNo = $RequestBody['PageNo'];
        $LoadAll = $RequestBody['LoadAll'];
        $StockStatus = $RequestBody['StockStatus'];

        $oldRows = [];
        $stockIds = explode(',', $StockID);
        foreach ($stockIds as $sid) {
            $oldRows[$sid] = $this->auditLogger->fetchRowById('CsStockData', 'StockID', trim($sid));
        }

        $sqlUpdate = "UPDATE `CsStockData` SET `StockStatus` = 1 WHERE StockID IN($StockID)";
        $stmtUpdate = $this->conn->prepare($sqlUpdate);
        $stmtUpdate->execute();

        foreach ($stockIds as $sid) {
            $trimmedSid = trim($sid);
            $newRow = $this->auditLogger->fetchRowById('CsStockData', 'StockID', $trimmedSid);
            $this->auditLogger->logUpdate('CsStockData', $trimmedSid, isset($oldRows[$trimmedSid]) ? $oldRows[$trimmedSid] : null, $newRow);
        }

        // $errorInfo = $stmtUpdate->errorInfo();
        // var_dump($errorInfo);

        if ($stmtUpdate) {
            $this->Error = null;
            $this->Msg = 'Stock Status Changed Successfully';
            return $this->getStoreOnFloorDetails($RequestBody);
        }
    }
}
