<?php
session_start();

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
$excludeList = array("Blocks", "Floors", "StoreRacks", "Stock", "ProductGroup", "Exporters", "Consignee", "Processor", "Shifts", "PackingType", "FreezingType", "Soaking", "Abstatus", "OptType", "OutType", "Grade", "Variety", "VarietyList", "StockAvailableVariety", "StockInVarietyList", "PoNumber", "PackingStyle", "Plant", "GeneralCategories", "Status", "TenantType", "ProductionLines", "Brand", "BrandType", "Species", "Tranction", "TypeProduct", "Type", "CsTransactionTypes", "ProductForm", "Preparation", "GlazeType", "ShipoutPoNumber", "ProductionPlant", "PoNumberNew", "TransactionTypesNew", "InwardGroups");
$filterColdStores = "T";
$filterStockBlgsTo = "T";
$filterShipoutPoNumber = "T";
$shimentOutButton = "T";
$filterorderStatus = "T";

$ProductionPlant = $_SESSION['UnitID'];
$ClientID = $_SESSION['ClientID'];
// echo print_r($_SESSION);exit;


    ?>

<!doctype html>
<html lang="en">

<head>

    <?php include_once ROOT . '/includes/head.php' ?>
    <style>
        /* .select2-container--open {
                z-index: 9999999
            }             */
        #myModal .modal-lg,
        #myModal .modal-xl {
            --bs-modal-width: 100% !important;
        }

        #stock-out-date {
            display: block;
            width: 100%;
            padding: .47rem .75rem;
            font-size: .9rem;
            font-weight: 400;
            line-height: 1.5;
            color: var(--bs-body-color);
            background-clip: padding-box;
            border: var(--bs-border-width) solid var(--bs-border-color-translucent);
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            border-radius: var(--bs-border-radius);
            -webkit-transition: border-color .15s ease-in-out, -webkit-box-shadow .15s ease-in-out;
            transition: border-color .15s ease-in-out, -webkit-box-shadow .15s ease-in-out;
            transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out;
            transition: border-color .15s ease-in-out, box-shadow .15s ease-in-out, -webkit-box-shadow .15s ease-in-out;
        }
        .exportMcs {
            display: none;
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
                <?php include_once WORKING_MODULE . '/includes/page-filter.php';
                include "PopUpTable.php";

                ?>

                <input type="hidden" id="hdnSelectedRow" />
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">





                        <div class="card">
                            <div class="card-body">

                                <div id="divDataList">

                                </div>
                                <input type="hidden" id="hdnActionType" />
                                <input type="hidden" id="remainingLooseBags" />
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
    function fnShowDataTable() {
        var table = $('#tblDataTable').DataTable({
            scrollY: "600px",
            scrollX: true,
            sScrollXInner: "100%",
            paging: false,
            destroy: true,
            fixedColumns: true,
            scrollCollapse: true,
            bInfo: false,
            dom: 'Blfrtip',
            // dom: '<"top-left"Blfrtip>',
            buttons: [{
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable btn-left-align',
                title: 'Shipment Out(' + $("#drpPoNumber option:selected").text() + ') -  <?php echo date("d-m-Y") ?>',
                format: {
                    body: function (data, row, column, node) {
                        return ($(node).find('.exportMcs').text() ? $(node).find('.exportMcs').text() : data);
                    }
                },
                footer: true,
                exportOptions: {
                    columns: ':visible'
                }
            },
            {
                extend: 'pdfHtml5', // Use pdfHtml5 for PDF export
                text: 'Download PDF',
                className: 'btnPdfDataTable btn-left-align',
                title: 'Shipment Out(' + $("#drpPoNumber option:selected").text() + ') -  <?php echo date("d-m-Y") ?>', // Use moment.js for dynamic date
                orientation: 'portrait', // Set PDF orientation to landscape
                exportOptions: {
                    columns: ':visible', // Export only visible columns
                    stripHtml: false // Preserve basic HTML formatting for a cleaner PDF
                }
            }
            ],
        });
    }

    $(document).ready(function () {
        $("#drpPDCategory").val(null).trigger('change')
        var productionPlant = <?php echo $ProductionPlant ?>;
        var ClientID = "<?php echo $ClientID ?>";
        if (productionPlant != 0) {
            $("#drpColdStore").prop('disabled', true);
            $("#drpColdStore").val($("[data-plant='" + productionPlant + "']").val()).trigger('change');

        } else {
            $("#drpColdStore").prop('disabled', false);
            $("#drpColdStore").val("").trigger('change');
        }

        var $drpPoNumber = $("#drpPoNumber");

        $drpPoNumber.find('option').each(function () {
            var optionPlantId = $(this).data('plantid');

            var plantIdArray = String(optionPlantId).split(',');
            if (productionPlant != 0) {
                if (plantIdArray.includes(String(productionPlant)) || $(this).val() === "") {
                    $(this).show();

                } else {
                    $(this).remove();
                }
            } else {
                $(this).show();
            }
        });

        if (productionPlant != 0) {
            if (ClientID == 'DFLV3') {
                $("#drpStockBlgsTo").prop('disabled', true);
            } else {
                $("#drpStockBlgsTo").prop('disabled', false);
            }
            $("#drpStockBlgsTo").val($("[data-processorID='" + productionPlant + "']").val()).trigger('change');
        } else {
            $("#drpStockBlgsTo").prop('disabled', false);
            $("#drpStockBlgsTo").val("").trigger('change');
        }
    });

    $(document).on("input", ".txtMCS", function () {
        var val = $(this).val();
        $(this).closest("td").find(".exportMcs").text(val);
    });

    $(document).on('input', '.txtLooseMcs', function () {

    let $row = $(this).closest('tr');

    let orderMcs = parseFloat($(this).data('ordermcs'), 10) || 0;
    let avalMcs  = parseFloat($(this).data('avalmcs'), 10) || 0;
    let looseMcs = parseFloat($(this).val(), 10) || 0;

    let maxLoose = parseFloat($(this).attr('max'), 10) || 0;

    // Enforce limit
    if (looseMcs > maxLoose) {
        looseMcs = maxLoose;
        $(this).val(maxLoose);
    }
    if (looseMcs < 0) {
        looseMcs = 0;
        $(this).val(0);
    }

    let selectedMcs = avalMcs + looseMcs;
    let reqMcs = orderMcs - selectedMcs;

    // Update UI
    $row.find('.totalLooseMcs').text(looseMcs);
    $row.find('.selectedMcs').text(selectedMcs);
    $row.find('.reqMcs').text(reqMcs);
});


    function fnSuccess(data) {

        $("#divDataList").html(data);
        fnShowDataTable();

        $("<button class='btn btn-primary' id='btnMoreSearch' style='padding: 3px 5px;margin-left: 10px;'>More Search</button>" +
            "<button class='btn btn-primary' id='btnLoadAll' style='padding: 3px 5px;margin-left: 10px;'>Load All</button>"
        )
            .insertAfter("#tblDataTable_filter label");

        $('html, body').animate({
            scrollTop: $("#divDataList").offset().top - 50
        }, 10);
    }

    function ShowList(pageNo, searchkey, loadAll) {

        var poNo = $("#drpPoNumber").val();
        var TenantID = $('#drpStockBlgsTo').val();

        if (TenantID == '') {
            TenantID = "BOTH";
        } else {
            TenantID = $('#drpStockBlgsTo').val();
        }

        if (poNo == '') {
            $("#drpPoNumber").focus();
            alert("Select Po");
            return;
        } else {

            $.ajax({
                url: 'Ajax/ajax-shipment-out.php',
                type: 'POST',
                data: {
                    coldStore: $("#drpColdStore").val(),
                    stockBlgsTo: TenantID,
                    poNo: poNo,
                    pageNo: pageNo,
                    searchKey: searchkey,
                    loadAll: loadAll,
                    pdCategory: $.trim($("#drpPDCategory").val()),
                },
                success: function (data) {
                    fnSuccess(data);
                    return false;
                }
            });
        }
    }

    $(document).ready(function () {
        $("#LoadToCons").prop("disabled", true);

    });

    $(document).on("click", "#btnMoreSearch", function () {
        var searchkey = $("#tblDataTable_filter input").val();
        if (searchkey.length > 0) {
            ShowList("1", searchkey, "0");
        }
    });

    $(document).on("click", "#btnLoadAll", function () {
        ShowList("1", "", "1");
    });

    $(document).on("click", ".btnPageno", function () {
        var pageno = $(this).attr("data-pageno");
        ShowList(pageno, "", "0");
    });

    $("#btnSearch").on("click", function () {
        ShowList("1", "", "0");
        $("#LoadToCons").prop("disabled", true);
    });
    $("#drpPoNumber").on("change", function () {
        ShowList("1", "", "0");
        $("#LoadToCons").prop("disabled", true);
    });

    $(document).on('click', '#LoadToCons', function (e) {
        e.preventDefault();

        var PoID = $('#drpPoNumber').val();
        var ColdstoreID = $('#drpColdStore').val();
        var TenantID = $('#drpStockBlgsTo').val();

        if (TenantID == '') {
            TenantID = "BOTH";
        } else {
            TenantID = $('#drpStockBlgsTo').val();
        }

        if (PoID == '') {
            $("#drpPoNumber").focus();
            alert("Select Po");
            return;
        } else {
            var sumMCS = 0;
            var mcsValue = 0;
            var looseValue = 0;
            var sumLoose = 0;
            var varietyId = 0;
            var mcsValues = [];
            var varietyIDs = [];
            $('.txtMCS').each(function () {
                const $row = $(this).closest("tr");

                if ($row.find(".rowCheck").prop('checked') == true) {
                // if ($(this).closest("tr").find(".rowCheck").prop('checked') == true) {
                    mcsValue = parseFloat($(this).val()) || 0;
                    looseValue = parseFloat($row.find(".txtLooseBags").val()) || 0;
                    varietyId = $(this).data('varietyid');
                    sumMCS += mcsValue;
                    sumLoose += looseValue;
                    mcsValues.push({
                        mcsValue: mcsValue,
                        looseValue: looseValue,
                        varietyId: varietyId
                    });
                    // mcsValues.push(mcsValue);
                    // varietyIDs.push(varietyId);
                }
            });
            var mcsValues = JSON.stringify(mcsValues);
            $.ajax({
                url: 'Ajax/ajax-po-out-popup.php',
                type: 'POST',
                data: {
                    'PoID': PoID,
                    'stockIds': $("#hdnSelectedRow").val(),
                    'TenantID': TenantID,
                    'ColdstoreID': ColdstoreID,
                    'mcsValues': mcsValues,
                    'varietyIDs': varietyIDs,
                    'sumMCS': sumMCS,
                    'sumLoose': sumLoose

                },
                success: function (data) {

                    $(".modal-header").html("").html('<h4 class="modal-title">Are you sure to Load the consignment?</h4><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>');
                    $("#PopUpTable").html(data);
                    $('#myModal').modal('show');
                    $('#myModal .modal-footer').hide('show');
                    $("#Update").css("display", "none");
                    var looseJson = $("#tdRemainingLooseBags").val();
                    $("#remainingLooseBags").val(looseJson);

                    $('#drpReason').select2({
                        dropdownParent: $('#PopUpTable')
                    });
                }
            });
        }
    });


    $("#myModal .btn.btn-danger").on("click", function () {
        $('#myModal').modal('hide');
    });


    $(document).on('click', '#CSOUT1', function (e) {
        e.preventDefault();
        var PoID = $('#PoID').val();
        var UnitID = $('#UnitID').val();
        var ColdstoreID = $('#ColdstoreID').val();
        var TenantID = $('#TenantID').val();
        var reason = $("#drpReason").val();
        var IsStockOutAtCs = $('#IsStockOutAtCs').val();
        var stockOutDate = "";
        if (IsStockOutAtCs) {
            stockOutDate = $('#stock-out-date').val();
        }
        if (PoID == '') {
            $("#PoID").focus();
            alert("Select Po");
            return;
        } else if (IsStockOutAtCs && stockOutDate == "") {
            $("#stock-out-date").focus();
            alert("Select Stock Out Date");
            return;
        } else {
            $('#myModal').modal('hide');
            var jsonObj = [];
            var sumMCS = 0;

            $("#tblDataTable tbody tr").each(function () {
                var checkbox = $(this).find(".rowCheck");

                if (checkbox.is(":checked")) {
                    var hdnStockId = $(this).find(".hdnStockId").val();
                    var hdnBarCodeID = $(this).find(".hdnBarCodeID").val();
                    var hdnStoreID = $(this).find(".hdnStoreID").val();
                    var hdnRackID = $(this).find(".hdnRackID").val();
                    var hdnBlockID = $(this).find(".hdnBlockID").val();
                    var txtMCS = $(this).find(".txtMCS").val();
                    var prodid = $(this).find(".rowCheck").attr("data-prodid");

                    sumMCS = parseFloat(sumMCS) + parseFloat(txtMCS);

                    jsonObj.push({
                        "StockID": hdnStockId,
                        "BarCodeID": hdnBarCodeID,
                        "StoreID": hdnStoreID,
                        "RackID": hdnRackID,
                        "OutMcs": txtMCS,
                        "OutLooseBags": "",
                        "BlockID": hdnBlockID,
                        "ProductionID": prodid
                    });
                }

            });

            var jsonString = JSON.stringify(jsonObj);
            var jsonRowsSelected = JSON.parse(jsonString);

            dataForm = {
                "methodType": "stockOut",
                "stockOutDate": stockOutDate,
                "stockOutReason": $("#drpReason").val(),
                "remarks": "",
                "totalMsc": sumMCS,
                "totalLooseBags": "",
                "stockItems": jsonRowsSelected,
                "selectedTransTypeId": $(document).find('#drpReason option:selected').data('transtypeid')
            };

            $.ajax({
                url: 'Ajax/AjaxStockOut.php',
                type: 'POST',
                data: dataForm,
                success: function (data) {

                    if (data.Error) {
                        alert(data.Error);
                    } else {
                        alert('Stock Out Successfully');
                        window.location.href = "shipment-out";
                    }
                }
            });
        }
    });
     $(document).on('click', '#CSOUT', function (e) {
        return CSOutBtnClick();
    })

    function CSOutBtnClick(){
        var PoID = $('#PoID').val();
        var UnitID = $('#UnitID').val();
        var ColdstoreID = $('#ColdstoreID').val();
        var TenantID = $('#TenantID').val();
        var reason = $("#drpReason").val();
        var IsStockOutAtCs = $('#IsStockOutAtCs').val();
        var jsonRemainingLooseBags = $("#remainingLooseBags").val();
        var stockOutDate = "";
        if (IsStockOutAtCs) {
            stockOutDate = $('#stock-out-date').val();
        }
        if (PoID == '') {
            $("#PoID").focus();
            alert("Select Po");
            return;
        } else if (IsStockOutAtCs && stockOutDate == "") {
            $("#stock-out-date").focus();
            alert("Select Stock Out Date");
            return;
        } else {
            $('#myModal').modal('hide');
            var jsonObj = [];
            var sumMCS = 0;

            $("#tblDataTable tbody tr").each(function () {
                var checkbox = $(this).find(".rowCheck");

                if (checkbox.is(":checked")) {
                    var hdnStockId = $(this).find(".hdnStockId").val();
                    var hdnBarCodeID = $(this).find(".hdnBarCodeID").val();
                    var hdnStoreID = $(this).find(".hdnStoreID").val();
                    var hdnRackID = $(this).find(".hdnRackID").val();
                    var hdnBlockID = $(this).find(".hdnBlockID").val();
                    var txtMCS = $(this).find(".txtMCS").val();
                    var LooseBags = $(this).find(".LooseBags").val();
                    var prodid = $(this).find(".rowCheck").attr("data-prodid");

                    sumMCS = parseFloat(sumMCS) + parseFloat(txtMCS);

                    jsonObj.push({
                        "StockID": hdnStockId,
                        "BarCodeID": hdnBarCodeID,
                        "StoreID": hdnStoreID,
                        "RackID": hdnRackID,
                        "OutMcs": txtMCS,
                        "OutLooseBags": "",
                        "LooseBags" : LooseBags,
                        "BlockID": hdnBlockID,
                        "ProductionID": prodid
                    });
                }

            });

            jsonObj.sort(function (a, b) {
                return parseFloat(b.StockID) - parseFloat(a.StockID);
            });
            var jsonString = JSON.stringify(jsonObj);
            var jsonRowsSelected = JSON.parse(jsonString);
            var looseMcsData = {};

            $('tr.loose-conversion').each(function () {

                var txtInput = $(this).find('.txtLooseMcs');

                if (txtInput.length > 0) {

                    var varietyId = txtInput.data('varietyid');
                    var looseMcs   = parseFloat(txtInput.val());
                    var totalLoose = parseFloat($(this).find('.totalLooseMcs').text());

                    looseMcsData[varietyId] = (totalLoose - looseMcs);
                }
            });

            dataForm = {
                "methodType": "stockOut",
                "stockOutDate": stockOutDate,
                "stockOutReason": $("#drpReason").val(),
                "remarks": "",
                "totalMsc": sumMCS,
                "totalLooseBags": "",
                "stockItems": JSON.stringify(jsonRowsSelected),
                "remainingLooseBags" : JSON.stringify(jsonRemainingLooseBags),
                "selectedLooseMcs" : JSON.stringify(looseMcsData),
                "IsShipment": "1",
                "selectedTransTypeId": $(document).find('#drpReason option:selected').data('transtypeid')
            };

            $.ajax({
                url: 'Ajax/AjaxStockOut.php',
                type: 'POST',
                data: dataForm,
                success: function (data) {

                    if (data.Error) {
                        alert(data.Error);
                    } else {
                        alert('Stock Out Successfully');
                        window.location.href = "shipment-out";
                    }
                }
            });
        }
    }
    function checkedlist() {
        var selectedOptions = new Array();
        $("input.rowCheck:checked").each(function () {
            selectedOptions.push(this.value);
        });
        $('#hdnSelectedRow').val(selectedOptions);

        if ($('#hdnSelectedRow').val().length == 0) {
            $("#LoadToCons").prop("disabled", true);
        } else {
            $("#LoadToCons").prop("disabled", false);
        }
    }

    $(document).on("keyup", ".txtMCS", function () {
        var actualMsc = parseFloat($(this).attr("data-mcs"));
        var enteredMsc = parseFloat($(this).val());
        if (enteredMsc > actualMsc) {
            alert("MCS exceeds actual value.");
            $(this).val(actualMsc);
        }
    });

    $(document).on("click", "input.rowCheck", function () {
        checkedlist();
        $("#checkall").prop("checked", false);
    });

    $(document).on("click", "#checkall", function () {
        if (this.checked) {
            $(".rowCheck").prop("checked", true);

        } else {
            $(".rowCheck").prop("checked", false);

        }
        checkedlist();
    });



    $(document).ready(function () {
        setTimeout(function() {
            getPoNumbers();
        }, 1000);
    });

    $(document).on("change", "#drpYear", function () {
        getPoNumbers();
        $("#divDataList").html("");
    });

    $(document).on("change", "#drpOrderStatus", function () {
        getPoNumbers();
        $("#divDataList").html("");

    });

    function getPoNumbers() {
        var year = $("#drpYear").val();
        var orderStatus = $("#drpOrderStatus").val();

        $.ajax({
            url: 'Ajax/ajax-order-packing-timeline.php',
            type: 'POST',
            data: {
                methodType: "getShipmentoutPoNumber",
                year: year,
                orderStatus: orderStatus,
            },
            success: function (data) {

                if (data) {
                    var listBlock = "<option value=''>Select</option>";
                    $.each(data, function (index, val) {
                        listBlock += '<option value="' + val.PoID + '" data-plantId="' + val.ProductionPlantID + '">' + val.PoNo + '</option>';
                    });
                    $("#drpPoNumber").html("").html(listBlock);
                }
            }
        });
    }



</script>

</body>

</html>