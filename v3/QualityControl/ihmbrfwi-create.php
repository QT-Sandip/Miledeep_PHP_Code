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

// Userlist names
$sqlQuery = "SELECT MIN(LUID) as USER_ID, LbuName as USER_NAME FROM `QcLabTechnologist` WHERE LbuStatus=1 Group by LbuName ORDER BY LbuName ASC";
$stmtUsers = $iconn->query($sqlQuery);
$RowUsersLists = [];
while ($RowUsers = $stmtUsers->fetch_array()) {
    $RowUsersLists[] = $RowUsers;
}
//end here

//Paramaters list
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
$ROWSWAB = array();
if ($urlId > 0) {
    $sqlGetRecord = " select * from micro_biology_water_ice where mbwi_id= '" . $urlId . "' ";
    $stmturlresults = $iconn->query($sqlGetRecord);
    if ($stmturlresults) {

        $ROWSWAB = $stmturlresults->fetch_array();


        $getEquipmentquery = " select InstrumentId, ParameterID, MediaID, InstrumentName
            from instrumentmaster ";
        $stmtGetEquipment = $iconn->query($getEquipmentquery);
        if ($stmtGetEquipment) {
            while ($rowEquipment = $stmtGetEquipment->fetch_array()) {
                $equpmentMasterList[$rowEquipment['MediaID']][] = $rowEquipment;
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
    "VALUE ADDITION" => "VALUE ADDITION",
    "SOAKING" => "SOAKING",
    "IQF-I INFEED - ARICO" => "IQF-I INFEED - ARICO",
    "IQF-II INFEED - GLORY" => "IQF-II INFEED - GLORY",
    "BLOCK" => "BLOCK",
    "RECIRCULATE WATER - ARICO" => "RECIRCULATE WATER - ARICO",
    "GLAZE WATER- ARICO" => "GLAZE WATER- ARICO",
    "GLAZE WATER- GLORY" => "GLAZE WATER- GLORY",
    "RAW WATER TANK -1" => "RAW WATER TANK -1",
    "RAW WATER TANK -2" => "RAW WATER TANK -2",
    "TREATED WATER TANK -1" => "TREATED WATER TANK -1",
    "TREATED WATER TANK -2" => "TREATED WATER TANK -2",
    "TREATED WATER TANK -3" => "TREATED WATER TANK -3",
    "TREATED WATER TANK -4" => "TREATED WATER TANK -4"
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

                                            <div class="form-group col-lg">
                                                <label for="report_type">Type Of Report </label>
                                                <select class="form-control select2" name="report_type" id="report_type"
                                                    required onchange="hideParamater(this.value)">
                                                    <option Value=""> Select</option>
                                                    <?php foreach ($reporttypearr as $RowUsersList) {
                                                        ?>
                                                        <option Value="<?php echo $RowUsersList; ?>" <?php echo ($ROWSWAB['report_type'] == $RowUsersList) ? "selected='selected'" : ''; ?>>
                                                            <?php echo $RowUsersList; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <div class="form-group col-lg">
                                                <label for="sample_drawn_by">Sample Drawn By </label>
                                                <select class="form-control select2" name="sample_drawn_by"
                                                    id="sample_drawn_by" required>
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

                                            <div class="form-group col-sm-3">
                                                <label for="sample_collection">Sample Collection Date</label>
                                                <input required type="date"
                                                    value="<?php echo $ROWSWAB['sample_collection'] ?>"
                                                    class="form-control" name='sample_collection' id="sample_collection"
                                                    placeholder="Enter Date of Sampling">
                                            </div>

                                            <div class="form-group col-sm-3">
                                                <label for="AnalyzedDate">Analysis Date</label>
                                                <input required type="date"
                                                    value="<?php echo $ROWSWAB['AnalyzedDate'] ?>" class="form-control"
                                                    name='AnalyzedDate' id="AnalyzedDate"
                                                    placeholder="Enter Date of Sampling">
                                            </div>

                                            <div class="form-group col-lg">
                                                <label for="plant_id">Plant Name</label>
                                                <select class="form-control" name="plant_id" id="plant_id" required>
                                                    <option Value=""> Select Plant</option>
                                                    <?php while ($RowPlantsList = $stmtPlants->fetch_array()) {
                                                        ?>
                                                        <option Value="<?php echo $RowPlantsList['ProcessingStationID']; ?>"
                                                            <?php echo ($ROWSWAB['plant_id'] == $RowPlantsList['ProcessingStationID']) ? "selected='selected'" : ''; ?>>
                                                            <?php echo $RowPlantsList['StationName']; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>
                                        </div>
                                        <hr>
                                        <div class="row">
                                            <div class="form-group col-lg-3">
                                                <label for="sample_desc">Sample Description </label>
                                                <select class="form-control select2" name="sample_desc" id="sample_desc"
                                                    required onChange="togglefield(this.value)">
                                                    <option Value=""> Select</option>
                                                    <?php foreach ($sampledescarr as $ldatakey => $ldataValue) {
                                                        ?>
                                                        <option Value="<?php echo $ldatakey; ?>" <?php echo ($ldatakey == $ROWSWAB['sample_desc']) ? "selected='selected'" : ''; ?>>
                                                            <?php echo $ldataValue; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="sample_desc">Location </label>
                                                <select class="form-control select2" name="location" id="location"
                                                    required>
                                                    <option Value=""> Select</option>
                                                    <?php foreach ($locationarr as $ldatakey => $ldataValue) {
                                                        ?>
                                                        <option Value="<?php echo $ldatakey; ?>" <?php echo ($ldatakey == $ROWSWAB['location']) ? "selected='selected'" : ''; ?>>
                                                            <?php echo $ldataValue; ?>
                                                        </option>
                                                    <?php } ?>
                                                </select>
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="tap_no_for_water">Tap Number Only For Water </label>
                                                <input required type="text" class="form-control" name="tap_no_for_water"
                                                    id="tap_no_for_water" placeholder="Enter Value"
                                                    value="<?php echo $ROWSWAB['tap_no_for_water'] ?>">
                                            </div>

                                            <div class="form-group col-lg-3">
                                                <label for="itype_of_ice">Type Of Ice</label>
                                                <select class="form-control select2" name="itype_of_ice"
                                                    id="itype_of_ice" required>
                                                    <option value="">Select</option>
                                                    <?php
                                                    $selectedIce = isset($ROWSWAB['itype_of_ice']) ? trim(strtoupper($ROWSWAB['itype_of_ice'])) : '';
                                                    foreach ($itypeoficearr as $key => $value) {
                                                        $keyTrimmed = trim(strtoupper($key));
                                                        $isSelected = ($keyTrimmed === $selectedIce) ? "selected='selected'" : '';
                                                        echo "<option value=\"$key\" $isSelected>$value</option>";
                                                    }
                                                    ?>
                                                </select>
                                            </div>

                                            <!-- </div> -->


                                            <!-- <div class="row">
                                            <div class="form-group col-lg-3">
                                                <label for="instant_swab">Instant Swab</label>
                                                <select class="form-control select2" name="instant_swab" id="instant_swab" required>
                                                    <option value="">Select</option>
                                                    <option value="Positive" <?php echo ($ROWSWAB['instant_swab'] == 'Positive') ? "selected='selected'" : ''; ?>>Positive</option>
                                                    <option value="Negative" <?php echo ($ROWSWAB['instant_swab'] == 'Negative') ? "selected='selected'" : ''; ?>>Negative</option>
                                                </select>
                                            </div> -->

                                        </div>
                                        <hr>
                                        <div id="updateid">
                                            <!-- ======================= TPC ======================= -->
                                            <div class="param-section" data-param="tpc">
                                                <div class="param-entry" data-index="0">
                                                    <div class="row">
                                                        <div class="form-group col-lg">
                                                            <h6>Parameter: TPC</h6>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="form-group col-lg">
                                                            <label for="tpc_media">Name Of The Media Used</label>
                                                            <select class="form-control select2" name="tpc_media"
                                                                id="tpc_media" required
                                                                onChange="getEquipment('tpc_media','tpc_equipement'); getKits('tpc_media','tpc_Kitlotno')">
                                                                <option Value=""> Select</option>
                                                                <?php foreach ($Media_mast_list as $RowMediaList) { ?>
                                                                    <option Value="<?php echo $RowMediaList['MediaID']; ?>"
                                                                        <?php echo ($RowMediaList['MediaID'] == $ROWSWAB['tpc_media']) ? "selected='selected'" : ''; ?>>
                                                                        <?php echo $RowMediaList['MediaName']; ?>
                                                                    </option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>

                                                        <div class="form-group col">
                                                            <label for="tpc_Kitlotno">Lot No</label>
                                                            <select class="form-control select2" name="tpc_Kitlotno"
                                                                id="tpc_Kitlotno" required multiple="multiple"
                                                                onChange="getBalance('tpc_Kitlotno','tpc_OpeningWells','tpc_BalanceWells','tpc_KitOpeningDate')">
                                                                <option Value=""> Select</option>
                                                                <?php
                                                                $ROWSWAB['tpc_Kitlotno'] = explode(',', $ROWSWAB['tpc_Kitlotno']);
                                                                foreach ($mediaMasterList[$ROWSWAB['Parameter']] as $RowMediaList) {
                                                                    ?>
                                                                    <option Value="<?php echo $RowMediaList['MediaID']; ?>"
                                                                        <?php echo in_array($RowMediaList['MediaID'], (array) $ROWSWAB['tpc_Kitlotno']) ? "selected='selected'" : ''; ?>>
                                                                        <?php echo $RowMediaList['MediaName']; ?>
                                                                    </option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>

                                                        <div class="form-group col">
                                                            <label for="tpc_KitOpeningDate">Opening Date</label>
                                                            <input required type="Date" class="form-control"
                                                                name="tpc_KitOpeningDate" id="tpc_KitOpeningDate">
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="form-group col">
                                                            <label for="tpc_OpeningWells">Balance Qty</label>
                                                            <input required type="text" class="form-control"
                                                                name="tpc_OpeningWells" id="tpc_OpeningWells" readonly>
                                                        </div>

                                                        <div class="form-group col">
                                                            <label for="tpc_UsedWells">Usage Qty</label>
                                                            <input required type="number" step="any"
                                                                class="form-control" name="tpc_UsedWells"
                                                                id="tpc_UsedWells" placeholder="Enter Usage"
                                                                onkeyup="checkVal('tpc_UsedWells','tpc_BalanceWells','tpc_Kitlotno')">
                                                        </div>

                                                        <div class="form-group col">
                                                            <label for="tpc_BalanceWells">Balance Qty</label>
                                                            <input required type="number" class="form-control"
                                                                name="tpc_BalanceWells" id="tpc_BalanceWells" readonly>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="form-group col-lg">
                                                            <label for="tpc_equipement">Equipment Used</label>
                                                            <select class="form-control select2" name="tpc_equipement"
                                                                id="tpc_equipement" required multiple>
                                                                <option Value=""> Select</option>
                                                                <?php
                                                                $ROWSWAB['tpc_equipement'] = explode(",", $ROWSWAB['tpc_equipement']);
                                                                foreach ($ROWSWAB['tpc_media'] as $tpcmk => $tpcmv) {
                                                                    foreach ($equpmentMasterList[$ROWSWAB['tpc_paramater']][$tpcmv] as $RoweqList) {
                                                                        ?>
                                                                        <option
                                                                            Value="<?php echo $RoweqList['InstrumentId']; ?>"
                                                                            <?php echo in_array($RoweqList['InstrumentId'], (array) $ROWSWAB['tpc_equipement']) ? "selected='selected'" : ''; ?>>
                                                                            <?php echo $RoweqList['InstrumentName']; ?>
                                                                        </option>
                                                                    <?php }
                                                                } ?>
                                                            </select>
                                                        </div>

                                                        <div class="form-group col-lg">
                                                            <label for="tpc_result1">TPC 37 °C</label>
                                                            <input required type="text" class="form-control"
                                                                name="tpc_result1" id="tpc_result1"
                                                                value="<?php echo $ROWSWAB['tpc_result1'] ?>">
                                                        </div>

                                                        <div class="form-group col-lg">
                                                            <label for="tpc_result2">TPC 22 °C</label>
                                                            <input required type="text" class="form-control"
                                                                name="tpc_result2" id="tpc_result2"
                                                                value="<?php echo $ROWSWAB['tpc_result2'] ?>">
                                                        </div>

                                                        <div class="form-group col-lg">
                                                            <label for="tpc_completion_date">Completion Date</label>
                                                            <input required type="date" class="form-control"
                                                                name="tpc_completion_date" id="tpc_completion_date"
                                                                value="<?php echo $ROWSWAB['tpc_completion_date'] ?>">
                                                        </div>
                                                    </div>

                                                    <!-- Add Button Row -->
                                                    <div class="row addBtnRow mt-2" style="text-align: right;">
                                                        <div class="col-lg">
                                                            <button type="button"
                                                                class="btn btn-sm btn-primary addParamBtn"
                                                                data-param="tpc">+ Add</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <hr>

                                            <!-- ======================= V. Cholerae ======================= -->
                                            <div class="param-section" data-param="vcholerae">
                                                <div class="param-entry" data-index="0">
                                                    <div class="row">
                                                        <div class="form-group col-lg">
                                                            <h6>Parameter: V. Cholerae</h6>
                                                        </div>
                                                    </div>

                                                    <!-- Media, Lot, Opening Date -->
                                                    <div class="row">
                                                        <div class="form-group col-lg">
                                                            <label for="vcholerae_media">Name Of The Media Used</label>
                                                            <select class="form-control select2" name="vcholerae_media"
                                                                id="vcholerae_media" required
                                                                onChange="getEquipment('vcholerae_media','vcholerae_equipement'); getKits('vcholerae_media','vcholerae_Kitlotno')">
                                                                <option Value=""> Select</option>
                                                                <?php foreach ($Media_mast_list as $RowMediaList) { ?>
                                                                    <option Value="<?php echo $RowMediaList['MediaID']; ?>"
                                                                        <?php echo ($RowMediaList['MediaID'] == $ROWSWAB['vcholerae_media']) ? "selected='selected'" : ''; ?>>
                                                                        <?php echo $RowMediaList['MediaName']; ?>
                                                                    </option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>

                                                        <div class="form-group col">
                                                            <label for="vcholerae_Kitlotno">Lot No</label>
                                                            <select class="form-control select2"
                                                                name="vcholerae_Kitlotno" id="vcholerae_Kitlotno"
                                                                required multiple="multiple"
                                                                onChange="getBalance('vcholerae_Kitlotno','vcholerae_OpeningWells','vcholerae_BalanceWells','vcholerae_KitOpeningDate')">
                                                                <option Value=""> Select</option>
                                                                <?php
                                                                $ROWSWAB['vcholerae_Kitlotno'] = explode(',', $ROWSWAB['vcholerae_Kitlotno']);
                                                                foreach ($mediaMasterList[$ROWSWAB['Parameter']] as $RowMediaList) {
                                                                    ?>
                                                                    <option Value="<?php echo $RowMediaList['MediaID']; ?>"
                                                                        <?php echo in_array($RowMediaList['MediaID'], (array) $ROWSWAB['vcholerae_Kitlotno']) ? "selected='selected'" : ''; ?>>
                                                                        <?php echo $RowMediaList['MediaName']; ?>
                                                                    </option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>

                                                        <div class="form-group col">
                                                            <label for="vcholerae_KitOpeningDate">Opening Date</label>
                                                            <input required type="Date" class="form-control"
                                                                name="vcholerae_KitOpeningDate"
                                                                id="vcholerae_KitOpeningDate">
                                                        </div>
                                                    </div>

                                                    <!-- Balance, Usage, Balance -->
                                                    <div class="row">
                                                        <div class="form-group col">
                                                            <label for="vcholerae_OpeningWells">Balance Qty</label>
                                                            <input required type="text" class="form-control"
                                                                name="vcholerae_OpeningWells"
                                                                id="vcholerae_OpeningWells" readonly>
                                                        </div>

                                                        <div class="form-group col">
                                                            <label for="vcholerae_UsedWells">Usage Qty</label>
                                                            <input required type="number" step="any"
                                                                class="form-control" name="vcholerae_UsedWells"
                                                                id="vcholerae_UsedWells" placeholder="Enter Usage"
                                                                onkeyup="checkVal('vcholerae_UsedWells','vcholerae_BalanceWells','vcholerae_Kitlotno')">
                                                        </div>

                                                        <div class="form-group col">
                                                            <label for="vcholerae_BalanceWells">Balance Qty</label>
                                                            <input required type="number" class="form-control"
                                                                name="vcholerae_BalanceWells"
                                                                id="vcholerae_BalanceWells" readonly>
                                                        </div>
                                                    </div>

                                                    <!-- Equipment + Result + Completion -->
                                                    <div class="row">
                                                        <div class="form-group col-lg">
                                                            <label for="vcholerae_equipement">Equipment Used</label>
                                                            <select class="form-control select2"
                                                                name="vcholerae_equipement" id="vcholerae_equipement"
                                                                required multiple>
                                                                <option Value=""> Select</option>
                                                                <?php
                                                                $ROWSWAB['vcholerae_equipement'] = explode(",", $ROWSWAB['vcholerae_equipement']);
                                                                foreach ($ROWSWAB['vcholerae_media'] as $vmk => $vmv) {
                                                                    foreach ($equpmentMasterList[$ROWSWAB['vcholerae_paramater']][$vmv] as $RoweqList) {
                                                                        ?>
                                                                        <option
                                                                            Value="<?php echo $RoweqList['InstrumentId']; ?>"
                                                                            <?php echo in_array($RoweqList['InstrumentId'], (array) $ROWSWAB['vcholerae_equipement']) ? "selected='selected'" : ''; ?>>
                                                                            <?php echo $RoweqList['InstrumentName']; ?>
                                                                        </option>
                                                                    <?php }
                                                                } ?>
                                                            </select>
                                                        </div>

                                                        <div class="form-group col-lg">
                                                            <label for="vcholerae_result1">Result</label>
                                                            <input required type="text" class="form-control"
                                                                name="vcholerae_result1" id="vcholerae_result1"
                                                                value="<?php echo $ROWSWAB['vcholerae_result1'] ?>">
                                                        </div>

                                                        <div class="form-group col-lg">
                                                            <label for="vcholerae_completion_date">Completion
                                                                Date</label>
                                                            <input required type="date" class="form-control"
                                                                name="vcholerae_completion_date"
                                                                id="vcholerae_completion_date"
                                                                value="<?php echo $ROWSWAB['vcholerae_completion_date'] ?>">
                                                        </div>
                                                    </div>

                                                    <!-- Add Button Row -->
                                                    <div class="row addBtnRow mt-2" style="text-align: right;">
                                                        <div class="col-lg">
                                                            <button type="button"
                                                                class="btn btn-sm btn-primary addParamBtn"
                                                                data-param="vcholerae">+ Add</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <hr>

                                            <!-- ======================= Coliforms ======================= -->
                                            <div class="param-section" data-param="coliforms">
                                                <div class="param-entry" data-index="0">
                                                    <div class="row">
                                                        <div class="form-group col-lg">
                                                            <h6>Parameter: Coliforms</h6>
                                                        </div>
                                                    </div>

                                                    <!-- Media, Lot, Opening Date -->
                                                    <div class="row" id="vrw">
                                                        <div class="form-group col-lg">
                                                            <label for="cholerae_media">Name Of The Media Used</label>
                                                            <select class="form-control select2" name="cholerae_media"
                                                                id="cholerae_media"
                                                                onChange="getEquipment('cholerae_media','cholerae_equipement'); getKits('cholerae_media','cholerae_Kitlotno')">
                                                                <option Value=""> Select</option>
                                                                <?php
                                                                foreach ($Media_mast_list as $RowMediaList) {
                                                                    ?>
                                                                    <option Value="<?php echo $RowMediaList['MediaID']; ?>"
                                                                        <?php echo ($RowMediaList['MediaID'] == $ROWSWAB['cholerae_media']) ? "selected='selected'" : ''; ?>>
                                                                        <?php echo $RowMediaList['MediaName']; ?>
                                                                    </option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>
                                                        <div class="form-group col">
                                                            <label for="cholerae_Kitlotno">Lot No</label>
                                                            <select class="form-control select2"
                                                                name="cholerae_Kitlotno" id="cholerae_Kitlotno" required
                                                                multiple="multiple"
                                                                onChange="getBalance('cholerae_Kitlotno','cholerae_OpeningWells','cholerae_BalanceWells','cholerae_KitOpeningDate')"
                                                                multiple="multiple">
                                                                <option Value=""> Select</option>
                                                                <?php
                                                                $ROWSWAB['cholerae_Kitlotno'] = explode(',', $ROWSWAB['cholerae_Kitlotno']);
                                                                foreach ($mediaMasterList[$ROWSWAB['Parameter']] as $RowMediaList) {
                                                                    ?>
                                                                    <option Value="<?php echo $RowMediaList['MediaID']; ?>"
                                                                        <?php echo in_array($RowMediaList['MediaID'], (array) $ROWSWAB['cholerae_Kitlotno']) ? "selected='selected'" : ''; ?>>
                                                                        <?php echo $RowMediaList['MediaName']; ?>
                                                                    </option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>

                                                        <div class="form-group col">
                                                            <label for="cholerae_KitOpeningDate">Opening Date</label>
                                                            <input required type="Date" class="form-control"
                                                                name="cholerae_KitOpeningDate"
                                                                id="cholerae_KitOpeningDate">
                                                        </div>

                                                    </div>
                                                    <div class="row" id="vrw_sub">

                                                        <div class="form-group col">
                                                            <label for="cholerae_OpeningWells">Total Qty</label>
                                                            <input required type="text" class="form-control"
                                                                name="cholerae_OpeningWells" id="cholerae_OpeningWells"
                                                                placeholder="Enter Total Qty" readonly="">
                                                        </div>

                                                        <div class="form-group col">
                                                            <label for="cholerae_UsedWells">Usage Qty</label>
                                                            <input required type="number" step="any"
                                                                class="form-control" name="cholerae_UsedWells"
                                                                id="cholerae_UsedWells" placeholder="Enter Usage"
                                                                onkeyup="checkVal('cholerae_UsedWells','cholerae_BalanceWells','cholerae_Kitlotno')">
                                                        </div>

                                                        <div class="form-group col">
                                                            <label for="cholerae_BalanceWells">Balance Qty</label>
                                                            <input required type="number" class="form-control"
                                                                name="cholerae_BalanceWells" id="cholerae_BalanceWells"
                                                                placeholder="Enter Balance" readonly="">
                                                        </div>
                                                    </div>
                                                    <div class="row" id="vrw_sub2">

                                                        <div class="form-group col-lg">
                                                            <label for="cholerae_equipement">Equipment Used</label>
                                                            <select class="form-control select2"
                                                                name="cholerae_equipement" id="cholerae_equipement"
                                                                multiple>
                                                                <option Value=""> Select</option>
                                                                <?php
                                                                $ROWSWAB['cholerae_equipement'] = explode(",", $ROWSWAB['cholerae_equipement']);
                                                                foreach ($ROWSWAB['cholerae_media'] as $vmk => $vmv) {
                                                                    foreach ($equpmentMasterList[$ROWSWAB['cholerae_paramater']][$vmv] as $RoweqList) {
                                                                        ?>
                                                                        <option
                                                                            Value="<?php echo $RoweqList['InstrumentId']; ?>"
                                                                            <?php echo in_array($RoweqList['InstrumentId'], (array) $ROWSWAB['cholerae_equipement']) ? "selected='selected'" : ''; ?>>
                                                                            <?php echo $RoweqList['InstrumentName']; ?>
                                                                        </option>
                                                                    <?php }
                                                                } ?>
                                                            </select>
                                                        </div>

                                                        <div class="form-group col-lg">
                                                            <label for="cholerae_result1">Coliforms</label>
                                                            <input type="text" class="form-control"
                                                                name="cholerae_result1" id="cholerae_result1"
                                                                placeholder="Coliforms result"
                                                                value="<?php echo $ROWSWAB['cholerae_result1'] ?>">
                                                        </div>

                                                        <div class="form-group col-lg">
                                                            <label for="cholerae_completion_date">Completion Date
                                                            </label>
                                                            <input type="date"
                                                                value="<?php echo $ROWSWAB['cholerae_completion_date'] ?>"
                                                                class="form-control" name="cholerae_completion_date"
                                                                id="cholerae_completion_date"
                                                                placeholder="Enter Date of Sampling">
                                                        </div>

                                                    </div>
                                                    <hr>

                                                    <!-- Add Button Row -->
                                                    <div class="row addBtnRow mt-2" style="text-align: right;">
                                                        <div class="col-lg">
                                                            <button type="button"
                                                                class="btn btn-sm btn-primary addParamBtn"
                                                                data-param="coliforms">+ Add</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <hr>
                                        </div>


                                        <div class="row">

                                            <div class="form-group col-lg-3">
                                                <label for="analysed_by">Analysed By : </label>
                                                <select class="form-control select2" name="analysed_by" id="analysed_by"
                                                    required>
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
                                                <select class="form-control select2" name="checked_by" id="checked_by"
                                                    required>
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
                                                <select class="form-control select2" name="verified_by" id="verified_by"
                                                    required>
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
                                                <input required type="text" class="form-control" name="remarks"
                                                    id="remarks" placeholder="Enter remarks"
                                                    value="<?php echo $ROWSWAB['remarks'] ?>">

                                            </div>

                                        </div>
                                        <hr>
                                        <div class="col-xs-12 " align="right">
                                            <button class="btn btn-danger" type="button" id="closeTab">Close</button>
                                            <button type="submit" name='submit' class="btn btn-success"
                                                id="submit">Submit</button>
                                            <button type="button" name='submit' class="btn btn-success" id="updateit"
                                                style="display:none">Update</button>

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
                                                <th>Result </th>
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
                success: function (data) {
                    $("#" + Kitlotno).html(data);
                },
                error: function (xhr, status, error) {
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
                success: function (response) {
                    if (!response.Error && response.data) {
                        const openingWells = parseFloat(response.data.OpeningWells || 0);
                        const balanceWells = parseFloat(response.data.BalanceWells || 0);
                        const mKitOpeningDate = response.data.mKitOpeningDate;

                        kitlotnos.forEach(function (lot) {
                            kitBalances[lot] = {
                                opening: openingWells,
                                balance: balanceWells
                            };
                        });

                        let lot = kitlotnos[0];
                        let previousBalance = null;

                        // 🔹 Find the last balance for this lot in all visible rows
                        $("[id*='Kitlotno']").each(function () {
                            const selected = $(this).val();
                            if (!selected || !Array.isArray(selected) || !selected.includes(lot)) return;
                            if (this.id === Kitlotno) return; // skip self

                            const baseId = this.id.replace("Kitlotno", "");
                            const bal = parseFloat($("#" + baseId + "BalanceWells").val());

                            if (!isNaN(bal)) {
                                previousBalance = bal; // always overwrite → ensures last visible balance
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
                error: function (xhr, status, error) {
                    console.error("AJAX error:", error);
                }
            });
        }



        function getTotalUsedForKit(kitlotno) {
            let total = 0;

            $("[id$='Kitlotno']").each(function () {
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

            $("[id*='Kitlotno']").each(function () {
                let selectedLots = $(this).val();
                if (!selectedLots) return;
                if (!Array.isArray(selectedLots)) selectedLots = [selectedLots];
                if (!selectedLots.includes(kitlotno)) return;

                // Handle baseId dynamically
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

        // function checkVal(UsedWells, BalanceWells, kitlotnoId) {
        // const usedWellsInput = document.getElementById(UsedWells);
        // const balanceWellsInput = document.getElementById(BalanceWells);
        // let balanceWells = $("#" + BalanceWells).val();
        // let usedWells = $("#" + UsedWells).val();

        // {
        // //$("#"+BalanceWells).val(balanceWells-usedWells);
        // const kitlotnos = $("#" + kitlotnoId).val(); // multi-select returns array

        // if (Array.isArray(kitlotnos)) {
        // kitlotnos.forEach(function(lot) {
        // recalculateAllBalances(lot, usedWellsInput);
        // });
        // }

        // const lot = $("#" + kitlotnoId).val()?.[0]; // assuming single kit per block
        // if (lot && kitBalances[lot]) {
        // const totalUsed = getTotalUsedForKit(lot);
        // const remaining = kitBalances[lot].balance - totalUsed;
        // balanceWellsInput.value = Math.max(remaining, 0).toFixed(2);
        // if (remaining < 0) {
        // alert("UsedWells should not exceed balanceWells");
        // }
        // }
        // }


        // }


        function checkVal(UsedWellsId) {
            const {
                baseId,
                suffix
            } = splitId(UsedWellsId, "UsedWells");
            const kitlotnoId = baseId + "Kitlotno" + suffix;

            const kitlotnos = $("#" + kitlotnoId).val();
            if (kitlotnos && kitlotnos.length > 0) {
                kitlotnos.forEach(function (lot) {
                    console.log("➡ Recalculating for lot:", lot);
                    recalculateAllBalances(lot);
                });
            }
        }




        function togglefield(val) {
            if (val === "WATER") {
                $("#itype_of_ice").val(0).trigger('change');
                $("#itype_of_ice").attr("disabled", true);
                $("#tap_no_for_water").attr("disabled", false);
            } else {
                $("#tap_no_for_water").val("");
                $("#tap_no_for_water").attr("disabled", true);
                $("#itype_of_ice").attr("disabled", false);

            }
        }

        // function hideParamater(val) {

        // if (val === "Water - Once In 4 Days") {
        // //$("#itype_of_ice").val(0).trigger('change');
        // //$("#itype_of_ice").attr("disabled", true); 
        // } else {
        // //$("#itype_of_ice").attr("disabled", false);
        // }

        // if (val == "Water - Daily Based") {
        // $("#cholerae_media").removeAttr("required").hide();
        // $("#cholerae_Kitlotno").removeAttr("required").hide();
        // $("#cholerae_KitOpeningDate").removeAttr("required").hide();
        // $("#cholerae_OpeningWells").removeAttr("required").hide();
        // $("#cholerae_UsedWells").removeAttr("required").hide();
        // $("#cholerae_BalanceWells").removeAttr("required").hide();
        // $("#cholerae_equipement").removeAttr("required").hide();
        // $("#cholerae_result1").removeAttr("required").hide();
        // $("#cholerae_completion_date").removeAttr("required").hide();
        // $('#vhr_group').find('input, select').each(function() {
        // $(this).val('');
        // if ($(this).is('select')) {
        // $(this).prop('selectedIndex', 0).trigger('change'); // For Select2
        // }
        // });
        // $("#vhr").hide();


        // $('#vrwh').find('input, select').each(function() {
        // $(this).val('');
        // if ($(this).is('select')) {
        // $(this).prop('selectedIndex', 0).trigger('change'); // For Select2
        // }
        // });
        // $("#vrwh").hide();

        // $('#vrw').find('input, select').each(function() {
        // $(this).val('');
        // if ($(this).is('select')) {
        // $(this).prop('selectedIndex', 0).trigger('change'); // For Select2
        // }
        // });
        // $("#vrw").hide();

        // $('#vrw_sub').find('input, select').each(function() {
        // $(this).val('');
        // if ($(this).is('select')) {
        // $(this).prop('selectedIndex', 0).trigger('change'); // For Select2
        // }
        // });
        // $("#vrw_sub").hide();
        // $('#vrw_sub2').find('input, select').each(function() {
        // $(this).val('');
        // if ($(this).is('select')) {
        // $(this).prop('selectedIndex', 0).trigger('change'); // For Select2
        // }
        // });
        // $("#vrw_sub2").hide();

        // } else {
        // $("#cholerae_media").attr("required", true).show();
        // $("#cholerae_Kitlotno").attr("required", true).show();
        // $("#cholerae_KitOpeningDate").attr("required", true).show();
        // $("#cholerae_OpeningWells").attr("required", true).show();
        // $("#cholerae_UsedWells").attr("required", true).show();
        // $("#cholerae_BalanceWells").attr("required", true).show();
        // $("#cholerae_equipement").attr("required", true).show();
        // $("#cholerae_result1").attr("required", true).show();
        // $("#cholerae_completion_date").attr("required", true).show();
        // $("#vhr").show();
        // $("#vrwh").show();
        // $("#vrw").show();
        // $("#vrw_sub").show();
        // $("#vrw_sub2").show();
        // }
        // }

        function hideParamater(val) {

            if (val === "Water - Once In 4 Days") {
                //$("#itype_of_ice").val(0).trigger('change');
                //$("#itype_of_ice").attr("disabled", true); 
            } else {
                //$("#itype_of_ice").attr("disabled", false);
            }

            if (val == "Water - Daily Based") {

                // --- Hide cholerae-related fields ---
                $("#cholerae_media").removeAttr("required").hide();
                $("#cholerae_Kitlotno").removeAttr("required").hide();
                $("#cholerae_KitOpeningDate").removeAttr("required").hide();
                $("#cholerae_OpeningWells").removeAttr("required").hide();
                $("#cholerae_UsedWells").removeAttr("required").hide();
                $("#cholerae_BalanceWells").removeAttr("required").hide();
                $("#cholerae_equipement").removeAttr("required").hide();
                $("#cholerae_result1").removeAttr("required").hide();
                $("#cholerae_completion_date").removeAttr("required").hide();

                // --- Hide VHR sections ---
                $('#vhr_group, #vhr, #vrwh, #vrw, #vrw_sub, #vrw_sub2').each(function () {
                    $(this).find('input, select').each(function () {
                        $(this).val('');
                        if ($(this).is('select')) {
                            $(this).prop('selectedIndex', 0).trigger('change'); // For Select2
                        }
                    });
                    $(this).hide();
                });

                // --- Hide Coliforms parameter section ---
                $('div.param-section[data-param="coliforms"]').find('input, select').val('');
                $('div.param-section[data-param="coliforms"]').find('select').prop('selectedIndex', 0).trigger('change'); // For Select2
                $('div.param-section[data-param="coliforms"]').hide();

            } else {

                // --- Show cholerae-related fields ---
                $("#cholerae_media").attr("required", true).show();
                $("#cholerae_Kitlotno").attr("required", true).show();
                $("#cholerae_KitOpeningDate").attr("required", true).show();
                $("#cholerae_OpeningWells").attr("required", true).show();
                $("#cholerae_UsedWells").attr("required", true).show();
                $("#cholerae_BalanceWells").attr("required", true).show();
                $("#cholerae_equipement").attr("required", true).show();
                $("#cholerae_result1").attr("required", true).show();
                $("#cholerae_completion_date").attr("required", true).show();

                // --- Show VHR sections ---
                $('#vhr, #vrwh, #vrw, #vrw_sub, #vrw_sub2').show();

                // --- Show Coliforms parameter section ---
                $('div.param-section[data-param="coliforms"]').show();
            }
        }


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
                success: function (data) {
                    $("#" + EquipmentUsed).html(data);
                },
                error: function (xhr, status, error) {
                    console.error("AJAX Error: ", error);
                }
            });
        }

        function collectDynamicEntries(paramPrefix) {
            var entries = [];
            $('.param-section[data-param="' + paramPrefix + '"] .param-entry').each(function (i) {
                var entry = $(this);

                var obj = {
                    NameMediaUsed: entry.find('[name*="media"], [id*="media"]').val() || "",
                    Kitlotno: entry.find('[name*="Kitlotno"], [id*="Kitlotno"]').val() ?
                        entry.find('[name*="Kitlotno"], [id*="Kitlotno"]').val().join(",") : "",
                    KitOpeningDate: entry.find('[name*="KitOpeningDate"], [id*="KitOpeningDate"]').val() || "",
                    OpeningWells: entry.find('[name*="OpeningWells"], [id*="OpeningWells"]').val() || "",
                    UsedWells: entry.find('[name*="UsedWells"], [id*="UsedWells"]').val() || "",
                    BalanceWells: entry.find('[name*="BalanceWells"], [id*="BalanceWells"]').val() || "",
                    Equipement: entry.find('[name*="equipement"], [id*="equipement"]').val() ?
                        entry.find('[name*="equipement"], [id*="equipement"]').val().join(",") : "",
                    Result1: entry.find('[name*="result1"], [id*="result1"]').val() || "",
                    Result2: entry.find('[name*="result2"], [id*="result2"]').val() || "",
                    CompletionDate: entry.find('[name*="completion_date"], [id*="completion_date"]').val() || ""
                };

                entries.push(obj);
                console.log("[" + paramPrefix + "] row " + i, obj);
            });
            return entries;
        }

        $(document).ready(function () {

            //$('#tpc_media').select2();
            $('#tpc_equipement').select2();
            //$('#vcholerae_media').select2();
            $('#vcholerae_equipement').select2();
            $('#cholerae_media').select2();
            $('#cholerae_equipement').select2();

            $('#tpc_Kitlotno').select2();
            $('#vcholerae_Kitlotno').select2();
            $('#cholerae_Kitlotno').select2();

            var urlId = '<?php echo $urlId; ?> ';
            var fromDate = "<?php echo $_GET['FromDate']; ?>";
            var toDate = "<?php echo $_GET['ToDate']; ?>";

            if (urlId == 0) {
                loadAdvances(fromDate, toDate);
            } else {
                let vval = $("#report_type").val();
                hideParamater(vval);
                $("#updateid").hide();
                $("#submit").hide();
                $("#updateit").show();

                $("#report_type").prop("disabled", true);
            }

            $(document).on("click", "#updateit", function () {
                event.preventDefault();
                $.ajax({
                    url: 'Ajax/Ajax-ihmbrfwi.php',
                    type: 'POST',
                    data: {
                        methodType: "post",
                        action: "insert",
                        // report_type: $("#report_type").val(),
                        sample_drawn_by: $("#sample_drawn_by").val(),
                        sample_collection: $("#sample_collection").val(),
                        AnalyzedDate: $("#AnalyzedDate").val(),
                        plant_id: $("#plant_id").val(),
                        sample_desc: $("#sample_desc").val(),
                        instant_swab: $("#instant_swab").val(),
                        location: $("#location").val(),
                        tap_no_for_water: $("#tap_no_for_water").val(),
                        itype_of_ice: $("#itype_of_ice").val(),
                        analysed_by: $("#analysed_by").val(),
                        checked_by: $("#checked_by").val(),
                        verified_by: $("#verified_by").val(),
                        remarks: $("#remarks").val(),

                        id: urlId,


                    },
                    success: function (data) { //return false;
                        var newUrl = "ihmbrfwi-create?FromDate=" + fromDate + "&ToDate=" + toDate;
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



            function getMultiVal(el) {
                var val = el.val();
                return Array.isArray(val) ? val.join(',') : val || "";
            }











            // $('#frmCreateIHMRP').submit(function() {
            // event.preventDefault();
            // $.ajax({
            // url: 'Ajax/Ajax-ihmbrfwi.php',
            // type: 'POST',
            // data: {
            // methodType: "post",
            // action: "insert",
            // report_type: $("#report_type").val(),
            // sample_drawn_by: $("#sample_drawn_by").val(),
            // sample_collection: $("#sample_collection").val(),
            // AnalyzedDate: $("#AnalyzedDate").val(),
            // plant_id: $("#plant_id").val(),
            // sample_desc: $("#sample_desc").val(),
            // instant_swab: $("#instant_swab").val(),
            // location: $("#location").val(),
            // tap_no_for_water: $("#tap_no_for_water").val(),
            // itype_of_ice: $("#itype_of_ice").val(),


            // tpc_media: $("#tpc_media").val(),
            // tpc_Kitlotno: $("#tpc_Kitlotno").val().join(','),
            // tpc_KitOpeningDate: $("#tpc_KitOpeningDate").val(),
            // tpc_OpeningWells: $("#tpc_OpeningWells").val(),
            // tpc_UsedWells: $("#tpc_UsedWells").val(),
            // tpc_BalanceWells: $("#tpc_BalanceWells").val(),
            // tpc_equipement: $("#tpc_equipement").val().join(','),
            // tpc_result1: $("#tpc_result1").val(),
            // tpc_result2: $("#tpc_result2").val(),
            // tpc_completion_date: $("#tpc_completion_date").val(),


            // vcholerae_media: $("#vcholerae_media").val(),
            // vcholerae_Kitlotno: $("#vcholerae_Kitlotno").val().join(','),
            // vcholerae_KitOpeningDate: $("#vcholerae_KitOpeningDate").val(),
            // vcholerae_OpeningWells: $("#vcholerae_OpeningWells").val(),
            // vcholerae_UsedWells: $("#vcholerae_UsedWells").val(),
            // vcholerae_BalanceWells: $("#vcholerae_BalanceWells").val(),
            // vcholerae_equipement: $("#vcholerae_equipement").val().join(','),
            // vcholerae_result1: $("#vcholerae_result1").val(),
            // vcholerae_completion_date: $("#vcholerae_completion_date").val(),


            // cholerae_media: $("#cholerae_media").val(),
            // cholerae_Kitlotno: $("#cholerae_Kitlotno").val().join(','),
            // cholerae_KitOpeningDate: $("#cholerae_KitOpeningDate").val(),
            // cholerae_OpeningWells: $("#cholerae_OpeningWells").val(),
            // cholerae_UsedWells: $("#cholerae_UsedWells").val(),
            // cholerae_BalanceWells: $("#cholerae_BalanceWells").val(),
            // cholerae_equipement: $("#cholerae_equipement").val().join(','),
            // cholerae_result1: $("#cholerae_result1").val(),
            // cholerae_completion_date: $("#cholerae_completion_date").val(),

            // analysed_by: $("#analysed_by").val(),
            // checked_by: $("#checked_by").val(),
            // verified_by: $("#verified_by").val(),
            // remarks: $("#remarks").val(),

            // id: urlId,


            // },
            // success: function(data) { //return false;
            // var newUrl = "ihmbrfwi-create?FromDate=" + fromDate + "&ToDate=" + toDate;
            // window.location.href = newUrl;
            // return false;

            // // Reset the entire form
            // document.getElementById('frmCreateIHMRP').reset();

            // // Reset all Select2 dropdowns (including multi-select)
            // $('#frmCreateIHMRP select.select2').val(null).trigger('change');

            // //var newUrl = "in-house-microbiology-report-for-product";
            // //window.location.href = newUrl;
            // return false;
            // }

            // });
            // });


            $('#frmCreateIHMRP').submit(function (event) {
                event.preventDefault();

                // Collect dynamic entries
                var tpcDynamic = collectDynamicEntries("tpc");
                var vcDynamic = collectDynamicEntries("vcholerae");
                var chDynamic = collectDynamicEntries("coliforms");

                // Fallbacks for empty arrays (collect single row from main form fields)
                if (tpcDynamic.length === 0) {
                    tpcDynamic.push({
                        NameMediaUsed: $("#tpc_media").val() || "",
                        Kitlotno: getMultiVal($("#tpc_Kitlotno")),
                        KitOpeningDate: $("#tpc_KitOpeningDate").val() || "",
                        OpeningWells: $("#tpc_OpeningWells").val() || "",
                        UsedWells: $("#tpc_UsedWells").val() || "",
                        BalanceWells: $("#tpc_BalanceWells").val() || "",
                        Equipement: getMultiVal($("#tpc_equipement")),
                        Result1: $("#tpc_result1").val() || "",
                        Result2: $("#tpc_result2").val() || "",
                        CompletionDate: $("#tpc_completion_date").val() || ""
                    });
                }

                if (vcDynamic.length === 0) {
                    vcDynamic.push({
                        NameMediaUsed: $("#vcholerae_media").val() || "",
                        Kitlotno: getMultiVal($("#vcholerae_Kitlotno")),
                        KitOpeningDate: $("#vcholerae_KitOpeningDate").val() || "",
                        OpeningWells: $("#vcholerae_OpeningWells").val() || "",
                        UsedWells: $("#vcholerae_UsedWells").val() || "",
                        BalanceWells: $("#vcholerae_BalanceWells").val() || "",
                        Equipement: getMultiVal($("#vcholerae_equipement")),
                        Result1: $("#vcholerae_result1").val() || "",
                        Result2: $("#vcholerae_result2").val() || "",
                        CompletionDate: $("#vcholerae_completion_date").val() || ""
                    });
                }

                if (chDynamic.length === 0) {
                    chDynamic.push({
                        NameMediaUsed: $("#cholerae_media").val() || "",
                        Kitlotno: getMultiVal($("#cholerae_Kitlotno")),
                        KitOpeningDate: $("#cholerae_KitOpeningDate").val() || "",
                        OpeningWells: $("#cholerae_OpeningWells").val() || "",
                        UsedWells: $("#cholerae_UsedWells").val() || "",
                        BalanceWells: $("#cholerae_BalanceWells").val() || "",
                        Equipement: getMultiVal($("#cholerae_equipement")),
                        Result1: $("#cholerae_result1").val() || "",
                        Result2: $("#cholerae_result2").val() || "",
                        CompletionDate: $("#cholerae_completion_date").val() || ""
                    });
                }
                // ===================== Existing AJAX call =====================
                $.ajax({
                    url: 'Ajax/Ajax-ihmbrfwi.php',
                    type: 'POST',
                    data: {
                        methodType: "post",
                        action: "insert",
                        report_type: $("#report_type").val(),
                        sample_drawn_by: $("#sample_drawn_by").val(),
                        sample_collection: $("#sample_collection").val(),
                        AnalyzedDate: $("#AnalyzedDate").val(),
                        plant_id: $("#plant_id").val(),
                        sample_desc: $("#sample_desc").val(),
                        instant_swab: $("#instant_swab").val(),
                        location: $("#location").val(),
                        tap_no_for_water: $("#tap_no_for_water").val(),
                        itype_of_ice: $("#itype_of_ice").val(),

                        // ===================== OLD fields =====================
                        tpc_media: $("#tpc_media").val(),
                        tpc_Kitlotno: $("#tpc_Kitlotno").val().join(','),
                        tpc_KitOpeningDate: $("#tpc_KitOpeningDate").val(),
                        tpc_OpeningWells: $("#tpc_OpeningWells").val(),
                        tpc_UsedWells: $("#tpc_UsedWells").val(),
                        tpc_BalanceWells: $("#tpc_BalanceWells").val(),
                        tpc_equipement: $("#tpc_equipement").val().join(','),
                        tpc_result1: $("#tpc_result1").val(),
                        tpc_result2: $("#tpc_result2").val(),
                        tpc_completion_date: $("#tpc_completion_date").val(),

                        vcholerae_media: $("#vcholerae_media").val(),
                        vcholerae_Kitlotno: $("#vcholerae_Kitlotno").val().join(','),
                        vcholerae_KitOpeningDate: $("#vcholerae_KitOpeningDate").val(),
                        vcholerae_OpeningWells: $("#vcholerae_OpeningWells").val(),
                        vcholerae_UsedWells: $("#vcholerae_UsedWells").val(),
                        vcholerae_BalanceWells: $("#vcholerae_BalanceWells").val(),
                        vcholerae_equipement: $("#vcholerae_equipement").val().join(','),
                        vcholerae_result1: $("#vcholerae_result1").val(),
                        vcholerae_completion_date: $("#vcholerae_completion_date").val(),

                        cholerae_media: $("#cholerae_media").val(),
                        cholerae_Kitlotno: $("#cholerae_Kitlotno").val().join(','),
                        cholerae_KitOpeningDate: $("#cholerae_KitOpeningDate").val(),
                        cholerae_OpeningWells: $("#cholerae_OpeningWells").val(),
                        cholerae_UsedWells: $("#cholerae_UsedWells").val(),
                        cholerae_BalanceWells: $("#cholerae_BalanceWells").val(),
                        cholerae_equipement: $("#cholerae_equipement").val().join(','),
                        cholerae_result1: $("#cholerae_result1").val(),
                        cholerae_completion_date: $("#cholerae_completion_date").val(),

                        // ===================== DYNAMIC entries =====================
                        tpcDynamicEntries: JSON.stringify(tpcDynamic),
                        vcholeraeDynamicEntries: JSON.stringify(vcDynamic),
                        choleraeDynamicEntries: JSON.stringify(chDynamic),


                        analysed_by: $("#analysed_by").val(),
                        checked_by: $("#checked_by").val(),
                        verified_by: $("#verified_by").val(),
                        remarks: $("#remarks").val(),

                        id: urlId || 0
                    },
                    success: function (data) {
                        try {
                            var res = (typeof data === "string") ? JSON.parse(data) : data;
                            if (!res.Error) {
                                urlId = parseInt(res.LastInsertedID); // Update ID after insert
                                console.log("Insert successful. New ID:", urlId);
                                var dateUrl = '<?php echo "FromDate=". $_GET["FromDate"] . "&ToDate=". $_GET["ToDate"]?>'
                                  window.location.href = "ihmbrfwi-create?" + dateUrl;
                               // window.location.href = "ihmbrfwi-create?id=" + urlId;
                            } else {
                                alert(res.Message || "An error occurred");
                            }
                        } catch (e) {
                            console.error("Invalid JSON from server", data);
                            alert("Unexpected server response");
                        }
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
                            var newUrl = "ihmbrfwi-create?FromDate=" + fromDate + "&ToDate=" + toDate;
                            window.location.href = newUrl;
                            return false;
                        },
                        error: function (xhr, status, error) {
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
                    url: 'Ajax/Ajihmbrfwilist',
                    type: 'POST',
                    data: function (d) {
                        d.fromDate = fromDate;
                        d.toDate = toDate;
                        d.fromPage = 'Create';

                    }
                },
                columns: [{
                    data: 'Sno'
                },
                {
                    data: 'report_type'
                },
                {
                    data: 'sample_collection'
                },
                {
                    data: 'sample_drawn_by'
                },
                {
                    data: null,
                    render: function (data, type, row) {
                        return 'TPC 22°C: ' + (row.tpc_result1 || 'N/A') + '<br>' +
                                'TPC 37°C: ' + (row.tpc_result2 || 'N/A') + '<br>' +
                            'V.Cholerae: ' + (row.vcholerae_result1 || 'N/A') + '<br>' +
                            'Coliforms: ' + (row.cholerae_result1 || 'N/A');
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
                    { targets: [4], orderable: false, searchable: false }, */

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

    function checkVal(UsedWellsId) {
        const { baseId, suffix } = splitId(UsedWellsId, "UsedWells");
        const kitlotnoId = baseId + "Kitlotno" + suffix;
        const kitlotnos = $("#" + kitlotnoId).val();
        if (kitlotnos && kitlotnos.length > 0) {
            kitlotnos.forEach(lot => recalculateAllBalances(lot));
        }
    }

    // ===== Add new parameter entry =====
    $(document).on('click', '.addParamBtn', function() {
        let param = $(this).data('param');
        let section = $(`.param-section[data-param='${param}']`);
        let totalEntries = section.find('.param-entry').length;
        let maxEntries = (param === 'sal') ? 6 : 3;

        if (totalEntries >= maxEntries) {
            alert(`You can only add up to ${maxEntries} entries for this parameter.`);
            return;
        }

        let oldEntry = $(this).closest('.param-entry');

        // Destroy select2 in old entry before cloning
        oldEntry.find('select').each(function() {
            if ($(this).hasClass('select2-hidden-accessible')) {
                $(this).select2('destroy');
            }
        });

        let newEntry = oldEntry.clone(false);
        let uniq = Date.now();

        // Update IDs and labels
        newEntry.find('[id]').each(function() {
            let oldId = $(this).attr('id');
            $(this).attr('id', oldId + '_' + uniq);
        });
        newEntry.find('label[for]').each(function() {
            let oldFor = $(this).attr('for');
            $(this).attr('for', oldFor + '_' + uniq);
        });

        // Clear input values except Kitlotno/NameMediaUsed/EquipmentUsed
        newEntry.find('input').each(function() {
            if (!this.id.includes("Kitlotno") && !this.id.includes("MediaUsed") && !this.id.includes("EquipmentUsed")) {
                $(this).val('');
            }
        });
        newEntry.find('select').each(function() {
            $(this).val(null);
        });

        // Remove old select2 containers if cloned
        newEntry.find('.select2-container').remove();

        // Remove Result and Completion Date fields
        newEntry.find("label").each(function() {
            const labelText = $(this).text().trim().toLowerCase();
            if (labelText.includes('result') || labelText.includes('completion date')) {
                $(this).closest('.form-group').remove();
            }
        });

        section.append('<hr>').append(newEntry);

        // Reinit select2 for Name of Media Used
        newEntry.find("select[id*='media'], select[id*='MediaUsed']").each(function() {
            $(this).select2({ width: '100%' });
        });

        // Reinit select2 for Equipment Used
        newEntry.find("select[id*='equip'], select[id*='EquipmentUsed']").each(function() {
            $(this).select2({ width: '100%' });
        });

        // Disable previous add button
        oldEntry.find('.addBtnRow .addParamBtn').prop('disabled', true);
        if (section.find('.param-entry').length >= maxEntries) {
            newEntry.find('.addBtnRow .addParamBtn').prop('disabled', true);
        }
    });

    // ===== Kitlotno change handler =====
    $(document).on('change', "select[id*='Kitlotno']", function() {
        const { baseId, suffix } = splitId(this.id, "Kitlotno");
        getBalance(
            this.id,
            baseId + "OpeningWells" + suffix,
            baseId + "BalanceWells" + suffix,
            baseId + "KitOpeningDate" + suffix
        );
    });

    // ===== UsedWells keyup/change handler =====
    $(document).on('keyup change', "input[id*='UsedWells']", function() {
        const { baseId, suffix } = splitId(this.id, "UsedWells");
        const kitlotnoId = baseId + "Kitlotno" + suffix;
        const lotnos = $("#" + kitlotnoId).val();
        if (lotnos && lotnos.length > 0) {
            lotnos.forEach(lot => recalculateAllBalances(lot));
        }
    });

    // ===== Name of Media Used change handler =====
    $(document).on('change', "select[id*='media'], select[id*='MediaUsed']", function() {
        let id = $(this).attr('id');
        let base = id.split('_')[0]; // handle cloned IDs
        let equipId = base + "_equip";
        let kitId = base + "_Kitlotno";

        getEquipment(id, equipId);
        getKits(id, kitId);
    });

    // ===== Remove duplicate onkeyup for cloned UsedWells =====
    $(document).on('DOMNodeInserted', '.param-entry', function(e) {
        $(e.target).find("input[id*='UsedWells']").removeAttr('onkeyup');
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

        $(document).on('click', '.addParamBtn', function () {
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
            newEntry.find('[id]').each(function () {
                let oldId = $(this).attr('id');
                $(this).attr('id', oldId + '_' + uniq);
            });
            newEntry.find('label[for]').each(function () {
                let oldFor = $(this).attr('for');
                if (newEntry.find('#' + oldFor + '_' + uniq).length > 0) {
                    $(this).attr('for', oldFor + '_' + uniq);
                }
            });

            // Clear inputs except Media & Lot selects
            newEntry.find('input').each(function () {
                if (!this.id.includes("Kitlotno") && !this.id.includes("media")) this.value = '';
            });
            newEntry.find('select').each(function () {
                if (!this.id.includes("Kitlotno") && !this.id.includes("media")) $(this).val(null);
            });

            // Reset Select2 fields
            newEntry.find('select').each(function () {
                let savedOptions = $(this).find('option').clone(true);
                $(this).siblings('.select2').remove();
                $(this)
                    .removeAttr('data-select2-id aria-hidden tabindex')
                    .removeClass('select2-hidden-accessible');
                $(this).empty().append(savedOptions);
            });

            // Update inline event handlers with new IDs
            newEntry.find('[onchange], [onkeyup]').each(function () {
                let attrType = $(this).attr('onchange') ? 'onchange' : 'onkeyup';
                let oldAttr = $(this).attr(attrType);
                if (oldAttr) {
                    newEntry.find('[id]').each(function () {
                        let newId = $(this).attr('id');
                        let baseId = newId.replace('_' + uniq, '');
                        oldAttr = oldAttr.replace(new RegExp(baseId + "(?!_\\d+)", "g"), newId);
                    });
                    $(this).attr(attrType, oldAttr);
                }
            });

            // Remove Result & Completion Date fields (for cloned entry)
            newEntry.find("label").each(function () {
                const labelText = $(this).text().trim().toLowerCase().replace(/\s+/g, ' ');

                if (labelText.includes('coliforms') || labelText.includes('result') || labelText.includes('completion date')) {
                    $(this).closest('.form-group').remove();
                }

                if (param == 'tpc' && labelText.includes('tpc 35 °c') || labelText.includes('tpc 22 °c')) {
                    $(this).closest('.form-group').remove();
                }
            });


            // Adjust col classes for non-TPC
            if (param !== 'tpc') {
                newEntry.find('.form-group').each(function () {
                    $(this).removeClass('col col-12 col-lg').addClass('col-md-4');
                });
            }


            // For TPC, adjust Equipment Used field class to col-md-4
if (param === 'tpc') {
    newEntry.find("select[id*='equipement']").closest('.form-group').removeClass('col col-12 col-lg').addClass('col-md-4');
}


            // Append cloned entry
            section.append('<hr>');
            section.append(newEntry);

            // Reinitialize all Select2 fields
            newEntry.find("select").each(function () {
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
        $(document).on('change', "select[id*='Kitlotno']", function () {
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
        $(document).on('keyup change', "input[id*='UsedWells']", function () {
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

    <!-- 
<script>
    $(document).ready(function() {
    // Freeze "Type of Report" if editing
    var isEdit = <?php echo isset($ROWSWAB['report_type']) ? 'true' : 'false'; ?>;
    if (isEdit) {
        $('#report_type').prop('disabled', true); // disable native select
        $('#report_type').select2({ disabled: true }); // disable Select2
    }
});

</script> -->


<script>
    function togglefield(sampleDesc) {
        sampleDesc = sampleDesc ? sampleDesc.toUpperCase() : "";

        if (sampleDesc === "WATER") {
            // WATER → Enable tap no, disable ice
            $("#tap_no_for_water").prop("disabled", false);
            $("#itype_of_ice").prop("disabled", true).val("");
        } else if (sampleDesc === "ICE") {
            // ICE → Enable ice, disable tap no
            $("#itype_of_ice").prop("disabled", false);
            $("#tap_no_for_water").prop("disabled", true).val("");
        } else {
            // Default → disable both
            $("#tap_no_for_water").prop("disabled", true).val("");
            $("#itype_of_ice").prop("disabled", true).val("");
        }
    }

    $(document).ready(function () {
        // Run once on load (for edit mode also)
        togglefield($("#sample_desc").val());
    });
</script>



</body>

</html>