<?php
include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");
include($cp . "Includes/IncludePage.php");

$PageTitleFolder = "Quality Control";
$PageTitle = "IN HOUSE MICROBIOLOGY REPORT FOR SWAB FOR FOOD CONTACT SURFACES Create";

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

//Medaimaters list
$SqlMediaList = "SELECT MediaID,MediaName FROM `usedmediamaster`";
$StmtMediaList = $iconn->query($SqlMediaList);
$Media_mast_list = array();
while ($RowMediaList = $StmtMediaList->fetch_array()) {
    $Media_mast_list[] = $RowMediaList;
}
//end here

$mediaMasterList = array();
$equpmentMasterList = array();

$urlId = (isset($_GET['id']) && $_GET['id']) ? $_GET['id'] : 0;
$ROWSWAB =  array();
if ($urlId > 0) {
    $sqlGetRecord = " select * from micro_biology_workers_food_surface  where mbwfs_id= '" . $urlId . "' ";
    $stmturlresults = $iconn->query($sqlGetRecord);
    if ($stmturlresults) {

        $ROWSWAB = $stmturlresults->fetch_array();
        $getMediaquery = " select ParameterID,MediaID,MediaName 
            from usedmediamaster 
            where 
                ParameterID in (
                " . $ROWSWAB['tpc_paramater'] . ",
                " . $ROWSWAB['vcholerae_paramater'] . "
            ) ";
        $stmtGetMedia = $iconn->query($getMediaquery);
        if ($stmtGetMedia) {
            while ($rowMedia = $stmtGetMedia->fetch_array()) {
                $mediaMasterList[$rowMedia['ParameterID']][] = $rowMedia;
            }
        }

        $getEquipmentquery = " select InstrumentId, ParameterID, MediaID, InstrumentName
            from instrumentmaster ";
        $stmtGetEquipment = $iconn->query($getEquipmentquery);
        if ($stmtGetEquipment) {
            while ($rowEquipment = $stmtGetEquipment->fetch_array()) {
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
    "OUTFEED - ARICO" => "OUTFEED - ARICO",
    "OUTFEED - GLORY" => "OUTFEED - GLORY",
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

$foodcsarr = array(
    "BOX" => "BOX",
    "NET" => "NET",
    "TABLE" => "TABLE",
    "WEIGHING NET" => "WEIGHING NET",
    "TROLLEY" => "TROLLEY",
    "GRADING MACHINE" => "GRADING MACHINE",
    "BASIN" => "BASIN",
    "CONVEYOUR BELT" => "CONVEYOUR BELT",
    "PAN" => "PAN",
    "SHOVELS" => "SHOVELS",
    "SOAKING SQUARE " => "SOAKING SQUARE ",
    "SHORTING TABLE " => "SHORTING TABLE ",
    "DE PANING MACHINES" => "DE PANING MACHINES",
    "PLATE FREEZER - 1" => "PLATE FREEZER - 1",
    "PLATE FREEZER - 2" => "PLATE FREEZER - 2",
    "PLATE FREEZER - 3" => "PLATE FREEZER - 3",
    "PLATE FREEZER - 4" => "PLATE FREEZER - 4",
    "COUNT CHECKING TABLE" => "COUNT CHECKING TABLE",
    "FREEZER PAN" => "FREEZER PAN",
    "CRATES" => "CRATES",
    "GRADS CHECKING TABLE" => "GRADS CHECKING TABLE",
    "SOAKING STIRRER" => "SOAKING STIRRER",
    "FILTH WASH MACHINE" => "FILTH WASH MACHINE",
    "ICE TROLLY" => "ICE TROLLY",
    "PACKING TABLE" => "PACKING TABLE",
    "POLY POUCHES" => "POLY POUCHES",
    "APRON" => "APRON",
    "GLOVES" => "GLOVES",
    "HAND DRYER" => "HAND DRYER",
    "AIR TEST" => "AIR TEST",
    "BASINS" => "BASINS",
    "UTENSILS" => "UTENSILS",
    "WEIGHING BALANCE" => "WEIGHING BALANCE",
   
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

                                            <div class="form-group col-lg-4">
                                                <label for="sample_drawn_by">Sample Drawn By </label>
                                                <select class="form-control select2" name="sample_drawn_by" id="sample_drawn_by" required>
                                                    <option Value=""> Select</option>
                                                    <?php foreach ($RowUsersLists as $RowUsersList) {
                                                        if ($RowUsersList['USER_NAME'] == 'APEX') {
                                                            continue;
                                                        }
                                                    ?>
                                                        <option Value="<?php echo $RowUsersList['USER_ID']; ?>" <?php echo ($ROWSWAB['sample_drawn_by'] == $RowUsersList['USER_ID']) ? "selected='selected'" : ''; ?>>
                                                            <?php echo $RowUsersList['USER_NAME']; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <div class="form-group col-lg-4">
                                                <label for="sample_desc">Sample Collection Area </label>
                                                <select class="form-control select2" name="sample_collection_area" id="sample_collection_area" required>
                                                    <option Value=""> Select</option>
                                                    <?php foreach ($locationarr as $ldatakey => $ldataValue) {
                                                    ?>
                                                        <option Value="<?php echo $ldatakey; ?>" <?php echo ($ldatakey == $ROWSWAB['sample_collection_area']) ? "selected='selected'" : ''; ?>>
                                                            <?php echo $ldataValue; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <div class="form-group col-lg-4">
                                                <label for="tap_no_for_water">Name Of The Food Contact Surface </label>
                                                <select class="form-control select2" name="name_food_contact_surface" id="name_food_contact_surface" required>
                                                    <option Value=""> Select</option>
                                                    <?php foreach ($foodcsarr as $ldatakey => $ldataValue) {
                                                    ?>
                                                        <option Value="<?php echo $ldatakey; ?>" <?php echo ($ldatakey == $ROWSWAB['name_food_contact_surface']) ? "selected='selected'" : ''; ?>>
                                                            <?php echo $ldataValue; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>

                                        </div>
                                        <div class="row">

                                            <div class="form-group col-lg-4">
                                                <label for="analysed_by">Sample Collection Date : </label>

                                                <input required type="date" value="<?php echo $ROWSWAB['sample_collection_date'] ?>" class="form-control"
                                                    name='sample_collection_date' id="sample_collection_date"
                                                    placeholder="Enter Date of Sampling">
                                            </div>

                                            <div class="form-group col-lg-4">
                                                <label for="AnalysisDate">Analysis Date </label>
                                                <input required type="date" value="<?php echo $ROWSWAB['AnalysisDate'] ?>" class="form-control"
                                                    name='AnalysisDate' id="AnalysisDate"
                                                    placeholder="Enter Date of Sampling">
                                            </div>

                                            <div class="form-group col-lg-4">
                                                <label for="exampleInputEmail1">Date Of Completion</label>
                                                <input required type="date" value="<?php echo $ROWSWAB['Dateofcompletion'] ?>" class="form-control"
                                                    name='Dateofcompletion' id="Dateofcompletion"
                                                    placeholder="Enter Date">
                                            </div>

                                        </div>
                                        <div id="hideforupdate">

                                            <!-- ================== TPC PARAMETER ================== -->
                                            <div class="param-section" data-param="tpc">
                                                <div class="param-entry">
                                                    <div class="row">
                                                        <div class="form-group col-lg">
                                                            <h6>Parameter: Tpc </h6>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="form-group col-lg">
                                                            <label for="tpc_media">Name Of The Media Used</label>
                                                            <select class="form-control select2" name="tpc_media" id="tpc_media" required
                                                                onChange="getEquipment('tpc_media','tpc_equipement'); getKits('tpc_media','tpc_Kitlotno');">
                                                                <option Value=""> Select</option>
                                                                <?php foreach ($Media_mast_list as $RowMediaList) { ?>
                                                                    <option Value="<?php echo $RowMediaList['MediaID']; ?>" <?php echo ($RowMediaList['MediaID'] == $ROWSWAB['tpc_media']) ? "selected='selected'" : ''; ?>>
                                                                        <?php echo $RowMediaList['MediaName']; ?>
                                                                    </option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>

                                                        <div class="form-group col">
                                                            <label for="tpc_Kitlotno">Lot No</label>
                                                            <select class="form-control select2" name="tpc_Kitlotno" id="tpc_Kitlotno" required
                                                                onChange="getBalance('tpc_Kitlotno','tpc_OpeningWells','tpc_BalanceWells','tpc_KitOpeningDate')" multiple>
                                                                <option Value=""> Select</option>
                                                                <?php
                                                                $ROWSWAB['tpc_Kitlotno'] = explode(',', $ROWSWAB['tpc_Kitlotno']);
                                                                foreach ($mediaMasterList[$ROWSWAB['Parameter']] as $RowMediaList) {
                                                                ?>
                                                                    <option Value="<?php echo $RowMediaList['MediaID']; ?>" <?php echo in_array($RowMediaList['MediaID'], (array)$ROWSWAB['tpc_Kitlotno']) ? "selected='selected'" : ''; ?>>
                                                                        <?php echo $RowMediaList['MediaName']; ?>
                                                                    </option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>

                                                        <div class="form-group col">
                                                            <label for="tpc_KitOpeningDate">Opening Date</label>
                                                            <input required type="Date" class="form-control" name="tpc_KitOpeningDate" id="tpc_KitOpeningDate">
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="form-group col" style="display:block">
                                                            <label for="tpc_OpeningWells">Total Qty</label>
                                                            <input required type="text" class="form-control" name="tpc_OpeningWells" id="tpc_OpeningWells" placeholder="" readonly="">
                                                        </div>

                                                        <div class="form-group col">
                                                            <label for="tpc_UsedWells">Usage Qty</label>
                                                            <input required type="number" step="any" class="form-control" name="tpc_UsedWells" id="tpc_UsedWells" placeholder="" onkeyup="checkVal('tpc_UsedWells','tpc_BalanceWells','tpc_Kitlotno')">
                                                        </div>

                                                        <div class="form-group col">
                                                            <label for="tpc_BalanceWells">Balance Qty</label>
                                                            <input required type="number" step="any" class="form-control" name="tpc_BalanceWells" id="tpc_BalanceWells" placeholder="" readonly="">
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="form-group col-lg-4">
                                                            <label for="tpc_equipement">Equipment Used</label>
                                                            <select class="form-control select2" name="tpc_equipement" id="tpc_equipement" required multiple>
                                                                <option Value=""> Select</option>
                                                                <?php
                                                                $ROWSWAB['tpc_equipement'] = explode(",", $ROWSWAB['tpc_equipement']);
                                                                foreach ($ROWSWAB['tpc_media'] as $tpcmk => $tpcmv) {
                                                                    foreach ($equpmentMasterList[$ROWSWAB['tpc_paramater']][$tpcmv] as $RoweqList) {
                                                                ?>
                                                                        <option Value="<?php echo $RoweqList['InstrumentId']; ?>" <?php echo in_array($RoweqList['InstrumentId'], (array)$ROWSWAB['tpc_equipement']) ? "selected='selected'" : ''; ?>>
                                                                            <?php echo $RoweqList['InstrumentName']; ?>
                                                                        </option>
                                                                <?php }
                                                                } ?>
                                                            </select>
                                                        </div>

                                                        <div class="form-group col-lg-4">
                                                            <label for="tpc_result1">TPC AT 37°C </label>
                                                            <input required type="text" class="form-control" name="tpc_result" id="tpc_result" placeholder="" value="<?php echo $ROWSWAB['tpc_result'] ?>">
                                                        </div>

                                                        <div class="form-group col-lg">
                                                            <label for="tpc_completion_date">Completion Date </label>
                                                            <input required="" type="date" value="<?php echo $ROWSWAB['tpc_completion_date'] ?>" class="form-control" name="tpc_completion_date" id="tpc_completion_date">
                                                        </div>
                                                    </div>

                                                    <!-- ADD BUTTON ROW -->
                                                    <div class="row addBtnRow mt-2" style="text-align: right;">
                                                        <div class="col-lg">
                                                            <button type="button" class="btn btn-primary addParamBtn" data-param="tpc">+ Add</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <hr>

                                            <!-- ================== V. CHOLERAE PARAMETER ================== -->
                                            <div class="param-section" data-param="vcholerae" style="display:none;">
                                                <div class="param-entry">
                                                    <div class="row">
                                                        <div class="form-group col-lg">
                                                            <h6>Parameter: V. CHOLERAE </h6>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="form-group col-lg">
                                                            <label for="vcholerae_media">Name Of The Media Used</label>
                                                            <select class="form-control select2" name="vcholerae_media" id="vcholerae_media"
                                                                onChange="getEquipment('vcholerae_media','vcholerae_equipement'); getKits('vcholerae_media','vcholerae_Kitlotno');">
                                                                <option Value=""> Select</option>
                                                                <?php foreach ($Media_mast_list as $RowMediaList) { ?>
                                                                    <option Value="<?php echo $RowMediaList['MediaID']; ?>" <?php echo ($RowMediaList['MediaID'] == $ROWSWAB['vcholerae_media']) ? "selected='selected'" : ''; ?>>
                                                                        <?php echo $RowMediaList['MediaName']; ?>
                                                                    </option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>

                                                        <div class="form-group col">
                                                            <label for="vcholerae_Kitlotno">Lot No</label>
                                                            <select class="form-control select2" name="vcholerae_Kitlotno" id="vcholerae_Kitlotno" multiple
                                                                onChange="getBalance('vcholerae_Kitlotno','vcholerae_OpeningWells','vcholerae_BalanceWells','vcholerae_KitOpeningDate')">
                                                                <option Value=""> Select</option>
                                                                <?php
                                                                $ROWSWAB['vcholerae_Kitlotno'] = explode(',', $ROWSWAB['vcholerae_Kitlotno']);
                                                                foreach ($mediaMasterList[$ROWSWAB['Parameter']] as $RowMediaList) {
                                                                ?>
                                                                    <option Value="<?php echo $RowMediaList['MediaID']; ?>" <?php echo in_array($RowMediaList['MediaID'], (array)$ROWSWAB['vcholerae_Kitlotno']) ? "selected='selected'" : ''; ?>>
                                                                        <?php echo $RowMediaList['MediaName']; ?>
                                                                    </option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>

                                                        <div class="form-group col">
                                                            <label for="vcholerae_KitOpeningDate">Opening Date</label>
                                                            <input type="Date" class="form-control" name="vcholerae_KitOpeningDate" id="vcholerae_KitOpeningDate">
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="form-group col" style="display:block">
                                                            <label for="vcholerae_OpeningWells">Total Qty</label>
                                                            <input type="text" class="form-control" name="vcholerae_OpeningWells" id="vcholerae_OpeningWells" placeholder="" readonly="">
                                                        </div>

                                                        <div class="form-group col">
                                                            <label for="vcholerae_UsedWells">Usage Qty</label>
                                                            <input type="number" step="any" class="form-control" name="vcholerae_UsedWells" id="vcholerae_UsedWells" placeholder="" onkeyup="checkVal('vcholerae_UsedWells','vcholerae_BalanceWells','vcholerae_Kitlotno')">
                                                        </div>

                                                        <div class="form-group col">
                                                            <label for="vcholerae_BalanceWells">Balance Qty</label>
                                                            <input type="number" step="any" class="form-control" name="vcholerae_BalanceWells" id="vcholerae_BalanceWells" placeholder="" readonly="">
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="form-group col-lg">
                                                            <label for="vcholerae_equipement">Equipment Used</label>
                                                            <select class="form-control select2" name="vcholerae_equipement" id="vcholerae_equipement" multiple>
                                                                <option Value=""> Select</option>
                                                                <?php
                                                                $ROWSWAB['vcholerae_equipement'] = explode(",", $ROWSWAB['vcholerae_equipement']);
                                                                foreach ($ROWSWAB['vcholerae_media'] as $vmk => $vmv) {
                                                                    foreach ($equpmentMasterList[$ROWSWAB['vcholerae_paramater']][$vmv] as $RoweqList) {
                                                                ?>
                                                                        <option Value="<?php echo $RoweqList['InstrumentId']; ?>" <?php echo in_array($RoweqList['InstrumentId'], (array)$ROWSWAB['vcholerae_equipement']) ? "selected='selected'" : ''; ?>>
                                                                            <?php echo $RoweqList['InstrumentName']; ?>
                                                                        </option>
                                                                <?php }
                                                                } ?>
                                                            </select>
                                                        </div>

                                                        <div class="form-group col-lg">
                                                            <label for="vcholerae_result">V. Cholerae</label>
                                                            <input type="text" class="form-control" name="vcholerae_result" id="vcholerae_result" placeholder="" value="<?php echo $ROWSWAB['vcholerae_result'] ?>">
                                                        </div>

                                                        <div class="form-group col-lg">
                                                            <label for="vcholerae_completion_date">Completion Date </label>
                                                            <input type="date" value="<?php echo $ROWSWAB['vcholerae_completion_date'] ?>" class="form-control" name="vcholerae_completion_date" id="vcholerae_completion_date" placeholder="">
                                                        </div>
                                                    </div>

                                                    <!-- ADD BUTTON ROW -->
                                                    <div class="row addBtnRow mt-2">
                                                        <div class="col-lg">
                                                            <button type="button" class="btn btn-primary addParamBtn" data-param="vcholerae">Add</button>
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                            <!-- <hr> -->
                                        </div>

                                        <div class="row">

                                            <div class="form-group col-lg-3">
                                                <label for="analysed_by">Analysed By : </label>
                                                <select class="form-control select2" name="analysed_by" id="analysed_by" required>
                                                    <option Value=""> Select</option>
                                                    <?php foreach ($RowUsersLists as $RowUsersList) {
                                                        if ($RowUsersList['USER_NAME'] == 'APEX') {
                                                            continue;
                                                        }
                                                    ?>
                                                        <option Value="<?php echo $RowUsersList['USER_ID']; ?>" <?php echo ($ROWSWAB['analysed_by'] == $RowUsersList['USER_ID']) ? "selected='selected'" : ''; ?>>
                                                            <?php echo $RowUsersList['USER_NAME']; ?>
                                                        </option>
                                                    <?php } ?>

                                                </select>
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="checked_by">Checked By : </label>
                                                <select class="form-control select2" name="checked_by" id="checked_by" required>
                                                    <option Value=""> Select</option>
                                                    <?php foreach ($RowUsersLists as $RowUsersList) {
                                                        if ($RowUsersList['USER_NAME'] == 'APEX') {
                                                            continue;
                                                        }
                                                    ?>
                                                        <option Value="<?php echo $RowUsersList['USER_ID']; ?>" <?php echo ($ROWSWAB['checked_by'] == $RowUsersList['USER_ID']) ? "selected='selected'" : ''; ?>>
                                                            <?php echo $RowUsersList['USER_NAME']; ?>
                                                        </option>
                                                    <?php } ?>

                                                </select>
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="verified_by">Verified By</label>
                                                <select class="form-control select2" name="verified_by" id="verified_by" required>
                                                    <option Value=""> Select</option>
                                                    <?php foreach ($RowUsersLists as $RowUsersList) {
                                                        if ($RowUsersList['USER_NAME'] == 'APEX') {
                                                            continue;
                                                        }
                                                    ?>
                                                        <option Value="<?php echo $RowUsersList['USER_ID']; ?>" <?php echo ($ROWSWAB['verified_by'] == $RowUsersList['USER_ID']) ? "selected='selected'" : ''; ?>>
                                                            <?php echo $RowUsersList['USER_NAME']; ?>
                                                        </option>
                                                    <?php } ?>

                                                </select>
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="remarks">Remarks</label>
                                                <input required type="text" class="form-control" name="remarks" id="remarks"
                                                    placeholder="" value="<?php echo $ROWSWAB['remarks'] ?>">

                                            </div>

                                        </div>
                                        <hr>
                                        <div class="col-xs-12 " align="right">
                                             <button class="btn btn-danger" type="button" id="closeTab">Close</button>
                                            <button type="submit" name='submit' class="btn btn-success" id="submit">Submit</button>
                                            <button type="button" name='submit' class="btn btn-success" id="updateit" style="display:none">Update</button>
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
                                    <table id="mytable-data" class="table dt-responsive nowrap w-100">
                                        <thead>
                                            <tr>
                                                <th>Sno </th>
                                                <th>Report Type </th>
                                                <th>Sample Collection Date </th>
                                                <th>Sample Drawn By </th>
                                                <th>Result</th>
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
         $('#closeTab').on('click', function () {
                window.close(); // Works only if opened via window.open()
            });
        function getEquipment(NameMediaUsed, EquipmentUsed) {
            //GET EQUIPMENT USED
            $("#" + EquipmentUsed).html("");
            var mediaVal = $("#" + NameMediaUsed).val();
            if (mediaVal == 0) {
                return false;
            }
            $.ajax({
                url: 'Ajax/Ajax-inhouse-getmedia.php',
                type: 'POST',
                data: {
                    mediaId: mediaVal,
                    action: "getEquipmentList"
                },
                success: function(data) {
                    $("#" + EquipmentUsed).html(data);
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error: ", error);
                }
            });
        }

        function getKits(NameMediaUsed, Kitlotno) {
            //GET Kits Nos
            $("#" + Kitlotno).html("");
            let mediaVal = $("#" + NameMediaUsed).val();
            if (mediaVal == 0) {
                return false;
            }
            $.ajax({
                url: 'Ajax/Ajax-inhouse-getmedia.php',
                type: 'POST',
                data: {
                    mediaId: mediaVal,
                    action: "getMediaKitsList",
                    fromPage: "ihmbrfpc"
                },
                success: function(data) {
                    $("#" + Kitlotno).html(data);
                },
                error: function(xhr, status, error) {
                    console.error("AJAX Error: ", error);
                }
            });
        }

        const kitBalances = {};

        // const kitBalances = {};

        function getBalance(Kitlotno, OpeningWells, BalanceWells, KitOpeningDate) {
            const kitlotnos = $("#" + Kitlotno).val();
            if (!kitlotnos || !Array.isArray(kitlotnos) || kitlotnos.length === 0) {
                return false;
            }

            const KitlotnoStr = kitlotnos.join(',');

            $.ajax({
                url: 'Ajax/Ajax-inhouse-getmedia.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    Kitlotno: KitlotnoStr,
                    action: "getBalance",
                    fromPage: "ihmbrfpc"
                },
                success: function(response) {
                    if (!response.Error && response.data) {
                        const openingWells = parseFloat(response.data.OpeningWells || 0);
                        const balanceWells = parseFloat(response.data.BalanceWells || 0);
                        const mKitOpeningDate = response.data.mKitOpeningDate;

                        kitlotnos.forEach(function(lot) {
                            kitBalances[lot] = {
                                opening: openingWells,
                                balance: balanceWells
                            };
                        });

                        let lot = kitlotnos[0];
                        let previousBalance = null;

                        // 🔹 Find the last balance for this lot in all visible rows
                        $("[id*='Kitlotno']").each(function() {
                            const selected = $(this).val();
                            if (!selected || !Array.isArray(selected) || !selected.includes(lot)) return;
                            if (this.id === Kitlotno) return; // skip self

                            const baseId = this.id.replace("Kitlotno", "");
                            const bal = parseFloat($("#" + baseId + "BalanceWells").val());

                            if (!isNaN(bal)) {
                                previousBalance = bal; //  always overwrite → ensures last visible balance
                            }
                        });

                        // Use the last visible balance if found
                        const openingToUse = previousBalance !== null ? previousBalance : balanceWells;
                        $("#" + OpeningWells).val(openingToUse);
                        $("#" + BalanceWells).val(openingToUse);

                        $("#" + KitOpeningDate).val(
                            mKitOpeningDate || new Date().toISOString().split('T')[0]
                        );

                        if (lot) recalculateAllBalances(lot);

                    } else {
                        console.error("Error in response:", response.Message);
                    }
                },
                error: function(xhr, status, error) {
                    console.error("AJAX error:", error);
                }
            });
        }



        function getTotalUsedForKit(kitlotno) {
            let total = 0;

            $("[id$='Kitlotno']").each(function() {
                const selected = $(this).val();
                if (!selected || !selected.includes(kitlotno)) return;

                const baseId = this.id.replace("Kitlotno", "");
                const used = parseFloat($("#" + baseId + "UsedWells").val()) || 0;
                total += used;
            });

            return total;
        }



        function recalculateAllBalances(kitlotno) {
            const original = kitBalances[kitlotno];
            if (!original) return;

            let remaining = original.balance;
            let rowIndex = 1;

            $("[id*='Kitlotno']").each(function() {
                let selectedLots = $(this).val();
                if (!selectedLots) return;
                if (!Array.isArray(selectedLots)) selectedLots = [selectedLots];
                if (!selectedLots.includes(kitlotno)) return;

                //  Handle baseId dynamically
                const {
                    baseId,
                    suffix
                } = splitId(this.id, "Kitlotno");

                const usedInput = $("#" + baseId + "UsedWells" + suffix);
                const openingInput = $("#" + baseId + "OpeningWells" + suffix);
                const balanceInput = $("#" + baseId + "BalanceWells" + suffix);

                const usedWells = parseFloat(usedInput.val()) || 0;

                openingInput.val(remaining);
                const updatedBalance = Math.max(remaining - usedWells, 0);
                balanceInput.val(updatedBalance);

                console.log(
                    `Lot ${kitlotno} row ${rowIndex}: Used ${usedWells}, Opening ${remaining}, Balance ${updatedBalance}`
                );

                remaining = updatedBalance;
                rowIndex++;
            });
        }

        function checkVal(UsedWells, BalanceWells, kitlotnoId) {
            const usedWellsInput = document.getElementById(UsedWells);
            const balanceWellsInput = document.getElementById(BalanceWells);
            let balanceWells = $("#" + BalanceWells).val();
            let usedWells = $("#" + UsedWells).val();

            {
                //$("#"+BalanceWells).val(balanceWells-usedWells);
                const kitlotnos = $("#" + kitlotnoId).val(); // multi-select returns array

                if (Array.isArray(kitlotnos)) {
                    kitlotnos.forEach(function(lot) {
                        recalculateAllBalances(lot, usedWellsInput);
                    });
                }

                const lot = $("#" + kitlotnoId).val()?.[0]; // assuming single kit per block
                if (lot && kitBalances[lot]) {
                    const totalUsed = getTotalUsedForKit(lot);
                    const remaining = kitBalances[lot].balance - totalUsed;
                    balanceWellsInput.value = Math.max(remaining, 0).toFixed(2);
                    if (remaining < 0) {
                        alert("UsedWells should not exceed balanceWells");
                    }
                }
            }


        }

        $(document).ready(function() {
            $('#tpc_media').select2();
            $('#tpc_Kitlotno').select2();
            $('#tpc_equipement').select2();
            $('#vcholerae_media').select2();
            $('#vcholerae_Kitlotno').select2();
            $('#vcholerae_equipement').select2();

            var urlId = '<?php echo $urlId; ?> ';
            var fromDate = "<?php echo $_GET['FromDate']; ?>";
            var toDate = "<?php echo $_GET['ToDate']; ?>";

            if (urlId == 0) {
                loadAdvances(fromDate, toDate);
            } else {
                $("#submit").hide();
                $("#updateit").show();
                $("#hideforupdate").hide();
            }

            $(document).on("click", "#updateit", function() {
                event.preventDefault();
                $.ajax({
                    url: 'Ajax/Ajax-ihmbrfwfs.php',
                    type: 'POST',
                    data: {
                        methodType: "post",
                        action: "insert",
                        sample_drawn_by: $("#sample_drawn_by").val(),
                        AnalysisDate: $("#AnalysisDate").val(),
                        Dateofcompletion: $("#Dateofcompletion").val(),
                        sample_collection_area: $("#sample_collection_area").val(),
                        name_food_contact_surface: $("#name_food_contact_surface").val(),
                        sample_collection_date: $("#sample_collection_date").val(),

                        analysed_by: $("#analysed_by").val(),
                        checked_by: $("#checked_by").val(),
                        verified_by: $("#verified_by").val(),
                        remarks: $("#remarks").val(),

                        id: urlId || 0
                    },
                     success: function(data) { //return false;
                        var newUrl = "ihmbrfwfs-create?FromDate=" + fromDate + "&ToDate=" + toDate;
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

            // $('#frmCreateIHMRP').submit(function() {
            //     event.preventDefault();
            //     $.ajax({
            //         url: 'Ajax/Ajax-ihmbrfwfs.php',
            //         type: 'POST',
            //         data: {
            //             methodType: "post",
            //             action: "insert",
            //             sample_drawn_by: $("#sample_drawn_by").val(),
            //             AnalysisDate: $("#AnalysisDate").val(),
            //             Dateofcompletion: $("#Dateofcompletion").val(),
            //             sample_collection_area: $("#sample_collection_area").val(),
            //             name_food_contact_surface: $("#name_food_contact_surface").val(),
            //             sample_collection_date: $("#sample_collection_date").val(),

            //             tpc_media: $("#tpc_media").val(),
            //             tpc_Kitlotno: $("#tpc_Kitlotno").val().join(','),
            //             tpc_KitOpeningDate: $("#tpc_KitOpeningDate").val(),
            //             tpc_OpeningWells: $("#tpc_OpeningWells").val(),
            //             tpc_UsedWells: $("#tpc_UsedWells").val(),
            //             tpc_BalanceWells: $("#tpc_BalanceWells").val(),
            //             tpc_equipement: $("#tpc_equipement").val().join(','),
            //             tpc_result: $("#tpc_result").val(),
            //             tpc_completion_date: $("#tpc_completion_date").val(),

            //             vcholerae_media: $("#vcholerae_media").val(),
            //             vcholerae_Kitlotno: $("#vcholerae_Kitlotno").val().join(','),
            //             vcholerae_KitOpeningDate: $("#vcholerae_KitOpeningDate").val(),
            //             vcholerae_OpeningWells: $("#vcholerae_OpeningWells").val(),
            //             vcholerae_UsedWells: $("#vcholerae_UsedWells").val(),
            //             vcholerae_BalanceWells: $("#vcholerae_BalanceWells").val(),
            //             vcholerae_equipement: $("#vcholerae_equipement").val().join(','),
            //             vcholerae_result: $("#vcholerae_result").val(),
            //             vcholerae_completion_date: $("#vcholerae_completion_date").val(),

            //             analysed_by: $("#analysed_by").val(),
            //             checked_by: $("#checked_by").val(),
            //             verified_by: $("#verified_by").val(),
            //             remarks: $("#remarks").val(),

            //             id: urlId,


            //         },
            //         success: function(data) { //return false;
            //             var newUrl = "ihmbrfwfs-create?FromDate=" + fromDate + "&ToDate=" + toDate;
            //             window.location.href = newUrl;
            //             return false;

            //             // Reset the entire form
            //             document.getElementById('frmCreateIHMRP').reset();

            //             // Reset all Select2 dropdowns (including multi-select)
            //             $('#frmCreateIHMRP select.select2').val(null).trigger('change');

            //             //var newUrl = "in-house-microbiology-report-for-product";
            //             //window.location.href = newUrl;
            //             return false;
            //         }

            //     });
            // });


            $('#frmCreateIHMRP').submit(function(event) {
                event.preventDefault();

                function collectDynamicEntries(param) {
                    var entries = [];
                    $(`.param-section[data-param="${param}"] .param-entry`).each(function() {
                        entries.push({
                            NameMediaUsed: $(this).find(`[name="${param}_media"]`).val() || "",
                            Kitlotno: $(this).find(`[name="${param}_Kitlotno"]`).val() || [],
                            KitOpeningDate: $(this).find(`[name="${param}_KitOpeningDate"]`).val() || "",
                            OpeningWells: $(this).find(`[name="${param}_OpeningWells"]`).val() || "",
                            UsedWells: $(this).find(`[name="${param}_UsedWells"]`).val() || "",
                            BalanceWells: $(this).find(`[name="${param}_BalanceWells"]`).val() || "",
                            Equipement: $(this).find(`[name="${param}_equipement"]`).val() || [],
                            Result: $(this).find(`[name="${param}_result"]`).val() || "",
                            CompletionDate: $(this).find(`[name="${param}_completion_date"]`).val() || ""
                        });
                    });
                    return entries;
                }

                var tpcDynamic = collectDynamicEntries("tpc");
                // var vcDynamic = collectDynamicEntries("vcholerae");

                $.ajax({
                    url: 'Ajax/Ajax-ihmbrfwfs.php',
                    type: 'POST',
                    data: {
                        methodType: "post",
                        action: "insert",
                        sample_drawn_by: $("#sample_drawn_by").val(),
                        AnalysisDate: $("#AnalysisDate").val(),
                        Dateofcompletion: $("#Dateofcompletion").val(),
                        sample_collection_area: $("#sample_collection_area").val(),
                        name_food_contact_surface: $("#name_food_contact_surface").val(),
                        sample_collection_date: $("#sample_collection_date").val(),
                        tpc_media: $("#tpc_media").val(),
                        tpc_Kitlotno: Array.isArray($("#tpc_Kitlotno").val()) ? $("#tpc_Kitlotno").val().join(',') : "",
                        tpc_KitOpeningDate: $("#tpc_KitOpeningDate").val(),
                        tpc_OpeningWells: $("#tpc_OpeningWells").val(),
                        tpc_UsedWells: $("#tpc_UsedWells").val(),
                        tpc_BalanceWells: $("#tpc_BalanceWells").val(),
                        tpc_equipement: Array.isArray($("#tpc_equipement").val()) ? $("#tpc_equipement").val().join(',') : "",
                        tpc_result: $("#tpc_result").val(),
                        tpc_completion_date: $("#tpc_completion_date").val(),
                        vcholerae_media: $("#vcholerae_media").val(),
                        vcholerae_Kitlotno: Array.isArray($("#vcholerae_Kitlotno").val()) ? $("#vcholerae_Kitlotno").val().join(',') : "",
                        vcholerae_KitOpeningDate: $("#vcholerae_KitOpeningDate").val(),
                        vcholerae_OpeningWells: $("#vcholerae_OpeningWells").val(),
                        vcholerae_UsedWells: $("#vcholerae_UsedWells").val(),
                        vcholerae_BalanceWells: $("#vcholerae_BalanceWells").val(),
                        vcholerae_equipement: Array.isArray($("#vcholerae_equipement").val()) ? $("#vcholerae_equipement").val().join(',') : "",
                        vcholerae_result: $("#vcholerae_result").val(),
                        vcholerae_completion_date: $("#vcholerae_completion_date").val(),

                        tpcDynamicEntries: JSON.stringify(tpcDynamic),

                        analysed_by: $("#analysed_by").val(),
                        checked_by: $("#checked_by").val(),
                        verified_by: $("#verified_by").val(),
                        remarks: $("#remarks").val(),
                        id: urlId || 0,

                        // vcholeraeDynamicEntries: JSON.stringify(vcDynamic)
                    },
                    success: function(data) {
                        document.getElementById('frmCreateIHMRP').reset();
                        $('#frmCreateIHMRP select.select2').val(null).trigger('change');
                        var newUrl = "ihmbrfwfs-create?FromDate=" + fromDate + "&ToDate=" + toDate;
                        window.location.href = newUrl;
                    },
                    error: function(xhr, status, error) {
                        console.error("AJAX Error:", status, error, xhr.responseText);
                    }
                });
            });



            $(document).on("click", ".btnDelete", function() {
                var selectedID = $(this).attr("data-id");
                var r = confirm("Are You Sure Do You Want To Delete?");
                if (r == true) {
                    $.ajax({
                        url: 'Ajax/Ajax-ihmbrfwfs.php',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            'id': selectedID, // Pass LotIds as an array
                            'action': 'Delete'
                        },
                        success: function(response) {
                            alert("Deleted successfully");
                            var newUrl = "ihmbrfwfs-create?FromDate=" + fromDate + "&ToDate=" + toDate;
                            window.location.href = newUrl;
                            return false;
                        },
                        error: function(xhr, status, error) {
                            console.error("Error:", status, error);
                            //alert("Error occurred while deleting.");
                        }

                    });
                } else {

                }
            });


        });

        function loadAdvances(fromDate, toDate) {

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
                    url: 'Ajax/Ajihmbrfwfslist',
                    type: 'POST',
                    data: function(d) {
                        d.fromDate = fromDate;
                        d.toDate = toDate;
                        d.fromPage = 'Create';

                    }
                },
                columns: [{
                        data: 'Sno'
                    },
                    {
                        data: 'name_food_contact_surface'
                    },
                    {
                        data: 'sample_collection_area'
                    },
                    {
                        data: 'sample_drawn_by'
                    },
                    {
                        data: 'tpc_result',
                        render: function(data) {
                            return 'TPC: ' + (data || 'N/A');
                        }
                    },
                    {
                        data: 'analysed_by'
                    },
                    {
                        data: 'checked_by'
                    },
                    {
                        data: 'verified_by'
                    },
                    {
                        data: 'Action'
                    }
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
                lengthMenu: [
                    [10, 50, 100],
                    [10, 50, 100]
                ],
                scrollX: true,
                scrollY: '500px',
                scrollCollapse: true,
                autoWidth: false,
                responsive: false,
                searching: false,
                ordering: false

            });
        }
    </script>


<!-- 
    <script>
        function splitId(id, fieldName) {
            const idx = id.indexOf(fieldName);
            return {
                baseId: id.substring(0, idx),
                suffix: id.substring(idx + fieldName.length)
            };
        }

        // Handles Usage Qty changes
        function checkVal(UsedWellsId) {
            const {
                baseId,
                suffix
            } = splitId(UsedWellsId, "UsedWells");
            const kitlotnoId = baseId + "Kitlotno" + suffix;
            const lotnos = $("#" + kitlotnoId).val();
            if (lotnos && lotnos.length > 0) {
                lotnos.forEach(lot => recalculateAllBalances(lot));
            }
        }

        $(document).on('click', '.addParamBtn', function() {
            let param = $(this).data('param'); // tpc / vcholerae / coliforms
            let section = $(`.param-section[data-param='${param}']`);
            let totalEntries = section.find('.param-entry').length;
            let maxEntries = (param === 'sal') ? 6 : 3;

            if (totalEntries >= maxEntries) {
                alert(`You can only add up to ${maxEntries} entries for this parameter.`);
                return;
            }

            let oldEntry = $(this).closest('.param-entry');
            let newEntry = oldEntry.clone(false);
            let uniq = Date.now();

            // Update IDs and 'for' labels
            newEntry.find('[id]').each(function() {
                let oldId = $(this).attr('id');
                $(this).attr('id', oldId + '_' + uniq);
            });
            newEntry.find('label[for]').each(function() {
                let oldFor = $(this).attr('for');
                if (newEntry.find('#' + oldFor + '_' + uniq).length > 0) {
                    $(this).attr('for', oldFor + '_' + uniq);
                }
            });

            // Clear inputs except Media & Lot selects
            newEntry.find('input').each(function() {
                if (!this.id.includes("Kitlotno") && !this.id.includes("media")) this.value = '';
            });
            newEntry.find('select').each(function() {
                if (!this.id.includes("Kitlotno") && !this.id.includes("media")) $(this).val(null);
            });

            // Reset Select2 fields
            newEntry.find('select').each(function() {
                let savedOptions = $(this).find('option').clone(true);
                $(this).siblings('.select2').remove();
                $(this)
                    .removeAttr('data-select2-id aria-hidden tabindex')
                    .removeClass('select2-hidden-accessible');
                $(this).empty().append(savedOptions);
            });

            // Update inline event handlers with new IDs
            newEntry.find('[onchange], [onkeyup]').each(function() {
                let attrType = $(this).attr('onchange') ? 'onchange' : 'onkeyup';
                let oldAttr = $(this).attr(attrType);
                if (oldAttr) {
                    newEntry.find('[id]').each(function() {
                        let newId = $(this).attr('id');
                        let baseId = newId.replace('_' + uniq, '');
                        oldAttr = oldAttr.replace(new RegExp(baseId + "(?!_\\d+)", "g"), newId);
                    });
                    $(this).attr(attrType, oldAttr);
                }
            });

            // Remove Result & Completion Date fields (for cloned entry)
            newEntry.find("label").each(function() {
                const labelText = $(this).text().trim().toLowerCase();
                if (labelText.includes('result') || labelText.includes('completion date')) {
                    $(this).closest('.form-group').remove();
                }
            });

            // Adjust col classes for non-TPC
            if (param !== 'tpc') {
                newEntry.find('.form-group').each(function() {
                    $(this).removeClass('col col-12 col-lg').addClass('col-md-4');
                });
            }

            // Append cloned entry
            section.append('<hr>');
            section.append(newEntry);

            // Reinitialize all Select2 fields
            newEntry.find("select").each(function() {
                if ($(this).data('select2')) $(this).select2('destroy');
                $(this).select2({
                    width: '100%'
                });
                $(this).val(null).trigger('change');
            });

            // Copy Lot No options from first entry
            let masterLotSelect = section.find("select[id*='Kitlotno']").first();
            let newLotSelect = newEntry.find("select[id*='Kitlotno']");
            if (newLotSelect.length) {
                let lotOptions = masterLotSelect.find('option').clone(true);
                newLotSelect.empty().append(lotOptions);
                newLotSelect.select2({
                    width: '100%'
                }).val(null).trigger('change');
            }

            // Copy Media Used select (with onchange AJAX)
            let mediaSelect = newEntry.find("select[id*='media']");
            if (mediaSelect.length) {
                mediaSelect.select2({
                    width: '100%'
                }).val(null).trigger('change');
            }

            // Copy Equipment Used select
            let equipSelect = newEntry.find("select[id*='equipement']");
            if (equipSelect.length) {
                equipSelect.select2({
                    width: '100%'
                }).val(null).trigger('change');
            }

            // Disable old add button & new add button if max reached
            oldEntry.find('.addBtnRow .addParamBtn').prop('disabled', true);
            if (section.find('.param-entry').length >= maxEntries) {
                newEntry.find('.addBtnRow .addParamBtn').prop('disabled', true);
            }
        });

        // Update balance on Lot No change
        $(document).on('change', "select[id*='Kitlotno']", function() {
            const {
                baseId,
                suffix
            } = splitId(this.id, "Kitlotno");
            getBalance(
                this.id,
                baseId + "OpeningWells" + suffix,
                baseId + "BalanceWells" + suffix,
                baseId + "KitOpeningDate" + suffix
            );
        });

        // Update balance on Usage Qty change
        $(document).on('keyup change', "input[id*='UsedWells']", function() {
            const {
                baseId,
                suffix
            } = splitId(this.id, "UsedWells");
            const kitlotnoId = baseId + "Kitlotno" + suffix;
            const lotnos = $("#" + kitlotnoId).val();
            if (lotnos && lotnos.length > 0) {
                lotnos.forEach(lot => recalculateAllBalances(lot));
            }
        });
    </script> -->

     <script>
        function splitId(id, fieldName) {
            const idx = id.indexOf(fieldName);
            return {
                baseId: id.substring(0, idx),
                suffix: id.substring(idx + fieldName.length)
            };
        }

        // Handles Usage Qty changes
        function checkVal(UsedWellsId) {
            const {
                baseId,
                suffix
            } = splitId(UsedWellsId, "UsedWells");
            const kitlotnoId = baseId + "Kitlotno" + suffix;
            const lotnos = $("#" + kitlotnoId).val();
            if (lotnos && lotnos.length > 0) {
                lotnos.forEach(lot => recalculateAllBalances(lot));
            }
        }

        $(document).on('click', '.addParamBtn', function() {
            let param = $(this).data('param'); // tpc / vcholerae / coliforms
            let section = $(`.param-section[data-param='${param}']`);
            let totalEntries = section.find('.param-entry').length;
            let maxEntries = (param === 'sal') ? 6 : 3;

            if (totalEntries >= maxEntries) {
                alert(`You can only add up to ${maxEntries} entries for this parameter.`);
                return;
            }

            let oldEntry = $(this).closest('.param-entry');
            let newEntry = oldEntry.clone(false);
            let uniq = Date.now();

            // Update IDs and 'for' labels
            newEntry.find('[id]').each(function() {
                let oldId = $(this).attr('id');
                $(this).attr('id', oldId + '_' + uniq);
            });
            newEntry.find('label[for]').each(function() {
                let oldFor = $(this).attr('for');
                if (newEntry.find('#' + oldFor + '_' + uniq).length > 0) {
                    $(this).attr('for', oldFor + '_' + uniq);
                }
            });

            // Clear inputs except Media & Lot selects
            newEntry.find('input').each(function() {
                if (!this.id.includes("Kitlotno") && !this.id.includes("media")) this.value = '';
            });
            newEntry.find('select').each(function() {
                if (!this.id.includes("Kitlotno") && !this.id.includes("media")) $(this).val(null);
            });

            // Reset Select2 fields
            newEntry.find('select').each(function() {
                let savedOptions = $(this).find('option').clone(true);
                $(this).siblings('.select2').remove();
                $(this)
                    .removeAttr('data-select2-id aria-hidden tabindex')
                    .removeClass('select2-hidden-accessible');
                $(this).empty().append(savedOptions);
            });

            // Update inline event handlers with new IDs
            newEntry.find('[onchange], [onkeyup]').each(function() {
                let attrType = $(this).attr('onchange') ? 'onchange' : 'onkeyup';
                let oldAttr = $(this).attr(attrType);
                if (oldAttr) {
                    newEntry.find('[id]').each(function() {
                        let newId = $(this).attr('id');
                        let baseId = newId.replace('_' + uniq, '');
                        oldAttr = oldAttr.replace(new RegExp(baseId + "(?!_\\d+)", "g"), newId);
                    });
                    $(this).attr(attrType, oldAttr);
                }
            });

            // Remove Result & Completion Date fields (for cloned entry)
            // newEntry.find("label").each(function() {
            //     const labelText = $(this).text().trim().toLowerCase();
            //     if (labelText.includes('result') || labelText.includes('completion date')) {
            //         $(this).closest('.form-group').remove();
            //     }
            // });


            newEntry.find("label").each(function() {
                const labelText = $(this).text().trim().toLowerCase().replace(/\s+/g, ' ');

                if (labelText.includes('v. cholerae') || labelText.includes('result') || labelText.includes('completion date')) {
                    $(this).closest('.form-group').remove();
                }


                if (param === 'tpc' && labelText.includes('tpc at 35°c')) {
                    $(this).closest('.form-group').remove();
                }

            });


            // Adjust col classes for non-TPC
            if (param !== 'tpc') {
                newEntry.find('.form-group').each(function() {
                    $(this).removeClass('col col-12 col-lg').addClass('col-md-4');
                });
            }

            // Append cloned entry
            section.append('<hr>');
            section.append(newEntry);

            // Reinitialize all Select2 fields
            newEntry.find("select").each(function() {
                if ($(this).data('select2')) $(this).select2('destroy');
                $(this).select2({
                    width: '100%'
                });
                $(this).val(null).trigger('change');
            });

            // Copy Lot No options from first entry
            let masterLotSelect = section.find("select[id*='Kitlotno']").first();
            let newLotSelect = newEntry.find("select[id*='Kitlotno']");
            if (newLotSelect.length) {
                let lotOptions = masterLotSelect.find('option').clone(true);
                newLotSelect.empty().append(lotOptions);
                newLotSelect.select2({
                    width: '100%'
                }).val(null).trigger('change');
            }

            // Copy Media Used select (with onchange AJAX)
            let mediaSelect = newEntry.find("select[id*='media']");
            if (mediaSelect.length) {
                mediaSelect.select2({
                    width: '100%'
                }).val(null).trigger('change');
            }

            // Copy Equipment Used select
            let equipSelect = newEntry.find("select[id*='equipement']");
            if (equipSelect.length) {
                equipSelect.select2({
                    width: '100%'
                }).val(null).trigger('change');
            }

            // Disable old add button & new add button if max reached
            oldEntry.find('.addBtnRow .addParamBtn').prop('disabled', true);
            if (section.find('.param-entry').length >= maxEntries) {
                newEntry.find('.addBtnRow .addParamBtn').prop('disabled', true);
            }
        });

        // Update balance on Lot No change
        $(document).on('change', "select[id*='Kitlotno']", function() {
            const {
                baseId,
                suffix
            } = splitId(this.id, "Kitlotno");
            getBalance(
                this.id,
                baseId + "OpeningWells" + suffix,
                baseId + "BalanceWells" + suffix,
                baseId + "KitOpeningDate" + suffix
            );
        });

        // Update balance on Usage Qty change
        $(document).on('keyup change', "input[id*='UsedWells']", function() {
            const {
                baseId,
                suffix
            } = splitId(this.id, "UsedWells");
            const kitlotnoId = baseId + "Kitlotno" + suffix;
            const lotnos = $("#" + kitlotnoId).val();
            if (lotnos && lotnos.length > 0) {
                lotnos.forEach(lot => recalculateAllBalances(lot));
            }
        });
    </script>
</body>

</html>