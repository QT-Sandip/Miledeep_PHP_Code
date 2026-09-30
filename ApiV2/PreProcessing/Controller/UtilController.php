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
        $this->auditLogger = new CentralAuditLogger($this->conn, 'PreProcessing');
    }
    // GET ALL

    function getFormatedDate($RawDate)
    {
        // $RawDate='';
        $RawDate = date("d-m-Y", strtotime($RawDate));

        if ($RawDate == '01-01-1970') {
            $RawDate = 'NA';
        }

        return $RawDate;
    }

    function getFormatedDateEmpty($rawDate)
    {


        $Date = date("d-m-Y", strtotime($rawDate));
        if ($Date == '01-01-1970') {
            $Date = '';
        }
        return $Date;
    }
    public function getFormatedBookingNumber($BookingStationID, $BookingNumber)
    {


        $sqlQuery = "SELECT LotCode FROM `RmPurchaseStations` WHERE PurchaseStationID='$BookingStationID'";
        $stmt = $this->conn->prepare($sqlQuery);
        // $stmt->bindParam(':proformaID', $proformaID);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $LotCode = $dataRow['LotCode'];
        $BookingNumber = str_replace("O", "0", $BookingNumber);
        $BookingNumber = ltrim($BookingNumber, "0");
        $BookingNumber = preg_replace("/[^0-9]/", "", $BookingNumber);
        $BookingNumber = sprintf('%04d', $BookingNumber);
        $BookingNumber = $LotCode . $BookingNumber;

        return $BookingNumber;
    }


    public function checkBookingNoAval($BookingDate, $BookingNumber, $LotId)
    {
        $Qry = '';
        if ($LotId != '') {
            $Qry = "and RmBookingID!='$LotId'";
        }

        $CheckYear = explode('-', $BookingDate);

        if ($CheckYear[1] > 3) {
            $DateStart = $CheckYear[0] . '-04-01'; //2021-04-01
            $DateEnd = ($CheckYear[0] + 1) . '-03-31'; //2022-03-31
        } else {
            $DateStart = ($CheckYear[0] - 1) . '-04-01'; //2021-04-01
            $DateEnd = ($CheckYear[0]) . '-03-31'; //2022-03-31
        }

        $sqlQuery = "SELECT * FROM `RmBookingDetatils`WHERE BookingNumber='$BookingNumber' and BookingDate BETWEEN '$DateStart' AND '$DateEnd' $Qry";
        $stmt = $this->conn->prepare($sqlQuery);
        // $stmt->bindParam(':proformaID', $proformaID);
        $stmt->execute();

        if ($stmt->rowCount() == '0') {
            return true;
        } else {
            return false;
        }

    }

    public function getFormatedArrivalNumber($ArrivalStationID, $ArrivalNumber)
    {


        $sqlQuery = "SELECT LotCode FROM `RmPurchaseStations` WHERE PurchaseStationID='$ArrivalStationID'";
        $stmt = $this->conn->prepare($sqlQuery);
        // $stmt->bindParam(':proformaID', $proformaID);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $LotCode = $dataRow['LotCode'];
        $ArrivalNumber = str_replace("O", "0", $ArrivalNumber);
        $ArrivalNumber = ltrim($ArrivalNumber, "0");
        $ArrivalNumber = preg_replace("/[^0-9]/", "", $ArrivalNumber);
        $ArrivalNumber = sprintf('%04d', $ArrivalNumber);
        $ArrivalNumber = $LotCode . $ArrivalNumber;

        return $ArrivalNumber;
    }

    public function checkArrivalNoAval($ArrivalDate, $ArrivalNumber, $LotId)
    {
        $Qry = '';
        if ($LotId != '') {
            $Qry = "and RmArrivalID!='$LotId'";
        }

        $CheckYear = explode('-', $ArrivalDate);

        if ($CheckYear[1] > 3) {
            $DateStart = $CheckYear[0] . '-04-01'; //2021-04-01
            $DateEnd = ($CheckYear[0] + 1) . '-03-31'; //2022-03-31
        } else {
            $DateStart = ($CheckYear[0] - 1) . '-04-01'; //2021-04-01
            $DateEnd = ($CheckYear[0]) . '-03-31'; //2022-03-31
        }

        $sqlQuery = "SELECT * FROM `RmArrivalDetatils`WHERE ArrivalNumber='$ArrivalNumber' and ArrivalDate BETWEEN '$DateStart' AND '$DateEnd' $Qry";
        $stmt = $this->conn->prepare($sqlQuery);
        // $stmt->bindParam(':proformaID', $proformaID);
        $stmt->execute();

        if ($stmt->rowCount() == '0') {
            return true;
        } else {
            return false;
        }

    }

    public function getRmPrice($Species, $PriceDate, $FINAL_COUNT)
    {

        $sqlQuery = "SELECT * FROM `RmPurchaseRatesHistory` where SType='$Species' and  RmPriceDate <= '$PriceDate 23:59:59'
                        order by RmPriceDate DESC, ID DESC  LIMIT 1";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        if ($stmt->rowCount() != 0) {
            $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
            $TARCK_ID = $dataRow['ID'];
            $RmPriceDate = $dataRow['RmPriceDate'];

            $FINAL_COUNT;

            if ($FINAL_COUNT > 99) {
                $temp_c = 100;
                $sql_acr = "SELECT * FROM RmPurchaseRates WHERE DateID='$TARCK_ID' and RmCount ='$temp_c'";
                $FINAL_COUNT = round($FINAL_COUNT);
            } else if ($FINAL_COUNT < 20) {
                $FINAL_COUNT = ceil($FINAL_COUNT);
                $sql_acr = "SELECT * FROM RmPurchaseRates WHERE DateID='$TARCK_ID' and RmCount =20";
            } else if ($FINAL_COUNT > 52.25 && $FINAL_COUNT < 60.01) {
                $FINAL_COUNT = ceil($FINAL_COUNT);
                $sql_acr = "SELECT * FROM RmPurchaseRates WHERE DateID='$TARCK_ID' and RmCount ='$FINAL_COUNT'";
            } else if ($FINAL_COUNT > 62.25 && $FINAL_COUNT < 70.01) {
                $FINAL_COUNT = ceil($FINAL_COUNT);
                $sql_acr = "SELECT * FROM RmPurchaseRates WHERE DateID='$TARCK_ID' and RmCount ='$FINAL_COUNT'";
            } else if ($FINAL_COUNT > 72.25 && $FINAL_COUNT < 80.01) {
                $FINAL_COUNT = ceil($FINAL_COUNT);
                $sql_acr = "SELECT * FROM RmPurchaseRates WHERE DateID='$TARCK_ID' and RmCount ='$FINAL_COUNT'";
            } else if ($FINAL_COUNT > 82.25 && $FINAL_COUNT < 90.01) {
                $FINAL_COUNT = ceil($FINAL_COUNT);
                $sql_acr = "SELECT * FROM RmPurchaseRates WHERE DateID='$TARCK_ID' and RmCount ='$FINAL_COUNT'";
            } else if ($FINAL_COUNT > 92.25 && $FINAL_COUNT < 100) {
                $FINAL_COUNT = ceil($FINAL_COUNT);
                $sql_acr = "SELECT * FROM RmPurchaseRates WHERE DateID='$TARCK_ID' and RmCount ='$FINAL_COUNT'";
            } else {

                $TEMP_DIFF = $FINAL_COUNT - round($FINAL_COUNT);

                if (abs($TEMP_DIFF) > 0.25 && abs($TEMP_DIFF) < 0.76) {

                    $TEMP_DIFF;
                    if ($TEMP_DIFF > 0) {
                        $LC = round($FINAL_COUNT);
                        $UC = ceil($FINAL_COUNT);


                    } else {
                        $LC = round($FINAL_COUNT) - 1;
                        $UC = round($FINAL_COUNT);


                    }

                    $sql_acr = "SELECT SUM(RmPrice/2) as RmPrice,SUM(RmCount/2) AS COUNT FROM RmPurchaseRates
                    WHERE DateID='$TARCK_ID' and RmCount >='$LC' and RmCount <='$UC'";
                } else {

                    $temp_c = round($FINAL_COUNT);
                    $sql_acr = "SELECT * FROM RmPurchaseRates WHERE DateID='$TARCK_ID' and RmCount ='$temp_c'";



                }

            }
            $stmt_acr = $this->conn->prepare($sql_acr);
            $stmt_acr->execute();
            $row = $stmt_acr->fetch(PDO::FETCH_ASSOC);
            $RmPrice = round($row['RmPrice'], 2);

            $data['RmPrice'] = $RmPrice;
            $data['BillHonCount'] = $FINAL_COUNT;
            return $data;

        }
    }






    public function getUserName($ID)
    {

        $sqlQuery = "SELECT UserName FROM `RmUsersList` WHERE ID='$ID'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        if ($stmt->rowCount() == '0') {
            $UserName = '';
        } else {
            $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
            $UserName = $dataRow['UserName'];
        }
        return $UserName;
    }



    function getSpecies()
    {


        $sqlQuery = "SELECT ID as SpeciesID,SpeciesName,ShortCode,Description FROM `ScrmSpeciesMaster` WHERE Status=1 ORDER by SortOrder Asc";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }
        return $Data;
    }
    function getRmPurchaseCenters($CenterID)
    {


        $sqlQuery = "SELECT StationName FROM `RmPurchaseStations` where  PurchaseStationID='$CenterID' ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $Data['StationName'] = $dataRow['StationName'];
        return $Data;
    }


    function getRmProcessingCenters($ProcessingStationID)
    {


        $sqlQuery = "SELECT StationName FROM `RmProcessingStations` where  ProcessingStationID='$ProcessingStationID' order by SortOrder asc";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $Data['StationName'] = $dataRow['StationName'];
        return $Data;
    }


    function getPurchaseCategory($RmCategoryID)
    {


        $sqlQuery = "SELECT CatID as RmCategoryID,PurchaseType as RmCategoryName FROM `RmPurchaseCategory` where CatID='$RmCategoryID'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);

        return $dataRow;
    }
    function getVehiclesDetails($VehicalID)
    {


        $sqlQuery = "SELECT ID as VehicalID,VehicalNumber FROM `RmVehicalDetails` where ID='$VehicalID'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);

        return $dataRow;
    }
    function getUsers($UserType)
    {


        $sqlQuery = "SELECT ID as UserID ,UserName FROM `RmUsersList`  WHERE ID='$UserType' ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);

        return $dataRow;
    }

    function getRMSuppliers($RmSupplierID)
    {


        $sqlQuery = "SELECT  ID as SupplierID,FinancierName as SupplierName,
        Address As SupplierAddress ,GstNo,PanNumber,PhoneNumber FROM `RmSuppliers` where ID='$RmSupplierID'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);


        return $dataRow;
    }


    function getDefectsList()
    {


        $sqlQuery = "SELECT DefectID As VarietyID,DefectName as VarietyName FROM `RmDefectsList`";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $Data[] = $dataRow;
        }

        return $Data;
    }



    function getArrivalPriceDetails($ArrivalID)
    {
        /*

        TotalBillAmount     > BillAmount + Extras + HarvestCommision  extras will direct effect on price

        ActualBillAmount    > Weight * Rate
        ExtraBillAmount     > Weight * Extra/Kg
        HarvestCommision    > Weight * HarvestCommision/Kg

        BillTDS
        HarvestTDS
        ExtraTDS


        TotalTDS            > BillTDS+HarvestTDS+ExtraTDS

        BillExcTds          > ActualBillAmount+ExtraBillAmount - (BillTDS+ExtraTDS)
        */

        $sqlQuery = "SELECT  HarvestCommission,Extra FROM `RmArrivalDetatils` WHERE  RmArrivalID='$ArrivalID'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $HarvestExpKg = $dataRow['HarvestCommission'];
        $ExtraKg = $dataRow['Extra'];

        $sqlQuery = "SELECT AVG(Count) as ArrivalCount,SUM(Weight) as ArrivalWeight,AVG(Rate) as ArrivalRate ,sum(Weight*Rate) as BillAmount
    FROM `RmArrivalBreakUpDetails` WHERE ArrivalID='$ArrivalID'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            $data['ArrivalCount'] = 'NA';
            $data['ArrivalWeight'] = 'NA';
            $data['BookingRate'] = 'NA';
            $data['TotalAmount'] = 'NA';

        } else {
            $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
            $data['ArrivalCount'] = round($dataRow['ArrivalCount'], 2);
            $data['ArrivalWeight'] = round($dataRow['ArrivalWeight'], 3);
            $data['ArrivalRate'] = round($dataRow['ArrivalRate'], 2);


            $BillAmount = $dataRow['BillAmount'];
            $ExtraBillAmount = $dataRow['ArrivalWeight'] * $ExtraKg;
            $HarvestBillAmount = $dataRow['ArrivalWeight'] * $HarvestExpKg;

            $TotalBillAmount = $BillAmount + $ExtraBillAmount + $HarvestBillAmount;

            $BillTDS = $this->getTdsValues($BillAmount, 0.1);
            $ExtraTDS = $this->getTdsValues($ExtraBillAmount, 0.1);
            $HarvestTDS = $this->getTdsValues($HarvestBillAmount, 0.1);
            $TotalTDS = $BillTDS + $ExtraTDS + $HarvestTDS;

            $TotalBillAmountExTDS = $TotalBillAmount - ($BillTDS + $ExtraTDS + $HarvestTDS);
            $BillAmountExTDS = ($BillAmount + $ExtraBillAmount) - ($BillTDS + $ExtraTDS);
            $ExtraBillAmountExTDs = $ExtraBillAmount - $ExtraTDS;
            $HarvestBillAmountExTDS = $HarvestBillAmount - $HarvestTDS;

            $data['HarvestExpanse'] = $HarvestExpKg;
            $data['BillAmount'] = $BillAmount;
            $data['ExtraBillAmount'] = $ExtraBillAmount;
            $data['HarvestBillAmount'] = $HarvestBillAmount;
            $data['TotalBillAmount'] = $TotalBillAmount;
            $data['BillTDS'] = $BillTDS;
            $data['ExtraTDS'] = $ExtraTDS;
            $data['HarvestTDS'] = $HarvestTDS;
            $data['TotalTDS'] = $TotalTDS;
            $data['TotalBillAmountExTDS'] = $TotalBillAmountExTDS;
            $data['BillAmountExTDS'] = $BillAmountExTDS;
            $data['ExtraBillAmountExTDs'] = $ExtraBillAmountExTDs;
            $data['HarvestBillAmountExTDS'] = $HarvestBillAmountExTDS;

        }
        return $data;

    }

    function getBillPriceDetails($ArrivalID)
    {

        /*

TotalBillAmount     > BillAmount + Extras + HarvestCommision  extras will direct effect on price

ActualBillAmount    > Weight * Rate
ExtraBillAmount     > Weight * Extra/Kg
HarvestCommision    > Weight * HarvestCommision/Kg

BillTDS
HarvestTDS
ExtraTDS


TotalTDS            > BillTDS+HarvestTDS+ExtraTDS

BillExcTds          > ActualBillAmount+ExtraBillAmount - (BillTDS+ExtraTDS)
*/

        $sqlQuery = "SELECT  HarvestCommission,Extra FROM `RmArrivalDetatils` WHERE  RmArrivalID='$ArrivalID'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $HarvestExpKg = $dataRow['HarvestCommission'];
        $ExtraKg = $dataRow['Extra'];

        $sqlQuery = "SELECT AVG(Count) as ArrivalCount,SUM(Weight) as ArrivalWeight,AVG(Rate) as ArrivalRate ,sum(Weight*Rate) as BillAmount
    FROM `RmBillBreakup` WHERE ArrivalID='$ArrivalID'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            $data['ArrivalCount'] = 'NA';
            $data['ArrivalWeight'] = 'NA';
            $data['BookingRate'] = 'NA';
            $data['TotalAmount'] = 'NA';

        } else {
            $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
            $data['ArrivalCount'] = round($dataRow['ArrivalCount'], 2);
            $data['ArrivalWeight'] = round($dataRow['ArrivalWeight'], 3);
            $data['ArrivalRate'] = round($dataRow['ArrivalRate'], 2);


            $BillAmount = $dataRow['BillAmount'];
            $ExtraBillAmount = $dataRow['ArrivalWeight'] * $ExtraKg;
            $HarvestBillAmount = $dataRow['ArrivalWeight'] * $HarvestExpKg;

            $TotalBillAmount = $BillAmount + $ExtraBillAmount + $HarvestBillAmount;

            $BillTDS = $this->getTdsValues($BillAmount, 0.1);
            $ExtraTDS = $this->getTdsValues($ExtraBillAmount, 0.1);
            $HarvestTDS = $this->getTdsValues($HarvestBillAmount, 0.1);
            $TotalTDS = $BillTDS + $ExtraTDS + $HarvestTDS;

            $TotalBillAmountExTDS = $TotalBillAmount - ($BillTDS + $ExtraTDS + $HarvestTDS);
            $BillAmountExTDS = ($BillAmount + $ExtraBillAmount) - ($BillTDS + $ExtraTDS);
            $ExtraBillAmountExTDs = $ExtraBillAmount - $ExtraTDS;
            $HarvestBillAmountExTDS = $HarvestBillAmount - $HarvestTDS;

            $data['BillAmount'] = $BillAmount;
            $data['ExtraBillAmount'] = $ExtraBillAmount;
            $data['HarvestBillAmount'] = $HarvestBillAmount;
            $data['TotalBillAmount'] = $TotalBillAmount;
            $data['BillTDS'] = $BillTDS;
            $data['ExtraTDS'] = $ExtraTDS;
            $data['HarvestTDS'] = $HarvestTDS;
            $data['TotalTDS'] = $TotalTDS;
            $data['TotalBillAmountExTDS'] = $TotalBillAmountExTDS;
            $data['BillAmountExTDS'] = $BillAmountExTDS;
            $data['ExtraBillAmountExTDs'] = $ExtraBillAmountExTDs;
            $data['HarvestBillAmountExTDS'] = $HarvestBillAmountExTDS;

        }
        return $data;

    }



    function getTdsValues($ValueTotal, $TdsPerc)
    {
        $ValueTotal;
        $ValueTotalExTds = ($ValueTotal * $TdsPerc) / 100;
        $ValueTotalExTds = round($ValueTotalExTds, 2);
        //  $ValueTotalExTds=362.09;
        $decimal = fmod($ValueTotalExTds, 1); //0.25
        $AryValueTds = explode('.', $ValueTotalExTds);
        if (count($AryValueTds) > 1) {
            $postFix = $AryValueTds[1];
            if ($decimal > 0.09) {
                $ValueTotalExTds = ceil($ValueTotalExTds);
            } else {
                $ValueTotalExTds = $AryValueTds[0];
            }
        }


        //return $ValueTotalExTds;
        return $ValueTotalExTds;
    }
    function UpdateTrackWiseRawHLWeight($TrackID, $DataID, $IsBlock, $requestBody = array())
    {
        $sqlTrackCount = "SELECT count(*) as TrackCount FROM `RmReceivingDetails`  WHERE ReceivingID= '$TrackID'and IsStoreMaterial=0
    and OutStage=0  ";

        $stmtTrackCount = $this->conn->prepare($sqlTrackCount);
        $stmtTrackCount->execute();
        $dataRowTrackCount = $stmtTrackCount->fetch(PDO::FETCH_ASSOC);
        $TrackCount = $dataRowTrackCount['TrackCount'];
        if ($TrackCount > 0) {

            $existingTrack = $this->auditLogger->fetchRowById('RmReceivingDetails', 'ReceivingID', $TrackID);

            $sqlUpdateBreakupWeight = "UPDATE `PpcHLGradingBreakup` SET `SoakingStageBlockWeight`='0',`FreezingStageBlockWeight`='0' WHERE `DataID`='$DataID' and  $IsBlock=0";
            $stmtUpdateBreakupWeight = $this->conn->prepare($sqlUpdateBreakupWeight);
            $stmtUpdateBreakupWeight->execute();

            $sqlUpdateQuery = " UPDATE RmReceivingDetails AS RRD JOIN ( SELECT PHGD.TrackID, SUM(PHGB.HLWeight) AS TotalHLWeight,
       GROUP_CONCAT(PHGB.DataID) AS ID FROM PpcHLGradingDetails AS PHGD LEFT JOIN PpcHLGradingBreakup AS PHGB ON PHGB.LotId = PHGD.LotID AND PHGB.CreatedFrom=1
       WHERE PHGD.TrackID IN ($TrackID) AND PHGB.Treatment != 'UNT' AND PHGB.IsBlock = 0 AND PHGD.CreatedFrom=1 GROUP BY PHGD.TrackID ) AS GD
       ON RRD.ReceivingID = '$TrackID' SET RRD.TrackWiseRawHLQuantity = COALESCE(GD.TotalHLWeight - ( SELECT COALESCE(SUM(SentWeight), 0)
       FROM PpcValueAddition WHERE FIND_IN_SET(GradingBreakupID, GD.ID) ), GD.TotalHLWeight)";

            $stmtUpdateQuery = $this->conn->prepare($sqlUpdateQuery);
            $stmtUpdateQuery->execute();

            $updatedTrack = $this->auditLogger->fetchRowById('RmReceivingDetails', 'ReceivingID', $TrackID);
            $this->auditLogger->logUpdate('RmReceivingDetails', $TrackID, $existingTrack, $updatedTrack, $requestBody, [
                'Remarks' => 'Track wise raw HL weight updated',
            ]);
        }
        return "success";
    }
    function UpdateTrackWiseRawMeatWeight($TrackID, $VaBreakUpID, $RequestBody = array() )
    {
        $RawQuery = '';
        $CookedQuery = '';
        if ($VaBreakUpID != '') {

            // $sqlSentWeight = "SELECT TrackID,SentWeight FROM `PpcValueAddition` WHERE VLID='$VaBreakUpID'";
            $sqlSentWeight = "SELECT     PVA.TrackID,    CASE   WHEN TypeProduct = 'RAW' THEN SentWeight  ELSE 0     END AS RawWeight,
    CASE   WHEN TypeProduct = 'COOKED' THEN SentWeight  ELSE 0     END AS CookedWeight FROM PpcValueAddition AS PVA
LEFT JOIN PpcHLVAVarietyList AS PHVL ON PHVL.VarietyID = PVA.VarietyID WHERE VLID = '$VaBreakUpID'";

            $stmtSentWeight = $this->conn->prepare($sqlSentWeight);
            $stmtSentWeight->execute();
            $dataRowSentWeight = $stmtSentWeight->fetch(PDO::FETCH_ASSOC);
            $RawWeight = $dataRowSentWeight['RawWeight'];
            $CookedWeight = $dataRowSentWeight['CookedWeight'];
            $RawQuery = " -$RawWeight";
            $CookedQuery = " -$CookedWeight";

        }

        $existingTrack = $this->auditLogger->fetchRowById('RmReceivingDetails', 'ReceivingID', $TrackID);

        $sqlUpdateRawMeatQtyQuery = " UPDATE RmReceivingDetails AS RRD JOIN (    SELECT   PVA.TrackID,  SUM(CASE WHEN TypeProduct = 'RAW' THEN PVA.WtAftrVA ELSE 0 END)
        $RawQuery  - COALESCE(( SELECT SUM(PSD.SoakInQty)  FROM ProdSoakingDetails AS PSD  WHERE FIND_IN_SET(PSD.VABreakupID, GROUP_CONCAT(PVA.GradingBreakupID))
        ), 0) AS TrackWiseRawMeatQuantity,  SUM(CASE WHEN TypeProduct = 'COOKED' THEN PVA.WtAftrVA ELSE 0 END) $CookedQuery - COALESCE((    SELECT SUM(PSD.SoakInQty)
            FROM ProdSoakingDetails AS PSD  WHERE FIND_IN_SET(PSD.VABreakupID, GROUP_CONCAT(PVA.GradingBreakupID))), 0) AS TrackWiseCookedMeatQuantity
    FROM PpcValueAddition AS PVA    LEFT JOIN PpcHLGradingBreakup AS PHGB ON PHGB.DataID = PVA.GradingBreakupID AND PHGB.CreatedFrom=1    LEFT JOIN PpcHLVAVarietyList AS PHVL ON PHVL.VarietyID = PVA.VarietyID
    WHERE PVA.TrackID IN ($TrackID)      AND PHGB.IsBlock = 0       AND PHGB.Treatment != 'UNT'    GROUP BY PVA.TrackID) AS CalculatedData
    ON RRD.ReceivingID = '$TrackID' SET     RRD.TrackWiseRawMeatQuantity = CalculatedData.TrackWiseRawMeatQuantity,    RRD.TrackWiseCookedMeatQuantity = CalculatedData.TrackWiseCookedMeatQuantity";

        $stmtUpdateRawMeatQtyQuery = $this->conn->prepare($sqlUpdateRawMeatQtyQuery);
        $stmtUpdateRawMeatQtyQuery->execute();

        $updatedTrack = $this->auditLogger->fetchRowById('RmReceivingDetails', 'ReceivingID', $TrackID);
        $this->auditLogger->logUpdate('RmReceivingDetails', $TrackID, $existingTrack, $updatedTrack, $RequestBody, [
            'Remarks' => 'Track wise raw/cooked meat weight updated',
        ]);

        return "success";

    }
    //     function UpdateTrackWiseCookedMeatWeight($TrackID, $VaBreakUpID)
//     {
//         $Query = '';
//         if ($VaBreakUpID != '') {

    //             $sqlSentWeight = "SELECT TrackID,SentWeight FROM `PpcValueAddition` WHERE VLID='$VaBreakUpID'";
//             $stmtSentWeight = $this->conn->prepare($sqlSentWeight);
//             $stmtSentWeight->execute();
//             $dataRowSentWeight = $stmtSentWeight->fetch(PDO::FETCH_ASSOC);
//             $SentWeight = $dataRowSentWeight['SentWeight'];
//             $Query = " -$SentWeight";

    //         }
//         $sqlUpdateRawMeatQtyQuery = " UPDATE  RmReceivingDetails AS RRD join (SELECT  PVA.TrackID,  SUM(PVA.WtAftrVA) AS VAQuantity,  (
//             SUM(PVA.WtAftrVA)  $Query -  COALESCE((  SELECT   SUM(PSD.SoakInQty)   FROM    ProdSoakingDetails AS PSD    WHERE  
//              FIND_IN_SET(PSD.VABreakupID, GROUP_CONCAT(PVA.GradingBreakupID)) ), 0) ) AS TrackWiseCookedMeatQuantity
//     FROM   PpcValueAddition AS PVA    LEFT JOIN   PpcHLGradingBreakup AS PHGB      ON PHGB.DataID = PVA.GradingBreakupID
//     LEFT JOIN     PpcHLVAVarietyList AS PHVL     ON PHVL.VarietyID = PVA.VarietyID    WHERE         PVA.TrackID IN ($TrackID) 
//         AND PHGB.IsBlock = 0         AND PHGB.Treatment != 'UNT'  and TypeProduct='COOKED'    GROUP BY         PVA.TrackID
// ) AS CalculatedData ON     RRD.ReceivingID  = '$TrackID' SET  RRD.TrackWiseCookedMeatQuantity = COALESCE(CalculatedData.TrackWiseCookedMeatQuantity,0)";

    //         $stmtUpdateRawMeatQtyQuery = $this->conn->prepare($sqlUpdateRawMeatQtyQuery);
//         $stmtUpdateRawMeatQtyQuery->execute();

    //         return "success";

    //     }
    function UpdateTrackWiseThawingMeatWeight($TrackID)
    {
        $sqlThawingMeatCount = "SELECT count(*) as TrackCount FROM `RmReceivingDetails`  WHERE ReceivingID= '$TrackID'and IsStoreMaterial=1 ";
       

        $stmtThawingMeatCount = $this->conn->prepare($sqlThawingMeatCount);
        $stmtThawingMeatCount->execute();
        $dataRowThawingMeatCount = $stmtThawingMeatCount->fetch(PDO::FETCH_ASSOC);
        $TrackCount = $dataRowThawingMeatCount['TrackCount'];
        if ($TrackCount > 0) {
          
//           $sqlUpdateThawingMeatQuery = " UPDATE RmReceivingDetails AS RRD JOIN ( SELECT RRD.ReceivingID as TrackID, SUM(RRB.ReceivingQty) AS TotalThawingWeight 
//     FROM RmReceivingDetails as RRD  LEFT JOIN RmReceivingBreakup AS RRB ON RRB.ReceivingID = RRD.ReceivingID   
//      WHERE RRD.ReceivingID IN ($TrackID) and IsStoreMaterial=1 and OutStage=1  GROUP BY RRD.ReceivingID ) AS GD    ON RRD.ReceivingID = '$TrackID'
//      SET RRD.TrackWiseThawingMeatQuantity = COALESCE(GD.TotalThawingWeight - ( SELECT COALESCE(SUM(SentWeight),0) 
//    FROM PpcValueAddition WHERE FIND_IN_SET(TrackID, GD.TrackID) ), GD.TotalThawingWeight)";

  $sqlUpdateThawingMeatQuery = "UPDATE RmReceivingDetails AS RRD
    JOIN ( SELECT RRD.ReceivingID AS TrackID,
           SUM(RRB.ReceivingQty) AS TotalThawingWeight
    FROM RmReceivingDetails AS RRD
    LEFT JOIN RmReceivingBreakup AS RRB
           ON RRB.ReceivingID = RRD.ReceivingID
    WHERE RRD.ReceivingID IN ($TrackID)
      AND IsStoreMaterial = 1
    GROUP BY RRD.ReceivingID) AS GD ON RRD.ReceivingID = GD.TrackID
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
        ELSE GD.TotalThawingWeight END)";

            $stmtUpdateThawingMeatQuery = $this->conn->prepare($sqlUpdateThawingMeatQuery);
            $stmtUpdateThawingMeatQuery->execute();
        }
        return "success";
    }

    function UpdateTrackWiseBlockMeatWeight($TrackID,$RequestBody = array())
    {

        $SqlFreezingBlockWeight = "SELECT     PHGD.TrackID,    GROUP_CONCAT(PHGB.DataID) AS AllDataIDs,    ( SELECT COALESCE(DataID ,'')
        FROM   PpcHLGradingBreakup   WHERE    IsBlock = 1   AND Treatment != 'UNT'  AND FIND_IN_SET(DataID, PHGB.DataID) AND CreatedFrom=1
    ) AS SoakingBreakUpID,    (   SELECT  COALESCE(sum(HLWeight) ,0)  FROM   PpcHLGradingBreakup   WHERE  IsBlock = 1    AND Treatment != 'UNT' 
            AND  FIND_IN_SET(DataID, PHGB.DataID) AND CreatedFrom=1   ) AS SoakingStageQuantity,    (  SELECT  COALESCE(DataID ,'') FROM  PpcHLGradingBreakup 
        WHERE   IsBlock = 1  AND Treatment = 'UNT'  AND FIND_IN_SET(DataID, PHGB.DataID)  AND CreatedFrom=1  ) AS FreezingBreakUpID    , (   SELECT 
           COALESCE(sum(HLWeight) ,0)   FROM      PpcHLGradingBreakup   WHERE   IsBlock = 1  AND Treatment = 'UNT' AND    FIND_IN_SET(DataID, PHGB.DataID) AND CreatedFrom=1
    ) AS FreezingStageQuantity , (   SELECT  COALESCE(sum(SoakInQty) ,0)   FROM  ProdSoakingDetails  WHERE    FIND_IN_SET(VABreakupID, PHGB.DataID)
    ) AS SoakingBlockQuantity   , ( SELECT   COALESCE(sum(ProductionWeight + RejectionWeight) ,0) FROM  ProdFreezingData  WHERE    FIND_IN_SET(GradingBreakupID, PHGB.DataID) and GradingBreakupID not in (SELECT distinct VABreakupID FROM ProdSoakingDetails where    FIND_IN_SET(VABreakupID, PHGB.DataID))
    ) AS FreezingBlockQuantity       FROM     PpcHLGradingDetails AS PHGD LEFT JOIN     PpcHLGradingBreakup AS PHGB     ON PHGB.LotID = PHGD.LotID AND PHGB.CreatedFrom=1
  WHERE   IsBlock = 1 and PHGD.TrackID in ($TrackID ) AND PHGD.CreatedFrom=1   GROUP BY     PHGD.TrackID,PHGB.DataID;";

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
            $stmtUpdateBlockWeights = $this->conn->prepare($SqlUpdateBlockWeights);
            $stmtUpdateBlockWeights->execute();

        }

        $SqlUpdateRmReceivingDetails = "UPDATE RmReceivingDetails AS RRD JOIN (  SELECT   PHGD.TrackID,  SUM(PHGB.SoakingStageBlockWeight + PHGB.FreezingStageBlockWeight) AS TotalBlockMeatQuantity
    FROM   PpcHLGradingDetails AS PHGD    LEFT JOIN  PpcHLGradingBreakup AS PHGB  ON PHGB.LotID = PHGD.LotID AND PHGB.CreatedFrom=1   WHERE   PHGD.TrackID IN ($TrackID) AND PHGD.CreatedFrom=1
    GROUP BY   PHGD.TrackID) AS SubQuery ON RRD.ReceivingID = '$TrackID' SET     RRD.TrackWiseBlockMeatQuantity = SubQuery.TotalBlockMeatQuantity";

        $stmtUpdateRmReceivingDetails = $this->conn->prepare($SqlUpdateRmReceivingDetails);
        $stmtUpdateRmReceivingDetails->execute();

        return "success";

    }

    function getCostingBreakUp($LotID)
    {
        // Final grading
        $SqlFinalGrading = "SELECT * FROM `PpcHLGradingBreakup` WHERE LotID = '$LotID' AND CreatedFrom=1 ORDER BY HLCount ASC";
        $StmtFinalGrading = $this->conn->prepare($SqlFinalGrading);
        $StmtFinalGrading->execute();

        // Lot details
        $SqlRotDetails = "SELECT RAD.Species as SType, RLD.PurchaseDate 
            FROM `RmArrivalDetatils` as RAD 
            LEFT JOIN `RmLotDetatils` as RLD ON RAD.RmArrivalID = RLD.RmReferenceID 
            LEFT JOIN RmPurchaseStations as RPS ON RPS.PurchaseStationID = RLD.PurchaseStationID 
            WHERE RmLotID = '$LotID'";
        $StmtLotDetails = $this->conn->prepare($SqlRotDetails);
        $StmtLotDetails->execute();
        $RowLotDetails = $StmtLotDetails->fetch(PDO::FETCH_ASSOC);

        if (!$RowLotDetails) return;

        $SType = $RowLotDetails['SType'];
        $PurchaseDate = $RowLotDetails['PurchaseDate'];

        // TrackID
        $SqlTrackId = "SELECT * FROM `SalesCostingRatesHistory` 
            WHERE FromDate <= '$PurchaseDate' AND SType = '$SType' 
            ORDER BY FromDate DESC, ID DESC LIMIT 1";
        $StmtTrackId = $this->conn->prepare($SqlTrackId);
        $StmtTrackId->execute();
        $RowTrackId = $StmtTrackId->fetch(PDO::FETCH_ASSOC);
        if (!$RowTrackId) return;

        $TrackID = $RowTrackId['ID'];

        // Final grading loop
        while ($RowFinalCounts = $StmtFinalGrading->fetch(PDO::FETCH_ASSOC)) {
            $HLCount = $RowFinalCounts['HLCount'];
            $HlWeight = $RowFinalCounts['HlWeight'];

            $SqlCostingRates = "SELECT * FROM SalesCostingRates 
                WHERE TrackID = '$TrackID' AND LowerLimit <= '$HLCount' AND UpperLimit >= '$HLCount'";
            $StmtCostingRates = $this->conn->prepare($SqlCostingRates);
            $StmtCostingRates->execute();
            $RowCostingRates = $StmtCostingRates->fetch(PDO::FETCH_ASSOC);
            if (!$RowCostingRates) continue;

            $Usd = $RowCostingRates['USD'];
            $Grade = $RowCostingRates['Grade'];

            $GradesUsdQuery = "UPDATE PpcHLGradingBreakup 
                SET PackingGrade = '$Grade', Usd = '$Usd' 
                WHERE LotID = '$LotID' AND HLCount = '$HLCount'";
            $GradesUsdExec = $this->conn->prepare($GradesUsdQuery);
            $GradesUsdExec->execute();
        }
    }
    
}

?>
