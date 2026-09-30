<?php
$Status = isset($_SESSION['Status']) ? $_SESSION['Status'] : '';
// ====================== CONFIG ======================
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

// Fallbacks in case variables are not set
$IncludesPath = isset($IncludesPath) ? $IncludesPath : $_SERVER['DOCUMENT_ROOT'] . '/includes/';
$href = isset($href) ? $href : '/assets/css/styles.css';

$User = "Proforma Register";
$PageTitle = $User . " Details";
$PageTitleFolder = "Reports";

// Dropdown options
$layout_options = ["Summary", "Detailed", "Compact"];
$output_options = ["PDF", "Excel", "Preview"];
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

                    <!-- ================= PROFORMA REGISTER FORM ================= -->
                    <form id="proformaForm">
                        <input type="hidden" id="proforma_id" name="proforma_id" value="">

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">

                                        <h4 class="mb-3">Proforma Register Details</h4>

                                        <div class="row">
                                            <!-- Date Options -->
                                            <div class="col-md-6">
                                                <label>Date Options</label>
                                                <select class="form-control" id="date_option" name="date_option">
                                                    <option value="">Select Date Option</option>
                                                    <option value="today">Today</option>
                                                    <option value="this_week">This Week</option>
                                                    <option value="this_month">This Month</option>
                                                    <option value="previous_month">Previous Month</option> <!-- 👈 Add this -->
                                                    <option value="custom">Custom</option>
                                                </select>

                                            </div>
                                        </div>
                                        <div class="row mt-2">

                                            <!-- Start Date -->
                                            <div class="col-md-6">
                                                <label>Start Date</label>
                                                <input type="date" class="form-control" id="start_date" name="start_date">
                                            </div>

                                            <!-- End Date -->
                                            <div class="col-md-6">
                                                <label>End Date</label>
                                                <input type="date" class="form-control" id="end_date" name="end_date">
                                            </div>
                                        </div>

                                        <div class="row mt-3">
                                            <!-- Layout Dropdown -->
                                            <div class="col-md-6">
                                                <label>Layout</label>
                                                <select class="form-control" id="layout" name="layout">
                                                    <option value="">Select Layout</option>
                                                    <?php foreach ($layout_options as $layout): ?>
                                                        <option value="<?= $layout ?>"><?= $layout ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <!-- Output Dropdown -->
                                            <div class="col-md-6">
                                                <label>Output</label>
                                                <select class="form-control" id="output" name="output">
                                                    <option value="">Select Output</option>
                                                    <?php foreach ($output_options as $opt): ?>
                                                        <option value="<?= $opt ?>"><?= $opt ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- ================= BUTTONS ================= -->
                                        <div class="text-center mt-4">
                                            <button type="button" id="btnSaveProforma" class="btn btn-primary">Save</button>
                                            <button type="button" id="btnUpdateProforma" class="btn btn-primary" style="display:none;">
                                                Update
                                            </button>
                                        </div>

                                    </div> <!-- end card-body -->
                                </div> <!-- end card -->
                            </div> <!-- end col -->
                        </div> <!-- end row -->
                    </form>

                    <!-- Proforma List -->
                    <div id="proformaListDiv" class="mt-4"></div>

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

            // ================== DATE OPTION HANDLER ==================
            $('#date_option').on('change', function() {
                const option = $(this).val();
                const today = new Date();
                let start = '',
                    end = '';

                if (option === 'today') {
                    start = end = today.toISOString().split('T')[0];
                } else if (option === 'this_week') {
                    const first = new Date(today);
                    const last = new Date(today);
                    const day = today.getDay(); // Sunday = 0
                    first.setDate(today.getDate() - day + 1); // Monday
                    last.setDate(first.getDate() + 6); // Sunday
                    start = first.toISOString().split('T')[0];
                    end = last.toISOString().split('T')[0];
                } else if (option === 'this_month') {
                    const y = today.getFullYear();
                    const m = today.getMonth();
                    start = new Date(y, m, 1).toISOString().split('T')[0];
                    end = new Date(y, m + 1, 0).toISOString().split('T')[0];
                } else if (option === 'previous_month') {
                    const y = today.getFullYear();
                    const m = today.getMonth();
                    start = new Date(y, m - 1, 1).toISOString().split('T')[0];
                    end = new Date(y, m, 0).toISOString().split('T')[0];
                } else if (option === 'custom') {
                    start = '';
                    end = '';
                }

                $('#start_date').val(start);
                $('#end_date').val(end);

                // Enable/disable date inputs
                if (option === 'custom') {
                    $('#start_date, #end_date').prop('disabled', false);
                } else {
                    $('#start_date, #end_date').prop('disabled', true);
                }
            });


            // ================== LOAD PROFORMA LIST ==================
            loadProformas();

            // ================== SAVE PROFORMA ==================
            $('#btnSaveProforma').click(function(e) {
                e.preventDefault();

                ensureDateRange();
                const formData = $('#proformaForm').serialize() + '&methodType=save';
                $.ajax({
                    url: 'Ajax/Ajax-PorformaRegister.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            alert(res.msg);
                            loadProformas();
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

            // ================== UPDATE PROFORMA ==================
            $('#btnUpdateProforma').click(function(e) {
                e.preventDefault();

                ensureDateRange(); //  make sure start/end dates are filled

                const formData = $('#proformaForm').serialize() + '&methodType=update';
                $.ajax({
                    url: 'Ajax/Ajax-PorformaRegister.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            alert(res.msg);
                            loadProformas();
                            resetForm();
                        } else {
                            alert('Error: ' + res.msg);
                        }
                    },
                    error: function() {
                        alert('Update request failed!');
                    }
                });
            });

            // ================== EDIT PROFORMA ==================
            $(document).on('click', '.btnEdit', function() {
                var proformaId = $(this).data('id');
                $.ajax({
                    url: 'Ajax/Ajax-PorformaRegister.php',
                    type: 'POST',
                    data: {
                        methodType: 'getSingle',
                        proforma_id: proformaId
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success' && res.data) {
                            $('#proforma_id').val(res.data.ProformaID);
                            $('#date_option').val(res.data.DateOption);
                            $('#start_date').val(res.data.StartDate);
                            $('#end_date').val(res.data.EndDate);
                            $('#layout').val(res.data.Layout);
                            $('#output').val(res.data.Output);

                            // Enable/disable date inputs accordingly
                            if (res.data.DateOption === 'custom') {
                                $('#start_date, #end_date').prop('disabled', false);
                            } else {
                                $('#start_date, #end_date').prop('disabled', true);
                            }

                            $('#btnSaveProforma').hide();
                            $('#btnUpdateProforma').show();
                        } else {
                            alert('Proforma record not found.');
                        }
                    },
                    error: function() {
                        alert('Failed to fetch details.');
                    }
                });
            });

            // ================== DELETE PROFORMA ==================
            $(document).on('click', '.btnDelete', function() {
                if (!confirm('Are you sure you want to delete this record?')) return;

                const proforma_id = $(this).data('id');

                $.ajax({
                    url: 'Ajax/Ajax-PorformaRegister.php',
                    type: 'POST',
                    data: {
                        methodType: 'delete',
                        proforma_id: proforma_id
                    },
                    dataType: 'json',
                    success: function(res) {
                        if (res.status === 'success') {
                            alert(res.msg);
                            loadProformas();
                        } else {
                            alert('Error: ' + res.msg);
                        }
                    },
                    error: function(xhr) {
                        console.error('Delete failed:', xhr.responseText);
                    }
                });
            });

            // ================== LOAD PROFORMA LIST ==================
            function loadProformas() {
                $.ajax({
                    url: 'Ajax/Ajax-PorformaRegister.php',
                    type: 'POST',
                    data: {
                        methodType: 'load'
                    },
                    success: function(html) {
                        $('#proformaListDiv').html(html);
                    }
                });
            }

            // ================== RESET FORM FUNCTION ==================
            function resetForm() {
                $('#proformaForm')[0].reset();
                $('#proforma_id').val('');
                $('#btnSaveProforma').show();
                $('#btnUpdateProforma').hide();
                $('#start_date, #end_date').prop('disabled', true);
            }

            // ================== ENSURE DATE RANGE ==================
            function ensureDateRange() {
                const option = $('#date_option').val();
                const today = new Date();
                let start = $('#start_date').val();
                let end = $('#end_date').val();

                // If empty, compute automatically
                if (!start || !end) {
                    if (option === 'today') {
                        start = end = today.toISOString().split('T')[0];
                    } else if (option === 'this_week') {
                        const first = new Date(today);
                        const last = new Date(today);
                        const day = today.getDay();
                        first.setDate(today.getDate() - day + 1);
                        last.setDate(first.getDate() + 6);
                        start = first.toISOString().split('T')[0];
                        end = last.toISOString().split('T')[0];
                    } else if (option === 'this_month') {
                        const y = today.getFullYear();
                        const m = today.getMonth();
                        start = new Date(y, m, 1).toISOString().split('T')[0];
                        end = new Date(y, m + 1, 0).toISOString().split('T')[0];
                    } else if (option === 'previous_month') {
                        const y = today.getFullYear();
                        const m = today.getMonth();
                        start = new Date(y, m - 1, 1).toISOString().split('T')[0];
                        end = new Date(y, m, 0).toISOString().split('T')[0];
                    }
                    $('#start_date').val(start);
                    $('#end_date').val(end);
                }
            }

        });
    </script>


</body>

</html>