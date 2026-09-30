<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include_once("Includes/CommercialInvoiceLock.php");
//include ($_SERVER['DOCUMENT_ROOT']."/SalesNew/Constants.php");
include("RestAPI.php");
include_once ROOT . 'config/api-call.php';

$PageTitleFolder = "Shipping";
$PageTitle = "Add FDA/EU/FOB Details";
$ContinueSeries = false;

if (isset($_GET["PoID"]) && !empty($_GET["PoID"])) {
    $PoID = $_GET["PoID"];
} else {
    $PoID = "";
}

if (!empty($PoID)) {

    $api = GetShipBillDetails_API;
    $bodyContent = array('PoID' => $PoID);
    $resGetShipBillDetails = CallAPI($api, "GET", "", $bodyContent);

    $resGetShipBill = $resGetShipBillDetails->Data->Data;
    $resGetFDADetails = $resGetShipBillDetails->Data->FDADetails;
} else {
    $resGetShipBillDetails = "";
}

$api = API_EXPORTS_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", "");

$resPortDestinations = $resDropdownsAPI->Data->PortDestinations;

$apiPopUp = API_CONTAINER_RETURN_DETAILS;
$bodyContent = array('PoID' => $PoID);
if ($PoID != '') {
    $resPopup = CallAPI($apiPopUp, "GET", "", $bodyContent);
    $Data = $resPopup->Data[0];
}

function funPrintValue($key)
{
    return (isset($key) && $key != "") ? $key : "";
}

$PostedUserId = $_SESSION['UserID'];
// echo $PostedUserId;

$FDAExam = funPrintValue($resGetFDADetails->FDAExam);
$Status = funPrintValue($resGetFDADetails->Status);

if (isset($_POST['submit'])) {
    // $invoiceNo = $_POST["invoiceNo"];
    // $invoiceDate = $_POST["invoiceDate"];
    // $consignee = $_POST["consignee"];
    // $portofLoading = $_POST["portofLoading"];
    // $portofDischarge = $_POST["portofDischarge"];
    // $countyofDestination = $_POST["countyofDestination"];
    // $totalNoOfCartons = $_POST["totalNoOfCartons"];
    // $netWt = $_POST["netWt"];
    // $invoiceValue = $_POST["invoiceValue"];
    // $invoiceFreight = $_POST["invoiceFreight"];
    // $invoiceOtherDeductions = $_POST["invoiceOtherDeductions"];
    // $fOBINR = $_POST["fOBINR"];

    $containerGateOutDate = $_POST["containerGateOutDate"];
    $emptyContainerReturnDate = $_POST["emptyContainerReturnDate"];
    $entryNumber = $_POST["entryNumber"];
    $fdaEUHoldDate = $_POST["fdaEUHoldDate"];
    $fdaEUReleaseDate = $_POST["fdaEUReleaseDate"];
    $fdaEntryNumber = $_POST["fdaEntryNumber"];
    $fdaEntryDate = $_POST["fdaEntryDate"];
    $FdaExam = $_POST["drpFdaExam"];
    $FDAContainerStatus = $_POST["drpStatus"];
    $ShippingNotes = $_POST["notes"];
    $Remarks = $_POST["remark"];
    $NoticeDate1 = $_POST["noticedate1"];
    $NoticeDate2 = $_POST["noticedate2"];
    $NoticeDate3 = $_POST["noticedate3"];
    $NoticeDate4 = $_POST["noticedate4"];
    $SampleCollectionDate = $_POST["sampleCollectionDate"];
    $SampleReceivedDate = $_POST["sampleReceivedDate"];

    // $agentInvoice = $_POST["agentInvoice"];
    // $actualAPaidUSD = $_POST["actualAPaidUSD"];

    // echo $shippingBillNo ." -- ". $_POST["shippingBillNo"];

    $api = PostFDADetails_API;

    $postBodyContent = array(
        "PoID" => $PoID,
        "ContainerGateOutDate" => $containerGateOutDate,
        "EmptyContainerReturnDate" => $emptyContainerReturnDate,
        "EmptyContainerEntryNumber" => $entryNumber,
        "FDAEUHoldDate" => $fdaEUHoldDate,
        "FDAEUReleaseCFIADate" => $fdaEUReleaseDate,
        "FDAEntryNumber" => $fdaEntryNumber,
        "FDAEntryDate" => $fdaEntryDate,
        "FDAExam" => $FdaExam,
        "FDAContainerStatus" => $FDAContainerStatus,
        "ShippingNotes" => $ShippingNotes,
        "Remarks" => $Remarks,
        "NoticeDate1" => $NoticeDate1,
        "NoticeDate2" => $NoticeDate2,
        "NoticeDate3" => $NoticeDate3,
        "NoticeDate4" => $NoticeDate4,
        "SampleCollectionDate" => $SampleCollectionDate,
        "SampleReceivedDate" => $SampleReceivedDate,
        // "AgentInvoice"=>$agentInvoice,
        // "ActualPaidUSD"=>$actualAPaidUSD,
        "PostedUserId" => $PostedUserId
    );

    // var_dump($postBodyContent);

    $resPostFDADetails = CallAPI($api, "POST", "", $postBodyContent);
    // var_dump($resPostFDADetails);
    // exit;
    $resPostFDA = $resPostFDADetails->Data;

    if (isset($resPostFDA->Msg)) {
        $_SESSION['Status'] = "T";
    }
    if (isset($resPostFDA->Error)) {
        $_SESSION['Error'] = "T";
    }

    if ($_POST['action'] == 'next') {
        header("Location: add-container-return-details?PoID=" . $PoID);
    } else {
        header("location: fda-status");
    }
    exit();
}
$hideIsfEditActions = isCompletedCollection($iconn, $PoID);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <?php include($IncludesPath . "title-meta.php"); ?>
    <?php include($IncludesPath . "DbLinks.php"); ?>
    <?php include($IncludesPath . "head-css.php"); ?>
    <style>
        select.error+.select2-container,
        input.error,
        textarea.error {
            border: 1px solid red;
        }

        input[type=text]:disabled,
        input[type=date]:disabled {
            background: #F1F1F1;
        }
    </style>
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
                    <?php include($IncludesPath . "page-title.php"); ?>

                    <!-- <?php if (isset($_SESSION['Status']) && !empty($_SESSION['Status'])) { ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="mdi mdi-check-all mr-2"></i>
                            Data updated successfully.
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <?php $_SESSION['Status'] = ''; ?>
                    <?php } ?> -->

                    <?php if (isset($_SESSION['Error']) && !empty($_SESSION['Error'])) { ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="mdi mdi-check-all mr-2"></i>
                            Some error has occurred.
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <?php $_SESSION['Error'] = ''; ?>
                    <?php } ?>

                    <!--<div class="row">

                           <form role="form" method="post"  id="frmShippingBilling">
                             <div class="col-12">-->
                    <form role="form" method="post" id="frmShippingBilling">
                        <div class="card">
                            <div class="card-body">
                                <div class="row">

                                    <div class="form-group col-lg-3">
                                        <label for="invoiceNo">Invoice No<span style="color:red">*</span></label>
                                        <input type="text" class="form-control" name='invoiceNo' id="invoiceNo"
                                            placeholder="Enter Invoice No" disabled
                                            value="<?php echo funPrintValue($resGetShipBill->CInvNo) ?>">
                                    </div>

                                    <div class="form-group col-lg-3">
                                        <label for="invoiceDate">Invoice Date</label>
                                        <input type="date" onkeydown="return false" class="form-control"
                                            name='invoiceDate' id="invoiceDate" placeholder="Enter Invoice Date"
                                            disabled value="<?php echo funPrintValue($resGetShipBill->CInvDate) ?>">
                                    </div>

                                    <div class="form-group col-lg-3">
                                        <label for="consignee">Consignee</label>
                                        <input type="text" class="form-control" name='consignee' id="consignee"
                                            placeholder="Enter Consignee" disabled
                                            value="<?php echo funPrintValue($resGetShipBill->ConsigneeName) ?>">
                                    </div>

                                    <div class="form-group col-lg-3">
                                        <label for="portofLoading">Port of Loading</label>
                                        <input type="text" class="form-control" name='portofLoading' id="portofLoading"
                                            placeholder="Port of Loading" disabled
                                            value="<?php echo funPrintValue($resGetShipBill->LoadingPort) ?>">
                                    </div>

                                    <div class="form-group col-lg-3">
                                        <label for="portofDischarge">Port of Discharge</label>
                                        <input type="text" class="form-control" name='portofDischarge'
                                            id="portofDischarge" placeholder="Port of Discharge" disabled
                                            value="<?php echo funPrintValue($resGetShipBill->DischargePort) ?>">
                                    </div>

                                    <div class="form-group col-lg-3">
                                        <label for="countyofDestination">County of Destination</label>
                                        <input type="text" class="form-control" name='countyofDestination'
                                            id="countyofDestination" placeholder="County of Destination" disabled
                                            value="<?php echo funPrintValue($resGetShipBill->CountryName) ?>">
                                    </div>
                                    <div class="form-group col-lg-3">
                                        <label for="containernumber">Container Number</label>
                                        <input type="text" class="form-control" name='containernumber'
                                            id="containernumber" placeholder="Container Number" disabled
                                            value="<?php echo funPrintValue($resGetShipBill->ContainerNo) ?>">
                                    </div>

                                    <div class="form-group col-lg-3">
                                        <label for="blnumber">BL Number</label>
                                        <input type="text" class="form-control" name='blnumber' id="blnumber"
                                            placeholder="BL Number" disabled
                                            value="<?php echo funPrintValue($resGetShipBill->BLNo) ?>">
                                    </div>

                                    <div class="form-group col-lg-3">
                                        <label for="totalNoOfCartons">Total No.of Cartons</label>
                                        <input type="text" class="form-control" name='totalNoOfCartons'
                                            id="totalNoOfCartons" placeholder="Enter Total No.of Cartons" disabled
                                            value="<?php echo funPrintValue($resGetShipBill->TotalMcs) ?>">
                                    </div>

                                    <div class="form-group col-lg-3">
                                        <label for="netWt">Net Wt (KGS)</label>
                                        <input type="text" class="form-control" name='netWt' id="netWt"
                                            placeholder="Enter Net Wt" disabled
                                            value="<?php echo funPrintValue($resGetShipBill->TotalNetWt) ?>">
                                    </div>

                                    <div class="form-group col-lg-3">
                                        <label for="invoiceValue">Invoice Value</label>
                                        <input type="text" class="form-control" name='invoiceValue' id="invoiceValue"
                                            placeholder="Enter Invoice Value" disabled
                                            value="<?php echo funPrintValue($resGetShipBill->InvValueUSD) ?>">
                                    </div>

                                    <div class="form-group col-lg-3">
                                        <label for="invoiceFreight">Freight</label>
                                        <input type="text" class="form-control" name='invoiceFreight'
                                            id="invoiceFreight" placeholder="Enter Freight" disabled
                                            value="<?php echo funPrintValue($resGetShipBill->Freight) ?>">
                                    </div>

                                    <div class="form-group col-lg-3">
                                        <label for="invoiceOtherDeductions">Other Deductions $</label>
                                        <input type="text" class="form-control" name='invoiceOtherDeductions'
                                            id="invoiceOtherDeductions" placeholder="Enter Other Deductions $" disabled
                                            value="<?php echo funPrintValue($resGetShipBill->Duty) ?>">
                                    </div>

                                    <div class="form-group col-lg-3">
                                        <label for="fOBINR">FOB INR</label>
                                        <input type="text" class="form-control" name='fOBINR' id="fOBINR"
                                            placeholder="Enter FOB INR" disabled
                                            value="<?php echo funPrintValue($resGetShipBill->FOBValueINR) ?>">
                                    </div>

                                </div>



                            </div>

                            <!-- end col -->
                        </div> <!-- end row -->


                        <!--<div class="col-12">-->
                        <div class="page-title-box">
                            <h4 class="mb-0" id="PageHeader">FDA/EU/FOB Details</h4>
                        </div>

                        <div class="card">

                            <div class="card-body">

                                <div class="row">

                                    <div class="form-group col-lg-3">
                                        <label for="containerGateOutDate">Container Gate Out Date</label>
                                        <input type="date" onkeydown="return false" class="form-control"
                                            name='containerGateOutDate' id="containerGateOutDate" placeholder="Enter"
                                            value="<?php echo funPrintValue($resGetFDADetails->ContainerGateOutDate) ?>" />
                                    </div>

                                    <div class="form-group col-lg-3">
                                        <label for="emptyContainerReturnDate">Empty Container Return Date</label>
                                        <input type="date" onkeydown="return false" class="form-control"
                                            name='emptyContainerReturnDate' id="emptyContainerReturnDate"
                                            placeholder="Enter Empty Container Return Date"
                                            value="<?php echo funPrintValue($resGetFDADetails->EmptyContainerReturnDate) ?>" />
                                    </div>

                                    <div class="form-group col-lg-3">
                                        <label for="entryNumber">Entry Number(Empty Container)</label>
                                        <input type="text" class="form-control" name='entryNumber' id="entryNumber"
                                            value="<?php echo funPrintValue($resGetFDADetails->EmptyContainerEntryNumber) ?>"
                                            placeholder="Enter Entry Number(Empty Container)" />
                                    </div>

                                    <div class="form-group col-lg-3">
                                        <label for="fdaEUHoldDate">FDA/EU Hold Date</label>
                                        <input type="date" onkeydown="return false" class="form-control"
                                            name='fdaEUHoldDate' id="fdaEUHoldDate"
                                            placeholder="Enter FDA/EU Release/CFIA Date"
                                            value="<?php echo funPrintValue($resGetFDADetails->FDAEUHoldDate) ?>" />
                                    </div>

                                    <div class="form-group col-lg-3">
                                        <label for="fdaEUReleaseDate">FDA/EU Release/CFIA Date</label>
                                        <input type="date" onkeydown="return false" class="form-control"
                                            name='fdaEUReleaseDate' id="fdaEUReleaseDate"
                                            value="<?php echo funPrintValue($resGetFDADetails->FDAEUReleaseCFIADate) ?>"
                                            placeholder="Enter FDA/EU Release/CFIA Date" />
                                    </div>

                                    <div class="form-group col-lg-3">
                                        <label for="fdaEntryNumber">FDA Entry Number</label>
                                        <input type="text" class="form-control" name='fdaEntryNumber'
                                            id="fdaEntryNumber"
                                            value="<?php echo funPrintValue($resGetFDADetails->FDAEntryNumber) ?>"
                                            placeholder="Enter FDA Entry Number" />
                                    </div>

                                    <div class="form-group col-lg-3">
                                        <label for="fdaEntryDate">FDA Entry Date</label>
                                        <input type="date" onkeydown="return false" class="form-control"
                                            name='fdaEntryDate' id="fdaEntryDate"
                                            value="<?php echo funPrintValue($resGetFDADetails->FDAEntryDate) ?>"
                                            placeholder="Enter FDA Entry Date" />
                                    </div>

                                    <!-- <div class="form-group col-lg-3" >
                                                    <label for="lEODate">FOB Value SB USD</label>
                                                    <input type="text" class="form-control" name='lEODate' id="lEODate" placeholder="Enter FOB Value SB USD" />
                                                </div>

                                                <div class="form-group col-lg-3" >
                                                    <label for="lEODate">FOB Value SB INR</label>
                                                    <input type="text" class="form-control" name='lEODate' id="lEODate" placeholder="Enter FOB Value SB INR"  />
                                                </div>

                                                <div class="form-group col-lg-3" >
                                                    <label for="lEODate">FOB Value INR</label>
                                                    <input type="text" class="form-control" name='lEODate' id="lEODate" placeholder="Enter FOB Value INR" />
                                                </div> -->

                                    <!-- <div class="form-group col-lg-3" >
                                                    <label for="agentInvoice">Agent Invoice</label>
                                                    <input type="text" class="form-control" name='agentInvoice' id="agentInvoice" placeholder="Enter Agent Invoice" />
                                                </div>

                                                <div class="form-group col-lg-3" >
                                                    <label for="actualAPaidUSD">Actual A Paid USD</label>
                                                    <input type="text" class="form-control" name='actualAPaidUSD' id="actualAPaidUSD" placeholder="Enter Actual A Paid USD" />
                                                </div>                                                 -->

                                    <div class="col-lg-3">
                                        <label>FDA Exam</label>
                                        <select class="form-control select2" name="drpFdaExam" id="drpFdaExam">
                                            <option value="">Select </option>
                                            <option value="Yes" <?php if ($FDAExam == "Yes")
                                                echo 'selected="selected"' ?>>
                                                    Yes</option>
                                                <option value="Proceed" <?php if ($FDAExam == "Proceed")
                                                echo 'selected="selected"' ?>>Proceed</option>
                                                <option value="Other Reasons" <?php if ($FDAExam == "Other Reasons")
                                                echo 'selected="selected"' ?>>Other Reasons</option>
                                            </select>
                                        </div>

                                        <div class="col-lg-3">
                                            <label class="w-100">Status <button type="button"
                                                    id="btnAddContainerReturnDetails" style="float: right;">Add
                                                    +</button></label>
                                            <select class="form-control select2" name="drpStatus" id="drpStatus">
                                                <option value="">Select </option>

                                                <option value="SELECTED FOR FDA EXAMINATION" <?php if ($Status == "SELECTED FOR FDA EXAMINATION")
                                                echo 'selected="selected"' ?>>SELECTED FOR FDA
                                                    EXAMINATION</option>
                                                <option value="CUSTOMS X-RAY-MET-SCREENING EXAM" <?php if ($Status == "CUSTOMS X-RAY-MET-SCREENING EXAM")
                                                echo 'selected="selected"' ?>>CUSTOMS
                                                    X-RAY-MET-SCREENING EXAM
                                                </option>
                                                <option value="AWAITING LOT DETAILS" <?php if ($Status == "AWAITING LOT DETAILS")
                                                echo 'selected="selected"' ?>>AWAITING LOT DETAILS</option>
                                                <option value="LOT DETAILS SUBMITTED" <?php if ($Status == "LOT DETAILS SUBMITTED")
                                                echo 'selected="selected"' ?>>LOT DETAILS SUBMITTED</option>
                                                <option value="LINE AVAILABILITY RECEIVED" <?php if ($Status == "LINE AVAILABILITY RECEIVED")
                                                echo 'selected="selected"' ?>>LINE AVAILABILITY
                                                    RECEIVED</option>
                                                <option value="SAMPLES COLLECTED" <?php if ($Status == "SAMPLES COLLECTED")
                                                echo 'selected="selected"' ?>>SAMPLES COLLECTED</option>
                                                <option value="LAB ANALYSIS IN PROCESS" <?php if ($Status == "LAB ANALYSIS IN PROCESS")
                                                echo 'selected="selected"' ?>>LAB ANALYSIS IN PROCESS</option>
                                                <option value="LAB ANALYSIS COMPLETED" <?php if ($Status == "LAB ANALYSIS COMPLETED")
                                                echo 'selected="selected"' ?>>LAB ANALYSIS COMPLETED
                                                </option>
                                                <option value="PENDING REVIEW BY FDA COMPLIANCE STAFF" <?php if ($Status == "PENDING REVIEW BY FDA COMPLIANCE STAFF")
                                                echo 'selected="selected"' ?>>PENDING REVIEW BY FDA COMPLIANCE STAFF</option>
                                                <option value="TESTED AND RELEASED" <?php if ($Status == "TESTED AND RELEASED")
                                                echo 'selected="selected"' ?>>TESTED AND RELEASED</option>
                                                <option value="RELEASED WITHOUT TESTING" <?php if ($Status == "RELEASED WITHOUT TESTING")
                                                echo 'selected="selected"' ?>>RELEASED WITHOUT TESTING
                                                </option>
                                                <option value="DETAINED" <?php if ($Status == "DETAINED")
                                                echo 'selected="selected"' ?>>DETAINED</option>
                                                <option value="RELEASED AFTER WRONGFUL DETENTION" <?php if ($Status == "RELEASED AFTER WRONGFUL DETENTION")
                                                echo 'selected="selected"' ?>>RELEASED AFTER WRONGFUL DETENTION</option>

                                                <option value="Rejected" <?php if ($Status == "Rejected")
                                                echo 'selected="selected"' ?>>Rejected</option>
                                                <option value="Cancelled" <?php if ($Status == "Cancelled")
                                                echo 'selected="selected"' ?>>Cancelled</option>
                                                <option value="Destroyed" <?php if ($Status == "Destroyed")
                                                echo 'selected="selected"' ?>>Destroyed</option>
                                                <option value="Container ROB" <?php if ($Status == "Container ROB")
                                                echo 'selected="selected"' ?>>Container ROB</option>
                                                <option value="Return Container" <?php if ($Status == "Return Container")
                                                echo 'selected="selected"' ?>>Return Container</option>
                                            </select>
                                        </div>

                                        <div class="form-group col-lg-3">
                                            <label for="SampleCollectionDate">Sample Collection Date</label>
                                            <input type="date" onkeydown="return false" class="form-control"
                                                name='sampleCollectionDate' id="sampleCollectionDate"
                                                value="<?php echo funPrintValue($resGetFDADetails->SampleCollectionDate) ?>"
                                            placeholder="" />
                                    </div>

                                    <div class="form-group col-lg-3">
                                        <label for="SampleReceivedDate">Sample Received Date</label>
                                        <input type="date" onkeydown="return false" class="form-control"
                                            name='sampleReceivedDate' id="sampleReceivedDate"
                                            value="<?php echo funPrintValue($resGetFDADetails->SampleReceivedDate) ?>"
                                            placeholder="" />
                                    </div>

                                    <div class="form-group col-lg-3">
                                        <label>Remarks</label>
                                        <textarea type="text" class="form-control" name='remark' id="remark"
                                            placeholder="Enter Remark">
                                                    <?php echo funPrintValue($resGetFDADetails->Remarks) ?>
                                                    </textarea>
                                    </div>

                                    <div class="form-group col-lg-3">
                                        <label>Notes</label>
                                        <textarea type="text" class="form-control" name='notes' id="notes"
                                            placeholder="Enter Notes" readonly>
                                                        <?php echo funPrintValue($resGetFDADetails->ShippingNotes) ?></textarea>
                                    </div>



                                    <div class="form-group col-lg-3">
                                        <label>NoticeDate1</label>
                                        <input type="date" class="form-control" name='noticedate1' id="noticedate1"
                                            value="<?php echo funPrintValue($resGetFDADetails->NoticeDate1) ?>"
                                            placeholder="Enter " readonly></input>
                                    </div>

                                    <div class="form-group col-lg-3">
                                        <label>NoticeDate2</label>
                                        <input type="date" class="form-control" name='noticedate2' id="noticedate2"
                                            placeholder="Enter " readonly
                                            value="<?php echo funPrintValue($resGetFDADetails->NoticeDate2) ?>"></input>
                                    </div>

                                    <div class="form-group col-lg-3">
                                        <label>NoticeDate3</label>
                                        <input type="date" class="form-control" name='noticedate3' id="noticedate3"
                                            placeholder="Enter " readonly
                                            value="<?php echo funPrintValue($resGetFDADetails->NoticeDate3) ?>"></input>
                                    </div>

                                    <div class="form-group col-lg-3">
                                        <label>NoticeDate4</label>
                                        <input type="date" class="form-control" name='noticedate4' id="noticedate4"
                                            placeholder="Enter "
                                            value="<?php echo funPrintValue($resGetFDADetails->NoticeDate4) ?>"
                                            readonly></input>
                                    </div>

                                </div>

                                <div class="row">
                                    <div class="col-12 text-right">
                                        <!-- <button hidden type="submit"  name='submit' id="Create" class="btn btn-success">Next</button> -->
                                        <!--<a target="" href="PIList.php" class="btn btn-danger"> Close</a>-->
                                        <!-- <button class="btn btn-primary" id="btnModalPopup" data-toggle="modal" data-target="#modalNoticeData" disabled>Notice Date </button>-->
                                        <input type="hidden" name="action" id="formAction" value="save" />
                                        <!-- <button type="submit" name='submit' id="btnNext" class="btn btn-primary">Next</button>  -->
                                        <a href="fda-status" id="close" name="close" class="btn btn-danger">Close </a>
                                        <?php if( !$hideIsfEditActions ){ ?>
                                        <button type="submit" name='submit' id="btnSave"
                                            class="btn btn-success">Save</button>
                                        <?php } ?>

                                    </div>
                                </div>

                            </div>
                            <!-- end col -->
                        </div> <!-- end row -->
                    </form>
                </div> <!-- end card body-->
            </div> <!-- end card body-->

            <!--</form> -->

            <!-- End Page-content -->

            <?php include($IncludesPath . "footer.php"); ?>

        </div>
        <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->

    <div class="modal fade" id="modalAddContainerReturnDetails" role="dialog">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title modheading"> Add Container Return Details <span class="spanInvNo"></span>
                    </h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div id="">
                        <div class="container-fluid">
                            <div class="row">
                                <div style="padding: 15px;">
                                    <div class="">
                                        <form role="form" method="post" id="frmReturnContainer">
                                            <div class="row">

                                                <div class="col col-md-3 mb-3">
                                                    <label for="txtRecievedDate" class="form-label">Stock Recieved
                                                        Date</label>
                                                    <input type="date" class="form-control" id="txtRecievedDate"
                                                        name="txtRecievedDate"
                                                        value='<?php echo $Data->ReceivedDate ?>' />
                                                </div>

                                                <div class="col col-md-3 mb-3">
                                                    <label for="txtReturnBillNo" class="form-label">Return Bill
                                                        No</label>
                                                    <input type="text" class="form-control" id="txtReturnBillNo"
                                                        name="txtReturnBillNo" placeholder="Enter Return Bill No"
                                                        value='<?php echo $Data->ReturnBLNo ?>' />
                                                </div>

                                                <div class="col col-md-3 mb-3">
                                                    <label for="txtContainerNo" class="form-label">Return Container
                                                        No</label>
                                                    <input type="text" class="form-control" id="txtContainerNo"
                                                        name="txtContainerNo" placeholder="Enter Return Container No"
                                                        value='<?php echo $Data->ReturnContainerNo ?>' />
                                                </div>

                                                <div class="col col-md-3 mb-3">
                                                    <label for="txtContainerWeight" class="form-label">Return Container
                                                        Net Weight KGs</label>
                                                    <input type="text" class="form-control" id="txtContainerWeight"
                                                        name="txtContainerWeight"
                                                        placeholder="Enter Return Container Net Weight KGs"
                                                        value='<?php echo $Data->NetWeightKgs ?>' />
                                                </div>

                                                <div class="col col-md-3 mb-3">
                                                    <label for="drpReturnPort">Container Return Port</label>
                                                    <select class="drpSelect" name="drpReturnPort" id="drpReturnPort">
                                                        <option value="" <?php echo ($Data->PortName == '') ? 'selected' : ''; ?>>Select</option>
                                                        <?php foreach ($resPortDestinations as $List) { ?>
                                                            <option value="<?php echo $List->PortId ?>" <?php echo ($Data->PortName == $List->Name) ? 'selected' : ''; ?>>
                                                                <?php echo $List->Name ?>
                                                            </option>
                                                        <?php } ?>
                                                    </select>
                                                </div>

                                                <div class="col col-md-3 mb-3">
                                                    <label for="txtBillOfEntryNo" class="form-label">Bill of Entry
                                                        No</label>
                                                    <input type="text" class="form-control" id="txtBillOfEntryNo"
                                                        name="txtBillOfEntryNo" placeholder="Enter Bill of Entry No"
                                                        value='<?php echo $Data->BillofEntryNo ?>' />
                                                </div>

                                                <div class="col col-md-3 mb-3">
                                                    <label for="txtBillOfEntryDate" class="form-label">Bill of Entry
                                                        Date</label>
                                                    <input type="date" class="form-control" id="txtBillOfEntryDate"
                                                        name="txtBillOfEntryDate" placeholder="Enter Bill of Entry Dat"
                                                        value='<?php echo $Data->BillofEntryDate ?>' />
                                                </div>

                                                <div class="col col-md-3 mb-3">
                                                    <label for="txtRemarks" class="form-label">Remarks<span
                                                            style="color:red">*</span></label>
                                                    <input type="text" class="form-control" id="txtRemarks"
                                                        name="txtRemarks" placeholder="Enter Remarks"
                                                        value='<?php echo $Data->Remarks ?>' />
                                                </div>

                                                <!-- <div class="col col-md-3 mb-3">
                                                        <label for="drpReturnStatus">Status<span style="color:red">*</span></label>
                                                        <select class="form-control drpSelect" name="drpReturnStatus" id="drpReturnStatus">
                                                            <option value="">Select</option>
                                                            <option value="Rejected">Rejected</option>
                                                            <option value="Cancelled">Cancelled [FDA/Custom]</option>
                                                            <option value="Destroyed">Destoryed</option>
                                                            <option value="Container ROB">Container ROB</option>
                                                            <option value="Return Container">Return Container</option>
                                                        <select>
                                                    </div> -->

                                                <div class="text-center w-100">
                                                    <!-- <input type="hidden" id="hdnReturnID" name="hdnReturnID" value="<?php echo $ReturnID ?>" /> -->
                                                    <input type="hidden" id="hdnPoID" name="hdnPoID"
                                                        value="<?php echo $_GET['PoID']; ?>" />
                                                    <button type="button" class="btn btn-danger"
                                                        data-dismiss="modal">Close</button>
                                                    <?php if ($ReturnID == '') { ?>
                                                        <button class="btn btn-primary btnSave" name="submit"
                                                            id="submit">Save</button>
                                                    <?php } else { ?>
                                                        <button class="btn btn-success btnSave" name="submit"
                                                            id="submit">Update</button>
                                                    <?php } ?>
                                                </div>

                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- <div class="modal fade" id="modalNoticeData" role="dialog">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title modheading"> Notice Dates <span class="spanInvNo"></span></h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div id="">
                                <div class="row">
                                    <div class="form-group col-lg-6" >
                                        <label for="noticeDate1">Notice Date1</label>
                                        <input type="date" class="form-control" name='noticeDate1' id="noticeDate1" placeholder="Enter Notice Date1" />
                                    </div>
                                    <div class="form-group col-lg-6" >
                                        <label for="noticeDate2">Notice Date2</label>
                                        <input type="date" class="form-control" name='noticeDate2' id="noticeDate2" placeholder="Enter Notice Date2" />
                                    </div>
                                    <div class="form-group col-lg-6" >
                                        <label for="noticeDate3">Notice Date3</label>
                                        <input type="date" class="form-control" name='noticeDate3' id="noticeDate3" placeholder="Enter Notice Date3" />
                                    </div>
                                    <div class="form-group col-lg-6" >
                                        <label for="noticeDate4">Notice Date4</label>
                                        <input type="date" class="form-control" name='noticeDate4' id="noticeDate4" placeholder="Enter Notice Date4" />
                                    </div>
                                    
                                    <div class="form-group text-center col-lg-12 mt-3" >                                        
                                        <input type="button" class="btn btn-primary" name='btnSaveNoticeDate' id="btnSaveNoticeDate" value="Save"  />
                                    </div>
                                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div> -->

    <?php include($IncludesPath . "vendor-scripts.php"); ?>
    <?php include($IncludesPath . "NewScripts.php"); ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"
        type="text/javascript"></script>

    <script>
        var poID = "<?php echo isset($_GET["PoID"]) && !empty($_GET["PoID"]) ? $_GET["PoID"] : "" ?>";


        $(document).on("keyup", "#exchangeRate, #sBFobUs", function () {
            this.value = this.value.replace(/[^0-9\.]/g, '');
            var val1 = +$("#exchangeRate").val();
            var val2 = +$("#sBFobUs").val();
            $("#sbFobInr").val(val1 * val2);
        });

        $("#drpFdaExam").on("change", function () {
            var val = $(this).val();
            // console.log(val);
            if (val == "Yes") {
                $('#notes').prop('readonly', false);
                $('#noticedate1').prop('readonly', false);
                $('#noticedate2').prop('readonly', false);
                $('#noticedate3').prop('readonly', false);
                $('#noticedate4').prop('readonly', false);
                // $('#btnModalPopup').prop('disabled', false); // If checked enable item       
            } else {
                //$('#btnModalPopup').prop('disabled', true); // If checked disable item 
                $('#notes').prop('readonly', true);
                $('#noticedate1').prop('readonly', true);
                $('#noticedate2').prop('readonly', true);
                $('#noticedate3').prop('readonly', true);
                $('#noticedate4').prop('readonly', true);
            }

        })
    </script>
    <script>
        jQuery.validator.addMethod('selectcheck', function (value) {
            return (value != '0' || value != '');
        }, "year required");

        jQuery.validator.addMethod("noSpace", function (value, element) {
            return value == '' || value.trim().length != 0;
        }, "No space please and don't leave it empty");

        $(document).ready(function () {

            $('#btnNext').on('click', function () {
                $('#formAction').val('next');
                $('#frmShippingBilling').submit();
            });

            var fdaExam = $("#drpFdaExam").val();

            if (fdaExam == "Yes") {
                $("#notes, #noticedate1, #noticedate2, #noticedate3, #noticedate4").attr("readonly", false);
            }

            $("#frmShippingBilling").validate({
                onkeyup: false,
                onfocusout: false,
                invalidHandler: function (form, validator) {
                    var errors = validator.numberOfInvalids();
                    if (errors) {
                        var firstInvalidElement = $(validator.errorList[0].element);
                        //$('html,body').scrollTop(firstInvalidElement.offset().top - 100);
                        firstInvalidElement.focus();
                    }
                },
                ignore: "",
                rules: {
                    invoiceNo: {
                        required: true,
                        noSpace: true
                    },
                    invoiceDate: {
                        required: true,
                        noSpace: true
                    },
                    consignee: {
                        required: true,
                        noSpace: true
                    },
                    portofLoading: {
                        required: true,
                        noSpace: true
                    },
                    portofDischarge: {
                        required: true,
                        noSpace: true
                    },
                    countyofDestination: {
                        required: true,
                        noSpace: true
                    },
                    totalNoOfCartons: {
                        required: true,
                        noSpace: true
                    },
                    netWt: {
                        required: true,
                        noSpace: true
                    },
                    invoiceValue: {
                        required: true,
                        noSpace: true
                    },
                    invoiceFreight: {
                        required: true,
                        noSpace: true
                    },
                    invoiceOtherDeductions: {
                        required: true,
                        noSpace: true
                    },
                    fOBINR: {
                        required: true,
                        noSpace: true
                    },

                    // containerGateOutDate: { required: true, noSpace: true },
                    // emptyContainerReturnDate: { required: true, noSpace: true },
                    // entryNumber: { required: true, noSpace: true },
                    // fdaEUHoldDate: { required: true, noSpace: true },
                    // fdaEUReleaseDate: { required: true, noSpace: true },
                    // fdaEntryNumber: { required: true, noSpace: true,number:true },
                    // fdaEntryDate: { required: true, noSpace: true },
                    // agentInvoice: { required: true, noSpace: true,number:true,min: 1 },
                    // actualAPaidUSD: { required: true, noSpace: true,number:true,min: 1 },

                },
                errorPlacement: function () {
                    return false; // suppresses error message text
                },
                submitHandler: function () {
                    return true;
                }
            });
        });

        $("#btnAddContainerReturnDetails").on("click", function () {
            $('#modalAddContainerReturnDetails').modal('show');
            $(".spanInvNo").text(" - " + $("#invoiceNo").val());
        });


        $("#frmReturnContainer").validate({

            onkeyup: false,
            onfocusout: false,
            invalidHandler: function (form, validator) {
                var errors = validator.numberOfInvalids();
                if (errors) {
                    var firstInvalidElement = $(validator.errorList[0].element);
                    firstInvalidElement.focus();
                }
            },
            ignore: "",
            rules: {
                // txtRecievedDate: {
                //     required: true,
                // },
                // txtReturnBillNo: {
                //     required: true,
                // },
                // txtContainerNo: {
                //     required: true,
                // },
                // txtContainerWeight: {
                //     required: true,
                // },
                // drpReturnPort: {
                //     required: true,
                // },
                // txtBillOfEntryNo: {
                //     required: true,
                // },
                // txtBillOfEntryDate: {
                //     required: true,
                // },
                txtRemarks: {
                    required: true,
                },
                // drpReturnStatus: {
                //     required: true,
                // },
            },
            errorPlacement: function () {
                return false;
            },
            submitHandler: function () {

                $.ajax({
                    url: 'Ajax/ajax-add-container-return-details.php',
                    type: 'POST',
                    data: {
                        poID: $("#hdnPoID").val(),
                        recievedDate: $("#txtRecievedDate").val(),
                        returnBillNo: $("#txtReturnBillNo").val(),
                        containerNo: $("#txtContainerNo").val(),
                        containerWeight: $("#txtContainerWeight").val(),
                        returnPort: $("#drpReturnPort").val(),
                        billEntryNo: $("#txtBillOfEntryNo").val(),
                        billEntryDate: $("#txtBillOfEntryDate").val(),
                        remarks: $("#txtRemarks").val()
                    },
                    success: function (data) {
                        console.log(data);
                        if (data.Error != '') {
                            alert(data.Msg);
                            window.location.href = "fda-status";
                        } else {
                            alert(data.Error);
                        }

                        return false;
                    }
                });

                return false;
            }
        });
    </script>

    <script>
        $(document).ready(function () {

            $(".drpSelect").select2({
                dropdownParent: $("#modalAddContainerReturnDetails"),
            });
        });
    </script>

</body>

</html>
