<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_STORE_DROPDOWN;

$resDropdownsAPI = CallAPI($api, "GET", "", "");

$resChemicalName = $resDropdownsAPI->Data->ChemicalName;
$resCurrency = $resDropdownsAPI->Data->Currency;


$apiList = API_GS_CHEM_PO_BrkUP;

$ChemPoBreakUpID = trim($_GET['ChemPoBreakUpID']);

$bodyContent = array(
    "ChemPoBreakUpID" => $ChemPoBreakUpID,
);

if ($ChemPoBreakUpID != '') {
    $result = CallAPI($apiList, "GET", "", $bodyContent);

    $Data = $result->Data[0];
}

?>

<!doctype html>
<html lang="en">

<head>

    <?php include_once ROOT . '/includes/head.php' ?>

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
                                <form role="form" method="post" id="frmHlvaVariety">
                                    <div class="row">

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpChemical">Chemical<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpChemical" id="drpChemical">
                                                <option value="" <?php echo ($Data->ChemicalName == '') ? 'selected' : '';  ?>>Select</option>
                                                <?php foreach ($resChemicalName as $List) { ?>
                                                    <option value="<?php echo $List->ChemID ?>" <?php echo ($Data->ChemicalName == $List->ChemicalName) ? 'selected' : ''; ?>
                                                        data-chemicalqty="<?php echo $List->NoOfKgPerBag ?>">
                                                        <?php echo $List->ChemicalName ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtBags" class="form-label">Bags Quantity<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtBags" name="txtBags" placeholder="Enter Bags Quantity" value='<?php echo $Data->QuantityInKgs ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtQty" class="form-label">Quantity In Kgs<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtQty" name="txtQty" placeholder="Enter Quantity in KGs" value='<?php echo $Data->QuantityInKgs ?>' readonly />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpCurrencyType">Currency Type<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpCurrencyType" id="drpCurrencyType">
                                                <option value="" <?php echo ($Data->ChemicalName == '') ? 'selected' : '';  ?>>Select</option>
                                                <?php foreach ($resCurrency as $List) { ?>
                                                    <option value="<?php echo $List->CurrencyID ?>"
                                                        <?php echo ($Data->CurrencyType == $List->CurrencyID) ? 'selected' : '';
                                                        ?> data-currencyname="<?php echo $List->CurrencyName ?>"><?php echo $List->CurrenySymbol . ' ' . $List->CurrencyName ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtRate" class="form-label">Rate Per Kg <span id="txtCurrencyName"></span> <span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtRate" name="txtRate" placeholder="Enter Rate" value='<?php echo $Data->RatePerKgInRupees ?>' />
                                        </div>

                                        <div class="text-center">
                                            <input type="hidden" id="hdnSaveType" name="hdnSaveType" value="<?php echo $ChemPoBreakUpID ?>" />
                                            <input type="hidden" id="hdnNewBrkUp" name="hdnNewBrkUp" />
                                            <a href="GsChemPosList" class="btn btn-danger">Close</a>
                                            <?php if ($ChemPoBreakUpID == '') { ?>
                                                <button class="btn btn-primary btnSave" name="submit" id="submit">Save</button>
                                            <?php } else { ?>
                                                <button class="btn btn-success btnSave" name="submit" id="submit">Update</button>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js" type="text/javascript"></script>
    <script>
        var breakUpID = "<?php echo isset($_GET["ChemPoBreakUpID"]) && !empty($_GET["ChemPoBreakUpID"]) ? $_GET["ChemPoBreakUpID"] : "" ?>";
        var ChemPoID = "<?php echo isset($_GET["ChemPoID"]) && !empty($_GET["ChemPoID"]) ? $_GET["ChemPoID"] : "" ?>";

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
                    title: 'GS Chemical PO BreakUp -<?php echo date("d-m-Y") ?>',
                    footer: true,
                    exportOptions: {
                        columns: ':visible'
                    }
                }],
            });
        }

        function ShowList() {
            $.ajax({
                url: 'Ajax/AjaxGsChemPoBrkUpCreate.php',
                type: 'POST',
                data: {
                    methodType: "get",
                    ChemPoID: ChemPoID
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


        function getTotalQty() {
            var bagQty = $("#drpChemical option:selected").data("chemicalqty");
            var noofBags = $("#txtBags").val();
            var totalQty = bagQty * noofBags;
            totalQty = totalQty ? totalQty : '';
            $("#txtQty").val(totalQty);
        }

        $(document).on("keyup", "#txtBags", function() {
            getTotalQty();
        });

        $(document).on("change", "#drpChemical", function() {
            getTotalQty();
        });


        $(document).on("change", "#drpCurrencyType", function() {
            var currencyName = $(this).find("option:selected").data("currencyname");
            // console.log(currencyName);
            $("#txtCurrencyName").text('(' + currencyName + ')');
        });

        $(document).on('click', '.btnDelete', function() {
            var breakUpID = $(this).data('id');
            if (confirm('Are you sure you want to delete this record?')) {
                $.ajax({
                    url: 'Ajax/AjaxGsChemPoBrkUpCreate.php',
                    type: 'POST',
                    data: {
                        methodType: "delete",
                        breakUpID: breakUpID
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

        $("#frmHlvaVariety").validate({

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
                drpChemical: {
                    required: true,
                },
                txtQty: {
                    required: true,
                },
                txtBags: {
                    required: true,
                },
                txtRate: {
                    required: true,
                },
                drpCurrencyType: {
                    required: true,
                },

            },
            errorPlacement: function() {
                return false; // suppresses error message text
            },
            submitHandler: function() {

                $.ajax({
                    url: 'Ajax/AjaxGsChemPoBrkUpCreate.php',
                    type: 'POST',
                    data: {
                        methodType: "post",
                        breakUpID: breakUpID,
                        ChemPoID: ChemPoID,
                        chemical: $("#drpChemical").val(),
                        bags: $("#txtBags").val(),
                        quantity: $("#txtQty").val(),
                        currency: $("#drpCurrencyType").val(),
                        rate: $("#txtRate").val(),
                    },
                    success: function(data) {
                        $("#divDataList").html(data);
                        fnShowDataTable();

                        $("#txtQty, #txtRate, #txtBags").val("");

                        $("#drpChemical").val("").trigger('change');
                        $("#drpCurrencyType").val("").trigger('change');
                        return false;
                    }
                });
                return false;
            }
        });
    </script>
    </body>

</html>