<?php 

$PageTitle="Employee Mess Deductions";
$PageTitleFolder="HRM";
 $Currentyear=date('Y');

//$Month=date("n", strtotime('m')); exit;
													 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


$SqlDepartments="SELECT * FROM `HrmDepartments`";
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
                                        <h4 class="card-title">Employee - Mess Deductions</h4>
                                        

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
    	
		
		$(document).on('click','#list',function(e){			
			e.preventDefault();
			loadMessDeductionList();
			
			
    		
    
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
  	
function loadMessDeductionList(){
		
			var Dept=$('#Dept').val();
			var SubDept=$('#SubDept').val();
			var MONTH=$('#MONTH').val();
			var DATE=$('#DATE').val();
			var YEAR=$('#YEAR').val();
			
		
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






