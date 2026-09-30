<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

session_start();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
$ClientConfigData = $_SESSION['ClientConfigData'];
$PackagingStoreIds = !empty($ClientConfigData['PackagingStoreIds']) ? trim($ClientConfigData['PackagingStoreIds']):[]; //anil

$filterPlant = "T";
$filterColdStores = "T";
$filterBlocksTwo = "T";
//$filterStockBlgsTo = "T";
//$filterVarietyList = "T";
//$filterGrade = "T";
//$filterStockType = "T";
//$buttomButton = "T";
$filterPoNumber = "T";
//$filterPackingType = "T";
$shimentOutButton = "T";


$api = API_STORE_RACK_DETAILS;

$bodyContent = array(
    "BlockID" => ""
);

$resRackDetails = CallAPI($api, "GET", "", $bodyContent);
// echo json_encode($resRackDetails, JSON_PRETTY_PRINT);
// exit;
$resStoresData = $resRackDetails->StoresData;
$resBlockData = $resRackDetails->BlockData;


/* $apiDrp = API_COLDSTORE_DROPDOWNS;

$bodyContent = array(
    'page'=>'daily-production-list',
    'exclude_list'=>array("StockAvailableVariety","ShipoutPoNumber","PoNumberNew",
    'StockBelongsTo','Shifts','FreezingType','Soaking','ProductCategory',
    'Abstatus','StockInVarietyList','InwardGroups','TransactionTypesNew','CsAllTransactionTypes','ProductionPlant','Processor',
    'Plant','PackingStyle','Grade','Brand','PackingType','PoNumberFreezing','GeneralCategories','BrandType','CsTransactionTypes','ProductForm','ProductionLines','Variety','VarietyList','Exporters','Consignee','Stock','ProductGroup',
    'PoNumber')
);
$resDrpList = callAPI($apiDrp, "GET", "", $bodyContent); */
$resColdStores = $resList->Data->ColdStores;
$resBlocks = $resList->Data->Blocks;
$resFloors = $resList->Data->Floors;

$ProductionPlant = $_SESSION['UnitID'];


// echo $ProductionPlant;
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
        #myModal .modal-lg,
        #myModal .modal-xl {
            --bs-modal-width: 80% !important;
        }
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

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <input type="hidden" id="hdnBlocks" value="<?php echo htmlspecialchars(json_encode($resBlockData), ENT_QUOTES, 'UTF-8'); ?>" />
                        <div class="card">
                            <div class="card-body">
                                <div id="divDataList">
                                </div>
                                <input type="hidden" id="hdnActionType" />
                                <input type="hidden" id="hdnSelectedRow" />
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

    <div class="modal" id="modalNewPalette" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Place Palette</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="frmPopup">
                    <div class="modal-body">
                        <div class="row">

                            <div class="col-md-4 mb-4">
                                <label for="drpStoreID" class="form-label">Store ID <span style="color:red">*</span></label>
                                <select class="form-select drpSelectModal" name="drpStoreID" id="drpStoreID">
                                    <option value="">Select</option>
                                    <?php
                                    foreach ($resStoresData as $item) {
                                        echo '<option value="' . $item->StoreID . '" data-blocks="' . $item->StoreBlocks . '">' . $item->StoreName . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-4">
                                <label for="drpBlockID" class="form-label">Block ID <span style="color:red">*</span></label>
                                <select class="form-select drpSelectModal" name="drpBlockID" id="drpBlockID">
                                    <option value="">Select</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-4">
                                <label for="drpRackName" class="form-label">Rack Name<span style="color:red">*</span></label>
                                <select class="form-select drpSelectModal" name="drpRackName" id="drpRackName">
                                    <option value="">Select</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                        <button type="submit" id="btnPlacePallet" class="btn btn-primary">Place Palette</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal" id="modalAdd" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Place Palette</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="frmAddPopup">
                    <div class="modal-body">
                        <div class="row">

                            <input type="hidden" id="hdnCSBlocks" value="<?php echo htmlspecialchars(json_encode($resBlocks), ENT_QUOTES, 'UTF-8'); ?>" />
                            <input type="hidden" id="hdnCSFloors" value="<?php echo htmlspecialchars(json_encode($resFloors), ENT_QUOTES, 'UTF-8'); ?>" />

                            <div class="col-md-4 mb-4">
                                <label for="drpStoreID" class="form-label">Store ID <span style="color:red">*</span></label>
                                <select class="form-select drpSelectModal" name="drpStoreID" id="drpStoreID" disabled>
                                    <option value="">Select</option>
                                    <?php
                                    foreach ($resColdStores as $coldStore) {
                                        echo '<option value="' . $coldStore->StoreID . '" data-blockslist="' . $coldStore->StoreBlocks . '">' . $coldStore->StoreName . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-4">
                                <label for="drpAddBlockID" class="form-label">Block ID <span style="color:red">*</span></label>
                                <select class="form-select drpSelectModal" name="drpAddBlockID" id="drpAddBlockID">
                                    <option value="">Select</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-4">
                                <label for="drpAddRackName" class="form-label">Rack Name<span style="color:red">*</span></label>
                                <select class="form-select drpSelectModal" name="drpAddRackName" id="drpAddRackName">
                                    <option value="">Select</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" id="hdnStockId" value="" />
                        <input type="hidden" id="hdnExistingId" value="" />
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                        <button type="submit" id="btnPlacePallet" class="btn btn-primary">Place Palette</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- END layout-wrapper -->




<?php include_once ROOT . '/includes/common-js.php' ?>

<!-- Required datatable js -->
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
<!-- included for fixed column -->
<script
    src="https://cdn.jsdelivr.net/npm/datatables.net-fixedcolumns@4.3.0/js/dataTables.fixedColumns.min.js"></script>
<link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/datatables.net-fixedcolumns-dt@4.3.0/css/fixedColumns.dataTables.min.css" />
<!-- included for fixed column -->
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
dom: '<"row mb-2"<"col-md-6 d-flex align-items-center"lf><"col-md-6 custom-filters"B>>rtip',

            // dom: '<"top-left"Blfrtip>',
            order: [], // disables initial sorting
            columnDefs: [
                { orderable: false, targets: [0, -1] } // disable on first and last columns
            ],
             fixedColumns: {
                leftColumns: 1 // Freeze until Soaking
            },
            language: {
                lengthMenu: "Show _MENU_ records",
                paginate: {
                    previous: "<i class='mdi mdi-chevron-left'>",
                    next: "<i class='mdi mdi-chevron-right'>"
                }
            },
            buttons: [{
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable btn-left-align',
                title: 'Store on Floor Stock <?php echo date("d-m-Y") ?>',
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

    });

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



    function poNumber(productionPlant) {
        var $drpPoNumber = $("#drpPoNumber");

        $drpPoNumber.find('option').each(function() {
            var optionPlantId = $(this).data('plantid');
            var plantIdArray = String(optionPlantId).split(',');

            if (productionPlant == 0 || plantIdArray.includes(String(productionPlant)) || $(this).val() === "") {
                $(this).show();
            } else {
                $(this).remove();
            }
        });
    }

    function plantAutoFill() {
        var productionPlant = <?php echo $ProductionPlant ?>;
        if (productionPlant != 0) {
            $("#drpColdStore").prop('disabled', true);
            $("#drpColdStore").val($("[data-plant='" + productionPlant + "']").val()).trigger('change');
        } else {
            $("#drpColdStore").prop('disabled', false);
            $("#drpColdStore").val("").trigger('change');
        }

        poNumber(productionPlant);
    }

    $(document).on("change", "#drpColdStore", function() {
        storeBlocks();

        var productionPlant = <?php echo $ProductionPlant ?>;
        poNumber(productionPlant);
    });

    $(document).ready(function() {
         $('#drpPoNumber').closest('div').after(`<div class="col-md-3 mb-3">
                                                        <label for="drpGroup">Group</label>
                                                        <select class="drpSelect form-control" id="drpGroup" name="drpGroup[]" multiple >
                                                            <option value="" disabled>All</option>
                                                            <option value="Mcs">Mcs</option>
                                                            <option value="Pouches">Pouches</option>
                                                            <option value="Stickers">Stickers</option>
                                                            <option value="Tape Rolls">Tape Rolls</option>
                                                            <option value="Spacers">Spacers</option>
                                                            <option value="Count Slips">Count Slips</option>
                                                        </select>
                                                    </div>`);
        $('#drpGroup').select2({
    closeOnSelect: false
});
        $('#drpPDCategory').parent('div').css('display','none')
        plantAutoFill();

        var blockIds = $("#drpColdStore option:selected").data("blocks");
        var PackagingStoreIdsArr = <?php echo json_encode(!is_array($PackagingStoreIds) ? explode(',',$PackagingStoreIds) :[]);?>;
        if (blockIds && blockIds.length > 0) {
            var blockIdsArray = blockIds.split(',').map(function(id) {
                return id.trim();
            });
            var commonValues = blockIdsArray.filter(value => PackagingStoreIdsArr.includes(value));

            var $drpBlocks = $("#drpBlocks");

            $drpBlocks.find('option').each(function() {
                var optionValue = $(this).val();
                //if (optionValue === "" || blockIdsArray.includes(optionValue) ) {
                if (optionValue === "" || $.inArray(optionValue, commonValues) !== -1 ) {
                    $(this).show();
                } else {
                    $(this).remove();
                }
            });
        }

        storeBlocks();

        $("#drpPoNumber").on("change", function() {return;
            var hdnGradeList = JSON.parse($("#hdnGradeList").val());
            var hdnPackingStyleList = JSON.parse($("#hdnPackingStyle").val());

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

                var packingstyleId = $("#drpPoNumber option:selected").data('packingstyleid').toString();
                var allowedPackingStyleId = packingstyleId.split(",");

                var listPackingStyle = "<option value=''>Select</option>";
                $.each(hdnPackingStyleList, function(index, val) {
                    if (allowedPackingStyleId.indexOf(val.PCKID) !== -1) {
                        listPackingStyle += '<option value="' + val.PCKID + '">' + val.PackingStyle + '</option>';
                    }
                });
                $("#drpPckgStyle").html("").html(listPackingStyle);

            } else {
                var listGrade = "<option value=''>Select</option>";
                $.each(hdnGradeList, function(index, val) {
                    listGrade += '<option value="' + val.value + '">' + val.Options + '</option>';
                });
                $("#drpGrade").html("").html(listGrade);

                var listPackingStyle = "<option value=''>Select</option>";
                $.each(hdnPackingStyleList, function(index, val) {
                    listPackingStyle += '<option value="' + val.PCKID + '">' + val.PackingStyle + '</option>';
                });
                $("#drpPckgStyle").html("").html(listPackingStyle);
            }
        });

        var productionPlant = <?php echo $ProductionPlant ?>;

        console.log('productionPlant' + productionPlant);
        if (productionPlant != 0) {
            console.log('productionPlant' + productionPlant);
            $("#drpStockBlgsTo").val($("[data-processorID='" + productionPlant + "']").val()).trigger('change');
        } else {
            $("#drpStockBlgsTo").val("").trigger('change');
        }
    });

    function storeBlocks() {
        var blocksList = JSON.parse($("#hdnBlocks").val());
        var PackagingStoreIdsArr = <?php echo json_encode(!is_array($PackagingStoreIds) ? explode(',',$PackagingStoreIds) :[]);?>;
       
        var coldStoreId = $("#drpColdStore");
        var selectBlocks = $(coldStoreId).find(':selected').data("blocks");

        if (selectBlocks) {
            var selectBlocksAry = selectBlocks.split(",");
            var commonValues = selectBlocksAry.filter(value => PackagingStoreIdsArr.includes(value));
            var listBlock = "<option value=''>Select</option>";
            $.each(blocksList, function(index, val) {
                //if (selectBlocksAry.indexOf(val.BlockID) !== -1) {
                if (commonValues.indexOf(val.BlockID) !== -1) {
                    listBlock += '<option value="' + val.BlockID + '">' + val.BlockName + '</option>';
                }
            });
            $("#drpBlocks").html("").html(listBlock);
        } else {
            $("#drpBlocks").html("<option value=''>Select</option>");
        }
    }

    function ShowList(pageNo, searchkey, loadAll) {

        $.ajax({
            url: 'Ajax/AjaxPackingStoreInward.php',
            type: 'POST',
            data: {
                coldStore: $("#drpColdStore").val(),
                stockBlgsTo: $("#drpStockBlgsTo").val(),
               // stockType: $("#drpStockType").val(),
                //stockType: 1,
                pckgStyle: $("#drpPckgStyle").val(),
                PoNumber: $("#drpPoNumber").val(),
                blocks: $("#drpBlocks").val(),
                group: $("#drpGroup").val(),
                pageNo: pageNo,
                searchKey: searchkey,
                loadAll: loadAll,
                methodType : 'GetPackingStoreDataByFilters'
            },
            success: function(data) {
                fnSuccess(data.HtmlRes);
                checkedlist();
                return false;
            }
        });
    }

    $(document).ready(function() {
        $('label[for="drpColdStore"]').text('Packing Store');
        $('#drpColdStore').each(function () {
            let optionCount = $(this).find('option').length;

            // If only one real option (excluding "Select")
            if (optionCount === 2) {
                $(this).prop('selectedIndex', 1).trigger('change');
            }
        });
       // $("#LoadToCons").prop("disabled", true);
        ShowList("1", "", "0");
    });

    $(document).on("click", "#btnMoreSearch", function() {
        var searchkey = $("#tblDataTable_filter input").val();
        if (searchkey.length > 0) {
            ShowList("1", $.trim(searchkey), "0"); 
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
        ShowList("1", "", "0");
    });

    $(document).on('click', '.AddToPallet', function(e) {
        e.preventDefault();

        var StockID = $(this).data('id');
        $('#Update').val(StockID);

        var r = confirm("Are You Sure Do You Want To Add To Existing Pallet ?");
        if (r == true) {
            $('#modalNewPalette').modal('show');

        } else {}
    });

    $(document).on('click', '.unplacePallate', function() {

        var bid = $(this).data('id');
        var stockid = $(this).data('stockid');
        var storeRackId = $(this).attr('data-storerackid');

        console.log('storeRackId ' + storeRackId);

        var r = confirm("Are You Sure Do You Want To Unplace Palette ?");
        if (r == true) {
            $.ajax({
                url: 'Ajax/AjaxAddStock.php',
                type: 'POST',
                data: {
                    BID: bid,
                    StockID: stockid,
                    storeRackId: storeRackId,
                    type: "PALLET_UNPLACE_FOR_PACKING_STORE"
                },
                success: function(data) {
                    data = JSON.parse(data);
                    console.log(data);
                    alert(data.msg);

                    $("#btnSearch").trigger("click");
                }
            });
        }
    });




$(document).on('click', '.shiftPallate', function() {


    var blocks = $("option:selected", this).attr("data-blockslist");
      //  var blockArray = blocks.split(',');
        var blockArray = Array();

        var blocksList = JSON.parse($("#hdnBlocks").val());

        var filteredArray = $.grep(blocksList, function(element) {
            return $.inArray(element.BlockID, blockArray) !== -1;
        });

        var listBlock = "<option value=''>Select</option>";
        $.each(filteredArray, function(index, val) {
            listBlock += '<option value="' + val.BlockID + '">' + val.BlockName + '</option>';
        });

        $("#drpBlockID").html(listBlock);
        $("#drpRackName").html("<option value=''>select</option>");

   // var storeID = $("#drpColdStores").val();
       // $("#drpStoreID").val(storeID).trigger('change');

    //    var listBlock = "<option value=''>Select</option>";
    //     listBlock += "<option value='1'>B1</option>";
    //     // $.each(filteredArray, function(index, val) {
    //     //     listBlock += '<option value="' + val.BlockID + '">' + val.BlockName + '</option>';
    //     // });

    //     $("#drpBlockID").html(listBlock);
    //     $("#drpRackName").html("<option value=''>select</option>");


        $('#modalNewPalette').modal('show');
    $("#aNewPlacePalette").trigger("click");
var bid = $(this).data('id');
var stockid = $(this).data('stockid');
var storeRackId = $(this).attr('data-storerackid');

console.log('storeRackId ' + storeRackId);

var r = confirm("Are You Sure Do You Want To Shift Palette ?");
// if (r == true) {
//     $.ajax({
//         url: 'Ajax/AjaxAddStock.php',
//         type: 'POST',
//         data: {
//             BID: bid,
//             StockID: stockid,
//             storeRackId: storeRackId,
//             type: "PALLET_UNPLACE"
//         },
//         success: function(data) {
//             data = JSON.parse(data);
//             console.log(data);
//             alert(data.msg);

//             $("#btnSearch").trigger("click");
//         }
//     });
// }
});



    $(document).on("change", "#drpBlockID", function() {
        var blockid = $(this).val();

        if (blockid != "") {
            $.ajax({
                url: 'Ajax/AjaxInward.php',
                type: 'POST',
                data: {
                    methodType: "getRackList",
                    blockID: $.trim($(this).val()),
                    storeID: $.trim($("#drpStoreID").val())
                },
                success: function(data) {
                    var listFloor = "<option value=''>select</option>";
                    $.each(data, function(index, val) {
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



    $('.drpSelectModal').select2({
        dropdownParent: $('#modalNewPalette')
    });

    $('.drpSelectModal').select2({
        dropdownParent: $('#modalAdd')
    });


    $(document).on("change", "#drpStoreID", function() {
        var blocksList = JSON.parse($("#hdnCSBlocks").val());
        $("#drpNoFloors").html("").html("<option value='' data-floors=''>Select</option>");

        var selectBlocks = $(this).find(':selected').attr('data-blockslist');
        var PackagingStoreIdsArr = <?php echo json_encode(!is_array($PackagingStoreIds) ? explode(',',$PackagingStoreIds) :[]);?>;
        if (selectBlocks) {

            var selectBlocksAry = selectBlocks.split(",");
            var commonValues = selectBlocksAry.filter(value => PackagingStoreIdsArr.includes(value));
            var listBlock = "<option value='' data-floors=''>Select</option>";
            $.each(blocksList, function(index, val) {
                if (commonValues.indexOf(val.BlockID) !== -1) {
                    listBlock += '<option value="' + val.BlockID + '" data-floors="' + val.Floors + '">' + val.BlockName + '</option>';
                }
            });
            // console.log(listBlock);
            $("#drpAddBlockID").html("").html(listBlock);
        } else {
            $('#drpAddBlockID, #drpAddRackName').select2('destroy');

            // $("#drpAddBlockID").val("");
            $("#drpAddBlockID").html("").html("<option value=''>Select</option>");
            $("#drpAddRackName").html("").html("<option value=''>Select</option>");

            $('#drpAddBlockID, #drpAddRackName').select2({
                dropdownParent: $('#modalAdd')
            });
        }

    })

    $(document).on("change", "#drpAddBlockID", function() {
        var blockid = $(this).val();
        var drpStoreID = $("#frmAddPopup #drpStoreID").val();

        $.ajax({
            url: 'Ajax/AjaxInward.php',
            type: 'POST',
            data: {
                methodType: "getRackList",
                blockID: $.trim($(this).val()),
                storeID: drpStoreID
            },
            success: function(data) {

                var listFloor = "<option value=''>Select</option>";
                $.each(data, function(index, val) {
                    if (val.IsPath != "1") {
                        if (val.Status == "1") {
                            listFloor += '<option value="' + val.RackID + '">' + val.RackName + " (Filled)" + '</option>';
                        } else {
                            listFloor += '<option value="' + val.RackID + '">' + val.RackName + '</option>';
                        }
                    }
                });

                $("#drpAddRackName").html(listFloor);

                return false;
            }
        });

    })


    $(document).on('click', '.aListAdd', function() {
        var sId = $(this).data('stockid');
        var storeid = $(this).closest("tr").find(".tdStoreName").data('storeid');
        var existingRackID = $(this).data('storerackid'); 

       // alert(existingRackID);

        $('#drpAddBlockID, #drpAddRackName').select2('destroy');

        $("#drpStoreID, #drpAddBlockID").val("");
        $("#drpAddRackName").html("").html("<option value=''>Select</option>");

        $('#drpStoreID, #drpAddBlockID, #drpAddRackName').select2({
            dropdownParent: $('#modalAdd')
        });

        if(existingRackID==0){
            var r = true;
            //var r = confirm("Are You Sure Do You Want To Add To Existing Palette ?");
            var isShifting=0; //false
        }else{
            var r = confirm("Are You Sure Do You Want To Shift Palette ?");
            var isShifting=1; // true
        }
       
        if (r == true) {
            $('#modalAdd').modal('show');
            $("#hdnStockId").val(sId);
            $("#hdnExistingId").val(existingRackID);
            $("#frmAddPopup #drpStoreID").val(storeid).trigger("change");
        } else {
            $("#hdnStockId").val("");
            $("#hdnExistingId").val("");
        }
    });

    $("#frmAddPopup").validate({

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
            drpAddBlockID: {
                required: true
            },
            drpAddRackName: {
                required: true
            },
        },
        errorPlacement: function() {
            return false; // suppresses error message text
        },
        submitHandler: function() {

            var hdnSid = $("#hdnStockId").val();
            var existingRackID = $("#hdnExistingId").val();

            $.ajax({
                type: 'POST',
                //url: 'Ajax/ajax-store-on-floor-stock.php',
                url: 'Ajax/AjaxPackingStoreInward.php',
                method: "post",
                data: {
                    methodType: "placePalette",
                   // methodType: "popupAdd",
                    stockID: hdnSid,
                    existingRackID:existingRackID,
                    rackID: $("#drpAddRackName").val(),
                    PostedUserId : "<?php echo !empty($_SESSION['UserID'])?$_SESSION['UserID']:'';?>"
                },
                success: function(data) {
                    if (data.Error) {
                        alert(data.Error);
                    } else {
                        alert(data.Msg);
                        $("#btnSearch").trigger("click");
                        $('#modalAdd').modal('hide');
                        $("#hdnStockId").val("");
                        $("#existingRackID").val("");
                    }
                }
            });

            return false;

        }
    });
    
    $(document).on("click", "input.rowCheck", function () {
        checkedlist();
        $("#checkall").prop("checked", false);
    });

    $(document).on("click", "#checkall", function () {
        if (this.checked) {
            $(".rowCheck").prop("checked", true);

        } else {
            $(".rowCheck").prop("checked", false);

        }
        checkedlist();
    });
    function checkedlist() {
        var selectedOptions = new Array();
        $("input.rowCheck:checked").each(function () {
            selectedOptions.push(this.value);
        });
        $('#hdnSelectedRow').val(selectedOptions);

        if ($('#hdnSelectedRow').val().length == 0) {
            $("#LoadToCons").prop("disabled", true);
        } else {
            $("#LoadToCons").prop("disabled", false);
        }
    }
    $(document).on('click', '#LoadToCons', function (e) {
        e.preventDefault();
        $("#myModal .modal-title").text('PO to Dummy Conversion / Transfer to Another PO');
        var RowCheckVal = $('#hdnSelectedRow').val();
        if(RowCheckVal){
            $.ajax({
                    url: 'Ajax/AjaxPackingStoreInward.php',
                    type: 'POST',
                    data: {
                        StockIds : RowCheckVal,
                        LoadAll : 1,
                        methodType : 'PoDummyTable'
                    },
                    success: function (data) {
                        $("#PopUpTable").html(data);
                        $('#myModal').modal('show');
                        //$('#myModal .modal-footer').hide('show');
                        //$("#Update").css("display", "none");
                        $("#Update").text('Save');
                        $('#drpSlabWeight').select2();
                        $('.packingType,.poNo').select2({
                            dropdownParent: $('#PopUpTable')
                        });
                        /* $('#drpReason').select2({
                            dropdownParent: $('#PopUpTable')
                        }); */
                    }
            })

        }
       
    })
    $("#myModal .btn.btn-danger").on("click", function () {
        $('#myModal').modal('hide');
    });
    $(document).on('input', '.AvlDummyQty', function () {
        let max = parseInt($(this).attr('max') || 0);
        let group = $(this).data('group');
        let value = parseInt($(this).val() || 0);
        let DamageQty = parseInt($(this).closest('td').next('td').find('.DamageQty').val() || 0)
        let IssuedQty = parseInt($(this).closest('td').prev('td').prev('td').prev('td').find('.IssuedQty').val() || 0)
        if($.inArray(group, ['Mcs', 'Pouches']) !== -1){
            value += DamageQty + IssuedQty;
        }
        if (value > max) {
            alert('Value Exceeded.');
            $(this).val('');
        }
        // value = parseInt($(this).val());
        if($.inArray(group, ['Mcs', 'Pouches']) !== -1){
            if(value == max){
                if(DamageQty == 0){
                    $(this).closest('td').next('td').find('.DamageQty').prop('readonly',true)
                }
                if(IssuedQty == 0){
                    $(this).closest('td').prev('td').prev('td').prev('td').find('.IssuedQty').prop('readonly',true)
                }
            }else{
                $(this).closest('td').next('td').find('.DamageQty').prop('readonly',false)
                $(this).closest('td').prev('td').prev('td').prev('td').find('.IssuedQty').prop('readonly',false)
            }
        }
    });
    $(document).on('input', '.DamageQty', function () {
        let max = parseInt($(this).attr('max') || 0);
        let group = $(this).data('group');
        let value = parseInt($(this).val() || 0);
        let DummyQty = parseInt($(this).closest('td').prev('td').find('.AvlDummyQty').val() || 0)
        let IssuedQty = parseInt($(this).closest('td').prev('td').prev('td').prev('td').prev('td').find('.IssuedQty').val() || 0)
        if($.inArray(group, ['Mcs', 'Pouches']) !== -1){
            value += DummyQty + IssuedQty;
        }
        if (value > max) {
            alert('Value Exceeded.');
            $(this).val('');
        }
        //value = parseInt($(this).val() || 0);
        if($.inArray(group, ['Mcs', 'Pouches']) !== -1){  
            if(value == max){
                if(DummyQty == 0){
                    $(this).closest('td').prev('td').find('.AvlDummyQty').prop('readonly',true)
                }
                if(IssuedQty == 0){
                    $(this).closest('td').prev('td').prev('td').prev('td').prev('td').find('.IssuedQty').prop('readonly',true)
                }
            }else{
                $(this).closest('td').prev('td').find('.AvlDummyQty').prop('readonly',false)
                $(this).closest('td').prev('td').prev('td').prev('td').prev('td').find('.IssuedQty').prop('readonly',false)
            }
        }
    });
    $(document).on('input', '.IssuedQty', function () {
        let max = parseInt($(this).attr('max') || 0);
        let group = $(this).data('group');
        let value = parseInt($(this).val() || 0);
        let DummyQty = parseInt($(this).closest('td').next('td').next('td').next('td').find('.AvlDummyQty').val() || 0)
        let DamageQty = parseInt($(this).closest('td').next('td').next('td').next('td').next('td').find('.DamageQty').val() || 0)
        if($.inArray(group, ['Mcs', 'Pouches']) !== -1){
            value += DummyQty + DamageQty;
        } 
        if (value > max) {
            alert('Value Exceeded.');
            $(this).val('');
        }
        //value = parseInt($(this).val() || 0);alert(value)
        if($.inArray(group, ['Mcs', 'Pouches']) !== -1){         
            if(value == max){
                if(DamageQty == 0){
                    $(this).closest('td').next('td').next('td').next('td').next('td').find('.DamageQty').prop('readonly',true)
                }
                if(DummyQty == 0){
                    $(this).closest('td').next('td').next('td').next('td').find('.AvlDummyQty').prop('readonly',true)
                }
            }else{
                $(this).closest('td').next('td').next('td').next('td').next('td').find('.DamageQty').prop('readonly',false)
                $(this).closest('td').next('td').next('td').next('td').find('.AvlDummyQty').prop('readonly',false)
            }
        }
    });
    $(document).on('click', '#Update', function () {
        let DummyObj = {}; let k = 0;
        var packingType = true; var Brand = true;
        $('.AvlDummyQty').each(function (i) {
            let IssuedQty = parseInt($('#IssuedQty'+i).val() || 0);
            if($('#drpPackingType'+i).val() == '' && IssuedQty == 0){
                $('#drpPackingType'+i).next('.select2-container')
                    .find('.select2-selection')
                    .css('border', '1px solid red');
                packingType = false;
                return;
            }else{
                $('#drpPackingType'+i).next('.select2-container')
                    .find('.select2-selection')
                    .css('border', '1px solid green');
            }
            if($('#drpPoNumber'+i).val()){
                if($('#drpBrand'+i).val() == ''){
                    $('#drpBrand'+i).next('.select2-container')
                    .find('.select2-selection')
                    .css('border', '1px solid red');
                    Brand = false;
                    return;
                }else{
                     $('#drpBrand'+i).next('.select2-container')
                    .find('.select2-selection')
                    .css('border', '1px solid green');
                }
            }
            if($(this).val()  && $(this).val() != 0){
                DummyObj[k] = {}; // initialize object first 
                DummyObj[k]['id'] = $(this).data('id');
                DummyObj[k]['val'] = $(this).val();
                DummyObj[k]['group'] = $(this).data('group');
                DummyObj[k]['PackingType'] =  $('#drpPackingType'+i).val();
                DummyObj[k]['PoNo'] =  $('#drpPoNumber'+i).val();
                DummyObj[k]['Brand'] =  $('#drpBrand'+i).length != 0 ? $('#drpBrand'+i).val() : '';
                k++;
            }           
        });
        if(!packingType  || !Brand){
            return;
        }
        //console.log(DummyObj);return;
        let DmageObj = {};let l = 0;
        $('.DamageQty').each(function (i) {
            if($(this).val() && $(this).val() != 0){
                DmageObj[l] = {}; // initialize object first            
                DmageObj[l]['id'] = $(this).data('id');
                DmageObj[l]['val'] = $(this).val();
                DmageObj[l]['group'] = $(this).data('group');
                l++;
            }
        });
        let IssuedQtyObj = {};let m = 0;
        $('.IssuedQty').each(function (i) {
            if($(this).val() && $(this).val() != 0){
                IssuedQtyObj[m] = {}; // initialize object first            
                IssuedQtyObj[m]['id'] = $(this).data('id');
                IssuedQtyObj[m]['val'] = $(this).val();
                IssuedQtyObj[m]['group'] = $(this).data('group');
                m++;
            }
        });
        //console.log(IssuedQtyObj);return;
        let MergedObj = {
            Dummy : DummyObj,
            Damage : DmageObj,
            IssuedQty : IssuedQtyObj,
            methodType : 'ConvertToDummy',
            PostedUser : "<?php echo !empty($_SESSION['UserID'])?$_SESSION['UserID']:'';?>"
        };
        //console.log(MergedObj);return;
        $.ajax({
                url: 'Ajax/AjaxPackingStoreInward.php',
                type: 'POST',
                data: MergedObj,
                success: function (data) {
                    ShowList("1", "", "0")
                    alert('Data Added/Updated Successfully');
                    $('#myModal').modal('hide');
                   //location.reload(); 
                },
                error:function(data){
                    console.log(data);
                }
        })
    })
    $(document).on('change', '.poNo', function () {
        var i = $.trim($(this).find(':selected').data('i'));
        let $td = $(this).closest('td');

        // REMOVE previously appended brand dropdown
        $td.find('.drpBrand').remove().select2('destroy');
        if($(this).val()){
            $.ajax({
                url: 'Ajax/AjaxPackingStoreInward.php',
                type: 'POST',
                data: {
                    methodType: "getBrandListByPoId",
                    Poid: $.trim($(this).val()),
                },
                success: function (data) {
                    var selected = '';
                    var list = `<select class="drpSelect form-control drpBrand select2" id="drpBrand`+i+`" name="drpBrand">
                                         <option value="">Select Brand</option>`;
                    if(data.Brands.length == 1){
                        selected = "selected='selected'";
                    }
                    $.each(data.Brands, function (index, val) {                        
                        list += '<option value="' + val.BrandID + '" '+selected+'>' + val.BrandName + '</option>';
                    });
                    list += '</select>';
                   // console.log(list);//return;
                    $('#drpPoNumber'+i).closest('td').append(list);

                    // initialize select2
                    $('#drpBrand' + i).select2();
                    return false;
                }
            });
        }
    })
    
    
</script>

</body>

</html>
