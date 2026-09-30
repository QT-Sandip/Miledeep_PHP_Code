<style type="text/css">
    .btn-close {
  --bs-btn-close-color: #000;
  --bs-btn-close-bg: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16' fill='%23000'%3e%3cpath d='M.293.293a1 1 0 0 1 1.414 0L8 6.586 14.293.293a1 1 0 1 1 1.414 1.414L9.414 8l6.293 6.293a1 1 0 0 1-1.414 1.414L8 9.414l-6.293 6.293a1 1 0 0 1-1.414-1.414L6.586 8 .293 1.707a1 1 0 0 1 0-1.414z'/%3e%3c/svg%3e");
  --bs-btn-close-opacity: 0.5;
  --bs-btn-close-hover-opacity: 0.75;
  --bs-btn-close-focus-shadow: none;
  --bs-btn-close-focus-opacity: 1;
  --bs-btn-close-disabled-opacity: 0.25;
  --bs-btn-close-white-filter: invert(1) grayscale(100%) brightness(200%);
  -webkit-box-sizing:
content-box;
  box-sizing: content-box;
  width: 1em;
  height: 1em;
  padding:
.25em .25em;
  color: var(--bs-btn-close-color);
  background: transparent var(--bs-btn-close-bg) center/1em auto no-repeat;
  border:
0;
  border-radius:
.375rem;
  opacity: var(--bs-btn-close-opacity);
}
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"
    type="text/javascript"></script>
<div class="modal fade" id="myModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Title</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        Content here
      </div>
    </div>
  </div>
</div>
<div class="modal fade" id="modalAddWorkerBatch" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl"> <!-- modal-fullscreen -->
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel"></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

            </div>
            <form role="form" id="frmAddWorkerBatch" method="post">
                <div class="modal-body">    
                    
                    <div class="container-fluid">
                        <div class="row">
                            
                                <div class="row">
                                    <div class="col-md-12 col-lg-12 mb-12">
                                            <label id="labelName" class="form-label"></label>
                                            <input type="text" class="form-control" id="inputValue" name="inputValue" required />
                                        </div>                                       
                                </div>
                                
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <input type="hidden" id="inputType" name="inputType" />
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                    <button type="submit"  name='submit' class="btn btn-primary">Save changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style type="text/css">
.modal-dialog{max-width: max-content!important;}    
</style>
<script>
        
    $("#frmAddWorkerBatch").validate({
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
            inputValue: { required: true, noSpace: true },
        },
        errorPlacement: function () {
            return false;  // suppresses error message text
        },
        submitHandler: function () {            
             $.ajax({
                url:'Ajax/AjaxWorkerMastersUpdate',
                type:'POST',
                dataType: 'json',
                data: {
                    inputValue: $("#inputValue").val(),
                    inputType:$("#inputType").val()
                },
                success:function(data) {
                    console.log(data);
                        window.test=data;
                    if(data.Error !== null) {
                        alert(data.Error);
                    }else{
                        var Type = $("#inputType").val().replace("Add", "").trim();
                        $('#modalAddWorkerBatch').modal('hide');
                        $('#inputValue').val('');
                        // Refresh the table data from the server
                        $('#' + Type+'MasterTable').DataTable().ajax.reload(null, false); // false = don't reset pagination
                        // if(data.Type == "AddBatch"){
                        //     var newOption = new Option(data.Data.BatchName, data.Data.BatchID, false, false);
                        //     $('#BatchID').append(newOption).trigger('change');
                        // }
                        // if(data.Type == "AddSection"){
                        //     var newOption = new Option(data.Data.SectionName, data.Data.SectionID, false, false);
                        //     $('#SectionID').append(newOption).trigger('change');
                        // }
                        alert(data.Msg);



                        // $('#drpSuppliers').val(data.Data.BatchID).trigger('change');

                        // $("#drpSupplierType").val("").trigger('change');
                        // $("#txtSupplierName, #txtGSTNumber, #txtPANNumber, #txtSupplierBankName, #txtBankAccountNumber, #txtIFSCCode, #txtSupplierAddress, #txtSupplierEMailID, #txtPhoneNumber").val("");
                        
                    }

                    return false;
                }
            });
            
            return false;          
                             
        }  
    });
    jQuery.validator.addMethod("noSpace", function(value, element) { 
        return value == '' || value.trim().length != 0;  
    }, "No space please and don't leave it empty");                
</script>
