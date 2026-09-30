<?php

session_start();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
$api = API_COLDSTORE_DROPDOWNS;
$excludeList = array("Blocks", "Floors", "StoreRacks", "Stock", "ProductGroup", "Exporters", "Consignee", "Processor", "OptType", "OutType", "Variety", "VarietyList", "StockAvailableVariety", "StockInVarietyList", "PoNumber", "GeneralCategories", "Status", "TenantType", "Brand", "BrandType", "Species", "Tranction", "TypeProduct", "Type", "ProductForm", "Preparation", "GlazeType", "ProductionPlant", "PoNumberNew", "TransactionTypesNew", "InwardGroups");
$bodyContent = array("exclude_list" => $excludeList);
$resDropdownsAPI = CallAPI($api, "GET", "", $bodyContent);

$resCsTransactionTypes = $resDropdownsAPI->Data->CsAllTransactionTypes;
$resProductGroup = $resDropdownsAPI->Data->ProductGroup;
$resColdStores = $resDropdownsAPI->Data->ColdStores;
$resPlant = $resDropdownsAPI->Data->Plant;
$resShifts = $resDropdownsAPI->Data->Shifts;
$resPoNumber = $resDropdownsAPI->Data->ShipoutPoNumber; //$resDropdownsAPI->Data->PoNumber;
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

        /* Freeze header */
        #tblDataTable thead th {
            position: sticky;
            top: 0;
            background: #fff;
            /* keep background so text is visible */
            z-index: 2;
        }

        /* Freeze first 6 columns */
        #tblDataTable th:nth-child(1),
        #tblDataTable td:nth-child(1) {
            position: sticky;
            left: 0;
            background: #fff;
            z-index: 3;
        }

        #tblDataTable th:nth-child(2),
        #tblDataTable td:nth-child(2) {
            position: sticky;
            left: 72px;
            /* adjust based on col width */
            background: #fff;
            z-index: 3;
        }

        #tblDataTable th:nth-child(3),
        #tblDataTable td:nth-child(3) {
            position: sticky;
            left: 225px;
            background: #fff;
            z-index: 3;
        }

        #tblDataTable th:nth-child(4),
        #tblDataTable td:nth-child(4) {
            position: sticky;
            left: 300px;
            background: #fff;
            z-index: 3;
        }

        #tblDataTable th:nth-child(5),
        #tblDataTable td:nth-child(5) {
            position: sticky;
            left: 540px;
            background: #fff;
            z-index: 3;
        }

        #tblDataTable th:nth-child(6),
        #tblDataTable td:nth-child(6) {
            position: sticky;
            left: 620px;
            background: #fff;
            z-index: 3;
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
                                                <option value="All">All</option>
                                                <?php foreach ($resPlant as $item) { ?>
                                                    <option value="<?php echo $item->ProcessingStationID; ?>"
                                                        data-plantid="<?php echo $item->ProcessingStationID ?>">
                                                        <?php echo $item->StationName; ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>


                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtProductionDate" class="form-label">From Date <span
                                                    style="color:red">*</span></label>
                                            <input type="date" class="form-control" id="txtProductionDate"
                                                name="txtProductionDate" value="<?php echo date("Y-m-d") ?>"
                                                max="<?php echo date("Y-m-d") ?>" />
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtProductionToDate" class="form-label">To Date</label>
                                            <input type="date" class="form-control" id="txtProductionToDate"
                                                name="txtProductionToDate" value="<?php echo date("Y-m-d") ?>"
                                                max="<?php echo date("Y-m-d") ?>" />
                                        </div>

                                        <!-- <div class="form-group col-sm-3 mb-3">
                                            <label for="drpGroupType form-control" class="form-label">Group Type</label>
                                            <select class="drpSelect form-control" name="drpGroupType"
                                                id="drpGroupType">
                                                <option value=""> Select Group</option>
                                                <option value="1"> Inward Group</option>
                                                <option value="2"> Outward Group</option>
                                            </select>
                                        </div>


                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpGroup form-control" class="form-label">Group</label>
                                            <select class="drpSelect form-control" name="drpGroup" id="drpGroup">
                                                <option value=''>Select</option>
                                            </select>
                                        </div> -->

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

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpPackingStyle" class="form-label">Packing Style</label>
                                            <select class="drpSelect form-control" name="drpPackingStyle"
                                                id="drpPackingStyle">
                                                <option value=""> Select</option>
                                                <?php foreach ($resPackingStyle as $item) { ?>
                                                    <option value="<?php echo $item->PCKID; ?>">
                                                        <?php echo $item->PackingStyle ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                            <input type="hidden" id="hdnPackingStyle" />
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpFreezingType" class="form-label">Freezing Type</label>
                                            <select class="drpSelect form-control" name="drpFreezingType"
                                                id="drpFreezingType">
                                                <option value=""> Select</option>
                                                <?php foreach ($resFreezingType as $item) { ?>
                                                    <option value="<?php echo $item->FreezingTypeName; ?>">
                                                        <?php echo $item->FreezingTypeName ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpSoaking" class="form-label">Soaking-Treatment</label>
                                            <select class="drpSelect form-control" name="drpSoaking" id="drpSoaking">
                                                <option value=""> Select</option>
                                                <?php foreach ($resSoaking as $item) { ?>
                                                    <option value="<?php echo $item->ShortCode; ?>">
                                                        <?php echo $item->TreatmentName ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>


                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpProductCategory" class="form-label">Product Category</label>
                                            <select class="drpSelect form-control" name="drpProductCategory"
                                                id="drpProductCategory">
                                                <option value=""> Select</option>
                                                <?php foreach ($resProductCategory as $item) { ?>
                                                    <option value="<?php echo $item->Name; ?>" <?php echo ($item->Name === 'G1') ? 'selected' : ''; ?>>
                                                        <?php echo $item->Name ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpType">Type</label>
                                            <select class="drpSelect form-control" id="drpType" name="drpType">
                                                <option value="Slabs">Slabs</option>
                                                <option value="MCS">MCS</option>
                                                <option value="KGS">KGS</option>
                                            </select>
                                        </div>

                                    </div>

                                    <div class="text-center">
                                        <button class="btn btn-primary" name="submit" id="btn-submit"
                                            type="button">Search</button>

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
<link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.4.0/css/fixedHeader.dataTables.min.css">
<link rel="stylesheet" href="https://cdn.datatables.net/fixedcolumns/4.3.0/css/fixedColumns.dataTables.min.css">

<script src="https://cdn.datatables.net/fixedheader/3.4.0/js/dataTables.fixedHeader.min.js"></script>
<script src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.min.js"></script>


<?php include_once ROOT . '/includes/app-js.php' ?>

<script>
    // var barCodeID = "<?php // echo isset($_GET["BarCodeID"]) && !empty($_GET["BarCodeID"]) ? $_GET["BarCodeID"] : "" 
                        ?>";

    var table;

    $(document).on('keydown', 'input[pattern]', function(e) {
        var input = $(this);
        var oldVal = input.val();
        var regex = new RegExp(input.attr('pattern'), 'g');

        setTimeout(function() {
            var newVal = input.val();
            if (!regex.test(newVal)) {
                input.val(oldVal);
            }
        }, 1);
    });

    var poptable;

    $(document).on('shown.bs.modal', '#modalVarietyList', function() {
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
            ordering: false,
            language: {
                lengthMenu: "Show _MENU_ records",
                paginate: {
                    previous: "<i class='mdi mdi-chevron-left'>",
                    next: "<i class='mdi mdi-chevron-right'>"
                }
            },
            drawCallback: function () {
                $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
            },
            dom: '<"row mb-2"<"col-md-6 d-flex align-items-center"lf><"col-md-6 d-flex justify-content-end"B>>rtip',
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
            language: {
                lengthMenu: "Show _MENU_ records",
                paginate: {
                    previous: "<i class='mdi mdi-chevron-left'>",
                    next: "<i class='mdi mdi-chevron-right'>"
                }
            },
            drawCallback: function () {
                $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
            },
            dom: '<"row mb-2"<"col-md-6 d-flex align-items-center"lf><"col-md-6 d-flex justify-content-end"B>>rtip',
            buttons: [

                {
                    extend: 'excel',
                    text: 'Download Excel',
                    className: 'btnExcelDataTable',
                    title: tableTitle || 'Anti Dumping Opening & Closing Report',
                    footer: true,
                    attr: {
                        style: 'padding: 3px 5px;margin-left: -4px;margin-top: 5px;'
                    },
                    exportOptions: {
                        columns: ':visible'
                    }
                }
            ],
        });
        $('#tblDataTable').on('draw.dt', function () {
    var info = table.page.info();

    $('#tblDataTable_info').html(
        'Showing ' +
        (info.start + 1) +
        ' to ' +
        (info.end - 1) +
        ' of ' +
        (info.recordsDisplay - 1) +
        ' entries'
    );
});
        $("<button class='btn btn-primary' id='btnMoreSearch' style='padding: 3px 5px;margin-left: 10px;'>More Search</button>" +
            // "<a class='btn btn-secondary' id='btnRefresh' href='javascript:void(0);' style='padding: 3px 5px;margin-left: 10px;'>Refresh</a>"+
            "<a class='btn btn-primary' id='LoadAll' href='javascript:void(0);' style='padding: 3px 5px;margin-left: 10px;'>Load All</a>"
        ).insertAfter("#tblDataTable_filter label");
        $("#tblDataTable_filter input").attr("placeholder", "ProductDesc,Slabs,PackingStyle,Grade Etc");
        $("<style>#tblDataTable_filter input::placeholder { font-size: 10px; }</style>").appendTo("head");
        // let noOfOpeningStock = 0;
        // let noOfProduction = 0;
        // let noOfRepackingIN = 0;
        // let noOfIbtIn = 0;
        // let noOfPhysicalIn = 0;
        // let noOfFGIN = 0;
        // let noOfReturnContainer = 0;
        // let noOfShipmentOut = 0;
        // let noOfMelting = 0;
        // let noOfRepackingOut = 0;
        // let noOfIbtOut = 0;
        // let noOfLocalSales = 0;
        // let noOfPhysicalOut = 0;
        // let noOfClosingStock = 0;
        // $('.OpeningStock').each(function () {
        //     // Parse the text content as a float, ignoring any commas
        //     const OpeningStock = parseFloat($(this).text().replace(/,/g, ''));
        //     if (!isNaN(OpeningStock)) {
        //       noOfOpeningStock += OpeningStock;
        //     }
        // });
        // $('.Production').each(function () {
        //     // Parse the text content as a float, ignoring any commas
        //     const Production = parseFloat($(this).text().replace(/,/g, ''));
        //     if (!isNaN(Production)) {
        //       noOfProduction += Production;
        //     }
        // });

        //  $('.RepackingIN').each(function () {
        //     // Parse the text content as a float, ignoring any commas
        //     const RepackingIN = parseFloat($(this).text().replace(/,/g, ''));
        //     if (!isNaN(RepackingIN)) {
        //       noOfRepackingIN += RepackingIN;
        //     }
        // });
        // $('.IbtIn').each(function () {
        //     // Parse the text content as a float, ignoring any commas
        //     const IbtIn = parseFloat($(this).text().replace(/,/g, ''));
        //     if (!isNaN(IbtIn)) {
        //       noOfIbtIn += IbtIn;
        //     }
        // });
        // $('.PhysicalIn').each(function () {
        //     // Parse the text content as a float, ignoring any commas
        //     const PhysicalIn = parseFloat($(this).text().replace(/,/g, ''));
        //     if (!isNaN(PhysicalIn)) {
        //       noOfPhysicalIn += PhysicalIn;
        //     }
        // });
        // $('.FGIN').each(function () {
        //     // Parse the text content as a float, ignoring any commas
        //     const FGIN = parseFloat($(this).text().replace(/,/g, ''));
        //     if (!isNaN(FGIN)) {
        //       noOfFGIN += FGIN;
        //     }
        // });

        //  $('.ReturnContainer').each(function () {
        //     // Parse the text content as a float, ignoring any commas
        //     const ReturnContainer = parseFloat($(this).text().replace(/,/g, ''));
        //     if (!isNaN(ReturnContainer)) {
        //       noOfReturnContainer += ReturnContainer;
        //     }
        // });
        // $('.ShipmentOut').each(function () {
        //     // Parse the text content as a float, ignoring any commas
        //     const ShipmentOut = parseFloat($(this).text().replace(/,/g, ''));
        //     if (!isNaN(ShipmentOut)) {
        //       noOfShipmentOut += ShipmentOut;
        //     }
        // });
        // $('.Melting').each(function () {
        //     // Parse the text content as a float, ignoring any commas
        //     const Melting = parseFloat($(this).text().replace(/,/g, ''));
        //     if (!isNaN(Melting)) {
        //       noOfMelting += Melting;
        //     }
        // });
        // $('.RepackingOut').each(function () {
        //     // Parse the text content as a float, ignoring any commas
        //     const RepackingOut = parseFloat($(this).text().replace(/,/g, ''));
        //     if (!isNaN(RepackingOut)) {
        //       noOfRepackingOut += RepackingOut;
        //     }
        // });

        //  $('.IbtOut').each(function () {
        //     // Parse the text content as a float, ignoring any commas
        //     const IbtOut = parseFloat($(this).text().replace(/,/g, ''));
        //     if (!isNaN(IbtOut)) {
        //       noOfIbtOut += IbtOut;
        //     }
        // });
        // $('.LocalSales').each(function () {
        //     // Parse the text content as a float, ignoring any commas
        //     const LocalSales = parseFloat($(this).text().replace(/,/g, ''));
        //     if (!isNaN(LocalSales)) {
        //       noOfLocalSales += LocalSales;
        //     }
        // });
        // $('.PhysicalOut').each(function () {
        //     // Parse the text content as a float, ignoring any commas
        //     const PhysicalOut = parseFloat($(this).text().replace(/,/g, ''));
        //     if (!isNaN(PhysicalOut)) {
        //       noOfPhysicalOut += PhysicalOut;
        //     }
        // });
        // $('.ClosingStock').each(function () {
        //     // Parse the text content as a float, ignoring any commas
        //     const ClosingStock = parseFloat($(this).text().replace(/,/g, ''));
        //     if (!isNaN(ClosingStock)) {
        //       noOfClosingStock += ClosingStock;
        //     }
        // });

        // $('#TotalOpeningStock').text(noOfOpeningStock.toLocaleString('en-US', {
        //     minimumFractionDigits: 2,
        //     maximumFractionDigits: 2
        // }));
        // $('#TotalProduction').text(noOfProduction.toLocaleString('en-US', {
        //     minimumFractionDigits: 2,
        //     maximumFractionDigits: 2
        // }));
        //  $('#TotalRepackingIN').text(noOfRepackingIN.toLocaleString('en-US', {
        //     minimumFractionDigits: 2,
        //     maximumFractionDigits: 2
        // }));
        // $('#TotalIbtIn').text(noOfIbtIn.toLocaleString('en-US', {
        //     minimumFractionDigits: 2,
        //     maximumFractionDigits: 2
        // }));
        //  $('#TotalPhysicalIn').text(noOfPhysicalIn.toLocaleString('en-US', {
        //     minimumFractionDigits: 2,
        //     maximumFractionDigits: 2
        // }));
        // $('#TotalFGIN').text(noOfFGIN.toLocaleString('en-US', {
        //     minimumFractionDigits: 2,
        //     maximumFractionDigits: 2
        // }));
        //  $('#TotalReturnContainer').text(noOfReturnContainer.toLocaleString('en-US', {
        //     minimumFractionDigits: 2,
        //     maximumFractionDigits: 2
        // }));
        // $('#TotalShipmentOut').text(noOfShipmentOut.toLocaleString('en-US', {
        //     minimumFractionDigits: 2,
        //     maximumFractionDigits: 2
        // }));
        //  $('#TotalMelting').text(noOfMelting.toLocaleString('en-US', {
        //     minimumFractionDigits: 2,
        //     maximumFractionDigits: 2
        // }));
        // $('#TotalRepackingOut').text(noOfRepackingOut.toLocaleString('en-US', {
        //     minimumFractionDigits: 2,
        //     maximumFractionDigits: 2
        // }));
        //  $('#TotalIbtOut').text(noOfIbtOut.toLocaleString('en-US', {
        //     minimumFractionDigits: 2,
        //     maximumFractionDigits: 2
        // }));
        // $('#TotalLocalSales').text(noOfLocalSales.toLocaleString('en-US', {
        //     minimumFractionDigits: 2,
        //     maximumFractionDigits: 2
        // }));
        //  $('#TotalPhysicalOut').text(noOfPhysicalOut.toLocaleString('en-US', {
        //     minimumFractionDigits: 2,
        //     maximumFractionDigits: 2
        // }));
        // $('#TotalClosingStock').text(noOfClosingStock.toLocaleString('en-US', {
        //     minimumFractionDigits: 2,
        //     maximumFractionDigits: 2
        // }));

        function updateInfoCount() {
        var info = table.page.info();

        $('#tblDataTable_info').html(
            'Showing ' +
            (info.start + 1) +
            ' to ' +
            (info.end - 1) +
            ' of ' +
            (info.recordsDisplay - 1) +
            ' entries'
        );
    }


    updateInfoCount(); // Initial load

    table.on('draw', function () {
        updateInfoCount(); // Search/filter
    });
    }


    $(document).on('click', '#LoadAll', function() {
        var LoadAll = '1';
        getProdDataByProdDate('', LoadAll);
    })
    /*
        $(document).on('click','#btnRefresh',function(){
            getProdDataByProdDate();
        }) */
    $(document).on('click', '#btnMoreSearch', function() {
        getProdDataByProdDate();
    })

    $("#txtVariety").on("click", function() {
        // if($("#drpPoNumber").val() != "") {
        getVarieties();

    });

    $(document).ready(function() {
        var productionPlant = <?php echo $ProductionPlant ?>;

        if (productionPlant != 0) {
            $("#drpProductionPlant").prop('disabled', true);
            $("#drpProductionPlant").val(productionPlant).trigger('change');
        } else {
            $("#drpProductionPlant").prop('disabled', false);
            $("#drpProductionPlant").val("All")
        }

        var plantId = $("#drpProductionPlant").val();

        var $drpPoNumber = $("#drpPoNumber");

        if (plantId != 'All') {
            $drpPoNumber.find('option').each(function() {
                var optionPlantId = $(this).data('plantid');

                var plantIdArray = String(optionPlantId).split(',').map(function(id) {
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
        }

        // $("#drpPoNumber").prop('disabled', true);
        $("#txtTotalInSlabs").prop('disabled', true);
        $("#txtTotalOutSlabs").prop('disabled', true);
        $("#txtOpeningStock").prop('disabled', true);
        $("#txtClosingStock").prop('disabled', true);

        if (productionPlant != 0) {

            $("#drpStockBelongs").val($("[data-processorID='" + productionPlant + "']").val()).trigger('change');
            $("#drpStockBelongs").prop("disabled", true);
        } else {
            $("#drpStockBelongs").val("").trigger('change');
            $("#drpStockBelongs").prop("disabled", false);
        }
    });



    $(document).on("click", ".selectVariety", function() {
        var selectedVariety = $(this).val();
        $("#txtVariety").val(selectedVariety);

        var selectedVarietyID = $(this).attr("data-po-grade-id");
        $("#txtVariety").attr("data-po-grade-id", selectedVarietyID);

        var selectedVarietyID = $(this).attr("data-varietyid");
        $("#txtVariety").attr("data-varietyid", selectedVarietyID);

        $('#modalVarietyList').modal('hide');
        getGrades();
        var PoID = $("#drpPoNumber").val();
        if (PoID == '') {
            getAllGrades();
        } else {
            getTotalByPoNumber();
        }
    })

    function fechAllPlantIds() {
        var plantId = $("#drpProductionPlant").val();
        var allPlantIds = [];
        if (plantId == 'All') {
            var $drpProductionPlant = $("#drpProductionPlant");
            $drpProductionPlant.find('option').each(function() {
                var optionPlantId = $(this).data('plantid');
                if (optionPlantId) {
                    var ids = String(optionPlantId)
                        .split(',')
                        .map(function(id) {
                            return id.trim();
                        });
                    allPlantIds = allPlantIds.concat(ids);
                }
            });
            plantId = allPlantIds.join(',');
        }
        return plantId;
    }

    function getVarieties() {
        $.ajax({
            type: 'POST',
            url: 'Ajax/ajax-anti-dumping-opening-closing-report.php',
            data: {
                'methodType': 'poNumber',
                'pOID': $("#drpPoNumber").val(),
            },
            success: function(data) {
                if (data) {


                    if (data.Error) {
                        alert(data.Error);
                    } else {
                        $('#modalVariety').html("").html(data.HtmlRes);
                        $('#modalVarietyList').modal('show');

                        if (data.ReadonlyDropdowns.PackingStyle == "Enable") {
                            $("#drpPackingStyle").prop("disabled", false);
                        } else {
                            $("#drpPackingStyle").prop("disabled", true);
                        }
                    }



                }
            }
        });
    }

    $(document).ready(function() {
        getProdDataByProdDate();
    });

    // $("#txtProductionDate").on("change", function () {
    //     getAllPOs();
    // });
    // $("#txtProductionToDate").on("change", function () {
    //     getAllPOs();
    // });

    // $("#drpGroup").on("change", function () {
    //     $("#txtVariety").val('').trigger('change');
    //     $("#drpGrade").val('').trigger('change');
    //     $("#drpPackingStyle").val('').trigger('change');
    //     $("#drpFreezingType").val('').trigger('change');
    //     $("#drpSoaking").val('').trigger('change');
    //     var selectedGroup = $(this).val();

    //     if (selectedGroup === "IBT IN") { 
    //         // Replace with the actual group value to match
    //         $("#drpFromPlant").closest("div").show();
    //         $("#drpToPlant").closest("div").hide();
    //     } else if(selectedGroup === "IBT Out"){
    //         $("#drpFromPlant").closest("div").hide();
    //         $("#drpToPlant").closest("div").show();
    //     } else{
    //          $("#drpFromPlant").closest("div").hide();
    //         $("#drpToPlant").closest("div").hide();
    //     }

    //     if(selectedGroup === "Shipment Out") {
    //         getAllPOs();
    //     } else{
    //          if($("#drpGroupType").val() != '' && $("#drpGroupType").val() == 2){
    //         var resPoNumber = <?php echo json_encode($resPoNumber) ?>;
    //         var list = "<option value=''>Select</option>";
    //                 if (resPoNumber) {
    //                     $.each(resPoNumber, function (index, value) {
    //                         list += "<option value=" + value.PoID + " data-plantid='" + value.ProductionPlantID + "'>" + value.PoNo + "</option>";
    //                     });
    //                 }

    //                 $('#drpPoNumber').select2('destroy');
    //                 $('#drpPoNumber').html(list);
    //                 $('#drpPoNumber').select2();
    //             }
    //     }
    // });

    $("#drpPoNumber").on("change", function() {
        $("#txtVariety").val('').trigger('change');
        $("#drpGrade").val('').trigger('change');
        $("#drpPackingStyle").val('').trigger('change');
        $("#drpFreezingType").val('').trigger('change');
        $("#drpSoaking").val('').trigger('change');
    });
    //     var resCsTransactionTypes = <?php echo json_encode($resCsTransactionTypes); ?>;
    //     if ($(this).val() == 1) {
    //          $("#drpPoNumber").prop('disabled', true);
    //         var list = "<option value=''>Select</option>";
    //         if (resCsTransactionTypes) {
    //             $.each(resCsTransactionTypes, function (index, value) {
    //                 if (value.TransType == 1 || value.TransType == 3 || value.TransType == 4) {
    //                     list += "<option value='" + value.TransCategory + "' data-transtypeid='" + value.TransTypeID + "'>" + value.TransCategory + "</option>";
    //                 }
    //             });
    //         }

    //         $('#drpGroup').select2('destroy');
    //         $('#drpGroup').html(list);
    //         $('#drpGroup').select2();
    //     } else if($(this).val() == 2) {

    //         $("#drpPoNumber").prop('disabled', false);
    //         var list = "<option value=''>Select</option>";
    //         if (resCsTransactionTypes) {
    //             $.each(resCsTransactionTypes, function (index, value) {
    //                 if (value.TransType == 2) {
    //                     list += "<option value='" + value.TransCategory + "' data-transtypeid='" + value.TransTypeID + "'>" + value.TransCategory + "</option>";
    //                 }
    //             });
    //         }

    //         $('#drpGroup').select2('destroy');
    //         $('#drpGroup').html(list);
    //         $('#drpGroup').select2();
    //     } else{
    //         $("#drpPoNumber").prop('disabled', true);
    //         var list = "<option value=''>Select</option>";

    //         $('#drpGroup').select2('destroy');
    //         $('#drpGroup').html(list);
    //         $('#drpGroup').select2();
    //     }


    // });

    function getGrades() {
        poGraidID = $.trim($("#txtVariety").attr("data-po-grade-id"));
        var PoID = $("#drpPoNumber").val();

        $.ajax({
            type: 'POST',
            url: 'Ajax/ajax-anti-dumping-opening-closing-report.php',
            data: {
                'methodType': 'varietyChange',
                'pOID': $("#drpPoNumber").val(),
                'varietyID': $("#txtVariety").attr("data-varietyid"),
                'poGradeID': poGraidID
            },
            success: function(data) {
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
                $("#drpFreezingType").val(obj.FreezingTypeID).trigger("change").prop("disabled", true);
                $("#drpSoaking").val(obj.SoakingType).trigger("change").prop("disabled", true);
                // $("#txtSalt").val(obj.Salt).attr("readonly", "true");

                $("#hdnPackingStyle").val(obj.PackingStyleID);
                if (obj.PackingStyle == "Enable") {
                    $("#drpPackingStyle").prop("disabled", false).val(obj.PackingStyleID).trigger("change");
                } else {
                    $("#drpPackingStyle").prop("disabled", true).val(obj.PackingStyleID).trigger("change");
                }
                // obj.PackingStyle
            }
        });
    }

    function getAllGrades() {
        var PoID = $("#drpPoNumber").val();

        $.ajax({
            type: 'POST',
            url: 'Ajax/ajax-anti-dumping-opening-closing-report.php',
            data: {
                'methodType': 'getVarietyAllGrades',
                'pOID': $("#drpPoNumber").val(),
                'varietyID': $("#txtVariety").attr("data-varietyid"), // $("#txtVariety").val(),
            },
            success: function(data) {
                var grade = $("#drpGrade").val();
                //alert("Hi")
                $("#drpGrade").prop("disabled", false);
                var list = "<option value=''>Select</option>";
                if (data) {
                    $.each(data, function(index, value) {
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

    function getProdDataByProdDate(pageNo = '', LoadAll = '') {
        var plantId = fechAllPlantIds();
        var varietyid = '';
        if ($.trim($("#drpPoNumber").val()) == '') {
            varietyid = $("#drpGrade option:selected").data("varietyid");
            // varietyid = $.trim($("#txtVariety").attr("data-varietyid"));
        } else {
            varietyid = $.trim($("#txtVariety").attr("data-varietyid"));
        }
        var txtStartDate = $("#txtProductionDate").val();
        var txtEndDate = $("#txtProductionToDate").val();
        var startDate = new Date(txtStartDate);
        var endDate = new Date(txtEndDate);
        var timeDiff = endDate.getTime() - startDate.getTime();
        var daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));
        var searchkey = $("#tblDataTable_filter input").val();

        var days = daysDiff + 1;
        if (days > 365) {
            var formData = $('#frmInward').serialize();
            formData += '&ProductionPlant=' + $.trim(plantId);
            var queryString = "Ajax/ajax-anti-dumping-opening-closing-report.php?" + formData + "&excelDownload=T&LoadAll=1";
            window.open(queryString, '_blank');
            return false;
        }
        $.ajax({
            url: 'Ajax/ajax-anti-dumping-opening-closing-report.php',
            type: 'POST',
            data: {
                methodType: "getList",
                txtProductionDate: $.trim($("#txtProductionDate").val()),
                txtProductionToDate: $.trim($("#txtProductionToDate").val()),
                // drpGroupType: $.trim($("#drpGroupType").val()),
                // drpGroup: $.trim($("#drpGroup").val()),
                // drpProductionPlant: $.trim($("#drpProductionPlant").val()),
                drpProductionPlant: $.trim(plantId),
                drpPoNumber: $.trim($("#drpPoNumber").val()),
                drpVariety: varietyid,
                drpPackingStyle: $.trim($("#drpPackingStyle").val()),
                drpGrade: $.trim($("#drpGrade").val()),
                drpFreezingType: $.trim($("#drpFreezingType").val()),
                drpSoaking: $.trim($("#drpSoaking").val()),
                drpProductCategory: $.trim($("#drpProductCategory").val()),
                drpType: $.trim($("#drpType").val()),
                PageNo: pageNo,
                LoadAll: LoadAll,
                MoreSearch: searchkey
            },
            success: function(data) {
                $("#divDataList").html(data);
                $tableName = "Anti Dumping Opening & Closing Report";
                fnShowDataTable($tableName);
                if (searchkey) {
                    $("#tblDataTable_filter input").val(searchkey.trim())
                }
                return false;
            }
        });
    }

    $(document).on("click", "#btn-submit", function() {
        var plantId = $("#drpProductionPlant").val();
        if (plantId == '') {
            alert("Please select Production Plant");
            return false;
        }
        $("#tblDataTable_filter input").val('')
        getProdDataByProdDate();
    });

    function getAllPOs() {
        $.ajax({
            type: 'POST',
            url: 'Ajax/ajax-anti-dumping-opening-closing-report.php',
            data: {
                'methodType': 'allPoNumbers',
                'ProductionPlant': $("#drpProductionPlant").val(),
                'ProductionDate': $("#txtProductionDate").val(),
                'ProductionToDate': $("#txtProductionToDate").val(),
            },
            success: function(data) {
                if (data) {
                    var list = "<option value=''>Select</option>";
                    if (data) {
                        $.each(data, function(index, value) {
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

    $(document).on("click", ".search", function() {
        getProdDataByProdDate();
    })
    $(document).on("click", ".btnPageno", function(e) {
        getProdDataByProdDate($(this).attr('data-pageno'));
    });
    $("#drpProductionPlant").on("change",function(){
        $("#divDataList").html("");
    });
</script>


</body>

</html>
