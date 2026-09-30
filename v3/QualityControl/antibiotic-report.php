<?php
session_start();
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
// $filterFromDate = "T";
// $filterToDate = "T";
// $filterReceivingPlant = "T";
// $filterButButton = "T";
$filterfromDates = "T";
$filterToDates = "T";
$filterYear = "T";
$filterLotId = "T";
$filterButtomButton = "T";
$ProductionPlant = $_SESSION['UnitID'];

?>
<!doctype html>
<html lang="en">

<head>
    <?php include_once ROOT . '/includes/head.php' ?>
    <?php include_once ROOT . 'includes/body-preloader.php' ?>
    <style>
        .highlight {
            border: 2px solid red;
        }
    </style>
</head>
<?php //include_once ROOT . '/includes/body-preloader.php' 
?>
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
                <?php include_once WORKING_MODULE . '/includes/page-filter.php'; ?>
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
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"
    type="text/javascript"></script>
<script>
    $(document).ready(function() {
        //    $("#drpYear").val("").change();

        $("#drpYear").select2({
            placeholder: "Select Year",
            width: "100%",
            minimumResultsForSearch: Infinity
        });

        $("#drpYear").val(null).trigger('change');



        $("#drpLotId").select2({
            placeholder: "Select Lot No",
            width: "100%",
            closeOnSelect: false
        });

        $('#drpLotId').on('click', function(e) {
            $(this).select2('open');
        });

        var productionPlant = <?php echo $ProductionPlant ?>;
        if (productionPlant != 0) {
            $("#drpReceivingPlantId").prop('disabled', true);
            $("#drpReceivingPlantId").val(productionPlant).trigger('change');
        } else {
            $("#drpReceivingPlantId").prop('disabled', false);
            $("#drpReceivingPlantId").val("").trigger('change');
        }
    });

    function fnPoStatusReportExcel(tableID, filename = '') {
        var fromDate = $("#txtFromDate").val();
        var toDate = $("#txtToDate").val();
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
        var trs = clonedTable.getElementsByTagName('tr');
        for (var i = 0; i < trs.length; i++) {
            trs[i].style.border = '1px solid #EFF2F7';
            var tds = trs[i].getElementsByTagName('td');
            for (var j = 0; j < tds.length; j++) {
                if (/^\d+\/\d+$/.test(tds[j].innerText)) {
                    tds[j].innerText = "'" + tds[j].innerText;
                    // tds[j].innerHTML = '<span style="mso-number-format:@">' + tds[j].innerText + '</span>';
                }
                tds[j].style.border = '1px solid #EFF2F7';
            }
        }
        var hiddenCells = clonedTable.querySelectorAll('td[style*="display: none"]');
        hiddenCells.forEach(function(cell) {
            cell.style.visibility = 'hidden';
        });
        var tableHTML = clonedTable.outerHTML;
        filename = filename ? filename + '_FromDate: ' + fromDate + '_ToDate: ' + toDate + '.xls' : 'excel_data.xls';
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
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable btn-left-align',
                title: 'Antibiotic Report <?php echo date("d-m-Y") ?>',
                footer: true,
                exportOptions: {
                    columns: ':visible',
                },
                action: function() {
                    fnPoStatusReportExcel('tblDataTable', 'Antibiotic Report');
                }
            }],
        });
         $( "<button class='btn btn-primary' id='btnMoreSearch' style='padding: 3px 5px;margin-left: 10px;'>More Search</button>"+
            "<a class='btn btn-primary' id='LoadAll' href='javascript:void(0);' style='padding: 3px 5px;margin-left: 10px;'>Load All</a>"
        ).insertAfter("#tblDataTable_filter label");
    }

    function ShowList(pageNo = '', hdnTotalRecords = '', loadAll = '', searchkey = '') {
        var selectedLotIds = $("#drpLotId").val();
        var lotIdsString = selectedLotIds.join(",");
        $.ajax({
            url: 'Ajax/ajax-antibiotic-report.php',
            type: 'POST',
            data: {
                fromDate: $("#txtFromDate").val(),
                toDate: $("#txtToDate").val(),
                methodType: "GET",
                lotIds: lotIdsString,
                pageNo:pageNo,
                loadAll:loadAll,
                hdnTotalRecords: hdnTotalRecords,
                SearchKey: searchkey
            },
            success: function(data) {
                $("#divDataList").html(data);
                fnShowDataTable();
                $("#txtFromDate").attr("disabled", false);
                $("#txtToDate").attr("disabled", false);
                //  $("#drpYear").val("").change();
                return false;
            }
        });
    }
    
// $('#drpYear').on('change', function() {
//     var selectedYear = $(this).val();

//     if (selectedYear) {
//         $("#txtFromDate").val(selectedYear + "-04-01");
//         $("#txtToDate").val((parseInt(selectedYear) + 1) + "-03-31");
//         $("#txtFromDate, #txtToDate").attr("disabled", true);

//         $.ajax({
//             url: 'Ajax/ajax-antibiotic-report.php',
//             type: 'POST',
//             dataType: 'json',
//             data: {
//                 fromDate: $("#txtFromDate").val(),
//                 toDate: $("#txtToDate").val(),
//                 methodType: "GET_BATCH",
//             },
//             success: function(data) {
//                 var options = "<option value=''>Select Lot No</option>";
//                 if (data.AntibioticLotsData && data.AntibioticLotsData.length) {
//                     data.AntibioticLotsData.forEach(element => {
//                         options += `<option value="${element.RmLotID}">${element.LotNumber}</option>`;
//                     });
//                 }

//                 // Update options without destroying Select2
//                 $('#drpLotId').html(options).val(null).trigger('change.select2');

//                 // Optional: automatically open Lot dropdown after loading
//                 $('#drpLotId').select2('open');
//             }
//         });

//     } else {
//         $("#txtFromDate, #txtToDate").attr("disabled", false).val("");
//         $('#drpLotId').html("<option value=''>Select Lot No</option>").val(null).trigger('change.select2');
//     }
// });
   $('#drpYear').on('change', function() {
        var selectedYear = $(this).val();
        if (selectedYear) {
            $("#txtFromDate").val(selectedYear + "-04-01");
            $("#txtToDate").val((parseInt(selectedYear) + 1) + "-03-31");
            $("#txtFromDate").attr("disabled", true);
            $("#txtToDate").attr("disabled", true);
            $.ajax({
                url: 'Ajax/ajax-antibiotic-report.php',
                type: 'POST',
                data: {
                    fromDate: $("#txtFromDate").val(),
                    toDate: $("#txtToDate").val(),
                    methodType: "GET_BATCH",
                },
                success: function(data) {
                    console.log(data)
                    $('#drpLotId').select2('destroy');
                    var List = "<option value=''>Select Lot No</option>";
                    data.AntibioticLotsData.forEach(element => {
                        //List += `<option value=${element.RmLotID}>${element.LotNumber}</option>`
                        List += `<option value=${element.LotNumber}>${element.LotNumber}</option>`
                    });
                    $('#drpLotId').html(List).select2();;
                }
            });
        } else {
            $('#drpLotId').select2('destroy');
            $("#txtFromDate").val(Date.now().toString().substring(0, 10));
            $("#txtToDate").val(Date.now().toString().substring(0, 10));
            $("#txtFromDate").attr("disabled", false);
            $("#txtToDate").attr("disabled", false);
        }
    });



    $("#btnSearch").on("click", function() {
        $("#txtFromDate, #txtToDate").removeClass("highlight");
        var fromDate = $("#txtFromDate").val();
        var toDate = $("#txtToDate").val();
        if (fromDate == "" || toDate == "") {
            if (fromDate == "") {
                $("#txtFromDate").addClass("highlight");
            }
            if (toDate == "") {
                $("#txtToDate").addClass("highlight");
            }
            return false;
        }
        // var fromDate = $("#txtFromDate").val();
        // var toDate = $("#txtToDate").val();
        // var receivingPlantId = $("#drpReceivingPlantId").val();
        // if (!fromDate && !toDate) {
        //     alert('Select From and To Dates');
        // } else if (!receivingPlantId) {
        //     alert('Select Plant');
        // } else {
        ShowList();
        // }
    });
    $(document).on('click', '.btnPageno', function(e) {
        var pageNo = $(this).data('pageno');
        var hdnTotalRecords = $('#hdnTotalRecords').val();
        ShowList(pageNo, hdnTotalRecords)
    })
    $(document).on('click', '#LoadAll', function(e) {
        ShowList('','',1, "")
    })
     $(document).on('click', '#btnMoreSearch', function(e) {
        var searchkey = $("#tblDataTable_filter input").val();
            if (searchkey.length > 0) {
                ShowList("1", "", "0",searchkey);
            }
    })
    
</script>
</body>

</html>