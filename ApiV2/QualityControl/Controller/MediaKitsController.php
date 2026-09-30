<?php

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class MediaKitsController
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
         $this->utilController = new UtilController($this->conn);
         $this->auditLogger = new CentralAuditLogger($this->conn, 'QualityControl');
    }
    public function postmediakitsdetails($RequestBody)
    {


        // echo print_r($RequestBody);
        // exit;
        $KitID = $RequestBody['KitID'];
        $Parameter = $RequestBody['Parameter'];
        $KitsOrderedDate = $RequestBody['KitsOrderedDate'];
        $KitsReceivedDate = $RequestBody['KitsReceivedDate'];
        $KitsLotNo = $RequestBody['KitsLotNo']; // Directly use this KitLotNo
        $NoOfKitsOrdered = $RequestBody['NoOfKitsOrdered'];
        $NoOfKitsReceived = $RequestBody['NoOfKitsReceived'];
        $media = $RequestBody['media'];
        $InvoiceNo = $RequestBody['InvoiceNo'];
        $ManufactureDate = $RequestBody['ManufactureDate'];
        $ExpiryDate = $RequestBody['ExpiryDate'];
        $BottleWeight = $RequestBody['BottleWeight'];
        date_default_timezone_set("Asia/Kolkata");
        $PostedDateAndTime = date("Y-m-d H:i:s");
        $PostedUserID = 1;
        $existingKit = null;
        if ($KitID != '') {
            $existingKit = $this->auditLogger->fetchRowById('mediakitsdetails', 'mKitID', $KitID);
        }

        if ($KitID == '') {
            // Directly insert the new record with KitsLotNo without checking duplicates

            $media = explode(",", $media);

            foreach ($media as $mk => $mid) {
                $sqlInsert = "INSERT INTO mediakitsdetails (
                    mKitsOrderedDate, 
                    mKitsReceivedDate, 
                    mKitsLotNo,
                    mNoOfKitsOrdered, 
                    mNoOfKitsReceived, 
                    mInvoiceNo, 
                    mManufactureDate, 
                    mExpiryDate, 
                    mBottleWeight,
                    mParameter,
                    mMedia,                    
                    mPostedUserID, 
                    mPostedDateAndTime 
                ) VALUES (
                    '$KitsOrderedDate', 
                    '$KitsReceivedDate', 
                    '$KitsLotNo', 
                    '$NoOfKitsOrdered',
                    '$NoOfKitsReceived', 
                    '$InvoiceNo', 
                    '$ManufactureDate', 
                    '$ExpiryDate',
                     '$BottleWeight', 
                    '$Parameter',
                    '$mid',                    
                    '$PostedUserID', 
                    '$PostedDateAndTime'
                )";
                //echo $sqlInsert; exit;
                $stmtInsert = $this->conn->prepare($sqlInsert);
                $stmtInsert->execute();

                if ($stmtInsert) {
                    $this->Error = null;
                    $this->Msg = 'Kits Posted Successfully';
                    $KitID = $this->conn->lastInsertId();
                    $createdKit = $this->auditLogger->fetchRowById('mediakitsdetails', 'mKitID', $KitID);
                    $this->auditLogger->logInsert('mediakitsdetails', $KitID, $createdKit, $RequestBody, [
                        'ReferenceNo' => $KitsLotNo,
                        'Remarks' => 'QC media kits header created',
                    ]);
                    if ($NoOfKitsReceived >= 0) {
                        $this->postKitsBreakUpData($RequestBody, $KitID);
                    }
                } else {
                    $this->Error = "Unable to Post Kits Data";
                    $this->Msg = "Unable to Post Kits Data";
                }
            }
        } else {
            $SqlBreakupCount = "SELECT count(*) as Rowcount FROM `mediakitsbreakupdetails` WHERE mKitID='$KitID'  ORDER BY `mKitBreakupID` DESC";

            $stmtBreakupCount = $this->conn->prepare($SqlBreakupCount);
            $stmtBreakupCount->execute();
            $dataRowBreakupCount = $stmtBreakupCount->fetch(PDO::FETCH_ASSOC);
            $RowBreakupCount = $dataRowBreakupCount['Rowcount'];

            // Update existing record logic
            $sqlUpdate = "UPDATE mediakitsdetails SET 
            mKitsOrderedDate='$KitsOrderedDate', 
            mKitsReceivedDate='$KitsReceivedDate', 
            mKitsLotNo='$KitsLotNo', 
            mParameter='$Parameter', 
            mmedia='$media',
            mNoOfKitsOrdered='$NoOfKitsOrdered', 
            mNoOfKitsReceived='$NoOfKitsReceived', 
            mInvoiceNo='$InvoiceNo', 
            mManufactureDate='$ManufactureDate', 
            mExpiryDate='$ExpiryDate', 
            mBottleWeight='$BottleWeight',
            mPostedUserID='$PostedUserID', 
            mPostedDateAndTime='$PostedDateAndTime' 
            WHERE mKitID='$KitID'";

            $stmtUpdate = $this->conn->prepare($sqlUpdate);
            $stmtUpdate->execute();

            // echo print_r($sqlUpdate);
            // exit;

            if ($stmtUpdate) {
                if ($RowBreakupCount == 0) {
                    $this->postKitsBreakUpData($RequestBody, $KitID);
                }
                $this->Error = null;
                $this->Msg = 'Kits Updated Successfully';
                $updatedKit = $this->auditLogger->fetchRowById('mediakitsdetails', 'mKitID', $KitID);
                $this->auditLogger->logUpdate('mediakitsdetails', $KitID, $existingKit, $updatedKit, $RequestBody, [
                    'ReferenceNo' => $KitsLotNo,
                    'Remarks' => 'QC media kits header updated',
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
        $sqlFetchMax = "SELECT max(mKitBreakUpAutoIncrementNumber) as mKitBreakUpAutoIncrementNumber FROM `mediakitsdetails` as QKD 
        left join mediakitsbreakupdetails as QKBD on QKBD.mKitID=QKD.mKitID WHERE mKitID='$KitID'";
        $stmtFetchMax = $this->conn->prepare($sqlFetchMax);
        $stmtFetchMax->execute();
        $result = $stmtFetchMax->fetch(PDO::FETCH_ASSOC);

        // Start incrementing from the next number
        $startNumber = $result['mKitBreakUpAutoIncrementNumber'];
        // Get required details from the request
        $NoOfKitsReceived = $RequestBody['NoOfKitsReceived'];
        $TotalWells = $RequestBody['TotalWells'];
        $mBalanceWells = $RequestBody['mBalanceWells'];
        $BottleWeight = $RequestBody['BottleWeight'];
        //$NoOfWells = $TotalWells / $NoOfKitsReceived;
        $NoOfWells = 500; // this was static as depika said.
        // Insert new KitBreakupLotNo values
        for ($i = 1; $i <= $NoOfKitsReceived; $i++) {
            $incrementedLotNo = $originalLotNo . '-' . ($startNumber + $i);
            $KitBreakUpAutoIncrementNumber = $startNumber + $i;


            $sqlInsertBreakUp = "INSERT INTO mediakitsbreakupdetails (mKitID,mKitBreakUpAutoIncrementNumber, mKitBreakupLotNo, mNoOfWells, mBalanceWells)
                                 VALUES ('$KitID','$KitBreakUpAutoIncrementNumber', '$incrementedLotNo', '$BottleWeight', '$BottleWeight')";
            $stmtInsertBreakUp = $this->conn->prepare($sqlInsertBreakUp);
            $stmtInsertBreakUp->execute();
            if ($stmtInsertBreakUp) {
                $breakupId = $this->conn->lastInsertId();
                $createdBreakup = $this->auditLogger->fetchRowById('mediakitsbreakupdetails', 'mKitBreakupID', $breakupId);
                $this->auditLogger->logInsert('mediakitsbreakupdetails', $breakupId, $createdBreakup, $RequestBody, [
                    'ReferenceNo' => $KitID,
                    'Remarks' => 'QC media kits breakup created',
                ]);
            }
        }

        return true;
    }


    public function getmediakitsdetails($RequestBody)
    {

        $BreakUpData = $RequestBody['BreakUpData'];
        $KitID = $RequestBody['KitID'];

        if ($BreakUpData == "1") {
            $KitsData = $this->getBreakUpDetails($KitID);
        } else {
            $KitsData = $this->getMediaKitsData($RequestBody);
        }


        return $KitsData;
    }
    public function getMediaKitsData($RequestBody)
    {

        $KitID = $RequestBody['KitID'];
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $PageNo = $RequestBody['PageNo'];
        $SearchKey = $RequestBody['SearchKey'];
        $LoadAll = $RequestBody['LoadAll'];

        $Query = '';

        $DateFilters = "";
        if (isset($FromDate) && $FromDate) {
            $DateFilters = " BETWEEN '$FromDate' AND '$ToDate'";
        }

        // $RecordsPerPage = 10;
        // $LimitMax = ($PageNo) * $RecordsPerPage;
        // $Limit = 'LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;



        if ($LoadAll == 1) {
            $Limit = '';
        }

        if ($SearchKey != '') {
            $Query .= " and CONCAT_WS('', mKitID, mKitsOrderedDate, mKitsReceivedDate, mKitsLotNo, mNoOfKitsOrdered,mNoOfKitsReceived,
            mInvoiceNo,mManufactureDate,mExpiryDate,mBottleWeight,mMedia) LIKE '%$SearchKey%'";
        }

        $totalRecords_q = $this->conn->prepare("SELECT COUNT(*) as count FROM mediakitsdetails");
        $totalRecords_q->execute();
        $totalRecords_d = $totalRecords_q->fetch(PDO::FETCH_ASSOC);
        $totalRecords = $totalRecords_d['count'];
        $totalFiltered_q = $this->conn->query("SELECT COUNT(*) as count FROM mediakitsdetails WHERE mKitsOrderedDate $DateFilters $Query ");
        $totalFiltered_q->execute();
        $totalFiltered_d = $totalFiltered_q->fetch(PDO::FETCH_ASSOC);
        $totalFiltered = $totalFiltered_d['count'];
        $sqlCount = "SELECT * FROM mediakitsdetails WHERE mKitsOrderedDate $DateFilters $Query ";
        $stmt = $this->conn->prepare($sqlCount);
        $stmt->execute();

        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);

        $TotalCount = $totalRecords;
        $PageCount = $totalFiltered;

        // $sqlFields = "`mKitID`, `mKitsOrderedDate`, `mKitsReceivedDate`, `mKitsLotNo`, `mTotalWells`, `mParameter`, `mMedia`, `mNoOfKitsOrdered`, `mNoOfKitsReceived`, `mInvoiceNo`, `mManufactureDate`, `mExpiryDate`,mBottleWeight, `mPostedUserID`, `mPostedDateAndTime`";

        // if ($KitID != '') {
        //     $sqlQuery = "SELECT $sqlFields FROM mediakitsdetails WHERE mKitID='$KitID'";
        // } else {
        //     $sqlQuery = "SELECT $sqlFields FROM mediakitsdetails 
        //     WHERE mKitsOrderedDate $DateFilters $Query order by mKitsOrderedDate asc " . $Limit;
        // }

        $sqlFields = "mk.mKitID, mk.mKitsOrderedDate, mk.mKitsReceivedDate, mk.mKitsLotNo, 
              mk.mTotalWells, mk.mParameter, mk.mMedia, um.MediaName,
              mk.mNoOfKitsOrdered, mk.mNoOfKitsReceived, mk.mInvoiceNo, 
              mk.mManufactureDate, mk.mExpiryDate, mk.mBottleWeight, 
              mk.mPostedUserID, mk.mPostedDateAndTime";

         if ($KitID != '') {
        $sqlQuery = "SELECT $sqlFields
                        FROM mediakitsdetails mk
                        LEFT JOIN usedmediamaster um ON um.MediaID = mk.mMedia
                        WHERE mk.mKitID='$KitID'";
        } else {
            $sqlQuery = "SELECT $sqlFields
                        FROM mediakitsdetails mk
                        LEFT JOIN usedmediamaster um ON um.MediaID = mk.mMedia
                        WHERE mk.mKitsOrderedDate $DateFilters $Query
                        ORDER BY mk.mKitsOrderedDate ASC " . $Limit;
        }

        error_log("meddia qury " . $sqlQuery);
        //echo $sqlQuery;

        //exit;
        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        $ArrayMediaKitsData = [];

        $i = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayMediaKitsList['SNO'] = ++$i;
            $ArrayMediaKitsList['mKitID'] = $dataRow['mKitID'];
            $ArrayMediaKitsList['mParameter'] = $dataRow['mParameter'];
            $ArrayMediaKitsList['mMedia'] = $dataRow['mMedia'];
            $ArrayMediaKitsList['mKitsOrderedDate'] = $this->utilController->getFormatedDate($dataRow['mKitsOrderedDate']);
            $ArrayMediaKitsList['mKitsReceivedDate'] = $this->utilController->getFormatedDate($dataRow['mKitsReceivedDate']);
            $ArrayMediaKitsList['mKitsLotNo'] = $dataRow['mKitsLotNo'];
            $ArrayMediaKitsList['mNoOfKitsOrdered'] = $dataRow['mNoOfKitsOrdered'];
            $ArrayMediaKitsList['mNoOfKitsReceived'] = $dataRow['mNoOfKitsReceived'];
            $ArrayMediaKitsList['mInvoiceNo'] = $dataRow['mInvoiceNo'];
            $ArrayMediaKitsList['mManufactureDate'] = $this->utilController->getFormatedDate($dataRow['mManufactureDate']);
            $ArrayMediaKitsList['mExpiryDate'] = $this->utilController->getFormatedDate($dataRow['mExpiryDate']);
            $ArrayMediaKitsList['mBottleWeight'] = $dataRow['mBottleWeight'];
            $ArrayMediaKitsList['mTotalWells'] = $dataRow['mTotalWells'];

            $ArrayMediaKitsList['EditURL'] = 'media-kits-create?KitID=' . $dataRow['mKitID'];
            $ArrayMediaKitsList['ConfirmURL'] = 'media-kits-create?KitID=' . $dataRow['mKitID'] . '&BreakUpData=1';
            $ArrayMediaKitsList['DeleteAccess'] = $this->getDeleteAccess($dataRow['mKitID']);
            $ArrayMediaKitsList['MediaName'] = $dataRow['MediaName'];

            $ArrayMediaKitsData[] = $ArrayMediaKitsList;
        }

        if (count($ArrayMediaKitsData) < $RecordsPerPage) {
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
        $data['Data'] = $ArrayMediaKitsData;

        return $data;
    }

    public function getBreakUpDetails($KitID)
    {
        $sqlBreakUpFields = "mKitBreakupID, QKBD.mKitID, mKitBreakupLotNo, mNoOfWells,mBalanceWells,mUtilizedWells,BottleWeight ";

        $sqlBreakUp = "SELECT $sqlBreakUpFields, QKD.mKitsOrderedDate ,QKD.mKitsReceivedDate, QKD.mKitsLotNo , 
        FROM mediakitsbreakupdetails AS QKBD
        LEFT JOIN mediakitsdetails AS QKD ON QKBD.mKitID = QKD.mKitID
       
        WHERE QKBD.mKitID = '$KitID'";
        // LEFT JOIN MediaRmAbReport AS QRAR ON QKBD.KitID = QRAR.KitID
        //QRAR.UsedWells

        $stmtBreakUp = $this->conn->prepare($sqlBreakUp);
        $stmtBreakUp->execute();

        $ArrayBreakUpDataList = [];

        $i = 0;
        while ($dataBreakUpRow = $stmtBreakUp->fetch(PDO::FETCH_ASSOC)) {
            $ArrayBreakUpData['SNO'] = ++$i;
            $ArrayBreakUpData['KitBreakupID'] = $dataBreakUpRow['mKitBreakupID'];
            $ArrayBreakUpData['KitID'] = $dataBreakUpRow['mKitID'];
            $ArrayBreakUpData['KitBreakupLotNo'] = $dataBreakUpRow['mKitBreakupLotNo'];
            // $ArrayBreakUpData['NoOfWells'] = $dataBreakUpRow['mNoOfWells'];
            $ArrayBreakUpData['KitsOrderedDate'] = $this->utilController->getFormatedDate($dataBreakUpRow['mKitsOrderedDate']);
            $ArrayBreakUpData['KitsReceivedDate'] = $this->utilController->getFormatedDate($dataBreakUpRow['mKitsReceivedDate']);
            $ArrayBreakUpData['KitsLotNo'] = $dataBreakUpRow['mKitsLotNo'];
            $ArrayBreakUpData['UtilizedWells'] = $dataBreakUpRow['mUtilizedWells'];
            $ArrayBreakUpData['BalanceWells'] = $dataBreakUpRow['mBalanceWells'];
            $ArrayBreakUpData['BottleWeight'] = $dataBreakUpRow['BottleWeight'];


            $ArrayBreakUpData['DeleteAccess'] = $this->getDeleteAccess($dataBreakUpRow['mKitID'], $dataBreakUpRow['mKitBreakupID']);
            $ArrayBreakUpData['EditURL'] = 'media-kits-create?KitBreakupID=' . $dataBreakUpRow['mKitBreakupID'];
            $ArrayBreakUpData['ConfirmURL'] = 'media-kits-create?KitBreakupID=' . $dataBreakUpRow['mKitBreakupID'] . '&BreakUpData=BreakUpData';

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
        $sqlRowCount = "SELECT count(*) as RowCount FROM mediarmihmputilizedkitbreakupdetails WHERE KitID='$KitID' $Query";
        $stmtRowCount = $this->conn->prepare($sqlRowCount);
        $stmtRowCount->execute();
        $dataBreakUpRow = $stmtRowCount->fetch(PDO::FETCH_ASSOC);
        if ($dataBreakUpRow['RowCount'] == 1) {
            $DeleteAccess = 0;
        }

        return $DeleteAccess;
    }

    public function deletemediakitsdetails($RequestBody)
    {
        $KitID = $RequestBody['KitID'];
        $BreakUpData = $RequestBody['BreakUpData'];
        $KitBreakUpID = $RequestBody['KitBreakUpID'];
        if ($BreakUpData == 1) {
            $existingBreakup = $this->auditLogger->fetchRowById('mediakitsbreakupdetails', 'mKitBreakupID', $KitBreakUpID);
            $sqlBreakupDelete = " DELETE FROM mediakitsbreakupdetails WHERE mKitID='$KitID' and mKitBreakupID='$KitBreakUpID'";
            $stmtBreakupDelete = $this->conn->prepare($sqlBreakupDelete);
            $stmtBreakupDelete->execute();
            if ($stmtBreakupDelete) {
                $this->auditLogger->logDelete('mediakitsbreakupdetails', $KitBreakUpID, $existingBreakup, $RequestBody, [
                    'ReferenceNo' => $KitID,
                    'Remarks' => 'QC media kits breakup deleted',
                ]);
                $Data['Error'] = null;
                $Data['Msg'] = 'Breakup deleted  successfully';
            }

        } else {
            $existingKit = $this->auditLogger->fetchRowById('mediakitsdetails', 'mKitID', $KitID);
            $sqlDelete = "DELETE FROM mediakitsdetails WHERE mKitID='$KitID'";
            $stmtDelete = $this->conn->prepare($sqlDelete);
            $stmtDelete->execute();

            if ($stmtDelete) {
                $this->auditLogger->logDelete('mediakitsdetails', $KitID, $existingKit, $RequestBody, [
                    'ReferenceNo' => $KitID,
                    'Remarks' => 'QC media kits header deleted',
                ]);
                $sqlBreakupDelete = " DELETE FROM mediakitsbreakupdetails WHERE mKitID='$KitID'";
                $stmtBreakupDelete = $this->conn->prepare($sqlBreakupDelete);
                $stmtBreakupDelete->execute();

                $Data['Error'] = null;
                $Data['Msg'] = 'Deleted Successfully';

            }
        }
        return $Data;
    }
}
