<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$apiList = API_CHEMICAL;

$ChemID = trim($_GET['ChemID']);

$bodyContent = array(
    "ChemID" => $ChemID,
);

if ($ChemID != '') {
    $result = CallAPI($apiList, "GET", "", $bodyContent);

    $Data = $result->Data[0];
}
// echo json_encode($result);

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


                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <div class="card">
                            <div class="card-body">
                                <form role="form" method="post" id="frmChemical">
                                    <div class="row">

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtChemical" class="form-label">Chemical Name<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtChemical" name="txtChemical" placeholder="Enter Chemical Name" value='<?php echo $Data->ChemicalName ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtDescription" class="form-label">Chemical Description<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtDescription" name="txtDescription" placeholder="Enter Chemical Description" value='<?php echo $Data->ChemicalDescription ?>' />
                                        </div>

                                        <div class="col col-md-3 mb-3">
                                            <label for="txtKgPerBag" class="form-label">NoOf Kg Per Bag<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtKgPerBag" name="txtKgPerBag" placeholder="Enter NoOf KGs Per Bag" value='<?php echo $Data->NoOfKgPerBag ?>' />
                                        </div>

                                        <!-- <div class="col col-md-3 mb-3">
                                            <label for="txtKgPerBag" class="form-label">NoOf Kg Per Bag<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtKgPerBag" name="txtKgPerBag" placeholder="Enter NoOf KGs Per Bag" value='<?php echo $Data->NoOfKgPerBag ?>' />
                                        </div>
                                        
                                        <div class="col col-md-3 mb-3">
                                            <label for="txtKgPerBag" class="form-label">NoOf Kg Per Bag<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtKgPerBag" name="txtKgPerBag" placeholder="Enter NoOf KGs Per Bag" value='<?php echo $Data->NoOfKgPerBag ?>' />
                                        </div> -->

                                        <div class="text-center">
                                            <input type="hidden" id="hdnSaveType" name="hdnSaveType" value="<?php echo $ChemID ?>" />
                                            <input type="hidden" id="hdnNewVariety" name="hdnNewVariety" />
                                            <a href="GsChemBuyersList" class="btn btn-danger">Close</a>
                                            <?php if ($ChemID == '') { ?>
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
        var chemID = "<?php echo isset($_GET["ChemID"]) && !empty($_GET["ChemID"]) ? $_GET["ChemID"] : "" ?>";

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
                    title: 'GS Chemicals  -<?php echo date("d-m-Y") ?>',
                    footer: true,
                    exportOptions: {
                        columns: ':visible'
                    }
                }],
            });
        }

        $(document).ready(function() {
            $.ajax({
                url: 'Ajax/AjaxGsChemicalCreate.php',
                type: 'POST',
                data: {
                    methodType: "get",
                    chemID: chemID
                },
                success: function(data) {
                    $("#divDataList").html(data);
                    fnShowDataTable();
                    return false;
                }
            });
        });

        $("#frmChemical").validate({

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
                txtChemical: {
                    required: true,
                },
                txtDescription: {
                    required: true,
                },
                txtKgPerBag: {
                    required: true,
                },
            },
            errorPlacement: function() {
                return false; // suppresses error message text
            },
            submitHandler: function() {

                $.ajax({
                    url: 'Ajax/AjaxGsChemicalCreate.php',
                    type: 'POST',
                    data: {
                        methodType: "post",
                        chemID: chemID,
                        chemical: $("#txtChemical").val(),
                        description: $("#txtDescription").val(),
                        kgPerBag: $("#txtKgPerBag").val(),
                    },
                    success: function(data) {
                        console.log(data);
                        if (data.Error != '') {
                            alert(data.Msg);
                            window.location.href = "GsChemicalsList.php";
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