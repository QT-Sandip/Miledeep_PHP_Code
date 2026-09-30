<?php
$host = "https://$_SERVER[HTTP_HOST]" . '/';

if ($host == 'https://camaroo.in/') {
    define("BASEURL", "https://camaroo.in/ApiV2/");
} else if ($host == 'https://staging-server.miledeepworks.in/') {
    define("BASEURL", "https://staging-server.miledeepworks.in/ApiV2/");
} else {
    include_once __DIR__ . '/../config/BaseUrl.php';
    $BaseUrlConfig = new BaseUrl;
    $BaseUrlConfig = $BaseUrlConfig->getBaseUrl(true);
    $host = $BaseUrlConfig->protocol . "://$BaseUrlConfig->host" . '/';

    define("BASEURL", $host . "ApiV2/");
}

define("API_LOGIN", "Auth/AuthLogin.php"); // Login.php

define("API_MENU", "App/Menu.php"); // Menu.php
define("API_MENU_V4", "App/MenuV4"); // Menu.php

/* Exporter - Module */
define("API_EXPORTER_DETAILS", "Exports/ExporterDetails"); // exporter-details.php
define("API_PRODUCT_LIST_DETAILS", "Exports/ProductListDetails"); // ProductList-details.php
define("API_PROCESSORS_DETAILS", "Exports/ProcessorsDetails"); // Processors-details.php
define("API_PAYMENT_TERMS_DETAILS", "Exports/PaymentTermsDetails"); // paymentTerms-details.php
define("API_LINERS_LIST_DETAILS", "Exports/LinersListDetails"); // LinersList-details.php
define("API_PREPARATION_LIST_DETAILS", "Exports/PreparationListDetails"); // preparationList-details.php
define("API_GRADES_LIST_DETAILS", "Exports/GradesListDetails"); // GradesList-details.php
define("API_PACKING_LIST_DETAILS", "Exports/PackingListDetails"); // PackingList-details.php
define("API_VARIETY_LIST_DETAILS", "Exports/VarietyListDetails"); // VarietyList-details.php
define("API_TAX_DETAILS", "Exports/TaxDetails"); // tax-details.php
define("API_CHA_LIST_DETAILS", "Exports/CHAListDetails"); // CHAList-details.php
define("API_DESCRIPTION_LIST_DETAILS", "Exports/DescriptionListDetails"); // DescriptionList-details.php
define("API_PORTS_LIST_DETAILS", "Exports/PortsListDetails"); // PortsList-details.php
define("API_EXCHANGE_RATES_DETAILS", "Exports/ExchangeRatesDetails"); // ExchangeRates-details.php
define("API_BANKS_LIST_DETAILS", "Exports/BanksListDetails"); // BanksList-details.php
define("API_STORE_DELIVERY_DETAILS_LIST", "Exports/StoreDeliveryDetailsList"); // StoreDeliveryList-details.php
define("API_BUYER_LIST_DETAILS", "Exports/BuyerListDetails"); // buyerList-details.php
define("API_CONSIGNEE_LIST_DETAILS", "Exports/ConsigneeListDetails"); // consigneeList-details.php
define("API_NOTIFY_PARTY_LIST", "Exports/NotifyPartyList"); // notifyPartyList.php
define("API_APPLICANT_LIST_DETAILS", "Exports/ApplicantListDetails"); // applicantList-details.php
define("API_BRANDS_LIST_DETAILS", "Exports/BrandsListDetails"); // brandsList-details.php
define("API_PROFORMA_LIST", "Exports/ProformaList"); // ProformaList.php
define("API_SHIPPING_BILL", "Exports/ShippingBill"); // ShippingBill.php
define("API_CLEARING_AGENT", "Exports/ClearingAgent"); // Clearingagent.php
define("API_COMMERCIAL_INVOICE", "Exports/CommercialInvoice"); // CommercialInvoice.php
define("API_CUSTOM_INVOICES", "Exports/CustomInvoices"); // CustomInvoices.php
define("API_ETA_STATUS", "Exports/ETAStatus"); // eta-status.php
define("API_ISF_DETAILS", "Exports/ISFDetails"); // ISFDetails.php
define("API_FDA_STATUS", "Exports/FDAStatus"); // FDAStatus.php
define("API_GST_SALES_REPORT", "Exports/GSTSalesReport"); // AjaxGSTSalesReport.php
define("API_ORDER_STATUS", "Exports/OrderStatus"); // AjaxProformaListActions.php
define("API_PO_STATUS_DETAILS", "Exports/PoStatusDetails"); // AjaxProformaListActions.php
define("API_INVOICES_PRINT_PAGES_LIST_POPUP", "Exports/InvoicesPrintPagesListPopup"); // custom-invoices.php
define("API_QCERTIFICATE_REPORT", "Exports/QCertificateReport"); // AjaxQCertificateReport.php
define("API_M_TRANSIT_INS_AND_PAYMENTS", "Exports/InsuranceReport"); // AjaxMTransitInsAndPayments.php
define("API_REALIZATION_DONE", "Exports/RealizationDone"); // AjaxPayReceived.php
define("API_CLEARING_AGENT_ACTIONS", "Exports/ClearingAgentActions"); // clearing-agent.php
define("API_COASTING_RATES", "Exports/CoastingRatesHistory"); // Durga
define("API_COASTING_RATES_PRINT", "Exports/CoastingRatesPrint"); // Durga
define("API_CURRENCY_MASTERS", "Exports/CurrencyMasters"); // CurrencyMasters.php durgaPrasad
define("API_ORDERS_BUYERS_DETAILS", "Exports/OrderBuyersDetails"); // OrderBuyersDetails.php durgaPrasad
define("API_PREPARATION_MASTARS", "Exports/PreparationList"); // PreparationList.php durgaPrasad
define("API_TAX_CHARGES", "Exports/TaxCharges"); // TaxCharges.php durgaPrasad
define("API_PO_TAX_CHARGES", "Exports/PoTaxCharges"); // PoTaxCharges.php Nithin
define("API_PRODUCT_MASTERS", "Exports/ProductMasters"); // ProductMasters.php durgaPrasad
define("API_GRADE_MASTERS", "Exports/GradesMasters"); // GradesMasters.php durgaPrasad
define("API_PACKING_STYLE", "Exports/PackingStyleMasters"); // PackingStyleMasters.php durgaPrasad
define("API_CONTAINER_RETURN_DETAILS", "Exports/ContainerReturnDetails"); // ContainerReturnDetails.php durgaPrasad
define("API_CONTAINER_RETURN_REPORT", "Exports/ContainerReturnReport"); // ContainerReturnReport.php durgaPrasad
define("API_SPECIES_MASTARS", "Exports/SpeciesMasters"); // species-master-list,create.php komali
define("API_EXPORT_INFORMATION_REPORT", "Exports/ExportInformationReport"); // ExcelDocExportInformationReport.php komali
define("API_SEA_CONTAINER_QUARANTINE", "Exports/SeaContainerQuarantine"); // quarantine-declaration-print.php durgaPrasad
define("API_SHIPMENT_ADVICE", "Exports/ShipmentAdvice"); // shipment-advice.php durgaPrasad
define("API_SHIPPING_LINES_CHARGES", "Exports/ShippingLinesCharges");
define("API_SHIPPING_LINES_CHARGES_REPORT", "Exports/ShippingLinesChargesReport");
define("API_LETTER_OF_GUATANTEE", "Exports/LetterOfGuarantee"); // letter-of-guarantee.php komali
define("API_BRANDS_MASTERS", "Exports/BrandsMasters"); // ajax-brands-list.php komali
define("API_BANKS_MASTER", "Exports/BanksMaster"); // ajax-banks-list-masters.php @mahesh
define("API_LINER_MASTER_DETALS", "Exports/LineMasterDetails"); // ajax-liner-list-master.php @mahesh
define("API_DELAYED_SHIPMENTS", "Exports/DelayedShipments"); // ajax-delayed-shipments.php @Komali
define("API_LINER_CONTRACT", "Exports/LinerContract"); // AjaxLinerContract.php @Maheswari 29-04-2025
define("API_RECIPROCAL_TARIFF_DETAILS", "Exports/ReciprocalTariffDetails"); // ReciprocalTariffDetails.php @Ramesh 27-05-2025


/***InvoiceReports* */
define("API_PDF_PROFORMA_PRINT", "Exports/ProformaPrint");
define("API_PDF_CUSTOM_PRINT", "Exports/CustomPrint"); // custome-invoice.php
define("API_PDF_COMMERCIAL_PRINT", "Exports/CommercialPrint"); // commercial-invoice.php
define("API_SHIPMENT_SALES_LINEWISE_REPORT", "Exports/ShipmentLineItemwiseReport"); // AjaxShipmentSalesLineItemwiseReport.php
define("API_SHIPMENT_SALES_LINE_PRICE_BOOK_REPORT", "Exports/ShipmentLineItemPriceBookReport");
define("API_DELETE_CLEARING_AGENT_BREACKUP", "Exports/DeleteClearingAgentBreakUp"); // AjaxShipmentSalesLineItemwiseReport.php
define("API_DELETE_COMMERCIAL_DATA", "Exports/DeleteCommercialData"); // AjaxShipmentSalesLineItemwiseReport.php
define("API_PACKING_REPORT_DATA", "Exports/PackingReportData"); // AjaxPackingData.php
define("API_ADD_PACKING_DATA", "Exports/AddPackingData"); // AjaxPackingData.php
define("API_DIGITAL_SIGNATURE", "Exports/DigitalSignature"); // S1/DocProformaInvoicePrint.php
define("API_VARIETY_LIST", "Exports/VarietyList"); // Durga ajax-variety-list.php
define("API_NOTIFICATIONS", "Exports/Notifications");
define("API_COUNTRY_ADDRESSES", "Exports/CountrywiseAddress"); //CoutrywiseAddress @durgaPrasad


// define("GetClearingAgentFormDataAPI", "Api?f=getClearingAgentFormData"); // ClearingAgentForm.php 
// define("PostClearingAgentDetailsAPI", "Api?f=PostClearingAgentDetails"); // ClearingAgentForm.php 
// define("GetStoreDeliveryDetailsAPI", "Exports/StoreDeliveryDetailsList"); // StoreDeliveryDetailsList.php 
// define("GetInsuranceTemplatesList_API", "Exports/InsuranceReport"); // clearing-agent.php
// define("CreatePaymentsTemplate_API", "Exports/InsuranceReport"); // InsurancePayments.php 


/***InvoiceReports* */

/* Purchase - Module */
define("API_DROPDOWNS", "App/Dropdowns"); // For App Level
define("API_EXPORTS_DROPDOWNS", "Exports/Dropdowns"); // For Sales

//define("API_RM_ARRIVAL_LIST", "Purchase/RmArrivalListDetails"); // RmArrivalList.php

define("API_RM_DROPDOWNS", "RmPurchase/Dropdowns"); //For Purchase


define("API_RM_BOOKING_LIST", "RmPurchase/RmBookingList"); //rm-booking-list.php
define("API_RM_BOOKING", "RmPurchase/RmBooking"); // create-booking.php

define("API_RM_ARRIVAL_LIST", "RmPurchase/RmArrivalList"); //rm-arrival-list.php
define("API_RM_ARRIVAL", "RmPurchase/RmArrival"); //create-arrival.php

define("API_RM_BILLS_LIST", "RmPurchase/RmBillList"); //rm-bills-list.php
define("API_RM_BILLS", "RmPurchase/RmBill"); //create-bills.php
define("API_SUPPLIER_LIST", "RmPurchase/SupplierList"); //supplier-list.php
define("API_PAYMENT_LIST", "RmPurchase/RmPaymentList"); //supplier-list.php
define("API_RM_PAYMENT", "RmPurchase/RmPayment"); //supplier-list.php
define("API_RM_SUPPLIER", "RmPurchase/RmSupplier"); // AjaxSupplier.php
define("API_RM_OVER_ALL_ARRIVAL", "RmPurchase/RmOverAllArrival"); // AjaxRmOverAllArrival.php
define("API_PURCHASE_REPORT", "RmPurchase/RmPurchaseReport"); // AjaxPurchaseReport.php
define("API_PURCHASE_REPORT_ITEM_WISE", "RmPurchase/RmPurchaseItemWiseReport"); // AjaxPurchaseItemWiseReport.php
define("API_RM_ARRIVAL_REPORT", "RmPurchase/RmArrivalReport"); // DocExlRmOverAllArrivalReport.php
define("API_RM_COUNT_WISE_PURCHASE_REPORT", "RmPurchase/RmCountWisePurchaseReport"); // CustomInvoice.php
define("API_RM_ARRIVAL_BALANCE_WEIGHT", "RmPurchase/RmArrivalBalanceWeight"); // CustomInvoice.php
define("API_RM_PURCHASE_DATE_WISE_REPORT", "RmPurchase/RmPurchaseDateWiseReport"); // purchase-date-wise-report.php
define("API_RM_PURCHASE_SUPPLIER_WISE_REPORT", "RmPurchase/RmPurchaseSupplierwiseReport"); // purchase-supplier-wise-report
define("API_RM_PURCHASE_OBSTRACT_REPORT", "RmPurchase/RmPurchaseObstractReport"); // purchase-obstract-report.php
define("API_RM_PAYMENT_DATE_WISE_REPORT", "RmPurchase/RmPaymentDateWiseReport"); // payment-date-wise-report.php
define("API_RM_PAYMENT_CENTER_WISE_REPORT", "RmPurchase/RmPaymentCenterwiseWiseReport"); // supplier-center-bill-wise-report.php
define("API_RM_PAYMENT_SUPPLIER_WISE_REPORT", "RmPurchase/RmPaymentSupplierWiseReport"); // supplier-bill-wise-report.php
define("API_SUPPLIER_WISE_LEDGER_REPORT", "RmPurchase/RmSupplierwiseLedgerReport"); //supplier-wise-ledger-report.php
define("API_RM_PAYMENT_AGENT_COMMISION_REPORT", "RmPurchase/RmPaymentAgentCommisionReport"); // agent-commision-bill-wise-report.php
define("API_RM_PURCHASE_MONTH_WISE_REPORT", "RmPurchase/RmPurchaseMonthWiseReport"); //supplier-wise-ledger-report.php
define("API_PAYMENT_VOUCHER", "RmPurchase/RmPaymentVoucherReport"); // @DurgaPrasad
define("API_RM_PAYMENT_SUMMARY_REPORT", "RmPurchase/RmPaymentSummaryReport"); // @uma //rm-payment-summary.PHP

define("API_RM_PRODUCTION_TRACK_LIST", "RmPurchase/RmProductionTrackIDList"); // Only for Dropdowns for now Preprocessing
define("API_RM_PRODUCTION_LIST", "RmPurchase/RmProductionList"); // Only for Dropdowns for now Preprocessing
define("API_AGENT_BILL_REPORT", "RmPurchase/RmAgentBillWiseOutStandingReport"); // @DurgaPrasad
define("API_RM_AGENT_LIST", "RmPurchase/RmAgentList"); // @DurgaPrasad 
define("API_RM_AGENT_CREATE", "RmPurchase/RmAgentCreate"); // @DurgaPrasad 
define("API_RM_FINANCIER_LIST", "RmPurchase/RmFinanciersList"); // @DurgaPrasad 
define("API_RM_FINANCIER_CREATE", "RmPurchase/RmFinancierCreate"); // @DurgaPrasad 
define("API_RM_TDS_REPORT", "RmPurchase/TdsReport"); // @DurgaPrasad 
define("API_RM_COASTING_REPORT", "RmPurchase/RmCoastingReport"); // RmCoastingReport.php @DurgaPrasad 
define("API_RM_RATES", "RmPurchase/RmRates"); // RmRates.php @DurgaPrasad 
define("API_MASTERS_COUNTS", "RmPurchase/MastersCounts"); // MastersCounts.php @komali
define("API_PURCHASE_RATES", "RmPurchase/AddPurchaseRatesM1"); // AddPurchaseRatesM1.php @DurgaPrasad
define("API_GET_RM_RATE", "RmPurchase/RmCountRateGet"); // RmCountRateGet.php @DurgaPrasad
define("API_HIGHEST_LOWEST_BILLS", "RmPurchase/HighestLowestPurchase"); // HighestLowestPurchase.php @DurgaPrasad
define("API_RM_PRICES", "RmPurchase/RmDeductionsCreate"); // RmDeductions.php @DurgaPrasad
define("API_RM_PRICES_LIST", "RmPurchase/RmDeductionsList"); // RmDeductionsList.php @DurgaPrasad
define("API_HARVESTING_DETAILS", "RmPurchase/HarvestingDetails"); // HarvestingDetails.php @DurgaPrasad
define("API_BOYS_GROUP_MASTERS", "RmPurchase/RmBoysGroupMasters"); // RmBoysGroupMasters.php @DurgaPrasad
define("API_HARVESTING_BOYS_REPORT", "RmPurchase/RmHarvestingWorkingBoysReport"); // RmHarvestingWorkingBoysReport.php @Mahesh
define("API_RM_BOOKING_REPORT", "RmPurchase/RmBookingReport"); // RmBookingReport.php @Mahesh
define("API_TDS_CALC_BG_PROCESS", "RmPurchase/RmTdsBillCalcBgProcess");
define("API_PURCHASE_AUDIT_HISTORY", "RmPurchase/AuditHistory"); // audit-history.php


//Quality Control
define("API_QC_ORDERS_LIST", "QualityControl/OrdersList"); // CustomInvoice.php
define("API_QC_SIMPV1", "QualityControl/SimpV1"); // 
define("API_QC_SIMP", "QualityControl/Simp"); // 
define("API_PPC_GRADING", "Api?f=postHlGradingReport"); // CustomInvoice.php
define("API_PPC_GRADING_BREAK_UP", "Api?f=postHlGradingBreakUp"); // CustomInvoice.php
define("API_QC_DROPDOWNS", "QualityControl/Dropdowns"); // Dropdowns.php durgaPrasad
define("API_LAB_MASTERS", "QualityControl/LabNamesMasters"); // LabNamesMasters.php durgaPrasad
define("API_MEDIA_MASTERS", "QualityControl/MediaNamesMasters");
define("API_INSTRUMENT_MASTERS", "QualityControl/InstrumentNamesMasters");
define("API_REQUIRED_ANALYSIS", "QualityControl/AnalysisRequiredMasters"); // AnalysisRequiredMasters.php durgaPrasad
define("API_ANTIBIOTIC_ONE", "QualityControl/AntibioticEntryForm1"); // AntibioticEntryForm1.php durgaPrasad
define("API_ANTIBIOTIC_BREAKUP", "QualityControl/AntibioticEntryForm2"); // AntibioticEntryForm2.php durgaPrasad
define("API_AUDIT_CATEGORIES", "QualityControl/AuditCategoriesMasters"); // AuditCategoriesMastesr.php durgaPrasad
// define("API_AUDIT_CATEGORIES_OPTIONS", "QualityControl/AuditCategoriesOptionsMastesr"); // AuditCategoriesOptionsMastesr.php durgaPrasad
define("API_AUDIT_CATEGORIES_OPTIONS", "QualityControl/AuditCategoriesOptionsMasters"); // AuditCategoriesOptionsMastesr.php durgaPrasad
define("API_FOOD_AUDIT", "QualityControl/FoodAudit"); // ajax-food-audit-create.php,ajax-food-audit-list.php durgaPrasad
define("API_FOOD_AUDIT_PAYMENT", "QualityControl/FoodAuditPayment"); // ajax-food-audit-create.php durgaPrasad
define("API_FOOD_AUDIT_REPORT", "QualityControl/FoodAuditReport"); // ajax-food-audit-report.php  durgaPrasad
define("API_SOCIAL_AUDIT", "QualityControl/SocialAudit"); // ajax-social-audit.php  durgaPrasad
define("API_SOCIAL_AUDIT_PAYMENT", "QualityControl/SocialAuditPayment"); // ajax-social-audit-payment.php  durgaPrasad
define("API_SOCIAL_AUDIT_REPORT", "QualityControl/SocialAuditReport"); // ajax-social-audit-report.php  durgaPrasad
define("API_FARM_AUDIT", "QualityControl/FarmAudit"); // ajax-audit-farm-create.php,ajax-audit-farm-list.php  durgaPrasad
define("API_FARM_AUDIT_PAYMENT", "QualityControl/FarmAuditPayment"); // ajax-audit-farm-create.php  durgaPrasad
define("API_FARM_AUDIT_REPORT", "QualityControl/FarmAuditReport"); // ajax-audit-farm-create.php  durgaPrasad
define("API_CERTIFICATION_BODY_MASTER", "QualityControl/CertificationBodyMaster"); // ajax-certification-body-master-create.php, ajax-certification-body-master-list.php  durgaPrasad
define("API_FARM_MASTER", "QualityControl/FarmMaster"); // ajax-certification-body-master-create.php, ajax-certification-body-master-list.php  durgaPrasad
define("API_HATCHERY_AUDIT", "QualityControl/HatcheryAudit"); // ajax-certification-body-master-create.php, ajax-certification-body-master-list.php  durgaPrasad
define("API_HATCHERY_AUDIT_PAYMENT", "QualityControl/HatcheryAuditPayment"); // ajax-certification-body-master-create.php, ajax-certification-body-master-list.php  durgaPrasad
define("API_HATCHERY_AUDIT_REPORT", "QualityControl/HatcheryAuditReport"); // ajax-hatchery-audit-report.php  durgaPrasad
define("API_FEED_MILL_AUDIT", "QualityControl/FeedMillAudit"); // ajax-feed-mill-audit-create.php  durgaPrasad
define("API_FEED_MILL_AUDIT_PAYMENT", "QualityControl/FeedMillAuditPayment"); // ajax-feed-mill-audit-payment.php  durgaPrasad
define("API_FEED_MILL_AUDIT_REPORT", "QualityControl/FeedMillAuditReport"); // ajax-feed-mill-report.php  durgaPrasad
define("API_CATEGORY_OPTIONS_MASTERS", "QualityControl/CategoryOptionsMaster"); // ajax-feed-mill-report.php  durgaPrasad
define("API_HATCHERY_MASTER", "QualityControl/HatcheryMaster"); // ajax-hatchery-master-list.php  durgaPrasad
define("API_BAAP_SHEET_REPORT", "QualityControl/BapSheetReport"); // ajax-baap-report.php  Mahesh
define("API_TRACEABILITY_UTILIZED_REPORT", "QualityControl/TraceabilityUtilizedReport"); // ajax-traceability-utilized-report.php  Prasad
define("API_NEW_ADDON_FIELDS_FOR_SYSCO", "QualityControl/QcNewAddonFieldsForSysco"); // ajax-qc-new-addon-fields-for-sysco.php  Prasad
define("API_BUYER_REPORT", "QualityControl/BuyerReport"); // ajax-qc-buyer-request-list.php  Prasad
define("API_SYSCO_PSIP_STATUS_REPORT", "QualityControl/SiscoPsipStatusReport"); // ajax-qc-buyer-request-list.php  Prasad
define("API_SYSCO_PSIP_REPORT_PDF", "QualityControl/SiscoPsipReportPdf"); // ajax-qc-buyer-request-list.php  Prasad
define("API_QC_KITS", "QualityControl/QcKits"); // ajax-qc-kits-list.php  Mahesh
define("API_KITS_SUMMARY_REPORT", "QualityControl/KitsSummaryReport"); // ajax-kits-summary-report.php  Mahesh
define("API_ANTIBIOTIC_REPORT", "QualityControl/AntibioticReport"); // ajax-antibiotic-report.php  Uma
define("API_TRACEABILITY_DROP_DOWN_LOAD_MORE", "QualityControl/TracebilityDropDownLoadMore");
define("API_CONTRACEABILITY_REPORT", "QualityControl/ConTraceabilityReport");
define("API_MICRO_BIOLOGY_REPORT", "QualityControl/MicroBiologyReport");
define("API_MEDIA_KITS", "QualityControl/MediaKits");
define("API_MEDIA_KITS_SUMMARY_REPORT", "QualityControl/MediaKitsSummaryReport");
define("API_MICRO_BIOLOGY_REPORT_WATER_ICE", "QualityControl/IhmbrfwiReport");
define("API_DEHEADING_CHECK_LIST", "QualityControl/DeheadingList");
define("API_GRADING_INSPECTION_DETAILS", "QualityControl/GradingInspectionDetails"); // GradingInspectionDetails.php @Ramesh
define("API_TRACEABILITY_UTILIZED_REPORT1", "QualityControl/TraceabilityUtilizedReport1");
define("API_QUANTITY_CHECK_VALUE", "QualityControl/QualityCheckDetails");
define("API_QC_TREATMENT_REGISTER_VERAITY_LIST", "QualityControl/QCTreatmentRegisterReport?f=getVeraityByLotId");

/*QC treatment start anil*/
define("API_QC_GET_UNIQUE_BATCH_IDS", "QualityControl/QCTreatmentRegisterReport?f=GetUniqueBatchIdsByArrivalDate");
define("API_QC_TREATMENT_REGISTER_DROPDOWN", "QualityControl/QCTreatmentRegisterReport?f=getDropdowns");
define("API_QC_TREATMENT_REGISTER_POST", "QualityControl/QCTreatmentRegisterReport?f=PostData");
define("API_QC_TREATMENT_REGISTER_LIST", "QualityControl/QCTreatmentRegisterReport?f=GetListByPagin");
define("API_DELETE_TREATMENT_REG_BY_ID", "QualityControl/QCTreatmentRegisterReport?f=DeleteTreatmentById");
define("API_QC_TREATMENT_REGISTER_QC", "QualityControl/QCTreatmentRegisterReport?f=DownloadReport");
define("API_QC_TREATMENT_REGISTER_GRADE_LIST", "QualityControl/QCTreatmentRegisterReport?f=getGradesByLotId");

define("API_DASHBOARD_GRAPHS", "QualityControl/DashboardGraphs");

// define("API_QC_QUALITY_ASSURANCE_CHECK_LIST_GET_BATCH_DATA", "QualityControl/QCQualityAssuranceChekList?f=getBatchData"); 
// define("API_QC_QUALITY_ASSURANCE_CHECK_LIST_DROPDOWNS", "QualityControl/QCQualityAssuranceChekList?f=getDropdowns"); //
// define('API_QC_QUALITY_ASSURANCE_CHECK_LIST_POST', "QualityControl/QCQualityAssuranceChekList?f=PostData"); //
// define('API_QC_QUALITY_ASSURANCE_CHECK_LIST_DELETE', "QualityControl/QCQualityAssuranceChekList?f=DeleteQualityCheckListById"); //
// define('API_QC_QUALITY_ASSURANCE_CHECK_LIST_GET', "QualityControl/QCQualityAssuranceChekList?f=GetQualityCheckListById"); //
// define('API_QC_QUALITY_ASSURANCE_CHECK_LIST_GET_FOR_LISTING', "QualityControl/QCQualityAssuranceChekList?f=GetQualityCheckList"); //

/*QC treatment end anil*/

//HRM
define("API_EMPLOYEE_DETAILS", "HumanResource/EmployeeList"); //EmployeeList.php 
define("API_EMPLOYEE_BANK_DETAILS_CREATE", "HumanResource/EmployeeBankDetailsCreate"); // Durga
define("API_SALARY_STRUCTURE_MASTER", "HumanResource/SalaryStructureMaster"); // 
define("API_BIO_METRIC_DATA", "HumanResource/BioMetricData"); // 



//PREPROCESING//

define("API_PREPROCESSING_DROPDOWNS", "PreProcessing/Dropdowns"); //For Preprocessing
define("API_DE_HEADING_DETAILS", "PreProcessing/DeheadingDetails"); //AjaxDeHeading.php
define("API_VALUE_ADDITION_DETAILS", "PreProcessing/ValueAdditionDetails"); //AjaxValueAddtion.php @DurgaPrasad
define("API_REGLAZING_DETAILS", "PreProcessing/ReglazingDetails");
define("API_GRADING_DETAILS", "PreProcessing/GradingDetails"); //AjaxGrading.php @DurgaPrasad
define("API_HAVL_BILL_DETAILS", "PreProcessing/HLVABillDetails"); //AjaxHAVLBill.php @DurgaPrasad
define("API_HL_YIELD_REPORT", "PreProcessing/HlYieldReport"); // Ajax/ajax-hl-yield-report.php
define("API_DE_HEADING_REPORT", "PreProcessing/DeHeadingReport"); // @DurgaPrasad
define("API_HL_VA_DAILY", "PreProcessing/HlVaBatchList"); // @DurgaPrasad
define("API_HLVA_RATES_LIST", "PreProcessing/HLVARatesList"); // @DurgaPrasad
define("API_HLVA_RATES_PRINT", "PreProcessing/HLVARatesPrint"); // @DurgaPrasad
define("API_PPC_STATIONS_LIST", "PreProcessing/PpcStationsList"); // @DurgaPrasad
define("API_HLVA_VARIETY_LIST", "PreProcessing/HLVAVarietyList"); // @DurgaPrasad
define("API_CREATE_HLVA_VARIETY", "PreProcessing/CreateHLVAVariety"); // @DurgaPrasad
define("API_CREATE_PPC_STATIONS", "PreProcessing/CreatePpcStation"); // @DurgaPrasad
define("API_ARRIVAL_RECEIVE", "PreProcessing/ArrivalReceivalPage"); // ArrivalReceivalPage.php @DurgaPrasad
define("API_DE_HEADING_TRACKING_DETAILS", "PreProcessing/DeheadingTrackingDetails"); //ajax-de-heading-trackid.php
define("API_RECEIVAL_TRACK_LIST", "PreProcessing/ReceivalTrackIDDetails"); //ajax-receival-trackid.php
// define("API_RECEIVAL_TRACK_BREAKUP", "PreProcessing/ReceivalTrackIDBreakUp"); //ajax-receival-trackid.php
define("API_GRADING_TRACK_DETAILS", "PreProcessing/GradingTrackDetails"); //ajax-grading-trackid.php
define("API_VALUE_ADDITION_TRACKING_DETAILS", "PreProcessing/ValueAdditionTrackingDetails"); //ajax-value-addition-trackID.php
define("API_HON_PACKING", "PreProcessing/HonPacking"); //ajax-hon-packing.php
define("API_RECEIVING_OVERALL_REPORT", "PreProcessing/RmReceivedDetails"); //ajax-receiving-trackid-overall-report.php
define("API_GRADING_OVERALL_REPORT", "PreProcessing/GradingOveralReport"); //ajax-grading-overall-report.php
define("API_FLOOR_BALANCE_REPORT", "PreProcessing/VaFloorBalanceReport"); //ajax-va-floor-balance-report.php
define("API_DEHEADING_OVERALL_REPORT", "PreProcessing/DeheadingOveralReport"); //ajax-deheading-report-new.php @DurgaPrasad
define("API_ASSUMED_REPORT", "PreProcessing/AssumedReport"); //ajax-assumed-report.php @komali
define("API_VALUE_ADDITION_TRANSFER_REPORT", "PreProcessing/ValueAdditionTransferReport"); //ajax-value-addition-report.php @komali
define("API_VALUE_ADDITION_HON_TRANSFER", "PreProcessing/ValueAdditionHonTransfer"); //ajax-value-addition-hon-transfer.php @durgaPrasad
define("API_CONSOLIDATION_REPORT", "PreProcessing/ConsolidationDailyAndMonthlyReport"); //ajax-value-addition-hon-transfer.php @durgaPrasad
define("API_VA_HL_TRANSFER_REPORT", "PreProcessing/VAHLTransferReport"); //ajax-va-hl-transfer-report.php @komali
define("API_TRACK_REPORT", "PreProcessing/TrackReport"); //ajax-track-report.php @uma
define("API_CREATE_CRATE_DATA", "PreProcessing/CreateCrateData"); //ajax-crate-creation.php @uma
define("API_SHIPMENT_GRADE_WISE_REPORT", "PreProcessing/ShipmentGradeWiseReport");
define("API_PLANT_CHECK_REPORT", "PreProcessing/PlantCheckReport"); //Uma
define("API_VALUE_ADDITION_REPORT", "PreProcessing/ValueAdditionReport"); //value-addition-report.php // Ramesh
define("API_MATERAIL_DISPATCH_CREATE", "PreProcessing/MaterialDispatchCreate"); //ajax-material-dispatchment-list.php// UMA
define("API_MATERAIL_DISPATCH_REPORT", "PreProcessing/MaterialDispatchReport"); //ajax-material-dispatchment-report.php// UMA
// define("API_DEHEADING_CHECK_LIST", "PreProcessing/DeheadingList"); 

define("API_CONSOLIDATION_REPORT_PACKING_STYLE", "PreProcessing/ConsolidationReport"); //ajax-value-addition-hon-transfer.php @durgaPrasad



//PRODUCTION//

define("API_ADDLQREPORT", "Production/Addlqreport");
define("API_PRODUCTION_DROPDOWNS", "Production/Dropdowns"); // @DurgaPrasad
define("API_SOAKING_DETAILS", "Production/SoakingDetails"); // @DurgaPrasad
define("API_SOAKING_TRACKID_DETAILS", "Production/SoakingTrackIDDetails"); // @DurgaPrasad
define("API_SOAKING_REPORTS", "Production/SoakingReport"); // ajax-soaking-report @Mahesh
define("API_TREATMENT_REGISTER_REPORT", "Production/TreatmentRegisterReport.php"); // ajax-treatment-register-report @uma

define("API_FREEZING_REPORT", "Production/FreezingReport"); // ajax-freezing-report @Komali
define("API_PRODUCTION_STANDARD_YIELDS", "Production/ProductionYields"); // ajax-production-standard-yields @Uma
define("API__RECONCILIATION_REPORT", "Production/ReconciliationReport"); // ajax-production-and-purchase-reconciliation-report @Mahesh
define("API__SOAKING_FLOOR_BALANCE_REPORT", "Production/SoakingFloorBalanceReport"); // ajax-ajax-soaking-floor-balance-report @uma
define("API_HOTOFINISHED_REPORT", "Production/HoToFinishedReport"); //Ajax/AjaxReportList @Ramesh
define("API_REGLAZING", "Production/ReglazingReport");
define("API_HOTOFINISHED_REPORT_DEVI", "Production/HoToFinishedReportDevi"); // @Mahi
define("API__SOAKING_FLOOR_BALANCE_REPORT_VARIETY", "Production/SoakingFloorBalanceVarietyReport"); // @mahi


//Cold Store//
define("API_COLDSTORE_DROPDOWNS", "ColdStore/Dropdowns"); //
define("API_STOCK_OUT_DROPDOWNS", "ColdStore/StockOutDropdowns"); //
define("API_DEPENDANT_DROPDOWNS", "ColdStore/DependantDropdowns"); // AjaxInward.php
define("API_CREATE_STORE_LAYOUT", "ColdStore/CreateStoreLayout"); //
define("API_DAILY_INWARD_DETAILS", "ColdStore/DailyInwardDetails"); //
define("API_DAILY_INWARD", "ColdStore/DailyInwardList"); // @DurgaPrasad
define("API_DAILY_OUTWARD", "ColdStore/DailyOutwardList"); // @DurgaPrasad
define("API_PLACE_PALLET", "ColdStore/PlacePallet"); // AjaxInward.php
define("API_CREATE_DAILY_PRODUCTION_DATA", "ColdStore/CreateDailyProductionData"); // uma //AjaxDailyProductionData
define("API_DAILY_PRODUCTION_DETAILS", "ColdStore/DailyProductionDetails"); // uma //ajax-daily-production-list
define("API_ADMIN_DAILY_PRODUCTION_DETAILS", "ColdStore/AdminDailyProductionDetails"); // Komali //ajax-admin-daily-production-list
define("API_FREEZING_TRACKID_DETAILS", "Production/FreezingTrackIDDetails"); // uma //ajax-freezing-trackid
define("API_STORE_RACK_DETAILS", "ColdStore/StoreRackDetails"); //inward.php
define("API_STORE_GENERAL_OPTIONS", "ColdStore/GeneralOptions"); //GeneralOptions.php @durgaPrasad
define("API_STORE_GENERAL_CATEGORY", "ColdStore/GeneralCategories"); //GeneralCategories.php @durgaPrasad
define("API_STORE_TENANTS", "ColdStore/StoreTenants"); //StoreTenants.php @durgaPrasad
define("API_STORE_COLDSTORES", "ColdStore/Coldstores"); //Coldstores.php @durgaPrasad
define("API_FLOOR_DATA", "ColdStore/FloorData"); //
define("API_BLOCK_DATA", "ColdStore/BlockData"); //
define("API_IBT_REQUEST", "ColdStore/CsIBTRequestEntry"); // CsIBTRequestEntry.php @durgaPrasad
define("API_IBT_REQUEST_BreakUp", "ColdStore/CsIBTBreakUp"); // CsIBTBreakUp.php @durgaPrasad
define("API_DAILY_FREEZING_INWARD_DETAILS", "ColdStore/CreateDailyFreezingDetails"); // CsIBTBreakUp.php @durgaPrasad
define("API_STOCKOUT", "ColdStore/StockOut"); // AjaxStockOut.php
define("API_STOCK_PREVIEW", "ColdStore/CsIBTBreakUpPreview"); // CsIBTBreakUpPreview.php @durgaPrasad
define("API_STOCK_OUT_DETAILS", "ColdStore/DailyStockOutDetails"); // DailyStockOutDetails.php @durgaPrasad
define("API_STOCK_PALLET_DETAILS", "ColdStore/PalletDetails"); // PalletDetails.php @durgaPrasad
define("API_STORE_ON_FLOOR_STOCK", "ColdStore/StoreOnFloorStock"); // ajax-store-on-floor-stock.php @durgaPrasad
define("API_STOCK_OPTIONS", "ColdStore/StockOptionDetails"); // ajax-stock-options.php @durgaPrasad
define("API_STORE_STOCK_SEARCH", "ColdStore/StoreStockSearch"); // ajax-store-stock-search.php @durgaPrasad
define("API_PO_STATUS_REPORT", "ColdStore/PoStatusReports"); // ajax-po-status-reports.php @durgaPrasad
define("API_SHIPMENT_OUT", "ColdStore/ShipmentOut"); // ajax-shipment-out.php @durgaPrasad
define("API_RUNNING_ORDERS", "ColdStore/RunningOrders"); // ajax-running-orders.php @durgaPrasad
define("API_STOCK_REPORT", "ColdStore/StockReport"); // ajax-stock-report.php @durgaPrasad
define("API_MONTHLY_PRODUCTION_REPORT", "ColdStore/MonthlyProductionReport"); // ajax-monthly-production-report @komali
define("API_INDEX_PAGE", "ColdStore/Index"); // ajax-index.php @durgaPrasad
define("API_ATTACH_STOCK_TOALREADY_FILLED_PALLET", "ColdStore/AttachStocktoAlreadyFilledPallet"); // ajax-store-on-floor-stock.php
define("API_ADMIN_INWARD_APPROVAL", "ColdStore/AdminInwardApproval"); // ajax-index.php @durgaPrasad
define("API_RACKWISE_STOCK_REPORT", "ColdStore/RackWiseStockReport"); // ajax-rackwise-stock-report.php @durgaPrasad
define("API_RAW_MATERIAL_REQUIREMENT_REPORT", "ColdStore/RawMaterialRequirementReport"); // ajax-rm-req.php @UMA
define("API_GRADE_MASTER_HL_COUNT", "ColdStore/GradeMasterHlCount"); // GradeMasterHlCount.php @durgaPrasad
define("API_EXCESS_STOCK_REPORT", "ColdStore/ExcessStockReport"); // ajax-excess-stock.php @durgaPrasad
define("API_STOCK_SUMMARY", "ColdStore/StockSummary"); // stock-summary.php @durgaPrasad
define("API_SHIPPED_PO_LIST", "ColdStore/ShippedPoList"); // ajax-shipped-po-list.php @komali
define("API_PO_OUT_POPUP", "ColdStore/PoOutPopUp"); // ajax-po-out-popup.php @durgaPrasad
define("API_OUTWARD_REPORT", "ColdStore/OutWardReport"); // ajax-outward-report.php @Komali
define("API_EXCESS_ORDER_REPORT", "ColdStore/ExcessOrderReport"); // ajax-excess-order-report.php @Komali
define("API_INWARD_REPORT", "ColdStore/InwardReport"); // ajax-inward-report.php 
define("API_CS_GROUP_MASTERS", "ColdStore/CsGroupMasters"); //ajax-cs-group-list.php @Komali
define("API_DAILY_PRODUCTION_REPORT", "ColdStore/DailyProductionReport"); //ajax-daily-production-report @Mahesh
define("API_MONTHLY_SLABS_PRODUCTION_REPORT", "ColdStore/MonthlySlabsProductionReport"); //ajax-monthly-production-report @Mahesh
define("API_CLOSING_AND_OPENING_STOCK_REPORT", "ColdStore/ClosingAndOpeningReport"); //ajax-closing-and-opening-stock-report @Komali
define("API_VARIETYWISE_STOCK_REPORT", "ColdStore/VarieywiseStockReport"); //ajax-varietywise-stock-report @durgaPrasad
define("API_SHIPMENT_STATUS_REPORT", "ColdStore/ShipmentStatusReport"); //ajax-shipment-status-report @durgaPrasad
define("API_THAWING_REPORT", "ColdStore/ThawingReport"); //ajax-thawing-report @durgaPrasad
define("API_SHIPMENT_REPORT", "ColdStore/ShipmentReport"); //ajax-shipment-report-daily-and-monthly @Komali
define("API_REPACKING_REPORT", "ColdStore/RepackingReport"); //ajax-repacking-report @durgaPrasad
define("API_IBT_OUT", "ColdStore/IbtOut"); //ajax-cs-ibt-breakup-preview @durgaPrasad
define("API_STOCK_SUMMARY_REPORT", "ColdStore/StockSummaryReport"); //ajax-varietywise-stock-report @durgaPrasad
define("API_IBT_REPORT", "ColdStore/IbtReport"); //ajax-inter-branch-transfer-report @komali
//define("API_CS_MONTHLY_PRODUCTION_REPORT", "ColdStore/MonthlyProductionReport"); // ajax-monthly-production-report @komali
define("API_ORDER_PACKING_TIMELINE_REPORT", "ColdStore/OrderPackingTimelineReport"); //ajax-order-packing-timeline @Prasad
define("API_EXCESS_STOCK_REPORT_GRADEWISE", "ColdStore/ExcessStockReportGradewise"); //ajax-excess-stock-report-gradewise @Prasad
define("API_STOCK_OVERALL_REPORT", "ColdStore/StockOverallReport"); //ajax-stock-overall-report @durgaPrasad
define("API_PHYSICAL_REPORT", "ColdStore/PhysicalReport"); //ajax-physical-report
define("API_RETURN_CONTAINER_LATEST", "ColdStore/ReturnContainerReport1");


define("API_STOCK_PRODUCTION_REPORT", "ColdStore/StockProductionReport"); // ajax-stock-production-report.php @sriram


// Store 
define("API_STORE_DROPDOWN", "Store/Dropdowns"); // DurgaPrasad page-filter.php
define("API_GS_CHEM_PO", "Store/GsChemPo"); // DurgaPrasad AjaxGsChemPoList.php
define("API_GS_CHEM_PO_BrkUP", "Store/GsChemPoBrkUp"); // DurgaPrasad GsChemPoBrkUp.php
define("API_GS_CHEM_PO_ORDERS", "Store/GsChemOrders"); // DurgaPrasad GsChemOrders.php
define("API_GS_CHEM_PO_ORDERS_LIST", "Store/GsChemOrdersList"); // DurgaPrasad GsChemOrdersList.php
define("API_GS_CHEM_PO_PAYMENTS", "Store/GsChemPoPayment"); // DurgaPrasad GsChemPoPayment.php
define("API_GS_CHEM_PO_PAYMT_LIST", "Store/GsChemPoPmtList"); // DurgaPrasad GsChemPoPmtList.php
define("API_GS_CHEM_CONSUMPSTION_LIST", "Store/GsChemConsumptionList"); // DurgaPrasad GsChemConsumptionList.php
define("API_GS_CHEM_CONSUMPTION", "Store/GsChemConsumption"); // DurgaPrasad GsChemConsumption.php
define("API_FS_AUDIT", "Store/FsAudit"); // DurgaPrasad FsAudit.php
define("API_FS_AUDIT_MILL", "Store/FsAuditMill"); // DurgaPrasad FsAuditMill.php
define("API_FS_AUDIT_PAYMENT", "Store/FsAuditPayment"); // DurgaPrasad FsAuditPayment.php
define("API_FS_MILL_PAYMENT", "Store/FsAuditMillPayment"); // DurgaPrasad FsAuditMillPayment.php
define("API_CHEM_BUYER", "Store/GsChemBuyer"); // DurgaPrasad GsChemBuyer.php
define("API_CHEMICAL", "Store/GsChemical"); // DurgaPrasad GsChemical.php
define("API_CHEMICAL_PO_PRINT", "Store/GsChemPoPrint"); // DurgaPrasad GsChemPoPrint.php
define("API_CHEMICAL_SUPPLIER", "Store/GsChemSupplier"); // DurgaPrasad GsChemSupplier.php
define("API_HATCHERY_MASTERS", "Store/HatcheryMasters"); // DurgaPrasad HatcheryMasters.php
define("API_CHEMICAL_OPENING_REPORT", "Store/GsChemicalOpeningReport"); // DurgaPrasad GsChemicalOpeningReport.php
define("API_CHEMICAL_OPENING_BALANCE_REPORT", "Store/GsChemOpenignBalanceReport"); // sriram GsChemicalOpeningReport.php


//Anti Dumping
define("API_ANTI_DUMPING_DETAILS", "AntiDumping/AntiDumpingDetails");
define("API_ANTI_DUMPING_REPORT", "AntiDumping/AntiDumpingReport");
define("API_ANTI_DUMPING_NEGATIVE_REPORT", "AntiDumping/AntiDumpingNegativeReport");
define("API_ANTI_DUMPING_SHIPMENT_REPORT", "AntiDumping/AntiDumpingShipmentReport");
define("API_ANTI_DUMPING_OPENING_CLOSING_REPORT", "AntiDumping/AntiDumpingOpeningClosingReport");

// HRM
define("API_HRM_DROPDOWNS", "HumanResource/Dropdowns"); //
define("API_HRM_SALARY_ELEMENTS_MASTER_LIST", "HumanResource/SalaryElementsMasterList"); // AjaxSalaryElements.php
define("API_HRM_SALARY_STRUCTURE_MASTER", "HumanResource/SalaryStructureMaster"); //
define("API_HRM_SALARY_STRUCTURE_LIST", "HumanResource/EmployeeSalaryStractures"); // ROBO
define("API_HRM_EMPLOYEE_CREATION", "HumanResource/EmployeeCreation"); //
define("API_HRM_BRANCH_MASTER", "HumanResource/HrmBranchMasters"); // HrmBranchMasters.php


define("API_APP_ACCESS_ROLES", "App/AccessRoles"); //
define("GET_ACCESS_ROLES", "App/AccessRolesList"); //durgaprasad
define("GET_USER_LIST", "App/Users"); //Teja
define("API_CLIENT_DETAILS", "App/ClientDetails"); // ClientDetails.php durgaPrasad


/***  Inventory Module */

define("API_GENERAL_ITEMS", "Inventory/GeneralItems");
define("API_PRICE_MASTER", "Inventory/PriceMaster");
define("API_GST_MASTER", "Inventory/GstMaster");
define("API_INVENTORY_DROPDOWNS", "Inventory/Dropdowns");
define("API_VENDOR", "Inventory/VendorMasterController"); // VendorMaster.php
define('API_DEPARTMENT', "Inventory/DepartmentMasterController"); // DepartmentMaster.php');
define('API_BRAND', "Inventory/BrandMasterController"); // BrandMaster.php
define("API_GOODS_RECEIPT_NOTE", "Inventory/GoodsReciptNoteGs");
define("API_MATERIAL_ISSUE", "Inventory/MaterialIssueGs");
define("API_INDENT_DETAILS", "Inventory/IndentDetails");
define("API_PO_GS", "Inventory/PoGeneralStores");

define("API_GRN_REPORT", "Inventory/GrnReportController");
define("API_PO_REPORT", "Inventory/PoGeneralReportController");
define("API_GS_INDENTLIST_REPORT", "Inventory/GsIndentListReport");

define("API_PM_PROFORMA_INVOICE", "Inventory/PmProformaInvoiceDetails");
define("API_PM_PURCHASE_ORDER", "Inventory/PmPurchaseOrderDetails");
define("API_GOODS_RECEIPT_NOTE_PACKAGING", "Inventory/GoodsReciptNotePackaging");
define("API_MATERIAL_ISSUE_PACKAGING", "Inventory/MaterialIssuePackaging");

define("API_PM_PROFORMA_REPORT", "Inventory/Pm-proformaReportController");
define("API_GOODS_REPORT", "Inventory/Pm-goods-noteReportController");
define("API_PM_PURCHASE_REPORT", "Inventory/Pm-purchaseReportController");


// define("API_PURCHASE", "Inventory/PorformaRegisterController");
// define("API_PROFORMA_CREATE", "Inventory/PorformaRegisterCreateController");


// SME
define("API_RAW_MATERIAL_REQUIREMENT_REPORT_SME", "ColdStore/RawMaterialRequirementReportSME"); // ajax-rm-req-sme.php @Sriram



/**** Old format APIS */

define("GetShipingBillList_API", "Api?f=getShipingBillList");  // AjaxShippingBill.php & AjaxFdaDetails.php
define("GetShipBillDetails_API", "Api?f=getShipBillDetails");  // ShippingBillForm.php 
define("PostShippingBillDetails_API", "Api?f=postShippingBillDetails");  // ShippingBillForm.php 
define("GetPendingInsuranceList_API", "Api?f=getPendingInsuranceList"); // AjaxInsuranceReportList.php, DocExlInsuranceReportList.php 
define("GetPendingContracts_API", "Api?f=getPendingContracts"); // Dashboard.php
define("GetPendingOrdersonHand_API", "Api?f=getPendingOrdersonHand"); // Dashboard.php
define("GetPortWisePoData_API", "Api?f=getPortWisePoData"); // Dashboard.php
define("GetPendingPaymentsData_API", "Api?f=getPendingPaymentsData"); // Dashboard.php
define("GetProformaInvoiceList_API", "Api?f=getProformaInvoiceList"); // AjaxPIList.php
define("GetCollectionList_API", "Api?f=getCollectionList"); // AjaxCollectionList.php
// define("GetNegotiationList_API", "Api?f=getNegotiationList"); // AjaxNegotiationList.php
define("GetRealizationList_API", "Api?f=getRealizationList"); // AjaxCollectionList.php
define("GetForwardContractorList_API", "Api?f=getForwardContractorList"); // AjaxForwardContract.php
define("PostFDADetails_API", "Api?f=postFDADetails"); // AddFDADetails.php
define("CreateInsuranceTemplate_API", "Api?f=createInsuranceTemplate"); // AddFDADetails.php 
define("GetBillRealizationDetails_API", "Api?f=getBillRealizationDetails"); // RealizationForm.php 
define("SaveBillRealizationDetails_API", "Api?f=saveBillRealizationDetails"); // RealizationForm.php 
define("GetInsuranceTemplatesList_API", "Api?f=getInsuranceTemplatesList"); // InsuranceTemplates.php 
define("GetInsuranceTemplateInvoicesList_API", "Api?f=getInsuranceTemplateInvoicesList"); // InsuranceTemplates.php 
define("DeleteInsuranceTemplate_API", "Api?f=DeleteInsuranceTemplate"); // AjaxInsuranceTemplateDelete.php 
define("GetPendingInsurancePaymentsList_API", "Api?f=getPendingInsurancePaymentsList"); // AjaxInsurancePayments.php 
define("CreatePaymentsTemplate_API", "Api?f=createPaymentsTemplate"); // InsurancePayments.php 
define("DeleteInsurancePaymentsTemplate_API", "Api?f=DeleteInsurancePaymentsTemplate"); // InsurancePayments.php 
define("GetInsurancePaymentData_API", "Api?f=getInsurancePaymentData"); // AjaxInsurancePaymentList.php 
define("PostPaymentsInsuranceData_API", "Api?f=PostPaymentsInsuranceData"); // AjaxInsurancePaymentList.php 
define("GetEtdEtaReportAPI", "Api?f=getEtd_EtaReport"); // AjaxEtaPoList.php 
define("GetPendingNegotiationReportAPI", "Api?f=getPendingNegotiationReport"); // AjaxPendingNegoReport.php 
define("GetBillRealizationReportAPI", "Api?f=getBillRealizationReport"); //  
define("GetPendingPaymentsReportAPI", "Api?f=getPendingPaymentsReport"); // 
define("GetStoreDeliveryDetailsAPI", "Api?f=getStoreDeliveryDetails"); // StoreDeliveryDetailsList.php 
define("GetMpedaReportDataAPI", "Api?f=getMpedaReportData"); // AjaxMpedaList.php 
define("GetShipmentReportDataAPI", "Api?f=getShipmentReportData"); // AjaxShipmntReportList.php 
define("GetEditPageStoreDeliveryDetailsAPI", "Api?f=getEditPageStoreDeliveryDetails"); // AddStoreDeliveryDetails.php 
define("PostStoreDeliveryDetailsAPI", "Api?f=PostStoreDeliveryDetails"); // AddStoreDeliveryDetails.php 
define("GetInvoicesPrintPagesListAPI", "Api?f=getInvoicesPrintPagesList"); // AddStoreDeliveryDetails.php 
define("GetClearingAgentDetailsAPI", "Api?f=getClearingAgentDetails"); // AddStoreDeliveryDetails.php 
define("GetClearingAgentReportDataAPI", "Api?f=getClearingAgentReportData"); // AddStoreDeliveryDetails.php 
define("ApproveClearingAgentReportDataAPI", "Api?f=approveClearingAgentReportData");
define("GetClearingAgentFormDataAPI", "Api?f=getClearingAgentFormData"); // ClearingAgentForm.php 
define("PostClearingAgentDetailsAPI", "Api?f=PostClearingAgentDetails"); // ClearingAgentForm.php 
define("PostClearingAgentBreakUpDetailsAPI", "Api?f=PostClearingAgentBreakUpDetails"); // ClearingAgentForm.php 
define("DeleteClearingAgentDetailsAPI", "Api?f=deleteClearingAgentDetails"); // ClearingAgentsList.php 
define("GetForwardContractUtilisedReportDataAPI", "Api?f=getForwardContractUtilisedReportData"); // ClearingAgentsList.php 
define("GetClearingAgentBreakupListAPI", "Api?f=getClearingAgentBreakupList"); // ClearingAgentsList.php 
define("GetFDAExaminationReportDataAPI", "Api?f=getFDAExaminationReportData"); // ClearingAgentsList.php 
define("GetFDAListAPI", "Api?f=getFDAList"); // FdaDetails.php 
define("QCertificateReportAPI", "Api?f=QCertificateReport"); // FdaDetails.php
define("GetFDAReleaseInformationAPI", "Api?f=getFDAReleaseInformation"); // FDAReleaseInformation.php
// define("QCertificateReportAPI", "Api?f=QCertificateReport"); // 

define("PostProfromaPrint", "Api?f=ProformaPrint"); // ClearingAgentsList.php 
define("CREATE_RISE_TICKET", "Api?f=RiseIssue"); // ClearingAgentsList.php 
define("API_DELETE_BILL", "Api?f=postDeleteGrading"); // DeleteBill.php 
define("API_DELETE_HL_GRADING", "Api?f=postDeleteGrading"); // DeleteHLGrading.php 
define("API_DELETE_HO_GRADING", "Api?f=postHonDeleteGrading"); // DeleteHOGrading.php 
define("API_DELETE_ONLY_BILL", "Api?f=postDeleteBillOnly"); // DeleteOnlyBill.php 
define("API_EDIT_BILL", "Api?f=postEditBill"); // EditBill.php 
define("API_EDIT_LOT", "Api?f=postEditLot"); // EditBill.php 

/* QC Quality Assurance Check List Strt */
define("API_QC_QUALITY_ASSURANCE_CHECK_LIST_GET_BATCH_DATA", "QualityControl/QCQualityAssuranceChekList?f=getBatchData");
define("API_QC_QUALITY_ASSURANCE_CHECK_LIST_DROPDOWNS", "QualityControl/QCQualityAssuranceChekList?f=getDropdowns"); //
define('API_QC_QUALITY_ASSURANCE_CHECK_LIST_POST', "QualityControl/QCQualityAssuranceChekList?f=PostData"); //
define('API_QC_QUALITY_ASSURANCE_CHECK_LIST_DELETE', "QualityControl/QCQualityAssuranceChekList?f=DeleteQualityCheckListById"); //
define('API_QC_QUALITY_ASSURANCE_CHECK_LIST_GET', "QualityControl/QCQualityAssuranceChekList?f=GetQualityCheckListById"); //
define('API_QC_QUALITY_ASSURANCE_CHECK_LIST_GET_FOR_LISTING', "QualityControl/QCQualityAssuranceChekList?f=GetQualityCheckList"); //
/* QC Quality Assurance Check List ends */
//
//@durgaprasad
/*UNT report*/
define("API_UNT_REPORT", "Production/UNTReport");
/*UNT report ends*/
define("API_PURCHASE_REPORT_ITEM_WISE_SUMMARY", "RmPurchase/RmPurchaseItemWiseSummaryReport"); // AjaxPurchaseItemWiseReport.php
/*packing store starts*/
define("API_DAILY_PACKING_STORE_INWARD_DETAILS", "ColdStore/DailyPackingStoreInwardDetails"); //
/*packing store ends*/
define("API_FREEZING_PRODUCTION_REPORT", "Production/FreezingProductionReport"); // ajax-freezing-report @Komali
define("API_VALUE_ADDITION_REPORT_GROUP", "PreProcessing/ValueAdditionReportGroup");
/************************** */


define("API_PURCHASE_COLDSTORE_REPORT", "QualityControl/PurchaseColdStoreReport");
define("API_VARIETYWISE_STOCK_REPORT_NEW", "ColdStore/VarieywiseStockReportNew"); //ajax-varietywise-stock-report @durgaPrasad
define("API_DEHEADING_BREAKUP_DATA", "PreProcessing/DeHeadingBreakupData"); 

define("API_DAILY_SLABS_PRODUCTION_REPORT", "ColdStore/DailySlabsProductionReport"); 
define("API_STOCK_SHIFTING_TO_ANOTHER_RACK", "ColdStore/StockShiftingtoAnotherRack"); 
define("API_PACKING_STORE_REPORT", "ColdStore/PackingStoreReport"); 
define("API_PK_CREATE_STORE_LAYOUT", "ColdStore/PKCreateStoreLayout"); //
define("API_STOCK_VALUE_REPORT", "ColdStore/StockValueReport"); 
define("API_GRADING_SUMMERY_REPORT", "PreProcessing/GradingSummeryReport"); 
define("API_ANTI_DUMPING_CLOSING_REPORT", "AntiDumping/AntiDumpingClosingReport.php");
define("API_DAILY_PRODUCTION_REPORT_FINAL_AND_DUMMY", "ColdStore/DailyProductionReportFinalAndDummy");
define("StoreCertificateNumber_API", "Api?f=StoreCertificateNumber_API");
define("API_CONSOLIDATION_REPORT_PACKING_STYLE", "PreProcessing/ConsolidationReport");
define("API_STOCK_VALUE_REPORT_LANDSCAPE", "ColdStore/StockValueReportLandScape"); 
define("API_GRADING_REPORT_V3", "PreProcessing/GradingReportV3");