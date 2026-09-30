<?php 
$PageTitle="Monthly Attendance";
$PageTitleFolder="HRM";

$Currentyear=date('Y');


include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

 $SqlHrmDept="SELECT DeptID,DepartmentName FROM `HrmDepartments`";
$StmtHrmDept=$iconn->query($SqlHrmDept);

$SqlWorkerCategory = "SELECT * FROM `EmpWorkerSubCategories`";
$SqlWorkerCategoryResult = $iconn->query($SqlWorkerCategory);

$start = strtotime('first day of this month');

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

         <?php include ($IncludesPath."title-meta.php"); ?>

        <!-- DataTables -->
        <?php  include ("Includes/DbLinks.php");?>
        <?php include ($IncludesPath."head-css.php");?>
		<style type="text/css">

			table.dataTable td {
			white-space: nowrap;
			vertical-align: middle;
			}

			table.dataTable th {
			white-space: nowrap;
			}

			.dt-center {
			text-align: center;
			}


			/* Prevent squishing and overflow clipping */
			#mytable th,
			#mytable td {
			min-width: 60px;
			white-space: nowrap;
			text-align: center;
			vertical-align: middle;
			}

			/* First two columns (Name, Bio ID) a bit wider */
			#mytable th:nth-child(1),
			#mytable td:nth-child(1) {
			width: 138px;
			text-align: left;
			}


			#mytable th:nth-child(2),
			#mytable td:nth-child(2) {
			min-width: 142px;
			text-align: left;
			}

			#mytable th, #mytable td {
			white-space: nowrap;
			text-align: center;
			vertical-align: middle;
			}

			#mytable th {
			font-weight: bold;
			}

			/* Prevent double-line header for date (like "1\nThu\nPH") */
			#mytable th span {
			display: inline-block;
			white-space: nowrap;
			}

			#mytable td {
			padding-top: 8px;
			padding-bottom: 8px;
			height: 40px;
			width: 100px;
			}
			#mytable th:nth-child(n+3),
			#mytable td:nth-child(n+3) {
			width: 75px;
			min-width: 75px;
			}


			#mytable td {
			color: #333;
			}

			#mytable td:contains("A") {
			color: red;
			font-weight: bold;
			}

			#mytable td:contains("PH") {
			color: orange;
			font-weight: bold;
			}

			#mytable td:matches(\d{2}:\d{2}) {
			color: green;
			}

			/* Sticky first two columns */
			#mytable th:nth-child(1),
			#mytable td:nth-child(1) {
			position: sticky;
			left: 0;
			background: white;
			z-index: 2;
			border-right: 1px solid #ccc;
			}

			#mytable th:nth-child(2),
			#mytable td:nth-child(2) {
			position: sticky;
			left: 138px; /* width of column 1 */
			background: white;
			z-index: 2;
			border-right: 1px solid #ccc;
			}

			#mytable tbody tr:hover td:first-child,
			#mytable tbody tr:hover td:nth-child(2) {
			background-color: #eef !important;  /* match row hover color */
			z-index: 3;  /* ensure it stays on top */
			color: black !important;
			}

			/* Zebra stripe for rows */
			#mytable tbody tr:nth-child(even) {
				background-color: #f9f9f9;
			}

			/* Slight hover highlight */
			#mytable tbody tr:hover {
				background-color: #eef;
			}

			/* Slightly reduce font size for better fit */
			#mytable {
				font-size: 13px;
			}

			/* Scroll-friendly container if needed */
			#mytable-container {
			overflow-x: auto;
			max-width: 100%;
			}

			#mytable tbody tr:nth-child(even) {
			background-color: #f9f9f9;
			}

			#mytable {
			font-size: 13px;
			}

			/* Style absent entries */
			.absent {
			color: red;
			font-weight: bold;
			}

			.absent {
			color: red;
			font-size: 13px;
			}
			.dataTables_wrapper {
			overflow-x: auto;
			}
		</style>
    </head>

    <body data-sidebar="dark">

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
                        <?php include ($IncludesPath."PreLoader.php");?>

       <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body ">

										<div class="row">
											<div class="form-group col-lg-3">

                                                <select class="form-control select2" name='EmpCategory' id="EmpCategory" required>
                                                    <option value="">Select Category </option>
                                                    <option <?php if ($RowEmployeeDetails['EmpCategory'] == "Staff") { ?>selected="selected" <?php } ?> value="Staff">Staff</option>
                                                    <option <?php if ($RowEmployeeDetails['EmpCategory'] == "Worker") { ?>selected="selected" <?php } ?> value="Worker">Worker</option>
                                                </select>
                                            </div>
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
                 <!-- <label for="exampleInputEmail1">DEPARTMENT </label>-->
                  <select class="form-control select2" name="Dept" id="Dept">

				  <?php $AryDeptNames=array();?>
				 <option value="">All Departments</option>
				 <?php while($RowDept=$StmtHrmDept->fetch_array()){
					 $DeptName=$RowDept['DepartmentName'];
					 if(in_array($DeptName, $AryDeptNames)){

					}else{
						$AryDeptNames[]=$DeptName;


					 ?>

					<option value="<?php echo $RowDept['DeptID'] ;?>" ><?php  echo $RowDept['DepartmentName']; ?></option>
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
<!--                  <label for="exampleInputEmail1">MONTH </label>-->
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
				  </div>

				  <div class="col-lg-3">
				  <div class="form-group " >
                 <!-- <label for="exampleInputEmail1">YEAR </label>-->

				   <select class="form-control select2" name="YEAR" id="YEAR">

                           <?php

                            for($Startyear; $Startyear <= $endYear; $Startyear++) { ?>

                        <option  <?php if($Startyear == $Currentyear){ ?> selected="selected" <?php }?>


				   value="<?php echo $Startyear ?>"><?php echo $Startyear ?></option>


							<?php }   ?>
                   <select>
				  </div>
				  </div><div class="col-lg-3">
				  <div class="form-group " >
                 <!-- <label for="exampleInputEmail1">YEAR </label>-->

				   <select class="form-control select2" name="Roll" id="Roll">
                        <option   value="OnRoll">OnRoll</option>
                        <option   value="OffRoll">OffRoll</option>
                   <select>
				  </div>
				  </div>



		 <div align="center" class="col-lg-3" >
		 								  <div class="form-group " >

		 <br>
		  <!--<label for="exampleInputEmail1"> </label>-->

		         <button id="list" align="center" type="button" class="btn btn-primary waves-effect waves-light"><i class= 'ri-arrow-right-line align-middle ml-2'>
				 </i> GET LIST</button>

			 <a style="display: none;"id="print"  target="_blank" class="btn btn-success waves-effect waves-light"><span class="glyphicon glyphicon-print"></span> Print</a>

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
                                    <div class="card-body" id="ATT_DATA" style="overflow-x: auto; max-width: 100%;">
                                        <h4 class="card-title">Employee - Attendance</h4>
                                        <table id="mytable" class="table table-bordered display nowrap" style="width: 100%;">
									        <thead></thead>
									        <tbody></tbody>
									    </table>


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
        <script>
        	$(function(){
			  $('.edit').click(function(e){
			    e.preventDefault();
			    $('#edit').modal('show');
			    var id = $(this).data('id');
			    getRow(id);
			  });

			  $('.delete').click(function(e){
			    e.preventDefault();
			    $('#delete').modal('show');
			    var id = $(this).data('id');
			    getRow(id);
			  });
			});

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

			function getRow(id){
			  $.ajax({
			    type: 'POST',
			    url: 'attendance_row',
			    data: {id:id},
			    dataType: 'json',
			    success: function(response){
			      $('#datepicker_edit').val(response.date);
			      $('#attendance_date').html(response.date);
			      $('#edit_time_in').val(response.time_in);
			      $('#edit_time_out').val(response.time_out);
			      $('#attid').val(response.attid);
			      $('#employee_name').html(response.firstname+' '+response.lastname);
			      $('#del_attid').val(response.attid);
			      $('#del_employee_name').html(response.firstname+' '+response.lastname);
			    }
			  });
			}

			$('document').ready(function(){

				$('#mytable tbody tr').click(function() {
				    $('#mytable tbody tr').removeClass('highlighted');
				    $(this).addClass('highlighted');
				});

				var doa_doc='<?php echo $DOA_DOC ?>';
    			$("#DOA_DOC").val(doa_doc);

				$(document).on('change','#Dept',function(e){
					e.preventDefault();
					var Dept=$(this).val();
					$.ajax({
						url:'Ajax/AjMonthlyAtt',
						type:'POST',
						data:{'Dept':Dept},
						success:function(data){
							$("#SubDept").html(data);
							$("#PACK_SIZE").html('<option value="">All Designation</option> ');
						}
					});
				});

				$(document).on('change','#REG_NO',function(e){
					e.preventDefault();
					var REG_NO=$(this).val();
					$.ajax({
						url:'Ajax/AjMonthlyAtt',
						type:'POST',
						data:{'REG_NO':REG_NO,'INV_ID': INV_ID},
						success:function(data){
							$("#PACK_SIZE").html(data);
						}
					});
				});

				$(document).on('click','#print',function(e){
					e.preventDefault();
					var Dept=$('#Dept').val();
					var SubDept=$('#SubDept').val();
					var Month=$('#MONTH').val();
					var Year=$('#YEAR').val();
					var Roll=$('#Roll').val();
					var EmpCategory=$('#EmpCategory').val();
					var WorkerCategory=$('#WorkerCategory').val();
					window.open("/v3/Hrm/MonthlyAtttendancePrint.php?Dept=" + Dept + "&SubDept=" + SubDept + "&Month=" + Month + "&Year=" + Year+"&Roll="+Roll+"&EmpCategory="+EmpCategory+"&WorkerCategory="+WorkerCategory);
				});

				$(document).on('click','#list',function(e){
					e.preventDefault();
					var Month=$('#MONTH').val();
					var Year=$('#YEAR').val();
					var current_year = '<?php echo date('Y'); ?>';
					var current_month ='<?php echo date('n'); ?>';
					if( current_year == Year){
						if( parseInt(current_month) < Month ){
							alert("Please choose earlier month!");
							return false;
						}
					}else if( current_year < Year){
							alert("Please choose earlier year!");
							return false;
					}
					if ($.fn.DataTable.isDataTable('#mytable')) {
	                    $('#mytable').DataTable().destroy();
	                }
	                loadDynamicTable();
				});

		    	$('#continuebtn').on('click',function(){
		    		$('form').submit();
		    	});
                /*if ($.fn.DataTable.isDataTable('#mytable')) {
                    $('#mytable').DataTable().destroy();
                }*/
                loadDynamicTable();

            });

            function loadDynamicTable() {
			    var Dept = $('#Dept').val();
			    var SubDept = $('#SubDept').val();
			    var Month = $('#MONTH').val();
			    var Year = $('#YEAR').val();
			    var EmpCategory = $('#EmpCategory').val();
			    var WorkerCategory = $('#WorkerCategory').val();

			    $("#print").show();

			    // First, destroy existing table and clear headers/body
			    if ($.fn.DataTable.isDataTable('#mytable')) {
			        $('#mytable').DataTable().clear().destroy();
			    }
			    $('#mytable thead').empty();
			    $('#mytable tbody').empty();

			    // Fetch dynamic headers
			    $.ajax({
			        url: 'Ajax/AjMonthlyAtt',
			        type: 'POST',
			        data: {
			            'Dept': Dept,
			            'SubDept': SubDept,
			            'Month': Month,
			            'Year': Year,
			            'Type': 'ColumnsOnly',
			            'EmpCategory': EmpCategory,
			            'WorkerCategory': WorkerCategory
			        },
			        dataType: 'json',
			        success: function (response) {
			            let headerRow = '<tr>';
			            let dynamicColumns = [];

			            response.forEach(col => {
			                headerRow += `<th>${col.title}</th>`;
			                dynamicColumns.push({ data: col.data });
			            });

			            headerRow += '</tr>';

			            // Inject header row BEFORE initializing DataTable
			            $('#mytable thead').html(headerRow);

			            // NOW initialize DataTable
			            $('#mytable').DataTable({
			                scrollX: true,
			    scrollCollapse: true,
			    paging: true,
			    serverSide: true,
			    autoWidth: false,
			    fixedHeader: true,
			    ordering: false,
			    columnDefs: [
			        { targets: "_all", className: "dt-center", width: "60px" },
			        { targets: [0, 1], width: "150px" } // Name & Biometric ID wider
			    ],
			                ajax: {
			                    url: 'Ajax/AjMonthlyAtt',
			                    type: 'POST',
			                    data: {
			                        'Dept': Dept,
			                        'SubDept': SubDept,
			                        'Month': Month,
			                        'Year': Year,
			                        'Type': 'List',
			                        'EmpCategory': EmpCategory,
			                        'WorkerCategory': WorkerCategory
			                    }
			                },
			                columns: dynamicColumns
			            });
			        }
			    });
			}
		</script>
    </body>
</html>
<?php if(0){ ?>

<script>
$(function(){
  $('.edit').click(function(e){
    e.preventDefault();
    $('#edit').modal('show');
    var id = $(this).data('id');
    getRow(id);
  });

  $('.delete').click(function(e){
    e.preventDefault();
    $('#delete').modal('show');
    var id = $(this).data('id');
    getRow(id);
  });
});

function getRow(id){
  $.ajax({
    type: 'POST',
    url: 'attendance_row',
    data: {id:id},
    dataType: 'json',
    success: function(response){
      $('#datepicker_edit').val(response.date);
      $('#attendance_date').html(response.date);
      $('#edit_time_in').val(response.time_in);
      $('#edit_time_out').val(response.time_out);
      $('#attid').val(response.attid);
      $('#employee_name').html(response.firstname+' '+response.lastname);
      $('#del_attid').val(response.attid);
      $('#del_employee_name').html(response.firstname+' '+response.lastname);
    }
  });
}
</script>


<Script>

$('document').ready(function(){
	var doa_doc='<?php echo $DOA_DOC?>';
	//var doa_doc='<?php echo $COUNTRY?>';

	//alert(doa_doc);

    $("#DOA_DOC").val(doa_doc);
    	$(document).on('change','#Dept',function(e){

    	e.preventDefault();

			var Dept=$(this).val();
			//var INV_ID=<?php echo $INVOICE_ID ?>;

			//alert(DEPT_CODE);
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
			//return false;



    	});


		$(document).on('change','#REG_NO',function(e){
			e.preventDefault();
			var REG_NO=$(this).val();
			//var INV_ID=<?php echo $INVOICE_ID ?>;

			//alert(INV_ID);
			$.ajax({
		  url:'Ajax/AjMonthlyAtt',
		  type:'POST',
		  data:{'REG_NO':REG_NO,'INV_ID': INV_ID},
		  success:function(data){
			  //console.log(data);
			  // var obj = JSON.parse(data);
			$("#PACK_SIZE").html(data);
					}
			})
			//return false;

    	});

		$(document).on('click','#list',function(e){
			e.preventDefault();

			var Dept=$('#Dept').val();
			var SubDept=$('#SubDept').val();
			var Month=$('#MONTH').val();
			var Year=$('#YEAR').val();
			var EmpCategory=$('#EmpCategory').val();
			var WorkerCategory=$('#WorkerCategory').val();
			 $("#print").show();

			$(document).on('click','#print',function(e){
			e.preventDefault();

			var Dept=$('#Dept').val();
			var SubDept=$('#SubDept').val();
			var Month=$('#MONTH').val();
			var Year=$('#YEAR').val();
			var Roll=$('#Roll').val();
			var EmpCategory=$('#EmpCategory').val();
			var WorkerCategory=$('#WorkerCategory').val();


window.open("/Hrm/MonthlyAtttendancePrint.php?Dept=" + Dept + "&SubDept=" + SubDept + "&Month=" + Month + "&Year=" + Year+"&Roll="+Roll+"&EmpCategory="+EmpCategory+"&WorkerCategory="+WorkerCategory);


    	});


			$.ajax({
		  url:'Ajax/AjMonthlyAtt',
		  type:'POST',
		  data:{'Dept':Dept,'SubDept': SubDept,'Month' : Month,'Year' : Year,'Type' : 'List','EmpCategory':EmpCategory,'WorkerCategory':WorkerCategory},
		  success:function(data){
			$("#ATT_DATA").html(data);
 					}
			})
			//return false;



    	});




    	$('#continuebtn').on('click',function(){
			alert('hi');

    		$('form').submit();
    	});
    });



</script>
<?php } ?>
