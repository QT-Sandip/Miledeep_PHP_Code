<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");
date_default_timezone_set('Asia/Kolkata');
if(!empty($_POST['Request']) && $_POST['Request'] == 'GetPorts' ){
    if(!empty($_POST['Country'])){
        if(is_array($_POST['Country'])){
            $country = ltrim((implode(',',$_POST['Country'])),',');
        }else{
            $country = $_POST['Country'];
        }
        $RowPortCities = [];
        if($country){
            $SqlScrmPortCities = "SELECT * FROM `ScrmPortCities` WHERE Country IN(".$country.")";
            $StmtPortCities = $iconn->query($SqlScrmPortCities);
            while ($PortCities = $StmtPortCities->fetch_assoc()) {
                $RowPortCities[] = $PortCities;
            }            
            $data['Error'] = null;
            $data['Ports'] = $RowPortCities;
            echo json_encode($data);die;
        }else{            
            $data['Error'] = 'Country Missed, Please select Country First';
            $data['Ports'] = $RowPortCities;
            echo json_encode($data);die;
        }
    }else{
        $data['Error'] = 'Country Missed, Please select Country First';
        $data['Ports'] = [];
        echo json_encode($data);die;
    }
}

$PageTitleFolder = "Master";
$PageTitle = "Add Freight Rates";

$ID = isset($_GET['id']) ? $_GET['id'] : "";
$PostedUserId = $_SESSION['UserID'];
$PageUrl = ucfirst(pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME));

$SqlLinerMaster = "SELECT * FROM `ScrmLinerMaster`";
$StmtLinerMaster = $iconn->query($SqlLinerMaster);
/* for port cities drop down */
/* $SqlScrmPortCities = "SELECT * FROM `ScrmPortCities`";
$StmtPortCities = $iconn->query($SqlScrmPortCities);
$RowPortCities = [];
while ($PortCities = $StmtPortCities->fetch_assoc()) {
    $RowPortCities[] = $PortCities;
} */
$SqlCountries = "SELECT `id`,`name` FROM `ScrmCountriesList`";
$StmtCountries = $iconn->query($SqlCountries);
if ($ID != '') {
   /*  for edit screen prepopulate */
    $SqlFreightRates = "SELECT * FROM `ScrmFreightRates` WHERE ID='$ID'";
    $StmtFreightRates = $iconn->query($SqlFreightRates);
    $FreightRatesRow = $StmtFreightRates->fetch_assoc();
    if(!empty($FreightRatesRow['Country'])){
        $SqlScrmPortCities = "SELECT * FROM `ScrmPortCities` WHERE Country IN(".$FreightRatesRow['Country'].")";
        $StmtPortCities = $iconn->query($SqlScrmPortCities);
        $RowPortCities = [];
        while ($PortCities = $StmtPortCities->fetch_assoc()) {
            $RowPortCities[] = $PortCities;
        }
    }
    if(!empty($FreightRatesRow['DestinationPort'])){
        $SqlScrmPortIds = "SELECT `DestinationPortID` FROM ScrmFreightRatesBreakUp WHERE `ParentID`=$ID ORDER BY `CreatedOn` DESC,`BreakupID` ASC";
        $StmtPortIds = $iconn->query($SqlScrmPortIds);
        $RowPortIds = [];
        while ($PortIds = $StmtPortIds->fetch_assoc()) {
            $RowPortIds[] = $PortIds['DestinationPortID'];
        }
        $FreightRatesRow['DestinationPort'] = implode(',',$RowPortIds);
    }
}

extract($_POST);
extract($_GET);

if (isset($_POST['submit'])) {
    $linerId = $_POST["liner_id"];
    $portIds = !empty($_POST["port_ids"])?implode(',',$_POST["port_ids"]):'';
    $fromDate = $_POST["from_date"];
    $toDate = $_POST["to_date"];
    $pluginFD = $_POST["plugin_fd"];
    $thcAndDC = $_POST["thc_and_dc"];
    $genset = $_POST["genset"];

    $RateType = $_POST["RateType"];
    $Dfd1 = $_POST["Dfd1"];
    $Dfd2 = $_POST["Dfd2"];
    $Combined = $_POST["Combined"];
    $Country = ltrim(implode(',',$_POST["Country"]),',');
    $LinerContractId = $_POST['LinerContractId'];
    $OFD = $_POST['OFD'];
    if ($ID == "") {  // New Exporter
        $sqlQuery = "INSERT INTO `ScrmFreightRates`
                    (`Liner`, `DestinationPort`, `FromDate`, `ToDate`, `PlugInFD`,`THCAndDC`, `Genset`,`RateType`,`DFD1`,`DFD2`,`Combined`,`Country`,`LinerContractId`,`OFD`) 
                    VALUES ('$linerId','$portIds','$fromDate','$toDate','$pluginFD','$thcAndDC','$genset','$RateType','$Dfd1','$Dfd2','$Combined','$Country','$LinerContractId','$OFD')";
        $statment = $iconn->query($sqlQuery);
        if ($statment) {
            legacyAuditLogInsertById('ScrmFreightRates', 'ID', $iconn->insert_id, $_POST, [
                'Liner' => $linerId,
                'ReferenceNo' => $iconn->insert_id,
                'Remarks' => 'Freight rate created',
            ], 'Sales');
            if(!empty($_POST['port_id'])){
                addOrUpdateBreakup($_POST,$iconn->insert_id);
            }
            echo '<script language="javascript" type="text/javascript"> 
            alert("Fright Rates added Successfully");
            window.location = "FreightRatesList.php";
            </script>';
        }
    } else {  
        $oldFreightData = legacyAuditFetchRowById('ScrmFreightRates', 'ID', $ID, 'Sales');
        $sqlQuery = "UPDATE `ScrmFreightRates` SET 
                        `Liner` = '$linerId', 
                        `DestinationPort` = '$portIds', 
                        `FromDate` = '$fromDate', 
                        `ToDate` = '$toDate',
                        `PlugInFD` = '$pluginFD',
                        `THCAndDC` = '$thcAndDC', 
                        `Genset` = '$genset',
                        `RateType` = '$RateType',
                        `DFD1` = '$Dfd1',
                        `DFD2` = '$Dfd2', 
                        `Combined` = '$Combined',
                        `Country` = '$Country',
                        `LinerContractId` = '$LinerContractId',
                        `OFD` = '$OFD'
                    WHERE `ID` = '$ID'";
        $statment = $iconn->query($sqlQuery);
        if ($statment) {
            legacyAuditLogUpdateById('ScrmFreightRates', 'ID', $ID, $oldFreightData, $_POST, [
                'Liner' => $linerId,
                'ReferenceNo' => $ID,
                'Remarks' => 'Freight rate updated',
            ], 'Sales');
            if(!empty($_POST['port_id'])){
                addOrUpdateBreakup($_POST,$ID);
            }
            echo '<script language="javascript" type="text/javascript"> 
            alert("Fright Rates Updated Successfully");
            window.location = "FreightRatesList.php";
            </script>';

            // header('Location: ProcessorDetails');

        }
    }
}
function addOrUpdateBreakup($pData,$id){
   // global $iconn;
   include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
    $date = date('Y-m-d H:i:s');
    if(!empty($pData['port_id']) && !empty($id)){
        $portIdsArr= [];
        for($i=1;$i<=count($pData['port_id']);$i++){
            $BreakupID = $pData["BreakupID"][$i];
            $port_id = $pData["port_id"][$i];
           // $RateType = $pData["RateType"][$i];
            $Rate = $pData["Rate"][$i];
            $Dfd1 = $pData["BDfd1"][$i];
            $Dfd2 = $pData["BDfd2"][$i];
            $ContryCode = $pData["ContryCode"][$i];
            $Remarks = $pData["Remarks"][$i];
            $Via = $pData["Via"][$i];
            /* $Combined = $pData["Combined"][$i]; */
            $portIdsArr[] = $port_id;
            if(empty($BreakupID)){
                $sqlQuery = "INSERT INTO `ScrmFreightRatesBreakUp`
                                (`ParentID`, `DestinationPortID`, `Rate`,Dfd1,`Dfd2`, `ContryCode`, `Remarks`,CreatedOn,Via) 
                            VALUES 
                                ('$id','$port_id','$Rate','$Dfd1','$Dfd2','$ContryCode','$Remarks','$date','$Via')";
            }else{
                $sqlQuery = "UPDATE ScrmFreightRatesBreakUp SET 
                                DestinationPortID='$port_id',
                                Rate='$Rate',
                                Dfd1='$Dfd1',
                                Dfd2='$Dfd2',
                                ContryCode='$ContryCode',
                                Remarks='$Remarks',
                                UpdatedOn='$date',
                                Via='$Via'
                            WHERE BreakupID=$BreakupID";
            }
            $statment = $iconn->query($sqlQuery);
        }
        if(!empty($portIdsArr)){
            $portIds =implode(',',$portIdsArr);
            $query = "SELECT GROUP_CONCAT(`BreakupID`) AS `BreakupIDs` FROM `ScrmFreightRatesBreakUp` WHERE `DestinationPortID` NOT IN($portIds) AND `ParentID`=$id";
           $statment = $iconn->query($query);
            $brkIds = $statment->fetch_assoc();
            if(!empty($brkIds['BreakupIDs'])){
                $BreakupIDs = $brkIds['BreakupIDs'];
                $deleteQuery = "DELETE FROM ScrmFreightRatesBreakUp WHERE BreakupID IN($BreakupIDs)";
                $iconn->query($deleteQuery); 
            }
        }
    }
}
// check weather value exits or not 
function funPrintValue($key)
{
    echo (isset($key) && $key != "") ? $key : "";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <?php include($IncludesPath . "title-meta.php"); ?>
    <?php include($IncludesPath . "DbLinks.php"); ?>
    <?php include($IncludesPath . "head-css.php"); ?>
    <style>
        select.error+.select2-container,
        input.error,
        textarea.error {
            border: 1px solid red;
        }

        .select2-container .select2-selection--multiple .select2-selection__choice,.select2-selection__choice__remove {
            background-color: #1cbb8c !important;
            border: 1px solid #1cbb8c !important;
            color: #FFFFFF !important;
        }
    </style>

    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">

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
        <!-- ============================================================== -->
        <!-- Start right Content here -->
        <!-- ============================================================== -->
        <div class="main-content">

            <div class="page-content">
                <div class="container-fluid">

                    <?php if ($Status != '') {     //Show Alert  	
                    ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="mdi mdi-check-all mr-2"></i>
                            <?php
                            echo $Status;
                            $_SESSION['Status'] = '';
                            ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php } ?>

                    <?php include($IncludesPath . "page-title.php"); ?>

                    <div class="row">
                        <div class="col-12">
                            <div class="card">

                                <form role="form" enctype="multipart/form-data" method="post" id="formAddFreightRates">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="form-group col-sm-3">
                                                <label for="from_date">From Date <span
                                                        style="color:red">*</span></label>
                                                <input required type="date" class="form-control" name="from_date"
                                                    id="from_date"
                                                    value="<?php funPrintValue($FreightRatesRow['FromDate']) ?>" />
                                            </div>
                                            <div class="form-group col-sm-3">
                                                <label for="to_date">To Date <span style="color:red">*</span></label>
                                                <input required type="date" class="form-control" name="to_date"
                                                    id="to_date"
                                                    value="<?php funPrintValue($FreightRatesRow['ToDate']) ?>" />
                                            </div>
                                            <div class="form-group col-sm-3">
                                                <label for="RateType">Rate Type <span
                                                        style="color:red">*</span></label>
                                                <select required class="form-control RateType" name="RateType" id="RateType">
                                                    <option value="1" <?php if (isset($FreightRatesRow['RateType']) && $FreightRatesRow['RateType'] == 1)
                                                                            echo 'selected'; ?>>Contract</option>
                                                    <option value="0" <?php if (isset($FreightRatesRow['RateType']) && $FreightRatesRow['RateType'] == 0)
                                                                            echo 'selected'; ?>>Spot</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="form-group col-sm-3">
                                                <label for="liner_id">Liner<span style="color:red">*</span></label>
                                                <select required class="form-control select2" name="liner_id"
                                                    id="liner_id">
                                                    <option value=""> Select Liner </Option>
                                                    <?php while ($RowLinerMaster = $StmtLinerMaster->fetch_array()) { 
                                                        $liner = '';
                                                        if(!empty($FreightRatesRow['Liner'])){
                                                            $liner = $FreightRatesRow['Liner'];
                                                        }else if(!empty($_GET['L_id'])){
                                                            $liner = $_GET['L_id'];
                                                        }
                                                        ?>
                                                        <option <?php if (!empty($liner) && $liner == $RowLinerMaster['LinerID']) { ?>
                                                            selected="selected" <?php } ?>
                                                            value="<?php echo $RowLinerMaster['LinerID']; ?>">
                                                            <?php echo $RowLinerMaster['LinerName']; ?>
                                                        </option>
                                                    <?php } ?>

                                                </select>
                                            </div> 
                                            <div class="form-group col-sm-3">
                                                <label for="Country">Country<span style="color:red">*</span></label>
                                                <select  class="form-control select2" name="Country[]" data-placeholder="Select Country"
                                                    id="Country" required
                                                    <?php echo (!isset($FreightRatesRow['RateType']) || $FreightRatesRow['RateType'] == 1)?'':'multiple';?>
                                                    >
                                                    <option value=""> Select </Option> 
                                                    <?php 
                                                        $countryArr = explode(',',$FreightRatesRow['Country']);
                                                        while ($RowCountries = $StmtCountries->fetch_array()) { ?>
                                                            <option <?php if (in_array($RowCountries['id'],$countryArr)) { ?>
                                                                    selected="selected" <?php } ?>
                                                                value="<?php echo $RowCountries['id']; ?>" >
                                                                <?php echo $RowCountries['name']; ?>
                                                            </option>
                                                    <?php } ?>

                                                </select>
                                            </div> 
                                            <div class="form-group col-sm-3">
                                                <label for="LinerContractId">Liner Contract Number <span
                                                        style="color:red"></span></label>
                                                <select class="drpSelect select2 form-control LinerContractId" name="LinerContractId" id="LinerContractId"
                                                <?php echo (empty($FreightRatesRow['RateType']) && !isset($FreightRatesRow['RateType'])) || $FreightRatesRow['RateType'] == 1 ? 'required':'disabled';?>>
                                                    <option value="">Select</option>
                                                </select>
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="plugin_fd">PlugIn FD <span
                                                        style="color:red"></span></label>
                                                <input  type="number" class="form-control" name="plugin_fd"
                                                    id="plugin_fd" placeholder="Enter PlugIn FD"
                                                    value="<?php funPrintValue($FreightRatesRow['PlugInFD']) ?>" />
                                            </div>
                                        </div>
                                        <div class="row">                                          
                                            
                                            
                                            <div class="form-group col-sm-3">
                                                <label for="OFD ">OFD <span style="color:red"></span></label>
                                                 <input  type="number" class="form-control" name="OFD"
                                                    id="OFD" placeholder="Enter PlugIn FD"
                                                    value="<?php funPrintValue($FreightRatesRow['OFD']) ?>" />
                                            </div>
                                            <div class="form-group col-sm-3">
                                                <label for="Combined ">Combined <span style="color:red">*</span></label>
                                                <select required class="form-control Combined" name="Combined" id="Combined">
                                                    <option value="1" <?php if (isset($FreightRatesRow['Combined']) && $FreightRatesRow['Combined'] == 1)
                                                                            echo 'selected'; ?>>Yes</option>
                                                    <option value="0" <?php if (isset($FreightRatesRow['Combined']) && $FreightRatesRow['Combined'] == 0)
                                                                            echo 'selected'; ?>>No</option>
                                                </select>
                                            </div>
                                            <div class="form-group col-sm-3">
                                                <label for="Dfd1">Dfd1 <span style="color:red"></span></label>
                                                <input  type="number" class="form-control Dfd1" name="Dfd1" id="Dfd1"
                                                    placeholder="Enter Dfd1"
                                                    value="<?php echo !empty($FreightRatesRow['DFD1']) ? $FreightRatesRow['DFD1'] : ''; ?>" />
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="Dfd2">Dfd2 <span style="color:red"></span></label>
                                                <input type="number" class="form-control Dfd2" name="Dfd2" id="Dfd2"
                                                    placeholder="Enter Dfd2"
                                                    value="<?php echo !empty($FreightRatesRow['DFD2']) ? $FreightRatesRow['DFD2'] : ''; ?>" 
                                                    <?php echo isset($FreightRatesRow['Combined']) && $FreightRatesRow['Combined'] != 1 ?'':'readonly'; ?>/>
                                            </div>

                                            
                                            
                                            <div class="form-group col-sm-3">
                                                <label for="thc_and_dc">THC and DC <span
                                                        style="color:red"></span></label>
                                                <input  type="number" class="form-control" name="thc_and_dc"
                                                    id="thc_and_dc" placeholder="Enter THC and DC"
                                                    value="<?php funPrintValue($FreightRatesRow['THCAndDC']) ?>" />
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="genset">Genset <span style="color:red"></span></label>
                                                <input  type="text" class="form-control" name="genset"
                                                    id="genset" placeholder="Enter Genset Info"
                                                    value="<?php funPrintValue($FreightRatesRow['Genset']) ?>" />
                                            </div> 
                                            <div class="form-group col-sm-6">
                                                <input type="hidden" name="selected_ports_order" id="selected_ports_order" value="<?php echo !empty($FreightRatesRow['DestinationPort']) ? $FreightRatesRow['DestinationPort'] : '';?>">
                                                <?php $DestinationPort = !empty($FreightRatesRow['DestinationPort']) ? $FreightRatesRow['DestinationPort'] : '';
                                                       $DestinationPortAry = !empty($DestinationPort) ? explode(',', $DestinationPort) : '';                                               
                                                 ?>
                                                <label for="port_ids">Destination Port<span
                                                        style="color:red"></span></label>
                                                <select  class=" form-control select2" data-placeholder="Select Ports" multiple name="port_ids[]" id="port_ids">
                                                    <?php
                                                    if (!empty($RowPortCities)) {
                                                        foreach ($RowPortCities as $val) { ?>
                                                            <option 
                                                             <?php echo !empty($DestinationPortAry) && in_array($val['PortId'], $DestinationPortAry) ? "selected='selected'" : ''; ?>
                                                                value="<?php echo $val['PortId']; ?>">
                                                                <?php echo $val['Name']; ?>
                                                            </option>
                                                    <?php }
                                                    } ?>

                                                </select>
                                            </div>
                                           
                                        </div>
                                        <hr>
                                        <div class="breakUp"></div>
                                        <div class="col-xs-12 box-footer text-right">
                                            <button type="submit" name='submit' class="btn btn-success">
                                                <?php
                                                if ($ID == '') {
                                                    echo "Save";
                                                } else {
                                                    echo "Update";
                                                }
                                                ?>
                                            </button>
                                            <a target="" href="FreightRatesList.php" class="btn btn-danger"> Close</a>
                                        </div>
                                    </div>
                                </form>
                                <!-- end col -->
                            </div> <!-- end card -->
                        </div> <!-- end col-->
                    </div> <!-- end row -->

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

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.min.js"
        type="text/javascript"></script>

    <script>
        jQuery.validator.addMethod('selectcheck', function(value) {
            return (value != '0' || value != '');
        }, "year required");
        jQuery.validator.addMethod("noSpace", function(value, element) {
            return value == '' || value.trim().length != 0;
        }, "No space please and don't leave it empty");

        $.validator.addMethod("greaterThanFromDate", function(value, element) {
            var fromDate = $('#from_date').val();
            return Date.parse(value) >= Date.parse(fromDate);
        }, "To Date must be greater than or equal to From Date.");
        $(document).ready(function() { 
            <?php 
            if(!empty($_GET['id']) && !empty($FreightRatesRow['DestinationPort'])){?>
                    var liner_id = "<?php echo !empty($FreightRatesRow['Liner'])?$FreightRatesRow['Liner']:'';?>";
                    var Country = "<?php echo !empty($FreightRatesRow['Country'])?$FreightRatesRow['Country']:'';?>";
                    var from_date = "<?php echo !empty($FreightRatesRow['FromDate'])?$FreightRatesRow['FromDate']:'';?>";
                    var to_date = "<?php echo !empty($FreightRatesRow['ToDate'])?$FreightRatesRow['ToDate']:'';?>";
                    var LinerContractId = "<?php echo !empty($FreightRatesRow['LinerContractId'])?$FreightRatesRow['LinerContractId']:'';?>";
                    var RateType = "<?php echo !empty($FreightRatesRow['RateType'])?$FreightRatesRow['RateType']:'';?>";
                    if(liner_id && Country && from_date && to_date && RateType == 1){
                        var dataObj = {
                            MethodType:'getLinerContractNos',
                            liner_id:liner_id,
                            Country:Country,
                            from_date:from_date,
                            to_date:to_date
                        }
                        getContractDropDown(dataObj, LinerContractId);                    
                    } 
            <?php }?>
            $('#Combined').on('change',function(){
                if($(this).val() == 1){
                    $('#Dfd2,.BDfd2').attr('readonly',true)
                }else{
                    $('#Dfd2,.BDfd2').attr('readonly',false)
                }
            })       
            $('#liner_id,#Country,#from_date,#to_date,#RateType').on('change',function(){
                var liner_id = $('#liner_id').val();
                var Country = $('#Country').val(); 
                var from_date = $('#from_date').val();
                var to_date = $('#to_date').val(); 
                var RateType = $('#RateType').val(); 
                if(liner_id && Country && from_date && to_date && RateType == 1){
                    var dataObj = {
                        MethodType:'getLinerContractNos',
                        liner_id:liner_id,
                        Country:Country,
                        from_date:from_date,
                        to_date:to_date
                    }
                    getContractDropDown(dataObj);                    
                }             
            })
            $('#Dfd1').on('change',function(){
                if($('.BDfd1').length != 0){
                    if(confirm('Are you sure you want to change all the Breakup Dfd1 values?')){
                        $('.BDfd1').val($('#Dfd1').val());
                    }
                }
            })
            $('#Dfd2').on('change',function(){
                if($('.BDfd2').length != 0){
                    if(confirm('Are you sure you want to change all the Breakup Dfd2 values?')){
                        $('.BDfd2').val($('#Dfd2').val());
                    }
                }
            })
            $('#Country').on('change',function(){ 
               // $('.breakUp').html('')
               // $('#selected_ports_order').val('')
               var selected_ports_order = $('#selected_ports_order').val().split(',');
                if($(this).val()){
                    $.ajax({
                        url: "AddFreightRatesNew.php",
                        method: "POST",
                        data: {
                            Request : 'GetPorts',
                            Country : $(this).val()
                        },
                        dataType: "json",
                        success: function(response) {
                            var html = `<option value="">Select Ports</option>`;
                            if(response.Ports.length != 0){
                                $.each(response.Ports, function(key,val) {
                                    html += `<option value="`+val.PortId+`">`+val.Name+`</option>`;
                                });

                            }
                            //$('#CountryId').select2('destroy').val(tdCountry).select2();
                           // $('#port_ids').select2('destroy');
                            $('#port_ids').html(html);                          
                            $('#port_ids').select2('destroy').select2();
                            $.each(selected_ports_order, function(key, val1) {
                                if(val1){
                                    // $('#port_ids').select2('destroy').val(val1).select2();
                                    $('#port_ids [value=' + val1 + ']').attr('selected', 'true')//.trigger("change");
                                }
                            });
                            var port_ids = $('#port_ids').val();
                            var pIdsArr = [];
                            var k = 0;
                            $.each(selected_ports_order, function(key, val1) {
                                if(port_ids.includes(val1)){
                                    pIdsArr[k] = val1;
                                    k++;
                                }
                            })
                            //console.log(pIdsArr)
                            var pIdsStr = pIdsArr.join(',');
                            if(pIdsStr){
                                $('#selected_ports_order').val(pIdsStr)  
                                    var dataObj = {
                                        'portIds':pIdsStr.split(','),
                                        'ParentID':"<?php echo !empty($_GET['id'])?$_GET['id']:'';?>",
                                        'Combined' : $('#Combined').val(),
                                        'Dfd1' : $('#Dfd1').val(),
                                        'Dfd2' : $('#Dfd2').val(),
                                        'Country' : $('#Country').val()
                                    }
                                    loadFreightRatesBreakUp(dataObj) 
                            }else{
                                $('.breakUp').html('')
                                $('#selected_ports_order').val('')
                            }

                        },
                        error: function(xhr, status, error) {
                            // Handle error
                            console.error("Error:", status, error);
                        },
                        complete: function() {
                            // This function is always executed regardless of success or error
                            console.log("Request completed");
                        }
                    });

                }else{
                    $('.breakUp').html('')
                    $('#selected_ports_order').val('')
                }
            })

            $("#formAddFreightRates").validate({
                onkeyup: false,
                onfocusout: false,
                invalidHandler: function(form, validator) {
                    var errors = validator.numberOfInvalids();
                    if (errors) {
                        var firstInvalidElement = $(validator.errorList[0].element);
                        firstInvalidElement.focus();
                    }
                },
                ignore: "",
                rules: {
                    liner_id: {
                        required: true
                    },
                    /* 'port_ids[]': {//main table
                        required: true
                    },
                    'port_id[]': {//breakup table
                        required: true
                    }, */
                    from_date: {
                        required: true,
                        date: true
                    },
                    to_date: {
                        required: true,
                        date: true
                    },
                    /* 'Rate[]': {
                        required: true,
                        number: true
                    }, */
                    /* plugin_fd: {
                        required: true,
                        number: true
                    },
                    'Dfd1': {
                        required: true,
                        number: true
                    },
                    thc_and_dc: {
                        required: true,
                        number: true
                    },
                    genset: {
                        required: true
                    } ,*/
                    Combined: {
                        required: true
                    },
                    RateType: {
                        required: true
                    }
                },
                messages: {
                    liner_id: {
                        required: "Please select a liner."
                    },
                    /* 'port_ids[]': {
                        required: "Please select a destination ports."
                    },
                    'port_id[]': {
                        required: "Please select a destination port."
                    }, */
                    
                    from_date: {
                        required: "Please select the from date.",
                        date: "Please enter a valid date."
                    },
                    to_date: {
                        required: "Please select the to date.",
                        date: "Please enter a valid date."
                    },
                    /* 'Rate[]': {
                        required: "Please enter the Spot Rate.",
                        number: "Please enter a valid number."
                    }, */
                   /*  plugin_fd: {
                        required: "Please enter the PlugIn FD.",
                        number: "Please enter a valid number."
                    },
                    'Dfd1': {
                        required: "Please enter the Dfd1.",
                        number: "Please enter a valid number."
                    }, */
                   /*  'Dfd2[]': {
                        required: "Please enter the Dfd2.",
                        number: "Please enter a valid number."
                    }, */
                    /* thc_and_dc: {
                        required: "Please enter THC and DC.",
                        number: "Please enter a valid number."
                    },
                    genset: {
                        required: "Please enter the Genset info."
                    }, */
                    Combined: {
                        required: "Please select Yes or No for Combined."
                    }
                },
                errorPlacement: function() {
                    return false; // suppresses error message text
                },
                submitHandler: function() {
                    return true;
                }
            });
           // let selectedInOrder = []; 
           // let selectedInOrder = $('#selected_ports_order').val()?$('#selected_ports_order').val().split(','):[];
            $('#port_ids').select2();

            $('#port_ids').on('select2:select', function (e) {
                e.preventDefault();
                const val = e.params.data.id;
                let selectedInOrder = $('#selected_ports_order').val()?$('#selected_ports_order').val().split(','):[];
                //console.log(selectedInOrder)
                if (!selectedInOrder.includes(val)) {
                    //selectedInOrder.push(val);
                    selectedInOrder.unshift(val);
                }
               
               //console.log('selectedInOrder:'+selectedInOrder)
                var portIDsRev = selectedInOrder;
                $('#selected_ports_order').val(selectedInOrder.join(','))
               // console.log('portIDsRev:'+portIDsRev.reverse())
                 var dataObj = {
                    //'portIds':portIDsRev.reverse(),
                    'portIds':portIDsRev,
                    'ParentID':"<?php echo !empty($_GET['id'])?$_GET['id']:'';?>",
                    'Combined' : $('#Combined').val(),
                    'Dfd1' : $('#Dfd1').val(),
                    'Dfd2' : $('#Dfd2').val()
                }
                loadFreightRatesBreakUp(dataObj);
                //portIDsRev.reverse()
                portIDsRev
            });

            $('#port_ids').on('select2:unselect', function (e) {
                if(confirm('Are you Sure to Delete This?')){
                    const val = e.params.data.id;
                    let selectedInOrder = $('#selected_ports_order').val()?$('#selected_ports_order').val().split(','):[];
                    selectedInOrder = selectedInOrder.filter(v => v !== val);
                    //  var portIDsRev = selectedInOrder.reverse();
                    var portIDsRev = selectedInOrder;
                    $('#selected_ports_order').val(selectedInOrder.join(','))
                    var dataObj = {
                        //'portIds':portIDsRev.reverse(),
                        'portIds':portIDsRev,
                        'ParentID':"<?php echo !empty($_GET['id'])?$_GET['id']:'';?>",
                        'Combined' : $('#Combined').val(),
                        'Dfd1' : $('#Dfd1').val(),
                        'Dfd2' : $('#Dfd2').val()
                    }
                    //console.log(selectedInOrder.reverse())
                    loadFreightRatesBreakUp(dataObj);
                    //portIDsRev.reverse()
                    portIDsRev
                }else{
                    var $sel = $(this);
                    var val = $sel.val() || [];
                    val.push(String(e.params.data.id));
                    $sel.val(val).trigger('change.select2');
                }
            });

            /* $('#port_ids').on('change',function(){return;
                var dataObj = {
                    'portIds':$(this).val(),
                    'ParentID':"<?php echo !empty($_GET['id'])?$_GET['id']:'';?>",
                    'Combined' : $('#Combined').val(),
                    'Dfd1' : $('#Dfd1').val(),
                    'Dfd2' : $('#Dfd2').val()
                }
                loadFreightRatesBreakUp(dataObj);                    
            }) */
            <?php 
            if(!empty($_GET['id']) && !empty($FreightRatesRow['DestinationPort'])){
               // $portIds = array_reverse(explode(',',$FreightRatesRow['DestinationPort']))
                 $portIds = explode(',',$FreightRatesRow['DestinationPort'])?>
                var dataObj = {
                    'portIds':<?php echo json_encode($portIds);?>,
                    'ParentID':"<?php echo !empty($_GET['id'])?$_GET['id']:'';?>",
                    'Combined' : $('#Combined').val(),
                    'Dfd1' : $('#Dfd1').val(),
                    'Dfd2' : $('#Dfd2').val()
                }
                setTimeout(function() {
                       loadFreightRatesBreakUp(dataObj)
                }, 50);
           <?php }?>
        });
        function loadFreightRatesBreakUp(dataObj){
           /* for saving previous filled values */
           var port_idsArr = [];
           $.each($(".port_ids"), function () {
                port_idsArr.push($(this).val());
            });
            var differencePortId = port_idsArr.filter(item => !dataObj.portIds.includes(item));
            if(differencePortId.length!=0){
                $.each(differencePortId, function (i,val) {
                    $('.portId_'+val).html('');
                })
            }

            //console.log(differencePortId);
           var calssNames = ['port_ids','Rate','RateType','BDfd1','BDfd2','ContryCode','Via','Remarks'];
            $.each(calssNames, function (i,val) {
                var arr = [];
                $.each($("."+val), function() {
                    arr.push($(this).val());
                });                
                dataObj[val] = arr;
            });
           // console.log(dataObj);
           // dataObj[Combined] = $('#Combined').val();
          //  var com = dataObj{Combined : $('#Combined').val()};
           // console.log(dataObj);
             /* for saving previous filled values ends*/
             if(dataObj['portIds'].length != 0){
                $.ajax({
                    url: "FreightRatesBreakup.php",
                    method: "POST",
                    data: dataObj,
                    dataType: "html", // Expected data type from the server
                    success: function(response) {
                        // Handle successful response
                        $('.breakUp').html(response);                          
                        $('.select2').select2();
                    },
                    error: function(xhr, status, error) {
                        // Handle error
                        console.error("Error:", status, error);
                    },
                    complete: function() {
                        // This function is always executed regardless of success or error
                        console.log("Request completed");
                    }
                });
             }else{
                $('.breakUp').html('');
             }
        }
        function getContractDropDown(dataObj , LinerContractId = ''){
            $.ajax({
                url: "Ajax/AjaxLinerContract.php",
                method: "POST",
                data: dataObj,
                dataType: "json",
                success: function(response) {
                    var html = `<option value="">Select</option>`;
                    if(response.LinerContractData){
                            $.each(response.LinerContractData, function(key,val) {
                            html += `<option value="`+val.LinerContractID+`">`+val.ContractNumber+`</option>`;
                        });
                    }
                    //$('#CountryId').select2('destroy').val(tdCountry).select2();
                    $('#LinerContractId').html(html);                          
                    //$('.select2').select2('destroy').select2();
                    if(LinerContractId){
                         $('#LinerContractId').select2('destroy').val(LinerContractId).select2();
                    }
                },
                error: function(xhr, status, error) {
                    // Handle error
                    console.error("Error:", status, error);
                },
                complete: function() {
                    // This function is always executed regardless of success or error
                    console.log("Request completed");
                }
            });
        }
       /*  $(document).on('change','#RateType',function(){
            if($(this).val() == 1){
                $('#LinerContractId').prop('required', true).prop('disabled', false);
                $('#Country,#LinerContractId').select2('destroy');
                $('#Country').removeAttr('multiple');
                $('#Country').select2().trigger("change");; 
            }else{
                $('#LinerContractId').prop('required', false).prop('disabled', true);
                $('#Country,#LinerContractId').select2('destroy');
                $('#Country').attr('multiple', 'multiple');
                $('#Country').select2().trigger("change");; 
            }
        }) */
       $(document).on('change','#RateType', function() {
            var $country = $('#Country');
            var $liner = $('#LinerContractId');
            //var CountryArr = $countryA.val();
            if($country.val().length > 1){
                if(confirm('All selected Countries/Ports will be cleared. Do you want to proceed?')){
                    $country.select2('destroy');
                    $liner.select2('destroy');
                    if ($(this).val() == 1) {
                        $liner.prop('required', true).prop('disabled', false);
                        $country.removeAttr('multiple');
                    } else {
                        $liner.prop('required', false).prop('disabled', true);
                        $country.attr('multiple', 'multiple');
                    }
                    // Reinitialize Select2
                    $country.select2({
                        placeholder: "Select",
                        width: '100%'
                    }).trigger("change");
                    $liner.select2({
                        placeholder: "Select",
                        width: '100%'
                    });
                }else{
                    var $sel = $(this);
                    var val = $sel.val() || [];
                    (val==1)? val = 0 :val = 1;
                    //alert(val)
                    $sel.val(val).trigger('change.select2'); // or just trigger change if needed
                    return;                   
                }
            }else{
                $country.select2('destroy');
                $liner.select2('destroy');
                if ($(this).val() == 1) {
                    $liner.prop('required', true).prop('disabled', false);
                    $country.removeAttr('multiple');
                } else {
                    $liner.prop('required', false).prop('disabled', true);
                    $country.attr('multiple', 'multiple');
                }
                // Reinitialize Select2
                $country.select2({
                    placeholder: "Select",
                    width: '100%'
                }).trigger("change");;
                $liner.select2({
                    placeholder: "Select",
                    width: '100%'
                });
            }            
        });
        function RateTypeChangeFun(){
            var $country = $('#Country');
            var $liner = $('#LinerContractId');
            //var CountryArr = $country.val();
            // Destroy Select2 first
            $country.select2('destroy');
            $liner.select2('destroy');

            if ($(this).val() == 1) {
                $liner.prop('required', true).prop('disabled', false);
                $country.removeAttr('multiple');
            } else {
                $liner.prop('required', false).prop('disabled', true);
                $country.attr('multiple', 'multiple');
            }

            // Reinitialize Select2
            $country.select2({
                placeholder: "Select",
                width: '100%'
            });
            $liner.select2({
                placeholder: "Select",
                width: '100%'
            });
        }
    </script>


</body>

</html>
