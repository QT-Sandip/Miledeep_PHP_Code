<?php
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';
?>
<!doctype html>
<html lang="en">

<head>
    <?php include_once ROOT . '/includes/head.php' ?>
</head>
<?php include_once ROOT . '/includes/body-preloader.php' ?>
<!-- Begin page -->
<div id="layout-wrapper">
<?php include_once ROOT . '/includes/menu.php' ?>
 <div class="main-content">

        <div class="page-content">
            <div class="container-fluid">

                <?php include_once ROOT . '/includes/page-header.php' ?>

                <!-- start page title -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                     <div class="row align-items-center">
                                                <div class="col-md-3 mt-3 mb-2">
                                                    <label for="drpPoDate">Select PO Date<span style="color:red">*</span></label>
                                                    <input type="date" id="drpPoDate" class="form-control" value="<?php echo date('Y-m-d', strtotime("2025-04-10")); ?>" max="2025-04-10"/>
                                                </div>
                                                <div class="col-md-3 mt-3 mb-2">
                                                    <label for="drpPoNumbers">Select PO Numbers<span style="color:red">*</span></label>
                                                    <select class="drpSelect form-control" name="drpPoNumbers[]" id="drpPoNumbers" multiple>
                                                    <option value="" disabled>Select</option>    
                                                    </select>
                                                    <input type="hidden" name="hdnPoNumber" id="hdnPoNumber" value="">
                                                </div>
                                                <div class="col-md-3 mt-3 mb-2">
                                                    <label for="drpReciprocalTariff">Reciprocal Tariffs<span style="color:red">*</span></label>
                                                    <select class="drpSelect form-control" name="drpReciprocalTariff" id="drpReciprocalTariff">
                                                        <option value="">Select</option>
                                                        <option value="1">YES</option>
                                                        <option value="0">NO</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3 mt-3 mb-2">
                                                    <label for="drpRevicedPo">Revised PO<span style="color:red">*</span></label>
                                                    <select class="drpSelect form-control" name="drpRevicedPo" id="drpRevicedPo">
                                                        <option value="">Select</option>
                                                        <option value="1">YES</option>
                                                        <option value="0">NO</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-3 mt-4 mb-2" style="top:8px;">
                                                    <button id="btnSubmit" type="button"
                                                        class="btn btn-primary waves-effect waves-light">
                                                        Apply
                                                    </button>
                                                    <button id="btnUpdate" type="button"
                                                        class="btn btn-primary waves-effect waves-light">
                                                        Update
                                                    </button>
                                                    <button id="btnReset" type="button"
                                                        class="btn btn-secondary waves-effect waves-light">
                                                        Reset
                                                    </button>
                                                </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
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
        <?php include_once ROOT . '/includes/footer.php' ?>
    </div>
    <!-- end main content-->
     <?php include_once ROOT . '/includes/common-js.php' ?>

    <!-- Required datatable js -->
    <script src="<?php echo WORKING_URL ?>assets/libs/datatables.net/js/jquery.dataTables.min.js"></script>
    <script src="<?php echo WORKING_URL ?>assets/libs/datatables.net-bs4/js/dataTables.bootstrap4.min.js"></script>

    <?php include_once ROOT . '/includes/app-js.php' ?>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js" type="text/javascript"></script>


    <script>
        
        function resetForm(fullReset=false) {
            if(fullReset){
                $("#hdnPoNumber").val("");
                $("#drpPoDate").val("").trigger('change');
            }
            $("#drpPoNumbers").val([]).trigger('change'); // Use trigger to update the select
            $("#drpReciprocalTariff").val("").trigger('change');;
            $("#drpRevicedPo").val("").trigger('change');
            $("#btnUpdate").hide();
            $("#btnSubmit").show();
        }
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
        
        function fnSuccess(data) {
            $("#divDataList").html(data);
            fnShowDataTable();

            $('html, body').animate({
                scrollTop: $("#divDataList").offset().top - 50
            }, 10);
        }

        function ShowList() {
            var date = $("#drpPoDate").val();
            $.ajax({
                url: 'Ajax/AjaxReciprocalTariffs.php',
                type: 'POST',
               data: {
                    methodType: 'getReciprocalTariff',
                    PoDate: date,
                },
                success: function(data) {
                    
                    fnSuccess(data);
                    // console.log(data)
                }
            });
        }
        $(document).ready(function() {
            $('#btnUpdate').hide();
        
        $("#btnSubmit").click(function(e) {
            e.preventDefault();
            var date = $("#drpPoDate").val();
            var poIDs = $("#drpPoNumbers").val();
            var reciprocalTariff = $("#drpReciprocalTariff").val();
            var revisedPo = $("#drpRevicedPo").val();
            var poIDsStr = poIDs.join(',');
            if (date == "" || poIDsStr == "" || reciprocalTariff == "" || revisedPo == "") {
                alert("Please fill all the fields");
                return false;
            }

            $.ajax({
                url: 'Ajax/AjaxReciprocalTariffs.php',
                type: 'POST',
                data: {
                    methodType: 'postReciprocalTariff',
                    PoDate: date,
                    PoIDs: poIDsStr,
                    ReciprocalTariff: reciprocalTariff,
                    RevisedPo: revisedPo
                },
                success: function(data) {
                    resetForm();
                    // console.log(data)
                    ShowList();
                }
            });
        });
        $("#btnUpdate").click(function(e) {
            e.preventDefault();
            var poDate = $("#drpPoDate").val();
            var poID = $("#hdnPoNumber").val();
            var reciprocalTariff = $("#drpReciprocalTariff").val();
            var revisedPo = $("#drpRevicedPo").val();
            console.log("UPDATE ",poDate,poID, reciprocalTariff, revisedPo);
            if (poDate == "" || poID == "" || reciprocalTariff == "" || revisedPo == "") {
                alert("Please fill all the fields");
                return false;
            }

            $.ajax({
                    url: 'Ajax/AjaxReciprocalTariffs.php',
                    type: 'POST',
                    data: {
                        methodType: 'updateReciprocalTariff',
                        PoDate: poDate,
                        PoIDs: poID,
                        ReciprocalTariff: reciprocalTariff,
                        RevisedPo: revisedPo
                    },
                    success: function(data) {
                        resetForm();
                        ShowList();
                    }
                });
        });
        
        $("#btnReset").click(function(e) {
            e.preventDefault();
           resetForm(true)
        });
        $("#drpPoDate").change(function() {
            resetForm();
            console.log("Date Changed")
            var date = $(this).val();
            $.ajax({
                url: 'Ajax/AjaxReciprocalTariffs.php',
                type: 'POST',
                data: {
                    methodType: 'getPoNumbers',
                    PoDate: date
                },
                success: function(response) {
                    if(response == "" || response == null || response == undefined || response == "NULL"){ 
                        alert("No PO Numbers Found");
                        return false;
                    }

                    var responseData = JSON.parse(response);
                    if (responseData.Error) {
                        alert(responseData.Msg);
                        return false;
                    }
                    
                    var options = '<option value="" disabled>Select</option>';
                    $.each(responseData.Data, function(index, item) {
                        options += '<option value="' + item.PoID + '">' + item.PoID + ' - ' + item.PoNo + '</option>';
                    });
                    $("#drpPoNumbers").html(options);
                    
                }
            });
            ShowList();
        });
        $(document).on("click", ".btnEditReciprocalTariff",function() {
            if (confirm("Are you sure you want to edit this record?")) {
            var poID = $(this).data("poid");
            var poDate = $(this).data("podate");
            var reciprocalTariff = $(this).data("reciprocaltariff");
            var revisedPo = $(this).data("revisedpo");
            console.log("EDIT ",poDate, poID, reciprocalTariff, revisedPo);
            // Populate the edit form fields with the data
            $("#drpPoDate").val(poDate).trigger('change');;
            $("#drpReciprocalTariff").val(reciprocalTariff).trigger('change');;
            $("#drpRevicedPo").val(revisedPo).trigger('change');
            $("#drpPoNumbers").val([poID]).trigger('change'); // Use trigger to update the select
            $("#hdnPoNumber").val(poID);
            $("#btnSubmit").hide();
            $("#btnUpdate").show();
            }
            // Show the edit modal
            
        });
        $(document).on("click", ".btnDeleteReciprocalTariff",function() {
            var poID = $(this).data("poid");
            var poDate = $("#drpPoDate").val();
            if (confirm("Are you sure you want to delete this record?")) {
                $.ajax({
                    url: 'Ajax/AjaxReciprocalTariffs.php',
                    type: 'POST',
                    data: {
                        methodType: 'deleteReciprocalTariff',
                        PoIDs: poID,
                        PoDate: poDate
                    },
                    success: function(data) {
                        ShowList();
                    }
                });
            }
        });
});
    </script>

    </body>

</html>