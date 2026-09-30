<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);


$PageTitle = "Freight Rates Report";
$PageTitleFolder = "Masters";
// $BranchID=$_SESSION['BranchID']; 
// $AccessType=$_SESSION['AccessType']; 


include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");

?>
<!DOCTYPE html>

<html lang="en">


<link href="<?php echo $href ?>" rel="stylesheet" type="text/css" />

<head>

    <?php include($IncludesPath . "title-meta.php"); ?>
    <?php include($IncludesPath . "DbLinks.php"); ?>
    <?php include($IncludesPath . "head-css.php"); ?>
    <style>
        input.error {
                border: 1px solid red;
            }
        .closePop:hover{
            cursor: pointer;
        }
        #btnSearch{
            margin-top: 12px;
        }
        .select2-container .select2-selection--multiple .select2-selection__choice,
        .select2-selection__choice__remove {
            background-color: #1cbb8c !important;
            border: 1px solid #1cbb8c !important;
            color: #FFFFFF !important;
        }
    </style>
</head>

<body data-sidebar="dark">
    <?php  include ($IncludesPath."PreLoader.php"); ?>

    <!-- Begin page -->
    <div id="layout-wrapper">

        <?php
        include($IncludesPath . "topbar.php");
        include($IncludesPath . "sidebar.php");
        $countryIdsQuery = "WITH RECURSIVE 
                            split_Country AS (
                                SELECT
                                    id,
                                    TRIM(SUBSTRING_INDEX(Country, ',', 1)) AS `Country`,
                                    SUBSTRING(Country, LENGTH(SUBSTRING_INDEX(Country, ',', 1)) + 2) AS remaining
                                FROM ScrmFreightRates
                                WHERE `Country` != 0 

                                UNION ALL

                                SELECT
                                    id,
                                    TRIM(SUBSTRING_INDEX(remaining, ',', 1)),
                                    SUBSTRING(remaining, LENGTH(SUBSTRING_INDEX(remaining, ',', 1)) + 2)
                                FROM split_Country
                                WHERE remaining <> ''
                            )
                            SELECT `Country`
                            FROM split_Country 
                            GROUP BY `Country`";
        $SqlCountries = "SELECT `id`,`name` FROM `ScrmCountriesList`  WHERE id IN($countryIdsQuery)";
        $StmtCountries = $iconn->query($SqlCountries);
        
        ?>
        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <?php include($IncludesPath . "page-title.php"); ?>
                    <!-- search row start -->
                    <div class="row rowFilter">
                        <div class="col-md-12">
                            <div class="card">
                                <div class="card-body">
                                    <div class="row">   
                                        <div class="form-group col-sm-3">
                                            <label for="Country">Country<span style="color:red"></span></label>
                                            <select  class="form-control select2" name="Country[]"
                                                id="Country" multiple>
                                                <option value="" disabled> Select </Option>
                                                <?php while ($RowCountries = $StmtCountries->fetch_array()) { ?>
                                                    <option <?php if (157 == $RowCountries['id']) { ?>
                                                            selected="selected" <?php } ?>
                                                        value="<?php echo $RowCountries['id']; ?>">
                                                        <?php echo $RowCountries['name']; ?>
                                                    </option>
                                                <?php } ?>

                                            </select>
                                        </div>                                 
                                        <div class="col col-md-3 mb-2">
                                            <label> From Date</label>
                                            <input type="date" value="" class="form-control" name="fromDate" id="fromDate">
                                        </div>
                                        <div class="col col-md-3 mb-2">
                                            <label> To Date</label>
                                            <input type="date" value="" class="form-control" name="toDate" id="toDate">
                                        </div>
                                        <div class="text-center mt-3">
                                            <button id="btnSearch" align="left" type="button" class="btn btn-primary waves-effect waves-light">
                                                Search <i class="ri-arrow-right-line align-middle ml-2"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- search row ends -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body" id="ShowDataTable">
                                  
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
    <?php include($IncludesPath . "NewScripts.php"); ?>
    <?php include($IncludesPath . "ShowDataTable.php"); ?>

 <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"
        type="text/javascript"></script>
</body>

</html>

<script>
    /* $(document).ajaxStart(function(){
        $("#wait").css("display", "block");
    });
    $(document).ajaxComplete(function(){
        $("#wait").css("display", "none");
    }); */
    $('#fromDate,#toDate').on('change', function () {
        let fromDate = $('#fromDate').val();
        let toDate = $('#toDate').val();
        let from = new Date(fromDate);
        let to = new Date(toDate);

        let diff = (to - from) / (1000 * 60 * 60 * 24);

        if (diff > 30) {
            alert("Maximum 30 days allowed.");
            $(this).val("").trigger('change');
        }
    });
    $(document).ready(function () {
        $('.select2').select2({
            width: '100%',
            maximumSelectionLength: 5
        });
        $('#btnSearch').click(function(){
            var fromDate = $('#fromDate').val();
            var toDate = $('#toDate').val();
            if(fromDate!= '' && toDate != ''){
                var dataObj ={
                    fromDate:fromDate,
                    toDate:toDate,
                    country:$('#Country').val(),
                }
            $.ajax({
                    url: "Ajax/AjaxFreightRatesReport.php",
                    method: "POST",
                    data: dataObj,
                    dataType: "json", // Expected data type from the server
                    success: function(response) {
                        if(response.filename){
                            window.location.href = response.filename;   
                        }else{
                            if(response.msg){
                                alert(response.msg)
                            }
                        }
                    },
                    error: function(xhr, status, error) {
                        // Handle error
                        console.error("Error:", status, error);
                    },
                })
            }else{
                alert('Plese Choose Dates first');
            }
        })

    });



</script>