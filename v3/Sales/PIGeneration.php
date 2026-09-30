<?php


// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
// include($_SERVER['DOCUMENT_ROOT'] . "/SalesNew/Constants.php");
include("Includes/IncludePage.php");

$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
$HasPIOtherTax = $ArrayClientConfigData['HasPIOtherTax'];

$ArrayClientInvoiceConfigData = json_decode(json_encode($_SESSION['ClientInvoiceConfigData']), true);
$HasFrozenWeight = $ArrayClientInvoiceConfigData['ShowFrozenWeight'];
$ShowPlaceOfReceipt = $ArrayClientInvoiceConfigData['ShowPlaceOfReceipt'];

$PageTitleFolder = "Sales";
$PageTitle = "New Proforma Invoice";
$ContinueSeries = false;
$DocType = 'PI';



$SqlUom = "SELECT * FROM ScrmUnitsMaster WHERE Status=1";
$StmtUom = $iconn->query($SqlUom);


$SqlPorts1 = "SELECT * FROM ScrmPortCities WHERE IsPortofLoading=1";
$StmtPorts = $iconn->query($SqlPorts1);


$SqlExporter = "SELECT * FROM AdminExporterDetails WHERE ExpType in(1,3)";
$StmtExporter = $iconn->query($SqlExporter);

$SqlProcessor = "SELECT * FROM AdminExporterDetails WHERE ExpType in(2,3)";
$StmtProcessor = $iconn->query($SqlProcessor);

$SqlImporter = "SELECT * FROM `ScrmImporterOfRecord` WHERE Status=1";
$StmtImporter = $iconn->query($SqlImporter);

$SqlNotifyparty = "SELECT * FROM `ScrmNotifyparty` WHERE Status=1";
$StmtNotifyparty = $iconn->query($SqlNotifyparty);

$SqlCertification = "SELECT * FROM `ScrmOrderCertification` WHERE Status=1";
$StmtCertification = $iconn->query($SqlCertification);

$SqlConsignee = "SELECT * FROM ScrmConsigneeDetails WHERE Status=1";
$StmtConsignee = $iconn->query($SqlConsignee);

$SqlUltimateConsignee = "SELECT * FROM ScrmUltimateConsigneeDetails WHERE Status=1";
$StmtUltimateConsignee = $iconn->query($SqlUltimateConsignee);

$SqlBuyer = "SELECT * FROM ScrmBuyerDetails WHERE Status=1";
$StmtBuyer = $iconn->query($SqlBuyer);


$SqlBank = "SELECT * FROM `ScrmBankDetailsMaster` WHERE `BankStatus` = '1'";
$StmtBank = $iconn->query($SqlBank);

// $SqlCurrency = "SELECT * FROM `ScrmCurrencyMaster`";
// $StmtCurrency = $iconn->query($SqlCurrency);
$SqlCurrency = "SELECT * FROM `ScrmCurrencyMaster` WHERE `Status` = '1'";
$StmtCurrency = $iconn->query($SqlCurrency);

$SqlCountryList = "SELECT * FROM ScrmCountriesList";
$StmtCountryList = $iconn->query($SqlCountryList);


while ($CountryRow = $StmtCountryList->fetch_array()) {
    $Countries[] = $CountryRow;
}

$SqlPaymentTerms = "SELECT * FROM ScrmPaymentTerms  WHERE `Status` = '1'";
$StmtPaymentTerms = $iconn->query($SqlPaymentTerms);

$SqlAgent = "SELECT * FROM `ScrmPiAgentMaster` WHERE Status=1";
$StmtAgent = $iconn->query($SqlAgent);


$SqlShipmentTerms = "SELECT * FROM `ScrmShipmentTermsMaster` WHERE Status=1";
$StmtShipmentTerms = $iconn->query($SqlShipmentTerms);

$sqlProductionPlant = "SELECT ProcessingStationID,StationName,FullName,ProdCode FROM `RmProcessingStations` WHERE StationStatus=1";
$stmtProductionPlant = $iconn->query($sqlProductionPlant);

// $SqlAdminUsers = "SELECT * FROM AdminUserDetails WHERE DepartmentID=9 and PoUserStatus='1'";
// $StmtAdminUsers = $iconn->query($SqlAdminUsers);

$AccAval = true;
$CYear = date("Y");
$AYear = $CYear + 1;
while ($AccAval) {
    $PoRefId = generateAccountId();
    $sql = "SELECT count(*) as Total FROM `ScrmPoData` where PoRefId='$PoRefId'";
    $sqlca = $iconn->query($sql);
    $rlt = mysqli_fetch_array($sqlca, MYSQLI_ASSOC);
    if ($rlt['Total'] == 0) {
        $AccAval = false;
    }
}

function generateAccountId()
{
    $generator = "1357902468";
    $result = "";
    for ($i = 1; $i <= 5; $i++) {
        $result .= substr($generator, (rand() % (strlen($generator))), 1);
    }

    return $result;
}

function checkInvoiceNo($iconn, $StartYear, $InvNo, $ExporterID)
{

    $EndYear = $StartYear + 1;
    $FinStartDate = $StartYear . '-04-01';
    $FinEndDate = $EndYear . '-03-31';

    /*if($ExporterID==5){
        $sqlCount="SELECT count<span style="color:red">*</span> as Count FROM `ScrmPIData` where InvDate BETWEEN '$FinStartDate' and '$FinEndDate' and
          InvNo='$InvNo' and ExporterID='$ExporterID'  and Status not in(2,4,7)";
        }else{*/
    $sqlCount = "SELECT count(*) as Count FROM `ScrmPIData` where InvDate BETWEEN '$FinStartDate' and '$FinEndDate' and
    InvNo='$InvNo' and Status not in(2,4,7) and ExporterID ='$ExporterID'";
    //}
    //echo $sqlCount; exit;
    $StmtCount = $iconn->query($sqlCount);
    $RowCount = $StmtCount->fetch_array();
    if ($RowCount['Count'] != 0) {
        return false;
    } else {
        return true;
    }
}



extract($_POST);
extract($_GET);



if (isset($_POST['submit'])) {

    $SqlCountryType = "SELECT CountryType FROM `ScrmCountriesList` where id='$DestinationCountry' ";
    $StmtCountryType = $iconn->query($SqlCountryType);
    $RowCountryType = $StmtCountryType->fetch_array();

    $TypeCountry = $RowCountryType['CountryType'];

    $StartYear = date('Y', strtotime($InvDate));
    $LoginId = $_SESSION["UserID"];

    for ($i = 0; $i < $NoContainers; $i++) {
        $Series = $i + 1;
        $GenInvNo = $InvNo + $i;
        $GenInvNo = sprintf('%03d', $GenInvNo);

        /*if($PoNo!=''&&$PoDate!=''){
            $Status=5;
            $ApprovedBy=;
            }
            else{
            $Status=0;
            }*/
        //$InvNumFull=getFullPiInvoiceNumbertoSave($ExporterID,$InvDate,$GenInvNo);

        $productionPlantIDs = implode(',', $ProductionPlant);

        $InvNumFull = getInvoiceNumber($iconn, $ExporterID, $ProcessorID, $InvDate, $GenInvNo, $DocType);
        if (checkInvoiceNo($iconn, $StartYear, $GenInvNo, $ExporterID)) {

            $SqlContainerValues[] = "('$PoRefId','$GenInvNo','$InvNumFull','$InvDate','$ExporterID','$ProcessorID',
            '$LotNo','$SciName','$ConsigneeID','$BuyerID','$PoNo','$PoDate','$DestinationCountry',
            '$TypeCountry','$PoStatus','$YEAR','$BankID','$PortOfDischarge',
            '$FinalDestination','$ByPayment','$ShippingTerms','$DeliveryTerms',
            '$PreparedBy' ,'$PRInstrctns','$ShipByDate',0,'$LoginId','$QtyUnit','$UnitCurrency','$Remarks', '$productionPlantIDs','$ImporterID',
            '$NotifyPartyID','$ReferenceNo','$Agent','$Certification', '$PlaceofReceipt','$PortofLoading','$ShowBank',
            '$UltimateConsigneeID','$Freight','$ChargeName','$ChargeAmount','$txtShipDate','$OtherCharges','$FinalDestinationCountry')";
        } else {
            $NoContainers++;
        }
    }

    $SqlContainerValues = implode(',', $SqlContainerValues);

    $sql_org_insert = "INSERT INTO `$TblScrmPIData`( `PoRefId`, `InvNo`,`InvNumFull`, `InvDate`, `ExporterID`, `ProcessorID`, `LotNo`, `SciName`,
    `ConsigneeID`, `BuyerID`, `PoNo`, `PoDate`, `DestinationCountry`, `TypeCountry`, `PoStatus`, `Year`,
    `BankID`, `PortOfDischarge`, `FinalDestination`, `PaymentTerms`, `ShippingTerms`, `DeliveryTerms`,
    `PreparedBy`, `PRInstrctns`, `ShipByDate`,  `Status`,`LoginId`,`QtyUnit`,`UnitCurrency`,`Remarks`,`ProductionPlantID`, `ImporterID`, `NotifyPartyID`
    , `ReferenceNo`, `Agent`, `Certification`, `PlaceofReceipt`, `PortofLoading`,
    `ShowBank`, `UltimateConsigneeID`,`Freight`,`ChargeName`,`ChargeAmount`,`ShipByDateTextformat`,`OtherCharges`,`FinalDestinationCountry`)
    VALUES  $SqlContainerValues";

    // echo $sql_org_insert; exit;

    $stmt1 = $iconn->query($sql_org_insert);
    $INV_ID = $iconn->insert_id;
    legacyAuditLogInsertById($TblScrmPIData, 'PoID', $INV_ID, $_POST, ['Remarks' => ''], 'Sales');
    // }
    if ($stmt1) {


        header('Location: PoShipmentDetails.php?PIID=' . $INV_ID);
        header('Location: PoBreakUp.php?id=' . $INV_ID);
    }
}

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

                    <?php } ?>

                    <?php include($IncludesPath . "page-title.php"); ?>




                    <div class="row">
                        <div class="col-12">
                            <div class="card">

                                <form role="form" method="post" enctype="multipart/form-data">
                                    <div class="card-body">

                                        <div class="row">


                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1">Select Exporter<span
                                                        style="color:red">*</span></label>
                                                <select required class="form-control select2" name="ExporterID"
                                                    id="ExporterID">
                                                    <option value="">Select Exporter</option>
                                                    <?php while ($RowExporter = $StmtExporter->fetch_array()) { ?>

                                                        <option Value="<?php echo $RowExporter['ExpID']; ?>">
                                                            <?php echo $RowExporter['ExpName']; ?>
                                                        </OPTION>

                                                    <?php } ?>

                                                </select>
                                            </div>


                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1">Select Processor<span
                                                        style="color:red">*</span></label>
                                                <select required class="form-control select2" name="ProcessorID"
                                                    id="ProcessorID">
                                                    <option value="">Select Processor</option>
                                                    <?php while ($RowProcessor = $StmtProcessor->fetch_array()) { ?>

                                                        <option Value="<?php echo $RowProcessor['ExpID']; ?>"
                                                            data-invcode="<?php echo $RowProcessor['InvShortCode'] ?>">
                                                            <?php echo $RowProcessor['ExpName'] . '(' . $RowProcessor['ExpApprovalNo'] . ')'; ?>
                                                        </OPTION>

                                                    <?php } ?>

                                                </select>
                                            </div>



                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1">No Of Containers</label>


                                                <div class="input-group">

                                                    <span class="input-group-btn">
                                                        <button class="btn btn-white btn-minuse" id="Cminus"
                                                            type="button">-</button>
                                                    </span>


                                                    <input readonly required type="text"
                                                        class="form-control no-padding add-color text-center height-25"
                                                        name="NoContainers" id="NoContainers"
                                                        placeholder="Enter Number Of Containers" maxlength="3"
                                                        value="1">
                                                    <span class="input-group-btn">
                                                        <button class="btn btn-red btn-pluss" id="Cplus"
                                                            type="button">+</button>
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputEmail1">Reference Number</label>
                                                <input type="text" class="form-control" name='ReferenceNo'
                                                    id="ReferenceNo" placeholder="Enter Reference Number" value="">
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputEmail1">Proforma Invoice No<span
                                                        style="color:red">*</span></label>
                                                <input required type="number" class="form-control" name='InvNo'
                                                    id="InvNo" placeholder="Enter Proforma Invoice No" value="">
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputEmail1">Proforma Invoice Date<span
                                                        style="color:red">*</span></label>
                                                <input required type="date" onkeydown="return false"
                                                    value="<?php echo date("Y-m-d"); ?>" class="form-control"
                                                    name='InvDate' id="InvDate"
                                                    placeholder="Enter Proforma Invoice No ">
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1"> P.O No</label>
                                                <input type="text" class="form-control" name='PoNo' id="PoNo"
                                                    placeholder="Enter P.O Number">
                                            </div>



                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1"> P.O Date</label>
                                                <input type="date" value="" class="form-control" name='PoDate'
                                                    id="PoDate" placeholder="Enter P.O Date">
                                            </div>


                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1">Consignee<span
                                                        style="color:red">*</span></label>
                                                <select required class="form-control select2" name="ConsigneeID"
                                                    id="ConsigneeID">
                                                    <option value="">Select Consignee</option>
                                                    <?php while ($RowConsignee = $StmtConsignee->fetch_array()) { ?>
                                                        <option value="<?php echo $RowConsignee['ConID'] ?>">
                                                            <?php echo $RowConsignee['ConsigneeName'] ?>
                                                        </option>

                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1">Ultimate Consignee</label>
                                                <select class="form-control select2" name="UltimateConsigneeID"
                                                    id="UltimateConsigneeID">
                                                    <option value="">Select</option>
                                                    <?php while ($RowUltimateConsignee = $StmtUltimateConsignee->fetch_array()) { ?>
                                                        <option value="<?php echo $RowUltimateConsignee['ConID'] ?>">
                                                            <?php echo $RowUltimateConsignee['ConsigneeName'] ?>
                                                        </option>

                                                    <?php } ?>
                                                </select>
                                            </div>




                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1">Buyer Name</label>
                                                <select class="form-control select2" name="BuyerID" id="BuyerID">
                                                    <option value="">Select Buyer</option>
                                                    <?php while ($RowBuyer = $StmtBuyer->fetch_array()) { ?>
                                                        <option value="<?php echo $RowBuyer['BuyerID'] ?>">
                                                            <?php echo $RowBuyer['BuyerName'] ?>
                                                        </option>

                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1">Notify Party</label>
                                                <select class="form-control select2" name="NotifyPartyID"
                                                    id="NotifyPartyID">
                                                    <option value="">Select</option>
                                                    <?php while ($RowNotifyparty = $StmtNotifyparty->fetch_array()) {
                                                        $NPartyAddress = mb_strimwidth($RowNotifyparty['NPartyAddress'], 0, 15, "...");
                                                        ?>
                                                        <option value="<?php echo $RowNotifyparty['NotifyPartyID'] ?>">
                                                            <?php echo $RowNotifyparty['NotifyPartyName'] . ' (' . $NPartyAddress . ')' ?>
                                                        </option>

                                                    <?php } ?>
                                                </select>
                                            </div>



                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1">Importer of Record<span
                                                        style="color:red">*</span></label>
                                                <select required class="form-control select2" name="ImporterID"
                                                    id="ImporterID">
                                                    <option value="">Select</option>
                                                    <?php while ($RowImporter = $StmtImporter->fetch_array()) { ?>
                                                        <option value="<?php echo $RowImporter['ImporterID'] ?>">
                                                            <?php echo $RowImporter['ImporterName'] ?>
                                                        </option>

                                                    <?php } ?>
                                                    <option value="0">NA</option>
                                                </select>
                                            </div>



                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1">Agent</label>
                                                <select class="form-control select2" name="Agent" id="Agent">
                                                    <option value="">Select</option>
                                                    <?php while ($RowAgent = $StmtAgent->fetch_array()) { ?>
                                                        <option value="<?php echo $RowAgent['AgentID'] ?>">
                                                            <?php echo $RowAgent['AgentName'] ?>
                                                        </option>

                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1">Certification</label>
                                                <select class="form-control select2" name="Certification"
                                                    id="Certification">
                                                    <option value="">Select</option>
                                                    <?php while ($RowCertification = $StmtCertification->fetch_array()) { ?>
                                                        <option value="<?php echo $RowCertification['CerificationID'] ?>">
                                                            <?php echo $RowCertification['CerificationName'] ?>
                                                        </option>

                                                    <?php } ?>
                                                </select>
                                            </div>
                                            <?php
                                            if ($ShowPlaceOfReceipt == 1) {
                                                ?>
                                                <div class="form-group col-lg-3">
                                                    <label for="PlaceofReceipt">Place of Receipt</label>
                                                    <select class="form-control select2" name="PlaceofReceipt"
                                                        id="PlaceofReceipt">
                                                        <option value="">Select</option>
                                                        <?php while ($RowPorts = $StmtPorts->fetch_array()) { ?>
                                                            <option value="<?php echo $RowPorts['PortId'] ?>">
                                                                <?php echo $RowPorts['Name'] ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                                <?php
                                                $StmtPorts->data_seek(0);
                                            }
                                            ?>
                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1">Port of Loading<span
                                                        style="color:red">*</span></label>
                                                <select required class="form-control select2" name="PortofLoading"
                                                    id="PortofLoading">
                                                    <option value="">Select</option>
                                                    <?php while ($RowPorts = $StmtPorts->fetch_array()) { ?>
                                                        <option value="<?php echo $RowPorts['PortId'] ?>">
                                                            <?php echo $RowPorts['Name'] ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1">Destination Country<span
                                                        style="color:red">*</span></label>


                                                <select required class="form-control select2" name="DestinationCountry"
                                                    id="DestinationCountry">
                                                    <option value="">Select Country</option>
                                                    <?php foreach ($Countries as $RowCountry) { ?>
                                                        <option value="<?php echo $RowCountry['id'] ?>">
                                                            <?php echo strtoupper($RowCountry['name']) ?>
                                                        </option>
                                                    <?php } ?>

                                                </select>
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1">Port of Discharge<span
                                                        style="color:red">*</span></label>
                                                <select required class="form-control select2" name="PortOfDischarge"
                                                    id="PortOfDischarge">
                                                    <option value="">Select Port</option>
                                                    <?php
                                                    $PortOfDischarge = $RowPIData['PortOfDischarge'];
                                                    foreach ($AryPorts as $RowPort) { ?>
                                                        <option <?php if ($PortOfDischarge == $RowPort['PortId']) { ?>
                                                                selected="selected" <?php } ?>
                                                            value="<?php echo $RowPort['PortId'] ?>">
                                                            <?php echo $RowPort['Name'] ?>
                                                        </option>

                                                    <?php } ?>
                                                </select>

                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1">Final Destination Country</label>


                                                <select class="form-control select2" name="FinalDestinationCountry"
                                                    id="FinalDestinationCountry">
                                                    <option value="">Select Country</option>
                                                    <?php foreach ($Countries as $RowCountry) { ?>
                                                        <option value="<?php echo $RowCountry['id'] ?>">
                                                            <?php echo strtoupper($RowCountry['name']) ?>
                                                        </option>
                                                    <?php } ?>

                                                </select>
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1"> Final Destination<span
                                                        style="color:red">*</span></label>

                                                <!--<input required type="text" class="form-control" name='FinalDestination'
                    id="FinalDestination" placeholder="Enter Final Destination" value="<?php //echo $RowPIData['FinalDestination'];
                    ?>">
                -->

                                                <select required class="form-control select2" name="FinalDestination"
                                                    id="FinalDestination">
                                                    <option value="">Select Final Destination</option>
                                                    <?php
                                                    $FinalDestination = $RowPIData['FinalDestination'];
                                                    foreach ($AryPorts as $RowPort) { ?>
                                                        <option value="<?php echo $RowPort['PortId'] ?>">
                                                            <?php echo $RowPort['Name'] ?>
                                                        </option>

                                                    <?php } ?>
                                                </select>

                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1">ShipBy Date</label>
                                                <input type="date" class="form-control" name="ShipByDate"
                                                    id="ShipByDate" placeholder="Enter ShipBy Date"
                                                    Value="<?php echo $RowPIData['ShipByDate']; ?>"></input>
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1">ShipBy Date 2 (Text)</label>
                                                <input type="text" class="form-control" name='txtShipDate'
                                                    id="txtShipDate" placeholder="Enter ShipBy Date Text ">
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1">Bank Details<span
                                                        style="color:red">*</span></label>
                                                <select required class="form-control select2" name="BankID" id="BankID">
                                                    <option value="">Select Bank</option>
                                                    <?php while ($RowBank = $StmtBank->fetch_array()) { ?>
                                                        <option value="<?php echo $RowBank['ID'] ?>">
                                                            <?php echo $RowBank['AccntName'] . ' -
                     ' . $RowBank['BankName'] . '-' . $RowBank['AccountNumber'] . '-' . $RowBank['RoutingThrough'] ?>
                                                        </option>

                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputEmail1">Show Bank<span
                                                        style="color:red">*</span></label>
                                                <select required class="form-control select2" name="ShowBank"
                                                    id="ShowBank">
                                                    <option value="0">No</option>
                                                    <option value="1">Yes</option>

                                                </select>
                                            </div>



                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1">Payment Terms<span
                                                        style="color:red">*</span></label>

                                                <select required class="form-control select2" name="ByPayment"
                                                    id="ByPayment">
                                                    <option value="">Select Payment</option>
                                                    <?php while ($RowPayment = $StmtPaymentTerms->fetch_array()) {
                                                        if ($RowPayment['LcStatus'] == 1) {
                                                            $LcStatus = 'LC';
                                                        } else {
                                                            $LcStatus = 'Non LC';
                                                        }
                                                        ?>
                                                        <option value="<?php echo $RowPayment['ID'] ?>">
                                                            <?php echo $RowPayment['PaymentName'] . ' - ' . $LcStatus . (($RowPayment['NoOfDays'] != 0) ? ' - ' . $RowPayment['ConditionDate'] . ' - (' . $RowPayment['NoOfDays'] . ' Days)' : '') ?>
                                                        </option>

                                                    <?php } ?>
                                                </select>
                                            </div>



                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputEmail1">Shipment Terms<span
                                                        style="color:red">*</span></label>

                                                <select required class="form-control select2" name="ShippingTerms"
                                                    id="ShippingTerms">
                                                    <option value="">Select Shipment Terms</option>

                                                    <?php while ($RowShipterms = $StmtShipmentTerms->fetch_array()) { ?>

                                                        <option value="<?php echo $RowShipterms['TermName'] ?>">
                                                            <?php echo $RowShipterms['TermName'] ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>

                                            </div>



                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputEmail1">Delivery Terms<span
                                                        style="color:red">*</span></label>
                                                <select required class="form-control select2" name="DeliveryTerms"
                                                    id="DeliveryTerms">
                                                    <option value="">Select</option>
                                                    <option value="CY/CY">CY/CY</option>
                                                    <option value="CY/SD">CY/SD</option>
                                                    <option value="FOB">FOB</option>

                                                </select>
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputEmail1">Quantity Units<span
                                                        style="color:red">*</span></label>

                                                <select required class="form-control select2" name="QtyUnit"
                                                    id="QtyUnit">
                                                    <option value="">Select Quantity Units</option>

                                                    <?php while ($RowUnit = $StmtUom->fetch_array()) { ?>
                                                        <option value="<?php echo $RowUnit['UnitCode'] ?>">
                                                            <?php echo $RowUnit['UnitName'] ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>

                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1">Currency<span
                                                        style="color:red">*</span></label>
                                                <select required class="form-control select2" name="UnitCurrency"
                                                    id="UnitCurrency">
                                                    <option value="">Select Currency</option>

                                                    <?php while ($RowCurrency = $StmtCurrency->fetch_array()) { ?>
                                                        <option value="<?php echo $RowCurrency['CurrencyCode'] ?>">
                                                            <?php echo $RowCurrency['CurrencyName'] . ' -
                     ' . $RowCurrency['CurrencyCode'] . '(' . $RowCurrency['CurrenySymbol'] . ')' ?>
                                                        </option>

                                                    <?php } ?>
                                                </select>
                                            </div>





                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Freight</label>
                                                <input type="text" class="form-control" name='Freight' id="Freight"
                                                    placeholder="Enter Freight"
                                                    value="<?php echo $RowCustomInvoice['Freight'] ?>">
                                            </div>
                                            <!-- Durga -->
                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Charge Name</label>
                                                <input type="text" class="form-control" name='ChargeName'
                                                    id="ChargeName" placeholder="Enter Charge Name" value="<?php //echo $RowCustomInvoice['Freight']
                                                    ?>">
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Charge Amount </label>
                                                <input type="text" class="form-control" name='ChargeAmount'
                                                    id="ChargeAmount" placeholder="Enter Charge Amount" value="<?php //echo $RowCustomInvoice['Freight']
                                                    ?>">
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputEmail1">Production Plant</label>
                                                <select required class="form-control select2" name="ProductionPlant[]"
                                                    id="ProductionPlant" multiple="multiple">
                                                    <!-- <option value="">Select</option> -->
                                                    <?php
                                                    while ($RowPlant = $stmtProductionPlant->fetch_array()) { ?>
                                                        <option <?php if ($RowPIData['ProductionPlantID'] == $RowPlant['ProcessingStationID']) { ?> selected="selected" <?php } ?>
                                                            value="<?php echo $RowPlant['ProcessingStationID'] ?>"
                                                            data-prodCode="<?php echo $RowPlant['ProdCode'] ?>">
                                                            <?php echo $RowPlant['FullName'] . ' - ' . $RowPlant['StationName'] ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <?php
                                            if ($HasPIOtherTax == 1) {
                                                ?>
                                                <div class="form-group col-lg-3">
                                                    <label for="exampleInputEmail1">Apply Other Tax<span
                                                            style="color:red">*</span></label>
                                                    <select required class="form-control select2" name="OtherCharges"
                                                        id="OtherCharges">
                                                        <option value="0">No</option>
                                                        <option value="1">Yes</option>

                                                    </select>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                            <?php
                                            if ($HasFrozenWeight == 1) {
                                                ?>
                                                <div class="form-group col-lg-3">
                                                    <label for="ShowFrozenWeight">Show Frozen Weight<span
                                                            style="color:red">*</span></label>
                                                    <select required class="form-control select2" name="ShowFrozenWeight"
                                                        id="ShowFrozenWeight">
                                                        <option value="0" selected>No</option>
                                                        <option value="1">Yes</option>
                                                    </select>
                                                </div>
                                                <?php
                                            }
                                            ?>
                                            <!-- Durga -->
                                            <div class="form-group col-lg-12">
                                                <label for="exampleInputEmail1">Remarks</label>

                                                <textarea name="Remarks" class="form-control" rows="2" cols="50"
                                                    id="Remarks" placeholder="Enter Remarks"></textarea>


                                            </div>

                                        </div>


                                        <div class="col-xs-12 box-footer" align="right">
                                            <button hidden type="submit" name='submit' id="Create"
                                                class="btn btn-success">Next</button>
                                        </div>
                                        <div class="col-xs-12 " align="right">
                                            <!--<a target="" href="PIList.php" class="btn btn-danger  "> Close</a>-->
                                            <button type="button" id="close" name="close" class="btn btn-danger">Close
                                            </button>

                                            <button type="button" id="CreatePI" class="btn btn-success">Next</button>
                                        </div>
                                    </div>

                                </form>

                                <!-- end col -->
                            </div> <!-- end row -->

                        </div> <!-- end card body-->
                    </div> <!-- end card -->
                </div><!-- end col-->
            </div>
            <!-- end row-->

        </div> <!-- container-fluid -->
    </div>
    <!-- End Page-content -->

    <?php include($IncludesPath . "footer.php"); ?>

    </div>
    <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->



    <?php include($IncludesPath . "vendor-scripts.php"); ?>
    <?php include($IncludesPath . "NewScripts.php"); ?>



</body>
<script>
    $('#Cminus').on('click', function () {

        var NoCon = $("#NoContainers").val();

        if (NoCon > 1) {
            NoCon = parseInt(NoCon) - 1;
            $("#NoContainers").val(NoCon);
        }
    });

    $('#Cplus').on('click', function () {

        var NoCon = $("#NoContainers").val();
        if (NoCon < 10) {
            NoCon = parseInt(NoCon) + 1;
            $("#NoContainers").val(NoCon);
        }
    });
</script>

<script>
    $("document").ready(function () {
        $("#ConsigneeID").select2();
        $("#BuyerID").select2();
        $("#DestinationCountry").select2();

        $("#file").change(function () {
            alert('changed!');
        });

        $('#ProductionPlant').select2();



    });
</script>


<script>
    $('#close').click(function (e) {
        window.location.href = 'proforma-list';
        // window.close();
    });


    $("document").ready(function () {

        $("#ConsigneeID").select2();
        $("#PortOfDischarge").select2();

        getBankDetails();


        $("#ProcessorID").change(function () {
            $("#ProductionPlant").val("").trigger("change");

            var selectedOption = $(this).find('option:selected');
            var invShortCode = selectedOption.attr('data-invcode');
            console.log('invShortCode ' + invShortCode);

            if (invShortCode) {
                $('#ProductionPlant option').each(function () {
                    if ($(this).attr('data-prodCode') == invShortCode) {
                        $(this).prop('selected', true);
                    }
                });
                $('#ProductionPlant').trigger('change');
            }
        });

    });

    $('#ConsigneeID').change(function () {
        var BuyerId = $('#ConsigneeID').val();
        if (BuyerId == '') {
            return;
        }
        $.ajax({
            type: 'POST',
            url: 'Ajax/AjBuyerDtls.php',
            dataType: 'json',
            data: {
                'BUYERID': BuyerId
            },
            success: function (response) {
                var myJSON = JSON.parse(JSON.stringify(response));

                $("#DestinationCountry").val(myJSON['ConsigneeCountry']).change();
                /*$("#BRAND").val(myJSON['ConsigneeBrand']);

                $("#PORT_OF_DIS").val(myJSON['ConsigneePort']);
                $("#ADDRESS_OF_CONSIGNEE").val(myJSON['ConsigneeAddress']);
                $("#FISHING_PMT_NO").val(myJSON['ConsigneeFishingNo']);
                */
            }
        });
    });

    $('#ExporterID').change(function () {

        getBankDetails();
        getInvoiceNO();

    });

    $('#InvDate').change(function () {
        getBankDetails();
        getInvoiceNO();
    });

    function getBankDetails() {
        var ExpID = $('#ExporterID').val();
        $.ajax({
            type: 'POST',
            url: 'Ajax/AjExpBankDetails.php',
            //dataType: 'json',
            data: {
                'ExpID': ExpID
            },
            success: function (response) {
                $("#BankID").html(response);
            }
        });

    }

    function getInvoiceNO() {
        var ExpID = $('#ExporterID').val();
        var InvDate = $('#InvDate').val();
        $.ajax({
            type: 'POST',
            url: 'Ajax/AjAutoInvNo.php',
            //dataType: 'json',
            data: {
                'ExpID': ExpID,
                'InvDate': InvDate
            },
            success: function (response) {
                $("#InvNo").val(response);
            }
        });

    }

    $('#DestinationCountry').change(function () {
        var CountryId = $('#DestinationCountry').val();
        if (CountryId == '') {
            return;
        }
        $.ajax({
            type: 'POST',
            url: 'Ajax/AjPortDtls.php',
            //dataType: 'json',
            data: {
                'CountryId': CountryId
            },
            success: function (response) {
                //var myJSON = JSON.parse(JSON.stringify(response));
                // console.log(response);
                $("#PortOfDischarge").html(response);
                // $("#FinalDestination").html(response);


            }
        });
        if (!$('#FinalDestinationCountry').val()) {
            $('#FinalDestination').val(null).trigger('change');
            loadFinalDestinationPorts(CountryId);
        }

        // $.ajax({
        //     type: 'POST',
        //     url: 'Ajax/AjPortFinalDest.php',
        //     //dataType: 'json',
        //     data: {
        //         'CountryId': CountryId
        //     },
        //     success: function (response) {
        //         //var myJSON = JSON.parse(JSON.stringify(response));
        //         // console.log(response);
        //         // $("#PortOfDischarge").html(response);
        //         $("#FinalDestination").html(response);


        //     }
        // });
    });

    $('#FinalDestinationCountry').change(function () {

        var CountryId = $(this).val();
        $('#FinalDestination').val(null).trigger('change');

        if (!CountryId) {
            loadFinalDestinationPorts($('#DestinationCountry').val());
            return;
        }
        loadFinalDestinationPorts(CountryId);
    });

    function loadFinalDestinationPorts(CountryId) {
        if (CountryId === '') return;

        $.ajax({
            type: 'POST',
            url: 'Ajax/AjPortFinalDest.php',
            data: { CountryId: CountryId },
            success: function (response) {
                $("#FinalDestination").html(response).trigger('change');
            }
        });
    }


</script>


<Script>
    $('document').ready(function () {


        $('#CreatePI').on('click', function () {
            var submitButton = document.getElementById("Create");

            var InvNo = $('#InvNo').val();
            var NoContainers = $('#NoContainers').val();
            var InvDate = $('#InvDate').val();
            var ExporterID = $('#ExporterID').val();
            if (InvNo == '') {
                submitButton.click();
                return;
            }

            $.ajax({
                type: 'POST',
                url: 'Ajax/AjCheckPINumberAvalabitity.php',
                dataType: 'json',
                data: {
                    'InvNo': InvNo,
                    'NoContainers': NoContainers,
                    'InvDate': InvDate,
                    'ExporterID': ExporterID
                },
                success: function (response) {
                    var myJSON = JSON.parse(JSON.stringify(response));
                    var NotAvalNo = "Invoice number are not available, ";
                    var Aval = "the available invoice numbers are  -" + myJSON['AvalNo'];



                    if (myJSON['Error'] == null) {
                        submitButton.click();
                    } else {
                        var r = confirm(NotAvalNo + "" + Aval);
                        if (r == true) {
                            submitButton.click();
                        } else {
                            //return;
                        }
                    }

                }
            });



        });

        //alert('hiiiii');
        $('#ConsigneeID').on("keyup", function () {
            /* Get input value on change */
            alert('hiiii');
            var inputVal = $(this).val();

            //alert(inputVal);
            var resultDropdown = $(this).siblings(".result");
            if (inputVal.length) {
                $.get("Ajax/AjaxConsigneeSearch.php", {
                    term: inputVal
                }).done(function (data) {
                    // Display the returned data in browser

                    //console.log('test');
                    console.log(data);
                    //alert(data);
                    resultDropdown.html(data);
                });
            } else {
                resultDropdown.empty();
            }

        });

        // Set search input value on click of result item
        $(document).on("click", ".result p", function () {
            var iid = $(this).attr('id');
            //alert(iid);
            $("#ConsigneeID").val(iid);
            $(this).parent(".result").empty();
            //alert(cat);
            $.ajax({
                type: 'POST',
                url: 'Ajax/AjaxConsigneeDetails.php',
                data: {
                    'id': iid
                },
                success: function (data) {
                    //alert(data);
                    console.log(data);
                    var obj = JSON.parse(data);


                    var C_NAME = obj.ConsigneeName;
                    var C_ADD_id = obj.ConsigneeAddress;
                    var C_PORT_OF_DIS = obj.ConsigneePort;
                    var BUYER_COUNTRY = obj.ConsigneeCountry;
                    var BRAND_NAME = obj.ConsigneeBrand;
                    var SPECIAL_INST = obj.ConsigneeSplIns;
                    var FISHING_PMT_NO = obj.ConsigneeFishingNo;
                    alert(C_NAME);


                    $('#ConsigneeId').val(iid);
                    $('#ConsigneeAddress').val(C_NAME);
                    $('#ConsigneeBrand').val(BRAND_NAME);
                    $('#ConsigneeCountry').val(BUYER_COUNTRY);
                    $('#ConsigneePort').val(C_PORT_OF_DIS);
                    $('#ConsigneeAddress').val(C_ADD_id);
                    $('#ConsigneeSplIns').val(SPECIAL_INST);
                    $('#ConsigneeFishingNo').val(FISHING_PMT_NO);

                }
            });
            $(this).parent(".result").empty();

        });

    });

    $("#Freight").on("keyup", function () {
        var v = parseInt($(this).val());
        if (v > 99999) { // durga prasad
            $(this).val("");
        }
    })
</script>

</html>
