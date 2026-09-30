<?php

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");



$PageTitleFolder = "Master";
$PageTitle = "Bank Details";


$BankID = $_GET['id'];
$PostedUserId = $_SESSION['UserID'];
$PageUrl = ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME));


$SqlExporter = "SELECT * FROM `AdminExporterDetails` WHERE ExpType=1";
$StmtExporter = $iconn->query($SqlExporter);


$SqlBank = "SELECT * FROM `ScrmBankDetailsMaster` WHERE ID='$BankID'";
$StmtBank = $iconn->query($SqlBank);
$RowBank = $StmtBank->fetch_array();
$Count = mysqli_num_rows($StmtBank);

extract($_POST);
extract($_GET);

$ValidationError = '';

if (isset($_POST['submit'])) {
    $IFSC = strtoupper(trim(isset($_POST['IFSC']) ? $_POST['IFSC'] : ''));
    $MICR = trim(isset($_POST['MICR']) ? $_POST['MICR'] : '');

    if ($IFSC !== '' && !preg_match('/^[A-Z]{4}0[A-Z0-9]{6}$/', $IFSC)) {
        $ValidationError = 'Enter a valid 11-character IFSC code (for example, SBIN0123456).';
    } elseif ($MICR !== '' && !preg_match('/^[0-9]{9}$/', $MICR)) {
        $ValidationError = 'MICR must contain exactly 9 digits.';
    }

    $IFSC = $iconn->real_escape_string($IFSC);
    $MICR = $iconn->real_escape_string($MICR);

    if ($ValidationError == '') {
        if ($Count == 0) {
            $sql_org_insert = "
	 INSERT INTO `ScrmBankDetailsMaster`(`ExpID`, `AccntName`, `AccountNumber`, `BankName`, `BankAddress`, `SwiftCode`, `IFSC`, `MICR`, `ABA_Numnber`, `BankDetails`, `RoutingThrough`, `NegotiatingBank`, `BankStatus`) 
      VALUES ('$ExpID','$AccntName','$AccountNumber','$BankName','$BankAddress','$SwiftCode','$IFSC','$MICR','$ABA_Numnber', '$BankDetails','$RoutingThrough','$NegotiatingBank','1')";

            $stmt1 = $iconn->query($sql_org_insert);

            if ($stmt1) {
                $insertedBankId = $iconn->insert_id;
                legacyAuditLogInsertById('ScrmBankDetailsMaster', 'ID', $insertedBankId, $_POST, array(
                    'ReferenceNo' => $insertedBankId,
                    'Remarks' => 'Bank details created',
                ), 'Sales');

                header('Location: BanksList.php');
            }

        } else {
            $existingBank = legacyAuditFetchRowById('ScrmBankDetailsMaster', 'ID', $BankID, 'Sales');
            $sql_org_insert = "UPDATE `ScrmBankDetailsMaster` SET `ExpID`='$ExpID',`AccntName`='$AccntName',`AccountNumber`='$AccountNumber',
    `BankName`='$BankName',`BankAddress`='$BankAddress',`SwiftCode`='$SwiftCode',`IFSC`='$IFSC',`MICR`='$MICR',`ABA_Numnber`='$ABA_Numnber',`BankDetails`='$BankDetails',
    `RoutingThrough`='$RoutingThrough',`NegotiatingBank`='$NegotiatingBank' WHERE
	`ID`='$BankID'";

            $stmt1 = $iconn->query($sql_org_insert);

            if ($stmt1) {
                legacyAuditLogUpdateById('ScrmBankDetailsMaster', 'ID', $BankID, $existingBank, $_POST, array(
                    'ReferenceNo' => $BankID,
                    'Remarks' => 'Bank details updated',
                ), 'Sales');

                $ArgumentsArray[PostedUserId] = $PostedUserId;
                $ArgumentsArray[PageUrl] = $PageUrl;
                $ArgumentsArray[Remarks] = $Remarks;
                $ArgumentsArray[ReferenceID] = $BankID;
                postUserEditLog($ArgumentsArray);

                header('Location: BanksList.php');
            }

        }


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
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="mdi mdi-check-all mr-2"></i>
                            <?php echo $Status;
                            $_SESSION['Status'] = ''; ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                    <?php } ?>

                    <?php if ($ValidationError != '') { ?>
                        <div class="alert alert-danger" role="alert">
                            <?php echo htmlspecialchars($ValidationError, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php } ?>

                    <?php include($IncludesPath . "page-title.php"); ?>




                    <div class="row">
                        <div class="col-12">
                            <div class="card">

                                <form role="form" method="post">
                                    <div class="card-body">

                                        <div class="row">

                                            <div class="form-group col-lg-3">
                                                <label for="exampleInputPassword1">Select Exporter<span
                                                        style="color:red">*</span></label>
                                                <select class="form-control select2" name="ExpID" id="ExpID">
                                                    <option value=""> Select </option>

                                                    <?php while ($RowExporter = $StmtExporter->fetch_array()) { ?>

                                                        <option <?php if ($RowExporter['ExpID'] == $RowBank['ExpID']) { ?>
                                                                selected=="selected" <?php } ?>
                                                            Value="<?php echo $RowExporter['ExpID']; ?>">
                                                            <?php echo $RowExporter['ExpName']; ?>
                                                        </OPTION>

                                                    <?php } ?>

                                                    <select>
                                            </div>
                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Account Name<span
                                                        style="color:red">*</span></label>
                                                <input required type="text" class="form-control" name='AccntName'
                                                    id="AccntName" placeholder="Enter Account Name"
                                                    value="<?php echo $RowBank['AccntName'] ?>">

                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Account Number<span
                                                        style="color:red">*</span></label>
                                                <input required type="text" class="form-control" name='AccountNumber'
                                                    id="AccountNumber" placeholder="Enter Account Number"
                                                    value="<?php echo $RowBank['AccountNumber'] ?>">

                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Bank Name<span
                                                        style="color:red">*</span></label>
                                                <input required type="text" class="form-control" name='BankName'
                                                    id="BankName" placeholder="Enter Bank Name"
                                                    value="<?php echo $RowBank['BankName'] ?>">

                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Bank Address<span
                                                        style="color:red">*</span></label>
                                                <input required type="text" class="form-control" name='BankAddress'
                                                    id="BankAddress" placeholder=" Enter Bank Address"
                                                    value="<?php echo $RowBank['BankAddress'] ?>">
                                            </div>



                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Swift Code <span
                                                        style="color:red">*</span></label>
                                                <input required type="text" class="form-control" name='SwiftCode'
                                                    id="SwiftCode" placeholder=" Enter Swift Code"
                                                    value="<?php echo $RowBank['SwiftCode'] ?>">
                                            </div>





                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">ABA Number<span
                                                        style="color:red">*</span></label>
                                                <input required type="text" class="form-control" name='ABA_Numnber'
                                                    id="ABA_Numnber" placeholder="Enter ABA Number"
                                                    value="<?php echo $RowBank['ABA_Numnber'] ?>">

                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="IFSC">IFSC</label>
                                                <input type="text" class="form-control text-uppercase" name="IFSC"
                                                    id="IFSC" maxlength="11" pattern="[A-Za-z]{4}0[A-Za-z0-9]{6}"
                                                    title="Enter a valid 11-character IFSC code"
                                                    placeholder="Enter IFSC"
                                                    value="<?php echo htmlspecialchars(isset($_POST['IFSC']) ? $_POST['IFSC'] : (isset($RowBank['IFSC']) ? $RowBank['IFSC'] : ''), ENT_QUOTES, 'UTF-8'); ?>">
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="MICR">MICR</label>
                                                <input type="text" class="form-control" name="MICR" id="MICR"
                                                    maxlength="9" inputmode="numeric" pattern="[0-9]{9}"
                                                    title="Enter exactly 9 digits" placeholder="Enter MICR"
                                                    value="<?php echo htmlspecialchars(isset($_POST['MICR']) ? $_POST['MICR'] : (isset($RowBank['MICR']) ? $RowBank['MICR'] : ''), ENT_QUOTES, 'UTF-8'); ?>">
                                            </div>


                                            <div class="form-group col-lg-6">
                                                <label for="exampleInputEmail1">Bank Details<span
                                                        style="color:red">*</span></label>

                                                <textarea required name="BankDetails" class="form-control" rows="2"
                                                    cols="50" id="BankDetails"
                                                    placeholder="Enter Bank Details"><?php echo $RowBank['BankDetails'] ?></textarea>


                                            </div>
                                            <div class="form-group col-lg-6">
                                                <label for="exampleInputEmail1">Routing Through<span
                                                        style="color:red">*</span></label>

                                                <textarea required name="RoutingThrough" class="form-control" rows="2"
                                                    cols="50" id="RoutingThrough"
                                                    placeholder="Enter Routing Through"><?php echo $RowBank['RoutingThrough'] ?></textarea>


                                            </div>
                                            <div class="form-group col-lg-6">
                                                <label for="exampleInputEmail1">Negotiating Bank<span
                                                        style="color:red">*</span></label>

                                                <textarea required name="NegotiatingBank" class="form-control" rows="2"
                                                    cols="50" id="NegotiatingBank"
                                                    placeholder="Enter Negotiating Bank"><?php echo $RowBank['NegotiatingBank'] ?></textarea>


                                            </div>
                                        </div>



                                        <div class="col-xs-12 box-footer" align="right">
                                            <?php if ($Count == 0) { ?>
                                                <a target="" href="BanksList.php" class="btn btn-danger  "> Close</a>

                                                <button type="submit" name='submit' class="btn btn-success">Add</button>
                                            <?php } else { ?>
                                                <a target="" href="BanksList.php" class="btn btn-danger  "> Close</a>

                                                <button type="submit" name='submit' class="btn btn-success">Update</button>
                                            <?php } ?>

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