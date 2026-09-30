<?php 

$PageTitle="Employee Leaves";
$PageTitleFolder="HRM";
 $Currentyear=date('Y');


include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


$SqlDepartments="SELECT DeptID,DepartmentName FROM `HrmDepartments`";
$StmtDepartments=$iconn->query($SqlDepartments);


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
				  

				  
		 <div align="center" class="col-lg-12" >
		 								  <div class="form-group " >

		 <br>
		  <!--<label for="exampleInputEmail1"> </label>-->
                 
		         <button id="list" align="center" type="button" class="btn btn-primary waves-effect waves-light">
				Search <i class= 'ri-arrow-right-line align-middle ml-2'>	 </i> </button>
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
                                        <table id="mytable" class="table table-bordered" style="width:100%">
                                            <thead>
                                                <tr>                        
                                                      <th>Bio Id </th>
                                                      <th>Name</th>
                                                      <th>Department</th>
                                                      <th>CLs</th>
									                  <th>SLs</th>
													  <th>Total Leaves</th>
													  <th style="width: 20%">Tools</th>
                                                </tr>
                                            </thead>
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
        <?php include ("Includes/NewScripts.php");?>
        <Script>
		
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
						return false;
					}
				});

    		});

    	$('#continuebtn').on('click',function(){
    		$('form').submit();
    	});
    

	$(function(){

		$(document).on("click", ".edit", function(e){
			e.preventDefault();

			$('#addnew').modal('show');
			var id = $(this).data('id');
			$("#leave_type option[value='casualleave']").remove();

			$('#leave_type option').each(function(){
				if (this.value == 'casualleave') {

				}else {

				}
			});

  		});


		  $('.delete').click(function(e){
		    e.preventDefault();
		    $('#delete').modal('show');
		    var id = (this).data('id');
		    getRow(id);
		  });
	});

	$('document').ready(function(){
		var doa_doc='<?php echo $DOA_DOC ?>';
    	$("#DOA_DOC").val(doa_doc);

		$(document).on('click','#list',function(e){
			e.preventDefault();
			loadManualAtt();
		});
	});

    function loadManualAtt() {  
        
        if ($.fn.DataTable.isDataTable('#mytable')) {
            $('#mytable').DataTable().destroy();
        }


        table = "";              
        var Dept=$('#Dept').val();
		var SubDept=$('#SubDept').val();
		var MONTH=$('#MONTH').val();
		var DATE=$('#DATE').val();
		var YEAR=$('#YEAR').val();
                 
        table = $('#mytable').DataTable({
            "lengthMenu": [[20,50,100], [20,50,100]],
            scrollX: true,
            scrollY: '400px',
            scrollCollapse: true,
            paging: true,
            processing: true,
            serverSide: true,
            ajax: {
                url: 'Ajax/AjEmpLeaveList.php',
                type: 'POST',
                data:{'DeptID':Dept,'SubDeptID': SubDept,'Month':MONTH,'Year': YEAR}
            },
            columns: [
                { data: 'BioMetricID' },
                { data: 'EmpName' },
                { data: 'DepartmentName' },
                { data: 'CasualLeave' },
                { data: 'SickLeave' },
                { data: 'TotalLeaves' },
                { data: 'Tools' }                        
            ],
            columnDefs: [
                { targets: [2], orderable: false, searchable: false },
                { targets: [3], orderable: false, searchable: false },
                { targets: [4], orderable: false, searchable: false },                

            ],
            drawCallback: function () {
                // // Initialize editable fields
                // $('.editable').editable();
            }
        });
    }


	</script>
    </body>
</html>				


<?php if(0){ ?>
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
$(function(){
   
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
});

</script>



<Script>

$('document').ready(function(){
	var doa_doc='<?php echo $DOA_DOC?>';
	
    $("#DOA_DOC").val(doa_doc);
    	
		
		$(document).on('click','#list',function(e){			
			e.preventDefault();
			
			var Dept=$('#Dept').val();
			var SubDept=$('#SubDept').val();
			var MONTH=$('#MONTH').val();
			var DATE=$('#DATE').val();
			var YEAR=$('#YEAR').val();
			
		
			$.ajax({
		  url:'Ajax/AjEmpLeaveList.php',
		  type:'POST',
		  data:{'DeptID':Dept,'SubDeptID': SubDept,'Month':MONTH,'Year': YEAR},
		  success:function(data){
			 //  console.log(data);
 $("#ATT_DATA").html(data);
 //$('#example1').DataTable()
 
 
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
			})
			//return false;
			
    		
    
    	});		
		
		

    
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
  	

</script>
<?php } ?>