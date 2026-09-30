<?php
$PageTitle = "Monthly Reports";
$PageTitleFolder = "HRM";

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
$Startyear=$Currentyear-1;
$endYear=$Startyear+2;
$SqlDeptList = "SELECT * FROM HrmDepartments";
$StmtDept = $iconn->query($SqlDeptList);
$today_date = date('Y-m-d');

$SqlBranchMaster = "SELECT * FROM HrmBranchMaster";
$StmtBranchMaster = $iconn->query($SqlBranchMaster);


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
                                                <select class="form-control select2" name="BranchID" id="BranchID">
                                                    <option value="">Select Unit</option>

                                                    <?php while ($RowBranchMaster = $StmtBranchMaster->fetch_array()) { ?>

                                                        <option value="<?php echo $RowBranchMaster['BranchID']; ?>">
                                                            <?php echo $RowBranchMaster['BranchName'].' ('.$RowBranchMaster['BranchCode'].')'; ?>                  
                                                            </option>
                                                    <?php    } ?>
                                                </select>
                                            </div>
                                        </div>


                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <select class="form-control select2" name="Category" id="Category">
                                                    <option value="">Select Category</option>
                                                    <option value="Staff">Staff</option>
                                                    <option value="Worker">Worker</option>
                                                </select>
                                            </div>
                                        </div>


                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <select class="form-control select2" name="DeptID" id="DeptID">
                                                    <option value="">Select Department</option>

                                                    <?php while ($DataRow = $StmtDept->fetch_array()) { ?>

                                                        <option value="<?php echo $DataRow['DeptID']; ?>">
                                                            <?php echo $DataRow['DepartmentName']; ?></option>
                                                    <?php    } ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-lg-3">
                                            <div class="form-group">

                                                <select class="form-control select2" name="SubDeptID" id="SubDeptID">
                                                    <option value="">Select SubDepartment</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-lg-3">
                                            <div class="form-group">

                                                <select class="form-control select2" name="Month" id="Month">
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
                                            <div class="form-group">

                                                <select class="form-control select2" name="Year" id="Year">
                                                    <?php

                                                    for ($year; $Startyear <= $Currentyear; $Startyear++) { ?>

                                                        <option value="<?php echo $Startyear ?>" <?php if ($Startyear == $Currentyear) {
                                                                                                        echo 'selected="selected"';
                                                                                                    } ?>>
                                                            <?php echo $Startyear ?></option>

                                                    <?php  }

                                                    ?>
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

                                    <div align="center" class="col-lg-12">

                                        <button id="list" align="right" type="button" class="btn btn-primary waves-effect waves-light">
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
                <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body" id="EmpList">
                                <h4 class="card-title">Reports List</h4>


                            </div> <!-- end card body-->
                        </div> <!-- end card -->
                    </div><!-- end col-->
                </div>
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
    <?php include("Includes/NewScripts.php"); ?>

</body>

</html>

<script>
    $(document).on('change', '#DeptID', function(e) {
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
            success: function(data) {
                $("#SubDeptID").html(data);
            }
        })


    });

    $('document').ready(function() {
        getData();

        $(document).on('click', '#list', function(e) {
            e.preventDefault();

            getData()
        });
    });


    function getData() {


        var DeptID = $('#DeptID').val();
        var SubDeptID = $('#SubDeptID').val();
        var Month = $('#Month').val();
        var Year = $('#Year').val();
        var BranchID = $('#BranchID').val();
        var Category = $('#Category').val();
        var EmpStatus = $('#EmpStatus').val();


        $.ajax({
            url: 'Ajax/AjMontlyReportsList',
            type: 'POST',
            data: {
                'BranchID': BranchID,
                'Category': Category,
                'DeptID': DeptID,
                'SubDeptID': SubDeptID,
                'Month': Month,
                'Year': Year,
                'Type': 5,
                'EmpStatus':EmpStatus
            },
            success: function(data) {
                showData(data);

            }
        });

    }
</script>

<script>
    $(document).on('change', '#Month', function(e) {
        e.preventDefault();
        getData();
    });




    function showData(data) {

        $("#EmpList").html(data);

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
            "fnStateSave": function(oSettings, oData) {
                localStorage.setItem('key-datatable', JSON.stringify(oData));
            },
            "fnStateLoad": function(oSettings) {
                return JSON.parse(localStorage.getItem('key-datatable'));
            }
        });
    }
    $(document).ready(function() {
        $('#Year').select2();
        $('#Month').select2();
        function updateMonthOptions(selectedYear) {
            const monthSelect = $('#Month');
            monthSelect.empty();
            let months;
            if (selectedYear === 2025) {
                months = [
                    /*["January", 1],
                    ["February", 2],
                    ["March", 3],
                    ["April", 4],
                    ["May", 5],
                    ["June", 6],
                    ["July", 7],
                    ["August", 8],
                    ["September", 9],
                    ["October", 10],
                    ["November", 11],
                    ["December", 12]*/
                ];

                months.forEach(m => {
                    monthSelect.append(new Option(m[0], m[1]));
                });
            } else if (selectedYear === 2026) {
                months = [
                    ["January", 1],
                    ["February", 2],
                    ["March", 3],
                    ["April", 4],
                    ["May", 5],
                    ["June", 6],
                    ["July", 7],
                    ["August", 8],
                    ["September", 9],
                    ["October", 10],
                    ["November", 11],
                    ["December", 12],
                ];

                months.forEach(m => {
                    monthSelect.append(new Option(m[0], m[1]));
                });
            }
            monthSelect.trigger('change.select2');
        }
        $('#Year').on('change', function() {
            const selectedYear = parseInt($(this).val());
            updateMonthOptions(selectedYear);
        });
        const defaultYear = parseInt($('#Year').val());
        updateMonthOptions(defaultYear);
    });
</script>
