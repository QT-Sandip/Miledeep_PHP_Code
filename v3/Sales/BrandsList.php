<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

$PageTitle = "Brands List";
$PageTitleFolder = "Sales";
$BranchID = $_SESSION['BranchID'];
$AccessType = $_SESSION['AccessType'];


include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$SqlBrand = "SELECT BrandID,ConsigneeName,BrandName,Remarks,PrintStatus,
CASE BrandType WHEN '0' THEN 'Both' WHEN '1' THEN 'Sales' WHEN '2' THEN 'Store' END AS BrandTypeName 
FROM `ScrmBrandsMaster`as SBM 
left join ScrmConsigneeDetails as SCD on SBM.BuyerID=SCD.ConID order by BrandID desc";

/*$SqlBrand="SELECT BrandID,ConsigneeName,BrandName,Remarks FROM `ScrmBrandsMaster` as SBM 
left join ScrmConsigneeDetails as SCD on SBM.BuyerID=SCD.ConID ORDER BY BrandID DESC";*/

$StmtBrand = $iconn->query($SqlBrand);

// $errorInfo = $StmtBrand->errorInfo();
// exit;



?>
<!DOCTYPE html>

<html lang="en">


<link href="<?php echo $href ?>" rel="stylesheet" type="text/css" />

<head>

    <?php include($IncludesPath . "title-meta.php"); ?>
    <?php include($IncludesPath . "DbLinks.php"); ?>
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
                                <div class="card-body" id="ShowDataTable">
                                    <div class="col-lg-12 box-footer" align="center">

                                        <a href="AddBrand.php" class="btn btn-success waves-effect waves-light">
                                            <i class="fa fa-plus"></i> New Brand</a>

                                    </div>


                                    <table id="key-datatable" data-paging="true" class="table dt-responsive nowrap w-100">
                                        <thead class="thead-light">
                                            <tr>


                                                <th>S.No</th>
                                                <th>Brand Name</th>
                                                <th>Brand Type</th>
                                                <th>Buyer</th>
                                                <th>Remarks</th>
                                                <th>Print Status</th>
                                                <th>Actions</th>


                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php

                                            $SNo = 1;
                                            while ($RowBrand = $StmtBrand->fetch_array()) {
                                                $BuyerName = $RowBrand['ConsigneeName'];
                                                $BrandType = $RowBrand['BrandType'];
                                                $BrandName = $RowBrand['BrandName'];
                                                $BrandTypeName = $RowBrand['BrandTypeName'];
                                                $Remarks = $RowBrand['Remarks'];
                                                $PrintStatus = $RowBrand['PrintStatus'];

                                            ?>

                                                <tr>

                                                    <td>
                                                        <?php echo $SNo ?>
                                                    </td>
                                                    <td>
                                                        <?php echo $BrandName ?>
                                                    </td>
                                                    <td>
                                                        <?php echo $BrandTypeName ?>
                                                    </td>
                                                    <td>
                                                        <?php echo $BuyerName ?>
                                                    </td>
                                                    <td>
                                                        <?php echo $Remarks ?>
                                                    </td>
                                                    <td>
                                                        <?php if ($PrintStatus == 1) {
                                                            echo "Print";
                                                        } else {
                                                            echo "Hide";
                                                        }
                                                        ?>
                                                    </td>

                                                    <td>



                                                        <a target="" href="AddBrand.php?id=<?php echo $RowBrand['BrandID']; ?>" class="mr-3 text-primary Edit" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="mdi mdi-pencil font-size-18"></i></a>

                                                    </td>
                                                </tr>
                                            <?php
                                                $SNo++;
                                            }
                                            ?>
                                        </tbody>
                                    </table>

                                </div>

                                <!-- end col -->
                            </div> <!-- end row -->





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
    <?php include($IncludesPath . "NewScripts.php"); ?>
    <?php include($IncludesPath . "ShowDataTable.php"); ?>


</body>

</html>

<script>
    $(document).ready(function() {
        showData();

    });
</script>
