<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

$filterCenter = "T";
$filterBillerType = "T";
$filterSupplier = "T";
$filterAgent = "T";
$filterPayDate = "T";
$buttonRight = "T";
$buttonPay = "T";

$ClientConfigData = (object)$_SESSION['ClientConfigData'];
$pancardduplicate = trim($ClientConfigData->pancardduplicate);
?>

<!doctype html>
<html lang="en">

<head>

    <?php include_once ROOT . 'includes/head.php' ?>
    <style>
        #tblPaymentMode_wrapper .dataTables_scrollBody {
            min-height: 120px;
        }

        .modal-dialog.modal-xl {
            max-width: 1600px;
        }
    </style>
</head>

<?php include_once ROOT . '/includes/body-preloader.php' ?>

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
                <?php include_once WORKING_MODULE . 'includes/page-filter.php'; ?>

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <div class="card">
                            <div class="card-body">

                                <input type="hidden" id="hdnSelectedRow" name="hdnSelectedRow" />

                                <div id="divDataList">
                                    <!-- -->
                                </div>

                            </div>
                        </div>


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

<div class="modal fade" id="ModalRmPayment" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl"> <!-- modal-fullscreen -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Bill Payment Mode<span id="spanBllDate"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

            </div>
            <form role="form" id="frmPaymentMode" method="post">
                <div class="modal-body">

                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-md-6 col-lg-3 mb-3">
                                <label for="drpPaymentMode" class="form-label">Payment Mode </label>
                                <select class="form-control select2" name="drpPaymentMode" id="drpPaymentMode">
                                    <option value="">Select</option>
                                    <option value="B">Cheque</option>
                                    <option value="C">Cash</option>
                                    <option value="J">Journal</option>
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-3 mb-3">
                                <label for="txtVoucherNo" class="form-label">Voucher No</label>
                                <input type="text" class="form-control" id="txtVoucherNo" readonly name="txtVoucherNo" placeholder=" Enter Voucher No">
                                <input type="hidden" class="form-control" id="hdnAutoVoucherNo" name="hdnAutoVoucherNo" />
                            </div>
                            <div class="col-md-6 col-lg-3 mb-3">
                                <label for="txtChequeNEFT" class="form-label">Cheque/NEFT.No</label>
                                <input type="text" class="form-control" id="txtChequeNEFT" disabled name="txtChequeNEFT" placeholder=" Enter Cheque/NEFT.No">
                            </div>
                            <div class="col-md-6 col-lg-3 mb-3">
                                <label for="txtAmount" class="form-label">Amount</label>
                                <input type="text" class="form-control" id="txtAmount" readonly name="txtAmount" placeholder=" Enter Amount">
                            </div>
                            <div class="col-md-6 col-lg-3 mb-3">
                                <label for="txtComments" class="form-label">Comments</label>
                                <input type="text" class="form-control" id="txtComments" name="txtComments" placeholder=" Enter Comments">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 col-lg-12 mt-3">
                                <div id="divDataListPopup">

                                </div>


                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <div class="text-danger text-end divPopupError" style="width:100%;display: none;">Total amount should not be grater then Payment Amount.</div>
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                    <button type="submit" name='btnPaymentMode' id="btnPaymentMode" class="btn btn-primary">Save changes</button>
                    <input type="hidden" id="hdnIsRowCheck" />
                    <input type="hidden" id="hdnIsColCheck" />
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once ROOT . 'includes/common-js.php' ?>

<!-- Required datatable js -->
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

<?php include_once ROOT . 'includes/app-js.php' ?>

<script>
    var tblPaymentMode;

    function fnShowDataTable() {
        var table = $('#tblDataTable').DataTable({
            scrollY: "600px",
            scrollX: true,
            sScrollXInner: "100%",
            paging: false,
            destroy: true,
            fixedColumns: true,
            scrollCollapse: true,
            columnDefs: [{
                searchable: false,
                orderable: false,
                targets: 0
            }],
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
            buttons:[],
            bSort: true,
            "bStateSave": true,
            "fnStateSave": function(oSettings, oData) {
                localStorage.setItem('key-datatable', JSON.stringify(oData));
            },
            "fnStateLoad": function(oSettings) {
                return JSON.parse(localStorage.getItem('key-datatable'));
            }
        });
    }

    function fnShowDataTablePaymentMode() {
        tblPaymentMode = $('#tblPaymentMode').DataTable({
            scrollY: "600px",
            scrollX: true,
            sScrollXInner: "100%",
            paging: false,
            destroy: true,
            fixedColumns: true,
            scrollCollapse: true,
            // columnDefs: [{
            //     searchable: false,
            //     orderable: false,
            //     targets: 0
            // }],
            bSort: false,

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
            buttons:[],
            "bStateSave": true,
            "fnStateSave": function(oSettings, oData) {
                localStorage.setItem('key-datatable', JSON.stringify(oData));
            },
            "fnStateLoad": function(oSettings) {
                return JSON.parse(localStorage.getItem('key-datatable'));
            }
        });
        // tblPaymentMode.DataTable().columns.adjust();

    }

    function ShowList() {
        $.ajax({
            url: 'Ajax/AjaxRMPayments.php',
            type: 'POST',
            data: {
                PsId: $("#drpCenter").val(),
                BillerType: $("#drpBillerType").val(),
                SupplierID: $("#drpSupplier").val(),
                AgentID: $("#drpAgent").val(),
                BillDate: $("#txtBillsDate").val()
            },
            success: function(data) {
                $("#divDataList").html(data);
                fnShowDataTable();
                return false;
            }
        });
    }

    $(document).on("change", "#drpBillerType", function() {
        var billerType = $(this).val();
        $('#drpSupplier, #drpAgent').val("").trigger("change");
        if (billerType == "1") {
            $('#drpSupplier').prop("disabled", true);
            $('#drpAgent').prop("disabled", false);
        } else {
            $('#drpSupplier').prop("disabled", false);
            $('#drpAgent').prop("disabled", true);
        }
    });

    $(document).ready(function() {
        $('#drpSupplier, #drpAgent').prop("disabled", true);
    });

    function searchRequired(val, selector) {
        if (val == "") {
            $(selector).next().find('.select2-selection').addClass('error');
        } else {
            $(selector).next().find('.select2-selection').removeClass('error');
        }
    }

    $("#btnSearch").on("click", function() {
        $("#hdnSelectedRow").val("");
        var drpCenter = $("#drpCenter").val();
        var drpBillerType = $("#drpBillerType").val();
        var drpAgent = $("#drpAgent").val();
        var drpSupplier = $("#drpSupplier").val();
        var txtBillsDate = $("#txtBillsDate").val();

        // console.log("drpCenter = "+ drpCenter +" - "+ "drpBillerType = "+  drpBillerType +" - "+ "drpAgent= "+ drpAgent +" - "+ "drpSupplier =" + drpSupplier);

        if (drpCenter == "" || drpBillerType == "") {

            // console.log("1");
            if (drpCenter == "") {
                $("#drpCenter").next().find('.select2-selection').addClass('error');
            } else {
                $("#drpCenter").next().find('.select2-selection').removeClass('error');
            }

            if (drpBillerType == "") {
                // console.log("2");
                $("#drpBillerType").next().find('.select2-selection').addClass('error');
                $("#drpSupplier").next().find('.select2-selection').removeClass('error');
                $("#drpAgent").next().find('.select2-selection').removeClass('error');
                $('#drpSupplier, #drpAgent').prop("disabled", true);
            }
            // console.log("else1");
            else if (drpBillerType == "1") {
                // console.log("3");
                if (drpAgent == "") {
                    $("#drpAgent").next().find('.select2-selection').addClass('error');
                } else {
                    $("#drpAgent").next().find('.select2-selection').removeClass('error');
                }
                $("#drpBillerType, #drpSupplier").next().find('.select2-selection').removeClass('error');
            } else if (drpBillerType == "2") {
                // console.log("4");
                if (drpSupplier == "") {
                    $("#drpSupplier").next().find('.select2-selection').addClass('error');
                } else {
                    $("#drpSupplier").next().find('.select2-selection').removeClass('error');
                }
                $("#drpBillerType, #drpAgent").next().find('.select2-selection').removeClass('error');
            }

        } else {
            // console.log("else2");

            if (drpBillerType == "1") {
                console.log("else3");
                if (drpAgent == "") {
                    $("#drpAgent").next().find('.select2-selection').addClass('error');
                } else {
                    $("#drpAgent").next().find('.select2-selection').removeClass('error');
                }
                $("#drpBillerType, #drpSupplier").next().find('.select2-selection').removeClass('error');
            }
             if (drpBillerType == "2") {
                console.log("else4");
                if (drpSupplier == "") {
                    $("#drpSupplier").next().find('.select2-selection').addClass('error');
                } else {
                    $("#drpSupplier").next().find('.select2-selection').removeClass('error');
                }
                $("#drpBillerType, #drpAgent").next().find('.select2-selection').removeClass('error');
            }
            if(drpCenter != "" && drpBillerType != "") {
                if (drpSupplier != "" || drpAgent != "") {

                    $('#drpCenter').next().find('.select2-selection').removeClass('error');
                    $('#drpBillerType').next().find('.select2-selection').removeClass('error');
                    $('#drpSupplier').next().find('.select2-selection').removeClass('error');
                    $('#drpAgent').next().find('.select2-selection').removeClass('error');

                    ShowList();
                }
            }
        }
    })


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
            $("#btnPay").prop("disabled", false);
        } else {
            $("#btnPay").prop("disabled", true);
            $('#hdnSelectedRow').val("");
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
        console.log("check all");
        if (this.checked) {
            $(".rowCheck").prop("checked", true);
            checkOptions();
        } else {
            $(".rowCheck").prop("checked", false);
            checkOptions();
        }
        //    $(".rowCheck").prop('checked', $(this).prop('checked'));
    });

    function convertDateToDDMMYYYY(date) {
        // Split the date into an array.
        var dateArray = date.split('-');

        // Reverse the order of the array elements.
        dateArray.reverse();

        // Join the array elements back into a string.
        var convertedDate = dateArray.join('-');

        return convertedDate;
    }

    $(document).on("click", "#btnPay", function() {

        var dtp = $("#txtBillsDate").val();

        dtp = convertDateToDDMMYYYY(dtp);

        $("#spanBllDate").text(" - " + dtp);
        $.ajax({
            url: 'Ajax/AjaxRMPaymentsPopup.php',
            type: 'POST',
            data: {
                ArrivalID: $("#hdnSelectedRow").val(),
                SupplierID: $("#drpSupplier").val(),
                BillDate: $("#txtBillsDate").val(),
                AgentID: $("#drpAgent").val(),
                AgentType: $("#drpBillerType").val(),
                apiMethod: "Get"
            },
            success: function(data) {
                $("#divDataListPopup").html("").html(data);
                $('#ModalRmPayment').modal('show');
                // setTimeout( function(){
                    fnShowDataTablePaymentMode();
                // }  , 1000 );

                // $("#hdnSelectedRow, #txtBillsDate").val("");
                // $("#drpSupplier").val("").trigger("change");
                return false;
            }
        });

        // tblPaymentMode.columns.adjust().draw();


    });

    $("#drpPaymentMode").on("change", function() {
        var value = $(this).val();
        console.log(value)
        if (value == "B") {
            $('#txtChequeNEFT').val("").prop("disabled", false);
        } else {
            $('#txtChequeNEFT').val("").prop("disabled", true);
        }

        $.ajax({
            url: 'Ajax/AjaxRMPaymentsPopup.php',
            type: 'POST',
            data: {
                "methodType": "getVoucherNo",
                "centerID": $("#drpCenter").val(),
                "billerType": $("#drpBillerType").val(),
                "paymentDate": $("#txtBillsDate").val(),
                "paymentMode": value
            },
            success: function(data) {
                console.log(data.Error);
                if (data.Error) {
                    alert(data.Error);
                    // fnShowModalConfirmation("Error", data.Error, "Close","","","T");
                } else {
                    // alert(data.Msg);
                    $("#txtVoucherNo").val(data.VoucherData.VoucherNo);
                    $("#hdnAutoVoucherNo").val(data.VoucherData.AutoVoucherNo);
                }
                return false;
            }
        });


    })

    $(document).on('shown.bs.modal', '#ModalRmPayment', function() {
        // tblPaymentMode.columns.adjust().draw();



        //fnSumOfAmountCol();

        $(".txtAmount").each(function() {
            var tdBalanceE = parseFloat($(this).closest("tr").find(".tdBalance").text()).toFixed(2);
            var amtE = parseFloat($(this).val()).toFixed(2);

            if (amtE > 0) {
                console.log(tdBalanceE, amtE);
                if (tdBalanceE >= amtE) {
                    var remaningE = tdBalanceE - amtE;
                    remaningE = parseFloat(remaningE).toFixed(2);
                    $(this).removeClass("error");
                    $(this).closest("tr").find(".txtRemaning").val(remaningE);
                    $("#btnPaymentMode").prop("disabled", false);

                } else {
                    $(this).addClass("error");
                    $(this).closest("tr").find(".txtRemaning").val(0);
                    $("#btnPaymentMode").prop("disabled", true);

                    $("#txtAmount").val("");
                    $(".dataTables_scrollFoot .tdAmount").text("");
                    $(".dataTables_scrollFoot .tdRemaning").text("");
                }
            }

        });

        fnSumOfAmountCol();

        if ( $.fn.DataTable.isDataTable('#tblPaymentMode') ) {
            $('#tblPaymentMode').DataTable().destroy();
        }

        fnShowDataTablePaymentMode();


        // tblPaymentMode.columns.adjust().draw();

    });

    function fnSumOfAmountCol() {
        var txtAmount;
        if ($("#txtAmount").val()) {
            txtAmount = parseFloat($("#txtAmount").val()).toFixed(2);
        } else {
            txtAmount = 0.00;
        }

        var sumAmount = 0;
        var sumRemaning = 0;

        $(".txtAmount").each(function() {
            if (!isNaN(this.value) && this.value.length != 0) {
                sumAmount += parseFloat(this.value);
            }
        });

        $(".txtRemaning").each(function() {
            if (!isNaN(this.value) && this.value.length != 0) {
                sumRemaning += parseFloat(this.value);
            }
        });

        sumAmount = parseFloat(sumAmount).toFixed(2);
        sumRemaning = parseFloat(sumRemaning).toFixed(2);

        //alert(sumRemaning);

        $("#txtAmount").val(sumAmount);

        $(".dataTables_scrollFoot .tdAmount").text(sumAmount);
        $(".dataTables_scrollFoot .tdRemaning").text(parseFloat(sumRemaning).toFixed(2));
    }

    $(document).on("keyup", ".txtAmount", function() {

        // fnSumOfAmountCol();

        var tdBalance = parseFloat($(this).closest("tr").find(".tdBalance").text()).toFixed(2);
        var amt = parseFloat($(this).val()).toFixed(2);

        //  alert(amt);

        if (amt > 0) {
            console.log("A1");
            console.log(tdBalance, amt);

            var remaning = tdBalance - amt;


            if (remaning >= 0) {
                console.log("A2");
                remaning = parseFloat(remaning).toFixed(2);

                //  alert();

                $(this).removeClass("error");
                $(this).closest("tr").find(".txtRemaning").val(remaning);
                $("#btnPaymentMode").prop("disabled", false);

            } else {
                console.log("A3");
                $(this).addClass("error");
                $(this).closest("tr").find(".txtRemaning").val(0);
                $("#btnPaymentMode").prop("disabled", true);

                $("#txtAmount").val("");
                $(".dataTables_scrollFoot .tdAmount").text("");
                $(".dataTables_scrollFoot .tdRemaning").text("");
            }
        } else {
            console.log("A4");
            $(this).addClass("error");
            $(this).closest("tr").find(".txtRemaning").val(0);
            $("#btnPaymentMode").prop("disabled", true);

            $("#txtAmount").val("");
            $(".dataTables_scrollFoot .tdAmount").text("");
            $(".dataTables_scrollFoot .tdRemaning").text("");
        }


        fnSumOfAmountCol();

    });

    $(document).ready(function() {
        $("#frmPaymentMode").validate({
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
                drpPaymentMode: {
                    required: true,
                    noSpace: true
                },
                txtVoucherNo: {
                    required: true,
                    noSpace: true
                },
                // txtChequeNEFT: { required: true, noSpace: true },
                txtAmount: {
                    required: true,
                    noSpace: true
                },

                txtChequeNEFT: {
                    required: function() {
                        return $("#drpPaymentMode").val() == "B";
                    },
                    noSpace: true
                }
            },
            errorPlacement: function() {
                return false; // suppresses error message text
            },
            submitHandler: function() {
                var PaymentDate = $('#txtBillsDate').val();

                const jsonObject = {
                    PaymentMode: $('#drpPaymentMode').val(),
                    Vocher: $('#txtVoucherNo').val(),
                    AutoVoucherNo: $('#hdnAutoVoucherNo').val(),
                    CheckNo: $('#txtChequeNEFT').val(),
                    Amount: $('#txtAmount').val(),
                    Remarks: $('#txtComments').val(),
                    PaymentDate: $('#txtBillsDate').val(),
                    CenterID: $('#drpCenter').val(),
                    BillerType: $("#drpBillerType").val(),

                    PaymentBreakUp: [],
                };

                $('td .txtAmount').each(function() {
                    const arrivalId = $(this).attr("data-arrivalid");
                    // const billerType = $(this).attr("data-billertype");
                    const billerType = $("#drpBillerType").val();

                    const amount = $(this).val();

                    jsonObject.PaymentBreakUp.push({
                        ArrivalID: arrivalId,
                        Amount: amount,
                        BillerType: billerType,

                    });
                });

                console.log(jsonObject);

                $.ajax({
                    url: 'Ajax/AjaxRMPaymentsPopup.php',
                    type: 'POST',
                    data: {
                        jsonData: jsonObject
                    },
                    success: function(data) {
                        console.log(data.Error);
                        if (data.Error) {
                            alert(data.Error);
                            // fnShowModalConfirmation("Error", data.Error, "Close","","","T");
                        } else {
                            alert(data.Msg);
                            $('#ModalRmPayment').modal('hide');
                            $("#drpPaymentMode, #txtVoucherNo, #txtChequeNEFT, #txtAmount, #txtComments").val("");
                            $("#btnSearch").trigger("click");
                            // fnShowModalConfirmation("Success", data.Msg, "Close","","","T");
                        }
                        return false;
                    }
                });


                return false;
            }
        });
    });

    var pancardduplicate = '<?php echo $pancardduplicate?$pancardduplicate:0; ?>';
if(pancardduplicate){
const LIMIT_DATE = "2026-03-31";

function checkDatesAndSubmit() {

    let fromDate = document.getElementById("txtBillsDate").value;



    // convert to Date objects
    let from = new Date(fromDate);

    let limit = new Date(LIMIT_DATE);

    // invalid range check

    // CASE 1: one side below limit and other above limit (NOT allowed)

    // CASE 2: valid cases → trigger AJAX

        triggerAjax(fromDate);

}


// Example AJAX function
function triggerAjax(fromDate) {
    $.ajax({
        url: "Ajax/AjaxGetSupplierList.php",
        type: "GET",
        dataType: "json",
        data: {
            fromDate: fromDate

        },
        success: function (res) {

            if (res.status && res.data.length > 0) {

                let options = '<option value="">Select Supplier</option>';

                $.each(res.data, function (i, item) {

                    options += `
                        <option value="${item.SupplierID}">
                            ${item.FinancierName} - ${item.PanNO}
                        </option>
                    `;
                });
                $("#drpSupplier").html("");
                $("#drpSupplier").html(options);

                // refresh select2 if used
                if ($.fn.select2) {
                    $("#drpSupplier").select2();
                }
            }
        },
        error: function (err) {
            console.log("Error loading suppliers", err);
        }
    });
}


// optional auto trigger button
$("#txtBillsDate").on("change", function () {
    checkDatesAndSubmit();
});
}
</script>

</body>

</html>
