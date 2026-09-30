<?php 

$PageTitleFolder="Sales";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($cp."Includes/IncludePage.php");

//include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");

$AccessType=$_SESSION["AccessType"]; 

$uid=$_SESSION['r_id'];

 $CurrentMonth=date('m');
// $PymtType= $_GET['PymtType'];

// if($PymtType=='Collections'){
	
$PageTitle="Collections List";		
// }ELSE{
	
// $PageTitle="Negotiations List";	
// }

//$PymtType=='Collections';

if($_GET['Year']==''){
$SelectedYear='';
}else{
$SelectedYear=$_SESSION["SelectedYear"];
}
//echo $_SESSION["SelectedYear"];
if($CurrentMonth > 3){	
$FinClsYear=$Currentyear+1;	
}else{
$FinClsYear=$Currentyear;
}

if($SelectedYear!=''){
	
if($CurrentMonth > 3){	
$FinClsYear=$SelectedYear+1;	
}else{
$FinClsYear=$SelectedYear;
}
}
 $SqlExporter="SELECT  ExpID,ExpName FROM `AdminExporterDetails` where ExpType=1";
$StmtExporter=$iconn->query($SqlExporter);


$SqlConsignee="SELECT * FROM `ScrmConsigneeDetails`";
$StmtConsignee=$iconn->query($SqlConsignee);

$SqlCountries="SELECT * FROM `ScrmCountriesList`";
$StmtCountries=$iconn->query($SqlCountries);

$ClientConfigData = (object) $_SESSION['ClientConfigData'];
$DefaultExporterID = $ClientConfigData->DefaultExporterID;

?>

<!doctype html>
<html lang="en">

<link href="https://serp.silverlights.in/Clients/Sme/User.css"  rel="stylesheet" type="text/css" />

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
					
                    <div class="form-group col-lg-3">
                        <label>Select Year: </label>
                        <select class="form-control select2" name="YEAR" id="YEAR">
                        <?php 
                            for($Startyear; $Startyear <= $endYear; $Startyear++) { 
                            $year=($Startyear-1).'-'.($Startyear); 
                        ?>
                                <option <?php if($Startyear == $FinClsYear){ ?> selected="selected" <?php } ?> value="<?php echo $Startyear-1 ?>"><?php echo $year ?></option>
                        <?php }   ?>
                        <select>
                    </div>		
                    
                    <div class="col-lg-3">
                        <label>Select Exporter: </label>
                        <select class="form-control select2" name="drpExporter" id="drpExporter">
                            <option value="">All</option>
                            <?php while($row_add=$StmtExporter->fetch_array()) {
                                $selected = !empty($DefaultExporterID) && ($DefaultExporterID == $row_add['ExpID']) ? 'selected' : '' ; ?>  
                                <option Value="<?php echo $row_add['ExpID'];?>" <?php echo $selected;?>><?php echo $row_add['ExpName'];?></option>
                            <?php } ?>
                        <select>
                    </div>	
				  
                    <div class="col-lg-3">
                        <label>Select Buyer: </label>
                        <select class="form-control select2" name="Buyer" id="Buyer">
                            <option value="">All</option>
                            <?php while($RowConsignee=$StmtConsignee->fetch_array()) {  ?>  
                                <option Value="<?php echo $RowConsignee['ConID'];?>"><?php echo $RowConsignee['ConsigneeName'];?></option>
                            <?php } ?>
                        <select>
                    </div>
					
                    <div class="col-lg-3">
                        <label>Select Country: </label>
                        <select class="form-control select2" name="Country" id="Country">
                            <option value="">All</option>
                            <?php while($RowCountries=$StmtCountries->fetch_array()) { ?>  
                                <option Value="<?php echo $RowCountries['id'];?>"><?php echo $RowCountries['name'];?></option>
                            <?php } ?>
                        <select> 
                    </div>

                    <div class="col-lg-3">
                        <label>Status</label>				
                        <select class="form-control select2" name="InvType" id="InvType">			 
                            <option Value="0">Pending</option>
                            <option Value="1">Completed</option>				
                        </select> 				
                    </div>	
                </div>				  
				  
                <div  class="col-lg-12 text-center">                                                        
                    <button id="list" type="button" class="btn btn-primary waves-effect waves-light"> Search <i class="ri-arrow-right-line align-middle ml-2"></i> </button>
                </div> 
        <!--     <a href="EmployeeCreate.php" class="btn btn-primary btn-sm btn-flat"><i class="fa fa-plus"></i> New Employee</a>--> 
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
                                                <div class="card-body" id="PIList"></div> 
                                                <!-- end card body-->
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

<script>

$('document').ready(function(){
	
	// getPiList();
    
    	$(document).on('click','#payBtn',function(e){
			
			//alert("test");
    	    e.preventDefault();
            $('.trremove').remove();
                         
			var refid=$(this).val();			
            var PO_ID = document.getElementById('PO_ID'); //or grab it by tagname etc
            PO_ID.value = ""+refid;  
            var large2='TEST'                                     						
            $('.modheading').text('Update Status');
            $('#myModal').modal('toggle');								
			//return false;
    	});
		  
    	$('#elisa').on('click',function(){
            alert("test");
    		$('form').submit();
    	});
    });    

</script>

<script>
    $(function(){
        $('#list').click(function(){
            getPiList();
        });
    });

    function getPiList(){
        var exporter=$('#drpExporter').val();
        var year=$('#YEAR').val();
        var buyer=$('#Buyer').val();
        var country=$('#Country').val();
        var billsType=$('#InvType').val();
        $.ajax({
            url:'Ajax/AjaxCollectionsList.php',
            type:'POST',
            data:{'Exporter':exporter,'Year':year,'Consignee':buyer,'Country':country,'BillsType':billsType},
            success:function(data){
                showData(data);			
            }
        });	
    }

</script>
<script>

$(document).ajaxStart(function(){
    $("#wait").css("display", "block");
  }); 
  $(document).ajaxComplete(function(){
    $("#wait").css("display", "none");
  });
  	
</script>


<script>

function showData(data){
			
		$("#PIList").html(data);	

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
		 order: [[1, 'desc']],
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
