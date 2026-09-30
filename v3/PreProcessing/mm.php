<?php

$PageTitle="Value Addition(Melting Data)";
$PageTitleFolder="Value Addition";
$BranchID=$_SESSION['BranchID']; 
$AccessType=$_SESSION['AccessType']; 


include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$LotId=$_GET['ID'];

//$SqlValueAddition="SELECT * FROM `PpcValueAddition` where LotID='$LotId' and VARIETY !='DeHeading' ORDER BY TIME_STAMP DESC";
$SqlValueAddition="SELECT * FROM `PpcValueAddition` as PVA 
left join PpcOptions as PO on PO.OptID=PVA.VarietyID 
where LotID='$LotId'";
$StmtValueAddition=$iconn->query($SqlValueAddition);

extract($_POST);
extract($_GET);



if(isset($_POST['submit'])){
	
}

			
?>
<!DOCTYPE html>

<html lang="en">
<link href="<?php echo  $href ?>"  rel="stylesheet" type="text/css" />

    <head>
        
                <?php include ($IncludesPath."title-meta.php"); ?>
		 		<?php include ($IncludesPath."DbLinks.php");?>
                <?php include ($IncludesPath."head-css.php");?>

    </head>

    <body data-sidebar="dark" class="sidebar-enable vertical-collpsed">
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
                                    <div class="card-body" id="ShowDataTable">
								<div class="col-lg-12 box-footer"  align="center">
			  	
				
					</div>		
										
			
<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">
			  <tr>
                
      <th  >S.No</th>
	  <th >Station</th>
	  <th >Processing Date</th>
	  <th >Variety</th>
	  <th >Sent HL/HO Wt(Kgs)</th>
	  <th >Received HL/HO Qty(Kgs) </th>
	  <th >HL/HO Count</th>
	  <th >Count After VA</th>
	  <th >Weight After VA</th>
	  <th >Returned Weight(if any)</th>
	  <th >Yield(%)</th>
	  <th >Remarks</th>
	  <th >Actions</th>
                  
               
				</tr>
				</thead>
                <tbody>
                  <?php
				  
				  $SNo=1;
                   
                   while($RowValueAddition=$StmtValueAddition->fetch_array()){

$StationID=$RowValueAddition['StationID'];

$SqlShedname="SELECT * FROM `PpcVAStations` where StationID='$StationID'";
$StmtShedname=$iconn->query($SqlShedname);
$RowShedname=$StmtShedname->fetch_array();
   
   $Yeild=ROUND(($RowValueAddition['WtAftrVA']/$RowValueAddition['SentWeight'])*100);
	$ProcessingDate = date("d-m-Y", strtotime($RowValueAddition['ProcessingDate']));
	?>
					   <tr>
                       
						<td  > <?php echo $SNo; ?>    </td>
	<td> <?php echo $RowShedname['UnitName']; ?> </td>
	<td> <?php echo $ProcessingDate; ?> </td>
	<td> <?php echo $RowValueAddition['Name']; ?> </td>
	<td> <?php echo $RowValueAddition['SentWeight']; ?> </td>
	<td> <?php echo $RowValueAddition['ReceivedWeight']; ?> </td>
	<td> <?php echo  $RowValueAddition['SentCount'];?> </td>
	<td> <?php echo $RowValueAddition['CountAftrVA'];  ?> </td>
	<td> <?php echo  $RowValueAddition['WtAftrVA']; ?> </td>
	<td> <?php echo  $RowValueAddition['ReturnWeight']; ?> </td>
	<td> <?php echo  $Yeild; ?> </td>
	<td> <?php echo  $RowValueAddition['Remarks']; ?> </td>
	<TD>
	
	<button class="btn btn-link text-danger Delete" 
		data-id="<?php  echo $RowValueAddition['VLID'];?>" 
			
							data-toggle="tooltip" data-placement="top" title="Delete" value=""
							data-original-title="Delete"><i class="mdi mdi-trash-can font-size-16">
							</i></button>
	</TD>
	
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
      		<?php include ($IncludesPath."ShowDataTable.php");?>


       
    </body>
</html>				

<script>
$('document').ready(function(){
		showData();
    	
    });
 
    $(document).on('click', '.Delete', function(e){
	  var VAID =$(this).data('id');


   		var r = confirm("Are you sure do you want to delete?");
  if (r == true) { 
		 //alert(clicked_id);		  
		$.ajax({
		  url:'Ajax/AjDltAl.php',
		  type:'POST',
		  data:{'TYPE':'VA_REPORT','D_ID':VAID},
		  success:function(data){
			  console.log(data);
			  alert("Deleted");
			  		location.reload();

			
			}
			});	

} else {

  }			
  });
  



/*function showData(data){
			
			$("#ClientList").html(data);	

		var Stable=	$("#key-datatable").DataTable({
		order: [[0, 'desc']],
	   keys:!0,
			language:{
				paginate:{
					previous:"<i class='mdi mdi-chevron-left'>",
					next:"<i class='mdi mdi-chevron-right'>"
					}
					},
					drawCallback:function(){
						$(".dataTables_paginate > .pagination").addClass("pagination-rounded")}

				});			

}*/
</script>
