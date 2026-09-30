<?php

include_once '../Global/Util.php';
include_once 'Controller/UtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class QualityCheckController
{
    private $conn;
    private $utilController;
    private $Error = null;
    private $Msg = '';
    private $auditLogger;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
        $this->auditLogger = new CentralAuditLogger($this->conn, 'QualityControl');
    }

    function getBatchData($RequestBody)
    {
        $ArrivalDate = $RequestBody['ArrivalDate'];
       $batchIdsQuery = "
    SELECT 
        rld.RmLotID AS RmArrivalID,
        rld.LotNumber AS ArrivalNumber,
        GROUP_CONCAT(DISTINCT pva.SentCount) AS SentCount,
        GROUP_CONCAT(DISTINCT PVL.ShortName) AS ShortName,
        GROUP_CONCAT(DISTINCT pva.VarietyID) AS VarietyID,
        pva.PlantID
    FROM RmLotDetatils rld 
    LEFT JOIN PpcValueAddition pva ON rld.RmLotID = pva.LotID
    LEFT JOIN RmArrivalDetatils RAD ON RAD.RmArrivalID = rld.RmReferenceID 
    LEFT JOIN PpcHLVAVarietyList PVL ON PVL.VarietyID = pva.VarietyID 
    WHERE pva.ProcessingDate = '$ArrivalDate' AND pva.VLID IS NOT NULL
    GROUP BY rld.LotNumber, rld.RmLotID, pva.PlantID;
";

        $stmtbBatchIds = $this->conn->prepare($batchIdsQuery);
        $stmtbBatchIds->execute();
        $batchIdsResultsData = [];
        $result = [];
        while ($dataBatchIdsRow = $stmtbBatchIds->fetch(PDO::FETCH_ASSOC)) {
            $batchIdsResults = [];
            $batchIdsResults['ArrivalId'] = $dataBatchIdsRow['RmArrivalID'];
            $batchIdsResults['ArrivalNumber'] = $dataBatchIdsRow['ArrivalNumber'];
            $batchIdsResults['PlantID'] = $dataBatchIdsRow['PlantID'];
            $batchIdsResults['VarietyID'] = $dataBatchIdsRow['VarietyID'];
            $batchIdsResults['SentCount'] = $dataBatchIdsRow['SentCount'];
            $batchIdsResults['Variety'] = $dataBatchIdsRow['ShortName'];
            $batchIdsResultsData[] = $batchIdsResults;
        }
        $result['Data'] = $batchIdsResultsData;
        return $result;
    }

    function getCountsData($RequestBody)
    {
        $ArrivalDate = $RequestBody['ArrivalDate'];
        $BatchCode = $RequestBody['BatchCode'];
        $Variety = $RequestBody['Variety'];
       $batchIdsQuery = "
    SELECT 
        pva.SentCount AS SentCount,
        pva.SentWeight AS SentWeight,
        PVL.ShortName AS ShortName,
        PGB.PackingGrade AS Grade,
        pva.WtAftrVA
    FROM PpcValueAddition pva
    LEFT JOIN RmLotDetatils rld ON rld.RmLotID = pva.LotID
    LEFT JOIN RmArrivalDetatils RAD ON RAD.RmArrivalID = rld.RmReferenceID 
    LEFT JOIN PpcHLGradingBreakup PGB ON PGB.DataID = pva.GradingBreakupID 
    LEFT JOIN PpcHLVAVarietyList PVL ON PVL.VarietyID = pva.VarietyID 
    WHERE pva.ProcessingDate = '$ArrivalDate' AND pva.VLID IS NOT NULL AND pva.LotID IN ($BatchCode) AND PVL.ShortName='$Variety'
";
// echo $batchIdsQuery;exit;

        $stmtbBatchIds = $this->conn->prepare($batchIdsQuery);
        $stmtbBatchIds->execute();
        $result = [];
        $groupedData = [];

        while ($row = $stmtbBatchIds->fetch(PDO::FETCH_ASSOC)) {
            $sentCount = $row['SentCount'];
            $sentWeight = $row['SentWeight'];
            $grade = $row['Grade'];
            $wtAftrVA = $row['WtAftrVA'];

            // Grouping by SentCount
            if (!isset($groupedData[$sentCount])) {
                $groupedData[$sentCount] = [
                    'SentCount' => $sentCount,
                    'WeightsGrades' => [],
                    'Variety' => $row['ShortName'],
                ];
            }

            $groupedData[$sentCount]['WeightsGrades'][] = $sentWeight . '@' . $grade. '@' . $wtAftrVA;
        }

        // Format final output
        $batchIdsResultsData = [];

        foreach ($groupedData as $entry) {
            $batchIdsResultsData[] = [
                'SentCount' => $entry['SentCount'],
                'WeightWithGrade' => implode(', ', $entry['WeightsGrades']),
                'Variety' => $entry['Variety']
            ];
        }
        $result['Data'] = $batchIdsResultsData;
        return $result;
    } 
    
    function getQuantityCheckData($RequestBody)
    {
        // echo print_r($RequestBody);
        // exit;

        $ArrivalDate = $RequestBody['ArrivalDate'];
        // $BatchCode = $RequestBody['BatchCode'];

        $sql = "SELECT DCL.*,DCL.ArrivalDate AS adate,DCL.Grade as GradeName,
        -- QIT1.Name AS ImproperPeelingName,
        QIT2.Name AS TypeOfCutName,QIT3.Name AS VeinName,QIT4.Name AS BrokenName,
        QIT5.Name AS OtherObjectionDefectsName,QIT6.Name AS CorrectiveActionName,
         RLD.LotNumber  FROM QualityCheckValue AS DCL
        LEFT JOIN RmArrivalDetatils as AR on AR.RmArrivalID=DCL.BatchCode
        LEFT JOIN RmLotDetatils as RLD on RLD.RmLotID=DCL.BatchCode
        -- LEFT JOIN QcImproperTypes AS QIT1 ON QIT1.ID=DCL.ImproperPeeling
        LEFT JOIN QcImproperTypes AS QIT2 ON QIT2.ID=DCL.TypeOfCut
        LEFT JOIN QcImproperTypes AS QIT3 ON QIT3.ID=DCL.Vein
        LEFT JOIN QcImproperTypes AS QIT4 ON QIT4.ID=DCL.Broken
        LEFT JOIN QcImproperTypes AS QIT5 ON QIT5.ID=DCL.OtherObjectionDefects
        LEFT JOIN QcImproperTypes AS QIT6 ON QIT6.ID=DCL.CorrectiveAction
        --  LEFT JOIN QcImproperTypes AS QIT7 ON QIT7.ID=DCL.ImproperCutting
        WHERE DCL.ArrivalDate='$ArrivalDate'";

        $stmt = $this->conn->prepare($sql);
        
        $stmt->execute();

        $i = 0;
        $QuantityData = [];

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $QualityList['SNO'] = ++$i;
            $QualityList['QuantityCheckID'] = $row['QuantityCheckID'];
            $QualityList['BatchCode'] = $row['BatchCode'];
            $QualityList['BatchNoType'] = $row['BatchNoType'];
            $QualityList['ArrivalNumber'] = !empty($row['BatchNoType']) && $row['BatchNoType'] == 'Arrival'?$row['BatchNoType'].'-'.$row['LotNumber']:$row['BatchNoType'].'-'.$row['ReglazeBatchNo'];
            $QualityList['ArrivalDate'] = $row['adate'];
            $QualityList['RoomTemperature'] = $row['RoomTemperature'];
            $QualityList['TableNo'] = $row['TableNo'];
            $QualityList['Variety'] = $row['Variety'];
            $QualityList['Grade'] = $row['GradeName'];
            $QualityList['TypeOfCut'] = $row['TypeOfCutName'];
            $QualityList['ImproperPeeling'] = $row['ImproperPeeling'];
            $QualityList['ImproperCutting'] = $row['ImproperCutting'];
            $QualityList['Vein'] = $row['Vein'];
            $QualityList['Broken'] = $row['Broken'];
            $QualityList['OtherObjectionDefects'] = $row['OtherObjectionDefects'];
            $QualityList['GradeId'] = $row['Grade'];
            $QualityList['TypeOfCutId'] = $row['TypeOfCut'];
            $QualityList['TotalPeelingGirls'] = $row['TotalPeelingGirls'];
            $QualityList['Quantity'] = $row['Quantity'];
            $QualityList['Yield'] = $row['Yield'];
            $QualityList['CheckingGirls'] = $row['CheckingGirls'];
            $QualityList['SuperVisor'] = $row['SuperVisor'];
            $QualityList['VerifiedBy'] = $row['VerifiedBy'];
            $QualityList['Remarks'] = $row['Remarks'];
            $QualityList['CorrectiveAction'] = $row['CorrectiveActionName'];
             $QualityList['CorrectiveActionId'] = $row['CorrectiveAction'];
             $QualityList['HeadLessQuantity'] = $row['HeadLessQuantity'];
              $QualityList['CheckedBy'] = $row['CheckedBy'];
              $QualityList['InspectedBy'] = $row['InspectedBy'];
              $QualityList['ValueAdditionDate'] = $row['ValueAdditionDate'];
            $QualityList['ReglazeBatchNo'] = $row['ReglazeBatchNo'];
            $QualityList['TableTemperature'] = $row['TableTemperature'];
            $QualityList['CrateTemperature'] = $row['CrateTemperature'];
            $QualityList['NumberSamplesChecked'] = $row['NumberSamplesChecked'];
             $QualityList['VAStation'] = $row['VAStation'];
              



            $QuantityData[] = $QualityList;
        }

        $data['Error'] = '';
        $data['Msg'] = 'Success';
        $data['QualityData'] = $QuantityData;


        return $data;
    }
    function postQuantityChecktData($RequestBody)
    {
        //      echo print_r($RequestBody);
        // exit;
        $QuantityCheckID = $RequestBody['QuantityCheckid'];
        $ArrivalDate = $RequestBody['ArrivalDate'];
        $BatchCode1 = $RequestBody['BatchCode'];
        $RoomTemperature = $RequestBody['RoomTemperature'];
        $TableNo = $RequestBody['TableNo'];
        $Variety = $RequestBody['Variety'];
        $Grade = $RequestBody['Grade'];
        $TypeOfCut = $RequestBody['TypeOfCut'];
        // $ImproperPeeling = $RequestBody['ImproperPeeling'];
        // $ImproperCutting = $RequestBody['ImproperCutting'];
        $ImproperPeeling = $RequestBody['ImproperPeeling'];
       $ImproperCutting = $RequestBody['ImproperCutting'];

        $Vein = $RequestBody['Vein'];
        $Broken = $RequestBody['Broken'];
        $OtherObjectionDefects = $RequestBody['OtherObjectionDefects'];
        $TotalPeelingGirls = $RequestBody['TotalPeelingGirls'];
        $HeadLessQuantity = $RequestBody['HeadLessQuantity'];
        $Quantity = $RequestBody['Quantity'];
        $Yield = $RequestBody['Yield'];
        $CheckingGirls = $RequestBody['CheckingGirls'];
        $SuperVisor = $RequestBody['SuperVisor'];
        $VerifiedBy = $RequestBody['VerifiedBy'];
        $Remarks = $RequestBody['Remarks'];
        $CorrectiveAction = $RequestBody['CorrectiveAction'];
        $HLCount = $RequestBody['HLCount'];
        $InspectedBy = $RequestBody['InspectedBy'];
        $ValueAdditionDate = $RequestBody['ValueAdditionDate'];
        $CheckedBy = $RequestBody['CheckedBy'];
        $ReglazeBatchNo = $RequestBody['ReglazeBatchNo'];
        $TableTemperature= $RequestBody['TableTemperature'];
        $CrateTemperature = $RequestBody['CrateTemperature'];
        $NumberSamplesChecked= $RequestBody['NumberSamplesChecked'];
        $BatchNoType= $RequestBody['BatchNoType'];
         $VAStation= $RequestBody['VAStation'];

        $BatchCodeArray = explode(',', $BatchCode1);

     if (!empty($QuantityCheckID)) {
     $existingRecord = $this->auditLogger->fetchRowById('QualityCheckValue', 'QuantityCheckID', $QuantityCheckID);
     foreach ($BatchCodeArray as $BatchCode) {
    $sqlQuery = "UPDATE QualityCheckValue SET
    BatchCode = '$BatchCode',
    ArrivalDate = '$ArrivalDate',
    RoomTemperature = '$RoomTemperature',
    TableNo = '$TableNo',
    Variety = '$Variety',
    Grade = '$Grade',
    TypeOfCut = '$TypeOfCut',
    ImproperPeeling = '$ImproperPeeling',
    ImproperCutting = '$ImproperCutting',
    Vein = '$Vein',
    Broken = '$Broken',
    OtherObjectionDefects = '$OtherObjectionDefects',
    TotalPeelingGirls = '$TotalPeelingGirls',
    HeadLessQuantity = '$HeadLessQuantity',
    Quantity = '$Quantity',
    Yield = '$Yield',
    CheckingGirls = '$CheckingGirls',
    SuperVisor = '$SuperVisor',
    VerifiedBy = '$VerifiedBy',
    Remarks = '$Remarks',
    CorrectiveAction = '$CorrectiveAction',
    HLCount = '$HLCount',
    InspectedBy = '$InspectedBy',
    ValueAdditionDate = '$ValueAdditionDate',
    CheckedBy = '$CheckedBy',
    ReglazeBatchNo = '$ReglazeBatchNo',
    TableTemperature = '$TableTemperature',
    CrateTemperature = '$CrateTemperature',
    NumberSamplesChecked = '$NumberSamplesChecked',
    BatchNoType='$BatchNoType',
      VAStation='$VAStation'
    

    WHERE QuantityCheckID = '$QuantityCheckID'";
                try {
                    $stmt = $this->conn->prepare($sqlQuery);
                   $result = $stmt->execute([
                ':BatchCode' => $BatchCode,
                ':ArrivalDate' => $ArrivalDate,
                ':RoomTemperature' => $RoomTemperature,
                ':TableNo' => $TableNo,
                ':Variety' => $Variety,
                ':Grade' => $Grade,
                ':TypeOfCut' => $TypeOfCut,
                ':ImproperPeeling' => $ImproperPeeling,
                ':ImproperCutting' => $ImproperCutting,
                ':Vein' => $Vein,
                ':Broken' => $Broken,
                ':OtherObjectionDefects' => $OtherObjectionDefects,
                ':TotalPeelingGirls' => $TotalPeelingGirls,
                ':HeadLessQuantity' => $HeadLessQuantity,
                ':Quantity' => $Quantity,
                ':Yield' => $Yield,
                ':CheckingGirls' => $CheckingGirls,
                ':SuperVisor' => $SuperVisor,
                ':VerifiedBy' => $VerifiedBy,
                ':Remarks' => $Remarks,
                ':CorrectiveAction' => $CorrectiveAction,
                ':HLCount' => $HLCount,
                ':InspectedBy' => $InspectedBy,
                ':ValueAdditionDate' => $ValueAdditionDate,
                ':QuantityCheckID' => $QuantityCheckID,
                ':CheckedBy' => $CheckedBy,
                ':ReglazeBatchNo' => $ReglazeBatchNo,
                ':TableTemperature' => $TableTemperature,
                ':CrateTemperature' => $CrateTemperature,
                ':NumberSamplesChecked' => $NumberSamplesChecked,
                ':BatchNoType'=>$BatchNoType,
                 ':VAStation'=>$VAStation
                
                
            ]);

                    if ($result) {
                        $updatedRecord = $this->auditLogger->fetchRowById('QualityCheckValue', 'QuantityCheckID', $QuantityCheckID);
                        $this->auditLogger->logUpdate('QualityCheckValue', $QuantityCheckID, $existingRecord, $updatedRecord, $RequestBody, [
                            'ReferenceNo' => $QuantityCheckID,
                            'Remarks' => 'QC quantity check updated',
                        ]);
                        return [
                            'Error' => '',
                            'Msg' => 'Data saved successfully',
                            'Data' => $this->conn->lastInsertId()
                        ];
                    } else {
                        $errorInfo = $stmt->errorInfo();
                        return [
                            'Error' => 'Insert failed: ' . $errorInfo[2],
                            'Msg' => 'Failed to insert record',
                            'Data' => null
                        ];
                    }
                } catch (PDOException $e) {
                    return [
                        'Error' => 'Exception: ' . $e->getMessage(),
                        'Msg' => 'Failed to insert record',
                        'Data' => null
                    ];
                }
            }
        } else {
            foreach ($BatchCodeArray as $BatchCode) {
                $sqlQuery = "INSERT INTO QualityCheckValue  (
     ArrivalDate,BatchCode, RoomTemperature,`TableNo`, `Variety`, `Grade`,`TypeOfCut`,
    ImproperPeeling, ImproperCutting, Vein, Broken, OtherObjectionDefects, TotalPeelingGirls,HeadLessQuantity,Quantity,Yield,
    CheckingGirls,SuperVisor,VerifiedBy,Remarks,CorrectiveAction,InspectedBy,ValueAdditionDate,CheckedBy,ReglazeBatchNo,TableTemperature,CrateTemperature,NumberSamplesChecked,BatchNoType,VAStation) VALUES (
          '$ArrivalDate',  '$BatchCode', '$RoomTemperature', '$TableNo', '$Variety', '$Grade','$TypeOfCut',
            '$ImproperPeeling', '$ImproperCutting', '$Vein', '$Broken', '$OtherObjectionDefects', '$TotalPeelingGirls', '$HeadLessQuantity',
       '$Quantity', '$Yield', '$CheckingGirls', '$SuperVisor', '$VerifiedBy','$Remarks','$CorrectiveAction','$InspectedBy','$ValueAdditionDate','$CheckedBy','$ReglazeBatchNo','$TableTemperature','$CrateTemperature','$NumberSamplesChecked','$BatchNoType','$VAStation')";

                try {
                    $stmt = $this->conn->prepare($sqlQuery);
                    $result = $stmt->execute([
                        ':BatchCode' => $BatchCode,
                        ':ArrivalDate' => $ArrivalDate,
                        ':RoomTemperature' => $RoomTemperature,
                        ':TableNo' => $TableNo,
                        ':Variety' => $Variety,
                        ':Grade' => $Grade,
                        ':TypeOfCut' => $TypeOfCut,
                        ':ImproperPeeling' => $ImproperPeeling,
                        ':ImproperCutting' => $ImproperCutting,
                        ':Vein' => $Vein,
                        ':Broken' => $Broken,
                        ':OtherObjectionDefects' => $OtherObjectionDefects,
                        ':TotalPeelingGirls' => $TotalPeelingGirls,
                        ':HeadLessQuantity' => $HeadLessQuantity,
                        ':Quantity' => $Quantity,
                        ':Yield' => $Yield,
                        ':CheckingGirls' => $CheckingGirls,
                        ':SuperVisor' => $SuperVisor,
                        ':VerifiedBy' => $VerifiedBy,
                        ':Remarks' => $Remarks,
                        ':CorrectiveAction' => $CorrectiveAction,
                        ':HLCount' => $HLCount,
                        ':InspectedBy' => $InspectedBy,
                        ':ValueAdditionDate' => $ValueAdditionDate,
                        ':CheckedBy' => $CheckedBy,
                        ':ReglazeBatchNo' => $ReglazeBatchNo,
                        ':TableTemperature' => $TableTemperature,
                        ':CrateTemperature' => $CrateTemperature,
                        ':NumberSamplesChecked' => $NumberSamplesChecked, 
                        ':BatchNoType'=>$BatchNoType,
                         ':VAStation'=>$VAStation
                    ]);
                    if ($result) {
                        $lastInsertId = $this->conn->lastInsertId();
                        $createdRecord = $this->auditLogger->fetchRowById('QualityCheckValue', 'QuantityCheckID', $lastInsertId);
                        $this->auditLogger->logInsert('QualityCheckValue', $lastInsertId, $createdRecord, $RequestBody, [
                            'ReferenceNo' => $lastInsertId,
                            'Remarks' => 'QC quantity check created',
                        ]);
                        return [
                            'Error' => '',
                            'Msg' => 'Data saved successfully',
                            'Data' => $this->conn->lastInsertId()
                        ];
                    } else {
                        $errorInfo = $stmt->errorInfo();
                        return [
                            'Error' => 'Insert failed: ' . $errorInfo[2],
                            'Msg' => 'Failed to insert record',
                            'Data' => null
                        ];
                    }
                } catch (PDOException $e) {
                    return [
                        'Error' => 'Exception: ' . $e->getMessage(),
                        'Msg' => 'Failed to insert record',
                        'Data' => null
                    ];
                }
            }
        }
    }
    function deleteQuantityChecktData($RequestBody)
    {
        $QuantityCheckID = $RequestBody['QuantityCheckid'];
        $existingRecord = $this->auditLogger->fetchRowById('QualityCheckValue', 'QuantityCheckID', $QuantityCheckID);

        $sqlDelete = "DELETE FROM `QualityCheckValue` WHERE QuantityCheckid = $QuantityCheckID";

        // echo ($sqlDelete);
        // exit;
        try {
            $stmt = $this->conn->prepare($sqlDelete);
            $result = $stmt->execute();

            if ($result) {
                $this->auditLogger->logDelete('QualityCheckValue', $QuantityCheckID, $existingRecord, $RequestBody, [
                    'ReferenceNo' => $QuantityCheckID,
                    'Remarks' => 'QC quantity check deleted',
                ]);
                return [
                    'Error' => '',
                    'Msg' => 'Data deleted successfully',
                    'Data' => $QuantityCheckID
                ];
            } else {
                $errorInfo = $stmt->errorInfo();
                return [
                    'Error' => 'Delete failed: ' . $errorInfo[2],
                    'Msg' => 'Failed to delete record',
                    'Data' => null
                ];
            }
        } catch (PDOException $e) {
            return [
                'Error' => 'Exception: ' . $e->getMessage(),
                'Msg' => 'Failed to delete record',
                'Data' => null
            ];
        }


    }


}
