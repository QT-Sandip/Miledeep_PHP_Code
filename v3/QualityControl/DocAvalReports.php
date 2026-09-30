<?php
//ini_set( 'display_errors', 1 );
$PageTitle="Available Reports List";
$PageTitleFolder="Quality Control";
$AccessType=$_SESSION['AccessType']; 

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
$TblQcAvalReports="QcAvalReportsV1";

$InvID=$_GET['PoID'];

$SqlInv="SELECT *,(SELECT COUNT(*) from $TblQcPoVarityList WHERE InvID='$InvID') as Vlist,
(SELECT COUNT(*) from $TblQcCodeList WHERE InvID='$InvID') as Clist
FROM `$TblScrmPoData` WHERE PoID='$InvID'"; 
$stmt_inv=$iconn->query($SqlInv);
$row_inv=$stmt_inv->fetch_array();
$ReportCat=$row_inv['TypeCountry'];
$PoNo=$row_inv['PoNo'];



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
                                    <div class="card-body" id="ClientList">
									
								<div class="col-lg-12 box-footer"  align="center">
			  	
							
				
					</div>			
			
 <table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">
			  <tr>
                
                 
      <th>Available Reports</th>
		<th>Actions</th>
               
				</tr>
				</thead>
                <tbody>
                  <?php
				  
  $SqlAvalRep="SELECT * FROM `$TblQcAvalReports` where ReportCat='$ReportCat' and OptionType !=1 Order by ReportName asc"; 
$StmtAval=$iconn->query($SqlAvalRep);  

   while($RowAvalRep=$StmtAval->fetch_array()){
	   while($RowAvalRep=$StmtAval->fetch_array()){
     $TableName=$RowAvalRep['TableName'];
     $QueryID=$RowAvalRep['QueryID'];
     $PoID=$RowAvalRep['PoID'];
     $ReportName=$RowAvalRep['ReportName'];
     $DisplayId=$RowAvalRep['DisplayParam'];
    
	  $SqlReport="select * from $TableName where $PoID='$InvID'"; 
     $StmtReports=$iconn->query($SqlReport); 
    while($RowRep=$StmtReports->fetch_array()){
		
		$QueryParamVal=$RowRep["$QueryID"];
		if($DisplayId!=''){
		 $DisplayName=$RowRep["$DisplayId"];
		}else{
		$DisplayName='';	
		}
	
		$RowAval['Count']=1;
		
		if($DisplayName!=''){
			$DisplayName=' ('.$DisplayName.')';
		}	
		 if($RowAval['Count'] > 0 ){ 
		 ?>
 
     <tr>
  		<td><?php echo $ReportName.$DisplayName ?></td>
  		<td >
  	 	<!--<a href="<?php echo $RowAvalRep['EditLink']. $QueryParamVal.'&PoID='.$InvID ?>"
         class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
	title="" data-original-title="Edit"><i class="mdi mdi-pencil font-size-18"></i> </a>-->
		 
		<a href="<?php echo $RowAvalRep['PrintLink']. $QueryParamVal.'&PoID='.$InvID ?>"
         class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
	title="" data-original-title="Print"><i class="mdi mdi-eye font-size-18"></i> </a>
		 
		 
		 
  	
  	  </td>
         </tr>
		 <?php }	 	
     }
   }} ?> 
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
	   showData();

	
    
	});


function showData(data){
			
					$("#ClientList").html(data);
 
 		
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
