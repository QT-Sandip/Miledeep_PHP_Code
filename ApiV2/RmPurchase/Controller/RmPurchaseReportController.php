<?php

include_once '../Global/Util.php';
include_once 'UtilController.php';

class RmPurchaseReportController
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
        $AgentID = $RequestBody['AgentID'];
        $ReportType = $RequestBody['ReportType'];


        $DateFilter = "between '$StartDate' and '$EndDate'";


        $Query = '';



        if ($CenterID != '') {
            $Query .= " and ArrivalStationID='$CenterID'";
            // $AryPurchaseCenters = $this->utilController->getRmPurchaseCenters($CenterID);
        }

        if ($Plant != '') {
            $Query .= " and RAD.ProcessingStationID='$Plant'";
        }

        if ($SupplierID != '') {
            $Query .= " and RmSupplierID='$SupplierID'";
        }
        if ($CompanyType != '') {
            $Query .= " and IsCompany='$CompanyType'";
        }

        if ($AgentID != '') {
            $Query .= " and AgentID='$AgentID'";
        }

        if ($ReportType != '') {
            //$Query .= " and IsCompany='$CompanyType'";
        }


        $SqlFields = "AgentID, RmArrivalID ,ArrivalNumber,BillDate,ArrivalStationID,RmSupplierID,ProcessingStationID,PanNumber,SupplierType";



        $sqlRmArrivalQuery = "SELECT $SqlFields,FinancierName as SupplierName FROM `RmArrivalDetatils` as RAD 
       left join RmSuppliers as RS on RAD.RmSupplierID=RS.ID
        WHERE  BillDate $DateFilter $Query and RAD.LotStatus='1' order by BillDate ,ArrivalNumber asc"; //uma

        $sqlRmArrivalQuery = "SELECT AgentID, RmArrivalID ,BillDate,ArrivalNumber,StationName,FinancierName as SupplierName,
        ArrivalBillAmount,ArrivalHarvestAmount,sum(Weight) as Weight,ArrivalStationID,RmSupplierID,RAD.ProcessingStationID,
        PanNumber,SupplierType
        FROM RmArrivalDetatils as RAD 
        left join RmSuppliers as RS on RAD.RmSupplierID=RS.ID 
        left join RmPurchaseStations as RPS on RPS.PurchaseStationID=RAD.ArrivalStationID
        left join RmArrivalBreakUpDetails as RBD on RBD.ArrivalID=RAD.RmArrivalID
        WHERE  BillDate $DateFilter $Query and RAD.LotStatus='1' group by RAD.RmArrivalID order by BillDate ,ArrivalNumber asc";

        $stmt = $this->conn->prepare($sqlRmArrivalQuery);
        $stmt->execute();

        $i = 0;
        $TotalQuantity = 0;
        $TotalValue = 0;
        $TotalAmountt = 0;
        $TotalHarvestAmount = 0;
        $TotalTDSValue = 0;
        $Data = array();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $ArrayBookingList['SNO'] = ++$i;
            $ArrayBookingList['WeightmentSlip'] = 'NA';
            $ArrayBookingList['BillNo'] = $dataRow['ArrivalNumber'];
            $ArrayBookingList['Date'] = $this->utilController->getFormatedDateEmpty($dataRow['BillDate']);

            $ArrayBookingList['ArrivalPlant'] = $dataRow['StationName'];
            $ArrayBookingList['Supplier'] = $dataRow['SupplierName'];
            $ArrayBookingList['Center'] = $dataRow['StationName'];
            $ArrayBookingList['FarmerName'] = $dataRow['SupplierName'];
            $ArrayBookingList['Type'] = $dataRow['SupplierType'];

            // if ($dataRow['AgentID'] != 0) {
            //     $ArrayBookingList['Type'] = "Agent-" . $dataRow['AgentID'];
            // } else {
            //     //  $ArrayBookingList['Type'] = $dataRow['SupplierType']; 
            // }
            $ArrayBookingList['PanNumber'] = $dataRow['PanNumber'];

            $TotalAmount = $dataRow['ArrivalHarvestAmount'] + $dataRow['ArrivalBillAmount'];

            $ArrayBookingList['Qty'] = number_format($dataRow['Weight'], 2);

            $ArrayBookingList['Amount'] = number_format($dataRow['ArrivalBillAmount'], 2);
            $ArrayBookingList['Rate'] = number_format(($dataRow['ArrivalBillAmount'] / $dataRow['Weight']), 2);

            $ArrayBookingList['HarvestAmount'] = number_format($dataRow['ArrivalHarvestAmount'], 2);
            $ArrayBookingList['TotalAmount'] = number_format($TotalAmount, 2);
            $ArrayBookingList['FinalAmount'] = number_format($TotalAmount, 2);

            // $TotalTDS = $AryArrivalPriceDetails['BillTDS'] + $AryArrivalPriceDetails['HarvestTDS'];
            // $ArrayBookingList['Count'] = round($WeightDetails['ArrivalCount'], 2);
            // $ArrayBookingList['Harvest'] = $AryArrivalPriceDetails['HarvestExpanse'];
            // $ArrayBookingList['Variety'] = $WeightDetails['Variety'];
            // $ArrayBookingList['PurchaseTDSdeducted'] = number_format($AryArrivalPriceDetails['BillTDS'], 2);
            // $ArrayBookingList['HarvestTDSDeducted'] = number_format($AryArrivalPriceDetails['HarvestTDS'], 2);


            $ArrayBookingList['TotalValue'] = number_format($TotalAmount, 2);
            $ArrayBookingList['TotalTDS'] = number_format($TotalTDS, 2);

            $TotalQuantity += $dataRow['Weight'];
            $TotalValue += $dataRow['ArrivalBillAmount'];
            $TotalAmountt += $TotalAmount;
            $TotalHarvestAmount += $dataRow['ArrivalHarvestAmount'];
            $TotalTDSValue += $TotalTDS;
            //  $TotalTDSValue +=$AryArrivalPriceDetails['TotalTDS'];

            //$Data[] = $ArrayBookingList;
            $ArrayReportData[] = $ArrayBookingList;
        }
        //$ArrayTotals['TotalInvoices'] = $i;
        $ArrayTotals['TotalQuantity'] = number_format($TotalQuantity, 2);
        $ArrayTotals['TotalValue'] = number_format($TotalValue, 2);
        $ArrayTotals['TotalAmountt'] = number_format($TotalAmountt, 2);
        $ArrayTotals['TotalHarvestAmount'] = number_format($TotalHarvestAmount, 2);
        $ArrayTotals['TotalTDSValue'] = number_format($TotalTDSValue, 2);
        $Data['PurchaseReportData'] = $ArrayReportData;
        $Data['Totals'] = $ArrayTotals;

        return $Data;
    }



}
