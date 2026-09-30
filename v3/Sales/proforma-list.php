<?php
// session_start();
// $Token = $_SESSION['Token'];
// echo $Token;
// exit;
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
$AccessType = $_SESSION['AccessType'];
$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
$IsHidePoEditCondition = $ArrayClientConfigData['IsHidePoEditCondition'];
$filterExporter = "T";
$filterConsignee = "T";
$filterCountry = "T";
$filterStatus = "T";

$filterButtomProformaList = "T";

// echo $_SESSION["Token"];

$api = API_EXPORTS_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", "");

$resPlantsList = $resDropdownsAPI->Data->ProductionPlantsList;

// echo json_decode($resPlantsList, JSON_PRETTY_PRINT);
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
                <?php include_once WORKING_MODULE . '/includes/page-filter.php' ?>

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <div class="card">
                            <div class="card-body">
                                <!-- <div class="text-center">
                                            <button id="btnSearch" align="left" type="button" class="btn btn-primary waves-effect waves-light">
                                                Search <i class="ri-arrow-right-line align-middle ml-2"></i> 
                                            </button>
                                            <a href="/purchase/create-booking" class="btn btn-success waves-effect waves-light">
                                                <i class="fa fa-plus"></i>  New pi
                                            </a>  
                                            <button class="btn btn-warning waves-effect waves-light" data-toggle="popover" data-trigger="focus"
                                            data-id=" " id="BtnPopUpBulk" value=" " data-original-title="" title=""> Bulk Approval
                                            </button>      
                                        </div> -->

                                <div id="divDataList">
                                    <!-- -->
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
    <div class="modal fade" id="modalBulkApproval" tabindex="-1" aria-labelledby="modalBulkApproval" aria-hidden="true">
        <form id="frmBlukApprove" role="form" method="post">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Order Status Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col col-md-6">
                                <h6>Select PI:</h6>
                                <select class="mySelect2 " name="drpPIList" id="drpPIList" multiple></select>
                            </div>
                            <div class="col col-md-6">
                                <h6>Status:</h6>
                                <select class="mySelect2" id="drpStatus" name="drpStatus"></select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name='submit' id="btnBulkApprove" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </div>
        </form>
    </div>



    <div class="modal fade" id="modalPoStatus" tabindex="-1" aria-labelledby="modalPoStatus" aria-hidden="true">
        <form id="frmSingleApprove" role="form" method="post">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">PO Status Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">

                            <div class="col col-md-3" id="divPIList">
                                <h6>Select Status:</h6>
                                <select class="mySelect3" name="drpSinglePIList" id="drpSinglePIList">
                                    <option value="">Select Status</option>
                                    <option value="1">Approved</option>
                                    <option value="5">Po Received</option>
                                    <!-- <option value="4">Rejected</option> -->
                                    <option value="2">Cancelled</option>
                                </select>
                            </div>

                            <div class="col col-md-3" id="divPoNo">
                                <h6>Po No</h6>
                                <input type="text" class="form-control" id="txtPoNo" name="txtPoNo" />
                            </div>
                            <div class="col col-md-3" id="divPoDate">
                                <h6>Po Date</h6>
                                <input type="date" class="form-control" id="txtPoDate" name="txtPoDate" max="<?php echo date('Y-m-d'); ?>" />
                            </div>


                            <div class="col col-md-3" id="divRemarks">
                                <h6>Remarks</h6>
                                <input type="text" class="form-control" id="txtRemarks" name="txtRemarks" />
                            </div>

                            <div class="col col-md-3" id="divProdPlant">
                                <h6>Production Plant:</h6>
                                <select class="mySelect3" name="drpProdPlant" id="drpProdPlant" multiple="multiple">
                                    <option value="" >Select Plant</option>
                                    <?php foreach ($resPlantsList as $item) { ?>
                                        <option value="<?php echo $item->ProcessingStationID ?>"><?php echo $item->StationName ?></option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" id="hdnOrderID" />
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name='submit' id="btnBulkApprove" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </div>
        </form>
    </div>



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
        $('.mySelect2').select2({
            dropdownParent: $('#modalBulkApproval .modal-content')
        });

        $('.mySelect3').select2({
            dropdownParent: $('#modalPoStatus .modal-content')
        });
    })

    function dateConvert(date) {
        // Original date in "dd-mm-yyyy" format
        var originalDate = date;

        // Split the date string into day, month, and year components
        var dateComponents = originalDate.split("-");
        var day = dateComponents[0];
        var month = dateComponents[1];
        var year = dateComponents[2];

        // Create a new date string in "yyyy-mm-dd" format
        return convertedDate = year + "-" + month + "-" + day;
    }

    $(document).on("click", ".btnPIStatus", function() {

        var accessType = "<?php echo $AccessType;?>";
        var IsHidePoEditCondition = "<?php echo $IsHidePoEditCondition;?>";
        var PIStatusTxt =  $(this).text().trim();
        if(IsHidePoEditCondition==0 || (IsHidePoEditCondition==1 && accessType == 3 && PIStatusTxt =='PI-Pending') || (IsHidePoEditCondition==1 && accessType < 3 )){
            $("#modalPoStatus").modal("show");
            var poid = $(this).attr("data-poid");
            var poStatus = $.trim($(this).text());
            $("#hdnOrderID").val(poid);

            $.ajax({
                url: 'Ajax/AjaxProformaListActions.php',
                type: 'POST',
                data: {
                    Action: "GetPoDetails",
                    POID: poid,
                },
                success: function(data) {
                console.log(data);

                    if (typeof data.Data[0] !== "undefined") {
                        console.log(data.Data[0]);
                        var res = data.Data[0];
                        var d = dateConvert(res.PoDate);

                        var productionPlantID = res.ProductionPlantID;
                        console.log('productionPlantID'+productionPlantID);
                        var productionPlantIDs = productionPlantID.split(',').map(Number);
                        
                        $('#drpProdPlant option').each(function() {
                            if (productionPlantIDs.includes(parseInt($(this).val()))) {
                                $(this).prop('selected', true).trigger('change');
                            }
                        });

                        $('#drpSinglePIList').val(res.Status).trigger('change');

                        $("#txtPoNo").val(res.PoNo);
                        $("#txtPoDate").val(d);
                        $("#txtRemarks").val(res.Remarks);

                    } else {
                        $('#drpSinglePIList, #drpProdPlant').val("").trigger("change");
                        $('#drpSinglePIList, #txtPoNo, #txtPoDate, #txtRemarks').val("");
                    }

                    return false;
                }
            });
        }
    });
</script>

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
            language: {
                            lengthMenu: "Show _MENU_ records",
                            paginate: {
                                previous: "<i class='mdi mdi-chevron-left'>",
                                next: "<i class='mdi mdi-chevron-right'>"
                            }
                        },
                        drawCallback: function () {
                            $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
                        },
                        dom: '<"row mb-2"<"col-md-6 d-flex align-items-center"lf><"col-md-6 d-flex justify-content-end"B>>rtip',

                    buttons: [],

            // dom: '<"top"i>rt<"bottom"lp><"clear">'
        });
    }

    function fnSuccess(data) {

        $("#divDataList").html(data);
        fnShowDataTable();

        $("<button class='btn btn-primary' id='btnMoreSearch' style='padding: 3px 5px;margin-left: 10px;'>More Search</button>" +
                "<button class='btn btn-primary' id='btnLoadAll' style='padding: 3px 5px;margin-left: 10px;'>Load All</button>"
            )
            .insertAfter("#tblDataTable_filter label");

        // var totalCount = $(document).find("#hdnTotalRecords").val();
        // console.log(totalCount);

        // $('<div class="dataTables_info" id="tblDataTable_info"> &nbsp;out of a Total of <b>'+totalCount+'</b> records</div>').insertAfter("#tblDataTable_info");  

        $('html, body').animate({
            scrollTop: $("#divDataList").offset().top - 50
        }, 10);
    }

    function ShowList(pageNo, searchkey, loadAll) {
        $.ajax({
            url: 'Ajax/AjaxProformaList.php',
            type: 'POST',
            data: {
                ExporterID: $("#drpExporterList").val(),
                ConsigneeID: $("#drpConsigneeList").val(),
                CountryID: $("#drpCountryList").val(),
                Status: $("#drpShimpmentStatuscodelist").val(),
                PageNo: pageNo,
                SearchKey: searchkey,
                LoadAll: loadAll,
            },
            success: function(data) {
                fnSuccess(data);
                return false;
            }
        });
    }

    function fnProformaActions(id, action, pNo) {
        $.ajax({
            url: 'Ajax/AjaxProformaListActions.php',
            type: 'POST',
            data: {
                Action: action,
                ID: id,
            },
            success: function(data) {
                console.log(data);

                var msg;
                if (data.Msg != "" || data.Msg === "undefined") {
                    msg = data.Msg;
                } else {
                    msg = "NO MSG";
                }

                if (msg) {
                    ShowList(pNo, "", "0");
                    if (action == "Duplicate") {
                        fnShowModalConfirmation("Success", msg, "OK", "", "", "F");
                    } else {
                        fnShowModalConfirmation("Success", data.Msg, "OK", "", "", "F");
                    }
                } else {
                    fnShowModalConfirmation("Error", data.Error, "Cancel", "", "", "F");
                }
                return false;
            }
        });
    }

    $(document).ready(function() {
        ShowList("1", "", "0");
    });

    $("#btnSearch").on("click", function() {
        ShowList("1", "", "0");
    })

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

    $(document).on("click", ".btnDelete", function() {
        var selectedID = $(this).attr("data-id");
        $("#hdnActionType").val("delete");
        fnShowModalConfirmation("Confirmation", "Are you sure you want to delete the item ?", "Cancel", "Continue",
            selectedID, "T");
        // fnShowModalConfirmation("Success", "Successfully Updated !", "Cancel", "", "", "T);
        // fnShowModalConfirmation("Error", "Something error has occurred", "Cancel", "", "", "T);
    })

    /* Delete */
    $(document).on("click", "#btnModalContinue", function() {
        var selectedID = $("#hdnSelectedData").val();
        var pNo = $(".active.btnPageno").attr("data-pageno");

        if ($("#hdnActionType").val() == "delete") {
            fnProformaActions(selectedID, "Delete", pNo);
        } else {
            fnProformaActions(selectedID, "Duplicate", pNo);
        }

    });

    $(document).on("click", ".btnDuplicate", function() {
        var selectedID = $(this).attr("data-id");
        $("#hdnActionType").val("duplicate");

        console.log(selectedID);

        fnShowModalConfirmation("Confirmation", "Are you sure you want to copy the item ?", "Cancel", "Continue",
            selectedID, "T");

    });



    $(document).on("click", "#BtnPopUpBulk", function() {
        $.ajax({
            url: 'Ajax/AjaxProformaListActions.php',
            type: 'POST',
            data: {
                Action: "Get"
            },
            success: function(data) {
                // console.log(data.Status);
                var list = '<option value=""> Select </option>';
                data.Status.forEach(function(arrayItem) {
                    list += '<option value="' + arrayItem.StatusID + '">' + arrayItem
                        .StatusName + '</option>';
                });
                $("#drpStatus").html(list).trigger("change");

                var PiList = '<option value=""> Select </option>';
                data.Orders.forEach(function(item) {
                    PiList += '<option value="' + item.PoID + '">' + item.InvNumFull +
                        '</option>';
                });
                $("#drpPIList").html(PiList).trigger("change");

                return false;
            }
        });
    })

    $(document).ready(function() {
        $("#frmBlukApprove").validate({
            onkeyup: false,
            onfocusout: false,
            invalidHandler: function(form, validator) {
                var errors = validator.numberOfInvalids();
                if (errors) {
                    var firstInvalidElement = $(validator.errorList[0].element);
                    //$('html,body').scrollTop(firstInvalidElement.offset().top - 100);
                    firstInvalidElement.focus();
                }
            },
            ignore: "",
            rules: {
                drpPIList: {
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
                var poId = $("#drpPIList").val();
                var status = $("#drpStatus").val();
                $.ajax({
                    url: 'Ajax/AjaxProformaListActions.php',
                    type: 'POST',
                    data: {
                        Action: "Put",
                        Status: status,
                        OrderIDs: poId
                    },
                    success: function(data) {
                        if (data.Error == null) {
                            $("#modalBulkApproval").modal('hide');
                            fnShowModalConfirmation("Success", data.Msg, "OK", "", "", "T");
                        } else {
                            $("#modalBulkApproval").modal('hide');
                            fnShowModalConfirmation("Error", data.Error, "Cancel", "", "",
                                "T");
                        }
                        ShowList("1", "", "0");
                        return false;
                    }
                });
                return false;
                // return true;
            }
        });




        $("#frmSingleApprove").validate({
            onkeyup: false,
            onfocusout: false,
            invalidHandler: function(form, validator) {
                var errors = validator.numberOfInvalids();
                if (errors) {
                    var firstInvalidElement = $(validator.errorList[0].element);
                    //$('html,body').scrollTop(firstInvalidElement.offset().top - 100);
                    firstInvalidElement.focus();
                }
            },
            ignore: "",
            rules: {
                drpSinglePIList: {
                    required: true
                },
                txtPoNo: {
                    required: {
                        depends: function() {
                            return $('#drpSinglePIList').val() === '5';
                        }
                    },
                },
                txtPoDate: {
                    required: {
                        depends: function() {
                            return $('#drpSinglePIList').val() === '5';
                        }
                    },
                }
            },
            errorPlacement: function() {
                return false; // suppresses error message text
            },
            submitHandler: function() {

                var drpPIList = $("#drpSinglePIList").val();
                var orderId = $("#hdnOrderID").val();
                var txtPoNo = $("#txtPoNo").val();
                var txtPoDate = $("#txtPoDate").val();
                var txtRemarks = $("#txtRemarks").val();
                // var prodPlant = $("#drpProdPlant").val();
                var prodPlant = $('#drpProdPlant').val();
                prodPlant = prodPlant.join(",");
                // console.log(drpPIList,txtPoNo,txtPoDate,txtRemarks,orderId);

                $.ajax({
                    url: 'Ajax/AjaxProformaListActions.php',
                    type: 'POST',
                    data: {
                        Action: "SingleApprove",
                        Status: drpPIList,
                        OrderId: orderId,
                        PoNumber: txtPoNo,
                        PoDate: txtPoDate,
                        Remarks: txtRemarks,
                        ProdPlant: prodPlant
                    },
                    success: function(data) {
                        console.log(data);
                        if (data.Error == null) {
                            var pNo = $(".active.btnPageno").attr("data-pageno");

                            $("#modalPoStatus").modal('hide');
                            fnShowModalConfirmation("Success", data.Msg, "OK", "", "", "T");
                            ShowList(pNo, "", "0");
                        } else {
                            $("#modalPoStatus").modal('hide');
                            fnShowModalConfirmation("Error", data.Error, "Cancel", "", "",
                                "T");
                        }

                        return false;
                    }
                });

                return false;
            }
        });


    });
</script>

</body>

</html>
