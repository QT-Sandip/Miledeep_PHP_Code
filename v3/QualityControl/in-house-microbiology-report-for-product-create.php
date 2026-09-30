<?php



// ini_set('display_errors', 1);

// ini_set('display_startup_errors', 1);

// error_reporting(E_ALL);

include($_SERVER['DOCUMENT_ROOT'] . "/includes/config.php");

include($cp . "Includes/IncludePage.php");



$PageTitleFolder = "Quality Control";

$PageTitle = "In House Microbiology Report For Product Create";



// batch no/lot no based on purchase date

$FromDate = $_GET['FromDate'];

$ToDate = $_GET['ToDate'];



$DateFilter = "between '$FromDate' and  '$ToDate'";



$SqlLots = " select RmLotID,LotNumber from RmLotDetatils as RLD where RLD.PurchaseDate !='' 

  and RLD.PurchaseDate $DateFilter AND NOT EXISTS (

      SELECT 1 

      FROM QcRmAbReport QRAR 

      WHERE QRAR.LotStatus = 1 

        AND FIND_IN_SET(RLD.RmLotID, QRAR.LotID) > 0

  )

  ORDER BY  RLD.PurchaseDate DESC, RLD.LotNumber";



//echo $SqlLots; exit;

$StmtLots = $iconn->query($SqlLots);

// end here



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



//Varity list

$SqlVarietyList = "SELECT ProductID,ShortName FROM `ScrmProductList`  where Status='1' ORDER by ProductID desc ";

$StmtVarietyList = $iconn->query($SqlVarietyList);

//end here



//Medaimaters list

$SqlMediaList = "SELECT MediaID,MediaName FROM `usedmediamaster` WHERE `Status`=1";

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

    $sqlGetRecord = " select * from swabproducts where id= '" . $urlId . "' ";

    $stmturlresults = $iconn->query($sqlGetRecord);

    if ($stmturlresults) {



        $ROWSWAB = $stmturlresults->fetch_array();

        $getMediaquery = " select ParameterID,MediaID,MediaName 

            from usedmediamaster 

            where 

                ParameterID in (

                " . $ROWSWAB['Parameter'] . ",

                " . $ROWSWAB['vp_parameter'] . ",

                " . $ROWSWAB['vc_parameter'] . ",

                " . $ROWSWAB['sa_parameter'] . ",

                " . $ROWSWAB['ec_parameter'] . "

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



$samleCollection = array(

    "RAW MATERIAL RECEIVING" => "RAW MATERIAL RECEIVING",

    "DEHEADING" => "DEHEADING",

    "GRADING" => "GRADING",

    "VLUE ADDITION" => "VLUE ADDITION",

    "SOAKING" => "SOAKING",

    "BLOCK" => "BLOCK",

    "INFEED - ARICO" => "INFEED - ARICO",

    "INFEED - GLORY" => "INFEED - GLORY",

    "OUTFEED - ARICO" => "OUTFEED - ARICO",

    "OUTFEED - GLORY" => "OUTFEED - GLORY",

    "COLDSTORE" => "COLDSTORE",

);



?>

<!DOCTYPE html>

<html lang="en">

<link href="<?php echo $href ?>" rel="stylesheet" type="text/css" />



<head>



    <?php include($IncludesPath . "title-meta.php"); ?>

    <?php include($IncludesPath . "DbLinks.php"); ?>

    <?php include($IncludesPath . "head-css.php"); ?>





    <style>
        .dataTables_wrapper .dataTables_paginate {

            display: flex !important;

            /* justify-content: center !important; */

            width: 100% !important;

            margin-top: 40px !important;

            position: relative;



        }
    </style>



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

                                            <div class="form-group col-lg" id="hidebatchno">

                                                <label for="BatchNumber">Batch No</label>

                                                <select class="drpSelect form-control" name="BatchNumber"
                                                    id="BatchNumber" required multiple="multiple">

                                                    <option Value=""> Select Lot</option>

                                                    <?php $ROWSWAB['BatchNumber'] = explode(',', $ROWSWAB['BatchNumber']);



                                                    while ($RowLotsList = $StmtLots->fetch_array()) {
                                                        ?>

                                                        <option Value="<?php echo $RowLotsList['RmLotID']; ?>" <?php echo (in_array($RowLotsList['RmLotID'], (array) $ROWSWAB['BatchNumber'])) ? "selected='selected'" : ''; ?>>

                                                            <?php echo $RowLotsList['LotNumber']; ?>

                                                        </option>

                                                    <?php } ?>

                                                </select>

                                            </div>

                                            <div class="form-group col-lg">

                                                <label for="Plant">Plant</label>

                                                <select class="form-control" name="Plant" id="Plant" required>

                                                    <option Value=""> Select Plant</option>

                                                    <?php while ($RowPlantsList = $stmtPlants->fetch_array()) {

                                                        ?>

                                                        <option Value="<?php echo $RowPlantsList['ProcessingStationID']; ?>"
                                                            <?php echo ($ROWSWAB['Plant'] == $RowPlantsList['ProcessingStationID']) ? "selected='selected'" : ''; ?>>

                                                            <?php echo $RowPlantsList['StationName']; ?>

                                                        </option>

                                                    <?php } ?>

                                                </select>

                                            </div>

                                            <div class="form-group col-lg">

                                                <label for="exampleInputEmail1">Date Of Sampling</label>

                                                <input required type="date" value="<?php echo $ROWSWAB['SampleDate'] ?>"
                                                    class="form-control" name='SampleDate' id="SampleDate"
                                                    placeholder="Enter Date of Sampling">

                                            </div>



                                            <div class="form-group col-lg">

                                                <label for="DrawnBy">Sample Drawn By </label>

                                                <select class="form-control select2" name="DrawnBy" id="DrawnBy"
                                                    required>

                                                    <option Value=""> Select</option>

                                                    <?php foreach ($RowUsersLists as $RowUsersList) {
                                                        if ($RowUsersList['USER_NAME'] == 'APEX') {
                                                            continue;
                                                        }

                                                        ?>

                                                        <option Value="<?php echo $RowUsersList['USER_ID']; ?>" <?php echo ($ROWSWAB['DrawnBy'] == $RowUsersList['USER_ID']) ? "selected='selected'" : ''; ?>>

                                                            <?php echo $RowUsersList['USER_NAME']; ?>

                                                        </option>

                                                    <?php } ?>

                                                </select>

                                            </div>



                                            <div class="form-group col-lg">

                                                <label for="type_of_method">Type of method</label>

                                                <select class="form-control" name="type_of_method" id="type_of_method"
                                                    required>

                                                    <option Value=""> Select</option>

                                                    <option Value="ISO" <?php echo ($ROWSWAB['type_of_method'] == "ISO") ? 'selected' : ''; ?>>ISO</option>

                                                    <option Value="BAM" <?php echo ($ROWSWAB['type_of_method'] == "BAM") ? 'selected' : ''; ?>>BAM</option>

                                                </select>

                                            </div>



                                            <!--div class="form-group col-lg-3">

                                                <label for="GatePassNo">Gate Pass Number </label>

                                                <input required type="text" class="form-control" name="GatePassNo" id="GatePassNo"

                                                placeholder="Enter Gate Pass Number" value="<?php  //echo $ROWSWAB['GatePassNo'] 
                                                
                                                ?>">

                                            </div-->

                                        </div>

                                        <hr>

                                        <div class="row">

                                            <div class="form-group col-lg-3">

                                                <label for="AnalysisDate">Analysis Date </label>

                                                <input required type="date"
                                                    value="<?php echo $ROWSWAB['AnalysisDate'] ?>" class="form-control"
                                                    name='AnalysisDate' id="AnalysisDate"
                                                    placeholder="Enter Date of Sampling">

                                            </div>



                                            <div class="form-group col-lg-3">

                                                <label for="ReportDate">Report Date </label>

                                                <input required type="date" value="<?php echo $ROWSWAB['ReportDate'] ?>"
                                                    class="form-control" name='ReportDate' id="ReportDate"
                                                    placeholder="Enter Date of Sampling">

                                            </div>



                                            <div class="form-group col-lg-3">

                                                <label for="Variety">Variety </label>

                                                <select class="form-control select2" name="Variety" id="Variety"
                                                    required>

                                                    <option Value=""> Select</option>

                                                    <?php while ($RowVarietyList = $StmtVarietyList->fetch_array()) {

                                                        ?>

                                                        <option Value="<?php echo $RowVarietyList['ProductID']; ?>" <?php echo ($ROWSWAB['Variety'] == $RowVarietyList['ProductID']) ? "selected='selected'" : ''; ?>>

                                                            <?php echo $RowVarietyList['ShortName']; ?>

                                                        </option>

                                                    <?php } ?>



                                                </select>

                                            </div>



                                            <div class="form-group col-lg-3">

                                                <label for="sample_collection">Sample Collection </label>

                                                <select class="form-control select2" name="sample_collection"
                                                    id="sample_collection" required>

                                                    <option Value=""> Select</option>

                                                    <?php foreach ($samleCollection as $RowVarietyList) {

                                                        ?>

                                                        <option Value="<?php echo $RowVarietyList; ?>" <?php echo ($ROWSWAB['sample_collection'] == $RowVarietyList) ? "selected='selected'" : ''; ?>>

                                                            <?php echo $RowVarietyList; ?>

                                                        </option>

                                                    <?php } ?>



                                                </select>

                                            </div>

                                        </div>

                                        <div id="updateMe">

                                            <hr>

                                            <!-- 🔹 TPC SECTION -->
                                            <div class="param-section" data-param="tpc">
                                                <div class="param-entry">
                                                    <div class="row">
                                                        <div class="form-group col">
                                                            <h6>Parameter: Tpc</h6>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="form-group col">
                                                            <label for="NameMediaUsed">Name Of The Media Used</label>
                                                            <select
                                                                class="form-control select2 ec_nameMediaUsed_dynamic"
                                                                name="NameMediaUsed[]" id="NameMediaUsed" required
                                                                onChange="getEquipment('NameMediaUsed','EquipmentUsed'); getKits('NameMediaUsed','Kitlotno')">
                                                                <option Value=""> Select</option>
                                                                <?php foreach ($Media_mast_list as $RowMediaList) { ?>
                                                                    <option Value="<?php echo $RowMediaList['MediaID']; ?>"
                                                                        <?php echo ($RowMediaList['MediaID'] == $ROWSWAB['NameMediaUsed']) ? "selected='selected'" : ''; ?>>
                                                                        <?php echo $RowMediaList['MediaName']; ?>
                                                                    </option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>

                                                        <div class="form-group col">
                                                            <label for="Kitlotno">Lot No</label>
                                                            <select class="form-control select2" name="Kitlotno[]"
                                                                id="Kitlotno" required multiple="multiple"
                                                                onChange="getBalance('Kitlotno','OpeningWells','BalanceWells','KitOpeningDate')">
                                                                <option Value=""> Select</option>
                                                                <?php
                                                                $ROWSWAB['Kitlotno'] = explode(',', $ROWSWAB['Kitlotno']);
                                                                foreach ($mediaMasterList[$ROWSWAB['Parameter']] as $RowMediaList) {
                                                                    ?>
                                                                    <option Value="<?php echo $RowMediaList['MediaID']; ?>"
                                                                        <?php echo in_array($RowMediaList['MediaID'], (array) $ROWSWAB['Kitlotno']) ? "selected='selected'" : ''; ?>>
                                                                        <?php echo $RowMediaList['MediaName']; ?>
                                                                    </option>
                                                                <?php } ?>
                                                            </select>
                                                        </div>

                                                        <div class="form-group col">
                                                            <label for="KitOpeningDate">Opening Date</label>
                                                            <input required type="Date" class="form-control"
                                                                name="KitOpeningDate[]" id="KitOpeningDate">
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="form-group col">
                                                            <label for="OpeningWells">Opening Balance Qty</label>
                                                            <input required type="text" class="form-control"
                                                                name="OpeningWells[]" id="OpeningWells"
                                                                placeholder="Enter Total Qty" readonly="">
                                                        </div>

                                                        <div class="form-group col">
                                                            <label for="UsedWells">Usage Qty</label>
                                                            <input required type="number" step="any"
                                                                class="form-control" name="UsedWells[]" id="UsedWells"
                                                                placeholder="Enter Usage">
                                                        </div>

                                                        <div class="form-group col">
                                                            <label for="BalanceWells">Balance Qty</label>
                                                            <input required type="number" step="any"
                                                                class="form-control" name="BalanceWells[]"
                                                                id="BalanceWells" placeholder="Enter Balance"
                                                                readonly="">
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="form-group col-md-4">
                                                            <label for="EquipmentUsed">Equipment Used</label>
                                                            <select class="form-control" name="EquipmentUsed[]"
                                                                id="EquipmentUsed" required multiple="multiple">
                                                                <option Value=""> Select</option>
                                                                <?php
                                                                $ROWSWAB['EquipmentUsed'] = explode(',', $ROWSWAB['EquipmentUsed']);
                                                                foreach ($ROWSWAB['NameMediaUsed'] as $knmu => $vnmu) {
                                                                    foreach ($equpmentMasterList[$ROWSWAB['Parameter']][$vnmu] as $RoweqList) {
                                                                        ?>
                                                                        <option
                                                                            Value="<?php echo $RoweqList['InstrumentId']; ?>"
                                                                            <?php echo (in_array($RoweqList['InstrumentId'], (array) $ROWSWAB['EquipmentUsed'])) ? "selected='selected'" : ''; ?>>
                                                                            <?php echo $RoweqList['InstrumentName']; ?>
                                                                        </option>
                                                                    <?php }
                                                                } ?>
                                                            </select>
                                                        </div>

                                                        <div class="form-group col">
                                                            <label for="TpcResult">Tpc Result </label>
                                                            <input required type="text" class="form-control"
                                                                name="tpc_Result[]" id="TpcResult"
                                                                placeholder="Enter TPC RESULT"
                                                                value="<?php echo $ROWSWAB['TpcResult'] ?>">
                                                        </div>

                                                        <div class="form-group col">
                                                            <label for="AnalysisDate">Completion Date </label>
                                                            <input required type="date"
                                                                value="<?php echo $ROWSWAB['tp_completed_date'] ?>"
                                                                class="form-control" name="tp_completed_date[]"
                                                                id="tp_completed_date">
                                                        </div>
                                                    </div>

                                                    <div class="row addBtnRow" style="text-align: right;">
                                                        <div class="col">
                                                            <button type="button" class="btn btn-primary addParamBtn"
                                                                data-param="tpc">+ Add</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <hr>

                                            <!-- 🔹 LOOP FOR OTHER PARAMETERS -->
                                            <?php
                                            $listofparameters = array(
                                                "ec" => "E.Coli Result",
                                                "sa" => "S.Aureus Result",
                                                "vc" => "V.Cholerae Result",
                                                "vp" => "V.Parahaemolyticus Result",
                                                "sal" => "Salmonella"
                                            );

                                            foreach ($listofparameters as $lk => $lv) {
                                                $name_field = preg_replace('/[^a-zA-Z0-9]+/', '', ucwords(strtolower($lv)));
                                                ?>
                                                <div class="param-section" data-param="<?php echo $lk; ?>">
                                                    <div class="param-entry">
                                                        <div class="row">
                                                            <div class="form-group col">
                                                                <h6>Parameter: <?php echo str_replace("Result", "", $lv) ?>
                                                                </h6>
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="form-group col-lg">
                                                                <label for="<?php echo $lk; ?>_nameMediaUsed">Name Of The
                                                                    Media Used</label>
                                                                <select class="form-control select2"
                                                                    name="<?php echo $lk; ?>_nameMediaUsed[]"
                                                                    id="<?php echo $lk; ?>_nameMediaUsed" required
                                                                    onChange="getEquipment('<?php echo $lk; ?>_nameMediaUsed','<?php echo $lk; ?>_equipmentUsed');getKits('<?php echo $lk; ?>_nameMediaUsed','<?php echo $lk; ?>_Kitlotno')">
                                                                    <option Value=""> Select</option>
                                                                    <?php foreach ($Media_mast_list as $RowMediaList) { ?>
                                                                        <option Value="<?php echo $RowMediaList['MediaID']; ?>"
                                                                            <?php echo ($RowMediaList['MediaID'] == $ROWSWAB[$lk . '_nameMediaUsed']) ? "selected='selected'" : ''; ?>>
                                                                            <?php echo $RowMediaList['MediaName']; ?>
                                                                        </option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>

                                                            <div class="form-group col">
                                                                <label for="<?php echo $lk; ?>_Kitlotno">Lot No</label>
                                                                <select class="form-control select2"
                                                                    name="<?php echo $lk; ?>_Kitlotno[]"
                                                                    id="<?php echo $lk; ?>_Kitlotno" required
                                                                    multiple="multiple"
                                                                    onChange="getBalance('<?php echo $lk; ?>_Kitlotno','<?php echo $lk; ?>_OpeningWells','<?php echo $lk; ?>_BalanceWells','<?php echo $lk; ?>_KitOpeningDate')">
                                                                    <option Value=""> Select</option>
                                                                    <?php
                                                                    $ROWSWAB[$lk . '_Kitlotno'] = explode(',', $ROWSWAB[$lk . '_Kitlotno']);
                                                                    foreach ($mediaMasterList[$ROWSWAB['Parameter']] as $RowMediaList) {
                                                                        ?>
                                                                        <option Value="<?php echo $RowMediaList['MediaID']; ?>"
                                                                            <?php echo in_array($RowMediaList['MediaID'], (array) $ROWSWAB['Kitlotno']) ? "selected='selected'" : ''; ?>>
                                                                            <?php echo $RowMediaList['MediaName']; ?>
                                                                        </option>
                                                                    <?php } ?>
                                                                </select>
                                                            </div>

                                                            <div class="form-group col">
                                                                <label for="<?php echo $lk; ?>_KitOpeningDate">Opening
                                                                    Date</label>
                                                                <input required type="Date" class="form-control"
                                                                    name="<?php echo $lk; ?>_KitOpeningDate[]"
                                                                    id="<?php echo $lk; ?>_KitOpeningDate">
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="form-group col">
                                                                <label for="<?php echo $lk; ?>_OpeningWells">Opening Balance
                                                                    Qty</label>
                                                                <input required type="text" class="form-control"
                                                                    name="<?php echo $lk; ?>_OpeningWells[]"
                                                                    id="<?php echo $lk; ?>_OpeningWells"
                                                                    placeholder="Enter Total Qty" readonly="">
                                                            </div>

                                                            <div class="form-group col">
                                                                <label for="<?php echo $lk; ?>_UsedWells">Usage Qty</label>
                                                                <input required type="number" step="any"
                                                                    class="form-control"
                                                                    name="<?php echo $lk; ?>_UsedWells[]"
                                                                    id="<?php echo $lk; ?>_UsedWells"
                                                                    placeholder="Enter Usage"
                                                                    onkeyup="checkVal('<?php echo $lk; ?>_UsedWells','<?php echo $lk; ?>_BalanceWells','<?php echo $lk; ?>_Kitlotno')">
                                                            </div>

                                                            <div class="form-group col">
                                                                <label for="<?php echo $lk; ?>_BalanceWells">Balance
                                                                    Qty</label>
                                                                <input required type="number" step="any"
                                                                    class="form-control"
                                                                    name="<?php echo $lk; ?>_BalanceWells[]"
                                                                    id="<?php echo $lk; ?>_BalanceWells"
                                                                    placeholder="Enter Balance" readonly="">
                                                            </div>
                                                        </div>

                                                        <div class="row">
                                                            <div class="form-group col-lg">
                                                                <label for="<?php echo $lk; ?>_equipmentUsed">Equipment
                                                                    Used</label>
                                                                <select class="form-control"
                                                                    name="<?php echo $lk; ?>_equipmentUsed[]"
                                                                    id="<?php echo $lk; ?>_equipmentUsed" required
                                                                    multiple="multiple">
                                                                    <option Value=""> Select</option>
                                                                    <?php
                                                                    $ROWSWAB[$lk . '_equipmentUsed'] = explode(",", $ROWSWAB[$lk . '_equipmentUsed']);
                                                                    foreach ($ROWSWAB[$lk . '_nameMediaUsed'] as $kenmu => $venmu) {
                                                                        foreach ($equpmentMasterList[$ROWSWAB[$lk . '_parameter']][$venmu] as $RoweqList) {
                                                                            ?>
                                                                            <option
                                                                                Value="<?php echo $RoweqList['InstrumentId']; ?>"
                                                                                <?php echo in_array($RoweqList['InstrumentId'], (array) $ROWSWAB[$lk . '_equipmentUsed']) ? "selected='selected'" : ''; ?>>
                                                                                <?php echo $RoweqList['InstrumentName']; ?>
                                                                            </option>
                                                                        <?php }
                                                                    } ?>
                                                                </select>
                                                            </div>

                                                            <div class="form-group col-lg">
                                                                <label
                                                                    for="<?php echo $name_field; ?>"><?php echo $lv; ?></label>
                                                                <input required type="text" class="form-control"
                                                                    name="<?php echo $lk; ?>_Result[]"
                                                                    id="<?php echo $name_field; ?>"
                                                                    placeholder="Enter RESULT"
                                                                    value="<?php echo $ROWSWAB[$name_field] ?>">
                                                            </div>

                                                            <div class="form-group col-lg">
                                                                <label for="<?php echo $lk; ?>_completed_date">Completion
                                                                    Date </label>
                                                                <input required type="date"
                                                                    value="<?php echo $ROWSWAB[$lk . '_completed_date'] ?>"
                                                                    class="form-control"
                                                                    name="<?php echo $lk; ?>_completed_date[]"
                                                                    id="<?php echo $lk; ?>_completed_date"
                                                                    placeholder="Enter Date of Sampling">
                                                            </div>
                                                        </div>

                                                        <div class="row addBtnRow" style="text-align: right;">
                                                            <div class="col">
                                                                <button type="button" class="btn btn-primary addParamBtn"
                                                                    data-param="<?php echo $lk; ?>">+ Add</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <hr>
                                            <?php } ?>
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

                                                <label for="CheckedBy">Checked By : </label>

                                                <select class="form-control select2" name="CheckedBy" id="CheckedBy"
                                                    required>

                                                    <option Value=""> Select</option>

                                                    <?php foreach ($RowUsersLists as $RowUsersList) {
                                                        if ($RowUsersList['USER_NAME'] == 'APEX') {
                                                            continue;
                                                        }
                                                        ?>

                                                        <option Value="<?php echo $RowUsersList['USER_ID']; ?>" <?php echo ($ROWSWAB['CheckedBy'] == $RowUsersList['USER_ID']) ? "selected='selected'" : ''; ?>>

                                                            <?php echo $RowUsersList['USER_NAME']; ?>

                                                        </option>

                                                    <?php } ?>



                                                </select>

                                            </div>



                                            <div class="form-group col-lg-3">

                                                <label for="VerfiedBy">Verified By</label>

                                                <select class="form-control select2" name="VerfiedBy" id="VerfiedBy"
                                                    required>

                                                    <option Value=""> Select</option>

                                                    <?php foreach ($RowUsersLists as $RowUsersList) {
                                                        if ($RowUsersList['USER_NAME'] == 'APEX') {
                                                            continue;
                                                        }
                                                        ?>

                                                        <option Value="<?php echo $RowUsersList['USER_ID']; ?>" <?php echo ($ROWSWAB['VerfiedBy'] == $RowUsersList['USER_ID']) ? "selected='selected'" : ''; ?>>

                                                            <?php echo $RowUsersList['USER_NAME']; ?>

                                                        </option>

                                                    <?php } ?>



                                                </select>

                                            </div>



                                            <div class="form-group col-lg-3">

                                                <label for="Remarks">Remarks</label>

                                                <input required type="text" class="form-control" name="Remarks"
                                                    id="Remarks" placeholder="Enter Remarks"
                                                    value="<?php echo $ROWSWAB['Remarks'] ?>">



                                            </div>



                                        </div>

                                        <hr>

                                        <div class="col-xs-12 " align="right">
                                            <button class="btn btn-danger" type="button" id="closeTab">Close</button>

                                            <button type="submit" name='submit' class="btn btn-success"
                                                id="submit">Submit</button>

                                            <button type="button" name='update' class="btn btn-success" id="update"
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

                                                <th>Batch Number </th>

                                                <th>Sample Collection Date </th>

                                                <th>Sample Drawn By </th>

                                                <th>Result </th>

                                                <th>Analysis Date </th>

                                                <th>Report Date </th>

                                                <th>Variety </th>

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
        function getMedia(Parameter, NameMediaUsed, EquipmentUsed) {

            //GET MediaList

            $("#" + NameMediaUsed).html("");

            $("#" + EquipmentUsed).html("");

            var parameterVal = $("#" + Parameter).val();

            if (parameterVal == 0) {

                return false;

            }

            $.ajax({

                url: 'Ajax/Ajax-inhouse-getmedia.php',

                type: 'POST',

                data: {

                    parameterId: parameterVal,

                    action: "getMediaList"

                },

                success: function (data) {

                    $("#" + NameMediaUsed).html(data);

                },

                error: function (xhr, status, error) {

                    console.error("AJAX Error: ", error);

                }

            });

        }





        function getEquipment(NameMediaUsed, EquipmentUsed, uniqId = '') {

            //GET EQUIPMENT USED
            if (uniqId) {
                NameMediaUsed = NameMediaUsed + uniqId;
                EquipmentUsed = EquipmentUsed + uniqId;
            }
            $("#" + EquipmentUsed).html("");

            let mediaVal = $("#" + NameMediaUsed).val();

            if (mediaVal == 0) {

                return false;

            }

            $.ajax({

                url: 'Ajax/Ajax-inhouse-getmedia.php',

                type: 'POST',

                data: {

                    mediaId: mediaVal,

                    action: "getEquipmentList",

                    fromPage: "ihmbrfpc"

                },

                success: function (data) {

                    $("#" + EquipmentUsed).html(data);

                },

                error: function (xhr, status, error) {

                    console.error("AJAX Error: ", error);

                }

            });

        }



        function getKits(NameMediaUsed, Kitlotno, uniqId = '') {
            //GET Kits Nos


            var {
                baseId,
                suffix
            } = splitId(Kitlotno, "Kitlotno");
            if (uniqId) {
                suffix = suffix + uniqId;
                Kitlotno = Kitlotno + uniqId;
                NameMediaUsed = NameMediaUsed + uniqId;
            }
            $("#" + Kitlotno).html("");
            $('#' + baseId + "OpeningWells" + suffix + ',#' + baseId + "UsedWells" + suffix + ',#' + baseId + "BalanceWells" + suffix + ',#' + baseId + "KitOpeningDate" + suffix).val('')



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
                    //debugger;
                    $("#" + Kitlotno).html(data);

                },

                error: function (xhr, status, error) {
                    //debugger;
                    console.error("AJAX Error: ", error);

                }

            });

        }



        // const kitBalances = {};

        // function getBalance(Kitlotno, OpeningWells, BalanceWells, KitOpeningDate) {

        //     const $row = $(Kitlotno).closest("#BalanceWells");

        //     const kitlotno = $(Kitlotno).val();

        //     $("#" + OpeningWells).val(0);

        //     $("#" + BalanceWells).val(0);



        //     let kitlotnos = $("#" + Kitlotno).val();

        //     var KitlotnoStr = kitlotnos.join(',');



        //     if (KitlotnoStr == 0) return false;



        //     $.ajax({

        //         url: 'Ajax/Ajax-inhouse-getmedia.php',

        //         type: 'POST',

        //         dataType: 'json',

        //         data: {
        //             Kitlotno: KitlotnoStr,
        //             action: "getBalance",
        //             fromPage: "ihmbrfpc"
        //         },

        //         success: function(response) {

        //             if (!response.Error && response.data) {

        //                 const openingWells = parseFloat(response.data.OpeningWells || 0);

        //                 const balanceWells = parseFloat(response.data.BalanceWells || 0);

        //                 const mKitOpeningDate = response.data.mKitOpeningDate;



        //                 kitlotnos.forEach(function(lot) {

        //                     // if (!kitBalances[lot]) {

        //                     //     kitBalances[lot] = {

        //                     //         opening: openingWells,

        //                     //         balance: balanceWells

        //                     //     };

        //                     // }


        //                     kitBalances[lot] = {
        //                         opening: openingWells,
        //                         balance: balanceWells
        //                     };

        //                 });



        //                 // Set values in the current row

        //                 $("#" + OpeningWells).val(balanceWells);

        //                 $("#" + BalanceWells).val(balanceWells);

        //                 $("#" + KitOpeningDate).val(mKitOpeningDate || new Date().toISOString().split('T')[0]);



        //                 // Recalculate all rows using this lot

        //                 kitlotnos.forEach(recalculateAllBalances);

        //             } else {

        //                 console.error("Error in response:", response.Message);

        //             }

        //         },

        //         error: function(xhr, status, error) {

        //             console.error("AJAX error:", error);

        //         }

        //     });

        // }

        function collectDynamicEntries(paramPrefix) {
            var entries = [];
            $('.param-section[data-param="' + paramPrefix + '"] .param-entry').each(function () {
                var entry = $(this);

                function getVal(name) {
                    var elem = entry.find('[name="' + name + '[]"]');
                    if (!elem.length) return null;
                    var val = elem.val();
                    if (Array.isArray(val)) return val.join(','); // normalize multi-selects
                    return val !== "" ? val : null;
                }

                entries.push({
                    NameMediaUsed: getVal(paramPrefix + '_nameMediaUsed') || getVal('NameMediaUsed'),
                    Kitlotno: getVal(paramPrefix + '_Kitlotno') || getVal('Kitlotno'),
                    KitOpeningDate: getVal(paramPrefix + '_KitOpeningDate') || getVal('KitOpeningDate'),
                    OpeningWells: getVal(paramPrefix + '_OpeningWells') || getVal('OpeningWells'),
                    UsedWells: getVal(paramPrefix + '_UsedWells') || getVal('UsedWells'),
                    BalanceWells: getVal(paramPrefix + '_BalanceWells') || getVal('BalanceWells'),
                    EquipmentUsed: getVal(paramPrefix + '_equipmentUsed') || getVal('EquipmentUsed'),
                    Result: getVal(paramPrefix + '_Result') || getVal('Tpc_Result'),
                    CompletedDate: getVal(paramPrefix + '_completed_date') || getVal('tp_completed_date')
                });
            });
            return entries;
        }
        const kitBalances = {};

        function getBalance(Kitlotno, OpeningWells, BalanceWells, KitOpeningDate, UniqId = '') {
            var UsedWells = 'UsedWells';
            if (UniqId) {
                Kitlotno = Kitlotno + UniqId;
                OpeningWells = OpeningWells + UniqId;
                BalanceWells = BalanceWells + UniqId;
                KitOpeningDate = KitOpeningDate + UniqId;
            }
            const {
                baseId,
                suffix
            } = splitId(Kitlotno, "Kitlotno");
            $('#' + baseId + 'UsedWells' + UniqId).val('')
            var tpcDynamic = collectDynamicEntries('tpc');
            //console.log(tpcDynamic);return;
            var ecDynamic = collectDynamicEntries('ec');
            var saDynamic = collectDynamicEntries('sa');
            var vcDynamic = collectDynamicEntries('vc');
            var vpDynamic = collectDynamicEntries('vp');
            var salDynamic = collectDynamicEntries('sal');
            const kitlotnos = $("#" + Kitlotno).val();
            if (!kitlotnos || !Array.isArray(kitlotnos) || kitlotnos.length === 0) {
                return false;
            }
            var kitlotArr = [];
            $(kitlotnos).each(function (index, element) {
                if (element != 0) {
                    kitlotArr.push(element);
                }
            })
            const KitlotnoStr = kitlotArr.join(',');
            $.ajax({
                url: 'Ajax/Ajax-inhouse-getmedia.php',
                type: 'POST',
                dataType: 'json',
                data: {
                    Kitlotno: KitlotnoStr,
                    action: "getBalance",
                    fromPage: "ihmbrfpc",

                    tpcDynamicEntries: JSON.stringify(tpcDynamic),
                    ecDynamicEntries: JSON.stringify(ecDynamic),
                    saDynamicEntries: JSON.stringify(saDynamic),
                    vcDynamicEntries: JSON.stringify(vcDynamic),
                    vpDynamicEntries: JSON.stringify(vpDynamic),
                    salDynamicEntries: JSON.stringify(salDynamic),
                },
                success: function (response) {
                    if (!response.Error && response.data) {
                        const openingWells = parseFloat(response.data.OpeningWells || 0);
                        const balanceWells = parseFloat(response.data.BalanceWells || 0);
                        const mKitOpeningDate = response.data.mKitOpeningDate;

                        kitlotnos.forEach(function (lot) {
                            var lot = lot + UniqId
                            kitBalances[lot] = {
                                opening: openingWells,
                                balance: balanceWells
                            };
                            //console.log(kitBalances)
                        });

                        let lot = kitlotnos[0] + UniqId;
                        let previousBalance = null;

                        // 🔹 Find the last balance for this lot in all visible rows
                        $("[id*='Kitlotno']").each(function () {
                            const selected = $(this).val();
                            if (!selected || !Array.isArray(selected) || !selected.includes(lot)) return;
                            if (this.id === Kitlotno) return; // skip self

                            //  const baseId = this.id.replace("Kitlotno", "");//alert(baseId)
                            //const bal = parseFloat($("#" + baseId + "BalanceWells").val());
                            const {
                                baseId,
                                suffix
                            } = splitId(this.id, "Kitlotno");
                            const bal = parseFloat($("#" + baseId + "BalanceWells").val());
                            if (!isNaN(bal)) {
                                previousBalance = bal; //  always overwrite → ensures last visible balance
                            }
                        });

                        // Use the last visible balance if found
                        /* const openingToUse = previousBalance !== null ? previousBalance : balanceWells;
                        $("#" + OpeningWells).val(openingToUse);
                        $("#" + BalanceWells).val(openingToUse); */
                        $("#" + OpeningWells).val(openingWells);
                        $("#" + BalanceWells).val(balanceWells);

                        $("#" + KitOpeningDate).val(
                            mKitOpeningDate || new Date().toISOString().split('T')[0]
                        );
                        const {
                            baseId,
                            suffix
                        } = splitId(Kitlotno, "Kitlotno");
                        var UsedWells = $('#' + baseId + 'UsedWells' + UniqId).val();
                        if (openingWells < UsedWells) {
                            $('#' + baseId + 'UsedWells' + UniqId).val('')
                        }
                        if (lot) recalculateAllBalances(lot, baseId, UniqId);


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

            $("[id*='Kitlotno']").each(function () {
                const selected = $(this).val();
                if (!selected || !selected.includes(kitlotno)) return;

                const baseId = this.id.replace("Kitlotno", "");
                const used = parseFloat($("#" + baseId + "UsedWells").val()) || 0;
                total += used;
            });

            return total;
        }





        // function recalculateAllBalances(kitlotno, changedInput = null) {

        //     const original = kitBalances[kitlotno];

        //     if (!original) return;



        //     let remaining = original.balance;



        //     $("[id$='Kitlotno']").each(function() {

        //         const selectedLots = $(this).val();

        //         if (!selectedLots || !selectedLots.includes(kitlotno)) return;



        //         const baseId = this.id.replace("Kitlotno", "");

        //         const usedInput = document.getElementById(baseId + "UsedWells");

        //         const usedWells = parseFloat($(usedInput).val()) || 0;



        //         // Set OpeningWells and BalanceWells based on remaining

        //         $("#" + baseId + "OpeningWells").val(remaining);

        //         const updatedBalance = Math.max(remaining - usedWells, 0);

        //         $("#" + baseId + "BalanceWells").val(updatedBalance);



        //         // Update remaining for next row

        //         remaining = updatedBalance;

        //     });

        // }



        function recalculateAllBalancesOld(kitlotno) {
            const original = kitBalances[kitlotno];
            if (!original) return;

            let remaining = original.balance;
            let rowIndex = 1;

            $("[id*='Kitlotno']").each(function () {
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

        function recalculateAllBalances(kitlotno, baseIdF = '', suffixF = '') {
            const original = kitBalances[kitlotno];
            if (suffixF) {
                kitlotno = kitlotno.split(suffixF)[0]
            }
            //console.log(kitlotno)
            if (!original) return;

            //let remaining = original.balance;
            let remaining = original.opening
            let rowIndex = 1;

            //$("[id*='Kitlotno']").each(function() {
            let selectedLots = $('#' + baseIdF + 'Kitlotno' + suffixF).val();
            if (!selectedLots) return;
            if (!Array.isArray(selectedLots)) selectedLots = [selectedLots];
            if (!selectedLots.includes(kitlotno)) return;

            const usedInput = $("#" + baseIdF + "UsedWells" + suffixF);
            const openingInput = $("#" + baseIdF + "OpeningWells" + suffixF);
            const balanceInput = $("#" + baseIdF + "BalanceWells" + suffixF);

            const usedWells = parseFloat(usedInput.val()) || 0;
            openingInput.val(remaining);
            const updatedBalance = Math.max(remaining - usedWells, 0);
            balanceInput.val(updatedBalance);

            console.log(
                `Lot ${kitlotno} row ${rowIndex}: Used ${usedWells}, Opening ${remaining}, Balance ${updatedBalance}`
            );

            // remaining = updatedBalance;
            //rowIndex++;
            //});
        }











        // function checkVal(UsedWells, BalanceWells, kitlotnoId) {

        //     const usedWellsInput = document.getElementById(UsedWells);

        //     const balanceWellsInput = document.getElementById(BalanceWells);

        //     let balanceWells = $("#" + BalanceWells).val();

        //     let usedWells = $("#" + UsedWells).val();



        //     {

        //         //$("#"+BalanceWells).val(balanceWells-usedWells);

        //         const kitlotnos = $("#" + kitlotnoId).val(); // multi-select returns array



        //         if (Array.isArray(kitlotnos)) {

        //             kitlotnos.forEach(function(lot) {

        //                 recalculateAllBalances(lot, usedWellsInput);

        //             });

        //         }



        //         const lot = $("#" + kitlotnoId).val()?.[0]; // assuming single kit per block

        //         if (lot && kitBalances[lot]) {

        //             const totalUsed = getTotalUsedForKit(lot);

        //             const remaining = kitBalances[lot].balance - totalUsed;

        //             balanceWellsInput.value = Math.max(remaining, 0).toFixed(2);

        //             if (remaining < 0) {

        //                 alert("UsedWells should not exceed balanceWells");

        //             }

        //         }

        //     }





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
                    var lot = lot + suffix;
                    recalculateAllBalances(lot, baseId, suffix);
                });
            }
        }







        $(document).ready(function () {




            $('#BatchNumber').select2();

            //$('#NameMediaUsed').select2();

            $('#EquipmentUsed').select2();

            //$('#ec_nameMediaUsed').select2();

            $('#ec_equipmentUsed').select2();

            //$('#sa_nameMediaUsed').select2();

            $('#sa_equipmentUsed').select2();

            //$('#vc_nameMediaUsed').select2();

            $('#vc_equipmentUsed').select2();

            //$('#vp_nameMediaUsed').select2();

            $('#vp_equipmentUsed').select2();

            //$('#sal_nameMediaUsed').select2();

            $('#sal_equipmentUsed').select2();

            $('#Kitlotno').select2();

            $('#ec_Kitlotno').select2();

            $('#sa_Kitlotno').select2();

            $('#vc_Kitlotno').select2();

            $('#vp_Kitlotno').select2();

            $('#sal_Kitlotno').select2();



            var urlId = '<?php echo $urlId; ?> ';

            var fromDate = "<?php echo $_GET['FromDate']; ?>";

            var toDate = "<?php echo $_GET['ToDate']; ?>";



            if (urlId == 0) {

                loadAdvances(fromDate, toDate);

            } else {

                $("#updateMe").hide();

                $("#hidebatchno").hide();

                $("#submit").hide();

                $("#update").show();

            }

            const startDateInput = document.getElementById('AnalysisDate');

            const endDateInput = document.getElementById('ReportDate');



            startDateInput.addEventListener('change', function () {

                const startDate = new Date(this.value);

                if (!isNaN(startDate)) {

                    // Add 5 days

                    const endDate = new Date(startDate);

                    endDate.setDate(startDate.getDate() + 4);



                    // Format the new date as YYYY-MM-DD

                    const yyyy = endDate.getFullYear();

                    const mm = String(endDate.getMonth() + 1).padStart(2, '0');

                    const dd = String(endDate.getDate()).padStart(2, '0');



                    endDateInput.value = `${yyyy}-${mm}-${dd}`;

                }

            });





            $(document).on("click", "#update", function () {

                let drpBatchNumber = $("#BatchNumber").val().join(',');

                $.ajax({

                    url: 'Ajax/Ajax-inhouse-getmedia.php',

                    type: 'POST',

                    dataType: 'json',

                    data: {

                        methodType: "post",

                        action: "update",

                        type_of_method: $("#type_of_method").val(),

                        Plant: $("#Plant").val(),

                        SampleDate: $("#SampleDate").val(),

                        DrawnBy: $("#DrawnBy").val(),

                        GatePassNo: $("#GatePassNo").val(),

                        AnalysisDate: $("#AnalysisDate").val(),

                        ReportDate: $("#ReportDate").val(),

                        Variety: $("#Variety").val(),

                        sample_collection: $("#sample_collection").val(),

                        analysed_by: $("#analysed_by").val(),

                        CheckedBy: $("#CheckedBy").val(),

                        VerfiedBy: $("#VerfiedBy").val(),

                        Remarks: $("#Remarks").val(),



                        id: urlId,

                    },

                    success: function (response) { //return false;

                        alert("Updated successfully");

                        var newUrl = "in-house-microbiology-report-for-product-create?FromDate=" + fromDate + "&ToDate=" + toDate;

                        window.location.href = newUrl;

                        return false;

                    },

                    error: function (xhr, status, error) {

                        console.error("Error:", status, error);

                        //alert("Error occurred while deleting.");

                    }



                });

            });

            if (typeof urlId === "undefined") {
                urlId = 0;
            }


            function collectDynamicEntries(paramPrefix) {
                var entries = [];
                $('.param-section[data-param="' + paramPrefix + '"] .param-entry').each(function () {
                    var entry = $(this);

                    function getVal(name) {
                        var elem = entry.find('[name="' + name + '[]"]');
                        if (!elem.length) return null;
                        var val = elem.val();
                        if (Array.isArray(val)) return val.join(','); // normalize multi-selects
                        return val !== "" ? val : null;
                    }

                    entries.push({
                        NameMediaUsed: getVal(paramPrefix + '_nameMediaUsed') || getVal('NameMediaUsed'),
                        Kitlotno: getVal(paramPrefix + '_Kitlotno') || getVal('Kitlotno'),
                        KitOpeningDate: getVal(paramPrefix + '_KitOpeningDate') || getVal('KitOpeningDate'),
                        OpeningWells: getVal(paramPrefix + '_OpeningWells') || getVal('OpeningWells'),
                        UsedWells: getVal(paramPrefix + '_UsedWells') || getVal('UsedWells'),
                        BalanceWells: getVal(paramPrefix + '_BalanceWells') || getVal('BalanceWells'),
                        EquipmentUsed: getVal(paramPrefix + '_equipmentUsed') || getVal('EquipmentUsed'),
                        Result: getVal(paramPrefix + '_Result') || getVal('Tpc_Result'),
                        CompletedDate: getVal(paramPrefix + '_completed_date') || getVal('tp_completed_date')
                    });
                });
                return entries;
            }









            $('#frmCreateIHMRP').submit(function (event) {
                event.preventDefault();

                let data = $("#frmCreateIHMRP").serialize();
                console.log(data);//return;
                var drpBatchNumber = $("#BatchNumber").val().join(',');

                //var NameMediaUsed = $('#NameMediaUsed').val();
                var NameMediaUsed = $("[name='NameMediaUsed[]']").val();
                // var EquipmentUsed = $('#EquipmentUsed').val().join(',');
                var EquipmentUsed = $("[name='EquipmentUsed[]']").val().join(',');

                // var ec_nameMediaUsed = $('#ec_nameMediaUsed').val();
                var ec_nameMediaUsed = $("[name='ec_nameMediaUsed[]']").val()
                // var ec_equipmentUsed = $('#ec_equipmentUsed').val().join(',');
                var ec_equipmentUsed = $("[name='ec_equipmentUsed[]']").val().join(',');

                // var sa_nameMediaUsed = $('#sa_nameMediaUsed').val();
                var sa_nameMediaUsed = $("[name='sa_nameMediaUsed[]']").val()
                // var sa_equipmentUsed = $('#sa_equipmentUsed').val().join(',');
                var sa_equipmentUsed = $("[name='sa_equipmentUsed[]']").val().join(',');

                // var vc_nameMediaUsed = $('#vc_nameMediaUsed').val();
                var vc_nameMediaUsed = $("[name='vc_nameMediaUsed[]']").val()
                //var vc_equipmentUsed = $('#vc_equipmentUsed').val().join(',');
                var vc_equipmentUsed = $("[name='vc_equipmentUsed[]']").val().join(',');

                //var vp_nameMediaUsed = $('#vp_nameMediaUsed').val();
                var vp_nameMediaUsed = $("[name='vp_nameMediaUsed[]']").val()
                //var vp_equipmentUsed = $('#vp_equipmentUsed').val().join(',');
                var vp_equipmentUsed = $("[name='vp_equipmentUsed[]']").val().join(',');

                //var sal_nameMediaUsed = $('#sal_nameMediaUsed').val();
                var sal_nameMediaUsed = $("[name='sal_nameMediaUsed[]']").val()
                //var sal_equipmentUsed = $('#sal_equipmentUsed').val().join(',');
                var sal_equipmentUsed = $("[name='sal_equipmentUsed[]']").val().join(',');

                // var Kitlotno = $('#Kitlotno').val().join(',');
                var Kitlotno = $("[name='Kitlotno[]']").val().join(',');

                /*  var ec_Kitlotno = $('#ec_Kitlotno').val().join(',');
                 var sa_Kitlotno = $('#sa_Kitlotno').val().join(',');
                 var vc_Kitlotno = $('#vc_Kitlotno').val().join(',');
                 var vp_Kitlotno = $('#vp_Kitlotno').val().join(',');
                 var sal_Kitlotno = $('#sal_Kitlotno').val().join(','); */

                var ec_Kitlotno = $("[name='ec_Kitlotno[]']").val().join(',');
                var sa_Kitlotno = $("[name='sa_Kitlotno[]']").val().join(',');
                var vc_Kitlotno = $("[name='vc_Kitlotno[]']").val().join(',');
                var vp_Kitlotno = $("[name='vp_Kitlotno[]']").val().join(',');
                var sal_Kitlotno = $("[name='sal_Kitlotno[]']").val().join(',');

                // Prepare dynamic entry objects
                var tpcDynamic = collectDynamicEntries('tpc');
                //console.log(tpcDynamic);return;
                var ecDynamic = collectDynamicEntries('ec');
                var saDynamic = collectDynamicEntries('sa');
                var vcDynamic = collectDynamicEntries('vc');
                var vpDynamic = collectDynamicEntries('vp');
                var salDynamic = collectDynamicEntries('sal');

                // Fallback single row if no dynamic entries
                if (ecDynamic.length === 0) {
                    ecDynamic = [{
                        NameMediaUsed: ec_nameMediaUsed,
                        Kitlotno: ec_Kitlotno,
                        KitOpeningDate: $("[name='ec_KitOpeningDate[]']").val(),
                        OpeningWells: $("[name='ec_OpeningWells[]']").val(),
                        UsedWells: $("[name='ec_UsedWells[]']").val(),
                        BalanceWells: $("[name='ec_BalanceWells[]']").val(),
                        EquipmentUsed: ec_equipmentUsed
                    }];
                }
                if (saDynamic.length === 0) {
                    saDynamic = [{
                        NameMediaUsed: sa_nameMediaUsed,
                        Kitlotno: sa_Kitlotno,
                        KitOpeningDate: $("[name='sa_KitOpeningDate[]']").val(),
                        OpeningWells: $("[name='sa_OpeningWells[]']").val(),
                        UsedWells: $("[name='sa_UsedWells[]']").val(),
                        BalanceWells: $("[name='sa_BalanceWells[]']").val(),
                        EquipmentUsed: sa_equipmentUsed
                    }];
                }
                if (vcDynamic.length === 0) {
                    vcDynamic = [{
                        NameMediaUsed: vc_nameMediaUsed,
                        Kitlotno: vc_Kitlotno,
                        KitOpeningDate: $("[name='vc_KitOpeningDate[]']").val(),
                        OpeningWells: $("[name='vc_OpeningWells[]']").val(),
                        UsedWells: $("[name='vc_UsedWells[]']").val(),
                        BalanceWells: $("[name='vc_BalanceWells[]']").val(),
                        EquipmentUsed: vc_equipmentUsed
                    }];
                }
                if (vpDynamic.length === 0) {
                    vpDynamic = [{
                        NameMediaUsed: vp_nameMediaUsed,
                        Kitlotno: vp_Kitlotno,
                        KitOpeningDate: $("[name='vp_KitOpeningDate[]']").val(),
                        OpeningWells: $("[name='vp_OpeningWells[]']").val(),
                        UsedWells: $("[name='vp_UsedWells[]']").val(),
                        BalanceWells: $("[name='vp_BalanceWells[]']").val(),
                        EquipmentUsed: vp_equipmentUsed
                    }];
                }
                if (salDynamic.length === 0) {
                    salDynamic = [{
                        NameMediaUsed: sal_nameMediaUsed,
                        Kitlotno: sal_Kitlotno,
                        KitOpeningDate: $("[name='sal_KitOpeningDate[]']").val(),
                        OpeningWells: $("[name='sal_OpeningWells[]']").val(),
                        UsedWells: $("[name='sal_UsedWells[]']").val(),
                        BalanceWells: $("[name='sal_BalanceWells[]']").val(),
                        EquipmentUsed: sal_equipmentUsed
                    }];
                }
                $.ajax({
                    url: 'Ajax/Ajax-inhouse-getmedia.php',
                    type: 'POST',
                    data: {
                        methodType: "post",
                        action: (urlId && urlId > 0) ? "update" : "insert", // insert or update
                        type_of_method: $('#type_of_method').val(),

                        BatchNumber: drpBatchNumber,
                        Plant: $("#Plant").val(),
                        SampleDate: $("#SampleDate").val(),
                        DrawnBy: $("#DrawnBy").val(),
                        GatePassNo: $("#GatePassNo").val(),
                        AnalysisDate: $("#AnalysisDate").val(),
                        ReportDate: $("#ReportDate").val(),
                        Variety: $("#Variety").val(),
                        sample_collection: $("#sample_collection").val(),

                        Parameter: 0,
                        NameMediaUsed: NameMediaUsed,
                        Kitlotno: Kitlotno,
                        KitOpeningDate: $('#KitOpeningDate').val(),
                        OpeningWells: $('#OpeningWells').val(),
                        UsedWells: $('#UsedWells').val(),
                        BalanceWells: $('#BalanceWells').val(),
                        EquipmentUsed: EquipmentUsed,
                        TpcResult: $("#TpcResult").val(),
                        tp_completed_date: $("#tp_completed_date").val(),

                        ec_parameter: $("#ec_parameter").val(),
                        ec_nameMediaUsed: ec_nameMediaUsed,
                        ec_Kitlotno: ec_Kitlotno,
                        ec_KitOpeningDate: $('#ec_KitOpeningDate').val(),
                        ec_OpeningWells: $('#ec_OpeningWells').val(),
                        ec_UsedWells: $('#ec_UsedWells').val(),
                        ec_BalanceWells: $('#ec_BalanceWells').val(),
                        ec_equipmentUsed: ec_equipmentUsed,
                        EcoliResult: $("#EcoliResult").val(),
                        ec_completed_date: $("#ec_completed_date").val(),

                        sa_parameter: $("#sa_parameter").val(),
                        sa_nameMediaUsed: sa_nameMediaUsed,
                        sa_Kitlotno: sa_Kitlotno,
                        sa_KitOpeningDate: $('#sa_KitOpeningDate').val(),
                        sa_OpeningWells: $('#sa_OpeningWells').val(),
                        sa_UsedWells: $('#sa_UsedWells').val(),
                        sa_BalanceWells: $('#sa_BalanceWells').val(),
                        sa_equipmentUsed: sa_equipmentUsed,
                        SaureusResult: $("#SaureusResult").val(),
                        sa_completed_date: $("#sa_completed_date").val(),

                        vc_parameter: $("#vc_parameter").val(),
                        vc_nameMediaUsed: vc_nameMediaUsed,
                        vc_Kitlotno: vc_Kitlotno,
                        vc_KitOpeningDate: $('#vc_KitOpeningDate').val(),
                        vc_OpeningWells: $('#vc_OpeningWells').val(),
                        vc_UsedWells: $('#vc_UsedWells').val(),
                        vc_BalanceWells: $('#vc_BalanceWells').val(),
                        vc_equipmentUsed: vc_equipmentUsed,
                        VcholeraeResult: $("#VcholeraeResult").val(),
                        vc_completed_date: $("#vc_completed_date").val(),

                        vp_parameter: $("#vp_parameter").val(),
                        vp_nameMediaUsed: vp_nameMediaUsed,
                        vp_Kitlotno: vp_Kitlotno,
                        vp_KitOpeningDate: $('#vp_KitOpeningDate').val(),
                        vp_OpeningWells: $('#vp_OpeningWells').val(),
                        vp_UsedWells: $('#vp_UsedWells').val(),
                        vp_BalanceWells: $('#vp_BalanceWells').val(),
                        vp_equipmentUsed: vp_equipmentUsed,
                        VparahaemolyticusResult: $("#VparahaemolyticusResult").val(),
                        vp_completed_date: $("#vp_completed_date").val(),

                        sal_parameter: $("#sal_parameter").val(),
                        sal_nameMediaUsed: sal_nameMediaUsed,
                        sal_Kitlotno: sal_Kitlotno,
                        sal_KitOpeningDate: $('#sal_KitOpeningDate').val(),
                        sal_OpeningWells: $('#sal_OpeningWells').val(),
                        sal_UsedWells: $('#sal_UsedWells').val(),
                        sal_BalanceWells: $('#sal_BalanceWells').val(),
                        sal_equipmentUsed: sal_equipmentUsed,
                        Salmonella: $("#Salmonella").val(),
                        sal_completed_date: $("#sal_completed_date").val(),

                        analysed_by: $("#analysed_by").val(),
                        CheckedBy: $("#CheckedBy").val(),
                        VerfiedBy: $("#VerfiedBy").val(),
                        Remarks: $("#Remarks").val(),

                        tpcDynamicEntries: JSON.stringify(tpcDynamic),
                        ecDynamicEntries: JSON.stringify(ecDynamic),
                        saDynamicEntries: JSON.stringify(saDynamic),
                        vcDynamicEntries: JSON.stringify(vcDynamic),
                        vpDynamicEntries: JSON.stringify(vpDynamic),
                        salDynamicEntries: JSON.stringify(salDynamic),

                        id: urlId || 0
                    },
                    success: function (data) {
                        try {
                            var res = (typeof data === "string") ? JSON.parse(data) : data;
                            console.log("Server response:", res);
                            if (res.BalMessage) {
                                alert(res.BalMessage);
                                return;
                            }
                            if (!res.Error) {
                                if (!urlId || urlId == 0) {
                                    urlId = parseInt(res.LastInsertedID); // ensure it's a number
                                    console.log(" Insert successful. New ID:", urlId);

                                    // Optional: If you want to continue working with this same record
                                    // You can call another function to save dynamic entries if needed

                                    var dateUrl = '<?php echo "FromDate=". $_GET["FromDate"] . "&ToDate=". $_GET["ToDate"]?>'
                                    // Later you can do:
                                     window.location.href = "in-house-microbiology-report-for-product-create?" + dateUrl;
                                    //window.location.href = "in-house-microbiology-report-for-product-create?id=" + urlId;
                                } else {
                                    alert("Record updated successfully.");
                                }


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

                        url: 'Ajax/Ajax-inhouse-getmedia.php',

                        type: 'POST',

                        dataType: 'json',

                        data: {

                            'id': selectedID, // Pass LotIds as an array

                            'action': 'Delete'

                        },

                        success: function (response) {

                            alert("Deleted successfully");

                            var newUrl = "in-house-microbiology-report-for-product-create?FromDate=" + fromDate + "&ToDate=" + toDate;

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

                    url: 'Ajax/AjSwabProdList',

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

                    data: 'BatchNumber'

                },

                {

                    data: 'SampleDate'

                },

                {

                    data: 'DrawnBy'

                },
                {
                    data: null,
                    render: function (data, type, row) {
                        return 'TPC: ' + (row.TpcResultValue || 'N/A') + '<br>' +
                            'E.Coli: ' + (row.EcoliResultValue || 'N/A') + '<br>' +
                            'S.Aureus: ' + (row.SaureusResultValue || 'N/A') + '<br>' +
                            'V.Cholerae: ' + (row.VcholeraeResultValue || 'N/A') + '<br>' +
                            'V.Parahaemolyticus: ' + (row.VparahaemolyticusResultValue || 'N/A') + '<br>' +
                            'Salmonella: ' + (row.SalmonellaValue || 'N/A');
                    }
                },
                {

                    data: 'AnalysisDate'

                },

                {

                    data: 'ReportDate'

                },

                {

                    data: 'Variety'

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

                ordering: false,



                language: {

                    paginate: {

                        previous: "<i class='mdi mdi-chevron-left'></i>",

                        next: "<i class='mdi mdi-chevron-right'></i>"

                    }

                },

                drawCallback: function () {

                    $('.dataTables_paginate > .pagination').addClass('pagination-rounded');

                }







            });

        }
    </script>



    <script>
        function splitId(id, fieldName) {
            const idx = id.indexOf(fieldName);
            return {
                baseId: id.substring(0, idx),
                suffix: id.substring(idx + fieldName.length)
            };
        }

        /* function checkVal(UsedWellsId) {
            const {
                baseId,
                suffix
            } = splitId(UsedWellsId, "UsedWells");
            const kitlotnoId = baseId + "Kitlotno" + suffix;
            const kitlotnos = $("#" + kitlotnoId).val();
            if (kitlotnos && kitlotnos.length > 0) {
                kitlotnos.forEach(lot => recalculateAllBalances(lot,baseId,suffix));
            }
        } */

        $(document).on('click', '.addParamBtn', function () {
            let param = $(this).data('param');
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

            // update IDs
            newEntry.find('[id]').each(function () {
                let oldId = $(this).attr('id');
                oldId = oldId.split('_')
                //console.log(oldId)
                var condition = (param == 'tpc') ? oldId.length > 2 : oldId.length >= 2;
                if (condition) {
                    $(this).attr('id', oldId[0] + '_' + oldId[1] + '_' + uniq);
                } else {
                    $(this).attr('id', oldId[0] + '_' + uniq);
                }
            });

            // update label for=
            /* newEntry.find('label[for]').each(function() {
                let oldFor = $(this).attr('for');
                if (newEntry.find('#' + oldFor + '_' + uniq).length > 0) {
                    $(this).attr('for', oldFor + '_' + uniq);
                }
            }); */
            newEntry.find("label[for]").each(function () {
                let oldFor = $(this).attr("for");

                // only update if it doesn't already end with _uniq
                if (!oldFor.endsWith("_" + uniq)) {
                    $(this).attr("for", oldFor + "_" + uniq);
                }
            });


            // clear inputs except Kitlotno/NameMediaUsed
            newEntry.find('input').each(function () {
                if (!this.id.includes("Kitlotno") && !this.id.includes("NameMediaUsed")) {
                    this.value = '';
                }
            });
            newEntry.find('select').each(function () {
                if (!this.id.includes("Kitlotno") && !this.id.includes("NameMediaUsed")) {
                    $(this).val(null);
                }
            });

            // Reset Select2 markup
            newEntry.find('select').each(function () {
                let savedOptions = $(this).find('option').clone(true);
                $(this).siblings('.select2').remove();
                $(this)
                    .removeAttr('data-select2-id aria-hidden tabindex')
                    .removeClass('select2-hidden-accessible');
                $(this).empty().append(savedOptions);
            });

            // Update onchange IDs
            newEntry.find('[onchange]').each(function () {
                let oldAttr = $(this).attr('onchange');//alert(oldAttr)
                /* if (oldAttr) {
                   // let newAttr = oldAttr.replace(/(_\d+)?$/, '_' + uniq);
                    let newAttr = oldAttr.replace(/\)/g, ", '_" + uniq + "')");
                    $(this).attr('onchange', newAttr);
                } */
                if (oldAttr) {
                    // Split multiple function calls
                    let funcs = oldAttr.split(";");

                    let updatedFuncs = funcs.map(function (f) {
                        f = f.trim();
                        if (!f) return "";
                        // Remove last argument, add new one
                        return f.replace(/\((.*?)\)/, function (match, inner) {
                            let args = inner.split(",");
                            // args.pop();  
                            if (args.length && /^['"]?_.*/.test(args[args.length - 1])) {
                                args.pop();
                            }                      // remove last argument if argument has _
                            args.push("'_" + uniq + "'");     // add new
                            return "(" + args.join(",") + ")";
                        });
                    });

                    let newAttr = updatedFuncs.join("; ");
                    $(this).attr("onchange", newAttr);
                }
            });

            // Remove "Result" and "Completion Date" for all parameters
            newEntry.find("label").each(function () {
                const labelText = $(this).text().trim().toLowerCase();
                if (labelText.includes('result') || labelText.includes('completion date')) {
                    $(this).closest('.form-group').remove();
                }
            });

            // For sal parameter, remove "Salmonella" field
            if (param === 'sal') {
                newEntry.find("label").each(function () {
                    const labelText = $(this).text().trim().toLowerCase();
                    if (labelText === 'salmonella') {
                        $(this).closest('.form-group').remove();
                    }
                });
            }

            // adjust column classes only for non-tpc parameters
            if (param !== 'tpc') {
                newEntry.find('.form-group').each(function () {
                    const label = $(this).find('label').text().trim().toLowerCase();
                    if (label.includes('equipment used')) {
                        $(this).removeClass('col col-12 col-lg').addClass('col-md-4');
                    } else {
                        $(this).removeClass('col col-12 col-lg').addClass('col-md-4');
                    }
                });
            }

            section.append('<hr>');
            section.append(newEntry);

            // Reinit all selects
            newEntry.find("select").each(function () {
                // Destroy old instance if exists
                if ($(this).hasClass("select2-hidden-accessible") || $(this).data('select2')) {
                    alert()
                    $(this).select2("destroy");
                }

                // Give unique ID if necessary
                /* let oldId = $(this).attr("id");
                if (oldId) {
                    $(this).attr("id", oldId + "_" + uniq);
                } */

                // Reinitialize
                $(this).select2({
                    width: "100%"
                });
            });

            // Copy Lot No
            let masterLotSelect = section.find("select[id*='Kitlotno']").first();
            let newLotSelect = newEntry.find("select[id*='Kitlotno']");
            if (newLotSelect.length) {
                let lotOptions = masterLotSelect.length ?
                    masterLotSelect.find('option').clone(true) :
                    oldEntry.find("select[id*='Kitlotno'] option").clone(true);

                newLotSelect.empty().append(lotOptions);
                if (newLotSelect.data('select2')) newLotSelect.select2('destroy');
                newLotSelect.select2({
                    width: '100%'
                });
                newLotSelect.val(null).trigger('change');
            }

            // Copy Name of Media Used with AJAX config
            let masterMediaSelect = section.find("select[name$='nameMediaUsed[]'], select[name='NameMediaUsed[]']").first();
            let newMediaSelect = newEntry.find("select[name$='nameMediaUsed[]'], select[name='NameMediaUsed[]']");

            if (newMediaSelect.length) {
                let mediaOptions = masterMediaSelect.length ?
                    masterMediaSelect.find('option').clone(true) :
                    oldEntry.find("select[id*='NameMediaUsed'] option").clone(true);

                newMediaSelect.empty().append(mediaOptions);
                if (newMediaSelect.data('select2')) newMediaSelect.select2('destroy');

                if (masterMediaSelect.data('select2')) {
                    let cfg = masterMediaSelect.data('select2').options.options;
                    newMediaSelect.select2(cfg);
                } else {
                    newMediaSelect.select2({
                        width: '100%'
                    });
                }

                newMediaSelect.val(null).trigger('change');
            }

            //  Updated Equipment Used reinit — special case for TPC
            if (param === 'tpc') {
                // Match first EquipmentUsed dropdown even if it has a suffix
                masterEquipSelect = section.find(".param-entry").first().find("select[id^='EquipmentUsed']");
                // Match the cloned dropdown for TPC even if it has a suffix
                newEquipSelect = newEntry.find("select[id^='EquipmentUsed']");
            } else {
                masterEquipSelect = section.find("select[id*='equipmentUsed'], select[id='EquipmentUsed'], select[name*='equipmentUsed']").first();
                newEquipSelect = newEntry.find("select[id*='equipmentUsed'], select[id='EquipmentUsed'], select[name*='equipmentUsed']");
            }


            if (newEquipSelect.length) {
                let equipOptions = masterEquipSelect.find('option').clone(true);
                newEquipSelect.empty().append(equipOptions);

                if (newEquipSelect.data('select2')) newEquipSelect.select2('destroy');

                if (masterEquipSelect.data('select2')) {
                    let cfg = masterEquipSelect.data('select2').options.options;
                    newEquipSelect.select2(cfg);
                } else {
                    newEquipSelect.select2({
                        width: '100%'
                    });
                }

                newEquipSelect.val(null).trigger('change');
            }

            // disable previous add button
            oldEntry.find('.addBtnRow .addParamBtn').prop('disabled', true);
            if (section.find('.param-entry').length >= maxEntries) {
                newEntry.find('.addBtnRow .addParamBtn').prop('disabled', true);
            }
        });

        $(document).on('change', "select[id*='Kitlotno']", function () {
            const {
                baseId,
                suffix
            } = splitId(this.id, "Kitlotno");
            /* getBalance(
                this.id,
                baseId + "OpeningWells" + suffix,
                baseId + "BalanceWells" + suffix,
                baseId + "KitOpeningDate" + suffix
            ); */
            var Kitlotnos = $(this).val();
            if ((Kitlotnos.length == 1 && Kitlotnos[0] == 0) || Kitlotnos.length == 0) {
                $('#' + baseId + "OpeningWells" + suffix + ',#' + baseId + "UsedWells" + suffix + ',#' + baseId + "BalanceWells" + suffix + ',#' + baseId + "KitOpeningDate" + suffix).val('')
            }
        });

        $(document).on('keyup change', "input[id*='UsedWells']", function () {
            //debugger;
            const {
                baseId,
                suffix
            } = splitId(this.id, "UsedWells");
            const kitlotnoId = baseId + "Kitlotno" + suffix;
            const lotnos = $("#" + kitlotnoId).val();
            if (lotnos && lotnos.length > 0) {
                lotnos.forEach(lot => recalculateAllBalances(lot + suffix, baseId, suffix));
            }
        });

        $(document).on('DOMNodeInserted', '.param-entry', function (e) {
            $(e.target).find("input[id*='UsedWells']").removeAttr('onkeyup');
        });

        $(document).on('keyup change', "input[id*='UsedWells']", function () {
            const {
                baseId,
                suffix
            } = splitId(this.id, "UsedWells");
            var OpeningWells = $('#' + baseId + "OpeningWells" + suffix).val() != '' ? $('#' + baseId + "OpeningWells" + suffix).val() : 0;
            //alert(parseFloat($(this).val())+' > '+parseFloat(OpeningWells))
            if (parseFloat($(this).val()) > parseFloat(OpeningWells)) {
                alert('Balance Exceeds');
                $(this).val('');
                var OpeningWells = $('#' + baseId + "OpeningWells" + suffix).val()
                $('#' + baseId + "BalanceWells" + suffix).val(OpeningWells)
            }
        });
        /* $(document).on('change', "select[id*='Kitlotno']", function() {
            const {
                baseId,
                suffix
            } = splitId(this.id, "Kitlotno");
            const kitlotnoId = baseId + "Kitlotno" + suffix;
            const lotnos = $("#" + kitlotnoId).val();
            if (lotnos && lotnos.length > 0) {
                lotnos.forEach(lot => recalculateAllBalances(lot));
            }
        }); */
    </script>




































</body>

</html>