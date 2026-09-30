<?php

session_start();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_COLDSTORE_DROPDOWNS;

$resList = callAPI($api, "GET", "", "");

$resColdStores = $resList->Data->ColdStores;
$resStockBelongsTo = $resList->Data->StockBelongsTo;
$resPackingType = $resList->Data->PackingType;
$resProductGroup = $resList->Data->ProductGroup;
$resCsTransactionTypes = $resList->Data->CsTransactionTypes;

// echo json_encode($resStockBelongsTo, JSON_PRETTY_PRINT);


$TodayDate = date('Y-m-d');

$ProductionPlant = $_SESSION['UnitID'];
// echo $ProductionPlant;

// var_dump($ColdStores);
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
                                <form role="form" method="post" id="frmDailyProdList">
                                    <div class="row">
                                        
                                            <div class="col-md-3 mb-2">
                                            <label for="txtSupc">SUPC<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtSupc" name="txtSupc" >
                                        </div>


                                        <div class="col-md-3 mb-2">
                                            <label for="txtProductionDate">Production Date<span style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtProductionDate" name="txtProductionDate" max="<?php echo $TodayDate; ?>" value="<?php echo $TodayDate; ?>">
                                        </div>

                                        <!-- <div class="form-group col-sm-3 mb-3">
                                            <label for="drpColdStores">Cold Stores</label>
                                            <select class="drpSelect" name="drpColdStores" id="drpColdStores">
                                                <option value=""> Select</option>
                                                <?php foreach ($resColdStores as $Item) { ?>
                                                    <option value="<?php echo $Item->StoreID ?>">
                                                        <?php echo $Item->StoreName ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div> -->

                                        <div class="col-md-3 mb-2">
                                            <label for="drpStockBlnTo">Stock Belongs to</label>
                                            <select class="drpSelect form-control" name="drpStockBlnTo" id="drpStockBlnTo">
                                                <option value=""> Select</option>
                                                <?php foreach ($resStockBelongsTo as $Item) { ?>
                                                    <option value="<?php echo $Item->TenantID ?>" data-processorID="<?php echo $Item->ProcessorID; ?>">
                                                        <?php echo $Item->TenantName ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-3 mb-2">
                                            <label for="drpPackingType">Packing Type</label>
                                            <select class="drpSelect form-control" name="drpPackingType" id="drpPackingType">
                                                <option value=""> Select</option>
                                                <?php foreach ($resPackingType as $Item) { ?>
                                                    <option value="<?php echo $Item->Name ?>">
                                                        <?php echo $Item->Name ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-3 mb-2">
                                            <label for="drpGroup">Group</label>
                                            <select class="drpSelect form-control" name="drpGroup" id="drpGroup">
                                                <option value=""> Select</option>
                                                <?php
                                                foreach ($resCsTransactionTypes as $item) {
                                                    if ($item->TransType == 1) {
                                                        echo '<option value="' . $item->TransCategory . '">' . $item->TransCategory . ' </option>';
                                                    }
                                                }
                                                ?>
                                            </select>
                                        </div>

                                        <div class="col-lg-12 text-center">
                                            <button id="btnSearch" type="submit" class="btn btn-primary waves-effect waves-light">
                                                Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                                            </button>
                                            <a href="daily-production-data?type=Production" class="btn btn-success waves-effect waves-light"><i class="fa fa-plus"></i> Add</a>


                                        </div>

                                    </div>
                                </form>
                                <!-- <input type="hidden" id="hdnActionType" /> -->
                            </div>

                        </div>

                        <div id="divDataList">
                            <!-- -->
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


<!-- Modal -->
<div class="modal fade" id="popupMCSLoose" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Mcs Update</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="frmMCSLoose" role="form" method="post">
                <div class="modal-body">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-6 col-lg-6 mb-3">
                                <label for="txtMcs" class="form-label">Mcs <span class="spanMsc"></span> <span style="color:red">*</span></label>
                                <input type="number" class="form-control" id="txtMcs" name="txtMcs">
                                <input type="hidden" id="hdnMinMcs" />
                            </div>

                            <div class="col-md-6 col-lg-6 mb-3">
                                <label for="txtLooseBags" class="form-label">Loose Bags <span class="spanBags"></span><span style="color:red">*</span></label>
                                <input type="number" class="form-control" id="txtLooseBags" name="txtLooseBags">
                                <input type="hidden" id="hdnMinBags" />
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="hdnProductionid" />
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>




<?php include_once ROOT . '/includes/common-js.php' ?>

<!-- Required datatable js -->
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

<?php include_once ROOT . '/includes/app-js.php' ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js" type="text/javascript"></script>


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
            bInfo: false
        });
    }

    function fnSuccess(data) {

        $("#divDataList").html(data);
        fnShowDataTable();

        // $("<button class='btn btn-primary' id='btnMoreSearch' style='padding: 3px 5px;margin-left: 10px;'>More Search</button>" +
        //     "<button class='btn btn-primary' id='btnLoadAll' style='padding: 3px 5px;margin-left: 10px;'>Load All</button>"
        // ).insertAfter("#tblDataTable_filter label");

        $('html, body').animate({
            scrollTop: $("#divDataList").offset().top - 50
        }, 10);
    }

    $(document).ready(function() {
        var productionPlant = <?php echo $ProductionPlant ?>;

        console.log('productionPlant' + productionPlant);
        if (productionPlant != 0) {
            console.log('productionPlant' + productionPlant);
            $("#drpStockBlnTo").val($("[data-processorID='" + productionPlant + "']").val()).trigger('change');
        } else {
            $("#drpStockBlnTo").val("").trigger('change');
        }

        ShowList();
    })

    function ShowList(pageNo, searchkey, loadAll) {
        $.ajax({
            url: 'Ajax/ajax-daily-production-list.php',
            type: 'POST',
            data: {
                date: $("#txtProductionDate").val(),
                coldStore: $("#drpColdStores").val(),
                stockBlnTO: $("#drpStockBlnTo").val(),
                packingType: $("#drpPackingType").val(),
                supc: $("#txtSupc").val(), //for supc based data edit

                group: $("#drpGroup").val(),
                PageNo: pageNo,
                SearchKey: searchkey,
                LoadAll: loadAll,
            },
            success: function(data) {
                fnSuccess(data);
                return false;
            }
        });
    }

    // $("#btnSearch").on("click", function() {
    //     ShowList();
    // });

    $(document).on("click", ".btnDelete", function() {
        if (confirm('Are you sure want to delete?')) {

            $.ajax({
                url: 'Ajax/ajax-daily-production-list.php',
                type: 'POST',
                data: {
                    methodType: "delete",
                    ProductionID: $(this).attr("data-prodid"),
                    ProductionDate: $("#txtProductionDate").val()
                },
                success: function(data) {

                    $("#divDataList").html(data);
                    fnShowDataTable();

                    alert($(document).find("#hdnAPIRes").val());

                    if ($(document).find("#hdnAPIRes").attr("data-restype") == "error") {
                        $("#btnSearch").trigger('click');
                    }

                    return false;
                }
            });
        }

    });

    // $(document).ready(function() {
    //     ShowList("1", "", "0");
    // });

    // $(document).on("click", "#btnMoreSearch", function() {
    //     var searchkey = $("#tblDataTable_filter input").val();
    //     if (searchkey.length > 0) {
    //         ShowList("1", searchkey, "0");
    //     }
    // });

    // $(document).on("click", "#btnLoadAll", function() {
    //     ShowList("1", "", "1");
    // });

    // $(document).on("click", ".btnPageno", function() {
    //     var pageno = $(this).attr("data-pageno");
    //     ShowList(pageno, "", "0");
    // });

    jQuery.validator.addMethod(
        "noSpace",
        function(value, element) {
            return value == "" || value.trim().length != 0;
        },
        "No space please and don't leave it empty"
    );
    $("#frmDailyProdList").validate({
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
            txtProductionDate: {
                required: true
            },
            txtSupc: { //for supc based data edit
                required: true
            }
        },
        errorPlacement: function() {
            return false; // suppresses error message text
        },
        submitHandler: function() {
            ShowList();
            return false;
        }
    });

    $(document).on("click", ".btnEdit", function() {
        $('#popupMCSLoose').modal('show');

        $("#txtMcs, #txtLooseBags").val("");

        var tdMsc = parseInt($.trim($(this).closest("tr").find(".tdMsc").text()));
        var tdBalanceMsc = parseInt($.trim($(this).closest("tr").find(".tdBalanceMsc").text()));
        var tdBags = parseInt($.trim($(this).closest("tr").find(".tdBags").text()));
        var tdBalanceBags = parseInt($.trim($(this).closest("tr").find(".tdBalanceBags").text()));

        $("#hdnMinMcs").val(tdMsc - tdBalanceMsc);
        $(".spanMsc").text("in store : ( " + $("#hdnMinMcs").val() + " )");
        $("#hdnMinBags").val(tdBags - tdBalanceBags);
        $(".spanBags").text("in store : ( " + $("#hdnMinBags").val() + " )");

        $("#txtMcs").val(tdMsc);
        $("#txtLooseBags").val(tdBags);
        $("#hdnProductionid").val($(this).attr("data-productionid"));

    });

    $("#txtMcs").on("change", function() {
        var val = parseInt($(this).val());
        var minVal = parseInt($("#hdnMinMcs").val());
        if (val < minVal) {
            alert("Mcs Value should not be less then " + minVal);
            $(this).val("");
            // $(this).val(minVal);
        }
    });
    $("#txtLooseBags").on("change", function() {
        var val = parseInt($(this).val());
        var minBags = parseInt($("#hdnMinBags").val());
        if (val < minBags) {
            alert("Loose Bags should not be less then " + minBags);
            $(this).val("");
            // $(this).val(minVal);
        }
    });

    $("#frmMCSLoose").validate({

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
            txtMcs: {
                required: true,
                // noSpace: true
            },
            txtLooseBags: {
                required: true
            },
        },
        errorPlacement: function() {
            return false; // suppresses error message text
        },
        submitHandler: function() {

            $.ajax({
                url: 'Ajax/ajax-daily-production-list.php',
                type: 'POST',
                data: {
                    methodType: "McsUpdate",
                    productionID: $("#hdnProductionid").val(),
                    txtMcs: $("#txtMcs").val(),
                    txtLooseBags: $("#txtLooseBags").val(),
                },
                success: function(data) {
                    $("#divDataList").html(data);
                    fnShowDataTable();

                    alert($(document).find("#hdnAPIRes").val());

                    $('#popupMCSLoose').modal('hide');

                    // if ($(document).find("#hdnAPIRes").attr("data-restype") == "error") {
                    //     $("#btnSearch").trigger('click');
                    // }
                    return false;
                }
            });

            // $("#hdnProductionid").val($(this).attr("data-productionid"));

            return false;
        }
    });
</script>

</body>

</html>