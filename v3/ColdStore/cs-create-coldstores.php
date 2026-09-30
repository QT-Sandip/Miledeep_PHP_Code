<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_COLDSTORE_DROPDOWNS;
$excludeList = array("ColdStores","Floors","StoreRacks","Stock","StockBelongsTo","ProductGroup","Exporters","Consignee","Processor","Shifts","PackingType","FreezingType","Soaking","ProductCategory","Abstatus","OptType","OutType","Grade","Variety","VarietyList","StockAvailableVariety","StockInVarietyList","PoNumber","PackingStyle","Plant","GeneralCategories","Status","TenantType","ProductionLines","Brand","BrandType","Species","Tranction","TypeProduct","Type","CsTransactionTypes","ProductForm","Preparation","GlazeType","ShipoutPoNumber","PoNumberNew","TransactionTypesNew","InwardGroups");
$bodyContent = ["exclude_list" => $excludeList];
$resDropdownsAPI = CallAPI($api, "GET", "", $bodyContent);
$resBlocks = $resDropdownsAPI->Data->Blocks;
$resPlant = $resDropdownsAPI->Data->ProductionPlant;


$apiList = API_STORE_COLDSTORES;
$StoreID = isset($_GET["StoreID"]) && !empty($_GET["StoreID"]) ? $_GET["StoreID"] : "";

$bodyContent = array(
    "StoreID" => $StoreID
);
if ($StoreID != '') {
    $result = CallAPI($apiList, "GET", "", $bodyContent);
   //  var_dump($result);
    $Data = $result->Data[0];

    $StoreName = $Data->StoreName;
    $StoreAddress = $Data->StoreAddress;
    $StoreLocation = $Data->StoreLocation;
    $Incharge = $Data->Incharge;
    $StorePhoneNumber = $Data->StorePhoneNumber;
    $StoreGSTNumber = $Data->StoreGSTNumber;
    $StoreBlocks = explode(',', $Data->StoreBlocks);
    $ProductionPlantID = $Data->StationName;
    $StoreStatus = $Data->StoreStatus;
} else {
    $StoreName = "";
    $StoreAddress = "";
    $StoreLocation = "";
    $Incharge = "";
    $StorePhoneNumber = "";
    $StoreGSTNumber = "";
    $StoreBlocks = "";
    $ProductionPlantID = "";
    $StoreStatus = "";
}

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
                                <form role="form" method="post" id="frmCreateColdStore">
                                    <div class="row">

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtStoreName">Store Name<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtStoreName' id="txtStoreName" placeholder="Enter Store Name" 
                                             value="<?php echo $StoreName ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtStoreAddress">Store Address<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtStoreAddress' id="txtStoreAddress" placeholder="Enter Store Address" 
                                             value="<?php echo $StoreAddress ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtStoreLocation">Store Location<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtStoreLocation' id="txtStoreLocation" placeholder="Enter Store Location" 
                                             value="<?php echo $StoreLocation ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtInCharge">In Charge<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtInCharge' id="txtInCharge" placeholder="Enter In Charge" 
                                             value="<?php echo $Incharge ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtPhoneNumber">Phone Number<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtPhoneNumber' id="txtPhoneNumber" placeholder="Enter Phone Number" 
                                             value="<?php echo $StorePhoneNumber ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtGSTNumber">GST Number<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtGSTNumber' id="txtGSTNumber" placeholder="Enter GST Number" 
                                             value="<?php echo $StoreGSTNumber ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpBlocks">Blocks<span style="color:red">*</span></label>
                                            <select class="drpSelect form-control" name="drpBlocks" id="drpBlocks" multiple >
                                                <option value="">Select</option>
                                                <?php 
                                                    foreach($resBlocks as $item) {
                                                        if(in_array($item->BlockID, $StoreBlocks)) {
                                                            $selected = "selected";
                                                        } else {
                                                            $selected = "";
                                                        }
                                                        echo '<option value="'.$item->BlockID.'"'.$selected.'>'.$item->BlockName.'</option>';
                                                    }
                                                ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpProductionPlant">Production Plant<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpProductionPlant" id="drpProductionPlant">
                                                <option value="" <?php echo ($Data->StationName == '') ? 'selected' : '';  ?>>Select</option>
                                                <?php foreach ($resPlant as $item) { ?>
                                                    <option value="<?php echo $item->ProcessingStationID ?>" <?php echo ($Data->StationName == $item->StationName) ? 'selected' : ''; ?>> <?php echo $item->StationName ?></option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpStatus">Status<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpStatus" id="drpStatus">
                                                <?php if (isset($_GET['StoreID']) && !empty($_GET['StoreID'])) { ?>
                                                    <option value="1" <?php echo ($Data->StoreStatus == "1") ? 'selected' : ''; ?>>Active</option>
                                                    <option value="0" <?php echo ($Data->StoreStatus =="0") ? 'selected' : ''; ?>>In-Active</option>
                                                <?php } else { ?>
                                                    <option value="">Select</option>
                                                    <option value="1" selected>Active</option>
                                                    <option value="0">InActive</option>
                                                <?php } ?>
                                            </select>
                                        </div>


                                        <div class="text-center">
                                            <input type="hidden" id="hdnSaveType" name="hdnSaveType" value="<?php echo $StoreID ?>" />
                                            <input type="hidden" id="hdnNewVariety" name="hdnNewVariety" />
                                            <a href="cs-coldstores-list" class="btn btn-danger">Close</a>
                                            <?php if ($StoreID == '') { ?>
                                                <button class="btn btn-primary btnSave" name="submit" id="submit">Submit</button>
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
                
        var storeID = "<?php echo isset($_GET["StoreID"]) && !empty($_GET["StoreID"]) ? $_GET["StoreID"] : "" ?>";

        // function fnShowDataTable() {
        //     table = $('#tblDataTable').DataTable({
        //         scrollY: "600px",
        //         scrollX: true,
        //         sScrollXInner: "100%",
        //         paging: false,
        //         destroy: true,
        //         fixedColumns: true,
        //         scrollCollapse: true,
        //         dom: 'Blfrtip',
        //         // dom: '<"top-left-buttons"Bf>rt<"bottom"lip>',
        //         buttons: [{
        //             extend: 'excel',
        //             text: 'Download Excel',
        //             className: 'btnExcelDataTable',
        //             title: 'Agent List - <?php // echo date("d-m-Y") 
                                            ?>',
        //             footer: true,
        //             exportOptions: {
        //                 columns: ':visible'
        //             }
        //         }],
        //     });
        // }

        // $(document).ready(function() {
        //     $.ajax({
        //         url: 'Ajax/ajax-create-agent.php',
        //         type: 'POST',
        //         data: {
        //             methodType: "get",
        //             ID: id
        //         },
        //         success: function(data) {
        //             $("#divDataList").html(data);
        //             fnShowDataTable();
        //             return false;
        //         }
        //     });
        // });

        $(document).ready(function() {
            // $(".drpMultiSelect").select2();    
            $(".drpSelect").select2({
                width: "100%",
            });
        });

        jQuery.validator.addMethod(
            "noSpace",
            function(value, element) {
                return value == "" || value.trim().length != 0;
            },
            "No space please and don't leave it empty"
        );
        $("#frmCreateColdStore").validate({

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
                txtStoreName: {
                    required: true,
                    noSpace: true
                },
                txtStoreAddress: {
                    required: true,
                    noSpace: true
                },
                txtStoreLocation: {
                    required: true,
                    noSpace: true
                },
                txtInCharge: {
                    required: true,
                    noSpace: true
                },
                txtPhoneNumber: {
                    required: true,
                    noSpace: true
                },
                txtGSTNumber: {
                    required: true,
                    noSpace: true
                },
                drpBlocks: {
                    required: true
                },
                // drpProductionPlant: {
                //     required: true
                // },
                drpStatus: {
                    required: true
                },
            },
            errorPlacement: function() {
                return false; // suppresses error message text
            },
            submitHandler: function() {

                $.ajax({
                    url: 'Ajax/ajax-cs-create-coldstores.php',
                    type: 'POST',
                    data: {
                        // methodType: "post",
                        storeID: storeID,
                        storeName: $("#txtStoreName").val(),
                        storeAddress: $("#txtStoreAddress").val(),
                        storeLocation: $("#txtStoreLocation").val(),
                        inCharge: $("#txtInCharge").val(),
                        phoneNumber: $("#txtPhoneNumber").val(),
                        gstNumber: $("#txtGSTNumber").val(),
                        storeBlocks: $("#drpBlocks").val().join(","),
                        productionPlant: $("#drpProductionPlant").val(),
                        status: $("#drpStatus").val(),
                    },
                    success: function(data) {
                        console.log(data);
                        if (data.Error) {
                            alert(data.Error);
                        //   console.log(data.Error);
                        } else {
                            alert(data.Msg);
                            window.location.href = "cs-coldstores-list";
                        }
                        // $("#divDataList").html(data);
                        // fnShowDataTable();
                        return false;
                    }
                });

                return false;
            }
        });
    </script>

    </body>

</html>