<?php 
$PageTitle="MPEDA Report";
$PageTitleFolder="Reports";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");

$uid=$_SESSION['r_id'];



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



 $CurrentMonth=date('m');	

 /*if($_GET['Year']=='Na'){
$SelectedYear='';
}else{
$SelectedYear=$_SESSION["SelectedYear"];
}*/
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



$SqlExporter="SELECT ExpID,ExpName FROM `AdminExporterDetails` where ExpType=1";
$StmtExporter=$iconn->query($SqlExporter);

$ClientConfigDataObj = (object) $_SESSION['ClientConfigData'];
$DefaultExporterID = $ClientConfigDataObj->DefaultExporterID;

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

        <style>
            table.subTable td {
                border: 0px !important;
            }
        </style>

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

                                            <div class="col-lg-4">
                                                <label>Select Exporter: </label>
                                                <select class="form-control select2" name="drpExporter" id="drpExporter">
                                                    <option value="" >All</option>
                                                    <?php while($row_add=$StmtExporter->fetch_array()) {
                                                        $selected = !empty($DefaultExporterID) && ($DefaultExporterID == $row_add['ExpID']) ? 'selected' : '' ;  ?>  
                                                    <option Value="<?php echo $row_add['ExpID'];?>" <?php echo $selected;?>><?php echo $row_add['ExpName'];?></option>
                                                    <?php } ?>
                                                <select>
                                            </div>					
                                                
                                            <div class="form-group col-lg-4">
                                                <label>From Date</label>
                                                <input type='date' value='<?php echo $year;?>-04-01' class="form-control" name="fromDate" id="fromDate"></input>
                                            </div>  
                                            
                                            <div class="form-group col-lg-4">
                                                <label>To Date</label>
                                                <input type='date' value='<?php echo $EndDate;?>' class="form-control" name="toDate" id="toDate"></input>
                                            </div>

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
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
		<?php include ($IncludesPath."ShowDataTable.php");?>
  

    </body>

<script>
// $(function(){

    $('#list').click(function(){

        var exporter=$('#drpExporter').val();
        var fromDate = $('#fromDate').val();
        var toDate = $('#toDate').val();

        $.ajax({
            url:'Ajax/AjaxMpedaList.php',
            type:'POST',
            data:{'Exporter':exporter,'FromDate':fromDate,'ToDate':toDate},
            success:function(data){
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

                    order: [
                        [1, 'asc']
                    ],
                    //"bStateSave": true,

                    buttons: [{extend: 'excel', text: 'Download Excel', className: 'btnExcelDataTable',title: 'Mpeda Report', footer: true,
                    exportOptions: {
                        columns: ':visible',
                        format: {
                            body: function (data, row, column, node) {

                                const dateColumns = [2];

                                if (dateColumns.includes(column)) {
                                    return moment(data, "DD-MM-YYYY").format("YYYY-MM-DD");
                                } else {
                                    return data;
                                }
                                
                            }
                        }
                        
                    }
                }],



                    // dom: 'Blfrtip',
                    // buttons: [{extend: 'excel', text: 'Download Excel', className: 'btnExcelDataTable',title: 'MPEDA Report', footer: true,
                    //     exportOptions: {
                    //         columns: ':visible',
                    //         // stripNewlines: true,

                    //     },
                    //     // body: function ( data, row, column, node ) {
                    //     //     // return data.replace(/<br\s*\/?>/ig, "\r\n");
                    //     //     re = /<br\s*\/?>/i;
                    //     //     if (re.test(data)) {
                    //     //         return '"' + data.replace(/<br\s*\/?>/ig, "\n") + '"';
                    //     //     } else {
                    //     //         return data;
                    //     //     }
                    //     // }
                    // }],
                    "fnStateSave": function(oSettings, oData) {
                        localStorage.setItem('key-datatable', JSON.stringify(oData));
                    },
                    "fnStateLoad": function(oSettings) {
                        return JSON.parse(localStorage.getItem('key-datatable'));
                    },
                    "fnInitComplete": function () {
                        var tfoot = $('#key-datatable tfoot').html();
                            tfoot = '<tfoot>'+tfoot+'</tfoot>';
                        $('#key-datatable tfoot').remove();
                        $(tfoot).insertAfter('#key-datatable > tbody');

                        //$('<div class="dt-buttons btn-group flex-wrap"><button class="btn btn-secondary buttons-excel btnExcelDataTable" type="button"><span>Download Excel</span></button></div>').insertAfter("#ShowDataTable .dataTables_filter label");
                        //$("#key-datatable tr, #key-datatable th, #key-datatable th .dataTables_sizing").css("height", "0px");
                    },
                    drawCallback: function() {
                        $(document).trigger('afterSearch');
                    }

                });

            }

        });	

    });
// });

function columnSum(classname, floatNumber) {
    var sum = 0;

    $(classname).each(function () {
        var str = $(this).text();
        val = str.replace(/\,|\$/g, "");

        sum += parseFloat(val);
    });
    return sum.toFixed(floatNumber);
}

$(document).on('afterSearch', function() {

    var bodyTotInv = $(".bodyTotInv").length;
    var bodyTotalNetWgtKgs = columnSum(".bodyTotalNetWgtKgs",2);
    var bodyTotalFOBValueINR = columnSum(".bodyTotalFOBValueINR",2);
    var bodyTotalFOBValueInForeignCurrency = columnSum(".bodyTotalFOBValueInForeignCurrency",2);

    $(".footTotInv div, .dataTables_scrollFootInner .footTotInv").text(bodyTotInv);
    $(".footTotalNetWgtKgs div, .dataTables_scrollFootInner .footTotalNetWgtKgs").text(bodyTotalNetWgtKgs);
    $(".footTotalFOBValueINR div, .dataTables_scrollFootInner .footTotalFOBValueINR").text(bodyTotalFOBValueINR);
    $(".footTotalFOBValueInForeignCurrency div, .dataTables_scrollFootInner .footTotalFOBValueInForeignCurrency").text(bodyTotalFOBValueInForeignCurrency);

    $("#key-datatable tr, #key-datatable th, #key-datatable th .dataTables_sizing").css("height", "0px");

    var tfoot = $('#key-datatable tfoot').html();
        tfoot = '<tfoot>'+tfoot+'</tfoot>';
    $('#key-datatable tfoot').remove();
    $(tfoot).insertAfter('#key-datatable > tbody');
});

function exportTableToExcel(tableID, filename = '') {
    var downloadLink;
    var dataType = 'data:application/vnd.ms-excel';
    var tableSelect = document.getElementById(tableID);
    // var tableSelect = document.getElementsByClassName(tableID);

    // Apply CSS styles for borders
    // tableSelect.style.border = '1px solid #eff2f7';
    // var trs = tableSelect.getElementsByTagName('tr');
    // for (var i = 0; i < trs.length; i++) {
    //     trs[i].style.border = '1px solid #eff2f7';
    //     var tds = trs[i].getElementsByTagName('td');
    //     for (var j = 0; j < tds.length; j++) {
    //         tds[j].style.border = '1px solid #eff2f7';
    //     }
    // }

    tableSelect.style.border = '1px solid #eff2f7';
    tableSelect.style.borderCollapse = 'collapse';

    var ths = tableSelect.getElementsByTagName('th');
    for (var i = 0; i < ths.length; i++) {
        ths[i].style.border = '1px solid #eff2f7';
    }

    var tds = tableSelect.getElementsByTagName('td');
    for (var i = 0; i < tds.length; i++) {
        tds[i].style.border = '1px solid #eff2f7';
    }

    $("#key-datatable tr, #key-datatable th, #key-datatable th .dataTables_sizing").css("height", "auto");


    var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');

    filename = filename ? filename + '.xls' : 'MPEDA_Report.xls';

    downloadLink = document.createElement('a');
    document.body.appendChild(downloadLink);

    if (navigator.msSaveOrOpenBlob) {
        var blob = new Blob(['\ufeff', tableHTML], {
            type: dataType
        });
        navigator.msSaveOrOpenBlob(blob, filename);
    } else {
        downloadLink.href = dataType + ', ' + tableHTML;
        downloadLink.download = filename;
        downloadLink.click();
    }
}

$(document).on("click", ".btnExcelDataTable", function(){
    // var excel_data = $('#key-datatable').html();  

    var tfoot = $('#key-datatable tfoot').html();
        tfoot = '<tfoot>'+tfoot+'</tfoot>';
    $('#key-datatable tfoot').remove();
    $(tfoot).insertAfter('#key-datatable > tbody');

    exportTableToExcel('key-datatable', 'Mpeda Report');
    $("#key-datatable tr, #key-datatable th, #key-datatable th .dataTables_sizing").css("height", "0px");
});
$("#fromDate").on("change", function () {
    $("#ShowDataTable").html("");
});
</script>
