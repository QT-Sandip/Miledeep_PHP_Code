<?php

$Status = $_SESSION['Status'];


include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");


if (isset($_POST['submit'])) {
}
?>




<!DOCTYPE html>


<html lang="en">

<head>

    <?php include($IncludesPath . "title-meta.php"); ?>
    <?php include("Includes/DbLinks.php"); ?>
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

                    <?php
                    if ($Status != '') {
                        //Show Alert 
                    ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="mdi mdi-check-all mr-2"></i>
                            <?php echo $Status;
                            $_SESSION['Status'] = '';      ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>

                    <?php } ?>

                    <?php include($IncludesPath . "page-title.php"); ?>


                    <div class="row">
                        <div class="col-12">
                            <div class="card">

                                <form role="form" method="post">
                                    <div class="card-body">

                                        <div class="row">

                                            <div class="form-group col-lg-3">
                                                <label for="drpFeildBelongsto">Feild Belongs To </label>
                                                <select class="form-control select2" id="drpFeildBelongsto" name="drpFeildBelongsto">
                                                    <option value="">Student </option>
                                                    <option value="">Staff </option>
                                                </select>
                                            </div>
                                            <div class="form-group col-lg-3">
                                                <label for="drpFeildType">Feild Type </label>
                                                <select class="form-control select2" id="drpFeildType" name="drpFeildType">
                                                    <option value="">Student </option>
                                                    <option value="">Staff </option>
                                                </select>
                                            </div>
                                            <div class="form-group  col-lg-3">
                                                <label for="txtFeildName">Feild Name</label>
                                                <input class="form-control" type="text" id="txtFeildName" name="txtFeildName" placeholder="Enter Feild Name" value="" required>
                                            </div>   
                                            <div class="form-group  col-lg-3">
                                                <label for="txtGrid">Grid</label>
                                                <input class="form-control" type="text" id="txtGrid" name="txtGrid" placeholder="Eg:- col-md-6" value="" required>
                                            </div>
                                            <div class="form-group  col-lg-3">
                                                <label for="txtFeildValues">Feild Values('Seperated By Commas')</label>
                                                <input class="form-control" type="text" id="txtFeildValues" name="txtFeildValues" placeholder="Enter Gross Salary" value="" required>
                                            </div>

                                            <!-- end row -->
                                        </div>
                                        <div class="col-xs-12 box-footer" align="right">
                                            <button type="submit" name='submit' id="submit" class="btn btn-success waves-effect waves-light">
                                                Submit</button>
                                        </div>

                                </form>
                            </div>
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
    <?php include("Includes/NewScripts.php"); ?>



</body>

</html>


<Script>
    $(document).ready(function() {
        $("#submit").click(function() {
            var tabledata = {
                'drpFeildBelongsto': $("#drpFeildBelongsto").val().trigger("change"),
                'drpFeildType': $("#drpFeildType").val().trigger("change"),
                'txtFeildName': $("#txtFeildName").val(),
                'txtGrid': $("#txtGrid").val(),
                'txtFeildValues': $("#txtFeildValues").val()
            };
            $.ajax({
                url: 'Ajax/AjaxCreateAllEmployeeDeatils2',
                type: 'POST',
                data: tabledata,
                success: function(data) {
                    $("#SubDeptID").html(data);
                }
            })
        })
    });


    // $(document).on('change', '#DeptID', function(e) {
    //     e.preventDefault();

    //     var DeptId = $(this).val();
    //     $.ajax({
    //         url: 'Ajax/AjDropDowns',
    //         type: 'POST',
    //         data: {
    //             'Type': 'SubDept',
    //             'DeptId': DeptId
    //         },
    //         success: function(data) {
    //             $("#SubDeptID").html(data);
    //         }
    //     })
    // });



    // $(document).on('change', '#ShiftCategory', function(e) {

    //     var ShiftCategory = $(this).val();


    //     if (ShiftCategory == 'Rotational') {}

    // });
</script>