<?php

$PageTitle="Insurance Template";
$PageTitleFolder="Insurance";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/api-call.php';
include ($cp."Includes/IncludePage.php");

//include ("RestAPI.php");

// var_dump($resList);

// echo $_SESSION["Token"];

?>
<!DOCTYPE html>

<html lang="en">


<link href="<?php echo  $href ?>"  rel="stylesheet" type="text/css" />
    <head>        
        <?php include ($IncludesPath."title-meta.php"); ?>
        <?php include ($IncludesPath."DbLinks.php");?>
        <?php include ($IncludesPath."head-css.php");?>
        <style>
            body.modal-open {
                overflow: hidden;
            }

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

                        <?php include ($IncludesPath."page-title.php"); ?>			
 
                        <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body" id="ShowDataTable">      	
                            </div> <!-- end col -->
                        </div> <!-- end row -->
    
 



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


<div class="modal fade" id="ModalTempIns" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Insurance Template No : <span></span></h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
	  <form role="form" id="frmTransitType" method="post">
		<div class="modal-body">      
			<div class="showInsList">
			</div>	
		</div>
	  </form>
    </div>
  </div>
</div>


        

		<?php include ($IncludesPath."vendor-scripts.php");?>
		<?php include ($IncludesPath."NewScripts.php");?>
      <?php //include ($IncludesPath."ShowDataTable.php");?>

       
    </body>
</html>				

<script>
var tableInvoice;
function fnViewInvoices(insID) {
    $.ajax({
        url:'Ajax/AjaxInsuranceList.php',
        type:'POST',
        data:{"InsuranceID":insID},
        success:function(data){
            $(".showInsList").html(data);	
            tableInvoice = $('#tblData').DataTable({
                bSort : true,
                destroy: true,
                scrollY: "400px",
                scrollX:true,
                sScrollXInner: "150%", 
                scrollCollapse: true,  // Make the list collapse to max-height applied.
                paging:false,
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
            buttons:[],
                "bStateSave": true,
                "fnStateSave": function (oSettings, oData) {
                    localStorage.setItem('key-datatable', JSON.stringify(oData));
                },
                "fnStateLoad": function (oSettings) {
                    return JSON.parse(localStorage.getItem('key-datatable'));
                }
            });
        }
    });	
}


$(document).on("click", ".btnViewInvoices", function(){
    var insID=$(this).attr("data-insID"); 		
    
    var insText = $(this).closest("tr").find(".tdInsNo").text();
    $(".modal-title span").text(insText);
    
    fnViewInvoices(insID);
    console.log(insId);

});

function fbPageLoad() {
    $.ajax({
        url:'Ajax/AjaxInsuranceTemplatePage.php',
        type:'POST',
        success:function(data){
            $("#ShowDataTable").html(data);	
            var table = $('#key-datatable').DataTable({
                scrollY: "600px",
                scrollX:true,
                sScrollXInner: "150%", 
                paging:true,
                destroy: true,
                fixedColumns: true,
                scrollCollapse: true,  // Make the list collapse to max-height applied.
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
            buttons:[],
                // dom: 'Bfrtip',
                // buttons: [{
                //     extend: 'excelHtml5',
                //     text: 'Download Excel',
                //     className: 'btnExcelDataTable',
                //     title: 'Insurance Templates Report',
                //     footer: true,
                //     exportOptions: {
                //         columns: ':visible'
                //     }
                // }], 
                "bStateSave": true,
                "fnStateSave": function (oSettings, oData) {
                    localStorage.setItem('key-datatable', JSON.stringify(oData));
                },
                "fnStateLoad": function (oSettings) {
                    return JSON.parse(localStorage.getItem('key-datatable'));
                }
            });
        }
    });	
}

$(document).ready(function(){
    fbPageLoad();
});

var UID = "<?php echo $uid ?>";

/* Page Delete */
$(document).on("click", ".btnDeleteInsTemp", function(e) {
    var insTemID = $(this).attr("data-insID");
    var text = "Are you sure you want to delete the Insurance from list.";

    if (confirm(text) == true) {
        $.ajax({
            url:'Ajax/AjaxInsuranceTemplateFunction.php',
            type:'POST',
            data:{"PoID":"", "InsuranceID":insTemID, "PostedUserId":UID, "DeleteTemplate": "T"},
            success:function(data){
                if(data.res = "success"){
                    fbPageLoad();
                }else{
                    conosole.log("Some Error");
                }
            }
        });	
    } else {
        
    }
  
})

/* Popup Delete */
$(document).on("click", ".btnDeleteIns", function() {
    var inspiidID = $(this).attr("data-piid");
    var insTemID = $(this).attr("data-instemid");    
    var text = "Are you sure you want to delete the Insurance from list.";

    if (confirm(text) == true) {
        $.ajax({
            url:'Ajax/AjaxInsuranceTemplateFunction.php',
            type:'POST',
            data:{"PoID":inspiidID, "InsuranceID":insTemID, "PostedUserId":UID, "DeleteTemplate": "T"},
            success:function(data){
                if(data.res = "success"){
                    fnViewInvoices(insTemID);
                    return false;
                }else{
                    conosole.log("Some Error");
                }
            }
        });	
    } else {
        
    }  

})

 $('#ModalTempIns').on('shown.bs.modal', function (e) {
        tableInvoice.columns.adjust().draw();
});


</script>
