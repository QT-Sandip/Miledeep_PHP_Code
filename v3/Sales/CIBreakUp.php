<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
$PIId=$_GET['PIID'];



$SqlCIGradeData="SELECT * FROM `$ScrmCIGradeData` where InvId='$PIId' ";
$StmtCIGradeData=$iconn->query($SqlCIGradeData); 


$SqlPI="SELECT * FROM `$TblScrmPIData` where PoID='$PIId'";
$StmtPI=$iconn->query($SqlPI);
$RowPI=$StmtPI->fetch_array();

$PageTitleFolder="SalesNew";
$PageTitle=" Grade wise Data - ".$RowPI['InvNo'];


extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	
}

 ?>


<!DOCTYPE html>
<html lang="en">

    <head>
        
            <?php include ($IncludesPath."title-meta.php"); ?>
		 	<?php  include ($IncludesPath."DbLinks.php");?>
            <?php include ($IncludesPath."head-css.php");?>

    </head>

    <body data-sidebar="dark">

	
	 <?php
			include ($IncludesPath."PreLoader.php"); ?>

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
					
						 <?php
	if($Status !=''){
	//Show Alert 
	?>
	 <div class="alert alert-success alert-dismissible fade show" role="alert">
                                                <i class="mdi mdi-check-all mr-2"></i>
                                                      <?php echo $Status;
$_SESSION['Status']='';	  ?>
                                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>

<?php } ?>

                        <?php include ($IncludesPath."page-title.php");?>

	
          
	
<div class="row">
                            <div class="col-12">
                                <div class="card">
								
								<form role="form" method="post">
                                    <div class="card-body">
									
										<div class="row">

    <table id="" 
										class="table table-striped 
										table-bordered dt-responsive nowrap">
              

    <thead>

      <tr>
	  
	  
	  <th>Variety</th>
	   <th>Grade</th>
	   <th>Packing Style</th>

	  <th>Treatment</th>
	 <th>Freezing Type</th>
	  <th>Brand</th>
	  	 
	  
	  <th> Mcs</th>
	  <th> Amount</th>
	  <th> Actions</th>
	  	
      </tr>
    </thead>
    <tbody>
	<?php
	//echo $sql;
   while($row=$StmtCIGradeData->fetch_array()){
	 $PO_ID=$row['ID'];  
	 $PackingID=$row['PackingStyleID'];  
	 
	 $SqlPckStyl="SELECT * FROM `AdminPackingStyles` WHERE PCKID ='$PackingID'";
	 
	
	 $StmtPckStyl=$iconn->query($SqlPckStyl); 
     $RowPckStyl=$StmtPckStyl->fetch_array();
	 $Packing=$RowPckStyl['PackingStyle'];
	?>
	<tr>			
	
	<td><?php echo $row['Variety']; ?> </td>	
	<td><?php echo $row['Grade']; ?> </td>	
	<td><?php echo $Packing; ?> </td>	
	<td><?php echo $row['Treatment']; ?> </td>	
	<td><?php echo $row['FreezingType']; ?> </td>	
	<td><?php echo $row['Brand']; ?> </td>	
	<td > <input  type="text" class="form-control"  name="Mcs" id="Mcs<?php echo $row['ID']; ?>"
				  placeholder="Enter DAY CODE" value="<?php echo $row['Mcs']; ?>"> </td>					  

	<td><?php echo $row['Price']; ?> </td>	

	
	
	<td>
	 	<!-- <button id="<?php  echo $row['ID'];?>" onClick="update_click(this.id)" class=" glyphicon glyphicon-edit btn btn-primary">  Update</button>-->

<button class="btn btn-link text-primary Update" data-id="<?php  echo $row['ID'];?>" 
			
							data-toggle="tooltip" data-placement="top" title="" value=""
							data-original-title="Update"><i class="mdi mdi-pencil font-size-18"></i></button> 						
	
	</td>
       

        </tr>

	  <?php 

   }
	  ?>

     </tbody>

  </table>
  			  </div>
				
				
					
				
				<div class="col-xs-12 box-footer"  align="right">

   <a target="" href="DocAddReports.php?PoID=<?php echo $PIId ?>"
       class="btn btn-danger  "> Close</a>
</div>
            </form>
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

   // function update_click(clicked_id){
	   $(document).on('click', '.Update', function(e){
	  var ref_id='<?php echo $PIId?>';
	  
	  	  var clicked_id =$(this).data('id');

	  var McsID='#Mcs'+clicked_id;
	 
	  var Mcs=$(McsID+"").val();
		 
		//alert(clicked_id);
		var r = confirm("Are You Sure Do You Want To Update Mcs?");
  if (r == true) {
    
		$.ajax({
		  url:'Ajax/AjaxDelete.php',
		  type:'POST',
		  data:{
		  
		  'TYPE':'McsUpdate',
		  'D_ID':clicked_id,
		  'ref_id':ref_id,
		  'Mcs':Mcs
		  
		  
		  
		  },
		  success:function(data){
			 /* console.log(data);
			  
			 $("#PACK_SIZE").html(data);*/
			 alert("Mcs Updated Successfully");
			 //showData(data);
			}
			});	
			}else{}
			
					  
  });
</script>
<script>

function showData(data){
			
					//$("#PIList").html(data);
 
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
