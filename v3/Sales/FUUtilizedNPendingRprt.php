<?php
$PageTitle = "FU Utilized Pending Report";
$PageTitleFolder = "Sales";

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($_SERVER['DOCUMENT_ROOT'] . "/includes/Globals.php");

$uid = $_SESSION['r_id'];

$CurrentMonth = date('m');
//echo $endYear;exit;

if ($CurrentMonth > 3) {
    $FinClsYear = $Currentyear + 1;
} else {
    $FinClsYear = $Currentyear;
}
//echo $endYear;exit;

$SqlForwardContracts = "SELECT FCID,FCNumber FROM `ScrmForwardContractsDetails` WHERE Status=1";
$StmtForwardContracts = $iconn->query($SqlForwardContracts);


$SqlExporter = "SELECT  ExpID,ExpName FROM `AdminExporterDetails` where ExpType=1 ";
$StmtExporter = $iconn->query($SqlExporter);

$ClientConfigDataObj = (object) $_SESSION['ClientConfigData'];
$DefaultExporterID = $ClientConfigDataObj->DefaultExporterID;

?>

<!doctype html>
<html lang="en">

<link href="https://serp.silverlights.in/Clients/Jlsf/User.css" rel="stylesheet" type="text/css" />

<head>

    <?php include($IncludesPath . "title-meta.php"); ?>

    <!-- DataTables -->
    <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-select-bs4/css//select.bootstrap4.min.css" rel="stylesheet" type="text/css" />

    <!-- Responsive datatable examples -->
    <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />


    <link href="<?php echo $BaseDist ?>assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet">
    <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css" rel="stylesheet">
    <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css" rel="stylesheet" />
    <?php include($IncludesPath . "head-css.php"); ?>

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
                            <div class="card">
                                <div class="card-body">

                                    <div class="row">
                                        <div class="col-lg-3">
                                            <label>Company: </label>
                                            <select class="form-control select2" name="company" id="company">
                                                <option value="">All</option>
                                                <?php while ($row_add = $StmtExporter->fetch_array()) {
                                                    $selected = !empty($DefaultExporterID) && ($DefaultExporterID == $row_add['ExpID']) ? 'selected' : '' ; ?>
                                                    <option Value="<?php echo $row_add['ExpID']; ?>" <?php echo $selected;?>><?php echo $row_add['ExpName']; ?></option>
                                                <?php } ?>

                                                <select>
                                        </div>

                                        <div class="col-lg-3">
                                            <label>Forward Contract No: </label>
                                            <select class="form-control select2" name="forwardContractNo" id="forwardContractNo">
                                                <option value="">All</option>
                                                <?php while ($RowForwardContracts = $StmtForwardContracts->fetch_array()) { ?>
                                                    <option Value="<?php echo $RowForwardContracts['FCID']; ?>"><?php echo $RowForwardContracts['FCNumber']; ?></option>
                                                <?php } ?>
                                                <select>
                                        </div>

                                        <div class="col-lg-12 text-center mt-3">
                                            <button id="list" type="button" class="btn btn-primary waves-effect waves-light">
                                                Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                                            </button>
                                        </div>

                                    </div>


                                </div>
                                <!-- end row -->
                            </div>
                        </div>
                    </div>

                    <!-- end col -->
                </div> <!-- end row -->
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body" id="POList">
                                    <!--<h4 class="card-title">Proforma Invoice List</h4>-->


                                </div> <!-- end card body-->
                            </div> <!-- end card -->
                        </div><!-- end col-->
                    </div>
                </div>
                <!-- end row-->


            </div> <!-- end card body-->
        </div> <!-- end card -->
    </div><!-- end col-->
    </div>
    <!-- end row-->

    </div> <!-- container-fluid -->
    </div>
    <!-- End Page-content -->

    <?php include($IncludesPath . "footer.php"); ?>

    </div>
    <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->



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
    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

    <!-- Datatable init js -->
    <script src="<?php echo $BaseDist ?>assets/js/pages/datatables.init.js"></script>

    <script src="<?php echo $BaseDist ?>assets/js/app.js"></script>


    <script>
        $('#list').click(function() {

            var company = $('#company').val();
            var forwardContractNo = $('#forwardContractNo').val();


            // function exportTableToExcel(tableID, filename = '') {
            //     var downloadLink;
            //     var dataType = 'data:application/vnd.ms-excel';
            //     var tableSelect = document.getElementById(tableID);

            //     // Apply CSS styles for borders
            //     tableSelect.style.border = '1px solid #eff2f7';
            //     var trs = tableSelect.getElementsByTagName('tr');
            //     for (var i = 0; i < trs.length; i++) {
            //         trs[i].style.border = '1px solid #eff2f7';
            //         var tds = trs[i].getElementsByTagName('td');
            //         for (var j = 0; j < tds.length; j++) {
            //             // tds[j].style.border = '1px solid #eff2f7';
            //         }
            //     }

            //     var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');

            //     filename = filename ? filename + '.xls' : 'excel_data.xls';

            //     downloadLink = document.createElement('a');
            //     document.body.appendChild(downloadLink);

            //     if (navigator.msSaveOrOpenBlob) {
            //         var blob = new Blob(['\ufeff', tableHTML], {
            //             type: dataType
            //         });
            //         navigator.msSaveOrOpenBlob(blob, filename);
            //     } else {
            //         downloadLink.href = dataType + ', ' + tableHTML;
            //         downloadLink.download = filename;
            //         downloadLink.click();
            //     }
            // }

            $.ajax({
                //   url:'Ajax/AjaxFUUtilizedNPendngRprt',
                url: 'Ajax/AjaxFUUtilizedNPendngRprtDev',
                type: 'POST',
                // data:{'EXPORTER':EXPORTER,'YEAR':YEAR,'TYPE':'POLIST'},
                data: {
                    'Exporter': company,
                    'ForwardContractNo': forwardContractNo
                },
                success: function(data) {
                    //showData(data);
                    var columns = [{
                        title: ''
                    }];
                    $("#POList").html(data);


                    // var buttons = new $.fn.dataTable.Buttons(table, {
                    //     buttons: [
                    //         // {extend: 'excelHtml5', text: 'Download Excel', className: 'btnExcelDataTable', 	title: 'Pending Contracts Report', footer: true,
                    //         // exportOptions: {
                    //         //     columns: ':visible'
                    //         // }},
                    //         {
                    //             text: 'Download Excel',
                    //             className: 'btnExcelDataTable',
                    //             exportOptions: {
                    //                 columns: ':visible'
                    //             },
                    //             action: function () {
                    //                 // fnPendingOrderPlantWiseExcel();
                    //                 // fnPendingOrderPlantWiseExcel("", "POLIST");
                    //                 var excel_data = $('#key-datatable').closest("div.col-sm-12").html();
                    //                 exportTableToExcel('key-datatable', 'Orders');
                    //             }
                    //         }
                    //     ]
                    // }).container().appendTo($('#key-datatable_filter'));

                    // $('#key-datatable_wrapper > .row > .col-sm-12.col-md-6:first-child')
                    //     .html('<h4 class="modal-title">Orders</h4>');
                    // // $('#myModalFullScreen').modal('show');

                    // $('<div class="dt-buttons btn-group flex-wrap"><button class="btn btn-secondary buttons-excel btnExcelDataTable" type="button"><span>Download Excel</span></button> </div>')
                    //     .insertAfter("#key-datatable_filter lable");


                    var table = $('#key-datatable').DataTable({
                        scrollY: "600px",
                        scrollX: true,
                        //sScrollX: "100%",
                        sScrollXInner: "150%",
                        scrollCollapse: true,
                        paging: true,
                        destroy: true,
                        fixedColumns: true,
                        bSort: true,
                        order: [
                            [1, 'desc']
                        ],
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
            buttons:[],
                        "bStateSave": true,
                        // dom: 'Blfrtip',
                        // buttons: [{
                        //     extend: 'excel', text: 'Download Excel', className: 'btnExcelDataTable',title: 'FC_Utilized_Report', footer: true,
                        //     customize: function (xlsx) {
                        //         // var sheet = xlsx.xl.worksheets['sheet1.xml'];

                        //         // // Column - 2
                        //         // $('col', sheet).eq(2).attr('width', 20);
                        //         // $('row c', sheet).attr( 's', '55' );

                        //         // // Column - 4
                        //         // $('col', sheet).eq(4).attr('width', 13);
                        //         // $('row e', sheet).attr( 's', '55' );

                        //         // // Column - 5
                        //         // $('col', sheet).eq(5).attr('width', 13);
                        //         // $('row f', sheet).attr( 's', '55' );

                        //     },
                        //     exportOptions: {
                        //         columns: ':visible',
                        //         // format: {
                        //         //     body: function (data, row, column, node) {

                        //         //         const dateColumns = [2];

                        //         //         if (dateColumns.includes(column)) {
                        //         //             return .split(', ').join('\n');
                        //         //         } else {
                        //         //             return data;
                        //         //         }

                        //         //     }
                        //         // }
                        //     },
                        //     // action :function () {
                        //     //     // fnPendingOrderPlantWiseExcel();
                        //     //     // fnPendingOrderPlantWiseExcel("", "POLIST");
                        //     //     var excel_data = $('#key-datatable').closest(".card-body").html();
                        //     //     exportTableToExcel('key-datatable', 'FC_Utilized_Report');
                        //     // }
                        // }],
                        "fnStateSave": function(oSettings, oData) {
                            localStorage.setItem('key-datatable', JSON.stringify(oData));
                        },
                        "fnStateLoad": function(oSettings) {
                            return JSON.parse(localStorage.getItem('key-datatable'));
                        }
                    });

                    var downloadLink = '<button class="btn btn-secondary btnExcelDataTable" tabindex="0" aria-controls="key-datatableFullscreen" type="button" style="margin-left: 15px;"><span>Download Excel</span></button>';
                    $(document).find(".dataTables_filter input.form-control.form-control-sm").after(downloadLink);
                }
            });

        });
    </script>
    <script>
        $(document).on("click", ".btnExcelDataTable", function() {
            var company = $('#company').val();
            var forwardContractNo = $('#forwardContractNo').val();

            var url = 'Reports/DocExcelFUUtilizedPendingReport.php?company=' + company + '&forwardContractNo=' + forwardContractNo;
            window.open(url, '_blank');
        });
$("#forwardContractNo").on("change", function () {
    $("#POList").html("");
});
    </script>


</body>

</html>
