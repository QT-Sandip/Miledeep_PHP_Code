<?php
include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class PoGeneralReportMainController
{
    private $conn;
    private $auditLogger;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->auditLogger = new CentralAuditLogger($this->conn, 'Inventory');
    }


    public function loadPoGeneralReport($filters = [])
    {
        $sql = "SELECT PO.Id, PO.DocNo, PO.Code, PO.Date, PO.Status, S.VendorName AS Supplier
            FROM GsProformaOrder AS PO
             LEFT JOIN Vendors AS S ON PO.Supplier = S.VendorID
            WHERE 1=1";

        $params = [];

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
            $sql .= " AND PO.Status = $Status";
        }

        if (!empty($filters['FromDate']) && !empty($filters['ToDate'])) {
            $sql .= " AND PO.Date BETWEEN '" . $filters['FromDate'] . "' AND '" . $filters['ToDate'] . "'";
        }

        if (!empty($filters['doc_no'])) {
            $sql .= " AND PO.PoNo LIKE '%" . $filters['doc_no'] . "%'";
        }

        $sql .= " ORDER BY PO.Id DESC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);

        $result = [];
        $sn = 1;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $result[] = [
                'SNo' => $sn++,
                'Id' => $row['Id'],
                'DocNo' => $row['DocNo'],
                'Code' => $row['Code'],
                'Date' => $row['Date'],
                'Supplier' => $row['Supplier'] ,
                'Status' => $row['Status']
            ];
        }

        return [
            'status' => 'success',
            'Msg' => 'PO Report fetched successfully',
            'Error' => null,
            'Data' => $result
        ];
    }

    public function updatePoStatus($RequestBody)
    {

        $poId = isset($RequestBody['poId']) ? $RequestBody['poId'] : '';
        $existingPo = $this->auditLogger->fetchRowById('GsProformaOrder', 'Id', $poId);
        $sql = "UPDATE GsProformaOrder SET `Status` = 1 WHERE Id=$poId";
        $stmt = $this->conn->prepare($sql);
        if ($stmt->execute()) {
            $updatedPo = $this->auditLogger->fetchRowById('GsProformaOrder', 'Id', $poId);
            $this->auditLogger->logUpdate('GsProformaOrder', $poId, $existingPo, $updatedPo, $RequestBody, [
                'ReferenceNo' => isset($updatedPo['Code']) ? $updatedPo['Code'] : $poId,
                'Remarks' => 'Inventory general stores PO status updated',
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


}
