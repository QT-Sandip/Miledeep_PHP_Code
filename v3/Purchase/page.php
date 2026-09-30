<?php include_once '../config/global.php' ?>
<!doctype html>
<html lang="en">

    <head>
        
        <?php include_once ROOT . '/includes/head.php' ?>

    </head>

    <?php include_once ROOT . '/includes/body-preloader.php' ?>  

        <!-- Begin page -->
        <div id="layout-wrapper">
            
            <?php include_once ROOT . '/includes/menu.php' ?>                   

            <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">

                <div class="page-content">
                    <div class="container-fluid">

                        <?php include_once ROOT . '/includes/page-header.php' ?>

                        <?php include_once ROOT . '/includes/page-filter.php' ?>

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">

                                <div class="card">
                                    <div class="card-body">
                                        <!-- <h4 class="card-title">Bootstrap Validation - Normal</h4> -->
                                        <form class="needs-validation" novalidate>
                                            <div class="row">
                                                <div class="col-md-6 col-lg-3 mb-3">
                                                    <label for="validationCustom01" class="form-label">First name</label>
                                                    <input type="text" class="form-control" id="validationCustom01"
                                                        placeholder="First name" value="Mark" />
                                                </div>
                                                <div class="col-md-6 col-lg-3 mb-3">                                                    
                                                    <label for="validationCustom02" class="form-label">Last name</label>
                                                    <input type="text" class="form-control" id="validationCustom02"
                                                        placeholder="Last name" value="Otto" />
                                                </div>
                                                <div class="col-md-6 col-lg-3 mb-3">                                                    
                                                    <label for="validationCustom03" class="form-label">State</label>
                                                    <select class="drpSelect" id="validationCustom03" name="state">
                                                        <option value="">Select Conignee</option>
                                                        <option value="AL">Alabama</option>
                                                        <option value="WY">Wyoming</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6 col-lg-3 mb-3">                                                    
                                                    <label for="validationCustom04" class="form-label">City</label>
                                                    <input type="text" class="form-control" id="validationCustom04"
                                                        placeholder="City" />
                                                    <div class="invalid-feedback">
                                                        Please provide a valid city.
                                                    </div>
                                                </div>
                                                
                                                <div class="col-md-6 col-lg-3 mb-3">                                                    
                                                    <label for="validationCustom05" class="form-label">Zip</label>
                                                    <input type="text" class="form-control" id="validationCustom05"
                                                        placeholder="Zip" />
                                                    <div class="invalid-feedback">
                                                        Please provide a valid zip.
                                                    </div>
                                                </div>

                                                <div class="col-md-6 col-lg-3 mb-3">                                                    
                                                    <label for="validationCustom05" class="form-label">Zip</label>
                                                    <input type="text" class="form-control" id="validationCustom05"
                                                        placeholder="Zip" />
                                                    <div class="invalid-feedback">
                                                        Please provide a valid zip.
                                                    </div>
                                                </div>

                                                <div class="col-md-6 col-lg-3 mb-3">                                                    
                                                    <label for="validationCustom05" class="form-label">Zip</label>
                                                    <input type="text" class="form-control" id="validationCustom05"
                                                        placeholder="Zip" />
                                                    <div class="invalid-feedback">
                                                        Please provide a valid zip.
                                                    </div>
                                                </div>
                                                <div class="col-md-6 col-lg-3 mb-3">                                                    
                                                    <label for="validationCustom05" class="form-label">Zip</label>
                                                    <input type="text" class="form-control" id="validationCustom05"
                                                        placeholder="Zip" />
                                                    <div class="invalid-feedback">
                                                        Please provide a valid zip.
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="text-center">
                                                <button class="btn btn-primary" type="submit">Save</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>


                            </div>
                        </div>

                    </div> <!-- container-fluid -->
                </div>
                <!-- End Page-content -->
                
                <?php include_once ROOT . '/includes/footer.php' ?>

            </div>
            <!-- end main content-->

        </div>
        <!-- END layout-wrapper -->

        <?php include_once ROOT . '/includes/common-js.php' ?>

        <?php include_once ROOT . '/includes/app-js.php' ?>

    </body>
</html>
