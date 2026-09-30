<?php 
$PageTitle="Holidays List";
$PageTitleFolder="HRM";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$year = date("y");
$LastYear=$year-1;
$NextYear=$year+1;
?>

<!doctype html>
<html lang="en">
<link href="https://serp.silverlights.in/Clients/Jlsf/User.css"  rel="stylesheet" type="text/css" />

    <head>
        
         <?php include ($IncludesPath."title-meta.php"); ?>
         <?php  include ("Includes/DbLinks.php");?>
        <?php include ($IncludesPath."head-css.php");?>

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
										
										          
												
				<!--<div class="form-group col-sm-3" >
                  <label for="exampleInputEmail1">Select Year</label>
                  <select class="form-control" name="Year" id="Year">
                    <option Value="2021">2021</OPTION>
                    <option Value="2022">2022</OPTION>
                    <option Value="2023">2023</OPTION>
     
					<select> 
                </div> -->
				<div class="col-lg-3">
                                                    <div class="form-group">
                                                        
                                                        <select class="form-control select2"  name="Year" 
														id="Year">
                                                           <?php
														
														for($year; $Startyear <= $Currentyear; $Startyear++) { ?>
                                                            
                                                                 <option value="<?php echo $Startyear ?>"<?php if($Startyear==$Currentyear){ echo 'selected="selected"'; }?>>
																 <?php echo $Startyear ?></option>
                                                             
                                                            <?php  } 
                                                         
                                                            ?>
                                                        </select>
													</div>
                                                </div>
										

	<div align="center" class="col-lg-12">
 <button id="list" align="center" type="button" class="btn btn-primary waves-effect waves-light">
        Search <i class="ri-arrow-right-line align-middle ml-2"></i> 
                   </button>
													
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
                                    <div class="card-body" id="HolidaysList">
                                        <h4 class="card-title">Holidays - List</h4>
                                        

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
                
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        

		<?php include ($IncludesPath."vendor-scripts.php");?>
      <?php include ("Includes/NewScripts.php");?>


        <!-- Required datatable js -->

    </body>
</html>

<script>
		

$(document).on('click','#list',function(e){			
			e.preventDefault();
			
			var Year=$('#Year').val();

			$.ajax({
			url:'Ajax/AjHolidaysList.php',
		  type:'POST',
		  data:{'Year':Year },
		  success:function(data){
			  showData(data);

		  }
 
})
			//return false;
});				
		
		
		


function showData(data){
			
					$("#HolidaysList").html(data);
 
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

</script>
