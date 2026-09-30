<?php

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
class UtilController
{
    private $conn;
    private $auditLogger;

    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->auditLogger = new CentralAuditLogger($this->conn, 'ColdStore');
    }
    // GET ALL
    public function getCsStockAvalWeightinKgs($PackingStyleID, $AvalMcs, $LooseBags)
    {


        $sqlKgsQuery = "SELECT TotalSlabs,SlabWeight,MFactor,Uom,LbsFactor FROM `AdminPackingStyles` WHERE PCKID='$PackingStyleID'";
        $stmtKgsQuery = $this->conn->prepare($sqlKgsQuery);
        $stmtKgsQuery->execute();
        $RowStockData = $stmtKgsQuery->fetch(PDO::FETCH_ASSOC);

        if ($RowStockData['Uom'] == 'KGS' || $RowStockData['Uom'] == 'GRAMS') {
            $ActualKgs = ($AvalMcs * $RowStockData['TotalSlabs'] + $LooseBags) * $RowStockData['SlabWeight'] * $RowStockData['MFactor'];
        } else {
            $ActualKgs = ($AvalMcs * $RowStockData['TotalSlabs'] + $LooseBags) * $RowStockData['SlabWeight'] * $RowStockData['LbsFactor'] * 0.454;
        }

        // $McsKgs = $AvalMcs * $RowStockData['TotalSlabs'] * $RowStockData['SlabWeight'] * $RowStockData['MFactor'];
        // $LooseKgs = $LooseBags * $RowStockData['SlabWeight'] * $RowStockData['MFactor'];
        // $ActualKgs = $McsKgs + $LooseKgs;
        return $ActualKgs;
    }

    public function getFinancialYear($FinClsYear)
    {
        $DateStart = $FinClsYear . '-04-01';
        $DateEnd = ($FinClsYear + 1) . '-03-31';

        return $DateFilter = "BETWEEN '$DateStart' and '$DateEnd'";

    }

    public function getFormatedDatelines($DATE)
    {

        $TIME_STAMP = strtotime($DATE);
        $formated_date = date("d-m-Y", $TIME_STAMP);
        if ($formated_date == '01-01-1970') {
            $formated_date = '--';
        }
        return $formated_date;
    }

    function getFormatedDateEmpty($RawDate)
    {
        $RawDate = date("d-m-Y", strtotime($RawDate));
        if ($RawDate == '01-01-1970') {
            $RawDate = '';
        }
        return $RawDate;
    }

    function UpdateTrackWiseThawingMeatWeight($TrackID)
    {
        $sqlThawingMeatCount = "SELECT count(*) as TrackCount FROM `RmReceivingDetails`  WHERE ReceivingID= '$TrackID'and IsStoreMaterial=1 ";
        $stmtThawingMeatCount = $this->conn->prepare($sqlThawingMeatCount);
        $stmtThawingMeatCount->execute();
        $dataRowThawingMeatCount = $stmtThawingMeatCount->fetch(PDO::FETCH_ASSOC);
        $TrackCount = $dataRowThawingMeatCount['TrackCount'];
        if ($TrackCount > 0) {
//             $sqlUpdateThawingMeatQuery = " UPDATE RmReceivingDetails AS RRD JOIN ( SELECT RRD.ReceivingID as TrackID, SUM(RRB.ReceivingQty) AS TotalThawingWeight
//     FROM RmReceivingDetails as RRD  LEFT JOIN RmReceivingBreakup AS RRB ON RRB.ReceivingID = RRD.ReceivingID
//      WHERE RRD.ReceivingID IN ($TrackID) and IsStoreMaterial=1 and OutStage=1  GROUP BY RRD.ReceivingID ) AS GD    ON RRD.ReceivingID = '$TrackID'
//      SET RRD.TrackWiseThawingMeatQuantity = COALESCE(GD.TotalThawingWeight - ( SELECT COALESCE(SUM(SentWeight),0)
//    FROM PpcValueAddition WHERE FIND_IN_SET(TrackID, GD.TrackID) ), GD.TotalThawingWeight)";

  $sqlUpdateThawingMeatQuery = "UPDATE RmReceivingDetails AS RRD
JOIN (
    SELECT RRD.ReceivingID AS TrackID,
           SUM(RRB.ReceivingQty) AS TotalThawingWeight
    FROM RmReceivingDetails AS RRD
    LEFT JOIN RmReceivingBreakup AS RRB
           ON RRB.ReceivingID = RRD.ReceivingID
    WHERE RRD.ReceivingID IN ($TrackID)
      AND IsStoreMaterial = 1
    GROUP BY RRD.ReceivingID
) AS GD ON RRD.ReceivingID = GD.TrackID
SET RRD.TrackWiseThawingMeatQuantity = (
    CASE
        WHEN RRD.OutStage = 0 THEN
            GD.TotalThawingWeight - (
                SELECT COALESCE(SUM(SentWeight), 0)
                FROM PpcValueAddition
                WHERE FIND_IN_SET(GD.TrackID, TrackID)
            )
        WHEN RRD.OutStage = 1 THEN
            GD.TotalThawingWeight - (
                SELECT COALESCE(SUM(SoakInQty), 0)
                FROM ProdSoakingDetails
                WHERE FIND_IN_SET(GD.TrackID, TrackID)
            )
        WHEN RRD.OutStage = 2 THEN
           GD.TotalThawingWeight - (
                SELECT COALESCE(SUM(ProductionWeight+RejectionWeight), 0)
                FROM ProdFreezingData
                WHERE FIND_IN_SET(GD.TrackID, TrackID)
            )
        ELSE GD.TotalThawingWeight
    END
)";


            $stmtUpdateThawingMeatQuery = $this->conn->prepare($sqlUpdateThawingMeatQuery);
            $stmtUpdateThawingMeatQuery->execute();
        }
        return "success";
    }

    function UpdateTrackWiseBlockMeatWeight($TrackID, $RequestBody = array())
    {

        $SqlFreezingBlockWeight = "SELECT     PHGD.TrackID,    GROUP_CONCAT(PHGB.DataID) AS AllDataIDs,    ( SELECT COALESCE(DataID ,'')
        FROM   PpcHLGradingBreakup   WHERE    IsBlock = 1   AND Treatment != 'UNT'  AND FIND_IN_SET(DataID, PHGB.DataID)
    ) AS SoakingBreakUpID,    (   SELECT  COALESCE(sum(HLWeight) ,0)  FROM   PpcHLGradingBreakup   WHERE  IsBlock = 1    AND Treatment != 'UNT'
            AND  FIND_IN_SET(DataID, PHGB.DataID)    ) AS SoakingStageQuantity,    (  SELECT  COALESCE(DataID ,'') FROM  PpcHLGradingBreakup
        WHERE   IsBlock = 1  AND Treatment = 'UNT'  AND FIND_IN_SET(DataID, PHGB.DataID)    ) AS FreezingBreakUpID    , (   SELECT
           COALESCE(sum(HLWeight) ,0)   FROM      PpcHLGradingBreakup   WHERE   IsBlock = 1  AND Treatment = 'UNT' AND    FIND_IN_SET(DataID, PHGB.DataID)
    ) AS FreezingStageQuantity , (   SELECT  COALESCE(sum(SoakInQty) ,0)   FROM  ProdSoakingDetails  WHERE    FIND_IN_SET(VABreakupID, PHGB.DataID)
    ) AS SoakingBlockQuantity   , ( SELECT   COALESCE(sum(ProductionWeight + RejectionWeight) ,0) FROM  ProdFreezingData  WHERE    FIND_IN_SET(GradingBreakupID, PHGB.DataID) and GradingBreakupID not in (SELECT distinct VABreakupID FROM ProdSoakingDetails where    FIND_IN_SET(VABreakupID, PHGB.DataID))
    ) AS FreezingBlockQuantity       FROM     PpcHLGradingDetails AS PHGD LEFT JOIN     PpcHLGradingBreakup AS PHGB     ON PHGB.LotID = PHGD.LotID
  WHERE   IsBlock = 1 and PHGD.TrackID in ($TrackID )   GROUP BY     PHGD.TrackID,PHGB.DataID";

        $stmtFreezingBlockWeight = $this->conn->prepare($SqlFreezingBlockWeight);
        $stmtFreezingBlockWeight->execute();

        while ($dataRowFreezingBlockWeight = $stmtFreezingBlockWeight->fetch(PDO::FETCH_ASSOC)) {
            $GradingBreakUpID = $dataRowFreezingBlockWeight['AllDataIDs'];
            $SoakingStageQuantity = $dataRowFreezingBlockWeight['SoakingStageQuantity']; //nothing but gradingweight
            $SoakingBlockQuantity = $dataRowFreezingBlockWeight['SoakingBlockQuantity'];
            $FreezingStageQuantity = $dataRowFreezingBlockWeight['FreezingStageQuantity'];  //nothing but gradingweight
            $FreezingBlockQuantity = $dataRowFreezingBlockWeight['FreezingBlockQuantity'];

            $TotalBalanceSoakingBlockMeatBreakUpwise = $SoakingStageQuantity - $SoakingBlockQuantity;
            $TotalBalanceFreezingBlockMeatBreakUpwise = $FreezingStageQuantity - $FreezingBlockQuantity;
            if ($TotalBalanceFreezingBlockMeatBreakUpwise < 1) {
                $TotalBalanceFreezingBlockMeatBreakUpwise = 0;

            }
            if ($TotalBalanceSoakingBlockMeatBreakUpwise < 1) {
                $TotalBalanceSoakingBlockMeatBreakUpwise = 0;

            }

            $SqlUpdateBlockWeights = "UPDATE `PpcHLGradingBreakup` SET `SoakingStageBlockWeight`='$TotalBalanceSoakingBlockMeatBreakUpwise',
        `FreezingStageBlockWeight`='$TotalBalanceFreezingBlockMeatBreakUpwise' WHERE `DataID`='$GradingBreakUpID'";
            $oldGradingRow = $this->auditLogger->fetchRowById('PpcHLGradingBreakup', 'DataID', $GradingBreakUpID);
            $stmtUpdateBlockWeights = $this->conn->prepare($SqlUpdateBlockWeights);
            $stmtUpdateBlockWeights->execute();
            $this->auditLogger->logUpdate('PpcHLGradingBreakup', $GradingBreakUpID, $oldGradingRow, $this->auditLogger->fetchRowById('PpcHLGradingBreakup', 'DataID', $GradingBreakUpID), $RequestBody);

        }

        $oldRmRow = $this->auditLogger->fetchRowById('RmReceivingDetails', 'ReceivingID', $TrackID);
        $SqlUpdateRmReceivingDetails = "UPDATE RmReceivingDetails AS RRD JOIN (  SELECT   PHGD.TrackID,  SUM(PHGB.SoakingStageBlockWeight + PHGB.FreezingStageBlockWeight) AS TotalBlockMeatQuantity
    FROM   PpcHLGradingDetails AS PHGD    LEFT JOIN  PpcHLGradingBreakup AS PHGB  ON PHGB.LotID = PHGD.LotID    WHERE   PHGD.TrackID IN ($TrackID)
    GROUP BY   PHGD.TrackID) AS SubQuery ON RRD.ReceivingID = '$TrackID' SET     RRD.TrackWiseBlockMeatQuantity = SubQuery.TotalBlockMeatQuantity";

        $stmtUpdateRmReceivingDetails = $this->conn->prepare($SqlUpdateRmReceivingDetails);
        $stmtUpdateRmReceivingDetails->execute();
        $this->auditLogger->logUpdate('RmReceivingDetails', $TrackID, $oldRmRow, $this->auditLogger->fetchRowById('RmReceivingDetails', 'ReceivingID', $TrackID), $RequestBody);

        return "success";

    }

}

?>
