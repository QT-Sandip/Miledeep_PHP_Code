<?php
$Status = $_SESSION['Status'];

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$PageTitle = "Add New Financier";

$PageTitleFolder = "Purchase";



extract($_POST);
extract($_GET);



if (isset($_POST['submit'])) {


    $SqlFinancierDetails = "INSERT INTO `RmSuppliers`(`FinancierName`,
	`BankName`, `BankAccNo`, `GstNo`, `IFSC`, `PanNumber`, `IsCompany`) VALUES
	 ('$FinancierName','$BankName','$BankAccNo','$GstNo','$IFSC','$PanNumber','$IsCompany')";


    $StmtFinancierDetails = $iconn->query($SqlFinancierDetails);
    //	echo $SqlFinancierDetails; exit;
    if ($StmtFinancierDetails) {
        $insertedFinancierId = $iconn->insert_id;
        legacyAuditLogInsertById('RmSuppliers', 'ID', $insertedFinancierId, array(
            'FinancierName' => $FinancierName,
            'BankName' => $BankName,
            'BankAccNo' => $BankAccNo,
            'GstNo' => $GstNo,
            'IFSC' => $IFSC,
            'PanNumber' => $PanNumber,
            'IsCompany' => $IsCompany,
        ), array(
            'ReferenceNo' => $insertedFinancierId,
            'Remarks' => 'Financier created from legacy purchase screen',
        ), 'Purchase');


        echo "<script>alert('Success');</script>";

    }
}

?>



<!DOCTYPE html>
<html lang="en">

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

                                <form role="form" method="post">
                                    <div class="card-body">

                                        <div class="row">


                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Financier Name</label>
                                                <input required type="text" class="form-control" name='FinancierName'
                                                    id="FinancierName" placeholder="Enter Financier Name">
                                            </div>






                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputPassword1">GST Number</label>
                                                <input type="text" class="form-control" name="GstNo" id="GstNo"
                                                    placeholder="Enter GST Number">
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputEmail1">Company </label>

                                                <select class="form-control select2" name="IsCompany" id="IsCompany">


                                                    <option Value="0">Non-Company </OPTION>
                                                    <option Value="1">Company </OPTION>

                                                </select>
                                            </div>



                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputPassword1">PAN Number</label>
                                                <input type="text" class="form-control" name="PanNumber" id="PanNumber"
                                                    placeholder="Enter PAN Number ">
                                            </div>


                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputPassword1">Financier Bank Name</label>
                                                <input type="text" class="form-control" name="BankName" id="BankName"
                                                    placeholder="Enter Bank Name ">
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputPassword1">Bank Account Number</label>
                                                <input type="text" class="form-control" name="BankAccNo" id="BankAccNo"
                                                    placeholder="Enter Bank Account Number ">
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="exampleInputPassword1">IFSC Code</label>
                                                <input type="text" class="form-control" name="IFSC" id="IFSC"
                                                    placeholder="Enter IFSC Code">
                                            </div>




                                        </div>







                                        <div class="col-xs-12 box-footer" align="right">
                                            <button type="submit" name='submit' class="btn btn-success">Submit</button>
                                        </div>
                                </form>
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



</body>

</html>
