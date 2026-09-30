<?php 
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';


?>
<!doctype html>
<html lang="en">

    <head>
        
        <?php include_once ROOT . '/includes/head.php' ?>

    </head>

    <?php  include_once ROOT . '/includes/body-preloader.php' ?>  

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

                        <?php // include_once ROOT . 'Sales/includes/page-filter.php' ?>
                        <?php // include_once WORKING_MODULE . '/includes/page-filter.php' ?>

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">

                                <div class="card">
                                    <div class="card-body">
                                        <form role="form" method="post" id="frmSalaryElements">
                                            <div class="row">
												
												<div class="form-group mb-3 col-sm-3" >
													<label for="txtElementName">Element Name <span style="color:red">*</span></label>
													<input required type="text"  class="form-control" name='txtElementName' id="txtElementName" 
														placeholder="Enter Element Name"  />		
												</div>

												<div class="form-group mb-3 col-lg-3">
													<label for="drpElementType">Element Type<span style="color:red">*</span></label>              			   
													<select required class="form-control drpSelect" name="drpElementType" id="drpElementType">
													    <option value=""> Select </option>
													    <option value="text"> Text </option>
													    <!-- <option value="number"> Number </option> -->
													    <!-- <option value="date"> Date </option> -->
													    <!-- <option value="dropdown"> Dropdown </option> -->
													<select>   
												</div> 

                                                <div class="form-group mb-3 col-sm-3" >
													<label for="txtElementValue">Element(Dropdown) Value (comma separated)  <span style="color:red">*</span></label>
													<input required type="text"  class="form-control" name='txtElementValue' id="txtElementValue" 
														placeholder="Enter Element Value"  />		
												</div>

                                                <!-- <div class="form-group mb-3 col-lg-3">
													<label for="drpBasedon">Based on<span style="color:red">*</span></label>              			   
													<select required class="form-control drpSelect" name="drpBasedon" id="drpBasedon">
													    <option value=""> Select </option>
													    <option value="basic"> Basic Salary </option>
													<select>   
												</div>                                                  -->

                                                <div class="form-group mb-3 col-lg-3">
													<label for="drpCalculationMode">Calculation Mode<span style="color:red">*</span></label>              			   
													<select required class="form-control drpSelect " name="drpCalculationMode" id="drpCalculationMode">
													    <option value=""> Select </option>
													    <option value="plus"> Addition </option>
													    <option value="minus"> Deduction </option>
													<select>   
												</div> 

                                            </div>

                                            <div class="col-xs-12 box-footer text-center">
												<a target="" href="ExporterDetails.php" class="btn btn-danger"> Close</a>
												<button type="submit" name='submit' class="btn btn-success"> Submit </button>
											</div>	
                                        </form>

                                    </div>
                                </div>

                                <div id="divDataList">
                                    <!-- -->
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

        <!-- Required datatable js -->
        <script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
        <script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
        
        <?php include_once ROOT . 'includes/app-js.php' ?>
        

        <script>

            function fnShowDataTable() {
                var table = $('#tblDataTable').DataTable({
                    scrollY: "600px",
                    scrollX: true,
                    sScrollXInner: "100%",
                    paging: false,
                    destroy: true,
                    fixedColumns: true,
                    scrollCollapse: true,
                    bInfo: false

                });
            }
            
            $(document).ready(function () {
                $.ajax({
                    url: 'Ajax/AjaxSalaryElements.php',
                    type: 'POST',
                    data: {
                        methodType: "get",
                    },
                    success: function (data) {
                        $("#divDataList").html(data);
                        fnShowDataTable();
                    }
                });
            });

            jQuery.validator.addMethod('selectcheck', function (value) {
				return (value != '0' || value != '');
			}, "year required");
			jQuery.validator.addMethod("noSpace", function(value, element) { 
				return value == '' || value.trim().length != 0;  
			}, "No space please and don't leave it empty");

            
// txtElementName
// drpElementType
// txtElementValue
// drpBasedon
// drpCalculationMode
			
            $("#frmSalaryElements").validate({
                onkeyup: false,
                onfocusout: false,
                invalidHandler: function (form, validator) {
                    var errors = validator.numberOfInvalids();
                    if (errors) {
                        var firstInvalidElement = $(validator.errorList[0].element);
                        //$('html,body').scrollTop(firstInvalidElement.offset().top - 100);
                        firstInvalidElement.focus();
                    }
                },
                ignore: "",
                rules: {
                    txtElementName: { required: true, noSpace: true },
                    drpElementType: { required: true, selectcheck: true },
                    txtElementValue: { required: false },
                    drpBasedon: { required: false },
                    drpCalculationMode: { required: true, selectcheck: true }
                },
                errorPlacement: function () {
                    return false;  // suppresses error message text
                },
                submitHandler: function () {
                    $.ajax({
                        url: 'Ajax/AjaxSalaryElements.php',
                        type: 'POST',
                        data: {
                            methodType: "post",
                            elementName: $("#txtElementName").val(),
                            elementType: $("#drpElementType").val(),
                            elementsValues: $("#txtElementValue").val(),
                            basedOn: $("#drpBasedon").val(),
                            calculationMode: $("#drpCalculationMode").val(),
                        },
                        success: function (data) {
                            $("#divDataList").html(data);
                            fnShowDataTable();
                            return false;
                        }
                    });

                    return false;
                }

            });


        </script>

    </body>
</html>
