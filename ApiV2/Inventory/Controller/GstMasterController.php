<?php
include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class GstMasterController
{
    private $conn;
    private $Error = null;
    private $auditLogger;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->auditLogger = new CentralAuditLogger($this->conn, 'Inventory');
    }

    public function postGstMaster($RequestBody)
    {
        // echo print_r($RequestBody);exit;
        $GstMaster_id = isset($RequestBody['GstMaster_id']) ? $RequestBody['GstMaster_id'] : '';
        $item_id = isset($RequestBody['item_id']) ? $RequestBody['item_id'] : 0;
        $vendor_id = (isset($RequestBody['vendor_id']) && $RequestBody['vendor_id'] != '') ? $RequestBody['vendor_id'] : 0;
        $from_date = isset($RequestBody['from_date']) ? $RequestBody['from_date'] : '';
        $units = isset($RequestBody['units']) ? $RequestBody['units'] : '';
        $gst = isset($RequestBody['gst']) ? $RequestBody['gst'] : 0;
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

        $existingGst = null;
        if ($GstMaster_id != '') {
            $existingGst = $this->auditLogger->fetchRowById('GstMaster', 'Id', $GstMaster_id);
        }

        if ($GstMaster_id == '') {

            $sql = "INSERT INTO GstMaster (VendorID,GeneralItemId, Gst, FromDate, Units,Narration,ApprovedBy) VALUES ($vendor_id,$item_id,$gst,'$from_date','$units','$narration','$approved_by')";
        } else {
            $sql = "UPDATE GstMaster SET
            VendorID=$vendor_id,GeneralItemId=$item_id,Gst=$gst,FromDate='$from_date',Units='$units',Narration='$narration',ApprovedBy='$approved_by'
            WHERE Id=$GstMaster_id";
        }

        // echo $sql;exit;
        $stmt = $this->conn->prepare($sql);
        if ($stmt->execute()) {
            $LastID = $GstMaster_id == '' ? $this->conn->lastInsertId() : $GstMaster_id;
            $savedGst = $this->auditLogger->fetchRowById('GstMaster', 'Id', $LastID);
            if ($GstMaster_id == '') {
                $this->auditLogger->logInsert('GstMaster', $LastID, $savedGst, $RequestBody, [
                    'ReferenceNo' => $item_id,
                    'Remarks' => 'Inventory GST master created',
                ]);
            } else {
                $this->auditLogger->logUpdate('GstMaster', $LastID, $existingGst, $savedGst, $RequestBody, [
                    'ReferenceNo' => $item_id,
                    'Remarks' => 'Inventory GST master updated',
                ]);
            }
            $Data['Error'] = null;
            $Data['status'] = "success";
            $Data['Msg'] = "Data Saved Successfully";
            $Data['LastInsertedID'] = $LastID;
            $Data['Data'] = $this->loadGstMaster($RequestBody);
        }else {
    $error = $stmt->errorInfo();            // get detailed DB error
    $Data['Error'] = $error[2];             // keep DB error separately
    $Data['status'] = "fail";

    // Simple message without repeating "Error"
    $Data['Msg'] = " While saving GST data";

    $Data['Data'] = [];
}


        return $Data;
    }

    public function getGstMaster($RequestBody)
    {

        $GstMaster_id = isset($RequestBody['GstMaster_id']) ? $RequestBody['GstMaster_id'] : '';
        $sql = "SELECT PM.Id,PM.VendorID,GI.name,PM.GeneralItemId, PM.Gst, FromDate, Units,Narration,ApprovedBy,VS.VendorName FROM GstMaster AS PM 
        LEFT JOIN GeneralItems AS GI ON PM.GeneralItemId=GI.GeneralItemId LEFT JOIN Vendors AS VS ON PM.VendorID=VS.VendorID WHERE PM.Id=$GstMaster_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $GstMaster = $stmt->fetch(PDO::FETCH_ASSOC);
        $ArrayGstMaster['SNo'] = 1;
        $ArrayGstMaster['GstMasterId'] = $GstMaster['Id'];
        $ArrayGstMaster['VendorID'] = $GstMaster['VendorID'];
        $ArrayGstMaster['VendorName'] = $GstMaster['VendorName'];
        $ArrayGstMaster['name'] = $GstMaster['name'];
        $ArrayGstMaster['Gst'] = $GstMaster['Gst'];
        $ArrayGstMaster['FromDate'] = $GstMaster['FromDate'];
        $ArrayGstMaster['Units'] = $GstMaster['Units'];
        $ArrayGstMaster['GeneralItemId'] = $GstMaster['GeneralItemId'];
        $ArrayGstMaster['Narration'] = $GstMaster['Narration'];
        $ArrayGstMaster['ApprovedBy'] = $GstMaster['ApprovedBy'];
        $Data['Error'] = null;
        $Data['status'] = "success";
        $Data['Msg'] = $GstMaster ? "Success" : "No Data found";
        $Data['Data'] = (is_array($ArrayGstMaster) || isset($ArrayGstMaster)) ? $ArrayGstMaster : [];

        return $Data;
    }

    public function deleteGstMaster($RequestBody)
    {
        $GstMaster_id = isset($RequestBody['GstMaster_id']) ? $RequestBody['GstMaster_id'] : '';
        $existingGst = $this->auditLogger->fetchRowById('GstMaster', 'Id', $GstMaster_id);
        $stmt = $this->conn->prepare("DELETE FROM GstMaster WHERE Id=$GstMaster_id");

        if ($stmt->execute()) {
            $this->auditLogger->logDelete('GstMaster', $GstMaster_id, $existingGst, $RequestBody, [
                'ReferenceNo' => isset($existingGst['GeneralItemId']) ? $existingGst['GeneralItemId'] : $GstMaster_id,
                'Remarks' => 'Inventory GST master deleted',
            ]);
            $Data['Error'] = null;
            $Data['status'] = "success";
            $Data['Msg'] = "Data deleted successfully";
            $Data['GstMaster_id'] = $GstMaster_id;
            $Data['Data'] = $this->loadGstMaster($RequestBody);
        } else {
            $error = $stmt->errorInfo();
            $Data['Error'] = $error[2];
            $Data['status'] = "fail";
            $Data['Msg'] = "Error deleting data";
            $Data['Data'] = [];
        }

        return $Data;
    }

    public function loadGstMaster($RequestBody)
    {

        $sql = "SELECT PM.Id,PM.VendorID,GI.name,PM.GeneralItemId, PM.Gst, FromDate, Units,Narration,ApprovedBy,VS.VendorName FROM GstMaster AS PM 
        LEFT JOIN GeneralItems AS GI ON PM.GeneralItemId=GI.GeneralItemId LEFT JOIN Vendors AS VS ON PM.VendorID=VS.VendorID ORDER BY PM.Id DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
       
        $GstMasterList = [];
        $i = 0;
        while ($dataRow = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $ArrayGstMaster['SNo'] = ++$i;
            $ArrayGstMaster['GstMasterId'] = $dataRow['Id'];
            $ArrayGstMaster['VendorID'] = $dataRow['VendorID'];
            $ArrayGstMaster['VendorName'] = $dataRow['VendorName'];
            $ArrayGstMaster['itemName'] = $dataRow['name'];
            $ArrayGstMaster['Gst'] = $dataRow['Gst'];
            $ArrayGstMaster['FromDate'] = $dataRow['FromDate'];
            $ArrayGstMaster['Units'] = $dataRow['Units'];
            $ArrayGstMaster['GeneralItemId'] = $dataRow['GeneralItemId'];
            $ArrayGstMaster['Narration'] = $dataRow['Narration'];
            $ArrayGstMaster['ApprovedBy'] = $dataRow['ApprovedBy'];

            $GstMasterList[] = $ArrayGstMaster;
        }

        $Data['Error'] = null;
        $Data['status'] = "success";
        $Data['Msg'] = "Success";
        $Data['Data'] = $GstMasterList;

        return $Data;
    }
}
