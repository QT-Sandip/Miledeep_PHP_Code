<?php

$PageTitle="Pay Received";
$PageTitleFolder="Reports";
$AccessType=$_SESSION['AccessType']; 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");


$uid=$_SESSION['r_id'];
//echo $href;exit;
 $CurrentMonth=date('m');	
//echo $endYear;exit;

if($CurrentMonth >3){	
    $FinClsYear=$Currentyear+1;	
}else{
    $FinClsYear=$Currentyear;
}
 //echo $endYear;exit;

$SqlExporter="SELECT  ExpID,ExpName FROM `AdminExporterDetails` where ExpType=1";
$StmtExporter=$iconn->query($SqlExporter);

?>
<!DOCTYPE html>

<html lang="en">

<link href="<?php echo  $href; ?>"  rel="stylesheet" type="text/css" />
    <head>        
        <?php include ($IncludesPath."title-meta.php"); ?>
        <?php include ($IncludesPath."DbLinks.php");?>
        <?php include ($IncludesPath."head-css.php");?>

                <!-- <link href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css" rel="stylesheet" type="text/css"/> -->

    </head>

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
                        <div class="row">
		
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body" id="ShowDataTable">									

                                    <div class="row">
                                        <div class="col-lg-4">
                                            <label>Select Year: </label>
                                            <select class="form-control select2" name="year" id="year">                         
                                                    <?php	 						
                                                        for($Startyear; $Startyear <= $endYear; $Startyear++) { 						
                                                                $year=($Startyear-1).'-'.($Startyear); 
                                                    ?>                                                            
                                                    <option <?php  if($Startyear == $FinClsYear) { echo 'selected="selected"'; } ?>
                                                            value="<?php echo $Startyear-1 ?>"> <?php echo $year ?></option>                                                         
                                                        <?php }   ?>
                                            <select>
                                        </div>

                                        <div class="col-lg-4">
                                            <label>Select Exporter: </label>				
                                            <select class="form-control select2" name="drpExporter" id="drpExporter">
                                                <?php while($row_add=$StmtExporter->fetch_array()) { ?>  				
                                                <option Value="<?php echo $row_add['ExpID'];?>"><?php echo $row_add['ExpName'];?></option>				
                                                <?php } ?>
                                                <option value="" >All</option>
                                            <select>
                                        </div>

                                        <div class="col-lg-4">
                                            <label>Type </label>				
                                            <select class="form-control select2" name="type" id="type">			 
                                                <option Value="0">Available</option>
                                                <option Value="1">Utilized</option>				
                                            </select> 				
                                        </div>
                                        <div class="col-lg-12 mt-4 text-center">			  	
                                            <button id="list" type="button" class="btn btn-primary waves-effect waves-light text-left">
                                                Search <i class="ri-arrow-right-line align-middle ml-2"></i> 
                                            </button>
                                            <!-- <a href="AddForwardContract.php" class="btn btn-success waves-effect waves-light"><i class="fa fa-file-excel"></i> Download Excel</a>
                                            <a href="AddForwardContract.php" class="btn btn-success waves-effect waves-light"><i class="fa fa-file-pdf"></i> Download PDF</a> -->

                                        </div>
                                    </div>

                                                   	
									</div> <!-- end col -->
                                </div> <!-- end row -->

                                <div class="col-12">
                                    <div class="card">
                                        <div class="card-body" id="PIList">
                                            
                                        </div>
                                    </div>
                                </div>


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
         <?php // include ($IncludesPath."ShowDataTable.php");?>
       
    </body>
</html>				



<script>
$(function(){
	$('#list').click(function(){
		var exporter=$('#drpExporter').val();
        var year=$('#year').val();
        var type=$('#type').val();
        
        $.ajax({
            url:'Ajax/AjaxPayReceivedList.php',
            type:'POST',
            data:{"Type":type,"Year":year,"Exporter":exporter},
            success:function(data){
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
                    "bStateSave": true,
                    dom: 'Blfrtip',
                    buttons: [{extend: 'excel', text: 'Download Excel', className: 'btnExcelDataTable', title:"Realization_Done_Report"}],
                    "fnStateSave": function (oSettings, oData) {
                        localStorage.setItem('key-datatable', JSON.stringify(oData));
                    },
                    "fnStateLoad": function (oSettings) {
                        return JSON.parse(localStorage.getItem('key-datatable'));
                    }
                });	
            }
        });	
	});
});

$(document).ajaxStart(function(){
	$("#wait").css("display", "block");
}); 
$(document).ajaxComplete(function(){
	$("#wait").css("display", "none");
});

</script>
