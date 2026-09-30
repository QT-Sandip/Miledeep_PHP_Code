<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_COLDSTORE_DROPDOWNS;
$excludeList = array("ColdStores","Blocks","StoreRacks","Stock","StockBelongsTo","ProductGroup","Exporters","Consignee","Processor","Shifts","PackingType","FreezingType","Soaking","ProductCategory","Abstatus","OptType","OutType","Grade","Variety","VarietyList","StockAvailableVariety","StockInVarietyList","PoNumber","PackingStyle","Plant","GeneralCategories","Status","TenantType","ProductionLines","Brand","BrandType","Species","Tranction","TypeProduct","Type","CsTransactionTypes","ProductForm","Preparation","GlazeType","ShipoutPoNumber","ProductionPlant","PoNumberNew","TransactionTypesNew","InwardGroups");
$bodyContent = ["exclude_list" => $excludeList];
$resDropdownsAPI = CallAPI($api, "GET", "", $bodyContent);

$resFloors = $resDropdownsAPI->Data->Floors;
// $resBlocks = $resDropdownsAPI->Data->Blocks;

$BlockID = isset($_GET["BlockID"]) && !empty($_GET["BlockID"]) ? $_GET["BlockID"] : "";

if ($BlockID != '') {

    $api = API_BLOCK_DATA;

    $bodyContent = array(
        "BlockID" => $BlockID,
        "PageNo"=> "1",
        "SearchKey"=> "",
        "LoadAll"=> ""
    );

    $result = CallAPI($api, "GET", "", $bodyContent);

 //echo json_encode($result, JSON_PRETTY_PRINT);
    $Data = $result->Data[0];

    $BlockID = $Data->BlockID;
    $BlockName = $Data->BlockName;
    $FloorIDs = explode(',', $Data->FloorIDs);    
    $FloorName = $Data->FloorName;
    $Status = $Data->Status;
} else {
    $BlockID = "";
    $BlockName = "";
    $FloorIDs = "";
    $FloorName = "";
    $Status = "";
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
                                <form role="form" method="post" id="frmCreateGeneralOptions">
                                    <div class="row">

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtBlockName">Block Name<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtBlockName' id="txtBlockName" placeholder="Enter Block Name" <?php  echo (!empty($BlockID) ? "readonly" : "")?> value="<?php  echo $BlockName?>">
                                        </div>                                        

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpFloor">Floor<span style="color:red">*</span></label>
                                            <select class=" form-control" name="drpFloor" id="drpFloor" multiple>
                                                <option value="">Select</option>
                                                <?php 
                                                    foreach($resFloors as $item) {
                                                        if(in_array($item->FloorID, $FloorIDs)) {
                                                            $selected = "selected";
                                                        } else {
                                                            $selected = "";
                                                        }
                                                        echo '<option value="'.$item->FloorID.'"'.$selected.'>'.$item->FloorName.'</option>';
                                                    }
                                                ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="drpStatus">Status<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpStatus" id="drpStatus">
                                                <?php if (isset($_GET['BlockID']) && !empty($_GET['BlockID'])) { ?>
                                                    <option value="1" <?php echo ($Data->Status == "1") ? 'selected' : ''; ?>>Active</option>
                                                    <option value="0" <?php echo ($Data->Status == "0") ? 'selected' : ''; ?>>In-Active</option>
                                                <?php } else { ?>
                                                    <option value="">Select</option>
                                                    <option value="1" selected>Active</option>
                                                    <option value="0">InActive</option>
                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="text-center">
                                            <!-- <input type="hidden" id="hdnSaveType" name="hdnSaveType" value="<?php // echo $OptID ?>" />
                                            <input type="hidden" id="hdnNewVariety" name="hdnNewVariety" /> -->
                                            <a href="cs-block-list" class="btn btn-danger">Close</a>

                                            <?php  
                                                if ($BlockID == '') { 
                                                    echo '<button class="btn btn-primary btnSave" name="submit" id="submit">Submit</button>';
                                                } else {  
                                                    echo '<button class="btn btn-success btnSave" name="submit" id="submit">Update</button>';
                                                }
                                            ?>
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

        $(document).ready(function () {
            $("#drpFloor").select2();    
        });


        var blockID = "<?php echo isset($_GET["BlockID"]) && !empty($_GET["BlockID"]) ? $_GET["BlockID"] : "" ?>";

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
        //             title: 'Agent List - <?php // echo date("d-m-Y") ?>',
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

        jQuery.validator.addMethod(
            "noSpace",
            function(value, element) {
                return value == "" || value.trim().length != 0;
            },
            "No space please and don't leave it empty"
        );
        $("#frmCreateGeneralOptions").validate({

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
                txtBlockName: {
                    required: true,
                    noSpace: true
                },
                // drpBlockName: {
                //     required: true
                // },
                drpFloor: {
                    required: true
                },
                drpStatus: {
                    required: true
                },
            },
            errorPlacement: function() {
                return false; // suppresses error message text
            },
            submitHandler: function() {

                $.ajax({
                    url: 'Ajax/ajax-cs-create-block.php',
                    type: 'POST',
                    data: {
                        // methodType: "post",
                        blockID: blockID,
                        blockName: $("#txtBlockName").val(),
                        floor: $("#drpFloor").val().join(","),
                        status: $("#drpStatus").val(),
                    },
                    success: function(data) {
                        if (data.Error != '') {
                            alert(data.Msg);
                            window.location.href = "cs-block-list.php";
                        } else {
                            alert(data.Error);
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