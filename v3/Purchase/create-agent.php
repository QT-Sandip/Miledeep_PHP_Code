<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$apiList = API_RM_AGENT_LIST;

$AgentID = trim($_GET['ID']);

$bodyContent = array(
    "ID" => $AgentID
);

if ($AgentID != '') {
    $result = CallAPI($apiList, "GET", "", $bodyContent);

    // var_dump($result);

    $Data = $result->Data[0];
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
                                <form role="form" method="post" id="frmAgent">
                                    <div class="row">

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtAgentName">Agent Name<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name='txtAgentName' id="txtAgentName" placeholder="Enter Agent Name" value="<?php echo $Data->UserName ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtPanTNumber">PAN Number<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" name="txtPanNumber" id="txtPanNumber" maxlength="10" placeholder="Enter PAN Number" value="<?php echo $Data->PanNumber ?>">
                                        </div>


                                        <div class="col col-md-3 mb-3">
                                            <label for="drpCompany">Company<span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpCompany" id="drpCompany">
                                                <?php if (isset($_GET['ID']) && !empty($_GET['ID'])) {
                                                    echo '<option value="0" ' . (($Data->IsCompany == 0) ? 'selected' : '') . '>Non-Company</option>
                                                   <option value="1" ' . (($Data->IsCompany == 1) ? 'selected' : '') . '>Company</option>';
                                                } else {
                                                    echo '<option value="0" selected>Non-Company</option>
                                                    <option value="1">Company</option>';
                                                } ?>
                                            </select>
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtGstNumber">GST Number</label>
                                            <input type="text" class="form-control" name="txtGstNumber" id="txtGstNumber" maxlength="15" placeholder="Enter GST Number" value="<?php echo $Data->GstNo ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtBankName">Agent Bank Name</label>
                                            <input type="text" class="form-control" name="txtBankName" id="txtBankName" placeholder="Enter Bank Name " value="<?php echo $Data->BankName ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtBankAccNo">Bank Account Number</label>
                                            <input type="text" class="form-control" name="txtBankAccNo" id="txtBankAccNo" placeholder="Enter Bank Account Number " value="<?php echo $Data->BankAccNo ?>">
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtIfsc">IFSC Code</label>
                                            <input type="text" class="form-control" name="txtIfsc" id="txtIfsc" placeholder="Enter IFSC Code" value="<?php echo $Data->IFSC ?>">
                                        </div>

                                        <div class="text-center">
                                            <input type="hidden" id="hdnSaveType" name="hdnSaveType" value="<?php echo $AgentID ?>" />
                                            <input type="hidden" id="hdnNewVariety" name="hdnNewVariety" />
                                            <a href="agent-list" class="btn btn-danger">Close</a>
                                            <?php if ($AgentID == '') { ?>
                                                <button class="btn btn-primary btnSave" name="submit" id="submit">Submit</button>
                                            <?php } else { ?>
                                                <button class="btn btn-success btnSave" name="submit" id="submit">Update</button>
                                            <?php } ?>
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js" type="text/javascript"></script>
    <script>
        var agentID = "<?php echo isset($_GET["ID"]) && !empty($_GET["ID"]) ? $_GET["ID"] : "" ?>";

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
                    title: 'HLVA Variety-List-<?php echo date("d-m-Y") ?>',
                    footer: true,
                    exportOptions: {
                        columns: ':visible'
                    }
                }],
            });
        }

        $(document).ready(function() {
            $.ajax({
                url: 'Ajax/ajax-create-agent.php',
                type: 'POST',
                data: {
                    methodType: "get",
                    AgentID: agentID
                },
                success: function(data) {
                    $("#divDataList").html(data);
                    fnShowDataTable();
                    return false;
                }
            });
        });

        jQuery.validator.addMethod(
            "noSpace",
            function(value, element) {
                return value == "" || value.trim().length != 0;
            },
            "No space please and don't leave it empty"
        );
        $("#frmAgent").validate({

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
                txtAgentName: {
                    required: true,
                },
                // txtGstNumber: {
                //     required: true,
                // },
                txtPanNumber: {
                    required: true,
                    noSpace: true
                },
                // txtBankName: {
                //     required: true,
                //     noSpace: true
                // },
                // txtBankAccNo: {
                //     required: true,
                //     noSpace: true
                // },
                // txtIfsc: {
                //     required: true,
                // },

            },
            errorPlacement: function() {
                return false; // suppresses error message text
            },
            submitHandler: function() {

                $.ajax({
                    url: 'Ajax/ajax-create-agent.php',
                    type: 'POST',
                    data: {
                        methodType: "post",
                        agentID: agentID,
                        agentName: $("#txtAgentName").val(),
                        gstNumber: $("#txtGstNumber").val(),
                        panNumber: $("#txtPanNumber").val(),
                        bankName: $("#txtBankName").val(),
                        bankAccNo: $("#txtBankAccNo").val(),
                        ifsc: $("#txtIfsc").val(),
                        company: $("#drpCompany").val(),
                    },
                    success: function(data) {
                        $("#divDataList").html(data);
                        fnShowDataTable();
                        return false;
                    }
                });

                return false;
            }
        });
    </script>

    </body>

</html>