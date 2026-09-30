<?php
    include ($_SERVER['DOCUMENT_ROOT']."/includes/config.php");
    include ($_SERVER['DOCUMENT_ROOT']."/SalesNew/Constants.php");

    $PageTitleFolder="Shipping";
    $PageTitle="Negotiation";
    $ContinueSeries = false;

    if(isset($_POST['submit'])){
      $invoiceNo = $_POST["invoiceNo"];
      $bankRefNo = $_POST["bankRefNo"];
      $buyer = $_POST["buyer"];
      $negotiationDate = $_POST["negotiationDate"];
      $invoiceDate = $_POST["invoiceDate"];
      $invValue = $_POST["invValue"];
      $negotiationRate = $_POST["negotiationRate"];
      $quantity = $_POST["quantity"];
      $shortFall = $_POST["shortFall"];
      $paymentTerms = $_POST["paymentTerms"];
      $vesselName = $_POST["vesselName"];
      $shippingOnboardDate = $_POST["shippingOnboardDate"];
      $bLDate = $_POST["bLDate"];
      $dueDate = $_POST["dueDate"];
      $eTAPOD = $_POST["eTAPOD"];
      $bankDueDate = $_POST["bankDueDate"];

      $radioOptions = $_POST["radioOptions"];

      $drpFCNo = $_POST["drpFCNo"];
      $fcRate = $_POST["fcRate"];
      $fcUtilizedValue = $_POST["fcUtilizedValue"];
      $fcUtilizedDate = $_POST["fcUtilizedDate"];
      $fcBalance = $_POST["fcBalance"];
      $fcValue = $_POST["fcValue"];
      $fcRealisedValue1 = $_POST["fcRealisedValue1"];
      $fcRealisedValue2 = $_POST["fcRealisedValue2"];

      $srUtilizedValue = $_POST["srUtilizedValue"];
      $srUtilizedDate = $_POST["srUtilizedDate"];
      $srRate = $_POST["srRate"];
      $srRate1 = $_POST["srRate1"];
      $srRealisedValue1 = $_POST["srRealisedValue1"];
      $srRealisedValue2 = $_POST["srRealisedValue2"];

      $drpPCFCRef = $_POST["drpPCFCRef"];
      $pcfcRate = $_POST["pcfcRate"];
      $pcfcUtilizedValue = $_POST["pcfcUtilizedValue"];
      $pcfcUtilizedDate = $_POST["pcfcUtilizedDate"];
      $pcfcBalance = $_POST["pcfcBalance"];
      $pcfcValue = $_POST["pcfcValue"];
      $pcfcRealisedValue1 = $_POST["pcfcRealisedValue1"];
      $pcfcRealisedValue2 = $_POST["pcfcRealisedValue2"];

    }
?>

<!DOCTYPE html>
<html lang="en">
    <head>        
            <?php include ($IncludesPath."title-meta.php"); ?>
		 	<?php  include ($IncludesPath."DbLinks.php");?>
            <?php include ($IncludesPath."head-css.php");?>
            <style>
				select.error + .select2-container, input.error, textarea.error {
					border: 1px solid red;
				}
                input[type=text]:disabled, input[type=date]:disabled{
                    background: #F1F1F1;
                }
                #key-datatable_wrapper{
                    width: 100%;
                }
			</style>
    </head>
    <body data-sidebar="dark">	
	 <?php
        include ($IncludesPath."PreLoader.php"); ?>

        <!-- Begin page -->
        <div id="layout-wrapper">
        <?php
            include ($IncludesPath."topbar.php");
            include ($IncludesPath."sidebar.php"); 
        ?>
 <!-- ============================================================== -->
            <!-- Start right Content here -->
            <!-- ============================================================== -->
            <div class="main-content">

                <div class="page-content">
                    <div class="container-fluid">
                        <?php include ($IncludesPath."page-title.php"); ?>	
                        <div class="row">

                            <form role="form" method="post" enctype="multipart/form-data" id="frmShippingBilling">
                            <div class="col-12">
                                <div class="card">								
                                        <div class="card-body">									
                                            <div class="row">	

                                                <div class="form-group col-lg-6" >
                                                    <label for="invoiceNo">Invoice No.<span style="color:red">*</span></label>
                                                    <input type="text" class="form-control" name='invoiceNo' id="invoiceNo" placeholder="Enter Invoice No"  value="">
                                                </div>

                                                <div class="form-group col-lg-6" >
                                                    <label for="bankRefNo">Bank Ref No.</label>
                                                    <input type="text" class="form-control" name='bankRefNo' id="bankRefNo" placeholder="Enter Bank Ref No."  value="">
                                                </div>

                                                <div class="form-group col-lg-6" >
                                                    <label for="buyer">Buyer</label>
                                                    <input type="text" class="form-control" name='buyer' id="buyer" placeholder="Enter Buyer" disabled value="">
                                                </div>

                                                <div class="form-group col-lg-6" >
                                                    <label for="negotiationDate">Negotiation Date</label>
                                                    <input type="date" class="form-control" name='negotiationDate' id="negotiationDate" placeholder="Enter Negotiation Date"  value="">
                                                </div>

                                                <div class="form-group col-lg-6" >
                                                    <label for="invoiceDate">Invoice Date</label>
                                                    <input type="date" class="form-control" name='invoiceDate' id="invoiceDate" placeholder="Invoice Date" disabled value="">
                                                </div>

                                                <div class="form-group col-lg-6" >
                                                    &nbsp;
                                                </div>

                                                <div class="form-group col-lg-6" >
                                                    <label for="invValue">Inv Value</label>
                                                    <input type="text" class="form-control" name='invValue' id="invValue" placeholder="Inv Value" disabled value="">
                                                </div>

                                                <div class="form-group col-lg-6" >
                                                    <label for="negotiationRate">Negotiation Rate</label>
                                                    <input type="text" class="form-control" name='negotiationRate' id="negotiationRate" placeholder="Enter Negotiation Rate"  value="">
                                                </div>

                                                <div class="form-group col-lg-6" >
                                                    <label for="quantity">Quantity</label>
                                                    <input type="text" class="form-control" name='quantity' id="quantity" placeholder="Enter Quantity" disabled value="">
                                                </div>

                                                <div class="form-group col-lg-6" >
                                                    <label for="shortFall">ShortFall</label>
                                                    <input type="text" class="form-control" name='shortFall' id="shortFall" placeholder="Enter ShortFall" disabled value="">
                                                </div>

                                                <div class="form-group col-lg-6" >
                                                    <label for="paymentTerms">Payment Terms</label>
                                                    <input type="text" class="form-control" name='paymentTerms' id="paymentTerms" placeholder="Enter Payment Terms" disabled value="">
                                                </div>

                                                <div class="form-group col-lg-6" >
                                                    <label for="vesselName">Vessel Name</label>
                                                    <input type="text" class="form-control" name='vesselName' id="vesselName" placeholder="Enter Vessel Name"  value="">
                                                </div>

                                                <div class="form-group col-lg-6" >
                                                    <label for="shippingOnboardDate">Shipping Onboard Date</label>
                                                    <input type="date" class="form-control" name='shippingOnboardDate' id="shippingOnboardDate" placeholder="Enter Shipping Onboard Date" disabled value="">
                                                </div>

                                                <div class="form-group col-lg-6" >
                                                    <label for="bLDate">BL Date</label>
                                                    <input type="date" class="form-control" name='bLDate' id="bLDate" placeholder="Enter BL Date"  value="">
                                                </div>

                                                <div class="form-group col-lg-6" >
                                                    <label for="dueDate">Due Date</label>
                                                    <input type="date" class="form-control" name='dueDate' id="dueDate" placeholder="Enter Due Date"  value="">
                                                </div>

                                                <div class="form-group col-lg-6" >
                                                    <label for="eTAPOD">ETA POD</label>
                                                    <input type="text" class="form-control" name='eTAPOD' id="eTAPOD" placeholder="Enter ETA POD"  value="">
                                                </div>

                                                <div class="form-group col-lg-6" >
                                                    <label for="bankDueDate">Bank Due Date</label>
                                                    <input type="date" class="form-control" name='bankDueDate' id="bankDueDate" placeholder="Enter Bank Due Date"  value="">
                                                </div>

                                            </div>		
                                            
                                            

                                        </div>				
                                    
                                <!-- end col -->
                                </div> <!-- end row -->
	                        </div> <!-- end card body-->

                            <div class="col-12">
                                <div class="card">								
                                  
                                        <div class="card-body">									
                                            <div class="row">	
                                                <div class="col-12 text-left">

                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" id="forwardContract" checked="checked" name="radioOptions" value="FC" />
                                                        <label class="form-check-label" for="forwardContract">Forward Contract</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" id="spotRate" name="radioOptions" value="SR" />
                                                        <label class="form-check-label" for="spotRate">Spot Rate</label>
                                                    </div>
                                                    <div class="form-check form-check-inline">
                                                        <input class="form-check-input" type="radio" id="pcfc" name="radioOptions" value="PCFC" />
                                                        <label class="form-check-label" for="pcfc">PCFC</label>
                                                    </div>
                                                    <br/><br/>
                                                </div>

                                            </div>	

                                            <!-- Section Forward Contract -->
                                            <div class="row divForwardContract">                                                

                                                    <div class="form-group col-lg-6" >
                                                        <label for="drpFCNo">Forward Contract No.</label>
                                                        <select required class="form-control select2" name="drpFCNo" id="drpFCNo">
                                                            <option value=""> All </Option>
                                                            <option value="1"> Option 1 </Option>
                                                            <option value="2"> Option 2 </Option>
                                                        <select>  
                                                    </div>

                                                    <div class="form-group col-lg-6" >
                                                        <label for="fcRate">Rate</label>
                                                        <input type="text" class="form-control" name='fcRate' id="fcRate" placeholder="Enter Rate" disabled  value="">
                                                    </div>

                                                    <div class="form-group col-lg-6" >
                                                        <div class="row">
                                                            <div class="form-group col-lg-6 col-md-6 col-sm-6" >
                                                                <label for="fcUtilizedValue">Utilized Value $</label>
                                                                <input type="text" class="form-control" name='fcUtilizedValue' id="fcUtilizedValue" placeholder="Enter Utilized Value $"  value="">
                                                            </div>
                                                            <div class="form-group col-lg-6  col-md-6 col-sm-6" >
                                                                <label for="fcUtilizedDate">Utilized Date</label>
                                                                <input type="date" class="form-control" name='fcUtilizedDate' id="fcUtilizedDate" placeholder="Enter Utilized Date" value="">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group col-lg-6" >
                                                        <div class="row">
                                                            <div class="form-group col-lg-6 col-md-6 col-sm-6" >
                                                                <label for="fcBalance">Balance</label>
                                                                <input type="text" class="form-control" name='fcBalance' id="fcBalance" placeholder="Enter Balance" disabled  value="">
                                                            </div>
                                                            <div class="form-group col-lg-6 col-md-6 col-sm-6" >
                                                                <label for="fcValue">Value INR</label>
                                                                <input type="text" class="form-control" name='fcValue' id="fcValue" placeholder="Enter Value INR" disabled value="">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-12">
                                                        <div class="row">
                                                            <div class="col">&nbsp;</div>
                                                            <div class="col"><label style="margin-top:10px">Total Realised Value</label></div>                                                            
                                                            <div class="col">
                                                                <input type="text" class="form-control" name="fcRealisedValue1" id="fcRealisedValue1" disabled />
                                                            </div>
                                                            <div class="col">
                                                                <input type="text" class="form-control" name="fcRealisedValue2" id="fcRealisedValue2" disabled />
                                                            </div>
                                                            <div class="col">&nbsp;</div>
                                                        </div>
                                                    </div>
                                            </div>
                                            <!-- END Forward Contract -->

                                            <!-- Section Spot Rate -->
                                            <div class="row divSpotRate" style="display:none">                                                

                                                    <div class="form-group col-lg-6" >
                                                        <div class="row">
                                                            <div class="form-group col-lg-6 col-md-6 col-sm-6" >
                                                                <label for="srUtilizedValue">Utilized Value $</label>
                                                                <input type="text" class="form-control" name='srUtilizedValue' id="srUtilizedValue" placeholder="Enter Utilized Value $"  value="">
                                                            </div>
                                                            <div class="form-group col-lg-6  col-md-6 col-sm-6" >
                                                                <label for="srUtilizedDate">Utilized Date</label>
                                                                <input type="date" class="form-control" name='srUtilizedDate' id="srUtilizedDate" placeholder="Enter Utilized Date" value="">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group col-lg-6" >
                                                        <label for="srRate">Rate</label>
                                                        <input type="text" class="form-control" name='srRate' id="srRate" placeholder="Enter Rate"  value="">
                                                    </div>

                                                    <div class="form-group col-lg-6" >&nbsp;</div>

                                                    <div class="form-group col-lg-6" >
                                                        <!-- <label for="fcRate">Rate</label> -->
                                                        <input type="text" class="form-control" name='srRate1' id="srRate1" placeholder="Enter Rate" disabled  value="">
                                                    </div>

                                                    

                                                    

                                                    <div class="col-lg-12">
                                                        <div class="row">
                                                            <div class="col">&nbsp;</div>
                                                            <div class="col"><label style="margin-top:10px">Total Realised Value</label></div>                                                            
                                                            <div class="col">
                                                                <input type="text" class="form-control" name="srRealisedValue1" id="srRealisedValue1" disabled />
                                                            </div>
                                                            <div class="col">
                                                                <input type="text" class="form-control" name="srRealisedValue2" id="srRealisedValue2" disabled />
                                                            </div>
                                                            <div class="col">&nbsp;</div>
                                                        </div>
                                                    </div>
                                            </div>
                                            <!-- END Spot Rate -->
                                            
                                            <!-- Section PCFC -->
                                            <div class="row divPCFC" style="display:none">                                                

                                                    <div class="form-group col-lg-6" >
                                                        <label for="drpPCFCRef">PCFC Ref.</label>
                                                        <select required class="form-control select2" name="drpPCFCRef" id="drpPCFCRef">
                                                            <option value=""> All </Option>
                                                            <option value="1"> Option 1 </Option>
                                                            <option value="2"> Option 2 </Option>
                                                        <select>  
                                                    </div>

                                                    <div class="form-group col-lg-6" >
                                                        <label for="pcfcRate">Rate</label>
                                                        <input type="text" class="form-control" name='pcfcRate' id="pcfcRate" placeholder="Enter Rate" disabled  value="">
                                                    </div>

                                                    <div class="form-group col-lg-6" >
                                                        <div class="row">
                                                            <div class="form-group col-lg-6 col-md-6 col-sm-6" >
                                                                <label for="pcfcUtilizedValue">Utilized Value $</label>
                                                                <input type="text" class="form-control" name='pcfcUtilizedValue' id="pcfcUtilizedValue" placeholder="Enter Utilized Value $"  value="">
                                                            </div>
                                                            <div class="form-group col-lg-6  col-md-6 col-sm-6" >
                                                                <label for="pcfcUtilizedDate">Utilized Date</label>
                                                                <input type="date" class="form-control" name='pcfcUtilizedDate' id="pcfcUtilizedDate" placeholder="Enter Utilized Date" value="">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="form-group col-lg-6" >
                                                        <div class="row">
                                                            <div class="form-group col-lg-6 col-md-6 col-sm-6" >
                                                                <label for="pcfcBalance">Balance</label>
                                                                <input type="text" class="form-control" name='pcfcBalance' id="pcfcBalance" placeholder="Enter Balance" disabled  value="">
                                                            </div>
                                                            <div class="form-group col-lg-6 col-md-6 col-sm-6" >
                                                                <label for="pcfcValue">Value INR</label>
                                                                <input type="text" class="form-control" name='pcfcValue' id="pcfcValue" placeholder="Enter Value INR" disabled value="">
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-lg-12">
                                                        <div class="row">
                                                            <div class="col">&nbsp;</div>
                                                            <div class="col"><label style="margin-top:10px">Total Realised Value</label></div>                                                            
                                                            <div class="col">
                                                                <input type="text" class="form-control" name="pcfcRealisedValue1" id="pcfcRealisedValue1" disabled />
                                                            </div>
                                                            <div class="col">
                                                                <input type="text" class="form-control" name="pcfcRealisedValue2" id="pcfcRealisedValue2" disabled />
                                                            </div>
                                                            <div class="col">&nbsp;</div>
                                                        </div>
                                                    </div>
                                            </div>
                                            <!-- END Section 1 -->


                                            
                                            <div class="row">	
                                                <div class="col-12 text-right">
                                                    <!-- <button hidden type="submit"  name='submit' id="Create" class="btn btn-success">Next</button> -->
                                                    <!--<a target="" href="PIList.php" class="btn btn-danger"> Close</a>-->
                                                    <button type="button" id="close"  name="close"  class="btn btn-danger">Close </button>
                                                    <button type="submit"  id="CreatePI" class="btn btn-success">Save</button>
                                                </div>
                                            </div>

                                        </div>				
                                        <!-- end col -->
                                    </div> <!-- end row -->
                                </div> <!-- end card body-->
                            </form>			

                        </div> <!-- end card -->
                    </div><!-- end col-->
                </div>
                <!-- end row-->
                        
            </div> <!-- container-fluid -->
        </div>
        <!-- End Page-content -->

        <?php include ($IncludesPath."footer.php");?>
                
    </div>
            <!-- end main content-->

</div>
        <!-- END layout-wrapper -->        

    <?php include ($IncludesPath."vendor-scripts.php");?>
    <?php include ($IncludesPath."NewScripts.php");?>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js" type="text/javascript"></script>
		
    <script>
        $('input[type=radio][name=radioOptions]').change(function() {
            var selectedOption = $(this).val();
            if(selectedOption == "FC") {
                $(".divForwardContract").show();
                $(".divSpotRate").hide();
                $(".divPCFC").hide();
            }else if(selectedOption == "SR") {
                $(".divSpotRate").show();
                $(".divForwardContract").hide();
                $(".divPCFC").hide();
            }else{
                $(".divPCFC").show();
                $(".divForwardContract").hide();
                $(".divSpotRate").hide();
            }
        });

        </script>
		<script>
			jQuery.validator.addMethod('selectcheck', function (value) {
				return (value != '0' || value != '');
			}, "year required");
			
            jQuery.validator.addMethod("noSpace", function(value, element) { 
				return value == '' || value.trim().length != 0;  
			}, "No space please and don't leave it empty");

			$(document).ready(function(){
				$("#frmShippingBilling").validate({
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
					rules: {
						invoiceNo: { required: true, noSpace: true },
                        bankRefNo: { required: true, noSpace: true },
                        buyer: { required: true, noSpace: true },
                        negotiationDate: { required: true, noSpace: true },
                        invoiceDate: { required: true, noSpace: true },
                        invValue: { required: true, noSpace: true },
                        negotiationRate: { required: true, noSpace: true },
                        quantity: { required: true, noSpace: true },
                        shortFall: { required: true, noSpace: true },
                        paymentTerms: { required: true, noSpace: true },
                        vesselName: { required: true, noSpace: true },
                        shippingOnboardDate: { required: true, noSpace: true },
                        bLDate: { required: true, noSpace: true },
                        dueDate: { required: true, noSpace: true },
                        eTAPOD: { required: true, noSpace: true },
                        bankDueDate: { required: true, noSpace: true },

                        drpFCNo: { required: true, selectcheck: true },
                        fcRate: { required: true, noSpace: true },
                        fcUtilizedValue: { required: true, noSpace: true },
                        fcUtilizedDate: { required: true, noSpace: true },
                        fcBalance: { required: true, noSpace: true },
                        fcValue: { required: true, noSpace: true },
                        fcRealisedValue1: { required: true, noSpace: true },
                        fcRealisedValue2: { required: true, noSpace: true },

                        srUtilizedValue: { required: true, noSpace: true },
                        srUtilizedDate: { required: true, noSpace: true },
                        srRate: { required: true, noSpace: true },
                        srRate1: { required: true, noSpace: true },
                        srRealisedValue1: { required: true, noSpace: true },
                        srRealisedValue2: { required: true, noSpace: true },

                        drpPCFCRef: { required: true, selectcheck: true },
                        pcfcRate: { required: true, noSpace: true },
                        pcfcUtilizedValue: { required: true, noSpace: true },
                        pcfcUtilizedDate: { required: true, noSpace: true },
                        pcfcBalance: { required: true, noSpace: true },
                        pcfcValue: { required: true, noSpace: true },
                        pcfcRealisedValue1: { required: true, noSpace: true },
                        pcfcRealisedValue2: { required: true, noSpace: true }
					},
					errorPlacement: function () {
						return false;  // suppresses error message text
					},
					submitHandler: function () {
						return true;
					}
				});
			});
		</script>

    </body>
</html>

			

