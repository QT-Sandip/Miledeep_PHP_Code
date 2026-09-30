<?php
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");

$PageTitleFolder = "Quality Control";
$PageTitle = "In House Microbiology Report For Water and Ice Create";

// batch no/lot no based on purchase date
$FromDate = $_GET['FromDate'];
$ToDate = $_GET['ToDate'];

//plant names
$sqlQuery = "SELECT ProcessingStationID,StationName,FullName FROM `RmProcessingStations` WHERE StationStatus=1 ORDER BY SortOrder ASC";
$stmtPlants = $iconn->query($sqlQuery);
//end here

//  Userlist names
$sqlQuery = "SELECT MIN(LUID) as USER_ID, LbuName as USER_NAME FROM `QcLabTechnologist` WHERE LbuStatus=1 Group by LbuName ORDER BY LbuName ASC";
$stmtUsers = $iconn->query($sqlQuery);
$RowUsersLists = [];
while ($RowUsers = $stmtUsers->fetch_array()) {
    $RowUsersLists[] = $RowUsers;
}
//end here

//Paramaters list
$SqlParameterList = "SELECT TestID as ParameterID,TestName as ParameterName FROM `QcTestList`";
$StmtParameterList = $iconn->query($SqlParameterList);
$parameter_mast_list = array();
while ($RowParameterList = $StmtParameterList->fetch_array()) {
    $parameter_mast_list[] = $RowParameterList;        
}
//end here

$mediaMasterList = array();
$equpmentMasterList = array();

$urlId = (isset($_GET['id']) && $_GET['id']) ? $_GET['id'] : 0;
$ROWSWAB =  array();
if($urlId>0){
    $sqlGetRecord = " select * from micro_biology_workers_hand  where mbwh_id= '".$urlId."' ";
    $stmturlresults = $iconn->query($sqlGetRecord);
    if($stmturlresults){
       
       $ROWSWAB = $stmturlresults->fetch_array(); 
       $getMediaquery = " select ParameterID,MediaID,MediaName 
            from usedmediamaster 
            where 
                ParameterID in (
                ".$ROWSWAB['tpc_paramater'].",
                ".$ROWSWAB['vcholerae_paramater']."
            ) ";
        $stmtGetMedia = $iconn->query($getMediaquery);    
        if($stmtGetMedia){
            while( $rowMedia = $stmtGetMedia->fetch_array() ){
                $mediaMasterList[$rowMedia['ParameterID']][] = $rowMedia;            
            }
        }

        $getEquipmentquery = " select InstrumentId, ParameterID, MediaID, InstrumentName
            from instrumentmaster "
            ;
        $stmtGetEquipment = $iconn->query($getEquipmentquery);    
        if($stmtGetEquipment){
            while( $rowEquipment = $stmtGetEquipment->fetch_array() ){
                $equpmentMasterList[$rowEquipment['ParameterID']][$rowEquipment['MediaID']][] = $rowEquipment;            
            }
        }

    }
}

$reporttypearr = array( 
    "Water - Daily Based" => "Water - Daily Based", 
    "Water - Once in 4days" => "Water - Once In 4 Days",
    "Water And Ice Weekly Once" => "Water And Ice Weekly Once"
);

$sampledescarr = array( 
    "WATER" => "WATER", 
    "ICE" => "ICE" 
);
$locationarr = array( 
    "RAW MATERIAL RECEIVING" => "RAW MATERIAL RECEIVING", 
    "DEHEADING" => "DEHEADING", 
    "GRADING" => "GRADING", 
    "VALUE ADDITION" => "VALUE ADDITION", 
    "SOAKING" => "SOAKING",     
    "BLOCK" => "BLOCK",     
    "INFEED - ARICO" => "INFEED - ARICO",         
    "INFEED - GLORY" => "INFEED - GLORY",     
    "OUTFEED - ARICO" => "INFEED - ARICO",     
    "OUTFEED - GLORY" => "INFEED - GLORY",     
    "REPACKING" => "REPACKING",         
);

$itypeoficearr = array(
    "PLATE ICE CHUTE -1" => "PLATE ICE CHUTE -1",
    "PLATE ICE CHUTE -2" => "PLATE ICE CHUTE -2",
    "PLATE ICE CHUTE -3" => "PLATE ICE CHUTE -3",
    "PLATE ICE CHUTE -4" => "PLATE ICE CHUTE -4",
    "FLAKE ICE CHUTE -1" => "FLAKE ICE CHUTE -1",
    "FLAKE ICE CHUTE -2" => "FLAKE ICE CHUTE -2",
    "FLAKE ICE CHUTE -3" => "FLAKE ICE CHUTE -3",
    "FLAKE ICE CHUTE -4" => "FLAKE ICE CHUTE -4",
);

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
                                <form id="frmCreateIHMRP">
                                    <div class="card-body">
                                        <div class="row">                                            
                                            
                                            <div class="form-group col-lg-3">
                                                <label for="sample_drawn_by">Sample Drawn By  </label>
                                                <select class="form-control select2" name="sample_drawn_by" id="sample_drawn_by" required
                                                    >
                                                    <option Value=""> Select</option>
                                                    <?php foreach ($RowUsersLists as $RowUsersList) {
                                                        ?>
                                                        <option Value="<?php echo $RowUsersList['USER_ID']; ?>" <?php echo ($ROWSWAB['sample_drawn_by']==$RowUsersList['USER_ID'])?"selected='selected'":''; ?>>
                                                            <?php echo $RowUsersList['USER_NAME']; ?>
                                                        </option>
                                                    <?php } ?>        
                                                </select>
                                            </div>                                                                                     

                                            <div class="form-group col-lg-3">
                                                <label for="sample_desc">Sample Collection Area </label>
                                                <select class="form-control select2" name="sample_collection_area" id="sample_collection_area" required
                                                    >
                                                    <option Value=""> Select</option>
                                                    <?php foreach($locationarr as $ldatakey => $ldataValue) {
                                                        ?>
                                                        <option Value="<?php echo $ldatakey; ?>" <?php echo ($ldatakey==$ROWSWAB['sample_collection_area'])?"selected='selected'":''; ?>>
                                                            <?php echo $ldataValue; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>                                                           
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="tap_no_for_water">Name Of The Food Worker Hand </label>
                                                <input required type="text" class="form-control" name="name_food_worker_hand" id="name_food_worker_hand"
                                                placeholder="Enter TPC RESULT" value="<?php  echo $ROWSWAB['name_food_worker_hand'] ?>">
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="analysed_by">Emp Id : </label>
                                                <select class="form-control select2" name="emp_id" id="emp_id" required
                                                    >
                                                    <option Value=""> Select</option>
                                                    <?php foreach ($RowUsersLists as $RowUsersList) {
                                                        ?>
                                                        <option Value="<?php echo $RowUsersList['USER_ID']; ?>" <?php echo ($ROWSWAB['emp_id']==$RowUsersList['USER_ID'])?"selected='selected'":''; ?>>
                                                            <?php echo $RowUsersList['USER_NAME']; ?>
                                                        </option>
                                                    <?php } ?>
                                                    
                                                </select>
                                            </div>
                                            
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="form-group col-lg">   
                                                <label for="Parameter">Parameter </label>
                                                <select class="form-control select2" name="tpc_paramater" id="tpc_paramater" required
                                                    onChange="getMedia('tpc_paramater','tpc_media','tpc_equipement')">
                                                    <option Value="0"> Select</option>
                                                    <?php 
                                                    foreach($parameter_mast_list as $RowParameterList){                                                    
                                                        ?>
                                                        <option Value="<?php echo $RowParameterList['ParameterID']; ?>" <?php echo ($ROWSWAB['tpc_paramater']==$RowParameterList['ParameterID'])?"selected='selected'":''; ?>>
                                                            <?php echo $RowParameterList['ParameterName']; ?>
                                                        </option>
                                                    <?php } ?>       
                                                </select>
                                            </div>

                                            <div class="form-group col-lg">
                                                <label for="tpc_media">Name Of The Media Used</label>
                                                <select class="form-control select2" name="tpc_media" id="tpc_media" required
                                                    onChange="getEquipment('tpc_media','tpc_equipement')" multiple>
                                                    <option Value=""> Select</option>
                                                    <?php 
                                                    $ROWSWAB['tpc_media'] = explode(",", $ROWSWAB['tpc_media']);
                                                    foreach($mediaMasterList[$ROWSWAB['tpc_paramater']] as $RowMediaList){                                                                                                                
                                                        ?>
                                                        <option Value="<?php echo $RowMediaList['MediaID']; ?>" <?php echo in_array($RowMediaList['MediaID'], (array)$ROWSWAB['tpc_media'])?"selected='selected'":''; ?> >
                                                            <?php echo $RowMediaList['MediaName']; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <div class="form-group col-lg">
                                                <label for="tpc_equipement">Equipment Used</label>
                                                <select class="form-control select2" name="tpc_equipement" id="tpc_equipement" required multiple 
                                                    >
                                                    <option Value=""> Select</option>
                                                    <?php 
                                                    $ROWSWAB['tpc_equipement'] = explode( "," , $ROWSWAB['tpc_equipement'] );
                                                    foreach( $ROWSWAB['tpc_media'] as $tpcmk => $tpcmv){
                                                        foreach($equpmentMasterList[$ROWSWAB['tpc_paramater']][$tpcmv] as $RoweqList){                                                                                                                
                                                            ?>
                                                            <option Value="<?php echo $RoweqList['InstrumentId']; ?>" <?php echo in_array($RoweqList['InstrumentId'],(array)$ROWSWAB['tpc_equipement'])?"selected='selected'":''; ?> >
                                                                <?php echo $RoweqList['InstrumentName']; ?>
                                                            </option>
                                                        <?php } 
                                                    } ?>
                                                </select>
                                            </div>

                                            <div class="form-group col-lg">
                                                <label for="tpc_result1">Tpc 35c </label>
                                                <input required type="text" class="form-control" name="tpc_result" id="tpc_result"
                                                placeholder="Enter TPC RESULT" value="<?php  echo $ROWSWAB['tpc_result'] ?>">
                                            </div>                                            
                                            
                                            <div class="form-group col-lg">
                                                <label for="tpc_completion_date">Completion Date </label>
                                                <input required="" type="date" value="<?php  echo $ROWSWAB['tpc_completion_date'] ?>" class="form-control" name="tpc_completion_date" id="tpc_completion_date" >           
                                            </div>
                                            
                                        </div>
                                        <hr>
                                        <div class="row">                                            

                                            <div class="form-group col-lg">   
                                                <label for="vcholerae_paramater">Parameter </label>
                                                <select class="form-control select2" name="vcholerae_paramater" id="vcholerae_paramater" required
                                                    onChange="getMedia('vcholerae_paramater','vcholerae_media','vcholerae_equipement')" >
                                                    <option Value="0"> Select</option>
                                                    <?php                                                     
                                                    foreach($parameter_mast_list as $RowParameterList){                                                    
                                                        ?>
                                                        <option Value="<?php echo $RowParameterList['ParameterID']; ?>" <?php echo ($ROWSWAB['vcholerae_paramater']==$RowParameterList['ParameterID'])?"selected='selected'":''; ?>>
                                                            <?php echo $RowParameterList['ParameterName']; ?>
                                                        </option>
                                                    <?php } ?>       
                                                </select>
                                            </div>

                                            <div class="form-group col-lg">
                                                <label for="vcholerae_media">Name Of The Media Used</label>
                                                <select class="form-control select2" name="vcholerae_media" id="vcholerae_media" required
                                                    onChange="getEquipment('vcholerae_media','vcholerae_equipement')" multiple>
                                                    <option Value=""> Select</option>
                                                    <?php 
                                                    $ROWSWAB['vcholerae_media'] = explode( ",", $ROWSWAB['vcholerae_media'] );
                                                    foreach($mediaMasterList[$ROWSWAB['vcholerae_paramater']] as $RowMediaList){                                                    
                                                            //echo "<pre>"; print_r($RowMediaList); exit;
                                                        ?>
                                                        <option Value="<?php echo $RowMediaList['MediaID']; ?>" <?php echo in_array($RowMediaList['MediaID'] , (array)$ROWSWAB['vcholerae_media'] )?"selected='selected'":''; ?>>
                                                            <?php echo $RowMediaList['MediaName']; ?>
                                                        </option>
                                                    <?php } ?>      
                                                </select>
                                            </div>

                                            <div class="form-group col-lg">
                                                <label for="vcholerae_equipement">Equipment Used</label>
                                                <select class="form-control select2" name="vcholerae_equipement" id="vcholerae_equipement" required multiple 
                                                    >
                                                    <option Value=""> Select</option>
                                                    <?php 
                                                    $ROWSWAB['vcholerae_equipement'] = explode( "," , $ROWSWAB['vcholerae_equipement'] );
                                                    foreach( $ROWSWAB['vcholerae_media'] as $vmk => $vmv ){
                                                        foreach($equpmentMasterList[$ROWSWAB['vcholerae_paramater']][$vmv] as $RoweqList){
                                                            ?>
                                                            <option Value="<?php echo $RoweqList['InstrumentId']; ?>" <?php echo in_array($RoweqList['InstrumentId'], (array)$ROWSWAB['vcholerae_equipement'])?"selected='selected'":''; ?> >
                                                                <?php echo $RoweqList['InstrumentName']; ?>
                                                            </option>
                                                        <?php } 
                                                    } ?>
                                                </select>
                                            </div>

                                            <div class="form-group col-lg">
                                                <label for="vcholerae_result">Vcholerae Coliforms</label>
                                                <input required type="text" class="form-control" name="vcholerae_result" id="vcholerae_result"
                                                placeholder="Vcholerae  RESULT1" value="<?php  echo $ROWSWAB['vcholerae_result'] ?>">
                                            </div>                                            

                                            <div class="form-group col-lg">
                                                <label for="vcholerae_completion_date">Completion Date  </label>
                                                <input required="" type="date" value="<?php  echo $ROWSWAB['vcholerae_completion_date'] ?>" class="form-control" name="vcholerae_completion_date" id="vcholerae_completion_date" placeholder="Enter Date of Sampling">           
                                            </div>
                                            
                                        </div>
                                        <hr>                                       
                                        
                                        <div class="row">    

                                            <div class="form-group col-lg-3">
                                                <label for="analysed_by">Analysed By : </label>
                                                <select class="form-control select2" name="analysed_by" id="analysed_by" required
                                                    >
                                                    <option Value=""> Select</option>
                                                    <?php foreach ($RowUsersLists as $RowUsersList) {
                                                        ?>
                                                        <option Value="<?php echo $RowUsersList['USER_ID']; ?>" <?php echo ($ROWSWAB['analysed_by']==$RowUsersList['USER_ID'])?"selected='selected'":''; ?>>
                                                            <?php echo $RowUsersList['USER_NAME']; ?>
                                                        </option>
                                                    <?php } ?>
                                                    
                                                </select>
                                            </div>
                                            
                                            <div class="form-group col-lg-3">
                                                <label for="checked_by">Checked By : </label>
                                                <select class="form-control select2" name="checked_by" id="checked_by" required
                                                    >
                                                    <option Value=""> Select</option>
                                                    <?php foreach ($RowUsersLists as $RowUsersList) {
                                                        ?>
                                                        <option Value="<?php echo $RowUsersList['USER_ID']; ?>" <?php echo ($ROWSWAB['checked_by']==$RowUsersList['USER_ID'])?"selected='selected'":''; ?>>
                                                            <?php echo $RowUsersList['USER_NAME']; ?>
                                                        </option>
                                                    <?php } ?>
                                                    
                                                </select>
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="verified_by">Verified By</label>
                                                <select class="form-control select2" name="verified_by" id="verified_by" required
                                                    >
                                                    <option Value=""> Select</option>
                                                    <?php foreach ($RowUsersLists as $RowUsersList) {
                                                        ?>
                                                        <option Value="<?php echo $RowUsersList['USER_ID']; ?>" <?php echo ($ROWSWAB['verified_by']==$RowUsersList['USER_ID'])?"selected='selected'":''; ?>>
                                                            <?php echo $RowUsersList['USER_NAME']; ?>
                                                        </option>
                                                    <?php } ?>
                                                    
                                                </select>
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="remarks">Remarks</label>
                                                <input required type="text" class="form-control" name="remarks" id="remarks"
                                                placeholder="Enter remarks" value="<?php  echo $ROWSWAB['remarks'] ?>">

                                            </div>

                                        </div>
                                        <hr>
                                        <div class="col-xs-12 "  align="right">
                                            <button type="submit"  name='submit' class="btn btn-success" id="submit">Submit</button>
                                        </div>
                                    </div>
                                </form>
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
                                                <th>Report Type </th>                         
                                                <th>Sample Collection Date </th>
                                                <th>Sample Drawn By </th>
                                                <th>Analysed By </th>
                                                <th>Checked By </th>
                                                <th>Varified By </th>
                                                <th>Action </th>                                                
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
        
        function getMedia(Parameter,NameMediaUsed,EquipmentUsed) {
            //GET MediaList
            $("#"+NameMediaUsed).html("");
            $("#"+EquipmentUsed).html("");
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

        
        function getEquipment(NameMediaUsed,EquipmentUsed){ 
            //GET EQUIPMENT USED
            $("#"+EquipmentUsed).html("");            
            var mediaVal = $("#"+NameMediaUsed).val().join(',');                
            if (mediaVal == 0) {
                return false;
            }
            $.ajax({
                url: 'Ajax/Ajax-inhouse-getmedia.php',
                type: 'POST',
                data: { mediaId: mediaVal, action: "getEquipmentList" },
                success: function(data) {                    
                    $("#"+EquipmentUsed).html(data);
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error: ", error);
                }
            });
        }

        $(document).ready(function() {
            ','
            $('#tpc_media').select2();
            $('#tpc_equipement').select2();
            $('#vcholerae_media').select2();
            $('#vcholerae_equipement').select2();

            var urlId = '<?php echo $urlId; ?> ';
            var fromDate = "<?php echo $_GET['FromDate']; ?>";
            var toDate = "<?php echo $_GET['ToDate']; ?>";

            if(urlId == 0){
                loadAdvances(fromDate,toDate);
            }else{
                $("#submit").text("Update");
            }
            
            $('#frmCreateIHMRP').submit(function() { 
                 event.preventDefault();                
                $.ajax({
                    url: 'Ajax/Ajax-ihmbrfwh.php',
                    type: 'POST',
                    data: {
                        methodType: "post",
                        action: "insert",                        
                        sample_drawn_by: $("#sample_drawn_by").val(),                          
                        sample_collection_area: $("#sample_collection_area").val(),                                                                        
                        name_food_worker_hand: $("#name_food_worker_hand").val(),
                        emp_id: $("#emp_id").val(),

                        tpc_paramater: $("#tpc_paramater").val(),
                        tpc_media: $("#tpc_media").val().join(','),
                        tpc_equipement: $("#tpc_equipement").val().join(','),
                        tpc_result: $("#tpc_result").val(),                        
                        tpc_completion_date: $("#tpc_completion_date").val(),

                        vcholerae_paramater: $("#vcholerae_paramater").val(),
                        vcholerae_media: $("#vcholerae_media").val().join(','),
                        vcholerae_equipement: $("#vcholerae_equipement").val().join(','),
                        vcholerae_result: $("#vcholerae_result").val(),                        
                        vcholerae_completion_date: $("#vcholerae_completion_date").val(),

                        analysed_by: $("#analysed_by").val(),
                        checked_by: $("#checked_by").val(),
                        verified_by: $("#verified_by").val(),
                        remarks: $("#remarks").val(),

                        id: urlId,


                    },
                    success: function (data) {  //return false;
                            var newUrl = "ihmbrfwh-create?FromDate="+fromDate+"&ToDate="+toDate;
                            window.location.href = newUrl;
                            return false;    
                        
                        // Reset the entire form
                        document.getElementById('frmCreateIHMRP').reset();

                        // Reset all Select2 dropdowns (including multi-select)
                        $('#frmCreateIHMRP select.select2').val(null).trigger('change');

                        //var newUrl = "in-house-microbiology-report-for-product";
                        //window.location.href = newUrl;
                        return false;
                    }                            
                    
                });
            });            

            
            $(document).on("click", ".btnDelete", function () {
                var selectedID = $(this).attr("data-id");
                var r = confirm("Are You Sure Do You Want To Delete?");
                if (r == true) {
                    $.ajax({
                        url: 'Ajax/Ajax-ihmbrfwh.php',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            'id': selectedID, // Pass LotIds as an array
                            'action': 'Delete'
                        },
                        success: function (response) {
                            alert("Deleted successfully");
                            var newUrl = "ihmbrfwh-create?FromDate="+fromDate+"&ToDate="+toDate;
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

    function loadAdvances(fromDate,toDate) {

            $('#ATT_DATA').show();
        //var fromDate = $('#fromDate').val();
        //var toDate = $('#toDate').val();        
        
        if ($.fn.DataTable.isDataTable('#mytable-data')) {
            $('#mytable-data').DataTable().destroy();
        }

        table = "";     
        table = $('#mytable-data').DataTable({                                  
            serverSide: true,
            ajax: {
                url: 'Ajax/Ajihmbrfwhlist',
                type: 'POST',
                data: function(d) { 
                    d.fromDate = fromDate;
                    d.toDate = toDate; 
                    d.fromPage='Create';                   

                }
            },
            columns: [
                { data: 'Sno' },
                { data: 'name_food_worker_hand' },                
                { data: 'sample_collection_area' },
                { data: 'sample_drawn_by' },                
                { data: 'analysed_by' },
                { data: 'checked_by' },
                { data: 'verified_by' },
                { data: 'Action' }
                /*{ data: 'Parameter' },
                { data: 'NameMediaUsed' },
                { data: 'EquipmentUsed' },
                { data: 'TpcResult' },
                { data: 'EcoliResult' },
                { data: 'SaureusResult' },
                { data: 'VcholeraeResult' },
                { data: 'VparahaemolyticusResult' },
                { data: 'Salmonella' },
                { data: 'CheckedBy' },
                { data: 'VerfiedBy' },
                { data: 'Remarks' }*/
            ],
            columnDefs: [
                /*{ targets: [2], orderable: false, searchable: false },
                { targets: [3], orderable: false, searchable: false },
                { targets: [4], orderable: false, searchable: false },                */

            ],
            paging: true,
            pageLength: 10,
            lengthMenu: [[10,50,100], [10,50,100]],
            scrollX: true,
            scrollY: '500px',
            scrollCollapse: true,
            autoWidth: false,
            responsive: false,
            searching: false,
            ordering:false            

        });
    }
    </script>      
</body>
</html>