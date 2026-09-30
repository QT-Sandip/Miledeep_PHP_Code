<?php
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

$PageTitleFolder = "Quality Control";
include($cp . "Includes/IncludePage.php");


$QueryParms = $_GET['Q'];

if ($QueryParms != '') {
    $QueryParms = urlDecode64($QueryParms);
    $queries = array();
    parse_str($QueryParms, $_GET);
}


$InvID = $_GET['PoID'];

$SqlInvoice = "SELECT * FROM $TblScrmPoData where PoID='$InvID'";
$StmtInvoice = $iconn->query($SqlInvoice);
$InvoiceDetails = $StmtInvoice->fetch_array();
$PageTitle = "Add Documents-" . $InvoiceDetails['PoNo'];

?>

<!doctype html>
<html lang="en">
<!--<link href="https://serp.silverlights.in/Clients/JME/User.css"  rel="stylesheet" type="text/css" /> -->

<head>

    <?php include($IncludesPath . "title-meta.php"); ?>
    <?php include($IncludesPath . "DbLinks.php"); ?>
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


            <div class="modal fade" id="myModal" role="dialog">
                <div class="modal-dialog">
                    <!-- Modal content-->
                    <div class="modal-content">
                        <div class="modal-header">
                            <h4 class="modal-title modheading"></h4>
                            <button type="button" class="close" data-dismiss="modal">&times;</button>

                        </div>
                        <div class="modal-body">

                            <div class="box-body" id="CreateDocID">

                            </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="page-content">
                <div class="container-fluid">

                    <?php include($IncludesPath . "page-title.php"); ?>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">

                                    <div class="row">
                                        <div class="col-lg-3">
                                            <div class="form-group">
                                                <select class="form-control select2" name="DocType" id="DocType">
                                                    <option value="1">Export Docs</option>
                                                    <!--  <option value="2">Mpeda Doc</option>-->
                                                    <option value="3">Covering Letters</option>
                                                    <option value="4">Internal Test Reports</option>
                                                    <option value="5">SIMP Docs</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div align="center" class="col-lg-3">

                                            <button id="payBtn" align="center" type="button" class="btn btn-primary waves-effect waves-light">
                                                Create <i class="ri-arrow-right-line align-middle ml-2"></i>
                                            </button>
                                        </div>

                                    </div>
                                    <!-- end row -->
                                </div>
                            </div>
                        </div>

                        <!-- end col -->
                    </div> <!-- end row -->

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body" id="ClientList">
                                    <h4 class="card-title">Purchased Lot - List</h4>


                                </div> <!-- end card body-->
                            </div> <!-- end card -->
                        </div><!-- end col-->
                    </div>
                    <!-- end row-->


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



</body>

</html>



<Script>
    $('document').ready(function() {

        var DocType = $('#DocType').val();
        var InvId = "<?php echo $InvID; ?>";
        //var InvId="<?php echo $QueryParms; ?>";//uma
        getAvalDos(DocType, InvId);




        $(document).on('click', '#payBtn', function(e) {
            var DocType = $('#DocType').val();



            $.ajax({
                url: 'Ajax/AjaxDocList.php',
                type: 'POST',
                data: {
                    'Type': 'DocCreate',
                    'DocType': DocType,
                    'InvId': InvId
                },
                success: function(data) {
                    $("#CreateDocID").html(data);
                    //$('#example1').DataTable();
                    $('.modheading').text('Select Document');
                    $('#myModal').modal('toggle');
                }
            });


        $(document).on('change', '.qc-lab-sampling-type', function() {
            var $select = $(this);
            var samplingType = $select.val();
            var poId = $select.data('poid');
            var $createLink = $select.closest('td').find('.qc-lab-sampling-create-link');

            $createLink.hide();

            if (samplingType === '') {
                return;
            }

            $select.prop('disabled', true);

            $.ajax({
                url: 'Ajax/AjaxDocList.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    Type: 'SaveLabSamplingType',
                    PoID: poId,
                    SamplingType: samplingType
                },
                success: function(response) {
                    if (response && response.success) {
                        $createLink.show();
                    } else {
                        alert((response && response.message) ? response.message : 'Unable to save sampling type');
                    }
                },
                error: function() {
                    alert('Unable to save sampling type');
                },
                complete: function() {
                    $select.prop('disabled', false);
                }
            });
        });





            //return false;
        });


    });


    function getAvalDos(DocType, InvId) {
        $.ajax({
            url: 'Ajax/AjaxDocList.php',
            type: 'POST',
            data: {
                'Type': 'DocList',
                'DocType': DocType,
                'InvId': InvId
            },
            success: function(data) {
                showData(data);
                // $("#EmpList").html(data);
                //$('#example1').DataTable();
            }
        });
    }
</script>





<script>
    $(function() {
        $('#DocType').change(function() {
            var DocType = $('#DocType').val();
            var InvId = "<?php echo $InvID; ?>";
            getAvalDos(DocType, InvId);
        });
    });
</script>

<script>
    $(function() {
        $('#example1').dataTable({
            "order": [
                [0, 'desc']
            ]
        });
    })
</script>


<script>
    $(document).ajaxStart(function() {
        $("#wait").css("display", "block");
    });
    $(document).ajaxComplete(function() {
        $("#wait").css("display", "none");
    });
</script>
<script>
    function showData(data) {

        $("#ClientList").html(data);

        var table = $('#key-datatable').DataTable({
            scrollY: "600px",
            scrollX: true,
            //sScrollX: "100%",
            sScrollXInner: "150%",
            // scrollCollapse:true,
            paging: true,
            destroy: true,
            fixedColumns: true,
            bSort: true,
            "bStateSave": true,
            "fnStateSave": function(oSettings, oData) {
                localStorage.setItem('key-datatable', JSON.stringify(oData));
            },
            "fnStateLoad": function(oSettings) {
                return JSON.parse(localStorage.getItem('key-datatable'));
            }
        });
    }
</script>
