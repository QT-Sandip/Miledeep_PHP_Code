<?php 

include_once $_SERVER['DOCUMENT_ROOT'] .'/config/global.php';
include_once ROOT.'config/api-call.php';

// $id = isset($_GET["id"]) && !empty($_GET["id"]) ? $_GET["id"] : "";

if($id != "") {
    // API CALL 
}

$api = API_RM_DROPDOWNS;

$resDropdownsAPI = CallAPI($api, "GET", "", "");
$resRmSuppliers = $resDropdownsAPI->Data->RmSuppliers;

?>

<!doctype html>
<html lang="en">

<head>

    <?php include_once ROOT . 'includes/head.php' ?>

</head>

<?php include_once ROOT . 'includes/body-preloader.php' ?>

<!-- Begin page -->
<div id="layout-wrapper">

    <?php include_once ROOT . 'includes/menu.php' ?>

    <!-- ============================================================== -->
    <!-- Start right Content here -->
    <!-- ============================================================== -->
    <div class="main-content">
    <div class="page-content">
            <div class="container-fluid">

                <?php include_once ROOT . 'includes/page-header.php' ?>

                <?php if(isset($_SESSION['Msg'])) { ?>
                    <div class="alert alert-success" role="alert">
                        <?php echo $Status ?>
                    </div>
                <?php $_SESSION['Error'] = null; } ?>

                <?php if (isset($_SESSION['Error'])) { ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $Status ?>
                </div>
                <?php $_SESSION['Msg'] = null; } ?>

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                    <div class="card">
                            <div class="card-body">
                                <!-- <h4 class="card-title">Bootstrap Validation - Normal</h4> -->
                                <form id="frmCreateSupplier">
                                    <div class="row">
                                        <div class="form-group col-sm-3">
                                            <label for="drpSuppliersType" class="form-label">Suppliers Type <span style="color:red">*</span></label>
                                            <select class="drpSelect" name="drpSupplierType" id="drpSupplierType" >
                                                <option value=""> Select</option>
                                                <option value="Individual" >Individual</option>
                                                <option value="Bussiness" >Bussiness</option>
                                            </select>
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtSupplierName" class="form-label">Suppliers Name<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtSupplierName" name="txtSupplierName"
                                                placeholder="Enter Financier Name " required />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtGSTNumber" class="form-label">GST Number <span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtGSTNumber" 
                                                name="txtGSTNumber" placeholder="Enter GST Number"  />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtPANNumber" class="form-label">PAN Number <span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtPANNumber"  
                                                name="txtPANNumber" placeholder="Enter PAN Number"  />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtSupplierBankName" class="form-label">Suppliers Bank Name<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtSupplierBankName" name="txtSupplierBankName"
                                                placeholder="Enter Financier Bank Name "  />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtBankAccountNumber" class="form-label">Bank Account Number <span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtBankAccountNumber" 
                                                name="txtBankAccountNumber" placeholder="Enter Bank Account Number "  />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtIFSCCode" class="form-label">IFSCCode <span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtIFSCCode" 
                                                name="txtIFSCCode" placeholder="Enter IFSC Code "  />
                                        </div>
                                        
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtSupplierAddress" class="form-label">Supplier Address <span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtSupplierAddress" 
                                                name="txtSupplierAddress" placeholder="Enter Supplier Address"  />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtSupplierEMailID" class="form-label">Supplier EMail ID<span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtSupplierEMailID" 
                                                name="txtSupplierEMailID" placeholder="Enter Supplier EMail ID"  />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtPhoneNumber" class="form-label">Phone Number <span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtPhoneNumber" 
                                                name="txtPhoneNumber" placeholder="Enter Phone Number"  />
                                        </div>
                                        <div class="col-md-6 col-lg-3 mb-3">
                                            <label for="txtPurchaseCenter" class="form-label">Purchase Center <span style="color:red">*</span></label>
                                            <input type="text" class="form-control" id="txtPurchaseCenter" 
                                                name="txtPurchaseCenter" placeholder="Enter Purchase Center"  />
                                        </div>
                                    </div>
                                    <div class="text-center">
                                        <!-- <a href="rm-booking-list" class="btn btn-danger" >Close</a> -->
                                        <button type="submit" name="submit" class="btn btn-success">Save</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                    </div>
                </div>

            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->

        <?php include_once ROOT . 'includes/footer.php' ?>

    </div>
    <!-- end main content-->

</div>
<!-- END layout-wrapper -->

<?php include_once ROOT . 'includes/common-js.php' ?>



<!-- Required datatable js -->
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
<script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

<?php include_once ROOT . '/includes/app-js.php' ?>
<script>
        
    $("#frmCreateSupplier").validate({
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
            drpSuppliers: { required: true, selectcheck: true },
        },
        errorPlacement: function () {
            return false;  // suppresses error message text
        },
        submitHandler: 
        function () {
            console.log($("#drpSupplierType").val()); 
            
             $.ajax({
                url:'Ajax/AjaxSupplier.php',
                type:'POST',
                data: {
                    // id: '<?php // echo $id; ?>',
                    SupplierType: $("#drpSupplierType").val(),
                    SupplierName: $("#txtSupplierName").val(),
                    GSTNumber: $("#txtGSTNumber").val(),
                    PANNumber: $("#txtPANNumber").val(),
                    SupplierBankName: $("#txtSupplierBankName").val(),
                    BankAccountNumber: $("#txtBankAccountNumber").val(),
                    IFSCCode: $("#txtIFSCCode").val(),
                    SupplierAddress: $("#txtSupplierAddress").val(),
                    SupplierEMailID: $("#txtSupplierEMailID").val(),
                    PhoneNumber: $("#txtPhoneNumber").val(),
                    PurchaseCenter: $("#txtPurchaseCenter").val()
                },
                success:function(data) {
                    console.log(data);

                    if(data.Error != "") {
                        alert(data.Error);
                    }else{
                        alert(data.Msg);
                    }

                    return false;
                }
            });
            
            return false;          
                             
        }  
    });
                
</script>

</body>

</html>