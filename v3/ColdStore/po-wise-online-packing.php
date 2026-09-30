<?php include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

$filterSpecies = "T"; 
$filterConsignee  = "T";
$filterPlant = "T";
$filterVariety = "T";
$filterTypeProduct = "T";
$filterPoNumber = "T";
$filterDestination = "T";
$filterFreezingType = "T";
$filterGrade = "T";
$filterBrand = "T";
$buttomButton = "T";

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
                <?php include_once WORKING_MODULE . 'includes/page-filter.php'; ?>
                <!-- <?php // include_once WORKING_MODULE . '/includes/page-filter.php'; ?> -->

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

<link href="https://cdn.datatables.net/fixedcolumns/4.3.0/css/fixedColumns.dataTables.css" rel="stylesheet">
<script src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.js"></script>

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
            // fixedColumns: true,
            scrollCollapse: true,
            bInfo: false,
            bSort: false,
            dom: 'Blfrtip',
            // dom: '<"top-left"Blfrtip>',
            buttons: [{
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable btn-left-align',
                title: 'Rack Wise Stock Report <?php echo date("d-m-Y") ?>',
                footer: true,
                exportOptions: {
                    columns: ':visible'
                }
            }],
        });
    }

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


        // Scroll the container horizontally to the position of the target class
        container.scrollLeft(position);
    }

    function ShowList(pageNo, searchkey, loadAll) {
        $.ajax({
            url: 'Ajax/ajax-po-wise-online-packing.php',
            type: 'POST',
            data: {
                // specie,Buyer,plant,variety,brand,productform,destination,freezingtype,grade
                specie: $.trim($("#drpSpecies").val()),
                buyer: $.trim($("#drpConsignee").val()),
                variety: $.trim($("#drpVariety").val()),
                plant: $.trim($("#drpPlant").val()),
                grade: $.trim($("#drpGrade").val()),
                ordernumber : $.trim($("#drpPoNumber").val()),
                productform: $.trim($("#drpTypeProduct").val()),
                destination: $.trim($("#drpDestination").val()),
                freezingtype: $.trim($("#drpFreezingType").val()),
                brand: $.trim($("#drpBrand").val()),
                pageNo: pageNo,
                searchkey: searchkey,
                loadAll: loadAll,
            },
            success: function(data) {
                fnSuccess(data);
                // $("#divDataList").html(data);
                // fnShowDataTable();
                return false;
            }
        });
    }

    $(document).ready(function() {
        ShowList("1", "", "0");
    });

    $("#btnSearch").on("click", function() {
        ShowList("1", "", "0");
    });

    
</script>

</body>

</html>