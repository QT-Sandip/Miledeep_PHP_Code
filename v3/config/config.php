<?php
include_once __DIR__.'/../config/BaseUrl.php';
$BaseUrlConfig = new BaseUrl;
$BaseUrlConfig = $BaseUrlConfig->getBaseUrl(true);
$host = $BaseUrlConfig->protocol."://$BaseUrlConfig->host" . '/';
define("BASEURL", $host."ApiV2/");

define("API_LOGIN", "Auth/AuthLogin.php"); // Login.php
define("API_MENU", "App/Menu.php"); // Menu.php

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
define("API_PDF_PROFORMA_PRINT", "Exports/ProformaPrint");
define("API_ORDER_STATUS", "Exports/OrderStatus"); // AjaxProformaListActions.php
define("API_PO_STATUS_DETAILS", "Exports/PoStatusDetails"); // AjaxProformaListActions.php
define("API_SHIPMENT_SALES_LINEWISE_REPORT", "Exports/ShipmentLineItemwiseReport"); // AjaxShipmentSalesLineItemwiseReport.php



/* Purchase - Module */
define("API_DROPDOWNS", "App/Dropdowns"); // For App Level
define("API_EXPORTS_DROPDOWNS", "App/Dropdowns"); // For Sales
//define("API_RM_ARRIVAL_LIST", "Purchase/RmArrivalListDetails"); // RmArrivalList.php

define("API_RM_DROPDOWNS", "RmPurchase/Dropdowns"); //For Purchase

define("API_RM_BOOKING_LIST", "RmPurchase/RmBookingList"); //rm-booking-list.php
define("API_RM_BOOKING", "RmPurchase/RmBooking"); // create-booking.php

define("API_RM_ARRIVAL_LIST", "RmPurchase/RmArrivalList"); //rm-arrival-list.php
define("API_RM_ARRIVAL", "RmPurchase/RmArrival"); //create-arrival.php

define("API_RM_BILLS_LIST", "RmPurchase/RmBillList"); //rm-bills-list.php
define("API_RM_BILLS", "RmPurchase/RmBill"); //create-bills.php





?>