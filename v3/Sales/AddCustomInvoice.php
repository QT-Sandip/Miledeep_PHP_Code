<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
include($cp . "Includes/CommercialInvoiceLock.php");

$PageTitleFolder = "Sales";
$PageTitle = "Custom Invoice Creation";

$DocType = 'CUI';
$PIId = $_GET['PIID'];
$Backurl = $_GET['backurl']; //uma
$ExporterID = $_GET['ExporterID'];
$PostedUserId = $_SESSION['UserID'];
$PageUrl = ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME));

//print_r($_SESSION["ClientConfigData"]);

$AryConfigData = (array) $_SESSION["ClientConfigData"]; //by uma(28-02-2024)
// print_r($AryConfigData);
$ConsolidateGrossweightPopup = $AryConfigData["ConsolidateGrossweightPopup"]; //by uma(28-02-2024)
$EnableCustomInvoicePoData = $AryConfigData['enableCustomInvoicePoData'];
$SamePiNumber = $AryConfigData["SamePiNumber"];
//$ConsolidateGrossweightPopup =$_SESSION["ClientConfigData"]["ConsolidateGrossweightPopup"];//by uma(28-02-2024)



$AryPriceWeightData = getInvoiceWeight($iconn, $GradeID, $PIId);
$AryPriceData = getPaymentValues($iconn, $PIId, '', $AryConfigData);

$NetWeight = $AryPriceWeightData['Qty'];
//$GrossWeight = $AryPriceWeightData['GrossWeightKgs'];
$GrossWeight = $AryPriceWeightData['GrossQty']; //uma
$DutiesPrice = $AryPriceData['DutiesPrice'];
$InvValueUSD = $AryPriceData['InvValueUSD'];




$SqlCommercial = "SELECT count(*) as Total FROM ScrmCommercialData where PoID='$PIId'";
$StmtCommercial = $iconn->query($SqlCommercial);
$RowCommercial = $StmtCommercial->fetch_array();
$CommTotal = $RowCommercial['Total'];

if ($CommTotal > 0) {
    $buttonState = 'disabled';
    $Showbutton = false;
} else {
    $buttonState = '';
    $Showbutton = true;
}



$SqlBank = "SELECT * FROM AdminBankDetails";
$StmtBank = $iconn->query($SqlBank);

$SqlLiner = "SELECT * FROM `ScrmLinerMaster` WHERE Status=1";
$StmtLiner = $iconn->query($SqlLiner);

$SqlExchangeRate = "SELECT ExchangeRate FROM `ScrmExchangeRates` as SER
left join ScrmPoData as SPD on SPD.UnitCurrency=SER.CurrencyType WHERE PoID='$PIId ' ORDER by FromDate DESC LIMIT 1";
$StmtExchangeRate = $iconn->query($SqlExchangeRate);
$RowExchangeRate = $StmtExchangeRate->fetch_array();
$ExchangeRate = $RowExchangeRate['ExchangeRate'];
//script


//$SqlPackingStyle = "SELECT DISTINCT(PackingStyleID) as PackingStyleID FROM `ScrmPoGradeData` WHERE InvId='$PIId'";
// $SqlPackingStyle = "SELECT ID as PoGradeID FROM `ScrmPoGradeData` WHERE InvId='$PIId'"; // uma 28-02-2024
/**uma grossweight popup 28-02-2024*/
if ($ConsolidateGrossweightPopup == 0) {
    $SqlPackingStyle = "SELECT ID as PoGradeID FROM `ScrmPoGradeData` WHERE InvId='$PIId'";
} else {
    $SqlPackingStyle = "SELECT DISTINCT(PackingStyleID) as PoGradeID FROM `ScrmPoGradeData` WHERE InvId='$PIId'";
}
/**uma grossweight popup 28-02-2024*/
$StmtPackingStyle = $iconn->query($SqlPackingStyle);


$AryPackingStyleIds = array();
while ($RowPackingStyle = $StmtPackingStyle->fetch_array()) {
    $AryPackingStyleIds[] = $RowPackingStyle['PoGradeID'];
}

$AryPoGradeID = array();

//script


$SqlPI = "SELECT ExporterID,ProcessorID,Freight,QtyUnit,ChargeAmount FROM `ScrmPoData`  WHERE PoID='$PIId'";
$StmtPI = $iconn->query($SqlPI);
$RowPI = $StmtPI->fetch_array();

$ExpID = $RowPI['ExporterID'];
$ProcessorID = $RowPI['ProcessorID'];
$PIFreight = $RowPI['Freight'];
$QtyUnit = $RowPI['QtyUnit'];
$ChargeAmount = $RowPI['ChargeAmount'];

$SqlCIAutoGeneration = "SELECT CIAutoGeneration FROM `AdminConfigDetails`";
$StmtCIAutoGeneration = $iconn->query($SqlCIAutoGeneration);
$RowCIAutoGeneration = $StmtCIAutoGeneration->fetch_array();
$ISCIAutoGeneration = $RowCIAutoGeneration['CIAutoGeneration'];


if ($ISCIAutoGeneration) {
    // ECHO $CInvDate;

    if ($CInvDate == '') {
        $CInvDate = date('Y-m-d', time());
    }

    $Query = ' AND CInvDate ' . getCurrentData($CInvDate);
    $SqlPiDetails = "SELECT max(CInvNo) as InvNO from ScrmCustomInvoices as SCI Left join ScrmPoData as SPD on SCI.PiID=SPD.PoID
    where SPD.ExporterID='$ExpID' and SPD.ProcessorID='$ProcessorID' $Query";
    $StmtPiDetails = $iconn->query($SqlPiDetails);
    $RowPiDetails = $StmtPiDetails->fetch_array();
    $AutoInvNo = $RowPiDetails['InvNO'] + 1;

    $SqlCancelDetails = "SELECT count(*) as Count from ScrmCancelledCustomInvoices as SCI
    Left join ScrmPoData as SPD on SCI.PiID=SPD.PoID where SPD.ExporterID='$ExpID' and SPD.ProcessorID='$ProcessorID' $Query and CInvNo='$AutoInvNo'";
    $StmtCancelDetails = $iconn->query($SqlCancelDetails);
    $RowCancelDetails = $StmtCancelDetails->fetch_array();
    $Count = $RowCancelDetails['Count'];
    if ($Count != 0) {
        $AutoInvNo = $AutoInvNo + 1;
    }
}

function getCurrentData($CInvDate)
{
    $Currentyear = date('Y', strtotime($CInvDate));
    $CurrentMonth = date('m', strtotime($CInvDate));

    if ($CurrentMonth > 3) {
        $DateStart = $Currentyear . '-04-01';
        $DateEnd = ($Currentyear + 1) . '-03-31';
    } else {
        $DateStart = ($Currentyear - 1) . '-04-01';
        $DateEnd = ($Currentyear) . '-03-31';
    }

    $DateFilter = "BETWEEN '$DateStart' and '$DateEnd'";

    return $DateFilter;
}
// if ($ISCIAutoGeneration) {
//    $SqlPiDetails = "SELECT max(CInvNo) as InvNO from ScrmCustomInvoices as SCI Left join ScrmPoData as SPD on SCI.PiID=SPD.PoID
// where SPD.ExporterID='$ExpID' and SPD.ProcessorID='$ProcessorID'";
//  $StmtPiDetails = $iconn->query($SqlPiDetails);
//  $RowPiDetails = $StmtPiDetails->fetch_array();
//  $AutoInvNo = $RowPiDetails['InvNO'] + 1;
// }
$CalculatedValues = getPaymentValues($iconn, $PIId);
$SqlCount = "SELECT * FROM `ScrmCustomInvoices` WHERE PiID='$PIId'";

$StmtCount = $iconn->query($SqlCount);

//echo $SqlCount;
$RowCustomInvoice = $StmtCount->fetch_array();
$InvNo = $RowCustomInvoice['CInvNo'];
$CInvDate = $RowCustomInvoice['CInvDate'];
$Discount = $RowCustomInvoice['Discount'];

$cshideDeleteAccess = false;
if( $CInvDate <= '2026-04-01' ){

}

if ($Discount == '') {
    $Discount = $ChargeAmount;
}

if ($InvNo == '') {



    $InvNo = $AutoInvNo;

    date_default_timezone_set('Asia/Kolkata');
    $CInvDate = date('Y-m-d', time());
    //$CustomsInvNo = getInvoiceFullNumbertosave($ExpID, $ProcessorID, $CInvDate, $InvNo);
    $CustomsInvNo = getInvoiceNumber($iconn, $ExpID, $ProcessorID, $CInvDate, $InvNo, 'CI');
} else {
    $InvNo;
    $CustomsInvNo = getInvoiceNumber($iconn, $ExpID, $ProcessorID, $CInvDate, $InvNo, 'CI');
}

$ExRate = $RowCustomInvoice['ExRate'];
$CFreight = $RowCustomInvoice['Freight'];


$TotalGrossWt = $RowCustomInvoice['TotalGrossWt'];

$SqlGrosweight = "SELECT sum(GrossWt) as GrossWt ,sum(PIGrossWt) as PIGrossWt FROM `ScrmPoGradeData` WHERE InvId='$PIId'";
$StmtGrosweight = $iconn->query($SqlGrosweight);
$RowGrosweight = $StmtGrosweight->FETCH_ARRAY();


if ($TotalGrossWt != '') {
    // $GrossWeight = round($TotalGrossWt,2);
    $GrossWeight = $TotalGrossWt;
} else if ($RowGrosweight['GrossWt'] > 0) {
    // $GrossWeight = round($RowGrosweight['GrossWt'],2);
    $GrossWeight = $RowGrosweight['GrossWt'];
} elseif ($RowGrosweight['PIGrossWt'] > 0) {
    // $GrossWeight = round($RowGrosweight['PIGrossWt'],2);
    $GrossWeight = $RowGrosweight['PIGrossWt'];
} else {
    // $GrossWeight = round($AryPriceWeightData['GrossQty'],2);//uma
    $GrossWeight = $AryPriceWeightData['GrossQty']; //uma
}

if ($CFreight != '') {
    $Freight = $RowCustomInvoice['Freight'];
} else {
    $Freight = $PIFreight;
}

if ($ExRate == '') {
    $ExchangeRate = $RowExchangeRate['ExchangeRate'];
} else {
    $ExchangeRate = $ExRate;
}

$Count = mysqli_num_rows($StmtCount);


$SqlGradeData = "SELECT * FROM `ScrmCIGradeData` where InvId='$PIId'  ORDER BY ID DESC";
$StmtGradeData = $iconn->query($SqlGradeData);
$CIGradeCount = mysqli_num_rows($StmtGradeData);

$SqlPIVarietyList = "SELECT * FROM `ScrmPIVarietyList`";
$StmtPIVarietyList = $iconn->query($SqlPIVarietyList);

$SqlUserDetails = "SELECT * FROM `AdminUserDetails` WHERE CustomUserID='1' and DepartmentID=9";
$StmtUserDetails = $iconn->query($SqlUserDetails);



extract($_POST);
extract($_GET);




if (isset($_POST['submit'])) {

    $InvNo = $CInvNo;
    $CustomsInvNo = getInvoiceNumber($iconn, $ExpID, $ProcessorID, $CInvDate, $InvNo, 'CI');

    // if(!empty($RowCustomInvoice['CInvDate'])){
    // }else {
    //     echo '<script>alert("Update Failed. Invoice Date Already Exists")</script>';
    // }

    if ($SamePiNumber == 1) {
        $SqlInvNumFull = "SELECT InvNumFull FROM `ScrmPoData` WHERE PoID= '$PIId'";
        $StmtInvNumFull = $iconn->query($SqlInvNumFull);
        $RowInvNumFull = $StmtInvNumFull->FETCH_ARRAY();
        $CustomsInvNo = $RowInvNumFull['InvNumFull'];
    }

    if ($Count == 0) {


        $SqlCIInsert = "INSERT INTO `ScrmCustomInvoices`
        (`PiID`, `CInvNo`,`CustomsInvNo`, `CInvDate`,`CInvCode`, `IGST`, `Discount`,
        `BankID`, `NoOfDays`,`ExporterID`,`AdvanceAmount`,
        `OverSeasCommission`,`Description`, `CIDescription`,
        `NameofRepresentative`, `PackDescription`,
        `QCertificateNo`, `ContainerNo`, `ProductTypes`, `USClearance`,
        `PaymentType`, `ByRoad`, `IRNNumber`, `ClearenceCharges`,
        `TotalNetWt`, `TotalGrossWt`, `Duty`, `Freight`, `ExRate`,
         `StockOutDate`, `Vessel`, `VoyageNo`, `Remarks`, `CIDescriptionspl`,
         `ShippingLineID`, `CodeType`, `Note`, `ByRoadDate`, `LinerSealNo`, `CustomSealNo`, `DriverName`, `VehicelNumber`, `ContainerWeight`)
        VALUES ('$PIId','$CInvNo','$CustomsInvNo','$CInvDate','$CInvCode','$IGST','$Discount',
        '$BankID','$NoOfDays','$ExpID','$AdvanceAmount','$OverSeasCommission',
        '$Description','$CIDescription','$NameofRepresentative',
        '$PackDescription','$QCertificateNo','$ContainerNo',
        '$ProductTypes','$USClearance','$PaymentType',
        '$ByRoad','$IRNNumber','$ClearenceCharges','$TotalNetWt','$TotalGrossWt'
        ,'$Duty','$Freight','$ExRate','$StockOutDate','$Vessel'
        ,'$VoyageNo','$Remarks','$CIDescriptionspl','$ShippingLineID','$CodeType',
        '$Note','$ByRoadDate','$LinerSealNo','$CustomSealNo','$DriverName','$VehicelNumber','$ContainerWeight')";

        $StmtInsert = $iconn->query($SqlCIInsert);
    } else {
        $oldCustInvData = legacyAuditFetchRowById('ScrmCustomInvoices', 'PiID', $PIId, 'Sales');

        $SqlCIInsert = "UPDATE `ScrmCustomInvoices` SET
        `CInvNo`='$CInvNo',
        `CustomsInvNo`='$CustomsInvNo',
        `CInvDate`='$CInvDate',
        `CInvCode`='$CInvCode',
        `IGST`='$IGST',
        `Discount`='$Discount',
         `BankID`='$BankID',
         `NoOfDays`='$NoOfDays',
         `AdvanceAmount`='$AdvanceAmount',
         `OverSeasCommission`='$OverSeasCommission',
         `Description`='$Description',
         `QCertificateNo`='$QCertificateNo',
         `ContainerNo`='$ContainerNo',
         `ProductTypes`='$ProductTypes',
         `USClearance`='$USClearance',
         `PaymentType`='$PaymentType',
         `ByRoad`='$ByRoad',
         `IRNNumber`='$IRNNumber',
         `TotalNetWt`='$TotalNetWt',
         `TotalGrossWt`='$TotalGrossWt',
         `Duty`='$Duty',
         `Vessel`='$Vessel',
         `VoyageNo`='$VoyageNo',
         `ExRate`='$ExRate',
         `Remarks`='$Remarks',
         `StockOutDate`='$StockOutDate',
         `Freight`='$Freight',
         `CIDescriptionspl`='$CIDescriptionspl',
         `ShippingLineID`='$ShippingLineID',
         `ClearenceCharges`='$ClearenceCharges',
         `CodeType`='$CodeType',
         `ByRoadDate`='$ByRoadDate',
         `Note`='$Note',
         `LinerSealNo`='$LinerSealNo',
         `CustomSealNo`='$CustomSealNo',
         `DriverName`='$DriverName',
         `VehicelNumber`='$VehicelNumber',
         `ContainerWeight`='$ContainerWeight'
        WHERE `PiID`='$PIId'";


        $StmtInsert = $iconn->query($SqlCIInsert);
    }

    //echo $SqlCIInsert; exit;

    if ($StmtInsert) {
        if ($Count == 0) {
            legacyAuditLogInsertById('ScrmCustomInvoices', 'PiID', $PIId, $_POST, [
                'CInvNo' => $CInvNo,
                'ReferenceNo' => $PIId,
                'Remarks' => 'Custom invoice created',
            ], 'Sales');
        } else {
            legacyAuditLogUpdateById('ScrmCustomInvoices', 'PiID', $PIId, $oldCustInvData, $_POST, [
                'CInvNo' => $CInvNo,
                'ReferenceNo' => $PIId,
                'Remarks' => 'Custom invoice updated',
            ], 'Sales');
        }

        if ($EnableCustomInvoicePoData == 1 && empty($CInvDate)) {

            $sqlFetch = "SELECT * FROM `ScrmPoGradeData` WHERE InvId='$PIId'";
            $result = $iconn->query($sqlFetch);
            // var_dump( $result->num_rows); exit;
            if ($result && $result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    // Assign each column from the row
                    $PoGradeId     = $row['ID'];
                    $Grade         = $row['Grade'];
                    $Mcs           = $row['Mcs'];
                    $FreezingType  = $row['FreezingType'];
                    $PackingStyleID = $row['PackingStyleID'];
                    $Glaze         = $row['GlazeType'];
                    $GlazePerc     = $row['GlazePerc'];
                    $QcDescription = $row['QcDescription'];
                    $VarietyName   = $row['Variety'];
                    $VarietyId     = $row['VarietyID'];
                    $Treatment     = $row['Treatment'];
                    $Price         = $row['Price'];
                    $Brand         = $row['Brand'];
                    $DeviSupc      = $row['DeviSupc'];
                    $BuyerItemCode = $row['BuyerItemCode'];
                    $Description   = $row['Description'];
                    $InvId         = $row['InvId'];
                    $BAP           = $row['BAP'];

                $SqlPoGradeData2 = "
                INSERT INTO `ScrmPIGradeData` (
                    `ID`, `Grade`, `Mcs`, `FreezingType`, `PackingStyleID`, `GlazeType`, `GlazePerc`,
                    `QcDescription`, `Variety`, `VarietyID`, `Treatment`, `Price`, `Brand`,
                    `DeviSupc`, `BuyerItemCode`, `Description`, `InvId`, `BAP`
                ) VALUES (
                    '$PoGradeId', '$Grade', '$Mcs', '$FreezingType', '$PackingStyleID', '$Glaze', '$GlazePerc',
                    '$QcDescription', '$VarietyName', '$VarietyId', '$Treatment', '$Price', '$Brand',
                    '$DeviSupc', '$BuyerItemCode', '$Description', '$InvId', '$BAP'
                ) ON DUPLICATE KEY UPDATE
                    `Grade`=VALUES(`Grade`),
                    `Mcs`=VALUES(`Mcs`),
                    `FreezingType`=VALUES(`FreezingType`),
                    `PackingStyleID`=VALUES(`PackingStyleID`),
                    `GlazeType`=VALUES(`GlazeType`),
                    `GlazePerc`=VALUES(`GlazePerc`),
                    `QcDescription`=VALUES(`QcDescription`),
                    `Variety`=VALUES(`Variety`),
                    `VarietyID`=VALUES(`VarietyID`),
                    `Treatment`=VALUES(`Treatment`),
                    `Price`=VALUES(`Price`),
                    `Brand`=VALUES(`Brand`),
                    `DeviSupc`=VALUES(`DeviSupc`),
                    `BuyerItemCode`=VALUES(`BuyerItemCode`),
                    `Description`=VALUES(`Description`),
                    `InvId`=VALUES(`InvId`),
                    `BAP`=VALUES(`BAP`)
                    ";
                    $StmntSqlPoGradeData = $iconn->query($SqlPoGradeData2);
                    // var_dump($StmntSqlPoGradeData); exit;
                    if (!$StmntSqlPoGradeData) {
                        echo "Insert error: " . $iconn->error;
                    }
                }
            }
        }
        postUpdateShipmentBreif($iconn, $CInvDate, $CInvNo, $PIId, $StockOutDate);
        postContainerNo($PIId, $ContainerNo, $LinerSealNo, $DocType);
        postUpdateCharges($iconn, $Discount, $PIId);
        $ArgumentsArray[PostedUserId] = $PostedUserId;
        $ArgumentsArray[PageUrl] = $PageUrl;
        $ArgumentsArray[Remarks] = $Remarks;
        $ArgumentsArray[ReferenceID] = $PIId;
        postUserEditLog($ArgumentsArray);
        callBackUrl();
    } else {
        echo '<script>alert("Update Failed")</script>';
    }
}

$isCommercialInvoiceLocked = isOnlyCompletedCommercialInvoiceStatus($iconn, $PIId);
?>


<!DOCTYPE html>
<html lang="en">

<head>

    <?php include($IncludesPath . "title-meta.php"); ?>
    <?php include($IncludesPath . "DbLinks.php"); ?>
    <?php include($IncludesPath . "head-css.php"); ?>

</head>

<body data-sidebar="dark">


    <?php
    include($IncludesPath . "PreLoader.php"); ?>

    <!-- Begin page -->
    <div id="layout-wrapper">


        <?php
        include($IncludesPath . "topbar.php");
        include($IncludesPath . "sidebar.php");
        ?>



        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">

            <div class="page-content">
                <div class="container-fluid">

                    <?php
                    if ($Status != '') {
                        //Show Alert
                    ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="mdi mdi-check-all mr-2"></i>
                            <?php echo $Status;
                            $_SESSION['Status'] = ''; ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                    <?php } ?>

                    <?php include($IncludesPath . "page-title.php");
                    include "PopUpTable.php";
                    include $cp . "Includes/ProformaView.php";
                    ?>



                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-12">
                                <div class="card">

                                    <form role="form" method="post">
                                        <div class="card-body">

                                            <div class="row">
                                                <?php if ($ISCIAutoGeneration) { ?>
                                                    <div class="form-group col-sm-3">
                                                        <label for="exampleInputEmail1">Custom Invoice<span
                                                                style="color:red">*</span></label>
                                                        <input hidden required type="text" class="form-control"
                                                            name='CInvNo' id="CInvNo" placeholder="Enter Custom Invoice No"
                                                            value="<?php echo $InvNo ?>">

                                                        <input readonly required type="text" class="form-control"
                                                            name='CInvNoFull' id="CInvNoFull"
                                                            placeholder="Enter Custom Invoice No"
                                                            value="<?php echo $CustomsInvNo ?>">

                                                    </div>
                                                <?php } else { ?>

                                                    <div class="form-group col-sm-3">
                                                        <label for="exampleInputEmail1">Custom Invoice<span
                                                                style="color:red">*</span></label>
                                                        <input required type="text" class="form-control" name='CInvNo'
                                                            id="CInvNo" placeholder="Enter Custom Invoice No"
                                                            value="<?php echo $InvNo ?>">



                                                    </div>
                                                <?php } ?>

                                                <div class="form-group col-sm-3">
                                                    <label for="exampleInputEmail1">Custom Invoice Date<span
                                                            style="color:red">*</span></label>
                                                    <input required type="date" onkeydown="return false"
                                                        class="form-control" name='CInvDate' id="CInvDate"
                                                        placeholder=" "
                                                        value="<?php echo !empty($RowCustomInvoice['CInvDate']) ? $RowCustomInvoice['CInvDate'] : date('Y-m-d'); ?>">
                                                </div>







                                                <div class="form-group col-sm-3">
                                                    <label for="exampleInputEmail1">Invoice Type (IGST/LUT)<span
                                                            style="color:red">*</span></label>
                                                    <select required class="form-control select2" id="IGST" name="IGST">
                                                        <option value="">Select</option>
                                                        <option <?php if ($RowCustomInvoice['IGST'] == 'IGST') { ?>selected="selected" <?php } ?> value="IGST">IGST</option>
                                                        <option <?php if ($RowCustomInvoice['IGST'] == 'LUT') { ?>selected="selected" <?php } ?> value="LUT">LUT</option>



                                                    </select>
                                                </div>

                                                <div class="form-group col-sm-3">
                                                    <label for="exampleInputEmail1">Product Type</label>
                                                    <select class="form-control select2" id="ProductTypes"
                                                        name="ProductTypes">
                                                        <option value="">Select</option>
                                                        <option <?php if ($RowCustomInvoice['ProductTypes'] == 'PTAAPTA') { ?>selected="selected" <?php } ?> value="PTAAPTA">PTAAPTA
                                                        </option>
                                                        <option <?php if ($RowCustomInvoice['ProductTypes'] == 'NO PTA/FTA') { ?>selected="selected" <?php } ?>
                                                            value="NO PTA/FTA">NO PTA/FTA</option>
                                                    </select>
                                                </div>

                                                <div class="form-group col-sm-3">
                                                    <label for="exampleInputEmail1">Code Type</label>
                                                    <select class="form-control select2" id="CodeType" name="CodeType">
                                                        <option value="">Select</option>
                                                        <option <?php if ($RowCustomInvoice['CodeType'] == 'ITEM CODE') { ?>selected="selected" <?php } ?> value="ITEM CODE">ITEM
                                                            CODE</option>
                                                        <option <?php if ($RowCustomInvoice['CodeType'] == 'EPCG LICENSE NUMBER') { ?>selected="selected" <?php } ?>
                                                            value="EPCG LICENSE NUMBER">EPCG LICENSE NUMBER
                                                        </option>
                                                        <option <?php if ($RowCustomInvoice['CodeType'] == 'PO-NO') { ?>selected="selected" <?php } ?> value="PO-NO">PO-NO
                                                        </option>
                                                        <option <?php if ($RowCustomInvoice['CodeType'] == 'PCS/BAGS') { ?>selected="selected" <?php } ?> value="PCS/BAGS">PCS/BAGS
                                                        </option>
                                                        <option <?php if ($RowCustomInvoice['CodeType'] == 'LOT CODE') { ?>selected="selected" <?php } ?> value="LOT CODE">LOT CODE
                                                        </option>
                                                    </select>
                                                </div>

                                                <div class="form-group col-sm-3">
                                                    <label for="exampleInputEmail1">Payment Type<span
                                                            style="color:red">*</span></label>
                                                    <select required class="form-control select2" id="PaymentType"
                                                        name="PaymentType">
                                                        <option value="">Select</option>
                                                        <option <?php if ($RowCustomInvoice['PaymentType'] == 'Collections') { ?>selected="selected" <?php } ?> value="Collections">
                                                            Collections</option>
                                                        <option <?php if ($RowCustomInvoice['PaymentType'] == 'Negotiations') { ?>selected="selected" <?php } ?> value="Negotiations">
                                                            Negotiations</option>
                                                    </select>
                                                </div>

                                                <div class="form-group col-sm-3">
                                                    <label for="exampleInputEmail1">Q Certificate No</label>
                                                    <input type="text" class="form-control" name='QCertificateNo'
                                                        id="QCertificateNo" placeholder="Enter Q Certificate No"
                                                        value="<?php echo $RowCustomInvoice['QCertificateNo'] ?>">

                                                </div>




                                                <!--<div class="form-group col-lg-3">
                  <label for="exampleInputPassword1">Bank Details</label>
                   <select  class="form-control select2" name="BankID" id="BankID">
                                   <option value="">Select Bank</option>

                   <?php if ($RowCustomInvoice['BankID'] == 0) {
                        $BankID = $RowPIData['BankID'];
                    } else {
                        $BankID = $RowCustomInvoice['BankID'];
                    }
                    while ($RowBank = $StmtBank->fetch_array()) { ?>
                     <option <?php if ($BankID == $RowBank['ID']) { ?> selected="selected"  <?php } ?>


                     value="<?php echo $RowBank['ID'] ?>">
                     <?php echo $RowBank['AccntName'] . ' -
                     ' . $RowBank['BankName'] . '-' . $RowBank['AccountNumber'] ?></option>

                 <?php } ?>
                  </select>
                </div>-->

                                                <div class="form-group col-lg-3">
                                                    <label for="exampleInputEmail1">Discount</label>
                                                    <input type="decimal" class="form-control" name='Discount'
                                                        id="Discount" placeholder="Enter Discount" maxlength="6"
                                                        value="<?php echo $Discount ?>">

                                                </div>


                                                <!--<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Terms No.of Days</label>
                <input  type="number" class="form-control" name='NoOfDays' id="NoOfDays"
                  placeholder="Enter No.of Days" value="<?php echo $RowCustomInvoice['NoOfDays'] ?>">

                </div>-->

                                                <div class="form-group col-lg-3">
                                                    <label for="exampleInputEmail1">Advance Amount</label>
                                                    <input type="text" class="form-control" name='AdvanceAmount'
                                                        id="AdvanceAmount" placeholder="Enter Advance Amount"
                                                        value="<?php echo $RowCustomInvoice['AdvanceAmount'] ?>">

                                                </div>
                                                <div class="form-group col-lg-3">
                                                    <label for="exampleInputEmail1">OverSeas
                                                        Commission(Cents/KG)</label>
                                                    <input type="text" class="form-control" name='OverSeasCommission'
                                                        id="OverSeasCommission" placeholder="Enter OverSeas Commission"
                                                        value="<?php echo $RowCustomInvoice['OverSeasCommission'] ?>">

                                                </div>

                                                <!-- <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">US Clearance Charges</label>
                <input  type="text" class="form-control" name='ClearenceCharges' id="ClearenceCharges"
                  placeholder="Enter US Clearance Charges" value="<?php echo $RowCustomInvoice['ClearenceCharges'] ?>">

                </div>-->

                                                <div class="form-group col-lg-3">
                                                    <label for="exampleInputEmail1">StockOut Date</label>
                                                    <input type="Date" onkeydown="return false" class="form-control"
                                                        name='StockOutDate' id="StockOutDate"
                                                        placeholder="Enter Transport Date"
                                                        value="<?php echo $RowCustomInvoice['StockOutDate'] ?>">

                                                </div>

                                                <div class="form-group col-lg-3">
                                                    <label for="exampleInputPassword1">Shipping Line</label>
                                                    <select class="form-control select2" name="ShippingLineID"
                                                        id="ShippingLineID">
                                                        <option value="">Select</option>

                                                        <?php while ($RowLiner = $StmtLiner->fetch_array()) { ?>
                                                            <option <?php if ($RowCustomInvoice['ShippingLineID'] == $RowLiner['LinerID']) { ?> selected="selected" <?php } ?>
                                                                value="<?php echo $RowLiner['LinerID'] ?>">
                                                                <?php echo $RowLiner['LinerName'] ?>
                                                            </option>

                                                        <?php } ?>
                                                    </select>
                                                </div>

                                                <div class="form-group col-lg-3">
                                                    <label for="exampleInputEmail1">Container No</label>
                                                    <input type="text" class="form-control" name='ContainerNo'
                                                        id="ContainerNo" placeholder="Enter Container No"
                                                        value="<?php echo $RowCustomInvoice['ContainerNo'] ?>">
                                                </div>


                                                <div class="form-group col-sm-3">
                                                    <label for="exampleInputEmail1">Liner Seal Number</label>
                                                    <input type="text" class="form-control" name='LinerSealNo'
                                                        id="LinerSealNo" placeholder="Enter Linear Seal Number"
                                                        value="<?php echo $RowCustomInvoice['LinerSealNo']; ?>">

                                                </div>

                                                <div class="form-group col-lg-3">
                                                    <label for="exampleInputEmail1">Custom Seal No</label>
                                                    <input type="text" class="form-control" name='CustomSealNo'
                                                        id="CustomSealNo" placeholder="Enter  Custom Seal No"
                                                        value="<?php echo $RowCustomInvoice['CustomSealNo'] ?>">
                                                </div>

                                                <div class="form-group col-lg-3">
                                                    <label for="exampleInputEmail1">Driver Name </label>
                                                    <input type="text" class="form-control" name='DriverName'
                                                        id="DriverName" placeholder="Enter  Driver Name "
                                                        value="<?php echo $RowCustomInvoice['DriverName'] ?>">
                                                </div>

                                                <div class="form-group col-lg-3">
                                                    <label for="exampleInputEmail1">Vehicle Number</label>
                                                    <input type="text" class="form-control" name='VehicelNumber'
                                                        id="VehicelNumber" placeholder="Enter  Vehicle Number"
                                                        value="<?php echo $RowCustomInvoice['VehicelNumber'] ?>">
                                                </div>



                                                <!--<div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Vessel</label>
                <input  type="text" class="form-control" name='Vessel' id="Vessel"
                  placeholder="Enter Vessel" value="<?php echo $RowCustomInvoice['Vessel'] ?>">

                </div>
                <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">Voyage No</label>
                <input  type="text" class="form-control" name='VoyageNo' id="VoyageNo"
                  placeholder="Enter Voyage No" value="<?php echo $RowCustomInvoice['VoyageNo'] ?>">

                </div>-->

                                                <div class="form-group col-lg-3">
                                                    <label for="exampleInputEmail1">By Road</label>
                                                    <input type="text" class="form-control" name='ByRoad' id="ByRoad"
                                                        placeholder="Enter By Road Details"
                                                        value="<?php echo $RowCustomInvoice['ByRoad'] ?>">

                                                </div>
                                                <div class="form-group col-lg-3">
                                                    <label for="exampleInputEmail1">Container Weight</label>
                                                    <input type="text" class="form-control" name='ContainerWeight'
                                                        id="ContainerWeight" placeholder="Enter Tyre Weight"
                                                        value="<?php echo $RowCustomInvoice['ContainerWeight'] ?>">
                                                </div>

                                                <!-- <div class="form-group col-lg-3" >
                  <label for="exampleInputEmail1">By Road Date</label>
                <input  type="Date" onkeydown="return false" class="form-control" name='ByRoadDate' id="ByRoadDate"
                  placeholder="Enter Transport Date" value="<?php echo $RowCustomInvoice['ByRoadDate'] ?>">

                </div>
                -->

                                            </div>
                                            <HR>
                                            </HR>

                                            <div class="row">

                                                <div class="form-group col-sm-3">
                                                    <label for="exampleInputEmail1">Invoice Value</label>
                                                    <input readonly type="text" class="form-control" name='InvValueUSD'
                                                        id="InvValueUSD" style="background: gray;color: white;"
                                                        placeholder="Enter Total Invoice Value"
                                                        value="<?php echo number_format($InvValueUSD, 2) ?>">

                                                </div>

                                                <div class="form-group col-sm-3">
                                                    <label for="exampleInputEmail1">Total NetWt</label>
                                                    <input readonly type="text" class="form-control" name='TotalNetWt'
                                                        id="TotalNetWt" style="background: gray;color: white;"
                                                        placeholder="Enter Total Net Weight"
                                                        value="<?php echo $NetWeight ?>">

                                                </div>
                                                <div class="form-group col-sm-3">
                                                    <label for="exampleInputEmail1">Total GrossWt(
                                                        <?php echo $QtyUnit; ?>)<span style="color:red">*</span>
                                                    </label>
                                                    <button id="AddGrossWtPerBrkUp" data-id="<?php echo $PIId ?>"
                                                        data-value="
               <?php echo $PIId ?>" class="text">
                                                        <b> Add + </b></button>


                                                    <input readonly type="text" style="background: gray;color: white;"
                                                        class="form-control" name='TotalGrossWt' id="TotalGrossWt"
                                                        placeholder="Enter Total Gross Weight"
                                                        value="<?php echo $GrossWeight ?>">

                                                </div>
                                                <div class="form-group col-sm-3">
                                                    <label for="exampleInputEmail1">Duty</label>
                                                    <input readonly type="text" class="form-control" name='Duty'
                                                        id="Duty" style="background: gray;color: white;"
                                                        placeholder="Enter Duty" value="<?php echo $DutiesPrice ?>">

                                                </div>
                                                <div class="form-group col-sm-3">
                                                    <label for="exampleInputEmail1">Freight<span
                                                            style="color:red">*</span></label>
                                                    <input required type="text" class="form-control Freight"
                                                        name='Freight' id="Freight" placeholder="Enter Freight"
                                                        value="<?php echo $Freight; ?>">

                                                </div>
                                                <div class="form-group col-sm-3">
                                                    <label for="exampleInputEmail1">ExRate<span
                                                            style="color:red">*</span></label>
                                                    <input required type="text" class="form-control" name='ExRate'
                                                        id="ExRate" placeholder="Enter Exchange Rate"
                                                        value="<?php echo $ExchangeRate; ?>">

                                                </div>



                                                <div class="form-group col-lg-12">
                                                    <label for="exampleInputEmail1">IRN Number</label>

                                                    <textarea name="IRNNumber" class="form-control" rows="2" cols="50"
                                                        id="IRNNumber"
                                                        placeholder="Enter IRN Number"><?php echo $RowCustomInvoice['IRNNumber'] ?></textarea>


                                                </div>

                                                <div class="form-group col-lg-12">
                                                    <label for="exampleInputEmail1">Remarks</label>

                                                    <textarea name="Remarks" class="form-control" rows="2" cols="50"
                                                        id="Remarks"
                                                        placeholder="Enter Remarks"><?php echo $RowCustomInvoice['Remarks'] ?></textarea>


                                                </div>


                                                <div class="form-group col-lg-12">
                                                    <label for="exampleInputEmail1">Note(Display Only)</label>

                                                    <textarea name="Note" class="form-control" rows="2" cols="50"
                                                        id="Note"
                                                        placeholder="Enter Note"><?php echo $RowCustomInvoice['Note'] ?></textarea>


                                                </div>

                                            </div>




                                            <?php if ($Count == 0) { ?>
                                                <div class="col-xs-12 box-footer" align="right">
                                                    <button type="button" id="close" name="close"
                                                        class="btn btn-danger">Close </button>


                                                    <button hidden type="submit" name='submit' id="Create"
                                                        class="btn btn-primary">Add</button>
                                                    <button type="button" id="CreateInv"
                                                        class="btn btn-primary">Add</button>
                                                </div>
                                            <?php } else { ?>

                                                <div class="col-xs-12 box-footer" align="right">
                                                    <button type="button" id="close" name="close"
                                                        class="btn btn-danger">Close </button>

                                                    <?php if(!$isCommercialInvoiceLocked){ ?>
                                                    <button hidden type="submit" name='submit' id="Create"
                                                        class="btn btn-primary">Update</button>
                                                    <button type="button" id="CreateInv"
                                                        class="btn btn-primary">Update</button>

                                                    <?php } if ($Showbutton) { ?>
                                                        <button id="<?php echo $PIId; ?>" style="width:100px;"
                                                            onClick="reply_click(this.id)" class="btn btn-warning ">
                                                            Delete</button>

                                                    <?php } ?>


                                                </div>
                                            <?php } ?>

                                    </form>
                                    <!-- end col -->
                                </div> <!-- end row -->

                            </div> <!-- end card body-->
                        </div> <!-- end card -->


                    </div>


                </div><!-- end col-->
            </div>



            <?php include($IncludesPath . "footer.php"); ?>

        </div>
        <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->



    <?php include($IncludesPath . "vendor-scripts.php"); ?>
    <?php include($IncludesPath . "NewScripts.php"); ?>



</body>

</html>


<script>
    $('#close').click(function(e) {
        window.close();
        window.location.href = 'custom-invoices';
    });


    function reply_click(clicked_id) {
        var r = confirm("Are You Sure Do You Want To Delete " + clicked_id + "?");
        if (r == true) {
            $.ajax({
                url: 'Ajax/AjaxDelete.php',
                type: 'POST',
                data: {
                    'TYPE': 'CustomDelete',
                    'PiID': clicked_id
                },
                success: function(data) {
                    // alert(data);
                    alert("Deleted Successfully");
                }
            });
        }
    }
</script>

<script>
    function showData(data) {

        //$("#PIList").html(data);

        var Stable = $('#scroll-vertical-datatable').DataTable({
            "scrollY": "450px",
            "scrollCollapse": true,
            "paging": false,
            "language": {
                "paginate": {
                    "previous": "<i class='mdi mdi-chevron-left'>",
                    "next": "<i class='mdi mdi-chevron-right'>"
                }
            },
            "drawCallback": function() {
                $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
            },
            buttons: ['copy', 'excel', 'pdf', 'colvis']
        });

        Stable.buttons().container()
            .appendTo('#scroll-vertical-datatable_wrapper .col-md-6:eq(0)');
    }
</script>



<script>
    $(document).on('click', '#AddGrossWtPerBrkUp', function(e) {
        e.preventDefault();

        var PoNo = $(this).data('value');
        var PoID = $(this).data('id');

        $('#Update').val(PoID);
        showPoPopUp(PoNo, PoID);

    });

    function showPoPopUp(PoNo, PoID) {

        $.ajax({
            url: 'Ajax/AjaxGrossWtPopUp.php',
            type: 'POST',
            data: {
                'PoID': PoID
            },
            success: function(data) {
                //  $("#Update").hide();

                $('.modal-title').text('PO Status Details - ' + PoNo);
                $("#PopUpTable").html(data);

                $('#myModal').modal('show');

                var table = $('#key-datatable').DataTable({
                    bSort: false,
                    // scrollY: "380px",
                    // scrollX:true,
                    // sScrollXInner: "100%",
                    // paging:true,
                    // destroy: true,
                    // fixedColumns: true,
                    // "bStateSave": true,
                    // "fnStateSave": function (oSettings, oData) {
                    //  localStorage.setItem('key-datatable', JSON.stringify(oData));
                    // },
                    // "fnStateLoad": function (oSettings) {
                    //  return JSON.parse(localStorage.getItem('key-datatable'));
                    // }
                });


            }
        });

    }


    $(document).on('click', '#Update', function(e) {
        updateGrossWeight();
    });

    function validateFloatKeyPress(el, evt) {
        var charCode = (evt.which) ? evt.which : evt.keyCode;
        var number = el.value.split('.');

        if (charCode != 46 && charCode > 31 && (charCode < 48 || charCode > 57)) {
            return false;
        }

        // Just one dot
        if (number.length > 1 && charCode == 46) {
            return false;
        }

        // Get the carat position
        var caratPos = getSelectionStart(el);
        var dotPos = el.value.indexOf(".");

        if (caratPos > dotPos && dotPos > -1 && (number[1].length > 2)) {
            return false;
        }

        return true;
    }

    // Thanks: http://javascript.nwbox.com/cursor_position/
    function getSelectionStart(o) {
        if (o.createTextRange) {
            var r = document.selection.createRange().duplicate();
            r.moveEnd('character', o.value.length);
            if (r.text == '') return o.value.length;
            return o.value.lastIndexOf(r.text);
        } else {
            return o.selectionStart;
        }
    }

    $(document).on('keypress', '.grossWeight', function(evt) {
        if (!validateFloatKeyPress(this, evt)) {
            evt.preventDefault();
        }
    });
    $(document).on('paste', '.grossWeight', function(evt) {
        evt.preventDefault();
    });



    function updateGrossWeight() {

        var InvoiceID = '<?php echo $PIId ?>';
        var Backurl = '<?php echo $backurl ?>'; //uma
        var AryPackingStyleIds = <?php echo json_encode($AryPackingStyleIds); ?>;
        var ClickedId = $(this).data('id');

        var AryBreakUp = [];


        for (i = 0; i < AryPackingStyleIds.length; i++) {
            ClickedId = AryPackingStyleIds[i];

            var GrossWeight = '#GrossWeight' + ClickedId;
            var GrossWeight = $(GrossWeight + "").val();



            var jsonData = {};

            jsonData['PckId'] = ClickedId;
            jsonData['GrossWeight'] = GrossWeight;


            AryBreakUp.push(jsonData);


        }


        var hasInvalidGrossWeight = $.grep(AryBreakUp, function(item) {
            return item.GrossWeight === '' || item.GrossWeight === '0' || item.GrossWeight === "0" || item.GrossWeight === "." || item.GrossWeight === ".0" || item.GrossWeight === "0.0";
        }).length > 0;


        if (hasInvalidGrossWeight) {
            alert("Please Enter Gross wt");
            return false;
        }

        // if (GrossWeight == '' || GrossWeight == '0' || GrossWeight == 0) {
        //     alert('Please Enter Gross wt');
        //     return;
        // }


        $.ajax({
            type: 'POST',
            dataType: "json",
            url: 'Ajax/AjCsUpdates.php',
            data: {
                'InvoiceID': InvoiceID,
                'Backurl': Backurl, //uma
                'Type': 'GrosswtUpdate',
                'AryBreakUp': JSON.stringify(AryBreakUp)
            },


            success: function(data) {

                var obj = JSON.parse(JSON.stringify(data));
                if (obj.error == null) {
                    $('#TotalGrossWt').val(obj.TotalGrossWt);

                    alert("Updated Successfully");

                    $('#myModal').modal('hide');
                    location.reload();


                } else {
                    alert(obj.error);
                }



            }
        });

    }



    $('#CreateInv').on('click', function() {


        var InvNo = $('#CInvNo').val();
        var InvDate = $('#CInvDate').val();
        var submitButton = document.getElementById("Create");
        if (InvNo == '') {
            alert('Enter Invoice No');
            return;
        } else if (InvDate == '') {
            alert('Enter Invoice Date')
            return;
        }

        var PoID = <?php echo $PIId ?>;
        $.ajax({
            type: 'POST',
            dataType: "json",
            url: 'Ajax/AjCheckInvNo.php',
            data: {
                'InvNo': InvNo,
                'InvDate': InvDate,
                'PoID': PoID
            },
            success: function(data) {
                var obj = JSON.parse(JSON.stringify(data));
                if (obj.Error == null) {
                    submitButton.click();

                } else {
                    alert(obj.Error);
                }
            }
        });

    });

    $(".Freight").on("keyup", function() {
        var v = parseInt($(this).val());
        if (v > 99999) { // durga prasad
            $(this).val("");
        }
    })
</script>
