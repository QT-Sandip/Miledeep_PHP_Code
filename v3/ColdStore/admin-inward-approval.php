<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$filterProductionDate = "T";
$filterColdStores = "T";
$filterStockBlgsTo = "T";
$filterVarietyList = "T";
$filterGrade = "T";
$filterPckgStyle = "T";
$filterStockStatus = "T";
$adminInwardButton = "T";
$filterPoNumber = "T";
$filterSpecies = "T";
$filterFreezingType = "T";
$filterTypeProduct = "T";

$api = API_STORE_RACK_DETAILS;

$bodyContent = array(
    "BlockID" => ""
);

$resRackDetails = CallAPI($api, "GET", "", $bodyContent);
// echo json_encode($resRackDetails, JSON_PRETTY_PRINT);
// exit;
$resStoresData = $resRackDetails->StoresData;
$resBlockData = $resRackDetails->BlockData;


$apiDrp = API_COLDSTORE_DROPDOWNS;

$resDrpList = CallAPI($apiDrp, "GET", "", "");
$resColdStores = $resList->Data->ColdStores;
$resBlocks = $resList->Data->Blocks;
$resFloors = $resList->Data->Floors;

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
                <?php include_once WORKING_MODULE . '/includes/page-filter.php';
                include "PopUpTable.php";

                ?>

                <input type="hidden" id="hdnSelectedRow" />
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <input type="hidden" id="hdnBlocks" value="<?php echo htmlspecialchars(json_encode($resBlockData), ENT_QUOTES, 'UTF-8'); ?>" />

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
            bsort: false,
            dom: 'Blfrtip',
            // dom: '<"top-left"Blfrtip>',
            buttons: [{
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable btn-left-align',
                title: 'Admin Inward Approval  <?php echo date("d-m-Y") ?>',
                footer: true,
                exportOptions: {
                    columns: ':visible'
                }
            }],
        });
    }

    $("#drpVariety").on("change", function() {
        var species = $.trim($(this).find(':selected').data('species'));
        var shortName = $.trim($(this).find(':selected').data('shortname'));
        var typeProduct = $.trim($(this).find(':selected').data('typeproduct'));

        $("#hdnFilterSpecies").val(species);
        $("#hdnFilterShortName").val(shortName);
        $("#hdnFilterTypeProduct").val(typeProduct);

    })

    function fnSuccess(data) {

        $("#divDataList").html("").html(data);
        fnShowDataTable();

        $("<button class='btn btn-primary' id='btnMoreSearch' style='padding: 3px 5px;margin-left: 10px;'>More Search</button>" +
                "<button class='btn btn-primary' id='btnLoadAll' style='padding: 3px 5px;margin-left: 10px;'>Load All</button>"
            )
            .insertAfter("#tblDataTable_filter label");

        $('html, body').animate({
            scrollTop: $("#divDataList").offset().top - 50
        }, 10);


        var container = $(document).find('.pagination');

        if($(document).find('.page-link.active').length != 0) {
            var targetClass = $(document).find('.page-link.active');
            var offset = targetClass.offset().left - container.offset().left;

            container.scrollLeft(offset);
        }

        // var position = targetClass.position().left;
    }

    function ShowList(pageNo, searchkey, loadAll) {
        $.ajax({
            url: 'Ajax/ajax-admin-inward-approval.php',
            type: 'POST',
            data: {
                coldStore: $("#drpColdStore").val(),
                stockBlgsTo: $("#drpStockBlgsTo").val(),
                variety: $("#drpVariety").val(),
                grade: $("#drpGrade").val(),
                pckgStyle: $("#drpPckgStyle").val(),
                stockStatus: $("#drpStockStatus").val(),
                fShortName: $("#hdnFilterShortName").val(),
                PoNumber: $("#drpPoNumber").val(),
                species: $("#drpSpecies").val(),
                typeProduct: $("#drpTypeProduct").val(),
                freezingType: $("#drpFreezingType").val(),
                pageNo: pageNo,
                searchKey: searchkey,
                loadAll: loadAll,
            },
            success: function(data) {
                fnSuccess(data);
                return false;
            }
        });
    }


    // function stockStatus() {
    //     var stockStatus = $("#drpStockStatus").val();
    //     if (stockStatus == 1) {
    //         $("#btnApprove").prop('disabled', true);
    //     }
    // }

    $(document).ready(function() {
        ShowList("1", "", "0");
        $("#btnApprove").prop("disabled", true);
        // stockStatus();
    });

    $(document).on("click", "#btnMoreSearch", function() {
        var searchkey = $("#tblDataTable_filter input").val();
        if (searchkey.length > 0) {
            ShowList("1", searchkey, "0");
        }
        // stockStatus();
    });

    $(document).on("click", "#btnLoadAll", function() {
        ShowList("1", "", "1");
        // stockStatus();
    });

    $(document).on("click", ".btnPageno", function() {
        var pageno = $(this).attr("data-pageno");
        ShowList(pageno, "", "0");
        stockStatus();
    });

    $("#btnSearch").on("click", function() {
        ShowList("1", "", "0");
        // stockStatus();
    });

    function checkedlist() {
        var selectedOptions = new Array();
        $("input.rowCheck:checked").each(function() {
            selectedOptions.push(this.value);
        });
        $('#hdnSelectedRow').val(selectedOptions);

        if($('#hdnSelectedRow').val().length == 0) {
            $("#btnApprove").prop("disabled", true);
        }else{
            $("#btnApprove").prop("disabled", false);
        }
    }

    $(document).on("click", "input.rowCheck", function() {
        checkedlist();
        $("#checkall").prop("checked", false);
    });

    $(document).on("click", "#checkall", function() {
        if (this.checked) {
            $(".rowCheck").prop("checked", true);
            
        } else {
            $(".rowCheck").prop("checked", false);
            
        }
        checkedlist();
    });


    $("#btnApprove").on("click", function() {
        var selectedRows = $("#hdnSelectedRow").val();
        if (confirm('Do you want to Approve the Stock Status?')) {
            $.ajax({
                url: 'Ajax/ajax-admin-inward-approval.php',
                type: 'POST',
                data: {
                    methodtype: "inwardApproval",
                    stockIdList: $("#hdnSelectedRow").val(),
                    pageNo: $(".active.btnPageno").attr("data-pageno"),
                    searchKey: $("#tblDataTable_filter input[type=search]").val(),
                    stockStatus: $("#drpStockStatus").val(),
                    productionDate: $("#txtProductionDate").val(),
                    loadAll: "0",
                },
                success: function(data) {
                    fnSuccess(data);
                    // $("#divDataList").html("").html(data);
                    //fnShowDataTable();
                    alert($(document).find("#hdnSuccess").val());
                    return false;
                }
            });
        }
    });


    
</script>

</body>

</html>