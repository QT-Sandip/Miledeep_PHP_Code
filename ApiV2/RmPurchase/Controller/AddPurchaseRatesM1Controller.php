<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/AuditLogger.php';

class AddPurchaseRatesM1Controller
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
        $this->auditLogger = new AuditLogger($this->conn, 'Purchase');
    }

    public function postRmCountDetails($RequestBody)
    {
        $BaseRates = $RequestBody['BaseRates'];
        $Species = $RequestBody['Species'];
        $UserID = $RequestBody['UserID'];
        $DateId = $RequestBody['DateId'];
        $IsEdit = $RequestBody['IsEdit'];
        $PurchaseCenter = $RequestBody['PurchaseCenter'];
        $SubmissionDate = $RequestBody['SubmissionDate'];

        $dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
        $PostedDate = $dateTime->format("Y-m-d H:i:s");

        if ($DateId == '' || $DateId == 0) {

            $ExistingPurchaseCenters = $this->getExistingCentersInsert($RequestBody);
            if (!$ExistingPurchaseCenters) {

                $PurchaseCenters = trim($PurchaseCenter);
                $sqlInsertRateHis = "INSERT INTO `RmPurchaseRatesHistory` 
                (`UserID`,`RmPriceDate`,`SType`,`PostedDate`,`CenterID`) VALUES 
                ('$UserID','$SubmissionDate','$Species','$PostedDate','$PurchaseCenters')";
                $stmtInsertRateHis = $this->conn->prepare($sqlInsertRateHis);
                $stmtInsertRateHis->execute();
                $TrackId = $this->conn->lastInsertId();
                if ($stmtInsertRateHis) {
                    $newRateHistory = $this->auditLogger->fetchRowById('RmPurchaseRatesHistory', 'ID', $TrackId);
                    $this->auditLogger->logInsert('RmPurchaseRatesHistory', $TrackId, $newRateHistory, $RequestBody, [
                        'ReferenceNo' => $SubmissionDate,
                        'Remarks' => 'Purchase rate history created',
                    ]);
                }
            } else {
                $this->Error = "Rates Already Existing for " . $ExistingPurchaseCenters;
                $this->Msg = "Rates Already Existing for " . $ExistingPurchaseCenters;
            }
        } else {

            $ExistingPurchaseCentersUpdate = $this->getExistingCentersUpdate($RequestBody);

            if (!$ExistingPurchaseCentersUpdate) {
                $PurchaseCenters = trim($PurchaseCenter);
                $existingRateHistory = $this->auditLogger->fetchRowById('RmPurchaseRatesHistory', 'ID', $DateId);

                $sqlUpdateRateHis = "UPDATE `RmPurchaseRatesHistory` SET `UserID` = '$UserID',`RmPriceDate` = '$SubmissionDate',
                `SType` = '$Species',`PostedDate` = '$PostedDate',`CenterID` = '$PurchaseCenters' WHERE ID='$DateId'";

                $stmtUpdateRateHis = $this->conn->prepare($sqlUpdateRateHis);
                $stmtUpdateRateHis->execute();
                if ($stmtUpdateRateHis) {
                    $updatedRateHistory = $this->auditLogger->fetchRowById('RmPurchaseRatesHistory', 'ID', $DateId);
                    $this->auditLogger->logUpdate('RmPurchaseRatesHistory', $DateId, $existingRateHistory, $updatedRateHistory, $RequestBody, [
                        'ReferenceNo' => $SubmissionDate,
                        'Remarks' => 'Purchase rate history updated',
                    ]);
                }
            } else {
                $this->Error = "Rates Already Existing for " . $ExistingPurchaseCentersUpdate;
                $this->Msg = "Rates Already Existing for " . $ExistingPurchaseCentersUpdate;
            }
        }

        if ($sqlUpdateRateHis || $stmtInsertRateHis) {
            for ($i = 0; $i < count($BaseRates); $i++) {
                $aryRates = explode('/', $BaseRates[$i]);

                $rmCount = floatval($aryRates[0]);
                $baseRate = floatval($aryRates[1]);

                if ($IsEdit == 0) {
                    $sqlPurRates = "INSERT INTO `RmPurchaseRates` (`RmCount`, `RmPrice`, `DateID`) VALUES ('$rmCount','$baseRate','$TrackId')";
                } else if ($IsEdit == 1) {
                    $existingRate = $this->auditLogger->fetchRowsByConditions('RmPurchaseRates', ['DateID' => $DateId, 'RmCount' => $rmCount]);
                    $existingRate = !empty($existingRate) ? $existingRate[0] : null;
                    $sqlPurRates = "UPDATE `RmPurchaseRates` SET RmPrice='$baseRate' WHERE DateID='$DateId' AND RmCount='$rmCount'";
                }
                $stmtPurRates = $this->conn->prepare($sqlPurRates);
                $stmtPurRates->execute();
                if ($stmtPurRates) {
                    if ($IsEdit == 0) {
                        $newRateId = $this->conn->lastInsertId();
                        $newRate = $this->auditLogger->fetchRowById('RmPurchaseRates', 'ID', $newRateId);
                        if ($newRate == null) {
                            $newRateRows = $this->auditLogger->fetchRowsByConditions('RmPurchaseRates', ['DateID' => $TrackId, 'RmCount' => $rmCount]);
                            $newRate = !empty($newRateRows) ? $newRateRows[0] : null;
                            $newRateId = isset($newRate['ID']) ? $newRate['ID'] : $rmCount;
                        }
                        $this->auditLogger->logInsert('RmPurchaseRates', $newRateId, $newRate, $RequestBody, [
                            'ReferenceNo' => $SubmissionDate,
                            'Remarks' => 'Purchase rate created',
                        ]);
                    } else if ($IsEdit == 1) {
                        $updatedRate = $this->auditLogger->fetchRowsByConditions('RmPurchaseRates', ['DateID' => $DateId, 'RmCount' => $rmCount]);
                        $updatedRate = !empty($updatedRate) ? $updatedRate[0] : null;
                        $this->auditLogger->logUpdate('RmPurchaseRates', isset($updatedRate['ID']) ? $updatedRate['ID'] : $rmCount, $existingRate, $updatedRate, $RequestBody, [
                            'ReferenceNo' => $SubmissionDate,
                            'Remarks' => 'Purchase rate updated',
                        ]);
                    }
                    $this->Error = null;
                    if ($TrackId != 0) {
                        $this->Msg = "RM Prices Posted For Date - " . date('d-m-Y', strtotime($SubmissionDate));
                    } else {
                        $this->Msg = "RM Prices Updated For Date - " . date('d-m-Y', strtotime($SubmissionDate));
                    }
                } else {
                    $this->Error = 'Unable to save Data';
                    $this->Msg = 'Unable to save Data';
                }
            }
        }

        $getData = $this->getRmCountDetails($RequestBody);
        $data['Error'] = $this->Error;
        $data['Msg'] = $this->Msg;
        $data['TrackId'] = $TrackId;
        $data['Data'] = $getData['Data'];

        return $data;
    }

    function copyRmCountDetails($RequestBody)
    {
        // Validate Input
        if (empty($RequestBody['DateId']) || empty($RequestBody['SubmissionDate']) || empty($RequestBody['Stype'])) {
            $this->Error = "Missing required parameters.";
            $this->Msg = "Error: DateId, SubmissionDate, and Stype are required.";
            return ['Error' => $this->Error, 'Msg' => $this->Msg];
        }

        $DateId = $RequestBody['DateId'];
        $SubmissionDate = $RequestBody['SubmissionDate'];
        $Stype = $RequestBody['Stype'];

        $dateTime = new DateTime('now', new DateTimeZone('Asia/Kolkata'));
        $PostedDate = $dateTime->format("Y-m-d H:i:s");

        try {
            // Check if the SubmissionDate already exists in RmPurchaseRatesHistory 
            // and if any of the CenterIDs match between the given DateId and SubmissionDate with same SType
            $sqlCheckRateHis = "SELECT COUNT(*) 
                FROM RmPurchaseRatesHistory AS R1
                WHERE R1.RmPriceDate = ? 
                AND R1.SType = ? 
                AND R1.CenterID IN (
                    SELECT R2.CenterID 
                    FROM RmPurchaseRatesHistory AS R2 
                    WHERE R2.ID = ? 
                    AND R2.SType = ?
                )";

            $stmtCheckRateHis = $this->conn->prepare($sqlCheckRateHis);
            $stmtCheckRateHis->execute([$SubmissionDate, $Stype, $DateId, $Stype]);
            $existingRateHis = $stmtCheckRateHis->fetchColumn();

            if ($existingRateHis > 0) {
                throw new Exception("Rm Price Rate already exists for this date - " . date('d-m-Y', strtotime($SubmissionDate)));
            }
            // Insert into RmPurchaseRatesHistory
            $sqlInsertRateHis = "INSERT INTO RmPurchaseRatesHistory (UserID, RmPriceDate, PostedDate, SType, CenterID)
            SELECT UserID, ?, ?, SType, CenterID
            FROM RmPurchaseRatesHistory
            WHERE ID = ? AND SType = ?";

            $stmtPurRatesHis = $this->conn->prepare($sqlInsertRateHis);
            $stmtPurRatesHis->execute([$SubmissionDate, $PostedDate, $DateId, $Stype]);

            $NewDateID = $this->conn->lastInsertId(); // Get the new DateID
            if ($stmtPurRatesHis) {
                $newHistory = $this->auditLogger->fetchRowById('RmPurchaseRatesHistory', 'ID', $NewDateID);
                $this->auditLogger->logInsert('RmPurchaseRatesHistory', $NewDateID, $newHistory, $RequestBody, [
                    'ReferenceNo' => $SubmissionDate,
                    'Remarks' => 'Purchase rate history copied',
                ]);
            }

            if ($NewDateID && $stmtPurRatesHis) {
                // Check if the date already exists in RmPurchaseRates
                $sqlCheckRate = "SELECT COUNT(*) FROM RmPurchaseRates WHERE DateID = ?";
                $stmtCheckRate = $this->conn->prepare($sqlCheckRate);
                $stmtCheckRate->execute([$NewDateID]);
                $existingRate = $stmtCheckRate->fetchColumn();

                if ($existingRate > 0) {
                    throw new Exception("Rm Price Rate already exists for this date - " . date('d-m-Y', strtotime($SubmissionDate)));
                }
                // Insert into RmPurchaseRates
                $sqlRateHis = "INSERT INTO RmPurchaseRates (RmCount, RmPrice, DateID)
                SELECT RmCount, RmPrice, ?
                FROM RmPurchaseRates
                WHERE DateID = ?";

                $stmtPurRates = $this->conn->prepare($sqlRateHis);
                $stmtPurRates->execute([$NewDateID, $DateId]);
                if ($stmtPurRates) {
                    $copiedRates = $this->auditLogger->fetchRowsByConditions('RmPurchaseRates', ['DateID' => $NewDateID]);
                    foreach ($copiedRates as $copiedRate) {
                        $this->auditLogger->logInsert('RmPurchaseRates', isset($copiedRate['ID']) ? $copiedRate['ID'] : $copiedRate['RmCount'], $copiedRate, $RequestBody, [
                            'ReferenceNo' => $SubmissionDate,
                            'Remarks' => 'Purchase rate copied',
                        ]);
                    }
                    $this->Error = null;
                    $this->Msg = "RM Prices Copied For Date - " . date('d-m-Y', strtotime($SubmissionDate));
                } else {
                    throw new Exception("Failed to Save, Try again later.");
                }
            } else {
                throw new Exception("Failed to Save, Try again later.");
            }
        } catch (Exception $e) {
            $this->Error = $e->getMessage();
            $this->Msg = 'Error: ' . $e->getMessage();
        }

        return [
            'Error' => $this->Error,
            'Msg' => $this->Msg
        ];
    }

    function deleteRmCountDetails($RequestBody)
    {
        // Validate Input
        if (empty($RequestBody['DateId']) || empty($RequestBody['Stype'])) {
            $this->Error = "Missing required parameters.";
            $this->Msg = "Error: DateId and Stype are required.";
            return ['Error' => $this->Error, 'Msg' => $this->Msg];
        }

        $DateId = $RequestBody['DateId'];
        $Stype = $RequestBody['Stype'];

        // Check if the DateId exists in RmPurchaseRatesHistory and is valid
        $sqlCheckRateHis = "SELECT COUNT(*) FROM RmPurchaseRatesHistory WHERE ID = ? AND SType = ?";
        $stmtCheckRateHis = $this->conn->prepare($sqlCheckRateHis);
        $stmtCheckRateHis->execute([$DateId, $Stype]);
        $existingRateHis = $stmtCheckRateHis->fetchColumn();

        if ($existingRateHis == 0) {
            $this->Error = "No matching RM Price Rate found for the given date and SType.";
            $this->Msg = "Error: Rm Price Rate does not exist ";
            return ['Error' => $this->Error, 'Msg' => $this->Msg];
        }

        try {
            // Begin Transaction to ensure atomic operation
            $this->conn->beginTransaction();
            $existingRates = $this->auditLogger->fetchRowsByConditions('RmPurchaseRates', ['DateID' => $DateId]);
            $existingHistory = $this->auditLogger->fetchRowById('RmPurchaseRatesHistory', 'ID', $DateId);

            // Check if data exists in RmPurchaseRates to prevent deletion of empty rows
            $sqlCheckRate = "SELECT COUNT(*) FROM RmPurchaseRates WHERE DateID = ?";
            $stmtCheckRate = $this->conn->prepare($sqlCheckRate);
            $stmtCheckRate->execute([$DateId]);
            $existingRate = $stmtCheckRate->fetchColumn();

            if ($existingRate == 0) {
                throw new Exception("No related data found in RmPurchaseRates for the given DateId.");
            }

            // Delete from RmPurchaseRates
            $sqlDeleteRate = "DELETE FROM RmPurchaseRates WHERE DateID = ?";
            $stmtDeleteRate = $this->conn->prepare($sqlDeleteRate);
            $stmtDeleteRate->execute([$DateId]);

            // Delete from RmPurchaseRatesHistory
            $sqlDeleteRateHis = "DELETE FROM RmPurchaseRatesHistory WHERE ID = ? AND SType = ?";
            $stmtDeleteRateHis = $this->conn->prepare($sqlDeleteRateHis);
            $stmtDeleteRateHis->execute([$DateId, $Stype]);

            // Commit Transaction
            $this->conn->commit();
            foreach ($existingRates as $existingRate) {
                $this->auditLogger->logDelete('RmPurchaseRates', isset($existingRate['ID']) ? $existingRate['ID'] : $existingRate['RmCount'], $existingRate, $RequestBody, [
                    'ReferenceNo' => $DateId,
                    'Remarks' => 'Purchase rate deleted',
                ]);
            }
            if ($existingHistory != null) {
                $this->auditLogger->logDelete('RmPurchaseRatesHistory', $DateId, $existingHistory, $RequestBody, [
                    'ReferenceNo' => $DateId,
                    'Remarks' => 'Purchase rate history deleted',
                ]);
            }

            $this->Error = null;
            $this->Msg = "RM Price Deleted Successfully ";
        } catch (Exception $e) {
            // Rollback Transaction if any error occurs
            $this->conn->rollBack();

            $this->Error = $e->getMessage();
            $this->Msg = 'Error: ' . $e->getMessage();
        }

        return [
            'Error' => $this->Error,
            'Msg' => $this->Msg
        ];
    }

    function getExistingCentersInsert($RequestBody)
    {
        $PurchaseCenter = $RequestBody['PurchaseCenter'];
        $SubmissionDate = $RequestBody['SubmissionDate'];
        $Species = $RequestBody['Species'];

        $sqlRatesCenters = "SELECT GROUP_CONCAT(CenterID SEPARATOR ', ') AS CenterIDs FROM RmPurchaseRatesHistory 
        WHERE SType = '$Species' AND RmPriceDate = '$SubmissionDate'";
        $stmRatesCenter = $this->conn->prepare($sqlRatesCenters);
        $stmRatesCenter->execute();
        $dataRowCenters = $stmRatesCenter->fetch(PDO::FETCH_ASSOC);
        $Centers = $dataRowCenters['CenterIDs'];
        // echo 'Centers <br>';
        // echo $Centers;
        $PurchaseCenterArray = explode(', ', $PurchaseCenter);
        $CentersArray = explode(', ', $Centers);

        $ArrayPuchaseCenters = [];
        foreach ($PurchaseCenterArray as $item) {
            $individualValues = explode(',', $item);
            $ArrayPuchaseCenters = array_merge($ArrayPuchaseCenters, $individualValues);
        }
        $ArrayCenters = [];
        foreach ($CentersArray as $item) {
            $individualValues = explode(',', $item);
            $ArrayCenters = array_merge($ArrayCenters, $individualValues);
        }
        $MatchedValues = array_intersect($ArrayPuchaseCenters, $ArrayCenters);
        $MatchedString = implode(',', $MatchedValues);
        $MatchedStringTrim = trim($MatchedString);

        $sqlPurchaseCenters = "SELECT  GROUP_CONCAT(StationName SEPARATOR ', ') AS PurchaseCenters FROM RmPurchaseStations 
        WHERE PurchaseStationID IN ($MatchedStringTrim)";
        $stmPurchaseCenters = $this->conn->prepare($sqlPurchaseCenters);
        $stmPurchaseCenters->execute();
        $dataRowPurchaseCenters = $stmPurchaseCenters->fetch(PDO::FETCH_ASSOC);
        $PurchaseCenters = $dataRowPurchaseCenters['PurchaseCenters'];

        return $PurchaseCenters;
    }

    function getExistingCentersUpdate($RequestBody)
    {
        $PurchaseCenter = $RequestBody['PurchaseCenter'];
        $SubmissionDate = $RequestBody['SubmissionDate'];
        $Species = $RequestBody['Species'];
        $DateId = $RequestBody['DateId'];

        $sqlRatesCenters = "SELECT GROUP_CONCAT(CenterID SEPARATOR ', ') AS CenterIDs FROM RmPurchaseRatesHistory 
        WHERE SType = '$Species' AND RmPriceDate = '$SubmissionDate' AND ID != '$DateId'";
        $stmRatesCenter = $this->conn->prepare($sqlRatesCenters);
        $stmRatesCenter->execute();
        $dataRowCenters = $stmRatesCenter->fetch(PDO::FETCH_ASSOC);
        $Centers = $dataRowCenters['CenterIDs'];

        $PurchaseCenterArray = explode(', ', $PurchaseCenter);
        $CentersArray = explode(', ', $Centers);

        $ArrayPuchaseCenters = [];
        foreach ($PurchaseCenterArray as $item) {
            $individualValues = explode(',', $item);
            $ArrayPuchaseCenters = array_merge($ArrayPuchaseCenters, $individualValues);
        }
        $ArrayCenters = [];
        foreach ($CentersArray as $item) {
            $individualValues = explode(',', $item);
            $ArrayCenters = array_merge($ArrayCenters, $individualValues);
        }
        $MatchedValues = array_intersect($ArrayPuchaseCenters, $ArrayCenters);
        $MatchedString = implode(',', $MatchedValues);
        $MatchedStringTrim = trim($MatchedString);

        $sqlPurchaseCenters = "SELECT  GROUP_CONCAT(StationName SEPARATOR ', ') AS PurchaseCenters FROM RmPurchaseStations 
        WHERE PurchaseStationID IN ($MatchedStringTrim)";
        $stmPurchaseCenters = $this->conn->prepare($sqlPurchaseCenters);
        $stmPurchaseCenters->execute();
        $dataRowPurchaseCenters = $stmPurchaseCenters->fetch(PDO::FETCH_ASSOC);
        $PurchaseCenters = $dataRowPurchaseCenters['PurchaseCenters'];

        return $PurchaseCenters;
    }

    function getRmCountDetails($RequestBody)
    {
        $Species = $RequestBody['Species'];
        $DateId = $RequestBody['DateId'];

        if ($DateId != 0) {
            $sqlFields = "RateId,RmRR.RmCount,Status,SortOrder,RmRR.Stype,RmPrice,RmPriceDate, CenterID";

            $sqlQuery = "SELECT $sqlFields FROM `RmRatesRange` AS RmRR
            LEFT JOIN RmPurchaseRates AS RmPR ON RmRR.RmCount = RmPR.RmCount
            LEFT JOIN RmPurchaseRatesHistory AS RmPH ON RmPH.ID = RmPR.DateID
            WHERE RmRR.Stype='$Species' AND DateId='$DateId' ORDER BY CAST(RmRR.RmCount AS UNSIGNED) ASC";
        } else {
            $sqlFields = "RateId,RmCount,Status,SortOrder,Stype";

            $sqlQuery = "SELECT $sqlFields FROM `RmRatesRange` 
            WHERE Stype='$Species' ORDER BY CAST(RmCount AS UNSIGNED) ASC";
        }

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        $ArrayRmCountData = [];

        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ArrayRmCountList['RateId'] = $dataRow['RateId'];
            $ArrayRmCountList['RmCount'] = $dataRow['RmCount'];
            if ($DateId != 0) {
                $ArrayRmCountList['RmPrice'] = round($dataRow['RmPrice']);
                $ArrayRmCountList['RmPriceDate'] = $dataRow['RmPriceDate'];
                $ArrayRmCountList['CenterID'] = $dataRow['CenterID'];
            } else {
                $ArrayRmCountList['RmPrice'] = '';
                $ArrayRmCountList['RmPriceDate'] = '';
                $ArrayRmCountList['CenterID'] = '';
            }
            $ArrayRmCountList['Stype'] = $dataRow['Stype'];

            $ArrayRmCountData[] =  $ArrayRmCountList;
        }

        // $CentersData = $this->getPurchaseCenters($RequestBody);
        $data['Error'] = null;
        $data['Msg'] = 'Success';
        // $data['CentersData'] = $CentersData;
        $data['Data'] = $ArrayRmCountData;

        return $data;
    }


    // function getPurchaseCenters($RequestBody)
    // {
    //     $RmPriceDate = $RequestBody['RmPriceDate'];
    //     $Stype = $RequestBody['Species'];

    //     $sqlCheckCenter = "SELECT GROUP_CONCAT(CenterID SEPARATOR ',') AS CenterIDS FROM RmPurchaseRatesHistory 
    //     WHERE SType = '$Stype' AND RmPriceDate = '$RmPriceDate'";
    //     $stmtCheckCenter = $this->conn->prepare($sqlCheckCenter);
    //     $stmtCheckCenter->execute();
    //     $dataCheckCenter = $stmtCheckCenter->fetch(PDO::FETCH_ASSOC);
    //     $CenterID = $dataCheckCenter['CenterIDS'];

    //     $sqlFields = "PurchaseStationID,StationName";
    //     $sqlPurchaseCenter = "SELECT $sqlFields FROM `RmPurchaseStations`";
    //     $stmtPurchaseCenter = $this->conn->prepare($sqlPurchaseCenter);
    //     $stmtPurchaseCenter->execute();
    //     $ArrayCentersData = [];
    //     while ($dataCenters = $stmtPurchaseCenter->fetch(PDO::FETCH_ASSOC)) {
    //         $PurchaseStationID = $dataCenters['PurchaseStationID'];
    //         $CentersList['PurchaseStationID'] = $PurchaseStationID;
    //         $CentersList['StationName'] = $dataCenters['StationName'];
    //         if (strpos($CenterID, ",$PurchaseStationID,") !== false) {
    //             $CentersList['Rates'] = true; // Set Rates to True if matched
    //         } else {
    //             $CentersList['Rates'] = false; // Set Rates to False if not matched
    //         }
    //         $ArrayCentersData[] = $CentersList;
    //     }
    //     return $ArrayCentersData;
    // }
}
