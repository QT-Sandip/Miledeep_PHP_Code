<?php

$PageTitle="Mails";
$PageTitleFolder="SalesNew";
$AccessType=$_SESSION['AccessType']; 


include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

//  $SqlMailsLog="select * from `ScrmMailsLog`  as SML
// left join ScrmExportAvalReports as SER on SER.ID=SML.ReportIds
// ORDER BY MailID desc";

$SqlMailsLog="select * from `ScrmMailsLog`  as SML
left join ScrmExportAvalReports as SER on SER.ID=SML.ReportIds
ORDER by SentDateTime desc limit 100";
$StmtMailsLog=$iconn->query($SqlMailsLog);
//ECHO $SqlMailsLog; EXIT;

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
			  	
							 <a href="CreateMail.php" class="btn btn-success waves-effect waves-light">
				<i class="fa fa-plus"></i> Add</a> 
				
					</div>		
										
			
<table  id="key-datatable" data-paging="false" class="table dt-responsive nowrap w-100" >	
                     <thead class="thead-light">
					 
			  <tr>
                
                 
                 <th>ID</th>
		<th>From Mail</th>
        <th>To Mail</th>
        <th>CC Mail</th>
        <th>Subject</th>
        <th>Message</th>
        <th>Sent Date</th>
      <!--  <th>Actions</th>-->
               
				</tr>
				</thead>
                <tbody>
                  <?php
				  
				  $SNo=1;
                   
                    while($RowMailsLog = $StmtMailsLog->fetch_assoc()){
				$LinerAddress=mb_strimwidth($RowMailsLog['LinerAddress'], 0, 25, "...");
				//$PoIDArray=$RowMailsLog['PoId'];
				  $PoIDArray=explode(',',$RowMailsLog['PoId']); 

			    $FileWriteUrl=$_SESSION["ClientPath"]."Reports/".$RowMailsLog['PrintLink'].$RowMailsLog['QueryID'].'='.$PoID.'&Type=F&FileName='.$FileName."&Token=".$_SESSION['UserID']; 
						?>
                       
					   <tr>
                       
						  <td> <?php echo $SNo; ?></td>
        <td ><?php echo  mb_strimwidth($RowMailsLog['FromMail'], 0, 20, "..."); ?> </td>	  
		<td><?php echo mb_strimwidth($RowMailsLog['ToMail'], 0, 20, "..."); ?></td>
		<td><?php echo mb_strimwidth($RowMailsLog['CCMail'], 0, 20, "..."); ?></td>

		<td><?php echo mb_strimwidth($RowMailsLog['Subject'], 0, 20, "..."); ?></td>
		<td><?php echo mb_strimwidth($RowMailsLog['Message'], 0, 20, "..."); ?></td>
		<td><?php echo date('d-m-Y H:i:s',strtotime($RowMailsLog['SentDateTime'])); ?></td>
		<!--<td> 
		<a href="DocAddReports.php?PoID=<?php $row['PoID']?>" class="mr-3 text-primary" data-toggle="tooltip" data-placement="top"
						 title="View" data-original-title="View">
						 <i class="dripicons-preview font-size-18"></i>
						 </a>
		
						 </td>-->

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
