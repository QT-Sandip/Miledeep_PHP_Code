<?php 

$PageTitle="Worker Detail Masters";
$PageTitleFolder="HRM";
 $Currentyear=date('Y');

//$Month=date("n", strtotime('m')); exit;
													 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


$SqlDepartments="SELECT * FROM `HrmDepartments`";
$StmtDepartments=$iconn->query($SqlDepartments);

$SqlWorkerCategory = "SELECT * FROM `EmpWorkerSubCategories`";
$SqlWorkerCategoryResult = $iconn->query($SqlWorkerCategory);


 $Startyear=$Currentyear-1;
 $endYear=$Currentyear+1;

IF(ISSET($_SESSION['YEAR'])==''){
	$_SESSION['YEAR'] = date("Y");
	$_SESSION['MONTH'] = date("m");
}

$YEAR=$_SESSION['YEAR'];
$MONTH=	$_SESSION['MONTH'];

if($_GET['MONTH']!=''){
	$month= $_GET['MONTH'];
}else{
	 
	$month= date("m");
$month= trim($month);

}

if($_GET['YEAR']!=''){
	$year= $_GET['YEAR'];
}else{
	 
	$year= date("Y");

}


?>

<!doctype html>
<html lang="en">


    <head>
        
    <?php include($IncludesPath . "title-meta.php"); ?>
  <?php include("Includes/DbLinks.php"); ?>
  <?php include($IncludesPath . "head-css.php"); ?>

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
                                        <!-- Nav tabs -->
                                        <ul class="nav nav-tabs nav-tabs-custom nav-justified" role="tablist">
                                            <li class="nav-item">
                                                <a class="nav-link active" data-toggle="tab" href="#Village-Master" role="tab" data-tab="Village" data-table="VillageMasterTable" id="Village-Masters-Tab">
                                                    <span class="d-block d-sm-none"><i class="fas fa-home"></i></span>
                                                    <span class="d-none d-sm-block">Village</span> 
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" data-toggle="tab" href="#Batch-Master" role="tab" data-tab="Batch" data-table="BatchMasterTable">
                                                    <span class="d-block d-sm-none"><i class="far fa-user"></i></span>
                                                    <span class="d-none d-sm-block">Batch</span> 
                                                </a>
                                            </li>
                                            <li class="nav-item">
                                                <a class="nav-link" data-toggle="tab" href="#Section-Master" role="tab" data-tab="Section" data-table="SectionMasterTable">
                                                    <span class="d-block d-sm-none"><i class="far fa-envelope"></i></span>
                                                    <span class="d-none d-sm-block">Section</span>   
                                                </a>
                                            </li>
                                        </ul>
        
                                        <!-- Tab panes -->
                                        <div class="tab-content p-3 text-muted">
                                            <div class="tab-pane active" id="Village-Master" role="tabpanel">
												<div class="table-responsive">
												<table id="VillageMasterTable" class="table table-bordered" style="width:100%">
												    <thead>
												        <tr>
												            <th>ID</th>
												            <th>Village Name</th>
												            <th>Actions</th> <!-- Edit button column -->
												        </tr>
												    </thead>
												</table>
												</div>                                    
                                            </div>
                                            <div class="tab-pane" id="Batch-Master" role="tabpanel">
													<div class="table-responsive">
													<table id="BatchMasterTable" class="table table-bordered" style="width:100%">
													    <thead>
													        <tr>
													            <th>ID</th>
													            <th>Batch Name</th>
													            <th>Actions</th> <!-- Edit button column -->
													        </tr>
													    </thead>
													</table>
													</div>                                    
                                            </div>
                                            <div class="tab-pane" id="Section-Master" role="tabpanel">
													<div class="table-responsive">
													<table id="SectionMasterTable" class="table table-bordered" style="width:100%">
													    <thead>
													        <tr>
													            <th>ID</th>
													            <th>Section Name</th>
													            <th>Actions</th> <!-- Edit button column -->
													        </tr>
													    </thead>
													</table>
													</div>                                    
                                            </div>
                                        </div>
        
                                    </div>
                                </div>
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
                 <?php include ($IncludesPath."leave_register.php");?>
                
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        

     	<?php include_once ROOT . 'includes/common-js.php' ?>
		<?php include ($IncludesPath."vendor-scripts.php");?>
        <?php include ("Includes/NewScripts.php");?>
     	<?php include_once WORKING_MODULE . 'modalWorkerMasters.php' ?>

    </body>
</html>				



<Script>

$('document').ready(function(){
	var doa_doc='<?php echo $DOA_DOC?>';
	
    $("#DOA_DOC").val(doa_doc);
    	$(document).on('change','#Dept',function(e){
			
    	e.preventDefault();
			               
			var Dept=$(this).val();
			
			$.ajax({
		  url:'Ajax/AjMonthlyAtt',
		  type:'POST',
		  data:{'Dept':Dept},
		  success:function(data){
			  //console.log(data);
			  // var obj = JSON.parse(data);
			$("#SubDept").html(data);
			$("#PACK_SIZE").html('<option value="">All Designation</option> ');
					}
			})

    	});
    
    	$('#continuebtn').on('click',function(){
		
    		$('form').submit();
    	});
    });  
  	

</script>


<script>
/*$(function(){
   
   $(document).on("click", ".edit", function(e){
	  e.preventDefault();
    
	 $('#addnew').modal('show');
    var id = $(this).data('id');
		$("#leave_type option[value='casualleave']").remove();
	
	
	$('#leave_type option').each(function(){
    if (this.value == 'casualleave') {
        //exists = true;
        //return false;
    }else {
		//$("#leave_type").append('<option value="casualleave">Casual Leave</option>');
	}
});
		
    
  });
  

  $('.delete').click(function(e){
    e.preventDefault();
    $('#delete').modal('show');
    var id = (this).data('id');
    getRow(id);
  });
});*/

	//uma	
	$(document).on('click','#BtnAddMessDeduction',function(e){			
			e.preventDefault();

			var EmpBioID=$(this).val();
			var Amount='#Amount'+EmpBioID;
			
			var Month=$('#MONTH').val();
			var Year=$('#YEAR').val();
			
	  
	  var Amount=$(Amount+"").val();
		//alert(Amount);
	
var r = confirm("Are You Sure Do You Want To Submit?");
  if (r == true) {
$.ajax({
    type: 'POST',
    url: 'Ajax/AjAddMessDeduction.php',
    data: {'Amount':Amount,'Type':'AddMessDeduction','EmpBioID':EmpBioID,'Year':Year,'Month':Month},
	
	//dataType: 'json',
    
    success: function(response){
		var data = JSON.parse(response);
			if(data['sts'] == 'failed'){
			alert(data['msg']);
			}else{
				$('#myModal').modal('hide');
			//alert(data['msg']);
							//location.reload();
							
			loadMessDeductionList();

				
			}
	 }
  });
}else{}	

		
    	});	
		
</script>



<Script>

$('document').ready(function(){
	var doa_doc='<?php echo $DOA_DOC?>';
	
    $("#DOA_DOC").val(doa_doc);
    	
		

		
		

    
    	$('#continuebtn').on('click',function(){
			alert('hi');
    
    		$('form').submit();
    	});
    });  

$(document).ajaxStart(function(){
    $("#wait").css("display", "block");
  });
  $(document).ajaxComplete(function(){
    $("#wait").css("display", "none");
  });
  	
function loadMessDeductionList(){
		
			var Dept=$('#Dept').val();
			var SubDept=$('#SubDept').val();
			var MONTH=$('#MONTH').val();
			var DATE=$('#DATE').val();
			var YEAR=$('#YEAR').val();
			var EmpCategory = $('#EmpCategory').val();
			var WorkerSubCategory = $('#WorkerSubCategory').val();
			
		
			$.ajax({
		  url:'Ajax/AjMessDeductionList',
		  type:'POST',
		  data:{'DeptID':Dept,'SubDeptID': SubDept,'Month':MONTH,'Year': YEAR},
		  success:function(data){
			 //  console.log(data);
 //$("#ATT_DATA").html(data);
 //$('#example1').DataTable()
 showData(data);
 
 
 
					}
			})
			//return false;
		
}
</script>
<script>
function showData(data){
	
	$("#ATT_DATA").html(data);
			
					//$("#EmpList").html(data);
 
 		
 		var Stable=    $('#scroll-vertical-datatable').DataTable({
        "scrollY":        "450px",
        "scrollCollapse": true,
        "paging":         false,
        "language": {
            "paginate": {
                "previous": "<i class='mdi mdi-chevron-left'>",
                "next": "<i class='mdi mdi-chevron-right'>"
            }
        },
        "drawCallback": function () {
            $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
        },
		buttons: ['copy', 'excel', 'pdf', 'colvis']
    });
	
	Stable.buttons().container()
        .appendTo('#scroll-vertical-datatable_wrapper .col-md-6:eq(0)');
		}




</script> 
  <script src="https://markcell.github.io/jquery-tabledit/assets/js/tabledit.min.js"></script>
<script>
selectedTab="";
$(document).ready(function () {
setTimeout(function(){
	$('.nav-item .active').trigger('click');	
},500);
table = "";

loadDynamicTable();

$(document).on('click','#list',function(e){			
	e.preventDefault();
	loadDynamicTable();
});

function loadDynamicTable() {

}

});


$('#EmpCategory').on('change', function (e) {	  
	  const EmpCategory = $(this).val();
	  if(EmpCategory == 'Worker'){
	  	$('#worker-type-select').show();
	  }else{
	  	$('#worker-type-select').hide();
	  }
	});

	$(document).ready(function(){
		const EmpCategory = $('#EmpCategory').val();
		if(EmpCategory == "Worker"){
	  		$('#worker-type-select').show();
		}
	});

$('#WorkerSubCategory').on('change', function (e) {	  
  const WorkerType = $(this).val();

  if (WorkerType == '1') {
    $('#worker-batch-select').show();
    $('#worker-section-select').show();
  } else if (WorkerType == '2') {
    $('#worker-batch-select').show();
    
    // clear and hide section select
    $('#worker-section-select select').val(null).trigger("change");
    $('#worker-section-select').hide();
  } else {
    // clear both and hide both
    $('#worker-batch-select select').val(null).trigger("change");
    $('#worker-section-select select').val(null).trigger("change");
    $('#worker-batch-select').hide();
    $('#worker-section-select').hide();
  }
});


    $('#WorkerSubCategory').on('change', function (e) {     
      const WorkerCategory = $(this).val();
      var Month=$('#MONTH').val();
      var Year=$('#YEAR').val();
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
    });

    $('#MONTH').on('change', function (e) {   
      var Month=$('#MONTH').val();
      var Year=$('#YEAR').val();
	  var EmpCategory = $('#EmpCategory').val();
	  var WorkerSubCategory = $('#WorkerSubCategory').val();
	  if( EmpCategory=='Worker' && WorkerSubCategory == '2' ){
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

$('.nav-link').on('click',function(){
	var Type = $(this).attr('data-tab');
	var TableID = $(this).attr('data-table');
	getTable(Type,TableID);
});

function getTable(Type,TableID){
	selectedTab = Type;
	            var Dept=$('#Dept').val();
				var SubDept=$('#SubDept').val();
				var MONTH=$('#MONTH').val();
				var DATE=$('#DATE').val();
				var YEAR=$('#YEAR').val();
				var EmpCategory = $('#EmpCategory').val();
				var WorkerSubCategory = $('#WorkerSubCategory').val();
				var FifteenDaysSub=$('#FifteenDaysSub').val();


	    if(EmpCategory == 'Worker'){
	        if (WorkerSubCategory == "") {
	            $("#WorkerSubCategory").next().find('.select2-selection').addClass('error');
	            return false;
	        } else {
	            $("#WorkerSubCategory").next().find('.select2-selection').removeClass('error');
	            if(WorkerSubCategory == 2){
	                if(FifteenDaysSub == ""){
	                     $("#FifteenDaysSub").next().find('.select2-selection').addClass('error');
	                     return false;                    
	                 }else{
	                    $("#FifteenDaysSub").next().find('.select2-selection').removeClass('error');                    
	                 }
	            }     

	        }
	    }

		if ($.fn.DataTable.isDataTable('#' + TableID)) {
		    $('#' + TableID).DataTable().destroy();
		}
	    table = $('#' + TableID).DataTable({
	        "lengthMenu": [[100,200,500], [100,200,500]],
	        scrollX: true,
	        scrollY: '400px',
	        scrollCollapse: true,
	        paging: true,
	        processing: true,
	        serverSide: true,
	        ajax: {
	            url: 'Ajax/AjaxGetWorkerMasters',
	            type: 'POST',
	            data: function (param) {
	             param.Type = Type;
	            }            
	        },
	        columns: [
	            { data: 'ID' },
	            { data: 'Field' },
	            { data: 'edit_button' }  // Column for Edit button
	        ],
	        drawCallback: function () {
	            // // Initialize editable fields
	            // $('.editable').editable();
				$('#newBatch').remove();
				$('#' + TableID+'_length').append(
					'<a href="#" id="newBatch" data-type="'+Type+'" data-bs-toggle="modal" data-bs-target="#modalAddWorkerBatch" class="btn btn-success waves-effect waves-light" id="add-village-btn">\
						<i class="fa fa-plus"></i> New '+Type+'</a>');
	        }
	    });

		    // 🔄 UNBIND previous handlers to prevent multiple calls
		    $('#' + TableID).off('click', '.edit-btn');
		    $('#' + TableID).off('click', '.save-btn');
		    $('#' + TableID).off('click', '.cancel-btn');

		    var columnNames = ['ID', 'inputValue']; // same order as columns

		    $('#' + TableID).on('click', '.edit-btn', function () {
		        var row = $(this).closest('tr');
		        var rowData = table.row(row).data();

			    // Replace each editable cell with an input, excluding first two columns
			    row.find('td').each(function (index) {
			        // Skip first two columns and last column (action buttons)
			        if (index >= 1 && index < columnNames.length) {
			            var value = $(this).text().trim();
			            $(this).attr('data-original', value); // store original value
			            $(this).html('<input type="text" class="form-control" name="' + columnNames[index] + '" value="' + value + '">');
			        }
			    });

		        // Add Save and Cancel buttons
		        row.find('td:last').html(`
		            <button class="btn btn-sm btn-success save-btn" data-id="${rowData.id}">Save</button>
		            <button class="btn btn-sm btn-secondary cancel-btn" data-id="${rowData.id}">Cancel</button>
		        `);
		    });


    $('#' + TableID).on('click', '.save-btn', function () {
        var row = $(this).closest('tr');
        var inputs = row.find('input');
        var RowID = row.attr('data-empid');
        var data = {};
        inputs.each(function () {
            var name = $(this).attr('name');
            var value = $(this).val();
            data[name] = value;
        });
        data['ID'] = RowID;
        data['inputType'] = 'Add'+Type;

        $.post('Ajax/AjaxWorkerMastersUpdate', data, function (response) {
            console.log(data);

            // Replace each input with plain text
            inputs.each(function () {
                var value = $(this).val();
                $(this).parent().html(value);
            });

            // Restore Edit button
            row.find('td:last').html(`
                <button class="btn btn-sm btn-primary edit-btn">Edit</button>
            `);
        }).fail(function (err) {
        	ErrorMsg = JSON.parse(err.responseText);
        	if(ErrorMsg){
        		alert(ErrorMsg.error);
        	}else{
	            alert('Save failed!');        		
        	}
        });
    });




    // Handle Cancel button click (for cancelling the edit)
    $('#' + TableID).on('click', '.cancel-btn', function () {
        var row = $(this).closest('tr');
        var rowData = table.row(row).data();

        // Revert each input cell to original data
        row.find('td').each(function (index) {
            if (index < 10) {
                var original = $(this).attr('data-original');
                $(this).html(original);
            }
        });

        // Restore Edit button
        row.find('td:last').html(
        	`<button class="btn btn-sm btn-primary edit-btn" data-id="${rowData.id}">Edit</button>
					<button type="button" id="btnDelete" name="btnDelete" data-id="${rowData.id}" class="btn btn-link p-0 deleteWorkerMaster">
						<i class="mdi mdi-delete font-size-24 text-danger"></i>
                    </button>        	
        	`);

    });	
}


    $(document).on('click','#newBatch',function(){
    	var Type = $(this).attr('data-type');
    	$('#inputValue').val('');
    	$('#inputValue').removeClass('error');
		$('#modalAddWorkerBatch .modal-title').text("Add " + Type + " Name");
        $('#inputType').val('Add'+Type);
        $('#labelName').html(Type+' Name<span style="color:red">*</span>');
        $('#inputValue').attr('placeholder', "Enter "+Type+" Name");
    })
	$(document).on('click','.deleteWorkerMaster', function (){
        if (confirm("Are you sure you want to delete this item ?") == true) {
	        var row = $(this).closest('tr');
	        var inputs = row.find('input');
	        var RowID = row.attr('data-empid');
	        var data = {};
	        inputs.each(function () {
	            var name = $(this).attr('name');
	            var value = $(this).val();
	            data[name] = value;
	        });
        data['ID'] = RowID;
        data['inputType'] = selectedTab;
        data['Action'] = 'DELETE';

        $.post('Ajax/AjaxWorkerMastersUpdate', data, function (response) {
            console.log(data);
        // Refresh the table data from the server
        $('#' + selectedTab+'MasterTable').DataTable().ajax.reload(null, false); // false = don't reset pagination
        }).fail(function (err) {
        	ErrorMsg = JSON.parse(err.responseText);
        	if(ErrorMsg){
        		alert(ErrorMsg.error);
        	}else{
	            alert('Save failed!');        		
        	}
        });

        }
    });
</script>