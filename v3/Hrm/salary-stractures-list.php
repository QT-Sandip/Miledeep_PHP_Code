<?php

// phpinfo();

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_HRM_SALARY_STRUCTURE_LIST;

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
			  	                        <a href="create-salary-structure" class="btn btn-success waves-effect waves-light">
                                        <i class="fa fa-plus"></i> New structure</a> 
					                </div>
                                    <table id="accessList" class="table table-hover table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <th style="min-width: 50px;">ID</th>
                                            <th style="min-width: 100px;">structure Name</th>
                                            <th>Additions</th>
                                            <th>Deductions</th> 
                                            
                                            <th style="min-width: 80px;">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php  foreach ($List as $item) { ?>
                                            <tr>
                                                <td class="text-center"> <?php echo $item->ID ?></td>
                                                <td> <?php echo $item->RoleName ?></td>                                            
                                                <td> <?php echo $item->Additions ?></td>
                                                <td> <?php echo $item->Deductions ?></td>
                                                <td class="text-center"> <a target="_blank" href="create-salary-structure.php?id=<?php  echo $item->ID;?>" 
                                                    class="mr-3 text-primary Edit" data-toggle="tooltip" data-placement="top"
                                                    title="" data-original-title="Edit"><i class="mdi mdi-pencil font-size-18"></i></a>
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