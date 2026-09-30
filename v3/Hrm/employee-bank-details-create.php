<?php



// phpinfo();



// ini_set('display_errors', 1);

// ini_set('display_startup_errors', 1);

// error_reporting(E_ALL);





include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';



include_once ROOT . 'config/api-call.php';

$ClientConfigData = $_SESSION['ClientConfigData'];

//echo "d:".$ClientConfigData['bankdetailsedit']. "==" . $_SESSION['UserID'];

$ClientConfigData = $_SESSION['ClientConfigData'];



$api = API_HRM_DROPDOWNS;



$resDropdownsAPI = CallAPI($api, "GET", "", "");



$resBankDetails = $resDropdownsAPI->Data->BankDetails;



$api = API_EMPLOYEE_BANK_DETAILS_CREATE;

$empID = trim($_GET["EmpID"]);



$bodyContent = array(



    "EmployeeID" => $empID



);



$result = CallAPI($api, "GET", "", $bodyContent);



$error = $result->Error;







if (!isset($error) || empty($error)) {







    $resList = $result->Data->EmployeeBankDetails;



    if (isset($resList) && !empty($resList)) {

        if (!empty($ClientConfigData['bankdetailsedit'])) {

            // 1. Strip out pesky backslashes if magic quotes or escaping happened
            $clean_json = stripslashes($ClientConfigData['bankdetailsedit']);

            // 2. Remove UTF-8 non-breaking spaces (C2 A0) and other hidden whitespace
            $clean_json = preg_replace('/^\xEF\xBB\xBF/', '', $clean_json); // Remove BOM if present
            $clean_json = str_replace(array("\xc2\xa0", "\xa0"), ' ', $clean_json); // Convert non-breaking spaces to regular spaces
            $clean_json = preg_replace('/[\x00-\x1F\x7F-\x9F]/u', '', $clean_json); // Clean control characters

            // 3. Decode to an associative array
            $required_users = json_decode($clean_json, true);

            // 4. Validate that it successfully decoded AND the user is in the array
            if (is_array($required_users) && in_array($_SESSION['UserID'], $required_users)) {

                // Optional: Put code here if you want to do something when they ARE authorized
                // Otherwise, just let the code continue running normally.

            } else {
                // Debugging block inside the failure state
                /*echo "sid: " . (isset($_SESSION['UserID']) ? $_SESSION['UserID'] : 'Not Set') . "<br>";
                echo "<pre>required_users = "; print_r($required_users); echo "</pre>";
                echo "<pre>bankdetailsedit = "; print_r($ClientConfigData['bankdetailsedit']); echo "</pre>";
                echo "JSON Error: " . json_last_error_msg() . "<br>"; // This will tell us EXACTLY why it failed*/
                echo "You are not authorized";
                exit;
            }

        } else {
            echo "You are not authorized";
            exit;
        }

    }

}

?>



<!doctype html>

<html lang="en">



<head>



    <?php include_once ROOT . 'includes/head.php' ?>



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

                <?php // include_once WORKING_MODULE . '/includes/page-filter.php'

                ?>



                <!-- start page title -->

                <div class="row">

                    <div class="col-12">



                        <div class="card">

                            <div class="card-body">

                                <div>

                                    <form role="form" method="post" id="frmEmployeeBankDetailsCreate">



                                        <div class="row">



                                            <div class="col col-md-3 mb-3">

                                                <label for="drpBank">Bank Name</label>

                                                <select class="form-control drpSelect" name="drpBank" id="drpBank">

                                                    <option value=""> Select Bank</option>

                                                    <?php foreach ($resBankDetails as $BankDetails) { ?>

                                                        <option value="<?php echo $BankDetails->BankNameID ?>">

                                                            <?php echo $BankDetails->BankName ?>

                                                        </option>

                                                    <?php } ?>

                                                    <select>

                                            </div>



                                            <div class="form-group mb-3 col-lg-3">

                                                <label for="txtAccountNumber">Account Number</label>

                                                <input type="text" class="form-control" name='txtAccountNumber'

                                                    id="txtAccountNumber" value="" />

                                            </div>

                                            <div class="form-group mb-3 col-lg-3">

                                                <label for="txtIFSC">IFSC</label>

                                                <input type="text" class="form-control" name='txtIFSC' id="txtIFSC"

                                                    value="" />

                                            </div>



                                            <div class="form-group mb-3 col-sm-3">

                                                <label for="drpAccountType">Account Type </label>

                                                <select class="form-control drpSelect" name="drpAccountType"

                                                    id="drpAccountType">

                                                    <option value=""> Select Bank</option>

                                                    <option value="savings">Savings </option>

                                                    <select>

                                            </div>



                                            <div class="form-group mb-3 col-sm-3">

                                                <label for="txtDOC">Date Of Creation</label>

                                                <input type="date" class="form-control" name='txtDOC' id="txtDOC" />

                                            </div>

                                            <div class="col-xs-12 text-center box-footer">

                                                <input type="hidden" id="hdnBreakUpId" name="hdnBreakUpId" />

                                                <a href="EmployeeBankDetails" class="btn btn-danger"> Close</a>

                                                <button type="submit" id="btnSearch" name="btnSearch"

                                                    class="btn btn-success waves-effect waves-light"> Add</button>

                                                <button type="button" id="btnNew" name="btnNew"

                                                    class="btn btn-success waves-effect waves-light"> New</button>

                                            </div>

                                        </div>

                                    </form>

                                </div>

                            </div>

                        </div>



                        <div class="card">

                            <div class="card-body">

                                <div id="divDataList">



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



<?php include_once ROOT . 'includes/common-js.php' ?>



<!-- Required datatable js -->

<!-- <script src="<?php //echo WORKING_URL

?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>

<script src="<?php //echo WORKING_URL

?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script> -->



<script src="../assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>

<script src="../assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>



<?php include_once ROOT . 'includes/app-js.php' ?>



<script>

    var id = '<?php echo isset($_GET["EmpID"]) && !empty($_GET["EmpID"]) ? $_GET["EmpID"] : "" ?>';

    function fnShowDataTable() {

        var table = $('#tblDataTable').DataTable({

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

                footer: true,

                title: "Employee Bank Details"

            }],

            drawCallback: function () {

                $(document).trigger('afterSearch');

            }

        });

    }





    $(document).ready(function () {

        $.ajax({

            url: 'Ajax/ajax-employee-bank-details-create.php',

            type: 'POST',

            data: {

                methodType: "get",

                empID: id

            },

            success: function (response) {

                try {
                    var data = typeof response === 'string' ? JSON.parse(response) : response;
                } catch(e) {
                    $("#divDataList").html(response);
                    fnShowDataTable();
                    return false;
                }

                if (data.status === "success") {
                    $("#divDataList").html(data.table);
                    fnShowDataTable();
                } else {
                    $("#divDataList").html('<div class="alert alert-danger">' + (data.message || 'An error occurred.') + '</div>');
                }

                return false;

            }

        });
    });







    $("#frmEmployeeBankDetailsCreate").validate({

        onkeyup: false,

        onfocusout: false,

        invalidHandler: function (form, validator) {

            var errors = validator.numberOfInvalids();

            if (errors) {

                var firstInvalidElement = $(validator.errorList[0].element);

                //$('html,body').scrollTop(firstInvalidElement.offset().top - 100);

                firstInvalidElement.focus();

            }

        },

        ignore: "",

        rules: {

            drpBank: {

                required: true

            },

            txtAccountNumber: {

                required: true,

                noSpace: true

            },

            drpAccountType: {

                required: true

            },

            txtIFSC: {

                required: true,

                noSpace: true

            },



            txtDOC: {

                required: true,

                noSpace: true

            }

        },

        errorPlacement: function () {

            return false; // suppresses error message text

        },

        submitHandler: function () {



            $.ajax({

                url: 'Ajax/ajax-employee-bank-details-create.php',

                type: 'POST',

                data: {

                    methodType: "post",

                    breakupId: $("#hdnBreakUpId").val(),

                    empID: id,

                    bank: $("#drpBank").val(),

                    accountNumber: $("#txtAccountNumber").val(),

                    accountType: $("#drpAccountType").val(),

                    ifsc: $("#txtIFSC").val(),

                    doc: $("#txtDOC").val(),

                },

                success: function (response) {

                    try {
                        var data = typeof response === 'string' ? JSON.parse(response) : response;
                    } catch(e) {
                        if ($("#divAlertMessage").length === 0) {
                            $("#frmEmployeeBankDetailsCreate").before('<div id="divAlertMessage"></div>');
                        }
                        $("#divAlertMessage").html(response);
                        return false;
                    }

                    if (data.status === "error") {

                        if ($("#divAlertMessage").length === 0) {

                            $("#frmEmployeeBankDetailsCreate").before('<div id="divAlertMessage"></div>');

                        }

                        $("#divAlertMessage").html('<div class="alert alert-danger">' + data.message + '</div>');

                        return false;

                    }

                    $("#divAlertMessage").html('');

                    $("#divDataList").html(data.table);

                    fnShowDataTable();

                    $("#drpBank").val("").trigger("change");
                    $("#txtAccountNumber").val("");
                    $("#drpAccountType").val("").trigger("change");
                    $("#txtIFSC").val("");
                    $("#txtDOC").val("");
                    $("#hdnBreakUpId").val("");

                    return false;

                }

            });



            // $.ajax({

            //     url: 'Ajax/AjaxSalaryElements.php',

            //     type: 'POST',

            //     data: frmData,

            //     success: function (data) {

            //         // $("#divDataList").html(data);

            //         // fnShowDataTable();

            //         return false;

            //     }

            // });



            return false;

        }

    });



    $(document).on("click", ".btnEdit", function () {

        var breakupId = $(this).attr("data-breakup-id");



        var tdBankId = $(this).closest("tr").find(".tdBankName").attr("data-bankid");

        var tdAccountNumber = $(this).closest("tr").find(".tdAccountNumber").text();

        var tdAccountTypeId = $(this).closest("tr").find(".tdAccountType").attr("data-actype");

        var tdIfsc = $(this).closest("tr").find(".tdIfsc").text();

        var tdDoc = $(this).closest("tr").find(".tdDateOfCreation").attr("data-doc-date");



        // console.log(tdIfsc);



        $("#hdnBreakUpId").val(breakupId);

        $("#drpBank").val($.trim(tdBankId)).trigger("change");

        $("#txtAccountNumber").val($.trim(tdAccountNumber));

        $("#drpAccountType").val($.trim(tdAccountTypeId)).trigger("change");

        $("#txtIFSC").val($.trim(tdIfsc));

        $("#txtDOC").val($.trim(tdDoc));



        $("#btnSearch").text("Update");



    });



    $(document).on("click", "#btnNew", function () {

        $("#drpBank").val("").trigger("change");

        $("#txtAccountNumber").val("");

        $("#drpAccountType").val("").trigger("change");

        $("#txtIFSC").val("");

        $("#txtDOC").val("");

        $("#hdnBreakUpId").val("");

        $("#btnSearch").text("Add");

    });

</script>



</body>



</html>

