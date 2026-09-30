<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include $_SERVER['DOCUMENT_ROOT'] . "/includes/config.php";


$filterYear = "T";
$filterConsignee = "T";
$filterCountry = "T";
$filterButButton = "T";
$filterPortDestinations = "T";
$filterGrade = "T";
$filterTypeProduct = "T";
$filterShortName = "T";
$filterPIStatus = "T";
$PageTitleFolder = "Reports";
$PageTitle = "Price Book";

?>
<!doctype html>
<html lang="en">

<head>

    <?php include_once ROOT . '/includes/head.php' ?>
    <?php include $IncludesPath . "head-css.php"; ?>
    <style>
        #tblDataTable {
            width: 100% !important;
            table-layout: auto;
        }

        #tblDataTable th,
        #tblDataTable td {
            box-sizing: border-box;
            white-space: nowrap;
            vertical-align: middle;
        }

        #tblDataTable_wrapper .dataTables_scrollHead table,
        #tblDataTable_wrapper .dataTables_scrollBody table {
            margin: 0 !important;
        }

        table.dataTable tbody tr td input {
            height: 24px;        /* adjust as needed */
            padding: 2px 6px;    /* reduce internal spacing */
            font-size: 12px;     /* smaller text = smaller box */
        }
        table.dataTable tbody tr {
            height: 28px !important;
        }

        table.dataTable tbody td {
            padding: 3px 6px !important;
        }
        body[data-layout=horizontal] .page-content {
            margin-top: 0px !important;
        }

        .export-col-item:hover {
            background-color: #0d6efd !important;
            color: #fff !important;
            border-color: #0d6efd !important;
            transition: 0.2s;
        }

        .export-col-item input:checked+span {
            font-weight: 600;
        }

        /* .export-col-item.active {
            background-color: #0d6efd !important;
            color: #fff !important;
            border-color: #0d6efd !important;
            transition: 0.2s;
        } */

        #tblDataTable_wrapper .dt-buttons.btn-group.flex-wrap,
        .dt-buttons.btn-group.flex-wrap {
            float: right;

            margin-left: 7px;
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

                <?php // include_once ROOT . 'Sales/includes/page-filter.php' 
                ?>
                <?php include_once WORKING_MODULE . '/includes/page-filter.php' ?>

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <!-- <div class="card">
                            <div class="card-body"> -->

                        <div>
                            <div class="card">
                                <div class="card-body" id="divDataList">
                                </div>
                            </div>
                        </div>

                        <!-- </div>
                            </div> -->


                    </div>
                </div>

            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->
        <?php include_once ROOT . '/includes/footer.php' ?>

    </div>
    <!-- end main content-->

</div>
<!-- END layout-wrapper -->

<?php include_once ROOT . '/includes/common-js.php' ?>

<!-- Required datatable js -->
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

<?php include_once ROOT . '/includes/app-js.php' ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/fixedcolumns/4.3.0/css/fixedColumns.dataTables.min.css">
<script src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.min.js"></script>

<script>
    $(document).on('click', '.read-more', function (e) {
        e.preventDefault();
        var $td = $(this).closest('td');
        $td.find('.short-text, .read-more').hide();
        $td.find('.full-text').removeClass('d-none');
        $td.append('<a href="#" class="read-less">Read less</a>');
    });

    $(document).on('click', '.read-less', function (e) {
        e.preventDefault();
        var $td = $(this).closest('td');
        $td.find('.full-text').addClass('d-none');
        $td.find('.short-text, .read-more').show();
        $(this).remove();
    });

    function updateExportColUI() {
        $(".export-col-item").each(function () {
            let chk = $(this).find("input.export-col");
            if (chk.prop("checked")) {
                $(this).addClass("active");
            } else {
                $(this).removeClass("active");
            }
        });
    }

    function decodeHtml(html) {
        return $('<textarea/>').html(html).text();
    }


    function fnShowDataTable() {
        var table = $('#tblDataTable')
            .on('init.dt', function (e, settings) {
                // Get DataTable API instance correctly
                var api = new $.fn.dataTable.Api(settings);
                let selectorHtml = `
    <div class="mb-2 d-flex align-items-center">
        <label class="form-check-label fw-bold me-2">Select All</label>
        <div class="form-check form-switch">
            <input class="form-check-input" type="checkbox" id="selectAllCols" checked>
        </div>
    </div>

    <div id="columnSelectorList" 
        style="max-height: 140px; overflow-y: auto; border: 1px solid #ddd; border-radius: 6px; padding: 8px;">
`;

                api.columns().every(function (index) {
                    const columnTitle = $(this.header()).text().trim();
                    if (columnTitle !== "") {
                        selectorHtml += `
            <label class="export-col-item badge rounded-pill bg-light text-dark border 
                           d-inline-flex align-items-center p-2 me-2 mb-2" style="cursor:pointer;">
                <input type="checkbox" class="export-col form-check-input me-2" value="${index}" checked>
                ${columnTitle}
            </label>
        `;
                    }
                });

                selectorHtml += `</div>`;
                $("#columnSelector").html(selectorHtml);


                window.selectedColumnsCache = [];
                $(".export-col:checked").each(function () {
                    window.selectedColumnsCache.push(parseInt($(this).val()));
                });

            })
            .DataTable({
                scrollY: "600px",
                scrollX: true,
                scrollCollapse: true,
                paging: true,
                pageLength: 500,
                destroy: true,
                autoWidth: false,
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
                columnDefs: [{
                    targets: 15,
                    width: '200px'
                }],
                fixedColumns: {
                    leftColumns: 3
                },
                buttons: [{
                    extend: 'excel',
                    text: 'Download Excel',
                    className: 'btnExcelDataTable',
                    title: "Price Book Report",
                    footer: true,
                    exportOptions: {
                        columns: function (idx) {
                            return window.selectedColumnsCache.includes(idx);
                        },


                        format: {
                            body: (function () {

                                // Cache header titles once
                                let headerTexts = [];
                                $('#tblDataTable thead th').each(function () {
                                    headerTexts.push($(this).text().trim().toLowerCase());
                                });

                                // Recognized date headers (lowercase)
                                const dateColumnNames = ["po date", "shipment date", "stock out date"];

                                return function (data, row, column, node) {

                                    // Input field export optimization
                                    var $input = $('input', node);
                                    if ($input.length) {
                                        return $input.val();
                                    }
                                    let originalIndex = $(node).closest('td, th').index();
                                    // Date formatting based on header, not index
                                    if (dateColumnNames.includes(headerTexts[originalIndex])) {
                                        if (data === "--") return "--";
                                        return moment(data, "DD-MM-YYYY").format("YYYY-MM-DD");
                                    }

                                    return decodeHtml(data);
                                };
                            })()
                        }
                    }
                }],
                initComplete: function () {
                    var api = this.api();
                    setTimeout(function() {
                        api.columns.adjust();
                        if(api.fixedColumns) {
                            api.fixedColumns().relayout();
                        }
                    }, 250);
                }
            });

        function adjustShipmentPriceReportColumns() {
            var fixedColumns = table.fixedColumns ? table.fixedColumns() : null;
            table.columns.adjust();
            if (fixedColumns && fixedColumns.relayout) {
                fixedColumns.relayout();
            }
        }

        table.on('draw.dt', function () {
            adjustShipmentPriceReportColumns();
        });

        setTimeout(function () {
            adjustShipmentPriceReportColumns();
        }, 100);




        // Select All functionality
        $(document).on('change', '#selectAllCols', function () {
            $(".export-col").prop('checked', this.checked);
            window.selectedColumnsCache = [];
            $(".export-col:checked").each(function () {
                window.selectedColumnsCache.push(parseInt($(this).val()));
            });

            updateExportColUI();
        });


        // $(document).on("click", ".export-col-item", function (e) {
        //     if (e.target.tagName !== "INPUT") {
        //         let chk = $(this).find("input.export-col");
        //         chk.prop("checked", !chk.prop("checked"));
        //     }

        //     // Add active UI color when checked
        //     if ($(this).find("input.export-col").prop("checked")) {
        //         $(this).addClass("bg-primary text-white border-primary");
        //     } else {
        //         $(this).removeClass("bg-primary text-white border-primary");
        //     }
        // });
        $(document).on('change', '.export-col', function () {
            window.selectedColumnsCache = [];
            $(".export-col:checked").each(function () {
                window.selectedColumnsCache.push(parseInt($(this).val()));
            });

            updateExportColUI();
        });


    }

    $(document).on('change', '#drpConsigneeList', function () {
        var Buyer = $('#drpConsigneeList').val();

        $.ajax({
            url: 'Ajax/AjaxBrandSearch.php',
            type: 'POST',
            data: {
                'Buyer': Buyer,
            },
            success: function (data) {
                var response = JSON.parse(data);
                //  alert(data);
                console.log(response);
                // $("#drpConsigneeList").html(response.Brand);
                $("#drpCountryList").html(response.Country);
                $('#drpCountryList').change();
            }

        });

    });


    $('#drpCountryList').change(function () {
        var CountryId = $('#drpCountryList').val();

        $.ajax({
            type: 'POST',
            url: 'Ajax/AjPortDtls.php',
            //dataType: 'json',
            data: {
                'CountryId': CountryId
            },
            success: function (response) {
                //var myJSON = JSON.parse(JSON.stringify(response));
                // console.log(response);
                $("#drpPortDestinations").html(response);
                // $("#FinalDestination").html(response);


            }
        });


    });

    $(document).on('change', '.remarks', function () {
        let id = $(this).data('id');
        let field = $(this).data('field');
        let table = $(this).data('table');
        let row = $(this).closest('tr');
        let price = row.find('.ddpPrice').val();
        let remarks = row.find('.remarks').val();
        $.ajax({
            url: "Ajax/AjaxShipmentLineItemwisePriceReport.php",
            type: "POST",
            data: {
                MethodType: "UpdateDDPPriceRemarks",
                ItemID: id, DDPPrice: price,
                Remarks: remarks,
                Table: table,
                FromDate: $("#fromDate").val(),
                ToDate: $("#toDate").val(),
            },
            success: function (res) {
                console.log("Saved: " + id);
            }
        });
    });


    $("#btnSearch").on("click", function () {

        var exporter = $("#drpExporterList").val();
        var txtStartDate = $("#fromDate").val();
        var txtEndDate = $("#toDate").val();

        var startDate = new Date(txtStartDate);
        var endDate = new Date(txtEndDate);

        var timeDiff = endDate.getTime() - startDate.getTime();
        var daysDiff = Math.ceil(timeDiff / (1000 * 3600 * 24));

        var days = daysDiff + 1;

        console.log(days);
        // temperary condition to escape excel download for large data
        if (days > 31000) {
            var url = 'Ajax/AjaxShipmentLineItemwisePriceReport.php?Exporter=' + exporter + '&FromDate=' + txtStartDate + '&ToDate=' + txtEndDate + '&excelDownload=T';
            window.open(url, '_blank');
        } else {
            $.ajax({
                url: 'Ajax/AjaxShipmentLineItemwisePriceReport.php',
                type: 'POST',
                data: {
                    Year: $("#drpYear").val(),
                    Consignee: $("#drpConsigneeList").val(),
                    Country: $("#drpCountryList").val(),
                    PortDestination: $("#drpPortDestinations").val(),
                    Product: $("#drpProductList").val(),
                    Variety: $("#drpShortName").val(),
                    FromDate: $("#fromDate").val(),
                    ToDate: $("#toDate").val(),
                    Status: $("#drpPiStatus").val(),
                    Grade: $("#drpGrade").val(),
                    TypeProduct: $("#drpTypeProduct").val(),
                },
                success: function (data) {
                    var data = JSON.parse(data);
                    debugger;
                    filterDropdowns(data);
                    $("#divDataList").html(data.html);
                    fnShowDataTable();
                    return false;
                }
            });
        }

    })

    filterDropdowns = function (data) {
        var countryDropdown = $("#drpCountryList");
        countryDropdown.empty();

        countryDropdown.append('<option value="">Select Country</option>');

        $.each(data.filteredCountries, function (index, item) {
            countryDropdown.append(
                '<option value="' + item.id + '">' + item.value + '</option>'
            );
        });

        var portDropdown = $("#drpPortDestinations");
        portDropdown.empty();

        portDropdown.append('<option value="">Select Port</option>');

        $.each(data.filteredPorts, function (index, item) {
            portDropdown.append(
                '<option value="' + item.id + '">' + item.value + '</option>'
            );
        });
    }
$("#drpConsigneeList").on("change", function () {
    $("#divDataList").html("");
});
</script>

</body>

</html>
