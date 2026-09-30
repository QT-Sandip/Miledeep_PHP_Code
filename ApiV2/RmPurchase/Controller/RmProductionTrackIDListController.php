<?php

// ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once 'UtilController.php';

class RmProductionTrackIDListController
{

    private $conn;
    private $error = null;
    private $msg = "Data Submitted";
    private $db_table = "RmArrivalDetatils";
    private $utilController;


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
    }
    // GET ALL
    public function getArrivalsByVehicle($RequestBody)
    {
        $Data['VehicleData'] = $this->getVehicleData($RequestBody);
        $Data['RmDeHeadingTrackIds'] = $this->getDeheadingTrackIDs($RequestBody);
        $responseData['Error'] = $this->error;
        $responseData['Msg'] = $this->msg;
        $responseData['Data'] = $Data;

        return $responseData;
    }

    public function getVehicleData($RequestBody)
    {
        $ArrivalDate = $RequestBody['ArrivalDate'];
        $PlantID = $RequestBody['PlantID'];
        $IsVehicleTrack = $RequestBody['IsVehicleTrack'];

        if ($PlantID != '' && $PlantID != 0) {
            $Query = "AND ProcessingStationID='$PlantID'";
        }

        if ($IsVehicleTrack == 0) {
            $GroupQry = '  limit 1';
        } else {
            $GroupQry = ' GROUP by RAD.VehicalID, PlantID ';
        }
        // $sqlVehicleFields = "ID as VehicalID,VehicalNumber ,ArrivalStationID as CenterID,ProcessingStationID as PlantID,TrackCode";
        $sqlVehicleFields = "ReceivingID,VehicalID,VehicalNumber ,ArrivalStationID as CenterID,ProcessingStationID as PlantID,TrackCode";
        $sqlVehicle = "SELECT $sqlVehicleFields FROM RmArrivalDetatils AS RAD
        LEFT JOIN RmVehicalDetails AS RVD ON RAD.VehicalID=RVD.ID
        LEFT JOIN RmReceivingDetails AS RRD ON RAD.ArrivalDate=RRD.ArrivalDate AND RAD.VehicalID = RRD.VehicleID  
        AND RAD.ProcessingStationID = RRD.ReceivingPlantID 
        WHERE  RAD.ArrivalDate='$ArrivalDate' $Query AND LotStatus =1   
         $GroupQry ";

        $stmt = $this->conn->prepare($sqlVehicle);
        $stmt->execute();

        // $errorInfo = $stmt->errorInfo();
        // var_dump($errorInfo);

        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($IsVehicleTrack == 0) {
                $ArrayVehicleData['VehicleID'] = '0';
                $ArrayVehicleData['VehicalNumber'] = 'Others';
            } else {
                $ArrayVehicleData['VehicleID'] = $dataRow['VehicalID'] ? $dataRow['VehicalID'] : '0';
                $ArrayVehicleData['VehicalNumber'] = $dataRow['VehicalNumber'] ? $dataRow['VehicalNumber'] : 'Others';
            }


            $ArrayVehicleData['TrackCode'] = $dataRow['TrackCode'] ? ' - ' . $dataRow['TrackCode'] : '';
            $ArrayVehicleData['CenterID'] = $dataRow['CenterID'];
            $ArrayVehicleData['PlantID'] = $dataRow['PlantID'];
            $ArrayVehicleData['ReceivingID'] = $dataRow['ReceivingID'];
            $vehicleData[] = $ArrayVehicleData;
        }
        return $vehicleData;
    }

    function getDeheadingTrackIDs($RequestBody)
    {
        $ArrivalDate = $RequestBody['ArrivalDate'];
        $ReceivingPlantID = (isset($RequestBody['ReceivingPlantID']) && $RequestBody['ReceivingPlantID'] != "") ? $RequestBody['ReceivingPlantID'] : ((isset($RequestBody['PlantID']) && $RequestBody['PlantID'] != "") ? $RequestBody['PlantID'] : 0); //plantID
        $Type = $RequestBody['Type'];
        $IsHONPacking = $RequestBody['IsHONPacking'];
        $IsRejInVA = $RequestBody['IsRejInVA'];
        $AddvalueAddition = (isset($RequestBody['AddvalueAddition']) && $RequestBody['AddvalueAddition'] !== '') ? $RequestBody['AddvalueAddition'] : 0;

        $Query = '';
        $Group = " GROUP BY RRD.TrackCode";
        if ($Type == 'Vehicle') {
            $Query = '';
            $Join = '';
            $Query .= " AND ReceivingPlantID='$ReceivingPlantID'";
        } else if ($Type == 'Deheading') { //uma 17-08-2024
            $HonQuery = '';
            if ($IsHONPacking == 1) {
                $HonQuery = ' AND RRB.HONPacking=0';
            }
            $Query = '';
            $Join = ' LEFT JOIN RmReceivingBreakup AS RRB ON RRD.ReceivingID=RRB.ReceivingID';
            // $Query .= " and IsStoreMaterial!=1 and ReceivingPlantID='$ReceivingPlantID' ";
            $Query .= " and IsStoreMaterial!=1 and ReceivingPlantID='$ReceivingPlantID' and OutStage=0 $HonQuery";
        } else if ($Type == 'grading') {
            $Query = ''; //uma 17-08-2024
            $Field = ',PlantID as DeheadingPlanID, GradingPlantID, RAD.Species';
            $Query .= " and GradingPlantID='$ReceivingPlantID' and IsStoreMaterial!=1 ";
            $Join = " LEFT JOIN RmArrivalDetatils AS RAD ON RAD.VehicalID = RRD.VehicleID and RRD.ArrivalDate = RAD.ArrivalDate
            LEFT JOIN  PpcDeheading as PPD on PPD.TrackID=RRD.ReceivingID AND PPD.CreatedFrom=1";
        } else if ($Type == 'ValueAddition') {
            $Query = '';
            $Field = ',PlantID as DeheadingPlanID, ValueAdditionPlantID AS GradingPlantID,RAD.Species';
            $Query .= " AND ValueAdditionPlantID='$ReceivingPlantID'";//  AND OutStage NOT IN (2,3)
            $Join = " LEFT JOIN RmArrivalDetatils AS RAD ON RAD.VehicalID = RRD.VehicleID and RRD.ArrivalDate=RAD.ArrivalDate
            LEFT JOIN  PpcHLGradingDetails as PHGD on PHGD.TrackID=RRD.ReceivingID AND PHGD.CreatedFrom=1";

            $RejQuery = '';
            if ($IsRejInVA != 1) {
                $RejQuery = ' and IsStoreMaterial=1 ';
            }
            $sqlStoreTrackIDs = "SELECT distinct ReceivingID as TrackID,TrackCode,ReceivingPlantID as PlantID FROM `RmReceivingDetails` AS RRD 
            WHERE ArrivalDate='$ArrivalDate' $RejQuery  and OutStage = 1" . (($ReceivingPlantID == 0) ? "" : (" and ReceivingPlantID='$ReceivingPlantID'"));

            $stmtStoreTrackIDs = $this->conn->prepare($sqlStoreTrackIDs);

            $stmtStoreTrackIDs->execute();
            while ($dataRowStoreTrackIDs = $stmtStoreTrackIDs->fetch(PDO::FETCH_ASSOC)) {
                $TrackID = $dataRowStoreTrackIDs['TrackID'];
                $dataRowStoreTrackIDs['DeheadingPlanID'] = $dataRowStoreTrackIDs['PlantID'];
                $dataRowStoreTrackIDs['GradingPlantID'] = $dataRowStoreTrackIDs['PlantID'];
                $dataRowStoreTrackIDs['Species'] = $this->getStoreSpecies($TrackID);

                $ArrayStoreTrackIDs[] = $dataRowStoreTrackIDs;
            }
        } else if ($Type == 'ValueAdditionTransfer') {
            $Query = '';
            $Field = ',PlantID as DeheadingPlanID, ValueAdditionPlantID AS GradingPlantID';
            $Query .= " AND ValueAdditionPlantID='$ReceivingPlantID'";
            $Query .= " AND IsStoreMaterial=0  AND OutStage = 0 ";
            $Join = " LEFT JOIN  PpcHLGradingDetails as PHGD on PHGD.TrackID=RRD.ReceivingID AND PHGD.CreatedFrom=1";
        } else if ($Type == 'Soaking') {
            $Query = '';
            $VAQuery = '';
            if($AddvalueAddition == 1){
                $VAQuery = " OR (Treatment = 'UNT' AND RRD.ArrivalDate = '$ArrivalDate' AND IsBlock = 1 AND PHGB.valueAddition = 1 AND RRD.ReceivingPlantID='$ReceivingPlantID')";
            }else{
                $VAQuery = '';
            }
            $Field = ',PHGD.PlantID as DeheadingPlanID, SoakingPlantID AS GradingPlantID,RAD.Species';
            // $Query .= " AND (ReceivingID in (SELECT distinct PHGD.TrackID FROM `PpcHLGradingDetails` as PHGD left join 
            // PpcHLGradingBreakup as PHGB on PHGB.LotID=PHGD.LotID LEFT JOIN PpcValueAddition as PVA on PVA.TrackID=PHGD.TrackID
            // WHERE Treatment!='UNT'AND RRD.ArrivalDate='$ArrivalDate' AND ((PHGD.PlantID='$ReceivingPlantID') OR (SoakingPlantID='$ReceivingPlantID'))))
            //  or (ReceivingID in(SELECT distinct TrackID FROM `PpcValueAddition` WHERE TrackID=RRD.ReceivingID and RRD.OutStage=1 and SoakingPlantID='$ReceivingPlantID' AND RRD.ArrivalDate='$ArrivalDate'))";

            //sriram 28-01-25
            // $Query .= " AND (ReceivingID in (SELECT distinct PHGD.TrackID FROM `PpcHLGradingDetails` as PHGD left join 
            // PpcHLGradingBreakup as PHGB on PHGB.LotID=PHGD.LotID LEFT JOIN PpcValueAddition as PVA on PVA.TrackID=PHGD.TrackID
            // WHERE Treatment!='UNT'AND RRD.ArrivalDate='$ArrivalDate' AND (SoakingPlantID='$ReceivingPlantID')))
            // or (ReceivingID in(SELECT distinct TrackID FROM `PpcValueAddition` WHERE TrackID=RRD.ReceivingID and RRD.OutStage=1 and SoakingPlantID='$ReceivingPlantID' AND RRD.ArrivalDate='$ArrivalDate'))";

            $Query .= " AND (RRD.OutStage = 1 AND SoakingPlantID = '$ReceivingPlantID' AND RRD.ArrivalDate = '$ArrivalDate') 
            OR (Treatment != 'UNT' AND RRD.ArrivalDate = '$ArrivalDate' AND SoakingPlantID = '$ReceivingPlantID') 
            OR(Treatment != 'UNT' AND RRD.ArrivalDate = '$ArrivalDate' AND IsBlock = 1 AND RRD.ReceivingPlantID='$ReceivingPlantID')";
            // $Join = " LEFT JOIN RmArrivalDetatils AS RAD ON RAD.VehicalID = RRD.VehicleID
            // LEFT JOIN PpcValueAddition as PVA on PVA.TrackID=RRD.ReceivingID
            $Join = " LEFT JOIN RmArrivalDetatils AS RAD ON RAD.VehicalID = RRD.VehicleID AND RAD.ArrivalDate='$ArrivalDate'
            LEFT JOIN  PpcHLGradingDetails as PHGD on PHGD.TrackID=RRD.ReceivingID AND PHGD.CreatedFrom=1
            LEFT JOIN PpcHLGradingBreakup AS PHGB ON PHGB.LotID = PHGD.LotID AND PHGB.CreatedFrom=1
            LEFT JOIN PpcValueAddition as PVA on PVA.TrackID=RRD.ReceivingID  AND PVA.CreatedFrom=1"; //UMA

            $sqlStoreTrackIDs = "SELECT distinct ReceivingID as TrackID,TrackCode,ReceivingPlantID as PlantID FROM `RmReceivingDetails` AS RRD 
            WHERE ArrivalDate='$ArrivalDate' and IsStoreMaterial=1  and OutStage =2" . (($ReceivingPlantID == 0) ? "" : (" and ReceivingPlantID='$ReceivingPlantID'"));
            
            $stmtStoreTrackIDs = $this->conn->prepare($sqlStoreTrackIDs);
            $stmtStoreTrackIDs->execute();
            while ($dataRowStoreTrackIDs = $stmtStoreTrackIDs->fetch(PDO::FETCH_ASSOC)) {
                $TrackID = $dataRowStoreTrackIDs['TrackID'];
                $dataRowStoreTrackIDs['DeheadingPlanID'] = $dataRowStoreTrackIDs['PlantID'];
                $dataRowStoreTrackIDs['GradingPlantID'] = $dataRowStoreTrackIDs['PlantID'];
                $dataRowStoreTrackIDs['Species'] = $this->getStoreSpecies($TrackID);

                $ArrayStoreTrackIDs[] = $dataRowStoreTrackIDs;
            }
        } else if ($Type == 'Freezing') {
            // $Query .= " and ReceivingPlantID='$ReceivingPlantID'";

            // sriram 28-01-25

            //     $Group = '';
            //     $Field = ",PVA.SoakingPlantID,PHGD.ValueAdditionPlantID ";
            //     // $Query .= " AND (PVA.SoakingPlantID='$ReceivingPlantID' OR ((
            //     //     PVA.SoakingPlantID = '' OR PVA.SoakingPlantID IS NULL
            //     // ) AND ((RRD.ReceivingPlantID='$ReceivingPlantID' AND RRD.OutStage IN ('3')) OR (PHGD.ValueAdditionPlantID='$ReceivingPlantID' AND RRD.OutStage IN ('0')))))
            //     //  OR(Treatment = 'UNT' AND RRD.ArrivalDate = '$ArrivalDate' AND IsBlock = 1 AND RRD.ReceivingPlantID='$ReceivingPlantID') OR (RRD.OutStage=2 AND PSD.PlantID='$ReceivingPlantID' AND RRD.ArrivalDate = '$ArrivalDate')";
            //     $Join = "LEFT JOIN PpcValueAddition as PVA on PVA.TrackID=RRD.ReceivingID
            //     LEFT JOIN  PpcHLGradingDetails as PHGD on PHGD.TrackID=RRD.ReceivingID
            //     LEFT JOIN PpcHLGradingBreakup AS PHGB ON PHGB.LotID = PHGD.LotID";
            //     $Query .= " AND PVA.SoakingPlantID = '$ReceivingPlantID'

            // UNION ALL

            // SELECT DISTINCT
            //     RRD.ReceivingID AS TrackID,
            //     RRD.TrackCode,
            //     RRD.ReceivingPlantID AS PlantID,
            //     PVA.SoakingPlantID,
            //     PHGD.ValueAdditionPlantID
            // FROM RmReceivingDetails AS RRD
            // LEFT JOIN PpcValueAddition AS PVA ON PVA.TrackID = RRD.ReceivingID
            // LEFT JOIN PpcHLGradingDetails AS PHGD ON PHGD.TrackID = RRD.ReceivingID
            // LEFT JOIN PpcHLGradingBreakup AS PHGB ON PHGB.LotID = PHGD.LotID
            // WHERE 
            //     RRD.ArrivalDate = '$ArrivalDate' 
            //     AND (PVA.SoakingPlantID = '' OR PVA.SoakingPlantID IS NULL) 
            //     AND (
            //         (RRD.ReceivingPlantID = '$ReceivingPlantID' AND RRD.OutStage IN('3')) 
            //         OR (PHGD.ValueAdditionPlantID = '$ReceivingPlantID' AND RRD.OutStage IN('0'))
            //     )

            // UNION ALL

            // SELECT DISTINCT
            //     RRD.ReceivingID AS TrackID,
            //     RRD.TrackCode,
            //     RRD.ReceivingPlantID AS PlantID,
            //     NULL AS SoakingPlantID,
            //     NULL AS ValueAdditionPlantID
            // FROM RmReceivingDetails AS RRD
            // LEFT JOIN PpcValueAddition AS PVA ON PVA.TrackID = RRD.ReceivingID
            // LEFT JOIN PpcHLGradingDetails AS PHGD
            // ON
            //     PHGD.TrackID = RRD.ReceivingID
            // LEFT JOIN PpcHLGradingBreakup AS PHGB
            // ON
            //     PHGB.LotID = PHGD.LotID

            // WHERE 
            //     Treatment = 'UNT' 
            //     AND RRD.ArrivalDate = '$ArrivalDate' 
            //     AND IsBlock = 1 
            //     AND RRD.ReceivingPlantID = '$ReceivingPlantID'

            // UNION ALL

            // SELECT DISTINCT
            //     RRD.ReceivingID AS TrackID,
            //     RRD.TrackCode,
            //     RRD.ReceivingPlantID AS PlantID,
            //     NULL AS SoakingPlantID,
            //     NULL AS ValueAdditionPlantID
            // FROM RmReceivingDetails AS RRD
            // LEFT JOIN ProdSoakingDetails AS PSD ON RRD.ReceivingID = PSD.TrackID
            // WHERE 
            //     RRD.ArrivalDate = '$ArrivalDate' 
            //     AND RRD.OutStage = 2 
            //     AND PSD.PlantID = '$ReceivingPlantID'";

            $HonQuery = '';
            $HonJoin = '';
            if ($IsHONPacking == 1) {
                $HonQuery = " OR (RRD.ReceivingPlantID='$ReceivingPlantID' AND RRD.ArrivalDate = '$ArrivalDate' AND RRB.HONPacking=1)";
                $HonJoin = " LEFT JOIN RmReceivingBreakup AS RRB ON RRD.ReceivingID=RRB.ReceivingID";
            }

            $Field = ",PVA.SoakingPlantID,PHGD.ValueAdditionPlantID,
            CASE WHEN IsStoreMaterial = 0 THEN RAD.Species ELSE '' END AS  Species ";
            $Query .= " AND (PVA.SoakingPlantID='$ReceivingPlantID' OR ((
            PVA.SoakingPlantID = '' OR PVA.SoakingPlantID IS NULL
        ) AND ((" . (($ReceivingPlantID == 0) ? "" : ("RRD.ReceivingPlantID='$ReceivingPlantID' AND ")) . "RRD.OutStage IN ('3')) OR (PHGD.ValueAdditionPlantID='$ReceivingPlantID' AND RRD.OutStage IN ('0'))
        OR(Treatment = 'UNT' AND RRD.ArrivalDate = '$ArrivalDate' AND IsBlock = 1 AND RRD.ReceivingPlantID='$ReceivingPlantID') OR (RRD.OutStage=2 AND PSD.PlantID='$ReceivingPlantID' AND RRD.ArrivalDate = '$ArrivalDate'))))
          $HonQuery";
            $Join = "LEFT JOIN  PpcHLGradingDetails as PHGD on PHGD.TrackID=RRD.ReceivingID AND PHGD.CreatedFrom=1
        LEFT JOIN PpcHLGradingBreakup AS PHGB ON PHGB.LotID = PHGD.LotID AND PHGB.CreatedFrom=1
        LEFT JOIN PpcValueAddition as PVA on PVA.TrackID=RRD.ReceivingID  AND PVA.CreatedFrom=1 AND (PVA.GradingBreakupID=PHGB.DataID OR PVA.GradingBreakupID=0 )
        LEFT JOIN ProdSoakingDetails AS PSD ON RRD.ReceivingID = PSD.TrackID AND (PSD.VABreakupID=PHGB.DataID OR PSD.VABreakupID=0) AND PSD.CreatedFrom=1 $HonJoin
        
        LEFT JOIN `RmArrivalDetatils` RAD ON RAD.ArrivalDate=RRD.ArrivalDate AND RAD.VehicalID = RRD.VehicleID ";

        } else if ($Type == 'FreezingDateChange') {

            $HonQuery = '';
            $HonJoin = '';
            if ($IsHONPacking == 1) {
                $HonQuery = " OR (RRD.ReceivingPlantID='$ReceivingPlantID' AND RRD.ArrivalDate = '$ArrivalDate' AND RRB.HONPacking=1)";
                $HonJoin = " LEFT JOIN RmReceivingBreakup AS RRB ON RRD.ReceivingID=RRB.ReceivingID";
            }
            $Join = "";
            $Query = " and ReceivingPlantID='$ReceivingPlantID' $HonJoin $HonQuery";
        }

        $sqlTrackIDFields = "DISTINCT RRD.ReceivingID as TrackID,TrackCode,ReceivingPlantID as PlantID $Field";
        $sqlTrackIDs = "SELECT $sqlTrackIDFields FROM `RmReceivingDetails` AS RRD $Join
        WHERE RRD.ArrivalDate='$ArrivalDate' $Query  $Group";

        // echo $sqlTrackIDs;exit;

        $stmt = $this->conn->prepare($sqlTrackIDs);
        $stmt->execute();

        // $errorInfo = $stmt->errorInfo();
        // var_dump($errorInfo);

        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $PlantID = $dataRow['PlantID'];
            $DeheadingPlanID = $dataRow['DeheadingPlanID'];
            $GradingPlantID = $dataRow['GradingPlantID'];
            if ($GradingPlantID == '') { //uma 
                $GradingPlantID = $DeheadingPlanID;
            }
            if ($GradingPlantID == '') { //sriram
                $GradingPlantID = $dataRow['SoakingPlantID'];
            }
            if ($GradingPlantID == '') { //sriram
                $GradingPlantID = $dataRow['PlantID'];
            }

            $TrackCodeList['TrackID'] = $dataRow['TrackID'];
            $TrackCodeList['TrackCode'] = $dataRow['TrackCode'];
            $TrackCodeList['Species'] = !empty($dataRow['Species']) ? $dataRow['Species'] : '';
            $TrackCodeList['PlantID'] = $PlantID;
            $TrackCodeList['DeheadingPlanID'] = $DeheadingPlanID;
            $TrackCodeList['GradingPlantID'] = $GradingPlantID;
            if ($Type != 'Deheading') { // temparory
                // if ($Type != 'Deheading' ) { // original
                if ($GradingPlantID == '' || $PlantID == $GradingPlantID) {
                    $TrackCodeList['TransferStatus'] = false;
                    $TrackCodeList['TrackCode'] = $dataRow['TrackCode'];
                } else {
                    $TrackCodeList['TransferStatus'] = true;
                    $TrackCodeList['TrackCode'] = $dataRow['TrackCode'] . " (Transferred)";
                }
            }

            $ArrayReceivingDetails[] = $TrackCodeList;
        }

        // echo print_r($ArrayReceivingDetails);
        // exit;
        if (empty($ArrayReceivingDetails)) {
            $ArrayReceivingDetails = $ArrayStoreTrackIDs;
        } else if (empty($ArrayStoreTrackIDs)) {
            $ArrayReceivingDetails = $ArrayReceivingDetails;
        } else {
            $ArrayReceivingDetails = array_merge($ArrayStoreTrackIDs, $ArrayReceivingDetails);
        }

        return $ArrayReceivingDetails;
    }
    function getStoreSpecies($TrackID)
    {
        $sqlSpecies = "SELECT SPV.Species FROM `RmReceivingBreakup` as RRB 
        left join CsStockData as CSD on CSD.StockID=RRB.CsStockID 
        left join ScrmPIVarietyList as SPV on SPV.VarietyID=CSD.VarietyID WHERE ReceivingID='$TrackID'";
        $stmtSpecies = $this->conn->prepare($sqlSpecies);
        $stmtSpecies->execute();
        $dataRowSpecies = $stmtSpecies->fetch(PDO::FETCH_ASSOC);

        $Species = $dataRowSpecies['Species'];
        return $Species;
    }
}
