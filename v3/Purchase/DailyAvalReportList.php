<?php 
$PageTitle="Daily  Reports";
$PageTitleFolder="Purchase";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
$SqlDeptList="SELECT * FROM RmPurchaseStations";
$StmtDept=$iconn->query($SqlDeptList);

$today_date=date('Y-m-d');

$api = API_RM_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", "");
$resSpecies = $resDropdownsAPI->Data->Species;
$sqlQueryCosting = "SELECT * FROM `SalesCostingRatesHistory` WHERE SType!='' GROUP BY SType";
$SpeciesTypeCount=$iconn->query($sqlQueryCosting);
$SpeciesTypeCount=$SpeciesTypeCount->num_rows;
?>

<!doctype html>
<html lang="en">


    <head>
        
         <?php include ($IncludesPath."title-meta.php"); ?>

        <!-- DataTables -->
        <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-bs4/css/dataTables.bootstrap4.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons-bs4/css/buttons.bootstrap4.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-select-bs4/css//select.bootstrap4.min.css" rel="stylesheet" type="text/css" />

        <!-- Responsive datatable examples -->
        <link href="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive-bs4/css/responsive.bootstrap4.min.css" rel="stylesheet" type="text/css" />     


        <link href="<?php echo $BaseDist ?>assets/libs/select2/css/select2.min.css" rel="stylesheet" type="text/css" />
        <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-datepicker/css/bootstrap-datepicker.min.css" rel="stylesheet">
        <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-colorpicker/css/bootstrap-colorpicker.min.css" rel="stylesheet">
        <link href="<?php echo $BaseDist ?>assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.css" rel="stylesheet" />
        <?php include ($IncludesPath."head-css.php");?>

    </head>

    <body data-sidebar="dark">
    
     <?php include ($IncludesPath."PreLoader.php"); ?>
    


        <!-- Begin page -->
        <div id="layout-wrapper">

            <?php
            include ($IncludesPath."topbar.php");
            include ($IncludesPath."sidebar.php"); 
            ?>

            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">

                <div class="page-content">
                    <div class="container-fluid">

                        <?php include ($IncludesPath."page-title.php");?>
                        
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">
                                    
                                        <div class="row">
                                                <div class="col-lg-3">
                                                    <div class="form-group ">
                                                     <label for="exampleInputEmail1">Purchase Stations</label>
                                                       <select class="form-control select2" name="PurchaseStationID" id="PurchaseStationID">
                                                            <option value="">All Purchase Station</option>
                                                        
                                                            <?php while($DataRow=$StmtDept->fetch_array()){?>   
                 
                                                <option value="<?php echo $DataRow['PurchaseStationID'] ;?>" >
                                                <?php  echo $DataRow['StationName']; ?></option>
                                                <?php    } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                
                                                <?php 
                                                if($SpeciesTypeCount > 1){
                                                    ?>
                                                <div class="col-lg-3">
                                                    <div class="form-group ">
                                                     <label for="drpSpecies">Species <span style="color:red">*</span></label>
                                                       <select class="form-control select2" name="drpSpecies" id="drpSpecies">
                                                                 <option value="" <?php echo ($Data->Species == '') ? 'selected' : '';  ?>> Select</option>
                                                            <?php foreach ($resSpecies as $Item) { ?>
                                                                <option value="<?php echo $Item->ShortCode; ?>" <?php echo ($Data->Species == $Item->ShortCode) ? 'selected' : ''; ?>>
                                                                    <?php echo $Item->SpeciesName; ?></option>
                                                            <?php } ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                    <?php
                                                }
                                                ?>
                                                
                                                <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">From Date</label>
                  <input type="date" class="form-control" name='FromDate' id="FromDate"
                  max="<?php echo $today_date; ?>"placeholder="" value="<?php echo $today_date; ?>">
          </div>
          
          
          <div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">To Date</label>
                  <input type="date" class="form-control" name='ToDate' id="ToDate"
                  max="<?php echo $today_date; ?>"placeholder="" value="<?php echo $today_date; ?>">
          </div>
          </div>
                                                
                                                <div align="center" class="col-lg-12">
                                                     
                                                       <button id="list" align="right" type="button" class="btn btn-primary waves-effect waves-light">
                                                Search <i class="ri-arrow-right-line align-middle ml-2"></i> 
                                            </button>
                                                    </div>
                                               
                                                
                                                 


                                        </div>
                        <!-- end row -->
                                    </div>
                                    </div>
                                    </div>
        
                                         <!-- end col -->
                        </div> <!-- end row -->

                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body" id="EmpList">
                                        <h4 class="card-title">Purchased Lot - List</h4>
                                        

                                    </div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->
                        </div>
                        <!-- end row-->


                                    </div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->
                        </div>
                        <!-- end row-->
                        
                    </div> <!-- container-fluid -->
                </div>
                <!-- End Page-content -->

                 <?php include ($IncludesPath."footer.php");?>
                
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        

        <?php include ($IncludesPath."vendor-scripts.php");?>
      

        <!-- Required datatable js -->
         <script src="<?php echo $BaseDist ?>assets/libs/select2/js/select2.min.js"></script>
          
        <script src="<?php echo $BaseDist ?>assets/libs/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/bootstrap-colorpicker/js/bootstrap-colorpicker.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/bootstrap-touchspin/jquery.bootstrap-touchspin.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/bootstrap-maxlength/bootstrap-maxlength.min.js"></script>
         <script src="<?php echo $BaseDist ?>assets/js/pages/form-advanced.init.js"></script>

         
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
        <!-- Buttons examples -->
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons/js/dataTables.buttons.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons-bs4/js/buttons.bootstrap4.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/jszip/jszip.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/pdfmake/build/pdfmake.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/pdfmake/build/vfs_fonts.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons/js/buttons.html5.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons/js/buttons.print.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-buttons/js/buttons.colVis.min.js"></script>

        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-keytable/js/dataTables.keyTable.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-select/js/dataTables.select.min.js"></script>
        
        <!-- Responsive examples -->
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive/js/dataTables.responsive.min.js"></script>
        <script src="<?php echo $BaseDist ?>assets/libs/datatables.net-responsive-bs4/js/responsive.bootstrap4.min.js"></script>

        <!-- Datatable init js -->
        <script src="<?php echo $BaseDist ?>assets/js/pages/datatables.init.js"></script>

        <script src="<?php echo $BaseDist ?>assets/js/app.js"></script>

    </body>
</html>

<script>
$('document').ready(function(){
        getData(true);

        $(document).on('click','#list',function(e){
            e.preventDefault();

            getData()
        });
    });


function getData(isOnload=false){
    
            var PsId=$('#PurchaseStationID').val();
            var FromDate=$('#FromDate').val();
            var ToDate=$('#ToDate').val();
            var SpeciesType=$('#drpSpecies').val();
            if ($('#drpSpecies').length && SpeciesType=="") {
                if(!isOnload)
                alert('Please Select Species Type');
            } else {
                    $.ajax({
                  url:'Ajax/AjaxReportList',
                  type:'POST',
                  data:{'PsId' : PsId,'Month': FromDate,'Year' : ToDate,'Type': 2, 'SpeciesType': SpeciesType},
                  success:function(data){
                     showData(data);

                            }
                    });
            }       

    
}

</script>

<script>
$(document).on('change','#PurchaseStationID',function(e){
       e.preventDefault();
        getData();
        });
        

$(document).on('change','#Month',function(e){           
            e.preventDefault();     
            getData();
});             
        
        
        
        

  



function showData(data){
            
                    $("#EmpList").html(data);
 
        var table = $('#key-datatable').DataTable( {
        scrollY: "600px",
         scrollX:true,
          //sScrollX: "100%",
        sScrollXInner: "150%", 
        // scrollCollapse:true,
        paging:true,
        destroy: true,
        fixedColumns: true,
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
            buttons: [],
         bSort : false,
          "bStateSave": true,
           "fnStateSave": function (oSettings, oData) {
            localStorage.setItem('key-datatable', JSON.stringify(oData));
        },
        "fnStateLoad": function (oSettings) {
            return JSON.parse(localStorage.getItem('key-datatable'));
        }
    } );
        }

</script>
