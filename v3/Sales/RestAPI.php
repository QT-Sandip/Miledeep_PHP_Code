<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
$root = $_SERVER['DOCUMENT_ROOT'] . "/";
include_once $root.'config/config.php';
include_once $root.'config/api-call.php';

//echo ROOT;


// define("BASEURL_OLD", "https://camaroo.in/ApiV2/");

// define("GetShipingBillList_API", "Api?f=getShipingBillList");  // AjaxShippingBill.php & AjaxFdaDetails.php
// define("GetShipBillDetails_API", "Api?f=getShipBillDetails");  // ShippingBillForm.php 
// define("PostShippingBillDetails_API", "Api?f=postShippingBillDetails");  // ShippingBillForm.php 
// define("GetPendingInsuranceList_API", "Api?f=getPendingInsuranceList"); // AjaxInsuranceReportList.php, DocExlInsuranceReportList.php 
// define("GetPendingContracts_API", "Api?f=getPendingContracts"); // Dashboard.php
// define("GetPortWisePoData_API", "Api?f=getPortWisePoData"); // Dashboard.php
// define("GetPendingPaymentsData_API", "Api?f=getPendingPaymentsData"); // Dashboard.php
// define("GetProformaInvoiceList_API", "Api?f=getProformaInvoiceList"); // AjaxPIList.php
// define("GetCollectionList_API", "Api?f=getCollectionList"); // AjaxCollectionList.php
// // define("GetNegotiationList_API", "Api?f=getNegotiationList"); // AjaxNegotiationList.php
// define("GetRealizationList_API", "Api?f=getRealizationList"); // AjaxCollectionList.php
// define("GetForwardContractorList_API", "Api?f=getForwardContractorList"); // AjaxForwardContract.php
// define("PostFDADetails_API", "Api?f=postFDADetails"); // AddFDADetails.php
// define("CreateInsuranceTemplate_API", "Api?f=createInsuranceTemplate"); // AddFDADetails.php 
// define("GetBillRealizationDetails_API", "Api?f=getBillRealizationDetails"); // RealizationForm.php 
// define("SaveBillRealizationDetails_API", "Api?f=saveBillRealizationDetails"); // RealizationForm.php 
// define("GetInsuranceTemplatesList_API", "Api?f=getInsuranceTemplatesList"); // InsuranceTemplates.php 
// define("GetInsuranceTemplateInvoicesList_API", "Api?f=getInsuranceTemplateInvoicesList"); // InsuranceTemplates.php 
// define("DeleteInsuranceTemplate_API", "Api?f=DeleteInsuranceTemplate"); // AjaxInsuranceTemplateDelete.php 
// define("GetPendingInsurancePaymentsList_API", "Api?f=getPendingInsurancePaymentsList"); // AjaxInsurancePayments.php 
// define("CreatePaymentsTemplate_API", "Api?f=createPaymentsTemplate"); // InsurancePayments.php 
// define("DeleteInsurancePaymentsTemplate_API", "Api?f=DeleteInsurancePaymentsTemplate"); // InsurancePayments.php 
// define("GetInsurancePaymentData_API", "Api?f=getInsurancePaymentData"); // AjaxInsurancePaymentList.php 
// define("PostPaymentsInsuranceData_API", "Api?f=PostPaymentsInsuranceData"); // AjaxInsurancePaymentList.php 
// define("GetEtdEtaReportAPI", "Api?f=getEtd_EtaReport"); // AjaxEtaPoList.php 
// define("GetPendingNegotiationReportAPI", "Api?f=getPendingNegotiationReport"); // AjaxPendingNegoReport.php 
// define("GetBillRealizationReportAPI", "Api?f=getBillRealizationReport"); //  
// define("GetPendingPaymentsReportAPI", "Api?f=getPendingPaymentsReport"); // 
// define("GetStoreDeliveryDetailsAPI", "Api?f=getStoreDeliveryDetails"); // StoreDeliveryDetailsList.php 
// define("GetMpedaReportDataAPI", "Api?f=getMpedaReportData"); // AjaxMpedaList.php 
// define("GetShipmentReportDataAPI", "Api?f=getShipmentReportData"); // AjaxShipmntReportList.php 
// define("GetEditPageStoreDeliveryDetailsAPI", "Api?f=getEditPageStoreDeliveryDetails"); // AddStoreDeliveryDetails.php 
// define("PostStoreDeliveryDetailsAPI", "Api?f=PostStoreDeliveryDetails"); // AddStoreDeliveryDetails.php 
// define("GetInvoicesPrintPagesListAPI", "Api?f=getInvoicesPrintPagesList"); // AddStoreDeliveryDetails.php 
// define("GetClearingAgentDetailsAPI", "Api?f=getClearingAgentDetails"); // AddStoreDeliveryDetails.php 
// define("GetClearingAgentReportDataAPI", "Api?f=getClearingAgentReportData"); // AddStoreDeliveryDetails.php 
// define("GetClearingAgentFormDataAPI", "Api?f=getClearingAgentFormData"); // ClearingAgentForm.php 
// define("PostClearingAgentDetailsAPI", "Api?f=PostClearingAgentDetails"); // ClearingAgentForm.php 
// define("PostClearingAgentBreakUpDetailsAPI", "Api?f=PostClearingAgentBreakUpDetails"); // ClearingAgentForm.php 
// define("DeleteClearingAgentDetailsAPI", "Api?f=deleteClearingAgentDetails"); // ClearingAgentsList.php 
// define("GetForwardContractUtilisedReportDataAPI", "Api?f=getForwardContractUtilisedReportData"); // ClearingAgentsList.php 
// define("GetClearingAgentBreakupListAPI", "Api?f=getClearingAgentBreakupList"); // ClearingAgentsList.php 
// define("GetFDAExaminationReportDataAPI", "Api?f=getFDAExaminationReportData"); // ClearingAgentsList.php 
// define("GetFDAListAPI", "Api?f=getFDAList"); // FdaDetails.php 
// define("QCertificateReportAPI", "Api?f=QCertificateReport"); // FdaDetails.php 
// // define("QCertificateReportAPI", "Api?f=QCertificateReport"); // 

// define("PostProfromaPrint", "Api?f=ProformaPrint"); // ClearingAgentsList.php 
//define("API_MENU", "Api?f=https://camaroo.in/ApiV2/App/Menu.php"); // Menu.php



// if (!function_exists('CallAPI')) {
// function CallAPI($api, $method, $headerContent, $bodyContent) {
//     if($api=='ProformaPrint'){
//         $url = "https://camaroo.in/Api/Exports/ProformaPrint";
//     }else{
//         $url = BASEURL_OLD . $api;
//     }

//      echo $url; 
    
//     $curl = curl_init($url);
//     $Token ='Bearer '.$_SESSION['Token'];
    
//     // $headers = array("Content-Type: application/json");
//    // $headers = array("Authorization: Bearer eyJhbGdvIjoiSFMyNTYiLCJ0eXBlIjoiSldUIiwiZXhwaXJlIjoxNjkyMzc0MzMwfQ==.eyJVc2VySUQiOiI2IiwiQ2xpZW50SUQiOiJTTFMyMDIxMDAzIiwidGltZSI6MTY5MjI3NDMzMH0=.N2NmOTdmYzM1N2Y2Yzk1MzdlN2U3MTNmYTk4ODNjMGU0MTk1NTU2MGI1MDA1NWQ3Yjc4M2Y0OTRmNjM1NDVmZQ==");
//     $headers = array(
//         "Content-Type: application/json", 
//         "Authorization: $Token");

//     if(!empty($headerContent)){
//         $headers = array_merge($headerContent, $headers);
//     }

//     curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);  // Headers Content    
    
//     if(isset($bodyContent) && !empty($bodyContent)){
//         $jsonBody = json_encode($bodyContent);           
//         $jsonBody = ltrim(rtrim($jsonBody, ']'),'[');
//         curl_setopt($curl, CURLOPT_POSTFIELDS, $jsonBody); // Body Content
//     }

//     curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    
//     switch ($method) {
//         case "GET":
//             curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "GET");
//             break;
//         case "POST":
//             curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "POST");
//             break;
//         case "PUT":
//             curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
//             break;
//         case "DELETE":
//             curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE"); 
//             break;
//     }
//      $response = curl_exec($curl);
    
//     $data = json_decode($response);

//     /* Check for 404 (file not found). */
//     $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
//     // Check the HTTP Status code
//     switch ($httpCode) {
//         case 200:
//             $error_status = "200: Success";
//             return ($data);
//             break;
//         case 404:
//             $error_status = "404: API Not found";
//             break;
//         case 500:
//             $error_status = "500: servers replied with an error.";
//             break;
//         case 502:
//             $error_status = "502: servers may be down or being upgraded. Hopefully they'll be OK soon!";
//             break;
//         case 503:
//             $error_status = "503: service unavailable. Hopefully they'll be OK soon!";
//             break;
//         default:
//             $error_status = "Undocumented error: " . $httpCode . " : " . curl_error($curl);
//             break;
//     }
//     curl_close($curl);
//     echo $error_status;
//     die;
// }
// }

?>