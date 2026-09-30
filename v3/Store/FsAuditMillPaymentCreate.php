<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_STORE_DROPDOWN;

$resDropdownsAPI = CallAPI($api, "GET", "", "");

$resAuditPaymt = $resDropdownsAPI->Data->AuditPaymt;

$apiList = API_FS_AUDIT;

$FsaID = trim($_GET['FsaID']);

$bodyContent = array(
    "FsaID" => $FsaID,
);

if ($FsaID != '') {
    $result = CallAPI($apiList, "GET", "", $bodyContent);

    $Data = $result->Data[0];
}


// $CurrentMonth = date('m');
// $CurrentYear = date('Y');

// if ($CurrentMonth > 3) {
//     $PastFY = $CurrentYear - 1;
//     $CurrentFY = $CurrentYear;
// } else {
//     $PastFY = $CurrentYear - 2;
//     $CurrentFY = $CurrentYear - 1;
// }


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
                                <form role="form" method="post" id="frmMillPayment">
                                    <div class="row">

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpPaymtType">Payment Type<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpPaymtType" id="drpPaymtType">
                                                <option value="">Select</option>
                                                <?php foreach ($resAuditPaymt as $List) { ?>
                                                    <option value="<?php  echo $List->PaymentID  ?>"><?php  echo $List->PaymentName ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtInvDate" class="form-label">Invoice Date<span style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtInvDate" name="txtInvDate"/>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtInvNo" class="form-label">Invoice Number<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtInvNo" name="txtInvNo" placeholder="Enter Invoice Number" />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtInvVal" class="form-label">Invoice Value<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtInvVal" name="txtInvVal" placeholder="Enter Invoice Value" />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtPaymtDate" class="form-label">Payment Date<span style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtPaymtDate" name="txtPaymtDate" />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtAmount" class="form-label">Total Amount<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtAmount" name="txtAmount" placeholder="Enter Amount" />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtRemarks" class="form-label">Remarks</label>
                                            <input type="text" class="form-control" id="txtRemarks" name="txtRemarks" placeholder="Enter Remarks" />
                                        </div>

                                        <div class="text-center">
                                            <input type="hidden" id="hdnSaveType" name="hdnSaveType" value="<?php echo $FsaID ?>" />
                                            <input type="hidden" id="hdnNewVariety" name="hdnNewVariety" />
                                            <a href="FsAuditMillList" class="btn btn-danger">Close</a>
                                            <?php // if ($FsaID == '') { 
                                            ?>
                                            <button class="btn btn-primary btnSave" name="submit" id="submit">Save</button>
                                            <?php // } else { 
                                            ?>
                                            <!-- <button class="btn btn-success btnSave" name="submit" id="submit">Update</button> -->
                                            <?php // } 
                                            ?>
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
        var paymentID = "<?php echo isset($_GET["PaymentID"]) && !empty($_GET["PaymentID"]) ? $_GET["PaymentID"] : "" ?>";
        var fhfAuditID = "<?php echo isset($_GET["FHFAuditID"]) && !empty($_GET["FHFAuditID"]) ? $_GET["FHFAuditID"] : "" ?>";

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
                    title: 'FS Audit Mill Payment -<?php echo date("d-m-Y") ?>',
                    footer: true,
                    exportOptions: {
                        columns: ':visible'
                    }
                }],
            });
        }

        function ShowList() {
            $.ajax({
                url: 'Ajax/AjaxFsAuditMillPaymentCreate.php',
                type: 'POST',
                data: {
                    methodType: "get",
                    fhfAuditID: fhfAuditID
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
            var paymentID = $(this).data('id');
            if (confirm('Are you sure you want to delete this record?')) {
                $.ajax({
                    url: 'Ajax/AjaxFsAuditMillPaymentCreate.php',
                    type: 'POST',
                    data: {
                        methodType: "delete",
                        paymentID: paymentID
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

        $("#frmMillPayment").validate({

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
                drpPaymtType: {
                    required: true,
                },
                txtInvDate: {
                    required: true,
                },
                txtInvNo: {
                    required: true,
                },
                txtInvVal: {
                    required: true,
                },
                txtPaymtDate: {
                    required: true,
                },
                txtAmount: {
                    required: true,
                },
            },
            errorPlacement: function() {
                return false;
            },
            submitHandler: function() {

                $.ajax({
                    url: 'Ajax/AjaxFsAuditMillPaymentCreate.php',
                    type: 'POST',
                    data: {
                        methodType: "post",
                        paymentID: paymentID,
                        fhfAuditID: fhfAuditID,
                        paymentType: $("#drpPaymtType").val(),
                        invDate: $("#txtInvDate").val(),
                        invNo: $("#txtInvNo").val(),
                        invVal: $("#txtInvVal").val(),
                        paymtDate: $("#txtPaymtDate").val(),
                        amount: $("#txtAmount").val(),
                        remarks: $("#txtRemarks").val(),
                    },
                    success: function(data) {
                        $("#divDataList").html(data);
                        fnShowDataTable();

                        $("#txtInvDate, #txtInvNo, #txtPaymtDate, #txtInvVal, #txtAmount, #txtRemarks").val("");
                        $("#drpPaymtType").val("").trigger('change');

                        return false;

                    }
                });

                return false;
            }
        });
    </script>

    </body>

</html>