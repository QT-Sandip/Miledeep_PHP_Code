<?php
include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class PriceMasterController
{
    private $conn;
    private $Error = null;
    private $auditLogger;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->auditLogger = new CentralAuditLogger($this->conn, 'Inventory');
    }

    public function postPriceMaster($RequestBody)
    {
        // echo print_r($RequestBody);exit;
        $PriceMaster_id = isset($RequestBody['PriceMaster_id']) ? $RequestBody['PriceMaster_id'] : '';
        $item_id = isset($RequestBody['item_id']) ? $RequestBody['item_id'] : 0;
        $vendor_id = (isset($RequestBody['vendor_id']) && $RequestBody['vendor_id'] != '') ? $RequestBody['vendor_id'] : 0;
        $from_date = isset($RequestBody['from_date']) ? $RequestBody['from_date'] : '';
        $units = isset($RequestBody['units']) ? $RequestBody['units'] : '';
        $unit_price = isset($RequestBody['unit_price']) ? $RequestBody['unit_price'] : 0;
        $narration = isset($RequestBody['narration']) ? $RequestBody['narration'] : '';
        $approved_by = isset($RequestBody['approved_by']) ? $RequestBody['approved_by'] : '';


        // Check duplicate item_id
//     $check = $iconn->prepare("SELECT * FROM prices WHERE item_id=? AND vendor_id=?");
//     $check->bind_param("ss", $item_id,$vendor_id);
//     $check->execute();
//     $check_result = $check->get_result();
//     if ($check_result->num_rows > 0) {
//         echo json_encode(['status' => 'error', 'msg' => 'Price ID already exists']);
//         exit;
//     }

        $existingPrice = null;
        if ($PriceMaster_id != '') {
            $existingPrice = $this->auditLogger->fetchRowById('PriceMaster', 'Id', $PriceMaster_id);
        }

        if ($PriceMaster_id == '') {

            $sql = "INSERT INTO PriceMaster (VendorID,GeneralItemId, UnitPrice, FromDate, Units,Narration,ApprovedBy) VALUES ($vendor_id,$item_id,$unit_price,'$from_date','$units','$narration','$approved_by')";
        } else {
            $sql = "UPDATE PriceMaster SET
            VendorID=$vendor_id,GeneralItemId=$item_id,UnitPrice=$unit_price,FromDate='$from_date',Units='$units',Narration='$narration',ApprovedBy='$approved_by'
            WHERE Id=$PriceMaster_id";
        }

        // echo $sql;exit;
        $stmt = $this->conn->prepare($sql);
        if ($stmt->execute()) {
            $LastID = $PriceMaster_id == '' ? $this->conn->lastInsertId() : $PriceMaster_id;
            $savedPrice = $this->auditLogger->fetchRowById('PriceMaster', 'Id', $LastID);
            if ($PriceMaster_id == '') {
                $this->auditLogger->logInsert('PriceMaster', $LastID, $savedPrice, $RequestBody, [
                    'ReferenceNo' => $item_id,
                    'Remarks' => 'Inventory price master created',
                ]);
            } else {
                $this->auditLogger->logUpdate('PriceMaster', $LastID, $existingPrice, $savedPrice, $RequestBody, [
                    'ReferenceNo' => $item_id,
                    'Remarks' => 'Inventory price master updated',
                ]);
            }
            $Data['Error'] = null;
            $Data['status'] = "success";
            $Data['Msg'] = "Data Saved Successfully";
            $Data['LastInsertedID'] = $LastID;
            $Data['Data'] = $this->loadPriceMaster($RequestBody);
        } else {
    $error = $stmt->errorInfo();            // get detailed DB error
    $Data['Error'] = $error[2];             // keep DB error separately
    $Data['status'] = "fail";

    // Simple message without repeating "Error"
    $Data['Msg'] = " While saving Price data";

    $Data['Data'] = [];
}


        return $Data;
    }

    public function getPriceMaster($RequestBody)
    {

        $PriceMaster_id = isset($RequestBody['PriceMaster_id']) ? $RequestBody['PriceMaster_id'] : '';
        $sql = "SELECT PM.Id,PM.VendorID,GI.name,PM.GeneralItemId, UnitPrice, FromDate, Units,Narration,ApprovedBy,VS.VendorName FROM PriceMaster AS PM 
        LEFT JOIN GeneralItems AS GI ON PM.GeneralItemId=GI.GeneralItemId LEFT JOIN Vendors AS VS ON PM.VendorID=VS.VendorID WHERE PM.Id=$PriceMaster_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $PriceMaster = $stmt->fetch(PDO::FETCH_ASSOC);
        $ArrayPriceMaster['SNo'] = 1;
        $ArrayPriceMaster['PriceMasterId'] = $PriceMaster['Id'];
        $ArrayPriceMaster['VendorID'] = $PriceMaster['VendorID'];
        $ArrayPriceMaster['VendorName'] = $PriceMaster['VendorName'];
        $ArrayPriceMaster['name'] = $PriceMaster['name'];
        $ArrayPriceMaster['UnitPrice'] = $PriceMaster['UnitPrice'];
        $ArrayPriceMaster['FromDate'] = $PriceMaster['FromDate'];
        $ArrayPriceMaster['Units'] = $PriceMaster['Units'];
        $ArrayPriceMaster['GeneralItemId'] = $PriceMaster['GeneralItemId'];
        $ArrayPriceMaster['Narration'] = $PriceMaster['Narration'];
        $ArrayPriceMaster['ApprovedBy'] = $PriceMaster['ApprovedBy'];
        $Data['Error'] = null;
        $Data['status'] = "success";
        $Data['Msg'] = $PriceMaster ? "Success" : "No Data found";
        $Data['Data'] = (is_array($ArrayPriceMaster) || isset($ArrayPriceMaster)) ? $ArrayPriceMaster : [];

        return $Data;
    }

    public function deletePriceMaster($RequestBody)
    {
        $PriceMaster_id = isset($RequestBody['PriceMaster_id']) ? $RequestBody['PriceMaster_id'] : '';
        $existingPrice = $this->auditLogger->fetchRowById('PriceMaster', 'Id', $PriceMaster_id);
        $stmt = $this->conn->prepare("DELETE FROM PriceMaster WHERE Id=$PriceMaster_id");

        if ($stmt->execute()) {
            $this->auditLogger->logDelete('PriceMaster', $PriceMaster_id, $existingPrice, $RequestBody, [
                'ReferenceNo' => isset($existingPrice['GeneralItemId']) ? $existingPrice['GeneralItemId'] : $PriceMaster_id,
                'Remarks' => 'Inventory price master deleted',
            ]);
            $Data['Error'] = null;
            $Data['status'] = "success";
            $Data['Msg'] = "Item deleted successfully";
            $Data['PriceMaster_id'] = $PriceMaster_id;
            $Data['Data'] = $this->loadPriceMaster($RequestBody);
        } else {
            $error = $stmt->errorInfo();
            $Data['Error'] = $error[2];
            $Data['status'] = "fail";
            $Data['Msg'] = "Error deleting data";
            $Data['Data'] = [];
        }

        return $Data;
    }

    public function loadPriceMaster($RequestBody)
    {

        $sql = "SELECT PM.Id,PM.VendorID,GI.name,PM.GeneralItemId, UnitPrice, FromDate, Units,Narration,ApprovedBy,VS.VendorName FROM PriceMaster AS PM 
        LEFT JOIN GeneralItems AS GI ON PM.GeneralItemId=GI.GeneralItemId LEFT JOIN Vendors AS VS ON PM.VendorID=VS.VendorID ORDER BY PM.Id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
       
        $PriceMasterList = [];
        $i = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayPriceMaster['SNo'] = ++$i;
            $ArrayPriceMaster['PriceMasterId'] = $dataRow['Id'];
            $ArrayPriceMaster['VendorID'] = $dataRow['VendorID'];
            $ArrayPriceMaster['VendorName'] = $dataRow['VendorName'];
            $ArrayPriceMaster['itemName'] = $dataRow['name'];
            $ArrayPriceMaster['UnitPrice'] = $dataRow['UnitPrice'];
            $ArrayPriceMaster['FromDate'] = $dataRow['FromDate'];
            $ArrayPriceMaster['Units'] = $dataRow['Units'];
            $ArrayPriceMaster['GeneralItemId'] = $dataRow['GeneralItemId'];
             $ArrayPriceMaster['Narration'] = $dataRow['Narration'];
            $ArrayPriceMaster['ApprovedBy'] = $dataRow['ApprovedBy'];
            $PriceMasterList[] = $ArrayPriceMaster;
        }

        $Data['Error'] = null;
        $Data['status'] = "success";
        $Data['Msg'] = "Success";
        $Data['Data'] = $PriceMasterList;

        return $Data;
    }
}
