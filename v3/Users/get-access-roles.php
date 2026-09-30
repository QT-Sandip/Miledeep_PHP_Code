<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = GET_ACCESS_ROLES;

$result = CallAPI($api, "GET", "", "");

// var_dump($result);

$error  = $result->Error;
$List = $result->Data;

// var_dump($List);

?>

<!doctype html>
<html lang="en">

<head>

    <?php include_once ROOT . 'includes/head.php' ?>

</head>

<?php include_once ROOT . '/includes/body-preloader.php' ?>

<!-- Begin page -->
<div id="layout-wrapper">

    <?php include_once ROOT . 'includes/menu.php' ?>

    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <!-- ============================================================== -->
    <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">

                <?php include_once ROOT . 'includes/page-header.php' ?>
                
                <!-- start page title -->
                <div class="row">
                    <div class="col-12">

                        <div class="card">
                            <div class="card-body">

                                <div id="divDataList">
                                    <!-- -->
                                    <div class="col-lg-12 box-footer"  align="center">
			  	                        <a href="edit-roles" class="btn btn-success waves-effect waves-light">
                                        <i class="fa fa-plus"></i> New Role</a> 
					                </div>
                                    <table id="accessList" class="table table-hover table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="min-width: 50px;">ID</th>
                                            <th style="min-width: 100px;">Role Name</th>
                                            <th style="min-width: 100px;">Department</th>
                                            <th>Menu</th>
                                            <th>Sub Menu</th>
                                            <th style="min-width: 80px;">Edit Access</th>
                                            <th style="min-width: 100px;">Delete Access</th>
                                            <th style="min-width: 80px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php  foreach ($List as $item) { ?>
                                            <tr>
                                                <td class="text-center"> <?php echo $item->ID ?></td>
                                                <td> <?php echo $item->RoleName ?></td>
                                                <td> <?php echo $item->DeptID ?></td>
                                                <td> <?php echo $item->HeaderNames ?></td>
                                                <td> <?php echo $item->SubHeaderNames ?></td>
                                                <td class="text-center"> <?php echo $item->EditAccess ?></td>
                                                <td class="text-center"> <?php echo $item->DeleteAccess ?></td>
                                                <td class="text-center">
                                                    
                                             <?php    if($item->DefaultRole!=1){  ?>
                                                    <a target="_blank" href="edit-roles.php?id=<?php  echo $item->ID;?>" 
                                                    class="mr-3 text-primary Edit" data-toggle="tooltip" data-placement="top"
                                                    title="Edit" data-original-title="Edit"><i class="mdi mdi-pencil font-size-18"></i></a>
                                                    <?php   } ?>
                                                </td>
                                            </tr>
                                        <?php  } ?> 
                                    </tbody>
                                    </table>
                                </div>

                            </div>
                        </div>


                    </div>
                </div>

            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->
        <?php include_once ROOT . 'includes/footer.php' ?>

    </div>
    <!-- end main content-->

</div>
<!-- END layout-wrapper -->

<?php include_once ROOT . 'includes/common-js.php' ?>

<!-- Required datatable js -->
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

<?php include_once ROOT . 'includes/app-js.php' ?>

<script>
     function fnShowDataTable() {                
        var table = $('#accessList').DataTable({                    
            scrollY: "600px",
            scrollX:true,
            sScrollXInner: "100%", 
            paging:false,
            destroy: true,
            fixedColumns: true,
            scrollCollapse:true,
            dom: 'Blfrtip',
            buttons: [{
                extend: 'excel',
                text: 'Download Excel',
                className: 'btnExcelDataTable',
                title: 'Users Access Roles',
                footer: true,
                exportOptions: {
                    columns: ':visible'
                }
            }],
        });            
    }
    $(document).ready(function () {
        fnShowDataTable()
    });
</script>
</body>

</html>