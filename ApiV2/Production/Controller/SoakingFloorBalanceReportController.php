<?php
include_once '../Global/Util.php';
class SoakingFloorBalanceReportController
{
    private $conn;
    private $Error = null;
    private $Msg = '';

    public function __construct($db)
    {
        $this->conn = $db;
    }
    public function getGradingTrackNumbers($RequestBody)
    {
        // $StartDate = $RequestBody['StartDate'];
        // $StartDate = "2025-06-01";  client requirement report start date should be June 1st
        $StartDate = "2025-10-25"; 
        $EndDate = $RequestBody['EndDate'];
        $ReceivingPlantID = $RequestBody['ReceivingPlantID'];
        $ChillRoomPreparationType = $RequestBody['ChillRoomPreparationType'];
        $DateFilter = "RmRD.ArrivalDate BETWEEN '$StartDate' AND '$EndDate'";
        // $DateFilter = "RmRD.ArrivalDate <= '$EndDate'";
        $Query = '';

        if ($ReceivingPlantID != '') {
            $Query .= " AND RmRD.ReceivingPlantID='$ReceivingPlantID'";
        }

        $Data['Error'] = null;
        $Data['Msg'] = 'Success';

        if ($ChillRoomPreparationType == '' || $ChillRoomPreparationType == 'RAW HL QTY') {

            // $this->UpdateTrackWiseRawHLWeight($DateFilter, $Query);

            $ArrayRawHLTrackData = $this->getRawHLTrack($DateFilter, $Query);

            if (!empty($ArrayRawHLTrackData)) {
                // Initialize an array to store TrackIDs
                $RawHLTrackIDs = [];
                // Loop through the data to extract TrackIDs
                foreach ($ArrayRawHLTrackData as $data) {
                    if (isset($data['TrackID'])) {
                        $RawHLTrackIDs[] = $data['TrackID'];
                    }
                }
                $Data['RawHLTrackData'] = $ArrayRawHLTrackData;
                $RawHLBalanceData = $this->getRawHLBalanceData($RawHLTrackIDs);
                $Data['RawHLBalanceData'] = $RawHLBalanceData;
            }
        }

        if ($ChillRoomPreparationType == '' || $ChillRoomPreparationType == 'COOKED HL QTY') {

            // $this->UpdateTrackWiseRawHLWeight($DateFilter, $Query);

            $ArrayCookedHLTrackData = $this->getCookedHLTrack($DateFilter, $Query);

            if (!empty($ArrayCookedHLTrackData)) {
                // Initialize an array to store TrackIDs
                $CookedHLTrackIDs = [];
                // Loop through the data to extract TrackIDs
                foreach ($ArrayCookedHLTrackData as $data) {
                    if (isset($data['TrackID'])) {
                        $CookedHLTrackIDs[] = $data['TrackID'];
                    }
                }
                $Data['CookedHLTrackData'] = $ArrayCookedHLTrackData;
                $CookedHLBalanceData = $this->getCookedHLBalanceData($CookedHLTrackIDs);
                $Data['CookedHLBalanceData'] = $CookedHLBalanceData;
            }
        }


        if ($ChillRoomPreparationType == '' || $ChillRoomPreparationType == 'RAW MEAT QTY') {

            // $this->UpdateTrackWiseRawMeatWeight($DateFilter, $Query);

            $ArrayRawMeatTrackData = $this->getRawMeatTrack($DateFilter, $Query);

            if (!empty($ArrayRawMeatTrackData)) {
                // Initialize an array to store TrackIDs
                $RawMeatTrackIDs = [];
                // Loop through the data to extract TrackIDs
                foreach ($ArrayRawMeatTrackData as $data) {
                    if (isset($data['TrackID'])) {
                        $RawMeatTrackIDs[] = $data['TrackID'];
                    }
                }
                $Data['RawMeatTrackData'] = $ArrayRawMeatTrackData;
                $RawMeatBalanceData = $this->getRawMeatBalanceData($RawMeatTrackIDs);
                $Data['RawMeatBalanceData'] = $RawMeatBalanceData;
            }
        }


        if ($ChillRoomPreparationType == '' || $ChillRoomPreparationType == 'COOKING MEAT QTY') {

            // $this->UpdateTrackWiseCookedMeatWeight($DateFilter, $Query);

            $ArrayCookedMeatTrackData = $this->getCookedMeatTrack($DateFilter, $Query);

            if (!empty($ArrayCookedMeatTrackData)) {
                // Initialize an array to store TrackIDs
                $CookedMeatTrackIDs = [];
                // Loop through the data to extract TrackIDs
                foreach ($ArrayCookedMeatTrackData as $data) {
                    if (isset($data['TrackID'])) {
                        $CookedMeatTrackIDs[] = $data['TrackID'];
                    }
                }
                $Data['CookedMeatTrackData'] = $ArrayCookedMeatTrackData;
                $CookedMeatBalanceData = $this->getCookedMeatBalanceData($CookedMeatTrackIDs);
                $Data['CookedMeatBalanceData'] = $CookedMeatBalanceData;
            }
        }

        if ($ChillRoomPreparationType == '' || $ChillRoomPreparationType == 'BLOCK MEAT QTY') {

            // $this->UpdateTrackWiseBlockMeatWeight($DateFilter, $Query);
            $ArrayBlockMeatTrackData = $this->getBlockMeatTrack($DateFilter, $Query);

            if (!empty($ArrayBlockMeatTrackData)) {
                // Initialize an array to store TrackIDs
                $BlockMeatTrackIDs = [];
                // Loop through the data to extract TrackIDs
                foreach ($ArrayBlockMeatTrackData as $data) {
                    if (isset($data['TrackID'])) {
                        $BlockMeatTrackIDs[] = $data['TrackID'];
                    }
                }
                $Data['BlockMeatTrackData'] = $ArrayBlockMeatTrackData;
                $BlockMeatBalanceData = $this->getBlockMeatBalanceData($BlockMeatTrackIDs);
                $Data['BlockMeatBalanceData'] = $BlockMeatBalanceData;
            }
        }

        if ($ChillRoomPreparationType == '' || $ChillRoomPreparationType == 'THAWING MEAT QTY') {
            // $this->UpdateTrackWiseThawingMeatWeight($DateFilter, $Query);

            $ArrayThawingMeatTrackData = $this->getThawingMeatTrack($DateFilter, $Query);

            if (!empty($ArrayThawingMeatTrackData)) {
                // Initialize an array to store TrackIDs
                $ThawingMeatTrackIDs = [];
                // Loop through the data to extract TrackIDs
                foreach ($ArrayThawingMeatTrackData as $data) {
                    if (isset($data['TrackID'])) {
                        $ThawingMeatTrackIDs[] = $data['TrackID'];
                    }
                }
                $Data['ThawingMeatTrackData'] = $ArrayThawingMeatTrackData;
                $ThawingMeatBalanceData = $this->getThawingMeatBalanceData($ThawingMeatTrackIDs);
                $Data['ThawingMeatBalanceData'] = $ThawingMeatBalanceData;
            }

        }

        //    if ($ChillRoomPreparationType == '' || $ChillRoomPreparationType == 'COOKED HL QTY') {

        //         // $this->UpdateTrackWiseRawHLWeight($DateFilter, $Query);

        //         $ArrayCookedHLTrackData = $this->getCookedHLTrack($DateFilter, $Query);

        //         if (!empty($ArrayCookedHLTrackData)) {
        //             // Initialize an array to store TrackIDs
        //             $CookedHLTrackIDs = [];
        //             // Loop through the data to extract TrackIDs
        //             foreach ($ArrayCookedHLTrackData as $data) {
        //                 if (isset($data['TrackID'])) {
        //                     $CookedHLTrackIDs[] = $data['TrackID'];
        //                 }
        //             }
        //             $Data['CookedHLTrackData'] = $ArrayCookedHLTrackData;
        //             $CookedHLBalanceData = $this->getCookedHLBalanceData($CookedHLTrackIDs);
        //             $Data['CookedHLBalanceData'] = $CookedHLBalanceData;
        //         }
        //     }


        return $Data;
    }


    /***RawHL****/

    function getRawHLTrack($DateFilter, $Query)
    {
        $sqlFields = "  ReceivingID as TrackID,TrackCode,ReceivingPlantID,ArrivalDate,TrackWiseRawHLQuantity";
        $sqlQuery = "SELECT  $sqlFields FROM `RmReceivingDetails` as RmRD WHERE $DateFilter 
          $Query and TrackWiseRawHLQuantity>100 and IsStoreMaterial=0 and OutStage=0";

        $stmtSelect = $this->conn->prepare($sqlQuery);

        // echo print_r($sqlQuery);
        // exit;
        $stmtSelect->execute();

        $i = 0;
        $ArrayReceivedData = [];
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
            $ArrivalDate = !empty($dataRow['ArrivalDate']) ? date('d-m-Y', strtotime($dataRow['ArrivalDate'])) : '';
            $ArrayReceivedDetails['SNo'] = ++$i;
            $ArrayReceivedDetails['ArrivalDate'] = $ArrivalDate;
            $ArrayReceivedDetails['TrackID'] = $dataRow['TrackID'];
            $ArrayReceivedDetails['TrackCode'] = $dataRow['TrackCode'];
            $ArrayReceivedDetails['ReceivingPlantID'] = $dataRow['ReceivingPlantID'];
            $ArrayReceivedDetails['TrackWiseRawHLBalanceQty'] = $dataRow['TrackWiseRawHLQuantity'];
            $ArrayReceivedData[] = $ArrayReceivedDetails;

        }
        return $ArrayReceivedData;
    }
    function getRawHLBalanceData($RawHLTrackIDs)
    {
        $sqlSelect = "DROP TABLE IF EXISTS `RAWHLTemp`";
        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();

        $ArrayRawHLTrackIds = implode(',', $RawHLTrackIDs);

        $sqlCreateTempTable = "CREATE  TABLE RAWHLTemp AS SELECT  PHGD.TrackID AS RawHLTrackID, PHVL.ShortName AS RawHLVariety, PHVL.Species AS RawHLSpecies,    PackingGrade AS RawHLGrade,    
    GROUP_CONCAT(PHGB.DataID) AS RawHLGradingBreakUpId,    SUM(PHGB.HLWeight) - COALESCE(( SELECT SUM(SentWeight)  FROM PpcValueAddition 
        WHERE FIND_IN_SET(GradingBreakupID, GROUP_CONCAT(PHGB.DataID))    ), 0) AS RawHLWeight FROM   PpcHLGradingBreakup AS PHGB
LEFT JOIN     PpcHLVAVarietyList AS PHVL ON PHGB.VarietyID = PHVL.VarietyID 
LEFT JOIN     PpcHLGradingDetails AS PHGD ON PHGD.LotID = PHGB.LotID
WHERE     PHGD.TrackID IN ($ArrayRawHLTrackIds)    AND PHGB.VarietyID != 0    AND PHGB.Treatment != 'UNT'    AND PHGB.IsBlock = 0 AND PHVL.TypeProduct = 'RAW'
GROUP BY     ShortName, PackingGrade, PHGD.TrackID,PHVL.Species ORDER BY     ABS(PackingGrade) ";

        $stmtCreateTempTable = $this->conn->prepare($sqlCreateTempTable);
        $stmtCreateTempTable->execute();

        // echo print_r($sqlCreateTempTable);
        // exit;

        $sqlRawHLSelect = "SELECT SUM(RawHLWeight) AS TotalRawHLWeight, RawHLVariety, RawHLSpecies, RawHLGrade FROM RAWHLTemp 
        GROUP BY RawHLVariety, RawHLGrade,RawHLSpecies HAVING SUM(RawHLWeight) > 100 ORDER BY ABS(RawHLGrade); ";
        $stmtRawHL = $this->conn->prepare($sqlRawHLSelect);
        $stmtRawHL->execute();

        $SNO = 0;
        while ($rowstmtRawHL = $stmtRawHL->fetch(PDO::FETCH_ASSOC)) {
            $RawHLBalanceQty = $rowstmtRawHL['TotalRawHLWeight'];
            $PackingGrade = $rowstmtRawHL['RawHLGrade'];
            $ShortName = $rowstmtRawHL['RawHLVariety'];
            $Species = $rowstmtRawHL['RawHLSpecies'];

            $ArrayVarietyList['SNO'] = ++$SNO;
            $ArrayVarietyList['PackingGrade'] = $rowstmtRawHL['RawHLGrade'];
            $ArrayVarietyList['ShortName'] = $rowstmtRawHL['RawHLVariety'] . "-" . $rowstmtRawHL['RawHLSpecies'];
            $ArrayVarietyList['BalanceQty'] = $RawHLBalanceQty;
            $ArrayBalanceData = [];
            $TotalRawHLBalanceQty = 0;

            foreach ($RawHLTrackIDs as $TrackIds) {
                $TrackID = $TrackIds;
                $sqlBalanceSelect = "  SELECT sum(RawHLWeight) as BalanceRawHLWeight FROM `RAWHLTemp` where RawHLTrackID='$TrackID' and RawHLVariety='$ShortName' and RawHLSpecies='$Species' and RawHLGrade='$PackingGrade'";
                $stmtBalanceSelect = $this->conn->prepare($sqlBalanceSelect);
                $stmtBalanceSelect->execute();
                $rowBalance = $stmtBalanceSelect->fetch(PDO::FETCH_ASSOC);
                $RawHLBalanceQty = $rowBalance['BalanceRawHLWeight'];
                $ArrayBalanceList['Balance'] = ($RawHLBalanceQty < 100) ? '-' : number_format($RawHLBalanceQty, 3);
                if ($RawHLBalanceQty >= 100) {
                    $TotalRawHLBalanceQty += $RawHLBalanceQty;
                }
                $ArrayBalanceData[] = $ArrayBalanceList;
            }

            $ArrayVarietyList['TotalBalanceQty'] = number_format($TotalRawHLBalanceQty, 3);
            $ArrayVarietyList['BalanceData'] = $ArrayBalanceData;

            $ArrayVarietyData[] = $ArrayVarietyList;
            $GrandBalanceQty += $TotalRawHLBalanceQty;
        }

        if ($GrandBalanceQty != 0) {
            $Data['GrandBalanceQty'] = number_format($GrandBalanceQty, 3);
            $Data['ArrayVarietyData'] = $ArrayVarietyData;
            $Data['qry'] = $sqlCreateTempTable;
            $Data['qry1'] = $sqlRawHLSelect;
        }
        return $Data;
    }
    /***RawHL****/




    /***CookedHL****/
    function getCookedHLTrack($DateFilter, $Query)
    {
        $sqlFields = "  ReceivingID as TrackID,TrackCode,ReceivingPlantID,ArrivalDate,TrackWiseRawHLQuantity";
        $sqlQuery = "SELECT  $sqlFields FROM `RmReceivingDetails` as RmRD WHERE $DateFilter 
          $Query and TrackWiseRawHLQuantity>100 and IsStoreMaterial=0 and OutStage=0";

        $stmtSelect = $this->conn->prepare($sqlQuery);

        // echo print_r($sqlQuery);
        // exit;
        $stmtSelect->execute();

        $i = 0;
        $ArrayReceivedData = [];
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
            $ArrivalDate = !empty($dataRow['ArrivalDate']) ? date('d-m-Y', strtotime($dataRow['ArrivalDate'])) : '';
            $ArrayReceivedDetails['SNo'] = ++$i;
            $ArrayReceivedDetails['ArrivalDate'] = $ArrivalDate;
            $ArrayReceivedDetails['TrackID'] = $dataRow['TrackID'];
            $ArrayReceivedDetails['TrackCode'] = $dataRow['TrackCode'];
            $ArrayReceivedDetails['ReceivingPlantID'] = $dataRow['ReceivingPlantID'];
            $ArrayReceivedDetails['TrackWiseRawHLBalanceQty'] = $dataRow['TrackWiseRawHLQuantity'];
            $ArrayReceivedDetails['rowCount'] = 1;
            $ArrayReceivedData[] = $ArrayReceivedDetails;

        }
        return $ArrayReceivedData;
    }
    function getCookedHLBalanceData($CookedHLTrackIDs)
    {
        $sqlSelect = "DROP TABLE IF EXISTS `RAWHLTemp`";
        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();

        $ArrayCookedHLTrackIds = implode(',', $CookedHLTrackIDs);

        $sqlCookedCreateTempTable = "CREATE  TABLE RAWHLTemp AS SELECT  PHGD.TrackID AS RawHLTrackID, PHVL.ShortName AS RawHLVariety, PHVL.Species AS RawHLSpecies,    PackingGrade AS RawHLGrade,    
    GROUP_CONCAT(PHGB.DataID) AS RawHLGradingBreakUpId,    SUM(PHGB.HLWeight) - COALESCE(( SELECT SUM(SentWeight)  FROM PpcValueAddition 
        WHERE FIND_IN_SET(GradingBreakupID, GROUP_CONCAT(PHGB.DataID))    ), 0) AS RawHLWeight FROM   PpcHLGradingBreakup AS PHGB
LEFT JOIN     PpcHLVAVarietyList AS PHVL ON PHGB.VarietyID = PHVL.VarietyID 
LEFT JOIN     PpcHLGradingDetails AS PHGD ON PHGD.LotID = PHGB.LotID
WHERE     PHGD.TrackID IN ($ArrayCookedHLTrackIds)    AND PHGB.VarietyID != 0    AND PHGB.Treatment != 'UNT'    AND PHGB.IsBlock = 0 AND PHVL.TypeProduct = 'COOKED' 
GROUP BY     ShortName, PackingGrade, PHGD.TrackID,PHVL.Species ORDER BY     ABS(PackingGrade) ";

        $stmtCreateTempTable = $this->conn->prepare($sqlCookedCreateTempTable);
        $stmtCreateTempTable->execute();

        // echo print_r($sqlCreateTempTable);
        // exit;

        $sqlRawHLSelect = "SELECT SUM(RawHLWeight) AS TotalRawHLWeight, RawHLVariety, RawHLSpecies, RawHLGrade FROM RAWHLTemp 
        GROUP BY RawHLVariety, RawHLGrade,RawHLSpecies HAVING SUM(RawHLWeight) > 100 ORDER BY ABS(RawHLGrade); ";
        $stmtRawHL = $this->conn->prepare($sqlRawHLSelect);
        $stmtRawHL->execute();

        $SNO = 0;
        while ($rowstmtRawHL = $stmtRawHL->fetch(PDO::FETCH_ASSOC)) {
            $CookedHLBalanceQty = $rowstmtRawHL['TotalRawHLWeight'];
            $PackingGrade = $rowstmtRawHL['RawHLGrade'];
            $ShortName = $rowstmtRawHL['RawHLVariety'];
            $Species = $rowstmtRawHL['RawHLSpecies'];

            $ArrayVarietyList['SNO'] = ++$SNO;
            $ArrayVarietyList['PackingGrade'] = $rowstmtRawHL['RawHLGrade'];
            $ArrayVarietyList['ShortName'] = $rowstmtRawHL['RawHLVariety'] . "-" . $rowstmtRawHL['RawHLSpecies'];
            $ArrayVarietyList['BalanceQty'] = $CookedHLBalanceQty;
            $ArrayBalanceData = [];
            $TotalCookedHLBalanceQty = 0;

            foreach ($CookedHLTrackIDs as $TrackIds) {
                $TrackID = $TrackIds;
                $sqlCookedBalanceSelect = "  SELECT sum(RawHLWeight) as BalanceRawHLWeight FROM `RAWHLTemp` where RawHLTrackID='$TrackID' and RawHLVariety='$ShortName' and RawHLSpecies='$Species' and RawHLGrade='$PackingGrade'";
                $stmtBalanceSelect = $this->conn->prepare($sqlCookedBalanceSelect);
                $stmtBalanceSelect->execute();
                $rowBalance = $stmtBalanceSelect->fetch(PDO::FETCH_ASSOC);
                $CookedHLBalanceQty = $rowBalance['BalanceRawHLWeight'];
                $ArrayBalanceList['Balance'] = ($CookedHLBalanceQty < 100) ? '-' : number_format($CookedHLBalanceQty, 3);

                if ($CookedHLBalanceQty >= 100) {
                    $TotalCookedHLBalanceQty += $CookedHLBalanceQty;
                }
                $ArrayBalanceData[] = $ArrayBalanceList;
            }

            $ArrayVarietyList['TotalBalanceQty'] = number_format($TotalCookedHLBalanceQty, 3);
            $ArrayVarietyList['BalanceData'] = $ArrayBalanceData;

            $ArrayVarietyData[] = $ArrayVarietyList;
            $GrandBalanceQty += $TotalCookedHLBalanceQty;
        }

        if ($GrandBalanceQty != 0) {
            $Data['GrandBalanceQty'] = number_format($GrandBalanceQty, 3);
            $Data['ArrayVarietyData'] = $ArrayVarietyData;
        }
        return $Data;
    }



    /***RawMeat****/

    function getRawMeatTrack($DateFilter, $Query)
    {
        $sqlRawMeatFields = "  ReceivingID as TrackID,TrackCode,ReceivingPlantID,ArrivalDate,TrackWiseRawMeatQuantity";
        $sqlRawMeatQuery = "SELECT  $sqlRawMeatFields FROM `RmReceivingDetails` as RmRD WHERE $DateFilter 
          $Query and TrackWiseRawMeatQuantity>=100 and IsStoreMaterial=0 and OutStage=0";

        $stmtSelectRawMeat = $this->conn->prepare($sqlRawMeatQuery);
        $stmtSelectRawMeat->execute();

        $i = 0;
        $ArrayReceivedData = [];
        while ($dataRowSelectRawMeat = $stmtSelectRawMeat->fetch(PDO::FETCH_ASSOC)) {
            $ArrivalDate = !empty($dataRowSelectRawMeat['ArrivalDate']) ? date('d-m-Y', strtotime($dataRowSelectRawMeat['ArrivalDate'])) : '';
            $ArrayReceivedDetails['SNo'] = ++$i;
            $ArrayReceivedDetails['ArrivalDate'] = $ArrivalDate;
            $ArrayReceivedDetails['TrackID'] = $dataRowSelectRawMeat['TrackID'];
            $ArrayReceivedDetails['TrackCode'] = $dataRowSelectRawMeat['TrackCode'];
            $ArrayReceivedDetails['ReceivingPlantID'] = $dataRowSelectRawMeat['ReceivingPlantID'];
            $ArrayReceivedDetails['TrackWiseRawMeatQuantity'] = $dataRowSelectRawMeat['TrackWiseRawMeatQuantity'];
            $ArrayReceivedData[] = $ArrayReceivedDetails;

        }
        return $ArrayReceivedData;

    }

    function getRawMeatBalanceData($RawMeatTrackIDs)
    {
        $sqlSelect = "DROP TABLE IF EXISTS `RawMeatTemp`";
        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();

        $ArrayRawMeatTrackIds = implode(',', $RawMeatTrackIDs);

        $sqlCreateTempRawMeatTable = "CREATE  TABLE RawMeatTemp AS WITH PreAggregatedData AS (    SELECT   PVA.TrackID, PVA.GradingBreakupID, 
        PVA.VarietyID,  PHVL.Species,   PHGB.PackingGrade, PHVL.ShortName,  SUM(PVA.WtAftrVA) AS VAQuantity   FROM  PpcValueAddition AS PVA 
    LEFT JOIN  PpcHLGradingBreakup AS PHGB ON PHGB.DataID = PVA.GradingBreakupID   LEFT JOIN    PpcHLVAVarietyList AS PHVL ON PHVL.VarietyID = PVA.VarietyID 
    WHERE   PVA.TrackID IN ($ArrayRawMeatTrackIds)   AND PHGB.IsBlock = 0   AND PHGB.Treatment != 'UNT'  AND PHVL.TypeProduct = 'RAW'    
    GROUP BY  PVA.GradingBreakupID, PVA.VarietyID, PVA.TrackID,  PHVL.Species, PHGB.PackingGrade, PHVL.ShortName),SoakingData AS 
    (    SELECT   PSD.VABreakupID, PSD.Variety,  SUM(PSD.SoakInQty) AS SoakingQuantity   FROM   ProdSoakingDetails AS PSD 
     GROUP BY  PSD.VABreakupID,   PSD.Variety)SELECT  PAD.TrackID AS RawMeatTrackID,    PAD.ShortName AS RawMeatVariety, 
      PAD.Species AS RawMeatSpecies,    PAD.PackingGrade AS RawMeatGrade, GROUP_CONCAT(DISTINCT PAD.GradingBreakupID) AS RawMeatGradingBreakUpId,
    SUM(PAD.VAQuantity) - COALESCE(SUM(SD.SoakingQuantity), 0) AS RawMeatWeight FROM   PreAggregatedData AS PAD 
LEFT JOIN   SoakingData AS SD ON PAD.GradingBreakupID = SD.VABreakupID AND PAD.VarietyID = SD.Variety GROUP BY   PAD.PackingGrade, 
PAD.ShortName, PAD.Species, PAD.TrackID HAVING    RawMeatWeight >= 100 ORDER BY   ABS(PAD.PackingGrade); ";


        $stmtCreateTempRawMeatTable = $this->conn->prepare($sqlCreateTempRawMeatTable);
        $stmtCreateTempRawMeatTable->execute();

        $sqlRawMeatSelect = "SELECT SUM(RawMeatWeight) AS TotalRawMeatBalanceQty, RawMeatVariety, RawMeatSpecies, RawMeatGrade FROM RawMeatTemp 
        GROUP BY RawMeatVariety, RawMeatGrade,RawMeatSpecies HAVING SUM(RawMeatWeight) > 100 ORDER BY ABS(RawMeatGrade); ";


        $stmtRawMeat = $this->conn->prepare($sqlRawMeatSelect);
        $stmtRawMeat->execute();

        $SNO = 0;
        while ($rowstmtRawMeat = $stmtRawMeat->fetch(PDO::FETCH_ASSOC)) {
            $RawMeatBalanceQty = $rowstmtRawMeat['TotalRawMeatBalanceQty'];
            $PackingGrade = $rowstmtRawMeat['RawMeatGrade'];
            $ShortName = $rowstmtRawMeat['RawMeatVariety'];
            $Species = $rowstmtRawMeat['RawMeatSpecies'];

            $ArrayVarietyList['SNO'] = ++$SNO;
            $ArrayVarietyList['PackingGrade'] = $rowstmtRawMeat['RawMeatGrade'];
            $ArrayVarietyList['ShortName'] = $rowstmtRawMeat['RawMeatVariety'] . "-" . $rowstmtRawMeat['RawMeatSpecies'];
            $ArrayVarietyList['BalanceQty'] = $RawMeatBalanceQty;
            $ArrayBalanceData = [];
            $TotalRawMeatBalanceQty = 0;

            foreach ($RawMeatTrackIDs as $TrackIds) {
                $TrackID = $TrackIds;
                $sqlRawMeatBalanceSelect = "  SELECT sum(RawMeatWeight) as BalanceRawMeatWeight FROM `RawMeatTemp` where RawMeatTrackID='$TrackID' and RawMeatVariety='$ShortName'
                 and RawMeatSpecies='$Species' and RawMeatGrade='$PackingGrade'";
                $stmtRawMeatBalanceSelect = $this->conn->prepare($sqlRawMeatBalanceSelect);
                $stmtRawMeatBalanceSelect->execute();
                $rowRawMeatBalance = $stmtRawMeatBalanceSelect->fetch(PDO::FETCH_ASSOC);
                $RawMeatBalanceQty = $rowRawMeatBalance['BalanceRawMeatWeight'];
                $ArrayBalanceList['Balance'] = ($RawMeatBalanceQty < 100) ? '-' : number_format($RawMeatBalanceQty, 3);

                if ($RawMeatBalanceQty >= 100) {
                    $TotalRawMeatBalanceQty += $RawMeatBalanceQty;
                }
                $ArrayBalanceData[] = $ArrayBalanceList;
            }

            $ArrayVarietyList['TotalBalanceQty'] = number_format($TotalRawMeatBalanceQty, 3);
            $ArrayVarietyList['BalanceData'] = $ArrayBalanceData;

            $ArrayVarietyData[] = $ArrayVarietyList;
            $GrandBalanceQty += $TotalRawMeatBalanceQty;
        }

        if ($GrandBalanceQty != 0) {
            $Data['GrandBalanceQty'] = number_format($GrandBalanceQty, 3);
            $Data['ArrayVarietyData'] = $ArrayVarietyData;
        }
        return $Data;


    }

    /***RawMeat****/

    /***CookedMeat****/


    function getCookedMeatTrack($DateFilter, $Query)
    {
        $sqlCookedMeatFields = "  ReceivingID as TrackID,TrackCode,ReceivingPlantID,ArrivalDate,TrackWiseCookedMeatQuantity";
        $sqlCookedMeatQuery = "SELECT  $sqlCookedMeatFields FROM `RmReceivingDetails` as RmRD WHERE $DateFilter 
          $Query and TrackWiseCookedMeatQuantity>=100 and IsStoreMaterial=0 and OutStage=0";

        $stmtSelectCookedMeat = $this->conn->prepare($sqlCookedMeatQuery);
        $stmtSelectCookedMeat->execute();

        $i = 0;
        $ArrayReceivedData = [];
        while ($dataRowCookedMeat = $stmtSelectCookedMeat->fetch(PDO::FETCH_ASSOC)) {
            $ArrivalDate = !empty($dataRowCookedMeat['ArrivalDate']) ? date('d-m-Y', strtotime($dataRowCookedMeat['ArrivalDate'])) : '';
            $ArrayReceivedDetails['SNo'] = ++$i;
            $ArrayReceivedDetails['ArrivalDate'] = $ArrivalDate;
            $ArrayReceivedDetails['TrackID'] = $dataRowCookedMeat['TrackID'];
            $ArrayReceivedDetails['TrackCode'] = $dataRowCookedMeat['TrackCode'];
            $ArrayReceivedDetails['ReceivingPlantID'] = $dataRowCookedMeat['ReceivingPlantID'];
            $ArrayReceivedDetails['TrackWiseCookedMeatQuantity'] = $dataRowCookedMeat['TrackWiseCookedMeatQuantity'];
            $ArrayReceivedData[] = $ArrayReceivedDetails;

        }
        return $ArrayReceivedData;

    }

    function getCookedMeatBalanceData($CookedMeatTrackIDs)
    {

        $sqlDropCookedMeatTemp = "DROP TABLE IF EXISTS `CookedMeatTemp`";
        $stmtDropCookedMeatTemp = $this->conn->prepare($sqlDropCookedMeatTemp);
        $stmtDropCookedMeatTemp->execute();

        $ArrayCookedMeatTrackIDs = implode(',', $CookedMeatTrackIDs);


        $sqlCreateCookedMeatTempTable = "CREATE  TABLE CookedMeatTemp AS WITH PreAggregatedData AS (    SELECT   PVA.TrackID, PVA.GradingBreakupID, 
        PVA.VarietyID,  PHVL.Species,   PHGB.PackingGrade, PHVL.ShortName,  SUM(PVA.WtAftrVA) AS VAQuantity   FROM  PpcValueAddition AS PVA 
    LEFT JOIN  PpcHLGradingBreakup AS PHGB ON PHGB.DataID = PVA.GradingBreakupID   LEFT JOIN    PpcHLVAVarietyList AS PHVL ON PHVL.VarietyID = PVA.VarietyID 
    WHERE   PVA.TrackID IN ($ArrayCookedMeatTrackIDs)   AND PHGB.IsBlock = 0   AND PHGB.Treatment != 'UNT'  AND PHVL.TypeProduct = 'COOKED'    
    GROUP BY  PVA.GradingBreakupID, PVA.VarietyID, PVA.TrackID,  PHVL.Species, PHGB.PackingGrade, PHVL.ShortName),SoakingData AS 
    (    SELECT   PSD.VABreakupID, PSD.Variety,  SUM(PSD.SoakInQty) AS SoakingQuantity   FROM   ProdSoakingDetails AS PSD 
     GROUP BY  PSD.VABreakupID,   PSD.Variety)
     
     SELECT  PAD.TrackID AS CookedMeatTrackID,    PAD.ShortName AS CookedMeatVariety, 
      PAD.Species AS CookedMeatSpecies,    PAD.PackingGrade AS CookedMeatGrade, GROUP_CONCAT(DISTINCT PAD.GradingBreakupID) AS CookedMeatGradingBreakUpId,
    SUM(PAD.VAQuantity) - COALESCE(SUM(SD.SoakingQuantity), 0) AS CookedMeatWeight FROM   PreAggregatedData AS PAD 
LEFT JOIN   SoakingData AS SD ON PAD.GradingBreakupID = SD.VABreakupID AND PAD.VarietyID = SD.Variety GROUP BY   PAD.PackingGrade, 
PAD.ShortName, PAD.Species, PAD.TrackID HAVING    CookedMeatWeight >= 100 ORDER BY   ABS(PAD.PackingGrade); ";


        $stmtCreateCookedMeatTempTable = $this->conn->prepare($sqlCreateCookedMeatTempTable);
        $stmtCreateCookedMeatTempTable->execute();

        $sqlCookedMeatSelect = "SELECT SUM(CookedMeatWeight) AS TotalCookedMeatBalanceQty, CookedMeatVariety, CookedMeatSpecies, CookedMeatGrade FROM CookedMeatTemp 
        GROUP BY CookedMeatVariety, CookedMeatGrade,CookedMeatSpecies HAVING SUM(CookedMeatWeight) > 100 ORDER BY ABS(CookedMeatGrade); ";
        $stmtCookedMeat = $this->conn->prepare($sqlCookedMeatSelect);
        $stmtCookedMeat->execute();

        $SNO = 0;
        while ($rowstmtCookedMeat = $stmtCookedMeat->fetch(PDO::FETCH_ASSOC)) {
            $CookedMeatBalanceQty = $rowstmtCookedMeat['TotalCookedMeatBalanceQty'];
            $PackingGrade = $rowstmtCookedMeat['CookedMeatGrade'];
            $ShortName = $rowstmtCookedMeat['CookedMeatVariety'];
            $Species = $rowstmtCookedMeat['CookedMeatSpecies'];

            $ArrayVarietyList['SNO'] = ++$SNO;
            $ArrayVarietyList['PackingGrade'] = $rowstmtCookedMeat['CookedMeatGrade'];
            $ArrayVarietyList['ShortName'] = $rowstmtCookedMeat['CookedMeatVariety'] . "-" . $rowstmtCookedMeat['CookedMeatSpecies'];
            $ArrayVarietyList['BalanceQty'] = $CookedMeatBalanceQty;
            $ArrayBalanceData = [];
            $TotalCookedMeatBalanceQty = 0;

            foreach ($CookedMeatTrackIDs as $TrackIds) {
                $TrackID = $TrackIds;
                $sqlBalanceSelectCookedMeat = "  SELECT sum(CookedMeatWeight) as BalanceCookedMeatWeight FROM `CookedMeatTemp` where CookedMeatTrackID='$TrackID' and CookedMeatVariety='$ShortName'
                 and CookedMeatSpecies='$Species' and CookedMeatGrade='$PackingGrade'";
                $stmtBalanceSelectCookedMeat = $this->conn->prepare($sqlBalanceSelectCookedMeat);
                $stmtBalanceSelectCookedMeat->execute();
                $rowBalanceCookedMeat = $stmtBalanceSelectCookedMeat->fetch(PDO::FETCH_ASSOC);
                $CookedMeatBalanceQty = $rowBalanceCookedMeat['BalanceCookedMeatWeight'];
                $ArrayBalanceList['Balance'] = ($CookedMeatBalanceQty < 100) ? '-' : number_format($CookedMeatBalanceQty, 3);
                if ($CookedMeatBalanceQty >= 100) {
                    $TotalCookedMeatBalanceQty += $CookedMeatBalanceQty;
                }
                $ArrayBalanceData[] = $ArrayBalanceList;
            }

            $ArrayVarietyList['TotalBalanceQty'] = number_format($TotalCookedMeatBalanceQty, 3);
            $ArrayVarietyList['BalanceData'] = $ArrayBalanceData;

            $ArrayVarietyData[] = $ArrayVarietyList;
            $GrandBalanceQty += $TotalCookedMeatBalanceQty;
        }

        if ($GrandBalanceQty != 0) {
            $Data['GrandBalanceQty'] = number_format($GrandBalanceQty, 3);
            $Data['ArrayVarietyData'] = $ArrayVarietyData;
        }
        return $Data;
    }

    /***CookedMeat****/

    //     /***BlockMeat****/


    //     function UpdateTrackWiseBlockMeatWeight($DateFilter, $Query)
//     {
//         $sqlGetBlockMeatQuery = "SELECT group_concat(ReceivingID) as TrackId FROM `RmReceivingDetails` as RmRD WHERE $DateFilter $Query 
//         and IsStoreMaterial=0 and OutStage=0  and ReceivingID in (SELECT distinct TrackID FROM `PpcHLGradingDetails` as PHGD
//          left join PpcHLGradingBreakup as PHGB on PHGB.LotID=PHGD.LotID WHERE IsBlock=1)";

    //         $stmtGetBlockMeatQuery = $this->conn->prepare($sqlGetBlockMeatQuery);
//         $stmtGetBlockMeatQuery->execute();
//         $dataRowGetBlockMeatQuery = $stmtGetBlockMeatQuery->fetch(PDO::FETCH_ASSOC);
//         $TrackID = $dataRowGetBlockMeatQuery['TrackId'];

    //         $SqlFreezingBlockWeight = "SELECT     PHGD.TrackID,    GROUP_CONCAT(PHGB.DataID) AS AllDataIDs,    ( SELECT COALESCE(DataID ,'')
//         FROM   PpcHLGradingBreakup   WHERE    IsBlock = 1   AND Treatment != 'UNT'  AND FIND_IN_SET(DataID, PHGB.DataID)
//     ) AS SoakingBreakUpID,    (   SELECT  COALESCE(sum(HLWeight) ,0)  FROM   PpcHLGradingBreakup   WHERE  IsBlock = 1    AND Treatment != 'UNT' 
//             AND  FIND_IN_SET(DataID, PHGB.DataID)    ) AS SoakingStageQuantity,    (  SELECT  COALESCE(DataID ,'') FROM  PpcHLGradingBreakup 
//         WHERE   IsBlock = 1  AND Treatment = 'UNT'  AND FIND_IN_SET(DataID, PHGB.DataID)    ) AS FreezingBreakUpID    , (   SELECT 
//            COALESCE(sum(HLWeight) ,0)   FROM      PpcHLGradingBreakup   WHERE   IsBlock = 1  AND Treatment = 'UNT' AND    FIND_IN_SET(DataID, PHGB.DataID)
//     ) AS FreezingStageQuantity , (   SELECT  COALESCE(sum(SoakInQty) ,0)   FROM  ProdSoakingDetails  WHERE    FIND_IN_SET(VABreakupID, PHGB.DataID)
//     ) AS SoakingBlockQuantity   , ( SELECT   COALESCE(sum(ProductionWeight) ,0) FROM  ProdFreezingData  WHERE    FIND_IN_SET(GradingBreakupID, PHGB.DataID) and GradingBreakupID not in (SELECT distinct VABreakupID FROM ProdSoakingDetails where    FIND_IN_SET(VABreakupID, PHGB.DataID))
//     ) AS FreezingBlockQuantity       FROM     PpcHLGradingDetails AS PHGD LEFT JOIN     PpcHLGradingBreakup AS PHGB     ON PHGB.LotID = PHGD.LotID
//   WHERE   IsBlock = 1 and PHGD.TrackID in ($TrackID )   GROUP BY     PHGD.TrackID,PHGB.DataID;";


    //         $stmtFreezingBlockWeight = $this->conn->prepare($SqlFreezingBlockWeight);

    //         $stmtFreezingBlockWeight->execute();

    //         while ($dataRowFreezingBlockWeight = $stmtFreezingBlockWeight->fetch(PDO::FETCH_ASSOC)) {
//             $GradingBreakUpID = $dataRowFreezingBlockWeight['AllDataIDs'];
//             $SoakingStageQuantity = $dataRowFreezingBlockWeight['SoakingStageQuantity']; //nothing but gradingweight
//             $SoakingBlockQuantity = $dataRowFreezingBlockWeight['SoakingBlockQuantity'];
//             $FreezingStageQuantity = $dataRowFreezingBlockWeight['FreezingStageQuantity'];  //nothing but gradingweight
//             $FreezingBlockQuantity = $dataRowFreezingBlockWeight['FreezingBlockQuantity'];

    //             $TotalBalanceSoakingBlockMeatBreakUpwise = $SoakingStageQuantity - $SoakingBlockQuantity;
//             $TotalBalanceFreezingBlockMeatBreakUpwise = $FreezingStageQuantity - $FreezingBlockQuantity;

    //             $SqlUpdateBlockWeights = "UPDATE `PpcHLGradingBreakup` SET `SoakingStageBlockWeight`='$TotalBalanceSoakingBlockMeatBreakUpwise',
//         `FreezingStageBlockWeight`='$TotalBalanceFreezingBlockMeatBreakUpwise' WHERE `DataID`='$GradingBreakUpID'";

    //             $stmtUpdateBlockWeights = $this->conn->prepare($SqlUpdateBlockWeights);
//             $stmtUpdateBlockWeights->execute();

    //         }


    //         $SqlUpdateRmReceivingDetails = "UPDATE RmReceivingDetails AS RRD JOIN (  SELECT   PHGD.TrackID,  SUM(PHGB.SoakingStageBlockWeight + PHGB.FreezingStageBlockWeight) AS TotalBlockMeatQuantity
//     FROM   PpcHLGradingDetails AS PHGD    LEFT JOIN  PpcHLGradingBreakup AS PHGB  ON PHGB.LotID = PHGD.LotID    WHERE   PHGD.TrackID IN ($TrackID)
//     GROUP BY   PHGD.TrackID) AS SubQuery ON RRD.ReceivingID = SubQuery.TrackID SET     RRD.TrackWiseBlockMeatQuantity = SubQuery.TotalBlockMeatQuantity";

    //         $stmtUpdateRmReceivingDetails = $this->conn->prepare($SqlUpdateRmReceivingDetails);
//         $stmtUpdateRmReceivingDetails->execute();

    //         return "success";

    //     }

    function getBlockMeatTrack($DateFilter, $Query)
    {
        $sqlBlockMeatFields = "  ReceivingID as TrackID,TrackCode,ReceivingPlantID,ArrivalDate,TrackWiseBlockMeatQuantity";
        $sqlBlockMeatQuery = "SELECT  $sqlBlockMeatFields FROM `RmReceivingDetails` as RmRD WHERE $DateFilter 
          $Query and TrackWiseBlockMeatQuantity>=100 and IsStoreMaterial=0 and OutStage=0";

        $stmtBlockMeat = $this->conn->prepare($sqlBlockMeatQuery);
        $stmtBlockMeat->execute();

        $i = 0;
        $ArrayReceivedData = [];
        while ($dataRowBlockMeat = $stmtBlockMeat->fetch(PDO::FETCH_ASSOC)) {
            $ArrivalDate = !empty($dataRowBlockMeat['ArrivalDate']) ? date('d-m-Y', strtotime($dataRowBlockMeat['ArrivalDate'])) : '';
            $ArrayReceivedDetails['SNo'] = ++$i;
            $ArrayReceivedDetails['ArrivalDate'] = $ArrivalDate;
            $ArrayReceivedDetails['TrackID'] = $dataRowBlockMeat['TrackID'];
            $ArrayReceivedDetails['TrackCode'] = $dataRowBlockMeat['TrackCode'];
            $ArrayReceivedDetails['ReceivingPlantID'] = $dataRowBlockMeat['ReceivingPlantID'];
            $ArrayReceivedDetails['TrackWiseBlockMeatQuantity'] = $dataRowBlockMeat['TrackWiseBlockMeatQuantity'];
            $ArrayReceivedData[] = $ArrayReceivedDetails;

        }
        // print_r($ArrayReceivedData);
        return $ArrayReceivedData;
    }

    function getBlockMeatBalanceData($BlockMeatTrackIDs)
    {

        $sqlDropBlockMeatTemp = "DROP TABLE IF EXISTS `BlockMeatTemp`";
        $stmtDropBlockMeatTemp = $this->conn->prepare($sqlDropBlockMeatTemp);
        $stmtDropBlockMeatTemp->execute();

        $ArrayBlockMeatTrackIDs = implode(',', $BlockMeatTrackIDs);

        $sqlCreateBlockMeatTempTable = "CREATE  TABLE BlockMeatTemp AS SELECT  PHGD.TrackID as BlockMeatTrackID,    PHGB.PackingGrade as BlockMeatGrade, 
    PHVL.ShortName as BlockMeatVariety,    PHVL.Species as BlockMeatSpecies,    GROUP_CONCAT(PHGB.DataID) AS BlockMeatGradingBreakUpId,
    SUM(PHGB.SoakingStageBlockWeight + PHGB.FreezingStageBlockWeight) AS BlockMeatWeight FROM     PpcHLGradingDetails AS PHGD
LEFT JOIN     PpcHLGradingBreakup AS PHGB     ON PHGB.LotID = PHGD.LotID LEFT JOIN     PpcHLVAVarietyList AS PHVL 
    ON PHVL.VarietyID = PHGB.VarietyID WHERE     PHGD.TrackID IN ($ArrayBlockMeatTrackIDs) GROUP BY     PHGB.PackingGrade, 
    PHVL.ShortName,     PHVL.Species,     PHGD.TrackID HAVING     BlockMeatWeight > 100 ORDER BY     ABS(PHGB.PackingGrade); ";

        $stmtCreateBlockMeatTempTable = $this->conn->prepare($sqlCreateBlockMeatTempTable);
        $stmtCreateBlockMeatTempTable->execute();

        $sqlBlockMeatSelect = "SELECT SUM(BlockMeatWeight) AS TotalBlockMeatBalanceQty, BlockMeatVariety, BlockMeatSpecies, BlockMeatGrade FROM BlockMeatTemp 
        GROUP BY BlockMeatVariety, BlockMeatGrade,BlockMeatSpecies HAVING SUM(BlockMeatWeight) > 100 ORDER BY ABS(BlockMeatGrade); ";
        $stmtBlockMeat = $this->conn->prepare($sqlBlockMeatSelect);
        $stmtBlockMeat->execute();

        $SNO = 0;
        while ($rowstmtBlockMeat = $stmtBlockMeat->fetch(PDO::FETCH_ASSOC)) {
            $BlockMeatBalanceQty = $rowstmtBlockMeat['TotalBlockMeatBalanceQty'];
            $PackingGrade = $rowstmtBlockMeat['BlockMeatGrade'];
            $ShortName = $rowstmtBlockMeat['BlockMeatVariety'];
            $Species = $rowstmtBlockMeat['BlockMeatSpecies'];

            $ArrayVarietyList['SNO'] = ++$SNO;
            $ArrayVarietyList['PackingGrade'] = $rowstmtBlockMeat['BlockMeatGrade'];
            $ArrayVarietyList['ShortName'] = $rowstmtBlockMeat['BlockMeatVariety'] . "-" . $rowstmtBlockMeat['BlockMeatSpecies'];
            $ArrayVarietyList['BalanceQty'] = $BlockMeatBalanceQty;
            $ArrayBalanceData = [];
            $TotalBlockMeatBalanceQty = 0;

            foreach ($BlockMeatTrackIDs as $TrackIds) {
                $TrackID = $TrackIds;
                $sqlBalanceSelectBlockMeat = "  SELECT sum(BlockMeatWeight) as BalanceBlockMeatWeight FROM `BlockMeatTemp` where BlockMeatTrackID='$TrackID' and BlockMeatVariety='$ShortName'
                 and BlockMeatSpecies='$Species' and BlockMeatGrade='$PackingGrade'";
                $stmtBalanceSelecBlockMeat = $this->conn->prepare($sqlBalanceSelectBlockMeat);
                $stmtBalanceSelecBlockMeat->execute();
                $rowBalanceBlockMeat = $stmtBalanceSelecBlockMeat->fetch(PDO::FETCH_ASSOC);
                $BlockMeatBalanceQty = $rowBalanceBlockMeat['BalanceBlockMeatWeight'];
                $ArrayBalanceList['Balance'] = ($BlockMeatBalanceQty < 100) ? '-' : number_format($BlockMeatBalanceQty, 3);
                if ($BlockMeatBalanceQty >= 100) {
                    $TotalBlockMeatBalanceQty += $BlockMeatBalanceQty;
                }
                $ArrayBalanceData[] = $ArrayBalanceList;
            }

            $ArrayVarietyList['TotalBalanceQty'] = number_format($TotalBlockMeatBalanceQty, 3);
            $ArrayVarietyList['BalanceData'] = $ArrayBalanceData;

            $ArrayVarietyData[] = $ArrayVarietyList;
            $GrandBalanceQty += $TotalBlockMeatBalanceQty;
        }

        if ($GrandBalanceQty != 0) {
            $Data['GrandBalanceQty'] = number_format($GrandBalanceQty, 3);
            $Data['ArrayVarietyData'] = $ArrayVarietyData;
        }
        return $Data;

        /***BlockMeat****/

    }

    /***ThawingMeat****/
    //    

    function getThawingMeatTrack($DateFilter, $Query)
    {
        $sqlThawingMeatFields = "  ReceivingID as TrackID,TrackCode,ReceivingPlantID,ArrivalDate,TrackWiseThawingMeatQuantity";
        $sqlThawingMeatQuery = "SELECT  $sqlThawingMeatFields FROM `RmReceivingDetails` as RmRD WHERE $DateFilter 
          $Query and TrackWiseThawingMeatQuantity>100 and IsStoreMaterial=1 and OutStage=1";

        $stmtSelectThawingMeat = $this->conn->prepare($sqlThawingMeatQuery);
        $stmtSelectThawingMeat->execute();

        $i = 0;
        $ArrayReceivedData = [];
        while ($dataRowThawingMeat = $stmtSelectThawingMeat->fetch(PDO::FETCH_ASSOC)) {
            $ArrivalDate = !empty($dataRowThawingMeat['ArrivalDate']) ? date('d-m-Y', strtotime($dataRowThawingMeat['ArrivalDate'])) : '';
            $ArrayReceivedDetails['SNo'] = ++$i;
            $ArrayReceivedDetails['ArrivalDate'] = $ArrivalDate;
            $ArrayReceivedDetails['TrackID'] = $dataRowThawingMeat['TrackID'];
            $ArrayReceivedDetails['TrackCode'] = $dataRowThawingMeat['TrackCode'];
            $ArrayReceivedDetails['ReceivingPlantID'] = $dataRowThawingMeat['ReceivingPlantID'];
            $ArrayReceivedDetails['TrackWiseThawingMeatQuantity'] = $dataRowThawingMeat['TrackWiseThawingMeatQuantity'];
            $ArrayReceivedData[] = $ArrayReceivedDetails;

        }
        return $ArrayReceivedData;
    }

    function getThawingMeatBalanceData($ThawingMeatTrackIDs)
    {
        $sqlSelect = "DROP TABLE IF EXISTS `ThawingMeatTemp`";
        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();

        $ArrayThawingMeatTrackIDs = implode(',', $ThawingMeatTrackIDs);

        $sqlCreateThawingMeatTempTable = "CREATE TABLE ThawingMeatTemp AS SELECT  ReceivingID as ThawingMeatTrackID,ShortName as ThawingMeatVariety,
    Species as ThawingMeatSpecies,    CSD.Grade as ThawingMeatGrade,  SUM(ReceivingQty) - SUM(COALESCE(SentWeight, 0)) AS ThawingMeatWeight
FROM     RmReceivingBreakup AS PHGB LEFT JOIN     CsStockData AS CSD ON CSD.StockID = PHGB.CsStockID LEFT JOIN     ScrmPIVarietyList AS SPVL ON SPVL.VarietyID = CSD.VarietyID
LEFT JOIN     PpcValueAddition AS PVA ON PVA.TrackID = PHGB.ReceivingID WHERE     ReceivingID IN ($ArrayThawingMeatTrackIDs)
GROUP BY    ShortName, Species, CSD.Grade, ReceivingID HAVING     ThawingMeatWeight > 100 ORDER BY     ABS(CSD.Grade)";


        $stmtCreateThawingMeatTempTable = $this->conn->prepare($sqlCreateThawingMeatTempTable);
        $stmtCreateThawingMeatTempTable->execute();

        $sqlThawingMeatSelect = "SELECT SUM(ThawingMeatWeight) AS TotalThawingMeatWeight, ThawingMeatVariety, ThawingMeatSpecies, ThawingMeatGrade FROM ThawingMeatTemp 
        GROUP BY ThawingMeatVariety, ThawingMeatGrade,ThawingMeatSpecies HAVING SUM(ThawingMeatWeight) > 100 ORDER BY ABS(ThawingMeatGrade); ";
        $stmtThawingMeat = $this->conn->prepare($sqlThawingMeatSelect);
        $stmtThawingMeat->execute();

        $SNO = 0;
        while ($rowThawingMeat = $stmtThawingMeat->fetch(PDO::FETCH_ASSOC)) {
            $RawHLBalanceQty = $rowThawingMeat['TotalThawingMeatWeight'];
            $PackingGrade = $rowThawingMeat['ThawingMeatGrade'];
            $ShortName = $rowThawingMeat['ThawingMeatVariety'];
            $Species = $rowThawingMeat['ThawingMeatSpecies'];

            $ArrayVarietyList['SNO'] = ++$SNO;
            $ArrayVarietyList['PackingGrade'] = $rowThawingMeat['ThawingMeatGrade'];
            $ArrayVarietyList['ShortName'] = $rowThawingMeat['ThawingMeatVariety'] . "-" . $rowThawingMeat['ThawingMeatSpecies'];
            $ArrayVarietyList['BalanceQty'] = $RawHLBalanceQty;
            $ArrayBalanceData = [];
            $TotalThawingMeatBalanceQty = 0;

            foreach ($ThawingMeatTrackIDs as $TrackIds) {
                $TrackID = $TrackIds;
                $sqlBalanceSelect = "  SELECT sum(ThawingMeatWeight) as BalanceThawingMeatWeight FROM `ThawingMeatTemp` where ThawingMeatTrackID='$TrackID' and ThawingMeatVariety='$ShortName' and ThawingMeatSpecies='$Species' and ThawingMeatGrade='$PackingGrade'";
                $stmtBalanceSelect = $this->conn->prepare($sqlBalanceSelect);
                $stmtBalanceSelect->execute();
                $rowBalance = $stmtBalanceSelect->fetch(PDO::FETCH_ASSOC);
                $ThawingMeatBalanceQty = $rowBalance['BalanceThawingMeatWeight'];
                $ArrayBalanceList['Balance'] = ($ThawingMeatBalanceQty < 100) ? '-' : number_format($ThawingMeatBalanceQty, 3);
                if ($ThawingMeatBalanceQty >= 100) {
                    $TotalThawingMeatBalanceQty += $ThawingMeatBalanceQty;
                }
                $ArrayBalanceData[] = $ArrayBalanceList;
            }

            $ArrayVarietyList['TotalBalanceQty'] = number_format($TotalThawingMeatBalanceQty, 3);
            $ArrayVarietyList['BalanceData'] = $ArrayBalanceData;

            $ArrayVarietyData[] = $ArrayVarietyList;
            $GrandBalanceQty += $TotalThawingMeatBalanceQty;
        }

        if ($GrandBalanceQty != 0) {
            $Data['GrandBalanceQty'] = number_format($GrandBalanceQty, 3);
            $Data['ArrayVarietyData'] = $ArrayVarietyData;
        }
        return $Data;




















        //         $sqlRawHLFields = "CSD.VarietyID, CSD.Grade,ShortName, SPVL.Species ,group_concat(distinct RmRD.ReceivingID)TrackID,sum(ReceivingQty) as  ThawingQuantity";

        //         $sqlRawHLSelect = "SELECT  $sqlRawHLFields FROM RmReceivingBreakup AS RMRB
//  left join CsStockData as CSD  on CSD.StockID=RMRB.CsStockID
//  left join RmReceivingDetails as RmRD on RmRD.ReceivingID=RMRB.ReceivingID
//  left join ScrmPIVarietyList as SPVL on SPVL.VarietyID=CSD.VarietyID
//   WHERE $DateFilter  $Query  and OutStage=1 
//   and IsStoreMaterial=1 GROUP BY ShortName, CSD.Grade ORDER BY abs(CSD.Grade)";

        //         $stmtRawHL = $this->conn->prepare($sqlRawHLSelect);
//         $stmtRawHL->execute();

        //         $SNO = 0;
//         while ($rowstmtRawHL = $stmtRawHL->fetch(PDO::FETCH_ASSOC)) {
//             $TrackID = $rowstmtRawHL['TrackID'];

        //             $sqlVAWeight = "SELECT SUM(SentWeight) AS TotalVAQty FROM PpcValueAddition WHERE  TrackID in ($TrackID)";
//             $stmtVAWeight = $this->conn->prepare($sqlVAWeight);
//             $stmtVAWeight->execute();
//             $rowVAWeight = $stmtVAWeight->fetch(PDO::FETCH_ASSOC);

        //             $ThawingQuantity = $rowstmtRawHL['ThawingQuantity'] ? $rowstmtRawHL['ThawingQuantity'] : '0';
//             $TotalVAQty = $rowVAWeight['TotalVAQty'] ? $rowVAWeight['TotalVAQty'] : '0';

        //             $ThawingMeatBalanceQty = $ThawingQuantity - $TotalVAQty;

        //             if ($ThawingMeatBalanceQty > 0) {
//                 $PackingGrade = $rowstmtRawHL['Grade'];
//                 $ShortName = $rowstmtRawHL['ShortName'];

        //                 $ArrayVarietyList['SNO'] = ++$SNO;
//                 $ArrayVarietyList['PackingGrade'] = $rowstmtRawHL['Grade'];
//                 $ArrayVarietyList['TrackID'] = $rowstmtRawHL['TrackID'];
//                 $ArrayVarietyList['ShortName'] = $rowstmtRawHL['ShortName'] . "-" . $rowstmtRawHL['Species'];
//                 $ArrayVarietyList['BalanceQty'] = $ThawingMeatBalanceQty;
//                 $ArrayBalanceData = [];
//                 $TotalThawingMeatBalanceQty = 0;

        //                 $sqlTrackID = "SELECT  ReceivingID as TrackID FROM `RmReceivingDetails` as RmRD  WHERE $DateFilter 
//                     $Query and OutStage=1 order by ReceivingID asc ";

        //                 $stmtTrackID = $this->conn->prepare($sqlTrackID);
//                 $stmtTrackID->execute();

        //                 while ($rowTrackID = $stmtTrackID->fetch(PDO::FETCH_ASSOC)) {
//                     $TrackID = $rowTrackID['TrackID'];
//                     // $ThawingMeatBalanceQty = $this->getThawingMeatTrackWeight($TrackID);

        //                     // if ($ThawingMeatBalanceQty > 0) {

        //                     $sqlBalanceFields = "sum(ReceivingQty) AS HLWeight,(select sum(SentWeight) from  PpcValueAddition where TrackID = RmRD.ReceivingID AND SPV.ShortName = '$ShortName' 
//                                 AND CSD.Grade = '$PackingGrade' ) AS TotalQty";
//                     $sqlBalanceSelect = " SELECT $sqlBalanceFields FROM `RmReceivingBreakup` as RRB 
//                                 LEFT JOIN RmReceivingDetails AS RmRD ON RmRD.ReceivingID = RRB.ReceivingID
//                                 left join CsStockData as CSD on CSD.StockID=RRB.CsStockID 
//                                 left join ScrmPIVarietyList as SPV on SPV.VarietyID=CSD.VarietyID WHERE RmRD.ReceivingID ='$TrackID' AND SPV.ShortName = '$ShortName' 
//                                 AND CSD.Grade = '$PackingGrade' ";

        //                     $stmtBalanceSelect = $this->conn->prepare($sqlBalanceSelect);
//                     $stmtBalanceSelect->execute();

        //                     while ($rowBalance = $stmtBalanceSelect->fetch(PDO::FETCH_ASSOC)) {
//                         $HLWeight = $rowBalance['HLWeight'] ? $rowBalance['HLWeight'] : 0;
//                         $TotalQty = $rowBalance['TotalQty'] ? $rowBalance['TotalQty'] : 0;

        //                         $ThawingMeatBalanceQty = $HLWeight + $TotalQty;

        //                         $ArrayBalanceList['Balance'] = ($ThawingMeatBalanceQty == 0) ? '0' : number_format($ThawingMeatBalanceQty, 3);

        //                         $TotalThawingMeatBalanceQty += $ThawingMeatBalanceQty;
//                         $ArrayBalanceData[] = $ArrayBalanceList;
//                     }
//                     // }
//                 }

        //                 $ArrayVarietyList['TotalBalanceQty'] = number_format($TotalThawingMeatBalanceQty, 3);
//                 $ArrayVarietyList['BalanceData'] = $ArrayBalanceData;

        //                 $ArrayVarietyData[] = $ArrayVarietyList;
//                 $GrandBalanceQty += $TotalThawingMeatBalanceQty;

        //             }
//         }

        //         if ($GrandBalanceQty != 0) {
//             $Data['GrandBalanceQty'] = number_format($GrandBalanceQty, 3);
//             $Data['ArrayVarietyData'] = $ArrayVarietyData;
//         }
//         return $Data;




    }
    /****ThawingMeat** */


    //      function getCookedHLTrack($DateFilter, $Query)
//     {
//         $sqlFields = "  ReceivingID as TrackID,TrackCode,ReceivingPlantID,ArrivalDate,TrackWiseRawHLQuantity";
//         $sqlQuery = "SELECT  $sqlFields FROM `RmReceivingDetails` as RmRD WHERE $DateFilter 
//           $Query and TrackWiseRawHLQuantity>100 and IsStoreMaterial=0 and OutStage=0";

    //         $stmtSelect = $this->conn->prepare($sqlQuery);

    //         // echo print_r($sqlQuery);
//         // exit;
//         $stmtSelect->execute();

    //         $i = 0;
//         $ArrayReceivedData = [];
//         while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
//             $ArrivalDate = !empty($dataRow['ArrivalDate']) ? date('d-m-Y', strtotime($dataRow['ArrivalDate'])) : '';
//             $ArrayReceivedDetails['SNo'] = ++$i;
//             $ArrayReceivedDetails['ArrivalDate'] = $ArrivalDate;
//             $ArrayReceivedDetails['TrackID'] = $dataRow['TrackID'];
//             $ArrayReceivedDetails['TrackCode'] = $dataRow['TrackCode'];
//             $ArrayReceivedDetails['ReceivingPlantID'] = $dataRow['ReceivingPlantID'];
//             $ArrayReceivedDetails['TrackWiseRawHLBalanceQty'] = $dataRow['TrackWiseRawHLQuantity'];
//              $ArrayReceivedDetails['rowCount'] = 1;
//             $ArrayReceivedData[] = $ArrayReceivedDetails;

    //         }
//         return $ArrayReceivedData;
//     }
//     function getCookedHLBalanceData($CookedHLTrackIDs)
//     {
//         $sqlSelect = "DROP TABLE IF EXISTS `RAWHLTemp`";
//         $stmtSelect = $this->conn->prepare($sqlSelect);
//         $stmtSelect->execute();

    //         $ArrayCookedHLTrackIds = implode(',', $CookedHLTrackIDs);

    //         $sqlCookedCreateTempTable = "CREATE  TABLE RAWHLTemp AS SELECT  PHGD.TrackID AS RawHLTrackID, PHVL.ShortName AS RawHLVariety, PHVL.Species AS RawHLSpecies,    PackingGrade AS RawHLGrade,    
//     GROUP_CONCAT(PHGB.DataID) AS RawHLGradingBreakUpId,    SUM(PHGB.HLWeight) - COALESCE(( SELECT SUM(SentWeight)  FROM PpcValueAddition 
//         WHERE FIND_IN_SET(GradingBreakupID, GROUP_CONCAT(PHGB.DataID))    ), 0) AS RawHLWeight FROM   PpcHLGradingBreakup AS PHGB
// LEFT JOIN     PpcHLVAVarietyList AS PHVL ON PHGB.VarietyID = PHVL.VarietyID 
// LEFT JOIN     PpcHLGradingDetails AS PHGD ON PHGD.LotID = PHGB.LotID
// WHERE     PHGD.TrackID IN ($ArrayCookedHLTrackIds)    AND PHGB.VarietyID != 0    AND PHGB.Treatment != 'UNT'    AND PHGB.IsBlock = 0 AND PHVL.TypeProduct = 'COOKED' 
// GROUP BY     ShortName, PackingGrade, PHGD.TrackID,PHVL.Species ORDER BY     ABS(PackingGrade) ";

    //         $stmtCreateTempTable = $this->conn->prepare($sqlCookedCreateTempTable);
//         $stmtCreateTempTable->execute();

    //         // echo print_r($sqlCreateTempTable);
//         // exit;

    //         $sqlRawHLSelect = "SELECT SUM(RawHLWeight) AS TotalRawHLWeight, RawHLVariety, RawHLSpecies, RawHLGrade FROM RAWHLTemp 
//         GROUP BY RawHLVariety, RawHLGrade,RawHLSpecies HAVING SUM(RawHLWeight) > 100 ORDER BY ABS(RawHLGrade); ";
//         $stmtRawHL = $this->conn->prepare($sqlRawHLSelect);
//         $stmtRawHL->execute();

    //         $SNO = 0;
//         while ($rowstmtRawHL = $stmtRawHL->fetch(PDO::FETCH_ASSOC)) {
//             $CookedHLBalanceQty = $rowstmtRawHL['TotalRawHLWeight'];
//             $PackingGrade = $rowstmtRawHL['RawHLGrade'];
//             $ShortName = $rowstmtRawHL['RawHLVariety'];
//             $Species = $rowstmtRawHL['RawHLSpecies'];

    //             $ArrayVarietyList['SNO'] = ++$SNO;
//             $ArrayVarietyList['PackingGrade'] = $rowstmtRawHL['RawHLGrade'];
//             $ArrayVarietyList['ShortName'] = $rowstmtRawHL['RawHLVariety'] . "-" . $rowstmtRawHL['RawHLSpecies'];
//             $ArrayVarietyList['BalanceQty'] = $CookedHLBalanceQty;
//             $ArrayBalanceData = [];
//             $TotalCookedHLBalanceQty = 0;

    //             foreach ($CookedHLTrackIDs as $TrackIds) {
//                 $TrackID = $TrackIds;
//                 $sqlCookedBalanceSelect = "  SELECT sum(RawHLWeight) as BalanceRawHLWeight FROM `RAWHLTemp` where RawHLTrackID='$TrackID' and RawHLVariety='$ShortName' and RawHLSpecies='$Species' and RawHLGrade='$PackingGrade'";
//                 $stmtBalanceSelect = $this->conn->prepare($sqlCookedBalanceSelect);
//                 $stmtBalanceSelect->execute();
//                 $rowBalance = $stmtBalanceSelect->fetch(PDO::FETCH_ASSOC);
//                 $CookedHLBalanceQty = $rowBalance['BalanceRawHLWeight'];
//                 $ArrayBalanceList['Balance'] = ($CookedHLBalanceQty == 0) ? '-' : number_format($CookedHLBalanceQty, 3);

    //                 $TotalCookedHLBalanceQty += $CookedHLBalanceQty;
//                 $ArrayBalanceData[] = $ArrayBalanceList;
//             }

    //             $ArrayVarietyList['TotalBalanceQty'] = number_format($TotalCookedHLBalanceQty, 3);
//             $ArrayVarietyList['BalanceData'] = $ArrayBalanceData;

    //             $ArrayVarietyData[] = $ArrayVarietyList;
//             $GrandBalanceQty += $TotalCookedHLBalanceQty;
//         }

    //         if ($GrandBalanceQty != 0) {
//             $Data['GrandBalanceQty'] = number_format($GrandBalanceQty, 3);
//             $Data['ArrayVarietyData'] = $ArrayVarietyData;
//         }
//         return $Data;
//     }


}
