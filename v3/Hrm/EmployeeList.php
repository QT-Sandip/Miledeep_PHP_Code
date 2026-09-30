<?php 
$PageTitle="Employee List";
$PageTitleFolder="HRM";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
$SqlWorkerCategory = "SELECT * FROM `EmpWorkerSubCategories`";
$SqlWorkerCategoryResult = $iconn->query($SqlWorkerCategory);

$SqlDeptList="SELECT DeptID,DepartmentName FROM HrmDepartments";
$StmtDept=$iconn->query($SqlDeptList);
?>

<!doctype html>
<html lang="en">
<link href="https://serp.silverlights.in/Clients/Jlsf/User.css"  rel="stylesheet" type="text/css" />


    <head>

         <?php include ($IncludesPath."title-meta.php"); ?>

        <!-- DataTables -->
        <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-select-bs4/css//select.bootstrap4.min.css" rel="stylesheet" type="text/css" />

        <!-- Responsive datatable examples -->
        <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />


        <link href="<?php echo $BaseDist ?>assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet">
        <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css" rel="stylesheet">
        <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css" rel="stylesheet" />
        <?php include ($IncludesPath."head-css.php");?>

    </head>

    <body data-sidebar="dark">

     <?php include ($IncludesPath."PreLoader.php"); ?>



        <!-- Begin page -->
        <div id="layout-wrapper">

            <?php
            include ($IncludesPath."topbar.php");
            include ($IncludesPath."sidebar.php");
            ?>

            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">

                <div class="page-content">
                    <div class="container-fluid">

                        <?php include ($IncludesPath."page-title.php");?>

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">

                                        <div class="row">
                                            <div class="form-group col-lg-3">

                                                <select class="form-control select2" name='EmpCategory' id="EmpCategory" required>
                                                    <option value="">Select Category </option>
                                                    <option <?php if ($RowEmployeeDetails['EmpCategory'] == "Staff") { ?>selected="selected" <?php } ?> value="Staff">Staff</option>
                                                    <option <?php if ($RowEmployeeDetails['EmpCategory'] == "Worker") { ?>selected="selected" <?php } ?> value="Worker">Worker</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-lg-3" id="worker-type-select">
                                                <select class="form-control select2" name='WorkerCategory' id="WorkerCategory">
                                                    <option value="">Select Worker Category</option>
                                                        <?php while ($RowWorkerType = $SqlWorkerCategoryResult->fetch_array()) {
                                                        ?>
                                                            <option value="<?php echo $RowWorkerType['CategoryID']; ?>">
                                                                <?php echo $RowWorkerType['CategoryName']; ?>
                                                            </option>

                                                    <?php } ?>
                                                </select>
                                            </div>
                                                <div class="col-lg-3">
                                                    <div class="form-group">
                                                       <select class="form-control select2" name="DeptID" id="DeptID">
                                                            <option value="">Select Department</option>

                                                            <?php while($DataRow=$StmtDept->fetch_array()){?>

                                                <option value="<?php echo $DataRow['DeptID'] ;?>" >
                                                <?php  echo $DataRow['DepartmentName']; ?></option>
                                                <?php    } ?>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-lg-3">
                                                    <div class="form-group">

                                                        <select class="form-control select2"  name="SubDeptID"
                                                        id="SubDeptID">
                                                            <option value="">Select SubDepartment</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-lg-3">
                                                    <div class="form-group">

                                                         <select class="form-control select2"  name="EmpStatus"
                                                        id="EmpStatus">

                                                            <option value="1">Active</option>
                                                            <option value="0">In Active</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                </div>
                                                <div align="center" class="col-lg-3">

                                                       <button id="list" align="center" type="button" class="btn btn-primary waves-effect waves-light">
                                                Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                                            </button>


                                                <!--     <a href="EmployeeCreate.php" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-plus"></i> New Employee</a>-->


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
                                                    <th>Bio ID</th>
                                                    <th>Name</th>
                                                    <th>EmpCategory</th>
                                                    <th>WorkerSubCategory</th>
                                                    <th>Department</th>
                                                    <th>SubDepartment</th>
                                                    <th>Designation</th>
                                                    <th>Contractor</th>
                                                    <th>Status</th>
                                                    <th>Tools</th>
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

                 <?php include ($IncludesPath."footer.php");?>

            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->



        <?php include ($IncludesPath."vendor-scripts.php");?>


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
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

        <!-- Datatable init js -->
        <script src="<?php echo $BaseDist ?>assets/js/pages/datatables.init.js"></script>

        <script src="<?php echo $BaseDist ?>assets/js/app.js"></script>
        <script>
            $(document).on('change','#DeptID',function(e){
               e.preventDefault();
               var DeptId=$(this).val();
               var Type='SubDept';

                $.ajax({
                    url:'Ajax/AjDropDowns',
                    type:'POST',
                    data:{'DeptId':DeptId,'Type':Type},
                    success:function(data){
                        $("#SubDeptID").html(data);
                    }
                });

            });

            $(document).on('click','#list',function(e){
                e.preventDefault();
                if ($.fn.DataTable.isDataTable('#key-datatable')) {
                    $('#key-datatable').DataTable().destroy();
                }
                loadDynamicTable();
            });

            $(document).on('click', '.EmpActive', function(e){
                e.preventDefault();

                var EmpId =$(this).data('id');
                var EmpStatus = $(this).val();
                var Type = 'EmpStatus';

                var DeptID=$('#DeptID').val();
                var SubDeptID=$('#SubDeptID').val();
                var EmpCategory=$('#EmpCategory').val();
                var WorkerCategory=$('#WorkerCategory').val();

                $.ajax({

                    type: 'POST',
                    url: 'Ajax/AjEmpList.php',
                    data: {'EmpID': EmpId,'EmpStatus':EmpStatus, 'Type' : Type, 'DeptID':DeptID,'SubDeptID': SubDeptID, 'EmpCategory':EmpCategory, 'WorkerCategory':WorkerCategory},

                    success: function(data){
                        if ($.fn.DataTable.isDataTable('#key-datatable')) {
                            $('#key-datatable').DataTable().destroy();
                        }
                        loadDynamicTable();
                    }
                });

            });

            $(document).on('click', '.Delete', function(e){
                e.preventDefault();

                var DeptID=$('#DeptID').val();
                var SubDeptID=$('#SubDeptID').val();
                var EmpId =$(this).data('id');
                var EmpStatus = $(this).val();
                var Type = 'DeleteEmployee';

                var r = confirm("Are You Sure You Want To Delete?");
                if (r == true) {
                    $.ajax({

                        type: 'POST',
                        url: 'Ajax/AjEmpList.php',
                        data: {'EmpID': EmpId,'EmpStatus': EmpStatus,'Type': Type,'DeptID': DeptID,'SubDeptID': SubDeptID},

                        success: function(data){
                            if ($.fn.DataTable.isDataTable('#key-datatable')) {
                                $('#key-datatable').DataTable().destroy();
                            }
                            loadDynamicTable();
                        }
                    });
                }

            });

            $(document).ready(function () {

                if ($.fn.DataTable.isDataTable('#key-datatable')) {
                    $('#key-datatable').DataTable().destroy();
                }
                table = "";
                loadDynamicTable();

            });

            $('#EmpCategory').on('change', function (e) {
              const EmpCategory = $(this).val();
              if(EmpCategory == 'Worker'){
                $('#worker-type-select').show();
              }else{
                // Reset Select2 value
                $('#worker-type-select select') // or adjust selector if needed
                  .val(null)                     // clear value
                  .trigger('change');           // notify Select2
                $('#worker-type-select').hide();
              }
            });

            function loadDynamicTable() {
                var DeptID=$('#DeptID').val();
                var SubDeptID=$('#SubDeptID').val();
                var EmpStatus=$('#EmpStatus').val();
                var EmpCategory=$('#EmpCategory').val();
                var WorkerCategory=$('#WorkerCategory').val();
                table = $('#key-datatable').DataTable({
                    "lengthMenu": [[20,50,100], [20,50,100]],
                    scrollX: true,
                    scrollY: '400px',
                    scrollCollapse: true,
                    paging: true,
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: 'Ajax/AjaxGetEmpList',
                        type: 'POST',
                        data:{'DeptID':DeptID,'SubDeptID': SubDeptID,'EmpStatus': EmpStatus, 'EmpCategory':EmpCategory, 'WorkerCategory':WorkerCategory},
                    },
                    columns: [
                        { data: 'BioMetricID' },
                        { data: 'EmpName' },
                        { data: 'EmpCategory' },
                        { data: 'WorkerSubCategory' },
                        { data: 'DepartmentName' },
                        { data: 'SubDeptName' },
                        { data: 'Designation' },
                        { data: 'ContractorName' },
                        { data: 'Status' },
                        { data: 'Tools' }
                    ],
                    columnDefs: [
                        { targets: [6], orderable: false, searchable: false },
                        { targets: [7], orderable: false, searchable: false },

                    ],
                    drawCallback: function () {

                    }
                });
            }
        </script>
    </body>
</html>
