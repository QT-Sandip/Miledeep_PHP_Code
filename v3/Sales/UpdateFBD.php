<?php 
$PageTitle="ETA/ETD Status";
$PageTitleFolder="Sales";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");

$uid=$_SESSION['r_id'];

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

$month = date('m');
if ($month >= 4) {
  
    $year = date('Y');
} else {
   
    $year = date('Y') - 1;
}
$EndDate = date("Y-m-d");



$SqlExporter="SELECT ExpID,ExpName FROM `AdminExporterDetails` where ExpType=1";
$StmtExporter=$iconn->query($SqlExporter);

$SqlExporter="SELECT LinerID,LinerName FROM `ScrmLinerMaster`";
$StmtLinear=$iconn->query($SqlExporter);

$SqlConsignee="SELECT * FROM `ScrmConsigneeDetails` LEFT JOIN `ScrmCountriesList` ON ScrmConsigneeDetails.ConsigneeCountry = ScrmCountriesList.id";
$StmtConsignee=$iconn->query($SqlConsignee);


$SqlProcessor="SELECT  ExpID,ExpName,ExpApprovalNo FROM `AdminExporterDetails` where ExpType=2 ";
$StmtProcessor=$iconn->query($SqlProcessor);

$ClientConfigDataObj = (object) $_SESSION['ClientConfigData'];
$DefaultExporterID = $ClientConfigDataObj->DefaultExporterID;

?>

<!doctype html>
<html lang="en">


<link href="<?php echo  $href ?>" rel="stylesheet" type="text/css" />

<head>

    <?php include ($IncludesPath."title-meta.php"); ?>

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
                                        <div class="col-lg-3 mb-3">
                                            <label for="NAME_OF_PROCESSOR">Select Exporter: </label>

                                            <select class="form-control select2" name="NAME_OF_PROCESSOR"
                                                id="NAME_OF_PROCESSOR">
                                                <option value="">All</option>

                                                <?php while($row_add=$StmtExporter->fetch_array()) {
                                                    $selected = !empty($DefaultExporterID) && ($DefaultExporterID == $row_add['ExpID']) ? 'selected' : '' ; ?>

                                                <option Value="<?php echo $row_add['ExpID'];?>" <?php echo $selected;?>>
                                                    <?php echo $row_add['ExpName'];?></OPTION>

                                                <?php } ?>
                                            </select>
                                        </div>

                                      
										<div class="col-lg-3 mb-3">
                                            <label for="processor">Plant</label>
                                            <select class="form-control select2" name="processor" id="processor">
                                                <option value="">All</option>
                                                <?php while($RowProcessor=$StmtProcessor->fetch_array()) { ?>  
                                                <option Value="<?php echo $RowProcessor['ExpID'];?>"><?php echo $RowProcessor['ExpName'].'-'.$RowProcessor['ExpApprovalNo'];?></option>
                                                <?php } ?>
                                            <select> 
                                        </div>

                                        <div class="col-lg-3  mb-3">
                                            <label for="LinerID">Liner</label>
                                            <select class="form-control select2" name="LinerID" id="LinerID">
                                                <option value="">All</option>
                                                <?php  
                                                    while($row_add=$StmtLinear->fetch_array()) {  
				                                        echo '<option value="'. $row_add["LinerID"] .'"> '. $row_add["LinerName"] .'</option>';
                                                    }
                                                ?>
                                            </select>
                                        </div>        
                                        
                                        <div class="col-lg-3 mb-3">
                                            <label for="drpConsignee">Consignee</label>
                                            <select class="form-control select2" name="drpConsignee" id="drpConsignee">
                                                <option value="">All</option>   
                                                <?php while($RowConsignee=$StmtConsignee->fetch_array()) { ?>  
                                                <option Value="<?php echo $RowConsignee['ConID'];?>"><?php echo $RowConsignee['ConsigneeName'].' - '.$RowConsignee['name'];?></option>
                                                <?php } ?>                                             
                                            </select>
                                        </div>

                                        <div class="col-lg-3 mb-3">
                                            <label for="ETAFromDate">ETA From Date (Discharge)</label>
                                            <input type="date"   class="form-control" name="ETAFromDate" id="ETAFromDate" />
                                        </div>

                                        <div class="col-lg-3 mb-3">
                                            <label for="ETAToDate">ETA To Date (Discharge)</label>
                                            <input type="date" class="form-control" name="ETAToDate" id="ETAToDate" />
                                        </div>
										
										
                                   <!-- 
								   value='<?php echo $year; ?>-04-01' value='<?php echo $EndDate; ?>' 
								   <div class="col-lg-3  mb-3">
                                            <label for="drpLiner">Liner</label>
                                            <select class="form-control select2" name="drpLiner" id="drpLiner">
                                                <option value="">All</option>                                                
                                            </select>
                                        </div> -->

                                        

                                        <!-- <div class="col-lg-3  mb-3">
                                            <label>Consignee</label>
                                            <select class="from-control select2" name="Consignee" id="Consignee">
                                                <option value=" ">ALL</option>
                                                <option value="Consignee1">Consignee 1</option>
                                                <option value="Consignee2">Consignee 2</option>
                                            </select>
                                        </div>

                                        <div class="col-lg-3  mb-3">
                                            <label>ETA From:</label>
                                            <input type='date' class="from-control select2" name="ETAFrom" id="ETAFrom" />
                                        </div> -->


                                        <div class="col-lg-3 text-left mt-4">
                                            <button id="list" align="left" type="button"
                                                class="btn btn-primary"
                                                >
                                                Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                                            </button>
                                        </div>



                                        <!--<div class="form-group col-lg-2">
                	  <label>Select Year: </label>
                   <select class="form-control select2" name="YEAR" id="YEAR">
                         
                           <?php
							 						
                            /*for($Startyear; $Startyear <= $endYear; $Startyear++) { 
						
									$year=($Startyear-1).'-'.($Startyear); ?>
                                                            
                        <option  
						
						<?php  if($Startyear == $FinClsYear){ ?> selected="selected" 
						<?php   }   	?>			
						
						
						
						
				   
				   value="<?php echo $Startyear-1 ?>"><?php echo $year ?></option>
							
                                                         
							<?php }  */ ?>
                   <select>
				  </div>-->
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
                                <input type="hidden" id="scrollRight" />
                                <div class="card-body" id="ShowDataTable">
                                    <!--<h4 class="card-title">Proforma Invoice List</h4>-->


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
    <?php // include ($IncludesPath."ShowDataTable.php");?>









    <Script>
    $('document').ready(function() {

        //getPiList(); 
        $('#elisa').on('click', function() {
            //alert("test");
            $('form').submit();
        });
    });
    </script>

    <script>
    $(function() {
        $('#example1').dataTable({
            "order": [
                [0, 'desc']
            ]
        });
    })
    </script>

    <script>
    $(document).on('click', '.BtnEditETA', function(e) {
        e.preventDefault();

        //var PiID=$(this).val();
        var PiID = $(this).data('id');
        var EstimatedETA = '#EstimatedETA' + PiID;
        var ETARemarks = '#ETARemarks' + PiID;
        //var ContainerGateOutDate='#ContainerGateOutDate'+PiID;			
        //var EmptyContainerReturnDate='#EmptyContainerReturnDate'+PiID;
        //var EntryNumber='#EntryNumber'+PiID;
        var IsFDA = '#IsFDA' + PiID;
        var IsFDASelector = '#IsFDA' + PiID; // IsFDA5


        var EstimatedETA = $(EstimatedETA + "").val();
        var ETARemarks = $(ETARemarks + "").val();
        // var ContainerGateOutDate=$(ContainerGateOutDate+"").val();
        //  var EmptyContainerReturnDate=$(EmptyContainerReturnDate+"").val();
        // var EntryNumber=$(EntryNumber+"").val();
        var IsFDA = $(IsFDA + "").val();
        //var IsFDASelector =$(IsFDA+"").val();
        if ($(IsFDASelector).prop('checked')) {
            //if (IsFDA!='') {
            var isFDAChecked = "1";
        } else {
            var isFDAChecked = "0";
        }
        //alert(isFDAChecked); 

        var r = confirm("Do you Want To Submit?");
        if (r == true) {

            $.ajax({
                type: 'POST',
                url: 'Ajax/AjaxDelete.php',
                data: {
                    'EstimatedETA': EstimatedETA,
                    'ETARemarks': ETARemarks,
                    'TYPE': 'UPDATEETA',
                    'PiID': PiID,
                    'IsFDAChecked': isFDAChecked
                },

                /*data: {'EstimatedETA':EstimatedETA,'ETARemarks':ETARemarks,'EmptyContainerReturnDate':EmptyContainerReturnDate,'ContainerGateOutDate':ContainerGateOutDate,'EntryNumber':EntryNumber,
	'TYPE':'UPDATEETA','PiID':PiID,'IsFDAChecked':isFDAChecked},*/


                success: function(response) {
                    $("#scrollRight").val("T");
                    getPiList();


                }
            });
        } else {
            //location.reload();
        }

    });
    </script>


    <script>
    $(function() {
        $('#list').click(function() {

            getPiList();



        });
    });


    function getPiList() {
        var EXPORTER = $('#NAME_OF_PROCESSOR').val();
        var YEAR = $('#YEAR').val();
        var LinerID = $('#LinerID').val();
        var ETAToDate = $('#ETAToDate').val();
        var ETAFromDate = $('#ETAFromDate').val();
        var ConsigneeID = $('#drpConsignee').val();
        var processor = $('#processor').val();

  
 
  
        $.ajax({
            url: 'Ajax/AjaxFBDPoList.php',
            type: 'POST',

            data: {
                'EXPORTER': EXPORTER,
                'TYPE': 'POLIST',
                'ConsigneeID': ConsigneeID,
                'processor': processor,
                'ETAToDate': ETAToDate,
                'ETAFromDate': ETAFromDate,
                'LinerID': LinerID
            },
            success: function(data) {
                //showData(data);
                
                $("#ShowDataTable").html(data);

                var table = $('#key-datatable').DataTable({

                    scrollY: "600px",
                    scrollX: true,
                    sScrollXInner: "150%",
                    paging: true,
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

                    // columnDefs:[{
                    //     "targets":10,
                    //     "orderable": false
                    // }],
                    buttons: [$.extend( true, {}, fixNewLine, {
                            extend: 'excelHtml5',
                            // extend: 'excel',
                            text: 'Download Excel',
                            className: 'btnExcelDataTable',
                            title: 'ETA_Status'
                        }),],
                    bSort: false,
                    "bStateSave": true,
                    "fnStateSave": function(oSettings, oData) {
                        localStorage.setItem('key-datatable', JSON.stringify(oData));
                    },
                    "fnStateLoad": function(oSettings) {
                        return JSON.parse(localStorage.getItem('key-datatable'));
                    }
                });
                if ($("#scrollRight").val() == "T") {
                    $(".dataTables_scrollBody").scrollLeft(3000);
                }
            }
        });
        // var fixNewLine = {
        //     exportOptions: {
        //         format: {
        //             body: function ( data, column, row ,node) {
        //                 // Strip $ from salary column to make it numeric
        //                 if(row===5 || row===6 || row===7){
        //                     return  data.replace( /<br\s*\/?>/ig, "\n" );
        //                 }else if(/* row === 13 || */ row === 20 || row === 21){
        //                     return node.innerText;//if any html content exists display plain text
        //                 }
        //                     return  data.replace( /<.*?>/ig, "");//if any html content exists then display plain text
        //             }
        //         }
        //     }
        // };
        var fixNewLine = {
    exportOptions: {
        format: {
            body: function (data, column, row, node) {
                var temp = document.createElement("div");
                temp.innerHTML = data.replace(/<br\s*\/?>/ig, "\n");
                return temp.textContent || temp.innerText || "";
            }
        }
    }
};

    }



    $(document).on('click', '#BtnTrack', function(e) {
        e.preventDefault();

        var ContainerTrackingUrl = $(this).val();
        var PiID = $(this).data('id');
        //alert(PiID);

        var ContainerNo = '#ContainerNo' + PiID;

        var ContainerNo = $(ContainerNo + "").val();
        //var ContainerNo='#ContainerNo';
        //var ContainerNo=$(ContainerNo+"").val();
        //alert(ContainerNo);

        copyToClipboard(ContainerNo);


        function copyToClipboard(ContainerNo) {
            var dummy = document.createElement("input");
            // to avoid breaking orgain page when copying more words
            // cant copy when adding below this code
            // dummy.style.display = 'none'
            document.body.appendChild(dummy);
            //Be careful if you use texarea. setAttribute('value', value), which works with "input" does not work with "textarea". – Eduard
            dummy.value = ContainerNo;
            dummy.select();
            document.execCommand("copy");
            document.body.removeChild(dummy);
        }

        window.open('https://' + ContainerTrackingUrl, '_blank');
        //window.location.href = 'https://'+ContainerTrackingUrl;






    });
    </script>

</body>

</html>
