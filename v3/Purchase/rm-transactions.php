<?php

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';

$filterYear = "T";
$filterCenter = "T";
$filterSupplier = "T";
$filterPaymentMode = "T";
$buttonBottom = "T";
$buttonPay = "T";

?>

<!doctype html>
<html lang="en">

    <head>
        
        <?php include_once ROOT . 'includes/head.php' ?>

    </head>

    <?php include_once ROOT . '/includes/body-preloader.php' ?>  

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
                        <?php include_once WORKING_MODULE . 'includes/page-filter.php'; ?>   

                        <!-- start page title -->
                        <div class="row">
                            <div class="col-12">

                                <div class="card">
                                    <div class="card-body">

                                        <div id="divDataList">
                                            <!-- -->
                                        </div>

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

        <div class="modal fade" id="ModalRmPayment" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl"> <!-- modal-fullscreen -->
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Bill Payment Mode</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>

                    </div>
                    <form role="form" id="frmPaymentMode" method="post">
                        <div class="modal-body">    
                            
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-md-6 col-lg-3 mb-3">
                                        <label for="drpPaymentMode" class="form-label">Payment Mode</label>
                                        <select class="form-control select2" name="drpPaymentMode" id="drpPaymentMode">			 
                                            <option value="">Select</option>
                                            <option value="B">Cheque</option>
                                            <option value="C">Cash</option>				
                                            <option value="J">Journal</option>				
                                        </select> 	
                                    </div>
                                    <div class="col-md-6 col-lg-3 mb-3">
                                        <label for="txtVoucherNo" class="form-label">Voucher No</label>
                                        <input type="text" class="form-control" id="txtVoucherNo"
                                            name="txtVoucherNo" placeholder=" Enter Voucher No" >
                                    </div>
                                    <div class="col-md-6 col-lg-3 mb-3">
                                        <label for="txtChequeNEFT" class="form-label">Cheque/NEFT.No</label>
                                        <input type="text" class="form-control" id="txtChequeNEFT" disabled
                                            name="txtChequeNEFT" placeholder=" Enter Cheque/NEFT.No" >
                                    </div>
                                    <div class="col-md-6 col-lg-3 mb-3">
                                        <label for="txtAmount" class="form-label">Amount</label>
                                        <input type="text" class="form-control" id="txtAmount" 
                                            name="txtAmount" placeholder=" Enter Amount" >
                                    </div>
                                    <div class="col-md-6 col-lg-3 mb-3">
                                        <label for="txtComments" class="form-label">Comments</label>
                                        <input type="text" class="form-control" id="txtComments"
                                            name="txtComments" placeholder=" Enter Comments" >
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-12 col-lg-12 mt-3">
                                        
                                        <table id="tblPaymentMode" class="table table-hover table-bordered">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>BillNumber</th>	
                                                    <th>BillDate</th>
                                                    <th>SupplierName</th>	
                                                    <th>ActualBill</th>	
                                                    <th>AlreadyPaid</th>
                                                    <th>BalanceValue</th>
                                                    <th>Amount</th>
                                                    <th>Remanigs</th>
                                                    <!-- <th class="text-center">Actions</th> -->
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php // foreach ($List as $item) { ?>
                                                        <tr>
                                                            <td>001</td>	
                                                            <td>Bill Date</td>
                                                            <td>Supplier Name</td>	
                                                            <td>Actual Bill</td>	
                                                            <td>Already Paid</td>
                                                            <td class="tdBalance">250</td>
                                                            <td><input class="form-control txtAmount" id="txtAmount1" name="txtAmount1" value="250" /></td>
                                                            <td><input class="form-control txtRemaning" id="txtRemaning1" name="txtRemaning1" disabled /></td>
                                                        </tr>

                                                        <tr>
                                                            <td>002</td>	
                                                            <td>Bill Date</td>
                                                            <td>Supplier Name</td>	
                                                            <td>Actual Bill</td>	
                                                            <td>Already Paid</td>
                                                            <td class="tdBalance">300</td>
                                                            <td><input class="form-control txtAmount" id="txtAmount2" name="txtAmount2" value="300" /></td>
                                                            <td><input class="form-control txtRemaning" id="txtRemaning2" name="txtRemaning2" disabled /></td>
                                                        </tr>

                                                        <tr>
                                                            <td>003</td>	
                                                            <td>Bill Date</td>
                                                            <td>Supplier Name</td>	
                                                            <td>Actual Bill</td>	
                                                            <td>Already Paid</td>
                                                            <td class="tdBalance">400</td>
                                                            <td><input class="form-control txtAmount" id="txtAmount3" name="txtAmount3" value="400" /></td>
                                                            <td><input class="form-control txtRemaning" id="txtRemaning3" name="txtRemaning3" disabled /></td>
                                                        </tr>
                                                <?php // } ?>
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td>Total</td>	
                                                    <td></td>
                                                    <td></td>	
                                                    <td></td>	
                                                    <td></td>
                                                    <td></td>
                                                    <td class="tdAmount"></td>
                                                    <td class="tdRemaning"></td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <input type="hidden" id="hdnSelectedRow" name="hdnSelectedRow" />
                            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
                            <button type="submit"  name='submit' class="btn btn-primary">Save changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php include_once ROOT . 'includes/common-js.php' ?>

        <!-- Required datatable js -->
        <script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
        <script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>
        
        <?php include_once ROOT . 'includes/app-js.php' ?>

        <script>
           

            var tblPaymentMode;
            function fnShowDataTable() {                
                var table = $('#tblDataTable').DataTable({                    
                    scrollY: "600px",
                    scrollX:true,
                    sScrollXInner: "100%", 
                    paging:false,
                    destroy: true,
                    fixedColumns: true,
                    scrollCollapse:true,
                    columnDefs: [{
                        searchable: false,
                        orderable: false,
                        targets: 0
                    }],
                    bSort : true,
                        "bStateSave": true,
                        "fnStateSave": function (oSettings, oData) {
                        localStorage.setItem('key-datatable', JSON.stringify(oData));
                    },
                    "fnStateLoad": function (oSettings) {
                        return JSON.parse(localStorage.getItem('key-datatable'));
                    }
                });            
            }
            function fnShowDataTablePaymentMode() {                
                tblPaymentMode = $('#tblPaymentMode').DataTable({                    
                    scrollY: "600px",
                    scrollX:true,
                    sScrollXInner: "100%", 
                    paging:false,
                    destroy: true,
                    fixedColumns: true,
                    scrollCollapse:true,
                    // columnDefs: [{
                    //     searchable: false,
                    //     orderable: false,
                    //     targets: 0
                    // }],
                    bSort : true,
                        "bStateSave": true,
                        "fnStateSave": function (oSettings, oData) {
                        localStorage.setItem('key-datatable', JSON.stringify(oData));
                    },
                    "fnStateLoad": function (oSettings) {
                        return JSON.parse(localStorage.getItem('key-datatable'));
                    }
                });            
            }
            function ShowList() {
                $.ajax({
                    url:'Ajax/AjaxRMPayments.php',
                    type:'POST',
                    data:{
                        // PsId: $("#drpPurchaseList").val(),
                        // ArrivalDate: $("#txtArrivalDate").val()
                    },
                    success:function(data){
                        $("#divDataList").html(data);
                        fnShowDataTable();
                        return false;
                    }
                });
            }
            $(document).ready(function(){
                ShowList();
            });

            $("#btnSearch").on("click", function(){
                ShowList();
            })

            
            function checkOptions(unCheckVal) {
                var selectedOptions = new Array();
                $("input.rowCheck:checked").each(function () {                    
                    selectedOptions.push(this.value);
                });

                var hdnSelectedVal = $('#hdnSelectedRow').val(); 
                if(hdnSelectedVal != "") {
                    var sArray = hdnSelectedVal.split(","); 
                    var combinedArray = sArray.concat(selectedOptions);

                    var uniqueArray = [];
                    $.each(combinedArray, function(index, value) {
                        if ($.inArray(value, uniqueArray) === -1) {
                            uniqueArray.push(value);
                        }
                    });
                    
                    if(unCheckVal) {
                        uniqueArray.splice(uniqueArray.indexOf(unCheckVal), 1);
                    }

                    var joinedArray = uniqueArray.join(",");

                    $('#hdnSelectedRow').val(joinedArray);
                }else {
                    $('#hdnSelectedRow').val(selectedOptions.join(","));	
                }

                // $('#hdnSelectedRow').val(joinedArray);
                if(selectedOptions.length > 0) {
                    $("#btnPay").prop("disabled", false);
                }else{
                    $("#btnPay").prop("disabled", true);
                    $('#hdnSelectedRow').val("");
                }
            }

            $(document).on("click", "input.rowCheck", function () {

                console.log($(this).val());
                var isChecked = $(this).prop('checked');

                if (isChecked) {
                    checkOptions();
                } else {
                    checkOptions($(this).val());
                }
                
                $("#checkall").prop("checked", false);	
            });

            $(document).on("click", "#checkall", function () {
                console.log("check all");
                if (this.checked) {
                    $(".rowCheck").prop("checked", true);		
                    checkOptions();
                } else {
                    $(".rowCheck").prop("checked", false);		
                    checkOptions();
                }	
            //    $(".rowCheck").prop('checked', $(this).prop('checked'));
            });

            $(document).on("click", "#btnPay", function(){
                
                fnShowDataTablePaymentMode();
                // tblPaymentMode.columns.adjust().draw();
            });

            $("#drpPaymentMode").on("change", function(){
                var value = $(this).val();
                console.log(value)
                if(value == "B") {
                    $('#txtChequeNEFT').val("").prop("disabled", false);
                }else{
                    $('#txtChequeNEFT').val("").prop("disabled", true);
                }   
            })
            
            $(document).on('shown.bs.modal', '#ModalRmPayment', function () {
                tblPaymentMode.columns.adjust().draw();
                
                $(".txtAmount").each(function() {
                    // console.log($(this));
                    var tdBalance = parseInt($(this).closest("tr").find(".tdBalance").text());   
                    var amt = parseInt($(this).val());

                    if(amt > 0) {
                        var remaning = tdBalance - amt;
                        if( tdBalance > amt) {
                            $(this).closest("tr").find(".txtRemaning").val(remaning); //txtRemaning
                        }else{
                            $(this).closest("tr").find(".txtRemaning").val(0); //txtRemaning
                        }
                    }

                });

                fnPaymentModeCalucation();
            });
            
            function fnPaymentModeCalucation() {              
                                  
                var sumAmount = 0;
                var sumRemaning = 0;
                $(".txtAmount").each(function() {    
                    if (!isNaN(this.value) && this.value.length != 0) {
                        sumAmount += parseFloat(this.value);
                    }    
                });

                $(".txtRemaning").each(function() {          
                    if (!isNaN(this.value) && this.value.length != 0) {
                        sumRemaning += parseFloat(this.value);
                    }    
                });

                $(".dataTables_scrollFoot .tdAmount").text(sumAmount);
                $(".dataTables_scrollFoot .tdRemaning").text(sumRemaning);
            }
            $(document).on("keyup", ".txtAmount", function() {
                var amt = parseInt($(this).val());
                var tdBalance = parseInt($(this).closest("tr").find(".tdBalance").text());   
                
                // if(amt > 0) {
                    var remaning = tdBalance - amt;
                    if( tdBalance > amt) {
                        $(this).closest("tr").find(".txtRemaning").val(remaning); //txtRemaning
                    }else{
                        $(this).closest("tr").find(".txtRemaning").val(0); //txtRemaning
                    }
                // }else{
                //      $(this).closest("tr").find(".txtRemaning").val(0); //txtRemaning
                // }

                fnPaymentModeCalucation();
            });

            
        </script>

    </body>
</html>
