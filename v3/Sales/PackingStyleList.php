<?php

$PageTitle = "Packing Styles List";
$PageTitleFolder = "Sales";

$AccessType = $_SESSION['AccessType'];


include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$SqlPacking = "SELECT * FROM `AdminPackingStyles` Where Status='1'
   order by PCKID DESC ";

$StmtPacking = $iconn->query($SqlPacking);



?>
<!DOCTYPE html>

<html lang="en">


<link href="https://serp.silverlights.in/Clients/Jlsf/User.css" rel="stylesheet" type="text/css" />

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
                                <div class="card-body" id="ClientList">
                                    <div class="text-center col-lg-12 box-footer">

                                        <a href="CreatePackingStyles.php"
                                            class="btn btn-success waves-effect waves-light">
                                            <i class="fa fa-plus"></i> New Packing Style</a>

                                    </div>
                                    <table id="keydatatable" data-paging="true"
                                        class="table dt-responsive nowrap w-100">
                                        <thead class="thead-light">

                                            <tr>

                                                <th>ID</th>
                                                <th>Packing Style</th>
                                                <th>Gross Weight</th>
                                                <th>Net Weight</th>
                                                <!--<th>Total Slabs</th>-->
                                                <th>UOM</th>
                                                <th>Actions</th>
                                                <!--<th>Slab Weight</th>-->



                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php

                                            $SNo = 1;

                                            while ($RowPacking = $StmtPacking->fetch_assoc()) {
                                                $Netweight = $RowPacking['TotalSlabs'] * $RowPacking['SlabWeight']; ?>

                                                <tr>

                                                    <td> <?php echo $SNo ?></td>


                                                    <td> <?php echo $RowPacking['PackingStyle'] ?></td>
                                                    <td> <?php echo $RowPacking['GrossWeight'] ?></td>
                                                    <td> <?php echo $Netweight ?></td>
                                                    <td> <?php echo $RowPacking['Uom'] ?></td>
                                                    <!--<td> <?php echo $RowPacking['SlabWeight'] ?></td> -->
                                                    <td>



                                                        <a target=""
                                                            href="CreatePackingStyles?PackingStyleID=<?php echo $RowPacking['PCKID']; ?>"
                                                            class="mr-3 text-primary Edit" data-toggle="tooltip"
                                                            data-placement="top" title="" data-original-title="Edit"><i
                                                                class="mdi mdi-pencil font-size-18"></i></a>

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


    <!-- hi -->

    <?php include($IncludesPath . "NewScripts.php"); ?>


    <script>
        var table = $('#keydatatable').DataTable({
            search: {
                regex: true,
                smart: false,
                caseInsensitive: false
            },
            scrollY: "600px",
            scrollX: true,
            //sScrollX: "100%",
            sScrollXInner: "150%",
            scrollCollapse: true,
            paging: true,
            destroy: true,
            fixedColumns: true,
            bSort: true,
            language: {
                lengthMenu: "Show _MENU_ records",
                paginate: {
                    previous: "<i class='mdi mdi-chevron-left'>",
                    next: "<i class='mdi mdi-chevron-right'>"
                }
            },
            drawCallback: function () {
                $(".dataTables_paginate > .pagination").addClass("pagination-rounded");
            },
            dom: '<"row mb-2"<"col-md-6 d-flex align-items-center"lf>>rtip',
            order: [
                [0, 'desc']
            ],
            "bStateSave": true,
            "fnStateSave": function (oSettings, oData) {
                localStorage.setItem('key-datatable', JSON.stringify(oData));
            },
            "fnStateLoad": function (oSettings) {
                return JSON.parse(localStorage.getItem('key-datatable'));
            }
        });
    </script>

</body>

</html>
