<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$apiList = API_CURRENCY_MASTERS;

$CurrencyID = isset($_GET["CurrencyID"]) && !empty($_GET["CurrencyID"]) ? $_GET["CurrencyID"] : "";

$bodyContent = array(
    "CurrencyID" => $CurrencyID
);

if ($CurrencyID != '') {
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
                                <form role="form" method="post" id="frmCreateCurrency">
                                    <div class="row">

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtCurrencyName">Currency Name<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtCurrencyName' id="txtCurrencyName" placeholder="Enter Currency Name" value="<?php echo $Data->CurrencyName ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtCurrencyCode">Currency Code<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtCurrencyCode' id="txtCurrencyCode" placeholder="Enter Currency Code" value="<?php echo $Data->CurrencyCode ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtCurrencySymbol">Curreny Symbol<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtCurrencySymbol' id="txtCurrencySymbol" placeholder="Enter Currency Symbol" value="<?php echo $Data->CurrenySymbol ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtCurrencyShortCode">Currency Short Code</label>
                                            <input type="text" class="form-control" name='txtCurrencyShortCode' id="txtCurrencyShortCode" placeholder="Enter Currency Short Code" value="<?php echo $Data->CurrencyShortCode ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpStatus">Status<span style="color:red">*</span> </label>
                                            <select class="drpSelect" name="drpStatus" id="drpStatus">
                                                <?php if (isset($_GET['CurrencyID']) && !empty($_GET['CurrencyID'])) { ?>
                                                    <option value="1" <?php echo ($Data->Status == "1") ? 'selected' : ''; ?>>Active</option>
                                                    <option value="0" <?php echo ($Data->Status == "0") ? 'selected' : ''; ?>>In-Active</option>
                                                <?php } else { ?>
                                                    <option value="">All</option>
                                                    <option value="1" selected>Active</option>
                                                    <option value="0">In-Active</option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="text-center">
                                            <input type="hidden" id="hdnSaveType" name="hdnSaveType" value="<?php echo $CurrencyID ?>" />
                                            <input type="hidden" id="hdnNewVariety" name="hdnNewVariety" />
                                            <a href="currency-list" class="btn btn-danger">Close</a>
                                            <?php if ($CurrencyID == '') { ?>
                                                <button class="btn btn-primary btnSave" name="submit" id="submit">Submit</button>
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
        var currencyID = "<?php echo isset($_GET["CurrencyID"]) && !empty($_GET["CurrencyID"]) ? $_GET["CurrencyID"] : "" ?>";

        // function fnShowDataTable() {
        //     table = $('#tblDataTable').DataTable({
        //         scrollY: "600px",
        //         scrollX: true,
        //         sScrollXInner: "100%",
        //         paging: false,
        //         destroy: true,
        //         fixedColumns: true,
        //         scrollCollapse: true,
        //         dom: 'Blfrtip',
        //         dom: '<"top-left-buttons"Bf>rt<"bottom"lip>',
        //         buttons: [{
        //             extend: 'excel',
        //             text: 'Download Excel',
        //             className: 'btnExcelDataTable',
        //             title: 'Agent List - <?php echo date("d-m-Y") ?>',
        //             footer: true,
        //             exportOptions: {
        //                 columns: ':visible'
        //             }
        //         }],
        //     });
        // }

        // $(document).ready(function() {
        //     $.ajax({
        //         url: 'Ajax/ajax-cs-create-tenants.php',
        //         type: 'POST',
        //         data: {
        //             methodType: "get",
        //             tenantID: tenantID
        //         },
        //         success: function(data) {
        //             $("#divDataList").html(data);
        //             fnShowDataTable();
        //             return false;
        //         }
        //     });
        // });

        jQuery.validator.addMethod(
            "noSpace",
            function(value, element) {
                return value == "" || value.trim().length != 0;
            },
            "No space please and don't leave it empty"
        );
        $("#frmCreateCurrency").validate({

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
                txtCurrencyName: {
                    required: true,
                    noSpace: true
                },
                txtCurrencyCode: {
                    required: true,
                    noSpace: true
                },
                txtCurrencySymbol: {
                    required: true,
                    noSpace: true
                },
                // txtCurrencyShortCode: {
                //     required: true,
                //     noSpace: true
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
                    url: 'Ajax/ajax-currency-create.php',
                    type: 'POST',
                    data: {
                        // methodType: "post",
                        currencyID: currencyID,
                        currencyName: $("#txtCurrencyName").val(),
                        currencyCode: $("#txtCurrencyCode").val(),
                        currencySymbol: $("#txtCurrencySymbol").val(),
                        shortCode: $("#txtCurrencyShortCode").val(),
                        status: $("#drpStatus").val()
                    },
                    success: function(data) {
                        console.log(data);
                        if (data.Error != '') {
                            alert(data.Msg);
                            window.location.href = "currency-list.php";
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