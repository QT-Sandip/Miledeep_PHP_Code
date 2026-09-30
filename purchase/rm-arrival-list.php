<?php 

include_once '../config/global.php';
include_once '../config/api-call.php';

// $api = API_RM_ARRIVAL_LIST;


// $bodyContent = array(
//     'ArrivalDate'=>$ArrivalDate,
//     'Type'=>$Type,
//     'PsId'=>$PsId);

// $resList = CallAPI($api, "GET", "", "$bodyContent");

// //$EditAccess =  $resList->EditAccess;

// //$EditLink = $resList->EditLink;

// $error =  $resList->Error;

// $list = $resList->Data;

?>

<!doctype html>
<html lang="en">

    <head>
        
        <?php include_once ROOT . '/includes/head.php' ?>

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
                        <?php include_once ROOT . '/includes/page-filter.php' ?>   

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">

                                <div class="card">
                                    <div class="card-body">
                                        <div class="text-center">
                                            <a href="#" class="btn btn-success waves-effect waves-light">
				                                <i class="fa fa-plus"></i>  Create Lot
                                            </a>        
                                        </div>
                                        <?php // if(!isset($error) || empty($error)) { ?>
                                        
                                        <table id="tblDataTable" class="table table-hover table-bordered">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>S.No.</th>	
                                                    <th>Arrival Number </th>
                                                    <th>Arrival Date</th>	
                                                    <th>Purchase Type</th>	
                                                    <th>Grader</th>
                                                    <th>Agent</th>
                                                    <th>Arrival Count</th>
                                                    <th>Arrival Weight</th>
                                                    <th>Arrival Rate</th>	
                                                     <th>Actions</th> 
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php // foreach($list as $item) { ?>
                                                <tr>
                                                    <td><?php // echo $item->SNO ?></td>	
                                                    <td><?php // echo $item->ArrivalNumber ?></td>
                                                    <td><?php // echo $item->ArrivalDate ?></td>	
                                                    <td><?php // echo $item->PurchaseType ?></td>
                                                    <td><?php // echo $item->Grader ?></td>	
                                                    <td><?php // echo $item->Agent ?></td>
                                                    <td><?php // echo $item->ArrivalCount ?></td>
                                                    <td><?php // echo $item->ArrivalWeight ?></td>	
                                                    <td><?php // echo $item->BankDetails ?></td>
                                                    <td><?php // echo $item->BankDetails ?></td>
                                                </tr>
                                                <?php // } ?>
                                            </tbody>
                                        </table>

                                        <?php // } ?>

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


