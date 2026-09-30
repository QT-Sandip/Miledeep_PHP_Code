<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class ValueAdditionHonTransferController
{
    private $conn;
    //	private $db_table = "ScrmApplicants";
    private $utilController;
    private $Error = null;
    private $Msg = '';
    private $auditLogger;


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->auditLogger = new CentralAuditLogger($this->conn, 'PreProcessing');
        // $this->utilController = new UtilController($this->conn);
    }

    public function getArrivalDetails($RequestBody)
    {
        $TrackID = $RequestBody['TrackID'];
        $ArrivalID = $RequestBody['ArrivalID'];
        $Count = $RequestBody['Count'];
        $LotID = $RequestBody['LotID'];
        $BillDetails = $this->getArrivalBillDetails($TrackID);
        $HonCountDetails = $this->getCountsDetails($ArrivalID);
        $VaTransferData = $this->getVaTransferDetails($TrackID, $ArrivalID);
        $VaWeight = $this->getBalanceQty($TrackID, $ArrivalID, $Count, $LotID);

        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['BillNumbers'] = $BillDetails;
        $Data['HonCount'] = $HonCountDetails;
        $Data['VaTransferData'] = $VaTransferData;
        $Data['VaWeight'] = $VaWeight;

        return $Data;
    }

    function getArrivalBillDetails($TrackID)
    {
        $sqlFields = "RLD.RmLotID,PHGD.MixIDs,PHGD.IsMix";
        $sqlMixIDs = "SELECT $sqlFields FROM RmReceivingBreakup AS RRB
        LEFT JOIN PpcHLGradingDetails  AS PHGD ON PHGD.TrackBreakUpID=RRB.ReceivalBreakupID
        LEFT JOIN RmLotDetatils  AS RLD  ON RLD.RmLotID=PHGD.LotID
        WHERE ReceivingID='$TrackID' AND RmLotID IS NOT NULL GROUP BY MixIDs";

        $stmtMixIDs = $this->conn->prepare($sqlMixIDs);
        $stmtMixIDs->execute();

        $ArrayBillsData = [];
        while ($rowMixIDs = $stmtMixIDs->fetch(PDO::FETCH_ASSOC)) {
            $IsMix = $rowMixIDs['IsMix'];
            $LotID = $rowMixIDs['RmLotID'];
            $MixIDs = $rowMixIDs['MixIDs'];

            if ($IsMix == 1) {
                $GroupQuery = " AND RHGD.MixIDs IN ($MixIDs) GROUP BY MixIDs";
                $BillNumberField = "ArrivalNumber, RAD.RmArrivalID ,GROUP_CONCAT(DISTINCT RmArrivalID) as RmArrivalIDs,
                GROUP_CONCAT(DISTINCT ArrivalNumber SEPARATOR ', ') as BillNumber,GROUP_CONCAT(DISTINCT RmLotID) as RmLotIDs,
                GROUP_CONCAT(DISTINCT PurchaseCount SEPARATOR ', ') AS PurchaseCount";
            } else {
                $BillNumberField = " ArrivalNumber, RAD.RmArrivalID ,ReceivalBreakupID,RmLotID, PurchaseCount";
                $GroupQuery = " AND RLD.RmLotID IN ($LotID) GROUP BY RAD.RmArrivalID";
            }

            $sqlBillNumbers = "SELECT $BillNumberField FROM RmArrivalBreakUpDetails AS RABD 
            LEFT JOIN RmArrivalDetatils AS RAD ON RAD.RmArrivalID=RABD.ArrivalID 
            LEFT JOIN RmReceivingBreakup AS RRB ON RRB.ArrivalBreakupID=RABD.ArrivalBrkUpID 
            LEFT JOIN RmLotDetatils AS RLD ON RLD.BreakUpID=RRB.ArrivalBreakupID 
            LEFT JOIN PpcHLGradingDetails AS RHGD ON RHGD.LotID=RLD.RmLotID
            WHERE RRB.ReceivingID= '$TrackID' AND ReceivalBreakupID IS NOT NULL $GroupQuery ";
            $stmtBillNumbers = $this->conn->prepare($sqlBillNumbers);
            $stmtBillNumbers->execute();

            $rowBillNumber = $stmtBillNumbers->fetch(PDO::FETCH_ASSOC);
            if ($IsMix == 1) {
                $ArrayBillsList['BillNumber'] = $rowBillNumber['BillNumber'] . ' Mix' . ' (' . $rowBillNumber['PurchaseCount'] . ')';
                $ArrayBillsList['LotIDs'] = $rowBillNumber['RmLotIDs'];
                $ArrayBillsList['PurchaseCount'] = $rowBillNumber['PurchaseCount'];
                $ArrayBillsList['MixStatus'] = 'Mixed';
            } else {
                $ArrayBillsList['BillNumber'] = $rowBillNumber['ArrivalNumber'] . '(' . $rowBillNumber['PurchaseCount'] . ' C )';
                $ArrayBillsList['LotIDs'] = $rowBillNumber['RmLotID'];
                $ArrayBillsList['PurchaseCount'] = $rowBillNumber['PurchaseCount'];
                $ArrayBillsList['MixStatus'] = 'NonMixed';
            }
            $ArrayBillsData[] = $ArrayBillsList;
        }
        return $ArrayBillsData;
    }

    public function getCountsDetails($ArrivalID)
    {
        $sqlCountFields = "PurchaseCount AS Count, PurchaseWeight AS Weight,Rate, LotNumber, RmLotID";

        $sqlCounts = "SELECT $sqlCountFields FROM RmLotDetatils AS RLD
        LEFT JOIN RmArrivalBreakUpDetails AS RABD ON RLD.BreakUpID = RABD.ArrivalBrkUpID
        WHERE RmLotID IN ($ArrivalID) ORDER BY PurchaseCount";
        $stmtCounts = $this->conn->prepare($sqlCounts);
        $stmtCounts->execute();
        $ArrayCountsData = [];
        while ($rowCounts = $stmtCounts->fetch(PDO::FETCH_ASSOC)) {
            $ArrayCountsList['HonCount'] = $rowCounts['Count'];
            $ArrayCountsList['HonWeight'] = $rowCounts['Weight'];
            $ArrayCountsList['HonRate'] = $rowCounts['Rate'];
            $ArrayCountsList['LotNumber'] = $rowCounts['LotNumber'];
            $ArrayCountsList['RmLotID'] = $rowCounts['RmLotID'];
            $ArrayCountsList['VaWeight'] = $rowCounts['VaWeight'];
            $ArrayCountsList['BalanceWeight'] = $rowCounts['Weight'] - $rowCounts['VaWeight'];
            $ArrayCountsData[] = $ArrayCountsList;
        }
        return $ArrayCountsData;
    }

    public function getBalanceQty($TrackID, $ArrivalID, $Count, $LotID)
    {
        $sqlVaWeight = "SELECT SUM(VaWeight) AS VaWeight FROM PpcValueAdditionTransferDetails 
        WHERE TrackID = '$TrackID' AND ArrivalCode IN ($ArrivalID) AND HonCount = '$Count' AND LotID = '$LotID'";
        $stmtVaWeight = $this->conn->prepare($sqlVaWeight);
        $stmtVaWeight->execute();
        $rowVaWeight = $stmtVaWeight->fetch(PDO::FETCH_ASSOC);
        $VaWeight = $rowVaWeight['VaWeight'] ? $rowVaWeight['VaWeight'] : '0';
        return $VaWeight;
    }

    public function getVaTransferDetails($TrackID, $ArrivalID)
    {
        $sqlFields = "VATransferID, PVAT.ArrivalDate, PVAT.TrackID, PVAT.ArrivalPlant, ArrivalCode, HonCount, VaWeight, 
        TranferPlant, TransferDate, HonWeight,HonRate,PVAT.LotID, LotNumber, RPS.StationName AS ArrivalPlantName, 
        RPS2.StationName AS TranferPlantName, RAD.BillDate,ArrivalCode";

        $sqlSelect = "SELECT $sqlFields FROM PpcValueAdditionTransferDetails AS PVAT
        LEFT JOIN RmLotDetatils AS RLD ON PVAT.LotID = RLD.RmLotID
        LEFT JOIN RmArrivalDetatils AS RAD ON RAD.RmArrivalID = RLD.RmReferenceID
        LEFT JOIN RmProcessingStations AS RPS ON PVAT.ArrivalPlant = RPS.ProcessingStationID
        LEFT JOIN RmProcessingStations AS RPS2 ON PVAT.TranferPlant = RPS2.ProcessingStationID
        WHERE PVAT.TrackID = '$TrackID' AND PVAT.ArrivalCode IN ($ArrivalID)";
        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();

        $i = 0;
        $ArrayData = [];
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
            $ArrayList['SNO'] = ++$i;
            $ArrayList['VATransferID'] = $dataRow['VATransferID'];
            $ArrayList['LotNumber'] = $dataRow['LotNumber'];
            $ArrayList['ArrivalCode'] = $dataRow['ArrivalCode'];
            $ArrayList['TrackID'] = $dataRow['TrackID'];
            $ArrayList['ArrivalDate'] = $dataRow['ArrivalDate'] ? date('d-m-Y', strtotime($dataRow['ArrivalDate'])) : '';
            $ArrayList['BillDate'] = $dataRow['BillDate'] ? date('d-m-Y', strtotime($dataRow['BillDate'])) : '';
            $ArrayList['HonLotID'] = $dataRow['LotID'];
            $ArrayList['ArrivalPlant'] = $dataRow['ArrivalPlant'];
            $ArrayList['ArrivalPlantName'] = $dataRow['ArrivalPlantName'];
            $ArrayList['TranferPlant'] = $dataRow['TranferPlant'];
            $ArrayList['TranferPlantName'] = $dataRow['TranferPlantName'];
            $ArrayList['TransferDate'] = $dataRow['TransferDate'] ? date('d-m-Y', strtotime($dataRow['TransferDate'])) : '';
            $ArrayList['HonCount'] = $dataRow['HonCount'];
            $ArrayList['HonWeight'] = $dataRow['HonWeight'];
            $ArrayList['HonRate'] = $dataRow['HonRate'];
            $ArrayList['VaWeight'] = $dataRow['VaWeight'];

            $ArrayData[] = $ArrayList;
        }
        return $ArrayData;
    }


    public function postVaTransferDetails($RequestBody)
    {
        $VATransferID = $RequestBody['VATransferID'];
        $ArrivalDate = $RequestBody['ArrivalDate'];
        $TrackID = $RequestBody['TrackID'];
        $ArrivalPlant = $RequestBody['ArrivalPlant'];
        $ArrivalCode = $RequestBody['ArrivalID'];
        $HonCount = $RequestBody['HonCount'];
        $VaWeight = $RequestBody['VaWeight'];
        $TranferPlant = $RequestBody['TranferPlant'];
        $TransferDate = $RequestBody['TransferDate'];
        $HonWeight = $RequestBody['HonWeight'];
        $HonRate = $RequestBody['HonRate'];
        $HonLotID = $RequestBody['HonLotID'];
        $existingTransfer = null;

        if ($VATransferID == 0) {
            $sqlInsertVaTransfer = "INSERT INTO `PpcValueAdditionTransferDetails` (ArrivalDate, TrackID, ArrivalPlant, 
            ArrivalCode, HonCount, VaWeight, TranferPlant, TransferDate, HonWeight, HonRate, LotID)
            VALUES('$ArrivalDate', '$TrackID', '$ArrivalPlant', '$ArrivalCode', '$HonCount', '$VaWeight', '$TranferPlant', 
            '$TransferDate', '$HonWeight', $HonRate, $HonLotID)";
            $stmtInsertVaTransfer = $this->conn->prepare($sqlInsertVaTransfer);
            $stmtInsertVaTransfer->execute();
            if ($stmtInsertVaTransfer) {
                $this->Error = null;
                $this->Msg = 'Data Posted Successfully';
                $lastInsertId = $this->conn->lastInsertId();
                $createdTransfer = $this->auditLogger->fetchRowById('PpcValueAdditionTransferDetails', 'VATransferID', $lastInsertId);
                $this->auditLogger->logInsert('PpcValueAdditionTransferDetails', $lastInsertId, $createdTransfer, $RequestBody, [
                    'ReferenceNo' => $TrackID,
                    'Remarks' => 'PreProcessing VA HON transfer created',
                ]);
            } else {
                $this->Error = 'Unable to Insert Data';
                $this->Msg = 'Unable to Insert Data';
            }
        } else {
            $existingTransfer = $this->auditLogger->fetchRowById('PpcValueAdditionTransferDetails', 'VATransferID', $VATransferID);
            $sqlUpdateVaTransfer = "UPDATE PpcValueAdditionTransferDetails SET ArrivalDate = '$ArrivalDate', TrackID = '$TrackID',
            ArrivalPlant = '$ArrivalPlant', ArrivalCode = '$ArrivalCode', HonCount = '$HonCount', VaWeight = '$VaWeight', 
            TranferPlant = '$TranferPlant', TransferDate = '$TransferDate', HonWeight = '$HonWeight', HonRate = '$HonRate', LotID = '$HonLotID'
            WHERE `VATransferID` = $VATransferID";
            $stmtUpdateVaTransfer = $this->conn->prepare($sqlUpdateVaTransfer);
            $stmtUpdateVaTransfer->execute();
            if ($stmtUpdateVaTransfer) {
                $this->Error = null;
                $this->Msg = 'Data Updated Successfully';
                $updatedTransfer = $this->auditLogger->fetchRowById('PpcValueAdditionTransferDetails', 'VATransferID', $VATransferID);
                $this->auditLogger->logUpdate('PpcValueAdditionTransferDetails', $VATransferID, $existingTransfer, $updatedTransfer, $RequestBody, [
                    'ReferenceNo' => $TrackID,
                    'Remarks' => 'PreProcessing VA HON transfer updated',
                ]);
            } else {
                $this->Error = 'Unable to Update Data';
                $this->Msg = 'Unable to Update Data';
            }
        }

        $HonCountDetails = $this->getCountsDetails($ArrivalCode);
        $VaTransferData = $this->getVaTransferDetails($TrackID, $ArrivalCode);
        $VaWeight = $this->getBalanceQty($TrackID, $ArrivalCode, $HonCount, $HonLotID);

        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        $Data['HonCount'] = $HonCountDetails;
        $Data['VaTransferData'] = $VaTransferData;
        $Data['VaWeight'] = $VaWeight;

        return $Data;
    }

    function deleteVaTransfer($RequestBody)
    {
        $VATransferID = $RequestBody['VATransferID'];
        $TrackID = $RequestBody['TrackID'];
        $ArrivalCode = $RequestBody['ArrivalID'];
        $Count = $RequestBody['Count'];
        $LotID = $RequestBody['LotID'];
        $existingTransfer = $this->auditLogger->fetchRowById('PpcValueAdditionTransferDetails', 'VATransferID', $VATransferID);


        $sqlDelete = "DELETE FROM PpcValueAdditionTransferDetails WHERE `VATransferID` = $VATransferID";
        $stmtDelete = $this->conn->prepare($sqlDelete);
        $stmtDelete->execute();

        if ($stmtDelete) {
            $this->Error = null;
            $this->Msg = 'Data Deleted Successfully';
            $this->auditLogger->logDelete('PpcValueAdditionTransferDetails', $VATransferID, $existingTransfer, $RequestBody, [
                'ReferenceNo' => $TrackID,
                'Remarks' => 'PreProcessing VA HON transfer deleted',
            ]);
        } else {
            $this->Error = 'Unable to Delete Data';
            $this->Msg = 'Unable to Delete Data';
        }

        $HonCountDetails = $this->getCountsDetails($ArrivalCode);
        $VaTransferData = $this->getVaTransferDetails($TrackID, $ArrivalCode);
        $VaWeight = $this->getBalanceQty($TrackID, $ArrivalCode, $Count, $LotID);

        $Data['Error'] = $this->Error;
        $Data['Msg'] = $this->Msg;
        $Data['HonCount'] = $HonCountDetails;
        $Data['VaTransferData'] = $VaTransferData;
        $Data['VaWeight'] = $VaWeight;

        return $Data;
    }
}
