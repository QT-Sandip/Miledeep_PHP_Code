<?php

$PageTitle="Banks List";
$PageTitleFolder="Sales";
$AccessType=$_SESSION['AccessType']; 


include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$SqlBank="SELECT * FROM `ScrmBankDetailsMaster` as SBM 
left join AdminExporterDetails as AED on AED.ExpID=SBM.ExpID
 where BankStatus=1 order by ID DESC  limit 100";
$StmtBank=$iconn->query($SqlBank);



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
                                    <div class="card-body" id="ShowDataTable">
									
								<div class="col-lg-12 box-footer"  align="center">
			  	
							 <a href="AddBank.php" class="btn btn-success waves-effect waves-light">
				<i class="fa fa-plus"></i> New Bank</a> 
				
					</div>			
			
<table  id="key-datatable" data-paging="false" class="table dt-responsive  w-100" >	
 <thead class="thead-light">
			  <tr>
                
                 
      <th>ID</th>
      <th>Exporter Name</th>
      <th>Account Name</th>
      <th>Account Number</th>
      <th>Bank Name</th>
      <th>Bank Address</th>
      <th>SwiftCode	</th>
      <th>ABA Numnber</th>
	  <th>Bank Details</th>
      <th>Routing Through</th>
      <th>Negotiating Bank	</th>
      <th>Actions</th>
               
				</tr>
				</thead>
                <tbody>
                  <?php
				  
				  $SNo=1;
                   
                    while($RowBank = $StmtBank->fetch_assoc()){?>
                       
					   <tr>
                       
<td> <?php echo $SNo ?></td>
		<td> <?php echo $RowBank['ExpName']?></td>  
		<td> <?php echo $RowBank['AccntName']?></td>  
		<td> <?php echo $RowBank['AccountNumber']?></td>  
		<td> <?php echo $RowBank['BankName']?></td>  
		<!-- <td> <?php //echo mb_strimwidth($RowBank['BankAddress'], 0, 30, "...")?></td>   -->
    <td> <?php echo $RowBank['BankAddress']?></td>  
		<td> <?php echo $RowBank['SwiftCode']?></td> 
		<td> <?php echo $RowBank['ABA_Numnber']?></td>  
    <td> <?php  echo $RowBank['BankDetails']?></td>   
		<td> <?php  echo $RowBank['RoutingThrough']?></td>  
		<td> <?php  echo $RowBank['NegotiatingBank']?></td>

		<!-- <td> <?php // echo  mb_strimwidth($RowBank['BankDetails'],0,30,"...")?></td> --> 
		<!-- <td> <?php // echo  mb_strimwidth($RowBank['RoutingThrough'],0,30,"...")?></td>  
		<td> <?php // echo mb_strimwidth($RowBank['NegotiatingBank'],0,30,"...")?></td>    -->
		
		 
						  <td>
						  
						  
                         
						 <a target="_blank" href="AddBank.php?id=<?php  echo $RowBank['ID'];?>" 
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
