<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_RM_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", "");

$resSpecies = $resDropdownsAPI->Data->Species;
$resPurchaseCategory = $resDropdownsAPI->Data->PurchaseCategory;
$resPurchaseCenters = $resDropdownsAPI->Data->PurchaseCenters;

$RateID = $_GET['RateID'];

$apiData = API_RM_PRICES;
$bodyRequest = [
    "RateID" => $RateID
];
$resData = CallAPI($apiData, "GET", "", $bodyRequest);

$DeductionData = $resData->data->rmDeductionData;

// echo json_encode($DeductionData, JSON_PRETTY_PRINT);
// exit;


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
                <?php // include_once WORKING_MODULE . '/includes/page-filter.php' 
                ?>

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <div class="card">
                            <div class="card-body">

                                <form role="form" method="post" id="frmRmDeductions">
                                    <div class="row">
                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpPurchaseType" class="form-label">Purchase Type <span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpPurchaseType" id="drpPurchaseType" <?php echo (!empty($RateID) ? "disabled" : "") ?>>
                                                <option value=""> Select</option>
                                                <?php foreach ($resPurchaseCategory as $Item) { ?>
                                                    <option value="<?php echo $Item->RmCategoryID; ?>" <?php echo ($DeductionData->PurchaseType == $Item->RmCategoryID) ? 'selected' : ''; ?>>
                                                        <?php echo $Item->RmCategoryName; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpSpecies">Species <span style="color:red">*</span></label>
                                            <select class="drpSelect form-control" id="drpSpecies" name="drpSpecies" <?php echo (!empty($RateID) ? "disabled" : "") ?>>
                                                <option value="">Select</option>
                                                <?php foreach ($resSpecies as $item) { ?>
                                                    <option value="<?php echo $item->ShortCode ?>" <?php echo ($DeductionData->Species == $item->ShortCode) ? 'selected' : ''; ?>><?php echo $item->SpeciesName . '(' . $item->ShortCode . ')' ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpCenters" class="form-label">Center Name <span style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpCenters" id="drpCenters" multiple="multiple" <?php echo (!empty($RateID) ? "disabled" : "") ?>>
                                                <option value="0" <?php echo ($DeductionData->Species == 0) ? 'selected' : ''; ?>> All</option>
                                                <?php foreach ($resPurchaseCenters as $Item) { ?>
                                                    <?php $selected = '';
                                                    $selectedIds = explode(",", $DeductionData->CenterID);
                                                    if (in_array($Item->PurchaseStationID, $selectedIds)) {
                                                        $selected = 'selected="selected"';
                                                    }
                                                    ?>
                                                    <option <?php echo $selected; ?> value="<?php echo $Item->PurchaseStationID; ?>">
                                                        <?php echo $Item->StationName; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtEffectedDate" class="form-label">Effected Date <span style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtEffectedDate" name="txtEffectedDate" <?php echo (!empty($RateID) ? "readonly" : "") ?> value="<?php echo date($DeductionData->EffectedDate); ?>" />
                                        </div>
                                        <hr>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtStartCount" class="form-label">Start Count <span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtStartCount" name="txtStartCount" placeholder="Enter Start Count" value="" />
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtEndCount" class="form-label">End Count <span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtEndCount" name="txtEndCount" placeholder="Enter End Count" value="" />
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtStartWeight" class="form-label">Start Weight <span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtStartWeight" name="txtStartWeight" placeholder="Enter Start Weight" value="" />
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtEndWeight" class="form-label">End Weight <span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtEndWeight" name="txtEndWeight" placeholder="Enter End Weight" value="" />
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtExtraRate" class="form-label">Extra Rate <span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtExtraRate" name="txtExtraRate" placeholder="Enter Extra Rate" value="" />
                                        </div>

                                        <div class="text-center mt-1">
                                            <input type="hidden" id="hdnRateID" name="hdnRateID" value="<?php echo $RateID ?>" />
                                            <input type="hidden" id="hdnNewRateID" name="hdnNewRateID" value="<?php echo $RateID ?>" />
                                            <input type="hidden" id="hdnBreakUpID" name="hdnBreakUpID" value="" />
                                            <a href="rm-deductions-list" class="btn btn-danger">Close</a>
                                            <button id="btnSubmit" type="submit" class="btn btn-primary waves-effect waves-light">
                                                Submit
                                            </button>
                                        </div>

                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div id="divDataList">
                                </div>
                                <input type="hidden" id="hdnActionType" />
                            </div>
                        </div>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js" type="text/javascript"></script>


    <script>
        function fnShowDataTable() {
            var drpPurchaseType = $("#drpPurchaseType option:selected").text().trim();
            var drpSpecies = $("#drpSpecies").val();
            var txtEffectedDate = $("#txtEffectedDate").val();
            if (txtEffectedDate) {
                var dateParts = txtEffectedDate.split("-");
                txtEffectedDate = dateParts[2] + "-" + dateParts[1] + "-" + dateParts[0]; // Reformat to dd-mm-yyyy
            }
            // console.log(drpPurchaseType);
            // console.log(drpSpecies);
            // console.log(txtEffectedDate);

            var table = $('#tblDataTable').DataTable({
                scrollY: "600px",
                scrollX: true,
                sScrollXInner: "100%",
                paging: false,
                destroy: true,
                fixedColumns: true,
                scrollCollapse: true,
                info: false,
                ordering: false,
                dom: 'Blfrtip',
                buttons: [{
                    extend: 'excel',
                    text: 'Download Excel',
                    className: 'btnExcelDataTable btn-left-align',
                    title: 'RM Deductions <?php echo date("d-m-Y") ?>',
                    title: 'RM Price Variation ' + drpPurchaseType + ' ' + drpSpecies + ' ' + txtEffectedDate,
                    footer: true,
                    exportOptions: {
                        columns: ':visible'
                    }
                }],
            });
        }

        $(document).ready(function() {
            $('#drpCenters').select2();
            ShowList();


            $(document).on('click', '.btnEdit', function() {
                var brkUpID = $(this).data('brkupid');
                console.log(brkUpID);
                $('#hdnBreakUpID').val(brkUpID);

                $("#txtStartCount").val($(this).closest("tr").find(".tdStartCount").text());
                $("#txtEndCount").val($(this).closest("tr").find(".tdEndCount").text());
                $("#txtStartWeight").val($(this).closest("tr").find(".tdStartWeight").text());
                $("#txtEndWeight").val($(this).closest("tr").find(".tdEndWeight").text());
                $("#txtExtraRate").val($(this).closest("tr").find(".tdExtra").text());
            });

        });

        function ShowList() {
            $.ajax({
                url: 'Ajax/ajax-rm-deductions.php',
                type: 'POST',
                data: {
                    methodType: "getList",
                    rateID: $("#hdnRateID").val()
                },
                success: function(data) {
                    console.log(data);
                    $("#divDataList").html(data.Content);
                    fnShowDataTable();
                }
            });
        }


        jQuery.validator.addMethod(
            "noSpace",
            function(value, element) {
                return value == "" || value.trim().length != 0;
            },
            "No space please and don't leave it empty"
        );
        $("#frmRmDeductions").validate({

            onkeyup: false,
            onfocusout: false,
            invalidHandler: function(form, validator) {
                var errors = validator.numberOfInvalids();
                if (errors) {
                    var firstInvalidElement = $(validator.errorList[0].element);
                    firstInvalidElement.focus();
                }
            },
            ignore: "",
            rules: {
                drpPurchaseType: {
                    required: true,
                },
                drpSpecies: {
                    required: true,
                },
                drpCenters: {
                    required: true,
                },
                txtEffectedDate: {
                    required: true,
                },
                txtStartCount: {
                    required: true,
                },
                txtEndCount: {
                    required: true,
                },
                txtStartWeight: {
                    required: true,
                },
                txtEndWeight: {
                    required: true,
                },
                txtExtraRate: {
                    required: true,
                },
            },
            errorPlacement: function() {
                return false;
            },
            submitHandler: function() {
                var centers = $('#drpCenters').val().join(',');
                $.ajax({
                    url: 'Ajax/ajax-rm-deductions.php',
                    type: 'POST',
                    data: {
                        methodType: 'formPost',
                        rateID: $("#hdnRateID").val(),
                        breakUpID: $("#hdnBreakUpID").val(),
                        purchaseType: $("#drpPurchaseType").val(),
                        species: $('#drpSpecies').val(),
                        centers: centers,
                        effectedDate: $('#txtEffectedDate').val(),
                        startCount: $('#txtStartCount').val(),
                        endCount: $('#txtEndCount').val(),
                        startWeight: $('#txtStartWeight').val(),
                        endWeight: $('#txtEndWeight').val(),
                        extraRate: $('#txtExtraRate').val(),
                    },
                    success: function(data) {
                        console.log(data);
                        if (data.Error != '') {
                            alert(data.Msg);
                            $("#divDataList").html(data.Content);
                            fnShowDataTable();

                            if (data.RateID) {
                                $("#hdnNewRateID").val(data.RateID);
                            }

                            var existingRateID = $(document).find("#hdnRateID").val();

                            if (existingRateID != '') {
                                window.location.href = 'rm-deductions?RateID=' + existingRateID;
                            } else {
                                window.location.href = 'rm-deductions?RateID=' + data.RateID;
                            }

                            $("#drpPurchaseType, #drpSpecies, #drpCenters").prop('disabled', true);
                            $("#txtEffectedDate").prop('readonly', true);
                            $("#txtStartCount, #txtEndCount, #txtStartWeight, #txtEndWeight, #txtExtraRate").val("");

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