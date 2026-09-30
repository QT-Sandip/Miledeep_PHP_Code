<?php 

include_once '../config/global.php';
include_once '../config/api-call.php';

$api = API_BANKS_LIST_DETAILS;

$resList = CallAPI($api, "GET", "", "");

$error =  $resList->Error;

$list = $resList->Data;

?>

<!doctype html>
<html lang="en">

    <head>
        
        <?php  include_once ROOT . '/includes/head.php' ?>

    </head>

    <?php include_once ROOT . '/includes/body-preloader.php' ?>  

        <!-- Begin page -->
        <div id="layout-wrapper">

            <?php include_once ROOT . '/includes/menu.php' ?>                   

            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">

                <div class="page-content">
                    <div class="container-fluid">

                        <?php include_once ROOT . '/includes/page-header.php' ?>   

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">

                                <div class="card">
                                    <div class="card-body">
                                        <div class="text-center">
                                            <a href="#" class="btn btn-success waves-effect waves-light">
				                                <i class="fa fa-plus"></i>  New Exporter
                                            </a>        
                                        </div>
                                        <?php if(!isset($error) || empty($error)) { ?>
                                        
                                        <table id="tblDataTable" class="table table-hover table-bordered">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>S.No.</th>	
                                                    <th>ExporterName </th>
                                                    <th>AccountName</th>	
                                                    <th>AccountNumber</th>	
                                                    <th>BankName</th>
                                                    <th>BankAddress</th>
                                                    <th>SwitftCode</th>
                                                    <th>ABANumber</th>	
                                                    <th>BankDetails</th>
                                                    <th>RoutingThrough</th>
                                                    <th>NegotiatingBank</th>
                                                    <th>Actions</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($list as $item) { ?>
                                                <tr>
                                                    <td><?php echo $item->SNO ?></td>	
                                                    <td><?php echo $item->ExporterName ?></td>
                                                    <td><?php echo $item->AccountName ?></td>	
                                                    <td><?php echo $item->AccountNumber ?></td>
                                                    <td><?php echo $item->BankName ?></td>	
                                                    <td><?php echo $item->BankAddress ?></td>
                                                    <td><?php echo $item->SwitftCode ?></td>
                                                    <td><?php echo $item->ABANumber ?></td>	
                                                    <td><?php echo $item->BankDetails ?></td>
                                                    <td><?php echo $item->RoutingThrough ?></td>	
                                                    <td><?php echo $item->NegotiatingBank ?></td>
                                                    <td></td>
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
                <?php include_once ROOT . '/includes/footer.php' ?>
                
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        <?php include_once ROOT . '/includes/common-js.php' ?>

        <!-- Required datatable js -->
        <script src="../assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
        <script src="../assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
        
        <?php include_once ROOT . '/includes/app-js.php' ?>

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

