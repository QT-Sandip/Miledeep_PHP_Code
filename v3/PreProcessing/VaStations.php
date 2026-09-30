<?php

$Status=$_SESSION['Status']; 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");


$User="Va Stations";

$PageTitle=$User ." List";

$PageTitleFolder="Preprocessing";

$SqlRmUsersList="SELECT * FROM `PpcVAStations` ";

$StmtRmUsersList=$iconn->query($SqlRmUsersList);

 ?>

<!DOCTYPE html>

<html lang="en">
<link href="<?php echo  $href ?>"  rel="stylesheet" type="text/css" />
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
						<div class="card-body" id="ShowDataTable">
							
							<div class="col-lg-12 box-footer"  align="center">
					<a target="_blank" href="CreateDetails.php?Type=<?php echo $UserType; ?>"class="btn btn-success waves-effect waves-light"><i class="fa fa-plus"></i> Add </a></div>
					
 <table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >	
 <thead class="thead-light">
 
      <tr>
	  <th>S.No</th> 
      <th>Name</th> 
      <th>Location</th> 
      <th>Sunday Extra %</th> 
      <th>Sunday Extra /Person</th> 
     
	  
	  </tr>

    </thead>
<tbody>



	<?php
	
	$SNo=1;
 while($RowRmUsersList=$StmtRmUsersList->fetch_array()) { ?>


		<td id="a_type<?php  echo $RowRmUsersList['StationID'];?>"><?php echo $SNo; ?></td>
		<td id="a_type<?php  echo $RowRmUsersList['StationID'];?>"><?php echo $RowRmUsersList['UnitName']; ?></td>
		<td id="a_type<?php  echo $RowRmUsersList['StationIDID'];?>"><?php echo $RowRmUsersList['UnitLocation']; ?></td>
		<td id="a_type<?php  echo $RowRmUsersList['StationID'];?>"><?php echo $RowRmUsersList['ExtraPercPerKgs']; ?></td>
		<td id="a_type<?php  echo $RowRmUsersList['StationID'];?>"><?php echo $RowRmUsersList['ExtraAmountPerPerson']; ?></td>


        </tr>

	  <?php
    $SNo++;
   }

	  ?>
</tbody>

  </table>
  </div> <!-- end row -->
    
	</div> <!-- end card body-->
                                </div> <!-- end card -->
                            </div><!-- end col-->
                        </div>
                        <!-- end row-->
                        
                    </div> 
					<?php include ($IncludesPath."footer.php");?>
                
            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        

		<?php include ($IncludesPath."vendor-scripts.php");?>
		<?php include ($IncludesPath."NewScripts.php");?>
		<?php include ($IncludesPath."ShowDataTable.php");?>
  
       
    </body>
</html>				


<script>
$(document ).ready(function() {
   showData();
});
 
 
</script>

</html>
