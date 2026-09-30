<?php
$PageTitle = "Manual Attendance";
$PageTitleFolder = "HRM";
$PageTitleLink = "home";
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
// Current URL path
$currentUrl = $_SERVER['REQUEST_URI'];

// Remove base path if needed
$currentPath = trim(parse_url($currentUrl, PHP_URL_PATH), '/');

// Example:
// v3/Hrm/ManualAttendance
// remove v3/
$currentPath = preg_replace('#^v3/#', '', $currentPath);
//echo "<pre>"; print_r($_SESSION);
// flag
$hasAccess = false;
$sessionData = $_SESSION;
// Check MenuList
if (!empty($sessionData['MenuList'])) {

    foreach ($sessionData['MenuList'] as $menu) {
        //echo "<div>"; print_r($menu); exit;

        if (!empty($menu->SubMenu)) {

            foreach ($menu->SubMenu as $subMenu) {

                if (
                    isset($subMenu->PathUrl) &&
                    trim($subMenu->PathUrl, '/') == $currentPath
                ) {
                    $hasAccess = true;
                    break 2;
                }
            }
        }
    }
}else{
    //echo "Menu list empty";
}

// Access check
if (!$hasAccess) {

    header("HTTP/1.1 403 Forbidden");
    exit("Access Denied");

}
$SqlHrmDept = "SELECT DeptID,DepartmentName FROM HrmDepartments order by DepartmentName asc";
$stmt_hrm_dm = $iconn->query($SqlHrmDept);
?>

<!doctype html>
<html lang="en">
<link href="https://serp.silverlights.in/Clients/SatishMarine/User.css" rel="stylesheet" type="text/css" />

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
                    <?php include "PopUpTable.php"; ?>


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
                                                    <?php while ($CODE = $stmt_hrm_dm->fetch_array()) { ?>
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
                                                    <option value="">All SubDepartments</option>
                                                </select>
                                            </div>
                                        </div>



                                        <div class="form-group col-sm-3">

                                            <input type="date" value='<?php echo date('Y-m-d'); ?>' class="form-control"
                                                name="DATE" id="DATE">

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
                                <div class="card-body" id="EmpList">

                                    <table id="key-datatable" class="table table-bordered" style="width:100%">
                                            <thead>
                                                <tr>                        
                                                    <th>Bio ID</th>
                                                      <th>Name</th>        
                                                      <th>Department</th>
                                                      <th>Shift/Status</th>
                                                      <th>Time In</th>
                                                      <th>Time Out</th>
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
            $(function () {

                $(document).on("click", ".edit", function (e) {
                    e.preventDefault();

                    $('#addnew').modal('show');
                    var id = $(this).data('id');

                    var DATE = $('#DATE').val();

                    var empname = '#empname' + id;
                    var time_in = '#time_in' + id;
                    var time_out = '#time_out' + id;

                    var empname = $(empname + "").text();
                    var time_in = $(time_in + "").text();
                    var time_out = $(time_out + "").text();



                    $("#employee").val(empname);
                    $("#time_in").val(time_in);
                    $("#time_out").val(time_out);
                    $("#datepicker_add").val(DATE);
                    $("#save").val(id);

                });

                $('#save').click(function (e) {
                    e.preventDefault();
                    //alert('hi');
                    var buttonObject = document.getElementById("save")
                    var emp_id = buttonObject.value
                    var time_in = $("#time_in").val();
                    var time_out = $("#time_out").val();

                    var M_DEPT = $('#M_DEPT').val();
                    var S_DEPT = $('#S_DEPT').val();
                    var DATE = $('#DATE').val();

                    $('#addnew').modal('hide');


                    $.ajax({
                        type: 'POST',
                        url: 'Ajax/AjaxSaveManualAtt.php',
                        data: { 'emp_id': emp_id, 'time_in': time_in, 'time_out': time_out, 'M_DEPT': M_DEPT, 'S_DEPT': S_DEPT, 'CURRDATE': DATE },
                        success: function (data) {
                            alert('Success');

                            // console.log(data);
                            /* $("#ATT_DATA").html(data);
                           $('#example1').DataTable();*/
                            showData(data);
                        }
                    });



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
        $(document).ready(function () {

            $(document).on('change', '#DeptID', function (e) {
                e.preventDefault();
                var DeptID = $(this).val();
                var Type = 'SubDept';
                $.ajax({
                    url: 'Ajax/AjDropDowns',
                    type: 'POST',
                    data: { 'DeptId': DeptID, 'Type': Type },
                    success: function (data) {
                        console.log(data);
                        $("#SubDeptID").html(data);
                    }
                })
            });

            $(document).on('change', '#REG_NO', function (e) {
                e.preventDefault();
                var REG_NO = $(this).val();

                $.ajax({
                    url: 'Ajax/AjManualAtt.php',
                    type: 'POST',
                    data: { 'REG_NO': REG_NO, 'INV_ID': INV_ID },
                    success: function (data) {
                        console.log(data);
                        $("#PACK_SIZE").html(data);
                    }
                })
            });
            
            $(document).on('click', '#BtnEditManualAttend', function (e) {
                e.preventDefault();

                var EmpBioID = $(this).val();
                var TimeOutId = '#TimeOut' + EmpBioID;
                var Date = $('#DATE').val();

                var TimeInId = '#TimeIn' + EmpBioID;

                var TimeOut = $(TimeOutId + "").val();
                var TimeIn = $(TimeInId + "").val();


                $.ajax({
                    type: 'POST',
                    url: 'Ajax/AjAddManualAttendance.php',
                    data: { 'TimeIn': TimeIn, 'TimeOut': TimeOut, 'Date': Date, 'Type': 'AddManualAttd', 'EmpBioID': EmpBioID },

                    //dataType: 'json',

                    success: function (response) {
                        var data = JSON.parse(response);
                        if (data['sts'] == 'failed') {
                            alert(data['msg']);
                        } else {
                            $('#myModal').modal('hide');
                            //alert(data['msg']);
                            //location.reload();

                            loadManualAtt();


                        }
                    }
                });                
            });

            $(document).on('click', '#list', function (e) {
                e.preventDefault();
                loadManualAtt();
            });

            
            loadManualAtt();

        });

        function validateTime(obj) {
            try {
                new Date(obj).toISOString()
                return true
            } catch (e) {
                return false
            }
        }

        function loadManualAtt() {  
            if ($.fn.DataTable.isDataTable('#key-datatable')) {
                $('#key-datatable').DataTable().destroy();
            }
            table = "";              
            var DeptID = $('#DeptID').val();
            var SubDeptID = $('#SubDeptID').val();
            var Date = $('#DATE').val();

            if (Date == '') {
                $("#Date").focus();
                alert("Date Required");
                return;
            }            
            table = $('#key-datatable').DataTable({
                "lengthMenu": [[20,50,100], [20,50,100]],
                scrollX: true,
                scrollY: '400px',
                scrollCollapse: true,
                paging: true,
                processing: true,
                serverSide: true,
                ajax: {
                    url: 'Ajax/AjManualAtt.php',
                    type: 'POST',
                    data: { 'DeptID': DeptID, 'SubDeptID': SubDeptID, 'Date': Date },
                },
                columns: [
                    { data: 'BioMetricID' },
                    { data: 'EmpName' },
                    { data: 'DepartmentName' },
                    { data: 'Shift' },
                    { data: 'TimeIn' },
                    { data: 'TimeOut' },                    
                    { data: 'Tools' }                        
                ],
                columnDefs: [
                    { targets: [2], orderable: false, searchable: false },
                    { targets: [3], orderable: false, searchable: false },
                    { targets: [4], orderable: false, searchable: false },
                    { targets: [5], orderable: false, searchable: false },
                    { targets: [6], orderable: false, searchable: false },

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

    $(function () {

        $(document).on("click", ".edit", function (e) {
            e.preventDefault();

            $('#addnew').modal('show');
            var id = $(this).data('id');

            var DATE = $('#DATE').val();

            var empname = '#empname' + id;
            var time_in = '#time_in' + id;
            var time_out = '#time_out' + id;

            var empname = $(empname + "").text();
            var time_in = $(time_in + "").text();
            var time_out = $(time_out + "").text();



            $("#employee").val(empname);
            $("#time_in").val(time_in);
            $("#time_out").val(time_out);
            $("#datepicker_add").val(DATE);
            $("#save").val(id);

        });

        $('#save').click(function (e) {
            e.preventDefault();
            //alert('hi');
            var buttonObject = document.getElementById("save")
            var emp_id = buttonObject.value
            var time_in = $("#time_in").val();
            var time_out = $("#time_out").val();

            var M_DEPT = $('#M_DEPT').val();
            var S_DEPT = $('#S_DEPT').val();
            var DATE = $('#DATE').val();

            $('#addnew').modal('hide');


            $.ajax({
                type: 'POST',
                url: 'Ajax/AjaxSaveManualAtt.php',
                data: { 'emp_id': emp_id, 'time_in': time_in, 'time_out': time_out, 'M_DEPT': M_DEPT, 'S_DEPT': S_DEPT, 'CURRDATE': DATE },
                success: function (data) {
                    alert('Success');

                    // console.log(data);
                    /* $("#ATT_DATA").html(data);
                   $('#example1').DataTable();*/
                    showData(data);
                }
            });



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
</script>



<Script>

    $('document').ready(function () {



        $(document).on('change', '#DeptID', function (e) {
            e.preventDefault();
            var DeptID = $(this).val();
            var Type = 'SubDept';
            $.ajax({
                url: 'Ajax/AjDropDowns',
                type: 'POST',
                data: { 'DeptId': DeptID, 'Type': Type },
                success: function (data) {
                    console.log(data);
                    $("#SubDeptID").html(data);
                }
            })
        });

        //uma	
        $(document).on('click', '#BtnEditManualAttend', function (e) {
            e.preventDefault();

            var EmpBioID = $(this).val();
            var TimeOutId = '#TimeOut' + EmpBioID;
            var Date = $('#DATE').val();

            var TimeInId = '#TimeIn' + EmpBioID;

            var TimeOut = $(TimeOutId + "").val();
            var TimeIn = $(TimeInId + "").val();


            $.ajax({
                type: 'POST',
                url: 'Ajax/AjAddManualAttendance.php',
                data: { 'TimeIn': TimeIn, 'TimeOut': TimeOut, 'Date': Date, 'Type': 'AddManualAttd', 'EmpBioID': EmpBioID },

                //dataType: 'json',

                success: function (response) {
                    var data = JSON.parse(response);
                    if (data['sts'] == 'failed') {
                        alert(data['msg']);
                    } else {
                        $('#myModal').modal('hide');
                        //alert(data['msg']);
                        //location.reload();

                        loadManualAtt();


                    }
                }
            });


            /*$.ajax({
               url:'Ajax/AjPopUpManualAttd.php',
              type:'POST',
              data:{'EmpBioID':EmpBioID , 'TYPE':'KK'},
              success:function(data){
                 
                 //$('.modal-title').text(EmpBioID+' ?');
                $("#PopUpTable").html(data);
                $('#myModal').modal('show');
                        }
                });*/
            //}
        });









        $(document).on('change', '#REG_NO', function (e) {
            e.preventDefault();
            var REG_NO = $(this).val();

            $.ajax({
                url: 'Ajax/AjManualAtt.php',
                type: 'POST',
                data: { 'REG_NO': REG_NO, 'INV_ID': INV_ID },
                success: function (data) {
                    console.log(data);
                    $("#PACK_SIZE").html(data);
                }
            })
        });



        $(document).on('click', '#list', function (e) {
            e.preventDefault();
            loadManualAtt();
        });


    });


    function loadManualAtt() {

        var DeptID = $('#DeptID').val();
        var SubDeptID = $('#SubDeptID').val();
        var Date = $('#DATE').val();

        if (Date == '') {
            $("#Date").focus();
            alert("Date Required");
            return;
        }
        $.ajax({
            url: 'Ajax/AjManualAtt.php',
            type: 'POST',
            data: { 'DeptID': DeptID, 'SubDeptID': SubDeptID, 'Date': Date },
            success: function (data) {
                //console.log(data);
                showData(data);
            }
        })

    }


</script>






<script language="javascript">
    function validateTime(obj) {
        try {
            new Date(obj).toISOString()
            return true
        } catch (e) {
            return false
        }
    }


</script>

<script>

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
            order: [[1, 'desc']],
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
