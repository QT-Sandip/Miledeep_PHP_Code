<?php
$PageTitle = "Daily Attendance";
$PageTitleFolder = "HRM";
$PageTitleLink = "home";
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
$SqlDepartments = "SELECT DeptID,DepartmentName FROM HrmDepartments Order by DepartmentName";
$StmtDept = $iconn->query($SqlDepartments);

$Curdate = date("d-m-Y");

?>

<!doctype html>
<html lang="en">


<head>

    <?php include($IncludesPath . "title-meta.php"); 
          include "Includes/DbLinks.php";
          include($IncludesPath . "head-css.php"); ?>

</head>

<body data-sidebar="dark">

    <?php include($IncludesPath . "PreLoader.php"); ?>



    <!-- Begin page -->
    <div id="layout-wrapper">

        <?php
        include($IncludesPath . "topbar.php");
        include($IncludesPath . "sidebar.php");
        ?>

        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">

            <div class="page-content">
                <div class="container-fluid">

                    <?php include($IncludesPath . "page-title.php"); ?>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">

                                    <div class="row">

                                        <div class="form-group col-lg-3">

                                            <select class="form-control select2" id="DeptID" name="DeptID" required>
                                                <option value="">All Departments</option>
                                                <?php
                                                $SqlDeptList = "SELECT * FROM HrmDepartments Order by DepartmentName";
                                                $StmtDept = $iconn->query($SqlDeptList);
                                                while ($DeptRow = $StmtDept->fetch_array()) { ?>
                                                    <option value="<?php echo $DeptRow['DeptID']; ?>">
                                                        <?php echo $DeptRow['DepartmentName']; ?>
                                                    </option>

                                                <?php } ?>
                                            </select>
                                        </div>

                                        <div class="form-group col-lg-3">
                                            <select class="form-control select2" id="SubDeptID" name="SubDeptID"
                                                required>
                                                <option value="">All SubDepartments</option>
                                            </select>
                                        </div>



                                        <div class="form-group col-sm-3">

                                            <input type="date" value='<?php echo date('Y-m-d'); ?>' class="form-control"
                                                name="Date" id="Date">

                                            </input>

                                        </div>

                                        <div align="center" class="col-lg-12">

                                            <button id="list" align="center" type="button"
                                                class="btn btn-primary waves-effect waves-light">
                                                Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                                            </button>
                                        </div>





                                    </div>
                                    <!-- end row -->
                                </div>
                            </div>
                        </div>

                        <!-- end col -->
                    </div> <!-- end row -->

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body" id="ATT_DATA">

                                    <table id="key-datatable" class="table table-bordered" style="width:100%">
                                            <thead>
                                                <tr>                        
                                                    <th>Date</th>
                                                    <th>Bio ID</th>
                                                    <th>Name</th>
                                                    <th>Department</th>
                                                    <th>Shift</th>
                                                    <th>Time In</th>
                                                    <th>Time Out</th>
                                                    <th>Hours</th>
                                                </tr>
                                            </thead>
                                        </table>

                                </div> <!-- end card body-->
                            </div> <!-- end card -->
                        </div><!-- end col-->
                    </div>
                    <!-- end row-->


                </div> <!-- end card body-->
            </div> <!-- end card -->
        </div><!-- end col-->
    </div>
    <!-- end row-->

    </div> <!-- container-fluid -->
    </div>
    <!-- End Page-content -->

    <?php include($IncludesPath . "footer.php"); ?>

    </div>
    <!-- end main content-->

    </div>
    <!-- END layout-wrapper -->



    <?php include($IncludesPath . "vendor-scripts.php"); ?>

    <?php include "Includes/NewScripts.php";?>
    <!-- Required datatable js -->
    
    <script type="text/javascript">
        $(function () {
            $('.edit').click(function (e) {
                e.preventDefault();
                $('#edit').modal('show');
                var id = $(this).data('id');
                getRow(id);
            });

            $('.delete').click(function (e) {
                e.preventDefault();
                $('#delete').modal('show');
                var id = $(this).data('id');
                getRow(id);
            });
        });

        function getRow(id) {
            $.ajax({
                type: 'POST',
                url: 'attendance_row.php',
                data: { id: id },
                dataType: 'json',
                success: function (response) {
                    $('#datepicker_edit').val(response.date);
                    $('#attendance_date').html(response.date);
                    $('#edit_time_in').val(response.time_in);
                    $('#edit_time_out').val(response.time_out);
                    $('#attid').val(response.attid);
                    $('#employee_name').html(response.firstname + ' ' + response.lastname);
                    $('#del_attid').val(response.attid);
                    $('#del_employee_name').html(response.firstname + ' ' + response.lastname);
                }
            });
        }

        $(document).on('change', '#DeptID', function (e) {

            e.preventDefault();

            var DeptId = $(this).val();
            var Type = 'SubDept';
            $.ajax({
                url: 'Ajax/AjDropDowns',
                type: 'POST',
                data: { 'DeptId': DeptId, 'Type': Type },
                success: function (data) {
                    $("#SubDeptID").html(data);
                }
            })
        });

        $(document).on('click', '#list', function (e) {
            e.preventDefault();

            if ($.fn.DataTable.isDataTable('#key-datatable')) {
                $('#key-datatable').DataTable().destroy();
            }
            table = "";
            loadDynamicTable();
        });

        $(document).ready(function () {
                
            if ($.fn.DataTable.isDataTable('#key-datatable')) {
                $('#key-datatable').DataTable().destroy();
            }
            table = "";
            loadDynamicTable();

        });

        function loadDynamicTable() {   
            var DeptID = $('#DeptID').val();
            var SubDeptID = $('#SubDeptID').val();
            var Date = $('#Date').val();

            if (Date == '') {
                $("#DATE").focus();
                alert("Date Required");
                return;
            }             
            
            table = $('#key-datatable').DataTable({
                "lengthMenu": [[200,500,1000], [200,500,1000]],
                scrollX: true,
                scrollY: '400px',
                scrollCollapse: true,
                paging: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: 'Ajax/AjaxDailyAttendance',
                    type: 'POST',
                    data:{'DeptID':DeptID,'SubDeptID': SubDeptID, 'Date': Date, 'Type': 'List'},
                },
                columns: [
                    { data: 'Date' },
                    { data: 'BioMetricID' },
                    { data: 'EmpName' },
                    { data: 'DepartmentName' },
                    { data: 'ShiftName' },
                    { data: 'TimeIn' },
                    { data: 'TimeOut' },                    
                    { data: 'diff' }                    
                    
                ],
                columnDefs: [
                    { targets: [3], orderable: false, searchable: false },
                    { targets: [4], orderable: false, searchable: false },
                    { targets: [5], orderable: false, searchable: false },
                    { targets: [6], orderable: false, searchable: false },
                    { targets: [7], orderable: false, searchable: false }

                ],
                drawCallback: function () {
                    // // Initialize editable fields
                    // $('.editable').editable();
                }
            });

        }
    </script>
</body>

</html>