<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

$filterDepartment = "T";
$filterSubDepartments = "T";
$filterDesignations = "T";
$filterEmployeeStatus = "T";

$buttonBottom = "T";
$buttonBottom = "T";
?>

<!doctype html>
<html lang="en">

<head>

    <?php include_once ROOT . '/includes/head.php' ?>
    <style>
        <?php
        $url = $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];

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
                <?php // include_once ROOT . 'v3/Sales/includes/page-filter.php' 
                ?>
                <?php include_once WORKING_MODULE . '/includes/page-filter.php' ?>

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
                                        
                                        <div class="text-center">
                                            <!-- <a href="create-employee-basic" class="newItem btn btn-success waves-effect waves-light">
                                                <i class="fa fa-plus"></i> New
                                            </a>   -->
                                            
                                            <a href="create-employee" class="newItem btn btn-success waves-effect waves-light">
                                                <i class="fa fa-plus"></i> New
                                            </a>
                                        </div> 
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
            bInfo: false

        });
    }

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
            url: 'Ajax/ajax-empolyee-list.php',
            type: 'POST',
            data: {
                DeptID: $("#drpDepartment").val(),
                SubDeptID: $("#drpSubDepartments").val(),
                DesigID: $("#drpDesignations").val(),
                EmpStatus: $("#drpEmployeeStatus").val(),
                SearchKey: searchkey,
                PageNo: pageNo,
                LoadAll: loadAll
            },
            success: function(data) {
                fnSuccess(data);
            }
        });
    }

    $(document).ready(function() {
        $("#drpEmployeeStatus").val("1").trigger("change");
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

    $(document).on("click", ".btnDelete", function() {
        var selectedID = $(this).attr("data-id");
        $("#hdnActionType").val("delete");
        fnShowModalConfirmation("Confirmation", "Are you sure you want to delete the item ?", "Cancel", "Continue",
            selectedID, "T");
        // fnShowModalConfirmation("Success", "Successfully Updated !", "Cancel", "", "", "T);
        // fnShowModalConfirmation("Error", "Something error has occurred", "Cancel", "", "", "T);
    })


    $(document).on("click", "#btnModalContinue", function() {
        var selectedID = $("#hdnSelectedData").val();
        var pNo = $(".active.btnPageno").attr("data-pageno");

        if ($("#hdnActionType").val() == "delete") {
            fnCustom(selectedID, "Delete", pNo);
        } else {
            fnCustom(selectedID, "Duplicate", pNo);
        }

    });

    // $(document).on("click", ".btnDuplicate", function(){
    //     var selectedID = $(this).attr("data-id");
    //     $("#hdnActionType").val("duplicate");

    //     fnShowModalConfirmation("Confirmation", "Are you sure you want to copy the item ?", "Cancel", "Continue", selectedID, "T");

    // });

    $(document).on("click", ".btnViewDocRpt", function() {
        $('#modalDocView').modal('show');

        var rowID = $(this).attr("data-id");
        console.log(rowID);
        var tdCInvNo = $(this).closest("tr").find(".tdCInvNo").text();
        $(".spanInvNo").text(" - " + tdCInvNo);

        $.ajax({
            url: 'Ajax/AjaxDocCustomInvoice.php',
            type: 'POST',
            data: {
                'ID': rowID
            },
            success: function(data) {
                $("#CreateDocID").html(data);
            }
        });

    })
</script>

</body>

</html>