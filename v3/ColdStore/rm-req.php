<?php
session_start();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
$PageTitle = "Rm Requirement";
$excludeList = array("ColdStores","Blocks","Floors","StoreRacks","Stock","StockBelongsTo","ProductGroup","Exporters","Consignee","Processor","Shifts","PackingType","Soaking","Abstatus","OptType","OutType","Variety","StockAvailableVariety","StockInVarietyList","PackingStyle","GeneralCategories","Status","TenantType","ProductionLines","Brand","BrandType","Tranction","Type","CsTransactionTypes","ProductForm","Preparation","GlazeType","ShipoutPoNumber","ProductionPlant","PoNumberNew","TransactionTypesNew","InwardGroups");
$filterSpecies = "T";
// $filterColdStores = "T";
$filterTypeProduct = "T";
$filterPoNumber = "T";
$filterPlant = "T";
$filterVarietyList = "T";
$filterFreezingType = "T";
$filterGrade = "T";
$buttomButton = "T";

$ProductionPlant = $_SESSION['UnitID'];

?>

<!doctype html>
<html lang="en">

<head>
    <?php include_once ROOT . '/includes/head.php' ?>
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
                title: 'RM REQ  <?php echo date("d-m-Y") ?>',
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
        // container.scrollLeft(position);
    }

    // function plantChange() {
    //     var productionPlant = <?php echo $ProductionPlant ?>;

    //     if (productionPlant != 0) {
    //         $("#drpPlant").val(productionPlant).trigger('change');

    //     } else {
    //         $("#drpPlant").val("").trigger('change');
    //     }

    //     var $drpPoNumber = $("#drpPoNumber");

    //     if (productionPlant != 0) {
    //         $drpPoNumber.find('option').each(function() {
    //             var optionPlantId = $(this).data('plantid');

    //             var plantIdArray = String(optionPlantId).split(',');

    //             if (plantIdArray.includes(String(productionPlant)) || $(this).val() === "") {
    //                 $(this).show();
    //             } else {
    //                 $(this).remove();
    //             }
    //         });
    //     }

    //     $("#drpPoNumber").on("change", function() {

    //         var hdnGradeList = JSON.parse($("#hdnGradeList").val());

    //         if ($("#drpPoNumber").val() !== '') {
    //             var gradeId = $("#drpPoNumber option:selected").data('grade');
    //             var allowedGradeIds = gradeId.split(",");

    //             var listGrade = "<option value=''>Select</option>";
    //             $.each(hdnGradeList, function(index, val) {
    //                 if (allowedGradeIds.indexOf(val.value) !== -1) {
    //                     listGrade += '<option value="' + val.value + '">' + val.Options + '</option>';
    //                 }
    //             });
    //             $("#drpGrade").html("").html(listGrade);
    //         } else {
    //             var listGrade = "<option value=''>Select</option>";
    //             $.each(hdnGradeList, function(index, val) {
    //                 listGrade += '<option value="' + val.value + '">' + val.Options + '</option>';
    //             });
    //             $("#drpGrade").html("").html(listGrade);
    //         }
    //     });
    // }
    $(document).ready(function() {
        var productionPlant = <?php echo $ProductionPlant ?>;

        if (productionPlant != 0) {
            $("#drpPlant").val(productionPlant).trigger('change');

        } else {
            $("#drpPlant").val("").trigger('change');
        }

        var $drpPoNumber = $("#drpPoNumber");

        if (productionPlant != 0) {
            $drpPoNumber.find('option').each(function() {
                var optionPlantId = $(this).data('plantid');

                var plantIdArray = String(optionPlantId).split(',');

                if (plantIdArray.includes(String(productionPlant)) || $(this).val() === "") {
                    $(this).show();
                } else {
                    $(this).remove();
                }
            });
        }

        $("#drpPoNumber").on("change", function() {

            var hdnGradeList = JSON.parse($("#hdnGradeList").val());

            if ($("#drpPoNumber").val() !== '') {
                var gradeId = $("#drpPoNumber option:selected").data('grade');
                var allowedGradeIds = gradeId.split(",");

                var listGrade = "<option value=''>Select</option>";
                $.each(hdnGradeList, function(index, val) {
                    if (allowedGradeIds.indexOf(val.value) !== -1) {
                        listGrade += '<option value="' + val.value + '">' + val.Options + '</option>';
                    }
                });
                $("#drpGrade").html("").html(listGrade);
            } else {
                var listGrade = "<option value=''>Select</option>";
                $.each(hdnGradeList, function(index, val) {
                    listGrade += '<option value="' + val.value + '">' + val.Options + '</option>';
                });
                $("#drpGrade").html("").html(listGrade);
            }
        });
    });

    function ShowList(pageNo, searchkey, loadAll) {
        $.ajax({
            url: 'Ajax/ajax-rm-req.php',
            type: 'POST',
            data: {
                ordernumber: $.trim($("#drpPoNumber").val()),
                variety: $.trim($("#drpVariety").val()),
                species: $.trim($("#drpSpecies").val()),
                productform: $.trim($("#drpTypeProduct").val()),
                freezingtype: $.trim($("#drpFreezingType").val()),
                // plant: $("#drpColdStore option:selected").data("plant"),
                plant: $("#drpPlant").val(),
                grade: $.trim($("#drpGrade").val()),
                pageNo: pageNo,
                searchkey: searchkey,
                loadAll: loadAll,
            },
            beforeSend: function () {
            // Show loader
            $("#preloader").show();
            $("#status").show();
        },
            success: function(data) {
                fnSuccess(data);
                // $("#divDataList").html(data);
                // fnShowDataTable();
                return false;
            },
            complete: function () {
            // Hide loader after success/error
            $("#preloader").hide();
            $("#status").hide();
        }
        });
    }

    $(document).ready(function() {
        $('#btnSearch').before(
            '<span class="text-primary position-absolute start-0" style="margin-left: 14px;font-size: 15px;"><strong class="">Note</strong> : Today Raw Material  Quantity is not added</span>'
        );
        //ShowList("1", "", "0");
    });

    $("#btnSearch").on("click", function() {
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
    $("#drpPlant, #drpPoNumber").on("change",function(){
        $("#divDataList").html("");
    });
</script>

</body>

</html>
