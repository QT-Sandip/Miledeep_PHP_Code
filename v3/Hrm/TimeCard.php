<?php
$PageTitle = "Time Card";
$PageTitleFolder = "HRM";

/*$year=date('Y');
$Currentyear=2021;
 $Startyear=$year-2;
 $endYear=$year+1;*/
$Currentyear = date('Y');


include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$SqlHrmDept = "SELECT DeptID,DepartmentName FROM `HrmDepartments`";
$StmtHrmDept = $iconn->query($SqlHrmDept);

$start = strtotime('first day of this month');

if (isset($_SESSION['YEAR']) == '') {
    $_SESSION['YEAR'] = date("Y");
    $_SESSION['MONTH'] = date("m");
}

$YEAR = $_SESSION['YEAR'];
$MONTH = $_SESSION['MONTH'];

if ($_GET['MONTH'] != '') {
    $month = $_GET['MONTH'];
} else {

    $month = date("m");
    $month = trim($month);

}

if ($_GET['YEAR'] != '') {
    $year = $_GET['YEAR'];
} else {

    $year = date("Y");

}


?>

<!doctype html>
<html lang="en">


<head>

    <?php include($IncludesPath . "title-meta.php"); ?>

    <!-- DataTables -->
    <?php include("Includes/DbLinks.php"); ?>
    <?php include($IncludesPath . "head-css.php"); ?>

</head>

<body data-sidebar="dark">

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
                    <?php include($IncludesPath . "PreLoader.php"); ?>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body ">

                                    <div class="row">
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <!-- <label for="exampleInputEmail1">DEPARTMENT </label>-->
                                                <select class="form-control select2" name="DeptID" id="DeptID">

                                                    <?php $AryDeptNames = array(); ?>
                                                    <option value="">All Departments</option>
                                                    <?php while ($RowDept = $StmtHrmDept->fetch_array()) {
                                                        $DeptName = $RowDept['DepartmentName'];
                                                        if (in_array($DeptName, $AryDeptNames)) {

                                                        } else {
                                                            $AryDeptNames[] = $DeptName;


                                                            ?>

                                                            <option value="<?php echo $RowDept['DeptID']; ?>">
                                                                <?php echo $RowDept['DepartmentName']; ?>
                                                            </option>
                                                        <?php }
                                                    } ?>
                                                    <select>
                                            </div>
                                        </div>


                                        <div class="col-lg-3">

                                            <div class="form-group">
                                                <!--<label for="exampleInputEmail1">SUB DEPARTMENT</label>-->
                                                <select class="form-control select2" name="SubDeptID" id="SubDeptID">
                                                    <option value="">All Sub Department</option>
                                                    <select>
                                            </div>
                                        </div>

                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <!--                  <label for="exampleInputEmail1">MONTH </label>-->
                                                <select class="form-control select2" name="MONTH" id="MONTH">
                                                    <?php
                                                    for ($i = 0; $i < 12; $i++) {
                                                        $time = strtotime(sprintf('%d months', $i), $start);
                                                        $label = date('F', $time);
                                                        $value = date('n', $time);
                                                        echo "<option value='$value'>$label</option>";
                                                    } ?>
                                                    <select>
                                            </div>
                                        </div>

                                        <div class="col-lg-3">
                                            <div class="form-group ">
                                                <!-- <label for="exampleInputEmail1">YEAR </label>-->

                                                <select class="form-control select2" name="YEAR" id="YEAR">
                                                    <?php
                                                    for ($Startyear; $Startyear <= $endYear; $Startyear++) { ?>

                                                        <option <?php if ($Startyear == $Currentyear) { ?> selected="selected"
                                                            <?php } ?> value="<?php echo $Startyear ?>">
                                                            <?php echo $Startyear ?>
                                                        </option>

                                                    <?php } ?>
                                                    <select>
                                            </div>
                                        </div>



                                        <div align="center" class="col-lg-12">
                                            <div class="form-group ">

                                                <br>
                                                <!--<label for="exampleInputEmail1"> </label>-->

                                                <button id="list" align="center" type="button"
                                                    class="btn btn-primary waves-effect waves-light">Search
                                                    <i class='ri-arrow-right-line align-middle ml-2'>
                                                    </i> </button>


                                            </div>
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
                                                    <th>Bio Id </th>
                                                      <th>Name</th>
                                                      <th>Department</th>
                                                     
                                                      <th>Status</th>
                                                      <th style="width: 20%">Tools</th>
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
    <?php include($IncludesPath . "leave_register.php"); ?>

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
            $("#print").show();

        });
        
        $('#continuebtn').on('click', function () {
            

            $('form').submit();
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
            var Month = $('#MONTH').val();
            var Year = $('#YEAR').val();
            table = $('#key-datatable').DataTable({
                "lengthMenu": [[20,50,100], [20,50,100]],
                scrollX: true,
                scrollY: '400px',
                scrollCollapse: true,
                paging: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: 'Ajax/AjTimeCard',
                    type: 'POST',
                    data:{'DeptID':DeptID,'SubDeptID': SubDeptID, 'Month': Month, 'Year': Year, 'Type': 'List'},
                },
                columns: [
                    { data: 'BioMetricID' },
                    { data: 'EmpName' },
                    { data: 'DepartmentName' },
                    { data: 'EmpStatus' },                    
                    { data: 'Tools' }                        
                ],
                columnDefs: [
                    { targets: [3], orderable: false, searchable: false },
                    { targets: [4], orderable: false, searchable: false }
                    

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