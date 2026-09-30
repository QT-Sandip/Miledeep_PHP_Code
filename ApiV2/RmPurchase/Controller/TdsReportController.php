<?php

include_once '../Global/Util.php';
include_once 'UtilController.php';

class TdsReportController
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
    public function GetRmPurchseReportDetails($RequestBody)
    {
        $CenterID = $RequestBody['CenterID'];
        $SupplierID = $RequestBody['SupplierID'];
        $StartDate = $RequestBody['StartDate'];
        $EndDate = $RequestBody['EndDate'];
        $Plant = $RequestBody['Plant'];
        $CompanyType = $RequestBody['CompanyType'];
        $ReqAgentID = $RequestBody['AgentID'];
        $ReportType = $RequestBody['ReportType'];
        $Type = $RequestBody['Type'];

        $DateFilter = "between '$StartDate' and '$EndDate'";

        $Query = '';
        $Groupby = '';
        $Orderby = '';
        if ($CenterID != '') {
            $AryPurchaseCenters = $this->utilController->getRmPurchaseCenters($CenterID);
            $Query .= " and RAD.ArrivalStationID='$CenterID'";
            $Orderby = " order by RAD.ArrivalStationID,BillDate ,ArrivalNumber asc";
        } else {
            $Orderby = " order by BillDate ,ArrivalNumber asc";
        }

        if ($CenterID != '' && $SupplierID != '') {
            $Query = " and RAD.ArrivalStationID='$CenterID'";
            $Query .= " and RAD.RmSupplierID in ($SupplierID)";
        }
        if ($Plant != '') {
            $Query .= " and RAD.ProcessingStationID='$Plant'";
        }
        if ($CenterID == '' && $SupplierID != '') {

            $sqlSupplier = "SELECT GROUP_CONCAT(ID) as SupplierID FROM `RmSuppliers` WHERE PanNumber =(SELECT PanNumber FROM `RmSuppliers` 
            WHERE ID=$SupplierID)";
            $stmt = $this->conn->prepare($sqlSupplier);
            $stmt->execute();
            $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
            $SearchSupplierID = $dataRow['SupplierID'];
            $Query .= " and RAD.RmSupplierID in ($SearchSupplierID)";
        }
        if ($CompanyType != '') {
            $Query .= " and IsCompany='$CompanyType'";
        }
        if ($ReqAgentID != '') {
            $Query .= " and AgentID='$ReqAgentID'";
            $AryAgentDeatils = $this->utilController->getAgentName($ReqAgentID);
            $AgentName = $AryAgentDeatils['UserName'];
            $AgentPanNumber = $AryAgentDeatils['PanNumber'];
        }
        if ($ReportType == 'M' && $Type == '1') {
            $Groupby .= " group by RAD.AgentID";
        } else if ($ReportType == 'M' && $Type == '2') {
            $Groupby .= " group by RAD.RmSupplierID";
        } else {
            $Groupby .= " group by RAD.RmArrivalID";
        }
        $SqlFields = "AgentID, group_concat(RmArrivalID) as RmArrivalID ,ArrivalNumber,BillDate,ArrivalStationID,RmSupplierID,ProcessingStationID,PanNumber,SupplierType";
        $sqlRmArrivalQuery = "SELECT $SqlFields,FinancierName FROM `RmArrivalDetatils` as RAD 
       left join RmSuppliers as RS on RAD.RmSupplierID=RS.ID
        WHERE  BillDate $DateFilter $Query and RAD.LotStatus='1' $Groupby $Orderby"; //uma
        $stmt = $this->conn->prepare($sqlRmArrivalQuery);
        $stmt->execute();

        $i = 0;
        $j = 0;
        $TotalQuantity = 0;
        $TotalValue = 0;
        $TotalAmountt = 0;
        $TotalHarvestAmount = 0;
        $TotalTDSValue = 0;
        $Data = array();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $BillDate = $this->utilController->getFormatedDateEmpty($dataRow['BillDate']);
            // $AryArrivalPriceDetails = $this->utilController->getArrivalBillDetails($dataRow['RmArrivalID']);
            $AryArrivalPriceDetails = $this->getArrivalBillDetails($dataRow['RmArrivalID']);
            if ($CenterID == '') {
                $AryPurchaseCenters = $this->utilController->getRmPurchaseCenters($dataRow['ArrivalStationID']);
            }
            if ($ReqAgentID == '') {
                $AgentName = "";
                $AgentPanNumber = "";
                $AgentGstNumber = "";
            }

            $ArrivalIDs = $dataRow['RmArrivalID'];
            // $TdsDeductedValue = $AryArrivalPriceDetails['TdsDeductedValue'];
            $PurchaseValue = $AryArrivalPriceDetails['BillAmount'];
            $HarverstValue = $AryArrivalPriceDetails['HarvestBillAmount'];
            $TotalAmount = $AryArrivalPriceDetails['HarvestBillAmount'] + $AryArrivalPriceDetails['BillAmount'];

            $sqlRmBillQuery = "SELECT  sum(`BillTds`) as BillTds, sum(`HarvestTds`) as HarvestTds,sum(TdsDeductedValue) as TdsDeductedValue
            FROM `RmBillDetails` WHERE ArrivalID in ($ArrivalIDs)"; //uma
            $stmtRmBillQuery = $this->conn->prepare($sqlRmBillQuery);
            $stmtRmBillQuery->execute();
            $dataRowBillQuery = $stmtRmBillQuery->fetch(PDO::FETCH_ASSOC);
            $PurchaseTdsValue = $dataRowBillQuery['BillTds'];
            $HarvestTdsValue = $dataRowBillQuery['HarvestTds'];
            $TotalTDS = $dataRowBillQuery['BillTds'] + $dataRowBillQuery['HarvestTds'];
            $TdsDeductedValue = $dataRowBillQuery['TdsDeductedValue'];


            if ($ReqAgentID == '' && $dataRow['AgentID'] != 0) {
                $AryAgentDeatils = $this->utilController->getAgentName($dataRow['AgentID']);
                $AgentName = $AryAgentDeatils['UserName'];
                $AgentPanNumber = $AryAgentDeatils['PanNumber'];
                $AgentGstNumber = $AryAgentDeatils['GstNo'];

                $SupplierPurValue = $PurchaseValue;
                $SupplierHarValue = 0;
                $SupplierPurTdsVal = $PurchaseTdsValue;
                $SupplierHarTdsVal = 0;
                $SupplierTotalVal = $PurchaseValue;
                $SupplierTotalTds = $PurchaseTdsValue;

                $AgentHarValue = $HarverstValue;
                $AgentHarTdsVal = $HarvestTdsValue;
                $AgentTotalVal = $HarverstValue;
                $AgentTotalTds = $HarvestTdsValue;
            }
            /**uma 03-06-2024** */ else if ($ReqAgentID != '' && $dataRow['AgentID'] != 0) {
                $AryAgentDeatils = $this->utilController->getAgentName($dataRow['AgentID']);
                $AgentName = $AryAgentDeatils['UserName'];
                $AgentPanNumber = $AryAgentDeatils['PanNumber'];
                $AgentGstNumber = $AryAgentDeatils['GstNo'];

                $SupplierPurValue = $PurchaseValue;
                $SupplierHarValue = 0;
                $SupplierPurTdsVal = $PurchaseTdsValue;
                $SupplierHarTdsVal = 0;
                $SupplierTotalVal = $PurchaseValue;
                $SupplierTotalTds = $PurchaseTdsValue;

                $AgentHarValue = $HarverstValue;
                $AgentHarTdsVal = $HarvestTdsValue;
                $AgentTotalVal = $HarverstValue;
                $AgentTotalTds = $HarvestTdsValue;
            }
            /**uma 03-06-2024** */ else {
                $SupplierPurValue = $PurchaseValue;
                $SupplierHarValue = $HarverstValue;
                $SupplierPurTdsVal = $PurchaseTdsValue;
                $SupplierHarTdsVal = $HarvestTdsValue;
                $SupplierTotalVal = $PurchaseValue + $HarverstValue;
                $SupplierTotalTds = $PurchaseTdsValue + $HarvestTdsValue;

                $AgentHarValue = 0;
                $AgentHarTdsVal = 0;
                $AgentTotalVal = 0;
                $AgentTotalTds = 0;
            }


            if ($Type == '') {
                $ArrayBookingList['SNO'] = ++$i;
                $ArrayBookingList['WeightmentSlip'] = $dataRow['ArrivalNumber'];
                $ArrayBookingList['BillNo'] = $dataRow['ArrivalNumber'];
                $ArrayBookingList['Center'] = $AryPurchaseCenters['StationName'];
                $ArrayBookingList['FarmerName'] = $dataRow['FinancierName'];
                $ArrayBookingList['Type'] = $dataRow['SupplierType'];
                $ArrayBookingList['PanNumber'] = $dataRow['PanNumber'];
                $ArrayBookingList['Date'] = $BillDate;
                $ArrayBookingList['AgentName'] = $AgentName;
                $ArrayBookingList['AgentPanNumber'] = $AgentPanNumber;
                $ArrayBookingList['TdsDeductedValue'] = $TdsDeductedValue;

                // $ArrayBookingList['Harvest'] = $AryArrivalPriceDetails['HarvestExpanse'];
                $ArrayBookingList['TotalAmount'] = number_format($TotalAmount, 2);
                $ArrayBookingList['FinalAmount'] = number_format($TotalAmount, 2);
                $ArrayBookingList['Amount'] = number_format($SupplierPurValue, 2);
                $ArrayBookingList['HarvestAmount'] = number_format($SupplierHarValue, 2);
                $ArrayBookingList['PurchaseTDSdeducted'] = number_format($SupplierPurTdsVal, 2);
                $ArrayBookingList['HarvestTDSDeducted'] = number_format($SupplierHarTdsVal, 2);
                $ArrayBookingList['TotalValue'] = number_format($SupplierTotalVal, 2);
                $ArrayBookingList['TotalTDS'] = number_format($SupplierTotalTds, 2);

                $TotalValue += $PurchaseValue;
                $TotalAmountt += $TotalAmount;
                $TotalHarvestAmount += $AryArrivalPriceDetails['HarvestBillAmount'];
                $TotalTDSValue += $TotalTDS;


                $ArrayReportData[] = $ArrayBookingList;
            } else if ($Type != 1) {

                $AryAgentHarvestAmount = $this->getSupplierHarvestAmount($ArrivalIDs);
                $BillAmount = $AryAgentHarvestAmount['HarvestAmount'];
                $SupplierHarTdsVal = $AryAgentHarvestAmount['HarvestTds'];
                $SupplierHarValue = $AryAgentHarvestAmount['HarvestAmount'];
                $SupplierTotalVal = $PurchaseValue + $AryAgentHarvestAmount['HarvestAmount'];
                $SupplierTotalTds = $PurchaseTdsValue + $AryAgentHarvestAmount['HarvestTds'];

                $ArrayBookingList['SNO'] = ++$i;
                $ArrayBookingList['WeightmentSlip'] = $dataRow['ArrivalNumber'];
                $ArrayBookingList['BillNo'] = $dataRow['ArrivalNumber'];
                $ArrayBookingList['Center'] = $AryPurchaseCenters['StationName'];
                $ArrayBookingList['FarmerName'] = $dataRow['FinancierName'];
                $ArrayBookingList['Type'] = $dataRow['SupplierType'];
                $ArrayBookingList['PanNumber'] = $dataRow['PanNumber'];
                $ArrayBookingList['Date'] = $BillDate;
                $ArrayBookingList['AgentName'] = $AgentName;
                $ArrayBookingList['AgentPanNumber'] = $AgentPanNumber;
                $ArrayBookingList['TdsDeductedValue'] = $TdsDeductedValue;
                // $ArrayBookingList['Harvest'] = $AryArrivalPriceDetails['HarvestExpanse'];
                $ArrayBookingList['TotalAmount'] = number_format($TotalAmount, 2);
                $ArrayBookingList['FinalAmount'] = number_format($TotalAmount, 2);
                $ArrayBookingList['Amount'] = number_format($SupplierPurValue, 2);
                $ArrayBookingList['HarvestAmount'] = number_format($SupplierHarValue, 2);
                $ArrayBookingList['PurchaseTDSdeducted'] = number_format($SupplierPurTdsVal, 2);
                $ArrayBookingList['HarvestTDSDeducted'] = number_format($SupplierHarTdsVal, 2);
                $ArrayBookingList['TotalValue'] = number_format($SupplierTotalVal, 2);
                $ArrayBookingList['TotalTDS'] = number_format($SupplierTotalTds, 2);

                $TotalValue += $PurchaseValue;
                $TotalAmountt += $SupplierTotalVal;
                $TotalHarvestAmount += $SupplierHarValue;

                $TotalTDSValue += $SupplierTotalTds;


                $ArrayReportData[] = $ArrayBookingList;
            }

            if ($Type != 2) {
                $AgentID = $dataRow['AgentID'];

                if ($dataRow['AgentID'] != '' && $dataRow['AgentID'] != 0) {

                    $AryAgentHarvestAmount = $this->getAgentHarvestAmount($ArrivalIDs, $AgentID);
                    $BillAmount = $AryAgentHarvestAmount['HarvestAmount'];
                    $AgentHarTdsVal = $AryAgentHarvestAmount['HarvestTds'];
                    $AgentHarValue = $AryAgentHarvestAmount['HarvestAmount'];
                    $AgentTotalTds = $AryAgentHarvestAmount['HarvestTds'];

                    if ($Type != '' && $BillAmount > 0) {
                        $BillNo = $dataRow['ArrivalNumber'];
                        $Center = $AryPurchaseCenters['StationName'];
                        $SNo = ++$j;

                    }
                    if ($BillAmount > 0) {

                        $ArrayBookingList = array();
                        $ArrayBookingList['SNO'] = $SNo;
                        $ArrayBookingList['BillNo'] = $BillNo;
                        $ArrayBookingList['Center'] = $Center;
                        $ArrayBookingList['Date'] = $BillDate;
                        $ArrayBookingList['Amount'] = number_format(0, 2);
                        $ArrayBookingList['HarvestAmount'] = number_format($AgentHarValue, 2);
                        $ArrayBookingList['PurchaseTDSdeducted'] = number_format(0, 2);
                        $ArrayBookingList['HarvestTDSDeducted'] = number_format($AgentHarTdsVal, 2);
                        $ArrayBookingList['TotalValue'] = number_format($BillAmount, 2);
                        $ArrayBookingList['TotalTDS'] = number_format($AgentTotalTds, 2);
                        $ArrayBookingList['FarmerName'] = $AgentName;
                        $ArrayBookingList['PanNumber'] = $AgentPanNumber;
                        $ArrayBookingList['GstNo'] = $AgentGstNumber;
                        $ArrayBookingList['Type'] = "Agent";

                        if ($Type != '') {

                            $TotalHarvestAmount += $AgentHarValue;
                            $TotalAmountt += $AgentHarValue;
                            $TotalTDSValue += $AgentTotalTds;
                        }

                        $ArrayReportData[] = $ArrayBookingList;
                    }
                }

            }
        }

        $ArrayTotals['TotalQuantity'] = number_format($TotalQuantity, 2);
        $ArrayTotals['TotalValue'] = number_format($TotalValue, 2);
        $ArrayTotals['TotalAmountt'] = number_format($TotalAmountt, 2);
        $ArrayTotals['TotalHarvestAmount'] = number_format($TotalHarvestAmount, 2);
        $ArrayTotals['TotalTDSValue'] = number_format($TotalTDSValue, 2);

        $Data['PurchaseReportData'] = $ArrayReportData;
        $Data['Totals'] = $ArrayTotals;

        return $Data;
    }
    public function getBreakUpDetails($ArrivalIDs)
    {
        $sqlQuery = "SELECT Variety,AVG(Count) as ArrivalCount,SUM(Weight) as ArrivalWeight,AVG(Rate) as ArrivalRate ,SUM(Weight*Rate) as ArrivalAmount
        FROM `RmArrivalBreakUpDetails` WHERE ArrivalID in($ArrivalIDs)";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            $data['ArrivalCount'] = 'NA';
            $data['ArrivalWeight'] = 'NA';
            $data['BookingRate'] = 'NA';
            $data['ArrivalAmount'] = 'NA';

        } else {
            $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
            $data['ArrivalCount'] = $dataRow['ArrivalCount'];
            $data['ArrivalWeight'] = $dataRow['ArrivalWeight'];
            $data['ArrivalRate'] = $dataRow['ArrivalRate'];
            $data['ArrivalAmount'] = $dataRow['ArrivalAmount'];
            $data['Variety'] = $dataRow['Variety'];
        }
        return $data;
    }



    /***uma** */

    function getSupplierHarvestAmount($ArrivalID)
    {
        $SqlHar = "SELECT 
    sum(HarvestAmount) as  HarvestAmount ,
    sum(HarvestTds) as HarvestTds 
    FROM `RmBillDetails` WHERE  ArrivalID in ($ArrivalID) and BillAgentID=0 ";
        $stmt = $this->conn->prepare($SqlHar);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        return $dataRow;
    }
    function getAgentHarvestAmount($ArrivalID, $AgentID)
    {
        $SqlHar = "SELECT sum(HarvestAmount) as  HarvestAmount,sum(HarvestTds) as HarvestTds
      FROM `RmBillDetails` WHERE  ArrivalID in ($ArrivalID) and BillAgentID in ($AgentID)  and HarvestAmount > 0 group by BillAgentID ";
        $stmt = $this->conn->prepare($SqlHar);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
        return $dataRow;
    }
    /***uma** */

    function getArrivalBillDetails($ArrivalID)
    {
        $sqlBillAmountQuery = "SELECT sum(ArrivalBillAmount) as BillAmount,  
        sum(ArrivalHarvestAmount) as HarvestAmount,sum(ArrivalBillTds) as BillTds,sum(ArrivalHarvestTds) as HarvestTds
         from RmArrivalDetatils as RAD   
        WHERE RAD.RmArrivalID in ($ArrivalID)";
        $stmtBillAmount = $this->conn->prepare($sqlBillAmountQuery);
        $stmtBillAmount->execute();
        $dataRowBillAmount = $stmtBillAmount->fetch(PDO::FETCH_ASSOC);

        $BillAmount = $dataRowBillAmount['BillAmount']; //uma
        $HarvestBillAmount = $dataRowBillAmount['HarvestAmount']; //uma

        $data['BillAmount'] = $BillAmount;
        $data['HarvestBillAmount'] = $HarvestBillAmount;
        return $data;
    }

}
