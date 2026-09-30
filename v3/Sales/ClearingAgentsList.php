<?php 
$PageTitle="Clearing Agents List";
$PageTitleFolder="Payments";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");

$uid=$_SESSION['r_id'];

$SqlCountries="SELECT * FROM `ScrmCountriesList`";
$StmtCountries=$iconn->query($SqlCountries);

// Get the current month
$month = date('m');
// Check if the current month is greater than or equal to April
if ($month >= 4) {
    // Get the current year
    $year = date('Y');
} else {
    // Get the previous year
    $year = date('Y') - 1;
}
// Print the financial year
//echo $year . '-' . ($year + 1);

$EndDate = date("Y-m-d");


?>

<!doctype html>
<html lang="en">


<link href="<?php echo  $href ?>"  rel="stylesheet" type="text/css" />
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

    <!--<body data-sidebar="dark" class="sidebar-enable vertical-collpsed">-->
    <body data-sidebar="dark" class="sidebar-enable">
	
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
										<?php include "PopUpTable.php"; ?>

						
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body">	  
				  	
                                        <div class="row">                                            				
                                                
                                            <div class="form-group col-lg-4">
                                                <label>From Date</label>
                                                <input type='date' value='<?php echo $year;?>-04-01' class="form-control" name="fromDate" id="fromDate"></input>
                                            </div>  
                                            
                                            <div class="form-group col-lg-4">
                                                <label>To Date</label>
                                                <input type='date' value='<?php echo $EndDate;?>' class="form-control" name="toDate" id="toDate"></input>
                                            </div>

                                            <div class="col-lg-4">
                                                <label>Status: </label>
                                                <select class="form-control select2" name="drpStatus" id="drpStatus">
                                                    <option value="0">Pending</option>
                                                    <option value="1">Completed</option>
                                                <select>
                                            </div>	
                                            
                                           <!-- <div class="col-lg-3">
                                                <label>Select Country: </label>
							                    <select class="form-control select2" name="drpCountry" id="drpCountry">
			                                      <option value="" >All</option>
				                                   <?php while($RowCountries=$StmtCountries->fetch_array()) { 
				                                   ?> 
                                                   <option Value="<?php echo $RowCountries['id'];?>"><?php echo $RowCountries['name'];?></OPTION>
				                                   <?php } ?>
				                               <select> 
                                            </div>-->

                                            <div class="col-lg-12 text-center">                                                     
                                                <button id="list" type="button" class="btn btn-primary waves-effect waves-light">
                                                    Search <i class="ri-arrow-right-line align-middle ml-2"></i> 
                                                </button>				
                                            </div>   

                                        </div>
                                            
                                            
		 
											   
										</div>
									</div>
									</div>
									</div>
        
                                         <!-- end col -->
                        </div> <!-- end row -->
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body" id="ShowDataTable">
                                            
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
      
<?php include ($IncludesPath."NewScripts.php");?>
		<?php include ($IncludesPath."ShowDataTable.php");?>
  

    </body>

<script>
//$(function(){
    $('#list').click(function(){
        getClearingAgentsPoList();    
    });


    $(document).on("click", ".btnDeleteClearingAgentDetails", function() {
        var id = $(this).attr("data-PoId");
        var text = "Are you sure do you want to delete this?";

        if (confirm(text) == true) {
            $.ajax({
                url:'Ajax/AjaxClearingAgentChargesBreakUp.php',
                type:'POST',
                data:{"PoID":id, "DeleteCaBreakupCharges": "T"},
                success:function(data){
                    if(data.res = "success"){
                         getClearingAgentsPoList();    
                    }else{
                        conosole.log("Some Error");
                    }
                }
            });	
        } else {
            
        }
    }); 

//});
function getClearingAgentsPoList(){
    var status=$('#drpStatus').val();
        var fromDate = $('#fromDate').val();
        var toDate = $('#toDate').val();
       // var country = $('#drpCountry').val();

        $.ajax({
            url:'Ajax/AjaxClearingAgentsList.php',
            type:'POST',
            data:{'StatusType':status,'FromDate':fromDate,'ToDate':toDate,'Country':'157'},
            success:function(data){
                showData(data);

            }
        });	
}


function showData(data){
			
    $("#ShowDataTable").html(data);
                var table = $('#key-datatable').DataTable({
                    scrollY: "600px",
                    scrollX: true,
                    //sScrollX: "100%",
                    sScrollXInner: "150%",
                    scrollCollapse:true,
                    paging: true,
                    destroy: true,
                    fixedColumns: true,
                    bSort: true,
                    order: [
                        [1, 'asc']
                    ],
                    "bStateSave": true,
                    // dom: 'Blfrtip',
                    // buttons: [{extend: 'excel', text: 'Download Excel', className: 'btnExcelDataTable',title: 'MPEDA Report', footer: true,
                    //     exportOptions: {
                    //         columns: ':visible'
                    //     }
                    // }],
                    "fnStateSave": function(oSettings, oData) {
                        localStorage.setItem('key-datatable', JSON.stringify(oData));
                    },
                    "fnStateLoad": function(oSettings) {
                        return JSON.parse(localStorage.getItem('key-datatable'));
                    }
                });
                
    
    }

</script>

