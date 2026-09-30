<?php

$PageTitle = "Loading Register List";
$PageTitleFolder = "Quality Control";
$Currentyear = date('Y');
$current_date = date("Y-m-d");
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$query = "SELECT * FROM loading_register_master ORDER BY id DESC";
$result = $iconn->query($query);
?>

<!doctype html>
<html lang="en">
<head>

    <!-- DataTables -->
    <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css"
        rel="stylesheet" type="text/css" />
    <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css"
        rel="stylesheet" type="text/css" />
    <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-select-bs4/css//select.bootstrap4.min.css"
        rel="stylesheet" type="text/css" />

    <!-- Responsive datatable examples -->
    <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css"
        rel="stylesheet" type="text/css" />


    <link href="<?php echo $BaseDist ?>assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css"
        rel="stylesheet">
    <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css"
        rel="stylesheet">
    <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css"
        rel="stylesheet" />
    <?php include($IncludesPath . "head-css.php"); ?>
    <style>
        .dataTables_scrollBody {
            overflow-y: auto !important;
        }
        .dataTables_wrapper {
            overflow-x: auto;
        }
    </style>
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
                    <?php include "PopUpTable.php"; ?>
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="card">
                                                <div class="card-body">
                                                    <div class="row">
                                                        
                                                        <div class="form-group col-sm-3" >
                                                            <label for="exampleInputEmail1">From Date</label>
                                                            <input required type="date" value="<?php  echo $ROWSWAB['SampleDate']?$ROWSWAB['SampleDate']:$current_date?>" class="form-control"
                                                            name='SampleDate' id="fromDate"
                                                            placeholder="Enter Date of Sampling">
                                                        </div>

                                                        <div class="form-group col-sm-3" >
                                                            <label for="exampleInputEmail1">To Date </label>
                                                            <input required type="date" value="<?php  echo $ROWSWAB['SampleDate']?$ROWSWAB['SampleDate']:$current_date?>" class="form-control"
                                                            name='SampleDate' id="toDate"
                                                            placeholder="Enter Date of Sampling">
                                                        </div>

                                                        <div class="form-group col-sm-3 mt-4" >
                                                            <div class="d-flex gap-2">
                                                                <button id="list" type="button" class="btn btn-primary mr-3">
                                                                    Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                                                                </button>
                                                                <a id="createreport" href="javascript:void(0)" class="btn btn-success">
                                                                    Create Report
                                                                </a>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                        </div>                        
                                    </div>
                                    <div class="row" id="ATT_DATA" style="display:block;"> 
                                    <!--input type="text" id="customSearch" placeholder="Search..."-->                                       
                                        <table  id="mytable-data" class="table dt-responsive nowrap w-100" >
                                        <thead>
                                            <tr>                                            
                                                <th>Sno</th>
                                                <th>Invoice No</th>
                                                <th>Container</th>
                                                <th>Vehicle</th>
                                                <th>PO No</th>                                  
                                                <th>Action</th>
                                            </tr>
                                        </thead> 
                                        <tbody></tbody>                                       
                                    </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php include($IncludesPath . "vendor-scripts.php"); ?>
        <?php include($IncludesPath . "NewScripts.php"); ?>
        <?php include($IncludesPath . "ShowDataTable.php"); ?>
    </div>
    <script>
        $('#customSearch').on('keyup', function() {
            let value = this.value.toLowerCase();
            $('#mytable-data tbody tr').each(function() {
                let rowText = $(this).text().toLowerCase();
                $(this).toggle(rowText.indexOf(value) > -1);
            });
        });

        $("#createreport").click(function(){            
            var fromDate = $('#fromDate').val();
            var toDate = $('#toDate').val();                  
            if( fromDate == "" || toDate == "" || fromDate == undefined || toDate == undefined ){                     
                alert("Please Select Dates");                    
                return false;      
            }
            window.location.href="loading-register.php?FromDate="+fromDate+"&ToDate="+toDate;
        });
        $('#mytable-data').on('click', '.btnDelete', function(e) {
            e.preventDefault(); 
            var id =$(this).data('id');
            var r = confirm("Are You Sure You Want To Delete?");
            if (r == true) {
                $.ajax({
                    url: 'Ajax/Ajax-loading-register',
                    type: 'POST',
                    dataType: 'json',
                    data: { id: id, action: "delete" },
                    success: function(response) {                            
                        $('#mytable-data').DataTable().ajax.reload(null, false);
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error: ", error);
                    }
                });
            }
        });
        $("#list").click(function(){
                var fromDate = $('#fromDate').val();
                var toDate = $('#toDate').val();                  
                if( fromDate == "" || toDate == "" || fromDate == undefined || toDate == undefined ){                     
                    alert("Please Select Dates");                    
                    return false;      
                }
                loadAdvances();
            }); 

        function loadAdvances(loadAll = false) {
            $("#ATT_DATA").show();
            var fromDate = $('#fromDate').val();
            var toDate = $('#toDate').val();        
            
            if ($.fn.DataTable.isDataTable('#mytable-data')) {
                $('#mytable-data').DataTable().destroy();
            }

            let table = "";     
            table = $('#mytable-data').DataTable({                                                  
                serverSide: !loadAll,
                processing: true,
                ajax: {
                    url: 'Ajax/Ajax-loading-register',
                    type: 'POST',
                    data: function(d) {
                        d.fromDate = fromDate;
                        d.toDate = toDate;  
                        d.fromPage = "Listpage";
                        d.action = "List";    
                        d.loadAll= loadAll;              
                    }
                },
                columns: [
                    { data: 'Sno' },
                      { data: 'InvNumFull' },
                      { data: 'container_no' },
                      { data: 'vehicle_no' },
                      { data: 'po_no' },
                      { data: 'Action'}
                ],
                columnDefs: [
                    /*{ targets: [2], orderable: false, searchable: false },
                    { targets: [3], orderable: false, searchable: false },
                    { targets: [4], orderable: false, searchable: false },                */

                ],
                paging: !loadAll,
                pageLength: 10,
                lengthMenu: [[10,50,100], [10,50,100]],
                scrollX: true,
                scrollY: '500px',
                scrollCollapse: true,
                autoWidth: false,
                responsive: false,
                searching: false,
                ordering:false,                         

            });
        }
    </script>
</body>
</html>
