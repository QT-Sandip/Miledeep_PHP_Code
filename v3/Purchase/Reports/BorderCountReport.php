<?php
$Status=$_SESSION['Status']; 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/Globals.php");
include ($_SERVER['DOCUMENT_ROOT']."/includes/UrlEncode.php");
  
 
  $QueryParms=$_GET['Q']; 
  
  if($QueryParms!=''){
  $QueryParms=urlDecode64($QueryParms);  
  $queries = array();
  parse_str($QueryParms, $_GET);
  }
  $Type=$_GET['Type'];

  $Month=$_GET['Month']; 
  $Year=$_GET['Year']; 
  $PsID=$_GET['PsID']; 
  
  if($PsID!=''){
	  $PsID=$_GET['PsID'];	  
	  $Query="and PurchaseStationID='$PsID'";
  }else{
	   $Query="";
  }


if($Type==1){
			$Query= "month(PurchaseDate)='$Month'  and year(PurchaseDate)='$Year'  $Query";
		 }else {
			$Query= "PurchaseDate between '$Month'  and '$Year'  $Query"; 
		 }

  

 $sql="SELECT DISTINCT(AgentID) AS AGENT,
(SELECT COUNT(*) FROM RmLotDetatils as C1 WHERE C1.AgentID=imd.AgentID and $Query ) as TOTAL ,
(SELECT COUNT(*) FROM RmLotDetatils as C1 WHERE C1.AgentID=imd.AgentID and C1.PurchaseCount >= 30 and C1.PurchaseCount < 32.25 and $Query) as CONE,
(SELECT COUNT(*) FROM RmLotDetatils as C1 WHERE C1.AgentID=imd.AgentID and C1.PurchaseCount >= 32.26 and C1.PurchaseCount < 35.25 and $Query) as CNINE,
 
(SELECT COUNT(*) FROM RmLotDetatils as C2 WHERE C2.AgentID=imd.AgentID and C2.PurchaseCount >= 40 and C2.PurchaseCount < 42.25 and $Query) as CTWO,
(SELECT COUNT(*) FROM RmLotDetatils as C10 WHERE C10.AgentID=imd.AgentID and C10.PurchaseCount >= 42.26 and C10.PurchaseCount < 45.25 and $Query) as CTEN,
(SELECT COUNT(*) FROM RmLotDetatils as C3 WHERE C3.AgentID=imd.AgentID and C3.PurchaseCount >= 50 and C3.PurchaseCount < 52.25 and $Query) as CTHREE,
(SELECT COUNT(*) FROM RmLotDetatils as C4 WHERE C4.AgentID=imd.AgentID and C4.PurchaseCount >= 60 and C4.PurchaseCount < 62.25and $Query) as CFOUR,
(SELECT COUNT(*) FROM RmLotDetatils as C5 WHERE C5.AgentID=imd.AgentID and C5.PurchaseCount >= 70 and C5.PurchaseCount < 72.25 and $Query) as CFIVE,
(SELECT COUNT(*) FROM RmLotDetatils as C6 WHERE C6.AgentID=imd.AgentID and C6.PurchaseCount >= 80 and C6.PurchaseCount < 82.25 and $Query) as CSIX,
(SELECT COUNT(*) FROM RmLotDetatils as C7 WHERE C7.AgentID=imd.AgentID and C7.PurchaseCount >= 90 and C7.PurchaseCount < 92.25 and $Query) as CSEVEN,
(SELECT COUNT(*) FROM RmLotDetatils as C8 WHERE C8.AgentID=imd.AgentID and C8.PurchaseCount >= 100 and C8.PurchaseCount < 102.25 and $Query) as CEIGHT
FROM `RmLotDetatils` as imd WHERE $Query ORDER BY imd.AgentID asc" ;

$stmt=$iconn->query($sql);


$PageTitle="Border count Lot List";

$PageTitleFolder="Purchase";


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

  
  <table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">
      

      <tr>
	                            <td><B>S No</B></td>
								<td><B>Agent Name</B></td>
								<td><B>Supplied Lots</B></td>
								<td><B>30 - 32 C</B></td>
								<td><B>33 - 35 C</B></td>								
								<td><B>40 - 42 C</B></td>
								<td><B>43 - 45 C</B></td>
								<td><B>50 - 52 C</B></td>
								<td><B>60 - 62 C</B></td>
								<td><B>70 - 72 C</B></td>
								<td><B>80 - 82 C</B></td>
								<td><B>90 - 92 C</B></td>
								<td><B>100 - 102 C</B></td>
								<td><B>Border Lots</B></td>
								<td><B>Percentage (%)</B></td>
								<td><B>Options</B></td> 
	  </tr>

    </thead>
<tbody>



	<?php
	
	$SNo=1;
 while($row=$stmt->fetch_array()) { 

$SNO++;
	$AGENT=$row['AGENT'] ;
	$SqlAgentName="SELECT * FROM `RmUsersList` WHERE ID='$AGENT'";
	$StmtAgentName=$iconn->query($SqlAgentName);
    $RowAgentName=$StmtAgentName->fetch_array();
	
	$AgentName=$RowAgentName['UserName'];
	
	$DropCount=$row['CONE']+$row['CTWO']+$row['CTHREE']+$row['CFOUR']+$row['CFIVE']
	+$row['CSIX']+$row['CSEVEN']+$row['CEIGHT']+$row['CNINE']+$row['CTEN'];
	$DropCounts=round($DropCount/$row['TOTAL'],2)*100;
	
	$QueryParms='Agent='.$AGENT.'&Month='.$Month .'&Year='.$Year .'&Type='.$Type;
	$QueryParms=urlEncode64($QueryParms);

 

	?>
	
	<td  align="CENTER" ><B><?php echo $SNO ?></B></td>	
  <td  align="LEFT" ><?php echo $AgentName ?></td>
  <td  align="CENTER" ><B><?php echo $row['TOTAL']?></B></td>
  <td  align="CENTER" > <?php echo $row['CONE']?></td>
  <td  align="CENTER" > <?php echo $row['CNINE']?></td> 
  <td  align="CENTER"  > <?php echo $row['CTWO']?></td>
  <td  align="CENTER"  > <?php echo $row['CTEN']?></td>
  <td  align="CENTER"  ><?php echo$row['CTHREE']?></td>
  <td  align="CENTER"  ><?php echo$row['CFOUR']?></td>
  <td  align="CENTER"  ><?php echo$row['CFIVE']?></td>
  <td  align="CENTER"  ><?php echo$row['CSIX']?></td>
  <td  align="RIGHT"  ><?php echo$row['CSEVEN']?></td>
  <td  align="RIGHT" ><?php echo$row['CEIGHT']?></td>
  <td  align="RIGHT" ><b><?php echo$DropCount?></b>	</td>
  <td  align="RIGHT" ><?php echo$DropCounts?> % </td>
<td>

<a  target='_blank' href="<?php echo $_SESSION["SideBarPath"];?>PreProcessing/Reports/GradingReportPrint.php?Q=<?php echo $QueryParms?>" class="btn btn-primary">view</a>
<!-- <a  target='_blank' href="AgentsBorderLots.php?Q=<?php //echo$QueryParms?>" class="btn btn-primary">view</a> -->
</td>

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

$('document').ready(function(){
	
	showData();
    

    }); 
	/*  

function showData(data){
			
			$("#PIList").html(data);	
$('#key-datatable').dataTable({
        "bStateSave": true,
		order: [[1, 'desc']],
		keys:!0,
			language:{ 
				paginate:{
					previous:"<i class='mdi mdi-chevron-left'>",
					next:"<i class='mdi mdi-chevron-right'>"
					}
					},
					drawCallback:function(){
						$(".dataTables_paginate > .pagination").addClass("pagination-rounded")},
        "fnStateSave": function (oSettings, oData) {
            localStorage.setItem('key-datatable', JSON.stringify(oData));
        },
        "fnStateLoad": function (oSettings) {
            return JSON.parse(localStorage.getItem('key-datatable'));
        }
    });		
 
}*/
</script>
