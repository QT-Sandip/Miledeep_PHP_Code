<?php
session_start();
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_STORE_DROPDOWN;

$resDropdownsAPI = CallAPI($api, "GET", "", "");

$resPoNo = $resDropdownsAPI->Data->PoNo;
$resProPlants = $resDropdownsAPI->Data->ProcessingPlants;
$resChemical = $resDropdownsAPI->Data->ChemicalName;
$resPoChemicals = $resDropdownsAPI->Data->PoChemicals;

// echo json_encode($resPoChemicals, JSON_PRETTY_PRINT);
// exit;
// echo json_encode($resDropdownsAPI);
$apiList = API_GS_CHEM_PO_ORDERS;

$ChemPoID = trim($_GET['ChemPoID']);

$bodyContent = array(
    "ChemPoID" => $ChemPoID
);

if ($ChemPoID != '') {
    $result = CallAPI($apiList, "GET", "", $bodyContent);

    $Data = $result->Data[0];
}
// var_dump($result);


$CurrentMonth = date('m');
$CurrentYear = date('Y');

if ($CurrentMonth > 3) {
    $PastFY = $CurrentYear - 1;
    $CurrentFY = $CurrentYear;
} else {
    $PastFY = $CurrentYear - 2;
    $CurrentFY = $CurrentYear - 1;
}

$PlantID = $_SESSION['UnitID'];
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

                <?php if (isset($_SESSION['Msg'])) { ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo $Status ?>
                    </div>
                <?php $_SESSION['Error'] = null;
                } ?>

                <?php if (isset($_SESSION['Error'])) { ?>
                    <div class="alert alert-danger" role="alert">
                        <?php echo $Status ?>
                    </div>
                <?php $_SESSION['Msg'] = null;
                } ?>

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <div class="card">
                            <div class="card-body">
                                <form role="form" method="post" id="frmHlvaVariety">
                                    <div class="row">
                                        <input type="hidden" id="hdnChemicals" value="<?php echo htmlspecialchars(json_encode($resChemical), ENT_QUOTES, 'UTF-8'); ?>" />

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpPrPlnt">Processing Plant<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpPrPlnt" id="drpPrPlnt">
                                                <option value="">All</option>
                                                <?php foreach ($resProPlants as $List) { ?>
                                                    <option value="<?php echo $List->ProcessingStationID ?>">
                                                        <?php echo $List->StationName ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpPoNo">PoNo<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpPoNo" id="drpPoNo">
                                                <option value="" <?php echo ($Data->ChemPoNo == '') ? 'selected' : ''; ?>>
                                                    All</option>
                                                <?php foreach ($resPoChemicals as $List) { ?>
                                                    <option value="<?php echo $List->ChemPoID ?>" data-chemicalid="<?php echo $List->ChemicalID; ?>" <?php echo ($Data->ChemPoNo == $List->ChemPoNo) ? 'selected' : ''; ?>>
                                                        <?php echo $List->ChemPoNo ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <!-- <div class="col col-md-3 mb-3">
                                            <label for="drpChemical">Chemical<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpChemical" id="drpChemical">
                                                <option value="" <?php echo ($Data->ChemicalName == '') ? 'selected' : ''; ?>>Select</option>
                                                <?php foreach ($resChemical as $List) { ?>
                                                    <option value="<?php echo $List->ChemID ?>" <?php echo ($Data->ChemicalName == $List->ChemicalName) ? 'selected' : ''; ?>
                                                        data-chemicalqty="<?php echo $List->NoOfKgPerBag ?>">
                                                        <?php echo $List->ChemicalName ?>
                                                    </option>
                                                <?php } ?>
                                            </select>
                                        </div> -->

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpChemical">Chemical<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpChemical" id="drpChemical">
                                                <option value="">Select</option>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtBLNo" class="form-label">BL No<span
                                                    style="color:red"></span></label>
                                            <input type="text" class="form-control" id="txtBLNo" name="txtBLNo"
                                                placeholder="Enter BL No" />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtCtrNo" class="form-label">Container No<span
                                                    style="color:red"></span></label>
                                            <input type="text" class="form-control" id="txtCtrNo" name="txtCtrNo"
                                                placeholder="Enter Container No" />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtBags" class="form-label">Received Bags<span
                                                    style="color:red">*</span><span
                                                    id="txtBalanceMessage"></span></label>
                                            <input type="text" class="form-control" id="txtBags" name="txtBags"
                                                placeholder="Enter Received Bags" />
                                                 <input type="hidden" id="hdnBalanceBags" value="" />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtQty" class="form-label">Received Qty in KGs<span
                                                    style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtQty" name="txtQty"
                                                placeholder="Enter Quantity Received" readonly />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtBlEntryNo" class="form-label">Bill of Entry No<span
                                                    style="color:red"></span></label>
                                            <input type="text" class="form-control" id="txtBlEntryNo"
                                                name="txtBlEntryNo" placeholder="Enter Bill of Entry No" />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtBlDate" class="form-label">Bill of Entry Date<span
                                                    style="color:red"></span></label>
                                            <input type="date" class="form-control" id="txtBlDate" name="txtBlDate" />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtClrdDate" class="form-label">Cleared Date<span
                                                    style="color:red"></span></label>
                                            <input type="date" class="form-control" id="txtClrdDate"
                                                name="txtClrdDate" />
                                        </div>

                                        <div class="text-center">
                                            <input type="hidden" id="hdnSaveType" name="hdnSaveType"
                                                value="<?php echo $ReceivedID ?>" />
                                            <input type="hidden" id="hdnNewVariety" name="hdnNewVariety" />
                                            <a href="GsChemOrdersList.php" class="btn btn-danger">Close</a>
                                            <button class="btn btn-primary btnSave" name="submit"
                                                id="submit">Save</button>
                                        </div>

                                    </div>
                                </form>

                            </div>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div id="divDataList">

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
        function fnShowDataTable() {
            table = $('#tblDataTable').DataTable({
                scrollY: "600px",
                scrollX: true,
                sScrollXInner: "100%",
                paging: false,
                destroy: true,
                fixedColumns: true,
                scrollCollapse: true,
                dom: 'Blfrtip',
                buttons: [{
                    extend: 'excel',
                    text: 'Download Excel',
                    className: 'btnExcelDataTable',
                    title: 'Chemical Receiving List -<?php echo date("d-m-Y") ?>',
                    footer: true,
                    exportOptions: {
                        columns: ':visible'
                    }
                }],
            });
        }

        function ShowList() {
            $.ajax({
                url: 'Ajax/AjaxGsChemOrdersCreate.php',
                type: 'POST',
                data: {
                    methodType: "get",
                    chemPoID: $("#drpPoNo").val(),
                    plantID: $("#drpPrPlnt").val(),
                    ChemicalID: $("#drpChemical").val(),
                },
                success: function(data) {
                    $("#divDataList").html(data);
                    fnShowDataTable();
                    var balanceqty = $(".tdPoNo").data("balance");
                    console.log("balanceqty",balanceqty);
                    console.log(document);
                    if (balanceqty > 0) {
                        $("#txtBalanceMessage").text("(Balance Qty : " + (parseFloat(balanceqty) || 0).toFixed(3) + " Bags )").css("color", "green");
                    } else {
                        $("#txtBalanceMessage").text("(Balance Qty : " + (parseFloat(balanceqty) || 0).toFixed(3) + " Bags )").css("color", "red");
                    }
                    return false;
                }
            });
        };

        // $(document).on("change", "#drpPoNo", function() {
        //     // ShowList()
        // });
        
        $(document).on("change", "#drpPrPlnt", function() {
            // ShowList()
        });

        $(document).ready(function() {
            productionPlant();

            var plantID = $("#drpPrPlnt option:selected").val();
            console.log('plantID ' + plantID);
            var poNo = <?php echo $ChemPoID ? $ChemPoID : 0 ?>;
            if (poNo != 0) {
                $("#drpPoNo").val(poNo).trigger("change");
                $("#drpPoNo").prop('disabled', true);
                getPoChemicals();

            } else {
                // $("#drpPoNo").val("").trigger("change");
                $("#drpPoNo").prop('disabled', false);
            }

        });

        function productionPlant() {
            var productionPlant = <?php echo $PlantID ?>;
            if (productionPlant != 0) {
                $("#drpPrPlnt").prop('disabled', true);
                $("#drpPrPlnt").val(productionPlant).trigger('change');
            } else {
                $("#drpPrPlnt").prop('disabled', false);
                $("#drpPrPlnt").val("").trigger('change');
            }

        }

        $(document).on("change", "#drpPoNo", function() {
            ShowList();
            getPoChemicals();
            $("#txtBLNo, #txtCtrNo,#txtQty,#txtBlEntryNo,#txtBlDate,#txtClrdDate,#txtBags").val("");

        });

        function getTotalQty() {
            var bagQty = $("#drpChemical option:selected").data("chemicalqty");
            var noofBags = $("#txtBags").val();
            var totalQty = bagQty * noofBags;
            totalQty = totalQty ? totalQty : '';
            $("#txtQty").val(totalQty);
        }

        function getPoChemicals() {
            var chemicalList = JSON.parse($("#hdnChemicals").val());
            var selectChemicals = $("#drpPoNo").find(':selected').attr('data-chemicalid');
            if (selectChemicals != "") {

                var selectChemicalsAry = selectChemicals.split(",");
                var listBlock = "";
                $.each(chemicalList, function(index, val) {
                    if (selectChemicalsAry.indexOf(val.ChemID) !== -1) {
                        listBlock += '<option value="' + val.ChemID + '" data-chemicalqty="' + val.NoOfKgPerBag + '">' + val.ChemicalName + '</option>';
                    }
                });
                $("#drpChemical").html(listBlock).trigger("change");
            }
        }

        $(document).on("keyup", "#txtBags", function() {
            getTotalQty();
        });

        $(document).on("change", "#drpChemical", function() {
            ShowList();
            getTotalQty();
        });
        $("#frmHlvaVariety").validate({

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
                drpPoNo: {
                    required: true,
                },
                /* txtBLNo: {
                    required: true,
                },
                txtCtrNo: {
                    required: true,
                }, */
                drpChemical: {
                    required: true,
                },
                txtBags: {
                    required: true,
                },
                txtQty: {
                    required: true,
                },
                /* txtBlEntryNo: {
                    required: true,
                },
                txtBlDate: {
                    required: true,
                },
                txtClrdDate: {
                    required: true,
                }, */
                drpPrPlnt: {
                    required: true,
                },
            },
            errorPlacement: function() {
                return false;
            },
            submitHandler: function() {

                $.ajax({
                    url: 'Ajax/AjaxGsChemOrdersCreate.php',
                    type: 'POST',
                    data: {
                        methodType: "post",
                        poNo: $("#drpPoNo").val(),
                        blNo: $("#txtBLNo").val(),
                        ctrNo: $("#txtCtrNo").val(),
                        chemName: $("#drpChemical").val(),
                        bags: $("#txtBags").val(),
                        qty: $("#txtQty").val(),
                        blEntryNo: $("#txtBlEntryNo").val(),
                        blDate: $("#txtBlDate").val(),
                        clrdDate: $("#txtClrdDate").val(),
                        prPlnt: $("#drpPrPlnt").val(),
                        ChemicalID: $("#drpChemical").val(),
                    },
                    success: function(data) {
                        $("#divDataList").html(data);
                        ShowList();

                        $("#txtBLNo, #txtCtrNo,#txtQty,#txtBlEntryNo,#txtBlDate,#txtClrdDate,#txtBags").val("");
                        return false;
                    }
                });

                return false;
            }
        });


        $(document).on('click', '.btnDelete', function() {
            var receivedID = $(this).data('id');
            if (confirm('Are you sure you want to delete this record?')) {
                $.ajax({
                    url: 'Ajax/AjaxGsChemOrdersCreate.php',
                    type: 'POST',
                    data: {
                        methodType: "delete",
                        receivedID: receivedID,
                        chemPoID: $("#drpPoNo").val(),
                        plantID: $("#drpPrPlnt").val(),
                        ChemicalID: $("#drpChemical").val(),
                    },
                    success: function(data) {
                        $("#divDataList").html(data);
                        ShowList();
                    },
                });
            }
        });
    </script>

    </body>

</html>