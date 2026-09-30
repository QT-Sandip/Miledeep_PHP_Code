<?php

// ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once 'UtilController.php';
include_once '../App/Controller/AppUtilController.php';
include_once __DIR__ . '/AuditLogger.php';


class RmArrivalListController
{

    private $conn;
    private $db_table = "RmArrivalDetatils";
    private $utilController;
    private $auditLogger;


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->utilController = new UtilController($this->conn);
        $this->appController = new AppUtilController($this->conn);
        $this->auditLogger = new AuditLogger($this->conn, 'Purchase');

    }
    // GET ALL
    public function GetRmArrivalListDetails($RequestBody)
    {

        $ArrivalDate = $RequestBody['ArrivalDate'];
        $ArrivalPlant = $RequestBody['ArrivalPlant'];
        $Supplier = $RequestBody['Supplier'];
        $PsId = $RequestBody['PsId'];
        $ClientID=$RequestBody['ClientID'];

        $Query = '';

        if (isset($ArrivalPlant) && $ArrivalPlant != '') {
            $Query .= " and RLD.ProcessingStationID='$ArrivalPlant'";
        }

        if (isset($Supplier) && $Supplier != '') {
            $Query .= " and RLD.RmSupplierID='$Supplier'";
        }

        if (isset($PsId) && $PsId != '') {
            $Query .= " and RLD.ArrivalStationID='$PsId'";
        }

        $SqlFields = " RmArrivalID ,GraderID,AgentID,GROUP_CONCAT(DISTINCT AbStatus) as AbStatus,Name,Colour,RmSupplierID,HarvestCommission,
        PurchaseType,ArrivalNumber,RLD.LotStatus,FarmAddress,ArrivalDate,RPSS.StationName";

        $sqlRmArrivalQuery = "SELECT  $SqlFields FROM " . $this->db_table . " as RLD 
        left join RmPurchaseStations as RPS on RLD.ArrivalStationID=RPS.PurchaseStationID
        left join RmProcessingStations as RPSS on RLD.ProcessingStationID=RPSS.ProcessingStationID 
        left join QcRmAbReport as QCAB on RLD.RmArrivalID=QCAB.LotID
        left join QcABOptions as ABO on ABO.OptID=QCAB.AbStatus
        left join RmPurchaseCategory as RPC on RLD.PurchaseCategoryID=RPC.CatID
        where ArrivalDate ='$ArrivalDate' and RLD.LotStatus='1' $Query GROUP BY RLD.RmArrivalID order by ArrivalDate,ArrivalNumber desc";

        $stmt = $this->conn->prepare($sqlRmArrivalQuery);
        $stmt->execute();

        $i = 0;
        $Data = array();
        $TableTotalAmount = 0;
        $TableTotalWeight = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {

            $ArrayBookingList['SNO'] = ++$i;
            $ArrayBookingList['ArrivalNumber'] = $dataRow['ArrivalNumber'];
            $ArrayBookingList['ArrivalDate'] = $dataRow['ArrivalDate'];
            $ArrayBookingList['ArrivalPlant'] = $dataRow['StationName'];
            $ArrayBookingList['PurchaseType'] = $dataRow['PurchaseType'];
            $ArrayBookingList['RmArrivalID'] = $dataRow['RmArrivalID'];
            $ArrayBookingList['Supplier'] = $this->utilController->getRMSuppliers($dataRow['RmSupplierID'])['SupplierName'];
            $GraderID = $dataRow['GraderID'];
            $ArrayBookingList['Grader'] = $this->utilController->getUserName($GraderID);
            $AgentID = $dataRow['AgentID'];
            $ArrayBookingList['Agent'] = $this->utilController->getUserName($AgentID);
            $ArrivalID = $dataRow['RmArrivalID'];
            $WeightDetails = $this->getBreakUpDetails($ArrivalID);
            $Editlink = $this->getEditLink($dataRow['RmArrivalID']);
            $ReceivingCount = $this->getReceivingData($ArrivalID);

            $ArrayBookingList['ReceivingCount'] = $ReceivingCount;

            if ($Editlink == "" || $ReceivingCount != 0) {
                $DeleteAccess = false;
            } else {
                $DeleteAccess = true;
            }
            $ArrayBookingList['ArrivalCount'] = sprintf('%0.2f', $WeightDetails['ArrivalCount']);
            $ArrayBookingList['ArrivalWeight'] = sprintf('%0.3f', $WeightDetails['ArrivalWeight']);
            $TableTotalWeight += $WeightDetails['ArrivalWeight'];
            $ArrayBookingList['TableTotalWeight'] = $TableTotalWeight;
            $ArrayBookingList['HarvestCommission'] = round($WeightDetails['ArrivalWeight'] * $dataRow['HarvestCommission'], 2);
            $TotalAmount = $WeightDetails['TotalAmount'] + $ArrayBookingList['HarvestCommission'];
            $ArrayBookingList['ArrivalRate'] = sprintf('%0.2f', ($TotalAmount / $WeightDetails['ArrivalWeight']));
            $ArrayBookingList['TotalAmt'] = sprintf('%0.2f', round($WeightDetails['TotalAmount'] + $ArrayBookingList['HarvestCommission'], 2));
            $ArrayBookingList['EditLink'] = $this->getEditLink($dataRow['RmArrivalID']);
            $ArrayBookingList['DeleteAccess'] = $DeleteAccess;
            $ArrayBookingList['PrintLink'] =$this->getPrintLink($ClientID,$ArrivalID);

            $TableTotalAmount += sprintf('%0.2f', round($WeightDetails['TotalAmount'] + $ArrayBookingList['HarvestCommission'], 2));
            $ArrayBookingList['TableTotalAmount'] = $TableTotalAmount;

            $Data[] = $ArrayBookingList;
        }
        return $Data;
    }



    public function getBreakUpDetails($ArrivalID)
    {

        $sqlQuery = "SELECT AVG(Count) as ArrivalCount,SUM(Weight) as ArrivalWeight,AVG(Rate) as ArrivalRate ,sum(Weight*Rate) as TotalAmount
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
            $data['TotalAmount'] = $dataRow['TotalAmount'];
        }
        return $data;
    }


    function deleteArrivalDetails($RequestBody)
    {
        $RmArrivalID = $RequestBody['RmArrivalID'];
        $existingArrival = $this->auditLogger->fetchRowById('RmArrivalDetatils', 'RmArrivalID', $RmArrivalID);
        $existingBreakups = $this->auditLogger->fetchRowsByConditions('RmArrivalBreakUpDetails', ['ArrivalID' => $RmArrivalID]);
        $existingLots = $this->auditLogger->fetchRowsByConditions('RmLotDetatils', ['RmReferenceID' => $RmArrivalID]);
        $existingPanwise = $this->auditLogger->fetchRowsByConditions('RmPanWiseSuppliedAmount', ['PanwiseArrivalID' => $RmArrivalID]);

        $sqlDelete = "DELETE FROM `RmArrivalDetatils` WHERE RmArrivalID='$RmArrivalID'";

        $stmtDelete = $this->conn->prepare($sqlDelete);
        $stmtDelete->execute();
        if ($stmtDelete && $existingArrival != null) {
            $this->auditLogger->logDelete('RmArrivalDetatils', $RmArrivalID, $existingArrival, $RequestBody, [
                'ReferenceNo' => isset($existingArrival['ArrivalNumber']) ? $existingArrival['ArrivalNumber'] : (string) $RmArrivalID,
                'Remarks' => 'Arrival deleted from arrival list',
            ]);
        }

        if ($stmtDelete) {

            $sqlBrkUpDelete = "DELETE FROM `RmArrivalBreakUpDetails` WHERE ArrivalID='$RmArrivalID'";

            $stmtBrkUpDelete = $this->conn->prepare($sqlBrkUpDelete);
            $stmtBrkUpDelete->execute();
            if ($stmtBrkUpDelete) {
                foreach ($existingBreakups as $breakup) {
                    $this->auditLogger->logDelete('RmArrivalBreakUpDetails', $breakup['ArrivalBrkUpID'], $breakup, $RequestBody, [
                        'ReferenceNo' => $RmArrivalID,
                        'Remarks' => 'Arrival breakup deleted from arrival list',
                    ]);
                }
            }

            if ($stmtBrkUpDelete) {

                ////uma 25-05-2024 Tds calculation purpose//
                $sqlDeletePanWiseSuppliedAmount = "delete  from `RmPanWiseSuppliedAmount` where PanwiseArrivalID='$RmArrivalID'";
                $stmtDeletePanWiseSuppliedAmount = $this->conn->prepare($sqlDeletePanWiseSuppliedAmount);
                $stmtDeletePanWiseSuppliedAmount->execute();
                if ($stmtDeletePanWiseSuppliedAmount) {
                    foreach ($existingPanwise as $panwise) {
                        $this->auditLogger->logDelete('RmPanWiseSuppliedAmount', isset($panwise['PanwiseID']) ? $panwise['PanwiseID'] : $panwise['PanwiseArrivalID'], $panwise, $RequestBody, [
                            'ReferenceNo' => $RmArrivalID,
                            'Remarks' => 'Panwise supplied amount deleted from arrival list',
                        ]);
                    }
                }
                //uma 25-05-2024 Tds calculation purpose

                $sqlLotDelete = "DELETE FROM `RmLotDetatils` WHERE RmReferenceID='$RmArrivalID'";

                $stmtLotDelete = $this->conn->prepare($sqlLotDelete);
                $stmtLotDelete->execute();
                if ($stmtLotDelete) {
                    foreach ($existingLots as $lot) {
                        $this->auditLogger->logDelete('RmLotDetatils', isset($lot['RmLotID']) ? $lot['RmLotID'] : $lot['BreakUpID'], $lot, $RequestBody, [
                            'ReferenceNo' => $RmArrivalID,
                            'Remarks' => 'Arrival lot deleted from arrival list',
                        ]);
                    }
                }

                if ($stmtLotDelete) {
                    $ArrivalData = $this->GetRmArrivalListDetails($RequestBody);
                    $Data['Error'] = null;
                    $Data['Msg'] = 'Arrival Deleted Successfully';
                    $Data['Data'] = $ArrivalData['Data'];
                    return $Data;
                }
            }
        } else {
            $Data['Error'] = 'Unable to Delete Arrival';
            $Data['Msg'] = 'Unable to Delete Arrival';
            return $Data;
        }
    }
    public function getEditLink($ArrivalID)
    {
        $sqlEditAccess = "SELECT * FROM `RmBillDetails` WHERE ArrivalID='$ArrivalID'";
        $stmtEditAccess = $this->conn->prepare($sqlEditAccess);
        $stmtEditAccess->execute();

        if ($stmtEditAccess->rowCount() == 0) {

            $EditLink = 'create-arrival.php';
            //    $DeleteAccess=true;

        } else {
            $EditLink = '';
        }
        return $EditLink;
    }

    public function getReceivingData($ArrivalID)
    {
        $sqlReceiving = "SELECT COUNT(*) AS RowCount FROM RmReceivingBreakup WHERE ArrivalID='$ArrivalID'";
        $stmtReceiving = $this->conn->prepare($sqlReceiving);
        $stmtReceiving->execute();

        $dataRow = $stmtReceiving->fetch(PDO::FETCH_ASSOC);
        $RowCount = $dataRow['RowCount'];

        return $RowCount;
    }

    public function getPrintLink($ClientID,$ArrivalID)
    {
        $AccessPlantCheckReport = $this->appController->getClientConfig($ClientID)['AccessPlantCheckReport'];

        $PrintLink='';
        if($AccessPlantCheckReport==1){
            // $PrintLink="/Purchase/Reports/plant-check-report.php";
            $PrintLink="Reports/plant-check-report.php";

            
        }
        return $PrintLink;
    }
}
