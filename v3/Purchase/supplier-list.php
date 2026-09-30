<?php 

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] .'/config/global.php';
include_once ROOT .'config/api-call.php';
 
$api = API_SUPPLIER_LIST;

$resList = CallAPI($api, "GET", "", "");

$EditAccess =  $resList->EditAccess;

$EditLink = $resList->EditLink;

$error =  $resList->Error;

$list = $resList->Data;

//var_dump($list);

?>

<!doctype html>
<html lang="en">

    <head>
        
        <?php include_once ROOT . 'includes/head.php' ?>

    </head>

    <?php include_once ROOT . 'includes/body-preloader.php' ?>  

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
                                        <div class="text-center">
                                            <a href="create-supplier" class="btn btn-success waves-effect waves-light">
				                                <i class="fa fa-plus"></i>  New Supplier
                                            </a>        
                                        </div>
                                        <?php if(!isset($error) || empty($error)) { ?>
                                        
                                        <table id="tblDataTable" class="table table-hover table-bordered">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>S.No</th>	
                                                    <th>Supplier Name</th>
                                                    <th>PAN No</th>	
                                                    <th>Bank Name</th>	
                                                    <th>Account No</th>
                                                    <th>IFSC</th>
                                                    <th>GST No</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($list as $item) { ?>
                                                <tr>
                                                    <td><?php echo $item->SNO ?></td>	
                                                    <td><?php echo $item->SupplierName ?></td>
                                                    <td><?php echo $item->PanNumber ?></td>	
                                                    <td><?php echo  $item->BankName ?></td>	
                                                    <td><?php echo $item->BankAccNo ?></td>
                                                    <td><?php echo $item->IFSC ?></td>
                                                    <td><?php echo $item->GstNo ?></td>
                                                   
                                                </tr>
                                                <?php } ?>
                                            </tbody>
                                        </table>

                                        <?php } ?>

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
             var table = $('#tblDataTable').DataTable({                    
                scrollY: "600px",
                scrollX:true,
                sScrollXInner: "100%", 
                paging:false,
                destroy: true,
                fixedColumns: true,
                scrollCollapse:true
            });
        </script>

    </body>
</html>
