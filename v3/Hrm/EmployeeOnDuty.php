<?php
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
$sql_hrm_dm = "SELECT DeptID,DepartmentName FROM `HrmDepartments`";
$stmt_hrm_dm = $iconn->query($sql_hrm_dm);

$PageTitle = "Employee OnDuty";
$PageTitleFolder = "HRM";
$PageTitleLink = "home";
$Startyear = $Currentyear - 1;
$endYear = $Currentyear + 1;
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

/*$year=date('Y');
 //$Startyear=$year-1;
 $endYear=$year+1;*/
$month = strtotime(date('Y') . '-' . date('m') . '-' . date('j') . ' - 12 months');
$end = strtotime(date('Y') . '-' . date('m') . '-' . date('j') . ' + 0 months');
?>

<!doctype html>
<html lang="en">
<link href="https://serp.silverlights.in/Clients/Jlsf/User.css" rel="stylesheet" type="text/css" />

<head>

    <?php include($IncludesPath . "title-meta.php"); ?>
    <?php include("Includes/DbLinks.php"); ?>
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
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <select class="form-control select2" name="DeptID" id="DeptID">
                                                    <!--<option>Select Department</option>-->
                                                    <option value="">All Departments</option>
                                                    <?php while ($CODE = $stmt_hrm_dm->fetch_array()) {
                                                        $tedt = $CODE['DepartmentName'];
                                                        ?>

                                                        <option value="<?php echo $CODE['DeptID']; ?>">
                                                            <?php echo $CODE['DepartmentName']; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-lg-3">
                                            <div class="form-group">

                                                <select class="form-control select2" name="SubDeptID" id="SubDeptID">
                                                    <!-- <option>All SubDepartments</option>-->
                                                    <option value="">All SubDepartments</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-lg-3">

                                            <div class="form-group">

                                                <select class="form-control select2" name="Month" id="Month" size='1'>
                                                    <?php

                                                    $start = strtotime('first day of this month');
                                                    for ($i = 0; $i < 12; $i++) {
                                                        $time = strtotime(sprintf('%d months', $i), $start);

                                                        $label = date('F', $time);
                                                        $value = date('n', $time);
                                                        echo "<option value='$value'>$label</option>";
                                                    }


                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-lg-3">
                                            <div class="form-group ">

                                                <select class="form-control select2" name="Year" id="Year">
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
                                    </div>
                                    <div align="center" class="col-lg-12">

                                        <button id="list" align="center" type="button"
                                            class="btn btn-primary waves-effect waves-light">
                                            Search <i class='ri-arrow-right-line align-middle ml-2'> </i>
                                        </button>
                                    </div>





                                </div>
                                <!-- end row -->
                            </div>
                        </div>
                    </div>

                    <!-- end col -->
                </div> <!-- end row -->

                <div class="container-fluid">
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
                                                      <th>Total ODCount</th>
                                                      <th>Total</th>
                                                </tr>
                                            </thead>
                                        </table>


                                </div> <!-- end card body-->
                            </div> <!-- end card -->
                        </div><!-- end col-->
                    </div>
                    <!-- end row-->
                </div>


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
    <?php include("Includes/NewScripts.php"); ?>

    <!-- Required datatable js -->
    <script>
    $(document).on('change', '#DeptID', function (e) {
        e.preventDefault();
        var DeptId = $(this).val();
        var Type = 'SubDept';

        $.ajax({
            url: 'Ajax/AjDropDowns',
            type: 'POST',
            data: {
                'DeptId': DeptId,
                'Type': Type
            },
            success: function (data) {
                $("#SubDeptID").html(data);
            }
        })
    });

    $(document).on('click', '#list', function (e) {
        e.preventDefault();
        loadManualAtt();        

    });

    $(document).ready(function () {
        loadManualAtt();
    });

    function loadManualAtt() {  
        
        if ($.fn.DataTable.isDataTable('#key-datatable')) {
            $('#key-datatable').DataTable().destroy();
        }

        table = "";              
        var DeptID = $('#DeptID').val();
        var SubDeptID = $('#SubDeptID').val();
        var Month = $('#Month').val();
        var DATE = $('#DATE').val();
        var Year = $('#Year').val();
                 
        table = $('#key-datatable').DataTable({
            "lengthMenu": [[20,50,100], [20,50,100]],
            scrollX: true,
            scrollY: '400px',
            scrollCollapse: true,
            paging: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: 'Ajax/AjEmpOnDuty.php',
                type: 'POST',
                data: {
                    'DeptID': DeptID,
                    'SubDeptID': SubDeptID,
                    'Month': Month,
                    'Year': Year
                },
            },
            columns: [
                { data: 'BioMetricID' },
                { data: 'EmpName' },
                { data: 'DepartmentName' },
                { data: 'TotalODCount' },
                { data: 'Tools' }                        
            ],
            columnDefs: [
                { targets: [2], orderable: false, searchable: false },
                { targets: [3], orderable: false, searchable: false },
                { targets: [4], orderable: false, searchable: false },                

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
<?php if(0){ ?>
<script>
    $(document).on('change', '#DeptID', function (e) {
        e.preventDefault();
        var DeptId = $(this).val();
        var Type = 'SubDept';

        $.ajax({
            url: 'Ajax/AjDropDowns',
            type: 'POST',
            data: {
                'DeptId': DeptId,
                'Type': Type
            },
            success: function (data) {
                $("#SubDeptID").html(data);
            }
        })


    });




    $(document).on('click', '#list', function (e) {
        e.preventDefault();

        var DeptID = $('#DeptID').val();
        var SubDeptID = $('#SubDeptID').val();
        var Month = $('#Month').val();
        var DATE = $('#DATE').val();
        var Year = $('#Year').val();


        $.ajax({
            url: 'Ajax/AjEmpOnDuty.php',
            type: 'POST',
            // data:{'DeptID':DeptID,'SubDeptID': SubDeptID,'MONTH':MONTH,'YEAR': YEAR},
            data: {
                'DeptID': DeptID,
                'SubDeptID': SubDeptID,
                'Month': Month,
                'Year': Year
            },
            success: function (data) {
                // console.log(data);
                //$("#ATT_DATA").html(data);

                showData(data);
            }

        })
        //return false;



    });
</script>
<script>
    function showData(data) {

        $("#ATT_DATA").html(data);

        //$("#EmpList").html(data);

        var table = $('#key-datatable').DataTable({
            scrollY: "600px",
            scrollX: true,
            //sScrollX: "100%",
            sScrollXInner: "150%",
            // scrollCollapse:true,
            paging: true,
            destroy: true,
            fixedColumns: true,
            bSort: true,
            "bStateSave": true,
            "fnStateSave": function (oSettings, oData) {
                localStorage.setItem('key-datatable', JSON.stringify(oData));
            },
            "fnStateLoad": function (oSettings) {
                return JSON.parse(localStorage.getItem('key-datatable'));
            }
        });
    }
</script>
<?php } ?>