<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class ArrivalReceivalPageController
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
        $this->auditLogger = new CentralAuditLogger($this->conn, 'PreProcessing');
        // $this->utilController = new UtilController($this->conn);
    }

    public function postRecievalDetails($RequestBody)
    {
        $RmVehicleArrivalID = $RequestBody['RmVehicleArrivalID'];

        if ($RmVehicleArrivalID == '') {
            $Data = $this->insertReciavalDetails($RequestBody);
        } else {
            $Data = $this->updateRecievalDetails($RequestBody);
        }

        return $Data;
    }

    public function insertReciavalDetails($RequestBody)
    {
        $ArrivalID = $RequestBody['ArrivalID'];
        $ProductionWeight = $RequestBody['ProductionWeight'];
        $CountNetWeight = $RequestBody['CountNetWeight'];
        $ArrivalCount = $RequestBody['ArrivalCount'];
        $VehicleSealNumber = $RequestBody['VehicleSealNumber'];
        $VehicleNumber = $RequestBody['VehicleNumber'];
        $RmReceivalNumber = $RequestBody['RmReceivalNumber'];

        $sqlInsert = "INSERT INTO RmVehicleWiseArrivalDetails (ArrivalID, ProductionWeight, CountNetWeight, ArrivalCount, VehicleSealNumber, VehicleNumber,RmReceivalNumber) 
        VALUES ('$ArrivalID','$ProductionWeight','$CountNetWeight','$ArrivalCount','$VehicleSealNumber','$VehicleNumber','$RmReceivalNumber')";

        $stmtInsert = $this->conn->prepare($sqlInsert);
        $stmtInsert->execute();

        if ($stmtInsert) {

            $lastInsertedId = $this->conn->lastInsertId();
            $RequestBody['RmVehicleArrivalID'] = $lastInsertedId;
            $createdReceival = $this->auditLogger->fetchRowById('RmVehicleWiseArrivalDetails', 'RmVehicleArrivalID', $lastInsertedId);
            $this->auditLogger->logInsert('RmVehicleWiseArrivalDetails', $lastInsertedId, $createdReceival, $RequestBody, [
                'ReferenceNo' => $ArrivalID,
                'Remarks' => 'PreProcessing arrival receival created',
            ]);

            $receivalData = $this->getRecievalDetails($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'Receival Posted Successfully';
            $Data['Data'] = $receivalData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to Insert Receival';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    public function updateRecievalDetails($RequestBody)
    {
        $RmVehicleArrivalID = $RequestBody['RmVehicleArrivalID'];
        $ArrivalID = $RequestBody['ArrivalID'];
        $ProductionWeight = $RequestBody['ProductionWeight'];
        $CountNetWeight = $RequestBody['CountNetWeight'];
        $ArrivalCount = $RequestBody['ArrivalCount'];
        $VehicleSealNumber = $RequestBody['VehicleSealNumber'];
        $VehicleNumber = $RequestBody['VehicleNumber'];
        $RmReceivalNumber = $RequestBody['RmReceivalNumber'];
        $existingReceival = $this->auditLogger->fetchRowById('RmVehicleWiseArrivalDetails', 'RmVehicleArrivalID', $RmVehicleArrivalID);

        $sqlUpdate = "UPDATE RmVehicleWiseArrivalDetails SET ArrivalID='$ArrivalID',ProductionWeight='$ProductionWeight',CountNetWeight='$CountNetWeight',
        ArrivalCount='$ArrivalCount',VehicleSealNumber='$VehicleSealNumber',VehicleNumber='$VehicleNumber',RmReceivalNumber='$RmReceivalNumber'
        WHERE RmVehicleArrivalID='$RmVehicleArrivalID'";

        $stmtUpdate = $this->conn->prepare($sqlUpdate);
        $stmtUpdate->execute();

        if ($stmtUpdate) {
            $updatedReceival = $this->auditLogger->fetchRowById('RmVehicleWiseArrivalDetails', 'RmVehicleArrivalID', $RmVehicleArrivalID);
            $this->auditLogger->logUpdate('RmVehicleWiseArrivalDetails', $RmVehicleArrivalID, $existingReceival, $updatedReceival, $RequestBody, [
                'ReferenceNo' => $ArrivalID,
                'Remarks' => 'PreProcessing arrival receival updated',
            ]);
            $receivalData = $this->getRecievalDetails($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'Receival Updated Successfully';
            $Data['Data'] = $receivalData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to update Receival';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    function deleteReceivalDetails($RequestBody)
    {
        $RmVehicleArrivalID = $RequestBody['RmVehicleArrivalID'];
        $existingReceival = $this->auditLogger->fetchRowById('RmVehicleWiseArrivalDetails', 'RmVehicleArrivalID', $RmVehicleArrivalID);

        $sqlDelete = "DELETE FROM `RmVehicleWiseArrivalDetails` WHERE RmVehicleArrivalID='$RmVehicleArrivalID'";

        $stmtUpdate = $this->conn->prepare($sqlDelete);
        $stmtUpdate->execute();

        if ($stmtUpdate) {
            $this->auditLogger->logDelete('RmVehicleWiseArrivalDetails', $RmVehicleArrivalID, $existingReceival, $RequestBody, [
                'ReferenceNo' => $RmVehicleArrivalID,
                'Remarks' => 'PreProcessing arrival receival deleted',
            ]);
            $receivalData = $this->getRecievalDetails($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'Receival Deleted Successfully';
            $Data['Data'] = $receivalData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to Delete Receival';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    function getRecievalDetails($RequestBody)
    {

        $ArrivalID = $RequestBody['ArrivalID'];

        $sqlFields = "RVWAD.RmVehicleArrivalID, RVWAD.ArrivalID, RVWAD.ProductionWeight, RVWAD.CountNetWeight, RVWAD.ArrivalCount, RVWAD.VehicleSealNumber, RVWAD.VehicleNumber,
        RVWAD.RmReceivalNumber, RLD.LotNumber AS ArrivalNumber";

        $sqlQuery = "SELECT $sqlFields FROM RmVehicleWiseArrivalDetails AS RVWAD
        LEFT JOIN RmLotDetatils AS RLD ON RVWAD.ArrivalID=RLD.RmLotID
        WHERE ArrivalID='$ArrivalID'";

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($errorInfo);

        $ArrayReceivalData = [];

        $i = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayReceivalList['SNO'] = ++$i;
            $ArrayReceivalList['RmVehicleArrivalID'] = $dataRow['RmVehicleArrivalID'];
            $ArrayReceivalList['ArrivalID'] = $dataRow['ArrivalID'];
            $ArrayReceivalList['ArrivalNumber'] = $dataRow['ArrivalNumber'];
            $ArrayReceivalList['ProductionWeight'] = $dataRow['ProductionWeight'];
            $ArrayReceivalList['CountNetWeight'] = $dataRow['CountNetWeight'];
            $ArrayReceivalList['ArrivalCount'] = $dataRow['ArrivalCount'];
            $ArrayReceivalList['VehicleSealNumber'] = $dataRow['VehicleSealNumber'];
            $ArrayReceivalList['VehicleNumber'] = $dataRow['VehicleNumber'];
            $ArrayReceivalList['RmReceivalNumber'] = $dataRow['RmReceivalNumber'];

            $ArrayReceivalData[] =  $ArrayReceivalList;
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['EditLink'] = "#";
        $data['Data'] = $ArrayReceivalData;
        
        return $data;
    }

    // function getPoBrkUpTotals($RequestBody)
    // {

    //     $ChemPoID = $RequestBody['ChemPoID'];

    //     $sqlTotal = "SELECT sum(QuantityInKgs) AS TotalQty, sum(QuantityInKgs*RatePerKgInRupees) AS TotalAmount FROM GsChemicalPoBreakUp WHERE ChemPoID='$ChemPoID'";

    //     $stmtTotal = $this->conn->prepare($sqlTotal);
    //     $stmtTotal->execute();

    //     $ArrayTotal = [];

    //     while ($dataRow = $stmtTotal->fetch(PDO::FETCH_ASSOC)) {
    //         $ArrayTotalData['TotalQty'] = $dataRow['TotalQty'];
    //         $ArrayTotalData['TotalAmount'] = $dataRow['TotalAmount'];

    //         $ArrayTotal[] = $ArrayTotalData;
    //     }

    //     $data['Data'] = $ArrayTotal;

    //     return $data;
    // }
}
