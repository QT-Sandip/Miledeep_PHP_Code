<?php
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($_SERVER['DOCUMENT_ROOT'] . "/includes/Globals.php");

$StartDate = '2024-12-01';
$EndDate = date("Y-m-d ");
$ReceivingPlantID = 2;
// $ChillRoomPreparationType = $RequestBody['ChillRoomPreparationType'];
$Query = '';
$DateFilter = "RmRD.ArrivalDate BETWEEN '$StartDate' AND '$EndDate'";
if ($ReceivingPlantID != '') {
    $Query .= " AND RmRD.ReceivingPlantID='$ReceivingPlantID'";
}

$BlockMeat = UpdateTrackWiseBlockMeatWeight($DateFilter, $Query, $iconn);
// $CookedMeat = UpdateTrackWiseCookedMeatWeight($DateFilter, $Query, $iconn);
// $RawMeat = UpdateTrackWiseRawMeatWeight($DateFilter, $Query, $iconn);
// $RawHL = UpdateTrackWiseRawHLWeight($DateFilter, $Query, $iconn);
// $ThawingMeat = UpdateTrackWiseThawingMeatWeight($DateFilter, $Query, $iconn);
/***BlockMeat** */
function UpdateTrackWiseBlockMeatWeight($DateFilter, $Query, $iconn)
{
    echo $sqlGetBlockMeatQuery = "SELECT group_concat(ReceivingID) as TrackId FROM `RmReceivingDetails` as RmRD WHERE $DateFilter $Query 
        and IsStoreMaterial=0 and OutStage=0  and ReceivingID in (SELECT distinct TrackID FROM `PpcHLGradingDetails` as PHGD
         left join PpcHLGradingBreakup as PHGB on PHGB.LotID=PHGD.LotID WHERE IsBlock=1)";
    exit;

    $stmtGetBlockMeatQuery = $iconn->query($sqlGetBlockMeatQuery);

    $dataRowGetBlockMeatQuery = $stmtGetBlockMeatQuery->fetch_array();
    $TrackID = $dataRowGetBlockMeatQuery['TrackId'];

    $SqlFreezingBlockWeight = "SELECT     PHGD.TrackID,    GROUP_CONCAT(PHGB.DataID) AS AllDataIDs,    ( SELECT COALESCE(DataID ,'')
        FROM   PpcHLGradingBreakup   WHERE    IsBlock = 1   AND Treatment != 'UNT'  AND FIND_IN_SET(DataID, PHGB.DataID)
    ) AS SoakingBreakUpID,    (   SELECT  COALESCE(sum(HLWeight) ,0)  FROM   PpcHLGradingBreakup   WHERE  IsBlock = 1    AND Treatment != 'UNT' 
            AND  FIND_IN_SET(DataID, PHGB.DataID)    ) AS SoakingStageQuantity,    (  SELECT  COALESCE(DataID ,'') FROM  PpcHLGradingBreakup 
        WHERE   IsBlock = 1  AND Treatment = 'UNT'  AND FIND_IN_SET(DataID, PHGB.DataID)    ) AS FreezingBreakUpID    , (   SELECT 
           COALESCE(sum(HLWeight) ,0)   FROM      PpcHLGradingBreakup   WHERE   IsBlock = 1  AND Treatment = 'UNT' AND    FIND_IN_SET(DataID, PHGB.DataID)
    ) AS FreezingStageQuantity , (   SELECT  COALESCE(sum(SoakInQty) ,0)   FROM  ProdSoakingDetails  WHERE    FIND_IN_SET(VABreakupID, PHGB.DataID)
    ) AS SoakingBlockQuantity   , ( SELECT   COALESCE(sum(ProductionWeight) ,0) FROM  ProdFreezingData  WHERE    FIND_IN_SET(GradingBreakupID, PHGB.DataID) and GradingBreakupID not in (SELECT distinct VABreakupID FROM ProdSoakingDetails where    FIND_IN_SET(VABreakupID, PHGB.DataID))
    ) AS FreezingBlockQuantity       FROM     PpcHLGradingDetails AS PHGD LEFT JOIN     PpcHLGradingBreakup AS PHGB     ON PHGB.LotID = PHGD.LotID
  WHERE   IsBlock = 1 and PHGD.TrackID in ($TrackID )   GROUP BY     PHGD.TrackID,PHGB.DataID;";


    $stmtFreezingBlockWeight = $iconn->query($SqlFreezingBlockWeight);

    while ($dataRowFreezingBlockWeight = $stmtFreezingBlockWeight->fetch_array()) {
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

        echo $SqlUpdateBlockWeights = "UPDATE `PpcHLGradingBreakup` SET `SoakingStageBlockWeight`='$TotalBalanceSoakingBlockMeatBreakUpwise',
        `FreezingStageBlockWeight`='$TotalBalanceFreezingBlockMeatBreakUpwise' WHERE `DataID`='$GradingBreakUpID'";
        $stmtUpdateBlockWeights = $iconn->query($SqlUpdateBlockWeights);


    }


    $SqlUpdateRmReceivingDetails = "UPDATE RmReceivingDetails AS RRD JOIN (  SELECT   PHGD.TrackID,  SUM(PHGB.SoakingStageBlockWeight + PHGB.FreezingStageBlockWeight) AS TotalBlockMeatQuantity
    FROM   PpcHLGradingDetails AS PHGD    LEFT JOIN  PpcHLGradingBreakup AS PHGB  ON PHGB.LotID = PHGD.LotID    WHERE   PHGD.TrackID IN ($TrackID)
    GROUP BY   PHGD.TrackID) AS SubQuery ON RRD.ReceivingID = SubQuery.TrackID SET     RRD.TrackWiseBlockMeatQuantity = SubQuery.TotalBlockMeatQuantity";

    $stmtUpdateRmReceivingDetails = $iconn->query($SqlUpdateRmReceivingDetails);

    return "success";

}

// function UpdateTrackWiseBlockMeatWeight($DateFilter, $Query)
// {
//     $sqlGetBlockMeatQuery = "SELECT group_concat(ReceivingID) as TrackId FROM `RmReceivingDetails` as RmRD WHERE $DateFilter $Query 
//         and IsStoreMaterial=0 and OutStage=0  and ReceivingID in (SELECT distinct TrackID FROM `PpcHLGradingDetails` as PHGD
//          left join PpcHLGradingBreakup as PHGB on PHGB.LotID=PHGD.LotID WHERE IsBlock=1)";



//     $stmtGetBlockMeatQuery = $this->conn->prepare($sqlGetBlockMeatQuery);

//     $stmtGetBlockMeatQuery->execute();
//     // $dataRowGetBlockMeatQuery = $stmtGetBlockMeatQuery->fetch_array();
//     $dataRowGetBlockMeatQuery = $stmtGetBlockMeatQuery->fetch(PDO::FETCH_ASSOC);
//     $TrackID = $dataRowGetBlockMeatQuery['TrackId'];

//     $SqlFreezingBlockWeight = "SELECT     PHGD.TrackID,    GROUP_CONCAT(PHGB.DataID) AS AllDataIDs,    ( SELECT COALESCE(DataID ,'')
//         FROM   PpcHLGradingBreakup   WHERE    IsBlock = 1   AND Treatment != 'UNT'  AND FIND_IN_SET(DataID, PHGB.DataID)
//     ) AS SoakingBreakUpID,    (   SELECT  COALESCE(sum(HLWeight) ,0)  FROM   PpcHLGradingBreakup   WHERE  IsBlock = 1    AND Treatment != 'UNT' 
//             AND  FIND_IN_SET(DataID, PHGB.DataID)    ) AS SoakingStageQuantity,    (  SELECT  COALESCE(DataID ,'') FROM  PpcHLGradingBreakup 
//         WHERE   IsBlock = 1  AND Treatment = 'UNT'  AND FIND_IN_SET(DataID, PHGB.DataID)    ) AS FreezingBreakUpID    , (   SELECT 
//            COALESCE(sum(HLWeight) ,0)   FROM      PpcHLGradingBreakup   WHERE   IsBlock = 1  AND Treatment = 'UNT' AND    FIND_IN_SET(DataID, PHGB.DataID)
//     ) AS FreezingStageQuantity , (   SELECT  COALESCE(sum(SoakInQty) ,0)   FROM  ProdSoakingDetails  WHERE    FIND_IN_SET(VABreakupID, PHGB.DataID)
//     ) AS SoakingBlockQuantity   , ( SELECT   COALESCE(sum(ProductionWeight) ,0) FROM  ProdFreezingData  WHERE    FIND_IN_SET(GradingBreakupID, PHGB.DataID) and GradingBreakupID not in (SELECT distinct VABreakupID FROM ProdSoakingDetails where    FIND_IN_SET(VABreakupID, PHGB.DataID))
//     ) AS FreezingBlockQuantity       FROM     PpcHLGradingDetails AS PHGD LEFT JOIN     PpcHLGradingBreakup AS PHGB     ON PHGB.LotID = PHGD.LotID
//   WHERE   IsBlock = 1 and PHGD.TrackID in ($TrackID )   GROUP BY     PHGD.TrackID,PHGB.DataID;";


//     // $stmtFreezingBlockWeight = $iconn->query($SqlFreezingBlockWeight);
//     $stmtFreezingBlockWeight = $this->conn->prepare($SqlFreezingBlockWeight);

//     $stmtFreezingBlockWeight->execute();
//     // $stmtFreezingBlockWeight->execute();
//     // while ($dataRowFreezingBlockWeight = $stmtFreezingBlockWeight->fetch_array()) {
//     while ($dataRowFreezingBlockWeight = $stmtFreezingBlockWeight->fetch(PDO::FETCH_ASSOC)) {
//         $GradingBreakUpID = $dataRowFreezingBlockWeight['AllDataIDs'];
//         $SoakingStageQuantity = $dataRowFreezingBlockWeight['SoakingStageQuantity']; //nothing but gradingweight
//         $SoakingBlockQuantity = $dataRowFreezingBlockWeight['SoakingBlockQuantity'];
//         $FreezingStageQuantity = $dataRowFreezingBlockWeight['FreezingStageQuantity'];  //nothing but gradingweight
//         $FreezingBlockQuantity = $dataRowFreezingBlockWeight['FreezingBlockQuantity'];

//         $TotalBalanceSoakingBlockMeatBreakUpwise = $SoakingStageQuantity - $SoakingBlockQuantity;
//         $TotalBalanceFreezingBlockMeatBreakUpwise = $FreezingStageQuantity - $FreezingBlockQuantity;

//         $SqlUpdateBlockWeights = "UPDATE `PpcHLGradingBreakup` SET `SoakingStageBlockWeight`='$TotalBalanceSoakingBlockMeatBreakUpwise',
//         `FreezingStageBlockWeight`='$TotalBalanceFreezingBlockMeatBreakUpwise' WHERE `DataID`='$GradingBreakUpID'";
//         // $stmtUpdateBlockWeights = $iconn->query($SqlUpdateBlockWeights);
//         $stmtUpdateBlockWeights = $this->conn->prepare($SqlUpdateBlockWeights);

//         $stmtUpdateBlockWeights->execute();
//         if ($stmtUpdateBlockWeights) {
//             echo "Success";
//         }
//         // $stmtUpdateBlockWeights->execute();

//     }


//     $SqlUpdateRmReceivingDetails = "UPDATE RmReceivingDetails AS RRD JOIN (  SELECT   PHGD.TrackID,  SUM(PHGB.SoakingStageBlockWeight + PHGB.FreezingStageBlockWeight) AS TotalBlockMeatQuantity
//     FROM   PpcHLGradingDetails AS PHGD    LEFT JOIN  PpcHLGradingBreakup AS PHGB  ON PHGB.LotID = PHGD.LotID    WHERE   PHGD.TrackID IN ($TrackID)
//     GROUP BY   PHGD.TrackID) AS SubQuery ON RRD.ReceivingID = SubQuery.TrackID SET     RRD.TrackWiseBlockMeatQuantity = SubQuery.TotalBlockMeatQuantity";


//     // $stmtUpdateRmReceivingDetails = $iconn->query($SqlUpdateRmReceivingDetails);
//     $stmtUpdateRmReceivingDetails = $this->conn->prepare($SqlUpdateRmReceivingDetails);

//     $stmtUpdateRmReceivingDetails->execute();
//     // $stmtUpdateRmReceivingDetails->execute();

//     return "success";

// }
/***BlockMeat** */

/***CookedMeat****/

function UpdateTrackWiseCookedMeatWeight($DateFilter, $Query, $iconn)
{
    $sqlCookedMeatQuery = "SELECT GROUP_CONCAT(ReceivingID)as TrackId FROM `RmReceivingDetails` as RmRD WHERE $DateFilter $Query 
        and IsStoreMaterial=0 and OutStage=0  and ReceivingID in (SELECT TrackID FROM `PpcValueAddition`)";

    $stmtSelectCookedMeat = $iconn->query($sqlCookedMeatQuery);
    $dataRowCookedMeat = $stmtSelectCookedMeat->fetch_array();
    $TrackID = $dataRowCookedMeat['TrackId'];

    echo $sqlUpdateCookedMeatQtyQuery = " UPDATE  RmReceivingDetails AS RRD join (SELECT  PVA.TrackID,  SUM(PVA.WtAftrVA) AS VAQuantity,  (
            SUM(PVA.WtAftrVA) - COALESCE((  SELECT   SUM(PSD.SoakInQty)   FROM    ProdSoakingDetails AS PSD    WHERE  
             FIND_IN_SET(PSD.VABreakupID, GROUP_CONCAT(PVA.GradingBreakupID)) ), 0) ) AS TrackWiseCookedMeatQuantity
    FROM   PpcValueAddition AS PVA    LEFT JOIN   PpcHLGradingBreakup AS PHGB      ON PHGB.DataID = PVA.GradingBreakupID
    LEFT JOIN     PpcHLVAVarietyList AS PHVL     ON PHVL.VarietyID = PVA.VarietyID    WHERE         PVA.TrackID IN ($TrackID) 
        AND PHGB.IsBlock = 0         AND PHGB.Treatment != 'UNT'  and TypeProduct='COOKED'    GROUP BY         PVA.TrackID
) AS CalculatedData ON     RRD.ReceivingID  = CalculatedData.TrackID SET  RRD.TrackWiseCookedMeatQuantity = CalculatedData.TrackWiseCookedMeatQuantity";

    $stmtUpdateCookedMeatQtyQuery = $iconn->query($sqlUpdateCookedMeatQtyQuery);
    return "success";

}
/***CookedMeat** */

/***RawMeat****/

function UpdateTrackWiseRawMeatWeight($DateFilter, $Query, $iconn)
{
    $sqlRawMeatQuery = "SELECT GROUP_CONCAT(ReceivingID)as TrackId FROM `RmReceivingDetails` as RmRD WHERE $DateFilter $Query 
        and IsStoreMaterial=0 and OutStage=0  and ReceivingID in (SELECT TrackID FROM `PpcValueAddition`)";

    $stmtRawMeatSelect = $iconn->query($sqlRawMeatQuery);
    $dataRowRawMeat = $stmtRawMeatSelect->fetch_array();
    $TrackID = $dataRowRawMeat['TrackId'];

    echo $sqlUpdateRawMeatQtyQuery = " UPDATE  RmReceivingDetails AS RRD join (SELECT  PVA.TrackID,  SUM(PVA.WtAftrVA) AS VAQuantity,  (
            SUM(PVA.WtAftrVA) - COALESCE((  SELECT   SUM(PSD.SoakInQty)   FROM    ProdSoakingDetails AS PSD    WHERE  
             FIND_IN_SET(PSD.VABreakupID, GROUP_CONCAT(PVA.GradingBreakupID)) ), 0) ) AS TrackWiseRawMeatQuantity
    FROM   PpcValueAddition AS PVA    LEFT JOIN   PpcHLGradingBreakup AS PHGB      ON PHGB.DataID = PVA.GradingBreakupID
    LEFT JOIN     PpcHLVAVarietyList AS PHVL     ON PHVL.VarietyID = PVA.VarietyID    WHERE         PVA.TrackID IN ($TrackID) 
        AND PHGB.IsBlock = 0         AND PHGB.Treatment != 'UNT'  and TypeProduct='RAW'    GROUP BY         PVA.TrackID
) AS CalculatedData ON     RRD.ReceivingID  = CalculatedData.TrackID SET  RRD.TrackWiseRawMeatQuantity = CalculatedData.TrackWiseRawMeatQuantity";

    $stmtUpdateRawMeatQtyQuery = $iconn->query($sqlUpdateRawMeatQtyQuery);
    return "success";

}
/***RawMeat****/

/***RawHL****/
function UpdateTrackWiseRawHLWeight($DateFilter, $Query, $iconn)
{
    $sqlThawingQuery = "SELECT GROUP_CONCAT(ReceivingID)as TrackId FROM `RmReceivingDetails` as RmRD WHERE $DateFilter $Query and IsStoreMaterial=0 
    and OutStage=0  and ReceivingID in (SELECT TrackID FROM `PpcHLGradingDetails`) ";

    $stmtSelectThawing = $iconn->query($sqlThawingQuery);
    $dataRowThawing = $stmtSelectThawing->fetch_array();
    $TrackID = $dataRowThawing['TrackId'];

    echo $sqlThawingUpdateQuery = " UPDATE RmReceivingDetails AS RRD JOIN ( SELECT PHGD.TrackID, SUM(PHGB.HLWeight) AS TotalHLWeight, 
   GROUP_CONCAT(PHGB.DataID) AS ID FROM PpcHLGradingDetails AS PHGD LEFT JOIN PpcHLGradingBreakup AS PHGB ON PHGB.LotId = PHGD.LotID 
   WHERE PHGD.TrackID IN ($TrackID) AND PHGB.Treatment != 'UNT' AND PHGB.IsBlock = 0 GROUP BY PHGD.TrackID ) AS GD 
   ON RRD.ReceivingID = GD.TrackID SET RRD.TrackWiseRawHLQuantity = COALESCE(GD.TotalHLWeight - ( SELECT COALESCE(SUM(SentWeight), 0) 
   FROM PpcValueAddition WHERE FIND_IN_SET(GradingBreakupID, GD.ID) ), GD.TotalHLWeight)";

    $stmtThawingUpdateQuery = $iconn->query($sqlThawingUpdateQuery);
    return "success";
}

/***RawHL****/
/***ThawingMeat****/
function UpdateTrackWiseThawingMeatWeight($DateFilter, $Query, $iconn)
{
    $sqlThawingMeatQuery = "SELECT GROUP_CONCAT(ReceivingID)as TrackId FROM `RmReceivingDetails` as RmRD WHERE $DateFilter $Query and IsStoreMaterial=1
    and OutStage=1  ";

    $stmtSelectThawingMeat = $iconn->query($sqlThawingMeatQuery);
    $dataRowThawingMeat = $stmtSelectThawingMeat->fetch_array();
    $TrackID = $dataRowThawingMeat['TrackId'];

    echo $sqlUpdateThawingMeatQuery = " UPDATE RmReceivingDetails AS RRD JOIN ( SELECT RRD.ReceivingID as TrackID, SUM(RRB.ReceivingQty) AS TotalThawingWeight 
    FROM RmReceivingDetails as RRD  LEFT JOIN RmReceivingBreakup AS RRB ON RRB.ReceivingID = RRD.ReceivingID   
     WHERE RRD.ReceivingID IN ($TrackID)   GROUP BY RRD.ReceivingID ) AS GD    ON RRD.ReceivingID = GD.TrackID 
     SET RRD.TrackWiseThawingMeatQuantity = COALESCE(GD.TotalThawingWeight - ( SELECT COALESCE(SUM(SentWeight),0) 
   FROM PpcValueAddition WHERE FIND_IN_SET(TrackID, GD.TrackID) ), GD.TotalThawingWeight)";

    $stmtUpdateThawingMeatQuery = $iconn->query($sqlUpdateThawingMeatQuery);
    return "success";
}
/***ThawingMeat****/

?>