<?php
$Status = isset($_SESSION['Status']) ? $_SESSION['Status'] : '';
// ====================== CONFIG ======================
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

// Fallbacks in case variables are not set
$href = isset($href) ? $href : '/assets/css/styles.css';

$User = "Department Master";
$PageTitle = $User . " Details";
$PageTitleFolder = "Masters";

// Example categories (can be fetched from DB later)
$categories = ["HR", "Finance", "IT", "Production", "Marketing"];
$status_options = ["Active", "Inactive"];

$api = API_DEPARTMENT;
$bodyContent = ['methodType' => 'getDropDowns'];
 $result = CallAPI($api, "GET", "", $bodyContent);
 $Departments = $result->Data->Departments;
 $SubDepartments = $result->Data->SubDepartments;
?>

<!DOCTYPE html>
<html lang="en">
<link href="<?php echo $href ?>" rel="stylesheet" type="text/css" />

<head>
    <?php include($IncludesPath . "title-meta.php"); ?>
    <?php include($IncludesPath . "DbLinks.php"); ?>
    <?php include($IncludesPath . "head-css.php"); ?>
</head>

<body data-sidebar="dark">

    <?php include($IncludesPath . "PreLoader.php"); ?>

    <!-- Begin page -->
    <div id="layout-wrapper">

        <?php
        include($IncludesPath . "topbar.php");
        include($IncludesPath . "sidebar.php");
        ?>

        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">

                    <?php if ($Status != '') { ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="mdi mdi-check-all mr-2"></i>
                            <?php echo $Status;
                            $_SESSION['Status'] = ''; ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php } ?>

                    <?php include($IncludesPath . "page-title.php"); ?>

                    <!-- ================= DEPARTMENT FORM ================= -->
                    <form id="departmentForm">
                        <!-- Hidden field for auto-increment DepartmentID -->
                        <input type="hidden" id="department_id" name="department_id" value="">

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">

                                        <h4 class="mb-3">Department Details</h4>
                                        <div class="row">

                                            <!-- Department Code (User Enters) -->
                                            <!-- <div class="col-md-6">
                                                <label>Department Code</label>
                                                <input type="text" class="form-control" id="department_code" name="department_code"
                                                    placeholder="Enter Department Code">
                                            </div> -->

                                            <div class="col-md-6">
                                                <label>Department Name</label>
                                                <select class="form-control" id="category" name="category">
                                                    <option value="">Select Department</option>
                                                    <?php 
                                                    if(!empty($Departments)){
                                                        foreach($Departments as $DepartmentRow){?>
                                                             <option value="<?php echo $DepartmentRow->Value;?>"><?php echo $DepartmentRow->DepartmentName;?></option>
                                                    <?php }
                                                    } ?>
                                                </select>
                                            </div>

                                            <!-- Department Name -->
                                            <div class="col-md-6">
                                                <label>Sub Department Name</label>
                                                <select class="form-control" id="department_name" name="department_name">
                                                    <option value="">Select Sub Department</option>
                                                    <?php 
                                                    if(!empty($SubDepartments)){
                                                        foreach($SubDepartments as $SubDepartmentRow){?>
                                                             <option value="<?php echo $SubDepartmentRow->Value;?>"><?php echo $SubDepartmentRow->SubDepartmentName;?></option>
                                                    <?php }
                                                    } ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row mt-3">

                                            <div class="col-md-6">
                                                <label>Status</label>
                                                <select class="form-control" id="stat" name="stat">
                                                    <option value="">Select Status</option>
                                                    <?php foreach ($status_options as $st): ?>
                                                        <option value="<?= $st ?>"><?= $st ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- ================= BUTTONS ================= -->
                                        <div class="text-center mt-4">
                                            <button type="button" id="btnSaveDepartment" class="btn btn-primary">Save</button>
                                            <button type="button" id="btnUpdateDepartment" class="btn btn-primary" style="display:none;">
                                                <i class="fa fa-edit"></i> Update
                                            </button>
                                        </div>

                                    </div> <!-- end card-body -->
                                </div> <!-- end card -->
                            </div> <!-- end col -->
                        </div> <!-- end row -->
                    </form>


                    <!-- Department list -->
                    <div id="departmentListDiv" class="mt-4"></div>

                </div> <!-- container-fluid -->
                <?php include($IncludesPath . "footer.php"); ?>
            </div> <!-- page-content -->
        </div> <!-- main-content -->

    </div> <!-- END layout-wrapper -->

    <?php include($IncludesPath . "vendor-scripts.php"); ?>
    <?php include($IncludesPath . "NewScripts.php"); ?>
    <?php include($IncludesPath . "ShowDataTable.php"); ?>



    <script>
        $(document).ready(function() {
            $('#category,#department_name,#stat').select2();
            loadDepartments(); // Initial load of department list

            // ================== SAVE DEPARTMENT ==================
            $('#btnSaveDepartment').click(function(e) {
                e.preventDefault();
                const formData = $('#departmentForm').serialize() + "&methodType=save";
                $.ajax({
                    url: 'Ajax/AjaxDepartmentMaster.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            alert(res.msg);
                            loadDepartments();
                            resetForm(); // ✅ reset and switch back to save mode
                        } else {
                            alert('Error: ' + res.msg);
                        }
                    },
                    error: function() {
                        alert('Save request failed!');
                    }
                });
            });

            // ================== UPDATE DEPARTMENT ==================
            $('#btnUpdateDepartment').click(function(e) {
                e.preventDefault();
                const formData = $('#departmentForm').serialize() + '&methodType=update';
                $.ajax({
                    url: 'Ajax/AjaxDepartmentMaster.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            alert(res.msg);
                            loadDepartments();
                            resetForm(); // switch back to save mode
                        } else {
                            alert('Error: ' + res.msg);
                        }
                    },
                    error: function() {
                        alert('Update request failed!');
                    }
                });
            });

            // ================== EDIT DEPARTMENT ==================
            $(document).on('click', '.btnEdit', function() {
                var deptId = $(this).data('id');
                $.ajax({
                    url: 'Ajax/AjaxDepartmentMaster.php', //  correct path
                    type: 'POST',
                    data: {
                        methodType: 'getSingle',
                        department_id: deptId
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success' && res.data) {
                            $('#department_id').val(res.data.DepartmentID);
                            $('#department_code').val(res.data.DepartmentCode);
                            $('#department_name').val(res.data.DepartmentName);
                            $('#category').val(res.data.Category);
                            $('#stat').val(res.data.Status);

                            // Switch form to EDIT MODE
                            $('#btnSaveDepartment').hide();
                            $('#btnUpdateDepartment').show();
                            <?php 
                            if($_SESSION['ClientID'] == 'SAIMEV3'){ ?>
                                getSubDeportments(res.data.Category, res.data.DepartmentName);
                            <?php }?>
                        } else {
                            alert('Department not found.');
                        }
                    },
                    error: function() {
                        alert('Failed to fetch department details.');
                    }
                });
            });

            // ================== DELETE DEPARTMENT ==================
            $(document).on('click', '.btnDelete', function() {
                if (!confirm('Are you sure you want to delete this department?')) return;

                const department_id = $(this).data('id');

                $.ajax({
                    url: 'Ajax/AjaxDepartmentMaster.php',
                    type: 'POST',
                    data: {
                        methodType: 'delete',
                        department_id: department_id
                    },

                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            alert(res.msg);
                            loadDepartments();
                        } else {
                            alert('Error: ' + res.msg);
                        }
                    },
                    error: function(xhr) {
                        console.error('Delete failed:', xhr.responseText);
                    }
                });
            });

            // // ================== LOAD DEPARTMENT LIST ==================
            // function loadDepartments() {
            //     $.ajax({
            //         url: 'Ajax/AjaxDepartmentMaster.php',
            //         type: 'POST',
            //         data: {
            //             methodType: 'load'
            //         },
            //         success: function(html) {
            //             $('#departmentListDiv').html(html);
            //         }
            //     });
            // }




            // =======================================================
            // ADDING SEARCH + MORE SEARCH + LOAD ALL + EXCEL
            // =======================================================

            // More Search
            $(document).on("click", "#btnMoreSearch", function() {
                $(this).data("moresearch", true);

                let searchKey = $("#tblDepartment_filter input").val();

                if (searchKey.length > 0) {
                    loadDepartments({
                        searchKey: searchKey,
                        loadAll: false
                    });
                }
            });

            // Load All
            $(document).on("click", "#btnLoadAll", function() {
                $(this).data("loadall", true);

                loadDepartments({
                    searchKey: "",
                    loadAll: true
                });
            });


            // ================== SEARCH / LOAD ALL ==================
            function loadDepartments(options = {}) {

                let searchKey = options.searchKey || "";
                let loadAll = options.loadAll || false;

                $.ajax({
                    url: "Ajax/AjaxDepartmentMaster.php",
                    type: "POST",
                    data: {
                        methodType: "load",
                        searchkey: searchKey,
                        loadAll: loadAll
                    },
                    dataType: "json",
                    success: function(res) {

                        if (res.status === "success") {

                            if ($.fn.DataTable.isDataTable("#tblDepartment")) {
                                $("#tblDepartment").DataTable().destroy();
                            }

                            $("#departmentListDiv").html(res.html);

                            setTimeout(() => fnDepartmentDataTable(), 100);

                        } else {
                            $("#departmentListDiv").html(
                                `<div class='alert alert-warning'>${res.msg}</div>`
                            );
                        }
                    }
                });
            }



            // ================== DATATABLE CONFIG ==================
            function fnDepartmentDataTable() {

                table = $("#tblDepartment").DataTable({
                    scrollY: "600px",
                    scrollX: true,
                    sScrollXInner: "100%",
                    paging: true,
                    pageLength: 25,
                    lengthMenu: [10, 25, 50, 100, -1],
                    destroy: true,
                    fixedColumns: true,
                    scrollCollapse: true,
                    dom: '<"d-flex justify-content-end align-items-center mb-2"fB>rtip',
                    buttons: [{
                        extend: 'excel',
                        text: 'Download Excel',
                        className: 'btnExcelDataTable btn btn-success',
                        title: 'Department List',
                        footer: true,
                        exportOptions: {
                            columns: ':visible'
                        }
                    }]
                });

                // ---------------------------------------
                // ADD MORE SEARCH + LOAD ALL (SAME STYLE)
                // ---------------------------------------
                setTimeout(() => {

                    // More Search
                    if (!$("#btnMoreSearch").length) {
                        $('<button class="btn btn-primary ml-2" id="btnMoreSearch">More Search</button>')
                            .insertAfter("#tblDepartment_filter label");
                    }

                    // Load All
                    if (!$("#btnLoadAll").length) {
                        $('<button class="btn btn-primary ml-2" id="btnLoadAll">Load All</button>')
                            .insertAfter("#btnMoreSearch");
                    }

                }, 200);
            }

            // ================== RESET FORM FUNCTION ==================
            function resetForm() {
                $('#departmentForm')[0].reset();
                $('#department_id').val('');
                $('#btnSaveDepartment').show();
                $('#btnUpdateDepartment').hide();
            }

            // ================== AUTO LOAD IF EDIT PAGE (OPTIONAL) ==================
            const urlParams = new URLSearchParams(window.location.search);
            const deptId = urlParams.get('id');
            if (deptId) {
                $.ajax({
                    url: 'Ajax/AjaxDepartmentMaster.php',
                    type: 'POST',
                    data: {
                        methodType: 'getSingle',
                        department_id: deptId
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success' && res.data) {
                            $('#department_id').val(res.data.DepartmentID);
                            $('#department_code').val(res.data.DepartmentCode);
                            $('#department_name').val(res.data.DepartmentName);
                            $('#category').val(res.data.Category);
                            $('#stat').val(res.data.Status);
                            $('#btnSaveDepartment').hide();
                            $('#btnUpdateDepartment').show();
                        }
                    }
                });
            }

        });
        <?php 
             if($_SESSION['ClientID'] == 'SAIMEV3'){ ?>
                $(document).on("change", "#category", function() { 
                    getSubDeportments($(this).val());
                });
                function getSubDeportments(DepartmentsValue, SubDepartmentID = ''){
                    if(DepartmentsValue){
                        $.ajax({
                            url: 'Ajax/AjaxDepartmentMaster.php',
                            type: 'POST',
                            data: {
                                methodType: 'getSubDepartments',
                                DepartmentValue: DepartmentsValue
                            },
                            dataType: 'json',
                            success: function(res) {
                                var list = "<option value=''>Select Sub Department</option>";
                                if (res.status === 'success' && res.data) {                       
                                    $.each(res.data, function(index, value) {
                                        var selected = '';
                                        if(SubDepartmentID && SubDepartmentID == value.Value){
                                            selected = "selected";
                                        }
                                        list += "<option value='" + value.Value + "' "+selected+">" + value.SubDepartmentName + "</option>";
                                    });
                                }
                                $('#department_name').select2('destroy').html(list).select2();

                                /* setTimeout(function() {
                                    $('#department_name').select2('destroy').val(DepartmentID).select2();
                                }, 500); */
                            },
                            error: function(){
                                console.log('Error');
                                var list = "<option value=''>Select Sub Department</option>";
                                $('#department_name').select2('destroy').html(list).select2();
                            }
                        });

                    }
                }
           <?php  }
       ?>
    </script>

</body>

</html>


</body>

</html>