<?php
session_start();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$apiList = API_FOOD_AUDIT;

$FoodAuditID = $_GET["FoodAuditID"];

$bodyContent = array(
    "FoodAuditID" => $FoodAuditID
);

if ($FoodAuditID != '') {
    $result = CallAPI($apiList, "GET", "", $bodyContent);
    // echo json_encode($result, JSON_PRETTY_PRINT);
    // exit;
    $Data = $result->Data[0];
}

$api = API_QC_DROPDOWNS;

$reslist = CallAPI($api, "GET", "", "");

// $resProductionPlant = $reslist->Data->Plant;
$resAuditCategory = $reslist->Data->AuditCategory;

$ProductionPlant = $_SESSION['UnitID'];
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
                                <div class="row">

                                    <!-- <div class="form-group col-sm-3 mb-3">
                                    <label for="drpProductionPlant" class="form-label">Production Plant<span style="color:red">*</span></label>
                                     <select class="drpSelect form-control" name="drpProductionPlant" id="drpProductionPlant"  >
                                        <option value=""> Select</option>
                                            <?php foreach ($resProductionPlant as $Item) { ?>
                                             <option value="<?php echo $Item->ProcessingStationID; ?>">
                                            <?php echo $Item->FullName . ' - ' . $Item->StationName; ?></option>
                                            <?php } ?>
                                    </select>
                                </div> -->

                                    <div class="col col-md-3 mb-3">
                                        <label for="drpCategoryName">Audit Category</label>
                                        <select class="drpSelect" name="drpCategoryName" id="drpCategoryName">
                                            <option value="" <?php echo ($Data->AuditID == '') ? 'selected' : ''; ?>>
                                                Select</option>
                                            <?php foreach ($resAuditCategory as $item) { ?>
                                                <?php //if ($item->PlantID == $ProductionPlant) { ?>
                                                <option value="<?php echo $item->AuditID ?>" <?php echo ($Data->AuditNameID == $item->AuditID) ? 'selected' : ''; ?>
                                                    data-certificate-no="<?php echo $item->AuditCertificationNo ?>">
                                                    <?php echo $item->CategoryName ?></option>
                                                <? php// } ?>
                                            <?php } ?>
                                        </select>
                                    </div>

                                </div>
                                <div class="text-center">
                                    <button id="btnSearch" type="button"
                                        class="btn btn-primary waves-effect waves-light">
                                        Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                                    </button>
                                    <a href="audit-categories-option-create"
                                        class="btn btn-success waves-effect waves-light">
                                        <i class="fa fa-plus"></i> Create Category Options
                                    </a>
                                </div>
                                <br>

                            </div>
                        </div>

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

    $(document).ready(function () {

        var productionPlant = <?php echo $ProductionPlant ?>;
        if (productionPlant != 0) {
            $("#drpProductionPlant").prop('disabled', true);
            $("#drpProductionPlant").val(productionPlant).trigger('change');

        } else {
            $("#drpProductionPlant").prop('disabled', false);
            $("#drpProductionPlant").val("").trigger('change');
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
            url: 'Ajax/ajax-audit-categories-options-list.php',
            type: 'POST',
            data: {
                //productionPlant: $("#drpProductionPlant").val(),
                categoryName: $("#drpCategoryName").val(),
                pageNo: pageNo,
                searchkey: searchkey,
                loadAll: loadAll,
            },
            success: function (data) {
                fnSuccess(data);
                return false;
            }
        });
    }

    $(document).ready(function () {
        ShowList("1", "", "0");
    });

    $(document).on("click", "#btnMoreSearch", function () {
        var searchkey = $("#tblDataTable_filter input").val();
        if (searchkey.length > 0) {
            ShowList("1", searchkey, "0");
        }
    });

    $(document).on("click", "#btnLoadAll", function () {
        ShowList("1", "", "1");
    });

    $(document).on("click", ".btnPageno", function () {
        var pageno = $(this).attr("data-pageno");
        ShowList(pageno, "", "0");
    });

    $("#btnSearch").on("click", function () {
        ShowList("1", "", "0");
    })
</script>

</body>

</html>