<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$apiList = API_RM_FINANCIER_LIST;

$ID = trim($_GET['ID']);

$ClientID = $_SESSION['ClientID'];

$bodyContent = array(
    "ID" => $ID
);

if ($ID != '') {
    $result = CallAPI($apiList, "GET", "", $bodyContent);

    // var_dump($result);

    $Data = $result->Data[0];
}


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
                                <form role="form" method="post" id="frmFinancier">
                                    <div class="row">

                                        <div class="form-group col-sm-3">
                                            <label for="drpFinancierType" class="form-label">Financier Type <span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpFinancierType" id="drpFinancierType"
                                                style="z-index: 2000;" value="<?php echo $Data->SupplierType ?>">
                                                <option value="" <?php echo ($Data->SupplierType == '') ? 'selected' : '' ?>> Select</option>
                                                <option value="Individual" <?php echo ($Data->SupplierType == 'Individual') ? 'selected' : '' ?>>Individual
                                                </option>
                                                 <?php if ($ClientID === 'DFLV3' || $ClientID === 'CAMAROV3') { ?>
                                                <option value="Firm" <?php echo ($Data->SupplierType == 'Firm') ? 'selected' : '' ?>>Firm</option>
                                                <?php }else{ ?>
                                                    <option value="Business" <?php echo ($Data->SupplierType == 'Business') ? 'selected' : '' ?>>Business</option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtFinancierName">Financier Name<span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtFinancierName'
                                                id="txtFinancierName" placeholder="Enter Financier Name"
                                                value="<?php echo $Data->FinancierName ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtPanTNumber">PAN Number</label>
                                            <input type="text" class="form-control" name="txtPanNumber"
                                                id="txtPanNumber" maxlength="10" placeholder="Enter PAN Number"
                                                value="<?php echo $Data->PanNumber ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpCompany">Company</label>
                                            <select class="drpSelect" name="drpCompany" id="drpCompany">
                                                <?php if (isset($_GET['ID']) && !empty($_GET['ID'])) {
                                                    echo '<option value="0" ' . (($Data->IsCompany == 0) ? 'selected' : '') . '>Non-Company</option>
                                                   <option value="1" ' . (($Data->IsCompany == 1) ? 'selected' : '') . '>Company</option>';
                                                } else {
                                                    echo '<option value="0" selected>Non-Company</option>
                                                    <option value="1">Company</option>';
                                                } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtGstNumber">GST Number</label>
                                            <input type="text" class="form-control" name="txtGstNumber"
                                                id="txtGstNumber" maxlength="15" placeholder="Enter GST Number"
                                                value="<?php echo $Data->GstNo ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtBankName">Financier Bank Name</label>
                                            <input type="text" class="form-control" name="txtBankName" id="txtBankName"
                                                placeholder="Enter Bank Name " value="<?php echo $Data->BankName ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtBankAccNo">Bank Account Number</label>
                                            <input type="text" class="form-control" name="txtBankAccNo"
                                                id="txtBankAccNo" placeholder="Enter Bank Account Number "
                                                value="<?php echo $Data->BankAccNo ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtIfsc">IFSC Code</label>
                                            <input type="text" class="form-control" name="txtIfsc" id="txtIfsc"
                                                placeholder="Enter IFSC Code" value="<?php echo $Data->IFSC ?>">
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtFinancierAddress" class="form-label">Financier Address
                                            </label>
                                            <input type="text" class="form-control" id="txtFinancierAddress"
                                                name="txtFinancierAddress" placeholder="Enter Financier Address"
                                                value="<?php echo $Data->Address ?>" />
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtFinancierEMailID" class="form-label">Financier EMail
                                                ID</label>
                                            <input type="text" class="form-control" id="txtFinancierEMailID"
                                                name="txtFinancierEMailID" placeholder="Enter Financier EMail ID"
                                                value="<?php echo $Data->EMailID ?>" />
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtPhoneNumber" class="form-label">Phone Number </label>
                                            <input type="text" class="form-control" id="txtPhoneNumber"
                                                name="txtPhoneNumber" placeholder="Enter Phone Number"
                                                value="<?php echo $Data->PhoneNumber ?>" />
                                        </div>

                                        <div class="text-center">
                                            <input type="hidden" id="hdnSaveType" name="hdnSaveType"
                                                value="<?php echo $ID ?>" />
                                            <input type="hidden" id="hdnNewVariety" name="hdnNewVariety" />
                                            <a href="financier-list" class="btn btn-danger">Close</a>
                                            <?php if ($ID == '') { ?>
                                                <button class="btn btn-primary btnSave" name="submit"
                                                    id="submit">Submit</button>
                                            <?php } else { ?>
                                                <button class="btn btn-success btnSave" name="submit"
                                                    id="submit">Update</button>
                                            <?php } ?>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"
        type="text/javascript"></script>
    <script>
        var id = "<?php echo isset($_GET["ID"]) && !empty($_GET["ID"]) ? $_GET["ID"] : "" ?>";

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
                    title: 'Financier List -<?php echo date("d-m-Y") ?>',
                    footer: true,
                    exportOptions: {
                        columns: ':visible'
                    }
                }],
            });
        }

        $(document).ready(function () {
            $.ajax({
                url: 'Ajax/ajax-create-financieer.php',
                type: 'POST',
                data: {
                    methodType: "get",
                    id: id
                },
                success: function (data) {
                    $("#divDataList").html(data);
                    fnShowDataTable();
                    return false;
                }
            });
        });

        jQuery.validator.addMethod(
            "noSpace",
            function (value, element) {
                return value == "" || value.trim().length != 0;
            },
            "No space please and don't leave it empty"
        );
        $("#frmFinancier").validate({

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
                txtFinancierName: {
                    required: true,
                },
                // txtGstNumber: {
                //     required: true,
                // },
                // txtPanNumber: {
                //     required: true,
                //     noSpace: true
                // },
                // txtBankName: {
                //     required: true,
                //     noSpace: true
                // },
                // txtBankAccNo: {
                //     required: true,
                //     noSpace: true
                // },
                // txtIfsc: {
                //     required: true,
                // },

            },
            errorPlacement: function () {
                return false; // suppresses error message text
            },
            submitHandler: function () {

                $.ajax({
                    url: 'Ajax/ajax-create-financieer.php',
                    type: 'POST',
                    data: {
                        methodType: "post",
                        id: id,
                        financierType: $("#drpFinancierType").val(),
                        financierName: $("#txtFinancierName").val(),
                        gstNumber: $("#txtGstNumber").val(),
                        panNumber: $("#txtPanNumber").val(),
                        bankName: $("#txtBankName").val(),
                        bankAccNo: $("#txtBankAccNo").val(),
                        ifsc: $("#txtIfsc").val(),
                        company: $("#drpCompany").val(),
                        financierAddress: $("#txtFinancierAddress").val(),
                        financierEmail: $("#txtFinancierEMailID").val(),
                        phoneNumber: $("#txtPhoneNumber").val(),
                    },
                    success: function (data) {
                        $("#divDataList").html(data);
                        fnShowDataTable();
                        return false;
                    }
                });

                return false;
            }
        });
    </script>

    </body>

</html>
