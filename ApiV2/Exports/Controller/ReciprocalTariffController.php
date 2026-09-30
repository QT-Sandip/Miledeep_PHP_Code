<?php
include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
class ReciprocalTariffController
{
    private $conn;
    private $auditLogger;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->auditLogger = new CentralAuditLogger($this->conn, 'Exports');
    }

    public function getReciprocalTariff($RequestBody)
    {
        $PoDate = $RequestBody["PoDate"];

        $sql = "SELECT RT.*, SPD.PoNo
                FROM `ReciprocalTariffs` RT
                LEFT JOIN ScrmPIData AS SPD ON RT.PoID = SPD.PoID
                WHERE RT.PoDate = '$PoDate'
                ORDER BY RT.PoID DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $resultArr = [];
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $resultArr[] = $dataRow;
        }
        $result['Error'] = $sql;
        $result['Msg'] = "Get Reciprocal Tariffs Success";
        $result['Data'] = $resultArr;
        return $result;
    }

    public function postReciprocalTariff($RequestBody)
    {
        $PoIDs = $RequestBody["PoIDs"];
        $ReciprocalTariff = $RequestBody["ReciprocalTariff"];
        $RevisedPo = $RequestBody["RevisedPo"];
        $PoDate = $RequestBody["PoDate"];
        $PoIDs = explode(",", $PoIDs);
        $result = [];
        foreach ($PoIDs as $key => $PoID) {
            $sql = "INSERT INTO `ReciprocalTariffs` (PoID, ReciprocalTariff, RevisedPo, PoDate)VALUES ($PoID, $ReciprocalTariff, $RevisedPo, '$PoDate')";
            $stmt = $this->conn->prepare($sql);

            if (!$stmt->execute()) {
                $result['Error'] = $sql;
                $result['Msg'] = "Failed to insert data for PO ID: " . $PoID;
                $result['Data'] = [];
                return $result;
            }
            $createdTariff = $this->auditLogger->fetchRowById('ReciprocalTariffs', 'PoID', $PoID);
            $this->auditLogger->logInsert('ReciprocalTariffs', $PoID, $createdTariff, $RequestBody, [
                'ReferenceNo' => $PoID,
                'Remarks' => 'Export reciprocal tariff created',
                'Async' => 1,
            ]);
        }
        $result['Error'] = false;
        $result['Msg'] = "Post Reciprocal Tariffs Success";
        $result['Data'] = [];
        return $result;
    }

    public function updateReciprocalTariff($RequestBody)
    {
        $PoID = $RequestBody["PoIDs"];
        $ReciprocalTariff = $RequestBody["ReciprocalTariff"];
        $RevisedPo = $RequestBody["RevisedPo"];
        $PoDate = $RequestBody["PoDate"];
        $existingTariff = $this->auditLogger->fetchRowById('ReciprocalTariffs', 'PoID', $PoID);

        $result = [];
        $sql = "UPDATE `ReciprocalTariffs` SET ReciprocalTariff = $ReciprocalTariff, RevisedPo = $RevisedPo, PoDate = '$PoDate'
                    WHERE PoID = $PoID";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt->execute()) {
            $result['Error'] = true;
            $result['Msg'] = "Failed to update data for PO ID: " . $PoID;
            return $result;
        }
        $updatedTariff = $this->auditLogger->fetchRowById('ReciprocalTariffs', 'PoID', $PoID);
        $this->auditLogger->logUpdate('ReciprocalTariffs', $PoID, $existingTariff, $updatedTariff, $RequestBody, [
            'ReferenceNo' => $PoID,
            'Remarks' => 'Export reciprocal tariff updated',
        ]);
        // Your logic to update a specific reciprocal tariff
        $result['Error'] = false;
        $result['Msg'] = "Update Reciprocal Tariffs Success";
        $result['Data'] = [];
        return $result;
    }

    public function deleteReciprocalTariff($RequestBody)
    {
        $PoDate = $RequestBody["PoDate"];
        $PoIDs = $RequestBody["PoIDs"];
        $existingTariff = $this->auditLogger->fetchRowsByConditions('ReciprocalTariffs', ['PoID' => $PoIDs, 'PoDate' => $PoDate]);
        $result = [];
        $sql = "DELETE FROM `ReciprocalTariffs` WHERE PoID = $PoIDs AND PoDate = '$PoDate'";
        $stmt = $this->conn->prepare($sql);

        if (!$stmt->execute()) {
            $result['Error'] = true;
            $result['Msg'] = "Failed to delete data for PO ID: " . $PoIDs;
            $result['Data'] = [];
            return $result;
        }
        foreach ($existingTariff as $row) {
            $this->auditLogger->logDelete('ReciprocalTariffs', $PoIDs, $row, $RequestBody, [
                'ReferenceNo' => $PoIDs,
                'Remarks' => 'Export reciprocal tariff deleted',
                'Async' => 1,
            ]);
        }
        // Your logic to delete a specific reciprocal tariff
        $result['Error'] = false;
        $result['Msg'] = "Delete Reciprocal Tariffs Success";
        $result['Data'] = [];
        return $result;
    }

    public function getPoNumbers($RequestBody)
    {
        $poDate = $RequestBody['PoDate'];
        $sqlPOData = "SELECT * FROM ScrmPIData as SPD  
        left join ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id
        WHERE PoDate='$poDate' and (SPD.DestinationCountry = 157 OR SCL.ShowFrightCharge = 1 )
        ORDER BY InvNumFull DESC";



        $OnWater = $RequestBody['OnWater'];
        if ($OnWater) {
            $sqlPOData = "SELECT * from ScrmPIData as SPD left join ScrmCustomInvoices as SCI on SCI.PiID=SPD.PoID
                    left join ScrmISFDetails  ISF on ISF.PIID=SCI.PiID
                     left join ScrmCountriesList as SCL on SPD.DestinationCountry=SCL.id WHERE (((SCI.CInvID ='' OR SCI.CInvID IS NULL) OR (SCI.StockOutDate='' OR SCI.StockOutDate IS NULL)) OR ((SCI.StockOutDate is not null AND SCI.StockOutDate != '')
                    AND (ISF.EmptyReturn is null or  ISF.EmptyReturn = '' ))) AND (SPD.DestinationCountry = 157 OR SCL.ShowFrightCharge = 1 )AND  SPD.Status NOT IN (4)";
        }


        $stmt = $this->conn->prepare($sqlPOData);
        $stmt->execute();
        $result = [];
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ResultDataArray['PoID'] = $dataRow['PoID'];
            $ResultDataArray['PoNo'] = $dataRow['PoNo'];
            $resultData[] = $ResultDataArray;
        }
        $result['Error'] = false;
        $result['Msg'] = "Success";
        $result['Data'] = $resultData ? $resultData : [];
        $result['Qry'] = $sqlPOData;
        return $result;
    }
}
