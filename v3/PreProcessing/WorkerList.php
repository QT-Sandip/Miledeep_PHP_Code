<?php

$PageTitle="Workers List";
$PageTitleFolder="PreProcessing";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

  $SqlContractor="SELECT * FROM `PpcWorkersMaster`";
$StmtContractor=$iconn->query($SqlContractor);

?>
<!DOCTYPE html>

<html lang="en">
<link href="https://serp.silverlights.in/Clients/JME/User.css"  rel="stylesheet" type="text/css" />

    <head>
        
         <?php include ($IncludesPath."title-meta.php"); ?>
		 		<?php include ($IncludesPath."DbLinks.php");?>
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
                                    <div class="card-body" id="WorkersList">
									
															<div class="col-lg-12 box-footer"  align="center">

									<a href="CreateWorkers.php" class="btn btn-success waves-effect waves-light">
				<i class="fa fa-plus"></i> New Worker </a>
				
								</div>		
					
			
<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">
      
			  <tr>
                
                 
                  <th>S.No</th>
				  <th>Worker Name</th>
				  <th>Address  </th>
				  
                  <th>Wages</th>
				  <th>Auto Charge  </th>
				  <th>Extra</th>
				  
                  <th style="width: 10%">Tools</th>
                  
               
				</tr>
				</thead>
                <tbody>
                  <?php
				  
				  $SNo=1;
                   
                    while($RowContractor=$StmtContractor->fetch_array()){?>
                       
					   <tr>
                       
						  <td><?php echo $SNo;?></td>
						  <td><?php echo  $RowContractor['WorkerName'];?></td>
						  <td><?php echo  $RowContractor['Address'];?></td>
						  <td><?php echo $RowContractor['Wage'];?></td>

						  <td><?php echo  $RowContractor['AutoCharge'];?></td>
						  <td><?php echo  $RowContractor['Extra'];?></td>
						   
                         
                          <td>
						  
						
						  
                         
						 <a target="_blank" href="CreateWorkers.php?ID=<?php  echo $RowContractor['WorkerID'];?>" 
						 class="mr-3 text-primary Edit" data-toggle="tooltip" data-placement="top"
						 title="" data-original-title="Edit"><i class="mdi mdi-pencil font-size-18"></i></a>

                         
              
		  
		  
		 <!-- <button class="btn btn-link text-danger Delete" data-id="<?php echo $RowContractor['WorkerID']; ?>" 
			
							data-toggle="tooltip" data-placement="top" title="" value=""
							data-original-title="Delete"><i class="mdi mdi-trash-can font-size-18"></i></button> 						
			-->					 
	
					   </td>
                        </tr>
                      <?php
                $SNo++;    }
                  ?>
                </tbody>
              </table>
            	
									</div>
        
                                         <!-- end col -->
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

        

		<?php include ($IncludesPath."vendor-scripts.php");?>
		<?php include ($IncludesPath."NewScripts.php");?>
      

       
    </body>
</html>				

<script>
$(document).ready(function(){
	  // showData();

	$(document).on('click', '.ClientActive', function(e){
e.preventDefault();
	//alert('hiiiii');
   
     var ClientId =$(this).data('id');
	 var ClientStatus = $(this).val();
	 var Type = 'ClientStatus';
	 
	 
	$.ajax({
	
	type: 'POST',
    url: 'Ajax/AjaxClientsDelete.php',
    data: {'ClientId': ClientId,'ClientStatus':ClientStatus, 'Type' : Type},
    
    success: function(data){
		
		 /*showData(data);
			location.reload();*/
			
			
			var obj = JSON.parse(JSON.stringify(data));
		 
		 
    
		 if(obj.error == null){
			 alert("Status Updated Successfully");
			location.reload();
		 }else{
			 alert(obj.error);
		 }
			 

     }
  });
 
    //getRow(id,type);
  });
  
	
	
  $(document).on('click', '.Delete', function(e){
e.preventDefault();
	//alert('hiiiii');
	
	 var ClientId =$(this).data('id');
	 var ClientStatus = $(this).val();
	 var Type = 'DeleteClient';
	  var r = confirm("Are You Sure Do You Want To Delete?");
  if (r == true) {
  
	
	$.ajax({
	
	type: 'POST',
     url: 'Ajax/AjaxClientsDelete.php',
    data: {'ClientId': ClientId,'ClientStatus':ClientStatus, 'Type' : Type},
   
    success: function(data){
		/*showData(data);
        location.reload();*/
		var obj = JSON.parse(JSON.stringify(data));
		 
		 
    
		 if(obj.error == null){
			 alert("Client Deleted Successfully");
			location.reload();
		 }else{
			 alert(obj.error);
		 }
			 

     

     }
  });
 }else{}
  });
    
	});


function showData(data){
			
					$("#WorkersList").html(data);
 
 		var table = $('#key-datatable').DataTable( {
        scrollY: "600px",
    scrollX: true,
    paging: false,
    destroy: true,
    scrollCollapse: true,
    info: false,
    ordering: false,
    autoWidth: false,
    fixedColumns: true, // just to be explicit
            dom: 'Blfrtip',
            buttons:[],
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
