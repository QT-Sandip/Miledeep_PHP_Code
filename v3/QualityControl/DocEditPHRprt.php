<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
session_start();
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($_SERVER['DOCUMENT_ROOT'] . "/includes/UrlEncode.php");

$ClientID = $_SESSION["ClientID"];
$ClientConfigData = $_SESSION['ClientConfigData'];
// $IsShowQcAddOnFields = $ClientConfigData->IsShowQcAddOnFields;  // uncomment this line after auth-login.php converted to API modal
$IsShowQcAddOnFields = $ClientConfigData['IsShowQcAddOnFields']; // remove this line after auth-login.php converted to API modal


$QueryParms = $_GET['Q'];
$QueryParm = $_GET['Q'];
if ($QueryParms != '') {
    $QueryParms = urlDecode64($QueryParms);
    $queries = array();
    parse_str($QueryParms, $_GET);
}

$PoID = $_GET['PoID'];
$ReferenceId = $_GET['RID'];
$SqlPo = "SELECT * FROM `ScrmPoData` where PoID='$PoID'";
$StmtPo = $iconn->query($SqlPo);
$RowPo = $StmtPo->fetch_array();

$SqlPHReport = "SELECT * FROM `QcPhAbReports` where ID='$ReferenceId'";
$stmtPHReport = $iconn->query($SqlPHReport);

$ReportDetails = $stmtPHReport->fetch_array();
$HarvestTimenDate = $ReportDetails['HarvestingDate'];
$HarvestARRAY[] = explode('/', $HarvestTimenDate);
$HarvestTime = $HarvestARRAY[0];
$HarvestTime;

//robo
$SqlCodeList = "SELECT Tracebility,CLID FROM `QcCodeList` as cl LEFT JOIN QcTracebilityList as tl on
 tl.TraceID=cl.TracebilityID WHERE cl.InvID='$PoID' GROUP BY Tracebility";
$StmtCodeList = $iconn->query($SqlCodeList);

$SqlLab = "SELECT * FROM `QcLabTechnologist` WHERE LbuStatus=1";
$StmtLab = $iconn->query($SqlLab);
$StmtLab1 = $iconn->query($SqlLab);

$PageTitle = "IN-HOUSE ELISA PRE-HARVEST TEST REPORT  - " . $RowPo['PoNo'];
$PageTitleFolder = "Quality Control";

extract($_POST);
extract($_GET);

if (isset($_POST['submit'])) {

    $sqlVerifiedBy = "SELECT LbuName FROM `QcLabTechnologist` WHERE LUID='$VerifiedBy'";
    $stmtVerifiedBy = $iconn->query($sqlVerifiedBy);
    $RowVerifiedBy = $stmtVerifiedBy->fetch_array();
    $VerifiedByName = $RowVerifiedBy['LbuName'];

    $sqlTestedBy = "SELECT LbuName FROM `QcLabTechnologist` WHERE LUID='$TestedBy'";
    $stmtTestedBy = $iconn->query($sqlTestedBy);
    $RowTestedBy = $stmtTestedBy->fetch_array();
    $TestedByName = $RowTestedBy['LbuName'];
    // echo
    $sql_update = "UPDATE `QcPhAbReports` SET `ReportDate`='$ReportDate',`TracebilityID`='$TracebilityID',
	`LabRep`='$LabRep',
	`SamplingDate`='$SamplingDate',`SamplingTime`='$SamplingTime',
	`SampleID`='$SampleID',
	`AnalysisDate`='$AnalysisDate',`ReceivedDate`='$ReceivedDate',
	`DateIssuence`='$DateIssuence',
	`TestedByID`='$TestedBy',`TestedBy`='$TestedByName',`VerifiedBy`='$VerifiedByName',`VerifiedByID`='$VerifiedBy',
    `FinishedWeight`='$FinishedWeight',`SampleQuantity`='$SampleWeight',
	`Yield`='$Yield' ,`Source`='$Source',
	`HarvestingDate`='$HarvestingDate',
    `SamplingQty`='$SamplingQty',`SamplingFinishedQty`='$SamplingFinishedQty',`SamplingPreharvestQty`='$SamplingPreharvestQty',
	`ReceivedQty`='$ReceivedQty',`RecordNo`='$RecordNo'
	WHERE ID=$ReferenceId";
    // exit;

    $sql_update;


    $stmt1 = $iconn->query($sql_update);
    header('Location: DocAddReports.php?Q=' . $QueryParm);
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
    <?php include($IncludesPath . "PreLoader.php"); ?>

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



                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <form role="form" method="post">
                                    <div class="card-body">

                                        <div class="row">

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputPassword1">Traceability</label>
                                                <select class="form-control select2" name="TracebilityID"
                                                    id="TracebilityID">
                                                    <option value="">Select</option>
                                                    <?php while ($RowCodeList = $StmtCodeList->fetch_array()) { ?>
                                                        <option <?php if ($RowCodeList['CLID'] == $ReportDetails['TracebilityID']) { ?>
                                                                selected="selected" <?php } ?>
                                                            value="<?php echo $RowCodeList['CLID'] ?>">
                                                            <?php echo $RowCodeList['Tracebility'] ?>
                                                        </option>
                                                    <?php } ?>
                                                    <select>
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputPassword1">Report Date</label>
                                                <input required type="date" class="form-control"
                                                    value="<?php echo $ReportDetails['ReportDate'] ?>" name="ReportDate"
                                                    id="ReportDate">
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Date of Sample Collection</label>
                                                <input type="date" class="form-control" name="SamplingDate"
                                                    id="SamplingDate" placeholder="dd/mm/yyyy"
                                                    value="<?php echo $ReportDetails['SamplingDate'] ?>">
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Time of Sample Collection</label>
                                                <input type="time" class="form-control" name="SamplingTime"
                                                    id="SamplingTime" placeholder="hh:mm"
                                                    value="<?php echo $date = date("H:i", strtotime($ReportDetails['SamplingTime'])); ?>">
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Quantity of Raw Material</label>
                                                <input type="text" class="form-control" name="SamplingQty"
                                                    id="SamplingQty" placeholder="Enter Quantity of Raw Material"
                                                    value="<?php echo $ReportDetails['SamplingQty'] ?>">
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Quantity of Finished Product</label>
                                                <input type="text" class="form-control" name="SamplingFinishedQty"
                                                    id="SamplingFinishedQty"
                                                    placeholder="Enter Quantity of Finished Product"
                                                    value="<?php echo $ReportDetails['SamplingFinishedQty'] ?>">
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Quantity of Pre-Harvest</label>
                                                <input type="text" class="form-control" name="SamplingPreharvestQty"
                                                    id="SamplingPreharvestQty"
                                                    placeholder="Enter Quantity of Pre-Harvest"
                                                    value="<?php echo $ReportDetails['SamplingPreharvestQty'] ?>">
                                            </div>


                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Sample id/code</label>
                                                <input type="text" class="form-control" name='SampleID' id="SampleID"
                                                    placeholder="Enter Production Codes"
                                                    value="<?php echo $ReportDetails['SampleID'] ?>">

                                            </div>
                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1"> Sample Quantity</label>
                                                <input type="text" class="form-control" name='SampleWeight'
                                                    id="SampleWeight" placeholder="Enter  Sample Quantity"
                                                    value="<?php echo $ReportDetails['SampleQuantity'] ?>">
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1"> Received Quantity</label>
                                                <input type="text" class="form-control" name='ReceivedQty'
                                                    id="ReceivedQty" placeholder="Enter  Received Qty in KGS"
                                                    value="<?php echo $ReportDetails['ReceivedQty'] ?>">
                                            </div>



                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1"> Finished Quantity</label>
                                                <input type="text" class="form-control" name='FinishedWeight'
                                                    id="FinishedWeight" placeholder="Enter  Finished Quantity in Kgs"
                                                    value="<?php echo $ReportDetails['FinishedWeight'] ?>">
                                            </div>

                                            <?php
                                            $HarvestingDate = str_replace('/', ' ', $ReportDetails['HarvestingDate']);
                                            $HarvestDate = $newDate = date('Y-m-d\TH:i', strtotime($HarvestingDate)); ?>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1"> Date & Time Of Harvest </label>
                                                <input type="datetime-local" class="form-control" name='HarvestingDate'
                                                    id="HarvestingDate" placeholder="Enter Date & Time Of Harvest"
                                                    value="<?php echo $HarvestDate; ?>">
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputPassword1"> Received Date </label>
                                                <input required type="date" value="<?php echo date("Y-m-d"); ?>"
                                                    class="form-control" name="ReceivedDate" id="ReceivedDate"
                                                    placeholder="Enter Received Date "
                                                    value="<?php echo $ReportDetails['ReceivedDate'] ?>">
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputPassword1">Date Of Analysis</label>
                                                <input type="date" class="form-control" name="AnalysisDate"
                                                    id="AnalysisDate" placeholder="Enter Date Of Sampling "
                                                    value="<?php echo $ReportDetails['AnalysisDate'] ?>">
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Date Of Issuance </label>
                                                <input type="date" class="form-control" name='DateIssuence'
                                                    id="DateIssuence" placeholder="Enter Production Codes"
                                                    value="<?php echo $ReportDetails['DateIssuence'] ?>">
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Name of the Representation/Technologist
                                                </label>
                                                <input type="text" class="form-control" name='LabRep' id="LabRep"
                                                    placeholder="Enter Production Codes"
                                                    value="<?php echo $ReportDetails['LabRep'] ?>">
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Tested By</label>
                                                <select class="form-control select2" name="TestedBy" id="TestedBy">
                                                    <?php while ($RowLab = $StmtLab->fetch_array()) { ?>
                                                        <option <?php if ($ReportDetails['TestedBy'] == $RowLab['LbuName']) { ?> selected="selected" <?php } ?>
                                                            value="<?php echo $RowLab['LUID'] ?>">
                                                            <?php echo $RowLab['LbuName'] ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Verified By </label>
                                                <select class="form-control select2" name="VerifiedBy" id="VerifiedBy">
                                                    <option value="">Select</option>
                                                    <?php while ($RowLab = $StmtLab1->fetch_array()) { ?>
                                                        <option <?php if ($ReportDetails['VerifiedBy'] == $RowLab['LbuName']) { ?> selected="selected" <?php } ?>
                                                            value="<?php echo $RowLab['LUID'] ?>">
                                                            <?php echo $RowLab['LbuName'] ?>
                                                        </option>

                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1"> Source </label>
                                                <input type="text" class="form-control" name='Source' id="Source"
                                                    value="<?php echo $ReportDetails['Source'] ?>" required>
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Yield </label>
                                                <input type="float" class="form-control" name='Yield' id="Yield"
                                                    value="<?php echo $ReportDetails['Yield'] ?>" required>
                                            </div>

                                        </div>
                                        <?php if ($IsShowQcAddOnFields == 1) { ?>
                                            <hr>
                                            <div class="row">
                                                <div class="form-group col-lg-3">
                                                    <label for="exampleInputPassword1">Record No</label>
                                                    <input type="text" class="form-control" name="RecordNo" id="RecordNo"
                                                        placeholder="Enter Record Number"
                                                        value="<?php echo $ReportDetails['RecordNo'] ?>">
                                                </div>
                                            </div>
                                        <?php } ?>


                                        <div class=" col-xs-12 box-footer" align="right">
                                            <a href="DocAddReports.php?Q=<?php echo $QueryParm ?>"
                                                class="btn btn-danger">Close</a>
                                            <button type="submit" name="submit"
                                                class="btn btn-success waves-effect waves-light">Update</button>
                                        </div>
                                </form>

                            </div>
                        </div>

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