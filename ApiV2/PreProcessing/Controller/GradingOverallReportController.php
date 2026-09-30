<?php

if (1) {
    /****************************************this is new code********************************
     * Removed some quires
     * Moved outside from loop few quires
     *****************************************************************************************/
    // ini_set('display_errors', 1);
    // ini_set('display_startup_errors', 1);
    // error_reporting(E_ERROR);

    include_once '../Global/Util.php';
    class GradingOverallReportController
    {
        private $conn;
        private $Error = null;
        private $Msg   = '';

        public function __construct($db)
        {
            $this->conn = $db;
        }
        public function getGradingTrackNumbers($RequestBody)
        {
            if(!empty($RequestBody["type"]) && $RequestBody["type"] == 'getGradingMachines'){
                $results = $this->getGradingMachinesListByPlantId($RequestBody['ReceivingPlantId']);
                if(!empty($results)){
                    $Data['Error'] = null;
                    $Data['Msg'] = 'Success';
                    $Data['Data'] = $results;
                }else{
                    $Data['Error'] = 'No Data Found';
                }
                return $Data;
            }
            // $ArrivalDate      = $RequestBody['ArrivalDate'];
            $FromDate         = $RequestBody['FromDate'];
            $ToDate           = $RequestBody['ToDate'];
            $MachineID        = $RequestBody['MachineID'];
            $ReceivingPlantID = $RequestBody['ReceivingPlantID'];
            $IsPlantFilter    = $RequestBody['IsPlantFilter'];
            $MergeHonLoose    = $RequestBody['MergeHonLoose'];
            $GradingDate = $RequestBody['GradingDate'];


            $Query = '';
            if($FromDate && $ToDate){
               $Query = " RmRD.ArrivalDate BETWEEN '$FromDate' AND '$ToDate' "; 
            }elseif($GradingDate){
                $Query = " PHGD.GradingDate = '$GradingDate' "; 
            }else{
                $Query = " 1 ";
            }
            if ($MachineID != '') {
                $Query .= " AND PHGD.MachineID='$MachineID'";
            }
            if ($ReceivingPlantID != '') {
                $Query .= " AND RmRD.ReceivingPlantID='$ReceivingPlantID'";
            }

            $OrderQuery = '';
            if ($IsPlantFilter == 1) {
                $OrderQuery = ' ORDER BY RmRD.ReceivingID';
            } else {
                $OrderQuery = '';
            }
            $sqlFields = "DISTINCT PHGD.TrackID,RmRD.ArrivalDate,PHGD.MachineID, GradingDate,
            RmRD.TrackCode,RmRD.ReceivingPlantID, MachineName, RmRD.ReceivingID";

            $sqlQuery = "SELECT $sqlFields FROM PpcHLGradingDetails AS PHGD
            LEFT JOIN RmReceivingDetails AS RmRD ON RmRD.ReceivingID = PHGD.TrackID
            LEFT JOIN AdminGradingMachines AS AGD ON AGD.MachineID=PHGD.MachineID
            WHERE $Query GROUP BY RmRD.TrackCode,GradingDate $OrderQuery";

            $stmtSelect = $this->conn->prepare($sqlQuery);
            $stmtSelect->execute();

            $ArrayReceivedData  = [];
            $i                  = 0;
            $combinedArrivalIDs = [];
            while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
                $TrackID     = $dataRow['TrackID'];
                $GradingDate = $dataRow['GradingDate'];
                $BillNoData  = $this->getBillDetails($TrackID, $GradingDate, $MergeHonLoose);

                $ArrayReceivedDetails['SNo']           = ++$i;
                $ArrayReceivedDetails['ArrivalDate']   = $dataRow['ArrivalDate'] ? date('d-m-Y', strtotime($dataRow['ArrivalDate'])) : '';
                $ArrayReceivedDetails['MachineID']     = $dataRow['MachineID'];
                $ArrayReceivedDetails['MachineName']   = $dataRow['MachineName'];
                $ArrayReceivedDetails['TrackID']       = $dataRow['TrackID'];
                $ArrayReceivedDetails['TrackCode']     = $dataRow['TrackCode'];
                $ArrayReceivedDetails['GradingDate']   = $dataRow['GradingDate'] ? date('d-m-Y', strtotime($dataRow['GradingDate'])) : '';
                $ArrayReceivedDetails['BillDataCount'] = $BillNoData['RowCount'];
                $ArrayReceivedDetails['BillData']      = $BillNoData['BillData'];

                if ($BillNoData['BillData'] != null) {
                    if (isset($BillNoData['BillData'])) {
                        foreach ($BillNoData['BillData'] as $bill) {
                            $combinedArrivalIDs[] = $bill['ArrivalID'];
                        }
                    }
                    $ArrayReceivedData[] = $ArrayReceivedDetails;
                }
            }

            $VarietyCombinationData            = $this->getVarietyWiseData($RequestBody, $combinedArrivalIDs);
            $VarietyCombinationWeightTrackwise = $this->getVarietyWiseTotals($combinedArrivalIDs, $RequestBody);
            $GrandTotals                       = $this->getOverallTotals($RequestBody);

            $Data['Error']                             = null;
            $Data['Msg']                               = 'Success';
            $Data['Data']                              = $ArrayReceivedData;
            $Data['VarietyCombinationData']            = $VarietyCombinationData['ArrayData'];
            $Data['VarietyCombinationWeightTrackwise'] = $VarietyCombinationWeightTrackwise;
            $Data['GrandTotals']                       = $GrandTotals;

            return $Data;
        }

        public function getBillDetails($TrackID, $GradingDate, $MergeHonLoose)
        {
            $sqlFields = " ArrivalNumber,Count, Weight,HLID as GradingID";
            if ($GradingDate != '') {
                $Qry = " and  GradingDate='$GradingDate'";
            }

            $SqlGradingCount = "SELECT RLD.RmLotID,PHGD.MixIDs,PHGD.IsMix
            FROM RmReceivingBreakup as RRB
            left join PpcHLGradingDetails  as PHGD on PHGD.TrackBreakUpID=RRB.ReceivalBreakupID
            left join RmLotDetatils  as RLD  on RLD.RmLotID=PHGD.LotID
            where ReceivingID='$TrackID'  $Qry  and RmLotID is NOT null group by MixIDs";

            $stmtGradingCount = $this->conn->prepare($SqlGradingCount);
            $stmtGradingCount->execute();
            $BillListCount = $stmtGradingCount->rowCount();

            $ArrayReceivingCount = [];
            while ($dataRowGradingCount = $stmtGradingCount->fetch(PDO::FETCH_ASSOC)) {
                $IsMix = $dataRowGradingCount['IsMix'];

                $LotID  = $dataRowGradingCount['RmLotID'];
                $MixIDs = $dataRowGradingCount['MixIDs'];

                // if ($IsMix == 1) {
                //     $GroupQuery      = " and RHGD.MixIDs in ($MixIDs) group by MixIDs";
                //     $BillNumberField = ",GROUP_CONCAT(ReceivalBreakupID) as ReceivalBreakupID,GROUP_CONCAT(RmLotID)as RmArrivalID, GROUP_CONCAT(ArrivalNumber,' (',round(Count,2),'-',Weight,')' SEPARATOR ',  ') as BillNumber";
                // } else {
                //     $BillNumberField = ",ReceivalBreakupID,RmLotID as RmArrivalID";
                //     $GroupQuery      = " and RLD.RmLotID in ($LotID )";
                // }

                if ($IsMix == 1) {
                    $GroupQuery = " and RHGD.MixIDs in ($MixIDs) group by MixIDs";
                    /* if ($MergeHonLoose == 1) {
                        $BillNumberField = ",GROUP_CONCAT(ReceivalBreakupID) as ReceivalBreakupID,GROUP_CONCAT(RmLotID)as RmArrivalID, GROUP_CONCAT(ArrivalNumber,'(',Count ,' - ',SUM(Weight),')') as BillNumber";
                    } else { */
                        $BillNumberField = ",GROUP_CONCAT(ReceivalBreakupID) as ReceivalBreakupID,GROUP_CONCAT(RmLotID)as RmArrivalID, GROUP_CONCAT(ArrivalNumber,'(',Count ,' - ',Weight,')') as BillNumber";
                    //}
                } else {
                    $BillNumberField = ",ReceivalBreakupID,RmLotID as RmArrivalID";
                    // $GroupQuery = " and RLD.RmLotID='$LotID' ";
                    $GroupQuery = " and RLD.RmLotID in ($LotID )";
                }

                if ($MergeHonLoose == 1) {
                    $sqlFields = " ArrivalNumber,Count, SUM(Weight) AS Weight,HLID as GradingID";

                    $GroupHonQuery = " GROUP BY RAD.ArrivalNumber";
                    if($IsMix == 1){
                        //$GroupHonQuery = ",RAD.ArrivalNumber";
                        $GroupHonQuery = "";
                    }
                    $sqlQueryReceivingCounts = "SELECT $sqlFields $BillNumberField FROM RmArrivalBreakUpDetails AS RABD
                LEFT JOIN RmArrivalDetatils AS RAD ON RAD.RmArrivalID=RABD.ArrivalID
                LEFT JOIN RmReceivingBreakup AS RRB ON RRB.ArrivalID=RABD.ArrivalID
                LEFT JOIN RmLotDetatils AS RLD ON RLD.BreakUpID=RRB.ArrivalBreakupID
                LEFT JOIN PpcHLGradingDetails AS RHGD ON RHGD.LotID=RLD.RmLotID
                WHERE RRB.ReceivingID= '$TrackID' AND ReceivalBreakupID IS NOT NULL $GroupQuery $GroupHonQuery";
                } else {
                    $sqlFields = " ArrivalNumber,Count, Weight,HLID as GradingID";
                    $sqlQueryReceivingCounts = "SELECT $sqlFields $BillNumberField FROM RmArrivalBreakUpDetails AS RABD
                LEFT JOIN RmArrivalDetatils AS RAD ON RAD.RmArrivalID=RABD.ArrivalID
                LEFT JOIN RmReceivingBreakup AS RRB ON RRB.ArrivalBreakupID=RABD.ArrivalBrkUpID
                LEFT JOIN RmLotDetatils AS RLD ON RLD.BreakUpID=RRB.ArrivalBreakupID
                LEFT JOIN PpcHLGradingDetails AS RHGD ON RHGD.LotID=RLD.RmLotID
                WHERE RRB.ReceivingID= '$TrackID' AND ReceivalBreakupID IS NOT NULL $GroupQuery ";
                }
                // $sqlQueryReceivingCounts = "SELECT $sqlFields $BillNumberField FROM RmArrivalBreakUpDetails AS RABD
                // LEFT JOIN RmArrivalDetatils AS RAD ON RAD.RmArrivalID=RABD.ArrivalID
                // LEFT JOIN RmReceivingBreakup AS RRB ON RRB.ArrivalBreakupID=RABD.ArrivalBrkUpID
                // LEFT JOIN RmLotDetatils AS RLD ON RLD.BreakUpID=RRB.ArrivalBreakupID
                // LEFT JOIN PpcHLGradingDetails AS RHGD ON RHGD.LotID=RLD.RmLotID
                // WHERE RRB.ReceivingID= '$TrackID' AND ReceivalBreakupID IS NOT NULL $GroupQuery ";
                $stmtReceivingCounts = $this->conn->prepare($sqlQueryReceivingCounts);
                $stmtReceivingCounts->execute();
                $dataRowReceivingCounts = $stmtReceivingCounts->fetch(PDO::FETCH_ASSOC);
                $ReceivingCount         = array();

                $ReceivingCount['ReceivalBreakupID'] = $dataRowReceivingCounts['ReceivalBreakupID'];
                $ReceivingCount['ArrivalID']         = $dataRowReceivingCounts['RmArrivalID'];
                if ($IsMix == 0) {
                    $ReceivingCount['BillNumber'] = $dataRowReceivingCounts['ArrivalNumber'] . ' (' . round($dataRowReceivingCounts['Count'], 2) . '-' . $dataRowReceivingCounts['Weight'] . ')';
                } else {
                    $ReceivingCount['BillNumber'] = $dataRowReceivingCounts['BillNumber'];
                }

                $ArrayReceivingCount[] = $ReceivingCount;
            }
            $data['RowCount'] = $BillListCount;
            $data['BillData'] = $ArrayReceivingCount;

            return $data;
        }

        public function getVarietyWiseTotals($combinedArrivalIDs, $RequestBody)
        {
            $TypeProduct = $RequestBody['TypeProduct'];
            $Grade = $RequestBody['Grade'];
            $MergeHonLoose = $RequestBody['MergeHonLoose'];

            $Query = '';
            if ($TypeProduct != '') {
                $Query .= " AND PHVL.TypeProduct='$TypeProduct'";
            }
            if ($Grade != '') {
                $Query .= " AND PHGB.PackingGrade='$Grade'";
            }
            $ArrayTotalHLWeightTrackwise = array();
            foreach ($combinedArrivalIDs as $idGroup) {
                $sqlHLWeightTrackwise = "SELECT SUM(HLWeight) AS TrackwiseWeight ,PHVL.Species FROM PpcHLGradingBreakup AS PHGB
                LEFT JOIN PpcHLVAVarietyList AS PHVL ON PHGB.VarietyID=PHVL.VarietyID
                WHERE  PHGB.MixedID IN ($idGroup) $Query";
                $stmtHLWeightTrackwise = $this->conn->prepare($sqlHLWeightTrackwise);
                $stmtHLWeightTrackwise->execute();
                while ($rowHLWeightTrackwise = $stmtHLWeightTrackwise->fetch(PDO::FETCH_ASSOC)) {
                    $ArrayTotalHLWeightTrackwiseList['HlWeight'] = $rowHLWeightTrackwise['TrackwiseWeight'] ? $rowHLWeightTrackwise['TrackwiseWeight'] : '0.00';
                    $ArrayTotalHLWeightTrackwiseList['Species'] = $rowHLWeightTrackwise['Species'];
                }

                if ($MergeHonLoose == 1) {
                    $sqlHONQuery = "SELECT sum(Weight) as HonWeight ,GROUP_CONCAT(RLD.LotNumber) as LotNumber FROM `RmLotDetatils` as RLD
                            LEFT JOIN RmArrivalBreakUpDetails as RABD on RABD.ArrivalID=RLD.RmReferenceID WHERE RmLotID in ($idGroup)";
                } else {
                    $sqlHONQuery = "SELECT sum(Weight) as HonWeight FROM `RmLotDetatils` as RLD
                        LEFT JOIN RmArrivalBreakUpDetails as RABD on RABD.ArrivalBrkUpID=RLD.BreakUpID WHERE RmLotID in ($idGroup)";
                }

                $stmtHON = $this->conn->prepare($sqlHONQuery);
                $stmtHON->execute();
                while ($rowHONWeight = $stmtHON->fetch(PDO::FETCH_ASSOC)) {
                    $ArrayTotalHLWeightTrackwiseList['HonWeight'] = $rowHONWeight['HonWeight'] ? $rowHONWeight['HonWeight'] : '0.00';
                }
                $TotalYeild                               = ($ArrayTotalHLWeightTrackwiseList['HlWeight'] / $ArrayTotalHLWeightTrackwiseList['HonWeight']) * 100;
                $ArrayTotalHLWeightTrackwiseList['Yield'] = round($TotalYeild, 2);
                $ArrayTotalHLWeightTrackwise[]            = $ArrayTotalHLWeightTrackwiseList;
            }
            return $ArrayTotalHLWeightTrackwise;
        }

        public function getVarietyWiseData($RequestBody, $combinedArrivalIDs)
{
    $FromDate         = $RequestBody['FromDate'];
    $ToDate           = $RequestBody['ToDate'];
    $MachineID        = $RequestBody['MachineID'];
    $ReceivingPlantID = $RequestBody['ReceivingPlantID'];
    $TypeProduct      = $RequestBody['TypeProduct'];
    $MergeHonLoose    = $RequestBody['MergeHonLoose'];
    $Grade            = $RequestBody['Grade'];
    $GradingDate      = $RequestBody['GradingDate'];

    //----------------------------------------------------------
    // Build Where Condition
    //----------------------------------------------------------

    if ($FromDate && $ToDate) {
        $Query = "RmRD.ArrivalDate BETWEEN '$FromDate' AND '$ToDate'";
    } elseif ($GradingDate) {
        $Query = "PHGD.GradingDate='$GradingDate'";
    } else {
        $Query = "1";
    }

    if ($MachineID != '') {
        $Query .= " AND PHGD.MachineID='$MachineID'";
    }

    if ($ReceivingPlantID != '') {
        $Query .= " AND RmRD.ReceivingPlantID='$ReceivingPlantID'";
    }

    if ($TypeProduct != '') {
        $Query .= " AND PHVL.TypeProduct='$TypeProduct'";
    }

    if ($Grade != '') {
        $Query .= " AND PHGB.PackingGrade='$Grade'";
    }

    //----------------------------------------------------------
    // Dynamic SUM Columns
    //----------------------------------------------------------

    $sumColumns = [];

    foreach ($combinedArrivalIDs as $index => $idGroup) {
        if($idGroup){
            $sumColumns[] = "
                SUM(
                    CASE
                        WHEN PHGB.MixedID IN ($idGroup)
                        THEN PHGB.HLWeight
                        ELSE 0
                    END
                ) AS Weight$index
            ";
        }
    }
    $sumColumns = implode(",\n", $sumColumns);

    //----------------------------------------------------------
    // Single SQL Query
    //----------------------------------------------------------

    $sql = "
        SELECT
            PHGB.VarietyID,
            PHGB.HLCount,
            PHGB.PackingGrade,
            PHGB.Treatment,
            PHVL.ShortName,
            PHVL.Species,
            $sumColumns

        FROM PpcHLGradingBreakup PHGB

        LEFT JOIN PpcHLVAVarietyList PHVL
            ON PHGB.VarietyID = PHVL.VarietyID

        LEFT JOIN PpcHLGradingDetails PHGD
            ON PHGD.LotID = PHGB.LotID

        LEFT JOIN RmReceivingDetails RmRD
            ON RmRD.ReceivingID = PHGD.TrackID

        WHERE $Query

        GROUP BY
            PHGB.VarietyID,
            PHGB.HLCount,
            PHGB.PackingGrade,
            PHGB.Treatment,
            PHVL.ShortName,
            PHVL.Species

        ORDER BY PHGB.HLCount ASC
    ";

    $stmt = $this->conn->prepare($sql);
    $stmt->execute();

    $ArrayData = [];
    $i = 0;

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

        $ArrayList = [];

        $ArrayList['SNO']          = ++$i;
        $ArrayList['VarietyID']    = $row['VarietyID'];
        $ArrayList['HLCount']      = $row['HLCount'];
        $ArrayList['PackingGrade'] = $row['PackingGrade'];
        $ArrayList['ShortName']    = $row['ShortName'] . "-" . $row['Species'];
        $ArrayList['Treatment']    = $row['Treatment'];

        $ArrayHLWeightData = [];
        $TotalHLWeight = 0;

        foreach ($combinedArrivalIDs as $index => $value) {

            $Weight = isset($row["Weight$index"]) ? (float)$row["Weight$index"] : 0;

            $ArrayHLWeightData[] = [
                "Weight" => $Weight
            ];

            $TotalHLWeight += $Weight;
        }

        $ArrayList['HLWeightData']  = $ArrayHLWeightData;
        $ArrayList['TotalHLWeight'] = $TotalHLWeight;

        $ArrayData[] = $ArrayList;
    }

    return [
        "ArrayData" => $ArrayData
    ];
}

        public function getOverallTotals($RequestBody)
        {
            // $ArrivalDate      = $RequestBody['ArrivalDate'];
            $FromDate         = $RequestBody['FromDate'];
            $ToDate           = $RequestBody['ToDate'];
            $MachineID        = $RequestBody['MachineID'];
            $ReceivingPlantID = $RequestBody['ReceivingPlantID'];
            $MergeHonLoose = $RequestBody['MergeHonLoose'];
            $TypeProduct = $RequestBody['TypeProduct'];
            $GradingDate = $RequestBody['GradingDate'];


            $Query = '';
            if($FromDate && $ToDate){
               $Query = " RmRD.ArrivalDate BETWEEN '$FromDate' AND '$ToDate' "; 
            }elseif($GradingDate){
                $Query = " PHGD.GradingDate = '$GradingDate' "; 
            }else{
                $Query = " 1 ";
            }
            $GroupQuery = '';
            if ($MachineID != '') {
                $Query .= " AND PHGD.MachineID='$MachineID'";
                $GroupQuery .= " ,PHGD.MachineID";
            }
            if ($ReceivingPlantID != '') {
                $Query .= " AND RmRD.ReceivingPlantID='$ReceivingPlantID'";
                $GroupQuery .= " ,RmRD.ReceivingPlantID";
            }

            $TypeProductQuery = "";
            if ($TypeProduct != '') {
                $TypeProductQuery = " AND PHVL.TypeProduct='$TypeProduct'";
            }

            $sqlHLQty = "SELECT SUM(HLWeight) AS HLWeight, GROUP_CONCAT(PHGB.MixedID) AS LotIDs FROM PpcHLGradingBreakup AS PHGB
            LEFT JOIN PpcHLGradingDetails AS PHGD ON PHGD.LotID = PHGB.LotID
            LEFT JOIN RmReceivingDetails AS RmRD ON RmRD.ReceivingID = PHGD.TrackID
            LEFT JOIN PpcHLVAVarietyList AS PHVL ON PHGB.VarietyID=PHVL.VarietyID
            WHERE $Query $TypeProductQuery";

            // Commented Group by while using From & to date
            // GROUP BY RmRD.ArrivalDate $GroupQuery";

            $stmtHLQty = $this->conn->prepare($sqlHLQty);
            $stmtHLQty->execute();
            $dataHLQty = $stmtHLQty->fetch(PDO::FETCH_ASSOC);
            $HLWeight  = $dataHLQty['HLWeight'];
            $LotIDs    = $dataHLQty['LotIDs'];

            if ($MergeHonLoose == 1) {
                // $sqlHonQty = "SELECT SUM(PurchaseWeight) AS PurchaseWeight FROM RmLotDetatils WHERE RmLotID IN ($LotIDs)";
                $sqlHonQty = "SELECT sum(Weight) as PurchaseWeight ,GROUP_CONCAT(RLD.LotNumber) as LotNumber FROM `RmLotDetatils` as RLD
                            LEFT JOIN RmArrivalBreakUpDetails as RABD on RABD.ArrivalID=RLD.RmReferenceID WHERE RmLotID in ($LotIDs)";
            } else {
                $sqlHonQty = "SELECT SUM(PurchaseWeight) AS PurchaseWeight FROM RmLotDetatils WHERE RmLotID IN ($LotIDs)";
            }

            $stmtHonQty = $this->conn->prepare($sqlHonQty);
            $stmtHonQty->execute();
            $dataHonQty = $stmtHonQty->fetch(PDO::FETCH_ASSOC);
            $HonWeight  = $dataHonQty['PurchaseWeight'];

            $sqlTypeProduct = "SELECT SUM(HLWeight) AS HLWeight,PVA.TypeProduct FROM PpcHLGradingBreakup AS PHGB
            LEFT JOIN PpcHLGradingDetails AS PHGD ON PHGD.LotID=PHGB.LotID
            LEFT JOIN RmReceivingDetails AS RmRD ON RmRD.ReceivingID=PHGD.TrackID
            LEFT JOIN PpcHLVAVarietyList AS PVA ON PVA.VarietyID=PHGB.VarietyID
            WHERE $Query
            GROUP BY RmRD.ArrivalDate,PVA.TypeProduct$GroupQuery";
            $stmtTypeProduct = $this->conn->prepare($sqlTypeProduct);
            $stmtTypeProduct->execute();
            $ArrayList['others']  = 0;
            $ArrayList['raw'] = 0;
            $ArrayList['cooked'] = 0;
            while ($dataTypeProduct = $stmtTypeProduct->fetch(PDO::FETCH_ASSOC)) {
                $TypeProdWeight  = $dataTypeProduct['HLWeight'];
                $TypeProduct    = $dataTypeProduct['TypeProduct'];
                if (strtolower($TypeProduct) == "raw") {
                    $ArrayList['raw']  = round((float)$ArrayList['raw'] + (float)$TypeProdWeight, 2);
                } else if (strtolower($TypeProduct) == "cooked") {
                    $ArrayList['cooked']  = round((float)$ArrayList['cooked'] + (float)$TypeProdWeight, 2);
                } else {
                    $ArrayList['others']  = round((float)$ArrayList['others'] + (float)$TypeProdWeight, 2);
                }
            }

            $ArrayList['HLWeight']  = round($HLWeight, 2);
            $ArrayList['HonWeight'] = round($HonWeight, 2);
            $Yield                  = ($HLWeight / $HonWeight) * 100;
            $ArrayList['Yield']     = round($Yield, 2);

            $Data[] = $ArrayList;

            return $Data;
        }
        function getGradingMachinesListByPlantId($ReceivingPlantId){
            if(!empty($ReceivingPlantId)){
                $sqlQuery = "SELECT MachineID,MachineName, UnitID FROM `AdminGradingMachines` WHERE UnitID = '$ReceivingPlantId'";
            
                $stmt = $this->conn->prepare($sqlQuery);
                $stmt->execute();
                while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $Data[] = $dataRow;
                }
                return $Data; 
            }
        }
    }
    
    
} else {
    /* This is old code */

    // ini_set('display_errors', 1);
    // ini_set('display_startup_errors', 1);
    // error_reporting(E_ALL);

    include_once '../Global/Util.php';

    class GradingOverallReportController
    {
        private $conn;
        private $Error = null;
        private $Msg   = '';

        public function __construct($db)
        {
            $this->conn = $db;
        }
        public function getGradingTrackNumbers($RequestBody)
        {
            if(!empty($RequestBody["type"]) && $RequestBody["type"] == 'getGradingMachines'){
                $results = $this->getGradingMachinesListByPlantId($RequestBody['ReceivingPlantId']);
                if(!empty($results)){
                    $Data['Error'] = null;
                    $Data['Msg'] = 'Success';
                    $Data['Data'] = $results;
                }else{
                    $Data['Error'] = 'No Data Found';
                }
                return $Data;
            }
            $FromDate      = $RequestBody['FromDate'];
            $ToDate         = $RequestBody['ToDate'];
            $MachineID        = $RequestBody['MachineID'];
            $ReceivingPlantID = $RequestBody['ReceivingPlantID'];
            $IsPlantFilter    = $RequestBody['IsPlantFilter'];
            $MergeHonLoose    = $RequestBody['MergeHonLoose'];
            $GradingDate = $RequestBody['GradingDate'];

            $Query = '';
            if($FromDate && $ToDate){
               $Query = " RmRD.ArrivalDate BETWEEN '$FromDate' AND '$ToDate' "; 
            }elseif($GradingDate){
                $Query = " PHGD.GradingDate = '$GradingDate' "; 
            }else{
                $Query = " 1 ";
            }
            if ($MachineID != '') {
                $Query .= " AND PHGD.MachineID='$MachineID'";
            }
            if ($ReceivingPlantID != '') {
                $Query .= " AND RmRD.ReceivingPlantID='$ReceivingPlantID'";
            }

            $OrderQuery = '';
            if ($IsPlantFilter == 1) {
                $OrderQuery = ' ORDER BY RmRD.ReceivingID';
            } else {
                $OrderQuery = '';
            }
            $sqlFields = "DISTINCT PHGD.TrackID,RmRD.ArrivalDate,PHGD.MachineID, GradingDate,
                RmRD.TrackCode,RmRD.ReceivingPlantID, MachineName, RmRD.ReceivingID";

            $sqlQuery = "SELECT $sqlFields FROM PpcHLGradingDetails AS PHGD
                LEFT JOIN RmReceivingDetails AS RmRD ON RmRD.ReceivingID = PHGD.TrackID
                LEFT JOIN AdminGradingMachines AS AGD ON AGD.MachineID=PHGD.MachineID
                WHERE $Query GROUP BY RmRD.TrackCode,GradingDate $OrderQuery";

            $stmtSelect = $this->conn->prepare($sqlQuery);
            $stmtSelect->execute();

            $ArrayReceivedData = [];
            $i                 = 0;
            while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
                $TrackID     = $dataRow['TrackID'];
                $GradingDate = $dataRow['GradingDate'];
                $BillNoData  = $this->getBillDetails($TrackID, $GradingDate, $MergeHonLoose);

                $ArrayReceivedDetails['SNo']           = ++$i;
                $ArrayReceivedDetails['ArrivalDate']   = $dataRow['ArrivalDate'] ? date('d-m-Y', strtotime($dataRow['ArrivalDate'])) : '';
                $ArrayReceivedDetails['MachineID']     = $dataRow['MachineID'];
                $ArrayReceivedDetails['MachineName']   = $dataRow['MachineName'];
                $ArrayReceivedDetails['TrackID']       = $dataRow['TrackID'];
                $ArrayReceivedDetails['TrackCode']     = $dataRow['TrackCode'];
                $ArrayReceivedDetails['GradingDate']   = $dataRow['GradingDate'] ? date('d-m-Y', strtotime($dataRow['GradingDate'])) : '';
                $ArrayReceivedDetails['BillDataCount'] = $BillNoData['RowCount'];
                $ArrayReceivedDetails['BillData']      = $BillNoData['BillData'];
                if ($BillNoData['BillData'] != null) {
                    $ArrayReceivedData[] = $ArrayReceivedDetails;
                }
            }

            $VarietyCombinationData = $this->getVarietyWiseData($RequestBody);
            $GrandTotals            = $this->getOverallTotals($RequestBody);

            $Data['Error']                  = null;
            $Data['Msg']                    = 'Success';
            $Data['Data']                   = $ArrayReceivedData;
            $Data['VarietyCombinationData'] = $VarietyCombinationData['ArrayData'];
            $Data['GrandTotals']            = $GrandTotals;

            return $Data;
        }

        public function getBillDetails($TrackID, $GradingDate, $MergeHonLoose)
        {
            $sqlFields = " ArrivalNumber,Count, Weight,HLID as GradingID";
            if ($GradingDate != '') {
                $Qry = " and  GradingDate='$GradingDate'";
            }

            $SqlGradingCount = "SELECT RLD.RmLotID,PHGD.MixIDs,PHGD.IsMix
                FROM RmReceivingBreakup as RRB
                left join PpcHLGradingDetails  as PHGD on PHGD.TrackBreakUpID=RRB.ReceivalBreakupID
                left join RmLotDetatils  as RLD  on RLD.RmLotID=PHGD.LotID
                where ReceivingID='$TrackID'  $Qry  and RmLotID is NOT null group by MixIDs";

            $stmtGradingCount = $this->conn->prepare($SqlGradingCount);
            $stmtGradingCount->execute();
            $BillListCount = $stmtGradingCount->rowCount();

            $ArrayReceivingCount = [];
            while ($dataRowGradingCount = $stmtGradingCount->fetch(PDO::FETCH_ASSOC)) {
                $IsMix = $dataRowGradingCount['IsMix'];

                $LotID  = $dataRowGradingCount['RmLotID'];
                $MixIDs = $dataRowGradingCount['MixIDs'];

                // if ($IsMix == 1) {
                //     $GroupQuery      = " and RHGD.MixIDs in ($MixIDs) group by MixIDs";
                //     $BillNumberField = ",GROUP_CONCAT(ReceivalBreakupID) as ReceivalBreakupID,GROUP_CONCAT(RmLotID)as RmArrivalID, GROUP_CONCAT(ArrivalNumber,' (',round(Count,2),'-',Weight,')' SEPARATOR ',  ') as BillNumber";
                // } else {
                //     $BillNumberField = ",ReceivalBreakupID,RmLotID as RmArrivalID";
                //     $GroupQuery      = " and RLD.RmLotID in ($LotID )";
                // }

                // $sqlQueryReceivingCounts = "SELECT $sqlFields $BillNumberField FROM RmArrivalBreakUpDetails AS RABD
                // LEFT JOIN RmArrivalDetatils AS RAD ON RAD.RmArrivalID=RABD.ArrivalID
                // LEFT JOIN RmReceivingBreakup AS RRB ON RRB.ArrivalBreakupID=RABD.ArrivalBrkUpID
                // LEFT JOIN RmLotDetatils AS RLD ON RLD.BreakUpID=RRB.ArrivalBreakupID
                // LEFT JOIN PpcHLGradingDetails AS RHGD ON RHGD.LotID=RLD.RmLotID
                // WHERE RRB.ReceivingID= '$TrackID' AND ReceivalBreakupID IS NOT NULL $GroupQuery ";

                if ($IsMix == 1) {
                    $GroupQuery = " and RHGD.MixIDs in ($MixIDs) group by MixIDs";
                    if ($MergeHonLoose == 1) {
                        $BillNumberField = ",GROUP_CONCAT(ReceivalBreakupID) as ReceivalBreakupID,GROUP_CONCAT(RmLotID)as RmArrivalID, GROUP_CONCAT(ArrivalNumber,'(',Count ,' - ',SUM(Weight),')') as BillNumber";
                    } else {
                        $BillNumberField = ",GROUP_CONCAT(ReceivalBreakupID) as ReceivalBreakupID,GROUP_CONCAT(RmLotID)as RmArrivalID, GROUP_CONCAT(ArrivalNumber,'(',Count ,' - ',Weight,')') as BillNumber";
                    }
                } else {
                    $BillNumberField = ",ReceivalBreakupID,RmLotID as RmArrivalID";
                    // $GroupQuery = " and RLD.RmLotID='$LotID' ";
                    $GroupQuery = " and RLD.RmLotID in ($LotID )";
                }

                if ($MergeHonLoose == 1) {
                    $sqlFields = " ArrivalNumber,Count, SUM(Weight) AS Weight,HLID as GradingID";

                    $GroupHonQuery = " GROUP BY RAD.ArrivalNumber";
                    $sqlQueryReceivingCounts = "SELECT $sqlFields $BillNumberField FROM RmArrivalBreakUpDetails AS RABD
                LEFT JOIN RmArrivalDetatils AS RAD ON RAD.RmArrivalID=RABD.ArrivalID
                LEFT JOIN RmReceivingBreakup AS RRB ON RRB.ArrivalID=RABD.ArrivalID
                LEFT JOIN RmLotDetatils AS RLD ON RLD.BreakUpID=RRB.ArrivalBreakupID
                LEFT JOIN PpcHLGradingDetails AS RHGD ON RHGD.LotID=RLD.RmLotID
                WHERE RRB.ReceivingID= '$TrackID' AND ReceivalBreakupID IS NOT NULL $GroupQuery $GroupHonQuery";
                } else {
                    $sqlFields = " ArrivalNumber,Count, Weight,HLID as GradingID";
                    $sqlQueryReceivingCounts = "SELECT $sqlFields $BillNumberField FROM RmArrivalBreakUpDetails AS RABD
                LEFT JOIN RmArrivalDetatils AS RAD ON RAD.RmArrivalID=RABD.ArrivalID
                LEFT JOIN RmReceivingBreakup AS RRB ON RRB.ArrivalBreakupID=RABD.ArrivalBrkUpID
                LEFT JOIN RmLotDetatils AS RLD ON RLD.BreakUpID=RRB.ArrivalBreakupID
                LEFT JOIN PpcHLGradingDetails AS RHGD ON RHGD.LotID=RLD.RmLotID
                WHERE RRB.ReceivingID= '$TrackID' AND ReceivalBreakupID IS NOT NULL $GroupQuery ";
                }
                $stmtReceivingCounts = $this->conn->prepare($sqlQueryReceivingCounts);
                $stmtReceivingCounts->execute();
                $dataRowReceivingCounts = $stmtReceivingCounts->fetch(PDO::FETCH_ASSOC);
                $ReceivingCount         = array();

                $ReceivingCount['ReceivalBreakupID'] = $dataRowReceivingCounts['ReceivalBreakupID'];
                $ReceivingCount['ArrivalID']         = $dataRowReceivingCounts['RmArrivalID'];
                if ($IsMix == 0) {
                    $ReceivingCount['BillNumber'] = $dataRowReceivingCounts['ArrivalNumber'] . ' (' . round($dataRowReceivingCounts['Count'], 2) . '-' . $dataRowReceivingCounts['Weight'] . ')';
                } else {
                    $ReceivingCount['BillNumber'] = $dataRowReceivingCounts['BillNumber'];
                }

                $ArrayReceivingCount[] = $ReceivingCount;
            }
            $data['RowCount'] = $BillListCount;
            $data['BillData'] = $ArrayReceivingCount;

            return $data;
        }

        public function getVarietyWiseData($RequestBody)
        {
            // $ArrivalDate      = $RequestBody['ArrivalDate'];
            $FromDate         = $RequestBody['FromDate'];
            $ToDate           = $RequestBody['ToDate'];
            $MachineID        = $RequestBody['MachineID'];
            $ReceivingPlantID = $RequestBody['ReceivingPlantID'];
            $MergeHonLoose = $RequestBody['MergeHonLoose'];
            $GradingDate = $RequestBody['GradingDate'];

            $Query = '';
            if($FromDate && $ToDate){
               $Query = " RmRD.ArrivalDate BETWEEN '$FromDate' AND '$ToDate' "; 
            }elseif($GradingDate){
                $Query = " PHGD.GradingDate = '$GradingDate' "; 
            }else{
                $Query = " 1 ";
            }
            if ($MachineID != '') {
                $Query .= " AND PHGD.MachineID='$MachineID'";
            }
            if ($ReceivingPlantID != '') {
                $Query .= " AND RmRD.ReceivingPlantID='$ReceivingPlantID'";
            }

            $sqlTrackID = "SELECT DISTINCT PHGD.TrackID,GradingDate FROM PpcHLGradingDetails AS PHGD
                LEFT JOIN RmReceivingDetails AS RmRD ON RmRD.ReceivingID = PHGD.TrackID
                LEFT JOIN AdminGradingMachines AS AGD ON AGD.MachineID=PHGD.MachineID
                WHERE $Query";
            $stmtTrackID = $this->conn->prepare($sqlTrackID);
            $stmtTrackID->execute();

            $combinedArrivalIDs = [];

            while ($rowTrackID = $stmtTrackID->fetch(PDO::FETCH_ASSOC)) {
                $TrackID     = $rowTrackID['TrackID'];
                $GradingDate = $rowTrackID['GradingDate'];
                $BillNoData  = $this->getBillDetails($TrackID, $GradingDate, $MergeHonLoose);

                if (isset($BillNoData['BillData'])) {
                    foreach ($BillNoData['BillData'] as $bill) {
                        $combinedArrivalIDs[] = $bill['ArrivalID'];
                    }
                }
            }
            $sqlFields = "PHGB.VarietyID, HLCount, PackingGrade,ShortName, PHGB.Treatment,PHGD.TrackID,PHVL.Species";
            $sqlQuery  = "SELECT $sqlFields FROM PpcHLGradingBreakup AS PHGB
                LEFT JOIN PpcHLVAVarietyList AS PHVL ON PHGB.VarietyID=PHVL.VarietyID
                LEFT JOIN PpcHLGradingDetails AS PHGD ON PHGD.LotID = PHGB.LotID
                LEFT JOIN RmReceivingDetails AS RmRD ON RmRD.ReceivingID = PHGD.TrackID
                WHERE $Query GROUP BY PHGB.VarietyID, HLCount, PackingGrade, PHGB.Treatment order by HLCount asc";
            $stmtSelect = $this->conn->prepare($sqlQuery);
            $stmtSelect->execute();

            $ArrayData     = [];
            $i             = 0;
            $TotalHLWeight = 0;

            while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

                $ArrayList['SNO']          = ++$i;
                $ArrayList['VarietyID']    = $dataRow['VarietyID'];
                $ArrayList['HLCount']      = $dataRow['HLCount'];
                $ArrayList['PackingGrade'] = $dataRow['PackingGrade'];
                $ArrayList['ShortName']    = $dataRow['ShortName'] . "-" . $dataRow['Species'];
                $ArrayList['Treatment']    = $dataRow['Treatment'];

                $VarietyID    = $dataRow['VarietyID'];
                $HLCount      = $dataRow['HLCount'];
                $PackingGrade = $dataRow['PackingGrade'];
                $Treatment    = $dataRow['Treatment'];
                $TrackID      = $dataRow['TrackID'];

                $ArrayHLWeightData           = [];
                $ArrayTotalHLWeightTrackwise = [];
                $TotalHLWeight               = 0;
                $TotalHLWeightTrackwise      = 0;
                foreach ($combinedArrivalIDs as $idGroup) {

                    $sqlHLWeightTrackwise = "SELECT SUM(HLWeight) AS TrackwiseWeight FROM PpcHLGradingBreakup AS PHGB
                        WHERE  PHGB.MixedID IN ($idGroup)";
                    $stmtHLWeightTrackwise = $this->conn->prepare($sqlHLWeightTrackwise);
                    $stmtHLWeightTrackwise->execute();
                    while ($rowHLWeightTrackwise = $stmtHLWeightTrackwise->fetch(PDO::FETCH_ASSOC)) {
                        $ArrayTotalHLWeightTrackwiseList['HlWeight'] = $rowHLWeightTrackwise['TrackwiseWeight'] ? $rowHLWeightTrackwise['TrackwiseWeight'] : '0.00';
                    }

                    if ($MergeHonLoose == 1) {
                        // $sqlHonQty = "SELECT SUM(PurchaseWeight) AS PurchaseWeight FROM RmLotDetatils WHERE RmLotID IN ($LotIDs)";
                        $sqlHONQuery = "SELECT sum(Weight) as PurchaseWeight ,GROUP_CONCAT(RLD.LotNumber) as LotNumber FROM `RmLotDetatils` as RLD
                                        LEFT JOIN RmArrivalBreakUpDetails as RABD on RABD.ArrivalID=RLD.RmReferenceID WHERE RmLotID in ($idGroup)";
                    } else {
                        $sqlHONQuery = "SELECT sum(Weight) as HonWeight FROM `RmLotDetatils` as RLD
                                    LEFT JOIN RmArrivalBreakUpDetails as RABD on RABD.ArrivalBrkUpID=RLD.BreakUpID WHERE RmLotID in ($idGroup)";
                    }

                    $stmtHON = $this->conn->prepare($sqlHONQuery);
                    $stmtHON->execute();
                    while ($rowHONWeight = $stmtHON->fetch(PDO::FETCH_ASSOC)) {
                        $ArrayTotalHLWeightTrackwiseList['HonWeight'] = $rowHONWeight['HonWeight'] ? $rowHONWeight['HonWeight'] : '0.00';
                    }
                    $TotalYeild                               = ($ArrayTotalHLWeightTrackwiseList['HlWeight'] / $ArrayTotalHLWeightTrackwiseList['HonWeight']) * 100;
                    $ArrayTotalHLWeightTrackwiseList['Yield'] = round($TotalYeild, 2);
                    $ArrayTotalHLWeightTrackwise[]            = $ArrayTotalHLWeightTrackwiseList;
                    $sqlHLWeight                              = "SELECT SUM(HLWeight) AS Weight FROM PpcHLGradingBreakup AS PHGB
                        LEFT JOIN PpcHLVAVarietyList AS PHVL ON PHGB.VarietyID=PHVL.VarietyID
                        LEFT JOIN PpcHLGradingDetails AS PHGD ON PHGD.LotID = PHGB.LotID
                        LEFT JOIN RmReceivingDetails AS RmRD ON RmRD.ReceivingID = PHGD.TrackID
                        WHERE PHGB.VarietyID='$VarietyID' AND HLCount='$HLCount' AND PackingGrade='$PackingGrade'
                        AND PHGB.Treatment='$Treatment' AND PHGB.MixedID IN ($idGroup)";
                    $stmtHLWeight = $this->conn->prepare($sqlHLWeight);
                    $stmtHLWeight->execute();

                    while ($rowHLWeight = $stmtHLWeight->fetch(PDO::FETCH_ASSOC)) {

                        $Weight                 = $rowHLWeight['Weight'] ? $rowHLWeight['Weight'] : 0;
                        $HLWeightList['Weight'] = $Weight;
                        $HLWeightList['qry']    = $sqlHLWeight;

                        $TotalHLWeight += $Weight;
                        $ArrayHLWeightData[] = $HLWeightList;
                    }
                }

                $ArrayList['HLWeightData']           = $ArrayHLWeightData;
                $ArrayList['TotalHLWeight']          = $TotalHLWeight;
                $ArrayList['TotalHLWeightTrackwise'] = $ArrayTotalHLWeightTrackwise;

                $ArrayData[] = $ArrayList;
            }

            $Data['ArrayData'] = $ArrayData;
            return $Data;
        }

        // function getOverallTotals($RequestBody)
        // {
        //     $ArrivalDate = $RequestBody['ArrivalDate'];
        //     $MachineID = $RequestBody['MachineID'];
        //     $ReceivingPlantID = $RequestBody['ReceivingPlantID'];

        //     $Query = '';
        //     if ($MachineID != '') {
        //         $Query .= " AND PHGD.MachineID='$MachineID'";
        //     }
        //     if ($ReceivingPlantID != '') {
        //         $Query .= " AND RmRD.ReceivingPlantID='$ReceivingPlantID'";
        //     }

        //     $sqlSelect = "SELECT SUM(HLWeight) AS HLWeight,(SELECT SUM(Weight) as HonWeight FROM RmReceivingBreakup AS RmRB
        //     LEFT JOIN RmArrivalBreakUpDetails AS RABD ON RmRB.ArrivalBreakupID = RABD.ArrivalBrkUpID
        //     LEFT JOIN RmReceivingDetails AS RmRD ON RmRD.ReceivingID = RmRB.ReceivingID
        //     WHERE RmRD.ArrivalDate='$ArrivalDate' AND RmRD.ReceivingPlantID='$ReceivingPlantID') as HOnWeight
        //     FROM PpcHLGradingBreakup AS PHGB
        //     LEFT JOIN PpcHLGradingDetails AS PHGD ON PHGD.LotID = PHGB.LotID
        //     LEFT JOIN RmReceivingDetails AS RmRD ON RmRD.ReceivingID = PHGD.TrackID
        //     WHERE RmRD.ArrivalDate='$ArrivalDate' $Query";
        //     $stmtSelect = $this->conn->prepare($sqlSelect);
        //     $stmtSelect->execute();
        //     $dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC);
        //     $HonWeight = $dataRow['HOnWeight'];
        //     $HLWeight = $dataRow['HLWeight'];

        //     $ArrayList['HonWeight'] = round($HonWeight, 2);
        //     $ArrayList['HLWeight'] = round($HLWeight, 2);
        //     $Yield = ($HLWeight / $HonWeight) * 100;
        //     $ArrayList['Yield'] = round($Yield, 2);

        //     $Data[] = $ArrayList;

        //     return $Data;
        // }

        public function getOverallTotals($RequestBody)
        {
            // $ArrivalDate      = $RequestBody['ArrivalDate'];
            $FromDate         = $RequestBody['FromDate'];
            $ToDate           = $RequestBody['ToDate'];
            $MachineID        = $RequestBody['MachineID'];
            $ReceivingPlantID = $RequestBody['ReceivingPlantID'];
            $MergeHonLoose = $RequestBody['MergeHonLoose'];
            $TypeProduct = $RequestBody['TypeProduct'];
            $GradingDate = $RequestBody['GradingDate'];

            $Query = '';
            if($FromDate && $ToDate){
               $Query = " RmRD.ArrivalDate BETWEEN '$FromDate' AND '$ToDate' "; 
            }elseif($GradingDate){
                $Query = " PHGD.GradingDate = '$GradingDate' "; 
            }else{
                $Query = " 1 ";
            }
            $GroupQuery = '';
            if ($MachineID != '') {
                $Query .= " AND PHGD.MachineID='$MachineID'";
                $GroupQuery .= " ,PHGD.MachineID";
            }
            if ($ReceivingPlantID != '') {
                $Query .= " AND RmRD.ReceivingPlantID='$ReceivingPlantID'";
                $GroupQuery .= " ,RmRD.ReceivingPlantID";
            }

            $TypeProductQuery = "";
            if ($TypeProduct != '') {
                $TypeProductQuery = " AND PHVL.TypeProduct='$TypeProduct'";
            }

            $sqlHLQty = "SELECT SUM(HLWeight) AS HLWeight, GROUP_CONCAT(PHGB.MixedID) AS LotIDs FROM PpcHLGradingBreakup AS PHGB
                LEFT JOIN PpcHLGradingDetails AS PHGD ON PHGD.LotID = PHGB.LotID
                LEFT JOIN RmReceivingDetails AS RmRD ON RmRD.ReceivingID = PHGD.TrackID
                 LEFT JOIN PpcHLVAVarietyList AS PHVL ON PHGB.VarietyID=PHVL.VarietyID
                WHERE $Query $TypeProductQuery
                GROUP BY RmRD.ArrivalDate $GroupQuery";
            $stmtHLQty = $this->conn->prepare($sqlHLQty);
            $stmtHLQty->execute();
            $dataHLQty = $stmtHLQty->fetch(PDO::FETCH_ASSOC);
            $HLWeight  = $dataHLQty['HLWeight'];
            $LotIDs    = $dataHLQty['LotIDs'];

            if ($MergeHonLoose == 1) {
                // $sqlHonQty = "SELECT SUM(PurchaseWeight) AS PurchaseWeight FROM RmLotDetatils WHERE RmLotID IN ($LotIDs)";
                $sqlHonQty = "SELECT sum(Weight) as PurchaseWeight ,GROUP_CONCAT(RLD.LotNumber) as LotNumber FROM `RmLotDetatils` as RLD
                                LEFT JOIN RmArrivalBreakUpDetails as RABD on RABD.ArrivalID=RLD.RmReferenceID WHERE RmLotID in ($LotIDs)";
            } else {
                $sqlHonQty = "SELECT SUM(PurchaseWeight) AS PurchaseWeight FROM RmLotDetatils WHERE RmLotID IN ($LotIDs)";
            }

            $stmtHonQty = $this->conn->prepare($sqlHonQty);
            $stmtHonQty->execute();
            $dataHonQty = $stmtHonQty->fetch(PDO::FETCH_ASSOC);
            $HonWeight  = $dataHonQty['PurchaseWeight'];

            $ArrayList['HLWeight']  = round($HLWeight, 2);
            $ArrayList['HonWeight'] = round($HonWeight, 2);
            $Yield                  = ($HLWeight / $HonWeight) * 100;
            $ArrayList['Yield']     = round($Yield, 2);

            $Data[] = $ArrayList;

            return $Data;
        }
        function getGradingMachinesListByPlantId($ReceivingPlantId){
            if(!empty($ReceivingPlantId)){
                $sqlQuery = "SELECT MachineID,MachineName, UnitID FROM `AdminGradingMachines` WHERE UnitID = '$ReceivingPlantId'";
            
                $stmt = $this->conn->prepare($sqlQuery);
                $stmt->execute();
                while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    $Data[] = $dataRow;
                }
                return $Data; 
            }
        }
    }
}
