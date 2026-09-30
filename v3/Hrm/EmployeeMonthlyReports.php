<?php 
$PageTitle="Monthly Reports";
$PageTitleFolder="HRM";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
$SqlDeptList="SELECT * FROM HrmDepartments";
$StmtDept=$iconn->query($SqlDeptList);
$today_date=date('Y-m-d');


$SqlWorkerCategory = "SELECT * FROM `EmpWorkerSubCategories`";
$SqlWorkerCategoryResult = $iconn->query($SqlWorkerCategory);

$ArrayClientConfigData = json_decode(json_encode($_SESSION['ClientConfigData']), true);
$IsEmpCategoryEnabled  = $ArrayClientConfigData['SplitStaffAndWorkerSalarySheets'];

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
        <style type="text/css">
            .error{border-color:#FF0000!important;}
        </style>

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
                                                <?php 
                                                if($IsEmpCategoryEnabled){
                                                ?>
                                                <div class="col-lg-3">
                                                    <select class="form-control select2" name='EmpCategory' id="EmpCategory">
                                                        <option value="">Select Category </option>
                                                        <option <?php if ($RowEmployeeDetails['EmpCategory'] == "Staff") { ?>selected="selected" <?php } ?> value="Staff">Staff</option>
                                                        <option <?php if ($RowEmployeeDetails['EmpCategory'] == "Worker") { ?>selected="selected" <?php } ?> value="Worker">Worker</option>
                                                    </select>
                                                </div>
                                                <?php    
                                                }
                                                ?>
                                            
                                            <div class="form-group col-lg-3" id="worker-type-select">
                                                <select class="form-control select2" name='WorkerCategory' id="WorkerCategory">
                                                    <option value="">Select Worker Category</option>
                                                        <?php while ($RowWorkerType = $SqlWorkerCategoryResult->fetch_array()) {
                                                        ?>  
                                                            <option value="<?php echo $RowWorkerType['CategoryID']; ?>">
                                                                <?php echo $RowWorkerType['CategoryName']; ?>
                                                            </option>

                                                    <?php } ?>
                                                </select>
                                            </div>
                <div class="col-lg-3">
                    <div class="form-group">
                    <select class="form-control select2" name="DeptID" id="DeptID">
                    <option value="">Select Department</option>
                                                        
                    <?php while($DataRow=$StmtDept->fetch_array()){?>   
                 
                    <option value="<?php echo $DataRow['DeptID'] ;?>" >
                    <?php  echo $DataRow['DepartmentName']; ?></option>
                    <?php    } ?>
                    </select>
                </div>
              </div>                        
                        
                <div class="col-lg-3">
                 <div class="form-group">
                                                        
                 <select class="form-control select2"  name="SubDeptID"     id="SubDeptID">
                 <option value="">Select SubDepartment</option>
                 </select>
               </div>
               </div>                       

        <div class="col-lg-3">
                                                    <div class="form-group">
                                                        
                                                        <select class="form-control select2"  name="Month" id="Month">
                                                            <?php
                                                            $start = strtotime('first day of this month');
                                                     for ($i = 0; $i < 12; $i++) {
                                                          $time = strtotime(sprintf('%d months', $i), $start);   
                                                       
                                                          $label = date('F', $time);   
                                                          $value = date('n', $time);
                                                          echo "<option value='$value'>$label</option>";
                                                         }
                                                         
                                                         ?>
                                                        </select>
                                                    </div>
                                                </div>
                                                
                                                <div class="col-lg-3">
                                                    <div class="form-group">
                                                        
                                                        <select class="form-control select2"  name="Year" id="Year">
                                                           <?php for($Startyear; $Startyear <= $Currentyear; $Startyear++) { ?>
                                                            >
                                                                 <option value="<?php echo $Startyear ?>"<?php if($Startyear==$Currentyear){ echo 'selected="selected"'; }?>>
                                                                 <?php echo $Startyear ?></option>
                                                             
                                                            <?php  } 
                                                         
                                                            ?>
                                                        </select>
                                                    </div>
                                                </div>        
                                                <?php 
                                                if($IsEmpCategoryEnabled){
                                                ?>
                                                <div class="form-group col-lg-3" id="fifteen-days-sub">
                                                    <select class="form-control select2" name='FifteenDaysSub' id="FifteenDaysSub">
                                                        <option value="">Select Workers Date Range</option>
                                                    </select>
                                                </div>
                                                <?php    
                                                }
                                                ?>
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
                        <div class="container-fluid">
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body" id="EmpList">
                                        <h4 class="card-title">Reports List</h4>
                                        

                                    </div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->
                        </div>
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

$(document).on('change','#DeptID',function(e){
       e.preventDefault();
       var DeptId=$(this).val();
       var Type='SubDept';

        $.ajax({
          url:'Ajax/AjDropDowns',
          type:'POST',
          data:{'DeptId':DeptId,'Type':Type},
          success:function(data){
            $("#SubDeptID").html(data);
                    }
            })
        
    
        });
        
$('document').ready(function(){
        getData();

        $(document).on('click','#list',function(e){
            e.preventDefault();

            getData()
        });
    });


function getData(){
        
    var DeptID=$('#DeptID').val();
    var SubDeptID=$('#SubDeptID').val();
    var Month=$('#Month').val();
    var Year=$('#Year').val();
    var EmpCategory=$('#EmpCategory').val();
    var EmpSubCategory=$('#WorkerCategory').val();
    var FifteenDaysSub=$('#FifteenDaysSub').val();

    if(EmpCategory == 'Worker'){
        if (EmpSubCategory == "") {
            $("#WorkerCategory").next().find('.select2-selection').addClass('error');
            return false;
        } else {
            if(EmpSubCategory == 2){
                $("#WorkerCategory").next().find('.select2-selection').removeClass('error');
                if(FifteenDaysSub == ""){
                     $("#FifteenDaysSub").next().find('.select2-selection').addClass('error');
                     return false;                    
                 }else{
                    $("#FifteenDaysSub").next().find('.select2-selection').removeClass('error');                    
                 }
            }     

        }
    }
    
    $.ajax({
    url:'Ajax/AjMontlyReportsList',
    type:'POST',
    data:{'DeptID' : DeptID,'SubDeptID': SubDeptID,'Month':Month,'Year' : Year,'Type': 2,EmpCategory:EmpCategory,EmpSubCategory:EmpSubCategory,MonthSegment:FifteenDaysSub},
    success:function(data){
     showData(data);

            }
    });
    
}

</script>

<script>

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
         bSort : true,
          "bStateSave": true,
           "fnStateSave": function (oSettings, oData) {
            localStorage.setItem('key-datatable', JSON.stringify(oData));
        },
        "fnStateLoad": function (oSettings) {
            return JSON.parse(localStorage.getItem('key-datatable'));
        }
  } );
        }

    $('#EmpCategory').on('change', function (e) {     
      const EmpCategory = $(this).val();
      if(EmpCategory == 'Worker'){
        $('#worker-type-select').show();
      }else{
        // Reset Select2 value
        $('#worker-type-select select') // or adjust selector if needed
          .val(null)                     // clear value
          .trigger('change');           // notify Select2
        $('#worker-type-select').hide();
      }
    });

    $('#WorkerCategory').on('change', function (e) {     
      const WorkerCategory = $(this).val();
      var Month=$('#Month').val();
      var Year=$('#Year').val();
      if( WorkerCategory == 2){
        monthSegmentData(Year,Month);
      }else{
        $('#fifteen-days-sub select')
          .val(null)
          .trigger('change');        
        $('#fifteen-days-sub').hide();
      }

$('#FifteenDaysSub').select2({
    templateResult: formatOption,
    templateSelection: formatOption
});
});
function formatOption(state) {
    if (!state.id) return state.text;

    // Highlight "Days left" text if it exists
    const text = state.text;
    const daysLeftRegex = /(.*?)( - \d+ Days left)/;
    const match = text.match(daysLeftRegex);

    if (match) {
        return $(`<span>${match[1]}<span style="color: red; font-weight: bold;">${match[2]}</span></span>`);
    } else {
        return $('<span>' + text + '</span>');
    }
}

$('#Month').on('change', function (e) {   
  var Month=$('#Month').val();
  var Year=$('#Year').val();
  var EmpCategory = $('#EmpCategory').val();
  var WorkerCategory = $('#WorkerCategory').val();
  if( EmpCategory=='Worker' && WorkerCategory == '2' ){
      monthSegmentData(Year,Month);
  }
});

function monthSegmentData(Year,Month){
    $.ajax({
        url:'Ajax/AjWorkersMonthSegments',
        type:'POST',
        data:{'Year':Year,'Month':Month},
        dataType:'JSON',
        success:function(data){
                // Clear existing options (except default)
                $('#FifteenDaysSub').find('option:not(:first)').remove();
                // Append each item as an option
                data.data.forEach(function(label, index) {
                    $('#FifteenDaysSub').append(
                        $('<option>', {
                            value: index+1, // or use label as value if needed
                            text: label
                        })
                    );
                });
                $('#fifteen-days-sub').show();

            }
    }); 
}

</script>
