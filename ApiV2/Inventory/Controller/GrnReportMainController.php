<?php
include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class GrnReportMainController
{
    private $conn;
    private $auditLogger;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->auditLogger = new CentralAuditLogger($this->conn, 'Inventory');
    }

     public function updatePoStatus($RequestBody)
    {

        $poId = isset($RequestBody['poId']) ? $RequestBody['poId'] : '';
        $existingGrn = $this->auditLogger->fetchRowById('GoodsReceiptNote', 'Id', $poId);
        $sql = "UPDATE GoodsReceiptNote SET `Status` = 1 WHERE Id=$poId";
        $stmt = $this->conn->prepare($sql);
        if ($stmt->execute()) {
            $updatedGrn = $this->auditLogger->fetchRowById('GoodsReceiptNote', 'Id', $poId);
            $this->auditLogger->logUpdate('GoodsReceiptNote', $poId, $existingGrn, $updatedGrn, $RequestBody, [
                'ReferenceNo' => isset($updatedGrn['Code']) ? $updatedGrn['Code'] : $poId,
                'Remarks' => 'Inventory goods receipt note status updated',
            ]);

            $Data['Error'] = null;
            $Data['status'] = "success";
            $Data['Msg'] = "Proforma Updated Successfully";
            $Data['Data'] = "Proforma Updated Successfully";
        } else {
            $Data['Error'] = "Proforma Update Failed";
            $Data['status'] = "FAIL";
            $Data['Msg'] = "Proforma Update Failed";
        }


        return $Data;
    }
    // Fetch GRN list for report
    public function loadGrnReport($filters)
    {

        // echo print_r($filters);exit;
        $sql = "SELECT PM.Id,
                       PM.PoNo,
                       PM.InvoiceNo,
                       PM.Code,
                       PM.Date,
                       PM.Status,
                       V.VendorName
                FROM GoodsReceiptNote AS PM
                LEFT JOIN Vendors AS V ON PM.VendorId = V.VendorID
                WHERE 1=1";

        // Apply filters dynamically
        if (!empty($filters['Status']) && strtolower($filters['Status']) != 'all') {
            $Status = 0;
            if ($filters['Status'] == "Pending") {
                $Status = 1;
            } else if ($filters['Status'] == "Completed") {
                $Status = 2;
            } else {
                $Status = 0;
            }
            $sql .= " AND PM.Status = $Status";
        }

        if (!empty($filters['FromDate']) && !empty($filters['ToDate'])) {
            $sql .= " AND PM.Date BETWEEN '" . $filters['FromDate'] . "' AND '" . $filters['ToDate'] . "'";
        }

        if (!empty($filters['doc_no'])) {
            $sql .= " AND PM.PoNo LIKE '" . $filters['doc_no'] . "'";
        }

        $sql .= " ORDER BY PM.Id DESC";
        // echo $sql;exit;
         $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $result = [];
        $sn = 1;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = [
                'SNo' => $sn++,
                'Id' => $row['Id'],
                'PoNo' => $row['PoNo'],
                'InvoiceNo' => $row['InvoiceNo'],
                'Code' => $row['Code'],
                'Date' => $row['Date'],
                'Status' => $row['Status'],
                'VendorName' => $row['VendorName']
            ];
        }

        return [
            'status' => 'success',
            'Msg' => 'GRN Report fetched successfully',
            'Error' => null,
            'Data' => $result
        ];
    }
}
