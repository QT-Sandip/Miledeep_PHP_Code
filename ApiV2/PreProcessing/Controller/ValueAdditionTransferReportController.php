<?php

include_once '../Global/Util.php';
include_once 'UtilController.php';

class ValueAdditionTransferReportController
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
    public function getVATransferDetails($RequestBody)
    {

        $StartDate = $RequestBody['StartDate'];
        $EndDate = $RequestBody['EndDate'];
        $Plant = $RequestBody['Plant'];
        $DateType = $RequestBody['DateType'];
        if($DateType == 0){
            $DateFilter = " RAD.ArrivalDate BETWEEN '$StartDate' AND '$EndDate'";
        }else{
            $DateFilter = " BillDate BETWEEN '$StartDate' AND '$EndDate'";
        }
        
        $Query = '';

        if ($Plant != '') {
            $Query .= " AND RAD.ProcessingStationID='$Plant'";
        }

        $sqlFields = "ArrivalNumber, BillDate, RAD.ArrivalDate, Variety, Count,VehicalNumber,Weight, Species,FinancierName as SupplierName,
        HarvestCommission, RPRS.StationName as ProductionPlant, RmLotID, RABD.Rate, RmPS.StationName as CenterName";

        $sqlSelect = "SELECT $sqlFields FROM RmArrivalDetatils AS RAD
        LEFT JOIN RmLotDetatils AS RLD ON RAD.RmArrivalID = RLD.RmReferenceID
        LEFT JOIN RmArrivalBreakUpDetails AS RABD ON RABD.ArrivalBrkUpID=RLD.BreakUpID
        LEFT JOIN RmProcessingStations AS RPRS ON RPRS.ProcessingStationID=RAD.ProcessingStationID
        LEFT JOIN RmPurchaseStations AS RmPS ON RAD.ArrivalStationID = RmPS.PurchaseStationID
        LEFT JOIN RmVehicalDetails AS RVD ON RVD.ID=RAD.VehicalID
        LEFT JOIN RmSuppliers AS RMS ON RMS.ID=RAD.RmSupplierID
        WHERE $DateFilter $Query GROUP BY RmLotID, Count";
        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();

        $i = 0;
        $ArrayData = [];
        $TotalFinalWeight = 0;
        $TotalPurchaseAmount = 0;
        $TotalFinalAmount = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
            $ArrayList['SNO'] = ++$i;
            $ArrayList['ArrivalNumber'] = $dataRow['ArrivalNumber'];
            $ArrayList['ArrivalDate'] = $dataRow['ArrivalDate'] ? date('d-m-Y', strtotime($dataRow['ArrivalDate'])) : '';
            $ArrayList['BillDate'] = $dataRow['BillDate'] ? date('d-m-Y', strtotime($dataRow['BillDate'])) : '';
            $ArrayList['RmLotID'] = $dataRow['RmLotID'];
            $ArrayList['VehicleNumber'] = $dataRow['VehicalNumber'];
            $ArrayList['ProductionPlant'] = $dataRow['ProductionPlant'];
            $ArrayList['CenterName'] = $dataRow['CenterName'];
            $ArrayList['SupplierName'] = $dataRow['SupplierName'];
            $ArrayList['HarvestCommission'] = $dataRow['HarvestCommission'];
            $ArrayList['Species'] = $dataRow['Species'];
            $ArrayList['Variety'] = $dataRow['Variety'];
            $ArrayList['Count'] = $dataRow['Count'];
            $ArrayList['Rate'] = $dataRow['Rate'];
            $ArrayList['RmProductionPlant'] = $dataRow['ProductionPlant'];
            $ArrayList['TransferDate'] = '';

            $BreakUpData = $this->getVaTransferData($dataRow['RmLotID']);
            $FinalWeight = $dataRow['Weight'] - $BreakUpData['TotalVaWeight'];
            $ArrayList['FinalWeight'] = $FinalWeight;
            $PurchaseAmount = $FinalWeight * $dataRow['Rate'];
            $HarvestAmount = $FinalWeight * $dataRow['HarvestCommission'];
            $ArrayList['PurchaseAmount'] = $PurchaseAmount;
            $ArrayList['HarvestAmount'] = $HarvestAmount;
            $FinalAmountAmount = $PurchaseAmount + $HarvestAmount;
            $ArrayList['FinalAmountAmount'] = $FinalAmountAmount;

            $ArrayData[] = $ArrayList;

            $TotalFinalWeight += $FinalWeight;
            $TotalPurchaseAmount += $PurchaseAmount;
            $TotalFinalAmount += $FinalAmountAmount;

            if (!empty($BreakUpData['DataArray'])) {
                foreach ($BreakUpData['DataArray'] as $dataList) {
                    $ListArray['SNO'] = ++$i;
                    $ListArray['ArrivalNumber'] = $dataRow['ArrivalNumber'];
                    $ListArray['ArrivalDate'] = $dataRow['ArrivalDate'] ? date('d-m-Y', strtotime($dataRow['ArrivalDate'])) : '';
                    $ListArray['BillDate'] = $dataRow['BillDate'] ? date('d-m-Y', strtotime($dataRow['BillDate'])) : '';
                    $ListArray['RmLotID'] = $dataRow['RmLotID'];
                    $ListArray['VehicleNumber'] = $dataRow['VehicalNumber'];
                    $ListArray['ProductionPlant'] = $dataRow['ProductionPlant'];
                    $ListArray['CenterName'] = $dataRow['CenterName'];
                    $ListArray['SupplierName'] = $dataRow['SupplierName'];
                    $ListArray['HarvestCommission'] = $dataRow['HarvestCommission'];
                    $ListArray['Species'] = $dataRow['Species'];
                    $ListArray['Variety'] = $dataRow['Variety'];
                    $ListArray['Count'] = $dataRow['Count'];
                    $ListArray['Rate'] = $dataRow['Rate'];
                    $ListArray['RmProductionPlant'] = $dataList['RmProductionPlant'];
                    $ListArray['TransferDate'] = $dataList['TransferDate'] ? date('d-m-Y', strtotime($dataList['TransferDate'])) : 'NA';
                    $FinalWeight = $dataList['VaWeight'];
                    $ListArray['FinalWeight'] = $FinalWeight;
                    $PurchaseAmount = $FinalWeight * $dataRow['Rate'];
                    $HarvestAmount = $FinalWeight * $dataRow['HarvestCommission'];
                    $ListArray['PurchaseAmount'] = $PurchaseAmount;
                    $ListArray['HarvestAmount'] = $HarvestAmount;
                    $FinalAmountAmount = $PurchaseAmount + $HarvestAmount;
                    $ListArray['FinalAmountAmount'] = $FinalAmountAmount;

                    $ArrayData[] = $ListArray;
                    $TotalFinalWeight += $FinalWeight;
                    $TotalPurchaseAmount += $PurchaseAmount;
                    $TotalFinalAmount += $FinalAmountAmount;
                }
            }
        }

        $Data['Error'] = null;
        $Data['Msg'] = 'Success';
        $Data['ReportData'] = $ArrayData;
        $Data['TotalFinalWeight'] = $TotalFinalWeight;
        $Data['TotalPurchaseAmount'] = $TotalPurchaseAmount;
        $Data['TotalFinalAmount'] = $TotalFinalAmount;

        return $Data;
    }

    function getVaTransferData($LotID)
    {
        $sqlFields = "ArrivalPlant, HonCount, VaWeight, TranferPlant, TransferDate, LotID,
        RPS.StationName AS RmProductionPlant";
        $sqlSelect = "SELECT $sqlFields FROM PpcValueAdditionTransferDetails AS PVAT
        LEFT JOIN RmProcessingStations AS RPS ON PVAT.TranferPlant = RPS.ProcessingStationID
        WHERE LotID = '$LotID'";
        $stmtSelect = $this->conn->prepare($sqlSelect);
        $stmtSelect->execute();
        // $errorInfo = $stmtSelect->errorInfo();
        // var_dump($errorInfo);
        $DataArray = [];
        $TotalVaWeight = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {
            $DataList['ArrivalPlant'] = $dataRow['ArrivalPlant'];
            $DataList['HonCount'] = $dataRow['HonCount'];
            $VaWeight = $dataRow['VaWeight'];
            $DataList['VaWeight'] = $VaWeight;
            $DataList['TranferPlant'] = $dataRow['TranferPlant'];
            $DataList['RmProductionPlant'] = $dataRow['RmProductionPlant'];
            $DataList['TransferDate'] = $dataRow['TransferDate'];
            $DataList['LotID'] = $dataRow['LotID'];

            $TotalVaWeight += $VaWeight;
            $DataArray[] = $DataList;
        }

        $Data['DataArray'] = $DataArray;
        $Data['TotalVaWeight'] = $TotalVaWeight;
        return $Data;
    }
}
