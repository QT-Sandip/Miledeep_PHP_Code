<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");

$PageTitleFolder = "Master";
$PageTitle = "Add Freight Rates";

$ID = isset($_GET['id']) ? $_GET['id'] : "";
$PostedUserId = $_SESSION['UserID'];
$PageUrl = ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME));


$SqlCountries = "SELECT * FROM `ScrmCountriesList`";
$StmtCountries = $iconn->query($SqlCountries);

$SqlLinerMaster = "SELECT * FROM `ScrmLinerMaster`";
$StmtLinerMaster = $iconn->query($SqlLinerMaster);

$SqlScrmPortCities = "SELECT * FROM `ScrmPortCities`";
$StmtPortCities = $iconn->query($SqlScrmPortCities);

if ($ID != '') {
    $SqlFreightRates = "SELECT * FROM `ScrmFreightRates` WHERE ID='$ID'";
    $StmtFreightRates = $iconn->query($SqlFreightRates);
    $FreightRatesRow = $StmtFreightRates->fetch_assoc();

}

extract($_POST);
extract($_GET);

if (isset($_POST['submit'])) {
    $linerId = $_POST["liner_id"];
    $portId = $_POST["port_id"];
    $countryId = $_POST["country_id"];
    $fromDate = $_POST["from_date"];
    $toDate = $_POST["to_date"];
    $rateType = $_POST["rate_type"];
    $rate = $_POST["rate"];
    $pluginFD = $_POST["plugin_fd"];
    $dfd1 = $_POST["dfd1"];
    $dfd2 = $_POST["dfd2"];
    $thcAndDC = $_POST["thc_and_dc"];
    $genset = $_POST["genset"];
    $combined = $_POST["combined"];


    if ($ID == "") {  // New Exporter
        $sqlQuery = "INSERT INTO `ScrmFreightRates`(`Liner`, `DestinationPort`, `Country`, `FromDate`, `ToDate`,`RateType`, `Rate`, `PlugInFD`, `DFD1`, `DFD2`, `THCAndDC`, `Genset`, `Combined`) VALUES ( 
        '$linerId',
    '$portId',
    '$countryId',
    '$fromDate',
    '$toDate',
    '$rateType',
    '$rate',
    '$pluginFD',
    '$dfd1',
    '$dfd2',
    '$thcAndDC',
    '$genset',
    '$combined')";
        $statment = $iconn->query($sqlQuery);
        if ($statment) {
            $insertedFreightRateId = $iconn->insert_id;
            legacyAuditLogInsertById('ScrmFreightRates', 'ID', $insertedFreightRateId, $_POST, array(
                'ReferenceNo' => $insertedFreightRateId,
                'Remarks' => 'Freight rate created',
            ), 'Sales');

            // 14-02-2024
            echo '<script language="javascript" type="text/javascript"> 
            alert("Fright Rates added Successfully");
            window.location = "FreightRatesList.php";
            </script>';
            // header('Location: AddProcessor.php');
        }
    } else {  // Update the Exporter
        $existingFreightRate = legacyAuditFetchRowById('ScrmFreightRates', 'ID', $ID, 'Sales');
        $sqlQuery = "UPDATE `ScrmFreightRates` SET 
    `Liner` = '$linerId', 
    `DestinationPort` = '$portId', 
    `Country` = '$countryId', 
    `FromDate` = '$fromDate', 
    `ToDate` = '$toDate',
    `RateType`='$rateType',
    `Rate` = '$rate', 
    `PlugInFD` = '$pluginFD', 
    `DFD1` = '$dfd1', 
    `DFD2` = '$dfd2', 
    `THCAndDC` = '$thcAndDC', 
    `Genset` = '$genset', 
    `Combined` = '$combined' 
WHERE `ID` = '$ID'";
        $statment = $iconn->query($sqlQuery);
        if ($statment) {
            legacyAuditLogUpdateById('ScrmFreightRates', 'ID', $ID, $existingFreightRate, $_POST, array(
                'ReferenceNo' => $ID,
                'Remarks' => 'Freight rate updated',
            ), 'Sales');

            echo '<script language="javascript" type="text/javascript"> 
            alert("Fright Rates Updated Successfully");
            window.location = "FreightRatesList.php";
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

                                <form role="form" method="post" id="formAddFreightRates">
                                    <div class="card-body">

                                        <div class="row">

                                            <div class="form-group col-sm-3">
                                                <label for="liner_id">Liner<span style="color:red">*</span></label>
                                                <select required class="form-control select2" name="liner_id"
                                                    id="liner_id">
                                                    <option value=""> All </Option>
                                                    <?php while ($RowLinerMaster = $StmtLinerMaster->fetch_array()) { ?>
                                                        <option <?php if ($FreightRatesRow['Liner'] == $RowLinerMaster['LinerID']) { ?>
                                                                selected="selected" <?php } ?>
                                                            value="<?php echo $RowLinerMaster['LinerID']; ?>">
                                                            <?php echo $RowLinerMaster['LinerName']; ?>
                                                        </option>
                                                    <?php } ?>

                                                </select>
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="port_id">Destination Port<span
                                                        style="color:red">*</span></label>
                                                <select required class="form-control select2" name="port_id"
                                                    id="port_id">
                                                    <option value=""> All </Option>
                                                    <?php while ($RowPortCities = $StmtPortCities->fetch_array()) { ?>
                                                        <option <?php if ($FreightRatesRow['DestinationPort'] == $RowPortCities['PortId']) { ?> selected="selected" <?php } ?>
                                                            value="<?php echo $RowPortCities['PortId']; ?>">
                                                            <?php echo $RowPortCities['Name']; ?>
                                                        </option>
                                                    <?php } ?>

                                                </select>
                                            </div>
                                            <div class="form-group col-lg-3">
                                                <label for="country_id">Select Country<span
                                                        style="color:red">*</span></label>
                                                <select required class="form-control select2" name="country_id"
                                                    id="country_id">
                                                    <option value=""> All </Option>
                                                    <?php while ($RowCountries = $StmtCountries->fetch_array()) { ?>
                                                        <option <?php if ($FreightRatesRow['Country'] == $RowCountries['id']) { ?> selected="selected" <?php } ?>
                                                            value="<?php echo $RowCountries['id']; ?>">
                                                            <?php echo $RowCountries['name']; ?>
                                                        </option>
                                                    <?php } ?>

                                                </select>
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="from_date">From Date <span
                                                        style="color:red">*</span></label>
                                                <input required type="date" class="form-control" name="from_date"
                                                    id="from_date"
                                                    value="<?php funPrintValue($FreightRatesRow['FromDate']) ?>" />
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="to_date">To Date <span style="color:red">*</span></label>
                                                <input required type="date" class="form-control" name="to_date"
                                                    id="to_date"
                                                    value="<?php funPrintValue($FreightRatesRow['ToDate']) ?>" />
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="rate_type">Rate Type <span
                                                        style="color:red">*</span></label>
                                                <select required class="form-control" name="rate_type" id="rate_type">
                                                    <option value="1" <?php if (isset($FreightRatesRow['RateType']) && $FreightRatesRow['RateType'] == 1)
                                                        echo 'selected'; ?>>Contract</option>
                                                    <option value="0" <?php if (isset($FreightRatesRow['RateType']) && $FreightRatesRow['RateType'] == 0)
                                                        echo 'selected'; ?>>Spot</option>
                                                </select>
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="rate"> Rate <span style="color:red">*</span></label>
                                                <input required type="number" class="form-control" name="rate" id="rate"
                                                    placeholder="Enter Rate"
                                                    value="<?php funPrintValue($FreightRatesRow['Rate']) ?>" />
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="plugin_fd">PlugIn FD <span
                                                        style="color:red">*</span></label>
                                                <input required type="number" class="form-control" name="plugin_fd"
                                                    id="plugin_fd" placeholder="Enter PlugIn FD"
                                                    value="<?php funPrintValue($FreightRatesRow['PlugInFD']) ?>" />
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="combined">Combined <span style="color:red">*</span></label>
                                                <select required class="form-control" name="combined" id="combined">
                                                    <option value="1" <?php if (isset($FreightRatesRow['Combined']) && $FreightRatesRow['Combined'] == 1)
                                                        echo 'selected'; ?>>Yes</option>
                                                    <option value="0" <?php if (isset($FreightRatesRow['Combined']) && $FreightRatesRow['Combined'] == 0)
                                                        echo 'selected'; ?>>No</option>
                                                </select>
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="dfd1">DFD1 <span style="color:red">*</span></label>
                                                <input required type="number" class="form-control" name="dfd1" id="dfd1"
                                                    placeholder="Enter DFD1"
                                                    value="<?php funPrintValue($FreightRatesRow['DFD1']) ?>" />
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="dfd2">DFD2 <span style="color:red"></span></label>
                                                <input required type="number" class="form-control" name="dfd2" id="dfd2"
                                                    placeholder="Enter DFD2"
                                                    value="<?php funPrintValue($FreightRatesRow['DFD2']) ?>" />
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="thc_and_dc">THC and DC <span
                                                        style="color:red">*</span></label>
                                                <input required type="number" class="form-control" name="thc_and_dc"
                                                    id="thc_and_dc" placeholder="Enter THC and DC"
                                                    value="<?php funPrintValue($FreightRatesRow['THCAndDC']) ?>" />
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="genset">Genset <span style="color:red">*</span></label>
                                                <input required type="text" class="form-control" name="genset"
                                                    id="genset" placeholder="Enter Genset Info"
                                                    value="<?php funPrintValue($FreightRatesRow['Genset']) ?>" />
                                            </div>

                                        </div>

                                        <div class="col-xs-12 box-footer text-right">
                                            <a target="" href="FreightRatesList.php" class="btn btn-danger"> Close</a>
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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"
        type="text/javascript"></script>

    <script>
        jQuery.validator.addMethod('selectcheck', function (value) {
            return (value != '0' || value != '');
        }, "year required");
        jQuery.validator.addMethod("noSpace", function (value, element) {
            return value == '' || value.trim().length != 0;
        }, "No space please and don't leave it empty");

        $.validator.addMethod("greaterThanFromDate", function (value, element) {
            var fromDate = $('#from_date').val();
            return Date.parse(value) >= Date.parse(fromDate);
        }, "To Date must be greater than or equal to From Date.");
        $(document).ready(function () {


            $("#formAddFreightRates").validate({
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
                    liner_id: {
                        required: true
                    },
                    port_id: {
                        required: true
                    },
                    country_id: {
                        required: true
                    },
                    from_date: {
                        required: true,
                        date: true
                    },
                    to_date: {
                        required: true,
                        date: true
                    },
                    rate: {
                        required: true,
                        number: true
                    },
                    plugin_fd: {
                        required: true,
                        number: true
                    },
                    dfd1: {
                        required: true,
                        number: true
                    },
                    thc_and_dc: {
                        required: true,
                        number: true
                    },
                    genset: {
                        required: true
                    },
                    combined: {
                        required: true
                    },
                    rate_type: {
                        required: true
                    }
                },
                messages: {
                    liner_id: {
                        required: "Please select a liner."
                    },
                    port_id: {
                        required: "Please select a destination port."
                    },
                    country_id: {
                        required: "Please select a country."
                    },
                    from_date: {
                        required: "Please select the from date.",
                        date: "Please enter a valid date."
                    },
                    to_date: {
                        required: "Please select the to date.",
                        date: "Please enter a valid date."
                    },
                    rate: {
                        required: "Please enter the Spot Rate.",
                        number: "Please enter a valid number."
                    },
                    plugin_fd: {
                        required: "Please enter the PlugIn FD.",
                        number: "Please enter a valid number."
                    },
                    dfd1: {
                        required: "Please enter the DFD1.",
                        number: "Please enter a valid number."
                    },
                    dfd2: {
                        required: "Please enter the DFD2.",
                        number: "Please enter a valid number."
                    },
                    thc_and_dc: {
                        required: "Please enter THC and DC.",
                        number: "Please enter a valid number."
                    },
                    genset: {
                        required: "Please enter the Genset info."
                    },
                    combined: {
                        required: "Please select Yes or No for Combined."
                    }
                },
                errorPlacement: function () {
                    return false; // suppresses error message text
                },
                submitHandler: function () {
                    return true;
                }
            });

        });
    </script>


</body>

</html>
