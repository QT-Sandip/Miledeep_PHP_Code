<?php
$Status = isset($_SESSION['Status']) ? $_SESSION['Status'] : '';
// ====================== CONFIG ======================
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

// Fallbacks in case variables are not set
$IncludesPath = isset($IncludesPath) ? $IncludesPath : $_SERVER['DOCUMENT_ROOT'] . '/includes/';
$href = isset($href) ? $href : '/assets/css/styles.css';

$User = "Brand Master";
$PageTitle = $User . " Details";
$PageTitleFolder = "Masters";

// Example categories (can be fetched from DB later)
$categories = ["Clothing", "Electronics", "Food", "Automobile", "Home Appliances"];
$status_options = ["Active", "Inactive"];
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

                    <!-- ================= BRAND FORM ================= -->
                    <form id="brandForm" enctype="multipart/form-data">
                        <!-- Hidden field for auto-increment BrandID -->
                        <input type="hidden" id="brand_db_id" name="brand_db_id" value="">

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">

                                        <h4 class="mb-3">Brand Details</h4>
                                        <div class="row">

                                            <!-- Brand Code -->
                                            <div class="col-md-6">
                                                <label>Brand Code</label>
                                                <input type="text" class="form-control" id="brand_code" name="brand_code"
                                                    placeholder="Enter Brand Code">
                                            </div>

                                            <!-- Brand Name -->
                                            <div class="col-md-6">
                                                <label>Brand Name</label>
                                                <input type="text" class="form-control" id="brand_name" name="brand_name"
                                                    placeholder="Enter Brand Name">
                                            </div>
                                        </div>

                                        <!-- <div class="row mt-3">
                                            <div class="col-md-6">
                                                <label>Category</label>
                                                <select class="form-control" id="category" name="category">
                                                    <option value="">Select Category</option>
                                                    <?php foreach ($categories as $cat): ?>
                                                        <option value="<?= $cat ?>"><?= $cat ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div class="col-md-6">
                                                <label>Status</label>
                                                <select class="form-control" id="stat" name="stat">
                                                    <option value="">Select Status</option>
                                                    <?php foreach ($status_options as $st): ?>
                                                        <option value="<?= $st ?>"><?= $st ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div> -->



                                        <div class="row mt-3">
                                            <div class="col-md-6">
                                                <label>Category</label>
                                                <select class="form-control" id="category" name="category">
                                                    <option value="">Select Category</option>
                                                     <option value="General">General</option>
                                                    <option value="Packaging_Related ">Packaging Related </option>
                                                    <option value="Sanitory">Sanitory</option>
                                                    <option value="Chemical">Chemical</option> 
                                                    <option value="Stationery">Stationery</option>
                                                    <option value="Uniforms">Uniforms</option>
                                                </select>
                                            </div>

                                            <div class="col-md-6">
                                                <label>Buyer Name</label>
                                                <input type="text" class="form-control" id="company_name" name="company_name"
                                                    placeholder="Enter Buyer Name">
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

                                            <!-- <div class="col-md-6">
                                                <label>Upload Logo</label>
                                                <input type="file" class="form-control" id="logo" name="logo">
                                            </div> -->
                                        </div>

                                        <!-- <div class="row mt-3">


                                            <div class="col-md-12">
                                                <label>Description</label>
                                                <textarea class="form-control" id="description" name="description"
                                                    placeholder="Enter Brand Description"></textarea>
                                            </div>
                                        </div> -->

                                        <!-- ================= BUTTONS ================= -->
                                        <div class="text-center mt-4">
                                            <button type="button" id="btnSaveBrand" class="btn btn-primary">Save</button>
                                            <button type="button" id="btnUpdateBrand" class="btn btn-primary" style="display:none;">
                                                <i class="fa fa-edit"></i> Update
                                            </button>
                                        </div>

                                    </div> <!-- end card-body -->
                                </div> <!-- end card -->
                            </div> <!-- end col -->
                        </div> <!-- end row -->
                    </form>


                    <!-- Brand list -->
                    <div id="brandListDiv" class="mt-4"></div>

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

            // ================== LOAD BRAND LIST ON PAGE LOAD ==================
            loadBrands();

            // ================== SAVE BRAND ==================
            $('#btnSaveBrand').click(function(e) {
                e.preventDefault();

                const formData = new FormData();
                formData.append('methodType', 'save');
                formData.append('BrandName', $('#brand_name').val());
                formData.append('BrandCode', $('#brand_code').val());
                formData.append('Category', $('#category').val());
                formData.append('CompanyName', $('#company_name').val());

                formData.append('Status', $('#stat').val());
                // formData.append('Description', $('#description').val());
                // formData.append('Logo', $('#logo')[0].files[0]);

                $.ajax({
                    url: 'Ajax/Ajax-Brand.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            alert(res.msg);
                            loadBrands();
                            resetForm();
                        } else {
                            alert('Error: ' + res.msg);
                        }
                    },
                    error: function() {
                        alert('Save request failed!');
                    }
                });
            });





            function fnShowDataTable() {
                // Use correct table ID
                const table = $('#tblBrand').DataTable({
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
                        title: 'Brand List',
                        footer: true,
                        exportOptions: {
                            columns: ':visible'
                        }
                    }]
                });

                // Add More Search and Load All buttons
                setTimeout(() => {
                    if (!$("#btnMoreSearch").length) {
                        $('<button class="btn btn-primary ml-2" id="btnMoreSearch">More Search</button>')
                            .insertAfter("#tblBrand_filter label");
                    }
                    if (!$("#btnLoadAll").length) {
                        $('<button class="btn btn-primary ml-2" id="btnLoadAll">Load All</button>')
                            .insertAfter("#btnMoreSearch");
                    }
                }, 200);
            }

            // ================== MORE SEARCH & LOAD ALL BUTTONS ==================
            $(document).on("click", "#btnLoadAll", function() {
                loadBrands({
                    loadAll: true
                });
            });

            $(document).on("click", "#btnMoreSearch", function() {
                const searchKey = $("#tblBrand_filter input").val();
                loadBrands({
                    searchKey: searchKey,
                    loadAll: false
                });
            });

            // ================== UPDATE BRAND ==================
            $('#btnUpdateBrand').click(function(e) {
                e.preventDefault();

                const formData = new FormData();
                formData.append('methodType', 'update');
                formData.append('BrandID', $('#brand_db_id').val());
                formData.append('BrandName', $('#brand_name').val());
                formData.append('BrandCode', $('#brand_code').val());
                formData.append('Category', $('#category').val());
                formData.append('CompanyName', $('#company_name').val());
                formData.append('Status', $('#stat').val());
                // formData.append('Description', $('#description').val());
                // formData.append('Logo', $('#logo')[0].files[0]);

                $.ajax({
                    url: 'Ajax/Ajax-Brand.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            alert(res.msg);
                            loadBrands();
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
            // ================== EDIT BRAND ==================
            $(document).on('click', '.btnEdit', function() {
                var brandId = $(this).data('id');
                $.ajax({
                    url: 'Ajax/Ajax-Brand.php',
                    type: 'POST',
                    data: {
                        methodType: 'getSingle',
                        BrandID: brandId
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success' && res.data) {
                            $('#brand_db_id').val(res.data.BrandID);
                            $('#brand_code').val(res.data.BrandCode);
                            $('#brand_name').val(res.data.BrandName);
                            $('#category').val(res.data.Category);
                            $('#company_name').val(res.data.CompanyName);
                            $('#stat').val(res.data.Status);
                            // $('#description').val(res.data.Description);

                            // Switch form to EDIT MODE
                            $('#btnSaveBrand').hide();
                            $('#btnUpdateBrand').show();
                        } else {
                            alert('Brand not found.');
                        }
                    },
                    error: function() {
                        alert('Failed to fetch brand details.');
                    }
                });
            });

            // ================== DELETE BRAND ==================
            $(document).on('click', '.btnDelete', function() {
                const brandId = $(this).data('id');

                if (!brandId) {
                    alert('Brand ID missing!');
                    return;
                }

                if (confirm('Are you sure you want to delete this brand?')) {
                    $.ajax({
                        url: 'Ajax/Ajax-Brand.php',
                        type: 'POST',
                        data: {
                            methodType: 'delete',
                            BrandID: brandId
                        },
                        dataType: 'json',
                        success: function(res) {
                            if (res.status === 'success') {
                                alert(res.msg);
                                loadBrands(); // reload table
                            } else {
                                alert('Error: ' + res.msg);
                            }
                        },
                        error: function() {
                            alert('Delete request failed!');
                        }
                    });
                }
            });

            // ================== LOAD BRAND LIST ==================
            function loadBrands(options = {}) {
                const searchKey = options.searchKey || "";
                const loadAll = options.loadAll || false;

                $.ajax({
                    url: 'Ajax/Ajax-Brand.php',
                    type: 'POST',
                    data: {
                        methodType: 'load',
                        searchKey: searchKey,
                        loadAll: loadAll
                    },
                    success: function(html) {
                        $('#brandListDiv').html(html);
                        fnShowDataTable(); // Initialize after table is inserted
                    }
                });
            }

            // ================== RESET FORM FUNCTION ==================
            function resetForm() {
                $('#brandForm')[0].reset();
                $('#brand_db_id').val('');
                $('#btnSaveBrand').show();
                $('#btnUpdateBrand').hide();
            }

            // ================== AUTO LOAD IF EDIT PAGE (OPTIONAL) ==================
            const urlParams = new URLSearchParams(window.location.search);
            const brandId = urlParams.get('id');
            if (brandId) {
                $.ajax({
                    url: 'Ajax/Ajax-Brand.php',
                    type: 'POST',
                    data: {
                        methodType: 'getSingle',
                        BrandID: brandId
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success' && res.data) {
                            $('#brand_db_id').val(res.data.BrandID);
                            $('#brand_code').val(res.data.BrandCode);
                            $('#brand_name').val(res.data.BrandName);
                            $('#category').val(res.data.Category);
                            $('#company_name').val(res.data.CompanyName);

                            $('#stat').val(res.data.Status);
                            // $('#description').val(res.data.Description);
                            $('#btnSaveBrand').hide();
                            $('#btnUpdateBrand').show();
                        }
                    }
                });
            }

        });
    </script>

</body>

</html>