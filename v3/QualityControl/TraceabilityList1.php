<?php

$PageTitle = "Traceability List1";
$PageTitleFolder = "Quality Control";

$AccessType = $_SESSION['AccessType'];


include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$sql = "SELECT * FROM `qctracebilitylist1`";
$stmt = $iconn->query($sql);

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

                                        <a href="CreateTraceability1.php" target="_blank"
                                            class="btn btn-success waves-effect waves-light">
                                            <i class="fa fa-plus"></i> New Traceability</a>

                                    </div>

                                    <table id="key-datatable" data-paging="true"
                                        class="table dt-responsive nowrap w-100">

                                        <thead class="thead-light">
                                            <tr>

                                                <th>ID</th>
                                                <th>Farmer Name </th>
                                                <th>Farmer Address</th>
                                                <th>Traceability</th>
                                                <th>Tonnage</th>
                                                <th>Start Date</th>
                                                <th>Expiry Date</th>
                                                <th>Actions</th>

                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php

                                            $i = 1;
                                            while ($row = $stmt->fetch_array()) {


                                            ?>

                                                <TR>
                                                    <td id="<?php echo $row['TraceID']; ?>"> <?php echo $i++; ?></td>
                                                    <td> <a
                                                            href="<?php echo $row['TraceID']; ?>"></a><?php echo mb_strimwidth($row['FarmerName'], 0, 20, "..."); ?>
                                                    </td>

                                                    <td id="a_type<?php echo $row['TraceID']; ?>">
                                                        <?php echo mb_strimwidth($row['AddressContinued'], 0, 20, "..."); ?>
                                                    </td>

                                                    <td id="p_number<?php echo $row['TraceID']; ?>">
                                                        <?php echo $row['Tracebility']; ?>
                                                    </td>
                                                    <td id="tonnage<?php echo $row['TraceID']; ?>">
                                                        <?php echo $row['Tonnage']; ?>
                                                    </td>
                                                    <td id="startdate<?php echo $row['TraceID']; ?>">
                                                        <?php echo !empty($row['StartDate']) ? date('d-m-Y', strtotime($row['StartDate'])) : '';
                                                        ?>
                                                    </td>
                                                    <td id="expirydate<?php echo $row['TraceID']; ?>">
                                                        <?php echo !empty($row['ExpiryDate']) ? date('d-m-Y', strtotime($row['ExpiryDate'])) : '';
                                                        ?>
                                                    </td>


                                                    <TD>

                                                        <a target="_blank"
                                                            href="CreateTraceability1?ID=<?PHP echo $row['TraceID']; ?>"
                                                            class="mr-3 text-primary" data-toggle="tooltip"
                                                            data-placement="top" title="" data-original-title="Edit"><i
                                                                class="mdi mdi-pencil font-size-18"></i></a>

                                                        <!--<a target="_blank" class=" glyphicon glyphicon-edit btn btn-primary"
                    href="CreateTraceability1.php?ID=<?PHP echo $row['TraceID']; ?>">EDIT</a>	</TD>-->

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
            pageLength: 100,
            lengthMenu: [10, 25, 50, 100],
            destroy: true,
            fixedColumns: false,
            bSort: true,
            "bStateSave": false,
            "fnStateSave": function(oSettings, oData) {
                localStorage.setItem('key-datatable', JSON.stringify(oData));
            },
            "fnStateLoad": function(oSettings) {
                return JSON.parse(localStorage.getItem('key-datatable'));
            },
            "language": {
                "paginate": {
                    "previous": "<i class='mdi mdi-chevron-left'>",
                    "next": "<i class='mdi mdi-chevron-right'>"
                }
            },
            "drawCallback": function() {
                $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
            },
        });
        table.buttons().container().appendTo('#key-datatable_wrapper .col-md-6:eq(0)');
    }
</script>