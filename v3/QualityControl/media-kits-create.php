<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

include($cp . "Includes/IncludePage.php");

$PageTitleFolder = "Quality Control";
$PageTitle = "Media Create";

$apiList = API_MEDIA_KITS;

$KitID = $_GET["KitID"];
$DeleteAccess = $_GET['DeleteAccess'];

$ClientConfigData = $_SESSION['ClientConfigData'];
// $DefaultNoOfWells = $ClientConfigData->DefaultNoOfWells;  // uncomment this line after auth-login.php converted to API modal
//$DefaultNoOfWells = $ClientConfigData['DefaultNoOfWells']; // remove this line after auth-login.php converted to API modal


$bodyContent = array(
    "KitID" => $KitID,
    "DeleteAccess"=>$DeleteAccess
);
$mediaMasterList = [];
if ($KitID != '') {
    $result = CallAPI($apiList, "GET", "", $bodyContent);
      $Data = $result->Data[0];
      $getMediaquery = " select ParameterID,MediaID,MediaName 
            from usedmediamaster 
            where 
                ParameterID = 
                ".$Data->mParameter."
             ";
            
        $stmtGetMedia = $iconn->query($getMediaquery);    
        if($stmtGetMedia){
            while( $rowMedia = $stmtGetMedia->fetch_array() ){
                $mediaMasterList[$rowMedia['ParameterID']][] = $rowMedia;            
            }
        }
}
//echo "<pre>"; print_r($result); echo "</pre>";

$SqlMediaList = "select MediaID,MediaName from usedmediamaster";
$StmtMediaList = $iconn->query($SqlMediaList);
$mediaMasterList = array();
while ($RowMediaList = $StmtMediaList->fetch_array()) {
    $mediaMasterList[] = $RowMediaList;        
}   

?>
<!DOCTYPE html>
<html lang="en">
<link href="<?php echo $href ?>" rel="stylesheet" type="text/css" />

<head>

    <?php include($IncludesPath . "title-meta.php"); ?>
    <?php include($IncludesPath . "DbLinks.php"); ?>
    <?php include($IncludesPath . "head-css.php"); ?>

</head>

<body data-sidebar="dark">

    <?php
    include($IncludesPath . "PreLoader.php"); ?>

    <!-- Begin page -->
    <div id="layout-wrapper">

        <?php
        include($IncludesPath . "topbar.php");
        include($IncludesPath . "sidebar.php");
        ?>

        
        <div class="main-content">
            <div class="page-content">
                <div class="container-fluid">
                    <?php include($IncludesPath . "page-title.php"); ?>
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body">
                                    <form id="frmMediaKits">
                                        <div class="row">
                                            <div class="col col-md-3 mb-3">
                                                <label for="NameMediaUsed">Media<span style="color:red">*</span></label>
                                                <select class="form-control select2" name="NameMediaUsed" id="NameMediaUsed" required
                                                    >
                                                    <option Value=""> Select</option>                                                    
                                                    <?php 
                                                    foreach($mediaMasterList as $RowMediaList){                                                    
                                                        ?>
                                                        <option Value="<?php echo $RowMediaList['MediaID']; ?>" <?php echo ($Data->mMedia==$RowMediaList['MediaID'])?"selected='selected'":''; ?>>
                                                            <?php echo $RowMediaList['MediaName']; ?>
                                                        </option>
                                                    <?php } ?>       
                                                </select>
                                            </div>

                                            <div class="col col-md-3 mb-3">
                                                <label for="txtKitsOrderedDate" class="form-label">Ordered Date <span
                                                        style="color:red">*</span></label>
                                                <input type="date" class="form-control" id="txtKitsOrderedDate"
                                                    name="txtKitsOrderedDate"
                                                    value='<?php echo $Data->mKitsOrderedDate ?>' />
                                            </div>

                                            <div class="col col-md-3 mb-3">
                                                <label for="txtReceivedDate" class="form-label">Received Date</label>
                                                <input type="date" class="form-control" id="txtReceivedDate"
                                                    name="txtReceivedDate" value='<?php echo $Data->mKitsReceivedDate ?>' />
                                            </div>

                                            <div class="col col-md-3 mb-3">
                                                <label for="txtKitsLotNo" class="form-label"> Lot No <span
                                                        style="color:red">*</span></label>
                                                <input type="text" class="form-control" id="txtKitsLotNo"
                                                    name="txtKitsLotNo" placeholder="Enter Lot No"
                                                    value='<?php echo $Data->mKitsLotNo ?>' <?php echo ($Data->mKitsLotNo)?'readonly':'' ?> />
                                            </div>

                                            <div class="col col-md-3 mb-3">
                                                <label for="txtOrderedKits" class="form-label">Ordered <span
                                                        style="color:red">*</span></label>
                                                <input type="text" class="form-control" id="txtOrderedKits"
                                                    name="txtOrderedKits" placeholder="Enter Orderd"
                                                    value='<?php echo $Data->mNoOfKitsOrdered ?>' <?php echo ($Data->mKitsLotNo)?'readonly':'' ?> />
                                            </div>

                                            <div class="col col-md-3 mb-3">
                                                <label for="txtReceivedKits" class="form-label">Received </label>
                                                <input type="text" class="form-control" id="txtReceivedKits"
                                                    name="txtReceivedKits" placeholder="Enter Received"
                                                    value='<?php echo $Data->mNoOfKitsReceived ?>' />
                                            </div>

                                            <div class="col col-md-3 mb-3">
                                                <label for="txtInvoiceNo" class="form-label">Invoice No <span
                                                        style="color:red">*</span></label>
                                                <input type="text" class="form-control" id="txtInvoiceNo"
                                                    name="txtInvoiceNo" placeholder="Enter Invoice No"
                                                    value='<?php echo $Data->mInvoiceNo ?>'>
                                            </div>

                                            <div class="col col-md-3 mb-3">
                                                <label for="manufactureDate" class="form-label">Manufacture Date</label>
                                                <input type="Date" class="form-control" id="manufactureDate"
                                                    name="manufactureDate" value='<?php echo $Data->mManufactureDate ?>'>
                                            </div>

                                            <div class="col col-md-3 mb-3">
                                                <label for="expiryDate" class="form-label">Expiry Date </label>
                                                <input type="Date" class="form-control" id="expiryDate" name="expiryDate"
                                                    value='<?php echo $Data->mExpiryDate ?>'>
                                            </div>

                                              <div class="col col-md-3 mb-3">
                                                <label for="bottleWeight" class="form-label">Bottle Weight</label>
                                                <input type="bottleWeight" class="form-control" id="bottleWeight" 
                                                name="bottleWeight"  placeholder="Enter Bottle Weight"
                                                    value='<?php echo $Data->mBottleWeight ?>'>
                                            </div>

                                            <div class="text-center mt-4 pt-2">
                                                <input type="hidden" id="hdnKitID" name="hdnKitID"
                                                    value="<?php echo $KitID ?>" />
                                                <a href="media-kits-list" class="btn btn-danger">Close</a>
                                                <?php if ($KitID == '') { ?>
                                                    <button type="submit" class="btn btn-primary btnSave" name="submit"
                                                        id="submit">Submit</button>
                                                <?php } else { ?>
                                                    <button class="btn btn-success btnSave" name="submit"
                                                        id="submit">Update</button>
                                                <?php } ?>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-body" id="ATT_DATA" style="display:none">
                                        <table  id="mytable-data" class="table dt-responsive nowrap w-100" >
                                        <thead>
                                            <tr>
                                                <th>Sno </th>
                                                 <th>Media Name </th>
                                                <th>Ordered Date </th>                         
                                                <th>Received Date </th>
                                                <th>Lot No </th>
                                                <th>Ordered </th>                                 
                                                <th>Received</th>                                                
                                                <th>Invoice No</th>                                                
                                                <th>Manufacture Date</th>                                                
                                                <th>Expiry Date</th>   
                                                <th>Bottle Weight</th>                                                
                                            </tr>
                                        </thead>                                        
                                    </table>
                                </div> <!-- end card body-->
                            </div> <!-- end card -->
                        </div><!-- end col-->
                    </div>

                </div>
            </div>
        </div>
        
        <?php include($IncludesPath . "vendor-scripts.php"); ?>
        <?php include($IncludesPath . "NewScripts.php"); ?>
        <?php include($IncludesPath . "ShowDataTable.php"); ?>
    
    </div> 
    
    <script>
        
        function getMedia(Parameter,NameMediaUsed) {
            //GET MediaList
            $("#"+NameMediaUsed).html("");
            
            var parameterVal = $("#"+Parameter).val();                
            if (parameterVal == 0) {
                return false;
            }
            $.ajax({
                url: 'Ajax/Ajax-inhouse-getmedia.php',
                type: 'POST',
                data: { parameterId: parameterVal, action: "getMediaList" },
                success: function(data) {                    
                    $("#"+NameMediaUsed).html(data);
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error: ", error);
                }
            });
        }       
        

        $(document).ready(function() {

            /*$('#btnLoadAll').on('click', function () {
                loadAdvances(true); // Load all records
            });*/
            
            handleReceivedKitsReadonly();
            $('#NameMediaUsed').select2();            
            var urlId = '<?php echo $urlId; ?> ';          
            if(urlId == 0){                
                loadAdvances();
                
                handleReceivedKitsReadonly();
            }else{

                $("#submit").text("Update");
            }
            
            $('#frmMediaKits').submit(function() { 
            
                 event.preventDefault();                
                
                var orderedKits = $("#txtOrderedKits").val();
                var receivedKits = $("#txtReceivedKits").val();

                // Check if OrderedKits and ReceivedKits are greater than 0
                if (orderedKits <= 0) {
                    alert("Ordered must be greater than 0");
                    return false;  // Prevent form submission
                }

                if (receivedKits > 0) {
                    // Set KitsLotNo readonly only after ReceivedKits is entered and form is submitted
                    $("#txtKitsLotNo").prop('readonly', true);
                   
                }

                $.ajax({
                    url: 'Ajax/ajax-media-kits-create.php',
                    type: 'POST',
                    data: {
                        methodType: "postFormData",
                        kitID: $("#hdnKitID").val(),
                        DeleteAccess: $("#btnSave").val(),
                        kitsOrderedDate: $("#txtKitsOrderedDate").val(),
                        receivedDate: $("#txtReceivedDate").val(),
                        kitsLotNo: $("#txtKitsLotNo").val(),
                        orderedKits: $("#txtOrderedKits").val(),
                        receivedKits: $("#txtReceivedKits").val(),
                        invoiceNo: $("#txtInvoiceNo").val(),
                        manufactureDate: $("#manufactureDate").val(),
                        expiryDate: $("#expiryDate").val(),
                        bottleWeight: $("#bottleWeight").val(),
                        media: $("#NameMediaUsed").val()
                    },
                    success: function (data) { 
                            var newUrl = "media-kits-list";
                            window.location.href = newUrl;
                            return false;    
                    }                             
                    
                });
            });            

            
            $(document).on("click", ".btnDelete", function () {
                var selectedID = $(this).attr("data-id");
                var r = confirm("Are You Sure Do You Want To Delete?");
                if (r == true) {
                    $.ajax({
                        url: 'Ajax/Ajax-ihmbrfwi.php',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            'id': selectedID, // Pass LotIds as an array
                            'action': 'Delete'
                        },
                        success: function (response) {
                            alert("Deleted successfully");
                            var newUrl = "ihmbrfwi-create?FromDate="+fromDate+"&ToDate="+toDate;
                            window.location.href = newUrl;
                            return false;
                        },
                        error: function (xhr, status, error) {
                            console.error("Error:", status, error);
                            //alert("Error occurred while deleting.");
                        }

                    });
                }
                else {

                }
            });
            

        });

        document.getElementById('txtReceivedKits').addEventListener('input', function () {
        const receivedkits = parseFloat(document.getElementById('txtReceivedKits').value) || 0;
        //var DefaultNoOfWells=<?php echo $DefaultNoOfWells?>
        
        // var totalwells=receivedkits*96;
        //var totalwells=receivedkits*DefaultNoOfWells;

        //$("#txtTotalWells").val(totalwells);
    }); 

        function handleReceivedKitsReadonly(receivedKits = null) {
        const currentReceivedKits = receivedKits !== null ? receivedKits : parseFloat($("#txtReceivedKits").val()) || 0;
        
        if (currentReceivedKits > 0) {
            $("#txtReceivedKits").prop('readonly', true);
        } else {
            $("#txtReceivedKits").prop('readonly', false);
        }
    }
    function loadAdvances(loadAll = false) {
        $('#ATT_DATA').show();

        if ($.fn.DataTable.isDataTable('#mytable-data')) {
            $('#mytable-data').DataTable().destroy();
        }

        $('#mytable-data').DataTable({
    serverSide: !loadAll,
    processing: true,
    ajax: {
        url: 'Ajax/ajax-media-kits-create',
        type: 'POST',
        data: {
            methodType: "getBreakUpData",
            kitID: $("#hdnKitID").val(),
            DeleteAccess: $("#btnSave").val(),
            loadAll: loadAll
        }
    },
    columns: [
        { data: 'SNO' },
        { data: 'MediaName' }, 
        { data: 'KitsOrderedDate' },
        { data: 'KitsReceivedDate' },
        { data: 'mKitsLotNo' },
        { data: 'mNoOfKitsOrdered' },
        { data: 'mNoOfKitsReceived' },
        { data: 'mInvoiceNo' },
        { data: 'mManufactureDate' },
        { data: 'mExpiryDate' },
         { data: 'mBottleWeight' },
    ],
    paging: !loadAll,
    pageLength: 10,
    lengthMenu: [[10, 50, 100], [10, 50, 100]],
    scrollX: true,
    scrollY: '500px',
    scrollCollapse: true,
    autoWidth: false,
    responsive: false,
    searching: true,
    ordering: false,

    dom: 'Bfrtip', // Enable button area (above the table)
    buttons: [
        {
            text: 'Load All',
            className: 'btn btn-primary',
            action: function (e, dt, node, config) {
                // Reload the table with all data
                loadAdvances(true);
            },
            init: function (api, node, config) {
                $(node).attr('id', 'btnLoadAll');
            }
        },
        {
            extend: 'excelHtml5',
            text: 'Export to Excel',
            className: 'btn btn-success',
            title: 'Media Data',
            exportOptions: {
                columns: ':visible'
            }
        }
    ]
});

    }

    </script>      
</body>
</html>