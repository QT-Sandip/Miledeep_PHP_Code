<?php

    include_once $_SERVER['DOCUMENT_ROOT'] .'/config/global.php';
    include_once ROOT.'config/api-call.php';

    $methodType = trim($_POST["methodType"]);

    $api = API_RM_SUPPLIER;
    
    // $id = $_POST["id"];
    $SupplierType = trim($_POST["SupplierType"]); // BookingID == Empty (Insert new) or Edit Booking by ID
    $SupplierName = trim($_POST["SupplierName"]);
    $PANNumber = trim($_POST["PANNumber"]);
    $GSTNumber = trim($_POST["GSTNumber"]);
    $BankAccountNumber = trim($_POST["BankAccountNumber"]);
    $SupplierBankName = trim($_POST["SupplierBankName"]);
    $IFSCCode = trim($_POST["IFSCCode"]);
    $SupplierAddress = trim($_POST["SupplierAddress"]);
    $SupplierEMailID = trim($_POST["SupplierEMailID"]);
    $PhoneNumber = trim($_POST["PhoneNumber"]);
    $PurchaseCenter = trim($_POST["PurchaseCenter"]);

    $bodyContent = array(
        // "ID" => $id,
        "SupplierType" => $SupplierType,
        "SupplierName" => $SupplierName,
        "PANNumber" => $PANNumber,
        "GSTNumber" => $GSTNumber,
        "BankAccNo" => $BankAccountNumber,
        "SupplierBank" => $SupplierBankName,
        "IFSCCode" => $IFSCCode,
        "EMailID" => $SupplierEMailID,
        "Address" => $SupplierAddress,
        "PhoneNumber" => $PhoneNumber
    );

    $resSupplier = CallAPI($api, "POST", "", $bodyContent);

    // var_dump($resSupplier);

    $resResults = json_encode($resSupplier);

    header('Content-Type: application/json');
    echo $resResults;

?>