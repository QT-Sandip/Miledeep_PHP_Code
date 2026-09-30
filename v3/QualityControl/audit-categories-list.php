    
<?php
session_start();
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$apiList = API_AUDIT_CATEGORIES;

$AuditID = $_GET["AuditID"];

if ($AuditID != '') {
    $result = CallAPI($apiList, "GET", "", $bodyContent);
    $Data = $result->Data[0];
   
}
$bodyContent = array(
    "AuditID" => $AuditID
);

$api = $api = API_QC_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", $bodyContent);
$resProductionPlant = $resDropdownsAPI->Data->Plant;
$ProductionPlant = $_SESSION['UnitID'];
// echo $productionPlant;
// exit;
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
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <div class="card">
                            <div class="card-body">

                            <!-- <div class="form-group col-sm-3 mb-3">
                                <label for="drpArrivalPlant" class="form-label">Production Plant<span style="color:red">*</span></label>
                                    <select class="drpSelect form-control" name="drpArrivalPlant" id="drpArrivalPlant" disabled>
                                    <option value=""> Select</option>
                                        <?php foreach ($resProductionPlant as $Item) { ?>
                                    <option value="<?php echo $Item->ProcessingStationID; ?>">
                                        <?php echo $Item->FullName . ' - ' . $Item->StationName; ?></option>
                                    <?php } ?>
                                 </select>
                            </div> -->

                                <div class="text-center">
                                    <a href="audit-categories-create" class="btn btn-success waves-effect waves-light">
                                        
                                        

                                        <i class="fa fa-plus"></i> Create Audit Categories
                                    </a>
                                </div>
                                <br>
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
        $("#drpArrivalPlant").prop('disabled', true);
        $("#drpArrivalPlant").val(productionPlant).trigger('change');

    } else {
        $("#drpArrivalPlant").prop('disabled', false);
        $("#drpArrivalPlant").val("").trigger('change');
    }
    });

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
            buttons: [{
                extend: 'excel',
                text: 'Download Excel',
                title: 'Audit Categories List - <?php echo date("d-m-Y") ?>',
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
    }

    function ShowList(pageNo, searchkey, loadAll) {
        $.ajax({
            url: 'Ajax/ajax-audit-categories-list.php',
            type: 'POST',
            data: {
                pageNo: pageNo,
                searchkey: searchkey,
                loadAll: loadAll,
                //arrivalPlant: $("#drpArrivalPlant").val(),
            },
            success: function(data) {
                fnSuccess(data);
                return false;
            }
        });
    }

    $(document).ready(function() {
        ShowList("1", "", "0");
    });

    $(document).on("click", "#btnMoreSearch", function() {
        var searchkey = $("#tblDataTable_filter input").val();
        if (searchkey.length > 0) {
            ShowList("1", searchkey, "0");
        }
    });

    $(document).on("click", "#btnLoadAll", function() {
        ShowList("1", "", "1");
    });

    $(document).on("click", ".btnPageno", function() {
        var pageno = $(this).attr("data-pageno");
        ShowList(pageno, "", "0");
    });

    $("#btnSearch").on("click", function() {
        ShowList();
    })
    
</script>

</body>

</html>