<?php
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
class PmproformaReportMainController
{
    private $conn;
    private $auditLogger;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->auditLogger = new CentralAuditLogger($this->conn, 'Inventory');
    }

    public function getPurchaseReport($RequestBody)
    {
        $FromDate   = $RequestBody['FromDate'] ;
        $ToDate     = $RequestBody['ToDate'] ;
        $Status     = $RequestBody['Status'] ;
        $SearchKey  = $RequestBody['SearchKey'] ;
        $LoadAll    = $RequestBody['LoadAll'] ;

        // Base query
        $Query = "WHERE 1=1";
        $params = [];

        // Date & Status filters
        if (!$LoadAll) {
            if ($FromDate != '' && $ToDate != '') {
                $Query .= " AND DATE(PiDate) BETWEEN '$FromDate' AND '$ToDate'";
            }
            if ($Status != '') {
                $Query .= " AND Status = $Status";
            }
        }

        // Search filter (PoID or Supplier)
        if ($SearchKey != '') {
            $Query .= " AND (PiID LIKE :searchKey OR Supplier LIKE :searchKey)";
            $params[':searchKey'] = '%' . $SearchKey . '%';
        }

        // Final SQL
        $sql = "SELECT *
            FROM PmProformaInvoice
            $Query
            ORDER BY PiDate DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();

        $List = [];
        while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
            $List[] = $row;
        }

        return ['Error' => null, 'Msg' => 'Success', 'Data' => $List];
    }


    public function updatePurchaseStatus($RequestBody)
{
    $purchaseId = $RequestBody['purchaseId'];
    $existingPurchase = $this->auditLogger->fetchRowById('PmProformaInvoice', 'PiID', $purchaseId);

 
    $sql = "
        UPDATE PmProformaInvoice
        SET Status = '1'
        WHERE PiID = :id AND Status = '0'
    ";

    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':id', $purchaseId, PDO::PARAM_INT);
    $stmt->execute();

    $rowsAffected = $stmt->rowCount();
    if ($rowsAffected > 0) {
        $updatedPurchase = $this->auditLogger->fetchRowById('PmProformaInvoice', 'PiID', $purchaseId);
        $this->auditLogger->logUpdate('PmProformaInvoice', $purchaseId, $existingPurchase, $updatedPurchase, $RequestBody, [
            'ReferenceNo' => isset($updatedPurchase['DocNo']) ? $updatedPurchase['DocNo'] : $purchaseId,
            'Remarks' => 'Inventory packaging proforma invoice status updated',
        ]);
    }

    return [
        'status' => $rowsAffected > 0 ? 'success' : 'failed',
        'rowsAffected' => $rowsAffected
    ];
}

}
