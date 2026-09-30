<?php
include_once '../Global/Util.php';
include_once '../App/Controller/AppUtilController.php';


class UtilController
{

    private $conn;
    private $clientid;
    private $appController; // uma 20-04-2024
    private $tdsceil_startDate;

    // Db connection
    public function __construct($db, $clientid= '')
    {
        $this->conn = $db;
        $this->clientid = $clientid;
        $this->appController = new AppUtilController($this->conn);
        $this->tdsceil_startDate = new DateTime('2026-08-11');
    }
    // GET ALL

    function getFormatedDate($RawDate)
    {
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
    //Mohan 20-11-2025 for getting client config
    public function getClientConfig($ClientID)
    {
        return $this->appController->getClientConfig($ClientID);
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


    public function checkBookingNoAval($BookingDate, $BookingNumber, $LotId, $BookingDayWise)
    {
        $Qry = '';
        if ($LotId != '') {
            $Qry = "and RmBookingID !='$LotId'";
        }

        $CheckYear = explode('-', $BookingDate);

        if ($CheckYear[1] > 3) {
            $DateStart = $CheckYear[0] . '-04-01'; //2021-04-01
            $DateEnd = ($CheckYear[0] + 1) . '-03-31'; //2022-03-31
        } else {
            $DateStart = ($CheckYear[0] - 1) . '-04-01'; //2021-04-01
            $DateEnd = ($CheckYear[0]) . '-03-31'; //2022-03-31
        }

        if ($BookingDayWise == 1) {
            $sqlQuery = "SELECT * FROM `RmBookingDetatils`WHERE BookingNumber='$BookingNumber' and BookingDate = '$BookingDate' $Qry";
        } else {
            $sqlQuery = "SELECT * FROM `RmBookingDetatils`WHERE BookingNumber='$BookingNumber' and BookingDate BETWEEN '$DateStart' AND '$DateEnd' $Qry";
        }
        $stmt = $this->conn->prepare($sqlQuery);
        // $stmt->bindParam(':proformaID', $proformaID);
        $stmt->execute();
        if ($stmt->rowCount() == 0) {
            return true;
        }
        return false;
    }

    // public function getFormatedArrivalNumber($ArrivalStationID, $BillDate, $AutoArrivalReferenceNo)
    public function getFormatedArrivalNumber($ArrivalStationID, $BillDate, $ClientID, $AutoArrivalReferenceNo)
    {
        // $ClientID='CAMAROV3';
        $YearBasedPurchaseBillNoFormat = $this->appController->getClientConfig($ClientID)['YearBasedPurchaseBillNoFormat']; // uma 20-04-2024
        $CheckYear = explode('-', $BillDate); //uma  based on bill date confirmed by lokesh //02-12-2023

        if ($CheckYear[1] > 3) {
            $DateStart = $CheckYear[0] . '-04-01'; //2021-04-01
            $DateEnd = ($CheckYear[0] + 1) . '-03-31'; //2022-03-31

            $CurrentYear = date("y", strtotime($BillDate));
            $NxtYear = date("y", strtotime($BillDate)) + 1;
        } else {
            $DateStart = ($CheckYear[0] - 1) . '-04-01'; //2021-04-01
            $DateEnd = ($CheckYear[0]) . '-03-31'; //2022-03-31

            $CurrentYear = date("y", strtotime($BillDate)) - 1;
            $NxtYear = date("y", strtotime($BillDate));
        }
        //   echo  $CurrentYear; exit;
        //uma 19-04-2023 Billformat
        // if($YearBasedPurchaseBillNoFormat==1){
        $SqlBillNumberFormat = "SELECT * FROM `RmBillNumbersFormat` where PurchaseStationID='$ArrivalStationID'";
        $stmtBillNumberFormat = $this->conn->prepare($SqlBillNumberFormat);
        $stmtBillNumberFormat->execute();
        $dataRowBillNumberFormat = $stmtBillNumberFormat->fetch(PDO::FETCH_ASSOC);
        // print_r($dataRowBillNumberFormat);
        if ($dataRowBillNumberFormat['DateFormat'] != '') {
            if ($dataRowBillNumberFormat['DateFormat'] == 'YY') {
                $FinancialYear = $CurrentYear;
            } else {
                $FinancialYear = $CurrentYear . $dataRowBillNumberFormat['DateSeperator'] . $NxtYear;
            }

            $YearSeparator = $dataRowBillNumberFormat['NormalSeparator'] . $FinancialYear . $dataRowBillNumberFormat['NormalSeparator'];
        }
        // echo $FinancialYear; exit;

        //uma 19-04-2023 Billformat


        if ($AutoArrivalReferenceNo == null) {
            $sqlAutoArrivalNumberQuery = "  SELECT max(AutoArrivalReferenceNo) as ArrivalNumber FROM `RmArrivalDetatils` where  BillDate
      BETWEEN '$DateStart' and '$DateEnd' and ArrivalStationID='$ArrivalStationID'";

            $stmtAutoArrivalNumber = $this->conn->prepare($sqlAutoArrivalNumberQuery);
            // $stmt->bindParam(':proformaID', $proformaID);
            $stmtAutoArrivalNumber->execute();
            $dataRowAutoArrivalNumber = $stmtAutoArrivalNumber->fetch(PDO::FETCH_ASSOC);

            $AutoArrivalNumber = $dataRowAutoArrivalNumber['ArrivalNumber'] + 1;
        } else {
            $AutoArrivalNumber = $AutoArrivalReferenceNo;
        }

        $sqlQuery = "SELECT LotCode FROM `RmPurchaseStations` WHERE PurchaseStationID='$ArrivalStationID'";

        $stmt = $this->conn->prepare($sqlQuery);
        // $stmt->bindParam(':proformaID', $proformaID);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $LotCode = $dataRow['LotCode'];
        $ArrivalNumber = str_replace("O", "0", $AutoArrivalNumber);
        $ArrivalNumber = ltrim($ArrivalNumber, "0");
        $ArrivalNumber = preg_replace("/[^0-9]/", "", $ArrivalNumber);
        // $ArrivalNumber = sprintf('%04d', $ArrivalNumber);
        $ArrivalNumber = sprintf('%0' . $dataRowBillNumberFormat['LeadingZeros'] . 'd', $ArrivalNumber);



        if ($YearBasedPurchaseBillNoFormat == 1) {
            $ArrivalNumber = $LotCode . $YearSeparator . $ArrivalNumber; //uma 19-04-2024
        } else {
            $ArrivalNumber = $LotCode . $ArrivalNumber;
            $YearSeparator = '';
        }
        // $ArrivalNumber = $LotCode ;//updated by uma
        // $ArrivalNumber = preg_replace("/".$AutoArrivalNumber."$/", "", $ArrivalNumber);


        $ArrayArrivalNumber['AutoArrivalNumber'] = $AutoArrivalNumber;
        $ArrayArrivalNumber['ArrivalNumber'] = $ArrivalNumber;
        $ArrayArrivalNumber['LotCode'] = $LotCode;
        $ArrayArrivalNumber['YearSeparator'] = $YearSeparator;




        return $ArrayArrivalNumber;
    }

    //public function checkArrivalNoAval($ArrivalDate, $ArrivalNumber, $LotId)
    public function checkArrivalNoAval($BillDate, $ArrivalNumber, $LotId, $ArrivalStationID) //uma
    {
        $Qry = '';
        if ($LotId != '') {
            $Qry = "and RmArrivalID!='$LotId'";
        }

        // $CheckYear = explode('-', $ArrivalDate);
        $CheckYear = explode('-', $BillDate); //uma  based on bill date confirmed by lokesh //02-12-2023

        if ($CheckYear[1] > 3) {
            $DateStart = $CheckYear[0] . '-04-01'; //2021-04-01
            $DateEnd = ($CheckYear[0] + 1) . '-03-31'; //2022-03-31
        } else {
            $DateStart = ($CheckYear[0] - 1) . '-04-01'; //2021-04-01
            $DateEnd = ($CheckYear[0]) . '-03-31'; //2022-03-31
        }

        // $sqlQuery = "SELECT * FROM `RmArrivalDetatils`WHERE ArrivalNumber='$ArrivalNumber' and ArrivalDate BETWEEN '$DateStart' AND '$DateEnd' $Qry";
        $sqlQuery = "SELECT * FROM `RmArrivalDetatils`WHERE ArrivalNumber='$ArrivalNumber' and BillDate BETWEEN '$DateStart' AND '$DateEnd'
        and ArrivalStationID='$ArrivalStationID' $Qry"; //uma
        $stmt = $this->conn->prepare($sqlQuery);
        // $stmt->bindParam(':proformaID', $proformaID);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {

            return true;
        } else {

            return false;
        }
    }

    public function getRmPrice($Species, $PriceDate, $FINAL_COUNT, $CenterID)
    {

        // $sqlQuery = "SELECT * FROM `RmPurchaseRatesHistory` where SType='$Species' and  RmPriceDate <= '$PriceDate 23:59:59'
        //                 order by RmPriceDate DESC, ID DESC  LIMIT 1";

        //  echo  $sqlQuery = "SELECT * FROM `RmPurchaseRatesHistory` WHERE CenterID in (0,$CenterID) and RmPriceDate<='$PriceDate 23:59:59'
        // and SType='$Species' order by RmPriceDate  DESC, ID DESC limit 1";  exit;



        $sqlQuery = "SELECT *
FROM RmPurchaseRatesHistory
WHERE  RmPriceDate<='$PriceDate 23:59:59'
        and SType='$Species' and ((FIND_IN_SET('$CenterID', IF(centerID = '', '0', centerID)) > 0 ) or FIND_IN_SET('0', IF(centerID = '', '0', centerID)) > 0)
     ORDER BY
    CASE
        WHEN FIND_IN_SET('$CenterID', IF(centerID = '', '0', centerID)) > 0 THEN 1
        WHEN FIND_IN_SET('0', IF(centerID = '', '0', centerID)) > 0 THEN 2
    END,
RmPriceDate  DESC, ID DESC limit 1 ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        if ($stmt->rowCount() != 0) {
            $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
            $TARCK_ID = $dataRow['ID'];
            $RmPriceDate = $dataRow['RmPriceDate'];

            if ($FINAL_COUNT > 99) {
                $temp_c = 100;
                $sql_acr = "SELECT * FROM RmPurchaseRates WHERE DateID='$TARCK_ID' and CONVERT(RmCount, DECIMAL(10, 2)) = CONVERT('$temp_c', DECIMAL(10, 2))";
                $FINAL_COUNT = round($FINAL_COUNT);
            } else if ($FINAL_COUNT > 52.25 && $FINAL_COUNT < 60.01) {
                $FINAL_COUNT = ceil($FINAL_COUNT);
                $sql_acr = "SELECT * FROM RmPurchaseRates WHERE DateID='$TARCK_ID' and CONVERT(RmCount, DECIMAL(10, 2)) = CONVERT('$FINAL_COUNT', DECIMAL(10, 2))";
            } else if ($FINAL_COUNT > 62.25 && $FINAL_COUNT < 70.01) {
                $FINAL_COUNT = ceil($FINAL_COUNT);
                $sql_acr = "SELECT * FROM RmPurchaseRates WHERE DateID='$TARCK_ID' and CONVERT(RmCount, DECIMAL(10, 2)) = CONVERT('$FINAL_COUNT', DECIMAL(10, 2))";
            } else if ($FINAL_COUNT > 72.25 && $FINAL_COUNT < 80.01) {
                $FINAL_COUNT = ceil($FINAL_COUNT);
                $sql_acr = "SELECT * FROM RmPurchaseRates WHERE DateID='$TARCK_ID' and CONVERT(RmCount, DECIMAL(10, 2)) = CONVERT('$FINAL_COUNT', DECIMAL(10, 2))";
            } else if ($FINAL_COUNT > 82.25 && $FINAL_COUNT < 90.01) {
                $FINAL_COUNT = ceil($FINAL_COUNT);
                $sql_acr = "SELECT * FROM RmPurchaseRates WHERE DateID='$TARCK_ID' and CONVERT(RmCount, DECIMAL(10, 2)) = CONVERT('$FINAL_COUNT', DECIMAL(10, 2))";
            } else if ($FINAL_COUNT > 92.25 && $FINAL_COUNT < 100) {
                $FINAL_COUNT = ceil($FINAL_COUNT);
                $sql_acr = "SELECT * FROM RmPurchaseRates WHERE DateID='$TARCK_ID' and CONVERT(RmCount, DECIMAL(10, 2)) = CONVERT('$FINAL_COUNT', DECIMAL(10, 2))";
            } else {

                //echo $TEMP_DIFF = $FINAL_COUNT - round($FINAL_COUNT);
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

                    $sql_acr = "SELECT RmPrice, RmCount AS COUNT FROM RmPurchaseRates
                        WHERE DateID='$TARCK_ID' and RmCount = $FINAL_COUNT";
                    $stmt_acr = $this->conn->prepare($sql_acr);
                    $stmt_acr->execute();
                    $row = $stmt_acr->fetch(PDO::FETCH_ASSOC);
                    if ($row['COUNT'] === null) {
                        $sql_acr = "SELECT SUM(RmPrice/2) as RmPrice, SUM(RmCount/2) AS COUNT FROM RmPurchaseRates
                        WHERE DateID='$TARCK_ID' and RmCount >='$LC' and RmCount <='$UC' and RmCount != $FINAL_COUNT";
                    }
                } else {

                    $temp_c = round($FINAL_COUNT);
                    $sql_acr = "SELECT * FROM RmPurchaseRates WHERE DateID='$TARCK_ID' and RmCount ='$temp_c'";
                }
            }
            $stmt_acr = $this->conn->prepare($sql_acr);
            $stmt_acr->execute();
            $row = $stmt_acr->fetch(PDO::FETCH_ASSOC);
            $RmPrice = round($row['RmPrice'], 2);

            if ($FINAL_COUNT > 99) {
                $temp_c = 100;
                $Diff = $FINAL_COUNT - $temp_c;
                $RmPrice = $RmPrice - $Diff;
            }

            if ($RmPrice < 0) {
                $RmPrice = 1; // edited by uma 09-04-2024 Approved by surya

            }
            //    ECHO  $RmPrice;
            $data['RmPrice'] = $RmPrice;
            $data['BillHonCount'] = $FINAL_COUNT;
            return $data;
        }
    }

    public function getDefectPrice($Species, $DefectName, $MasterRate)
    {

        $RmPrice = 0;
        $sqlQuery = "SELECT DefectPerc FROM `RmDefectsList` WHERE DefectName='$DefectName'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        if ($stmt->rowCount() != 0) {
            $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
            $DefectPerc = $dataRow['DefectPerc'];
            $RmPrice = round(($DefectPerc * $MasterRate) / 100, 2);
        }
        return $RmPrice;
    }

    public function getRmDeductionPrice($Species, $PurchaseCategoryID, $CenterID, $BillDate, $Count, $Weight)
    {

        // $sqlQuery = "SELECT DeductionRateID FROM `RmDeductionRates`
        //  WHERE PurchaseCategoryID='$PurchaseCategoryID' and Species='$Species' and
        // CenterID in (0,$CenterID) and EffectedDate<='$BillDate' order by EffectedDate desc ,DeductionRateID desc LIMIT 1";

        $sqlQuery = " SELECT DeductionRateID
FROM RmDeductionRates
WHERE PurchaseCategoryID='$PurchaseCategoryID' and Species='$Species' and EffectedDate<='$BillDate'
 and ((FIND_IN_SET('$CenterID', centerID) > 0 ) or FIND_IN_SET('0', centerID) > 0)
     ORDER BY
    CASE
        WHEN FIND_IN_SET('$CenterID', centerID) > 0 THEN 1
        WHEN FIND_IN_SET('0', centerID) > 0 THEN 2
    END,
EffectedDate  DESC, DeductionRateID DESC limit 1";

        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $DeductionRateID = $dataRow['DeductionRateID'];

        $sqlDeductionPriceQuery = "SELECT Extra FROM `RmDeductionRatesBreakup` WHERE DeductionRateID='$DeductionRateID'
 and ($Count BETWEEN StartCount and EndCount) and ($Weight BETWEEN StartWeight and EndWeight)";
        $stmtDeductionPrice = $this->conn->prepare($sqlDeductionPriceQuery);
        $stmtDeductionPrice->execute();
        $dataRowDeductionPrice = $stmtDeductionPrice->fetch(PDO::FETCH_ASSOC);
        $Extra = $dataRowDeductionPrice['Extra'];

        return $Extra;
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

    public function getAgentName($ID)
    {

        $sqlQuery = "SELECT UserName,PanNumber,GstNo FROM `RmUsersList` WHERE ID='$ID'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        if ($stmt->rowCount() == '0') {
            $UserName = '';
        } else {
            $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
            $UserName = $dataRow['UserName'];
        }
        return $dataRow;
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


        // $sqlQuery = "SELECT StationName FROM `RmPurchaseStations` where  PurchaseStationID='$CenterID' ";
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


        $sqlQuery = "SELECT ID as UserID ,UserName,PanNumber FROM `RmUsersList`  WHERE ID='$UserType' ";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);

        return $dataRow;
    }

    function getRMSuppliers($RmSupplierID)
    {


        $sqlQuery = "SELECT  ID as SupplierID,FinancierName as SupplierName,SupplierType,
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


    function getArrivalPriceDetailsV3($aryRequest, $tdsAmount) //uma
    {

        $ArrivalID = $aryRequest['ArrivalID'];
        $ArrivalBrkID = $aryRequest['ArrivalBrkID'];
        $isEditBill = $aryRequest['isEditBill'];

        $sqlQuery = "SELECT  HarvestCommission,Extra FROM `RmArrivalDetatils` WHERE  RmArrivalID in ($ArrivalID)";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $HarvestExpKg = $dataRow['HarvestCommission'];
        $ExtraKg = $dataRow['Extra'];

        if (isset($ArrivalBrkID) && $ArrivalBrkID != '') {
            $sqlQuery = "SELECT AVG(Count) as ArrivalCount,SUM(Weight) as ArrivalWeight,AVG(Rate) as ArrivalRate ,sum(Weight*Rate) as BillAmount
            FROM `RmArrivalBreakUpDetails` WHERE ArrivalBrkUpID='$ArrivalBrkID'";
        } else {
            $sqlQuery = "SELECT AVG(Count) as ArrivalCount,SUM(Weight) as ArrivalWeight,AVG(Rate) as ArrivalRate ,sum(Weight*Rate) as BillAmount
            FROM `RmArrivalBreakUpDetails` WHERE ArrivalID in ($ArrivalID)";
        }

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

            $aryTdsRequest['ArrivalID'] = $ArrivalID;

            $aryTdsRequest['BillAmount'] = $BillAmount;
            $aryTdsRequest['HarvestBillAmount'] = $HarvestBillAmount;
            $aryTdsRequest['ExtraBillAmount'] = $ExtraBillAmount;
            $aryTdsRequest['isEditBill'] = $isEditBill;


            $AryTdsDetails = $this->getTdsValues($aryTdsRequest, $tdsAmount);
            $BillTDS = $AryTdsDetails['BillTDS'];
            $HarvestTDS = $AryTdsDetails['HarvestTDS'];
            $ExtraTDS = $AryTdsDetails['ExtraTDS'];
            $UnderTdsAmount = $AryTdsDetails['UnderTdsAmount'];
            $TdsDeductedValue = $AryTdsDetails['TdsDeductedValue'];


            /*uma* */
            if ($HarvestBillAmount > 0 && $HarvestTDS == 0) {
                //   $HarvestTDS = 1; by teja because of 50L condition
            }
            /*uma* */
            $TotalTDS = $BillTDS + $ExtraTDS + $HarvestTDS;

            $TotalBillAmountExTDS = $TotalBillAmount - ($BillTDS + $ExtraTDS + $HarvestTDS);
            $BillAmountExTDS = ($BillAmount + $ExtraBillAmount) - ($BillTDS + $ExtraTDS);
            $ExtraBillAmountExTDs = $ExtraBillAmount - $ExtraTDS;
            $HarvestBillAmountExTDS = $HarvestBillAmount - $HarvestTDS;

            $data['UnderTdsAmount'] = $UnderTdsAmount;
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
            $data['TdsDeductedValue'] = $TdsDeductedValue;
        }
        return $data;
    }

    function getArrivalPriceDetails($ArrivalID, $ArrivalBrkID) //uma
    {


        $sqlQuery = "SELECT  HarvestCommission,Extra FROM `RmArrivalDetatils` WHERE  RmArrivalID in ($ArrivalID)";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $HarvestExpKg = $dataRow['HarvestCommission'];
        $ExtraKg = $dataRow['Extra'];

        if (isset($ArrivalBrkID) && $ArrivalBrkID != '') {
            $sqlQuery = "SELECT AVG(Count) as ArrivalCount,SUM(Weight) as ArrivalWeight,AVG(Rate) as ArrivalRate ,sum(Weight*Rate) as BillAmount
            FROM `RmArrivalBreakUpDetails` WHERE ArrivalBrkUpID='$ArrivalBrkID'";
        } else {
            $sqlQuery = "SELECT AVG(Count) as ArrivalCount,SUM(Weight) as ArrivalWeight,AVG(Rate) as ArrivalRate ,sum(Weight*Rate) as BillAmount
            FROM `RmArrivalBreakUpDetails` WHERE ArrivalID in ($ArrivalID)";
        }

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

            $aryTdsRequest['ArrivalID'] = $ArrivalID;

            $aryTdsRequest['BillAmount'] = $BillAmount;
            $aryTdsRequest['HarvestBillAmount'] = $HarvestBillAmount;
            $aryTdsRequest['ExtraBillAmount'] = $ExtraBillAmount;


            $AryTdsDetails = $this->getTdsValues($aryTdsRequest);
            $BillTDS = $AryTdsDetails['BillTDS'];
            $HarvestTDS = $AryTdsDetails['HarvestTDS'];
            $ExtraTDS = $AryTdsDetails['ExtraTDS'];
            $UnderTdsAmount = $AryTdsDetails['UnderTdsAmount'];
            $TdsDeductedValue = $AryTdsDetails['TdsDeductedValue'];


            /*uma* */
            if ($HarvestBillAmount > 0 && $HarvestTDS == 0) {
                //   $HarvestTDS = 1; by teja because of 50L condition
            }
            /*uma* */
            $TotalTDS = $BillTDS + $ExtraTDS + $HarvestTDS;

            $TotalBillAmountExTDS = $TotalBillAmount - ($BillTDS + $ExtraTDS + $HarvestTDS);
            $BillAmountExTDS = ($BillAmount + $ExtraBillAmount) - ($BillTDS + $ExtraTDS);
            $ExtraBillAmountExTDs = $ExtraBillAmount - $ExtraTDS;
            $HarvestBillAmountExTDS = $HarvestBillAmount - $HarvestTDS;

            $data['UnderTdsAmount'] = $UnderTdsAmount;
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
            $data['TdsDeductedValue'] = $TdsDeductedValue;
        }
        return $data;
    }



    function getBillPriceDetails($ArrivalID, $IsCalcTds = true) //UMA
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

        // $sqlQuery = "SELECT  HarvestCommission,Extra FROM `RmArrivalDetatils` WHERE  RmArrivalID='$ArrivalID'";
        $sqlExtraQuery = "SELECT  Extra FROM `RmArrivalDetatils` WHERE  RmArrivalID in ($ArrivalID)";

        $stmtExtraAmount = $this->conn->prepare($sqlExtraQuery);
        $stmtExtraAmount->execute();
        $dataRowExtraAmount = $stmtExtraAmount->fetch(PDO::FETCH_ASSOC);
        // $HarvestExpKg = $dataRow['HarvestCommission'];
        $ExtraKg = $dataRowExtraAmount['Extra'];



        //     $sqlQuery = "SELECT AVG(Count) as ArrivalCount,SUM(Weight) as ArrivalWeight,AVG(Rate) as ArrivalRate ,sum(Weight*Rate) as BillAmount
        // FROM `RmBillBreakup` WHERE ArrivalID in ($ArrivalID)";
        $sqlQuery = "SELECT AVG(Count) as ArrivalCount,SUM(Weight) as ArrivalWeight,AVG(Rate) as ArrivalRate
    FROM `RmBillBreakup` WHERE ArrivalID in ($ArrivalID)";



        // $sqlQuery = "SELECT AVG(Count) as ArrivalCount,SUM(Weight) as ArrivalWeight,AVG(Rate) as ArrivalRate ,sum(BillAmount) as BillAmount,
        //  sum(HarvestAmount) as HarvestAmount,sum(BillTds) as BillTds,sum(HarvestTds) as HarvestTds
        // from RmBillBreakup as RBB  left join `RmBillDetails` as RBD on RBB.ArrivalID = RBD.ArrivalID
        //  WHERE RBB.ArrivalID in ($ArrivalID)";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            $data['ArrivalCount'] = 'NA';
            $data['ArrivalWeight'] = 'NA';
            $data['BookingRate'] = 'NA';
            $data['TotalAmount'] = 'NA';
        } else {
            $sqlBillAmountQuery = "SELECT sum(BillAmount) as BillAmount,
            sum(HarvestAmount) as HarvestAmount,sum(BillTds) as BillTds,sum(HarvestTds) as HarvestTds, sum(HarvestCommision) as HarvestCommision
           from RmBillDetails as RBB
            WHERE RBB.ArrivalID in ($ArrivalID)";
            $stmtBillAmount = $this->conn->prepare($sqlBillAmountQuery);
            $stmtBillAmount->execute();

            $dataRowBillAmount = $stmtBillAmount->fetch(PDO::FETCH_ASSOC);

            $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
            $data['ArrivalCount'] = round($dataRow['ArrivalCount'], 2);
            $data['ArrivalWeight'] = round($dataRow['ArrivalWeight'], 3);
            $data['ArrivalRate'] = round($dataRow['ArrivalRate'], 2);


            $BillAmount = $dataRowBillAmount['BillAmount']; //uma
            $ExtraBillAmount = $dataRow['ArrivalWeight'] * $ExtraKg;
            //  $HarvestBillAmount = $dataRow['ArrivalWeight'] * $HarvestExpKg;
            $HarvestBillAmount = $dataRowBillAmount['HarvestAmount']; //uma

            $TotalBillAmount = $BillAmount + $ExtraBillAmount + $HarvestBillAmount;


            $BillTDS = $dataRowBillAmount['BillTds']; //uma
            $HarvestTDS = $dataRowBillAmount['HarvestTds']; //uma
            $HarvestCommision = $dataRowBillAmount['HarvestCommision'];
            $aryTdsRequest['ArrivalID'] = $ArrivalID;

            $aryTdsRequest['Type'] = 'ExtraTDS';
            // $ExtraTDS = $this->getTdsValues($ExtraBillAmount, 0.1,$aryTdsRequest);

            if ($IsCalcTds) {
                $AryTdsDetails = $this->getTdsValues($aryTdsRequest);
            } else {
                $AryTdsDetails = array();
            }

            $ExtraTDS = $AryTdsDetails['ExtraTDS'];


            $TotalTDS = $BillTDS + $ExtraTDS + $HarvestTDS;

            $TotalBillAmountExTDS = $TotalBillAmount - ($BillTDS + $ExtraTDS + $HarvestTDS);
            $BillAmountExTDS = ($BillAmount + $ExtraBillAmount) - ($BillTDS + $HarvestTDS); //$ExtraTDS
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
            $data['HarvestCommision'] = $HarvestCommision;
        }
        return $data;
    }
    function getArrivalBillDetails($ArrivalID) //UMA 30-01-2024
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

        // $sqlQuery = "SELECT  HarvestCommission,Extra FROM `RmArrivalDetatils` WHERE  RmArrivalID='$ArrivalID'";
        $sqlExtraQuery = "SELECT  Extra FROM `RmArrivalDetatils` WHERE  RmArrivalID in ($ArrivalID)";

        $stmtExtraAmount = $this->conn->prepare($sqlExtraQuery);
        $stmtExtraAmount->execute();
        $dataRowExtraAmount = $stmtExtraAmount->fetch(PDO::FETCH_ASSOC);

        $ExtraKg = $dataRowExtraAmount['Extra'];








        //     $sqlQuery = "SELECT AVG(Count) as ArrivalCount,SUM(Weight) as ArrivalWeight,AVG(Rate) as ArrivalRate ,sum(Weight*Rate) as BillAmount
        // FROM `RmBillBreakup` WHERE ArrivalID in ($ArrivalID)";
        $sqlQuery = "SELECT AVG(Count) as ArrivalCount,SUM(Weight) as ArrivalWeight,AVG(Rate) as ArrivalRate
    FROM `RmArrivalBreakUpDetails` WHERE ArrivalID in ($ArrivalID)";



        // $sqlQuery = "SELECT AVG(Count) as ArrivalCount,SUM(Weight) as ArrivalWeight,AVG(Rate) as ArrivalRate ,sum(BillAmount) as BillAmount,
        //  sum(HarvestAmount) as HarvestAmount,sum(BillTds) as BillTds,sum(HarvestTds) as HarvestTds
        // from RmBillBreakup as RBB  left join `RmBillDetails` as RBD on RBB.ArrivalID = RBD.ArrivalID
        //  WHERE RBB.ArrivalID in ($ArrivalID)";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            $data['ArrivalCount'] = 'NA';
            $data['ArrivalWeight'] = 'NA';
            $data['BookingRate'] = 'NA';
            $data['TotalAmount'] = 'NA';
        } else {


            $sqlBillAmount = "SELECT  sum(TdsDeductedValue) as TdsDeductedValue FROM `RmBillDetails` WHERE  ArrivalID in ($ArrivalID)";
            $stmtBillAmount = $this->conn->prepare($sqlBillAmount);
            $stmtBillAmount->execute();
            $dataRowBillAmount = $stmtBillAmount->fetch(PDO::FETCH_ASSOC);

            $TdsDeductedValue = $dataRowBillAmount['TdsDeductedValue'];

            $sqlBillAmountQuery = "SELECT sum(ArrivalBillAmount) as BillAmount,
            sum(ArrivalHarvestAmount) as HarvestAmount,sum(ArrivalBillTds) as BillTds,sum(ArrivalHarvestTds) as HarvestTds
            from RmArrivalDetatils as RAD
            WHERE RAD.RmArrivalID in ($ArrivalID)";
            $stmtBillAmount = $this->conn->prepare($sqlBillAmountQuery);
            $stmtBillAmount->execute();

            $dataRowBillAmount = $stmtBillAmount->fetch(PDO::FETCH_ASSOC);

            $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
            $data['ArrivalCount'] = round($dataRow['ArrivalCount'], 2);
            $data['ArrivalWeight'] = round($dataRow['ArrivalWeight'], 3);
            $data['ArrivalRate'] = round($dataRow['ArrivalRate'], 2);


            $BillAmount = $dataRowBillAmount['BillAmount']; //uma
            $ExtraBillAmount = $dataRow['ArrivalWeight'] * $ExtraKg;
            $HarvestBillAmount = $dataRowBillAmount['HarvestAmount']; //uma

            $TotalBillAmount = $BillAmount + $ExtraBillAmount + $HarvestBillAmount;


            $BillTDS = $dataRowBillAmount['BillTds']; //uma
            $HarvestTDS = $dataRowBillAmount['HarvestTds']; //uma
            $aryTdsRequest['ArrivalID'] = $ArrivalID;

            $aryTdsRequest['Type'] = 'ExtraTDS';
            $AryTdsDetails = $this->getTdsValues($aryTdsRequest);

            $ExtraTDS = $AryTdsDetails['ExtraTDS'];

            $TotalTDS = $BillTDS + $ExtraTDS + $HarvestTDS;

            $TotalBillAmountExTDS = $TotalBillAmount - ($BillTDS + $ExtraTDS + $HarvestTDS);
            $BillAmountExTDS = ($BillAmount + $ExtraBillAmount) - ($BillTDS + $HarvestTDS); //$ExtraTDS
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
            $data['TdsDeductedValue'] = $TdsDeductedValue;
        }
        return $data;
    }




    function getTdsValuesO($ValueTotal, $TdsPerc, $c_ArrivalDate = "")
    {
        $ValueTotalExTds = ($ValueTotal * $TdsPerc) / 100;
        if($this->clientid == "DFLV3"){
            if(isset($c_ArrivalDate) && $c_ArrivalDate !="" ){
                if($c_ArrivalDate >= $this->tdsceil_startDate){
                    return $ValueTotalExTds;
                }
            }
        }
        $decimal = fmod($ValueTotalExTds, 1); //0.25
        $AryValueTds = explode('.', $ValueTotalExTds);
        if (count($AryValueTds) > 1) {
            $postFix = '0.' . $AryValueTds[1];
            if ($postFix >= 0.10) { //uma TDS VALUE AFTER DECIMAL POINT  -->>IF  GRATER THAN OR EQUAL TO  0.10 --> ROUND OF NEXT VALUE approved by baburao

                $ValueTotalExTds = ceil($ValueTotalExTds);
            } else {
                $ValueTotalExTds = $AryValueTds[0];
            }
        }
        // $ValueTotalExTds = ($ValueTotal * $TdsPerc) / 100; //commented by uma on 07-06-2024
        // $ValueTotalExTds = round($ValueTotalExTds, 2);
        return $ValueTotalExTds;
    }


    function getTdsRound($tdsValue, $c_ArrivalDate = "")
    {
        if($this->clientid == "DFLV3"){
            if(isset($c_ArrivalDate) && $c_ArrivalDate !="" ){

                if($c_ArrivalDate >= $this->tdsceil_startDate){
                    return $tdsValue;
                }
            }
        }
        $ValueTotalExTds = $tdsValue;
        $decimal = fmod($ValueTotalExTds, 1); //0.25
        $AryValueTds = explode('.', $ValueTotalExTds);
        if (count($AryValueTds) > 1) {
            $postFix = '0.' . $AryValueTds[1];
            if ($postFix >= 0.10) { //uma TDS VALUE AFTER DECIMAL POINT  -->>IF  GRATER THAN OR EQUAL TO  0.10 --> ROUND OF NEXT VALUE approved by baburao

                $ValueTotalExTds = ceil($ValueTotalExTds);
            } else {
                $ValueTotalExTds = $AryValueTds[0];
            }
        }
        return $ValueTotalExTds;
    }




    function getTdsValues($aryTdsRequest, $tdsAmount)
    {

        $ArrivalID = $aryTdsRequest['ArrivalID'];
        $TdsLimit = $tdsAmount;

        $sqlArrivalDetails = "SELECT AgentID,RmSupplierID,ArrivalDate,BillDate,RSD.PanNumber as supplierPAN ,
             RUL.PanNumber as agentPAN FROM `RmArrivalDetatils` as RAD
            left join RmSuppliers as RSD on RAD.RmSupplierID=RSD.ID
            left join RmUsersList as RUL on RUL.ID=RAD.AgentID
            WHERE RmArrivalID=$ArrivalID";
        $stmtArrivalDetails = $this->conn->prepare($sqlArrivalDetails);
        $stmtArrivalDetails->execute();

        $dataArrivalDetails = $stmtArrivalDetails->fetch(PDO::FETCH_ASSOC);




        $RmSupplierID = $dataArrivalDetails['RmSupplierID'];
        $AgentID = $dataArrivalDetails['AgentID'];
        $supplierPAN = trim($dataArrivalDetails['supplierPAN']);
        $agentPAN = trim($dataArrivalDetails['agentPAN']);
        $ArrivalDate = trim($dataArrivalDetails['ArrivalDate']);
        $BillDate = trim($dataArrivalDetails['BillDate']);

        $c_ArrivalDate = new DateTime($BillDate);

        if ($AgentID == 0) {
            $agentPAN = $supplierPAN;
            $agentIDs = 0;
        }

        $sqlRmSupplierDetails = "SELECT GROUP_CONCAT(ID) as supplierIDs from RmSuppliers WHERE PanNumber='$supplierPAN'";
        $stmtRmSupplierDetails = $this->conn->prepare($sqlRmSupplierDetails);
        $stmtRmSupplierDetails->execute();
        $dataSupplierDetails = $stmtRmSupplierDetails->fetch(PDO::FETCH_ASSOC);
        $supplierIDs = $dataSupplierDetails['supplierIDs'];


        $pastDataRequest['tdsLimit'] = $TdsLimit;
        $pastDataRequest['agentPAN'] = $agentPAN;
        $pastDataRequest['supplierPAN'] = $supplierPAN;
        $pastDataRequest['supplierIDs'] = $supplierIDs;
        $pastDataRequest['agentIDs'] = $agentIDs;
        $pastDataRequest['ArrivalID'] = $ArrivalID;
        $pastDataRequest['agentAsSupplierIDs'] = $agentAsSupplierIDs;
        $pastDataRequest['BillDate'] = $dataArrivalDetails['BillDate'];
        $pastDataRequest['checkPastBill'] = false;



        $BillAmount = $aryTdsRequest['BillAmount'];
        $HarvestBillAmount = $aryTdsRequest['HarvestBillAmount'];
        $ExtraBillAmount = $aryTdsRequest['ExtraBillAmount'];
        $pastDataRequest['BillAmount'] = $BillAmount;
        $pastDataRequest['isEditBill'] = $aryTdsRequest['isEditBill'];





        /**********************Total Supplier Total Value *******************/
        $pastDataRequest['checkPAN'] = $supplierPAN;
        $pastDataRequest['underTdsArrivalIDs'] = null;
        // $ArySupplierBillAmout = $this->getPanRmBillAmout($pastDataRequest); // Will Get Bill amount for that PAN
        // $ArySupplierHarvestAmount = $this->getPanHarvestAmount($pastDataRequest); // Will Get Harvest amount for that PAN

        //$ArySupplierBillAmout['PastBillAmount'];// ECHO "<BR>";
        // $TotalSupplierBillValue = $ArySupplierBillAmout['PastBillAmount'] + $ArySupplierHarvestAmount['PastHarvestAmount'];
        // $TotalSupplierTdsValue = $ArySupplierBillAmout['PastBillTds'] + $ArySupplierHarvestAmount['PastHarvestTds'];

        /**********************Total Supplied Value Before Current Bill *******************/
        //$pastDataRequest['checkPastBill'] = true;

        // $ArySupplierBeforeRmBillAmout = $this->getPanRmBillAmout($pastDataRequest);
        //$ArySupplierBeforeHarvestAmount = $this->getPanHarvestAmount($pastDataRequest);


        //  $TotalSupplierBeforeBillValue = $ArySupplierBeforeRmBillAmout['PastBillAmount'] + $ArySupplierBeforeHarvestAmount['PastHarvestAmount'];
        // $TotalSupplierBeforeTdsValue = $ArySupplierBeforeRmBillAmout['PastBillTds'] + $ArySupplierBeforeHarvestAmount['PastHarvestTds'];

        // if ($TotalSupplierBeforeBillValue > $TdsLimit) {
        //     $isUnderTdsBill = $this->checkArrivalUnderTdsLimit($pastDataRequest);
        //     $isTdsLimitExceeBill = $this->isTdsLimitExceedBill($pastDataRequest);
        // }
        // $aryIsTdsExcessBill['isTdsLimitExceeBill']=$isTdsLimitExceeBill;
        // $aryIsTdsExcessBill['CurrentBillValue']=$CurrentBillValue;

        $aryArrivalUnderTdsLimit = $this->checkArrivalUnderTdsLimit($pastDataRequest);
        $aryIsTdsExcessBill = $this->isTdsLimitExceedBill($pastDataRequest);
        $isUnderTdsBill = $aryArrivalUnderTdsLimit['isUnderTdsLimit'];
        $isTdsLimitExceeBill = $aryIsTdsExcessBill['isTdsLimitExceeBill'];



        $TdsDeductedValue = $CurrentBillValue = $BillAmount;
        $UnderTdsAmount = 1;
        if ($isUnderTdsBill) {
            $CurrentFinalBillTds = 0;
            $TdsDeductedValue = 0;
            $UnderTdsAmount = 0;
        } else if ($isTdsLimitExceeBill) {
            $TdsDeductedValue = $CurrentBillValue = $aryIsTdsExcessBill['CurrentBillValue'];
            $CurrentFinalBillTds = $this->getTdsValuesO($CurrentBillValue, 0.1, $c_ArrivalDate);
            if (($CurrentBillValue != 0) && ($CurrentBillValue < 100)) { //condition added on 18-06-2024 to deduct 1 ruppee if bill tds value is below 1 ruppee on 11-06-2024
                $CurrentFinalBillTds = 1;
            }
        } else {
            $TdsDeductedValue = $CurrentBillValue;
            $CurrentFinalBillTds = $this->getTdsValuesO($CurrentBillValue, 0.1, $c_ArrivalDate);
            if (($CurrentBillValue != 0) && ($CurrentBillValue < 100)) { //condition added on 18-06-2024 to deduct 1 ruppee if bill tds value is below 1 ruppee
                $CurrentFinalBillTds = 1;
            }
        }



        $pastDataRequest['checkPAN'] = $agentPAN;
        $AryAgentBillAmout = $this->getPanRmBillAmout($pastDataRequest);
        $AryAgentHarvestAmount = $this->getPanHarvestAmount($pastDataRequest);

        $TotalAgentBillValue = $AryAgentBillAmout['PastBillAmount'] + $AryAgentHarvestAmount['PastHarvestAmount'];
        $TotalAgentTdsValue = $AryAgentBillAmout['PastBillTds'] + $AryAgentHarvestAmount['PastHarvestTds'];

        if ($AgentID == 0 && $isUnderTdsBill) {
            $CurrentFinalHarvestTds = 0;
        } else if ($AgentID == 0 && $isTdsLimitExceeBill) {
            $CurrentFinalHarvestTds = 0; //$this->getTdsValuesO($HarvestBillAmount, 0.1);
        } else if ($AgentID != 0 && $TotalAgentBillValue > $TdsLimit) { //uma
            $CurrentFinalHarvestTds = $this->getTdsValuesO($HarvestBillAmount, 0.1,$c_ArrivalDate);

            // if ($CurrentFinalHarvestTds < 1 ) { //condition added on 11-06-2024 to deduct 1 ruppee if harvest tds value is below 1 ruppee
            //     $CurrentFinalHarvestTds = 1;
            // }

            if (($HarvestBillAmount != 0) && ($HarvestBillAmount < 100)) { //condition added on 17-06-2024 to deduct 1 ruppee if harvest tds value is below 1 ruppee
                $CurrentFinalHarvestTds = 1;
            }
        } else if ($AgentID != 0 && $isUnderTdsBill) { //uma
            $CurrentFinalHarvestTds = 0;
        } else if ($TotalAgentBillValue > $TdsLimit) {
            $CurrentFinalHarvestTds = $this->getTdsValuesO($HarvestBillAmount, 0.1,$c_ArrivalDate);

            if (($HarvestBillAmount != 0) && ($HarvestBillAmount < 100)) { //condition added on 11-06-2024 to deduct 1 ruppee if harvest tds value is below 1 ruppee
                $CurrentFinalHarvestTds = 1;
            }
        }



        // if($TotalAgentBillValue > $TdsLimit){
        //     $CurrentFinalHarvestTds=$this->getTdsValuesO($HarvestBillAmount, 0.1);
        // }else{
        //     $CurrentFinalHarvestTds=0;
        // }

        if($this->clientid == "DFLV3" && $c_ArrivalDate >= $this->tdsceil_startDate){
            $BillTDS = $CurrentFinalBillTds;
        }else{
            $BillTDS = round($CurrentFinalBillTds, 2);
        }

        $HarvestTDS = $CurrentFinalHarvestTds;
        $ExtraTDS = $this->getTdsValuesO($ExtraBillAmount, 0.1,$c_ArrivalDate);


        /*


                 $TotalTdsApplicableValue = $TotalSupplierBillValue - $TdsLimit;
                 $TdsAmountCheck = $this->getTdsValuesO($TotalTdsApplicableValue+$BillAmount, 0.1);
                    if ($TdsAmountCheck >= $TotalSupplierTdsValue) {
                     $CurrentFinalBillTds = $TdsAmountCheck - $TotalSupplierTdsValue;
                    }


                 /**********************************************************************/
        /*
                $CurrentBillValue = $BillAmount;
                $TotalSupplierBeforeBillValue;

                if($CurrentBillValue > $TdsLimit){
                    $CurrentBillValue=$CurrentBillValue-$TdsLimit;
                    $CurrentFinalBillTds=$this->getTdsValuesO($CurrentBillValue, 0.1);
                }else if($TotalSupplierBeforeBillValue < $TdsLimit &&  ($TotalSupplierBeforeBillValue+$CurrentBillValue) > $TdsLimit){
                    $TotalSupplierBeforeBillValue;
                    $CurrentBillValue=($TotalSupplierBeforeBillValue+$CurrentBillValue)-$TdsLimit;
                    $CurrentFinalBillTds=$this->getTdsValuesO($CurrentBillValue, 0.1);
                }else if($TotalSupplierBeforeBillValue < $TdsLimit &&  ($TotalSupplierBeforeBillValue+$CurrentBillValue) < $TdsLimit){
                    if ($TdsAmountCheck >= $TotalSupplierTdsValue) {
                        $CurrentFinalBillTds = $TdsAmountCheck - $TotalSupplierTdsValue;
                       }else{
                    $CurrentBillValue=0;
                    $CurrentFinalBillTds=0;
                       }
                }else if($TotalSupplierBeforeBillValue < $TdsLimit &&  $TotalSupplierBillValue > $TdsLimit){
                    $CurrentBillValue=($TotalSupplierBeforeBillValue+$CurrentBillValue)-$TdsLimit;
                    $CurrentFinalBillTds=$this->getTdsValuesO($CurrentBillValue, 0.1);
                }else if($TotalSupplierBillValue > $TdsLimit){
                     $CurrentBillValue=$CurrentBillValue+0;
                    $CurrentFinalBillTds=$this->getTdsValuesO($CurrentBillValue, 0.1);
                }

        // $CurrentFinalBillTds;

                  //  $TdsAmountCheck = $this->getTdsValuesO($TotalTdsApplicableValue, 0.1);

                    //  $TdsAmountCheck;
                    if ($TdsAmountCheck >= $TotalSupplierTdsValue) {
                     $CurrentFinalBillTds = $TdsAmountCheck - $TotalSupplierTdsValue;
                    }



                /**********************Total Agent Total Value *******************/
        /*     $pastDataRequest['checkPAN'] = $agentPAN;
             $AryAgentBillAmout = $this->getPanRmBillAmout($pastDataRequest);
             $AryAgentHarvestAmount = $this->getPanHarvestAmount($pastDataRequest);


             $TotalAgentBillValue=$AryAgentBillAmout['PastBillAmount']+$AryAgentHarvestAmount['PastHarvestAmount'];
             $TotalAgentTdsValue=$AryAgentBillAmout['PastBillTds']+$AryAgentHarvestAmount['PastHarvestTds'];

             //echo "HI";

             if($TotalAgentBillValue > $TdsLimit){
                 //$CurrentHarvestValue=$HarvestTDS-$TdsLimit;
                 $CurrentFinalHarvestTds=$this->getTdsValuesO($HarvestBillAmount, 0.1);
             }

             $BillTDS = round($CurrentFinalBillTds,2);
             $HarvestTDS = $CurrentFinalHarvestTds;
             $ExtraTDS = $this->getTdsValuesO($ExtraBillAmount, 0.1);
              $tempBillAmt=round(($TotalSupplierBillValue),2);

             if($tempBillAmt < $TdsLimit) {
                 $UnderTdsAmount=1;
             }else{
                 $UnderTdsAmount=0;
             }

     */

        /*Beside Code*/
        /*Beside Code*/

        $AryTdsData['BillTDS'] = $BillTDS;
        $AryTdsData['HarvestTDS'] = $HarvestTDS;
        $AryTdsData['ExtraTDS'] = $ExtraTDS;
        $AryTdsData['UnderTdsAmount'] = $UnderTdsAmount;
        $AryTdsData['TdsDeductedValue'] = $TdsDeductedValue;

        return $AryTdsData;
    }


    function checkArrivalUnderTdsLimit($aryTdsRequest)
    {

        $isUnderTdsLimit = false;
        $dateFilter = $this->getBillDateLimit($aryTdsRequest);
        $ArrivalID = $aryTdsRequest['ArrivalID'];
        $supplierIDs = $aryTdsRequest['supplierIDs'];
        $tdsLimit = $aryTdsRequest['tdsLimit'] + 1;
        $BillAmount = $aryTdsRequest['BillAmount'];
        $BillDate = $aryTdsRequest['BillDate'];
        $isEditBill = $aryTdsRequest['isEditBill'];
        $checkPAN = $aryTdsRequest['checkPAN'];


        if ($isEditBill) {
            $sqlArrivalDetails = "update RmArrivalDetatils set ArrivalBillAmount= '$BillAmount' where RmArrivalID='$ArrivalID'";
            $stmtArrivalDetails = $this->conn->prepare($sqlArrivalDetails);
            $stmtArrivalDetails->execute();

            $sqlPanWiseSuppliedAmount = "update RmPanWiseSuppliedAmount set PanwiseAmount= '$BillAmount'
        where PanwiseArrivalID='$ArrivalID' and PanAmountType=0";
            $stmtPanWiseSuppliedAmount = $this->conn->prepare($sqlPanWiseSuppliedAmount);
            $stmtPanWiseSuppliedAmount->execute();
        }

        /*$sqlArrivalDetails = "SELECT *
        FROM (
            SELECT *,
                   SUM(PanwiseAmount) OVER (ORDER BY PanwiseBillDate ASC, PanwiseAutoArrivalReferenceNO ASC) AS running_total
            FROM `RmPanWiseSuppliedAmount`
            WHERE PanwiseBillDate  $dateFilter
              AND BillPanNumber ='$checkPAN'
        ) AS subquery

        WHERE running_total < $tdsLimit
        ORDER BY PanwiseBillDate ASC, PanwiseAutoArrivalReferenceNO ASC;";*/


        /* // commented on 01-aug-2025
        $sqlArrivalDetails = "SELECT * FROM ( SELECT *, SUM(PanwiseAmount) OVER (ORDER BY PanwiseBillDate ASC,
        PanwiseAutoArrivalReferenceNO ASC) AS running_total FROM `RmPanWiseSuppliedAmount`  AS RPSA
    LEFT JOIN RmBillDetails AS RBD
        ON RBD.ArrivalID = RPSA.PanwiseArrivalID
        LEFT JOIN RmArrivalDetatils AS RAD
        ON RBD.ArrivalID = RAD.RmArrivalID
        WHERE PanwiseBillDate $dateFilter AND BillPanNumber ='$checkPAN' AND RBD.RmBillID IS NOT NULL)
        AS subquery WHERE running_total <  $tdsLimit
        ORDER BY PanwiseBillDate ASC, PanwiseAutoArrivalReferenceNO ASC, RBD.RmBillID ASC ";*/

        $c_billDate = new DateTime($BillDate);
        $c_startDate = new DateTime('2025-08-01');

        if ($c_billDate >= $c_startDate) {

            $sqlArrivalDetails = "SELECT PanwiseArrivalID FROM ( SELECT RPSA.PanwiseArrivalID,PanwiseBillDate,PanwiseAutoArrivalReferenceNO, SUM(PanwiseAmount) OVER (ORDER BY PanwiseBillDate ASC,
                PanwiseAutoArrivalReferenceNO ASC) AS running_total FROM `RmPanWiseSuppliedAmount`  AS RPSA
            LEFT JOIN RmBillDetails AS RBD
                ON RBD.ArrivalID = RPSA.PanwiseArrivalID
                LEFT JOIN RmArrivalDetatils AS RAD
                ON RBD.ArrivalID = RAD.RmArrivalID
                WHERE PanwiseBillDate $dateFilter AND BillPanNumber ='$checkPAN' AND RBD.RmBillID IS NOT NULL and RPSA.PanAmountType=0)
                AS subquery WHERE running_total <  $tdsLimit
                ORDER BY PanwiseBillDate ASC, PanwiseAutoArrivalReferenceNO ASC";
        } else {
            $sqlArrivalDetails = "SELECT PanwiseArrivalID FROM ( SELECT RPSA.PanwiseArrivalID,PanwiseBillDate,PanwiseAutoArrivalReferenceNO, SUM(PanwiseAmount) OVER (ORDER BY PanwiseBillDate ASC,
                PanwiseAutoArrivalReferenceNO ASC) AS running_total FROM `RmPanWiseSuppliedAmount`  AS RPSA
            LEFT JOIN RmBillDetails AS RBD
                ON RBD.ArrivalID = RPSA.PanwiseArrivalID
                LEFT JOIN RmArrivalDetatils AS RAD
                ON RBD.ArrivalID = RAD.RmArrivalID
                WHERE PanwiseBillDate $dateFilter AND BillPanNumber ='$checkPAN' AND RBD.RmBillID IS NOT NULL)
                AS subquery WHERE running_total <  $tdsLimit
                ORDER BY PanwiseBillDate ASC, PanwiseAutoArrivalReferenceNO ASC";
        }



        //error_log("✅ 124Under TDS Limit Arrival IDs: " . print_r($sqlArrivalDetails, true));
        $stmtArrivalDetails = $this->conn->prepare($sqlArrivalDetails);
        $stmtArrivalDetails->execute();
        $aryArrivalID = array();

        while ($dataArrivalDetails = $stmtArrivalDetails->fetch(PDO::FETCH_ASSOC)) {

            $aryArrivalID[] = $dataArrivalDetails['PanwiseArrivalID'];
            if ($dataArrivalDetails['PanwiseArrivalID'] == $ArrivalID) {
                $isUnderTdsLimit = true;
            }
        }

        $aryArrivalUnderTdsLimit['isUnderTdsLimit'] = $isUnderTdsLimit;
        $aryArrivalUnderTdsLimit['aryArrivalID'] = $aryArrivalID;
        // var_dump( $aryArrivalUnderTdsLimit['aryArrivalID']);
        return $aryArrivalUnderTdsLimit;
    }

    function isTdsLimitExceedBill($aryTdsRequest)
    {

        $isTdsLimitExceeBill = false;
        $dateFilter = $this->getBillDateLimit($aryTdsRequest);
        $ArrivalID = $aryTdsRequest['ArrivalID'];
        $supplierIDs = $aryTdsRequest['supplierIDs'];
        $tdsLimit = $aryTdsRequest['tdsLimit'] + 1;
        $BillAmount = $aryTdsRequest['BillAmount'];
        $BillDate = $aryTdsRequest['BillDate'];
        $isEditBill = $aryTdsRequest['isEditBill'];
        $recordsLimit = $aryTdsRequest['limitRecords'];
        $checkPAN = $aryTdsRequest['checkPAN'];
        if ($recordsLimit) {
            $recordsLimit = "LIMIT 100 OFFSET 1";
        } else {

            $recordsLimit = " LIMIT 1 ";
        }


        if ($isEditBill) {
            $sqlArrivalDetails = "update RmArrivalDetatils set ArrivalBillAmount= '$BillAmount' where RmArrivalID='$ArrivalID'";
            $stmtArrivalDetails = $this->conn->prepare($sqlArrivalDetails);
            $stmtArrivalDetails->execute();

            $sqlPanWiseSuppliedAmount = "update RmPanWiseSuppliedAmount set PanwiseAmount= '$BillAmount'
        where PanwiseArrivalID='$ArrivalID' and PanAmountType=0";
            $stmtPanWiseSuppliedAmount = $this->conn->prepare($sqlPanWiseSuppliedAmount);
            $stmtPanWiseSuppliedAmount->execute();
        }


        /*
        // commented on 01-aug-25
        $sqlArrivalDetails = "SELECT * FROM ( SELECT *, SUM(PanwiseAmount) OVER (ORDER BY PanwiseBillDate ASC,
        PanwiseAutoArrivalReferenceNO ASC) AS running_total FROM `RmPanWiseSuppliedAmount`  AS RPSA
    LEFT JOIN RmBillDetails AS RBD
        ON RBD.ArrivalID = RPSA.PanwiseArrivalID
        WHERE PanwiseBillDate $dateFilter AND BillPanNumber ='$checkPAN' AND RBD.RmBillID IS NOT NULL)
        AS subquery WHERE running_total >  $tdsLimit
        ORDER BY PanwiseBillDate ASC, PanwiseAutoArrivalReferenceNO ASC $recordsLimit ";
        */

        $c_billDate = new DateTime($BillDate);
        $c_startDate = new DateTime('2025-08-01');

        if ($c_billDate >= $c_startDate) {
            $sqlArrivalDetails = "SELECT PanwiseArrivalID,running_total FROM ( SELECT PanwiseArrivalID,PanwiseBillDate ,PanwiseAutoArrivalReferenceNO, SUM(PanwiseAmount) OVER (ORDER BY PanwiseBillDate ASC,
                PanwiseAutoArrivalReferenceNO ASC) AS running_total FROM `RmPanWiseSuppliedAmount`  AS RPSA
            LEFT JOIN RmBillDetails AS RBD
                ON RBD.ArrivalID = RPSA.PanwiseArrivalID
                WHERE PanwiseBillDate $dateFilter AND BillPanNumber ='$checkPAN' AND RBD.RmBillID IS NOT NULL and RPSA.PanAmountType=0)
                AS subquery WHERE running_total >  $tdsLimit
                ORDER BY PanwiseBillDate ASC, PanwiseAutoArrivalReferenceNO ASC $recordsLimit ";
        } else {

            $sqlArrivalDetails = "SELECT PanwiseArrivalID,running_total FROM ( SELECT PanwiseArrivalID,PanwiseBillDate ,PanwiseAutoArrivalReferenceNO, SUM(PanwiseAmount) OVER (ORDER BY PanwiseBillDate ASC,
                PanwiseAutoArrivalReferenceNO ASC) AS running_total FROM `RmPanWiseSuppliedAmount`  AS RPSA
            LEFT JOIN RmBillDetails AS RBD
                ON RBD.ArrivalID = RPSA.PanwiseArrivalID
                WHERE PanwiseBillDate $dateFilter AND BillPanNumber ='$checkPAN' AND RBD.RmBillID IS NOT NULL )
                AS subquery WHERE running_total >  $tdsLimit
                ORDER BY PanwiseBillDate ASC, PanwiseAutoArrivalReferenceNO ASC $recordsLimit ";
        }

        $stmtArrivalDetails = $this->conn->prepare($sqlArrivalDetails);
        $stmtArrivalDetails->execute();

        error_log("⚠️ Panwise Query: " . print_r($sqlArrivalDetails, true));
        $aryArrivalID = array();
        $firstIndex = true;
        $Sno = 0;
        while ($dataArrivalDetails = $stmtArrivalDetails->fetch(PDO::FETCH_ASSOC)) {
            $Sno++;
            //  echo $Sno;
            $aryArrivalID[] = $dataArrivalDetails['PanwiseArrivalID'];

            if ($dataArrivalDetails['PanwiseArrivalID'] == $ArrivalID) {

                $isTdsLimitExceeBill = true;
                $CurrentBillValue = $dataArrivalDetails['running_total'] - ($tdsLimit - 1);
                $TdsArrivalID = $dataArrivalDetails['PanwiseArrivalID'];
                // echo "hii";
            } else {
                //$isTdsLimitExceeBill = false;
                // $CurrentBillValue=$dataArrivalDetails['running_total']-($tdsLimit-1);
                // $TdsArrivalID=$dataArrivalDetails['RmArrivalID'];
            }
        }
        $aryIsTdsExcessBill['isTdsLimitExceeBill'] = $isTdsLimitExceeBill;
        $aryIsTdsExcessBill['CurrentBillValue'] = $CurrentBillValue;
        $aryIsTdsExcessBill['aryArrivalID'] = $aryArrivalID;
        return $aryIsTdsExcessBill;
    }

    function getBillDateLimit($aryTdsRequest)
    {


        $BillDate = $aryTdsRequest['BillDate'];
        $CheckYear = explode('-', $BillDate);

        if ($CheckYear[1] > 3) {
            $DateStart = $CheckYear[0] . '-04-01'; //2021-04-01
            $DateEnd = ($CheckYear[0] + 1) . '-03-31'; //2022-03-31
        } else {
            $DateStart = ($CheckYear[0] - 1) . '-04-01'; //2021-04-01
            $DateEnd = ($CheckYear[0]) . '-03-31'; //2022-03-31
        }
        return $Qry = "between '$DateStart' and '$DateEnd'";
    }
    // Update Because 50L Condition
    function getTdsValuesDep($queryValue, $TdsPerc, $aryTdsRequest)
    {

        $ArrivalID = $aryTdsRequest['ArrivalID'];

        $sqlArrivalDetails = "SELECT AgentID,RmSupplierID,BillDate,RSD.PanNumber as supplierPAN ,RUL.PanNumber as agentPAN FROM `RmArrivalDetatils` as RAD
            left join RmSuppliers as RSD on RAD.RmSupplierID=RSD.ID
            left join RmUsersList as RUL on RUL.ID=RAD.AgentID
            WHERE RmArrivalID=$ArrivalID";
        $stmtArrivalDetails = $this->conn->prepare($sqlArrivalDetails);
        $stmtArrivalDetails->execute();

        $dataArrivalDetails = $stmtArrivalDetails->fetch(PDO::FETCH_ASSOC);


        $AgentID = $dataArrivalDetails['AgentID'];
        $supplierPAN = trim($dataArrivalDetails['supplierPAN']);
        $agentPAN = trim($dataArrivalDetails['agentPAN']);

        if ($AgentID == 0) {
            $agentPAN = $supplierPAN;
            $agentIDs = 0;
        } else if ($agentPAN != $supplierPAN) {
            $sqlRmAgentDetails = "SELECT GROUP_CONCAT(ID) as agentIDs from RmUsersList WHERE PanNumber ='$agentPAN'";
            $stmtAgentDetails = $this->conn->prepare($sqlRmAgentDetails);
            $stmtAgentDetails->execute();
            $dataSupplierDetails = $stmtAgentDetails->fetch(PDO::FETCH_ASSOC);

            $agentIDs = $dataSupplierDetails['agentIDs'];

            $sqlRmSupplierDetails = "SELECT GROUP_CONCAT(ID) as supplierIDs from RmSuppliers WHERE PanNumber='$agentPAN'";
            $stmtRmSupplierDetails = $this->conn->prepare($sqlRmSupplierDetails);
            $stmtRmSupplierDetails->execute();
            $dataSupplierDetails = $stmtRmSupplierDetails->fetch(PDO::FETCH_ASSOC);
            $agentAsSupplierIDs = $dataSupplierDetails['supplierIDs'];
        }

        $sqlRmSupplierDetails = "SELECT GROUP_CONCAT(ID) as supplierIDs from RmSuppliers WHERE PanNumber='$supplierPAN'";
        $stmtRmSupplierDetails = $this->conn->prepare($sqlRmSupplierDetails);
        $stmtRmSupplierDetails->execute();
        $dataSupplierDetails = $stmtRmSupplierDetails->fetch(PDO::FETCH_ASSOC);
        $supplierIDs = $dataSupplierDetails['supplierIDs'];

        $pastDataRequest['supplierIDs'] = $supplierIDs;
        $pastDataRequest['agentIDs'] = $agentIDs;
        $pastDataRequest['ArrivalID'] = $ArrivalID;
        $pastDataRequest['agentAsSupplierIDs'] = $agentAsSupplierIDs;
        $pastDataRequest['BillDate'] = $dataArrivalDetails['BillDate'];
        $BillAmount = $aryTdsRequest['BillAmount'];
        $HarvestBillAmount = $aryTdsRequest['HarvestBillAmount'];
        $ExtraBillAmount = $aryTdsRequest['ExtraBillAmount'];

        if ($AgentID == 0) {
            $ArySupplierdata = $this->getSupplierPastAmount($pastDataRequest);
        } else if ($agentPAN != $supplierPAN) {
            $ArySupplierdata = $this->getSupplierPastAmount($pastDataRequest); // SupplierValue inc Harvest Commision  used for Bill Tds Calc
            $PastHarvestAmount = $this->getAgentPastAmount($pastDataRequest);
        }



        $PastBillAmount = $ArySupplierdata['PastSuppliedValue'];
        $PastSuppliedTds = $ArySupplierdata['PastSuppliedTds'];

        $TdsLimit = 5000000;
        $TdsAmount = 0;

        if (trim($agentPAN) == trim($supplierPAN)) {
            $TotalSuppliedValue = $PastBillAmount + $BillAmount + $HarvestBillAmount;
            $TotalTdsApplicableValue = $TotalSuppliedValue - $TdsLimit;

            $TdsAmountCheck = $this->getTdsValuesO($TotalTdsApplicableValue, 0.1);

            if ($PastBillAmount > $TdsLimit) {
                $TdsAmount = $this->getTdsValuesO($queryValue, 0.1);
            } else if ($TotalTdsApplicableValue > 0) {
                if ($queryValue == $BillAmount) {
                    $TdsAmount = $this->getTdsValuesO($TotalTdsApplicableValue, 0.1);
                    if ($PastSuppliedTds > $TdsAmountCheck) {
                        $TdsAmount = 0;
                    } else if ($TdsAmountCheck >= $PastSuppliedTds) {
                        $TdsAmount = $TdsAmountCheck - $PastSuppliedTds;
                    }
                } else {
                    $TdsAmount = 0;
                }
            } else {
                $TdsAmount = 0;
            }
        } else if (trim($agentPAN) != trim($supplierPAN)) {
            $TotalSuppliedValue = $PastBillAmount + $BillAmount + $HarvestBillAmount;
            $TotalTdsApplicableValue = $TotalSuppliedValue - $TdsLimit;

            $TdsAmountCheck = $this->getTdsValuesO($TotalTdsApplicableValue, 0.1);

            if ($PastBillAmount > $TdsLimit) {
                $TdsAmount = $this->getTdsValuesO($queryValue, 0.1);
            } else if ($TotalTdsApplicableValue > 0) {
                if ($queryValue == $BillAmount) {
                    $TdsAmount = $this->getTdsValuesO($TotalTdsApplicableValue, 0.1);
                    if ($PastSuppliedTds > $TdsAmountCheck) {
                        $TdsAmount = 0;
                    } else if ($TdsAmountCheck >= $PastSuppliedTds) {
                        $TdsAmount = $TdsAmountCheck - $PastSuppliedTds;
                    }
                } else {
                    $TdsAmount = 0;
                }
            } else {
                $TdsAmount = 0;
            }
        }





        return $TdsAmount;
    }



    function getPanRmBillAmout($pastDataRequest)
    {
        $supplierIDs = $pastDataRequest['supplierIDs'];
        $checkPAN = $pastDataRequest['checkPAN'];
        $ArrivalID = $pastDataRequest['ArrivalID'];
        $BillDate = $pastDataRequest['BillDate'];
        $checkPastBill = $pastDataRequest['checkPastBill'];
        $underTdsArrivalIDs = $pastDataRequest['underTdsArrivalIDs'];

        $CheckYear = explode('-', $BillDate);

        if ($CheckYear[1] > 3) {
            $DateStart = $CheckYear[0] . '-04-01'; //2021-04-01
            $DateEnd = ($CheckYear[0] + 1) . '-03-31'; //2022-03-31
        } else {
            $DateStart = ($CheckYear[0] - 1) . '-04-01'; //2021-04-01
            $DateEnd = ($CheckYear[0]) . '-03-31'; //2022-03-31
        }
        $DateEnd = $BillDate;
        if ($underTdsArrivalIDs != null) {
            $Qry = " and RAD.RmArrivalID in ($underTdsArrivalIDs) and RAD.RmArrivalID!=$ArrivalID and RAD.LotStatus=1";
        } else {
            $Qry = " and RAD.BillDate
            between '$DateStart' and '$DateEnd' and RAD.RmArrivalID!=$ArrivalID and RAD.LotStatus=1";
        }

        $Qry = " and RAD.BillDate between '$DateStart' and '$DateEnd' and RAD.RmArrivalID!=$ArrivalID  and RAD.LotStatus=1";
        // $Qry = " and RAD.BillDate between '$DateStart' and '$DateEnd' and RAD.LotStatus=1";

        // if ($checkPastBill) {
        //     $sqlArrivalQuery = "SELECT AutoArrivalReferenceNo as RefNumber  from RmArrivalDetatils WHERE RmArrivalID =$ArrivalID";

        //     $stmtArrival = $this->conn->prepare($sqlArrivalQuery);
        //     $stmtArrival->execute();
        //     $dataArrival = $stmtArrival->fetch(PDO::FETCH_ASSOC);
        //     $RefNumber = $dataArrival['RefNumber'];
        //     $Qry .= " and RAD.AutoArrivalReferenceNo < $RefNumber";
        // }

        //// $DateEnd = $BillDate;



        $sqlBillAmountQuery = "SELECT sum(BillAmount) as PastBillAmount,
                                sum(ActBillTds) as PastBillTds from RmBillDetails as RBD
                                left Join RmArrivalDetatils as RAD on RAD.RmArrivalID=RBD.ArrivalID
                                left join RmSuppliers as RS on RS.ID=RAD.RmSupplierID
                                WHERE RS.PanNumber='$checkPAN' $Qry"; //RAD.RmSupplierID in ($supplierIDs)

        $stmtBillAmount = $this->conn->prepare($sqlBillAmountQuery);
        $stmtBillAmount->execute();

        $dataRowBillAmount = $stmtBillAmount->fetch(PDO::FETCH_ASSOC);
        $PastBillAmount = $dataRowBillAmount['PastBillAmount'];
        $PastBillTds = $dataRowBillAmount['PastBillTds'];

        // $PastSuppliedValue = $PastBillAmount ;
        // $PastSuppliedTds = $PastBillTds ;

        $Arydata['PastBillAmount'] = $PastBillAmount;
        $Arydata['PastBillTds'] = $PastBillTds;
        return $Arydata;
    }

    function getPanHarvestAmount($pastDataRequest)
    {

        $supplierIDs = $pastDataRequest['supplierIDs'];
        $agentIDs = $pastDataRequest['agentIDs'];
        $checkPAN = $pastDataRequest['checkPAN'];
        $ArrivalID = $pastDataRequest['ArrivalID'];
        $BillDate = $pastDataRequest['BillDate'];
        $checkPastBill = $pastDataRequest['checkPastBill'];

        $CheckYear = explode('-', $BillDate); //uma  based on bill date confirmed by lokesh //02-12-2023

        if ($CheckYear[1] > 3) {
            $DateStart = $CheckYear[0] . '-04-01'; //2021-04-01
            $DateEnd = ($CheckYear[0] + 1) . '-03-31'; //2022-03-31
        } else {
            $DateStart = ($CheckYear[0] - 1) . '-04-01'; //2021-04-01
            $DateEnd = ($CheckYear[0]) . '-03-31'; //2022-03-31
        }

        $DateEnd = $BillDate;


        $Qry = " and RAD.BillDate  between '$DateStart' and '$DateEnd' and RAD.RmArrivalID!=$ArrivalID  and RAD.LotStatus=1";

        // if ($checkPastBill) {
        //     $sqlArrivalQuery = "SELECT AutoArrivalReferenceNo as RefNumber from RmArrivalDetatils WHERE RmArrivalID =$ArrivalID";

        //     $stmtArrival = $this->conn->prepare($sqlArrivalQuery);
        //     $stmtArrival->execute();
        //     $dataArrival = $stmtArrival->fetch(PDO::FETCH_ASSOC);
        //     $RefNumber = $dataArrival['RefNumber'];
        //     $Qry .= "and RAD.AutoArrivalReferenceNo < $RefNumber";
        // }

        $sqlHarvestAmountQuery = "SELECT
        sum(HarvestAmount) as PastHarvestAmount ,
        sum(ActHarvestTds) as PastHarvestTds from RmBillDetails as RBD
        left Join RmArrivalDetatils as RAD on RAD.RmArrivalID=RBD.ArrivalID
        left join RmUsersList as RUL on RUL.ID=RAD.AgentID
        WHERE RUL.PanNumber ='$checkPAN'  $Qry"; //RAD.AgentID in ($agentIDs)


        $stmtHarvestAmount = $this->conn->prepare($sqlHarvestAmountQuery);
        $stmtHarvestAmount->execute();

        $dataHarvestBillAmount = $stmtHarvestAmount->fetch(PDO::FETCH_ASSOC);
        $PastHarvestAmount2 = $dataHarvestBillAmount['PastHarvestAmount'];
        $PastHarvestTds2 = $dataHarvestBillAmount['PastHarvestTds'];

        // $PastSuppliedValue = $PastBillAmount + $PastHarvestAmount1 + $PastHarvestAmount2;
        // $PastSuppliedTds = $PastBillTds + $PastHarvestTds1 + $PastHarvestTds2;

        $PastHarvestAmount = $PastHarvestAmount2;
        $PastHarvestTds = $PastHarvestTds2;

        $Arydata['PastHarvestAmount'] = $PastHarvestAmount;
        $Arydata['PastHarvestTds'] = $PastHarvestTds;



        return $Arydata;
    }

    function getSupplierPastAmount($pastDataRequest)
    {


        $supplierIDs = $pastDataRequest['supplierIDs'];
        $agentIDs = $pastDataRequest['agentIDs'];
        $ArrivalID = $pastDataRequest['ArrivalID'];
        $BillDate = $pastDataRequest['BillDate'];

        $CheckYear = explode('-', $BillDate); //uma  based on bill date confirmed by lokesh //02-12-2023

        if ($CheckYear[1] > 3) {
            $DateStart = $CheckYear[0] . '-04-01'; //2021-04-01
            $DateEnd = ($CheckYear[0] + 1) . '-03-31'; //2022-03-31
        } else {
            $DateStart = ($CheckYear[0] - 1) . '-04-01'; //2021-04-01
            $DateEnd = ($CheckYear[0]) . '-03-31'; //2022-03-31
        }

        $sqlBillAmountQuery = "SELECT
        sum(ArrivalBillAmount) as PastBillAmount,
        sum(ArrivalBillTds) as PastBillTds from RmArrivalDetatils as RAD
        WHERE RAD.RmSupplierID in ($supplierIDs) and BillDate
        between '$DateStart' and '$DateEnd' and RmArrivalID!=$ArrivalID and LotStatus=1";

        $sqlBillAmountQuery = "SELECT sum(BillAmount) as PastBillAmount,
                                sum(BillTds) as PastBillTds from RmBillDetails as RBD
                                left Join RmArrivalDetatils as RAD on RAD.RmArrivalID=RBD.ArrivalID
                                WHERE RAD.RmSupplierID in ($supplierIDs) and RAD.BillDate
                                between '$DateStart' and '$DateEnd' and RAD.RmArrivalID!=$ArrivalID and RAD.LotStatus=1";

        $stmtBillAmount = $this->conn->prepare($sqlBillAmountQuery);
        $stmtBillAmount->execute();

        $dataRowBillAmount = $stmtBillAmount->fetch(PDO::FETCH_ASSOC);
        $PastBillAmount = $dataRowBillAmount['PastBillAmount'];
        $PastBillTds = $dataRowBillAmount['PastBillTds'];


        $sqlHarvestAmountQuery = "SELECT
        sum(HarvestAmount) as PastHarvestAmount ,
        sum(HarvestTds) as PastHarvestTds from RmArrivalDetatils as RBD
        left Join RmArrivalDetatils as RAD on RAD.RmArrivalID=RBD.ArrivalID
        WHERE RAD.RmSupplierID in ($supplierIDs) and AgentID=0 and RAD.BillDate
        between '$DateStart' and '$DateEnd' and RAD.RmArrivalID!=$ArrivalID and RAD.LotStatus=1";
        $stmtHarvestAmount = $this->conn->prepare($sqlHarvestAmountQuery);
        $stmtHarvestAmount->execute();

        $dataHarvestBillAmount = $stmtHarvestAmount->fetch(PDO::FETCH_ASSOC);
        $PastHarvestAmount1 = $dataHarvestBillAmount['PastHarvestAmount'];
        $PastHarvestTds1 = $dataHarvestBillAmount['PastHarvestTds'];

        $sqlHarvestAmountQuery = "SELECT
        sum(HarvestAmount) as PastHarvestAmount ,
        sum(HarvestTds) as PastHarvestTds from RmArrivalDetatils as RBD
        left Join RmArrivalDetatils as RAD on RAD.RmArrivalID=RBD.ArrivalID
        WHERE RAD.AgentID in ($agentIDs) RAD.BillDate between '$DateStart' and '$DateEnd' and RAD.RmArrivalID!=$ArrivalID and RAD.LotStatus=1";
        $stmtHarvestAmount = $this->conn->prepare($sqlHarvestAmountQuery);
        $stmtHarvestAmount->execute();

        $dataHarvestBillAmount = $stmtHarvestAmount->fetch(PDO::FETCH_ASSOC);
        $PastHarvestAmount2 = $dataHarvestBillAmount['PastHarvestAmount'];
        $PastHarvestTds2 = $dataHarvestBillAmount['PastHarvestTds'];

        $PastSuppliedValue = $PastBillAmount + $PastHarvestAmount1 + $PastHarvestAmount2;
        $PastSuppliedTds = $PastBillTds + $PastHarvestTds1 + $PastHarvestTds2;

        $Arydata['PastSuppliedValue'] = $PastSuppliedValue;
        $Arydata['PastSuppliedTds'] = $PastSuppliedTds;

        return $Arydata;
    }

    function getAgentPastAmount($pastDataRequest)
    {
        $supplierIDs = $pastDataRequest['supplierIDs'];
        $agentIDs = $pastDataRequest['agentIDs'];
        $ArrivalID = $pastDataRequest['ArrivalID'];
        $BillDate = $pastDataRequest['BillDate'];
        $agentAsSupplierIDs = $pastDataRequest['agentAsSupplierIDs'];

        $CheckYear = explode('-', $BillDate); //uma  based on bill date confirmed by lokesh //02-12-2023

        if ($CheckYear[1] > 3) {
            $DateStart = $CheckYear[0] . '-04-01'; //2021-04-01
            $DateEnd = ($CheckYear[0] + 1) . '-03-31'; //2022-03-31
        } else {
            $DateStart = ($CheckYear[0] - 1) . '-04-01'; //2021-04-01
            $DateEnd = ($CheckYear[0]) . '-03-31'; //2022-03-31
        }

        $DateEnd = $BillDate;

        // Bill Amount For That Supplier PAN
        $sqlBillAmountQuery = "SELECT
        sum(BillAmount) as PastBillAmount,
        sum(BillTds) as PastBillTds from RmBillDetails as RBD
        left Join RmArrivalDetatils as RAD on RAD.RmArrivalID=RBD.ArrivalID
        WHERE RAD.RmSupplierID in ($agentAsSupplierIDs) and
        RAD.BillDate between '$DateStart' and '$DateEnd' and RAD.RmArrivalID!=$ArrivalID and RAD.LotStatus=1";
        $stmtBillAmount = $this->conn->prepare($sqlBillAmountQuery);
        $stmtBillAmount->execute();

        $dataRowBillAmount = $stmtBillAmount->fetch(PDO::FETCH_ASSOC);
        $PastBillAmount = $dataRowBillAmount['PastBillAmount'];
        $PastBillTds = $dataRowBillAmount['PastBillTds'];

        // Harvert Amount For That Supplier PAN AgentID =0
        $sqlHarvestAmountQuery = "SELECT
        sum(HarvestAmount) as PastHarvestAmount ,
        sum(HarvestTds) as PastHarvestTds from RmArrivalDetatils as RBD
        left Join RmArrivalDetatils as RAD on RAD.RmArrivalID=RBD.ArrivalID
        WHERE RAD.RmSupplierID in ($agentAsSupplierIDs) and RAD.AgentID=0 and
        RAD.BillDate between '$DateStart' and '$DateEnd' and RADRmArrivalID!=$ArrivalID and RAD.LotStatus=1";
        $stmtHarvestAmount = $this->conn->prepare($sqlHarvestAmountQuery);
        $stmtHarvestAmount->execute();

        $dataHarvestBillAmount = $stmtHarvestAmount->fetch(PDO::FETCH_ASSOC);
        $PastHarvestAmount1 = $dataHarvestBillAmount['PastHarvestAmount'];
        $PastHarvestTds1 = $dataHarvestBillAmount['PastHarvestTds'];

        $sqlHarvestAmountQuery = "SELECT
        sum(HarvestAmount) as PastHarvestAmount ,
        sum(HarvestTds) as PastHarvestTds from RmArrivalDetatils as RBD
        left Join RmArrivalDetatils as RAD on RAD.RmArrivalID=RBD.ArrivalID
        WHERE RAD.AgentID in ($agentIDs) RAD.BillDate between '$DateStart' and '$DateEnd'
        and RAD.RmArrivalID!=$ArrivalID and RAD.LotStatus=1";
        $stmtHarvestAmount = $this->conn->prepare($sqlHarvestAmountQuery);
        $stmtHarvestAmount->execute();

        $dataHarvestBillAmount = $stmtHarvestAmount->fetch(PDO::FETCH_ASSOC);
        $PastHarvestAmount2 = $dataHarvestBillAmount['PastHarvestAmount'];
        $PastHarvestTds2 = $dataHarvestBillAmount['PastHarvestTds'];

        $PastSuppliedValue = $PastBillAmount + $PastHarvestAmount1 + $PastHarvestAmount2;
        $PastSuppliedTds = $PastBillTds + $PastHarvestTds1 + $PastHarvestTds2;

        $Arydata['PastSuppliedValue'] = $PastSuppliedValue;
        $Arydata['PastSuppliedTds'] = $PastSuppliedTds;

        return $Arydata;
    }


    /***words in rupees** */
    function AmountInWords($number)
    {
        $no = (int) floor($number);
        $point = (int) round(($number - $no) * 100);
        $hundred = null;
        $digits_1 = strlen($no);
        $i = 0;
        $str = array();
        $words = array(
            '0' => '',
            '1' => 'One',
            '2' => 'Two',
            '3' => 'Three',
            '4' => 'Four',
            '5' => 'Five',
            '6' => 'Six',
            '7' => 'Seven',
            '8' => 'Eight',
            '9' => 'Nine',
            '10' => 'Ten',
            '11' => 'Eleven',
            '12' => 'Twelve',
            '13' => 'Thirteen',
            '14' => 'Fourteen',
            '15' => 'Fifteen',
            '16' => 'Sixteen',
            '17' => 'Seventeen',
            '18' => 'Eighteen',
            '19' => 'Nineteen',
            '20' => 'Twenty',
            '30' => 'Thirty',
            '40' => 'Forty',
            '50' => 'Fifty',
            '60' => 'Sixty',
            '70' => 'Seventy',
            '80' => 'Eighty',
            '90' => 'Ninety'
        );
        $digits = array('', 'Hundred', 'Thousand', 'Lakh', 'Crore');
        while ($i < $digits_1) {
            $divider = ($i == 2) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += ($divider == 10) ? 1 : 2;


            if ($number) {
                //  $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
                $plural = (($counter = count($str)) && $number > 9) ? '' : null;
                $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                $str[] = ($number < 21) ? $words[$number] .
                    " " . $digits[$counter] . $plural . " " . $hundred
                    :
                    $words[floor($number / 10) * 10]
                    . " " . $words[$number % 10] . " "
                    . $digits[$counter] . $plural . " " . $hundred;
            } else
                $str[] = null;
        }
        $str = array_reverse($str);
        $result = implode('', $str);


        if ($point > 20) {
            $points = ($point) ?
                "" . $words[floor($point / 10) * 10] . " " .
                $words[$point = $point % 10] : '';
        } else {
            $points = $words[$point];
        }
        if ($points != '') {
            $resultinwords = $result . "Rupees  and " . $points . " Paise Only";
        } else {

            $resultinwords = $result . "Rupees Only";
        }


        return $resultinwords;
    }


    /***words in rupees** */



    function createDateRangeArray($strDateFrom, $strDateTo)
    {
        // takes two dates formatted as YYYY-MM-DD and creates an
        // inclusive array of the dates between the from and to dates.

        // could test validity of dates here but I'm already doing
        // that in the main script

        $aryRange = [];

        $iDateFrom = mktime(1, 0, 0, substr($strDateFrom, 5, 2), substr($strDateFrom, 8, 2), substr($strDateFrom, 0, 4));
        $iDateTo = mktime(1, 0, 0, substr($strDateTo, 5, 2), substr($strDateTo, 8, 2), substr($strDateTo, 0, 4));

        if ($iDateTo >= $iDateFrom) {
            array_push($aryRange, date('Y-m-d', $iDateFrom)); // first entry
            while ($iDateFrom < $iDateTo) {
                $iDateFrom += 86400; // add 24 hours
                array_push($aryRange, date('Y-m-d', $iDateFrom));
            }
        }
        return $aryRange;
    }




    function getFormatedVoucherNumber($PaymentDate, $CenterID, $PaymentMode) ////uma///////
    {
        $CheckYear = explode('-', $PaymentDate);

        if ($CheckYear[1] > 3) {
            $DateStart = $CheckYear[0] . '-04-01'; //2021-04-01
            $DateEnd = ($CheckYear[0] + 1) . '-03-31'; //2022-03-31
        } else {
            $DateStart = ($CheckYear[0] - 1) . '-04-01'; //2021-04-01
            $DateEnd = ($CheckYear[0]) . '-03-31'; //2022-03-31
        }

        $SqlMaxVoucher = "SELECT max(VoucherNo) as Voucher FROM `RmPaymentDetails` WHERE Date BETWEEN '$DateStart' and '$DateEnd'
         and StationID='$CenterID' and PaymentMode='$PaymentMode'";
        $stmtMaxVoucher = $this->conn->prepare($SqlMaxVoucher);
        $stmtMaxVoucher->execute();
        $RowMaxVoucher = $stmtMaxVoucher->fetch(PDO::FETCH_ASSOC);

        $AutoVoucher = $RowMaxVoucher['Voucher'] + 1;


        $sqlQuery = "SELECT LotCode FROM `RmPurchaseStations` WHERE PurchaseStationID='$CenterID'";
        $stmt = $this->conn->prepare($sqlQuery);
        // $stmt->bindParam(':proformaID', $proformaID);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $LotCode = $dataRow['LotCode'];
        // $ArrivalNumber = str_replace("O", "0", $ArrivalNumber);
        // $ArrivalNumber = ltrim($ArrivalNumber, "0");
        // $ArrivalNumber = preg_replace("/[^0-9]/", "", $ArrivalNumber);
        $Voucher = sprintf('%02d', $AutoVoucher);
        $Voucher = $PaymentMode . $LotCode . $Voucher;

        $ArrayVoucher['AutoVoucherNo'] = $AutoVoucher;
        $ArrayVoucher['VoucherNo'] = $Voucher;

        return $ArrayVoucher;
    }


    function getGradingStatus($ArrivalID)
    {
        $Data['GradingAval'] = false;

        $Sqlquery = "SELECT IsCalcVales,Comission as DefComission,RmLotID FROM `RmArrivalDetatils` as RAD
        LEFT join RmPurchaseCategory as RPC on RAD.PurchaseCategoryID=RPC.CatID
        LEFT join RmLotDetatils as RLD on RLD.RmReferenceID=RAD.RmArrivalID
        WHERE RAD.RmArrivalID='$ArrivalID'";
        $stmt = $this->conn->prepare($Sqlquery);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        $RmLotID = $dataRow['RmLotID'];
        if ($dataRow['IsCalcVales'] == 1) {
            $Data['IsCalcVales'] = true;
        } else {
            $Data['IsCalcVales'] = false;
            $Data['GradingAval'] = true;
        }


        $Sqlquery = "SELECT * FROM `PpcHLGradingDetails` where LotID='$RmLotID'";
        $stmt = $this->conn->prepare($Sqlquery);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            $Data['GradingAval'] = true;
        } else {
            $Data['GradingAval'] = false;
        }



        $Data['IsCalcVales'];
        $Data['DefComission'] = $dataRow['DefComission'];



        return $Data;
    }

    ////uma///////

    function customRound($val,$c_ArrivalDate='')
    {
        if( $this->clientid == "DFLV3" ){
            if(isset($c_ArrivalDate) && $c_ArrivalDate !="" ){

                if($c_ArrivalDate >= $this->tdsceil_startDate){
                    return $val;
                }
            }
        }
        $intPart = floor($val);
        $decimalPart = $val - $intPart;

        if ($decimalPart >= 0.10) {
            return ceil($val);
        } else {
            return $intPart;
        }

        return $val;
    }

    // function updateBillTable(){
    function updateBillTable($ArrivalID, $tdsAmount, $ClientID)
    {
        $Sqlquery = "SELECT RmArrivalID,ArrivalDate,BillDate FROM `RmArrivalDetatils` where RmArrivalID='$ArrivalID'";
        $stmt = $this->conn->prepare($Sqlquery);

        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrivalID = $dataRow['RmArrivalID'];
            $ArrivalDate = $dataRow['ArrivalDate'];
            $BillDate = $dataRow['BillDate'];
            $aryRequest['ArrivalID'] = $ArrivalID;
            $aryRequest['ArrivalBrkID'] = '';
            $aryRequest['isEditBill'] = true;
            $ArrayData = $this->getArrivalPriceDetailsV3($aryRequest, $tdsAmount);
            $BillAmount = $ArrayData['BillAmount'];
            $HarvestBillAmount = $ArrayData['HarvestBillAmount'];
            $ActBillTds = $BillTDS = $ArrayData['BillTDS'];
            $ActHarvestTds = $HarvestTDS = $ArrayData['HarvestTDS'];
            $UnderTdsAmount = $ArrayData['UnderTdsAmount'];
            $TdsDeductedValue = $ArrayData['TdsDeductedValue'];
            $c_ArrivalDate = new DateTime($BillDate);

            $BillTDS = $this->getTdsRound($BillTDS,$c_ArrivalDate);
            $HarvestTDS = $this->getTdsRound($HarvestTDS,$c_ArrivalDate);
            $HarvestCommision = 0;
            $AgentComm = $this->appController->getClientConfig($ClientID)['AgentComissionTwoPercent'];
            if ($AgentComm) {
                $c_billDate = new DateTime($BillDate);
                $c_startDate = new DateTime('2025-08-01');


                if ($c_billDate >= $c_startDate) {
                    $HarvestCommision = $this->customRound($HarvestBillAmount * 0.02,$c_ArrivalDate);
                    $HarvestTDS = 0;
                    $ActHarvestTds = 0;
                }
            }

            if( $this->clientid == "DFLV3" && $c_ArrivalDate >= $this->tdsceil_startDate){
                $BillTDS = ceil($BillTDS);
                $HarvestCommision = ceil($HarvestCommision);
            }

            $sqlupdateQuery = "  UPDATE `RmBillDetails` SET
            `BillAmount`='$BillAmount',
            `HarvestAmount`='$HarvestBillAmount',
            `BillTds`='$BillTDS',
            `HarvestTds`='$HarvestTDS' ,
            `ActBillTds`='$ActBillTds',
            `ActHarvestTds`='$ActHarvestTds' ,
            `HarvestCommision`='$HarvestCommision' ,
            `TdsDeductedValue`='$TdsDeductedValue' ,
            `UnderTdsAmount`='$UnderTdsAmount'
            WHERE `ArrivalID`='$ArrivalID'";
            $stmtupdate = $this->conn->prepare($sqlupdateQuery);

            $stmtupdate->execute();
        }
    }


    function updateArrivalBillAmountandTds($ArrivalID)
    {
        $Sqlquery = "SELECT RmArrivalID,ArrivalDate,BillDate FROM `RmArrivalDetatils` where RmArrivalID='$ArrivalID'";
        $stmt = $this->conn->prepare($Sqlquery);

        $stmt->execute();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrivalID = $dataRow['RmArrivalID'];
            $ArrivalDate = $dataRow['ArrivalDate'];
            $BillDate = $dataRow['BillDate'];
            $c_ArrivalDate = new DateTime($BillDate);

            $ArrayData = $this->getArrivalPriceDetails($ArrivalID, '');

            $BillAmount = $ArrayData['BillAmount'];
            $HarvestBillAmount = $ArrayData['HarvestBillAmount'];
            $BillTDS = $ArrayData['BillTDS'];
            $HarvestTDS = $ArrayData['HarvestTDS'];

            $BillTDS = $this->getTdsRound($BillTDS,$c_ArrivalDate);
            $HarvestTDS = $this->getTdsRound($HarvestTDS,$c_ArrivalDate);

            if ($HarvestBillAmount > 0 && $HarvestTDS == 0) {
                // $HarvestTDS = 1; bu teja beacuse of 50l condition
            }


            if( $this->clientid == "DFLV3" && $c_ArrivalDate >= $this->tdsceil_startDate){
                $BillTDS = ceil($BillTDS);
                $HarvestTDS = ceil($HarvestTDS);
            }

            $sqlupdateQuery = "  UPDATE `RmArrivalDetatils` SET `ArrivalBillAmount`='$BillAmount',`ArrivalHarvestAmount`='$HarvestBillAmount',
            `ArrivalBillTds`='$BillTDS',`ArrivalHarvestTds`='$HarvestTDS' WHERE `RmArrivalID`='$ArrivalID'";
            $stmtupdate = $this->conn->prepare($sqlupdateQuery);

            $stmtupdate->execute();
        }
    }




    function getFinDateFilter($Date)
    {

        $CheckYear = explode('-', $Date);

        if ($CheckYear[1] > 3) {
            $DateStart = $CheckYear[0] . '-04-01'; //2021-04-01
            $DateEnd = ($CheckYear[0] + 1) . '-03-31'; //2022-03-31
        } else {
            $DateStart = ($CheckYear[0] - 1) . '-04-01'; //2021-04-01
            $DateEnd = ($CheckYear[0]) . '-03-31'; //2022-03-31
        }

        return "BETWEEN '$DateStart' and '$DateEnd'";
    }






    function getCalcWeight($BreakUpID)
    {


        // $Sqlquery = "SELECT PurchaseDate as BillDate,PurchaseCount as ExpCount,PurchaseWeight as ExpQty,RmReferenceID
        //  FROM `RmLotDetatils` as RLD WHERE RLD.BreakUpID='$BreakUpID'";

        $Sqlquery = "SELECT ArrivalStationID,BillDate,PurchaseCount as ExpCount,Weight as ExpQty,ArrivalID as RmReferenceID,RmLotID FROM `RmArrivalBreakUpDetails` as RABD 
 left join RmArrivalDetatils as RAD  on RABD.ArrivalID=RAD.RmArrivalID
 left join RmLotDetatils as RLD  on RABD.ArrivalBrkUpID=RLD.BreakUpID WHERE RABD.ArrivalBrkUpID='$BreakUpID'";
        $stmt = $this->conn->prepare($Sqlquery);
        $stmt->execute();
        $arrivalDetails = $stmt->fetch(PDO::FETCH_ASSOC);


        //    print_r($this->conn->errorInfo());
        //     print_r($stmt->errorInfo());
        //     var_dump($arrivalDetails);

        $ArrivalID = $arrivalDetails['RmLotID'];
        $PurchaseDate = $arrivalDetails['BillDate'];
        $ExpCount = $arrivalDetails['ExpCount'];
        $ExpQty = $arrivalDetails['ExpQty'];
        $RmReferenceID = $arrivalDetails['RmReferenceID'];
        $CenterID = $arrivalDetails['ArrivalStationID'];


        $Sqlquery = "SELECT Species FROM `RmArrivalDetatils` as RLD WHERE RmArrivalID='$RmReferenceID'";
        $stmt = $this->conn->prepare($Sqlquery);
        $stmt->execute();
        $arrivalDetails = $stmt->fetch(PDO::FETCH_ASSOC);
        $Species = $arrivalDetails['Species'];


        $SqlHonGrading = "SELECT sum(HonWeight) as HonPacked FROM `PpcHonGradingBreakup` WHERE LotID='$ArrivalID'";
        $StmtHonGrading = $this->conn->prepare($SqlHonGrading);
        $StmtHonGrading->execute();
        $RowHonGrading = $StmtHonGrading->fetch(PDO::FETCH_ASSOC);
        $HonPacked = $RowHonGrading['HonPacked'];

        $SqlGrading = "SELECT * FROM `PpcHLGradingBreakup` where LotID='$ArrivalID' Order by HLCount ASC";
        $StmtGrading = $this->conn->prepare($SqlGrading);
        $StmtGrading->execute();

        $HLWeight = 0;
        $AryGrades = array();
        while ($RowGrading = $StmtGrading->fetch(PDO::FETCH_ASSOC)) {
            $HLWeight = $HLWeight + $RowGrading['HLWeight'];
            $AryGrades[] = $RowGrading;
        }


        $TempYield = $this->getStandardYeild($ExpCount, 'HL', $Species, $PurchaseDate);
        $TempCount = $this->getCalcActualCount($TempYield, $AryGrades);
        $AvgCount = $this->getCalcActualCount($TempYield, $AryGrades);
        $StandardYield = $this->getStandardYeild($AvgCount, 'HL', $Species, $PurchaseDate);
        $LoopCount = 0;
        $YieldError = 0;

        while ($TempYield != $StandardYield) {
            $TempCount = $this->getCalcActualCount($StandardYield, $AryGrades);
            $TempYield = $this->getStandardYeild($TempCount, 'HL', $Species, $PurchaseDate);
            $AvgCount = $this->getCalcActualCount($TempYield, $AryGrades);
            $StandardYield = $this->getStandardYeild($AvgCount, 'HL', $Species, $PurchaseDate);
            $AvgCount = $this->getCalcActualCount($StandardYield, $AryGrades);
            if ($LoopCount > 10) {
                $YieldError = 1;
                if ($TempYield < $StandardYield) {
                    $StandardYield = $TempYield;
                    $AvgCount = $TempCount;
                }
                break;
            }
            $LoopCount++;
        }


        $BillYield = $StandardYield;
        $BillHonWeight = round(($HLWeight / $BillYield) * 100, 2);
        $BillHonCount = $AvgCount;
        // $PriceData = $this->getRmPrice($Species, $PurchaseDate, $BillHonCount);
        $PriceData = $this->getRmPrice($Species, $PurchaseDate, $BillHonCount, $CenterID); //updated by uma on 10-07-2024
        $BillRate = $PriceData['RmPrice'];
        // $RmPriceDate=$PriceData['RmPriceDate'];
        $BillHonCount = $PriceData['BillHonCount'];




        $BillHonWeight = $BillHonWeight + $HonPacked;

        $DATA['Yield'] = $BillYield;
        $DATA['YieldError'] = $YieldError;
        $DATA['HonWeight'] = $BillHonWeight;
        $DATA['HonCount'] = $BillHonCount;
        $DATA['BillRate'] = $BillRate;


        return $DATA;
    }



    function getStandardYeild($AVG_COUNT, $TYPE, $Species, $BillDate)
    {


        $sql_yield = "SELECT * FROM `RmStandardYields`
      where Type='$Species'
      and LowerCount <= '$AVG_COUNT'
      and UpperCount >='$AVG_COUNT' AND EffectiveDate<='$BillDate' ORDER BY EffectiveDate DESC";
        $stmt = $this->conn->prepare($sql_yield);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $STRND_YEILD = $row['Yield'];

        return $STRND_YEILD;
    }

    function getCalcActualCount($StandardYield, $AryGrades)
    {
        $Weight = 0;
        $Pieces = 0;

        //print_r($AryGrades);

        foreach ($AryGrades as $Item) {
            $Weight += $Item['HLWeight'];
            $Pieces += $Item['HLWeight'] * $Item['HLCount'] * 2.2;
        }

        //   echo $StandardYield;

        $HonQty = ($Weight / $StandardYield) * 100;

        $ActCount = $Pieces / $HonQty;
        $ActCount = round($ActCount, 2);
        //  $ActCount= getAvgCount($ActCount);

        /*          if($ActCount != round($ActCount)){
        $ActCount=$ActCount-0.5;  //LOT ID ISSUE  6311
        }
        */
        $ActCount;

        return $ActCount;
    }

    function getNumberOfRecords($Table, $Conditions)
    {
        $sqlQuery = "SELECT * FROM $Table WHERE $Conditions";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        return $stmt->rowCount();
    }

    function checkVehicleIsUsedWithSameDateAndAnotherPlant($VehicleID, $ArrivalDate, $PlantId)
    {


        $sql_yield = "SELECT * FROM `RmArrivalDetatils`
      where VehicalID='$VehicleID' AND ArrivalDate='$ArrivalDate' AND ProcessingStationID not in ($PlantId)";
        $stmt = $this->conn->prepare($sql_yield);
        $stmt->execute();
        if ($stmt->rowCount() == 0) {
            return true;
        } else {
            return false;
        }
    }
}
