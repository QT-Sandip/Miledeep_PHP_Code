<?php
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';
class PmGoodsNoteReportMainController
{
    private $conn;
    private $auditLogger;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->auditLogger = new CentralAuditLogger($this->conn, 'Inventory');
    }

    public function getGoodsReport($RequestBody)
    {
        $FromDate   = $RequestBody['FromDate'] ;
        $ToDate     = $RequestBody['ToDate'] ;
        $Status     = $RequestBody['Status'] ;
        $SearchKey  = $RequestBody['SearchKey'];
        $LoadAll    = $RequestBody['LoadAll'] ;

        /* ---------------- BASE QUERY ---------------- */
        $Query  = "WHERE 1=1";
        $params = [];

        /* ---------------- DATE & STATUS FILTER ---------------- */
        if (!$LoadAll) {

            if ($FromDate != '' && $ToDate != '') {
                $Query .= " AND DATE(Date) BETWEEN :from_date AND :to_date";
                $params[':from_date'] = $FromDate;
                $params[':to_date']   = $ToDate;
            }

            if ($Status != '') {
                $Query .= " AND status = :status";
                $params[':status'] = $Status;
            }
        }

        /* ---------------- SEARCH FILTER ---------------- */
        if ($SearchKey != '') {
            $Query .= " AND (DocNo LIKE :searchKey OR InvoiceNumber LIKE :searchKey)";
            $params[':searchKey'] = '%' . $SearchKey . '%';
        }

        /* ---------------- FINAL SQL ---------------- */
        $sql = "
            SELECT 
                ID,
                DocNo,
                DATE_FORMAT(Date, '%d-%m-%Y') AS Date,
                VendorId,
                FreightType,
                IsGST,
                InvoiceNumber,
                InvoiceDate,
                CreditNoteType,
                Narration,
                status
            FROM PmGoodsReceiptNote
            $Query
            ORDER BY Date DESC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        $List = [];
        while ($row = $stmt->fetch(PDO::FETCH_OBJ)) {
            $List[] = $row;
        }

        return [
            'Error' => null,
            'Msg'   => 'Success',
            'Data'  => $List
        ];
    }


 public function updateGoodsStatus($RequestBody)
{
    $grnId = (int) ($RequestBody['grnId']);
    $existingGrn = $this->auditLogger->fetchRowById('PmGoodsReceiptNote', 'ID', $grnId);

    $sql = "
        UPDATE PmGoodsReceiptNote
        SET status = 1
        WHERE ID = :id AND status = 0
    ";

    $stmt = $this->conn->prepare($sql);
    $stmt->bindParam(':id', $grnId, PDO::PARAM_INT);
    $stmt->execute();
    if ($stmt->rowCount() > 0) {
        $updatedGrn = $this->auditLogger->fetchRowById('PmGoodsReceiptNote', 'ID', $grnId);
        $this->auditLogger->logUpdate('PmGoodsReceiptNote', $grnId, $existingGrn, $updatedGrn, $RequestBody, [
            'ReferenceNo' => isset($updatedGrn['DocNo']) ? $updatedGrn['DocNo'] : $grnId,
            'Remarks' => 'Inventory packaging goods receipt note status updated',
        ]);
    }

    return [
        'status' => $stmt->rowCount() > 0 ? 'success' : 'failed'
    ];
}


}
