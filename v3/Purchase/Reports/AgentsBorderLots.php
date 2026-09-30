<?php

$Status=$_SESSION['Status']; 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/UrlEncode.php");

$PageTitle="Border Count Lots";

$PageTitleFolder="Admin";
 
 $QueryParms=$_GET['Q']; 
  
  if($QueryParms!=''){
  $QueryParms=urlDecode64($QueryParms);  
  $queries = array();
  parse_str($QueryParms, $_GET);
  }
  $Type=$_GET['Type'];

  $Month=$_GET['Month']; 
  $Year=$_GET['Year'];
  $Agent=$_GET['Agent'];

if($Type==1){
			$Query= "month(PurchaseDate)='$Month'  and year(PurchaseDate)='$Year'  $Query";
		 }else {
			$Query= "PurchaseDate between '$Month'  and '$Year'  $Query"; 
		 }

 $SqlBorderCountLots="SELECT * FROM `RmLotDetatils`  WHERE AgentID='$Agent'  
and 
( PurchaseCount >= 30 and PurchaseCount < 32.25
OR PurchaseCount >= 32.25 and PurchaseCount < 35.3 
OR PurchaseCount >= 40 and PurchaseCount < 42.25
OR PurchaseCount >= 42.26 and PurchaseCount < 45.25
OR PurchaseCount >= 50 and PurchaseCount < 52.25
OR PurchaseCount >= 60 and PurchaseCount < 62.25
OR PurchaseCount >= 70 and PurchaseCount < 72.25
OR PurchaseCount >= 80 and PurchaseCount < 82.25
OR PurchaseCount >= 90 and PurchaseCount < 92.25
OR PurchaseCount >= 100 and PurchaseCount < 102.25
 )
and $Query ORDER BY PurchaseCount asc"; 

$StmtlBorderCountLots=$iconn->query($SqlBorderCountLots);

 ?>

<!DOCTYPE html>

<html lang="en">
<link href="<?php echo  $href ?>"  rel="stylesheet" type="text/css" />
<link href="https://serp.silverlights.in/Clients/JME/User.css"  rel="stylesheet" type="text/css" />

    <head>
        
            <?php include ($IncludesPath."title-meta.php"); ?>
		 	<?php  include ($IncludesPath."DbLinks.php");?>
            <?php include ($IncludesPath."head-css.php");?>

    </head>

    <body data-sidebar="dark">

	
	 <?php		include ($IncludesPath."PreLoader.php"); ?>

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
<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">
      
      <tr>
	  <th>S.No</th> 
      <th>Agent Name</th> 
      <th>RM Date</th> 
      <th>Lot Number</th> 
      <th>Grader</th> 
      <th>Supervisor</th> 
      <th>Count</th> 
      <th>Head ON Qty</th> 
      <th>Vehicle</th> 
      <th>Farm Location</th> 
	  
	  </tr>

    </thead>
<tbody>



	<?php
	
	$SNo=1;
 while($RowlBorderCountLots=$StmtlBorderCountLots->fetch_array()) { 
 
 $GraderID=$RowlBorderCountLots['GraderID'];
 $SupevisorID=$RowlBorderCountLots['SupevisorID'];
 $AgentID=$RowlBorderCountLots['AgentID'];
 
 $SqlGraderName="SELECT *,
 (SELECT UserName FROM RmUsersList WHERE ID='$SupevisorID') AS Supevisor,
 (SELECT UserName FROM RmUsersList WHERE ID='$AgentID') AS Agent
 FROM RmUsersList WHERE ID='$GraderID'"; 
 
 $StmtGraderName=$iconn->query($SqlGraderName);
 $RowGraderName=$StmtGraderName->fetch_array();
 
 $GraderName=$RowGraderName['UserName'];
 $Supevisor=$RowGraderName['Supevisor'];
 $Agent=$RowGraderName['Agent'];
 
 $PurchaseDate = date("d-m-Y", strtotime($RowlBorderCountLots['PurchaseDate']));
 
 ?>


<tr>
		<td id="a_type<?php  echo $RowlBorderCountLots['ID'];?>"><?php echo $SNo; ?></td>
		<td id="a_type<?php  echo $RowlBorderCountLots['ID'];?>"><?php echo $Agent; ?></td>
		<td id="a_type<?php  echo $RowlBorderCountLots['ID'];?>"><?php echo $PurchaseDate; ?></td>
		<td id="a_type<?php  echo $RowlBorderCountLots['ID'];?>"><?php echo $RowlBorderCountLots['LotNumber']; ?></td>
		<td id="a_type<?php  echo $RowlBorderCountLots['ID'];?>"><?php echo $GraderName; ?></td>
		<td id="a_type<?php  echo $RowlBorderCountLots['ID'];?>"><?php echo $Supevisor; ?></td>
		<td id="a_type<?php  echo $RowlBorderCountLots['ID'];?>"><?php echo $RowlBorderCountLots['PurchaseCount']; ?></td>
		<td id="a_type<?php  echo $RowlBorderCountLots['ID'];?>"><?php echo $RowlBorderCountLots['PurchaseWeight']; ?></td>
		<td id="a_type<?php  echo $RowlBorderCountLots['ID'];?>"><?php echo $RowlBorderCountLots['VehicalID']; ?></td>
		<td id="a_type<?php  echo $RowlBorderCountLots['ID'];?>"><?php echo $RowlBorderCountLots['FarmAddress']; ?></td>


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
 /* $(function () {
    $('#example1').DataTable()
    $('#example2').DataTable({
      'paging'      : true,
      'lengthChange': false,
      'searching'   : false,
      'ordering'    : true,
      'info'        : true,
      'autoWidth'   : false
    })
  })*/
  
  function showData(data){
			
					$("#EmpList").html(data);
 
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



<script>
$(document ).ready(function() {
   showData();
});
 
  
  function showData(data){
			
					$("#EmpList").html(data);
 
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

</html>
