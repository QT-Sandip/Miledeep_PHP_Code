<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
session_start();


include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$ProductionPlant = $_SESSION['UnitID'];

$api = API_COLDSTORE_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", "");

$resColdStores = $resDropdownsAPI->Data->ColdStores;

$apiList = API_IBT_REQUEST;

$IBTID = trim($_GET['IBTID']);

$bodyContent = array(
    "IBTID" => $IBTID,
);

if ($IBTID != '') {
    $result = CallAPI($apiList, "GET", "", $bodyContent);

    $Data = $result->Data[0];
}
// echo json_encode($result);

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
                                <form role="form" method="post" id="frmIbtRequest">
                                    <div class="row">

                                        <div class="col-md-3 mb-3">
                                            <label for="drpColdStore">From Store</label>
                                            <select class="drpSelect" id="drpColdStore" name="drpColdStore">
                                                <option value="">Select</option>
                                                <?php foreach ($resColdStores as $item) { ?>
                                                    <option value="<?php echo $item->StoreID ?>" data-plant="<?php echo $item->ProductionPlantID ?>" data-blocks="<?php echo $item->StoreBlocks ?>"><?php echo $item->StoreName ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtOrderDate" class="form-label">Order Date<span style="color:red">*</span></label>
                                            <input type="Date" class="form-control" id="txtOrderDate" name="txtOrderDate" value="<?php echo $Data->OrderDate; ?>" />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtOrderNum" class="form-label">Order Number<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtOrderNum" name="txtOrderNum" placeholder="Enter Order Number" value="<?php echo $Data->IBTOrderNumber ?>" />
                                            <input type="hidden" id="hdnAutoIBTNo" value="" />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpToStore">To Store<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpToStore" id="drpToStore">
                                                <option value="" <?php echo ($Data->ToStoreName == '') ? 'selected' : '';  ?>>Select</option>
                                                <?php foreach ($resColdStores as $List) { ?>
                                                    <?php if ($List->ProductionPlantID != $ProductionPlant) { ?>
                                                        <option value="<?php echo $List->StoreID ?>"
                                                            <?php echo ($Data->ToStoreName == $List->StoreName) ? 'selected' : ''; ?>>
                                                            <?php echo $List->StoreName ?></option>
                                                    <?php } ?>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtRemarks" class="form-label">Remarks</label>
                                            <input type="text" class="form-control" id="txtRemarks" name="txtRemarks" placeholder="Enter Remarks" value='<?php echo $Data->Remarks ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpStatus">Request Status<span style="color:red">*</span> </label>
                                            <select class="drpSelect" name="drpStatus" id="drpStatus">
                                                <?php if (isset($_GET['IBTID']) && !empty($_GET['IBTID'])) { ?>
                                                    <option value="1" <?php echo (($Data->RequestStatus == 1) ? 'selected' : '') ?>>Active</option>
                                                    <option value="0" <?php echo (($Data->RequestStatus == 0) ? 'selected' : '') ?>>In-Active</option>
                                                <?php } else { ?>

                                                    <option value="">All</option>
                                                    <option value="1" selected>Active</option>
                                                    <option value="0">In-Active</option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="text-center">
                                            <input type="hidden" id="hdnSaveType" name="hdnSaveType" value="<?php echo $IBTID ?>" />
                                            <input type="hidden" id="hdnNewVariety" name="hdnNewVariety" />
                                            <a href="CsIBTRequestList" class="btn btn-danger">Close</a>
                                            <?php if ($IBTID == '') { ?>
                                                <button class="btn btn-primary btnSave" name="submit" id="submit">Save</button>
                                            <?php } else { ?>
                                                <button class="btn btn-success btnSave" name="submit" id="submit">Update</button>
                                            <?php } ?>
                                        </div>

                                    </div>
                                </form>

                            </div>
                        </div>
                        <!-- <div class="card">
                            <div class="card-body">
                                <div id="divDataList">

                                </div>
                            </div>
                        </div> -->
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
                $("#drpColdStore").prop('disabled', true);
                $("#drpColdStore").val($("[data-plant='" + productionPlant + "']").val()).trigger('change');

            } else {
                $("#drpColdStore").prop('disabled', false);
                $("#drpColdStore").val("").trigger('change');
            }
            // getOrderNumber()
        });

        var ibtID = "<?php echo isset($_GET["IBTID"]) && !empty($_GET["IBTID"]) ? $_GET["IBTID"] : "" ?>";


        $(document).on('change', '#txtOrderDate', function() {
            getOrderNumber();
        });

        function getOrderNumber() {
            var productionPlant = <?php echo $ProductionPlant ?>;
            $.ajax({
                url: 'Ajax/AjaxCsIBTRequestEntryForm.php',
                type: 'POST',
                data: {
                    methodType: "getOrderNumber",
                    orderDate: $("#txtOrderDate").val(),
                    fromStore: $("#drpColdStore").val(),
                    plantID: productionPlant,
                    type: "getAutogeneratedIBTCode",
                },
                success: function(data) {
                    console.log(data);
                    $("#hdnAutoIBTNo").val(data.AutoIBTNo);
                    $("#txtOrderNum").val(data.IBTCode);
                    return false;
                }
            });
        }

        $("#frmIbtRequest").validate({

            
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
                txtOrderNum: {
                    required: true,
                },
                txtOrderDate: {
                    required: true,
                },
                drpColdStore: {
                    required: true,
                },
                drpToStore: {
                    required: true,
                },
                // status: {
                //     required: true,
                // },
                drpStatus: {
                    required: true,
                }
            },
            errorPlacement: function() {
                             return false;
            },
            submitHandler: function() {
                var productionPlant = <?php echo $ProductionPlant ?>;
                $.ajax({
                    url: 'Ajax/AjaxCsIBTRequestEntryForm.php',
                    type: 'POST',
                    data: {
                        methodType: "post",
                        ibtID: ibtID,
                        plantID: productionPlant,
                        autoIBTNo: $("#hdnAutoIBTNo").val(),
                        orderNum: $("#txtOrderNum").val(),
                        orderDate: $("#txtOrderDate").val(),
                        fromStore: $("#drpColdStore").val(),
                        toStore: $("#drpToStore").val(),
                        remarks: $("#txtRemarks").val(),
                        status: $("#drpStatus").val()
                    },
                    success: function(data) {
                        // console.log(data);
                        var list = data.Data;
                        if (data.Error != '') {
                            alert(data.Msg);
                            var addLink = list[0].AddLink;
                            // console.log(addLink);
                            window.location.href = addLink;
                        } else {
                            alert(data.Error);
                        }

                        return false;
                    }
                });

                return false;
            }
        });
    </script>

    </body>

</html>