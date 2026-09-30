<div class="modal fade" id="modalCreateSupplier" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-xl"> <!-- modal-fullscreen -->

        <div class="modal-content">

            <div class="modal-header">

                <h5 class="modal-title" id="exampleModalLabel">Add Supplier </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>



            </div>

            <form role="form" id="frmCreateSupplier" method="post">

                <div class="modal-body">



                    <div class="container-fluid">

                        <div class="row">



                            <div class="row">

                                <div class="form-group col-sm-3">

                                    <label for="drpSuppliersType" class="form-label">Suppliers Type <span
                                            style="color:red">*</span></label>

                                    <select class="drpSelect" name="drpSuppliersType" id="drpSupplierType"
                                        style="z-index: 2000;">

                                        <option value=""> Select</option>

                                        <option value="Individual">Individual</option>



                                        <!-- <option value="Farm" >Farm</option> -->
                                        <?php if ($_SESSION['ClientID'] === 'DFLV3' || 
                                        $_SESSION['ClientID'] === 'CAMAROV3') { ?>
                                            <option value="Firm">Firm</option>
                                        <?php }else{ ?>
                                            <option value="Business">Business</option>
                                        <?php } ?>

                                    </select>

                                </div>

                                <div class="col-md-6 col-lg-3 mb-3">

                                    <label for="txtSupplierName" class="form-label">Suppliers Name<span
                                            style="color:red">*</span></label>

                                    <input type="text" class="form-control" id="txtSupplierName" name="txtSupplierName"
                                        placeholder="Enter Financier Name " required />

                                </div>

                                <div class="col-md-6 col-lg-3 mb-3">

                                    <label for="txtGSTNumber" class="form-label">GST Number </label>

                                    <input type="text" class="form-control" id="txtGSTNumber" name="txtGSTNumber"
                                        placeholder="Enter GST Number" />

                                </div>

                                <div class="col-md-6 col-lg-3 mb-3">

                                    <label for="txtPANNumber" class="form-label">PAN Number <span
                                            style="color:red">*</span></label>

                                    <input type="text" class="form-control" id="txtPANNumber" name="txtPANNumber"
                                        placeholder="Enter PAN Number" />

                                </div>

                                <div class="col-md-6 col-lg-3 mb-3">

                                    <label for="txtSupplierBankName" class="form-label">Suppliers Bank Name</label>

                                    <input type="text" class="form-control" id="txtSupplierBankName"
                                        name="txtSupplierBankName" placeholder="Enter Financier Bank Name " />

                                </div>

                                <div class="col-md-6 col-lg-3 mb-3">

                                    <label for="txtBankAccountNumber" class="form-label">Bank Account Number </label>

                                    <input type="text" class="form-control" id="txtBankAccountNumber"
                                        name="txtBankAccountNumber" placeholder="Enter Bank Account Number " />

                                </div>

                                <div class="col-md-6 col-lg-3 mb-3">

                                    <label for="txtIFSCCode" class="form-label">IFSCCode </label>

                                    <input type="text" class="form-control" id="txtIFSCCode" name="txtIFSCCode"
                                        placeholder="Enter IFSC Code " />

                                </div>



                                <div class="col-md-6 col-lg-3 mb-3">

                                    <label for="txtSupplierAddress" class="form-label">Supplier Address </label>

                                    <input type="text" class="form-control" id="txtSupplierAddress"
                                        name="txtSupplierAddress" placeholder="Enter Supplier Address" />

                                </div>

                                <div class="col-md-6 col-lg-3 mb-3">

                                    <label for="txtSupplierEMailID" class="form-label">Supplier EMail ID</label>

                                    <input type="text" class="form-control" id="txtSupplierEMailID"
                                        name="txtSupplierEMailID" placeholder="Enter Supplier EMail ID" />

                                </div>

                                <div class="col-md-6 col-lg-3 mb-3">

                                    <label for="txtPhoneNumber" class="form-label">Phone Number </label>

                                    <input type="text" class="form-control" id="txtPhoneNumber" name="txtPhoneNumber"
                                        placeholder="Enter Phone Number" />

                                </div>



                            </div>



                        </div>

                    </div>

                </div>

                <div class="modal-footer">

                    <input type="hidden" id="hdnSelectedRow" name="hdnSelectedRow" />

                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>

                    <button type="submit" name='submit' class="btn btn-primary">Save changes</button>

                </div>

            </form>

        </div>

    </div>

</div>



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

            drpSuppliersType: { required: true },

            drpSuppliers: { required: true, noSpace: true },



            txtPANNumber: { required: true, noSpace: true },

        },

        errorPlacement: function () {

            return false;  // suppresses error message text

        },

        submitHandler: function () {

            // console.log($("#drpSupplierType").val()); 



            $.ajax({

                url: 'Ajax/AjaxSupplier.php',

                type: 'POST',

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

                    PhoneNumber: $("#txtPhoneNumber").val()

                },

                success: function (data) {

                    console.log(data);



                    if (data.Error !== null) {

                        alert(data.Error);

                    } else {

                        console.log(data.Data.FinancierName, data.Data.SupplierID);

                        $('#modalCreateSupplier').modal('hide');



                        alert(data.Msg);



                        var newOption = new Option(data.Data.FinancierName, data.Data.SupplierID, false, false);

                        $('#drpSuppliers').append(newOption).trigger('change');



                        $('#drpSuppliers').val(data.Data.SupplierID).trigger('change');



                        $("#drpSupplierType").val("").trigger('change');

                        $("#txtSupplierName, #txtGSTNumber, #txtPANNumber, #txtSupplierBankName, #txtBankAccountNumber, #txtIFSCCode, #txtSupplierAddress, #txtSupplierEMailID, #txtPhoneNumber").val("");



                    }



                    return false;

                }

            });



            return false;



        }

    });



</script>
