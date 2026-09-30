<?php

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

include_once $_SERVER['DOCUMENT_ROOT'] . '/config/global.php';
include_once ROOT . 'config/api-call.php';

$api = API_PRODUCTION_STANDARD_YIELDS;

$PageNo = trim($_POST["PageNo"]);
$SearchKey = trim($_POST["SearchKey"]);
$LoadAll = trim($_POST["LoadAll"]);

$bodyContent = array(
    'SearchKey' => $SearchKey,
    'PageNo' => $PageNo,
    'LoadAll' => $LoadAll
);

$resList = CallAPI($api, "GET", "", $bodyContent);

// var_dump($resList);
// echo json_encode($resList, JSON_PRETTY_PRINT);
// exit;
if (isset($resList) && !empty($resList)) {

    $error = $resList->Error;

    if (!isset($error) || empty($error)) {
        $PageCount = $resList->PageCount;
        $resTotal = $resList->TotalCount;
        $List = $resList->Data;
        ?>
        <?php if (!isset($error) || empty($error)) { ?>

            <table id="tblDataTable" class="table table-hover table-bordered">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 80px">S.No</th>
                        <th style="min-width: 155px;">Species</th>
                        <th>Type Product</th>
                        <th>Variety</th>
                        <th>Grade</th>
                        <th>Count</th>
                        <th>Freezing Type</th>
                        <th style="min-width: 185px;">Treatment</th>
                        <th>Type of Material</th>
                        <th>Yield</th>
                        <th>Status</th>
                        <th style="width: 100px">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($List as $item) { ?>
                        <tr>
                            <td><?php echo $item->SNO ?></td>
                            <td><?php echo $item->SpeciesName ?></td>
                            <td><?php echo $item->TypeProduct ?></td>
                            <td><?php echo $item->Variety ?></td>
                            <td><?php echo $item->Grade ?></td>
                            <td><?php echo $item->Count ?></td>
                            <td><?php echo $item->FreezingType ?></td>
                            <td><?php echo $item->TreatmentName ?></td>
                            <td><?php echo $item->TypeOfMaterial ?></td>
                            <td><?php echo $item->YieldPercentage ?></td>
                            <td>
                                <?php if ($item->Status == "1") { ?>
                                    <button class="btn btn-link badge badge-success font-size-10"
                                        >
                                        Active
                                    </button>
                                <?php } else { ?>
                                    <button class="btn btn-link badge badge-danger font-size-10"
                                        >
                                        InActive
                                    </button>
                                <?php } ?>
                            </td>
                            <td class="text-center">
                                <a href='<?php echo $item->EditLink ?>' class="mr-3 text-primary btnActions" data-placement="top"
                                    title="Edit " data-original-title="Edit">
                                    <i class="mdi mdi-pencil font-size-16"></i>
                                </a>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <div class="mt-3 mb-3">Showing 1 to <?php echo count($List) ?> out of <?php echo $resTotal ?> entries</div>
            <?php $activepagestyle = "border:2px solid #74788d" ?>
        <?php } ?>
        <div>
            <input type='hidden' id='hdnTotalRecords' value='<?php echo $List->TotalCount ?>' />
            <ul class="pagination pagination-rounded justify-content-center">
                <?php
                for ($i = 1; $i <= $PageCount; $i++) {
                    $pageActive = ($PageNo == $i ? "active" : "");
                    echo '<li class="page-item"><button class="page-link ' . $pageActive . ' btnPageno" data-pageno="' . $i . '">' . $i . '</button></li>';
                }
                ?>
            </ul>
        </div>
        <?php
    } else {
        echo "<div class='text-center mt-3 mb-3 text-bold'>ERROR : " . $error . "</div>";
    }
}
?>
