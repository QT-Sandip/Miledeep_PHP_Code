<?php

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class QcKitsController
{
    private $conn;
    private $utilController;
    private $Error = null;
    private $Msg = '';
    private $auditLogger;

    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->auditLogger = new CentralAuditLogger($this->conn, 'QualityControl');
        // $this->utilController = new UtilController($this->conn);
    }
    public function postQcKitsDetails($RequestBody)
    {
        $KitID = $RequestBody['KitID'];
        $Parameter = $RequestBody['Parameter'];
        $KitsOrderedDate = $RequestBody['KitsOrderedDate'];
        $KitsReceivedDate = $RequestBody['KitsReceivedDate'];
        $KitsLotNo = $RequestBody['KitsLotNo']; // Directly use this KitLotNo
        $NoOfKitsOrdered = $RequestBody['NoOfKitsOrdered'];
        $NoOfKitsReceived = $RequestBody['NoOfKitsReceived'];
        $TotalWells = $RequestBody['TotalWells'];
        $InvoiceNo = $RequestBody['InvoiceNo'];
        $ManufactureDate = $RequestBody['ManufactureDate'];
        $ExpiryDate = $RequestBody['ExpiryDate'];
        date_default_timezone_set("Asia/Kolkata");
        $PostedDateAndTime = date("Y-m-d H:i:s");
        $PostedUserID = 1;
        $existingKit = null;
        if ($KitID != '') {
            $existingKit = $this->auditLogger->fetchRowById('QcKitsDetails', 'KitID', $KitID);
        }

        if ($KitID == '') {
            // Directly insert the new record with KitsLotNo without checking duplicates
            $sqlInsert = "INSERT INTO QcKitsDetails (KitsOrderedDate, KitsReceivedDate, KitsLotNo, 
            NoOfKitsOrdered, NoOfKitsReceived, InvoiceNo, ManufactureDate, ExpiryDate, Parameter, 
            TotalWells, PostedUserID, PostedDateAndTime) 
            VALUES ('$KitsOrderedDate', '$KitsReceivedDate', '$KitsLotNo', '$NoOfKitsOrdered', 
            '$NoOfKitsReceived', '$InvoiceNo', '$ManufactureDate', '$ExpiryDate', '$Parameter', 
            '$TotalWells', '$PostedUserID', '$PostedDateAndTime')";

            $stmtInsert = $this->conn->prepare($sqlInsert);
            $stmtInsert->execute();

            if ($stmtInsert) {
                $this->Error = null;
                $this->Msg = 'Kits Posted Successfully';
                $KitID = $this->conn->lastInsertId();
                $createdKit = $this->auditLogger->fetchRowById('QcKitsDetails', 'KitID', $KitID);
                $this->auditLogger->logInsert('QcKitsDetails', $KitID, $createdKit, $RequestBody, [
                    'ReferenceNo' => $KitsLotNo,
                    'Remarks' => 'QC kits header created',
                ]);


                if ($NoOfKitsReceived >= 0) {
                    $this->postKitsBreakUpData($RequestBody, $KitID);
                }
                // }
            } else {
                $this->Error = "Unable to Post Kits Data";
                $this->Msg = "Unable to Post Kits Data";
            }
        } else {
            $SqlBreakupCount = "SELECT count(*) as Rowcount FROM `QcKitsBreakupDetails` WHERE KitID='$KitID'  ORDER BY `KitBreakupID` DESC";
            $stmtBreakupCount = $this->conn->prepare($SqlBreakupCount);
            $stmtBreakupCount->execute();
            $dataRowBreakupCount = $stmtBreakupCount->fetch(PDO::FETCH_ASSOC);
            $RowBreakupCount = $dataRowBreakupCount['Rowcount'];

            // Update existing record logic
            $sqlUpdate = "UPDATE QcKitsDetails SET KitsOrderedDate='$KitsOrderedDate', 
            KitsReceivedDate='$KitsReceivedDate', KitsLotNo='$KitsLotNo', Parameter='$Parameter',
            TotalWells='$TotalWells', NoOfKitsOrdered='$NoOfKitsOrdered', 
            NoOfKitsReceived='$NoOfKitsReceived', InvoiceNo='$InvoiceNo', 
            ManufactureDate='$ManufactureDate', ExpiryDate='$ExpiryDate', 
            PostedUserID='$PostedUserID', PostedDateAndTime='$PostedDateAndTime' 
            WHERE KitID='$KitID'";

            $stmtUpdate = $this->conn->prepare($sqlUpdate);
            $stmtUpdate->execute();

            if ($stmtUpdate) {
                if ($RowBreakupCount == 0) {
                    $this->postKitsBreakUpData($RequestBody, $KitID);
                }
                $this->Error = null;
                $this->Msg = 'Kits Updated Successfully';
                $updatedKit = $this->auditLogger->fetchRowById('QcKitsDetails', 'KitID', $KitID);
                $this->auditLogger->logUpdate('QcKitsDetails', $KitID, $existingKit, $updatedKit, $RequestBody, [
                    'ReferenceNo' => $KitsLotNo,
                    'Remarks' => 'QC kits header updated',
                ]);
            } else {
                $this->Error = "Unable to Update Kits Data";
                $this->Msg = "Unable to Update Kits Data";
            }
        }

        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        $Data['KitID'] = $KitID;
        $Data['KitsLotNo'] = $KitsLotNo;

        return $Data;
    }


    function postKitsBreakUpData($RequestBody, $KitID)
    {
        // Fetch the base KitsLotNo from the request
        $originalLotNo = $RequestBody['KitsLotNo'];

        // Get the current maximum KitBreakupLotNo ending number
        $sqlFetchMax = "SELECT max(KitBreakUpAutoIncrementNumber) as KitBreakUpAutoIncrementNumber FROM `QcKitsDetails` as QKD 
        left join QcKitsBreakupDetails as QKBD on QKBD.KitID=QKD.KitID WHERE KitsLotNo='$originalLotNo'";
        $stmtFetchMax = $this->conn->prepare($sqlFetchMax);
        $stmtFetchMax->execute();
        $result = $stmtFetchMax->fetch(PDO::FETCH_ASSOC);

        // Start incrementing from the next number
        $startNumber = $result['KitBreakUpAutoIncrementNumber'];

        // Get required details from the request
        $NoOfKitsReceived = $RequestBody['NoOfKitsReceived'];
        $TotalWells = $RequestBody['TotalWells'];
        $NoOfWells = $TotalWells / $NoOfKitsReceived;

        // Insert new KitBreakupLotNo values
        for ($i = 1; $i <= $NoOfKitsReceived; $i++) {
            $incrementedLotNo = $originalLotNo . '-' . ($startNumber + $i);
            $KitBreakUpAutoIncrementNumber = $startNumber + $i;

            $sqlInsertBreakUp = "INSERT INTO QcKitsBreakupDetails (KitID,KitBreakUpAutoIncrementNumber, KitBreakupLotNo, NoOfWells, BalanceWells)
                                 VALUES ('$KitID','$KitBreakUpAutoIncrementNumber', '$incrementedLotNo', '$NoOfWells', '$NoOfWells')";
            $stmtInsertBreakUp = $this->conn->prepare($sqlInsertBreakUp);
            $stmtInsertBreakUp->execute();
            if ($stmtInsertBreakUp) {
                $breakupId = $this->conn->lastInsertId();
                $createdBreakup = $this->auditLogger->fetchRowById('QcKitsBreakupDetails', 'KitBreakupID', $breakupId);
                $this->auditLogger->logInsert('QcKitsBreakupDetails', $breakupId, $createdBreakup, $RequestBody, [
                    'ReferenceNo' => $KitID,
                    'Remarks' => 'QC kits breakup created',
                ]);
            }
        }

        return true;
    }


    public function getQcKitsDetails($RequestBody)
    {

        $BreakUpData = $RequestBody['BreakUpData'];
        $KitID = $RequestBody['KitID'];

        if ($BreakUpData == "1") {
            $KitsData = $this->getBreakUpDetails($KitID);
        } else {
            $KitsData = $this->getQcKitsData($RequestBody);
        }


        return $KitsData;
    }
    public function getQcKitsData($RequestBody)
    {
        $KitID = $RequestBody['KitID'];
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $PageNo = $RequestBody['PageNo'];
        $SearchKey = $RequestBody['SearchKey'];
        $LoadAll = $RequestBody['LoadAll'];

        $Query = '';

        $DateFilters = " BETWEEN '$FromDate' AND '$ToDate'";

        $RecordsPerPage = 100;
        $LimitMax = ($PageNo - 1) * $RecordsPerPage;
        $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;

        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query .= " and CONCAT_WS('', KitID, KitsOrderedDate, KitsReceivedDate, KitsLotNo, NoOfKitsOrdered,NoOfKitsReceived,
            InvoiceNo,ManufactureDate,ExpiryDate,Parameter) LIKE '%$SearchKey%'";
        }

        $sqlCount = "SELECT * FROM QcKitsDetails WHERE KitsOrderedDate $DateFilters $Query ";
        $stmt = $this->conn->prepare($sqlCount);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $TotalCount = $dataRow['Total'];
        $PageCount = ceil($TotalCount / $RecordsPerPage);

        $sqlFields = "KitID, KitsOrderedDate, KitsReceivedDate, KitsLotNo, NoOfKitsOrdered ,NoOfKitsReceived,
        InvoiceNo,ManufactureDate,ExpiryDate,Parameter,TotalWells";

        if ($KitID != '') {
            $sqlQuery = "SELECT $sqlFields FROM QcKitsDetails WHERE KitID='$KitID'";
        } else {
            $sqlQuery = "SELECT $sqlFields FROM QcKitsDetails 
            WHERE KitsOrderedDate $DateFilters $Query order by KitsOrderedDate asc " . $Limit;
        }
        // echo $sqlQuery;
        // exit;
        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        $ArrayQcKitsData = [];

        $i = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayQcKitsList['SNO'] = ++$i;
            $ArrayQcKitsList['KitID'] = $dataRow['KitID'];
            $ArrayQcKitsList['Parameter'] = $dataRow['Parameter'];
            $ArrayQcKitsList['KitsOrderedDate'] = $dataRow['KitsOrderedDate'];
            $ArrayQcKitsList['KitsReceivedDate'] = $dataRow['KitsReceivedDate'];
            $ArrayQcKitsList['KitsLotNo'] = $dataRow['KitsLotNo'];
            $ArrayQcKitsList['NoOfKitsOrdered'] = $dataRow['NoOfKitsOrdered'];
            $ArrayQcKitsList['NoOfKitsReceived'] = $dataRow['NoOfKitsReceived'];
            $ArrayQcKitsList['InvoiceNo'] = $dataRow['InvoiceNo'];
            $ArrayQcKitsList['ManufactureDate'] = $dataRow['ManufactureDate'];
            $ArrayQcKitsList['ExpiryDate'] = $dataRow['ExpiryDate'];
            $ArrayQcKitsList['TotalWells'] = $dataRow['TotalWells'];

            $ArrayQcKitsList['EditURL'] = 'qc-kits-create?KitID=' . $dataRow['KitID'];
            $ArrayQcKitsList['ConfirmURL'] = 'qc-kits-create?KitID=' . $dataRow['KitID'] . '&BreakUpData=1';
            $ArrayQcKitsList['DeleteAccess'] = $this->getDeleteAccess($dataRow['KitID']);

            $ArrayQcKitsData[] = $ArrayQcKitsList;
        }

        if (count($ArrayQcKitsData) < $RecordsPerPage) {
            $RecordsAval = 0;
        } else {
            $RecordsAval = 1;
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['TotalCount'] = $TotalCount;
        $data['RemainingCount'] = "";
        $data['PageCount'] = $PageCount;
        $data['RecordsAval'] = $RecordsAval;
        $data['Data'] = $ArrayQcKitsData;

        return $data;
    }

    public function getBreakUpDetails($KitID)
    {
        $sqlBreakUpFields = "KitBreakupID, QKBD.KitID, KitBreakupLotNo, NoOfWells,BalanceWells,UtilizedWells,DamagedWells ";

        $sqlBreakUp = "SELECT $sqlBreakUpFields, QKD.KitsOrderedDate ,QKD.KitsReceivedDate, QKD.KitsLotNo
        FROM QcKitsBreakupDetails AS QKBD
        LEFT JOIN QcKitsDetails AS QKD ON QKBD.KitID = QKD.KitID
       
        WHERE QKBD.KitID = '$KitID'";
        // LEFT JOIN QcRmAbReport AS QRAR ON QKBD.KitID = QRAR.KitID
        //QRAR.UsedWells

        $stmtBreakUp = $this->conn->prepare($sqlBreakUp);
        $stmtBreakUp->execute();

        $ArrayBreakUpDataList = [];

        $i = 0;
        while ($dataBreakUpRow = $stmtBreakUp->fetch(PDO::FETCH_ASSOC)) {
            $ArrayBreakUpData['SNO'] = ++$i;
            $ArrayBreakUpData['KitBreakupID'] = $dataBreakUpRow['KitBreakupID'];
            $ArrayBreakUpData['KitID'] = $dataBreakUpRow['KitID'];
            $ArrayBreakUpData['KitBreakupLotNo'] = $dataBreakUpRow['KitBreakupLotNo'];
            $ArrayBreakUpData['NoOfWells'] = $dataBreakUpRow['NoOfWells'];
            $ArrayBreakUpData['KitsOrderedDate'] = $dataBreakUpRow['KitsOrderedDate'];
            $ArrayBreakUpData['KitsReceivedDate'] = $dataBreakUpRow['KitsReceivedDate'];
            $ArrayBreakUpData['KitsLotNo'] = $dataBreakUpRow['KitsLotNo'];
            $ArrayBreakUpData['UtilizedWells'] = $dataBreakUpRow['UtilizedWells'];
            $ArrayBreakUpData['BalanceWells'] = $dataBreakUpRow['BalanceWells'];
            $ArrayBreakUpData['DamagedWells'] = $dataBreakUpRow['DamagedWells'];

            $ArrayBreakUpData['DeleteAccess'] = $this->getDeleteAccess($dataBreakUpRow['KitID'], $dataBreakUpRow['KitBreakupID']);
            $ArrayBreakUpData['EditURL'] = 'qc-kits-create?KitBreakupID=' . $dataBreakUpRow['KitBreakupID'];
            $ArrayBreakUpData['ConfirmURL'] = 'qc-kits-create?KitBreakupID=' . $dataBreakUpRow['KitBreakupID'] . '&BreakUpData=BreakUpData';

            $ArrayBreakUpDataList[] = $ArrayBreakUpData;
        }
        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['Data'] = $ArrayBreakUpDataList;

        return $Data;
    }

    public function getDeleteAccess($KitID, $KitBreakupID)
    {
        $DeleteAccess = 1;
        $Query = '';
        if ($KitBreakupID != '') {
            $Query = "and KitBreakUpID='$KitBreakupID'";
        }
        $sqlRowCount = "SELECT count(*) as RowCount FROM QcRmAbUtilizedKitBreakupDetails WHERE KitID='$KitID' $Query";
        $stmtRowCount = $this->conn->prepare($sqlRowCount);
        $stmtRowCount->execute();
        $dataBreakUpRow = $stmtRowCount->fetch(PDO::FETCH_ASSOC);
        if ($dataBreakUpRow['RowCount'] == 1) {
            $DeleteAccess = 0;
        }

        return $DeleteAccess;
    }

    public function deleteQcKitsDetails($RequestBody)
    {
        $KitID = $RequestBody['KitID'];
        $BreakUpData = $RequestBody['BreakUpData'];
        $KitBreakUpID = $RequestBody['KitBreakUpID'];
        if ($BreakUpData == 1) {
            $existingBreakup = $this->auditLogger->fetchRowById('QcKitsBreakupDetails', 'KitBreakupID', $KitBreakUpID);
            $sqlBreakupDelete = " DELETE FROM QcKitsBreakupDetails WHERE KitID='$KitID' and KitBreakupID='$KitBreakUpID'";
            $stmtBreakupDelete = $this->conn->prepare($sqlBreakupDelete);
            $stmtBreakupDelete->execute();
            if ($stmtBreakupDelete) {
                $this->auditLogger->logDelete('QcKitsBreakupDetails', $KitBreakUpID, $existingBreakup, $RequestBody, [
                    'ReferenceNo' => $KitID,
                    'Remarks' => 'QC kits breakup deleted',
                ]);
                $Data['Error'] = null;
                $Data['Msg'] = 'Breakup deleted  successfully';
            }

        } else {
            $existingKit = $this->auditLogger->fetchRowById('QcKitsDetails', 'KitID', $KitID);
            $sqlDelete = "DELETE FROM QcKitsDetails WHERE KitID='$KitID'";
            $stmtDelete = $this->conn->prepare($sqlDelete);
            $stmtDelete->execute();

            if ($stmtDelete) {
                $this->auditLogger->logDelete('QcKitsDetails', $KitID, $existingKit, $RequestBody, [
                    'ReferenceNo' => $KitID,
                    'Remarks' => 'QC kits header deleted',
                ]);
                $sqlBreakupDelete = " DELETE FROM QcKitsBreakupDetails WHERE KitID='$KitID'";
                $stmtBreakupDelete = $this->conn->prepare($sqlBreakupDelete);
                $stmtBreakupDelete->execute();

                $Data['Error'] = null;
                $Data['Msg'] = 'Deleted Successfully';

            }
        }
        return $Data;
    }
}
