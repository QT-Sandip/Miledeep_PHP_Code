<?php

$PageTitle = "Preparation List";
$PageTitleFolder = "Sales";
$AccessType = $_SESSION['AccessType'];


include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$SqlPreparation = "SELECT * FROM `ScrmPreparationMaster`WHERE Status=1   ORDER BY `ID` DESC";
$StmtPreparation = $iconn->query($SqlPreparation);



?>
<!DOCTYPE html>

<html lang="en">
<link href="<?php echo  $href ?>" rel="stylesheet" type="text/css" />

<head>

  <?php include($IncludesPath . "title-meta.php"); ?>
  <?php include($IncludesPath . "DbLinks.php"); ?>
  <?php include($IncludesPath . "head-css.php"); ?>

</head>

<body data-sidebar="dark">
  <?php include($IncludesPath . "PreLoader.php"); ?>

  <!-- Begin page -->
  <div id="layout-wrapper">


    <?php
    include($IncludesPath . "topbar.php");
    include($IncludesPath . "sidebar.php");
    ?>



    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <!-- ============================================================== -->
    <div class="main-content">

      <div class="page-content">
        <div class="container-fluid">

          <?php include($IncludesPath . "page-title.php"); ?>


          <div class="row">
            <div class="col-12">
              <div class="card">
                <div class="card-body" id="ShowDataTable">

                  <div class="col-lg-12 box-footer" align="center">

                    <a href="AddPreparation.php" class="btn btn-success waves-effect waves-light">
                      <i class="fa fa-plus"></i> New Preparation</a>

                  </div>

                  <table id="key-datatable" data-paging="true" class="table dt-responsive nowrap w-100">
                    <thead class="thead-light">
                      <tr>
                        <th>ID</th>
                        <th>Preparation Name</th>
                        <th>Description</th>
                        <th>Short Code</th>
                        <th>HS Code</th>
                        <th>Qc Preparation Short Code</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php

                      $SNo = 1;

                      while ($RowPreparation = $StmtPreparation->fetch_assoc()) { ?>

                        <tr>

                          <td> <?php echo $SNo ?></td>
                          <td> <?php echo $RowPreparation['PreparationName'] ?></td>
                          <td> <?php echo mb_strimwidth($RowPreparation['PreparationDesc'], 0, 30, "...") ?></td>
                          <td> <?php echo $RowPreparation['ShortCode'] ?></td>
                          <td> <?php echo $RowPreparation['HSCode'] ?></td>
                          <td> <?php echo $RowPreparation['QcPreparationShortCode'] ?></td>
                          <td>
                            <a target="_blank" href="AddPreparation.php?id=<?php echo $RowPreparation['ID']; ?>" class="mr-3 text-primary Edit" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="mdi mdi-pencil font-size-18"></i></a>
                          </td>
                        </tr>
                      <?php
                        $SNo++;
                      }
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

  <?php include($IncludesPath . "footer.php"); ?>

  </div>
  <!-- end main content-->

  </div>
  <!-- END layout-wrapper -->



  <?php include($IncludesPath . "vendor-scripts.php"); ?>
  <?php include($IncludesPath . "NewScripts.php"); ?>
  <?php include($IncludesPath . "ShowDataTable.php"); ?>


</body>

</html>

<script>
  $(document).ready(function() {
    showData();



  });
</script>
