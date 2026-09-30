<?php
session_start();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

// $api = API_COLDSTORE_DROPDOWNS;

$apiList = API_COLDSTORE_DROPDOWNS;
$resDropdowns = CallAPI($apiList, "GET", "", "");

$resVariety = $resDropdowns->Data->VarietyList;
$resAbstatus = $resDropdowns->Data->Abstatus;
$resStockBelongsTo = $resDropdowns->Data->StockBelongsTo;
$resCsTransactionTypes = $resDropdowns->Data->CsTransactionTypes;
$resGrade = $resDropdowns->Data->Grade;
$resFreezingType = $resDropdowns->Data->FreezingType;
$resSoaking = $resDropdowns->Data->Soaking;
$resProductCategory = $resDropdowns->Data->ProductCategory;
$resBrand = $resDropdowns->Data->Brand;
$resSpecies = $resDropdowns->Data->Species;
$resPoNumber = $resDropdowns->Data->PoNumberNew;
$resTypeProduct  = $resDropdowns->Data->TypeProduct;
$resPackingStyle = $resDropdowns->Data->PackingStyle;

$api = API_STOCK_OUT_DROPDOWNS;
$resDropdownsAPI = CallAPI($api, "GET", "", "");
$resColdStores = $resDropdownsAPI->Data->ColdStores;
$resVarietyList = $resDropdownsAPI->Data->Variety;

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
        
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <div class="card">
                            <div class="card-body">
                                <form id="frmStockOutSearch" role="form" method="post">
                                    <div class="row">

                                        <div class="col-md-3 mb-3">
                                            <label for="drpColdStore">Cold Stores</label>
                                            <select class="drpSelect form-control" id="drpColdStore" name="drpColdStore">
                                                <option value="">Select</option>
                                                <?php foreach ($resColdStores as $item) { ?>
                                                    <option value="<?php echo $item->StoreID ?>" data-plant="<?php echo $item->ProductionPlantID ?>"><?php echo $item->StoreName ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpStockBelongs" class="form-label">Stock Belongs To <span style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpStockBelongs" id="drpStockBelongs">
                                                <option value=""> Select</option>
                                                <?php foreach ($resStockBelongsTo as $item) { ?>
                                                    <option value="<?php echo $item->TenantID; ?>" data-processorID="<?php echo $item->ProcessorID; ?>">
                                                        <?php echo $item->TenantName; ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="form-group col-sm-3 mb-3">
                                            <label for="drpGroup form-control" class="form-label">Group<span style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpGroup" id="drpGroup">
                                                <option value=""> Select</option>
                                                <?php
                                                foreach ($resCsTransactionTypes as $item) {
                                                    if ($item->TransType == "1") {
                                                        echo "<option value='" . $item->TransCategory . "' data-transtypeid=" . $item->TransTypeID . ">" . $item->TransCategory . "</option>";
                                                    }
                                                }

                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpGrade" class="form-label">Grade</label>
                                            <select class="drpSelect form-control" name="drpGrade" id="drpGrade">
                                                <option value=""> Select</option>
                                                <?php foreach ($resGrade as $item) { ?>
                                                    <option value="<?php echo $item->value; ?>">
                                                        <?php echo $item->Options ?></option>
                                                <?php } ?>
                                            </select>
                                            <input type="hidden" id="hdnGradeList" value='<?php echo json_encode($resGrade) ?>' />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpFreezingType" class="form-label">Freezing Type</label>
                                            <select class="drpSelect form-control" name="drpFreezingType" id="drpFreezingType">
                                                <option value=""> Select</option>
                                                <?php foreach ($resFreezingType as $item) { ?>
                                                    <option value="<?php echo $item->FreezingTypeName; ?>">
                                                        <?php echo $item->FreezingTypeName ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpSoaking" class="form-label">Soaking</label>
                                            <select class="drpSelect form-control" name="drpSoaking" id="drpSoaking">
                                                <option value=""> Select</option>
                                                <?php foreach ($resSoaking as $item) { ?>
                                                    <option value="<?php echo $item->ShortCode; ?>">
                                                        <?php echo $item->TreatmentName ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpProductCategory" class="form-label">Product Category <span style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpProductCategory" id="drpProductCategory">
                                                <option value=""> Select</option>
                                                <?php foreach ($resProductCategory as $item) { ?>
                                                    <option value="<?php echo $item->Name; ?>">
                                                        <?php echo $item->Name ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="drpAbStatus" class="form-label">Ab Status <span style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpAbStatus" id="drpAbStatus">
                                                <!-- <option value=""> Select</option> -->
                                                <?php foreach ($resAbstatus as $item) { ?>
                                                    <option value="<?php echo $item->OptID; ?>">
                                                        <?php echo $item->Name ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>



                                        <div class="col-md-3 mb-3">
                                            <label for="txtVariety" class="form-label">Variety</label>
                                            <input type="input" class="form-control" id="txtVariety" name="txtVariety" placeholder="Click to Select Variety" readonly />
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label for="drpPoNumber">PO Number</label>
                                            <select class="drpSelect form-control" id="drpPoNumber" name="drpPoNumber">
                                                <option value="">Select</option>
                                                <?php foreach ($resPoNumber as $item) { ?>
                                                    <option value="<?php echo $item->PoID ?>" data-plantid="<?php echo $item->ProductionPlantID ?>" data-grade="<?php echo $item->Grade ?>" data-packingstyleid="<?php echo $item->PackingStyles ?>">
                                                        <?php echo $item->PoNo ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label for="drpPckgStyle">Packing Style</label>
                                            <select class="drpSelect form-control" id="drpPckgStyle" name="drpPckgStyle">
                                                <option value="">Select</option>
                                                <?php foreach ($resPackingStyle as $item) { ?>
                                                    <option value="<?php echo $item->PCKID ?>"><?php echo $item->PackingStyle ?></option>
                                                <?php } ?>
                                            </select>
                                            <input type="hidden" id="hdnPackingStyle" value='<?php echo json_encode($resPackingStyle) ?>' />

                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label for="drpPackingType">Packing Type</label>
                                            <select class="drpSelect form-control" name="drpPackingType" id="drpPackingType">
                                                <option value=""> Select</option>
                                                <option value="DUMMY"> DUMMY</option>
                                                <option value="MASTER"> MASTER</option>
                                            </select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="drpBrand">Brand</label>
                                            <select class="drpSelect form-control" id="drpBrand" name="drpBrand">
                                                <option value="">Select</option>
                                                <?php foreach ($resBrand as $item) { ?>
                                                    <option value="<?php echo $item->BrandID ?>"><?php echo $item->BrandName ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>
                                        <div class="col col-md-3 mb-2">
                                            <label>Type Product </label>
                                            <select class="form-control drpSelect" name="drpTypeProduct" id="drpTypeProduct">
                                                <option value="">All</option>
                                                <?php foreach ($resTypeProduct as $TypeProduct) { ?>
                                                    <option value="<?php echo $TypeProduct ?>"><?php echo $TypeProduct ?></option>
                                                <?php } ?>
                                                <select>
                                        </div>
                                        <div class="col-md-3 mb-3">
                                            <label for="drpSpecies">Species</label>
                                            <select class="drpSelect form-control" id="drpSpecies" name="drpSpecies">
                                                <option value="">Select</option>
                                                <?php foreach ($resSpecies as $item) { ?>
                                                    <option value="<?php echo $item->ShortCode ?>"><?php echo $item->SpeciesName ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-3 mb-3">
                                            <label for="drpVariety">Variety</label>
                                            <select class="drpSelect form-control" id="drpVariety" name="drpVariety">
                                                <option value="">Select</option>
                                                <?php foreach ($resVariety as $item) { ?>
                                                    <option value="<?php echo $item->ProductID  ?>"><?php echo $item->ShortName  ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col-md-3 mb-3 text-center mt-1">
                                            <!-- <input type="hidden" id="hdnSelectedRow" name="hdnSelectedRow" /> -->
                                            <label for="btnSearch" style="display: block;">&nbsp;</label>
                                            <button id="btnSearch" style="text-align:left" type="submit" class="btn btn-primary waves-effect waves-light">
                                                Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                                            </button>
                                            <button id="btnStockOut" disabled style="text-align:left" type="button" class="btn btn-success  waves-effect waves-light" data-bs-toggle="modal" data-bs-target="#ModalStockOut">
                                                Stock Out <i class="ri-arrow-right-line align-middle ml-2"></i>
                                            </button>
                                        </div>
                                    </div>
                                </form>

                            </div>
                        </div>

                        <!-- <div class="card">
                            <div class="card-body"> -->
                        <div id="divDataList"></div>
                        <!-- </div>
                        </div> -->

                    </div>
                </div>

            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->

        <div class="modal fade" id="ModalStockOut" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Stock Out Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <!--<form role="form" id="frmTransitType" onsubmit="return validateForm()" method="post">-->
                    <form role="form" id="frmStockOut" method="post">
                        <div class="modal-body">
                            <div class="row">
                                <table class="popTable table table-hover table-bordered dataTable no-footer">
                                    <thead>
                                        <th>ID</th>
                                        <th>Variety</th>
                                        <th>Grade</th>
                                        <th>MCS</th>
                                        <th>Loose</th>
                                        <th>Packing</th>
                                        <th style="width: 250px">Stock Out Date</th>
                                        <th style="width: 250px">Reason</th>
                                        <th style="width: 250px">Remarks</th>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <input type="hidden" id="hdnSelectedRow" name="hdnSelectedRow" />
                            <input type="hidden" id="hdnObjRowsSelected" name="hdnObjRowsSelected" />
                            <input type="hidden" id="hdnSumMCS" name="hdnSumMCS" />
                            <input type="hidden" id="hdnSumLooseBags" name="hdnSumLooseBags" />
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                            <button type="button" id="btnPopStockOut" name='btnPopStockOut' class="btn btn-primary">Stock Out</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>



        <div class="modal fade" id="modalVarietyList" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Variety List</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <!--<form role="form" id="frmTransitType" onsubmit="return validateForm()" method="post">-->
                    <div class="modal-body">
                        <div class="row">
                            <table class="popVarietyTable table table-hover table-bordered dataTable no-footer" style="width: 100%;">
                                <thead>
                                    <th>S.No</th>
                                    <th>Short Name</th>
                                    <th>Description</th>
                                    <th>Grade</th>
                                    <th>Brand Name</th>
                                    <th>Packing Style</th>
                                    <th>Buyer SUPC</th>
                                    <th>Production Plant </th>
                                    <th>Type</th>
                                    <!-- <th>Our SUPC</th> -->
                                    <th>Actions</th>
                                </thead>
                                <tbody>
                                    <?php
                                    $i = 1;
                                    foreach ($resVarietyList as $item) {
                                        echo '
                                            <tr data-varietyid="' . $item->VarietyID . '">
                                                <td>' . $i . '</td>
                                                <td>' . $item->ShortName . '</td>
                                                <td>' . $item->ProductDescription . '</td>
                                                <td>' . $item->Grade . '</td>                                        
                                                <td>' . $item->BrandName . '</td>
                                                <td>' . $item->PackingStyle . '</td>
                                                <td>' . $item->DeviSUPC . '</td>
                                                <td>' . $item->StoreName . '</td>
                                                <td>' . $item->TypeProduct . '</td>
                                                <td>
                                                    <button  class="btn btn-success selectVariety" data-id="' . $item->VarietyID . '" value="' . $item->Grade . ' ' . $item->ShortName . ' ' . $item->DeviSUPC . ' ' . $item->ProductDescription . '" style="padding:3px 15px">Select</button>
                                                </td>
                                            </tr>';
                                        $i++;
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>


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
            bInfo: false
        });
    }

    $(document).ready(function() {

        var productionPlant = <?php echo $ProductionPlant ?>;
        if (productionPlant != 0) {
            // $("#drpColdStore").prop('disabled', true);
            $("#drpColdStore").val($("[data-plant='" + productionPlant + "']").val()).trigger('change');

        } else {
            // $("#drpColdStore").prop('disabled', false);
            $("#drpColdStore").val("").trigger('change');
        }

        var $drpPoNumber = $("#drpPoNumber");

        $drpPoNumber.find('option').each(function() {
            var optionPlantId = $(this).data('plantid');

            var plantIdArray = String(optionPlantId).split(',');

            if (plantIdArray.includes(String(productionPlant)) || $(this).val() === "") {
                $(this).show();
            } else {
                $(this).remove();
            }
        });

        $("#drpPoNumber").on("change", function() {
            
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
        
        if (productionPlant != 0) {
            console.log('productionPlant' + productionPlant);
            $("#drpStockBelongs").val($("[data-processorID='" + productionPlant + "']").val()).trigger('change');
        } else {
            $("#drpStockBelongs").val("").trigger('change');
        }
    });

    var poptable;

    $(document).on('shown.bs.modal', '#modalVarietyList', function() {
        if ($.fn.dataTable.isDataTable('.popVarietyTable')) {
            poptable.destroy();
        }
        // poptable.columns.adjust().draw();
        poptable = $('.popVarietyTable').DataTable({
            scrollY: "600px",
            scrollX: true,
            sScrollXInner: "100%",
            paging: false,
            destroy: true,
            fixedColumns: true,
            scrollCollapse: true,
            bInfo: false,
            ordering: false
        });

    });


    $(document).ready(function() {


        function handleVarietyClick() {
            $('#modalVarietyList').modal('show');
        };

        $("#drpPoNumber").on("change", function() {
            var selectedValue = $(this).val();
            if (selectedValue != "") {
                $("#txtVariety").off("click", handleVarietyClick);
            } else {
                $("#txtVariety").on("click", handleVarietyClick);
            }
        });

        $("#txtVariety").on("click", function() {
            handleVarietyClick();
        });

        $(".selectVariety").on("click", function() {
            var selectedVariety = $(this).val();
            var selectedVarietyID = $(this).attr("data-id");
            $("#txtVariety").val(selectedVariety);
            $("#txtVariety").attr("data-id", selectedVarietyID);
            $('#modalVarietyList').modal('hide');

            $("#drpPoNumber").val('').trigger('change');
            $('#drpPoNumber').prop('disabled', true);
        });


    });

    $("#frmStockOutSearch").validate({

        onkeyup: false,
        onfocusout: false,
        invalidHandler: function(form, validator) {
            var errors = validator.numberOfInvalids();
            if (errors) {
                var firstInvalidElement = $(validator.errorList[0].element);
                firstInvalidElement.focus();
            }
        },
        ignore: function(index, element) {
            if (element.id === "drpPoNumber" && $(element).prop("disabled")) {
                return true;
            }
            if (element.id === "txtVariety" && !$(element).data('events')?.click) {
                return true;
            }
            return false;
        },
        rules: {
            txtVariety: {
                required: function(element) {
                    return $("#drpPoNumber").prop("disabled") === false;
                }
            }
        },
        errorPlacement: function() {
            return false; // suppresses error message text
        },
        submitHandler: function() {
            console.log($("#txtVariety").val() + $("#txtVariety").attr("data-id"));
            $.ajax({
                url: 'Ajax/AjaxStockOutNew.php',
                type: 'POST',
                data: {
                    "methodType": "getList",
                    "productionID": "",
                    "coldStore": $("#drpColdStore").val(),
                    "packingType": $("#drpPackingType").val(),
                    "variety": $("#txtVariety").attr("data-id"),
                    "poNumber": $("#drpPoNumber").val(),
                    "stockBelongsTo": $("#drpStockBelongsTo").val(),
                    "grade": $("#drpGrade").val(),
                    "freezingType": $("#drpFreezingType").val(),
                    "productcategory": $("#drpProductCategory").val(),
                    "soaking": $("#drpSoaking").val(),
                    "abStatus": $("#drpAbStatus").val(),
                    "group": $("#drpGroup").val(),
                    "brand": $("#drpBrand").val(),
                    "typeProduct": $("#drpTypeProduct").val(),
                    "species": $("#drpSpecies").val()
                },
                success: function(data) {
                    $("#divDataList").html(data);
                    fnShowDataTable();
                }
            });
            return false;
        }
    });

    function checkOptions(unCheckVal) {
        var selectedOptions = new Array();
        $("input.rowCheck:checked").each(function() {

            selectedOptions.push(this.value);
        });

        var hdnSelectedVal = $('#hdnSelectedRow').val();
        if (hdnSelectedVal != "") {
            var sArray = hdnSelectedVal.split(",");
            var combinedArray = sArray.concat(selectedOptions);

            var uniqueArray = [];
            $.each(combinedArray, function(index, value) {
                if ($.inArray(value, uniqueArray) === -1) {
                    uniqueArray.push(value);
                }
            });

            if (unCheckVal) {
                uniqueArray.splice(uniqueArray.indexOf(unCheckVal), 1);
            }

            var joinedArray = uniqueArray.join(",");

            $('#hdnSelectedRow').val(joinedArray);
        } else {
            $('#hdnSelectedRow').val(selectedOptions.join(","));
        }


        // $('#hdnSelectedRow').val(joinedArray);
        if (selectedOptions.length > 0) {
            $("#btnStockOut").prop("disabled", false);
        } else {
            $("#btnStockOut").prop("disabled", true);
        }
    }

    $(document).on("click", "input.rowCheck", function() {

        console.log($(this).val());
        var isChecked = $(this).prop('checked');

        if (isChecked) {
            checkOptions();
        } else {
            checkOptions($(this).val());
        }

        $("#checkall").prop("checked", false);

    });

    $(document).on("click", "#checkall", function() {
        if (this.checked) {
            $(".rowCheck").prop("checked", true);
            checkOptions();
        } else {
            $(".rowCheck").prop("checked", false);
            checkOptions();
        }
    });

    function fnToday() {
        var today = new Date();
        var dd = String(today.getDate()).padStart(2, '0'); // Ensure two-digit day
        var mm = String(today.getMonth() + 1).padStart(2, '0'); // January is 0!
        var yyyy = today.getFullYear();
        var todayDate = yyyy + '-' + mm + '-' + dd;

        return todayDate;
    }

    $("#btnStockOut").on("click", function() {
        var jsonObj = [];
        var sumMCS = 0;
        var sumLooseBags = 0;

        $("#tblDataTable tbody tr").each(function() {
            var checkbox = $(this).find(".rowCheck");

            if (checkbox.is(":checked")) {
                var hdnStockId = $(this).find(".hdnStockId").val();
                var hdnBarCodeID = $(this).find(".hdnBarCodeID").val();
                var hdnStoreID = $(this).find(".hdnStoreID").val();
                var hdnRackID = $(this).find(".hdnRackID").val();
                var hdnBlockID = $(this).find(".hdnBlockID").val();
                var txtMCS = $(this).find(".txtMCS").val();
                var txtLooseBags = $(this).find(".txtLooseBags").val();
                var prodid = $(this).find(".rowCheck").attr("data-prodid");

                sumMCS = parseInt(sumMCS) + parseInt(txtMCS);
                sumLooseBags = parseInt(sumLooseBags) + parseInt(txtLooseBags);

                jsonObj.push({
                    "StockID": hdnStockId,
                    "BarCodeID": hdnBarCodeID,
                    "StoreID": hdnStoreID,
                    "RackID": hdnRackID,
                    "OutMcs": txtMCS,
                    "OutLooseBags": txtLooseBags,
                    "BlockID": hdnBlockID,
                    "ProductionID": prodid
                });
            }

        });



        // console.log(jsonObj);
        // console.log(sumMCS);
        // console.log(sumLooseBags);

        $("#hdnSumMCS").val(sumMCS);
        $("#hdnSumLooseBags").val(sumLooseBags);
        $("#hdnObjRowsSelected").val(JSON.stringify(jsonObj));

        var tdVariety = $(".tdVariety").text();
        var tdGrade = $(".tdGrade").text();
        var tdPackingStyle = $(".tdPackingStyle").text();

        var today = fnToday();
        /* 
        var drpReasone = "<?php
                            // foreach($resCsTransactionTypes as $resCs) {
                            //     // if($resCs->TransType == "2") {
                            //     if($resCs->TransType == 2 or $resCs->TransType == 3) {
                            //         echo "<option value='".$resCs->TransCategory."' data-transtypeid=".$resCs->TransTypeID.">".$resCs->TransCategory."</option>";
                            //     }
                            // } 
                            ?>"; 
        */

        var popRow = '<tr>' +
            '<td style="padding-top: 15px;">1</td>' +
            '<td style="padding-top: 15px;">' + tdVariety + '</td>' +
            '<td style="padding-top: 15px;">' + tdGrade + '</td>' +
            '<td style="padding-top: 15px;">' + sumMCS + '</td>' +
            '<td style="padding-top: 15px;">' + sumLooseBags + '</td>' +
            '<td style="padding-top: 15px;">' + tdPackingStyle + '</td>' +
            '<td><div class="col-md-6 col-lg-3 mb-3" style="width: 100%"><input type="date" class="form-control" id="txtStockOutDate"  name="txtStockOutDate" value="' + today + '" /></div></td>' +
            '<td><div class="col-md-6 col-lg-3 mb-3" style="width:100%"><select class="form-control drpSelect" id="drpReason" name="drpReason"><?php foreach ($resCsTransactionTypes as $resCs) {
                                                                                                                                                    // if($resCs->TransType == "2") {
                                                                                                                                                    if ($resCs->TransType == 2 or $resCs->TransType == 3) {
                                                                                                                                                        echo '<option value="' . $resCs->TransCategory . '" data-transtypeid="' . $resCs->TransTypeID . '">' . $resCs->TransCategory . '</option>';
                                                                                                                                                    }
                                                                                                                                                } ?> </select></div></td>' +
            '<td><div class="col-md-6 col-lg-3 mb-3" style="width: 100%"><input type="text" class="form-control" id="txtRemarks" name="txtRemarks" value="" placeholder="Enter Remarks" /></div></td>' +
            '</tr>';
        $(".popTable tbody").html("").html(popRow);

        $('#drpReason').select2({
            dropdownParent: $('#ModalStockOut')
        });

        $(".popTable tbody").html("").html(popRow);

    });

    $("#btnPopStockOut").on("click", function() {

        var stockOutDate = $("#txtStockOutDate").val();
        var remarks = $("#txtRemarks").val();
        var totMsc = $("#hdnSumMCS").val();
        var totLooseBags = $("#hdnSumLooseBags").val();
        var reason = $("#drpReason").val();
        var jsonRowsSelected = JSON.parse($("#hdnObjRowsSelected").val());
        var selectedTransTypeId = $(document).find('#drpReason option:selected').data('transtypeid');

        // console.log(remarks);

        $.ajax({
            url: 'Ajax/AjaxStockOut.php',
            type: 'POST',
            data: {
                "methodType": "stockOut",
                "stockOutDate": stockOutDate,
                "stockOutReason": reason,
                "remarks": remarks,
                // "productionID":prdId,
                // "productionDate":$("#txtProductionDate").val(),
                "totalMsc": totMsc,
                "totalLooseBags": totLooseBags,
                "stockItems": jsonRowsSelected,
                "selectedTransTypeId": selectedTransTypeId
            },
            success: function(data) {
                if (data.Error) {
                    alert(data.Error);
                } else {
                    alert(data.Msg);
                    window.location.href = "stock-out";
                    // $('#ModalStockOut').modal().hide();

                    // $("#btnStockOut").prop("disabled", true);

                }

                console.log(data);
            }
        });


    });

    $(document).on("keyup", ".txtMCS", function() {
        var actualMsc = parseInt($(this).attr("data-mcs"));
        var enteredMsc = parseInt($(this).val());
        if (enteredMsc > actualMsc) {
            alert("MCS exceeds actual value.");
            $(this).val(actualMsc);
        }
    });

    $(document).on("keyup", ".txtLooseBags", function() {
        var actualBags = parseInt($(this).attr("data-bags"));
        var enteredBags = parseInt($(this).val());
        if (enteredBags > actualBags) {
            alert("MCS exceeds actual value.");
            $(this).val(actualBags);
        }
    })


    // });
</script>

</body>

</html>