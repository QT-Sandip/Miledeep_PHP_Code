<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_RM_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", "");

$resExporter = $resDropdownsAPI->Data->Exporter;
$resSpecies = $resDropdownsAPI->Data->Species;
$resProcessingStations = $resDropdownsAPI->Data->ProcessingStations;
$resPurchaseCenters = $resDropdownsAPI->Data->PurchaseCenters;

// var_dump($resDropdownsAPI);
// Species
// PurchaseCenters
// ProcessingStations
// PurchaseCategory
// Vehicles
// RmAgents
// Drivers
// RmGraders
// RmSuppliers
// RmVariety

$filterExporter = "T";
$filterConsignee = "T";
$filterCountry = "T";
$filterInvoiceStatus = "T";
$filterYear = "T";

$filterSideButton = "T";

$PlantID = $_SESSION['UnitID'];
$AccessType = $_SESSION['AccessType'];
?>

<!doctype html>
<html lang="en">

<head>

    <?php include_once ROOT . '/includes/head.php' ?>
    <style>
        /* Make all disabled fields look gray */
        input:disabled,
        select:disabled {
            background-color: #eee !important;
            color: #6c757d !important;
            opacity: 1 !important;
            cursor: not-allowed;
        }
        .tdSpieces{ font-size:14px !important }
        .tdWeight{ font-size:14px !important }
        .tdBalanceWeight{ font-size:14px !important }
        tfoot #TotalWeight,
            tfoot #TotalAmount {
                font-size: 14px !important;
            }

            /* Optional: ensure all <th> inside this tfoot scale too */
            tfoot #TotalWeight,
            tfoot #TotalAmount,
            tfoot th {
                font-size: 14px !important;
            }
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
                <?php // include_once ROOT . '/Sales/includes/page-filter.php' 
                ?>

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <div class="card">
                            <form role="form" id="frmRmOverAllArrival" method="post">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-lg-3 mb-3">
                                            <label class="form-label" for="txtDate">Date</label>
                                            <input type="date" class="form-control" name="txtDate" id="txtDate"
                                                placeholder="Enter Date" value="<?php echo date('Y-m-d'); ?>" />
                                        </div>

                                        <div class="col-lg-3 mb-3">
                                            <label class="form-label" for="drpCompany">Company</label>
                                            <select class="form-control drpSelect" name="drpCompany" id="drpCompany">
                                                <option value="">Select</option>
                                                <!-- <option value="2">Devi Fisheries</option> -->
                                                <?php
                                                $isFirst = true;
                                                foreach ($resExporter as $Exporter) {
                                                    $selected = $isFirst ? 'selected' : '';
                                                    echo '<option value="' . $Exporter->ExpID . '" ' . $selected . '>' . $Exporter->ExpName . '</option>';
                                                    $isFirst = false;
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-lg-3 mb-3">
                                            <label class="form-label" for="drpPlant">Species</label>
                                            <select class="form-control drpSelect" name="drpSpieces" id="drpSpieces">
                                                <option value="">Select</option>
                                                <?php
                                                foreach ($resSpecies as $item)
                                                    echo '<option value="' . $item->ShortCode . '">' . $item->SpeciesName . '</option>';
                                                ?>
                                            </select>
                                        </div>



                                        <div class="col-lg-3 mb-3">
                                            <label class="form-label" for="drpPlant">Plant</label>
                                            <select class="form-control drpSelect" name="drpPlant" id="drpPlant">
                                                <option value="">Select</option>
                                                <?php
                                                foreach ($resProcessingStations as $item)
                                                    echo '<option value="' . $item->ProcessingStationID . '">' . $item->StationName . '</option>';
                                                ?>
                                            </select>
                                        </div>



                                        <div class="col-lg-3 mb-3">
                                            <label class="form-label" for="drpCenter">Center</label>
                                            <select class="form-control drpSelect" name="drpCenter" id="drpCenter">
                                                <option value="">Select</option>
                                                <?php
                                                foreach ($resPurchaseCenters as $item)
                                                    echo '<option value="' . $item->PurchaseStationID . '">' . $item->StationName . '</option>';
                                                ?>
                                            </select>
                                        </div>




                                        <div class="col-lg-3 mb-3">
                                            <label class="form-label" for="txtWeight">Weight</label>
                                            <input type="text" class="form-control" name="txtWeight" id="txtWeight"
                                                placeholder="Enter Weight" value="" />
                                            <div></div>
                                        </div>
                                        <div class="col-lg-3 mb-3">
                                            <label class="form-label" for="txtAmount">Amount</label>
                                            <input type="text" class="form-control" name="txtAmount" id="txtAmount"
                                                placeholder="Enter Amount" value="" />
                                        </div>

                                        <!-- <div class="col-lg-3 mb-3">
                                            <label class="form-label" for="txtNumber">Number</label>
                                            <input type="text" class="form-control" name="txtNumber" id="txtNumber"
                                                placeholder="Enter Number" value="" />
                                        </div> -->

                                        <div class="col-lg-3 mb-3">
                                            <label class="form-label" for="txtAverageRate">Average Rate</label>
                                            <input type="text" class="form-control" name="txtAverageRate" readonly
                                                id="txtAverageRate" placeholder="Enter Average Rate" value="" />
                                        </div>





                                        <div class="mb-0">
                                            <div class="text-end">
                                                <input type="hidden" value="" id="hdnID" />
                                                <!-- <button type="reset" class="btn btn-danger waves-effect">
                                                    Cancel
                                                </button> -->
                                                <button type="button"
                                                    class="btn btn-info waves-effect waves-light me-1 btnSearch">
                                                    Search
                                                </button>
                                                <button type="button"
                                                    class="btn btn-success waves-effect waves-light me-1 btnNew" <?php echo ($AccessType != 3 ? '' : 'disabled'); ?>>
                                                    New
                                                </button>
                                                <button type="submit"
                                                    class="btn btn-primary waves-effect waves-light me-1" <?php echo ($AccessType != 3 ? '' : 'disabled'); ?>>
                                                    Submit
                                                </button>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </form>

                            <div class="my-3 mx-3">
                                <div id="divDataList">
                                    <!-- -->
                                </div>
                                <input type="hidden" id="hdnActionType" />

                            </div>
                        </div>


                    </div>
                </div>
            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->
        <?php include_once ROOT . '/includes/footer.php' ?>

    </div>
    <!-- end main content-->



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
    function fnShowDataTable() {
        var table = $('#tblDataTable').DataTable({
            scrollY: "600px",
            scrollX: true,
            sScrollXInner: "100%",
            paging: false,
            destroy: true,
            fixedColumns: true,
            scrollCollapse: true,
            dom: 'Blfrtip',
            buttons: [{
                extend: 'excel',
                text: 'Download Table',
                className: 'btnExcelDataTable',
                title: 'RM Over All Arrival',
                footer: true,
                exportOptions: {
                    columns: ':visible'
                },
                customize: function (xlsx) {
                    var sheet = xlsx.xl.worksheets['sheet1.xml'];
                    var styles = xlsx.xl['styles.xml'];
                    // Add a custom format for 3 decimals: 0.000
                    var numFmts = styles.getElementsByTagName('numFmts')[0];
                    var cellXfs = styles.getElementsByTagName('cellXfs')[0];
                    // Create numFmt entry if not exists
                    if (!numFmts) {
                        numFmts = styles.createElement('numFmts');
                        numFmts.setAttribute('count', 0);
                        styles.documentElement.insertBefore(numFmts, styles.documentElement.firstChild);
                    }
                    var numFmtId = 300; // Custom ID (>164)
                    var fmt = styles.createElement('numFmt');
                    fmt.setAttribute('numFmtId', numFmtId);
                    fmt.setAttribute('formatCode', '0.000'); // 3 decimals
                    numFmts.appendChild(fmt);
                    // Update numFmts count
                    numFmts.setAttribute('count', numFmts.getElementsByTagName('numFmt').length);
                    // Create XF style
                    var xf = styles.createElement('xf');
                    xf.setAttribute('numFmtId', numFmtId);
                    xf.setAttribute('applyNumberFormat', '1');
                    cellXfs.appendChild(xf);
                    // Index of the newly inserted style
                    var newStyleIndex = cellXfs.getElementsByTagName('xf').length - 1;
                    // Apply 3 decimals ONLY to column F
                    $('row c[r^="F"]', sheet).each(function () {
                        $(this).attr('s', newStyleIndex);
                    });
                }
            }],
        });
        table.on('draw.dt', function () {
            let totalWeight = 0;
            let amounts = 0;

            $('.tdWeight').each(function () {
                // Parse the text content as a float, ignoring any commas
                const weight = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(weight)) {
                    totalWeight += weight;
                }
            });
            $('.tdAmount').each(function () {
                // Parse the text content as a float, ignoring any commas
                const value = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(value)) {
                    amounts += value;
                }
            });

            $('#TotalWeight').text(totalWeight.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
            $('#TotalAmount').text(amounts.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
        });
    }

    function fnSuccess(data) {

        $("#divDataList").html(data);
        fnShowDataTable();

        // $("<button class='btn btn-primary' id='btnMoreSearch' style='padding: 3px 5px;margin-left: 10px;'>More Search</button>" +
        //     "<button class='btn btn-primary' id='btnLoadAll' style='padding: 3px 5px;margin-left: 10px;'>Load All</button>")
        //     .insertAfter("#tblDataTable_filter label");

        $("<button class='btn btn-secondary buttons-excel buttons-html5 btnExcelDataTable' id='btnExcelDownload' style='margin-left: 15px; margin-top:-5px;' type='button'><span>Download Report</span></button>")
            .insertAfter("#tblDataTable_filter label");


        $('html, body').animate({
            scrollTop: $("#divDataList").offset().top - 50
        }, 10);

        if (typeof $("#hdnMsg").val() != "undefined") {
            if ($("#hdnMsg").val() != "") {
                alert($("#hdnMsg").val());
            }
        }

    }

    $(document).ready(function () {
        var plantId = <?php echo $PlantID ?>;
        var accessType = <?php echo $AccessType ?>;
        console.log('accessType' + accessType);

        if (accessType == 3) {
            if (plantId != 0) {
                $("#drpPlant").prop('disabled', true);
                $("#drpPlant").val(plantId).trigger('change');
            } else {
                $("#drpPlant").prop('disabled', false);
                $("#drpPlant").val("").trigger('change');
            }
        } else {
            $("#drpPlant").prop('disabled', false);
            $("#drpPlant").val("").trigger('change');
        }
    });

    function ShowList() {
        $.ajax({
            url: 'Ajax/AjaxRmOverAllArrival.php',
            type: 'POST',
            data: {
                ArrivalDate: $("#txtDate").val(),
                Company: $("#drpCompany").val(),
                Plant: $("#drpPlant").val(),
                Center: $("#drpCenter").val(),
                Species: $("#drpSpieces").val(),
                MethodType: "Get"
            },
            success: function (data) {
                //$("#divDataList").html(data);
                // fnShowDataTable();
                fnSuccess(data);
                return false;
            }
        });
    }
    $(document).ready(function () {
        ShowList();
    });

    $(document).on("click", ".btnSearch", function () {
        ShowList();
    })

    $(document).on("click", "#btnExcelDownload", function () {
        console.log($('#select2-drpCenter-container').text().split(' ').join('_'));

        var arrivalDate = $("#txtDate").val();
        var plant = $("#drpPlant").val();
        var plantName = $("#select2-drpPlant-container").text().split(' ').join('-');
        var center = $("#drpCenter").val();
        var centerName = $('#select2-drpCenter-container').text().split(' ').join('_');

        var company = $("#drpCompany").val();
        var companyName = $("#select2-drpCompany-container").text();
        var spieces = $("#drpSpieces").val();

        var queryString = "/v3/Purchase/Reports/DocExlRmOverAllArrivalReport?ArrivalDate=" + arrivalDate + "&PlantID=" + plant + "&PlantName=" + plantName + "&CenterID=" + center + "&CenterName=" + centerName + "&CompanyID=" + company + "&CompanyName=" + companyName + "&Species=" + spieces;
        window.open(queryString, '_blank');


    })

    $(document).on("click", ".btnEdit", function () {
        if (confirm('Are you sure do you want to edit ?')) {

            var row = $(this).closest("tr");

            var totalWeight = parseFloat(row.find(".tdWeight").text().replace(/,/g, ""));
            var balanceWeight = parseFloat(row.find(".tdBalanceWeight").text().replace(/,/g, ""));

            if (totalWeight === balanceWeight) {

                $("#txtDate").prop("disabled", false);
                $("#drpCompany").prop("disabled", false);
                $("#drpPlant").prop("disabled", false);
                $("#drpCenter").prop("disabled", false);
                $("#drpSpieces").prop("disabled", false);
                $("#txtAmount").prop("disabled", false);
                $("#txtWeight").prop("disabled", false);
                $("#txtAverageRate").prop("disabled", false);
            } else {
                $("#txtDate").prop("disabled", true);
                $("#drpCompany").prop("disabled", true);
                $("#drpPlant").prop("disabled", true);
                $("#drpCenter").prop("disabled", true);
                $("#drpSpieces").prop("disabled", true);
                $("#txtAmount").prop("disabled", false);
                $("#txtWeight").prop("disabled", false);
                
            }

            var id = $(this).attr("data-id");
            var tdDate = $.trim($(this).closest("tr").find(".tdDate").attr("data-date"));
            var hdnCompany = $(this).closest("tr").find(".hdnCompany").val();
            var hdnPlant = $(this).closest("tr").find(".hdnPlant").val();
            var tdCenter = $(this).closest("tr").find(".hdnCenter").val();
            var tdSpieces = $(this).closest("tr").find(".tdSpieces").text();
            var tdWeight = $(this).closest("tr").find(".tdWeight").text();
            // var tdNumber = $(this).closest("tr").find(".tdNumber").text();
            var tdAverageRate = $(this).closest("tr").find(".tdAverageRate").text();
            var tdAmount = $(this).closest("tr").find(".tdAmount").text();

            $("#hdnID").val(id);
            $("#txtDate").val(tdDate);
            $("#drpCompany").val(hdnCompany).trigger("change");
            $("#drpPlant").val(hdnPlant).trigger("change");
            $("#drpCenter").val(tdCenter).trigger("change");
            $("#drpSpieces").val(tdSpieces).trigger("change");
            $("#txtWeight").val(tdWeight);
            // $("#txtNumber").val(tdNumber);
            $("#txtAverageRate").val(tdAverageRate);
            $("#txtAmount").val(tdAmount);
        }
    })
    $(".btnNew").on("click", function () {
        $("#txtDate, #drpCompany, #drpPlant, #drpCenter, #drpSpieces, #txtAmount, #txtAverageRate").prop("disabled", false);

        $("#hdnID").val("");
        // $("#txtDate, #txtWeight, #txtAverageRate, #txtAmount").val("");
        // $("#drpCompany, #drpPlant,#drpCenter, #drpSpieces").val("").trigger("change");
        $("#txtWeight, #txtAverageRate, #txtAmount").val("");

    })

    // // 27-01-2024 Edited Durga Prasad
    // $(document).on("click", ".btnView", function() {
    //     // alert("hello");
    //     var viewID = $(this).attr("data-id");
    //     // console.log(viewID);
    //     $.ajax({
    //         url: 'create-arrival.php',
    //         type: 'POST',
    //         data: {
    //             methodType: "Get",
    //             ArrivalID: viewID
    //         },
    //         success: function(data) {
    //             return false;
    //         }
    //     });
    // });

    // console.log(viewID);


    $(document).on("click", ".btnDelete", function () {
        var selectedID = $(this).attr("data-id");
        var tdDate = $.trim($(this).closest("tr").find(".tdDate").attr("data-date"));

        if (confirm("Are you sure do you want to delete !") == true) {
            $.ajax({
                url: 'Ajax/AjaxRmOverAllArrival.php',
                type: 'POST',
                data: {
                    ArrivalID: selectedID,
                    ArrivalDate: tdDate,
                    MethodType: "Delete"
                },
                success: function (data) {
                    //$("#divDataList").html(data);
                    // fnShowDataTable();
                    // fnSuccess(data);

                    // alert("Deleted Posted Successful");
                    // fnSuccess(data);

                    if (data.Error == null) {
                        alert(data.Msg);
                        ShowList();
                        // location.reload();
                    }
                    return false;
                }
            });
        } else {

        }

        // $("#hdnActionType").val("delete");
        // fnShowModalConfirmation("Confirmation", "Are you sure you want to delete the item ?", "Cancel", "Continue",
        //     selectedID, "T");
        // fnShowModalConfirmation("Success", "Successfully Updated !", "Cancel", "", "", "F");
        // fnShowModalConfirmation("Error", "Something error has occurred", "Cancel", "", "", "T");
    })

    /* Delete */
    // $(document).on("click", "#btnModalContinue", function() {
    //     var selectedID = $("#hdnSelectedData").val();
    //     // var pNo = $(".active.btnPageno").attr("data-pageno");

    //     if ($("#hdnActionType").val() == "delete") {

    //     }

    // });

    $("#txtAmount, #txtWeight").on("keyup", function () {
        var amt = parseFloat($("#txtAmount").val());
        var weg = parseFloat($("#txtWeight").val());

        var avgRate = (amt / weg).toFixed(2);

        if ($.isNumeric(avgRate)) {

            $("#txtAverageRate").val(avgRate);
        } else {
            $("#txtAverageRate").val("");

        }

        // if (isNaN($(avgRate).val()) || $(avgRate).val() === undefined) {
        //     console.log("error");
        // } else {
        //     $("#txtAverageRate").val(avgRate);
        // }



    })

    $("#frmRmOverAllArrival").validate({
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
            txtDate: {
                required: true
            },
            drpCompany: {
                required: true,
                // selectcheck: true
            },
            drpPlant: {
                required: true,
                // selectcheck: true
            },
            drpCenter: {
                required: true,
                // selectcheck: true
            },
            drpSpieces: {
                required: true,
                // selectcheck: true
            },
            txtWeight: {
                required: true,
                number: true
            },
            // txtNumber: {
            //     required: true,
            //     number: true
            // },
            txtAverageRate: {
                required: true,
                number: true
            },
            txtAmount: {
                required: true,
                number: true
            }
        },
        errorPlacement: function () {
            return false; // suppresses error message text
        },
        submitHandler: function () {

            $.ajax({
                url: 'Ajax/AjaxRmOverAllArrival.php',
                type: 'POST',
                data: {
                    MethodType: "Post",
                    ArrivalID: $("#hdnID").val(),
                    ArrivalDate: $("#txtDate").val(),
                    CompanyID: $("#drpCompany").val(),
                    PlantID: $("#drpPlant").val(),
                    CenterID: $("#drpCenter").val(),
                    Species: $("#drpSpieces").val(),
                    TotalWeight: $("#txtWeight").val(),
                    TotalAmount: $("#txtAmount").val(),
                    AvgRate: $("#txtAverageRate").val()
                },
                success: function (data) {
                    if (data) {
                        //alert("Success! Your data has been successfully added/modified.");
                        fnSuccess(data);

                        // $("#txtWeight, #txtAverageRate, #txtAmount").val("");
                        // $("#drpCompany, #drpSpieces, #drpPlant, #drpCenter").val("").trigger("change");
                        $("#txtWeight, #txtAverageRate, #txtAmount").val("");

                        //$("#drpCompany, #drpSpieces, #drpPlant, #drpCenter").val("").trigger("change");
                        //ShowList();
                    } else {

                    }


                    // $("#drpCompany, #drpPlant,#drpCenter, #drpSpieces").val("").trigger("change");

                    return false;

                    // var data = JSON.parse(data);
                    // var htmlData = data.html;
                    // var resJson = data.json;
                    // var error = data.Error;
                    // var msg = data.Msg;

                    // //console.log(data);

                    // if (error === null) {
                    //     alert(msg);
                    // } else {
                    //     alert(error);
                    //     return false;
                    // }

                    // if (bookingID == "") {
                    //     var newUrl = window.location.href + "?BookingID=" + resJson.RmBookingID;
                    //     // window.history.pushState({}, '', newUrl);
                    //     window.location.href = newUrl;
                    // }

                    // $("#divDataList").html(htmlData);
                    // fnShowDataTable();

                    // if (bookingID) {
                    //     bindFormData(resJson);
                    // }
                    // $("#txtBookingCount").val("");
                    // $("#txtBookingWeight").val("");
                    // $("#txtBookingRate").val("");

                }
            });
            // } else {
            //     return true;
            // }          
            return false;
        }
    });
</script>


</body>

</html>
