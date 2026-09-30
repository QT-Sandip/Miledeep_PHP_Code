<?php

$PageTitle="Lots List";
$PageTitleFolder="Purchase";

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$LotID=$_GET["LotID"];

  
  $SqlTrace = "SELECT `RmLotID`,`LotID`,LotStatusID, `RmReferenceID`, `PostedUserID`, `LotNumber`, RMD.PurchaseStationID ,
ArrivedWeight,ArrivedCount,ArrivedRate,ArrivalDate,RLSD.LotStatus,
`PurchaseCategoryID`,`CreatedDateTime`, `Year`, RMD.LotStatus,
(SELECT UserName from  RmUsersList  as RUL where RUL.ID=RMD.AgentID) as AgentName,
(SELECT UserName from  RmUsersList  as RUL where RUL.ID=RMD.GraderID) as GraderName,
(SELECT PurchaseType from  RmPurchaseCategory  as RUL where RUL.CatID=RMD.PurchaseCategoryID) as PurchaseType
FROM `RmLotStatusDetails` as  RLSD
left join RmLotDetatils  as RMD on RLSD.LotID=RMD.RmLotID 
where RMD.LotStatus='1' and LotID='$LotID'  
ORDER BY RMD.PurchaseDate DESC, RMD.LotNumber DESC LIMIT 100";
  $StmtTrace = $iconn->query($SqlTrace);
  
  //echo $SqlTrace;

?>
<!DOCTYPE html>

<html lang="en">
<link href="<?php echo  $href ?>"  rel="stylesheet" type="text/css" />
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
                                    <div class="card-body" id="TraceabilityList">
									        <div class="card-body" id="ShowDataTable">
											
								
			
<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >										
		
<thead class="thead-light">
			  <tr>
                
                 
               <th>ID</th>
                  <th>LOT</th>
				  <th>Arrival Date</th>
				   <th>Type</th>
				  <th>Grader</th>
				  <th>Agent</th>
				  <th>Count</th>
				  <th>Weight</th>
				  <th>Rate</th>
				  <th>Actions</th>
                  
               
				</tr>
				</thead>
                <tbody>
                  <?php
				  
				  $SNo=1;
                   
                    while($RowRmLotDetatils = $StmtTrace->fetch_assoc()){
						
						
						
					
						?>
                     
					   <tr>
                       
				<td><?php echo $RowRmLotDetatils['RmLotID']; ?></td>
                <td><B><?php echo $RowRmLotDetatils['LotNumber']; ?></B></td>
                <td><?php echo date('d-m-Y',strtotime($RowRmLotDetatils['ArrivalDate'])); ?></td>			
                <td><?php echo $RowRmLotDetatils['PurchaseType']; ?></td>  
                <td><?php echo $RowRmLotDetatils['GraderName']; ?></td>
                <td><?php echo $RowRmLotDetatils['AgentName']; ?></td>
                <td><?php echo $RowRmLotDetatils['ArrivedCount']; ?></td>
                <td><?php echo $RowRmLotDetatils['ArrivedWeight']; ?></td>
                <td><?php echo $RowRmLotDetatils['ArrivedRate']; ?></td>
						
                          <td>
						  
						
						  
                         
						 <a target="_blank" href="EditLot.php?ID=<?php  echo $RowRmLotDetatils['LotStatusID'];?>" 
						 class="mr-3 text-primary Edit" data-toggle="tooltip" data-placement="top"
						 title="" data-original-title="Edit"><i class="mdi mdi-pencil font-size-18"></i></a>

                         
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
		<?php include ($IncludesPath."ShowDataTable.php");?>
      

       
    </body>
</html>				

<script>
$(document).ready(function(){
	   showData();

	});



</script>
