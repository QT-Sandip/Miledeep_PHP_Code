<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once 'UtilController.php';
include_once __DIR__ . '/AuditLogger.php';

class RmBillListController
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
        $this->auditLogger = new AuditLogger($this->conn, 'Purchase');
    }
    // GET ALL
    public function GetRmBillListDetails($RequestBody)
    {
        // $BillDate = $RequestBody['BillDate'];
        $PsId = $RequestBody['PsId'];
        $ArrivalPlant = $RequestBody['ArrivalPlant'];
        $FromDate = $RequestBody['FromDate'];
        $ToDate = $RequestBody['ToDate'];
        $Supplier = $RequestBody['Supplier'];
        $Type = $RequestBody['Type'];
        $Agent = $RequestBody['Agent'];
        $RM_Bill_List_Order_Change = $RequestBody['RM_Bill_List_Order_Change'];

        // $PsId = $RequestBody['PsId'];

        $Query = '';
        $GroupQuery = '';

        if ($PsId != '') {
            $Query = "and RLD.ArrivalStationID='$PsId'";
        }

        if (isset($ArrivalPlant) && $ArrivalPlant != '') {
            $Query .= " and RLD.ProcessingStationID='$ArrivalPlant'";
        }

        if (isset($Supplier) && $Supplier != '') {
            $Query .= " AND RLD.RmSupplierID IN (
        SELECT ID 
        FROM RmSuppliers 
        WHERE PanNumber = '$Supplier'
    )";
        }

        if (isset($Agent) && $Agent != '') {
            $Query .= " and RLD.AgentID='$Agent'";
        }

        if (isset($PsId) && $PsId != '') {
            $Query .= " and RLD.ArrivalStationID='$PsId'";
        }

        // if ($Type == 0) {
        //     $Query .= "and ArrivalDate <='$BillDate' and RLD.RmArrivalID not in (SELECT ArrivalID FROM `RmBillDetails`)";
        // } else {
        //     $Query .= "and ArrivalDate = '$BillDate' and  RLD.RmArrivalID  in (SELECT ArrivalID FROM `RmBillDetails`)";
        // }

        if ($Type == 0) {
            $Query .= "and ArrivalDate Between '$FromDate' and '$ToDate' and RLD.RmArrivalID not in (SELECT ArrivalID FROM `RmBillDetails`)";
        } else {
            $Query .= "and ArrivalDate Between '$FromDate' and '$ToDate' and  RLD.RmArrivalID  in (SELECT ArrivalID FROM `RmBillDetails`)";
        }

        $SqlFields = " COALESCE(RBD.BillStatus,0) as BillStatus,RmArrivalID ,GraderID,RLD.AgentID,RmSupplierID,HarvestCommission,
        PurchaseType,ArrivalNumber,LotStatus,FarmAddress,UnderTdsAmount
        ,ArrivalDate";

        $order_by = "";

        if($RM_Bill_List_Order_Change){
            $order_by = "order by ArrivalNumber asc";
        }else{
            $order_by = "order by ArrivalDate asc";
        }

        $sqlRmArrivalQuery = "SELECT  $SqlFields FROM " . $this->db_table . " as RLD 
        left join RmPurchaseStations as RPS on RLD.ArrivalStationID=RPS.PurchaseStationID 

        left join RmPurchaseCategory as RPC on RLD.PurchaseCategoryID=RPC.CatID
        left join RmBillDetails as RBD on RBD.ArrivalID=RLD.RmArrivalID
        where LotStatus='1'  $Query $order_by";
        //echo '<pre>';print_r($sqlRmArrivalQuery);die;
        $stmt = $this->conn->prepare($sqlRmArrivalQuery);
        $stmt->execute();

        $i = 0;
        $Data = array();
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayBookingList['SNO'] = ++$i;
            $ArrayBookingList['RmArrivalID'] = $dataRow['RmArrivalID'];
            $ArrayBookingList['ArrivalNumber'] = $dataRow['ArrivalNumber'];
            $ArrayBookingList['ArrivalDate'] = $dataRow['ArrivalDate'];
            $ArrayBookingList['PurchaseType'] = $dataRow['PurchaseType'];
            $ArrayBookingList['Supplier'] = $this->utilController->getRMSuppliers($dataRow['RmSupplierID'])['SupplierName'];
            $ArrayBookingList['TotalAmt'] = 'NA';
            $GraderID = $dataRow['GraderID'];
            $ArrayBookingList['Grader'] = $this->utilController->getUserName($GraderID);
            $AgentID = $dataRow['AgentID'];
            $ArrayBookingList['Agent'] = $this->utilController->getUserName($AgentID);
            $ArrivalID = $dataRow['RmArrivalID'];
            $ArrayBookingList['BillStatus'] = $dataRow['BillStatus'];
            $WeightDetails = $this->getBreakUpDetails($ArrivalID);
            $ArrayBookingList['ArrivalCount'] = $WeightDetails['ArrivalCount'];
            $ArrayBookingList['ArrivalWeight'] = $WeightDetails['ArrivalWeight'];
            $ArrayBookingList['ArrivalRate'] = $WeightDetails['ArrivalRate'];
            $ArrayBookingList['TotalBillAmount'] = $WeightDetails['ArrivalRate'];
            $ArrayBookingList['UnderTdsAmount'] = $dataRow['UnderTdsAmount'] = 0;
            $ArrayBookingList['HarvestCommission'] = round($WeightDetails['ArrivalWeight'] * $dataRow['HarvestCommission'], 2);
            $ArrayBookingList['TotalBillAmount'] = sprintf('%0.2f', round($WeightDetails['TotalAmount'] + $ArrayBookingList['HarvestCommission'], 2));
            $ArrayBookingList['DeleteAccess'] = $this->getDeleteAccess($ArrivalID);
            $Data[] = $ArrayBookingList;
        }
        return $Data;
    }



    public function getBreakUpDetails($ArrivalID)
    {
        $sqlQuery = "SELECT round(AVG(Count),2) as ArrivalCount,round(sum(Weight),2) as ArrivalWeight,round(AVG(Rate),2) as ArrivalRate,sum(Weight*Rate) as TotalAmount 
        FROM `RmArrivalBreakUpDetails` WHERE ArrivalID='$ArrivalID'";
        $stmt = $this->conn->prepare($sqlQuery);
        $stmt->execute();

        if ($stmt->rowCount() == 0) {
            $data['ArrivalCount'] = 'NA';
            $data['ArrivalWeight'] = 'NA';
            $data['ArrivalRate'] = 'NA';
            $data['TotalAmount'] = 'NA';
        } else {
            $dataRow = $stmt->fetch(PDO::FETCH_ASSOC);
            $data['ArrivalCount'] = $dataRow['ArrivalCount'];
            $data['ArrivalWeight'] = $dataRow['ArrivalWeight'];
            $data['ArrivalRate'] = $dataRow['ArrivalRate'];
            $data['TotalAmount'] = $dataRow['TotalAmount'];
            ;
        }
        return $data;
    }
    function deleteBreakUpDetails($RequestBody)
    {
        $RmArrivalID = $RequestBody['RmArrivalID'];
        /*$existingArrivalContext = $this->utilController->getArrivalPanContext($RmArrivalID);
        $oldSupplierPanNumber = isset($existingArrivalContext['SupplierPanNumber']) ? $existingArrivalContext['SupplierPanNumber'] : null;
        $billDate = isset($existingArrivalContext['BillDate']) ? $existingArrivalContext['BillDate'] : null;*/
        $existingBillDetails = $this->auditLogger->fetchRowsByConditions('RmBillDetails', ['ArrivalID' => $RmArrivalID]);
        $existingBillDetails = !empty($existingBillDetails) ? $existingBillDetails[0] : null;
        $existingBillBreakups = $this->auditLogger->fetchRowsByConditions('RmBillBreakup', ['ArrivalID' => $RmArrivalID]);

        $sqlDelete = "DELETE FROM `RmBillDetails` WHERE ArrivalID='$RmArrivalID'";

        $stmtDelete = $this->conn->prepare($sqlDelete);
        $stmtDelete->execute();
        if ($stmtDelete && $existingBillDetails != null) {
            $this->auditLogger->logDelete('RmBillDetails', $RmArrivalID, $existingBillDetails, $RequestBody, [
                'ReferenceNo' => $RmArrivalID,
                'Remarks' => 'Bill deleted from bill list',
            ]);
        }

        if ($stmtDelete) {

            $pmtSql = "DELETE FROM `RmBillBreakup` WHERE ArrivalID='$RmArrivalID'";

            $stmtpmtDelete = $this->conn->prepare($pmtSql);
            $stmtpmtDelete->execute();
            if ($stmtpmtDelete) {
                foreach ($existingBillBreakups as $billBreakup) {
                    $this->auditLogger->logDelete('RmBillBreakup', $billBreakup['BreakUpID'], $billBreakup, $RequestBody, [
                        'ReferenceNo' => $RmArrivalID,
                        'Remarks' => 'Bill breakup deleted from bill list',
                    ]);
                }
            }

            $pmtSql = "update FROM `RmArrivalDetatils` set ArrivalBillTds='0',ArrivalHarvestTds ='0' WHERE RmArrivalID='$RmArrivalID'";

            $stmtpmtDelete = $this->conn->prepare($pmtSql);
            $stmtpmtDelete->execute();

            if ($stmtpmtDelete) {
                $BrkUpData = $this->GetRmBillListDetails($RequestBody);
                $Data['Error'] = null;
                $Data['Msg'] = 'BreakUp Deleted Successfully';
                $Data['Data'] = $BrkUpData['Data'];
                return $Data;
            }
        } else {
            $Data['Error'] = 'Unable to Delete BreakUp';
            $Data['Msg'] = 'Unable to Delete BreakUp';
            return $Data;
        }
    }
    public function getDeleteAccess($ArrivalID)
    {
        // echo 
        $sqlDeleteAccess = "SELECT * FROM `RmPaymentBreakup` WHERE ArrivalID='$ArrivalID'";
        $stmtDeleteAccess = $this->conn->prepare($sqlDeleteAccess);
        $stmtDeleteAccess->execute();

        // echo $stmtDeleteAccess->rowCount();
        if ($stmtDeleteAccess->rowCount() == 0) {

            $DeleteAccess = true;
        } else {
            $DeleteAccess = false;
        }
        //    echo $DeleteAccess;
        return $DeleteAccess;
    }
}
