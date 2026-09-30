<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$filterExporter = "T";
$filterConsignee = "T";
$filterCountry = "T";
// $filterInvoiceStatus = "T";
// $filterCInvoiceStatus = "T";
$filterCustomsStatus = "T";
$filterYear = "T";

// $filterButButton = "T";
$filterSideButton = "T";


$api = API_EXPORTS_DROPDOWNS;

$resDropDown = CallAPI($api, "GET", "", "");

$statusList = $resDropDown->Data->CustomsCancelStatus;
// echo json_encode($resDropDown, JSON_PRETTY_PRINT);
// exit;


?>

<!doctype html>
<html lang="en">

<head>

    <?php include_once ROOT . '/includes/head.php' ?>
    <style>
    #tblDataTable td:nth-child(9) {white-space: nowrap;}
        <?php
        echo   $url = $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

        if (strpos($url, "QualityControl") !== false) {
            echo '
                    a.btnActions[title="Excel"],
                    .btnDuplicate,
                    .btnViewDocRpt,
                    .qc-hide{ 
                        display: none;
                    }
                ';
            $qc = "T";
        } else {
            $qc = "F";
        }
        ?>
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
                <?php include_once ROOT . 'v3/Sales/includes/page-filter.php' ?>

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <div class="card">
                            <div class="card-body">
                                <!--  <div class="text-center">
                                        <button id="btnSearch" align="left" type="button" class="btn btn-primary waves-effect waves-light">
                                              Search <i class="ri-arrow-right-line align-middle ml-2"></i> 
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

    <div class="modal fade" id="modalDocView" tabindex="-1" role="dialog" aria-labelledby="staticBackdropLabel" aria-hidden="true">
        <div class="modal-dialog " role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Available Reports <span class="spanInvNo"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="CreateDocID"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalPoStatus" tabindex="-1" aria-labelledby="modalPoStatus" aria-hidden="true">
        <form id="frmPOStatusDetails" role="form">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">PO Status Details</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">

                            <div class="col col-md-6" id="divPIList">
                                <h6>Select Status:</h6>
                                <select class="mySelect3" name="drpPoStatus" id="drpPoStatus">
                                    <option value="">Select Status</option>
                                    <?php
                                    foreach ($statusList as $item) {
                                        echo '<option value="' . $item->Name . '">' . $item->Name . '</option>';
                                    }

                                    ?>
                                    <!-- <option value="1">Approved</option>
                                    <option value="5">Po Received</option>
                                    <option value="2">Cancelled</option>
                                    <option value="4">Rejected</option> -->
                                </select>
                            </div>
                            <!-- <div class="col col-md-3" id="divPoNo">
                                <h6>Po No</h6>
                                <input type="text" class="form-control" id="txtPoNo" name="txtPoNo" />
                            </div> 
                            <div class="col col-md-3" id="divPoDate">
                                <h6>Po Date</h6>
                                <input type="date" class="form-control" id="txtPoDate" name="txtPoDate" />
                            </div> -->
                            <div class="col col-md-6" id="divRemarks">
                                <h6>Remarks</h6>
                                <input type="text" class="form-control" id="txtRemarks" name="txtRemarks" />
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <input type="hidden" id="hdnPoID" value="34">
                        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                        <button type="submit" name="submit" id="btnPOStatusDetails" class="btn btn-primary">Submit</button>
                    </div>
                </div>
            </div>
        </form>
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
            buttons:[]

            });
        }
        // <script>
        $(document).ready(function() {
            $('.mySelect3').select2({
                dropdownParent: $('#modalPoStatus .modal-content')
            });
        })
        $(document).on("click", ".btnPIStatus", function() {
            var popupStatus = $(this).attr("data-popupstatus");

            if (popupStatus == 'Show') {
                $("#modalPoStatus").modal("show");
                var poid = $(this).attr("data-id");
                var poStatus = $.trim($(this).text());
                $("#hdnPoID").val(poid);
            } else {
                alert("Please Delete Commercial data to cancel this Invoice");
            }

            console.log(popupStatus);

        })
        // $('#modalPoStatus').on('shown.bs.modal', function () {

        // if(poStatus == "PI-Pending") {
        //     $("#divPIList").show();
        //     $("#divPoNo").show();
        //     $("#divPoDate").show();
        // }else if(poStatus == "Po Received") {
        //     $("#divPIList").show();
        //     $("#divPoNo").hide();
        //     $("#divPoDate").hide();
        // }else if(poStatus == "Approved") {
        //     $("#divPIList").show();
        //     $("#divPoNo").show();
        //     $("#divPoDate").show();
        // }else if(poStatus == "Cancelled") {
        //     $("#divPIList").hide();
        //     $("#divPoNo").hide();
        //     $("#divPoDate").hide();
        // }

        function fnSuccess(data) {
            $("#divDataList").html(data);
            fnShowDataTable();

            $("<button class='btn btn-primary' id='btnMoreSearch' style='padding: 3px 5px;margin-left: 10px;'>More Search</button>" +
                    "<button class='btn btn-primary' id='btnLoadAll' style='padding: 3px 5px;margin-left: 10px;'>Load All</button>")
                .insertAfter("#tblDataTable_filter label");


            $('html, body').animate({
                scrollTop: $("#divDataList").offset().top - 50
            }, 10);
        }

        function ShowList(pageNo, searchkey, loadAll) {
            $.ajax({
                url: 'Ajax/AjaxCustomInvoices.php',
                type: 'POST',
                data: {
                    ExporterID: $("#drpExporterList").val(),
                    ConsigneeID: $("#drpConsigneeList").val(),
                    CountryID: $("#drpCountryList").val(),
                    Year: $("#drpYear").val(),
                    InvType: $("#drpStatus").val(),
                    PageNo: pageNo,
                    SearchKey: searchkey,
                    LoadAll: loadAll,
                    qc: '<?php echo $qc; ?>'

                },
                success: function(data) {
                    fnSuccess(data);
                }
            });
        }

        $(document).ready(function() {
            $("#drpStatus").val("1").trigger("change");
            ShowList("1", "", "0");
        });

        $("#btnSearch").on("click", function() {
            ShowList("1", "", "0");
        });

        $(document).on("click", "#btnMoreSearch", function() {
            var searchkey = $("#tblDataTable_filter input").val();
            if (searchkey.length > 0) {
                ShowList("1", searchkey, "0");
            }
        });

        $(document).on("click", ".btnPageno", function() {
            var pageno = $(this).attr("data-pageno");
            ShowList(pageno, "", "0");
        });

        $(document).on("click", "#btnLoadAll", function() {
            ShowList("1", "", "1");
        });

        function fnCustom(id, action, pNo) {
            $.ajax({
                url: 'Ajax/AjaxCustomInvoicesActions.php',
                type: 'POST',
                data: {
                    Action: action,
                    ID: id,
                },
                success: function(data) {
                    console.log("respponse", data);

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

        $(document).on("click", "#btnModalContinue", function() {
            var selectedID = $("#hdnSelectedData").val();
            var pNo = $(".active.btnPageno").attr("data-pageno");

            if ($("#hdnActionType").val() == "delete") {
                fnCustom(selectedID, "Delete", pNo);
            } else {
                fnCustom(selectedID, "Duplicate", pNo);
            }

        });

        $(document).on("click", ".btnDuplicate", function() {
            var selectedID = $(this).attr("data-id");
            $("#hdnActionType").val("duplicate");

            fnShowModalConfirmation("Confirmation", "Are you sure you want to copy the item ?", "Cancel", "Continue", selectedID, "T");

        });



        $(document).on("click", ".btnViewDocRpt", function() {
            $('#modalDocView').modal('show');

            var rowID = $(this).attr("data-id");
            var hasSauce = $(this).attr("data-sauce");
            console.log(rowID);
            var tdCInvNo = $(this).closest("tr").find(".tdCInvNo").text();
            $(".spanInvNo").text(" - " + tdCInvNo);

            $.ajax({
                url: 'Ajax/AjaxDocCustomInvoice.php',
                type: 'POST',
                data: {
                    'ID': rowID,
                    'HasSauce': hasSauce
                },
                success: function(data) {
                    $("#CreateDocID").html(data);
                }
            });
        });

        // Durga 
        // $(document).ready(function() {
        //     if(CustomPoUrl != "") {
        //         $("#printButton").show();
        //     } else {
        //         $("#printButton").hide();
        //     }
        // })
        jQuery.validator.addMethod('selectcheck', function(value) {
            return (value != '0' || value != '');
        }, "year required");
        jQuery.validator.addMethod("noSpace", function(value, element) {
            return value == '' || value.trim().length != 0;
        }, "No space please and don't leave it empty");
        $(document).ready(function() {
            $("#frmPOStatusDetails").validate({
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
                    txtRemarks: {
                        required: true,
                        noSpace: true
                    },
                    drpPoStatus: {
                        required: true,
                    }
                },
                errorPlacement: function() {
                    return false; // suppresses error message text
                },
                submitHandler: function() {
                    var searchkey = $("#tblDataTable_filter input").val();
                    $.ajax({
                        url: 'Ajax/AjaxCustomInvoices.php',
                        type: 'POST',
                        data: {
                            ExporterID: $("#drpExporterList").val(),
                            ConsigneeID: $("#drpConsigneeList").val(),
                            CountryID: $("#drpCountryList").val(),
                            Year: $("#drpYear").val(),
                            InvType: $("#drpStatus").val(),
                            PageNo: $(".active.btnPageno").attr("data-pageno"),
                            SearchKey: searchkey,
                            LoadAll: "",
                            qc: '<?php echo $qc; ?>',
                            PiID: $("#hdnPoID").val(),
                            poStatus: $("#drpPoStatus").val(),
                            poStatusRemark: $("#txtRemarks").val(),

                        },
                        success: function(data) {
                            // fnSuccess(data);

                            $("#divDataList").html(data);
                            fnShowDataTable();

                            var apstatus = $(document).find("#hdnAPIStatus").val();

                            alert(apstatus);

                            $('#modalPoStatus').modal('hide');

                            $("<button class='btn btn-primary' id='btnMoreSearch' style='padding: 3px 5px;margin-left: 10px;'>More Search</button>" +
                                    "<button class='btn btn-primary' id='btnLoadAll' style='padding: 3px 5px;margin-left: 10px;'>Load All</button>")
                                .insertAfter("#tblDataTable_filter label");


                            $('html, body').animate({
                                scrollTop: $("#divDataList").offset().top - 50
                            }, 10);


                        }
                    });

                    return false;
                }
            });
        });
    </script>

    </body>

</html>