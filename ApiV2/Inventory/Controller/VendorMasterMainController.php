<?php
include_once '../Global/Util.php';
include_once __DIR__ . '/../../Global/Audit/CentralAuditLogger.php';

class VendorMasterMainController
{
    private $conn;
    private $Error = null;
    private $auditLogger;

    public function __construct($db)
    {
        $this->conn = $db;
        $this->auditLogger = new CentralAuditLogger($this->conn, 'Inventory');
    }

   public function postVendorDetails($RequestBody)
{
    $VendorID   = isset($RequestBody['vendor_id']) ? $RequestBody['vendor_id'] : '';
    $VendorName = isset($RequestBody['vendor_name']) ? $RequestBody['vendor_name'] : '';
    $VendorCode = isset($RequestBody['vendor_code']) ? $RequestBody['vendor_code'] : '';
    $Email      = isset($RequestBody['email']) ? $RequestBody['email'] : '';
    $Address    = isset($RequestBody['address']) ? $RequestBody['address'] : '';
    $TelNo      = isset($RequestBody['tel_no']) ? $RequestBody['tel_no'] : '';
    $FaxNo      = isset($RequestBody['fax_no']) ? $RequestBody['fax_no'] : '';
    $DeliveryAddress = isset($RequestBody['delivery_address']) ? $RequestBody['delivery_address'] : '';
    $Pin        = isset($RequestBody['pin']) ? $RequestBody['pin'] : '';
    $GSTIN      = isset($RequestBody['gstin']) ? $RequestBody['gstin'] : '';
    $IGSTNumber = isset($RequestBody['igst_number']) ? $RequestBody['igst_number'] : '';
    $IGSTPercentage      = isset($RequestBody['igst_percentage']) && $RequestBody['igst_percentage'] != '' ? $RequestBody['igst_percentage'] : 0;
    $IGSTApplicableFrom  = isset($RequestBody['igst_applicable_from']) ? $RequestBody['igst_applicable_from'] : '';
    $State      = isset($RequestBody['state']) ? $RequestBody['state'] : '';
    $StateName  = isset($RequestBody['state_name']) ? $RequestBody['state_name'] : '';
    $IGSTStateName  = isset($RequestBody['igst_state_name']) ? $RequestBody['igst_state_name'] : '';

    $AccountType = isset($RequestBody['account_type']) ? $RequestBody['account_type'] : '';
    $CreditLimit = isset($RequestBody['credit_limit']) && $RequestBody['credit_limit'] != '' ? $RequestBody['credit_limit'] : 0;
    $CreditDays  = isset($RequestBody['credit_days']) && $RequestBody['credit_days'] != '' ? $RequestBody['credit_days'] : 0;
    $Group       = isset($RequestBody['group']) ? $RequestBody['group'] : '';
    $ChequeDiscountLimit = isset($RequestBody['cheque_discount_limit']) && $RequestBody['cheque_discount_limit'] != '' ? $RequestBody['cheque_discount_limit'] : 0;
    $RateOfInterest      = isset($RequestBody['rate_of_interest']) && $RequestBody['rate_of_interest'] != '' ? $RequestBody['rate_of_interest'] : 0;
    $PdcDiscountedAC     = isset($RequestBody['pdc_discounted_ac']) ? $RequestBody['pdc_discounted_ac'] : '';
    $BankAC              = isset($RequestBody['bank_ac']) ? $RequestBody['bank_ac'] : '';
    $ItemDesc            = isset($RequestBody['item_desc']) ? $RequestBody['item_desc'] : '';
    $BankBranch          = isset($RequestBody['bank_branch']) ? $RequestBody['bank_branch'] : '';
    $CashFlowClassification = isset($RequestBody['cash_flow_classification']) ? $RequestBody['cash_flow_classification'] : '';
    $BankAccountName     = isset($RequestBody['bank_account_name']) ? $RequestBody['bank_account_name'] : '';
    $BankAccountNumber   = isset($RequestBody['bank_account_number']) ? $RequestBody['bank_account_number'] : '';
    $IFSCCode            = isset($RequestBody['ifsc_code']) ? $RequestBody['ifsc_code'] : '';
    $PaymentType         = isset($RequestBody['payment_type']) ? $RequestBody['payment_type'] : '';
    $FinanceEmail        = isset($RequestBody['finance_email']) ? $RequestBody['finance_email'] : '';
    $Consignee           = isset($RequestBody['consignee']) ? $RequestBody['consignee'] : '';
    $SendEmailCustomer   = isset($RequestBody['send_email_customer']) ? 1 : 0;
    $AllowPortal         = isset($RequestBody['allow_portal']) ? 1 : 0;
    $Qty                 = isset($RequestBody['qty']) ? $RequestBody['qty'] : '';

    $DebitCreditProposal        = isset($RequestBody['debit_credit_proposal']) ? $RequestBody['debit_credit_proposal'] : '';
    $DebitCreditRequired        = isset($RequestBody['debit_credit_required']) ? $RequestBody['debit_credit_required'] : '';
    $ExchangeAdjustmentGainAC   = isset($RequestBody['exchange_adjustment_gain_ac']) ? $RequestBody['exchange_adjustment_gain_ac'] : '';
    $ExchangeAdjustmentLossAC   = isset($RequestBody['exchange_adjustment_loss_ac']) ? $RequestBody['exchange_adjustment_loss_ac'] : '';

    $PrimaryAccount             = isset($RequestBody['primary_account']) ? $RequestBody['primary_account'] : '';
    $DefaultCurrency            = isset($RequestBody['default_currency']) ? $RequestBody['default_currency'] : '';
    $ConsolidationMethod        = isset($RequestBody['consolidation_method']) ? $RequestBody['consolidation_method'] : '';
    $PaymentTerms               = isset($RequestBody['payment_terms']) ? $RequestBody['payment_terms'] : '';
    $ReminderTerms              = isset($RequestBody['reminder_terms']) ? $RequestBody['reminder_terms'] : '';
    $FinanceChargeTerms         = isset($RequestBody['finance_charge_terms']) ? $RequestBody['finance_charge_terms'] : '';

    $CurrentDateTime = getCurrentDateTime()['TimeStamp'];
    $existingVendor = null;
    if ($VendorID != '') {
        $existingVendor = $this->auditLogger->fetchRowById('Vendors', 'VendorID', $VendorID);
    }

    // ================= INSERT =================
    if ($VendorID == '') {
        $sql = "INSERT INTO Vendors (
            VendorName, VendorCode, Email, `Address`, TelNo, FaxNo, DeliveryAddress, Pin, GSTIN, IGSTNumber, IGSTPercentage, IGSTApplicableFrom, 
            `State`, StateName,   AccountType, CreditLimit, CreditDays, `Group`, ChequeDiscountLimit, RateOfInterest, 
            PdcDiscountedAC, BankAC, ItemDesc, BankBranch, CashFlowClassification, BankAccountName, 
            BankAccountNumber, IFSCCode, PaymentType, FinanceEmail, Consignee, SendEmailCustomer, AllowPortal, Qty, 
            DebitCreditProposal, DebitCreditRequired, ExchangeAdjustmentGainAC, ExchangeAdjustmentLossAC, 
            PrimaryAccount, DefaultCurrency, ConsolidationMethod, PaymentTerms, ReminderTerms, FinanceChargeTerms, 
            CreatedDate
        ) VALUES (
            '$VendorName', '$VendorCode', '$Email', '$Address', '$TelNo', '$FaxNo', '$DeliveryAddress', '$Pin', '$GSTIN', '$IGSTNumber', '$IGSTPercentage', '$IGSTApplicableFrom',
            '$State', '$StateName', '$AccountType', $CreditLimit, $CreditDays, '$Group', $ChequeDiscountLimit, $RateOfInterest,
            '$PdcDiscountedAC', '$BankAC', '$ItemDesc', '$BankBranch', '$CashFlowClassification', '$BankAccountName',
            '$BankAccountNumber', '$IFSCCode', '$PaymentType', '$FinanceEmail', '$Consignee', '$SendEmailCustomer', '$AllowPortal', '$Qty',
            '$DebitCreditProposal', '$DebitCreditRequired', '$ExchangeAdjustmentGainAC', '$ExchangeAdjustmentLossAC',
            '$PrimaryAccount', '$DefaultCurrency', '$ConsolidationMethod', '$PaymentTerms', '$ReminderTerms', '$FinanceChargeTerms',
            '$CurrentDateTime'
        )";
    }
    // ================= UPDATE =================
    else {
        $sql = "UPDATE Vendors SET
            VendorName = '$VendorName',
            VendorCode = '$VendorCode',
            Email = '$Email',
            `Address` = '$Address',
            TelNo = '$TelNo',
            FaxNo = '$FaxNo',
            DeliveryAddress = '$DeliveryAddress',
            Pin = '$Pin',
            GSTIN = '$GSTIN',
            IGSTNumber = '$IGSTNumber',
            IGSTPercentage = '$IGSTPercentage',
            IGSTApplicableFrom = '$IGSTApplicableFrom',
            `State` = '$State',
            StateName = '$StateName',
            AccountType = '$AccountType',
            CreditLimit = $CreditLimit,
            CreditDays = $CreditDays,
            `Group` = '$Group',
            ChequeDiscountLimit = $ChequeDiscountLimit,
            RateOfInterest = $RateOfInterest,
            PdcDiscountedAC = '$PdcDiscountedAC',
            BankAC = '$BankAC',
            ItemDesc = '$ItemDesc',
            BankBranch = '$BankBranch',
            CashFlowClassification = '$CashFlowClassification',
            BankAccountName = '$BankAccountName',
            BankAccountNumber = '$BankAccountNumber',
            IFSCCode = '$IFSCCode',
            PaymentType = '$PaymentType',
            FinanceEmail = '$FinanceEmail',
            Consignee = '$Consignee',
            SendEmailCustomer = '$SendEmailCustomer',
            AllowPortal = '$AllowPortal',
            Qty = '$Qty',
            DebitCreditProposal = '$DebitCreditProposal',
            DebitCreditRequired = '$DebitCreditRequired',
            ExchangeAdjustmentGainAC = '$ExchangeAdjustmentGainAC',
            ExchangeAdjustmentLossAC = '$ExchangeAdjustmentLossAC',
            PrimaryAccount = '$PrimaryAccount',
            DefaultCurrency = '$DefaultCurrency',
            ConsolidationMethod = '$ConsolidationMethod',
            PaymentTerms = '$PaymentTerms',
            ReminderTerms = '$ReminderTerms',
            FinanceChargeTerms = '$FinanceChargeTerms',
            UpdatedDate = '$CurrentDateTime'
            WHERE VendorID = $VendorID";
    }
    $stmt = $this->conn->prepare($sql);

    // Execute
    if ($stmt->execute()) {
        $LastID = $VendorID ?: $this->conn->lastInsertId();
        $savedVendor = $this->auditLogger->fetchRowById('Vendors', 'VendorID', $LastID);
        if ($VendorID == '') {
            $this->auditLogger->logInsert('Vendors', $LastID, $savedVendor, $RequestBody, [
                'ReferenceNo' => $VendorCode,
                'Remarks' => 'Inventory vendor created',
            ]);
        } else {
            $this->auditLogger->logUpdate('Vendors', $LastID, $existingVendor, $savedVendor, $RequestBody, [
                'ReferenceNo' => $VendorCode,
                'Remarks' => 'Inventory vendor updated',
            ]);
        }
        $Data['Error'] = null;
        $Data['Msg']   = "Vendor saved successfully";
        $Data['Data']  = ["VendorID" => $LastID];
        $Data['sql']   = $sql; // optional for debug
    } else {
        $error = $stmt->errorInfo();
        $Data['Error'] = $error[2];
        $Data['Msg']   = "Error while saving vendor";
        $Data['sql']   = $sql;
        $Data['Data']  = [];
    }

    return $Data;
}




    // ===================== GET SINGLE VENDOR =====================
    public function getVendor($VendorID = null)
    {
        try {
            if (!empty($VendorID)) {
                // Fetch a single vendor
                $stmt = $this->conn->prepare("SELECT * FROM Vendors WHERE VendorID = :VendorID");
                $stmt->bindParam(':VendorID', $VendorID);
                $stmt->execute();
                $vendor = $stmt->fetch(PDO::FETCH_ASSOC);

                return [
                    'Error' => null,
                    'Msg' => $vendor ? "Success" : "No vendor found",
                    'Data' => $vendor ? [$vendor] : [] // wrap single vendor in array for consistency
                ];
            } else {
                // Fetch all vendors
                $stmt = $this->conn->prepare("SELECT * FROM Vendors ORDER BY VendorID DESC");
                $stmt->execute();
                $vendors = $stmt->fetchAll(PDO::FETCH_ASSOC);

                return [
                    'Error' => null,
                    'Msg' => count($vendors) ? "Vendors retrieved successfully" : "No vendors found",
                    'Data' => $vendors
                ];
            }
        } catch (Exception $e) {
            return [
                'Error' => $e->getMessage(),
                'Msg' => 'Database error while fetching vendors',
                'Data' => []
            ];
        }
    }

    // ===================== DELETE VENDOR =====================
    public function deleteVendor($VendorID,$RequestBody)
    {
        $existingVendor = $this->auditLogger->fetchRowById('Vendors', 'VendorID', $VendorID);
        $stmt = $this->conn->prepare("DELETE FROM Vendors WHERE VendorID=:VendorID");
        $stmt->bindParam(':VendorID', $VendorID);

        if ($stmt->execute()) {
            $this->auditLogger->logDelete('Vendors', $VendorID, $existingVendor, $RequestBody, [
                'ReferenceNo' => isset($existingVendor['VendorCode']) ? $existingVendor['VendorCode'] : $VendorID,
                'Remarks' => 'Inventory vendor deleted',
            ]);
            return [
                'Error' => null,
                'Msg' => "Vendor deleted successfully",
                'Data' => ["VendorID" => $VendorID]
            ];
        } else {
            $error = $stmt->errorInfo();
            return [
                'Error' => $error[2],
                'Msg' => "Error deleting vendor",
                'Data' => []
            ];
        }
    }

    // ===================== LOAD ALL VENDORS =====================
    public function loadVendors()
    {
        $Data = array();
        try {
            $sql = "SELECT VendorID, VendorName, VendorCode, AccountType, Email, TelNo 
                FROM Vendors 
                ORDER BY VendorID DESC";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $Data['Data'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($stmt->rowCount() > 0) {
                $Data['status'] = "success";
                $Data['msg'] = "Vendors Found";
            } else {
                $Data['status'] = "success";
                $Data['msg'] = "No Vendors Found";
                $Data['Data'] = [];
            }
        } catch (Exception $e) {
            $Data['status'] = "error";
            $Data['msg'] = "Exception: " . $e->getMessage();
        }

        return $Data;
    }
}
