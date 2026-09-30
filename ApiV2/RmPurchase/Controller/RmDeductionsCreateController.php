<?php
include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once __DIR__ . '/AuditLogger.php';
class RmDeductionsCreateController
{
    private $conn;  
    private $utilController;
    private $auditLogger;
    private $Error = null;
    private $Msg = '';

    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
        $this->auditLogger = new AuditLogger($this->conn, 'Purchase');
    }
    public function getRmDeductions($RequestBody)
    {
        $RateID = $RequestBody['RateID'];
        $ArrayDeductionData = $this->getRmDeductionDetails($RateID);

        $Message = 'Success';
        $data['status'] = $this->geterrorMessages($Message);
        $data['data'] = $ArrayDeductionData;
        return $data;
    }
    function getRmDeductionDetails($RateID)
    {
        $SqlFieldsRmDeductionData = " DeductionRateID as RateID,`PurchaseCategoryID` as PurchaseType, `Species`, `CenterID`, `EffectedDate`,
        PurchaseType as PurchaseCategoryName";

        $sqlRmDeductionData = "SELECT $SqlFieldsRmDeductionData FROM `RmDeductionRates` as RAD 
       left join RmPurchaseCategory as RPC on RPC.CatID= RAD.PurchaseCategoryID 
       WHERE DeductionRateID='$RateID'"; 
        $stmtRmDeductionData = $this->conn->prepare($sqlRmDeductionData);
        $stmtRmDeductionData->execute();
        $dataRowDeductionData = $stmtRmDeductionData->fetch(PDO::FETCH_ASSOC);
       

        $SqlFieldsRmDeductionBreakupData = "DeductionBreakupID,`StartCount`, `EndCount`,  `StartWeight`, `EndWeight`, `Extra`";

        $sqlRmDeductionBreakupData = "SELECT $SqlFieldsRmDeductionBreakupData FROM  RmDeductionRatesBreakup  WHERE DeductionRateID='$RateID'";
        $stmtRmDeductionDataBreakupData = $this->conn->prepare($sqlRmDeductionBreakupData);
        $stmtRmDeductionDataBreakupData->execute();
        $SNo=0;
        while ($dataRowDeductionBreakupData = $stmtRmDeductionDataBreakupData->fetch(PDO::FETCH_ASSOC)) {
            $dataRowDeductionBreakupData['SNo']=++$SNo;            
            $dataRowDeductionBreakupData['PurchaseCategoryName']=$dataRowDeductionData['PurchaseCategoryName'];          
            $dataRowDeductionBreakupData['EffectedDateinDMYFormat']=$this->utilController->getFormatedDate($dataRowDeductionData['EffectedDate']);          
            $ArrayData[] = $dataRowDeductionBreakupData;
        }
        $ArrayDeductionData['rmDeductionData'] = $dataRowDeductionData;
        $ArrayDeductionData['rmDeductionBreakUpData'] = $ArrayData;
        return $ArrayDeductionData;

    }
    public function postRmDeductionRates($RequestBody)
    {
        $RateID = $RequestBody['RateID'];
        $PurchaseType = $RequestBody['PurchaseType'];
        $CenterId = $RequestBody['CenterId'];
        $Species = $RequestBody['Species'];
        $EffectedDate = $RequestBody['EffectedDate'];

          $sqlRmDedutionRateRowcount = "SELECT count(*) as RowCount,DeductionRateID FROM `RmDeductionRates` WHERE PurchaseCategoryID='$PurchaseType' and Species='$Species' and 
        CenterID IN ($CenterId) and EffectedDate='$EffectedDate'";  
        $stmtRmDedutionRateRowcount = $this->conn->prepare($sqlRmDedutionRateRowcount);
        $stmtRmDedutionRateRowcount->execute();
        $dataRowDedutionRateRowcount = $stmtRmDedutionRateRowcount->fetch(PDO::FETCH_ASSOC);
        $RowCount = $dataRowDedutionRateRowcount['RowCount'];
        $DeductionRateID = $dataRowDedutionRateRowcount['DeductionRateID'];

        if ($RowCount == 0 && $RateID == '') {
            $sqlInsertRmDeduction = "INSERT INTO `RmDeductionRates`(`PurchaseCategoryID`, `Species`, `CenterID`, `EffectedDate`) 
        VALUES ('$PurchaseType','$Species','$CenterId','$EffectedDate')"; 
            $stmtInsertRmDeduction = $this->conn->prepare($sqlInsertRmDeduction);
            $stmtInsertRmDeduction->execute();
            $RateID = $this->conn->lastInsertId();
            if ($stmtInsertRmDeduction) {
                $newRate = $this->auditLogger->fetchRowById('RmDeductionRates', 'DeductionRateID', $RateID);
                $this->auditLogger->logInsert('RmDeductionRates', $RateID, $newRate, $RequestBody, [
                    'ReferenceNo' => $RateID,
                    'Remarks' => 'Deduction rate created',
                ]);
            }
        }
       
        else if ($DeductionRateID == $RateID && $RateID != '') {
            $existingRate = $this->auditLogger->fetchRowById('RmDeductionRates', 'DeductionRateID', $RateID);

            $sqlInsertRmDeduction = "UPDATE `RmDeductionRates` SET `PurchaseCategoryID`='$PurchaseType',
            `Species`='$Species',`CenterID`='$CenterId',`EffectedDate`='$EffectedDate' WHERE `DeductionRateID`='$RateID'";
            $stmtInsertRmDeduction = $this->conn->prepare($sqlInsertRmDeduction);
            $stmtInsertRmDeduction->execute();
            if ($stmtInsertRmDeduction) {
                $updatedRate = $this->auditLogger->fetchRowById('RmDeductionRates', 'DeductionRateID', $RateID);
                $this->auditLogger->logUpdate('RmDeductionRates', $RateID, $existingRate, $updatedRate, $RequestBody, [
                    'ReferenceNo' => $RateID,
                    'Remarks' => 'Deduction rate updated',
                ]);
            }
        }

        if ($stmtInsertRmDeduction) {
            $BreakUp = $this->postDeductionRateBreakUp($RateID, $RequestBody);
            $Message = $BreakUp['Msg'];      
        } else {
            $Message = 'Data Already Exists';
            $this->geterrorMessages($Message);
            $RateID=$DeductionRateID;
        }
        $ArrayDeductionData = $this->getRmDeductionDetails($RateID);
        $data['status'] = $this->geterrorMessages($Message);
        $data['data'] = $ArrayDeductionData;
        return $data;

    }
     
    function postDeductionRateBreakUp($RateID, $RequestBody)
    {
        $RateBreakUpID = $RequestBody['RateBreakUpID'];
        $StartCount = $RequestBody['StartCount'];
        $EndCount = $RequestBody['EndCount'];
        $StartWeight = $RequestBody['StartWeight'];
        $EndWeight = $RequestBody['EndWeight'];
        $Extra = $RequestBody['Extra'];

        $sqlRmDedutionRateBreakupRowcount = "SELECT count(*) as RowCount FROM `RmDeductionRatesBreakup` WHERE StartCount='$StartCount' 
        and EndCount='$EndCount' and   StartWeight='$StartWeight' and DeductionRateID='$RateID'";
        $stmtRmDedutionRateBreakupRowcount = $this->conn->prepare($sqlRmDedutionRateBreakupRowcount);
        $stmtRmDedutionRateBreakupRowcount->execute();
        $dataRowDedutionRateBreakupRowcount = $stmtRmDedutionRateBreakupRowcount->fetch(PDO::FETCH_ASSOC);
        $RowCount = $dataRowDedutionRateBreakupRowcount['RowCount'];

        if ($RowCount == 0 && $RateBreakUpID == '') {
            $sqlInsertRmDeductionBreakup = "INSERT INTO `RmDeductionRatesBreakup`( `DeductionRateID`, `StartCount`, `EndCount`, 
        `StartWeight`, `EndWeight`, `Extra`) VALUES('$RateID','$StartCount','$EndCount','$StartWeight','$EndWeight','$Extra')";
            $stmtInsertRmDeductionBreakup = $this->conn->prepare($sqlInsertRmDeductionBreakup);
            $stmtInsertRmDeductionBreakup->execute();
            if ($stmtInsertRmDeductionBreakup) {
                $newBreakupId = $this->conn->lastInsertId();
                $newBreakup = $this->auditLogger->fetchRowById('RmDeductionRatesBreakup', 'DeductionBreakupID', $newBreakupId);
                $this->auditLogger->logInsert('RmDeductionRatesBreakup', $newBreakupId, $newBreakup, $RequestBody, [
                    'ReferenceNo' => $RateID,
                    'Remarks' => 'Deduction rate breakup created',
                ]);
            }
        }

        else if ($RateBreakUpID != '') {
            $existingBreakup = $this->auditLogger->fetchRowById('RmDeductionRatesBreakup', 'DeductionBreakupID', $RateBreakUpID);
            $sqlInsertRmDeductionBreakup = "UPDATE `RmDeductionRatesBreakup` SET `DeductionRateID`='$RateID',
            `StartCount`='$StartCount',`EndCount`='$EndCount', `StartWeight`='$StartWeight',`EndWeight`='$EndWeight',`Extra`='$Extra'
             WHERE `DeductionBreakupID`='$RateBreakUpID'";
            $stmtInsertRmDeductionBreakup = $this->conn->prepare($sqlInsertRmDeductionBreakup);
            $stmtInsertRmDeductionBreakup->execute();
            if ($stmtInsertRmDeductionBreakup) {
                $updatedBreakup = $this->auditLogger->fetchRowById('RmDeductionRatesBreakup', 'DeductionBreakupID', $RateBreakUpID);
                $this->auditLogger->logUpdate('RmDeductionRatesBreakup', $RateBreakUpID, $existingBreakup, $updatedBreakup, $RequestBody, [
                    'ReferenceNo' => $RateID,
                    'Remarks' => 'Deduction rate breakup updated',
                ]);
            }
        }

        if ($stmtInsertRmDeductionBreakup) {
            $Message = 'Success';
            $this->geterrorMessages($Message);
        } else {
            $Message = 'Data Already Exists';
            $this->geterrorMessages($Message);
        }
        return $this->geterrorMessages($Message);

    }

    function geterrorMessages($Message)
    {
        $data['Error'] = null;
        $data['Msg'] = $Message;
        return $data;
    }
}
