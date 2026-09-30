<?php

session_start();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$bid = isset($_GET["BID"]) && !empty($_GET["BID"]) ? $_GET["BID"] : "";
$barCodeId = isset($_GET["BarCodeId"]) && !empty($_GET["BarCodeId"]) ? $_GET["BarCodeId"] : "";
$StockID = isset($_GET["StockID"]) && !empty($_GET["StockID"]) ? $_GET["StockID"] : "";
$ClientConfigData = $_SESSION['ClientConfigData'];
$PackagingStoreIds = !empty($ClientConfigData['PackagingStoreIds']) ? trim($ClientConfigData['PackagingStoreIds']):[]; //anil


$api = API_COLDSTORE_DROPDOWNS;
$excludeList = array("StoreRacks","Stock","StockBelongsTo","ProductGroup","Exporters","Consignee","Processor","Shifts","PackingType","FreezingType","Soaking","ProductCategory","Abstatus","OptType","OutType","Grade","Variety","VarietyList","StockAvailableVariety","StockInVarietyList","PoNumber","PackingStyle","Plant","GeneralCategories","Status","TenantType","ProductionLines","Brand","BrandType","Species","Tranction","TypeProduct","Type","CsTransactionTypes","ProductForm","Preparation","GlazeType","ShipoutPoNumber","ProductionPlant","PoNumberNew","TransactionTypesNew","InwardGroups");
$bodyContent['exclude_list'] = $excludeList ? $excludeList : [];
$resList = CallAPI($api, "GET", "", $bodyContent);
$resColdStores = $resList->Data->ColdStores;
$resBlocks = $resList->Data->Blocks;
$resFloors = $resList->Data->Floors;

$ProductionPlant = $_SESSION['UnitID'];
if(empty($ProductionPlant) && count($resColdStores) == 1){
    $ProductionPlant = 1;
}
//echo '<pre>';print_r($_SESSION);die;

?>

<!doctype html>
<html lang="en">

<head>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" integrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <?php include_once ROOT . 'includes/head.php' ?>

    <style>

        .dropdown-list.show {
    display: block;
    opacity: 1;
    transform: scaleY(1);
}
        .used2-dot::before {
            background: oklch(0.5 0.26 301.69);
            border: 2px solid oklch(0.5 0.26 301.69);
        }
        .used3-dot::before {
            background: #3747e6;
            border: 2px solid #3747e6;
        }
        .text-xs{ font-weight: 400; }
        .summary-value{ font-weight: 400; }
      table.table-bordered.dataTable tbody th, table.table-bordered.dataTable tbody td{
        color: oklch(37.3% 0.034 259.733);
      }
    .total-stored-text-layout ul{margin: unset!important;}
    .store-filter-panel{padding: unset!important;}
    body{font-family: Inter}
    .total-box .label,.total-box .value{margin-bottom:unset!important;font-size: 14px;}
    .total-box-container{display: table; margin: 0 auto;margin-bottom: 15px;}
    .bg-green-50{background-color: var(--color-green-50);}
    .bg-amber-50{background-color: oklch(97.7% 0.014 308.299);}
    .bg-amber-200{background-color: oklch(90.2% 0.063 306.703);}
    .bg-amber-200{background-color: oklch(90.2% 0.063 306.703);}
    .border-amber-200{border-color:oklch(90.2% 0.063 306.703);}
    .bg-rose-50{background-color: oklch(98% 0.016 73.684);}
    .bg-rose-200{background-color: oklch(90.1% 0.076 70.697);}
    .bg-blue-50{background-color: var(--color-blue-50);}
      .border-blue-200 {
        border-color: var(--color-blue-200);
      }
      .summary-icon {
    width: 48px;
    height: 48px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 26px;
    background-color: var(--color-blue-200);
    </style>

</head>

<?php include_once ROOT . 'includes/body-preloader.php' ?>

<!-- Begin page -->
<div id="layout-wrapper">

    <?php include_once ROOT . 'includes/menu.php' ?>

    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <!-- ============================================================== -->
    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">

                <?php include_once ROOT . 'includes/page-header.php' ?>

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <div class="card">
                            <div class="card-body">
                                <form id="frmCreateStoreLayout">
                                    <div class="row" style="width:100%">

                                        <input type="hidden" id="hdnBlocks" value="<?php echo htmlspecialchars(json_encode($resBlocks), ENT_QUOTES, 'UTF-8'); ?>" />
                                        <input type="hidden" id="hdnFloors" value="<?php echo htmlspecialchars(json_encode($resFloors), ENT_QUOTES, 'UTF-8'); ?>" />

                                        <div class="col-md-3 mb-3">
                                            <label for="drpStoreID" class="form-label">Store ID <span style="color:red">*</span></label>
                                            <select class="form-control drpSelect" name="drpStoreID" id="drpStoreID">
                                                <option value="">Select</option>
                                                <?php
                                                foreach ($resColdStores as $coldStore) {
                                                    echo '<option value="' . $coldStore->StoreID . '" data-blockslist="' . $coldStore->StoreBlocks . '" data-plant="' . $coldStore->ProductionPlantID . '">' . $coldStore->StoreName . '</option>';
                                                }
                                                ?>
                                                <select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="drpBlockID" class="form-label">Block ID <span style="color:red">*</span></label>
                                            <select class="form-control drpSelect" name="drpBlockID" id="drpBlockID">
                                                <option value="">Select</option>
                                                <select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="drpNoFloors" class="form-label">Floors<span style="color:red">*</span></label>
                                            <select class="form-control drpSelect" name="drpNoFloors" id="drpNoFloors" required>
                                                <option value="">Select</option>
                                                <select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="drpRackName" class="form-label">Rack Name</label>
                                            <select class="form-control drpSelect" name="drpRackName" id="drpRackName">
                                                <option value="">Select</option>
                                                <select>
                                        </div>
                                        <div class="col-md-12 text-center mb-3">
                                            <div><button class="btn btn-primary" name="btnSubmit" id="btnSubmit" type="submit">Submit</button></div>
                                        </div>

                                    </div>
                                </form>

                            </div>
                        </div>

                        <!-- <div class="card">
                            <div class="card-body"> -->
                        <div id="divStoreLayout"></div>
                        <!-- </div>
                        </div> -->

                    </div>
                </div>

            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->

        <div>
            <!-- Modal -->
            <div class="modal fade" id="popupPalletDeails1" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="exampleModalLabel">Pallet Details</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <table class="table table-bordered">
                                <thead>

                                    <tr>
                                        <th>ID</th>
                                        <th>Variety</th>
                                        <th>Grade</th>
                                        <th>Packing Type</th>
                                        <th>Packing Style</th>
                                        <th>Feezing Type</th>
                                        <th>Category</th>
                                        <th>Shift</th>
                                        <th>MCS</th>
                                        <th>Loose</th>
                                    </tr>

                                </thead>
                                <tbody>
                                    <tr>
                                        <td>ID</td>
                                        <td>Variety</td>
                                        <td>Grade</td>
                                        <td>Packing Type</td>
                                        <td>Packing Style</td>
                                        <td>Feezing Type</td>
                                        <td>Category</td>
                                        <td>Shift</td>
                                        <td>MCS</td>
                                        <td>Loose</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" id="btnPlacePopup1">Place</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal -->

            <div class="modal fade" id="popupPalletDeails2" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">

                <div class="modal-dialog modal-xl">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="exampleModalLabel">Pallet Details</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div id="rackTable" style="overflow-x: scroll;">
                                <!-- <table class="table table-bordered">
                                <thead>

                                    <tr>
                                        <th>Variety</th>
                                        <th>Grade</th>
                                    </tr>

                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Variety</td>
                                        <td>Grade</td>
                                    </tr>
                                </tbody>
                            </table> -->
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                            <button type="button" class="btn btn-primary" id="btnPlacePopup2" data-rackid="">Place</button>
                        </div>
                    </div>
                </div>
            </div>


        </div>

        <div>

        </div>

        <?php include_once ROOT . 'includes/footer.php' ?>

    </div>
    <!-- end main content-->

</div>
<!-- END layout-wrapper -->

<?php include_once ROOT . 'includes/common-js.php' ?>
<?php include_once WORKING_MODULE . 'modalSupplier.php' ?>
<?php include_once ROOT . '/includes/app-js.php' ?>
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
 <script src="https://cdn.jsdelivr.net/npm/datatables.net-fixedcolumns@4.3.0/js/dataTables.fixedColumns.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/datatables.net-fixedcolumns-dt@4.3.0/css/fixedColumns.dataTables.min.css" />
    <link rel="stylesheet" type="text/css" href="/assets/css/coldstore_viewracklayout.css">
  

<script>
    var qParam = "<?php echo $bid ?>";
    var barCodeId = "<?php echo $barCodeId ?>";
    var stockID = "<?php echo $StockID ?>";

    $(document).on('click', ".divUnFilled .form-check-input", function() {
        // if ($(this).prop('checked')==true) {
        //     $('#popupPalletDeails1').modal('show');
        // }

        if (barCodeId != "") {
            $('#popupPalletDeails2').modal('show');
            $("#btnPlacePopup2").attr("data-rackid", $(this).attr("data-rackid"));
            $("#btnPlacePopup2").prop('disabled', false);
        } else {
            alert("Rack empty");
        }

    });

    $(document).ready(function() {

        var productionPlant = <?php echo $ProductionPlant ?>;
        if (productionPlant != 0) {
            $("#drpStoreID").prop('disabled', true);
            $("#drpStoreID").val($("[data-plant='" + productionPlant + "']").val()).trigger('change');

        } else {
            $("#drpStoreID").prop('disabled', false);
            $("#drpStoreID").val("").trigger('change');
        }
    });
    // Durga Prasad 09-02-2024
    $(document).on('click', '.rackno', function() {
         $("#rackTable").html('');
        /*  if ($.fn.DataTable.isDataTable('#rackTableData')) {
            $('#rackTableData').DataTable().clear().destroy();
        } */
        var rack = $(this).data('rackid');
        console.log('Hello, RackID = ' + rack);
        $.ajax({
            url: 'Ajax/PKStore_AjaxViewStoreLayout.php',
            type: 'POST', 
            data: {
                methodType: 'rackDetailsForPakingStore',
                rackNo: rack
            },
            success: function(data) {
                $("#rackTable").html(data);
                
                /* $('#rackTableData').DataTable({

                    searching: false,
                    ordering: false,
                    scrollY: "300px",
                    scrollX: true,
                    sScrollXInner: "100%",
                    paging: false,
                    info: false,
                    
                  
                }); */


                return false;
            },
        });
    });
    // Durga Prasad 09-02-2024
    $(document).on('click', ".divFilled .form-check-input", function() {

        if (barCodeId != "") {
            alert("Rack filled");
        } else {
            $('#popupPalletDeails2').modal('show');
            $("#btnPlacePopup2").attr("data-rackid", $(this).attr("data-rackid"));
            $("#btnPlacePopup2").prop('disabled', true);
        }
    });


    // $(document).on("click", "#btnSubmit", function() {
    //     if($("#drpNoFloors").val() != "") {
    //         $.ajax({
    //             type: 'POST',
    //             url: 'Ajax/PKStore_AjaxViewStoreLayout.php',
    //             method: "post",
    //             data: {
    //                 methodType: "search",
    //                 storeID: $("#drpStoreID").val(),
    //                 blockID: $("#drpBlockID").val(),
    //                 floorsID: $("#drpNoFloors").val(),
    //                 rackName: $("#drpRackName").val(),
    //                 qparam: qParam
    //             },
    //             success: function(data) {
    //                 $("#divStoreLayout").html(data);
    //                 // jsonObject();
    //             }
    //         });
    //     } else {
    //         alert("please select Floors");
    //     }
    // });


    $("#frmCreateStoreLayout").validate({

        onkeyup: false,
        onfocusout: false,
        invalidHandler: function(form, validator) {
            var errors = validator.numberOfInvalids();
            if (errors) {
                var firstInvalidElement = $(validator.errorList[0].element);
                firstInvalidElement.focus();
            }
        },
        ignore: "",
        rules: {
            drpStoreID: {
                required: true
            },
            drpBlockID: {
                required: true
            },
            drpNoFloors: {
                required: true
            },
        },
        errorPlacement: function() {
            return false; // suppresses error message text
        },
        submitHandler: function() {
            $.ajax({
                type: 'POST',
                url: 'Ajax/PKStore_AjaxViewStoreLayout.php',
                method: "post",
                data: {
                    methodType: "search",
                    storeID: $("#drpStoreID").val(),
                    blockID: $("#drpBlockID").val(),
                    floorsID: $("#drpNoFloors").val(),
                    rackName: $("#drpRackName").val(),
                    qparam: qParam
                },
                success: function(data) {
                    $("#divStoreLayout").html(data);
                    // jsonObject();
                }
            });

            return false;

        }
    });

    $(document).on("change", "#drpStoreID", function() {
        var blocksList = JSON.parse($("#hdnBlocks").val());
        $("#drpNoFloors").html("").html("<option value='' data-floors=''>Select</option>");
   
        var selectBlocks = $(this).find(':selected').attr('data-blockslist');
        var PackagingStoreIdsArr = <?php echo json_encode(!is_array($PackagingStoreIds) ? explode(',',$PackagingStoreIds) :[]);?>;
        if (selectBlocks != "") {

            var selectBlocksAry = selectBlocks.split(",");
            if(PackagingStoreIdsArr.length != 0){
                 var selectBlocksAry = selectBlocksAry.filter(value => PackagingStoreIdsArr.includes(value));
            }

            var listBlock = "<option value='' data-floors=''>Select</option>";
            $.each(blocksList, function(index, val) {
                if (selectBlocksAry.indexOf(val.BlockID) !== -1) {
                    listBlock += '<option value="' + val.BlockID + '" data-floors="' + val.Floors + '">' + val.BlockName + '</option>';
                }
            });
            $("#drpBlockID").html("").html(listBlock);
        }
    })

    $(document).on("change", "#drpBlockID", function() {
        var floorList = JSON.parse($("#hdnFloors").val());

        var selectFloors = $(document).find("#drpBlockID").find(':selected').attr('data-floors');

        if (selectFloors != "") {

            var selectFloorsAry = selectFloors.split(",");

            var listFloor = "<option value=''>Select</option>";
            $.each(floorList, function(index, val) {
                if (selectFloorsAry.indexOf(val.FloorID) !== -1) {
                    listFloor += '<option value="' + val.FloorID + '">' + val.FloorName + '</option>';
                }
            });

            $("#drpNoFloors").html("").html(listFloor);
        } else {
            $("#drpNoFloors").html("").html("<option value=''>Select</option>");
        }
    })

    // Getting RackNumbers from Inward Page 13-03-2024
    $(document).on("change", "#drpNoFloors", function() {
        var floorID = $(this).val();

        if(floorID != "") {
            $.ajax({
                url: 'Ajax/AjaxInward.php',
                type: 'POST',
                data: {
                    methodType: "getRackList",
                    floorID: $.trim($(this).val()),
                    blockID: $.trim($("#drpBlockID").val()),
                    storeID: $.trim($("#drpStoreID").val())
                },
                success: function(data) {
                    console.log(data);

                    var listFloor = "<option value=''>select</option>";
                    $.each(data, function(index, val) {
                        // listFloor += '<option value="' + val.RackID + '">' + val.RackName + '</option>';
                        if (val.IsPath != "1") {
                            if (val.Status == "1") {
                                listFloor += '<option value="' + val.RackID + '">' + val.RackName + " (Filled)" + '</option>';
                            } else {
                                listFloor += '<option value="' + val.RackID + '">' + val.RackName + '</option>';
                            }
                        }
                    });

                    $("#drpRackName").html(listFloor);

                    return false;
                }
            });
        } else {
            $("#drpRackName").html("<option value=''>select</option>");
        }

    })

    $(document).on("click", "#btnPlacePopup1", function() {

        $.ajax({
            type: 'POST',
            url: 'Ajax/PKStore_AjaxViewStoreLayout.php',
            method: "post",
            data: {
                methodType: "palletDetails_pop1",
            },
            success: function(data) {
                // $("#divStoreLayout").html(data);
                // jsonObject();
            }
        });

    });

    $(document).on("click", "#btnPlacePopup2", function() {

        $.ajax({
            type: 'POST',
            //url: 'Ajax/PKStore_AjaxViewStoreLayout.php',
            url: 'Ajax/AjaxPackingStoreInward.php',
            method: "post",
            data: {
                methodType: "placePaletteFromLayOut",
                barCId: barCodeId,
                stockID: stockID,
                rackID: $.trim($(this).attr("data-rackid"))
            },
            success: function(data) {
                console.log(data);
                $('#modalNewPalette').modal('hide');

                if (data.Error) {
                    alert(data.Error);
                } else {
                    alert(data.Msg);
                    // $("#btnSubmit").trigger('click');
                    window.location.href = "packing-store-view-store-layout";
                }
                $('#popupPalletDeails2').modal('hide');
            }
        });

    });
</script>
</body>

</html>
