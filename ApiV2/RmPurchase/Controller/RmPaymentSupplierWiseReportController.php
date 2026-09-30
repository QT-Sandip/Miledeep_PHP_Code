<?php
include_once '../Global/Util.php';
include_once 'UtilController.php';
class RmPaymentSupplierWiseReportController
{
    private $conn;
    private $db_table = "RmArrivalDetatils";
    private $utilController;
    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
    }
    // GET ALL
    public function GetRmPaymentSupplierWiseReportDetails($RequestBody)
    {
        $Plant = $RequestBody['Plant'];
        $CenterID = $RequestBody['CenterID'];
        $StartDate = $RequestBody['StartDate'];
        $SupplierID = $RequestBody['SupplierID'];
        $ReqAgentID = $RequestBody['AgentID'];
        $ReportType = $RequestBody['ReportType'];
        $Type = $RequestBody['Type'];
        $DateFilter = "  BillDate <='$StartDate' AND BillDate >= '2023-01-01' ";



        $Query = '';

        if ($CenterID != '') {
            $Query .= " and ArrivalStationID='$CenterID'";
        }

        if ($Plant != '') {
            $Query .= " and ProcessingStationID='$Plant'";
        }

        if ($SupplierID != '') {
            if($StartDate >='2026-04-01'){
                $query = "SELECT GROUP_CONCAT(ID) as ids FROM RmSuppliers WHERE PanNumber = ( SELECT PanNumber FROM RmSuppliers WHERE ID = '$SupplierID' ) ";
                $res = $this->conn->prepare($query);
                $res->execute();
                $row = $res->fetch(PDO::FETCH_ASSOC);
                $Query .= " and RmSupplierID in(".$row['ids'].") ";
            }else{
                $Query .= " and RmSupplierID='$SupplierID'";
            }

        }
      if ($ReqAgentID != '') {
            $Query .= " and AgentID='$ReqAgentID'";

        }

        if ($Type == '1' && $ReportType == 'M') {
            $Groupby = " and RAD.AgentID!=0 group by RAD.AgentID";
        } else if ($Type == '2' && $ReportType == 'M') {
            $Groupby = " group by RAD.RmSupplierID";
        }
        // else if ($Type == '1' && $ReportType == 'B') {
        //     $Groupby = " and RAD.AgentID!=0 and RBD.HarvestAmount > 0 group by RAD.AgentID";
        // }

        else if ($Type == '1' && $ReportType == 'B') {
            $Groupby = " and RAD.AgentID!=0  group by RAD.AgentID";

        } else {
            $Groupby = "  group by RAD.RmSupplierID, RAD.ArrivalStationID";
        }


        $sqlSuppliersID = "SELECT  RmSupplierID as SupplierID,AgentID,ArrivalStationID FROM `RmBillDetails` as RPD
        left join RmArrivalDetatils as RAD on RAD.RmArrivalID=RPD.ArrivalID

         WHERE $DateFilter and RAD.LotStatus='1' $Query   $Groupby
        order by ArrivalStationID,ArrivalNumber,BillDate  asc";


        $stmtSuppliersID = $this->conn->prepare($sqlSuppliersID);

        $stmtSuppliersID->execute();


        $ArrayReportData = array();

        while ($dataRow = $stmtSuppliersID->fetch(PDO::FETCH_ASSOC)) {
            $newQuery = $Query;
            if ($CenterID == '') {
                $ArrivalStationID = $dataRow['ArrivalStationID'];
                $newQuery .= " and ArrivalStationID='$ArrivalStationID'";
            }
            $ArrayReportDataTemp = $this->getSupplierWiseData($DateFilter, $newQuery, $dataRow, $StartDate, $ReportType, $Type, $Groupby);
            foreach ($ArrayReportDataTemp as $Item) {
                if ($ReportType == "M" && $Item['SNO'] != '') {
                    $Sno = count($ArrayReportData);
                    $Item['SNO'] = ++$Sno;
                }
                $ArrayReportData[] = $Item;
                $GTotalOSAmount += $Item['OSAmount'];
                $GTotalBillAmount += $Item['BillAmount'];
                $GTotalPaidAmount += $Item['PaidAmount'];
            }

        }
        $ArrayTotals['TotalOSAmount'] = round($GTotalOSAmount, 2);
        $ArrayTotals['TotalBillAmount'] = round($GTotalBillAmount, 2);
        $ArrayTotals['TotalPaidAmount'] = number_format($GTotalPaidAmount, 2);

        $Data['PaymentCenterwiseReportData'] = $ArrayReportData;
        $Data['Totals'] = $ArrayTotals;
        return $Data;
    }
    public function getSupplierWiseData($DateFilter, $Query, $suppllierRow, $StartDate, $ReportType, $Type, $Groupby)
    {
        $SupplierID = $suppllierRow['SupplierID'];
        $FinancierName = $suppllierRow['FinancierName'];
        $PanNumber = $suppllierRow['PanNumber'];
        $sqlRmArrivalQuery = $this->getQuery($DateFilter, $Query, $suppllierRow, $StartDate, $ReportType, $Type, $Groupby);
        $stmt = $this->conn->prepare($sqlRmArrivalQuery);
        $stmt->execute();
        $SubTotal = 0;
        $TotalPaidAmount = 0;
        $TotalBillAmount = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($CenterID == '') {
                $AryPurchaseCenters = $this->utilController->getRmPurchaseCenters($dataRow['ArrivalStationID']);
            }
            $supplierOutstandingAmount = 0;
            $agentOutstandingAmount = 0;
            $ArrivalIDs = $dataRow['RmArrivalID'];
            $AgentID = $dataRow['AgentID'];
            $supplierPaidAmount = $dataRow['SPaidAmount'];
            $agentPaidAmount = $dataRow['APaidAmount'];
            if ($ReportType == "B") {
                $DisplaySubtotal = 1;
            } else {
                $DisplaySubtotal = 0;
            }
            $BilNumber = $dataRow['ArrivalNumber'];// . '-' . $ArrivalIDs;
            $ArySupplierHarvestAmount = $this->getSupplierHarvestAmount($ArrivalIDs);
            $AryAgentHarvestAmount = $this->getAgentHarvestAmount($ArrivalIDs, $AgentID);
            $SupplierHarvestAmountExTds = $ArySupplierHarvestAmount['HarvestAmount'] - $ArySupplierHarvestAmount['HarvestTds'];
            $AgentHarvestAmountExTds = $AryAgentHarvestAmount['HarvestAmount'] - $AryAgentHarvestAmount['HarvestTds'] - $AryAgentHarvestAmount['HarvestCommision'];
            $supplierBillAmountExTds = $dataRow['BillAmount'] - $dataRow['BillTds'];
            $supplierBillValueExTds = $supplierBillAmountExTds + $SupplierHarvestAmountExTds;
            $agentBillValueExTds = $AgentHarvestAmountExTds - $dataRow['HarvestTds'];
            $supplierOutstandingAmount = round($supplierBillValueExTds - $supplierPaidAmount, 2);
            $agentOutstandingAmount = $agentBillValueExTds - $agentPaidAmount;
            if ($AgentID == 0) {
                $OutstandingAmountSubTotal = $supplierOutstandingAmount;
            } else

                if ($Type == 1) {
                    $OutstandingAmountSubTotal = $agentOutstandingAmount;//+$agentOutstandingAmount;
                } else if ($Type == 2) {
                    $OutstandingAmountSubTotal = $supplierOutstandingAmount;
                } else if ($Type == "") {
                    $OutstandingAmountSubTotal = $supplierOutstandingAmount + $agentOutstandingAmount;

                }
            if (round($supplierOutstandingAmount, 2) > 0) {
                $TotalBillAmount += $ArrayBookingList['BillAmount'];
                $TotalPaidAmount += $dataRow['PaidAmount'];
                $SubTotal += $OutstandingAmountSubTotal;
            } else if (round($agentOutstandingAmount, 2) > 0) {
                $TotalBillAmount += $ArrayBookingList['BillAmount'];
                $TotalPaidAmount += $dataRow['PaidAmount'];
                $SubTotal += $OutstandingAmountSubTotal;
            }
            if ($Type == "") {
                $ArrayBookingList['SNO'] = ++$i;
                $ArrayBookingList['Supplier'] = $dataRow['FinancierName'];
                $ArrayBookingList['Center'] = $AryPurchaseCenters['StationName'];
                $ArrayBookingList['PanNumber'] = $dataRow['PanNumber'];
                $ArrayBookingList['GstNo'] = $dataRow['GstNo'];
                $ArrayBookingList['BillDate'] = $this->utilController->getFormatedDateEmpty($dataRow['BillDate']);
                $ArrayBookingList['BillNo'] = $BilNumber;
                $ArrayBookingList['BillAmount'] = sprintf('%0.2f', round($supplierBillValueExTds, 2));
                $ArrayBookingList['PaidAmount'] = sprintf('%0.2f', round($supplierPaidAmount, 2));
                $ArrayBookingList['OSAmount'] = sprintf('%0.2f', round($supplierOutstandingAmount, 2));
                if (round($supplierOutstandingAmount, 2) > 0) {
                    $ArrayReportData[] = $ArrayBookingList;
                }

            } else if ($Type != 1) {

                $ArrayBookingList['SNO'] = ++$i;
                $ArrayBookingList['Supplier'] = $dataRow['FinancierName'];
                $ArrayBookingList['Center'] = $AryPurchaseCenters['StationName'];
                $ArrayBookingList['PanNumber'] = $dataRow['PanNumber'];
                $ArrayBookingList['GstNo'] = $dataRow['GstNo'];
                $ArrayBookingList['BillDate'] = $this->utilController->getFormatedDateEmpty($dataRow['BillDate']);
                $ArrayBookingList['BillNo'] = $BilNumber;
                $ArrayBookingList['BillAmount'] = sprintf('%0.2f', round($supplierBillValueExTds, 2));
                $ArrayBookingList['PaidAmount'] = sprintf('%0.2f', round($supplierPaidAmount, 2));
                $ArrayBookingList['OSAmount'] = sprintf('%0.2f', round($supplierOutstandingAmount, 2));
                if (round($supplierOutstandingAmount, 2) > 0) {
                    $ArrayReportData[] = $ArrayBookingList;
                }

            }

            if ($Type != 2) {
                if ($AgentID != '' && $AgentID != 0) {
                    if ($agentOutstandingAmount > 0) {
                        $ArrayBookingList = array();
                        if ($Type == '') {
                            $SNo = ' ';
                        } else {
                            $SNo = ++$i;
                        }
                        $ArrayBookingList['SNO'] = $SNo;
                        $ArrayBookingList['OSAmount'] = sprintf('%0.2f', round($agentOutstandingAmount, 2));
                        $ArrayBookingList['BillAmount'] = sprintf('%0.2f', round($agentBillValueExTds, 2));
                        $ArrayBookingList['BillDate'] = $this->utilController->getFormatedDateEmpty($dataRow['BillDate']);
                        $ArrayBookingList['PaidAmount'] = sprintf('%0.2f', round($agentPaidAmount, 2));
                        $ArrayBookingList['BillNo'] = $BilNumber;
                        $AryAgent = $this->utilController->getAgentName($AgentID);
                        $ArrayBookingList['Supplier'] = $AryAgent['UserName'];
                        $ArrayBookingList['PanNumber'] = $AryAgent['PanNumber'];
                        $ArrayBookingList['GstNo'] = $AryAgent['GstNo'];
                        $ArrayBookingList['Center'] = $AryPurchaseCenters['StationName'];
                        $ArrayReportData[] = $ArrayBookingList;
                    }
                }
            }

        }


        /********************To Diplay Sub Total*************************** */
        if ($DisplaySubtotal == 1) {
            $ArrayBookingList = array();
            $ArrayBookingList['SNO'] = '';
            $ArrayBookingList['Supplier'] = "";
            $ArrayBookingList['SubTotal'] = sprintf('%0.2f', round($SubTotal, 2));
            $ArrayReportData[] = $ArrayBookingList;
            $SubTotal = 0;
        }

        /********************To Diplay Sub Total*************************** */
        return $ArrayReportData;

    }
    function getSupplierHarvestAmount($ArrivalID)
    {
        $SqlHar = "SELECT
     sum(HarvestAmount) as  HarvestAmount ,
     sum(HarvestTds) as HarvestTds
     FROM `RmBillDetails` WHERE  RmBillDetails.ArrivalID in ($ArrivalID) and BillAgentID=0 ";
        $stmt = $this->conn->prepare($SqlHar);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        return $dataRow;
    }

    function getAgentHarvestAmount($ArrivalID, $AgentID)
    {
        $SqlHar = "SELECT sum(HarvestAmount) as  HarvestAmount,sum(HarvestTds) as HarvestTds,sum(HarvestCommision) as HarvestCommision FROM `RmBillDetails` WHERE  RmBillDetails.ArrivalID
      in ($ArrivalID) and BillAgentID in ($AgentID) group by BillAgentID ";
        $stmt = $this->conn->prepare($SqlHar);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        return $dataRow;
    }
    function getQuery($DateFilter, $Query, $suppllierRow, $StartDate, $ReportType, $Type, $Groupby)
    {

        $SupplierID = $suppllierRow['SupplierID'];
        $AgentID = $suppllierRow['AgentID'];

        if ($Type == '1' && $ReportType == 'M') {
            $intQry = " and  RAD.AgentID in ($AgentID)";
        } else if ($Type == '1' && $ReportType == 'B') {
            $intQry = " and  RAD.AgentID in ($AgentID)";
        } else if ($Type == '' && $ReportType == 'M') {
            $intQry = " and RAD.RmSupplierID in ($SupplierID) ";
        } else if ($Type == '2' && $ReportType == 'M') {
            $intQry = " and RAD.RmSupplierID in ($SupplierID)";
        } else {
            $intQry = " and RAD.RmSupplierID in ($SupplierID)";
        }

        $ReportType;

        if ($ReportType == 'M') {

            // $sqlArrivalID = "SELECT  GROUP_CONCAT(RmArrivalID) RmArrivalID
            //  FROM `RmArrivalDetatils` as RAD  WHERE RAD.BillDate <= '$StartDate'  $intQry  $Query
            //  $Groupby ";

            $sqlArrivalID = "SELECT  GROUP_CONCAT(RmArrivalID) RmArrivalID
                 FROM `RmArrivalDetatils` as RAD  WHERE RAD.BillDate <= '$StartDate' AND RAD.BillDate >= '2023-01-01'  $intQry  $Query
                 $Groupby  order by ArrivalStationID,ArrivalNumber,BillDate asc";
            $stmt = $this->conn->prepare($sqlArrivalID);
            $stmt->execute();
            $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
            $RmArrivalID = $dataRow['RmArrivalID'];

            $sqlRmArrivalQuery = "SELECT  GROUP_CONCAT(RmArrivalID) RmArrivalID ,GROUP_CONCAT(RAD.AgentID) as AgentID,RAD.BillDate,
                     RAD.RmSupplierID,
                RAD.ArrivalNumber,RSD.FinancierName,RSD.GstNo,RSD.PanNumber,RAD.ArrivalNumber,RAD.ArrivalStationID,
                sum(RBD.BillAmount) as BillAmount,
                sum(RBD.HarvestAmount) as HarvestAmount,
                sum(RBD.HarvestTds) as HarvestTdss,
                sum(RBD.BillTds) as BillTds ,


                (sum(RBD.BillAmount)+sum(RBD.HarvestAmount))-(sum(RBD.BillTds)+sum(RBD.HarvestTds)) as TotalOsAmount,



                 (SELECT COALESCE(sum(RPBD.Amount),0) FROM RmPaymentBreakup as RPBD
                 left join RmPaymentDetails as RPD on RPD.PaymentID=RPBD.PaymentID where
                 ArrivalID in ( $RmArrivalID) and RPD.Date   <= '$StartDate' AND RPD.Date >= '2023-01-01') as PaidAmount,
                 (SELECT COALESCE(sum(RPBD.Amount),0) FROM RmPaymentBreakup as RPBD
                 left join RmPaymentDetails as RPD on RPD.PaymentID=RPBD.PaymentID where RPBD.BillerType=2
                 and ArrivalID in ( $RmArrivalID) and RPD.Date   <= '$StartDate'  AND RPD.Date >= '2023-01-01') as SPaidAmount,
                 (SELECT COALESCE(sum(RPBD.Amount),0) FROM RmPaymentBreakup as RPBD
                 left join RmPaymentDetails as RPD on RPD.PaymentID=RPBD.PaymentID where RPBD.BillerType=1
                 and ArrivalID in ( $RmArrivalID) and RPD.Date   <= '$StartDate'  AND RPD.Date >= '2023-01-01') as APaidAmount
                 FROM `RmArrivalDetatils` as RAD
                 left join RmSuppliers as RSD on RAD.RmSupplierID=RSD.ID
                 left join RmBillDetails as RBD on RAD.RmArrivalID=RBD.ArrivalID

                 WHERE RAD.BillDate <= '$StartDate'  AND RAD.BillDate >= '2023-01-01'  $intQry  $Query
                 $Groupby  HAVING PaidAmount < TotalOsAmount order by ArrivalStationID,ArrivalNumber,BillDate  asc";  //and  RAD.RmSupplierID =$SupplierID

        } else {

            $sqlRmArrivalQuery = "SELECT
                RAD.RmArrivalID,RAD.ArrivalStationID,RAD.BillDate,RAD.RmSupplierID, RAD.AgentID,
                RAD.ArrivalNumber,RSD.FinancierName,RSD.PanNumber,
                RAD.ArrivalNumber,RBD.BillAmount,RBD.BillTds ,
                max((RBD.BillAmount+RBD.HarvestAmount)-(RBD.BillTds+RBD.HarvestTds)) as BExtd,
                (SELECT COALESCE(sum(RPBD.Amount),0) FROM RmPaymentBreakup as RPBD
                 left join RmPaymentDetails as RPD on RPD.PaymentID=RPBD.PaymentID where
                 ArrivalID=RBD.ArrivalID and RPD.Date   <= '$StartDate' AND RPD.Date >= '2023-01-01') as PaidAmount,
                 (SELECT COALESCE(sum(RPBD.Amount),0) FROM RmPaymentBreakup as RPBD
                 left join RmPaymentDetails as RPD on RPD.PaymentID=RPBD.PaymentID where RPBD.BillerType=2
                 and ArrivalID=RBD.ArrivalID and RPD.Date   <= '$StartDate' AND RPD.Date >= '2023-01-01') as SPaidAmount,
                 (SELECT COALESCE(sum(RPBD.Amount),0) FROM RmPaymentBreakup as RPBD
                 left join RmPaymentDetails as RPD on RPD.PaymentID=RPBD.PaymentID where RPBD.BillerType=1
                 and ArrivalID=RBD.ArrivalID and RPD.Date   <= '$StartDate' AND RPD.Date >= '2023-01-01') as APaidAmount
                FROM RmArrivalDetatils as RAD
                left join RmSuppliers as RSD on RAD.RmSupplierID=RSD.ID
                left join RmBillDetails as RBD on RAD.RmArrivalID=RBD.ArrivalID

                WHERE RAD.BillDate <= '$StartDate' AND RAD.BillDate >= '2023-01-01'  $intQry  $Query
                group by RAD.RmArrivalID HAVING PaidAmount < BExtd order by ArrivalStationID,ArrivalNumber,BillDate  asc"; //and  RAD.RmSupplierID =$SupplierID 

        }

        return $sqlRmArrivalQuery;

    }

}
