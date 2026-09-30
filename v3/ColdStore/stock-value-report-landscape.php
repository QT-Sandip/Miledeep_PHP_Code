<?php
session_start();

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
$AccessType = $_SESSION['AccessType'];

$filterColdStores = "T";
//$filterPlant = "T";
$filterProductionDate = "T";
$filterTypeProduct = "T";
$filterSpecies = "T";
$filterFreezingType = "T";
//$filterVarietyOnlyTwo = "T";
$filterShortName = "T";
$filterSoaking = "T";
$filterPckgStyle = "T";
$filterGrade = "T";
//$filterType = "T";
$buttomButton = "T";
$filterStockBlgsTo = 'T';

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
                <?php include_once WORKING_MODULE . '/includes/page-filter.php'; ?>

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <!-- <input type="hidden" id="hdnBlocks" value="<?php echo htmlspecialchars(json_encode($resBlockData), ENT_QUOTES, 'UTF-8'); ?>" /> -->
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
            scrollCollapse: true,
            bInfo: false,
            bSort: false,
            dom: 'Blfrtip',
            searching: false,
            buttons: [{
                extend: 'excel',
                text: 'Report Download',
                className: 'btnExcelDataTable btn-left-align',
                // title: 'Receiving Overall Report <?php echo date("d-m-Y") ?>',
                // footer: true,
                // exportOptions: {
                //     columns: ':visible'
                // },
                action: function() {
                    ShowList();
                }
            }],
        });
    }

    $(document).ready(function() {
        $("#drpStockBlgsTo").prop('required', true);
        $('#drpPDCategory').parent('div').hide();
        var productionPlant = <?php echo $ProductionPlant ?>;
        if (productionPlant != 0) {
            $("#drpPlant, #drpColdStore").prop('disabled', true);
            $("#drpStockBlgsTo").val($("[data-processorid='" + productionPlant + "']").val()).trigger('change');
            $("#drpPlant").val(productionPlant).trigger('change');
             $("#drpColdStore").val($("[data-plant='" + productionPlant + "']").val()).trigger('change');

        } else {
            $("#drpPlant, #drpStockBlgsTo, #drpColdStore").prop('disabled', false);
            $("#drpPlant, #drpStockBlgsTo, #drpColdStore").val("").trigger('change');
        }

    });

    function fnSuccess(data) {

        $("#divDataList").html(data);
        fnShowDataTable();

        $('html, body').animate({
            scrollTop: $("#divDataList").offset().top - 50
        }, 10);
    }

    function ShowList() {
        /* if($("#drpStockBlgsTo").length != 0 && !$("#drpStockBlgsTo").val()){
            $("#drpStockBlgsTo").next('.select2-container')
            .find('.select2-selection')
            .css('border', '1px solid red');
            return;
        }else{
            $("#drpStockBlgsTo").next('.select2-container')
            .find('.select2-selection')
            .css('border', '1px solid');
        } */
        $.ajax({
            url: 'Ajax/ajax-stock-value-report-landscape-v2.php',
            type: 'POST',
            data: {
                coldStores: $.trim($("#drpColdStore").val()),
                plantID: $("#drpPlant").val(),
                productionDate: $.trim($("#txtProductionDate").val()),
                typeProduct: $.trim($("#drpTypeProduct").val()),
                typeSpecies: $.trim($("#drpSpecies").val()),
                freezingType: $.trim($("#drpFreezingType").val()),
                typeVariety: $.trim($("#drpVariety").val()),
                typeSoaking: $.trim($("#drpSoaking").val()),
                pakingStyle: $.trim($("#drpPckgStyle").val()),
                typeGrade: $.trim($("#drpGrade").val()),
                //type: $.trim($("#drpType").val()),
                type: 'MCS',
                drpStockBlgsTo : $.trim($("#drpStockBlgsTo").val())
            },
            timeout: 0, // unlimited
            dataType: "json", 
            success: function(response) {
                //console.log(response)
                if(response.filename){
                    window.location.href = response.filename;   
                }else{
                    if(response.msg){
                        alert(response.msg)
                    }
                }
            },
            error: function(response){
                console.log(response);
                alert('Time Out.')
            }
        });
    }
    $("#btnSearch").on("click", function() {
        <?php 
        if($AccessType == 1){?>            
            ShowSummeryList();
        <?php }
        else {?>            
            ShowList();
        <?php } ?>
    });
    function ShowSummeryList(){
        $.ajax({
            url: 'Ajax/ajax-stock-value-report-landscape-v2.php',
            type: 'POST',
            data: {
                coldStores: $.trim($("#drpColdStore").val()),
                plantID: $("#drpPlant").val(),
                productionDate: $.trim($("#txtProductionDate").val()),
                typeProduct: $.trim($("#drpTypeProduct").val()),
                typeSpecies: $.trim($("#drpSpecies").val()),
                freezingType: $.trim($("#drpFreezingType").val()),
                typeVariety: $.trim($("#drpVariety").val()),
                typeSoaking: $.trim($("#drpSoaking").val()),
                pakingStyle: $.trim($("#drpPckgStyle").val()),
                typeGrade: $.trim($("#drpGrade").val()),
                //type: $.trim($("#drpType").val()),
                type: 'MCS',
                drpStockBlgsTo : $.trim($("#drpStockBlgsTo").val()),
                Request : 'SummeryData'
            },
            timeout: 0, // unlimited
            dataType: "html", 
            success: function(response) {
               // console.log(response)
                $('#divDataList').html(response);
                fnShowDataTable();
            },
            error: function(response){
                console.log(response);
                alert('Time Out.')
            }
        });
    }
</script>

</body>

</html>