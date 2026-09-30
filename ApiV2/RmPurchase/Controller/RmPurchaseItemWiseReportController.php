<?php

include_once '../Global/Util.php';
include_once 'UtilController.php';

class RmPurchaseItemWiseReportController
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

        $DateFilter = " between '$StartDate' and '$EndDate'";
        $Query = '';



        if ($CenterID != '') {
            $Query .= " and ArrivalStationID='$CenterID'";
        } else {
            $CenterID = 0;
        }

        if ($Plant != '') {
            $Query .= " and RAD.ProcessingStationID='$Plant'";
        } else {
            $Plant = 0;
        }

        if ($SupplierID != '') {
            $Query .= " and RmSupplierID='$SupplierID'";
        } else {
            $SupplierID = 0;
        }
        if ($CompanyType != '') {
            $Query .= " and IsCompany='$CompanyType'";
        } else {
            $CompanyType = 0;
        }


        $SqlFields = "ArrivalNumber,BillDate,RAD.ArrivalDate,RPS.StationName as CenterName,RPRS.StationName as ProductionPlant ,FinancierName as SupplierName
        , Count,VehicalNumber,Weight,Variety,Rate,Species,HarvestCommission,(Weight*Rate) as ArrivalAmount,
        (Weight*HarvestCommission) as HarvestBillAmount";

        $sqlRmArrivalQuery = "SELECT $SqlFields  FROM `RmArrivalDetatils` as RAD 
        left join RmSuppliers as RMS on RMS.ID=RAD.RmSupplierID
        left join RmVehicalDetails as RMV on RMV.ID=RAD.VehicalID
         left join RmArrivalBreakUpDetails as RABD on RABD.ArrivalID=RAD.RmArrivalID 
         left join RmPurchaseStations as RPS on RPS.PurchaseStationID=RAD.ArrivalStationID 
         left join RmProcessingStations as RPRS on RPRS.ProcessingStationID=RAD.ProcessingStationID
         where  BillDate $DateFilter $Query order by RPS.StationName asc,BillDate asc,ArrivalNumber ASC";

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
            $ArrayBookingList['ArrivalDate'] = $this->utilController->getFormatedDateEmpty($dataRow['ArrivalDate']);

            $ArrayBookingList['ArrivalPlant'] = $dataRow['ProductionPlant'];
            $ArrayBookingList['Supplier'] = $dataRow['SupplierName'];
            $ArrayBookingList['Center'] = $dataRow['CenterName'];
            $ArrayBookingList['ProductionPlant'] = $dataRow['ProductionPlant'];

            $ArrivalIDs = $dataRow['RmArrivalID'];

            if ($dataRow['Variety'] == 'HEAD ON') {
                $dataRow['Variety'] = 'HON';
            }

            $TotalAmount = $dataRow['HarvestBillAmount'] + $dataRow['ArrivalAmount'];
            $TotalTDS = $dataRow['BillTDS'] + $dataRow['HarvestTDS'];

            $ArrayBookingList['Count'] = round($dataRow['Count'], 2);
            $ArrayBookingList['VehicalNumber'] = $dataRow['VehicalNumber'] ? $dataRow['VehicalNumber'] : 'Others ';
            $ArrayBookingList['Qty'] = number_format($dataRow['Weight'], 2);

            $ArrayBookingList['Amount'] = number_format($dataRow['ArrivalAmount'], 2);
            $ArrayBookingList['Rate'] = number_format($dataRow['Rate'], 2);
            $ArrayBookingList['Harvest'] = $dataRow['HarvestCommission'];
            $ArrayBookingList['HarvestAmount'] = number_format($dataRow['HarvestBillAmount'], 2);
            $ArrayBookingList['TotalAmount'] = number_format($TotalAmount, 2);
            $ArrayBookingList['FinalAmount'] = number_format($TotalAmount, 2);
            $ArrayBookingList['Variety'] = $dataRow['Variety'] . "-" . $dataRow['Species'];
            $ArrayBookingList['PurchaseTDSdeducted'] = number_format($dataRow['BillTDS'], 2);
            $ArrayBookingList['HarvestTDSDeducted'] = number_format($dataRow['HarvestTDS'], 2);
            $ArrayBookingList['TotalValue'] = number_format($TotalAmount, 2);
            $ArrayBookingList['TotalTDS'] = number_format($TotalTDS, 2);

            // $TotalQuantity += $dataRow['ArrivalWeight'];
            $TotalQuantity += $dataRow['Weight'];
            $TotalValue += $dataRow['ArrivalAmount'];
            $TotalAmountt += $TotalAmount;
            // $TotalHarvestAmount += $AryArrivalPriceDetails['HarvestBillAmount'];
            $TotalHarvestAmount += $dataRow['HarvestBillAmount'];
            $TotalTDSValue += $TotalTDS;

            $ArrayReportData[] = $ArrayBookingList;
        }
        $Data['DownloadAccess'] = $PrintAccess;
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
