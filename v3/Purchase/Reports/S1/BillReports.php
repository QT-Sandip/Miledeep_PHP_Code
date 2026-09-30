<style>
    /* Remove all borders from tfoot */
    table.dataTable tfoot {
        border: none !important;
        /* Remove the entire tfoot border */
    }

    table.dataTable tfoot td {
        border: none !important;
        /* Remove the border for all td elements in the tfoot */
    }

    table.dataTable tfoot tr:hover {
        background: none !important;
    }
</style>
<?php
$PageTitle = "Final Reports" . $row_inv['PO_NUM'];
$PageTitleFolder = "Purchase";


include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$LotID = $_GET['LotID'];
$AccessType = $_SESSION["AccessType"];


/* $SqlOrgnDetails = "SELECT *  FROM `QcRmOrgnDetails`  where LotID='$LotID'";
$StmtOrgnDetails = $iconn->query($SqlOrgnDetails);

$NumRows = $StmtOrgnDetails->num_rows;
$RowOrgnDetails = $StmtOrgnDetails->fetch_array();
$orgn_status = $RowOrgnDetails['PostedUserID']; */
?>
<!DOCTYPE html>

<html lang="en">


<head>

    <?php include($IncludesPath . "title-meta.php"); ?>
    <?php include($IncludesPath . "DbLinks.php"); ?>
</head>
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
                    <div class=" row">
                    <div class="col-12">
                        <div class="card ">
                            <div class="card-body " id="ClientList">

                                <!-- <form id="pdf-form" method="post"> -->
                                <table id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100">

                                    <thead class="thead-light">

                                        <tr>


                                            <th> Available Reports </th>
                                            <th>
                                                <!-- <input type="checkbox" name="selected_all_rows" id="selected_all_rows" />  -->
                                                Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        
                                        <!-- ROBO -->
                                        <?php if ($AccessType < 3) {

                                            ?>
                                            <tr>
                                                <td>1. Costing Report</td>
                                                <td>
                                                   <!--  <input type="checkbox" name="selected_rows[]" class="report-checkbox"
                                                        value="/Reports/S1/CostingReportPrint.php?LotID=<?php echo $LotID ?>" /> -->
                                                    <a target="_blank"
                                                        href="<?php echo ""; // $_SESSION["ClientPath"] 
                                                            ?>CostingReportPrint.php?LotID=<?php echo $LotID ?>"
                                                        class="text-success" data-toggle="tooltip" data-placement="top"
                                                        title="" data-original-title="Print"><i
                                                            class="mdi mdi-printer font-size-18"></i></a>

                                                </td>
                                            </tr>

                                        <?php } ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="2" style="text-align: right;">

                                                <input type="hidden" id="pdf-contents" name="pdfContents" />
                                                <button id="print_button" type="button"
                                                    class="btn btn-primary waves-effect waves-light ">
                                                    Print <i class="mdi mdi-printer  ml-2"></i>
                                                </button>

                                                <button id="submit_button" type="button"
                                                    class="btn btn-success waves-effect waves-light">
                                                    Download <i class="ri-arrow-right-line align-middle ml-2"></i>
                                                </button>
                                            </td>
                                        </tr>

                                    </tfoot>

                                </table>
                                <!-- </form> -->

                            </div>

                            <!-- end col -->
                        </div> <!-- end row -->

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
    <?php include($IncludesPath . "NewScripts.php"); ?>



</body>

</html>

<script>
    $(document).ready(function () {

        // showData();
        downloadReports();
        checkBoxChangeHandler();
        printHandler()
        // SelectAll();
        $('#submit_button').hide();
        $('#print_button').hide();
    })

    function showData(data) {

        // $("#ClientList").html(data);

        var table = $('#key-datatable').DataTable({
            scrollY: "210px",
            scrollX: true,
            //sScrollX: "100%",
            sScrollXInner: "150%",
            // scrollCollapse:true,
            paging: true,
            destroy: true,
            fixedColumns: true,
            bSort: true,
            "bStateSave": true,
            "fnStateSave": function (oSettings, oData) {
                localStorage.setItem('key-datatable', JSON.stringify(oData));
            },
            "fnStateLoad": function (oSettings) {
                return JSON.parse(localStorage.getItem('key-datatable'));
            },

        });
    }

    function downloadReports() {
        $(document).off('click', '#submit_button');
        $(document).on('click', '#submit_button', function () {
            console.log("download click")
            var selectedOptions = [];
            $('input[name="selected_rows[]"]:checked').each(function () {
                selectedOptions.push($(this).val() + "&type=download");
            });
            if (selectedOptions?.length > 0) {
                selectedOptions.forEach(function (phpFile) {
                    window.open(phpFile, '_blank');
                });
            }
        });
    }

    function checkBoxChangeHandler() {
        $(document).off('click', '.report-checkbox');
        $(document).on('click', '.report-checkbox', function () {
            if ($('input[name="selected_rows[]"]:checked').length > 0) {
                $('#submit_button').show();
                $('#print_button').show();
            } else {
                $('#submit_button').hide();
                $('#print_button').hide();
            }
        });
    }

    function printHandler() {
        $(document).off('click', '#print_button');
        $(document).on('click', '#print_button', function () {
            console.log("print click")
            var selectedOptions = [];
            $('input[name="selected_rows[]"]:checked').each(function () {
                selectedOptions.push($(this).val());
            });
            if (selectedOptions?.length > 0) {
                const fileParams = selectedOptions.reduce((acc, item) => {
                    if (acc.length > 0) {
                        acc += ",";
                    }
                    return acc += item?.split("/")[1]?.split('.')[0];
                }, "");
                const lotId = selectedOptions[0]?.split("?")[1]?.split("=")[1].split("&")[0]
                console.log(fileParams, lotId)
                window.open('Reports/BulkReport.php?fileParams=' + fileParams + "&lotId=" + lotId, '_blank');

            }
        });
    }

    // function SelectAll() {
    //     $(document).off('click', '#selected_all_rows');
    //     $(document).on('click', '#selected_all_rows', function() {
    //         $('input[name="selected_rows[]"]').each(function() {
    //             $(this).prop('checked', !$(this).prop('checked'));
    //         })
    //     });
    // }
</script>
<?php

?>