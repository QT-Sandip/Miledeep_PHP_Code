<?php 

$PageTitle="Employee Additions";
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
									
										<div class="row">

											<div class="form-group col-lg-3">
												<select class="form-control select2" name='EmpCategory' id="EmpCategory">
													<option <?php if ($RowEmployeeDetails['EmpCategory'] == "Staff") { ?>selected="selected" <?php } ?> value="Staff">Staff</option>
													<option <?php if ($RowEmployeeDetails['EmpCategory'] == "Worker") { ?>selected="selected" <?php } ?> value="Worker">Worker</option>
												</select>
											</div>

											<div class="form-group col-lg-3" id="worker-type-select">
												<select class="form-control select2" name='WorkerSubCategory' id="WorkerSubCategory">
													<option value="">Select Worker SubCategory </option>
														<?php while ($RowWorkerType = $SqlWorkerCategoryResult->fetch_array()) {
														?>	
 															<option <?php if ($RowWorkerType['CategoryID'] == $RowEmployeeDetails['WorkerSubCategory']) { ?>selected="selected" <?php } ?> value="<?php echo $RowWorkerType['CategoryID']; ?>">
  																<?php echo $RowWorkerType['CategoryName']; ?>
  															</option>

  													<?php } ?>
												</select>
											</div>
                                                <div class="col-lg-3">
                                                    <div class="form-group">
                 <!-- <label for="exampleInputEmail1">DEPARTMENT </label>-->
                  <select class="form-control select2" name="Dept" id="Dept">
				   
				  <?php $AryDepartmentNames=array();?>
				 <option value="">All Departments</option>
				 <?php while($RowDepartment=$StmtDepartments->fetch_array()){
					 $DeptName=$RowDepartment['DepartmentName'];
					 if(in_array($DeptName, $AryDepartmentNames)){
					
					}else{
						$AryDepartmentNames[]=$DeptName;
						
					
					 ?>	
				 
					<option value="<?php echo $RowDepartment['DeptID'] ;?>" ><?php  echo $RowDepartment['DepartmentName']; ?></option>
				<?php  }  } ?>
				  <select>
				  </div>
				  </div>
				  
				  
				  
                    <div class="col-lg-3">
                                                    
		            <div class="form-group">
                     <!--<label for="exampleInputEmail1">SUB DEPARTMENT</label>-->
                  <select class="form-control select2" name="SubDept" id="SubDept">
				   <option value="">All Sub Department</option>
				  <select>
				  </div>
				  </div>
				  
				  
				  <div class="col-lg-3">
												
                                                    <div class="form-group">
                                                        
                                                        <select class="form-control select2" name="MONTH" id="MONTH" size='1'>
                                                     <?php
													 
													  $start = strtotime('first day of this month');
													  /*$Date = date('d-m-Y');
													  $Month=*/
													  
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
				  
	<!--<div class="col-lg-3">                                                
		     <div class="form-group">			
                  <select class="form-control select2" name="MONTH" id="MONTH">
                   <?php
                     for ($i = 0; $i < 12; $i++) {
                      $time = strtotime(sprintf('%d months', $i), $start);   
		               $label = date('F', $time);   
                       $value = date('n', $time);
                   echo "<option value='$value'>$label</option>";
                   }  ?>     
				  <select>
				  </div>
				  </div>-->
				  
				  <div class="col-lg-3">
								  <div class="form-group " >
                 
                  <select class="form-control select2" name="YEAR" id="YEAR">
                           <?php
						   for($Startyear; $Startyear <= $endYear; $Startyear++) { ?>
                                                            
              <option  <?php if($Startyear == $Currentyear){ ?> selected="selected" <?php }?>
						 value="<?php echo $Startyear ?>"><?php echo $Startyear ?></option>
							                                
							<?php }   ?>
                   <select>
				  </div>
				  </div>
            <div class="form-group col-lg-3" id="fifteen-days-sub">
                <select class="form-control select2" name='FifteenDaysSub' id="FifteenDaysSub">
                    <option value="">Select Workers Date Range</option>
                </select>
            </div>				  

				  
		 <div align="center" class="col-lg-12" >
		 								  <div class="form-group " >

		 <br>
		  <!--<label for="exampleInputEmail1"> </label>-->
                 
		         <button id="list" align="center" type="button" class="btn btn-primary waves-effect waves-light">
				 Search<i class= 'ri-arrow-right-line align-middle ml-2'>
				 </i> </button>
				  </div>
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
                                    <div class="card-body" id="ATT_DATA">
                                        <!-- <h4 class="card-title">Employee - Mess Deductions</h4> -->
<div class="table-responsive">
<table id="dynamicTable" class="table table-bordered" style="width:100%">
    <thead>
        <tr>
            <th>Bio Id</th>
            <th>Name</th>
            <th>Emp. Category</th>
            <th>Arrear Amount</th>
            <th>Shipment Charges</th>
            <th>Commission</th>
            <th>Sunday Incentive</th>
            <th>OThours</th>
            <th>Actions</th> <!-- Edit button column -->
        </tr>
    </thead>
</table>
</div>                                    

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
                 <?php include ($IncludesPath."leave_register.php");?>
                
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        

		<?php include ($IncludesPath."vendor-scripts.php");?>
<?php include ("Includes/NewScripts.php");?>


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
$(document).ready(function () {
table = "";
loadDynamicTable();

$(document).on('click','#list',function(e){			
	e.preventDefault();
	loadDynamicTable();
});	
function loadDynamicTable() {
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
	if ($.fn.DataTable.isDataTable('#dynamicTable')) {
	    $('#dynamicTable').DataTable().destroy();
	}
    table = $('#dynamicTable').DataTable({
        "lengthMenu": [[100,200,500], [100,200,500]],
        scrollX: true,
        scrollY: '400px',
        scrollCollapse: true,
        paging: true,
        processing: true,
        serverSide: true,
        ajax: {
            url: 'Ajax/AjaxGetEmpAdditions',
            type: 'POST',
            data: function (param) {
             param.month = MONTH;
             param.year = YEAR;
             param.dept= Dept;
             param.subdept=SubDept;
             param.empcategory=EmpCategory;
             param.workersubcategory=WorkerSubCategory;
             param.monthsegment=FifteenDaysSub;
            }            
        },
        columns: [
            { data: 'BioMetricID' },
            { data: 'EmpName' },
            { data: 'EmpCategory' },
            { data: 'ArrearAmount' },
            { data: 'ShipmentCharges' },
            { data: 'Commission' },
            { data: 'SundayIncentive' },
            { data: 'OThours' },
            { data: 'edit_button' }  // Column for Edit button
        ],
        columnDefs: [
            { targets: [4], orderable: false, searchable: false }  // Disable sorting for the edit column
        ],
        drawCallback: function () {
            // // Initialize editable fields
            // $('.editable').editable();
        }
    });
}

    var columnNames = ['BioMetricID', 'EmpName', 'EmpCategory', 'ArrearAmount', 'ShipmentCharges', 'Commission', 'SundayIncentive', 'OThours']; // same order as columns

    $('#dynamicTable').on('click', '.edit-btn', function () {
        var row = $(this).closest('tr');
        var rowData = table.row(row).data();

	    // Replace each editable cell with an input, excluding first two columns
	    row.find('td').each(function (index) {
	        // Skip first two columns and last column (action buttons)
	        if (index >= 3 && index < columnNames.length) {
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



    $('#dynamicTable').on('click', '.save-btn', function () {
        var row = $(this).closest('tr');
        var inputs = row.find('input');
        var EmpID = row.attr('data-empid');
        var Month = $('#MONTH').val();
        var Year = $('#YEAR').val();
		var BioID = row.find('td').eq(0).text().trim();
		var FifteenDaysSub=$('#FifteenDaysSub').val();

        var data = {};
        inputs.each(function () {
            var name = $(this).attr('name');
            var value = $(this).val();
            data[name] = value;
        });
        data['Month'] = Month;
        data['Year'] = Year;
        data['EmpID'] = EmpID;
        data['BioID'] = BioID;
        data['MonthSegment'] = FifteenDaysSub;

        $.post('Ajax/AjaxUpdateEmpAdditions', data, function (response) {
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
    $('#dynamicTable').on('click', '.cancel-btn', function () {
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
        row.find('td:last').html(`<button class="btn btn-sm btn-primary edit-btn" data-id="${rowData.id}">Edit</button>`);
    });


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

// $('#EmpCategory').val("Staff").trigger("change").prop("disabled", true);

</script>