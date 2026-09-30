<?php
include_once '../Global/Util.php';
// include_once 'Controller/UtilController.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class PorformaRegisterCreateController
{
    private $conn;
    private $utilController;
    private $Error = null;
    private $auditLogger;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->auditLogger = new CentralAuditLogger($this->conn, 'Inventory');
        // $this->utilController = new UtilController($this->conn);
    }

    // ===================== SAVE / UPDATE PROFORMA =====================
    public function postProformaDetails($RequestBody)
    {
        $ProformaID         = isset($RequestBody['proforma_id']) ? $RequestBody['proforma_id'] : '';
        $ShipmentDate       = isset($RequestBody['shipment_date']) ? $RequestBody['shipment_date'] : '';
        $ProNo              = isset($RequestBody['pro_no']) ? $RequestBody['pro_no'] : '';
        $CountryOrigin      = isset($RequestBody['country_origin']) ? $RequestBody['country_origin'] : '';
        $CountryDestination = isset($RequestBody['country_destination']) ? $RequestBody['country_destination'] : '';
        $FinalDestination   = isset($RequestBody['final_destination']) ? $RequestBody['final_destination'] : '';
        $PortDischarge      = isset($RequestBody['port_discharge']) ? $RequestBody['port_discharge'] : '';
        $POApprovalDate     = isset($RequestBody['po_approval_date']) ? $RequestBody['po_approval_date'] : '';
        $ProcessPackedBy    = isset($RequestBody['process_packed_by']) ? $RequestBody['process_packed_by'] : '';
        $IsGlaze            = isset($RequestBody['is_glaze']) ? $RequestBody['is_glaze'] : '';
        $DeliveryTerms      = isset($RequestBody['delivery_terms']) ? $RequestBody['delivery_terms'] : '';
        $BuyerOther         = isset($RequestBody['buyer_other']) ? $RequestBody['buyer_other'] : '';
        $AmountWords        = isset($RequestBody['amount_words']) ? $RequestBody['amount_words'] : '';
        $AdvanceAmount      = isset($RequestBody['advance_amount']) ? $RequestBody['advance_amount'] : '';
        $POAttachment       = isset($RequestBody['po_attachment']) ? $RequestBody['po_attachment'] : '';

        // Newly added fields
        $TotalQuantity      = isset($RequestBody['total_quantity']) ? $RequestBody['total_quantity'] : '';
        $Commission         = isset($RequestBody['commission']) ? $RequestBody['commission'] : '';
        $CommissionValue    = isset($RequestBody['commission_value']) ? $RequestBody['commission_value'] : '';
        $ApprovedBy         = isset($RequestBody['approved_by']) ? $RequestBody['approved_by'] : '';

        $CurrentDateTime    = getCurrentDateTime()['TimeStamp'];

        try {
            $existingProforma = null;
            if ($ProformaID != '' && $ProformaID != '0') {
                $existingProforma = $this->auditLogger->fetchRowById('proforma_register_create', 'ProformaID', $ProformaID);
            }
            if ($ProformaID == '' || $ProformaID == '0') {
                // ===================== INSERT =====================
                $sql = "INSERT INTO proforma_register_create (
                            ShipmentDate, ProNo, CountryOrigin, CountryDestination, FinalDestination,
                            PortDischarge, POApprovalDate, ProcessPackedBy, IsGlaze, DeliveryTerms,
                            BuyerOther, AmountWords, AdvanceAmount, POAttachment,
                            TotalQuantity, Commission, CommissionValue, ApprovedBy, CreatedDate
                        ) VALUES (
                            '$ShipmentDate', '$ProNo', '$CountryOrigin', '$CountryDestination', '$FinalDestination',
                            '$PortDischarge', '$POApprovalDate', '$ProcessPackedBy', '$IsGlaze', '$DeliveryTerms',
                            '$BuyerOther', '$AmountWords', '$AdvanceAmount', '$POAttachment',
                            '$TotalQuantity', '$Commission', '$CommissionValue', '$ApprovedBy', '$CurrentDateTime'
                        )";
                $stmt = $this->conn->prepare($sql);
            } else {
                // ===================== UPDATE =====================
                $sql = "UPDATE proforma_register_create SET
                            ShipmentDate = '$ShipmentDate',
                            ProNo = '$ProNo',
                            CountryOrigin = '$CountryOrigin',
                            CountryDestination = '$CountryDestination',
                            FinalDestination = '$FinalDestination',
                            PortDischarge = '$PortDischarge',
                            POApprovalDate = '$POApprovalDate',
                            ProcessPackedBy = '$ProcessPackedBy',
                            IsGlaze = '$IsGlaze',
                            DeliveryTerms = '$DeliveryTerms',
                            BuyerOther = '$BuyerOther',
                            AmountWords = '$AmountWords',
                            AdvanceAmount = '$AdvanceAmount',
                            POAttachment = '$POAttachment',
                            TotalQuantity = '$TotalQuantity',
                            Commission = '$Commission',
                            CommissionValue = '$CommissionValue',
                            ApprovedBy = '$ApprovedBy',
                            UpdatedDate = '$CurrentDateTime'
                        WHERE ProformaID = $ProformaID";
                $stmt = $this->conn->prepare($sql);
            }

            if ($stmt->execute()) {
                if ($ProformaID == '' || $ProformaID == '0') {
                    $LastID = $this->conn->lastInsertId();
                    $savedProforma = $this->auditLogger->fetchRowById('proforma_register_create', 'ProformaID', $LastID);
                    $this->auditLogger->logInsert('proforma_register_create', $LastID, $savedProforma, $RequestBody, [
                        'ReferenceNo' => $ProNo,
                        'Remarks' => 'Inventory proforma register create record created',
                    ]);
                    $Data['Error'] = null;
                    $Data['Msg']   = 'Proforma record created successfully';
                    $Data['Data']  = ['ProformaID' => $LastID];
                } else {
                    $savedProforma = $this->auditLogger->fetchRowById('proforma_register_create', 'ProformaID', $ProformaID);
                    $this->auditLogger->logUpdate('proforma_register_create', $ProformaID, $existingProforma, $savedProforma, $RequestBody, [
                        'ReferenceNo' => $ProNo,
                        'Remarks' => 'Inventory proforma register create record updated',
                    ]);
                    $Data['Error'] = null;
                    $Data['Msg']   = 'Proforma record updated successfully';
                    $Data['Data']  = ['ProformaID' => $ProformaID];
                }
            } else {
                $error = $stmt->errorInfo();
                $Data['Error'] = isset($error[2]) ? $error[2] : 'Unknown database error';
                $Data['Msg']   = 'Error while saving Proforma Register: ' . (isset($error[2]) ? $error[2] : 'No further info');
                $Data['Data']  = [];
            }
        } catch (PDOException $e) {
            $Data['Error'] = $e->getMessage();
            $Data['Msg']   = 'Database error while saving Proforma Register';
            $Data['Data']  = [];
        }

        return $Data;
    }

    // ===================== GET SINGLE PROFORMA =====================
    public function getProforma($RequestBody)
    {
        $ProformaID = '';
        if (is_array($RequestBody)) {
            if (isset($RequestBody['ProformaID'])) {
                $ProformaID = $RequestBody['ProformaID'];
            } elseif (isset($RequestBody['proforma_id'])) {
                $ProformaID = $RequestBody['proforma_id'];
            }
            $ProformaID = trim($ProformaID);
        } else {
            $ProformaID = trim($RequestBody);
        }

        $sql = "SELECT * FROM proforma_register_create WHERE 1=1";
        if (!empty($ProformaID)) {
            $sql .= " AND ProformaID = $ProformaID";
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $record = $stmt->fetch(PDO::FETCH_ASSOC);

        $Data['Error'] = null;
        $Data['Msg']   = $record ? "Success" : "No record found";
        $Data['Data']  = $record ?: [];

        return $Data;
    }

    // ===================== DELETE PROFORMA =====================
    public function deleteProforma($RequestBody)
    {
        $ProformaID = isset($RequestBody['ProformaID']) ? trim($RequestBody['ProformaID']) : '';


        if (empty($ProformaID)) {
            return [
                'Error' => 'Missing Proforma ID',
                'Msg'   => 'Proforma ID not found in request',
                'Data'  => []
            ];
        }

        $existingProforma = $this->auditLogger->fetchRowById('proforma_register_create', 'ProformaID', $ProformaID);
        $sql = "DELETE FROM proforma_register_create WHERE ProformaID = $ProformaID";
        $stmt = $this->conn->prepare($sql);

        if ($stmt->execute()) {
            if ($stmt->rowCount() > 0) {
                $this->auditLogger->logDelete('proforma_register_create', $ProformaID, $existingProforma, $RequestBody, [
                    'ReferenceNo' => isset($existingProforma['ProNo']) ? $existingProforma['ProNo'] : $ProformaID,
                    'Remarks' => 'Inventory proforma register create record deleted',
                ]);
                $Data['Error'] = null;
                $Data['Msg']   = "Proforma deleted successfully";
                $Data['Data']  = ["ProformaID" => $ProformaID];
            } else {
                $Data['Error'] = "No record found";
                $Data['Msg']   = "No Proforma found with ID: $ProformaID";
                $Data['Data']  = [];
            }
        } else {
            $error = $stmt->errorInfo();
            $Data['Error'] = isset($error[2]) ? $error[2] : 'Unknown database error';
            $Data['Msg']   = "Error deleting Proforma: " . (isset($error[2]) ? $error[2] : 'No further info');
            $Data['Data']  = [];
        }

        return $Data;
    }

    // ===================== LOAD ALL PROFORMAS =====================
    public function loadProformas()
    {
        $sql = "SELECT * FROM proforma_register_create ORDER BY ProformaID DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute();
        $records = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $Data['Error'] = null;
        $Data['Msg']   = "Success";
        $Data['Data']  = $records;

        return $Data;
    }
}
?>
