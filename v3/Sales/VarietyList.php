<?php

$PageTitle = "Variety List";
$PageTitleFolder = "Sales";
$BranchID = $_SESSION['BranchID'];
$AccessType = $_SESSION['AccessType'];


include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$SqlVarietyList = "SELECT *,SBM.BrandName AS BRND,STM.ShortCode as Treatment,
  APS.PackingStyle AS PCKSTYL
  FROM `ScrmPIVarietyList` SPVL
left join ScrmBrandsMaster SBM ON SBM.BrandID=SPVL.BrandName
left join AdminPackingStyles APS ON APS.PCKID=SPVL.PackingStyle
left join ScrmTreatmentsMaster STM ON STM.ShortCode=SPVL.Treatment
  where SPVL.Status='1' ORDER by VarietyID desc ";

$StmtVarietyList = $iconn->query($SqlVarietyList);

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

                    <a href="AddVariety" class="btn btn-success waves-effect waves-light">
                      <i class="fa fa-plus"></i> New Variety</a>

                  </div>


                  <table id="key-datatable" data-paging="false" class="table dt-responsive wrap w-100">
                    <thead class="thead-light">
                      <tr>
                        <th>S.No</th>
                        <th>Short Name</th>
                        <th>Full Name</th>
                        <th>Grade</th>
                        <th>FreezingType</th>
                        <th>Treatment</th>
                        <th>Preparation</th>
                        <th>BrandName </th>
                        <th>PackingStyle </th>
                        <th>Product Description</th>
                        <th>Type</th>
                        <th>BuyerSUPC</th>
                        <th>Our SUPC</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php

                      $SNo = 1;

                      while ($RowVariety = $StmtVarietyList->fetch_assoc()) {


                      ?>

                        <tr>

                          <td> <?php echo $SNo ?></td>
                          <!--<td> <?php echo $RowVariety['VarietyID'] ?></td> -->
                          <td> <?php echo $RowVariety['ShortName'] ?></td>
                          <td> <?php echo $RowVariety['FullName'] ?></td>
                          <td> <?php echo $RowVariety['Grade'] ?></td>
                          <td> <?php echo $RowVariety['FreezingType'] ?></td>
                          <td> <?php echo $RowVariety['TreatmentName'] ?></td>
                          <td> <?php echo $RowVariety['PreparationType'] ?></td>
                          <td> <?php echo $RowVariety['BRND'] ?></td>
                          <td> <?php echo $RowVariety['PCKSTYL'] ?></td>

                          <td> <?php echo $RowVariety['ProductDescription'] ?></td>
                          <td> <?php echo $RowVariety['TypeProduct'] ?></td>
                          <td> <?php echo $RowVariety['BuyerSUPC'] ?></td>
                          <td> <?php echo $RowVariety['DeviSUPC'] ?></td>

                          <td>



                            <a target="" href="AddVariety?id=<?php echo $RowVariety['VarietyID']; ?>" class="mr-3 text-primary Edit" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="mdi mdi-pencil font-size-18"></i></a>

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