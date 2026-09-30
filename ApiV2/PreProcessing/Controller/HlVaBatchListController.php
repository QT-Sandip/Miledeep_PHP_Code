<?php

//ini_set('display_errors', 1); ini_set('display_startup_errors', 1); error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once 'UtilController.php';

class HlVaBatchListController
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



    public function postDeleteRmHlVaDetails($RequestBody)
    {

        $BatchID = $RequestBody['BillID'];


        $sqlRmArrivalQuery = "delete FROM `PpcDailyBatchDetails` WHERE ID=$BatchID";
        $stmt = $this->conn->prepare($sqlRmArrivalQuery);
        $stmt->execute();

        $sqlRmArrivalQuery = "delete FROM `PpcDailyBatchBreakUpDeatils` WHERE DailyBatchID=$BatchID";
        $stmt = $this->conn->prepare($sqlRmArrivalQuery);
        $stmt->execute();

        $sqlRmArrivalQuery = "delete FROM `PpcDailyBatchPeelingBreakUpDeatils` WHERE DailyBatchID=$BatchID";
        $stmt = $this->conn->prepare($sqlRmArrivalQuery);
        $stmt->execute();

        return $this->getRmHlVaDetails($RequestBody);
    }
    public function getRmHlVaDetails($RequestBody)
    {

        $BatchDate = $RequestBody['BatchDate'];
        $ArrivalPlant = $RequestBody['ArrivalPlant'];
        $Supplier = $RequestBody['Supplier'];
        $PsId = $RequestBody['PsId'];

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

        //$Query


        $SqlFields = " RmArrivalID ,GraderID,AgentID,AbStatus,Name,Colour,RmSupplierID,HarvestCommission,
        PurchaseType,ArrivalNumber,LotStatus,FarmAddress
        ,ArrivalDate";

        $sqlRmArrivalQuery = "SELECT ID as DailyBatchID,UnitName,ContrctrName,SubContractorName,PDBD.Persons,Adjustments,PDBD.Extras,Comission,PSCM.AutoCharge,PDBD.Remarks,PDBD.Type,PDBD.ApprovalStatus
       FROM `PpcDailyBatchDetails` as PDBD
       left join PpcVAStations as PVS on PVS.StationID=PDBD.StationID
       left join PpcSubContractorMaster as PSCM on  PDBD.BatchID=PSCM.SubContactorID
       left join PpcContractMaster as PCM on PCM.ContractorID=PSCM.ContactorID
       WHERE Date='$BatchDate' order by PCM.ContrctrName,Type asc";
        $stmt = $this->conn->prepare($sqlRmArrivalQuery);
        $stmt->execute();

        $i = 0;
        $Data = array();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {



            $dataRow['SNO'] = ++$i;
            $dataRow['Shift'] = 'A Shift';
            $Qty = $this->getBreakUpDetails($dataRow['DailyBatchID'], $dataRow['Type']);

            if ($dataRow['Type'] == 1) {
                $dataRow['Type'] = 'DeHeading ';
            } else {
                $dataRow['Type'] = 'Value Addition ';
            }

            $dataRow['Qty'] = $Qty;
            $Data[] = $dataRow;
        }
        return $Data;
    }



    public function getBreakUpDetails($DailyBatchID, $Type)
    {

        if ($Type == '1') {
            $sqlQuery = "SELECT sum(HLQty) as Qty FROM `PpcDailyBatchBreakUpDeatils` WHERE DailyBatchID=$DailyBatchID";
        } else {
            $sqlQuery = "SELECT sum(HLQty) as Qty  FROM `PpcDailyBatchPeelingBreakUpDeatils` WHERE DailyBatchID=$DailyBatchID";
        }
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();
        $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);


        return $dataRow['Qty'] ? $dataRow['Qty'] : 0;
    }
}
