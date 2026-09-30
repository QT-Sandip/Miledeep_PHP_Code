<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_COLDSTORE_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", "");

$resVariety = $resDropdownsAPI->Data->Variety;
$resStock = $resDropdownsAPI->Data->Stock;
$resPackingType = $resDropdownsAPI->Data->PackingType;

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
                                <form role="form" method="post" id="frmIbtRequestBreakUp">
                                    <div class="row">

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpVariety">Variety<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpVariety" id="drpVariety">
                                                <option value="">Select</option>
                                                <?php foreach ($resVariety as $List) { ?>
                                                    <option value="<?php echo $List->VarietyID ?>"> <?php echo $List->FullName ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpSupc">SUPC<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpSupc" id="drpSupc">
                                                <option value="">Select</option>
                                                <?php foreach ($resVariety as $List) { ?>
                                                    <option value="<?php echo $List->DeviSUPC ?>"><?php echo $List->DeviSUPC ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpStock">Stock<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpStock" id="drpStock">
                                                <option value="">Select</option>
                                                <?php foreach ($resStock as $List) { ?>
                                                    <option value="<?php echo $List->StockID ?>"><?php echo $List->AvalMcs ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpPoID">PoID<span style="color:red">*</span> </label>
                                            <select class="drpSelect" name="drpPoID" id="drpPoID">
                                                <option value="">Select</option>
                                                <option value="1">DUMMY</option>
                                                <option value="2">MASTER</option>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpPackingType">Packing Type<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpPackingType" id="drpPackingType">
                                                <option value="">Select</option>
                                                <?php foreach ($resPackingType as $List) { ?>
                                                    <option value="<?php echo $List->OptID ?>"><?php echo $List->Name ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtMcs" class="form-label">NoOf Mcs<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtMcs" name="txtMcs" placeholder="Enter NoOf Mcs" />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtBags" class="form-label">NoOf Loose Bags<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtBags" name="txtBags" placeholder="Enter NoOf Bags" />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpStatus">Status<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpStatus" id="drpStatus">
                                                <option value="">All</option>
                                                <option value="1" selected>Active</option>
                                                <option value="0">In-Active</option>;
                                            </select>
                                        </div>

                                        <div class="text-center">
                                            <input type="hidden" id="hdnSaveType" name="hdnSaveType" value="<?php echo $IBTID ?>" />
                                            <input type="hidden" id="hdnNewVariety" name="hdnNewVariety" />
                                            <a href="CsIBTRequestList" class="btn btn-danger">Close</a>
                                            <button class="btn btn-primary btnSave" name="submit" id="submit">Save</button>
                                        </div>

                                    </div>
                                </form>

                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div id="divDataList">

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
        var ibtID = "<?php echo isset($_GET["IBTID"]) && !empty($_GET["IBTID"]) ? $_GET["IBTID"] : "" ?>";
        var ibtBreakupID = "<?php echo isset($_GET["IBTBreakupID "]) && !empty($_GET["IBTBreakupID "]) ? $_GET["IBTBreakupID "] : "" ?>";

        function fnShowDataTable() {
            table = $('#tblDataTable').DataTable({
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
                    text: 'Download Excel',
                    className: 'btnExcelDataTable',
                    title: 'Cs IBT Request BreakUp -<?php echo date("d-m-Y") ?>',
                    footer: true,
                    exportOptions: {
                        columns: ':visible'
                    }
                }],
            });
        }

        function ShowList() {
            $.ajax({
                url: 'Ajax/AjaxCsIBTRequestBreakUp.php',
                type: 'POST',
                data: {
                    methodType: "get",
                    ibtID: ibtID
                },
                success: function(data) {
                    $("#divDataList").html(data);
                    fnShowDataTable();
                    return false;
                }
            });
        };

        $(document).ready(function() {
            ShowList();
        });

        $(document).on('click', '.btnDelete', function() {
            var ibtBreakUpID = $(this).data('id');
            if (confirm('Are you sure you want to delete this record?')) {
                $.ajax({
                    url: 'Ajax/AjaxCsIBTRequestBreakUp.php',
                    type: 'POST',
                    data: {
                        methodType: "delete",
                        ibtBreakUpID: ibtBreakUpID
                    },
                    success: function(data) {
                        ShowList();
                    },
                    // error: function(xhr, status, error) {
                    //     console.error(error);
                    // }
                });

            }
        });

        $("#frmIbtRequestBreakUp").validate({

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
                drpVariety: {
                    required: true,
                },
                drpSupc: {
                    required: true,
                },
                drpStock: {
                    required: true,
                },
                drpPoID: {
                    required: true,
                },
                drpPackingType: {
                    required: true,
                },
                txtMcs: {
                    required: true,
                },
                txtBags: {
                    required: true,
                },
                drpStatus: {
                    required: true,
                },
            },
            errorPlacement: function() {
                return false;
            },
            submitHandler: function() {

                $.ajax({
                    url: 'Ajax/AjaxCsIBTRequestBreakUp.php',
                    type: 'POST',
                    data: {
                        methodType: "post",
                        ibtID: ibtID,
                        ibtBreakupID: ibtBreakupID,
                        variety: $("#drpVariety").val(),
                        supc: $("#drpSupc").val(),
                        stock: $("#drpStock").val(),
                        poID: $("#drpPoID").val(),
                        packingType: $("#drpPackingType").val(),
                        mcs: $("#txtMcs").val(),
                        bags: $("#txtBags").val(),
                        status: $("#drpStatus").val()
                    },
                    success: function(data) {
                        // $("#divDataList").html(data);
                        ShowList();

                        $("#txtMcs, #txtBags").val("");
                        $("#drpVariety, #drpSupc, #drpStock, #drpPoID, #drpPackingType, #drpStatus").val("").trigger('change');
                        return false;
                    }
                });

                return false;
            }
        });
    </script>

    </body>

</html>