<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_STORE_DROPDOWN;

$resDropdownsAPI = CallAPI($api, "GET", "", "");

$resCountry = $resDropdownsAPI->Data->Country;

$apiList = API_CHEMICAL_SUPPLIER;

$ChemSupplierID = trim($_GET['ChemSupplierID']);

$bodyContent = array(
    "ChemSupplierID" => $ChemSupplierID,
);

if ($ChemSupplierID != '') {
    $result = CallAPI($apiList, "GET", "", $bodyContent);

    $Data = $result->Data[0];
}
// echo json_encode($result);

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
                                <form role="form" method="post" id="frmSupplier">
                                    <div class="row">

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtSupplierName" class="form-label">Supplier Name<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtSupplierName" name="txtSupplierName" placeholder="Enter Supplier Name" value='<?php echo $Data->SupplierName ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtSupplierAdd" class="form-label">Supplier Address<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtSupplierAdd" name="txtSupplierAdd" placeholder="Enter Supplier Address" value='<?php echo $Data->SupplierAddress ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtSupplierPhone" class="form-label">Supplier Phone Number<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtSupplierPhone" name="txtSupplierPhone" placeholder="Enter Supplier Phone Number" value='<?php echo $Data->SupplierPhoneNumber ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpCountry">Country<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpCountry" id="drpCountry">
                                                <option value="" <?php echo ($Data->CountryName == '') ? 'selected' : '';  ?>>Select</option>
                                                <?php foreach ($resCountry as $List) { ?>
                                                    <option value="<?php echo $List->id ?>" <?php echo ($Data->CountryName == $List->name) ? 'selected' : ''; ?>>
                                                    <?php echo $List->name ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtSupplierEmail" class="form-label">Supplier Email</label>
                                            <input type="text" class="form-control" id="txtSupplierEmail" name="txtSupplierEmail" placeholder="Enter Supplier Email Number" value='<?php echo $Data->SupplierEmailAddress ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpStatus">Status<span style="color:red">*</span> </label>
                                            <select class="drpSelect" name="drpStatus" id="drpStatus">
                                                <?php
                                                if (isset($_GET['ChemBuyerID']) && !empty($_GET['ChemBuyerID'])) {

                                                    echo '<option value="1" ' . (($Data->Status == 1) ? 'selected' : '') . '>Active</option>
                                                    <option value="0" ' . (($Data->Status == 0) ? 'selected' : '') . '  >In-Active</option>';
                                                } else {
                                                    echo '
                                                    <option value="">All</option>
                                                    <option value="1" selected>Active</option>
                                                    <option value="0">In-Active</option>';
                                                }
                                                ?>

                                            </select>
                                        </div>

                                        <div class="text-center">
                                            <input type="hidden" id="hdnSaveType" name="hdnSaveType" value="<?php echo $ChemBuyerID ?>" />
                                            <input type="hidden" id="hdnNewVariety" name="hdnNewVariety" />
                                            <a href="GsChemSuppliersList" class="btn btn-danger">Close</a>
                                            <?php if ($ChemBuyerID == '') { ?>
                                                <button class="btn btn-primary btnSave" name="submit" id="submit">Save</button>
                                            <?php } else { ?>
                                                <button class="btn btn-success btnSave" name="submit" id="submit">Update</button>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js" type="text/javascript"></script>
    <script>
        var chemSupplierID = "<?php echo isset($_GET["ChemSupplierID"]) && !empty($_GET["ChemSupplierID"]) ? $_GET["ChemSupplierID"] : "" ?>";

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
                    title: 'GS Chemical Buyers -<?php echo date("d-m-Y") ?>',
                    footer: true,
                    exportOptions: {
                        columns: ':visible'
                    }
                }],
            });
        }

        // $(document).ready(function() {
        //     $.ajax({
        //         url: 'Ajax/AjaxGsChemSupplierCreate.php',
        //         type: 'POST',
        //         data: {
        //             methodType: "get",
        //             chemBuyerID: chemBuyerID
        //         },
        //         success: function(data) {
        //             $("#divDataList").html(data);
        //             fnShowDataTable();
        //             return false;
        //         }
        //     });
        // });

        $("#frmSupplier").validate({

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
                txtSupplierName: {
                    required: true,
                },
                txtSupplierAdd: {
                    required: true,
                },
                txtSupplierPhone: {
                    required: true,
                },
                drpCountry: {
                    required: true,
                },
                // txtSupplierEmail: {
                //     required: true,
                // },
                drpStatus: {
                    required: true,
                },

            },
            errorPlacement: function() {
                return false; // suppresses error message text
            },
            submitHandler: function() {

                $.ajax({
                    url: 'Ajax/AjaxGsChemSupplierCreate.php',
                    type: 'POST',
                    data: {
                        // methodType: "post",
                        chemSupplierID: chemSupplierID,
                        supplierName: $("#txtSupplierName").val(),
                        supplierAdd: $("#txtSupplierAdd").val(),
                        supplierPhone: $("#txtSupplierPhone").val(),
                        country: $("#drpCountry").val(),
                        supplierEmail: $("#txtSupplierEmail").val(),
                        status: $("#drpStatus").val()
                    },
                    success: function(data) {
                        console.log(data);
                        if (data.Error != '') {
                            alert(data.Msg);
                            window.location.href = "GsChemSuppliersList.php";
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