<?php
$PageTitle = "Employee Salary Details";
$PageTitleFolder = "HRM";

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
$SqlDeptList = "SELECT DeptID,DepartmentName FROM HrmDepartments";
$StmtDept = $iconn->query($SqlDeptList);
?>

<!doctype html>
<html lang="en">

<head>

    <?php include($IncludesPath . "title-meta.php"); ?>

    <!-- DataTables -->
    <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css"
        rel="stylesheet" type="text/css" />
    <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css"
        rel="stylesheet" type="text/css" />
    <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-select-bs4/css//select.bootstrap4.min.css"
        rel="stylesheet" type="text/css" />

    <!-- Responsive datatable examples -->
    <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css"
        rel="stylesheet" type="text/css" />


    <link href="<?php echo $BaseDist ?>assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css"
        rel="stylesheet">
    <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css"
        rel="stylesheet">
    <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css"
        rel="stylesheet" />
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
                                        <div class="col-lg-4">
                                            <div class="form-group">
                                                <select class="form-control select2" name="DeptID" id="DeptID">
                                                    <option value="">Select Department</option>

                                                    <?php while ($DataRow = $StmtDept->fetch_array()) { ?>

                                                        <option value="<?php echo $DataRow['DeptID']; ?>">
                                                            <?php echo $DataRow['DepartmentName']; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-lg-4">
                                            <div class="form-group">

                                                <select class="form-control select2" name="SubDeptID" id="SubDeptID">
                                                    <option value="">Select SubDepartment</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div align="center" class="col-lg-3">

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
                                <div class="card-body" id="EmpList">
                                    <table id="key-datatable" class="table table-bordered" style="width:100%">
                                            <thead>
                                                <tr>                        
                                                    <th>Bio Id </th>
                                                  <th>Name</th>
                                                  <th>Designation</th>
                                                  <th>Basic</th>
                                                  <th>HRA</th>
                                                  <th>Daily Al</th>
                                                  <th>Special Al </th>
                                                  <th>Medical Al</th>
                                                  <th>Conveyence</th>
                                                  <th>Incentives</th>
                                                  <th>Others</th>
                                                  <th >Tools</th>
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


    <!-- Required datatable js -->
    <script src="<?php echo $BaseDist ?>assets/libs/select2/js/select2.min.js"></script>

    <script src="<?php echo $BaseDist ?>assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/bootstrap-maxlength/bootstrap-maxlength.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/js/pages/form-advanced.init.js"></script>


    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
    <!-- Buttons examples -->
    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/jszip/jszip.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/pdfmake/build/pdfmake.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/pdfmake/build/vfs_fonts.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons/js/buttons.html5.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons/js/buttons.print.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons/js/buttons.colVis.min.js"></script>

    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js"></script>
    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-select/js/dataTables.select.min.js"></script>

    <!-- Responsive examples -->
    <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
    <script
        src="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

    <!-- Datatable init js -->
    <script src="<?php echo $BaseDist ?>assets/js/pages/datatables.init.js"></script>

    <script src="<?php echo $BaseDist ?>assets/js/app.js"></script>
    <script>
        $(document).on('change', '#DeptID', function (e) {
            e.preventDefault();
            var DeptID = $(this).val();
            var Type = 'Dept';

            $.ajax({
                url: 'Ajax/AjEmpSalaryDetails',
                type: 'POST',
                data: { 'DeptID': DeptID, 'Type': Type },
                success: function (data) {
                    $("#SubDeptID").html(data);
                }
            });
        });
        $(document).on('click', '.emp_active', function (e) {
        e.preventDefault();
        //alert('hiiiii');

        var EmpId = $(this).data('id');
        var EmpStatus = $(this).val();
        var Type = 'EmpStatus';

        var DeptID = $('#DeptID').val();
        var SubDeptID = $('#SubDeptID').val();

        $.ajax({

            type: 'POST',
            url: 'Ajax/AjEmpSalaryDetails',
            data: { 'EmpID': EmpId, 'EmpStatus': EmpStatus, 'Type': Type, 'DeptID': DeptID, 'SubDeptID': SubDeptID },

            success: function (data) {
                if ($.fn.DataTable.isDataTable('#key-datatable')) {
                    $('#key-datatable').DataTable().destroy();
                }
                table = "";
                loadDynamicTable(); 
            }
        });

        //getRow(id,type);
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
            table = $('#key-datatable').DataTable({
                "lengthMenu": [[20,50,100], [20,50,100]],
                scrollX: true,
                scrollY: '400px',
                scrollCollapse: true,
                paging: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: 'Ajax/AjEmpSalaryDetails',
                    type: 'POST',
                    data:{'DeptID':DeptID,'SubDeptID': SubDeptID},
                },
                columns: [
                    { data: 'BioMetricID' },
                    { data: 'EmpName' },
                    { data: 'Designation' },
                    { data: 'Basic' },
                    { data: 'HRA' },
                    { data: 'DailyAllowance' },
                    { data: 'SpecialAllowance' },
                    { data: 'MedicalAllowance' },
                    { data: 'Conveyence' },
                    { data: 'Incentives' },
                    { data: 'Others' },
                    { data: 'Tools' }                        
                ],
                columnDefs: [
                    { targets: [3], orderable: false, searchable: false },
                    { targets: [4], orderable: false, searchable: false },
                    { targets: [5], orderable: false, searchable: false },
                    { targets: [6], orderable: false, searchable: false },
                    { targets: [7], orderable: false, searchable: false },                    
                    { targets: [8], orderable: false, searchable: false },
                    { targets: [9], orderable: false, searchable: false },
                    { targets: [10], orderable: false, searchable: false },
                    { targets: [11], orderable: false, searchable: false },                    

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

<script>
    


    




    


    function showData(data) {

        $("#EmpList").html(data);

        var Stable = $('#scroll-vertical-datatable').DataTable({
            "scrollY": "450px",
            "scrollCollapse": true,
            "paging": false,
            "language": {
                "paginate": {
                    "previous": "<i class='mdi mdi-chevron-left'>",
                    "next": "<i class='mdi mdi-chevron-right'>"
                }
            },
            "drawCallback": function () {
                $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
            },
            buttons: ['copy', 'excel', 'pdf', 'colvis']
        });

        Stable.buttons().container()
            .appendTo('#scroll-vertical-datatable_wrapper .col-md-6:eq(0)');
    }

</script>