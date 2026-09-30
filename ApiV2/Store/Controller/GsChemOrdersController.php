<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
// include_once 'Controller/UtilController.php';

class GsChemOrdersController
{
    private $conn;
    //	private $db_table = "ScrmApplicants";
    private $utilController;
    private $Error = null;
    private $Msg = '';
    private $auditLogger;


    // Db connection
    public function __construct($db)
    {
        $this->conn = $db;
        $this->auditLogger = new CentralAuditLogger($this->conn, 'Store');
        // $this->utilController = new UtilController($this->conn);
    }

    public function postChemOrderDetails($RequestBody)
    {
        $ReceivedID = $RequestBody['ReceivedID'];

        if ($ReceivedID == '') {
            $Data = $this->insertChemOrdersDetails($RequestBody);
        } else {
            $Data = $this->updateChemOrdersDetails($RequestBody);
        }

        return $Data;
    }

    public function insertChemOrdersDetails($RequestBody)
    {
        $ReceivedID = $RequestBody['ReceivedID'];
        $ChemPoID = $RequestBody['ChemPoID'];
        $BLNo = $RequestBody['BLNo'];
        $ContainerNo = $RequestBody['ContainerNo'];
        $ChemicalID = $RequestBody['ChemicalID'];
        $ReceivedBags = $RequestBody['ReceivedBags'];
        $QuantityReceived = $RequestBody['QuantityReceived'];
        $BillOfEntryNo = $RequestBody['BillOfEntryNo'];
        $BillDate = $RequestBody['BillDate'];
        $ClearedDate = $RequestBody['ClearedDate'];
        date_default_timezone_set("Asia/Kolkata");
        $PostedDate = date("Y-m-d H:i:s");
        $ProcessingPlantID = $RequestBody['ProcessingPlantID'];

        $QtyRcvd = $this->getQtyReceivedTotal($RequestBody);
        $PoQty = $this->getPoQtyTotal($RequestBody);

        $TotalQtyRcvd = $QtyRcvd['Data'][0]['TotalQtyRcvd'];
        $TotalPoQty = $PoQty['Data'][0]['TotalPoQty'];

        if ($TotalQtyRcvd + $ReceivedBags <= $TotalPoQty) {

            $sqlInsert = "INSERT INTO GsChemReceivedQtys(ReceivedID, ChemPoID, BLNo, ContainerNo, QuantityReceived,
            BillOfEntryNo,BillDate,ClearedDate,PostedDateNTime,ProcessingPlantID,ChemicalID,ReceivedBags) 
            VALUES ('$ReceivedID','$ChemPoID','$BLNo','$ContainerNo','$QuantityReceived','$BillOfEntryNo',
            '$BillDate','$ClearedDate','$PostedDate','$ProcessingPlantID','$ChemicalID','$ReceivedBags')";

            $stmtInsert = $this->conn->prepare($sqlInsert);
            $stmtInsert->execute();

            if ($stmtInsert) {

                $lastInsertedId = $this->conn->lastInsertId();
                $RequestBody['ReceivedID'] = $lastInsertedId;
                $createdOrder = $this->auditLogger->fetchRowById('GsChemReceivedQtys', 'ReceivedID', $lastInsertedId);
                $this->auditLogger->logInsert('GsChemReceivedQtys', $lastInsertedId, $createdOrder, $RequestBody, [
                    'ReferenceNo' => $ChemPoID,
                    'Remarks' => 'Store chemical receipt created',
                ]);

                $chemOrderData = $this->getChemOrderDetails($RequestBody);
                $TotalData = $this->getQtyReceivedTotal($RequestBody);
                $Data['Error'] = null;
                $Data['Msg'] = 'Order Posted Successfully';
                $Data['Data'] = $chemOrderData['Data'];
                $Data['Total'] = $TotalData['Data'];
                return $Data;
            } else {
                $Data['Error'] = 'Unable to Insert Order';
                $Data['Msg'] = $this->Error;
                return $Data;
            }
        } else {
            $Data['Error'] = 'Received Bags (' . ($TotalQtyRcvd + $ReceivedBags) . ') Exceeds PO Total Bags (' . ($TotalPoQty ? $TotalPoQty : 0) . ')';
            $Data['Msg'] = 'Received Bags (' . ($TotalQtyRcvd + $ReceivedBags) . ') Exceeds PO Total Bags (' . ($TotalPoQty ? $TotalPoQty : 0) . ')';
        }
        return $Data;
    }

    public function updateChemOrdersDetails($RequestBody)
    {
        $ReceivedID = $RequestBody['ReceivedID'];
        $ChemPoID = $RequestBody['ChemPoID'];
        $BLNo = $RequestBody['BLNo'];
        $ContainerNo = $RequestBody['ContainerNo'];
        $ChemicalID = $RequestBody['ChemicalID'];
        $ReceivedBags = $RequestBody['ReceivedBags'];
        $QuantityReceived = $RequestBody['QuantityReceived'];
        $BillOfEntryNo = $RequestBody['BillOfEntryNo'];
        $BillDate = $RequestBody['BillDate'];
        $ClearedDate = $RequestBody['ClearedDate'];
        date_default_timezone_set("Asia/Kolkata");
        $PostedDate = date("Y-m-d H:i:s");
        $ProcessingPlantID = $RequestBody['ProcessingPlantID'];
        $existingOrder = $this->auditLogger->fetchRowById('GsChemReceivedQtys', 'ReceivedID', $ReceivedID);

        $QtyRcvd = $this->getQtyReceivedTotal($RequestBody);
        $PoQty = $this->getPoQtyTotal($RequestBody);

        $TotalQtyRcvd = $QtyRcvd['Data'][0]['TotalQtyRcvd'];
        $TotalPoQty = $PoQty['Data'][0]['TotalPoQty'];

        if ($TotalQtyRcvd + $ReceivedBags <= $TotalPoQty) {

            $sqlUpdate = "UPDATE GsChemReceivedQtys SET ChemPoID='$ChemPoID',BLNo='$BLNo',ContainerNo='$ContainerNo',
            QuantityReceived='$QuantityReceived',BillOfEntryNo='$BillOfEntryNo',BillDate='$BillDate',ClearedDate='$ClearedDate',
            PostedDateNTime='$PostedDate',ProcessingPlantID='$ProcessingPlantID',ChemicalID='$ChemicalID',ReceivedBags='$ReceivedBags',
            WHERE ReceivedID='$ReceivedID'";

            $stmtUpdate = $this->conn->prepare($sqlUpdate);
            $stmtUpdate->execute();


            if ($stmtUpdate) {
                $updatedOrder = $this->auditLogger->fetchRowById('GsChemReceivedQtys', 'ReceivedID', $ReceivedID);
                $this->auditLogger->logUpdate('GsChemReceivedQtys', $ReceivedID, $existingOrder, $updatedOrder, $RequestBody, [
                    'ReferenceNo' => $ChemPoID,
                    'Remarks' => 'Store chemical receipt updated',
                ]);
                $chemOrderData = $this->getChemOrderDetails($RequestBody);
                $TotalData = $this->getQtyReceivedTotal($RequestBody);
                $Data['Error'] = null;
                $Data['Msg'] = 'Order Updated Successfully';
                $Data['Data'] = $chemOrderData['Data'];
                $Data['Total'] = $TotalData['Data'];
                return $Data;
            } else {
                $Data['Error'] = 'Unable to update Order';
                $Data['Msg'] = $this->Error;
                return $Data;
            }
        } else {
            $Data['Error'] = 'Received Bags (' . ($TotalQtyRcvd + $ReceivedBags) . ') Exceeds PO Total Bags (' . ($TotalPoQty ? $TotalPoQty : 0) . ')';
            $Data['Msg'] = 'Received Bags (' . ($TotalQtyRcvd + $ReceivedBags) . ') Exceeds PO Total Bags (' . ($TotalPoQty ? $TotalPoQty : 0) . ')';
            return $Data;
        }
    }

    function deleteChemOrderDetails($RequestBody)
    {
        $ReceivedID = $RequestBody['ReceivedID'];
        $existingOrder = $this->auditLogger->fetchRowById('GsChemReceivedQtys', 'ReceivedID', $ReceivedID);

        $sqlDelete = "DELETE FROM `GsChemReceivedQtys` WHERE ReceivedID='$ReceivedID'";

        $stmtDelete = $this->conn->prepare($sqlDelete);
        $stmtDelete->execute();

        if ($stmtDelete) {
            $this->auditLogger->logDelete('GsChemReceivedQtys', $ReceivedID, $existingOrder, $RequestBody, [
                'ReferenceNo' => isset($existingOrder['ChemPoID']) ? $existingOrder['ChemPoID'] : $ReceivedID,
                'Remarks' => 'Store chemical receipt deleted',
            ]);
            $chemOrderData = $this->getChemOrderDetails($RequestBody);
            $TotalData = $this->getQtyReceivedTotal($RequestBody);
            $Data['Error'] = null;
            $Data['Msg'] = 'Order Deleted Successfully';
            $Data['Data'] = $chemOrderData['Data'];
            $Data['Total'] = $TotalData['Data'];
            return $Data;
        } else {
            $Data['Error'] = 'Unable to Delete Order';
            $Data['Msg'] = $this->Error;
            return $Data;
        }
    }

    function getChemOrderDetails($RequestBody)
    {
        $ChemPoID = $RequestBody['ChemPoID'];
        $PlantID = $RequestBody['PlantID'];
        if($RequestBody['ChemicalID'] != ''){
        $QtyRcvd = $this->getQtyReceivedTotal($RequestBody);
        $PoQty = $this->getPoQtyTotal($RequestBody);

      $TotalQtyRcvd = isset($QtyRcvd['Data'][0]['TotalQtyRcvd']) ? $QtyRcvd['Data'][0]['TotalQtyRcvd'] : 0;
     $TotalPoQty = isset($PoQty['Data'][0]['TotalPoQty']) ? $PoQty['Data'][0]['TotalPoQty'] : 0;
        }else{
            $TotalQtyRcvd = 0;
            $TotalPoQty =0;
        }
        $Query = '';
        if ($PlantID != '') {
            $Query .= " AND GCRQ.ProcessingPlantID = '$PlantID'";
        }
        $sqlFields = "GCRQ.ReceivedID,GCRQ.ChemPoID,GCRQ.BLNo,GCRQ.ContainerNo,
        GCRQ.QuantityReceived,GCRQ.ReceivedBags, GCRQ.ChemicalID,
        GCRQ.BillOfEntryNo,GCRQ.BillDate,
        GCRQ.ClearedDate,GCRQ.ProcessingPlantID,GCPs.ChemPoNo,RPS.StationName,GCM.ChemicalName";

        $sqlQuery = "SELECT $sqlFields FROM GsChemReceivedQtys AS GCRQ
        LEFT JOIN GsChemicalPos AS GCPs ON GCRQ.ChemPoID = GCPs.ChemPoID
        LEFT JOIN RmProcessingStations AS RPS ON GCRQ.ProcessingPlantID = RPS.ProcessingStationID
        LEFT JOIN GsChemicalPoBreakUp AS GCPB ON GCRQ.ChemPoID = GCPB.ChemPoID
        LEFT JOIN GsChemicalMaster AS GCM ON GCM.ChemID = GCRQ.ChemicalID
        WHERE GCRQ.ChemPoID='$ChemPoID' $Query
        GROUP BY GCRQ.ReceivedID";

        $stmtSelect = $this->conn->prepare($sqlQuery);
        $stmtSelect->execute();

        $ArrayChemOrderData = [];
        $TotalReceivedBags = 0;
        $TotalReceivedQty = 0;
        $i = 0;
        while ($dataRow = $stmtSelect->fetch(PDO::FETCH_ASSOC)) {

            $ChemicalID = $dataRow['ChemicalID'];
            $ProcessingPlantID = $dataRow['ProcessingPlantID'];

            $sqlConsumeQty = "SELECT SUM(BagsUsed) AS TotalUsedBags FROM GsPlantWiseConsumptionDetails 
            WHERE ChemicalID = '$ChemicalID' AND PlantID = '$ProcessingPlantID'";
            $stmtConsumeQty = $this->conn->prepare($sqlConsumeQty);
            $stmtConsumeQty->execute();
            $dataConsumed = $stmtConsumeQty->fetch(PDO::FETCH_ASSOC);
            $TotalUsedBags = $dataConsumed['TotalUsedBags'];

            $ArrayChemOrderList['SNO'] = ++$i;
            $ArrayChemOrderList['ReceivedID'] = $dataRow['ReceivedID'];
            $ArrayChemOrderList['ChemPoID'] = $dataRow['ChemPoID'];
            $ArrayChemOrderList['ChemPoNo'] = $dataRow['ChemPoNo'];
            $ArrayChemOrderList['BLNo'] = $dataRow['BLNo'];
            $ArrayChemOrderList['ContainerNo'] = $dataRow['ContainerNo'];
            $ArrayChemOrderList['ReceivedBags'] = $dataRow['ReceivedBags'];
            $ArrayChemOrderList['QuantityReceived'] = $dataRow['QuantityReceived'];
            $ArrayChemOrderList['ChemicalID'] = $dataRow['ChemicalID'];
            $ArrayChemOrderList['BillOfEntryNo'] = $dataRow['BillOfEntryNo'];
            $ArrayChemOrderList['BillDate'] = $dataRow['BillDate'];
            $ArrayChemOrderList['ClearedDate'] = $dataRow['ClearedDate'];
            $ArrayChemOrderList['ProcessingPlantID'] = $dataRow['ProcessingPlantID'];
            $ArrayChemOrderList['StationName'] = $dataRow['StationName'];
            $ArrayChemOrderList['ChemicalName'] = $dataRow['ChemicalName'];
            if ($TotalUsedBags > 0) {
                $DeleteRow = "No";
            } else {
                $DeleteRow = "Yes";
            }
            $ArrayChemOrderList['DeleteRow'] = $DeleteRow;

            $TotalReceivedQty += $dataRow['QuantityReceived'];
            $TotalReceivedBags += $dataRow['ReceivedBags'];
            $ArrayChemOrderData[] = $ArrayChemOrderList;
        }

        $data['Error'] = null;
        $data['Msg'] = 'Success';
        $data['AddLink'] = "";
        $data['Data'] = $ArrayChemOrderData;
        $data['Total'] = [
            "TotalReceivedBags" => $TotalReceivedBags,
            "TotalReceivedQty" => $TotalReceivedQty,
            "BalanceBags" => ($TotalPoQty - $TotalQtyRcvd)
        ];

        return $data;
    }

    function getQtyReceivedTotal($RequestBody)
    {
        $ChemPoID = $RequestBody['ChemPoID'];
        $ChemicalID = $RequestBody['ChemicalID'];

        $sqlTotal = "SELECT SUM(ReceivedBags) AS TotalQtyRcvd FROM GsChemReceivedQtys WHERE ChemPoID='$ChemPoID' AND ChemicalID = '$ChemicalID'";

        $stmtTotal = $this->conn->prepare($sqlTotal);
        $stmtTotal->execute();

        $ArrayTotal = [];

        while ($dataRow = $stmtTotal->fetch(PDO::FETCH_ASSOC)) {

            $ArrayTotalData['TotalQtyRcvd'] = $dataRow['TotalQtyRcvd'];

            $ArrayTotal[] = $ArrayTotalData;
        }

        $data['Data'] = $ArrayTotal;
        return $data;
    }
    function getPoQtyTotal($RequestBody)
    {
        $ChemPoID = $RequestBody['ChemPoID'];
        $ChemicalID = $RequestBody['ChemicalID'];

        $sqlTotal = "SELECT SUM(QuantityInBags) AS TotalPoQty FROM GsChemicalPoBreakUp WHERE ChemPoID='$ChemPoID' AND ChemicalID = '$ChemicalID'";

        $stmtTotal = $this->conn->prepare($sqlTotal);
        $stmtTotal->execute();

        $ArrayTotal = [];

        while ($dataRow = $stmtTotal->fetch(PDO::FETCH_ASSOC)) {

            $ArrayTotalData['TotalPoQty'] = $dataRow['TotalPoQty'];

            $ArrayTotal[] = $ArrayTotalData;
        }

        $data['Data'] = $ArrayTotal;

        return $data;
    }
}
