<?php

$PageTitle = "Lab Users List";
$PageTitleFolder = "Quality Control";


include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
$ProductionPlant = $_SESSION['UnitID'];

if ($ProductionPlant != '') {
    $Query = " and PlantID= '$ProductionPlant'";
}

$SqlLabUsersList = "SELECT * FROM `QcLabTechnologist` where LbuStatus='1' $Query";
$StmtLabUsersList = $iconn->query($SqlLabUsersList);



?>
<!DOCTYPE html>

<html lang="en">
<link href="https://serp.silverlights.in/Clients/JME/User.css" rel="stylesheet" type="text/css" />

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
                                    <div class="col-lg-12 box-footer" align="center">

                                        <a href="DocAddLabUserNames.php" target="_blank"
                                            class="btn btn-success waves-effect waves-light">
                                            <i class="fa fa-plus"></i> New Lab User</a>

                                    </div>

                                    <table id="key-datatable" data-paging="false"
                                        class="table dt-responsive nowrap w-100">

                                        <thead class="thead-light">
                                            <tr>

                                                <th>ID</th>
                                                <th>Lab User Name </th>
                                                <th>Lab User Designation</th>
                                                <th>Actions</th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php


                                            while ($RowLabUsersList = $StmtLabUsersList->fetch_array()) {

                                            ?>

                                                <TR>
                                                    <td id="<?php echo $RowLabUsersList['LUID']; ?>">
                                                        <?php echo $RowLabUsersList['LUID']; ?>
                                                    </td>
                                                    <td> <a
                                                            href="<?php echo $RowLabUsersList['LUID']; ?>"></a><?php echo mb_strimwidth($RowLabUsersList['LbuName'], 0, 20, "..."); ?>
                                                    </td>
                                                    <td id="a_type<?php echo $RowLabUsersList['LUID']; ?>">
                                                        <?php echo mb_strimwidth($RowLabUsersList['LbuDesignation'], 0, 20, "..."); ?>
                                                    </td>


                                                    <TD>

                                                        <a target="_blank"
                                                            href="DocAddLabUserNames?ID=<?PHP echo $RowLabUsersList['LUID']; ?>"
                                                            class="mr-3 text-primary" data-toggle="tooltip"
                                                            data-placement="top" title="" data-original-title="Edit"><i
                                                                class="mdi mdi-pencil font-size-18"></i></a>

                                                        <!--<a target="_blank" class=" glyphicon glyphicon-edit btn btn-primary"
                    href="CreateTraceability.php?ID=<?PHP echo $row['LUID']; ?>">EDIT</a>	</TD>-->

                                                    <?php
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



</body>

</html>

<script>
    $(document).ready(function() {
        showData();



    });

    function showData(data) {

        $("#ClientList").html(data);


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
            dom: '<"row mb-2"<"col-md-6 d-flex align-items-center"lf><"col-md-6 d-flex justify-content-end"B>>rtip',
            buttons:[],
            "bStateSave": true,
            "fnStateSave": function(oSettings, oData) {
                localStorage.setItem('key-datatable', JSON.stringify(oData));
            },
            "fnStateLoad": function(oSettings) {
                return JSON.parse(localStorage.getItem('key-datatable'));
            }
        });
    }
</script>
