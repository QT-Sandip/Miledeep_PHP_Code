<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($_SERVER['DOCUMENT_ROOT'] . "/includes/Util.php");

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';


$api = API_EXPORTS_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", "");

$resFreezingType = $resDropdownsAPI->Data->FreezingType;

$apiList = API_NEW_ADDON_FIELDS_FOR_SYSCO;
$QueryParms = $_GET['Q'];

if ($QueryParms != '') {
    $QueryParms = urlDecode64($QueryParms);
    $queries = array();
    parse_str($QueryParms, $_GET);
}


$PoID = $_GET['PoID'];

$bodyContent = array(
    "PoID" => $PoID
);


if ($PoID != '') {
    $result = CallAPI($apiList, "GET", "", $bodyContent);
    // echo json_encode($result, JSON_PRETTY_PRINT);
    // exit;
    $Data = $result->OptionsData[0];
}
// var_dump($Data);
?>

<!doctype html>
<html lang="en">

<head>

    <?php include_once ROOT . '/includes/head.php' ?>
    <style>
        /* .select2-container--open {
                z-index: 9999999
            }             */
    </style>

</head>

<?php include_once ROOT . '/includes/body-preloader.php' ?>

<!-- Begin page -->
<div id="layout-wrapper">

    <?php include_once ROOT . '/includes/menu.php' ?>

    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <!-- ============================================================== -->
    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">

                <?php include_once ROOT . '/includes/page-header.php' ?>
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <div class="card">
                            <div class="card-body">
                                <form role="form" method="post" id="frmCountry">
                                    <div class="row">

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtLotCode" class="form-label">Lot Code</label>
                                            <input type="text" class="form-control" id="txtLotCode"
                                                name="txtLotCode" placeholder="Enter Lot Code"
                                                value="<?php echo $Data->LotCode ?>">
                                        </div>
                                        
                                        <div class="col col-md-3 mb-3">
                                            <label for="txtFactoryId" class="form-label">Factory Id</label>
                                            <input type="text" class="form-control" id="txtFactoryId"
                                                name="txtFactoryId" placeholder="Enter Factory Id"
                                                value="<?php echo $Data->FactoryID ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtShipmentDate" class="form-label">Shipment Date</label>
                                            <input type="Date" class="form-control" id="txtShipmentDate"
                                                name="txtShipmentDate" placeholder="Enter Shipment Date"
                                                value="<?php echo $Data->ShipmentDate ?>">
                                        </div>
                                        <div class="col col-md-3 mb-3">
                                            <label for="txtRequestSentDate" class="form-label">Request Sent Date
                                            </label>
                                            <input type="Date" class="form-control" id="txtRequestSentDate"
                                                name="txtRequestSentDate" value="<?php echo $Data->RequestSentDate ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtSampleSendingDate" class="form-label">Sample Sending
                                                Date</label>
                                            <input type="Date" class="form-control" id="txtSampleSendingDate"
                                                name="txtSampleSendingDate"
                                                placeholder="Enter the Reporting Expection Date"
                                                value="<?php echo $Data->SampleSendingDate ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtReportExpectingDate" class="form-label">Report Expecting
                                                Date</label>
                                            <input type="Date" class="form-control" id="txtReportExpectingDate"
                                                name="txtReportExpectingDate" placeholder="Enter Report Expecting Date"
                                                value="<?php echo $Data->ReportExpectingDate ?>">
                                        </div> 
                                        <div class="col col-md-3 mb-3">
                                            <label for="txtSGSRequestDate" class="form-label">SGS Request Date(Letter)
                                                </label>
                                            <input type="Date" class="form-control" id="txtSGSRequestDate"
                                                name="txtSGSRequestDate" placeholder=""
                                                value="<?php echo $Data->SGSRequestDate ?>">
                                        </div>
                                        
                                        <div class="col col-md-3 mb-3">
                                            <label for="txtSimpSentDate" class="form-label">Simp Sent Date
                                                </label>
                                            <input type="Date" class="form-control" id="txtSimpSentDate"
                                                name="txtSimpSentDate" placeholder=""
                                                value="<?php echo $Data->SimpSentDate ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpStatus">Status</label>
                                            <select class="drpSelect" name="drpStatus" id="drpStatus">
                                                
                                                <option value="" selected>Select</option>
                                                <option <?php echo ($Data->Status == "Pending") ? 'selected' : ''; ?>
                                                    value="Pending">Pending</option>
                                                <option <?php echo ($Data->Status == "Active") ? 'selected' : ''; ?>
                                                    value="Active">Active</option>
                                                <option <?php echo ($Data->Status == "Waiting for Inspection") ? 'selected' : ''; ?>
                                                    value="Waiting for Inspection">Waiting for Inspection</option>
                                                <option <?php echo ($Data->Status == "Not Selected") ? 'selected' : ''; ?>
                                                    value="Not Selected">Not Selected</option>
                                                <option <?php echo ($Data->Status == "Inspection Complete Pass") ? 'selected' : ''; ?>
                                                    value="Inspection Complete Pass">Inspection Complete Pass
                                                </option>
                                                <option <?php echo ($Data->Status == "Fail") ? 'selected' : ''; ?>
                                                    value="Fail">Fail</option>

                                               
                                            </select>
                                        </div>

                                        <div class="text-center">
                                            <input type="hidden" id="hdnSaveType" name="hdnSaveType"
                                                value="<?php echo $PoID ?>" />
                                            <input type="hidden" id="hdnNewVariety" name="hdnNewVariety" />
                                            <a href="DocAddReports.php?PoID=<?php echo $PoID; ?>"
                                                class="btn btn-danger">Close</a>
                                            <?php if ($PoID == '') { ?>
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
                        <!-- <div class="card">
                            <div class="card-body">
                                <div id="divDataList">

                                </div>
                            </div>
                        </div> -->
                    </div>

                </div> <!-- container-fluid -->
            </div>
            <!-- End Page-content -->
            <?php include_once ROOT . '/includes/footer.php' ?>

        </div>
        <!-- end main content-->
        <!-- Modal -->

    </div>
    <!-- END layout-wrapper -->

    <?php include_once ROOT . '/includes/common-js.php' ?>

    <!-- Required datatable js -->
    <script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

    <?php include_once ROOT . '/includes/app-js.php' ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"
        type="text/javascript"></script>
    <script>
        // var id = "<?php // echo isset($_GET["ID"]) && !empty($_GET["ID"]) ? $_GET["ID"] : "" ?>";
        var id = <?php echo $PoID ?>

        $("#frmCountry").validate({

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
                // txtSpeciesName: {
                //     required: true,
                // },
            },
            errorPlacement: function () {
                return false;
            },
            submitHandler: function () {

                $.ajax({
                    url: 'Ajax/ajax-qc-new-addon-fields-for-sysco.php',
                    type: 'POST',
                    data: {
                        id: id,
                        factoryId: $("#txtFactoryId").val(),
                        shipmentDate: $("#txtShipmentDate").val(),
                        requestSentDate: $("#txtRequestSentDate").val(),
                        sampleSendingDate: $("#txtSampleSendingDate").val(),
                        reportExpectingDate: $("#txtReportExpectingDate").val(),
                        sgsRequestDate: $("#txtSGSRequestDate").val(),
                        simpSentDate: $("#txtSimpSentDate").val(),
                        lotCode: $("#txtLotCode").val(),
                        status: $("#drpStatus").val(),
                    },
                    success: function (data) {
                        console.log(data);
                        if (data.Error != '') {
                            alert(data.Msg);
                            window.location.href = "DocAddReports.php?PoID=" + id;
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

    </body>

</html>