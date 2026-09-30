<?php
$PageTitle = "Return Report";
$PageTitleFolder = "Cold Store";
$Currentyear = date('Y');

$filterPlant = "T";
$filterSpecies = "T";
// $filterSoakingOne = "T";
$filterShortName = "T";
$filterGrade = "T";
$filterPckgStyle = "T";
$filterfromDate = "T";
$filtertoDate = "T";
$filterSideButton = "T";


$excludeList = array("ColdStores", "Blocks", "Floors", "StoreRacks", "Stock", "StockBelongsTo", "ProductGroup", "Exporters", "Consignee", "Processor", "Shifts", "PackingType", "FreezingType", "Abstatus", "OptType", "OutType", "Variety", "StockAvailableVariety", "StockInVarietyList", "PoNumber", "GeneralCategories", "Status", "TenantType", "ProductionLines", "Brand", "BrandType", "Tranction", "TypeProduct", "Type", "CsTransactionTypes", "ProductForm", "Preparation", "GlazeType", "ShipoutPoNumber", "ProductionPlant", "PoNumberNew", "TransactionTypesNew", "InwardGroups");

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
?>

<!doctype html>
<html lang="en">

<head>

    <!-- DataTables -->
    <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css"
        rel="stylesheet" type="text/css" />
    <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css"
        rel="stylesheet" type="text/css" />
    <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-select-bs4/css//select.bootstrap4.min.css"
        rel="stylesheet" type="text/css" />

    <!-- Responsive datatable examples -->
    <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css"
        rel="stylesheet" type="text/css" />


    <link href="<?php echo $BaseDist ?>assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css"
        rel="stylesheet">
    <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css"
        rel="stylesheet">
    <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css"
        rel="stylesheet" />
    <?php include($IncludesPath . "head-css.php"); ?>
    <style>
        .dataTables_scrollBody {
            overflow-y: auto !important;
        }

        .dataTables_wrapper {
            overflow-x: auto;
        }
    </style>
</head>

<body data-sidebar="dark">
    <?php include($IncludesPath . "PreLoader.php"); ?>

    <!-- Begin page -->
    <div id="layout-wrapper">

        <?php
        include($IncludesPath . "topbar.php");
        include($IncludesPath . "sidebar.php");
        ?>

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">

            <div class="page-content">
                <div class="container-fluid">

                    <?php include($IncludesPath . "page-title.php"); ?>
                    <?php include "PopUpTable.php"; ?>

                    <div class="row">
                        <div class="col-12">
                            <?php include_once WORKING_MODULE . '/includes/page-filter.php' ?>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body" id="ATT_DATA" style="display:none">

                                    <table id="mytable-data" class="table dt-responsive nowrap w-100">

                                        <thead>
                                            <tr>
                                                <th>Sno</th>
                                                <th>Type</th>
                                                <th>Po Number</th>
                                                <th>Po Date</th>
                                                <th>Supc</th>
                                                <th>Product</th>
                                                <th>Grade</th>
                                                <th>Packing Style</th>
                                                <th>Mcs</th>
                                                <th>KGs</th>
                                                <th>Production Date</th>
                                                <th>Production Plant</th>
                                                <th>Packing Style</th>
                                                <th>Product</th>
                                                <th>Grade</th>
                                                <th>Packing Type</th>
                                                <th>Group</th>
                                                <th>Mcs</th>
                                                <th>KGs</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>


                                </div> <!-- end card body-->
                            </div> <!-- end card -->
                        </div><!-- end col-->
                    </div>
                </div>
            </div>
            <?php include($IncludesPath . "footer.php"); ?>
        </div>
    </div>
    <?php include($IncludesPath . "vendor-scripts.php"); ?>


    <!-- Required datatable js -->
    <script src="<?php echo $BaseDist ?>assets/libs/select2/js/select2.min.js"></script>

    <script src="<?php echo $BaseDist ?>assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/bootstrap-maxlength/bootstrap-maxlength.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/js/pages/form-advanced.init.js"></script>


    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
    <!-- Buttons examples -->
    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/jszip/jszip.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/pdfmake/build/pdfmake.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/pdfmake/build/vfs_fonts.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons/js/buttons.html5.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons/js/buttons.print.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons/js/buttons.colVis.min.js"></script>

    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-select/js/dataTables.select.min.js"></script>

    <!-- Responsive examples -->
    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
    <script
        src="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

    <!-- Datatable init js -->
    <script src="<?php echo $BaseDist ?>assets/js/pages/datatables.init.js"></script>

    <script src="<?php echo $BaseDist ?>assets/js/app.js"></script>
    <script src="https://debug.datatables.net/debug.js"></script>

    <Script>
        $('document').ready(function () {
            //loadAdvances();
            $("#btnSearch").click(function () {
                /*var fromDate = $('#fromDate').val();
                var toDate = $('#toDate').val();                  
                if( fromDate == "" || toDate == "" || fromDate == undefined || toDate == undefined ){                     
                    alert("Please Select Dates");                    
                    return false;      
                }*/
                loadAdvances();
            });

        });
        function loadAdvances() {

            if ($.fn.DataTable.isDataTable('#mytable-data')) {
                let table = $('#mytable-data').DataTable();
                table.search('').columns().search('').draw(); // Clear all search filters
                table.clear().destroy(); // Now destroy cleanly
            }


            var data = {
                fromDate: $('#txtFromDate').val(),
                toDate: $('#txtToDate').val(),
                fromPage: "Listpage",
                PageNo: 1, // set manually or dynamically if paginated

                productionPlant: $("#drpPlant").val(),
                species: $("#drpSpecies").val(),
                // Soaking: $("#drpSoaking").val(),
                Variety: $("#drpVariety").val(),
                Grade: $("#drpGrade").val(),
                PackingStyle: $("#drpPckgStyle").val(),
                // ProductCategory: $("#drpPDCategory").val()
            };

            $.ajax({
                url: 'Ajax/Aj-return-report-list',
                type: 'POST',
                data: data,
                success: function (response) {
                    // Handle successful response (HTML or JSON)
                    console.log("Server Response:", response);

                    // Example: display inside a div
                    $('#ATT_DATA').show();
                    //$('#ATT_DATA').html(response);
                    renderTable(response);
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error:", error);
                    $('#ATT_DATA').html('<p style="color:red;">Something went wrong.</p>');
                }
            });
        }

        function renderTable(data) {
            const tbody = document.querySelector('#mytable-data tbody');
            tbody.innerHTML = ''; // clear existing data if needed
            let counter = 1;
            console.log(data);
            if (data != null && data != '') {
                data.forEach(item => {

                    const breakupList = Array.isArray(item.BreakUpData) && item.BreakUpData.length > 0
                        ? item.BreakUpData
                        : [{}];

                    const firstBreakup = breakupList[0];
                    const rowspan = breakupList.length;
                    let row = document.createElement('tr');
                    row.innerHTML = `
                        <td rowspan="${rowspan}" style="vertical-align: middle; text-align: center;">${counter++}</td>
                        <td rowspan="${rowspan}" style="vertical-align: middle; text-align: center;">${item.ProductionTypeID || 'Return Container'}</td>
                        <td rowspan="${rowspan}" style="vertical-align: middle; text-align: center;">${item.PoNo || ''}</td>
                        <td rowspan="${rowspan}" style="vertical-align: middle; text-align: center;">${item.PoDate || ''}</td>
                        <td rowspan="${rowspan}" style="vertical-align: middle; text-align: center;">${item.DeviSupc || ''}</td>
                        <td rowspan="${rowspan}" style="vertical-align: middle; text-align: center;">${item.ShortName || ''}</td>
                        <td rowspan="${rowspan}" style="vertical-align: middle; text-align: center;">${item.Grade || ''}</td>
                        <td rowspan="${rowspan}" style="vertical-align: middle; text-align: center;">${item.PackingStyle || ''}</td>
                        <td rowspan="${rowspan}" style="vertical-align: middle; text-align: center;">${item.Mcs || ''}</td>
                        <td rowspan="${rowspan}" style="vertical-align: middle; text-align: center;">${item.PriceKgs || ''}</td>

                        <td>${firstBreakup.ProductionDate || ''}</td>
                        <td>${firstBreakup.ProductionPlantName || ''}</td>
                        <td>${firstBreakup.PackingStyle || ''}</td>
                        <td>${firstBreakup.ProductDescription || ''}</td>
                        <td>${firstBreakup.Grade || ''}</td>
                        <td>${firstBreakup.PackingType || ''}</td>
                        <td>${firstBreakup.Group || ''}</td>
                        <td>${firstBreakup.Mcs || ''}</td>
                        <td>${firstBreakup.Kgs || ''}</td>
                    `;
                    tbody.appendChild(row);

                    for (let i = 1; i < breakupList.length; i++) {
                        const breakup = breakupList[i];
                        let subRow = document.createElement('tr');
                        let emptyCells = '';
                        for (let j = 0; j < 10; j++) {
                            emptyCells += `<td style="display: none;"></td>`;
                        }
                        subRow.innerHTML = `
                            ${emptyCells}
                            <td>${breakup.ProductionDate || ''}</td>
                            <td>${breakup.ProductionPlantName || ''}</td>
                            <td>${breakup.PackingStyle || ''}</td>
                            <td>${breakup.ProductDescription || ''}</td>
                            <td>${breakup.Grade || ''}</td>
                            <td>${breakup.PackingType || ''}</td>
                            <td>${breakup.Group || ''}</td>
                            <td>${breakup.Mcs || ''}</td>
                            <td>${breakup.Kgs || ''}</td>
                        `;
                        tbody.appendChild(subRow);
                    }
                });
            } else {
                const row = document.createElement('tr');
                row.innerHTML = `<td>No data available</td>`;
            }
            if ($.fn.DataTable.isDataTable('#mytable-data')) {
                $('#mytable-data').DataTable().clear().destroy(); // Remove existing instance
            }

            $('#mytable-data').DataTable({
                responsive: true,
                scrollX: true,
                paging: false,
                searching: true,
                scrollY: '500px',
                scrollCollapse: true,
                autoWidth: false,
                responsive: false,
                ordering: false,
                dom: 'Bfrtip', // enables buttons
                buttons: [
                    {
                        extend: 'excelHtml5',
                        title: 'Return Container Report',
                        text: 'Download Excel',
                        className: 'btn btn-success'
                    }
                ],
                //pageLength: 10,
                //lengthMenu: [10, 25, 50, 100],
            });
        }

        function renderTable1(data) {
            const tbody = document.querySelector('#mytable-data tbody');
            let counter = 1;

            data.forEach(item => {
                const row = document.createElement('tr');

                row.innerHTML = `
            <td>${counter++}</td>
            <td>${item.ProductionTypeID || ''}</td>
            <td>${item.PoID || ''}</td>
            <td>${item.PoDate || ''}</td>
            <td>${item.emptyReturn || ''}</td>
            <td>${item.DeviSupc || ''}</td>
            <td>${item.Variety || ''}</td>
            <td>${item.Grade || ''}</td>
            <td>${item.PackingTypeID || ''}</td>
            <td>${item.PackingStyle || ''}</td>
            <td>${item.AvalMcs || ''}</td>
            <td>${item.looseBags || ''}</td>
            <td>${item.AvalKgs || ''}</td>
            <td>
                ${generateBreakupTable(item.BreakUpData)}
            </td>
        `;
                tbody.appendChild(row);
            });
        }

        function generateBreakupTable(breakups) {
            if (!Array.isArray(breakups) || breakups.length === 0) {
                return '<div class="breakup-table-row empty"><div></div>'.repeat(13) + '</div>';
            }

            let html = '<div class="breakup-table">';
            html += `<div class="breakup-header">
                        <div>Production Date</div>
                        <div>Transaction Date</div>
                        <div>Production Plant</div>
                        <div>PoNo</div>
                        <div>Packing Style</div>
                        <div>Product</div>
                        <div>Grade</div>
                        <div>Packing Type</div>
                        <div>Group</div>
                        <div>Mcs</div>
                        <div>Loose Bags</div>
                        <div>KGs</div>
                        <div>Remarks</div>
                    </div>`;

            breakups.forEach(item => {
                html += `<div class="breakup-row">
                            <div>${item.ProductionDate || ''}</div>
                            <div>${item.TransactionDate || ''}</div>
                            <div>${item.ProductionPlantName || ''}</div>
                            <div>${item.PoNo || ''}</div>
                            <div>${item.PackingStyle || ''}</div>
                            <div>${item.ProductDescription || ''}</div>
                            <div>${item.Grade || ''}</div>
                            <div>${item.PackingType || ''}</div>
                            <div>${item.Group || ''}</div>
                            <div>${item.Mcs || ''}</div>
                            <div>${item.LooseBags || ''}</div>
                            <div>${item.Kgs || ''}</div>
                            <div>${item.remarks || ''}</div>
                        </div>`;
            });

            html += '</div>';
            return html;
        }


        function generateBreakupTable2(breakups) {
            if (!Array.isArray(breakups) || breakups.length === 0) {
                return '<table><tr>' + '<td></td>'.repeat(13) + '</tr></table>';
            }

            let html = '<table>';
            html += `<tr>
                    <th>Production Date</th>
                    <th>Transaction Date</th>
                    <th>Production Plant</th>
                    <th>PoNo</th>
                    <th>Packing Style</th>
                    <th>Product</th>
                    <th>Grade</th>
                    <th>Packing Type</th>
                    <th>Group</th>
                    <th>Mcs</th>
                    <th>Loose Bags</th>
                    <th>KGs</th>
                    <th>Remarks</th>
                </tr>`;

            breakups.forEach(item => {
                html += `<tr>
            <td>${item.ProductionDate || ''}</td>
            <td>${item.TransactionDate || ''}</td>
            <td>${item.ProductionPlantName || ''}</td>
            <td>${item.PoNo || ''}</td>
            <td>${item.PackingStyle || ''}</td>
            <td>${item.ProductDescription || ''}</td>
            <td>${item.Grade || ''}</td>
            <td>${item.PackingType || ''}</td>
            <td>${item.Group || ''}</td>
            <td>${item.Mcs || ''}</td>
            <td>${item.LooseBags || ''}</td>
            <td>${item.Kgs || ''}</td>
            <td>${item.remarks || ''}</td>
        </tr>`;
            });

            html += '</table>';
            return html;
        }

        function loadAdvances1() {
            $('#ATT_DATA').show();
            var fromDate = $('#txtFromDate').val();
            var toDate = $('#txtToDate').val();

            if ($.fn.DataTable.isDataTable('#mytable-data')) {
                $('#mytable-data').DataTable().destroy();
            }

            table = "";
            table = $('#mytable-data').DataTable({
                serverSide: false,
                ajax: {
                    url: 'Ajax/Aj-return-report-list',
                    type: 'POST',
                    data: function (d) {
                        d.fromDate = fromDate;
                        d.toDate = toDate;
                        d.fromPage = "Listpage";
                        d.PageNo = (d.start / d.length) + 1;
                        d.productionPlant = $("#drpPlant").val();
                        d.species = $("#drpSpecies").val();
                        // d.Soaking = $("#drpSoaking").val();
                        d.Variety = $("#drpVariety").val();
                        d.Grade = $("#drpGrade").val();
                        d.PackingStyle = $("#drpPckgStyle").val();
                        // d.ProductCategory = $("#drpPDCategory").val();
                    }
                },
                columns: [
                    { data: 'Sno' },
                    { data: 'Type' },
                    { data: 'Ponumber' },
                    { data: 'PoDate' },
                    { data: 'ReturnContainerDate' },
                    { data: 'Supc' },
                    { data: 'Product' },
                    { data: 'Grade' },
                    { data: 'PackingType' },
                    { data: 'PackingStyle' },
                    { data: 'Mcs' },
                    { data: 'LooseBags' },
                    { data: 'Kgss' },
                    { data: 'cProductionDate' },
                    { data: 'cTransationDate' },
                    { data: 'cPlant' },
                    { data: 'cPono' },
                    { data: 'cPackingStyle' },
                    { data: 'cProduct' },
                    { data: 'cGrade' },
                    { data: 'cPackingType' },
                    { data: 'cGroup' },
                    { data: 'cMcs' },
                    { data: 'cLooseBags' },
                    { data: 'cKgss' },
                    { data: 'cRemarks' }
                ],
                dom: 'Bfrtip', // enables buttons
                buttons: [
                    {
                        extend: 'excelHtml5',
                        title: 'Return Container Report',
                        text: 'Download Excel',
                        className: 'btn btn-success'
                    }
                ],
                columnDefs: [
                    /*{ targets: [2], orderable: false, searchable: false },
                    { targets: [3], orderable: false, searchable: false },
                    { targets: [4], orderable: false, searchable: false },                */

                ],
                paging: false,
                //pageLength: 100,
                //lengthMenu: [[10,50,100], [10,50,100]],
                scrollX: true,
                scrollY: '500px',
                scrollCollapse: true,
                autoWidth: false,
                responsive: false,
                searching: true,
                ordering: false,

            });
        }
    </Script>

</body>

</html>