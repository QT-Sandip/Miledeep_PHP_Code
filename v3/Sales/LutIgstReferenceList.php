<?php

include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");

$PageTitle = "LUT / IGST Reference Numbers";
$PageTitleFolder = "Sales";
$AccessType = isset($_SESSION['AccessType']) ? $_SESSION['AccessType'] : '';
$ListError = '';

$SqlLutIgst = "SELECT SELID.*, AED.ExpName FROM `ScrmExporterLutIgstDetails` as SELID
left join AdminExporterDetails as AED on AED.ExpID=SELID.ExporterID
order by SELID.FromDate DESC, SELID.ID DESC";
$StmtLutIgst = $iconn->query($SqlLutIgst);
if(!$StmtLutIgst){
    $ListError = 'Unable to load LUT / IGST references.';
    error_log('LutIgstReferenceList query failed: '.$iconn->error);
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

<?php if($ListError!=''){ ?>
<div class="alert alert-danger" role="alert"><?php echo htmlspecialchars($ListError, ENT_QUOTES, 'UTF-8'); ?></div>
<?php } ?>


       <div class="row">
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-body" id="ShowDataTable">

								<div class="col-lg-12 box-footer"  align="center">

							 <a href="AddLutIgstReference.php" class="btn btn-success waves-effect waves-light">
				<i class="fa fa-plus"></i> New LUT / IGST Reference</a>

					</div>

<table  id="key-datatable" data-paging="true" class="table dt-responsive  w-100" >
 <thead class="thead-light">
			  <tr>

      <th>ID</th>
      <th>Exporter Name</th>
      <th>Type</th>
      <th>Reference Number</th>
      <th>Financial Year</th>
      <th>From Date</th>
      <th>Remarks</th>
      <th>Status</th>
      <th>Actions</th>

				</tr>
				</thead>
                <tbody>
                  <?php

				  $SNo=1;

                    while($StmtLutIgst && ($RowLutIgst = $StmtLutIgst->fetch_assoc())){?>

					   <tr>

<td> <?php echo $SNo ?></td>
		<td> <?php echo htmlspecialchars($RowLutIgst['ExpName'], ENT_QUOTES, 'UTF-8')?></td>
		<td> <?php echo htmlspecialchars($RowLutIgst['Type'], ENT_QUOTES, 'UTF-8')?></td>
		<td> <?php echo htmlspecialchars($RowLutIgst['ReferenceNumber'], ENT_QUOTES, 'UTF-8')?></td>
		<td> <?php echo htmlspecialchars($RowLutIgst['FinancialYear'], ENT_QUOTES, 'UTF-8')?></td>
		<td> <?php echo htmlspecialchars($RowLutIgst['FromDate'], ENT_QUOTES, 'UTF-8')?></td>
		<td> <?php echo htmlspecialchars($RowLutIgst['Remarks'], ENT_QUOTES, 'UTF-8')?></td>
		<td> <?php echo ((int) $RowLutIgst['Status']===1) ? 'Active' : 'Inactive'; ?></td>

						  <td>


						 <a target="" href="AddLutIgstReference.php?id=<?php echo (int) $RowLutIgst['ID'];?>"
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
