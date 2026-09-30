<?php
session_start();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$filterfromDate = "T";
$filtertoDate = "T";
$filterSpecies = "T";
$filterPlant = "T";
$filterSideButton = "T";
$filterTypeProduct = "T";
// $filterMonth = "T";
// $filterYear = "T";
$filterFreezingType = "T";
//$filterDayMonthSelection = "T";
$filterColdStores = "T";
//$filterTypeSlabsNMcs = "T";

$filterBrand = "T";
$filterVarietyList = "T";
$filterGrade = "T";
$filterNewGroup = "T";
$filterPckgStyle = "T";
$filterPackingType = "T";


$ProductionPlant = $_SESSION['UnitID'];

// echo $ProductionPlant;

?>

<!doctype html>
<html lang="en">

<head>
    <?php include_once ROOT . '/includes/head.php' ?>

    <style>
        #tblDataTable {
            width: 100%;
            overflow-x: auto;
        }

        #tblDataTable thead th {
            position: sticky;
            top: 0;
            background-color: #f8f9fa;
            z-index: 2;
        }

        #tblDataTable th:nth-child(-n+6),
        #tblDataTable td:nth-child(-n+6) {
            position: sticky;
            left: 0;
            background-color: #f8f9fa;
            z-index: 1;
        }

        #tblDataTable th:nth-child(1),
        #tblDataTable td:nth-child(1) {
            left: 0;
        }

        #tblDataTable th:nth-child(2),
        #tblDataTable td:nth-child(2) {
            left: 50px;
        }

        #tblDataTable th:nth-child(3),
        #tblDataTable td:nth-child(3) {
            left: 150px;
        }

        #tblDataTable th:nth-child(4),
        #tblDataTable td:nth-child(4) {
            left: 250px;
        }

        #tblDataTable th:nth-child(5),
        #tblDataTable td:nth-child(5) {
            left: 350px;
        }

        #tblDataTable th:nth-child(6),
        #tblDataTable td:nth-child(6) {
            left: 450px;
        }

        #tblDataTable th:nth-child(7),
        #tblDataTable td:nth-child(7) {
            position: relative;
            left: auto;
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
                <?php include_once WORKING_MODULE . '/includes/page-filter.php' ?>


                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <div class="card">

                            <div class="card-body">

                                <div id="divDataList">

                                </div>
                                <input type="hidden" id="hdnActionType" />
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
    $(document).ready(function() {

        var productionPlant = <?php echo $ProductionPlant ?>;
        if (productionPlant != 0) {
            $("#drpPlant").prop('disabled', true);
            $("#drpPlant").val(productionPlant).trigger('change');
        } else {
            $("#drpPlant").prop('disabled', false);
            $("#drpPlant").val("").trigger('change');
        }

        dayMonthType();

        $('#drpDayMonthSelection').change(function() {
            dayMonthType();
        });
        

    });

    function dayMonthType() {
        var type = $("#drpDayMonthSelection").val();
        if (type == "DayWise") {
            $("#txtFromDate").prop('disabled', false);
            $("#txtToDate").prop('disabled', false);
            $("#drpmonth").prop('disabled', true);
            $("#drpYear").prop('disabled', true);
        } else if (type == "MonthWise") {
            $("#txtFromDate").prop('disabled', true);
            $("#txtToDate").prop('disabled', true);
            $("#drpmonth").prop('disabled', false);
            $("#drpYear").prop('disabled', false);
        }
    }

    function fnPoStatusReportExcel(tableID, filename = '') {
        var plant = $("#drpPlant option:selected").text();
        var fromDate = $("#txtFromDate").val().split('-').reverse().join('/');
        var toDate = $("#txtToDate").val().split('-').reverse().join('/');
        

        $("#tblDataTable").find(".thead-light  tr").height("auto");
        $("#tblDataTable").find(".thead-light  tr th").height("auto");
        $("#tblDataTable").find(".thead-light  tr th div").height("auto");

        $("#tblDataTable").find("tfoot tr").height("auto");
        $("#tblDataTable").find("tfoot tr td").height("auto");
        $("#tblDataTable").find("tfoot tr th div").height("auto").css("font-weight", "bold");

        var downloadLink;
        var dataType = 'data:application/vnd.ms-excel';
        var tableSelect = document.getElementById(tableID);

        var clonedTable = tableSelect.cloneNode(true);

        var reportNameRow = document.createElement('tr');
        reportNameRow.innerHTML = `
        <td colspan="${clonedTable.querySelectorAll('tr:first-child th').length}" style="text-align: center; font-weight: bold;">
            Daily_Production Report Plant:${plant} 
        </td>`;
        clonedTable.querySelector('thead').insertBefore(reportNameRow, clonedTable.querySelector('thead').firstChild);

        var trs = clonedTable.getElementsByTagName('tr');
        for (var i = 0; i < trs.length; i++) {
            trs[i].style.border = '1px solid #eff2f7';
            var tds = trs[i].getElementsByTagName('td');
            for (var j = 0; j < tds.length; j++) {
                tds[j].style.border = '1px solid #eff2f7';
            }
        }

        var hiddenCells = clonedTable.querySelectorAll('td[style*="display: none"]');
        hiddenCells.forEach(function(cell) {
            cell.style.visibility = 'hidden';
        });

        var tableHTML = clonedTable.outerHTML;

        filename = filename ? filename  + '.xls' : 'Daily Production Report.xls';

        downloadLink = document.createElement('a');
        document.body.appendChild(downloadLink);

        if (navigator.msSaveOrOpenBlob) {
            var blob = new Blob(['\ufeff', tableHTML], {
                type: dataType
            });
            navigator.msSaveOrOpenBlob(blob, filename);
        } else {
            downloadLink.href = dataType + ', ' + encodeURIComponent(tableHTML);
            downloadLink.download = filename;
            downloadLink.click();
        }

        $("#tblDataTable").find(".thead-light  tr").height("0px");
        $("#tblDataTable").find(".thead-light  tr th").height("0px");
        $("#tblDataTable").find(".thead-light  tr th div").height("0px");

        $("#tblDataTable").find("tfoot tr").height("0px");
        $("#tblDataTable").find("tfoot tr td").height("0px");
        $("#tblDataTable").find("tfoot tr th div").height("0px");
    }


    function fnShowDataTable() {
        var table = $('#tblDataTable').DataTable({
            scrollY: "600px",
            scrollX: true,
            sScrollXInner: "100%",
            paging: false,
            destroy: true,
            scrollCollapse: true,
            bInfo: false,
            bSort: false,
            dom: 'Blfrtip',
            buttons: [{
                    extend: 'excelHtml5',
                    text: 'Download Excel',
                    className: 'btnExcelDataTable btn-left-align',
                    //title: 'Daily Production Report <?php //echo date("d-m-Y") ?>',
                    title: 'Daily Production Report - Plant: '+$.trim($("#drpPlant option:selected").text()),
                    footer: true,
                    // messageTop: function () {
                    //     return 'Daily Production Report - Plant: ' + 
                    //         $("#drpPlant option:selected").text();
                    // },
                    exportOptions: {
                        columns: ':visible'
                    },
                    customize: function (xlsx) {

                        var sheet = xlsx.xl.worksheets['sheet1.xml'];
                        

                        // 🔥 Merge footer columns (example: E & F columns)
                        var lastRow = $('sheetData row', sheet).last().attr('r');

                        var mergeCells = sheet.getElementsByTagName('mergeCells')[0];

                        if (!mergeCells) {
                            mergeCells = sheet.createElement('mergeCells');
                            sheet.getElementsByTagName('worksheet')[0].appendChild(mergeCells);
                        }

                        var mergeCell = sheet.createElement('mergeCell');
                        mergeCell.setAttribute('ref', 'E' + lastRow + ':F' + lastRow);

                        mergeCells.appendChild(mergeCell);
                        mergeCells.setAttribute('count', mergeCells.childNodes.length);
                    }
                }]

        });
        table.on('draw.dt', function () {
            let noOfSlabs = 0;
            let noOfKgs = 0;
            $('.Slabs').each(function () {
                // Parse the text content as a float, ignoring any commas
                const Slabs = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(Slabs)) {
                  noOfSlabs += Slabs;
                }
            });
            $('.Kgs').each(function () {
                // Parse the text content as a float, ignoring any commas
                const weight = parseFloat($(this).text().replace(/,/g, ''));
                if (!isNaN(weight)) {
                  noOfKgs += weight;
                }
            });
           
            $('#totalSlabs').text(noOfSlabs.toLocaleString('en-US', {
                //minimumFractionDigits: 2,
                //maximumFractionDigits: 2
            }));
            $('#totalKgs').text(noOfKgs.toLocaleString('en-US', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            }));
        });
        return table;
        
    }

    function ShowList() {
        var Variety = '';
        if($('#drpVariety option:selected').text() != 'Select'){
            Variety = $('#drpVariety option:selected').text();
        }
        $.ajax({
            url: 'Ajax/ajax-daily-production-report-new.php',
            type: 'POST',
            data: {
                fromDate: $("#txtFromDate").val(),
                toDate: $("#txtToDate").val(),
                species: $("#drpSpecies").val(),
                productionPlant: $("#drpPlant").val(),
                TypeProduct: $("#drpTypeProduct").val(),
                freezingType: $("#drpFreezingType").val(),
                month: $("#drpmonth").val(),
                year: $("#drpYear").val(),
                dayMonthSelection: $("#drpDayMonthSelection").val(),
                coldStore: $("#drpColdStore").val(),
                slabType: $("#drpType").val(),

                Brand : $("#drpBrand").val(),
                Variety : Variety,
                Grade : $("#drpGrade").val(),
                Group : $("#drpGroup").val(),
                PckgStyle : $("#drpPckgStyle").val(),
                PackingType : $("#drpPackingType").val(),

            },
            success: function(data) {
                $("#divDataList").html(data);
                fnShowDataTable();

                return false;
            }
        });
    }

    $("#btnSearch").on("click", function() {
        var fromDate = $("#txtFromDate").val();
        var toDate = $("#txtToDate").val();
        var month = $("#drpmonth").val();
        var year = $("#drpYear").val();
        if (fromDate != '' && toDate != '') {
            ShowList();
        } else if (month != '' && year != '') {
            ShowList();

        } else {
            alert('Select From Date and To Date (or) month and year');
        }
    })
</script>

</body>

</html>