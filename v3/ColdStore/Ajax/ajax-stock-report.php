<style>
    #tblDataTable {
        overflow: auto;
        white-space: nowrap;
    }

    #tblDataTable th,
    #tblDataTable td {
        white-space: nowrap;
        vertical-align: middle;
    }

    /* Sticky Columns */
    .sticky-col {
        position: sticky;
        background: #fff;
        z-index: 2;
    }

    .sticky-header {
        z-index: 5 !important;
        background: #e9ecef !important;
    }
</style>

<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$ClientConfigData = $_SESSION['ClientConfigData'];
$showStockUnitWise = $ClientConfigData['showStockUnitWise'];
$convertLooseToMcs = $ClientConfigData['convertLooseToMcs'];
$StockReportDisableFilterFields = $ClientConfigData['StockReportDisableFilterFields'];

$api = API_STOCK_REPORT;

$bodyContent = array(

    'ColdstoreID' => trim($_POST["coldStore"]),
    'TenantID' => trim($_POST["stockBlgsTo"]),
    'VarietyID' => trim($_POST["variety"]),
    'PackData' => trim($_POST["packingType"]),
    'Category' => trim($_POST["pdCategory"]),
    'Group' => trim($_POST["group"]),
    'Grade' => trim($_POST["grade"]),
    'PackingStyle' => trim($_POST["pckgStyle"]),
    'FreezingType' => trim($_POST["freezingType"]),
    'TypeProduct' => trim($_POST["productType"]),
    'Blocks' => trim($_POST["blocksTwo"]),
    'Soaking' => trim($_POST["soaking"]),
    'AntiBiotic' => trim($_POST["abStatus"]),
    'PONumber' => trim($_POST["poNumber"]),
    'Species' => trim($_POST["species"]),
    'Brand' => trim($_POST["brand"]),
    'Type' => trim($_POST["Type"]),
    'ConvertLooseToMcs' => $convertLooseToMcs,
    'PageNo' => trim($_POST["pageNo"]),
    'SearchKey' => trim($_POST["searchKey"]),
    'LoadAll' => trim($_POST["loadAll"])

);

$resList = CallAPI($api, "GET", "", $bodyContent);

if (isset($resList) && !empty($resList)) {

    $Data = '';

    if ($StockReportDisableFilterFields == 1 && !empty(trim($_POST["species"]))) {
        $IsSpecies = 1;
        $Data .= 'Species : ' . $_POST["species"] . ' | ';
    } else {
        $IsSpecies = 0;
    }

    if ($StockReportDisableFilterFields == 1 && !empty(trim($_POST["productType"]))) {
        $IsTypeProduct = 1;
        $Data .= 'Type Product : ' . $_POST["productType"] . ' | ';
    } else {
        $IsTypeProduct = 0;
    }

    if ($StockReportDisableFilterFields == 1 && !empty(trim($_POST["freezingType"]))) {
        $IsFreezing = 1;
        $Data .= 'Freezing Type : ' . $_POST["freezingType"] . ' | ';
    } else {
        $IsFreezing = 0;
    }

    if ($StockReportDisableFilterFields == 1 && !empty(trim($_POST["soaking"]))) {
        $IsSoaking = 1;
        $Data .= 'Soaking : ' . $_POST["soaking"] . ' | ';
    } else {
        $IsSoaking = 0;
    }

    if ($StockReportDisableFilterFields == 1 && !empty(trim($_POST["brand"]))) {
        $IsBrand = 1;
        $Data .= 'Brand : ' . $_POST["brandname"] . ' | ';
    } else {
        $IsBrand = 0;
    }

    if ($StockReportDisableFilterFields == 1 && !empty(trim($_POST["variety"]))) {
        $IsVariety = 1;
        $Data .= 'Variety : ' . $_POST["variety"] . ' | ';
    } else {
        $IsVariety = 0;
    }

    if ($StockReportDisableFilterFields == 1 && !empty(trim($_POST["pckgStyle"]))) {
        $IsPackingStyle = 1;
        $Data .= 'Packing Style : ' . $_POST["packingstyleName"] . ' | ';
    } else {
        $IsPackingStyle = 0;
    }

    if ($StockReportDisableFilterFields == 1 && !empty(trim($_POST["packingType"]))) {
        $IsPackingType = 1;
        $Data .= 'Packing Type : ' . $_POST["packingType"] . ' | ';
    } else {
        $IsPackingType = 0;
    }

    if ($StockReportDisableFilterFields == 1 && !empty(trim($_POST["pdCategory"]))) {
        $IsProductCategory = 1;
        $Data .= 'Product Category : ' . $_POST["pdCategory"] . ' | ';
    } else {
        $IsProductCategory = 0;
    }

    if ($StockReportDisableFilterFields == 1 && !empty(trim($_POST["abStatus"]))) {
        $IsAbStatus = 1;
        $Data .= 'Ab Status : ' . $_POST["abStatus"] . ' | ';
    } else {
        $IsAbStatus = 0;
    }

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {

        $resTotal = $resList->TotalCount;
        $List = $resList->Data;
        $GradesList = $resList->Grades;
        $StockTotal = $resList->StockTotal;
        $ProductTotal = $resList->ProductTotal;
?>

        <div style="overflow-x:auto; width:100%;">
            <H5 style="font-size: 16px; font-weight: 700;color:#000;">
                <?php echo $Data ?>
            </H5>

            <table id="tblDataTable" class="table table-hover table-bordered">

                <thead class="thead-light">
                    <tr>

                        <!-- Sticky Columns -->
                        <th class="sticky-col sticky-header" style="left:0px; min-width:30px;">S.No</th>


                        <?php if ($IsPackingType == 0) { ?>
                            <th class="sticky-col sticky-header" style="left:<?php echo $left; ?>px; min-width:70px;">Packing Type</th>
                        <?php } ?>

                        <?php if ($IsBrand == 0) { ?>
                            <th class="sticky-col sticky-header" style="left:<?php echo $left; ?>px; min-width:120px;">Brand</th>
                        <?php } ?>

                        <?php if ($IsVariety == 0) { ?>
                            <th class="sticky-col sticky-header" style="left:<?php echo $left; ?>px; min-width:70px;">Variety</th>
                        <?php } ?>

                        <?php if ($IsPackingStyle == 0) { ?>
                            <th class="sticky-col sticky-header" style="left:<?php echo $left; ?>px; min-width:70px;">Packing</th>
                        <?php } ?>

                        <?php if ($IsTypeProduct == 0) { ?>
                            <th class="sticky-col sticky-header" style="left:<?php echo $left; ?>px; min-width:70px;">Product Type</th>
                        <?php } ?>

                        <?php if ($IsFreezing == 0) { ?>
                            <th class="sticky-col sticky-header" style="left:<?php echo $left; ?>px; min-width:70px;">Freezing Type</th>
                        
                        <?php } ?>

                        <!-- Remaining Columns -->
                        <?php if ($IsSpecies == 0) { ?>
                            <th>Species</th>
                        <?php } ?>

                        <?php if ($IsProductCategory == 0) { ?>
                            <th>Product Category</th>
                        <?php } ?>

                        <?php if ($IsAbStatus == 0) { ?>
                            <th>Ab Status</th>
                        <?php } ?>

                        <th>Glaze</th>
                        <th>Glaze Type</th>

                        <?php if ($IsSoaking == 0) { ?>
                            <th>Soaking</th>
                        <?php } ?>

                        <?php foreach ($GradesList as $list) { ?>
                            <th class="thGrades"><?php echo $list ?></th>
                        <?php } ?>

                        <th>MCS</th>
                        <th>Loose</th>
                        <th>Slabs</th>
                        <th>KGs</th>

                    </tr>
                </thead>

                <tbody>

                    <?php
                    foreach ($List as $item) {

                        $left = 30;
                    ?>

                        <tr>

                            <td class="sticky-col" style="left:0px; background:#fff;">
                                <?php echo htmlspecialchars($item->SNo); ?>
                            </td>

                            <?php if ($IsPackingType == 0) { ?>
                                <td class="sticky-col" style="left:<?php echo $left; ?>px; background:#fff; min-width:70px;">
                                    <?php echo $item->PackingType ?>
                                </td>
                                <?php $left += 70; ?>
                            <?php } ?>

                            <?php if ($IsBrand == 0) { ?>
                                <td class="sticky-col" style="left:<?php echo $left; ?>px; background:#fff; min-width:120px;">
                                    <?php echo $item->Brand ?>
                                </td>
                                <?php $left += 150; ?>
                            <?php } ?>

                            <?php if ($IsVariety == 0) { ?>
                                <td class="sticky-col" style="left:<?php echo $left; ?>px; background:#fff; min-width:70px;">
                                    <?php echo $item->Species . '-' . $item->Variety ?>
                                </td>
                                <?php $left += 70; ?>
                            <?php } ?>

                            <?php if ($IsPackingStyle == 0) { ?>
                                <td class="sticky-col" style="left:<?php echo $left; ?>px; background:#fff; min-width:120px;">
                                    <?php echo $item->PackingStyle ?>
                                </td>
                                <?php $left += 120; ?>
                            <?php } ?>

                            <?php if ($IsTypeProduct == 0) { ?>
                                <td class="sticky-col" style="left:<?php echo $left; ?>px; background:#fff; min-width:70px;">
                                    <?php echo $item->TypeProduct ?>
                                </td>
                                <?php $left += 70; ?>
                            <?php } ?>

                            <?php if ($IsFreezing == 0) { ?>
                                <td class="sticky-col" style="left:<?php echo $left; ?>px; background:#fff; min-width:50px;">
                                    <?php echo $item->FreezingType ?>
                                </td>
                                <?php $left += 50; ?>
                            <?php } ?>

                            <!-- Remaining Data -->
                            <?php if ($IsSpecies == 0) { ?>
                                <td><?php echo $item->Species ?></td>
                            <?php } ?>

                            <?php if ($IsProductCategory == 0) { ?>
                                <td><?php echo $item->ProductCategory ?></td>
                            <?php } ?>

                            <?php if ($IsAbStatus == 0) { ?>
                                <td><?php echo $item->AbStatus ?></td>
                            <?php } ?>

                            <td><?php echo $item->GlazePerc ?></td>
                            <td><?php echo $item->Glaze ?></td>

                            <?php if ($IsSoaking == 0) { ?>
                                <td><?php echo $item->Soaking ?></td>
                            <?php } ?>

                            <?php
                            $g = 0;
                            foreach ($item->SubGrades as $SubGrades) {
                                foreach ($GradesList as $list) {
                                    if ($list == $SubGrades->GradeName) {
                                        echo '<td class="Grade'. $g.'" data-grade_mcs="' . $SubGrades->GradeMcs . '"  data-grade_looseBags="' . $SubGrades->GradeLooseBags . '">' . $SubGrades->GradeValue . '</td>';
                                        $g++;
                                    }
                                }
                            }
                            ?>
                            <?php
                            $dataInfo = [
                                "variety" => $item->VarietyID,
                                "pckgStyle" => $item->PackingStyleID,
                                "pckgType" => $item->PackingType,
                                "prodCategory" => $item->ProductCategory,
                                "fSpecies" => $item->Species,
                                "species" => $item->Species,
                                "productType" => $item->TypeProduct,
                                "fTypeProduct" => $item->TypeProduct,
                                "fsoaking" => $item->Soaking,
                                "freezingType" => $item->FreezingType,
                                "fShortName" => $item->Variety,
                                "grade" => trim($_POST["grade"]),
                                "GlazePerc" => $item->GlazePerc,
                                "Brand" => $item->BrandID,
                                "RequestFrom" => 'StockReport'
                            ];

                            // Add plant only if $condition == 1
                            if ($showStockUnitWise == 1) {
                                $dataInfo["coldStore"] = $item->UnitID;
                            }
                            ?>

                            <td data-info='<?php echo json_encode($dataInfo);
                            ?>' class="show-rack-popup mcs" style="color: blue; cursor: pointer; ">
                                <?php echo $item->ProductMcs ?>
                            </td>
                            <td class="looseBags"><?php echo $item->ProductLooseBags ?></td>
                            <td class="slabs"><?php echo $item->ProductSlabs ?></td>
                            <td class="kgs"><?php echo $item->ProductTotalKgs ?></td>

                        </tr>

                    <?php } ?>

                </tbody>
                <tfoot>
                    <tr>
                        <th>Overall Totals</th>
                         <?php if ($IsPackingType == 0) { ?>
                             <th></th>
                        <?php } ?>
                        <?php if ($IsBrand == 0) { ?>
                             <th></th>
                        <?php } ?>
                        <?php if ($IsVariety == 0) { ?>
                            <th></th>
                        <?php } ?>
                        <?php if ($IsPackingStyle == 0) { ?>
                             <th></th>
                        <?php } ?>
                        <?php if ($IsTypeProduct == 0) { ?>
                             <th></th>
                        <?php } ?>
                        <?php if ($IsFreezing == 0) { ?>
                             <th></th>
                        <?php } ?>
                        <?php if ($IsSpecies == 0) { ?>
                             <th></th>
                        <?php } ?>
                        <?php if ($IsProductCategory == 0) { ?>
                             <th></th>
                        <?php } ?>
                        <?php if ($IsAbStatus == 0) { ?>
                             <th></th>
                        <?php } ?>
                        <th></th>
                        <th></th>
                        <?php if ($IsSoaking == 0) { ?>
                            <th> </th>
                        <?php } ?>
                        <?php
                        $gf= 0;
                        foreach ($StockTotal as $total) {
                            foreach ($GradesList as $list) {
                                if ($list == $total->GradeName) {
                                    echo '<th class="GradeTotal'. $gf.'">' . $total->GradeValue . '</th>';
                                    $gf++;
                                }
                            }
                        }
                        ?>
                        <th class="totalMcs"><?php echo $ProductTotal->TotalMcs ?></th>
                        <th class="totalLoose"><?php echo $ProductTotal->TotalLooseBags ?></th>
                        <th class="totalSlabs"><?php echo $ProductTotal->TotalLSlabs ?></th>
                        <th class="totalKgs"><?php echo $ProductTotal->TotalWeight ?></th>
                    </tr>
                </tfoot>

            </table>
        </div>

<?php
    } else {
        echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : " . $error . "</div>";
    }
}
?>