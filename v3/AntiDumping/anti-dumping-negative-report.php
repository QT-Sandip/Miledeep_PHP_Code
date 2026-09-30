<?php

session_start();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_COLDSTORE_DROPDOWNS;
$excludeList = array( "Blocks", "Floors", "StoreRacks", "Stock", "ProductGroup", "Exporters", "Consignee", "Processor", "OptType", "OutType", "Variety", "VarietyList", "StockAvailableVariety", "StockInVarietyList", "PoNumber", "GeneralCategories", "Status", "TenantType", "Brand", "BrandType", "Species", "Tranction", "TypeProduct", "Type", "ProductForm", "Preparation", "GlazeType", "ProductionPlant", "PoNumberNew", "TransactionTypesNew", "InwardGroups");
$bodyContent = array("exclude_list" => $excludeList);
$resDropdownsAPI = CallAPI($api, "GET", "", $bodyContent);

$resCsTransactionTypes = $resDropdownsAPI->Data->CsAllTransactionTypes;
$resProductGroup = $resDropdownsAPI->Data->ProductGroup;
$resColdStores = $resDropdownsAPI->Data->ColdStores;
$resPlant = $resDropdownsAPI->Data->Plant;
$resShifts = $resDropdownsAPI->Data->Shifts;
$resPoNumber = $resDropdownsAPI->Data->ShipoutPoNumber;//$resDropdownsAPI->Data->PoNumber;
$resVariety = $resDropdownsAPI->Data->Variety;
$resPackingType = $resDropdownsAPI->Data->PackingType;
$resPackingStyle = $resDropdownsAPI->Data->PackingStyle;
$resGrade = $resDropdownsAPI->Data->Grade;
$resFreezingType = $resDropdownsAPI->Data->FreezingType;
$resSoaking = $resDropdownsAPI->Data->Soaking;
$resProductCategory = $resDropdownsAPI->Data->ProductCategory;
$resAbstatus = $resDropdownsAPI->Data->Abstatus;

$resBlocks = $resDropdownsAPI->Data->Blocks;
$resFloors = $resDropdownsAPI->Data->Floors;
$resProductionLines = $resDropdownsAPI->Data->ProductionLines;

$StockOutID = isset($_GET["StockOutID"]) && !empty($_GET["StockOutID"]) ? $_GET["StockOutID"] : "";
$ProductionPlant = $_SESSION['UnitID'];
$PackingStyleData = "";

?>

<!doctype html>
<html lang="en">

<head>

    <?php include_once ROOT . 'includes/head.php' ?>

</head>

<?php include_once ROOT . 'includes/body-preloader.php' ?>

<!-- Begin page -->
<div id="layout-wrapper">
      <style>
        .pagination-rounded .page-link {
            border-radius: 30px !important;
            margin: 0 3px;
            border: none;
            text-align: center;
            height: 35px;
        }
        .page-link.active {
        z-index: 3;
        color: #fff;
        background-color: #5664d2;
        }
    </style>
    <?php include_once ROOT . 'includes/menu.php' ?>

    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <!-- ============================================================== -->
    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">

                <?php include_once ROOT . 'includes/page-header.php' ?>

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <div class="card">
                            <div class="card-body">
                                <!-- <h4 class="card-title">Bootstrap Validation - Normal</h4> -->
                                <form id="frmInward" method="POST">
                                    <div class="row div_inward">

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpProductionPlant" class="form-label">Production Plant<span
                                                    style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpProductionPlant"
                                                id="drpProductionPlant">
                                                <option value=""> Select</option>
                                                <?php foreach ($resPlant as $item) { ?>
                                                    <option value="<?php echo $item->ProcessingStationID; ?>">
                                                        <?php echo $item->StationName; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3">
                                            <label for="drpPoNumber" class="form-label">Po Number</label>
                                            <select class="drpSelect form-select" name="drpPoNumber" id="drpPoNumber">
                                                <option value=""> Select</option>
                                                <?php foreach ($resPoNumber as $item) { ?>
                                                    <option value="<?php echo $item->PoID; ?>"
                                                        data-plantid="<?php echo $item->ProductionPlantID ?>">
                                                        <?php echo $item->PoNo ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="txtVariety" class="form-label">Variety</label>
                                            <input type="input" class="form-control" id="txtVariety" name="txtVariety"
                                                placeholder="Click to Select Variety" readonly />
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpGrade" class="form-label">Grade</label>
                                            <select class="drpSelect form-control" name="drpGrade" id="drpGrade" disabled>
                                                <option value=""> Select</option>
                                                <?php foreach ($resGrade as $item) { ?>
                                                    <option value="<?php echo $item->value; ?>">
                                                        <?php echo $item->Options ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                         <!-- <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtProductionDate" class="form-label">From Date</label>
                                            <input type="date" class="form-control" id="txtProductionDate"
                                                name="txtProductionDate" value="" />
                                        </div>

                                         <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtProductionToDate" class="form-label">To Date</label>
                                            <input type="date" class="form-control" id="txtProductionToDate"
                                                name="txtProductionToDate" value="" />
                                        </div> -->

                                        <?php
                                            // Get first and last day of current month
                                            $firstDay = date('Y-m-01');
                                            $lastDay  = date('Y-m-d');
                                        ?>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtProductionDate" class="form-label">From Date</label>
                                            <input type="date" class="form-control" id="txtProductionDate"
                                                name="txtProductionDate" value="<?php echo $firstDay; ?>" />
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtProductionToDate" class="form-label">To Date</label>
                                            <input type="date" class="form-control" id="txtProductionToDate"
                                                name="txtProductionToDate" value="<?php echo $lastDay; ?>" />
                                        </div>
                                    </div>

                                    <div class="text-center">
                                        <button class="btn btn-primary" name="submit" id="btn-submit"
                                            type="button">Search <i class="ri-arrow-right-line align-middle ml-2"></i></button>

                                    </div>
                                </form>
                            </div>
                        </div>


                        <div id="divDataList"></div>

                    </div>
                </div>

            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->

        <?php include_once ROOT . 'includes/footer.php' ?>

    </div>
    <!-- end main content-->

    <div class="modal fade" id="modalVarietyList" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Variety List</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <!--<form role="form" id="frmTransitType" onsubmit="return validateForm()" method="post">-->
                <div class="modal-body">
                    <div class="row">
                        <div id="modalVariety"></div>
                    </div>
                </div>
                <!-- <div class="modal-footer">                            
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                            <button type="button" id="btnPopStockOut" name='btnPopStockOut' class="btn btn-primary">Stock Out</button>
                        </div> -->
            </div>
        </div>
    </div>

</div>
<!-- END layout-wrapper -->


<?php include_once ROOT . 'includes/common-js.php' ?>

<?php include_once WORKING_MODULE . 'modalSupplier.php' ?>


<!-- Required datatable js -->
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

<?php include_once ROOT . '/includes/app-js.php' ?>

<script>
    // var barCodeID = "<?php // echo isset($_GET["BarCodeID"]) && !empty($_GET["BarCodeID"]) ? $_GET["BarCodeID"] : "" 
    ?>";

    var table;

    $(document).on('keydown', 'input[pattern]', function (e) {
        var input = $(this);
        var oldVal = input.val();
        var regex = new RegExp(input.attr('pattern'), 'g');

        setTimeout(function () {
            var newVal = input.val();
            if (!regex.test(newVal)) {
                input.val(oldVal);
            }
        }, 1);
    });

    var poptable;

    $(document).on('shown.bs.modal', '#modalVarietyList', function () {
        if ($.fn.dataTable.isDataTable('.popVarietyTable')) {
            poptable.destroy();
        }
        // poptable.columns.adjust().draw();
        poptable = $('.popVarietyTable').DataTable({
            scrollY: "600px",
            scrollX: true,
            sScrollXInner: "100%",
            paging: false,
            destroy: true,
            fixedColumns: true,
            scrollCollapse: true,
            bInfo: false,
            ordering: false
        });

    });

    function fnShowDataTable(tableTitle) {
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
                title: tableTitle || 'Anti Dumping Negative Report',
                footer: true,
                exportOptions: {
                    columns: ':visible'
                }
            }],
        });
           $("<button class='btn btn-primary' id='btnMoreSearch' style='padding: 3px 5px;margin-left: 10px;'>More Search</button>"+
           // "<a class='btn btn-secondary' id='btnRefresh' href='javascript:void(0);' style='padding: 3px 5px;margin-left: 10px;'>Refresh</a>"+
             "<a class='btn btn-primary' id='LoadAll' href='javascript:void(0);' style='padding: 3px 5px;margin-left: 10px;'>Load All</a>"
        ).insertAfter("#tblDataTable_filter label");
        $("#tblDataTable_filter input").attr("placeholder", "ProductDesc,Slabs,PackingStyle,Grade Etc");
        $("<style>#tblDataTable_filter input::placeholder { font-size: 10px; }</style>").appendTo("head");
    }
 $(document).on('click','#LoadAll',function(){
        var LoadAll = '1';
        getProdDataByProdDate('',LoadAll);
    })/* 
    $(document).on('click','#btnRefresh',function(){
        getProdDataByProdDate();
    }) */
    $(document).on('click','#btnMoreSearch',function(){
        getProdDataByProdDate();
    })

    $("#txtVariety").on("click", function () {
        // if($("#drpPoNumber").val() != "") {
        getVarieties();

    });

    $(document).ready(function () {
        var productionPlant = <?php echo $ProductionPlant ?>;

        if (productionPlant != 0) {
            $("#drpProductionPlant").prop('disabled', true);
            $("#drpProductionPlant").val(productionPlant).trigger('change');
        } else {
            $("#drpProductionPlant").prop('disabled', false);
            $("#drpProductionPlant").val("").trigger('change');
        }

        var plantId = $("#drpProductionPlant").val();

        var $drpPoNumber = $("#drpPoNumber");

        $drpPoNumber.find('option').each(function () {
            var optionPlantId = $(this).data('plantid');

            var plantIdArray = String(optionPlantId).split(',').map(function (id) {
                return id.trim();
            });

            if (productionPlant != 0) {
                if (plantIdArray.includes(plantId) || $(this).val() === "") {
                    $(this).show();
                } else {
                    $(this).remove();
                }
            } else {
                $(this).show();
            }
        });
        $("#drpPoNumber").prop('disabled', false);

        if (productionPlant != 0) {

            $("#drpStockBelongs").val($("[data-processorID='" + productionPlant + "']").val()).trigger('change');
            $("#drpStockBelongs").prop("disabled", true);
        } else {
            $("#drpStockBelongs").val("").trigger('change');
            $("#drpStockBelongs").prop("disabled", false);
        }
    });



    $(document).on("click", ".selectVariety", function () {
        var selectedVariety = $(this).val();
        $("#txtVariety").val(selectedVariety);

        var selectedVarietyID = $(this).attr("data-po-grade-id");
        $("#txtVariety").attr("data-po-grade-id", selectedVarietyID);

        var selectedVarietyID = $(this).attr("data-varietyid");
        $("#txtVariety").attr("data-varietyid", selectedVarietyID);

        $('#modalVarietyList').modal('hide');
        getGrades();
        var PoID = $("#drpPoNumber").val();
        if(PoID == ''){
            getAllGrades();
        } else{
            getTotalByPoNumber();
        }
    })


    function getVarieties() {
        $.ajax({
            type: 'POST',
            url: 'Ajax/ajax-anti-dumping-negative-report.php',
            data: {
                'methodType': 'poNumber',
                'pOID': $("#drpPoNumber").val(),
            },
            success: function (data) {
                if (data) {


                    if (data.Error) {
                        alert(data.Error);
                    } else {
                        $('#modalVariety').html("").html(data.HtmlRes);
                        $('#modalVarietyList').modal('show');

                    }



                }
            }
        });
    }

    $(document).ready(function () {
        getProdDataByProdDate();
    });

     function getGrades() {
        poGraidID = $.trim($("#txtVariety").attr("data-po-grade-id"));
        var PoID = $("#drpPoNumber").val();

        $.ajax({
            type: 'POST',
            url: 'Ajax/ajax-anti-dumping-negative-report.php',
            data: {
                'methodType': 'varietyChange',
                'pOID': $("#drpPoNumber").val(),
                'varietyID': $("#txtVariety").attr("data-varietyid"),
                'poGradeID': poGraidID
            },
            success: function (data) {
                //alert("Hi")
                var obj = data.DependentDropdownsData.GradeData;
                var grade = obj.Grade;
                $("#drpGrade").prop("disabled", false);
                // Check if option exists
                if ($("#drpGrade option[value='" + grade + "']").length === 0) {
                    // Add the missing option
                    $("#drpGrade").append(
                        $("<option>", {
                            value: grade,
                            text: grade,
                            "data-varietyid": obj.VarietyID || ""
                        })
                    );
                }

                // Select the grade
                $("#drpGrade").val(grade).trigger("change");

            }
        });
    }

    function getAllGrades() {
        var PoID = $("#drpPoNumber").val();

        $.ajax({
            type: 'POST',
            url: 'Ajax/ajax-anti-dumping-negative-report.php',
            data: {
                'methodType': 'getVarietyAllGrades',
                'pOID': $("#drpPoNumber").val(),
                'varietyID': $("#txtVariety").attr("data-varietyid"), // $("#txtVariety").val(),
            },
            success: function (data) {
                var grade = $("#drpGrade").val();
                //alert("Hi")
                $("#drpGrade").prop("disabled", false);
                var list = "<option value=''>Select</option>";
                if (data) {
                    $.each(data, function (index, value) {
                        list += "<option value='" + value.Grade + "' data-varietyid='" + value.VarietyID + "'>" + value.Grade + "</option>";
                    });
                }

                $('#drpGrade').select2('destroy');
                $('#drpGrade').html(list);
                $('#drpGrade').select2();

                if (grade) {
                    $('#drpGrade').val(grade).trigger('change'); 
                }

            }
        });
    }

    function getProdDataByProdDate(pageNo = '', LoadAll ='') {
        var varietyid = '';
        if($.trim($("#drpPoNumber").val()) == ''){
            varietyid = $("#drpGrade option:selected").data("varietyid");
            // varietyid = $.trim($("#txtVariety").attr("data-varietyid"));
        }else{
            varietyid = $.trim($("#txtVariety").attr("data-varietyid"));
        }
        var searchkey = $("#tblDataTable_filter input").val();
        $.ajax({
            url: 'Ajax/ajax-anti-dumping-negative-report.php',
            type: 'POST',
            data: {
                methodType: "getList",
                txtProductionDate: $.trim($("#txtProductionDate").val()),
                txtProductionToDate: $.trim($("#txtProductionToDate").val()),
                drpProductionPlant: $.trim($("#drpProductionPlant").val()),
                drpPoNumber: $.trim($("#drpPoNumber").val()),
                drpVariety: varietyid,
                drpGrade: $.trim($("#drpGrade").val()),
                PageNo : pageNo,
                LoadAll : LoadAll,
                MoreSearch : searchkey
            },
            success: function (data) {
                $("#divDataList").html(data);
                $tableName = "Anti Dumping Negative Report";
                fnShowDataTable($tableName);
                return false;
            }
        });
    }
    $(document).on("click", "#btn-submit", function () {
        getProdDataByProdDate();
    });

    function getAllPOs() {
        $.ajax({
            type: 'POST',
            url: 'Ajax/ajax-anti-dumping-negative-report.php',
            data: {
                'methodType': 'allPoNumbers',
                'ProductionPlant': $("#drpProductionPlant").val(),
                'ProductionDate': $("#txtProductionDate").val(),
                'ProductionToDate': $("#txtProductionToDate").val(),
            },
            success: function (data) {
                if (data) {
                    var list = "<option value=''>Select</option>";
                    if (data) {
                        $.each(data, function (index, value) {
                            list += "<option value=" + value.PoID + " data-plantid='" + value.ProductionPlantID + "'>" + value.PoNo + "</option>";
                        });
                    }

                    $('#drpPoNumber').select2('destroy');
                    $('#drpPoNumber').html(list);
                    $('#drpPoNumber').select2();

                }
            }
        });
    }
    $(document).on("click", ".btnPageno", function(e) {
        getProdDataByProdDate( $(this).attr('data-pageno'));
    });
    $("#drpProductionPlant").on("change",function(){
        $("#divDataList").html("");
    });
</script>

</body>

</html>
