<?php

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$sql_lab_users = "SELECT * FROM QcLabTechnologist where LbuStatus=1 ";
$stmt_verification_users = $iconn->query($sql_lab_users);
$stmt_inspection_users = $iconn->query($sql_lab_users);

$sqlGradesQuery = "SELECT Options,value FROM ProductionGrades";
$stmtGradesQuery = $iconn->query($sqlGradesQuery);
// while ($dataRow = $stmtGradesQuery->fetch(PDO::FETCH_ASSOC)) {
//     $Data[] = $dataRow;
// }
// $row_lab_users = $stmt_lab_users->fetch_array()
?>
<!doctype html>
<html lang="en">

<head>
    <?php include_once ROOT . 'includes/head.php' ?>
    <style>
        .form-control:disabled {
            background-color: #f5f5f5;
            opacity: 1;
        }
    </style>
</head>

<body>

    <?php include_once ROOT . 'includes/body-preloader.php' ?>

    <!-- Begin page -->
    <div id="layout-wrapper">

        <?php include_once ROOT . 'includes/menu.php' ?>

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">

            <div class="page-content">
                <div class="container-fluid">

                    <?php include_once ROOT . 'includes/page-header.php' ?>

                </div>
                <!-- start page title -->

                <div class="row">
                    <div class="col-12">

                        <div class="card">
                            <div class="card-body">
                                <form id="frmGradingInspection" class="needs-validation">
                                    <div class="row">
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtArrivalDate" class="form-label">Arrival Date <span style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtArrivalDate" name="txtArrivalDate" placeholder=""
                                                value="<?php echo date("Y-m-d"); ?>" max="<?php echo date('Y-m-d'); ?>" />
                                        </div>
                                        <!-- <h4 class="card-title">Bootstrap Validation - Normal</h4> -->
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpBatchCode" class="form-label">Batch Number <span style="color:red">*</span></label>
                                            <select class="drpSelect form-control select2" name="drpBatchCode" id="drpBatchCode">
                                                <option value=""> Select</option>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtVariety" class="form-label">Variety<span style="color:red">*</span></label>
                                            <input type="text" class="form-control " id="txtVariety" name="txtVariety" />
                                        </div>
                                        <!-- <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpGrade" class="form-label">Grade<span style="color:red">*</span></label>
                                            <select class="drpSelect form-control select2" name="drpGrade" id="drpGrade">
                                                <option value=""> Select</option>
                                                <?php while ($rowsGradesQuery = $stmtGradesQuery->fetch_array()) { ?>
                                                    <option value="<?php echo $rowsGradesQuery['value'] ?>">
                                                        <?php echo $rowsGradesQuery['Options'] ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div> -->


                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpGrade" class="form-label">Grade</label>
                                            <select class="drpSelect form-control select2" name="drpGrade" id="drpGrade">
                                                <option value="">Select</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtGeneralAppearance" class="form-label">General Appearance<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtGeneralAppearance" name="txtGeneralAppearance" value="Good" />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtColor" class="form-label">Color<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtColor" name="txtColor" value="Light" />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtFreshness" class="form-label">Freshness<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtFreshness" name="txtFreshness" value="Good" />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtOdour" class="form-label">Odour<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtOdour" name="txtOdour" value="Fresh" />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtTexture" class="form-label">Texture<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtTexture" name="txtTexture" value="Firm" />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtTemperature" class="form-label">Temperature<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtTemperature" name="txtTemperature" value="" />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtCountPerLb" class="form-label">Count / Lb<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtCountPerLb" name="txtCountPerLb" value="" />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtBigPieceperGram" class="form-label">Big Piece<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtBigPieceperGram" name="txtBigPieceperGram" value="" />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtSmallPieceperGram" class="form-label">Small Piece<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtSmallPieceperGram" name="txtSmallPieceperGram" value="" />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtUniformity" class="form-label">Uniformity<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtUniformity" name="txtUniformity" value="" />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtTTotalPiecesperGram" class="form-label">Total Pieces<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtTotalPiecesperGram" name="txtTotalPiecesperGram" value="" />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtDeterioration" class="form-label">Deterioration</label>
                                            <input type="text" class="form-control" id="txtDeterioration" name="txtDeterioration" value="Nill" />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtDiscolouration" class="form-label">Discolouration</label>
                                            <input type="text" class="form-control" id="txtDiscolouration" name="txtDiscolouration" value="Nill" />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtBlackSpots" class="form-label">Black Spots</label>
                                            <input type="text" class="form-control" id="txtBlackSpots" name="txtBlackSpots" value="" />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtSoftShell" class="form-label">Soft Shell</label>
                                            <input type="text" class="form-control" id="txtSoftShell" name="txtSoftShell" value="" />
                                        </div>
                                        <!-- <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtHONQuantity" class="form-label">HON Quantity<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtHONQuantity" name="txtHONQuantity" value="" />
                                        </div> -->
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtHLQuantity" class="form-label">HL Quantity<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtHLQuantity" name="txtHLQuantity" value="" />
                                        </div>
                                        <!-- <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtYield" class="form-label">Yield(%)<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtYield" name="txtYield" value="" />
                                        </div> -->

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtHangingMeat" class="form-label">Hanging Meat</label>
                                            <input type="text" class="form-control" id="txtHangingMeat"
                                                name="txtHangingMeat">
                                        </div>

                                       
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpInspectedBy">Inspected By<span style="color:red">*</span></label>
                                            <select class="form-control drpSelect select2" name="drpInspectedBy" id="drpInspectedBy">
                                                <option value=""> Select</option>
                                                <?php while ($row_inspection_users = $stmt_inspection_users->fetch_array()) { ?>
                                                    <option value="<?php echo $row_inspection_users['LbuName'] ?>">
                                                        <?php echo $row_inspection_users['LbuName'] ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                         <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpVerifiedBy">Verified By<span style="color:red">*</span></label>
                                            <select class="form-control drpSelect select2" name="drpVerifiedBy" id="drpVerifiedBy">
                                                <option value=""> Select</option>
                                                <option value="KL Ganesh" selected>KL Ganesh</option>
                                                <?php while ($row_verification_users = $stmt_verification_users->fetch_array()) { ?>
                                                    <option value="<?php echo $row_verification_users['LbuName'] ?>">
                                                        <?php echo $row_verification_users['LbuName'] ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                         <div class="form-group col-sm-3 mb-3">
                                            <label for="txtRemarks" class="form-label">Remarks<span
                                                    style="color:red"></span></label>
                                            <input type="text" class="form-control" id="txtRemarks"
                                                name="txtRemarks">
                                        </div>
                                        
                                        <div class="col-md-12 col-lg-12 mb-3">
                                            <div class="d-flex align-items-center justify-content-center h-100 gap-2">
                                                <button type="button" class="btn btn-primary" id="btnSave">Save</button>
                                                <button type="button" class="btn btn-secondary" id="btnClear">Clear</button>
                                            </div>
                                        </div>
                                        <input type="hidden" name="isEdit" id="isEdit" value="0">
                                        <input type="hidden" name="hdnArrivalID" id="hdnArrivalID" value="">
                                        <input type="hidden" name="hdnInspectionID" id="hdnInspectionID" value="">

                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="card">
                            <div class="card-body">

                                <div id="divDataList" style="padding: 15px;">


                                </div>

                            </div>
                        </div>

                    </div>
                </div>

            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->

        <?php include_once ROOT . 'includes/footer.php' ?>

    </div>
    <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->


    <?php include_once ROOT . 'includes/common-js.php' ?>
    <?php include_once WORKING_MODULE . 'modalSupplier.php' ?>


    <!-- Required datatable js -->
    <script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="https://cdn.jsdelivr.net/jquery.validation/1.16.0/jquery.validate.min.js"></script>
    <?php include_once ROOT . '/includes/app-js.php' ?>
    <script>
        function clearForm(full = false) {
            if (full) {
                $("#txtArrivalDate").val("");
            }
            $("#drpBatchCode").val("").trigger('change');
            $("#txtVariety").val("");
            $("#drpGrade").val("").trigger('change');
            $("#txtGeneralAppearance").val("Good");
            $("#txtColor").val("Light");
            $("#txtFreshness").val("Good");
            $("#txtOdour").val("Fresh");
            $("#txtTexture").val("Firm");
            $("#txtTemperature").val("");
            $("#txtCountPerLb").val("");
            $("#txtBigPieceperGram").val("");
            $("#txtSmallPieceperGram").val("");
            $("#txtUniformity").val("");
            $("#txtTotalPiecesperGram").val("");
            $("#txtDeterioration").val("Nill");
            $("#txtDiscolouration").val("Nill");
            $("#txtBlackSpots").val("");
            $("#txtSoftShell").val("");
            $("#txtHONQuantity").val("");
            $("#txtHLQuantity").val("");
            $("#txtYield").val("");
            $("#txtHangingMeat").val("");
            $("#drpVerifiedBy").val("KL Ganesh").trigger('change');
            $("#drpInspectedBy").val("").trigger('change');
              $("# txtRemarks").val("");
           
            $("#isEdit").val("0");
            $("#divDataList").html("");
            $("#btnSave").html("Save");
            $("#hdnArrivalID").val("");
              $("#txtRemarks").val("");
        }

        function getGradingInspectionDetails() {
            $.ajax({
                url: 'Ajax/AjaxGradingInspection.php',
                type: 'POST',
                data: {
                    methodType: "get",
                    ArrivalDate: $("#txtArrivalDate").val(),
                },
                success: function(data) {
                    if (data.Error) {
                        alert(data.Msg);
                    } else if (data.html) {
                        $("#divDataList").html(data.html);
                        fnShowDataTable();
                    }
                }
            });
        }


        $(document).on("change", "#drpBatchCode", function() {
            $.ajax({
                url: 'Ajax/AjaxGradingInspection.php',
                type: 'POST',
                data: {
                    ArrivalDate: $("#txtArrivalDate").val(),
                    batchCode: $('#drpBatchCode').val(),
                    methodType: "getGrade"
                },
                success: function(data) {
                    var list = "<option value=''>Select</option>";
                    if (data.Data.length > 0) {
                        $.each(data.Data, function(index, value) {
                            list += "<option value='" + value.packingGrade + "' data-weight='" + value.hLWeight + "'>" + value.packingGrade + "</option>";

                        });
                    }
                    // console.log(list);

                    $('#drpGrade').select2('destroy');
                    $('#drpGrade').html(list);
                    $('#drpGrade').select2();
                    return false;
                }
            });
        });

        $(document).on("change", "#drpGrade", function() {
            var hlWeight = $(this).find('option:selected').data('weight') || '';
            $('#txtHLQuantity').val(hlWeight);
        });





        function loadBatchCodes(arrivalDate) {
            $.ajax({
                url: 'Ajax/AjaxGradingInspection.php',
                type: 'POST',
                data: {
                    ArrivalDate: arrivalDate,
                    methodType: "getLotDetails",
                },
                success: function(data) {
                    // console.log(data)

                    if (data.Error) {
                        alert(data.Msg);
                    } else if (data.Data && data.Data.length > 0) {
                        var list = "<option value=''>Select</option>";
                        if (data.Data) {
                            $.each(data.Data, function(index, value) {
                                // console.log(index, value)
                                // Assuming value has properties RmArrivalID and ArrivalNumber
                                if (value.RmArrivalID && value.RmArrivalID) {
                                    list += "<option value='" + value.ArrivalNumber + "' data-arrivalid='" + value.RmArrivalID + "'>" + value.ArrivalNumber + "</option>";
                                }
                            });
                        }


                    } else {
                        var list = "<option value=''>Select</option>"
                    }
                    $('#drpBatchCode').select2('destroy');
                    $('#drpBatchCode').html(list);
                    $('#drpBatchCode').select2();


                    return false;
                }
            });
        }
        $(document).ready(function() {
            console.log("document ready");

            // Initial load
            loadBatchCodes($("#txtArrivalDate").val());
            getGradingInspectionDetails();

            // On date change
            $(document).on("change", "#txtArrivalDate", function() {
                $("#drpBatchCode, #drpGrade, #drpVerifiedBy, #drpInspectedBy").each(function() {
                    $(this).removeClass('is-invalid error').removeAttr('aria-invalid');
                    $(this).next("label.error").remove();
                });

                $("#frmGradingInspection").validate().resetForm();
                $("#frmGradingInspection").find('.is-invalid').removeClass('is-invalid');

                loadBatchCodes($(this).val());
                getGradingInspectionDetails();
            });


            // On clear button click
            $(document).on("click", "#btnClear", function() {
                clearForm();
            });

            // // :white_check_mark: On edit button click — FIX: removed setting variety manually
            // $(document).on("click", ".btnEdit", function() {
            //     var row = $(this).closest("tr");
            //     var cells = row.find("td");
            //     var ArrivalID = $(this).data("arrivalid");
            //     var InspectionID = $(this).data("id");

            //     const arrivalDate = cells.eq(1).text(); // Arrival Date
            //     const batchCode = cells.eq(2).text(); // Batch Code
            //     const variety = cells.eq(3).text(); // Variety

            //     // Set values to form fields
            //     $("#txtArrivalDate").val(arrivalDate);
            //     $("#drpBatchCode").val(batchCode).trigger("change"); // Match your <select> options
            //     $("#txtVariety").val(variety); // If you're using an <input>, use its ID here

            //     $("#drpGrade").val(cells.eq(4).text()).trigger("change");
            //     $("#txtGeneralAppearance").val(cells.eq(5).text());
            //     $("#txtColor").val(cells.eq(6).text());
            //     $("#txtFreshness").val(cells.eq(7).text());
            //     $("#txtOdour").val(cells.eq(8).text());
            //     $("#txtTexture").val(cells.eq(9).text());
            //     $("#txtTemperature").val(cells.eq(10).text());
            //     $("#txtCountPerLb").val(cells.eq(11).text());
            //     $("#txtBigPieceperGram").val(cells.eq(12).text());
            //     $("#txtSmallPieceperGram").val(cells.eq(13).text());
            //     $("#txtUniformity").val(cells.eq(14).text());
            //     $("#txtTotalPiecesperGram").val(cells.eq(15).text());
            //     $("#txtDeterioration").val(cells.eq(16).text());
            //     $("#txtDiscolouration").val(cells.eq(17).text());
            //     $("#txtBlackSpots").val(cells.eq(18).text());
            //     $("#txtSoftShell").val(cells.eq(19).text());
            //     $("#txtHONQuantity").val(cells.eq(20).text());
            //     $("#txtHLQuantity").val(cells.eq(21).text());
            //     $("#txtYield").val(cells.eq(22).text());
            //     $("#txtHangingMeat").val(cells.eq(23).text());
            //     $("#drpVerifiedBy").val(cells.eq(24).text()).trigger("change");
            //     $("#drpInspectedBy").val(cells.eq(25).text()).trigger("change");

            //     $("#hdnArrivalID").val(ArrivalID);
            //     $("#hdnInspectionID").val(InspectionID);
            //     $("#isEdit").val("1");
            //     $("#btnSave").html("Update");
            // });

            $(document).on("click", ".btnEdit", function() {
                var row = $(this).closest("tr");
                var cells = row.find("td");
                var ArrivalID = $(this).data("arrivalid");
                var InspectionID = $(this).data("id");

                // Arrival Date (convert d-m-Y → Y-m-d)
                var arrivalDateText = cells.eq(1).text().trim();
                var parts = arrivalDateText.split("-");
                var arrivalDateFormatted = parts.length === 3 ? parts[2] + "-" + parts[1] + "-" + parts[0] : "";
                $("#txtArrivalDate").val(arrivalDateFormatted);
                $("#drpBatchCode").val(cells.eq(2).text().trim()).trigger("change");
                $("#txtVariety").val(cells.eq(3).text().trim());
                // $("#drpGrade").val(cells.eq(4).text().trim()).trigger("change");

                var gradeValue = cells.eq(4).text().trim();
                setTimeout(function() {
                    $("#drpGrade").val(gradeValue).trigger("change");
                }, 500);
                $("#txtGeneralAppearance").val(cells.eq(5).text().trim());
                $("#txtColor").val(cells.eq(6).text().trim());
                $("#txtFreshness").val(cells.eq(7).text().trim());
                $("#txtOdour").val(cells.eq(8).text().trim());
                $("#txtTexture").val(cells.eq(9).text().trim());
                $("#txtTemperature").val(cells.eq(10).text().trim());
                $("#txtCountPerLb").val(cells.eq(11).text().trim());
                $("#txtBigPieceperGram").val(cells.eq(12).text().trim());
                $("#txtSmallPieceperGram").val(cells.eq(13).text().trim());
                $("#txtUniformity").val(cells.eq(14).text().trim());
                $("#txtTotalPiecesperGram").val(cells.eq(15).text().trim());
                $("#txtDeterioration").val(cells.eq(16).text().trim());
                $("#txtDiscolouration").val(cells.eq(17).text().trim());
                $("#txtBlackSpots").val(cells.eq(18).text().trim());
                $("#txtSoftShell").val(cells.eq(19).text().trim());
                $("#txtHLQuantity").val(cells.eq(20).text().trim());
                $("#txtHangingMeat").val(cells.eq(21).text().trim());
                 $("#drpInspectedBy").val(cells.eq(22).text().trim()).trigger("change");
                $("#drpVerifiedBy").val(cells.eq(23).text().trim()).trigger("change");
                $("#txtRemarks").val(cells.eq(24).text().trim());
                 
                
                // Hidden values
                $("#hdnArrivalID").val(ArrivalID);
                $("#hdnInspectionID").val(InspectionID);
                $("#isEdit").val("1");
                $("#btnSave").html("Update");
            });


            //on delete button click
            $(document).on("click", ".btnDelete", function() {
                var row = $(this).closest("tr");
                var data = table.row(row).data();
                var InspectionID = $(this).data("id");
                console.log(InspectionID)
                if (confirm("Are you sure you want to delete this record?")) {
                    $.ajax({
                        url: 'Ajax/AjaxGradingInspection.php',
                        type: 'POST',
                        data: {
                            methodType: "delete",
                            InspectionID: InspectionID
                        },
                        success: function(response) {
                            if (response.Error) {
                                alert(response.Msg);
                            } else {
                                alert(response.Msg);
                                table.row(row).remove().draw();
                            }
                        }
                    });
                }
            });
            $(document).on("change", "#txtHONQuantity", function() {
                const honQuantity = $("#txtHONQuantity").val();
                const hlQuantity = $("#txtHLQuantity").val();

                if (!!honQuantity && !!hlQuantity && honQuantity > 0 && hlQuantity > 0) {
                    const yield = ((hlQuantity / honQuantity) * 100) || 0;
                    $("#txtYield").val(yield.toFixed(2));
                }
            });
            $(document).on("change", "#txtHLQuantity", function() {
                const honQuantity = $("#txtHONQuantity").val();
                const hlQuantity = $("#txtHLQuantity").val();

                if (!!honQuantity && !!hlQuantity && honQuantity > 0 && hlQuantity > 0) {
                    const yield = ((hlQuantity / honQuantity) * 100) || 0;
                    $("#txtYield").val(yield.toFixed(2));
                }
            });
            var validationRules = {
                rules: {
                    txtArrivalDate: {
                        required: true,
                        date: true
                    },
                    drpBatchCode: {
                        required: true
                    },
                    txtVariety: {
                        required: true
                    },
                    drpGrade: {
                        required: true
                    },
                    txtGeneralAppearance: {
                        required: true
                    },
                    txtColor: {
                        required: true
                    },
                    txtFreshness: {
                        required: true
                    },
                    txtOdour: {
                        required: true
                    },
                    txtTexture: {
                        required: true
                    },
                    txtTemperature: {
                        required: true
                    },
                    txtCountPerLb: {
                        required: true,
                        number: true
                    },
                    txtBigPieceperGram: {
                        required: true,
                        number: true
                    },
                    txtSmallPieceperGram: {
                        required: true,
                        number: true
                    },
                    txtUniformity: {
                        required: true
                    },
                    txtTotalPiecesperGram: {
                        required: true,
                        number: true
                    },
                    txtDeterioration: {
                        required: false
                    },
                    txtDiscolouration: {
                        required: false
                    },
                    txtBlackSpots: {
                        required: false
                    },
                    txtSoftShell: {
                        required: false
                    },
                    txtHONQuantity: {
                        required: true,
                        number: true
                    },
                    txtHLQuantity: {
                        required: true,
                        number: true
                    },
                    txtYield: {
                        required: true,
                        number: true
                    },
                    drpVerifiedBy: {
                        required: true
                    },
                    drpInspectedBy: {
                        required: true
                    },
                }
            };
            $("#frmGradingInspection").validate(validationRules);

        });


        function fnShowDataTable() {
            var excelHeader = "Grading Inspection Details";

            // Destroy existing DataTable if it exists
            if ($.fn.DataTable.isDataTable('#tblDataTable')) {
                $('#tblDataTable').DataTable().destroy();
            }

            // Initialize new DataTable with proper scrolling configuration
            table = $('#tblDataTable').DataTable({
                scrollY: "500px",
                scrollX: true,
                sScrollXInner: "100%",
                scrollCollapse: true,
                paging: false,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    [10, 25, 50, 100, "All"]
                ],
                responsive: false, // Disable responsive to maintain horizontal scroll
                autoWidth: true,
                columnDefs: [{
                        targets: '_all',
                        className: 'text-nowrap'
                    } // Prevent text wrapping
                ],
                language: {
                lengthMenu: "Show _MENU_ records",
                paginate: {
                    previous: "<i class='mdi mdi-chevron-left'>",
                    next: "<i class='mdi mdi-chevron-right'>"
                }
            },
            drawCallback: function () {
                $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
            },
            dom: '<"row mb-2"<"col-md-6 d-flex align-items-center"lf><"col-md-6 d-flex justify-content-end"B>>rtip',
            buttons:[]

            });
        }
        $(document).on("click", "#btnSave", function() {
            if ($("#frmGradingInspection").valid()) {
                var row = $(this).closest("tr");
                var data = table.row(row).data();
                var isEdit = $("#isEdit").val();
                var ArrivalID = $("#hdnArrivalID").val();
                var InspectionID = $("#hdnInspectionID").val();
                // console.log("ArrivalID: ",ArrivalID, "isEdit: ", isEdit)
                console.log($("#drpVerifiedBy").val())
                let formData = {
                    ArrivalDate: $("#txtArrivalDate").val(),
                    ArrivalID: ArrivalID,
                    InspectionID: InspectionID,
                    BatchCode: $("#drpBatchCode").val(),
                    Variety: $("#txtVariety").val(),
                    Grade: $("#drpGrade").val(),
                    GeneralAppearance: $("#txtGeneralAppearance").val(),
                    Color: $("#txtColor").val(),
                    Freshness: $("#txtFreshness").val(),
                    Odour: $("#txtOdour").val(),
                    Texture: $("#txtTexture").val(),
                    Temperature: $("#txtTemperature").val(),
                    CountPerLb: $("#txtCountPerLb").val(),
                    BigPieceperGram: $("#txtBigPieceperGram").val(),
                    SmallPieceperGram: $("#txtSmallPieceperGram").val(),
                    Uniformity: $("#txtUniformity").val(),
                    TotalPiecesperGram: $("#txtTotalPiecesperGram").val(),
                    Deterioration: $("#txtDeterioration").val(),
                    Discolouration: $("#txtDiscolouration").val(),
                    BlackSpots: $("#txtBlackSpots").val(),
                    SoftShell: $("#txtSoftShell").val(),
                    HONQuantity: $("#txtHONQuantity").val(),
                    HLQuantity: $("#txtHLQuantity").val(),
                    Yield: $("#txtYield").val(),
                    HangingMeat: $("#txtHangingMeat").val(),
                    InspectedBy: $("#drpInspectedBy").val(),
                    VerifiedBy: $("#drpVerifiedBy").val(),
                    Remarks: $("#txtRemarks").val(),
                    methodType: isEdit == "1" ? "update" : "post"
                };

                // console.log("Save",formData)

                $.ajax({
                    url: 'Ajax/AjaxGradingInspection.php',
                    type: 'POST',
                    data: formData,
                    success: function(data) {
                        if (data.Error) {
                            alert(data.Msg);
                        } else {
                            alert(data.Msg);
                            clearForm();
                            getGradingInspectionDetails()
                        }
                    }
                });
            } // ← Added missing closing brace for the if statement
        });
    </script>

    <script>
        function calculateUniformity() {
            const big = parseFloat($("#txtBigPieceperGram").val());
            const small = parseFloat($("#txtSmallPieceperGram").val());

            if (!isNaN(big) && !isNaN(small) && small !== 0) {
                const uniformity = big / small;
                $("#txtUniformity").val(uniformity.toFixed(2));
            } else {
                $("#txtUniformity").val('');
            }
        }

        $(document).ready(function() {
            $(document).on("input", "#txtBigPieceperGram, #txtSmallPieceperGram", function() {
                calculateUniformity();
            });
        });
    </script>
</body>

</html>
