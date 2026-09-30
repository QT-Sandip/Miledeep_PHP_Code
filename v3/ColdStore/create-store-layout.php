<?php 

include_once $_SERVER['DOCUMENT_ROOT'] .'/config/global.php';
include_once ROOT.'config/api-call.php';

$api = API_COLDSTORE_DROPDOWNS;

$resList = CallAPI($api, "GET", "", "");
$resColdStores = $resList->Data->ColdStores;
$resBlocks = $resList->Data->Blocks;
$resFloors = $resList->Data->Floors;

// echo json_encode($resColdStores, JSON_PRETTY_PRINT);

?>

<!doctype html>
<html lang="en">

<head>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" i
ntegrity="sha512-z3gLpd7yknf1YoNbCzqRKc4qyor8gaKU1qmn+CShxbuBusANI9QpRohGBreCFkKxLhei6S9CQXFEbbKuqLg0DA==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <?php include_once ROOT . 'includes/head.php' ?>
    <style>
        #divStoreLayout {
            padding-bottom: 25px;
        }
        #divStoreLayout table{
            margin: 0 auto;
        }
        #divStoreLayout table td{
            padding: 4px 6px;
            border: 1px solid #e0e0e0;
        }
        #divStoreLayout table td input[type="text"] {
            width: 48px !important;
            padding: 2px;
            text-align: center;
            font-size: 12px;
            float: right;
        }
        .tdHeaderRow, .trHeaderCol{
            background-color: #e0e0e0;
        }


        .form-check-input{
            width: 20px;
            height: 20px
        ;
        }
        .form-check-input:checked {
            background-color: #089420;
            border-color: #089420;
        }
        #divStoreLayout table td {
            padding: 4px 6px;
            border: 1px solid #e0e0e0;
            background: #089420;
            color: #FFF;
        }
        #divStoreLayout table td input[type="text"] {
            background: #ffffff;
            color: #000000;
        }
        td.tdHeaderRow {
            min-width: 160px;
        }

        .tdHeaderRow, .trHeaderCol td {
            background-color: #FAFAFA !important;
            color: #333 !important;
        }
        .tdHeaderRow .form-check-input,
        .trHeaderCol .form-check-input{
            background-color: #046b00;
            border-color: #333333;
            width: 15px;
            height: 15px;
        }
        .tdbgbg-white{
            background: #FAFAFA !important;
            color:#333333 !important;
        }
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
                                <!-- <h4 class="card-title">Bootstrap Validation - Normal</h4> -->
                                <form id="frmCreateStoreLayout">
                                    <div class="row">

                                        <input type="hidden" id="hdnBlocks" value="<?php echo htmlspecialchars(json_encode($resBlocks), ENT_QUOTES, 'UTF-8'); ?>" />
                                        <input type="hidden" id="hdnFloors" value="<?php echo htmlspecialchars(json_encode($resFloors), ENT_QUOTES, 'UTF-8'); ?>" />

                                        <div class="col-md-2 mb-3">
                                            <label for="drpStoreID" class="form-label">Store ID <span style="color:red">*</span></label>
                                            <select class="form-control drpSelect" name="drpStoreID" id="drpStoreID">
                                                <option  value="">Select</option>
                                                <?php 
                                                    foreach($resColdStores as $coldStore) {
                                                        echo '<option value="'.$coldStore->StoreID.'" data-blockslist="'.$coldStore->StoreBlocks.'">'.$coldStore->StoreName.'</option>';
                                                    }
                                                ?>
                                            <select>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label for="drpBlockID" class="form-label">Block ID <span style="color:red">*</span></label>
                                            <select class="form-control drpSelect" name="drpBlockID" id="drpBlockID">
                                                <option value="">Select</option>
                                            <select>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label for="drpNoFloors" class="form-label">Floors<span style="color:red">*</span></label>
                                            <select class="form-control drpSelect" name="drpNoFloors" id="drpNoFloors">
                                                <option value="">Select</option>
                                            <select>
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <label for="txtNoRows" class="form-label">No. of Rows <span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtNoRows" name="txtNoRows" placeholder="Enter no. of rows" />
                                        </div>
                                        
                                        <div class="col-md-2 mb-3">
                                            <label for="txtNoCols" class="form-label">No. of Columns<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtNoCols" name="txtNoCols" placeholder="Enter no. of columns" />
                                        </div>
                                        <div class="col-md-2 mb-3">
                                            <div><label for="btnSubmit" class="form-label">&nbsp;</label></div>
                                            <div><button class="btn btn-primary" name="btnSubmit" id="btnSubmit" type="submit">Submit</button></div>
                                        </div>
                                        
                                    </div>
                                    <div>
                                        <p>Note: Leave the Textbox if you have a way to walk</p>
                                    </div>
                                    <!-- <div class="text-center">
                                        <a href="rm-arrival-list" class="btn btn-danger">Close</a>                                        
                                        <a href="javascript:void(0)" class="newItem btn btn-success waves-effect waves-light">
                                            <i class="fa fa-plus"></i> New
                                        </a>
                                        <button class="btn btn-primary" name="submit" id="submit"
                                            type="submit">Save</button>
                                    </div> -->
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

        <?php include_once ROOT . 'includes/footer.php' ?>

    </div>
    <!-- end main content-->

</div>
<!-- END layout-wrapper -->

<?php include_once ROOT . 'includes/common-js.php' ?>
<?php include_once WORKING_MODULE . 'modalSupplier.php' ?>
<?php include_once ROOT . '/includes/app-js.php' ?>

<script>

    // var norows = 10;
    // var nocols = 5;
    var jsonData = [];

    function jsonObject(norows, nocols) {

        jsonData = [];
        var currentRow = null;
        for(var r = 1; r <= norows; r++) {
            currentRow = {
                "Row": r,
                "Name": "test",
                "RowData": []
            };

            jsonData.push(currentRow);

            for(var c = 1; c <= nocols; c++) {
                currentRow.RowData.push({
                    "Name": $("#txt"+r+"-"+c).val(),
                    "RackID": $("#hdn"+r+"-"+c).val(),
                    "RackType": $("#chk"+r+"-"+c).is(':checked') ? "1" : "0"
                });
            }
        };

        // console.log(JSON.stringify(jsonData));
        return jsonData;

    }   


    // $(document).on("click","#btnSubmit", function() {

    //     $.ajax({
    //         type: 'POST',
    //         url: 'Ajax/AjaxCreateStoreLayout.php',
    //         method:"post",
    //         data: {
    //             storeID: $("#drpStoreID").val(),
    //             blockID: $("#drpBlockID").val(),
    //             floorsID:$("#drpNoFloors").val(),
    //             rows:$("#txtNoRows").val(),
    //             cols:$("#txtNoCols").val(),
    //         },
    //         success:function(data) {
    //             $("#divStoreLayout").html(data);
    //             // jsonObject();
    //         }
    //     });

    // });

    $(document).on("change", "#drpStoreID", function() {
        var blocksList = JSON.parse($("#hdnBlocks").val());
        $("#drpNoFloors").html("").html("<option value='' data-floors=''>Select</option>");

        // var storeid = $(this).val();        
        var selectBlocks = $(this).find(':selected').attr('data-blockslist');

        if(selectBlocks != "") {

            var selectBlocksAry = selectBlocks.split(",");

            var listBlock = "<option value='' data-floors=''>Select</option>";
            $.each(blocksList, function(index, val) {
                if(selectBlocksAry.indexOf(val.BlockID) !== -1) {
                    listBlock += '<option value="'+val.BlockID+'" data-floors="'+val.Floors+'">'+val.BlockName+'</option>';
                }
            });
            // console.log(listBlock);
            $("#drpBlockID").html("").html(listBlock);
        }

    })

    $(document).on("change", "#drpBlockID", function() {
        var floorList = JSON.parse($("#hdnFloors").val());

        // var blockid = $(this).val();
        var selectFloors = $(document).find("#drpBlockID").find(':selected').attr('data-floors');
        
        if(selectFloors != "") {

            var selectFloorsAry = selectFloors.split(",");

            var listFloor = "<option value=''>Select</option>";
            $.each(floorList, function(index, val) {
                if(selectFloorsAry.indexOf(val.FloorID) !== -1) {
                    listFloor += '<option value="'+val.FloorID+'">'+val.FloorName+'</option>';
                }
            });

            // console.log(listFloor);
            $("#drpNoFloors").html("").html(listFloor);
        } else {
            $("#drpNoFloors").html("").html("<option value=''>Select</option>");
        }
    })

    $(document).on("change", ".chkAllCol", function(){
        var colNo = $(this).attr("data-colno");
        console.log(colNo);

        if ($(this).prop('checked')==true) { 
            $(".chkCol"+colNo).prop("checked", true);
            $(".txtCol"+colNo).show();
            $(".iPathCol"+colNo).hide();
            // $(".dvCol"+colNo).show();
            // $(".dvPathCol"+colNo).hide();
            $(".tdCol"+colNo).removeClass("tdbgbg-white");
        } else {
            $(".chkCol"+colNo).prop("checked", false);
            $(".txtCol"+colNo).hide();
            $(".iPathCol"+colNo).show();
            // $(".dvCol"+colNo).hide();
            // $(".dvPathCol"+colNo).show();
            $(".tdCol"+colNo).addClass("tdbgbg-white");
        }	
        // jsonObject();
    });

    $(document).on("change", ".chkAllRow", function(){
        var rowNo = $(this).attr("data-rowno");
        console.log(rowNo);

        if ($(this).prop('checked')==true) { 
            $(".chkRow"+rowNo).prop("checked", true);
            $(".txtRow"+rowNo).show();
            $(".iPathRow"+rowNo).hide();
            // $(".dvRow"+rowNo).show();
            // $(".dvPathRow"+rowNo).hide();
            $(".tdRow"+rowNo).removeClass("tdbgbg-white");
        } else {
            $(".chkRow"+rowNo).prop("checked", false);
            $(".txtRow"+rowNo).hide();
            $(".iPathRow"+rowNo).show();
            // $(".dvRow"+rowNo).hide();
            // $(".dvPathRow"+rowNo).show();
            $(".tdRow"+rowNo).addClass("tdbgbg-white");
            
        }	
        // jsonObject();
    })

    // $(document).on("change keyup", ".txtCellName", function(){
    //     jsonObject();
    // });

    $(document).on("change", ".form-check-input", function(){
        var cellId = $(this).attr("data-cellid");
        if ($(this).prop('checked')==true) { 
            // $("#div"+cellId).show();
            // $("#path"+cellId).hide();
            $("#txt"+cellId).show();
            $(".iCell"+cellId).hide();
            $(".tdCell"+cellId).removeClass("tdbgbg-white");
            
        } else {
            // $("#div"+cellId).hide();
            // $("#path"+cellId).show();       
            $("#txt"+cellId).hide();
            $(".iCell"+cellId).show();
            $(".tdCell"+cellId).addClass("tdbgbg-white");
        }
        
    });

    jQuery.validator.addMethod(
        "noSpace",
        function(value, element) {
            return value == "" || value.trim().length != 0;
        },
        "No space please and don't leave it empty"
    );

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
            txtNoRows: {
                required: true,
                noSpace: true
            },
            txtNoCols: {
                required: true,
                noSpace: true
            },
        },
        errorPlacement: function() {
            return false; // suppresses error message text
        },
        submitHandler: function() {
            $.ajax({
                type: 'POST',
                url: 'Ajax/AjaxCreateStoreLayout.php',
                method:"post",
                data: {
                    storeID: $("#drpStoreID").val(),
                    blockID: $("#drpBlockID").val(),
                    floorsID:$("#drpNoFloors").val(),
                    rows:$("#txtNoRows").val(),
                    cols:$("#txtNoCols").val(),
                },
                success:function(data) {
                    $("#divStoreLayout").html(data);
                    // jsonObject();
                }
            });
            
            return false;

        }
        });

    $(document).on("click", "#btnSave", function(){
        var colCount = $("#hdnColCount").val();
        var rowCount = $("#hdnRowCount").val();
        
        // console.log(colCount, rowCount);

        var jsonBody = JSON.stringify(jsonObject(rowCount, colCount));

        // var jsonBody = jsonObject();
        console.log(jsonBody);

        $.ajax({
            type: 'POST',
            url: 'Ajax/AjaxCreateStoreLayout.php',
            method:"POST",
            data: {
                methodType: "put",
                jsonObj: jsonBody,
                storeID: $("#drpStoreID").val(),
                blockID: $("#drpBlockID").val(),
                floorsID:$("#drpNoFloors").val(),
            },
            success:function(data) {
                // $("#divStoreLayout").html(data);
                // jsonObject();
            }
        });
    });

    $(document).on("keyup", ".txtRow", function() {
        var rid = $(this).attr("data-rid");
        var rval = $(this).val();
        if(rval != "") {
            $(this).closest("tr").find(".form-check .txtCellName").val(rval);
        }

    })




    




</script>
</body>
</html>