<?php
$PageTitleFolder = "Master";
$PageTitle = "Liner Contract";

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($_SERVER['DOCUMENT_ROOT'] . "/includes/Globals.php");

$SqlLinerMaster = "SELECT * FROM `ScrmLinerMaster`";
$StmtLinerMaster = $iconn->query($SqlLinerMaster);
$SqlCountries = "SELECT `id`,`name` FROM `ScrmCountriesList`";
$StmtCountries = $iconn->query($SqlCountries);

?>

<!doctype html>
<html lang="en">

<link href="https://serp.silverlights.in/Clients/Jlsf/User.css" rel="stylesheet" type="text/css" />

<head>

    <?php include($IncludesPath . "title-meta.php"); ?>

    <!-- DataTables -->
    <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css"
        rel="stylesheet" type="text/css" />
    <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css"
        rel="stylesheet" type="text/css" />
    <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-select-bs4/css//select.bootstrap4.min.css"
        rel="stylesheet" type="text/css" />

    <!-- Responsive datatable examples -->
    <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css"
        rel="stylesheet" type="text/css" />


    <link href="<?php echo $BaseDist ?>assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css"
        rel="stylesheet">
    <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css"
        rel="stylesheet">
    <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css"
        rel="stylesheet" />
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
                    <?php include "PopUpTable.php"; ?>


                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <form role="form" method="post" id="formLinerContract">
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
                                                <input type="hidden" id="hdnID" name="hdnID" value="" />
                                            </div>
                                            <div class="form-group col-sm-3">
                                                <label for="CountryId">Country<span style="color:red">*</span></label>
                                                <select required class="form-control select2" name="CountryId"
                                                    id="CountryId">
                                                    <option value=""> Select </Option>
                                                    <?php while ($RowCountries = $StmtCountries->fetch_array()) { ?>
                                                        <option <?php if ($FreightRatesRow['CountryId'] == $RowCountries['id']) { ?>
                                                                selected="selected" <?php } ?>
                                                            value="<?php echo $RowCountries['id']; ?>">
                                                            <?php echo $RowCountries['name']; ?>
                                                        </option>
                                                    <?php } ?>

                                                </select>
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Contract Number<span
                                                        style="color:red">*</span></label>
                                                <input required type="text" class="form-control" name='ContractNumber'
                                                    id="ContractNumber" placeholder="Enter Forward Contract Number"
                                                    value="<?php echo $RowFCDtls['ContractNumber'] ?>">
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Value<span
                                                        style="color:red">*</span></label>
                                                <input required type="number" class="form-control" name='Value'
                                                    id="Value" placeholder="Enter Value"
                                                    value="<?php echo $RowFCDtls['Value'] ?>">
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label>Period </label>
                                                <select class="form-control select2" name="Period" id="Period">
                                                    <?php
                                                    for ($Startyear; $Startyear <= $endYear + 3; $Startyear++) {
                                                        $year = ($Startyear - 1) . '-' . ($Startyear); ?>

                                                        <option <?php if ($Startyear == $Period) { ?> selected="selected"
                                                            <?php } ?> value="<?php echo $Startyear - 1 ?>">
                                                            <?php echo $year ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <div class="col-lg-3">
                                                <div class="form-group">
                                                    <label for="exampleInputEmail1">Month </label>
                                                    <select class="form-control select2" name="Month" id="Month">
                                                        <?php
                                                        for ($i = 0; $i < 12; $i++) {
                                                            $time = strtotime(sprintf('%d months', $i), $start);
                                                            $label = date('F', $time);
                                                            $value = date('n', $time); ?>
                                                            <option <?php if ($RowFCDtls['Month'] == $value) { ?>
                                                                    selected="selected" <?php } ?>
                                                                value="<?php echo $value; ?>"><?php echo $label; ?></option>
                                                        <?php } ?>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Start Date<span
                                                        style="color:red">*</span></label>
                                                <input required type="Date" onkeydown="return false"
                                                    class="form-control" name='StartDate' id="StartDate"
                                                    placeholder="Enter Collection Rate"
                                                    value="<?php echo $RowFCDtls['StartDate'] ?>">
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">End Date<span
                                                        style="color:red">*</span></label>
                                                <input required type="Date" onkeydown="return false"
                                                    class="form-control" name='EndDate' id="EndDate"
                                                    placeholder="Enter End Dt"
                                                    value="<?php echo $RowFCDtls['EndDate'] ?>">
                                            </div>
                                            <div align="right" class="col-lg-12 mt-4">
                                                <div class="col-xs-12 box-footer text-right">
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

                                                <!-- <div class="form-group col-sm-3 d-flex align-items-end justify-content-end">
                                <div class="col-xs-12 box-footer text-right"> 
                                <button type="submit" name="submit" class="btn btn-success">
                                <?php
                                if ($ID == '') {
                                    echo "Submit";
                                }
                                ?>
                            </button>
                            </div> -->
                                            </div>
                                        </div>
                                </form>
                            </div>


                        </div>
                        <!-- end row -->
                    </div>
                </div>
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body" id="formLinerContract">
                                <div id="divDataList"></div>

                            </div> <!-- end card body-->
                        </div> <!-- end card -->
                    </div><!-- end col-->
                </div>
            </div>

            <!-- end col -->
        </div> <!-- end row -->

        <div class="contasiner-fluid">

        </div>
        <!-- end row-->


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

    <?php include($IncludesPath . "vendor-scripts.php"); ?>
    <?php include($IncludesPath . "NewScripts.php"); ?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"
        type="text/javascript"></script>

    <script>

        $(document).ready(function () {
            loadData();
            function loadData() {
                $.ajax({
                    url: 'Ajax/AjaxLinerContract.php',
                    type: 'POST',
                    data: {
                        MethodType: "GET",
                    },
                    success: function (data) {
                        fnSuccess(data);
                        return false;
                    }
                });


            }


            $(document).on("click", ".btnEdit", function () {
                if (confirm('Are you sure do you want to edit ?')) {
                    var row = $(this).closest("tr");
                    // debugger
                    var LinerContractId = $(this).attr("data-id");
                    var tdLiner = row.find(".tdLiner").data("value");
                    var tdCountry = row.find(".tdCountry").data("value");
                    var tdContarctNumber = row.find(".tdContarctNumber").data("value");
                    var tdValue = row.find(".tdValue").data("value");
                    var tdPeriod = row.find(".tdPeriod").data("value");
                    var tdMonth = row.find(".tdMonth").data("value");
                    var tdStartDate = row.find(".tdStartDate").attr("data-date");
                    var tdEndDate = row.find(".tdEndDate").attr("data-date");

                    $("#hdnID").val(LinerContractId);
                    // $("#liner_id").val(tdLiner);
                    $('#liner_id').select2('destroy');
                    $('#liner_id').val(tdLiner);
                    $('#liner_id').select2();
                    $('#CountryId').select2('destroy').val(tdCountry).select2();
                    $("#ContractNumber").val(tdContarctNumber).trigger("change");
                    $("#Value").val(tdValue).trigger("change");
                    $("#Period").val(tdPeriod).trigger("change");
                    $("#Month").val(tdMonth).trigger("change");
                    // $('#Month').select2('destroy');
                    // $('#Month').val(tdMonth);
                    // $('#Month').select2();
                    $("#StartDate").val(tdStartDate);
                    $("#EndDate").val(tdEndDate);
                    $("button[name='submit']").text("Update").removeClass("btn-success").addClass("btn-primary");
                }
            });


            function fnSuccess(data) {

                $("#divDataList").html(data);
                fnShowDataTable();
            }

            function fnShowDataTable() {
                var table = $('#tblDataTable').DataTable({
                    scrollY: "600px",
                    scrollX: true,
                    sScrollXInner: "100%",
                    paging: false,
                    destroy: true,
                    fixedColumns: true,
                    scrollCollapse: true,
                    bInfo: false
                });
            }
            $("#formLinerContract").validate({
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
                    ContractNumber: {
                        required: true
                    },
                    Value: {
                        required: true
                    },
                    Period: {
                        required: true
                    },
                    Month: {
                        required: true
                    },
                    StartDate: {
                        required: true
                    },
                    EndDate: {
                        required: true
                    },

                },
                messages: {
                    liner_id: {
                        required: "Please select a liner."
                    },
                    ContractNumber: {
                        required: "Please select a Contract Number."
                    },
                    Value: {
                        required: "Please select a net value."
                    },
                    Period: {
                        required: "Please select a Period."
                    },
                    Month: {
                        required: "Please select a month."
                    },
                    StartDate: {
                        required: "Please select the start date.",
                        date: "Please enter a valid date."
                    },
                    EndDate: {
                        required: "Please select the end date.",
                        date: "Please enter a valid date."
                    },


                },

                errorPlacement: function () {
                    return false; // suppresses error message text
                },
                submitHandler: function () {
                    // debugger
                    $.ajax({
                        url: 'Ajax/AjaxLinerContract.php',
                        type: 'POST',
                        data: {
                            MethodType: "POST",
                            LinerContractID: $("#hdnID").val(),
                            liner: $("#liner_id").val(),
                            contractNumber: $('#ContractNumber').val(),
                            value: $('#Value').val(),
                            period: $('#Period').val(),
                            month: $('#Month').val(),
                            startDate: $("#StartDate").val(),
                            endDate: $("#EndDate").val(),
                            CountryId: $("#CountryId").val(),
                        },
                        success: function (data) {
                            // fnSuccess(data);
                            loadData();
                            resetForm();
                            return false;
                        }
                    });
                }


            });
            function resetForm() {
                $("#formLinerContract")[0].reset();
                $("#hdnID").val("");
                $('#liner_id,#CountryId').val("").trigger('change');
                // $("#Period").val("").trigger("change");
                // $("#Month").val("").trigger("change");
                $("button[name='submit']").text("Save").removeClass("btn-primary").addClass("btn-success");
            }

        });

    </script>
</body>

</html>