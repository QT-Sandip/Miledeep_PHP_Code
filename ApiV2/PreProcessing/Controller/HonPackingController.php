<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class HonPackingController
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

    public function postHonPackingDetails($RequestBody)
    {
        $this->postHonFormDetails($RequestBody);

        $data['Error'] = $this->Error;
        $data['Msg'] = $this->Msg;
        $data['Data'] = $this->getHonPackingDetails($RequestBody)['Data'];
        $data['Totals'] = $this->getHonPackingDetails($RequestBody)['Totals'];

        return $data;
    }

    public function postHonFormDetails($RequestBody)
    {
        $HonID = $RequestBody['HonID'];
        $LotID = $RequestBody['ArrivalID'];
        $GradingDate = $RequestBody['GradingDate'];
        $PlantID = $RequestBody['PlantID'];
        $StationID = $RequestBody['StationID'];
        $Remarks = $RequestBody['Remarks'];
        $HonCount = $RequestBody['HonCount'];
        $HonWeight = $RequestBody['HonWeight'];

        date_default_timezone_set("Asia/Kolkata");
        $PostedDate = date("Y-m-d H:i:s");
        $existingHonHeader = null;
        $existingHonBreakup = null;
        if ($HonID != 0) {
            $existingHonHeader = $this->auditLogger->fetchOne("SELECT * FROM PpcHonGradingDetails WHERE LotID = ? AND HonID = ?", [$LotID, $HonID]);
            $existingHonBreakup = $this->auditLogger->fetchOne("SELECT * FROM PpcHonGradingBreakup WHERE LotID = ?", [$LotID]);
        }

        if ($HonID == 0) {
            $sqlInsert = "INSERT INTO `PpcHonGradingDetails`(`HonID`, `LotID`,`GradingDate`, `PlantID`, `StationID`, `Remarks`, `PostedDate`) 
            VALUES ('$HonID','$LotID','$GradingDate','$PlantID','$StationID','$Remarks','$PostedDate')";

            $stmtInsert = $this->conn->prepare($sqlInsert);
            $stmtInsert->execute();

            $sqlBrkInsert = "INSERT INTO `PpcHonGradingBreakup` (`HonID`, `LotID`, `HonCount`, `HonWeight`) 
            VALUES ('$HonID', '$LotID', '$HonCount', '$HonWeight')";

            $stmtBrkInsert = $this->conn->prepare($sqlBrkInsert);
            $stmtBrkInsert->execute();

            $this->Error = null;
            $this->Msg = 'Hon Packing Posted Successfully';
            $createdHonHeader = $this->auditLogger->fetchOne("SELECT * FROM PpcHonGradingDetails WHERE LotID = ? ORDER BY HonID DESC LIMIT 1", [$LotID]);
            $createdHonBreakup = $this->auditLogger->fetchOne("SELECT * FROM PpcHonGradingBreakup WHERE LotID = ? ORDER BY HonID DESC LIMIT 1", [$LotID]);
            $headerId = isset($createdHonHeader['HonID']) ? $createdHonHeader['HonID'] : $LotID;
            $breakupId = isset($createdHonBreakup['HonID']) ? $createdHonBreakup['HonID'] : $LotID;
            $this->auditLogger->logInsert('PpcHonGradingDetails', $headerId, $createdHonHeader, $RequestBody, [
                'ReferenceNo' => $LotID,
                'Remarks' => 'PreProcessing HON packing header created',
            ]);
            $this->auditLogger->logInsert('PpcHonGradingBreakup', $breakupId, $createdHonBreakup, $RequestBody, [
                'ReferenceNo' => $LotID,
                'Remarks' => 'PreProcessing HON packing breakup created',
            ]);
        } else {
            $sqlUpdate = "UPDATE `PpcHonGradingDetails` SET `GradingDate`='$GradingDate',
            `PlantID`='$PlantID',`StationID`='$StationID',`Remarks`='$Remarks', `PostedDate`='$PostedDate' 
            WHERE `LotID`='$LotID' AND `HonID`='$HonID'";

            $stmtUpdate = $this->conn->prepare($sqlUpdate);
            $stmtUpdate->execute();

            $sqlBrkUpdate = "UPDATE `PpcHonGradingBreakup` SET  `HonCount`='$HonCount', `HonWeight`='$HonWeight' 
            WHERE `LotID`='$LotID'";

            $stmtBrkUpdate = $this->conn->prepare($sqlBrkUpdate);
            $stmtBrkUpdate->execute();

            $this->Error = null;
            $this->Msg = 'Hon Packing Updated Successfully';
            $updatedHonHeader = $this->auditLogger->fetchOne("SELECT * FROM PpcHonGradingDetails WHERE LotID = ? AND HonID = ?", [$LotID, $HonID]);
            $updatedHonBreakup = $this->auditLogger->fetchOne("SELECT * FROM PpcHonGradingBreakup WHERE LotID = ?", [$LotID]);
            $this->auditLogger->logUpdate('PpcHonGradingDetails', $HonID, $existingHonHeader, $updatedHonHeader, $RequestBody, [
                'ReferenceNo' => $LotID,
                'Remarks' => 'PreProcessing HON packing header updated',
            ]);
            $this->auditLogger->logUpdate('PpcHonGradingBreakup', $LotID, $existingHonBreakup, $updatedHonBreakup, $RequestBody, [
                'ReferenceNo' => $LotID,
                'Remarks' => 'PreProcessing HON packing breakup updated',
            ]);
        }
    }

    public function getHonPackingDetails($RequestBody)
    {
        $LotID = $RequestBody['ArrivalID'];

        $sqlFields = "PHD.HonID, PHD.LotID, PHD.GradingDate, PHD.StationID, PHD.PlantID, PHD.Remarks,
        PHGB.HonID AS BreakUpID, PHGB.LotID AS BrkLotID, PHGB.HonWeight, PHGB.HonCount, RmPS.ProcessingStationID, RmPS.FullName,
        PVS.StationID, PVS.UnitName, ArrivalNumber,RLD.RmLotID as RmArrivalID";

        $sqlQuery = "SELECT $sqlFields FROM `PpcHonGradingDetails` AS PHD
        LEFT JOIN PpcHonGradingBreakup AS PHGB ON PHD.LotID = PHGB.LotID
        LEFT JOIN RmProcessingStations AS RmPS ON PHD.PlantID = RmPS.ProcessingStationID
        LEFT JOIN PpcVAStations AS PVS ON PHD.StationID = PVS.StationID
        LEFT JOIN RmLotDetatils as RLD on PHD.LotID = RLD.RmLotID
        LEFT JOIN RmArrivalDetatils AS RAD ON RAD.RmArrivalID=RLD.RmReferenceID
        WHERE PHD.LotID='$LotID' GROUP BY PHD.LotID";

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($errorInfo);

        $ArrayPackingData = [];

        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayList['HonID'] = $dataRow['HonID'];
            $ArrayList['LotID'] = $dataRow['LotID'];
            $ArrayList['ArrivalNumber'] = $dataRow['ArrivalNumber'];
            $ArrayList['GradingDate'] = $dataRow['GradingDate'];
            $ArrayList['StationID'] = $dataRow['StationID'];
            $ArrayList['UnitName'] = $dataRow['UnitName'];
            $ArrayList['PlantID'] = $dataRow['PlantID'];
            $ArrayList['FullName'] = $dataRow['FullName'];
            $ArrayList['BreakUpID'] = $dataRow['BreakUpID'];
            $ArrayList['HonWeight'] = $dataRow['HonWeight'];
            $ArrayList['HonCount'] = $dataRow['HonCount'];
            $ArrayList['Remarks'] = $dataRow['Remarks'];

            $TotalWeight += $dataRow['HonWeight'];
            $ArrayPackingData[] =  $ArrayList;
        }

        $data['Error'] = null;
        $data['Msg'] = "Success";
        $data['Data'] = $ArrayPackingData;
        $data['Totals']['TotalWeight'] = $TotalWeight;

        return $data;
    }

    public function deleteHonPacking($RequestBody)
    {
        $HonID = $RequestBody['HonID'];
        $LotID = $RequestBody['ArrivalID'];
        $existingHonHeader = $this->auditLogger->fetchOne("SELECT * FROM PpcHonGradingDetails WHERE LotID = ?", [$LotID]);
        $existingHonBreakup = $this->auditLogger->fetchOne("SELECT * FROM PpcHonGradingBreakup WHERE LotID = ?", [$LotID]);

        $sqlDelete = "DELETE FROM PpcHonGradingDetails WHERE LotID='$LotID'";
        $stmtDelete = $this->conn->prepare($sqlDelete);
        $stmtDelete->execute();

        // $errorInfo = $stmtDelete->errorInfo();
        // var_dump($errorInfo);

    
       $sqlDeleteBrkUp = "DELETE FROM PpcHonGradingBreakup WHERE LotID='$LotID'";
        $stmtDeleteBrkUp = $this->conn->prepare($sqlDeleteBrkUp);
        $stmtDeleteBrkUp->execute();

        // $errorInfo = $stmtDeleteBrkUp->errorInfo();
        // var_dump($errorInfo);
      
        $data['Error'] = null;
        $data['Msg'] = "Deleted Successfully";
        $data['Data'] = $this->getHonPackingDetails($RequestBody)['Data'];
        $data['Totals'] = $this->getHonPackingDetails($RequestBody)['Totals'];
        $this->auditLogger->logDelete('PpcHonGradingDetails', $HonID, $existingHonHeader, $RequestBody, [
            'ReferenceNo' => $LotID,
            'Remarks' => 'PreProcessing HON packing header deleted',
        ]);
        $this->auditLogger->logDelete('PpcHonGradingBreakup', $LotID, $existingHonBreakup, $RequestBody, [
            'ReferenceNo' => $LotID,
            'Remarks' => 'PreProcessing HON packing breakup deleted',
        ]);

        return $data;
    }
}
