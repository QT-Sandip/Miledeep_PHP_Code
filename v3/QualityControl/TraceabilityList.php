<?php

$PageTitle = "Traceability List";
$PageTitleFolder = "Quality Control";

$AccessType = $_SESSION['AccessType'];



include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$RecordsPerPage = 100;
$searchQry = ' WHERE 1 ';
$queryParm = '';
if(!empty($_GET['searchKey'])){
    $searchKey = trim($_GET['searchKey']);
    $searchQry .= " AND CONCAT(
                        COALESCE(FarmerName, ''), ' ',
                        COALESCE(AddressContinued, ''), ' ',
                        COALESCE(Tracebility, ''), ' ',
                        COALESCE(Tonnage, ''), ' ',
                        COALESCE(DATE_FORMAT(StartDate, '%d-%m-%Y'), ''), ' ',
                        COALESCE(PondNumber, ''), ' ',      
                        COALESCE(Bap, ''), ' ',  
                        COALESCE(CONCAT(Tracebility, '-',Bap)), ' ',	                         
                        COALESCE(DATE_FORMAT(ExpiryDate, '%d-%m-%Y'), '')
                    ) LIKE '%$searchKey%'";
    $queryParm = '&searchKey='.$searchKey;
}
$cntSql = "SELECT COUNT(TraceID) CNT FROM `QcTracebilityList`  $searchQry";
$stmtCount = $iconn->query($cntSql);
$rowCount = $stmtCount->fetch_assoc();
$TotalCount = $rowCount['CNT'];
$PageCount = ceil($TotalCount / $RecordsPerPage);
$PageNo = !empty($_GET['PageNo'])?$_GET['PageNo']:1;
//echo '<pre>';print_r($PageCount);die;

$sql = "SELECT * FROM `QcTracebilityList` $searchQry ";
$sql .=" ORDER BY TraceID DESC ";
$LimitMax = ($PageNo - 1) * $RecordsPerPage;
$sql .= ' LIMIT ' . $RecordsPerPage . ' OFFSET ' . $LimitMax;
//echo $sql;die;
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
    <style>
        div:not([class]):has(> ul.pagination) ul.pagination li.page-item .page-link.active {
    background-color: #5664d2;
    color: white;
    border-color: var(--bs-pagination-active-bg);
}
    /*.pagination-rounded .page-link {
        border-radius: 30px !important;
        margin: 0 3px;
        border: none;
        text-align: center;
        height: 35px;
    }
    .page-link.active {
    z-index: 3;
    color: #fff;
    background-color: #5664d2;
    }*/
</style>
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

                                        <a href="CreateTraceability.php"
                                            class="btn btn-success waves-effect waves-light">
                                            <i class="fa fa-plus"></i> New Traceability</a>

                                    </div>

                                    <table id="key-datatable" data-paging="false"
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

                                            $i = $LimitMax+1;
                                            while ($row = $stmt->fetch_array()) {

                                                // echo print_r($row);
                                                // exit;
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
                                                        <?php echo $row['Tracebility'].'-'.$row['Bap']; ?>
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

                                                        <a 
                                                            href="CreateTraceability?ID=<?PHP echo $row['TraceID']; ?>"
                                                            class="mr-3 text-primary" data-toggle="tooltip"
                                                            data-placement="top" title="" data-original-title="Edit"><i
                                                                class="mdi mdi-pencil font-size-18"></i></a>

                                                        <!--<a target="_blank" class=" glyphicon glyphicon-edit btn btn-primary"
                    href="CreateTraceability.php?ID=<?PHP echo $row['TraceID']; ?>">EDIT</a>	</TD>-->

                                                    <?php
                                                }
                                                    ?>
                                        </tbody>
                                    </table>
                                    <?php 
                                    if(!empty($PageCount)){?>
                                        <div class="mt-3 mb-3">Showing <?php echo !empty($LimitMax)?$LimitMax+1:1;?> to <?php echo --$i ?> out of <?php echo $TotalCount ?> entries </div>
                                        <div>
                                            <ul class="pagination pagination-rounded <?php echo $PageCount<30?'justify-content-center':'';?>" style="overflow-x: auto;margin: 0px auto;">

<?php
// LEFT ARROW
if ($PageNo > 1) {
    echo '<li class="page-item">
            <a class="page-link btnPageno" href="?PageNo='.($PageNo-1).$queryParm.'">&laquo;</a>
          </li>';
}

$range = 2; // how many pages to show around current page

for ($p = 1; $p <= $PageCount; $p++) {

    // Always show first 2 and last 2 pages
    if (
        $p <= 2 ||
        $p > $PageCount - 2 ||
        ($p >= $PageNo - $range && $p <= $PageNo + $range)
    ) {
        $pageActive = ($PageNo == $p ? "active" : "");
        echo '<li class="page-item">
                <a class="page-link '.$pageActive.' btnPageno" href="?PageNo='.$p.$queryParm.'" data-pageno="'.$p.'">'.$p.'</a>
              </li>';
    }
    // Add dots
    elseif (
        $p == 3 && $PageNo > 5 ||
        $p == $PageCount - 2 && $PageNo < $PageCount - 4
    ) {
        echo '<li class="page-item disabled"><span class="page-link">...</span></li>';
    }
}

// RIGHT ARROW
if ($PageNo < $PageCount) {
    echo '<li class="page-item">
            <a class="page-link btnPageno" href="?PageNo='.($PageNo+1).$queryParm.'">&raquo;</a>
          </li>';
}
?>

</ul>
                                        </div>
                                    <?php }?>

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
        // localStorage.removeItem('key-datatable');

        var table = $('#key-datatable').DataTable({
            scrollY: "600px",
            scrollX: true,
            //sScrollX: "100%",
            sScrollXInner: "150%",
            // scrollCollapse:true,
            paging: false,
            // pageLength: 100,
            // lengthMenu: [10, 25, 50, 100],
            destroy: true,
            fixedColumns: false,
            bSort: false,
            info: false,//for Showing 1 to 10 of 100 entries
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
            dom: '<"row mb-2"<"col-md-6 d-flex align-items-center"lf><"col-md-6 d-flex justify-content-end"B>>rtip',
            buttons:[],
            "drawCallback": function() {
                $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
            },
        });
        // table.page(0).draw('page');        
        table.buttons().container().appendTo('#key-datatable_wrapper .col-md-6:eq(0)');
        $("<button class='btn btn-primary' id='btnMoreSearch' style='padding: 3px 5px;margin-left: 10px;'>More Search</button>"+
            "<a class='btn btn-warning' id='btnRefresh' href='TraceabilityList' style='padding: 3px 5px;margin-left: 10px;'>Refresh</a>")
            .insertAfter("#key-datatable_filter label");
        let params = new URLSearchParams(window.location.search);
        var searchKey = params.get('searchKey'); // ?name=Anil → Anil
        $("#key-datatable_filter input").val(searchKey);
    }
     $(document).on("click", "#btnMoreSearch", function() {
            var searchkey = $("#key-datatable_filter input").val();
            if (searchkey.length > 0) {
                window.location = 'TraceabilityList?searchKey='+searchkey.trim();
            }else{
                window.location = 'TraceabilityList';
            }
        });
    $(document).on("click", "#btnRefresh", function(e) {
        e.preventDefault();
        $("#key-datatable_filter input").val('');
        window.location = 'TraceabilityList';
    })
</script>
