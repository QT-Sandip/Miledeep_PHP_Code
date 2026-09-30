<?php
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
class PmPurchaseReportMainController
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
        $Status     = $RequestBody['Status'];
        $SearchKey  = $RequestBody['SearchKey'] ;
        $LoadAll    = $RequestBody['LoadAll'] ;

        // Base query
        $Query = "WHERE 1=1";
        $params = [];

        // Date & Status filters
        if (!$LoadAll) {
            if ($FromDate != '' && $ToDate != '') {
                $Query .= " AND DATE(PoDate) BETWEEN :from_date AND :to_date";
                $params[':from_date'] = $FromDate;
                $params[':to_date']   = $ToDate;
            }
            if ($Status != '') {
                $Query .= " AND Status = :status";
                $params[':status'] = $Status;
            }
        }

        // Search filter (PoID or Supplier)
        if ($SearchKey != '') {
            $Query .= " AND (PoID LIKE :searchKey OR Supplier LIKE :searchKey)";
            $params[':searchKey'] = '%' . $SearchKey . '%';
        }

        // Final SQL
        $sql = "SELECT PoID, ProformaID, DATE_FORMAT(PoDate, '%d-%m-%Y') AS PoDate,
                   Supplier, PaymentTerms, FreightType, FreightCharges, Subject, Narration, Status
            FROM PmPurchaseOrder
            $Query
            ORDER BY PoDate DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        $List = [];
        while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
            $List[] = $row;
        }

        return ['Error' => null, 'Msg' => 'Success', 'Data' => $List];
    }


    public function updatePurchaseStatus($RequestBody)
{
    $purchaseId = $RequestBody['purchaseId'] ;
    $existingPurchase = $this->auditLogger->fetchRowById('PmPurchaseOrder', 'PoID', $purchaseId);

 
    $sql = "
        UPDATE PmPurchaseOrder
        SET Status = '1'
        WHERE PoID = :id AND Status = '0'
    ";

    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':id', $purchaseId, PDO::PARAM_INT);
    $stmt->execute();

    $rowsAffected = $stmt->rowCount();
    if ($rowsAffected > 0) {
        $updatedPurchase = $this->auditLogger->fetchRowById('PmPurchaseOrder', 'PoID', $purchaseId);
        $this->auditLogger->logUpdate('PmPurchaseOrder', $purchaseId, $existingPurchase, $updatedPurchase, $RequestBody, [
            'ReferenceNo' => $purchaseId,
            'Remarks' => 'Inventory packaging purchase order status updated',
        ]);
    }

    return [
        'status' => $rowsAffected > 0 ? 'success' : 'failed',
        'rowsAffected' => $rowsAffected
    ];
}

}
