<?php

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");

$PageTitleFolder = "Master";
$PageTitle = "Add Processor";

$ID = isset($_GET['id']) ? $_GET['id'] : "";
$PostedUserId = $_SESSION['UserID'];
$PageUrl = ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME));


$SqlCountries = "SELECT * FROM `ScrmCountriesList`";
$StmtCountries = $iconn->query($SqlCountries);

if (isset($_GET['id'])) { // In Update Mode
    $sqlGetExporterDetails = "SELECT * FROM `AdminExporterDetails` WHERE ExpID = " . $ID . " LIMIT 1";
    $GetExporterDetails = $iconn->query($sqlGetExporterDetails);
    $results = $GetExporterDetails->fetch_array();
}

// $SqlChargesList="SELECT * FROM `ScrmMiscCharges` "; 
// $StmtChargesList=$iconn->query($SqlChargesList);

extract($_POST);
extract($_GET);

if (isset($_POST['submit'])) {
    $processorType = "2";
    $processorName = $_POST["processorName"];
    $address = $_POST["address"];
    $countryID = $_POST["countryID"];
    $city = $_POST["city"];
    $zipCode = $_POST["zipCode"];
    $phone = $_POST["phone"];
    $fax = $_POST["fax"];
    $usFdaNo = $_POST["usFdaNo"];
    $approvalNo = $_POST["approvalNo"];
    $ieCode = $_POST["ieCode"];
    $invShortCode = $_POST["invShortCode"];
    $gstin = $_POST["GSTIN"];
    $pan = $_POST["pan"];
    $authSign = $_POST["authSign"];
    $processorBAPNo = $_POST["processorBAPNo"];

    if ($ID == "") {  // New Exporter
        $sqlQuery = "INSERT INTO `AdminExporterDetails`(`ExpType`, `ExpName`, `ExpAddress`, `ExpCountry`, `ExpCity`, `ExpZipCode`, `ExpPhoneNumber`, `ExpFax`, `ExpUsfdaNo`, 
        `ExpApprovalNo`, `ExpIeCode`, `InvShortCode`, `ExpGstNo`,`ExpPanNumber`, `AuthSign`, `ProcessorBAPNo`) 
                                    VALUES ('$processorType','$processorName','$address','$countryID','$city','$zipCode','$phone','$fax',
                                    '$usFdaNo','$approvalNo','$ieCode','$invShortCode','$gstin', '$pan', '$authSign', '$processorBAPNo')";
        $statment = $iconn->query($sqlQuery);
        if ($statment) {
            $insertedProcessorId = $iconn->insert_id;
            legacyAuditLogInsertById('AdminExporterDetails', 'ExpID', $insertedProcessorId, $_POST, array(
                'ReferenceNo' => $insertedProcessorId,
                'Remarks' => 'Processor created',
            ), 'Sales');

            // 14-02-2024
            echo '<script language="javascript" type="text/javascript"> 
            alert("Processor Posted Successfully");
            window.location = "ProcessorDetails.php";
            </script>';
            // header('Location: AddProcessor.php');
        }
    } else {  // Update the Exporter
        $existingProcessor = legacyAuditFetchRowById('AdminExporterDetails', 'ExpID', $ID, 'Sales');
        $sqlQuery = "UPDATE `AdminExporterDetails` SET `ExpType`='$processorType', `ExpName`='$processorName', `ExpAddress`='$address', `ExpCountry`='$countryID',
					`ExpCity`='$city', `ExpZipCode`='$zipCode',`ExpPhoneNumber`='$phone',`ExpFax`='$fax', `ExpUsfdaNo`='$usFdaNo',
                     `ExpApprovalNo`='$approvalNo', `ExpIeCode`='$ieCode', `InvShortCode`='$invShortCode', `ExpGstNo`='$gstin', 
                     `ExpPanNumber`='$pan', `AuthSign` = '$authSign', `ProcessorBAPNo` = '$processorBAPNo'
					WHERE `ExpID`='$ID'";
        $statment = $iconn->query($sqlQuery);
        if ($statment) {
            legacyAuditLogUpdateById('AdminExporterDetails', 'ExpID', $ID, $existingProcessor, $_POST, array(
                'ReferenceNo' => $ID,
                'Remarks' => 'Processor updated',
            ), 'Sales');

            // 14-02-2024
            // echo '<script language="javascript" type="text/javascript">
            //         alert("Processor Updated Successfully");
            //     </script>';

            $ArgumentsArray[PostedUserId] = $PostedUserId;
            $ArgumentsArray[PageUrl] = $PageUrl;
            $ArgumentsArray[Remarks] = $Remarks;
            $ArgumentsArray[ReferenceID] = $ID;
            postUserEditLog($ArgumentsArray);

            echo '<script language="javascript" type="text/javascript"> 
            alert("Processor Updated Successfully");
            window.location = "ProcessorDetails.php";
            </script>';

            // header('Location: ProcessorDetails');

        }
    }
}

// check weather value exits or not 
function funPrintValue($key)
{
    echo (isset($key) && $key != "") ? $key : "";
}

// if(isset($_POST['submit'])){
// 	$PostedDate=date("Y-m-d");

// 	if($ID==''){
// 	 $sql_org_insert="INSERT INTO `ScrmMiscCharges`(`CountryID`, `ClearanceCharge`, `AntiDumpingCharge`, `AmcChargePerc`, `ApplicableDate`, `GeneralType`, `ValidUpTo`, `MerchandiseProcessFee`, `USClearance`, `HarbourMaintenanceFee`, `DrawbackRateperKG`, `DrawbackRateperPercentage`, `ISFStaticDuty`, `ServiceTaxPercentage`, `PremiumRate`, `BLServiceTax`, `BLSWCES`, `PostedDate`) VALUES ('$CountryID','$ClearanceCharge','$AntiDumpingCharge','$AmcChargePerc','$ApplicableDate','$GeneralType','$ValidUpTo','$MerchandiseProcessFee','$USClearance','$HarbourMaintenanceFee','$DrawbackRateperKG','$DrawbackRateperPercentage','$ISFStaticDuty','$ServiceTaxPercentage','$PremiumRate','$BLServiceTax','$BLSWCES','$PostedDate')";


// 	//$stmt1=$iconn->query($sql_org_insert);

// 	/*if($stmt1){

// 		header('Location: AddMiscCharges.php');
// 	}*/
// }

// else{
// 	$sql_org_insert="UPDATE `ScrmMiscCharges` SET `CountryID`='$CountryID',`ClearanceCharge`='$ClearanceCharge',`AntiDumpingCharge`='$AntiDumpingCharge',`AmcChargePerc`='$AmcChargePerc',`ApplicableDate`='$ApplicableDate',`GeneralType`='$GeneralType',`ValidUpTo`='$ValidUpTo',`MerchandiseProcessFee`='$MerchandiseProcessFee',
// 	`USClearance`='$USClearance',`HarbourMaintenanceFee`='$HarbourMaintenanceFee',`DrawbackRateperKG`='$DrawbackRateperKG',`DrawbackRateperPercentage`='$DrawbackRateperPercentage',`ISFStaticDuty`='$ISFStaticDuty',`ServiceTaxPercentage`='$ServiceTaxPercentage',`PremiumRate`='$PremiumRate',`BLServiceTax`='$BLServiceTax',`BLSWCES`='$BLSWCES',
// 	`PostedDate`='$PostedDate' WHERE `ID`='$ID'";
// }

// 	$stmt1=$iconn->query($sql_org_insert);

// 	if($stmt1){

// 		header('Location: MiscChargesList.php');
// 	}

// }

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
    </style>

    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">

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

                    <?php if ($Status != '') {     //Show Alert  	
                    ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="mdi mdi-check-all mr-2"></i>
                            <?php
                            echo $Status;
                            $_SESSION['Status'] = '';
                            ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php } ?>

                    <?php include($IncludesPath . "page-title.php"); ?>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">

                                <form role="form" method="post" id="frmAddProcessor">
                                    <div class="card-body">

                                        <div class="row">

                                            <div class="form-group col-sm-3">
                                                <label for="processorName">Processor Name<span style="color:red">*</span></label>
                                                <input required type="text" step="any" class="form-control" name='processorName' id="processorName" placeholder="Enter Processor name" value="<?php funPrintValue($results["ExpName"]) ?>" />
                                            </div>


                                            <div class="form-group col-sm-3">
                                                <label for="address">Address<span style="color:red">*</span></label>
                                                <textarea required type="text" step="any" class="form-control" name='address' id="address" placeholder="Enter Address"><?php echo funPrintValue($results["ExpAddress"]) ?></textarea>
                                            </div>
                                            <div class="form-group col-lg-3">
                                                <label for="countryID">Select Country<span style="color:red">*</span></label>
                                                <select required class="form-control select2" name="countryID" id="countryID">
                                                    <option value=""> All </Option>
                                                    <?php while ($RowCountries = $StmtCountries->fetch_array()) { ?>
                                                        <option <?php if ($results['ExpCountry'] == $RowCountries['name']) { ?> selected="selected" <?php } ?> value="<?php echo $RowCountries['name']; ?>">
                                                            <?php echo $RowCountries['name']; ?></option>
                                                    <?php } ?>

                                                    <select>
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="city">City<span style="color:red">*</span></label>
                                                <input required type="text" step="any" class="form-control" name='city' id="city" placeholder="Enter City" value="<?php funPrintValue($results["ExpCity"]) ?>" />
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="zipCode">Zip Code<span style="color:red">*</span></label>
                                                <input required type="text" step="any" class="form-control" name='zipCode' id="zipCode" placeholder="Enter Zip Code" value="<?php funPrintValue($results["ExpZipCode"]) ?>" />
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="phone">Phone</label>
                                                <input type="text" step="any" class="form-control" name='phone' id="phone" placeholder="Enter Phone" value="<?php funPrintValue($results["ExpPhoneNumber"]) ?>" />
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="fax">Fax</label>
                                                <input type="text" class="form-control" name='fax' id="fax" placeholder="Enter Fax" value="<?php funPrintValue($results["ExpFax"]) ?>" />
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="usFdaNo">US FDA REG. No.<span style="color:red">*</span></label>
                                                <input required type="text" step="any" class="form-control" name='usFdaNo' id="usFdaNo" placeholder="Enter US FDA REG. No." value="<?php funPrintValue($results["ExpUsfdaNo"]) ?>" />
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="approvalNo">EIA No<span style="color:red">*</span></label>
                                                <input required type="text" class="form-control" name='approvalNo' id="approvalNo" placeholder="Enter EIA No" value="<?php funPrintValue($results["ExpApprovalNo"]) ?>" />
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="ieCode">IE CODE</label>
                                                <input type="text" class="form-control" step="any" name='ieCode' id="ieCode" placeholder="Enter IE CODE" value="<?php funPrintValue($results["ExpIeCode"]) ?>" />
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="invShortCode">Invoice Code<span style="color:red">*</span></label>
                                                <input required type="text" step="any" class="form-control" name='invShortCode' id="invShortCode" placeholder="Enter Invoice Code" value="<?php funPrintValue($results["InvShortCode"]) ?>" />
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="GSTIN">GSTIN</label>
                                                <input type="text" step="any" class="form-control" name='GSTIN' id="GSTIN" placeholder="Enter GSTIN" value="<?php funPrintValue($results["ExpGstNo"]) ?>" />
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="pan">PAN</label>
                                                <input type="text" step="any" class="form-control" name='pan' id="pan" placeholder="Enter PAN no." value="<?php funPrintValue($results["ExpPanNumber"]) ?>" />
                                            </div>
                                            <div class="form-group col-sm-3">
                                                <label for="bap">BAP of PLANT</label>
                                                <input type="text" step="any" class="form-control" name='processorBAPNo' id="processorBAPNo" placeholder="Enter BAP" value="<?php funPrintValue($results["ProcessorBAPNo"]) ?>" />
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="invoiceCode">Auth Sign</label>
                                                <input type="text" step="any" class="form-control" name='authSign' id="authSign" placeholder="Enter Auth Sign" value="<?php funPrintValue($results["AuthSign"]) ?>" />
                                            </div>


                                        </div>

                                        <div class="col-xs-12 box-footer text-right">
                                            <a target="" href="ProcessorDetails.php" class="btn btn-danger"> Close</a>
                                            <button type="submit" name='submit' class="btn btn-success">
                                                <?php
                                                if ($ID == '') {
                                                    echo "Save";
                                                } else {
                                                    echo "Update";
                                                }
                                                ?>
                                            </button>
                                        </div>
                                    </div>
                                </form>
                                <!-- end col -->
                            </div> <!-- end card -->
                        </div> <!-- end col-->
                    </div> <!-- end row -->

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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js" type="text/javascript"></script>

    <script>
        jQuery.validator.addMethod('selectcheck', function(value) {
            return (value != '0' || value != '');
        }, "year required");
        jQuery.validator.addMethod("noSpace", function(value, element) {
            return value == '' || value.trim().length != 0;
        }, "No space please and don't leave it empty");
        $(document).ready(function() {
            $("#frmAddProcessor").validate({
                onkeyup: false,
                onfocusout: false,
                invalidHandler: function(form, validator) {
                    var errors = validator.numberOfInvalids();
                    if (errors) {
                        var firstInvalidElement = $(validator.errorList[0].element);
                        //$('html,body').scrollTop(firstInvalidElement.offset().top - 100);
                        firstInvalidElement.focus();
                    }
                },
                ignore: "",
                rules: {
                    processorName: {
                        required: true,
                        noSpace: true
                    },
                    address: {
                        required: true,
                        noSpace: true
                    },
                    countryID: {
                        selectcheck: true
                    },
                    city: {
                        required: true,
                        noSpace: true
                    },
                    zipCode: {
                        required: true,
                        noSpace: true
                    },
                    phone: {
                        // required: true,
                        noSpace: true,
                        number: true
                    },
                    //fax: { required: true, noSpace: true },
                    usFdaNo: {
                        required: true,
                        noSpace: true
                    },
                    approvalNo: {
                        required: true,
                        noSpace: true
                    },
                    // ieCode: {
                    //     required: true,
                    //     noSpace: true
                    // },
                    invShortCode: {
                        required: true,
                        noSpace: true
                    },
                    // GSTIN: {
                    //     required: true,
                    //     noSpace: true
                    // },
                    // pan: {
                    //     required: true,
                    //     noSpace: true
                    // }
                    //authSign: { required: true, noSpace: true }
                },
                errorPlacement: function() {
                    return false; // suppresses error message text
                },
                submitHandler: function() {
                    return true;
                }
            });
        });
    </script>


</body>

</html>
